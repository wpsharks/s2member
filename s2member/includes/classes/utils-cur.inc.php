<?php
/**
* Currency utilities.
*
* Copyright: © 2009-2011
* {@link http://www.websharks-inc.com/ WebSharks, Inc.}
* ( coded in the USA )
*
* Released under the terms of the GNU General Public License.
* You should have received a copy of the GNU General Public License,
* along with this software. In the main directory, see: /licensing/
* If not, see: {@link http://www.gnu.org/licenses/}.
*
* @package s2Member\Utilities
* @since 110531
*/
if(realpath(__FILE__) === realpath($_SERVER["SCRIPT_FILENAME"]))
	exit("Do not access this file directly.");
/**/
if(!class_exists("c_ws_plugin__s2member_utils_cur"))
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
				* Uses the Google® currency conversion API.
				*
				* @package s2Member\Utilities
				* @since 3.5
				*
				* @param int|str $a The amount, in ``$from``.
				* @param str $from A 3 character Currency Code.
				* @param str $to A 3 character Currency Code.
				* @return float|str|bool A numeric amount in ``$to``,
				* 	after having been converted. Else false.
				*
				* @see http://www.techmug.com/ajax-currency-converter-with-google-api/
				*/
				public static function convert($a = FALSE, $from = FALSE, $to = FALSE)
					{
						if(is_numeric($a) && strlen($from = strtoupper($from)) === 3 && strlen($to = strtoupper($to)) === 3)
							{
								$q = number_format($a, 2, ".", "").$from."=?".$to;
								$api = "http://www.google.com/ig/calculator?hl=en&q=".urlencode($q);
								/**/
								if(($json = preg_replace('/([{,])\s*([^"]+?)\s*:/', '$1"$2":', c_ws_plugin__s2member_utils_urls::remote($api))) && is_array($json = json_decode($json, true)) && !empty($json["icc"]) && isset($json["rhs"]) && strlen($json["rhs"]))
									{
										if(is_numeric($c_a = preg_replace("/ .*$/", "", trim($json["rhs"]))) && $c_a >= 0)
											return number_format($c_a, 2, ".", "");
									}
							}
						/**/
						return false; /* Default return value. */
					}
				/**
				* Converts Currency Codes to Currency Symbols.
				*
				* Defaults to the `$` dollar sign.
				*
				* @package s2Member\Utilities
				* @since 110531
				*
				* @param str $currency Expects a 3 character Currency Code.
				* @return str A Currency Symbol. Defaults to the `$` sign.
				*/
				public static function symbol($currency = FALSE)
					{
						$symbols["AUD"] = "$"; /* Australian Dollar */
						$symbols["BRL"] = "R$"; /* Brazilian Real */
						$symbols["CAD"] = "$"; /* Canadian Dollar */
						$symbols["CZK"] = "Kč"; /* Czech Koruna */
						$symbols["DKK"] = "kr"; /* Danish Krone */
						$symbols["EUR"] = "€"; /* Euro */
						$symbols["HKD"] = "$"; /* Hong Kong Dollar */
						$symbols["HUF"] = "Ft"; /* Hungarian Forint */
						$symbols["ILS"] = "₪"; /* Israeli New Sheqel */
						$symbols["JPY"] = "¥"; /* Japanese Yen */
						$symbols["MYR"] = "RM"; /* Malaysian Ringgit */
						$symbols["MXN"] = "$"; /* Mexican Peso */
						$symbols["NOK"] = "kr"; /* Norwegian Krone */
						$symbols["NZD"] = "$"; /* New Zealand Dollar */
						$symbols["PHP"] = "Php"; /* Philippine Peso */
						$symbols["PLN"] = "zł"; /* Polish Zloty */
						$symbols["GBP"] = "£"; /* Pound Sterling */
						$symbols["SGD"] = "$"; /* Singapore Dollar */
						$symbols["SEK"] = "kr"; /* Swedish Krona */
						$symbols["CHF"] = "CHF"; /* Swiss Franc */
						$symbols["TWD"] = "NT$"; /* Taiwan New Dollar */
						$symbols["THB"] = "฿"; /* Thai Baht */
						$symbols["USD"] = "$"; /* U.S. Dollar */
						/**/
						if(($currency = strtoupper($currency)) && !empty($symbols[$currency]))
							return $symbols[$currency];
						/**/
						else /* Else `$` sign. */
						return "$";
					}
			}
	}
?>