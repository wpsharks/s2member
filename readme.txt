=== s2Member® Pro ===

Version: 161129
Stable tag: 161129

SSL Compatible: yes
bbPress® Compatible: yes
WordPress® Compatible: yes
BuddyPress® Compatible: yes
WP® Multisite Compatible: yes
Multisite Blog Farm Compatible: yes

Stripe™ Compatible: yes
PayPal® Pro Compatible: yes
PayPal® Standard Compatible: yes
Authorize.Net® Compatible: yes
Google® Checkout Compatible: yes
ClickBank® Compatible: yes

Tested up to: 4.7
Requires at least: 4.2
Requires: s2Member® Framework

Requires PHP: 5.2
Tested up to PHP: 7.0.12

Copyright: © 2009 WebSharks, Inc.
License: GNU General Public License v2 or later.
Contributors: WebSharks, JasWSInc, raamdev, KristineDS, renzms

Author: s2Member® / WebSharks, Inc.
Author URI: http://s2member.com/
Donate link: http://s2member.com/donate/
Beta link: http://s2member.com/beta-testers/

Text Domain: s2member
Domain Path: ../s2member/src/includes/translations

Plugin Name: s2Member® Pro Add-on
Forum URI: http://s2member.com/forums/
Plugin URI: http://s2member.com/
Privacy URI: http://s2member.com/privacy-policy/
Changelog URI: http://s2member.com/changelog/
Video Tutorials: http://s2member.com/videos/
Knowledge Base: http://s2member.com/kb/
Newsletter: http://s2member.com/r/subscribe/
Pro Add-on / Home Page: http://s2member.com/
Pro Add-on / Prices: http://s2member.com/prices/
Pro Add-on / Auto-Update URL: https://www.s2member.com/
PayPal Pro Integration: http://s2member.com/r/pp-account-types/
Professional Installation URI: http://s2member.com/r/professional-installation/

Description: s2Member® Pro adds PayPal® Payments Pro integration, advanced import/export tools, and many other enhancements.
Tags: s2, s2member, s2 member, membership, users, user, members, member, subscribers, subscriber, members only, roles, capabilities, capability, register, signup, stripe, paypal, paypal pro, pay pal, authorize, authorize.net, google wallet, clickbank, click bank, buddypress, buddy press, bbpress, bb press, shopping cart, cart, checkout, ecommerce

s2Member® Pro adds Stripe™, PayPal® Payments Pro and Authorize.Net integrations, advanced import/export tools, and many other enhancements.

== Description ==

