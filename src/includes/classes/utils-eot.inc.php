<?php
/**
 * Access context shared by EOT and deletion notifications.
 *
 * @package s2Member\Utils_EOT
 * @since 261007
 */
if(!defined('WPINC'))
	exit('Do not access this file directly.');

if(!class_exists('c_ws_plugin__s2member_utils_eot'))
{
	/**
	 * Captures membership access before an EOT and formats the resulting change.
	 *
	 * @package s2Member\Utils_EOT
	 * @since 261007
	 */
	class c_ws_plugin__s2member_utils_eot
	{
		/**
		 * Captures the paid membership role and enabled custom capabilities.
		 *
		 * An unrelated WordPress role may precede the membership role. When multiple
		 * paid roles exist, use the first one, consistently with EOT role demotion.
		 *
		 * @param \WP_User|null $user User whose current access should be captured.
		 * @return array Previous role, membership level and sorted capability names.
		 */
		public static function snapshot($user = NULL)
		{
			$role = '';
			$ccaps = array();
			if($user instanceof WP_User && $user->ID)
			{
				$role = c_ws_plugin__s2member_user_access::user_access_role($user);
				foreach((array)$user->roles as $assigned_role)
					if(preg_match('/^s2member_level[1-9][0-9]*$/', $assigned_role))
					{
						$role = $assigned_role;
						break;
					}
				$ccaps = array_values(array_unique(c_ws_plugin__s2member_user_access::user_access_ccaps($user)));
				sort($ccaps, SORT_STRING);
			}
			$level = $role === 'subscriber' ? '0' : (preg_match('/^s2member_level([0-9]+)$/', $role, $matches) ? $matches[1] : '');
			return array('previous_role' => $role, 'previous_s2member_level' => $level, 'ccaps_before' => $ccaps);
		}

		/**
		 * Describes actual access changes after demotion or deletion.
		 *
		 * @param array $before Snapshot captured before the event changed access.
		 * @param \WP_User|null $user Surviving user; null means the account/site membership is being deleted.
		 * @return array Notification fields, with sorted comma-separated capability lists.
		 */
		public static function context($before, $user = NULL)
		{
			$after = self::snapshot($user);
			$before_ccaps = isset($before['ccaps_before']) ? (array)$before['ccaps_before'] : array();
			$remaining = $after['ccaps_before'];
			$removed = array_values(array_diff($before_ccaps, $remaining));
			sort($before_ccaps, SORT_STRING);
			sort($removed, SORT_STRING);
			return array(
				'previous_role' => isset($before['previous_role']) ? (string)$before['previous_role'] : '',
				'previous_s2member_level' => isset($before['previous_s2member_level']) ? (string)$before['previous_s2member_level'] : '',
				'ccaps_before' => implode(',', $before_ccaps),
				'ccaps_removed' => implode(',', $removed),
				'ccaps_remaining' => implode(',', $remaining),
			);
		}

		/**
		 * Replaces the access-context tokens before other notification substitutions.
		 *
		 * @param string $text Notification text or URL containing replacement codes.
		 * @param array $context Access fields returned by context().
		 * @param bool $is_url Whether values need URL encoding.
		 * @return string Text containing the supplied access values.
		 */
		public static function fill($text, $context, $is_url = FALSE)
		{
			return preg_replace_callback('/%%(previous_role|previous_s2member_level|ccaps_before|ccaps_removed|ccaps_remaining)%%/i', function($matches) use ($context, $is_url)
			{
				$key = strtolower($matches[1]);
				$value = isset($context[$key]) ? (string)$context[$key] : '';
				return $is_url ? urlencode($value) : $value;
			}, (string)$text);
		}

		/**
		 * Formats the access fields appended to the standard notification email.
		 *
		 * @param array $context Access fields returned by context().
		 * @return string Plain-text field lines, ending with a newline.
		 */
		public static function email_fields($context)
		{
			$text = '';
			foreach($context as $key => $value)
				$text .= $key.': '.($value === '' && strpos($key, 'ccaps_') === 0 ? '(none)' : $value)."\n";
			return $text;
		}
	}
}
