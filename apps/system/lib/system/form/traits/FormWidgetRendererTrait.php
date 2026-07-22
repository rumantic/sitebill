<?php
trait FormWidgetRendererTrait
{
    /**
     * Get check box
     * @param array $item_array
     * @return string
     */
    function get_checkbox($item_array)
    {

        $dp = array();
        $dp['id'] = $this->form_id . '_' . $item_array['name'];
        $dp['placeholder'] = $item_array['title'];
        $dp['class'] = (isset($this->classes['checkbox']) ? $this->classes['checkbox'] : '');
        $isChecked = false;
        if ($item_array['value'] == 1) {
            $isChecked = true;
        }

        $html = $this->form_decorator->decorateCheckboxInput($item_array['name'], $item_array['value'], $isChecked, $dp);

        /*$rs = '<input id="'.$this->form_id.'_'.$item_array['name'].'" type="checkbox" name="'.$item_array['name'].'" value="'.$item_array['value'].'"';
        if ( $item_array['value'] == 1 ) {
            $rs .= ' checked ';
        }
        $rs .= '/>';*/
        return $html;
    }

    /**
     * Get select box
     * @param array $item_array
     * @return string
     */
    function get_select_box($item_array)
    {
        $parameters = array();
        if (isset($item_array['parameters'])) {
            $parameters = $item_array['parameters'];
        }

        if (isset($parameters['multiselect']) && 1 == (int)$parameters['multiselect']) {
            $rs = $this->form_decorator->decorateMultiselectItem($item_array['name'], $item_array['select_data'], $item_array['values_array']);
            /*foreach ( $item_array['select_data'] as $item_id => $item_value ) {
                $rs .= '<input type="checkbox" name="'.$item_array['name'].'[]" value="'.$item_id.'"'.((isset($item_array['values_array']) && in_array($item_id, $item_array['values_array'])) ? ' checked="checked"' : '').'>'.$item_value.'<br/>';
            }*/
        } else {

            $rs = '<select class="' . @$this->classes['select'] . '" name="' . $item_array['name'] . '">';
            if (!empty($item_array['select_data'])) {
                foreach ($item_array['select_data'] as $item_id => $item_value) {

                    if ($item_id === '__optgroup') {
                        //echo $item_id.'=__optgroup'.'<br />';;
                        $optgroup_content = $item_value;
                        $rs .= '<optgroup label="' . $optgroup_content['name'] . '">';
                        if (is_array($optgroup_content['select_data']) && count($optgroup_content['select_data']) > 0) {
                            foreach ($optgroup_content['select_data'] as $ogi => $ogv) {
                                if ($ogi == $item_array['value']) {
                                    $selected = "selected";
                                } else {
                                    $selected = "";
                                }
                                $rs .= '<option value="' . $ogi . '" ' . $selected . '>' . $ogv . '</option>';
                            }
                            $rs .= '</optgroup>';
                        }
                    } else {
                        //echo $item_id.'!=__optgroup'.'<br />';;
                        if ($item_id == $item_array['value']) {
                            $selected = "selected";
                        } else {
                            $selected = "";
                        }
                        $rs .= '<option value="' . $item_id . '" ' . $selected . '>' . $item_value . '</option>';
                    }
                }
            }
            $rs .= '</select>';
        }


        return $rs;
    }

    function get_radiogroup($item_array)
    {
        $val = $item_array['value'];

        $ret = '';
        if (!empty($item['select_data'])) {
            foreach ($item['select_data'] as $k => $v) {
                $ret .= '<input type="radio" name="' . $item['name'] . '" value="' . $k . '"' . ($k == $val ? ' checked="checked"' : '') . '> ' . $v;
            }
        }
        return $ret;
    }

