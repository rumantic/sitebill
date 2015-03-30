<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * News fronend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class news_site extends news_admin {
	
	function frontend () {
		
		if ( !$this->getConfigValue('apps.news.enable') ) {
			return false;
		}
		
		if(''!=$this->getConfigValue('apps.news.alias')){
			$app_alias=$this->getConfigValue('apps.news.alias');
		}else{
			$app_alias='news';
		}
		
		if(''!=$this->getConfigValue('apps.news.item_alias')){
			$app_item_alias=$this->getConfigValue('apps.news.item_alias');
		}else{
			$app_item_alias='news';
		}
		
		$REQUESTURIPATH=Sitebill::getClearRequestURI();
		
		if($REQUESTURIPATH=='account_news'){
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/news/admin/user_admin.php';
			$AUN=new user_news_admin();
			$this->template->assert('main', $AUN->main());
			return true;
		}
		
		$this->initNewsModel();
		//$query='';
    	
    		
    		if(preg_match('/^'.$app_item_alias.'(\d+).html$/', $REQUESTURIPATH, $matches)){
    			
    			$breadcrumbs=array();
    			$breadcrumbs[]='<a href="'.(SITEBILL_MAIN_URL!='' ? SITEBILL_MAIN_URL : '').'/">'.Multilanguage::_('L_HOME').'</a>';
    			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/tab.php');
    			 
    			if(1==$this->getConfigValue('apps.news.use_news_topics')){
    				$this->template->assert('news_topics', $this->getNewsTopicsList());
    			}else{
    				$this->template->assert('news_topics', array());
    			}
    			 
    			$this->template->assert('meta_title', $this->getConfigValue('apps.news.meta_title'));
    			$this->template->assert('meta_title', $this->getConfigValue('apps.news.meta_desription'));
    			$this->template->assert('meta_title', $this->getConfigValue('apps.news.meta_keywords'));
    			
    			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/news/admin/news_model.php');
				$Object=new News_Model();
				//$model=$Object->get_model();
				$model=$this->data_model;
				$news=$Object->init_model_data_from_db($this->table_name, $this->primary_key, $matches[1], $model[$this->table_name], TRUE);
    			
				$hasUploadify=false;
				$uploads=false;
				foreach ($model[$this->table_name] as $mitem){
					if($mitem['type']=='uploadify_image'){
						$hasUploadify=true;
						continue;
					}
				}
				if(!$hasUploadify){
					foreach ($this->data_model[$this->table_name] as $mitem){
						if($mitem['type']=='uploads'){
							$uploads=$mitem['name'];
							continue;
						}
					}
				}
				
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    		$data_model = new Data_Model();
	    		if($hasUploadify){
	    			$image_array = $data_model->get_image_array ( 'news', 'news', 'news_id', $news['news_id']['value'] );
	    			if ( count($image_array) > 0 ) {
	    				$news['prev_img']=$image_array[0]['img_preview'];
	    				$news['normal_img']=$image_array[0]['img_normal'];
	    				$news['img'] = $image_array;
	    			}
	    		}elseif($uploads!==false && is_array($news[$uploads]['value'])){
	    			$news['prev_img']=SITEBILL_MAIN_URL.'/img/data/'.$news[$uploads]['value'][0]['preview'];
	    			$news['normal_img']=SITEBILL_MAIN_URL.'/img/data/'.$news[$uploads]['value'][0]['normal'];
	    			$news['img'] = $news[$uploads]['value'];
	    		}
				
	            if ( preg_match('/\./', $news['date']['value']) ) {
	            	$news['date']['value_string']=$news['date']['value'];
	            } else {
	            	$news['date']['value_string']=date('d.m.Y',$news['date']['value']);
	            }
	            if ( $this->getConfigValue('apps.news.folder_title') != '' ) {
	            	$breadcrumbs[]='<a href="'.(SITEBILL_MAIN_URL!='' ? SITEBILL_MAIN_URL : '').'/'.$app_alias.'/">'.$this->getConfigValue('apps.news.folder_title').'</a>';
	            } else {
	            	$breadcrumbs[]='<a href="'.(SITEBILL_MAIN_URL!='' ? SITEBILL_MAIN_URL : '').'/'.$app_alias.'/">'.Multilanguage::_('PAGE_TITLE','news').'</a>';
	            }
				$breadcrumbs[]='<a href="'.(SITEBILL_MAIN_URL!='' ? SITEBILL_MAIN_URL : '').'/'.$app_item_alias.$news['news_id']['value'].'.html">'.$news['title']['value'].'</a>';
				$this->template->assert('breadcrumbs', $this->get_breadcrumbs($breadcrumbs));
				$this->template->assert('title', $news['title']['value']);
				if(isset($news['meta_title']['value']) && $news['meta_title']['value']!=''){
					$this->template->assert('meta_title', $news['meta_title']['value']);
				}
				if(isset($news['meta_description']['value']) && $news['meta_description']['value']!=''){
					$this->template->assert('meta_description', $news['meta_description']['value']);
				}
				if(isset($news['meta_keywords']['value']) && $news['meta_keywords']['value']!=''){
					$this->template->assert('meta_keywords', $news['meta_keywords']['value']);
				}
				$this->template->assert('news', $news);
				if(1==(int)$this->getConfigValue('apps.news.append_more_news_view')){
					$this->template->assert('more_news', $this->get_more_news($news['news_id']['value']));
				}else{
					$this->template->assert('more_news', array());
				}
				
				$this->set_apps_template('news', $this->getConfigValue('theme'), 'main_file_tpl', 'news_view.tpl');
    			return true;
    		}elseif(/*1==$this->getConfigValue('apps.news.use_news_topics') && */preg_match('/'.$app_alias.'\/(.*)[\/]?/', $REQUESTURIPATH, $matches)){
    			
    			$breadcrumbs=array();
    			$breadcrumbs[]='<a href="'.(SITEBILL_MAIN_URL!='' ? SITEBILL_MAIN_URL : '').'/">'.Multilanguage::_('L_HOME').'</a>';
    			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/tab.php');
    				
    			if(1==$this->getConfigValue('apps.news.use_news_topics')){
    				$this->template->assert('news_topics', $this->getNewsTopicsList());
    			}else{
    				$this->template->assert('news_topics', array());
    			}
    				
    			$this->template->assert('meta_title', $this->getConfigValue('apps.news.meta_title'));
    			$this->template->assert('meta_title', $this->getConfigValue('apps.news.meta_desription'));
    			$this->template->assert('meta_title', $this->getConfigValue('apps.news.meta_keywords'));
    			
    			if(1==$this->getConfigValue('apps.news.use_news_topics')){
    				$news_topic=$this->detectNewsTopic($matches[1]);
    			}else{
    				$news_topic=false;
    			}
    			
    			if(!$news_topic){
    				$news_item=$this->detectNews($matches[1]);
    			}else{
    				$news_item=false;
    			}
    			
    			if($news_topic){
    				
    				
    				
    				if ( $this->getConfigValue('apps.news.folder_title') != '' ) {
    					$breadcrumbs[]='<a href="'.(SITEBILL_MAIN_URL!='' ? SITEBILL_MAIN_URL : '').'/'.$app_alias.'/">'.$this->getConfigValue('apps.news.folder_title').'</a>';
    				} else {
    					$breadcrumbs[]='<a href="'.(SITEBILL_MAIN_URL!='' ? SITEBILL_MAIN_URL : '').'/'.$app_alias.'/">'.Multilanguage::_('PAGE_TITLE','news').'</a>';
    				}
    				$breadcrumbs[]=$news_topic['name'];
    				$page=(int)$this->getRequestValue('page');
    				$per_page=$this->getConfigValue('apps.news.front.per_page');
    				$this->template->assert('breadcrumbs', $this->get_breadcrumbs($breadcrumbs));
    				require_once SITEBILL_DOCUMENT_ROOT.'/apps/news/site/news_grid_constructor.php';
    				$NGC=new News_Grid_Constructor();
    				$news=$NGC->get_sitebill_adv_ext(array('page'=>$page, 'per_page'=>$per_page, 'news_topic_id'=>$news_topic['id']));
    			}elseif($news_item){
    				
    				
    				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/news/admin/news_model.php');
    				$Object=new News_Model();
    				$model=$this->data_model;
    				
    				$hasUploadify=false;
    				$uploads=false;
    				foreach ($this->data_model[$this->table_name] as $mitem){
    					if($mitem['type']=='uploadify_image'){
    						$hasUploadify=true;
    						continue;
    					}
    				}
    				if(!$hasUploadify){
    					foreach ($this->data_model[$this->table_name] as $mitem){
    						if($mitem['type']=='uploads'){
    							$uploads=$mitem['name'];
    							continue;
    						}
    					}
    				}
    				
    				$news=$Object->init_model_data_from_db($this->table_name, $this->primary_key, $news_item['news_id'], $model[$this->table_name], TRUE);
    				if($hasUploadify){
    					require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    					$data_model = new Data_Model();
    					$image_array = $data_model->get_image_array ( 'news', 'news', 'news_id', $news['news_id']['value'] );
    					if ( count($image_array) > 0 ) {
    						$news['prev_img']=$image_array[0]['img_preview'];
    						$news['normal_img']=$image_array[0]['img_normal'];
    						$news['img'] = $image_array;
    					}
    				}elseif($uploads!==false && is_array($news[$uploads]['value']) && count($news[$uploads]['value'])>0){
    					$news['prev_img']=SITEBILL_MAIN_URL.'/img/data/'.$news[$uploads]['value'][0]['preview'];
    					$news['normal_img']=SITEBILL_MAIN_URL.'/img/data/'.$news[$uploads]['value'][0]['normal'];
    					$news['img'] = $news[$uploads]['value'];
    				}
    				
    				
    				if ( preg_match('/\./', $news['date']['value']) ) {
    					$news['date']['value_string']=$news['date']['value'];
    				} else {
    					$news['date']['value_string']=date('d.m.Y',$news['date']['value']);
    				}
    				if ( $this->getConfigValue('apps.news.folder_title') != '' ) {
    					$breadcrumbs[]='<a href="'.(SITEBILL_MAIN_URL!='' ? SITEBILL_MAIN_URL : '').'/'.$app_alias.'/">'.$this->getConfigValue('apps.news.folder_title').'</a>';
    				} else {
    					$breadcrumbs[]='<a href="'.(SITEBILL_MAIN_URL!='' ? SITEBILL_MAIN_URL : '').'/'.$app_alias.'/">'.Multilanguage::_('PAGE_TITLE','news').'</a>';
    				}
    				$breadcrumbs[]=/*'<a href="'.(SITEBILL_MAIN_URL!='' ? SITEBILL_MAIN_URL : '').'/'.$app_item_alias.$news['news_id']['value'].'.html">'.*/$news['title']['value']/*.'</a>'*/;
    				$this->template->assert('breadcrumbs', $this->get_breadcrumbs($breadcrumbs));
    				$this->template->assert('title', $news['title']['value']);
    				if(isset($news['meta_title']['value']) && $news['meta_title']['value']!=''){
    					$this->template->assert('meta_title', $news['meta_title']['value']);
    				}
    				if(isset($news['meta_description']['value']) && $news['meta_description']['value']!=''){
    					$this->template->assert('meta_description', $news['meta_description']['value']);
    				}
    				if(isset($news['meta_keywords']['value']) && $news['meta_keywords']['value']!=''){
    					$this->template->assert('meta_keywords', $news['meta_keywords']['value']);
    				}
    				$this->template->assert('news', $news);
    				if(1==(int)$this->getConfigValue('apps.news.append_more_news_view')){
    					$this->template->assert('more_news', $this->get_more_news($news['news_id']['value']));
    				}else{
    					$this->template->assert('more_news', array());
    				}
    				
    				$this->set_apps_template('news', $this->getConfigValue('theme'), 'main_file_tpl', 'news_view.tpl');
    				return true;
    			}else{
    				
    				
    				if ( $this->getConfigValue('apps.news.folder_title') != '' ) {
    					$breadcrumbs[]='<a href="'.(SITEBILL_MAIN_URL!='' ? SITEBILL_MAIN_URL : '').'/'.$app_alias.'/">'.$this->getConfigValue('apps.news.folder_title').'</a>';
    				} else {
    					$breadcrumbs[]='<a href="'.(SITEBILL_MAIN_URL!='' ? SITEBILL_MAIN_URL : '').'/'.$app_alias.'/">'.Multilanguage::_('PAGE_TITLE','news').'</a>';
    				}
    				$page=(int)$this->getRequestValue('page');
    				
    				$per_page=$this->getConfigValue('apps.news.front.per_page');
    				$this->template->assert('breadcrumbs', $this->get_breadcrumbs($breadcrumbs));
    			
    				
    			
    				require_once SITEBILL_DOCUMENT_ROOT.'/apps/news/site/news_grid_constructor.php';
    				$NGC=new News_Grid_Constructor();
    				$news=$NGC->get_sitebill_adv_ext(array('page'=>$page, 'per_page'=>$per_page));
    			}
    			
    			/*if(1==$this->getConfigValue('apps.news.use_news_topics')){
    				$this->template->assert('news_topics', $this->getNewsTopicsList());
    			}else{
    				$this->template->assert('news_topics', array());
    			}*/
    			
    			if(''!=$this->getConfigValue('apps.news.meta_title')){
    				$this->template->assert('meta_title', $this->getConfigValue('apps.news.meta_title'));
    			}elseif(''!=$this->getConfigValue('apps.news.app_title')){
    				$this->template->assert('title', $this->getConfigValue('apps.news.app_title'));
    			}else{
    				$this->template->assert('title', Multilanguage::_('PAGE_TITLE','news'));
    			}
    			
    			$this->template->assert('news', $news);
    			 
    			$this->set_apps_template('news', $this->getConfigValue('theme'), 'main_file_tpl', 'news_grid.tpl');
    			return true;
    		}elseif(preg_match('/^'.$app_alias.'$/', $REQUESTURIPATH)){
    			
    			$breadcrumbs=array();
    			$breadcrumbs[]='<a href="'.(SITEBILL_MAIN_URL!='' ? SITEBILL_MAIN_URL : '').'/">'.Multilanguage::_('L_HOME').'</a>';
    			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/tab.php');
    				
    			if(1==$this->getConfigValue('apps.news.use_news_topics')){
    				$this->template->assert('news_topics', $this->getNewsTopicsList());
    			}else{
    				$this->template->assert('news_topics', array());
    			}
    				
    			$this->template->assert('meta_title', $this->getConfigValue('apps.news.meta_title'));
    			$this->template->assert('meta_title', $this->getConfigValue('apps.news.meta_desription'));
    			$this->template->assert('meta_title', $this->getConfigValue('apps.news.meta_keywords'));
    			
    			
    			if ( $this->getConfigValue('apps.news.folder_title') != '' ) {
    				$breadcrumbs[]='<a href="'.(SITEBILL_MAIN_URL!='' ? SITEBILL_MAIN_URL : '').'/'.$app_alias.'/">'.$this->getConfigValue('apps.news.folder_title').'</a>';
    			} else {
    				$breadcrumbs[]='<a href="'.(SITEBILL_MAIN_URL!='' ? SITEBILL_MAIN_URL : '').'/'.$app_alias.'/">'.Multilanguage::_('PAGE_TITLE','news').'</a>';
    			}
    			$page=(int)$this->getRequestValue('page');
    			$per_page=$this->getConfigValue('apps.news.front.per_page');
    			$this->template->assert('breadcrumbs', $this->get_breadcrumbs($breadcrumbs));
    			require_once SITEBILL_DOCUMENT_ROOT.'/apps/news/site/news_grid_constructor.php';
    			$NGC=new News_Grid_Constructor();
    			$news=$NGC->get_sitebill_adv_ext(array('page'=>$page,'per_page'=>$per_page));
    			
    			/*if(1==$this->getConfigValue('apps.news.use_news_topics')){
    				$this->template->assert('news_topics', $this->getNewsTopicsList());
    			}else{
    				$this->template->assert('news_topics', array());
    			}*/
    			
    			
    			if(''!=$this->getConfigValue('apps.news.meta_title')){
    				$this->template->assert('meta_title', $this->getConfigValue('apps.news.meta_title'));
    			}elseif(''!=$this->getConfigValue('apps.news.app_title')){
    				$this->template->assert('title', $this->getConfigValue('apps.news.app_title'));
    			}else{
    				$this->template->assert('title', Multilanguage::_('PAGE_TITLE','news'));
    			}
    			$this->template->assert('news', $news);
    			//$this->template->assert('pager', $news_items['pager']);
    			$this->set_apps_template('news', $this->getConfigValue('theme'), 'main_file_tpl', 'news_grid.tpl');
    			return true;
    		}
    	return false;
    }
    
    function get_more_news ( $current_news_id ) {
        $news=array();
        
        if(''!=$this->getConfigValue('apps.news.item_alias')){
        	$app_item_alias=$this->getConfigValue('apps.news.item_alias');
        }else{
        	$app_item_alias='news';
        }
        
        if(0!=(int)$this->getConfigValue('apps.news.append_more_news_view_count')){
        	$count=(int)$this->getConfigValue('apps.news.append_more_news_view_count');
        }else{
        	$count=$this->getConfigValue('apps.news.news_line.per_page');
        }
        
        if($count==0){
        	$count=4;
        }
        
        $checkuser=false;
        if(isset($_SESSION['user_domain_owner']) && (int)$_SESSION['user_domain_owner']['user_id']!=0){
        	$checkuser=true;
        }
        
        $query='SELECT * FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE news_id <> '.$current_news_id.($checkuser ? ' AND user_id='.$_SESSION['user_domain_owner']['user_id'] : '').' ORDER BY `date` DESC LIMIT '.$count;
        $this->db->exec($query);
        if($this->db->success){
        	$i=0;
        	while($this->db->fetch_assoc()){
        		$this->db->row['date']=date('d.m.Y', $this->db->row['date']);
        		$news[$i]=$this->db->row;
        		$news[$i]['href']=$this->getNewsRoute($this->db->row['news_id'], $this->db->row['newsalias']);
        		$i++;
        	}
        
        }
        if(count($news)>0){
        	$hasUploadify=false;
        	$uploads=false;
        	$model=$this->data_model;
        	foreach ($model[$this->table_name] as $mitem){
        		if($mitem['type']=='uploadify_image'){
        			$hasUploadify=true;
        			continue;
        		}
        	}
        	if(!$hasUploadify){
        		foreach ($this->data_model[$this->table_name] as $mitem){
        			if($mitem['type']=='uploads'){
        				$uploads=$mitem['name'];
        				continue;
        			}
        		}
        	}
        	
        	if($hasUploadify){
        		foreach($news as $k=>$n){
        			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
        			$data_model = new Data_Model();
        			$image_array = $data_model->get_image_array ( 'news', 'news', 'news_id', $n['news_id'] );
        			if ( count($image_array) > 0 ) {
        				$news[$k]['prev_img']=$image_array[0]['img_preview'];
        			}
        		}
        	}elseif($uploads!=''){
        		foreach($news as $k=>$n){
        			if($n[$uploads]!=''){
        				$ims=unserialize($n[$uploads]);
        			}else{
        				$ims=array();
        			}
        			if(isset($ims[0])){
        				$news[$k]['prev_img']=SITEBILL_MAIN_URL.'/img/data/'.$ims[0]['preview'];
        			}
        		}
        	}
        	
        }
        return $news;
    }
    
    
    
}