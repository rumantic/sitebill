<?php
/**
 * User data manager
 * @author http://www.sitebill.ru
 */
class User_Shop_Data_Manager extends Shop_Product_Manager {
    /**
     * Constructor
     */
    function __construct() {
        $this->Shop_Product_Manager();
    }
    
    /**
     * Main
     * @param void
     * @return string
     */
    function main () {
		$user_id=$this->getSessionUserId();
		if ( $user_id == '' or $user_id < 1 ) {
		    return 'Доступ запрещен';
		}
		
		
		
    	require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/permission/permission.php';
    	$Permission=new Permission();
    	
    	if(!$Permission->get_access ( $user_id, 'shop', 'add_private_publication' )){
    		return 'У Вас нет доступа к этой функции';
    	}
    	
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    //$form_data = $data_model->get_kvartira_model($this->getConfigValue('ajax_form_in_user'));
	    
	    $form_data=$this->get_product_model();
	    //print_r($form_data);
	    
		$rs = $this->getTopMenu();
		
		$do_prolong=$this->getRequestValue('submit_prolong');
		if($do_prolong!==NULL){
			$ids=array();
			$ids=$this->getRequestValue('checked_items');
			$term=(int)$this->getRequestValue('prolong_term');
			if((count($ids)>0) AND ($term>0)){
				//$rs.='Prolong # '.implode(',',$ids).' on '.$term.'days';
				$this->prolongProducts($ids, $user_id, $term);
			}
			
			if($this->getConfigValue('apps.shoplog.enable') AND (count($ids)>0) AND ($term>0)){
	        	require_once SITEBILL_DOCUMENT_ROOT.'/apps/shoplog/admin/admin.php';
        		$Logger=new shoplog_admin();
        		foreach($ids as $id){
        			$Logger->addLog($id, $user_id, 'edit', $this->table_name);
        		}
        	}
			
			if($_SESSION['attenton_message']!=''){
				$this->template->assert('attenton_message', $_SESSION['attenton_message']);
				unset($_SESSION['attenton_message']);
			}
			$rs .= $this->grid($user_id, $this->getRequestValue('topic_id'));
		}else{
			switch( $this->getRequestValue('do') ){
				case 'unarchive' : {
					$ids=array();
					$ids=$this->getRequestValue('checked_items');
					if(count($ids)>0){
						$this->unarchiveProducts($ids, $user_id);
					}
					
					if($this->getConfigValue('apps.shoplog.enable') AND (count($ids)>0)){
			        	require_once SITEBILL_DOCUMENT_ROOT.'/apps/shoplog/admin/admin.php';
		        		$Logger=new shoplog_admin();
		        		foreach($ids as $id){
		        			$Logger->addLog($id, $user_id, 'edit', $this->table_name);
		        		}
		        	}
					
					$rs .= $this->grid($user_id, $this->getRequestValue('topic_id'));
					break;
				}
				case 'archive' : {
					$ids=array();
					$ids=$this->getRequestValue('checked_items');
					if(count($ids)>0){
						$this->archiveProducts($ids, $user_id);
					}
					
					if($this->getConfigValue('apps.shoplog.enable') AND (count($ids)>0)){
			        	require_once SITEBILL_DOCUMENT_ROOT.'/apps/shoplog/admin/admin.php';
		        		$Logger=new shoplog_admin();
		        		foreach($ids as $id){
		        			$Logger->addLog($id, $user_id, 'edit', $this->table_name);
		        		}
		        	}
					
					$rs .= $this->grid($user_id, $this->getRequestValue('topic_id'));
					break;
				}
				case 'edit_done' : {
				    if ( !$this->check_access_to_data($user_id, $this->getRequestValue('product_id')) ) {
				        return 'Доступ запрещен';
				    }
				    
		            $form_data['shop_product'] = $data_model->init_model_data_from_request($form_data['shop_product']);
		            $form_data['shop_product']['user_id']['value'] = $this->getSessionUserId(); 
				    $form_data['shop_product']['product_add_date']['value'] = time();
		            
					if(!$Permission->get_access ( $user_id, 'shop', 'add_unmoderated_publication' )){
			    		$can_add_unmoderated=FALSE;
			    	}else{
			    		$can_add_unmoderated=TRUE;
			    	}
			    	
			    	if(!$can_add_unmoderated){
			    		$form_data['shop_product']['active']['value'] = 0;
		            	$form_data['shop_product']['active']['type'] = 'hidden';
			    	}else{
			    		unset($form_data['shop_product']['active']);
			    	}
				    
				    //$form_data['shop_product']['active']['value'] = 0;
					
				    
				    //unset($form_data['shop_product']['active']);
		            unset($form_data['shop_product']['view_count']);
		            
		            if ( !$this->check_data( $form_data['shop_product'] ) ) {
				        $rs = $this->get_form($form_data['shop_product'], 'edit');
				    } else {
				        $this->edit_data($form_data['shop_product']);
				        if ( $this->getError() ) {
				            $rs = $this->get_form($form_data['shop_product'], 'edit');
				        } else {
					        if($this->getConfigValue('apps.shoplog.enable')){
				        		require_once SITEBILL_DOCUMENT_ROOT.'/apps/shoplog/admin/admin.php';
					        	$Logger=new shoplog_admin();
					        	$Logger->addLog($form_data[$this->table_name][$this->primary_key]['value'], $user_id, 'edit', $this->table_name);
				        	}
				        	if(!$can_add_unmoderated){
					        	$to=$this->getConfigValue('order_email_acceptor');
					        	/*require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
							    $mailer = new Mailer();*/
						        $body='Клиентом изменено объявление на сайте<br>ID объявления '.$form_data[$this->table_name][$this->primary_key]['value'].'<br>';
						    	$body .= '<a href="http://'.$_SERVER['SERVER_NAME'].'/admin/">Одобрить в админке</a><br>';
						    	$subject = $_SERVER['SERVER_NAME'].': Изменено объявление : требует модерации';
			                    $from = $this->getConfigValue('order_email_acceptor');
			                    
			                   /* if ( $this->getConfigValue('use_smtp') ) {
			                        $mailer->send_smtp($to, $from, $subject, $body, 1);
			                    } else {
			                        $mailer->send_simple($to, $from, $subject, $body, 1);
			                    }*/
			                    $this->sendFirmMail($to, $from, $subject, $body);
			                }
				            $rs .= $this->grid($user_id, $this->getRequestValue('topic_id'));
				        }
				    }
				    break;
				}
			    
				case 'edit' : {
				    if ( !$this->check_access_to_data($user_id, $this->getRequestValue('product_id')) ) {
				        return 'Доступ запрещен';
				    }
				    
	            	if ( $this->getRequestValue('subdo') == 'delete_image' ) {
	            		$this->deleteImage('shop_product', $this->getRequestValue('image_id'));
	            	}
	            	
	            	if ( $this->getRequestValue('subdo') == 'up_image' ) {
	            		$this->reorderImage('shop_product', $this->getRequestValue('image_id'), 'id', $this->getRequestValue('id'),'up');
	            	}
	            	
	            	if ( $this->getRequestValue('subdo') == 'down_image' ) {
	            		$this->reorderImage('shop_product', $this->getRequestValue('image_id'), 'id', $this->getRequestValue('id'), 'down');
	            	}
	            	
					unset($form_data['shop_product']['add_date']);
	            	$form_data['shop_product']['user_id']['value'] = $this->getSessionUserId(); 
	            	$form_data['shop_product']['user_id']['type'] = 'hidden';
	            	unset($form_data['shop_product']['view_count']);
				    
	                $form_data['shop_product'] = $data_model->init_model_data_from_db ( 'shop_product', 'product_id', $this->getRequestValue('product_id'), $form_data['shop_product'] );
	                unset($form_data['shop_product']['active']);
	                $form_data['shop_product']['product_add_date']['type'] = 'hidden';
	                $rs = $this->get_form($form_data['shop_product'], 'edit');
					
				    break;
				}
				case 'delete' : {
				    if ( !$this->check_access_to_data($user_id, $this->getRequestValue('product_id')) ) {
				        return 'Доступ запрещен';
				    }
					if($this->getConfigValue('apps.shoplog.enable')){
			        	require_once SITEBILL_DOCUMENT_ROOT.'/apps/shoplog/admin/admin.php';
		        		$Logger=new shoplog_admin();
		        		$Logger->addLog($this->getRequestValue('product_id'), $user_id, 'delete', $this->table_name);
			        }
			        $this->delete_data('shop_product', 'product_id', $this->getRequestValue('product_id'));
				    
					
		        
			        $rs .= $this->grid($user_id, $this->getRequestValue('topic_id'));
			        
					break;
				}
				
				case 'new_done' : {
	        		
		            $form_data['shop_product'] = $data_model->init_model_data_from_request($form_data['shop_product']);
		            $form_data['shop_product']['user_id']['value'] = $this->getSessionUserId(); 
		            $form_data['shop_product']['user_id']['type'] = 'hidden';
		            $form_data['shop_product']['product_add_date']['type'] = 'hidden';
		            $form_data['shop_product']['product_add_date']['value'] = time();
		            if(!$Permission->get_access ( $user_id, 'shop', 'add_unmoderated_publication' )){
			    		$can_add_unmoderated=FALSE;
			    	}else{
			    		$can_add_unmoderated=TRUE;
			    	}
			    	
			    	//echo '$can_add_unmoderated = '.$can_add_unmoderated.'<br>';
		            
					if(!$can_add_unmoderated){
			    		$form_data['shop_product']['active']['value'] = 0;
		            	$form_data['shop_product']['active']['type'] = 'hidden';
			    	}else{
			    		$form_data['shop_product']['active']['value'] = 1;
		            	$form_data['shop_product']['active']['type'] = 'hidden';
			    	}
			    	
		            
					if ( !$this->check_data( $form_data['shop_product'] ) ) {
				        $rs = $this->get_form($form_data['shop_product'], 'new');
				        
				    } else {
				        $new_record_id=$this->add_data($form_data['shop_product']);
				        if ( $this->getError() ) {
				            $rs = $this->get_form($form_data['shop_product']);
				        } else {
				        	
					        if($this->getConfigValue('apps.shoplog.enable')){
					        	require_once SITEBILL_DOCUMENT_ROOT.'/apps/shoplog/admin/admin.php';
					        	$Logger=new shoplog_admin();
					        	$Logger->addLog($new_record_id, $user_id, 'new', $this->table_name);
				        	}
				        	
				        	$recipients_list=$this->getConfigValue('apps.shop.recipients_list');
				        	$recipients=array();
				        	$recipients=explode(',',$recipients_list);
				        	$recipients[]=$this->getConfigValue('order_email_acceptor');
				        	
				        	/*require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
						    $mailer = new Mailer();*/
					        if(!$can_add_unmoderated){
					    		$body='Клиентом добавлено новое объявление на сайте<br>';
					    		$subj_part = ': требует модерации';
					    		$body .= '<a href="http://'.$_SERVER['SERVER_NAME'].'/admin/">Одобрить в админке</a><br>';
					    	}else{
					    		$body='Сотрудником добавлено новое объявление на сайте<br>';
					    		$subj_part = ': не требует модерации';
					    		$body .= 'Объявление автоматически опубликовано<br>';
					    	}
					    	require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/view.php');
					    	$table_view = new Table_View();
					    	$body .= '<table border="1">';
					    	$body .= $table_view->compile_view($form_data['shop_product']);
					    	$body .= '</table>';
						    //$body='Добавлено новое объявление на сайте';
		                    $subject = $_SERVER['SERVER_NAME'].': Добавлено новое объявление '.$subj_part;
		                    $from = $this->getConfigValue('order_email_acceptor');
		                    
		                    if(count($recipients)>0){
		                    	foreach($recipients as $r){
		                    		$to = trim($r);
				                    /*if ( $this->getConfigValue('use_smtp') ) {
				                        $mailer->send_smtp($to, $from, $subject, $body, 1);
				                    } else {
				                        $mailer->send_simple($to, $from, $subject, $body, 1);
				                    }*/
				                    $this->sendFirmMail($to, $from, $subject, $body);
		                    	}
		                    }
		                    
					        if(!$can_add_unmoderated){
					    		$rs .= 'Ваше объявление отправлено на модерацию';
					    	}else{
					    		$rs .= 'Ваше объявление добавлено';
					    	}
		                    
				            $rs .= $this->grid($user_id, $this->getRequestValue('topic_id'));
				        }
				    }
					break;
				}
				
				case 'new' : {
					$form_data['shop_product']['user_id']['value'] = $this->getSessionUserId(); 
	            	$form_data['shop_product']['user_id']['type'] = 'hidden';
	            	$form_data['shop_product']['active']['value'] = 0;
	            	$form_data['shop_product']['active']['type'] = 'hidden';
					unset($form_data['shop_product']['product_add_date']);
					unset($form_data['shop_product']['view_count']);
				    $rs = $this->get_form($form_data['shop_product']);
					break;
				}
				default : {
				    $rs .= $this->grid($user_id, $this->getRequestValue('topic_id'));
				}
			}
		}
		
		
		
		return $rs;
	}
	
	
	
