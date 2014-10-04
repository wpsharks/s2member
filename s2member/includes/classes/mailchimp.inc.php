<?php
/**
 * MailChimp
 *
 * Copyright: © 2009-2011
 * {@link http://www.websharks-inc.com/ WebSharks, Inc.}
 * (coded in the USA)
 *
 * Released under the terms of the GNU General Public License.
 * You should have received a copy of the GNU General Public License,
 * along with this software. In the main directory, see: /licensing/
 * If not, see: {@link http://www.gnu.org/licenses/}.
 *
 * @since 141004
 * @package s2Member\List_Servers
 */
if(realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME']))
	exit('Do not access this file directly.');

if(!class_exists('c_ws_plugin__s2member_mailchimp'))
{
	/**
	 * MailChimp
	 *
	 * @since 141004
	 * @package s2Member\List_Servers
	 */
	class c_ws_plugin__s2member_mailchimp
	{
		/**
		 * Subscribe.
		 *
		 * @since 141004
		 * @package s2Member\List_Servers
		 *
		 * @param array $args Input arguments.
		 *
		 * @return bool True if successful.
		 */
		public static function subscribe($args)
		{
			$defaults = array(
				'role'          => '',
				'level'         => '',
				'login'         => '',
				'pass'          => '',
				'email'         => '',
				'fname'         => '',
				'lname'         => '',
				'ip'            => '',
				'opt_in'        => FALSE,
				'double_opt_in' => FALSE,
				'user_id'       => 0
			);
		}

		/**
		 * Unsubscribe.
		 *
		 * @since 141004
		 * @package s2Member\List_Servers
		 *
		 * @param array $args Input arguments.
		 *
		 * @return bool True if successful.
		 */
		public static function unsubscribe($args)
		{
			$defaults = array(
				'role'    => '',
				'level'   => '',
				'login'   => '',
				'pass'    => '',
				'email'   => '',
				'fname'   => '',
				'lname'   => '',
				'ip'      => '',
				'opt_out' => FALSE,
				'user_id' => 0
			);
		}

		/**
		 * Transition.
		 *
		 * @since 141004
		 * @package s2Member\List_Servers
		 *
		 * @param array $old_args Input arguments.
		 * @param array $new_args Input arguments.
		 *
		 * @return bool True if successful.
		 */
		public static function transition($old_args, $new_args)
		{
			$defaults = array(
				'role'          => '',
				'level'         => '',
				'login'         => '',
				'pass'          => '',
				'email'         => '',
				'fname'         => '',
				'lname'         => '',
				'ip'            => '',
				'opt_in'        => FALSE,
				'double_opt_in' => FALSE,
				'user_id'       => 0
			);
		}
	}
}