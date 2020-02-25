<?php

/**
 * Menu manager
 */
class Menu_Manager extends Object_Manager {

    /**
     * Constructor
     */
    function Menu_Manager() {
        $this->SiteBill();
        $this->check_table();
        $this->table_name = 'menu';
        $this->action = 'menu';
        $this->app_title = Multilanguage::_('MENU_APP_NAME', 'system');
        $this->primary_key = 'menu_id';
        $this->grid_key = 'name';

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/version/version.php';
        $version = new Version();
        if (!$version->get_version_value('menu.table')) {
            $this->check_table();
            $version->set_version_value('menu.table', 1);
        }

        $this->data_model = $this->get_menu_model();
    }

    protected function _deleteAction() {
        $rs = '';
        $pk = (int) $this->getRequestValue($this->primary_key);
        $DBC = DBC::getInstance();
        $query = 'DELETE FROM ' . DB_PREFIX . '_menu_structure WHERE menu_id=?';
        $stmt = $DBC->query($query, array($pk));
        $this->delete_data($this->table_name, $this->primary_key, $pk);
        if ($this->getError()) {
            $rs .= '<div align="center">' . Multilanguage::_('L_ERROR_ON_DELETE') . ': ' . $this->GetErrorMessage() . '<br>';
            $rs .= '<a href="?action=' . $this->action . '">ОК</a>';
            $rs .= '</div>';
        } else {
            $rs .= $this->grid();
        }
        return $rs;
    }

