= v230504 =

- (Pro) **Fix**: Stripe subscriptions weren't using customer cards updated with the Billing Update pro-form. The subscription saved the first card, instead of defaulting to the card in the customer's profile. This release fixes that. The card is not added to a new subscription anymore, only to the customer's profile, and updating his profile's card with the Billing Update pro-form, will also update the subscription so it uses it. Thanks to Jim Antonucci for his help with this.

- (Pro) **Enhancement**: The Stripe Billing Update pro-form now includes a field for the cardholder's name (i.e. Name On Card). Adding the name to the card will improve successful subscription charges. Thanks to Andy Johnsen for the idea.

= v230425 =

- (Framework) **Fix**: Fixed domain name format validation for custom profile fields.

- (Framework) **Fix**: Fixes to markdown parser for PHP8 compatibility.

- (Framework) **Fix**: Fixed HTML near AWeber's API key field.

= v230413 =

- (Pro) **Bug Fix**: An error could happen on PHP8 during Pro installation in a multisite network. Fixed in this release.

- (Framework) **Bug Fix**: An error could happen on PHP8 when saving an edited user profile. Fixed in this release.

- (Framework) **UI Enhancement**: In the List Servers admin page, removed mentions of the AWeber email parser, which isn't available any more. 

= v221103 =

- (Framework) **Bug Fix**: Removed latest changes to gateway notification and return handlers, that were causing difficulties with member access in some scenarios. 

= v221031 =

