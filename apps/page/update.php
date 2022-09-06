<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

class page_update extends SiteBill {

    /**
     * Construct
     */
    function __construct() {
        parent::__construct();
    }

    function main() {
        $rs = '';
        $DBC = DBC::getInstance();
        $query = 'SELECT table_id FROM ' . DB_PREFIX . '_table WHERE name=?';
        $stmt = $DBC->query($query, array('page'));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $table_id = $ar['table_id'];
            $query = 'SELECT COUNT(columns_id) AS _cnt FROM ' . DB_PREFIX . '_columns WHERE name=? AND table_id=?';
            $stmt = $DBC->query($query, array('template', $table_id));
            $ar = $DBC->fetch($stmt);
            if ($ar['_cnt'] == 0) {
                $query = 'SELECT MAX(sort_order) AS _cnt FROM ' . DB_PREFIX . '_columns WHERE table_id=?';
                $stmt = $DBC->query($query, array($table_id));
                $ar = $DBC->fetch($stmt);
                $sort_order = intval($ar['_cnt']) + 1;
                $query = 'INSERT INTO ' . DB_PREFIX . '_columns (`active`,`table_id`,`name`,`title`,`type`,`group_id`,`sort_order`) VALUES (?,?,?,?,?,?,?)';
                $stmt = $DBC->query($query, array(1, $table_id, 'template', 'Шаблон', 'safe_string', 0, $sort_order));
                if ($stmt) {
                    $rs .= 'Колонка [template] в модель добавлена<br>';
                } else {
                    $rs .= 'Ошибка добавления колонки [template] в модель: ' . $DBC->getLastError() . '<br>';
                }
            } else {
                $rs .= 'Колонка [template] уже существует<br>';
            }
        } else {
            $rs .= 'отсутствует модель [page]<br>';
        }

        $query_data[] = "ALTER TABLE " . DB_PREFIX . "_page ADD COLUMN is_service INT(11) not null default 0";
        $query_data[] = "ALTER TABLE " . DB_PREFIX . "_page ADD COLUMN template VARCHAR(255)";
        $query_data[] = "ALTER TABLE " . DB_PREFIX . "_page ADD COLUMN meta_title TEXT";
        $rs .= '<h3>' . Multilanguage::_('SQL_NOW', 'system') . '</h3>';


        foreach ($query_data as $query) {
            $success = false;
            $stmt = $DBC->query($query, array(), $rows, $success);
            if (!$success) {
                $rs .= Multilanguage::_('ERROR_ON_SQL_RUN', 'system') . ': ' . $query . '<br>';
            } else {
                $rs .= Multilanguage::_('QUERY_SUCCESS', 'system') . ': ' . $query . '<br>';
            }
        }
        return $rs;
    }

}
