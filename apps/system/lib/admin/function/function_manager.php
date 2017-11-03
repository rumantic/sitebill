<?php

/**
 * Function manager
 * @author Kondin Dmitriy <kondin@etown.ru> http://sitebill.ru
 */
class Function_Manager extends Object_Manager {

    /**
     * Constructor
     */
    function Function_Manager() {
        $this->Sitebill();
        $this->table_name = 'function';
        $this->action = 'function';
        $this->app_title = Multilanguage::_('FUNCTION_APP_NAME', 'system');
        $this->primary_key = 'function_id';
        $this->grid_key = 'name';

        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/version/version.php';
        $version = new Version();
        if (!$version->get_version_value('function.table')) {
            $this->create_table();
            $version->set_version_value('function.table', 1);
        }

        $this->data_model = $this->get_function_model();
    }

    function create_table() {
        $query = "
CREATE TABLE `" . DB_PREFIX . "_function` (
  `function_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `description` text,
  PRIMARY KEY (`function_id`)
) ENGINE=MyISAM DEFAULT CHARSET=" . DB_ENCODING . " ;
        ";
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
    }

    /**
     * Grid
     * @param void
     * @return string
     */
    function grid($params = array(), $default_params = array()) {
        global $_SESSION;
        global $__db_prefix;
        if (is_object($this->language)) {
            $query = "select * from " . DB_PREFIX . "_" . $this->table_name . " where language_id = 0 order by name asc";
        } else {
            $query = "select * from " . DB_PREFIX . "_" . $this->table_name . " order by name asc";
        }

        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);

        $rs = '<table class="table table-hover">';
        $rs .= '<thead>';
        $rs .= '<tr>';
        $rs .= '<th>' . Multilanguage::_('L_TEXT_TITLE') . '</th>';
        $rs .= '<th>' . Multilanguage::_('DESCRIPTION', 'system') . '</th>';
        $rs .= '<th></th>';
        $rs .= '</tr>';
        $rs .= '<thead>';
        $rs .= '<tbody>';
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $rs .= '<tr>';
                $rs .= '<td>' . $ar['name'] . '</td>';
                $rs .= '<td>' . $ar['description'] . '</td>';

                $rs .= '<td>';
                //$rs .= '<a class="btn btn-info" href="?action='.$this->action.'&do=edit&'.$this->primary_key.'='.$ar[$this->primary_key].'"><i class="icon-white icon-pencil"></i></a> ';
                //$rs .= '<a class="btn btn-danger" href="?action='.$this->action.'&do=delete&'.$this->primary_key.'='.$ar[$this->primary_key].'" onclick="if ( confirm(\''.Multilanguage::_('L_MESSAGE_REALLY_WANT_DELETE').'\') ) {return true;} else {return false;}"><i class="icon-white icon-remove"></i></a> ';


                if (is_object($this->language)) {
                    if ($this->language->get_version($this->table_name, $this->primary_key, $ar[$this->primary_key], 1)) {
                        $exist = true;
                    } else {
                        $exist = false;
                    }
                    $rs .= $this->language->get_control($this->action, 'edit', $this->primary_key, $ar[$this->primary_key], 1, $exist);
                }
                $rs .= '</td>';
                $rs .= '</tr>';
            }
        }
        $rs .= '</tbody>';
        $rs .= '</table>';


        return $rs;
    }

    /**
     * Get function model
     * @param
     * @return
     */
    function get_function_model() {
        $form_function = array();

        $form_function['function']['function_id']['name'] = 'function_id';
        $form_function['function']['function_id']['title'] = Multilanguage::_('L_ID');
        $form_function['function']['function_id']['value'] = 0;
        $form_function['function']['function_id']['length'] = 40;
        $form_function['function']['function_id']['type'] = 'primary_key';
        $form_function['function']['function_id']['required'] = 'off';
        $form_function['function']['function_id']['unique'] = 'off';

        $form_function['function']['name']['name'] = 'name';
        $form_function['function']['name']['title'] = Multilanguage::_('FUNCTION_NAME', 'system');
        $form_function['function']['name']['value'] = '';
        $form_function['function']['name']['length'] = 40;
        $form_function['function']['name']['type'] = 'safe_string';
        $form_function['function']['name']['required'] = 'on';
        $form_function['function']['name']['unique'] = 'off';

        $form_function['function']['description']['name'] = 'description';
        $form_function['function']['description']['title'] = Multilanguage::_('DESCRIPTION', 'system');
        $form_function['function']['description']['value'] = '';
        $form_function['function']['description']['length'] = 40;
        $form_function['function']['description']['type'] = 'safe_string';
        $form_function['function']['description']['required'] = 'on';
        $form_function['function']['description']['unique'] = 'off';

        return $form_function;
    }

    /**
     * Get top menu
     * @param void 
     * @return string
     */
    function getTopMenu() {
        $rs = '';
        $rs .= '<a href="?action=' . $this->action . '&do=new" class="btn btn-primary">' . Multilanguage::_('ADD_FUNCTION', 'system') . '</a>';
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
            Multilanguage::_('TABLE_DNA', 'system') => 'SELECT COUNT(*) AS rs FROM ' . DB_PREFIX . '_dna WHERE function_id=?',
            Multilanguage::_('TABLE_COMP_FUNC', 'system') => 'SELECT COUNT(*) AS rs FROM ' . DB_PREFIX . '_component_function WHERE function_id=?'
        );
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
            $this->riseError(implode('<br />', $ans));
        }
    }

}
