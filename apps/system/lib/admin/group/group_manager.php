<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Group manager
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class Group_Manager extends Object_Manager
{

    /**
     * Constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->table_name = 'group';
        $this->action = 'group';
        $this->app_title = Multilanguage::_('GROUP_APP_NAME', 'system');
        $this->primary_key = 'group_id';
        $this->grid_key = 'name';

        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/version/version.php';
        $version = new Version();
        if (!$version->get_version_value('group.table')) {
            $this->create_table();
            $version->set_version_value('group.table', 1);
        }

        if (!$version->get_version_value('dna.table')) {
            $this->create_dna_table();
            $version->set_version_value('dna.table', 1);
        }


        $this->data_model = $this->get_group_model();
    }

    /**
     * Load group array by group.system_name value
     * @param string $system_name
     * @return array
     */
    function load_by_system_name($system_name)
    {
        $DBC = DBC::getInstance();
        $group_id = 0;
        $query = "SELECT `group_id` FROM " . DB_PREFIX . "_group WHERE `system_name`=?";
        $stmt = $DBC->query($query, array($system_name));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $group_id = $ar['group_id'];
        }

        return $this->load_by_id($group_id);
    }

    /**
     * Get group model
     * @param
     * @return
     */
    function get_group_model()
    {

        require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
        $ATH = new Admin_Table_Helper();
        $form_data = $ATH->load_model('group', false);
        if (empty($form_data)) {
            $form_data = array();
            $form_data = $this->getUserGroupModelDescription();
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php';
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php';
            $TA = new table_admin();
            $TA->create_table_and_columns($form_data, 'group');
            $form_data = array();
            $form_data = $ATH->load_model('group', false);
        }

        return $form_data;
    }

    function getUserGroupModelDescription()
    {
        $form_group = array();

        /*$form_group['group']['is_aggr']['name'] = 'is_aggr';
        $form_group['group']['is_aggr']['title'] = Multilanguage::_('IS_AGGREGABLE','system');
        $form_group['group']['is_aggr']['value'] = '';
        $form_group['group']['is_aggr']['length'] = 40;
        $form_group['group']['is_aggr']['type'] = 'checkbox';
        $form_group['group']['is_aggr']['required'] = 'off';
        $form_group['group']['is_aggr']['unique'] = 'off';

        $form_group['group']['aggr_list']['name'] = 'aggr_list';
        $form_group['group']['aggr_list']['title'] = Multilanguage::_('IS_AGGR_LIST','system');
        $form_group['group']['aggr_list']['value'] = '';
        $form_group['group']['aggr_list']['length'] = 40;
        $form_group['group']['aggr_list']['type'] = 'safe_string';
        $form_group['group']['aggr_list']['required'] = 'off';
        $form_group['group']['aggr_list']['unique'] = 'off';*/

        $form_group['group']['group_id']['name'] = 'group_id';
        $form_group['group']['group_id']['title'] = Multilanguage::_('L_ID');
        $form_group['group']['group_id']['value'] = 0;
        $form_group['group']['group_id']['length'] = 40;
        $form_group['group']['group_id']['type'] = 'primary_key';
        $form_group['group']['group_id']['required'] = 'off';
        $form_group['group']['group_id']['unique'] = 'off';

        $form_group['group']['name']['name'] = 'name';
        $form_group['group']['name']['title'] = Multilanguage::_('GROUP_NAME', 'system');
        $form_group['group']['name']['value'] = '';
        $form_group['group']['name']['length'] = 40;
        $form_group['group']['name']['type'] = 'safe_string';
        $form_group['group']['name']['required'] = 'on';
        $form_group['group']['name']['unique'] = 'off';

        $form_group['group']['system_name']['name'] = 'system_name';
        $form_group['group']['system_name']['title'] = Multilanguage::_('SYSTEM_NAME', 'system') . ' (' . Multilanguage::_('LATIN_LETTERS_ONLY', 'system') . ')';
        $form_group['group']['system_name']['value'] = '';
        $form_group['group']['system_name']['length'] = 40;
        $form_group['group']['system_name']['type'] = 'safe_string';
        $form_group['group']['system_name']['required'] = 'on';
        $form_group['group']['system_name']['unique'] = 'off';

        return $form_group;
    }

    function create_dna_table()
    {
        $query = "
CREATE TABLE `" . DB_PREFIX . "_dna` (
  `group_id` int(11) NOT NULL DEFAULT '0',
  `component_id` int(11) NOT NULL DEFAULT '0',
  `function_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=" . DB_ENCODING . " ;
        ";
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
    }

    function create_table()
    {
        $query = "
CREATE TABLE `" . DB_PREFIX . "_group` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=" . DB_ENCODING . " ;
        ";
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
    }

    /**
     * Main
     * @param void
     * @return string
     */
    function main()
    {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;

        $rs = $this->getTopMenu();

        switch ($this->getRequestValue('do')) {

            case 'structure_permission':
                $rs = $this->structurePermissionProcessor();
                return $rs;
                break;

            case 'edit_done' :
            {
                $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
                if (!$this->check_data($form_data[$this->table_name])) {
                    $rs = $this->get_form($form_data[$this->table_name], 'edit');
                } else {
                    $this->edit_data($form_data[$this->table_name]);
                    $rs .= $this->grid();
                }
                break;
            }

            case 'edit' :
            {
                $form_data[$this->table_name] = $data_model->init_model_data_from_db($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name]);
                //echo '<pre>';
                //print_r($form_data[$this->table_name]);
                $rs = $this->get_form($form_data[$this->table_name], 'edit');

                break;
            }
            case 'delete' :
            {
                $this->delete_data($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key));
                if ($this->getError()) {
                    $rs .= '<div align="center">' . Multilanguage::_('L_ERROR_ON_DELETE') . ': ' . $this->GetErrorMessage() . '<br>';
                    $rs .= '<a href="?action=' . $this->action . '">ОК</a>';
                    $rs .= '</div>';
                } else {
                    $rs .= $this->grid();
                }

                break;
            }

            case 'new_done' :
            {

                $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
                //echo '<pre>';
                //print_r($form_data['data']);

                if (!$this->check_data($form_data[$this->table_name])) {
                    $rs = $this->get_form($form_data[$this->table_name], 'new');
                } else {
                    $this->add_data($form_data[$this->table_name]);
                    $rs .= $this->grid();
                }
                break;
            }

            case 'new' :
            {
                $rs = $this->get_form($form_data[$this->table_name]);
                break;
            }
            case 'mass_delete' :
            {
                $id_array = array();
                $ids = trim($this->getRequestValue('ids'));
                if ($ids != '') {
                    $id_array = explode(',', $ids);
                }
                $rs .= $this->mass_delete_data($this->table_name, $this->primary_key, $id_array);
                break;
            }
            default :
            {
                $rs .= $this->grid($user_id);
            }
        }
        $rs_new = $this->get_app_title_bar();
        $rs_new .= $rs;

        return $rs_new;
    }

    /**
     * Structure permission processor
     * @param
     * @return
     */
    function structurePermissionProcessor()
    {
        //echo $this->getRequestValue('structure_permission_do', 'default');
        switch ($this->getRequestValue('structure_permission_do')) {
            case 'update':
                $rs = $this->updateStructurePermission($this->getRequestValue('group_id'));
                return $rs;
                break;

            default:
                $rs = $this->getStructurePermissionEditForm($this->getRequestValue('group_id'));
                return $rs;
        }
    }

    /**
     * Update structure permission
     * @param int $group_id group ID
     * @return string
     */
    function updateStructurePermission($group_id)
    {

        //delete dna items for this group_id
        $query = "delete from " . DB_PREFIX . "_dna where group_id=$group_id";
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);

        $component_list = $this->loadComponentList();

        foreach ($component_list as $component_id => $function_list) {
            foreach ($function_list as $function_id) {
                if (($this->getRequestValue($component_id . ':' . $function_id) == $function_id)) {
                    $query = "insert into " . DB_PREFIX . "_dna (group_id, component_id, function_id) values ($group_id, $component_id, $function_id)";
                    //echo $query;
                    $stmt = $DBC->query($query, array(), $row, $succes_mark);
                    if (!$succes_mark) {
                        $rs .= $DBC->getLastError();
                    }
                }
            }
        }
        $rs = '<div align="center">' . Multilanguage::_('RULES_UPDATED_SUCCESS', 'system') . '<br><a href="?action=group">OK</a></div>';
        return $rs;
    }

    /**
     * Load component list
     * @param void
     * @return array
     */
    function loadComponentList()
    {
        $query = "select * from " . DB_PREFIX . "_component_function order by component_id, function_id";
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        $ra = array();
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ra[$ar['component_id']][] = $ar['function_id'];
            }
        }
        return $ra;
    }

    /**
     * Get structure permission edit form
     * @param int $group_id group ID
     * @return string
     */
    function getStructurePermissionEditForm($group_id)
    {
        $rs = '<form method="post">';
        $rs .= '<table id="structure" class="table table-hover">';
        $rs .= '<thead>';
        $rs .= '<tr><td>' . Multilanguage::_('ACCESS_RULES_SET', 'system') . '</td></tr>';
        $rs .= '</thead>';
        $rs .= $this->getComponentList($group_id);
        $rs .= '<input type="hidden" name="action" value="group">';
        $rs .= '<input type="hidden" name="group_id" value="' . $this->getRequestValue('group_id') . '">';
        $rs .= '<input type="hidden" name="structure_permission_do" value="update">';
        $rs .= '<input type="hidden" name="do" value="structure_permission">';
        $rs .= '<tr><td><input type="submit" class="btn btn-primary btn-large" value="' . Multilanguage::_('L_TEXT_SAVE') . '"></td></tr>';
        $rs .= '</table>';
        $rs .= '</form>';
        return $rs;
    }

    /**
     * Get component list
     * @param int $group_id group ID
     * @return string
     */
    function getComponentList($group_id)
    {
        $ra = array();
        $query = "select * from " . DB_PREFIX . "_component order by name";
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ra[] = $ar;
            }
        }
        foreach ($ra as $item_id => $row) {
            $rs .= '<tr><td><b>' . $row['title'] . ' (' . $row['name'] . ')' . '</b>' . $this->getFunctionRow($row['component_id'], $group_id) . '</td></tr>';
        }
        return $rs;
    }

    /**
     * Get function row
     * @param int $component_id
     * @param int $group_id group ID
     * @return string
     */
    function getFunctionRow($component_id, $group_id)
    {
        $molekula = array();

        //load DNA structure
        //return 'row';
        $query = "select * from " . DB_PREFIX . "_dna where group_id=$group_id and component_id=$component_id";
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $component[] = $ar['function_id'];
            }
        }

        $query = "select a.* from " . DB_PREFIX . "_component_function ma, " . DB_PREFIX . "_function a where ma.component_id=$component_id and ma.function_id=a.function_id";
        $stmt = $DBC->query($query);
        $rs = '<table border="1">';
        $rs .= '<tr>';
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                if (is_array($component) and @in_array($ar['function_id'], $component)) {
                    $checked = "checked";
                } else {
                    $checked = '';
                }
                $rs .= '<td>' . $ar['name'] . ' <input type="checkbox" name="' . $component_id . ':' . $ar['function_id'] . '" value="' . $ar['function_id'] . '" ' . $checked . '></td>';
            }
        }
        $rs .= '</tr>';
        $rs .= '</table>';
        return $rs;
    }

    /**
     * Grid
     * @param void
     * @return string
     */
    function grid($params = array(), $default_params = array())
    {
        global $_SESSION;
        $DBC = DBC::getInstance();

        $query = "select * from " . DB_PREFIX . "_" . $this->table_name . " order by '" . $this->grid_key . "'";
        $stmt = $DBC->query($query);

        $rs = '<table class="table table-hover">';
        $rs .= '<thead>';
        $rs .= '<tr>';
        $rs .= '<th>' . Multilanguage::_('L_TEXT_TITLE') . '</th>';
        $rs .= '<th></th>';
        $rs .= '</tr>';
        $rs .= '<thead>';
        $rs .= '<tbody>';
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $rs .= '<tr>';
                $rs .= '<td>' . $ar[$this->grid_key] . '</td>';
                $rs .= '<td>
	            <a class="btn btn-info" href="?action=' . $this->action . '&do=edit&' . $this->primary_key . '=' . $ar[$this->primary_key] . '"><i class="icon-white icon-pencil"></i></a>
	            <a class="btn btn-danger" href="?action=' . $this->action . '&do=delete&' . $this->primary_key . '=' . $ar[$this->primary_key] . '" onclick="if ( confirm(\'' . Multilanguage::_('L_MESSAGE_REALLY_WANT_DELETE') . '\') ) {return true;} else {return false;}"><i class="icon-white icon-remove"></i></a>
				<a href="?action=' . $this->action . '&do=structure_permission&' . $this->primary_key . '=' . $ar[$this->primary_key] . '" class="btn btn-info">права доступа</a>
	            </td>';
                $rs .= '</tr>';
            }
        }
        $rs .= '</tbody>';
        $rs .= '</table>';

        return $rs;
    }

    /**
     * Delete data
     * @param string $table_name
     * @param string $primary_key
     * @param int $primary_key_value
     */
    function delete_data($table_name, $primary_key, $primary_key_value)
    {
        $search_queries = array(
            Multilanguage::_('TABLE_USER', 'system') => 'SELECT COUNT(*) AS rs FROM ' . DB_PREFIX . '_user WHERE group_id=?'
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
