<?php

class datacompare extends SiteBill {

    use \system\traits\blade\BladeTrait;

    function __construct()
    {
        parent::__construct();
        $this->add_resource_path(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/resources/views/');
    }

    function ajax(){
        $action = $this->getRequestValue('action');

        switch($action){
            case 'comparelist' : {
                $compared = array();
                $max = intval($this->getRequestValue('count'));
                if($max > 10 || $max == 0){
                    $max = 3;
                }
                $data = array();

                if(isset($_SESSION['compared']) && is_array($_SESSION['compared']) && !empty($_SESSION['compared'])){
                    $compared = $_SESSION['compared'];
                }

                if(!empty($compared)){
                    $Grid_Constructor = $this->_getGridConstructor();
                    $params = array(
                        'id' => $compared,
                        'page_limit' => $max
                    );
                    $objects = $Grid_Constructor->get_sitebill_adv_core($params, false, false, false, false);
                    $params = array(
                        'items' => $objects['data'],
                        'sitebill' => $this
                    );
                    return $this->view('layout.partials._compareblocklisting', $params);
                }
                return '';
            }
            case 'add' : {
                $id = intval($this->getRequestValue('id'));
                if (!isset($_SESSION['compared'])) {
                    $_SESSION['compared'] = array();
                }
                if (!in_array($id, $_SESSION['compared'])) {
                    $_SESSION['compared'][] = $id;
                }
                return json_encode(array('status' => 1, 'count' => count($_SESSION['compared'])));
                break;
            }
            case 'remove' : {
                $id = intval($this->getRequestValue('id'));
                if (isset($_SESSION['compared']) && false !== $key = array_search($id, $_SESSION['compared'])) {
                    unset($_SESSION['compared'][$key]);
                }
                return json_encode(array('status' => 1, 'count' => count($_SESSION['compared'])));
                break;
            }
        }

    }
}