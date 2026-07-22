<?php
trait FormCompileComplexTrait
{
    function compile_captcha_element($item_array)
    {

        $captcha_type = $this->getConfigValue('captcha_type');
        $id = 'captcha_refresh_' . md5(time() . rand(100, 999));
        if ($captcha_type == 2) {
            return FALSE;
        } /*elseif ($captcha_type == 4) {

            $string .= '<div class="g-recaptcha" data-sitekey="'.$this->getConfigValue('google_recaptcha_key').'"></div>';
        }*/ elseif ($captcha_type == 3) {

            $captcha_session_key = $this->generateCaptchaSessionKey();

            $string = '<img id="capcha_img" class="capcha_img" src="' . SITEBILL_MAIN_URL . '/apps/third/kcaptcha/index.php?captcha_session_key=' . $captcha_session_key . '" width="180" height="80" />';
            $string .= '<br /><a href="javascript:void(0);" rel="nofollow" id="' . $id . '" class="captcha_refresh">' . Multilanguage::_('CAPTCHA_REFR', 'system') . '</a>';
            $string .= '<br /><input type="text" placeholder="' . $item_array['title'] . '" class="' . $this->classes['input'] . '" name="' . $item_array['name'] . '" value="" />';
            $string .= '<input type="hidden" name="captcha_session_key" value="' . $captcha_session_key . '">';

            $js_string = '';
            if (!defined('NO_DYNAMIC_INCS') || !NO_DYNAMIC_INCS) {
                $js_string .= '<script src="' . SITEBILL_MAIN_URL . '/apps/system/js/md5.js"></script>';
            }


            $js_string .= '<script type="text/javascript">';
            $js_string .= '$(document).ready(function(){
                $("#' . $id . '").click(function(){
                    var hash = "s"+new Date().getTime();
                    $(this).prevAll(".capcha_img").eq(0).attr("src", estate_folder+\'/apps/third/kcaptcha/index.php?captcha_session_key=\' + hash);
                    $(this).nextAll("input[name=captcha_session_key]").val(hash);
                });
            });';
            $js_string .= '</script>';
            $string .= $js_string;

            $html_array['src'] = SITEBILL_APPS_DIR . '/third/kcaptcha/index.php?captcha_session_key=' . $captcha_session_key;
        } else {
            $captcha_session_key = $this->generateCaptchaSessionKey();

            $string = '<img id="capcha_img" class="capcha_img" src="' . SITEBILL_MAIN_URL . '/captcha.php?captcha_session_key=' . $captcha_session_key . '" width="180" height="80" />';
            $string .= '<br /><a href="javascript:void(0);" rel="nofollow" id="' . $id . '" class="captcha_refresh">' . Multilanguage::_('CAPTCHA_REFR', 'system') . '</a>';
            $string .= '<br /><input type="text" placeholder="' . $item_array['title'] . '" class="' . $this->classes['input'] . '" name="' . $item_array['name'] . '" value="" />';
            $string .= '<input type="hidden" name="captcha_session_key" value="' . $captcha_session_key . '">';

            $js_string = '';
            if (!defined('NO_DYNAMIC_INCS') || !NO_DYNAMIC_INCS) {
                $js_string .= '<script src="' . SITEBILL_MAIN_URL . '/apps/system/js/md5.js"></script>';
            }

            $js_string .= '<script type="text/javascript">';
            $js_string .= '$(document).ready(function(){
                $("#' . $id . '").click(function(){
                    //var new_key=new Date().getTime();
                    //var hash = CryptoJS.MD5(String(new_key));
                    var hash = "s"+new Date().getTime();
                    $(this).prevAll(".capcha_img").eq(0).attr("src", estate_folder+\'/captcha.php?captcha_session_key=\' + hash);
                    $(this).nextAll("input[name=captcha_session_key]").val(hash);
                });
            });';
            $js_string .= '</script>';
            $string .= $js_string;

