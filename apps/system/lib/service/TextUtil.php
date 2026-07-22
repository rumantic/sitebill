<?php
/**
 * TextUtil — standalone text utility service extracted from TextUtilTrait.
 *
 * All methods are pure (no DB, no external dependencies).
 * Accepts encoding as constructor parameter (defaults to SITE_ENCODING constant).
 *
 * @package SiteBill\Service
 */

class TextUtil
{
    /** @var string Character encoding */
    private $encoding;

    /** @var TextUtil|null Singleton instance */
    private static $instance = null;

    /**
     * @param string|null $encoding Character encoding (defaults to SITE_ENCODING constant)
     */
    public function __construct($encoding = null)
    {
        if ($encoding !== null) {
            $this->encoding = $encoding;
        } elseif (defined('SITE_ENCODING')) {
            $this->encoding = SITE_ENCODING;
        } else {
            $this->encoding = 'UTF-8';
        }
    }

    /**
     * Get singleton instance.
     *
     * @return TextUtil
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Sanitize a value using htmlspecialchars.
     *
     * @param mixed $value String or array to sanitize
     * @param int $flags htmlspecialchars flags
     * @return mixed
     */
    public function sanitize($value, $flags)
    {
        if (is_array($value)) {
            $value = $this->htmlspecialcharsRecursive($value, $flags);
        } else {
            $value = htmlspecialchars($this->escape($value), $flags, $this->encoding);
        }
        return $value;
    }

    /**
     * Escape text (identity function, override point).
     *
     * @param string $text
     * @return string
     */
    public function escape($text)
    {
        return $text;
    }

    /**
     * Recursively apply htmlspecialchars to a value.
     *
     * @param mixed $value String or array
     * @param int $flags htmlspecialchars flags (default: ENT_COMPAT | ENT_HTML401)
     * @return mixed
     */
    public function htmlspecialcharsRecursive($value, $flags = 0)
    {
        if ($flags === 0) {
            $flags = ENT_COMPAT | ENT_HTML401;
        }
        if (is_array($value)) {
            if (count($value) > 0) {
                foreach ($value as $ak => $av) {
                    if (is_array($av)) {
                        $value[$ak] = $this->htmlspecialcharsRecursive($av);
                    } else {
                        $value[$ak] = $this->escape(htmlspecialchars($av, $flags, $this->encoding));
                    }
                }
            }
        } else {
            $value = $this->escape(htmlspecialchars($value, $flags, $this->encoding));
        }
        return $value;
    }

    /**
     * Replace &nbsp; entities with space.
     *
     * @param string $val
     * @return string
     */
    public static function replaceNbsp($val)
    {
        return str_replace(['&nbsp;'], [' '], $val);
    }

    /**
     * Recursively apply htmlspecialchars_decode to a value.
     *
     * @param mixed $value String or array
     * @param int $flags htmlspecialchars flags
     * @return mixed
     */
    public function htmlspecialcharsDecodeRecursive($value, $flags = 0)
    {
        if ($flags === 0) {
            if (defined('ENT_HTML401')) {
                $flags = ENT_COMPAT | ENT_HTML401;
            } else {
                $flags = ENT_COMPAT;
            }
        }
        if (is_array($value)) {
            if (count($value) > 0) {
                foreach ($value as $ak => $av) {
                    if (is_array($av)) {
                        $value[$ak] = $this->htmlspecialcharsDecodeRecursive($av);
                    } else {
                        $value[$ak] = htmlspecialchars_decode($av, $flags);
                    }
                }
            }
        } else {
            $value = htmlspecialchars_decode($value, $flags);
        }
        return $value;
    }

    /**
     * Convert encoding (wrapper for iconv).
     *
     * @param string $in_charset Source encoding
     * @param string $out_charset Target encoding
     * @param string $string Input string
     * @return string
     */
    public static function iconvConvert($in_charset, $out_charset, $string)
    {
        if (strtolower($in_charset) == strtolower($out_charset)) {
            return $string;
        } else {
            return iconv($in_charset, $out_charset . '//IGNORE', $string);
        }
    }

