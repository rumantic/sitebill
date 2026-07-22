<?php

trait StructureCrudTrait
{
    function edit_data($form_data, $language_id = 0, $primary_key_value = false)
    {

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        if ($primary_key_value) {
            $query_params = $data_model->get_prepared_edit_query(DB_PREFIX . '_' . $this->table_name, $this->primary_key, $primary_key_value, $form_data, $language_id);
        } else {
            $query_params = $data_model->get_prepared_edit_query(DB_PREFIX . '_' . $this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data, $language_id);
            $primary_key_value = $this->getRequestValue($this->primary_key);
        }

        $query_params_vals = $query_params['p'];


        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query_params['q'], $query_params_vals, $rows, $success);


        if (!$success) {
            $this->riseError($DBC->getLastError());
            return false;
        }

        foreach ($form_data as $form_item) {
            if ($form_item['type'] == 'uploads') {
                $imgs_uploads = $this->appendUploads($this->table_name, $form_item, $this->primary_key, (int)$this->getRequestValue($this->primary_key));
                //$this->set_imgs($imgs_uploads);
            } elseif ($form_item['type'] == 'select_by_query_multi') {
                //echo 1;
                $vals = $form_item['value'];
                if (!is_array($vals)) {
                    $vals = (array)$vals;
                }
                $query = 'DELETE FROM ' . DB_PREFIX . '_multiple_field WHERE `table_name`=? AND `field_name`=? AND `primary_id`=?';
                $stmt = $DBC->query($query, array($this->table_name, $form_item['name'], $primary_key_value));
                //echo $DBC->getLastError();
                if (!empty($vals)) {
                    //refresh
                    $query = 'INSERT INTO ' . DB_PREFIX . '_multiple_field (`table_name`, `field_name`, `primary_id`, `field_value`) VALUES (?,?,?,?)';
                    foreach ($vals as $val) {
                        $stmt = $DBC->query($query, array($this->table_name, $form_item['name'], $primary_key_value, $val));
                    }
                }
            }
        }
        foreach ($form_data as $form_item) {
            if ($form_item['type'] == 'uploadify_image') {
                $imgs = $this->editImageMulti($this->action, $this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key));
                //$this->set_imgs($imgs);
            }
        }
        foreach ($form_data as $form_item) {
            if ($form_item['type'] == 'uploadify_file') {
                $imgs = $this->editFileMulti($this->action, $this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key));
                //$this->set_imgs($imgs);
            }
        }
    }

    function add_data($form_data, $language_id = 0)
    {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        //$query = $data_model->get_insert_query(DB_PREFIX.'_'.$this->table_name, $form_data, $language_id);
        $query_params = $data_model->get_prepared_insert_query(DB_PREFIX . '_' . $this->table_name, $form_data, $language_id);
        $query_params_vals = $query_params['p'];
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query_params['q'], $query_params_vals, $rows, $success);
        //$stmt=$DBC->query($query, array(), $row, $success);
        if (!$success) {
            $this->riseError($DBC->getLastError());
            return false;
        }
        $new_record_id = $DBC->lastInsertId();
        if ($new_record_id > 0) {
            foreach ($form_data as $form_item) {
                if ($form_item['type'] == 'uploads') {
                    $imgs_uploads = $this->appendUploads($this->table_name, $form_item, $this->primary_key, $new_record_id);

                    //$this->set_imgs($imgs_uploads);
                } elseif ($form_item['type'] == 'select_by_query_multi') {
                    //echo 1;
                    $vals = $form_item['value'];
                    if (!is_array($vals)) {
                        $vals = (array)$vals;
                    }
                    $query = 'DELETE FROM ' . DB_PREFIX . '_multiple_field WHERE `table_name`=? AND `field_name`=? AND `primary_id`=?';
                    $stmt = $DBC->query($query, array($this->table_name, $form_item['name'], $new_record_id));
                    //echo $DBC->getLastError();
                    if (!empty($vals)) {
                        //refresh
                        $query = 'INSERT INTO ' . DB_PREFIX . '_multiple_field (`table_name`, `field_name`, `primary_id`, `field_value`) VALUES (?,?,?,?)';
                        foreach ($vals as $val) {
                            $stmt = $DBC->query($query, array($this->table_name, $form_item['name'], $new_record_id, $val));
                        }
                    }
                }
            }
            $imgs = $this->editImageMulti($this->action, $this->table_name, $this->primary_key, $new_record_id);

            //$this->set_imgs($imgs);
        }

        return $new_record_id;
    }

    /**
     * Delete record
     * @param int $id topic ID
     * @return boolean
     */
    function deleteRecord($id)
    {
        $imgs_ids = array();
        $DBC = DBC::getInstance();
        if (1 == $this->getConfigValue('allow_topic_images')) {
            $query = 'SELECT image_id FROM ' . DB_PREFIX . '_topic_image WHERE id=' . $id;


            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $imgs_ids[] = $ar['image_id'];
                }
            }
        }

        $query = "delete from " . DB_PREFIX . "_topic where id=" . $id . "";
        $stmt = $DBC->query($query);
        if (!$stmt) {
            echo 'ERROR ON DELETE';
        }
        if (count($imgs_ids) > 0) {
            foreach ($imgs_ids as $im) {
                $this->deleteImage('topic', $im);
            }
        }
        return TRUE;
    }

    function deleteTopicItem($id, $delete_option, $childs_delete_option)
    {
        $DBC = DBC::getInstance();
        $childs_delete_option = 'move_up';
        if ($delete_option == 'delete_current') {
            if ($childs_delete_option == 'move_up') {
                $query = 'SELECT parent_id FROM ' . DB_PREFIX . '_topic WHERE id=? LIMIT 1';
                $stmt = $DBC->query($query, array($id));
                $ar = $DBC->fetch($stmt);
                $parent_id = (int)$ar['parent_id'];


                $query = 'UPDATE ' . DB_PREFIX . '_topic SET parent_id=? WHERE parent_id=?';
                $stmt = $DBC->query($query, array($parent_id, $id));

                $query = 'UPDATE ' . DB_PREFIX . '_data SET topic_id=? WHERE topic_id=?';
                $stmt = $DBC->query($query, array($parent_id, $id));

                $this->deleteRecord($id);
            }
        } elseif ($delete_option == 'delete_incoming') {
            if ($childs_delete_option == 'move_up') {
                $parent_id = $id;


                $category_structure = $this->loadCategoryStructure();
                $childs_topics = array();
                $childs_topics = $this->get_all_childs($id, $category_structure);
                if (count($childs_topics) > 0) {
                    $str_a = array();
                    foreach ($childs_topics as $ct) {
                        $str_a[] = '?';
                    }

                    $params = $childs_topics;
                    array_unshift($params, $id);

                    $query = 'UPDATE ' . DB_PREFIX . '_data SET topic_id=? WHERE topic_id IN (' . implode(',', $str_a) . ')';
                    $stmt = $DBC->query($query, $params);

                    foreach ($childs_topics as $did) {
                        $this->deleteRecord($did);
                    }
                }
            }
        } elseif ($delete_option == 'delete_branch') {
            if ($childs_delete_option == 'move_up') {
                $query = 'SELECT parent_id FROM ' . DB_PREFIX . '_topic WHERE id=? LIMIT 1';
                $stmt = $DBC->query($query, array($id));
                $ar = $DBC->fetch($stmt);
                $parent_id = (int)$ar['parent_id'];


                $category_structure = $this->loadCategoryStructure();
                $childs_topics = array();
                $childs_topics = $this->get_all_childs($id, $category_structure);

                if (count($childs_topics) > 0) {
                    $str_a = array();
                    foreach ($childs_topics as $ct) {
                        $str_a[] = '?';
                    }

                    $childs_topics[] = $id;
                    $str_a[] = '?';

                    $params = $childs_topics;
                    array_unshift($params, $parent_id);

                    $query = 'UPDATE ' . DB_PREFIX . '_data SET topic_id=? WHERE topic_id IN (' . implode(',', $str_a) . ')';
                    $stmt = $DBC->query($query, $params);

                    foreach ($childs_topics as $did) {
                        $this->deleteRecord($did);
                    }
                }
            }
        }
    }

    function get_form($form_data = array(), $do = 'new', $language_id = 0, $button_title = '', $action = 'index.php')
    {

        $_SESSION['allow_disable_root_structure_select'] = false;
        global $smarty;
        if ($button_title == '') {
            $button_title = Multilanguage::_('L_TEXT_SAVE');
        }
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');
        $form_generator = new Form_Generator();


        $rs = $this->get_ajax_functions();
        if (1 == $this->getConfigValue('apps.geodata.enable')) {
            $rs .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/geodata/js/geodata.js"></script>';
        }
        $rs .= '<form method="post" class="form-horizontal" action="' . $action . '" enctype="multipart/form-data">';

        if ($this->getError()) {
            $smarty->assign('form_error', $form_generator->get_error_message_row($this->GetErrorMessage()));
        }

        $el = $form_generator->compile_form_elements($form_data);

        if ($do == 'new') {
            $el['private'][] = array('html' => '<input type="hidden" name="do" value="new_done" />');
            $el['private'][] = array('html' => '<input type="hidden" name="id" value="' . $this->getRequestValue('id') . '" />');
        } else {
            $el['private'][] = array('html' => '<input type="hidden" name="do" value="edit_done" />');
            $el['private'][] = array('html' => '<input type="hidden" name="id" value="' . $form_data['id']['value'] . '" />');
        }
        $el['private'][] = array('html' => '<input type="hidden" name="action" value="structure">');
        $el['private'][] = array('html' => '<input type="hidden" name="language_id" value="' . $language_id . '">');

        $el['form_header'] = $rs;
        $el['form_footer'] = '</form>';

        /* if ( $do != 'new' ) {
          $el['controls']['apply']=array('html'=>'<button id="apply_changes" class="btn btn-info">'.Multilanguage::_('L_TEXT_APPLY').'</button>');
          } */
        $el['controls']['submit'] = array('html' => '<button id="formsubmit" onClick="return SitebillCore.formsubmit(this);" name="submit" class="btn btn-primary">' . $button_title . '</button>');


        $smarty->assign('form_elements', $el);
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data.tpl')) {
            $tpl_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data.tpl';
        } else {
            $tpl_name = $this->getAdminTplFolder() . '/data_form.tpl';
        }
        return $smarty->fetch($tpl_name);
    }

    /**
     * Get form
     * @param string $action action
     * @return string
     */
    function getForm($action = 'new')
    {
        $form_data = $this->getStrModel();
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php';
        $OM = new Object_Manager();
        $rs = $OM->get_form($form_data['structure'], $action, 0, '', SITEBILL_MAIN_URL . '/admin/index.php?action=structure');
        return $rs;
    }

    /**
     * Load
     * @param int $record_id record ID
     * @return boolean
     */
    function load($record_id)
    {
        $DBC = DBC::getInstance();

        $query = "select * from " . DB_PREFIX . "_topic where id=$record_id";
        $stmt = $DBC->query($query);
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $this->setRequestValue('name', $ar['name']);

            $languages = Multilanguage::foreignLanguages();
            foreach ($languages as $language_id => $language_title) {
                $this->setRequestValue('name_' . $language_id, $ar['name_' . $language_id]);
            }


            $this->setRequestValue('id', $ar['id']);
            $this->setRequestValue('url', $ar['url']);
            $this->setRequestValue('description', $ar['description']);
            $this->setRequestValue('parent_id', $ar['parent_id']);
            $this->setRequestValue('meta_title', $ar['meta_title']);
            $this->setRequestValue('meta_keywords', $ar['meta_keywords']);
            $this->setRequestValue('meta_description', $ar['meta_description']);
        }
    }

    /**
     * Check data
     * @param void
     * @return boolean
     */
    function checkData()
    {
        if ($this->getRequestValue('name') == '') {
            $this->riseError(Multilanguage::_('NOT_SET_TOPIC_NAME', 'system'));
            return false;
        }
        if ($this->getRequestValue('parent_id') == $this->getRequestValue('id')) {
            $this->riseError(Multilanguage::_('CANT_BE_PARENT_YOURSELF', 'system'));
            return false;
        }

        if (0 != (int)$this->getRequestValue('id') && 0 != (int)$this->getRequestValue('parent_id')) {
            $id = (int)$this->getRequestValue('id');
            $parent_id = (int)$this->getRequestValue('parent_id');
            $category_structure = $this->loadCategoryStructure();
            $childs = $this->get_all_childs($id, $category_structure);
            if (in_array($parent_id, $childs)) {
                $this->riseError(Multilanguage::_('CANT_BE_PARENT_YOURSELF', 'system'));
                return false;
            }
        }

        if ($this->getRequestValue('url') == '') {
            $this->riseError('Не указан ЧПУ каталога');
            return false;
        }

        return true;
    }
}
