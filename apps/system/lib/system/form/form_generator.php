<?php

use system\lib\system\form\Form_Injector;

/**
 * Form generator
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class Form_Generator extends SiteBill {
    use \system\lib\system\apps\traits\ContextTrait;

    protected $form_id = null;
    protected $use_placeholders = false;
    protected $bootstrap_version = '2';
    protected $form_decorator;

    public function getFormDecorator(){
        return $this->form_decorator;
    }
    static $cache;

    protected function generateFormId() {
        $this->form_id = 'frm_' . md5(time() . rand(10, 99));
    }

    public function getFormId() {
        return $this->form_id;
    }

    protected $classes = array();

    /**
     * Total values count in select
     * @var array
     */

    var $total_in_select = array();
    private $class_bootstrap3_input = "";
    /**
     * Construct
     * @param void
     * @return void
     */
    function __construct() {
        $this->SiteBill();
        require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_decorator.php';
        if(!defined('ADMIN')){
            if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/system/local_form_decorator.php')){
                require_once SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/system/local_form_decorator.php';
                $decorator_class='Local_Form_Decorator';
            }else{
                $decorator_class='Form_Decorator';
            }
        }else{
            $decorator_class='Form_Decorator';
        }

        $this->form_decorator=new $decorator_class();
        $this->form_decorator->setFormGenerator($this);

        $this->generateFormId();
        $bootstrap_version = trim($this->getConfigValue('bootstrap_version'));
        if (intval($this->getConfigValue('form_hint_enable'))) {
            $this->setUsePlaceholders();
        }

        if ($bootstrap_version == '3') {
            $this->class_bootstrap3_input = 'form-control';
            $this->classes['input'] = 'form-control';
            $this->classes['select'] = 'form-control';
            $this->classes['textarea'] = 'form-control';
            $this->classes['checkbox'] = '';
            $this->bootstrap_version = $bootstrap_version;
            //echo '3B';
            //debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        } elseif ($bootstrap_version == '4') {
            $this->classes['input'] = 'form-control';
            $this->classes['select'] = 'mdb-select';
            $this->classes['checkbox'] = 'filled-in';
            $this->classes['textarea'] = 'md-textarea';
            $this->bootstrap_version = $bootstrap_version;
            //echo '4B';
        } elseif ($bootstrap_version == '4md') {
            $this->classes['input'] = 'form-control';
            $this->classes['select'] = 'mdb-select';
            $this->classes['checkbox'] = 'filled-in';
            $this->classes['textarea'] = 'md-textarea';
            $this->bootstrap_version = $bootstrap_version;
            //echo '4B';
        }
        if (!defined('ADMIN_MODE')) {
            if ('' != $this->getConfigValue('template.' . $this->getConfigValue('theme') . '.form_input_class')) {
                $this->classes['input'] = $this->getConfigValue('template.' . $this->getConfigValue('theme') . '.form_input_class');
            }
            if ('' != $this->getConfigValue('template.' . $this->getConfigValue('theme') . '.form_select_class')) {
                $this->classes['select'] = $this->getConfigValue('template.' . $this->getConfigValue('theme') . '.form_select_class');
            }
            if ('' != $this->getConfigValue('template.' . $this->getConfigValue('theme') . '.form_textarea_class')) {
                $this->classes['textarea'] = $this->getConfigValue('template.' . $this->getConfigValue('theme') . '.form_textarea_class');
            }
            if ('' != $this->getConfigValue('template.' . $this->getConfigValue('theme') . '.form_checkbox_class')) {
                $this->classes['checkbox'] = $this->getConfigValue('template.' . $this->getConfigValue('theme') . '.form_checkbox_class');
            }
        }
    }

    public function setUsePlaceholders($val = true) {
        $this->use_placeholders = $val;
    }

    public function getScripts($form_data) {
        $scripts = array();
        $styles = array();
        foreach ($form_data as $item_id => $item_array) {
            if ($item_array['type'] == 'textarea_editor') {
                if (isset($item_array['editor']) && ($item_array['editor'] !== 'editor')) {
                    if ($this->getConfigValue($item_array['editor']) != '') {
                        $editor_code = $this->getConfigValue($item_array['editor']);
                    } else {
                        $editor_code = $this->getConfigValue('editor');
                    }
                } else {
                    $editor_code = $this->getConfigValue('editor');
                }
                if ($editor_code == 'ckeditor') {
                    $scripts[] = SITEBILL_MAIN_URL . '/ckeditor/ckeditor.js';
                    $scripts[] = SITEBILL_MAIN_URL . '/ckeditor/adapters/jquery.js';
                } else {
                    $styles[] = SITEBILL_MAIN_URL . '/js/cleditor/jquery.cleditor.css';
                    $scripts[] = SITEBILL_MAIN_URL . '/js/cleditor/jquery.cleditor.min.js';
                }
            } elseif ($item_array['type'] == 'captcha') {
                $scripts[] = SITEBILL_MAIN_URL . '/apps/system/js/md5.js';
            } elseif ($item_array['type'] == 'docuploads') {
                $scripts[] = SITEBILL_MAIN_URL . '/apps/system/js/dropzone/dropzone.js';
                $scripts[] = SITEBILL_MAIN_URL . '/apps/system/js/dataimagelist.js?v=2';
                $styles[] = SITEBILL_MAIN_URL . '/apps/system/js/dropzone/dropzone.css?v=1';
            } elseif ($item_array['type'] == 'uploads') {
                $scripts[] = SITEBILL_MAIN_URL . '/apps/system/js/dropzone/dropzone.js';
                $scripts[] = SITEBILL_MAIN_URL . '/apps/system/js/dataimagelist.js?v=2';
                $styles[] = SITEBILL_MAIN_URL . '/apps/system/js/dropzone/dropzone.css?v=1';
            } elseif ($item_array['type'] == 'tlocation') {
                $styles[] = SITEBILL_MAIN_URL . '/apps/system/js/bootstrap/css/bootstrap-combobox.css';
                $scripts[] = SITEBILL_MAIN_URL . '/apps/system/js/bootstrap/js/bootstrap-combobox.js';
                $scripts[] = SITEBILL_MAIN_URL . '/apps/tlocation/js/form_utils.js';
            } elseif ($item_array['type'] == 'geodata') {
                //$scripts[]=SITEBILL_MAIN_URL.'/apps/system/js/md5.js';
                $scripts[] = SITEBILL_MAIN_URL . '/apps/geodata/js/geodata.js';
            } elseif ($item_array['type'] == 'mobilephone') {
                $scripts[] = SITEBILL_MAIN_URL . '/apps/system/js/jquery.maskedinput.min.js';
            } elseif ($item_array['type'] == 'dtdate') {
                $styles[] = SITEBILL_MAIN_URL . '/apps/system/js/bootstrap/css/bootstrap-datetimepicker.min.css';
                $scripts[] = SITEBILL_MAIN_URL . '/apps/system/js/bootstrap/js/bootstrap-datetimepicker.min.js';
            } elseif ($item_array['type'] == 'dttime') {
                $styles[] = SITEBILL_MAIN_URL . '/apps/system/js/bootstrap/css/bootstrap-datetimepicker.min.css';
                $scripts[] = SITEBILL_MAIN_URL . '/apps/system/js/bootstrap/js/bootstrap-datetimepicker.min.js';
            } elseif ($item_array['type'] == 'dtdatetime') {
                $styles[] = SITEBILL_MAIN_URL . '/apps/system/js/bootstrap/css/bootstrap-datetimepicker.min.css';
                $scripts[] = SITEBILL_MAIN_URL . '/apps/system/js/bootstrap/js/bootstrap-datetimepicker.min.js';
            } elseif ($item_array['type'] == 'datetime') {
                $styles[] = SITEBILL_MAIN_URL . '/apps/system/js/bootstrap/css/bootstrap-datetimepicker.min.css';
                $scripts[] = SITEBILL_MAIN_URL . '/apps/system/js/bootstrap/js/bootstrap-datetimepicker.min.js';
            } elseif ($item_array['type'] == 'select_box_by_query') {
                if (isset($item_array['combo']) && $item_array['combo'] == 1 && 1 == $this->getConfigValue('use_combobox')) {
                    $scripts[] = SITEBILL_MAIN_URL . '/apps/system/js/mycombobox.js';
                    $styles[] = SITEBILL_MAIN_URL . '/apps/system/css/mycombobox.css';
                }
            }
        }

        if (!empty($scripts)) {
            $scripts = array_unique($scripts);
        }
        if (!empty($styles)) {
            $styles = array_unique($styles);
        }
        print_r($scripts);
        print_r($styles);
    }

    function compile_price_element($item_array) {
      global $smarty;
        $tpl=$this->get_field_tpl($item_array['type'],$item_array['table_name'],$item_array['name']);
        $value = $item_array['value'];
        $value = floatval(str_replace(' ', '', $item_array['value']));
        if($value == 0){
            $value = '';
        }
        $item_array['value']=$value;
        $id = $this->form_id . '_' . $item_array['name'];
        if($tpl){
          $smarty->assign('id',$id);
          $smarty->assign('classes',$this->classes);
          $smarty->assign('item_array',$item_array);
          $smarty->assign('theme',$this->getConfigValue('theme'));
          $smarty->assign('NO_DYNAMIC_INCS',defined('NO_DYNAMIC_INCS') ? NO_DYNAMIC_INCS : false);
          $string=$smarty->fetch($tpl);
        }else{
          $string = '';
          if (!defined('NO_DYNAMIC_INCS') || !NO_DYNAMIC_INCS) {
              $string .= '<script src="' . SITEBILL_MAIN_URL . '/apps/system/js/autoNumeric.js"></script>';
          }
          $string .= '<script type="text/javascript">$(document).ready(function() {$("#' . $id . '").autoNumeric({aSep: \' \', vMax: \'999999999999\', vMin: \'0\'});});</script>'.
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

    function compile_textarea_editor_element($item_array) {
        $parameters = $item_array['parameters'];
        global $smarty;
        $tpl=$this->get_field_tpl($item_array['type'],$item_array['table_name'],$item_array['name']);

        $id = $item_array['name'] . '_' . md5(time() . '_' . rand(10, 99));
        if (isset($item_array['editor']) AND ( $item_array['editor'] !== 'editor')) {
            if ($this->getConfigValue($item_array['editor']) != '') {
                $editor_code = $this->getConfigValue($item_array['editor']);
            } else {
                $editor_code = $this->getConfigValue('editor');
            }
        } elseif (isset($parameters['editor']) AND ( $parameters['editor'] !== '')) {
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

        if($tpl){
            $smarty->assign('id',$id);
            $smarty->assign('editor_code',$editor_code);
            $smarty->assign('classes',$this->classes);
            $smarty->assign('item_array',$item_array);
            $smarty->assign('theme',$this->getConfigValue('theme'));
            $smarty->assign('NO_DYNAMIC_INCS',defined('NO_DYNAMIC_INCS') ? NO_DYNAMIC_INCS : false);
            $rs=$smarty->fetch($tpl);
        }else{
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
          } elseif($editor_code == 'codemirror') {
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
              if (isset($parameters['width']) && (int) $parameters['width'] != 0) {
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



          $rs .= '<textarea id="' . $id . '" class="input editor_'.$editor_code.'" name="' . $item_array['name'] . '" rows="' . $item_array['rows'] . '" cols="' . $item_array['cols'] . '">' . $item_array['value'] . '</textarea>';
        }
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $rs,
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type'],
        );
    }

    function compile_textarea_element($item_array) {
        global $smarty;
        $tpl=$this->get_field_tpl($item_array['type'],$item_array['table_name'],$item_array['name']);
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

        if (isset($parameters['rows']) && (int) $parameters['rows'] != 0) {
            $item_array['rows'] = (int) $parameters['rows'];
        }

        if (!isset($item_array['cols'])) {
            $item_array['cols'] = 40;
        }

        if (isset($parameters['cols']) && (int) $parameters['cols'] != 0) {
            $item_array['cols'] = (int) $parameters['cols'];
        }

        if($tpl){
          $smarty->assign('id',$id);
          $smarty->assign('classes',$this->classes);
          $smarty->assign('item_array',$item_array);
          $smarty->assign('theme',$this->getConfigValue('theme'));
          $smarty->assign('NO_DYNAMIC_INCS',defined('NO_DYNAMIC_INCS') ? NO_DYNAMIC_INCS : false);
          $str2=$smarty->fetch($tpl);
        }else{
          if (isset($item_array['lined']) && 1 === (int) $parameters['lined']) {
              $fields = explode('|', $parameters['fields']);

              //$id=md5(time().rand(100, 999));
              $str = '<script type="text/javascript">
              $(document).ready(function() {
                  $( "#' . $id . '" ).SitebillLineEditor({fields: ["' . implode('","', $fields) . '"]});
              });
              </script>';
          }
          $str2='<textarea id="' . $id . '" class="' . $this->classes['textarea'] . '" name="' . $item_array['name'] . '" rows="' . $item_array['rows'] . '" cols="' . $item_array['cols'] . '"' . ((isset($parameters['styles']) && $parameters['styles'] != '') ? ' style="' . $parameters['styles'] . '"' : '') . '>' . htmlspecialchars($item_array['value']) . '</textarea>';
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

    function compile_captcha_element($item_array) {

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
                    //var new_key=new Date().getTime();
                    //var hash = CryptoJS.MD5(String(new_key));
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
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_youtube_element($item_array) {
        global $smarty;
        $tpl = $this->get_field_tpl($item_array['type'], $item_array['table_name'], $item_array['name']);
        $html='';
        if($tpl){
            $smarty->assign('id',$this->form_id . '_' . $item_array['name']);
            $smarty->assign('classes',$this->classes);
            $smarty->assign('item_array',$item_array);
            $smarty->assign('theme',$this->getConfigValue('theme'));
            $smarty->assign('NO_DYNAMIC_INCS',defined('NO_DYNAMIC_INCS') ? NO_DYNAMIC_INCS : false);
            $html=$smarty->fetch($tpl);
        }else{

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

    function compile_safe_string_element($item_array) {
        global $smarty;
        $tpl=$this->get_field_tpl($item_array['type'],$item_array['table_name'],$item_array['name']);
        $html='';
        if($tpl){
            $smarty->assign('id',$this->form_id . '_' . $item_array['name']);
            $smarty->assign('classes',$this->classes);
            $smarty->assign('item_array',$item_array);
            $smarty->assign('theme',$this->getConfigValue('theme'));
            $smarty->assign('NO_DYNAMIC_INCS',defined('NO_DYNAMIC_INCS') ? NO_DYNAMIC_INCS : false);
            $html=$smarty->fetch($tpl);
        }else{

            $value = htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING);
            $params = array();
            if (isset($item_array['parameters'])) {
                $params = $item_array['parameters'];
            }
            if ( $params['dadata'] == 1 ) {
                $test = "<link rel='stylesheet prefetch' href='https://cdn.jsdelivr.net/npm/suggestions-jquery@latest/dist/css/suggestions.min.css'>\n";
                //$test .= "<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/1.10.2/jquery.min.js'></script>";
                $test .= "<script src='https://cdn.jsdelivr.net/npm/suggestions-jquery@latest/dist/js/jquery.suggestions.min.js'></script>";
                if (file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/js/dadata/dadata.js') ) {
                    $test .= "<script src='".SITEBILL_MAIN_URL."/template/frontend/".$this->getConfigValue('theme')."/js/dadata/dadata.js?t=".time()."'></script>";
                } else {
                    $test .= "<script src='".SITEBILL_MAIN_URL."/apps/system/js/dadata/dadata.js?t=".time()."'></script>";
                }
                $test .= '<script type="text/javascript">$(document).ready(function () { $("#'. $this->form_id . '_' . $item_array['name'] .'").suggestions({ token: "f26c98c6b12d1deb3c1ea1205db88e5cf6e652a0", type: "ADDRESS", onSelect: showSelected }); });</script>';
            }

            $html = $test.'<input id="' . $this->form_id . '_' . $item_array['name'] . '" placeholder="' . (isset($item_array['placeholder']) ? $item_array['placeholder'] : $item_array['title']) . '" type="text" class="sform_datata ' . $this->classes['input'] . '" name="' . $item_array['name'] . '" value="' . $value . '"' . ((isset($params['styles']) && $params['styles'] != '') ? ' style="' . $params['styles'] . '"' : '') . ((isset($params['onclick']) && $params['onclick'] != '') ? ' onclick="' . $params['onclick'] . '"' : '') . ((isset($params['onchange']) && $params['onchange'] != '') ? ' onchange="' . $params['onchange'] . '"' : '') . ' />';
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
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $html,
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'id' => $this->form_id . '_' . $item_array['name'],
            'type' => $item_array['type'],
        );
    }

    function compile_client_id_element($item_array) {
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

    function compile_docuploads_element($item_array) {
        $script_code = array();
        $collection = array();
        if (!defined('NO_DYNAMIC_INCS') || !NO_DYNAMIC_INCS) {
            $script_code[] = '<script src="' . SITEBILL_MAIN_URL . '/apps/system/js/dropzone/dropzone.js"></script>';
            $script_code[] = '<link rel="stylesheet" type="text/css" href="' . SITEBILL_MAIN_URL . '/apps/system/js/dropzone/dropzone.css?v=1">';
            $script_code[] = '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/system/js/dataimagelist.js?v=2"></script>';
        }

        //$html.='<script src="'.SITEBILL_MAIN_URL.'/apps/system/js/dropzone_sitebill.js"></script>';
        $params = $item_array['parameters'];

        if (isset($params['max_file_size']) && 0 != (int) $params['max_file_size']) {
            $max_file_size = (int) $params['max_file_size'];
        } else {
            $max_file_size = (int) str_replace('M', '', ini_get('upload_max_filesize'));
        }

        $html = $this->getDropzonePlugin($this->get_session_key(), array('element' => $item_array, 'max_file_size' => $max_file_size, 'type' => 'docupoads', 'accepted' => $params['accepted']));
        if (is_array($item_array['value']) && count($item_array['value']) > 0) {
            $table_name = $item_array['table_name'];
            $primary_key = $item_array['primary_key'];
            $primary_key_value = $item_array['primary_key_value'];
            $class = 'uploaded_' . md5(time() . rand(100, 999));
            $html .= '<div class="dz-preview-uploaded ' . $class . '">';
            $html .= '<a class="btn btn-mini btn-warning dz-preview-clear" onClick="DataImagelist.dz_clearDocs(this, ' . $primary_key_value . ', \'' . $table_name . '\', \'' . $primary_key . '\', \'' . $item_array['name'] . '\');">'._e('Удалить все').'</a>';
            $html .= '<ul class="dz-preview-uploaded-list">';



            foreach ($item_array['value'] as $itk => $ita) {
                if (!empty($ita['remote']) and $ita['remote'] === 'true') {
                    $prefix = '';
                } else {
                    $prefix = SITEBILL_MAIN_URL . '/img/mediadocs/';
                }
                $html .= '<li class="dz-preview-uploaded-item dz-preview-uploaded-item-docs" data-order="'.$itk.'">
                        <div class="dz-preview-uploaded-item-image-preview">
                            <div class="dz-preview-uploaded-item-doc">
                                <a href="' . $prefix . $ita['normal'] . '" target="_blank" download>' . $ita['normal'] . '</a>
                            </div>
                            <div class="dz-preview-uploaded-item-description" onDblClick="DataImagelist.dz_dblClick(this, ' . $primary_key_value . ', \'' . $table_name . '\', \'' . $primary_key . '\', \'' . $item_array['name'] . '\');">
                                ' . ($ita['title'] == '' ? _e('Описание') : $ita['title']) . '
                            </div>
                            <div class="dz-preview-uploaded-item-description-editable" style="display: none;">
                                <input type="text" value="' . ($ita['title'] == '' ? _e('Описание') : $ita['title']) . '" />
                                <button class="btn btn-success btn-small save_desc"><i class="icon-white icon-ok"></i></button>
                                <button class="btn btn-danger btn-small canc_desc"><i class="icon-white icon-remove"></i></button>
                            </div>
                            <a href="javascript:void(0);" onClick="DataImagelist.dz_upImage(this, ' . $primary_key_value . ', \'' . $table_name . '\', \'' . $primary_key . '\', \'' . $item_array['name'] . '\');" class="btn btn-small go_up" title="Выше"><i class="icon icon-chevron-left"></i></a>
                            <a href="javascript:void(0);" onClick="DataImagelist.dz_deleteDoc(this, ' . $primary_key_value . ', \'' . $table_name . '\', \'' . $primary_key . '\', \'' . $item_array['name'] . '\');" class="btn btn-small remove" title="Удалить"><i class="icon icon-remove"></i></a>
                            <a href="javascript:void(0);" onClick="DataImagelist.dz_downImage(this, ' . $primary_key_value . ', \'' . $table_name . '\', \'' . $primary_key . '\', \'' . $item_array['name'] . '\');" class="btn btn-small go_down" title="Ниже"><i class="icon icon-chevron-right"></i></a>
                            <a href="javascript:void(0);" onClick="DataImagelist.dz_makeMain(this, ' . $primary_key_value . ', \'' . $table_name . '\', \'' . $primary_key . '\', \'' . $item_array['name'] . '\');" class="btn btn-small go_down" title="Сделать главной"><i class="icon icon-star"></i></a>
                        </div>
                        </li>';
            }
            $html .= '</ul>';
            $html .= '</div>';
        }

        $collection[] = array(
            'title' => $item_array['title'],
            'name' => $item_array['name'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $html,
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );



        //$html.=$this->getDropzonePlugin($this->get_session_key());
        $answer = new stdClass();
        $answer->collection = $collection;
        $answer->scripts = $script_code;
        //print_r($answer);
        return $answer;

        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $html,
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : '')
        );
    }

    function compile_uploads_element($item_array) {

        $script_code = array();
        $collection = array();
        if (!defined('NO_DYNAMIC_INCS') || !NO_DYNAMIC_INCS) {
            $script_code[] = '<script src="' . SITEBILL_MAIN_URL . '/apps/system/js/dropzone/dropzone.js"></script>';
            $script_code[] = '<link rel="stylesheet" type="text/css" href="' . SITEBILL_MAIN_URL . '/apps/system/js/dropzone/dropzone.css?v=1">';
            $script_code[] = '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/system/js/dataimagelist.js?v=2"></script>';
        }

        $params = $item_array['parameters'];

        if (isset($params['max_file_size']) && 0 != (int) $params['max_file_size']) {
            $max_file_size = (int) $params['max_file_size'];
        } else {
            $max_file_size = (int) str_replace('M', '', ini_get('upload_max_filesize'));
        }

        $table_name = $item_array['table_name'];
        $primary_key = $item_array['primary_key'];
        $primary_key_value = $item_array['primary_key_value'];

        $html = $this->getDropzonePlugin($this->get_session_key(), array('element' => $item_array, 'max_file_size' => $max_file_size, 'min_img_count' => (isset($params['min_img_count']) ? (int) $params['min_img_count'] : 0), 'max_img_count' => (isset($params['max_img_count']) ? (int) $params['max_img_count'] : 0)));
        $image_list = array();



        $DBC = DBC::getInstance();
        $query = 'SELECT `' . $item_array['name'] . '` FROM ' . DB_PREFIX . '_' . $table_name . ' WHERE `' . $primary_key . '`=?';
        //echo $primary_key_value;
        $stmt = $DBC->query($query, array($primary_key_value));
        //echo $DBC->getLastError();
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($ar[$item_array['name']] != '') {
                $image_list = unserialize($ar[$item_array['name']]);
            }
        }


        $code = $item_array['table_name'].'.'.$item_array['name'];
        if(isset($params['tagged']) && $params['tagged'] == 1){
            $tagged = true;
            $taggedlng = false;
        }else{
            $tagged = false;
            $taggedlng = false;
        }

        $imagedescused = true;
        if(isset($params['disableimagedesc']) && $params['disableimagedesc'] == 1){
            $imagedescused = false;
        }


        $DBC = DBC::getInstance();

        if($tagged){
            $query = 'SELECT imagetag_id, name FROM '.DB_PREFIX.'_imagetag WHERE `code` = ?';
            $stmt = $DBC->query($query, array($code));
            if($stmt){
                while($ar = $DBC->fetch($stmt)){
                    $tags[$ar['imagetag_id']] = $ar['name'];
                }
            }
        }



        /*require_once SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/system/uploader_tagger.php';
        $tagger = new uploader_tagger();
        $tagger->setItem($code);*/

        //print_r($image_list);
        if (is_array($image_list) && count($image_list) > 0) {

            $uploadedid = 'uploaded_' . md5(time() . rand(100, 999));




            $html .= '<script>';
            $html .= '$(document).ready(function(){';
            //$html .= 'DataImagelist.dz_addSortable(\''.$uploadedid.'\', ' . $primary_key_value . ', \'' . $table_name . '\', \'' . $primary_key . '\', \'' . $item_array['name'] . '\')';
            $html .= '});';
			$html .= '</script>';

            $html .= '<div id="'.$uploadedid.'" class="dz-preview-uploaded">';
            $html .= '<a class="btn btn-mini btn-warning dz-preview-clear" onClick="DataImagelist.dz_clearImages(this, ' . $primary_key_value . ', \'' . $table_name . '\', \'' . $primary_key . '\', \'' . $item_array['name'] . '\');">' . Multilanguage::_('L_DROPZONE_DELETEALL') . '</a>';
            if ( $this->getConfigValue('apps.downloader.enable') and  $table_name == 'data' ) {
                $html .= '<a class="btn btn-mini btn-success dz-preview-clear" style="margin-left: 120px;" href="'.SITEBILL_MAIN_URL.'/'.$this->getConfigValue('apps.downloader.alias').'/'.$primary_key_value.'">'._e('Скачать все фото').'</a>';
            }

            $html .= '<ul class="dz-preview-uploaded-list">';
            $bootstrap_version = trim($this->getConfigValue('bootstrap_version'));
            foreach ($image_list as $itk => $ita) {

                if($tagged){
                    if(isset($ita['tags'])){
                        $currenttags = $ita['tags'];
                    }else{
                        $currenttags = array();
                    }

                    $taghtml = '';
                    if(!empty($tags)){

                      $taghtml .= '<div>';
                      $taghtml .= '<select onchange="DataImagelist.dz_changeTags(this, ' . $primary_key_value . ', \'' . $table_name . '\', \'' . $primary_key . '\', \'' . $item_array['name'] . '\');">';
                      $taghtml .= '<option value="0">Выбрать</option>';
                      foreach($tags as $tk => $tv){
                          $taghtml .= '<option value="'.$tk.'"'.(in_array($tk, $currenttags) ? ' selected="selected"' : '').'>'.$tv.'</option>';
                      }
                      $taghtml .= '</select>';
                      $taghtml .= '</div>';
                    }
                }

                /**
                 * TODO
                 * проверить зависимость размещение селекта тегов
                 */


                if ( filter_var($ita['preview'], FILTER_VALIDATE_URL) ) {
                    $img_url = $ita['preview'];
                    $normal_img_url = $ita['normal'];
                } else {
                    $img_url = SITEBILL_MAIN_URL . '/img/data/' . $ita['preview'];
                    $normal_img_url = $this->getServerFullUrl(true).SITEBILL_MAIN_URL . '/img/data/' . $ita['normal'];
                }
                if ($this->getConfigValue('apps.downloader.src_enable')) {
                    $normal_img_url = SITEBILL_MAIN_URL.'/'.$this->getConfigValue('apps.downloader.src_alias').'/?url='.$normal_img_url;
                }
                $html .= '<li class="dz-preview-uploaded-item" data-order="'.$itk.'">
    					<div class="dz-preview-uploaded-item-image-preview">
							<div class="dz-preview-uploaded-item-image">
								<a href="'.$normal_img_url.'" download><img src="' . $img_url . '" /></a>
							</div>';
                if($imagedescused){
                    $html .= '<div class="get_field_tpl" onDblClick="DataImagelist.dz_dblClick(this, ' . $primary_key_value . ', \'' . $table_name . '\', \'' . $primary_key . '\', \'' . $item_array['name'] . '\');">
								' . ($ita['title'] == '' ? _e('Описание') : $ita['title']) . '
							</div>'.$taghtml.'';

                    $html .= '<div class="dz-preview-uploaded-item-description-editable" style="display: none;">
								<input type="text" value="' . ($ita['title'] == '' ? _e('Описание') : $ita['title']) . '" />
								<button class="btn btn-success btn-small save_desc"><i class="icon-white icon-ok"></i></button>
								<button class="btn btn-danger btn-small canc_desc"><i class="icon-white icon-remove"></i></button>
							</div>';
                }

				$html .= '<a href="javascript:void(0);" onClick="DataImagelist.dz_upImage(this, ' . $primary_key_value . ', \'' . $table_name . '\', \'' . $primary_key . '\', \'' . $item_array['name'] . '\');" class="btn btn-small go_up" title="Выше">' . (($bootstrap_version == '3' && !defined('ADMIN_MODE')) ? '<span class="glyphicon glyphicon-chevron-left"></span>' : '<i class="icon icon-chevron-left"></i>') . '</a>
<a href="javascript:void(0);" onClick="DataImagelist.dz_deleteImage(this, ' . $primary_key_value . ', \'' . $table_name . '\', \'' . $primary_key . '\', \'' . $item_array['name'] . '\');" class="btn btn-small remove" title="Удалить">' . (($bootstrap_version == '3' && !defined('ADMIN_MODE')) ? '<span class="glyphicon glyphicon-remove"></span>' : '<i class="icon icon-remove"></i>') . '</a>
<a href="javascript:void(0);" onClick="DataImagelist.dz_downImage(this, ' . $primary_key_value . ', \'' . $table_name . '\', \'' . $primary_key . '\', \'' . $item_array['name'] . '\');" class="btn btn-small go_down" title="Ниже">' . (($bootstrap_version == '3' && !defined('ADMIN_MODE')) ? '<span class="glyphicon glyphicon-chevron-right"></span>' : '<i class="icon icon-chevron-right"></i>') . '</a>
<a href="javascript:void(0);" onClick="DataImagelist.dz_makeMain(this, ' . $primary_key_value . ', \'' . $table_name . '\', \'' . $primary_key . '\', \'' . $item_array['name'] . '\');" class="btn btn-small go_down" title="Сделать главной">' . (($bootstrap_version == '3' && !defined('ADMIN_MODE')) ? '<span class="glyphicon glyphicon-star"></span>' : '<i class="icon icon-star"></i>') . '</a>
<a href="javascript:void(0);" onClick="DataImagelist.dz_rotateImage(this, ' . $primary_key_value . ', \'' . $table_name . '\', \'' . $primary_key . '\', \'' . $item_array['name'] . '\', \'ccw\')"><img src="' . SITEBILL_MAIN_URL . '/apps/admin/admin/template/img/rotccw.png" border="0" alt="Повернуть против часовой стрелки" title="Повернуть против часовой стрелки"></a>
<a href="javascript:void(0);" onClick="DataImagelist.dz_rotateImage(this, ' . $primary_key_value . ', \'' . $table_name . '\', \'' . $primary_key . '\', \'' . $item_array['name'] . '\', \'cw\')"><img src="' . SITEBILL_MAIN_URL . '/apps/admin/admin/template/img/rotcw.png" border="0" alt="Повернуть по часовой стрелке" title="Повернуть по часовой стрелке"></a>
						</div>
						</li>';
            }
            $html .= '</ul>';
            $html .= '</div>';
        }

        $collection[] = array(
            'title' => $item_array['title'],
            'hint' => $item_array['hint'],
            'name' => $item_array['name'],
            'type' => $item_array['type'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $html,
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : '')
        );



        //$html.=$this->getDropzonePlugin($this->get_session_key());
        $answer = new stdClass();
        $answer->collection = $collection;
        $answer->scripts = $script_code;
        //print_r($answer);
        return $answer;

        return array(
            'title' => $item_array['title'],
            'type' => $item_array['type'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $html,
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : '')
        );
    }



    function compile_gadres_element($item_array) {
      global $smarty;
      $tpl=$this->get_field_tpl($item_array['type'],$item_array['table_name'],$item_array['name']);
      $id = md5(rand(1000, 9999) . time());
      $str='';
      if($tpl){
        $smarty->assign('id',$id);
        $smarty->assign('classes',$this->classes);
        $smarty->assign('item_array',$item_array);
        $smarty->assign('theme',$this->getConfigValue('theme'));
        $smarty->assign('NO_DYNAMIC_INCS',defined('NO_DYNAMIC_INCS') ? NO_DYNAMIC_INCS : false);
        $str=$smarty->fetch($tpl);
      }else{

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
        $str.='<input type="hidden" name="gadres[' . $item_array['name'] . ']" value="' . $value . '"><input class="' . $this->classes['input'] . '" id="gadres_' . $id . '" type="text" name="' . $item_array['name'] . '" value="" placeholder="' . $value . '"' . ((isset($params['styles']) && $params['styles'] != '') ? ' style="' . $params['styles'] . '"' : '') . ' />';
      }
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $str,
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_tlocation_element($item_array) {


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
            $show_names = (int) $params['show_names'];
        } else {
            $show_names = 1;
        }

        if (isset($params['names'])) {
            $_x = array();
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
        $script_code .= '<script>$(document).ready(function(){TLocationForm.setHandler("' . $uniq_class_name . '", ' . (int) $this->getConfigValue('link_street_to_city') . '' . ($autocomplete ? ', 1' : '') . ')});</script>';

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

            if ((int) $values['country_id'] != 0) {
                $query = 'SELECT region_id, name FROM ' . DB_PREFIX . '_region WHERE country_id=? ORDER BY name ASC';
                $stmt = $DBC->query($query, array($values['country_id']));
            } elseif (isset($defaults['country_id']) && (int) $defaults['country_id'] != 0) {
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
            if ((int) $values['region_id'] != 0) {
                $query = 'SELECT city_id, name FROM ' . DB_PREFIX . '_city WHERE region_id=? ORDER BY name ASC';
                $stmt = $DBC->query($query, array($values['region_id']));
            } elseif (isset($defaults['region_id']) && (int) $defaults['region_id'] != 0) {
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
            if ((int) $values['city_id'] != 0) {
                $query = 'SELECT id, name FROM ' . DB_PREFIX . '_district WHERE city_id=? ORDER BY name ASC';
                $stmt = $DBC->query($query, array($values['city_id']));
            } elseif (isset($defaults['city_id']) && (int) $defaults['city_id'] != 0) {
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
            if ((int) $values['city_id'] != 0) {
                $query = 'SELECT street_id, name FROM ' . DB_PREFIX . '_street WHERE city_id=? ORDER BY name ASC';
                $stmt = $DBC->query($query, array($values['city_id']));
            } elseif (isset($defaults['city_id']) && (int) $defaults['city_id'] != 0) {
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

    function get_field_tpl($type,$tablename,$fieldname,$formname=''){
        $tpl='';
        //var_dump(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/system/fields/name-'.$tablename.'.'.$fieldname.'.tpl');
        if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/system/fields/name-'.$tablename.'.'.$fieldname.'.tpl')){
            $tpl=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/system/fields/name-'.$tablename.'.'.$fieldname.'.tpl';
        }elseif(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/system/fields/type-'.$type.'.tpl')){
            $tpl=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/system/fields/type-'.$type.'.tpl';

        }elseif(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/system/fields/name-'.$tablename.'.'.$fieldname.'.tpl')){
            $tpl=SITEBILL_DOCUMENT_ROOT.'/apps/system/fields/name-'.$tablename.'.'.$fieldname.'.tpl';
        }elseif(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/system/fields/type-'.$type.'.tpl')){
            $tpl=SITEBILL_DOCUMENT_ROOT.'/apps/system/fields/type-'.$type.'.tpl';
        }
        return $tpl;
    }


    function compile_parameter_element($item_array) {
      global $smarty;
      $tpl=$this->get_field_tpl($item_array['type'],$item_array['table_name'],$item_array['name']);
      $id = md5(rand(1000, 9999) . time());
      $html='';
      if($tpl){
        $smarty->assign('id',$id);
        $smarty->assign('classes',$this->classes);
        $smarty->assign('item_array',$item_array);
        $smarty->assign('theme',$this->getConfigValue('theme'));
        $smarty->assign('NO_DYNAMIC_INCS',defined('NO_DYNAMIC_INCS') ? NO_DYNAMIC_INCS : false);
        $html=$smarty->fetch($tpl);
      }else{

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

    function compile_attachment_element($item_array) {
        $value = htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING);
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => '<input type="text" name="' . $item_array['name'] . '"  size="' . $item_array['length'] . '" maxlength="' . $item_array['maxlength'] . '" value="' . $value . '" />',
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_select_box_structure_simple_multiple_element($item_array) {
        if (!isset($item_array['values_array'])) {
            $item_array['values_array'] = array(0 => 0);
        }
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();

        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $Structure_Manager->getCategorySelectBoxWithName($item_array['name'], $item_array['values_array']),
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );

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

    function compile_grade_element($item_array) {
        $html = '';

        $vals = array();

        if (isset($item_array['grade_values'])) {
            $vals = $item_array['grade_values'];
        } elseif (isset($item_array['select_data'])) {
            $vals = $item_array['select_data'];
        }

        if (!empty($vals)) {
            foreach ($vals as $item_id => $item_id_name) {
                if ($item_array['value'] == $item_id) {
                    $checked = 'checked="checked"';
                } else {
                    $checked = '';
                }
                $html .= '<span>' . $item_id_name . '</span><input type="radio" name="' . $item_array['name'] . '" value="' . $item_id . '" ' . $checked . '>&nbsp;&nbsp;&nbsp;';
            }
        } else {
            $html .= '<input class="' . $this->classes['input'] . '" type="text" name="' . $item_array['name'] . '" value="' . $item_array['value'] . '" />';
        }

        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $html,
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_geodata_element($item_array) {
        $parameters = $item_array['parameters'];
        $value = $item_array['value'];
        $str = '';
        $map_options = array();
        $mapsizes = array();

        if(isset($parameters['map_width']) && trim($parameters['map_width'])!=0){
            if(preg_match('/(\d+)%$/', $parameters['map_width'], $matches)){
                $map_options[]='width: \''.trim($parameters['map_width']).'\'';
                $mapsizes[] = 'width: '.trim($parameters['map_width']);
            }elseif(preg_match('/(\d+)px$/', $parameters['map_width'], $matches)){
                $map_options[]='width: \''.trim($parameters['map_width']).'\'';
                $mapsizes[] = 'width: '.trim($parameters['map_width']);
            }elseif(intval($parameters['map_width'])>0){
                $map_options[]='width: \''.intval($parameters['map_width']).'px\'';
                $mapsizes[] = 'width: '.intval($parameters['map_width']).'px';
            }

        }
        if(isset($parameters['map_height']) && trim($parameters['map_height'])!=0){
            if(preg_match('/(\d+)%$/', $parameters['map_height'], $matches)){
                $map_options[]='height: \''.trim($parameters['map_height']).'\'';
                $mapsizes[] = 'height: '.trim($parameters['map_height']);
            }elseif(preg_match('/(\d+)px$/', $parameters['map_height'], $matches)){
                $map_options[]='height: \''.trim($parameters['map_height']).'\'';
                $mapsizes[] = 'height: '.trim($parameters['map_height']);
            }elseif(intval($parameters['map_height'])>0){
                $map_options[]='height: \''.intval($parameters['map_height']).'px\'';
                $mapsizes[] = 'height: '.intval($parameters['map_height']).'px';
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
            $confields = array_map(function($c) {
                return trim($c);
            }, $confields);
            $map_options[] = 'confields: [\'' . implode('\',\'', $confields) . '\']';
        } else {
            $map_options[] = 'confields: []';
        }

        if(isset($parameters['usemapsearch']) && intval($parameters['usemapsearch']) == 1){
            $map_options[] = 'usemapsearch: true';
        }

        if (1 == $this->getConfigValue('apps.geodata.no_scroll_zoom')) {
            $map_options[] = 'no_scroll_zoom: 1';
        }

        $mtype = '';
        if(1 == $this->getConfigValue('use_google_map')){
            $mtype = 'google';
        }elseif(2 == $this->getConfigValue('use_google_map')){
            $mtype = 'leaflet_osm';
        }else{
            $mtype = 'yandex';
        }
        //$this->template->assert('map_type', '');


        $map_options[] = 'map_type: ' . '\''.$mtype.'\'';
        $id = md5(time() . rand(100, 999));


        /*$str = '<div id="geodata_'.$id.'">';
        $str .= '<input type="hidden" geodata="lat" class="' . $this->classes['input'] . '" name="' . $item_array['name'] . '[lat]" value="' . (isset($value['lat']) ? $value['lat'] : '') . '" />';
        //$str.= $map_lat_input;
        $str .= '</div>';*/












        $map_div_open = '<div id="geodata_' . $id . '" coords="' . $this->getConfigValue('apps.geodata.new_map_center') . '" zoom="' . $this->getConfigValue('apps.geodata.map_zoom_default') . '" class="geodata_form_el">';
        $map_div_close = '</div>';
        $str .= $map_div;
        //$str.='<div class="geodata_form_co">';
        $map_lat_input = '<input type="text" geodata="lat" class="' . $this->classes['input'] . '" name="' . $item_array['name'] . '[lat]" value="' . (isset($value['lat']) ? $value['lat'] : '') . '" />';
        //$str.= $map_lat_input;
        $map_lng_input = '<input type="text" geodata="lng" class="' . $this->classes['input'] . '" name="' . $item_array['name'] . '[lng]" value="' . (isset($value['lng']) ? $value['lng'] : '') . '" />';
        $map_div_map = '<div class="geodata_map_holder" style="'.(!empty($mapsizes) ? implode('; ', $mapsizes).';' : '').'"></div>';
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

    function compile_avatar_element($item_array) {

        $table_name = $item_array['table_name'];
        $primary_key = $item_array['primary_key'];
        $primary_key_value = $item_array['primary_key_value'];

        $script_code = array();
        $collection = array();
        if (!defined('NO_DYNAMIC_INCS') || !NO_DYNAMIC_INCS) {
            $script_code[] = '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/system/js/dataimagelist.js?v=2"></script>';
        }

        $html = '';
        $html .= '<div class="frm_avatar">';
        $html .= '<input type="file" name="' . $item_array['name'] . '" />';
        if ($item_array['value'] != '') {
            $html .= '<div class="frm_avatar_im">';
            $html .= '<img src="' . SITEBILL_MAIN_URL . '/img/data/' . $item_array['value'] . '" border="0"/>';
            $html .= '</div>';
            $html .= '<div class="frm_avatar_cntrl">';
            $html .= '<input type="checkbox" class="' . $this->classes['checkbox'] . '" name="delete_avatar[' . $item_array['name'] . ']" value="yes" /> Удалить фото';
            $html .= '<a href="javascript:void(0);" onClick="DataImagelist.av_deleteImage(this, ' . $primary_key_value . ', \'' . $table_name . '\', \'' . $primary_key . '\', \'' . $item_array['name'] . '\');" class="btn btn-small remove" title="Удалить"><i class="icon icon-remove"></i></a>';
            $html .= '</div>';
        }
        $html .= '</div>';
        /* if ( $item_array['value'] != '' ) {
          $image_list = '<img src="'.SITEBILL_MAIN_URL.'/img/data/'.$item_array['value'].'" border="0"/>
          <br>
          <a href="#">Удалить фото</a>
          <input type="checkbox" name="delava['.$item_array['name'].']" value="yes" /> Удалить фото';
          }else{
          $image_list = '';
          } */


        $collection[] = array(
            'title' => $item_array['title'],
            'hint' => $item_array['hint'],
            'name' => $item_array['name'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $html,
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );



        //$html.=$this->getDropzonePlugin($this->get_session_key());
        $answer = new stdClass();
        $answer->collection = $collection;
        $answer->scripts = $script_code;
        //print_r($answer);
        return $answer;


        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => '<input type="file" name="' . $item_array['name'] . '" />',
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'image_list' => $image_list
        );
    }

    function compile_photo_element($item_array) {
        $image_list = '';
        if ($item_array['value'] != '') {
            $image_list .= '<div id="photo_element_image_list_deprecated" class="photo_element">';
            $image_list .= '<img src="' . SITEBILL_MAIN_URL . '/img/data/user/' . $item_array['value'] . '" border="0"/>';
            switch ($this->bootstrap_version) {
                case '3' : {
                        $image_list .= '<div class="checkbox"><label><input type="checkbox" name="delpic" value="yes">Удалить фото</label></div>';
                        break;
                    }
                case '4md' : {
                        $image_list .= '<input type="checkbox" id="delpic" name="delpic" value="yes"><label for="delpic">Удалить фото</label>';
                        break;
                    }
                case '4' : {
                        $image_list .= '<label class="form-check-label"><input type="checkbox" class="form-check-input" name="delpic" value="yes">Удалить фото</label>';
                        break;
                    }
                default : {
                        $image_list .= '<label class="checkbox"><input type="checkbox" name="delpic" value="yes"> Удалить фото</label>';
                    }
            }
            $image_list .= '</div>';
        }
        try {
            if ( $this->get_context() ) {
                $update_user_id = $this->getRequestValue($this->get_context()->primary_key);
                if ( $this->getRequestValue('do') == 'new' or  $this->getRequestValue('do') == 'new_done') {
                    $update_user_id = 'new';
                }
            } else {
                $update_user_id = '0';
            }
        } catch (Exception $e) {
            $update_user_id = '0';
        }

        if ($item_array['value'] != '') {
            $image = SITEBILL_MAIN_URL . '/img/data/user/' . $item_array['value'];
        } elseif ($_SESSION['new_avatar_img'] != '') {
            $image = SITEBILL_MAIN_URL . '/img/data/user/' . $_SESSION['new_avatar_img'];
        } else {
            $image = '';
        }

        $image_cropper = '
            <image-cropper 
                update_user_id="'.$update_user_id.'" 
                language="ru" 
                image_url="' . $image . '" 
                width="'.$this->getConfigValue('user_pic_width').'"
                height="'.$this->getConfigValue('user_pic_height').'"
                upload_button_title="'._e('Загрузить новое фото').'"
                image_delete_title="'._e('Удалить фото').'"
                >
            </image-cropper>';


        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $image_cropper.'<input id="photo_element_deprecated" type="file" name="' . $item_array['name'] . '" />',
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'image_list' => $image_list,
            'type' => $item_array['type']
        );
    }

    function compile_password_element($item_array) {
        //$value=htmlspecialchars($item_array['value']);
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => '<input type="password" class="' . (isset($this->classes['input']) ? $this->classes['input'] : '') . '" name="' . $item_array['name'] . '" value="' . $item_array['value'] . '" />',
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_hidden_element($item_array) {
        $value = htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING);
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => '<input type="hidden" name="' . $item_array['name'] . '" value="' . $value . '" />',
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_primary_key_element($item_array) {
        $value = htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING);
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => '<input type="hidden" name="' . $item_array['name'] . '" value="' . $value . '" />',
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_mobilephone_element($item_array) {
        $value = htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING);
        if (isset($item_array['parameters']['mask']) && $item_array['parameters']['mask'] != '') {
            $mask = $item_array['parameters']['mask'];
        } else {
            $mask = 'h (hhh) hhh-hh-hh';
        }
        $id = md5($item_array['name'] . '_' . rand(100, 999));
        $string = '';
        if (!defined('NO_DYNAMIC_INCS') || !NO_DYNAMIC_INCS) {
            $string = '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/system/js/jquery.maskedinput.min.js"></script>';
        }
        $string .= '<script type="text/javascript">
                $(document).ready(function() {
                    $.mask.definitions["h"] = "[0-9]";
                    $("#' . $id . '").mask("' . $mask . '");
                });
            </script>';
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $string . '<input id="' . $id . '" class="' . $this->classes['input'] . '" type="text" name="' . $item_array['name'] . '" value="' . $value . '" />',
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_email_element($item_array) {
        return $this->compile_safe_string_element($item_array);
    }

    function compile_spacer_text_element($item_array) {

        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $item_array['value'],
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_selectbox_element($item_array) {
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $this->get_select_box($item_array),
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_radiogroup_element($item_array) {
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $this->get_radiogroup($item_array),
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    /* function compile_separator_element($item_array){
      return array(
      'title'=>$item_array['title'],
      'html'=>'',
      'type'=>'separator',
      'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
      );
      } */

    function compile_separator_element($item_array) {
        return array(
            'title' => $item_array['title'],
            'required' => 0,
            'html' => '<h2>' . $item_array['title'] . '</h2>',
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_checkbox_element($item_array) {
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $this->get_checkbox($item_array),
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'id' => $this->form_id . '_' . $item_array['name'],
            'type' => $item_array['type']
        );
    }

    function compile_select_entity_element($item_array, $model = null) {
        $rs = '';
        if (isset($item_array['parameters'])) {
            $parameters = $item_array['parameters'];
        } else {
            $parameters = array();
        }

        $model = $parameters['model'];
        $value_name = $parameters['value_name'];
        if ($value_name == '') {
            $value_name = 'name';
        }

        $form_data = array();

        require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
        $ATH = new Admin_Table_Helper();
        $form_data = $ATH->load_model($model, false);
        foreach ($form_data[$model] as $it) {
            if ($it['type'] == 'primary_key') {
                $primary_key_name = $it['name'];
                break;
            }
        }
        //$primary_key_name='flatplanning_id';
        //$primary_key_name='flatplanning_id';

        $DBC = DBC::getInstance();
        $query = 'SELECT * FROM ' . DB_PREFIX . '_' . $model;
        $stmt = $DBC->query($query);
        $rs .= '<select class="' . $this->classes['select'] . '" name="' . $item_array['name'] . '" id="' . $item_array['name'] . '">';
        $rs .= '<option value="0">--</option>';
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $value = $ar[$value_name];
                $value = htmlspecialchars($value, ENT_QUOTES, SITE_ENCODING);
                $rs .= '<option value="' . $ar[$primary_key_name] . '" ' . $selected . '>' . $value . '</option>';
            }
        }
        $rs .= '</select>';

        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $rs,
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_select_box_by_query_element($item_array, $model = null) {

        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $this->get_single_select_box_by_query($item_array, $model),
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_select_box_structure_element($item_array) {
        if (isset($item_array['parameters']) && isset($item_array['parameters']['type'])) {
            $type = $item_array['parameters']['type'];
        } else {
            $type = '';
        }
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        if ($item_array['title_default'] != '') {
            $zero_title = $item_array['title_default'];
        } else {
            $zero_title = '';
        }

        if(isset($item_array['parameters']['nonzerotitle'])){
			$nonzerotitle = $item_array['parameters']['nonzerotitle'];
		}else{
            $nonzerotitle = '';
        }

        $html = '';
        if ($type == 'leveled') {
            $html = $Structure_Manager->getCategorySelectBoxLeveled($item_array['name'], $item_array['value'], array('zerotitle' => $zero_title, 'nonzerotitle' => $nonzerotitle));
        } else {
            $html = $Structure_Manager->getCategorySelectBoxWithName($item_array['name'], $item_array['value'], false, array(), $zero_title);
        }
        //echo $zero_title;
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $html,
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_structure_element($item_array) {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_implements.php');
        $SM = Structure_Implements::getManager($item_array['entity']);

        //$equire_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
        //$Structure_Manager = new Structure_Manager();


        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $SM->getCategorySelectBoxWithName($item_array['name'], $item_array['value'], false, $item_array['parameters'], $zero_title),
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_select_by_query_multi_element($item_array, $model = null) {
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $this->get_select_by_query_multi($item_array, $model),
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_injector_element($item_array, $model) {
        $form_injector = new Form_Injector();

        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $form_injector->compile($item_array, $this, $model),
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }


    function compile_select_box_by_query_multiple_element($item_array) {
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $this->get_single_select_box_by_query_multiple($item_array),
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_pluploader_element($item_array) {

        $_count = 0;
        $image_list = $this->getImageListAdmin($item_array['action'], $item_array['table_name'], $item_array['primary_key'], $item_array['primary_key_value'], $_count);

        if ($this->getConfigValue('photo_per_data') > 0 AND $item_array['action'] == 'data') {
            if ($_count >= $this->getConfigValue('photo_per_data')) {
                return array(
                    'title' => $item_array['title'],
                    'required' => ($item_array['required'] == "on" ? 1 : 0),
                    'image_list' => $image_list,
                    'html' => '',
                    'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
                    'type' => $item_array['type']
                );
            }
        }
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'image_list' => $image_list,
            'html' => $this->getPluploaderPlugin($this->get_session_key()),
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_uploadify_element($item_array) {

        $parameters = $item_array['parameters'];
        $_count = 0;
        $image_list = $this->getImageListAdmin($item_array['action'], $item_array['table_name'], $item_array['primary_key'], $item_array['primary_key_value'], $_count);

        if ($this->getConfigValue('photo_per_data') > 0 AND $item_array['action'] == 'data') {
            if ($_count >= $this->getConfigValue('photo_per_data')) {
                return array(
                    'title' => $item_array['title'],
                    'required' => ($item_array['required'] == "on" ? 1 : 0),
                    'image_list' => $image_list,
                    'html' => '',
                    'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
                    'type' => $item_array['type']
                );
            }
        }
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'image_list' => $image_list,
            'html' => $this->getUploadifyPlugin($this->get_session_key(), $parameters),
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_uploadify_file_element($item_array) {
        $image_list = $this->getFileListAdmin($item_array['action'], $item_array['table_name'], $item_array['primary_key'], $item_array['primary_key_value']);
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'image_list' => $image_list,
            'html' => $this->getUploadifyFilePlugin($this->get_session_key()),
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_pluploader_file_element($item_array) {
        $image_list = $this->getFileListAdmin($item_array['action'], $item_array['table_name'], $item_array['primary_key'], $item_array['primary_key_value']);
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'image_list' => $image_list,
            'html' => $this->getPluploaderPlugin($this->get_session_key()),
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_form_elements($form_data, $ignore_tabs = false) {

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

                case 'service_type_select_box_structure': {
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
                        case 'pluploader' : {
                                $rs = $this->compile_pluploader_element($item_array);
                                break;
                            }
                        default : {
                                $rs = $this->compile_uploadify_element($item_array);
                            }
                    }

                    break;

                case 'uploadify_file':
                    switch ($this->getConfigValue('uploader_type')) {
                        case 'pluploader' : {
                                //$rs = $this->compile_pluploader_element($item_array);
                                $rs = $this->compile_pluploader_file_element($item_array);
                                break;
                            }
                        default : {
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

        $scripts = array_unique($scripts);
        $elements['scripts'] = implode('', $scripts);
        $elements['scripts'] = $scripts;
        return $elements;
    }

    /**
     * Compile form inputs
     * @param $form_data form data
     * @return string
     */
    function compile_form($form_data, $ignore_tabs = false) {
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

                case 'langselect': {
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

                case 'service_type_select_box_structure': {
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
                        case 'pluploader' : {
                                $rs = $this->get_pluploader_row($item_array);
                                break;
                            }
                        default : {
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

    /**
     * Get spacer text
     * @param array $item_array
     * @return string
     */
    function get_spacer_text($item_array) {
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
    function get_error_message_row($error_message) {
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
    function get_select_box_by_query_row($item_array) {
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
    function get_select_box_by_query_multiple_row($item_array) {
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
    function get_total_in_select($key) {
        return $this->total_in_select[$key];
    }

    /**
     * Get single select box by query
     * @param array $item_array
     * @return string
     */
    function get_single_select_box_by_query($item_array, $model = null) {
        if ( defined('VUE_ENABLED') and VUE_ENABLED == true ) {
            $rs = '<select-by-query 
                    column_name="'.$item_array['name'].'" 
                    model_name="'.$item_array['table_name'].'"
                    placeholder="'.$item_array['title_default'].'"
                    value="'.$item_array['value'].'"
                    >
                   </select-by-query>';
            return $rs;
        }

        $lang = $this->getCurrentLang();
        $item_md5 = md5(serialize($item_array).$lang);
        if ( isset(self::$cache[$item_md5]) ) {
            return self::$cache[$item_md5];
        }

        /* $links=array(
          'country_id'=>array(
          array('linked_element'=>'region_id', 'linked_field'=>'country_id'),
          ),
          'region_id'=>array(
          array('linked_element'=>'city_id', 'linked_field'=>'region_id'),
          ),
          'city_id'=>array(
          array('linked_element'=>'district_id', 'linked_field'=>'city_id'),
          array('linked_element'=>'street_id', 'linked_field'=>'city_id'),
          )
          ); */

        if (isset($item_array['parameters'])) {
            $parameters = $item_array['parameters'];
        } else {
            $parameters = array();
        }


        /*
          $value_name_parts=array();
          if(isset($parameters['query_name_parts']) && $parameters['query_name_parts']!=''){
          $value_name_parts=explode(',', $parameters['query_name_parts']);
          foreach($value_name_parts as $k=>$v){
          if(trim($v)==''){
          unset($value_name_parts[$k]);
          }else{
          $value_name_parts[$k]='`'.trim($v).'`';
          }
          }
          }

          if(count($value_name_parts)>0){
          $vname='CONCAT_WS(\''.$parameters['query_name_parts_separator'].'\', '.implode(',', $value_name_parts).') AS `'.$parameters['query_name'].'`';
          }

          if(isset($parameters['query_order']) && $parameters['query_order']!=''){
          $p_order='`'.trim($parameters['query_order']).'`'.' '.trim($parameters['query_order_direct']);
          }

          $q='SELECT '.trim($parameters['query_key']).', '.$vname.' FROM '.DB_PREFIX.'_'.$item_array['primary_key_table'].' ORDER BY '.$p_order.'<br>';
         */





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
                list($a, $b)=explode(',', $depended_element_name);
                if($b!=''){
                    $depended_element_name=$a;
                    $depended_element_name_key=$b;
                }else{
                    $depended_element_name_key=$depended_element_name;
                }
            }
        } else {
            /* switch($item_array['name']){
              case 'country_id' : {
              if ( $this->getConfigValue('apps.realty.ajax_region_refresh') ) {
              $links[]=array(
              'linked_element'=>'region_id',
              'linked_field'=>'country_id'
              );
              }

              $depended_element_name='';
              break;
              }
              case 'region_id' : {
              if ( $this->getConfigValue('apps.realty.ajax_city_refresh') ) {
              $links[]=array(
              'linked_element'=>'city_id',
              'linked_field'=>'region_id'
              );
              }
              if ( $this->getConfigValue('apps.realty.ajax_region_refresh') ) {
              $depended_element_name='country_id';
              }

              break;
              }
              case 'city_id' : {
              if ( $this->getConfigValue('apps.realty.ajax_district_refresh') ) {
              $links[]=array(
              'linked_element'=>'district_id',
              'linked_field'=>'city_id'
              );
              }

              if(1==$this->getConfigValue('link_metro_to_district')){

              }else{
              if ( $this->getConfigValue('apps.realty.ajax_metro_refresh') ) {
              $links[]=array(
              'linked_element'=>'metro_id',
              'linked_field'=>'city_id'
              );
              }

              }
              if($this->getConfigValue('link_street_to_city')){
              if ( $this->getConfigValue('apps.realty.ajax_street_refresh') ) {
              $links[]=array(
              'linked_element'=>'street_id',
              'linked_field'=>'city_id'
              );
              }
              }
              if ( $this->getConfigValue('apps.realty.ajax_city_refresh') ) {
              $depended_element_name='region_id';
              }

              break;
              }
              case 'district_id' : {
              if(1==$this->getConfigValue('link_metro_to_district')){
              if($this->getConfigValue('apps.realty.ajax_metro_refresh')){
              $links[]=array(
              'linked_element'=>'metro_id',
              'linked_field'=>'district_id'
              );
              }

              }
              if(1!=intval($this->getConfigValue('link_street_to_city'))){
              if($this->getConfigValue('apps.realty.ajax_street_refresh')){
              $links[]=array(
              'linked_element'=>'street_id',
              'linked_field'=>'district_id'
              );
              }


              }
              if($this->getConfigValue('apps.realty.ajax_district_refresh')){
              $depended_element_name='city_id';
              }

              break;
              }
              case 'metro_id' : {
              if($this->getConfigValue('apps.realty.ajax_metro_refresh')){
              if(1==$this->getConfigValue('link_metro_to_district')){
              $depended_element_name='district_id';
              }else{
              $depended_element_name='city_id';
              }
              }

              break;
              }
              case 'street_id' : {
              if($this->getConfigValue('apps.realty.ajax_street_refresh')){
              if(1==$this->getConfigValue('link_street_to_city')){
              $depended_element_name='city_id';
              }else{
              $depended_element_name='district_id';
              }
              }

              break;
              }
              } */
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
            if (isset($parameters['autocomplete_notappend']) && 0 != (int) $parameters['autocomplete_notappend']) {
                $_no_insert = true;
            }


            $onchange = array();
            if (isset($links) && count($links) > 0) {
                foreach ($links as $lnks) {
                    $onchange[] = 'LinkedElements.refresh(this, \'' . $lnks['linked_element'] . '\', \'' . $lnks['linked_field'] . '\', \'' . $item_array['table_name'] . '\');';
                }
            }

            return '<div class="geoautocomplete_block"><input class="' . (isset($this->classes['input']) ? $this->classes['input'] : '') . ' geoautocomplete" type="text" placeholder="' . $item_array['title_default'] . '" '.($_no_insert ? '' : 'name="geoautocomplete[' . $item_array['name'] . ']"').' value="' . $value . '" pk="' . $item_array['primary_key_name'] . '" from="' . $item_array['primary_key_table'] . '" data-depel="' . (isset($parameters['autocomplete_dep_el']) ? $parameters['autocomplete_dep_el'] : '') . '" data-depelkey="' . (isset($parameters['autocomplete_dep_el_key']) ? $parameters['autocomplete_dep_el_key'] : '') . '"' . ($_no_insert ? ' data-notappend="true"' : '') . ' data-model="' . $item_array['table_name'] . '" /><input type="hidden" onchange="' . implode(' ', $onchange) . ' ' . '" name="' . $item_array['name'] . '" value="' . $item_array['value'] . '" /></div>';
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


            $ret=array();

            if ($depended_element_name != '') {
                $depended_value = $model[$depended_element_name]['value'];
                if(isset($parameters['use_query']) && $parameters['use_query'] != '' ){
                    $query=$parameters['use_query'];
                    if($_REQUEST['debug']==1)var_dump($query);
                    $stmt = $DBC->query($query, array($value));
                    if ($stmt) {
                        while ($ar = $DBC->fetch($stmt)) {
                            if($ar[$depended_element_name]==$depended_value){
                                $ret[] = array($item_array['primary_key_name']=>$ar[$item_array['primary_key_name']],$value_name=>$ar[$value_name]);
                            }
                        }
                    }
                }else{
                    if ((int) $depended_value != 0) {
                        $query = 'SELECT `' . $item_array['primary_key_name'] . '`, `' . $value_name_l . '` AS ' . $value_name . ' FROM ' . DB_PREFIX . '_' . $item_array['primary_key_table'] . ' WHERE `' . $depended_element_name . '`=?'.($parameters['addwhere']>'' ? ' and '.$parameters['addwhere'] : '');

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
                        $stmt = $DBC->query($query, array((int) $depended_value));
                        if ($stmt) {
                            while ($ar = $DBC->fetch($stmt)) {
                                $ret[] = array($item_array['primary_key_name'] => $ar[$item_array['primary_key_name']], $value_name => $ar[$value_name]);
                            }
                        }
                    }
                }
            } else {
                $query = 'SELECT `' . $item_array['primary_key_name'] . '`, `' . $value_name_l . '` AS ' . $value_name . ' FROM ' . DB_PREFIX . '_' . $item_array['primary_key_table'].((isset($parameters['addwhere']) && $parameters['addwhere'] != '') ? ' WHERE '.$parameters['addwhere'] : '');
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
                foreach($ret as $k=>$v){
                //while ($ar = $DBC->fetch($stmt)) {
                    $this->total_in_select[$item_array['name']] ++;
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

            if (isset($parameters['multiselect']) && (int) $parameters['multiselect'] == 1) {
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
                    $item_array['value'] = (array) $item_array['value'];
                }
                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        $this->total_in_select[$item_array['name']] ++;
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

                if($realquery != ''){
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
                $stmt = $DBC->query($query);
                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        $this->total_in_select[$item_array['name']] ++;
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


    function get_select_by_query_multi($item_array, $model = null){


        if (isset($item_array['parameters'])) {
            $parameters = $item_array['parameters'];
        } else {
            $parameters = array();
        }

        $rs='';
        if(isset($parameters['mode']) && $parameters['mode'] == 'checkbox'){
            $checkbox_mode = true;
        } else {
            $checkbox_mode = false;
        }

        $size = 0;
        if(isset($parameters['multiselect_size']) && intval($parameters['multiselect_size']) > 0){
            $size = intval($parameters['multiselect_size']);
        }

        //
        $values = $item_array['value'];
        //print_r($values);
        if (!is_array($values)) {
            $values = (array) $values;
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


        if (1 == intval($this->getConfigValue('apps.realty.off_system_ajax'))) {
            $depended_element_name = '';
            if (isset($parameters['depended']) && $parameters['depended'] != '') {
                $depended_element_name = trim($parameters['depended']);
                list($a, $b)=explode(',', $depended_element_name);
                if($b!=''){
                    $depended_element_name=$a;
                    $depended_element_name_key=$b;
                }else{
                    $depended_element_name_key=$depended_element_name;
                }
            }





            if ($depended_element_name != '') {
                $depended_value = intval($model[$depended_element_name]['value']);

                if ($depended_value != 0) {
                    $query = 'SELECT `' . $item_array['primary_key_name'] . '`, `' . $value_name_l . '` AS ' . $value_name . ' FROM ' . DB_PREFIX . '_' . $item_array['primary_key_table'] . ' WHERE `' . $depended_element_name . '`=?'.($parameters['addwhere']>'' ? ' and '.$parameters['addwhere'] : '');

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
                }else{
                    $query = '';
                }
            }
        }

        if($query != ''){
            if(!empty($query_params)){
                $stmt = $DBC->query($query, $query_params);
            }else{
                $stmt = $DBC->query($query);
            }

            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {

                    //var_dump($ar);

                    $this->total_in_select[$item_array['name']] ++;
                    $value = $ar[$value_name_l];
                    $value = trim($value);
                    $value = htmlspecialchars($value, ENT_QUOTES, SITE_ENCODING);
                    $selected = false;

                    if (is_array($values) && in_array($ar[$item_array['primary_key_name']], $values)) {
                        $selected = true;
                    }elseif(!is_array($values) && $ar[$item_array['primary_key_name']] == $values){
                        $selected = true;
                    }

                    $options[] = array($ar[$item_array['primary_key_name']], $value, $selected);
                }
            }
        }



        //print_r($options);

        $this->total_in_select[$item_array['name']] = 0;

        if ($checkbox_mode) {
            $rs .= '<div class="multiselect_set multiselect_set_c multiselect_set_' . $item_array['name'] . '" id="' . $item_array['name'] . '">';
            if(!empty($options)){
                foreach($options as $option){
                    $this->total_in_select[$item_array['name']] ++;
                    $value = $option[1];
                    $value = trim($value);
                    $value = htmlspecialchars($value, ENT_QUOTES, SITE_ENCODING);
                    $rs .= '<div class="multiselect_set_item"'.($parameters['data_field']>'' ? ' data-'.$parameters['data_field'].'="'.$ar[$parameters['data_field']].'"' : '').'><label><input type="checkbox"' . ($option[2] == 1 ? ' checked="checked"' : '') . ' name="' . $item_array['name'] . '[]" value="' . $option[0] . '"> <span>' . $value . '</span></label></div>';

                }
            }
            $rs .= '</div>';
        } else {
            $rs .= '<div class="multiselect_set multiselect_set_s multiselect_set_' . $item_array['name'] . '">';
            $rs .= '<select size="'.$size.'" name="' . $item_array['name'] . '[]" id="' . $item_array['name'] . '" multiple="multiple" class="' . $this->classes['select'] . '">';
            if(!empty($options)){
                foreach($options as $option){
                    $this->total_in_select[$item_array['name']] ++;
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
    function get_single_select_box_by_query_multiple($item_array) {
        $values = array();
        if (isset($item_array['values_array'])) {
            $values = (array) $item_array['values_array'];
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
                $this->total_in_select[$item_array['name']] ++;
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
    function get_select_box_row($item_array) {
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
    function get_uploader_row($item_array) {
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
    function get_pluploader_row($item_array) {
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
    function get_uploadify_row($item_array) {
        $rs = '';
        $rs .= '<tr  class="row3">';
        $rs .= '<td colspan="2">';
        $rs .= '<h2>' . Multilanguage::_('L_PHOTO_1') . '</h2>';

        //$action, $table_name, $key, $record_id
        $_count = 0;
        $rs .= $this->getImageListAdmin($item_array['action'], $item_array['table_name'], $item_array['primary_key'], $item_array['primary_key_value'], $_count);

        $rs .= '</td>';
        $rs .= '</tr>';
        if ($this->getConfigValue('photo_per_data') > 0 AND $item_array['action'] == 'data') {
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
    function get_uploadify_file_row($item_array) {
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
    function get_separator_row($item_array) {
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
    function get_injector_row($item_array) {
        $form_injector = new Form_Injector();


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
    function get_select_box_structure_row($item_array) {
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

    function get_structure_row($item_array) {

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
    function get_select_box_structure_simple_multiple_row($item_array) {
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

    function get_shop_select_box_structure_row($item_array) {
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
    function get_service_type_select_box_structure_row($item_array) {

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
    function get_checkbox_box_row($item_array) {
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
    function get_textarea_row($item_array) {
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
    function get_textarea_editor_row($item_array) {
        //sleep(1);
        $id = $item_array['name'] . '_' . md5(time() . '_' . rand(10, 99));
        $rs = '';
        if (isset($item_array['editor']) AND ( $item_array['editor'] !== 'editor')) {
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
    function get_grade_row($item_array) {
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

    /**
     * Get check box
     * @param array $item_array
     * @return string
     */
    function get_checkbox ( $item_array ) {

        $dp=array();
        $dp['id']=$this->form_id.'_'.$item_array['name'];
        $dp['placeholder']=$item_array['title'];
        $dp['class']=(isset($this->classes['input']) ? $this->classes['input'] : '');

        $dp['class']=(isset($this->classes['checkbox']) ? $this->classes['checkbox'] : '');
        $isChecked=false;
        if($item_array['value']==1){
            $isChecked=true;
        }

        $html=$this->form_decorator->decorateCheckboxInput($item_array['name'], $item_array['value'], $isChecked, $dp);

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
    function get_select_box($item_array) {
        $parameters = array();
        if (isset($item_array['parameters'])) {
            $parameters = $item_array['parameters'];
        }

        if (isset($parameters['multiselect']) && 1 == (int) $parameters['multiselect']) {
            $rs = $this->form_decorator->decorateMultiselectItem($item_array['name'], $item_array['select_data'], $item_array['values_array']);
            /*foreach ( $item_array['select_data'] as $item_id => $item_value ) {
                $rs .= '<input type="checkbox" name="'.$item_array['name'].'[]" value="'.$item_id.'"'.((isset($item_array['values_array']) && in_array($item_id, $item_array['values_array'])) ? ' checked="checked"' : '').'>'.$item_value.'<br/>';
            }*/
        }else{

            $rs = '<select class="' . $this->classes['select'] . '" name="' . $item_array['name'] . '">';
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

    function get_radiogroup($item_array) {
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
    function get_captcha_input($item_array) {
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
    function generateCaptchaSessionKey() {
        return md5(time() . rand(9999, 4) . 'random key captcha string core sitebill');
    }

    /**
     * Get date input
     * @param array $item_array
     * @return string
     */
    function get_date_input($item_array) {
        $string = '';
        $string .= '<script type="text/javascript">$(document).ready(function() {$( "#' . $item_array['name'] . '" ).datepicker({showOn: "button",dateFormat: "dd.mm.yy",buttonImage: "' . SITEBILL_MAIN_URL . '/img/calendar.gif",buttonImageOnly: true});});</script>';
        $string .= "<tr  class=\"row3\" alt=\"" . $item_array['name'] . "\">\n";

        $string .= '<td>' . $item_array['title'] . ($item_array['required'] == "on" ? '<span style="color: red;">*</span>' : '').'</td>'."\n";

        if (preg_match('/(\d{4,4})-(\d{2,2})-(\d{2,2}) (\d{2,2}):(\d{2,2}):(\d{2,2})/', $item_array['value'])) {
            $item_array['value'] = date('d.m.Y', strtotime($item_array['value']));
        } elseif (preg_match('/(\d{4,4})-(\d{2,2})-(\d{2,2})/', $item_array['value'])) {
            $item_array['value'] = date('d.m.Y', strtotime($item_array['value']));
        } elseif ($item_array['value'] == 0 || $item_array['value'] == '') {

            $item_array['value'] = '';
        } else {
            $item_array['value'] = date('d.m.Y', $item_array['value']);
        }

        $string .= '<td><input type="text" name="'.$item_array['name'].'" id="'.$item_array['name'].'" value="'.htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING).'" size="10" maxlength="'.$item_array['maxlength'].'"></td>';
        $string .= '</tr>'."\n";

        /* Return html code */
        return $string;
    }

    function compile_date_element($item_array) {
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
          }else */if (preg_match('/(\d{4,4})-(\d{2,2})-(\d{2,2}) (\d{2,2}):(\d{2,2}):(\d{2,2})/', $item_array['value'])) {
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

    function compile_dtdatetime_element($item_array) {

        $id = $item_array['name'].'_' . md5(time() . rand(100, 999));

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
        if ($parameters['noSeconds'] == 1) {
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
        $html = '<div id="' . $id . '">'.$html.'</div>';
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $string . $html,
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_dtdate_element($item_array) {

        $id = $item_array['name'].'_' . md5(time() . rand(100, 999));

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

    function compile_dttime_element($item_array) {

        $id = $item_array['name'].'_' . md5(time() . rand(100, 999));

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

    function compile_datetime_element($item_array) {
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

    /**
     * Get safe string input
     * @param array  $item_array
     * @return string
     */
    function get_safe_text_input($item_array) {


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

    function get_geodata_input($item_array) {
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
     * @param array  $item_array
     * @return string
     */
    function get_price_input($item_array) {
        if ($item_array['value'] != '') {
            $value = number_format((int) str_replace(' ', '', $item_array['value']), 0, ',', ' ');
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
     * @param array  $item_array
     * @return string
     */
    function get_email_input($item_array) {

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
     * Get safe string input for mobile phone number
     * @param array  $item_array
     * @return string
     */
    function get_mobilephone_input($item_array) {

        /* Un-quote slashes */
        $value = htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING);
        $id = md5($item_array['name'] . '_' . rand(100, 999));
        $string = '';
        if (!defined('NO_DYNAMIC_INCS') || !NO_DYNAMIC_INCS) {
            $string = '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/system/js/jquery.maskedinput.min.js"></script>';
        }
        $string .= '<script type="text/javascript">$(document).ready(function() {$.mask.definitions["h"] = "[0-9]"; $("#' . $id . '").mask("h (hhh) hhh-hh-hh");});</script>';

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

    /**
     * Get password input
     * @param array  $item_array
     * @return string
     */
    function get_password_input($item_array) {

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
    function get_photo_input($item_array) {

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
                case '3' : {
                        $string .= '<div class="checkbox"><label><input type="checkbox" name="delpic" value="yes">Удалить фото</label></div>';
                        break;
                    }
                case '4md' :
                case '4' : {
                        $string .= '<label class="form-check-label"><input type="checkbox" class="form-check-input" name="delpic" value="yes">Удалить фото</label>';
                        break;
                    }
                default : {
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
    function get_hidden_input($item_array) {
        $string = '';
        $string .= '<input type="hidden" name="'.$item_array['name'].'" value="'.$item_array['value'].'" />';
        return $string;
    }

    function get_tlocation($item_array) {




        $string = '';




        $params = $item_array['parameters'];
        if (isset($params['visibles'])) {
            $visibles = explode('|', $params['visibles']);
        } else {
            $visibles = array();
        }

        if (isset($params['show_names'])) {
            $show_names = (int) $params['show_names'];
        } else {
            $show_names = 1;
        }

        if (isset($params['names'])) {
            $_x = array();
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
        $script_code .= '<script>$(document).ready(function(){TLocationForm.setHandler("' . $uniq_class_name . '", ' . (int) $this->getConfigValue('link_street_to_city') . ')});</script>';

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

            if ((int) $values['country_id'] != 0) {
                $query = 'SELECT region_id, name FROM ' . DB_PREFIX . '_region WHERE country_id=? ORDER BY name ASC';
                $stmt = $DBC->query($query, array($values['country_id']));
            } elseif (isset($defaults['country_id']) && (int) $defaults['country_id'] != 0) {
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
            if ((int) $values['region_id'] != 0) {
                $query = 'SELECT city_id, name FROM ' . DB_PREFIX . '_city WHERE region_id=? ORDER BY name ASC';
                $stmt = $DBC->query($query, array($values['region_id']));
            } elseif (isset($defaults['region_id']) && (int) $defaults['region_id'] != 0) {
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
                if ((int) $values['city_id'] != 0) {
                    $query = 'SELECT id, name FROM ' . DB_PREFIX . '_district WHERE city_id=? ORDER BY name ASC';
                    $stmt = $DBC->query($query, array($values['city_id']));
                } elseif (isset($defaults['city_id']) && (int) $defaults['city_id'] != 0) {
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
                if ((int) $values['city_id'] != 0) {
                    $query = 'SELECT street_id, name FROM ' . DB_PREFIX . '_street WHERE city_id=? ORDER BY name ASC';
                    $stmt = $DBC->query($query, array($values['city_id']));
                } elseif (isset($defaults['city_id']) && (int) $defaults['city_id'] != 0) {
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
                if ((int) $values['city_id'] != 0) {
                    $query = 'SELECT id, name FROM ' . DB_PREFIX . '_district WHERE city_id=? ORDER BY name ASC';
                    $stmt = $DBC->query($query, array($values['city_id']));
                } elseif (isset($defaults['city_id']) && (int) $defaults['city_id'] != 0) {
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
                if ((int) $values['district_id'] != 0) {
                    $query = 'SELECT street_id, name FROM ' . DB_PREFIX . '_street WHERE district_id=? ORDER BY name ASC';
                    $stmt = $DBC->query($query, array($values['district_id']));
                } elseif (isset($defaults['district_id']) && (int) $defaults['district_id'] != 0) {
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

    function compile_select_box_structure_multiple_checkbox($item_array) {
        if (!isset($item_array['values_array'])) {
            $item_array['values_array'] = array(0 => 0);
        }
        if (!is_array($item_array['values_array'])) {
            $item_array['values_array'] = (array) $item_array['values_array'];
        }
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();

        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $Structure_Manager->getCategoryCheckboxes($item_array['name'], $item_array['values_array']),
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function get_select_box_by_query_as_checkboxes($item_array, $model = null) {
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
                $this->total_in_select[$item_array['name']] ++;
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

    function getAgreementFormBlock(){

        if($this->getConfigValue('post_form_agreement_enable_note')){
            if(Multilanguage::is_set('L_AGREEMENT_TEXT_FORM_NOTE')){
                $text=Multilanguage::_('L_AGREEMENT_TEXT_FORM_NOTE');
                /*if(!empty($this->form_decorator) && method_exists($this->form_decorator, 'decorateAgreementFormBlockNote')){
                    return $this->form_decorator->decorateAgreementFormBlockNote($text);
                }else{
                    return '<div class="agreement_form_block"><div class="agreement_form_block_note">'.$text.'</div></div>';
                }*/
                return $this->form_decorator->decorateAgreementFormBlockNote($text);
            }
        }else{
            if(Multilanguage::is_set('L_AGREEMENT_TEXT_FORM')){
                $text=Multilanguage::_('L_AGREEMENT_TEXT_FORM');
            }else{
                $text=_e($this->getConfigValue('post_form_agreement_text_add'));
            }

            $id=md5(time().rand(100,999));
            /*if(!empty($this->form_decorator) && method_exists($this->form_decorator, 'decorateAgreementFormBlockCheckbox')){
                return $this->form_decorator->decorateAgreementFormBlockCheckbox($text, $id);
            }else{
                return '<div class="agreement_form_block"><input type="hidden" name="agreement_el" value="1"><div class="agreement_form_block_input"><input id="agreement_form_block_input_'.$id.'" type="checkbox" name="agreement" value="1"></div><label for="agreement_form_block_input_'.$id.'">'.$text.'</label></div>';
            }*/
            return $this->form_decorator->decorateAgreementFormBlockCheckbox($text, $id);
        }

    }

}
