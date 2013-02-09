<?php
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit("Do not access this file directly.");
?>

// s2Member-only mode. Only load (o)nly/(a)ll files.

if (file_exists (WPMU_PLUGIN_DIR . "/s2member-o-hacks.php"))
	include_once WPMU_PLUGIN_DIR . "/s2member-o-hacks.php";

else if (file_exists (WPMU_PLUGIN_DIR . "/s2-o-hacks.php"))
	include_once WPMU_PLUGIN_DIR . "/s2-o-hacks.php";

// --------------------------------------------------

if (file_exists (WPMU_PLUGIN_DIR . "/s2member-a-hacks.php"))
	include_once WPMU_PLUGIN_DIR . "/s2member-a-hacks.php";

else if (file_exists (WPMU_PLUGIN_DIR . "/s2-a-hacks.php"))
	include_once WPMU_PLUGIN_DIR . "/s2-a-hacks.php";

// --------------------------------------------------

if (file_exists (WPMU_PLUGIN_DIR . "/s2member-o.php"))
	include_once WPMU_PLUGIN_DIR . "/s2member-o.php";

else if (file_exists (WPMU_PLUGIN_DIR . "/s2-o.php"))
	include_once WPMU_PLUGIN_DIR . "/s2-o.php";

// --------------------------------------------------

if (file_exists (WPMU_PLUGIN_DIR . "/s2member-a.php"))
	include_once WPMU_PLUGIN_DIR . "/s2member-a.php";

else if (file_exists (WPMU_PLUGIN_DIR . "/s2-a.php"))
	include_once WPMU_PLUGIN_DIR . "/s2-a.php";