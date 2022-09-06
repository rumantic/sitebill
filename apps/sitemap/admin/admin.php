<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Sitemap admin backend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class sitemap_admin extends Object_Manager {

    private $urls = array();
    private $site_link;
    private $priority;
    private $changefreq;
    private $output_file;

    /*
     * Temporally added
     * Will exist only for new System ver. 3.0.42
     */

    public function _getServerFullUrl($domain_only = false) {
        return (1 === (int) $this->getConfigValue('work_on_https') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . (!$domain_only ? SITEBILL_MAIN_URL : '');
    }

    //private $action='sitemap';
    /**
     * Constructor
     */
    function __construct($realty_type = false) {
        parent::__construct();
        Multilanguage::appendAppDictionary('sitemap');
        $this->checkConfiguration();
        $this->action = 'sitemap';
        //$this->site_link='http://'.$_SERVER['SERVER_NAME'].(SITEBILL_MAIN_URL!='' ? SITEBILL_MAIN_URL.'/' : '/');
        $this->site_link = $this->_getServerFullUrl(true);
        $this->output_file = SITEBILL_DOCUMENT_ROOT . '/sitemap.xml';

        $changefreq_values = array(
            '1' => array('always', 'всегда'),
            '2' => array('hourly', 'ежечасно'),
            '3' => array('daily', 'ежедневно'),
            '4' => array('weekly', 'еженедельно'),
            '5' => array('monthly', 'ежемесячно'),
            '6' => array('yearly', 'ежегодно'),
            '0' => array('never', 'никогда')
        );

        $this->priority['news'] = str_replace(',', '.', $this->getConfigValue('apps.sitemap.priority.news'));

        $this->priority['topic'] = str_replace(',', '.', $this->getConfigValue('apps.sitemap.priority.topic'));

        $this->priority['country'] = str_replace(',', '.', $this->getConfigValue('apps.sitemap.priority.country'));
        $this->priority['city'] = str_replace(',', '.', $this->getConfigValue('apps.sitemap.priority.city'));

        $this->priority['page'] = str_replace(',', '.', $this->getConfigValue('apps.sitemap.priority.page'));

        $this->priority['menu'] = str_replace(',', '.', $this->getConfigValue('apps.sitemap.priority.menu'));

        $this->priority['data'] = str_replace(',', '.', $this->getConfigValue('apps.sitemap.priority.data'));

        $this->priority['company'] = str_replace(',', '.', $this->getConfigValue('apps.sitemap.priority.company'));

        $this->priority['company_topic'] = str_replace(',', '.', $this->getConfigValue('apps.sitemap.priority.company_topic'));

        $this->changefreq['news'] = $changefreq_values[((int) $this->getConfigValue('apps.sitemap.changefreq.news') < 7 ? (int) $this->getConfigValue('apps.sitemap.changefreq.news') : '6')][0];
        $this->changefreq['topic'] = $changefreq_values[((int) $this->getConfigValue('apps.sitemap.changefreq.topic') < 7 ? (int) $this->getConfigValue('apps.sitemap.changefreq.topic') : '6')][0];
        $this->changefreq['country'] = $changefreq_values[((int) $this->getConfigValue('apps.sitemap.changefreq.country') < 7 ? (int) $this->getConfigValue('apps.sitemap.changefreq.country') : '6')][0];
        $this->changefreq['city'] = $changefreq_values[((int) $this->getConfigValue('apps.sitemap.changefreq.city') < 7 ? (int) $this->getConfigValue('apps.sitemap.changefreq.city') : '6')][0];
        $this->changefreq['page'] = $changefreq_values[((int) $this->getConfigValue('apps.sitemap.changefreq.page') < 7 ? (int) $this->getConfigValue('apps.sitemap.changefreq.page') : '6')][0];
        $this->changefreq['menu'] = $changefreq_values[((int) $this->getConfigValue('apps.sitemap.changefreq.menu') < 7 ? (int) $this->getConfigValue('apps.sitemap.changefreq.menu') : '6')][0];

        $this->changefreq['data'] = $changefreq_values[((int) $this->getConfigValue('apps.sitemap.changefreq.data') < 7 ? (int) $this->getConfigValue('apps.sitemap.changefreq.data') : '6')][0];

        $this->changefreq['company'] = $changefreq_values[((int) $this->getConfigValue('apps.sitemap.changefreq.company') < 7 ? (int) $this->getConfigValue('apps.sitemap.changefreq.company') : '6')][0];

        $this->changefreq['company_topic'] = $changefreq_values[((int) $this->getConfigValue('apps.sitemap.changefreq.company_topic') < 7 ? (int) $this->getConfigValue('apps.sitemap.changefreq.company_topic') : '6')][0];


        //print_r($this->priority);
        //print_r($this->changefreq);
    }

    function main() {
        $rs = $this->getTopMenu();
        return $rs;
    }

    /*
     * Build sitemap index file
     */

    protected function createSitemapIndexFile($file_name, $urls) {
        $output_file = $file_name;
        $ret = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $ret .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        if (count($urls) > 0) {

            foreach ($urls as $u) {
                $ret .= '<sitemap>' . "\n";
                $ret .= '<loc>' . $this->createUrlTpl($u, true) . '</loc>' . "\n";
                $ret .= '</sitemap>' . "\n";
            }
        }
        $ret .= '</sitemapindex>' . "\n";
        $host = $_SERVER['HTTP_HOST'];
        if ($host == 'erver.ru' || preg_match('/([a-z]+).erver.ru/', $host)) {
            if ($host != 'erver.ru') {
                $sitemap_prefix = md5($_SERVER['HTTP_HOST']) . '.';
                $output_file = preg_replace('/^' . preg_quote($sitemap_prefix, '/') . '/', '', $output_file);
                $output_file = str_replace('erver.ru', $host, $output_file);
            }
        }
        $f = fopen($output_file, 'w');
        fwrite($f, SiteBill::iconv(SITE_ENCODING, 'utf-8', $ret));
        fclose($f);
        chmod($output_file, 0755);
    }

    protected function createSitemapFile($file_name, $urls) {
        $output_file = $file_name;
        $ret = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $ret .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        if (count($urls) > 0) {
            $domain = $_SERVER['HTTP_HOST'];

            foreach ($urls as $u) {

                if (preg_match('/^(http:|https:)/', $u['url'])) {

                    if (parse_url($u['url'], PHP_URL_HOST) == $domain) {
                        $ret .= '<url>' . "\n";
                        $ret .= '<loc>' . $u['url'] . '</loc>' . "\n";
                        $ret .= '<lastmod>' . date('Y-m-d', time()) . '</lastmod>' . "\n";
                        $ret .= '<changefreq>' . $u['changefreq'] . '</changefreq>' . "\n";
                        $ret .= '<priority>' . $u['priority'] . '</priority>' . "\n";
                        $ret .= '</url>' . "\n";
                    } else {
                        continue;
                    }
                } elseif ($u['url'] === '#') {
                    continue;
                } else {
                    $ret .= '<url>' . "\n";
                    $ret .= '<loc>' . $this->createUrlTpl($u['url'], true) . '</loc>' . "\n";
                    $ret .= '<lastmod>' . date('Y-m-d', time()) . '</lastmod>' . "\n";
                    $ret .= '<changefreq>' . $u['changefreq'] . '</changefreq>' . "\n";
                    $ret .= '<priority>' . $u['priority'] . '</priority>' . "\n";
                    $ret .= '</url>' . "\n";
                }



                /* if(preg_match('/^http:/', $u['url'])){
                  if(parse_url($u['url'] ,PHP_URL_HOST)==$domain){
                  $ret.='<loc>'.$u['url'].'</loc>'."\n";
                  }else{
                  continue;
                  }

                  }elseif($u['url']=='#'){
                  continue;
                  }else{
                  $ret.='<loc>'.$this->site_link.$u['url'].'</loc>'."\n";
                  } */
            }
        }
        $ret .= '</urlset>' . "\n";
        $host = $_SERVER['HTTP_HOST'];
        if ($host == 'erver.ru' || preg_match('/([a-z]+).erver.ru/', $host)) {
            if ($host != 'erver.ru') {
                $sitemap_prefix = md5($_SERVER['HTTP_HOST']) . '.';
                $output_file = preg_replace('/^' . preg_quote($sitemap_prefix, '/') . '/', '', $output_file);
                $output_file = str_replace('erver.ru', $host, $output_file);
            }
        }
        $f = fopen($output_file, 'w');
        fwrite($f, SiteBill::iconv(SITE_ENCODING, 'utf-8', $ret));
        fclose($f);
        chmod($output_file, 0755);
    }

    protected function buildSitemap() {
        //Блокируем с помощью лок-файла обращение к сайтмапу, пока он не будет сгенерирован
        $this->create_lock_file();
        $max_count = 10000;
        $urls = $this->getSitemapItems();
        $sitemap_prefix = md5($_SERVER['HTTP_HOST']) . '.';
        if (1 === intval($this->getConfigValue('apps.language.use_langs'))){
            $sitemap_prefix .= $this->getCurrentLang() . '.';
        }
        $output_file = SITEBILL_DOCUMENT_ROOT . '/cache/' . $sitemap_prefix . 'sitemap.xml';

        //echo count($urls);
        if (count($urls) <= $max_count) {
            $this->createSitemapFile($output_file, $urls);
        } else {

            $urls_count = count($urls);
            $pages = ceil($urls_count / $max_count);
            //echo $pages;
            $files_urls = array();
            for ($i = 1; $i <= $pages; $i++) {
                $start = ($i - 1) * $max_count;
                $offset = $max_count;
                $url_set = array_slice($urls, $start, $offset);
                $output_file = SITEBILL_DOCUMENT_ROOT . '/cache/' . $sitemap_prefix . 'sitemap_page' . $i . '.xml';
                $this->createSitemapFile($output_file, $url_set);
                $files_urls[] = SITEBILL_MAIN_URL . '/sitemap.xml?page=' . $i;
            }
            $output_file = SITEBILL_DOCUMENT_ROOT . '/cache/' . $sitemap_prefix . 'sitemap.xml';
            $this->createSitemapIndexFile($output_file, $files_urls);
        }
        $this->remove_lock_file();
    }

    protected function getSitemapItemsHTML() {

        $apps_urls = array();
        if (is_dir(SITEBILL_DOCUMENT_ROOT . '/apps')) {
            if ($dh = opendir(SITEBILL_DOCUMENT_ROOT . '/apps')) {
                while (($app_dir = readdir($dh)) !== false) {
                    if (is_dir(SITEBILL_DOCUMENT_ROOT . '/apps/' . $app_dir) and ! preg_match('/\./', $app_dir)) {
                        if (is_file(SITEBILL_DOCUMENT_ROOT . '/apps/' . $app_dir . '/site/site.php')) {

                            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/' . $app_dir . '/admin/admin.php')) {
                                require_once (SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/' . $app_dir . '/admin/admin.php');
                            } else {
                                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/' . $app_dir . '/admin/admin.php');
                            }

                            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/' . $app_dir . '/site/local_site.php')) {
                                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/' . $app_dir . '/site/site.php');
                                require_once (SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/' . $app_dir . '/site/local_site.php');
                                $app_class_name = 'local_' . $app_dir . '_site';
                            } elseif (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/' . $app_dir . '/site/site.php')) {
                                require_once (SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/' . $app_dir . '/site/site.php');
                                $app_class_name = $app_dir . '_site';
                            } else {
                                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/' . $app_dir . '/site/site.php');
                                $app_class_name = $app_dir . '_site';
                            }

                            //echo $app_class_name.'<br>';
                            $app_class_inst = new $app_class_name;
                            if (method_exists($app_class_inst, 'sitemapHTML')) {
                                //print_r($app_class_inst->sitemapHTML($this));
                                $u = $app_class_inst->sitemapHTML($this);

                                $apps_urls = array_merge($apps_urls, $u);
                            }
                        }
                    }
                }
                closedir($dh);
            }
        }

        return $apps_urls;
    }

    protected function getSitemapItems() {

        $urls = array();
        $DBC = DBC::getInstance();

        $region_id = 0;
        $host = $_SERVER['HTTP_HOST'];
        if ($host == 'erver.ru' || preg_match('/([a-z]+).erver.ru/', $host)) {
            if (preg_match('/([a-z]+).erver.ru/', $host)) {
                $region_alias = $host;
            } else {
                $region_alias = '';
            }

            if ($region_alias != '') {
                $DBC = DBC::getInstance();
                $query = 'SELECT region_id FROM ' . DB_PREFIX . '_region WHERE domain=? LIMIT 1';
                $stmt = $DBC->query($query, array($host));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    $region_id = (int) $ar['region_id'];
                }
            }
        }




        /*

          if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/news/admin/admin.php') && 1==$this->getConfigValue('apps.news.enable')){
          $urls[]=array('url'=>'news/','changefreq'=>$this->changefreq['news'],'priority'=>$this->priority['news']);
          }
         */


        $level_enable = $this->getConfigValue('apps.seo.level_enable');
        $html_prefix_enable = $this->getConfigValue('apps.seo.html_prefix_enable');
        $data_alias_enable = $this->getConfigValue('apps.seo.data_alias_enable');

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $category_structure = $Structure_Manager->loadCategoryStructure();


        $urls[] = array('url' => '', 'changefreq' => 'daily', 'priority' => '1.0');

        /*
         * Prepare Topics urls
         */
        if (1 == $this->getConfigValue('apps.sitemap.topics_enable')) {
            if (1 == $this->getConfigValue('use_topic_publish_status')) {
                $query = 'SELECT `id`, `url` FROM ' . DB_PREFIX . '_topic WHERE `published`=1';
            } else {
                $query = 'SELECT `id`, `url` FROM ' . DB_PREFIX . '_topic';
            }

            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $url = '';
                    if (1 == $level_enable) {
                        if ($category_structure['catalog'][$ar['id']]['url'] != '') {
                            $url = $category_structure['catalog'][$ar['id']]['url'];
                        } else {
                            $url = 'topic' . $ar['id'] . '.html';
                        }
                    } else {
                        if ($category_structure['catalog'][$ar['id']]['url'] != '') {
                            $url = $category_structure['catalog'][$ar['id']]['url'];
                        } else {
                            $url = 'topic' . $ar['id'] . '.html';
                        }
                    }
                    $url = SITEBILL_MAIN_URL . '/' . $url;
                    $urls[] = array('url' => $url, 'changefreq' => $this->changefreq['topic'], 'priority' => $this->priority['topic']);
                }
            }
        }

        /*
         * Prepare Country urls
         */
        if (1 == $this->getConfigValue('apps.sitemap.country_enable')) {
            $query = 'SELECT country_id, url FROM ' . DB_PREFIX . '_country WHERE url!=\'\'';
            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $url = SITEBILL_MAIN_URL . '/' . $ar['url'];
                    if ($url != '') {
                        $urls[] = array('url' => $url, 'changefreq' => $this->changefreq['country'], 'priority' => $this->priority['country']);
                    }
                }
            }
        }

        /*
         * Prepare City urls
         */
        if (1 == $this->getConfigValue('apps.sitemap.city_enable')) {
            $query = 'SELECT `city_id`, `url` FROM ' . DB_PREFIX . '_city WHERE url!=?';
            $stmt = $DBC->query($query, array(''));
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $url = SITEBILL_MAIN_URL . '/' . $ar['url'];
                    if ($url != '') {
                        $urls[] = array('url' => $url, 'changefreq' => $this->changefreq['city'], 'priority' => $this->priority['city']);
                    }
                }
            }
        }



        /*
         * Prepare Data urls
         */
        if (1 == $this->getConfigValue('apps.sitemap.data_enable')) {
            $data = array();
            if ($region_id != 0) {
                $query = 'SELECT `id`, `topic_id`' . (1 == $data_alias_enable ? ', `translit_alias`' : '') . ' FROM ' . DB_PREFIX . '_data WHERE `active`=1 AND `region_id`=' . $region_id . ' ORDER BY `id`';
            } else {
                $query = 'SELECT `id`, `topic_id`' . (1 == $data_alias_enable ? ', `translit_alias`' : '') . ' FROM ' . DB_PREFIX . '_data WHERE `active`=1 ORDER BY `id`';
            }

            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $data[] = $ar;
                }
            }

            if (count($data) > 0) {
                foreach ($data as $k => $d) {
                    if (isset($d['translit_alias'])) {
                        $translit_alias = $d['translit_alias'];
                    } else {
                        $translit_alias = '';
                    }
                    $data[$k]['href'] = $this->getRealtyHREF($d['id'], true, array('topic_id' => $d['topic_id'], 'alias' => $translit_alias));
                }
                foreach ($data as $k => $d) {
                    //$url=trim(str_replace('\\', '/', $d['href']),'/');
                    $url = str_replace('\\', '/', $d['href']);
                    if ($url != '') {
                        $urls[] = array('url' => $url, 'changefreq' => $this->changefreq['data'], 'priority' => $this->priority['data']);
                    }
                }
            }
        }

        /*
         * Prepare Company urls
         */
        if ($this->getConfigValue('apps.company.enable') && $this->getConfigValue('apps.sitemap.company_enable')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_implements.php');
            $Structure_Manager = Structure_Implements::getManager('company');
            $category_structure = $Structure_Manager->loadCategoryStructure();


            /* if(count($category_structure)>0){
              foreach($category_structure['catalog'] as $cs){
              if($cs['url']!=''){
              $url=SITEBILL_MAIN_URL.$this->getConfigValue('apps.company.namespace').'/'.$cs['url'];
              }else{
              $url=SITEBILL_MAIN_URL.$this->getConfigValue('apps.company.namespace').'/company'.$cs['id'];
              }
              $urls[]=array('url'=>$url,'changefreq'=>$this->changefreq['company_topic'],'priority'=>$this->priority['company_topic']);
              }
              } */



            $ret = array();
            if ($region_id != 0) {
                $query = 'SELECT company_id, company_topic_id, alias FROM ' . DB_PREFIX . '_company WHERE region_id=' . $region_id . ' ORDER BY company_id';
            } else {
                $query = 'SELECT company_id, company_topic_id, alias FROM ' . DB_PREFIX . '_company ORDER BY company_id';
            }

            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $ret[] = $ar;
                }
            }

            if (count($ret) > 0) {
                foreach ($ret as $k => $v) {

                    if (1 == $level_enable) {

                        if ($category_structure['catalog'][$v['company_topic_id']]['url'] != '') {
                            $ret[$k]['parent_category_url'] = $category_structure['catalog'][$v['company_topic_id']]['url'] . self::$_trslashes;
                        } else {
                            $ret[$k]['parent_category_url'] = '';
                        }
                    } else {
                        $ret[$k]['parent_category_url'] = '';
                    }
                    if ($v['alias'] == '') {
                        $ret[$k]['href'] = $this->getConfigValue('apps.company.namespace') . '/' . $ret[$k]['parent_category_url'] . 'company' . $v['company_id'];
                    } else {
                        $ret[$k]['href'] = $this->getConfigValue('apps.company.namespace') . '/' . $ret[$k]['parent_category_url'] . $v['alias'];
                    }
                }
                foreach ($ret as $k => $d) {
                    $url = $d['href'];
                    $urls[] = array('url' => $url, 'changefreq' => $this->changefreq['company'], 'priority' => $this->priority['company']);
                }
            }
        }

        /*
         * Prepare Apps urls (news/articles/menues/predefs etc)
         */
        $apps_urls = array();
        if (is_dir(SITEBILL_DOCUMENT_ROOT . '/apps')) {
            if ($dh = opendir(SITEBILL_DOCUMENT_ROOT . '/apps')) {
                while (($app_dir = readdir($dh)) !== false) {
                    //echo '$app_dir = '.$app_dir.'<br>';
                    if (is_dir(SITEBILL_DOCUMENT_ROOT . '/apps/' . $app_dir) and ! preg_match('/\./', $app_dir)) {
                        if (is_file(SITEBILL_DOCUMENT_ROOT . '/apps/' . $app_dir . '/site/site.php')) {
                            if ( $app_dir === 'fasteditor' ) {
                                continue;
                            }

                            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/' . $app_dir . '/admin/admin.php')) {
                                require_once (SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/' . $app_dir . '/admin/admin.php');
                            } else {
                                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/' . $app_dir . '/admin/admin.php');
                            }

                            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/' . $app_dir . '/site/local_site.php')) {
                                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/' . $app_dir . '/site/site.php');
                                require_once (SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/' . $app_dir . '/site/local_site.php');
                                $app_class_name = 'local_' . $app_dir . '_site';
                            } elseif (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/' . $app_dir . '/site/site.php')) {
                                require_once (SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/' . $app_dir . '/site/site.php');
                                $app_class_name = $app_dir . '_site';
                            } else {
                                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/' . $app_dir . '/site/site.php');
                                $app_class_name = $app_dir . '_site';
                            }

                            //echo $app_class_name.'<br>';
                            $app_class_inst = new $app_class_name;
                            if (method_exists($app_class_inst, 'sitemap')) {
                                $apps_urls[] = $app_class_inst->sitemap($this);
                            }
                        }
                    }
                }
                closedir($dh);
            }
        }

        if (!empty($apps_urls)) {
            foreach ($apps_urls as $app_url) {
                if (!empty($app_url)) {
                    foreach ($app_url as $aurl) {
                        //$url=ltrim(str_replace('\\', '/', $aurl['url']),'/');
                        //$aurl['url']=$url;
                        $urls[] = $aurl;
                    }
                }
            }
        }
        return $urls;
    }

    public function validatePriority($priority) {
        return str_replace(',', '.', $priority);
    }

    public function validateFrequency($frequency) {

        $changefreq_values = array(
            '1' => 'always',
            '2' => 'hourly',
            '3' => 'daily',
            '4' => 'weekly',
            '5' => 'monthly',
            '6' => 'yearly',
            '0' => 'never'
        );

        if (in_array($frequency, $changefreq_values)) {
            return $frequency;
        }

        $frequency = intval($frequency);
        if (isset($changefreq_values[$frequency])) {
            return $changefreq_values[$frequency];
        }

        return 'never';
    }

    private function generateSitemap() {
        $DBC = DBC::getInstance();

        $region_id = 0;
        $host = $_SERVER['HTTP_HOST'];
        if ($host == 'erver.ru' || preg_match('/([a-z]+).erver.ru/', $host)) {
            if (preg_match('/([a-z]+).erver.ru/', $host)) {
                $region_alias = $host;
            } else {
                $region_alias = '';
            }

            if ($host != 'erver.ru') {
                $this->output_file = str_replace('erver.ru', $host, $this->output_file);
            }



            if ($region_alias != '') {
                $DBC = DBC::getInstance();
                $query = 'SELECT region_id FROM ' . DB_PREFIX . '_region WHERE domain=? LIMIT 1';
                $stmt = $DBC->query($query, array($host));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    $region_id = (int) $ar['region_id'];
                }
            }
        }






        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/news/admin/admin.php') && 1 == $this->getConfigValue('apps.news.enable')) {
            $this->urls[] = array('url' => 'news/', 'changefreq' => $this->changefreq['news'], 'priority' => $this->priority['news']);
        }



        $level_enable = $this->getConfigValue('apps.seo.level_enable');
        $html_prefix_enable = $this->getConfigValue('apps.seo.html_prefix_enable');
        $data_alias_enable = $this->getConfigValue('apps.seo.data_alias_enable');

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $category_structure = $Structure_Manager->loadCategoryStructure();

        if (1 == $this->getConfigValue('apps.sitemap.topics_enable')) {
            $query = 'SELECT id, url FROM ' . DB_PREFIX . '_topic';
            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    if (1 == $level_enable) {
                        if ($category_structure['catalog'][$ar['id']]['url'] != '') {
                            $url = $category_structure['catalog'][$ar['id']]['url'] . '/';
                        } else {
                            $url = 'topic' . $ar['id'] . '.html';
                        }
                    } else {
                        if ($category_structure['catalog'][$ar['id']]['url'] != '') {
                            $url = $category_structure['catalog'][$ar['id']]['url'] . '/';
                        } else {
                            $url = 'topic' . $ar['id'] . '.html';
                        }
                    }

                    $this->urls[] = array('url' => $url, 'changefreq' => $this->changefreq['topic'], 'priority' => $this->priority['topic']);
                }
            }
        }

        if (1 == $this->getConfigValue('apps.sitemap.country_enable')) {
            $query = 'SELECT country_id, url FROM ' . DB_PREFIX . '_country';
            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $url = $ar['url'];
                    if ($url != '') {
                        $this->urls[] = array('url' => $url, 'changefreq' => $this->changefreq['country'], 'priority' => $this->priority['country']);
                    }
                }
            }
        }

        $query = 'SELECT is_service FROM ' . DB_PREFIX . '_page LIMIT 1';
        $stmt = $DBC->query($query);
        if ($stmt) {
            $query = 'SELECT uri FROM ' . DB_PREFIX . '_page WHERE is_service=0';
        } else {
            $query = 'SELECT uri FROM ' . DB_PREFIX . '_page';
        }


        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                if ($ar['uri'] != '') {
                    $url = trim(str_replace('\\', '/', $ar['uri']), '/') . '/';
                }
                $this->urls[] = array('url' => $url, 'changefreq' => $this->changefreq['page'], 'priority' => $this->priority['page']);
            }
        }

        $query = 'SELECT url FROM ' . DB_PREFIX . '_menu_structure';
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                if ($ar['url'] != '') {
                    $url = trim(str_replace('\\', '/', $ar['url']), '/') . '/';
                }
                $this->urls[] = array('url' => $url, 'changefreq' => $this->changefreq['menu'], 'priority' => $this->priority['menu']);
            }
        }

        //Генерация урлов объявлений
        if (1 == $this->getConfigValue('apps.sitemap.data_enable')) {




            $data = array();
            if ($region_id != 0) {
                $query = 'SELECT `id`, `topic_id`' . (1 == $data_alias_enable ? ', `translit_alias`' : '') . ' FROM ' . DB_PREFIX . '_data WHERE `active`=1 AND `region_id`=' . $region_id . ' ORDER BY `id`';
            } else {
                $query = 'SELECT `id`, `topic_id`' . (1 == $data_alias_enable ? ', `translit_alias`' : '') . ' FROM ' . DB_PREFIX . '_data WHERE `active`=1 ORDER BY `id`';
            }

            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $data[] = $ar;
                }
            }

            if (count($data) > 0) {
                foreach ($data as $k => $d) {

                    if (1 == $level_enable) {
                        if ($category_structure['catalog'][$d['topic_id']]['url'] != '') {
                            $data[$k]['parent_category_url'] = $category_structure['catalog'][$d['topic_id']]['url'] . '/';
                        } else {
                            $data[$k]['parent_category_url'] = '';
                        }
                    } else {
                        $data[$k]['parent_category_url'] = '';
                    }
                    if (1 == $data_alias_enable && $d['translit_alias'] != '') {
                        $data[$k]['href'] = SITEBILL_MAIN_URL . '/' . $data[$k]['parent_category_url'] . $d['translit_alias'];
                    } elseif (1 == $html_prefix_enable) {
                        $data[$k]['href'] = SITEBILL_MAIN_URL . '/' . $data[$k]['parent_category_url'] . 'realty' . $data[$k]['id'] . '.html';
                    } else {
                        $data[$k]['href'] = SITEBILL_MAIN_URL . '/' . $data[$k]['parent_category_url'] . 'realty' . $data[$k]['id'];
                    }
                }
                foreach ($data as $k => $d) {
                    $url = trim(str_replace('\\', '/', $d['href']), '/');
                    $this->urls[] = array('url' => $url, 'changefreq' => $this->changefreq['data'], 'priority' => $this->priority['data']);
                }
            }
        }

        if ($this->getConfigValue('apps.company.enable') && $this->getConfigValue('apps.sitemap.company_enable')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_implements.php');
            $Structure_Manager = Structure_Implements::getManager('company');
            $category_structure = $Structure_Manager->loadCategoryStructure();


            if (count($category_structure) > 0) {
                foreach ($category_structure['catalog'] as $cs) {
                    if ($cs['url'] != '') {
                        $url = SITEBILL_MAIN_URL . $this->getConfigValue('apps.company.namespace') . '/' . $cs['url'];
                    } else {
                        $url = SITEBILL_MAIN_URL . $this->getConfigValue('apps.company.namespace') . '/company' . $cs['id'];
                    }
                    $this->urls[] = array('url' => $url, 'changefreq' => $this->changefreq['company_topic'], 'priority' => $this->priority['company_topic']);
                }
            }



            $ret = array();
            if ($region_id != 0) {
                $query = 'SELECT company_id, company_topic_id, alias FROM ' . DB_PREFIX . '_company WHERE region_id=' . $region_id . ' ORDER BY company_id';
            } else {
                $query = 'SELECT company_id, company_topic_id, alias FROM ' . DB_PREFIX . '_company ORDER BY company_id';
            }

            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $ret[] = $ar;
                }
            }

            if (count($ret) > 0) {
                foreach ($ret as $k => $v) {

                    if (1 == $level_enable) {

                        if ($category_structure['catalog'][$v['company_topic_id']]['url'] != '') {
                            $ret[$k]['parent_category_url'] = $category_structure['catalog'][$v['company_topic_id']]['url'] . '/';
                        } else {
                            $ret[$k]['parent_category_url'] = '';
                        }
                    } else {
                        $ret[$k]['parent_category_url'] = '';
                    }
                    if ($v['alias'] == '') {
                        $ret[$k]['href'] = SITEBILL_MAIN_URL . '/' . $this->getConfigValue('apps.company.namespace') . '/' . $ret[$k]['parent_category_url'] . 'company' . $v['company_id'];
                    } else {
                        $ret[$k]['href'] = SITEBILL_MAIN_URL . '/' . $this->getConfigValue('apps.company.namespace') . '/' . $ret[$k]['parent_category_url'] . $v['alias'];
                    }
                }
                foreach ($ret as $k => $d) {
                    $url = trim(str_replace('\\', '/', $d['href']), '/');
                    $this->urls[] = array('url' => $url, 'changefreq' => $this->changefreq['company'], 'priority' => $this->priority['company']);
                }
            }
        }

        $apps_urls = array();
        if (is_dir(SITEBILL_DOCUMENT_ROOT . '/apps')) {
            if ($dh = opendir(SITEBILL_DOCUMENT_ROOT . '/apps')) {
                while (($app_dir = readdir($dh)) !== false) {
                    //echo '$app_dir = '.$app_dir.'<br>';
                    if (is_dir(SITEBILL_DOCUMENT_ROOT . '/apps/' . $app_dir) and ! preg_match('/\./', $app_dir)) {
                        if (is_file(SITEBILL_DOCUMENT_ROOT . '/apps/' . $app_dir . '/site/site.php')) {

                            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/' . $app_dir . '/admin/admin.php')) {
                                require_once (SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/' . $app_dir . '/admin/admin.php');
                            } else {
                                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/' . $app_dir . '/admin/admin.php');
                            }

                            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/' . $app_dir . '/site/local_site.php')) {
                                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/' . $app_dir . '/site/site.php');
                                require_once (SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/' . $app_dir . '/site/local_site.php');
                                $app_class_name = 'local_' . $app_dir . '_site';
                            } elseif (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/' . $app_dir . '/site/site.php')) {
                                require_once (SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/' . $app_dir . '/site/site.php');
                                $app_class_name = $app_dir . '_site';
                            } else {
                                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/' . $app_dir . '/site/site.php');
                                $app_class_name = $app_dir . '_site';
                            }

                            //echo $app_class_name.'<br>';
                            $app_class_inst = new $app_class_name;
                            if (method_exists($app_class_inst, 'sitemap')) {
                                $apps_urls[] = $app_class_inst->sitemap($this);
                            }
                        }
                    }
                }
                closedir($dh);
            }
        }

        if (!empty($apps_urls)) {
            foreach ($apps_urls as $app_url) {
                if (!empty($app_url)) {
                    foreach ($app_url as $aurl) {
                        $url = trim(str_replace('\\', '/', $aurl['url']), '/');
                        $aurl['url'] = $url;
                        $this->urls[] = $aurl;
                    }
                }
            }
        }

        //print_r($apps_urls);


        $ret = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $ret .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        if (count($this->urls) > 0) {
            foreach ($this->urls as $u) {
                $ret .= '<url>' . "\n";
                if (preg_match('/^http:/', $u['url'])) {
                    $ret .= '<loc>' . $u['url'] . '</loc>' . "\n";
                } else {
                    $ret .= '<loc>' . $this->site_link . $u['url'] . '</loc>' . "\n";
                }
                $ret .= '<lastmod>' . date('Y-m-d', time()) . '</lastmod>' . "\n";
                $ret .= '<changefreq>' . $u['changefreq'] . '</changefreq>' . "\n";
                $ret .= '<priority>' . $u['priority'] . '</priority>' . "\n";
                $ret .= '</url>' . "\n";
            }
        }
        $ret .= '</urlset>' . "\n";
        $f = fopen($this->output_file, 'w');
        fwrite($f, SiteBill::iconv(SITE_ENCODING, 'utf-8', $ret));
        fclose($f);
        chmod($this->output_file, 0755);
    }

    function getTopMenu() {
        $rs = '';
        $rs .= '/sitemap.xml генерируется автоматически при обращении к адресу ' . SITEBILL_MAIN_URL . '/sitemap.xml Необходимо чтобы физически файла в корне sitemap.xml не было.';
        return $rs;
    }

    private function checkConfiguration() {
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/config/admin/admin.php';
        $CF = new config_admin();
        if ($CF) {
            if (!$CF->check_config_item('apps.sitemap.priority.news')) {
                $CF->addParamToConfig('apps.sitemap.priority.news', '0.5', 'Приоритетность URL <b>раздела новостей</b> относительно других URL на Вашем сайте. Диапазон от 0.0 до 1.0');
            }

            if (!$CF->check_config_item('apps.sitemap.priority.topic')) {
                $CF->addParamToConfig('apps.sitemap.priority.topic', '0.5', 'Приоритетность URL <b>категорий</b> относительно других URL на Вашем сайте. Диапазон от 0.0 до 1.0');
            }

            if (!$CF->check_config_item('apps.sitemap.priority.page')) {
                $CF->addParamToConfig('apps.sitemap.priority.page', '0.5', 'Приоритетность URL <b>статических страниц</b> относительно других URL на Вашем сайте. Диапазон от 0.0 до 1.0');
            }

            if (!$CF->check_config_item('apps.sitemap.priority.menu')) {
                $CF->addParamToConfig('apps.sitemap.priority.menu', '0.5', 'Приоритетность URL <b>дополнительных меню</b> относительно других URL на Вашем сайте. Диапазон от 0.0 до 1.0');
            }

            if (!$CF->check_config_item('apps.sitemap.priority.data')) {
                $CF->addParamToConfig('apps.sitemap.priority.data', '0.5', 'Приоритетность URL <b>объявлений</b> относительно других URL на Вашем сайте. Диапазон от 0.0 до 1.0');
            }

            if (!$CF->check_config_item('apps.sitemap.priority.country')) {
                $CF->addParamToConfig('apps.sitemap.priority.country', '0.5', 'Приоритетность URL <b>Стран</b> относительно других URL на Вашем сайте. Диапазон от 0.0 до 1.0');
            }
            if (!$CF->check_config_item('apps.sitemap.priority.city')) {
                $CF->addParamToConfig('apps.sitemap.priority.city', '0.5', 'Приоритетность URL <b>Городов</b> относительно других URL на Вашем сайте. Диапазон от 0.0 до 1.0');
            }

            if (!$CF->check_config_item('apps.sitemap.priority.company')) {
                $CF->addParamToConfig('apps.sitemap.priority.company', '0.5', 'Приоритетность URL <b>компании</b> относительно других URL на Вашем сайте. Диапазон от 0.0 до 1.0');
            }

            if (!$CF->check_config_item('apps.sitemap.priority.company_topic')) {
                $CF->addParamToConfig('apps.sitemap.priority.company_topic', '0.5', 'Приоритетность URL <b>разделов компаний</b> относительно других URL на Вашем сайте. Диапазон от 0.0 до 1.0');
            }

            if (!$CF->check_config_item('apps.sitemap.changefreq.news')) {
                $CF->addParamToConfig('apps.sitemap.changefreq.news', '3', 'Вероятная частота изменения <b>страницы раздела новостей</b>. Одно из числовых значений (1-всегда, 2-ежечасно, 3-ежедневно, 4-еженедельно, 5-ежемесячно, 6-ежегодно, 7-никогда)');
            }

            if (!$CF->check_config_item('apps.sitemap.changefreq.country')) {
                $CF->addParamToConfig('apps.sitemap.changefreq.country', '3', 'Вероятная частота изменения <b>страницы Страны</b>. Одно из числовых значений (1-всегда, 2-ежечасно, 3-ежедневно, 4-еженедельно, 5-ежемесячно, 6-ежегодно, 7-никогда)');
            }

            if (!$CF->check_config_item('apps.sitemap.changefreq.city')) {
                $CF->addParamToConfig('apps.sitemap.changefreq.city', '3', 'Вероятная частота изменения <b>страницы Города</b>. Одно из числовых значений (1-всегда, 2-ежечасно, 3-ежедневно, 4-еженедельно, 5-ежемесячно, 6-ежегодно, 7-никогда)');
            }

            if (!$CF->check_config_item('apps.sitemap.changefreq.topic')) {
                $CF->addParamToConfig('apps.sitemap.changefreq.topic', '3', 'Вероятная частота изменения <b>страницы категории</b>. Одно из числовых значений (1-всегда, 2-ежечасно, 3-ежедневно, 4-еженедельно, 5-ежемесячно, 6-ежегодно, 7-никогда)');
            }

            if (!$CF->check_config_item('apps.sitemap.changefreq.page')) {
                $CF->addParamToConfig('apps.sitemap.changefreq.page', '5', 'Вероятная частота изменения <b>статической страницы</b>. Одно из числовых значений (1-всегда, 2-ежечасно, 3-ежедневно, 4-еженедельно, 5-ежемесячно, 6-ежегодно, 7-никогда)');
            }

            if (!$CF->check_config_item('apps.sitemap.changefreq.menu')) {
                $CF->addParamToConfig('apps.sitemap.changefreq.menu', '5', 'Вероятная частота изменения <b>вспомогательных меню</b>. Одно из числовых значений (1-всегда, 2-ежечасно, 3-ежедневно, 4-еженедельно, 5-ежемесячно, 6-ежегодно, 7-никогда)');
            }

            if (!$CF->check_config_item('apps.sitemap.changefreq.data')) {
                $CF->addParamToConfig('apps.sitemap.changefreq.data', '4', 'Вероятная частота изменения <b>объявления</b>. Одно из числовых значений (1-всегда, 2-ежечасно, 3-ежедневно, 4-еженедельно, 5-ежемесячно, 6-ежегодно, 7-никогда)');
            }

            if (!$CF->check_config_item('apps.sitemap.changefreq.company')) {
                $CF->addParamToConfig('apps.sitemap.changefreq.company', '5', 'Вероятная частота изменения информации о <b>компании</b>. Одно из числовых значений (1-всегда, 2-ежечасно, 3-ежедневно, 4-еженедельно, 5-ежемесячно, 6-ежегодно, 7-никогда)');
            }

            if (!$CF->check_config_item('apps.sitemap.changefreq.company_topic')) {
                $CF->addParamToConfig('apps.sitemap.changefreq.company_topic', '5', 'Вероятная частота изменения информации о <b>разделах компаний</b>. Одно из числовых значений (1-всегда, 2-ежечасно, 3-ежедневно, 4-еженедельно, 5-ежемесячно, 6-ежегодно, 7-никогда)');
            }

            if (!$CF->check_config_item('apps.sitemap.data_enable')) {
                $CF->addParamToConfig('apps.sitemap.data_enable', '0', 'Выводить объявления в sitemap');
            }

            if (!$CF->check_config_item('apps.sitemap.company_enable')) {
                $CF->addParamToConfig('apps.sitemap.company_enable', '0', 'Выводить компании в sitemap');
            }

            if (!$CF->check_config_item('apps.sitemap.topics_enable')) {
                $CF->addParamToConfig('apps.sitemap.topics_enable', '1', 'Выводить категории в sitemap');
            }

            if (!$CF->check_config_item('apps.sitemap.country_enable')) {
                $CF->addParamToConfig('apps.sitemap.country_enable', '0', 'Выводить страны в sitemap');
            }

            if (!$CF->check_config_item('apps.sitemap.city_enable')) {
                $CF->addParamToConfig('apps.sitemap.city_enable', '0', 'Выводить города в sitemap');
            }


            if (!$CF->check_config_item('apps.sitemap.sitemaplivetime')) {
                $CF->addParamToConfig('apps.sitemap.sitemaplivetime', '86400', 'Срок жизни файла карты сайта в секундах');
            }
            $CF->addParamToConfig('apps.sitemap.use_lock_file', '0', 'Использовать lock-файл на момент генерации sitemap (если база очень большая)', 1);
        }
        unset($CF);
    }

    function get_lock_filename () {
        return SITEBILL_DOCUMENT_ROOT . '/cache/sitemap_lock';
    }

    function is_locked () {
        if ( !$this->getConfigValue('apps.sitemap.use_lock_file') ) {
            return false;
        }
        if ( file_exists($this->get_lock_filename()) ) {
            return true;
        }
        return false;
    }

    function create_lock_file () {
        if ( !$this->getConfigValue('apps.sitemap.use_lock_file') ) {
            return false;
        }
        if ( !$this->is_locked() ) {
            file_put_contents($this->get_lock_filename(), date());
            return true;
        }
        return false;
    }
    function remove_lock_file () {
        if ( $this->is_locked() ) {
            unlink($this->get_lock_filename());
            return true;
        }
        return false;
    }

}
