<?php
/**
 * Log utilities.
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
 * @package s2Member\Utilities
 * @since 3.5
 */
if(realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME']))
	exit ('Do not access this file directly.');

if(!class_exists('c_ws_plugin__s2member_utils_logs'))
{
	/**
	 * Log utilities.
	 *
	 * @package s2Member\Utilities
	 * @since 3.5
	 */
	class c_ws_plugin__s2member_utils_logs
	{
		/**
		 * Array of log file descriptions.
		 *
		 * @package s2Member\Utilities
		 * @since 120214
		 *
		 * @var array Array of log file descriptions.
		 */
		public static $log_file_descriptions = array
		( // Array keys are regex patterns matching their associated log file names.
		  '/paypal\-api/'          => array('short' => 'PayPal API communication.', 'long' => 'This log file records all communication between s2Member and PayPal APIs. Such as PayPal Button Encryption and PayPal Pro API calls that process transactions. See also: gateway-core-ipn.log (s2Member\'s core processor).'),
		  '/paypal\-payflow\-api/' => array('short' => 'PayPal (PayFlow Edition) API communication.', 'long' => 'This log file records all communication between s2Member and the PayPal (PayFlow Edition) APIs. Only applicable if you operate a PayPal Payments Pro (PayFlow Edition) account. See also: gateway-core-ipn.log (s2Member\'s core processor).'),
		  '/gateway\-core\-ipn/'   => array('short' => 'Core PayPal IPN and post-processing handler.', 'long' => 'This log file records all communication between s2Member and the PayPal IPN service. Also logs all post-processing routines from other Payment Gateway integrations, where s2Member translates its communication with other Payment Gateways into a format it\'s core PayPal processing routines can understand. All transactions pass through s2Member\'s core PayPal processor and they will be logged in this file. Including transactions processed via s2Member Pro Forms; for all Payment Gateway integrations.'),
		  '/gateway\-core\-rtn/'   => array('short' => 'Core PayPal PDT/Auto-Return communication.', 'long' => 'This log file records all communication between s2Member and the PayPal PDT Auto-Return system (i.e. routines that help s2Member process Thank-You pages). Also logs all Auto-Return routines from other Payment Gateway integrations (those implemented via Payment Buttons), where s2Member translates its communication with other Payment Gateways into a format it\'s core PayPal processing routines can understand. Not used in s2Member Pro Form integrations however.'),

		  '/stripe\-api/'          => array('short' => 'Stripe API communication.', 'long' => 'This log file records all communication between s2Member and Stripe APIs.'),
		  '/stripe\-ipn/'          => array('short' => 'Stripe Webhook/IPN communication.', 'long' => 'This log file records the Webhook/IPN data that Stripe sends to s2Member.'),

		  '/authnet\-api/'         => array('short' => 'Authorize.Net API communication.', 'long' => 'This log file records all communication between s2Member and Authorize.Net APIs (for both AIM and ARB integrations).'),
		  '/authnet\-arb/'         => array('short' => 'Authorize.Net ARB Subscription status checks.', 'long' => 'This log file records s2Member\'s Authorize.Net ARB Subscription status checks. s2Member polls the ARB service periodically to check the status of existing Members (e.g. to see if billing is still active or not).'),
		  '/authnet\-ipn/'         => array('short' => 'Authorize.Net Silent Post/IPN communication.', 'long' => 'This log file records the Silent Post/IPN data Authorize.Net sends to s2Member with details regarding new transactions.'),

		  '/alipay\-ipn/'          => array('short' => 'AliPay IPN communication.', 'long' => 'This log file records the IPN data AliPay sends to s2Member with details regarding new transactions. See also: gateway-core-ipn.log (s2Member\'s core processor).'),
		  '/alipay\-rtn/'          => array('short' => 'AliPay Auto-Return communication.', 'long' => 'This log file records the Auto-Return data AliPay sends to s2Member with details regarding new transactions (i.e. logs routines that help s2Member process Thank-You pages). See also: gateway-core-rtn.log (s2Member\'s core processor).'),

		  '/clickbank\-ipn/'       => array('short' => 'ClickBank IPN communication.', 'long' => 'This log file records the IPN data ClickBank sends to s2Member with details regarding new transactions, cancellations, expirations, etc. See also: gateway-core-ipn.log (s2Member\'s core processor).'),
		  '/clickbank\-rtn/'       => array('short' => 'ClickBank Auto-Return communication.', 'long' => 'This log file records the Auto-Return data ClickBank sends to s2Member with details regarding new transactions (i.e. logs routines that help s2Member process Thank-You pages). See also: gateway-core-rtn.log (s2Member\'s core processor).'),

		  '/google\-rtn/'          => array('short' => 'Google Auto-Return communication.', 'long' => 'This log file records the Auto-Return data Google sends to s2Member with details regarding new transactions (i.e. logs routines that help s2Member process Thank-You pages). See also: gateway-core-rtn.log (s2Member\'s core processor). NOTE (regarding Google Wallet)... this particular log file is currently implemented for a possible future use ONLY. At this time there is no need for an Auto-Return handler with Google Wallet, because Google Wallet return handling is done via email-only at this time.'),
		  '/google\-ipn/'          => array('short' => 'Google Postback/IPN communication.', 'long' => 'This log file records the Postback/IPN data Google sends to s2Member with details regarding new transactions, cancellations, expirations, etc. See also: gateway-core-ipn.log (s2Member\'s core processor).'),

		  '/ccbill\-ipn/'          => array('short' => 'ccBill Bg Post/IPN communication.', 'long' => 'This log file records the Bg Post/IPN data ccBill sends to s2Member with details regarding new transactions. See also: gateway-core-ipn.log (s2Member\'s core processor).'),
		  '/ccbill\-rtn/'          => array('short' => 'ccBill Auto-Return communication.', 'long' => 'This log file records the Auto-Return data ccBill sends to s2Member with details regarding new transactions (i.e. logs routines that help s2Member process Thank-You pages). See also: gateway-core-rtn.log (s2Member\'s core processor).'),
		  '/ccbill\-dl\-ipn/'      => array('short' => 'ccBill Datalink Subscription status checks.', 'long' => 'This log file records s2Member\'s ccBill Datalink Subscription status checks that may result in actions taken by s2Member. s2Member polls the ccBill Datalink service periodically to check the status of existing Members (e.g. to see if billing is still active or not).'),
		  '/ccbill\-dl/'           => array('short' => 'ccBill Datalink collections.', 'long' => 'This log file records s2Member\'s ccBill Datalink connections. s2Member polls the ccBill Datalink service periodically to obtain information about existing Users/Members.'),

		  '/mailchimp\-api/'       => array('short' => 'MailChimp API communication.', 'long' => 'This log file records all of s2Member\'s communication with MailChimp APIs.'),
		  '/aweber\-api/'          => array('short' => 'AWeber API communication.', 'long' => 'This log file records all of s2Member\'s communication with AWeber APIs.'),

		  '/reg\-handler/'         => array('short' => 'User registrations processed by s2Member.', 'long' => 'This log file records all User/Member registrations processed by s2Member (either directly or indirectly). This includes both free and paid registrations. It also logs registrations that occur as a result of new Users/Members being created from the Dashboard by a site owner. It also includes registrations that occur through the s2Member Pro Remote Operations API.'),

		  '/s2\-http\-api\-debug/' => array('short' => 'All outgoing HTTP connections related to s2Member.', 'long' => 'This log file records all outgoing WP_Http connections that are specifically related to s2Member. This log file can be extremely helpful. It includes technical details about remote HTTP connections that are not available in other log files.'),
		  '/wp\-http\-api\-debug/' => array('short' => 'All outgoing WordPress HTTP connections.', 'long' => 'This log file records all outgoing HTTP connections processed by the WP_Http class. This includes everything processed by WordPress; even things unrelated to s2Member. This log file can be extremely helpful. It includes technical details about remote HTTP connections that are not available in other log files.'),
		);

		/**
		 * Logs HTTP communication (if enabled).
		 *
		 * @package s2Member\Utilities
		 * @since 120212
		 */
		public static function http_api_debug($response = NULL, $state = NULL, $class = NULL, $args = NULL, $url = NULL)
		{
			if(!$GLOBALS['WS_PLUGIN__']['s2member']['o']['gateway_debug_logs'])
				return; // Logging is NOT enabled in this case.

			$is_s2member = (!empty($args['s2member']) || strpos($url, 's2member') !== FALSE) ? TRUE : FALSE;

			if(!$GLOBALS['WS_PLUGIN__']['s2member']['o']['gateway_debug_logs_extensive'] && !$is_s2member)
				return; // Extensive logging is NOT enabled in this case.

			global $current_site, $current_blog; // For Multisite support.

			$http_api_debug = array(
				'state'           => $state,
				'transport_class' => $class,
				'args'            => $args,
				'url'             => $url,
				'response'        => $response
			);
			$logt           = c_ws_plugin__s2member_utilities::time_details();
			$logv           = c_ws_plugin__s2member_utilities::ver_details();
			$logm           = c_ws_plugin__s2member_utilities::mem_details();

			$log4 = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."\n".'User-Agent: '.$_SERVER['HTTP_USER_AGENT'];
			$log4 = (is_multisite() && !is_main_site()) ? ($_log4 = $current_blog->domain.$current_blog->path)."\n".$log4 : $log4;
			$log2 = (is_multisite() && !is_main_site()) ? 'http-api-debug-4-'.trim(preg_replace('/[^a-z0-9]/i', '-', (!empty($_log4) ? $_log4 : '')), '-').'.log' : 'http-api-debug.log';

			$http_api_debug_conceal_private_info = c_ws_plugin__s2member_utils_logs::conceal_private_info(var_export($http_api_debug, TRUE));

			if(is_dir($logs_dir = $GLOBALS['WS_PLUGIN__']['s2member']['c']['logs_dir']))
				if(is_writable($logs_dir) && c_ws_plugin__s2member_utils_logs::archive_oversize_log_files())
				{
					if($GLOBALS['WS_PLUGIN__']['s2member']['o']['gateway_debug_logs_extensive'])
						file_put_contents($logs_dir.'/wp-'.$log2,
						                  'LOG ENTRY: '.$logt."\n".$logv."\n".$logm."\n".$log4."\n".
						                  $http_api_debug_conceal_private_info."\n\n",
						                  FILE_APPEND);

					if($is_s2member) // Log s2Member HTTP connections separately.
						file_put_contents($logs_dir.'/s2-'.$log2,
						                  'LOG ENTRY: '.$logt."\n".$logv."\n".$logm."\n".$log4."\n".
						                  $http_api_debug_conceal_private_info."\n\n",
						                  FILE_APPEND);
				}
		}

		/**
		 * Archives logs to prevent HUGE files from building up over time.
		 *
		 * This routine is staggered to conserve resources.
		 * This is called by all logging routines for s2Member.
		 *
		 * @package s2Member\Utilities
		 * @since 3.5
		 *
		 * @param bool $stagger Optional. Defaults to true. If false, the routine will run, regardless.
		 *
		 * @return bool Always returns true.
		 */
		public static function archive_oversize_log_files($stagger = TRUE)
		{
			if(!$stagger || is_float($stagger = time() / 2)) // Stagger this routine?
			{
				if(is_dir($dir = $GLOBALS['WS_PLUGIN__']['s2member']['c']['logs_dir']) && is_writable($dir))
				{
					$max = apply_filters('ws_plugin__s2member_oversize_log_file_bytes', 2097152, get_defined_vars());

					$log_files = scandir($dir);
					shuffle($log_files);
					$counter = 1;

					foreach($log_files as $file) // Go through each log file. Up to 25 files at a time.
					{
						if(preg_match('/\.log$/i', $file) && !preg_match('/\-ARCHIVED\-/i', $file) && is_file($dir_file = $dir.'/'.$file))
						{
							if(filesize($dir_file) > $max && is_writable($dir_file)) // The file must be writable.
								if($log = preg_replace('/\.log$/i', '', $dir_file)) // Strip .log before renaming.
									rename($dir_file, $log.'-ARCHIVED-'.date('m-d-Y').'-'.time().'.log');
						}
						if(($counter = $counter + 1) > 25) // Up to 25 files at a time.
							break; // Stop for now.
					}
				}
			}
			return TRUE;
		}

		/**
		 * Removes expired Transients inserted into the database by s2Member.
		 *
		 * This routine is staggered to conserve resources.
		 * Only 5 Transients are deleted each time.
		 *
		 * This is called by s2Member's Auto-EOT System, every 10 minutes.
		 *
		 * @package s2Member\Utilities
		 * @since 3.5
		 *
		 * @param bool $stagger Optional. Defaults to true. If false, the routine will run, regardless.
		 *
		 * @return bool Always returns true.
		 */
		public static function cleanup_expired_s2m_transients($stagger = TRUE)
		{
			global $wpdb;
			/** @var wpdb $wpdb */

			if(!$stagger || is_float($stagger = time() / 2)) // Stagger this routine?
			{
				if(is_array($expired_s2m_transients = $wpdb->get_results("SELECT * FROM `".$wpdb->options."` WHERE `option_name` LIKE '".esc_sql(like_escape('_transient_timeout_s2m_'))."%' AND `option_value` < '".esc_sql(time())."' LIMIT 5")) && !empty($expired_s2m_transients))
				{
					foreach($expired_s2m_transients as $expired_s2m_transient) // Delete the _timeout, and also the transient entry name itself.
						if(($id = $expired_s2m_transient->option_id) && ($name = preg_replace('/_transient_timeout_/i', '_transient_', $expired_s2m_transient->option_name, 1)))
							$wpdb->query("DELETE FROM `".$wpdb->options."` WHERE (`option_id` = '".esc_sql($id)."' OR `option_name` = '".esc_sql($name)."')");
				}
			}
			return TRUE;
		}

		/**
		 * Attempts to conceal private details in log entries.
		 *
		 * @package s2Member\Utilities
		 * @since 130315
		 *
		 * @param string $log_entry The log entry we need to conceal private details in.
		 *
		 * @return string Filtered string with some data X'd out :-)
		 */
		public static function conceal_private_info($log_entry)
		{
			$log_entry = preg_replace('/\b([3456][0-9]{10,11})([0-9]{4})\b/', 'xxxxxxxxxxxx'.'$2', (string)$log_entry);

			$log_entry = preg_replace('/(\'.*pass_?(?:word)?(?:[0-9]+)?\'\s*\=\>\s*\')([^\']+)(\')/', '$1'.'xxxxxxxx/pass'.'$3', $log_entry);
			$log_entry = preg_replace('/([&?][^&]*pass_?(?:word)?(?:[0-9]+)?\=)([^&]+)/', '$1'.'xxxxxxxx/pass', $log_entry);

			$log_entry = preg_replace('/(\'api_?(?:key|secret)\'\s*\=\>\s*\')([^\']+)(\')/', '$1'.'xxxxxxxx/api/key/sec'.'$3', $log_entry);
			$log_entry = preg_replace('/([&?][^&]api_?(?:key|secret)\=)([^&]+)/', '$1'.'xxxxxxxx/api/key/sec', $log_entry);

			$log_entry = preg_replace('/(\'(?:PWD|SIGNATURE)\'\s*\=\>\s*\')([^\']+)(\')/', '$1'.'xxxxxxxx/PWD/SIG'.'$3', $log_entry);
			$log_entry = preg_replace('/([&?][^&](?:PWD|SIGNATURE)\=)([^&]+)/', '$1'.'xxxxxxxx/PWD/SIG', $log_entry);

			$log_entry = preg_replace('/(\'(?:x_login|x_tran_key)\'\s*\=\>\s*\')([^\']+)(\')/', '$1'.'xxxxxxxx/key/tran'.'$3', $log_entry);
			$log_entry = preg_replace('/([&?][^&](?:x_login|x_tran_key)\=)([^&]+)/', '$1'.'xxxxxxxx/key/tran', $log_entry);

			return $log_entry;
		}
	}
}