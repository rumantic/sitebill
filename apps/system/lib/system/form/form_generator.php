<?php

use system\lib\system\form\Form_Injector;

/**
 * Form generator
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/traits/FormCompileBasicTrait.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/traits/FormCompileSelectTrait.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/traits/FormCompileMediaTrait.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/traits/FormCompileComplexTrait.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/traits/FormAssemblyTrait.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/traits/FormRowRendererTrait.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/traits/FormWidgetRendererTrait.php';

class Form_Generator extends SiteBill
{
    use \system\lib\system\apps\traits\ContextTrait;
    use \system\lib\system\form\traits\MobilePhoneTrait;
    use FormCompileBasicTrait;
    use FormCompileSelectTrait;
    use FormCompileMediaTrait;
    use FormCompileComplexTrait;
    use FormAssemblyTrait;
    use FormRowRendererTrait;
    use FormWidgetRendererTrait;

    protected $form_id = null;
    protected $use_placeholders = false;
    protected $bootstrap_version = '2';
    protected $form_decorator;

    public function getFormDecorator()
    {
        return $this->form_decorator;
    }

    static $cache;

    protected function generateFormId()
    {
        $this->form_id = 'frm_' . md5(time() . rand(10, 99));
    }

    public function getFormId()
    {
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
    function __construct()
    {
        parent::__construct();
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_decorator.php';
        if (!defined('ADMIN')) {
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/system/local_form_decorator.php')) {
                require_once SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/system/local_form_decorator.php';
                $decorator_class = 'Local_Form_Decorator';
            } else {
                $decorator_class = 'Form_Decorator';
            }
        } else {
            $decorator_class = 'Form_Decorator';
        }

        $this->form_decorator = new $decorator_class();
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
        } elseif ($bootstrap_version == 'tailwind') {
            $this->classes['input'] = 'w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm';
            $this->classes['select'] = 'w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm bg-white';
            $this->classes['textarea'] = 'w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm';
            $this->classes['checkbox'] = 'h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500';
            $this->bootstrap_version = $bootstrap_version;
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

    public function setUsePlaceholders($val = true)
    {
        $this->use_placeholders = $val;
    }

    public function getScripts($form_data)
    {
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
                $scripts[] = SITEBILL_MAIN_URL . '/apps/system/js/dropzone/dropzone.js?v=2';
                $scripts[] = SITEBILL_MAIN_URL . '/apps/system/js/dataimagelist.js?v=2';
                $styles[] = SITEBILL_MAIN_URL . '/apps/system/js/dropzone/dropzone.css?v=1';
            } elseif ($item_array['type'] == 'uploads') {
                $scripts[] = SITEBILL_MAIN_URL . '/apps/system/js/dropzone/dropzone.js?v=2';
                $scripts[] = SITEBILL_MAIN_URL . '/apps/system/js/dataimagelist.js?v=2';
                $styles[] = SITEBILL_MAIN_URL . '/apps/system/js/dropzone/dropzone.css?v=1';
            } elseif ($item_array['type'] == 'tlocation') {
                $styles[] = SITEBILL_MAIN_URL . '/apps/system/js/bootstrap/css/bootstrap-combobox.css';
                $scripts[] = SITEBILL_MAIN_URL . '/apps/system/js/bootstrap/js/bootstrap-combobox.js';
                $scripts[] = SITEBILL_MAIN_URL . '/apps/tlocation/js/form_utils.js';
            } elseif ($item_array['type'] == 'geodata') {
                //$scripts[]=SITEBILL_MAIN_URL.'/apps/system/js/md5.js';
                $scripts[] = SITEBILL_MAIN_URL . '/apps/geodata/js/geodata.js?v=1';
            } elseif ($item_array['type'] == 'mobilephone') {
                $scripts[] = $this->get_mobilephone_input_js_plugin();
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
    }














    function getAgreementFormBlock()
    {

        if ($this->getConfigValue('post_form_agreement_enable_note')) {
            if (Multilanguage::is_set('L_AGREEMENT_TEXT_FORM_NOTE')) {
                $text = Multilanguage::_('L_AGREEMENT_TEXT_FORM_NOTE');
                /*if(!empty($this->form_decorator) && method_exists($this->form_decorator, 'decorateAgreementFormBlockNote')){
                    return $this->form_decorator->decorateAgreementFormBlockNote($text);
                }else{
                    return '<div class="agreement_form_block"><div class="agreement_form_block_note">'.$text.'</div></div>';
                }*/
                return $this->form_decorator->decorateAgreementFormBlockNote($text);
            }
        } else {
            if (Multilanguage::is_set('L_AGREEMENT_TEXT_FORM')) {
                $text = Multilanguage::_('L_AGREEMENT_TEXT_FORM');
            } else {
                $text = _e($this->getConfigValue('post_form_agreement_text_add'));
            }

            $id = md5(time() . rand(100, 999));
            /*if(!empty($this->form_decorator) && method_exists($this->form_decorator, 'decorateAgreementFormBlockCheckbox')){
                return $this->form_decorator->decorateAgreementFormBlockCheckbox($text, $id);
            }else{
                return '<div class="agreement_form_block"><input type="hidden" name="agreement_el" value="1"><div class="agreement_form_block_input"><input id="agreement_form_block_input_'.$id.'" type="checkbox" name="agreement" value="1"></div><label for="agreement_form_block_input_'.$id.'">'.$text.'</label></div>';
            }*/
            return $this->form_decorator->decorateAgreementFormBlockCheckbox($text, $id);
        }

    }

}
