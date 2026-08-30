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

			//260829.2325 Use add_option() so an extremely unlikely ID collision cannot overwrite another in-progress checkout.
			for($attempt = 0; $attempt < 3; $attempt++)
			{
				$state = self::create_with_id(self::generate_id(), $gateway, $operation, $purchase_fingerprint, $user_id, $ttl);
				if($state)
					return $state;
			}
			return FALSE;
		}

		/**
		 * Resumes a signed browser Gateway Checkout, or creates its durable state on first use.
		 *
		 * @package s2Member\Gateway_Checkouts
		 * @since 260830.0059
		 *
		 * @param string  $gateway              Gateway identifier.
		 * @param string  $operation            Gateway operation identifier.
		 * @param string  $gateway_checkout_id  Browser Gateway Checkout ID, if any.
		 * @param string  $browser_token        Signed browser token, if any.
		 * @param string  $purchase_fingerprint Finalized purchase fingerprint, if known.
		 * @param integer $user_id              Current WordPress user ID, if any.
		 * @param integer $ttl                  Optional state TTL in seconds when a replacement identity is needed.
		 *
		 * @return array|bool Gateway Checkout state, else FALSE.
		 */
		public static function create_or_resume($gateway = '', $operation = '', $gateway_checkout_id = '', $browser_token = '', $purchase_fingerprint = '', $user_id = 0, $ttl = 0)
		{
			$gateway              = sanitize_key((string)$gateway);
			$operation            = sanitize_key((string)$operation);
			$purchase_fingerprint = (string)$purchase_fingerprint;
			$user_id              = abs((int)$user_id);

			if(!$gateway || !$operation)
				return FALSE;

			if($gateway_checkout_id && $browser_token && self::browser_token_verify($gateway_checkout_id, $browser_token))
			{
				$browser_expires_at = self::browser_token_expires_at($browser_token);
				$state = self::get($gateway_checkout_id);

				if(!$state && $browser_expires_at > time())
				{
					//260830.0059 Form renders use a signed provisional identity without writing to the database; persist it only when checkout processing actually begins.
					$state = self::create_with_id($gateway_checkout_id, $gateway, $operation, $purchase_fingerprint, $user_id, 0, $browser_expires_at);
					if(!$state)
						$state = self::get($gateway_checkout_id); // Another concurrent request may have created the same signed checkout first.
				}
				if($state && (string)$state['gateway'] === $gateway && (string)$state['operation'] === $operation
				   && (empty($state['user_id']) || ($user_id && (int)$state['user_id'] === $user_id)))
				{
					if($purchase_fingerprint)
					{
						//260830.0308 Once bound, a checkout cannot be reassigned to different purchase terms or a different known WordPress user.
						if(!empty($state['purchase_fingerprint']) && !hash_equals((string)$state['purchase_fingerprint'], $purchase_fingerprint))
							return FALSE;

						$state['purchase_fingerprint'] = $purchase_fingerprint;
						if(!$state['user_id'] && $user_id)
							$state['user_id'] = $user_id;
						$state['updated_at'] = time();

						if(!update_option('ws_plugin__s2member_gateway_checkout_'.$gateway_checkout_id, $state, FALSE))
						{
							$persisted_state = self::get($gateway_checkout_id);
							if($persisted_state !== $state)
								return FALSE;
						}
					}
					return $state;
				}
			}
			return self::create($gateway, $operation, $purchase_fingerprint, $user_id, $ttl);
		}

		/**
		 * Creates or preserves a signed provisional browser identity without durable state.
		 *
		 * @package s2Member\Gateway_Checkouts
		 * @since 260830.0059
		 *
		 * @param string  $gateway_checkout_id Existing browser Gateway Checkout ID, if any.
		 * @param string  $browser_token       Existing signed browser token, if any.
		 * @param integer $ttl                 Optional token TTL in seconds.
		 *
		 * @return array Browser identity containing `id`, `token`, and `expires_at`.
		 */
		public static function browser_identity($gateway_checkout_id = '', $browser_token = '', $ttl = 0)
		{
			if($gateway_checkout_id && $browser_token && self::browser_token_verify($gateway_checkout_id, $browser_token))
				return array('id' => (string)$gateway_checkout_id, 'token' => (string)$browser_token, 'expires_at' => self::browser_token_expires_at($browser_token));

			$gateway_checkout_id = self::generate_id();
			$expires_at = time() + self::ttl($ttl);
			$browser_token = self::browser_token($gateway_checkout_id, $expires_at);

			return array('id' => $gateway_checkout_id, 'token' => $browser_token, 'expires_at' => $expires_at);
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
			if(!self::valid_id($gateway_checkout_id))
				return FALSE;

			$option_name = 'ws_plugin__s2member_gateway_checkout_'.$gateway_checkout_id;
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

			//260830.0408 Only operational fields are mutable here; gateway, purchase, and user identity are established when the checkout is created/resumed.
			$updates = array_intersect_key($updates, array('gateway_ids' => TRUE, 'gateway_status' => TRUE, 'fulfillment_status' => TRUE, 'context' => TRUE));
			$state = array_merge($state, $updates);
			$state['updated_at'] = time();

			if(!update_option('ws_plugin__s2member_gateway_checkout_'.$gateway_checkout_id, $state, FALSE))
			{
				//260829.2325 WordPress returns FALSE when an update makes no database change; return the persisted state if it already matches.
				$persisted_state = self::get($gateway_checkout_id);
				if($persisted_state !== $state)
					return FALSE;
			}
			return $state;
		}

		/**
		 * Acquires an atomic processing lock for a Gateway Checkout.
		 *
		 * @package s2Member\Gateway_Checkouts
		 * @since 260830.0408
		 *
		 * @param string  $gateway_checkout_id Gateway Checkout ID.
		 * @param integer $timeout             Optional stale-lock timeout in seconds.
		 *
		 * @return string|bool Lock token if acquired; else FALSE.
		 */
		public static function processing_lock($gateway_checkout_id = '', $timeout = 300)
		{
			if(!self::valid_id($gateway_checkout_id) || !self::get($gateway_checkout_id))
				return FALSE;

			$option_name = 's2m_gateway_checkout_lock_'.$gateway_checkout_id;
			$timeout = max(30, abs((int)$timeout));
			$token = self::generate_id();
			$lock = array('token' => $token, 'time' => time());

			if(add_option($option_name, $lock, '', 'no'))
				return $token;

			$existing = get_option($option_name, FALSE);
			if(!is_array($existing) || empty($existing['time']) || time() - (int)$existing['time'] >= $timeout)
			{
				//260830.0408 Replace malformed/stale locks atomically; only one racing request can win the new add_option().
				delete_option($option_name);
				if(add_option($option_name, $lock, '', 'no'))
					return $token;
			}
			return FALSE;
		}

		/**
		 * Releases a Gateway Checkout processing lock owned by the supplied token.
		 *
		 * @package s2Member\Gateway_Checkouts
		 * @since 260830.0408
		 *
		 * @param string $gateway_checkout_id Gateway Checkout ID.
		 * @param string $token               Lock token returned by processing_lock().
		 *
		 * @return bool TRUE if released; else FALSE.
		 */
		public static function processing_unlock($gateway_checkout_id = '', $token = '')
		{
			if(!self::valid_id($gateway_checkout_id) || !self::valid_id($token))
				return FALSE;

			$option_name = 's2m_gateway_checkout_lock_'.$gateway_checkout_id;
			$existing = get_option($option_name, FALSE);
			if(!is_array($existing) || empty($existing['token']) || !hash_equals((string)$existing['token'], (string)$token))
				return FALSE;

			return delete_option($option_name);
		}

		/**
		 * Deletes durable Gateway Checkout state.
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
			if(!self::valid_id($gateway_checkout_id))
				return FALSE;

			delete_option('s2m_gateway_checkout_lock_'.$gateway_checkout_id);

			return delete_option('ws_plugin__s2member_gateway_checkout_'.$gateway_checkout_id);
		}

		/**
		 * Creates a signed browser token for a Gateway Checkout identity.
		 *
		 * @package s2Member\Gateway_Checkouts
		 * @since 260829.2325
		 *
		 * @param string  $gateway_checkout_id Gateway Checkout ID.
		 * @param integer $expires_at          Optional absolute expiration time for a provisional identity.
		 *
		 * @return string Signed browser token, else an empty string.
		 */
		public static function browser_token($gateway_checkout_id = '', $expires_at = 0)
		{
			if(!self::valid_id($gateway_checkout_id))
				return '';

			if(!$expires_at)
			{
				$state = self::get($gateway_checkout_id);
				if(!$state)
					return '';

				$expires_at = (int)$state['expires_at'];
			}
			if((int)$expires_at <= time())
				return '';

			$payload = (string)$gateway_checkout_id.'|'.(int)$expires_at;
			$key = hash_hmac('sha256', 's2member:gateway-checkout:browser-token', c_ws_plugin__s2member_utils_encryption::key());
			$signature = hash_hmac('sha256', $payload, $key);

			return (int)$expires_at.'.'.$signature;
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
			if(!self::valid_id($gateway_checkout_id) || !is_string($browser_token) || !preg_match('/^([0-9]+)\.([a-f0-9]{64})$/i', $browser_token, $matches))
				return FALSE;

			$expires_at = (int)$matches[1];
			if($expires_at <= time())
				return FALSE;

			$payload = (string)$gateway_checkout_id.'|'.$expires_at;
			$key = hash_hmac('sha256', 's2member:gateway-checkout:browser-token', c_ws_plugin__s2member_utils_encryption::key());
			$expected = hash_hmac('sha256', $payload, $key);
			if(!hash_equals($expected, strtolower($matches[2])))
				return FALSE;

			$state = self::get($gateway_checkout_id);
			//260830.0059 A provisional browser identity has no state yet; once state exists, its expiration must remain bound to the signed token.
			return !$state || $expires_at === (int)$state['expires_at'];
		}

		/**
		 * Gets the expiration time encoded in a syntactically valid browser token.
		 *
		 * @package s2Member\Gateway_Checkouts
		 * @since 260830.0059
		 *
		 * @param string $browser_token Signed browser token.
		 *
		 * @return integer Absolute expiration time, else 0.
		 */
		protected static function browser_token_expires_at($browser_token = '')
		{
			return is_string($browser_token) && preg_match('/^([0-9]+)\.[a-f0-9]{64}$/i', $browser_token, $matches) ? (int)$matches[1] : 0;
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
			$option_prefix = 'ws_plugin__s2member_gateway_checkout_';
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
		 * Creates durable Gateway Checkout state with a specific signed identity.
		 *
		 * @package s2Member\Gateway_Checkouts
		 * @since 260830.0059
		 *
		 * @param string  $gateway_checkout_id  Gateway Checkout ID.
		 * @param string  $gateway              Gateway identifier.
		 * @param string  $operation            Gateway operation identifier.
		 * @param string  $purchase_fingerprint Purchase fingerprint, if already known.
		 * @param integer $user_id              WordPress user ID, if already known.
		 * @param integer $ttl                  State TTL in seconds.
		 * @param integer $expires_at           Optional absolute expiration time; used by signed provisional browser identities.
		 *
		 * @return array|bool Gateway Checkout state, else FALSE.
		 */
		protected static function create_with_id($gateway_checkout_id = '', $gateway = '', $operation = '', $purchase_fingerprint = '', $user_id = 0, $ttl = 0, $expires_at = 0)
		{
			$gateway     = sanitize_key((string)$gateway);
			$operation   = sanitize_key((string)$operation);
			$user_id     = abs((int)$user_id);
			$expires_at  = abs((int)$expires_at);
			$ttl         = $expires_at ? 0 : self::ttl($ttl);

			if(!self::valid_id($gateway_checkout_id) || !$gateway || !$operation || (!$ttl && $expires_at <= time()))
				return FALSE;

			$option_name = 'ws_plugin__s2member_gateway_checkout_'.$gateway_checkout_id;

			$now = time();
			$expires_at = $expires_at ?: $now + $ttl;
			$state = array(
				'version'              => 1,
				'id'                   => (string)$gateway_checkout_id,
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
				'expires_at'           => $expires_at,
			);

			if(!add_option($option_name, $state, '', 'no'))
				return FALSE;

			//260830.0135 Opportunistically prune a bounded batch so expired Gateway Checkouts do not accumulate on sites without adding another scheduled task.
			if(wp_rand(1, 100) === 1)
				self::cleanup_expired(50);

			return $state;
		}
	}
}
