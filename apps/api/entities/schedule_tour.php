<?php
namespace api\entities;

use system\traits\PermissionsTrait;

/**
 * schedule_tour object
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
class schedule_tour extends \Object_Manager {
    use PermissionsTrait;
    /**
     * Constructor
     */
    function __construct() {
        parent::__construct();
        $this->table_name = 'schedule_tour';
        $this->action = 'schedule_tour';
        $this->app_title = 'Запланированные туры';
        $this->primary_key = 'id';
        $this->create_or_update_table();
        if ( method_exists($this, 'create_custom_entity') ) {
            if ( $this->create_custom_entity($this->app_title) ) {

            }
        }
        $this->enableNobodyAccess();
    }

    function get_default_grid_items () {
        return array($this->primary_key, 'fio', 'email','phone','object_href', 'date');
    }

    protected function _after_add_done_action($form_data) {
        $subject = $_SERVER['SERVER_NAME'] . ': ' . ' Заявка с записью на просмотр';
        $from = $this->getConfigValue('system_email');
        $to = $this->getConfigValue('order_email_acceptor');


        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/view.php');
        $table_view = new \Table_View();
        $body = '<table class="table">';
        $body .= $table_view->compile_view($form_data['schedule_tour']);
        $body .= '</table>';


        $this->sendFirmMail($to, $from, $subject, $body);

        return $form_data;
    }


    function get_model () {
        return array(
            $this->table_name => array(
                'id' => array(
                    'name' => 'id',
                    'title' => 'id',
                    'type' => \system\types\model\Dictionary::PRIMARY_KEY,
                ),
                'fio' => array(
                    'name' => 'fio',
                    'title' => 'ФИО',
                    'value' => '',
                    'type' => \system\types\model\Dictionary::SAFE_STRING,
                    'required' => 'on',
                ),
                'email' => array(
                    'name' => 'email',
                    'title' => 'Email',
                    'value' => '',
                    'type' => \system\types\model\Dictionary::SAFE_STRING,
                    'required' => 'off',
                ),
                'phone' => array(
                    'name' => 'phone',
                    'title' => 'Телефон',
                    'value' => '',
                    'type' => \system\types\model\Dictionary::SAFE_STRING,
                    'required' => 'off',
                ),
                'object_href' => array(
                    'name' => 'object_href',
                    'title' => 'Ссылка на объект',
                    'value' => '',
                    'type' => \system\types\model\Dictionary::SAFE_STRING,
                    'required' => 'off',
                ),
                'date' => array(
                    'name' => 'date',
                    'title' => 'Дата',
                    'value' => '',
                    'type' => \system\types\model\Dictionary::DTDATETIME,
                    'required' => 'off',
                ),
            )
        );
    }
}
