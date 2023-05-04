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

Version: 230504
Stable tag: 230504

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

Tested up to: 6.3-alpha-55716
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
${__FILE__}['tmp'] = '230504'; //version//
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

//230411 Promo upgrade
if (is_admin() && !defined('WS_PLUGIN__S2MEMBER_PRO_VERSION')) {
	// Dismiss
	add_action('admin_init', function(){
		$user_id = get_current_user_id();
		if (isset($_GET['s2-dismiss-230411']))
				add_user_meta($user_id, 's2_notice_dismissed_230411', 'true', true);
	});
	// Notice
	add_action('admin_notices', function(){
		$user_id = get_current_user_id();
		$logo_url = $GLOBALS['WS_PLUGIN__']['s2member']['c']['dir_url'].'/src/images/logo-square-big.png';
		$dismiss_url = add_query_arg('s2-dismiss-230411', '', $_SERVER['REQUEST_URI']);
		$color1 = '#009bff'; // blue
		$color2 = '#89ccf6'; // blue glow
		$color3 = '#ff00c3'; // pink
		if (isset($_GET['s2-show-notice']) || !get_user_meta($user_id, 's2_notice_dismissed_230411')) {
			echo '
				<div class="notice" style="position:relative; border-left-color:'.$color2.'; box-shadow: 0px 0px 6px 0px '.$color1.' !important;">
					<table><tr>
					<td><a href="https://s2member.com/" target="_blank"><img src="'.$logo_url.'" height="70" width="70" align="top" style="padding-right:1em; filter: hue-rotate(0deg) saturate(80) brightness(100%); -webkit-filter: hue-rotate(0deg) saturate(80) brightness(100%);" /></a></td>
					<td>
						<span style="font-style:italic;"><b>I\'m very happy you\'re using s2Member!</b> 💕 so I discounted <a href="https://s2member.com/prices" target="_blank" style="color:'.$color3.' !important; font-weight:bold;">20% OFF s2Member Pro</a> for you, if you get it now...<br />
						Make more money with <a href="https://s2member.com/testimonials/" target="_blank" style="color:'.$color3.' !important; font-weight:bold;">s2Member Pro!</a> with on-site payments, success redirections, reminder emails, <a href="https://s2member.com/features/" target="_blank">and more!</a></span><br />
						<b><i>This is a limited offer and expires soon...</i> ➡️ <i><a href="https://s2member.com/prices/" target="_blank" style="color:'.$color1.' !important;">Secure your lifetime license NOW at the best price!</a></i></b>&nbsp; ⬅️ 😀<br />
					</td>
					</tr></table>
					<a href="'.$dismiss_url.'" class="notice-dismiss" style="text-decoration:none;"><span class="screen-reader-text">Dismiss this notice.</span></a>
				</div>';
		}
});
}
