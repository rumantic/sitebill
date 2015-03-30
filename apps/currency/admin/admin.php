<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * Currencies options and courses admin backend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class currency_admin extends Object_Manager {
	
	private $courses=array();
	
	/**
     * Constructor
     */
    function __construct( $realty_type = false ) {
        $this->SiteBill();
        
        $this->table_name = 'currency';
        $this->action = 'currency';
        $this->primary_key = 'currency_id';
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/currency/admin/currency_model.php');
        $this->data_model_object=new Currency_Model();
		$this->data_model=$this->data_model_object->get_model();
		
        //$this->install();
        $this->loadCourses();
    }
    
    protected function _installAction(){
    	$this->install();
    }
    
    function main () {
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$form_data = $this->data_model;
    	$rs = $this->getTopMenu();
    
    	switch( $this->getRequestValue('do') ){
    		case 'structure' : {
    			$rs = $this->structure_processor();
    			break;
    		}
    
    		case 'edit_done' : {
    			$form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
    			$form_data[$this->table_name]['course']['value']=str_replace(',', '.', $form_data[$this->table_name]['course']['value']);
    			
    			
    			$data_model->forse_auto_add_values($form_data[$this->table_name]);
    			if ( !$this->check_data( $form_data[$this->table_name] ) ) {
    				$rs = $this->get_form($form_data[$this->table_name], 'edit');
    			} else {
    				$this->edit_data($form_data[$this->table_name]);
    				if ( $this->getError() ) {
    					$form_data['data']=$this->removeTemporaryFields($form_data['data'],$remove_this_names);
    					$rs = $this->get_form($form_data[$this->table_name], 'edit');
    				} else {
    					$rs .= $this->grid();
    				}
    			}
    			break;
    		}
    
    		case 'edit' : {
    			if ( $this->getRequestValue('language_id') > 0 and !$this->language->get_version($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $this->getRequestValue('language_id')) ) {
    				$rs = $this->get_form($form_data[$this->table_name], 'new', $this->getRequestValue('language_id'));
    			} else {
    				if ( $this->getRequestValue('language_id') > 0 ) {
    					$form_data[$this->table_name] = $data_model->init_model_data_from_db_language ( $this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name], false, $this->getRequestValue('language_id') );
    				} else {
    					$form_data[$this->table_name] = $data_model->init_model_data_from_db ( $this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name] );
    				}
    				$rs = $this->get_form($form_data[$this->table_name], 'edit');
    			}
    
    			break;
    		}
    		case 'delete' : {
    			$this->delete_data($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key));
    			if ( $this->getError() ) {
    				$rs .= '<div align="center">'.Multilanguage::_('L_ERROR_ON_DELETE').': '.$this->GetErrorMessage().'<br>';
    				$rs .= '<a href="?action='.$this->action.'">ОК</a>';
    				$rs .= '</div>';
    			} else {
    				$rs .= $this->grid();
    			}
    
    
    			break;
    		}
    			
    		case 'new_done' : {
    			$form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
    			$form_data[$this->table_name]['course']['value']=str_replace(',', '.', $form_data[$this->table_name]['course']['value']);
    			$data_model->forse_auto_add_values($form_data[$this->table_name]);
    			if ( !$this->check_data( $form_data[$this->table_name] ) || (1==$this->getConfigValue('filter_double_data') && !$this->checkUniquety($form_data[$this->table_name]))  ) {
    				$form_data['data']=$this->removeTemporaryFields($form_data['data'],$remove_this_names);
    				$rs = $this->get_form($form_data[$this->table_name], 'new');
    				 
    			} else {
    				$new_record_id=$this->add_data($form_data[$this->table_name], $this->getRequestValue('language_id'));
    				if ( $this->getError() ) {
    					$rs = $this->get_form($form_data[$this->table_name], 'new');
    				} else {
    					$rs .= $this->grid();
    				}
    			}
    			break;
    		}
    			
    		case 'new' : {
    			$rs = $this->get_form($form_data[$this->table_name]);
    			break;
    		}
    		case 'mass_delete' : {
    			$id_array=array();
    			$ids=trim($this->getRequestValue('ids'));
    			if($ids!=''){
    				$id_array=explode(',',$ids);
    			}
    			$rs.=$this->mass_delete_data($this->table_name, $this->primary_key, $id_array);
    			break;
    		}
    		default : {
    			$rs .= $this->grid($user_id);
    		}
    	}
    	$rs_new = $this->get_app_title_bar();
    	$rs_new .= $rs;
    	return $rs_new;
    }
    
    private function loadCourses(){
    	$query='SELECT currency_id, course FROM '.DB_PREFIX.'_'.$this->table_name;
		$this->db->exec($query);
		if($this->db->success){
			while($this->db->fetch_assoc()){
				$this->courses[$this->db->row['currency_id']]=$this->db->row['course'];
			}
		}
	}
    
    public function getUEPrice($sum, $curency_id){
    	if(isset($this->courses[$curency_id])){
    		return $sum*$this->courses[$curency_id];
    	}else{
    		return $sum;
    	}
    }
    
    public function getUECoefficient($currency_from,$currency_to){
    	if(isset($this->courses[$currency_from]) && isset($this->courses[$currency_to])){
    		return $this->courses[$currency_from]/$this->courses[$currency_to];
    	}else{
    		return 1;
    	}
    }
    
    public function getCourse($currency){
    	if(isset($this->courses[$currency])){
    		return $this->courses[$currency];
    	}else{
    		return 1;
    	}
    }
   
    
    function grid () {
    	
    	$params=array();
    	$params['action']=$this->action;
    	
    	
    	
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/grid.php');
        $common_grid = new Common_Grid($this);
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/page.php');
        $common_page = new Common_Page();
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/tab.php');
    	$common_tab = new Common_Tab();
		$url='/admin/index.php?action='.$this->action;
		
    	$common_grid->add_grid_item('currency_id');
        $common_grid->add_grid_item('code');
        $common_grid->add_grid_item('name');
        $common_grid->add_grid_item('is_active');
        $common_grid->add_grid_item('sort_order');
        $common_grid->add_grid_item('course');
        
        $common_grid->add_grid_control('edit');
        $common_grid->add_grid_control('delete');
		$common_grid->set_grid_query("SELECT * FROM ".DB_PREFIX."_".$this->table_name." ORDER BY sort_order ASC, code ASC, currency_id ASC");
		$params['page']=$this->getRequestValue('page');
		$params['per_page']=$this->getConfigValue('common_per_page');
        
        $common_grid->setPagerParams($params);
        
        $common_page->setTab($common_tab);
        $common_page->setGrid($common_grid);
        
		$rs .= $common_page->toString();
		return $rs;
    }
    
    function install () {
        $query="CREATE TABLE IF NOT EXISTS `re_currency` (
		  `currency_id` int(11) NOT NULL AUTO_INCREMENT,
		  `code` varchar(3) NOT NULL,
		  `name` varchar(30) NOT NULL,
		  `sort_order` tinyint(4) NOT NULL,
		  `course` varchar(10) NOT NULL,
		  `is_default` tinyint(4) NOT NULL DEFAULT '0',
		  `is_active` tinyint(4) NOT NULL DEFAULT '1',
		  PRIMARY KEY (`currency_id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=".DB_ENCODING." AUTO_INCREMENT=1";

		$this->db->exec($query);
		
		$query = "alter table ".DB_PREFIX."_data add column currency_id int(11) not null default 0";
		$this->db->exec($query);
        
	}
	
	function getTopMenu(){
		$rs.='<a href="'.SITEBILL_MAIN_URL.'/admin/index.php?action='.$this->action.'&do=new" class="btn btn-primary">'.Multilanguage::_('L_ADD_PARAMETER').'</a>';
		return $rs;
	}
	
	function convert($sum, $from_currency, $to_currency){
		$result=$sum;
		$courses=array();
		$koefficient=1;
		$query='SELECT currency_id, course FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE '.$this->primary_key.' IN ('.(int)$from_currency.','.(int)$to_currency.')';
		//echo $query;
		$this->db->exec($query);
		if($this->db->success){
			while($this->db->fetch_assoc()){
				$courses[$this->db->row['currency_id']]=$this->db->row['course'];
			}
		}
		//print_r($courses);
		if(!empty($courses)){
			if((int)$courses[$from_currency]!=0 && (int)$courses[$to_currency]!=0){
				$koefficient=$courses[$from_currency]/$courses[$to_currency];
			}
		}
		return $sum*$koefficient;
	}
    
}