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
			   || $record['created'] < 1 || $record['updated'] < $record['created'] || $record['attempts'] < 0)
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
	}
}
