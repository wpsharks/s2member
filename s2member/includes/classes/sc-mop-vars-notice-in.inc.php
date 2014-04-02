<?php
/**
 * Shortcode for `[s2MOP /]`.
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
 * @package s2Member\Shortcodes
 * @since 140331
 */
if(realpath(__FILE__) === realpath($_SERVER["SCRIPT_FILENAME"]))
	exit ("Do not access this file directly.");

/*
 * Working shortcode examples (as of Raam's changes on 2014-03-30 02:18:45 EDT):
 *
 * [s2MOP]You were trying to access a protected: %%SEEKING_TYPE%%[/s2MOP]
 * [s2MOP]You were trying to access content that requires a %%REQUIRED_TYPE%% that you don't have.[/s2MOP]
 * [s2MOP]You were trying to access content protected via s2Member: %%RESTRICTION_TYPE%% restrictions[/s2MOP]
 * [s2MOP seeking_type="post" required_type="level" restriction_type="post"]You were trying to access a restricted post with Post ID #%%SEEKING_POST_ID%% which requires that you be a Member at Level #%%REQUIRED_LEVEL%%[/s2MOP]
 * [s2MOP required_type="level"]"%%POST_TITLE%%" is a protected %%SEEKING_TYPE%% that requires Membership Level #%%REQUIRED_LEVEL%%[/s2MOP]
 */

