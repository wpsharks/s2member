<?php
/**
* Profile modifications for BuddyPress (inner processing routines).
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
* @package s2Member\Profiles
* @since 3.5
*/
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit("Do not access this file directly.");

if (!class_exists ("c_ws_plugin__s2member_profile_mods_4bp_in"))
	{
		/**
		* Profile modifications for BuddyPress (inner processing routines).
		*
		* @package s2Member\Profiles
		* @since 3.5
		*/
		class c_ws_plugin__s2member_profile_mods_4bp_in
			{
				/**
				* Handles Profile modifications for Custom Fields *(created with s2Member)*; integrated with BuddyPress.
				*
				* @package s2Member\Profiles
				* @since 3.5
				*
				* @attaches-to ``add_action("init");``
				*
				* @return null After re-configuring the ``$current_user`` object.
				*/
				public static function handle_profile_modifications_4bp ()
					{
						global $current_user; // We'll need to update this global object.

						$user = &$current_user; // Shorter reference to the ``$current_user`` object.

						do_action ("ws_plugin__s2member_before_handle_profile_modifications_4bp", get_defined_vars ());

						if (!empty ($_POST["ws_plugin__s2member_profile_4bp_save"]) && is_user_logged_in () && is_object ($user) && !empty ($user->ID) && ($user_id = $user->ID))
							{
								if (($nonce = $_POST["ws_plugin__s2member_profile_4bp_save"]) && wp_verify_nonce ($nonce, "ws-plugin--s2member-profile-4bp-save"))
									{
										$GLOBALS["ws_plugin__s2member_profile_4bp_saved"] = true; // Global flag as having been saved/updated successfully.

										$_p = c_ws_plugin__s2member_utils_strings::trim_deep (stripslashes_deep ($_POST)); // Clean ``$_POST`` vars.

										if ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_fields"])
											if ($fields_applicable = c_ws_plugin__s2member_custom_reg_fields::custom_fields_configured_at_level ("auto-detection", "profile"))
												{
													$_existing_fields = get_user_option ("s2member_custom_fields", $user_id);

													foreach (json_decode ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_fields"], true) as $field)
														{
															$field_var = preg_replace ("/[^a-z0-9]/i", "_", strtolower ($field["id"]));
															$field_id_class = preg_replace ("/_/", "-", $field_var);

															if (!in_array ($field["id"], $fields_applicable) || strpos ($field["editable"], "no") === 0)
																{
																	if (isset ($_existing_fields[$field_var]) && ((is_array ($_existing_fields[$field_var]) && !empty ($_existing_fields[$field_var])) || (is_string ($_existing_fields[$field_var]) && strlen ($_existing_fields[$field_var]))))
																		$fields[$field_var] = $_existing_fields[$field_var];
																	else // Else ``unset()``.
																		unset($fields[$field_var]);
																}
															else if ($field["required"] === "yes" && (!isset ($_p["ws_plugin__s2member_profile_4bp_" . $field_var]) || (!is_array ($_p["ws_plugin__s2member_profile_4bp_" . $field_var]) && !is_string ($_p["ws_plugin__s2member_profile_4bp_" . $field_var])) || (is_array ($_p["ws_plugin__s2member_profile_4bp_" . $field_var]) && empty ($_p["ws_plugin__s2member_profile_4bp_" . $field_var])) || (is_string ($_p["ws_plugin__s2member_profile_4bp_" . $field_var]) && !strlen ($_p["ws_plugin__s2member_profile_4bp_" . $field_var]))))
																{
																	if (isset ($_existing_fields[$field_var]) && ((is_array ($_existing_fields[$field_var]) && !empty ($_existing_fields[$field_var])) || (is_string ($_existing_fields[$field_var]) && strlen ($_existing_fields[$field_var]))))
																		$fields[$field_var] = $_existing_fields[$field_var];
																	else // Else ``unset()``.
																		unset($fields[$field_var]);
																}
															else if (isset ($_p["ws_plugin__s2member_profile_4bp_" . $field_var]))
																{
																	if ((is_array ($_p["ws_plugin__s2member_profile_4bp_" . $field_var]) && !empty ($_p["ws_plugin__s2member_profile_4bp_" . $field_var])) || (is_string ($_p["ws_plugin__s2member_profile_4bp_" . $field_var]) && strlen ($_p["ws_plugin__s2member_profile_4bp_" . $field_var])))
																		$fields[$field_var] = $_p["ws_plugin__s2member_profile_4bp_" . $field_var];
																	else // Else ``unset()``.
																		unset($fields[$field_var]);
																}
															else // Else ``unset()``.
																unset($fields[$field_var]);
														}

													if (!empty ($fields))
														update_user_option ($user_id, "s2member_custom_fields", $fields);
													else // Else delete their Custom Fields?
														delete_user_option ($user_id, "s2member_custom_fields");
												}

										foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
										do_action ("ws_plugin__s2member_during_handle_profile_modifications_4bp", get_defined_vars ());
										unset /* Unset defined __refs, __v. */ ($__refs, $__v);

										$user = new WP_User ($user_id); // Update the ``WP_User`` object for the current User/Member.
										(function_exists ("setup_userdata")) ? setup_userdata () : null; // Update global vars.
									}
							}

						do_action ("ws_plugin__s2member_after_handle_profile_modifications_4bp", get_defined_vars ());

						return /* Return for uniformity. */;
					}
			}
	}
?>