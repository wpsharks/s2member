<?php
// @codingStandardsIgnoreFile
/**
 * Encryption utilities.
 *
 * @since 3.5 Nearly the first release.
 */
if (!defined('WPINC')) { // MUST have.
    exit('Do not access this file directly.');
}
if (!class_exists('c_ws_plugin__s2member_utils_encryption')) {
    /**
     * Encryption utilities.
     *
     * @since 3.5 Nearly the first release.
     */
    class c_ws_plugin__s2member_utils_encryption
    {
        /**
         * Encryption key.
         *
         * @since 111106 Get key.
         *
         * @param string $key Custom key.
         *
         * @return string Encryption key.
         */
        public static function key($key = '')
        {
            if (($key = trim((string) $key))) {
                return $key;
            } elseif (($key = $GLOBALS['WS_PLUGIN__']['s2member']['o']['sec_encryption_key'])) {
                return $key;
            } elseif (($key = wp_salt())) {
                return $key;
            }
            return $key = md5($_SERVER['HTTP_HOST']);
        }

        /**
         * A unique, unguessable, non-numeric, caSe-insensitive key (20 chars max).
         *
         * @since 150124 Adding gift code generation.
         *
         * @note 32-bit systems usually have `PHP_INT_MAX` = `2147483647`.
         *    We limit `mt_rand()` to a max of `999999999`.
         *
         * @note A max possible length of 20 chars assumes this function
         *    will not be called after `Sat, 20 Nov 2286 17:46:39 GMT`.
         *    At which point a UNIX timestamp will grow in size.
         *
         * @note Key always begins with a `k` to prevent PHP's `is_numeric()`
         *    function from ever thinking it's a number in a different representation.
         *    See: <http://php.net/manual/en/function.is-numeric.php> for further details.
         *
         * @return string A unique, unguessable, non-numeric, caSe-insensitive key (20 chars max).
         */
        public static function uunnci_key_20_max()
        {
            $microtime_19_max = number_format(microtime(true), 9, '.', '');
            // e.g., `9999999999`.`999999999` (max decimals: `9`, max overall precision: `19`).
            // Assuming timestamp is never > 10 digits; i.e., before `Sat, 20 Nov 2286 17:46:39 GMT`.

            list($seconds_10_max, $microseconds_9_max) = explode('.', $microtime_19_max, 2);
            // e.g., `array(`9999999999`, `999999999`)`. Max total digits combined: `19`.

            $seconds_base36      = base_convert($seconds_10_max, '10', '36'); // e.g., max `9999999999`, to base 36.
            $microseconds_base36 = base_convert($microseconds_9_max, '10', '36'); // e.g., max `999999999`, to base 36.
            $mt_rand_base36      = base_convert(mt_rand(1, 999999999), '10', '36'); // e.g., max `999999999`, to base 36.
            $key                 = 'k'.$mt_rand_base36.$seconds_base36.$microseconds_base36; // e.g., `kgjdgxr4ldqpdrgjdgxr`.

            return $key; // Max possible value: `kgjdgxr4ldqpdrgjdgxr` (20 chars).
        }

        /**
         * Encrypt w/ best possible technique.
         *
         * @since 3.5 Nearly the first release.
         *
         * @param string    $string       String to encrypt.
         * @param string    $key          Optional custom encryption key.
         * @param bool      $w_md5_cs     Defaults to true. When true, an MD5 checksum.
         * @param bool|null $allow_defuse Allow Defuse encryption as a better alternative?
         *
         * @return string Encrypted string.
         */
        public static function encrypt($string = '', $key = '', $w_md5_cs = true, $allow_defuse = null)
        {
            $allow_defuse = isset($allow_defuse) ? $allow_defuse
                : apply_filters('c_ws_plugin__s2member_allow_defuse', true);

            if ($allow_defuse && version_compare(PHP_VERSION, '7.0.4', '>=')) {
                return c_ws_plugin__s2member_utils_defuse::encrypt($string, $key);
            } // This is a new/improved way of handling encryption.

            if (function_exists('mcrypt_encrypt')
                && in_array('rijndael-256', @mcrypt_list_algorithms())
                && in_array('cbc', @mcrypt_list_modes())) {
                //
                $string = is_string($string) ? $string : '';
                $string = isset($string[0]) ? '~r2|'.$string : '';

                $key = self::key($key); // Get encryption key.
                $key = substr($key, 0, @mcrypt_get_key_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC));
                $iv  = c_ws_plugin__s2member_utils_strings::random_str_gen(@mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC), false);

                if (isset($string[0]) && is_string($e = @mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $string, MCRYPT_MODE_CBC, $iv)) && isset($e[0])) {
                    $e = '~r2:'.$iv.($w_md5_cs ? ':'.md5($e) : '').'|'.$e;
                }
                return isset($e) && is_string($e) && isset($e[0])
                    ? ($base64 = c_ws_plugin__s2member_utils_strings::base64_url_safe_encode($e))
                    : ''; // Default to empty string.
            }
            return self::xencrypt($string, $key, $w_md5_cs);
        }

        /**
         * Decrypt w/ best possible technique.
         *
         * @since 3.5 Nearly the first release.
         *
         * @param string    $base64       String to decrypt (base64).
         * @param string    $key          Optional custom decryption key.
         * @param bool|null $allow_defuse Allow Defuse encryption as a better alternative?
         *
         * @return string Decrypted string, else empty string.
         */
        public static function decrypt($base64 = '', $key = '', $allow_defuse = null)
        {
            if (!is_string($base64) || !isset($base64[0])) {
                return ''; // Not possible.
            } // Fail when not a string or empty.

            $allow_defuse = isset($allow_defuse) ? $allow_defuse
                : apply_filters('c_ws_plugin__s2member_allow_defuse', true);

            if ($allow_defuse && version_compare(PHP_VERSION, '7.0.4', '>=')
                   && ($_d = c_ws_plugin__s2member_utils_defuse::decrypt($base64, $key))) {
                return $string = $_d; // Defuse success.
            } // This is a new/improved way of handling decryption.

            if (function_exists('mcrypt_decrypt')
                && in_array('rijndael-256', @mcrypt_list_algorithms())
                && in_array('cbc', @mcrypt_list_modes())) {
                //
                $e = c_ws_plugin__s2member_utils_strings::base64_url_safe_decode($base64);

                if (preg_match('/^~r2\:([a-zA-Z0-9]+)(?:\:([a-zA-Z0-9]+))?\|(.*)$/s', $e, $iv_md5_e)) {
                    $key = self::key($key); // Get encryption key.
                    $key = substr($key, 0, @mcrypt_get_key_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC));

                    if (isset($iv_md5_e[3][0]) && (empty($iv_md5_e[2]) || $iv_md5_e[2] === md5($iv_md5_e[3]))) {
                        $d = @mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $iv_md5_e[3], MCRYPT_MODE_CBC, $iv_md5_e[1]);
                    }
                    if (!isset($d)) { // Failed above?
                        return ''; // Empty string on failure.
                    } elseif (!strlen($d = preg_replace('/^~r2\|/', '', $d, 1, $r2)) || !$r2) {
                        return ''; // Empty string on failure.
                    }
                    return $string = rtrim($d, "\0\4");
                }
            }
            return self::xdecrypt($base64, $key);
        }

        /**
         * XOR two-way encryption/decryption, with a base64 wrapper.
         *
         * @since 3.5 Nearly the first release.
         *
         * @param string $string   A string of data to encrypt.
         * @param string $key      Optional. Key used for encryption. Defaults to the one configured for s2Member. Short of that, defaults to: ``wp_salt()``.
         * @param bool   $w_md5_cs Optional. Defaults to true. When true, an MD5 checksum is used in the encrypted string *(recommended)*.
         *
         * @return string Encrypted string.
         */
        public static function xencrypt($string = '', $key = '', $w_md5_cs = true)
        {
            $string = is_string($string) ? $string : '';
            $string = isset($string[0]) ? '~xe|'.$string : '';
            $key    = self::key($key); // Get encryption key.

            for ($i = 1, $e = ''; $i <= strlen($string); ++$i) {
                $char    = substr($string, $i - 1, 1);
                $keychar = substr($key, ($i % strlen($key)) - 1, 1);
                $e .= chr(ord($char) + ord($keychar));
            }
            $e             = isset($e[0]) ? '~xe'.($w_md5_cs ? ':'.md5($e) : '').'|'.$e : '';
            return $base64 = isset($e[0]) ? ($base64 = c_ws_plugin__s2member_utils_strings::base64_url_safe_encode($e)) : '';
        }

        /**
         * XOR decryption.
         *
         * @since 3.5 Nearly the first release.
         *
         * @param string $base64 String to decrypt (base64).
         * @param string $key    Optional custom decryption key.
         *
         * @return string Decrypted string.
         */
        public static function xdecrypt($base64 = '', $key = '')
        {
            if (!is_string($base64) || !isset($base64[0])) {
                return ''; // Not possible.
            } // Fail when not a string or empty.

            $e = c_ws_plugin__s2member_utils_strings::base64_url_safe_decode($base64);

            if (preg_match('/^~xe(?:\:([a-zA-Z0-9]+))?\|(.*)$/s', $e, $md5_e)) {
                $key = self::key($key); // Get encryption key.

                if (isset($md5_e[2][0]) && (empty($md5_e[1]) || $md5_e[1] === md5($md5_e[2]))) {
                    for ($i = 1, $d = ''; $i <= strlen($md5_e[2]); ++$i) {
                        $char    = substr($md5_e[2], $i - 1, 1);
                        $keychar = substr($key, ($i % strlen($key)) - 1, 1);
                        $d .= chr(ord($char) - ord($keychar));
                    } // Reverse XOR encryption.
                } // Else the checksum was not a match.

                if (!isset($d)) { // Failed above?
                    return ''; // Empty string on failure.
                } elseif (!strlen($d = preg_replace('/^~xe\|/', '', $d, 1, $xe)) || !$xe) {
                    return ''; // Empty string on failure.
                }
                return $string = $d; // Decryption success.
            }
            return ''; // Empty string on failure.
        }
    }
}
