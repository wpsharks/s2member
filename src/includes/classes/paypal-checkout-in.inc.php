<?php
// @codingStandardsIgnoreFile
/**
 * s2Member's PayPal Checkout (REST) handler (create/capture).
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

			if(!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_debug_logs']))
				$GLOBALS['ws_plugin__s2member_log'] = 'paypal';

			nocache_headers();
			header('Content-Type: application/json; charset=UTF-8');

			$op = !empty($_POST['s2member_paypal_checkout_op']) ? strtolower(trim(stripslashes((string)$_POST['s2member_paypal_checkout_op']))) : '';
			$t  = !empty($_POST['s2member_paypal_checkout_t'])  ? trim(stripslashes((string)$_POST['s2member_paypal_checkout_t'])) : '';

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

			if($op === 'create_order')
			{
				$order = c_ws_plugin__s2member_paypal_utilities::paypal_checkout_order_create($token);

				if(empty($order['id']))
				{
					echo json_encode(array('error' => 'order_create_failed', 'debug' => $order));
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

				if(empty($capture['status']) || strtoupper($capture['status']) !== 'COMPLETED')
				{
					echo json_encode(array('error' => 'order_capture_failed', 'debug' => $capture));
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
					echo json_encode(array('error' => 'capture_missing_fields', 'debug' => $capture));
					exit();
				}

				// Extra safety: enforce token matches amount/currency/invoice/custom if provided.
				if(!empty($token['amount']) && (string)$token['amount'] !== (string)$pu_amount)
				{
					echo json_encode(array('error' => 'amount_mismatch'));
					exit();
				}
				if(!empty($token['cc']) && strtoupper((string)$token['cc']) !== strtoupper((string)$pu_cc))
				{
					echo json_encode(array('error' => 'currency_mismatch'));
					exit();
				}
				if(!empty($capture['purchase_units'][0]['invoice_id']) && (string)$capture['purchase_units'][0]['invoice_id'] !== (string)$token['invoice'])
				{
					echo json_encode(array('error' => 'invoice_mismatch'));
					exit();
				}
				if(!empty($capture['purchase_units'][0]['custom_id']) && !empty($token['custom']) && (string)$capture['purchase_units'][0]['custom_id'] !== (string)$token['custom'])
				{
					echo json_encode(array('error' => 'custom_mismatch'));
					exit();
				}

				$paypal = array(
					'txn_type'       => 'web_accept',
					'payment_status' => 'Completed',

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

				// 1) Fire the existing IPN handler via proxy (provisions access, emails, logs, etc).
				$notify_url  = home_url('/?s2member_paypal_notify=1');
				$notify_post = array_merge($paypal, array(
					's2member_paypal_proxy'              => 'paypal_checkout',
					's2member_paypal_proxy_verification' => c_ws_plugin__s2member_paypal_utilities::paypal_proxy_key_gen(),
				));
				c_ws_plugin__s2member_utils_urls::remote($notify_url, $notify_post, array('timeout' => 20));

				// 2) Send the user through the existing Return handler via POST (sets cookies, thank-you UX, reg tokens, etc).
				$return_url = (string)$token['return'];
				$return_url = add_query_arg('s2member_paypal_proxy', 'paypal_checkout', $return_url);

				$return_post = array_merge($paypal, array(
					's2member_paypal_proxy'              => 'paypal_checkout',
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
