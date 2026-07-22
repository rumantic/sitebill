<?php
trait FormCompileBasicTrait
{
    function compile_price_element($item_array)
    {
        global $smarty;
        $tpl = $this->get_field_tpl($item_array['type'], $item_array['table_name'], $item_array['name']);
        $value = (float)str_replace(' ', '', $item_array['value']);
        if ($value == 0) {
            $value = '';
        }
        $item_array['value'] = $value;
        $id = $this->form_id . '_' . $item_array['name'];
        if ($tpl) {
            $smarty->assign('id', $id);
            $smarty->assign('classes', $this->classes);
            $smarty->assign('item_array', $item_array);
            $smarty->assign('theme', $this->getConfigValue('theme'));
            $smarty->assign('NO_DYNAMIC_INCS', defined('NO_DYNAMIC_INCS') ? NO_DYNAMIC_INCS : false);
            $string = $smarty->fetch($tpl);
        } else {
            $string = '';
            if (!defined('NO_DYNAMIC_INCS') || !NO_DYNAMIC_INCS) {
                $string .= '<script src="' . SITEBILL_MAIN_URL . '/apps/system/js/autoNumeric.js"></script>';
            }
            $string .= '<script type="text/javascript">$(document).ready(function() {$("#' . $id . '").autoNumeric({aSep: \' \', vMax: \'999999999999\', vMin: \'0\'});});</script>' .
                '<input type="text" id="' . $id . '" class="price_field ' . $this->classes['input'] . '" name="' . $item_array['name'] . '" value="' . ($value != 0 ? $value : '') . '" />';
        }
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $string,
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'id' => $this->form_id . '_' . $item_array['name'],
            'type' => $item_array['type'],
        );
    }

    function compile_textarea_editor_element($item_array)
    {
        $parameters = $item_array['parameters'];
        global $smarty;
        $tpl = $this->get_field_tpl($item_array['type'], $item_array['table_name'], $item_array['name']);

        $id = $item_array['name'] . '_' . md5(time() . '_' . rand(10, 99));
        if (isset($item_array['editor']) and ($item_array['editor'] !== 'editor')) {
            if ($this->getConfigValue($item_array['editor']) != '') {
                $editor_code = $this->getConfigValue($item_array['editor']);
            } else {
                $editor_code = $this->getConfigValue('editor');
            }
        } elseif (isset($parameters['editor']) and ($parameters['editor'] !== '')) {
            $editor_code = $parameters['editor'];
        } else {
            $editor_code = $this->getConfigValue('editor');
        }

        if ($item_array['rows'] == '') {
            $item_array['rows'] = 10;
        }

        if ($item_array['cols'] == '') {
            $item_array['cols'] = 30;
        }

        if ($tpl) {
            $smarty->assign('id', $id);
            $smarty->assign('editor_code', $editor_code);
            $smarty->assign('classes', $this->classes);
            $smarty->assign('item_array', $item_array);
            $smarty->assign('theme', $this->getConfigValue('theme'));
            $smarty->assign('NO_DYNAMIC_INCS', defined('NO_DYNAMIC_INCS') ? NO_DYNAMIC_INCS : false);
            $rs = $smarty->fetch($tpl);
        } else {
            $rs = '';
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
            } elseif ($editor_code == 'wysibb') {
                if (!defined('NO_DYNAMIC_INCS') || !NO_DYNAMIC_INCS) {
                    $rs .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/wysibb/jquery.wysibb.min.js"></script>';
                    $rs .= '<link rel="stylesheet" href="' . SITEBILL_MAIN_URL . '/wysibb/theme/default/wbbtheme.css" />';
                }

                $rs .= '<script type="text/javascript">
                  $(document).ready(function() {
                      $("textarea#' . $id . '").wysibb({
                      buttons: "bold,italic,underline,|,img,link,|,code,quote"
                      });
                  });
              </script>';
            } elseif ($editor_code == 'codemirror') {
                $rs .= '<link rel="stylesheet" href="' . SITEBILL_MAIN_URL . '/apps/third/codemirror/lib/codemirror.css">';
                $rs .= '<link rel="stylesheet" href="' . SITEBILL_MAIN_URL . '/apps/third/codemirror/addon/fold/foldgutter.css" />';
                $rs .= '<link rel="stylesheet" href="' . SITEBILL_MAIN_URL . '/apps/third/codemirror/addon/display/fullscreen.css">';

                $rs .= '<script src="' . SITEBILL_MAIN_URL . '/apps/third/codemirror/lib/codemirror.js"></script>';
                $rs .= '<script src="' . SITEBILL_MAIN_URL . '/apps/third/codemirror/addon/fold/foldcode.js"></script>';
                $rs .= '<script src="' . SITEBILL_MAIN_URL . '/apps/third/codemirror/addon/fold/foldgutter.js"></script>';
                $rs .= '<script src="' . SITEBILL_MAIN_URL . '/apps/third/codemirror/addon/fold/brace-fold.js"></script>';
                $rs .= '<script src="' . SITEBILL_MAIN_URL . '/apps/third/codemirror/addon/fold/xml-fold.js"></script>';
                $rs .= '<script src="' . SITEBILL_MAIN_URL . '/apps/third/codemirror/addon/fold/comment-fold.js"></script>';

                $rs .= '<script src="' . SITEBILL_MAIN_URL . '/apps/third/codemirror/mode/xml/xml.js"></script>';
                $rs .= '<script src="' . SITEBILL_MAIN_URL . '/apps/third/codemirror/mode/css/css.js"></script>';
                $rs .= '<script src="' . SITEBILL_MAIN_URL . '/apps/third/codemirror/mode/javascript/javascript.js"></script>';
                $rs .= '<script src="' . SITEBILL_MAIN_URL . '/apps/third/codemirror/mode/htmlmixed/htmlmixed.js"></script>';
                $rs .= '<script src="' . SITEBILL_MAIN_URL . '/apps/third/codemirror/addon/display/fullscreen.js"></script>';

                $rs .= '<script type="text/javascript">
                  $(document).ready(function() {
                      
                      CodeMirror.fromTextArea(document.getElementById("' . $id . '"),{
                        mode: "htmlmixed",
                        lineNumbers: true,
                        viewportMargin: Infinity,
                        lineWrapping: true,
                        foldGutter: true,
                        gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"],
                        extraKeys: {
                            "F11": function(cm) {
                                cm.setOption("fullScreen", !cm.getOption("fullScreen"));
                            },
                            "Esc": function(cm) {
                                if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
                            }
                        }
                    });
                      
                     
                  });
              </script>';
            } else {
                if (isset($parameters['width']) && (int)$parameters['width'] != 0) {
                    $width = $parameters['width'];
                } else {
                    $width = 350;
                }
                if (!defined('NO_DYNAMIC_INCS') || !NO_DYNAMIC_INCS) {
                    $rs .= '<link rel="stylesheet" type="text/css" href="' . SITEBILL_MAIN_URL . '/js/cleditor/jquery.cleditor.css" />';
                    $rs .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/js/cleditor/jquery.cleditor.min.js"></script>';
                }

                $rs .= '<script type="text/javascript">$(document).ready(function() {$("textarea#' . $id . '").cleditor({width:' . $width . '});});</script>';
            }


            $rs .= '<textarea id="' . $id . '" class="input editor_' . $editor_code . '" name="' . $item_array['name'] . '" rows="' . $item_array['rows'] . '" cols="' . $item_array['cols'] . '">' . $item_array['value'] . '</textarea>';
        }
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $rs,
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type'],
        );
    }

    function compile_textarea_element($item_array)
    {
        global $smarty;
        $tpl = $this->get_field_tpl($item_array['type'], $item_array['table_name'], $item_array['name']);
        $parameters = array();
        $str = '';
        $str2 = '';
        $id = $this->form_id . '_' . $item_array['name'];
        if (isset($item_array['parameters'])) {
            $parameters = $item_array['parameters'];
        }
        if (!isset($item_array['rows'])) {
            $item_array['rows'] = 10;
        }

        if (isset($parameters['rows']) && (int)$parameters['rows'] != 0) {
            $item_array['rows'] = (int)$parameters['rows'];
        }

        if (!isset($item_array['cols'])) {
            $item_array['cols'] = 40;
        }

        if (isset($parameters['cols']) && (int)$parameters['cols'] != 0) {
            $item_array['cols'] = (int)$parameters['cols'];
        }

        if ($tpl) {
            $smarty->assign('id', $id);
            $smarty->assign('classes', $this->classes);
            $smarty->assign('item_array', $item_array);
            $smarty->assign('theme', $this->getConfigValue('theme'));
            $smarty->assign('NO_DYNAMIC_INCS', defined('NO_DYNAMIC_INCS') ? NO_DYNAMIC_INCS : false);
            $str2 = $smarty->fetch($tpl);
        } else {
            if (isset($item_array['lined']) && 1 === (int)$parameters['lined']) {
                $fields = explode('|', $parameters['fields']);

                //$id=md5(time().rand(100, 999));
                $str = '<script type="text/javascript">
              $(document).ready(function() {
                  $( "#' . $id . '" ).SitebillLineEditor({fields: ["' . implode('","', $fields) . '"]});
              });
              </script>';
            }
            $str2 = '<textarea id="' . $id . '" class="' . $this->classes['textarea'] . '" name="' . $item_array['name'] . '" rows="' . $item_array['rows'] . '" cols="' . $item_array['cols'] . '"' . ((isset($parameters['styles']) && $parameters['styles'] != '') ? ' style="' . $parameters['styles'] . '"' : '') . ' placeholder="' . $item_array['title'] . '">' . htmlspecialchars($item_array['value']) . '</textarea>';
        }
        if (isset($parameters['modal_input']) && $parameters['modal_input'] == 'search_params') {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/mysearch/admin/modal_input.php');
            $modal_input = new modal_input($item_array['name'], $item_array['value']);
            return array(
                'title' => $item_array['title'],
                'type' => $item_array['type'],
                'required' => ($item_array['required'] == "on" ? 1 : 0),
                'html' => $str . $modal_input->get_form(),
                'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
                'id' => $id,
            );
        } else {
            return array(
                'title' => $item_array['title'],
                'type' => $item_array['type'],
                'required' => ($item_array['required'] == "on" ? 1 : 0),
                'html' => $str . $str2,
                'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
                'id' => $id,
            );
        }
    }

    function compile_youtube_element($item_array)
    {
        global $smarty;
        $tpl = $this->get_field_tpl($item_array['type'], $item_array['table_name'], $item_array['name']);
        $html = '';
        if ($tpl) {
            $smarty->assign('id', $this->form_id . '_' . $item_array['name']);
            $smarty->assign('classes', $this->classes);
            $smarty->assign('item_array', $item_array);
            $smarty->assign('theme', $this->getConfigValue('theme'));
            $smarty->assign('NO_DYNAMIC_INCS', defined('NO_DYNAMIC_INCS') ? NO_DYNAMIC_INCS : false);
            $html = $smarty->fetch($tpl);
        } else {

            $value = htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING);
            $params = array();
            if (isset($item_array['parameters'])) {
                $params = $item_array['parameters'];
            }
            $html = '<input id="' . $this->form_id . '_' . $item_array['name'] . '" placeholder="' . $item_array['title'] . '" type="text" class="' . $this->classes['input'] . '" name="' . $item_array['name'] . '" value="' . $value . '"' . ((isset($params['styles']) && $params['styles'] != '') ? ' style="' . $params['styles'] . '"' : '') . ((isset($params['onclick']) && $params['onclick'] != '') ? ' onclick="' . $params['onclick'] . '"' : '') . ((isset($params['onchange']) && $params['onchange'] != '') ? ' onchange="' . $params['onchange'] . '"' : '') . ' />';
        }
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $html,
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'id' => $this->form_id . '_' . $item_array['name'],
            'type' => $item_array['type'],
        );
    }

    function compile_safe_string_element($item_array)
    {
        global $smarty;
        $tpl = @$this->get_field_tpl($item_array['type'], $item_array['table_name'], $item_array['name']);
        $html = '';
        if ($tpl) {
            $smarty->assign('id', $this->form_id . '_' . $item_array['name']);
            $smarty->assign('classes', $this->classes);
            $smarty->assign('item_array', $item_array);
            $smarty->assign('theme', $this->getConfigValue('theme'));
            $smarty->assign('NO_DYNAMIC_INCS', defined('NO_DYNAMIC_INCS') ? NO_DYNAMIC_INCS : false);
            $html = @$smarty->fetch($tpl);
        } else {

            $value = htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING);
            $params = array();
            if (isset($item_array['parameters'])) {
                $params = $item_array['parameters'];
            }
            if ($params['dadata'] == 1) {
                $test = "<link rel='stylesheet prefetch' href='https://cdn.jsdelivr.net/npm/suggestions-jquery@latest/dist/css/suggestions.min.css'>\n";
                //$test .= "<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/1.10.2/jquery.min.js'></script>";
                $test .= "<script src='https://cdn.jsdelivr.net/npm/suggestions-jquery@latest/dist/js/jquery.suggestions.min.js'></script>";
                if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/js/dadata/dadata.js')) {
                    $test .= "<script src='" . SITEBILL_MAIN_URL . "/template/frontend/" . $this->getConfigValue('theme') . "/js/dadata/dadata.js?t=" . time() . "'></script>";
                } else {
                    $test .= "<script src='" . SITEBILL_MAIN_URL . "/apps/system/js/dadata/dadata.js?t=" . time() . "'></script>";
                }
                $test .= '<script type="text/javascript">$(document).ready(function () { $("#' . $this->form_id . '_' . $item_array['name'] . '").suggestions({ token: "f26c98c6b12d1deb3c1ea1205db88e5cf6e652a0", type: "ADDRESS", onSelect: showSelected }); });</script>';
            }

            $html = $test . '<input id="' . $this->form_id . '_' . $item_array['name'] . '" placeholder="' . (isset($item_array['placeholder']) ? $item_array['placeholder'] : $item_array['title']) . '" type="text" class="sform_datata ' . $this->classes['input'] . '" name="' . $item_array['name'] . '" value="' . $value . '"' . ((isset($params['styles']) && $params['styles'] != '') ? ' style="' . $params['styles'] . '"' : '') . ((isset($params['onclick']) && $params['onclick'] != '') ? ' onclick="' . $params['onclick'] . '"' : '') . ((isset($params['onchange']) && $params['onchange'] != '') ? ' onchange="' . $params['onchange'] . '"' : '') . ' />';
            /*$dp=array();
            $dp['id']=$this->form_id.'_'.$item_array['name'];
            $dp['placeholder']=$item_array['title'];
            $dp['class']=$this->classes['input'];

            $dp['class']=$this->classes['input'];
            if(isset($params['styles']) && $params['styles']!=''){
                $dp['styles']=$params['styles'];
            }
            if(isset($params['onchange']) && $params['onchange']!=''){
                $dp['onchange']=$params['onchange'];
            }
            if(isset($params['onclick']) && $params['onclick']!=''){
                $dp['onclick']=$params['onclick'];
            }

            $html=$this->form_decorator->decorateTextInput($item_array['name'], $value, $dp);*/

            //  $html='<input id="'.$this->form_id.'_'.$item_array['name'].'" placeholder="'.$item_array['title'].'" type="text" class="'.$this->classes['input'].'" name="'.$item_array['name'].'" value="'.$value.'"'.((isset($params['styles']) && $params['styles']!='') ? ' style="'.$params['styles'].'"' : '').((isset($params['onclick']) && $params['onclick']!='') ? ' onclick="'.$params['onclick'].'"' : '').((isset($params['onchange']) && $params['onchange']!='') ? ' onchange="'.$params['onchange'].'"' : '').' />';

            /*if(intval($params['meashurable'])==1){
                $vars=explode(',', $params['meashurable_vars']);
                $def=trim($params['meashurable_def']);
                $html.='<div class="meashtype" data-variants="sqm,ar,ha">';
                foreach($vars as $var){
                    $html.='<input type="radio" name="_meash_'.$item_array['name'].'" value="'.$var.'"'.($var==$def ? ' checked="checked"' : '').'>'.$var.'';
                }
                $html.='</div>';
            }*/
            /*return array(
                    'title'=>$item_array['title'],
                    'required'=>($item_array['required'] == "on" ? 1 : 0),
                    'html'=>
                    'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
            );*/
        }
        return array(
            'title' => @$item_array['title'],
            'required' => @($item_array['required'] == "on" ? 1 : 0),
            'html' => $html,
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'id' => $this->form_id . '_' . $item_array['name'],
            'type' => $item_array['type'],
        );
    }

    function compile_password_element($item_array)
    {
        //$value=htmlspecialchars($item_array['value']);
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => '<input type="password" class="' . (isset($this->classes['input']) ? $this->classes['input'] : '') . '" name="' . $item_array['name'] . '" value="' . $item_array['value'] . '" />',
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_hidden_element($item_array)
    {
        $value = htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING);
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => '<input type="hidden" name="' . $item_array['name'] . '" value="' . $value . '" />',
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_primary_key_element($item_array)
    {
        $value = htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING);
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => '<input type="hidden" name="' . $item_array['name'] . '" value="' . $value . '" />',
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_email_element($item_array)
    {
        return $this->compile_safe_string_element($item_array);
    }

    function compile_spacer_text_element($item_array)
    {

        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $item_array['value'],
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

}
