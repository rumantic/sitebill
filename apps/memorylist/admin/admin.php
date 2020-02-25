<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Memory list admin
 * Класс для обработки сохраненных списков пользователей (подборки на просмотр квартир, списки сравнения, избранные варианты)
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class memorylist_admin extends Object_Manager {

    /**
     * Constructor
     */
    function __construct($realty_type = false) {
        $this->SiteBill();
        Multilanguage::appendAppDictionary('memorylist');
        $this->table_name = 'data';
        $this->primary_key = 'id';
        $this->this_user = (isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0);
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');

        $data_model = new Data_Model();
        $this->data_model = $data_model->get_kvartira_model($this->getConfigValue('ajax_form_in_admin'));

        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/config/admin/admin.php');
        $config_admin = new config_admin();
        $config_admin->addParamToConfig('apps.memorylist.public_access_enable', '0', 'Все подборки общие', 1);


    }

    function ajax() {
        if ('search' == $this->getRequestValue('action')) {
            //$grid_constructor=$this->_grid_constructor;
            $grid_constructor = $this->_getGridConstructor();
            $USER_ID = $this->this_user;

            $params = $this->getRequestValue('params');
            $params['user_id'] = $USER_ID;
            $params['admin'] = 1;
            $params['_collect_user_info'] = 1;
            $res = $grid_constructor->get_sitebill_adv_ext_base_ajax($params);

            global $smarty;

            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/userdata/site/template/account_grid.tpl')) {
                $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/userdata/site/template/account_grid.tpl';
            } else {
                $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/userdata/site/template/account_grid.tpl';
            }
            $smarty->assign('grid_items', $res['data']);
            $smarty->assign('my_own', 1);
            $grid = $smarty->fetch($tpl);

            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/userdata/site/template/account_pager.tpl')) {
                $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/userdata/site/template/account_pager.tpl';
            } else {
                $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/userdata/site/template/account_pager.tpl';
            }

            $smarty->assign('pager_array', $res['paging']);
            $pager = $smarty->fetch($tpl);

            return json_encode(array('grid' => $grid, 'pager' => $pager, '_total_records' => $res['_total_records'], 'order' => $res['order']));
        } elseif ('base' == $this->getRequestValue('action')) {
            global $smarty;
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/userdata/admin/memory_list.php';
            $ML = new Memory_List();
            $memory_lists = $ML->getUserMemoryLists($this->this_user);
            foreach ($memory_lists as $ml) {
                if (isset($ml['items']) && count($ml['items']) > 0) {
                    foreach ($ml['items'] as $item) {
                        $items_in_memory[$item['id']][] = $ml;
                    }
                }
            }

            $use_watchlists = (1 == (int) $this->getConfigValue('apps.userdata.watchlist_enable') ? true : false);
            $params = $this->getRequestValue('params');
            $USER_ID = $this->this_user;
            $params['_collect_user_info'] = 1;


            if ($params['_owner'] == 'my') {
                $params['user_id'] = $USER_ID;
                $params['admin'] = 1;
                $smarty->assign('my_own', 1);
            } else {
                $params['active'] = 1;
            }
            unset($params['_owner']);

            if (isset($params['topic_id'])) {
                $params['topic_id'] = (array) $params['topic_id'];
            }


            if ($use_watchlists) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/userdata/admin/user_watchlistmanager.php';
                $WLM = new User_Watchlistmanager();
                $opened_categories = $WLM->getUserOpenedCategories($_SESSION['user_id']);
                //print_r($opened_categories);

                if (empty($opened_categories)) {
                    $params['topic_id'] = -1;
                    if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/userdata/site/template/you_have_no_access.tpl')) {
                        $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/userdata/site/template/you_have_no_access.tpl';
                    } else {
                        $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/userdata/site/template/you_have_no_access.tpl';
                    }
                    $grid = $smarty->fetch($tpl);
                    return json_encode(array('grid' => $grid, 'pager' => '', '_total_records' => '', 'order' => ''));
                } else {
                    if (isset($params['topic_id'])) {
                        $topics = $params['topic_id'];
                        if (is_array($topics)) {
                            $avial_topics = array();
                            foreach ($topics as $k => $t) {
                                if ((int) $t != 0 && in_array((int) $t, $opened_categories)) {
                                    $avial_topics[] = $t;
                                }
                            }
                            if (!empty($avial_topics)) {
                                $params['topic_id'] = $avial_topics;
                            } else {
                                $params['topic_id'] = -1;
                            }
                        } else {
                            if ((int) $topics != 0 && !in_array((int) $topics, $opened_categories)) {
                                $params['topic_id'] = -1;
                            }
                        }
                    } else {
                        $params['topic_id'] = $opened_categories;
                    }
                }
            }

            //$grid_constructor=$this->_grid_constructor;
            $grid_constructor = $this->_getGridConstructor();


            $res = $grid_constructor->get_sitebill_adv_ext_base_ajax($params);



            $smarty->assign('items_in_memory', $items_in_memory);

            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/userdata/site/template/account_grid.tpl')) {
                $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/userdata/site/template/account_grid.tpl';
            } else {
                $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/userdata/site/template/account_grid.tpl';
            }
            $smarty->assign('grid_items', $res['data']);
            $grid = $smarty->fetch($tpl);

            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/userdata/site/template/account_pager.tpl')) {
                $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/userdata/site/template/account_pager.tpl';
            } else {
                $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/userdata/site/template/account_pager.tpl';
            }
            $smarty->assign('pager_array', $res['paging']);
            //print_r($res['paging']);
            $pager = $smarty->fetch($tpl);

            return json_encode(array('grid' => $grid, 'pager' => $pager, '_total_records' => $res['_total_records'], 'order' => $res['order']));
        } elseif ('duplicate' == $this->getRequestValue('action')) {
            $id_array = array();
            $id_array = $this->getRequestValue('ids');
            $this->duplicate($this->table_name, $this->primary_key, $id_array);
        } elseif ('mass_delete' == $this->getRequestValue('action')) {
            $id_array = array();
            $id_array = $this->getRequestValue('ids');

            $this->mass_delete_data($this->table_name, $this->primary_key, $id_array);
        } elseif ('memorylist' == $this->getRequestValue('action')) {
            $do = $this->getRequestValue('doaction');

            if ($do == 'remove') {
                $id = $this->getRequestValue('itemid');
                $listid = $this->getRequestValue('listid');
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/memorylist/admin/memory_list.php';
                $ML = new Memory_List();
                $result = $ML->deleteItems($listid, array($id));
            } elseif ($do == 'add') {
                $ret = array();
                $id = $this->getRequestValue('itemid');
                $listid = $this->getRequestValue('listid');
                $listtitle = $this->getRequestValue('listtitle');

                require_once SITEBILL_DOCUMENT_ROOT . '/apps/memorylist/admin/memory_list.php';
                $ML = new Memory_List();

                if ($listtitle != '' && $_listid = $ML->createMemoryList($listtitle)) {
                    $listid = $_listid;
                }

                $ML->appendItems($listid, array($id));

                $result = $ML->getUserMemoryLists($this->this_user);
                foreach ($result as $ml) {
                    if ($ml['memorylist_id'] == $listid) {
                        $ret = $ml;
                        break;
                    }
                }
                return json_encode($ret);
            } elseif ($do == 'getlists') {
                $ret = array();
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/memorylist/admin/memory_list.php';
                $ML = new Memory_List();
                $result = $ML->getUserMemoryLists($this->this_user);
                foreach ($result as $ml) {
                    $ret[] = array('id' => $ml['memorylist_id'], 'title' => $ml['title']);
                }
                return json_encode($ret);
            }
        } elseif ('mywatchlist' == $this->getRequestValue('action')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/userdata/admin/user_watchlistmanager.php';
            $WLM = new User_Watchlistmanager();
            $result = $WLM->grid();
            return json_encode(array('list' => $result));

            $USER_ID = $this->this_user;
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
            $Structure_Manager = new Structure_Manager();
            $category_structure = $Structure_Manager->loadCategoryStructure();

            $wls = array();
            $cats = array();

            $DBC = DBC::getInstance();
            $query = 'SELECT * FROM ' . DB_PREFIX . '_watchlist_1 WHERE user_id=?';
            $stmt = $DBC->query($query, array($USER_ID));
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    if ($ar['category_id'] != '') {
                        $cats = explode(',', $ar['category_id']);
                        foreach ($cats as $cat) {
                            $ar['category_names'][] = $category_structure['catalog'][$cat]['name'];
                        }
                    }
                    $wls[] = $ar;
                }
            }

            if (!empty($wls)) {
                $now = time();
                foreach ($wls as $k => $v) {
                    if ($v['watch_time_end'] == 0) {
                        $wls[$k]['watch_time_end_str'] = 'не активирован';
                    } elseif ($v['watch_time_end'] <= $now) {
                        $wls[$k]['watch_time_end_str'] = 'завершен';
                    } else {
                        $wls[$k]['watch_time_end_str'] = 'работает до ' . date('d-m-Y H:i', $v['watch_time_end']);
                        $wls[$k]['days_to_end'] = (int) (($v['watch_time_end'] - $now) / (3600 * 24));
                    }
                }
            }

            global $smarty;
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/userdata/site/template/watchlist_grid.tpl')) {
                $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/userdata/site/template/watchlist_grid.tpl';
            } else {
                $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/userdata/site/template/watchlist_grid.tpl';
            }
            if (1 == $this->getConfigValue('apps.userdata.use_categories')) {
                $smarty->assign('wl_has_categories', 1);
            } else {
                $smarty->assign('wl_has_categories', 0);
            }
            $smarty->assign('wl_list', $wls);
            $list = $smarty->fetch($tpl);
            return json_encode(array('list' => $list));
        } elseif ('price_diapasone' == $this->getRequestValue('action')) {
            $max_rent_price = 100000;
            $max_sale_price = 30000000;
            $prtype = $this->getRequestValue('prtype');
            $max_price = $max_sale_price;
            if ($prtype == 'rent') {
                $max_price = $max_rent_price;
            } elseif ($prtype == 'sale') {
                $max_price = $max_sale_price;
            }

            $diapasones = 20;
            $diapasone_range = ceil($max_price / $diapasones);
            $diapasone_range_count = (string) $diapasone_range;
            $dlenth = $diapasone_range_count[0] * pow(10, strlen($diapasone_range) - 1) . '<br>';
            $real_diapasones = floor($max_price / $dlenth) + 1;
            $price_diapasones = '';
            for ($i = 1; $i <= $real_diapasones; $i++) {
                $price_diapasones .= '<option value="' . $dlenth * $i . '">' . number_format($dlenth * $i, 0, '.', ' ') . '</option>';
            }

            return $price_diapasones;
        }
    }

}