You can learn more about s2Member® Pro at [s2Member.com](http://www.s2member.com/).

== Installation ==

= s2Member® Pro is Very Easy to Install =

1. First, you need to have the latest version of the [s2Member® Framework](http://www.s2member.com/framework/) already installed.
2. Then, upload the `/s2member-pro` folder to your `/wp-content/plugins/` directory.
3. That's it! s2Member® Pro will be loaded into the free version of s2Member automatically.

= See Also (s2Member.com) =

[Detailed installation/upgrade instructions](http://www.s2member.com/pro/#!s2_tab_jump=s2-pro-install-update).

= Is s2Member compatible with Multisite Networking? =

Yes. s2Member and s2Member Pro, are also both compatible with Multisite Networking. After you enable Multisite Networking, install the s2Member plugin. Then navigate to `s2Member → Multisite (Config)` in the Dashboard on your Main Site.

== Frequently Asked Questions ==

= Please Check the Following s2Member® Resources =

* s2Member® FAQs: <http://s2member.com/faqs/>
* Knowledge Base: <http://s2member.com/kb/>
* Video Tutorials: <http://s2member.com/videos/>
* Community: <http://s2member.com/forums/>
* Codex: <http://s2member.com/codex/>

= Translating s2Member® =

Please see: <http://s2member.com/r/translations/>

== License ==

Copyright: © 2013 [WebSharks, Inc.](http://www.websharks-inc.com/bizdev/) (coded in the USA)

Released under the terms of the [GNU General Public License](http://www.gnu.org/licenses/gpl-2.0.html).

= Credits / Additional Acknowledgments =

* Software designed for WordPress®.
	- GPL License <http://codex.wordpress.org/GPL>
	- WordPress® <http://wordpress.org>
* JavaScript extensions require jQuery.
	- GPL License <http://jquery.org/license>
	- jQuery <http://jquery.com/>
* Readme parsing routines, powered (in part) by PHP Markdown.
	- BSD / GPL Compatible License <http://michelf.com/projects/php-markdown/license/>
	- PHP Markdown <http://michelf.com/projects/php-markdown/>
* Administration panel (tools icon) was provided by Everaldo.com.
	- LGPL License <http://www.everaldo.com/crystal/?action=license>
	- Everaldo <http://www.everaldo.com/crystal/?action=downloads>
* Administration panel (videos icon) was provided by David Vignoni.
	- LGPL License <http://www.iconfinder.com/search/?q=iconset%3Anuvola2>
	- David Vignoni <http://www.icon-king.com/>
* PayPal® and its associated API, buttons & services have been integrated into this software via external hyperlinks.
  The files/services provided by PayPal® are not distributed with this software. They have their own terms & conditions.
	- PayPal®, a 3rd party service, is powered by eBay, Inc. <http://www.paypal.com/>
	- PayPal® is a trademark of eBay, Inc. <http://www.ebay.com/>
* The W3C® and its associated validator & services have been integrated into this software via external hyperlinks.
  The files/services provided by the W3C® are not distributed with this software. They have their own terms & conditions.
	- The W3C®, a 3rd party service, is powered by the World Wide Web Consortium <http://validator.w3.org/>
	- W3C® is a trademark of the World Wide Web Consortium. <http://www.w3.org/>
* The MailChimp® services have been integrated into this software through a GPL compatible API & hyperlinks.
  The services provided by MailChimp® are not distributed with this software. They have their own terms & conditions.
	- MailChimp®, a 3rd party service, is powered by The Rocket Science Group, LLC <http://www.mailchimp.com/>
	- MailChimp® is a trademark of The Rocket Science Group, LLC. <http://www.mailchimp.com/terms-policies/terms-of-use/>
* The AWeber® services have been integrated into this software through hyperlinks & email commands.
  The services provided by AWeber® are not distributed with this software. They have their own terms & conditions.
	- AWeber®, a 3rd party service, is powered by AWeber Communications <http://www.aweber.com/about.htm>
	- AWeber® is a trademark of AWeber Communications. <http://www.aweber.com/service-agreement.htm>

== Upgrade Notice ==

= v160801 =

(Maintenance Release) Upgrade immediately.

== Changelog ==

= v161129 =

- (s2Member Pro) **Bug Fix:** Stripe refund notifications via the Stripe Webhook were always interpreted by s2Member as full refunds. This release corrects this bug so that s2Member will handle partial refunds via the Stripe API properly in all cases. Props @raamdev for reporting.

- (s2Member/s2Member Pro) **Bug Fix:** Updating profile via `[s2Member-Profile /]` when changing email addresses may leave the old email address on configured email list servers in some scenarios. Props @renzms for reporting. For further details see [issue #1007](https://github.com/websharks/s2member/issues/1007).

- (s2Member/s2Member Pro) **SSL Compatibility & Option Deprecation:** In previous versions of s2Member there was a setting in the UI that allowed you to force non-SSL redirects to the Login Welcome Page. By popular demand, this setting has been deprecated and removed from the UI.

  _**New Approach:** The new approach taken in the latest release of s2Member is to automatically detect when a non-SSL redirection should occur, and when it should not occur (i.e., when the default WordPress core behavior should remain as-is)._

  _s2Member does this by looking at the `FORCE_SSL_LOGIN` and `FORCE_SSL_ADMIN` settings in WordPress, and also at your configured `siteurl` option in WordPress. If you are not forcing SSL logins, or your `siteurl` begins with `https://` (indicating that your entire site is served over SSL), non-SSL redirects will no longer be forced by s2Member, which resolves problems on many sites that serve their entire site over SSL (a growing trend over the past couple years)._

  _Conversely, if `FORCE_SSL_LOGIN` or `FORCE_SSL_ADMIN` are true, and your configured `siteurl` option in WordPress does NOT begin with `https://` (e.g., just plain `http://`), then a non-SSL redirect **is** forced, as necessary, in order to avoid login cookie conflicts; i.e., the old behavior is preserved by this automatic detection._

  _Overall, this new approach improves compatibility with WordPress core, particularly on sites that serve all of their pages over `https://` (as recommended by Google)._

  _**Backward Compatibility:** As noted previously, the old option that allowed you to configure s2Member to force non-SSL redirects to the Login Welcome Page has been officially deprecated and removed from the UI. However, the old option does still exist internally, but only for backward compatibility. A WordPress filter is exposed that allows developers to alter the old setting if necessary. You can use the filter to force a `true` or `false` value._

  ```php
  <?php
  add_filter('ws_plugin__s2member_login_redirection_always_http', '__return_true');
  // OR add_filter('ws_plugin__s2member_login_redirection_always_http', '__return_false');
  ```

- (s2Member/s2Member Pro) **Bug Fix:** Username/password email being sent to users whenever Custom Passwords are enabled in your s2Member configuration and registration occurs via the default `wp-login.php?action=register` form. Fixed in this release. See also: [issue #870](https://github.com/websharks/s2member/issues/870) if you'd like additional details.

- (s2Member Pro) **Bug Fix:** In the `[s2Member-List /]` search box shortcode an empty `action=""` attribute produces a warning due to invalid syntax in HTML v5. Fixed in this release. See [Issue #1006](https://github.com/websharks/s2member/issues/1006)

- (s2Member/s2Member Pro) **IP Detection:** This release improves s2Member's ability to determine the current user's IP address. s2Member now searches through `HTTP_CF_CONNECTING_IP`, `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED`, `HTTP_VIA`, and `REMOTE_ADDR` (in that order) to locate the first valid public IP address. Either IPv4 or IPv6. Among other things, this improves s2Member's compatibility with sites using CloudFlare. See also: [issue #526](https://github.com/websharks/s2member/issues/526) if you'd like additional details.

- (s2Member Pro) **JSON API:** In the pro version it is now possible to use the s2Member Pro Remote Operations API to send and receive JSON input/output. This makes the Remote Operations API in s2Member compatible with a variety of scripting languages, not just PHP; i.e., prior to this release the Remote Operations API required that you always use PHP's `serialize()` and `unserialize()` functions when making API calls. The use of `serialize()` and `unserialize()` are no longer a requirement since input/output data is now sent and received in the more portable JSON format. For new code samples, please see: **Dashboard → s2Member → API / Scripting → Pro API For Remote Operations**. See also: [issue #987](https://github.com/websharks/s2member/issues/987) if you'd like additional details on this change.

  _**Note:** The old s2Member Pro Remote Operations API has been deprecated but will continue to function just like before (via `serialize()` and `unserialize()`) for the foreseeable future. Moving forward, we recommend the new JSON code samples. Again, you will find those under: **Dashboard → s2Member → API / Scripting → Pro API For Remote Operations**_

- (s2Member/s2Member Pro) Enforce data types when determining PHP constants. See [this GitHub issue](https://github.com/websharks/s2member/issues/989) if you'd like further details.

- (s2Member/s2Member Pro) **Phing Build Routines:** Starting with this release, developers working on the s2Member project are now able to perform builds of the software via the `websharks/phings` project; i.e., the structure of the plugin directories has been changed (slightly) to conform to Phing and PSR4 standards. This makes it easier for our developers to prepare and release new versions of the software in the future.

= v160801 =

- (s2Member/s2Member Pro) **WP v4.6 Compatibility.** A full round of tests was performed against this release of s2Member, s2Member Pro, and the upcoming release of WordPress v4.6. In particular, the new HTTP API needed testing, along with the new optimized loading sequence in WordPress v4.6. Our tests indicate there are no compatibility issues, and we therefore encourage all s2Member site owners to upgrade to WordPress v4.6 whenever it becomes available publicly.

- (s2Member/s2Member Pro) **Bug Fix:** Allow for `<` and `>` to work in the `[s2If php="" /]` shortcode attribute as expected. Some Visual Editors convert these into `&lt;` and `&gt;`, so it's necessary to interpret them as such whenever the shortcode is parsed by s2Member.

- (s2Member/s2Member Pro) **JS API:** Reducing the number of variables provided by the s2Member JavaScript API by default, and adding a new filter that allows them to all be enabled when/if desirable: `ws_plugin__s2member_js_api_constants_enable`. Props @JeffStarr for reporting.

= v160503 =

- (s2Member/s2Member Pro) **Security Enhancement:** This release forces `CURLOPT_SSL_VERIFYPEER` to a value of `TRUE` in the AWeber SDK that is used when/if you integrate with AWeber. In short, this forces AWeber to have a valid/verifiable SSL certificate before any data is exchanged between s2Member and the AWeber API behind-the-scenes. Props at WordPress security team for reporting this.

= v160424 =

- (s2Member/s2Member Pro) **PHP Compat./Bug Fix:** This follow-up release includes a patch that will prevent fatal errors when s2Member and/or s2Member Pro are installed on a site running PHP v5.2 or PHP v5.3; i.e., this release corrects a bug that was causing fatal errors on these older versions of PHP. _Note that s2Member and s2Member Pro are once again compatible with PHP v5.2+, up to PHP v7.0._ Props @krumch. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/938) for details.

= v160423 =

- (s2Member/s2Member Pro) **WP v4.5 Compatibility.** This release offers full compatibility with the latest release of WordPress v4.5. Nothing major was changed for standard WordPress installations, but there were a few subtle tweaks here and there to improve v4.5 compatibility. We encourage all users to upgrade right away.

  **NOTE: WP v4.5 for Multisite Networks running s2Member Pro:** This release corrects a bug first introduced in the previous release of s2Member Pro that resulted in an error message (`Uncaught Error: Class 'c_ws_plugin__s2member_mms_patches' not found`) when updating to WP v4.5. It has been corrected in this release, but in order to avoid this problem altogether please follow this procedure when upgrading WordPress.

  **WP v4.5 Multisite Upgrade Procedure:**

  - Upgrade s2Member and s2Member Pro ​_before_​ updating WordPress core.
  - Then upgrade WordPress core and observe that Multisite Patches are applied properly.

  _If you have already upgraded to WP v4.5 and worked past this issue by patching manually, that's fine. You can still upgrade s2Member and s2Member Pro. After the upgrade you may feel free to enable automatic patching again if that's desirable._

- (s2Member/s2Member Pro) **Bug Fix:** This release corrects a bug first introduced in the previous release which was causing a PHP warning about `cf_stream_extn_resource_exclusions`. A symptom was to have mysterious problems with `[s2Stream /]` or the `[s2File /]` shortcode. Fixed in this release. Props at @raamdev @renzms for reporting. See also [this GitHub issue](https://github.com/websharks/s2member/issues/901) for details.

- (s2Member/s2Member Pro) **PayPal SSL Compatibility:** This release of s2Member provides an `https://` IPN URL for PayPal IPN integrations. It also provides a helpful note (in the Dashboard) about a new requirement that PayPal has with respect to the IPN URL that you configure at PayPal.com. s2Member has been updated to help you with this new requirement.

  **New PayPal.com IPN Requirement:** PayPal.com is now requiring any new IPN URL that you configure to be entered as an `https://` URL; i.e., if you log into your PayPal.com account and try to configure a _brand new_ IPN URL, that URL _must_ use `https://`. PayPal.com will refuse it otherwise.

  However, the `notify_url=` parameter in standard PayPal buttons should continue to work with either `http://` or `https://`, and any existing configurations out there that still use an `http://` IPN URL should continue to work as well. So this is about planning for the future. We have been told that PayPal will eventually _require_ that all IPN URLs use an `https://` protocol; i.e., they will eventually stop supporting `http://` IPN URLs altogether (at some point in the future), they are not giving anyone a date yet. For this reason we strongly suggest that you [review the details given here](https://github.com/websharks/s2member/issues/914).

  Since PayPal is moving in a direction that will eventually require all site owners to have an SSL certificate in the future, s2Member's instructions (and the IPN URL it provides you with) will now be presented in the form of an `https://` URL with additional details to help you through the process of configuring an IPN handler for PayPal.

  See: **Dashboard → s2Member → PayPal Options → PayPal IPN Integration**

  Props @codeforest for reporting. See [this GitHub issue](https://github.com/websharks/s2member/issues/914) for further details.

- (s2Member/s2Member Pro) **Bug Fix:** Email field on Registration page not shown as required via `*` symbol like other fields in this form. Caused by a change in WordPress core. Fixed in this release. Props @spottydog63 @renzms. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/907) for details.

- (s2Member/s2Member Pro) **Bug Fix:** `E_NOTICE` level errors in cache handler when running in `WP_DEBUG` mode. Props at @KTS915 for reporting. Fixed in this release. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/917).

- (s2Member/s2Member Pro) **i18n Compatibility:** This release of s2Member moves the `load_plugin_textdomain()` call into the `plugins_loaded` hook instead of it being run on `init`. Props @KTS915 for reporting. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/899) for details.

- (s2Member Pro) **Multisite Patches:** Fixed a bug (`Uncaught Error: Class 'c_ws_plugin__s2member_mms_patches' not found`) whenever WordPress was being updated and Multisite Patches were being applied in the pro version of s2Member. See: [this GitHub issue](https://github.com/websharks/s2member/issues/929) for details.

- (s2Member/s2Member Pro) **Security Enhancement:** This release of s2Member defaults PayPal Button Encryption to a value of `on` instead of `off`; i.e., there is a new default behavior. Existing s2Member installations are unaffected by this change, but if you install s2Member on a new site you will notice that (if using PayPal Buttons), Button Encryption will be enabled by default.

  _Note that in order for Button Encryption to work, you must fill-in the API credentials for s2Member under: **Dashboard → s2Member → PayPal Options → PayPal Account Details**_

= v160303 =

- (s2Member/s2Member Pro) **Comet Cache Compat.:** This release improves compatibility with Comet Cache (formerly ZenCache), whenever you have it configured to cache logged-in users. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/888). Props @KTS915 for reporting!

- (s2Member Pro) **ClickBank IPN v6 Compat.:** Version 6 of the ClickBank IPN system was recently updated in a way that causes it to return `transactionType = CANCEL-TEST-REBILL` in test mode, instead of the previous value, which was: `TEST_CANCEL-REBILL`. s2Member Pro has been updated to understand either/or. See also [this GitHub issue](https://github.com/websharks/s2member/issues/882) for further details.

- (s2Member Pro) **Stripe Bug Fix:** This release corrects a bug caused by typos in the source code that were preventing refunds from being processed as expected whenever Stripe was integrated. Props @YearOfBenj for reporting this important issue. Props @patdumond for relaying vital information. See also [this GitHub issue](https://github.com/websharks/s2member/issues/874) if you'd like additional details.

- (s2Member Pro) **PayPal Bug Fix:** Under some conditions, the EOT behavior in s2Member Pro (when integrated with PayPal Pro) would immediately terminate access whenever a customer's subscription naturally expires. Recent versions of the Payflow system set the status to `EXPIRED`, and this was handled as an immediate EOT instead of as a delayed EOT that is subject to date calculations to determine the correct date on which a customer should lose access; i.e., based on what they have already paid for. Fixed in this release. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/873) if you'd like additional details.

- (s2Member Pro) **One-Time Offer Bug Fix:** This release corrects some inconsistencies in the One-Time Offers system that comes with s2Member Pro. Symptoms included seemingly unpredictable behavior whenever redirections were configured without a specific Membership Level. Props @jacobposey for reporting. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/855) if you'd like additional details.

- (s2Member/s2Member Pro) **Bug Fix:** s2Member was not properly respecting `DISALLOW_FILE_MODS` in a specific scenario related to GZIP. Props @renzms @kristineds. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/832) for further details.

- (s2Member,s2Member Pro) **Bug Fix:** Resolved a minor glitch in the **WordPress Dashboard → Settings → General** panel, where s2Member's notice regarding Open Registration was inadvertently forcing the entire page into italics. Props @renzms @kristineds @raamdev ~ See also: [this GitHub issue](https://github.com/websharks/s2member/issues/831) if you'd like additional details.

- (s2Member/s2Member Pro) **PayPal Sandbox:** This release updates the inline documentation under the PayPal Account Settings section of s2Member. We now suggest that instead of enabling PayPal Sandbox Mode (sometimes buggy at best), that site owners run tests with low-dollar amounts against a live PayPal account instead; e.g., $0.01 test transactions in live mode work great also. See [this GitHub issue](https://github.com/websharks/s2member/issues/891) if you'd like additional details. Props @raamdev for mentioning this again.

= v160120 =

- (s2Member,s2Member Pro) **Bug Fix:** Resolved a minor glitch in the **WordPress Dashboard → Settings → General** panel, where s2Member's notice regarding Open Registration was inadvertently forcing the entire page into italics. Props @renzms @kristineds @raamdev ~ See also: [this GitHub issue](https://github.com/websharks/s2member/issues/831) if you'd like additional details.

- (s2Member) **Multisite Support:** This release of s2Member (the free version only) removes full support for Multisite Networks, which is now a Pro feature; i.e., only available in the Pro version.

  ##### Is s2Member still compatible with WordPress Multisite Networking?
  Multisite support is no longer included in the s2Member Framework. However, it is available with s2Member Pro. s2Member Pro is compatible with Multisite Networking. After you enable Multisite Networking, install the s2Member Pro Add-On. Then, navigate to `s2Member → Multisite (Config)` in the Dashboard of your Main Site. You can learn more about s2Member Pro at [s2Member.com](http://www.s2member.com/).

  ##### I was using the free version in a Multisite Network before. What happened?
  s2Member (when running on a Multisite Network) requires minor alterations in WordPress core that are not compatible with plugins available at WordPress.org (i.e., not allowed) at this time. For this reason, full support for Multisite Networks is now available only in the pro version.

  ##### What if I already configured Multisite options on a site running the free version?
  If you already customized s2Member's Multisite Network configuration options in a previous release, those settings will remain and still be functional over the short-term; i.e., the functionality that makes s2Member compatible with Multisite Networking is still included, even in the s2Member Framework. However, the routines that deal with core patches, and those that allow you to change Multisite options are no longer available. You will need to acquire the Pro version. Or, you can revert to [a previous release](http://s2member.com/release-archive/). s2Member Framework v151218 is suggested if you go that route.

  _See also: [this GitHub issue](https://github.com/websharks/s2member/issues/850) for further details._

= v151218 =

- (s2Member Pro) **Reminder Email Notification Exclusions:** It is now possible to enable/disable EOT Renewal/Reminder Email notifications on a per-user basis. You can edit a user's profile in the WP Dashboard and check "_No (exclude)_" to prevent specific users from receiving any reminder emails that you configured. Props at @patdumond @luisrock. See also [this GitHub issue](https://github.com/websharks/s2member/issues/816).

- (s2Member) **PHP v7 Compat.:** This release addresses one remaining issue with the `preg_replace` `/e` modifier as reported in [this GitHub issue](https://github.com/websharks/s2member/issues/811). Props @nerdworker for reporting. Thanks!

- (s2Member/s2Member Pro) **WP v4.4 Compat.:** This release corrects an issue that impacted sites attempting to run s2Member on a Multisite Network; i.e., it corrects a problem with the `load.php` patch against the latest release of WordPress. Props @crazycoolcam for reporting! See also [this GitHub issue](https://github.com/websharks/s2member/issues/812).

- (s2Member/s2Member Pro) **Getting Help:** This release adds a new menu page titled, "Getting Help w/ s2Member". This new section of your Dashboard provides quick & easy access to s2Member KB articles, suggestions, and our tech support department (for pro customers). Props @patdumond @raamdev. See also [this GitHub issue](https://github.com/websharks/s2member/issues/814).

= v151210 =

- (s2Member/s2Member Pro) **WP/PHP Compat:** Updated for compatibility with WordPress 4.4 and PHP v7.0. Note that s2Member and s2Member Pro also remain compatible with WordPress 4.3 and PHP 5.2. However, PHP 5.5+ is strongly recommended.

- (s2Member Pro) **New Feature! EOT Renewal/Reminder Email Notifications:** This release adds a long-awaited feature which allows you to configure & send EOT Renewal/Reminder Email notifications to your customers; to let them know their account with you will expire soon.

  It's possible to configure one or more notifications, each with a different set of recipients, and a different subject and message body. Notifications can be sent out X days before the EOT occurs, _the day_ of the EOT, or X days after the EOT has already occurred; e.g., to encourage renewals.

  See: **Dashboard → s2Member → Stripe Options → EOT Renewal/Reminder Email(s)**
  _Also works with PayPal Pro, Authorize.Net, and ClickBank._

  Props @clavaque @KTS915 @raamdev @patdumond @kristineds @pagelab @chronicelite @csexplorer17 @radven, and all of our great supporters. See [this GitHub issue](https://github.com/websharks/s2member/issues/122#issuecomment-161531763).

- (s2Member/s2Member Pro) **Cleanup:** This release improves the list of Other Gateways; moving deprecated payment gateways to the bottom of the list and improving the display of the list overall. Props @kristineds @clavaque. For further details, see [this GitHub issue](https://github.com/websharks/s2member/issues/715).

- (s2Member/s2Member Pro) **Bug Fix:** This release corrects an "Insecure Content Warning" that may have appeared in certain portions of the s2Member Dashboard panels whenever you accessed your Dashboard over the `https` protocol. The issue was seen in Google Chrome and it was simply a `<form>` tag that referenced the s2Member mailing list. This is now hidden by default if you access the Dashboard over SSL, in order to avoid this warning. Props @patdumond for reporting. Props @renzms for fixing. See also [this GitHub issue](https://github.com/websharks/s2member/issues/678) if you'd like additional details.

- (s2Member Pro) **Stripe Locale:** This release adjusts the Stripe overlay so that it will automatically display in the language associated with a visitor's country. This was accomplished by setting the Stripe Checkout variable `locale: 'auto'` as suggested in [this GitHub issue](https://github.com/websharks/s2member/issues/728). Props @renzms

- (s2Member Pro) **Stripe Bug Fix:** This release improves the way Stripe Image Branding and Stripe Statement Descriptions are applied whenever you intentionally leave them empty. It also changes the default value of Stripe Image Branding to an empty string; which will tell Stripe to use the account-level default value that you configured in your Stripe Dashboard in favor of that which you configure with s2Member. The choice is still yours, but this release sets what others have told us are better default values. See also [this GitHub issue](https://github.com/websharks/s2member/issues/666) if you'd like additional details.

- (s2Member Pro) **Stripe Enhancement:** This release makes it possible to configure the Stripe "Remember Me" functionality with s2Member; i.e., it is now possible to turn this on/off if you so desire. See also [this GitHub issue](https://github.com/websharks/s2member/issues/357) for details.

- (s2Member Pro) **Stripe Enhancement:** This release makes it possible for you to tell Stripe to collect a customer's full Billing Address and/or full Shipping Address. See [this GitHub issue](https://github.com/websharks/s2member/issues/667) for additional details.

- (s2Member/s2Member Pro) **UI Clarity:** This release improves the way the New User Email Notification panel behaves whenever you also have Custom Passwords enabled with s2Member. The New User Email Notification is only sent when Custom Passwords are off, so this panel should disable itself whenever that is the case. Fixed in this release. Props @raamdev See also: [this GitHub issue](https://github.com/websharks/s2member/issues/739) if you'd like additional details.

- (s2Member/s2Member Pro) **Bug Fix:** This release resolves a minor issue for developers running Vagrant and VVV with symlink plugins. Props @magbicaleman ~ See [this GitHub issue](https://github.com/websharks/s2member/issues/717) for further details.

- (s2Member Pro) **Conflict Resolution:** This release resolves a conflict with the WP Full Stripe plugin and any other plugins that already load an existing copy of the Stripe SDK at runtime; in concert with s2Member Pro. See [this GitHub issue](https://github.com/websharks/s2member/issues/750) if you'd like additional details.

- (s2Member/s2Member Pro) **New Log File:** This release of s2Member adds a new log file that keeps track of all automatic EOTs that occur through the underlying CRON job. The new log file is named: `auto-eot-system.log` and you can learn more about this file and view it from: **Dashboard → s2Member → Log Files (Debug) → Log Viewer**. Props @raamdev ~ See [this GitHub issue](https://github.com/websharks/s2member/issues/759) if you'd like additional details.

- (s2Member Pro) **`[s2Member-List /]` Bug Fix:** This release resolves a problem in the `[s2Member-List /]` shortcode whenever it is configured to search Custom Fields generated with s2Member. Props @patdumond @renzms. See [this GitHub issue](https://github.com/websharks/s2member/issues/765) if you'd like additional details.

- (s2Member Pro) **Stripe Enhancement:** This release updates s2Member's Stripe integration so that any Buy Now transaction spawns a Stripe popup with the amount and full description filled within the popup itself as well. Props @raamdev. See [this GitHub issue](https://github.com/websharks/s2member/issues/749) for further details.

- (s2Member/s2Member Pro) **WP v4.3 Compat.** This release addresses a minor conflict between functionality provided by s2Member and that of the WordPress core itself. Whenever you change a user's password by editing their account, you can choose to send them an email about this change (or not). Since WordPress v4.3, the WordPress core will _also_ send a more vague email to notify the user of a password change, which is not customizable. This release disables that default email notification in favor of the more helpful and customizable email message that can be sent by s2Member. Simply tick the "Reset Password & Resend New User Email Notification" checkbox whenever you are editing a user. Props @patdumond for reporting. See also [this GitHub issue](https://github.com/websharks/s2member/issues/777) if you'd like additional details.

- (s2Member/s2Member Pro) **PayPal Compat.** This release resolves a conflict between s2Member and a nasty bug at PayPal.com that came to light recently. In some cases, customers reported that clicking the "Continue" button at PayPal.com simply reloaded the page and gave no response. We found that this was attributed to a bug on the PayPal side (see [792](https://github.com/websharks/s2member/issues/792)). To work around this bug, we are using a new default value for the `ns="1"` shortcode attribute in PayPal Pro-Forms and PayPal Buttons. The new default value is `ns="0"`, which seems to work around this bug for the time being. Props @patdumond @raamdev for reporting and testing this fix. See also [full report here](https://github.com/websharks/s2member/issues/792).

  - `ns="0"` (**new default**) = prompt for a shipping address, but do not require one
  - `ns="1"` (old default) = do not prompt for a shipping address whatsoever

  See also: **Dashboard → s2Member → PayPal Pro-Forms → PayPal Shortcode Attributes (Explained)**

- (s2Member/s2Member Pro) **Getting Started:** The old Quick Start Guide was renamed to "Getting Started" in this release. It was also cleaned up and improved a bit; i.e., brought up-to-date. In addition, there is a new welcome message for first-time users of the software that invites them to read over the Getting Started page before they begin. Props @raamdev. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/655).

- (s2Member Pro) **Stripe Bug Fix:** This release corrects a problem with Stripe refund and chargeback notification handling. s2Member Pro will now receive and handle Stripe refund and/or chargeback events (through your existing Webhook) as expected.

  See: **Dashboard → s2Member → Stripe Options → Automatic EOT Behavior** for options that allow you to control the way in which s2Member should respond whenever a refund is processed, or when a dispute (chargeback) occurs at Stripe.

  Props @ElizWS and @tubiz w/ AffiliateWP. See also [this GitHub issue](https://github.com/websharks/s2member/issues/706).

- (s2Member Pro) **`[s2Member-List /]`** Added the ability to search usermeta data too. For instance, you can now search `first_name`, `last_name`, `nickname`, `description`, `s2member_subscr_id`, `s2member_custom`, etc, etc. See [this GitHub issue](https://github.com/websharks/s2member/issues/596).

  _**Note:** The `first_name`, `last_name`, and `nickname` columns are now a part of the default value for the `search_columns=""` attribute in the `[s2Member-List /]` shortcode. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/596). Props @patdumond for her ideas._

- (s2Member Pro) **`[s2Member-List /]`** There are some new `orderby=""` options. You may now choose to order the list by: `first_name`, `last_name`, or `nickname`.

- (s2Member Pro) **`[s2Member-List /]`** It is now possible to search through s2Member Custom Registration/Profile Fields that may contain an array of values; i.e., you can now search _any_ Custom Registration/Profile Field in s2Member. For instance, if a field is designed to accept multiple selections, or you provide a set of multiple checkbox options. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/555).

- (s2Member Pro) **`[s2Member-List /]` Bug Fix:** Meta fields that contained a timestamp were being displayed by the `date_i18n()` function in WP core. However, the time offset calculation was wrong; i.e., not a match to the local time configured by your installation of WordPress. Fixed in this release.

- (s2Member Pro) **`[s2Member-List /]` Bug Fix:** Minor formatting corrections for replacement codes made available for the `link_*=""` attributes in the `[s2Member-List /]` shortcode.

- (s2Member Pro) **`[s2Member-List /]`:** It is now possible to search for an exact match by surrounding your search query with double quotes; e.g., `"john doe"` (in quotes, for an exact match), instead of the default behavior, which is `*john doe*` behind-the-scenes; i.e., a fuzzy match.

- (s2Member Pro) **`[s2Member-List /]`:** Several behind-the-scenes performance enhancements.

- (s2Member/s2Member Pro) **PHP 7 Compat.** This release of s2Member removes its use of the `/e` modifier in calls to `preg_replace()`, which was deprecated in PHP 5.5 and has been removed in PHP 7. Props @bridgeport. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/415).

For older Changelog entries, please see the [changelog.md](https://github.com/websharks/s2member/blob/master/s2member/changelog.md) file.
