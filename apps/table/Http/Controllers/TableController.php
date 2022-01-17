<?php
namespace table\Http\Controllers;


use bridge\Http\Controllers\BaseController;

class TableController extends BaseController
{
    /**
     * @var Permission
     */
    private $permission;

    private $TableAdmin;

    function __construct()
    {
        parent::__construct();

        $this->add_apps_local_and_root_resource_paths('table');
        $this->add_resource_path(SITEBILL_DOCUMENT_ROOT.'/apps/admin3/resources/views/');





        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/admin.php');
        $this->TableAdmin = new \table_admin();

    }

    /**
     * Создание обработчика
     * @param $id
     */
    function handlercreate($id){

    }

    /**
     * Редактирование обработчика
     * @param $id
     */
    function handleredit($id){

    }

    /**
     * Создать свойство в модели
     * @param $id
     */
    function columncreate($id){

    }

    /**
     * Создать новую модель
     */
    function tablenew(){

    }

    /**
     * Обновить физическую структуру модели в базе
     * @param $id
     * @return string
     */
    function tableupdate($id){

    }

    /**
     * Удалить модель
     * @param $id
     * @return string
     */
    function tabledelete($id){

    }

    /**
     * Создать физическую структуру модели в базе
     * @param $id
     * @return string
     */
    function tablecreate($id){

    }

    /**
     * Редактировать структуру модели
     * @param $id
     * @return string
     */
    function tableedit($id){

    }

    function tableview($id){
        $params = array();
        $params['columns'] = $this->getColumns($id);
        return $this->return_pageview('view', $params);
    }

    function tablelist(){
        $params = array();
        $params['tables'] = $this->TableAdmin->loadTablesInfo();
        return $this->view('list', $params);
    }

    function getColumns($tableid){
        $columns = array();

        $DBC = \DBC::getInstance();

        $groups = array();
        $query = 'SELECT name, group_id FROM '.DB_PREFIX.'_group';
        $stmt = $DBC->query($query);
        if($stmt){
            while($ar = $DBC->fetch($stmt)){
                $groups[$ar['group_id']] = $ar['name'];
            }
        }


        $query = "SELECT * FROM " . DB_PREFIX . "_columns WHERE table_id=? ORDER BY sort_order ASC";

        $stmt = $DBC->query($query, array($tableid));
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {

                $ar['_groupnames'] = [];

                $gnames = array();
                if($ar['group_id'] != ''){
                    $gr = explode(',', $ar['group_id']);
                    foreach ($gr as $gid){
                        if($gid != 0){
                            if(isset($groups[$gid])){
                                $ar['_groupnames'][] = $groups[$gid];
                            }else{
                                $ar['_groupnames'][] = '???';
                            }
                        }
                    }
                }

                $columns[$ar['columns_id']] = $ar;
            }
        }
        //print_r($columns);

        return $columns;

    }

}
