<?php
/**
 * ObjectModelInitTrait — Model initialization and table creation methods extracted from Object_Manager.
 *
 * Methods: init_db_model, create_or_update_table, create_custom_entity
 *
 * @see Object_Manager
 */

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

trait ObjectModelInitTrait
{
    function init_db_model($table_name, $default_object_model, $params = false, $create_custom_entity = false, $custom_entity_title = '')
    {
        $result['status'] = 'first_run';

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
            $ATH = new Admin_Table_Helper();
            $form_data = $ATH->load_model($table_name, false);
            if (empty($form_data)) {
                $form_data = $default_object_model->get_model($params);
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php';
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php';
                $TA = new table_admin();
                $TA->create_table_and_columns($form_data, $table_name);
                $form_data = $ATH->load_model($table_name, false);
                if (!$ATH->check_table_exist($table_name)) {
                    $ATH->create_table($table_name);
                }
                if ($create_custom_entity) {
                    if (!$TA->check_entity_exist($table_name)) {
                        $TA->create_customentity_record($table_name, $custom_entity_title);
                    }
                }
            } else {
                $result['status'] = 'second_run';
            }
        } else {
            $form_data = $default_object_model->get_model($params);
        }

        $this->model = $default_object_model;
        $this->data_model = $form_data;

        if (!$this->check_table_exist(DB_PREFIX . '_' . $this->table_name)) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php';
            $TA = new \table_admin();
            $TA->create_table_and_columns($this->data_model, $this->table_name);
            $TA->helper->create_table_from_model($this->table_name, $this->data_model);
        }

        return $result;
    }

    public function create_or_update_table()
    {
        $this->data_model = $this->get_model();
        if (!$this->check_table_exist(DB_PREFIX . '_' . $this->table_name)) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php';
            $TA = new \table_admin();
            $TA->create_table_and_columns($this->data_model, $this->table_name);
            if (method_exists($TA->helper, 'create_table_from_model')) {
                $TA->helper->create_table_from_model($this->table_name, $this->data_model);
            }
        }
    }

    function create_custom_entity($custom_entity_title = '')
    {
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php';
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php';
        $TA = new \table_admin();
        if (!$TA->check_entity_exist($this->table_name)) {
            return $TA->create_customentity_record($this->table_name, $custom_entity_title);
        }
        return false;
    }
}
