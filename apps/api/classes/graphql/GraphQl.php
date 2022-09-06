<?php
namespace api\classes\graphql;
require_once(SITEBILL_DOCUMENT_ROOT . '/apps/api/classes/class.common.php');
require_once(SITEBILL_DOCUMENT_ROOT . '/apps/api/classes/class.controller.php');
require_once(SITEBILL_DOCUMENT_ROOT . '/apps/api/classes/class.model.php');


class GraphQl extends \API_model {
    public function update($model_name, $key_value, $ql_items, $user_id) {
        $only_ql = true;

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new \Data_Model();

        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php' );
        $permission = new \Permission();
        if (!$permission->get_access($user_id, $model_name, 'access')) {
            $response = new \API_Response('error', _e('Доступ запрещен'));
            return $this->json_string($response->get());
        }



        //$this->writeLog('key_value = '.$key_value);
        //$this->writeArrayLog($ql_items);



        $model_object = $this->init_custom_model_object($model_name);

        if (count($ql_items) > 0) {
            $this->update_native_request_params($ql_items, $model_object, false, false);
        }

        $primary_key = $model_object->primary_key;
        $this->setRequestValue($primary_key, $key_value);

        if ((int) $key_value == 0) {
            //Предварительно проверим данные, чтобы не создавать заведомо неправильную запись
            $model_data = $model_object->data_model[$model_name];
            $model_data = $data_model->init_model_data_from_request($model_data);
            if (!$model_object->check_data($model_data)) {
                $response = new \API_Response('error', $model_object->GetErrorMessage());
                return $this->json_string($response->get());
            }

            //$this->writeArrayLog($model_data['price']);
            //$this->writeLog('key value 0 = '.$key_value);


            $this->_new_empty_record();
            if ($this->getError()) {
                $response = new \API_Response('error', $this->GetErrorMessage());
                return $this->json_string($response->get());
            }
            $key_value = $this->get_record_id();
            $model_data[$primary_key]['value'] = $key_value;
            $this->setRequestValue($primary_key, $key_value);
        } else {
            //$this->writeLog('key value not 0 = '.$key_value);

            $model_data = $model_object->load_by_id($key_value);

            $model_data = $data_model->init_model_data_from_request($model_data);
            $model_data[$primary_key]['value'] = $key_value;
        }
        //$this->writeLog('key_value after = '.$key_value);

        $this->setRequestValue('do', 'edit_done');

        //$this->writeLog(var_export($model_data, true));
        if ($model_object->getError()) {
            $response = new \API_Response('error', $model_object->GetErrorMessage());
            return $this->json_string($response->get());
        }
        if (!$model_data) {
            $response = new \API_Response('error', 'record not found');
            return $this->json_string($response->get());
        }

        if (count($ql_items) > 0) {
            //$this->writeArrayLog($ql_items);
            //$this->writeArrayLog($_POST);
            //$this->writeArrayLog($model_data['price']);
            //$this->writeLog('after first init');
            //$this->writeArrayLog($model_data['price']);
            //$this->writeLog('second init');

            foreach ($ql_items as $key => $value) {
                //$model_data[$key]['value'] = $value;
                if ($only_ql and $key != $primary_key) {
                    $new_model[$model_name][$key] = $model_object->data_model[$model_name][$key];
                }
            }
            if ($only_ql) {
                $new_model[$model_name][$primary_key] = $model_object->data_model[$model_name][$primary_key];
                $model_object->data_model = $new_model;
                foreach ($model_data as $key => $item) {
                    if (!isset($new_model[$model_name][$key]) and $key != $primary_key) {
                        unset($model_data[$key]);
                    }
                }
            }
        }

        $model_data = $model_object->_before_check_action($model_data, 'edit');
        //$this->writeArrayLog($ql_items);
        $this->writeArrayLog($model_data);
        //$this->writeArrayLog($this->request->dump(), true);
        //$this->writeArrayLog($_REQUEST, true);

        if (!$model_object->check_data($model_data)) {
            $response = new \API_Response('error', $model_object->GetErrorMessage());
        } else {
            $model_data = $model_object->_before_edit_done_action($model_data);
            $model_object->edit_data($model_data, 0, $key_value);

            if ($model_object->getError()) {
                $response = new \API_Response('error', $model_object->GetErrorMessage());
            } else {
                if ($this->getConfigValue('apps.realtylog.enable')) {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylog/admin/admin.php';
                    $Logger = new \realtylog_admin();
                    $Logger->addLog($model_data[$primary_key]['value'], $user_id, 'edit', 'data');
                }
                if ($this->getConfigValue('apps.realtylogv2.enable')) {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylogv2/admin/admin.php';
                    $Logger = new \realtylogv2_admin();
                    $Logger->addLog($model_data[$primary_key]['value'], $user_id, 'edit', 'data', 'id');
                }


                $response = new \API_Response('success', 'edit ql complete');
            }
        }

        return $this->json_string($response->get());
    }
}
