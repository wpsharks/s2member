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
         * Defuse key.
         *
         * @since 170418 Defuse.
         *
         * @param string $key A custom key.
         *
         * @return string Defuse encryption key.
         */
        public static function key($key = '')
        {
            $key = (string) $key;

            if (isset($key[0])) { // Custom?
                if (strpos($key, 'def00000') === 0) {
                    return $key; // Defuse key.
                }
                $sec_key        = c_ws_plugin__s2member_utils_encryption::key($key);
                $def_combo_keys = $GLOBALS['WS_PLUGIN__']['s2member']['o']['def_custom_combo_encryption_keys'];

                if ($sec_key && !empty($def_combo_keys[$sec_key])) {
                    return $def_combo_keys[$sec_key]; // Use existing key.
                }
                try { // Catch Defuse exceptions.
                    if (!($def_key = Key::createNewRandomKey()->saveToAsciiSafeString())) {
                        throw new Exception('Defuse keygen failure.');
                    }
                } catch (Throwable $Exception) {
                    throw new Exception($Exception->getMessage());
                }
                $def_combo_keys[$sec_key] = $def_key;
                array_unshift($def_combo_keys, 'update-signal');
                $options['ws_plugin__s2member_def_custom_combo_encryption_keys'] = $def_combo_keys;
                c_ws_plugin__s2member_menu_pages::update_all_options($options, true, false, false, false, false);
                //
            } else { // Default behavior is to use the configured key.
                $sec_key       = c_ws_plugin__s2member_utils_encryption::key();
                $def_combo_key = $GLOBALS['WS_PLUGIN__']['s2member']['o']['def_combo_encryption_key'];

                if ($sec_key && $def_combo_key && strpos($def_combo_key, $sec_key."\n") === 0
                        && ($def_key = str_replace($sec_key."\n", '', $def_combo_key))) {
                    return $def_key; // Use existing key.
                }
                try { // Catch Defuse exceptions.
                    if (!($def_key = Key::createNewRandomKey()->saveToAsciiSafeString())) {
                        throw new Exception('Defuse keygen failure.');
                    }
                } catch (Throwable $Exception) {
                    throw new Exception($Exception->getMessage());
                }
                $options['ws_plugin__s2member_def_combo_encryption_key'] = $sec_key."\n".$def_key;
                c_ws_plugin__s2member_menu_pages::update_all_options($options, true, false, false, false, false);
            }
            return $def_key;
        }

        /**
         * Defuse encryption.
         *
         * @since 170418 Defuse.
         *
         * @param string $string String to encrypt.
         * @param string $key    A custom key passed to `::key()`.
         *
         * @return string Encrypted string w/ a URL-safe base64 wrapper.
         */
        public static function encrypt($string, $key = '')
        {
            $string = (string) $string;

            if (!isset($string[0])) {
                return ''; // Not possible.
            } // Empty string is an empty string.

            try { // Catch Defuse exceptions.
                $Key           = Key::loadFromAsciiSafeString(self::key($key));
                $encrypted     = Crypto::encrypt($string, $Key, false);
                return $base64 = s::base64_url_safe_encode($encrypted);
            } catch (Throwable $Exception) {
                throw new Exception($Exception->getMessage());
            }
        }

        /**
         * Defuse decryption.
         *
         * @since 170418 Defuse.
         *
         * @param string $base64 String to decrypt.
         * @param string $key    A custom key passed to `::key()`.
         *
         * @return string Decrypted string.
         */
        public static function decrypt($base64, $key = '')
        {
            if (!is_string($base64) || !isset($base64[0])) {
                return ''; // Not possible.
            } // Fail when not a string or empty.

            try { // Catch Defuse exceptions.
                $Key           = Key::loadFromAsciiSafeString(self::key($key));
                $encrypted     = s::base64_url_safe_decode($base64);
                return $string = Crypto::decrypt($encrypted, $Key, false);
            } catch (Throwable $Exception) {
                return ''; // Soft failure.
            }
        }
    }
}
