<?php

/**
 * City manager
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
class City_Manager extends Object_Manager {

    /**
     * Constructor
     */
    function City_Manager() {
        $this->SiteBill();
        $this->table_name = 'city';
        $this->action = 'city';
        $this->app_title = Multilanguage::_('CITY_APP_NAME', 'system');
        $this->primary_key = 'city_id';

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $this->data_model = $data_model->get_city_model();
    }

    function grid2() {
        $default_params['grid_item'] = array('city_id', 'name', 'region_id');
        return parent::grid(array(), $default_params);
    }

    function getTopMenu() {
        $rs = '';
        $rs .= '<a href="?action=' . $this->action . '&do=new" class="btn btn-primary">' . Multilanguage::_('L_ADD_RECORD_BUTTON') . '</a> ';
        if (isset($_COOKIE['city_manager_mode']) && $_COOKIE['city_manager_mode'] == 'modern') {
            $rs .= '<a href="#" class="btn btn-primary setmode" data-type="standart">S</a> ';
        } else {
            $rs .= '<a href="#" class="btn btn-primary setmode" data-type="modern">M</a> ';
        }
        $rs .= '<script>$(document).ready(function(){$(".setmode").click(function(){$.cookie("city_manager_mode", $(this).data("type"));window.location.replace(window.location.href)});});</script>';
        return $rs;
    }

    /**
     * Delete data
     * @param string $table_name
     * @param string $primary_key
     * @param int $primary_key_value
     */
    function delete_data($table_name, $primary_key, $primary_key_value) {
        $search_queries = array(
            Multilanguage::_('TABLE_ADS', 'system') => 'SELECT COUNT(*) AS rs FROM ' . DB_PREFIX . '_data WHERE city_id=?',
            Multilanguage::_('TABLE_METRO', 'system') => 'SELECT COUNT(*) AS rs FROM ' . DB_PREFIX . '_metro WHERE city_id=?'
        );
        if ($this->getConfigValue('link_street_to_city')) {
            $search_queries[Multilanguage::_('TABLE_STREET', 'system')] = 'SELECT COUNT(*) AS rs FROM ' . DB_PREFIX . '_street WHERE city_id=?';
        }
        $ans = array();
        $DBC = DBC::getInstance();
        foreach ($search_queries as $k => $v) {
            $query = str_replace('?', $primary_key_value, $v);
            $stmt = $DBC->query($query);
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                $rs = $ar['rs'];
                if ($rs != 0) {
                    $ans[] = sprintf(Multilanguage::_('MESSAGE_CANT_DELETE', 'system'), $k);
                }
            }
        }
        if (empty($ans)) {
            return parent::delete_data($table_name, $primary_key, $primary_key_value);
        } else {
            return $this->riseError(implode('<br />', $ans));
        }
    }

    function ajax() {
        $ret = array();
        $where = array();
        $wherep = array();


        $page = intval($this->getRequestValue('page'));
        if ($page == 0) {
            $page = 1;
        }
        $per_page = intval($this->getRequestValue('per_page'));
        if ($per_page == 0) {
            $per_page = 10;
        }

        $DBC = DBC::getInstance();
        $used_fields = array();
        $query = 'SELECT `grid_fields` FROM ' . DB_PREFIX . '_table_grids WHERE `action_code`=?';
        $stmt = $DBC->query($query, array($this->action));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $used_fields = json_decode($ar['grid_fields']);
        } else {
            $used_fields[] = $this->primary_key;
            $used_fields[] = 'name';
        }
        if (!in_array($this->primary_key, $used_fields)) {
            $used_fields[] = $this->primary_key;
        }

        $asc = $this->getRequestValue('sort_asc');
        if ($asc == 'asc') {
            $asc = 'ASC';
        } else {
            $asc = 'DESC';
        }

        if ('' != $this->getRequestValue('sort') && in_array($this->getRequestValue('sort'), $used_fields)) {
            $sort = 'ORDER BY ' . $this->getRequestValue('sort') . ' ' . $asc;
        } else {
            $sort = 'ORDER BY ' . $this->primary_key . ' DESC';
        }

        $name = $this->getRequestValue('name');
        if ($name != '') {
            $compare = $this->getRequestValue('_compare');
            //echo $what;
            if ($compare != 'first' && $compare != 'any') {
                $compare = 'any';
            }

            $swhere = array();
            $t = explode(' ', $name);
            foreach ($t as $_t) {
                $swhere[] = '`name` LIKE ?';
                if ($compare == 'first') {
                    $wherep[] = '' . trim($_t) . '%';
                } else {
                    $wherep[] = '%' . trim($_t) . '%';
                }
            }

            $where[] = '(' . implode(' OR ', $swhere) . ')';
        }

        if (intval($this->getRequestValue('region_id')) > 0) {
            $where[] = '(region_id=?)';
            $wherep[] = intval($this->getRequestValue('region_id'));
        }

        $query = 'SELECT SQL_CALC_FOUND_ROWS ' . implode(',', $used_fields) . ' FROM ' . DB_PREFIX . '_' . $this->table_name . (!empty($where) ? ' WHERE ' . implode(' AND ', $where) : '') . ' ' . $sort . ' LIMIT ' . ($page - 1) * $per_page . ', ' . $per_page;

        $stmt = $DBC->query($query, $wherep);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ret[$ar[$this->primary_key]] = $ar;
            }
        }

        $query = 'SELECT FOUND_ROWS() AS _cnt';
        $stmt = $DBC->query($query);
        $ar = $DBC->fetch($stmt);
        $total = $ar['_cnt'];

        $pages = ceil($total / $per_page);

        $html = '';
        $pager = '';
        if (!empty($ret)) {
            global $smarty;
            $smarty->assign('list_items', $ret);
            $smarty->assign('used_fields', $used_fields);
            $smarty->assign('primary_key', $this->primary_key);
            $smarty->assign('action', $this->action);
            $html = $smarty->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/system/template/city_admin/grid_list.tpl');
            if ($pages > 1) {
                $pager = '<ul>';
                //if($)
                for ($i = 1; $i <= $pages; $i++) {
                    $pager .= '<li' . ($page == $i ? ' class="active"' : '') . '><a href="#" data-page="' . $i . '">' . $i . '</a></li>';
                }
                $pager .= '</ul>';
            } else {
                $pager = '';
            }
        }
        return json_encode(array('pager' => $pager, 'html' => $html));
    }

    function grid($params = array(), $default_params = array()) {
        if (isset($_COOKIE['city_manager_mode']) && $_COOKIE['city_manager_mode'] == 'modern') {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/grid.php');
            $common_grid = new Common_Grid($this);
            $common_grid->set_action($this->action);
            $common_grid->set_grid_table($this->table_name);

            $page = intval($this->getRequestValue('page'));
            if ($page == 0) {
                $page = 1;
            }
            $per_page = intval($this->getRequestValue('per_page'));
            if ($per_page == 0) {
                $per_page = 10;
            }

            $DBC = DBC::getInstance();
            $used_fields = array();
            $query = 'SELECT `grid_fields` FROM ' . DB_PREFIX . '_table_grids WHERE `action_code`=?';
            $stmt = $DBC->query($query, array($this->action));
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                $used_fields = json_decode($ar['grid_fields']);
            } else {
                $used_fields[] = $this->primary_key;
                $used_fields[] = 'name';
            }
            if (!in_array($this->primary_key, $used_fields)) {
                $used_fields[] = $this->primary_key;
            }
            $query = 'SELECT ' . implode(',', $used_fields) . ' FROM ' . DB_PREFIX . '_' . $this->table_name . ' LIMIT ' . ($page - 1) * $per_page . ', ' . $per_page;
            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $ret[$ar[$this->primary_key]] = $ar;
                }
            }

            $heading = array();
            foreach ($used_fields as $uf) {
                $heading[$uf] = $this->data_model[$this->table_name][$uf]['title'];
            }

            global $smarty;
            $smarty->assign('datarequest_url', SITEBILL_MAIN_URL . '/js/ajax.php');
            $smarty->assign('dataload_action', 'city_load_data');
            $smarty->assign('start_sort', $this->primary_key);
            $smarty->assign('list_items', $ret);
            $smarty->assign('list_heading', $heading);
            $html = $smarty->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/system/template/city_admin/grid.tpl');
            $html = $common_grid->extended_items() . $html;

            return $html;
        } else {
            $default_params['grid_item'] = array('city_id', 'name', 'region_id');
            return parent::grid(array(), $default_params);
            //return parent::grid($params);
        }

        print_r($ret);
        //echo $query;



        /* if(isset($params['grid_controls']) && count($params['grid_controls'])>0){
          foreach($params['grid_controls'] as $grid_item){
          $common_grid->add_grid_control($grid_item);
          }
          }else{
          $common_grid->add_grid_control('edit');
          $common_grid->add_grid_control('delete');
          } */

        if (isset($params['grid_conditions']) && count($params['grid_conditions']) > 0) {
            $common_grid->set_conditions($params['grid_conditions']);
        }
        $common_grid->set_grid_query('SELECT * FROM ' . DB_PREFIX . '_' . $this->table_name);


        //$common_grid->set_grid_query('SELECT * FROM '.DB_PREFIX.'_'.$this->table_name.' ORDER BY name ASC');


        $common_grid->setPagerParams(array('action' => $this->action, 'page' => $this->getRequestValue('page'), 'per_page' => $this->getConfigValue('common_per_page')));
        print_r($common_grid->construct_grid_array());
        $rs = $common_grid->extended_items();
        $rs .= $common_grid->construct_grid();
        return $rs;
    }

    public static function getAjaxPager($pages, $current_page) {
        $offset_left = 3;
        $offset_right = 3;
        $left_page = $current_page - $offset_left;
        $right_page = $current_page - $offset_right;
        if ($left_page < 0) {
            
        }

        $pager = '<ul>';
        //if($)
        for ($i = 1; $i <= $pages; $i++) {
            $pager .= '<li' . ($page == $i ? ' class="active"' : '') . '><a href="#" data-page="' . $i . '">' . $i . '</a></li>';
        }
        $pager .= '</ul>';
    }

}

?>