if(!class_exists("c_ws_plugin__s2member_sc_mop_vars_notice_in"))
	{
		/**
		 * Shortcode for `[s2MOP /]`.
		 *
		 * @package s2Member\Shortcodes
		 * @since 140331
		 */
		class c_ws_plugin__s2member_sc_mop_vars_notice_in
		{
			public static function shortcode($attr = array(), $content = "", $shortcode = "")
				{
					$_g = stripslashes_deep($_GET);

					if(!isset($_g["_s2member_seeking"]) || !is_array($_g["_s2member_seeking"])
					   || !c_ws_plugin__s2member_utils_urls::s2member_sig_ok($_SERVER["QUERY_STRING"])
						// The query string is going to contain the new MOP Vars, whereas this works on the old ones.
						//    Still, that's OK for now. The new query string vars use an s2Member signature too.
					) return "";

					$valid_required_types    = array("level", "ccap", "sp");
					$valid_seeking_types     = array("page", "post", "catg", "ptag", "file", "ruri");
					$valid_restriction_types = array("page", "post", "catg", "ptag", "file", "ruri", "ccap", "sp", "sys");
					$attr                    = shortcode_atts(array("seeking_type" => "", "required_type" => "", "restriction_type" => ""), $attr, $shortcode);

					# ---------------------------------------------------------------------------------------------------

					if($attr["seeking_type"] !== "" || $attr["required_type"] !== "" || $attr["restriction_type"] !== "")
						{
							$attr["seeking_type"]    = array_unique(preg_split('/[|;,\s]+/', $attr["seeking_type"], NULL, PREG_SPLIT_NO_EMPTY));
							$attr["required_type"]   = array_unique(preg_split('/[|;,\s]+/', $attr["required_type"], NULL, PREG_SPLIT_NO_EMPTY));
							$attr["restricton_type"] = array_unique(preg_split('/[|;,\s]+/', $attr["restricton_type"], NULL, PREG_SPLIT_NO_EMPTY));

							if(isset($attr["seeking_type"]) && array_intersect($attr["seeking_type"], $valid_seeking_types))
								if(empty($_g["_s2member_seeking"]["type"]) || !in_array($_g["_s2member_seeking"]["type"], $attr["seeking_type"], TRUE))
									return "";

							if(isset($attr["required_type"]) && array_intersect($attr["required_type"], $valid_required_types))
								if(empty($_g["_s2member_req"]["type"]) || !in_array($_g["_s2member_req"]["type"], $attr["required_type"], TRUE))
									return "";

							if(isset($attr["restriction_type"]) && array_intersect($attr["restriction_type"], $valid_restriction_types))
								if(empty($_g["_s2member_res"]["type"]) || !in_array($_g["_s2member_res"]["type"], $attr["restriction_type"], TRUE))
									return "";
						}
					# ---------------------------------------------------------------------------------------------------

					if(!empty ($_g["_s2member_seeking"]["type"]) /* One of: page|post|catg|ptag|file|ruri */)
						$content = str_ireplace("%%SEEKING_TYPE%%", esc_html($_g["_s2member_seeking"]["type"]), $content);

					# ---------------------------------------------------------------------------------------------------

					if(!empty ($_g["_s2member_seeking"]["page"]))
						$content = str_ireplace("%%SEEKING_PAGE_ID%%", esc_html($_g["_s2member_seeking"]["page"]), $content);

					else if(!empty ($_g["_s2member_seeking"]["post"]))
						$content = str_ireplace("%%SEEKING_POST_ID%%", esc_html($_g["_s2member_seeking"]["post"]), $content);

					else if(!empty ($_g["_s2member_seeking"]["catg"]))
						$content = str_ireplace("%%SEEKING_CAT_ID%%", esc_html($_g["_s2member_seeking"]["catg"]), $content);

					else if(!empty ($_g["_s2member_seeking"]["ptag"]))
						$content = str_ireplace("%%SEEKING_TAG_ID%%", esc_html($_g["_s2member_seeking"]["ptag"]), $content);

					else if(!empty ($_g["_s2member_seeking"]["file"]))
						$content = str_ireplace("%%SEEKING_FILE%%", esc_html($_g["_s2member_seeking"]["file"]), $content);

					else if(!empty ($_g["_s2member_seeking"]["ruri"]) /* Full URI they were trying to access. */)
						$content = str_ireplace("%%SEEKING_RURI%%", esc_html(base64_decode($_g["_s2member_seeking"]["ruri"])), $content);

					# ---------------------------------------------------------------------------------------------------

					if(!empty ($_g["_s2member_seeking"]["_uri"]) /* Full URI they were trying to access. */)
						$content = str_ireplace("%%SEEKING_URI%%", esc_html(site_url(base64_decode($_g["_s2member_seeking"]["_uri"]))), $content);

					# ---------------------------------------------------------------------------------------------------

					if(!empty ($_g["_s2member_req"]["type"]) /* One of: level|ccap|sp */)
						$content = str_ireplace("%%REQUIRED_TYPE%%", esc_html($_g["_s2member_req"]["type"]), $content);

					# ---------------------------------------------------------------------------------------------------

					if(isset ($_g["_s2member_req"]["level"]))
						$content = str_ireplace("%%REQUIRED_LEVEL%%", esc_html($_g["_s2member_req"]["level"]), $content);

					else if(!empty ($_g["_s2member_req"]["ccap"]))
						$content = str_ireplace("%%REQUIRED_CCAP%%", esc_html($_g["_s2member_req"]["ccap"]), $content);

					else if(!empty ($_g["_s2member_req"]["sp"]))
						$content = str_ireplace("%%REQUIRED_SP%%", esc_html($_g["_s2member_req"]["sp"]), $content);

					# ---------------------------------------------------------------------------------------------------

					if(!empty ($_g["_s2member_res"]["type"]) /* One of: post|page|catg|ptag|file|ruri|ccap|sp|sys */)
						$content = str_ireplace("%%RESTRICTION_TYPE%%", esc_html($_g["_s2member_res"]["type"]), $content);

					# ---------------------------------------------------------------------------------------------------

					if(!empty ($_g["_s2member_seeking"]["type"]) && $_g["_s2member_seeking"]["type"] == "post")
						$content = str_ireplace(array("%%POST_TITLE%%", "%%PAGE_TITLE%%"), get_the_title((integer)$_g["_s2member_seeking"]["post"]), $content);

					# ---------------------------------------------------------------------------------------------------

					if(!empty ($_g["_s2member_seeking"]["type"]) && $_g["_s2member_seeking"]["type"] == "page")
						$content = str_ireplace(array("%%POST_TITLE%%", "%%PAGE_TITLE%%"), get_the_title((integer)$_g["_s2member_seeking"]["page"]), $content);

					# ---------------------------------------------------------------------------------------------------

					return apply_filters("c_ws_plugin__s2member_sc_mop_vars_notice_content", $content, get_defined_vars());
				}
		}
	}
?>