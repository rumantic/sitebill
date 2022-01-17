<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Service menu backend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class menu_admin extends Object_Manager {

    private $pages = array();

    /**
     * Constructor
     */
    function __construct() {
        $this->SiteBill();
    }

    public function _preload() {
        $this->loadMenus();
    }

    public function loadMenus() {
        $ra = array();
        $DBC = DBC::getInstance();

        $query = "SELECT ms.*, m.tag, m.name as menu_title FROM " . DB_PREFIX . "_menu m, " . DB_PREFIX . "_menu_structure ms WHERE m.menu_id=ms.menu_id ORDER BY ms.sort_order";
        $stmt = $DBC->query($query);
        if ($stmt) {
            $trailing_slashe = '/';
            if (1 == (int) $this->getConfigValue('apps.seo.no_trailing_slashes')) {
                $trailing_slashe = '';
            }
            while ($ar = $DBC->fetch($stmt)) {
                if (isset($ar['name_' . Multilanguage::get_current_language()]) && $ar['name_' . Multilanguage::get_current_language()] != '') {
                    $ar['name'] = $ar['name_' . Multilanguage::get_current_language()];
                }
                $ar['url'] = trim($ar['url']);
                if ($ar['url'] != '' && 0 !== strpos($ar['url'], 'http:') && 0 !== strpos($ar['url'], 'https:')) {
                    $ar['url'] = $this->createUrlTpl($ar['url']);
                    //$ar['url'] = SITEBILL_MAIN_URL . (self::$current_lang_prefix != '' ? '/'.self::$current_lang_prefix : '') . '/' . trim($ar['url'], '/') . ((false === strpos($ar['url'], '.') && $ar['url'] != '#' && false === strpos($ar['url'], '?')) ? $trailing_slashe : '');
                }
                $ra[$ar['tag']][] = $ar;
            }
        }
        //echo '<pre>';
        //print_r($ra);
        //exit();
        if (!empty($ra)) {
            foreach ($ra as $tag => $menu_structure) {
                $this->template->assign($tag, $menu_structure);
                $tag_title = $tag . '_title';
                $this->template->assign($tag_title, $menu_structure[0]['menu_title']);
            }
        }
        return $ra;
    }

    public function sitemap_pages_count($sitemap) {
        $cnt = 0;
        $DBC = DBC::getInstance();
        $query = 'SELECT `url` FROM ' . DB_PREFIX . '_menu_structure WHERE `url` <> ?';
        $stmt = $DBC->query($query, array(''));
        $domain = $_SERVER['HTTP_HOST'];
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $url = trim($ar['url']);
                $url = trim(str_replace('\\', '/', $url), '/');
                if ($url == '' || $url == '#') {
                    $url = '';
                } elseif (preg_match('/^(http:|https:)/', $url) && preg_match('/' . $domain . '/', $url)) {
                    $url = trim($url);
                } elseif (preg_match('/^(http:|https:)/', $url)) {
                    $url = '';
                } elseif (preg_match('/^' . $domain . '/', $url)) {
                    $url = trim(preg_replace('/^(' . $domain . ')/', '', $url), '/');
                } else {
                    //explode
                }
                if ($url != '') {
                    $cnt += 1;
                }
            }
        }
        if($cnt > 0){
            $cnt = intval(ceil($cnt/$sitemap->getPerPageCount()));
        }
        return $cnt;
    }

    public function sitemap($sitemap) {
        $urls = array();

        $priority = $this->getConfigValue('apps.sitemap.priority.menu');
        $changefreq = $this->getConfigValue('apps.sitemap.changefreq.menu');

        $DBC = DBC::getInstance();
        $query = 'SELECT url FROM ' . DB_PREFIX . '_menu_structure';
        $stmt = $DBC->query($query);
        $domain = $_SERVER['HTTP_HOST'];
        if ($stmt) {
            if (1 == (int) $this->getConfigValue('apps.seo.no_trailing_slashes')) {
                $trailing_slashe = '';
            } else {
                $trailing_slashe = '/';
            }
            while ($ar = $DBC->fetch($stmt)) {
                $url = trim($ar['url']);
                //echo $url.'<br>';

                $url = trim(str_replace('\\', '/', $url), '/');
                if ($url == '' || $url == '#') {
                    $url = '';
                } elseif (preg_match('/^(http:|https:)/', $url) && preg_match('/' . $domain . '/', $url)) {
                    //$url=trim(preg_replace('/^(((http:|https:)\/\/?)'.$domain.')/', '', $url), '/');
                    $url = trim($url);
                    /* if(parse_url($url, PHP_URL_HOST)==$domain){
                      $url=preg_replace('/^(((http:|https:)\/\/?)'.$domain.')/', '', $url);
                      }else{
                      $url='';
                      } */
                } elseif (preg_match('/^(http:|https:)/', $url)) {
                    $url = '';
                } elseif (preg_match('/^' . $domain . '/', $url)) {
                    $url = trim(preg_replace('/^(' . $domain . ')/', '', $url), '/');
                } else {
                    //explode
                }
                if ($url != '') {
                    if (preg_match('/^(http:|https:)/', $url)) {
                        //$url=SITEBILL_MAIN_URL.'/'.$url;
                    } else {
                        if (strpos($url, '.')) {
                            $url = SITEBILL_MAIN_URL . '/' . $url;
                        } else {
                            $url = SITEBILL_MAIN_URL . '/' . $url . $trailing_slashe;
                        }
                    }
                    $urls[] = array('url' => $url, 'changefreq' => $sitemap->validateFrequency($changefreq), 'priority' => $sitemap->validatePriority($priority));
                }
                //echo $url.'<br>';
                //echo '<hr>';
                /* if(trim($ar['url'])!='' && trim($ar['url'])!='#'){
                  if(preg_match('/^(http:|https:)/', $ar['url']) && parse_url($u['url'], PHP_URL_HOST)==$domain){

                  }else{

                  }
                  $url=trim(str_replace('\\', '/', $ar['url']),'/');

                  } */
            }
        }

        return $urls;
    }

    function migrations () {
        return [
            "ALTER TABLE " . DB_PREFIX . "_menu_structure ADD column action varchar(255) NOT NULL DEFAULT ''",
            "ALTER TABLE " . DB_PREFIX . "_menu_structure ADD column icon varchar(255) NOT NULL DEFAULT ''",
            "ALTER TABLE " . DB_PREFIX . "_menu_structure ADD column params text NOT NULL DEFAULT ''",
        ];
    }

}