	/**
	 * Check access to data
	 * @param int $user_id
	 * @param int $data_id
	 * @return boolean
	 */
	function check_access_to_data ( $user_id, $data_id ) {
	    $query = "select product_id from ".DB_PREFIX."_shop_product where user_id=$user_id and product_id=$data_id";
	    $this->db->exec($query);
	    $this->db->fetch_assoc();
	    if ( $this->db->row['product_id'] > 0 ) {
	        return true;
	    }
	    return false;
	}
	
	/**
	 * Return grid
	 * @param int $user_id user id
	 * @param int $current_category_id current category id
	 * @return string
	 */
	function grid ( $user_id, $current_category_id ) {
		$type=$this->getRequestValue('pub_type');
		//echo $type;
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $rs .= '<form method="post" action="'.SITEBILL_MAIN_URL.'/user/account/data/">';
        $rs .= '<table border="0" width="99%">';
        $rs .= '<tr>';
        $rs .= '<td style="vertical-align: top; width: 290px;">';
        //$rs .= $Structure_Manager->get_category_tree_control_shop($current_category_id, $user_id);
		if($type=='unmoderated'){
			$rs .= $Structure_Manager->get_category_tree_control_shop($current_category_id, $user_id,false,array('active'=>'notactive','action=product','pub_type='.$type));
			//$common_page->setTree($Structure_Manager->get_category_tree_control_shop($this->getRequestValue('topic_id'),0,false,array('active'=>'notactive','action=product','type='.$type)));
        }elseif($type=='active'){
        	$rs .= $Structure_Manager->get_category_tree_control_shop($current_category_id, $user_id,false,array('active'=>'1','action=product','pub_type='.$type));
			//$common_page->setTree($Structure_Manager->get_category_tree_control_shop($this->getRequestValue('topic_id'),0,false,array('active'=>'1','action=product','type='.$type)));
		}elseif($type=='archived'){
			$rs .= $Structure_Manager->get_category_tree_control_shop($current_category_id, $user_id,false,array('archived'=>'1','action=product','pub_type='.$type));
		}elseif($type=='notarchived'){
			$rs .= $Structure_Manager->get_category_tree_control_shop($current_category_id, $user_id,false,array('archived'=>'notarchived','action=product','pub_type='.$type));
		}else{
			$rs .= $Structure_Manager->get_category_tree_control_shop($current_category_id, $user_id,false,array('action=product','pub_type='.$type));
        	//$common_page->setTree($Structure_Manager->get_category_tree_control_shop($this->getRequestValue('topic_id'),0,false,array('action=product','type='.$type)));
        }
        $rs .= '</td>';
        $rs .= '<td style="vertical-align: top;">';
        $rs .= $this->get_data_grid($user_id, $current_category_id);
        $rs .= '</td>';
        $rs .= '</tr>';
        
        $rs .= '</table>';
        $rs .= '</form>';
        
        return $rs;
        
	}
	
