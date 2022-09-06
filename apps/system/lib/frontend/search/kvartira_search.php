<?php

/**
 * Kvartira search form
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class Kvartira_Search_Form extends SiteBill {

    protected $custom_elements = array();

    /**
     * Construct
     */
    function __construct() {
        parent::__construct();
    }

    /*
     * Set custom elements to search form model
     * Allow to change type, parameters and other properties of elemets or delete elements
     * string $el - model element name
     * mixed $params - array of model element description
     * change element if $params has type array
     * delete element if $params has type string and value ::delete::
     */

    function setCustomElements($el, $params) {
        $this->custom_elements[$el] = $params;
    }

    /*
     * Return model for search form
     * By default - this is "data" model
     */


    function getSearchFormModel() {
        $data_model = new Data_Model();
        $kvartira_model = $data_model->get_kvartira_model(true);
        $kvartira_model = $kvartira_model['data'];
        $kvartira_model = $this->cleanUpModel($kvartira_model);
        return $kvartira_model;
    }

    function cleanUpModel ( $model ) {
        foreach ( $model as $item_key => $item ) {
            if ( isset($item['parameters']['autocomplete']) ) {
                if ( $item['parameters']['autocomplete'] == 1 and  $item['parameters']['disable_autocomplete_on_search'] == 1 ) {
                    $model[$item_key]['parameters']['autocomplete'] = 0;
                }
            }
        }
        return $model;
    }

    /**
     * Main
     * @param
     * @return
     */
    function main() {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');
        $form_generator = new Form_Generator();

        $kvartira_model['data'] = $this->getSearchFormModel();

        if (!empty($this->custom_elements)) {
            $kvartira_model['data'] = $this->array_overlay($kvartira_model['data'], $this->custom_elements);
        }

        $kvartira_model['data'] = $data_model->init_model_data_from_request($kvartira_model['data'], true, true);




        $this->template->assert('ajax_functions', $this->get_ajax_functions());

        if ($this->getConfigValue('country_in_form') && isset($kvartira_model['data']['country_id'])) {
            if ($kvartira_model['data']['country_id']['type'] == 'select_by_query_multiple') {
                $this->template->assert('country_list', $form_generator->get_single_select_box_by_query_multiple($kvartira_model['data']['country_id'], $kvartira_model['data']));
            } else {
                $this->template->assert('country_list', $form_generator->get_single_select_box_by_query($kvartira_model['data']['country_id'], $kvartira_model['data']));
            }
        }
        if ($this->getConfigValue('district_in_form') && isset($kvartira_model['data']['district_id'])) {
            if ($kvartira_model['data']['district_id']['type'] == 'select_by_query_multiple') {
                $this->template->assert('district_list', $form_generator->get_single_select_box_by_query_multiple($kvartira_model['data']['district_id'], $kvartira_model['data']));
            } else {
                $this->template->assert('district_list', $form_generator->get_single_select_box_by_query($kvartira_model['data']['district_id'], $kvartira_model['data']));
            }
        }

        if ($this->getConfigValue('street_in_form') && isset($kvartira_model['data']['street_id'])) {
            $this->template->assert('street_list', $form_generator->get_single_select_box_by_query($kvartira_model['data']['street_id'], $kvartira_model['data']));
        }

        if ($this->getConfigValue('region_in_form') && isset($kvartira_model['data']['region_id'])) {
            if ($kvartira_model['data']['region_id']['type'] == 'select_by_query_multiple') {
                $this->template->assert('region_list', $form_generator->get_single_select_box_by_query_multiple($kvartira_model['data']['region_id'], $kvartira_model['data']));
            } else {
                $this->template->assert('region_list', $form_generator->get_single_select_box_by_query($kvartira_model['data']['region_id'], $kvartira_model['data']));
            }
        }

        if ($this->getConfigValue('metro_in_form') && isset($kvartira_model['data']['metro_id'])) {
            if ($kvartira_model['data']['metro_id']['type'] == 'select_by_query_multiple') {
                $this->template->assert('metro_list', $form_generator->get_single_select_box_by_query_multiple($kvartira_model['data']['metro_id'], $kvartira_model['data']));
            } else {
                $this->template->assert('metro_list', $form_generator->get_single_select_box_by_query($kvartira_model['data']['metro_id'], $kvartira_model['data']));
            }
        }

        if ($this->getConfigValue('city_in_form') && isset($kvartira_model['data']['city_id'])) {
            if ($kvartira_model['data']['city_id']['type'] == 'select_by_query_multiple') {
                $this->template->assert('city_list', $form_generator->get_single_select_box_by_query_multiple($kvartira_model['data']['city_id'], $kvartira_model['data']));
            } else {
                $this->template->assert('city_list', $form_generator->get_single_select_box_by_query($kvartira_model['data']['city_id'], $kvartira_model['data']));
            }
        }

        if (isset($kvartira_model['data']['currency_id']) && 1 == $this->getConfigValue('currency_enable')) {
            $this->template->assert('currency_list', $form_generator->get_single_select_box_by_query($kvartira_model['data']['currency_id']));
        }

        if ($this->getConfigValue('optype_in_form') && isset($kvartira_model['data']['optype'])) {
            if ($kvartira_model['data']['optype']['type'] == 'select_box') {
                $this->template->assert('optype_list', $form_generator->get_select_box($kvartira_model['data']['optype']));
            } elseif ($kvartira_model['data']['optype']['type'] == 'select_by_query') {
                $this->template->assert('optype_list', $form_generator->get_single_select_box_by_query($kvartira_model['data']['optype']));
            }
        }

        if (isset($kvartira_model['data']['tlocation'])) {
            $this->template->assert('country_list', '');
            $this->template->assert('region_list', '');
            $this->template->assert('city_list', '');
            $this->template->assert('district_list', '');
            $this->template->assert('street_list', '');

            $tdata = $form_generator->compile_tlocation_element($kvartira_model['data']['tlocation']);
            foreach ($tdata->collection as $el) {
                if ($el['name'] == 'country_id') {
                    $this->template->assert('country_list', $el['html']);
                }
                if ($el['name'] == 'region_id') {
                    $this->template->assert('region_list', $el['html']);
                }
                if ($el['name'] == 'city_id') {
                    $this->template->assert('city_list', $el['html']);
                }
                if ($el['name'] == 'district_id') {
                    $this->template->assert('district_list', $el['html']);
                }
                if ($el['name'] == 'street_id') {
                    $this->template->assert('street_list', $el['html']);
                }
            }

            $this->template->assert('scripts', $tdata->scripts);
        }

        if (isset($kvartira_model['data']['price'])) {
            $this->template->assert('price', $kvartira_model['data']['price']['value']);
        }

        $this->template->assert('id', $this->getRequestValue('id'));
    }

    /*
     * Replace search form model elements by custom elements
     */

    function array_overlay($a1, $a2) {
        foreach ($a1 as $k => $v) {
            if ($a2[$k] == "::delete::") {
                unset($a1[$k]);
                continue;
            };
            if (!array_key_exists($k, $a2))
                continue;
            if (is_array($v) && is_array($a2[$k])) {
                $a1[$k] = $this->array_overlay($v, $a2[$k]);
            } else {
                $a1[$k] = $a2[$k];
            }
        }
        return $a1;
    }

}
