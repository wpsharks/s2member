<?php
// @codingStandardsIgnoreFile
/**
* Get Help Panel (markup only; $draft and $notice provided by class).
*
* @package s2Member\Menu_Pages
* @since 250824
*/

if (!defined('WPINC'))
	exit('Do not access this file directly.');

// Build action URL (same page, strip noisy args).
$action = esc_url(remove_query_arg(
	array('s2m_help','updated','settings-updated','error'),
	wp_unslash($_SERVER['REQUEST_URI'])
));

// Visible mailbox (filterable) + optional support portal link.
$mailbox     = apply_filters('ws_plugin__s2member_get_help_mailbox', 'support@wpsharks.com');
$subject_def = apply_filters('ws_plugin__s2member_get_help_subject', ''); // keep default blank
$support_url = 'https://s2member.com/support?s2=1';

echo '<style>
.ws-plugin--s2member-get-help-panel label {font-size: 115%; weight: 600;}
.ws-plugin--s2member-get-help-panel input:not([type="submit"]),
.ws-plugin--s2member-get-help-panel textarea {max-width: 700px !important;}
</style>';

echo '<div class="ws-menu-page-group" title="Need Help? 🛟 " default-state="closed">' . "\n";
echo '	<div class="ws-menu-page-section ws-plugin--s2member-get-help-panel">' . "\n";

if (!empty($notice['type'])) {
	$class = $notice['type'] === 'success' ? 'notice-success' : 'notice-error';
	echo '		<div class="notice '.$class.' is-dismissible" style="margin:0 0 12px;"><p>'.esc_html($notice['text']).'</p></div>'."\n";
}

echo '		<p>If you have questions, run into a problem, or aren’t sure how to achieve the setup you have in mind, free support is available.</p>'."\n";

echo '		<p>If you’d like us to take care of setup, configuration, customizations, or even build new features for you, we also offer professional services.</p>'."\n";

echo '		<p>Please use the form below to tell us the details of what you need or want:</p>'."\n";

echo '		<form method="post" action="'.$action.'" style="margin-top:.75em;" onsubmit="this.querySelector(\'button[type=submit]\').setAttribute(\'disabled\',\'disabled\');">' . "\n";
echo '			' . wp_nonce_field('s2member_get_help', 's2m_help_nonce', true, false) . "\n";
echo '			<input type="hidden" name="s2m_help_submit" value="1" />' . "\n";

echo '			<p><label for="s2m_help_name"><strong>Your Name</strong></label><br />' . "\n";
echo '			<input type="text" id="s2m_help_name" name="name" class="regular-text" value="'.esc_attr($draft['name']).'" /></p>' . "\n";

echo '			<p><label for="s2m_help_email"><strong>Your Email</strong></label> *<br />' . "\n";
echo '			<input type="email" id="s2m_help_email" name="email" class="regular-text" value="'.esc_attr($draft['email']).'" required /></p>' . "\n";

echo '			<p><label for="s2m_help_subject"><strong>Subject</strong></label> *<br />' . "\n";
echo '			<input type="text" id="s2m_help_subject" name="subject" class="regular-text" value="'.esc_attr($draft['subject']).'" placeholder="'.esc_attr($subject_def).'" required /></p>' . "\n";

echo '			<p><label for="s2m_help_details"><strong>How can I help?</strong></label> *<br />' . "\n";
echo '			<textarea id="s2m_help_details" name="details" class="large-text code" rows="6" placeholder="Describe your support question or what you need done." required>'.esc_textarea($draft['details']).'</textarea></p>' . "\n";

echo '			<p><label for="s2m_help_meta"><strong>Additional details</strong></label><br />' . "\n";
echo '			<textarea id="s2m_help_meta" name="meta" class="large-text code" rows="2">'.esc_textarea($draft['meta']).'</textarea></p>' . "\n";

echo '			<p>' . "\n";
echo '				<button type="submit" class="button button-primary">Send</button> ' . "\n";
echo '				<span style="margin-left:.5em;color:#555d66;">(You’ll get a copy.) Or email <a href="mailto:'.esc_attr($mailbox).'">'.esc_html($mailbox).'</a>. ' . "\n";
echo '				Or <a href="'.esc_url($support_url).'" target="_blank" rel="noopener">open the Support page</a>.</span>' . "\n";
echo '			</p>' . "\n";

echo '		</form>' . "\n";

echo '	</div>' . "\n";
echo '</div>' . "\n";