	/**
	 * Get data grid
	 * @param int $user_id
	 * @return string
	 */
	function get_data_grid ( $user_id, $current_category_id = false) {
	    
        global $smarty;
        require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/grid/grid_constructor.php';
        require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/grid/shop_grid_constructor.php';
        $shop_grid_constructor = new Shop_Grid_Constructor();
        
        $params['topic_id'] = $this->getRequestValue('topic_id');
        $params['order'] = $this->getRequestValue('order');
        $params['page'] = $this->getRequestValue('page');
        $params['asc'] = $this->getRequestValue('asc');
        $params['price'] = $this->getRequestValue('price');
        $params['user_id'] = $user_id;
        $params['admin'] = 1;
        $params['pub_type'] = $this->getRequestValue('pub_type');
        $params['save_topic_id']=TRUE;
        
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $category_structure = $Structure_Manager->loadCategoryStructure();
        
        $res = $shop_grid_constructor->get_shop_grid($shop_grid_constructor->get_sitebill_adv_ext( $params ));
        //echo '<pre>';
        //print_r($res);
        
        
        
        $this->template->assign('grid_items', $res);
        
        $smarty->assign('admin', 1);
        $smarty->assign('pub_type', $params['pub_type']);
        $smarty->assign('topic_id', $params['topic_id']);
        
        $html = $smarty->fetch( "realty_grid.tpl" );
        return $html;
	}
	
