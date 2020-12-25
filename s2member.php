<?php
// @codingStandardsIgnoreFile
/**
 * The main plugin file.
 *
 * This file loads the plugin after checking
 * PHP, WordPress and other compatibility requirements.
 *
 * Copyright: © 2009-2011
 * {@link http://wpsharks.com/ WP Sharks}
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

Version: 201225
Stable tag: 201225

SSL Compatible: yes
bbPress Compatible: yes
WordPress Compatible: yes
BuddyPress Compatible: yes
WP Multisite Compatible: yes
Multisite Blog Farm Compatible: yes

PayPal Standard Compatible: yes
Stripe Compatible: yes w/s2Member Pro
PayPal Pro Compatible: yes w/s2Member Pro
Authorize.Net Compatible: yes w/s2Member Pro
ClickBank Compatible: yes w/s2Member Pro

Tested up to: 5.7
Requires at least: 4.2

Requires PHP: 5.6.2
Tested up to PHP: 7.4.6

Copyright: © 2009 WP Sharks
License: GNU General Public License
Contributors: WebSharks, JasWSInc, anguz, raamdev, bruce-caldwell, clavaque

Author: WP Sharks
Author URI: http://s2member.com/
Donate link: http://s2member.com/r/donate

Text Domain: s2member
Domain Path: /src/includes/translations

Plugin Name: s2Member Framework
Forum URI: http://s2member.com/r/forum/
Plugin URI: http://s2member.com/
Privacy URI: http://s2member.com/privacy-policy/
Changelog URI: http://s2member.com/changelog/
Video Tutorials: http://s2member.com/r/s2member-videos/
Knowledge Base: http://s2member.com/kb/
Newsletter: http://s2member.com/r/subscribe/
PayPal Pro Integration: http://s2member.com/r/pp-account-types/

Description: s2Member, a powerful (free) membership plugin for WordPress. Protect/secure members only content with roles/capabilities.
Tags: s2, s2member, s2 member, membership, users, user, members, member, subscribers, subscriber, members only, roles, capabilities, capability, register, signup, paypal, paypal pro, pay pal, authorize, authorize.net, google wallet, clickbank, click bank, buddypress, buddy press, bbpress, bb press, shopping cart, cart, checkout, ecommerce

-- end section for WordPress parsing. ------------------------------------------------------------------------------- */
if(!defined('WPINC')) // MUST have WordPress.
	exit('Do not access this file directly.');
/**
 * The installed version of s2Member.
 *
 * @package s2Member
 * @since 3.0
 *
 * @var string
 */
${__FILE__}['tmp'] = '201225'; //version//
if(!defined('WS_PLUGIN__S2MEMBER_VERSION'))
	define('WS_PLUGIN__S2MEMBER_VERSION', ${__FILE__}['tmp']);
/**
 * Minimum PHP version required to run s2Member.
 *
 * @package s2Member
 * @since 3.0
 *
 * @var string
 */
${__FILE__}['tmp'] = '5.6.2'; //php-required-version//
if(!defined('WS_PLUGIN__S2MEMBER_MIN_PHP_VERSION'))
	define('WS_PLUGIN__S2MEMBER_MIN_PHP_VERSION', ${__FILE__}['tmp']);
/**
 * Minimum WordPress version required to run s2Member.
 *
 * @package s2Member
 * @since 3.0
 *
 * @var string
 */
${__FILE__}['tmp'] = '4.2'; //wp-required-version//
if(!defined('WS_PLUGIN__S2MEMBER_MIN_WP_VERSION'))
	define('WS_PLUGIN__S2MEMBER_MIN_WP_VERSION', ${__FILE__}['tmp']);
/**
 * Minimum Pro version required by the Framework.
 *
 * @package s2Member
 * @since 3.0
 *
 * @var string
 */
${__FILE__}['tmp'] = '201225'; //version//
if(!defined('WS_PLUGIN__S2MEMBER_MIN_PRO_VERSION'))
	define('WS_PLUGIN__S2MEMBER_MIN_PRO_VERSION', ${__FILE__}['tmp']);
/*
Several compatibility checks.
If all pass, load the s2Member plugin.
*/
if(version_compare(PHP_VERSION, WS_PLUGIN__S2MEMBER_MIN_PHP_VERSION, '>=') && version_compare(get_bloginfo('version'), WS_PLUGIN__S2MEMBER_MIN_WP_VERSION, '>=') && !isset($GLOBALS['WS_PLUGIN__']['s2member']))
{
	$GLOBALS['WS_PLUGIN__']['s2member']['l'] = __FILE__;
	/*
	Hook before loaded.
	*/
	do_action('ws_plugin__s2member_before_loaded');
	/*
	System configuraton.
	*/
	include_once dirname(__FILE__).'/src/includes/syscon.inc.php';
	/*
	Hooks and Filters.
	*/
	include_once dirname(__FILE__).'/src/includes/hooks.inc.php';
	/*
	Hook after system config & Hooks are loaded.
	*/
	do_action('ws_plugin__s2member_config_hooks_loaded');
	/*
	Load a possible Pro module, if/when available.
	*/
	if(apply_filters('ws_plugin__s2member_load_pro', TRUE))
	{
		if(is_file($_s2member_pro = dirname(__FILE__).'-pro/pro-module.php'))
			include_once $_s2member_pro;

		else if(is_file($_s2member_pro = WP_PLUGIN_DIR.'/'.basename(dirname(__FILE__)).'-pro/pro-module.php'))
			include_once $_s2member_pro;

		unset($_s2member_pro); // Housekeeping.
	}
	/*
	Configure options and their defaults.
	*/
	ws_plugin__s2member_configure_options_and_their_defaults();
	/*
	Function includes.
	*/
	include_once dirname(__FILE__).'/src/includes/funcs.inc.php';
	/*
	Include Shortcodes.
	*/
	include_once dirname(__FILE__).'/src/includes/codes.inc.php';
	/*
	Hooks after loaded.
	*/
	do_action('ws_plugin__s2member_loaded');
	do_action('ws_plugin__s2member_after_loaded');
}
/*
Else NOT compatible. Do we need admin compatibility errors now?
*/
else if(is_admin()) // Admin compatibility errors.
{
	if(!version_compare(PHP_VERSION, WS_PLUGIN__S2MEMBER_MIN_PHP_VERSION, '>='))
	{
		add_action('all_admin_notices', function(){
			echo '<div class="error fade"><p>You need PHP v' . WS_PLUGIN__S2MEMBER_MIN_PHP_VERSION . '+ to use the s2Member plugin.</p></div>';
		});
	}
	else if(!version_compare(get_bloginfo('version'), WS_PLUGIN__S2MEMBER_MIN_WP_VERSION, '>='))
	{
		add_action('all_admin_notices', function(){
			echo '<div class="error fade"><p>You need WordPress v' . WS_PLUGIN__S2MEMBER_MIN_WP_VERSION . '+ to use the s2Member plugin.</p></div>';
		});
	}
}
unset(${__FILE__}); // Housekeeping.
