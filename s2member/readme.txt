=== s2Member® Framework (Member Roles, Capabilities, Membership, PayPal Members) ===

Version: 130816
Stable tag: 130816

SSL Compatible: yes
bbPress® Compatible: yes
WordPress® Compatible: yes
BuddyPress® Compatible: yes
WP® Multisite Compatible: yes
Multisite Blog Farm Compatible: yes

PayPal® Standard Compatible: yes
PayPal® Pro Compatible: yes w/s2Member® Pro
Authorize.Net® Compatible: yes w/s2Member® Pro
Google® Checkout Compatible: yes w/s2Member® Pro
ClickBank® Compatible: yes w/s2Member® Pro

Tested up to: 3.7-alpha
Requires at least: 3.3

Copyright: © 2009 WebSharks, Inc.
License: GNU General Public License v2 or later.
Contributors: WebSharks

Author: s2Member® / WebSharks, Inc.
Author URI: http://www.s2member.com/
Donate link: http://www.s2member.com/donate/

Text Domain: s2member
Domain Path: /includes/translations

Plugin Name: s2Member® Framework
Forum URI: http://www.s2member.com/forums/
Plugin URI: http://www.s2member.com/framework/
Privacy URI: http://www.s2member.com/privacy/
Video Tutorials: http://www.s2member.com/videos/
Knowledge Base: http://www.s2member.com/kb/
Pro Module / Home Page: http://www.s2member.com/
Pro Module / Prices: http://www.s2member.com/prices/
Pro Module / Auto-Update URL: http://www.s2member.com/
PayPal Pro Integration: http://www.s2member.com/videos/ED70D90C6749DA3D/
Professional Installation URI: http://www.s2member.com/professional-installation/

Description: s2Member® — a powerful (free) membership plugin for WordPress®. Protect members only content with roles/capabilities.
Tags: s2, s2member, s2 member, membership, users, user, members, member, subscribers, subscriber, members only, roles, capabilities, capability, register, signup, paypal, paypal pro, pay pal, authorize, authorize.net, google checkout, clickbank, click bank, buddypress, buddy press, bbpress, bb press, shopping cart, cart, checkout, ecommerce

s2Member® — a powerful (free) membership plugin for WordPress®. Protect members only content with roles/capabilities.

== Installation ==