	/**
	 * Check data
	 * @param array $form_data
	 * @return boolean
	 */
	function check_data ( $form_data ) {
	    //require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    if ( !$data_model->check_data($form_data) ) {
	        $this->riseError($data_model->GetErrorMessage());
	        return false;
	    }
	    return true;
	}
	
	/**
	 * Get top menu
	 * @param void 
	 * @return string
	 */
	function getTopMenu () {
	    $rs = '<table><tr><td><a href="'.SITEBILL_MAIN_URL.'/account/data/?do=new" class="btn btn-primary">Добавить объявление</a></td></tr></table>';
		return $rs;
	}
   
   
	
	function get_form ( $form_data=array(), $do = 'new' ) {
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_generator.php');
		$form_generator = new Form_Generator();
	    
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/account.php');
	    $account = new Account();
	    $account_value = $account->getAccountValue($this->getSessionUserId());
        
	    $rs .= $this->get_ajax_functions();
		
		$rs .= '<form method="post" action="'.SITEBILL_MAIN_URL.'/account/data/">';
	    $rs .= '<table>';
		if ( $this->getConfigValue('advert_cost') > 0 and ($do == 'new' or $do == 'new_done' ) ) {
		    $rs .= '<tr>';
		    $rs .= '<td colspan="2">';
		    $rs .= '<b>Стоимость размещения одного объявления '.$this->getConfigValue('advert_cost').' руб.</b>';
		    $rs .= '</td>';
		    $rs .= '</tr>';
		    
	        if ( $account_value <  $this->getConfigValue('advert_cost') ) {
		        $rs .= '<tr>';
		        $rs .= '<td colspan="2">';
		        $rs .= 'Ваш баланс '.$account_value.' руб.';
		        $rs .= '<br>';
		        $rs .= '<b>На вашем счету не хватает средств для размещения объявления, <a href="'.SITEBILL_MAIN_URL.'/account/balance/?do=add_bill">пополнить</a></b></td>';
		        $rs .= '</tr>';
		        $rs .= '</table>';
        		return $rs;
	        }
		}
		if ( $this->getError() ) {
		    $rs .= $form_generator->get_error_message_row($this->GetErrorMessage());
		}
		$rs .= $form_generator->compile_form($form_data);
		
		if ( $do == 'new' ) {
		    $rs .= '<input type="hidden" name="do" value="new_done">';
		} else {
		    $rs .= '<input type="hidden" name="do" value="edit_done">';
		    $rs .= '<input type="hidden" name="product_id" value="'.$form_data['product_id']['value'].'">';
		}
		
		$rs .= '<tr>';
		$rs .= '<td></td>';
		$rs .= '<td><input type="submit" name="submit" value="Сохранить"></td>';
		$rs .= '</tr>';
		$rs .= '</table>';
		$rs .= '</form>';
		
		return $rs;
		
	}
	
	function unarchiveProducts($ids,$user_id){
		foreach($ids as $id){
			$query='UPDATE '.DB_PREFIX.'_'.$this->table_name.' SET product_add_date='.time().' WHERE '.$this->primary_key.'='.$id.' AND user_id='.$user_id;
			$this->db->exec($query);
		}
	}
	
	function archiveProducts($ids,$user_id){
		foreach($ids as $id){
			$query='UPDATE '.DB_PREFIX.'_'.$this->table_name.' SET product_add_date='.(time()-365*24*3600).' WHERE '.$this->primary_key.'='.$id.' AND user_id='.$user_id;
			$this->db->exec($query);
		}
	}
	
	function prolongProducts($ids,$user_id,$term){
		
		$query='SELECT publication_limit FROM '.DB_PREFIX.'_user WHERE user_id='.$user_id;
		$this->db->exec($query);
		$this->db->fetch_assoc();
		$pub_limit=$this->db->row['publication_limit'];
		if($term>$pub_limit){
			$term=$pub_limit;
			$message='Максимальный срок на который вы можете продлить объявление составляет '.$pub_limit.' день/дней';
			$_SESSION['attenton_message']=$message;
		}
		foreach($ids as $id){
			$query='UPDATE '.DB_PREFIX.'_'.$this->table_name.' SET product_add_date=product_add_date+'.($term*24*3600).' WHERE '.$this->primary_key.'='.$id.' AND user_id='.$user_id;
			$this->db->exec($query);
		}
	}
	
}
?>
