<?php
/**
 * UrlBuilder — standalone URL construction service
 *
 * Извлечён из SiteBill::UrlTrait (Этап 2 рефакторинга).
 * Может использоваться самостоятельно (без наследования от SiteBill).
 *
 * Зависимости:
 *  - ConfigProvider (объект с методом getConfigValue)
 *  - Статический контекст SiteBill ($_trslashes, $_request) — передаётся извне
 *
 * @author Refactoring — auto-extracted from UrlTrait
 */

class UrlBuilder
{
    /** @var object Любой объект с методом getConfigValue($key) */
    private $config;

    /** @var string Trailing slash/empty depending on config */
    private $trslashes;

    /** @var array Request context (clearRequestUri, request_lang_prefix, locale) */
    private $request;

    /**
     * @param object $config  Object with getConfigValue($key) method
     * @param string $trslashes  Trailing slashes setting ('' or '/')
     * @param array  $request  Request context array
     */
    public function __construct($config, string $trslashes = '/', array $request = [])
    {
        $this->config = $config;
        $this->trslashes = $trslashes;
        $this->request = $request;
    }

    /**
     * Update request context (e.g. after initRequest)
     */
    public function setRequest(array $request): void
    {
        $this->request = $request;
    }

    /**
     * Create URL with optional locale prefix, absolute/relative, etc.
     *
     * @param string $path      URL request path including query string
     * @param bool   $absolute  Whether URL must be absolute
     * @param bool   $monolang  Whether URL should skip locale prefix
     * @param string|null $locale  Explicit locale prefix (null = auto-detect)
     * @return string
     */
    public function createUrl(string $path, bool $absolute = false, bool $monolang = false, ?string $locale = null): string
    {
        $trslashes = $this->trslashes;
        $alias = '';
        $hash = '';
        $query = '';

        if ($path === '#') {
            return $path;
        }

        $pathparts = explode('#', $path);
        if (isset($pathparts[1])) {
            $hash = $pathparts[1];
        }
        $path = $pathparts[0];

        $pathparts = explode('?', $path);
        if (isset($pathparts[0])) {
            $alias = $pathparts[0];
        }
        if (isset($pathparts[1])) {
            $query = $pathparts[1];
        }

        $alias = trim($alias, '/');
        if ($alias === '#') {
            return $alias . (isset($query) && $query !== '' ? '?' . $query : '');
        }

        $parts = [];
        if (!$monolang) {
            if (!is_null($locale) && $locale !== '') {
                $parts[] = $locale;
            } elseif (!is_null($locale) && $locale === '') {
                // explicit empty locale — skip prefix
            } elseif (isset($this->request['request_lang_prefix']) && $this->request['request_lang_prefix'] !== '') {
                $parts[] = $this->request['request_lang_prefix'];
            }
        }

        if ($alias !== '') {
            if (false !== strpos($alias, '.')) {
                $trslashes = '';
            }
            $parts[] = $alias;
        }

        $_alias = (!empty($parts) ? implode('/', $parts) . $trslashes : '');

        if ($absolute) {
            $alias = $this->getServerFullUrl() . ($_alias !== '' || $query !== '' || $hash !== '' ? '/' : '');
        } else {
            $alias = SITEBILL_MAIN_URL . '/';
        }

        $alias = $alias . ($_alias !== '' ? $_alias : '') . ($query !== '' ? '?' . $query : '') . ($hash !== '' ? '#' . $hash : '');

        return $alias;
    }

    /**
     * Build absolute URL
     *
     * @param array $params  ['path' => ..., 'monolang' => 0|1, 'locale' => ...]
     * @return string
     */
    public function absoluteUrl(array $params): string
    {
        $path = $params['path'];
        $monolang = isset($params['monolang']) && $params['monolang'] == 1;
        $locale = isset($params['locale']) ? trim($params['locale']) : null;
        return $this->createUrl($path, true, $monolang, $locale);
    }

    /**
     * Build relative URL
     *
     * @param array $params  ['path' => ..., 'monolang' => 0|1, 'locale' => ...]
     * @return string
     */
    public function relativeUrl(array $params): string
    {
        $path = $params['path'];
        $monolang = isset($params['monolang']) && $params['monolang'] == 1;
        $locale = isset($params['locale']) ? trim($params['locale']) : null;
        return $this->createUrl($path, false, $monolang, $locale);
    }

    /**
     * Format URL (supports 'abs' param to toggle absolute/relative)
     *
     * @param array $params ['path' => ..., 'abs' => 0|1, 'monolang' => 0|1, 'locale' => ...]
     * @return string
     */
    public function formatUrl(array $params): string
    {
        $path = $params['path'];
        $absolute = isset($params['abs']) && $params['abs'] == 1;
        $monolang = isset($params['monolang']) && $params['monolang'] == 1;
        $locale = isset($params['locale']) ? trim($params['locale']) : null;
        return $this->createUrl($path, $absolute, $monolang, $locale);
    }

    /**
     * Build user profile URL
     *
     * @param int  $rid       User record ID
     * @param bool $external  Whether to create absolute URL
     * @param array $params   Additional parameters
     * @return string
     */
    public function getUserHREF($rid, bool $external = false, array $params = []): string
    {
        $config = $this->config;

        if (false === $config->getConfigValue('apps.seo.user_html_end')) {
            $use_html_end = true;
        } else {
            $use_html_end = (1 === intval($config->getConfigValue('apps.seo.user_html_end')));
        }

        if (false === $config->getConfigValue('apps.seo.user_slash_divider')) {
            $use_slash_divider = false;
        } else {
            $use_slash_divider = (1 === intval($config->getConfigValue('apps.seo.user_slash_divider')));
        }

        $user_alias = trim($config->getConfigValue('apps.seo.user_alias'));
        if ($user_alias === '' || $user_alias === false) {
            $user_alias = 'user';
        }

        if ($use_slash_divider) {
            $user_alias = $user_alias . '/' . $rid;
        } else {
            $user_alias = $user_alias . $rid;
        }

        if ($use_html_end) {
            $user_alias = $user_alias . '.html';
        } else {
            $user_alias = $user_alias . $this->trslashes;
        }

        if ($config->getConfigValue('apps.agents.enable')) {
            $user_alias = $config->getConfigValue('apps.agents.alias') . '/' . $rid . $this->trslashes;
        }

        return $this->createUrl($user_alias, $external);
    }

