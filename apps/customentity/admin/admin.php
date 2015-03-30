<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
class customentity_admin extends Object_Manager {
    private $entity='';
    private static $custom_entity_holder=false;
    
    function __construct() {
    	
    	
        $this->SiteBill();
        
        $this->entity=$this->getRequestValue('action');
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
    	$ret=array();
    	//var_dump(self::$custom_entity_holder);
    	if(false!==self::$custom_entity_holder){
    		$ret=self::$custom_entity_holder;
    	}else{
    		$DBC=DBC::getInstance();
    		$query='SELECT * FROM '.DB_PREFIX.'_customentity ORDER BY entity_title';
    		$stmt=$DBC->query($query);
    		if($stmt){
    			while($ar=$DBC->fetch($stmt)){
    				$ret[$ar['entity_name']]=$ar;
    				$ret[$ar['entity_name']]['href']=SITEBILL_MAIN_URL.'/admin/?action='.$ar['entity_name'];
    			}
    		}
    		self::$custom_entity_holder=$ret;
    	}
    	
    	
    	return $ret;
    }
    
    protected function _installAction(){
    	$this->install();
    }
    
	function install(){
		$query = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."_customentity` (
		  `entity_name` varchar(255) NOT NULL,
		  `entity_title` varchar(255) NOT NULL,
		  PRIMARY KEY (`entity_name`)
		) ENGINE=MyISAM DEFAULT CHARSET=".DB_ENCODING.";";
		$this->db->exec($query);
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
    		$form_data=$ATH->load_model($table_name, true, true);
    		if(empty($form_data)){
    			$form_data = array();
    			$form_data = $form_region;
    			require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php';
    			require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/admin.php';
    			$TA=new table_admin();
    			$TA->create_table_and_columns($form_data, $table_name);
    			$form_data = array();
    			$form_data=$ATH->load_model($table_name, $ignore_user_group);
    		}
    		$form_data = $ATH->add_ajax($form_data);
    	}else{
    		$form_data = array();
    	}
    	return $form_data;
    }
    
    
    
    
}