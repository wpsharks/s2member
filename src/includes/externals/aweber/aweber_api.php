<?php
// @codingStandardsIgnoreFile

if(!defined('WPINC')) //260928.0402 This API entry point is only loaded through WordPress/s2Member; reject direct web requests.
	exit('Do not access this file directly.');

if (class_exists('AWeberAPI')) {
    trigger_error("Duplicate: Another AWeberAPI client library is already in scope.", E_USER_WARNING);
}
else {
    require_once('aweber.php');
}