**NOTE:** Please do NOT use the WordPress® forums to seek company support. Support for s2Member® is handled in [our own support forums](http://www.s2member.com/forums/).

= s2Member® is very easy to install (instructions) =
1. Upload the `/s2member` folder to your `/wp-content/plugins/` directory.
2. Activate the plugin through the `Plugins` menu in WordPress®.
3. Navigate to the `s2Member® Options` panel for configuration details.

= See Also (s2Member.com) =
[Detailed installation/upgrade instructions](http://www.s2member.com/framework/#!s2_tab_jump=s2-framework-install-update).

= Is s2Member compatible with Multisite Networking? =
Yes. s2Member and s2Member Pro, are also both compatible with Multisite Networking. After you enable Multisite Networking, install the s2Member plugin. Then navigate to `s2Member -› Multisite (Config)` in the Dashboard on your Main Site.

== Description ==

**NOTE:** Please do NOT use the WordPress® forums to seek company support. Support for s2Member® is handled in [our own support forums](http://www.s2member.com/forums/).

[youtube http://www.youtube.com/watch?v=2C3Lan7vxw0 /]

The s2Member® Framework (free) integrates with PayPal® Website Payments Standard (also free), and fully supports recurring billing. s2Member supports custom Pages for registration (including Custom Registration/Profile Fields), account access, and a lot more. s2Member is compatible with Multisite Networking too, and even with BuddyPress and bbPress. With the s2Member® Pro add-on (an optional paid upgrade), you can add support for unlimited Membership Levels, PayPal® Website Payments Pro (w/ Pro Forms to facilitate on-site credit card processing), Authorize.Net® (also with Pro Forms), Google® Checkout, ClickBank®, advanced User Import/Export tools, the ability to use Coupon Codes, and many other enhancements. Videos available at: [s2Member.com / Videos](http://www.s2member.com/videos/).

s2Member supports Free Subscribers (at Level #0), and up to four primary Membership Levels [1-4] (unlimited with s2Member® Pro). You can label your Membership Levels anything you like. The defaults are Free, Bronze, Silver, Gold, and Platinum. s2Member also supports an unlimited number of Custom Capability Packages. Custom Capabilities are an easy way to extend s2Member in creative ways. Custom Capabilities allow you to create an unlimited number of Membership Packages, all with different Capabilities and prices.

s2Member allows you to protect Pages, Posts, Tags, Categories, URIs, URI word fragments, URI Replacement Codes for BuddyPress, Specific Post/Page "Buy Now" Access, and even portions of content within Posts/Pages/themes/plugins. All settings are configurable through the s2Member Options panel. This makes s2Member VERY easy to integrate into any site powered by WordPress®. With s2Member, you can also protect downloadable files, using restrictions to control how many downloads can occur within a certain amount of time; all based on Membership Level or even Custom Capabilities. sMember® can even integrate with Amazon® S3 and CloudFront (optional) for serving protected audio/video streams over an RTMP protocol.

You can learn more about s2Member® at [s2Member.com](http://www.s2member.com/).

== Frequently Asked Questions ==

**NOTE:** Please do NOT use the WordPress® forums to seek company support. Support for s2Member® is handled in [our own support forums](http://www.s2member.com/forums/).

= Please check the following s2Member® resources: =
* s2Member® FAQs: http://www.s2member.com/faqs/
* Knowledge Base: http://www.s2member.com/kb/
* Video Tutorials: http://www.s2member.com/videos/
* Support Forums: http://www.s2member.com/forums/
* Codex: http://www.s2member.com/codex/

= Translating s2Member® =
Please see [this FAQ entry](http://www.s2member.com/faqs/#s2-faqs-translations)

== Upgrade Notice ==

= v130816 =
(Maintenance Release) Upgrade immediately.

== Changelog ==

= v130816 =
* (s2Member Pro) **Compatibility, ClickBank (#467)** Improving support for ClickBank PitchPlus Upsell Flows. Please see [this thread](http://www.s2member.com/forums/topic/clickbank-buttons-not-working/#post-55725) for further details.
* (s2Member/s2Member Pro) **User Search on Multisite Networks (#468)** User search functionality was partially broken for Child Blogs in a Multisite Network after some improvements were implemented in s2Member® v130731. The issue has now been corrected in this release for Multisite Networks. For further details, please see [this thread](http://www.s2member.com/forums/topic/user-search-no-longer-working/#post-55778).
* (s2Member/s2Member Pro) **Z-Index in Menu Pages (#461)** Stacking order against a WordPress® installation running a Dashboard with a collapsed sidebar menu (left side) was causing some UI problems. Fixed in this release.
* (s2Member/s2Member Pro) **SSL Compatibility (#437)** Adding a new option in the `s2Member® -› General Options -› Login Welcome Page` section. The default value for this new option is always `yes`. However, the default functionality can be turned off (if you prefer). This new option allows site owners to better integrate with a core WordPress® feature commonly referred to as `FORCE_SSL_LOGIN` and/or `FORCE_SSL_ADMIN`. This new feature can be used, or not. It is intended mainly for site owners running w/ `FORCE_SSL_LOGIN` and/or `FORCE_SSL_ADMIN`.
* (s2Member/s2Member Pro) **Login/Registration Design Option (#437)** Adding a new option in the `s2Member® -› General Options -› Login/Registration Design` section. This new option (found at the bottom of `s2Member® -› General Options -› Login/Registration Design`) allows a site owner to show/hide the `Back To Home Page` link at the bottom of the default WordPress® Login/Registration system. This can be useful for site owners running w/ `FORCE_SSL_LOGIN` and/or `FORCE_SSL_ADMIN`; where they would prefer NOT to link customers back to the main site under a default HTTPS link; but rather, create their own link and gain better control over this area of their site.
* (s2Member/s2Member Pro) **Videos (#467)** Updating internal documentation found in `Dashboard -› s2Member® -› Quick-Start`. Specifically, the video player here was integrated with an older version of the YouTube® API and was not working properly. Fixed in this release.

= v130802 =
* (s2Member Pro) **Compatibility, WordPress® v3.6** Updating s2Member® Pro Form templates and their underlying CSS. This update improves their appearance against the Twenty Thirteen theme that comes with WordPress® v3.6. Specifically, some of the Pro Form buttons were a little out of place in this new default theme. Fixed in this release.
* (s2Member Pro) **Compatibility, Checkout Options (#443)** Revision 3. Updating this feature to support a wider variety of WordPress® configurations and content filters. This update also resolves an empty `desc=""` attribute error reported by some site owners. Feature description... It is now possible to build dropdown menus offering your customers a variety of options using a Single Pro Form. This is accomplished quite easily using Shortcodes. For full details and examples, please check this section of your Dashboard in the latest release. See: `s2Member® -› PayPal® Pro Forms -› Wrapping Multiple Shortcodes as "Checkout Options"`. NOTE: this works for PayPal® Pro Forms, and ALSO for Authorize.Net Pro Forms.

= v130801 =
* (s2Member Pro) **New Feature; Checkout Options (#403)** Revision 2. Updating documentation on this new feature to prevent conufusion for site owners. s2Member® Pro now supports "Checkout Options". It is now possible to build dropdown menus offering your customers a variety of options using a Single Pro Form. This is accomplished quite easily using Shortcodes. For full details and examples, please check this section of your Dashboard in the latest release. See: `s2Member® -› PayPal® Pro Forms -› Wrapping Multiple Shortcodes as "Checkout Options"`. NOTE: this works for PayPal® Pro Forms, and ALSO for Authorize.Net Pro Forms.

= v130731 =
* (s2Member Pro) **New Feature; Checkout Options (#403)** s2Member® Pro now supports "Checkout Options". It is now possible to build dropdown menus offering your customers a variety of options using a Single Pro Form. This is accomplished quite easily using Shortcodes. For full details and examples, please check this section of your Dashboard in the latest release. See: `s2Member® -› PayPal® Pro Forms -› Wrapping Multiple Shortcodes as "Checkout Options"`. NOTE: this works for PayPal® Pro Forms, and ALSO for Authorize.Net Pro Forms.
* (s2Member Pro) **Free Checkout (#403)** It is now possible to offer a 100% free checkout experience using any of s2Member's Pro Form Shortcodes. In previous releases of s2Member® it was not possible to set the `ra=""` Attribute to a zero dollar amount. Now it is! This works for PayPal® Pro Forms, and also for Authorize.Net Pro Forms.
* (s2Member Pro) **100% Off Coupons (#403)** It is now possible to offer a 100% off coupon. This works for PayPal® Pro Forms, and also for Authorize.Net Pro Forms. See: `s2Member® -› Pro Coupon Codes` for details and examples.
* (s2Member Pro) **Expiration Date Dropdowns (#428)** This release improves all s2Member® Pro Form templates by adding dropdown menus for the customer's credit card expiration month/year instead of the simple text input field used in previous releases.
* (s2Member/s2Member Pro) **MySQL Wait Timeout (#349)** s2Member now automatically increases the MySQL `wait_timeout` to `300` seconds during s2Member processing routines. Reason for increase: should any 3rd party service API result in unexpected connection timeouts (such as PayPal, Authorize.Net, Amazon, MailChimp, AWeber, etc); this may cause a delay that could potentially exceed the default `wait_timeout` of `30` seconds on the MySQL resource handle that is global to all of WordPress. Increasing `wait_timeout` before transaction processing will decrease the chance of failure after a timeout is exceeded. Among other things, this resolves an elusive bug where there are mysterious 404 errors after checkout under the right scenario (e.g. when an unexpected timeout occurs). This may also resolve problems associated w/ some mysterious reports where emails were not sent during s2Member's attempt to complete post-processing of a transaction (and/or where other portions of post-processing failed under rare circumstances).
* (s2Member/s2Member Pro) **Alternative Views (#300)** This release gives s2Member® the ability to hide protected content in widgets that list protected WordPress® Pages. This is a new Alternative View in the Dashboard. See: `s2Member® -› Restriction Options -› Alternative Views` for further details please.
* (s2Member/s2Member Pro) **Documentation Update (#350)** Subtle improvements to the built-in documentation pertaining to s2Member's Automatic List Transitioning feature in the Dashboard. See: `s2Member® -› API/List Servers -› Automatic Unsubscribes` for further details please.
* (s2Member/s2Member Pro) **Bug Fix (#387)** In s2Member® Only mode, a recursive scan for the WordPress® `/wp-load.php` file was failing somtimes when/if a custom directory was configured for plugins. Fixed in this release. See [this thread](http://www.s2member.com/forums/topic/problem-with-wordpress-folder-search-code/) for further details.
* (s2Member/s2Member Pro) **Bug Fix (#418)** Fixed incorrect pagination of user search results in the Dashboard.
* (s2Member/s2Member Pro) **Bug Fix (#418)** Fixed slow query against user searches in the Dashboard.
* (s2Member/s2Member Pro) **Bug Fix (#418)** Fixed incorrect result totals under some rare scenarios in user search results.

= v130617 =
* (s2Member/s2Member Pro) **IP Restrictions (#148)** It is now possible to introduce a custom template file that controls the error message displayed when/if a user breaches security by exceeding your maximum unique IP addresses; as configured under `s2Member -› Restriction Options -› Unique IP Restrictions`. If you would like to use a custom template for this message, please copy the default template file from `/s2member/includes/templates/errors/ip-restrictions.php` and place this file into your active WordPress® theme directory (or into the `/wp-content/` directory if you prefer). s2Member will automatically find your custom template in one of these locations; and s2Member will use your custom template instead of the built-in default.
* (s2Member Pro) **Bug Fix (#302)** Updating Authorize.Net Pro Form Generator to support max days of `365` instead of `7`.
* (s2Member Pro) **Amazon S3 Secret Access Keys (#321)** Updating UI configuration panel to better explain what a Secret Access Key is; and adding a note about Secret Access Keys for Amazon S3 integration. Amazon® is deprecating Secret Access Keys, but they ARE still required for digitally signed URLs. This update changes nothing in s2Member's functionality. It simply adds some additional detail to a configuration field that will assist site owners integrating s2Member w/ Amazon S3 for the first time.
* (s2Member/s2Member Pro) **Translations (#317)** Updating `/s2member/includes/translations/translations.txt` (README file). Adding blurp about fuzzy translation entries in existing PO files that translate s2Member. This contains some additional tips on how to update existing PO files upon each release of s2Member and/or s2Member Pro.
* (s2Member/s2Member Pro) **Bug Fix (#321)** Fixing bug in `/s2member/includes/templates/cfg-files/s2-cross-xml.php` for S3 Buckets (resulting in `crossdomain.xml`). This file contained a parse error and was causing some problems for site owners integrating Adobe Flash content served via Amazon S3. Fixed in this release.
* (s2Member Pro) **PayPal Pro Forms (#315)** Adding note in the Dashboard here (`s2Member -› PayPal Pro Forms -› Shortcode Attributes Explained`). In the list of Shortcode Attributes we are adding a note regarding max character length for the `desc=""` Attribute in a PayPal Pro Form Shortcode. This can be as long as you like. However, all descriptions passed through PayPal® APIs are truncated automatically to 60 characters max (e.g. the maximum allowed length for PayPal® descriptions is 60 characters). Nothing new here, but we thought it would be a good idea to clarify this behavior in the documentation. Updated in this release.
* (s2Member Pro) **PayPal Pro Forms (#312)** Preventing the use of multiple Pro Forms in the same Post/Page. This has never been possible, it is known to break the functionality of s2Member Pro Forms. Please limit Pro Form Shortcodes to ONE for each Post/Page; and do NOT attempt to use more than one Pro Form Shortcode on the same Post/Page (at the same time). In this release we have added a friendly JavaScript alert/warning for site owners that attempt this, so that problems and confusion can be avoided in this unlikely scenario.

= v130513 =
* (s2Member/s2Member Pro) **s2Stream Shortcode Bug Fix (#256)** Fixing a bug first introduced in the previous release where we added support for `player_aspectratio`. This quick update corrects the PHP parse error at line #154 of `sc-files-in.inc.php`. It also corrects the behavior of the `player_height=""` and `player_aspectratio=""` Shortcode Attributes for the `s2Stream` Shortcode. Many thanks to everyone that reported this bug.
* (s2Member Pro) **Codestyling Localization** Removing symlink creator for Codestyleing Localization compatibility. There have been some reports of problems during WordPress® automatic upgrades (when/if the symlink exists). Until we can find a way to avoid this, we're disabling the automatic symlink generator. If you're running the Codestyling Localization plugin together with s2Member Pro, you will need to create the symlink yourself if you want to make s2Member fully compatible. Please create a symlink here: `/wp-content/plugins/s2member/s2member-pro` that points to the s2Member Pro directory: `/wp-content/plugins/s2member-pro`. See notes in previous changelog for further details on this.

 **IMPORTANT NOTE:** If you upgraded previously to v130510 (and you ran the Codestyling Localization plugin together with s2Member® v130510 — at any time); please delete this symlink via FTP: `/wp-content/plugins/s2member/s2member-pro`. Please do this BEFORE attempting an automatic upgrade via WordPress®.

 If you missed this note and you've already attempted an automatic upgrade, you will have trouble. Here's how to correct the problem.

 1. Log into your site via FTP and delete these two directories manually.
  `/wp-content/plugins/s2member` and `/wp-content/plugins/s2member-pro`.

 2. Now, please follow the [instructions here](http://www.s2member.com/pro/#!s2_tab_jump=s2-pro-install-update) to upgrade s2Member® Pro manually.


= v130510 =
* (s2Member Pro) **Authorize.Net UK (and Other Currencies) (#104)** Adding support for Authorize.Net UK and other currencies too. s2Member Pro now officially supports Authorize.Net UK Edition. It is now possible to change your Authorize.Net Pro Form Shortcode Attribute `cc="USD"` to one of these values: `cc="USD"`, or `cc="CAD"` or `cc="EUR"` or `cc="GBP"`. For further details, please see: `Dashboard -› Authorize.Net Pro Forms -› Shortcode Attributes (Explained)`.
* (s2Member Pro) **ClickBank Skins (#227)** Adding support for the `cbskin=""` Shortcode Attribute. For further details, please see: `Dashboard -› ClickBank Buttons -› Shortcode Attributes (Explained)`.
* (s2Member Pro) **ClickBank PitchPlus Upsell Flows (#227)** Adding support for ClickBank PitchPlus Upsell Flows via new Shortcode Attributes: `cbfid=""`, `cbur=""`, `cbf="auto"`. s2Member Pro now officially supports ClickBank PitchPlus Upsell Flows. We support PitchPlus Basic and PitchPlus Advanced too. For further details, please see: `Dashboard -› ClickBank Buttons -› Shortcode Attributes (Explained)`.
* (s2Member/s2Member Pro) **Codestyling Localization** Adding automatic symlink creator for improved compatibility with the CodeStyling Localization plugin. A symlink is created automatically whenever the CodeStyling Localization plugin is installed, and s2Member® Pro is installed as well. The symlink allows the CodeStyling Localization plugin to scan files from the s2Member® Pro directory too; instead of only scanning the s2Member® Framework directory. s2Member and s2Member Pro are now both compatible with the Codestyling Localization plugin (optional).
* (s2Member/s2Member Pro) **Custom Templates w/ s2Stream Shortcode** Adding support for custom templates to be used in conjunction with the `s2Stream` Shortcode. It is now possible to take the default player templates from `/s2member/includes/templates/players/` and put these files inside your own WordPress® theme directory (or inside the `/wp-content/` directory). s2Member will automatically find your template files in these locations. Your custom template files will then be used instead of the built-in defaults.
* (s2Member/s2Member Pro) **Bug Fix (#59)** Resending a password to a User from the Dashboard (while changing the User's email address at the same time); resulted in the email being sent to the previous email address instead of the new one. Fixed in this release.
* (s2Member/s2Member Pro) **Bug Fix (#216)** Removing `-MultiViews` from s2Member's `.htaccess` file in the `/s2member-files/` directory. This improves compatibility with some Apache installations that simply have `AllowOverride All`; because `All` may not include `MultiViews` in some versions of Apache.
* (s2Member/s2Member Pro) **IP Restrictions (#149)** In the Dashboard, we now report if a User is at (or above) max allowable IPs; instead of reporting only if they have actually triggered an internal security breach (which times out quickly in most cases). Fixed in this release.
* (s2Member/s2Member Pro) **Enhancing JW Player Integration (#251)** Adding support for s2Stream Shortcode Attribute `player_aspectratio="12:5"` (as one example). See also [this post](http://www.longtailvideo.com/blog/32697/new-in-jw-player-responsive-design) at Longtail Video.
* (s2Member Pro) **Updating Payflow Integration (#193)** Removing DAILY `D` option for Payflow Recurring Billing. No longer supported by Payflow. However, s2Member will let a `D` value slip by Shortcode Attribute validation in case a site owner has arranged for this to become available against their Payflow account somehow; and to remain backward compatible with previous versions of s2Member Pro. Moving forward, it is NO longer possible to bill customers on a daily basis with PayPal Payments Pro (Payflow Edition). This is a PayPal limitation.
* (s2Member Pro) **Updating Payflow Integration w/ Payflow Bug Workaround (#193)** Updating Payflow integration to support Billing Agreement IDs (aka: `BAID` values) when working together with Payflow Express Checkout APIs (and where a site owner is charging on a recurring basis).

 This improvement also allows s2Member to work around a Payflow bug where `BILLINGTYPE=RecurringPayments` results in error `#7 (Invalid/Expired TOKEN)` whenever Payflow attempts to rebill a customer. s2Member now sets `BILLINGTYPE=RecurringBilling`; and we pass `BAID=B-xxxxxx` instead of passing `TOKEN=xxxxxx`.

 Site owners also need to [contact PayPal MTS](http://www.paypal.com/mts) and ask to have `Reference Transactions` (free) enabled for `Recurring Billing` service. ~ However, you ONLY need `Recurring Billing` service and `Reference Transactions` enabled IF you're operating a PayPal Payments Pro (Payflow Edition) account; and only IF you're charging customers on a recurring basis.

 All of these changes related to Payflow (as detailed in this changelog entry), impact only ONE specific scenario.
 - You have a PayPal Payments Pro (Payflow Edition) account.
 - You charge your customers on a recurring basis.
 - A customer chooses PayPal as their billing method during checkout.

 No other part of s2Member's integration with Payflow was modified in this release.

 For further details, please see [this thread](http://www.s2member.com/forums/topic/paypal-expired-security-token/page/2/) (or monitor the [s2Member KB](http://www.s2member.com/kb/) for new articles on this subject).
* (s2Member Pro) **Payflow API Docs**  Updating `s2m-pro-extras.zip` to include the latest versions of the PayPal Pro (Payflow Edition) APIs. s2Member Pro customers can download this optional ZIP file from their My Account page at s2Member.com. These are extras only, they are NOT part of the s2Member application.
* (s2Member Pro) **Google Checkout Bug Fix (#214)**  Updating s2Member's Google Checkout integration to properly support the `rrt` Shortcode Attribute. Fixed in this release. For further details please see [this thread](http://www.s2member.com/forums/topic/google-recurring-problem/#post-48218).
* (s2Member Pro) **Username Validation (#246)** Now forcing user input (during Pro Form registration) to lowercase on Multisite Networks to prevent unnecessary validation errors during checkout (saving a customer time). Also, s2Member now validates a customer's Username before it is passed through `sanitize_user()` (a core WordPress® function). This prevents confusion for a customer where certain characters were stripped out automatically, causing them problems when attempting to log in for the first time (e.g. the customer thinks their Username is `john~doe`; when it is actually `johndoe` because WordPress (when running a Multisite Network) removes anything that is NOT `a-z0-9 _.-@` (and s2Member removes whitespace as well).

= v130406 =
* (s2Member/s2Member Pro) **Multisite Networks (#145)** Bug fix on Multisite Networks related to User deletions and subsequent logins on child blogs. For further details, please see [this thread](http://www.s2member.com/forums/topic/deleted-users-can-log-in/#post-46738).
* (s2Member Pro) **New Feature (#59)** It is now possible to Edit a User in the Dashboard and check a box to have the User's password reset, and an email message sent automatically to the User/Member with a copy of the Username/Password. This requires s2Member® Pro.
* (s2Member Pro) **API Functions (#158)** New PayPal® Pro API Functions (`s2member_pro_paypal_rbp_for_user`, `s2member_pro_paypal_rbp_times_for_user`). These are for developers. For further details, please see [this article](http://www.s2member.com/codex/stable/s2member/api_functions/package-summary/) in the s2Member® Codex.
* (s2Member Pro) **API Functions (#158)** New PayPal® Pro (PayFlow® Edition) API Functions (`s2member_pro_payflow_rbp_for_user`, `s2member_pro_payflow_rbp_times_for_user`). These are for developers. For further details, please see [this article](http://www.s2member.com/codex/stable/s2member/api_functions/package-summary/) in the s2Member® Codex.

= v130404 =
* (s2Member Pro) **Compatibility (#108)** Updating s2Member® Pro's integration with Authorize.Net to allow charges up to `$99,999.00` (formerly `$10,000.00`).
* (s2Member/s2Member Pro) **User Searches (#91)** Updating `pre_user_query` filter to include `first_name/last_name` (instead of only the `display_name`).
* (s2Member/s2Member Pro) **bbPress Integration (#88)** Updating bbPress Role/Cap filter to exclude itself during bbPress deactivation. This was causing a loss of the `read` Capability inadvertently.
* (s2Member/s2Member Pro) **PayPal® 20% Rule (#100)** Removing mention of the PayPal® 20% rule/limitation in the inline documentation. We confirmed with PayPal this ridiculous rule no longer applies to PayPal Standard Buttons.
* (s2Member/s2Member Pro) **File Downloads (#73)** Fixed bug related to `%2F` in file download URLs leading to a `ccap` directory.
* (s2Member/s2Member Pro) **Debug Logging (#69)** Logging now disabled by default. New log recommendation/warning notices updated throughout all menu pages for s2Member®.
* (s2Member/s2Member Pro) **Debug Logging (#69)** Logging must now be disabled (and the logs directory must be non-existent or empty) for an s2Member® Security Badge to go green. There is only one way to bypass this security check. See KB Article: [s2Member® Security Badges](http://www.s2member.com/kb/security-badges/). Notices are displayed on activation of this version to warn site owners about this change.
* (s2Member/s2Member Pro) **JW Player (#121)** Adding CSS `class` attribute to all JW Player template files used in conjunction with the `s2Stream` shortcode. New CSS class name: `s2member-jwplayer-v6`.
* (s2Member/s2Member Pro) **JW Player (#121)** Adding support for percentage-based width/height values in `player_width="" player_height=""` attributes of an s2Stream shortcode that generates a JW Player. Example: `player_width="100%"`.

= v130221 =
* (s2Member/s2Member Pro) **Bug Fix (#41)** Custom Registration/Profile Fields with an ID that ended with `-[0-9]+` or `_[0-9]+` was failing JavaScript validation due to a parsing issue. Fixed in this release.
* (s2Member/s2Member Pro) **Bug Fix (#43)** A bug first introduced in the last release of s2Member® v130220 was preventing Administrative New User Notifications from being sent by s2Member®. Fixed in this release.

= v130220 =
* (s2Member Pro) **Feature Enhancement/User Exportation (#33)** Adding support for UTF-8 BOM in CSV User export files. Now a configurable option during User/Member Exportation.
* (s2Member/s2Member Pro) **Feature Enhancement/Emails (#21)** Adding additional Replacement Codes for New User Email Notifications (for both the User/Member Notification and also for the Administrator Notification). The following Replacement Codes are now possible: `%%role%%`, `%%label%%`, `%%level%%`, `%%ccaps%%`. Also adding four new Filters for developers. These include: `ws_plugin__s2member_welcome_email_sbj`, `ws_plugin__s2member_welcome_email_msg`, `ws_plugin__s2member_admin_new_user_email_sbj`, `ws_plugin__s2member_admin_new_user_email_msg`. See `Dashboard -› General Options -›  Email Configuration` for further details.
* (s2Member/s2Member Pro) **Feature Enhancement/Emails (#30)** Adding support for PHP tags in the following emails: New User Notification, Administrative New User Notification, Signup Confirmation Email, Specific Post/Page Confirmation Email. See the relevant sections in your Dashboard for further details. Such as: `s2Member® -› General Options -› Email Configuration` and `s2Member® -› PayPal® Options -› Signup Confirmation Email`.
* (s2Member/s2Member Pro) **Feature Enhancement/Shortcodes (#23)** Adding support for the `lang=""` Attribute in PayPal Buttons, PayPal Pro Forms, and in Google Checkout Buttons. This is a bit different from the existing `lc=""` value. The `lc=""` value controls the interface at PayPal, while the `lang=""` value controls the language of the Standard and/or Express Checkout Button itself (with respect to s2Member®). For further details, please see: `Dashboard -› PayPal Buttons (or Pro Forms) -› Shortcode Attributes (Explained)`.
* (s2Member/s2Member Pro) **Bug Fix** Fixing bug in User Access Package. Now checking if `$cap_enabled` also is `TRUE`; just in case another plugin or hack file attempts to disable Custom Capabilities without removing them. Not likely, but we can support this easily with a quick update in this release. Note... this has no impact on s2Member's existing functionality. Custom Capabilities continue to work just as they always have.
* (s2Member/s2Member Pro) **Feature Enhancement/Logging** Adding new logger. Logs to file `reg-handler.log`. Includes all User/Member registrations handled by s2Member® (either directly or indirectly). Only if logging is enabled. For further details, please check your Dashboard here: `s2Member® -› Log Files (Debug)`.
* (s2Member/s2Member Pro) **Feature Enhancement/EOTs (#29)** Adding UI option for EOT Grace Time. For further details, please see: `Dashboard -› PayPal Options -› Automatic EOT Behavior`. Also adding a new Filter for developers: `ws_plugin__s2member_eot_grace_time`.
* (s2Member/s2Member Pro) **Feature Enhancement/EOTs** Adding UI option for EOT Custom Capability Removal. For further details, please see: `Dashboard -› PayPal Options -› Automatic EOT Behavior`. Also adding a new Filter for developers: `ws_plugin__s2member_remove_ccaps_during_eot_events`.
* (s2Member/s2Member Pro) **Feature Enhancement/s2Stream Shortcode (#32)** Adding additional support for JW Player™ Captions, Titles, Descriptions, and Media IDs (i.e. `player_title=""`, `player_description=""`, `player_mediaid=""`, `player_captions=""`). Please check the Shortcode Attributes tab in [this KB article](http://www.s2member.com/kb/jwplayer-s2stream-shortcodes/#using-s2stream-shortcodes) for further details.

= v130214 =
* **(Maintenance Release) Upgrade immediately.**
* (s2Member/s2Member Pro) **Log Viewer (#16)** Additional log file descriptions have been added to the Dashboard, along with some other UI enhancements in this section.
* (s2Member/s2Member Pro) **Bug Fix (#18)** Usernames consisting of all numeric values were not always being redirected to the Login Welcome Page upon logging in, even when s2Member® was configured to do so. Fixed in this release. Discussed in [this thread](http://www.s2member.com/forums/topic/subscribers-not-taken-to-welcome-page/page/2/#post-41663).
* (s2Member Pro) **Coupon Codes (#19)** Adding new Replacement Codes: `%%full_coupon_code%%`, `%%coupon_code%%` and `%%coupon_affiliate_id%%`. These are now available in all API Tracking Codes, in all Custom Return URLs for Pro Forms, and in most API Notifications.
* (s2Member Pro) **Coupon Codes (#19)** Deprecating the `%%affiliate_id%%` Replacement Code for tracking Affiliate Coupon Codes in favor of `%%coupon_affiliate_id%%`.
* (s2Member/s2Member Pro) **Last Login Time** Improving readability of Last Login Time in list of Users/Members.
* (s2Member/s2Member Pro) **Compatibility** Improving support for WordPress® v3.6-alpha with respect to `tabindex` values on `/wp-login.php`.
* (s2Member/s2Member Pro) **Compatibility** Bumping minimum WordPress® requirement from v3.2 up to v3.3. Starting with this release, s2Member® is no longer compatible with the much older WordPress® v3.2.

= v130213 =
* **(Maintenance Release) Upgrade immediately.**
* (s2Member Pro) **Compatibility (#13)** PayPal® Payments Pro, PayPal® Payments Pro (Payflow Edition), and Authorize.Net. s2Member® Pro now supports a recurring interval of Semi-Yearly (e.g. charges occur every six months). This has always been possible by manipulating Shortcode Attribues, but it's now officially supported by Pro Form Generators in your Dashboard — which come with s2Member® Pro.
* (s2Member Pro) **Compatibility (#13)** ClickBank® Recurring Products. ClickBank® has started allowing a Weekly recurring interval and stopped allowing Yearly. s2Member® has been updated in this release to support a Weekly recurring interval with ClickBank®; and to remove the Yearly option in the ClickBank® Button Generator.
* (s2Member Pro) **Compatibility (#13)** PayPal® Payments Pro (Payflow Edition). PayPal® Payments Pro (Payflow Edition) has started allowing a Daily recurring interval. s2Member® has been updated in this release to support a Daily recurring interval with PayPal® Payments Pro (Payflow Edition). Daily recurring intervals remain possible with PayPal® Pro accounts that do not include the additional Payflow API. This change simply adds official support for Daily recurring billing with PayPal® Payments Pro (Payflow Edition).
* (s2Member) **Debugging Assistance** Updating s2Member's PayPal® PDT/Auto-Return handler to better handle scenarios where a site owner is missing a PayPal® PDT Identity Token in their s2Member® configuration, or has incorrectly set the `custom=""` Shortcode Attribute in Payment Buttons generated with s2Member®. Administrative notices are now displayed in the Dashboard when/if this occurs and s2Member® can catch the issue during post-processing of a transaction.
* (s2Member/s2Member Pro) **General Code Cleanup** Removing all `/**/` markers in the s2Member® codebase. These were used in conjunction with PolyStyle® code formatting tools to preserve line breaks in the code. The WebSharks™ development team no longer uses PolyStyle®, making these obsolete now. Removed in this release to improve readability for developers.
* (s2Member/s2Member Pro) **General Code Cleanup** Removing all unnecessary uses of PHP's `eval()` function in s2Member's codebase. These were used to keep repetitive code all in a single line; part of a standard the WebSharks™ development team is now moving away from. Removed in this release to improve readability for developers; and to prevent unnecessary confusion.
* (s2Member/s2Member Pro) **Auto-EOT System** Updated s2Member's Auto-EOT System. s2Member® now leaves an additional note behind after a demotion, which references the Paid Subscr. Gateway and Paid Subscr. ID values before the demotion occurred. This way there is a better reference left behind after an automatic demotion occurs.
* (s2Member/s2Member Pro) **Searching Users** Updating search function in list of Users (i.e. `Dashboard -› Users -› [Search Box]`) to include the Administrative Notes field when searching for Users. This allows references to old Paid Subscr. IDs in the Administrative Notes field to be considered when searching Users/Members.
* (s2Member/s2Member Pro) **Last Login Time** Adding new User Option value (tracked by s2Member®). This option value tracks the last time each User/Member logged into your site. Ex: `get_user_option("s2member_last_login_time")`.
* (s2Member/s2Member Pro) **Last Login Time** Adding new User data column to list of Users in the Dashboard: `Last Login Time`.
* (s2Member/s2Member Pro) **Last Login Time** Adding new API Function: [`s2member_last_login_time()`](http://www.s2member.com/codex/stable/s2member/api_functions/package-summary/).
* (s2Member/s2Member Pro) **ezPHP** Updated all internal documentation references that pointed to Exec-PHP or the PHP Execution plugin as recommendations for developers that wish to integrate PHP tags into Posts/Pages/Widgets. These old references now point to the [ezPHP](http://www.s2member.com/kb/ezphp-plugin/) plugin by s2Member® Lead Developer: Jason Caldwell. s2Member® remains compatible with other PHP plugins, but we recommend [ezPHP](http://www.s2member.com/kb/ezphp-plugin/) for the best compatibility with both s2Member® and WordPress® itself.
* (s2Member/s2Member Pro) **Simple Shortcode Conditionals** Adding a [Simple Shortcode Conditionals](http://www.s2member.com/kb/simple-shortcode-conditionals/) section to `s2Member® -› Restriction Options` in the Dashboard. This way more site owners will be aware of this feature from the start.
* (s2Member/s2Member Pro) **Login/Registration Design** Login/Registration Design with s2Member® is now optional (e.g. this feature can be disabled now — if you prefer). See: `Dashboard -› s2Member® -› General Options -› Login/Registration Design`. This feature is enabled by default on all s2Member® installations.
* (s2Member/s2Member Pro) **Inline Documentation** Adding more links to KB articles throughout the Dashboard area.
* (s2Member/s2Member Pro) **Inline Documentation** Updating all spaced parenthesis like `( something... )` to remove the grammatical errors — by removing the extra spaces inside these brackets.
* (s2Member/s2Member Pro) **Inline Documentation** Removing all references to PriMoThemes and/or primothemes.com within the application itself. PriMoThemes is now s2Member® (as of Jan 2012 — it's been awhile; so time to remove these obviously).
* (s2Member/s2Member Pro) **Inline Documentation** Adding link to "more updates..." in the Dashboard, pointing to the s2Member® KB. Increasing number of recent KB udpates from 3 up to 5. These are visible from any s2Member® page in the Dashboard (top of the right-hand column).
* (s2Member/s2Member Pro) **Inline Documentation** Adding [s2Member® Pro](http://www.s2member.com/pro/) (a recommended upgrade) to the Quick-Start Guide for s2Member® — in the Dashboard.
* (s2Member/s2Member Pro) **Inline Documentation** Adding Troubleshooting section to the Quick-Start Guide for s2Member® — in the Dashboard.
* (s2Member/s2Member Pro) **Inline Documentation** Adding Perfect Theme section to the Quick-Start Guide for s2Member® — in the Dashboard.
* (s2Member/s2Member Pro) **Inline Documentation** Adding video tutorial to the `Dashboard -› s2Member® -› API / Scripting -› Custom Capabilities` section.
* (s2Member/s2Member Pro) **Logging Functionality** Adding an s2Member® Log Viewer to the Dashboard for all site owners; and also for s2Member® Support Reps to use when running diagnostics. See: `Dashboard -› s2Member® -› Log Files (Debug)` for further details.
* (s2Member/s2Member Pro) **Logging Functionality** Logging routines are now enabled by default on all new installations of s2Member®. Existing installations of s2Member® are advised to enable logging, by visiting this section of your Dashboard. See: `s2Member® -› PayPal® Options (or Authorize.Net, ClickBank, etc) -› Account Details -› Logging`.
* (s2Member/s2Member Pro) **Logging Functionality** Additional logging routines that will track all s2Member® HTTP communication within WordPress® is now enabled by default. This new log file will be located inside `/wp-content/plugins/s2member-logs`. It is named: `s2-http-api-debug.log`. See: `Dashboard -› s2Member® -› Log Files (Debug)` for further details.
* (s2Member/s2Member Pro) **Logging Functionality** Additional logging routines that will track *all* HTTP communication within WordPress® are now possible (these are quite extensive). See: `Dashboard -› s2Member® -› Log Files (Debug) -› Logging Configuration` for further details. This more extensive logging is disabled by default; it must be enabled by a site owner. For debugging only — this should NEVER be enabled on a live site.
* (s2Member/s2Member Pro) **Logging Functionality** Adding date/time to all log entries maintained by s2Member®.
* (s2Member/s2Member Pro) **GZIP Conflicts** Adding additional lines of defense against GZIP conflicts during file downloads, with calls to `@apache_setenv("no-gzip", "1")` in other areas — not just during public file downloads (e.g. also during User/Member exporations, log file downloads, etc).
* (s2Member/s2Member Pro) **GZIP Conflicts** Adding an additional line of defense against GZIP conflicts during file downloads, with this line now appearing in the `.htaccess` file snippet added by the s2Member® software application: `RewriteCond %{QUERY_STRING} (^|\?|&)no-gzip\=1`.
* (s2Member/s2Member Pro) **GZIP Conflicts** Adding an additional line of defense against GZIP conflicts during User/Member exporations, log file downloads, and other downloads that come straight from the Dashboard area to site owners via web browsers. s2Member® now sends `Content-Encoding: none` to prevent Apache's `mod_deflate` from interfering with s2Member® under these special scenarios. A `Content-Encoding: none` header value is technically invalid, but it's known to prevent issues with `mod_deflate`. Since a `Content-Encoding: none` header value is technically invalid, s2Member® does NOT implement this during public file downloads; where we need to provide wider support for a long list of devices that may choke on this incorrect value. This is only implemented for site owners in the administrative areas of WordPress; and only for file downloads related to CSV export files and logs.
* (s2Member/s2Member Pro) **Bug Fix** Fixed incorrect `preg_split` limit against `$paypal['item_number']` in IPN handler for `subscr_payment` and `subscr_cancel` transaction types. Doesn't appear to have affected anything negatively, but it was wrong none the less. Fixed in this release.
* (s2Member/s2Member Pro) **Bug Fix** Fixed incorrect handling of a single opt-in checkbox on BuddyPress registration forms, which was not being wrapped with s2Member's BuddyPress container divs at all times. A symptom of this bug was to see a checkbox on your BuddyPress registration that was out of alignment or out of position. Fixed in this release.
* (s2Member/s2Member Pro) **Compatibility** Updated all of s2Member's IPN handlers to accept `$_REQUEST` data for Proxy-related variables like `s2member_paypal_proxy_return_url`. This allows s2Member® itself to use `$_POST` variables for Proxy-related variables; and it further reduces the likelihood of 403 Forbidden errors caused by [paranoid Mod Security configurations](http://www.s2member.com/kb/mod-security-random-503-403-errors/). One issue this should help to correct, is a mysterious case where a `success=""` Shortcode Attribute is not working as you might expect. This can be caused by [paranoid Mod Security configurations](http://www.s2member.com/kb/mod-security-random-503-403-errors/) at places like HostGator®, because a URL is passing through a query string. This release will help to prevent this from becoming a problem, because `success=""` URLs will be passed through `$_POST` variables now in all Pro Form integrations.

= v130207 =
* **(Maintenance Release) Upgrade immediately.**
* (s2Member Pro) **Bug Fix (#2)** Modification Tracking Codes not working properly under s2Member's Authorize.Net integration. Fixed in this release. Discussed in [this thread](http://www.s2member.com/forums/topic/shareasale-integration-not-working/#post-40954).
* (s2Member) **Compatibility (#4)** PayPal® integrated into a site charging in the JPY currency was incorrectly limited to an amount of 10000.00. Fixed in this release. Discussed in [this thread](http://www.s2member.com/forums/topic/default-currency-can-i-change-it-to-yen/#post-40590).
* (s2Member) **Compatibility (#5)** Incorrect `tabindex` values in WordPress® v3.5+. Fixed in this release. Discussed in [this thread](http://www.s2member.com/forums/topic/tabindex-messed-up-on-registration-page/#post-40591).
* (s2Member/s2Member Pro) **Line Breaks (#3)** Some line breaks in both s2Member® and s2Member® Pro were converted to CRLF inadvertently in the previous release. No real harm done, but this was causing some problems for the s2Member® Server Scanner because it uses a checksum against installation files; which was being thrown off balance due to the unexpected line break style. Fixed in this release. A symptom of this bug was to see invalid checksums when running diagnostics with the s2Member® Server Scanner.
* (s2Member/s2Member Pro) **Compatibility (#6)** s2Member® File Downloads (audio/video files) with spaces in a file name were not always being handled properly. Fixed in this release. Discussed in [this thread](http://www.s2member.com/forums/topic/jwplayer-filename-bug/#post-40799).

= v130203 =
* **(New Release) Please read this changelog for important details.**
* (s2Member Pro) **Remote Ops API (`create_user`)** s2Member® Pro's Remote Operations API, for the `create_user` Operation has been updated to support a new specification: `modify_if_login_exists`. For further details, please check your s2Member® Pro Dashboard here: `s2Member® -› API / Scripting -› Remote Operations API`.
* (s2Member Pro) **Remote Ops API (`modify_user`,`delete_user`)** s2Member® Pro's Remote Operations API has been updated to support two additional Operations: `update_user` and `delete_user`. For further details on these new Operations, please check your s2Member® Pro Dashboard here: `s2Member® -› API / Scripting -› Remote Operations API`.
* (s2Member Pro) **Remote Ops API (`init` hook priority)** s2Member® Pro's Remote Operations API has been updated to prevent conflicts when running in concert with BuddyPress v1.6.4+. Hook priority now running at default value of `11`, right after BuddyPress v1.6.4 at hook priority `10`.
* (s2Member/s2Member Pro) **s2Stream Shortcode (#88)** s2Member® now supports JW Player® license keys (for the professional edition) using Shortcode Attribute `player_key=""` (or they can be specified sitewide via JavaScript provided by Longtail Video — optional). See [this discussion](http://www.s2member.com/forums/topic/jwplayer-shortcode-for-poster-not-working/#post-40435). See also: [this KB article](http://www.s2member.com/kb/jwplayer-s2stream-shortcodes/#using-s2stream-shortcodes).
* (s2Member/s2Member Pro) **s2Stream Shortcode (#88)** Bug fix. The s2Stream Shortcode was not working properly (with respect to a specific Shortcode Attribute: `player_image=""`). Fixed in this release. See [this discussion](http://www.s2member.com/forums/topic/jwplayer-shortcode-for-poster-not-working/#post-40128). See also: [this KB article](http://www.s2member.com/kb/jwplayer-s2stream-shortcodes/#using-s2stream-shortcodes).
* (s2Member Pro) **User Exportation (#89)** s2Member® Pro's User Exportation now includes separate data columns for each Custom Registration/Profile Field that you've configured with s2Member®. Also, s2Member® Pro will now include ALL Custom Registration/Profile Fields (even if there is no value associated with certain Fields, for specific Users/Members — e.g. empty column values will now be included by s2Member® Pro). This provides a more consistent/readable CSV export file; a major improvement. Discussed in [this KB article](http://www.s2member.com/kb/importing-updating-users/#custom-registration-profile-fields).
* (s2Member Pro) **User Importation (#89)** s2Member® Pro's User/Member Import format changed in this release (with respect to Custom Registration/Profile Fields only). If you are importing Custom Registration/Profile Fields, please review [this KB article](http://www.s2member.com/kb/importing-updating-users/#custom-registration-profile-fields) before you import new Users/Members or mass update any existing Users/Members. ALSO NOTE: User/Member CSV Export Files generated by previous versions of s2Member® Pro (if they contained any Custom Registration/Profile Fields) will NOT be compatible with this latest release (e.g. you should NOT attempt to re-import those old files in an effort to mass update existing Users/Members). Please generate a new User/Member CSV Export File in the latest release of s2Member® Pro before attempting to edit and/or mass update existing Users/Members with applications like MS Excel or OpenOffice.

= v130123 =
* **(Maintenance Release) Upgrade immediately.**
* (s2Member/s2Member Pro) **s2Stream Shortcode (#78)** s2Member® now supports JW Player® license keys using Shortcode Attribute `player_key=""`. See [this KB article](http://www.s2member.com/kb/jwplayer-s2stream-shortcodes/) please. Also discussed in [this thread](http://www.s2member.com/forums/topic/new-jw-player-6-s2-video-audio-shortcodes/#post-38768).
* (s2Member/s2Member Pro) **s2Stream Shortcode (#79)** s2Member® now supports JW Player® [Advanced Option Blocks](http://www.longtailvideo.com/support/jw-player/28839/embedding-the-player) using Shortcode Attribute `player_option_blocks=""`. Example: `player_option_blocks="sharing:{}"`. See [this KB article](http://www.s2member.com/kb/jwplayer-s2stream-shortcodes/) please. Also discussed in [this thread](http://www.s2member.com/forums/topic/new-jw-player-6-s2-video-audio-shortcodes/#post-38768).
* (s2Member Pro) **User Exportation (#80)** s2Member® Pro User Exportation now occurs with MySQL `ORDER BY ID`, instead of no `ORDER BY` at all. This helps to prevent confusion and buggy behavior. Discussed in [this thread](http://www.s2member.com/forums/topic/user-export-not-working-properly/#post-39123).
* (s2Member Pro) **User Exportation (#81)** s2Member Pro's User Exportation now supports the exporation of up to `1000` User/Member table rows at once. Of course it remains possible to export ALL of your Users/Members with s2Member® Pro. All we've done here is bump the default limit from `250` up to `1000` at a time. In addition, there is a new Filter making it possible to extend this limit further on servers that can handle it. Use Filter: `ws_plugin__s2member_pro_export_users_limit` if you would like to export more Users all at once. See also: `Dashboard -› s2Member® Pro -› User/Member Exportation`.
* (s2Member/s2Member Pro) **KB Articles** Inline documentation updated in some areas, with a few links pointing to helpful/related KB articles.

= v130121 =
* **(Maintenance Release) Upgrade immediately.**
* **New Feature** s2Member® now comes with a new Shortcode `[s2Stream file_download="video.mp4" player="jwplayer-v6" ... /]`, making it MUCH easier for site owners to implemement RTMP streams of audio/video content. For further details, please check your Dashboard under: `s2Member® -› Download Options -› JW Player® v6 and RTMP Protocol Examples`. See also: `s2Member® -› Download Options -› Shortcode Attributes (Explained)`.
* **Compatibility (#75)** Updated s2Member's local file storage engine (for File Downloads via s2Member®), to support special characters in file names. Discussed in [this thread](http://www.s2member.com/forums/topic/problem-with-quotes-in-filename-downloads/#post-38395).
* **Bug Fix (#71)** A bug first introduced in the previous release of v130116, where we added support for byte-range requests to s2Member's File Download functionality, was causing multiple byte-range requests (processed by s2Member) to count against each User/Member as multiple File Downloads. Fixed in this release.
* **Compatibility** Updated s2Member's integration with Amazon® S3 to extend the default 30 second connection timeout (which was too conservative for many integrations) up to 24 hours by default, making it match the same as s2Member's Amazon® CloudFront connection timeout. For further details, please check your Dashboard under: `s2Member® -› Download Options -› Amazon® S3/CDN Storage -› Dev Note w/Technical Details`. It is possible to modify this connection timeout through a Filter discussed there.

= v130116 =
* **(Maintenance Release) Upgrade immediately.**
* **Compatibility (#39)** Updated codes samples for JW Player®, to include the `mp4:` prefix when implementing RTMP streams against MP4 video files. Discussed in [this thread](http://www.s2member.com/forums/topic/cloudfront-subfolder-streaming-error/#post-35750).
* **Compatibility (#51)** Updated Payflow® API to support recurring billing every six months. Discussed in [this thread](http://www.s2member.com/forums/topic/payflow-error-6-month-recurring-membership/#post-36053).
* **Bug Fix (#69)** Updated multisite user imporation routine, to support a specific scenario not covered under WordPress v3.5. Discussed in [this thread](http://www.s2member.com/forums/topic/users-on-multisite/).
* **Feature Improvement (#71)** s2Member® has been updated to support byte-range requests with it's default local file storage engine, served from the `/s2member-files/` directory. s2Member® has always supported byte-range requests when integrated with Amazon® CloudFront. Now it supports byte-range requests in it's default local storage engine too. This will improve compatibility with mobile devices, iTunes™  and other devices that use byte-range requests. Discussed in [this thread](http://www.s2member.com/forums/topic/any-way-to-set-accept-ranges-for-downloads/#post-15871).

= v121213 =
* **(Maintenance Release) Upgrade immediately.**
* **Updated for compatibility with WordPress® v3.5. Backward compatibility remains for previous versions of WordPress®, as far back as WordPress® v3.2.**
* (s2Member Pro) **Bug Fix**. An issue first introduced in s2Member® Pro v120517 where we fixed problems with the `maxlength` attribute in Authorize.Net Pro Forms, left a remaining problem. The State/Province field in the Billing Address section of a Pro Form, since s2Member® Pro v120517, has only accepted 2 characters when it should have been capable of accepting up to 40 characters. Fixed in this release.
* (s2Member / s2Member Pro) **Compatibility**. s2Member's Multsite Network patches now support `/wp-login.php` in WordPress® v3.5. Discussed in [this thread](http://www.s2member.com/forums/topic/fyi-wpmu-3-5-wp-login-php-file-not-verified/#post-34457).
* (s2Member / s2Member Pro) **Compatibility**. s2Member's login customizations for `/wp-login.php` have been tweaked to support WordPress® v3.5.
* (s2Member / s2Member Pro) **Checksums**. Each copy of s2Member® and s2Member® Pro now include a `checksum.txt` file in their root plugin directory. This file is used by server-scanning tools provided by WebSharks, Inc. This file simply serves to identify the state of the file structure upon each official release of the software.
* (s2Member Pro) **Bug Fix**. Free Registration Pro Forms submitted without having payment gateway API credentials configured within s2Member® resulted in an on-site error message when there should NOT be one (because a site owner is dealing with Free Registration only in this scenario). Fixed in this release.

= v121204 =
* **(Maintenance Release) Upgrade immediately.**
* (s2Member / s2Member Pro) **Bug Fix**. An issue with long billing agreement descriptions under PayPal® Pro (Payflow® Edition) accounts, when coupon codes were being used by customers, was addressed in this release. Symptoms of this bug were errors in s2Member® log files from the Payflow® API, with error code: `11581-Profile description is invalid`. Caused by undocumented length requirements for the billing agreement description under the Payflow® API. Fixed in this release. Discussed in [this thread](http://www.s2member.com/forums/topic/error-generic-processor-error-11581/page/2/#post-33477).
* (s2Member / s2Member Pro) **Compatibility**. Updated JW Player code samples for compatibility with JW Player v6. Discussed in [this thread](http://www.s2member.com/forums/topic/jw-player-rtmp-streaming-mp4-amazon-s3/page/2/#post-32074).

= v121201 =
* **(Maintenance Release) Upgrade immediately.**
* (s2Member / s2Member Pro) **Bug Fix**. Support Rep Cristián Lávaque found a bug in the behavior of s2Member’s Alternative View Restrictions, associated with Category listings in custom menu widgets. Fixed in this release. Discussed in [this thread](http://www.s2member.com/forums/topic/welcome-page-title-shows-but-no-content/page/2/#post-29802).
* (s2Member Pro) **Feature Enhancement**. s2Member Pro Forms integrated ONLY with PayPal Express Checkout (`accept="paypal" accept_via_paypal="paypal"`), will no longer display a Billing Method section on the Pro Form, as it's not necessary (there's only one possible option in this case, and it's already depicted by the PayPal button at the bottom of the Pro Form). Many site owners had implemented CSS hacks to hide this section of a Pro Form configured this way, based on [this FAQ article](http://www.s2member.com/faqs/#s2-faqs-paypal-pro-not-required). This hack is no longer necessary - starting with this release.
* (s2Member Pro) **Bug Fix**. s2Member Pro Forms integrated with Payflow Recurring Billing via PayPal Express Checkout were failing against some accounts with an erroneous error #10422 related to an invalid funding source. With some help from other site owners and the assistance of PayPal technical support, the underlying issue has been fixed in this release. Discussed in [this thread](http://www.s2member.com/forums/topic/paypal-error-36-transaction-failed/page/2/#post-31490).
* (s2Member Pro) **Compatiblity**. ccBill Buttons can now be generated for amounts exceeding $100.00, so long as prior ccBill approval is obtained from ccBill merchant support. Discussed in [this thread](http://www.s2member.com/forums/topic/cc-bill-button-increase-dollar-amount/#post-31636).
* (s2Member/s2Member Pro) **Compatiblity**. Updated to support Dynamic Roles introduced in bbPress® v2.2. Discussed in [this thread](http://www.s2member.com/forums/topic/dont-upgrade-to-bbpress-2-2/#post-32523).
* (s2Member Pro) **Authorize.Net**. True montly billing instead of every 30 days. Fixed in this release. Discussed in [this thread](http://www.s2member.com/forums/topic/1-month-recurring-billing-instead-of-30-days/#post-30420).

= v121023 =
* **(Maintenance Release) Upgrade immediately.**
* (s2Member / s2Member Pro) **Bug Fix**. A bug related to s2Member's `is_site_root()` method, when fancy permalinks are NOT in use; has been corrected for compatibility with the latest version of WordPress. Please see [this thread](http://www.s2member.com/forums/topic/new-custom-field-default-not-on-old-users/#post-28792) for futher details.
* (s2Member Pro) **Import/Export Bug Fix**. An issue related to RFC guidelines for escape sequences in CSV files has been addressed in this release. Please see [this thread](http://www.s2member.com/forums/topic/new-custom-field-default-not-on-old-users/#post-28792) for futher details.
* (s2Member Pro) **ccBill® DataLink Integration**. DataLink integration with ccBill® was updated for improved compatibility across multiple ccBill® sub-accounts.
* (s2Member Pro) **ccBill® DataLink Integration**. DataLink integration with ccBill® was updated for improved compatibility w/ ccBill® servers running on MST timezone.
* (s2Member/s2Member Pro) **API Function**. A new API Function was added. See: `s2member_login_ips_for($username)`. Please check the [s2Member® Codex](http://www.s2member.com/codex/stable/s2member/api_functions/package-summary/) for documentation. [This thread](http://www.s2member.com/forums/topic/s2member-restriction-options-unique-ip/#post-20562) may also be of some assistance.
* (s2Member/s2Member Pro) **404 Error (Bug Fix)**. A former dependency on `l10n.js` from the WordPress® core is no longer necessary. This old dependency has been removed to prevent 404 errors in the latest versions of WordPress®. Please check [this thread](http://www.s2member.com/forums/topic/wordpress-i10n-file-404-from-s2member/#post-20567) for further details.
* (s2Member Pro) **reCAPTCHA® Bug Fix**. A bug sometimes causing failed reCAPTCHA® responses after PayPal® Express Checkout has been corrected in this release. This occurred during certain scenarios, whenever reCAPTCHA® was enabled for checkout forms, and PayPal Express Checkout was selected as the payment method of choice.
* (s2Member Pro) **ccBill® DataLink Integration**. DataLink integration with ccBill® was modified to prevent dates in the future from being requested from the DataLink API. ccBill® was responding to some DataLink requests with a failed authentication, which were caused by dates/times in the future; according to MST on the ccBill® side of things.

= v120703 =
* **(Maintenance Release) Upgrade immediately.**
* (s2Member/s2Member Pro) **Payflow® Express Checkout**. An issue related to Express Checkout (when using the Payflow® API), has been corrected in this release. A bug in previous releases, was causing error messages under certain circumstances that read `Field format error: Invalid PayerID`.
* (s2Member/s2Member Pro) **WordPress® v3.4**. Standards compliance. Routine maintenance. Re-confirmed compatibility with WordPress® v3.4.

= v120622 =
* **(Maintenance Release) Upgrade immediately.**
* (s2Member/s2Member Pro) **WordPress® v3.4**. Confirmed compatibility with WordPress® v3.4.
* (s2Member/s2Member Pro) **Currency Conversion**. This release updates s2Member's currency conversion API, which is powered by Google®. Please see [this thread](http://www.s2member.com/forums/topic/paypal-agreecontinue-sends-to-memb-options/#post-16972) for further details.
* (s2Member/s2Member Pro) **Payflow® Bug Fix**. This release addresses a bug that existed in s2Member's Payflow® integration with Express Checkout. Resolved in this release. Please see [this thread](http://www.s2member.com/forums/topic/cant-do-recurring-billing-via-paypal-payflow/#post-16966) for further details.
* (s2Member/s2Member Pro) **Character Encoding**. This release fixes a big in s2Member's character encoding conversion, for IPN responses received from PayPal®. This releases also fixes an issue specifically with the pound sterling symbol `£`, which was causing some transient IPN data to become corrupted, under the right scenario.

= v120608 =
* **(Maintenance Release) Upgrade immediately.**
* (s2Member/s2Member Pro) **WordPress® v3.4**. Updated for compatibility with the coming release of [WordPress® v3.4](http://wordpress.org/news/2012/06/wordpress-3-4-release-candidate-2/). Additional details available [here](http://wordpress.org/news/2012/06/wordpress-3-4-release-candidate-2/).
* (s2Member/s2Member Pro) **Payflow® Bug Fix**. This release addresses two bugs that existed in s2Member's Payflow® integration. Resolved in this release. Please see [this thread](http://www.s2member.com/forums/topic/transactions-not-going-through/#post-15896) for further details.
* (s2Member Pro) **PayPal® Express Checkout**. This release enables "PayPal Account Optional" for PayPal® Express Checkout, via s2Member Pro Forms. In other words, this release makes the PayPal® Express Checkout option through Pro Forms, behave more like a standard PayPal® Button; where a customer is not always required to have a PayPal® account during checkout. This functionality is enabled automatically, there's nothing you need to change in your s2Member® integration. However, we do suggest that you turn "PayPal Account Optional" (on) inside your PayPal® account. Please see [this thread](http://www.s2member.com/forums/topic/paypal-express-for-paypal-pro-user/#post-15892) for further details.
* (s2Member) **Documentation**. Code samples for Content Dripping have been updated in the Dashboard, in order to correct a date comparison snippet, which was WRONG. Please check your Dashboard under: `s2Member® -› API Scripting -› Content Dripping -› Example #2`, for the updated code sample.

= v120601 =
* **(Maintenance Release) Upgrade immediately.**
* (s2Member Pro) **ClickBank® Button Shortcodes**. This release works around a bug that has been discovered on the ClickBank® side of things, when a `+` character appears in the `desc=""` attribute of your ClickBank® Button Shortcode. Resolved in this release. Please see [this thread](http://www.s2member.com/forums/topic/clickbank-output-url/#post-15166) for further details.
* (s2Member Pro) **Payflow® Daily Recurrence (Limitation)**. PayPal® Pro accounts with the Payflow® Edition API, are NOT capable of charging on a `daily` recurring basis. Previous releases of s2Member® Pro mistakenly documented this as being possible. Resolved in this release. PayPal® Pro accounts operating with the Payflow® Edition (and integrated with s2Member®), are only capable of charging recurring fees on the following schedules: `weekly, bi-weekly, monthly, quarterly, or yearly`. This is in large part, a limitation in the Payflow® API, which we hope will be resolved by PayPal® in a future version. Please feel free to contact PayPal® if you'd like to vote for this feature! This limitation does NOT affect existing PayPal® Pro accounts operating exclusively under the PayPal® Pro API (e.g. without Payflow®).
* (s2Member Pro) **New ccBill® Shortcodes**. s2Member® Pro now includes two new Shortcode Attributes for ccBill® payment button integrations. These include: `sub_account` and `form`. Making it possible to integrate a single installation of s2Member® Pro with multiple ccBill® sub-accounts, and/or multiple ccBill® forms (as they exist in your ccBill® account). For further details, please read the Shortcode documentation, found in your Dashboard under: `s2Member® -› ccBill® Buttons -› Shortcode Attributes (Explained)`.
* (s2Member/s2Member Pro) **Bug Fix**. A bug related to inaccurate role assignment, under certain scenarios (for administrative accounts). Resolved in this release. Please see [this thread](http://www.s2member.com/forums/topic/inaccurate-role-assignment-in-s2member-pro/#post-14122) for further details.

= v120517 =
* **(Maintenance Release) Upgrade immediately.**
* (s2Member Pro) **PayPal® Pro Forms**. This release removes all limitations on the maximum length of an initial trial/period. It is now possible to offer any number of days/weeks/months/years for free, or at a different initial rate.
* (s2Member Pro) **Authorize.Net® Bug Fix**. Max length (i.e. `maxlength=""`) adjusted in Pro Forms integrating with Authorize.Net. Transactions were sometimes failing due to character length restrictions imposed by the Authorize.Net® API. Fixed in this release. Please see [this thread](http://www.s2member.com/forums/topic/customer-charged-but-not-given-access/#post-13454) for further details.
* (s2Member/s2Member Pro) **JW Player® Code Samples**. Updated code samples for JW Player®, to reduce the possibility of a namespace conflict in configuration variables. For further details, please check [this thread](http://www.s2member.com/forums/topic/jw-player-new-code-conflict/#post-13819).

= v120514 =
* **(Maintenance Release) Upgrade immediately.**
* (s2Member Pro) **Payflow® API Support**. s2Member® Pro now supports PayPal® Pro accounts operating with the Payflow® edition. It is now possible to process recurring payments through newer PayPal® Pro accounts (e.g. those which may use the new Payflow® API for recurring billing). Please note, this feature should ONLY be used by site owners with a brand new PayPal® Pro account, which has Recurring Billing service enabled under the Payflow® edition. Site owners with existing PayPal® Pro accounts are NOT impacted by this feature, nor should they attempt to use this feature. For further details, please check your Dashboard under: `s2Member® -› PayPal® Options -› Payflow® Account Details`.
* (s2Member Pro) **Authorize.Net® Shortcode Attribute**. A new Shortcode Attribute `rrt=""`, is available for Authorize.Net® Pro Forms. For further details, please check your Dashboard under: `s2Member® -› Authorize.Net® Forms -› Shortcode Attributes (Explained)`.
* (s2Member Pro) **Authorize.Net® Bug Fix**. Transactions were sometimes failing due to character length restrictions imposed by the Authorize.Net® API. Fixed in this release. Please see [this thread](http://www.s2member.com/forums/topic/customer-charged-but-not-given-access/#post-13454) for further details.
* (s2Member/s2Member Pro) **Remote Request Hook**. A few developers requested this. A new WordPress® Hook was added to s2Member's remote connection routine. Search s2Member's source code for Hook name: `ws_plugin__s2member_before_wp_remote_request`.
* (s2Member Framework) **PayPal® Buttons**. Restrictions limiting the number of days/weeks/months/years allowed in recurring periods for a PayPal® Button have been increased. Max days was increased from `7` to `90`, weeks remains at `52` max, months is up from `12` to `24` max; years increased from `1`, up to `5` years max. This change impacts PayPal® Standard Buttons only, and does NOT affect Pro Forms, which operate on restrictions imposed by the PayPal® Pro API (and these are slightly different).
* (s2Member/s2Member Pro) **JW Player® Code Samples**. Updated code samples for JW Player®. For further details, please check your Dashboard under: `s2Member® -› Download Options -› JW Player® Code Samples`.

= v120309 =
* (s2Member Pro) **ccBill® Cancellations**. It's now possible for s2Member to pull ccBill® "cancellation" events, from the ccBill® DataLink Service Suite. For further details and configuration options, please check this section of your Dashboard: `s2Member -› ccBill Options -› DataLink Integration`.
* (s2Member/s2Member Pro) **Bug fix**. Some PHP installations running in safe mode were experiencing `400 Bad Request` errors whenever s2Member's Amazon® CloudFront configuration routines for file downloads were processed. Fixed in this release.

= v120308 =
* (s2Member/s2Member Pro) **Custom Registration/Profile Fields**. Now possible to create a Custom Field that's always hidden, during both registration and any future Profile edits (e.g. for administrative purposes only).
* (s2Member/s2Member Pro) **Compatibility**. Minor updates for compatibility with the coming release of WordPress® v3.4.
* (s2Member Pro) **Bug fix**. Broken link in UI leading to: `s2m-pro-extras.zip`. Corrected in this release.

= v120301 =
* (s2Member Pro) **ClickBank**. Bug fix in call to `http_build_query()` related to `arg_separator`. This affected installations of PHP with something other than a default INI value for argument separators. Fixed in this release for better compatibility.
* (s2Member/s2Member Pro) **File downloads**. Bug fix in s2Member's handling of the `"file_storage"` parameter to API Function `s2member_file_download_url()`. Fixed in this release.

= v120219 =
* (s2Member) **File downloads**. s2Member's `.htaccess` rules updated to also support older versions of the Apache 1.x series. However, we still recommend that you run s2Member® with Apache 2.0 or higher. Or, with another modern web server that's Apache-compatible, such as [LiteSpeed](http://litespeedtech.com/).
* (s2Member) **Link updates**. Some of the documentation built into the s2Member® plugin contained links which were outdated after our recent move to the new [s2Member.com](http://www.s2member.com/). These links have now been updated within the plugin.
* (s2Member) **New video tutorial**. [s2Member® Intros, Framework and Pro](http://www.s2member.com/videos/85E41C40550808C2/)
* (s2Member) **New video tutorial**. [s2Member® File Downloads, Complete Series / From Basics On Up](http://www.s2member.com/videos/7547A199A4385310/)
* (s2Member) **New video tutorial**. [s2Member® File Downloads, Amazon S3/CloudFront/JW Player](http://www.s2member.com/videos/BD496E5F2CCAB12A/)
* (s2Member) **New video tutorial**. [s2Member® File Downloads, Remote Auth/Podcasting](http://www.s2member.com/videos/71F49478D6983A9C/)
* (s2Member) **New video tutorial**. [s2Member® File Downloads, GZIP Conflicts?](http://www.s2member.com/videos/038A4033A8D2A2EB/)
* (s2Member) **New video tutorial**. [s2Member®, Using The PayPal Sandbox](http://www.s2member.com/videos/A7AEF89D281A75A0/)

= v120213 =
* (s2Member) **File downloads**. GZIP conflicts can now been resolved for file downloads. s2Member now introduces an `.htaccess` rewrite rule, which is automatically installed during activation and/or a future upgrade of the s2Member® Framework plugin. These rewrite rules are installed into your root `.htaccess` file for WordPress (if it's writable). If your `.htaccess` file is not writable, you will get a warning in your `s2Member -› Download Options` panel.

 For further details, please check your Dashboard under: `s2Member -› Download Options -› Preventing GZIP Conflicts`. Or see [this KB article](http://www.s2member.com/kb/resolving-problems-with-file-downloads/).
* (s2Member) **Optimization**. Slow query w/ memory issues during activation on a Multisite Network with over 30K Users/Members. Fixed in this release.
* (s2Member) **Compatibility**. Litespeed web server compatibility added to all areas of s2Member. A few `mod_rewrite` tweaks were needed. Fixed in this release.
* (s2Member) **Bug fix**. Automatic list transitioning issue, which was affected by Payment Button integrations where s2Member's Auto-Return handler was getting in the way. Fixed in this release.
* (s2Member/s2Member Pro) **Bug fix.** Due to an issue that once existed in releases of s2Member prior to v110927, s2Member's Auto EOT System was sometimes failing to succeed in cases where no IPN Signup Vars could be found (but only for Members who originally joined under a release of s2Member prior to v110927). s2Member v120213 resolves this elusive bug with a built-in workaround (i.e. a built-in default value in the code), specifically for this scenario.
* (s2Member Pro) **Bug fix.** If Membership Levels were changed with s2Member Pro via `/wp-config.php` using `define("MEMBERSHIP_LEVELS", 1)` or similar; s2Member was failing to cleanup all unused Capabilities in the `wp_user_roles` array, which may have been associated with previously used Membership Levels. This had no harmful side effects, but it was a bug nevertheless. Upgrading to the latest installation of s2Member automatically cleans up any Capabilities this bug left behind. New installations of s2Member will not be affected by this at all.
* (s2Member/s2Member Pro) **Routine maintenance.** Overall review of the codebase, security review, general code cleanup and maintenance.
* (s2Member) **New website.** A new website has been launched for s2Member. Please see: [s2Member.com](http://www.s2member.com/)
* **Coming soon.** Work continues on the next generation of s2Member®.

= v111220 - 1.0 =
* ... trimmed away at v111220.
* Initial release: v1.0.