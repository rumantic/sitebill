<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/components/ajax_common.php');

/**
 * Process ajax request and return json arrays with tags values 
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class model_tags extends ajax_common {

    private $ai_mode = false;
    private $primary_key_mode = false;
    private $store_all_mode = false;
    private $store = array();

    function set_ai_mode($mode) {
        $this->ai_mode = $mode;
    }

    function set_store_all_mode ($mode) {
        $this->store_all_mode = $mode;
    }

    function get_store_all_mode () {
        return $this->store_all_mode;
    }

    function push_to_store ( $primary_key, $items ) {
        $this->store[$primary_key] = $items;
    }

    function get_store () {
        return $this->store;
    }


    function enable_primary_key_mode() {
        $this->primary_key_mode = true;
    }

    function disable_primary_key_mode() {
        $this->primary_key_mode = false;
    }

    function get_primary_key_mode() {
        return $this->primary_key_mode;
    }

    function get_ai_mode() {
        return $this->ai_mode;
        //return true;
    }

    function get_array($model_name = '', $column_name = '', $result = 'json', $input_form_data = false) {
        if ( $input_form_data ) {
            $form_data = $input_form_data;
        } else {
            $form_data = $this->get_model($model_name);
        }
        if ($column_name == '') {
            $column_name = $this->getRequestValue('column_name');
        }

        if ($form_data[$column_name]['type'] == 'select_by_query') {
            $tags_array = $this->get_array_by_query($form_data[$column_name]);
            //} elseif ( $form_data[$this->getRequestValue('column_name')]['type'] == 'checkbox' ) {
            //	$tags_array = array();
        } elseif ($form_data[$column_name]['type'] == 'client_id') {
            $tags_array = $this->get_array_by_client_id($form_data[$column_name]);
        } elseif ($form_data[$column_name]['type'] == 'select_box_structure') {
            $nc = $this->get_array_by_structure($form_data[$column_name]);
            if ($result == 'json') {
                foreach ($nc as $v) {
                    $tags_array[] = $v['breadcrumbs'];
                }
            } else {
                $tags_array = $nc;
            }
        } elseif ($form_data[$column_name]['type'] == 'uploads') {
            $tags_array = array(0, 1);
        } elseif ($form_data[$column_name]['type'] == 'select_box') {
            $tags_array = $this->get_array_by_select_box($form_data[$column_name], $form_data);
        } else {
            $tags_array = $this->get_distinct_values($form_data[$column_name]);
        }

        if ($result == 'json') {
            return json_encode($tags_array);
        } else {
            return $tags_array;
        }
    }

    function ajax() {
        $sapi_name = php_sapi_name();
        if ($sapi_name == 'cgi' || $sapi_name == 'cgi-fcgi') {
            header('Status: 200 OK');
        } else {
            header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
        }
        header('Content-Type: application/json');
        if ($this->getRequestValue('do') == 'set') {
            $this->init_session_tags();
            echo json_encode(array('status' => 'ok'));
        } elseif ($this->getRequestValue('do') == 'clear') {
            $this->clear_session_tags();
            echo json_encode(array('status' => 'ok'));
        } else {
            echo $this->get_array();
        }
        exit;
    }

    function clear_session_tags() {
        $model_name = $this->getRequestValue('model_name');
        if ($model_name != '') {
            $_SESSION['model_tags'][$model_name]['tags_array'] = array();
        } else {
            $_SESSION['tags_array'] = array();
        }
        return true;
    }

    function init_session_tags() {
        $tags_array_string = $this->getRequestValue('tags_array');
        $model_name = $this->getRequestValue('model_name');
        $tags_array_string = html_entity_decode($tags_array_string);
        $decoded = json_decode($tags_array_string, true);
        if ($model_name != '') {
            $_SESSION['model_tags'][$model_name]['tags_array'] = $decoded;
        } else {
            $_SESSION['tags_array'] = $decoded;
        }
        //$this->writeLog(__METHOD__.var_export($_SESSION['tags_array'], true));
        return true;
    }

    function get_distinct_values($item_array) {
        $DBC = DBC::getInstance();
        $query = 'SELECT distinct(`' . $item_array['name'] . '`) FROM ' . DB_PREFIX . '_' . $this->get_table_name() . ' WHERE `' . $item_array['name'] . '` IS NOT NULL AND `' . $item_array['name'] . '` != \'\' ORDER BY `' . $item_array['name'] . '`';
        //$this->writeLog(__METHOD__.', query = '.$query);
        $stmt = $DBC->query($query);

        $ra = array();

        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $this->total_in_select[$item_array['name']] ++;
                $value = $ar[$item_array['name']];
                $value = trim($value);
                //$value = htmlspecialchars_decode($value);
                $value = htmlspecialchars($value, ENT_QUOTES, SITE_ENCODING);
                $ra[] = $value;
            }
        }
        return $ra;
    }

    function get_array_by_structure($item_array, $model = null) {
        $DBC = DBC::getInstance();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $structure_array = $Structure_Manager->loadCategoryStructure($this->getConfigValue('use_topic_publish_status'));
        return $Structure_Manager->convertToNestedArray($structure_array);
    }

    function get_array_by_client_id($item_array, $model = null) {
        $DBC = DBC::getInstance();

        $query = 'SELECT fio, phone FROM ' . DB_PREFIX . '_client';
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ra[] = $ar['fio'] . ', ' . $ar['phone'];
            }
        }
        return $ra;
    }

    function get_array_by_select_box($item_array, $model = null) {
        //$this->writeLog(__METHOD__." <pre>".var_export($item_array, true)."</pre>");
        return array_values($item_array['select_data']);
    }

    protected function getBreadcrumbs($params) {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $category_structure = $Structure_Manager->loadCategoryStructure();
        return $this->get_category_breadcrumbs_string($params, $category_structure, SITEBILL_MAIN_URL . '/');
    }

    /**
     * Get single select box by query
     * @param array $item_array
     * @return string
     */
    function get_array_by_query($item_array, $model = null) {
        $this->total_in_select[$item_array['name']] = 0;
        $DBC = DBC::getInstance();

        $query = $item_array['query'];
        
        if ($this->get_ai_mode()) {
            $query = $this->modify_query($query, $item_array);
        }
        $stmt = $DBC->query($query);

        $ra = array();

        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $this->total_in_select[$item_array['name']] ++;
                $value = $ar[$item_array['value_name']];
                $value = trim($value);

                $value = htmlspecialchars($value, ENT_QUOTES, SITE_ENCODING);
                if ( $this->get_store_all_mode() ) {
                    $this->push_to_store($ar[$item_array['primary_key_name']], $ar);
                }
                if ($this->get_primary_key_mode()) {
                    $ra[] = array('id' => $ar[$item_array['primary_key_name']], 'value' => $value);
                } else {
                    $ra[] = $value;
                }
            }
        } else {
            $this->writeLog(__METHOD__ . ',query = ' . $query . ' DB Error = ' . $DBC->getLastError());
            $this->riseError(__METHOD__ . ',query = ' . $query . ' DB Error = ' . $DBC->getLastError());
            return false;
        }
        return $ra;
    }

    function modify_query($query, $item_array) {
        //$this->writeLog(__METHOD__ . "query = $query" . '<pre>' . var_export($item_array, true) . '</pre>');
        // $query = strtolower($query);
        if (!preg_match('/where/i', $query) and preg_match('/ order /i', $query)) {
            $query_parts = explode(' order ', $query);
            //$this->writeLog(__METHOD__ . "query_parts" . '<pre>' . var_export($query_parts, true) . '</pre>');

            $query_filter = "select distinct(" . DB_PREFIX . "_" . $item_array['table_name'] . "." . $item_array['name'] . ") from " . DB_PREFIX . "_" . $item_array['table_name'];
            //Для district.id делаем ручное переопределение
            if ($item_array['name'] == 'district_id' and $item_array['table_name'] == 'data') {
                $item_array['name'] = 'id';
            }
            $result_query = $query_parts[0] . " where " . $item_array['name'] . " in (" . $query_filter . ") " . " order " . $query_parts[1];
            return $result_query;
        }
        return $query;
    }

}
