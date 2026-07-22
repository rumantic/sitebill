<?php
/**
 * TextUtilTrait — thin delegation layer to TextUtil service.
 * Extracted from SiteBill class (sitebill.php).
 *
 * All pure text methods are delegated to TextUtil service.
 * Static methods (iconv, removeDirectory) delegate to TextUtil static methods.
 */
require_once __DIR__ . '/../service/TextUtil.php';

trait TextUtilTrait
{
    /** @var TextUtil|null Lazy-loaded TextUtil service instance */
    private $textUtil = null;

    /**
     * Get or create TextUtil service instance.
     *
     * @return TextUtil
     */
    public function getTextUtil()
    {
        if ($this->textUtil === null) {
            $this->textUtil = new TextUtil();
        }
        return $this->textUtil;
    }

    function sanitize($value, $flags)
    {
        return $this->getTextUtil()->sanitize($value, $flags);
    }

    function escape($text)
    {
        return $this->getTextUtil()->escape($text);
    }

    function htmlspecialchars($value, $flags = '')
    {
        return $this->getTextUtil()->htmlspecialcharsRecursive($value, (int)$flags);
    }

    /**
     * Replace &nbsp; entities to space
     * @param string $val
     * @return string
     */
    function replace_nbsp_symbols($val)
    {
        return TextUtil::replaceNbsp($val);
    }

    function htmlspecialchars_decode($value, $flags = '')
    {
        return $this->getTextUtil()->htmlspecialcharsDecodeRecursive($value, (int)$flags);
    }

    public static function iconv($in_charset, $out_charset, $string)
    {
        return TextUtil::iconvConvert($in_charset, $out_charset, $string);
    }

    public static function removeDirectory($dir, &$msg = array())
    {
        TextUtil::removeDirectory($dir, $msg);
    }

    function transliteMe($str)
    {
        return $this->getTextUtil()->transliterate($str);
    }

    /**
     * Clear emojis' codes from text
     * @param string $text
     * @return string
     */
    public function clearEmojisFromText($text)
    {
        return TextUtil::clearEmojis($text);
    }

    function reducer_text($text, $max_length = 500)
    {
        return TextUtil::reducerText($text, $max_length);
    }
}
