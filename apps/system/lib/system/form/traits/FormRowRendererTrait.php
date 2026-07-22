<?php
trait FormRowRendererTrait
{
    /**
     * Get spacer text
     * @param array $item_array
     * @return string
     */
    function get_spacer_text($item_array)
    {
        $string = '';
        $string .= "<tr>\n";
        $string .= '<td>';
        $string .= $item_array['title'];
        $string .= '</td>';
        $string .= "<td colspan=\"2\">" . $item_array['value'] . "</td>\n";
        $string .= "</tr>\n";
        return $string;
    }

    /**
     * Get error message row
     * @param string $error_message
     * @return string
     */
    function get_error_message_row($error_message)
    {
        //$rs = '<tr>';
        //$rs .= '<td colspan="2">';
        $rs = '<div class="alert alert-error alert-danger">' . $error_message . '</div>';
        //$rs .= '</td>';
        //$rs .= '</tr>';
        return $rs;
    }

    /**
     * Get select box row
     * @param array $item_array
     * @return string
     */
    function get_select_box_by_query_row($item_array)
    {
        $rs = '<tr  class="row3"  alt="' . $item_array['name'] . '">';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        if ($item_array['required'] == "on") {
            $rs .= " <span style=\"color: red;\">*</span> \n";
        }
        $rs .= '</td>';
        $rs .= '<td>';
        $rs .= $this->get_single_select_box_by_query($item_array);
        $rs .= '</td>';
        $rs .= '</tr>';

        return $rs;
    }

    /**
     * Get select box row
     * @param array $item_array
     * @return string
     */
    function get_select_box_by_query_multiple_row($item_array)
    {
        $rs = '<tr  class="row3"  alt="' . $item_array['name'] . '">';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        if ($item_array['required'] == "on") {
            $rs .= " <span style=\"color: red;\">*</span> \n";
        }
        $rs .= '</td>';
        $rs .= '<td>';
        $rs .= $this->get_single_select_box_by_query_multiple($item_array);
        $rs .= '</td>';
        $rs .= '</tr>';

        return $rs;
    }

    /**
     * Get total in select
     * @param string $key
     * @return int
     */
    function get_total_in_select($key)
    {
        return $this->total_in_select[$key];
    }

