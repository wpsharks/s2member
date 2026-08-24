<?php
// @codingStandardsIgnoreFile
/**
 * s2Member's PayPal Checkout Webhook handler (REST).
 *
 * Receives PayPal webhooks, verifies authenticity, translates events into legacy
 * PayPal-IPN-like vars/txn_type equivalents, and proxies into s2Member's existing
 * PayPal notify handler (via a proxy key) to preserve provisioning behavior.
 *
 * - Signature verification: verify-webhook-signature.
 * - Idempotent processing: duplicate deliveries are safely ignored (and logged).
 * - Admin reachability test: optional GET-based "OK" response for diagnostics.
 *
 * Note: PayPal's Webhooks Simulator is best treated as connectivity-only; real sandbox
 * transactions are the reliable end-to-end verification path.
 *
 * @package s2Member\PayPal
 * @since 260112
 */
if(!defined('WPINC')) // MUST have WordPress.
	exit('Do not access this file directly.');

if(!class_exists('c_ws_plugin__s2member_paypal_webhook_in'))
{
	class c_ws_plugin__s2member_paypal_webhook_in
	{
		//260824.1833 Keep dispute transaction extraction directly testable while accepting PayPal's documented nested payload and a tolerated direct fallback.
		public static function paypal_checkout_dispute_seller_transaction_id($resource = array())
		{
			if(empty($resource['disputed_transactions']) || !is_array($resource['disputed_transactions']))
				return '';

			foreach($resource['disputed_transactions'] as $_disputed_transaction)
				if(is_array($_disputed_transaction) && !empty($_disputed_transaction['transaction_info']['seller_transaction_id']))
					return (string)$_disputed_transaction['transaction_info']['seller_transaction_id'];
				else if(is_array($_disputed_transaction) && !empty($_disputed_transaction['seller_transaction_id']))
					return (string)$_disputed_transaction['seller_transaction_id'];

			return '';
		}

		public static function paypal_webhook()
		{
			if(empty($_REQUEST['s2member_paypal_webhook']))
				return;

			//260218 Allow webhook processing even when Checkout buttons are disabled (if creds+webhook id exist).
			if(!c_ws_plugin__s2member_paypal_utilities::paypal_checkout_webhook_processing_is_enabled())
			{
				status_header(404);
				exit();
			}
			// Admin-only reachability test endpoint (does not validate signatures).
			if(!empty($_GET['s2member_paypal_webhook_test']) && current_user_can('manage_options')
			   && !empty($_GET['_wpnonce']) && wp_verify_nonce((string)$_GET['_wpnonce'], 's2member_ppco_webhook_test'))
			{
				$env_site    = c_ws_plugin__s2member_paypal_utilities::paypal_checkout_is_sandbox() ? 'sandbox' : 'live';
				$env_webhook = (!empty($_GET['ppco_webhook_env']) && $_GET['ppco_webhook_env'] === 'sandbox') ? 'sandbox' : 'live';

				c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
					'ppco'        => 'webhook',
					'env_setting' => $env_site,
					'env_webhook' => $env_webhook,
					'event'       => 'endpoint_test_ok',
					'host'        => !empty($_SERVER['HTTP_HOST']) ? (string)$_SERVER['HTTP_HOST'] : '',
					'uri'         => !empty($_SERVER['REQUEST_URI']) ? (string)$_SERVER['REQUEST_URI'] : '',
					'ssl'         => is_ssl() ? '1' : '0',
				));

				status_header(200);
				header('Content-Type: text/plain; charset=UTF-8');

				$lines = array(
					'SUCCESS',
					'',
					's2Member PayPal Webhook Endpoint (reachability test)',
					'Environment setting: '.$env_site,
					'Environment webhook: '.$env_webhook,
					'SSL: '.(is_ssl() ? 'yes' : 'no'),
					'Host: '.(!empty($_SERVER['HTTP_HOST']) ? (string)$_SERVER['HTTP_HOST'] : ''),
					'URI: '.(!empty($_SERVER['REQUEST_URI']) ? (string)$_SERVER['REQUEST_URI'] : ''),
					'Timestamp (UTC): '.gmdate('Y-m-d H:i:s'),
					'',
					'Note: This is a reachability-only test. Real PayPal webhooks are POST requests and require signature verification.',
				);

				echo implode("\n", $lines);
				exit();
			}

			if(strtoupper((string)$_SERVER['REQUEST_METHOD']) !== 'POST')
			{
				status_header(405);
				exit();
			}

			$raw_body = file_get_contents('php://input');
			$event    = json_decode((string)$raw_body, true);

			$headers = array();
			if(function_exists('getallheaders'))
				foreach((array)getallheaders() as $_k => $_v)
					$headers[strtolower((string)$_k)] = (string)$_v;

			// Fallback for hosts without getallheaders().
			foreach(array(
				'HTTP_PAYPAL_TRANSMISSION_ID'   => 'paypal-transmission-id',
				'HTTP_PAYPAL_TRANSMISSION_TIME' => 'paypal-transmission-time',
				'HTTP_PAYPAL_TRANSMISSION_SIG'  => 'paypal-transmission-sig',
				'HTTP_PAYPAL_CERT_URL'          => 'paypal-cert-url',
				'HTTP_PAYPAL_AUTH_ALGO'         => 'paypal-auth-algo',
			) as $_server => $_key)
				if(empty($headers[$_key]) && !empty($_SERVER[$_server]))
					$headers[$_key] = (string)$_SERVER[$_server];

			//260206 Detect environment from inbound PayPal cert URL.
			$cert_url     = !empty($headers['paypal-cert-url']) ? (string)$headers['paypal-cert-url'] : '';
			$env_site     = c_ws_plugin__s2member_paypal_utilities::paypal_checkout_is_sandbox() ? 'sandbox' : 'live';

			$cert_host    = $cert_url ? (string)parse_url($cert_url, PHP_URL_HOST) : '';
			$env_webhook  = 'unknown';

			if($cert_host && preg_match('/(^|\.)paypal\.com$/i', $cert_host))
				$env_webhook = (stripos($cert_host, 'sandbox') !== false || strpos($cert_url, 'sandbox') !== false) ? 'sandbox' : 'live';

			if(!is_array($event) || empty($event['id']) || empty($event['event_type']))
			{
				c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
					'ppco'        => 'webhook',
					'env_setting' => $env_site,
					'env_webhook' => $env_webhook,
					'event'       => 'invalid_payload',
				));
				status_header(400);
				exit();
			}

			$verified = c_ws_plugin__s2member_paypal_utilities::paypal_checkout_verify_webhook_signature($event, $raw_body, $headers);
			if(!$verified)
			{
				c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
					'ppco'       => 'webhook',
					'env_setting'=> $env_site,
					'env_webhook'=> $env_webhook,
					'event'      => 'signature_failed',
					'event_id'   => (string)$event['id'],
					'event_type' => (string)$event['event_type'],
					'tx_id'      => !empty($headers['paypal-transmission-id']) ? (string)$headers['paypal-transmission-id'] : '',
					'tx_time'    => !empty($headers['paypal-transmission-time']) ? (string)$headers['paypal-transmission-time'] : '',
					'auth_algo'  => !empty($headers['paypal-auth-algo']) ? (string)$headers['paypal-auth-algo'] : '',
					'cert_url'   => !empty($headers['paypal-cert-url']) ? (string)$headers['paypal-cert-url'] : '',
				));
				status_header(400);
				exit();
			}

			$event_id   = (string)$event['id'];
			$event_type = (string)$event['event_type'];

			//260406 Use option-based dedupe/lock markers for PayPal Checkout because transients were not reliable enough on some sites.
			$event_lock_option = 's2m_ppco_wh_lock_'.md5($event_id);
			$event_done_option = 's2m_ppco_wh_done_'.md5($event_id);
			$event_lock_ttl    = 900;
			$event_done_ttl    = 6 * HOUR_IN_SECONDS;
			$txn_done_ttl      = DAY_IN_SECONDS;
			$subscr_done_ttl   = DAY_IN_SECONDS;

			//260406 Occasionally clean up expired PayPal Checkout dedupe markers; the transient only throttles cleanup frequency.
			c_ws_plugin__s2member_paypal_utilities::dedupe_markers_cleanup('s2m_ppco_dedupe_cleanup_throttle', array(
				array('prefix' => 's2m_ppco_wh_done_', 'ttl' => $event_done_ttl),
				array('prefix' => 's2m_ppco_txn_done_', 'ttl' => $txn_done_ttl),
				array('prefix' => 's2m_ppco_subscr_done_', 'ttl' => $subscr_done_ttl),
			), 6 * HOUR_IN_SECONDS);

			$event_done_time = c_ws_plugin__s2member_paypal_utilities::dedupe_done_time_get($event_done_option, $event_done_ttl);
			if($event_done_time > 0)
			{
				c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
					'ppco'       => 'webhook',
					'env_setting'=> $env_site,
					'env_webhook'=> $env_webhook,
					'event'      => 'duplicate_event',
					'action'     => 'ignored',
					'note'       => 'Duplicate webhook delivery (event_id already processed).',
					'event_id'   => $event_id,
					'event_type' => $event_type,
				));
				status_header(200);
				exit();
			}

			if(!c_ws_plugin__s2member_paypal_utilities::dedupe_lock_acquire($event_lock_option, $event_lock_ttl))
			{
				c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
					'ppco'       => 'webhook',
					'env_setting'=> $env_site,
					'env_webhook'=> $env_webhook,
					'event'      => 'duplicate_event',
					'action'     => 'ignored',
					'note'       => 'Duplicate webhook delivery (event_id already processing).',
					'event_id'   => $event_id,
					'event_type' => $event_type,
				));
				status_header(200);
				exit();
			}

			$resource = !empty($event['resource']) && is_array($event['resource']) ? $event['resource'] : array();

			$paypal = array();
			$paypal['charset'] = 'utf-8';
			$paypal['custom']  = !empty($_SERVER['HTTP_HOST']) ? (string)$_SERVER['HTTP_HOST'] : (string)parse_url(home_url('/'), PHP_URL_HOST);

			$subscr_id = '';
			$txn_id    = '';

			$txn_done_option         = '';
			$subscr_done_option      = '';
			$subscr_handled_by_webhook = false;

			// Subscription lifecycle events.
			if(strpos($event_type, 'BILLING.SUBSCRIPTION.') === 0)
			{
				if(!empty($resource['id']))
					$subscr_id = (string)$resource['id'];

				if($subscr_id)
					$subscr_done_option = 's2m_ppco_subscr_done_'.md5($subscr_id); //260406 Match the checkout subscription-done option so webhook ACTIVATED/RE-ACTIVATED stays fallback-only.

				//260401 Treat CREATED as informational only, and let ACTIVATED/RE-ACTIVATED act only as a fallback when checkout has not already handled this Subscription.
				if($event_type === 'BILLING.SUBSCRIPTION.CREATED')
				{
					c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
						'ppco'       => 'webhook',
						'env_setting'=> $env_site,
						'env_webhook'=> $env_webhook,
						'event'      => 'subscription_created',
						'event_id'   => $event_id,
						'event_type' => $event_type,
						'subscr_id'  => $subscr_id,
					));

					//260406 Mark the webhook event done and release its lock for valid terminal events.
					c_ws_plugin__s2member_paypal_utilities::dedupe_done_mark($event_done_option);
					c_ws_plugin__s2member_paypal_utilities::dedupe_lock_release($event_lock_option);

					status_header(200);
					exit();
				}
				else if($event_type === 'BILLING.SUBSCRIPTION.ACTIVATED' || $event_type === 'BILLING.SUBSCRIPTION.RE-ACTIVATED')
				{
					$subscr_done_time = ($subscr_done_option) ? c_ws_plugin__s2member_paypal_utilities::dedupe_done_time_get($subscr_done_option, $subscr_done_ttl) : 0;

					//260401 Ignore webhook activation when checkout already handled this Subscription; otherwise allow webhook activation as a fallback.
					if($subscr_done_option && $subscr_done_time > 0)
					{
						c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
							'ppco'       => 'webhook',
							'env_setting'=> $env_site,
							'env_webhook'=> $env_webhook,
							'event'      => 'subscription_activation_ignored',
							'note'       => 'Checkout already handled this Subscription; skipping webhook fallback activation.',
							'event_id'   => $event_id,
							'event_type' => $event_type,
							'subscr_id'  => $subscr_id,
							'option'     => $subscr_done_option,
						));

						c_ws_plugin__s2member_paypal_utilities::dedupe_done_mark($event_done_option);
						c_ws_plugin__s2member_paypal_utilities::dedupe_lock_release($event_lock_option);

						status_header(200);
						exit();
					}

					//260818.0617 Recover the Checkout invoice from the verified PayPal event so Pro can restore prepared account state.
					if(!empty($resource['custom_id']))
						$paypal['invoice'] = (string)$resource['custom_id'];
					else if($subscr_id)
						{
							$subscription_details = c_ws_plugin__s2member_paypal_utilities::paypal_checkout_subscription_details($subscr_id);
							if(empty($subscription_details['__error']) && !empty($subscription_details['custom_id']))
								$paypal['invoice'] = (string)$subscription_details['custom_id'];
						}

					//260818.0617 Do not let incomplete activation fallback bypass invoice-keyed prepared state; PayPal can retry delivery.
					if(empty($paypal['invoice']))
						{
							c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
								'ppco'       => 'webhook',
								'env_setting'=> $env_site,
								'env_webhook'=> $env_webhook,
								'event'      => 'subscription_activation_invoice_missing',
								'event_id'   => $event_id,
								'event_type' => $event_type,
								'subscr_id'  => $subscr_id,
							));

							c_ws_plugin__s2member_paypal_utilities::dedupe_lock_release($event_lock_option);
							status_header(500);
							exit();
						}

					$paypal['txn_type']       = 'subscr_signup'; //260401 Keep webhook activation as a fallback to the legacy signup handler only when checkout did not already handle this Subscription.
					$paypal['payment_status'] = 'Completed';

					$subscr_handled_by_webhook = true;
				}
				else if($event_type === 'BILLING.SUBSCRIPTION.UPDATED')
					$paypal['txn_type'] = 'subscr_modify';
				else if($event_type === 'BILLING.SUBSCRIPTION.CANCELLED')
					$paypal['txn_type'] = 'subscr_cancel';
				else if($event_type === 'BILLING.SUBSCRIPTION.SUSPENDED')
					$paypal['txn_type'] = 'recurring_payment_suspended_due_to_max_failed_payment';
				else if($event_type === 'BILLING.SUBSCRIPTION.EXPIRED')
					$paypal['txn_type'] = 'subscr_eot';
				else if($event_type === 'BILLING.SUBSCRIPTION.PAYMENT.FAILED')
					$paypal['txn_type'] = 'subscr_failed';
				else
				{
					// Ignore other BILLING.SUBSCRIPTION.* events.
					c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
						'ppco'       => 'webhook',
						'env_setting'=> $env_site,
						'env_webhook'=> $env_webhook,
						'event'      => 'ignored',
						'event_id'   => $event_id,
						'event_type' => $event_type,
					));

					//260406 Mark the webhook event done and release its lock for valid terminal events.
					c_ws_plugin__s2member_paypal_utilities::dedupe_done_mark($event_done_option);
					c_ws_plugin__s2member_paypal_utilities::dedupe_lock_release($event_lock_option);

					status_header(200);
					exit();
				}

				$paypal['subscr_id'] = $subscr_id;
				$paypal['txn_id']    = $event_id; // best-effort unique id

				// Help legacy notify logic resolve a user when signup vars are missing (migrations, etc.).
				$paypal['mp_id']                = $subscr_id;
				$paypal['recurring_payment_id'] = $subscr_id;

				// Best-effort payer email for logs/fallback logic.
				if(!empty($resource['subscriber']['email_address']))
					$paypal['payer_email'] = (string)$resource['subscriber']['email_address'];

				// Enrich lifecycle events with stored signup vars so legacy notify handlers can match and set EOT properly.
				//!!! TO-DO: Deduplicate signup-vars enrichment logic (also used in PayPal Checkout proxy confirm flow).
				if(!empty($paypal['txn_type']) && $subscr_id
				   && in_array($paypal['txn_type'], array('subscr_signup', 'subscr_modify', 'subscr_cancel', 'subscr_eot', 'subscr_failed', 'recurring_payment_suspended_due_to_max_failed_payment'), true)
				   && ($user_id = c_ws_plugin__s2member_utils_users::get_user_id_with($subscr_id))
				   && is_array($ipn_signup_vars = get_user_option('s2member_ipn_signup_vars', $user_id))
				   && !empty($ipn_signup_vars['subscr_id']) && (string)$ipn_signup_vars['subscr_id'] === (string)$subscr_id
				)
				{
					if(empty($paypal['item_number']) && !empty($ipn_signup_vars['item_number']))
						$paypal['item_number'] = (string)$ipn_signup_vars['item_number'];

					if(empty($paypal['item_name']) && !empty($ipn_signup_vars['item_name']))
						$paypal['item_name'] = (string)$ipn_signup_vars['item_name'];

					if(empty($paypal['period1']) && !empty($ipn_signup_vars['period1']))
						$paypal['period1'] = (string)$ipn_signup_vars['period1'];

					if(empty($paypal['period3']) && !empty($ipn_signup_vars['period3']))
						$paypal['period3'] = (string)$ipn_signup_vars['period3'];
				}
			}

			//260824.1727 A newly opened dispute follows s2Member's established PayPal `new_case`/chargeback path.
			else if($event_type === 'CUSTOMER.DISPUTE.CREATED')
			{
				//260824.1833 Use the shared extractor so documented dispute payloads are covered by direct runtime QA.
				$seller_txn_id = self::paypal_checkout_dispute_seller_transaction_id($resource);

				if(!$seller_txn_id)
				{
					c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
						'ppco'       => 'webhook',
						'env_setting'=> $env_site,
						'env_webhook'=> $env_webhook,
						'event'      => 'dispute_transaction_missing',
						'event_id'   => $event_id,
						'event_type' => $event_type,
						'dispute_id' => !empty($resource['dispute_id']) ? (string)$resource['dispute_id'] : (!empty($resource['id']) ? (string)$resource['id'] : ''),
					));

					// A verified but incomplete dispute should be retried; do not mark it complete.
					c_ws_plugin__s2member_paypal_utilities::dedupe_lock_release($event_lock_option);
					status_header(500);
					exit();
				}

				$subscr_id = $seller_txn_id;

				// A first payment/one-time transaction may already identify the member directly.
				if(($user_id = c_ws_plugin__s2member_utils_users::get_user_id_with($seller_txn_id)))
				{
					if(($user_subscr_id = get_user_option('s2member_subscr_id', $user_id)))
						$subscr_id = (string)$user_subscr_id;
				}
				else
				{
					// Later Subscription payments identify the sale, not the Subscription; recover its billing agreement when available.
					$sale = c_ws_plugin__s2member_paypal_utilities::paypal_checkout_api_request('GET', '/v1/payments/sale/'.rawurlencode($seller_txn_id));

					if(!empty($sale['code']) && (int)$sale['code'] === 200 && !empty($sale['body']) && is_string($sale['body']))
					{
						$sale_details = json_decode($sale['body'], true);

						if(is_array($sale_details) && !empty($sale_details['billing_agreement_id']))
							$subscr_id = (string)$sale_details['billing_agreement_id'];
					}
				}

				$paypal['txn_type']      = 'new_case';
				$paypal['case_type']     = 'chargeback';
				$paypal['txn_id']        = $event_id;
				$paypal['parent_txn_id'] = $seller_txn_id;
				$paypal['subscr_id']     = $subscr_id;

				$paypal['mp_id']                = $subscr_id;
				$paypal['recurring_payment_id'] = $subscr_id;

				if(!empty($resource['dispute_amount']['value']))
					$paypal['mc_gross'] = (string)$resource['dispute_amount']['value'];
				else
					$paypal['mc_gross'] = '0';

				if(!empty($resource['dispute_amount']['currency_code']))
					$paypal['mc_currency'] = (string)$resource['dispute_amount']['currency_code'];
				else
					$paypal['mc_currency'] = $GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_default_currency'];

				if(!empty($resource['buyer']['email_address']))
					$paypal['payer_email'] = (string)$resource['buyer']['email_address'];

				// Recover the original signup context so the established chargeback handler can identify the membership.
				if($subscr_id
				   && ($user_id = c_ws_plugin__s2member_utils_users::get_user_id_with($subscr_id))
				   && is_array($ipn_signup_vars = get_user_option('s2member_ipn_signup_vars', $user_id))
				)
				{
					foreach(array('item_number', 'item_name', 'period1', 'period3', 'payer_email') as $_signup_var)
						if(empty($paypal[$_signup_var]) && !empty($ipn_signup_vars[$_signup_var]))
							$paypal[$_signup_var] = (string)$ipn_signup_vars[$_signup_var];
				}

				c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
					'ppco'         => 'webhook',
					'env_setting'  => $env_site,
					'env_webhook'  => $env_webhook,
					'event'        => 'dispute_created',
					'event_id'     => $event_id,
					'event_type'   => $event_type,
					'dispute_id'   => !empty($resource['dispute_id']) ? (string)$resource['dispute_id'] : (!empty($resource['id']) ? (string)$resource['id'] : ''),
					'parent_txn_id'=> $seller_txn_id,
					'subscr_id'    => $subscr_id,
				));
			}

			// Recurring payment events (PayPal often emits PAYMENT.SALE.COMPLETED for subscription payments).
			//260216 Add refund/reversal webhook support so refunds can trigger immediate EOT/demotion.
			else if(in_array($event_type, array(
				'PAYMENT.SALE.COMPLETED',
				'PAYMENT.CAPTURE.COMPLETED',
				'PAYMENT.SALE.REFUNDED',
				'PAYMENT.CAPTURE.REFUNDED',
				'PAYMENT.SALE.REVERSED',
				'PAYMENT.CAPTURE.REVERSED',
			), true))
			{
				if(!empty($resource['billing_agreement_id']))
					$subscr_id = (string)$resource['billing_agreement_id'];
				else if(!empty($resource['parent_payment']))
					$subscr_id = (string)$resource['parent_payment']; // fallback (not always present)
				else if(!empty($resource['subscription_id']))
					$subscr_id = (string)$resource['subscription_id'];
				else if(!empty($resource['supplementary_data']['related_ids']['billing_agreement_id']))
					$subscr_id = (string)$resource['supplementary_data']['related_ids']['billing_agreement_id'];

				//260228 Ignore one-time sale/capture webhooks that have no subscription reference.
				if(!$subscr_id)
				{
					c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
						'ppco'       => 'webhook',
						'env_setting'=> $env_site,
						'env_webhook'=> $env_webhook,
						'event'      => 'ignored_non_subscription_payment',
						'event_id'   => $event_id,
						'event_type' => $event_type,
						'resource'   => $resource,
					));

					//260406 Mark the webhook event done and release its lock for valid terminal events.
					c_ws_plugin__s2member_paypal_utilities::dedupe_done_mark($event_done_option);
					c_ws_plugin__s2member_paypal_utilities::dedupe_lock_release($event_lock_option);

					status_header(200);
					exit();
				}

				$paypal['txn_type'] = 'subscr_payment';

				if(strpos($event_type, '.REFUNDED') !== false)
					$paypal['payment_status'] = 'Refunded';
				else if(strpos($event_type, '.REVERSED') !== false)
					$paypal['payment_status'] = 'Reversed';
				else
					$paypal['payment_status'] = 'Completed';

				if(!empty($resource['id']))
					$txn_id = (string)$resource['id']; // original capture/sale id

				if(!empty($resource['amount']['total']))
					$paypal['mc_gross'] = (string)$resource['amount']['total'];
				else if(!empty($resource['amount']['value']))
					$paypal['mc_gross'] = (string)$resource['amount']['value'];

				if(!empty($resource['amount']['currency']))
					$paypal['mc_currency'] = (string)$resource['amount']['currency'];
				else if(!empty($resource['amount']['currency_code']))
					$paypal['mc_currency'] = (string)$resource['amount']['currency_code'];

				if(!empty($resource['payer']['payer_info']['email']))
					$paypal['payer_email'] = (string)$resource['payer']['payer_info']['email'];
				else if(!empty($resource['payer']['email_address']))
					$paypal['payer_email'] = (string)$resource['payer']['email_address'];

				$paypal['subscr_id'] = $subscr_id;

				//260216 Emulate IPN semantics for refund/reversal: parent_txn_id=original, txn_id=event delivery.
				if(!empty($paypal['payment_status']) && preg_match('/^(refunded|reversed|reversal)$/i', $paypal['payment_status']))
				{
					$paypal['parent_txn_id'] = $txn_id ? $txn_id : $event_id;
					$paypal['txn_id']        = $event_id;
				}
				else
					$paypal['txn_id'] = $txn_id ? $txn_id : $event_id;

				$paypal['mp_id']                = $subscr_id;
				$paypal['recurring_payment_id'] = $subscr_id;

				//260216 Enrich refund/reversal from stored signup vars so legacy handlers can demote immediately.
				if(!empty($paypal['payment_status']) && preg_match('/^(refunded|reversed|reversal)$/i', $paypal['payment_status'])
				   && $subscr_id
				   && ($user_id = c_ws_plugin__s2member_utils_users::get_user_id_with($subscr_id))
				   && is_array($ipn_signup_vars = get_user_option('s2member_ipn_signup_vars', $user_id))
				   && !empty($ipn_signup_vars['subscr_id']) && (string)$ipn_signup_vars['subscr_id'] === (string)$subscr_id
				)
				{
					if(empty($paypal['item_number']) && !empty($ipn_signup_vars['item_number']))
						$paypal['item_number'] = (string)$ipn_signup_vars['item_number'];

					if(empty($paypal['item_name']) && !empty($ipn_signup_vars['item_name']))
						$paypal['item_name'] = (string)$ipn_signup_vars['item_name'];

					if(empty($paypal['period1']) && !empty($ipn_signup_vars['period1']))
						$paypal['period1'] = (string)$ipn_signup_vars['period1'];

					if(empty($paypal['period3']) && !empty($ipn_signup_vars['period3']))
						$paypal['period3'] = (string)$ipn_signup_vars['period3'];

					if(empty($paypal['payer_email']) && !empty($ipn_signup_vars['payer_email']))
						$paypal['payer_email'] = (string)$ipn_signup_vars['payer_email'];
				}
			}
			else
			{
				// Ignore for MVP.
				c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
					'ppco'       => 'webhook',
					'env_setting'=> $env_site,
					'env_webhook'=> $env_webhook,
					'event'      => 'ignored',
					'event_id'   => $event_id,
					'event_type' => $event_type,
				));

				//260406 Mark the webhook event done and release its lock for valid terminal events.
				c_ws_plugin__s2member_paypal_utilities::dedupe_done_mark($event_done_option);
				c_ws_plugin__s2member_paypal_utilities::dedupe_lock_release($event_lock_option);

				status_header(200);
				exit();
			}

			//260406 Idempotency per txn prevents different webhook event IDs from double-processing the same payment.
			if(!empty($paypal['txn_type']))
			{
				$txn_key = (string)$event_id;

				//260216 For refund/reversal, prefer idempotency on original payment id.
				if(!empty($paypal['parent_txn_id']))
					$txn_key = (string)$paypal['parent_txn_id'];
				else if(!empty($paypal['txn_id']))
					$txn_key = (string)$paypal['txn_id'];

				//260824.1727 Refunds, reversals, and disputes can share the original payment ID; keep each later state independently idempotent.
				$txn_dedupe_key = $txn_key;
				if(!empty($paypal['payment_status']) && preg_match('/^(refunded|reversed|reversal)$/i', $paypal['payment_status']))
					$txn_dedupe_key = strtolower((string)$paypal['payment_status']).'|'.$txn_key;
				else if(!empty($paypal['txn_type']) && $paypal['txn_type'] === 'new_case' && !empty($paypal['case_type']) && $paypal['case_type'] === 'chargeback')
					$txn_dedupe_key = 'chargeback|'.$txn_key;

				$txn_done_option = 's2m_ppco_txn_done_'.md5($paypal['txn_type'].'|'.$subscr_id.'|'.$txn_dedupe_key);

				if($txn_key)
				{
					$txn_done_time = c_ws_plugin__s2member_paypal_utilities::dedupe_done_time_get($txn_done_option, $txn_done_ttl);

					if($txn_done_time > 0)
					{
						c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
							'ppco'       => 'webhook',
							'env_setting'=> $env_site,
							'env_webhook'=> $env_webhook,
							'event'      => 'duplicate_txn',
							'action'     => 'ignored',
							'note'       => 'Duplicate webhook delivery (txn_id already processed).',
							'event_id'   => $event_id,
							'event_type' => $event_type,
							'subscr_id'  => $subscr_id,
							'txn_id'     => !empty($paypal['txn_id']) ? (string)$paypal['txn_id'] : '',
							'option'     => $txn_done_option,
						));

						c_ws_plugin__s2member_paypal_utilities::dedupe_done_mark($event_done_option);
						c_ws_plugin__s2member_paypal_utilities::dedupe_lock_release($event_lock_option);

						status_header(200);
						exit();
					}
				}
			}

			// Proxy into existing s2Member PayPal notify handler to reuse all provisioning/eot logic.
			$url = add_query_arg('s2member_paypal_notify', '1', home_url('/'));
			$notify_duplicate = false;

			if($subscr_handled_by_webhook && !empty($subscr_done_option))
			{
				//260818.0603 Share the subscription Notify lock/done marker with browser confirmation so activation fallback cannot race it.
				$notify_result = c_ws_plugin__s2member_paypal_utilities::paypal_checkout_notify_once($paypal, $subscr_done_option, 'paypal_checkout_webhook');
				$notify_ok = !empty($notify_result['ok']);
				$notify_duplicate = !empty($notify_result['duplicate']);
				$code = !empty($notify_result['code']) ? (int)$notify_result['code'] : 0;
				$message = !empty($notify_result['message']) ? (string)$notify_result['message'] : (!empty($notify_result['error']) ? (string)$notify_result['error'] : '');
			}
			else
			{
				$post = array_merge($paypal, array(
					's2member_paypal_proxy'              => 'paypal',
					's2member_paypal_proxy_use'          => 'paypal_checkout_webhook',
					's2member_paypal_proxy_verification' => c_ws_plugin__s2member_paypal_utilities::paypal_proxy_key_gen(),
				));

				$r = c_ws_plugin__s2member_utils_urls::remote($url, $post, array(
					'timeout' => 20,
				), true);

				if(!is_array($r))
					$r = array('code' => 0, 'message' => 'request_failed', 'body' => '');

				$code = !empty($r['code']) ? (int)$r['code'] : 0;
				$message = !empty($r['message']) ? (string)$r['message'] : '';
				$notify_ok = ($code >= 200 && $code <= 299);
			}

			if($notify_ok)
			{
				c_ws_plugin__s2member_paypal_utilities::dedupe_done_mark($event_done_option);
				c_ws_plugin__s2member_paypal_utilities::dedupe_lock_release($event_lock_option);

				if(!empty($txn_done_option))
					c_ws_plugin__s2member_paypal_utilities::dedupe_done_mark($txn_done_option);

				c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
					'ppco'       => 'webhook',
					'env_setting'=> $env_site,
					'env_webhook'=> $env_webhook,
					'event'      => 'notify_proxy_response',
					'event_id'   => $event_id,
					'event_type' => $event_type,
					'subscr_id'  => $subscr_id,
					'txn_id'     => $txn_id ? $txn_id : $event_id,
					'url'        => $url,
					'code'       => $code,
					'message'    => $message,
					'duplicate'  => $notify_duplicate,
				));
			}
			else
			{
				//260406 Release the in-flight webhook lock on failure so PayPal retries can proceed.
				c_ws_plugin__s2member_paypal_utilities::dedupe_lock_release($event_lock_option);

				c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
					'ppco'       => 'webhook',
					'env_setting'=> $env_site,
					'env_webhook'=> $env_webhook,
					'event'      => 'notify_proxy_failed',
					'event_id'   => $event_id,
					'event_type' => $event_type,
					'subscr_id'  => $subscr_id,
					'txn_id'     => $txn_id ? $txn_id : $event_id,
					'url'        => $url,
					'code'       => $code,
					'message'    => $message,
				));

				//260818.0603 Activation fallback must remain retryable when shared fulfillment fails or is still in progress.
				if($subscr_handled_by_webhook)
				{
					status_header(500);
					exit();
				}
			}

			status_header(200);
			exit();
		}
	}
}
