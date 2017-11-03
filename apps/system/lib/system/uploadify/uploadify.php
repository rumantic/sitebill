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
	        
	        if(!$this->isMimeGood($tempFile, $ext, $mime)){
	        	if($uploader_type=='dropzone'){
	        		header('HTTP/1.1 200 OK');
	        		header('Content-Type: application/json');
	        		echo json_encode(array('status'=>'error', 'msg'=>'bad_file '.$mime));
	        	}else{
	        		echo 'bad_file';
	        	}
	        	return;
	        }
	        
	       	        
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
            		$allowed_exts=array('docx', 'doc', 'xls', 'pdf', 'xlsx');
            		
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
                $avail_ext = array('png','jpg','jpeg','doc','docx','xls','xlsx','pdf','zip','rar', 'csv');
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
		    	/*$handle = fopen($tempFile, "rb");
		    	$contents = fread($handle, filesize($tempFile));
		    	fclose($handle);
		    	$contents='data:image/png;base64,'.base64_encode($contents);
		    	*/
		    	//  $data = file_get_contents($path);
		    	// $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
		    	
		    	//echo '<img src="data:image/png;base64,'.base64_encode($contents).'">';
		    	//exit();
		    	echo SITEBILL_MAIN_URL.str_replace(SITEBILL_DOCUMENT_ROOT, '', $targetFile);
		    	//echo $contents."||".$preview_name_tmp;
		    	//header('HTTP/1.1 200 OK');
		    	//header('Content-Type: application/json');
		    	//echo json_encode(array('status'=>'OK', 'msg'=>$contents));
		    }
		    //
		    
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
    
    function isMimeGood($tempFile, $ext, &$mime){
    	$ext=strtolower($ext);
    	/*$fh=fopen($tempFile,'rb');
    	$bytes6=fread($fh,6);
    	var_dump($bytes6);
    	var_dump(bin2hex($bytes6));
    	if($bytes6=="\xd0\xcf\x11\xe0\xa1\xb1"){
    		echo "AAAA";
    	}*/
    	/*$fh=fopen($tempFile,'rb');
    	if ($fh) {
    		$bytes6=fread($fh,6);
    		fclose($fh);
    		if ($bytes6===false) return false;
    		if (substr($bytes6,0,3)=="\xff\xd8\xff") echo 'image/jpeg';
    		if ($bytes6=="\x89PNG\x0d\x0a") echo 'image/png';
    		if ($bytes6=="GIF87a" || $bytes6=="GIF89a") echo 'image/gif';
    		echo 'application/octet-stream';
    	}*/
    	
    	if(function_exists( 'finfo_open' ) && function_exists( 'finfo_file' ) && function_exists( 'finfo_close' ) ) {
    		$fileinfo = finfo_open( FILEINFO_MIME );
    		$output = finfo_file( $fileinfo, $tempFile );    		
    		finfo_close( $fileinfo );  
    		if($output!=''){
    			list($mct)=explode("; ",$output);
    		}
    		$mime=$mct;
    	}elseif(function_exists('mime_content_type')){
    		$mct=mime_content_type($tempFile);
    		$mime=$mct;
    	}else{
    		$mct='';
    		$fh=fopen($tempFile,'rb');
    		$bytes6=fread($fh,6);
    		if($ext=='png' && $bytes6=="\x89PNG\x0d\x0a"){
    			$mct='image/png';
    		}
    		if($ext=='jpg' && substr($bytes6,0,3)=="\xff\xd8\xff"){
    			$mct='image/jpeg';
    		}
    		if($ext=='jpeg' && substr($bytes6,0,3)=="\xff\xd8\xff"){
    			$mct='image/jpeg';
    		}
    		if($ext=='gif' && ($bytes6=="GIF87a" || $bytes6=="GIF89a")){
    			$mct='image/gif';
    		}
    		if($ext=='pdf' && substr($bytes6,0,4)=="%PDF"){
    			$mct='application/pdf';
    		}
    		if($ext=='xlsx'){
    			//$mct='application/vnd.ms-excel';
				$mct='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    		}
    		if($ext=='xls' && $bytes6=="\xd0\xcf\x11\xe0\xa1\xb1"){
    			$mct='application/vnd.ms-excel';
    		}
    		if($ext=='docx'){
    			//$mct='application/msword';
				$mct='application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    		}
    		if($ext=='doc' && $bytes6=="\xd0\xcf\x11\xe0\xa1\xb1"){
    			$mct='application/msword';
    		}
    		if($ext=='csv'){
    			$mct='text/plain';
    		}
    		if($ext=='txt'){
    			$mct='text/plain';
    		}
    		if($ext=='xml'){
    			$mct='application/xml';
    		}
    		if($ext=='zip' && substr($bytes6,0,2)=="PK"){
    			$mct='application/zip';
    		}
    		if($ext=='rar' && substr($bytes6,0,4)=="Rar!"){
    			$mct='application/x-rar';
    		}
    		
    		$mime=$mct;
    	}
    	
    	if($ext=='png' && $mct=='image/png'){
    		return true;
    	}elseif($ext=='jpg' && $mct=='image/jpeg'){
    		return true;
    	}elseif($ext=='jpeg' && $mct=='image/jpeg'){
    		return true;
    	}elseif($ext=='gif' && $mct=='image/gif'){
    		return true;
    	}elseif($ext=='pdf' && $mct=='application/pdf'){
    		return true;
    	/*}elseif($ext=='xlsx' && $mct=='application/vnd.ms-excel'){*/
    	}elseif($ext=='xlsx' && $mct=='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'){
    		return true;
    	}elseif($ext=='xls' && $mct=='application/vnd.ms-excel'){
    		return true;
    	//}elseif($ext=='docx' && $mct=='application/msword'){
    	}elseif($ext=='docx' && $mct=='application/vnd.openxmlformats-officedocument.wordprocessingml.document'){
    		return true;
    	}elseif($ext=='doc' && $mct=='application/msword'){
    		return true;
    	}elseif($ext=='csv' && $mct=='text/plain'){
    		return true;
    	}elseif($ext=='txt' && $mct=='text/plain'){
    		return true;
    	}elseif($ext=='xml' && $mct=='application/xml'){
    		return true;
    	}elseif($ext=='zip' && $mct=='application/zip'){
    		return true;
    	}elseif($ext=='rar' && $mct=='application/x-rar'){
    		return true;
    	}
    	return false;
    	/*
    	 * Extension MIME Type
.doc      application/msword
.dot      application/msword

.docx     application/vnd.openxmlformats-officedocument.wordprocessingml.document
.dotx     application/vnd.openxmlformats-officedocument.wordprocessingml.template
.docm     application/vnd.ms-word.document.macroEnabled.12
.dotm     application/vnd.ms-word.template.macroEnabled.12

.xls      application/vnd.ms-excel
.xlt      application/vnd.ms-excel
.xla      application/vnd.ms-excel

.xlsx     application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
.xltx     application/vnd.openxmlformats-officedocument.spreadsheetml.template
.xlsm     application/vnd.ms-excel.sheet.macroEnabled.12
.xltm     application/vnd.ms-excel.template.macroEnabled.12
.xlam     application/vnd.ms-excel.addin.macroEnabled.12
.xlsb     application/vnd.ms-excel.sheet.binary.macroEnabled.12

.ppt      application/vnd.ms-powerpoint
.pot      application/vnd.ms-powerpoint
.pps      application/vnd.ms-powerpoint
.ppa      application/vnd.ms-powerpoint

.pptx     application/vnd.openxmlformats-officedocument.presentationml.presentation
.potx     application/vnd.openxmlformats-officedocument.presentationml.template
.ppsx     application/vnd.openxmlformats-officedocument.presentationml.slideshow
.ppam     application/vnd.ms-powerpoint.addin.macroEnabled.12
.pptm     application/vnd.ms-powerpoint.presentation.macroEnabled.12
.potm     application/vnd.ms-powerpoint.template.macroEnabled.12
.ppsm     application/vnd.ms-powerpoint.slideshow.macroEnabled.12

.mdb      application/vnd.ms-access
    	 */
    }
}