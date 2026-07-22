<?php
/**
 * ObjectGridConfigTrait — Grid column configuration and index management extracted from Object_Manager.
 *
 * Methods: _formatgridAction, insert_table_grids, get_table_grids_fields, create_unique_index
 *
 * @see Object_Manager
 */

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

trait ObjectGridConfigTrait
{
    protected function _formatgridAction()
    {
        global $smarty;
        $DBC = DBC::getInstance();
        $action = $this->action;
        if ('post' === strtolower($_SERVER['REQUEST_METHOD'])) {
            $fields = $this->getRequestValue('field');
            if (is_array($fields) and @count($fields) > 0) {
                $query = 'INSERT INTO ' . DB_PREFIX . '_table_grids (`action_code`, `grid_fields`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `grid_fields`=?';
                $stmt = $DBC->query($query, array($action, json_encode($fields), json_encode($fields)));
            } else {
                $query = 'DELETE FROM ' . DB_PREFIX . '_table_grids WHERE `action_code`=?';
                $stmt = $DBC->query($query, array($action));
            }
        }

        $used_fields = array();
        $query = 'SELECT `grid_fields` FROM ' . DB_PREFIX . '_table_grids WHERE `action_code`=?';
        $stmt = $DBC->query($query, array($action));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $used_fields = json_decode($ar['grid_fields']);
        }

        $model_fields = $this->data_model[$this->table_name];
        $model_fields_resorted = array();

        if (!empty($used_fields)) {
            foreach ($used_fields as $uf) {
                $model_fields_resorted[$uf] = $model_fields[$uf];
                unset($model_fields[$uf]);
            }
            foreach ($model_fields as $k => $uf) {
                $model_fields_resorted[$k] = $model_fields[$k];
            }
            $model_fields = $model_fields_resorted;
        }

        $smarty->assign('used_fields', $used_fields);

        if ($this->save_url == 'empty') {
            $smarty->assign('save_url', '');
        } else {
            $smarty->assign('save_url', SITEBILL_MAIN_URL . '/admin/index.php?action=' . $this->action . '&do=formatgrid');
        }
        $smarty->assign('model_fields', $model_fields);
        $ret = $smarty->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/system/template/grid/grid_fields_managing.tpl');
        return $ret;
    }

    protected function insert_table_grids($table, $fields)
    {
        $DBC = DBC::getInstance();
        $query = 'INSERT INTO ' . DB_PREFIX . '_table_grids (`action_code`, `grid_fields`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `grid_fields`=?';
        $DBC->query($query, array($table, json_encode($fields), json_encode($fields)));
        if ($DBC->getLastError()) {
            $this->writeLog($DBC->getLastError());
            return false;
        }
        return true;
    }

    function get_table_grids_fields($table_name)
    {
        $DBC = DBC::getInstance();
        $used_fields = array();
        $query = 'SELECT `grid_fields` FROM ' . DB_PREFIX . '_table_grids WHERE `action_code`=?';
        $stmt = $DBC->query($query, array($table_name));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $used_fields = json_decode($ar['grid_fields']);
            if (!empty($used_fields)) {
                return $used_fields;
            }
        }
        return false;
    }

    function create_unique_index($table_name, $column_name)
    {
        $DBC = DBC::getInstance();

        $query = "SELECT DISTINCT TABLE_NAME, INDEX_NAME 
                    FROM INFORMATION_SCHEMA.STATISTICS 
                    WHERE TABLE_SCHEMA = ? and TABLE_NAME=? and INDEX_NAME=?";
        $stmt = $DBC->query($query, array(DB_BASE, DB_PREFIX . '_' . $table_name, 'idx_' . $column_name));
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                if ($ar['INDEX_NAME'] == 'idx_' . $column_name) {
                    return false;
                }
            }
        }

        $query = 'ALTER TABLE `' . DB_PREFIX . '_' . $table_name . '` ADD UNIQUE `idx_' . $column_name . '` (`' . $column_name . '` )';
        $stmt = $DBC->query($query, array());
        if ($DBC->getLastError()) {
            $this->writeLog($DBC->getLastError());
            return false;
        } else {
            $this->writeLog('success query: ' . 'ALTER TABLE `' . DB_PREFIX . '_' . $table_name . '` ADD UNIQUE `idx_' . $column_name . '` (`' . $column_name . '` )');
        }
        return true;
    }
}
