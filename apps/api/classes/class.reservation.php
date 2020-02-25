<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * reservation REST class
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class API_reservation extends API_Common {

    private $reservation_control;

    function __construct() {
        parent::__construct();

        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/profile.php');
        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/profile_using_model.php');
        $this->user_profile = new User_Profile_Model;

        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/reservation/admin/admin.php');
        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/reservation/site/site.php');
        $this->reservation_control = new reservation_site();
    }
    
    public function _rate_create() {
        
        $empty_data = array(
            'data' => array()
        );
        
        $user_id = $this->get_my_user_id();
        
        $ratedata = $this->request->get('data');

        $object_id = intval($this->request->get('object_id'));
        
        if($user_id === false || $object_id == 0){
            return $this->json_string($empty_data);
        }
        
        if(!$this->reservation_control->isObjectOwned($object_id, $user_id)){
            return $this->json_string($empty_data);
        }


        $result = $this->reservation_control->createRate($object_id, $ratedata);
        if ( $this->reservation_control->getError() ) {
            $result = array('status' => 0, 'error' => $this->reservation_control->getError());
        }
        $data = array(
            'data' => $result
        );


        return $this->json_string($data);
    }
    
    public function _rate_edit() {
        
        $empty_data = array(
            'data' => array()
        );

        $user_id = $this->get_my_user_id();
        
        $rate_id = intval($this->request->get('id'));
        
        $ratedata = $this->request->get('data');

        //$rate_id = intval($this->request->get('rate_id'));
        
        if($user_id === false || $rate_id == 0){
            return $this->json_string($empty_data);
        }
        
        if(!$this->reservation_control->isRateOwned($rate_id, $user_id)){
            return $this->json_string($empty_data);
        }

        $result = $this->reservation_control->editRate($rate_id, $ratedata);
        if ( $this->reservation_control->getError() ) {
            $result = array('status' => 0, 'error' => $this->reservation_control->getError());
        }
        $data = array(
            'data' => $result
        );

        return $this->json_string($data);
    }

    public function _rate_delete() {

        $empty_data = array(
            'data' => array()
        );

        $user_id = $this->get_my_user_id();

        $rate_id = intval($this->request->get('object_id'));

        if($user_id === false || $rate_id == 0){
            $response = new API_Response('error', 'user_id or rate_id is empty');
            return $this->json_string($response->get());
        }

        if(!$this->reservation_control->isRateOwned($rate_id, $user_id)){
            $response = new API_Response('error', 'cant access this rate id = '.$rate_id);
            return $this->json_string($response->get());
        }

        $result = $this->reservation_control->deleteRate($rate_id);
        if ( $this->reservation_control->getError() ) {
            $result = array('status' => 0, 'error' => $this->reservation_control->getError());
        }
        $data = array(
            'data' => $result
        );

        return $this->json_string($data);
    }


    public function _calender_data() {
        
        $empty_data = array(
            'data' => array()
        );
        
        $user_id = $this->get_my_user_id();

        $id = intval($this->request->get('id'));
        
        if($user_id === false || $id == 0){
            return $this->json_string($empty_data);
        }
        
        if(!$this->reservation_control->isObjectOwned($id, $user_id)){
            return $this->json_string($empty_data);
        }
        
        $start_date = trim($this->request->get('start'));
        $end_date = trim($this->request->get('end'));

        $data = array(
            'data' => $this->reservation_control->getPeriodInfo($id, $start_date, $end_date)
        );


        return $this->json_string($data);
    }


}
