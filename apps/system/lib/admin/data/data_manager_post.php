<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

class Data_Manager_Post extends Data_Manager {
    function __construct()
    {
        $this->setConfigValue('apps.geodata.use_google_places_api', 0);
        parent::__construct();

        $this->data_model[$this->table_name]['post_key'] = array(
            'title' => 'post_key',
            'name' => 'post_key',
            'type' => 'hidden',
            'value' => SConfig::getConfigValueStatic('apps.api.post_key'),
            'dbtype' => '0',
        );

        foreach ($this->data_model[$this->table_name] as $item) {
            if ( $item['name'] == 'active') {
                $this->data_model[$this->table_name][$item['name']]['value'] = 0;
            }

            if ( $item['type'] == 'docuploads') {
                unset($this->data_model[$this->table_name][$item['name']]);
            }
            if ( $item['type'] == 'uploads' and $item['name'] == 'image') {
                unset($this->data_model[$this->table_name][$item['name']]);

                $this->data_model[$this->table_name]['image_cache'] = array(
                    'title' => $item['title'],
                    'name' => 'image_cache',
                    'type' => 'textarea',
                    'parameters' => [
                        'serialize_array' => 1
                    ]
                );
            }

            if ( $item['type'] == 'select_by_query' and  $item['name'] != 'currency_id') {
                $this->data_model[$this->table_name][$item['name']]['parameters']['autocomplete'] = 1;
            }
            if ( $item['type'] == 'select_box_structure' ) {
                $this->data_model[$this->table_name][$item['name']]['type'] = 'textarea';
                $this->data_model[$this->table_name][$item['name']]['parameters']['structure_chain'] = 1;
                $this->data_model[$this->table_name][$item['name']]['value'] = '';

                unset($this->data_model[$this->table_name][$item['name']]['parameters']['type']);
                unset($this->data_model[$this->table_name][$item['name']]['parameters']['level_required']);
            }
        }
    }

    function _new_doneAction()
    {
        $result = parent::_new_doneAction();
        if ( $this->getError() ) {
            return $result;
        }
        return 'new record id = '.$this->get_new_record_id();
    }


    /**
     * Main
     * @param void
     * @return string
     */
    function main() {
        $do = $this->getRequestValue('do');
        if ( $do == '' ) {
            $do = 'new';
        }
        $action = '_' . $do . 'Action';

        if (!method_exists($this, $action)) {
            $action = '_defaultAction';
        }

        return $this->$action();
    }

    /**
     * Get form for edit or new record
     * @param array $form_data
     * @param string $do
     * @param int $language_id
     * @param string $button_title
     * @return string
     */
    function get_form($form_data = array(), $do = 'new', $language_id = 0, $button_title = '', $action = 'index.php') {
        $_SESSION['allow_disable_root_structure_select'] = true;
        global $smarty;
        if ($button_title == '') {
            $button_title = Multilanguage::_('L_TEXT_SAVE');
        }
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');
        $form_generator = new Form_Generator();

        $rs .= $this->get_ajax_functions();

        $topic_id = (int) $form_data['topic_id']['value'];
        $current_id = (int) $form_data[$this->primary_key]['value'];

        if ($topic_id != 0 && $current_id != 0) {

            $href = $this->getRealtyHREF($current_id, false, array('topic_id' => $topic_id, 'alias' => $form_data['translit_alias']['value']));
            $rs .= '<div class="row"><a class="btn btn-success pull-right" href="' . $href . '" target="_blank">' . Multilanguage::_('L_SEE_AT_SITE') . '</a></div>';
        }

        if (1 == $this->getConfigValue('apps.geodata.enable')) {
            $rs .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/geodata/js/geodata.js"></script>';
        }
        $rs .= '<form method="post" class="form-horizontal" action="' . ($this->get_default_form_action()?$this->get_default_form_action():$action) . '">';
        /* $id=md5('data_form_'.time());
          $rs .= '<form method="post" id="'.$id.'" class="form-horizontal" action="index.php" enctype="multipart/form-data">';
          $rs .= '<script>var control_visibility="'.$id.'";</script>'; */
        if ($this->getError()) {
            $smarty->assign('form_error', $form_generator->get_error_message_row($this->GetErrorMessage()));
        }

        $el = $form_generator->compile_form_elements($form_data);

        if ($do == 'new') {
            $el['private'][] = array('html' => '<input type="hidden" name="do" value="new_done" />');
            $el['private'][] = array('html' => '<input type="hidden" name="' . $this->primary_key . '" value="' . $this->getRequestValue($this->primary_key) . '" />');
        } else {
            $el['private'][] = array('html' => '<input type="hidden" name="do" value="edit_done" />');
            $el['private'][] = array('html' => '<input type="hidden" name="' . $this->primary_key . '" value="' . $form_data[$this->primary_key]['value'] . '" />');
        }
        $el['private'][] = array('html' => '<input type="hidden" name="action" value="' . $this->action . '">');
        $el['private'][] = array('html' => '<input type="hidden" name="language_id" value="' . $language_id . '">');

        $el['form_header'] = $rs;
        $el['form_footer'] = '</form>';

        if ($do != 'new') {
            $el['controls']['apply'] = array('html' => '<button id="apply_changes" class="btn btn-info">' . Multilanguage::_('L_TEXT_APPLY') . '</button>');
        }
        $el['controls']['submit'] = array('html' => '<button id="formsubmit" onClick="return SitebillCore.formsubmit(this);" name="submit" class="btn btn-primary">' . $button_title . '</button>');

        $smarty->assign('form_elements', $el);
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data_admin.tpl')) {
            $tpl_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data_admin.tpl';
        } elseif (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data.tpl')) {
            $tpl_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data.tpl';
        } else {
            $tpl_name = $this->getAdminTplFolder() . '/data_form.tpl';
        }
        $html = $smarty->fetch($tpl_name);
        /* if(file_exists(SITEBILL_MAIN_URL.'/template/frontend/'.$this->getConfigValue('theme').'/js/custom_data_admin.js')){

          } */

        return $html;
    }



}
