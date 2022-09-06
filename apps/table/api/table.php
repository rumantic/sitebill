<?php


namespace table\api;


use api\aliases\API_common_alias;
use system\lib\system\apps\traits\ContextTrait;
use api\traits\DirectDbTraits;



class table extends \API_Common
{
    use ContextTrait;
    use DirectDbTraits;

    /**
     * @var \table_admin
     */
    private $table_admin;

    function __construct()
    {
        parent::__construct();
        $this->enable_permission_mode();
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php';
        $this->table_admin = new \table_admin();
    }

    function _toggle () {
        $toggled_column_name = $this->request()->get('toggled_column_name');

        try {
            $this->direct_toggle(
                $this->request()->get('model_name'),
                $this->request()->get('primary_key'),
                $this->request()->get('primary_key_value'),
                $this->request()->get('toggled_column_name')
            );
            $response = new \API_Response(
                'success',
                'Статус поля '.$toggled_column_name.' обновлен успешно',
                true
            );
        } catch ( \Exception $e ) {
            $response = new \API_Response(
                'error',
                'Ошибка при обновлении статуса поля '.$toggled_column_name.' '.$e->getMessage(),
                false
            );
        }
        return $this->json_string($response->get());
    }

    function _get_models_list() {
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php';
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/modelmanager_admin.php';
        $SFA = new \modelmanager_admin();
        $response = new \API_Response('success', 'model list loaded', $SFA->loadAllModels());

        return $this->json_string($response->get());
    }

    function _reorder_columns() {
        $this->table_admin->reorder_columns(implode(',',$this->request()->get('params')['ids']));
    }


    function _get_tables_list () {
        $tables_list = $this->table_admin->getOnlyTablesList();
        if ( is_array($tables_list) ) {
            foreach ( $tables_list as $key => $value ) {
                $result[] = array(
                    'id' => $key,
                    'value' => $value
                );
            }
            $response = new \API_Response('success', 'tables list loaded', $result);
        } else {
            $response = new \API_Response('error', 'tables list load failed', null);
        }

        return $this->json_string($response->get());
    }

    function get_columns_array ($table_name) {
        $api_common = new API_common_alias();

        $model_object = $api_common->init_custom_model_object($table_name);
        $tables_list = $this->table_admin->getOnlyTableFields($table_name);
        if ( is_array($tables_list) ) {
            foreach ($tables_list as $key => $value) {
                if ($model_object and $model_object->data_model[$table_name][$key]) {
                    $title = $model_object->data_model[$table_name][$key]['title'];
                } else {
                    $title = '';
                }

                $result[] = array(
                    'id' => $key,
                    'value' => $value,
                    'title' => $title
                );
            }
            return $result;
        }
        return false;
    }

    function _get_columns_list () {
        $params = $this->request()->get('params');
        $table_name = $params['table_name'];

        $result = $this->get_columns_array($table_name);

        if ( $result ) {
            $response = new \API_Response('success', $this->request()->get('params'), $result);
        } else {
            $response = new \API_Response('error', 'tables list load failed', null);
        }

        return $this->json_string($response->get());
    }


    function _get_columns_type_mapper () {
        exit;
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/columns/admin/admin.php';
        $columns_admin = new \columns_admin();

        echo '<pre>';
        print_r($columns_admin->data_model['columns']['type']);
        echo '</pre>';

        $common_fields = [
            'name',
            'title',
            'value',
            'type',
            'required',
            'unique',
            'dbtype',
            'active_in_topic',
            'active_in_topic[]',
            'tab',
            'hint',
            'parameters[name][]',
            'parameters[value][]',
            'uaction'
        ];

        echo '<pre>';
        print_r($common_fields);
        echo '</pre>';



        /*
        var controls = [];
        var common_fields = ['name', 'title', 'value', 'type', 'required', 'unique', 'dbtype', 'active_in_topic', 'active_in_topic[]', 'tab', 'hint', 'parameters[name][]', 'parameters[value][]', 'uaction'];
        if (langs !== undefined) {
            for (var i = 0, l = langs.length; i < l; i++) {
                common_fields.push('title_' + langs[i]);
                common_fields.push('hint_' + langs[i]);
                common_fields.push('tab_' + langs[i]);
                //common_fields.push('hint_'+list[i]);
                //common_fields.push('hint_'+list[i]);
            }
        }

        controls['gadres'] = common_fields;
        controls['select_entity'] = common_fields;
        controls['date'] = common_fields;
        controls['uploads'] = common_fields.concat(['table_name', 'primary_key']);
        controls['docuploads'] = common_fields.concat(['table_name', 'primary_key']);
        controls['captcha'] = common_fields;
        //controls['datetime']=common_fields;
        controls['dtdatetime'] = common_fields;
        controls['dtdate'] = common_fields;
        controls['dttime'] = common_fields;
        controls['primary_key'] = common_fields;
        controls['password'] = common_fields;
        controls['photo'] = common_fields;
        controls['safe_string'] = common_fields;
        controls['hidden'] = common_fields;
        controls['checkbox'] = common_fields;
        controls['structure'] = common_fields.concat(['entity']);
        controls['select_box_structure'] = common_fields.concat(['value_string', 'title_default']);
        if (langs !== undefined) {
            for (var i = 0, l = langs.length; i < l; i++) {
                controls['select_box_structure'].push('title_default_' + langs[i]);
            }
        }
        controls['select_by_query'] = common_fields.concat(['primary_key_name', 'primary_key_table', 'value_string', 'query', 'value_name', 'title_default', 'value_default', 'combo']);
        if (langs !== undefined) {
            for (var i = 0, l = langs.length; i < l; i++) {
                controls['select_by_query'].push('title_default_' + langs[i]);
            }
        }
        controls['select_box'] = common_fields.concat(['select_data']);
        if (langs !== undefined) {
            for (var i = 0, l = langs.length; i < l; i++) {
                controls['select_box'].push('select_data_' + langs[i]);
            }
        }
        controls['grade'] = common_fields.concat(['select_data']);
        if (langs !== undefined) {
            for (var i = 0, l = langs.length; i < l; i++) {
                controls['grade'].push('select_data_' + langs[i]);
            }
        }
        controls['select_by_query_multi'] = common_fields.concat(['primary_key_name', 'primary_key_table', 'value_string', 'query', 'value_name', 'title_default', 'value_default']);
        if (langs !== undefined) {
            for (var i = 0, l = langs.length; i < l; i++) {
                controls['select_by_query_multi'].push('title_default_' + langs[i]);
            }
        }
        controls['auto_add_value'] = common_fields.concat(['value_table', 'value_primary_key', 'value_field', 'assign_to']);
        controls['price'] = common_fields;
        controls['textarea'] = common_fields;
        controls['textarea_editor'] = common_fields;
        controls['uploadify_image'] = common_fields.concat(['primary_key', 'primary_key_value', 'action', 'table_name']);
        controls['mobilephone'] = common_fields;
        controls['geodata'] = common_fields;
        controls['attachment'] = common_fields;
        controls['tlocation'] = common_fields;
        */

    }
}