- (Framework) **Bug Fix**: Fix PayPal IPNs being ignored because a bug in the last release. After updating to this release, you may want to [review your latest IPNs](https://www.paypal.com/merchantnotification/ipn/history) since updating to v221028, and re-send them from PayPal. See [thread 10208](https://f.wpsharks.com/t/10208)

= v221028 =

- (Framework) **Fix**: Initialized some array keys to prevent PHP warnings in PayPal notify and return files. Thanks Greg M. for your help.

- (Framework) **UI**: Widened the Logs viewer. Thanks Sim. See [thread 10064](https://f.wpsharks.com/t/10064)

- (Framework) **UI**: Framework auto-update is now allowed when Pro add-on installed.

- (Pro) **UI**: The Pro updater now shows when a newer version available, not just when required.

= v220925 =

- (Pro) **UI Enhancement**: In ClickBank Options admin page, added note about keeping IPN encryption disabled.

- (Pro) **Enhancement**: Removed ClickBank's name from the notify, return, and success URLs, replaced with just `cb`. Kudos to Eduardo for telling me about this. See [thread 9910](https://f.wpsharks.com/t/9910)

- (Pro) **Enhancement**: Added a PayPal payment request ID to help prevent random/rare PayPal duplicate charges. Kudos to Nathan for his help. See [thread 7999](https://f.wpsharks.com/t/7999/27)

- (Framework) **UI Enhancement**: Admin page panels widened for larger displays.

- (Framework) **UI Enhancement**: Simplified Getting Started and Getting Help admin pages.

- (Framework) **UI Enhancement**: In PayPal Options admin page, updated paths and links to PayPal settings.

- (Framework) **Bug Fix**: Removed the Security Badge's link to the old Flash powered page on s2Member's site.

- (Pro) **UI Enhancement**: Small improvements to the Pro upgrader.

= v220809 =

- (Framework) **Enhancement**: New `current_user_days_to_eot_less_than` function for conditionals. Useful when you want to show a message to a user on his last days of access before the EOT time in his profile. E.g. `[s2If current_user_days_to_eot_less_than(31)]Please renew your membership[/s2If]`. Kudos to Felix for his help, see [post 6783](https://f.wpsharks.com/t/6783).

= v220421 =

- (Framework & Pro) **Enhancement**: Improved PHP compatibility to 8.1.

- (Framework) **UI Fix**: `More Updates` link fixed.

= v220318 =

- (Framework) **Enhancement**: New `current_user_gateway_is` function for conditionals. Useful for sites using more than one gateway. E.g. `[s2If current_user_gateway_is(stripe)] ...`

- (Pro) **UI Fix**: Removed "Image Branding" setting from s2's Stripe options, not used in current integration.

= v210526 =

- (s2Member Framework & Pro) **UI Enhancement**: Started improving the admin interface. Lightened up the colors, and changed the layout a little bit. 

- (s2Member Framework) **UI Enhancement**: Added title tag to buttons to manage custom profile fields in admin, to improve use with screen-reader. [Thread 8836](https://f.wpsharks.com/t/8836/12)

- (s2Member Pro) **UI Fix**: Fixed typo in pro-form `rrt` attribute description. [Issue 1204](https://github.com/wpsharks/s2member/issues/1204)

- (s2Member Framework) **Bug Fix**: Registration Date sometimes wasn't formatted correctly with the s2Get shortcode. [Thread 8730](https://f.wpsharks.com/t/8730)

= v210208 =

- (s2Member Pro) **Enhancement**: In the Stripe integration, cancelling a subscription in the last minutes of a period, may cause the invoice for the new period to remain there and still be charged later. Now s2Member Pro attempts to find a draft or open invoice for the subscription being cancelled, and void it. Thanks Alan for reporting it. See [post 8386](https://f.wpsharks.com/t/8098).

- (s2Member Pro) **UI Enhancement**: Improved Stripe pro-form error message when trying to create a subscription with a bad card. Thanks everyone that reported it. See [issue #1184](https://github.com/wpsharks/s2member/issues/1184), [post 6043](https://f.wpsharks.com/t/6043), and [post 8386](https://f.wpsharks.com/t/8386).

- (s2Member Pro) **Enhancement**: Added the new action hooks `ws_plugin__s2member_pro_before_stripe_notify_event_switch` and `ws_plugin__s2member_pro_after_stripe_notify_event_switch` in the Stripe endpoint to allow customizations, e.g. new event handlers.

- (s2Member Pro) **UI Fix**: Removed some leftover mentions of Bitcoin support in Stripe's options.

- (s2Member Pro) **UI Fix**: Removed a couple of deprecated shortcode attributes from the documentation for Stripe's pro-form, leftovers from the old integration. Kudos to Debbie for bringing my attention to them. See [post 8053](https://f.wpsharks.com/t/8053).

- (s2Member Framework) **UI Fix**: Fixed some broken links and video players in the admin pages.

- (s2Member Framework) **Bug Fix**: Resolved a warning given when changing users role in bulk from the WP Admin > Users page.

- (s2Member Server Scanner) **Bug Fix**: Updated the [Server Scanner](https://s2member.com/kb-article/server-scanner/) to remove some outdated warnings.

= v201225 =

- (s2Member Framework) **Bug Fix**: View Password icon WP's login page was not displaying correctly. Kudos to Beee4life for reporting it. See [issue #1187](https://github.com/wpsharks/s2member/issues/1187)

- (s2Member Framework and Pro) **Enhancement**: Refactored PHP's deprecated _create_function_ with anonymous functions. Kudos to Berry for reporting it, see [post 6069](https://f.wpsharks.com/t/6069) 

- (s2Member Framework) **Bug Fix**: Added a check for empty return variable before trying to use it in paypal-utilities.inc.php.

- (s2Member Framework) **Bug Fix**: Added checks for undefined indexes before trying to use them in paypal-return-in-subscr-or-wa-w-level.inc.php.

- (s2Member Framework) **Bug Fix:** Added a check for undefined index before using it to define a couple of s2 constants. Kudos to Berry for reporting it, see [post 8181](https://f.wpsharks.com/t/8181/) 

- (s2Member Pro) **Bug Fix**: s2's payment notification when creating a Stripe subscription, was being sent twice. Added a check to ignore the webhook for the subscription's on-session first payment; s2's webhook endpoint is for off-session events. 

- (s2Member Framework) **Enhancement**: Added a new hook for the payment notification on subscription creation or buy now payments.

- (s2Member Pro) **Bug Fix**: Stripe paid trials were accumulating on failed payment attempts, causing a larger charge when it finally succeeded. Kudos to Alan for his help through the many attempts to fix this one, see [post 7002](https://f.wpsharks.com/t/7002).

- (s2Member Pro) **Enhancement**: Stripe duplicate payments were happening randomly to a few site owners, apparently from bad communication between their server and Stripe's. Added idempotency to prevent duplicates. Kudos to Alan and everyone in the forum that reported and gave details on this behavior, see [post 7002](https://f.wpsharks.com/t/7002)

= v200301 =

- (s2Member Pro) **Enhancement:** Added "Powered by Stripe" to Stripe pro-form's payment card field. Kudos to Josh, see [post 6716](https://f.wpsharks.com/t/6716).

- (s2Member Pro) **Bug Fix:** Stripe subscription cancellations were not happening when they should. This release updates the API integration for it and fixes that behavior. Kudos to Matt for reporting it, see [post 6909](https://f.wpsharks.com/t/6909).

- (s2Member Pro) **Bug Fix:** Updating the card with Stripe's pro-form sometimes gave an incorrect "missing billing method" error. Kudos to Corey, see [post 7058](https://f.wpsharks.com/t/7058).

- (s2Member Pro) **Small fix:** Removed Bitcoin mention next to Stripe in Gateways list. Missed it in [v191022](https://s2member.com/s2member-v191022-now-available/).

= v200221 =

- (s2Member Pro) **Bug Fix:** In some rare cases, another plugin loaded Stripe's class before s2Member, so when s2 tried loading it there'd be an error. This release fixes the check for the class before trying to load it. See [issue #1170](https://github.com/wpsharks/s2member/issues/1170)

  **Note:** s2Member won't have control over what version of the Stripe SDK was loaded by the other plugin. You'll need to get that other plugin to have an up-to-date version. If you don't have another plugin loading Stripe, this is not relevant to you.

- (s2Member Pro) **Bug Fix:** When using a 100% off coupon, requiring no payment, the Stripe pro-form was still loading the card field and requiring it, preventing the free signup. That's fixed in this release. See [issue #1171](https://github.com/wpsharks/s2member/issues/1171)

- (s2Member Pro) **Bug Fix:** The Stripe pro-form, when given an invalid card, didn't give a clear error message for it, and instead just "invalid parameter". Now it shows the correct card error, making it possible for the customer to try a different card to complete the payment.

- (s2Member Pro) **Feature Update:** The Indian Rupee was added to the list of currency symbols.

- (s2Member Pro) **Feature Enhancement:** The s2Member Pro add-on, not being a regular plugin was not uploadable via the WP plugin manager. This made it necessary to FTP, which is complicated for some site owners. In this release I made it possible for the plugin manager to upload or remove the Pro add-on.

  **Note:** It still is not a regular plugin. The activation link or status in the plugins manager is irrelevant, but I couldn't find how to remove it. s2Member Pro activates automatically when its version matches the Framework's, and it'll be mentioned next to the Framework's version in the plugins manager.

= v191022 =

- (s2Member Pro) **Feature Enhancement:** The Stripe pro-forms can now handle 3D Secure 2 for [Strong Customer Authentication](https://stripe.com/guides/strong-customer-authentication), as required by the new European regulation that came into effect recently. Props to those in the beta testing group, especially Brice and Felix. See [thread 5585](https://f.wpsharks.com/t/5585/).

- (s2Member Pro) **Feature Enhancement:** The Stripe pro-form now has the card field inline, instead of opening a modal to enter it. Before it required clicking the link to open the modal, enter the card details, submit that, and then submit the pro-form. Now you enter the card details as part of the pro-form. See [issue #588](https://github.com/wpsharks/s2member/issues/588).

- (s2Member Pro) **Stripe Integration Updates:** Upgraded the Stripe PHP SDK from v1.18 to v7.4.0, and the API from 2015-07-13 to 2019-10-08. Upgraded the integration from the Charges API to the latest Payment Intents API. Upgraded the card input from the old Stripe Checkout modal, to the new Stripe.js and Elements. 

- (s2Member Pro) **Optimization:** Stripe's JavaScript now only gets included if the page has a Stripe pro-form.

- (s2Member Pro) **Removed Stripe Bitcoin**: Stripe [dropped Bitcoin](https://stripe.com/blog/ending-bitcoin-support) last year, it's not available anymore. This update removes the Bitcoin options and mentions from the s2 admin pages.

- (s2Member Pro) **Bug Fix:** Subscriptions without at trial were showing a "trialing" status in Stripe for the first period. This behavior has now been solved. It will only say trialing when you set a trial period (free or paid) in your Stripe pro-form shortcode. See [issue #1052](https://github.com/wpsharks/s2member/issues/1052).

- (s2Member Pro) **Bug Fix:** The Stripe pro-form installments via the `rrt` shortcode attribute were charging an extra payment before ending the subscription. There was an error in the time calculation for this. This is solved in this release. Props to Brice. See [thread 5817](https://f.wpsharks.com/t/5817/).

- (s2Member Pro) **Bug Fix:** Some payments through the Stripe pro-form were creating a new Stripe customer when the user was already a customer. The Stripe customer ID was not being saved correctly in the user's profile. This is solved in this release. Props to demeritcowboy for reporting it.

= v190822 =

- (s2Member) **PayPal Integration Update:** PayPal deprecated the subscription modification button. Using the old possible values for this, now gives an error on PayPal's site. This button has been removed from the PayPal Standard integration in s2Member. Props to Tim for reporting it, see [forum thread 5861](https://f.wpsharks.com/t/5861), and [issue #1157](https://github.com/wpsharks/s2member/issues/1157).

- (s2Member) **Bug Fix:** PayPal would sometimes return the customer without the Custom Value expected by s2Member, incorrectly triggering an error. A small delay has now been added when needed to wait for PayPal to provide the missing value, so that the customer is met with the correct success message on return. Props to Josh Hartman for his help. See [forum thread 5250](https://f.wpsharks.com/t/5250).

- (s2Member) **Bug Fix:** Google's URL shortening service has been [discontinued](https://developers.googleblog.com/2018/03/transitioning-google-url-shortener.html). The s2Member integration with it was removed in this release. Props to Felix Hartmann for reporting it.

- (s2Member) **Feature Enhancement:** The popular URL shortening services have been abused in spam emails, and this can cause your site's emails with shortened signup URLs to end up in the spam folder. It's now possible to disable URL shortening when trying to avoid this problem. Props to Felix Hartmann for suggesting it. See [forum thread 5697](https://f.wpsharks.com/t/5697).

- (s2Member Pro) **New Feature:** It is now possible to use a custom URL shortener other than the defaults in the s2Member Framework. This is particularly useful to use [YOURLS](http://yourls.org/) for your links, making them unique to your site, looking more professional and avoiding the spam filters issue mentioned above. For more info see this [forum post](https://f.wpsharks.com/t/5697/19).

= v190617 =

- (s2Member Pro) **Authorize.Net Hash Upgrade:** Authorize.Net [announced](https://support.authorize.net/s/article/MD5-Hash-End-of-Life-Signature-Key-Replacement) the end-of-life for their MD5 Hash in favor of their new SHA512 Signature Key. Support for this has been added to s2Member Pro. The MD5 Hash is not provided by Authorize.Net any more, so the field for it in s2Member has been disabled. Props @krumch for his work. For further details see [forum thread 5514](https://f.wpsharks.com/t/5514).

  **Note:** For those that already used the MD5 Hash in their configuration, it is kept there and will keep working while Authorize.Net accepts it, which will not be much longer. It's important to update your integration with the new Signature Key. Once you have your Signature Key in the s2Member configuration, it will be favored over the old MD5 Hash._

- (s2Member Pro) **Bug Fix:** The multisite patch for `wp-admin/user_new.php` wasn't finding the code to replace because of changes in the latest releases of WordPress. It has now been updated, as well as the instructions in the Dashboard for those that prefer to apply it manually. Props @crazycoolcam for reporting it. For further details see [Issue #1132](https://github.com/wpsharks/s2member/issues/1132).

  **Note:** If you already had patched this file in the past, it's recommended that you remove the previous patch restoring it to the original file, and let s2Member Pro patch it again now, otherwise you risk getting it patched over the previous one and ending up with errors. After the new patch, please review that file to verify that it's correct._

- (s2Member Pro) **Bug Fix:** The search results for `s2Member-List` were not being ordered as specified in the `orderby` attribute when this was a field from the `usermeta` table in the database, e.g. `first_name`, `last_name`. This is now fixed and working correctly. Props to @stevenwolock for reporting it. For further details see [Issue #1103](https://github.com/wpsharks/s2member/issues/1103).

- (s2Member) **WP 5.2 Compat. Enhancement:** s2Member has been tested with WP up to 5.2.2-alpha. With `WP_DEBUG` enabled, only one "notice" was found. In `wp-login.php` it said 'login_headertitle is deprecated since version 5.2.0! Use login_headertext instead.' This release now uses `login_headertext` and doesn't get that notice anymore. Props Azunga for reporting it. See [forum thread 5962](https://f.wpsharks.com/t/5962).

= v170722 =

- (s2Member/s2Member Pro) **PayPal IPN Compatibility:** This release includes an updated PayPal IPN handler that is capable of reading number-suffixed IPN variables that are now being sent by PayPal's IPN system in some cases, for some customers. We strongly encourage all site owners to upgrade to this release as soon as possible, particularly if you're using PayPal to process transactions. Props @openmtbmap and @patdumond for reporting. See: [Issue #1112](https://github.com/websharks/s2member/issues/1112)

= v170524 =

- (s2Member/s2Member Pro) **PHP v7 Compat. Enhancements**: This release adds an integration with the [Defuse encryption library](https://github.com/defuse/php-encryption) for PHP, making it possible for s2Member to move away from the `mcrypt_*()` family of functions in versions of PHP >= 7.0.4, where the mcrypt library has been deprecated — `mcrypt_*()` will eventually be removed entirely.

  Starting with this release of s2Member, if you're running s2Member on PHP v7.0.4+, the Defuse library will be used automatically instead of mcrypt. See [Issue #1079](https://github.com/websharks/s2member/pull/1079).

  **Note:** Backward compatibility with mcrypt functions will remain for now, especially for the decryption of any data that was previously encrypted using RIJNDAEL-256; i.e., data encrypted by a previous release of the s2Member software. s2Member is capable of automatically determining the algorithm originally used to encrypt, which allows it to decrypt data using Defuse, else RIJNDAEL-256, else XOR as a last-ditch fallback.

  **API Functions:** `s2member_encrypt()` & `s2member_decrypt()`. These two API Functions provided by s2Member are impacted by this change. Starting with this release, if you're running s2Member on PHP v7.0.4+, the Defuse library is used automatically instead of the older mcrypt extension. Not to worry though; the `s2member_decrypt()` function is still capable of decrypting data encrypted by previous versions of the s2Member software.

- (s2Member/s2Member Pro) **UI Fix:** All menu page notices should be given the `notice` class and the additional `notice-[type]` class instead of the older generic `updated` and `error` classes. Fixed in this release. Related to [Issue #1034](https://github.com/websharks/s2member/issues/1034)

- (s2Member/s2Member Pro) **UI Fix:** Plugins displaying Dashboard-wide notices using the older `updated` and `error` classes should be handled better to avoid displaying them below the s2Member header (on s2Member menu pages) and with non-default WordPress styles. See: [Issue #1034](https://github.com/websharks/s2member/issues/1034)

- (s2Member/s2Member Pro) **UI Fix:** Improving color highlighting in input fields following a media library insertion; e.g., when adding a custom logo to the login/registration page.

- (s2Member Pro) **Bug Fix:** Merchants using PayPal Pro (Payflow Edition) to charge a fixed non-recurring fee following an initial 100% free trial period, were seeing their member accounts EOTd after the trial ended, instead of the EOT Time being set to the end of the fixed term period. Props @patdumond, James Hall, and many others for reporting this in the forums and at GitHub. See [Issue #1077](https://github.com/websharks/s2member/issues/1077).

- (s2Member Pro) **Bug Fix:** Updating PHP syntax in Simple Export tool, for compatibility w/ modern versions of PHP. Props @patdumond for reporting and helping us locate the underlying cause of this problem. See [Issue #1055](https://github.com/websharks/s2member/issues/1055).

- (s2Member Pro) **Stripe Bug Fix:** This releases corrects a seemingly rare conflict between s2Member and Stripe on certain mobile devices and in certain scenarios. In a case we examined, there was a problematic CSS `z-index` setting in the s2Member source code that was, at times, causing problems in the stacking order, which resulted in a user's inability to enter details into the Stripe popup form. In this release, s2Member's customization of the `z-index` stacking order has been removed entirely, as it is no longer necessary in the latest revision of the Stripe popup, which already handles `z-index` adequately. Props @jaspuduf for reporting and for helping us diagnose the problem. See [Issue #1057](https://github.com/websharks/s2member/issues/1057).

- (s2Member/s2Member Pro) **Security Enhancement:** This release removes the `%%user_pass%%` Replacement Code from the API Registration Notification email that is sent to a site owner; i.e., when/if it is configured by a site owner. Props @patdumond see [Issue #954](https://github.com/websharks/s2member/issues/954). This Replacement Code was removed as a security precaution.

- (s2Member/s2Member Pro) **Bug Fix:** Resolving internal warning: 'PHP Warning: Parameter 2 to c_ws_plugin__s2member_querys::_query_level_access_coms() expected to be a reference, value given'. This was resolved by removing the strict 'by reference' requirement from the list of parameters requested by s2Member.

- (s2Member/s2Member Pro) **Bug Fix:** Resolving internal warning: 'PHP Warning: Illegal string offset 'user_id' in s2member/src/includes/classes/sc-eots-in.inc.php'. This was resolved by typecasting `$attr` to an array in cases where WordPress core passes this as a string; e.g., when there are no attributes.

- (s2Member Pro) **Bug Fix:** Incorrect default option value for `reject_prepaid=""` attribute in Stripe Pro-Forms. See: [Issue #1089](https://github.com/websharks/s2member/issues/1089)

= v170221 =

- (s2Member/s2Member Pro) **JW Player v7:** This release adds support for JW Player v7 in the `[s2Stream /]` shortcode. See [Issue #774](https://github.com/websharks/s2member/issues/774).

- (s2Member Pro) **Bug Fix:** Allow Pro-Forms to use `success="%%sp_access_url%%"` without issue. See [Issue #1024](https://github.com/websharks/s2member/issues/1024).

- (s2Member/s2Member Pro) **AWS Region:** Adding AWS region `ap-northeast-2`. See [Issue #1033](https://github.com/websharks/s2member/issues/1033).

- (s2Member/s2Member Pro) **AWS Region:** Adding AWS region `eu-west-2`. See [Issue #1033](https://github.com/websharks/s2member/issues/1033).

- (s2Member) **Bug Fix:** This release corrects a minor server-side validation bug that was related to the use of non-personal email address. See [Thread #1195](https://forums.wpsharks.com/t/bugfix-file-custom-reg-fields-inc-php-missing-bracket/1195) and [Issue #1054](https://github.com/websharks/s2member/issues/1054).

- (s2Member) **Bug Fix:** Updated several outdated links within the software; e.g., removing older `www.` references, correcting forum links, and more. Also corrected missing changelog. See [Issue #1027](https://github.com/websharks/s2member/issues/1027).

- (s2Member Pro) **Pro Upgrader:** The pro upgrader has been refactored and now asks for your s2Member Pro License Key instead of your s2Member.com password. The next time you upgrade to the most recent version of s2Member Pro, you will be asked for your License Key. You can obtain your License Key by logging into your account at s2Member.com. Once logged in, visit your 'My Account' page, where you will find your License Key right at the top. See [Issue #668](https://github.com/websharks/s2member/issues/668).

- (s2Member/s2Member Pro) **CloudFlare Compat.:** Enhancing compatibility with Rocket Loader via `data-cfasync="false"` on dynamic s2Member scripts. See: [Issue #1038](https://github.com/websharks/s2member/issues/1038).

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

= v150925 =

- (s2Member/s2Member Pro) **WP v4.3 Compat.** This release corrects a minor backward compatibility issue with versions of WordPress before v4.3, and for installations of s2Member that still use the `%%user_pass%%` Replacement Code in their New User Email notification. See [this GitHub issue](https://github.com/websharks/s2member/issues/710) if you'd like additional details.

- (s2Member/s2Member Pro) **WP v4.3.1 Compat.** This release corrects a compatibility issue whenever you run s2Member together with WordPress v4.3.1+. Note that WordPress v4.3 made changes to the `wp_new_user_notification()` function in WordPress core. Then, a later release of WP v4.3.1 changed it again; breaking compatibility in both instances. This release brings s2Member up-to-date with WordPress v4.3.1 and preserves backward compatibility with WordPress v4.3, as well for versions prior. Props @bridgeport. See [this GitHub issue](https://github.com/websharks/s2member/issues/732) if you'd like additional details.

- (s2Member/s2Member Pro) **Bug Fix**: Fixed a bug where the s2Member CSS and JS was not loaded on the Dashboard when WordPress was installed in a subfolder that was different from the Home URL. Props @magbicaleman. See [Issue #696](https://github.com/websharks/s2member/pull/696).

- (s2Member Pro) **Bug Fix:** This release corrects a security issue related to the Pro Upgrade Wizard for s2Member Pro being displayed without checking `current_user_can('update_plugins')`. Resolved. Props @raamdev for identifying this and working to implement the fix. See [this GitHub issue](https://github.com/websharks/s2member/issues/697) if you'd like additional details.

- (s2Member Pro) **Bug Fix:** This release corrects a bug impacting the `wp_lostpassword_url()` function whenever s2Member is configured to run in a Multisite Network. The link is now adjusted automatically so that a lost password is always recovered from the current site, not the Main Site in the network. Props to @raamdev See also: [this GitHub issue](https://github.com/websharks/s2member/issues/711) for further details.

- (s2Member Pro) **Bug Fix:** Stripe Pro-Forms presented after a long block of text on a page, were not returning to the proper hash location after a Coupon Code was applied. Fixed in this release. Props @raamdev See also: [this GitHub issue](https://github.com/websharks/s2member/issues/730) if you'd like additional details.

- (s2Member/s2Member Pro) **SSL Edge Case:** This release corrects an SSL + Protected File Download problem that may have occurred in rare circumstances. Reproducing this required that you have a user with an ISP that changed their IP address whenever they accessed a site over `https` instead of `http`, and that an s2Member Protected File Download link is presented on an HTTPS page. And, that you were using s2Member's own force-SSL filters. A symptom of this issue was to receive mysterious reports of a user getting a 503 error when trying to access a protected file. Resolved in this release. See [this GitHub issue](https://github.com/websharks/s2member/issues/702) if you'd like additional details.

= v150827 =

- (s2Member/s2Member Pro) **WordPress v4.3 Compat./Bug Fix** This release of s2Member alters the way New User Notification Emails are sent, and in how they should be formatted in WordPress v4.3+.

  The New User Notification Email is now sent (to a user) only if they did _not_ set a Custom Password during their registration; i.e., only if they need this email to set their password for the first time. In short, s2Member now follows the same approach used by WordPress v4.3+.

  See:  **Dashboard → s2Member  → General Options → Email Configuration → New User Notification**

  So the purpose of this particular email has changed just a bit; i.e., the New User Notification Email. Instead of it being sent to every new user, it is only sent to users who need it for the purpose of obtaining a password which grants them access to their account for the first time.

  **Upgrading to WordPress v4.3 and the latest release of s2Member?**

  Please review this section of your Dashboard carefully:
  **s2Member  → General Options → Email Configuration → New User Notification**

  - If you are using s2Member to customize the New User Notification email, you should try to update this message so that it includes the new `%%wp_set_pass_url%%` Replacement Code.

  See also: [this comment at GitHub about the recent changes, with screenshots](https://github.com/websharks/s2member/issues/689#issuecomment-134563230).

- (s2Member Pro) **`[s2Member-List /]` Bug Fix:** This release corrects a bug in the `[s2Member-List /]` shortcode that was causing `levels="0"` not to work, and in fact any use of a `0` in the `levels=""` attribute was broken. See [this GitHub issue](https://github.com/websharks/s2member/issues/663) if you'd like additional details. Props to @patdumond for reproducing, reporting and testing this issue.

- (s2Member/s2Member Pro) **Emoji Bug Fix:** This release corrects a bug in s2Member's SSL filters that can be applied with the Post/Page Custom Field `s2member_force_ssl` being set to `yes`. A symptom of this bug was to see an SSL warning in the latest release of WordPress related to the new Emoji library. See [this GitHub issue](https://github.com/websharks/s2member/issues/674) if you'd like additional details.

= v150722 =

- (s2Member/s2Member Pro) **New Shortcode:** This release introduces a powerful new shortcode which allows you to display a user's EOT (End of Term) or NPT (next payment time) in a WordPress Post or Page. For further details and some minor limitations, please see [`[s2Eot /]` Shortcode Documentation](http://s2member.com/kb-article/s2eot-shortcode-documentation/). Props to @raamdev and @patdumond for their strategic assistance, feedback, and ideas for this shortcode.

- (s2Member/s2Member Pro) **Strong Password Enforcement:** This release of s2Member makes it possible for a site owner to enforce strong passwords; i.e., to require a minimum number of characters and a specific strength (i.e., mix of required characters). The default minimum length in s2Member changed from `6` to `8` characters minimum. The default password strength minimum is `good`. To customize, please see: **s2Member → General Options → Registration/Profile Fields & Options**. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/573) if you'd like additional details. Props to @patdumond and @KTS915 for ideas and feedback.

- (s2Member Pro) **reCAPTCHA v2 Upgrade:** This release of s2Member comes with an updated reCAPTCHA™ integration in order to take advantage of No CAPTCHA reCAPTCHA and other enhancements provided by the v2 update on Google's end.

  _Existing s2Member installations that already have an old set of reCAPTCHA v1 Public/Private keys will continue to function as before. However, it is suggested that you configure reCATPCHA v2 keys in order to put s2Member Pro-Forms into the v2 mode moving forward. Please see: **Dashboard → s2Member → General Options → CAPTCHA Anti-Spam Security** where you will find instructions._

- (s2Member/s2Member Pro) **PayPal IPN Compat.** This release addresses a problem with IPN connection failures that result in a 500 Internal Server Error on the PayPal side; occurring whenever s2Member attempts to verify IPN data. Please see: [this GitHub issue](https://github.com/websharks/s2member/issues/610) if you'd like additional details.

- (s2Member Pro) **Stripe Bug Fix:** This release corrects a bug in Stripe Pro-Form Checkout Options, where a Free Registration option could cause other paid Checkout Options to result in a checkout error under the right conditions. See [this GitHub issue](https://github.com/websharks/s2member/issues/569) for further details.

- (s2Member/s2Member) **Google Analytics Compat.** This release automatically preserves `utc_` variables that are used by Google Analytics whenever a Membership Options Page redirection occurs. i.e., if a visitor comes to the site with `utc_` variables and is redirected to the Membership Options Page, because the content they were trying to access is protected; the `utc_` variables are preserved during this redirection, and delivered as part of the Membership Options Page redirect.

- (s2Member Pro) **Authorize.Net Endpoint Filters:** This release adds two new WordPress Filters (i.e., Hooks) that can be used by developers in certain rare cases. Hook names are `ws_plugin__s2member_pro_authnet_aim_endpoint_url` and `ws_plugin__s2member_pro_authnet_arb_endpoint_url `. See [this GitHub issue](https://github.com/websharks/s2member/issues/575#issuecomment-104077606) if you'd like additional details and a quick example of use.

- (s2Member Pro) **Authorize.Net AIM Compat.:** This release addresses a compatibility issue that came to light recently, which was actually attributed to a bug in s2Member Pro that has been sliding through unnoticed until now. The format for an expiration date sent to the Authorize.Net AIM API should be `MM-YYYY`. The format for ARB API calls is `YYYY-MM`. s2Member Pro was sending `YYYY-MM` to both APIs. Fixed in this release. Props to @raamdev for investigating this. See also [this GitHub issue](https://github.com/websharks/s2member/issues/576) if you'd like additional details.

- (s2Member Pro) **`[s2Member-List /]` Bug:** This release corrects an issue in the `[s2Member-List /]` shortcode that was preventing the `display_name` DB column from being searchable. This release also adds the `display_name` to the list of default `search_columns=""` that are considered by the `[s2Member-List /]` shortcode. Props to @patdumond for researching this. See [this GitHub issue](https://github.com/websharks/s2member/issues/578) for further details.

- (s2Member/s2Member Pro) **Bug Fix:** This release corrects an issue where s2Member would fail to subscribe customers to configured mailing list IDs whenever an existing customer is upgrading and you have the Double Opt-In Checkbox turned off entirely. Fixed. See [this GitHub issue](https://github.com/websharks/s2member/issues/581) if you would like additional details.

- (s2Member Pro) **Stripe Bug Fix:** This release corrects a bug in s2Member's Stripe Pro-Forms, related to having multiple Checkout Options. The bug resulted in a missing error message whenever one of the Checkout Options was submitted incorrectly, and also resulted in the default Checkout Option being magically selected instead of the one that a customer was working with. Props to @patdumond and @bryanthankins. See: [this GitHub issue](https://github.com/websharks/s2member/issues/586) if you'd like additional details.

- (s2Member/s2Member Pro) **Bug Fix:** This release fixes an issue where the s2Drip shortcode was requiring PHP 5.3+; this fix allows the shortcode to work properly with PHP 5.2+.

- (s2Member Pro) **Compat.** A call to `WP_Widget` was updated to support WordPress v4.3+. See [this GitHub issue](https://github.com/websharks/s2member/issues/607) if you'd like additional details.

- (s2Member/s2Member Pro) **Bug Fix:** This release corrects a bug in the s2Member IPN handler that processes full refunds. In your s2Member EOT Behavior options, if you choose the  `refunds,partial_refunds,reversals` option it results in a full refund not being processed; i.e., an EOT does not occur as expected. s2Member was incorrectly recording that your configured preference was not to process refunds whenever a full refund occurs. Fixed in this release. See also [this GitHub issue](https://github.com/websharks/s2member/issues/614) if you'd like additional details.

- (s2Member/s2Member Pro) **Wikpedia Links:** Updated throughout to use an `https://` protocol. Now the Wikipedia default. This impacts mostly the back-end of s2Member which references a few articles at the Wikipedia. However, it also impacts Pro-Forms where a link is provided to users with more information about Security Codes that appear on the back of credit cards. See [this GitHub issue](https://github.com/websharks/s2member/issues/617) if you'd like additional details.

- (s2Member/s2Member Pro) **qTranslate X Compat.** This release includes a minor update that improves compatibility with qTranslate X. See [this GitHub issue](https://github.com/websharks/s2member/issues/618) if you'd like additional details.

- (s2Member/s2Member Pro) **AWeber Compat.** This release resolves an issue with AWeber rejecting subscribers that have IPv6 addresses. Until such time as AWeber adds support for IPv6 addresses, s2Member will simply send an empty IP address whenever it encounters an IPv6 address. This behavior was requested by the AWeber team. See [this GitHub issue](https://github.com/websharks/s2member/issues/611) if you'd like additional details.

- (s2Member Pro) **Coupon Code Expiration:** This release improves the way coupons that are set to expire are handled. Instead of expiring at midnight the day before the configured  expiration date, coupon codes now expire at the end of the configured day. As always, all times are calculated from GMT/UTC time, the same as WordPress itself. In short, if you set a coupon to expire Dec 5th, the coupon will now expire Dec 5th, at the end of the day (UTC time). The old behavior, was for the coupon to expire Dec 4th at midnight UTC time, which led to confusion in many cases. Please see [this GitHub issue](https://github.com/websharks/s2member/issues/612) if you'd like additional details.

- (s2Member/s2Member Pro) **IPN Proxy Key Bug:** This release corrects a minor bug in s2Member's IPN Proxy Key generation that was causing problems in just a few edge cases. This bug may have impacted your site if you had a domain name being accessed with a `Host:` header containing mixed caSe. Not normal behavior, but there are a few edge cases where it's important for s2Member to get this right in order to avoid an "Unable to verify $_POST vars." error. See [this GitHub issue](https://github.com/websharks/s2member/issues/590) if you'd like additional details.

- (s2Member/s2Member Pro) **Password Reset Layout:** This release improves the layout/CSS applied to the WordPress password reset form in order to better separate the password strength indicator from the instructions provided by WordPress. See [this GitHub issue](https://github.com/websharks/s2member/issues/585) if you'd like additional details. Props to @patdumond, @BugRat, and @raamdev for discovering this.

- (s2Member) **Back-end UI Quick Links:** This release resolves an overlap in the display of the quick links atop each menu page in the Dashboard. This bug impacted the lite version only. If you'd like additional details, please see [this GitHub issue](https://github.com/websharks/s2member/issues/589). Props to @raamdev for discovering this.

- (s2Member Pro) **Username Compat.:** This release updates s2Member's own validation against usernames in order to bring it inline with the most recent versions of WordPress core; i.e., we now allow whitespace in usernames. This release was updated so that usernames are validated only by the WordPress core function: `sanitize_user()`, which does allow single whitespace characters in usernames. Please see [this GitHub issue](https://github.com/websharks/s2member/issues/566) if you'd like additional details.

- (s2Member Pro) **Message After Modification:** This release improves the default response that a customer who is upgrading/downgrading receives after having completed checkout using a Pro-Form. Instead of asking the customer to "log back in", s2Member simply says, "Thank you. Your account has been updated.". There is no reason for a customer to log back in; i.e., this is not necessary, and that message was leading to some confusion. Note also that with Pro-Forms you can provide a Custom Return URL on Success using the `success=""` shortcode attribute. Thus, this message is simply a default. We suggest that you customize in all cases. See [this GitHub issue](https://github.com/websharks/s2member/issues/580) if you'd like additional details. Props to @patdumond for reporting this important issue.

- (s2Member Pro) **Documentation Update:** This releases improves the documentation for the `rrt=""` shortcode attribute in all Pro-Form implementations; e.g., PayPal Pro-Forms, Authorize.Net Pro-Forms, and Stripe Pro-Forms. The `rrt=""` attribute can be somewhat misleading, so we added the following: **IMPORTANT NOTE:** If you don't offer a trial period; i.e., the first charge occurs when a customer completes checkout, you should set this to the number of additional payments, and NOT to the total number. For instance, if I want to charge the customer a total of 3 times, and one of those charges occurs when they complete checkout, I set should this to `rrt="2"` for a grand total of three all together.

- (s2Member/s2Member Pro) **Bug Fix:** This release corrects an issue with EOT calculations under a specific circumstance. If a customer registered on the site for free, and later made a purchase that included a free trial period, and they canceled within the trial period, the EOT was being incorrectly calculated based on the user's WordPress registration time instead of being based on the time that their trial began. This resulted in an immediate EOT (due to it being a date in the past), instead of being set to the end of the trial. Fixed in this release.

- (s2Member/s2Member Pro) **Documentation Update:** This release replaces a specific symbol that has been used throughout the Dashboard with s2Member. Instead of the `⥱` symbol we are now using the more compatible `→` symbol instead. This is used to indicate a Dashboard path.

- (s2Member/s2Member) **E_NOTICE:** Several `E_NOTICE`-level warnings were resolved in this release. Note that `E_NOTICE`-level warnings only show up in `WP_DEBUG` mode for developers, but they are frustrating nonetheless. Props to @raamdev for reporting some of these.

- (s2Member Pro) **Bug Fix:** PayPal Pro-Forms selling to customers who choose a Maestro/Solo card may experience problems in some circumstances. GBP currency conversion was partially failing due to a change in the underlying API that s2Member calls upon. Fixed in this release. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/605) if you'd like additional details.

- (s2Member/s2Member Pro) **Opt-In Bug Fix:** This release of s2Member corrects a bug that was causing members to be automatically unsubscribed from your mailing list whenever you choose to hide the Double Opt-In Box. A customer updating their profile later without this box, was being unsubscribed inadvertently. Fixed in this release. Props to @raamdev for his work in reproducing and reporting this bug. See [this GitHub issue](https://github.com/websharks/s2member/issues/633) if you'd like additional details.

- (s2Member Pro) **Stripe Bug Workaround:** It came to our attention that some Stripe API calls that simply update the `name`, `address_state`, `address_zip`, and `address_country` for tax reporting purposes were resulting in a card decline even after Stripe approved the transaction. We suspect this is a bug in the Stripe API. It has been reported to Stripe. For now though, we are working around this issue by failing gracefully in such a scenario. This simple update is there only for tax reporting purposes, so if it fails it does not warrant a refusal to complete the transaction.m It is simply logged by s2Member for analysis. Please see [this GitHub issue](https://github.com/websharks/s2member/issues/535) where a deeper investigation is underway for our next maintenance release.

- (s2Member Pro) **Stripe API Update:** This release of s2Member takes advantage of the latest Stripe API version. Moving from `v2015-02-18` to `v2015-07-13`. See [this article at Stripe](https://stripe.com/docs/upgrades#api-changelog) if you'd like additional details. _Remember that s2Member's API calls to Stripe will always use this specific version of their API (`v2015-07-13`), even if your Stripe account is configured with an older default version. This is to ensure that s2Member works as intended for all site owners._

- (s2Member Pro) **Stripe Prepaid Cards:** This release makes it possible for site owners to reject prepaid cards if they choose to do so. Stripe has the ability to determine if a credit/debit card is backed by a prepaid funding source. If it is, you can choose to reject or allow this type of card. The default behavior is to accept it. See: **Dashboard → s2Member → Stripe Options → Account Details → Reject or Allow Prepaid Cards** for further details. See also: [this GiHub issue](https://github.com/websharks/s2member/issues/505) if you'd like more information. Props to @raamdev for determining the feasibility of this feature.

- (s2Member Pro) **Bug Fix:** `Notice: Undefined index: password1` in `paypal-registration-in.inc.php`. This was another `E_NOTICE`-level warning that was cleaned up in this release. Props to @raamdev See [this GitHub issue](https://github.com/websharks/s2member/issues/634) if you'd like additional details.

- (s2Member Pro) **Stripe Bug Fix:** This release fixes a bug in Stripe Pro-Forms where upon a customer applying a 100%-off coupon code, the customer is met with an erroneous error regarding a missing state/zipcode--and only when a site owner has defined a tax configuration file also. Fixed in this release. See also [this GitHub issue](https://github.com/websharks/s2member/issues/548) if you'd like additional details.

- (s2Member Pro) **Automatic Update Compat.:** [Automatic Background Updates](https://codex.wordpress.org/Configuring_Automatic_Background_Updates) were introduced in WordPress v3.7 and while by default only WordPress core updates are updated automatically in this special way, it's still possible to enable automatic background updates for everything; including themes and plugins. For instance, some web hosting companies enable automatic/background plugin updates in an attempt to improve overall security.

  That's fine. However, when s2Member Pro is installed, it works as an add-on for the s2Member Framework plugin, and any update of the Framework plugin requires a manual or interactive update of the Pro add-on. Otherwise your site is left with only a portion of its original functionality until you complete the update. For that reason, starting with this release of s2Member, automatic background updates of the s2Member Framework are disabled automatically when you are also running s2Member Pro.

  Props to @raamdev for addressing this issue and providing the source code which made this enhancement possible. See also [this GitHub issue](https://github.com/websharks/s2member/issues/523) if you'd like additional details.

  _See also: [Instructions for Updating s2Member and s2Member Pro](https://s2member.com/updating/)_
- (s2Member Pro) **`[s2Member-Login /]` Shortcode:** This release includes a new shortcode that allows you to display a login box on any Post/Page that you create with WordPress. It can also double as a way to display a user's profile summary; including their avatar. See: [`[s2Member-Login /]` Shortcode Documentation](http://s2member.com/kb-article/s2member-login-shortcode-documentation/) for further details.

- (s2Member Pro) **`[s2Member-Summary /]` Shortcode:** This release includes a new shortcode that allows you to display a user's profile summary (including avatar) in any Post/Page that you create with WordPress. It can also double as a way to display a login box in case the user is not logged in yet (optional). See: [`[s2Member-Summary /]` Shortcode Documentation](http://s2member.com/kb-article/s2member-summary-shortcode-documentation/) for further details. Props to @patdumond for her ideas and feedback on this new feature.

- (s2Member/s2Member Pro) **Avatar via Shortcode:** The `[s2Get /]` shortcode has been updated in support of user avatars, to make it easier for site owners to include a member's avatar in any WordPress Post/Page of their choosing; e.g., `[s2Get user_field="avatar" size="96" /]` produces an `<img />` tag with the user's avatar. See also: [`[s2Get /]` Shortcode Documentation](http://s2member.com/kb-article/s2get-shortcode-documentation/) for further details/examples. Props to @patdumond for her ideas and feedback on this feature.

- (s2Member/s2Member Pro) **`[s2Get date_format="" /]` Now Possible:** The `[s2Get /]` shortcode was updated to support date formats whenever the `user_field=""` key that you want to display ends with `_time`; e.g., `[s2Get user_field="s2member_last_payment_time" date_format="M jS, Y, g:i a T" /]` produces: `Mar 5th, 2022, 12:00 am UTC` instead of a UNIX timestamp. See also: [`[s2Get /]` Shortcode Documentation](http://s2member.com/kb-article/s2get-shortcode-documentation/) for further details/examples, including PHP equivalents.

  _See also: [New `[s2Eot /]` Shortcode](http://s2member.com/kb-article/s2eot-shortcode-documentation/) with EOT-specific date/time functionality enhancements._

- (s2Member/s2Member Pro) **WordPress v4.3-beta Compat.:** This release was tested against WordPress v4.2+, including WordPress v4.3-beta. A few minor adjustments were made to improve support in the upcoming release of WordPress v4.3 based on beta releases made available to us.

- (s2Member/s2Member Pro) **goo.gl URL Shortener:** This release addresses a problem with the Google URL Shortening API. Google now requires that you configure an API key. Otherwise, API calls will fail often and s2Member reverts back to tinyURL instead. Starting with this release, if you enable the Google URL Shortener, you will need to supply an API key for it to work as expected. See: **s2Member → General Options → URL Shortening Service Preference** for further details. See also [this GitHub issue](https://github.com/websharks/s2member/issues/587) if you'd like additional details. Props to @bridgeport for reproducing and reporting this bug.

- (s2Member/s2Member Pro) **Bitly URL Shortener:** This release adds support for Bitly to be used as your preferred URL Shortening service. Bitly has become very popular for many reasons. One reason to choose Bitly over others is that you can configure your Bitly account to use a custom domain of your choosing; i.e., shortened URLs may contain [a domain that you configure](https://bitly.com/a/settings/advanced). See: **s2Member → General Options → URL Shortening Service Preference** for further details.

- (s2Member Pro) **Other Gateways:** Starting with this release, when you install the s2Member Pro add-on for the first time, there are two Pro gateways enabled by default. When you first install s2Member Pro (first-time users only), both the Stripe and PayPal Pro payment gateways will already be enabled for you. This is to help site owners avoid confusion. In addition, first-time users will be greeted by s2Member Pro with a reminder to configure your "Other Gateways". See also [this GitHub issue](https://github.com/websharks/s2member/issues/528) if you'd like additional details. Props to @raamdev for identifying this usability issue and providing feedback/suggestions.

- (s2Member Pro) **Stats Collection:** Starting w/ this release of s2Member Pro, we are now collecting important/anonymous server details that will help us better understand which versions of PHP/MySQL are most widely used by site owners running the pro version of our software. For further details, please see: [What anonymous information does s2Member Pro report to WebSharks, and why?](http://s2member.com/kb-article/what-information-does-s2member-pro-report-to-websharks/)

= v150311 =

- (s2Member/s2Member) **Bug Fix:** The list of users in the WordPress Dashboard was going blank in a particular scenario where a search was attempted in concert with a sortable s2Member column. Fixed in this release. Props to @bridgeport for finding this. See also [this GitHub issue](https://github.com/websharks/s2member/issues/496#issuecomment-76821470) if you'd like technical details.
- (s2Member Pro) **`[s2Member-List /]` Bug Fix:** This release resolves an issue with pagination in the `[s2Member-List /]` shortcode after recent changes in the `WP_User_Query` class. See [this GitHub issue](https://github.com/websharks/s2member/issues/493) if you'd like additional details.
- (s2Member Pro) **Remote Operations API (Bug Fix):** If a remote API call was made to find a user by `user_login`, and that username was all numeric, the `WP_User` class treated it like a user ID instead of as an actual username. Resolved in this release by calling `new WP_User(0, [user login])` as the second argument to the constructor. Thereby forcing `WP_User` to consider it a username. See also [this GitHub issue](https://github.com/websharks/s2member/issues/498) if you'd like technical details.
- (s2Member Pro) **Stripe Bug Fix:** Stripe Pro-Forms for Specific Post/Page Access should not disable the email address field for logged-in users. Resolved in this release. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/500) if you'd like technical details.
- (s2Member Pro) **Stripe Pro-Forms:** This release corrects a bug first introduced in the last release that prevented custom templates for Stripe Pro-Forms from working as intended. Please see [this GitHub issue](https://github.com/websharks/s2member/issues/510) if you'd like additional details.
- (s2Member Pro) **Bug Fix for Gift/Redemption Codes:** This release of s2Member corrects a bug that impacted the generation of Gift/Redemption Codes whenever they were sold with Specific Post/Page Access. See also [this GitHub issue](https://github.com/websharks/s2member/issues/512) if you'd like additional details.

= v150225 =

- (s2Member Pro) **Accept Bitcoin via Stripe!** This release of s2Member Pro comes integrated with the latest version of the Stripe API, where it is now possible to accept Bitcoin right along with most major credit cards—made possible by [Stripe's latest update to support Bitcoin](https://stripe.com/bitcoin). It's as easy as flipping a switch :-) Please see: `Dashboard → s2Member Pro → Stripe Options → Account Details → Accept Bitcoin`. Referencing [this GitHub issue](https://github.com/websharks/s2member/issues/482); i.e., the original feature request.
- (s2Member Pro) **Stripe API Upgrade:** This release of s2Member Pro updates the Stripe SDK and Stripe API to the latest version (Stripe API version: `2015-02-18`). In addition, this release forces a specific version of the Stripe API in all communication between Stripe and s2Member; thereby avoiding a scenario where the Stripe API could be updated again in the future, in ways that might prevent s2Member Pro from operating as intended. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/484) if you'd like technical details. Props to @pauloz1890 for reporting this.
- (s2Member/s2Member Pro) **Security Badge Sizes:** This release of s2Member corrects an issue with the `[s2Member-Security-Badge v="1" /]` shortcode. If you set `v="2"` or `v="3"`, the dimensions were miscalculated. Props to @Mizagorn See [this GitHub issue](https://github.com/websharks/s2member/pull/466) if you'd like additional details.
- (s2Member Pro) **Bug Fix:** Opt-in checkbox state (and some custom fields) were losing state when switching from one type of Pro Form to another—whenever Pro Form Checkout Options were in use. Fixed in this release. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/468) if you'd like additional details. Props to @zenzoidman for finding this!
- (s2Member) **Bug Fix:** Alt. View Restrictions stopped working on navigation menu items in the previous release of s2Member v150203 due to a default argument value being misinterpreted by a sub-routine. Fixed in this release. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/475) if you'd like further details.
- (s2Member/s2Member Pro) **Bug Fix:** Some site owners reported "paying" customers being left with a Membership Level of `0` at seemingly random times that may have occurred only once in every 300+ transactions. The issue was related to a regular expression being performed against encrypted binary data with an ungreedy `?` in the regex pattern. Certain characters in the binary output would be lost when specific character sequences were encountered; resulting in a random failure to decrypt cookies set by s2Member. In short, the underlying cause was identified and corrected in this release. Thanks to all who reported this. Our appreciation goes out to everyone who helped to test for this elusive bug. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/397) if you'd like additional technical details.
- (s2Member/s2Member Pro) **UI Enhancements:** This release includes an enhanced UI, along with many subtle improvements to the inline documentation/instructions provided within the WordPress Dashboard.
- (s2Member Pro) **Retiring Google Wallet:** Google [announced that they are retiring Google Wallet for Digital Goods](https://support.google.com/wallet/business/answer/6107573). s2Member Pro continues to support Google Wallet, but this release updates the "Other Gateways" section in the Dashboard to make it clear that Google Wallet will not be supported in future versions of s2Member Pro. In fact, Google Wallet for Digital Goods will [close March 2nd, 2015](https://support.google.com/wallet/business/answer/6107573).
- (s2Member/s2Member) **bbPress Bug Fix:** This release resolves a security issue when running a Multisite Network with bbPress + s2Member. Level 0 access was being granted by the bbPress plugin across all sites in a network. That behavior is fine for bbPress, but is unexpected when s2Member is running in a Network environment. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/465) if you'd like additional details. **IMPORTANT TIP:** If you experienced this issue, please read through [these important comments](https://github.com/websharks/s2member/issues/465#issuecomment-76039842) about bbPress Participants needing to be removed from child blogs in order to fully rid yourself of this problem; i.e., once you complete the update of s2Member, you should [also read this please](https://github.com/websharks/s2member/issues/465#issuecomment-76039842).
- (s2Member/s2Member Pro) **404 / Alt. Views Bug Fix:** This release of s2Member corrects a rare issue where the Membership Options Page (or other pages) can produce random 404 errors whenever s2Member's Alt. View Restrictions are enabled, and there is another plugin installed which runs a DB query using the `WP_Query` class _before_ the Main WP Query has been run. Resolved through the use of `->is_main_query()` instead of tracking it statically via `$initial_query`. See also [this GitHub issue](https://github.com/websharks/s2member/issues/481) if you'd like additional technical details.

= v150203 =

- (s2Member Pro) **Gift/Redemption Codes:** This release adds a powerful new shortcode: `[s2Member-Gift-Codes /]`. This makes it easy to generate and sell access to gift codes (i.e., gift certificates) and/or to a list of redemption codes. For instance,  where a single team leader might like to purchase multiple accounts they can distribute to others on a team, or in a group. Video demo here: http://s2member.com/r/giftredemption-codes-video/ ~ See also: [this GitHub issue](https://github.com/websharks/s2member/issues/386) for additional technical details.
- (s2Member Pro) **User-Specific Coupon Codes:** This release of s2Member makes it possible to configure Pro-Form Coupon Codes that are connected (i.e., only valid) when entered by specific Users/Members who are logged into the site. See: `Dashboard → s2Member → Pro Coupon Codes`. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/403) for additional technical details.
- (s2Member Pro) **Coupon Code Max Uses:** This release of s2Member Pro adds the ability to set a maximum number of times that a Coupon Code can be used. This makes it easy to create Coupon Codes that are designed to be used only one time, for instance; or for X number of times. After a Coupon Code is used X number of times, it will expire automatically. See: `Dashboard → s2Member → Pro Coupon Codes`. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/285) for technical details.
- (s2Member Pro) **Coupon Code Usage Tracking:** This release of s2Member Pro adds the ability to track the number of times that each of your Coupon Codes have been used. It is also possible to alter the number of uses, and/or set a maximum number of uses. See: `Dashboard → s2Member → Pro Coupon Codes`. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/285) for technical details.
- (s2Member Pro) **Coupon Code Active/Expires Dates:** This release of s2Member Pro makes it possible to establish both a start and end time for each of your Pro Coupon Codes. In previous versions of s2Member, it was only possible to set an expiration date. You can now create Coupon Codes that will become active at some point in the future automatically. See: `Dashboard → s2Member → Pro Coupon Codes`. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/285) for technical details.
- (s2Member Pro) **Coupon Code UI Enhancements:** This release of s2Member Pro comes with an updated UI that makes it easier to manage your Pro Coupon Codes. See: `Dashboard → s2Member → Pro Coupon Codes`. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/285) for technical details.
- (s2Member Pro) **Store Coupon Codes for Each User:** s2Member Pro now stores a list of all coupon codes that a customer has used on your site. See: `Dashboard → Users → Choose User [Edit]`. Scrolling down to the set of s2-related fields will reveal a new list of coupon codes. This list will be filled for new customers only; i.e., s2Member does not have this data for past purchases; only for new customers that you acquire after updating to the latest release. See also [this GitHub issue](https://github.com/websharks/s2member/issues/462) if you'd additional details.
- (s2Member/s2Member Pro) **EOT Custom Value:** In this release of s2Member, the `get_user_option('s2member_custom')` value is preserved after an EOT has taken place, making it possible for site owners to continue to read this value (along with any custom pipe-delimited values they have injected there), even after an EOT has taken place. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/449).
- (s2Member/s2Member Pro) **JW Player Broken Links:** This release corrects some broken links referenced by the inline documentation for s2Member in the WordPress Dashboard. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/448) if you'd like further details.
- (s2Member/s2Member Pro) **Security:** This release of s2Member checks for the existence of the WordPress PHP Constant: `WPINC` instead of looking for the less reliable `$_SERVER['SCRIPT_FILENAME']`. Some site owners reported this was causing trouble in a localhost environment during testing, or when running s2Member on some hosts that are missing the `SCRIPT_FILENAME` environment variable; e.g., some Windows servers. Fixed in this release. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/454) if you'd like additional details.
- (s2Member Pro) **Advanced Import/Export Compat:** This release of s2Member Pro includes compatibility and a bug fix when running on WordPress v4.1+. Three PHP notices during importation, along with some quirky behavior associated with the `role` CSV column have been corrected. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/455) for technical details.
- (s2Member Pro) **`[s2Member-List /]` Bug Fix:** This release resolves an issue with pagination in the `[s2Member-List /]` shortcode after recent improvements to the search functionality. See [this GitHub issue](https://github.com/websharks/s2member/issues/155#issuecomment-69403120) if you'd like additional details.
- (s2Member Pro) **`[s2Member-List /]` Enhancement:** This release improves search functionality in the `[s2Member-List /]` shortcode, making it so that all searches default to `*[query]*`; i.e., are automatically wrapped by wildcards. If a user enters a wildcard explicitly (or a double quote), this default behavior is overridden and the search query is taken as given in such a scenario. This makes the search functionality easier for end-users to work with, since it no longer requires an exact match. Default behavior is now a fuzzy match. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/394) if you'd like further details.
- (s2Member/s2Member Pro) **AWS v4 Authentication:** This release of s2Member adds AWS v4 Authentication support for Amazon Web Service Regions that only accept the AWS v4 authentication scheme. If you had trouble in the recent past when attempting to integrate s2Member with S3 Buckets (or with CloudFront) in regions outside the USA, this release should resolve those issues for you. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/440) if you'd like additional technical details.
- (s2Member Pro) **Bug Fix:** Pro-Form Checkout Options not working in all cases whenever they are used together with Free Registration Forms. Resolved in this release.

= v150102 =

- (s2Member/s2Member Pro) **Custom Field Mapping:** This release of s2Member adds an internal mapping from s2Member's Custom Field values for each user, to the `get_user_option()` function in the WordPress core. This makes it possible to retrieve user custom field values like always via `get_user_field()` or now through the native `get_user_option()` function also. The benefit of this is that s2Member's custom fields are now more compatible with other themes/plugins for WordPress.
- (s2Member Pro) **[s2Member-List /] Shortcode:** It is now possible to search through custom fields created with s2Member using the `search_columns=""` attribute; e.g., `search_columns="user_login,user_email,s2member_custom_field_MYFIELDID"`; where `MYFIELDID` can be replaced with a field ID that you generate with s2Member via `Dashboard → s2Member → General Options → Registration/Profile Fields`. See also: [this KB article](http://www.s2member.com/kb/s2member-list-shortcode/) for further details. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/155) for details regarding this improvement.
- (s2Member/s2Member Pro) **MailChimp Bug Fix** This release fixes a bug first introduced in the previous release, which was causing Interest Groups configured w/ s2Member to not be added properly. Resolved in this release. Props to @ethanpil Thanks!
- (s2Member Pro) **ccBill Buttons** This release updates all ccBill button graphics. The MasterCard logo has been removed, and a new set of buttons was created to improve upon the set provided in previous versions of s2Member Pro. See: [this GitHub issue](https://github.com/websharks/s2member/issues/392) if you'd like further details.
- (s2Member Pro) **Authorize.Net** The `AUD` currency code is now supported by Authorize.Net, and thus, s2Member Pro has been updated to support the `AUD` currency code for Pro-Forms integrated with Authorize.Net. See [this GitHub issue](https://github.com/websharks/s2member/issues/383) if you'd like further details.
- (s2Member Pro) **Subscr. CID for Stripe** This release corrects a bug which made it impossible to update the Subscr. CID value (for Stripe) using the user edit form in the Dashboard. For further details, please see [this GitHub issue](https://github.com/websharks/s2member/issues/380).
- (s2Member/s2Member Pro) **Bug fix** s2Member's membership access times log was failing to collect all required access times under certain scenarios where multiple CCAPS were being added or removed in succession within the same process, but across multiple function calls. This resulted in unexpected behaviors (in rare cases) when attempting to use the `[s2Drip /]` shortcode. Fixed in this release. See [this GitHub issue](https://github.com/websharks/s2member/issues/406) for technical details.
- (s2Member/s2Member Pro) **Compatibility** This release includes a fix for s2Member's Multisite Network patches applied to the `wp-admin/user-new.php` file whenever you configure s2Member on a Multisite Network. This change makes s2Member compatible with the coming release of WordPress v4.1 and v4.2-beta as it exists now. See: [this GitHub issue](https://github.com/websharks/s2member/issues/410) if you'd like additional details.
- (s2Member Pro) **Bug Fix:** A feature that was previously introduced in v140816, which made it possible for site owners to set a failed payment threshold (in s2Member's Authorize.Net integration), was suffering from an off-by-one issue during total failed payment calculations. Fixed in this release. See also [this GitHub issue](https://github.com/websharks/s2member/issues/416) if you'd like further details.
- (s2Member Pro) **Feature Enhancement:** Whenever a failed payment threshold is reached (in s2Member's Authorize.Net integration), not only will s2Member terminate on-site access, but now the underlying ARB (Automated Recurring Profile) is cancelled at the same exact time. This way future billing attempts on the Authorize.Net side will not be possible; i.e., it ensures that a failed payment threshold will always terminate both on-site access and the ARB itself together at the same time, as opposed to allowing the ARB termination to occur automatically via Authorize.Net, _whenever_. See also [this GitHub issue](https://github.com/websharks/s2member/issues/416) if you'd like further details.
- (s2Member Pro) **ClickBank Disclaimer:** This release of s2Member adds a default Auto-Return Header Template (customizable from `s2Member → ClickBank Options` in the Dashboard) which includes a disclaimer that ClickBank requires of most merchants before final approval.

  _This default template should help to reduce the time it takes new merchants to receive final approval from ClickBank when first starting out in the ClickBank network. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/412) if you'd like further details._
- (s2Member Pro) **Bug Fix:** PayPal Pro-Forms for Specific Post/Page Access, and configured with `accept="paypal"` (i.e., to accept PayPal only) were not hiding the entire Billing Method section as intended. Fixed in this release. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/399) if you'd like further details.
- (s2Member Pro) **Bug Fix:** PayPal Pro-Forms using Express Checkout for Billing Agreements under a non-native currency (i.e., under a different currency than their own PayPal account) were failing under some scenarios (notably with the `BRL` currency code). Fixed in this release. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/414) if you'd like technical details.
- (s2Member Pro) **Stripe API:** s2Member's Stripe integration has been updated to use the new `statement_descriptor` field in favor of the now deprecated `statement_description`. See [this GitHub issue](https://github.com/websharks/s2member/issues/422) for technical details.
- (s2Member Pro) **Stripe Bug Fix:** In the case of a global tax rate having been applied to the total cost, there were certain scenarios where s2Member Pro would kick back an error message, "Invalid Parameters to Stripe". Fixed in this release. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/425) if you'd like technical details.
- (s2Member/s2Member Pro) **WP Core Compat.:** This version of s2Member forces the `wptexturize` filter off in WordPress, due to a bug that was introduced in recent versions of the WordPress core; which results in broken shortcodes in some scenarios. Until the underlying bug is fixed in the WP core, the `wptexturize` filter must be disabled to prevent corruption of any WordPress shortcode that may contain `<` or `>` symbols.

   See [this GitHub issue](https://github.com/websharks/s2member/issues/349) for further technical details. Also referencing: [this WordPress core bug report](https://core.trac.wordpress.org/ticket/29608).
- (s2Member/s2Member Pro) **Alt. Views:** This release fixes a bug that caused `wp_list_pages()` not to be filtered properly under certain scenarios. A symptom of this bug was to apply s2Member's Alt. View protection for "Pages", but for this not work properly in all cases. Fixed in this release. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/372) if you'd like technical details.
- (s2Member/s2Member Pro) **Currency Code/Symbol:** All email templates, API Notifications (except cancellation/EOT notifications), and all Custom Return URLs on Success; across all payment gateways; now support two additional Replacement Codes: `%%currency%%` and `%%currency_symbol%%`. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/314) if you'd like additional details.
- (s2Member Pro) **Coupon Codes:** All transaction-related email templates now support three additional Replacement Codes: `%%full_coupon_code%%`, `%%coupon_code%%`, and `%%coupon_affiliate_id%%`. These have been documented in your Dashboard in places where transaction-related email templates are configured. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/384) if you'd like additional details.
- (s2Member Pro) **Stripe Tax Info:** s2Member now attaches metadata to Stripe Charges and to Stripe Subscriptions which includes a JSON-encoded object containing two or more properties when tax applies.

  These metadata properties can be found in your Stripe Dashboard with the metadata key: `tax_info`; which contains the following JSON-encoded properties: `tax` (total tax that was or will be charged on the regular amount), `tax_per` (tax percentage rate that was applied based on your configuration of s2Member Pro); along with `trial_tax` and `trial_tax_per` in the case of a Stripe Subscription that includes an initial/trial period that requires payment; i.e., the tax applied (if any) to an initial/trial payment on a Subscription.

  We hope this additional information being recorded by s2Member and stored securely within your Stripe account will make it easier for you to maintain accurate bookkeeping records moving forward. This additional metadata is generated for new customers only. It will not be backfilled for any past transactions.

- (s2Member Pro) **Stripe Tax Info:** s2Member now passes the tax location; i.e., `address_state`, `address_zip`, and `address_country` to each Stripe Card object associated with a Stripe Customer.

  We hope this additional information being recorded by s2Member and stored securely within your Stripe account will make it easier for you to maintain accurate bookkeeping records moving forward. This additional cardholder data is collected and stored for new customers only; it will not be backfilled for any past transactions.

- (s2Member Pro) **Stripe IP Address:** s2Member now attaches the customer's IP address (as detected via `$_SERVER['REMOTE_ADDR']` on your server) into each Stripe Customer object, along with the customer's full name. These metadata properties can be found in your Stripe Dashboard with the metadata keys: `name` and `ip`.

- (s2Member Pro) **Stripe Coupon Code:** s2Member now attaches metadata w/ a coupon code used by your customer (if applicable) to each Stripe Charge and/or Stripe Subscription object.

  This metadata property can be found in your Stripe Dashboard with the metadata key: `coupon`; which contains the following JSON-encoded property: `code` i.e., the full coupon code used by your customer. This additional metadata is generated for new customers only. It will not be backfilled for any past transactions. Filled only for transactions that use a coupon code.
- (s2Member Pro) **Stripe Invoice:** This release corrects a bug in s2Member's Stripe integration whereby `subscr-signup-as-subscr-payment` was not always being forced into the core gateway processor; resulting in a miscalculation of the `last_payment_time` under certain scenarios. Fixed in this release. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/396) if you'd like additional details.

= v141007 =

- (s2Member Pro) **ClickBank IPN v6:** This release enables a new integration option for site owners integrated with ClickBank. You may now choose to integrate with v6 of ClickBank's IPN service, since all previous versions are slowly being phased out by ClickBank. Please see: `Dashboard → s2Member → ClickBank Options → IPN Integration` for v6 config. options. See also [this GitHub issue](https://github.com/websharks/s2member/issues/256) if you'd like further details regarding this topic. See also: [this article @ ClickBank](https://support.clickbank.com/entries/22803622-instant-notification-service).
- (s2Member/s2Member Pro) **AWeber API Integration:** This release of s2Member adds a new option for site owners using AWeber. It is now possible to integrate with the new [s2Member App](http://www.s2member.com/r/aweber-api-key) for AWeber; i.e., via the AWeber API instead of via email-based communication. For further details, please see: `Dashboard → s2Member → API / List Servers → AWeber Integration`. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/303) if you'd like additional details.
- (s2Member/s2Member Pro) **Bug Fix:** The EOT Behavior option for `refunds,partial_refunds,reversals` was not being accepted by s2Member. Fixed in this release. Please see [this GitHub issue](https://github.com/websharks/s2member/issues/345) if you'd like further details.
- (s2Member/s2Member Pro) **MailChimp API Wrapper:** This release of s2Member comes with an updated API wrapper class for MailChimp integration. No change in functionality, just a smoother, slightly faster, and more bug-free interaction with the MailChimp API. Please see [this GitHub issue](https://github.com/websharks/s2member/issues/303) if you'd like further details regarding this improvement. See also: [the official MailChimp API class](https://bitbucket.org/mailchimp/mailchimp-api-php); i.e., what s2Member has been updated to in this release.
- (s2Member/s2Member Pro) **URI Restrictions caSe-insensitive (Security Fix)** This release of s2Member changes the way URI Restrictions work. All URI Restrictions are now caSe-insensitive (i.e., `/some-path/` is now the same as `/some-Path/`), allowing s2Member to automatically pick up different variations used in attempts to exploit the behavior of certain slugs within the WordPress core. You can also change this new default behavior, if you prefer. Please see: `Dashboard → s2Member → Restriction Options → URI Restrictions`. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/354) for the details about why this was changed in the most recent copy of s2Member.
- (s2Member/s2Member) **AWeber Role-Based Emails:** In this release we're adding a note in the s2Member UI regarding role-based email addresses being rejected by AWeber. AWeber does not allow role-based emails like: `admin@` or `webmaster@` to be subscribed. It is suggested that you enable s2Member's config. option: "Force Personal Emails" if you intend to integrate with AWeber. Please see: `Dashboard → s2Member → General Options → Registration/Profile Fields`; where you can tell s2Member for force personal email addresses when someone registers on-site. This will prevent a potential subscriber from entering something like `admin@example.com` as their email address.

= v140921 =

- (s2Member/s2Member Pro) **WP v4 over SSL Compat.** A compatibility issue with the `home_url()` function in the latest release of WordPress has been resolved with this release. Some site owners reported that their s2Member menu pages were appearing without any CSS/JavaScript being loaded; i.e., the graphical UI was not appearing as one would expect under certain scenarios.
- (s2Member/s2Member Pro) **WP v4 Compat.** This release brings s2Member up-to-date with the latest changes to the `like_escape()` function in WP v4.0. The `like_escape()` function is now deprecated in favor of `wpdb::esc_like()`. s2Member has been updated in this release, but also remains compatible with previous versions of WordPress. See [this GitHub issue](https://github.com/websharks/s2member/issues/329) if you'd like further details.
- (s2Member Pro) **[s2MOP /] Shortcode Enhancment** The `[s2MOP /]` shortcode allows for a new `required_value=""` attribute. Please see [this KB article](http://www.s2member.com/kb/s2mop-shortcode/) for details about the `[s2MOP /]` shortcode. See also: [this GitHub issue](https://github.com/websharks/s2member-pro/issues/51) if you'd like further details.

= v140909 =

- (s2Member/s2Member Pro) **Compatibility:** Several instances of `site_url()` (a WordPress core function) have been converted to `home_url()` instead. This provides better compatibility with WordPress installations running from a sub-directory. Please see [this GitHub issue](https://github.com/websharks/s2member/issues/293) if you'd like further details.
- (s2Member Pro) **Bug Fix:** Ampersands; i.e., `&` symbols in a ClickBank button `desc=""` attribute are now converted to the word `and` automatically. The symbol itself causes issues in ClickBank's IPN processing. Fixed in this release. Please see [this GitHub issue](https://github.com/websharks/s2member/issues/253) if you'd like further details.
- (s2Member) **Bug Fix:** Improving compatibility with Mozilla/Firefox for the default `wp-login.php?action=register` handler. This release corrects an issue where `<select>` fields contained text with too large a font-size for Mozilla browsers to deal with. Please see [this GitHub issue](https://github.com/websharks/s2member/issues/244) if you'd like further details.
- (s2Member) **WP v4.0 / bbPress Compat.** A conflict between WordPress v4.0, bbPress v2.5.4 and the previous release of s2Member has been resolved. A symptom of this issue was to see the leading topic post missing from your bbPress forum threads.

  This was a complex issue related to changes in the most recent copy of WordPress where `WP_Query::$is_search` is flagged as `TRUE` when the `s` key `isset()` instead of `!empty()`. s2Member has implemented a workaround so that the conflict will no longer cause this problem for site owners running s2Member/bbPress.

  However, please note that some other 3rd-party plugins may still conflict in this way; when running the latest version of bbPress under WordPress v4.0. We are working to notify bbPress and other plugin authors about this issue; just to help others avoid the problem. While unconfirmed, some site owners reported that the Relevanssi search plugin may have some trouble with this also.
  For further details, please see <http://bbpress.org/?p=151839>. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/321) if you'd like all of the details regarding this workaround in the s2Member software.
- (s2Member) **WP v4.0 Compat.** Updating s2Member's use of the now-deprecated `get_all_category_ids()`. Using `get_terms()` instead. Please see [this GitHub issue](https://github.com/websharks/s2member/issues/322) if you'd like further details.
- (s2Member Pro) **Stripe Bug Fix:** This release corrects an issue with Stripe Pro-Forms and a `$0` trial period. A symptom of this bug was to find a customer's  Stripe token value missing from their Customer object in the Stripe Dashboard. This issue impacted Pro-Forms whenever a 100% free trial was offered (i.e., with a `$0` sale amount). Resolved by this release. Please see [this GitHub issue](https://github.com/websharks/s2member/issues/326) if you'd like the details.

= v140816 =

* (s2Member/s2Member Pro) **bbPress Forum Compatibility** Woohoo! This release of s2Member improves s2Member's compatibility with bbPress Forums/Topics/Replies. It is now possible to protect a Forum with s2Member, and have all Topics within that Forum protected automatically. No changes necessary to enable this feature. If you protect a bbPress Forum, this is how s2Member will behave automatically. It's a parent/child relationship that s2Member is now compatible with.

  *WARNING: If you have been running s2Member together with bbPress in the past, this change may impact you. Any bbPress Forums that are protected by s2Member will now also protect all Topics within that Forum. This improvement also impacts s2Member's Alt. View Restrictions. If you are using s2Member's Alt. View Restrictions, any bbPress Topics that live within a protected Forum will automatically be hidden from Alternative Views configured with s2Member.*

  Please see [this GitHub issue](https://github.com/websharks/s2member/issues/116) if you'd like more info.
* (s2Member Pro) **[s2Stream /] Resolutions** Awesome! This release introduces a new Shortcode Attribute (`player_resolutions=""`).  See [this screenshot](http://bit.ly/1uASNau) of the details and [this screenshot](http://bit.ly/1uASY5M) of the functionality.

  This is an s2Member Pro feature that allows a site owner to offer multiple resolutions of a video through the `[s2Stream /]` Shortcode implemented with s2Member's Download Restrictions. Please see [this KB article](http://www.s2member.com/kb/jwplayer-s2stream-shortcodes/#using-s2stream-shortcodes) and choose the **"Shortcode Attributes (Explained)" tab** for all the details, along with an example of `player_resolutions=""` in the `[s2Stream /]` Shortcode.

  See also: [this GitHub issue](https://github.com/websharks/s2member/issues/179) if you'd like more info.
* (s2Member Pro) **Authorize.Net** This release introduces a new configurable EOT Behavior option for site owners integrated with Authorize.Net. It is now possible to configure a Max Failed Payments threshold; after which s2Member will automatically trigger an EOT (End Of Term). See: `Dashboard → s2Member → Authorize.Net Options → EOT Behavior`. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/141) if you'd like more info.
* (s2Member/s2Member Pro) **Enhancement** A new Replacement Code (`%%current_user_nicename%%`) has been introduced by s2Member to improve compatibility with plugins like BuddyPress and bbPress. If you are currently using a Special Login Redirection URL as your s2Member Login Welcome Page, and you've used `%%current_user_login%%` (i.e., the old way), we suggest that you update your Special Redirection URL to use `%%current_user_nicename%%`. Please see [Jason's comments here](https://github.com/websharks/s2member/issues/276#issuecomment-51706582) for further details. See also: `Dashboard → s2Member → General Options → Login Welcome Page`. It is this area of your Dashboard where a Special Redirection URL can be configured.
* (s2Member Pro) **Pro Login Widget** There are some new Replacement Codes available for the "My Account" page URL, and the "My Profile" page URL whenever you configure the s2Member Pro Login Widget in WordPress. s2Member Pro now supports things like `%%current_user_nicename%%` and `%%current_user_level%%` in these customizable URLs. See: `Dashboard → Appearance → Widgets → s2Member Pro Login Widget` for further details. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/276#issuecomment-51706582) if you'd like more info.
* (s2Member Pro) **Stripe Bug Fix** Wrapping multiple Stripe Pro-Form Shortcodes together as "Checkout Options" was not working properly in the previous release. The dropdown for Checkout Options was not maintaining the underlying variable needed to keep a Checkout Option selected. Fixed in this release. If you'd like more info, please see [this GitHub issue](https://github.com/websharks/s2member/issues/296).
* (s2Member Pro) **Stripe Bug Fix** A few site owners reported issues between s2Member's integration with Stripe and other plugins that also depend on the Stripe SDK for PHP. Fixed in this release. If you'd like more info, please see [this GitHub issue](https://github.com/websharks/s2member/issues/295).
* (s2Member Pro) **Pro Cancellation Forms** This release introduces a new Shortcode Attribute that can be used with Pro Cancellation Forms. The new Shortcode Attribute is `unsub=""`. Setting this to a value of `unsub="1"` will enable an automatic unsubscribe upon cancellation. To clarify, this is related to any List Servers (e.g., MailChimp, AWeber, GetResponse) that you integrate with s2Member. If `unsub="1"` when a customer cancels future billing they will also be removed from the mailing list they are currently subscribed to, according to your List Server configuration in s2Member. Please see [this GitHub issue](https://github.com/websharks/s2member/issues/238) if you'd like more info.
* (s2Member/s2Member Pro) **Child Theme Compabitility** Portions of s2Member (e.g., Return-Page templates, s2Stream templates, Pro-Form templates, IP Restriction templates, and more) allow advanced site owners to use custom templates of their own. In the past these templates *had* to be created inside the parent theme directory or inside of your `/wp-content/` directory. Starting with this release, s2Member will also look for custom templates inside of your current Child Theme directory too (i.e., `get_stylesheet_dir()`). Please see [this GitHub issue](https://github.com/websharks/s2member/issues/271) if you'd like more info.
* (s2Member/s2Member Pro) **S3/CloudFront Compatibility** The latest release of s2Member has been made compatible with the latest changes at Amazon S3/CloudFront [regarding IAM users](http://aws.amazon.com/blogs/aws/updated-iam-console/). If you've been running s2Member together with Amazon S3/CloudFront there are no changes necessary in s2Member configuration. If you are just integrating s2Member with Amazon S3/CloudFront you are advised to setup an IAM user instead of using your AWS Root Keys. s2Member will continue to work with either Root Keys or with IAM user keys. Either are fine. If you'd like more info, please see [this GitHub issue](https://github.com/websharks/s2member/issues/297).
* (s2Member/s2Member Pro) **MySQLi Compatiblity** This release brings s2Member into full compatibility with the MySQLi extension. In the previous release it was reported that one specific routine in s2Member that checks the total number of users in your WordPress database was incompatible with MySQLi. Resolved in this release. See [this GitHub issue](https://github.com/websharks/s2member/issues/274) if you'd like more info.
* (s2Member/s2Member Pro) **Amazon CloudFront** s2Member now makes a new button [Reset CloudFront Configuration] available inside of your WP Dashboard under: `s2Member → Download Options → Amazon CloudFront`. This button allows a site owner (if necessary) to do a quick reset of s2Member's current integration with Amazon CloudFront Distributions. Please see [this GitHub issue](https://github.com/websharks/s2member/issues/270) if you'd like more info.
* (s2Member/s2Member Pro) **Bug Fix** This release corrects a bug in s2Member's log of a user's WordPress Capability access times. This bug had no serious impact on previous releases of s2Member. However, it was a bug that needed fixing nonetheless. Resolved in this release. Please see [this GitHub issue](https://github.com/websharks/s2member/issues/237) if you'd like more info.
* (s2Member/s2Member Pro) **Responsive Compatibility** This release of s2Member makes the `/wp-login.php` file (the WordPress Login/Registration system) Responsive; i.e., more compatible with mobile devices and tablets. This only impacts site owners that use s2Member's Login/Registration Design options to improve and customize the appearance of this core WordPress component. No changes necessary to existing installations for this to kick-in. It's automatic (assuming you are using this s2Member feature). Please see [this GitHub issue](https://github.com/websharks/s2member/issues/211) if you'd like more info.
* (s2Member Pro) **Remote Operations API** This release of s2Member makes it possible to change the API Key assigned to your WordPress installation. The Pro Remote Operations API is one way for developers to integrate with some of s2Member's functionality. See: `Dashboard → s2Member → API / Scripting → Remote Operations API`. This is where it's possible to change your API Key if you'd like to. Please see [this GitHub issue](https://github.com/websharks/s2member/issues/206) if you'd like more info.
* (s2Member/s2Member Pro) **Enhancement** s2Member's Auto-Return system (i.e., default Thank-You page handler) which integrates with: PayPal Standard Buttons, ClickBank, and Google Wallet; has been updated in this release. If a customer happens to find their way back to a self-expiring Auto-Return URL (a rare occurrence); instead of an unfriendly error message about duplicate return data, s2Member now provides a more friendly note that asks the customer to check their email for the details needed to access what they paid for. Please see [this GitHub issue](https://github.com/websharks/s2member/issues/205) if you'd like more info.
* (s2Member/s2Member Pro) **Enhancement** s2Member's Security Encryption Key configuration panel in the Dashboard was updated to include additional details related to the use of your Security Encryption Key. This additional information explains s2Member's use of this key in greater detail. See: `Dashboard → s2Member → General Options → Security Encryption Key`. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/182) if you'e like more info.
* (s2Member Pro) **One-Time-Offers Upon Login** This release resolves a conflict between s2Member's Pro Login Widget and One-Time-Offers (Upon Login)—a feature that can be configured with s2Member Pro. One-Time-Offers (Upon Login) now take precedence over certain automatic login redirections that can occur through the Pro Login Widget, removing the chance of a conflict. If a visitor logs into their account with a default login redirection URL (i.e., a redirection URL formulated dynamically by the Pro Login Widget that is not related to a visitor's request to access a specific page of the site), and a One-Time-Offer is triggered at the same time, the One-Time-Offer will take precendence. The visitor will see the One-Time-Offer instead of being redirected to the default location specified by the Pro Login Widget. Please see [this GitHub issue](https://github.com/websharks/s2member/issues/119) if you'd like more info.

= v140725 =

* (s2Member Pro) **NEW: Stripe Pro-Forms (Beta)** Holy hula hoop! s2Member now has a deep integration with Stripe for on-site credit card processing that uses a Stripe overlay. Stripe is an almost-free service that is super easy to setup and work with. We think you'll find that Stripe is quickly becoming the MOST popular of all payment gateways in the industry. A few bugs have [already been zapped](https://github.com/websharks/s2member/issues?milestone=5&page=1&state=closed) prior to this release after some initial beta testing was completed over the last few weeks. Everything is looking great so far, but please do [report any new issues via GitHub](https://github.com/websharks/s2member/issues?page=1&state=open).

  **If you are an s2Member Pro site owner** you can upgrade to the latest version of s2Member Pro at anytime you like; then enable Stripe as an additional payment gateway option. See: `Dashboard → s2Member Pro → Other Gateways`.

  **Questions About Stripe?** Please watch [this video](http://www.s2member.com/videos/L0aJz4-9mNanxemFZ_3G2-SIn-xAoiwD/) by Lead Develoer Jason Caldwell regarding s2Member's new integration with Stripe payment processing. It's never been easier! Jason answers several questions about Stripe in this video also. You might _also_ find it interesting to hear the [latest news regarding Stripe and Bitcoin](https://stripe.com/blog/bitcoin-the-stripe-perspective). We look forward to supporting Bitcoin in s2Member (via Stripe) very soon.

* (s2Member Pro) **NEW: Advanced Import/Export Tools Option** This release introduces a new "Advanced" set of user import/export tools. We think you'll find this to be an extremely helpful and much more powerful way to deal with user import/export in WordPress. Please see `Dashboard → s2Member → Import/Export` and click the link to enable the new Advanced Import/Export Tools. See also: [this KB article](http://www.s2member.com/kb/advanced-import-tools/) which documents the new tools. See also: [this GitHub issue](https://github.com/websharks/s2member/issues/149) if you'd like further details about the development behind this new feature.
* (s2Member Pro) **ClickBank Bug Fix** Nillable fields causing some issues (only under one specific scenario) after a recent change in the ClickBank API. Fixed in this release. See [this GitHub issue](https://github.com/websharks/s2member/issues/250) if you would like further detais.
* (s2Member Pro) **Compatibility** Resolved a minor single-quote issue in the Visual Editor. Please see [this GitHub issue](https://github.com/websharks/s2member/issues/228) if you'd like further details.
* (s2Member/s2Member Pro) **Bug Fix** Non-HTML whitespace being trimmed inside the `[s2If /]` shortcode. Resolved in this release. Please see [this GitHub issue](https://github.com/websharks/s2member/issues/224) if you'd like further details.
* (s2Member Pro) **Pro-Forms Bug Fix** Related to List Server processing during an upgrade. This bug impacted all payment gateways integrated with s2Member's Pro-Forms; including PayPal, Authorize.Net and now Stripe. Resolved in this release. Please see [this GitHub issue](https://github.com/websharks/s2member/issues/216) if you'd like further details.
* (s2Member Pro) **Bug Fix** An `array_intersect()` error in the `[s2MOP /]` shortcode under the right conditions. Fixed in this release. Please see [this GitHub issue](https://github.com/websharks/s2member/issues/204) if you'd like further details.
* (s2Member Pro) **Enhancement** Free Registration Pro-Forms can now be included in a list of nested Checkout Option drop-downs. Please see [this GitHub issue](https://github.com/websharks/s2member/issues/197) if you'd like further details. See also: `Dashboard → s2Member → [Your Payment Gateway] Pro-Forms → Checkout Options`; where there is more information about how to use Checkout Options with s2Member Pro-Forms.
* (s2Member/s2Member Pro) **Bug Fix / List Servers** This release corrects a bug that may cause members to be unsubscribed from a List Server if they forget to check the box again while editing their profile. In the previous release we introduced a feature that allows the checkbox to be pre-checked if the user already chose to subscribe once before. However, the internal tracking for this was not as reliable as it could be. There is still more work to be done on this front, but the immediate issue has now been resolved. Please see [this GitHub issue](https://github.com/websharks/s2member/issues/196) if you'd like further details.
* (s2Member/s2Member Pro) **WP_DEBUG Compat.** Some older portions of s2Member's codebase were cleaned up and reformatted in this update. This is an ongoing process to bring s2Member into full compatibility with `WP_DEBUG` mode in WordPress. This is intended to help other developers in the WP community. Much progress has been made on this front now, but still some more work to do. We will continue to update s2Member's codebase little-by-little with each release.
* (s2Member/s2Member Pro) **Uninstall vs. Deactivation** s2Member has always made a strong effort to cleanup after itself should you decide (for whatever reason) to uninstall it. However, we are also very concerned with preserving any data associated with such a powerful plugin that comes with so many options. Nobody wants accidental data loss, right!?


  In the past, s2Member came with a feature called "Deactivation Safeguards". These (if disabled) attached themselves to a plugin "deactivation" hook within WordPress and they would uninstall s2Member when you deactivated the plugin. Starting with this release ,s2Member's Deactivation routines have been changed. We now attach them to the WordPress "uninstall" hook—which is triggered only on plugin deletion.

  In this way, deactivating s2Member will never result in a loss of any data. Instead of "Deactivation Safeguards", s2Member now calls this feature "Plugin Deletion Safeguards". In short, to tell s2Member to uninstall itself (including any data/options associated with s2Member), you can simply disable s2Member's Plugin Deletion Safeguards under: `s2Member → General Options`, and then deactivate _and delete_ the s2Member plugin entirely. Whenever you delete the plugin this automatically and silently triggers s2Member's uninstaller which cleans up after itself nicely :-)

= v140630 =

- (s2Member Pro) **Stripe Payment Gateway** Hooray! s2Member Pro now integrates with [Stripe](http://www.s2member.com/r/stripe). s2Member Pro-Forms, Coupon Codes, Tax Settings, Checkout Options, and all of the other great features provided by s2Member Pro are now compatible with Stripe. To enable Stripe in your installation of s2Member, please see: `Dashboard → s2Member → Other Gateways → Stripe`.

  We expect Stripe to become the most popular payment gateway integration for s2Member Pro over the next few months. Stripe is nearly free; it's easier to setup, easier to maintain; and just more flexible overall in our opinion.

  _See also: [this GitHub issue](https://github.com/websharks/s2member/issues/177) where  efforts to integrate with Stripe took place. A quick tutorial video and KB articles will come soon at s2Member.com; once beta testing is complete._
- (s2Member Pro) **Bug Fix; [s2MOP /]** This release corrects a bug in the `[s2MOP /]` `restriction_type` attribute. See [this GitHub issue](https://github.com/websharks/s2member/issues/204) if you'd like further details.

= v140614 =

* (s2Member/s2Member Pro) **Quick Cache Compat.** This release makes it possible for Quick Cache to cache content protected by s2Member. If, and only if, you have [Quick Cache Pro](http://www.websharks-inc.com/product/quick-cache/) configured to enable user-specific caching; i.e., to cache when users are logged into the site. See [this GitHub issue](https://github.com/websharks/s2member/issues/172) if you'd like further details.
* (s2Member Pro) **ClickBank API Compat.** This release brings s2Member's ClickBank integration into full compatibility with a recent change in the ClickBank API. Under the right conditions, a symptom of this bug was to see an error regarding an unexpected `txnType` upon returning from checkout via ClickBank. Resolved in this release. See [this GitHub issue](https://github.com/websharks/s2member/issues/199) if you'd like further details.

= v140603 =

* (s2Member/s2Member Pro) **Profile Sync to List Servers** If you have a supported List Server integrated with s2Member (e.g., MailChimp, GetResponse, AWeber), the opt-in checkbox will now also be displayed in user profile editing panels (i.e., by the `[s2Member-Profile /]` shortcode, or if you integrate s2Member with BuddyPress profiles).

  If a user updates their profile, their profile on the List Server is updated too (i.e., s2Member updates their first name, last name, along with any merge vars or customs you've integrated through s2Member hooks/filters). If a user changes their email address, they will be subscribed with the new email address. Leaving the box unchecked during a profile update will effectively unsubscribe the user from the lists you have configured at their current Membership Level.

  *s2Member's AWeber integration does not yet support profile updates (i.e., changes in first/last name or other details); it only supports the ability to either subscribe or unsubscribe; and/or changes in email address.*

  See [this GitHub issue](https://github.com/websharks/s2member/issues/146) if you'd like further details.
* (s2Member/s2Member Pro) **BuddyPress Compatibility** This release makes s2Member and s2Member Pro compatible with the latest release of BuddyPress. BuddyPress v2.01 broke some of s2Member's previous integration. Fixed in this release. See [this GitHub issue](https://github.com/websharks/s2member/issues/162) if you'd like further details.
* (s2Member/s2Member Pro) **Multisite Lost Password URL** In a multisite environment (given the WordPress default behavior), a lost password URL generated by WordPress will force all users to recover their password from the Main Site in the Network; which is usually NOT desirable. This release of s2Member fixes this odd behavior in the WordPress core by allowing users to recover their password in the UI for the current Child Blog they are accessing. See [this GitHub issue](https://github.com/websharks/s2member/issues/138) if you'd like further details and ways to enable/disable; or even customize this further.
* (s2Member/s2Member Pro) **Default EOT Behavior** By popular demand, this release changes s2Member's default EOT (End Of Term) Behavior option with respect to refunds/chargebacks. The new default behavior (assuming you have not yet configured s2Member) is to force an EOT on chargebacks only; not on a refund of any kind. A refund is just a refund (in many cases) and an EOT (if one should occur) is something that a site owner generally should decide on their own; i.e., to review refunds manually and if an EOT should occur, the site owner will mostly like prefer to terminate a user's account access on their own; and on a case-by-case basis.

  **Further clarification...** EOT (End Of Term) is meant to occur when a subscription ends, and since a refund doesn't necessarily end a subscription, it's not the default anymore to trigger an automatic EOT on a refund event. Site owners that want an EOT on refunds still have that option available to them however. See `Dashboard → s2Member → [Payment Gateway] Options → Auto EOT Behavior → Chargebacks/Refunds`.

  See also: [this GitHub issue](https://github.com/websharks/s2member/issues/183) if you'd like further details.
* (s2Member/s2Member Pro) **Currency Conversion** s2Member's integration with the Google Currency Converter went stale after some changes to the Google API. This release updates s2Member's internal currency conversion routines to correct the issue. See [this GitHub issue](https://github.com/websharks/s2member/issues/169) if you'd like further details.
* (s2Member/s2Member Pro) **Bug Fix** This release corrects a sortable User column issue in the WordPress Dashboard; with respect to numeric values stored in the WordPress meta table. A symptom of this bug was to see sortable columns for Last Login Time, Total Logins, or EOT Time just a bit out of whack in some scenarios. Fixed in this release. See [this GitHub issue](https://github.com/websharks/s2member/issues/164) if you'd like further details.
* (s2Member/s2Member Pro) **Bug Fix** Updating the inline documentation for the `[s2Drip /]` shortcode in the Dashboard to match the most recent improvements to this feature; and to bring it up-to-date with [this KB article](http://www.s2member.com/kb/s2drip-shortcode/). See [this GitHub issue](https://github.com/websharks/s2member/issues/163) if you'd like further details.
* (s2Member Pro) **Enhancement** This release enhances the UI and error reporting for the `[s2Member-List /]` shortcode. See [this GitHub issue](https://github.com/websharks/s2member/issues/161) if you'd like further details. See also [this KB article](http://www.s2member.com/kb/s2member-list-shortcode/).
* (s2Member Pro) **Bug Fix** This release corrects an issue with the `[s2Member-List /]` shortcode when used on a site that does not use fancy permalinks. See [this GitHub issue](https://github.com/websharks/s2member/issues/160) if you'd like further details.
* (s2Member/s2Member Pro) **Bug Fix** This release adds width/height attributes to the image tags used in the s2Member Security Badges that display on-site (if you enable them); allowing them to pass a W3C validation. See [this GitHub issue](https://github.com/websharks/s2member/issues/157) if you'd like further details.
* (s2Member Pro) **Logging Enhancement** This release improves s2Member's log files with respect to Recurring Profiles created through PayPal Pro (Payflow Edition) and/or Authorize.Net. s2Member's automatic polling routines will now log scenarios where a user's account record is missing the original IPN Signup Vars that should be present on a site running s2Member Pro. See [this GitHub issue](https://github.com/websharks/s2member/issues/104) if you'd like further details.

= v140520 =

* (s2Member Pro) **`[s2Member-List /]` Shortcode** Amazing new feature! It is now possible to list members using a powerful shortcode, and even make it possible for members to view and search for each other. See [this KB article](http://www.s2member.com/?p=62860) for all the details on this feature. Very cool!
* (s2Member/s2Member Pro) **Server-Side Validation** For Registration/Profile Fields that you configure on your own (using the s2Member software), there is now support within all areas of the s2Member codebase for both JavaScript and *now server-side validation too*. In the past, all validations applied to custom fields was via JavaScript only. With server-side validation too, now it is impossible for required and/or invalid fields that you configure to go missing. This also resolves a few issues related to spam bots attempting to bypass JavaScript validation. See [this GitHub issue](https://github.com/WebSharks/s2Member/issues/99) if you'd like further details.
* (s2Member Pro) **Button Processing Animation**. Improving the processing animation used in submit buttons across Pro-Forms. Instead of a script-based solution (rather jumpy), we are now taking advantage of CSS3 for a much smoother animation. See [this GitHub issue](https://github.com/WebSharks/s2Member/issues/114) if you'd like further details.
* (s2Member Pro) **`[s2Drip /]` Shortcode Enhancement** This release adds support for a new `access=""` shortcode attribute that can parse `and` / `or` logic. Also, it is now possible for `[s2Drip /]` to be used with Custom Capabilities too! See [this KB article](http://www.s2member.com/kb/s2drip-shortcode/) for all the details.
* (s2Member/s2Member Pro) **Bug Fix, Custom Fields UI** This release corrects a bug related to the `jquery/.htaccess` file that ships with s2Member. A symptom was to have issues with the Registration/Profile Fields UI and find a JS error in the browser's developer console. Fixed in this release. See also, [this GitHub issue](https://github.com/WebSharks/s2Member/issues/144#issuecomment-43198045) if you'd like further details.
* (s2Member Pro) **Bug Fix, Clickbank HTTPS** This release corrects a bug in the default Auto-Return Page for ClickBank, when/if it's served over the `https` protocol. See [this GitHub issue](https://github.com/WebSharks/s2Member/issues/145) if you'd like further details.
* (s2Member/s2Member Pro) **Sortable User Columns** This release adds support for sortable user columns in the Dashboard, where possible. Things like EOT Time, Registration Time, Last Login Time, Total Logins, etc. NOTE: it is currently NOT possible to sort by Custom Registration/Profile Fields (yet). See [this GitHub issue](https://github.com/WebSharks/s2Member/issues/147) if you'd like further details.
* (s2Member/s2Member Pro) **Hook Priority for Translations** This release corrects a bug related to Gettext translations. There were a couple of areas within s2Member that weren't picking up all of the translation entries; caused by a conflict in hook priority. Fixed in this release. See [this GitHub issue](https://github.com/WebSharks/s2Member/issues/132) if you'd like further details.
* (s2Member Pro) **`[s2MOP /]` Shortcode Enhancement** A new Replacement Code was added: `%%REQUIRED_LEVEL_LABEL%%`. See [this KB article](http://www.s2member.com/kb/s2mop-shortcode/) and [this GitHub issue](https://github.com/WebSharks/s2Member/issues/129) if you'd like further details.

= v140423 =

* (s2Member/s2Member Pro) **WP v3.9 Compatibility**. Resolving an incompatibility between s2Member and WP v3.9 where s2Member was attempting to use the jQuery `highlight` effect no longer available by default; starting with WP v3.9. A symptom of this bug was to have problems closing the dialog box when creating new Registration/Profile Fields with s2Member in the WP Dashboard. See also [this GitHub issue](https://github.com/WebSharks/s2Member/issues/108).
* (s2Member Pro) **User Export Limitation**. Adding an option to the Import/Export panel used to export Users from your installation of WordPress. This new option makes it possible to specify an exact number of maximum rows to export; instead of the previous behavior which forced to a value of `1000` max. See also [this GitHub issue](https://github.com/WebSharks/s2Member/issues/80).
* (s2Member Pro) **s2Drip Enhancement**. Updating the s2Drip shortcode to allow for a time frame that specifies `from_day="1" to_day="1"`. See also [this GitHub issue](https://github.com/WebSharks/s2Member/issues/103). For instructions on how to use the `[s2Drip]` shortcode see [this KB article](http://www.s2member.com/kb/s2drip-shortcode/).
* (s2Member Pro) **s2Drip Enhancement**. Adding support for nested shortcodes inside the `[s2Drip]` conditional shortcode tags. See [this commit](https://github.com/WebSharks/s2Member-Pro/commit/3d042250736a074136924afd5b4030065aff881e) for detailed changes. For instructions on how to use the `[s2Drip]` shortcode see [this KB article](http://www.s2member.com/kb/s2drip-shortcode/).
* (s2Member Pro). **s2MOP Enhancement**. Adding an optional `%%POST_EXCERPT%%` Replacement Code to the `[s2MOP]` shortcode. For further details see [this GitHub issue](https://github.com/WebSharks/s2Member-Pro/pull/11). For instructions on how to use the `[s2MOP]` shortcode see [this KB article](http://www.s2member.com/kb/s2mop-shortcode/).
* (s2Member Pro). **s2MOP Enhancement**. Improving `[s2MOP]` Replacement Codes overall by converting internal slugs into textual labels for an improved user experience. Also making it possible to filter the default labels. See [this GitHub issue](https://github.com/WebSharks/s2Member-Pro/pull/10). For instructions on how to use the `[s2MOP]` shortcode see [this KB article](http://www.s2member.com/kb/s2mop-shortcode/).
* (s2Member Pro) **s2MOP Enhancement**. Adding support for nested shortcodes inside the `[s2MOP]` shortcode. For instructions on how to use the `[s2MOP]` shortcode see [this KB article](http://www.s2member.com/kb/s2mop-shortcode/).
* (s2Member/s2Member Pro) **Time Tracking**. This release begins tracking some additional timestamps to be used by features coming in a future version of s2Member. A new routine was added internally to go ahead and begin tracking some additional timestamps associated with the addition and/or removal of specific Membership Levels and/or Custom Capabilities. A future release of s2Member will take advantage of this data in some of it's shortcodes and API Functions (coming soon). See also [this GitHub issue](https://github.com/WebSharks/s2Member/issues/84).
* (s2Member/s2Member Pro) **Bug Fix**. A bug related to domain validation against some of the latest TLDs like `.photography` or `.solutions` has been resolved with this release. See also [this GitHub issue](https://github.com/WebSharks/s2Member/issues/98).
* (s2Member) **Bug Fix**. The declaration `function ksort_deep` should be `public static function ksort_deep`. Fixed in this release.
* (s2Member/s2Member Pro) **Bug Fix**. s2Member preventing some fields from making it into results provided by `WP_User_Query` on the front-end of a site utilizing this core class. Fixed in this release. See also [this GitHub issue](https://github.com/WebSharks/s2Member/issues/95).
* (s2Member Pro). **Authorize.Net Trial Limitation**. Updating s2Member Pro to support a 100% free trial period of any length when integrating with Authorize.Net. See also [this GitHub issue](https://github.com/WebSharks/s2Member/issues/106).
* (s2Member/s2Member Pro) **WP_DEBUG Compatibility**. Resolving several `E_NOTICE` level messages in an ongoing effort to satisfy `WP_DEBUG` mode. For further details, please see [this GitHub issue](https://github.com/WebSharks/s2Member/issues/97). See also [issue #105](https://github.com/WebSharks/s2Member/issues/105). See also [issue #91](https://github.com/WebSharks/s2Member/issues/91).

= v140409 =

* (s2Member Pro) **s2MOP Shortcode**. A new shortcode is now available for site owners running s2Member Pro: `[s2MOP]`. For further details, please check your Dashboard under: `s2Member → API / Scripting → Membership Options Page / Variables`. See also: [this KB article](http://www.s2member.com/kb/s2mop-shortcode/).
* (s2Member/s2Member Pro) **Compatibility**. Reviewed by Lead Developer Jason Caldwell for full compatibility against WordPress v3.9. s2Member also remains backward compatible with WP v3.3 - 3.8.
* (s2Member/s2Member Pro) **Security Review**. Reviewed by Lead Developer Jason Caldwell to look closer at any portions of s2Member which might be impacted by the [OpenSSL Heartbleed bug](http://heartbleed.com/). Please note, the Heartbleed bug is NOT an s2Member bug. It is a bug in the OpenSSL library used by many services across the web. Please see this [GitHub issue](https://github.com/WebSharks/s2Member/issues/90) where Jason posted a few tips for site owners running the s2Member software.
* (s2Member/s2Member Pro) **s2 MOP Vars**. The format of s2Member's MOP Vars has been updated in this release. Backward compatibility remains for the older formats, so this should not cause any problems for site owners using the older formats provided by previous versions of s2Member. For further details, please check your Dashboard under: `s2Member → API / Scripting → Membership Options Page / Variables`.
* (s2Member/s2Member Pro) **s2 MOP Vars**. s2Member MOP Vars are now an optional feature. It is now possible to disable the additional variables that s2Member appends to the end of your Membership Options Page URL when it redirects a visitor without access to something you've restricted. To configure this new option, please check your Dashboard under: `s2Member → General Options → Membership Options Page`.
* (s2Member/s2Member Pro) **Bug Fix**. Updating core IPN handler to correct a PHP warning `array to string conversion`. A symptom was to see warnings in your PHP error log when using a custom Thank-You page. Fixed in this release.
* (s2Member/s2Member Pro) **Bug Fix**. Updating the new EOT Time column in the list of WP Users so it displays a human readable date and time; as opposed to a UNIX timestamp. Fixed in this release.

= v140328 =

* (s2Member/s2Member Pro) **Compatibility**. Updated to support WordPress v3.9. Nothing significant, just minor UI tweaks to make s2Member fully compatible with WP v3.9.
* (s2Member) **Compatibility**. Updating for backward compatibility with WordPress v3.8 - 3.3 by tweaking calls to `get_post()`. See this [commit](https://github.com/WebSharks/s2Member/commit/7b8c8aecc3e8d0d0bada3e80f41615b968a763f8) for further details.
* (s2Member/s2Member Pro) **Translations**. Updating the `includes/translations/s2member.pot` [file](https://github.com/WebSharks/s2Member/tree/000000-dev/s2member/includes/translations) to include the entire set of translation strings for both s2Member and s2Member Pro. Some translation entries from JS files were missing in the previous release due to a glitch in our WP i18n processor. Fixed in this release.
* (s2Member) **Server Check Tool**. Updating the [s2Member Server Scanner](https://www.s2member.com/kb/server-scanner/) to exclude checksum validations against README files. This release includes other scanning improvements also, which allow the tool to do a better job of scanning for compatibility issues.
* (s2Member Pro) **GetResponse Integration**. This release adds support for GetResponse™ as an email service provider. s2Member now comes preintegrated with GetResponse™ (requires s2Member Pro), MailChimp® and AWeber™ too.
* (s2Member) **Double Opt-In Article**. Updating inline documentation to include further details about Double Opt-Ins via [this KB article](http://www.s2member.com/kb/double-opt-in-checkbox/).
* (s2Member) **Compatibility**. Adding support for `$_SERVER['WP_DIR']` (to help developers with a particular edge case). For further details please see [this GitHub issue](https://github.com/WebSharks/s2Member/issues/39).
* (s2Member) **Auto-EOT Time Column**. This release adds an Auto-EOT Time column to the list of Users in the WordPress Dashboard. Note: it is possible to show/hide specific columns with the Screen Options tab in WordPress.
* (s2Member) **HTML Trimming**. Adding a new utility method to the s2Member codebase. This [method](https://github.com/WebSharks/s2Member/commit/aa19d23511e9c34a5cc00628285107da9a565594) helps cleanup HTML-based whitespace (and extra line breaks) that are injected inadvertently by some themes when site owners use the `[s2If]` shortcode.
* (s2Member) **Bug Fix**. Fixing an issue related to Login Redirections over SSL. For further details please see [this GitHub issue](https://github.com/WebSharks/s2Member/issues/59).
* (s2Member) **All Custom Capabilities**. If your site offers many different CCAPS (Custom Capabilities) and you'd like a way to sell someone access to all of them at once (without needing to list each of them one-by-one); this is now possible. This is accomplished by selling a customer the special CCAP `all_ccaps`. If the `current_user_can('access_s2member_ccap_all_ccaps')`, they will be granted access to ALL Custom Capabilities that you've implemented on the site (now, and in the future). For instance, if the `current_user_can('access_s2member_ccap_all_ccaps')` they can also `access_s2member_ccap_music` even if they don't actually have CCAP `music`. In short, `all_ccaps` grants a customer access to all CCAPS automatically.
* (s2Member) **E_NOTICE**. Updating several areas of the s2Member codebase in an ongoing effort to make s2Member behave as expected when running in `WP_DEBUG` mode. Note that `WP_DEBUG` is NOT recommended for a production site (this mode is reserved for developers only please).
* (s2Member) **Bug Fix**. Fixing a bug related to redirections over SSL in an edge case. See [this GitHub commit](https://github.com/WebSharks/s2Member/commit/881a8f513ff00d1932f33928c771cab38ab84dc7) if you'd like further details.
* (s2Member) **Bug Fix**. Fixing a bug in the way s2Member handles IP Restrictions in one particular area of the source code. See [this GitHub issue](https://github.com/WebSharks/s2Member/issues/63) if you'd like further details.
* (s2Member) **File Downloads (Content-Encoding)**. Adding a new configurable option for site owners that use protected File Downloads with s2Member. There is a known issue on some hosting platforms; and this new configurable option provides a workaround that is related to the `Content-Encoding` header. Please see: `Dashboard → s2Member → Download Options → Preventing GZIP Conflicts` to configure this new setting. See also [this GitHub issue](https://github.com/WebSharks/s2Member/issues/70) if you'd like further details.
* (s2Member) **Apache Compatibility**. Updating s2Member's `.htaccess` files to provide support for the `Require` directive supplied by the `authz_core_module` in the most recent versions of Apache. s2Member also maintains backward compatibility for the `allow/deny` directives used in previous versions of Apache.
* (s2Member Pro) **Gateway-Specific Variables**. Adding gateway-specific variables for use in email templates configured with s2Member. For further details please see [this GitHub issue](https://github.com/WebSharks/s2Member/issues/25).
* (s2Member) **Cosmetics**. Updating s2Member's adminitrative menu pages to enhance their appearance in the most recent versions of WordPress. Nothing significant, just minor tweaks.
* (s2Member Pro) **PayPal IPNs**. Adding support for `txn_type` values: `merch_pmt`, `mp_signup`, and `mp_cancel` to enhance s2Member's support for Billing Agreements when integrated together with a PayPal Pro (Payflow Edition) account that processes Express Checkout transactions. This also resolves a bug related to cancellation IPN processing in a specific scenario. Fixed in this release.
* (s2Member Pro) **[s2If][else]**. Requires s2Member Pro. This release adds support for a new `[else]` condition when using the `[s2If]` shortcode to protect parts of your content. For examples, please see: `Dashboard → s2Member → API Scripting → Simple Shortcode Conditionals`. See also: `Dashboard → s2Member → Restriction Options → Simple Shortcode Conditionals`.
* (s2Member Pro) **[s2If php=""]**. Requires s2Member Pro. This release adds support for a new `php` Shortcode Attribute; e.g., `[s2If php=""]`. This makes it possible to use arbitrary PHP code in your `[s2If]` shortcodes. For examples, please see: `Dashboard → s2Member → API Scripting → Simple Shortcode Conditionals`. **Note: this feature is disabled by default.** You must enable the `php` attribute for this to work as expected. Please see: `Dashboard → s2Member → Restriction Options → Simple Shortcode Conditionals` (with s2Member Pro installed).
* (s2Member) **Bug Fix**. Correcting an issue related to Login Redirections and an E_NOTICE. Please see [this GitHub issue](https://github.com/WebSharks/s2Member/issues/75) if you'd like further details.
* (s2Member Pro) **[s2Drip]**. Requires s2Member Pro. This release adds support for a new easy-to-use shortcode that can drip content to paying Members based on their Membership Level. For examples, please see: `Dashboard → s2Member → API Scripting → Content Dripping`.
* (s2Member Pro) **ClickBank**. Adding support for the `vtid` parameter in ClickBank Button Shortcodes. See [this GitHub issue](https://github.com/WebSharks/s2Member/issues/44) if you'd like further details.
* (s2Member Pro) **PayPal Express Checkout**. Updating PayPal Express Checkout cancellation links so they will automatically use the PayPal Merchant ID supplied by a site owner (i.e., PayPal's recommended behavior). If you'd like further details please see [this GitHub commit](https://github.com/WebSharks/s2Member-Pro/commit/5efbe35eed352868a956c94e51ab09f8e561892a).
* (s2Member Pro) **Pro-Forms**. Adding a new filter for developers `s2member_pro_cancels_old_rp_before_new_rp`. This is true by default. If you'd like to prevent s2Member from terminating an existing Recurring Profile (before creating a new one); i.e., during an upgrade... you can set this to a FALSE value. Not recommended, but there are a few edge cases where it could be helpful for developers. See also [this GitHub commit](https://github.com/WebSharks/s2Member-Pro/commit/19a84c81070bb0e1869b5dbd9d0325cc458fd016).
* (s2Member Pro) **ClickBank Bug Fix**. Adding support for alphabetics in ClickBank Item Numbers. This bug impacted the ClickBank Button Generator only, it did not prevent alphabetics from being used in a raw Shortcode. Still, this has been resolved now. See also [this GitHub issue](https://github.com/WebSharks/s2Member/issues/46).
* (s2Member Pro) **PayPal Mobile Bug Fix**. This release corrects a bug related to the `MAXAMT` PayPal specification that is sent via Express Checkout on a mobile device. A symptom of this bug was to sometimes see PayPal's awkward default amount of `$25` when completing checkout on a mobile device. Fixed in this release.
* (s2Member Pro) **PayPal Express Checkout**. This release addresses an issue where a customer reaches PayPal.com with an item description that inadequately reads "Future payment" (i.e., missing the description provided by a site owner). PayPal's latest improvements have made it possible for s2Member to get this right. Resolved in this release.
* (s2Member/s2Member Pro) **Partial Refunds**. This release adds support for Partial Refunds in the s2Member EOT Behavior Options. See also [this GitHub issue](https://github.com/WebSharks/s2Member/issues/40).

= v140105 =

* (s2Member/s2Member Pro) **Compatibility**. Updated to support WordPress v3.8. Nothing significant, just minor UI tweaks in the Login/Registration Design for WordPress v3.8; e.g., `wp-login.php` got some minor improvements in this release of s2Member and s2Member Pro.
* (s2Member) **Compatibility**. PayPal's API for Subscription Cancellation Buttons was changed recently. PayPal now requires a Merchant ID instead of the site owner's email address. This issue has been causing an error when a user attempts to cancel a PayPal Subscription through an s2Member-generated PayPal Subscription Cancellation "Button" (i.e., this affects Buttons only, not Pro-Forms). Fixed in this release. Site owners using PayPal Buttons should update their PayPal Merchant ID for s2Member. Please see: `Dashboard → s2Member → PayPal Options → Account Details`.
* (s2Member/s2Member Pro) **Compatibility**. Default s2Member option value for CSS/JS Lazy Loading is now off instead of on; e.g., s2Member's CSS/JS JavaScript libraries are now loaded on every page by default. Lazy loading must now be enabled by a site owner as a performance enhancement (optional). For further details, please see: `Dashboard → s2Member → General Options → CSS/JS Lazy Loading`.
* (s2Member Pro) **Remote Operations API**. This release introduces two new API methods; `auth_check_user` and `get_user`. These methods (combined with those which already exist in s2Member Pro) now make s2Member Pro's Remote Operations API a pleasure to work with. For further details, please see: `Dashboard → s2Member Pro → API Scripting → Pro Remote Operations API`. Here you will find the API Key for your installation, along with several code samples.
* (s2Member/s2Member Pro) **Logging**. s2Member's core payment gateway processors now log to files `gateway-core-ipn.log` and `gateway-core-rtn.log`. These log file names were changed in this release. In previous versions of s2Member these log entries were kept inside `paypal-ipn.log` and `paypal-rtn.log`.
* (s2Member/s2Member Pro) **Updates**. The XML/RSS feed box for the most recent s2Member Updates (for site owners only; in the Dashboard); has been updated to our newest feed location at: `http://feeds.feedburner.com/s2member`.
* (s2Member/s2Member Pro) **Bug Fix**. s2Member should follow redirects in API calls to Amazon.com. See: <https://github.com/WebSharks/s2Member/issues/35> for further details.
* (s2Member/s2Member Pro) **Bug Fix**. By default, do not count login IP Restrictions against users who can `edit_posts`. See: <https://github.com/WebSharks/s2Member/issues/32> for further details.
* (s2Member/s2Member Pro) **Bug Fix**. Sleep offset to `10` seconds for `subscr_eot`. See <https://github.com/WebSharks/s2Member/issues/34> for further details.
* (s2Member/s2Member Pro) **Enhancement**. Updating the "s2" icon in the Dashboard to our most recent version.
* (s2Member Pro) **Bug Fix**. s2Member Pro now accepts `TOO MANY FAILURES` as an EOT response type status under PayPal Pro (Payflow Edition) accounts. This was previously causing a problem against newer PayPal Pro accounts (w/ the Payflow Edition); whereby some customers who were reaching Max Failed Payments were not being demoted properly in all cases. Fixed in this release.
* (s2Member/s2Member Pro) **Compatibility**. Adding support for `$_SERVER['HTTP_AUTHORIZATION']` when s2Member is used for Remote Auth file hosting. Some servers do not support `$_SERVER['PHP_AUTH_USER']`. Instead, s2Member can get the username/password by parsing them out of `$_SERVER['HTTP_AUTHORIZATION']` when/if necessary. Fixed in this release.
* (s2Member/s2Member Pro) **PHP Debug Notices.** Updating s2Member's source code to further prevent PHP debug notices when running in `WP_DEBUG` mode. This is part of an ongoing effort keep s2Member running smoothly in PHP strict mode; and to maintain conformity with WordPress standards.
* (s2Member Pro) **Bug Fix**. Define `abbr_bytes()` method as static to prevent issues during automatic upgrades of s2Member Pro. Please see <https://github.com/WebSharks/s2Member/issues/37> for further details.
* (s2Member/s2Member Pro) **Backward Compatibility.** Updating calls to `get_post()`. We're adding a `NULL` argument via `$null` to prevent warnings in older releases of WordPress where an argument was required; e.g., `$null = NULL; get_post($null)`.

= v131126 =

* (s2Member Pro) **Google Wallet.** s2Member Pro now supports [Google Wallet for Digital Goods](https://developers.google.com/commerce/wallet/digital/).

 In the past we offered support for Google "Checkout" (Google "Checkout" is no longer available as of November 2013). s2Member's previous integration with Google Checkout has now been fully updated to support Google "Wallet" for Digital Goods. To enable Google Wallet in your installation of s2Member Pro, please see: `Dashboard → s2Member Pro → Other Gateways → Google Wallet`. For setup instructions, please see: `Dashboard → s2Member Pro → Google Options`.

 At the time of this writing, there are no KB articles related to Google Wallet at s2Member.com (yet); these will come soon. Until then, you might find it helpful to review dev notes by Jason Caldwell (Lead Developer). Please see: <https://github.com/WebSharks/s2Member/issues/19>.

 We also suggest that you review the documentation in your Dashboard under: `s2Member Pro → Google Options` and `s2Member Pro → Google Buttons`. If you were previously using Google "Checkout", please review [this notice posted by Google](https://support.google.com/checkout/sell/answer/3080449?hl=en).
* (s2Member/s2Member Pro) **Bootstrap Compatibility.** s2Member has been updated throughout to support the [Twitter Bootstrap](http://getbootstrap.com/) CSS framework when running on a WordPress theme that's been built on Bootstrap. For instance; profile editing forms, custom registration/profile fields, s2Member Pro-Forms for checkout/registration; these will now look good on sites powered by Bootstrap. This change has no impact on functionality, only on appearance; and only IF your site is powered by Bootstrap. Throughout s2Member's HTML code we've added CSS class names that follow a Bootstrap standard. These Bootstrap classes are blended together with default structural styles that makes s2Member compatible with all WordPress themes. This way s2Member (and s2Member Pro) can produce a clean/professional appearance on just about any WordPress theme; and now Bootstrap is supported too!
* (s2Member Pro) **Configurable Emails.** We've added new configuration panels into the s2Member UI for a Modification Confirmation Email and also for the Custom Capability Confirmation Email. These are now configurable for all payment gateways integrated with s2Member Pro; e.g., AliPay, ccBill, ClickBank, Google Wallet, Authorize.Net, PayPal Standard, and PayPal Pro. These emails have always existed, but up until now customization required a WordPress filter. Now it's easier, you can customize these from the Dashboard! Please note: this feature comes only with s2Member Pro. As one example, please check your Dashboard under: `s2Member Pro → PayPal Options → Modification Confirmation Email`.
* (s2Member/s2Member Pro) **Snippets/Redirects.** Snippets and Redirects no longer carry the s2Member Restriction Options meta box in the Post/Page editing station; there's no need for Restrictions against these two special Post Types. This change, together with the latest improvements in these two plugins: [WP Snippets](http://wordpress.org/plugins/wp-snippets/) and [WP Redirects](http://wordpress.org/plugins/wp-redirects/) (also produced by our team) offer a more powerful solution now that all of these plugins are more compatible with each other.
* (s2Member/s2Member Pro) **Backward Compatibility.** Updating calls to `get_post()`. We're adding a `NULL` argument to prevent warnings in older releases of WordPress where an argument was required; e.g., `get_post(NULL)`.
* (s2Member/s2Member Pro) **Forward Compatibility.** Adding support for the `relative` scheme in SSL filters that deal with `set_url_scheme()` in the latest versions of WordPress. This improves s2Member's "force SSL mode" where a site owner sets the Custom Field for a Post/Page; e.g., `s2member_force_ssl` is set to `yes`. This change will better support themes/plugins that use absolute relative paths together with WordPress core functions like `site_url()` and `home_url()`.
* (s2Member/s2Member Pro) **PHP Debug Notices.** Updating s2Member's source code to further prevent PHP debug notices when running in `WP_DEBUG` mode. This is part of an ongoing effort keep s2Member running smoothly in PHP strict mode; and to maintain conformity with WordPress standards.
* (s2Member Pro) **Unlimited Membership Levels.** Updating the built-in software documentation for s2Member Pro to cover an edge case where a site owner many choose to exceed the recommended maximum for Membership Levels when running s2Member Pro. If you intend to use more than `100` Membership Levels (this is not recommended); but if you do, please see: `Dashboard → s2Member Pro → General Options → Membership Levels/Labels` for the latest details on this.
* (s2Member/s2Member Pro) **Dropping IE8 Support.** s2Member and s2Member Pro will no longer support IE8 in any official capacity. s2Member's HTML output and CSS files have been cleaned up; and all hacks related to IE8 have been removed. Out with the old, in with the new! We need to keep s2Member up-to-date with the latest improvements offered by IE9 and other modern browsers. While s2Member may continue to function relatively well in IE8, hacks used in the past to accomodate edge cases in this buggy browser have been removed in favor of standards compliance.
* (s2Member/s2Member Pro) **Lazy Loading CSS/JS.** s2Member now offers site owners the option to enable/disable lazy loading of CSS/JS libraries provided by the s2Member software. For further details, please see: `Dashboard → s2Member → General Options → CSS/JS Lazy Loading`.
* (s2Member/s2Member Pro) **Bug Fix.** s2Member and s2Member Pro have both been updated to prevent spaces in a comma-delimited list of Custom Capabilities; e.g., `ccaps="music, videos"` should be `ccaps="music,videos"` please. Spaces in this list have never been allowed, but now there is better server-side validation to prevent this from happening; reducing the chance of error when a site owner configures a Button or Pro-Form shortcode with s2Member.
* (s2Member/s2Member Pro) **Other Minor Bug Fixes.** Please see: <https://github.com/WebSharks/s2Member/commits/000000-dev>

= v131109 =

* (s2Member/s2Member Pro) **UI Makeover** This release of s2Member upgrades all administrative UI panels.
* (s2Member/s2Member Pro) **Compatibility** Updating s2Member for compatibility with the coming release of both Quick Cache LITE and Quick Cache Pro for WordPress. These are not available publicly yet, but they are expected for release very soon. This release of s2Member is compatible with both the current and future releases of Quick Cache for WordPress.
* (s2Member/s2Member Pro) **Improvement (Speed)** Lazy load s2Member's JS file at all times. Done, this release will speed your site up for first-time visitors.
* (s2Member/s2Member Pro) **Improvement (Speed)** Lazy load s2Member's CSS file at all times. Done, this release will speed your site up for first-time visitors.
* (s2Member/s2Member Pro) **Improvement (Speed)** Load s2Member's JS library in the footer if at all possible (instead of the `<head>`). Done, this will improve the speed of your site for first-time visitors.
* (s2Member/s2Member Pro) **Debug Notices** Resolved all of the most obvious PHP notices when running s2Member in debug mode. This improvement impacts developers only.
* (s2Member/s2Member Pro) **Password Strength Meter** Removed dependence on `password-strength-meter` (a JavaScript library) from the WordPress core. This was causing some SSL issues for site owners. In the past it was necessary for s2Member to load an additional JS resource for registration/checkout and Pro-Forms (`password-strength-meter`). Starting with this release, s2Member handles password strength meters all by itself, thereby avoiding the additional overhead; and also the issues associated with this core functionality over SSL pages. Fixed in this release.
* (s2Member/s2Member Pro) **Mobile Devices** The s2Stream shortcode (for protected audio/video files) was updated to better support mobile device playback. See also: <http://www.s2member.com/kb/jwplayer-s2stream-shortcodes/#s2stream-mobile-devices>.
* (s2Member Pro) **Checkout Options** Improving support for multiple Checkout Options. When a customer changes to a new Checkout Option by selecting an option from the drop-down menu in a Pro-Form; this action will now result in a hash jump back to the location of the Pro-Form on any given page; instead of to the top of the page, which could potentially result in a confusing experience on some sites (depending on the implementation). Aside: for developers, it's helpful to know that all s2Member Pro-Forms now have a hashable ID `#s2p-form` that is cleaner than the longer (product-specific) IDs associated with Pro-Forms; e.g., `s2member-pro-paypal-form...`, etc. If you need to hash a Pro-Form, please use the more general `#s2p-form` on the end of a URL. This will take a customer directly to that Pro-Form in the context of any given page.

= v131026 =

* (s2Member) **WordPress v3.7 Compatibility** s2Member further updated to support subtle changes in the WordPress v3.7 `wp-login.php` file. This release corrects a minor issue w/ patches applied by s2Member when running in a Multisite Network environment. If you are running s2Member on a Multisite Network, please be sure to run the automatic patcher provided in your Dashboard against WordPress v3.7 after updating to this release of s2Member.
* (s2Member Pro) **PayPal Central IPN** Updating the example file: `s2m-pro-extras/paypal-central-ipn.php` to use one of the latest PayPal IP addresses listed [here](https://ppmts.custhelp.com/app/answers/detail/a_id/92).

= v131025 =

* (s2Member) **WordPress v3.7 Compatibility** s2Member updated to support WordPress v3.7. s2Member remains compatible with WP v3.3 (or higher).
* (s2Member Pro) **New Feature: Simultaneous Login Monitoring** Available only w/ s2Member Pro. s2Member Pro has been updated to support configurations that limit the number of simultaneous logins a single username can receive. For further details, please see: `Dashboard → s2Member → Restriction Options → Simultaneous Login Restrictions` (when s2Member Pro is installed).
* (s2Member) **Post Restrictions (#3)** Adding support for `all-[post type]` in addition to the existing `all-[post type]s` (plural) currently supported by s2Member's Restriction Options for Posts. This makes it possible for a site owner to type only the Post Type after the keyword prefix `all-`; and excluding the plural `s` in cases when this is necessary.
* (s2Member) **Documentation Update (#3)** Adding note in Download Options panel regarding `raw` shortcode tags around Shortcodes when using the inFocus theme. See also [this thread](http://mysitemyway.com/support/topic/infocus-adding-tags-into-plugin-content) for further details.
* (s2Member) **Documentation Cleanup (#12)** General cleanup in several s2Member panels to improve inline documentation that comes w/ the software. Branding improvements, padding adjustments, and subtle textual changes.
* (s2Member) **Bug Fix (#11)** Running `isset()` against `$cache_needs_updating` to prevent NOTICE when running in `WP_DEBUG` mode.
* (s2Member) **Optimization (#9)** Removing all image source files (.fla and .pspimage) from the official distribution package to reduce overall filesize.
* (s2Member) **Quick Start Video (#10)** Adding Quick Start playlist to Quick Start section.
* (s2Member) **UI (#6)** Reducing padding around section headers in s2Member option panels to reduce the amount of space these consume.
* (s2Member Pro) **SSL** Forcing all automatic updates of s2Member Pro to occur over SSL for improved security.
* (s2Member) **Login Welcome Page** Improving support for new feature (force HTTP redirection). See: `s2Member → General Options → Login Welcome Page` for details on how this feature works.
* (s2Member) **Bug Fix: Registration/Profile Fields** Adding space between checkboxes and their labels.
* (s2Member) **ClickBank IPN Filter** Adding a new filter to s2Member's ClickBank IPN handler for developers integrating s2Member in creative ways: `c_ws_plugin__s2member_pro_clickbank_notify_handles_completions`. Defaults to a TRUE value. Forcing this filter to a FALSE value will prevent s2Member from handling term completions via IPN communication; in cases where it's preferred that a site owner deal with this specific scenario manually.
* (s2Member) **Conformity** Updating calls to `$wpdb->escape` changing to `esc_sql` to conform w/ WordPress standards.
* (s2Member Pro) **Compatibility: Checkout Options** Improving theme support for Checkout Options created using Pro-Forms by wrapping other Pro-Form Shortcodes. Some site owners reported line break injections in the previous verison. Fixed in this release.
* (s2Member) **See also: s2Member Repo** https://github.com/WebSharks/s2Member/commits/000000-dev

= v130816 =

* (s2Member Pro) **Compatibility, ClickBank (#467)** Improving support for ClickBank PitchPlus Upsell Flows. Please see [this thread](http://www.s2member.com/forums/topic/clickbank-buttons-not-working/#post-55725) for further details.
* (s2Member/s2Member Pro) **User Search on Multisite Networks (#468)** User search functionality was partially broken for Child Blogs in a Multisite Network after some improvements were implemented in s2Member® v130731. The issue has now been corrected in this release for Multisite Networks. For further details, please see [this thread](http://www.s2member.com/forums/topic/user-search-no-longer-working/#post-55778).
* (s2Member/s2Member Pro) **Z-Index in Menu Pages (#461)** Stacking order against a WordPress® installation running a Dashboard with a collapsed sidebar menu (left side) was causing some UI problems. Fixed in this release.
* (s2Member/s2Member Pro) **SSL Compatibility (#437)** Adding a new option in the `s2Member® → General Options → Login Welcome Page` section. The default value for this new option is always `yes`. However, the default functionality can be turned off (if you prefer). This new option allows site owners to better integrate with a core WordPress® feature commonly referred to as `FORCE_SSL_LOGIN` and/or `FORCE_SSL_ADMIN`. This new feature can be used, or not. It is intended mainly for site owners running w/ `FORCE_SSL_LOGIN` and/or `FORCE_SSL_ADMIN`.
* (s2Member/s2Member Pro) **Login/Registration Design Option (#437)** Adding a new option in the `s2Member® → General Options → Login/Registration Design` section. This new option (found at the bottom of `s2Member® → General Options → Login/Registration Design`) allows a site owner to show/hide the `Back To Home Page` link at the bottom of the default WordPress® Login/Registration system. This can be useful for site owners running w/ `FORCE_SSL_LOGIN` and/or `FORCE_SSL_ADMIN`; where they would prefer NOT to link customers back to the main site under a default HTTPS link; but rather, create their own link and gain better control over this area of their site.
* (s2Member/s2Member Pro) **Videos (#467)** Updating internal documentation found in `Dashboard → s2Member® → Quick-Start`. Specifically, the video player here was integrated with an older version of the YouTube® API and was not working properly. Fixed in this release.

= v130802 =

* (s2Member Pro) **Compatibility, WordPress® v3.6** Updating s2Member® Pro-Form templates and their underlying CSS. This update improves their appearance against the Twenty Thirteen theme that comes with WordPress® v3.6. Specifically, some of the Pro-Form buttons were a little out of place in this new default theme. Fixed in this release.
* (s2Member Pro) **Compatibility, Checkout Options (#443)** Revision 3. Updating this feature to support a wider variety of WordPress® configurations and content filters. This update also resolves an empty `desc=""` attribute error reported by some site owners. Feature description... It is now possible to build dropdown menus offering your customers a variety of options using a Single Pro-Form. This is accomplished quite easily using Shortcodes. For full details and examples, please check this section of your Dashboard in the latest release. See: `s2Member® → PayPal® Pro-Forms → Wrapping Multiple Shortcodes as "Checkout Options"`. NOTE: this works for PayPal® Pro-Forms, and ALSO for Authorize.Net Pro-Forms.

= v130801 =

* (s2Member Pro) **New Feature; Checkout Options (#403)** Revision 2. Updating documentation on this new feature to prevent conufusion for site owners. s2Member® Pro now supports "Checkout Options". It is now possible to build dropdown menus offering your customers a variety of options using a Single Pro-Form. This is accomplished quite easily using Shortcodes. For full details and examples, please check this section of your Dashboard in the latest release. See: `s2Member® → PayPal® Pro-Forms → Wrapping Multiple Shortcodes as "Checkout Options"`. NOTE: this works for PayPal® Pro-Forms, and ALSO for Authorize.Net Pro-Forms.

= v130731 =

* (s2Member Pro) **New Feature; Checkout Options (#403)** s2Member® Pro now supports "Checkout Options". It is now possible to build dropdown menus offering your customers a variety of options using a Single Pro-Form. This is accomplished quite easily using Shortcodes. For full details and examples, please check this section of your Dashboard in the latest release. See: `s2Member® → PayPal® Pro-Forms → Wrapping Multiple Shortcodes as "Checkout Options"`. NOTE: this works for PayPal® Pro-Forms, and ALSO for Authorize.Net Pro-Forms.
* (s2Member Pro) **Free Checkout (#403)** It is now possible to offer a 100% free checkout experience using any of s2Member's Pro-Form Shortcodes. In previous releases of s2Member® it was not possible to set the `ra=""` Attribute to a zero dollar amount. Now it is! This works for PayPal® Pro-Forms, and also for Authorize.Net Pro-Forms.
* (s2Member Pro) **100% Off Coupons (#403)** It is now possible to offer a 100% off coupon. This works for PayPal® Pro-Forms, and also for Authorize.Net Pro-Forms. See: `s2Member® → Pro Coupon Codes` for details and examples.
* (s2Member Pro) **Expiration Date Dropdowns (#428)** This release improves all s2Member® Pro-Form templates by adding dropdown menus for the customer's credit card expiration month/year instead of the simple text input field used in previous releases.
* (s2Member/s2Member Pro) **MySQL Wait Timeout (#349)** s2Member now automatically increases the MySQL `wait_timeout` to `300` seconds during s2Member processing routines. Reason for increase: should any 3rd party service API result in unexpected connection timeouts (such as PayPal, Authorize.Net, Amazon, MailChimp, AWeber, etc); this may cause a delay that could potentially exceed the default `wait_timeout` of `30` seconds on the MySQL resource handle that is global to all of WordPress. Increasing `wait_timeout` before transaction processing will decrease the chance of failure after a timeout is exceeded. Among other things, this resolves an elusive bug where there are mysterious 404 errors after checkout under the right scenario (e.g., when an unexpected timeout occurs). This may also resolve problems associated w/ some mysterious reports where emails were not sent during s2Member's attempt to complete post-processing of a transaction (and/or where other portions of post-processing failed under rare circumstances).
* (s2Member/s2Member Pro) **Alternative Views (#300)** This release gives s2Member® the ability to hide protected content in widgets that list protected WordPress® Pages. This is a new Alternative View in the Dashboard. See: `s2Member® → Restriction Options → Alternative Views` for further details please.
* (s2Member/s2Member Pro) **Documentation Update (#350)** Subtle improvements to the built-in documentation pertaining to s2Member's Automatic List Transitioning feature in the Dashboard. See: `s2Member® → API/List Servers → Automatic Unsubscribes` for further details please.
* (s2Member/s2Member Pro) **Bug Fix (#387)** In s2Member® Only mode, a recursive scan for the WordPress® `/wp-load.php` file was failing somtimes when/if a custom directory was configured for plugins. Fixed in this release. See [this thread](http://www.s2member.com/forums/topic/problem-with-wordpress-folder-search-code/) for further details.
* (s2Member/s2Member Pro) **Bug Fix (#418)** Fixed incorrect pagination of user search results in the Dashboard.
* (s2Member/s2Member Pro) **Bug Fix (#418)** Fixed slow query against user searches in the Dashboard.
* (s2Member/s2Member Pro) **Bug Fix (#418)** Fixed incorrect result totals under some rare scenarios in user search results.

= v130617 =

* (s2Member/s2Member Pro) **IP Restrictions (#148)** It is now possible to introduce a custom template file that controls the error message displayed when/if a user breaches security by exceeding your maximum unique IP addresses; as configured under `s2Member → Restriction Options → Unique IP Restrictions`. If you would like to use a custom template for this message, please copy the default template file from `/s2member/includes/templates/errors/ip-restrictions.php` and place this file into your active WordPress® theme directory (or into the `/wp-content/` directory if you prefer). s2Member will automatically find your custom template in one of these locations; and s2Member will use your custom template instead of the built-in default.
* (s2Member Pro) **Bug Fix (#302)** Updating Authorize.Net Pro-Form Generator to support max days of `365` instead of `7`.
* (s2Member Pro) **Amazon S3 Secret Access Keys (#321)** Updating UI configuration panel to better explain what a Secret Access Key is; and adding a note about Secret Access Keys for Amazon S3 integration. Amazon® is deprecating Secret Access Keys, but they ARE still required for digitally signed URLs. This update changes nothing in s2Member's functionality. It simply adds some additional detail to a configuration field that will assist site owners integrating s2Member w/ Amazon S3 for the first time.
* (s2Member/s2Member Pro) **Translations (#317)** Updating `/s2member/includes/translations/translations.txt` (README file). Adding blurp about fuzzy translation entries in existing PO files that translate s2Member. This contains some additional tips on how to update existing PO files upon each release of s2Member and/or s2Member Pro.
* (s2Member/s2Member Pro) **Bug Fix (#321)** Fixing bug in `/s2member/includes/templates/cfg-files/s2-cross-xml.php` for S3 Buckets (resulting in `crossdomain.xml`). This file contained a parse error and was causing some problems for site owners integrating Adobe Flash content served via Amazon S3. Fixed in this release.
* (s2Member Pro) **PayPal Pro-Forms (#315)** Adding note in the Dashboard here (`s2Member → PayPal Pro-Forms → Shortcode Attributes Explained`). In the list of Shortcode Attributes we are adding a note regarding max character length for the `desc=""` Attribute in a PayPal Pro-Form Shortcode. This can be as long as you like. However, all descriptions passed through PayPal® APIs are truncated automatically to 60 characters max (e.g., the maximum allowed length for PayPal® descriptions is 60 characters). Nothing new here, but we thought it would be a good idea to clarify this behavior in the documentation. Updated in this release.
* (s2Member Pro) **PayPal Pro-Forms (#312)** Preventing the use of multiple Pro-Forms in the same Post/Page. This has never been possible, it is known to break the functionality of s2Member Pro-Forms. Please limit Pro-Form Shortcodes to ONE for each Post/Page; and do NOT attempt to use more than one Pro-Form Shortcode on the same Post/Page (at the same time). In this release we have added a friendly JavaScript alert/warning for site owners that attempt this, so that problems and confusion can be avoided in this unlikely scenario.

= v130513 =

* (s2Member/s2Member Pro) **s2Stream Shortcode Bug Fix (#256)** Fixing a bug first introduced in the previous release where we added support for `player_aspectratio`. This quick update corrects the PHP parse error at line #154 of `sc-files-in.inc.php`. It also corrects the behavior of the `player_height=""` and `player_aspectratio=""` Shortcode Attributes for the `s2Stream` Shortcode. Many thanks to everyone that reported this bug.
* (s2Member Pro) **Codestyling Localization** Removing symlink creator for Codestyleing Localization compatibility. There have been some reports of problems during WordPress® automatic upgrades (when/if the symlink exists). Until we can find a way to avoid this, we're disabling the automatic symlink generator. If you're running the Codestyling Localization plugin together with s2Member Pro, you will need to create the symlink yourself if you want to make s2Member fully compatible. Please create a symlink here: `/wp-content/plugins/s2member/s2member-pro` that points to the s2Member Pro directory: `/wp-content/plugins/s2member-pro`. See notes in previous changelog for further details on this.

 **IMPORTANT NOTE:** If you upgraded previously to v130510 (and you ran the Codestyling Localization plugin together with s2Member® v130510—at any time); please delete this symlink via FTP: `/wp-content/plugins/s2member/s2member-pro`. Please do this BEFORE attempting an automatic upgrade via WordPress®.

 If you missed this note and you've already attempted an automatic upgrade, you will have trouble. Here's how to correct the problem.

 1. Log into your site via FTP and delete these two directories manually.
  `/wp-content/plugins/s2member` and `/wp-content/plugins/s2member-pro`.

 2. Now, please follow the [instructions here](http://www.s2member.com/pro/#!s2_tab_jump=s2-pro-install-update) to upgrade s2Member® Pro manually.

= v130510 =

* (s2Member Pro) **Authorize.Net UK (and Other Currencies) (#104)** Adding support for Authorize.Net UK and other currencies too. s2Member Pro now officially supports Authorize.Net UK Edition. It is now possible to change your Authorize.Net Pro-Form Shortcode Attribute `cc="USD"` to one of these values: `cc="USD"`, or `cc="CAD"` or `cc="EUR"` or `cc="GBP"`. For further details, please see: `Dashboard → Authorize.Net Pro-Forms → Shortcode Attributes (Explained)`.
* (s2Member Pro) **ClickBank Skins (#227)** Adding support for the `cbskin=""` Shortcode Attribute. For further details, please see: `Dashboard → ClickBank Buttons → Shortcode Attributes (Explained)`.
* (s2Member Pro) **ClickBank PitchPlus Upsell Flows (#227)** Adding support for ClickBank PitchPlus Upsell Flows via new Shortcode Attributes: `cbfid=""`, `cbur=""`, `cbf="auto"`. s2Member Pro now officially supports ClickBank PitchPlus Upsell Flows. We support PitchPlus Basic and PitchPlus Advanced too. For further details, please see: `Dashboard → ClickBank Buttons → Shortcode Attributes (Explained)`.
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
* (s2Member Pro) **Username Validation (#246)** Now forcing user input (during Pro-Form registration) to lowercase on Multisite Networks to prevent unnecessary validation errors during checkout (saving a customer time). Also, s2Member now validates a customer's Username before it is passed through `sanitize_user()` (a core WordPress® function). This prevents confusion for a customer where certain characters were stripped out automatically, causing them problems when attempting to log in for the first time (e.g., the customer thinks their Username is `john~doe`; when it is actually `johndoe` because WordPress (when running a Multisite Network) removes anything that is NOT `a-z0-9 _.-@` (and s2Member removes whitespace as well).

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
* (s2Member/s2Member Pro) **Feature Enhancement/Emails (#21)** Adding additional Replacement Codes for New User Email Notifications (for both the User/Member Notification and also for the Administrator Notification). The following Replacement Codes are now possible: `%%role%%`, `%%label%%`, `%%level%%`, `%%ccaps%%`. Also adding four new Filters for developers. These include: `ws_plugin__s2member_welcome_email_sbj`, `ws_plugin__s2member_welcome_email_msg`, `ws_plugin__s2member_admin_new_user_email_sbj`, `ws_plugin__s2member_admin_new_user_email_msg`. See `Dashboard → General Options →  Email Configuration` for further details.
* (s2Member/s2Member Pro) **Feature Enhancement/Emails (#30)** Adding support for PHP tags in the following emails: New User Notification, Administrative New User Notification, Signup Confirmation Email, Specific Post/Page Confirmation Email. See the relevant sections in your Dashboard for further details. Such as: `s2Member® → General Options → Email Configuration` and `s2Member® → PayPal® Options → Signup Confirmation Email`.
* (s2Member/s2Member Pro) **Feature Enhancement/Shortcodes (#23)** Adding support for the `lang=""` Attribute in PayPal Buttons, PayPal Pro-Forms, and in Google Checkout Buttons. This is a bit different from the existing `lc=""` value. The `lc=""` value controls the interface at PayPal, while the `lang=""` value controls the language of the Standard and/or Express Checkout Button itself (with respect to s2Member®). For further details, please see: `Dashboard → PayPal Buttons (or Pro-Forms) → Shortcode Attributes (Explained)`.
* (s2Member/s2Member Pro) **Bug Fix** Fixing bug in User Access Package. Now checking if `$cap_enabled` also is `TRUE`; just in case another plugin or hack file attempts to disable Custom Capabilities without removing them. Not likely, but we can support this easily with a quick update in this release. Note... this has no impact on s2Member's existing functionality. Custom Capabilities continue to work just as they always have.
* (s2Member/s2Member Pro) **Feature Enhancement/Logging** Adding new logger. Logs to file `reg-handler.log`. Includes all User/Member registrations handled by s2Member® (either directly or indirectly). Only if logging is enabled. For further details, please check your Dashboard here: `s2Member® → Log Files (Debug)`.
* (s2Member/s2Member Pro) **Feature Enhancement/EOTs (#29)** Adding UI option for EOT Grace Time. For further details, please see: `Dashboard → PayPal Options → Automatic EOT Behavior`. Also adding a new Filter for developers: `ws_plugin__s2member_eot_grace_time`.
* (s2Member/s2Member Pro) **Feature Enhancement/EOTs** Adding UI option for EOT Custom Capability Removal. For further details, please see: `Dashboard → PayPal Options → Automatic EOT Behavior`. Also adding a new Filter for developers: `ws_plugin__s2member_remove_ccaps_during_eot_events`.
* (s2Member/s2Member Pro) **Feature Enhancement/s2Stream Shortcode (#32)** Adding additional support for JW Player™ Captions, Titles, Descriptions, and Media IDs (i.e., `player_title=""`, `player_description=""`, `player_mediaid=""`, `player_captions=""`). Please check the Shortcode Attributes tab in [this KB article](http://www.s2member.com/kb/jwplayer-s2stream-shortcodes/#using-s2stream-shortcodes) for further details.

= v130214 =

* **(Maintenance Release) Upgrade immediately.**
* (s2Member/s2Member Pro) **Log Viewer (#16)** Additional log file descriptions have been added to the Dashboard, along with some other UI enhancements in this section.
* (s2Member/s2Member Pro) **Bug Fix (#18)** Usernames consisting of all numeric values were not always being redirected to the Login Welcome Page upon logging in, even when s2Member® was configured to do so. Fixed in this release. Discussed in [this thread](http://www.s2member.com/forums/topic/subscribers-not-taken-to-welcome-page/page/2/#post-41663).
* (s2Member Pro) **Coupon Codes (#19)** Adding new Replacement Codes: `%%full_coupon_code%%`, `%%coupon_code%%` and `%%coupon_affiliate_id%%`. These are now available in all API Tracking Codes, in all Custom Return URLs for Pro-Forms, and in most API Notifications.
* (s2Member Pro) **Coupon Codes (#19)** Deprecating the `%%affiliate_id%%` Replacement Code for tracking Affiliate Coupon Codes in favor of `%%coupon_affiliate_id%%`.
* (s2Member/s2Member Pro) **Last Login Time** Improving readability of Last Login Time in list of Users/Members.
* (s2Member/s2Member Pro) **Compatibility** Improving support for WordPress® v3.6-alpha with respect to `tabindex` values on `/wp-login.php`.
* (s2Member/s2Member Pro) **Compatibility** Bumping minimum WordPress® requirement from v3.2 up to v3.3. Starting with this release, s2Member® is no longer compatible with the much older WordPress® v3.2.

= v130213 =

* **(Maintenance Release) Upgrade immediately.**
* (s2Member Pro) **Compatibility (#13)** PayPal® Payments Pro, PayPal® Payments Pro (Payflow Edition), and Authorize.Net. s2Member® Pro now supports a recurring interval of Semi-Yearly (e.g., charges occur every six months). This has always been possible by manipulating Shortcode Attribues, but it's now officially supported by Pro-Form Generators in your Dashboard—which come with s2Member® Pro.
* (s2Member Pro) **Compatibility (#13)** ClickBank® Recurring Products. ClickBank® has started allowing a Weekly recurring interval and stopped allowing Yearly. s2Member® has been updated in this release to support a Weekly recurring interval with ClickBank®; and to remove the Yearly option in the ClickBank® Button Generator.
* (s2Member Pro) **Compatibility (#13)** PayPal® Payments Pro (Payflow Edition). PayPal® Payments Pro (Payflow Edition) has started allowing a Daily recurring interval. s2Member® has been updated in this release to support a Daily recurring interval with PayPal® Payments Pro (Payflow Edition). Daily recurring intervals remain possible with PayPal® Pro accounts that do not include the additional Payflow API. This change simply adds official support for Daily recurring billing with PayPal® Payments Pro (Payflow Edition).
* (s2Member) **Debugging Assistance** Updating s2Member's PayPal® PDT/Auto-Return handler to better handle scenarios where a site owner is missing a PayPal® PDT Identity Token in their s2Member® configuration, or has incorrectly set the `custom=""` Shortcode Attribute in Payment Buttons generated with s2Member®. Administrative notices are now displayed in the Dashboard when/if this occurs and s2Member® can catch the issue during post-processing of a transaction.
* (s2Member/s2Member Pro) **General Code Cleanup** Removing all `/**/` markers in the s2Member® codebase. These were used in conjunction with PolyStyle® code formatting tools to preserve line breaks in the code. The WebSharks™ development team no longer uses PolyStyle®, making these obsolete now. Removed in this release to improve readability for developers.
* (s2Member/s2Member Pro) **General Code Cleanup** Removing all unnecessary uses of PHP's `eval()` function in s2Member's codebase. These were used to keep repetitive code all in a single line; part of a standard the WebSharks™ development team is now moving away from. Removed in this release to improve readability for developers; and to prevent unnecessary confusion.
* (s2Member/s2Member Pro) **Auto-EOT System** Updated s2Member's Auto-EOT System. s2Member® now leaves an additional note behind after a demotion, which references the Paid Subscr. Gateway and Paid Subscr. ID values before the demotion occurred. This way there is a better reference left behind after an automatic demotion occurs.
* (s2Member/s2Member Pro) **Searching Users** Updating search function in list of Users (i.e., `Dashboard → Users → [Search Box]`) to include the Administrative Notes field when searching for Users. This allows references to old Paid Subscr. IDs in the Administrative Notes field to be considered when searching Users/Members.
* (s2Member/s2Member Pro) **Last Login Time** Adding new User Option value (tracked by s2Member®). This option value tracks the last time each User/Member logged into your site. Ex: `get_user_option("s2member_last_login_time")`.
* (s2Member/s2Member Pro) **Last Login Time** Adding new User data column to list of Users in the Dashboard: `Last Login Time`.
* (s2Member/s2Member Pro) **Last Login Time** Adding new API Function: [`s2member_last_login_time()`](http://www.s2member.com/codex/stable/s2member/api_functions/package-summary/).
* (s2Member/s2Member Pro) **ezPHP** Updated all internal documentation references that pointed to Exec-PHP or the PHP Execution plugin as recommendations for developers that wish to integrate PHP tags into Posts/Pages/Widgets. These old references now point to the [ezPHP](http://www.s2member.com/kb/ezphp-plugin/) plugin by s2Member® Lead Developer: Jason Caldwell. s2Member® remains compatible with other PHP plugins, but we recommend [ezPHP](http://www.s2member.com/kb/ezphp-plugin/) for the best compatibility with both s2Member® and WordPress® itself.
* (s2Member/s2Member Pro) **Simple Shortcode Conditionals** Adding a [Simple Shortcode Conditionals](http://www.s2member.com/kb/simple-shortcode-conditionals/) section to `s2Member® → Restriction Options` in the Dashboard. This way more site owners will be aware of this feature from the start.
* (s2Member/s2Member Pro) **Login/Registration Design** Login/Registration Design with s2Member® is now optional (e.g., this feature can be disabled now—if you prefer). See: `Dashboard → s2Member® → General Options → Login/Registration Design`. This feature is enabled by default on all s2Member® installations.
* (s2Member/s2Member Pro) **Inline Documentation** Adding more links to KB articles throughout the Dashboard area.
* (s2Member/s2Member Pro) **Inline Documentation** Updating all spaced parenthesis like `( something... )` to remove the grammatical errors—by removing the extra spaces inside these brackets.
* (s2Member/s2Member Pro) **Inline Documentation** Removing all references to PriMoThemes and/or primothemes.com within the application itself. PriMoThemes is now s2Member® (as of Jan 2012—it's been awhile; so time to remove these obviously).
* (s2Member/s2Member Pro) **Inline Documentation** Adding link to "more updates..." in the Dashboard, pointing to the s2Member® KB. Increasing number of recent KB udpates from 3 up to 5. These are visible from any s2Member® page in the Dashboard (top of the right-hand column).
* (s2Member/s2Member Pro) **Inline Documentation** Adding [s2Member® Pro](http://www.s2member.com/pro/) (a recommended upgrade) to the Quick-Start Guide for s2Member®—in the Dashboard.
* (s2Member/s2Member Pro) **Inline Documentation** Adding Troubleshooting section to the Quick-Start Guide for s2Member®—in the Dashboard.
* (s2Member/s2Member Pro) **Inline Documentation** Adding Perfect Theme section to the Quick-Start Guide for s2Member®—in the Dashboard.
* (s2Member/s2Member Pro) **Inline Documentation** Adding video tutorial to the `Dashboard → s2Member® → API / Scripting → Custom Capabilities` section.
* (s2Member/s2Member Pro) **Logging Functionality** Adding an s2Member® Log Viewer to the Dashboard for all site owners; and also for s2Member® Support Reps to use when running diagnostics. See: `Dashboard → s2Member® → Log Files (Debug)` for further details.
* (s2Member/s2Member Pro) **Logging Functionality** Logging routines are now enabled by default on all new installations of s2Member®. Existing installations of s2Member® are advised to enable logging, by visiting this section of your Dashboard. See: `s2Member® → PayPal® Options (or Authorize.Net, ClickBank, etc) → Account Details → Logging`.
* (s2Member/s2Member Pro) **Logging Functionality** Additional logging routines that will track all s2Member® HTTP communication within WordPress® is now enabled by default. This new log file will be located inside `/wp-content/plugins/s2member-logs`. It is named: `s2-http-api-debug.log`. See: `Dashboard → s2Member® → Log Files (Debug)` for further details.
* (s2Member/s2Member Pro) **Logging Functionality** Additional logging routines that will track *all* HTTP communication within WordPress® are now possible (these are quite extensive). See: `Dashboard → s2Member® → Log Files (Debug) → Logging Configuration` for further details. This more extensive logging is disabled by default; it must be enabled by a site owner. For debugging only—this should NEVER be enabled on a live site.
* (s2Member/s2Member Pro) **Logging Functionality** Adding date/time to all log entries maintained by s2Member®.
* (s2Member/s2Member Pro) **GZIP Conflicts** Adding additional lines of defense against GZIP conflicts during file downloads, with calls to `@apache_setenv("no-gzip", "1")` in other areas—not just during public file downloads (e.g., also during User/Member exporations, log file downloads, etc).
* (s2Member/s2Member Pro) **GZIP Conflicts** Adding an additional line of defense against GZIP conflicts during file downloads, with this line now appearing in the `.htaccess` file snippet added by the s2Member® software application: `RewriteCond %{QUERY_STRING} (^|\?|&)no-gzip\=1`.
* (s2Member/s2Member Pro) **GZIP Conflicts** Adding an additional line of defense against GZIP conflicts during User/Member exporations, log file downloads, and other downloads that come straight from the Dashboard area to site owners via web browsers. s2Member® now sends `Content-Encoding: none` to prevent Apache's `mod_deflate` from interfering with s2Member® under these special scenarios. A `Content-Encoding: none` header value is technically invalid, but it's known to prevent issues with `mod_deflate`. Since a `Content-Encoding: none` header value is technically invalid, s2Member® does NOT implement this during public file downloads; where we need to provide wider support for a long list of devices that may choke on this incorrect value. This is only implemented for site owners in the administrative areas of WordPress; and only for file downloads related to CSV export files and logs.
* (s2Member/s2Member Pro) **Bug Fix** Fixed incorrect `preg_split` limit against `$paypal['item_number']` in IPN handler for `subscr_payment` and `subscr_cancel` transaction types. Doesn't appear to have affected anything negatively, but it was wrong none the less. Fixed in this release.
* (s2Member/s2Member Pro) **Bug Fix** Fixed incorrect handling of a single opt-in checkbox on BuddyPress registration forms, which was not being wrapped with s2Member's BuddyPress container divs at all times. A symptom of this bug was to see a checkbox on your BuddyPress registration that was out of alignment or out of position. Fixed in this release.
* (s2Member/s2Member Pro) **Compatibility** Updated all of s2Member's IPN handlers to accept `$_REQUEST` data for Proxy-related variables like `s2member_paypal_proxy_return_url`. This allows s2Member® itself to use `$_POST` variables for Proxy-related variables; and it further reduces the likelihood of 403 Forbidden errors caused by [paranoid Mod Security configurations](http://www.s2member.com/kb/mod-security-random-503-403-errors/). One issue this should help to correct, is a mysterious case where a `success=""` Shortcode Attribute is not working as you might expect. This can be caused by [paranoid Mod Security configurations](http://www.s2member.com/kb/mod-security-random-503-403-errors/) at places like HostGator®, because a URL is passing through a query string. This release will help to prevent this from becoming a problem, because `success=""` URLs will be passed through `$_POST` variables now in all Pro-Form integrations.

= v130207 =

* **(Maintenance Release) Upgrade immediately.**
* (s2Member Pro) **Bug Fix (#2)** Modification Tracking Codes not working properly under s2Member's Authorize.Net integration. Fixed in this release. Discussed in [this thread](http://www.s2member.com/forums/topic/shareasale-integration-not-working/#post-40954).
* (s2Member) **Compatibility (#4)** PayPal® integrated into a site charging in the JPY currency was incorrectly limited to an amount of 10000.00. Fixed in this release. Discussed in [this thread](http://www.s2member.com/forums/topic/default-currency-can-i-change-it-to-yen/#post-40590).
* (s2Member) **Compatibility (#5)** Incorrect `tabindex` values in WordPress® v3.5+. Fixed in this release. Discussed in [this thread](http://www.s2member.com/forums/topic/tabindex-messed-up-on-registration-page/#post-40591).
* (s2Member/s2Member Pro) **Line Breaks (#3)** Some line breaks in both s2Member® and s2Member® Pro were converted to CRLF inadvertently in the previous release. No real harm done, but this was causing some problems for the s2Member® Server Scanner because it uses a checksum against installation files; which was being thrown off balance due to the unexpected line break style. Fixed in this release. A symptom of this bug was to see invalid checksums when running diagnostics with the s2Member® Server Scanner.
* (s2Member/s2Member Pro) **Compatibility (#6)** s2Member® File Downloads (audio/video files) with spaces in a file name were not always being handled properly. Fixed in this release. Discussed in [this thread](http://www.s2member.com/forums/topic/jwplayer-filename-bug/#post-40799).

= v130203 =

* **(New Release) Please read this changelog for important details.**
* (s2Member Pro) **Remote Ops API (`create_user`)** s2Member® Pro's Remote Operations API, for the `create_user` Operation has been updated to support a new specification: `modify_if_login_exists`. For further details, please check your s2Member® Pro Dashboard here: `s2Member® → API / Scripting → Remote Operations API`.
* (s2Member Pro) **Remote Ops API (`modify_user`,`delete_user`)** s2Member® Pro's Remote Operations API has been updated to support two additional Operations: `update_user` and `delete_user`. For further details on these new Operations, please check your s2Member® Pro Dashboard here: `s2Member® → API / Scripting → Remote Operations API`.
* (s2Member Pro) **Remote Ops API (`init` hook priority)** s2Member® Pro's Remote Operations API has been updated to prevent conflicts when running in concert with BuddyPress v1.6.4+. Hook priority now running at default value of `11`, right after BuddyPress v1.6.4 at hook priority `10`.
* (s2Member/s2Member Pro) **s2Stream Shortcode (#88)** s2Member® now supports JW Player® license keys (for the professional edition) using Shortcode Attribute `player_key=""` (or they can be specified sitewide via JavaScript provided by Longtail Video—optional). See [this discussion](http://www.s2member.com/forums/topic/jwplayer-shortcode-for-poster-not-working/#post-40435). See also: [this KB article](http://www.s2member.com/kb/jwplayer-s2stream-shortcodes/#using-s2stream-shortcodes).
* (s2Member/s2Member Pro) **s2Stream Shortcode (#88)** Bug fix. The s2Stream Shortcode was not working properly (with respect to a specific Shortcode Attribute: `player_image=""`). Fixed in this release. See [this discussion](http://www.s2member.com/forums/topic/jwplayer-shortcode-for-poster-not-working/#post-40128). See also: [this KB article](http://www.s2member.com/kb/jwplayer-s2stream-shortcodes/#using-s2stream-shortcodes).
* (s2Member Pro) **User Exportation (#89)** s2Member® Pro's User Exportation now includes separate data columns for each Custom Registration/Profile Field that you've configured with s2Member®. Also, s2Member® Pro will now include ALL Custom Registration/Profile Fields (even if there is no value associated with certain Fields, for specific Users/Members—e.g., empty column values will now be included by s2Member® Pro). This provides a more consistent/readable CSV export file; a major improvement. Discussed in [this KB article](http://www.s2member.com/kb/importing-updating-users/#custom-registration-profile-fields).
* (s2Member Pro) **User Importation (#89)** s2Member® Pro's User/Member Import format changed in this release (with respect to Custom Registration/Profile Fields only). If you are importing Custom Registration/Profile Fields, please review [this KB article](http://www.s2member.com/kb/importing-updating-users/#custom-registration-profile-fields) before you import new Users/Members or mass update any existing Users/Members. ALSO NOTE: User/Member CSV Export Files generated by previous versions of s2Member® Pro (if they contained any Custom Registration/Profile Fields) will NOT be compatible with this latest release (e.g., you should NOT attempt to re-import those old files in an effort to mass update existing Users/Members). Please generate a new User/Member CSV Export File in the latest release of s2Member® Pro before attempting to edit and/or mass update existing Users/Members with applications like MS Excel or OpenOffice.

= v130123 =

* **(Maintenance Release) Upgrade immediately.**
* (s2Member/s2Member Pro) **s2Stream Shortcode (#78)** s2Member® now supports JW Player® license keys using Shortcode Attribute `player_key=""`. See [this KB article](http://www.s2member.com/kb/jwplayer-s2stream-shortcodes/) please. Also discussed in [this thread](http://www.s2member.com/forums/topic/new-jw-player-6-s2-video-audio-shortcodes/#post-38768).
* (s2Member/s2Member Pro) **s2Stream Shortcode (#79)** s2Member® now supports JW Player® [Advanced Option Blocks](http://www.longtailvideo.com/support/jw-player/28839/embedding-the-player) using Shortcode Attribute `player_option_blocks=""`. Example: `player_option_blocks="sharing:{}"`. See [this KB article](http://www.s2member.com/kb/jwplayer-s2stream-shortcodes/) please. Also discussed in [this thread](http://www.s2member.com/forums/topic/new-jw-player-6-s2-video-audio-shortcodes/#post-38768).
* (s2Member Pro) **User Exportation (#80)** s2Member® Pro User Exportation now occurs with MySQL `ORDER BY ID`, instead of no `ORDER BY` at all. This helps to prevent confusion and buggy behavior. Discussed in [this thread](http://www.s2member.com/forums/topic/user-export-not-working-properly/#post-39123).
* (s2Member Pro) **User Exportation (#81)** s2Member Pro's User Exportation now supports the exporation of up to `1000` User/Member table rows at once. Of course it remains possible to export ALL of your Users/Members with s2Member® Pro. All we've done here is bump the default limit from `250` up to `1000` at a time. In addition, there is a new Filter making it possible to extend this limit further on servers that can handle it. Use Filter: `ws_plugin__s2member_pro_export_users_limit` if you would like to export more Users all at once. See also: `Dashboard → s2Member® Pro → User/Member Exportation`.
* (s2Member/s2Member Pro) **KB Articles** Inline documentation updated in some areas, with a few links pointing to helpful/related KB articles.

= v130121 =

* **(Maintenance Release) Upgrade immediately.**
* **New Feature** s2Member® now comes with a new Shortcode `[s2Stream file_download="video.mp4" player="jwplayer-v6" ... /]`, making it MUCH easier for site owners to implemement RTMP streams of audio/video content. For further details, please check your Dashboard under: `s2Member® → Download Options → JW Player® v6 and RTMP Protocol Examples`. See also: `s2Member® → Download Options → Shortcode Attributes (Explained)`.
* **Compatibility (#75)** Updated s2Member's local file storage engine (for File Downloads via s2Member®), to support special characters in file names. Discussed in [this thread](http://www.s2member.com/forums/topic/problem-with-quotes-in-filename-downloads/#post-38395).
* **Bug Fix (#71)** A bug first introduced in the previous release of v130116, where we added support for byte-range requests to s2Member's File Download functionality, was causing multiple byte-range requests (processed by s2Member) to count against each User/Member as multiple File Downloads. Fixed in this release.
* **Compatibility** Updated s2Member's integration with Amazon® S3 to extend the default 30 second connection timeout (which was too conservative for many integrations) up to 24 hours by default, making it match the same as s2Member's Amazon® CloudFront connection timeout. For further details, please check your Dashboard under: `s2Member® → Download Options → Amazon® S3/CDN Storage → Dev Note w/Technical Details`. It is possible to modify this connection timeout through a Filter discussed there.

= v130116 =

* **(Maintenance Release) Upgrade immediately.**
* **Compatibility (#39)** Updated codes samples for JW Player®, to include the `mp4:` prefix when implementing RTMP streams against MP4 video files. Discussed in [this thread](http://www.s2member.com/forums/topic/cloudfront-subfolder-streaming-error/#post-35750).
* **Compatibility (#51)** Updated Payflow® API to support recurring billing every six months. Discussed in [this thread](http://www.s2member.com/forums/topic/payflow-error-6-month-recurring-membership/#post-36053).
* **Bug Fix (#69)** Updated multisite user imporation routine, to support a specific scenario not covered under WordPress v3.5. Discussed in [this thread](http://www.s2member.com/forums/topic/users-on-multisite/).
* **Feature Improvement (#71)** s2Member® has been updated to support byte-range requests with it's default local file storage engine, served from the `/s2member-files/` directory. s2Member® has always supported byte-range requests when integrated with Amazon® CloudFront. Now it supports byte-range requests in it's default local storage engine too. This will improve compatibility with mobile devices, iTunes™  and other devices that use byte-range requests. Discussed in [this thread](http://www.s2member.com/forums/topic/any-way-to-set-accept-ranges-for-downloads/#post-15871).

= v121213 =

* ... trimmed away at v121213.
* Initial release: v1.0.
