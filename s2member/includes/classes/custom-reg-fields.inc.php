<?php
/**
* Custom Registration/Profile Fields for s2Member.
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
* @package s2Member\Custom_Reg_Fields
* @since 3.5
*/
if(realpath(__FILE__) === realpath($_SERVER["SCRIPT_FILENAME"]))
	exit("Do not access this file directly.");

if(!class_exists("c_ws_plugin__s2member_custom_reg_fields"))
	{
		/**
		* Custom Registration/Profile Fields for s2Member.
		*
		* @package s2Member\Custom_Reg_Fields
		* @since 3.5
		*/
		class c_ws_plugin__s2member_custom_reg_fields
			{
				/**
				* Generates all Custom Fields.
				*
				* @package s2Member\Custom_Reg_Fields
				* @since 3.5
				*
				* @param str $_function Function calling upon this routine.
				* @param array $_field The Field array of configuration options.
				* @param str $_name_prefix The `name=""` attribute prefix.
				* @param str $_id_prefix The `id=""` attribute prefix.
				* @param str $_classes Optional. String of space separated classes that will go inside the Field's `class=""` attribute.
				* @param str $_styles Optional. String of CSS styles that will go inside the Field's `style=""` attribute.
				* @param str|int $_tabindex. Optional numeric tabindex for the `tabindex=""` attribute.
				* @param str $_attrs Optional. Some additional Field attributes and values.
				* @param array $_submission Optional. But should be passed in with any submission data related to this Field. For instance, you might pass in ``$_POST``.
				* @param str|array $_value Optional. The value of this Field, either by default, or from the ``$_submission`` array.
				* @param str $_editable_context Optional. One of `profile|profile-view|registration`.
				* @return str The resulting Custom Field, in HTML format.
				*/
				public static function custom_field_gen($_function = FALSE, $_field = FALSE, $_name_prefix = FALSE, $_id_prefix = FALSE, $_classes = FALSE, $_styles = FALSE, $_tabindex = FALSE, $_attrs = FALSE, $_submission = FALSE, $_value = FALSE, $_editable_context = FALSE)
					{
						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("ws_plugin__s2member_before_custom_field_gen", get_defined_vars());
						unset /* Unset defined __refs, __v. */($__refs, $__v);

						if(!($gen = "") && $_function && is_array($field = $_field) && !empty($field["type"]) && !empty($field["id"]) && $_name_prefix && $_id_prefix)
							{
								foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
								do_action("ws_plugin__s2member_during_custom_field_gen_before", get_defined_vars());
								unset /* Unset defined __refs, __v. */($__refs, $__v);

								$field_var = preg_replace("/[^a-z0-9]/i", "_", strtolower($field["id"]));
								$field_id_class = preg_replace("/_/", "-", $field_var);

								$name_suffix = (preg_match("/\[$/", $_name_prefix)) ? ']' : '';
								$field_name = trim($_name_prefix.$field_var.$name_suffix);

								$common = /* Common attributes. */ '';
								$common .= ' name="'.esc_attr($field_name).'"';
								$common .= ' id="'.esc_attr($_id_prefix.$field_id_class).'"';
								$common .= ((!empty($field["required"]) && $field["required"] === "yes") ? ' aria-required="true"' : '');
								$common .= ((strlen($_tabindex)) ? ' tabindex="'.esc_attr($_tabindex).'"' : /* No tabindex if empty. */ '');
								$common .= (( /* Certain data expected? */!empty($field["expected"])) ? ' data-expected="'.esc_attr($field["expected"]).'"' : '');
								$common .= (($_editable_context === "profile-view" || ($_editable_context === "profile" && !empty($field["editable"]) && strpos($field["editable"], "no") === 0)) ? ' disabled="disabled"' : '');
								$common .= (($_classes || !empty($field["classes"])) ? ' class="'.esc_attr(trim($_classes.((!empty($field["classes"])) ? ' '.$field["classes"] : ''))).'"' : '');
								$common .= (($_styles || !empty($field["styles"])) ? ' style="'.esc_attr(trim($_styles.((!empty($field["styles"])) ? ' '.$field["styles"] : ''))).'"' : '');
								$common .= (($_attrs || !empty($field["attrs"])) ? ' '.trim($_attrs.((!empty($field["attrs"])) ? ' '.$field["attrs"] : '')) : '');

								if($field["type"] === "text")
									{
										if($_editable_context === "profile-view")
											$gen = esc_html((string)$_value);

										else // Else handle normally.
											{
												$gen = '<input type="text" maxlength="100" autocomplete="off"';
												$gen .= ' value="'.format_to_edit((!$_submission && isset($field["deflt"]) && strlen((string)$field["deflt"])) ? (string)$field["deflt"] : (string)$_value).'"';
												$gen .= $common.' />';
											}
									}
								else if($field["type"] === "textarea")
									{
										if($_editable_context === "profile-view")
											$gen = nl2br(esc_html((string)$_value));

										else // Else handle normally.
											{
												$gen = '<textarea rows="3"'.$common.'>';
												$gen .= format_to_edit((!$_submission && isset($field["deflt"]) && strlen((string)$field["deflt"])) ? (string)$field["deflt"] : (string)$_value);
												$gen .= '</textarea>';
											}
									}
								else if($field["type"] === "select" && !empty($field["options"]))
									{
										if($_editable_context === "profile-view")
											{
												foreach(preg_split("/[\r\n\t]+/", $field["options"]) as $option_line)
													{
														list($option_value, $option_label, $option_default) = c_ws_plugin__s2member_utils_strings::trim_deep(preg_split("/\|/", trim($option_line)));
														if($option_value === (string)$_value)
															{
																$gen = $option_label;
																break;
															}
													}
											}
										else // Else handle normally.
											{
												$gen = '<select'.$common.'>';
												$selected_default_option = false;
												foreach(preg_split("/[\r\n\t]+/", $field["options"]) as $option_line)
													{
														list($option_value, $option_label, $option_default) = c_ws_plugin__s2member_utils_strings::trim_deep(preg_split("/\|/", trim($option_line)));
														$gen .= '<option value="'.esc_attr($option_value).'"'.(((($option_default && !$_submission) || ($option_value === (string)$_value && !$selected_default_option)) && ($selected_default_option = true)) ? ' selected="selected"' : '').'>'.$option_label.'</option>';
													}
												$gen .= '</select>';
											}
									}
								else if($field["type"] === "selects" && !empty($field["options"]))
									{
										if($_editable_context === "profile-view")
											{
												foreach(preg_split("/[\r\n\t]+/", $field["options"]) as $option_line)
													{
														list($option_value, $option_label, $option_default) = c_ws_plugin__s2member_utils_strings::trim_deep(preg_split("/\|/", trim($option_line)));
														if(in_array($option_value, (array)$_value))
															$gen .= $option_label.", ";
													}
												$gen = c_ws_plugin__s2member_utils_strings::trim($gen, 0, ",");
											}
										else // Else handle normally.
											{
												$common = preg_replace('/ name\="(.+?)"/', ' name="$1[]"', $common);
												$common = preg_replace('/ style\="(.+?)"/', ' style="height:auto; $1"', $common);

												$gen = '<select multiple="multiple" size="3"'.$common.'>';
												foreach(preg_split("/[\r\n\t]+/", $field["options"]) as $option_line)
													{
														list($option_value, $option_label, $option_default) = c_ws_plugin__s2member_utils_strings::trim_deep(preg_split("/\|/", trim($option_line)));
														$gen .= '<option value="'.esc_attr($option_value).'"'.((($option_default && !$_submission) || in_array($option_value, (array)$_value)) ? ' selected="selected"' : '').'>'.$option_label.'</option>';
													}
												$gen .= '</select>';
											}
									}
								else if($field["type"] === "checkbox" && !empty($field["label"]))
									{
										if($_editable_context === "profile-view")
											$gen = ((string)$_value) ? "yes" : "no";

										else // Else handle normally.
											{
												$gen = '<input type="checkbox" value="1"';
												$gen .= (((string)$_value) ? ' checked="checked"' : '');
												$gen .= $common.' /><label for="'.esc_attr($_id_prefix.$field_id_class).'" style="display:inline !important; margin:0 !important;">'.$field["label"].'</label>';
											}
									}
								else if($field["type"] === "pre_checkbox" && !empty($field["label"]))
									{
										if($_editable_context === "profile-view")
											$gen = ((string)$_value) ? "yes" : "no";

										else // Else handle normally.
											{
												$gen = '<input type="checkbox" value="1"';
												$gen .= ((!$_submission || (string)$_value) ? ' checked="checked"' : '');
												$gen .= $common.' /><label for="'.esc_attr($_id_prefix.$field_id_class).'" style="display:inline !important; margin:0 !important;">'.$field["label"].'</label>';
											}
									}
								else if($field["type"] === "checkboxes" && !empty($field["options"]))
									{
										if($_editable_context === "profile-view")
											{
												foreach(preg_split("/[\r\n\t]+/", $field["options"]) as $i => $option_line)
													{
														list($option_value, $option_label, $option_default) = c_ws_plugin__s2member_utils_strings::trim_deep(preg_split("/\|/", trim($option_line)));
														if(in_array($option_value, (array)$_value))
															$gen .= $option_label.", ";
													}
												$gen = c_ws_plugin__s2member_utils_strings::trim($gen, 0, ",");
											}
										else // Else handle normally.
											{
												$common = preg_replace('/ name\="(.+?)"/', ' name="$1[]"', $common);

												$sep = apply_filters("ws_plugin__s2member_custom_field_gen_checkboxes_sep", "&nbsp;&nbsp;", get_defined_vars());
												$opl = apply_filters("ws_plugin__s2member_custom_field_gen_checkboxes_opl", "ws-plugin--s2member-custom-reg-field-op-l", get_defined_vars());

												foreach(preg_split("/[\r\n\t]+/", $field["options"]) as $i => $option_line)
													{
														$common_i = preg_replace('/ id\="(.+?)"/', ' id="$1---'.($i).'"', $common);

														list($option_value, $option_label, $option_default) = c_ws_plugin__s2member_utils_strings::trim_deep(preg_split("/\|/", trim($option_line)));

														$gen .= ($i > 0) ? $sep : ''; // Separators can be filtered above.
														$gen .= '<input type="checkbox" value="'.esc_attr($option_value).'"';
														$gen .= ((($option_default && !$_submission) || in_array($option_value, (array)$_value)) ? ' checked="checked"' : '');
														$gen .= $common_i.' /><label for="'.esc_attr($_id_prefix.$field_id_class."-".$i).'" class="'.esc_attr($opl).'" style="display:inline !important; margin:0 !important;">'.$option_label.'</label>';
													}
											}
									}
								else if($field["type"] === "radios" && !empty($field["options"]))
									{
										if($_editable_context === "profile-view")
											{
												foreach(preg_split("/[\r\n\t]+/", $field["options"]) as $i => $option_line)
													{
														list($option_value, $option_label, $option_default) = c_ws_plugin__s2member_utils_strings::trim_deep(preg_split("/\|/", trim($option_line)));
														if($option_value === (string)$_value)
															{
																$gen = $option_label;
																break;
															}
													}
											}
										else // Else handle normally.
											{
												$sep = apply_filters("ws_plugin__s2member_custom_field_gen_radios_sep", "&nbsp;&nbsp;", get_defined_vars());
												$opl = apply_filters("ws_plugin__s2member_custom_field_gen_radios_opl", "ws-plugin--s2member-custom-reg-field-op-l", get_defined_vars());

												foreach(preg_split("/[\r\n\t]+/", $field["options"]) as $i => $option_line)
													{
														$common_i = preg_replace('/ id\="(.+?)"/', ' id="$1---'.($i).'"', $common);

														list($option_value, $option_label, $option_default) = c_ws_plugin__s2member_utils_strings::trim_deep(preg_split("/\|/", trim($option_line)));

														$gen .= ($i > 0) ? $sep : ''; // Separators can be filtered above.
														$gen .= '<input type="radio" value="'.esc_attr($option_value).'"';
														$gen .= ((($option_default && !$_submission) || $option_value === (string)$_value) ? ' checked="checked"' : '');
														$gen .= $common_i.' /><label for="'.esc_attr($_id_prefix.$field_id_class."-".$i).'" class="'.esc_attr($opl).'" style="display:inline !important; margin:0 !important;">'.$option_label.'</label>';
													}
											}
									}
								else // Default to a text field input type when nothing matches.
									{
										if($_editable_context === "profile-view")
											$gen = esc_html((string)$_value);

										else // Else handle normally.
											{
												$gen = '<input type="text" maxlength="100" autocomplete="off"';
												$gen .= ' value="'.format_to_edit((!$_submission && isset($field["deflt"]) && strlen((string)$field["deflt"])) ? (string)$field["deflt"] : (string)$_value).'"';
												$gen .= $common.' />';
											}
									}
								foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
								do_action("ws_plugin__s2member_during_custom_field_gen_after", get_defined_vars());
								unset /* Unset defined __refs, __v. */($__refs, $__v);
							}
						return apply_filters("ws_plugin__s2member_custom_field_gen", $gen, get_defined_vars());
					}
				/**
				* Determines which Custom Fields apply to a specific Level.
				*
				* @package s2Member\Custom_Reg_Fields
				* @since 3.5
				*
				* @param str|int $_level Optional. Defaults to the current User's Access Level number.
				* 	You can either pass in a numeric Level number, or the string `auto-detection`.
				* @param str $_editable_context Optional. One of `profile|profile-view|registration`.
				* @return array Array of Custom Field IDs applicable.
				*/
				public static function custom_fields_configured_at_level($_level = "auto-detection", $_editable_context = FALSE)
					{
						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("ws_plugin__s2member_before_custom_fields_configured_at_level", get_defined_vars());
						unset /* Unset defined __refs, __v. */($__refs, $__v);

						$level = ($_level === "auto-detection") ? c_ws_plugin__s2member_user_access::user_access_level() : $_level;
						if($_level === "auto-detection" && $level < 0 && ($reg_cookies = c_ws_plugin__s2member_register_access::reg_cookies_ok()) && extract($reg_cookies) && preg_match($GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["membership_item_number_w_level_regex"], $item_number, $m) && !empty($m[1]) && is_numeric($m[1]))
							$level = /* A numeric Membership Level # . */ $m[1];

						$level = ($level !== "any" && (!is_numeric($level) || $level < 0)) ? 0 : /* Default. */ $level;

						if(($level === "any" || (is_numeric($level) && $level >= 0)) && $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_fields"])
							{
								foreach(json_decode($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_fields"], true) as $field)
									if($level === "any" || $field["levels"] === "all" || in_array($level, preg_split("/[;,]+/", preg_replace("/[^0-9;,]/", "", $field["levels"]))))
										if(empty($_editable_context) || $_editable_context === "administrative" || ($_editable_context === "registration" && $field["editable"] !== "no-always-invisible" && $field["editable"] !== "yes-invisible") || (($_editable_context === "profile" || $_editable_context === "profile-view") && $field["editable"] !== "no-invisible" && $field["editable"] !== "no-always-invisible"))
											$configured[] = /* Add this to the array. */ $field["id"];
							}
						return apply_filters("ws_plugin__s2member_custom_fields_configured_at_level", ((!empty($configured)) ? $configured : array()), get_defined_vars());
					}
				/**
				* Adds Custom Fields to: `/wp-signup.php`.
				*
				* For Multisite Blog Farms.
				*
				* @package s2Member\Custom_Reg_Fields
				* @since 3.5
				*
				* @attaches-to ``add_action("signup_extra_fields");``
				*
				* @return null
				*
				* @todo Optimize with ``empty()``.
				*/
				public static function ms_custom_registration_fields()
					{
						do_action("ws_plugin__s2member_before_ms_custom_registration_fields", get_defined_vars());

						if /* Must be Main Site of a Network. */(is_multisite() && is_main_site())
							{
								$_p = (!empty($_POST)) ? c_ws_plugin__s2member_utils_strings::trim_deep(stripslashes_deep($_POST)) : array();

								echo '<input type="hidden" name="ws_plugin__s2member_registration" value="'.esc_attr(wp_create_nonce("ws-plugin--s2member-registration")).'" />'."\n";

								foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
								do_action("ws_plugin__s2member_during_ms_custom_registration_fields_before", get_defined_vars());
								unset /* Unset defined __refs, __v. */($__refs, $__v);

								if($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_names"])
									{
										echo '<div class="ws-plugin--s2member-custom-reg-field-divider-section"></div>'."\n";

										foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
										do_action("ws_plugin__s2member_during_ms_custom_registration_fields_before_first_name", get_defined_vars());
										unset /* Unset defined __refs, __v. */($__refs, $__v);

										echo '<label for="ws-plugin--s2member-custom-reg-field-first-name">'._x("First Name", "s2member-front", "s2member").' *</label>'."\n";
										echo '<input type="text" aria-required="true" maxlength="100" autocomplete="off" name="ws_plugin__s2member_custom_reg_field_first_name" id="ws-plugin--s2member-custom-reg-field-first-name" class="ws-plugin--s2member-custom-reg-field" value="'.esc_attr($_p["ws_plugin__s2member_custom_reg_field_first_name"]).'" />'."\n";
										echo '<br />'."\n";

										foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
										do_action("ws_plugin__s2member_during_ms_custom_registration_fields_after_first_name", get_defined_vars());
										unset /* Unset defined __refs, __v. */($__refs, $__v);

										foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
										do_action("ws_plugin__s2member_during_ms_custom_registration_fields_before_last_name", get_defined_vars());
										unset /* Unset defined __refs, __v. */($__refs, $__v);

										echo '<label for="ws-plugin--s2member-custom-reg-field-last-name">'._x("Last Name", "s2member-front", "s2member").' *</label>'."\n";
										echo '<input type="text" aria-required="true" maxlength="100" autocomplete="off" name="ws_plugin__s2member_custom_reg_field_last_name" id="ws-plugin--s2member-custom-reg-field-last-name" class="ws-plugin--s2member-custom-reg-field" value="'.esc_attr($_p["ws_plugin__s2member_custom_reg_field_last_name"]).'" />'."\n";
										echo '<br />'."\n";

										foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
										do_action("ws_plugin__s2member_during_ms_custom_registration_fields_after_last_name", get_defined_vars());
										unset /* Unset defined __refs, __v. */($__refs, $__v);
									}
								if($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_fields"])
									if($fields_applicable = c_ws_plugin__s2member_custom_reg_fields::custom_fields_configured_at_level("auto-detection", "registration"))
										foreach(json_decode($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_fields"], true) as $field)
											{
												foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
												do_action("ws_plugin__s2member_during_ms_custom_registration_fields_before_custom_fields", get_defined_vars());
												unset /* Unset defined __refs, __v. */($__refs, $__v);

												if /* Field applicable? */(in_array($field["id"], $fields_applicable))
													{
														$field_var = preg_replace("/[^a-z0-9]/i", "_", strtolower($field["id"]));
														$field_id_class = preg_replace("/_/", "-", $field_var);

														foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
														if(apply_filters("ws_plugin__s2member_during_ms_custom_registration_fields_during_custom_fields_display", true, get_defined_vars()))
															{
																if /* Starts a new section? */(!empty($field["section"]) && $field["section"] === "yes")
																	echo '<div class="ws-plugin--s2member-custom-reg-field-divider-section'.((!empty($field["sectitle"])) ? '-title' : '').'">'.((!empty($field["sectitle"])) ? $field["sectitle"] : '').'</div>';

																echo '<label for="ws-plugin--s2member-custom-reg-field-'.esc_attr($field_id_class).'"'.((preg_match("/^(checkbox|pre_checkbox)$/", $field["type"])) ? ' style="display:none;"' : '').'>'.$field["label"].(($field["required"] === "yes") ? ' *' : '').'</label>'.((preg_match("/^(checkbox|pre_checkbox)$/", $field["type"])) ? '<br />' : '')."\n";
																echo c_ws_plugin__s2member_custom_reg_fields::custom_field_gen(__FUNCTION__, $field, "ws_plugin__s2member_custom_reg_field_", "ws-plugin--s2member-custom-reg-field-", "ws-plugin--s2member-custom-reg-field", "", "", "", $_p, $_p["ws_plugin__s2member_custom_reg_field_".$field_var], "registration");
																echo '<br />'."\n";
															}
														unset /* Unset defined __refs, __v. */($__refs, $__v);
													}
												foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
												do_action("ws_plugin__s2member_during_ms_custom_registration_fields_after_custom_fields", get_defined_vars());
												unset /* Unset defined __refs, __v. */($__refs, $__v);
											}
								if($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_opt_in"] && c_ws_plugin__s2member_list_servers::list_servers_integrated())
									{
										foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
										do_action("ws_plugin__s2member_during_ms_custom_registration_fields_before_opt_in", get_defined_vars());
										unset /* Unset defined __refs, __v. */($__refs, $__v);

										echo '<label for="ws-plugin--s2member-custom-reg-field-opt-in">'."\n";
										echo '<input type="checkbox" name="ws_plugin__s2member_custom_reg_field_opt_in" id="ws-plugin--s2member-custom-reg-field-opt-in" class="ws-plugin--s2member-custom-reg-field" value="1"'.(((empty($_p) && $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_opt_in"] == 1) || $_p["ws_plugin__s2member_custom_reg_field_opt_in"]) ? ' checked="checked"' : '').' />'."\n";
										echo $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_opt_in_label"]."\n";
										echo '</label>'."\n";
										echo '<br />'."\n";

										foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
										do_action("ws_plugin__s2member_during_ms_custom_registration_fields_after_opt_in", get_defined_vars());
										unset /* Unset defined __refs, __v. */($__refs, $__v);
									}
								foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
								do_action("ws_plugin__s2member_during_ms_custom_registration_fields_after", get_defined_vars());
								unset /* Unset defined __refs, __v. */($__refs, $__v);
							}
						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("ws_plugin__s2member_after_ms_custom_registration_fields", get_defined_vars());
						unset /* Unset defined __refs, __v. */($__refs, $__v);

						return /* Return for uniformity. */;
					}
				/**
				* Adds Custom Fields to: `/wp-login.php?action=register`.
				*
				* @package s2Member\Custom_Reg_Fields
				* @since 3.5
				*
				* @attaches-to ``add_action("register_form");``
				*
				* @return null
				*
				* @todo Optimize with ``empty()``.
				*/
				public static function custom_registration_fields()
					{
						do_action("ws_plugin__s2member_before_custom_registration_fields", get_defined_vars());

						$_p = (!empty($_POST)) ? c_ws_plugin__s2member_utils_strings::trim_deep(stripslashes_deep($_POST)) : array();

						echo '<input type="hidden" name="ws_plugin__s2member_registration" value="'.esc_attr(wp_create_nonce("ws-plugin--s2member-registration")).'" />'."\n";

						$tabindex = /* Incremented tabindex starting with 20. */ 20;

						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("ws_plugin__s2member_during_custom_registration_fields_before", get_defined_vars());
						unset /* Unset defined __refs, __v. */($__refs, $__v);

						if($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_password"])
							{
								foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
								do_action("ws_plugin__s2member_during_custom_registration_fields_before_user_pass", get_defined_vars());
								unset /* Unset defined __refs, __v. */($__refs, $__v);

								echo '<p>'."\n";

								echo '<label for="ws-plugin--s2member-custom-reg-field-user-pass1" title="'.esc_attr(_x("Please type your Password twice to confirm.", "s2member-front", "s2member")).'">'."\n";
								echo '<span>'._x("Password (please type it twice)", "s2member-front", "s2member").' *</span><br />'."\n";
								echo '<input type="password" aria-required="true" maxlength="100" autocomplete="off" name="ws_plugin__s2member_custom_reg_field_user_pass1" id="ws-plugin--s2member-custom-reg-field-user-pass1" class="ws-plugin--s2member-custom-reg-field" value="'.format_to_edit($_p["ws_plugin__s2member_custom_reg_field_user_pass1"]).'" tabindex="'.esc_attr(($tabindex = $tabindex + 10)).'" />'."\n";
								echo '</label>'."\n";

								echo '<label for="ws-plugin--s2member-custom-reg-field-user-pass2" title="'.esc_attr(_x("Please type your Password twice to confirm.", "s2member-front", "s2member")).'">'."\n";
								echo '<input type="password" maxlength="100" autocomplete="off" name="ws_plugin__s2member_custom_reg_field_user_pass2" id="ws-plugin--s2member-custom-reg-field-user-pass2" class="ws-plugin--s2member-custom-reg-field" value="'.format_to_edit($_p["ws_plugin__s2member_custom_reg_field_user_pass2"]).'" tabindex="'.esc_attr(($tabindex = $tabindex + 10)).'" />'."\n";
								echo '</label>'."\n";

								echo '<div id="ws-plugin--s2member-custom-reg-field-user-pass-strength" class="ws-plugin--s2member-password-strength"><em>'._x("password strength indicator", "s2member-front", "s2member").'</em></div>'."\n";

								echo '</p>'."\n";

								foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
								do_action("ws_plugin__s2member_during_custom_registration_fields_after_user_pass", get_defined_vars());
								unset /* Unset defined __refs, __v. */($__refs, $__v);
							}
						if($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_names"])
							{
								echo '<div class="ws-plugin--s2member-custom-reg-field-divider-section"></div>'."\n";

								foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
								do_action("ws_plugin__s2member_during_custom_registration_fields_before_first_name", get_defined_vars());
								unset /* Unset defined __refs, __v. */($__refs, $__v);

								echo '<p>'."\n";
								echo '<label for="ws-plugin--s2member-custom-reg-field-first-name">'."\n";
								echo '<span>'._x("First Name", "s2member-front", "s2member").' *</span><br />'."\n";
								echo '<input type="text" aria-required="true" maxlength="100" autocomplete="off" name="ws_plugin__s2member_custom_reg_field_first_name" id="ws-plugin--s2member-custom-reg-field-first-name" class="ws-plugin--s2member-custom-reg-field" value="'.esc_attr($_p["ws_plugin__s2member_custom_reg_field_first_name"]).'" tabindex="'.esc_attr(($tabindex = $tabindex + 10)).'" />'."\n";
								echo '</label>'."\n";
								echo '</p>'."\n";

								foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
								do_action("ws_plugin__s2member_during_custom_registration_fields_after_first_name", get_defined_vars());
								unset /* Unset defined __refs, __v. */($__refs, $__v);

								foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
								do_action("ws_plugin__s2member_during_custom_registration_fields_before_last_name", get_defined_vars());
								unset /* Unset defined __refs, __v. */($__refs, $__v);

								echo '<p>'."\n";
								echo '<label for="ws-plugin--s2member-custom-reg-field-last-name">'."\n";
								echo '<span>'._x("Last Name", "s2member-front", "s2member").' *</span><br />'."\n";
								echo '<input type="text" aria-required="true" maxlength="100" autocomplete="off" name="ws_plugin__s2member_custom_reg_field_last_name" id="ws-plugin--s2member-custom-reg-field-last-name" class="ws-plugin--s2member-custom-reg-field" value="'.esc_attr($_p["ws_plugin__s2member_custom_reg_field_last_name"]).'" tabindex="'.esc_attr(($tabindex = $tabindex + 10)).'" />'."\n";
								echo '</label>'."\n";
								echo '</p>'."\n";

								foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
								do_action("ws_plugin__s2member_during_custom_registration_fields_after_last_name", get_defined_vars());
								unset /* Unset defined __refs, __v. */($__refs, $__v);
							}
						if($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_fields"])
							if($fields_applicable = c_ws_plugin__s2member_custom_reg_fields::custom_fields_configured_at_level("auto-detection", "registration"))
								{
									$tabindex = /* Start tabindex at +9 ( +1 below ). */ $tabindex + 9;

									foreach(json_decode($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_fields"], true) as $field)
										{
											foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
											do_action("ws_plugin__s2member_during_custom_registration_fields_before_custom_fields", get_defined_vars());
											unset /* Unset defined __refs, __v. */($__refs, $__v);

											if /* Field applicable? */(in_array($field["id"], $fields_applicable))
												{
													$field_var = preg_replace("/[^a-z0-9]/i", "_", strtolower($field["id"]));
													$field_id_class = preg_replace("/_/", "-", $field_var);

													foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
													if(apply_filters("ws_plugin__s2member_during_custom_registration_fields_during_custom_fields_display", true, get_defined_vars()))
														{
															if /* Starts a new section? */(!empty($field["section"]) && $field["section"] === "yes")
																echo '<div class="ws-plugin--s2member-custom-reg-field-divider-section'.((!empty($field["sectitle"])) ? '-title' : '').'">'.((!empty($field["sectitle"])) ? $field["sectitle"] : '').'</div>';

															echo '<p>'."\n";
															echo '<label for="ws-plugin--s2member-custom-reg-field-'.esc_attr($field_id_class).'">'."\n";
															echo '<span'.((preg_match("/^(checkbox|pre_checkbox)$/", $field["type"])) ? ' style="display:none;"' : '').'>'.$field["label"].(($field["required"] === "yes") ? ' *' : '').'</span></label>'.((preg_match("/^(checkbox|pre_checkbox)$/", $field["type"])) ? '' : '<br />')."\n";
															echo c_ws_plugin__s2member_custom_reg_fields::custom_field_gen(__FUNCTION__, $field, "ws_plugin__s2member_custom_reg_field_", "ws-plugin--s2member-custom-reg-field-", "ws-plugin--s2member-custom-reg-field", "", ($tabindex = $tabindex + 1), "", $_p, $_p["ws_plugin__s2member_custom_reg_field_".$field_var], "registration");
															echo '</p>'."\n";
														}
													unset /* Unset defined __refs, __v. */($__refs, $__v);
												}
											foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
											do_action("ws_plugin__s2member_during_custom_registration_fields_after_custom_fields", get_defined_vars());
											unset /* Unset defined __refs, __v. */($__refs, $__v);
										}
								}
						if($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_opt_in"] && c_ws_plugin__s2member_list_servers::list_servers_integrated())
							{
								foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
								do_action("ws_plugin__s2member_during_custom_registration_fields_before_opt_in", get_defined_vars());
								unset /* Unset defined __refs, __v. */($__refs, $__v);

								echo '<p>'."\n";
								echo '<label for="ws-plugin--s2member-custom-reg-field-opt-in">'."\n";
								echo '<input type="checkbox" name="ws_plugin__s2member_custom_reg_field_opt_in" id="ws-plugin--s2member-custom-reg-field-opt-in" class="ws-plugin--s2member-custom-reg-field" value="1"'.(((empty($_p) && $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_opt_in"] == 1) || $_p["ws_plugin__s2member_custom_reg_field_opt_in"]) ? ' checked="checked"' : '').' tabindex="'.esc_attr(($tabindex = $tabindex + 10)).'" />'."\n";
								echo $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_opt_in_label"]."\n";
								echo '</label>'."\n";
								echo '</p>'."\n";

								foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
								do_action("ws_plugin__s2member_during_custom_registration_fields_after_opt_in", get_defined_vars());
								unset /* Unset defined __refs, __v. */($__refs, $__v);
							}
						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("ws_plugin__s2member_during_custom_registration_fields_after", get_defined_vars());
						unset /* Unset defined __refs, __v. */($__refs, $__v);

						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("ws_plugin__s2member_after_custom_registration_fields", get_defined_vars());
						unset /* Unset defined __refs, __v. */($__refs, $__v);

						return /* Return for uniformity. */;
					}
			}
	}
?>