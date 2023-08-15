<?php
// @codingStandardsIgnoreFile
/**
 * Shortcode `[s2If /]` (inner processing routines).
 *
 * Copyright: © 2009-2011
 * {@link http://websharks-inc.com/ WebSharks, Inc.}
 * (coded in the USA)
 *
 * Released under the terms of the GNU General Public License.
 * You should have received a copy of the GNU General Public License,
 * along with this software. In the main directory, see: /licensing/
 * If not, see: {@link http://www.gnu.org/licenses/}.
 *
 * @package s2Member\s2If
 * @since 3.5
 */
if(!defined('WPINC')) // MUST have WordPress.
	exit('Do not access this file directly.');

if(!class_exists('c_ws_plugin__s2member_sc_if_conds_in'))
{
	/**
	 * Shortcode `[s2If /]` (inner processing routines).
	 *
	 * @package s2Member\s2If
	 * @since 3.5
	 */
	class c_ws_plugin__s2member_sc_if_conds_in
	{
		/**
		 * Handles the Shortcode for: `[s2If /]`.
		 *
		 * These Shortcodes are also safe to use on a Multisite Blog Farm.
		 *
		 * Is Multisite Networking enabled? Please keep the following in mind.
		 * ``current_user_can()``, will ALWAYS return true for a Super Admin!
		 *   *(this can be confusing when testing conditionals)*.
		 *
		 * If you're running a Multisite Blog Farm, you can Filter this array:
		 *   `ws_plugin__s2member_sc_if_conditionals_blog_farm_safe`
		 *   ``$blog_farm_safe``
		 *
		 * @package s2Member\s2If
		 * @since 3.5
		 *
		 * @attaches-to ``add_shortcode('s2If')`` + _s2If, __s2If, ___s2If for nesting.
		 *
		 * @param array  $attr An array of Attributes.
		 * @param string $content Content inside the Shortcode.
		 * @param string $shortcode The actual Shortcode name itself.
		 *
		 * @return string The ``$content`` if true, else an empty string.
		 *
		 * @todo Add support for nested AND/OR conditionals inside the ONE Shortcode.
		 * @todo Address possible security issue on sites with multiple editors, some of which should not have access to this feature.
		 */
		public static function sc_if_conditionals($attr = array(), $content = '', $shortcode = '')
		{
			foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
			do_action('ws_plugin__s2member_before_sc_if_conditionals', get_defined_vars());
			unset($__refs, $__v); // Allows variables to be modified by reference.

			c_ws_plugin__s2member_no_cache::no_cache_constants(true);

			$pro_is_installed = c_ws_plugin__s2member_utils_conds::pro_is_installed(); // Has pro version?

			//230811 Whitelist of conditional functions
			$blog_farm_safe = array(
				'comments_open',
				'current_user_can',
				'current_user_can_for_blog',
				'current_user_cannot',
				'current_user_cannot_for_blog',
				'current_user_days_to_eot_less_than',
				'current_user_gateway_is',
				'current_user_is',
				'current_user_is_for_blog',
				'current_user_is_not',
				'current_user_is_not_for_blog',
				'has_excerpt',
				'has_post_thumbnail',
				'has_tag',
				'has_term',
				'in_category',
				'in_the_loop',
				'is_404',
				'is_active_sidebar',
				'is_admin',
				'is_archive',
				'is_attachment',
				'is_author',
				'is_blog_admin',
				'is_category',
				'is_child_theme',
				'is_comments_popup',
				'is_customize_preview',
				'is_date',
				'is_day',
				'is_feed',
				'is_front_page',
				'is_home',
				'is_main_site',
				'is_month',
				'is_multi_author',
				'is_multisite',
				'is_network_admin',
				'is_page',
				'is_page_template',
				'is_paged',
				'is_preview',
				'is_rtl',
				'is_search',
				'is_single',
				'is_singular',
				'is_sticky',
				'is_super_admin',
				'is_tag',
				'is_tax',
				'is_time',
				'is_trackback',
				'is_user_admin',
				'is_user_logged_in',
				'is_user_not_logged_in',
				'is_year',
				'pings_open',
				'user_can',
				'user_cannot',
				'user_is',
				'user_is_not',
			);
			$blog_farm_safe = apply_filters('ws_plugin__s2member_sc_if_conditionals_blog_farm_safe', $blog_farm_safe, get_defined_vars());

			//230814 Custom whitelist (pro)
			if ($pro_is_installed && !empty($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["sc_conds_whitelist"])) {
				$sc_conds_whitelist = explode(',', $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["sc_conds_whitelist"]);
				foreach ($sc_conds_whitelist as $white_func) {
					$white_func = trim(strtolower($white_func));
					if (function_exists($white_func)) {
            $blog_farm_safe[] = $white_func;
					}
				}
			}

			$sc_conds_allow_arbitrary_php = $GLOBALS['WS_PLUGIN__']['s2member']['o']['sc_conds_allow_arbitrary_php'];
			if(!$pro_is_installed || (is_multisite() && c_ws_plugin__s2member_utils_conds::is_multisite_farm() && !is_main_site()))
				$sc_conds_allow_arbitrary_php = FALSE; // Always disallow on child blogs of a blog farm.

			$attr =  // Trim quote entities to prevent issues in messy editors.
				c_ws_plugin__s2member_utils_strings::trim_qts_deep((array)$attr);

			$content_if      = $content_else = NULL; // Initialize.
			$shortcode_depth = strspn($shortcode, '_'); // Based on a zero index.
			$else_tag        = '['.str_repeat('_', $shortcode_depth).'else]'; // e.g., [else], [_else], [__else]

			if(strpos($content, $else_tag) !== FALSE && $pro_is_installed)
				list($content_if, $content_else) = explode($else_tag, $content, 2);

			# Arbitrary PHP code via the `php` attribute...

			if($sc_conds_allow_arbitrary_php && isset($attr['php']))
			{
				$attr['php'] = str_replace(array('&lt;', '&gt;', '&amp;'), array('<', '>', '&'), $attr['php']);

				if(($condition_succeeded = c_ws_plugin__s2member_sc_if_conds_in::evl($attr['php'])))
					$condition_content = isset($content_if) ? $content_if : $content;
				else $condition_content = isset($content_else) ? $content_else : '';

				if($condition_content) $condition_content = c_ws_plugin__s2member_utils_strings::trim_html($condition_content);

				return do_shortcode(apply_filters('ws_plugin__s2member_sc_if_conditionals', $condition_content, get_defined_vars()));
			}
			else if(isset($attr['php'])) // Site owner is trying to use `php`, but it's NOT allowed on this installation.
			{
				c_ws_plugin__s2member_sc_if_conds_in::warning('s2If syntax error. Simple Conditionals are not currently configured to allow arbitrary PHP code evaluation.');
				return ''; // Return now; empty string in this case.
			}
			# Default behavior otherwise...

			foreach($attr as $attr_key => $attr_value) // Detects and removes logical attributes.
				// It's NOT possible to mix logic. You MUST stick to one type of logic or another.
				// If both types of logic are needed, you MUST use two different Shortcodes.
				if(preg_match('/^(&&|&amp;&amp;|&#038;&#038;|AND|\|\||OR|[\!\=\<\>]+)$/i', $attr_value))
				{ // Stick with AND/OR. Ampersands are corrupted by the Visual Editor.

					$logicals[] = strtolower($attr_value); // Place all logicals into an array here.
					unset($attr[$attr_key]); // ^ Detect logic here. We'll use the first key #0.

					if(preg_match('/^[\!\=\<\>]+$/i', $attr_value)) // Error on these operators.
					{
						c_ws_plugin__s2member_sc_if_conds_in::warning('s2If, invalid operator [ '.$attr_value.' ]. Simple Conditionals cannot process operators like ( == != <> ). Please use Advanced (PHP) Conditionals instead.');
						return ''; // Return now; empty string in this case.
					}
				}
			if(!empty($logicals) && is_array($logicals) && count(array_unique($logicals)) > 1)
			{
				c_ws_plugin__s2member_sc_if_conds_in::warning('s2If, AND/OR malformed conditional logic. It\'s NOT possible to mix logic using AND/OR combinations. You MUST stick to one type of logic or another. If both types of logic are needed, you MUST use two different Shortcode expressions. Or, use Advanced (PHP) Conditionals instead.');
				return ''; // Return now; empty string in this case.
			}
			$conditional_logic = (!empty($logicals) && is_array($logicals) && preg_match('/^(\|\||OR)$/i', $logicals[0])) ? 'OR' : 'AND';

			foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
			do_action('ws_plugin__s2member_before_sc_if_conditionals_after_conditional_logic', get_defined_vars());
			unset($__refs, $__v); // Allows variables to be modified by reference.

			if($conditional_logic === 'AND') // This is the AND variation. This routine analyzes conditionals using AND logic (the default behavior).
			{
				foreach($attr as $attr_value) // This is the AND variation. This routine analyzes conditionals using AND logic (the default behavior).
				{
					if(preg_match('/^(\!?)(.+?)(\()(.*?)(\))$/', $attr_value, $m) && ($exclamation = $m[1]) !== 'nill' && ($conditional = $m[2]) && ($attr_args = preg_replace('/['."\r\n\t".'\s]/', '', $m[4])) !== 'nill')
					{
						if(!is_multisite() || !c_ws_plugin__s2member_utils_conds::is_multisite_farm() || is_main_site() || !(preg_match('/[\$\(\)]/', $attr_args) || preg_match('/new['."\r\n\t".'\s]/i', $attr_args)))
						{
							if(is_array($args = preg_split('/[;,]+/', $attr_args, 0, PREG_SPLIT_NO_EMPTY))) // Convert all arguments into an array. And take note; possibly into an empty array.
							{
								if((!is_multisite() || !c_ws_plugin__s2member_utils_conds::is_multisite_farm() || is_main_site()) && in_array(strtolower($conditional), $blog_farm_safe))
								{
									$test = ($exclamation) ? FALSE : TRUE; // If !exclamation (false) otherwise this defaults to true.

									if(preg_match('/^\{(.*?)\}$/', $attr_args)) // Single argument passed as an array.
									{
										if($test === TRUE && !c_ws_plugin__s2member_sc_if_conds_in::safer_call_func($conditional, $args))
										{
											$condition_failed = TRUE;
											break;
										}
										else if($test === FALSE && c_ws_plugin__s2member_sc_if_conds_in::safer_call_func($conditional, $args))
										{
											$condition_failed = TRUE;
											break;
										}
									}
									else if(empty($args)) // No arguments at all.
									{
										if($test === TRUE && !c_ws_plugin__s2member_sc_if_conds_in::safer_call_func($conditional))
										{
											$condition_failed = TRUE;
											break;
										}
										else if($test === FALSE && c_ws_plugin__s2member_sc_if_conds_in::safer_call_func($conditional))
										{
											$condition_failed = TRUE;
											break;
										}
									}
									else if($test === TRUE && !c_ws_plugin__s2member_sc_if_conds_in::safer_call_func($conditional, $args))
									{
										$condition_failed = TRUE;
										break;
									}
									else if($test === FALSE && c_ws_plugin__s2member_sc_if_conds_in::safer_call_func($conditional, $args))
									{
										$condition_failed = TRUE;
										break;
									}
								}
								else
								{
									c_ws_plugin__s2member_sc_if_conds_in::warning('s2If, unsafe conditional function [ '.$attr_value.' ]');
									return ''; // Return now; empty string in this case.
								}
							}
							else
							{
								c_ws_plugin__s2member_sc_if_conds_in::warning('s2If, conditional args are NOT an array [ '.$attr_value.' ]');
								return ''; // Return now; empty string in this case.
							}
						}
						else
						{
							c_ws_plugin__s2member_sc_if_conds_in::warning('s2If, unsafe conditional args [ '.$attr_value.' ]');
							return ''; // Return now; empty string in this case.
						}
					}
					else
					{
						c_ws_plugin__s2member_sc_if_conds_in::warning('s2If, malformed conditional [ '.$attr_value.' ]');
						return ''; // Return now; empty string in this case.
					}
				}
				if(!empty($condition_failed))
					$condition_content = isset($content_else) ? $content_else : '';
				else $condition_content = isset($content_if) ? $content_if : $content;

				if($condition_content) $condition_content = c_ws_plugin__s2member_utils_strings::trim_html($condition_content);

				return do_shortcode(apply_filters('ws_plugin__s2member_sc_if_conditionals', $condition_content, get_defined_vars()));
			}
			else if($conditional_logic === 'OR') // This is the OR variation. This routine analyzes conditionals using OR logic, instead of AND logic.
			{
				foreach($attr as $attr_value) // This is the OR variation. This routine analyzes conditionals using OR logic, instead of AND logic.
				{
					if(preg_match('/^(\!?)(.+?)(\()(.*?)(\))$/', $attr_value, $m) && ($exclamation = $m[1]) !== 'nill' && ($conditional = $m[2]) && ($attr_args = preg_replace('/['."\r\n\t".'\s]/', '', $m[4])) !== 'nill')
					{
						if(!is_multisite() || !c_ws_plugin__s2member_utils_conds::is_multisite_farm() || is_main_site() || !(preg_match('/[\$\(\)]/', $attr_args) || preg_match('/new['."\r\n\t".'\s]/i', $attr_args)))
						{
							if(is_array($args = preg_split('/[;,]+/', $attr_args, 0, PREG_SPLIT_NO_EMPTY))) // Convert all arguments into an array. And take note; possibly into an empty array.
							{
								if((!is_multisite() || !c_ws_plugin__s2member_utils_conds::is_multisite_farm() || is_main_site()) && in_array(strtolower($conditional), $blog_farm_safe))
								{
									$test = ($exclamation) ? FALSE : TRUE; // If !exclamation (false) otherwise this defaults to true.

									if(preg_match('/^\{(.*?)\}$/', $attr_args)) // Single argument passed as an array.
									{
										if($test === TRUE && c_ws_plugin__s2member_sc_if_conds_in::safer_call_func($conditional, $args))
										{
											$condition_succeeded = TRUE;
											break;
										}
										else if($test === FALSE && !c_ws_plugin__s2member_sc_if_conds_in::safer_call_func($conditional, $args))
										{
											$condition_succeeded = TRUE;
											break;
										}
									}
									else if(empty($args)) // No arguments at all.
									{
										if($test === TRUE && c_ws_plugin__s2member_sc_if_conds_in::safer_call_func($conditional))
										{
											$condition_succeeded = TRUE;
											break;
										}
										else if($test === FALSE && !c_ws_plugin__s2member_sc_if_conds_in::safer_call_func($conditional))
										{
											$condition_succeeded = TRUE;
											break;
										}
									}
									else if($test === TRUE && c_ws_plugin__s2member_sc_if_conds_in::safer_call_func($conditional, $args))
									{
										$condition_succeeded = TRUE;
										break;
									}
									else if($test === FALSE && !c_ws_plugin__s2member_sc_if_conds_in::safer_call_func($conditional, $args))
									{
										$condition_succeeded = TRUE;
										break;
									}
								}
								else
								{
									c_ws_plugin__s2member_sc_if_conds_in::warning('s2If, unsafe conditional function [ '.$attr_value.' ]');
									return ''; // Return now; empty string in this case.
								}
							}
							else
							{
								c_ws_plugin__s2member_sc_if_conds_in::warning('s2If, conditional args are NOT an array [ '.$attr_value.' ]');
								return ''; // Return now; empty string in this case.
							}
						}
						else
						{
							c_ws_plugin__s2member_sc_if_conds_in::warning('s2If, unsafe conditional args [ '.$attr_value.' ]');
							return ''; // Return now; empty string in this case.
						}
					}
					else
					{
						c_ws_plugin__s2member_sc_if_conds_in::warning('s2If, malformed conditional [ '.$attr_value.' ]');
						return ''; // Return now; empty string in this case.
					}
				}
				if(!empty($condition_succeeded))
					$condition_content = isset($content_if) ? $content_if : $content;
				else $condition_content = isset($content_else) ? $content_else : '';

				if($condition_content) $condition_content = c_ws_plugin__s2member_utils_strings::trim_html($condition_content);

				return do_shortcode(apply_filters('ws_plugin__s2member_sc_if_conditionals', $condition_content, get_defined_vars()));
			}
			return ''; // Default return value.
		}

		/**
		 * Sandbox for arbitrary PHP code evaluation in `[s2If/]` shortcodes.
		 *
		 * @package s2Member\s2If
		 * @since 140326
		 *
		 * @param string $expression PHP expression.
		 *
		 * @return bool TRUE if condition succeed; else FALSE.
		 */
		public static function evl($expression)
		{
			// Buffer the output.
			ob_start();
			$result = eval('return (' . (string)$expression . ');');
			$output = ob_get_clean();
			
			// Return bool true if result true or there's output.
			return ((bool)!empty($output) || (bool)$result);
		}

		/**
		 * Warning handler for s2If problems.
		 * 
		 * Instead of trigger_error, which prevents the page from loading, 
		 * this will simply not show the s2If block's content, letting the rest load,
		 * and log the error, with the URI where it happened.
		 *
		 * @package s2Member\s2If
		 * @since 230814
		 *
		 * @param string Warning message.
		 *
		 * @return
		 * @todo Asynch admin notice, enqueued for the admin to see later
		 */
		public static function warning($message)
		{
			$log_message = $message . ' - URI: ' . esc_url($_SERVER['REQUEST_URI']);
			error_log($log_message);
		}

		/**
		 * Do a safer call_user_func and call_user_func_array, 
		 * with sanitation and output buffering.
		 *
		 * @package s2Member\s2If
		 * @since 230814
		 *
		 * @param string $conditional The callable function or method to be invoked.
		 * @param array $args Optional array of arguments to pass to the function.
		 *
		 * @return bool The boolean result of the condition.
		 */
		public static function safer_call_func($conditional, $args = array())
		{
			// Sanitize
			$chars = '\'`"\=:;*<>()[]/\\|?!&%#';
			$trans = '                        ';
			$conditional = strtr($conditional, $chars, $trans);
			if (!empty($args)) {
				foreach ($args as $k => $arg) {
					$args[$k] = strtr($arg, $chars, $trans);
				}
			}

			// Buffer output
			ob_start();
			$result = call_user_func_array($conditional, $args);
			$output = ob_get_clean();

			// Return bool true if result true or there's output.
			return ((bool)!empty($output) || (bool)$result);
		}
	}
}
