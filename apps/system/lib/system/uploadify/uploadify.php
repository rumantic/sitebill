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
            $targetPath = SITEBILL_DOCUMENT_ROOT.'/cache/upl'.'/';

	        $arr=explode('.', $_FILES[$file_container_name]['name']);
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
            if ( $file_mode == 'excel' ) {
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
            $preview_name_tmp="jpg_".uniqid().'_'.time()."_".$i.".".$ext;
            $targetFile =  str_replace('//','/',$targetPath) . $preview_name_tmp;
            
            while ( file_exists($targetFile) ) {
                $i++;
                $preview_name_tmp="jpg_".uniqid().'_'.time()."_".$i.".".$ext;
                $targetFile =  str_replace('//','/',$targetPath) . $preview_name_tmp;
            }
		    //echo str_replace($_SERVER['DOCUMENT_ROOT'],'',$targetFile);
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
    		$stmt=$DBC->query($query, array($_REQUEST['session'], $targetFile, $element_name));
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
?>
