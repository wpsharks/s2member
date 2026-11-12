<?php
// @codingStandardsIgnoreFile
/**
 * Durable payment-event receipt utilities.
 *
 * @package s2Member\Payment_Events
 * @since 261111.2149
 */
if(!defined('WPINC')) // MUST have WordPress.
	exit('Do not access this file directly.');

if(!class_exists('c_ws_plugin__s2member_payment_events'))
{
	/**
	 * Store one compact receipt per logical gateway event, independently of delivery attempts.
	 *
	 * @package s2Member\Payment_Events
	 * @since 261111.2149
	 */
	class c_ws_plugin__s2member_payment_events
	{
		/**
		 * Construct a stable, site-local event option name without exposing provider identifiers.
		 *
		 * Callers must supply the merchant identity from trusted site configuration, not an
		 * unauthenticated notification. The event type must be semantic (e.g. payment),
		 * not a gateway delivery type that can change across duplicate notifications.
		 *
		 * @since 261111.2149
		 *
		 * @param string $gateway Canonical gateway slug.
		 * @param string $merchant Canonical trusted merchant identifier; preserve provider-specific case.
		 * @param string $transaction Provider transaction ID, case-sensitive.
		 * @param string $event Canonical event type, shared by equivalent delivery paths.
		 *
		 * @return string|bool Option name, or FALSE for invalid input.
		 */
		public static function option_name($gateway, $merchant, $transaction, $event)
		{
			if(!is_string($gateway) || !is_string($merchant) || !is_string($transaction) || !is_string($event))
				return FALSE;

			$gateway = strtolower(trim($gateway));
			$merchant = trim($merchant);
			$event = strtolower(trim($event));

			if(!preg_match('/^[a-z0-9_-]{1,48}$/D', $gateway) || !preg_match('/^[a-z0-9_-]{1,48}$/D', $event)
			   || $merchant === '' || strlen($merchant) > 254 || $transaction === '' || strlen($transaction) > 255
			   || preg_match('/[\x00-\x1f\x7f]/', $merchant) || preg_match('/[\x00-\x1f\x7f]/', $transaction))
				return FALSE;

			//261111.2149 Length-safe serialization keeps distinct identities distinct; never hash mutable IPN delivery metadata.
			return 's2m_pay_evt_'.hash('sha256', serialize(array($gateway, $merchant, $transaction, $event)));
		}

		/**
		 * Obtain an account scope from trusted, configured gateway settings.
		 *
		 * This is never read from an IPN or webhook payload. Provider account/key
		 * rotation can change the derived identity; migrate existing event records
		 * explicitly when changing a gateway account or configured identifier.
		 *
		 * @since 261112.1205
		 *
		 * @param string $gateway Authenticated canonical gateway slug.
		 *
		 * @return string|WP_Error Configured account scope or a failure to resolve it.
		 */
		public static function trusted_merchant($gateway)
		{
			if(!is_string($gateway) || !preg_match('/^[a-z0-9_-]{1,48}$/D', $gateway = strtolower(trim($gateway))))
				return new WP_Error('s2m_pay_invalid_gateway', 'Invalid authenticated payment gateway.');
			$options = isset($GLOBALS['WS_PLUGIN__']['s2member']['o']) && is_array($GLOBALS['WS_PLUGIN__']['s2member']['o']) ? $GLOBALS['WS_PLUGIN__']['s2member']['o'] : array();
			$field = '';
			$kind = 'id';
			switch($gateway)
			{
				case 'paypal':
					$field = !empty($options['paypal_merchant_id']) ? 'paypal_merchant_id' : 'paypal_business';
					$kind = $field === 'paypal_business' ? 'email' : 'id';
					break;
				case 'stripe': $field = 'pro_stripe_api_publishable_key'; break;
				case 'authnet': $field = 'pro_authnet_api_login_id'; break;
				case 'clickbank': $field = 'pro_clickbank_username'; break;
				case 'alipay': $field = 'pro_alipay_seller_email'; $kind = 'email'; break;
				case 'google': $field = 'pro_google_merchant_id'; break;
				case 'ccbill':
					//261112.1205 Both CCBill account and subaccount are relevant to merchant routing.
					$client = isset($options['pro_ccbill_client_id']) && is_scalar($options['pro_ccbill_client_id']) ? trim((string)$options['pro_ccbill_client_id']) : '';
					$sid = isset($options['pro_ccbill_client_sid']) && is_scalar($options['pro_ccbill_client_sid']) ? trim((string)$options['pro_ccbill_client_sid']) : '';
					if($client === '' || $sid === '')
						return new WP_Error('s2m_pay_missing_merchant', 'CCBill account identity is not configured.');
					$merchant = 'id:'.strlen($client).':'.$client.':'.strlen($sid).':'.$sid;
					return strlen($merchant) <= 254 && !preg_match('/[\x00-\x1f\x7f]/', $merchant) ? $merchant : new WP_Error('s2m_pay_invalid_merchant', 'Invalid configured CCBill account identity.');
				default:
					return new WP_Error('s2m_pay_unknown_gateway', 'Gateway account identity is not supported.');
			}
			$value = isset($options[$field]) && is_scalar($options[$field]) ? trim((string)$options[$field]) : '';
			if($value === '')
				return new WP_Error('s2m_pay_missing_merchant', 'Gateway account identity is not configured.');
			if($kind === 'email')
				$value = strtolower($value);
			$merchant = $kind.':'.$value;
			return strlen($merchant) <= 254 && !preg_match('/[\x00-\x1f\x7f]/', $merchant) ? $merchant : new WP_Error('s2m_pay_invalid_merchant', 'Invalid configured gateway account identity.');
		}

		/**
		 * Read an event receipt without relying on potentially stale option caches.
		 *
		 * @since 261111.2149
		 *
		 * @param string $name A name returned by option_name().
		 *
		 * @return array|bool|WP_Error Record, FALSE if absent, or WP_Error on invalid data/query failure.
		 */
		public static function read($name)
		{
			global $wpdb;

			if(!is_string($name) || !preg_match('/^s2m_pay_evt_[a-f0-9]{64}$/D', $name))
				return new WP_Error('s2m_pay_event_invalid_name', 'Invalid payment event record name.');

			$value = $wpdb->get_var($wpdb->prepare("SELECT `option_value` FROM `{$wpdb->options}` WHERE `option_name` = %s LIMIT 1", $name));
			if($wpdb->last_error !== '')
				return new WP_Error('s2m_pay_event_database_error', 'Could not read payment event.');
			if($value === NULL)
				return FALSE;

			$record = is_string($value) ? json_decode($value, TRUE) : NULL;
			if(!is_array($record) || !isset($record['version'], $record['id'], $record['gateway'], $record['event'], $record['status'], $record['created'], $record['updated'], $record['attempts'])
			   || $record['version'] !== 1 || $record['id'] !== $name || !is_string($record['gateway']) || !is_string($record['event'])
			   || !in_array($record['status'], array('received', 'processing', 'pending_registration', 'completed', 'failed'), TRUE)
			   || !is_int($record['created']) || !is_int($record['updated']) || !is_int($record['attempts'])
			   || $record['created'] < 1 || $record['updated'] < $record['created'] || $record['attempts'] < 0
			   || ($record['status'] === 'processing' && (!isset($record['owner']) || !is_string($record['owner'])
			       || !preg_match('/^[a-f0-9]{64}$/D', $record['owner']) || $record['attempts'] === 0))
			   || ($record['status'] !== 'processing' && array_key_exists('owner', $record))
			   || (array_key_exists('subscription_hash', $record) && (!is_string($record['subscription_hash'])
			       || !preg_match('/^[a-f0-9]{64}$/D', $record['subscription_hash'])))
			   //261112.1300 A processing worker records its irreversible-work boundary before changing a member or calling external hooks.
			   || (array_key_exists('fulfillment_started', $record) && (!is_int($record['fulfillment_started'])
			       || $record['fulfillment_started'] < $record['created']))
			   //261112.1527 A replay permission is immutable first-receipt metadata; old shadow receipts lack it and must not be replayed automatically.
			   || (array_key_exists('replay_authorized', $record) && $record['replay_authorized'] !== TRUE)
			   || (array_key_exists('replay_origin', $record) && $record['replay_origin'] !== 'authoritative')
			   || (isset($record['replay_authorized']) && !isset($record['replay_origin'])))
				return new WP_Error('s2m_pay_event_invalid_record', 'Payment event record has an unknown format.');

			return $record;
		}

		/**
		 * Insert a durable receipt if absent; never replace an existing event record.
		 *
		 * This method only records receipt. It does NOT grant access, claim a processing
		 * lease, or mark a payment completed. Later fulfillment must verify its own
		 * durable state and side effects before advancing a receipt's status.
		 *
		 * @since 261111.2149
		 *
		 * @param string $gateway Canonical gateway slug.
		 * @param string $merchant Trusted merchant identifier from site configuration.
		 * @param string $transaction Provider transaction ID.
		 * @param string $event Canonical semantic event type.
		 * @param bool $authoritative TRUE only when the entire incoming payment flow bypasses legacy transient fulfillment.
		 *
		 * @return array|WP_Error Array with `created` flag and `record`, or WP_Error.
		 */
		public static function receive($gateway, $merchant, $transaction, $event, $authoritative = FALSE)
		{
			global $wpdb;

			if(!is_bool($authoritative) || ($authoritative && (!is_string($event) || strtolower(trim($event)) !== 'payment')))
				return new WP_Error('s2m_pay_event_invalid_origin', 'Invalid payment-event replay origin.');
			$name = self::option_name($gateway, $merchant, $transaction, $event);
			if($name === FALSE)
				return new WP_Error('s2m_pay_event_invalid_identity', 'Invalid payment event identity.');

			$now = time();
			$record = array(
				'version' => 1,
				'id' => $name,
				'gateway' => strtolower(trim($gateway)),
				'event' => strtolower(trim($event)),
				'status' => 'received',
				'created' => $now,
				'updated' => $now,
				'attempts' => 0,
			);
			//261112.1534 Persist the authoritative origin with the first INSERT, so a pre-enqueue crash remains safely retryable.
			if($authoritative)
				$record['replay_origin'] = 'authoritative';
			$value = wp_json_encode($record);
			if(!is_string($value))
				return new WP_Error('s2m_pay_event_encode_failed', 'Could not encode payment event.');

			//261111.2149 A unique-indexed, non-overwriting insert is essential: concurrent deliveries must never reset an existing event's state.
			$inserted = $wpdb->query($wpdb->prepare("INSERT IGNORE INTO `{$wpdb->options}` (`option_name`, `option_value`, `autoload`) VALUES (%s, %s, %s)", $name, $value, 'no'));
			if($inserted === FALSE || $wpdb->last_error !== '' || ($inserted !== 0 && $inserted !== 1))
				return new WP_Error('s2m_pay_event_database_error', 'Could not create payment event.');

			if($inserted === 1)
			{
				wp_cache_delete($name, 'options');
				$notoptions = wp_cache_get('notoptions', 'options');
				if(is_array($notoptions) && isset($notoptions[$name]))
				{
					unset($notoptions[$name]);
					wp_cache_set('notoptions', $notoptions, 'options');
				}
				return array('created' => TRUE, 'record' => $record);
			}

			//261111.2149 Read the existing row from SQL: duplicate receipt is not proof of successful fulfillment.
			$existing = self::read($name);
			if(is_wp_error($existing))
				return $existing;
			if($existing === FALSE)
				return new WP_Error('s2m_pay_event_lost_record', 'Existing payment event disappeared during receipt.');
			if($existing['gateway'] !== $record['gateway'] || $existing['event'] !== $record['event'])
				return new WP_Error('s2m_pay_event_identity_mismatch', 'Existing payment event identity is inconsistent.');
			return array('created' => FALSE, 'record' => $existing);
		}

		/**
		 * Claim an existing receipt for work using a per-event database lease.
		 *
		 * Completed events are never claimed again. Pending-registration events require an
		 * explicit resume flag so ordinary gateway redeliveries cannot redo pending work.
		 * A processing record may be claimed after its previous lease expires, but this is
		 * not proof that its previous worker performed no external side effects.
		 *
		 * @since 261112.0508
		 *
		 * @param string $name Event name from option_name().
		 * @param int $lease_seconds Lease duration in seconds.
		 * @param bool $resume_pending Whether registration processing may resume a queued event.
		 *
		 * @return array|WP_Error `claimed`, `status`, and, on success, `token` and `record`.
		 */
		public static function claim($name, $lease_seconds = 300, $resume_pending = FALSE)
		{
			if(!self::valid_name($name) || !is_int($lease_seconds) || $lease_seconds < 1 || $lease_seconds > 86400
			   || !is_bool($resume_pending))
				return new WP_Error('s2m_pay_event_invalid_claim', 'Invalid payment event claim.');

			$resource = self::lease_resource($name);
			$token = c_ws_plugin__s2member_database::lock_acquire($resource, $lease_seconds);
			if(is_wp_error($token))
				return $token;
			if($token === FALSE)
				return array('claimed' => FALSE, 'status' => 'busy');

			$previous = self::read($name);
			if(is_wp_error($previous) || $previous === FALSE)
			{
				c_ws_plugin__s2member_database::lock_release($resource, $token);
				return is_wp_error($previous) ? $previous : new WP_Error('s2m_pay_event_missing', 'Payment event must be received before it can be claimed.');
			}

			if($previous['status'] === 'completed' || ($previous['status'] === 'pending_registration' && !$resume_pending))
			{
				$released = c_ws_plugin__s2member_database::lock_release($resource, $token);
				if(is_wp_error($released))
					return $released;
				return array('claimed' => FALSE, 'status' => $previous['status'], 'record' => $previous);
			}
			//261112.1300 A crashed worker may already have applied membership changes or external notifications; never automatically repeat ambiguous effects.
			if(isset($previous['fulfillment_started']))
			{
				$released = c_ws_plugin__s2member_database::lock_release($resource, $token);
				if(is_wp_error($released))
					return $released;
				return new WP_Error('s2m_pay_event_ambiguous_fulfillment', 'Payment fulfillment may have already produced side effects; reconciliation is required before retry.');
			}
			if($previous['attempts'] === PHP_INT_MAX)
			{
				c_ws_plugin__s2member_database::lock_release($resource, $token);
				return new WP_Error('s2m_pay_event_attempt_limit', 'Payment event attempt count cannot be advanced.');
			}

			//261112.0508 The owner is part of the persisted CAS value; an expired worker cannot overwrite a later claimant's outcome.
			$next = $previous;
			$next['status'] = 'processing';
			$next['owner'] = $token;
			$next['attempts']++;
			$next['updated'] = max(time(), $previous['updated']);
			$updated = self::replace($name, $previous, $next);
			if($updated !== TRUE)
			{
				c_ws_plugin__s2member_database::lock_release($resource, $token);
				if(is_wp_error($updated))
					return $updated;
				return array('claimed' => FALSE, 'status' => 'raced');
			}
			return array('claimed' => TRUE, 'status' => 'processing', 'token' => $token, 'record' => $next);
		}

		/**
		 * Record the irreversible-fulfillment boundary before modifying user data or firing hooks.
		 *
		 * This is a conservative crash barrier, not proof of completed fulfillment. If the
		 * worker dies after this marker is stored, claim() refuses an automatic replay.
		 * The operator must reconcile the payment and any external side effects first.
		 * Callers must confirm a TRUE result before beginning any side effects.
		 *
		 * @since 261112.1300
		 *
		 * @param string $name Payment event option name.
		 * @param string $token Current processing lease owner token.
		 *
		 * @return bool|WP_Error TRUE only when the marker was recorded by this owner, FALSE on lost ownership or repeat, or WP_Error.
		 */
		public static function begin_fulfillment($name, $token)
		{
			if(!self::valid_name($name) || !is_string($token) || !preg_match('/^[a-f0-9]{64}$/D', $token))
				return new WP_Error('s2m_pay_event_invalid_owner', 'Invalid payment event owner.');
			$previous = self::read($name);
			if(is_wp_error($previous) || $previous === FALSE)
				return is_wp_error($previous) ? $previous : FALSE;
			if($previous['status'] !== 'processing' || !isset($previous['owner']) || !hash_equals($previous['owner'], $token)
			   || isset($previous['fulfillment_started']))
				return FALSE;
			$owned = c_ws_plugin__s2member_database::lock_is_owned(self::lease_resource($name), $token);
			if($owned !== TRUE)
				return $owned;
			$next = $previous;
			$next['fulfillment_started'] = max(time(), $previous['created']);
			$next['updated'] = max(time(), $previous['updated']);
			$changed = self::replace($name, $previous, $next);
			if($changed !== TRUE)
				return $changed;
			//261112.1300 Recheck ownership after CAS: an expired lease must not authorize external side effects.
			return c_ws_plugin__s2member_database::lock_is_owned(self::lease_resource($name), $token);
		}

		/**
		 * Atomically advance an owned processing event to a durable outcome.
		 *
		 * Only record an event as completed after verifying its fulfillment. Pending
		 * registration means the required payment work still has to be completed later.
		 * Call release() in a finally block, including after an unsuccessful transition.
		 *
		 * @since 261112.0508
		 *
		 * @param string $name Payment event option name.
		 * @param string $token Processing token returned by claim().
		 * @param string $status One of completed, failed, or pending_registration.
		 *
		 * @return bool|WP_Error TRUE on persistence, FALSE on lost ownership/race, or WP_Error.
		 */
		public static function transition($name, $token, $status)
		{
			if(!self::valid_name($name) || !is_string($token) || !preg_match('/^[a-f0-9]{64}$/D', $token)
			   || !in_array($status, array('completed', 'failed', 'pending_registration'), TRUE))
				return new WP_Error('s2m_pay_event_invalid_transition', 'Invalid payment event transition.');

			$previous = self::read($name);
			if(is_wp_error($previous) || $previous === FALSE)
				return is_wp_error($previous) ? $previous : FALSE;
			if($previous['status'] !== 'processing' || !isset($previous['owner']) || !hash_equals($previous['owner'], $token))
				return FALSE;

			$owned = c_ws_plugin__s2member_database::lock_is_owned(self::lease_resource($name), $token);
			if($owned !== TRUE)
				return $owned;

			$next = $previous;
			$next['status'] = $status;
			unset($next['owner']);
			$next['updated'] = max(time(), $previous['updated']);
			//261112.0508 Compare against the exact previous record; a reclaimed lease changes owner and defeats stale finalization.
			return self::replace($name, $previous, $next);
		}

		/**
		 * Renew an event's active lease when its persisted processing owner matches.
		 *
		 * @since 261112.0508
		 *
		 * @param string $name Payment event option name.
		 * @param string $token Current processing owner.
		 * @param int $lease_seconds New lease duration.
		 *
		 * @return bool|WP_Error Renewal result from the shared database utility.
		 */
		public static function renew($name, $token, $lease_seconds = 300)
		{
			if(!self::valid_name($name) || !is_string($token) || !preg_match('/^[a-f0-9]{64}$/D', $token))
				return new WP_Error('s2m_pay_event_invalid_owner', 'Invalid payment event owner.');
			$record = self::read($name);
			if(is_wp_error($record) || $record === FALSE)
				return is_wp_error($record) ? $record : FALSE;
			if($record['status'] !== 'processing' || !hash_equals($record['owner'], $token))
				return FALSE;
			return c_ws_plugin__s2member_database::lock_renew(self::lease_resource($name), $token, $lease_seconds);
		}

		/**
		 * Release this worker's event lease, without deleting the durable event record.
		 *
		 * @since 261112.0508
		 *
		 * @param string $name Payment event option name.
		 * @param string $token Owner token.
		 *
		 * @return bool|WP_Error Result from the shared database utility.
		 */
		public static function release($name, $token)
		{
			if(!self::valid_name($name))
				return new WP_Error('s2m_pay_event_invalid_name', 'Invalid payment event record name.');
			return c_ws_plugin__s2member_database::lock_release(self::lease_resource($name), $token);
		}

		/**
		 * Persist a separate pending registration payment while the owning event lease is active.
		 *
		 * The queue is indexed by subscription and transaction event, never by subscription
		 * alone. Replays with a different payload do not overwrite the first queued event.
		 * Pending entries are retained until the receipt is durably completed and acknowledged.
		 *
		 * @since 261112.0600
		 *
		 * @param string $gateway Canonical gateway slug.
		 * @param string $merchant Trusted merchant identifier.
		 * @param array $ipn Minimal, authenticated recurring-payment payload to replay after registration.
		 * @param string $token Current processing lease owner.
		 *
		 * @return array|WP_Error `created` and `name` on success, or WP_Error.
		 */
		public static function queue_pending($gateway, $merchant, $ipn, $token)
		{
			global $wpdb;

			if(!is_array($ipn) || !isset($ipn['subscr_id'], $ipn['txn_id'], $ipn['subscr_gateway'], $ipn['txn_type'])
			   || !is_string($ipn['subscr_id']) || !self::valid_subscription($ipn['subscr_id'])
			   || !is_string($ipn['txn_id']) || !is_string($ipn['subscr_gateway']) || $ipn['subscr_gateway'] === ''
			   || $ipn['txn_type'] !== 'subscr_payment' || !is_string($token) || !preg_match('/^[a-f0-9]{64}$/D', $token))
				return new WP_Error('s2m_pay_pending_invalid_input', 'Invalid pending payment data or processing owner.');

			$name = self::option_name($gateway, $merchant, $ipn['txn_id'], 'payment');
			if($name === FALSE)
				return new WP_Error('s2m_pay_pending_invalid_identity', 'Invalid pending payment identity.');
			$receipt = self::read($name);
			if(is_wp_error($receipt) || $receipt === FALSE)
				return is_wp_error($receipt) ? $receipt : new WP_Error('s2m_pay_pending_missing', 'Payment receipt must be registered first.');
			if($receipt['status'] !== 'processing' || !isset($receipt['owner']) || !hash_equals($receipt['owner'], $token))
				return new WP_Error('s2m_pay_pending_not_owned', 'Pending payment requires a current processing claim.');
			$owned = c_ws_plugin__s2member_database::lock_is_owned(self::lease_resource($name), $token);
			if($owned !== TRUE)
				return is_wp_error($owned) ? $owned : new WP_Error('s2m_pay_pending_not_owned', 'Payment event lease has expired.');

			//261112.0600 Allow only the historical simulated IPN fields, and never persist proxy keys, headers, or full gateway payloads.
			$allowed = array('txn_type', 'subscr_gateway', 'subscr_id', 'subscr_baid', 'subscr_cid', 'txn_id', 'custom', 'invoice', 'mc_gross', 'mc_currency', 'tax', 'payer_email', 'first_name', 'last_name', 'item_name', 'item_number', 'option_name1', 'option_selection1', 'option_name2', 'option_selection2');
			$payload = array();
			foreach($allowed as $field)
				if(array_key_exists($field, $ipn))
				{
					if(!is_string($ipn[$field]) && !is_int($ipn[$field]) && !is_float($ipn[$field]))
						return new WP_Error('s2m_pay_pending_invalid_payload', 'Pending payment contains an invalid field.');
					$payload[$field] = (string)$ipn[$field];
				}

			$queue_name = self::pending_name($ipn['subscr_id'], $name);
			$record = array('version' => 1, 'event' => $name, 'created' => time(), 'ipn' => $payload);
			$value = wp_json_encode($record);
			if(!is_string($value) || strlen($value) > 8192)
				return new WP_Error('s2m_pay_pending_oversize', 'Pending payment cannot be encoded within the size limit.');

			//261112.0600 Bind each provider transaction to exactly one subscription, preventing a repeated transaction ID from entering another account's queue.
			$subscription_hash = hash('sha256', $ipn['subscr_id']);
			if(isset($receipt['subscription_hash']))
			{
				if(!hash_equals($receipt['subscription_hash'], $subscription_hash))
					return new WP_Error('s2m_pay_pending_subscription_mismatch', 'This payment event belongs to another subscription.');
			}
			else
			{
				$bound = $receipt;
				$bound['subscription_hash'] = $subscription_hash;
				$bound['updated'] = max(time(), $receipt['updated']);
				$changed = self::replace($name, $receipt, $bound);
				if($changed !== TRUE)
					return is_wp_error($changed) ? $changed : new WP_Error('s2m_pay_pending_raced', 'Payment event changed while binding its subscription.');
			}

			//261112.0600 The unique option_name index preserves every distinct transaction without a shared read-modify-write subscription list.
			$inserted = $wpdb->query($wpdb->prepare("INSERT IGNORE INTO `{$wpdb->options}` (`option_name`, `option_value`, `autoload`) VALUES (%s, %s, %s)", $queue_name, $value, 'no'));
			if($inserted === FALSE || $wpdb->last_error !== '' || ($inserted !== 0 && $inserted !== 1))
				return new WP_Error('s2m_pay_pending_database_error', 'Could not queue pending payment.');
			if($inserted === 1)
			{
				self::clear_option_cache($queue_name);
				return array('created' => TRUE, 'name' => $queue_name);
			}

			//261112.0600 An existing row must be intact and correspond to the same event; never overwrite its original replay payload.
			$previous = self::read_pending($queue_name, $name, $ipn['subscr_id']);
			if(is_wp_error($previous) || $previous === FALSE)
				return is_wp_error($previous) ? $previous : new WP_Error('s2m_pay_pending_lost', 'Pending payment disappeared while queueing.');
			return array('created' => FALSE, 'name' => $queue_name);
		}

		/**
		 * Record one verified payment as pending registration, without fulfilling it.
		 *
		 * The caller must already have authenticated the gateway and supplied a merchant
		 * identity from trusted site settings. Neither a raw IPN nor the gateway field
		 * in its replay payload can establish those identities. This method only queues
		 * work; it never claims that a member has received the purchased access.
		 *
		 * @since 261112.1116
		 *
		 * @param string $gateway Authenticated canonical gateway.
		 * @param string $merchant Trusted configured merchant identifier.
		 * @param array $ipn Verified, normalized simulated subscr_payment payload.
		 * @param bool $authoritative TRUE only for a first-ever payment received on a path with NO legacy fulfillment or transient replay.
		 *
		 * @return array|WP_Error `status`, `event`, `created` on success/busy, or WP_Error.
		 */
		public static function queue_verified_payment($gateway, $merchant, $ipn, $authoritative = FALSE)
		{
			if(!is_bool($authoritative))
				return new WP_Error('s2m_pay_pending_invalid_authority', 'Invalid pending payment replay authorization.');
			if(!is_array($ipn) || !isset($ipn['txn_type'], $ipn['txn_id'], $ipn['subscr_id'], $ipn['subscr_gateway'])
			   || !is_string($gateway) || !is_string($ipn['subscr_gateway']) || !is_string($ipn['txn_id'])
			   || !self::valid_subscription(isset($ipn['subscr_id']) ? $ipn['subscr_id'] : NULL)
			   || $ipn['txn_type'] !== 'subscr_payment' || strtolower(trim($gateway)) !== strtolower(trim($ipn['subscr_gateway'])))
				return new WP_Error('s2m_pay_pending_invalid_origin', 'Pending payment requires a verified matching gateway and payment identity.');

			$name = self::option_name($gateway, $merchant, $ipn['txn_id'], 'payment');
			if($name === FALSE)
				return new WP_Error('s2m_pay_pending_invalid_identity', 'Pending payment identity is invalid.');

			$receipt = self::receive($gateway, $merchant, $ipn['txn_id'], 'payment', $authoritative);
			if(is_wp_error($receipt))
				return $receipt;

			if($receipt['record']['status'] === 'completed')
				return array('event' => $name, 'status' => 'completed', 'created' => FALSE);

			//261112.1527 A pre-cutover shadow receipt may have been fulfilled by the legacy transient; never promote it on redelivery.
			if($authoritative && !isset($receipt['record']['replay_origin']))
				return new WP_Error('s2m_pay_pending_legacy_shadow', 'Existing payment receipts require reconciliation before authoritative replay.');

			if($receipt['record']['status'] === 'pending_registration')
			{
				//261112.1116 A pending status without its matching queue row is inconsistent; never acknowledge it as queued.
				$subscription_hash = hash('sha256', $ipn['subscr_id']);
				if(!isset($receipt['record']['subscription_hash']) || !hash_equals($receipt['record']['subscription_hash'], $subscription_hash))
					return new WP_Error('s2m_pay_pending_subscription_mismatch', 'Pending payment is not bound to this subscription.');
				$existing = self::read_pending(self::pending_name($ipn['subscr_id'], $name), $name, $ipn['subscr_id']);
				if(is_wp_error($existing))
					return $existing;
				if($existing === FALSE)
					return new WP_Error('s2m_pay_pending_missing', 'Pending receipt has no matching replay record.');
				return array('event' => $name, 'status' => 'pending_registration', 'created' => FALSE);
			}

			$claim = self::claim($name, 300);
			if(is_wp_error($claim))
				return $claim;
			if(empty($claim['claimed']))
				return array('event' => $name, 'status' => $claim['status'], 'created' => FALSE);

			$result = new WP_Error('s2m_pay_pending_unknown_outcome', 'Payment queue outcome is unknown.');
			try
			{
				$queued = self::queue_pending($gateway, $merchant, $ipn, $claim['token']);
				if(!is_wp_error($queued) && $authoritative)
				{
					//261112.1527 Only a new, immutable queue item from the same first receipt may be authorized for the future consumer.
					if(!$queued['created'] && !isset($receipt['record']['replay_origin']))
						$queued = new WP_Error('s2m_pay_pending_legacy_shadow', 'An existing queued payment cannot be promoted to automatic replay.');
					else
					{
						$current = self::read($name);
						if(is_wp_error($current))
							$queued = $current;
						else if($current === FALSE || $current['status'] !== 'processing' || !isset($current['owner'])
						        || !hash_equals($current['owner'], $claim['token']) || isset($current['fulfillment_started']))
							$queued = new WP_Error('s2m_pay_pending_lost_owner', 'Cannot authorize a changed or fulfilled payment receipt.');
						else
						{
							$owned = c_ws_plugin__s2member_database::lock_is_owned(self::lease_resource($name), $claim['token']);
							if($owned !== TRUE)
								$queued = is_wp_error($owned) ? $owned : new WP_Error('s2m_pay_pending_lost_owner', 'Cannot authorize an expired payment lease.');
							else
							{
								if(!isset($current['replay_origin']))
									$queued = new WP_Error('s2m_pay_pending_legacy_shadow', 'Untrusted payment origin cannot be authorized for replay.');
								else if(!isset($current['replay_authorized']))
								{
									$authorized = $current;
									$authorized['replay_authorized'] = TRUE;
									$authorized['updated'] = max(time(), $current['updated']);
									$stored = self::replace($name, $current, $authorized);
									if($stored !== TRUE)
										$queued = is_wp_error($stored) ? $stored : new WP_Error('s2m_pay_pending_raced', 'Payment receipt changed before replay authorization.');
								}
							}
						}
					}
				}
				if(is_wp_error($queued))
				{
					//261112.1116 A failed enqueue is retryable; it must never be marked as a completed payment.
					$failed = self::transition($name, $claim['token'], 'failed');
					$result = is_wp_error($failed) ? $failed : ($failed === TRUE ? $queued : new WP_Error('s2m_pay_pending_lost_owner', 'Could not persist the failed queue outcome.'));
				}
				else
				{
					//261112.1116 Record pending only after the separate replay row is durably written.
					$pending = self::transition($name, $claim['token'], 'pending_registration');
					$result = is_wp_error($pending) ? $pending : ($pending === TRUE
						? array('event' => $name, 'status' => 'pending_registration', 'created' => $queued['created'])
						: new WP_Error('s2m_pay_pending_lost_owner', 'Could not persist the pending payment outcome.'));
				}
			}
			finally
			{
				//261112.1116 Always release our own lease, including after a database exception during enqueue.
				$released = self::release($name, $claim['token']);
			}
			if(is_wp_error($released))
				return $released;
			if($released !== TRUE)
				return new WP_Error('s2m_pay_pending_lost_owner', 'Payment queue lease was lost.');
			return $result;
		}

		/**
		 * List a bounded batch of distinct registration payments in insert order.
		 *
		 * Entries are returned even if a receipt is already completed, so callers can
		 * explicitly acknowledge it; the queue is not itself proof of fulfillment.
		 *
		 * @since 261112.0600
		 *
		 * @param string $subscription Provider subscription ID, preserving case.
		 * @param int $limit Maximum number of entries, from 1 to 100.
		 *
		 * @return array|WP_Error Pending entries with `name`, `event`, `status`, and `ipn`, or WP_Error.
		 */
		public static function pending_for_subscription($subscription, $limit = 50)
		{
			global $wpdb;

			if(!self::valid_subscription($subscription) || !is_int($limit) || $limit < 1 || $limit > 100)
				return new WP_Error('s2m_pay_pending_invalid_query', 'Invalid pending payment query.');
			$prefix = 's2m_pay_pend_'.hash('sha256', $subscription).'_';
			$rows = $wpdb->get_results($wpdb->prepare("SELECT `option_name` FROM `{$wpdb->options}` WHERE `option_name` LIKE %s ORDER BY `option_id` ASC LIMIT %d", $wpdb->esc_like($prefix).'%', $limit), ARRAY_A);
			if($wpdb->last_error !== '' || !is_array($rows))
				return new WP_Error('s2m_pay_pending_database_error', 'Could not enumerate pending payments.');
			$entries = array();
			foreach($rows as $row)
			{
				$name = isset($row['option_name']) ? $row['option_name'] : '';
				if(!is_string($name) || !preg_match('/^s2m_pay_pend_[a-f0-9]{64}_[a-f0-9]{64}$/D', $name))
					return new WP_Error('s2m_pay_pending_invalid_record', 'Pending payment key has an unknown format.');
				$event = 's2m_pay_evt_'.substr($name, -64);
				$record = self::read_pending($name, $event, $subscription);
				if(is_wp_error($record) || $record === FALSE)
					return is_wp_error($record) ? $record : new WP_Error('s2m_pay_pending_lost', 'Pending payment disappeared while enumerating.');
				$receipt = self::read($event);
				if(is_wp_error($receipt) || $receipt === FALSE)
					return is_wp_error($receipt) ? $receipt : new WP_Error('s2m_pay_pending_missing', 'Pending payment has no durable receipt.');
				if(!isset($receipt['subscription_hash']) || !hash_equals($receipt['subscription_hash'], hash('sha256', $subscription)))
					return new WP_Error('s2m_pay_pending_subscription_mismatch', 'Pending payment is bound to another subscription.');
				//261112.1527 Old v5 shadow records have no authorization flag, even when a pending row is present.
				$entries[] = array('name' => $name, 'event' => $event, 'status' => $receipt['status'],
				                   'replay_authorized' => isset($receipt['replay_authorized']), 'ipn' => $record['ipn']);
			}
			return $entries;
		}

		/**
		 * Check whether a new, first-receipt payment is eligible for automated registration replay.
		 *
		 * This is a read-only prerequisite, NOT a processing claim or proof of fulfillment.
		 * Consumers must separately claim with ownership, checkpoint side effects, verify
		 * completion, and acknowledge only a completed receipt. Missing metadata on v5
		 * shadow receipts is an intentional permanent deny, not an upgrade opportunity.
		 *
		 * @since 261112.1527
		 *
		 * @param string $subscription Provider subscription ID, case-sensitive.
		 * @param string $event Durable event option name.
		 *
		 * @return bool|WP_Error TRUE only for an authorized, intact pending receipt; FALSE for ineligible state, or WP_Error.
		 */
		public static function replay_is_authorized($subscription, $event)
		{
			if(!self::valid_subscription($subscription) || !self::valid_name($event))
				return new WP_Error('s2m_pay_pending_invalid_query', 'Invalid replay eligibility query.');
			$receipt = self::read($event);
			if(is_wp_error($receipt) || $receipt === FALSE)
				return is_wp_error($receipt) ? $receipt : FALSE;
			if($receipt['status'] !== 'pending_registration' || !isset($receipt['replay_origin']) || !isset($receipt['replay_authorized']))
				return FALSE;
			if(!isset($receipt['subscription_hash']) || !hash_equals($receipt['subscription_hash'], hash('sha256', $subscription)))
				return new WP_Error('s2m_pay_pending_subscription_mismatch', 'Payment event belongs to another subscription.');
			$pending = self::read_pending(self::pending_name($subscription, $event), $event, $subscription);
			if(is_wp_error($pending))
				return $pending;
			return $pending !== FALSE ? TRUE : new WP_Error('s2m_pay_pending_missing', 'Authorized receipt has no matching replay record.');
		}

		/**
		 * Delete a pending replay only after its corresponding receipt is completed.
		 *
		 * @since 261112.0600
		 *
		 * @param string $subscription Provider subscription ID.
		 * @param string $event Receipt option name.
		 *
		 * @return bool|WP_Error TRUE when removed, FALSE when not completed or absent, or WP_Error.
		 */
		public static function acknowledge_pending($subscription, $event)
		{
			global $wpdb;

			if(!self::valid_subscription($subscription) || !self::valid_name($event))
				return new WP_Error('s2m_pay_pending_invalid_query', 'Invalid pending payment acknowledgement.');
			$receipt = self::read($event);
			if(is_wp_error($receipt) || $receipt === FALSE)
				return is_wp_error($receipt) ? $receipt : FALSE;
			if($receipt['status'] !== 'completed')
				return FALSE;
			if(!isset($receipt['subscription_hash']) || !hash_equals($receipt['subscription_hash'], hash('sha256', $subscription)))
				return new WP_Error('s2m_pay_pending_subscription_mismatch', 'Payment event belongs to another subscription.');

			$name = self::pending_name($subscription, $event);
			$previous = self::read_pending($name, $event, $subscription);
			if(is_wp_error($previous) || $previous === FALSE)
				return $previous;
			$old_value = wp_json_encode($previous);
			if(!is_string($old_value))
				return new WP_Error('s2m_pay_pending_encode_failed', 'Could not encode pending payment.');
			//261112.0600 Compare-and-delete only the matching replay, never a different event or concurrently changed payload.
			$deleted = $wpdb->query($wpdb->prepare("DELETE FROM `{$wpdb->options}` WHERE `option_name` = %s AND `option_value` = %s", $name, $old_value));
			if($deleted === FALSE || $wpdb->last_error !== '' || ($deleted !== 0 && $deleted !== 1))
				return new WP_Error('s2m_pay_pending_database_error', 'Could not acknowledge pending payment.');
			if($deleted === 1)
				self::clear_option_cache($name);
			return $deleted === 1;
		}

		/**
		 * Build a site-local queue index by subscription and immutable event identity.
		 *
		 * @since 261112.0600
		 *
		 * @param string $subscription Provider subscription ID.
		 * @param string $event Event receipt name.
		 *
		 * @return string Non-autoloaded option name.
		 */
		private static function pending_name($subscription, $event)
		{
			return 's2m_pay_pend_'.hash('sha256', $subscription).'_'.substr($event, -64);
		}

		/**
		 * Validate a case-sensitive provider subscription identifier.
		 *
		 * @since 261112.0600
		 *
		 * @param mixed $subscription Candidate subscription ID.
		 *
		 * @return bool Whether it can safely index pending payments.
		 */
		private static function valid_subscription($subscription)
		{
			return is_string($subscription) && $subscription !== '' && strlen($subscription) <= 255
			       && !preg_match('/[\x00-\x1f\x7f]/', $subscription);
		}

		/**
		 * Read one queued replay directly from SQL and reject malformed/stale records.
		 *
		 * @since 261112.0600
		 *
		 * @param string $name Queue option name.
		 * @param string $event Event option name.
		 * @param string $subscription Provider subscription ID.
		 *
		 * @return array|bool|WP_Error The queue record, FALSE if absent, or WP_Error.
		 */
		private static function read_pending($name, $event, $subscription)
		{
			global $wpdb;

			if(!self::valid_name($event) || $name !== self::pending_name($subscription, $event))
				return new WP_Error('s2m_pay_pending_invalid_name', 'Invalid pending payment key.');
			$value = $wpdb->get_var($wpdb->prepare("SELECT `option_value` FROM `{$wpdb->options}` WHERE `option_name` = %s LIMIT 1", $name));
			if($wpdb->last_error !== '')
				return new WP_Error('s2m_pay_pending_database_error', 'Could not read pending payment.');
			if($value === NULL)
				return FALSE;
			if(!is_string($value) || strlen($value) > 8192)
				return new WP_Error('s2m_pay_pending_invalid_record', 'Pending payment record exceeds its size limit.');
			$record = json_decode($value, TRUE);
			if(!is_array($record) || !isset($record['version'], $record['event'], $record['created'], $record['ipn'])
			   || $record['version'] !== 1 || $record['event'] !== $event || !is_int($record['created']) || $record['created'] < 1
			   || !is_array($record['ipn']) || !isset($record['ipn']['txn_type'], $record['ipn']['txn_id'], $record['ipn']['subscr_id'], $record['ipn']['subscr_gateway'])
			   || $record['ipn']['txn_type'] !== 'subscr_payment' || $record['ipn']['subscr_id'] !== $subscription
			   || !is_string($record['ipn']['txn_id']) || !is_string($record['ipn']['subscr_gateway'])
			   || $record['ipn']['txn_id'] === '' || $record['ipn']['subscr_gateway'] === '')
				return new WP_Error('s2m_pay_pending_invalid_record', 'Pending payment record has an unknown format.');
			foreach($record['ipn'] as $value)
				if(!is_string($value))
					return new WP_Error('s2m_pay_pending_invalid_record', 'Pending payment contains an unexpected field type.');
			return $record;
		}

		/**
		 * Invalidate WP option caches after a direct SQL modification.
		 *
		 * @since 261112.0600
		 *
		 * @param string $name Option name.
		 *
		 * @return void
		 */
		private static function clear_option_cache($name)
		{
			wp_cache_delete($name, 'options');
			$notoptions = wp_cache_get('notoptions', 'options');
			if(is_array($notoptions) && isset($notoptions[$name]))
			{
				unset($notoptions[$name]);
				wp_cache_set('notoptions', $notoptions, 'options');
			}
		}

		/**
		 * Update one event only if its complete prior database value still matches.
		 *
		 * @since 261112.0508
		 *
		 * @param string $name Payment event option name.
		 * @param array $previous Expected stored record.
		 * @param array $next Desired stored record.
		 *
		 * @return bool|WP_Error TRUE on update, FALSE on race, or WP_Error on query failure.
		 */
		private static function replace($name, $previous, $next)
		{
			global $wpdb;

			$old_value = wp_json_encode($previous);
			$new_value = wp_json_encode($next);
			if(!is_string($old_value) || !is_string($new_value))
				return new WP_Error('s2m_pay_event_encode_failed', 'Could not encode payment event.');

			//261112.0508 One SQL compare-and-swap prevents simultaneous workers from overwriting each other's status or owner.
			$changed = $wpdb->query($wpdb->prepare("UPDATE `{$wpdb->options}` SET `option_value` = %s WHERE `option_name` = %s AND `option_value` = %s", $new_value, $name, $old_value));
			if($changed === FALSE || $wpdb->last_error !== '')
				return new WP_Error('s2m_pay_event_database_error', 'Could not update payment event.');
			if($changed !== 0 && $changed !== 1)
				return new WP_Error('s2m_pay_event_database_error', 'Unexpected payment event update count.');
			if($changed === 1)
			{
				wp_cache_delete($name, 'options');
				$notoptions = wp_cache_get('notoptions', 'options');
				if(is_array($notoptions) && isset($notoptions[$name]))
				{
					unset($notoptions[$name]);
					wp_cache_set('notoptions', $notoptions, 'options');
				}
			}
			return $changed === 1;
		}

		/**
		 * Validate the local event-record option namespace.
		 *
		 * @since 261112.0508
		 *
		 * @param mixed $name Option name.
		 *
		 * @return bool Whether the name belongs to this store.
		 */
		private static function valid_name($name)
		{
			return is_string($name) && preg_match('/^s2m_pay_evt_[a-f0-9]{64}$/D', $name) === 1;
		}

		/**
		 * Derive the shared database resource from a validated event option name.
		 *
		 * @since 261112.0508
		 *
		 * @param string $name Validated event option name.
		 *
		 * @return string Namespaced lock resource.
		 */
		private static function lease_resource($name)
		{
			return 'payment-event/'.$name;
		}
	}
}
