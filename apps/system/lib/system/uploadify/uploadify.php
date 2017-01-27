<?php
/**
 * Uploadify class 
 * Store data into UPLOADIFY table
 */
if (  !defined('UPLOADIFY_TABLE')  ) 
{
    define('UPLOADIFY_TABLE', DB_PREFIX.'_uploadify');
}

class Sitebill_Uploadify extends Sitebill {
    /**
     * Constructor
     */
    function __construct() {
        $this->Sitebill();
    }
    
    /**
     * Main
     * @param boolean $file_mode
     * @return string
     */
    function main ( $file_mode = false ) {
    	
    	if(1==$this->getRequestValue('simple_mode')){
    		$simple_mode=true;
    	}else{
    		$simple_mode=false;
    	}
    	    	    	
    	$uploader_type=$this->getConfigValue('uploader_type');
    	if('dropzone'==$this->getRequestValue('uploader_type')){
    		$uploader_type='dropzone';
    	}
    	
        if (!empty($_FILES)) {
        	
        	switch($uploader_type){
        		case 'pluploader' : {
                	$file_container_name='file';
                	break;
                }
        		case 'dropzone' : {
        			$file_container_name='file';
        			break;
        		}
                default : {
                	$file_container_name='Filedata';
                }
        	}
        	
        	$tempFile = $_FILES[$file_container_name]['tmp_name'];
            $targetPath = SITEBILL_DOCUMENT_ROOT.'/cache/upl/';

	        $arr=explode('.', $_FILES[$file_container_name]['name']);
                $file_name_without_ext = $arr[0];
	        $ext=strtolower(end($arr));
	        
	       	        
            if ( ($_FILES[$file_container_name]['size'] / 1000000) >   ( (int)str_replace('M', '', ini_get('upload_max_filesize')) ) ) {
                if($uploader_type=='dropzone'){
                	header('HTTP/1.1 200 OK');
                	header('Content-Type: application/json');
                	echo json_encode(array('status'=>'error', 'msg'=>'Недопустимый размер файла'));
                }else{
                	echo 'max_file_size';
                }
                return;
            }
            if($uploader_type=='dropzone' && ''!=$this->getRequestValue('model')){
            	$DBC=DBC::getInstance();
            	$query='SELECT * FROM '.DB_PREFIX.'_columns WHERE name=? AND table_id=(SELECT table_id FROM '.DB_PREFIX.'_table WHERE name=? LIMIT 1)';
            	$stmt=$DBC->query($query, array($this->getRequestValue('element'), $this->getRequestValue('model')));
            	if(!$stmt){
            		header('HTTP/1.1 200 OK');
            		header('Content-Type: application/json');
            		echo json_encode(array('status'=>'error', 'msg'=>'Недопустимый тип файла'));
            		return;
            	}
            	$ar=$DBC->fetch($stmt);
            	
            	if($ar['parameters']!=''){
            		$parameters=unserialize($ar['parameters']);
            	}else{
            		$parameters=array();
            	}
                 	
            	if($ar['type']=='docuploads'){
            		$allowed_exts=array('doc', 'xls', 'pdf', 'xlsx', 'txt', 'csv');
            		
            		if($parameters['accepted']!=''){
            			$av=explode(',', $parameters['accepted']);
            			if(!empty($av)){
            				foreach ($av as $k=>$v){
            					$v=trim(ltrim($v, '.'));
            					if($v==''){
            						unset($av[$k]);
            					}else{
            						$av[$k]=$v;
            					}
            				}
            			}
            			if(!empty($av)){
            				$allowed_exts=$av;
            			}
            		}
            	}else{
            		
            		if(isset($parameters['max_img_count']) && $parameters['max_img_count']!=''){
            			
            			$max_img_count=intval($parameters['max_img_count']);
            		}else{
            			$max_img_count=-1;
            		}
            		
            		if($max_img_count>-1){
            			
            			$element=$this->getRequestValue('element');
            			$model=$this->getRequestValue('model');
            			$primary_key=$this->getRequestValue('primary_key');
            			$primary_key_value=intval($this->getRequestValue('primary_key_value'));
            			$DBC=DBC::getInstance();
            			$query='SELECT `'.$element.'` FROM '.DB_PREFIX.'_'.$model.' WHERE `'.$primary_key.'`=? LIMIT 1';
            			
            			$stmt=$DBC->query($query, array($primary_key_value));
            			
            			$attached_yet=array();
            			
            			if($stmt){
            				$ar=$DBC->fetch($stmt);
            				if($ar[$element]!=''){
            					$attached_yet=unserialize($ar[$element]);
            				}
            			}
            			
            			$quenue=0;
            			
            			$query = 'SELECT COUNT(*) AS _cnt FROM '.UPLOADIFY_TABLE.' WHERE `session_code`=? AND `element`=?';
            			$stmt=$DBC->query($query, array($_REQUEST['session'], $element));
            			if($stmt){
            				$ar=$DBC->fetch($stmt);
            				$quenue=$ar['_cnt'];
            			}
            			
            			$last_count=$max_img_count-count($attached_yet)-$quenue;
            			
            			if($last_count<1){
            				header('HTTP/1.1 200 OK');
		            		header('Content-Type: application/json');
		            		echo json_encode(array('status'=>'error', 'msg'=>'Максимальное количество файлов '.$max_img_count));
		            		return;
            			}
            			
            		}
            		
            		$allowed_exts=array('jpg','png','gif','jpeg');
            	}
            	
            	if(!in_array($ext, $allowed_exts)){
            		header('HTTP/1.1 200 OK');
            		header('Content-Type: application/json');
            		echo json_encode(array('status'=>'error', 'msg'=>'Недопустимый тип файла'));
            		return;
            	}
				
				/*if(!empty($av)){
					foreach ($av as $k=>$v){
						$v=trim(ltrim($v, '.'));
						if($v==''){
							unset($av[$k]);
						}
					}
				}
				if(!empty($av)){
					$allowed_exts=$av;
				}*/
            	/*if ( !in_array(strtolower($ext), $allowed_exts) ) {
            		if($uploader_type=='dropzone'){
            			header('HTTP/1.1 200 OK');
            			header('Content-Type: application/json');
            			echo json_encode(array('status'=>'error', 'msg'=>'Недопустимый тип файла'));
            		}else{
            			echo 'wrong_ext';
            		}
            		return;
            	}*/
            	
            	
            }elseif ( $file_mode == 'excel' ) {
            	$avail_ext = array('xls','xlsx');
            	if ( !in_array(strtolower($ext), $avail_ext) ) {
            		if($uploader_type=='dropzone'){
            			header('HTTP/1.1 200 OK');
           				header('Content-Type: application/json');
           				echo json_encode(array('status'=>'error', 'msg'=>'Недопустимый тип файла'));
            		}else{
            			echo 'wrong_ext';
            		}
            		return;
            	}
            } elseif ( $file_mode ) {
                $avail_ext = array('png','jpg','tif','jpeg','doc','docx','xls','xlsx','pdf','txt','zip','rar');
                if ( !in_array(strtolower($ext), $avail_ext) ) {
                	if($uploader_type=='dropzone'){
            			header('HTTP/1.1 200 OK');
            			header('Content-Type: application/json');
            			echo json_encode(array('status'=>'error', 'msg'=>'Недопустимый тип файла'));
            		}else{
            			echo 'wrong_ext';
            		}
                    return;
                }
            } elseif ( !in_array(strtolower($ext),array('jpg','png','gif','jpeg'))) {
            	if($uploader_type=='dropzone'){
           			header('HTTP/1.1 200 OK');
           			header('Content-Type: application/json');
           			echo json_encode(array('status'=>'error', 'msg'=>'Недопустимый тип файла'));
           			//echo 'Недопустимый тип файла';
           		}else{
           			echo 'wrong_ext';
           		}
                return;
            }
            $i = 1;
            if ( $this->getConfigValue('use_native_file_name_on_uploadify') ) {
                $preview_name_tmp=$this->transliteMe($file_name_without_ext).".".$ext;
            } else {
                $preview_name_tmp="jpg_".uniqid().'_'.time()."_".$i.".".$ext;
            }
            $targetFile =  str_replace('//','/',$targetPath) . $preview_name_tmp;
            
            while ( file_exists($targetFile) ) {
                $i++;
                if ( $this->getConfigValue('use_native_file_name_on_uploadify') ) {
                    $preview_name_tmp=$i.$preview_name_tmp;
                } else {
                    $preview_name_tmp="jpg_".uniqid().'_'.time()."_".$i.".".$ext;
                }
                $targetFile =  str_replace('//','/',$targetPath) . $preview_name_tmp;
            }
		   
		    if($uploader_type=='dropzone'){
		    	header('HTTP/1.1 200 OK');
		    	header('Content-Type: application/json');
		    	echo json_encode(array('status'=>'OK', 'msg'=>SITEBILL_MAIN_URL.str_replace(SITEBILL_DOCUMENT_ROOT, '', $targetFile)));
		    }else{
		    	echo SITEBILL_MAIN_URL.str_replace(SITEBILL_DOCUMENT_ROOT, '', $targetFile);
		    }
            
		    move_uploaded_file($tempFile,$targetFile);
		    /* На случай, если сервер выставляет на загруженные файлы права 0600*/
		     chmod($targetFile, 0755);
		    /**/
		    
        }
        if(!$simple_mode){
        	if($uploader_type=='dropzone'){
        		$element=$this->getRequestValue('element');
        		$this->addFile($_REQUEST['session'], $preview_name_tmp, $element);
        	}else{
        		$this->addFile($_REQUEST['session'], $preview_name_tmp);
        	}
        }
    }
    
    /**
     * Add file
     * @param string $session_code session code
     * @param string $targetFile target file
     * @return boolean
     */
    function addFile ( $session_code, $targetFile, $element_name='' ) {
    	$DBC=DBC::getInstance();
    	if($element_name!=''){
    		$query = 'INSERT INTO '.UPLOADIFY_TABLE.' (`session_code`, `file_name`, `element`) VALUES (?, ?, ?)';
    		$stmt=$DBC->query($query, array($session_code, $targetFile, $element_name));
    	}else{
    		$query = 'INSERT INTO '.UPLOADIFY_TABLE.' (`session_code`, `file_name`) VALUES (?, ?)';
    		$stmt=$DBC->query($query, array($session_code, $targetFile));
    	}
        if($stmt){
        	return true;
        }else{
        	return false;
        }
        
    }
}