<?php
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit("Do not access this file directly.");
?>

<option value="D" selected="selected"><?php echo esc_html (_x ("Days", "s2member-admin", "s2member")); ?></option>
<option value="W"><?php echo esc_html (_x ("Weeks", "s2member-admin", "s2member")); ?></option>
<option value="M"><?php echo esc_html (_x ("Months", "s2member-admin", "s2member")); ?></option>
<option value="Y"><?php echo esc_html (_x ("Years", "s2member-admin", "s2member")); ?></option>