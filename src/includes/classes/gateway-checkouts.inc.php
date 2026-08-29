<?php
// @codingStandardsIgnoreFile
/**
 * Gateway Checkout state utilities.
 *
 * A Gateway Checkout represents one logical customer checkout that can create
 * either a one-time payment or a recurring subscription. Its server-side state preserves
 * the checkout identity and recovery data across reloads, retries, redirects, and lost
 * gateway responses, so the same checkout can be reconciled safely instead of duplicated.
 *
 * @package s2Member\Gateway_Checkouts
 * @since 260829.2325
 */
if(!defined('WPINC')) //260829.2325 MUST have WordPress.
	exit ('Do not access this file directly.');

if(!class_exists('c_ws_plugin__s2member_gateway_checkouts'))
{
	/**
	 * Gateway Checkout state utilities.
	 *
	 * @package s2Member\Gateway_Checkouts
	 * @since 260829.2325
	 */
	class c_ws_plugin__s2member_gateway_checkouts
	{
		/**
		 * Generates a Gateway Checkout ID.
		 *
		 * @package s2Member\Gateway_Checkouts
		 * @since 260829.2325
		 *
		 * @return string Gateway Checkout ID.
		 */
		public static function generate_id()
		{
			//260829.2325 Prefer WordPress UUIDs, while retaining a sufficiently unique fallback for older WordPress versions supported by s2Member.
			return function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : md5(uniqid('s2member-gateway-checkout-', TRUE).wp_rand());
		}

		/**
		 * Validates a Gateway Checkout ID.
		 *
		 * @package s2Member\Gateway_Checkouts
		 * @since 260829.2325
		 *
		 * @param string $gateway_checkout_id Gateway Checkout ID.
		 *
		 * @return bool TRUE if valid; else FALSE.
		 */
		public static function valid_id($gateway_checkout_id = '')
		{
			$gateway_checkout_id = (string)$gateway_checkout_id;

			return (bool)preg_match('/^(?:[a-f0-9]{32}|[a-f0-9]{8}-[a-f0-9]{4}-[1-5][a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12})$/i', $gateway_checkout_id);
		}

		/**
		 * Builds a deterministic fingerprint from Gateway Checkout purchase terms.
		 *
		 * @package s2Member\Gateway_Checkouts
		 * @since 260829.2325
		 *
		 * @param array $purchase_terms Purchase terms.
		 *
		 * @return string SHA-256 fingerprint.
		 */
		public static function purchase_fingerprint($purchase_terms = array())
		{
			//260829.2325 Sort associative purchase data recursively so equivalent server-side terms fingerprint identically regardless of insertion order.
			$purchase_terms = c_ws_plugin__s2member_utils_arrays::ksort_deep((array)$purchase_terms, SORT_STRING);

			return hash('sha256', serialize($purchase_terms));
		}

		/**
		 * Creates durable state for a new Gateway Checkout.
		 *
		 * @package s2Member\Gateway_Checkouts
		 * @since 260829.2325
		 *
		 * @param string  $gateway              Gateway identifier.
		 * @param string  $operation            Gateway operation identifier.
		 * @param string  $purchase_fingerprint Purchase fingerprint, if already known.
		 * @param integer $user_id              WordPress user ID, if already known.
		 * @param integer $ttl                  Optional state TTL in seconds.
		 *
		 * @return array|bool Gateway Checkout state, else FALSE.
		 */
		public static function create($gateway = '', $operation = '', $purchase_fingerprint = '', $user_id = 0, $ttl = 0)
		{
			$gateway   = sanitize_key((string)$gateway);
			$operation = sanitize_key((string)$operation);
			$user_id   = abs((int)$user_id);
			$ttl       = self::ttl($ttl);

			if(!$gateway || !$operation || !$ttl)
				return FALSE;

			$now = time();

			//260829.2325 Use add_option() so an extremely unlikely ID collision cannot overwrite another in-progress checkout.
			for($attempt = 0; $attempt < 3; $attempt++)
			{
				$gateway_checkout_id = self::generate_id();
				$option_name = self::option_name($gateway_checkout_id);
				if(!$option_name)
					continue;

				$state = array(
					'version'              => 1,
					'id'                   => $gateway_checkout_id,
					'gateway'              => $gateway,
					'operation'            => $operation,
					'purchase_fingerprint' => (string)$purchase_fingerprint,
					'user_id'              => $user_id,
					'gateway_ids'          => array(),
					'gateway_status'       => '',
					'context'              => array(),
					'fulfillment_status'   => 'pending',
					'created_at'           => $now,
					'updated_at'           => $now,
					'expires_at'           => $now + $ttl,
				);

				if(add_option($option_name, $state, '', 'no'))
					return $state;
			}
			return FALSE;
		}

		/**
		 * Gets durable Gateway Checkout state.
		 *
		 * @package s2Member\Gateway_Checkouts
		 * @since 260829.2325
		 *
		 * @param string $gateway_checkout_id Gateway Checkout ID.
		 * @param bool   $allow_expired       Optional. Return expired state instead of removing it.
		 *
		 * @return array|bool Gateway Checkout state, else FALSE.
		 */
		public static function get($gateway_checkout_id = '', $allow_expired = FALSE)
		{
			$option_name = self::option_name($gateway_checkout_id);
			if(!$option_name)
				return FALSE;

			$state = get_option($option_name, FALSE);
			if(!is_array($state) || empty($state['id']) || !hash_equals((string)$gateway_checkout_id, (string)$state['id']) || empty($state['expires_at']))
				return FALSE;

			if(!$allow_expired && (int)$state['expires_at'] <= time())
			{
				//260829.2325 Expired checkout state is unusable for recovery; remove it lazily when encountered.
				self::delete($gateway_checkout_id);
				return FALSE;
			}
			return $state;
		}

		/**
		 * Binds finalized purchase identity to a Gateway Checkout.
		 *
		 * @package s2Member\Gateway_Checkouts
		 * @since 260829.2325
		 *
		 * @param string  $gateway_checkout_id  Gateway Checkout ID.
		 * @param string  $purchase_fingerprint Finalized purchase fingerprint.
		 * @param integer $user_id              WordPress user ID, if known.
		 *
		 * @return array|bool Bound state, else FALSE on an identity mismatch.
		 */
		public static function bind_purchase($gateway_checkout_id = '', $purchase_fingerprint = '', $user_id = 0)
		{
			$state = self::get($gateway_checkout_id);
			$purchase_fingerprint = (string)$purchase_fingerprint;
			$user_id = abs((int)$user_id);

			if(!$state || !$purchase_fingerprint)
				return FALSE;

			//260829.2325 Once bound, a checkout cannot be reassigned to different purchase terms or a different known WordPress user.
			if(!empty($state['purchase_fingerprint']) && !hash_equals((string)$state['purchase_fingerprint'], $purchase_fingerprint))
				return FALSE;
			if(!empty($state['user_id']) && $user_id && (int)$state['user_id'] !== $user_id)
				return FALSE;

			$state['purchase_fingerprint'] = $purchase_fingerprint;
			if(!$state['user_id'] && $user_id)
				$state['user_id'] = $user_id;
			$state['updated_at'] = time();

			if(!update_option(self::option_name($gateway_checkout_id), $state, FALSE))
			{
				$persisted_state = self::get($gateway_checkout_id);
				if($persisted_state !== $state)
					return FALSE;
			}
			return $state;
		}

		/**
		 * Updates operational Gateway Checkout state.
		 *
		 * @package s2Member\Gateway_Checkouts
		 * @since 260829.2325
		 *
		 * @param string $gateway_checkout_id Gateway Checkout ID.
		 * @param array  $updates             Operational state values to update.
		 *
		 * @return array|bool Updated state, else FALSE.
		 */
		public static function update($gateway_checkout_id = '', $updates = array())
		{
			$state = self::get($gateway_checkout_id);
			if(!$state || !is_array($updates))
				return FALSE;

			//260829.2325 Only operational fields are mutable here; gateway/purchase/user identity is changed exclusively through create() and bind_purchase().
			$updates = array_intersect_key($updates, array('gateway_ids' => TRUE, 'gateway_status' => TRUE, 'fulfillment_status' => TRUE, 'context' => TRUE));
			$state = array_merge($state, $updates);
			$state['updated_at'] = time();

			if(!update_option(self::option_name($gateway_checkout_id), $state, FALSE))
			{
				//260829.2325 WordPress returns FALSE when an update makes no database change; return the persisted state if it already matches.
				$persisted_state = self::get($gateway_checkout_id);
				if($persisted_state !== $state)
					return FALSE;
			}
			return $state;
		}

		/**
		 * Deletes durable Gateway Checkout state and any associated lock.
		 *
		 * @package s2Member\Gateway_Checkouts
		 * @since 260829.2325
		 *
		 * @param string $gateway_checkout_id Gateway Checkout ID.
		 *
		 * @return bool TRUE if state was deleted; else FALSE.
		 */
		public static function delete($gateway_checkout_id = '')
		{
			$option_name = self::option_name($gateway_checkout_id);
			if(!$option_name)
				return FALSE;

			delete_option(self::lock_option_name($gateway_checkout_id));

			return delete_option($option_name);
		}

		/**
		 * Creates a signed browser token for a Gateway Checkout.
		 *
		 * @package s2Member\Gateway_Checkouts
		 * @since 260829.2325
		 *
		 * @param string $gateway_checkout_id Gateway Checkout ID.
		 *
		 * @return string Signed browser token, else an empty string.
		 */
		public static function browser_token($gateway_checkout_id = '')
		{
			$state = self::get($gateway_checkout_id);
			if(!$state)
				return '';

			$expires = (int)$state['expires_at'];
			$payload = (string)$gateway_checkout_id.'|'.$expires;
			$key = hash_hmac('sha256', 's2member:gateway-checkout:browser-token', c_ws_plugin__s2member_utils_encryption::key());
			$signature = hash_hmac('sha256', $payload, $key);

			return $expires.'.'.$signature;
		}

		/**
		 * Verifies a signed Gateway Checkout browser token.
		 *
		 * @package s2Member\Gateway_Checkouts
		 * @since 260829.2325
		 *
		 * @param string $gateway_checkout_id Gateway Checkout ID.
		 * @param string $browser_token       Signed browser token.
		 *
		 * @return bool TRUE if valid; else FALSE.
		 */
		public static function browser_token_verify($gateway_checkout_id = '', $browser_token = '')
		{
			$state = self::get($gateway_checkout_id);
			if(!$state || !is_string($browser_token) || !preg_match('/^([0-9]+)\.([a-f0-9]{64})$/i', $browser_token, $matches))
				return FALSE;

			$expires = (int)$matches[1];
			if($expires <= time() || $expires !== (int)$state['expires_at'])
				return FALSE;

			$payload = (string)$gateway_checkout_id.'|'.$expires;
			$key = hash_hmac('sha256', 's2member:gateway-checkout:browser-token', c_ws_plugin__s2member_utils_encryption::key());
			$expected = hash_hmac('sha256', $payload, $key);

			return hash_equals($expected, strtolower($matches[2]));
		}

		/**
		 * Acquires an atomic short-lived lock for a Gateway Checkout.
		 *
		 * @package s2Member\Gateway_Checkouts
		 * @since 260829.2325
		 *
		 * @param string  $gateway_checkout_id Gateway Checkout ID.
		 * @param integer $lock_timeout        Optional. Stale lock timeout in seconds.
		 *
		 * @return string|bool Lock token if acquired; else FALSE.
		 */
		public static function lock_acquire($gateway_checkout_id = '', $lock_timeout = 120)
		{
			$lock_option = self::lock_option_name($gateway_checkout_id);
			if(!$lock_option || !self::get($gateway_checkout_id))
				return FALSE;

			$lock_timeout = max(5, abs((int)$lock_timeout));
			$lock_token = self::generate_id();
			$lock = array('token' => $lock_token, 'acquired_at' => time());

			if(add_option($lock_option, $lock, '', 'no'))
				return $lock_token;

			$existing_lock = get_option($lock_option, FALSE);
			if(!is_array($existing_lock) || empty($existing_lock['token']) || empty($existing_lock['acquired_at']) || time() - (int)$existing_lock['acquired_at'] >= $lock_timeout)
			{
				//260829.2325 Delete malformed/stale locks, then rely on atomic add_option() so only one racing request can acquire the replacement.
				delete_option($lock_option);
				if(add_option($lock_option, $lock, '', 'no'))
					return $lock_token;
			}
			return FALSE;
		}

		/**
		 * Releases a Gateway Checkout lock owned by the supplied token.
		 *
		 * @package s2Member\Gateway_Checkouts
		 * @since 260829.2325
		 *
		 * @param string $gateway_checkout_id Gateway Checkout ID.
		 * @param string $lock_token          Lock token returned by lock_acquire().
		 *
		 * @return bool TRUE if released; else FALSE.
		 */
		public static function lock_release($gateway_checkout_id = '', $lock_token = '')
		{
			$lock_option = self::lock_option_name($gateway_checkout_id);
			if(!$lock_option || !$lock_token)
				return FALSE;

			$existing_lock = get_option($lock_option, FALSE);
			if(!is_array($existing_lock) || empty($existing_lock['token']) || !hash_equals((string)$existing_lock['token'], (string)$lock_token))
				return FALSE;

			return delete_option($lock_option);
		}

		/**
		 * Removes a bounded batch of expired Gateway Checkout states.
		 *
		 * @package s2Member\Gateway_Checkouts
		 * @since 260829.2325
		 *
		 * @param integer $limit Maximum candidate options to inspect.
		 *
		 * @return integer Number of expired/malformed state options removed.
		 */
		public static function cleanup_expired($limit = 50)
		{
			global $wpdb;

			$limit = min(500, max(1, abs((int)$limit)));
			$option_prefix = self::option_prefix();
			$option_names = $wpdb->get_col($wpdb->prepare("SELECT `option_name` FROM `{$wpdb->options}` WHERE `option_name` LIKE %s ORDER BY `option_id` ASC LIMIT %d", $wpdb->esc_like($option_prefix).'%', $limit));
			$removed = 0;

			foreach((array)$option_names as $option_name)
			{
				$gateway_checkout_id = substr((string)$option_name, strlen($option_prefix));
				if(!self::valid_id($gateway_checkout_id))
					continue;

				$state = get_option($option_name, FALSE);
				if(!is_array($state) || empty($state['expires_at']) || (int)$state['expires_at'] <= time())
				{
					if(self::delete($gateway_checkout_id))
						$removed++;
				}
			}
			return $removed;
		}

		/**
		 * Gets the configured Gateway Checkout state TTL.
		 *
		 * @package s2Member\Gateway_Checkouts
		 * @since 260829.2325
		 *
		 * @param integer $ttl Optional explicit TTL in seconds.
		 *
		 * @return integer TTL in seconds.
		 */
		public static function ttl($ttl = 0)
		{
			if((int)$ttl > 0)
				return abs((int)$ttl);

			//260829.2325 Keep recovery state longer than a typical 24-hour gateway idempotency window; sites can tune this without changing the storage contract.
			return max(HOUR_IN_SECONDS, abs((int)apply_filters('ws_plugin__s2member_gateway_checkout_ttl', 2 * DAY_IN_SECONDS)));
		}

		/**
		 * Builds the option name for Gateway Checkout state.
		 *
		 * @package s2Member\Gateway_Checkouts
		 * @since 260829.2325
		 *
		 * @param string $gateway_checkout_id Gateway Checkout ID.
		 *
		 * @return string Option name, else an empty string.
		 */
		public static function option_name($gateway_checkout_id = '')
		{
			return self::valid_id($gateway_checkout_id) ? self::option_prefix().$gateway_checkout_id : '';
		}

		/**
		 * Builds the option name for a Gateway Checkout lock.
		 *
		 * @package s2Member\Gateway_Checkouts
		 * @since 260829.2325
		 *
		 * @param string $gateway_checkout_id Gateway Checkout ID.
		 *
		 * @return string Option name, else an empty string.
		 */
		public static function lock_option_name($gateway_checkout_id = '')
		{
			return self::valid_id($gateway_checkout_id) ? 'ws_plugin__s2member_gateway_checkout_lock_'.$gateway_checkout_id : '';
		}

		/**
		 * Gets the Gateway Checkout state option prefix.
		 *
		 * @package s2Member\Gateway_Checkouts
		 * @since 260829.2325
		 *
		 * @return string Option prefix.
		 */
		protected static function option_prefix()
		{
			return 'ws_plugin__s2member_gateway_checkout_';
		}
	}
}
