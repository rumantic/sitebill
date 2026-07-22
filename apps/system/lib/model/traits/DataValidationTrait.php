<?php
/**
 * DataValidationTrait — extracted from Data_Model class (model.php)
 * Auto-generated, do not edit manually.
 */
trait DataValidationTrait
{
    /**
     * Check data
     * @param array $model_array
     * @return boolean
     */
    function check_data($model_array, &$error_fields = array())
    {
        $errors = array();

        $agreement_el = intval($this->getRequestValue('agreement_el'));
        $agreement = intval($this->getRequestValue('agreement'));
        if ($agreement_el && !$agreement) {
            $errors[] = Multilanguage::_('L_ERROR_AGREEMENT');
        }

        /*
        $session_key = (string)$this->get_session_key();
        $fieldswithuploads = array();
        foreach ($model_array as $form_item) {
            if ($form_item['type'] == 'uploads') {
                if(isset($form_item['parameters']['min_img_count']) && 0 < intval($form_item['parameters']['min_img_count'])){
                    $fieldswithuploads[$form_item['name']] = intval($form_item['parameters']['min_img_count']);
                }

                if(!empty($fieldswithuploads)){
                    foreach ($fieldswithuploads as $fname => $mincount){
                        $ims = $this->load_uploadify_images($session_key, $fname);
                        if(!is_array($ims) || $mincount > count($ims)){
                            $errors[] = 'Согласно правилам сайта, необходимо добавить не менее '.$mincount.' фотографий';
                            $error_fields[$fname][] = 'Согласно правилам сайта, необходимо добавить не менее '.$mincount.' фотографий';
                        }
                    }
                }
            }
        }
        */
        foreach ($model_array as $key => $item_array) {

            $isUnique = false;
            if ($item_array['unique'] == 'on' || $item_array['unique'] == '1') {
                $isUnique = true;
            }

            if ($isUnique && $item_array['value'] != '') {
                $DBC = DBC::getInstance();
                $tname = $item_array['table_name'];
                $query = 'SELECT name FROM ' . DB_PREFIX . '_columns WHERE `type`=? AND table_id=(SELECT table_id FROM ' . DB_PREFIX . '_table WHERE name=? LIMIT 1)';
                $stmt = $DBC->query($query, array('primary_key', $tname));
                $primary_key_value = 0;
                $primary_key_name = '';
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    $primary_key_name = $ar['name'];
                    $primary_key_value = intval($model_array[$primary_key_name]['value']);
                }
            }

            if (isset($item_array['parameters'])) {
                $parameters = $item_array['parameters'];
            } else {
                $parameters = array();
            }

            $is_field_required = false;
            if ($model_array[$key]['required'] == 'on') {
                $is_field_required = true;
            }

            $req_off = array();
            if (isset($item_array['parameters']['reqoff']) && $item_array['parameters']['reqoff'] != '') {
                $ro = $item_array['parameters']['reqoff'];
                list($field, $vals) = explode(':', $ro);
                if ($field && $vals) {
                    $vals_array = explode(',', $vals);
                    $req_off[$field] = array();
                    foreach ($vals_array as $vals1) {
                        list($start, $end) = explode('-', $vals1);
                        if ($start !== null && $end !== null) {
                            $mar = range($start, $end);
                            if (is_array($mar)) {
                                $req_off[$field] = array_merge($req_off[$field], $mar);
                            }
                        } else {
                            $req_off[$field][] = trim($vals1);
                        }
                    }
                }
            }

            if (!empty($req_off)) {
                foreach ($req_off as $field => $vals) {
                    $cval = $model_array[$field]['value'];
                    if (in_array($cval, $vals)) {
                        $is_field_required = false;
                    }
                }
            }

            $req_off = array();
            if (isset($item_array['parameters']['reqoff_cond']) && $item_array['parameters']['reqoff_cond'] != '') {
                $ro = $item_array['parameters']['reqoff_cond'];
                $ro_list = explode('|', $ro);
                if (!empty($ro_list)) {
                    foreach ($ro_list as $ro_variant) {
                        list($field, $vals) = explode('=', $ro_variant);
                        if ($field && $vals) {
                            $req_off[] = array($field, $vals);
                        }
                    }
                }
            }

            if ($is_field_required && !empty($req_off)) {
                $s = 1;
                foreach ($req_off as $v) {
                    if ($model_array[$v[0]]['value'] == $v[1]) {
                        $s *= 1;
                    } else {
                        $s *= 0;
                        break;
                    }
                }

                if ($s == 1) {
                    $is_field_required = false;
                }
            }

            /*$validations = array();
            if (isset($item_array['parameters']['validation']) && $item_array['parameters']['validation'] != '') {
                $validations = explode('|', $item_array['parameters']['validation']);


                // accepted
            }*/

            $rules = array();
            if (isset($item_array['parameters']['rules']) && $item_array['parameters']['rules'] != '') {
                $rules_string = $item_array['parameters']['rules'];

                $rules_parts = explode(',', $rules_string);
                foreach ($rules_parts as $r => $rp) {
                    $rules_parts[$r] = trim($rp);
                }


                foreach ($rules_parts as $rp) {
                    $x = explode(':', $rp);
                    $rules[trim($x[0])] = (isset($x[1]) ? trim($x[1]) : '');
                }

                if (!isset($rules['Type'])) {
                    $rules['Type'] = 'string';
                }

                if (isset($rules['NotBlank']) && $model_array[$key]['value'] === '') {
                    $errors[] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                    $error_fields[$key][] = Multilanguage::_('L_ERROR_FIELD_NOT_FILLED') . ' ' . $model_array[$key]['title'];
                }


                switch ($rules['Type']) {
                    case 'string' :
                    {
                        $compare_text = strip_tags($model_array[$key]['value']);
                        $compare_text = str_replace(array("\n", "\r"), '', $compare_text);
                        if (isset($rules['MinLength']) && $rules['MinLength'] !== '') {
                            $min_l = (int)$rules['MinLength'];
                            $compare_text = strip_tags($model_array[$key]['value']);
                            $compare_text = str_replace(array("\n", "\r"), '', $compare_text);
                            if (mb_strlen($compare_text, SITE_ENCODING) < $min_l) {
                                $errors[] = sprintf(Multilanguage::_('L_ERROR_MINLENGTH'), $model_array[$key]['title'], $min_l);
                                $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_MINLENGTH'), $model_array[$key]['title'], $min_l);
                            }
                        }
                        if (isset($rules['MaxLength']) && $rules['MaxLength'] !== '') {
                            $max_l = (int)$rules['MaxLength'];
                            if (mb_strlen($compare_text, SITE_ENCODING) > $max_l) {
                                $errors[] = sprintf(Multilanguage::_('L_ERROR_MAXLENGTH'), $model_array[$key]['title'], $max_l);
                                $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_MAXLENGTH'), $model_array[$key]['title'], $max_l);
                            }
                        }
                        if (isset($rules['Email']) && $model_array[$key]['value'] != '' && !$this->validateEmailFormat($model_array[$key]['value'])) {
                            $errors[] = Multilanguage::_('L_ERROR_EMAIL_FORMAT_INVALID') . ' ' . $model_array[$key]['title'];
                            $error_fields[$key][] = Multilanguage::_('L_ERROR_EMAIL_FORMAT_INVALID') . ' ' . $model_array[$key]['title'];
                        }
                        break;
                    }
                    case 'numeric' :
                    {

                        if ($model_array[$key]['value'] !== '' && preg_match('/([^0-9])/', $model_array[$key]['value'])) {
                            $errors[] = sprintf(Multilanguage::_('L_ERROR_VALUE_FORMAT_INVALID_NUM'), $model_array[$key]['title']);
                            $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_VALUE_FORMAT_INVALID_NUM'), $model_array[$key]['title']);
                        }
                        if (isset($rules['MinLength']) && $rules['MinLength'] !== '') {
                            $min = (int)$rules['MinLength'];
                            if ($model_array[$key]['value'] != '' && strlen($model_array[$key]['value']) < $min) {
                                $errors[] = sprintf(Multilanguage::_('L_ERROR_MINLENGTH'), $model_array[$key]['title'], $min);
                                $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_MINLENGTH'), $model_array[$key]['title'], $min);
                            }
                        }
                        if (isset($rules['MaxLength']) && $rules['MaxLength'] !== '') {
                            $max = (int)$rules['MaxLength'];
                            if ($model_array[$key]['value'] != '' && strlen($model_array[$key]['value']) > $max) {
                                $errors[] = sprintf(Multilanguage::_('L_ERROR_MAXLENGTH'), $model_array[$key]['title'], $max);
                                $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_MAXLENGTH'), $model_array[$key]['title'], $max);
                            }
                        }
                        break;
                    }
                    case 'int' :
                    {

                        if ($model_array[$key]['value'] !== '' && !preg_match('/^[-+]?[0-9]*$/', $model_array[$key]['value'])) {
                            $errors[] = sprintf(Multilanguage::_('L_ERROR_VALUE_FORMAT_INVALID_INT'), $model_array[$key]['title']);
                            $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_VALUE_FORMAT_INVALID_INT'), $model_array[$key]['title']);
                        }
                        if (isset($rules['Min']) && $rules['Min'] !== '') {
                            $min = (int)$rules['Min'];
                            if ((int)$model_array[$key]['value'] != 0 && (int)$model_array[$key]['value'] < $min) {
                                $errors[] = sprintf(Multilanguage::_('L_ERROR_VALUE_CANTBE_LESS'), $model_array[$key]['title'], $min);
                                $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_VALUE_CANTBE_LESS'), $model_array[$key]['title'], $min);
                            }
                        }
                        if (isset($rules['Max']) && $rules['Max'] !== '') {
                            $max = (int)$rules['Max'];
                            if ((int)$model_array[$key]['value'] > $max) {
                                $errors[] = 'Значения поля ' . $model_array[$key]['title'] . ' не может быть больше ' . $max;
                                $error_fields[$key][] = 'Значения поля ' . $model_array[$key]['title'] . ' не может быть больше ' . $max;
                            }
                        }
                        break;
                    }
                    case 'decimal' :
                    {

                        if ($model_array[$key]['value'] !== '' && !preg_match('/^[-+]?[0-9]*[.]?[0-9]+$/', $model_array[$key]['value'])) {
                            $errors[] = sprintf(Multilanguage::_('L_ERROR_VALUE_FORMAT_INVALID_DEC'), $model_array[$key]['title']);
                            $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_VALUE_FORMAT_INVALID_DEC'), $model_array[$key]['title']);
                        }
                        if (isset($rules['Min']) && $rules['Min'] !== '') {
                            $min = (float)$rules['Min'];
                            //echo $min;
                            if (trim($model_array[$key]['value']) != '' && (float)$model_array[$key]['value'] < $min) {
                                $errors[] = sprintf(Multilanguage::_('L_ERROR_VALUE_CANTBE_LESS'), $model_array[$key]['title'], $min);
                                $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_VALUE_CANTBE_LESS'), $model_array[$key]['title'], $min);
                            }
                        }
                        if (isset($rules['Max']) && $rules['Max'] !== '') {
                            $max = (float)$rules['Max'];
                            if ((float)$model_array[$key]['value'] > $max) {
                                $errors[] = 'Значения поля ' . $model_array[$key]['title'] . ' не может быть больше ' . $max;
                                $error_fields[$key][] = 'Значения поля ' . $model_array[$key]['title'] . ' не может быть больше ' . $max;
                            }
                        }
                        break;
                    }
                    case 'email' :
                    {
                        if ($model_array[$key]['value'] !== '' && !filter_var($model_array[$key]['value'], FILTER_VALIDATE_EMAIL)) {
                            $errors[] = sprintf(Multilanguage::_('L_ERROR_VALUE_FORMAT_INVALID_EMAIL'), $model_array[$key]['title']);
                            $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_VALUE_FORMAT_INVALID_EMAIL'), $model_array[$key]['title']);
                        }
                        break;
                    }
                }
            }


            if ($model_array[$key]['type'] == 'safe_string' || $model_array[$key]['type'] == 'textarea') {
                if ($is_field_required && $model_array[$key]['value'] == '') {
                    //$this->riseError(sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']));
                    $errors[] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                    $error_fields[$key][] = Multilanguage::_('L_ERROR_FIELD_NOT_FILLED') . ' ' . $model_array[$key]['title'];
                }
            } elseif ($model_array[$key]['type'] == 'captcha') {
                $captcha_type = $this->getConfigValue('captcha_type');
                if ($captcha_type == 2) {

                } elseif ($captcha_type == 4) {
                    $recaptcha_token = $this->getRequestValue('g-recaptcha-response');

                    $url = 'https://www.google.com/recaptcha/api/siteverify';
                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $url);
                    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, "secret=6LfB4TgUAAAAALr-PM6PzvF5Hi5vXLQM93jpGHlJ&response=" . $recaptcha_token);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    $result = curl_exec($curl);
                    curl_close($curl);
                    $resp = json_decode($result, true);
                    if (!isset($resp['success']) || !$resp['success']) {
                        $errors[] = Multilanguage::_('L_ERROR_RECAPTCHA');
                        //$errors[] = Multilanguage::_('L_ERROR_CAPTCHA_INVALID');
                        $error_fields[$key][] = Multilanguage::_('L_ERROR_RECAPTCHA');
                    }
                } else {
                    if ($model_array[$key]['value'] == '' || $model_array[$key]['value'] != $_SESSION[$this->getRequestValue('captcha_session_key')]) {
                        //$this->riseError(Multilanguage::_('L_ERROR_CAPTCHA_INVALID'));
                        $errors[] = Multilanguage::_('L_ERROR_CAPTCHA_INVALID');
                        $error_fields[$key][] = Multilanguage::_('L_ERROR_CAPTCHA_INVALID');
                        //return false;
                    }
                }
            } elseif ($model_array[$key]['type'] == 'email') {
                if ($is_field_required && $model_array[$key]['value'] == '') {
                    $errors[] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                    $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                }
                if ($model_array[$key]['value'] != '' && !$this->validateEmailFormat($model_array[$key]['value'])) {
                    $errors[] = Multilanguage::_('L_ERROR_EMAIL_FORMAT_INVALID') . ' ' . $model_array[$key]['title'];
                    $error_fields[$key][] = Multilanguage::_('L_ERROR_EMAIL_FORMAT_INVALID') . ' ' . $model_array[$key]['title'];
                }
            } elseif ($model_array[$key]['type'] == 'mobilephone') {
                if ($is_field_required && $model_array[$key]['value'] == '') {
                    $this->riseError(sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']));
                    $errors[] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                    $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                }

                $mask = '';
                if ( $this->getConfigValue('apps.realty.mobilephone_old_mask') ) {
                    if (isset($parameters['mask'])) {
                        $mask = $parameters['mask'];
                        $mask = preg_replace('/[^h\d]/', '', $mask);
                        if ($mask != '') {
                            $mask = str_replace('h', '\d', $mask);
                        } else {
                            $mask = '';
                        }
                    }
                }

                if (($model_array[$key]['value'] != '') && (!$this->validateMobilePhoneNumberFormat($model_array[$key]['value'], $mask))) {
                    $errors[] = Multilanguage::_('L_ERROR_PHONE_FORMAT_INVALID') . ' ' . $model_array[$key]['title'];
                    $error_fields[$key][] = Multilanguage::_('L_ERROR_PHONE_FORMAT_INVALID') . ' ' . $model_array[$key]['title'];
                }
            } elseif ($model_array[$key]['type'] == 'select_box_structure_simple_multiple') {
                if ($is_field_required && count($model_array[$key]['values_array']) == 0) {
                    $errors[] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                    $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                }
            } elseif ($model_array[$key]['type'] == 'select_box_structure') {
                if ($is_field_required and $model_array[$key]['value'] == 0) {
                    $errors[] = _e('Укажите тип недвижимости') . ': ' . $model_array[$key]['title'];
                    $error_fields[$key][] = _e('Укажите тип недвижимости') . ': ' . $model_array[$key]['title'];
                }
                if (@$parameters['level_required'] > 0) {
                    if (($model_array[$key]['value'] != '') && (!$this->validateLevelRequired($model_array[$key]['value'], $parameters['level_required']))) {
                        $errors[] = _e('Укажите подтип недвижимости') . ': ' . $model_array[$key]['title'];
                        $error_fields[$key][] = _e('Укажите подтип недвижимости') . ': ' . $model_array[$key]['title'];
                    }
                }

            } elseif ($model_array[$key]['type'] == 'select_by_query_multiple') {
                if ($is_field_required && count($model_array[$key]['values_array']) == 0) {
                    $errors[] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                    $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                }
            } elseif ($model_array[$key]['type'] == 'dtdatetime') {
                if ($is_field_required && $model_array[$key]['value'] !== '' && !Sitebill_Datetime::checkDTDatetime($model_array[$key]['value'], $model_array[$key]['parameters'])) {
                    $errors[] = 'Invalid date format on field ' . $model_array[$key]['title'];
                } elseif ($is_field_required && $model_array[$key]['value'] === '') {
                    $errors[] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                    $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                }
            } elseif ($model_array[$key]['type'] == 'dtdate') {
                if ($is_field_required && $model_array[$key]['value'] !== '' && !Sitebill_Datetime::checkDTDatetime($model_array[$key]['value'], $model_array[$key]['parameters'])) {
                    $errors[] = 'Invalid date format on field ' . $model_array[$key]['title'];
                } elseif ($is_field_required && $model_array[$key]['value'] === '') {
                    $errors[] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                    $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                }
            } elseif ($model_array[$key]['type'] == 'dttime') {
                if ($is_field_required && $model_array[$key]['value'] !== '' && !Sitebill_Datetime::checkDTTime($model_array[$key]['value'], $model_array[$key]['parameters'])) {
                    $errors[] = 'Invalid date format on field ' . $model_array[$key]['title'];
                } elseif ($is_field_required && $model_array[$key]['value'] === '') {
                    $errors[] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                    $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                }
            } else {
                if ($is_field_required) {
                    if (!is_array($model_array[$key]['value'])) {
                        if (!preg_match('/.+/', $model_array[$key]['value']) || preg_match('/^[0]$/', $model_array[$key]['value'])) {
                            $errors[] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                            $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                        }
                    } elseif (empty($model_array[$key]['value'])) {
                        $errors[] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                        $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                    } else {
                        $values = $model_array[$key]['value'];
                        foreach ($values as $value) {
                            if (!preg_match('/.+/', $value) || preg_match('/^[0]$/', $value)) {
                                $errors[] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                                $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                            }
                        }
                    }
                }
            }

            if ($isUnique && $item_array['value'] != '' && !$this->checkIsValueUnique($item_array['value'], $item_array['name'], $item_array['table_name'], $primary_key_name, $primary_key_value)) {

                $errors[] = _e('Значение поля не уникально: ') . ' ' . $model_array[$key]['title'];
                $error_fields[$key][] = _e('Значение поля не уникально: ') . ' ' . $model_array[$key]['title'];
            }
        }
        if (!empty($errors)) {
            $this->riseError(implode('<br />', $errors));
            return false;
        }
        return true;
    }

    function forse_auto_add_values(&$model_array)
    {
        foreach ($model_array as $key => $item_array) {
            if ($item_array['type'] == 'auto_add_value' and $item_array['value'] != '') {
                $id = $this->get_value_id_by_name($item_array['value_table'], $item_array['value_field'], $item_array['value_primary_key'], strip_tags($item_array['value']));
                if ($id === FALSE) {
                    $id = 0;
                    $DBC = DBC::getInstance();
                    $query = 'INSERT INTO ' . DB_PREFIX . '_' . $item_array['value_table'] . ' (`' . $item_array['value_field'] . '`) VALUES (?)';
                    $stmt = $DBC->query($query, array(strip_tags($item_array['value'])));
                    if ($stmt) {
                        $id = $DBC->lastInsertId();
                    }

                    if ($id != 0) {
                        $model_array[$item_array['assign_to']]['value'] = $id;
                    }
                } else {
                    $model_array[$item_array['assign_to']]['value'] = $id;
                }
            }
        }
    }

    function forse_autocalc_values(&$model_array)
    {
    }

    function forse_injected_values(&$model_array)
    {
    }

    function checkIsValueUnique($value, $field, $table, $primary_key_name, $primary_key_value)
    {
        $DBC = DBC::getInstance();

        if ($primary_key_value != 0) {
            $query = 'SELECT COUNT(*) AS _c FROM ' . DB_PREFIX . '_' . $table . ' WHERE `' . $field . '`=? AND `' . $primary_key_name . '`!=?';
            $stmt = $DBC->query($query, array($value, $primary_key_value));
        } else {
            $query = 'SELECT COUNT(*) AS _c FROM ' . DB_PREFIX . '_' . $table . ' WHERE `' . $field . '`=?';
            $stmt = $DBC->query($query, array($value));
        }

        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($ar['_c'] > 0) {
                return false;
            }
        }
        return true;
    }

    private function validateLevelRequired($value, $level_required)
    {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $SM = new Structure_Manager();
        $category_structure = $SM->loadCategoryStructure();
        if ($category_structure['catalog'][$value]['parent_id'] == 0 and $level_required > 0) {
            return false;
        }
        return true;
    }

    /**
     * Извлекает списки свойств необходимых для сборки списка моделей объектов
     * @param array $model_array
     * @return mixed
     */
    private function extractModelFields($model_array){

        $uselangs = false;
        if (1 === intval($this->getConfigValue('apps.language.use_langs'))) {
            $uselangs = true;
            $postfix = $this->getLangPostfix($this->getCurrentLang());
        }

        $fields = [];
        $select_by_query = [];
        $has_multi_elements = [];
        $structures = [];
        $hasclients = false;
        $has_uploadify_image = false;



        foreach ($model_array as $model_item) {
            if ($model_item['dbtype'] == 'notable' && $model_item['type'] != 'select_by_query_multi') {
                continue;
            }
            if (!isset($model_item['type'])) {
                continue;
            }
            switch ($model_item['type']) {
                case 'safe_string' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'youtube' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'hidden' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'mobilephone' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'checkbox' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'select_box_structure' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'select_by_query' :
                {
                    $parameters = @$model_item['parameters'];
                    $fname = $model_item['value_name'];
                    $no_ml = (isset($parameters['no_ml']) ? intval($parameters['no_ml']) : 0);
                    if ($uselangs && 0 === $no_ml) {
                        $fname .= $postfix;
                    }
                    $select_by_query[$model_item['name']] = array(
                        'primary_key_table' => $model_item['primary_key_table'],
                        'primary_key_name' => $model_item['primary_key_name'],
                        'value_name' => $fname,
                        '_value_name' => $model_item['value_name']
                    );

                    $fields[] = $model_item['name'];
                    break;
                }
                case 'client_id' : {
                    $hasclients = true;
                    $fields[] = $model_item['name'];
                }
                case 'select_box' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'auto_add_value' :
                {
                    //$fields[]=$model_item['name'];
                    break;
                }
                case 'price' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'textarea' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'uploadify_image' :
                {
                    //$fields[]=$model_item['name'];
                    $has_uploadify_image = true;
                    break;
                }
                case 'uploadify_file' :
                {
                    //$fields[]=$model_item['name'];
                    break;
                }
                case 'select_by_query_multi' :
                {
                    $has_multi_elements[$model_item['name']] = $model_item['name'];
                    break;
                }
                case 'password' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'photo' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'geodata' :
                {
                    $fields[] = $model_item['name'] . '_lat';
                    $fields[] = $model_item['name'] . '_lng';
                    break;
                }
                case 'structure' :
                {
                    $fields[] = $model_item['name'];
                    $structures[$model_item['entity']] = $model_item['entity'];
                    break;
                }
                case 'textarea_editor' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'date' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'attachment' :
                {
                    //$fields[]=$model_item['name'];
                    break;
                }
                case 'tlocation' :
                {
                    $select_by_query['country_id'] = array(
                        'primary_key_table' => 'country',
                        'primary_key_name' => 'country_id',
                        'value_name' => 'name'
                    );
                    $select_by_query['region_id'] = array(
                        'primary_key_table' => 'region',
                        'primary_key_name' => 'region_id',
                        'value_name' => 'name'
                    );
                    $select_by_query['city_id'] = array(
                        'primary_key_table' => 'city',
                        'primary_key_name' => 'city_id',
                        'value_name' => 'name'
                    );
                    $select_by_query['district_id'] = array(
                        'primary_key_table' => 'district',
                        'primary_key_name' => 'id',
                        'value_name' => 'name'
                    );
                    $select_by_query['street_id'] = array(
                        'primary_key_table' => 'street',
                        'primary_key_name' => 'street_id',
                        'value_name' => 'name'
                    );

                    $fields[] = 'country_id';
                    $fields[] = 'region_id';
                    $fields[] = 'city_id';
                    $fields[] = 'district_id';
                    $fields[] = 'street_id';
                    break;
                }
                case 'captcha' :
                {
                    //$fields[]=$model_item['name'];
                    break;
                }
                case 'dtdatetime' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'dtdate' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'dttime' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'uploads' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'gadres' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'grade' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'separator' :
                {
                    //$fields[]=$model_item['name'];
                    break;
                }
                case 'spacer_text' :
                {
                    //$fields[]=$model_item['name'];
                    break;
                }
                case 'primary_key' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'values_list' :
                {
                    $fields[] = $model_item['name'];
                    //$this->get_values_list($model_array[$key]['what'], $model_array[$key]['primary_table'], $model_array[$key]['primary_key'], $model_array[$key]['secondary_table'], $model_array[$key]['secondary_key'], $primary_key_value);
                    break;
                }
                case 'parameter' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'select_box_structure_multiple_checkbox' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                default :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
            }
        }

        return compact(['fields', 'select_by_query', 'has_multi_elements', 'structures', 'hasclients', 'has_uploadify_image']);

    }

}
