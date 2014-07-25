<?php
/**
* The main plugin file.
*
* This file loads the plugin after checking
* PHP, WordPress and other compatibility requirements.
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
* @package s2Member
* @since 1.0
*/
/* -- This section for WordPress parsing. ------------------------------------------------------------------------------

Version: 140725
Stable tag: 140725

SSL Compatible: yes
bbPress Compatible: yes
WordPress Compatible: yes
BuddyPress Compatible: yes
WP Multisite Compatible: yes
Multisite Blog Farm Compatible: yes

PayPal Standard Compatible: yes
PayPal Pro Compatible: yes w/s2Member Pro
Authorize.Net Compatible: yes w/s2Member Pro
Google Wallet Compatible: yes w/s2Member Pro
ClickBank Compatible: yes w/s2Member Pro

Tested up to: 3.8
Requires at least: 3.3

Copyright: © 2009 WebSharks, Inc.
License: GNU General Public License
Contributors: WebSharks

Author: s2Member / WebSharks, Inc.
Author URI: http://www.s2member.com/
Donate link: http://www.s2member.com/donate/

Text Domain: s2member
Domain Path: /includes/translations

Plugin Name: s2Member Framework
Forum URI: http://www.s2member.com/forums/
Plugin URI: http://www.s2member.com/framework/
Privacy URI: http://www.s2member.com/privacy/
Video Tutorials: http://www.s2member.com/videos/
Pro Module / Home Page: http://www.s2member.com/
Pro Module / Prices: http://www.s2member.com/prices/
Pro Module / Auto-Update URL: http://www.s2member.com/
PayPal Pro Integration: http://www.s2member.com/videos/ED70D90C6749DA3D/
Professional Installation URI: http://www.s2member.com/professional-installation/

Description: s2Member, a powerful (free) membership plugin for WordPress. Protect/secure members only content with roles/capabilities.
Tags: s2, s2member, s2 member, membership, users, user, members, member, subscribers, subscriber, members only, roles, capabilities, capability, register, signup, paypal, paypal pro, pay pal, authorize, authorize.net, google wallet, clickbank, click bank, buddypress, buddy press, bbpress, bb press, shopping cart, cart, checkout, ecommerce

-- end section for WordPress parsing. ------------------------------------------------------------------------------- */
if(realpath(__FILE__) === realpath($_SERVER["SCRIPT_FILENAME"]))
	exit("Do not access this file directly.");
/**
* The installed version of s2Member.
*
* @package s2Member
* @since 3.0
*
* @var string
*/
if(!defined("WS_PLUGIN__S2MEMBER_VERSION"))
	define("WS_PLUGIN__S2MEMBER_VERSION", "140725" /* !#distro-version#! */);
/**
* Minimum PHP version required to run s2Member.
*
* @package s2Member
* @since 3.0
*
* @var string
*/
if(!defined("WS_PLUGIN__S2MEMBER_MIN_PHP_VERSION"))
	define("WS_PLUGIN__S2MEMBER_MIN_PHP_VERSION", "5.2" /* !#php-requires-at-least-version#! */);
/**
* Minimum WordPress version required to run s2Member.
*
* @package s2Member
* @since 3.0
*
* @var string
*/
if(!defined("WS_PLUGIN__S2MEMBER_MIN_WP_VERSION"))
	define("WS_PLUGIN__S2MEMBER_MIN_WP_VERSION", "3.3" /* !#wp-requires-at-least-version#! */);
/**
* Minimum Pro version required by the Framework.
*
* @package s2Member
* @since 3.0
*
* @var string
*/
if(!defined("WS_PLUGIN__S2MEMBER_MIN_PRO_VERSION"))
	define("WS_PLUGIN__S2MEMBER_MIN_PRO_VERSION", "140725" /* !#distro-version#! */);
/*
Several compatibility checks.
If all pass, load the s2Member plugin.
*/
if(version_compare(PHP_VERSION, WS_PLUGIN__S2MEMBER_MIN_PHP_VERSION, ">=") && version_compare(get_bloginfo("version"), WS_PLUGIN__S2MEMBER_MIN_WP_VERSION, ">=") && !isset($GLOBALS["WS_PLUGIN__"]["s2member"]))
	{
		$GLOBALS["WS_PLUGIN__"]["s2member"]["l"] = __FILE__;
		/*
		Hook before loaded.
		*/
		do_action("ws_plugin__s2member_before_loaded");
		/*
		System configuraton.
		*/
		include_once dirname(__FILE__)."/includes/syscon.inc.php";
		/*
		Hooks and Filters.
		*/
		include_once dirname(__FILE__)."/includes/hooks.inc.php";
		/*
		Hook after system config & Hooks are loaded.
		*/
		do_action("ws_plugin__s2member_config_hooks_loaded");
		/*
		Load a possible Pro module, if/when available.
		*/
		if(apply_filters("ws_plugin__s2member_load_pro", true) && file_exists(dirname(__FILE__)."-pro/pro-module.php"))
			{
				include_once dirname(__FILE__)."-pro/pro-module.php";
				if(is_dir(WP_PLUGIN_DIR."/codestyling-localization") && !is_dir(dirname(__FILE__)."/s2member-pro") && function_exists("symlink"))
					{
					// Removing this for now. It causes problems during upgrades.
						//@symlink(dirname(__FILE__)."-pro", dirname(__FILE__)."/s2member-pro"); // For CS localization compatibility.
						//@chmod(dirname(__FILE__)."/s2member-pro", 0755);
					}
			}
		/*
		Configure options and their defaults.
		*/
		ws_plugin__s2member_configure_options_and_their_defaults();
		/*
		Function includes.
		*/
		include_once dirname(__FILE__)."/includes/funcs.inc.php";
		/*
		Include Shortcodes.
		*/
		include_once dirname(__FILE__)."/includes/codes.inc.php";
		/*
		Hooks after loaded.
		*/
		do_action("ws_plugin__s2member_loaded");
		do_action("ws_plugin__s2member_after_loaded");
	}
/*
Else NOT compatible. Do we need admin compatibility errors now?
*/
else if(is_admin()) // Admin compatibility errors.
	{
		if(!version_compare(PHP_VERSION, WS_PLUGIN__S2MEMBER_MIN_PHP_VERSION, ">="))
			{
				add_action("all_admin_notices", create_function('', 'echo \'<div class="error fade"><p>You need PHP v\' . WS_PLUGIN__S2MEMBER_MIN_PHP_VERSION . \'+ to use the s2Member plugin.</p></div>\';'));
			}
		else if(!version_compare(get_bloginfo("version"), WS_PLUGIN__S2MEMBER_MIN_WP_VERSION, ">="))
			{
				add_action("all_admin_notices", create_function('', 'echo \'<div class="error fade"><p>You need WordPress v\' . WS_PLUGIN__S2MEMBER_MIN_WP_VERSION . \'+ to use the s2Member plugin.</p></div>\';'));
			}
	}
?>
