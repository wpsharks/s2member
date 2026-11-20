<?php
// @codingStandardsIgnoreFile
/**
 * Gateway-aware subscription cancellation dispatcher.
 *
 * Centralizes the existing replacement/cancellation routing; the historical
 * c_ws_plugin__s2member_utilities::cancel_gateway_subscription() remains as a
 * public compatibility wrapper. This does not make cancellation retry-safe.
 *
 * @package s2Member\Gateway_Subscriptions
 * @since 261116.2249
 */
if(!defined('WPINC')) // MUST have WordPress.
	exit('Do not access this file directly.');

if(!class_exists('c_ws_plugin__s2member_gateway_subscriptions'))
{
	/**
	 * Gateway-specific cancellation with unchanged replacement route precedence.
	 *
	 * @package s2Member\Gateway_Subscriptions
	 * @since 261116.2249
	 */
	class c_ws_plugin__s2member_gateway_subscriptions
	{
		/**
		 * Cancel a previously captured subscription using the legacy dispatch rules.
		 *
		 * This may call external provider APIs. Callers must not blindly retry a
		 * failed or interrupted cancellation without checking provider state.
		 *
		 * @since 261116.2249
		 * @param string|bool $subscr_gateway Previously captured gateway slug.
		 * @param string|bool $subscr_id Previously captured subscription identifier.
		 * @param string|bool $subscr_baid Previously captured billing agreement identifier.
		 * @param string|bool $subscr_cid Previously captured customer identifier.
		 * @param array $ipn_signup_vars Previously captured provider-routing hints.
		 * @param bool $immediate Whether to cancel immediately where supported.
		 * @param string $reason Optional provider cancellation explanation.
		 * @return bool Whether a provider cancellation request was accepted.
		 */
		public static function cancel_gateway_subscription($subscr_gateway = FALSE, $subscr_id = FALSE, $subscr_baid = FALSE, $subscr_cid = FALSE, $ipn_signup_vars = array(), $immediate = TRUE, $reason = '')
		{
			$subscr_gateway = strtolower((string)$subscr_gateway);
			$subscr_id = (string)$subscr_id;
			$subscr_baid = (string)$subscr_baid;
			$subscr_cid = (string)$subscr_cid;
			$ipn_signup_vars = (is_array($ipn_signup_vars)) ? $ipn_signup_vars : array();

			if(!$subscr_gateway || !$subscr_id)
				return FALSE;

			switch($subscr_gateway)
			{
				case 'stripe':
					if(!$subscr_cid || !class_exists('c_ws_plugin__s2member_pro_stripe_utilities'))
						return FALSE;

					//260407 Stripe replacement flows need the guard around old-subscription cancellation.
					c_ws_plugin__s2member_pro_stripe_utilities::set_replacement_cancellation_guard($subscr_id);

					if(!is_object(c_ws_plugin__s2member_pro_stripe_utilities::cancel_customer_subscription($subscr_cid, $subscr_id, !$immediate)))
					{
						c_ws_plugin__s2member_pro_stripe_utilities::clear_replacement_cancellation_guard($subscr_id);
						return FALSE;
					}
					return TRUE;

				case 'authnet':
					if(!class_exists('c_ws_plugin__s2member_pro_authnet_utilities'))
						return FALSE;

					return (($authnet = c_ws_plugin__s2member_pro_authnet_utilities::authnet_arb_response(array('x_method' => 'cancel', 'x_subscription_id' => $subscr_id))) && empty($authnet['__error'])) ? TRUE : FALSE;

				case 'paypal':
					$paypal_proxy_use = (!empty($ipn_signup_vars['s2member_paypal_proxy_use'])) ? (string)$ipn_signup_vars['s2member_paypal_proxy_use'] : '';
					$paypal_invoice = (!empty($ipn_signup_vars['invoice'])) ? (string)$ipn_signup_vars['invoice'] : '';
					$ppco_hint = ($paypal_proxy_use === 'paypal_checkout' || strpos($paypal_invoice, 's2mpf-') === 0);
					$can_try_ppco = FALSE;
					$can_try_standard = FALSE;
					$can_try_payflow = FALSE;

					$paypal_checkout_creds = c_ws_plugin__s2member_paypal_utilities::paypal_checkout_creds();
					$can_try_ppco = (!empty($paypal_checkout_creds['client_id']) && !empty($paypal_checkout_creds['secret']));

					$can_try_standard = (!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_api_username'])
					                     && !empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_api_password'])
					                     && !empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_api_signature']));

					$can_try_payflow = (!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_payflow_api_username'])
					                    && !empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_payflow_api_partner'])
					                    && !empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_payflow_api_vendor'])
					                    && !empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_payflow_api_password'])
					                    && class_exists('c_ws_plugin__s2member_pro_paypal_utilities'));

					//261116.2249 Keep original PayPal precedence for legacy ambiguous identifiers during extraction; do not guess a new provider route.
					//260819.0543 Use positive product provenance where available; I-* is ambiguous, while Payflow profile IDs use RP/RT prefixes.
					$payflow_hint = (bool)preg_match('/^R[PT]/i', $subscr_id);
					if($ppco_hint)
						$paypal_routes = array('ppco', 'standard', 'payflow');
					else if($payflow_hint)
						$paypal_routes = array('payflow', 'standard', 'ppco');
					else
						$paypal_routes = array('standard', 'ppco', 'payflow');

					$reason = trim((string)$reason);
					if(!$reason)
						$reason = 'Cancelled by replacement checkout.';

					foreach($paypal_routes as $_paypal_route)
					{
						switch($_paypal_route)
						{
							case 'ppco':
								if(!$can_try_ppco)
									break;

								if(($paypal_checkout = c_ws_plugin__s2member_paypal_utilities::paypal_checkout_subscription_cancel($subscr_id, $reason))
								   && !empty($paypal_checkout['code']) && ($paypal_checkout['code'] === 204 || ($paypal_checkout['code'] >= 200 && $paypal_checkout['code'] <= 299)))
									return TRUE;
								break;

							case 'standard':
								if(!$can_try_standard)
									break;

								if(($paypal_standard = c_ws_plugin__s2member_paypal_utilities::paypal_standard_subscription_cancel($subscr_id))
								   && empty($paypal_standard['__error']))
									return TRUE;
								break;

							case 'payflow':
								if(!$can_try_payflow)
									break;

								if(c_ws_plugin__s2member_pro_paypal_utilities::payflow_cancel_profile($subscr_id, $subscr_baid))
									return TRUE;
								break;
						}
					}
			}
			return FALSE;
		}
	}
}
