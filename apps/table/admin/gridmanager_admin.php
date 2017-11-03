<?php

class gridmanager_admin extends table_admin {

    public function __construct() {
        parent::__construct();
        if (!$this->helper->check_table_exist('table_gridmanager')) {
            $this->install();
        }
        $this->table_name = 'table_gridmanager';
    }

    function install() {
        $query = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "_table_gridmanager` (
			  `gridmanager_id` int(11) NOT NULL AUTO_INCREMENT,
			  `columns_id` int(10) unsigned NOT NULL,
			  PRIMARY KEY (`gridmanager_id`),
			  KEY `column_id` (`columns_id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=" . DB_ENCODING . " AUTO_INCREMENT=1 ;";
        $DBC = DBC::getInstance();
        $success = false;
        $stmt = $DBC->query($query, array(), $rows, $success);
        if (!$success) {
            $rs = Multilanguage::_('L_APPLICATION_INSTALLED_ERROR');
        } else {
            $rs = Multilanguage::_('L_APPLICATION_INSTALLED');
        }
        return $rs;
    }

    function main() {
        $rs = $this->getTopMenu();
        $rs .= $this->grid();


        $rs_new = $this->get_app_title_bar();
        $rs_new .= $rs;
        return $rs_new;
    }

    public function getGridColumns() {
        $ret = array();
        $DBC = DBC::getInstance();
        $q = 'SELECT c.name FROM ' . DB_PREFIX . '_' . $this->table_name . ' g LEFT JOIN ' . DB_PREFIX . '_columns c USING(columns_id) WHERE c.table_id=(SELECT table_id FROM ' . DB_PREFIX . '_table WHERE `name`=\'data\' LIMIT 1) ORDER BY g.gridmanager_id ASC';
        $stmt = $DBC->query($q);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ret[] = $ar['name'];
            }
        }
        if (empty($ret)) {
            $ret = array(
                'id',
                'topic_id',
                'price'
            );
        }
        return $ret;
    }

    private function _getColumnsNameIds() {
        $DBC = DBC::getInstance();

        $columns_ids = array();
        $query = 'SELECT columns_id, name FROM ' . DB_PREFIX . '_columns WHERE table_id=(SELECT table_id FROM ' . DB_PREFIX . '_table WHERE `name`=\'data\' LIMIT 1)';
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $columns_ids[$ar['name']] = $ar['columns_id'];
            }
        }

        return $columns_ids;
    }

    function grid($params = array(), $default_params = array()) {
        $DBC = DBC::getInstance();
        $columns = $this->_getColumnsNameIds();

        if (isset($_POST['submit'])) {
            $fields = $this->getRequestValue('field');
            if (!is_array($fields) || count($fields) == 0) {
                $fields['id'] = 'id';
                $fields['topic_id'] = 'topic_id';
                $fields['price'] = 'price';
            }
            $stmt = $DBC->query('TRUNCATE TABLE ' . DB_PREFIX . '_' . $this->table_name);
            $q = 'INSERT INTO ' . DB_PREFIX . '_' . $this->table_name . ' (columns_id) values(?)';
            foreach ($fields as $key => $f) {
                $stmt = $DBC->query($q, array($columns[$key]));
            }
        }
        $ret = array();

        $q = 'SELECT c.name FROM ' . DB_PREFIX . '_' . $this->table_name . ' g LEFT JOIN ' . DB_PREFIX . '_columns c USING(columns_id) WHERE c.table_id=(SELECT table_id FROM ' . DB_PREFIX . '_table WHERE `name`=\'data\' LIMIT 1) ORDER BY g.gridmanager_id ASC';



        $stmt = $DBC->query($q);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ret[] = $ar['name'];
            }
        }

        $model_data = $this->helper->load_model('data');



        foreach ($model_data['data'] as $k => $v) {
            $fields[$k] = array('title' => $v['title']);
            if (in_array($k, $ret)) {
                $fields[$k]['checked'] = 1;
            }
        }
        $this->template->assert('fields', $fields);
        global $smarty;
        $form_body = $smarty->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/template/gridmanager_list.tpl');
        return $form_body;
    }

    public function save_search_form() {
        $DBC = DBC::getInstance();
        $columns_ids = array();
        $form_id = (int) $this->getRequestValue('form_id');
        $topic_id = $this->getRequestValue('topic_id');
        $form_title = preg_replace('/[^A-Za-zА-Яа-я0-9єії\-_ ]/', '', SiteBill::iconv('utf-8', SITE_ENCODING, $this->getRequestValue('form_title')));
        $fields = $this->getRequestValue('fields');
        if (count($fields) == 0) {
            $q = "DELETE FROM " . DB_PREFIX . "_table_searchform WHERE `searchform_id`=" . $form_id;
            $stmt = $DBC->query($q);
            return;
        }
        $q = 'SELECT columns_id, name FROM ' . DB_PREFIX . '_columns WHERE table_id=(SELECT table_id FROM ' . DB_PREFIX . '_table WHERE `name`=\'data\' LIMIT 1)';
        $stmt = $DBC->query($q);
        while ($ar = $DBC->fetch($stmt)) {
            $columns_ids[$ar['name']] = $ar['columns_id'];
        }
        $q = "DELETE FROM " . DB_PREFIX . "_table_searchform WHERE `searchform_id`=" . $form_id;
        $stmt = $DBC->query($q);

        $input_fields = array();
        foreach ($fields as $f) {
            $input_fields[] = $columns_ids[$f];
        }

        if ($form_id == 0) {
            $DBC->query("INSERT INTO " . DB_PREFIX . "_table_searchform (`topic_id`, `columns`, `title`) VALUES (?,?,?)", array((int) $topic_id, serialize($input_fields), $form_title));
        } else {
            $DBC->query("INSERT INTO " . DB_PREFIX . "_table_searchform (`searchform_id`, `topic_id`, `columns`, `title`) VALUES (?,?,?,?)", array($form_id, (int) $topic_id, serialize($input_fields), $form_title));
        }
    }

}
