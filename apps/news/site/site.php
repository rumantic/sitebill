<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * News fronend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class news_site extends news_admin {

    public function front_account() {
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/news/admin/user_admin.php';
        $AUN = new user_news_admin();
        $this->template->assert('main', $AUN->main());
        return true;
    }

    public function front_news_grid($topic_id = 0) {

        if ('' != $this->getConfigValue('apps.news.alias')) {
            $app_alias = $this->getConfigValue('apps.news.alias');
        } else {
            $app_alias = 'news';
        }

        if ($this->getConfigValue('apps.news.folder_title') != '') {
            $app_title = $this->getConfigValue('apps.news.folder_title');
        } else {
            $app_title = Multilanguage::_('PAGE_TITLE', 'news');
        }

        if (1 == (int) $this->getConfigValue('apps.seo.no_trailing_slashes')) {
            $trailing_slashe = '';
        } else {
            $trailing_slashe = '/';
        }

        $meta_title = $this->getConfigValue('apps.news.meta_title');
        $meta_description = $this->getConfigValue('apps.news.meta_desription');
        $meta_keywords = $this->getConfigValue('apps.news.meta_keywords');
        $description = '';
        if ('' != $this->getConfigValue('apps.news.app_title')) {
            $title = $this->getConfigValue('apps.news.app_title');
        } else {
            $title = Multilanguage::_('PAGE_TITLE', 'news');
        }

        $breadcrumbs = array();
        $breadcrumbs[] = '<a href="' . $this->createUrlTpl('') . '/">' . Multilanguage::_('L_HOME') . '</a>';

        if ($topic_id != 0) {
            $breadcrumbs[] = '<a href="' . $this->createUrlTpl($app_alias) . '">' . $app_title . '</a>';
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
            $ATH = new Admin_Table_Helper();
            $news_topic = $ATH->load_model('news_topic', false);

            $DM = new Data_Model();

            $news_topic = $DM->init_model_data_from_db('news_topic', 'id', $topic_id, $news_topic['news_topic'], TRUE);
            if (1 === intval($this->getConfigValue('apps.language.use_langs'))) {
                $curlang = $this->getCurrentLang();

                foreach ($news_topic as $key => $item_array) {
                    $lang_key = $key . '_' . $curlang;
                    if (isset($news_topic[$lang_key]) && $news_topic[$lang_key]['value'] != '') {
                        $news_topic[$key]['value'] = $news_topic[$lang_key]['value'];
                    }
                }
            }


            $breadcrumbs[] = $news_topic['name']['value'];

            if (isset($news_topic['meta_title']) && $news_topic['meta_title']['value'] != '') {
                $meta_title = $news_topic['meta_title']['value'];
            }
            if (isset($news_topic['meta_description']) && $news_topic['meta_description']['value'] != '') {
                $meta_description = $news_topic['meta_description']['value'];
            }
            if (isset($news_topic['meta_keywords']) && $news_topic['meta_keywords']['value'] != '') {
                $meta_keywords = $news_topic['meta_keywords']['value'];
            }
            if (isset($news_topic['name']) && $news_topic['name']['value'] != '') {
                $title = $news_topic['name']['value'];
            }
            if (isset($news_topic['description']) && $news_topic['description']['value'] != '') {
                $description = $news_topic['description']['value'];
            }
        } else {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/page/admin/admin.php';
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/page/site/site.php';
            $PS = new page_site();
            $page = $PS->getPageByURI('_news');

            if ($page != 0) {
                if ($page['meta_title'] != '') {
                    $meta_title = $page['meta_title'];
                }
                if ($page['meta_description'] != '') {
                    $meta_description = $page['meta_description'];
                }
                if ($page['title'] != '') {
                    $title = $page['title'];
                }
                if ($page['body'] != '') {
                    $description = $page['body'];
                }

                if ($page['meta_keywords'] != '') {
                    $meta_keywords = $page['meta_keywords'];
                }
            }
            $breadcrumbs[] = $app_title;
        }



        $page = (int) $this->getRequestValue('page');
        $per_page = $this->getConfigValue('apps.news.front.per_page');

        require_once SITEBILL_DOCUMENT_ROOT . '/apps/news/site/news_grid_constructor.php';
        $NGC = new News_Grid_Constructor();
        $news = $NGC->get_sitebill_adv_ext(array('page' => $page, 'per_page' => $per_page, 'news_topic_id' => $topic_id));

        if (1 == $this->getConfigValue('apps.news.use_news_topics')) {
            $news_topics = $this->getNewsTopicsList();
            if (!empty($news)) {
                foreach ($news as $k => $v) {
                    if (isset($news_topics[$v['_news_topic_id']])) {
                        $news[$k]['_news_topic_id_'] = $news_topics[$v['_news_topic_id']];
                    } else {
                        $news[$k]['_news_topic_id_'] = array();
                    }
                }
            }
            $this->template->assert('news_topics', $news_topics);
        } else {
            $this->template->assert('news_topics', array());
        }


        $this->template->assert('title', $title);
        $this->template->assert('meta_title', $meta_title);
        $this->template->assert('meta_description', $meta_description);
        $this->template->assert('meta_keywords', $meta_keywords);
        $this->template->assert('description', $description);
        $this->template->assert('breadcrumbs', $this->get_breadcrumbs($breadcrumbs));
        $this->template->assert('news', $news);
        $this->set_apps_template('news', $this->getConfigValue('theme'), 'main_file_tpl', 'news_grid.tpl');
        return true;
    }

    public function front_news_grid_by_topic($topic_id) {
        return $this->front_news_grid($topic_id);
    }

    public function front_news_item($news_id, $by_alias = false) {
        //require_once(SITEBILL_DOCUMENT_ROOT.'/apps/news/admin/news_model.php');
        //$Object=new News_Model();
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $model = $this->data_model;
        $news = $data_model->init_model_data_from_db($this->table_name, $this->primary_key, $news_id, $model[$this->table_name], TRUE);
        if (false === $news) {
            return false;
        }
        if (!$by_alias && isset($news['newsalias']) && $news['newsalias']['value'] != '') {
            $href = $this->getNewsRoute($news['id']['value'], $news['newsalias']['value']);
            $this->go301($href);
        }

        if (1 === intval($this->getConfigValue('apps.language.use_langs'))) {
            $curlang = $this->getCurrentLang();

            foreach ($news as $key => $item_array) {
                $lang_key = $key . '_' . $curlang;
                if (isset($news[$lang_key]) && $news[$lang_key]['value'] != '') {
                    $news[$key]['value'] = $news[$lang_key]['value'];
                }
            }
        }

        if ($news['date']['type'] == 'dtdatetime' && strtotime($news['date']['value']) > time()) {
            return false;
        }
        if ($news['date']['type'] == 'date' && $news['date']['value'] > time()) {
            return false;
        }

        if ('' != $this->getConfigValue('apps.news.alias')) {
            $app_alias = $this->getConfigValue('apps.news.alias');
        } else {
            $app_alias = 'news';
        }

        if ($this->getConfigValue('apps.news.folder_title') != '') {
            $app_title = $this->getConfigValue('apps.news.folder_title');
        } else {
            $app_title = Multilanguage::_('PAGE_TITLE', 'news');
        }

        if (1 == (int) $this->getConfigValue('apps.seo.no_trailing_slashes')) {
            $trailing_slashe = '';
        } else {
            $trailing_slashe = '/';
        }

        $meta_title = $this->getConfigValue('apps.news.meta_title');
        $meta_description = $this->getConfigValue('apps.news.meta_desription');
        $meta_keywords = $this->getConfigValue('apps.news.meta_keywords');


        $breadcrumbs = array();
        $breadcrumbs[] = '<a href="' . $this->createUrlTpl('') . '">' . Multilanguage::_('L_HOME') . '</a>';
        //require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/tab.php');

        $hasUploadify = false;
        $uploads = false;
        foreach ($model[$this->table_name] as $mitem) {
            if ($mitem['type'] == 'uploadify_image') {
                $hasUploadify = true;
                continue;
            }
        }
        if (!$hasUploadify) {
            foreach ($this->data_model[$this->table_name] as $mitem) {
                if ($mitem['type'] == 'uploads') {
                    $uploads = $mitem['name'];
                    continue;
                }
            }
        }


        if ($hasUploadify) {
            $image_array = $data_model->get_image_array('news', 'news', 'news_id', $news['news_id']['value']);
            if (count($image_array) > 0) {
                $news['prev_img'] = $image_array[0]['img_preview'];
                $news['normal_img'] = $image_array[0]['img_normal'];
                $news['img'] = $image_array;
            }
        } elseif ($uploads !== false && is_array($news[$uploads]['value'])) {
            $news['prev_img'] = SITEBILL_MAIN_URL . '/img/data/' . $news[$uploads]['value'][0]['preview'];
            $news['normal_img'] = SITEBILL_MAIN_URL . '/img/data/' . $news[$uploads]['value'][0]['normal'];
            $news['img'] = $news[$uploads]['value'];
        }

        if ($news['date']['type'] == 'dtdatetime') {
            $news['date']['value_string'] = date('d.m.Y', strtotime($news['date']['value']));
        } else {
            if (preg_match('/\./', $news['date']['value'])) {
                $news['date']['value_string'] = $news['date']['value'];
            } else {
                $news['date']['value_string'] = date('d.m.Y', $news['date']['value']);
            }
        }

        $breadcrumbs[] = '<a href="' . $this->createUrlTpl($app_alias) . '">' . $app_title . '</a>';

        if (1 == $this->getConfigValue('apps.news.use_news_topics')) {
            $all_topics = $this->getNewsTopicsList();
            if (isset($all_topics[$news['news_topic_id']['value']])) {
                $breadcrumbs[] = '<a href="' . $all_topics[$news['news_topic_id']['value']]['url'] . '">' . $all_topics[$news['news_topic_id']['value']]['name'] . '</a>';
            }
            $this->template->assert('news_topics', $all_topics);
        } else {
            $this->template->assert('news_topics', array());
        }

        $breadcrumbs[] = $news['title']['value'];
        $this->template->assert('breadcrumbs', $this->get_breadcrumbs($breadcrumbs));

        $title = $news['title']['value'];

        if (isset($news['meta_title']['value']) && $news['meta_title']['value'] != '') {
            $meta_title = $news['meta_title']['value'];
        }

        if ($meta_title == '') {
            $meta_title = $title;
        }

        if (isset($news['meta_description']['value']) && $news['meta_description']['value'] != '') {
            $meta_description = $news['meta_description']['value'];
        }
        if (isset($news['meta_keywords']['value']) && $news['meta_keywords']['value'] != '') {
            $meta_keywords = $news['meta_keywords']['value'];
        }

        $this->template->assert('title', $title);
        $this->template->assert('meta_title', $meta_title);
        $this->template->assert('meta_description', $meta_description);
        $this->template->assert('meta_keywords', $meta_keywords);
        $this->template->assert('breadcrumbs', $this->get_breadcrumbs($breadcrumbs));
        $this->template->assert('news', $news);

        if (1 == (int) $this->getConfigValue('apps.news.append_more_news_view')) {
            $this->template->assert('more_news', $this->get_more_news($news_id));
        } else {
            $this->template->assert('more_news', array());
        }



        $this->set_apps_template('news', $this->getConfigValue('theme'), 'main_file_tpl', 'news_view.tpl');
        
        $this->template->assert('_socialtags', $this->generateSocials($this->getSocialTagsData($news)));
        
        return true;
    }
    
    protected function getSocialTagsData($art){
        
        $params = array();
        
        $params['title'] = $art['title']['value'];
        $params['description'] = $art['anons']['value'];
        $params['image'] = '';
        if(isset($art['image']['value'][0])){
            $params['image'] = $art['image']['value'][0]['preview'];
        }
        $params['url'] = $this->getNewsRoute($art['news_id']['value'], $art['alias']['value'], true);
        
        $params['tw:cardtype'] = 'summary';
        $params['og:type'] = 'news';
        
        return $params;
        
    }

    public function getNewsIdByAlias($url) {

        $DBC = DBC::getInstance();
        $query = 'SELECT news_id, newsalias FROM ' . DB_PREFIX . '_news WHERE newsalias=? AND `date`<=? LIMIT 1';
        if ($this->data_model[$this->table_name]['date']['type'] == 'dtdatetime') {
            $date = date('Y-m-d H:i:s', time());
        } else {
            $date = time();
        }
        $stmt = $DBC->query($query, array($url, $date));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);

            if ($url != $ar['newsalias']) {
                $new_location = $this->getNewsRoute($ar['news_id'], $ar['newsalias']);
                $this->go301($new_location);
            }
            return $ar['news_id'];
        }
        return false;
    }

    public function getNewsTopicIdByAlias($url) {
        if (1 == $this->getConfigValue('apps.news.use_news_topics')) {
            $DBC = DBC::getInstance();
            $query = 'SELECT id, url FROM ' . DB_PREFIX . '_news_topic WHERE url=? LIMIT 1';
            $stmt = $DBC->query($query, array($url));
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                if ($url != $ar['url']) {
                    if ('' != $this->getConfigValue('apps.news.alias')) {
                        $app_alias = $this->getConfigValue('apps.news.alias');
                    } else {
                        $app_alias = 'news';
                    }
                    $new_location = $this->createUrlTpl($app_alias . '/' . $ar['url']);
                    $this->go301($new_location);
                }
                return $ar['id'];
            }
        }
        return false;
    }

    function frontend() {

        if (!$this->getConfigValue('apps.news.enable')) {
            return false;
        }

        if ('' != $this->getConfigValue('apps.news.alias')) {
            $app_alias = $this->getConfigValue('apps.news.alias');
        } else {
            $app_alias = 'news';
        }

        if ('' != $this->getConfigValue('apps.news.item_alias')) {
            $app_item_alias = $this->getConfigValue('apps.news.item_alias');
        } else {
            $app_item_alias = 'news';
        }

        $REQUESTURIPATH = Sitebill::getClearRequestURI();
        $this->initNewsModel();

        if ($REQUESTURIPATH == 'account_news') {
            return $this->front_account();
        }

        if (preg_match('/^' . $app_item_alias . '(\d+).html$/', $REQUESTURIPATH, $matches)) {
            return $this->front_news_item($matches[1]);
        }


        if (preg_match('/' . $app_alias . '\/(.*)[\/]?/', $REQUESTURIPATH, $matches) && false !== ($tid = $this->getNewsTopicIdByAlias($matches[1]))) {
            return $this->front_news_grid_by_topic($tid);
            ;
        }

        if (preg_match('/' . $app_alias . '\/(.*)[\/]?/', $REQUESTURIPATH, $matches) && false !== ($nid = $this->getNewsIdByAlias($matches[1]))) {
            return $this->front_news_item($nid, true);
        }

        if (preg_match('/^' . $app_alias . '$/', $REQUESTURIPATH)) {
            return $this->front_news_grid();
        }

        return false;
    }

    function get_more_news($current_news_id) {
        $news = array();

        if ('' != $this->getConfigValue('apps.news.item_alias')) {
            $app_item_alias = $this->getConfigValue('apps.news.item_alias');
        } else {
            $app_item_alias = 'news';
        }

        if (0 != (int) $this->getConfigValue('apps.news.append_more_news_view_count')) {
            $count = (int) $this->getConfigValue('apps.news.append_more_news_view_count');
        } else {
            $count = $this->getConfigValue('apps.news.news_line.per_page');
        }

        if ($count == 0) {
            $count = 4;
        }

        $checkuser = false;
        if (isset($_SESSION['user_domain_owner']) && (int) $_SESSION['user_domain_owner']['user_id'] != 0) {
            $checkuser = true;
        }
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if ($this->data_model[$this->table_name]['date']['type'] == 'dtdatetime') {
            $date = date('Y-m-d H:i:s', time());
        } else {
            $date = time();
        }
        $query = 'SELECT * FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE `date`<=? AND `news_id` <> ' . $current_news_id . ($checkuser ? ' AND user_id=' . $_SESSION['user_domain_owner']['user_id'] : '') . ' ORDER BY `date` DESC LIMIT ' . $count;
        $stmt = $DBC->query($query, array($date));
        if ($stmt) {
            $i = 0;
            while ($ar = $DBC->fetch($stmt)) {
                //$ar['date']=date('d.m.Y', $ar['date']);
                $ar['_date'] = $ar['date'];
                if ($this->data_model[$this->table_name]['date']['type'] == 'dtdatetime') {
                    $ar['date'] = date('d.m.Y', strtotime($ar['date']));
                } else {
                    $ar['date'] = date('d.m.Y', $ar['date']);
                }
                $news[$i] = $ar;
                $news[$i]['href'] = $this->getNewsRoute($ar['news_id'], $ar['newsalias']);

                if (1 === intval($this->getConfigValue('apps.language.use_langs')) && (!isset($parameters['no_ml']) || 0 === intval($parameters['no_ml']))) {
                    $curlang = $this->getCurrentLang();

                    foreach ($news[$i] as $key => $item_array) {
                        $lang_key = $key . '_' . $curlang;
                        if (isset($news[$i][$lang_key]) && $news[$i][$lang_key] != '') {
                            $news[$i][$key] = $news[$i][$lang_key];
                        }
                    }
                }
                $i++;
            }
        }
        if (count($news) > 0) {
            $hasUploadify = false;
            $uploads = false;
            $model = $this->data_model;
            foreach ($model[$this->table_name] as $mitem) {
                if ($mitem['type'] == 'uploadify_image') {
                    $hasUploadify = true;
                    continue;
                }
            }
            if (!$hasUploadify) {
                foreach ($this->data_model[$this->table_name] as $mitem) {
                    if ($mitem['type'] == 'uploads') {
                        $uploads = $mitem['name'];
                        continue;
                    }
                }
            }

            if ($hasUploadify) {
                foreach ($news as $k => $n) {
                    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
                    $data_model = new Data_Model();
                    $image_array = $data_model->get_image_array('news', 'news', 'news_id', $n['news_id']);
                    if (count($image_array) > 0) {
                        $news[$k]['prev_img'] = $image_array[0]['img_preview'];
                    }
                }
            } elseif ($uploads != '') {
                foreach ($news as $k => $n) {
                    if ($n[$uploads] != '') {
                        $ims = unserialize($n[$uploads]);
                    } else {
                        $ims = array();
                    }
                    if (isset($ims[0])) {
                        $news[$k]['prev_img'] = SITEBILL_MAIN_URL . '/img/data/' . $ims[0]['preview'];
                        $news[$k]['normal_img'] = SITEBILL_MAIN_URL . '/img/data/' . $ims[0]['normal'];
                    }
                }
            }
        }
        return $news;
    }

}