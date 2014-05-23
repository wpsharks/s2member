<?php
/**
 * Currency utilities.
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
 * @since 110531
 */
if(realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME']))
	exit('Do not access this file directly.');

if(!class_exists('c_ws_plugin__s2member_utils_cur'))
{
	/**
	 * Currency utilities.
	 *
	 * @package s2Member\Utilities
	 * @since 3.5
	 */
	class c_ws_plugin__s2member_utils_cur
	{
		/**
		 * Currency converter.
		 *
		 * Uses the Google currency conversion API.
		 *
		 * @package s2Member\Utilities
		 * @since 3.5
		 *
		 * @param int|float|string $a The amount, in ``$from``.
		 * @param string           $from Three character currency code.
		 * @param string           $to Three character currency code.
		 *
		 * @return string A numeric amount in ``$to``, after having been converted. Else false.
		 */
		public static function convert($a = 0, $from = '', $to = '')
		{
			if(is_numeric($a) && strlen($from) === 3 && strlen($to) === 3)
			{
				$q        = strtoupper($from.'-'.$to); // Also need this to test the return value.
				$endpoint = 'http://www.freecurrencyconverterapi.com/api/convert?q='.urlencode($q).'&compact=y';

				if(($json = c_ws_plugin__s2member_utils_urls::remote($endpoint))
				   && is_object($json = json_decode($json)) && isset($json->{$q}->val)
				   && is_float($conversion = (float)$a * (float)$json->{$q}->val)
				) return number_format($conversion, 2, '.', '');
			}
			return ''; // Default return value.
		}

		/**
		 * Converts Currency Codes to Currency Symbols.
		 *
		 * Defaults to the `$` dollar sign.
		 *
		 * @package s2Member\Utilities
		 * @since 110531
		 *
		 * @param string $currency Expects a 3 character Currency Code.
		 *
		 * @return string A Currency Symbol. Defaults to the `$` sign.
		 */
		public static function symbol($currency = '')
		{
			$symbols['AUD'] = '$'; // Australian Dollar
			$symbols['BRL'] = 'R$'; // Brazilian Real
			$symbols['CAD'] = '$'; // Canadian Dollar
			$symbols['CZK'] = 'Kč'; // Czech Koruna
			$symbols['DKK'] = 'kr'; // Danish Krone
			$symbols['EUR'] = '€'; // Euro
			$symbols['HKD'] = '$'; // Hong Kong Dollar
			$symbols['HUF'] = 'Ft'; // Hungarian Forint
			$symbols['ILS'] = '₪'; // Israeli New Sheqel
			$symbols['JPY'] = '¥'; // Japanese Yen
			$symbols['MYR'] = 'RM'; // Malaysian Ringgit
			$symbols['MXN'] = '$'; // Mexican Peso
			$symbols['NOK'] = 'kr'; // Norwegian Krone
			$symbols['NZD'] = '$'; // New Zealand Dollar
			$symbols['PHP'] = 'Php'; // Philippine Peso
			$symbols['PLN'] = 'zł'; // Polish Zloty
			$symbols['GBP'] = '£'; // Pound Sterling
			$symbols['SGD'] = '$'; // Singapore Dollar
			$symbols['SEK'] = 'kr'; // Swedish Krona
			$symbols['CHF'] = 'CHF'; // Swiss Franc
			$symbols['TWD'] = 'NT$'; // Taiwan New Dollar
			$symbols['THB'] = '฿'; // Thai Baht
			$symbols['USD'] = '$'; // U.S. Dollar

			if(($currency = strtoupper($currency)) && !empty($symbols[$currency]))
				return $symbols[$currency];
			return '$'; // Else `$` sign (default value).
		}
	}
}