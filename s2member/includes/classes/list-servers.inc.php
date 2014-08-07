<?php
/**
 * List Server integrations.
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
 * @package s2Member\List_Servers
 * @since 3.5
 */
if(realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME']))
	exit('Do not access this file directly.');

if(!class_exists('c_ws_plugin__s2member_list_servers'))
{
	/**
	 * List Server integrations.
	 *
	 * @package s2Member\List_Servers
	 * @since 3.5
	 */
	class c_ws_plugin__s2member_list_servers
	{
		/**
		 * Determines whether or not any List Servers have been integrated.
		 *
		 * @package s2Member\List_Servers
		 * @since 3.5
		 *
		 * @return bool True if List Servers have been integrated, else false.
		 */
		public static function list_servers_integrated()
		{
			do_action('ws_plugin__s2member_before_list_servers_integrated', get_defined_vars());

			for($n = 0; $n <= $GLOBALS['WS_PLUGIN__']['s2member']['c']['levels']; $n++ /* Go through each Level; looking for a configured list. */)
				if(!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['level'.$n.'_mailchimp_list_ids']) || !empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['level'.$n.'_getresponse_list_ids']) || !empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['level'.$n.'_aweber_list_ids']))
					return apply_filters('ws_plugin__s2member_list_servers_integrated', TRUE, get_defined_vars());

			return apply_filters('ws_plugin__s2member_list_servers_integrated', FALSE, get_defined_vars());
		}

		/**
		 * Processes List Server integrations for s2Member.
		 *
		 * @package s2Member\List_Servers
		 * @since 3.5
		 *
		 * @param string     $role A WordPress Role ID/Name, such as `subscriber`, or `s2member_level1`.
		 * @param int|string $level A numeric s2Member Access Level number.
		 * @param string     $login Username for the User.
		 * @param string     $pass Plain Text Password for the User.
		 * @param string     $email Email Address for the User.
		 * @param string     $fname First Name for the User.
		 * @param string     $lname Last Name for the User.
		 * @param string     $ip IP Address for the User.
		 * @param bool       $opt_in Defaults to false; must be set to true. Indicates the User IS opting in.
		 * @param bool       $double_opt_in Defaults to true. If false, no email confirmation is required. Use at your own risk.
		 * @param int|string $user_id A WordPress User ID, numeric string or integer.
		 *
		 * @return bool True if at least one List Server is processed successfully, else false.
		 *
		 * @todo Integrate {@link https://labs.aweber.com/docs/php-library-walkthrough AWeber's API}.
		 * @todo Add a separate option for mail debugging; or consolidate?
		 * @todo Integrate AWeber API (much like the MailChimp API).
		 */
		public static function process_list_servers($role = '', $level = '', $login = '', $pass = '', $email = '', $fname = '', $lname = '', $ip = '', $opt_in = FALSE, $double_opt_in = TRUE, $user_id = 0)
		{
			global $current_site, $current_blog; // For Multisite support.

			foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
			do_action('ws_plugin__s2member_before_process_list_servers', get_defined_vars());
			unset($__refs, $__v);

			if(c_ws_plugin__s2member_list_servers::list_servers_integrated() && ($args = func_get_args()) && $role && is_string($role) && is_numeric($level) && $login && is_string($login) && is_string($pass = (string)$pass) && $email && is_string($email) && is_email($email) && is_string($fname = (string)$fname) && is_string($lname = (string)$lname) && is_string($ip = (string)$ip) && is_bool($opt_in = (bool)$opt_in) && $opt_in && is_bool($double_opt_in = (bool)$double_opt_in) && $user_id && is_numeric($user_id) && is_object($user = new WP_User ($user_id)) && !empty($user->ID))
			{
				$ccaps = implode(',', c_ws_plugin__s2member_user_access::user_access_ccaps($user));

				$email_configs_were_on = c_ws_plugin__s2member_email_configs::email_config_status();
				c_ws_plugin__s2member_email_configs::email_config_release( /* Release s2Member Filters before we begin this routine. */);

				if(!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['mailchimp_api_key']) && !empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['level'.$level.'_mailchimp_list_ids']))
				{
					if(!class_exists('NC_MCAPI')) // Include the MailChimp API Class here.
						include_once dirname(dirname(__FILE__)).'/externals/mailchimp/nc-mcapi.inc.php';

					$mcapi = new NC_MCAPI ($GLOBALS['WS_PLUGIN__']['s2member']['o']['mailchimp_api_key'], TRUE);

					foreach(preg_split('/['."\r\n\t".';,]+/', $GLOBALS['WS_PLUGIN__']['s2member']['o']['level'.$level.'_mailchimp_list_ids']) as $mailchimp_list)
					{
						$mailchimp = array('function' => __FUNCTION__, 'func_get_args' => $args, 'api_method' => 'listSubscribe');

						if(($mailchimp['list'] = trim($mailchimp_list)))
						{
							if(strpos($mailchimp['list'], '::') !== FALSE) // Also contains Interest Groups?
							{
								list ($mailchimp['list_id'], $mailchimp['interest_groups_title'], $mailchimp['interest_groups']) = preg_split('/\:\:/', $mailchimp['list'], 3);

								if(($mailchimp['interest_groups_title'] = trim($mailchimp['interest_groups_title'])) /* This is a title configured by the list master. */)
									if(($mailchimp['interest_groups'] = (trim($mailchimp['interest_groups'])) ? preg_split('/\|/', trim($mailchimp['interest_groups'])) : FALSE))
										$mailchimp['interest_groups'] = array('GROUPINGS' => array(array('name' => $mailchimp['interest_groups_title'], 'groups' => implode(',', $mailchimp['interest_groups']))));

								if(empty($mailchimp['list_id']) /* Need to double-check this. If empty, skip over this entry. */)
									continue; // Continue to next List, if there is one.
							}
							else $mailchimp['list_id'] = $mailchimp['list']; // Else, it's just a List ID.

							$fname                    = (!$fname) ? ucwords(strstr($email, '@', TRUE)) : $fname;
							$lname                    = (!$lname) ? '-' : $lname;
							$name                     = ($fname || $lname) ? trim($fname.' '.$lname) : ucwords(preg_replace('/^(.+?)@.+/', '$1', $email));
							$mailchimp['merge_array'] = array('MERGE1' => $fname, 'MERGE2' => $lname, 'OPTIN_IP' => $ip, 'OPTIN_TIME' => date('Y-m-d H:i:s'));
							$mailchimp['merge_array'] = (!empty($mailchimp['interest_groups'])) ? array_merge($mailchimp['merge_array'], $mailchimp['interest_groups']) : $mailchimp['merge_array'];
							$mailchimp['merge_array'] = apply_filters('ws_plugin__s2member_mailchimp_array', $mailchimp['merge_array'], get_defined_vars()); // Deprecated.
							// Filter: `ws_plugin__s2member_mailchimp_array` deprecated in v110523. Please use Filter: `ws_plugin__s2member_mailchimp_merge_array`.

							if($mailchimp['api_response'] = $mcapi->{$mailchimp['api_method']}($mailchimp['list_id'], $email, // See: `http://apidocs.mailchimp.com/` for full details.
								($mailchimp['api_merge_array'] = apply_filters('ws_plugin__s2member_mailchimp_merge_array', $mailchimp['merge_array'], get_defined_vars())), // Configured merge array above.
								($mailchimp['api_email_type'] = apply_filters('ws_plugin__s2member_mailchimp_email_type', 'html', get_defined_vars())), // Type of email to receive (i.e. html,text,mobile).
								($mailchimp['api_double_optin'] = apply_filters('ws_plugin__s2member_mailchimp_double_optin', $double_opt_in, get_defined_vars())), // Abuse of this may cause account suspension.
								($mailchimp['api_update_existing'] = apply_filters('ws_plugin__s2member_mailchimp_update_existing', TRUE, get_defined_vars())), // Existing subscribers should be updated with this?
								($mailchimp['api_replace_interests'] = apply_filters('ws_plugin__s2member_mailchimp_replace_interests', TRUE, get_defined_vars())), // Replace interest groups? (only if provided).
								($mailchimp['api_send_welcome'] = apply_filters('ws_plugin__s2member_mailchimp_send_welcome', FALSE, get_defined_vars())))
							) $mailchimp['api_success'] = $success = TRUE; // Flag indicating that we DO have a successful processing of a new List; affects the function's overall return value.

							$mailchimp['api_properties'] = $mcapi; // Include API instance too; as it contains some additional information for logs.

							$logt = c_ws_plugin__s2member_utilities::time_details();
							$logv = c_ws_plugin__s2member_utilities::ver_details();
							$logm = c_ws_plugin__s2member_utilities::mem_details();
							$log4 = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."\n".'User-Agent: '.@$_SERVER['HTTP_USER_AGENT'];
							$log4 = (is_multisite() && !is_main_site()) ? ($_log4 = $current_blog->domain.$current_blog->path)."\n".$log4 : $log4;
							$log2 = (is_multisite() && !is_main_site()) ? 'mailchimp-api-4-'.trim(preg_replace('/[^a-z0-9]/i', '-', (!empty($_log4) ? $_log4 : '')), '-').'.log' : 'mailchimp-api.log';

							if($GLOBALS['WS_PLUGIN__']['s2member']['o']['gateway_debug_logs'])
								if(is_dir($logs_dir = $GLOBALS['WS_PLUGIN__']['s2member']['c']['logs_dir']))
									if(is_writable($logs_dir) && c_ws_plugin__s2member_utils_logs::archive_oversize_log_files())
										file_put_contents($logs_dir.'/'.$log2,
										                  'LOG ENTRY: '.$logt."\n".$logv."\n".$logm."\n".$log4."\n".
										                  c_ws_plugin__s2member_utils_logs::conceal_private_info(var_export($mailchimp, TRUE))."\n\n",
										                  FILE_APPEND);
						}
					}
				}
				if(!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['getresponse_api_key']) && !empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['level'.$level.'_getresponse_list_ids']))
				{
					foreach(preg_split('/['."\r\n\t".';,]+/', $GLOBALS['WS_PLUGIN__']['s2member']['o']['level'.$level.'_getresponse_list_ids']) as $getresponse_list)
					{
						$getresponse = array('function' => __FUNCTION__, 'func_get_args' => $args, 'api_method' => 'add_contact');

						if(($getresponse['list_id'] = $getresponse['list'] = trim($getresponse_list)))
						{
							$getresponse['api_method']  = 'add_contact';
							$getresponse['api_headers'] = array('Content-Type' => 'application/json');
							$getresponse['api_params']  = array($GLOBALS['WS_PLUGIN__']['s2member']['o']['getresponse_api_key'], array('campaigns' => array($getresponse['list_id']), 'email' => array('EQUALS' => $email)));
							$getresponse['api_request'] = json_encode(array('method' => $getresponse['api_method'], 'params' => $getresponse['api_params'], 'id' => uniqid('', TRUE)));
							$name                       = ($fname || $lname) ? trim($fname.' '.$lname) : ucwords(preg_replace('/^(.+?)@.+/', '$1', $email));

							if(is_object($getresponse['api_response'] = json_decode(c_ws_plugin__s2member_utils_urls::remote('https://api2.getresponse.com', $getresponse['api_request'], array('headers' => $getresponse['api_headers'])))) && empty($getresponse['api_response']->error) && ($getresponse['api_response_contact_ids'] = array_keys((array)$getresponse['api_response']->result)) && ($getresponse['api_response_contact_id'] = $getresponse['api_response_contact_ids'][0]))
							{
								$getresponse['api_method']  = 'set_contact_name';
								$getresponse['api_params']  = array($GLOBALS['WS_PLUGIN__']['s2member']['o']['getresponse_api_key'], array('contact' => $getresponse['api_response_contact_id'], 'name' => $name));
								$getresponse['api_request'] = json_encode(array('method' => $getresponse['api_method'], 'params' => $getresponse['api_params'], 'id' => uniqid('', TRUE)));

								if(is_object($getresponse['api_response'] = json_decode(c_ws_plugin__s2member_utils_urls::remote('https://api2.getresponse.com', $getresponse['api_request'], array('headers' => $getresponse['api_headers'])))) && empty($getresponse['api_response']->error))
								{
									$getresponse['api_method']  = 'set_contact_customs';
									$getresponse['api_params']  = array($GLOBALS['WS_PLUGIN__']['s2member']['o']['getresponse_api_key'], array('contact' => $getresponse['api_response_contact_id'], 'customs' => apply_filters('ws_plugin__s2member_getresponse_customs_array', array(), get_defined_vars())));
									$getresponse['api_request'] = json_encode(array('method' => $getresponse['api_method'], 'params' => $getresponse['api_params'], 'id' => uniqid('', TRUE)));

									if(is_object($getresponse['api_response'] = json_decode(c_ws_plugin__s2member_utils_urls::remote('https://api2.getresponse.com', $getresponse['api_request'], array('headers' => $getresponse['api_headers'])))) && empty($getresponse['api_response']->error))
										$getresponse['api_success'] = $success = TRUE;
								}
							}
							else // Create a new contact; i.e. they do not exist on this list yet.
							{
								$getresponse['api_params'] = array($GLOBALS['WS_PLUGIN__']['s2member']['o']['getresponse_api_key'],
								                                   array('name'     => $name, 'email' => $email, 'ip' => $ip,
								                                         'campaign' => $getresponse['list_id'], 'action' => 'standard', 'cycle_day' => 0,
								                                         'customs'  => apply_filters('ws_plugin__s2member_getresponse_customs_array', array(), get_defined_vars())));
								if(!$getresponse['api_params'][1]['ip'] || $getresponse['api_params'][1]['ip'] === 'unknown') unset($getresponse['api_params'][1]['ip']);
								$getresponse['api_request'] = json_encode(array('method' => $getresponse['api_method'], 'params' => $getresponse['api_params'], 'id' => uniqid('', TRUE)));

								if(is_object($getresponse['api_response'] = json_decode(c_ws_plugin__s2member_utils_urls::remote('https://api2.getresponse.com', $getresponse['api_request'], array('headers' => $getresponse['api_headers'])))) && empty($getresponse['api_response']->error) && $getresponse['api_response']->result->queued)
									$getresponse['api_success'] = $success = TRUE;
							}
							$logt = c_ws_plugin__s2member_utilities::time_details();
							$logv = c_ws_plugin__s2member_utilities::ver_details();
							$logm = c_ws_plugin__s2member_utilities::mem_details();
							$log4 = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."\n".'User-Agent: '.@$_SERVER['HTTP_USER_AGENT'];
							$log4 = (is_multisite() && !is_main_site()) ? ($_log4 = $current_blog->domain.$current_blog->path)."\n".$log4 : $log4;
							$log2 = (is_multisite() && !is_main_site()) ? 'getresponse-api-4-'.trim(preg_replace('/[^a-z0-9]/i', '-', (!empty($_log4) ? $_log4 : '')), '-').'.log' : 'getresponse-api.log';

							if($GLOBALS['WS_PLUGIN__']['s2member']['o']['gateway_debug_logs'])
								if(is_dir($logs_dir = $GLOBALS['WS_PLUGIN__']['s2member']['c']['logs_dir']))
									if(is_writable($logs_dir) && c_ws_plugin__s2member_utils_logs::archive_oversize_log_files())
										file_put_contents($logs_dir.'/'.$log2,
										                  'LOG ENTRY: '.$logt."\n".$logv."\n".$logm."\n".$log4."\n".
										                  c_ws_plugin__s2member_utils_logs::conceal_private_info(var_export($getresponse, TRUE))."\n\n",
										                  FILE_APPEND);
						}
					}
				}
				if(!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['level'.$level.'_aweber_list_ids']))
				{
					foreach(preg_split('/['."\r\n\t".'\s;,]+/', $GLOBALS['WS_PLUGIN__']['s2member']['o']['level'.$level.'_aweber_list_ids']) as $aweber_list)
					{
						$aweber = array('function' => __FUNCTION__, 'func_get_args' => $args, 'wp_mail_method' => 'listSubscribe');

						if(($aweber['list_id'] = trim($aweber_list)))
						{
							$aweber['bcc']            = apply_filters('ws_plugin__s2member_aweber_bcc', FALSE, get_defined_vars());
							$aweber['pass_inclusion'] = (apply_filters('ws_plugin__s2member_aweber_pass_inclusion', FALSE, get_defined_vars()) && $pass) ? '\nPass: '.$pass : FALSE;
							$name                     = $buyer = ($fname || $lname) ? trim($fname.' '.$lname) : ucwords(preg_replace('/^(.+?)@.+/', '$1', $email)); // Must have. AWeber's PayPal Email Parser chokes on an empty value.

							if($aweber['wp_mail_response'] = wp_mail($aweber['list_id'].'@aweber.com', // AWeber List ID converts to email address @aweber.com.
								($aweber['wp_mail_sbj'] = apply_filters('ws_plugin__s2member_aweber_sbj', 's2Member Subscription Request', get_defined_vars())), // These Filters make it possible to customize these emails.
								($aweber['wp_mail_msg'] = apply_filters('ws_plugin__s2member_aweber_msg', 's2Member Subscription Request'."\n".'s2Member w/ PayPal Email ID'."\n".'Ad Tracking: s2Member-'.((is_multisite() && !is_main_site()) ? $current_blog->domain.$current_blog->path : $_SERVER['HTTP_HOST'])."\n".'EMail Address: '.$email."\n".'Buyer: '.$buyer."\n".'Full Name: '.trim($fname.' '.$lname)."\n".'First Name: '.$fname."\n".'Last Name: '.$lname."\n".'IP Address: '.$ip."\n".'User ID: '.$user_id."\n".'Login: '.$login.$aweber['pass_inclusion']."\n".'Role: '.$role."\n".'Level: '.$level."\n".'CCaps: '.$ccaps."\n".' - end.', get_defined_vars())),
								($aweber['wp_mail_headers'] = 'From: "'.preg_replace('/"/', "'", $GLOBALS['WS_PLUGIN__']['s2member']['o']['reg_email_from_name']).'" <'.$GLOBALS['WS_PLUGIN__']['s2member']['o']['reg_email_from_email'].'>'.(($aweber['bcc']) ? "\r\n".'Bcc: '.$aweber['bcc'] : '')."\r\n".'Content-Type: text/plain; charset=UTF-8'))
							) $aweber['wp_mail_success'] = $success = TRUE; // Flag indicating that we DO have a successful processing of a new List; affects the function's overall return value.

							$logt = c_ws_plugin__s2member_utilities::time_details();
							$logv = c_ws_plugin__s2member_utilities::ver_details();
							$logm = c_ws_plugin__s2member_utilities::mem_details();
							$log4 = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."\n".'User-Agent: '.@$_SERVER['HTTP_USER_AGENT'];
							$log4 = (is_multisite() && !is_main_site()) ? ($_log4 = $current_blog->domain.$current_blog->path)."\n".$log4 : $log4;
							$log2 = (is_multisite() && !is_main_site()) ? 'aweber-api-4-'.trim(preg_replace('/[^a-z0-9]/i', '-', (!empty($_log4) ? $_log4 : '')), '-').'.log' : 'aweber-api.log';

							if($GLOBALS['WS_PLUGIN__']['s2member']['o']['gateway_debug_logs'])
								if(is_dir($logs_dir = $GLOBALS['WS_PLUGIN__']['s2member']['c']['logs_dir']))
									if(is_writable($logs_dir) && c_ws_plugin__s2member_utils_logs::archive_oversize_log_files())
										file_put_contents($logs_dir.'/'.$log2,
										                  'LOG ENTRY: '.$logt."\n".$logv."\n".$logm."\n".$log4."\n".
										                  c_ws_plugin__s2member_utils_logs::conceal_private_info(var_export($aweber, TRUE))."\n\n",
										                  FILE_APPEND);
						}
					}
				}
				foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
				do_action('ws_plugin__s2member_during_process_list_servers', get_defined_vars());
				unset($__refs, $__v);

				if($email_configs_were_on)
					c_ws_plugin__s2member_email_configs::email_config();

				if($user_id) update_user_option($user_id, 's2member_opt_in', '1');
			}
			foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
			do_action('ws_plugin__s2member_after_process_list_servers', get_defined_vars());
			unset($__refs, $__v);

			return apply_filters('ws_plugin__s2member_process_list_servers', (isset ($success) && $success), get_defined_vars());
		}

		/**
		 * See {@link process_list_servers()} for further details about this wrapper.
		 *
		 * @param bool $opt_in Defaults to false; must be set to true. Indicates the User IS opting in.
		 * @param bool $double_opt_in Defaults to true. If false, no email confirmation is required. Use at your own risk.
		 * @param bool $clean_user_cache Defaults to true; i.e. we start from a fresh copy of the current user.
		 *
		 * @return bool True if at least one List Server is processed successfully, else false.
		 */
		public static function process_list_servers_against_current_user($opt_in = TRUE, $double_opt_in = TRUE, $clean_user_cache = TRUE)
		{
			if($clean_user_cache) // Start from a fresh user object here?
			{
				clean_user_cache(get_current_user_id());
				wp_cache_delete(get_current_user_id(), 'user_meta');
				$user = new WP_User(get_current_user_id());
			}
			else $user = wp_get_current_user();

			return self::process_list_servers(
				($role = c_ws_plugin__s2member_user_access::user_access_role($user)),
				($level = c_ws_plugin__s2member_user_access::user_access_level($user)),
				($login = $user->user_login),
				($pass = $user->user_pass),
				($email = $user->user_email),
				($fname = $user->first_name),
				($lname = $user->last_name),
				($ip = $_SERVER['REMOTE_ADDR']),
				($opt_in = $opt_in), ($double_opt_in = $double_opt_in),
				($user_id = $user->ID)
			);
		}

		/**
		 * Processes List Server removals for s2Member.
		 *
		 * @package s2Member\List_Servers
		 * @since 3.5
		 *
		 * @param string     $role A WordPress Role ID/Name, such as `subscriber`, or `s2member_level1`.
		 * @param int|string $level A numeric s2Member Access Level number.
		 * @param string     $login Username for the User.
		 * @param string     $pass Plain Text Password for the User.
		 * @param string     $email Email address for the User.
		 * @param string     $fname First Name for the User.
		 * @param string     $lname Last Name for the User.
		 * @param string     $ip IP Address for the User.
		 * @param bool       $opt_out Defaults to false; must be set to true. Indicates the User IS opting out.
		 * @param int|string $user_id A WordPress User ID, numeric string or integer.
		 *
		 * @return bool True if at least one List Server is processed successfully, else false.
		 *
		 * @todo Integrate {@link https://labs.aweber.com/docs/php-library-walkthrough AWeber's API}.
		 * @todo Add a separate option for mail debugging; or consolidate?
		 * @todo Integrate AWeber API (much like the MailChimp API).
		 */
		public static function process_list_server_removals($role = '', $level = '', $login = '', $pass = '', $email = '', $fname = '', $lname = '', $ip = '', $opt_out = FALSE, $user_id = 0)
		{
			global $current_site, $current_blog; // For Multisite support.

			foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
			do_action('ws_plugin__s2member_before_process_list_server_removals', get_defined_vars());
			unset($__refs, $__v);

			if(c_ws_plugin__s2member_list_servers::list_servers_integrated() && ($args = func_get_args()) && $role && is_string($role) && is_numeric($level) && $login && is_string($login) && is_string($pass = (string)$pass) && $email && is_string($email) && is_email($email) && is_string($fname = (string)$fname) && is_string($lname = (string)$lname) && is_string($ip = (string)$ip) && is_bool($opt_out = (bool)$opt_out) && $opt_out && $user_id && is_numeric($user_id) && is_object($user = new WP_User ($user_id)) && !empty($user->ID))
			{
				$ccaps = implode(',', c_ws_plugin__s2member_user_access::user_access_ccaps($user));

				$email_configs_were_on = c_ws_plugin__s2member_email_configs::email_config_status();
				c_ws_plugin__s2member_email_configs::email_config_release( /* Release s2Member Filters before we begin this routine. */);

				if(!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['mailchimp_api_key']) && !empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['level'.$level.'_mailchimp_list_ids']))
				{
					if(!class_exists('NC_MCAPI')) // Include the MailChimp API Class here.
						include_once dirname(dirname(__FILE__)).'/externals/mailchimp/nc-mcapi.inc.php';

					$mcapi = new NC_MCAPI ($GLOBALS['WS_PLUGIN__']['s2member']['o']['mailchimp_api_key'], TRUE);

					foreach(preg_split('/['."\r\n\t".';,]+/', $GLOBALS['WS_PLUGIN__']['s2member']['o']['level'.$level.'_mailchimp_list_ids']) as $mailchimp_list)
					{
						$mailchimp = array('function' => __FUNCTION__, 'func_get_args' => $args, 'api_removal_method' => 'listUnsubscribe');

						if(($mailchimp['list_id'] = trim(preg_replace('/\:\:.*$/', '', $mailchimp_list))))
						{
							if($mailchimp['api_removal_response'] = $mcapi->{$mailchimp['api_removal_method']}($mailchimp['list_id'], $email, // See: `http://apidocs.mailchimp.com/`.
								($mailchimp['api_removal_delete_member'] = apply_filters('ws_plugin__s2member_mailchimp_removal_delete_member', FALSE, get_defined_vars())), // Completely delete?
								($mailchimp['api_removal_send_goodbye'] = apply_filters('ws_plugin__s2member_mailchimp_removal_send_goodbye', FALSE, get_defined_vars())), // Send goodbye letter?
								($mailchimp['api_removal_send_notify'] = apply_filters('ws_plugin__s2member_mailchimp_removal_send_notify', FALSE, get_defined_vars())))
							) $mailchimp['api_removal_success'] = $removal_success = TRUE; // Flag indicating that we DO have a successful removal; affects the function's overall return value.
							$mailchimp['api_removal_properties'] = $mcapi; // Include API instance too; as it contains some additional information after each method is processed (need this in the logs).

							$logt = c_ws_plugin__s2member_utilities::time_details();
							$logv = c_ws_plugin__s2member_utilities::ver_details();
							$logm = c_ws_plugin__s2member_utilities::mem_details();
							$log4 = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."\n".'User-Agent: '.@$_SERVER['HTTP_USER_AGENT'];
							$log4 = (is_multisite() && !is_main_site()) ? ($_log4 = $current_blog->domain.$current_blog->path)."\n".$log4 : $log4;
							$log2 = (is_multisite() && !is_main_site()) ? 'mailchimp-api-4-'.trim(preg_replace('/[^a-z0-9]/i', '-', (!empty($_log4) ? $_log4 : '')), '-').'.log' : 'mailchimp-api.log';

							if($GLOBALS['WS_PLUGIN__']['s2member']['o']['gateway_debug_logs'])
								if(is_dir($logs_dir = $GLOBALS['WS_PLUGIN__']['s2member']['c']['logs_dir']))
									if(is_writable($logs_dir) && c_ws_plugin__s2member_utils_logs::archive_oversize_log_files())
										file_put_contents($logs_dir.'/'.$log2,
										                  'LOG ENTRY: '.$logt."\n".$logv."\n".$logm."\n".$log4."\n".
										                  c_ws_plugin__s2member_utils_logs::conceal_private_info(var_export($mailchimp, TRUE))."\n\n",
										                  FILE_APPEND);
						}
					}
				}
				if(!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['getresponse_api_key']) && !empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['level'.$level.'_getresponse_list_ids']))
				{
					foreach(preg_split('/['."\r\n\t".';,]+/', $GLOBALS['WS_PLUGIN__']['s2member']['o']['level'.$level.'_getresponse_list_ids']) as $getresponse_list)
					{
						$getresponse = array('function' => __FUNCTION__, 'func_get_args' => $args, 'api_removal_method' => 'delete_contact');

						if(($getresponse['list_id'] = $getresponse['list'] = trim($getresponse_list)))
						{
							$getresponse['api_method']  = 'get_contacts';
							$getresponse['api_headers'] = array('Content-Type' => 'application/json');
							$getresponse['api_params']  = array($GLOBALS['WS_PLUGIN__']['s2member']['o']['getresponse_api_key'], array('campaigns' => array($getresponse['list_id']), 'email' => array('EQUALS' => $email)));
							$getresponse['api_request'] = json_encode(array('method' => $getresponse['api_method'], 'params' => $getresponse['api_params'], 'id' => uniqid('', TRUE)));

							if(is_object($getresponse['api_response'] = json_decode(c_ws_plugin__s2member_utils_urls::remote('https://api2.getresponse.com', $getresponse['api_request'], array('headers' => $getresponse['api_headers'])))) && empty($getresponse['api_response']->error) && ($getresponse['api_response_contact_ids'] = array_keys((array)$getresponse['api_response']->result)) && ($getresponse['api_response_contact_id'] = $getresponse['api_response_contact_ids'][0]))
							{
								$getresponse['api_method']  = 'delete_contact'; // Update method now.
								$getresponse['api_params']  = array($GLOBALS['WS_PLUGIN__']['s2member']['o']['getresponse_api_key'], array('contact' => $getresponse['api_response_contact_id']));
								$getresponse['api_request'] = json_encode(array('method' => $getresponse['api_method'], 'params' => $getresponse['api_params'], 'id' => uniqid('', TRUE)));

								if(is_object($getresponse['api_response'] = json_decode(c_ws_plugin__s2member_utils_urls::remote('https://api2.getresponse.com', $getresponse['api_request'], array('headers' => $getresponse['api_headers'])))) && empty($getresponse['api_response']->error) && $getresponse['api_response']->result->deleted)
									$getresponse['api_success'] = $success = TRUE;
							}
							$logt = c_ws_plugin__s2member_utilities::time_details();
							$logv = c_ws_plugin__s2member_utilities::ver_details();
							$logm = c_ws_plugin__s2member_utilities::mem_details();
							$log4 = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."\n".'User-Agent: '.@$_SERVER['HTTP_USER_AGENT'];
							$log4 = (is_multisite() && !is_main_site()) ? ($_log4 = $current_blog->domain.$current_blog->path)."\n".$log4 : $log4;
							$log2 = (is_multisite() && !is_main_site()) ? 'getresponse-api-4-'.trim(preg_replace('/[^a-z0-9]/i', '-', (!empty($_log4) ? $_log4 : '')), '-').'.log' : 'getresponse-api.log';

							if($GLOBALS['WS_PLUGIN__']['s2member']['o']['gateway_debug_logs'])
								if(is_dir($logs_dir = $GLOBALS['WS_PLUGIN__']['s2member']['c']['logs_dir']))
									if(is_writable($logs_dir) && c_ws_plugin__s2member_utils_logs::archive_oversize_log_files())
										file_put_contents($logs_dir.'/'.$log2,
										                  'LOG ENTRY: '.$logt."\n".$logv."\n".$logm."\n".$log4."\n".
										                  c_ws_plugin__s2member_utils_logs::conceal_private_info(var_export($getresponse, TRUE))."\n\n",
										                  FILE_APPEND);
						}
					}
				}
				if(!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['level'.$level.'_aweber_list_ids']))
				{
					foreach(preg_split('/['."\r\n\t".'\s;,]+/', $GLOBALS['WS_PLUGIN__']['s2member']['o']['level'.$level.'_aweber_list_ids']) as $aweber_list)
					{
						$aweber = array('function' => __FUNCTION__, 'func_get_args' => $args, 'wp_mail_removal_method' => 'listUnsubscribe');

						if(($aweber['list_id'] = trim($aweber_list)))
						{
							$aweber['removal_bcc'] = apply_filters('ws_plugin__s2member_aweber_removal_bcc', FALSE, get_defined_vars());

							c_ws_plugin__s2member_email_configs::email_config(); // Email configs MUST be ON for removal requests.
							// The `From:` address MUST match AWeber account. See: <http://www.aweber.com/faq/questions/62/Can+I+Unsubscribe+People+Via+Email%3F>.

							if($aweber['wp_mail_removal_response'] = wp_mail($aweber['list_id'].'@aweber.com', // AWeber List ID converts to email address @aweber.com.
								($aweber['wp_mail_removal_sbj'] = apply_filters('ws_plugin__s2member_aweber_removal_sbj', 'REMOVE#'.$email.'#s2Member#'.$aweber['list_id'], get_defined_vars())), // Bug fix. AWeber does not like dots (possibly other chars) in the Ad Tracking field. Now using just: `s2Member`.
								($aweber['wp_mail_removal_msg'] = 'REMOVE'), ($aweber['wp_mail_removal_headers'] = 'From: "'.preg_replace('/"/', "'", $GLOBALS['WS_PLUGIN__']['s2member']['o']['reg_email_from_name']).'" <'.$GLOBALS['WS_PLUGIN__']['s2member']['o']['reg_email_from_email'].'>'.(($aweber['removal_bcc']) ? "\r\n".'Bcc: '.$aweber['removal_bcc'] : '')."\r\n".'Content-Type: text/plain; charset=UTF-8'))
							) $aweber['wp_mail_removal_success'] = $removal_success = TRUE; // Flag indicating that we DO have a successful removal; affects the function's overall return value.

							c_ws_plugin__s2member_email_configs::email_config_release( /* Release. */);

							$logt = c_ws_plugin__s2member_utilities::time_details();
							$logv = c_ws_plugin__s2member_utilities::ver_details();
							$logm = c_ws_plugin__s2member_utilities::mem_details();
							$log4 = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."\n".'User-Agent: '.@$_SERVER['HTTP_USER_AGENT'];
							$log4 = (is_multisite() && !is_main_site()) ? ($_log4 = $current_blog->domain.$current_blog->path)."\n".$log4 : $log4;
							$log2 = (is_multisite() && !is_main_site()) ? 'aweber-api-4-'.trim(preg_replace('/[^a-z0-9]/i', '-', (!empty($_log4) ? $_log4 : '')), '-').'.log' : 'aweber-api.log';

							if($GLOBALS['WS_PLUGIN__']['s2member']['o']['gateway_debug_logs'])
								if(is_dir($logs_dir = $GLOBALS['WS_PLUGIN__']['s2member']['c']['logs_dir']))
									if(is_writable($logs_dir) && c_ws_plugin__s2member_utils_logs::archive_oversize_log_files())
										file_put_contents($logs_dir.'/'.$log2,
										                  'LOG ENTRY: '.$logt."\n".$logv."\n".$logm."\n".$log4."\n".
										                  c_ws_plugin__s2member_utils_logs::conceal_private_info(var_export($aweber, TRUE))."\n\n",
										                  FILE_APPEND);
						}
					}
				}
				foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
				do_action('ws_plugin__s2member_during_process_list_server_removals', get_defined_vars());
				unset($__refs, $__v);

				if($email_configs_were_on)
					c_ws_plugin__s2member_email_configs::email_config();

				if($user_id) update_user_option($user_id, 's2member_opt_in', '0');
			}
			foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
			do_action('ws_plugin__s2member_after_process_list_server_removals', get_defined_vars());
			unset($__refs, $__v);

			return apply_filters('ws_plugin__s2member_process_list_server_removals', (isset ($removal_success) && $removal_success), get_defined_vars());
		}

		/**
		 * See {@link process_list_server_removals()} for further details about this wrapper.
		 *
		 * @param bool $opt_out Defaults to false; must be set to true. Indicates the User IS opting out.
		 * @param bool $clean_user_cache Defaults to true; i.e. we start from a fresh copy of the current user.
		 *
		 * @return bool True if at least one List Server removal is processed successfully, else false.
		 */
		public static function process_list_server_removals_against_current_user($opt_out = TRUE, $clean_user_cache = TRUE)
		{
			if($clean_user_cache) // Start from a fresh user object here?
			{
				clean_user_cache(get_current_user_id());
				wp_cache_delete(get_current_user_id(), 'user_meta');
				$user = new WP_User(get_current_user_id());
			}
			else $user = wp_get_current_user();

			return self::process_list_server_removals(
				($role = c_ws_plugin__s2member_user_access::user_access_role($user)),
				($level = c_ws_plugin__s2member_user_access::user_access_level($user)),
				($login = $user->user_login),
				($pass = $user->user_pass),
				($email = $user->user_email),
				($fname = $user->first_name),
				($lname = $user->last_name),
				($ip = $_SERVER['REMOTE_ADDR']),
				($opt_out = $opt_out),
				($user_id = $user->ID)
			);
		}

		/**
		 * Listens to Collective EOT/MOD Events processed internally by s2Member.
		 *
		 * This is only applicable when ``['custom_reg_auto_opt_outs']`` contains related Event(s).
		 *
		 * @package s2Member\List_Servers
		 * @since 3.5
		 *
		 * @attaches-to ``add_action('ws_plugin__s2member_during_collective_mods');``
		 * @attaches-to ``add_action('ws_plugin__s2member_during_collective_eots');``
		 *
		 * @param int|string $user_id Required. A WordPress User ID, numeric string or integer.
		 * @param array      $vars Required. An array of defined variables passed by the calling Hook.
		 * @param string     $event Required. A specific event that triggered this call from the Action Hook.
		 * @param string     $event_spec Required. A specific event specification *(a broader classification)*.
		 * @param string     $mod_new_role Required if ``$event_spec === 'modification'`` (but can be empty). Role the User is being modified to.
		 * @param string     $mod_new_user Optional. If ``$event_spec === 'modification'``, the new User object with current details.
		 * @param string     $mod_old_user Optional. If ``$event_spec === 'modification'``, the old/previous User obj with old details.
		 */
		public static function auto_process_list_server_removals($user_id, $vars, $event, $event_spec, $mod_new_role = NULL, $mod_new_user = NULL, $mod_old_user = NULL)
		{
			global $current_site, $current_blog; // For Multisite support.
			static $auto_processed = array( /* Process ONE time for each User. */);

			foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
			do_action('ws_plugin__s2member_before_auto_process_list_server_removals', get_defined_vars());
			unset($__refs, $__v);

			$custom_reg_auto_op_outs = c_ws_plugin__s2member_utils_strings::wrap_deep($GLOBALS['WS_PLUGIN__']['s2member']['o']['custom_reg_auto_opt_outs'], '/^', '$/i');

			if(c_ws_plugin__s2member_list_servers::list_servers_integrated() && $user_id && is_numeric($user_id) && !in_array($user_id, $auto_processed) && is_array($vars) && is_string($event = (string)$event) && is_string($event_spec = (string)$event_spec) && (c_ws_plugin__s2member_utils_arrays::in_regex_array($event, $custom_reg_auto_op_outs) || c_ws_plugin__s2member_utils_arrays::in_regex_array($event_spec, $custom_reg_auto_op_outs)) && is_object($user = $_user = new WP_User ($user_id)) && !empty($user->ID))
			{
				$mod_new_role = ($event_spec === 'modification' && $mod_new_role && is_string($mod_new_role)) ? $mod_new_role : FALSE; // Might be empty(i.e. they now have NO Role).
				$mod_new_user = ($event_spec === 'modification' && $mod_new_user && is_object($mod_new_user) && !empty($mod_new_user->ID) && $mod_new_user->ID === $_user->ID) ? $mod_new_user : FALSE;
				$mod_old_user = ($event_spec === 'modification' && $mod_old_user && is_object($mod_old_user) && !empty($mod_old_user->ID) && $mod_old_user->ID === $_user->ID) ? $mod_old_user : FALSE;

				$user = ($event_spec === 'modification' && $mod_old_user) ? $mod_old_user : $_user; // Now, should we switch over to the old/previous User object ``$mod_old_user`` here? Or, should we use the one pulled by this routine with the User's ID?

				if(($event_spec !== 'modification' || ($event_spec === 'modification' && (string)$mod_new_role !== c_ws_plugin__s2member_user_access::user_access_role($user) && strtotime($user->user_registered) < strtotime('-10 seconds') && ($event !== 'user-role-change' || ($event === 'user-role-change' && !empty($vars['_p']['ws_plugin__s2member_custom_reg_auto_opt_out_transitions']))))) && ($auto_processed[$user->ID] = TRUE))
				{
					$removed = c_ws_plugin__s2member_list_servers::process_list_server_removals(c_ws_plugin__s2member_user_access::user_access_role($user), c_ws_plugin__s2member_user_access::user_access_level($user), $user->user_login, FALSE, $user->user_email, $user->first_name, $user->last_name, FALSE, TRUE, $user->ID);

					if($event_spec === 'modification' && $mod_new_role && ($GLOBALS['WS_PLUGIN__']['s2member']['o']['custom_reg_auto_opt_out_transitions'] === '2' || ($GLOBALS['WS_PLUGIN__']['s2member']['o']['custom_reg_auto_opt_out_transitions'] === '1' && $removed)))
					{
						$user = ($event_spec === 'modification' && $mod_new_user) ? $mod_new_user : $_user; // Now, should we switch over to a new/current User object ``$mod_new_user`` here? (which may contain newly updated details). Or, should we simply use the User object pulled by this routine with the User's ID?

						$transitioned = c_ws_plugin__s2member_list_servers::process_list_servers($mod_new_role, c_ws_plugin__s2member_user_access::user_access_role_to_level($mod_new_role), $user->user_login, FALSE, $user->user_email, $user->first_name, $user->last_name, FALSE, TRUE, (($removed) ? FALSE : TRUE), $user->ID);

						foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
						do_action('ws_plugin__s2member_during_auto_process_list_server_removal_transitions', get_defined_vars());
						unset($__refs, $__v);
					}

					foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
					do_action('ws_plugin__s2member_during_auto_process_list_server_removals', get_defined_vars());
					unset($__refs, $__v);
				}
			}
			foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
			do_action('ws_plugin__s2member_after_auto_process_list_server_removals', get_defined_vars());
			unset($__refs, $__v);
		}
	}
}