            $html_array['src'] = SITEBILL_MAIN_URL . '/captcha.php?captcha_session_key=' . $captcha_session_key;
        }
        $html_array['refresh'] = '<a href="javascript:void(0);" rel="nofollow" id="' . $id . '" class="captcha_refresh">' . Multilanguage::_('CAPTCHA_REFR', 'system') . '</a>';
        $html_array['hidden'] = '<input type="hidden" name="captcha_session_key" value="' . $captcha_session_key . '">';
        $html_array['input'] = '<input placeholder="' . $item_array['title'] . '" type="text" class="' . $this->classes['input'] . '" name="' . $item_array['name'] . '" value=""  />';
        $html_array['js_string'] = $js_string;

        /*if ($captcha_type == 4){
            $html_array['refresh'] = '';
            $html_array['hidden'] = '';
            $html_array['input'] = $string;
            $html_array['js_string'] = '';
            $html_array['src']='';
        }*/

        $this->clear_captcha_session_table();


        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $string,
            'html_array' => $html_array,
            'type' => $item_array['type'],
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : '')
        );
    }

    function compile_client_id_element($item_array)
    {
        $value = intval($item_array['value']);
        $params = $item_array['parameters'];

        $id = md5('clientselect_' . time() . rand(100, 999));

        $script_code = '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/client/js/clientselect.js"></script>';
        $html = '<style>.found-contractors {border: 1px dashed hsl(210, 14%, 53%); padding: 10px; margin: 0 0 5px 0; font-size: 12px!important;} .phone { margin-left: 10px;}</style>';

        $html .= '<script>$(document).ready(function(){$("#' . $id . '").ClientSelect({selected_contractor: ' . $value . '});});</script>';
        $html .= '<div class="" id="' . $id . '">';
        $html .= '<input type="hidden" name="' . $item_array['name'] . '" id="id-contractor" value="' . $value . '" class="' . $this->classes['input'] . '">';
        if ($item_array['value_string'] != '') {
            $html .= '<div class="existing-contractor" style="display: block;">' . $item_array['value_string'] . '</div>';
        } else {
            $html .= '<div class="existing-contractor" style="display: none;"></div>';
        }
        if ($value != 0) {
            $html .= '<div class="contractor" style="display: none;">
            <div class="input text">
                <label for="ContractorSearchPhone">Введите от 4 цифр телефона для поиска</label>
                <input name="data[Contractor][search_phone]" class="search-contractor" type="text" id="ContractorSearchPhone" maxlength="17" value="">
            </div>
            <div class="found-contractors" style="display: none;"></div>
        </div>';
            $html .= '<div class="new-contractor" style="display: none;">
            <div class="input text"><label for="ContractorFio">Имя</label><input alt="fio" class="search-contractor" maxlength="255" type="text" id="ContractorFio"></div>
            <div class="input tel"><label for="ContractorPhone">Телефон</label><input alt="phone" class="search-contractor" maxlength="255" type="tel" id="ContractorPhone">
                    </div>
            <button class="new-contractor-button-save" style="display: block;">Создать</button>
        </div>
    <button class="new-contractor-button" style="display: block;">Создать нового</button>';

            $html .= '<button class="search-contractor-button" style="display: none;">Искать</button>';
        } else {
            $html .= '<div class="contractor" style="display: block;">
            <div class="input text"><label for="ContractorSearchPhone">Введите от 4 цифр телефона для поиска</label>
                <input name="data[Contractor][search_phone]" class="search-contractor" type="text" id="ContractorSearchPhone" maxlength="17" autocomplete="off" value="">
            </div>
            <div class="found-contractors" style="display: none;"></div>
        </div>';
            $html .= '<div class="new-contractor" style="display: none;">
            <div class="input text"><label for="ContractorFio">Имя</label><input alt="fio" class="search-contractor" maxlength="255" type="text" id="ContractorFio"></div>
            <div class="input tel"><label for="ContractorPhone">Телефон</label><input alt="phone" class="search-contractor" maxlength="255" type="tel" id="ContractorPhone">
                    </div>
            <button class="new-contractor-button-save" style="display: block;">Создать</button></div>
    <button class="new-contractor-button" style="display: block;">Создать нового</button>';
            $html .= '<button class="search-contractor-button" style="display: none;">Искать</button>';
        }

        $html .= '</div>';


        $collection = array();
        $collection[] = array(
            'title' => $item_array['title'],
            'name' => $item_array['name'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $html,
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );

        $answer = new stdClass();
        $answer->collection = $collection;
        $answer->scripts = array($script_code);
        //print_r($answer);
        return $answer;
    }

    function compile_gadres_element($item_array)
    {
        global $smarty;
        $tpl = $this->get_field_tpl($item_array['type'], $item_array['table_name'], $item_array['name']);
        $id = md5(rand(1000, 9999) . time());
        $str = '';
        if ($tpl) {
            $smarty->assign('id', $id);
            $smarty->assign('classes', $this->classes);
            $smarty->assign('item_array', $item_array);
            $smarty->assign('theme', $this->getConfigValue('theme'));
            $smarty->assign('NO_DYNAMIC_INCS', defined('NO_DYNAMIC_INCS') ? NO_DYNAMIC_INCS : false);
            $str = $smarty->fetch($tpl);
        } else {

            $params = $item_array['parameters'];
            $value = htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING);
            $str = '<script>$(document).ready(function(){$( "#gadres_' . $id . '" ).autocomplete({
            open: function() {
                $(".ui-menu")
                    .width($( this ).width());
            } ,
            source: function( request, response ) {
                var answer=[];
                var city_id=$( "#gadres_' . $id . '" ).parents("form").eq(0).find("[name=city_id]").val();
                $.ajax({
                    url: estate_folder + "/apps/geodata/js/ajax.php",
                    type: "POST",
                    dataType: "json",
                    data: {input: encodeURIComponent(request.term), action: "geocode_me", city_id: city_id},
                    success: function(data) {
                        $.map(data,function(n,i){
                            var o={};
                            o.value=n;
                            o.label=n;
                            answer.push(o);
                        });
                        response(answer);
                    }
                });
            },
            minLength: 3,
        });});</script>';
            $str .= '<input type="hidden" name="gadres[' . $item_array['name'] . ']" value="' . $value . '"><input class="' . $this->classes['input'] . '" id="gadres_' . $id . '" type="text" name="' . $item_array['name'] . '" value="" placeholder="' . $value . '"' . ((isset($params['styles']) && $params['styles'] != '') ? ' style="' . $params['styles'] . '"' : '') . ' />';
        }
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $str,
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_tlocation_element($item_array)
    {


        $collection = array();
        $is_script_attached = false;
        $autocomplete = false;


        $rets = array();
        $params = $item_array['parameters'];

        if (isset($params['autocomplete']) && $params['autocomplete'] == 1) {
            $autocomplete = true;
        }

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

        if (isset($params['default_titles'])) {
            $_x = array();
            $_x = explode('|', $params['default_titles']);

            if (!empty($_x)) {
                foreach ($_x as $v) {
                    list($key, $title) = explode(':', $v);
                    $default_titles[$key] = $title;
                }
            }
        } else {
            $default_titles = array();
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
        if (!isset($values['country_id'])) {
            $values['country_id'] = 0;
        }
        if (!isset($values['region_id'])) {
            $values['region_id'] = 0;
        }
        if (!isset($values['city_id'])) {
            $values['city_id'] = 0;
        }
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
        $script_code = '';
        if ($autocomplete) {
            if (!defined('NO_DYNAMIC_INCS') || !NO_DYNAMIC_INCS) {
                $script_code .= '<link rel="stylesheet" href="' . SITEBILL_MAIN_URL . '/apps/system/js/bootstrap/css/bootstrap-combobox.css" media="screen">';
                $script_code .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/system/js/bootstrap/js/bootstrap-combobox.js"></script>';
            }
        }
        $script_code .= '<style>.tlocation_object select {display: block; margin: 10px 0;}</style>';

        if (!defined('NO_DYNAMIC_INCS') || !NO_DYNAMIC_INCS) {
            $script_code .= '<script src="' . SITEBILL_MAIN_URL . '/apps/tlocation/js/form_utils.js"></script>';
        }
        $script_code .= '<script>$(document).ready(function(){TLocationForm.setHandler("' . $uniq_class_name . '", ' . (int)$this->getConfigValue('link_street_to_city') . '' . ($autocomplete ? ', 1' : '') . ')});</script>';

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
            /*
              if(!$is_script_attached){
              $rs.=$script_code;
              $is_script_attached=true;
              }
             */
            $rs .= '<span class="' . $uniq_class_name . '"><select name="country_id">';
            if ($autocomplete) {
                $rs .= '<option></option>';
            } else {
                $rs .= '<option value="0">' . (isset($default_titles['country_id']) ? $default_titles['country_id'] : '--') . '</option>';
            }

            /*
              $rs .= (($show_names && isset($field_names['country_id'])) ? '<label>'.$field_names['country_id'].'</label>' : '').'<select name="country_id">';
              $rs .= '<option value="0" '.$selected.'>--</option>';
             */
            if (!empty($data)) {
                foreach ($data as $d) {
                    if ($values['country_id'] == $d['country_id']) {
                        $rs .= '<option value="' . $d['country_id'] . '" selected="selected">' . $d['name'] . '</option>';
                    } else {
                        $rs .= '<option value="' . $d['country_id'] . '">' . $d['name'] . '</option>';
                    }
                }
            }
            //$rs .= '</select>';
            $rs .= '</select></span>';
            $collection[] = array(
                'title' => (($show_names && isset($field_names['country_id'])) ? $field_names['country_id'] : ''),
                'name' => 'country_id',
                'required' => 0,
                'html' => $rs,
                'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
                'type' => $item_array['type']
            );
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
            /*
              $rs .= (($show_names && isset($field_names['region_id'])) ? '<label>'.$field_names['region_id'].'</label>' : '').'<select name="region_id">';
              $rs .= '<option value="0" '.$selected.'>--</option>';
             */
            /* if(!$is_script_attached){
              $rs.=$script_code;
              $is_script_attached=true;
              } */

            $rs .= '<span class="' . $uniq_class_name . '"><select name="region_id">';
            if ($autocomplete) {
                $rs .= '<option></option>';
            } else {
                $rs .= '<option value="0" ' . $selected . '>' . (isset($default_titles['region_id']) ? $default_titles['region_id'] : '--') . '</option>';
            }


            if (!empty($data)) {
                foreach ($data as $d) {
                    if ($values['region_id'] == $d['region_id']) {
                        $rs .= '<option value="' . $d['region_id'] . '" selected="selected">' . $d['name'] . '</option>';
                    } else {
                        $rs .= '<option value="' . $d['region_id'] . '">' . $d['name'] . '</option>';
                    }
                }
            }
            //$rs .= '</select>';

            $rs .= '</select></span>';

            $collection[] = array(
                'title' => (($show_names && isset($field_names['region_id'])) ? $field_names['region_id'] : ''),
                'name' => 'region_id',
                'required' => 0,
                'html' => $rs,
                'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
                'type' => $item_array['type']
            );
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
            /*
              $rs .= (($show_names && isset($field_names['city_id'])) ? '<label>'.$field_names['city_id'].'</label>' : '').'<select name="city_id">';
              $rs .= '<option value="0" '.$selected.'>--</option>';

              if(!$is_script_attached){
              $rs.=$script_code;
              $is_script_attached=true;
              }
             */
            $rs .= '<span class="' . $uniq_class_name . '"><select name="city_id">';
            if ($autocomplete) {
                $rs .= '<option></option>';
            } else {
                $rs .= '<option value="0" ' . $selected . '>' . (isset($default_titles['city_id']) ? $default_titles['city_id'] : '--') . '</option>';
            }


            if (!empty($data)) {
                foreach ($data as $d) {
                    if ($values['city_id'] == $d['city_id']) {
                        $rs .= '<option value="' . $d['city_id'] . '" selected="selected">' . $d['name'] . '</option>';
                    } else {
                        $rs .= '<option value="' . $d['city_id'] . '">' . $d['name'] . '</option>';
                    }
                }
            }
            //$rs .= '</select>';
            $rs .= '</select></span>';

            $collection[] = array(
                'title' => (($show_names && isset($field_names['city_id'])) ? $field_names['city_id'] : ''),
                'name' => 'city_id',
                'required' => 0,
                'html' => $rs,
                'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
                'type' => $item_array['type']
            );
        }

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

            /* if(!$is_script_attached){
              $rs.=$script_code;
              $is_script_attached=true;
              } */

            $rs .= '<span class="' . $uniq_class_name . '"><select name="district_id" data-placeholder="' . (isset($default_titles['district_id']) ? $default_titles['district_id'] : '--') . '">';
            if ($autocomplete) {
                $rs .= '<option></option>';
            } else {
                $rs .= '<option value="0" ' . $selected . '>' . (isset($default_titles['district_id']) ? $default_titles['district_id'] : '--') . '</option>';
            }
            //

            /*
              $rs .= (($show_names && isset($field_names['district_id'])) ? '<label>'.$field_names['district_id'].'</label>' : '').'<select name="district_id">';
              $rs .= '<option value="0" '.$selected.'>--</option>';
             */
            if (!empty($data)) {
                foreach ($data as $d) {
                    if ($values['district_id'] == $d['id']) {
                        $rs .= '<option value="' . $d['id'] . '" selected="selected">' . $d['name'] . '</option>';
                    } else {
                        $rs .= '<option value="' . $d['id'] . '">' . $d['name'] . '</option>';
                    }
                }
            }
            //$rs .= '</select>';
            $rs .= '</select></span>';

            $collection[] = array(
                'title' => (($show_names && isset($field_names['district_id'])) ? $field_names['district_id'] : ''),
                'name' => 'district_id',
                'required' => 0,
                'html' => $rs,
                'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
                'type' => $item_array['type']
            );
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

            /* if(!$is_script_attached){
              $rs.=$script_code;
              $is_script_attached=true;
              } */

            $rs .= '<span class="' . $uniq_class_name . '"><select name="street_id" data-placeholder="' . (isset($default_titles['street_id']) ? $default_titles['street_id'] : '--') . '">';

            if ($autocomplete) {
                $rs .= '<option></option>';
            } else {
                $rs .= '<option value="0" ' . $selected . '>' . (isset($default_titles['street_id']) ? $default_titles['street_id'] : '--') . '</option>';
            }


            /*
              $rs .= (($show_names && isset($field_names['street_id'])) ? '<label>'.$field_names['street_id'].'</label>' : '').'<select name="street_id">';
              $rs .= '<option value="0" '.$selected.'>--</option>';
             */
            if (!empty($data)) {
                foreach ($data as $d) {
                    if ($values['street_id'] == $d['street_id']) {
                        $rs .= '<option value="' . $d['street_id'] . '" selected="selected">' . $d['name'] . '</option>';
                    } else {
                        $rs .= '<option value="' . $d['street_id'] . '">' . $d['name'] . '</option>';
                    }
                }
            }
            //$rs .= '</select>';
            $rs .= '</select></span>';

            $collection[] = array(
                'title' => (($show_names && isset($field_names['street_id'])) ? $field_names['street_id'] : ''),
                'name' => 'street_id',
                'required' => 0,
                'html' => $rs,
                'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
                'type' => $item_array['type']
            );
        }

        /*

          if(1==$this->getConfigValue('link_street_to_city')){
          global $smarty;
          $smarty->assign('link_street_to_city', 1);
          if(empty($visibles) || (!empty($visibles) && in_array('street_id', $visibles))){
          $data=array();
          $stmt=FALSE;
          if((int)$values['city_id']!=0){
          $query='SELECT street_id, name FROM '.DB_PREFIX.'_street WHERE city_id=? ORDER BY name ASC';
          $stmt=$DBC->query($query, array($values['city_id']));
          }elseif(isset($defaults['city_id']) && (int)$defaults['city_id']!=0){
          $query='SELECT street_id, name FROM '.DB_PREFIX.'_street WHERE city_id=? ORDER BY name ASC';
          $stmt=$DBC->query($query, array($defaults['city_id']));
          }elseif(!empty($visibles) && !in_array('city_id', $visibles)){
          $query='SELECT street_id, name FROM '.DB_PREFIX.'_street ORDER BY name ASC';
          $stmt=$DBC->query($query);
          }

          if($stmt){
          while($ar=$DBC->fetch($stmt)){
          $data[]=$ar;
          }
          }

          $rs .= (($show_names && isset($field_names['street_id'])) ? '<label>'.$field_names['street_id'].'</label>' : '').'<select name="street_id">';
          $rs .= '<option value="0" '.$selected.'>--</option>';

          if(!empty($data)){
          foreach($data as $d){
          if($values['street_id']==$d['street_id']){
          $rs .= '<option value="'.$d['street_id'].'" selected="selected">'.$d['name'].'</option>';
          }else{
          $rs .= '<option value="'.$d['street_id'].'">'.$d['name'].'</option>';
          }
          }
          }
          $rs .= '</select>';
          }

          }else{
          if(empty($visibles) || (!empty($visibles) && in_array('district_id', $visibles))){
          $data=array();
          $stmt=FALSE;
          if((int)$values['city_id']!=0){
          $query='SELECT id, name FROM '.DB_PREFIX.'_district WHERE city_id=? ORDER BY name ASC';
          $stmt=$DBC->query($query, array($values['city_id']));
          }elseif(isset($defaults['city_id']) && (int)$defaults['city_id']!=0){
          $query='SELECT id, name FROM '.DB_PREFIX.'_district WHERE city_id=? ORDER BY name ASC';
          $stmt=$DBC->query($query, array($defaults['city_id']));
          }elseif(!empty($visibles) && !in_array('city_id', $visibles)){
          $query='SELECT id, name FROM '.DB_PREFIX.'_district ORDER BY name ASC';
          $stmt=$DBC->query($query);
          }

          if($stmt){
          while($ar=$DBC->fetch($stmt)){
          $data[]=$ar;
          }
          }

          $rs .= (($show_names && isset($field_names['district_id'])) ? '<label>'.$field_names['district_id'].'</label>' : '').'<select name="district_id">';
          $rs .= '<option value="0" '.$selected.'>--</option>';

          if(!empty($data)){
          foreach($data as $d){
          if($values['district_id']==$d['id']){
          $rs .= '<option value="'.$d['id'].'" selected="selected">'.$d['name'].'</option>';
          }else{
          $rs .= '<option value="'.$d['id'].'">'.$d['name'].'</option>';
          }
          }
          }
          $rs .= '</select>';
          }

          if(empty($visibles) || (!empty($visibles) && in_array('street_id', $visibles))){

          $data=array();
          $stmt=FALSE;
          if((int)$values['district_id']!=0){
          $query='SELECT street_id, name FROM '.DB_PREFIX.'_street WHERE district_id=? ORDER BY name ASC';
          $stmt=$DBC->query($query, array($values['district_id']));
          }elseif(isset($defaults['district_id']) && (int)$defaults['district_id']!=0){
          $query='SELECT street_id, name FROM '.DB_PREFIX.'_street WHERE district_id=? ORDER BY name ASC';
          $stmt=$DBC->query($query, array($defaults['district_id']));
          }elseif(!empty($visibles) && !in_array('district_id', $visibles)){
          $query='SELECT street_id, name FROM '.DB_PREFIX.'_street ORDER BY name ASC';
          $stmt=$DBC->query($query);
          }

          if($stmt){
          while($ar=$DBC->fetch($stmt)){
          $data[]=$ar;
          }
          }

          $rs .= (($show_names && isset($field_names['street_id'])) ? '<label>'.$field_names['street_id'].'</label>' : '').'<select name="street_id">';
          $rs .= '<option value="0" '.$selected.'>--</option>';

          if(!empty($data)){
          foreach($data as $d){
          if($values['street_id']==$d['street_id']){
          $rs .= '<option value="'.$d['street_id'].'" selected="selected">'.$d['name'].'</option>';
          }else{
          $rs .= '<option value="'.$d['street_id'].'">'.$d['name'].'</option>';
          }
          }
          }
          $rs .= '</select>';
          }
          }

         */

        $answer = new stdClass();
        $answer->collection = $collection;
        $answer->scripts = array($script_code);
        //print_r($answer);
        return $answer;

        $rs .= '</div>';


        return array(
            'title' => $item_array['title'],
            'required' => 0,
            'html' => $rs,
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : '')
        );
    }

    function compile_parameter_element($item_array)
    {
        global $smarty;
        $tpl = $this->get_field_tpl($item_array['type'], $item_array['table_name'], $item_array['name']);
        $id = md5(rand(1000, 9999) . time());
        $html = '';
        if ($tpl) {
            $smarty->assign('id', $id);
            $smarty->assign('classes', $this->classes);
            $smarty->assign('item_array', $item_array);
            $smarty->assign('theme', $this->getConfigValue('theme'));
            $smarty->assign('NO_DYNAMIC_INCS', defined('NO_DYNAMIC_INCS') ? NO_DYNAMIC_INCS : false);
            $html = $smarty->fetch($tpl);
        } else {

            //$value=htmlspecialchars($item_array['value']);
            $html = '';
            $html .= '<script type="text/javascript">';
            $html .= '$(document).ready(function(){
            $(document).on("click", ".paramsrow a", function(){$(this).parents(".paramsrow").eq(0).remove();return false;});
            $("#add_column_params").click(function(){
                var pr=$(this).parents("#paramsblock").eq(0).find(".paramsrow:last").clone();
                $(this).before(pr);
                return false;
            });
        });';
            $html .= '</script>';
            $html .= '<div id="paramsblock">';
            if (is_array($item_array['value']) && count($item_array['value']) > 0) {
                foreach ($item_array['value'] as $pk => $pv) {
                    $html .= '<div class="paramsrow">';
                    $html .= '<input type="text" name="parameters[name][]" value="' . $pk . '" />=<input type="text" name="parameters[value][]" value="' . $pv . '" />';
                    $html .= '<a href="javascript:void(0);">x</a>';
                    $html .= '</div>';
                }
            }
            $html .= '<div class="paramsrow">';
            $html .= '<input type="text" name="parameters[name][]" value="" />=<input type="text" name="parameters[value][]" value="" />';
            $html .= '<a href="javascript:void(0);">x</a>';
            $html .= '</div>';
            $html .= '<button id="add_column_params">Add</button></div>';

        }
        return array(
            'title' => $item_array['title'],
            'required' => 0,
            'html' => $html,
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_geodata_element($item_array)
    {
        $parameters = $item_array['parameters'];
        $value = $item_array['value'];
        $str = '';
        $map_options = array();
        $mapsizes = array();

        if (isset($parameters['map_width']) && trim($parameters['map_width']) != 0) {
            if (preg_match('/(\d+)%$/', $parameters['map_width'], $matches)) {
                $map_options[] = 'width: \'' . trim($parameters['map_width']) . '\'';
                $mapsizes[] = 'width: ' . trim($parameters['map_width']);
            } elseif (preg_match('/(\d+)px$/', $parameters['map_width'], $matches)) {
                $map_options[] = 'width: \'' . trim($parameters['map_width']) . '\'';
                $mapsizes[] = 'width: ' . trim($parameters['map_width']);
            } elseif (intval($parameters['map_width']) > 0) {
                $map_options[] = 'width: \'' . intval($parameters['map_width']) . 'px\'';
                $mapsizes[] = 'width: ' . intval($parameters['map_width']) . 'px';
            }

        }
        if (isset($parameters['map_height']) && trim($parameters['map_height']) != 0) {
            if (preg_match('/(\d+)%$/', $parameters['map_height'], $matches)) {
                $map_options[] = 'height: \'' . trim($parameters['map_height']) . '\'';
                $mapsizes[] = 'height: ' . trim($parameters['map_height']);
            } elseif (preg_match('/(\d+)px$/', $parameters['map_height'], $matches)) {
                $map_options[] = 'height: \'' . trim($parameters['map_height']) . '\'';
                $mapsizes[] = 'height: ' . trim($parameters['map_height']);
            } elseif (intval($parameters['map_height']) > 0) {
                $map_options[] = 'height: \'' . intval($parameters['map_height']) . 'px\'';
                $mapsizes[] = 'height: ' . intval($parameters['map_height']) . 'px';
            }

        }

        /*if (isset($parameters['map_width']) && (int) $parameters['map_width'] != 0) {
            $map_options[] = 'width: ' . (int) $parameters['map_width'];
        }
        if (isset($parameters['map_height']) && (int) $parameters['map_height'] != 0) {
            $map_options[] = 'height: ' . (int) $parameters['map_height'];
        }*/
        if (isset($parameters['map_view_type']) && trim($parameters['map_view_type']) != '') {
            $map_options[] = 'map_view_type: \'' . trim($parameters['map_view_type']) . '\'';
        }
        if (isset($parameters['confields']) && trim($parameters['confields']) != '') {
            $confields = explode(',', $parameters['confields']);
            $confields = array_map(function ($c) {
                return trim($c);
            }, $confields);
            $map_options[] = 'confields: [\'' . implode('\',\'', $confields) . '\']';
        } else {
            $map_options[] = 'confields: []';
        }

        if (isset($parameters['usemapsearch']) && intval($parameters['usemapsearch']) == 1) {
            $map_options[] = 'usemapsearch: true';
        }

        if (1 == $this->getConfigValue('apps.geodata.no_scroll_zoom')) {
            $map_options[] = 'no_scroll_zoom: 1';
        }

        $mtype = '';
        if (1 == $this->getConfigValue('use_google_map')) {
            $mtype = 'google';
        } elseif (2 == $this->getConfigValue('use_google_map')) {
            $mtype = 'leaflet_osm';
        } else {
            $mtype = 'yandex';
        }
        //$this->template->assert('map_type', '');


        $map_options[] = 'map_type: ' . '\'' . $mtype . '\'';
        $id = md5(time() . rand(100, 999));


        /*$str = '<div id="geodata_'.$id.'">';
        $str .= '<input type="hidden" geodata="lat" class="' . $this->classes['input'] . '" name="' . $item_array['name'] . '[lat]" value="' . (isset($value['lat']) ? $value['lat'] : '') . '" />';
        //$str.= $map_lat_input;
        $str .= '</div>';*/


        $map_div_open = $this->add_places_input();
        $map_div_open .= '<div id="geodata_' . $id . '" coords="' . $this->getConfigValue('apps.geodata.new_map_center') . '" zoom="' . $this->getConfigValue('apps.geodata.map_zoom_default') . '" class="geodata_form_el">';
        $map_div_close = '</div>';
        $str .= $map_div;
        //$str.='<div class="geodata_form_co">';
        $map_lat_input = '<input type="text" geodata="lat" class="' . $this->classes['input'] . '" name="' . $item_array['name'] . '[lat]" value="' . (isset($value['lat']) ? $value['lat'] : '') . '" />';
        //$str.= $map_lat_input;
        $map_lng_input = '<input type="text" geodata="lng" class="' . $this->classes['input'] . '" name="' . $item_array['name'] . '[lng]" value="' . (isset($value['lng']) ? $value['lng'] : '') . '" />';
        $map_div_map = '<div class="geodata_map_holder" style="' . (!empty($mapsizes) ? implode('; ', $mapsizes) . ';' : '') . '"></div>';
        //$str.= $map_lng_input;
        //$str.='</div>';
        //$str.='</div>';
        $str .= $map_div_open;
        $str .= '<div class="geodata_form_co"><div class="geodata_form_name"></div><input type="hidden" name="geodata" value="">';
        $str .= $map_lat_input;
        $str .= $map_lng_input;
        $str .= '</div>';
        $str .= $map_div_map;
        $str .= $map_div_close;
        $map_js_string = '';
        if (1 == $this->getConfigValue('apps.geodata.enable')) {
            if (!defined('NO_DYNAMIC_INCS') || !NO_DYNAMIC_INCS) {
                //$map_js_string .= '<script src="'.SITEBILL_MAIN_URL.'/apps/system/js/md5.js"></script>';
            }
            $map_js_string .= '<script>$(document).ready(function(){$("#geodata_' . $id . '").Geodata(' . (count($map_options) > 0 ? '{' . implode(',', $map_options) . '}' : '') . ');});</script>';
        }
        $str .= $map_js_string;

        return array(
            'title' => $item_array['title'],
            'type' => $item_array['type'],
            'map_div_open' => $map_div_open,
            'map_div_close' => $map_div_close,
            'map_div_map' => $map_div_map,
            'map_lat_input' => $map_lat_input,
            'map_lng_input' => $map_lng_input,
            'map_js_string' => $map_js_string,
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $str,
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : '')
        );
    }

    function add_places_input()
    {
        if ($this->getConfigValue('apps.geodata.use_google_places_api')) {
            return $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/system/fields/places-input.tpl');
        }
        return '';
    }

    function compile_date_element($item_array)
    {
        $value = htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING);
        $string = '';
        /* $string .= '
          <script type="text/javascript">
          $(document).ready(function() {
          $( "#'.$item_array['name'].'" ).datepicker({
          showOn: "button",
          dateFormat: "dd.mm.yy",
          buttonImage: "'.SITEBILL_MAIN_URL.'/img/calendar.gif",
          buttonImageOnly: true
          });
          });
          </script>
          '; */
        $string .= '
            <script type="text/javascript">
                $(document).ready(function() {
                    $( "#' . $item_array['name'] . '" ).datepicker({dateFormat: "dd.mm.yy"});
                });
            </script>
        ';
        //echo $item_array['value'];
        /* if($item_array['value']==='' || $item_array['value']===0){
          $item_array['value'] = date('d.m.Y', time());
          //$item_array['value'] = '';
          }else */
        if (preg_match('/(\d{4,4})-(\d{2,2})-(\d{2,2}) (\d{2,2}):(\d{2,2}):(\d{2,2})/', $item_array['value'])) {
            $item_array['value'] = date('d.m.Y', strtotime($item_array['value']));
        } elseif (preg_match('/(\d{4,4})-(\d{2,2})-(\d{2,2})/', $item_array['value'])) {
            $item_array['value'] = date('d.m.Y', strtotime($item_array['value']));
        } elseif ($item_array['value'] == 0 || $item_array['value'] == '') {

            $item_array['value'] = '';
        } else {
            $item_array['value'] = date('d.m.Y', $item_array['value']);
        }
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $string . '<input class="' . $this->classes['input'] . '" type="text" id="' . $item_array['name'] . '" name="' . $item_array['name'] . '" value="' . $item_array['value'] . '" />',
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_dtdatetime_element($item_array)
    {

        $id = $item_array['name'] . '_' . md5(time() . rand(100, 999));

        $parameters = $item_array['parameters'];

        $date_formattype = $this->getConfigValue('date_format');

        $formattypes = Sitebill_Datetime::getFormats();

        if ($date_formattype != '' && isset($formattypes[$date_formattype])) {
            $date_formattype = $formattypes[$date_formattype];
        } else {
            $date_formattype = $formattypes['standart'];
        }

        $pickDate = 'pickDate: true';
        $pickTime = 'pickTime: true';
        if (is_array($parameters) && $parameters['noSeconds'] == 1) {
            $pickSeconds = 'pickSeconds: false';
            $format = 'format: "' . $date_formattype . ' hh:mm"';
        } else {
            $format = 'format: "' . $date_formattype . ' hh:mm:ss"';
        }
        $tpp = $format . ', ' . $pickDate . ', ' . $pickTime . ', ' . $pickSeconds;
        $value = htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING);

        if ($value != '' && $value != 'now') {
            $value = Sitebill_Datetime::getDatetimeFormattedFromCanonical($value);
        } elseif ($value == 'now') {
            $value = Sitebill_Datetime::getDatetimeFormattedFromCanonical(date('Y-m-d H:i:s', time()));
        } else {
            $value = '';
        }
        $string = '';
        if (!defined('NO_DYNAMIC_INCS') || !NO_DYNAMIC_INCS) {
            $string .= '<link rel="stylesheet" href="' . SITEBILL_MAIN_URL . '/apps/system/js/bootstrap/css/bootstrap-datetimepicker.min.css" media="screen">';
            $string .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/system/js/bootstrap/js/bootstrap-datetimepicker.min.js"></script>';
        }

        $string .= '<script type="text/javascript">$(document).ready(function() {$( "#' . $id . ' div.dt-element" ).datetimepicker({pick12HourFormat: false,language: "ru",' . $tpp . '});});</script>';
        $bootstrap_version = trim($this->getConfigValue('bootstrap_version'));
        if ($bootstrap_version == '3' && !defined('ADMIN_MODE')) {
            $html = '<div class="input-group input-append date dt-element"><input class="' . $this->classes['input'] . '" data-format="" type="text" name="' . $item_array['name'] . '" value="' . $value . '"></input><div class="add-on input-group-addon"><span class="glyphicon glyphicon-calendar"></span></div></div>';
        } else {
            $html = '<div class="input-append date dt-element"><input class="' . $this->classes['input'] . '" data-format="" type="text" name="' . $item_array['name'] . '" value="' . $value . '"></input><span class="add-on"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i></span></div>';
        }
        $html = '<div id="' . $id . '">' . $html . '</div>';
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $string . $html,
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_dtdate_element($item_array)
    {

        $id = $item_array['name'] . '_' . md5(time() . rand(100, 999));

        $parameters = $item_array['parameters'];

        $date_formattype = $this->getConfigValue('date_format');
        $date_formattype_code = $this->getConfigValue('date_format');

        $formattypes = Sitebill_Datetime::getFormats();

        if ($date_formattype != '' && isset($formattypes[$date_formattype])) {
            $date_formattype = $formattypes[$date_formattype];
        } else {
            $date_formattype = $formattypes['standart'];
        }

        $pickDate = 'pickDate: true';
        $pickTime = 'pickTime: false';
        $format = 'format: "' . $date_formattype . '"';
        $tpp = $format . ', ' . $pickDate . ', ' . $pickTime;
        $value = htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING);
        if ($value == '' && $item_array['default_value'] == 'now') {
            $value = date('Y-m-d H:i:s', time());
        }
        $value = Sitebill_Datetime::getDateFormattedFromCanonical($value);
        $string = '';
        if (!defined('NO_DYNAMIC_INCS') || !NO_DYNAMIC_INCS) {
            $string .= '<link rel="stylesheet" href="' . SITEBILL_MAIN_URL . '/apps/system/js/bootstrap/css/bootstrap-datetimepicker.min.css" media="screen">';
            $string .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/system/js/bootstrap/js/bootstrap-datetimepicker.min.js"></script>';
        }

        $string .= '
            <script type="text/javascript">
                $(document).ready(function() {
                    $( "#' . $id . '" ).datetimepicker({
                        autoclose: true,
                        pick12HourFormat: false,
                        language: "ru",
                        ' . $tpp . '

                    });
                });
            </script>
        ';

        $bootstrap_version = trim($this->getConfigValue('bootstrap_version'));
        if ($bootstrap_version == '3' && !defined('ADMIN_MODE')) {
            $html = '<div id="' . $id . '" class="input-group input-append date"><input class="' . $this->classes['input'] . '" data-format="" type="text" placeholder="' . $item_array['title'] . '" name="' . $item_array['name'] . '" value="' . $value . '"></input><div class="add-on input-group-addon"><span class="glyphicon glyphicon-calendar"></span></div></div>';
        } else {
            $html = '<div id="' . $id . '" class="input-append date"><input class="' . $this->classes['input'] . '" data-format="" type="text" name="' . $item_array['name'] . '" value="' . $value . '"></input><span class="add-on"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i></span></div>';
        }

        //$html = '<div><input id="' . $item_array['name'] . '" class="' . $this->classes['input'] . '" data-format="" type="text" name="' . $item_array['name'] . '" value="' . $value . '"></input></div>';

        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            /*'html' => $string . '<div id="' . $item_array['name'] . '" class="input-append date"><input class="' . $this->classes['input'] . '" data-format-code="' . $date_formattype_code . '" data-format="" type="text" name="' . $item_array['name'] . '" value="' . $value . '"></input><span class="add-on"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i></span></div>',*/
            'html' => $string . $html,
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_dttime_element($item_array)
    {

        $id = $item_array['name'] . '_' . md5(time() . rand(100, 999));

        $parameters = $item_array['parameters'];

        $pickDate = 'pickDate: false';
        $pickTime = 'pickTime: true';
        if ($parameters['noSeconds'] == 1) {
            $pickSeconds = 'pickSeconds: false';
            $format = 'format: "hh:mm"';
        } else {
            $format = 'format: "hh:mm:ss"';
        }
        $tpp = $format . ', ' . $pickDate . ', ' . $pickTime . ', ' . $pickSeconds;
        //$value=htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING);
        $value = htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING);
        if ($value == '' && $item_array['default_value'] == 'now') {
            $value = date('Y-m-d H:i:s', time());
        }
        $value = Sitebill_Datetime::getTimeFormattedFromCanonical($value);
        $string = '';
        if (!defined('NO_DYNAMIC_INCS') || !NO_DYNAMIC_INCS) {
            $string .= '<link rel="stylesheet" href="' . SITEBILL_MAIN_URL . '/apps/system/js/bootstrap/css/bootstrap-datetimepicker.min.css" media="screen">';
            $string .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/system/js/bootstrap/js/bootstrap-datetimepicker.min.js"></script>';
        }

        $string .= '
            <script type="text/javascript">
                $(document).ready(function() {
                    $( "#' . $id . '" ).datetimepicker({
                        pick12HourFormat: false,
                        language: "ru",
                        ' . $tpp . '

                    });
                });
            </script>
        ';

        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $string . '<div id="' . $id . '" class="input-append date"><input class="' . $this->classes['input'] . '" data-format="" type="text" name="' . $item_array['name'] . '" value="' . $value . '"></input><span class="add-on"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i></span></div>',
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_datetime_element($item_array)
    {
        $parameters = $item_array['parameters'];


        $formattypes = array(
            'standart' => 'yyyy-MM-dd',
            'eu' => 'dd/MM/yyyy',
            'us' => 'MM/dd/yyyy',
        );
        if (isset($parameters['inFormFormat']) && isset($formattypes[$parameters['inFormFormat']])) {
            $date_formattype = $formattypes[$parameters['inFormFormat']];
        } else {
            $date_formattype = $formattypes['standart'];
        }

        $dformat = (isset($parameters['format']) ? $parameters['format'] : 'DT');

        if ($dformat != 'D' && $dformat != 'T') {
            $dformat = 'DT';
        }

        $pickSeconds = 'pickSeconds: true';
        $pickDate = 'pickDate: true';
        $pickTime = 'pickTime: true';
        if ($dformat == 'D') {
            $pickDate = 'pickDate: true';
            $pickTime = 'pickTime: false';
            $format = 'format: "' . $date_formattype . '"';
        } elseif ($dformat == 'T') {
            $pickDate = 'pickDate: false';
            $pickTime = 'pickTime: true';
            if ($parameters['noSeconds'] == 1) {
                $pickSeconds = 'pickSeconds: false';
                $format = 'format: "hh:mm"';
            } else {
                $format = 'format: "hh:mm:ss"';
            }
        } else {
            $pickDate = 'pickDate: true';
            $pickTime = 'pickTime: true';
            if ($parameters['noSeconds'] == 1) {
                $pickSeconds = 'pickSeconds: false';
                $format = 'format: "' . $date_formattype . ' hh:mm"';
            } else {
                $format = 'format: "' . $date_formattype . ' hh:mm:ss"';
            }
        }
        $tpp = $format . ', ' . $pickDate . ', ' . $pickTime . ', ' . $pickSeconds;
        $value = htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING);
        $string = '';
        if (!defined('NO_DYNAMIC_INCS') || !NO_DYNAMIC_INCS) {
            $string .= '<link rel="stylesheet" href="' . SITEBILL_MAIN_URL . '/apps/system/js/bootstrap/css/bootstrap-datetimepicker.min.css" media="screen">';
            $string .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/system/js/bootstrap/js/bootstrap-datetimepicker.min.js"></script>';
        }

        $string .= '
            <script type="text/javascript">
                $(document).ready(function() {
                    $( "#' . $item_array['name'] . '" ).datetimepicker({
                        pick12HourFormat: false,
                        language: "ru",
                        ' . $tpp . '

                    });
                });
            </script>
        ';

        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $string . '<div id="' . $item_array['name'] . '" class="input-append date"><input class="' . $this->classes['input'] . '" data-format="" type="text" name="' . $item_array['name'] . '" value="' . $value . '"></input><span class="add-on"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i></span></div>',
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

}
