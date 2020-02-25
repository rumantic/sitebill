<?php

require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/grid/grid_constructor.php';

/**
 * Grid constructor
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class News_Grid_Constructor extends Grid_Constructor {

    /**
     * Constructor
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Main
     * @param array $param
     * @return array
     */
    function main($params) {
        
    }

    /**
     * Get sitebill adv ext
     * @param array $params
     * @param boolean $random
     * @return array
     */
    function get_sitebill_adv_ext($params, $random = false, $premium = false) {
        $DBC = DBC::getInstance();
        $paging_alias = SiteBill::getClearRequestURI();

        $form_data = array();

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
            $ATH = new Admin_Table_Helper();
            $form_data = $ATH->load_model('news', false);
            if (empty($form_data)) {
                $form_data = array();
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/news/admin/news_model.php');
                $Object = new News_Model();
                $form_data = $Object->get_model();
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php';
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php';
                $TA = new table_admin();
                $TA->create_table_and_columns($form_data, 'news');
                $form_data = array();
                $form_data = $ATH->load_model('news', false);
            }
        } else {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/news/admin/news_model.php');
            $Object = new News_Model();
            $form_data = $Object->get_model();
        }

        $model = $form_data['news'];

        if (isset($model['date']) && $model['date']['type'] == 'dtdatetime') {
            $use_datetime = true;
        } else {
            $use_datetime = false;
        }

        $where = array();
        $where_statement = '';

        $order = DB_PREFIX . "_news.date DESC, news_id DESC";
        if (!isset($params['page']) or $params['page'] == 0) {
            $page = 1;
        } else {
            $page = $params['page'];
        }

        if (!isset($params['per_page']) OR $params['per_page'] == 0) {
            $limit = $this->getConfigValue('apps.news.front.per_page');
        } else {
            $limit = $params['per_page'];
        }

        if (isset($params['news_topic_id']) && $params['news_topic_id'] != 0) {
            $where[] = 'news_topic_id=' . (int) $params['news_topic_id'];
        }

        if ($use_datetime) {
            $where[] = '`date`<=\'' . date('Y-m-d H:i:s', time()) . '\'';
        } else {
            $where[] = '`date`<=' . time();
        }


        if (isset($_SESSION['user_domain_owner']) && (int) $_SESSION['user_domain_owner']['user_id'] != 0) {
            $where[] = 'user_id=' . $_SESSION['user_domain_owner']['user_id'];
        }

        if (!empty($where)) {
            $where_statement = 'WHERE ' . implode(' AND ', $where);
        }

        if ($random) {
            $order = ' RAND() ';
        }


        $query = "SELECT COUNT(" . DB_PREFIX . "_news.news_id) AS total FROM " . DB_PREFIX . "_news $add_from_table $where_statement ORDER BY $order";
        $stmt = $DBC->query($query);
        $ar = $DBC->fetch($stmt);
        $total = $ar['total'];

        if ($total != 0) {
            $max_page = ceil($total / $limit);

            if ($page > $max_page) {
                $page = 1;
                $params['page'] = 1;
            }
        }

        $start = ($page - 1) * $limit;


        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/page_navigator.php';
        $Pager = new Page_Navigator($total, $page, $limit, '', array('pre_pages' => '3', 'post_pages' => '3'));
        $this->template->assert('pager', $Pager->getPagerArray());
        $paging = Page_Navigator::getPagingArray($total, $page, $limit, array(), $paging_alias);
        $this->template->assert('news_paging', $paging);
        
        $this->template->assert('news_pager', $this->get_page_links_list ($page, $total, $limit, array('page_url' => $paging_alias)));






        $query = "SELECT * FROM " . DB_PREFIX . "_news " . $add_from_table . " " . $where_statement . " ORDER BY " . $order . " LIMIT " . $start . ", " . $limit;
        //echo $query;
        $stmt = $DBC->query($query);
        $ra = array();
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ra[] = $ar;
            }
        }


        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $params = array();

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
        $_ids = array();
        foreach ($ra as $item_id => $item_array) {
            $_ids[] = $item_array['news_id'];

            if (1 === intval($this->getConfigValue('apps.language.use_langs')) && (!isset($parameters['no_ml']) || 0 === intval($parameters['no_ml']))) {
                $curlang = $this->getCurrentLang();

                foreach ($ra[$item_id] as $key => $item_array) {
                    $lang_key = $key . '_' . $curlang;
                    if (isset($ra[$item_id][$lang_key]) && $ra[$item_id][$lang_key] != '') {
                        $ra[$item_id][$key] = $ra[$item_id][$lang_key];
                    }
                }
            }
            $ra[$item_id]['_date']=$ra[$item_id]['date'];
            if ($use_datetime) {
                $ra[$item_id]['date'] = date('d.m.Y', strtotime($ra[$item_id]['date']));
            } else {
                $ra[$item_id]['date'] = date('d.m.Y', $ra[$item_id]['date']);
            }

            $ra[$item_id]['href'] = $this->getNewsRoute($ra[$item_id]['news_id'], $ra[$item_id]['newsalias']);
            $ra[$item_id]['_news_topic_id'] = $ra[$item_id]['news_topic_id'];
            $ra[$item_id]['news_topic_id'] = $data_model->get_string_value_by_id('news_topic', 'id', 'name', $ra[$item_id]['news_topic_id']);
        }





        $hasUploadify = false;
        $uploads = false;
        foreach ($model as $mitem) {
            if ($mitem['type'] == 'uploadify_image') {
                $hasUploadify = true;
                continue;
            }
        }
        if (!$hasUploadify) {
            foreach ($model as $mitem) {
                if ($mitem['type'] == 'uploads') {
                    $uploads = $mitem['name'];
                    continue;
                }
            }
        }

        if ($hasUploadify) {
            $key = 'news_id';
            $query = 'SELECT li.' . $key . ' , i.* FROM ' . DB_PREFIX . '_news_image li LEFT JOIN ' . IMAGE_TABLE . ' i USING(image_id) WHERE li.' . $key . ' IN (' . implode(', ', $_ids) . ') ORDER BY li.sort_order ASC';
            $DBC = DBC::getInstance();
            $stmt = $DBC->query($query);
            $images = array();
            if ($stmt) {
                $iurl = $this->storage_dir;
                while ($ar = $DBC->fetch($stmt)) {
                    $ar['img_preview'] = $iurl . $ar['preview'];
                    $ar['img_normal'] = $iurl . $ar['normal'];
                    $images[$ar[$key]][] = $ar;
                }
            }
            foreach ($ra as $k => $item) {
                if (isset($images[$item['news_id']])) {
                    $ra[$k]['prev_img'] = $images[$item['news_id']][0]['img_preview'];
                    $ra[$k]['img'] = $images[$item['news_id']];
                }
            }
        } elseif ($uploads !== false) {
            foreach ($ra as $k => $item) {
                if ($item[$uploads] != '') {
                    $ims = unserialize($item[$uploads]);
                } else {
                    $ims = array();
                }
                if (isset($ims[0])) {
                    $ra[$k]['prev_img'] = SITEBILL_MAIN_URL . '/img/data/' . $ims[0]['preview'];
                    $ra[$k]['norm_img'] = SITEBILL_MAIN_URL . '/img/data/' . $ims[0]['normal'];
                }
            }
        }


        return $ra;
    }

    protected function getNewsRoute($news_id, $news_alias = '') {
        if ('' != $this->getConfigValue('apps.news.alias')) {
            $app_news_alias = $this->getConfigValue('apps.news.alias');
        } else {
            $app_news_alias = 'news';
        }
        if (1 == (int) $this->getConfigValue('apps.seo.no_trailing_slashes')) {
            $trailing_slashe = '';
        } else {
            $trailing_slashe = '/';
        }
        if ('' != $this->getConfigValue('apps.news.item_alias')) {
            $app_item_alias = $this->getConfigValue('apps.news.item_alias');
        } else {
            $app_item_alias = 'news';
        }
        if ($news_alias != '') {
            return $this->createUrlTpl($app_news_alias . '/' . $news_alias);
        } else {
            return $this->createUrlTpl($app_item_alias . $news_id . '.html');
        }
    }

}