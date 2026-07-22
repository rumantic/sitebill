<?php
/**
 * UrlTrait — thin delegation layer to UrlBuilder service
 *
 * Сохраняет обратную совместимость: все существующие вызовы через $this->...()
 * продолжают работать. Логика перенесена в UrlBuilder (apps/system/lib/service/).
 *
 * Для нового кода рекомендуется использовать UrlBuilder напрямую:
 *   $builder = new UrlBuilder($config, $trslashes, $request);
 *   $url = $builder->createUrl('realty123.html', true);
 */
require_once __DIR__ . '/../service/UrlBuilder.php';

trait UrlTrait
{
    /** @var UrlBuilder|null Lazy-loaded service instance */
    private $urlBuilder;

    /**
     * Get or create UrlBuilder service instance
     * @return UrlBuilder
     */
    protected function getUrlBuilder(): UrlBuilder
    {
        if ($this->urlBuilder === null) {
            $this->urlBuilder = new UrlBuilder(
                $this,
                self::$_trslashes ?? '/',
                self::$_request ?? []
            );
        }
        // Sync request context (might change after initRequest)
        $this->urlBuilder->setRequest(self::$_request ?? []);
        return $this->urlBuilder;
    }

    /**
     * Create url
     * @param string $path - Url request path include query string
     * @param boolean $absolute - Is url must be absolute or relative
     * @param boolean $monolang - Must url have locale prefix (ex. admin section need no locale prefixes)
     * @param string $locale - Url locale prefix (different from requested)
     * @return string
     */
    public function createUrlTpl($path, $absolute = false, $monolang = false, $locale = null)
    {
        return $this->getUrlBuilder()->createUrl($path, $absolute, $monolang, $locale);
    }

    /**
     * Smarty function for absolute url creation
     * @param array $params
     * @return string
     */
    public function absoluteurl($params)
    {
        return $this->getUrlBuilder()->absoluteUrl($params);
    }

    /**
     * Smarty function for relative url creation
     * @param array $params
     * @return string
     */
    public function relativeurl($params)
    {
        return $this->getUrlBuilder()->relativeUrl($params);
    }

    /**
     * Smarty function for url creation
     * @param array $params
     * @return string
     */
    public function formaturl($params)
    {
        return $this->getUrlBuilder()->formatUrl($params);
    }

    public function getUserHREF($rid, $external = false, $params = array())
    {
        return $this->getUrlBuilder()->getUserHREF($rid, $external, $params);
    }

    public function getRealtyHREF($rid, $external = false, $params = [], $query_string = [])
    {
        return $this->getUrlBuilder()->getRealtyHREF($rid, $external, $params, $query_string);
    }

    /*
     * return nonslashed full net url
     */
    public function getServerFullUrl($domain_only = false)
    {
        return $this->getUrlBuilder()->getServerFullUrl($domain_only);
    }

    public function go301($new_location)
    {
        $this->getUrlBuilder()->go301($new_location);
    }

    public static function getClearRequestURI($test_url = '')
    {
        // Static method — cannot delegate to instance service, keeps original logic
        if ($test_url === '') {
            $url = $_SERVER['REQUEST_URI'] ?? '';
            if (!is_null(@self::$_request['clearRequestUri'])) {
                return self::$_request['clearRequestUri'];
            }
        } else {
            $url = $test_url;
        }

        $url = urldecode($url);
        $url = str_replace('\\', '/', $url);
        $url = preg_replace('/\/(\/+)/', '', $url);

        $query_str_pos = strpos($url, '?');
        if (false !== $query_str_pos) {
            $REQUESTURIPATH = substr($url, 0, $query_str_pos);
        } else {
            $REQUESTURIPATH = $url;
        }

        $SConfig = SConfig::getInstance();

        if (1 === (int)$SConfig::getConfigValueStatic('apps.language.use_langs')) {
            if (@self::$_request['request_lang_prefix'] !== '') {
                $REQUESTURIPATH = preg_replace('/^(\/' . self::$_request['request_lang_prefix'] . ')/', '', $REQUESTURIPATH);
                $_SERVER['REQUEST_URI'] = $REQUESTURIPATH;
            }
        }

        if (preg_match('/(\/(\/+))/', $REQUESTURIPATH)) {
            return $REQUESTURIPATH;
        }

        if ('/' === $REQUESTURIPATH) {
            return '';
        }

        if (substr($REQUESTURIPATH, 0, 1) === '/') {
            $REQUESTURIPATH = substr($REQUESTURIPATH, 1);
        }
        if (substr($REQUESTURIPATH, -1, 1) === '/') {
            $REQUESTURIPATH = substr($REQUESTURIPATH, 0, strlen($REQUESTURIPATH) - 1);
        }

        if (SITEBILL_MAIN_URL !== '') {
            $REQUESTURIPATH = trim(preg_replace('/^' . trim(SITEBILL_MAIN_URL, '/') . '/', '', $REQUESTURIPATH), '/');
        }

        if ($test_url === '') {
            self::$_request['clearRequestUri'] = $REQUESTURIPATH;
        }

        return $REQUESTURIPATH;
    }

    /**
     * Convert internationalized domain name
     * @param string $url
     * @return string
     */
    public function urlIDNAConvert($url)
    {
        return $this->getUrlBuilder()->urlIDNAConvert($url);
    }
}
