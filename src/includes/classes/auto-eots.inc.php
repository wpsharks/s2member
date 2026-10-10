<?php
// @codingStandardsIgnoreFile
/**
 * Backward compatibility for s2Member's historical Auto-EOT class.
 *
 * Copyright: © 2009-2011
 * {@link http://websharks-inc.com/ WebSharks, Inc.}
 * (coded in the USA)
 *
 * Released under the terms of the GNU General Public License.
 * You should have received a copy of the GNU General Public License,
 * along with this software. In the main directory, see: /licensing/
 * If not, see: {@link http://www.gnu.org/licenses/}.
 *
 * @package s2Member\Auto_EOT_System
 * @since 3.5
 */
if(!defined('WPINC')) // MUST have WordPress.
	exit ('Do not access this file directly.');

//261010.0545 Preserve the historical Auto-EOT class/file for third-party integrations while the EOT implementation lives in eot.inc.php.
if(!class_exists('c_ws_plugin__s2member_eot'))
	require_once dirname(__FILE__).'/eot.inc.php';

if(!class_exists('c_ws_plugin__s2member_auto_eots', FALSE))
	class_alias('c_ws_plugin__s2member_eot', 'c_ws_plugin__s2member_auto_eots');
