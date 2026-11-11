<?php
// @codingStandardsIgnoreFile
/**
* Database routines.
*
* Copyright: © 2009-2011
* {@link http://websharks-inc.com/ WebSharks, Inc.}
* (coded in the USA)
*
* Released under the terms of the GNU General Public License.
* You should have received a copy of the GNU General Public License,
* along with this software. In the main directory, see: /licensing/
* If not, see: {@link http://www.gnu.org/licenses/}.
*
* @package s2Member\Database
* @since 130625
*/
if(!defined('WPINC')) // MUST have WordPress.
	exit("Do not access this file directly.");

if(!class_exists('c_ws_plugin__s2member_database'))
{
	/**
	 * Database routines and site-scoped coordination primitives.
	 *
	 * @package s2Member\Database
	 * @since 130625
	 */
	class c_ws_plugin__s2member_database
	{
		/**
		 * Increase the database session timeout for s2Member requests.
		 *
		 * @package s2Member\Database
		 * @since 130625
		 *
		 * @attaches-to ``add_action("init");``
		 *
		 * @return void
		 */
		public static function wait_timeout()
		{
			global $wpdb; // Global database object reference.

			if(c_ws_plugin__s2member_systematics::is_s2_systematic_use_page()
				|| (!empty($_SERVER['QUERY_STRING']) && preg_match('/[\?&]s2member/', $_SERVER['QUERY_STRING'])))
				$increase_wait_timeout = TRUE;

			if(empty($increase_wait_timeout) && !empty($_POST)) foreach(array_keys($_POST) as $post_key)
				if(strpos($post_key, 's2member') === 0) { $increase_wait_timeout = TRUE; break; }

			if(!empty($increase_wait_timeout)) $wpdb->query('SET SESSION `wait_timeout` = 300');
		}

		/**
		 * Acquire a short, site-scoped database lease without overwriting another worker's lock.
		 *
		 * Only a matching owner token may renew or release this lease. Expiry permits recovery after
		 * worker failure, but is not a fencing token and does not guarantee exactly-once side effects.
		 * Callers must check WP_Error separately: it is a truthy object, not a successful token.
		 *
		 * @since 261111.0240
		 *
		 * @param string $resource Unique caller-namespaced resource identity; never an option name.
		 * @param int    $lease_seconds Lease duration, from 1 to 86400 seconds.
		 *
		 * @return string|bool|WP_Error Owner token, FALSE on contention, or WP_Error on failure.
		 */
		public static function lock_acquire($resource, $lease_seconds = 300)
		{
			global $wpdb;

			$option = self::lock_option_name($resource);
			if($option === FALSE || !self::lock_valid_lease($lease_seconds))
				return new WP_Error('s2member_lock_invalid_input', 'Invalid database lock resource or lease.');

			//261111.0240 Secure, lowercase hexadecimal tokens avoid case-insensitive option-value comparisons confusing owners.
			$token = self::lock_token_new();
			if($token === FALSE)
				return new WP_Error('s2member_lock_random_failure', 'Secure randomness is unavailable for database lock ownership.');
			$value = (time() + (int)$lease_seconds).':'.$token;
			//261111.0240 A non-overwriting INSERT relies on the unique option_name index, not WordPress add_option() cache checks.
			$insert = $wpdb->query($wpdb->prepare("INSERT IGNORE INTO `{$wpdb->options}` (`option_name`, `option_value`, `autoload`) VALUES (%s, %s, %s)", $option, $value, 'no'));
			if($insert === FALSE || $wpdb->last_error !== '')
				return new WP_Error('s2member_lock_database_error', 'Could not acquire database lock.');
			if($insert === 1)
			{
				self::lock_cache_clear($option);
				return $token;
			}
			if($insert !== 0)
				return new WP_Error('s2member_lock_database_error', 'Unexpected database lock insertion result.');

			$previous = self::lock_read($option);
			if(is_wp_error($previous))
				return $previous;
			if($previous === FALSE)
				return FALSE; // Another request may have released the lock between our INSERT and SELECT.

			$parts = self::lock_parse($previous);
			if($parts === FALSE)
				return new WP_Error('s2member_lock_invalid_record', 'Database lock record has an unknown format.');
			if($parts['expires'] > time())
				return FALSE;

			//261111.0240 Compare-and-swap an expired value; never delete it first or steal a lock renewed by another worker.
			$changed = $wpdb->query($wpdb->prepare("UPDATE `{$wpdb->options}` SET `option_value` = %s WHERE `option_name` = %s AND `option_value` = %s", $value, $option, $previous));
			if($changed === FALSE || $wpdb->last_error !== '')
				return new WP_Error('s2member_lock_database_error', 'Could not recover expired database lock.');
			if($changed === 1)
			{
				self::lock_cache_clear($option);
				return $token;
			}
			return FALSE;
		}

		/**
		 * Renew a still-active database lease held by this token.
		 *
		 * A worker whose lease has already expired must stop; it cannot revive its former ownership.
		 * The caller remains responsible for checking ownership around non-transactional side effects.
		 *
		 * @since 261111.0240
		 *
		 * @param string $resource Caller-namespaced resource identity.
		 * @param string $token Token returned by lock_acquire().
		 * @param int    $lease_seconds New lease duration, from 1 to 86400 seconds.
		 *
		 * @return bool|WP_Error TRUE on renewal, FALSE if not owned, or WP_Error on failure.
		 */
		public static function lock_renew($resource, $token, $lease_seconds = 300)
		{
			global $wpdb;

			$option = self::lock_option_name($resource);
			if($option === FALSE || !self::lock_valid_token($token) || !self::lock_valid_lease($lease_seconds))
				return new WP_Error('s2member_lock_invalid_input', 'Invalid database lock resource, owner, or lease.');

			$previous = self::lock_read($option);
			if(is_wp_error($previous) || $previous === FALSE)
				return $previous;
			$parts = self::lock_parse($previous);
			if($parts === FALSE)
				return new WP_Error('s2member_lock_invalid_record', 'Database lock record has an unknown format.');
			if(!hash_equals($parts['token'], $token) || $parts['expires'] <= time())
				return FALSE;

			//261111.0240 Guarantee a changed value even when renewed twice within one second; compare-and-swap rejects stale updates.
			$new_expiry = max($parts['expires'] + 1, time() + (int)$lease_seconds);
			$changed = $wpdb->query($wpdb->prepare("UPDATE `{$wpdb->options}` SET `option_value` = %s WHERE `option_name` = %s AND `option_value` = %s", $new_expiry.':'.$token, $option, $previous));
			if($changed === FALSE || $wpdb->last_error !== '')
				return new WP_Error('s2member_lock_database_error', 'Could not renew database lock.');
			if($changed === 1)
				self::lock_cache_clear($option);
			return $changed === 1;
		}

		/**
		 * Check an owner's current, unexpired lease directly against the database.
		 *
		 * This is only a point-in-time check; a subsequent operation may outlive the lease.
		 *
		 * @since 261111.0240
		 *
		 * @param string $resource Caller-namespaced resource identity.
		 * @param string $token Owner token returned by lock_acquire().
		 *
		 * @return bool|WP_Error TRUE while owned, FALSE otherwise, or WP_Error on failure.
		 */
		public static function lock_is_owned($resource, $token)
		{
			$option = self::lock_option_name($resource);
			if($option === FALSE || !self::lock_valid_token($token))
				return new WP_Error('s2member_lock_invalid_input', 'Invalid database lock resource or owner.');

			$previous = self::lock_read($option);
			if(is_wp_error($previous) || $previous === FALSE)
				return $previous;
			$parts = self::lock_parse($previous);
			if($parts === FALSE)
				return new WP_Error('s2member_lock_invalid_record', 'Database lock record has an unknown format.');
			return $parts['expires'] > time() && hash_equals($parts['token'], $token);
		}

		/**
		 * Release a database lease only if its exact owner and stored version still match.
		 *
		 * Releasing a superseded or expired owner's previous lease cannot remove a newer lock.
		 *
		 * @since 261111.0240
		 *
		 * @param string $resource Caller-namespaced resource identity.
		 * @param string $token Owner token returned by lock_acquire().
		 *
		 * @return bool|WP_Error TRUE if removed, FALSE if not owned, or WP_Error on failure.
		 */
		public static function lock_release($resource, $token)
		{
			global $wpdb;

			$option = self::lock_option_name($resource);
			if($option === FALSE || !self::lock_valid_token($token))
				return new WP_Error('s2member_lock_invalid_input', 'Invalid database lock resource or owner.');

			$previous = self::lock_read($option);
			if(is_wp_error($previous) || $previous === FALSE)
				return $previous;
			$parts = self::lock_parse($previous);
			if($parts === FALSE)
				return new WP_Error('s2member_lock_invalid_record', 'Database lock record has an unknown format.');
			if(!hash_equals($parts['token'], $token))
				return FALSE;

			//261111.0240 Match the full recorded version: late release must not delete a renewed or replaced lock.
			$removed = $wpdb->delete($wpdb->options, array('option_name' => $option, 'option_value' => $previous), array('%s', '%s'));
			if($removed === FALSE || $wpdb->last_error !== '')
				return new WP_Error('s2member_lock_database_error', 'Could not release database lock.');
			if($removed === 1)
				self::lock_cache_clear($option);
			return $removed === 1;
		}

		/**
		 * Derive a private, WordPress-option-safe lock name without exposing caller resource identifiers.
		 *
		 * @since 261111.0240
		 *
		 * @param string $resource Caller-namespaced resource identity.
		 *
		 * @return string|bool Hashed option name, or FALSE for invalid resources.
		 */
		private static function lock_option_name($resource)
		{
			return (is_string($resource) && $resource !== '' && strlen($resource) <= 512)
				? 'ws_plugin__s2member_db_lock_'.hash('sha256', $resource) : FALSE;
		}

		/**
		 * Validate an integer lease duration.
		 *
		 * @since 261111.0240
		 *
		 * @param mixed $seconds Lease duration.
		 *
		 * @return bool Whether the requested lease is supported.
		 */
		private static function lock_valid_lease($seconds)
		{
			return is_int($seconds) && $seconds >= 1 && $seconds <= 86400;
		}

		/**
		 * Validate a cryptographically generated internal lock token.
		 *
		 * @since 261111.0240
		 *
		 * @param mixed $token Lock token.
		 *
		 * @return bool Whether the token has the expected format.
		 */
		private static function lock_valid_token($token)
		{
			return is_string($token) && preg_match('/^[a-f0-9]{64}$/D', $token) === 1;
		}

		/**
		 * Generate a 256-bit token using system cryptographic randomness, including on PHP 5.6.
		 *
		 * @since 261111.0240
		 *
		 * @return string|bool Lowercase hexadecimal token, or FALSE when strong randomness is unavailable.
		 */
		private static function lock_token_new()
		{
			if(function_exists('random_bytes'))
			{
				try { $bytes = random_bytes(32); }
				catch(Exception $exception) { return FALSE; }
			}
			else if(function_exists('openssl_random_pseudo_bytes'))
			{
				//261111.0240 Older supported PHP versions lack random_bytes(); OpenSSL must report strong entropy.
				$strong = FALSE;
				$bytes = openssl_random_pseudo_bytes(32, $strong);
				if(!$strong)
					return FALSE;
			}
			else return FALSE;

			return is_string($bytes) && strlen($bytes) === 32 ? bin2hex($bytes) : FALSE;
		}

		/**
		 * Parse one internal expiry-and-owner record without accepting unknown lock formats.
		 *
		 * @since 261111.0240
		 *
		 * @param mixed $value Stored record.
		 *
		 * @return array|bool Expiry and token, or FALSE for malformed records.
		 */
		private static function lock_parse($value)
		{
			if(!is_string($value) || !preg_match('/^([1-9][0-9]{0,12}):([a-f0-9]{64})$/D', $value, $parts))
				return FALSE;
			return array('expires' => (int)$parts[1], 'token' => $parts[2]);
		}

		/**
		 * Read the live option value without relying on an object-cache snapshot.
		 *
		 * @since 261111.0240
		 *
		 * @param string $option Internal lock option name.
		 *
		 * @return string|bool|WP_Error Raw value, FALSE if absent, or WP_Error on DB failure.
		 */
		private static function lock_read($option)
		{
			global $wpdb;

			$value = $wpdb->get_var($wpdb->prepare("SELECT `option_value` FROM `{$wpdb->options}` WHERE `option_name` = %s LIMIT 1", $option));
			if($wpdb->last_error !== '')
				return new WP_Error('s2member_lock_database_error', 'Could not read database lock.');
			return $value === NULL ? FALSE : (string)$value;
		}

		/**
		 * Invalidate cached option values following direct SQL mutations.
		 *
		 * The lock API itself never uses get_option(), and no caller should use it for lock authority.
		 *
		 * @since 261111.0240
		 *
		 * @param string $option Internal lock option name.
		 *
		 * @return void
		 */
		private static function lock_cache_clear($option)
		{
			wp_cache_delete($option, 'options');
		}
	}
}
