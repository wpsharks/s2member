<?php
// @codingStandardsIgnoreFile
/**
 * s2Member's PayPal Checkout (REST) handler.
 *
 * @package s2Member\PayPal
 * @since 260101
 */
if(!defined('WPINC'))
	exit('Do not access this file directly.');

if(!class_exists('c_ws_plugin__s2member_paypal_checkout'))
{
	class c_ws_plugin__s2member_paypal_checkout
	{
		/**
		 * @attaches-to ``add_action('init');``
		 */
		public static function paypal_checkout()
		{
			if(!empty($_REQUEST['s2member_paypal_checkout']))
				c_ws_plugin__s2member_paypal_checkout_in::paypal_checkout();
		}
	}
}
