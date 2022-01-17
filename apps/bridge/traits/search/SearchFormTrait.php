<?php
namespace bridge\traits\search;

trait SearchFormTrait {

    public function getSearchForm(){
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new \Data_Model();
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');
        $form_generator = new \Form_Generator();
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new \Structure_Manager();

        $kvartira_model['data'] = $this->getSearchFormModel();

        $kvartira_model['data'] = $data_model->init_model_data_from_request($kvartira_model['data'], true, true);



        $searchformdata = array();
        $searchformdata['ajax_functions'] = $this->get_ajax_functions();

        $searchform = array();

        if (isset($kvartira_model['data']['topic_id'])) {
            $searchform['topic_id'] = $Structure_Manager->getCategorySelectBoxWithName('topic_id', $kvartira_model['data']['topic_id']['value']);
        }

        if ($this->getConfigValue('country_in_form') && isset($kvartira_model['data']['country_id'])) {
            if ($kvartira_model['data']['country_id']['type'] == 'select_by_query_multiple') {
                $searchform['country_id'] = $form_generator->get_single_select_box_by_query_multiple($kvartira_model['data']['country_id'], $kvartira_model['data']);
            } else {
                $searchform['country_id'] = $form_generator->get_single_select_box_by_query($kvartira_model['data']['country_id'], $kvartira_model['data']);
            }
        }
        if ($this->getConfigValue('region_in_form') && isset($kvartira_model['data']['region_id'])) {
            if ($kvartira_model['data']['region_id']['type'] == 'select_by_query_multiple') {
                $searchform['region_id'] = $form_generator->get_single_select_box_by_query_multiple($kvartira_model['data']['region_id'], $kvartira_model['data']);
            } else {
                $searchform['region_id'] = $form_generator->get_single_select_box_by_query($kvartira_model['data']['region_id'], $kvartira_model['data']);
            }
        }
        if ($this->getConfigValue('district_in_form') && isset($kvartira_model['data']['district_id'])) {
            if ($kvartira_model['data']['district_id']['type'] == 'select_by_query_multiple') {
                $searchform['district_id'] = $form_generator->get_single_select_box_by_query_multiple($kvartira_model['data']['district_id'], $kvartira_model['data']);
            } else {
                $searchform['district_id'] = $form_generator->get_single_select_box_by_query($kvartira_model['data']['district_id'], $kvartira_model['data']);
            }
        }

        if ($this->getConfigValue('city_in_form') && isset($kvartira_model['data']['city_id'])) {
            if ($kvartira_model['data']['city_id']['type'] == 'select_by_query_multiple') {
                $searchform['city_id'] = $form_generator->get_single_select_box_by_query_multiple($kvartira_model['data']['city_id'], $kvartira_model['data']);
            } else {
                $searchform['city_id'] = $form_generator->get_single_select_box_by_query($kvartira_model['data']['city_id'], $kvartira_model['data']);
            }
        }

        if ($this->getConfigValue('metro_in_form') && isset($kvartira_model['data']['metro_id'])) {
            if ($kvartira_model['data']['metro_id']['type'] == 'select_by_query_multiple') {
                $searchform['metro_id'] = $form_generator->get_single_select_box_by_query_multiple($kvartira_model['data']['metro_id'], $kvartira_model['data']);
            } else {
                $searchform['metro_id'] = $form_generator->get_single_select_box_by_query($kvartira_model['data']['metro_id'], $kvartira_model['data']);
            }
        }

        if ($this->getConfigValue('optype_in_form') && isset($kvartira_model['data']['optype'])) {
            if ($kvartira_model['data']['optype']['type'] == 'select_box') {
                $searchform['optype'] = $form_generator->get_select_box($kvartira_model['data']['optype']);
            } elseif ($kvartira_model['data']['optype']['type'] == 'select_by_query') {
                $searchform['optype'] = $form_generator->get_single_select_box_by_query($kvartira_model['data']['optype']);
            }
        }

        if ($this->getConfigValue('street_in_form') && isset($kvartira_model['data']['street_id'])) {
            $searchform['street_id'] = $form_generator->get_single_select_box_by_query($kvartira_model['data']['street_id'], $kvartira_model['data']);
        }

        $searchformdata['elements'] = $searchform;

/*







        if (isset($kvartira_model['data']['price'])) {
            $this->template->assert('price', $kvartira_model['data']['price']['value']);
        }

        $this->template->assert('id', $this->getRequestValue('id'));
*/

        return $searchformdata;
    }

    function getSearchFormModel() {
        $data_model = new \Data_Model();
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
}