    private function check_table() {
        $DBC = DBC::getInstance();
        $table = "
CREATE IF NOT EXIST TABLE `" . DB_PREFIX . "_menu` (
  `menu_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `tag` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`menu_id`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=" . DB_ENCODING . " ;
        ";
        //echo $table;
        $stmt = $DBC->query($table);


        $table = "
CREATE IF NOT EXIST TABLE `" . DB_PREFIX . "_menu_structure` (
  `menu_structure_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `url` text,
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `menu_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`menu_structure_id`)
) ENGINE=MyISAM AUTO_INCREMENT=68 DEFAULT CHARSET=" . DB_ENCODING . " ;
        ";
        $stmt = $DBC->query($table);
    }

    /**
     * Get menu model
     * @param
     * @return
     */
    function get_menu_model() {
        $form_menu = array();

        $form_menu['menu']['menu_id']['name'] = 'menu_id';
        $form_menu['menu']['menu_id']['title'] = Multilanguage::_('L_TEXT_IDENTIFIER');
        $form_menu['menu']['menu_id']['value'] = 0;
        $form_menu['menu']['menu_id']['length'] = 40;
        $form_menu['menu']['menu_id']['type'] = 'primary_key';
        $form_menu['menu']['menu_id']['required'] = 'off';
        $form_menu['menu']['menu_id']['unique'] = 'off';

        $form_menu['menu']['name']['name'] = 'name';
        $form_menu['menu']['name']['title'] = Multilanguage::_('L_TEXT_MENU_NAME');
        $form_menu['menu']['name']['value'] = '';
        $form_menu['menu']['name']['length'] = 40;
        $form_menu['menu']['name']['type'] = 'safe_string';
        $form_menu['menu']['name']['required'] = 'on';
        $form_menu['menu']['name']['unique'] = 'off';


        if (1 == $this->getConfigValue('apps.language.use_langs')) {
            $langs = Multilanguage::availableLanguages();

            foreach ($langs as $ln) {
                $form_menu['menu']['name_' . $ln]['name'] = 'name_' . $ln;
                $form_menu['menu']['name_' . $ln]['title'] = Multilanguage::_('L_TEXT_MENU_NAME') . ' (' . $ln . ')';
                $form_menu['menu']['name_' . $ln]['value'] = '';
                $form_menu['menu']['name_' . $ln]['length'] = 40;
                $form_menu['menu']['name_' . $ln]['type'] = 'safe_string';
                $form_menu['menu']['name_' . $ln]['required'] = 'on';
                $form_menu['menu']['name_' . $ln]['unique'] = 'off';
            }
            /* if($current_lang=='ru' || $current_lang==''){
              $lang_prefix='';
              }else{
              $lang_prefix='_'.$current_lang;
              } */
        }

        $form_menu['menu']['tag']['name'] = 'tag';
        $form_menu['menu']['tag']['title'] = Multilanguage::_('L_TEXT_MENU_TAG');
        $form_menu['menu']['tag']['value'] = '';
        $form_menu['menu']['tag']['length'] = 40;
        $form_menu['menu']['tag']['type'] = 'safe_string';
        $form_menu['menu']['tag']['required'] = 'on';
        $form_menu['menu']['tag']['unique'] = 'off';


        return $form_menu;
    }

    /**
     * Structure processor
     * @param void
     * @return string
     */
    function structure_processor() {
        $menu_id = (int) $this->getRequestValue('menu_id');

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/menu/menu_structure_manager.php');

        $menu_structure_manager = new Menu_Structure_Manager();

        $data_model = new Data_Model();
        $form_data = $this->data_model;
        $form_menu_structure = $menu_structure_manager->get_menu_structure_model();


        $form_data[$this->table_name] = $data_model->init_model_data_from_db($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name]);
        $rs .= '<p>Редактирование структуры меню: <b>' . $form_data[$this->table_name]['name']['value'] . '</b></p><br>';
        $rs .= $this->get_add_structure_menu($menu_id);

        switch ($this->getRequestValue('subdo')) {
            case 'delete' : {
                    $menu_structure_manager->delete_data('menu_structure', 'menu_structure_id', $this->getRequestValue('menu_structure_id'));
                    $rs .= $menu_structure_manager->grid_e($menu_id);
                    break;
                }

            case 'delete_catalog' : {
                    $menu_catalog_manager->delete_data('menu_catalog', 'menu_catalog_id', $this->getRequestValue('menu_catalog_id'));
                    $rs .= $menu_structure_manager->grid_e($menu_id);
                    break;
                }


            case 'edit_done' : {
                    $form_menu_structure['menu_structure'] = $data_model->init_model_data_from_request($form_menu_structure['menu_structure']);
                    if (!$this->check_data($form_menu_structure['menu_structure'])) {
                        $rs .= $this->get_form_extended($form_menu_structure['menu_structure'], 'structure', 'edit_done');
                    } else {
                        $menu_structure_manager->edit_data($form_menu_structure['menu_structure']);
                        if ($menu_structure_manager->getError()) {
                            $this->riseError($menu_structure_manager->GetErrorMessage());
                            $rs .= $this->get_form_extended($form_menu_structure['menu_structure'], 'structure', 'edit_done');
                            break;
                        }
                        $rs .= $menu_structure_manager->grid_e($menu_id);
                    }
                    break;
                }

            case 'edit' : {
                    $form_menu_structure['menu_structure'] = $data_model->init_model_data_from_db($menu_structure_manager->table_name, $menu_structure_manager->primary_key, $this->getRequestValue($menu_structure_manager->primary_key), $form_menu_structure['menu_structure']);
                    //echo '<pre>';
                    //print_r($form_data[$this->table_name]);
                    $rs .= $this->get_form_extended($form_menu_structure['menu_structure'], 'structure', 'edit_done');
                    break;
                }

            case 'edit_catalog_done' : {
                    $form_menu_catalog['menu_catalog'] = $data_model->init_model_data_from_request($form_menu_catalog['menu_catalog']);
                    if (!$this->check_data($form_menu_catalog['menu_catalog'])) {
                        $rs .= $this->get_form_extended($form_menu_catalog['menu_catalog'], 'structure', 'edit_catalog_done');
                    } else {
                        $menu_catalog_manager->edit_data($form_menu_catalog['menu_catalog']);
                        $rs .= $menu_structure_manager->grid_e($menu_id);
                    }
                    break;
                }

            case 'edit_catalog' : {
                    $form_menu_catalog['menu_catalog'] = $data_model->init_model_data_from_db($menu_catalog_manager->table_name, $menu_catalog_manager->primary_key, $this->getRequestValue($menu_catalog_manager->primary_key), $form_menu_catalog['menu_catalog']);
                    //echo '<pre>';
                    //print_r($form_data[$this->table_name]);
                    $rs .= $this->get_form_extended($form_menu_catalog['menu_catalog'], 'structure', 'edit_catalog_done');
                    break;
                }


            case 'new_done' : {
                    $form_menu_structure['menu_structure'] = $data_model->init_model_data_from_request($form_menu_structure['menu_structure']);
                    if (!$this->check_data($form_menu_structure['menu_structure'])) {
                        $rs .= $this->get_form_extended($form_menu_structure['menu_structure'], 'structure', 'new_done');
                    } else {
                        $menu_structure_manager->add_data($form_menu_structure['menu_structure']);
                        if ($menu_structure_manager->getError()) {
                            $this->riseError($menu_structure_manager->GetErrorMessage());
                            $rs .= $this->get_form_extended($form_menu_structure['menu_structure'], 'structure', 'new_done');
                            break;
                        }
                        
                        $rs .= $menu_structure_manager->grid_e($menu_id);
                        //$rs .= 'добавлен';
                    }
                    break;
                }

            case 'new_catalog_done' : {
                    $form_menu_catalog['menu_catalog'] = $data_model->init_model_data_from_request($form_menu_catalog['menu_catalog']);
                    if (!$this->check_data($form_menu_catalog['menu_catalog'])) {
                        $rs .= $this->get_form_extended($form_menu_catalog['menu_catalog'], 'structure', 'new_catalog_done');
                    } else {
                        $menu_catalog_manager->add_data($form_menu_catalog['menu_catalog']);
                        $rs .= $menu_structure_manager->grid_e($menu_id);
                        //$rs .= 'добавлен';
                    }
                    break;
                }


            case 'new_catalog' : {
                    $form_menu_catalog['menu_catalog']['menu_id']['value'] = $menu_id;
                    $rs .= $this->get_form_extended($form_menu_catalog['menu_catalog'], 'structure', 'new_catalog_done');
                    break;
                }

            case 'new' : {
                    $form_menu_structure['menu_structure']['menu_id']['value'] = $menu_id;
                    $rs .= $this->get_form_extended($form_menu_structure['menu_structure'], 'structure', 'new_done');
                    break;
                }

            default : {
                    $rs .= $menu_structure_manager->grid_e($menu_id);
                }
        }
        return $rs;
    }

    /**
     * Get form extended
     * @param array $form_data
     * @param string $do
     * @param string $subdo
     * @return string
     */
    function get_form_extended($form_data = array(), $do, $subdo) {

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');
        $form_generator = new Form_Generator();



        $rs .= '<form method="post" class="form-horizontal" action="index.php">';
        if ($this->getError()) {
            $rs .= $form_generator->get_error_message_row($this->GetErrorMessage());
        }
        //$rs .= $form_generator->compile_form($form_data);
        $el = $form_generator->compile_form_elements($form_data);
        //print_r($el);
        foreach ($el['public'][_e($this->getConfigValue('default_tab_name'))] as $elp) {
            $rs .= '<div class="form_element control-group" alt="' . $elp['name'] . '">';
            $rs .= '<label class="control-label">' . $elp['title'] . ($elp['required'] == 1 ? '<span style="color: red;">*</span>' : '') . ($elp['hint'] != '' ? ' <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content="' . $elp['hint'] . '"> <i class="icon-question-sign icon-white"></i></a>' : '') . '</label>';
            $rs .= '<div class="form_element_html controls">' . $elp['html'] . '</div>';
            $rs .= '</div>';
        }

        $rs .= '<input type="hidden" name="do" value="' . $do . '">';
        $rs .= '<input type="hidden" name="subdo" value="' . $subdo . '">';
        $rs .= '<input type="hidden" name="' . $this->primary_key . '" value="' . $this->getRequestValue('menu_id') . '">';
        if ($subdo == 'edit_done') {
            $rs .= '<input type="hidden" name="menu_structure_id" value="' . $this->getRequestValue('menu_structure_id') . '">';
        }

        if ($subdo == 'edit_catalog_done') {
            $rs .= '<input type="hidden" name="menu_catalog_id" value="' . $this->getRequestValue('menu_catalog_id') . '">';
        }


        $rs .= '<input type="hidden" name="action" value="' . $this->action . '">';
        $rs .= '<div class="form_element control-group" alt="' . $elp['name'] . '">';
        $rs .= '<div class="form_element_html controls"><input class="btn btn-primary" type="submit" name="submit" value="' . Multilanguage::_('L_TEXT_SAVE') . '"></div>';
        $rs .= '</div>';

        $rs .= '</form>';


        return $rs;
    }

    /**
     * Get add structure menu
     * @param int $menu_id
     */
    function get_add_structure_menu($menu_id) {
        $rs = '<a href="?action=' . $this->action . '&do=structure&subdo=new&menu_id=' . $menu_id . '" class="btn btn-primary">' . Multilanguage::_('L_TEXT_ADD_POINT') . '</a> ';
        return $rs;
    }

    /**
     * Grid
     * @param void
     * @return string
     */
    function grid($params = array(), $default_params = array()) {

        $DBC = DBC::getInstance();
        $query = 'SELECT * FROM ' . DB_PREFIX . '_' . $this->table_name . ' ORDER BY `' . $this->grid_key . '`';
        $stmt = $DBC->query($query);

        $rs = '<table class="table table-hover">';
        $rs .= '<thead>';
        $rs .= '<tr>';
        $rs .= '<th>' . Multilanguage::_('L_TEXT_TITLE') . '</th>';
        $rs .= '<th></th>';
        $rs .= '</tr>';
        $rs .= '</thead>';
        $rs .= '<tbody>';
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $rs .= '<tr>';
                $rs .= '<td>' . $ar[$this->grid_key] . '</td>';
                $rs .= '<td>
            <a class="btn btn-info" href="?action=' . $this->action . '&do=edit&' . $this->primary_key . '=' . $ar[$this->primary_key] . '"><i class="icon-white icon-pencil"></i></a>
            <a class="btn btn-danger" href="?action=' . $this->action . '&do=delete&' . $this->primary_key . '=' . $ar[$this->primary_key] . '" onclick="if ( confirm(\'' . Multilanguage::_('L_MESSAGE_REALLY_WANT_DELETE') . '\') ) {return true;} else {return false;}"><i class="icon-white icon-remove"></i></a>
			<a href="?action=' . $this->action . '&do=structure&' . $this->primary_key . '=' . $ar[$this->primary_key] . '" class="btn btn-info">' . Multilanguage::_('L_TEXT_STRUCTURE') . '</a>
            </td>';
                $rs .= '</tr>';
            }
        }

        $rs .= '</tbody>';
        $rs .= '</table>';
        return $rs;
    }

    /**
     * Get top menu
     * @param void 
     * @return string
     */
    function getTopMenu() {
        $rs = '';
        $rs .= '<a href="?action=' . $this->action . '&do=new" class="btn btn-primary">' . Multilanguage::_('L_TEXT_CREATE_MENU') . '</a>';
        return $rs;
    }

}

?>