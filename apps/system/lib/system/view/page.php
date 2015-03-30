<?php
/**
 * Construct page
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class Common_Page extends SiteBill {
	
	/**
     * Page collector
     * @var array
     */
	protected $_page=array();
	protected $tabs;
	protected $grid;
	
	public function __construct(){
		$this->SiteBill();
	}
	
	/**
	 * Set tab items
	 * @param Common_Tab $tabs
	 * @return void
	 */
	public function setTab ( Common_Tab $tabs ) {
	    $this->_page['tabs'] = $tabs;
	}
	
	/**
	 * Set grid item
	 * @param Common_Grid $grid
	 * @return void
	 */
	public function setGrid ( Common_Grid $grid ) {
	    $this->_page['grid'] = $grid;
	}
	
	/**
	 * Set tree item
	 * @param string $tree
	 * @return void
	 */
	public function setTree ( $tree ) {
	    $this->_page['tree'] = $tree;
	}
	
	
	public function toString () {
	    global $smarty;
	    if ( is_object($this->_page['tabs']) ) {
	    	$smarty->assign('tabs', $this->_page['tabs']->getTabs());
	    }
	    $smarty->assign('grid', $this->_page['grid']->construct_grid());
	    $smarty->assign('tree', $this->_page['tree']);
	    $rs = $smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/admin/admin/template/page.tpl');
	    return $rs;
	}
	
}