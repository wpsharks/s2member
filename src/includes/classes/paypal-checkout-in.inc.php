<?php
// @codingStandardsIgnoreFile
/**
 * s2Member's PayPal Checkout (REST) handler.
 *
 * Server-side entrypoint for PayPal Checkout operations used by s2Member shortcodes:
 * - Buy Now: create_order + capture_order (one-time payments).
 * - Subscriptions (membership level): get_plan_id + confirm_subscription.
 * - output="url|anchor": redirect/return flow (does not create orders on page load).
 * - Optional: cancel_subscription (on-site cancel for logged-in users).
 *
 * Successful operations are proxied into s2Member's existing PayPal notify/return handlers,
 * preserving legacy provisioning behavior (level/ccaps/EOT/etc.) without rewriting it.
 *
 * @package s2Member\PayPal
 * @since 260101
 */
if(!defined('WPINC')) // MUST have WordPress.
	exit ('Do not access this file directly.');

if(!class_exists('c_ws_plugin__s2member_paypal_checkout_in'))
{
	class c_ws_plugin__s2member_paypal_checkout_in
	{
		public static function paypal_checkout()
		{
			if(empty($_REQUEST['s2member_paypal_checkout']))
				return;

			@set_time_limit(0);
			@ini_set('memory_limit', apply_filters('admin_memory_limit', WP_MAX_MEMORY_LIMIT));
			@ini_set('display_errors', '0');

			$op = !empty($_REQUEST['s2member_paypal_checkout_op']) ? strtolower(trim(stripslashes((string)$_REQUEST['s2member_paypal_checkout_op']))) : '';
			$t  = !empty($_REQUEST['s2member_paypal_checkout_t'])  ? trim(stripslashes((string)$_REQUEST['s2member_paypal_checkout_t'])) : '';

			$is_redirect_mode = in_array($op, array('redirect', 'return', 'cancel'), true);

			nocache_headers();
			if($is_redirect_mode)
				header('Content-Type: text/html; charset=UTF-8');
			else
				header('Content-Type: application/json; charset=UTF-8');

			c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
					'ppco'    => 'checkout',
					'event'   => 'request',
					'get'     => $_GET,
					'post'    => $_POST,
					'method'  => !empty($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '',
					'ip'      => !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
					'ua'      => !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
					'referer' => !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
				));

			if(!$op || !$t)
			{
				echo json_encode(array('error' => 'missing_op_or_token'));
				exit();
			}
			if(!($token = @unserialize(c_ws_plugin__s2member_utils_encryption::decrypt($t))) || !is_array($token))
			{
				echo json_encode(array('error' => 'invalid_token'));
				exit();
			}
			if(!empty($token['exp']) && is_numeric($token['exp']) && time() > (int)$token['exp'])
			{
				echo json_encode(array('error' => 'token_expired'));
				exit();
			}
			if(empty($token['invoice']) || empty($token['ip']) || empty($token['item_number']) || empty($token['checksum']))
			{
				echo json_encode(array('error' => 'token_incomplete'));
				exit();
			}
			if($token['checksum'] !== md5($token['invoice'].$token['ip'].$token['item_number']))
			{
				echo json_encode(array('error' => 'token_checksum_mismatch'));
				exit();
			}

			if($token['ip'] !== c_ws_plugin__s2member_utils_ip::current())
			{
				c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
						'ppco'  => 'checkout',
						'event' => 'token_ip_mismatch',
						'token' => $token,
						'ip'    => c_ws_plugin__s2member_utils_ip::current(),
					));

				echo json_encode(array('error' => 'token_ip_mismatch'));
				exit();
			}

			// output="anchor|url" support: redirect-mode endpoints (GET).
			if($op === 'redirect' || $op === 'return' || $op === 'cancel')
			{
				// NOTE: These endpoints are intended for output="anchor|url" shortcode formats.
				// They redirect to PayPal approval URLs, then auto-POST into s2Member's existing PayPal notify + return handlers.

				if($op === 'cancel')
				{
					wp_redirect(!empty($token['cancel']) ? (string)$token['cancel'] : home_url('/'));
					exit();
				}

				$endpoint = home_url('/?s2member_paypal_checkout=1');
				$return_url = $endpoint.'&s2member_paypal_checkout_op=return&s2member_paypal_checkout_t='.rawurlencode($t);
				$cancel_url = $endpoint.'&s2member_paypal_checkout_op=cancel&s2member_paypal_checkout_t='.rawurlencode($t);

				if($op === 'redirect')
				{
					$pp_token = $token;
					$pp_token['return'] = $return_url;
					$pp_token['cancel'] = $cancel_url;

					if((!isset($pp_token['rr']) || (string)$pp_token['rr'] === '') || strtoupper((string)$pp_token['rr']) === 'BN')
					{
						$order = c_ws_plugin__s2member_paypal_utilities::paypal_checkout_order_create($pp_token);

						c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
							'ppco'   => 'checkout',
							'event'  => 'redirect_order_create_response',
							'order'  => $order,
							'token'  => $token,
						));

						$approve_url = '';
						if(!empty($order['links']) && is_array($order['links']))
							foreach($order['links'] as $link)
								if(!empty($link['rel']) && !empty($link['href']))
								{
									$rel = strtolower((string)$link['rel']);
									if($rel === 'approve' || $rel === 'payer-action' || $rel === 'approval_url')
										$approve_url = (string)$link['href'];
								}

						if(!$approve_url)
						{
							echo 'order_approval_url_missing';
							exit();
						}

						wp_redirect($approve_url);
						exit();
					}
					else
					{
						$subscription = c_ws_plugin__s2member_paypal_utilities::paypal_checkout_subscription_create($pp_token);

						c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
							'ppco'         => 'checkout',
							'event'        => 'redirect_subscription_create_response',
							'subscription' => $subscription,
							'token'        => $token,
						));

						$approve_url = '';
						if(!empty($subscription['links']) && is_array($subscription['links']))
							foreach($subscription['links'] as $link)
								if(!empty($link['rel']) && !empty($link['href']) && strtolower((string)$link['rel']) === 'approve')
									$approve_url = (string)$link['href'];

						if(!$approve_url)
						{
							echo 'subscription_approval_url_missing';
							exit();
						}

						wp_redirect($approve_url);
						exit();
					}
				}

				// Return URL: PayPal redirects here after approval.
				if((!isset($token['rr']) || (string)$token['rr'] === '') || strtoupper((string)$token['rr']) === 'BN')
				{
					$order_id = !empty($_GET['token']) ? trim(stripslashes((string)$_GET['token'])) : '';
					if(!$order_id)
					{
						echo 'missing_order_id';
						exit();
					}

					$capture = c_ws_plugin__s2member_paypal_utilities::paypal_checkout_order_capture($order_id, $token);

					$cap0 = (!empty($capture['purchase_units'][0]['payments']['captures'][0]) && is_array($capture['purchase_units'][0]['payments']['captures'][0])) ? $capture['purchase_units'][0]['payments']['captures'][0] : array();
					c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
						'ppco'       => 'checkout',
						'event'      => 'capture_response',
						'order_id'   => $order_id,
						'status'     => !empty($capture['status']) ? (string)$capture['status'] : '',
						'capture_id' => !empty($cap0['id']) ? (string)$cap0['id'] : '',
						'amount'     => !empty($cap0['amount']['value']) ? (string)$cap0['amount']['value'] : '',
						'cc'         => !empty($cap0['amount']['currency_code']) ? (string)$cap0['amount']['currency_code'] : '',
						'payer'      => !empty($capture['payer']['email_address']) ? (string)$capture['payer']['email_address'] : '',
						'capture'    => $capture,
						'token'      => $token,
					));

					if(empty($capture['status']) || strtoupper($capture['status']) !== 'COMPLETED')
					{
						echo 'order_capture_failed';
						exit();
					}

					$payer_email = !empty($capture['payer']['email_address']) ? (string)$capture['payer']['email_address'] : '';
					$first_name  = !empty($capture['payer']['name']['given_name']) ? (string)$capture['payer']['name']['given_name'] : '';
					$last_name   = !empty($capture['payer']['name']['surname']) ? (string)$capture['payer']['name']['surname'] : '';

					$pu_amount   = !empty($capture['purchase_units'][0]['payments']['captures'][0]['amount']['value']) ? (string)$capture['purchase_units'][0]['payments']['captures'][0]['amount']['value'] : '';
					$pu_cc       = !empty($capture['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code']) ? (string)$capture['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'] : '';
					$pu_cap_id   = !empty($capture['purchase_units'][0]['payments']['captures'][0]['id']) ? (string)$capture['purchase_units'][0]['payments']['captures'][0]['id'] : '';

					if(!$payer_email || !$pu_amount || !$pu_cc || !$pu_cap_id)
					{
						echo 'capture_missing_fields';
						exit();
					}

					if(!empty($token['amount']) && (string)$token['amount'] !== (string)$pu_amount)
					{
						echo 'amount_mismatch';
						exit();
					}
					if(!empty($token['cc']) && strtoupper((string)$token['cc']) !== strtoupper((string)$pu_cc))
					{
						echo 'currency_mismatch';
						exit();
					}

					$paypal = array(
						'txn_type'       => 'web_accept',
						'payment_status' => 'Completed',
						'txn_id'         => $pu_cap_id,
						'mc_gross'       => $pu_amount,
						'mc_currency'    => $pu_cc,
						'invoice'        => (string)$token['invoice'],
						'custom'         => (string)$token['custom'],
						'item_name'      => (string)$token['item_name'],
						'item_number'    => (string)$token['item_number'],
						'option_name1'      => (string)$token['on0'],
						'option_selection1' => (string)$token['os0'],
						'option_name2'      => (string)$token['on1'],
						'option_selection2' => (string)$token['os1'],
						'payer_email'    => $payer_email,
						'first_name'     => $first_name,
						'last_name'      => $last_name,
					);

					$notify_url  = home_url('/?s2member_paypal_notify=1');
					$notify_post = array_merge($paypal, array(
						's2member_paypal_proxy'              => 'paypal',
						's2member_paypal_proxy_use'          => 'paypal_checkout',
						's2member_paypal_proxy_verification' => c_ws_plugin__s2member_paypal_utilities::paypal_proxy_key_gen(),
					));
					$notify_r = c_ws_plugin__s2member_utils_urls::remote($notify_url, $notify_post, array('timeout' => 20), true);

					if(!is_array($notify_r))
					{
						echo 'notify_proxy_failed';
						exit();
					}

					$return_url = (string)$token['return'];
					$return_url = add_query_arg('s2member_paypal_proxy', 'paypal', $return_url);

					$return_post = array_merge($paypal, array(
						's2member_paypal_proxy'              => 'paypal',
						's2member_paypal_proxy_use'          => 'paypal_checkout',
						's2member_paypal_proxy_verification' => c_ws_plugin__s2member_paypal_utilities::paypal_proxy_key_gen(),
					));

					// Auto-POST into s2Member's existing PayPal return handler.
					echo '<!DOCTYPE html><html><head><meta charset="utf-8" /><meta name="robots" content="noindex,nofollow" /></head><body>';
					echo '<form id="s2m_ppco_rtn" method="post" action="'.esc_attr($return_url).'">';
					foreach($return_post as $k => $v)
						echo '<input type="hidden" name="'.esc_attr($k).'" value="'.esc_attr((string)$v).'" />';
					echo '</form><script type="text/javascript">document.getElementById("s2m_ppco_rtn").submit();</script></body></html>';
					exit();
				}
				else
				{
					$subscription_id = !empty($_GET['subscription_id']) ? trim(stripslashes((string)$_GET['subscription_id'])) : '';
					if(!$subscription_id)
					{
						echo 'missing_subscription_id';
						exit();
					}

					$subscription_r = c_ws_plugin__s2member_paypal_utilities::paypal_checkout_api_request('GET', '/v1/billing/subscriptions/'.rawurlencode($subscription_id));

					c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
						'ppco'            => 'checkout',
						'event'           => 'subscription_get_response',
						'subscription_id' => $subscription_id,
						'code'            => !empty($subscription_r['code']) ? (int)$subscription_r['code'] : 0,
						'body'            => !empty($subscription_r['body']) ? (string)$subscription_r['body'] : '',
						'token'           => $token,
					));

					$subscription_code = !empty($subscription_r['code']) ? (int)$subscription_r['code'] : 0;
					$subscription_body = !empty($subscription_r['body']) ? (string)$subscription_r['body'] : '';

					$subscription = array();
					if($subscription_body)
						$subscription = json_decode($subscription_body, true);

					if(!is_array($subscription))
						$subscription = array();

					if($subscription_code < 200 || $subscription_code > 299 || empty($subscription['id']))
					{
						echo 'subscription_get_failed';
						exit();
					}

					$custom_id = !empty($subscription['custom_id']) ? (string)$subscription['custom_id'] : '';
					if($custom_id && (string)$token['invoice'] && $custom_id !== (string)$token['invoice'])
					{
						echo 'subscription_custom_id_mismatch';
						exit();
					}

					$subscriber_email = !empty($subscription['subscriber']['email_address']) ? (string)$subscription['subscriber']['email_address'] : '';
					$first_name = !empty($subscription['subscriber']['name']['given_name']) ? (string)$subscription['subscriber']['name']['given_name'] : '';
					$last_name  = !empty($subscription['subscriber']['name']['surname']) ? (string)$subscription['subscriber']['name']['surname'] : '';

					$paypal = array(
						'txn_type'       => 'subscr_signup',
						'payment_status' => 'Completed',
						'subscr_gateway' => 'paypal',

						'txn_id'         => $subscription_id,
						'subscr_id'      => $subscription_id,
						'subscr_baid'    => $subscription_id,
						'subscr_cid'     => $subscription_id,

						'mc_gross'       => (string)$token['amount'],
						'mc_currency'    => strtoupper((string)$token['cc']),

						'period1'        => (!empty($token['tp']) && !empty($token['tt'])) ? ((string)$token['tp'].' '.strtoupper((string)$token['tt'])) : '0 D',
						'mc_amount1'     => (!empty($token['tp']) && !empty($token['tt'])) ? (string)$token['ta'] : '0.00',

						'period3'        => ((string)$token['rp'].' '.strtoupper((string)$token['rt'])),
						'mc_amount3'     => (string)$token['amount'],
						'recurring'      => ((isset($token['rr']) && (string)$token['rr'] === '1') ? '1' : '0'),

						'invoice'        => (string)$token['invoice'],
						'custom'         => (string)$token['custom'],
						'item_name'      => (string)$token['item_name'],
						'item_number'    => (string)$token['item_number'],

						'payer_email'    => $subscriber_email,
						'first_name'     => $first_name,
						'last_name'      => $last_name,

						'option_name1'      => (string)$token['on0'],
						'option_selection1' => (string)$token['os0'],
						'option_name2'      => (string)$token['on1'],
						'option_selection2' => (string)$token['os1'],
					);

					// Idempotency: avoid duplicate notify processing for the same subscription_id.
					$transient_ppco_subscr = 's2m_ppco_subscr_'.md5($subscription_id);
					if(!get_transient($transient_ppco_subscr))
					{
						set_transient($transient_ppco_subscr, 1, 31556926 * 10);

						$notify_url  = home_url('/?s2member_paypal_notify=1');
						$notify_post = array_merge($paypal, array(
							's2member_paypal_proxy'              => 'paypal',
							's2member_paypal_proxy_use'          => 'paypal_checkout',
							's2member_paypal_proxy_verification' => c_ws_plugin__s2member_paypal_utilities::paypal_proxy_key_gen(),
						));
						$notify_r = c_ws_plugin__s2member_utils_urls::remote($notify_url, $notify_post, array('timeout' => 20), true);

						if(!is_array($notify_r))
						{
							echo 'notify_proxy_failed';
							exit();
						}
					}

					$return_url2 = (string)$token['return'];
					$return_url2 = add_query_arg('s2member_paypal_proxy', 'paypal', $return_url2);

					$return_post2 = array_merge($paypal, array(
						's2member_paypal_proxy'              => 'paypal',
						's2member_paypal_proxy_use'          => 'paypal_checkout',
						's2member_paypal_proxy_verification' => c_ws_plugin__s2member_paypal_utilities::paypal_proxy_key_gen(),
					));

					echo '<!DOCTYPE html><html><head><meta charset="utf-8" /><meta name="robots" content="noindex,nofollow" /></head><body>';
					echo '<form id="s2m_ppco_rtn" method="post" action="'.esc_attr($return_url2).'">';
					foreach($return_post2 as $k => $v)
						echo '<input type="hidden" name="'.esc_attr($k).'" value="'.esc_attr((string)$v).'" />';
					echo '</form><script type="text/javascript">document.getElementById("s2m_ppco_rtn").submit();</script></body></html>';
					exit();
				}
			}

			if($op === 'get_plan_id')
			{
				if((!isset($token['rr']) || (string)$token['rr'] === '') || strtoupper((string)$token['rr']) === 'BN')
				{
					echo json_encode(array('error' => 'not_subscription'));
					exit();
				}

				$plan_id = c_ws_plugin__s2member_paypal_utilities::paypal_checkout_plan_get_id($token);

				c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
						'ppco'    => 'checkout',
						'event'   => 'get_plan_id_response',
						'plan_id' => $plan_id,
						'token'   => $token,
					));

				if(!$plan_id)
				{
					echo json_encode(array('error' => 'plan_create_failed'));
					exit();
				}

				echo json_encode(array('plan_id' => $plan_id));
				exit();
			}

			if($op === 'confirm_subscription')
			{
				if((!isset($token['rr']) || (string)$token['rr'] === '') || strtoupper((string)$token['rr']) === 'BN')
				{
					echo json_encode(array('error' => 'not_subscription'));
					exit();
				}
				$subscription_id = !empty($_POST['subscription_id']) ? trim(stripslashes((string)$_POST['subscription_id'])) : '';

				if(!$subscription_id)
				{
					echo json_encode(array('error' => 'missing_subscription_id'));
					exit();
				}
				$subscription_r = c_ws_plugin__s2member_paypal_utilities::paypal_checkout_api_request('GET', '/v1/billing/subscriptions/'.rawurlencode($subscription_id));

				c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
						'ppco'            => 'checkout',
						'event'           => 'subscription_get_response',
						'subscription_id' => $subscription_id,
						'subscription'    => $subscription_r,
						'token'           => $token,
					));

				$subscription_code = !empty($subscription_r['code']) ? (int)$subscription_r['code'] : 0;
				$subscription_body = !empty($subscription_r['body']) ? (string)$subscription_r['body'] : '';

				$subscription = array();
				if($subscription_body)
					$subscription = json_decode($subscription_body, true);

				if(!is_array($subscription))
					$subscription = array();

				if($subscription_code < 200 || $subscription_code > 299 || empty($subscription['id']))
				{
					c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
						'ppco'            => 'checkout',
						'event'           => 'subscription_get_failed',
						'subscription_id' => $subscription_id,
						'code'            => $subscription_code,
						'body'            => $subscription_body,
					));
					echo json_encode(array('error' => 'subscription_get_failed'));
					exit();
				}
				$status = !empty($subscription['status']) ? strtoupper((string)$subscription['status']) : '';

				$is_single_cycle = (isset($token['rr']) && (string)$token['rr'] === '0');
				$allow_expired_single_cycle = false;

				// PayPal can complete a single-cycle subscription immediately, returning status=EXPIRED after payment.
				if($is_single_cycle && $status === 'EXPIRED')
				{
					$lpv = '';
					$lpc = '';

					if(!empty($subscription['billing_info']['last_payment']['amount']['value']))
						$lpv = (string)$subscription['billing_info']['last_payment']['amount']['value'];

					if(!empty($subscription['billing_info']['last_payment']['amount']['currency_code']))
						$lpc = strtoupper((string)$subscription['billing_info']['last_payment']['amount']['currency_code']);

					if($lpv !== '' && $lpc !== '')
						$allow_expired_single_cycle = true;
				}

				if($status && !in_array($status, array('ACTIVE', 'APPROVED', 'APPROVAL_PENDING'), true) && !$allow_expired_single_cycle)
				{
					c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
						'ppco'            => 'checkout',
						'event'           => 'subscription_status_invalid',
						'subscription_id' => $subscription_id,
						'status'          => $status,
					));

					echo json_encode(array('error' => 'subscription_status_invalid'));
					exit();
				}
				$expected_plan_id = c_ws_plugin__s2member_paypal_utilities::paypal_checkout_plan_get_id($token);
				if($expected_plan_id && !empty($subscription['plan_id']) && (string)$subscription['plan_id'] !== (string)$expected_plan_id)
				{
					c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
						'ppco'            => 'checkout',
						'event'           => 'plan_mismatch',
						'subscription_id' => $subscription_id,
						'expected'        => $expected_plan_id,
						'actual'          => (string)$subscription['plan_id'],
					));
					echo json_encode(array('error' => 'plan_mismatch'));
					exit();
				}
				$custom_id = !empty($subscription['custom_id']) ? (string)$subscription['custom_id'] : '';
				if($custom_id && $custom_id !== (string)$token['invoice'])
				{
					c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
						'ppco'            => 'checkout',
						'event'           => 'subscription_custom_id_mismatch',
						'subscription_id' => $subscription_id,
						'expected'        => (string)$token['invoice'],
						'actual'          => $custom_id,
					));
					echo json_encode(array('error' => 'subscription_custom_id_mismatch'));
					exit();
				}

				$subscriber_email = !empty($subscription['subscriber']['email_address']) ? (string)$subscription['subscriber']['email_address'] : '';
				$first_name = !empty($subscription['subscriber']['name']['given_name']) ? (string)$subscription['subscriber']['name']['given_name'] : '';
				$last_name  = !empty($subscription['subscriber']['name']['surname']) ? (string)$subscription['subscriber']['name']['surname'] : '';

				$paypal = array(
					'txn_type'       => 'subscr_signup',
					'payment_status' => 'Completed',
					'subscr_gateway' => 'paypal',

					'txn_id'         => $subscription_id,
					'subscr_id'      => $subscription_id,
					'subscr_baid'    => $subscription_id,
					'subscr_cid'     => $subscription_id,

					'mc_gross'       => (string)$token['amount'],
					'mc_currency'    => strtoupper((string)$token['cc']),

					'period1'        => (!empty($token['tp']) && !empty($token['tt'])) ? ((string)$token['tp'].' '.strtoupper((string)$token['tt'])) : '0 D',
					'mc_amount1'     => (!empty($token['tp']) && !empty($token['tt'])) ? (string)$token['ta'] : '0.00',

					'period3'        => ((string)$token['rp'].' '.strtoupper((string)$token['rt'])),
					'mc_amount3'     => (string)$token['amount'],
					'recurring'      => ((isset($token['rr']) && (string)$token['rr'] === '1') ? '1' : '0'),

					'invoice'        => (string)$token['invoice'],
					'custom'         => (string)$token['custom'],
					'item_name'      => (string)$token['item_name'],
					'item_number'    => (string)$token['item_number'],

					'payer_email'    => $subscriber_email,
					'first_name'     => $first_name,
					'last_name'      => $last_name,

					'option_name1'      => (string)$token['on0'],
					'option_selection1' => (string)$token['os0'],
					'option_name2'      => (string)$token['on1'],
					'option_selection2' => (string)$token['os1'],
				);

				$ppco_dup_processed = false;
				$transient_ppco_subscr = 's2m_ppco_'.md5('s2member_transient_ppco_subscr_'.$subscription_id);
				$ppco_dup_processed     = (bool)get_transient($transient_ppco_subscr);

				if($ppco_dup_processed)
					c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
						'ppco'            => 'checkout',
						'event'           => 'duplicate_subscription_ignored',
						'subscription_id' => $subscription_id,
						'transient'       => $transient_ppco_subscr,
					));

				if(!$ppco_dup_processed)
				{
					set_transient($transient_ppco_subscr, time(), 31556926 * 10);

					c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
						'ppco'            => 'checkout',
						'event'           => 'idempotency_subscription_set',
						'subscription_id' => $subscription_id,
						'transient'       => $transient_ppco_subscr,
						'expires_secs'    => 31556926 * 10,
					));

					$notify_url  = home_url('/?s2member_paypal_notify=1');
					$notify_post = array_merge($paypal, array(
						's2member_paypal_proxy'              => 'paypal',
						's2member_paypal_proxy_use'          => 'paypal_checkout',
						's2member_paypal_proxy_verification' => c_ws_plugin__s2member_paypal_utilities::paypal_proxy_key_gen(),
					));
					$notify_r = c_ws_plugin__s2member_utils_urls::remote($notify_url, $notify_post, array('timeout' => 20), true);

					$notify_code = !empty($notify_r['code']) ? (int)$notify_r['code'] : 0;
					$notify_msg  = !empty($notify_r['message']) ? (string)$notify_r['message'] : '';
					$notify_body = !empty($notify_r['body']) ? $notify_r['body'] : '';

					if($notify_code >= 200 && $notify_code <= 299)
						c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
							'ppco'            => 'checkout',
							'event'           => 'notify_proxy_response',
							'subscription_id' => $subscription_id,
							'url'             => $notify_url,
							'code'            => $notify_code,
							'message'         => $notify_msg,
							'body'            => $notify_body,
						));
					else
					{
						c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
							'ppco'            => 'checkout',
							'event'           => 'notify_proxy_failed',
							'subscription_id' => $subscription_id,
							'url'             => $notify_url,
							'code'            => $notify_code,
							'message'         => $notify_msg,
							'body'            => $notify_body,
						));
						echo json_encode(array('error' => 'notify_proxy_failed'));
						exit();
					}
				}

				$return_url = (string)$token['return'];
				$return_url = add_query_arg('s2member_paypal_proxy', 'paypal', $return_url);

				$return_post = array_merge($paypal, array(
					's2member_paypal_proxy'              => 'paypal',
					's2member_paypal_proxy_use'          => 'paypal_checkout',
					's2member_paypal_proxy_verification' => c_ws_plugin__s2member_paypal_utilities::paypal_proxy_key_gen(),
				));

				echo json_encode(array(
					'rtn_url'  => $return_url,
					'rtn_post' => $return_post,
				));
				exit();
			}

			if($op === 'cancel_subscription')
			{
				if(!is_user_logged_in())
				{
					c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
						'ppco'  => 'checkout',
						'event' => 'cancel_subscription_not_logged_in',
						'token' => $token,
					));

					echo json_encode(array('error' => 'not_logged_in'));
					exit();
				}
				$user_id = (int)get_current_user_id();

				$nonce = !empty($_POST['s2member_paypal_checkout_nonce']) ? trim(stripslashes((string)$_POST['s2member_paypal_checkout_nonce'])) : '';
				if(!$nonce || !wp_verify_nonce($nonce, 's2m_ppco_cancel_'.$user_id))
				{
					c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
						'ppco'   => 'checkout',
						'event'  => 'cancel_subscription_bad_nonce',
						'user_id'=> $user_id,
					));

					echo json_encode(array('error' => 'bad_nonce'));
					exit();
				}

				$token_user_id  = !empty($token['user_id']) ? (int)$token['user_id'] : 0;
				$token_subscr_id = !empty($token['subscr_id']) ? (string)$token['subscr_id'] : '';

				if(!$token_user_id || $token_user_id !== $user_id || !$token_subscr_id)
				{
					c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
						'ppco'    => 'checkout',
						'event'   => 'cancel_subscription_token_mismatch',
						'user_id' => $user_id,
						'token'   => $token,
					));

					echo json_encode(array('error' => 'token_mismatch'));
					exit();
				}

				$subscr_id = (string)get_user_option('s2member_subscr_id', $user_id);
				if(!$subscr_id || $subscr_id !== $token_subscr_id)
				{
					c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
						'ppco'       => 'checkout',
						'event'      => 'cancel_subscription_user_mismatch',
						'user_id'    => $user_id,
						'user_subscr'=> $subscr_id,
						'token_subscr'=> $token_subscr_id,
					));

					echo json_encode(array('error' => 'user_mismatch'));
					exit();
				}

				$reason = !empty($_POST['reason']) ? trim(stripslashes((string)$_POST['reason'])) : 'Cancelled by subscriber.';
				$reason = sanitize_text_field($reason);
				if(!$reason)
					$reason = 'Cancelled by subscriber.';

				$r = c_ws_plugin__s2member_paypal_utilities::paypal_checkout_subscription_cancel($subscr_id, $reason);

				$code = !empty($r['code']) ? (int)$r['code'] : 0;
				$body = !empty($r['body']) ? (string)$r['body'] : '';

				c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
					'ppco'     => 'checkout',
					'event'    => 'cancel_subscription_response',
					'user_id'  => $user_id,
					'subscr_id'=> $subscr_id,
					'code'     => $code,
					'body'     => $body,
				));

				if($code === 204 || ($code >= 200 && $code <= 299))
				{
					// Immediately feed s2Member's existing cancel handler (webhooks may be missing in MVP sites).
					$paypal = array(
						'txn_type'       => 'subscr_cancel',
						'payment_status' => 'Completed',
						'subscr_gateway' => 'paypal',

						'txn_id'    => $subscr_id,
						'subscr_id' => $subscr_id,

						// Helps some older cancel paths/logs; not required for user resolution.
						'payer_email' => (string)wp_get_current_user()->user_email,
					);

					$notify_url  = home_url('/?s2member_paypal_notify=1');
					$notify_post = array_merge($paypal, array(
						's2member_paypal_proxy'              => 'paypal',
						's2member_paypal_proxy_use'          => 'paypal_checkout',
						's2member_paypal_proxy_verification' => c_ws_plugin__s2member_paypal_utilities::paypal_proxy_key_gen(),
					));

					$notify_r = c_ws_plugin__s2member_utils_urls::remote($notify_url, $notify_post, array('timeout' => 20), true);

					$notify_code = !empty($notify_r['code']) ? (int)$notify_r['code'] : 0;
					if(!($notify_code >= 200 && $notify_code <= 299))
					{
						c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
							'ppco'        => 'checkout',
							'event'       => 'cancel_subscription_notify_failed',
							'user_id'     => $user_id,
							'subscr_id'   => $subscr_id,
							'notify_code' => $notify_code,
							'notify_msg'  => !empty($notify_r['message']) ? (string)$notify_r['message'] : '',
						));
					}

					echo json_encode(array('ok' => 1));
					exit();
				}

				echo json_encode(array('error' => 'cancel_failed'));
				exit();
			}

			if($op === 'create_order')
			{
				$order = c_ws_plugin__s2member_paypal_utilities::paypal_checkout_order_create($token);

				c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
						'ppco'  => 'checkout',
						'event' => 'create_order_response',
						'order' => $order,
						'token' => $token,
					));

				if(empty($order['id']))
				{
					c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
							'ppco'  => 'checkout',
							'event' => 'order_create_failed',
							'order' => $order,
							'token' => $token,
						));

					echo json_encode(array('error' => 'order_create_failed'));
					exit();
				}
				echo json_encode(array('order_id' => $order['id']));
				exit();
			}
			else if($op === 'capture_order')
			{
				$order_id = !empty($_POST['order_id']) ? trim(stripslashes((string)$_POST['order_id'])) : '';

				if(!$order_id)
				{
					echo json_encode(array('error' => 'missing_order_id'));
					exit();
				}
				$capture = c_ws_plugin__s2member_paypal_utilities::paypal_checkout_order_capture($order_id, $token);

				$cap0 = (!empty($capture['purchase_units'][0]['payments']['captures'][0]) && is_array($capture['purchase_units'][0]['payments']['captures'][0])) ? $capture['purchase_units'][0]['payments']['captures'][0] : array();
				c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
					'ppco'       => 'checkout',
					'event'      => 'capture_response',
					'order_id'   => $order_id,
					'status'     => !empty($capture['status']) ? (string)$capture['status'] : '',
					'capture_id' => !empty($cap0['id']) ? (string)$cap0['id'] : '',
					'amount'     => !empty($cap0['amount']['value']) ? (string)$cap0['amount']['value'] : '',
					'cc'         => !empty($cap0['amount']['currency_code']) ? (string)$cap0['amount']['currency_code'] : '',
					'payer'      => !empty($capture['payer']['email_address']) ? (string)$capture['payer']['email_address'] : '',
					'capture'    => $capture,
					'token'      => $token,
				));

				if(empty($capture['status']) || strtoupper($capture['status']) !== 'COMPLETED')
				{
					echo json_encode(array('error' => 'order_capture_failed'));
					exit();
				}

				/*
				 * Build PayPal-like variables to feed s2Member's existing IPN + Return handlers.
				 */
				$payer_email = !empty($capture['payer']['email_address']) ? (string)$capture['payer']['email_address'] : '';
				$first_name  = !empty($capture['payer']['name']['given_name']) ? (string)$capture['payer']['name']['given_name'] : '';
				$last_name   = !empty($capture['payer']['name']['surname']) ? (string)$capture['payer']['name']['surname'] : '';

				$pu_amount   = !empty($capture['purchase_units'][0]['payments']['captures'][0]['amount']['value']) ? (string)$capture['purchase_units'][0]['payments']['captures'][0]['amount']['value'] : '';
				$pu_cc       = !empty($capture['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code']) ? (string)$capture['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'] : '';
				$pu_cap_id   = !empty($capture['purchase_units'][0]['payments']['captures'][0]['id']) ? (string)$capture['purchase_units'][0]['payments']['captures'][0]['id'] : '';

				if(!$payer_email || !$pu_amount || !$pu_cc || !$pu_cap_id)
				{
					c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
							'ppco'     => 'checkout',
							'event'    => 'capture_missing_fields',
							'order_id' => $order_id,
							'capture'  => $capture,
							'token'    => $token,
						));

					echo json_encode(array('error' => 'capture_missing_fields'));
					exit();
				}

				// Extra safety: enforce token matches amount/currency/invoice/custom if provided.
				if(!empty($token['amount']) && (string)$token['amount'] !== (string)$pu_amount)
				{
					c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
						'ppco'     => 'checkout',
						'event'    => 'amount_mismatch',
						'order_id' => $order_id,
						'token'    => $token,
						'pu'       => array('amount' => $pu_amount, 'cc' => $pu_cc),
					));
					echo json_encode(array('error' => 'amount_mismatch'));
					exit();
				}
				if(!empty($token['cc']) && strtoupper((string)$token['cc']) !== strtoupper((string)$pu_cc))
				{
					c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
						'ppco'     => 'checkout',
						'event'    => 'currency_mismatch',
						'order_id' => $order_id,
						'token'    => $token,
						'pu'       => array('amount' => $pu_amount, 'cc' => $pu_cc),
					));
					echo json_encode(array('error' => 'currency_mismatch'));
					exit();
				}
				$cap_invoice_id = '';
				if(!empty($capture['purchase_units'][0]['invoice_id']))
					$cap_invoice_id = (string)$capture['purchase_units'][0]['invoice_id'];
				else if(!empty($capture['purchase_units'][0]['payments']['captures'][0]['invoice_id']))
					$cap_invoice_id = (string)$capture['purchase_units'][0]['payments']['captures'][0]['invoice_id'];

				if($cap_invoice_id && $cap_invoice_id !== (string)$token['invoice'])
				{
					c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
						'ppco'     => 'checkout',
						'event'    => 'invoice_mismatch',
						'order_id' => $order_id,
						'token'    => $token,
						'invoice'  => $cap_invoice_id,
					));
					echo json_encode(array('error' => 'invoice_mismatch'));
					exit();
				}

				$cap_custom_id = '';
				if(!empty($capture['purchase_units'][0]['custom_id']))
					$cap_custom_id = (string)$capture['purchase_units'][0]['custom_id'];
				else if(!empty($capture['purchase_units'][0]['payments']['captures'][0]['custom_id']))
					$cap_custom_id = (string)$capture['purchase_units'][0]['payments']['captures'][0]['custom_id'];

				if($cap_custom_id && !empty($token['custom']) && $cap_custom_id !== (string)$token['custom'])
				{
					c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
						'ppco'     => 'checkout',
						'event'    => 'custom_mismatch',
						'order_id' => $order_id,
						'token'    => $token,
						'custom'   => array(
							'token'   => !empty($token['custom']) ? $token['custom'] : '',
							'paypal'  => $cap_custom_id,
						),
					));
					echo json_encode(array('error' => 'custom_mismatch'));
					exit();
				}

				$paypal = array(
					'txn_type'       => 'web_accept',
					'payment_status' => 'Completed',
					'subscr_gateway' => 'paypal',

					'txn_id'         => $pu_cap_id,
					'subscr_id'      => $pu_cap_id,
					'subscr_baid'    => $pu_cap_id,
					'subscr_cid'     => $pu_cap_id,

					'mc_gross'       => $pu_amount,
					'mc_currency'    => strtoupper($pu_cc),

					'invoice'        => (string)$token['invoice'],
					'custom'         => (string)$token['custom'],
					'item_name'      => (string)$token['item_name'],
					'item_number'    => (string)$token['item_number'],

					'payer_email'    => $payer_email,
					'first_name'     => $first_name,
					'last_name'      => $last_name,

					// Preserve s2Member's tracking option fields.
					'option_name1'      => (string)$token['on0'],
					'option_selection1' => (string)$token['os0'],
					'option_name2'      => (string)$token['on1'],
					'option_selection2' => (string)$token['os1'],
				);

				// Idempotency: prevent double-processing of the same PayPal capture ID.
				$ppco_dup_processed = false;
				if($pu_cap_id)
				{
					$transient_ppco_capture = 's2m_ppco_'.md5('s2member_transient_ppco_capture_'.$pu_cap_id);
					$ppco_dup_processed     = (bool)get_transient($transient_ppco_capture);

					if(!$ppco_dup_processed)
					{
						set_transient($transient_ppco_capture, time(), 31556926 * 10);

						c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
							'ppco'         => 'checkout',
							'event'        => 'idempotency_capture_set',
							'order_id'     => $order_id,
							'txn_id'       => $pu_cap_id,
							'transient'    => $transient_ppco_capture,
							'expires_secs' => 31556926 * 10,
						));
					}
					else
						c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
							'ppco'     => 'checkout',
							'event'    => 'duplicate_capture_ignored',
							'order_id' => $order_id,
							'txn_id'   => $pu_cap_id,
						));
				}

				if(!$ppco_dup_processed)
				{
					// 1) Fire the existing IPN handler via proxy (provisions access, emails, logs, etc).
					$notify_url  = home_url('/?s2member_paypal_notify=1');
					$notify_post = array_merge($paypal, array(
						's2member_paypal_proxy'              => 'paypal',
						's2member_paypal_proxy_use'          => 'paypal_checkout',
						's2member_paypal_proxy_verification' => c_ws_plugin__s2member_paypal_utilities::paypal_proxy_key_gen(),
					));
					$notify_r = c_ws_plugin__s2member_utils_urls::remote($notify_url, $notify_post, array('timeout' => 20), true);

					$notify_code = !empty($notify_r['code']) ? (int)$notify_r['code'] : 0;
					$notify_msg  = !empty($notify_r['message']) ? (string)$notify_r['message'] : '';
					$notify_body = !empty($notify_r['body']) ? $notify_r['body'] : '';

					if($notify_code >= 200 && $notify_code <= 299)
						c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
							'ppco'     => 'checkout',
							'event'    => 'notify_proxy_response',
							'order_id' => $order_id,
							'txn_id'   => $pu_cap_id,
							'url'      => $notify_url,
							'code'     => $notify_code,
							'message'  => $notify_msg,
							'body'     => $notify_body,
						));
					else
					{
						c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
							'ppco'     => 'checkout',
							'event'    => 'notify_proxy_failed',
							'order_id' => $order_id,
							'txn_id'   => $pu_cap_id,
							'url'      => $notify_url,
							'code'     => $notify_code,
							'message'  => $notify_msg,
							'body'     => $notify_body,
						));
						echo json_encode(array('error' => 'notify_proxy_failed'));
						exit();
					}
				}

				// 2) Send the user through the existing Return handler via POST (sets cookies, thank-you UX, reg tokens, etc).
				$return_url = (string)$token['return'];
				$return_url = add_query_arg('s2member_paypal_proxy', 'paypal', $return_url);

				$return_post = array_merge($paypal, array(
					's2member_paypal_proxy'              => 'paypal',
					's2member_paypal_proxy_use'          => 'paypal_checkout',
					's2member_paypal_proxy_verification' => c_ws_plugin__s2member_paypal_utilities::paypal_proxy_key_gen(),
				));

				echo json_encode(array(
					'rtn_url'  => $return_url,
					'rtn_post' => $return_post,
				));
				exit();
			}

			echo json_encode(array('error' => 'unknown_op'));
			exit();
		}
	}
}
