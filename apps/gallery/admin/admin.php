<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Gallery admin backend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class gallery_admin extends Object_Manager {
    use \system\traits\blade\BladeTrait;

    /**
     * Constructor
     */
    function __construct() {
        $this->SiteBill();
        Multilanguage::appendAppDictionary('gallery');
        $this->table_name = 'gallery';
        $this->action = 'gallery';
        $this->primary_key = 'gallery_id';

        $this->data_model = $this->get_gallery_model();
        $this->add_resource_path(SITEBILL_DOCUMENT_ROOT.'/apps/gallery/resources/views');
    }


    function structure_processor() {
        switch ($this->getRequestValue('do')) {
            case 'structure' : {
                    $this->install();
                    return 'Приложение установлено';
                    break;
                }
        }
    }

    function install() {
        $success_result = true;
        $DBC = DBC::getInstance();

        $query = "
CREATE TABLE `" . DB_PREFIX . "_gallery` (
  `gallery_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` text,
  `label` varchar(255) DEFAULT '',
  `short_description` text,
  `long_description` text,
  `image` text,
  `create_date` int(11) NOT NULL DEFAULT '0',
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `gallery_type` int(11) NOT NULL DEFAULT '0',
  `type_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`gallery_id`)
) ENGINE=MyISAM DEFAULT CHARSET=" . DB_ENCODING . " ;
        
ALTER TABLE `" . DB_PREFIX . "_gallery`
  ADD UNIQUE KEY `label` (`label`);
        ";
        $success = false;
        $stmt = $DBC->query($query, array(), $rows, $success);
        $success_result = $success_result && $success;

        if (!$success_result) {
            $rs = Multilanguage::_('L_APPLICATION_INSTALLED_ERROR');
        } else {
            $rs = Multilanguage::_('L_APPLICATION_INSTALLED');
        }
        return $rs;
    }

    /**
     * Grid
     * @param void
     * @return string
     */
    function grid($params = array(), $default_params = array()) {
        if (!$this->check_table_exist('gallery') or ! $this->check_table_exist('gallery_image')) {
            return 'Приложение не установлено. <a href="?action=gallery&do=structure&subdo=install">Нажмите чтобы установить</a>';
        }
        return parent::grid($params, $default_params);
    }

    function _get_gallery_model() {
        $form_gallery = array();

        $form_gallery['gallery']['gallery_id']['name'] = 'gallery_id';
        $form_gallery['gallery']['gallery_id']['title'] = Multilanguage::_('L_ID');
        $form_gallery['gallery']['gallery_id']['value'] = 0;
        $form_gallery['gallery']['gallery_id']['length'] = 40;
        $form_gallery['gallery']['gallery_id']['type'] = 'primary_key';
        $form_gallery['gallery']['gallery_id']['required'] = 'off';
        $form_gallery['gallery']['gallery_id']['unique'] = 'off';

        $form_gallery['gallery']['title']['name'] = 'title';
        $form_gallery['gallery']['title']['title'] = Multilanguage::_('GALLERY_NAME', 'gallery');
        $form_gallery['gallery']['title']['value'] = '';
        $form_gallery['gallery']['title']['length'] = 40;
        $form_gallery['gallery']['title']['type'] = 'safe_string';
        $form_gallery['gallery']['title']['required'] = 'on';
        $form_gallery['gallery']['title']['unique'] = 'off';

        $form_gallery['gallery']['label']['name'] = 'label';
        $form_gallery['gallery']['label']['title'] = _e('Метка');
        $form_gallery['gallery']['label']['value'] = '';
        $form_gallery['gallery']['label']['length'] = 40;
        $form_gallery['gallery']['label']['type'] = 'safe_string';
        $form_gallery['gallery']['label']['required'] = 'on';
        $form_gallery['gallery']['label']['unique'] = 'off';

        $form_gallery['gallery']['short_description']['name'] = 'short_description';
        $form_gallery['gallery']['short_description']['title'] = Multilanguage::_('GALLERY_SHORT_DESC', 'gallery');
        $form_gallery['gallery']['short_description']['value'] = '';
        $form_gallery['gallery']['short_description']['length'] = 40;
        $form_gallery['gallery']['short_description']['type'] = 'textarea_editor';
        $form_gallery['gallery']['short_description']['required'] = 'on';
        $form_gallery['gallery']['short_description']['unique'] = 'off';
        $form_gallery['gallery']['short_description']['rows'] = '10';
        $form_gallery['gallery']['short_description']['cols'] = '60';

        $form_gallery['gallery']['long_description']['name'] = 'long_description';
        $form_gallery['gallery']['long_description']['title'] = Multilanguage::_('GALLERY_FULL_DESC', 'gallery');
        $form_gallery['gallery']['long_description']['value'] = '';
        $form_gallery['gallery']['long_description']['length'] = 40;
        $form_gallery['gallery']['long_description']['type'] = 'textarea_editor';
        $form_gallery['gallery']['long_description']['required'] = 'on';
        $form_gallery['gallery']['long_description']['unique'] = 'off';
        $form_gallery['gallery']['long_description']['rows'] = '10';
        $form_gallery['gallery']['long_description']['cols'] = '60';

        $form_gallery['gallery']['image']['name'] = 'image';
        $form_gallery['gallery']['image']['title'] = 'Фото';
        $form_gallery['gallery']['image']['value'] = '';
        $form_gallery['gallery']['image']['length'] = 40;
        $form_gallery['gallery']['image']['type'] = 'uploads';
        $form_gallery['gallery']['image']['required'] = 'off';
        $form_gallery['gallery']['image']['unique'] = 'off';

        return $form_gallery;
    }

    /**
     * Get gallery model
     * @param
     * @return
     */
    function get_gallery_model() {
        $form_data = array();
        $table_name = 'gallery';
        //echo 'from table1';

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
            $ATH = new Admin_Table_Helper();
            $form_data = $ATH->load_model($table_name);
            if (empty($form_data)) {
                $form_data = array();
                $form_data = $this->_get_gallery_model();
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php';
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php';
                $TA = new table_admin();
                $TA->create_table_and_columns($form_data, $table_name);
                $form_data = array();
                $form_data = $ATH->load_model($table_name);
            }
        } else {
            $form_data = $this->_get_gallery_model($ajax);
        }
        return $form_data;
    }

}
