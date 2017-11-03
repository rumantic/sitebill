<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
class customentity_admin extends Object_Manager {
    private $entity='';
    private static $custom_entity_holder=false;
    
    function __construct() {
    	$this->SiteBill();
        $this->entity=$this->getRequestValue('action');
        $ent=$this->getEntityList();
        if(isset($ent[$this->entity])){
        	Multilanguage::appendAppDictionary('entity');
        	$model=$this->initEntityModel($this->entity);
        	$this->data_model=$model;
        	$this->table_name = $this->entity;
        	$this->action = $this->entity;
        	$this->primary_key = $this->getEntityPKName($model[$this->entity]);
        }
	}
    
    /*
     * Установка entity вручную, для внеших вызовов из других классов
     * @param string $entity - название модели
     * @return void
     */
    function custom_construct ( $entity ) {
        $this->entity = $entity;
        Multilanguage::appendAppDictionary('entity');
        if($this->entity!='' && $this->entity!='customentity'){
	        $model=$this->initEntityModel($this->entity);
	        $this->data_model=$model;
	        //echo $this->getEntityPKName($model[$this->entity]);
	        $this->table_name = $this->entity;
	        $this->action = $this->entity;
	        $this->primary_key = $this->getEntityPKName($model[$this->entity]);
        }
    }
    
       
    function main(){
    	if($this->entity=='customentity' && 'install'==$this->getRequestValue('do')){
    		$this->install();
    	}elseif($this->entity=='customentity'){
    		
    	}else{
    		return parent::main();
    	}
    }
    
    public static function checkEntity($entity){
    	require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
    	$ATH=new Admin_Table_Helper();
    	if($ATH->check_table_exist($entity)){
    		return true;
    	}
    	return false;
    }
    
    public static function getEntityList(){
    	require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/permission/permission.php');
    	$permission = new Permission();
    	
    	 
    	$ret=array();
    	//var_dump(self::$custom_entity_holder);
    	if(false!==self::$custom_entity_holder){
    		$ret=self::$custom_entity_holder;
    	}else{
    		//echo 'one time<br>';
    		$DBC=DBC::getInstance();
    		$query='SELECT * FROM '.DB_PREFIX.'_customentity ORDER BY entity_title';
    		$stmt=$DBC->query($query);
    		if($stmt){
    			while($ar=$DBC->fetch($stmt)){
   					$ret[$ar['entity_name']]=$ar;
   					$ret[$ar['entity_name']]['href']=SITEBILL_MAIN_URL.'/admin/?action='.$ar['entity_name'];
    			}
    		}
    		if ( !empty($ret) ) {
    			foreach ( $ret as $entity_name => $entity_info ) {
    				$permission->add_component($entity_name, $entity_info['entity_title']);
    				$permission->add_permission($entity_name, 'access');
    				if ( !$permission->get_access($_SESSION['user_id_value'], $entity_name, 'access') and $permission->getConfigValue('check_permissions')) {
    					unset($ret[$entity_name]);
    				}
    			}
    		}
    		self::$custom_entity_holder=$ret;
    	}
    	/*
    	echo '<pre>';
    	print_r($ret);
    	echo '</pre>';
    	*/
    	
    	
    	return $ret;
    }
    
    
	function install(){
		$query = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."_customentity` (
		  `entity_name` varchar(255) NOT NULL,
		  `entity_title` varchar(255) NOT NULL,
		  PRIMARY KEY (`entity_name`)
		) ENGINE=MyISAM DEFAULT CHARSET=".DB_ENCODING.";";
		$DBC=DBC::getInstance();
		$success=false;
    	$stmt=$DBC->query($query, array(), $rows, $success);
        if(!$success){
        	$rs = Multilanguage::_('L_APPLICATION_INSTALLED_ERROR');
        }else{
        	$rs = Multilanguage::_('L_APPLICATION_INSTALLED');;
        }
        return $rs;
	}
	
    
    private function getEntityPKName($model_elements){
    	foreach ($model_elements as $model_element){
    		if($model_element['type']=='primary_key'){
    			return $model_element['name'];
    		}
    	}
    	return false;
    }
    
    
   
    private function initEntityModel($table_name){
    	if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php') ){
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
    		$ATH=new Admin_Table_Helper();
    		$form_data=$ATH->load_model($table_name);
    		if(empty($form_data)){
    			$form_data = array();
    			$form_data = $form_region;
    			require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php';
    			require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/admin.php';
    			$TA=new table_admin();
    			$TA->create_table_and_columns($form_data, $table_name);
    			$form_data = array();
    			$form_data=$ATH->load_model($table_name);
    		}
    		$form_data = $ATH->add_ajax($form_data);
    	}else{
    		$form_data = array();
    	}
    	return $form_data;
    }
}