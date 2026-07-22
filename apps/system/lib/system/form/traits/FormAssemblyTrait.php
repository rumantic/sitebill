<?php
trait FormAssemblyTrait
{
    function get_field_tpl($type, $tablename, $fieldname, $formname = '')
    {
        $tpl = '';
        //var_dump(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/system/fields/name-'.$tablename.'.'.$fieldname.'.tpl');
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/system/fields/name-' . $tablename . '.' . $fieldname . '.tpl')) {
            $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/system/fields/name-' . $tablename . '.' . $fieldname . '.tpl';
        } elseif (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/system/fields/type-' . $type . '.tpl')) {
            $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/system/fields/type-' . $type . '.tpl';

        } elseif (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/system/fields/name-' . $tablename . '.' . $fieldname . '.tpl')) {
            $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/system/fields/name-' . $tablename . '.' . $fieldname . '.tpl';
        } elseif (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/system/fields/type-' . $type . '.tpl')) {
            $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/system/fields/type-' . $type . '.tpl';
        }
        return $tpl;
    }

    function compile_form_elements($form_data, $ignore_tabs = false)
    {

        $elements = array();
        $scripts = array();
        $default_tab_name = _e($this->getConfigValue('default_tab_name'));
        $tabs = array();
        //$tabs[$default_tab_name]=$default_tab_name;
        //print_r($form_data);
        foreach ($form_data as $item_id => $item_array) {
            if (!isset($item_array['name'])) {
                continue;
            }

            //$tab_name=$item_array['tab_'.$this->getCurrentLang()];

            /* if($tab_name==''){
              $tab_name=$item_array['tab'];
              }

              if($tab_name==''){
              $tab_name=$default_tab_name;
              } */


            switch ($item_array['type']) {
                case 'select_entity':
                    $rs = $this->compile_select_entity_element($item_array);
                    break;
                case 'gadres':
                    $rs = $this->compile_gadres_element($item_array);
                    break;
                case 'uploads':
                    $rs = $this->compile_uploads_element($item_array);
                    break;
                case 'client_id':
                    $rs = $this->compile_client_id_element($item_array);
                    break;
                case 'docuploads':
                    $rs = $this->compile_docuploads_element($item_array);
                    break;
                case 'tlocation':
                    $rs = $this->compile_tlocation_element($item_array);
                    break;
                case 'parameter':
                    $rs = $this->compile_parameter_element($item_array);
                    break;
                case 'price':
                    $rs = $this->compile_price_element($item_array);
                    break;
                case 'select_box':
                    $rs = $this->compile_selectbox_element($item_array);
                    break;
                case 'attachment':
                    $rs = $this->compile_attachment_element($item_array);
                    break;
                case 'geodata':
                    $rs = $this->compile_geodata_element($item_array);
                    break;

                case 'email':
                    $rs = $this->compile_email_element($item_array);
                    break;

                case 'mobilephone':
                    $rs = $this->compile_mobilephone_element($item_array);
                    break;

                case 'select_by_query':
                    $rs = $this->compile_select_box_by_query_element($item_array, $form_data);
                    break;
                case 'select_by_query_multi' :
                    $rs = $this->compile_select_by_query_multi_element($item_array, $form_data);
                    break;
                case 'select_by_query_multiple':
                    $rs = $this->compile_select_box_by_query_multiple_element($item_array);
                    break;

                case 'select_box_structure':
                    $rs = $this->compile_select_box_structure_element($item_array);
                    break;

                case 'select_box_structure_simple_multiple':
                    $rs = $this->compile_select_box_structure_simple_multiple_element($item_array);
                    break;

                case 'select_box_structure_multiple_checkbox':
                    $rs = $this->compile_select_box_structure_multiple_checkbox($item_array);
                    break;


                case 'shop_select_box_structure':
                    $rs = $this->get_shop_select_box_structure_row($item_array);
                    break;

                case 'service_type_select_box_structure':
                    {
                        $rs = $this->get_service_type_select_box_structure_row($item_array);
                    }
                    break;
                /*
                  case 'uploader':
                  $rs .= $this->get_uploader_row($item_array);
                  break;

                  case 'pluploader':
                  $rs .= $this->get_pluploader_row($item_array);
                  break;
                 */
                case 'uploadify_image':
                    switch ($this->getConfigValue('uploader_type')) {
                        case 'pluploader' :
                        {
                            $rs = $this->compile_pluploader_element($item_array);
                            break;
                        }
                        default :
                        {
                            $rs = $this->compile_uploadify_element($item_array);
                        }
                    }

                    break;

                case 'uploadify_file':
                    switch ($this->getConfigValue('uploader_type')) {
                        case 'pluploader' :
                        {
                            //$rs = $this->compile_pluploader_element($item_array);
                            $rs = $this->compile_pluploader_file_element($item_array);
                            break;
                        }
                        default :
                        {
                            $rs = $this->compile_uploadify_file_element($item_array);
                        }
                    }
                    //$rs = $this->get_uploadify_file_row($item_array);
                    break;

                case 'separator':
                    $rs = $this->compile_separator_element($item_array);
                    break;

                case 'checkbox':
                    $rs = $this->compile_checkbox_element($item_array);
                    break;

                case 'textarea':
                    $rs = $this->compile_textarea_element($item_array);
                    break;

                case 'textarea_editor':
                    $rs = $this->compile_textarea_editor_element($item_array);
                    break;

                case 'grade':
                    $rs = $this->compile_grade_element($item_array);
                    break;

                case 'date':
                    //$rs = $this->get_date_input($item_array);
                    $rs = $this->compile_date_element($item_array);
                    break;

                case 'datetime':
                    $rs = $this->compile_datetime_element($item_array);
                    break;
                case 'dtdatetime':
                    $rs = $this->compile_dtdatetime_element($item_array);
                    break;
                case 'dtdate':
                    $rs = $this->compile_dtdate_element($item_array);
                    break;
                case 'dttime':
                    $rs = $this->compile_dttime_element($item_array);
                    break;

                case 'auto_add_value':
                    $rs = $this->compile_safe_string_element($item_array);
                    break;

                case 'safe_string':
                    $rs = $this->compile_safe_string_element($item_array);
                    break;

                case 'password':
                    //$rs = $this->get_password_input($item_array);
                    $rs = $this->compile_password_element($item_array);
                    break;

                case 'photo':
                    $rs = $this->compile_photo_element($item_array);
                    break;

                case 'avatar':
                    $rs = $this->compile_avatar_element($item_array);
                    break;

                case 'captcha':
                    $rs = $this->compile_captcha_element($item_array);
                    break;

                case 'spacer_text':
                    $rs = $this->compile_spacer_text_element($item_array);
                    break;

                case 'hidden':
                    $rs = $this->compile_hidden_element($item_array);
                    break;

                case 'primary_key':
                    $rs = $this->compile_primary_key_element($item_array);
                    break;

                case 'values_list':
                    $rs = $this->get_safe_text_input($item_array);
                    break;

                case 'structure':
                    $rs = $this->compile_structure_element($item_array);
                    break;

                case 'injector':
                    $rs = $this->compile_injector_element($item_array, $form_data);
                    break;

                case 'youtube':
                    $rs = $this->compile_youtube_element($item_array);
                    break;

                default:
                    $rs = FALSE;
                    break;
            }

            if ($rs === FALSE) {

            } elseif (is_object($rs)) {
                if (isset($rs->collection) && count($rs->collection) != 0) {

                    foreach ($rs->collection as $collection_element) {
                        $ce = $collection_element;
                        $ce['hint'] = $item_array['hint'];
                        $ce['type'] = $item_array['type'];
                        //$ce['name']=$item_array['name'];
                        $ce['active_in_topic'] = $item_array['active_in_topic'];
                        if ($item_array['type'] == 'hidden' || $item_array['type'] == 'primary_key') {
                            $elements['private'][$ce['name']] = $ce;
                        } else {
                            if ($ce['tab'] == '') {
                                $ce['tab'] = $default_tab_name;
                            }
                            if ($ignore_tabs) {
                                $elements['public'][$default_tab_name][$ce['name']] = $ce;
                            } else {
                                $elements['public'][$ce['tab']][$ce['name']] = $ce;
                            }
                        }
                        $elements['hash'][$ce['name']] = $ce;
                    }
                }
                //
                if (isset($rs->scripts) && count($rs->scripts) != 0) {
                    foreach ($rs->scripts as $script_element) {
                        $scripts[] = $script_element;
                    }
                }
                //print_r($rs);
            } else {
                $rs['hint'] = (isset($item_array['hint']) ? $item_array['hint'] : '');
                $rs['name'] = $item_array['name'];
                $rs['active_in_topic'] = (isset($item_array['active_in_topic']) ? $item_array['active_in_topic'] : '');
                $rs['type'] = $item_array['type'];
                $rs['parameters'] = $item_array['parameters'];
                if ($item_array['type'] == 'hidden' || $item_array['type'] == 'primary_key') {
                    $elements['private'][$item_array['name']] = $rs;
                } else {
                    if ($rs['tab'] == '') {
                        $rs['tab'] = $default_tab_name;
                    }
                    if ($ignore_tabs) {
                        $elements['public'][$default_tab_name][$item_array['name']] = $rs;
                    } else {
                        $elements['public'][$rs['tab']][$item_array['name']] = $rs;
                    }
                }
                $elements['hash'][$item_array['name']] = $rs;
            }
        }

        $elements['scripts'] = array_unique($scripts);
        return $elements;
    }

    /**
     * Compile form inputs
     * @param $form_data form data
     * @return string
     */
    function compile_form($form_data, $ignore_tabs = false)
    {
        $Sitebill_Registry = Sitebill_Registry::getInstance();


        $elements[] = array();
        $default_tab_name = $this->getConfigValue('default_tab_name');
        $tabs = array();
        $tabs[$default_tab_name] = $default_tab_name;

        foreach ($form_data as $item_id => $item_array) {
            $rs = '';
            //echo "type = {$item_array['type']}, name = {$item_array['name']}<br>";
            if (!isset($item_array['type'])) {
                $item_array['type'] = '';
            }
            switch ($item_array['type']) {

                case 'langselect':
                {
                    $rs = $this->get_langselect($item_array);
                    break;
                }
                case 'price':
                    $rs = $this->get_price_input($item_array);
                    break;
                case 'tlocation':
                    $rs = $this->get_tlocation($item_array);
                    break;
                case 'select_box':
                    $rs = $this->get_select_box_row($item_array);
                    break;

                case 'email':
                    $rs = $this->get_email_input($item_array);
                    break;

                case 'mobilephone':
                    $rs = $this->get_mobilephone_input($item_array);
                    break;

                case 'select_by_query':
                    $rs = $this->get_select_box_by_query_row($item_array);
                    break;

                case 'select_by_query_multiple':
                    $rs = $this->get_select_box_by_query_multiple_row($item_array);
                    break;

                case 'select_box_structure':
                    $rs = $this->get_select_box_structure_row($item_array);
                    break;

                case 'structure':
                    $rs = $this->get_structure_row($item_array);
                    break;

                case 'select_box_structure_simple_multiple':
                    $rs = $this->get_select_box_structure_simple_multiple_row($item_array);
                    break;

                case 'shop_select_box_structure':
                    $rs = $this->get_shop_select_box_structure_row($item_array);
                    break;

                case 'service_type_select_box_structure':
                    {
                        $rs = $this->get_service_type_select_box_structure_row($item_array);
                    }
                    break;
                /*
                  case 'uploader':
                  $rs .= $this->get_uploader_row($item_array);
                  break;

                  case 'pluploader':
                  $rs .= $this->get_pluploader_row($item_array);
                  break;
                 */
                case 'uploadify_image':
                    switch ($this->getConfigValue('uploader_type')) {
                        case 'pluploader' :
                        {
                            $rs = $this->get_pluploader_row($item_array);
                            break;
                        }
                        default :
                        {
                            $rs = $this->get_uploadify_row($item_array);
                        }
                    }

                    break;

                case 'uploadify_file':
                    $rs = $this->get_uploadify_file_row($item_array);
                    break;

                case 'separator':
                    $rs = $this->get_separator_row($item_array);
                    break;

                case 'checkbox':
                    $rs = $this->get_checkbox_box_row($item_array);
                    break;

                case 'textarea':
                    $rs = $this->get_textarea_row($item_array);
                    break;

                case 'textarea_editor':
                    $rs = $this->get_textarea_editor_row($item_array);
                    break;

                case 'grade':
                    $rs = $this->get_grade_row($item_array);
                    break;

                case 'date':
                    $rs = $this->get_date_input($item_array);
                    break;

                case 'auto_add_value':
                    $rs = $this->get_safe_text_input($item_array);
                    break;

                case 'safe_string':
                    $rs = $this->get_safe_text_input($item_array);
                    break;

                case 'geodata':
                    $rs = $this->get_geodata_input($item_array);
                    break;

                case 'password':
                    $rs = $this->get_password_input($item_array);
                    break;

                case 'photo':
                    $rs = $this->get_photo_input($item_array);
                    break;

                case 'captcha':
                    $rs = $this->get_captcha_input($item_array);
                    break;

                case 'spacer_text':
                    $rs = $this->get_spacer_text($item_array);
                    break;

                case 'hidden':
                    $rs = $this->get_hidden_input($item_array);
                    break;

                case 'values_list':
                    $rs = $this->get_safe_text_input($item_array);
                    break;

                case 'injector':
                    $rs = $this->get_injector_row($item_array);
                    break;
            }


            // echo $default_tab_name;


            if (isset($item_array['tab']) && $item_array['tab'] != '') {
                $tabs[$item_array['tab']] = $item_array['tab'];
                if ($rs != '') {
                    $elements[$item_array['tab']][] = $rs;
                }
            } else {
                if ($rs != '') {
                    $elements[$default_tab_name][] = $rs;
                }
            }
        }
        $rt = '';

        if ($Sitebill_Registry->getFeedback('divide_step_form')) {
            $tabs_count = count($tabs);
            $current_step = $Sitebill_Registry->getFeedback('step');
            $Sitebill_Registry->addFeedback('steps', $tabs_count);
            if ($tabs_count > 1) {
                $tabs_names = array_keys($tabs);
            }
            $tabs_names = array_keys($tabs);

            $rt .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/system/js/form_tabs.js"></script>';
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/css/form_tabs.css')) {
                $rt .= '<link rel="stylesheet" type="text/css" href="' . SITEBILL_MAIN_URL . '/template/frontend/' . $this->getConfigValue('theme') . '/css/form_tabs.css" />';
            } else {
                $rt .= '<link rel="stylesheet" type="text/css" href="' . SITEBILL_MAIN_URL . '/apps/system/css/form_tabs.css" />';
            }

            $rt .= '<tbody id="form_tab_switcher" style="display:none;">';
            $rt .= '<tr colspan="2"><td>';
            $ti = 1;

            foreach ($tabs as $tab) {
                if ($ti > $current_step) {
                    $rt .= '<span>' . $tab . '</span>';
                } elseif ($ti == $current_step) {
                    $rt .= '<a href="' . md5($tab) . '" class="active_tab">' . $tab . '</a>';
                } else {
                    $rt .= '<a href="' . md5($tab) . '">' . $tab . '</a>';
                }

                $ti++;
            }
            $rt .= '</td></tr></tbody>';

            $ti = 1;
            foreach ($tabs as $tab) {
                if ($ti > $tabs_count) {
                    break;
                }
                if ($ti == $current_step) {
                    $rt .= '<tbody class="form_tab" id="' . md5($tab) . '">';
                    $rt .= '<tr colspan="2"><td>' . $tab . '</td></tr>';
                    if (count($elements[$tab]) > 0) {
                        foreach ($elements[$tab] as $el) {
                            $rt .= $el;
                        }
                    }
                    $rt .= '</tbody>';
                } else {
                    $rt .= '<tbody class="form_tab">';
                    $rt .= '<tr colspan="2"><td>' . $tab . '</td></tr>';
                    if (count($elements[$tab]) > 0) {
                        foreach ($elements[$tab] as $el) {
                            $rt .= $el;
                        }
                    }
                    $rt .= '</tbody>';
                }


                $ti++;
            }
        } elseif (count($tabs) > 1 && !$ignore_tabs) {

            $rt .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/system/js/form_tabs.js"></script>';
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/css/form_tabs.css')) {
                $rt .= '<link rel="stylesheet" type="text/css" href="' . SITEBILL_MAIN_URL . '/template/frontend/' . $this->getConfigValue('theme') . '/css/form_tabs.css" />';
            } else {
                $rt .= '<link rel="stylesheet" type="text/css" href="' . SITEBILL_MAIN_URL . '/apps/system/css/form_tabs.css" />';
            }
            $rt .= '<tbody id="form_tab_switcher">';
            $rt .= '<tr colspan="2"><td>';
            foreach ($tabs as $tab) {
                $rt .= '<a href="' . md5($tab) . '">' . $tab . '</a>';
            }
            $rt .= '</td></tr></tbody>';

            foreach ($tabs as $tab) {
                $rt .= '<tbody class="form_tab" id="' . md5($tab) . '">';
                $rt .= '<tr colspan="2"><td>' . $tab . '</td></tr>';
                if (count($elements[$tab]) > 0) {
                    foreach ($elements[$tab] as $el) {
                        //echo $el;
                        $rt .= $el;
                    }
                }
                $rt .= '</tbody>';
            }
        } elseif (count($tabs) > 1) {
            foreach ($tabs as $tab) {
                if (count($elements[$tab]) > 0) {
                    foreach ($elements[$tab] as $el) {
                        $rt .= $el;
                    }
                }
            }
        } else {
            if (is_array($elements[$default_tab_name]) && count($elements[$default_tab_name]) > 0) {
                foreach ($elements[$default_tab_name] as $el) {
                    $rt .= $el;
                }
            }
        }
        return $rt;
        //return $rs;
    }

}
