<?php
namespace api\entities;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * files_queue object
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
class files_queue extends \Object_Manager {
    /**
     * Constructor
     */
    function __construct() {
        $this->SiteBill();
        $this->table_name = 'files_queue';
        $this->action = 'files_queue';
        $this->primary_key = 'id';

        $this->data_model = $this->get_model();
        if ( !$this->check_table_exist(DB_PREFIX.'_'.$this->table_name) ) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php';
            $TA = new \table_admin();
            $TA->create_table_and_columns($this->data_model, $this->table_name);
            $TA->helper->create_table_from_model($this->table_name, $this->data_model);
        }
        $this->cleanUp();
    }

    /**
     * Удаляем старые файлы из очереди
     * @return void
     */
    function cleanUp () {
        $result = Capsule::table($this->table_name)
            ->selectRaw(
                'id'
            )
            ->where('created_at', '<', date('Y-m-d H:i:s', strtotime("-3 day", time())))
            ->get();
        if ( $result ) {
            foreach ($result as $item) {
                $ids[] = $item->id;
            }
            if ( $ids ) {
                $this->mass_delete_data($this->table_name, $this->primary_key, $ids);
            }
        }
    }


    function get_model () {
        return array(
            $this->table_name => array(
                'id' => array(
                    'name' => 'id',
                    'title' => 'id',
                    'type' => \system\types\model\Dictionary::PRIMARY_KEY,
                ),
                'files' => array(
                    'name' => 'files',
                    'title' => 'files',
                    'value' => '',
                    'type' => \system\types\model\Dictionary::DOCUPLOADS,
                    'required' => 'on',
                ),
                'created_at' => array(
                    'name' => 'created_at',
                    'title' => 'created_at',
                    'value' => 'now',
                    'type' => \system\types\model\Dictionary::DTDATETIME,
                    'required' => 'off',
                ),
            ),
        );
    }
}