    /**
     * Recursively remove a directory and its contents.
     *
     * @param string $dir Directory path
     * @param array &$msg Error messages accumulator
     */
    public static function removeDirectory($dir, &$msg = array())
    {
        $files = scandir($dir);

        if (count($files) > 2) {
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    if (is_dir($dir . '/' . $file)) {
                        self::removeDirectory($dir . '/' . $file, $msg);
                    } elseif (is_writable($dir . '/' . $file)) {
                        @unlink($dir . '/' . $file);
                    } else {
                        $msg[] = 'Файл/директория ' . $file . ' не удален. Удалите его самостоятельно.';
                    }
                }
            }
        }

        if (is_writable($dir)) {
            rmdir($dir);
        } else {
            $msg[] = 'Файл/директория ' . $dir . ' не удален. Удалите его самостоятельно.';
        }
    }

    /**
     * Transliterate a string (Cyrillic, Latin diacritics → ASCII slug).
     *
     * @param string $str Input string
     * @return string Transliterated slug
     */
    public function transliterate($str)
    {
        $str = str_replace(
            array(',', '.', '/', '\\', '"', '\'', '~', '!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '_', '+', '|', ';', '?', '<', '>', '`', '[', ']', '{', '}', '№'),
            '',
            $str
        );
        $str = mb_strtolower($str, $this->encoding);
        $tr = array(
            "а" => "a", "б" => "b",
            "в" => "v", "г" => "g", "д" => "d", "е" => "e", "ё" => "e", "ж" => "j",
            "з" => "z", "и" => "i", "й" => "y", "і" => "i", "ї" => "yi", "к" => "k", "л" => "l",
            "м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r",
            "с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "h",
            "ц" => "ts", "ч" => "ch", "ш" => "sh", "щ" => "sch", "ъ" => "y",
            "ы" => "i", "ь" => "", "э" => "e", "ю" => "yu", "я" => "ya", "і" => "i",
            "А" => "a", "Б" => "b",
            "В" => "v", "Г" => "g", "Д" => "d", "Е" => "e", "Ё" => "e", "Є" => "ye", "Ж" => "j",
            "З" => "z", "И" => "i", "Й" => "y", "І" => "i", "Ї" => "yi", "К" => "k", "Л" => "l",
            "М" => "m", "Н" => "n", "О" => "o", "П" => "p", "Р" => "r",
            "С" => "s", "Т" => "t", "У" => "u", "Ф" => "f", "Х" => "h",
            "Ц" => "ts", "Ч" => "ch", "Ш" => "sh", "Щ" => "sch", "Ъ" => "y",
            "Ы" => "i", "Ь" => "", "Э" => "e", "Ю" => "yu", "Я" => "ya", "І" => "i",
            " " => "-", 'Š' => 'S', 'š' => 's', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
            'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U',
            'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y'
        );

        $str = strtr(mb_strtolower($str, $this->encoding), $tr);
        $str = preg_replace('/([^a-z0-9-_])/', '', $str);
        $str = preg_replace('/(-+)/', '-', $str);
        return $str;
    }

    /**
     * Clear emoji codes from text.
     *
     * @param string $text
     * @return string
     */
    public static function clearEmojis($text)
    {
        // Emoticons
        $text = preg_replace('/[\x{1F600}-\x{1F64F}]/u', '', $text);
        // Miscellaneous Symbols and Pictographs
        $text = preg_replace('/[\x{1F300}-\x{1F5FF}]/u', '', $text);
        // Transport And Map Symbols
        $text = preg_replace('/[\x{1F680}-\x{1F6FF}]/u', '', $text);
        // Miscellaneous Symbols
        $text = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $text);
        // Dingbats
        $text = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $text);
        return $text;
    }

    /**
     * Truncate text and wrap in a popover div.
     *
     * @param string $text Input text
     * @param int $max_length Maximum length before truncation
     * @return string
     */
    public static function reducerText($text, $max_length = 500)
    {
        if (strlen($text) > $max_length) {
            $text = '<div 
                style="display: block; width: 100%; overflow: hidden;"
                rel="popover" class="tooltipe_block" data-content="' . strip_tags($text) . '"
                >' . substr(strip_tags($text), 0, $max_length) . '</div>';
        }
        return $text;
    }

    /**
     * Get the encoding this instance uses.
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }
}
