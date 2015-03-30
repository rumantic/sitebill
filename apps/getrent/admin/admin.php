<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * getrent admin backend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */

class getrent_admin extends Object_Manager {
    
    /**
     * Constructor
     */
    function __construct( $realty_type = false ) {
        $this->SiteBill();
        Multilanguage::appendAppDictionary('getrent');
        $this->table_name = 'data_get_rent';
        $this->action = 'getrent';
        
        
        $this->primary_key = 'data_get_rent_id';
        
        require_once (SITEBILL_DOCUMENT_ROOT.'/apps/config/admin/admin.php');
        $config_admin = new config_admin();
         
        if ( !$config_admin->check_config_item('apps.getrent.enable') ) {
        	$config_admin->addParamToConfig('apps.getrent.enable','0','Включить приложение Заявки на аренду');
        }
        
        if ( !$config_admin->check_config_item('apps.getrent.title') ) {
        	$config_admin->addParamToConfig('apps.getrent.title','Заявки на аренду','Название приложения');
        }
        
        if ( !$config_admin->check_config_item('apps.getrent.description') ) {
        	$config_admin->addParamToConfig('apps.getrent.description','Пожалуйста, заполните форму','Текст описания');
        }
        
        if ( !$config_admin->check_config_item('apps.getrent.folder_title') ) {
        	$config_admin->addParamToConfig('apps.getrent.folder_title','Заявки на аренду','Заголовок приложения со стороны сайта');
        }
        
        if ( !$config_admin->check_config_item('apps.getrent.meta_title') ) {
        	$config_admin->addParamToConfig('apps.getrent.meta_title','','META-заголовок');
        }
        
        if ( !$config_admin->check_config_item('apps.getrent.meta_keywords') ) {
        	$config_admin->addParamToConfig('apps.getrent.meta_keywords','','META-ключевые слова');
        }
        
        if ( !$config_admin->check_config_item('apps.getrent.meta_description') ) {
        	$config_admin->addParamToConfig('apps.getrent.meta_description','','META-описание');
        }
        //$this->install();
        //require_once(SITEBILL_DOCUMENT_ROOT.'/apps/mailbox/admin/mailbox_model.php');
        //$Object=new Mailbox_Model();
        $this->data_model=$this->get_model();
        
    }
    
