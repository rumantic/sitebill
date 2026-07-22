<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * News fronend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class news_site extends news_admin
{

    public function front_account()
    {
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/news/admin/user_admin.php';
        $AUN = new user_news_admin();
        $this->template->assert('main', $AUN->main());
        return true;
    }

    public function front_news_grid($topic_id = 0)
    {

        $tplvars = [];

        if($topic_id != 0){
            $tplvars['news_page_mode'] = 'listbycategory';
        }else{
            $tplvars['news_page_mode'] = 'listprimary';
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

        $meta_title = $this->getConfigValue('apps.news.meta_title');
        $meta_description = $this->getConfigValue('apps.news.meta_desription');
        $meta_keywords = $this->getConfigValue('apps.news.meta_keywords');
        $description = '';
        if ('' != $this->getConfigValue('apps.news.app_title')) {
            $title = $this->getConfigValue('apps.news.app_title');
        } else {
            $title = Multilanguage::_('PAGE_TITLE', 'news');
        }

        $breadcrumbs = [];

        $breadcrumbs[] = [
            'href' => $this->createUrlTpl(''),
            'name' => Multilanguage::_('L_HOME')
        ];


        if ($topic_id != 0) {
            $breadcrumbs[] = [
                'href' => $this->createUrlTpl($app_alias),
                'name' => $app_title
            ];

            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
            $ATH = new Admin_Table_Helper();
            $news_topic = $ATH->load_model('news_topic', false);

            $DM = new Data_Model();

            $news_topic = $DM->init_model_data_from_db('news_topic', 'id', $topic_id, $news_topic['news_topic'], TRUE);
            $news_topic = $DM->init_language_values($news_topic);

            $tplvars['news_topic'] = $news_topic;

            $breadcrumbs[] = [
                'href' => '',
                'name' => $news_topic['name']['value']
            ];

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
            $breadcrumbs[] = [
                'href' => '',
                'name' => $app_title
            ];
        }


        $page = (int)$this->getRequestValue('page');
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
            $tplvars['news_topics'] = $news_topics;
        } else {
            $tplvars['news_topics'] = [];
        }

        if(1 === (int)$this->getConfigValue('apps.news.groupbycountry')){
            $tplvars['news_countries'] = $this->getNewsCountryList(null, null);
        }

        $tplvars['title'] = $title;
        $tplvars['meta_title'] = $meta_title;
        $tplvars['meta_description'] = $meta_description;
        $tplvars['meta_keywords'] = $meta_keywords;
        $tplvars['description'] = $description;
        $tplvars['breadcrumbs'] = $this->getFrontend()->breadcrumbs2tpl($breadcrumbs);
        $tplvars['breadcrumbs_array'] = $breadcrumbs;

        $tplvars['news'] = $news;

        $tplvars = $this->appendNewsListCustomData($tplvars);

        foreach ($tplvars as $tvk => $tvv){
            $this->template->assert($tvk, $tvv);
        }

        $this->set_apps_template('news', $this->getConfigValue('theme'), 'main_file_tpl', 'news_grid.tpl');
        return true;
    }

    /**
     * @param int $countryid
     * @param int $categoryalias
     * @return array
     */
    public function getNewsCountryList($countryid = null, $categoryalias = null){
        $list = [];
        $postfix = $this->getLangPostfix($this->getCurrentLang());
        $DBC = DBC::getInstance();
        $query = 'SELECT `country_id`, `name'.$postfix.'` AS name, `url` FROM '.DB_PREFIX.'_country ORDER BY name ASC';
        $stmt=$DBC->query($query);
        if($stmt){
            while($ar=$DBC->fetch($stmt)){
                $ar['href'] = $this->createUrlTpl($this->getConfigValue('apps.news.alias').(!is_null($categoryalias) ? '/'.$categoryalias : '').'/'.$ar['url']);
                if($ar['country_id'] == $countryid){
                    $ar['selected'] = 1;
                }else{
                    $ar['selected'] = 0;
                }
                $list[$ar['country_id']] = $ar;
            }
        }
        return $list;
    }

    /**
     * @param array $country
     * @return bool
     */
    public function front_news_grid_by_country($country){

        $tplvars = [];

        $tplvars['country'] = $country;

        $app_alias = ('' !== $this->getConfigValue('apps.news.alias') ? $this->getConfigValue('apps.news.alias') : 'news');
        $app_title = ('' !== $this->getConfigValue('apps.news.folder_title') ? $this->getConfigValue('apps.news.folder_title') : Multilanguage::_('PAGE_TITLE', 'news'));

        $this->template->assert('news_page_mode', 'listbycountry');
        $tplvars['news_page_mode'] = 'listbycountry';

        $meta = $this->getListByCountryMeta($country);


        foreach($meta as $key => $value){
            $tplvars[$key] = $value;
        }

        $breadcrumbs = [];

        $breadcrumbs[] = [
            'href' => $this->createUrlTpl(''),
            'name' => Multilanguage::_('L_HOME')
        ];

        $breadcrumbs[] = [
            'href' => $this->createUrlTpl($app_alias),
            'name' => $app_title
        ];

        $breadcrumbs[] = [
            'href' => '',
            'name' => $meta['title']
        ];

        $page = (int)$this->getRequestValue('page');
        $per_page = $this->getConfigValue('apps.news.front.per_page');

        require_once SITEBILL_DOCUMENT_ROOT . '/apps/news/site/news_grid_constructor.php';
        $NGC = new News_Grid_Constructor();
        $news = $NGC->get_sitebill_adv_ext(array('page' => $page, 'per_page' => $per_page, 'country_id' => $country['country_id']['value']));




        if(1 === (int)$this->getConfigValue('apps.news.groupbycountry')){
            $tplvars['news_countries'] = $this->getNewsCountryList($country['country_id']['value'], null);
        }

        $tplvars['breadcrumbs'] = $this->getFrontend()->breadcrumbs2tpl($breadcrumbs);
        $tplvars['news'] = $news;

        $tplvars = $this->appendNewsListCustomData($tplvars);

        foreach ($tplvars as $tvk => $tvv){
            $this->template->assert($tvk, $tvv);
        }

        $this->set_apps_template('news', $this->getConfigValue('theme'), 'main_file_tpl', 'news_grid.tpl');
        return true;
    }

    /**
     * Используйте эту функцию для переопределения мета-данных
     * либо дополните модель страны полями articles_title, articles_meta_title, articles_meta_description
     * @param array $country_info
     * @return array
     */
    function getListByCountryMeta($country){

        $title = 'Новости о '.$country['name']['value'];
        $meta_title = '';
        $meta_description = '';
        if(isset($country['news_title']) && $country['news_title']['value'] != ''){
            $title = $country['news_title']['value'];
        }

        if(isset($country['news_meta_title']) && $country['news_meta_title']['value'] != ''){
            $meta_title = $country['news_meta_title']['value'];
        }

        if(isset($country['news_meta_description']) && $country['news_meta_description']['value'] != ''){
            $meta_description = $country['news_meta_description']['value'];
        }

        return array(
            'title' => $title,
            'meta_title' => $meta_title,
            'meta_description' => $meta_description
        );
    }

    public function front_news_grid_by_topic($topic_id)
    {
        return $this->front_news_grid($topic_id);
    }

    /**
     * @param int $news_id
     * @param bool $by_alias
     * @return bool
     */
    public function front_news_item($news_id, $by_alias = false)
    {
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

        $news = $data_model->init_language_values($news);

        if ($news['date']['type'] === 'dtdatetime' && strtotime($news['date']['value']) > time()) {
            return false;
        }
        if ($news['date']['type'] === 'date' && $news['date']['value'] > time()) {
            return false;
        }

        $tplvars = [];

        if (1 === (int)$this->getConfigValue('apps.news.collect_viewstat') && 1 === (int)$this->getConfigValue('apps.statoid.enable')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/statoid/admin/admin.php';
            $ST = new statoid_admin();
            $ST->collectObjectViewStat('news', 'news_id', $news_id, true);
        }

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/news/admin/news_model.php');
        $Object = new News_Model();

        $newsCountry = false;
        if(1 === (int)$this->getConfigValue('apps.news.groupbycountry') && isset($news['country_id']) && $news['country_id']['value'] !== 0){
            require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
            $ATH = new Admin_Table_Helper();
            $country_model = $ATH->load_model('country', true);
            $country_model = $country_model['country'];

            $newsCountry = $data_model->init_model_data_from_db('country', 'country_id', $news['country_id']['value'], $country_model, true);
            $newsCountry = $data_model->init_language_values($newsCountry);
        }

        $tplvars['newsCountry'] = $newsCountry;

        $newsMeta = $this->getNewsMeta($news);
        foreach ($newsMeta as $mk => $mv){
            $tplvars[$mk] = $mv;
        }

        $app_alias = ('' !== $this->getConfigValue('apps.news.alias') ? $this->getConfigValue('apps.news.alias') : 'news');

        $app_title = ('' !== $this->getConfigValue('apps.news.folder_title', '') ? $this->getConfigValue('apps.news.folder_title') : Multilanguage::_('PAGE_TITLE', 'news'));


        $breadcrumbs = [];

        $breadcrumbs[] = [
            'href' => $this->createUrlTpl(''),
            'name' => Multilanguage::_('L_HOME')
        ];

        $hasUploadify = false;
        $uploads = false;
        foreach ($model[$this->table_name] as $mitem) {
            if ($mitem['type'] === 'uploadify_image') {
                $hasUploadify = true;
                continue;
            }
        }
        if (!$hasUploadify) {
            foreach ($this->data_model[$this->table_name] as $mitem) {
                if ($mitem['type'] === 'uploads') {
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
            if ($news[$uploads]['value'][0]['preview'] != '') {
                $news['prev_img'] = $this->createMediaIncPath($news[$uploads]['value'][0], 'preview');
            }
            if ($news[$uploads]['value'][0]['normal'] != '') {
                $news['normal_img'] = $this->createMediaIncPath($news[$uploads]['value'][0]);
            }
            if (is_array($news[$uploads]['value']) and count($news[$uploads]['value']) > 0) {
                $news['img'] = $news[$uploads]['value'];
            }
        }

        if ($news['date']['type'] === 'dtdatetime') {
            $news['date']['value_string'] = date('d.m.Y', strtotime($news['date']['value']));
        } else {
            if (false !== strpos($news['date']['value'], ".")) {
                $news['date']['value_string'] = $news['date']['value'];
            } else {
                $news['date']['value_string'] = date('d.m.Y', $news['date']['value']);
            }
        }

        $breadcrumbs[] = [
            'href' => $this->createUrlTpl($app_alias),
            'name' => $app_title
        ];

        if($newsCountry){
            $breadcrumbs[] = [
                'href' => $this->createUrlTpl($app_alias.'/'.$newsCountry['url']['value']),
                'name' => $newsCountry['name']['value']
            ];
        }

        if (1 == $this->getConfigValue('apps.news.use_news_topics')) {
            $all_topics = $this->getNewsTopicsList();
            if (isset($all_topics[$news['news_topic_id']['value']])) {
                $breadcrumbs[] = [
                    'href' => $all_topics[$news['news_topic_id']['value']]['url'],
                    'name' => $all_topics[$news['news_topic_id']['value']]['name']
                ];
            }
            $tplvars['news_topics'] = $all_topics;
        } else {
            $tplvars['news_topics'] = [];
        }

        $breadcrumbs[] = [
            'href' => '',
            'name' => $newsMeta['title']
        ];

        $tplvars['breadcrumbs'] = $this->getFrontend()->breadcrumbs2tpl($breadcrumbs);
        $this->template->assign('breadcrumbs_array', $breadcrumbs);

        $tplvars['news'] = $news;

        if (1 == (int)$this->getConfigValue('apps.news.append_more_news_view')) {
            $tplvars['more_news'] = $this->get_more_news($news);
        } else {
            $tplvars['more_news'] = [];
        }

        $tplvars = $this->appendNewsItemCustomData($tplvars);

        foreach ($tplvars as $tvk => $tvv){
            $this->template->assert($tvk, $tvv);
        }

        $this->set_apps_template('news', $this->getConfigValue('theme'), 'main_file_tpl', 'news_view.tpl');

        $this->template->assert('_socialtags', $this->generateSocials($this->getSocialTagsData($news)));

        return true;
    }

    /**
     * Formation of article metadata
     * @param array articles model $art
     * @return array
     */
    protected function getNewsMeta($art) {
        $return = array(
            'title' => '',
            'meta_title' => '',
            'meta_description' => '',
            'meta_keywords' => ''
        );

        $return['title'] = $art['title']['value'];

        if(isset($art['meta_title']) && $art['meta_title']['value']!=''){
            $return['meta_title'] = $art['meta_title']['value'];
        }
        if(isset($art['meta_description']) && $art['meta_description']['value']!=''){
            $return['meta_description'] = $art['meta_description']['value'];
        }
        if(isset($art['meta_keywords']) && $art['meta_keywords']['value']!=''){
            $return['meta_keywords'] = $art['meta_keywords']['value'];
        }
        if($return['meta_title'] == ''){
            $return['meta_title'] = $return['title'];
        }

        return $return;
    }


    protected function getSocialTagsData($art)
    {

        $params = array();

        $params['title'] = $art['title']['value'];
        $params['description'] = $art['anons']['value'];
        $params['image'] = '';
        if (isset($art['image']['value'][0])) {
            $params['image'] = $art['image']['value'][0]['preview'];
        }
        $params['url'] = $this->getNewsRoute($art['news_id']['value'], $art['alias']['value'], true);

        $params['tw:cardtype'] = 'summary';
        $params['og:type'] = 'news';

        return $params;

    }

    public function getNewsIdByAlias($url)
    {

        $DBC = DBC::getInstance();
        $query = 'SELECT news_id, newsalias FROM ' . DB_PREFIX . '_news WHERE newsalias=? AND `date`<=? LIMIT 1';
        if ($this->data_model[$this->table_name]['date']['type'] === 'dtdatetime') {
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

    /**
     * Return country model object by alias or false when fail
     * @param string $alias
     * @return array|bool
     */
    public function getNewsCountryByAlias($alias){
        $DBC = DBC::getInstance();
        $query = 'SELECT country_id, url FROM ' . DB_PREFIX . '_country WHERE url = ? LIMIT 1';
        $stmt = $DBC->query($query, [$alias]);
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($alias !== $ar['url']) {
                if ('' != $this->getConfigValue('apps.news.alias')) {
                    $app_alias = $this->getConfigValue('apps.news.alias');
                } else {
                    $app_alias = 'news';
                }
                $new_location = $this->createUrlTpl($app_alias . '/' . $ar['url']);
                $this->go301($new_location);
                exit();
            }
            $countryid = $ar['country_id'];
            require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
            $ATH = new Admin_Table_Helper();
            $model = $ATH->load_model('country', true, true);
            if(!is_array($model) || !is_array($model['country']) || empty($model['country'])){
                return false;
            }
            $model = $model['country'];
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
            $DM = new Data_Model();
            $model = $DM->init_model_data_from_db('country', 'country_id', $countryid, $model, true);
            if($model){
                $model = $DM->init_language_values($model);
            }

            return $model;
        }
        return false;
    }

    public function getNewsTopicIdByAlias($url)
    {
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

    function frontend()
    {

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

        if ($REQUESTURIPATH === 'account_news') {
            return $this->front_account();
        }

        if($REQUESTURIPATH === $app_alias.'/sitemap.xml'){
            $this->getSitemap();
            return true;
        }

        if (preg_match('/^' . $app_item_alias . '(\d+).html$/', $REQUESTURIPATH, $matches)) {
            return $this->front_news_item($matches[1]);
        }


        if (1 === (int)$this->getConfigValue('apps.news.use_news_topics') && preg_match('/' . $app_alias . '\/(.*)[\/]?/', $REQUESTURIPATH, $matches) && false !== ($tid = $this->getNewsTopicIdByAlias($matches[1]))) {
            return $this->front_news_grid_by_topic($tid);
        }
        //dd($country);
        if(1 === (int)$this->getConfigValue('apps.news.groupbycountry') && preg_match('/' . $app_alias . '\/(.*)[\/]?/', $REQUESTURIPATH, $matches) && false !== ($country = $this->getNewsCountryByAlias($matches[1]))){
            return $this->front_news_grid_by_country($country);
        }

        if (preg_match('/' . $app_alias . '\/(.*)[\/]?/', $REQUESTURIPATH, $matches) && false !== ($nid = $this->getNewsIdByAlias($matches[1]))) {
            return $this->front_news_item($nid, true);
        }

        if (preg_match('/^' . $app_alias . '$/', $REQUESTURIPATH)) {
            return $this->front_news_grid();
        }

        return false;
    }

    /**
     * Select similar news list
     * @param array $newsItem
     * @return array
     */
    function get_more_news($newsItem)
    {

        $current_news_id = $newsItem['news_id']['value'];
        $news = array();
        if (0 != (int)$this->getConfigValue('apps.news.append_more_news_view_count')) {
            $count = (int)$this->getConfigValue('apps.news.append_more_news_view_count');
        } else {
            $count = $this->getConfigValue('apps.news.news_line.per_page');
        }

        if ($count == 0) {
            $count = 4;
        }

        $checkuser = false;
        if (isset($_SESSION['user_domain_owner']) && (int)$_SESSION['user_domain_owner']['user_id'] != 0) {
            $checkuser = true;
        }
        $DBC = DBC::getInstance();
        if ($this->data_model[$this->table_name]['date']['type'] === 'dtdatetime') {
            $date = date('Y-m-d H:i:s', time());
        } else {
            $date = time();
        }

        $qp = [];
        $qv = [];

        $qp[] = '`date` <= ?';
        $qv[] = $date;

        $qp[] = '`news_id` <> ?';
        $qv[] = $current_news_id;

        if($checkuser){
            $qp[] = '`user_id` = ?';
            $qv[] = $_SESSION['user_domain_owner']['user_id'];
        }

        if($this->use_topics && $newsItem['news_topic_id']['value'] != 0){
            $qp[] = '`news_topic_id` = ?';
            $qv[] = $newsItem['news_topic_id']['value'];
        }

        if(1 === (int)$this->getConfigValue('apps.news.groupbycountry') && $newsItem['country_id']['value'] != 0){
            $qp[] = '`country_id` = ?';
            $qv[] = $newsItem['country_id']['value'];
        }



        $query = 'SELECT * FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE '.implode(' AND ', $qp) . ' ORDER BY `date` DESC LIMIT ' . $count;
        $stmt = $DBC->query($query, $qv);
        if ($stmt) {
            $i = 0;
            while ($ar = $DBC->fetch($stmt)) {
                $ar['_date'] = $ar['date'];
                if ($this->data_model[$this->table_name]['date']['type'] === 'dtdatetime') {
                    $ar['date'] = date('d.m.Y', strtotime($ar['date']));
                } else {
                    $ar['date'] = date('d.m.Y', $ar['date']);
                }
                $news[$i] = $ar;
                $news[$i]['href'] = $this->getNewsRoute($ar['news_id'], $ar['newsalias']);

                if (1 === intval($this->getConfigValue('apps.language.use_langs'))) {
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
                if ($mitem['type'] === 'uploadify_image') {
                    $hasUploadify = true;
                    continue;
                }
            }
            if (!$hasUploadify) {
                foreach ($this->data_model[$this->table_name] as $mitem) {
                    if ($mitem['type'] === 'uploads') {
                        $uploads = $mitem['name'];
                        continue;
                    }
                }
            }
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
            $data_model = new Data_Model();

            if ($hasUploadify) {
                foreach ($news as $k => $n) {
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
                        //
                        $ims = $data_model->sharder_mirror($ims, true);
                        $news[$k]['prev_img'] = $this->createMediaIncPath($ims[0], 'preview');
                        $news[$k]['norm_img'] = $this->createMediaIncPath($ims[0]);
                    }
                }
            }
        }
        return $news;
    }

    /**
     * Allows to append additional data to news page
     * use this function localized version for appending data
     * @param array $data
     * @return array
     */
    protected function appendNewsItemCustomData($data){
        return $data;
    }

    /**
     * Allows to append additional data to newsl listing page
     * use this function localized version for appending data
     * @param array $data
     * @return array
     */
    protected function appendNewsListCustomData($data){
        return $data;
    }

    protected function convertModelToSitemapItem($modelItem){
        $datefieldtypeIsTime = true;
        if ($this->data_model[$this->table_name]['date']['type'] == 'dtdatetime') {
            $datefieldtypeIsTime = false;
        }
        $converted = [
            'url' => $this->getNewsRoute($modelItem[$this->primary_key]['value'], $modelItem['newsalias']['value'], true),
            'name' => $modelItem['title']['value'],
            'date' => ($datefieldtypeIsTime ? date('Y-m-d', $modelItem['date']['value']) : date('Y-m-d', strtotime($modelItem['date']['value']))),
            'title' => $modelItem['title']['value'],
        ];
        return $converted;
    }

    function getSitemap(){
        $DBC = DBC::getInstance();

        $qparams = [];

        $datefieldtypeIsTime = true;

        if ($this->data_model[$this->table_name]['date']['type'] == 'dtdatetime') {
            $qparams[] = date('Y-m-d H:i:s', time());
            $datefieldtypeIsTime = false;
        } else {
            $qparams[] = time();
        }


        $newsids = array();

        $query = 'SELECT `news_id` FROM '.DB_PREFIX.'_news WHERE `date` <= ? ORDER BY `' . $this->primary_key . '` ASC';
        $stmt = $DBC->query($query, $qparams);
        if($stmt){
            while($ar = $DBC->fetch($stmt)){
                $newsids[] = $ar['news_id'];
            }
        }

        $news = [];

        if(!empty($newsids)){
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
            $ATH = new Admin_Table_Helper();
            $form_data = $ATH->load_model($this->table_name, false);

            require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php';
            $Object = new Data_Model();
            $news = $Object->init_model_data_from_db_multi('news', 'news_id', $newsids, $form_data['news'], true);

            foreach($news as $id => $art){
                $news[$id] = $Object->init_language_values($art);
            }
        }





        $collector = [];

        if(!empty($news)){
            $curlang = $this->getCurrentLang();

            foreach ($news as $item){
                $convertedItem = $this->convertModelToSitemapItem($item);
                $cc = array();
                $cc[] = '<url>';
                $cc[] = '<loc>'.$convertedItem['url'].'</loc>';
                $cc[] = '<news:news>';
                $cc[] = '<news:publication>';
                $cc[] = '<news:name>'.$convertedItem['name'].'</news:name>';
                $cc[] = '<news:language>'.$curlang.'</news:language>';
                $cc[] = '</news:publication>';
                $cc[] = '<news:publication_date>'.$convertedItem['date'].'</news:publication_date>';
                $cc[] = '<news:title>'.$convertedItem['title'].'</news:title>';
                $cc[] = '</news:news>';
                $cc[] = '</url>';
                $collector[] = implode('', $cc);
            }
        }

        header("Content-Type: text/xml; charset=utf-8");
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">'.implode("\n", $collector).'</urlset>';
        exit();


    }

}
