<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * Logger admin backend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
if(!defined('NOTICE')){
	define('NOTICE','0');
}
if(!defined('WARNING')){
	define('WARNING','1');
}
if(!defined('ERROR')){
	define('ERROR','2');
}

class logger_admin extends Object_Manager {
	/**
	 * Constructor
	 */
	function __construct() {
		$this->SiteBill();
		//Multilanguage::appendAppDictionary('logger');
		$this->table_name = 'logger';
		$this->action = 'logger';
		
		
		$this->primary_key = 'logger_id';
		
		require_once (SITEBILL_DOCUMENT_ROOT.'/apps/config/admin/admin.php');
		$config_admin = new config_admin();
		 
		if ( !$config_admin->check_config_item('apps.logger.enable') ) {
			$config_admin->addParamToConfig('apps.logger.enable','0','Включить приложение Logger');
		}
		
		if ( !$config_admin->check_config_item('apps.logger.limit') ) {
			$config_admin->addParamToConfig('apps.logger.limit','10000','Максимальное количество записей в таблице лога');
		}
		
		if ( !$config_admin->check_config_item('apps.logger.per_page') ) {
			$config_admin->addParamToConfig('apps.logger.per_page','50','Количество сообщений на страницу');
		}
		
		$this->data_model=$this->get_model();
		
	}
	
	function getTopMenu () {
		$current_apps_name=$this->getRequestValue('apps_name');
		$DBC=DBC::getInstance();
		$query='SELECT DISTINCT apps_name FROM '.DB_PREFIX.'_'.$this->table_name.' ORDER BY apps_name';
		$stmt=$DBC->query($query);
		if($stmt){
			$select='<form action="'.SITEBILL_MAIN_URL.'/admin/index.php" method="get"><select name="apps_name">';
			while ($ar=$DBC->fetch($stmt)){
				$select.='<option'.($current_apps_name==$ar['apps_name'] ? ' selected="selected"' : '').' value="'.$ar['apps_name'].'">'.$ar['apps_name'].'</option>';
			}
			$select.='</select><input type="hidden" name="action" value="logger" /> <input class="btn" type="submit" value="Фильтровать" /></form>';
		}
		return $select;
	}
	
	private function get_model () {
		$form_data = array();
		
		$form_data['logger']['logger_id']['name'] = 'logger_id';
		$form_data['logger']['logger_id']['title'] = 'ID';
		$form_data['logger']['logger_id']['value'] = 0;
		$form_data['logger']['logger_id']['type'] = 'primary_key';
		
		$form_data['logger']['log_time']['name'] = 'log_time';
		$form_data['logger']['log_time']['title'] = 'Дата';
		$form_data['logger']['log_time']['value'] = 0;
		$form_data['logger']['log_time']['type'] = 'dtdatetime';
		
		$form_data['logger']['apps_name']['name'] = 'apps_name';
		$form_data['logger']['apps_name']['title'] = 'Приложение';
		$form_data['logger']['apps_name']['value'] = '';
		$form_data['logger']['apps_name']['type'] = 'safe_string';

		$form_data['logger']['method']['name'] = 'method';
		$form_data['logger']['method']['title'] = 'Метод';
		$form_data['logger']['method']['value'] = '';
		$form_data['logger']['method']['type'] = 'safe_string';

		$form_data['logger']['message']['name'] = 'message';
		$form_data['logger']['message']['title'] = 'Сообщение';
		$form_data['logger']['message']['value'] = '';
		$form_data['logger']['message']['type'] = 'safe_string';

		$form_data['logger']['type']['name'] = 'type';
		$form_data['logger']['type']['title'] = 'Тип ошибки';
		$form_data['logger']['type']['value'] = '';
		$form_data['logger']['type']['type'] = 'safe_string';
		
		return $form_data;
	}
	
	/*function main () {
		if ( $this->getRequestValue('do') == 'install' ) {
			$rs = $this->install();
			return $rs;
		}
		
		if ( !$this->check_table_exist('logger') ) {
			$rs = '<h1>Приложение не установлено. <a href="?action=logger&do=install">Установить</a></h1>';
			return $rs;
		}
		$this->get_app_title_bar();
		$rs = $this->grid();
		return $rs;
	}*/
	
	protected function _installAction(){
		return $this->install();
	}
	
	protected function _defaultAction(){
		if ( !$this->check_table_exist('logger') ) {
			$rs = '<h1>Приложение не установлено. <a href="?action=logger&do=install">Установить</a></h1>';
			return $rs;
		}
		return parent::_defaultAction();
	}
	
	function grid () {
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/grid.php');
		$common_grid = new Common_Grid($this);
	
	
		$common_grid->add_grid_item($this->primary_key);
		$common_grid->add_grid_item('type_id');
		$common_grid->add_grid_item('log_time');
		$common_grid->add_grid_item('message');
		$common_grid->add_grid_item('apps_name');
		$common_grid->add_grid_item('method');
		$common_grid->add_grid_item('type');
	
		$common_grid->setPagerParams(array('action'=>$this->action,'page'=>$this->getRequestValue('page'),'per_page'=>$this->getConfigValue('apps.logger.per_page')));
		if(''!=$this->getRequestValue('apps_name')){
			$common_grid->set_conditions(array('apps_name'=>$this->getRequestValue('apps_name')));
		}
			
		//$common_grid->set_grid_query("select * from ".DB_PREFIX."_".$this->table_name." order by logger_id desc");
		$rs = $common_grid->construct_grid();
		return $rs;
	}
	
	
	private function install () {
		$query = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."_logger` (
          `logger_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `log_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          `apps_name` varchar(255) NOT NULL DEFAULT '',
          `method` varchar(255) NOT NULL DEFAULT '',
		  `message` text,
          `type` int(11) not null default 0,
		  PRIMARY KEY (`logger_id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=".DB_ENCODING." AUTO_INCREMENT=1 ;";
		$this->db->exec($query);
		if ( !$this->db->success ) {
			$rs = $this->db->error.'<br>';
		} else {
			$rs = 'Установка успешна';
		}
		return $rs;
	}
	
	public function clean_log () {
		//echo 'clean_log<br>';
		$max_limit_id = 0;
		$DBC=DBC::getInstance();
		//get max ID
		$query='SELECT MAX(logger_id) as max_id FROM '.DB_PREFIX.'_logger';
		$stmt=$DBC->query($query);
		if($stmt){
			$ar=$DBC->fetch($stmt);
			$max_limit_id = $ar['max_id'] - $this->getConfigValue('apps.logger.limit');
		}
		if ( $max_limit_id > 0 ) {
			$query='DELETE FROM '.DB_PREFIX.'_logger WHERE logger_id < ?';
			$stmt=$DBC->query($query, array($max_limit_id));
		}
	}
	
	
	/**
	 * Write log into the table
	 * @param array $message_array
	 */
	public function write_log ( $message_array = array() ) {
		self::clean_log();
		//echo '<pre>';
		//print_r( $message_array);
		//echo '</pre>';
		$DBC=DBC::getInstance();
		$query='INSERT INTO '.DB_PREFIX.'_logger (`apps_name`, `method`, `message`, `type`) VALUES (?, ?, ?, ?)';
		//echo $query.'<br>';
		$stmt=$DBC->query($query, array($message_array['apps_name'], $message_array['method'], $message_array['message'], $message_array['type']));
		//print_r($stmt);
	}
}
