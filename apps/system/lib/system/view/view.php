<?php

/**
 * View models in table format
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class Table_View extends Data_Model {

    protected $absolute_urls = false;

    /**
     * Construct
     * @param void
     * @return void
     */
    function __construct() {
        $this->SiteBill();
    }

    public function setAbsoluteUrls() {
        $this->absolute_urls = true;
    }

    /**
     * Compile view
     * @param $form_data form data
     * @return string
     */
    function compile_view($form_data) {
        //echo '<pre>';
        //print_r($form_data);
        //echo '</pre>';
        foreach ($form_data as $item_id => $item_array) {
            switch ($item_array['type']) {
                case 'select_box':
                    $rs .= $this->get_select_box_row($item_array);
                    break;

                case 'select_by_query':
                    $rs .= $this->get_select_box_by_query_row($item_array);
                    break;
                case 'select_by_query_multi':
                    $rs .= $this->get_select_by_query_multi_row($item_array);
                    break;
                case 'select_box_structure':
                    $rs .= $this->get_select_box_structure_row($item_array);
                    break;

                case 'uploadify_image':
                    $rs .= $this->get_uploadify_preview($item_array);
                    break;

                case 'checkbox':
                    $rs .= $this->get_checkbox_box_row($item_array);
                    break;

                case 'textarea':
                    $rs .= $this->get_textarea_row($item_array);
                    break;

                case 'textarea_editor':
                    $rs .= $this->get_textarea_row($item_array);
                    break;

                case 'safe_string':
                    $rs .= $this->get_safe_text_input($item_array);
                    break;
                case 'client_id':
                    $rs .= $this->get_client_id_row($item_array);
                    break;
                case 'injector':
                    $rs .= $this->get_injector_row($item_array, $form_data);
                    break;

                case 'price':
                    $rs .= $this->get_safe_text_input($item_array);
                    break;

                case 'email':
                    $rs .= $this->get_safe_text_input($item_array);
                    break;

                case 'mobilephone':
                    $rs .= $this->get_safe_text_input($item_array);
                    break;
                case 'dtdatetime':
                    $rs .= $this->get_safe_text_input($item_array);
                    break;
                case 'dtdate':
                    $rs .= $this->get_safe_text_input($item_array);

                    break;
                case 'dttime':
                    $rs .= $this->get_safe_text_input($item_array);
                    break;
                case 'primary_key':
                    $rs .= $this->get_safe_text_input($item_array);
                    break;
                case 'docuploads':
                    $rs .= $this->get_docuploads_preview($item_array);
                    break;
                case 'uploads':
                    $rs .= $this->get_uploads_preview($item_array);
                    break;
                case 'tlocaion':
                    $rs .= $this->get_tlocaion_row($item_array);
                    break;
            }
        }
        return $rs;
    }

    /**
     * Get select box row
     * @param array $item_array
     * @return string
     */
    function get_select_box_row($item_array) {
        $rs = '<tr>';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        $rs .= '</td>';
        $rs .= '<td>';
        $rs .= $item_array['value_string'];
        $rs .= '</td>';
        $rs .= '</tr>';

        return $rs;
    }

    /**
     * Get select box row
     * @param array $item_array
     * @return string
     */
    function get_select_box_by_query_row($item_array) {
        $rs = '<tr>';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        $rs .= '</td>';
        $rs .= '<td>';

        $item_array['value_string'] = $this->get_string_value_by_id($item_array['primary_key_table'], $item_array['primary_key_name'], $item_array['value_name'], $item_array['value']);

        $rs .= $item_array['value_string'];
        $rs .= '</td>';
        $rs .= '</tr>';

        return $rs;
    }

    function get_select_by_query_multi_row($item_array) {
        $rs = '<tr>';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        $rs .= '</td>';
        $rs .= '<td>';

        $parameters = $model_array[$key]['parameters'];
        $name = $model_array[$key]['value_name'];
        if (1 === intval($this->getConfigValue('apps.language.use_langs')) && 0 === intval($parameters['no_ml'])) {
            $name .= $this->getLangPostfix($this->getCurrentLang());
        }

        /* $DBC=DBC::getInstance();
          $query='SELECT `field_value` FROM '.DB_PREFIX.'_multiple_field WHERE `table_name`=? AND `field_name`=? AND `primary_id`=?';
          $stmt=$DBC->query($query, array($table_name, $key, $primary_key_value));

          if($stmt){
          while($ar=$DBC->fetch($stmt)){
          $model_array[$key]['value'][] = $ar['field_value'];
          }
          }

          if(!empty($model_array[$key]['value'])){
          $query='SELECT `'.$name.'` FROM '.DB_PREFIX.'_'.$model_array[$key]['primary_key_table'].' WHERE `'.$model_array[$key]['primary_key_name'].'` IN ('.implode(',', $model_array[$key]['value']).')';

          $stmt=$DBC->query($query);
          if($stmt){
          while($ar=$DBC->fetch($stmt)){
          $model_array[$key]['value_string'][] = $ar[$name];
          }
          }
          } */

        $item_array['value_string'] = $this->get_string_value_by_id($item_array['primary_key_table'], $item_array['primary_key_name'], $item_array['value_name'], $item_array['value']);

        $rs .= $item_array['value_string'];
        $rs .= '</td>';
        $rs .= '</tr>';

        return $rs;
    }

    function get_client_id_row($item_array) {
        $rs = '<tr>';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        $rs .= '</td>';
        $rs .= '<td>';
        $rs .= $item_array['value_string'];
        $rs .= '</td>';
        $rs .= '</tr>';

        return $rs;
    }

    function get_injector_row($item_array, $model) {
        switch ( $item_array['name'] ) {
            case 'booking':
                $form_injection = new \reservation\admin\Form_Injection();
                break;
            case 'contact_id':
                $form_injection = new \client\admin\Form_Injection();
                break;
        }

        $rs = '<tr>';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        $rs .= '</td>';
        $rs .= '<td>';
        if ( isset($form_injection) ) {
            $rs .= $form_injection->get_content($item_array, null, $model);
        }
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
        $category_structure = $Structure_Manager->loadCategoryStructure();

        $rs = '<tr>';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        $rs .= '</td>';
        $rs .= '<td>';

        $params['topic_id'] = $item_array['value'];
        $rs .= $this->get_category_breadcrumbs_string($params, $category_structure);

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
        $rs = '<tr>';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        $rs .= '</td>';
        $rs .= '<td>';
        $rs .= $this->get_checkbox($item_array);
        $rs .= '</td>';
        $rs .= '</tr>';

        return $rs;
    }

    /**
     * Get uploadify row
     * @param array $item_array
     * @return string
     */
    function get_uploadify_row($item_array) {

        $rs .= '<tr>';
        $rs .= '<td colspan="2">';
        $rs .= '<h2>' . Multilanguage::_('L_PHOTO') . '</h2>';

        //$action, $table_name, $key, $record_id

        $rs .= $this->getImageListAdmin($item_array['action'], $item_array['table_name'], $item_array['primary_key'], $item_array['primary_key_value']);
        $rs .= '</td>';
        $rs .= '</tr>';
        //echo $rs;
        //exit;

        return $rs;
    }

    /**
     * Get check box
     * @param array $item_array
     * @return string
     */
    function get_checkbox($item_array) {
        if ($item_array['value'] == 1) {
            $rs .= Multilanguage::_('L_YES');
        } else {
            $rs .= Multilanguage::_('L_NO');
        }
        return $rs;
    }

    /**
     * Get textarea row
     * @param array $item_array
     * @return string
     */
    function get_textarea_row($item_array) {
        $rs = '<tr>';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        $rs .= '</td>';
        $rs .= '<td>';
        $rs .= $item_array['value'];
        $rs .= '</td>';
        $rs .= '</tr>';

        return $rs;
    }

    function get_tlocaion_row($item_array) {
        $rs = '<tr>';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        $rs .= '</td>';
        $rs .= '<td>';
        $rs .= $item_array['tlocation_string'];
        $rs .= '</td>';
        $rs .= '</tr>';

        return $rs;
    }

    function get_uploadify_preview($item_array) {

        $rs = '<tr>';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        $rs .= '</td>';
        $rs .= '<td class="view_table_uploads">';

        if (is_array($item_array['image_array']) && count($item_array['image_array']) > 0) {
            foreach ($item_array['image_array'] as $it) {
                $rs .= '<img src="' . SITEBILL_MAIN_URL . '/img/data/' . $it['preview'] . '">';
            }
        }

        $rs .= '</td>';
        $rs .= '</tr>';

        return $rs;
    }

    function get_uploads_preview($item_array) {
        $rs = '<tr>';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        $rs .= '</td>';
        $rs .= '<td class="view_table_uploads">';

        if (is_array($item_array['value']) && count($item_array['value']) > 0) {
            foreach ($item_array['value'] as $it) {
                $rs .= '<div>';
                if ($this->absolute_urls) {
                    $rs .= '<a href="' . $this->getServerFullUrl() . '/img/data/' . $it['normal'] . '" target="_blank"><img style="max-width:300px;" src="' . $this->getServerFullUrl() . '/img/data/' . $it['preview'] . '"></a>';
                } else {
                    if ( $it['remote'] == 'true' ) {
                        $rs .= '<a href="' . $it['normal'] . '" target="_blank"><img style="max-width:300px;" src="' . $it['preview'] . '"></a>';
                    } else {
                        $rs .= '<a href="' . SITEBILL_MAIN_URL . '/img/data/' . $it['normal'] . '" target="_blank"><img style="max-width:300px;" src="' . SITEBILL_MAIN_URL . '/img/data/' . $it['preview'] . '"></a>';
                    }
                }
                $rs .= '</div>';
            }
        }

        $rs .= '</td>';
        $rs .= '</tr>';

        return $rs;
    }

    function get_docuploads_preview($item_array) {
        $rs = '<tr>';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        $rs .= '</td>';
        $rs .= '<td class="view_table_docuploads">';

        if (is_array($item_array['value']) && count($item_array['value']) > 0) {
            foreach ($item_array['value'] as $it) {
                $rs .= '<div>';
                if ($this->absolute_urls) {
                    $rs .= '<a href="' . $this->getServerFullUrl() . '/img/mediadocs/' . $it['normal'] . '" target="_blank">' . $it['normal'] . '</a>';
                } else {
                    $rs .= '<a href="' . SITEBILL_MAIN_URL . '/img/mediadocs/' . $it['normal'] . '" target="_blank">' . $it['normal'] . '</a>';
                }
                $rs .= '</div>';
            }
        }

        $rs .= '</td>';
        $rs .= '</tr>';

        return $rs;
    }

    /**
     * Get safe string input
     * @param unknown_type $item_array
     * @return string
     */
    function get_safe_text_input($item_array) {

        /* Un-quote slashes */
        $value = stripslashes($value);
        /* HTML code */
        $string .= "<tr>\n";

        $string .= "<td class=\"$bg_color\">" . $item_array['title'] . "</td>\n";

        $string .= "<td class=\"$bg_color\">" . $item_array['value'] . "</td>\n";
        $string .= "</tr>\n";

        /* Return html code */
        return $string;
    }

}
