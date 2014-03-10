<?php
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit("Do not access this file directly.");
?>

// s2Member-only mode. Do NOT load theme functions, exclude all themes.