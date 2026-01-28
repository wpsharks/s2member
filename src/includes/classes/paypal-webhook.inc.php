<?php
// @codingStandardsIgnoreFile
/**
 * s2Member's PayPal Webhook handler (REST).
 *
 * @package s2Member\PayPal
 * @since 260112
 */
if(!defined('WPINC'))
	exit('Do not access this file directly.');

if(!class_exists('c_ws_plugin__s2member_paypal_webhook'))
{
	class c_ws_plugin__s2member_paypal_webhook
	{
		/**
		 * @attaches-to ``add_action('init');``
		 */
		public static function paypal_webhook()
		{
			if(!empty($_REQUEST['s2member_paypal_webhook']))
				c_ws_plugin__s2member_paypal_webhook_in::paypal_webhook();
		}
	}
}