    /**
     * Get captcha input
     * @param unknown_type $item_array
     * @return string
     */
    function get_captcha_input($item_array)
    {
        $this->clear_captcha_session_table();
        /* HTML code */

        $captcha_type = $this->getConfigValue('captcha_type');
        if ($captcha_type == 2) {
            return FALSE;
        } elseif ($captcha_type == 3) {
            $string = '';

            $string .= "<tr  class=\"row3\" alt=\"" . $item_array['name'] . "\">\n";

            $captcha_session_key = $this->generateCaptchaSessionKey();

            /* Mark required field with simbol '*' */
            $string .= "<td>" . $item_array['title'] . " <span style=\"color: red;\">*</span> </td>\n";

            $string .= "<td><img id=\"capcha_img\" class=\"capcha_img\" src=\"" . SITEBILL_MAIN_URL . "/third/kcaptcha/index.php?captcha_session_key=" . $captcha_session_key . "\" width=\"180\" height=\"80\">";
            $string .= '<br /><a href="javascript:void(0);" rel="nofollow" id="captcha_refresh" class="captcha_refresh">' . Multilanguage::_('CAPTCHA_REFR', 'system') . '</a>';
            $string .= "<br><input type=\"text\" name=\"" . $item_array['name'] . "\" value=\"\" size=\"23\" maxlength=\"" . $item_array['maxlength'] . "\">";
            $string .= '<input type="hidden" name="captcha_session_key" value="' . $captcha_session_key . '"></td>' . "\n";
            $string .= "</tr>\n";
            if (!defined('NO_DYNAMIC_INCS') || !NO_DYNAMIC_INCS) {
                $string .= '<script src="' . SITEBILL_MAIN_URL . '/apps/system/js/md5.js"></script>';
            }

            $string .= '<script type="text/javascript">';
            $string .= '$(document).ready(function(){
                $(".captcha_refresh").click(function(){
                    var new_key=new Date().getTime();
                    var hash = CryptoJS.MD5(String(new_key));
                    var parent=$(this).parents("td").eq(0);
                    parent.find(".capcha_img").eq(0).attr("src", estate_folder+\'/apps/third/kcaptcha/index.php?captcha_session_key=\' + hash);
                    parent.find("input[name=captcha_session_key]").val(hash);
                });

            });';
            $string .= '</script>';
        } else {
            $string = '';
            $string .= "<tr  class=\"row3\" alt=\"" . $item_array['name'] . "\">\n";

            $captcha_session_key = $this->generateCaptchaSessionKey();

            /* Mark required field with simbol '*' */
            $string .= "<td>" . $item_array['title'] . " <span style=\"color: red;\">*</span> </td>\n";

            $string .= "<td><img id=\"capcha_img\" class=\"capcha_img\" src=\"" . SITEBILL_MAIN_URL . "/captcha.php?captcha_session_key=" . $captcha_session_key . "\" width=\"180\" height=\"80\">";
            $string .= '<br /><a href="javascript:void(0);" rel="nofollow" id="captcha_refresh" class="captcha_refresh">' . Multilanguage::_('CAPTCHA_REFR', 'system') . '</a>';
            $string .= "<br><input type=\"text\" name=\"" . $item_array['name'] . "\" value=\"\" size=\"23\" maxlength=\"" . (isset($item_array['maxlength']) ? $item_array['maxlength'] : '') . "\"></td>" . "\n";
            $string .= '<input type="hidden" name="captcha_session_key" value="' . $captcha_session_key . '">';
            $string .= "</tr>\n";
            if (!defined('NO_DYNAMIC_INCS') || !NO_DYNAMIC_INCS) {
                $string .= '<script src="' . SITEBILL_MAIN_URL . '/apps/system/js/md5.js"></script>';
            }

            $string .= '<script type="text/javascript">';
            $string .= '$(document).ready(function(){
                $(".captcha_refresh").click(function(){
                    var new_key=new Date().getTime();
                    var hash = CryptoJS.MD5(String(new_key));
                    var parent=$(this).parents("td").eq(0);
                    parent.find(".capcha_img").eq(0).attr("src", estate_folder+\'/captcha.php?captcha_session_key=\' + hash);
                    parent.find("input[name=captcha_session_key]").val(hash);
                });
                /*$("#captcha_refresh").click(function(){
                    var new_key=new Date().getTime();
                    var hash = CryptoJS.MD5(String(new_key));
                    document.getElementById("capcha_img").src = estate_folder+\'/captcha.php?captcha_session_key=\' + hash;
                    $("input[name=captcha_session_key]").val(hash);
                });*/
            });';
            $string .= '</script>';
        }
        $this->clear_captcha_session_table();


        /* Return html code */
        return $string;
    }

    /**
     * Generate captcha session key
     * @param void
     * @return string
     */
    function generateCaptchaSessionKey()
    {
        return md5(time() . rand(1000, 9999) . 'random key captcha string core sitebill');
    }

    /**
     * Get date input
     * @param array $item_array
     * @return string
     */
    function get_date_input($item_array)
    {
        $string = '';
        $string .= '<script type="text/javascript">$(document).ready(function() {$( "#' . $item_array['name'] . '" ).datepicker({showOn: "button",dateFormat: "dd.mm.yy",buttonImage: "' . SITEBILL_MAIN_URL . '/img/calendar.gif",buttonImageOnly: true});});</script>';
        $string .= "<tr  class=\"row3\" alt=\"" . $item_array['name'] . "\">\n";

        $string .= '<td>' . $item_array['title'] . ($item_array['required'] == "on" ? '<span style="color: red;">*</span>' : '') . '</td>' . "\n";

        if (preg_match('/(\d{4,4})-(\d{2,2})-(\d{2,2}) (\d{2,2}):(\d{2,2}):(\d{2,2})/', $item_array['value'])) {
            $item_array['value'] = date('d.m.Y', strtotime($item_array['value']));
        } elseif (preg_match('/(\d{4,4})-(\d{2,2})-(\d{2,2})/', $item_array['value'])) {
            $item_array['value'] = date('d.m.Y', strtotime($item_array['value']));
        } elseif ($item_array['value'] == 0 || $item_array['value'] == '') {

            $item_array['value'] = '';
        } else {
            $item_array['value'] = date('d.m.Y', $item_array['value']);
        }

        $string .= '<td><input type="text" name="' . $item_array['name'] . '" id="' . $item_array['name'] . '" value="' . htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING) . '" size="10" maxlength="' . $item_array['maxlength'] . '"></td>';
        $string .= '</tr>' . "\n";

        /* Return html code */
        return $string;
    }

    /**
     * Get safe string input
     * @param array $item_array
     * @return string
     */
    function get_safe_text_input($item_array)
    {


        /* HTML code */
        $string = '';
        $string .= "<tr class=\"row3\" alt=\"" . $item_array['name'] . "\">\n";

        /* Mark required field with simbol '*' */
        if ($item_array['required'] == "on") {
            $string .= "<td>" . $item_array['title'] . " <span style=\"color: red;\">*</span>" . ((isset($item_array['hint']) && $item_array['hint'] != '') ? ' <span class="hint">(' . $item_array['hint'] . ')</span>' : '') . "</td>\n";
        } else {
            $string .= "<td>" . $item_array['title'] . ((isset($item_array['hint']) && $item_array['hint'] != '') ? ' <span class="hint">(' . $item_array['hint'] . ')</span>' : '') . "</td>\n";
        }

        $string .= '<td><input type="text" name="' . $item_array['name'] . '" value="' . htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING) . '"' . (isset($item_array['length']) ? ' size="' . $item_array['length'] . '"' : '') . (isset($item_array['maxlength']) ? ' maxlength="' . $item_array['maxlength'] . '"' : '') . ' /></td>' . "\n";
        $string .= '</tr>' . "\n";

        /* Return html code */
        return $string;
    }

    function get_geodata_input($item_array)
    {
        $string = '';
        $string .= "<tr class=\"row3\" alt=\"" . $item_array['name'] . "\">\n";

        if ($item_array['required'] == "on") {
            $string .= "<td>" . $item_array['title'] . " <span style=\"color: red;\">*</span>" . ((isset($item_array['hint']) && $item_array['hint'] != '') ? ' <span class="hint">(' . $item_array['hint'] . ')</span>' : '') . "</td>\n";
        } else {
            $string .= "<td>" . $item_array['title'] . ((isset($item_array['hint']) && $item_array['hint'] != '') ? ' <span class="hint">(' . $item_array['hint'] . ')</span>' : '') . "</td>\n";
        }

        $string .= "<td>";
        $string .= '<div id="geodata" coords="' . $this->getConfigValue('apps.geodata.new_map_center') . '">';
        $string .= "Lat: <input type=\"text\" geodata=\"lat\" name=\"" . $item_array['name'] . "[lat]\" value=\"" . (isset($item_array['value']['lat']) ? htmlspecialchars($item_array['value']['lat'], ENT_QUOTES, SITE_ENCODING) : '') . "\" size=\"" . $item_array['length'] . "\" />";
        $string .= "Lng: <input type=\"text\" geodata=\"lng\" name=\"" . $item_array['name'] . "[lng]\" value=\"" . (isset($item_array['value']['lng']) ? htmlspecialchars($item_array['value']['lng'], ENT_QUOTES, SITE_ENCODING) : '') . "\" size=\"" . $item_array['length'] . "\" />";
        $string .= '</div>';

        if (1 == $this->getConfigValue('apps.geodata.enable')) {
            if (!defined('NO_DYNAMIC_INCS') || !NO_DYNAMIC_INCS) {
                $string .= '<script src="' . SITEBILL_MAIN_URL . '/apps/system/js/md5.js"></script>';
            }
            $string .= '<script>$(document).ready(function(){$("#geodata").Geodata();});</script>';
        }

        $string .= "</td>\n";
        $string .= "</tr>\n";
        return $string;
    }

    /**
     * Get safe string input
     * @param array $item_array
     * @return string
     */
    function get_price_input($item_array)
    {
        if ($item_array['value'] != '') {
            $value = number_format((int)str_replace(' ', '', $item_array['value']), 0, ',', ' ');
        } else {
            $value = '';
        }
        $id = md5($item_array['name'] . '_' . rand(100, 999));
        $string = '';
        if (!defined('NO_DYNAMIC_INCS') || !NO_DYNAMIC_INCS) {
            $string .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/system/js/autoNumeric.js"></script>';
        }

        $string .= '<script type="text/javascript">$(document).ready(function() {$("input#' . $id . '").autoNumeric({aSep: \' \', vMax: \'999999999999\', vMin: \'0\'});});</script>';
        $string .= "<tr class=\"row3\" alt=\"" . $item_array['name'] . "\">\n";

        if ($item_array['required'] == "on") {
            $string .= "<td>" . $item_array['title'] . " <span style=\"color: red;\">*</span> </td>\n";
        } else {
            $string .= "<td>" . $item_array['title'] . "</td>\n";
        }

        $string .= "<td><input type=\"text\" id=\"" . $id . "\" name=\"" . $item_array['name'] . "\"  size=\"" . $item_array['length'] . "\" maxlength=\"" . $item_array['maxlength'] . "\" value=\"$value\" /></td>\n";
        $string .= "</tr>\n";

        return $string;
    }

    /**
     * Get safe string input for email
     * @param array $item_array
     * @return string
     */
    function get_email_input($item_array)
    {

        $string = '';
        $string .= "<tr  class=\"row3\" alt=\"" . $item_array['name'] . "\">\n";

        /* Mark required field with simbol '*' */
        if ($item_array['required'] == "on") {
            $string .= "<td>" . $item_array['title'] . " <span style=\"color: red;\">*</span> </td>\n";
        } else {
            $string .= "<td>" . $item_array['title'] . "</td>\n";
        }

        $string .= "<td><input type=\"text\" name=\"" . $item_array['name'] . "\" value=\"" . htmlspecialchars($item_array['value']) . "\" size=\"" . $item_array['length'] . "\" maxlength=\"" . $item_array['maxlength'] . "\"></td>\n";
        $string .= "</tr>\n";

        /* Return html code */
        return $string;
    }

    /**
     * Get password input
     * @param array $item_array
     * @return string
     */
    function get_password_input($item_array)
    {

        $string = '';
        $string .= "<tr  class=\"row3\" alt=\"" . $item_array['name'] . "\">\n";

        /* Mark required field with simbol '*' */
        if ($item_array['required'] == "on") {
            $string .= "<td>" . $item_array['title'] . " <span style=\"color: red;\">*</span> </td>\n";
        } else {
            $string .= "<td>" . $item_array['title'] . "</td>\n";
        }

        $string .= "<td><input type=\"password\" name=\"" . $item_array['name'] . "\" value=\"\" size=\"" . $item_array['length'] . "\" maxlength=\"" . $item_array['maxlength'] . "\"></td>\n";
        $string .= "</tr>\n";

        /* Return html code */
        return $string;
    }

    /**
     * Get photo input
     * @param array $item_array
     * @return string
     */
    function get_photo_input($item_array)
    {

        $string = '';
        $string .= "<tr  class=\"row3\" alt=\"" . $item_array['name'] . "\">\n";

        /* Mark required field with simbol '*' */
        if ($item_array['required'] == "on") {
            $string .= "<td>" . $item_array['title'] . " <span style=\"color: red;\">*</span> </td>\n";
        } else {
            $string .= "<td>" . $item_array['title'] . "</td>\n";
        }

        $string .= '<td>';
        if ($item_array['value'] != '') {
            $string .= '<div class="photo_element">';
            $string .= '<img src="' . SITEBILL_MAIN_URL . '/img/data/user/' . $item_array['value'] . '" border="0"/>';
            switch ($this->bootstrap_version) {
                case '3' :
                {
                    $string .= '<div class="checkbox"><label><input type="checkbox" name="delpic" value="yes">Удалить фото</label></div>';
                    break;
                }
                case '4md' :
                case '4' :
                {
                    $string .= '<label class="form-check-label"><input type="checkbox" class="form-check-input" name="delpic" value="yes">Удалить фото</label>';
                    break;
                }
                default :
                {
                    $string .= '<label class="checkbox"><input type="checkbox" name="delpic" value="yes"> Удалить фото</label>';
                }
            }
            $string .= '</div>';
            //$string .= '<img src="'.SITEBILL_MAIN_URL.'/img/data/user/'.$item_array['value'].'" border="0"/><br>';
        }
        $string .= '<input type="file" name="' . $item_array['name'] . '" />';
        $string .= '</td>';

        $string .= "</tr>\n";

        /* Return html code */
        return $string;
    }

    /**
     * Get hidden input
     * @param unknown_type $item_array
     * @return string
     */
    function get_hidden_input($item_array)
    {
        $string = '';
        $string .= '<input type="hidden" name="' . $item_array['name'] . '" value="' . $item_array['value'] . '" />';
        return $string;
    }

    function get_tlocation($item_array)
    {


        $string = '';


        $params = $item_array['parameters'];
        if (isset($params['visibles'])) {
            $visibles = explode('|', $params['visibles']);
        } else {
            $visibles = array();
        }

        if (isset($params['show_names'])) {
            $show_names = (int)$params['show_names'];
        } else {
            $show_names = 1;
        }

        if (isset($params['names'])) {
            $_x = explode('|', $params['names']);

            if (!empty($_x)) {
                foreach ($_x as $v) {
                    list($key, $title) = explode(':', $v);
                    $field_names[$key] = $title;
                }
            }
        } else {
            $field_names = array();
        }


        $defaults = array();
        if (isset($params['default_country_id'])) {
            $defaults['country_id'] = $params['default_country_id'];
        }
        if (isset($params['default_region_id'])) {
            $defaults['region_id'] = $params['default_region_id'];
        }
        if (isset($params['default_city_id'])) {
            $defaults['city_id'] = $params['default_city_id'];
        }
        if (isset($params['default_district_id'])) {
            $defaults['district_id'] = $params['default_district_id'];
        }

        $values = $item_array['value'];
        if ($values['country_id'] == 0) {
            $values['country_id'] = $defaults['country_id'];
        }
        if ($values['region_id'] == 0) {
            $values['region_id'] = $defaults['region_id'];
        }
        if ($values['city_id'] == 0) {
            $values['city_id'] = $defaults['city_id'];
        }

        $DBC = DBC::getInstance();


        $uniq_class_name = 'tlocation_object_' . md5(time() . '_' . rand(1000, 9999));

        $script_code = '<style>.tlocation_object select {display: block; margin: 10px 0;}</style>';
        if (!defined('NO_DYNAMIC_INCS') || !NO_DYNAMIC_INCS) {
            $script_code .= '<script src="' . SITEBILL_MAIN_URL . '/apps/tlocation/js/form_utils.js"></script>';
        }
        $script_code .= '<script>$(document).ready(function(){TLocationForm.setHandler("' . $uniq_class_name . '", ' . (int)$this->getConfigValue('link_street_to_city') . ')});</script>';

        $string = $script_code;

        $rs = '';

        if (empty($visibles) || (!empty($visibles) && in_array('country_id', $visibles))) {
            $data = array();
            $query = 'SELECT country_id, name FROM ' . DB_PREFIX . '_country ORDER BY name ASC';
            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $data[] = $ar;
                }
            }


            $rs .= '<span class="' . $uniq_class_name . '"><select name="country_id">';
            $rs .= '<option value="0" ' . $selected . '>--</option>';

            if (!empty($data)) {
                foreach ($data as $d) {
                    if ($values['country_id'] == $d['country_id']) {
                        $rs .= '<option value="' . $d['country_id'] . '" selected="selected">' . $d['name'] . '</option>';
                    } else {
                        $rs .= '<option value="' . $d['country_id'] . '">' . $d['name'] . '</option>';
                    }
                }
            }
            $rs .= '</select></span>';


            $string .= '<tr class="row3">';
            $string .= '<td>' . (($show_names && isset($field_names['country_id'])) ? $field_names['country_id'] : '') . '</td>';
            $string .= '<td>' . $rs . '</td>';
            $string .= '</tr>';
        }

        $rs = '';

        if (empty($visibles) || (!empty($visibles) && in_array('region_id', $visibles))) {
            $data = array();
            $stmt = FALSE;

            if ((int)$values['country_id'] != 0) {
                $query = 'SELECT region_id, name FROM ' . DB_PREFIX . '_region WHERE country_id=? ORDER BY name ASC';
                $stmt = $DBC->query($query, array($values['country_id']));
            } elseif (isset($defaults['country_id']) && (int)$defaults['country_id'] != 0) {
                $query = 'SELECT region_id, name FROM ' . DB_PREFIX . '_region WHERE country_id=? ORDER BY name ASC';
                $stmt = $DBC->query($query, array($defaults['country_id']));
            } elseif (!empty($visibles) && !in_array('country_id', $visibles)) {
                $query = 'SELECT region_id, name FROM ' . DB_PREFIX . '_region ORDER BY name ASC';
                $stmt = $DBC->query($query);
            }
            //echo $query;
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {

                    $data[] = $ar;
                }
            }


            $rs .= '<span class="' . $uniq_class_name . '"><select name="region_id">';
            $rs .= '<option value="0" ' . $selected . '>--</option>';

            if (!empty($data)) {
                foreach ($data as $d) {
                    if ($values['region_id'] == $d['region_id']) {
                        $rs .= '<option value="' . $d['region_id'] . '" selected="selected">' . $d['name'] . '</option>';
                    } else {
                        $rs .= '<option value="' . $d['region_id'] . '">' . $d['name'] . '</option>';
                    }
                }
            }
            $rs .= '</select></span>';

            $string .= '<tr class="row3">';
            $string .= '<td>' . (($show_names && isset($field_names['region_id'])) ? $field_names['region_id'] : '') . '</td>';
            $string .= '<td>' . $rs . '</td>';
            $string .= '</tr>';
        }

        $rs = '';

        if (empty($visibles) || (!empty($visibles) && in_array('city_id', $visibles))) {
            $data = array();
            $stmt = FALSE;
            if ((int)$values['region_id'] != 0) {
                $query = 'SELECT city_id, name FROM ' . DB_PREFIX . '_city WHERE region_id=? ORDER BY name ASC';
                $stmt = $DBC->query($query, array($values['region_id']));
            } elseif (isset($defaults['region_id']) && (int)$defaults['region_id'] != 0) {
                $query = 'SELECT city_id, name FROM ' . DB_PREFIX . '_city WHERE region_id=? ORDER BY name ASC';
                $stmt = $DBC->query($query, array($defaults['region_id']));
            } elseif (!empty($visibles) && !in_array('region_id', $visibles)) {
                $query = 'SELECT city_id, name FROM ' . DB_PREFIX . '_city ORDER BY name ASC';
                $stmt = $DBC->query($query);
            }

            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $data[] = $ar;
                }
            }


            $rs .= '<span class="' . $uniq_class_name . '"><select name="city_id">';
            $rs .= '<option value="0" ' . $selected . '>--</option>';

            if (!empty($data)) {
                foreach ($data as $d) {
                    if ($values['city_id'] == $d['city_id']) {
                        $rs .= '<option value="' . $d['city_id'] . '" selected="selected">' . $d['name'] . '</option>';
                    } else {
                        $rs .= '<option value="' . $d['city_id'] . '">' . $d['name'] . '</option>';
                    }
                }
            }
            $rs .= '</select></span>';

            $string .= '<tr class="row3">';
            $string .= '<td>' . (($show_names && isset($field_names['city_id'])) ? $field_names['city_id'] : '') . '</td>';
            $string .= '<td>' . $rs . '</td>';
            $string .= '</tr>';
        }

        $rs = '';


        if (1 == $this->getConfigValue('link_street_to_city')) {
            global $smarty;
            $smarty->assign('link_street_to_city', 1);

            $rs = '';

            if (empty($visibles) || (!empty($visibles) && in_array('district_id', $visibles))) {
                $data = array();
                $stmt = FALSE;
                if ((int)$values['city_id'] != 0) {
                    $query = 'SELECT id, name FROM ' . DB_PREFIX . '_district WHERE city_id=? ORDER BY name ASC';
                    $stmt = $DBC->query($query, array($values['city_id']));
                } elseif (isset($defaults['city_id']) && (int)$defaults['city_id'] != 0) {
                    $query = 'SELECT id, name FROM ' . DB_PREFIX . '_district WHERE city_id=? ORDER BY name ASC';
                    $stmt = $DBC->query($query, array($defaults['city_id']));
                } elseif (!empty($visibles) && !in_array('city_id', $visibles)) {
                    $query = 'SELECT id, name FROM ' . DB_PREFIX . '_district ORDER BY name ASC';
                    $stmt = $DBC->query($query);
                }

                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        $data[] = $ar;
                    }
                }


                $rs .= '<span class="' . $uniq_class_name . '"><select name="district_id">';
                $rs .= '<option value="0" ' . $selected . '>--</option>';

                if (!empty($data)) {
                    foreach ($data as $d) {
                        if ($values['district_id'] == $d['id']) {
                            $rs .= '<option value="' . $d['id'] . '" selected="selected">' . $d['name'] . '</option>';
                        } else {
                            $rs .= '<option value="' . $d['id'] . '">' . $d['name'] . '</option>';
                        }
                    }
                }
                $rs .= '</select></span>';

                $string .= '<tr class="row3">';
                $string .= '<td>' . (($show_names && isset($field_names['district_id'])) ? $field_names['district_id'] : '') . '</td>';
                $string .= '<td>' . $rs . '</td>';
                $string .= '</tr>';
            }

            $rs = '';

            if (empty($visibles) || (!empty($visibles) && in_array('street_id', $visibles))) {
                $data = array();
                $stmt = FALSE;
                if ((int)$values['city_id'] != 0) {
                    $query = 'SELECT street_id, name FROM ' . DB_PREFIX . '_street WHERE city_id=? ORDER BY name ASC';
                    $stmt = $DBC->query($query, array($values['city_id']));
                } elseif (isset($defaults['city_id']) && (int)$defaults['city_id'] != 0) {
                    $query = 'SELECT street_id, name FROM ' . DB_PREFIX . '_street WHERE city_id=? ORDER BY name ASC';
                    $stmt = $DBC->query($query, array($defaults['city_id']));
                } elseif (!empty($visibles) && !in_array('city_id', $visibles)) {
                    $query = 'SELECT street_id, name FROM ' . DB_PREFIX . '_street ORDER BY name ASC';
                    $stmt = $DBC->query($query);
                }

                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        $data[] = $ar;
                    }
                }


                $rs .= '<span class="' . $uniq_class_name . '"><select name="street_id">';
                $rs .= '<option value="0" ' . $selected . '>--</option>';

                if (!empty($data)) {
                    foreach ($data as $d) {
                        if ($values['street_id'] == $d['street_id']) {
                            $rs .= '<option value="' . $d['street_id'] . '" selected="selected">' . $d['name'] . '</option>';
                        } else {
                            $rs .= '<option value="' . $d['street_id'] . '">' . $d['name'] . '</option>';
                        }
                    }
                }
                $rs .= '</select></span>';

                $string .= '<tr class="row3">';
                $string .= '<td>' . (($show_names && isset($field_names['street_id'])) ? $field_names['street_id'] : '') . '</td>';
                $string .= '<td>' . $rs . '</td>';
                $string .= '</tr>';
            }
        } else {
            if (empty($visibles) || (!empty($visibles) && in_array('district_id', $visibles))) {
                $data = array();
                $stmt = FALSE;
                if ((int)$values['city_id'] != 0) {
                    $query = 'SELECT id, name FROM ' . DB_PREFIX . '_district WHERE city_id=? ORDER BY name ASC';
                    $stmt = $DBC->query($query, array($values['city_id']));
                } elseif (isset($defaults['city_id']) && (int)$defaults['city_id'] != 0) {
                    $query = 'SELECT id, name FROM ' . DB_PREFIX . '_district WHERE city_id=? ORDER BY name ASC';
                    $stmt = $DBC->query($query, array($defaults['city_id']));
                } elseif (!empty($visibles) && !in_array('city_id', $visibles)) {
                    $query = 'SELECT id, name FROM ' . DB_PREFIX . '_district ORDER BY name ASC';
                    $stmt = $DBC->query($query);
                }

                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        $data[] = $ar;
                    }
                }


                $rs .= '<span class="' . $uniq_class_name . '"><select name="district_id">';
                $rs .= '<option value="0" ' . $selected . '>--</option>';

                if (!empty($data)) {
                    foreach ($data as $d) {
                        if ($values['district_id'] == $d['id']) {
                            $rs .= '<option value="' . $d['id'] . '" selected="selected">' . $d['name'] . '</option>';
                        } else {
                            $rs .= '<option value="' . $d['id'] . '">' . $d['name'] . '</option>';
                        }
                    }
                }
                $rs .= '</select></span>';

                $string .= '<tr class="row3">';
                $string .= '<td>' . (($show_names && isset($field_names['district_id'])) ? $field_names['district_id'] : '') . '</td>';
                $string .= '<td>' . $rs . '</td>';
                $string .= '</tr>';
            }

            $rs = '';

            if (empty($visibles) || (!empty($visibles) && in_array('street_id', $visibles))) {

                $data = array();
                $stmt = FALSE;
                if ((int)$values['district_id'] != 0) {
                    $query = 'SELECT street_id, name FROM ' . DB_PREFIX . '_street WHERE district_id=? ORDER BY name ASC';
                    $stmt = $DBC->query($query, array($values['district_id']));
                } elseif (isset($defaults['district_id']) && (int)$defaults['district_id'] != 0) {
                    $query = 'SELECT street_id, name FROM ' . DB_PREFIX . '_street WHERE district_id=? ORDER BY name ASC';
                    $stmt = $DBC->query($query, array($defaults['district_id']));
                } elseif (!empty($visibles) && !in_array('district_id', $visibles)) {
                    $query = 'SELECT street_id, name FROM ' . DB_PREFIX . '_street ORDER BY name ASC';
                    $stmt = $DBC->query($query);
                }

                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        $data[] = $ar;
                    }
                }


                $rs .= '<span class="' . $uniq_class_name . '"><select name="street_id">';
                $rs .= '<option value="0" ' . $selected . '>--</option>';

                if (!empty($data)) {
                    foreach ($data as $d) {
                        if ($values['street_id'] == $d['street_id']) {
                            $rs .= '<option value="' . $d['street_id'] . '" selected="selected">' . $d['name'] . '</option>';
                        } else {
                            $rs .= '<option value="' . $d['street_id'] . '">' . $d['name'] . '</option>';
                        }
                    }
                }
                $rs .= '</select></span>';

                $string .= '<tr class="row3">';
                $string .= '<td>' . (($show_names && isset($field_names['street_id'])) ? $field_names['street_id'] : '') . '</td>';
                $string .= '<td>' . $rs . '</td>';
                $string .= '</tr>';
            }
        }


        return $string;
    }

    function get_select_box_by_query_as_checkboxes($item_array, $model = null)
    {
        $rs = '';
        $DBC = DBC::getInstance();
        $query = $item_array['query'];
        $stmt = $DBC->query($query);
        $rs .= '<div id="' . $item_array['name'] . '" class="select_box_by_query_as_checkboxes">';
        if (!is_array($item_array['value'])) {
            $item_array['value'] = array();
        }
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $this->total_in_select[$item_array['name']]++;
                $value = $ar[$item_array['value_name']];
                $value = trim($value);
                //$value = htmlspecialchars_decode($value);
                $value = htmlspecialchars($value, ENT_QUOTES, SITE_ENCODING);

                if (in_array($ar[$item_array['primary_key_name']], $item_array['value'])) {
                    $selected = 'checked="checked"';
                } else {
                    $selected = '';
                }
                $rs .= '<div><input type="checkbox"' . $selected . ' value="' . $ar[$item_array['primary_key_name']] . '" name="' . $item_array['name'] . '[]" /><span>' . $value . '</span></div>';
            }
        }
        $rs .= '</div>';
        //$rs .= '</select>';
        //$rs .= '</div>';

        return $rs;
    }

}
