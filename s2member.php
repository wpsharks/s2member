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

Version: 240325
Stable tag: 240325

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

Tested up to: 6.5-RC3-57866
Requires at least: 4.2

Requires PHP: 5.6.2
Tested up to PHP: 8.1

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
${__FILE__}['tmp'] = '240325'; //version//
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
${__FILE__}['tmp'] = '210526'; //!!!version//
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

//2300808 PayPal button encryption notice if they're using it
if (is_admin() && !empty($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_btn_encryption"])) {
	// Dismiss
	add_action('admin_init', function(){
		$user_id = get_current_user_id();
		if (isset($_GET['s2-dismiss-2300808']))
				add_user_meta($user_id, 's2_notice_dismissed_2300808', 'true', true);
	});
	// Notice
	add_action('admin_notices', function(){
		$user_id = get_current_user_id();
		$logo_url = $GLOBALS['WS_PLUGIN__']['s2member']['c']['dir_url'].'/src/images/logo-square-big.png';
		$dismiss_url = add_query_arg('s2-dismiss-2300808', '', $_SERVER['REQUEST_URI']);
		if (isset($_GET['s2-show-notice']) || !get_user_meta($user_id, 's2_notice_dismissed_2300808')) {
			echo '
				<div class="notice notice-warning" style="position:relative; margin: 0 0 15px 2px !important; padding: 0 40px 0 0 !important">
					<table cellspacing="11" cellpadding="0"><tr>
					<td><img src="'.$logo_url.'" height="40" width="40" align="top" /></td>
					<td><span>⚠️ PayPal has given some trouble recently with encrypted buttons, so for the time being it\'s recommended to leave encryption disabled and allow non-encrypted payments. See: <em>s2Member > PayPal Options > Account Details > Button Encryption</em></span></td>
					</tr></table>
					<a href="'.$dismiss_url.'" class="notice-dismiss" style="text-decoration:none;"><span class="screen-reader-text">Dismiss this notice.</span></a>
				</div>';
		}
});
}