    private function get_model () {
    	$form_data = array();
    	$table_name=$this->table_name;
    	if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php') ){
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
    		$ATH=new Admin_Table_Helper();
    		$form_data=$ATH->load_model($table_name);
    		if(empty($form_data)){
    			$form_data = array();
    			$form_data = $this->_get_get_rent_model();
    			require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php';
    			require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/admin.php';
    			$TA=new table_admin();
    			$TA->create_table_and_columns($form_data, $table_name);
    			$form_data = array();
    			$form_data=$ATH->load_model($table_name);
    		}
    	}else{
    		$form_data = $this->_get_get_rent_model();
    	}
    	return $form_data;
    }
    
    private function _get_get_rent_model(){
    	$form_data['data_get_rent']['data_get_rent_id']['name'] = 'data_get_rent_id';
    	$form_data['data_get_rent']['data_get_rent_id']['title'] = 'ID';
    	$form_data['data_get_rent']['data_get_rent_id']['value'] = 0;
    	$form_data['data_get_rent']['data_get_rent_id']['length'] = 40;
    	$form_data['data_get_rent']['data_get_rent_id']['type'] = 'primary_key';
    	$form_data['data_get_rent']['data_get_rent_id']['required'] = 'off';
    	$form_data['data_get_rent']['data_get_rent_id']['unique'] = 'off';
    	
    	$form_data['data_get_rent']['topic_id']['name'] = 'topic_id';
    	$form_data['data_get_rent']['topic_id']['title'] = 'Категория';
    	$form_data['data_get_rent']['topic_id']['value_string'] = '';
    	$form_data['data_get_rent']['topic_id']['value'] = 0;
    	$form_data['data_get_rent']['topic_id']['length'] = 40;
    	$form_data['data_get_rent']['topic_id']['type'] = 'select_box_structure';
    	$form_data['data_get_rent']['topic_id']['required'] = 'off';
    	$form_data['data_get_rent']['topic_id']['unique'] = 'off';
    	
    	$form_data['data_get_rent']['city_id']['name'] = 'city_id';
    	$form_data['data_get_rent']['city_id']['primary_key_name'] = 'city_id';
    	$form_data['data_get_rent']['city_id']['primary_key_table'] = 'city';
    	$form_data['data_get_rent']['city_id']['title'] = 'Населенный пункт';
    	$form_data['data_get_rent']['city_id']['value_string'] = '';
    	$form_data['data_get_rent']['city_id']['value'] = 0;
    	$form_data['data_get_rent']['city_id']['length'] = 40;
    	$form_data['data_get_rent']['city_id']['type'] = 'select_by_query';
    	$form_data['data_get_rent']['city_id']['query'] = 'select * from '.DB_PREFIX.'_city order by name';
    	$form_data['data_get_rent']['city_id']['value_name'] = 'name';
    	$form_data['data_get_rent']['city_id']['title_default'] = 'не указан';
    	$form_data['data_get_rent']['city_id']['value_default'] = 0;
    	$form_data['data_get_rent']['city_id']['required'] = 'off';
    	$form_data['data_get_rent']['city_id']['unique'] = 'off';

    	$form_data['data_get_rent']['district_id']['name'] = 'district_id';
    	$form_data['data_get_rent']['district_id']['primary_key_name'] = 'id';
    	$form_data['data_get_rent']['district_id']['primary_key_table'] = 'district';
    	$form_data['data_get_rent']['district_id']['title'] = Multilanguage::_('L_DISTRICT');
    	$form_data['data_get_rent']['district_id']['value_string'] = '';
    	$form_data['data_get_rent']['district_id']['value'] = 0;
    	$form_data['data_get_rent']['district_id']['length'] = 40;
    	$form_data['data_get_rent']['district_id']['type'] = 'select_by_query';
    	$form_data['data_get_rent']['district_id']['query'] = 'select * from '.DB_PREFIX.'_district order by name';
    	$form_data['data_get_rent']['district_id']['value_name'] = 'name';
    	$form_data['data_get_rent']['district_id']['title_default'] = 'не указан';
    	$form_data['data_get_rent']['district_id']['value_default'] = 0;
    	$form_data['data_get_rent']['district_id']['required'] = 'off';
    	$form_data['data_get_rent']['district_id']['unique'] = 'off';
    	 
    	$form_data['data_get_rent']['name']['name'] = 'name';
    	$form_data['data_get_rent']['name']['title'] = 'Имя';
    	$form_data['data_get_rent']['name']['value'] = '';
    	$form_data['data_get_rent']['name']['length'] = 40;
    	$form_data['data_get_rent']['name']['type'] = 'safe_string';
    	$form_data['data_get_rent']['name']['required'] = 'on';
    	$form_data['data_get_rent']['name']['unique'] = 'off';
    	
    	$form_data['data_get_rent']['phone']['name'] = 'phone';
    	$form_data['data_get_rent']['phone']['title'] = 'Телефон';
    	$form_data['data_get_rent']['phone']['value'] = '';
    	$form_data['data_get_rent']['phone']['length'] = 40;
    	$form_data['data_get_rent']['phone']['type'] = 'safe_string';
    	$form_data['data_get_rent']['phone']['required'] = 'on';
    	$form_data['data_get_rent']['phone']['unique'] = 'off';
    	
    	$form_data['data_get_rent']['email']['name'] = 'email';
    	$form_data['data_get_rent']['email']['title'] = 'Email';
    	$form_data['data_get_rent']['email']['value'] = '';
    	$form_data['data_get_rent']['email']['length'] = 40;
    	$form_data['data_get_rent']['email']['type'] = 'safe_string';
    	$form_data['data_get_rent']['email']['required'] = 'off';
    	$form_data['data_get_rent']['email']['unique'] = 'off';
    	
    	$form_data['data_get_rent']['time_range_id']['name'] = 'time_range_id';
    	$form_data['data_get_rent']['time_range_id']['title'] = 'Снять на';
    	$form_data['data_get_rent']['time_range_id']['value'] = 0;
    	$form_data['data_get_rent']['time_range_id']['length'] = 40;
    	$form_data['data_get_rent']['time_range_id']['type'] = 'select_box';
    	$form_data['data_get_rent']['time_range_id']['select_data'] = array('1' => 'Длительный срок', '2' => 'Короткое время');
    	$form_data['data_get_rent']['time_range_id']['required'] = 'off';
    	$form_data['data_get_rent']['time_range_id']['unique'] = 'off';
    	
    	$form_data['data_get_rent']['room_type_id']['name'] = 'room_type_id';
    	$form_data['data_get_rent']['room_type_id']['title'] = 'Кол.во комнат';
    	$form_data['data_get_rent']['room_type_id']['value'] = 0;
    	$form_data['data_get_rent']['room_type_id']['length'] = 40;
    	$form_data['data_get_rent']['room_type_id']['type'] = 'select_box';
    	$form_data['data_get_rent']['room_type_id']['select_data'] = array('1' => 'Комната на подселение', '2' => 'Гостинка','3' => '1-ком.', '4' => '2-ком.','5' => '3-ком.', '6' => '4-ком.');
    	$form_data['data_get_rent']['room_type_id']['required'] = 'off';
    	$form_data['data_get_rent']['room_type_id']['unique'] = 'off';
    	
    	$form_data['data_get_rent']['baby']['name'] = 'baby';
    	$form_data['data_get_rent']['baby']['title'] = 'Есть дети';
    	$form_data['data_get_rent']['baby']['value'] = 0;
    	$form_data['data_get_rent']['baby']['length'] = 40;
    	$form_data['data_get_rent']['baby']['type'] = 'checkbox';
    	$form_data['data_get_rent']['baby']['required'] = 'off';
    	$form_data['data_get_rent']['baby']['unique'] = 'off';
    	 
    	$form_data['data_get_rent']['pets']['name'] = 'pets';
    	$form_data['data_get_rent']['pets']['title'] = 'Есть домашние животные';
    	$form_data['data_get_rent']['pets']['value'] = 0;
    	$form_data['data_get_rent']['pets']['length'] = 40;
    	$form_data['data_get_rent']['pets']['type'] = 'checkbox';
    	$form_data['data_get_rent']['pets']['required'] = 'off';
    	$form_data['data_get_rent']['pets']['unique'] = 'off';

    	$form_data['data_get_rent']['foreigner']['name'] = 'foreigner';
    	$form_data['data_get_rent']['foreigner']['title'] = 'Иностранец';
    	$form_data['data_get_rent']['foreigner']['value'] = 0;
    	$form_data['data_get_rent']['foreigner']['length'] = 40;
    	$form_data['data_get_rent']['foreigner']['type'] = 'checkbox';
    	$form_data['data_get_rent']['foreigner']['required'] = 'off';
    	$form_data['data_get_rent']['foreigner']['unique'] = 'off';
    	 
    	$form_data['data_get_rent']['more']['name'] = 'more';
    	$form_data['data_get_rent']['more']['title'] = 'Дополнительные пожелания';
    	$form_data['data_get_rent']['more']['value'] = '';
    	$form_data['data_get_rent']['more']['length'] = 40;
    	$form_data['data_get_rent']['more']['type'] = 'textarea';
    	$form_data['data_get_rent']['more']['required'] = 'off';
    	$form_data['data_get_rent']['more']['unique'] = 'off';
    	$form_data['data_get_rent']['more']['rows'] = '10';
    	$form_data['data_get_rent']['more']['cols'] = '40';
    	
    	return $form_data;
    }
    
    
}