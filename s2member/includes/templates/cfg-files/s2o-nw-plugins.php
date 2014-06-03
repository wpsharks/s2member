<?php
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit("Do not access this file directly.");
?>

// s2Member-only mode. Only load the s2Member plugin, exclude all others.

$o_ws_plugin__s2member = preg_replace ("/-o\.php$/", ".php", __FILE__);
$o_ws_plugin__s2member_is_loaded_already = (defined ("WS_PLUGIN__S2MEMBER_VERSION")) ? true : false;
$o_ws_plugin__plugins_s2member = WP_PLUGIN_DIR . "/" . basename (dirname ($o_ws_plugin__s2member)) ."/" . basename($o_ws_plugin__s2member);

if ((!file_exists ($o_ws_plugin__plugins_s2member) || @is_link ($o_ws_plugin__plugins_s2member)) && file_exists ($o_ws_plugin__s2member) && !$o_ws_plugin__s2member_is_loaded_already)
	include_once $o_ws_plugin__s2member; // s2Member in a strange location?

else if (in_array($o_ws_plugin__plugins_s2member, wp_get_active_network_plugins ()) && file_exists ($o_ws_plugin__plugins_s2member) && !$o_ws_plugin__s2member_is_loaded_already)
	include_once $o_ws_plugin__plugins_s2member;

else if (apply_filters("ws_plugin_s2member_o_force", false) && !$o_ws_plugin__s2member_is_loaded_already) // Off by default. Force s2Member to load?
	include_once $o_ws_plugin__s2member;

unset($o_ws_plugin__plugins_s2member, $o_ws_plugin__s2member_is_loaded_already, $o_ws_plugin__s2member);