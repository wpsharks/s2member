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
		public static function paypal_webhook()
		{
			if(empty($_REQUEST['s2member_paypal_webhook']))
				return;

			if(!c_ws_plugin__s2member_paypal_utilities::paypal_checkout_is_enabled())
			{
				status_header(404);
				exit();
			}
			// Admin-only reachability test endpoint (does not validate signatures).
			if(!empty($_GET['s2member_paypal_webhook_test']) && current_user_can('manage_options')
			   && !empty($_GET['_wpnonce']) && wp_verify_nonce((string)$_GET['_wpnonce'], 's2member_ppco_webhook_test'))
			{
				$env = (!empty($_GET['ppco_webhook_env']) && $_GET['ppco_webhook_env'] === 'sandbox') ? 'sandbox' : 'live';

				c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
					'ppco'  => 'webhook',
					'env'   => $env,
					'event' => 'endpoint_test_ok',
					'host'  => !empty($_SERVER['HTTP_HOST']) ? (string)$_SERVER['HTTP_HOST'] : '',
					'uri'   => !empty($_SERVER['REQUEST_URI']) ? (string)$_SERVER['REQUEST_URI'] : '',
					'ssl'   => is_ssl() ? '1' : '0',
				));

				status_header(200);
				header('Content-Type: text/plain; charset=UTF-8');

				$lines = array(
					'SUCCESS',
					'',
					's2Member PayPal Webhook Endpoint (reachability test)',
					'Environment: '.$env,
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

			if(!is_array($event) || empty($event['id']) || empty($event['event_type']))
			{
				c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
					'ppco'  => 'webhook',
					'event' => 'invalid_payload',
				));
				status_header(400);
				exit();
			}

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
			$cert_url = !empty($headers['paypal-cert-url']) ? (string)$headers['paypal-cert-url'] : '';
			$env      = (strpos($cert_url, 'sandbox') !== false) ? 'sandbox' : 'live';

			$verified = c_ws_plugin__s2member_paypal_utilities::paypal_checkout_verify_webhook_signature($event, $raw_body, $headers);
			if(!$verified)
			{
				c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
					'ppco'       => 'webhook',
					'env'        => $env,
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

			// Idempotency per webhook event id (only after successful proxy).
			$event_id_transient = 's2m_ppco_wh_'.md5($event_id);
			if(get_transient($event_id_transient))
			{
				c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
					'ppco'       => 'webhook',
					'env'        => $env,
					'event'      => 'duplicate_event',
					'action'     => 'ignored',
					'note'       => 'Duplicate webhook delivery (event_id already processed).',
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

			// Subscription lifecycle events.
			if(strpos($event_type, 'BILLING.SUBSCRIPTION.') === 0)
			{
				if(!empty($resource['id']))
					$subscr_id = (string)$resource['id'];

				if($event_type === 'BILLING.SUBSCRIPTION.CANCELLED')
					$paypal['txn_type'] = 'subscr_cancel';
				else if($event_type === 'BILLING.SUBSCRIPTION.SUSPENDED')
					$paypal['txn_type'] = 'recurring_payment_suspended_due_to_max_failed_payment';
				else if($event_type === 'BILLING.SUBSCRIPTION.EXPIRED')
					$paypal['txn_type'] = 'subscr_eot';
				else if($event_type === 'BILLING.SUBSCRIPTION.PAYMENT.FAILED')
					$paypal['txn_type'] = 'subscr_failed';
				else
				{
					// Ignore other BILLING.SUBSCRIPTION.* events for MVP.
					c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
						'ppco'       => 'webhook',
						'env'        => $env,
						'event'      => 'ignored',
						'event_id'   => $event_id,
						'event_type' => $event_type,
					));
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
			}

			// Recurring payment events (PayPal often emits PAYMENT.SALE.COMPLETED for subscription payments).
			else if($event_type === 'PAYMENT.SALE.COMPLETED' || $event_type === 'PAYMENT.CAPTURE.COMPLETED')
			{
				$paypal['txn_type']       = 'subscr_payment';
				$paypal['payment_status'] = 'Completed';

				if(!empty($resource['billing_agreement_id']))
					$subscr_id = (string)$resource['billing_agreement_id'];
				else if(!empty($resource['parent_payment']))
					$subscr_id = (string)$resource['parent_payment']; // fallback (not always present)
				else if(!empty($resource['subscription_id']))
					$subscr_id = (string)$resource['subscription_id'];
				else if(!empty($resource['supplementary_data']['related_ids']['billing_agreement_id']))
					$subscr_id = (string)$resource['supplementary_data']['related_ids']['billing_agreement_id'];

				if(!empty($resource['id']))
					$txn_id = (string)$resource['id'];

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
				$paypal['txn_id']    = $txn_id ? $txn_id : $event_id;

				// Help legacy notify logic resolve a user when signup vars are missing (migrations, etc.).
				$paypal['mp_id']                = $subscr_id;
				$paypal['recurring_payment_id'] = $subscr_id;
			}
			else
			{
				// Ignore for MVP.
				c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
					'ppco'       => 'webhook',
					'event'      => 'ignored',
					'event_id'   => $event_id,
					'event_type' => $event_type,
				));
				status_header(200);
				exit();
			}

			// Idempotency per txn (prevents different webhook event IDs from double-processing the same payment).
			$txn_transient = '';
			if(!empty($paypal['txn_type']))
			{
				$txn_key = (string)$event_id;
				if(!empty($paypal['txn_id']))
					$txn_key = (string)$paypal['txn_id'];

				$txn_transient = 's2m_ppco_txn_'.md5($paypal['txn_type'].'|'.$subscr_id.'|'.$txn_key);

				if($txn_key && get_transient($txn_transient))
				{
					c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
						'ppco'       => 'webhook',
						'env'        => $env,
						'event'      => 'duplicate_txn',
						'action'     => 'ignored',
						'note'       => 'Duplicate webhook delivery (txn_id already processed).',
						'event_id'   => $event_id,
						'event_type' => $event_type,
						'subscr_id'  => $subscr_id,
						'txn_id'     => !empty($paypal['txn_id']) ? (string)$paypal['txn_id'] : '',
						'transient'  => $txn_transient,
					));
					status_header(200);
					exit();
				}
			}

			// Proxy into existing s2Member PayPal notify handler to reuse all provisioning/eot logic.
			$url  = add_query_arg('s2member_paypal_notify', '1', home_url('/'));
			$post = array_merge($paypal, array(
				's2member_paypal_proxy'              => 'paypal',
				's2member_paypal_proxy_use'          => 'paypal_checkout_webhook',
				's2member_paypal_proxy_verification' => c_ws_plugin__s2member_paypal_utilities::paypal_proxy_key_gen(),
			));

			$r    = c_ws_plugin__s2member_utils_urls::remote($url, $post, array(
				'timeout' => 20,
			), true);

			if(!is_array($r))
				$r = array('code' => 0, 'message' => 'request_failed', 'body' => '');

			$code = !empty($r['code']) ? (int)$r['code'] : 0;

			if($code >= 200 && $code <= 299)
			{
				set_transient($event_id_transient, time(), 315569260);
				if($txn_transient)
					set_transient($txn_transient, time(), 315569260);

				c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
					'ppco'       => 'webhook',
					'env'        => $env,
					'event'      => 'notify_proxy_response',
					'event_id'   => $event_id,
					'event_type' => $event_type,
					'subscr_id'  => $subscr_id,
					'txn_id'     => $txn_id ? $txn_id : $event_id,
					'url'        => $url,
					'code'       => $code,
					'message'    => !empty($r['message']) ? (string)$r['message'] : '',
				));
			}
			else
				c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
					'ppco'       => 'webhook',
					'env'        => $env,
					'event'      => 'notify_proxy_failed',
					'event_id'   => $event_id,
					'event_type' => $event_type,
					'subscr_id'  => $subscr_id,
					'txn_id'     => $txn_id ? $txn_id : $event_id,
					'url'        => $url,
					'code'       => $code,
					'message'    => !empty($r['message']) ? (string)$r['message'] : '',
				));

			status_header(200);
			exit();
		}
	}
}