    /**
     * Get single select box by query
     * @param array $item_array
     * @return string
     */
    function get_single_select_box_by_query($item_array, $model = null)
    {
        if (defined('VUE_ENABLED') and VUE_ENABLED == true) {
            $rs = '<select-by-query 
                    column_name="' . $item_array['name'] . '" 
                    model_name="' . $item_array['table_name'] . '"
                    placeholder="' . $item_array['title_default'] . '"
                    value="' . $item_array['value'] . '"
                    >
                   </select-by-query>';
            return $rs;
        }

        $lang = $this->getCurrentLang();
        $item_md5 = md5(serialize($item_array) . $lang);
        if (isset(self::$cache[$item_md5])) {
            return self::$cache[$item_md5];
        }

        if (isset($item_array['parameters'])) {
            $parameters = $item_array['parameters'];
        } else {
            $parameters = array();
        }

        if (1 == intval($this->getConfigValue('apps.realty.off_system_ajax'))) {
            if (isset($parameters['linked']) && $parameters['linked'] != '') {
                $linked_elts_str = explode(';', $parameters['linked']);
            }

            $links = array();
            if (!empty($linked_elts_str)) {
                foreach ($linked_elts_str as $str) {
                    $x = explode(',', $str);
                    $links[] = array(
                        'linked_element' => trim($x[0]),
                        'linked_field' => trim($x[1])
                    );
                }
            }
            $depended_element_name = '';
            if (isset($parameters['depended']) && $parameters['depended'] != '') {
                $depended_element_name = trim($parameters['depended']);
                list($a, $b) = explode(',', $depended_element_name);
                if ($b != '') {
                    $depended_element_name = $a;
                    $depended_element_name_key = $b;
                } else {
                    $depended_element_name_key = $depended_element_name;
                }
            }
        } else {
        }


        $rs = '';

        if (isset($parameters['autocomplete']) && $parameters['autocomplete'] == 1) {
            $value = '';
            if ($item_array['value'] != '') {
                $value_name = $item_array['value_name'];
                $value_name_l = $item_array['value_name'];
                if (1 === intval($this->getConfigValue('apps.language.use_langs')) && 0 === intval($parameters['no_ml'])) {
                    $curlang = $this->getCurrentLang();
                    if (1 === intval($this->getConfigValue('apps.language.use_default_as_ru')) && $curlang == 'ru') {

                    } else {
                        /*if (isset($form_data_c[$item_array['primary_key_table']][$value_name . '_' . $lang])) {

                        }*/
                        $value_name_l = $value_name . '_' . $lang;
                    }
                }


                $DBC = DBC::getInstance();
                $query = 'SELECT `' . $value_name_l . '` FROM ' . DB_PREFIX . '_' . $item_array['primary_key_table'] . ' WHERE `' . $item_array['primary_key_name'] . '`=?';
                $stmt = $DBC->query($query, array($item_array['value']));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    $value = $ar[$value_name_l];
                }
            }
            $_no_insert = false;
            if (isset($parameters['autocomplete_notappend']) && 0 != (int)$parameters['autocomplete_notappend']) {
                $_no_insert = true;
            }


            $onchange = array();
            if (isset($links) && count($links) > 0) {
                foreach ($links as $lnks) {
                    $onchange[] = 'LinkedElements.refresh(this, \'' . $lnks['linked_element'] . '\', \'' . $lnks['linked_field'] . '\', \'' . $item_array['table_name'] . '\');';
                }
            }

            return '<div class="geoautocomplete_block"><input class="' . (isset($this->classes['input']) ? $this->classes['input'] : '') . ' geoautocomplete" type="text" placeholder="' . $item_array['title_default'] . '" ' . ($_no_insert ? '' : 'name="geoautocomplete[' . $item_array['name'] . ']"') . ' value="' . $value . '" pk="' . $item_array['primary_key_name'] . '" from="' . $item_array['primary_key_table'] . '" data-depel="' . (isset($parameters['autocomplete_dep_el']) ? $parameters['autocomplete_dep_el'] : '') . '" data-depelkey="' . (isset($parameters['autocomplete_dep_el_key']) ? $parameters['autocomplete_dep_el_key'] : '') . '"' . ($_no_insert ? ' data-notappend="true"' : '') . ' data-model="' . $item_array['table_name'] . '" /><input type="hidden" onchange="' . implode(' ', $onchange) . ' ' . '" name="' . $item_array['name'] . '" value="' . $item_array['value'] . '" /></div>';
        } elseif (1 == $this->getConfigValue('apps.realty.off_system_ajax')/* || 1==1 */) {
            $selected = '';
            $onchange = array();
            if (count($links) > 0) {
                foreach ($links as $lnks) {
                    $onchange[] = 'LinkedElements.refresh(this, \'' . $lnks['linked_element'] . '\', \'' . $lnks['linked_field'] . '\', \'' . $item_array['table_name'] . '\');';
                }
            }
            if (isset($item_array['onchange'])) {
                $onchange[] = $item_array['onchange'];
            }
            $this->total_in_select[$item_array['name']] = 0;
            $rs .= '<select class="' . $this->classes['select'] . '" name="' . $item_array['name'] . '" id="' . $item_array['name'] . '" onchange="' . implode(' ', $onchange) . ' ' . '"' . (isset($item_array['onclick']) ? ' onClick="' . $item_array['onclick'] . '"' : ' ') . '>';
            if ($lang != 'ru') {
                $lang_key = 'title_default_' . $lang;
                if ($item_array[$lang_key] != '') {
                    $item_array['title_default'] = $item_array[$lang_key];
                }
            }
            $rs .= '<option value="' . $item_array['value_default'] . '" ' . $selected . '>' . $item_array['title_default'] . '</option>';
            //print_r($item_array);
            $DBC = DBC::getInstance();


            $value_name = $item_array['value_name'];
            $value_name_l = $item_array['value_name'];

            if (1 === intval($this->getConfigValue('apps.language.use_langs')) && (!isset($parameters['no_ml']) || 0 === intval($parameters['no_ml']))) {
                $curlang = $this->getCurrentLang();
                if (1 === intval($this->getConfigValue('apps.language.use_default_as_ru')) && $curlang == 'ru') {

                } else {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
                    $ATH = new Admin_Table_Helper();
                    $form_data_c = $ATH->load_model($item_array['primary_key_table'], false);
                    if (isset($form_data_c[$item_array['primary_key_table']][$value_name . '_' . $lang])) {
                        $value_name_l = $value_name . '_' . $lang;
                    }
                    //$value_name_l=$value_name.'_'.$lang;
                }
            }


            $ret = array();

            if ($depended_element_name != '') {
                $depended_value = $model[$depended_element_name]['value'];
                if (isset($parameters['use_query']) && $parameters['use_query'] != '') {
                    $query = $parameters['use_query'];
                    if ($_REQUEST['debug'] == 1) var_dump($query);
                    $stmt = $DBC->query($query, array($value));
                    if ($stmt) {
                        while ($ar = $DBC->fetch($stmt)) {
                            if ($ar[$depended_element_name] == $depended_value) {
                                $ret[] = array($item_array['primary_key_name'] => $ar[$item_array['primary_key_name']], $value_name => $ar[$value_name]);
                            }
                        }
                    }
                } else {
                    if ((int)$depended_value != 0) {
                        $query = 'SELECT `' . $item_array['primary_key_name'] . '`, `' . $value_name_l . '` AS ' . $value_name . ' FROM ' . DB_PREFIX . '_' . $item_array['primary_key_table'] . ' WHERE `' . $depended_element_name . '`=?' . ($parameters['addwhere'] > '' ? ' and ' . $parameters['addwhere'] : '');

                        $sorts = array();
                        if (isset($parameters['sort']) && $parameters['sort'] != '') {
                            if (isset($parameters['sort_dir']) && $parameters['sort_dir'] == 'desc') {
                                $sorts[] = '`' . $parameters['sort'] . '` DESC';
                            } else {
                                $sorts[] = '`' . $parameters['sort'] . '` ASC';
                            }
                        }
                        if (isset($parameters['sort2']) && $parameters['sort2'] != '') {
                            if (isset($parameters['sort_dir2']) && $parameters['sort_dir2'] == 'desc') {
                                $sorts[] = '`' . $parameters['sort2'] . '` DESC';
                            } else {
                                $sorts[] = '`' . $parameters['sort2'] . '` ASC';
                            }
                        }

                        if (!empty($sorts)) {
                            $query = $query . ' ORDER BY ' . implode(',', $sorts);
                        }
                        $stmt = $DBC->query($query, array((int)$depended_value));
                        if ($stmt) {
                            while ($ar = $DBC->fetch($stmt)) {
                                $ret[] = array($item_array['primary_key_name'] => $ar[$item_array['primary_key_name']], $value_name => $ar[$value_name]);
                            }
                        }
                    }
                }
            } else {
                $query = 'SELECT `' . $item_array['primary_key_name'] . '`, `' . $value_name_l . '` AS ' . $value_name . ' FROM ' . DB_PREFIX . '_' . $item_array['primary_key_table'] . ((isset($parameters['addwhere']) && $parameters['addwhere'] != '') ? ' WHERE ' . $parameters['addwhere'] : '');
                $sorts = array();
                if (isset($parameters['sort']) && $parameters['sort'] != '') {
                    if (isset($parameters['sort_dir']) && $parameters['sort_dir'] == 'desc') {
                        $sorts[] = '`' . $parameters['sort'] . '` DESC';
                    } else {
                        $sorts[] = '`' . $parameters['sort'] . '` ASC';
                    }
                }
                if (isset($parameters['sort2']) && $parameters['sort2'] != '') {
                    if (isset($parameters['sort_dir2']) && $parameters['sort_dir2'] == 'desc') {
                        $sorts[] = '`' . $parameters['sort2'] . '` DESC';
                    } else {
                        $sorts[] = '`' . $parameters['sort2'] . '` ASC';
                    }
                }
                if (!empty($sorts)) {
                    $query = $query . ' ORDER BY ' . implode(',', $sorts);
                } else {
                    $query = $item_array['query'];
                }

                $stmt = $DBC->query($query);
                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        $ret[] = $ar;
                    }
                }
            }

            if ($ret) {
                foreach ($ret as $k => $v) {
                    //while ($ar = $DBC->fetch($stmt)) {
                    $this->total_in_select[$item_array['name']]++;
                    $value = $v[$item_array['value_name']];
                    $value = trim($value);
                    //$value = htmlspecialchars_decode($value);
                    $value = htmlspecialchars($value, ENT_QUOTES, SITE_ENCODING);
                    if ($v[$item_array['primary_key_name']] == $item_array['value']) {
                        $selected = "selected";
                    } else {
                        $selected = "";
                    }
                    $rs .= '<option value="' . $v[$item_array['primary_key_name']] . '" ' . $selected . '>' . $value . '</option>';
                }
            }

            $rs .= '</select>';


            return $rs;
        } else {

            $combo = false;
            if (isset($item_array['combo']) && $item_array['combo'] == 1 && 1 == $this->getConfigValue('use_combobox')) {
                $combo = true;
                $tmp = $this->getRequestValue('tmp');
                //$ajax_
                if (isset($item_array['ajax_options']) && count($item_array['ajax_options']) > 0) {
                    $d = json_encode($item_array['ajax_options']);
                } else {
                    $d = json_encode(array());
                }
                $rs .= '<script type="text/javascript">$(document).ready(function(){$("select[id=' . $item_array['name'] . ']").mycombobox({tmp_val:\'' . $tmp[$item_array['name']] . '\',ajax_options:' . $d . '});});</script>';
            }

            if (isset($parameters['multiselect']) && (int)$parameters['multiselect'] == 1) {
                $this->total_in_select[$item_array['name']] = 0;
                $rs .= '<div id="' . $item_array['name'] . '_div">';

                $onchange = array();
                if (isset($item_array['onchange'])) {
                    $onchange[] = $item_array['onchange'];
                }
                if (isset($parameters['onchange']) && $parameters['onchange'] != '') {
                    $onchange[] = $parameters['onchange'];
                }

                $rs .= '<select class="' . $this->classes['select'] . '" name="' . $item_array['name'] . '[]" id="' . $item_array['name'] . '"' . (!empty($onchange) ? ' onchange="' . implode('', $onchange) . '"' : '') . (isset($item_array['onclick']) ? ' onClick="' . $item_array['onclick'] . '"' : '') . ' multiple="multiple">';
                if ($_SESSION['_lang'] != 'ru') {
                    $lang_key = 'title_default_' . $_SESSION['_lang'];
                    if ($item_array[$lang_key] != '') {
                        $item_array['title_default'] = $item_array[$lang_key];
                    }
                }
                //$rs .= '<option value="'.$item_array['value_default'].'">'.$item_array['title_default'].'</option>';
                $DBC = DBC::getInstance();
                $query = $item_array['query'];
                $stmt = $DBC->query($query);
                if (!is_array($item_array['value'])) {
                    $item_array['value'] = (array)$item_array['value'];
                }
                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        $this->total_in_select[$item_array['name']]++;
                        $value = $ar[$item_array['value_name']];
                        $value = trim($value);
                        //$value = htmlspecialchars_decode($value);
                        $value = htmlspecialchars($value, ENT_QUOTES, SITE_ENCODING);
                        if (in_array($ar[$item_array['primary_key_name']], $item_array['value'])) {
                            $selected = "selected";
                        } else {
                            $selected = "";
                        }
                        $rs .= '<option value="' . $ar[$item_array['primary_key_name']] . '" ' . $selected . '>' . $value . '</option>';
                    }
                }

                $rs .= '</select>';
                $rs .= '</div>';
            } else {


                $table_name = $item_array['table_name'];
                $field_name = $item_array['name'];

                $realquery = '';
                /*
                if($table_name == 'data'){

                    if($field_name == 'region_id'){
                        $realquery = 'SELECT * FROM ' . DB_PREFIX . '_region WHERE country_id=' . intval($model['country_id']['value']) . ' ORDER BY name';
                    }

                }
                */


                //print_r($item_array);

                $this->total_in_select[$item_array['name']] = 0;
                $rs .= '<div id="' . $item_array['name'] . '_div">';

                $onchange = array();
                if (isset($item_array['onchange'])) {
                    $onchange[] = $item_array['onchange'];
                }
                if (isset($parameters['onchange']) && $parameters['onchange'] != '') {
                    $onchange[] = $parameters['onchange'];
                }

                $rs .= '<select class="' . $this->classes['select'] . '" name="' . $item_array['name'] . '" id="' . $item_array['name'] . '"' . (!empty($onchange) ? ' onchange="' . implode('', $onchange) . '"' : '') . (isset($item_array['onclick']) ? ' onClick="' . $item_array['onclick'] . '"' : '') . '>';
                if ($_SESSION['_lang'] != 'ru') {
                    $lang_key = 'title_default_' . $_SESSION['_lang'];
                    if ($item_array[$lang_key] != '') {
                        $item_array['title_default'] = $item_array[$lang_key];
                    }
                }
                $rs .= '<option value="' . $item_array['value_default'] . '">' . $item_array['title_default'] . '</option>';
                $DBC = DBC::getInstance();
                $query = $item_array['query'];

                if ($realquery != '') {
                    $query = $realquery;
                }
                /* if(isset($parameters['ml_query'])){
                  $query=$parameters['ml_query'];
                  $curr_lang=$this->getCurrentLang();
                  if($curr_lang=='ru' && 1===intval($this->getConfigValue('apps.language.use_default_as_ru'))){
                  $curr_lang='';
                  }else{
                  $curr_lang='_'.$curr_lang;
                  }
                  $query=preg_replace('/\{ln\}/', $curr_lang, $query);
                  } */
                //echo $query;
                //$query=preg_replace('/\{current_user\}/', intval($_SESSION['user_id']), $query);
                $curr_lang = $this->getCurrentLang();
                if ( $query == '' ) {
                    return '';
                }
                $stmt = $DBC->query($query);
                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        $this->total_in_select[$item_array['name']]++;
                        if ($curr_lang != 'ru' && $ar[$item_array['value_name'] . '_' . $curr_lang] != '' && $this->getConfigValue('apps.language.use_langs')) {
                            $value = $ar[$item_array['value_name'] . '_' . $curr_lang];
                        } else {
                            $value = $ar[$item_array['value_name']];
                        }
                        $value = trim($value);
                        //$value = htmlspecialchars_decode($value);
                        $value = htmlspecialchars($value, ENT_QUOTES, SITE_ENCODING);
                        if ($ar[$item_array['primary_key_name']] == $item_array['value']) {
                            $selected = "selected";
                        } else {
                            $selected = "";
                        }
                        $rs .= '<option value="' . $ar[$item_array['primary_key_name']] . '" ' . $selected . '>' . $value . '</option>';
                    }
                }

                $rs .= '</select>';
                $rs .= '</div>';
            }
            //echo 'single<br>';
            //print_r($item_array).'<br>';
            //echo '<hr>';
            //echo $item_md5;
            //echo '<hr>';

            self::$cache[$item_md5] = $rs;
            return $rs;
        }
    }

    function get_select_by_query_multi($item_array, $model = null)
    {


        if (isset($item_array['parameters'])) {
            $parameters = $item_array['parameters'];
        } else {
            $parameters = array();
        }

        $rs = '';
        $tag_mode = false;
        if (isset($parameters['mode']) && $parameters['mode'] == 'checkbox') {
            $checkbox_mode = true;
        } elseif (isset($parameters['mode']) && $parameters['mode'] == 'tag') {
            $tag_mode = true;
        } else {
            $checkbox_mode = false;
        }

        $size = 0;
        if (isset($parameters['multiselect_size']) && intval($parameters['multiselect_size']) > 0) {
            $size = intval($parameters['multiselect_size']);
        }

        //
        $values = $item_array['value'];
        //print_r($values);
        if (!is_array($values)) {
            $values = (array)$values;
        }

        $DBC = DBC::getInstance();


        $options = array();


        $query = $item_array['query'];
        $query_params = array();

        $value_name = $item_array['value_name'];
        $value_name_l = $item_array['value_name'];
        if (1 === intval($this->getConfigValue('apps.language.use_langs')) && 0 === intval($parameters['no_ml'])) {
            $curlang = $this->getCurrentLang();
            if (1 === intval($this->getConfigValue('apps.language.use_default_as_ru')) && $curlang == 'ru') {

            } else {
                $value_name_l = $value_name . '_' . $curlang;
            }
        }

        if($tag_mode){
            $selected_options = '';
            if(!empty($values)){
                foreach ($item_array['value_string'] as $k => $v){
                    $selected_options .= '<span class="multiselect-item"><input type="hidden" name="'.$item_array['name'].'[]" value="'.$k.'">'.$v.'<button type="button" class="close">×</button></span>';
                }

            }
        }


        if (1 == intval($this->getConfigValue('apps.realty.off_system_ajax'))) {
            $depended_element_name = '';
            if (isset($parameters['depended']) && $parameters['depended'] != '') {
                $depended_element_name = trim($parameters['depended']);
                list($a, $b) = explode(',', $depended_element_name);
                if ($b != '') {
                    $depended_element_name = $a;
                    $depended_element_name_key = $b;
                } else {
                    $depended_element_name_key = $depended_element_name;
                }
            }


            if ($depended_element_name != '') {
                $depended_value = intval($model[$depended_element_name]['value']);

                if ($depended_value != 0) {
                    $query = 'SELECT `' . $item_array['primary_key_name'] . '`, `' . $value_name_l . '` AS ' . $value_name . ' FROM ' . DB_PREFIX . '_' . $item_array['primary_key_table'] . ' WHERE `' . $depended_element_name . '`=?' . ($parameters['addwhere'] > '' ? ' and ' . $parameters['addwhere'] : '');

                    $sorts = array();
                    if (isset($parameters['sort']) && $parameters['sort'] != '') {
                        if (isset($parameters['sort_dir']) && $parameters['sort_dir'] == 'desc') {
                            $sorts[] = '`' . $parameters['sort'] . '` DESC';
                        } else {
                            $sorts[] = '`' . $parameters['sort'] . '` ASC';
                        }
                    }
                    if (isset($parameters['sort2']) && $parameters['sort2'] != '') {
                        if (isset($parameters['sort_dir2']) && $parameters['sort_dir2'] == 'desc') {
                            $sorts[] = '`' . $parameters['sort2'] . '` DESC';
                        } else {
                            $sorts[] = '`' . $parameters['sort2'] . '` ASC';
                        }
                    }

                    if (!empty($sorts)) {
                        $query = $query . ' ORDER BY ' . implode(',', $sorts);
                    }

                    $query_params[] = $depended_value;
                    /*$stmt = $DBC->query($query, array((int) $depended_value));
                    if ($stmt) {
                        while ($ar = $DBC->fetch($stmt)) {
                            $ret[] = array($item_array['primary_key_name'] => $ar[$item_array['primary_key_name']], $value_name => $ar[$value_name]);
                        }
                    }
                    print_r($ret);*/
                } else {
                    $query = '';
                }
            }
        }

        if ($query != '') {
            if (!empty($query_params)) {
                $stmt = $DBC->query($query, $query_params);
            } else {
                $stmt = $DBC->query($query);
            }

            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {

                    //var_dump($ar);

                    $this->total_in_select[$item_array['name']]++;
                    $value = $ar[$value_name_l];
                    $value = trim($value);
                    $value = htmlspecialchars($value, ENT_QUOTES, SITE_ENCODING);
                    $selected = false;

                    if (is_array($values) && in_array($ar[$item_array['primary_key_name']], $values)) {
                        $selected = true;
                    } elseif (!is_array($values) && $ar[$item_array['primary_key_name']] == $values) {
                        $selected = true;
                    }

                    $options[] = array($ar[$item_array['primary_key_name']], $value, $selected);
                }
            }
        }


        //print_r($options);

        $this->total_in_select[$item_array['name']] = 0;

        if($tag_mode){
            $rs .= '<div class="multiselect_block" id="' . $item_array['name'] . '"><input class="' . (isset($this->classes['input']) ? $this->classes['input'] : '') . ' multiselect-autocomplete" type="text" placeholder="' . $item_array['title_default'] . '" data-model="' . $item_array['table_name'] . '" data-element="' . $item_array['name'] . '" />';
            $rs .= '<div class="multiselect-holder">'.$selected_options.'</div>';
            $rs .= '</div>';
        }elseif ($checkbox_mode) {
            $rs .= '<div class="multiselect_set multiselect_set_c multiselect_set_' . $item_array['name'] . '" id="' . $item_array['name'] . '">';
            if (!empty($options)) {
                foreach ($options as $option) {
                    $this->total_in_select[$item_array['name']]++;
                    $value = $option[1];
                    $value = trim($value);
                    $value = htmlspecialchars($value, ENT_QUOTES, SITE_ENCODING);
                    $rs .= '<div class="multiselect_set_item"' . ($parameters['data_field'] > '' ? ' data-' . $parameters['data_field'] . '="' . $ar[$parameters['data_field']] . '"' : '') . '><label><input type="checkbox"' . ($option[2] == 1 ? ' checked="checked"' : '') . ' name="' . $item_array['name'] . '[]" value="' . $option[0] . '"> <span>' . $value . '</span></label></div>';

                }
            }
            $rs .= '</div>';
        } else {
            $rs .= '<div class="multiselect_set multiselect_set_s multiselect_set_' . $item_array['name'] . '">';
            $rs .= '<select size="' . $size . '" name="' . $item_array['name'] . '[]" id="' . $item_array['name'] . '" multiple="multiple" class="' . $this->classes['select'] . '">';
            if (!empty($options)) {
                foreach ($options as $option) {
                    $this->total_in_select[$item_array['name']]++;
                    $value = $option[1];
                    $value = trim($value);
                    $value = htmlspecialchars($value, ENT_QUOTES, SITE_ENCODING);
                    $rs .= '<option value="' . $option[0] . '" ' . ($option[2] == 1 ? ' selected="selected"' : '') . '>' . $value . '</option>';
                }
            }
            $rs .= '</select>';
            $rs .= '</div>';
        }
        return $rs;
    }

    /**
     * Get single select box by query
     * @param array $item_array
     * @return string
     */
    function get_single_select_box_by_query_multiple($item_array)
    {
        $values = array();
        if (isset($item_array['values_array'])) {
            $values = (array)$item_array['values_array'];
        }
        $rs = '';

        $this->total_in_select[$item_array['name']] = 0;
        $rs .= '<div id="' . $item_array['name'] . '_div">';
        $rs .= '<select data-placeholder="' . $item_array['title_default'] . '" data-none-selected-text="' . $item_array['title_default'] . '" class="' . $this->classes['select'] . '" name="' . $item_array['name'] . '[]" id="' . $item_array['name'] . '"' . (isset($item_array['onchange']) ? ' onchange="' . $item_array['onchange'] . '"' : '') . ' multiple="multiple">';
        $DBC = DBC::getInstance();
        $query = $item_array['query'];
        $stmt = $DBC->query($query);

        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $this->total_in_select[$item_array['name']]++;
                $value = $ar[$item_array['value_name']];
                $value = trim($value);
                //$value = htmlspecialchars_decode($value);
                $value = htmlspecialchars($value, ENT_QUOTES, SITE_ENCODING);
                $selected = '';
                if (is_array($values)) {
                    if (in_array($ar[$item_array['primary_key_name']], $values)) {
                        $selected = "selected";
                    }
                }
                $rs .= '<option value="' . $ar[$item_array['primary_key_name']] . '" ' . $selected . '>' . $value . '</option>';
            }
        }

        $rs .= '</select>';
        $rs .= '</div>';

        return $rs;
    }

    /**
     * Get select box row
     * @param array $item_array
     * @return string
     */
    function get_select_box_row($item_array)
    {
        $rs = '<tr class="row3"  alt="' . $item_array['name'] . '">';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        if ($item_array['required'] == "on") {
            $rs .= " <span style=\"color: red;\">*</span> \n";
        }
        $rs .= ((isset($item_array['hint']) && $item_array['hint'] != '') ? ' <span class="hint">(' . $item_array['hint'] . ')</span>' : '') . '</td>';
        $rs .= '<td>';
        $rs .= $this->get_select_box($item_array);
        $rs .= '</td>';
        $rs .= '</tr>';

        return $rs;
    }

    /**
     * Get uploader row
     * @param array $item_array
     * @return string
     */
    function get_uploader_row($item_array)
    {
        $rs = '';
        $rs .= '<tr  alt="' . $item_array['name'] . '">';
        $rs .= '<td colspan="2">';
        $rs .= '<h2>' . Multilanguage::_('L_PHOTO_1') . '</h2>';


        $rs .= '</td>';
        $rs .= '</tr>';

        $rs .= '<tr>';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        if ($item_array['required'] == "on") {
            $rs .= " <span style=\"color: red;\">*</span> \n";
        }
        $rs .= '</td>';
        $rs .= '<td>';
        $rs .= $this->getUploaderPlugin($this->get_session_key());
        $rs .= '</td>';
        $rs .= '</tr>';

        //echo $rs;
        //exit;

        return $rs;
    }

    /**
     * Get uploader row
     * @param array $item_array
     * @return string
     */
    function get_pluploader_row($item_array)
    {
        $rs = '';
        $rs .= '<tr  class="row3">';
        $rs .= '<td colspan="2">';
        $rs .= '<h2>' . Multilanguage::_('L_PHOTO_1') . '</h2>';

        $rs .= $this->getImageListAdmin($item_array['action'], $item_array['table_name'], $item_array['primary_key'], $item_array['primary_key_value'], $_count);

        $rs .= '</td>';
        $rs .= '</tr>';

        $rs .= '<tr  class="row3"  alt="' . $item_array['name'] . '">';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        if ($item_array['required'] == "on") {
            $rs .= " <span style=\"color: red;\">*</span> \n";
        }
        $rs .= '</td>';
        $rs .= '<td>';
        //$rs .= $this->getPP($this->get_session_key());
        $rs .= $this->getPluploaderPlugin($this->get_session_key());
        $rs .= '</td>';
        $rs .= '</tr>';

        //echo $rs;
        //exit;

        return $rs;
    }

    /**
     * Get uploadify row
     * @param array $item_array
     * @return string
     */
    function get_uploadify_row($item_array)
    {
        $rs = '';
        $rs .= '<tr  class="row3">';
        $rs .= '<td colspan="2">';
        $rs .= '<h2>' . Multilanguage::_('L_PHOTO_1') . '</h2>';

        //$action, $table_name, $key, $record_id
        $_count = 0;
        $rs .= $this->getImageListAdmin($item_array['action'], $item_array['table_name'], $item_array['primary_key'], $item_array['primary_key_value'], $_count);

        $rs .= '</td>';
        $rs .= '</tr>';
        if ($this->getConfigValue('photo_per_data') > 0 and $item_array['action'] == 'data') {
            if ($_count >= $this->getConfigValue('photo_per_data')) {
                return $rs;
            }
        }
        $rs .= '<tr  class="row3"  alt="' . $item_array['name'] . '">';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        if ($item_array['required'] == "on") {
            $rs .= " <span style=\"color: red;\">*</span> \n";
        }
        $rs .= '</td>';
        $rs .= '<td>';
        $rs .= $this->getUploadifyPlugin($this->get_session_key());
        $rs .= '</td>';
        $rs .= '</tr>';


        //echo $rs;
        //exit;

        return $rs;
    }

    /**
     * Get uploadify file row
     * @param array $item_array
     * @return string
     */
    function get_uploadify_file_row($item_array)
    {
        $rs = '';
        $rs .= '<tr  class="row3"  alt="' . $item_array['name'] . '">';
        $rs .= '<td colspan="2">';
        $rs .= '<h2>' . Multilanguage::_('L_ATTACH_FILE') . '</h2>';

        //$action, $table_name, $key, $record_id

        $rs .= $this->getFileListAdmin($item_array['action'], $item_array['table_name'], $item_array['primary_key'], $item_array['primary_key_value']);
        $rs .= '</td>';
        $rs .= '</tr>';

        $rs .= '<tr  class="row3">';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        if ($item_array['required'] == "on") {
            $rs .= " <span style=\"color: red;\">*</span> \n";
        }
        $rs .= '</td>';
        $rs .= '<td>';
        $rs .= $this->getUploadifyFilePlugin($this->get_session_key());
        $rs .= '</td>';
        $rs .= '</tr>';

        //echo $rs;
        //exit;

        return $rs;
    }

    /**
     * Get separator row
     * @param array $item_array
     * @return string
     */
    function get_separator_row($item_array)
    {
        $rs = '';
        $rs .= '<tr>';
        $rs .= '<td colspan="2">';
        $rs .= '<h2>' . $item_array['title'] . '</h2>';
        $rs .= '</td>';
        $rs .= '</tr>';

        return $rs;
    }

    /**
     * Get injector row
     * @param array $item_array
     * @return string
     */
    function get_injector_row($item_array)
    {
        $form_injector = new \system\lib\system\form\Form_Injector();


        $rs = '<tr>';
        $rs .= '<td colspan="2">';
        $rs .= $form_injector->compile();
        $rs .= '</td>';
        $rs .= '</tr>';

        return $rs;
    }

    /**
     * Get select box structure row
     * @param array $item_array
     * @return string
     */
    function get_select_box_structure_row($item_array)
    {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();

        $rs = '<tr  class="row3"  alt="' . $item_array['name'] . '">';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        if ($item_array['required'] == "on") {
            $rs .= " <span style=\"color: red;\">*</span> \n";
        }
        $rs .= '</td>';
        $rs .= '<td>';
        $rs .= $Structure_Manager->getCategorySelectBoxWithName($item_array['name'], $item_array['value']);
        $rs .= '</td>';
        $rs .= '</tr>';

        return $rs;
    }

    function get_structure_row($item_array)
    {

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_implements.php');
        $SM = Structure_Implements::getManager($item_array['entity']);

        $rs = '<tr  class="row3"  alt="' . $item_array['name'] . '">';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        if ($item_array['required'] == "on") {
            $rs .= " <span style=\"color: red;\">*</span> \n";
        }
        $rs .= '</td>';
        $rs .= '<td>';
        $rs .= $SM->getCategorySelectBoxWithName($item_array['name'], $item_array['value']);
        $rs .= '</td>';
        $rs .= '</tr>';

        return $rs;
    }

    /**
     * Get select box structure row
     * @param array $item_array
     * @return string
     */
    function get_select_box_structure_simple_multiple_row($item_array)
    {
        if (!isset($item_array['values_array'])) {
            $item_array['values_array'] = array(0 => 0);
        }
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();

        $rs = '<tr  class="row3"  alt="' . $item_array['name'] . '">';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        if ($item_array['required'] == "on") {
            $rs .= " <span style=\"color: red;\">*</span> \n";
        }
        $rs .= '</td>';
        $rs .= '<td>';
        $rs .= $Structure_Manager->getCategorySelectBoxWithName($item_array['name'], $item_array['values_array']);
        $rs .= '</td>';
        $rs .= '</tr>';

        return $rs;
    }

    function get_shop_select_box_structure_row($item_array)
    {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();

        $rs = '<tr  class="row3"  alt="' . $item_array['name'] . '">';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        if ($item_array['required'] == "on") {
            $rs .= " <span style=\"color: red;\">*</span> \n";
        }
        $rs .= '</td>';
        $rs .= '<td>';
        $rs .= $Structure_Manager->getShopCategorySelectBoxWithName($item_array['name'], $item_array['value']);
        $rs .= '</td>';
        $rs .= '</tr>';

        return $rs;
    }

    /**
     * Get select box for tree table structure type
     * @param array $item_arrayy
     * @return select tag string
     * @author Kris
     */
    function get_service_type_select_box_structure_row($item_array)
    {

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $rs = '<tr  class="row3"  alt="' . $item_array['name'] . '">';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        if ($item_array['required'] == "on") {
            $rs .= " <span style=\"color: red;\">*</span> \n";
        }
        $rs .= '</td>';
        $rs .= '<td>';
        $rs .= $Structure_Manager->getServiceTypesTree_selectBox($item_array['name'], $item_array['value']);
        $rs .= '</td>';
        $rs .= '</tr>';

        return $rs;
    }

    /**
     * Get checkbox box row
     * @param array $item_array
     * @return string
     */
    function get_checkbox_box_row($item_array)
    {
        $rs = '<tr  class="row3" alt="' . $item_array['name'] . '">';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        if ($item_array['required'] == "on") {
            $rs .= " <span style=\"color: red;\">*</span> \n";
        }
        $rs .= ((isset($item_array['hint']) && $item_array['hint'] != '') ? ' <span class="hint">(' . $item_array['hint'] . ')</span>' : '') . '</td>';
        $rs .= '<td>';
        $rs .= $this->get_checkbox($item_array);
        if ($item_array['ajax_popup'] != '') {
            $rs .= $item_array['ajax_popup'];
        }
        $rs .= '</td>';
        $rs .= '</tr>';

        return $rs;
    }

    /**
     * Get textarea row
     * @param array $item_array
     * @return string
     */
    function get_textarea_row($item_array)
    {
        $rs = '<tr  class="row3"  alt="' . $item_array['name'] . '">';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        if ($item_array['required'] == "on") {
            $rs .= " <span style=\"color: red;\">*</span> \n";
        }

        if ($item_array['rows'] == '') {
            $item_array['rows'] = 10;
        }

        if ($item_array['cols'] == '') {
            $item_array['cols'] = 50;
        }

        $rs .= '</td>';
        $rs .= '<td>';
        $rs .= '<textarea name="' . $item_array['name'] . '" rows="' . $item_array['rows'] . '" cols="' . $item_array['cols'] . '">' . htmlspecialchars($item_array['value']) . '</textarea>';
        $rs .= '</td>';
        $rs .= '</tr>';

        return $rs;
    }

    /**
     * Get textarea with editor row
     * @param array $item_array
     * @return string
     */
    function get_textarea_editor_row($item_array)
    {
        //sleep(1);
        $id = $item_array['name'] . '_' . md5(time() . '_' . rand(10, 99));
        $rs = '';
        if (isset($item_array['editor']) and ($item_array['editor'] !== 'editor')) {
            if ($this->getConfigValue($item_array['editor']) != '') {
                $editor_code = $this->getConfigValue($item_array['editor']);
            } else {
                $editor_code = $this->getConfigValue('editor');
            }
        } else {
            $editor_code = $this->getConfigValue('editor');
        }
        if ($editor_code == 'ckeditor') {
            if (!defined('NO_DYNAMIC_INCS') || !NO_DYNAMIC_INCS) {
                $rs .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/ckeditor/ckeditor.js"></script>';
                $rs .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/ckeditor/adapters/jquery.js"></script>';
            }

            $rs .= '<script type="text/javascript">
                $(document).ready(function() {
                    $("textarea#' . $id . '").ckeditor({
        filebrowserBrowseUrl : \'/ckfinder/ckfinder.html\',
        filebrowserImageBrowseUrl : \'/ckfinder/ckfinder.html?Type=Images\',
        filebrowserFlashBrowseUrl : \'/ckfinder/ckfinder.html?Type=Flash\',
        filebrowserUploadUrl : \'/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files\',
        filebrowserImageUploadUrl : \'/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images\',
        filebrowserFlashUploadUrl : \'/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash\'
                    });
                });
            </script>';
        } else {
            if (!defined('NO_DYNAMIC_INCS') || !NO_DYNAMIC_INCS) {
                $rs .= '<link rel="stylesheet" type="text/css" href="' . SITEBILL_MAIN_URL . '/js/cleditor/jquery.cleditor.css" />';
                $rs .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/js/cleditor/jquery.cleditor.min.js"></script>';
            }

            $rs .= '<script type="text/javascript">
                $(document).ready(function() {
                    $("textarea#' . $id . '").cleditor();
                });
            </script>
            ';
        }
        $rs .= '<tr  class="row3"  alt="' . $item_array['name'] . '">';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        if ($item_array['required'] == "on") {
            $rs .= " <span style=\"color: red;\">*</span> \n";
        }

        if ($item_array['rows'] == '') {
            $item_array['rows'] = 10;
        }

        if ($item_array['cols'] == '') {
            $item_array['cols'] = 50;
        }

        $rs .= '</td>';
        $rs .= '<td>';
        $rs .= '<textarea id="' . $id . '" class="input" name="' . $item_array['name'] . '" rows="' . $item_array['rows'] . '" cols="' . $item_array['cols'] . '">' . $item_array['value'] . '</textarea>';
        $rs .= '</td>';
        $rs .= '</tr>';

        return $rs;
    }

    /**
     * Get grade row
     * @param array $item_array
     * @return string
     */
    function get_grade_row($item_array)
    {
        $rs = '<tr  class="row3"  alt="' . $item_array['name'] . '">';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        if ($item_array['required'] == "on") {
            $rs .= " <span style=\"color: red;\">*</span> \n";
        }
        $rs .= '</td>';
        $rs .= '<td>';

        $vals = array();

        if (isset($item_array['grade_values'])) {
            $vals = $item_array['grade_values'];
        } elseif (isset($item_array['select_data'])) {
            $vals = $item_array['select_data'];
        }

        if (!empty($vals)) {
            foreach ($vals as $item_id => $item_id_name) {
                if ($item_array['value'] == $item_id) {
                    $checked = 'checked';
                } else {
                    $checked = '';
                }
                $rs .= '<span>' . $item_id_name . '</span><input type="radio" name="' . $item_array['name'] . '" value="' . $item_id . '" ' . $checked . '>&nbsp;&nbsp;&nbsp;';
            }
        } else {
            $rs .= '<input type="text" name="' . $item_array['name'] . '" value="' . $item_array['value'] . '" />';
        }


        $rs .= '</td>';
        $rs .= '</tr>';

        return $rs;
    }

}
