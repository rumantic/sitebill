<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * Tags Manager admin backend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class toolbox_admin extends Object_Manager {
    /**
     * Constructor
     */
    function __construct( $realty_type = false ) {
        $this->SiteBill();
        $this->action='toolbox';
    }
    
    
    function getTopMenu () {
    	return '';
    }
    
    
	function _defaultAction () {
		$ret='<a class="btn" href="'.SITEBILL_MAIN_URL.'/admin/index.php?action='.$this->action.'&do=previewAdopt">Подгонка превьюшек</a> ';
		$ret.='<a class="btn" href="'.SITEBILL_MAIN_URL.'/admin/index.php?action='.$this->action.'&do=fromUploadify">Из Uploadify в Uploads</a>';
    	return $ret;
    }
    
    function _previewAdoptAction () {
    	$this->previewAdopt();
    	return $this->_defaultAction();
    }
    
    private function previewAdopt(){
    	$start=(int)$this->getRequestValue('start');
    	$step=100;
    	

    	
    	$pw=$this->getConfigValue('apps.realty.data_image_preview_width');
    	$ph=$this->getConfigValue('apps.realty.data_image_preview_height');
    	
    	$DBC=DBC::getInstance();
    	$query='SELECT i.* FROM re_data_image di LEFT JOIN re_image i USING(image_id) ORDER BY di.data_image_id LIMIT '.$start.', '.$step;
    	$stmt=$DBC->query($query);
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$ret[]=$ar;
    		}
    		foreach ($ret as $img){
    			$prev=$img['preview'];
    			$normal=$img['normal'];
    			$must_resize=false;
    			if(file_exists(SITEBILL_DOCUMENT_ROOT.'/img/data/'.$normal)){
    				if(file_exists(SITEBILL_DOCUMENT_ROOT.'/img/data/'.$prev)){
    					$sizes=getimagesize(SITEBILL_DOCUMENT_ROOT.'/img/data/'.$prev);
    					if($pw!=$sizes[0] || $ph!=$sizes[1]){
    						$must_resize=true;
    					}
    				}else{
    					$must_resize=true;
    				}
    				
    				if($must_resize){
    					$arr=explode('.', $prev);
    					$ext=strtolower($arr[count($arr)-1]);
    					$this->makePreview(SITEBILL_DOCUMENT_ROOT.'/img/data/'.$normal, SITEBILL_DOCUMENT_ROOT.'/img/data/'.$prev, $pw, $ph, $ext, 'smart');
    				}
    			}
    		}
    		header('location: '.SITEBILL_MAIN_URL.'/admin/index.php?action='.$this->action.'&do=previewAdopt&start='.($start+$step));
    		exit();
    	}
    	header('location: '.SITEBILL_MAIN_URL.'/admin/index.php?action='.$this->action);
    	exit();
    }
    
    function _fromUploadifyAction () {
    	$rs = '';
    	if(isset($_POST['uploads_field']) && trim($_POST['uploads_field'])!=''){
    		$rs .= 'Запуск миграции картинок для поля '.$_POST['uploads_field'].'<br>';
    		$field=trim($_POST['uploads_field']);
    		$ret=array();
    		$DBC=DBC::getInstance();
    		
    		$pdata=array();
    		$query='SELECT id, `'.$field.'` FROM '.DB_PREFIX.'_data WHERE `'.$field.'`<>\'\'';
    		$stmt=$DBC->query($query);
    		if($stmt){
    			while($ar=$DBC->fetch($stmt)){
    				$x=unserialize($ar[$field]);
    				if(!is_array($x) || empty($x)){
    					$pdata[]=$ar['id'];
    				}
    			}
    		}
    		
    		if(!empty($pdata)){
    			$rs .= 'Загружен массив записей с картинками uploads <pre>'.var_export($pdata, true).'</pre><br>';
    			 
    			$query='UPDATE '.DB_PREFIX.'_data SET `'.$field.'`=\'\' WHERE id=?';
    			foreach($pdata as $id){
    				$stmt=$DBC->query($query, array($id));
    			}
    		} else {
    			$rs .= 'Массив записей с картинками uploads пуст!<br>';
    		}
    		
    		$query='SELECT di.id, i.* FROM '.DB_PREFIX.'_data_image di LEFT JOIN '.DB_PREFIX.'_image i USING(image_id) ORDER BY di.sort_order';
    		$stmt=$DBC->query($query);
    		if($stmt){
    			while($ar=$DBC->fetch($stmt)){
    				$ret[]=$ar;
    			}
    		}
    		if(!empty($ret)){
    			$rs .= 'В базе имеется массив картинок от uploadify <pre>'.var_export($ret, true).'</pre>';
    			 
    			foreach($ret as $rt){
    				//$_rt=$rt;
    				if(isset($rt['normal'])){
    					$_rt['normal']=$rt['normal'];
    				}else{
    					$_rt['normal']='';
    				}
    				if(isset($rt['preview'])){
    					$_rt['preview']=$rt['preview'];
    				}else{
    					$_rt['preview']='';
    				}
    				if(isset($rt['title'])){
    					$_rt['title']=$rt['title'];
    				}else{
    					$_rt['title']='';
    				}
    				if(isset($rt['description'])){
    					$_rt['description']=$rt['description'];
    				}else{
    					$_rt['description']='';
    				}
    				//unset($_rt['id']);
    				$images[$rt['id']][]=$_rt;
    			}
    		}
    		if(!empty($images)){
    			foreach($images as $id=>$idimages){
    				foreach($idimages as $k=>$im){
    					$images[$id][$k]['mime']=strtolower(end(explode('.', $im['normal'])));
    					$images[$id][$k]['type']='graphic';
    				}
    			}
    			$query='UPDATE '.DB_PREFIX.'_data SET `'.$field.'`=? WHERE `'.$field.'`=\'\' AND id=?';
    			foreach($images as $id=>$idimages){
    				$stmt=$DBC->query($query, array(serialize($idimages), $id));
    			}
    		}
    		
    		return $rs .= $this->_defaultAction();
    	}else{
    		return '<form action="'.SITEBILL_MAIN_URL.'/admin/index.php?action='.$this->action.'&do=fromUploadify" method="post">Системное имя поля Uploads для картинок <input type="text" name="uploads_field"> <input type="submit" name="submit" value="Пуск" class="btn"></form>';
    	}
    	
    }
}