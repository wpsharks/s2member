<?php
// @codingStandardsIgnoreFile
/**
 * Encryption utilities.
 *
 * @since 170418 Defuse.
 */
use Defuse\Crypto\Key;
use Defuse\Crypto\Crypto;
use c_ws_plugin__s2member_utils_strings as s;

if (!defined('WPINC')) { // MUST have.
    exit('Do not access this file directly.');
}
if (!class_exists('c_ws_plugin__s2member_utils_defuse')) {
    /**
     * Encryption utilities.
     *
     * @since 170418 Defuse.
     */
    class c_ws_plugin__s2member_utils_defuse
    {
        /**
         * Creates a one-way hash for a secret key.
         *
         * @since 260809
         *
         * @param string $secret_key Secret encryption key.
         * @return string Secret key hash, else an empty string.
         */
        private static function secret_key_hash($secret_key)
        {
            $secret_key = (string) $secret_key;

            // Identify a secret key without storing the secret key itself in the new mapping.
            return isset($secret_key[0]) ? s::hmac_sha256_sign('s2member:defuse:key-mapping', $secret_key) : '';
        }

        /**
         * Converts one legacy default Defuse key mapping.
         *
         * @since 260809
         *
         * @param string $legacy_mapping Legacy `secret-key\nDefuse-key` mapping.
         * @return array Hashed secret-key-to-Defuse-key mapping, else an empty array.
         */
        private static function convert_legacy_defuse_key_mapping($legacy_mapping)
        {
            $legacy_mapping = (string) $legacy_mapping;
            $separator_pos  = strrpos($legacy_mapping, "\ndef00000");

            if ($separator_pos === false) {
                return array();
            }
            $secret_key = substr($legacy_mapping, 0, $separator_pos);
            $defuse_key = substr($legacy_mapping, $separator_pos + 1);

            if (!isset($secret_key[0]) || !isset($defuse_key[0])) {
                return array();
            }
            // Validate the existing Defuse key before preserving its mapping.
            try {
                Key::loadFromAsciiSafeString($defuse_key);
            }
            // Catch Defuse exceptions and other Throwables on PHP 7+.
            catch (Throwable $Exception) {
                return array();
            }
            // Catch Defuse exceptions on PHP 5.6, where Throwable is unavailable.
            catch (Exception $Exception) {
                return array();
            }
            return array(self::secret_key_hash($secret_key) => $defuse_key);
        }

        /**
         * Migrates legacy Defuse key mappings to hashed mappings.
         *
         * Preserves the existing Defuse keys while creating hashed mappings for
         * the current default key, recovery history, and custom secret keys.
         * Legacy mappings remain untouched for rollback compatibility.
         *
         * @since 260809
         *
         * @return void
         */
        public static function migrate_legacy_defuse_key_mappings()
        {
            $options = array();

            // Migrate the current default mapping without changing its Defuse key.
            $legacy_mapping        = $GLOBALS['WS_PLUGIN__']['s2member']['o']['def_combo_encryption_key'];
            $converted_defuse_keys = self::convert_legacy_defuse_key_mapping($legacy_mapping);
            $defuse_keys           = $converted_defuse_keys ? $converted_defuse_keys : $GLOBALS['WS_PLUGIN__']['s2member']['o']['secret_key_to_defuse_key'];
            if ($converted_defuse_keys && $converted_defuse_keys !== $GLOBALS['WS_PLUGIN__']['s2member']['o']['secret_key_to_defuse_key']) {
                $defuse_keys_update = $defuse_keys;
                array_unshift($defuse_keys_update, 'update-signal');
                $options['ws_plugin__s2member_secret_key_to_defuse_key'] = $defuse_keys_update;
            }

            // Preserve default mappings as recovery-only history; normal decryption does not search it.
            $defuse_key_history = $defuse_keys ? array($defuse_keys) : array();
            foreach ($GLOBALS['WS_PLUGIN__']['s2member']['o']['def_combo_encryption_key_history'] as $_legacy_mapping) {
                $_defuse_keys = self::convert_legacy_defuse_key_mapping($_legacy_mapping);
                if ($_defuse_keys && !in_array($_defuse_keys, $defuse_key_history, true)) {
                    $defuse_key_history[] = $_defuse_keys;
                }
            }
            foreach ($GLOBALS['WS_PLUGIN__']['s2member']['o']['secret_key_to_defuse_key_history'] as $_defuse_keys) {
                if (is_array($_defuse_keys) && count($_defuse_keys) === 1 && !in_array($_defuse_keys, $defuse_key_history, true)) {
                    $defuse_key_history[] = $_defuse_keys;
                }
            }
            $defuse_key_history = array_slice($defuse_key_history, 0, 10);
            if ($defuse_key_history !== $GLOBALS['WS_PLUGIN__']['s2member']['o']['secret_key_to_defuse_key_history']) {
                $defuse_key_history_update = $defuse_key_history;
                array_unshift($defuse_key_history_update, 'update-signal');
                $options['ws_plugin__s2member_secret_key_to_defuse_key_history'] = $defuse_key_history_update;
            }

            // Migrate custom mappings without storing custom secret keys in the new mapping.
            $custom_defuse_keys = $GLOBALS['WS_PLUGIN__']['s2member']['o']['custom_secret_key_to_defuse_key'];
            foreach ($GLOBALS['WS_PLUGIN__']['s2member']['o']['def_custom_combo_encryption_keys'] as $_secret_key => $_defuse_key) {
                $_secret_key = (string) $_secret_key;
                $_defuse_key = (string) $_defuse_key;

                if (isset($_secret_key[0]) && isset($_defuse_key[0])) {
                    $custom_defuse_keys[self::secret_key_hash($_secret_key)] = $_defuse_key;
                }
            }
            if ($custom_defuse_keys !== $GLOBALS['WS_PLUGIN__']['s2member']['o']['custom_secret_key_to_defuse_key']) {
                array_unshift($custom_defuse_keys, 'update-signal');
                $options['ws_plugin__s2member_custom_secret_key_to_defuse_key'] = $custom_defuse_keys;
            }
            if ($options) {
                c_ws_plugin__s2member_menu_pages::update_all_options($options, true, false, false, false, false);
            }
        }

        /**
         * Gets an existing Defuse encryption key.
         *
         * @since 260809
         *
         * @param string $secret_key Optional custom encryption secret key, or an existing Defuse key.
         * @return string Defuse encryption key, else an empty string.
         */
        public static function get_defuse_key($secret_key = '')
        {
            $secret_key = (string) $secret_key;

            if (isset($secret_key[0]) && strpos($secret_key, 'def00000') === 0) {
                return $secret_key; // Preserve direct Defuse-key input supported by the original accessor.
            }
            $has_custom_secret_key = isset($secret_key[0]);
            $secret_key            = c_ws_plugin__s2member_utils_encryption::key($secret_key);
            $secret_key_hash       = self::secret_key_hash($secret_key);

            if ($has_custom_secret_key) {
                // Prefer the legacy custom mapping during the compatibility release.
                $legacy_custom_defuse_keys = $GLOBALS['WS_PLUGIN__']['s2member']['o']['def_custom_combo_encryption_keys'];
                if ($secret_key && !empty($legacy_custom_defuse_keys[$secret_key])) {
                    return $legacy_custom_defuse_keys[$secret_key];
                }
                // Fall back to the hashed custom mapping prepared for later releases.
                $custom_defuse_keys = $GLOBALS['WS_PLUGIN__']['s2member']['o']['custom_secret_key_to_defuse_key'];
                if ($secret_key_hash && !empty($custom_defuse_keys[$secret_key_hash])) {
                    return $custom_defuse_keys[$secret_key_hash];
                }
                return '';
            }
            // Prefer the legacy default mapping during the compatibility release.
            $legacy_mapping = $GLOBALS['WS_PLUGIN__']['s2member']['o']['def_combo_encryption_key'];
            if ($secret_key && $legacy_mapping && strpos($legacy_mapping, $secret_key."\n") === 0) {
                return substr($legacy_mapping, strlen($secret_key) + 1);
            }
            // Fall back to the hashed default mapping prepared for later releases.
            $defuse_keys = $GLOBALS['WS_PLUGIN__']['s2member']['o']['secret_key_to_defuse_key'];

            return ($secret_key_hash && !empty($defuse_keys[$secret_key_hash])) ? $defuse_keys[$secret_key_hash] : '';
        }

        /**
         * Creates and stores a new Defuse encryption key.
         *
         * @since 260809
         *
         * @param string $secret_key Optional custom encryption secret key.
         * @return string New Defuse encryption key.
         */
        public static function create_defuse_key($secret_key = '')
        {
            $secret_key            = (string) $secret_key;
            $has_custom_secret_key = isset($secret_key[0]);
            $secret_key            = c_ws_plugin__s2member_utils_encryption::key($secret_key);
            $secret_key_hash       = self::secret_key_hash($secret_key);
            $options               = array();

            // Generate a new Defuse encryption key.
            try {
                if (!($defuse_key = Key::createNewRandomKey()->saveToAsciiSafeString())) {
                    throw new Exception('Defuse keygen failure.');
                }
            }
            // Catch Defuse exceptions and other Throwables on PHP 7+.
            catch (Throwable $Exception) {
                throw new Exception($Exception->getMessage());
            }
            // Catch Defuse exceptions on PHP 5.6, where Throwable is unavailable.
            catch (Exception $Exception) {
                throw new Exception($Exception->getMessage());
            }
            if ($has_custom_secret_key) {
                $legacy_custom_defuse_keys = $GLOBALS['WS_PLUGIN__']['s2member']['o']['def_custom_combo_encryption_keys'];
                $custom_defuse_keys        = $GLOBALS['WS_PLUGIN__']['s2member']['o']['custom_secret_key_to_defuse_key'];

                // Dual-write legacy and hashed custom mappings for rollback compatibility.
                $legacy_custom_defuse_keys[$secret_key] = $defuse_key;
                $custom_defuse_keys[$secret_key_hash]    = $defuse_key;
                array_unshift($legacy_custom_defuse_keys, 'update-signal');
                array_unshift($custom_defuse_keys, 'update-signal');
                $options['ws_plugin__s2member_def_custom_combo_encryption_keys'] = $legacy_custom_defuse_keys;
                $options['ws_plugin__s2member_custom_secret_key_to_defuse_key']  = $custom_defuse_keys;
            } else {
                // Dual-write legacy and hashed default mappings for rollback compatibility.
                $defuse_keys = array($secret_key_hash => $defuse_key);
                array_unshift($defuse_keys, 'update-signal');
                $options['ws_plugin__s2member_def_combo_encryption_key'] = $secret_key."\n".$defuse_key;
                $options['ws_plugin__s2member_secret_key_to_defuse_key'] = $defuse_keys;
            }
            c_ws_plugin__s2member_menu_pages::update_all_options($options, true, false, false, false, false);

            return $defuse_key;
        }

        /**
         * Gets an existing Defuse key, or creates one when needed.
         *
         * @since 260809
         *
         * @param string $secret_key Optional custom encryption secret key, or an existing Defuse key.
         * @return string Defuse encryption key.
         */
        public static function get_or_create_defuse_key($secret_key = '')
        {
            // Keep lookup read-only; persist a Defuse key only when one must be created.
            return ($defuse_key = self::get_defuse_key($secret_key)) ? $defuse_key : self::create_defuse_key($secret_key);
        }

        /**
         * Gets or creates a Defuse encryption key.
         *
         * @since 170418 Defuse.
         * @since 260809 Retained as a compatibility wrapper around the explicit Defuse key routines.
         *
         * @param string $key Optional custom encryption secret key, or an existing Defuse key.
         * @return string Defuse encryption key.
         */
        public static function key($key = '')
        {
            //260809 Preserve the original public method while making new internal key handling explicit.
            return self::get_or_create_defuse_key($key);
        }

        /**
         * Defuse encryption.
         *
         * @since 170418 Defuse.
         *
         * @param string $string String to encrypt.
         * @param string $key    Optional custom encryption secret key, or an existing Defuse key.
         * @return string Encrypted string w/ a URL-safe base64 wrapper.
         */
        public static function encrypt($string, $key = '')
        {
            $string = (string) $string;

            if (!isset($string[0])) {
                return ''; // Not possible.
            } // Empty string is an empty string.

            try {
                //260809 Create a Defuse key only when encryption needs one.
                $defuse_crypto = Key::loadFromAsciiSafeString(self::get_or_create_defuse_key($key));
                $encrypted     = Crypto::encrypt($string, $defuse_crypto, false);
                return $base64 = s::base64_url_safe_encode($encrypted);
            } catch (Throwable $Exception) {
                throw new Exception($Exception->getMessage());
            }
            //260809 Catch Defuse exceptions on PHP 5.6, where Throwable is unavailable.
            catch (Exception $Exception) {
                throw new Exception($Exception->getMessage());
            }
        }

        /**
         * Defuse decryption.
         *
         * @since 170418 Defuse.
         *
         * @param string $base64 String to decrypt.
         * @param string $key    Optional custom encryption secret key, or an existing Defuse key.
         * @return string Decrypted string.
         */
        public static function decrypt($base64, $key = '')
        {
            if (!is_string($base64) || !isset($base64[0])) {
                return ''; // Not possible.
            } // Fail when not a string or empty.

            try {
                //260809 Keep decryption read-only by retrieving only an existing Defuse key.
                $defuse_crypto = Key::loadFromAsciiSafeString(self::get_defuse_key($key));
                $encrypted     = s::base64_url_safe_decode($base64);
                return $string = Crypto::decrypt($encrypted, $defuse_crypto, false);
            } catch (Throwable $Exception) {
                return ''; // Soft failure.
            }
            //260809 Catch Defuse exceptions on PHP 5.6, where Throwable is unavailable.
            catch (Exception $Exception) {
                return ''; // Soft failure.
            }
        }
    }
}
