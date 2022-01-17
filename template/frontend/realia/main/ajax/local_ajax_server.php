<?php
class Local_Ajax_Server extends Ajax_Server {
	function main () {
		switch ( $this->getRequestValue('action') ) {
			case 'find' : {
				require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/grid/grid_constructor.php';
				$this->_grid_constructor = new Grid_Constructor();
				$grid_constructor=$this->_grid_constructor;
				$params=$this->getRequestValue('params');
				$res = $grid_constructor->get_sitebill_adv_ext_base_ajax( $params );
				global $smarty;
    		
	    		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/realty_grid_ajax.tpl')){
	    			$tpl=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/realty_grid_ajax.tpl';
	    		}else{
	    			$tpl=SITEBILL_DOCUMENT_ROOT.'/apps/userdata/site/template/account_grid.tpl';
	    		}
	    		$smarty->assign('grid_items', $res['data']);
	    		$smarty->assign('my_own', 1);
	    		$grid=$smarty->fetch($tpl);
	    		
	    		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/realty_grid_pager_ajax.tpl')){
	    			$tpl=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/realty_grid_pager_ajax.tpl';
	    		}else{
	    			$tpl=SITEBILL_DOCUMENT_ROOT.'/apps/userdata/site/template/account_pager.tpl';
	    		}
	    		
	    		$smarty->assign('pager_array', $res['paging']);
	    		$pager=$smarty->fetch($tpl);
	    		
	    		return json_encode(array('grid'=>$grid, 'pager'=>$pager, '_total_records'=>$res['_total_records'], 'order'=>$res['order']));
	    		
			}
		}
	}
}