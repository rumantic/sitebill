<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * News admin backend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class news_admin extends Object_Manager
{

    /**
     * Constructor
     */
    function __construct($mod_name = '')
    {
        parent::__construct();
        Multilanguage::appendAppDictionary('news');
        $this->action = 'news';
        $this->use_topics = false;
        $this->app_title = Multilanguage::_('APPLICATION_NAME', 'news');

        parent::set_mod($mod_name);
        if ($this->mod_name === 'topic') {
            $this->initNewsTopicModel();
        } else {
            $this->initNewsModel();
        }

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/config/admin/admin.php');
        $config_admin = new config_admin();

        $config_admin->addParamToConfig('apps.news.enable', '1', 'Включить News.Apps', SConfig::$fieldtypeCheckbox);

        $config_admin->addParamToConfig('apps.news.front.per_page', '5', 'Количество новостей на страницу');

        $config_admin->addParamToConfig('apps.news.news_line.per_page', '3', 'Количество новостей в новостном блоке на главной странице');


        $config_admin->addParamToConfig('apps.news.use_news_topics', '0', 'Использовать категории для новостей', SConfig::$fieldtypeCheckbox);

        if (1 == $this->getConfigValue('apps.news.use_news_topics')) {
            $this->use_topics = true;
        }

        $config_admin->addParamToConfig('apps.news.alias', 'news', 'Алиас адресов приложения');
        $config_admin->addParamToConfig('apps.news.item_alias', 'news', 'Подстановочный алиас');
        $config_admin->addParamToConfig('apps.news.app_title', 'Архив новостей', 'Заголовок приложения');
        $config_admin->addParamToConfig('apps.news.folder_title', 'Новости', 'Заголовок приложения в хлебных крошках');
        $config_admin->addParamToConfig('apps.news.append_more_news_view', '1', 'Выводить дополнительные новости в просмотре новости', SConfig::$fieldtypeCheckbox);
        $config_admin->addParamToConfig('apps.news.append_more_news_view_count', '2', 'Количество дополнительных новостей в просмотре новости');
        $config_admin->addParamToConfig('apps.news.meta_title', '', 'META заголовок');
        $config_admin->addParamToConfig('apps.news.meta_desription', '', 'META описание');
        $config_admin->addParamToConfig('apps.news.meta_keywords', '', 'META ключевые слова');
        $config_admin->addParamToConfig('apps.news.preload_column', '1', 'Формировать колонку новостей для главной', SConfig::$fieldtypeCheckbox);
        $config_admin->addParamToConfig('apps.news.preload_column_by_location', '0', 'Загрузка списка новостей по локации (город или страна)', SConfig::$fieldtypeCheckbox);

        $config_admin->addParamToConfig('apps.news.share_access', '0', 'Разделять доступ к записям в админке', SConfig::$fieldtypeCheckbox);
        $config_admin->addParamToConfig('apps.news.sitemaproot', '1', 'Выдавать ссылку на раздел в карту сайта', SConfig::$fieldtypeCheckbox);
        $config_admin->addParamToConfig('apps.news.sitemaptopics', '0', 'Выдавать ссылки на разделы новостей в карту сайта', SConfig::$fieldtypeCheckbox);
        $config_admin->addParamToConfig('apps.news.sitemapitems', '0', 'Выдавать ссылки на отдельные новости в карту сайта', SConfig::$fieldtypeCheckbox);
        $config_admin->addParamToConfig('apps.news.sitemaproot_changefreq', '4', 'Вероятная частота изменения <b>страницы раздела новостей</b>. Одно из числовых значений (1-всегда, 2-ежечасно, 3-ежедневно, 4-еженедельно, 5-ежемесячно, 6-ежегодно, 7-никогда)');
        $config_admin->addParamToConfig('apps.news.sitemapitems_changefreq', '7', 'Вероятная частота изменения <b>страницы новости</b>. Одно из числовых значений (1-всегда, 2-ежечасно, 3-ежедневно, 4-еженедельно, 5-ежемесячно, 6-ежегодно, 7-никогда)');
        $config_admin->addParamToConfig('apps.news.sitemaptopics_changefreq', '4', 'Вероятная частота изменения <b>страницы подразделов новостей</b>. Одно из числовых значений (1-всегда, 2-ежечасно, 3-ежедневно, 4-еженедельно, 5-ежемесячно, 6-ежегодно, 7-никогда)');
        $config_admin->addParamToConfig('apps.news.sitemaproot_priority', '0.5', 'Приоритетность URL <b>страницы раздела</b> относительно других URL на Вашем сайте. Диапазон от 0.0 до 1.0');
        $config_admin->addParamToConfig('apps.news.sitemapitems_priority', '0.5', 'Приоритетность URL <b>страницы новости</b> относительно других URL на Вашем сайте. Диапазон от 0.0 до 1.0');
        $config_admin->addParamToConfig('apps.news.sitemaptopics_priority', '0.5', 'Приоритетность URL <b>страницы подраздела новостей</b> относительно других URL на Вашем сайте. Диапазон от 0.0 до 1.0');

        /*if (!$config_admin->check_config_item('apps.news.user_enable_access_type')) {
            $config_admin->addParamToConfig('apps.news.user_enable_access_type', '0', 'Тип доступа к новостям из ЛК');
        }*/

        $config_admin->addParamToConfig(
            'apps.news.use_active_status',
            '0',
            'Выводить на фронте только активные новости (необходимо поле news.active)',
            SConfig::$fieldtypeCheckbox
        );

        /*$config_admin->addParamToConfig('apps.news.collect_viewstat', '0', 'Учитывать просмотры новостей', SConfig::$fieldtypeCheckbox);*/

        $config_admin->addParamToConfig('apps.news.groupbycountry', '0', 'Группировка новостей по странам', SConfig::$fieldtypeCheckbox);

    }

    protected function checkOwning($id, $user_id)
    {
        $DBC = DBC::getInstance();
        $query = 'SELECT COUNT(`' . $this->primary_key . '`) AS _cnt FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE `' . $this->primary_key . '`=? AND `user_id`=?';
        $stmt = $DBC->query($query, array($id, $user_id));
        $res = false;
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ((int)$ar['_cnt'] === 1) {
                $res = true;
            }
        }
        return $res;
    }

    public function sitemapHTML($sitemap)
    {
        $urls = array();
        $DBC = DBC::getInstance();
        $page = ((int)$this->getRequestValue('page') > 0 ? (int)$this->getRequestValue('page') : 1);
        $per_page = $this->getConfigValue('apps.page.per_page');

        $start = ($page - 1) * $per_page;
        if ($this->data_model[$this->table_name]['date']['type'] === 'dtdatetime') {
            $date = date('Y-m-d H:i:s', time());
        } else {
            $date = time();
        }
        if (isset($this->data_model[$this->table_name]['is_service'])) {
            $query = 'SELECT * FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE `is_service`=0 ORDER BY ' . $this->primary_key . ' DESC';
        } else {
            $query = 'SELECT * FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE `date`<=? ORDER BY ' . $this->primary_key . ' DESC';
        }

        $stmt = $DBC->query($query, array($date));
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ar['href'] = SITEBILL_MAIN_URL . '/' . trim($ar['uri'], '/');
                $urls[] = array('t' => $ar['title'], 'h' => $ar['href']);
            }
        }
        return $urls;
    }

    public function sitemap_pages_count($sitemap)
    {
        $cnt = 0;
        if (1 == (int)$this->getConfigValue('apps.news.enable')) {
            if (1 == (int)$this->getConfigValue('apps.news.sitemaproot')) {
                $cnt += 1;
            }
            if (1 === intval($this->getConfigValue('apps.news.sitemaptopics')) && 1 === intval($this->getConfigValue('apps.news.use_news_topics'))) {
                $ntl = $this->getNewsTopicsList();
                if (!empty($ntl)) {
                    $cnt += count($ntl);
                }
            }
            if (1 == (int)$this->getConfigValue('apps.news.sitemapitems')) {
                $DBC = DBC::getInstance();
                if ($this->data_model[$this->table_name]['date']['type'] === 'dtdatetime') {
                    $date = date('Y-m-d H:i:s', time());
                } else {
                    $date = time();
                }
                $query = 'SELECT COUNT(`' . $this->primary_key . '`) AS _cnt FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE `date`<=?';
                $stmt = $DBC->query($query, array($date));

                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    $cnt += $ar['_cnt'];
                }
            }
        }
        if ($cnt > 0) {
            $cnt = intval(ceil($cnt / $sitemap->getPerPageCount()));
        }
        return $cnt;
    }

    public function sitemap($sitemap, $page = 0)
    {

        //$perpagecount = $sitemap->getPerPageCount();
        $offset = 0;

        $orderstring = '';
        if ($page == 0) {
            $orderstring = ' ORDER BY `' . $this->primary_key . '` ASC';
        }

        $news = array();
        if (1 == (int)$this->getConfigValue('apps.news.enable')) {
            if (1 == (int)$this->getConfigValue('apps.news.sitemaproot')) {
                if ('' != $this->getConfigValue('apps.news.alias')) {
                    $app_alias = $this->getConfigValue('apps.news.alias');
                } else {
                    $app_alias = 'news';
                }
                $news[] = array('url' => SITEBILL_MAIN_URL . '/' . $app_alias . self::$_trslashes, 'changefreq' => $sitemap->validateFrequency($this->getConfigValue('apps.news.sitemaproot_changefreq')), 'priority' => $sitemap->validatePriority($this->getConfigValue('apps.news.sitemaproot_priority')));
            }
            if (1 === intval($this->getConfigValue('apps.news.sitemaptopics')) && 1 === intval($this->getConfigValue('apps.news.use_news_topics'))) {
                $ntl = $this->getNewsTopicsList();
                if (!empty($ntl)) {
                    foreach ($ntl as $n) {
                        $news[] = array('url' => $n['sitemap_url'], 'changefreq' => $sitemap->validateFrequency($this->getConfigValue('apps.news.sitemaptopics_changefreq')), 'priority' => $sitemap->validatePriority($this->getConfigValue('apps.news.sitemaptopics_priority')));
                    }
                }
            }
            if (1 == (int)$this->getConfigValue('apps.news.sitemapitems')) {
                $DBC = DBC::getInstance();
                if ($this->data_model[$this->table_name]['date']['type'] === 'dtdatetime') {
                    $date = date('Y-m-d H:i:s', time());
                } else {
                    $date = time();
                }
                $query = 'SELECT `' . $this->primary_key . '`, `newsalias` FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE `date`<=?' . $orderstring;
                $stmt = $DBC->query($query, array($date));

                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        $href = $this->getNewsRoute($ar[$this->primary_key], $ar['newsalias'], true);
                        $news[] = array('url' => $href, 'changefreq' => $sitemap->validateFrequency($this->getConfigValue('apps.news.sitemapitems_changefreq')), 'priority' => $sitemap->validatePriority($this->getConfigValue('apps.news.sitemapitems_priority')));
                    }
                }
            }

        }
        return $news;
    }

    protected function _deleteAction()
    {
        if ((1 === (int)$this->getConfigValue('check_permissions')) && $this->mod_name === 'topic' && ($_SESSION['current_user_group_name'] !== 'admin')) {
            return '';
        }
        if ((1 === (int)$this->getConfigValue('check_permissions')) && (1 === (int)$this->getConfigValue('apps.news.share_access')) && ($_SESSION['current_user_group_name'] !== 'admin')) {
            $rs = '';

            $news_id = (int)$this->getRequestValue($this->primary_key);
            $user_id = (int)$_SESSION['user_id_value'];

            if ($this->checkOwning($news_id, $user_id)) {
                $this->delete_data($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key));
                if ($this->getError()) {
                    $rs .= '<div align="center">' . Multilanguage::_('L_ERROR_ON_DELETE') . ': ' . $this->GetErrorMessage() . '<br>';
                    $rs .= '<a href="?action=' . $this->action . '">ОК</a>';
                    $rs .= '</div>';
                } else {
                    $rs .= $this->grid();
                }
            } else {
                $rs .= $this->grid();
            }


            return $rs;
        }

        return parent::_deleteAction();
    }

    protected function _editAction()
    {
        $news_id = (int)$this->getRequestValue($this->primary_key);
        $user_id = (int)$_SESSION['user_id_value'];


        if ((1 === (int)$this->getConfigValue('check_permissions')) && $this->mod_name === 'topic' && ($_SESSION['current_user_group_name'] !== 'admin')) {
            return Multilanguage::_('L_ACCESS_DENIED');
        }

        if ((1 === (int)$this->getConfigValue('check_permissions')) && (1 === (int)$this->getConfigValue('apps.news.share_access')) && ($_SESSION['current_user_group_name'] !== 'admin')) {
            if ($this->checkOwning($news_id, $user_id)) {
                return parent::_editAction();
            }

            return Multilanguage::_('L_ACCESS_DENIED');
        }

        return parent::_editAction();
    }

    protected function _edit_doneAction()
    {
        $news_id = (int)$this->getRequestValue($this->primary_key);
        $user_id = (int)$_SESSION['user_id'];
        if ((1 === (int)$this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name'] !== 'admin') && $this->mod_name === 'topic') {
            return Multilanguage::_('L_ACCESS_DENIED');
        }

        if ((1 === (int)$this->getConfigValue('check_permissions')) && (1 === (int)$this->getConfigValue('apps.news.share_access')) && ($_SESSION['current_user_group_name'] !== 'admin')) {
            if ($this->checkOwning($news_id, $user_id)) {
                $rs = '';
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
                $data_model = new Data_Model();
                $form_data = $this->data_model;

                $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);

                $new_values = $this->getRequestValue('_new_value');
                if (1 == $this->getConfigValue('use_combobox') && count($new_values) > 0) {
                    $remove_this_names = array();
                    foreach ($form_data[$this->table_name] as $fd) {
                        if (isset($new_values[$fd['name']]) && $new_values[$fd['name']] != '' && $fd['combo'] == 1) {
                            $id = md5(time() . '_' . random_int(100, 999));
                            $remove_this_names[] = $id;
                            $form_data[$this->table_name][$id]['value'] = $new_values[$fd['name']];
                            $form_data[$this->table_name][$id]['type'] = 'auto_add_value';
                            $form_data[$this->table_name][$id]['dbtype'] = 'notable';
                            $form_data[$this->table_name][$id]['value_table'] = $form_data[$this->table_name][$fd['name']]['primary_key_table'];
                            $form_data[$this->table_name][$id]['value_primary_key'] = $form_data[$this->table_name][$fd['name']]['primary_key_name'];
                            $form_data[$this->table_name][$id]['value_field'] = $form_data[$this->table_name][$fd['name']]['value_name'];
                            $form_data[$this->table_name][$id]['assign_to'] = $fd['name'];
                            $form_data[$this->table_name][$id]['required'] = 'off';
                            $form_data[$this->table_name][$id]['unique'] = 'off';
                        }
                    }
                }
                $data_model->forse_auto_add_values($form_data[$this->table_name]);
                $form_data[$this->table_name] = $this->_before_check_action($form_data[$this->table_name], 'edit');
                //$data_model->clear_auto_add_values($form_data[$this->table_name]);
                if (!$this->check_data($form_data[$this->table_name])) {
                    $form_data[$this->table_name] = $this->removeTemporaryFields($form_data[$this->table_name], $remove_this_names);
                    $rs = $this->get_form($form_data[$this->table_name], 'edit');
                } else {

                    if ($this->mod_name !== 'topic' && isset($form_data[$this->table_name]['user_id'])) {
                        $form_data[$this->table_name]['user_id']['value'] = $user_id;
                    }
                    $this->edit_data($form_data[$this->table_name]);
                    if ($this->getError()) {
                        $form_data[$this->table_name] = $this->removeTemporaryFields($form_data['data'], $remove_this_names);
                        $rs = $this->get_form($form_data[$this->table_name], 'edit');
                    } else {
                        $rs .= $this->grid();
                    }
                }
                return $rs;
            } else {
                return Multilanguage::_('L_ACCESS_DENIED');
            }
        } else {
            return parent::_edit_doneAction();
        }
    }

    protected function _newAction()
    {
        if ((1 === (int)$this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name'] !== 'admin') && $this->mod_name === 'topic') {
            return Multilanguage::_('L_ACCESS_DENIED');
        }
        if ((1 === (int)$this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name'] !== 'admin') && (1 !== (int)$this->getConfigValue('apps.news.share_access'))) {
            return Multilanguage::_('L_ACCESS_DENIED');
        }

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $form_data = $this->data_model;
        if ($form_data[$this->table_name]['date']['type'] === 'date') {
            $form_data[$this->table_name]['date']['value'] = time();
        }

        $rs = $this->get_form($form_data[$this->table_name]);
        return $rs;
    }

    protected function _new_doneAction()
    {
        if ((1 === (int)$this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name'] !== 'admin') && $this->mod_name === 'topic') {
            return Multilanguage::_('L_ACCESS_DENIED');
        }

        if ((1 === (int)$this->getConfigValue('check_permissions')) && (1 === (int)$this->getConfigValue('apps.news.share_access'))) {

            $rs = '';

            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
            $data_model = new Data_Model();
            $form_data = $this->data_model;


            $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
            $new_values = $this->getRequestValue('_new_value');
            if (1 == $this->getConfigValue('use_combobox') && count($new_values) > 0) {
                $remove_this_names = array();
                foreach ($form_data[$this->table_name] as $fd) {
                    if (isset($new_values[$fd['name']]) && $new_values[$fd['name']] != '' && $fd['combo'] == 1) {
                        $id = md5(time() . '_' . random_int(100, 999));
                        $remove_this_names[] = $id;
                        $form_data[$this->table_name][$id]['value'] = $new_values[$fd['name']];
                        $form_data[$this->table_name][$id]['type'] = 'auto_add_value';
                        $form_data[$this->table_name][$id]['dbtype'] = 'notable';
                        $form_data[$this->table_name][$id]['value_table'] = $form_data[$this->table_name][$fd['name']]['primary_key_table'];
                        $form_data[$this->table_name][$id]['value_primary_key'] = $form_data[$this->table_name][$fd['name']]['primary_key_name'];
                        $form_data[$this->table_name][$id]['value_field'] = $form_data[$this->table_name][$fd['name']]['value_name'];
                        $form_data[$this->table_name][$id]['assign_to'] = $fd['name'];
                        $form_data[$this->table_name][$id]['required'] = 'off';
                        $form_data[$this->table_name][$id]['unique'] = 'off';
                    }
                }
            }
            $data_model->forse_auto_add_values($form_data[$this->table_name]);
            $form_data[$this->table_name] = $this->_before_check_action($form_data[$this->table_name]);
            if (!$this->check_data($form_data[$this->table_name]) || (1 == $this->getConfigValue('filter_double_data') && !$this->checkUniquety($form_data[$this->table_name]))) {
                $form_data[$this->table_name] = $this->removeTemporaryFields($form_data[$this->table_name], $remove_this_names);
                $rs = $this->get_form($form_data[$this->table_name], 'new');
            } else {
                if ($this->mod_name !== 'topic' && isset($form_data[$this->table_name]['user_id'])) {
                    $form_data[$this->table_name]['user_id']['value'] = (int)$_SESSION['user_id'];
                }

                $this->add_data($form_data[$this->table_name], $this->getRequestValue('language_id'));
                if ($this->getError()) {
                    $form_data[$this->table_name] = $this->removeTemporaryFields($form_data['data'], $remove_this_names);
                    $rs = $this->get_form($form_data[$this->table_name], 'new');
                } else {
                    $rs .= $this->grid();
                }
            }
            return $rs;
        }

        return parent::_new_doneAction();
    }

    protected function initNewsModel()
    {
        $this->action = 'news';
        $this->table_name = 'news';
        $this->primary_key = 'news_id';

        require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
        $ATH = new Admin_Table_Helper();

        $form_data = $ATH->get_model($this->table_name, false, false, false, function(){
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/news/admin/news_model.php');
            $Object = new News_Model();
            return $Object->get_model();
        });

        $this->data_model = $form_data;
    }

    protected function initNewsTopicModel()
    {
        $this->action = 'news:topic';
        $this->table_name = 'news_topic';
        $this->primary_key = 'id';

        require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
        $ATH = new Admin_Table_Helper();

        $form_data = $ATH->get_model($this->table_name, false, false, false, function(){
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/news/admin/news_topic_model.php');
            $Object = new News_Topic_Model();
            return $Object->get_model();
        });

        $this->data_model = $form_data;
    }


    public function _before_check_action($form_data, $type = 'new')
    {
        $form_data = parent::_before_check_action($form_data, $type);
        if (isset($form_data['date'])) {
            if ($form_data['date']['type'] === 'date') {
                if ($form_data['date']['value'] != '' && $form_data['date']['value'] != '0') {
                    $time = date('H:i:s', $form_data['date']['value']);
                    if ($time === '00:00:00') {
                        $form_data['date']['value'] = strtotime(date('d-m-Y', $form_data['date']['value']) . ' ' . date('H:i:s', time()));
                    }
                } else {
                    $form_data['date']['value'] = time();
                }
            } elseif ($form_data['date']['type'] === 'dtdatetime') {

            }
        }

        if ($this->mod_name === 'topic') {

            if ($form_data['url']['value'] == '') {
                $form_data['url']['value'] = $this->transliteMe($form_data['name']['value']);
            }
            $form_data['url']['value'] = preg_replace('/[^a-zA-Z0-9-_]/', '', $form_data['url']['value']);
        } else {
            if (isset($form_data['newsalias']['value']) && $form_data['newsalias']['value'] != '') {
                $form_data['newsalias']['value'] = preg_replace('/[^a-zA-Z0-9-_]/', '', $form_data['newsalias']['value']);
            } elseif (isset($form_data['newsalias']) && $form_data['newsalias']['value'] == '') {
                $f = trim($this->getConfigValue('apps.news.alias_source_field'));
                if ($f == '') {
                    $f = 'title';
                }
                if (isset($form_data[$f]) && $form_data[$f]['value'] != '') {
                    $form_data['newsalias']['value'] = $this->get_transliteration($form_data[$f]['value']);
                }
            }
        }
        return $form_data;
    }

    function install()
    {
        $success_result = true;
        $DBC = DBC::getInstance();

        $query = "
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "_news` (
			  `news_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `title` varchar(255) NOT NULL DEFAULT '',
			  `description` mediumtext,
			  `date` int(11) NOT NULL DEFAULT '0',
			  `anons` mediumtext,
			  `meta_title` varchar(255) NOT NULL,
			  `meta_keywords` text NOT NULL,
			  `meta_description` text NOT NULL,
			  `newsalias` varchar(255) NOT NULL,
			  PRIMARY KEY (`news_id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=" . DB_ENCODING . ";";
        $success = false;
        $DBC->query($query, array(), $rows, $success);
        $success_result = $success_result && $success;
        if ($this->use_topics) {
            $query = "
				CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "_news_topic` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `name` varchar(255) NOT NULL,
				  `url` varchar(255) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=" . DB_ENCODING . " ;
        ";
            $success = false;
            $DBC->query($query, array(), $rows, $success);
            $success_result = $success_result && $success;
        }
        if (!$success_result) {
            $rs = Multilanguage::_('L_APPLICATION_INSTALLED_ERROR');
        } else {
            $rs = Multilanguage::_('L_APPLICATION_INSTALLED');
        }
        return $rs;

    }

    function getTopMenu()
    {
        $rs = '';
        $rs .= '<a href="?action=news" class="btn btn-primary">Все новости</a>';
        $rs .= ' <a href="?action=news&do=new" class="btn btn-primary">Добавить новость</a>';

        if ($this->use_topics) {
            if (((1 === (int)$this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name'] === 'admin')) || (0 === (int)$this->getConfigValue('check_permissions'))) {
                $rs .= ' <a href="?action=news:topic" class="btn btn-primary">Структура новостей</a>';
                $rs .= ' <a href="?action=news:topic&do=new" class="btn btn-primary">Добавить раздел</a>';
            }
        }
        $rs .= $this->get_extended_items();

        return $rs;
    }

    /**
     * Grid
     * @param void
     * @return string
     */
    function grid($params = array(), $default_params = array())
    {

        $rs = '';

        if ((1 === (int)$this->getConfigValue('check_permissions')) && $this->mod_name === 'topic' && ($_SESSION['current_user_group_name'] !== 'admin')) {
            return '';
        } elseif ((1 === (int)$this->getConfigValue('check_permissions')) && (1 === (int)$this->getConfigValue('apps.news.share_access')) && ($_SESSION['current_user_group_name'] !== 'admin')) {
            $params['grid_conditions'] = array('user_id' => $_SESSION['user_id_value']);
        }

        $rs .= parent::grid($params, $default_params);
        return $rs;
    }

    function get_default_grid_items () {
        if($this->mod_name === 'topic'){
            return [$this->primary_key, 'name'];
        }else{
            return [$this->primary_key, 'title'];
        }
    }

    function _preload()
    {
        global $smarty;
        if ($this->getConfigValue('apps.news.enable') && 1 == (int)$this->getConfigValue('apps.news.preload_column')) {
            $smarty->assign('apps_news_appsnewsalias', $this->getConfigValue('apps.news.alias'));
            $smarty->assign('news_list_column_html', $this->getNewsListBlock());
        } else {
            $smarty->assign('news_list_column_html', '');
        }

        if ($this->getConfigValue('apps.news.enable') && 1 == (int)$this->getConfigValue('apps.news.preload_column_by_location')) {
            // Загрузка списка новостей для страны или города
            $this->loadListByLocation();
        }

        return true;
    }

    function loadListByLocation() {
        $params = $this->getCityOrCountryFromUrl(Sitebill::getClearRequestURI());

        $news = $this->getNewsList($params);

        if ( is_array($news) and count($news) > 0  ) {
            $this->template->assign('news_list_column', $news);
        } elseif ( ($params['city_id'] > 0 or $params['country_id'] > 0) and  is_array($news) and count($news) == 0  ) {
            $params = [
                'city_id' => 0,
                'country_id' => 0,
            ];
            $news = $this->getNewsList($params);
            $this->template->assign('news_list_column', $news);
        }
    }

    function getCityOrCountryFromUrl( $REQUESTURIPATH )
    {
        $any_url_catched = false;
        $DBC = DBC::getInstance();
        $params['city_id'] = 0;
        $params['country_id'] = 0;

        if (!$any_url_catched && $REQUESTURIPATH != '' && str_contains($REQUESTURIPATH, '/')) {
            $country_and_city = explode('/', $REQUESTURIPATH);
            if ( is_array($country_and_city) && count($country_and_city) == 2 ) {
                $query = 'SELECT c.*, co.country_id as country_id FROM ' . DB_PREFIX . '_city c, ' . DB_PREFIX . '_country co  WHERE co.url=? and c.url=? LIMIT 1';
                $stmt = $DBC->query($query, array($country_and_city[0], $country_and_city[1]));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    if ((int)$ar['city_id'] != 0) {
                        $city_url_catched = true;
                        $params['city_id'] = $ar['city_id'];
                        $any_url_catched = true;
                    }
                    if ((int)$ar['country_id'] != 0) {
                        $params['country_id'] = $ar['country_id'];
                    }

                }
            }

        }


        if (!$any_url_catched && $REQUESTURIPATH != '') {
            if (intval($this->getConfigValue('apps.seo.no_city_url')) === 0) {
                $query = 'SELECT * FROM ' . DB_PREFIX . '_city WHERE url=? LIMIT 1';
                $stmt = $DBC->query($query, array($REQUESTURIPATH));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    if ((int)$ar['city_id'] != 0) {
                        $city_url_catched = true;
                        $params['city_id'] = $ar['city_id'];
                        $any_url_catched = true;
                    }
                }
            }
        }


        if (!$any_url_catched && $REQUESTURIPATH != '') {
            if (intval($this->getConfigValue('apps.seo.no_country_url')) === 0) {
                $query = 'SELECT * FROM ' . DB_PREFIX . '_country WHERE url=? LIMIT 1';
                $stmt = $DBC->query($query, array($REQUESTURIPATH));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);

                    if ((int)$ar['country_id'] != 0) {
                        $country_url_catched = true;
                        $params['country_id'] = $ar['country_id'];
                        $any_url_catched = true;
                    }
                }
            }
        }


        return $params;
    }

    private function get_transliteration($word)
    {
        return $this->transliteMe($word);
    }

    function getNewsListBlock()
    {
        $news = $this->getNewsList();
        $this->template->assign('news_list_column', $news);
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/news_list_column.tpl')) {
            return $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/news_list_column.tpl');
        }

        return $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/news/site/template/news_list_column.tpl');
    }

    function getNewsList( $params = array() )
    {
        $where = array();
        $news = array();
        if (isset($this->data_model[$this->table_name]['spec'])) {
            $control_spec = true;
            $where[] = 'n.`spec`=1';
        } else {
            $control_spec = false;
        }

        if ('' != $this->getConfigValue('apps.news.item_alias')) {
            $app_item_alias = $this->getConfigValue('apps.news.item_alias');
        } else {
            $app_item_alias = 'news';
        }

        $count = $this->getConfigValue('apps.news.news_line.per_page');
        if ($count == 0) {
            $count = 4;
        }

        $hasUploadify = false;
        $uploads = false;
        foreach ($this->data_model[$this->table_name] as $mitem) {
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

        if (isset($_SESSION['user_domain_owner']) && (int)$_SESSION['user_domain_owner']['user_id'] != 0) {
            $where[] = 'n.`user_id`=' . $_SESSION['user_domain_owner']['user_id'];
        }

        if ($this->data_model[$this->table_name]['date']['type'] === 'dtdatetime') {
            $where[] = 'n.`date`<=\'' . date('Y-m-d H:i:s', time()) . '\'';
        } else {
            $where[] = 'n.`date`<=' . time();
        }

        if ( isset($this->data_model[$this->table_name]['country_id']) and isset($params['country_id']) and $params['country_id'] > 0 ) {
            $where[] = 'n.`country_id` = ' . intval($params['country_id']);
        }
        if ( isset($this->data_model[$this->table_name]['city_id']) and isset($params['city_id']) and $params['city_id'] > 0) {
            $where[] = 'n.`city_id` = ' . intval($params['city_id']);
        }
        if ( isset($params['country_id']) and $params['country_id'] == 0 and isset($params['city_id']) and $params['city_id'] == 0 ) {
            $where[] = 'n.`city_id` = ' . intval($params['city_id']);
            $where[] = 'n.`country_id` = ' . intval($params['country_id']);
        }


        if (1 == $this->getConfigValue('apps.news.use_news_topics')) {
            $query = 'SELECT n.*, nt.name AS news_topic_id FROM ' . DB_PREFIX . '_' . $this->table_name . ' n LEFT JOIN ' . DB_PREFIX . '_news_topic nt ON nt.id=n.news_topic_id' . (!empty($where) ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY n.`date` DESC LIMIT ' . $count;
        } else {
            $query = 'SELECT n.* FROM ' . DB_PREFIX . '_' . $this->table_name . ' n' . (!empty($where) ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY n.`date` DESC LIMIT ' . $count;
        }


        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if ($stmt) {
            $i = 0;
            while ($ar = $DBC->fetch($stmt)) {
                if ($this->data_model[$this->table_name]['date']['type'] === 'dtdatetime') {
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

            foreach ($news as $k => $n) {
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
                $data_model = new Data_Model();
                if ($hasUploadify) {
                    $image_array = $data_model->get_image_array('news', 'news', 'news_id', $n['news_id']);
                    if (count($image_array) > 0) {
                        $news[$k]['img_preview'] = $image_array[0]['img_preview'];
                    }
                } elseif ($uploads !== false) {
                    $ims = $news[$k][$uploads];
                    if ($ims != '') {
                        $ims = unserialize($ims);
                        $ims = $data_model->sharder_mirror($ims, true);
                    } else {
                        $ims = array();
                    }
                    if (isset($ims[0])) {
                        $news[$k]['img_preview'] = $this->createMediaIncPath($ims[0], 'preview');
                    }
                }
            }
        }
        return $news;
    }

    public function detectNewsTopic($url)
    {
        $DBC = DBC::getInstance();
        $query = 'SELECT id, name, url FROM ' . DB_PREFIX . '_news_topic WHERE url=? LIMIT 1';
        $stmt = $DBC->query($query, array($url));
        if ($stmt) {
            return $DBC->fetch($stmt);
        }
        return false;
    }

    public function detectNews($url)
    {
        $DBC = DBC::getInstance();
        $query = 'SELECT news_id FROM ' . DB_PREFIX . '_news WHERE newsalias=? AND `date`<=? LIMIT 1';
        if ($this->data_model[$this->table_name]['date']['type'] === 'dtdatetime') {
            $date = date('Y-m-d H:i:s', time());
        } else {
            $date = time();
        }
        $stmt = $DBC->query($query, array($url, $date));
        if ($stmt) {
            return $DBC->fetch($stmt);
        }
        return false;
    }

    public function getNewsTopicsList()
    {
        if ('' != $this->getConfigValue('apps.news.alias')) {
            $app_alias = $this->getConfigValue('apps.news.alias');
        } else {
            $app_alias = 'news';
        }
        $ret = array();
        $fname = 'name';
        if (1 === intval($this->getConfigValue('apps.language.use_langs'))) {

            $postfix = $this->getLangPostfix($this->getCurrentLang());

            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
            $ATH = new Admin_Table_Helper();
            $form_data = $ATH->load_model('news_topic', false);
            if (isset($form_data['news_topic'][$fname . $postfix])) {
                $fname = $fname . $postfix;
            }
        }
        $DBC = DBC::getInstance();
        $query = 'SELECT `id`, `' . $fname . '` AS `name`, `url` FROM ' . DB_PREFIX . '_news_topic ORDER BY `' . $fname . '`';
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ar['url'] = $this->createUrlTpl($app_alias . '/' . $ar['url']);
                $ar['sitemap_url'] = $this->createUrlTpl($app_alias . '/' . $ar['url'], true);
                $ret[$ar['id']] = $ar;
            }
        }
        return $ret;
    }

    /*
     * $mode = 0|1|2
     * 0 - standart internal link
     * 1 - external link with protocol
     * 2 - trimmd internal link without SITEBILL_MAIN_URL
     */

    public function getNewsRoute($news_id, $news_alias = '', $external = false)
    {
        if ('' != $this->getConfigValue('apps.news.alias')) {
            $app_news_alias = $this->getConfigValue('apps.news.alias');
        } else {
            $app_news_alias = 'news';
        }
        if ('' != $this->getConfigValue('apps.news.item_alias')) {
            $app_item_alias = $this->getConfigValue('apps.news.item_alias');
        } else {
            $app_item_alias = 'news';
        }

        if ($external) {
            if ($news_alias != '') {
                return $this->createUrlTpl($app_news_alias . '/' . $news_alias, true);
            }

            return $this->createUrlTpl($app_item_alias . $news_id . '.html', true);
        } else {
            if ($news_alias != '') {
                return $this->createUrlTpl($app_news_alias . '/' . $news_alias);
            }

            return $this->createUrlTpl($app_item_alias . $news_id . '.html');
        }
    }

}
