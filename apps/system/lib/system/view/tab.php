<?php
/**
 * Construct tabs
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class Common_Tab extends SiteBill {
	
	/**
     * Tabs collector
     * @var array
     */
	private $_tabs=array();
	
	public function __construct(){
		$this->SiteBill();
	}
	
	/**
     * Add tab to Tab Collector
     * @param array $params settings hash-array (title|url|oth params)
     * @return void
     */
	public function addTab($params=array()){
		if((is_array($params)) AND (count($params)>0)){
			$this->_tabs[]=array('title'=>$params['title'],'url'=>$params['url'],'current'=>(isset($params['current'])? TRUE : FALSE ));
		}
	}
	
	/**
     * Add styles to tab item
     * Add containers to each tab here
     * @param string $data data to adding styles
     * @param array $params any special parameters
     * @return string
     */	
	private function addStyles($data,$params=array()){
		return $data;
	}
	
	/**
     * Return tabs string in div-block with elements divided by |
     * @return string
     */
	public function getTabs(){
	    global $smarty;
	    
		if(count($this->_tabs)>0){
			$ret='';
			foreach($this->_tabs as $t){
				if($t['current']){
					$ret[]=$this->addStyles($t['title']);
				}else{
					$ret[]=$this->addStyles('<a href="'.$t['url'].'">'.$t['title'].'</a>');
				}
				
			}
			$smarty->assign('tabs', $this->_tabs);
			$rs = $smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/admin/admin/template/tabs.tpl');
			return $rs;
			return '<div>'.implode(' | ',$ret).'</div>';
		}else{
			return '';
		}
	}
}