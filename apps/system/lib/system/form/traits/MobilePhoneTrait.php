<?php
namespace system\lib\system\form\traits;

use system\traits\blade\BladeTrait;

trait MobilePhoneTrait {
    use BladeTrait;
    function compile_mobilephone_element($item_array)
    {
        $value = htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING);
        $id = 'id'.md5($item_array['name'] . '_' . rand(100, 999));
        $string = '';
        if (!defined('NO_DYNAMIC_INCS') || !NO_DYNAMIC_INCS) {
            $string = $this->get_mobilephone_input_js_plugin();
        }
        $string .= $this->get_mobilephone_input_init_script($id, $item_array);
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => '
            <input type="tel" id="' . $id . '_intl" name="' . $id . '_intl_name" value="' . ($value?'+'.$value:'') . '">
            <div id="' . $id . '_confirm_mobile"></div>
            <span id="' . $id . 'valid-msg" class="hide-intl">✓ '._e('Правильный').'</span>
            <span id="' . $id . 'error-msg" class="hide-intl"></span>            
            <input id="' . $id . '" class="' . $this->classes['input'] . '" type="hidden" name="' . $item_array['name'] . '" value="' . $value . '" />'.$string,
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    /**
     * Get safe string input for mobile phone number
     * @param array $item_array
     * @return string
     */
    function get_mobilephone_input($item_array)
    {

        /* Un-quote slashes */
        $value = htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING);
        $id = md5($item_array['name'] . '_' . rand(100, 999));
        $string = '';
        if (!defined('NO_DYNAMIC_INCS') || !NO_DYNAMIC_INCS) {
            $string = $this->get_mobilephone_input_js_plugin();
        }
        $string .= $this->get_mobilephone_input_init_script($id, $item_array);

        $string .= "<tr  class=\"row3\" alt=\"" . $item_array['name'] . "\">\n";

        if ($item_array['required'] == "on") {
            $string .= "<td>" . $item_array['title'] . " <span style=\"color: red;\">*</span> </td>\n";
        } else {
            $string .= "<td>" . $item_array['title'] . "</td>\n";
        }

        $string .= "<td><input id=\"" . $id . "\" type=\"text\" name=\"" . $item_array['name'] . "\" value=\"" . htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING) . "\" size=\"" . $item_array['length'] . "\" maxlength=\"" . $item_array['maxlength'] . "\"></td>\n";
        $string .= "</tr>\n";

        return $string;
    }

    function get_mobilephone_input_js_plugin () {
        if ( $this->getConfigValue('apps.realty.mobilephone_old_mask') ) {
            $string = '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/system/js/jquery.maskedinput.min.js"></script>';
        } else {
            $string = '
            <link rel="stylesheet" href="'.SITEBILL_MAIN_URL.'/apps/system/js/intl-tel-input/css/intlTelInput.min.css">
            <script src="'.SITEBILL_MAIN_URL.'/apps/system/js/intl-tel-input/js/intlTelInput.js"></script>
            ';
        }
        return $string;
    }

    function get_mobilephone_input_init_script_masked_input ($id, $item_array) {
        if (isset($item_array['parameters']['mask']) && $item_array['parameters']['mask'] != '') {
            $mask = $item_array['parameters']['mask'];
        } else {
            $mask = 'h (hhh) hhh-hh-hh';
        }

        $string = '<script type="text/javascript">
                $(document).ready(function() {
                    $.mask.definitions["h"] = "[0-9]";
                    $("#' . $id . '").mask("' . $mask . '");
                });
            </script>';
        return $string;
    }

    function get_mobilephone_input_init_script_intl_tel($id, $item_array) {
        $this->add_apps_local_and_root_resource_paths('system');
        return $this->view('apps.system.lib.system.form.traits.MobilePhoneTraitTemplate',
            [
                'id' => $id,
                'item_array' => $item_array
            ]
        );

    }

    function get_mobilephone_input_init_script ($id, $item_array) {
        if ( $this->getConfigValue('apps.realty.mobilephone_old_mask') ) {
            return $this->get_mobilephone_input_init_script_masked_input($id, $item_array);
        } else {
            return $this->get_mobilephone_input_init_script_intl_tel($id, $item_array);
        }
    }
}