    /**
     * Build realty item URL
     *
     * @param int   $rid           Record ID
     * @param bool  $external      Whether to create absolute URL
     * @param array $params        ['topic_id' => ..., 'alias' => ...]
     * @param array $query_string  Query string parameters
     * @return string
     */
    public function getRealtyHREF($rid, bool $external = false, array $params = [], array $query_string = []): string
    {
        $config = $this->config;
        $parts = [];

        $topic_id = isset($params['topic_id']) ? (int)$params['topic_id'] : 0;
        $alias = isset($params['alias']) ? $params['alias'] : '';

        $realty_alias = trim($config->getConfigValue('apps.seo.realty_alias'));
        if ($realty_alias === '' || $realty_alias === false) {
            $realty_alias = 'realty';
        }

        if (1 == $config->getConfigValue('apps.seo.level_enable')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
            $Structure_Manager = new \Structure_Manager();
            $category_structure = $Structure_Manager->loadCategoryStructure();
            if (isset($category_structure['catalog'][$topic_id]) && $category_structure['catalog'][$topic_id]['url'] !== '') {
                $parts[] = $category_structure['catalog'][$topic_id]['url'];
            }
        }

        if (1 == $config->getConfigValue('apps.seo.data_alias_enable') && $alias !== '') {
            $parts[] = $alias;
        } elseif (1 == $config->getConfigValue('apps.seo.html_prefix_enable')) {
            $parts[] = $realty_alias . $rid . '.html';
        } else {
            $parts[] = $realty_alias . $rid;
        }

        $href = $this->createUrl(implode('/', $parts), $external);
        if (!empty($query_string)) {
            $href .= '?' . http_build_query($query_string);
        }
        return $href;
    }

    /**
     * Get full server URL (protocol + host + main URL)
     *
     * @param bool $domain_only  If true, skip SITEBILL_MAIN_URL suffix
     * @return string
     */
    public function getServerFullUrl(bool $domain_only = false): string
    {
        $HTTP_HOST = '';
        if (defined('HTTP_HOST') && HTTP_HOST !== '') {
            $HTTP_HOST = HTTP_HOST;
        } elseif (!isset($_SERVER['HTTP_HOST']) && defined('HTTP_HOST')) {
            $HTTP_HOST = HTTP_HOST;
        } else {
            $HTTP_HOST = $_SERVER['HTTP_HOST'] ?? 'localhost';
        }

        $protocol = (1 === (int)$this->config->getConfigValue('work_on_https')) ? 'https' : 'http';
        return $protocol . '://' . $HTTP_HOST . (!$domain_only ? SITEBILL_MAIN_URL : '');
    }

    /**
     * Send 301 redirect
     *
     * @param string $new_location
     */
    public function go301(string $new_location): void
    {
        $sapi_name = php_sapi_name();
        if ($sapi_name === 'cgi' || $sapi_name === 'cgi-fcgi') {
            header('Status: 301 Moved Permanently');
        } else {
            header($_SERVER['SERVER_PROTOCOL'] . ' 301 Moved Permanently');
        }
        header('Location: ' . $new_location);
        exit();
    }

    /**
     * Get clean request URI (without query string, lang prefix, main url prefix)
     *
     * @param string $test_url  Optional URL to parse instead of $_SERVER['REQUEST_URI']
     * @return string
     */
    public function getClearRequestURI(string $test_url = ''): string
    {
        if ($test_url === '') {
            $url = $_SERVER['REQUEST_URI'] ?? '';
            if (!is_null($this->request['clearRequestUri'] ?? null)) {
                return $this->request['clearRequestUri'];
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

        $SConfig = \SConfig::getInstance();

        if (1 === (int)$SConfig::getConfigValueStatic('apps.language.use_langs')) {
            $request_lang_prefix = $this->request['request_lang_prefix'] ?? '';
            if ($request_lang_prefix !== '') {
                $REQUESTURIPATH = preg_replace('/^(\/' . $request_lang_prefix . ')/', '', $REQUESTURIPATH);
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
            $this->request['clearRequestUri'] = $REQUESTURIPATH;
        }

        return $REQUESTURIPATH;
    }

    /**
     * Convert internationalized domain name (IDN)
     *
     * @param string $url
     * @return string
     */
    public function urlIDNAConvert(string $url): string
    {
        $url = \SiteBill::iconv(SITE_ENCODING, 'utf-8', $url);

        $probe = parse_url($url, PHP_URL_HOST);
        if ($probe) {
            $domain = $probe;
        } else {
            $domain = $url;
        }

        $encoded_domain = '';
        if (class_exists(\Algo26\IdnaConvert\ToIdn::class)) {
            $IDN = new \Algo26\IdnaConvert\ToIdn();
            $encoded_domain = $IDN->convert($domain);
        } else {
            include_once(SITEBILL_APPS_DIR . '/third/idna_convert/idna_convert.class.php');
            $converter = new \idna_convert();
            $encoded_domain = $converter->encode($domain);
        }

        if ($encoded_domain !== '') {
            $url = str_replace($domain, $encoded_domain, $url);
        }
        return $url;
    }
}
