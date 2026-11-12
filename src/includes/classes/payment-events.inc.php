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
			   || ($record['status'] !== 'processing' && array_key_exists('owner', $record)))
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
		 *
		 * @return array|WP_Error Array with `created` flag and `record`, or WP_Error.
		 */
		public static function receive($gateway, $merchant, $transaction, $event)
		{
			global $wpdb;

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
