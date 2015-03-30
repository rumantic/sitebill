<?php
/**
 * Form generator
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class Form_Generator extends SiteBill {
    
    /**
     * Total values count in select
     * @var array
     */
    
    var $total_in_select = array();
    /**
     * Construct
     * @param void
     * @return void
     */
    function __construct() {
        $this->SiteBill();
    }
    
    function compile_price_element($item_array){
    	$value=number_format((int)str_replace(' ', '', $item_array['value']),0,',',' ');
    	$id=md5($item_array['name'].'_'.rand(100,999));
    	$string = '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/js/autoNumeric-1.7.5.js"></script>';
    	$string .= '<script type="text/javascript">
		    	$(document).ready(function() {
		    		$("#'.$id.'").autoNumeric({aSep: \' \', vMax: \'999999999999\', vMin: \'0\'});
      
      
				});
    		</script>';
   		return array(
    		'title'=>$item_array['title'],
    		'required'=>($item_array['required'] == "on" ? 1 : 0),
    		'html'=>$string.'<input type="text" id="'.$id.'" class="price_field" name="'.$item_array['name'].'" value="'.($value!=0 ? $value : '').'" />',
    		'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    }
    
    function compile_textarea_editor_element($item_array){
    	$parameters=$item_array['parameters'];
    	sleep(1);
    	$id='id'.time().'_'.rand(0,9);
    	$rs='';
    	if(isset($item_array['editor']) AND ($item_array['editor']!=='editor')){
    		if($this->getConfigValue($item_array['editor'])!=''){
    			$editor_code=$this->getConfigValue($item_array['editor']);
    		}else{
    			$editor_code=$this->getConfigValue('editor');
    		}
    	}else{
    		$editor_code=$this->getConfigValue('editor');
    	}
    	if ( $editor_code == 'ckeditor' ) {
    		$rs .= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/ckeditor/ckeditor.js"></script>';
    		$rs .= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/ckeditor/adapters/jquery.js"></script>';
    		$rs .= '<script type="text/javascript">
		    	$(document).ready(function() {
        			$("textarea#'.$id.'").ckeditor({
		filebrowserBrowseUrl : \'/ckfinder/ckfinder.html\',
        filebrowserImageBrowseUrl : \'/ckfinder/ckfinder.html?Type=Images\',
        filebrowserFlashBrowseUrl : \'/ckfinder/ckfinder.html?Type=Flash\',
        filebrowserUploadUrl : \'/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files\',
        filebrowserImageUploadUrl : \'/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images\',
        filebrowserFlashUploadUrl : \'/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash\'
    				});
				});
    		</script>';
    	} elseif ( $editor_code == 'wysibb' ) {
    	
    		$rs .= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/wysibb/jquery.wysibb.min.js"></script>';
    		$rs .= '<link rel="stylesheet" href="'.SITEBILL_MAIN_URL.'/wysibb/theme/default/wbbtheme.css" />';
    		$rs .= '<script type="text/javascript">
		    	$(document).ready(function() {
        			$("textarea#'.$id.'").wysibb({
					buttons: "bold,italic,underline,|,img,link,|,code,quote"
					});
				});
    		</script>';
    	} elseif( $editor_code == 'bbeditor' ){
    		$rs .= '<link rel="stylesheet" type="text/css" href="'.SITEBILL_MAIN_URL.'/apps/bbcode/site/js/bbeditor/bbeditor.css" />';
    		$rs .= '<script src="'.SITEBILL_MAIN_URL.'/apps/bbcode/site/js/bbeditor/jquery.bbcode.js" type="text/javascript"></script>';
    		$rs .= '<script type="text/javascript">
			  $(document).ready(function(){
			    $("textarea#'.$id.'").bbcode({tag_bold:true,tag_italic:true,tag_underline:true,tag_link:true,tag_image:true,button_image:false});
			    process();
			  });
			
			  var bbcode="";
			  function process()
			  {
			    if (bbcode != $("textarea#'.$id.'").val())
			      {
			        bbcode = $("textarea#'.$id.'").val();
			        $.get("'.SITEBILL_MAIN_URL.'/apps/bbcode/site/js/bbeditor/bbParser.php",
			        {
			          bbcode: bbcode
			        },
			        function(txt){
			          $("#test'.$id.'").html(txt);
			        })
			      }
			    setTimeout("process()", 2000);
			  }
			</script>';
    	}else {
    		if(isset($parameters['width']) && (int)$parameters['width']!=0){
    			$width=$parameters['width'];
    		}else{
    			$width=350;
    		}
    		$rs .= '<link rel="stylesheet" type="text/css" href="'.SITEBILL_MAIN_URL.'/js/cleditor/jquery.cleditor.css" />
    		<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/js/cleditor/jquery.cleditor.min.js"></script>
    		<script type="text/javascript">
      			$(document).ready(function() {
        			$("textarea#'.$id.'").cleditor({width:'.$width.'});
				});
    		</script>
        	';
    	}
    	
    	
    	if ( $item_array['rows'] == '' ) {
    		$item_array['rows'] = 10;
    	}
    	
    	if ( $item_array['cols'] == '' ) {
    		$item_array['cols'] = 30;
    	}
    	
    	$rs .= '<textarea id="'.$id.'" class="input" name="'.$item_array['name'].'" rows="'.$item_array['rows'].'" cols="'.$item_array['cols'].'">'.$item_array['value'].'</textarea>';
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'html'=>$rs,
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    }
    
    function compile_textarea_element ( $item_array ) {
        if ( !isset($item_array['rows']) ) {
            $item_array['rows'] = 10;
        }
        
        if(isset($item_array['parameters']['rows']) && (int)$item_array['parameters']['rows']!=0){
        	$item_array['rows'] = (int)$item_array['parameters']['rows'];
        }
        
        if ( !isset($item_array['cols']) ) {
        	$item_array['cols'] = 40;
        }
        
        if(isset($item_array['parameters']['cols']) && (int)$item_array['parameters']['cols']!=0){
        	$item_array['cols'] = (int)$item_array['parameters']['cols'];
        }
        
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'html'=>'<textarea name="'.$item_array['name'].'" rows="'.$item_array['rows'].'" cols="'.$item_array['cols'].'">'.$item_array['value'].'</textarea>',
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    }
    
    function compile_captcha_element ( $item_array ) {
    	/*
    	$captcha_type=$this->getConfigValue('captcha_type');
    	if($captcha_type==2){
    		return FALSE;
    	}elseif($captcha_type==1){
    		$this->clear_captcha_session_table();
    		$captcha_session_key = $this->generateCaptchaSessionKey();
    		
    		$string = 'GOOGLE+CAPTCHA';
    		require_once(SITEBILL_DOCUMENT_ROOT.'/recaptchalib.php');
    		//$publickey = "6Ldv8-YSAAAAAAnsPbXndo5A2SVp9uJa3J2lkype"; // You got this from the signup page.
    		$string=recaptcha_get_html($this->getConfigValue('captcha_g_public_key'));
    		
    		return array(
    				'title'=>$item_array['title'],
    				'required'=>($item_array['required'] == "on" ? 1 : 0),
    				'html'=>$string,
    				'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    		);
    	}else{
    		$this->clear_captcha_session_table();
    		$captcha_session_key = $this->generateCaptchaSessionKey();
    		
    		$string = '<img id="capcha_img" class="capcha_img" src="'.SITEBILL_MAIN_URL.'/captcha.php?captcha_session_key='.$captcha_session_key.'" width="180" height="80" />';
    		$string .= '<br /><a href="javascript:void(0);" id="captcha_refresh" class="captcha_refresh">Обновить картинку</a>';
    		$string .= '<br /><input type="text" name="'.$item_array['name'].'" value="" size="23" maxlength="'.$item_array['maxlength'].'" />';
    		$string .= '<input type="hidden" name="captcha_session_key" value="'.$captcha_session_key.'">';
    		$string .= '<script src="http://crypto-js.googlecode.com/svn/tags/3.1.2/build/rollups/md5.js"></script>';
    		 
    		$string .= '<script type="text/javascript">';
    		$string .= '$(document).ready(function(){
    		$(".captcha_refresh").click(function(){
				var new_key=new Date().getTime();
				var hash = CryptoJS.MD5(String(new_key));
    			$(this).prevAll(".capcha_img").eq(0).attr("src", estate_folder+\'/captcha.php?captcha_session_key=\' + hash);
        		$(this).nextAll("input[name=captcha_session_key]").val(hash);
    		});
    		
    	});';
    		$string .= '</script>';
    		
    		return array(
    				'title'=>$item_array['title'],
    				'required'=>($item_array['required'] == "on" ? 1 : 0),
    				'html'=>$string,
    				'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    		);
    	}
    	*/
    	$captcha_type=$this->getConfigValue('captcha_type');
    	if($captcha_type==2){
    		return FALSE;
    	}elseif($captcha_type==3){
    		$captcha_session_key = $this->generateCaptchaSessionKey();
    		 
    		$string = '<img id="capcha_img" class="capcha_img" src="'.SITEBILL_MAIN_URL.'/third/kcaptcha/index.php?captcha_session_key='.$captcha_session_key.'" width="180" height="80" />';
    		$string .= '<br /><a href="javascript:void(0);" id="captcha_refresh" class="captcha_refresh">Обновить картинку</a>';
    		$string .= '<br /><input type="text" name="'.$item_array['name'].'" value="" />';
    		$string .= '<input type="hidden" name="captcha_session_key" value="'.$captcha_session_key.'">';
    		$string .= '<script src="'.SITEBILL_MAIN_URL.'/apps/system/js/md5.js"></script>';
    		
    		$string .= '<script type="text/javascript">';
    		$string .= '$(document).ready(function(){
	    		$(".captcha_refresh").click(function(){
					var new_key=new Date().getTime();
					var hash = CryptoJS.MD5(String(new_key));
	    			$(this).prevAll(".capcha_img").eq(0).attr("src", estate_folder+\'/third/kcaptcha/index.php?captcha_session_key=\' + hash);
	        		$(this).nextAll("input[name=captcha_session_key]").val(hash);
	    		});
	    	});';
    		$string .= '</script>';
    	}else{
    		$captcha_session_key = $this->generateCaptchaSessionKey();
    		 
    		$string = '<img id="capcha_img" class="capcha_img" src="'.SITEBILL_MAIN_URL.'/captcha.php?captcha_session_key='.$captcha_session_key.'" width="180" height="80" />';
    		$string .= '<br /><a href="javascript:void(0);" id="captcha_refresh" class="captcha_refresh">Обновить картинку</a>';
    		$string .= '<br /><input type="text" name="'.$item_array['name'].'" value="" />';
    		$string .= '<input type="hidden" name="captcha_session_key" value="'.$captcha_session_key.'">';
    		$string .= '<script src="'.SITEBILL_MAIN_URL.'/apps/system/js/md5.js"></script>';
    		
    		$string .= '<script type="text/javascript">';
    		$string .= '$(document).ready(function(){
	    		$(".captcha_refresh").click(function(){
					var new_key=new Date().getTime();
					var hash = CryptoJS.MD5(String(new_key));
	    			$(this).prevAll(".capcha_img").eq(0).attr("src", estate_folder+\'/captcha.php?captcha_session_key=\' + hash);
	        		$(this).nextAll("input[name=captcha_session_key]").val(hash);
	    		});
	    	});';
    		$string .= '</script>';
    		
    		
    		
    	}
    	$this->clear_captcha_session_table();
    	
    	
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'html'=>$string,
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    	
    }
    
    function compile_safe_string_element($item_array){
    	$value=htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING);
    	$params=$item_array['parameters'];
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'html'=>'<input type="text" name="'.$item_array['name'].'" value="'.$value.'"'.((isset($params['styles']) && $params['styles']!='') ? ' style="'.$params['styles'].'"' : '').' />',
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    }
    
    function compile_docuploads_element($item_array){
    	$script_code=array();
    	$collection=array();
    	$script_code[]='<script src="'.SITEBILL_MAIN_URL.'/apps/system/js/dropzone/dropzone.js"></script>';
    	$script_code[]='<link rel="stylesheet" type="text/css" href="'.SITEBILL_MAIN_URL.'/apps/system/js/dropzone/dropzone.css">';
    	$script_code[]= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/system/js/dataimagelist.js"></script>';
    	//$html.='<script src="'.SITEBILL_MAIN_URL.'/apps/system/js/dropzone_sitebill.js"></script>';
    	$params=$item_array['parameters'];
    	 
    	if(isset($params['max_file_size']) && 0!=(int)$params['max_file_size']){
    		$max_file_size=(int)$params['max_file_size'];
    	}else{
    		$max_file_size=(int)str_replace('M', '', ini_get('upload_max_filesize'));
    	}
    	 
    	$html=$this->getDropzonePlugin($this->get_session_key(), array('element'=>$item_array['name'], 'max_file_size'=>$max_file_size));
    	if(is_array($item_array['value']) && count($item_array['value'])>0){
    		$table_name=$item_array['table_name'];
    		$primary_key=$item_array['primary_key'];
    		$primary_key_value=$item_array['primary_key_value'];
    		$class='uploaded_'.md5(time().rand(100, 999));
    		$html.='<script></script>';
    		$html.='<div class="dz-preview-uploaded '.$class.'">';
    		$html.='<a class="btn btn-mini btn-warning dz-preview-clear" onClick="DataImagelist.dz_clearImages(this, '.$primary_key_value.', \''.$table_name.'\', \''.$primary_key.'\', \''.$item_array['name'].'\');">Удалить все</a>';
    		$html.='<ul class="dz-preview-uploaded-list">';
    
    		foreach($item_array['value'] as $ita){
    			$html.='<li class="dz-preview-uploaded-item">
    					<div class="dz-preview-uploaded-item-image-preview">
							<div class="dz-preview-uploaded-item-image">
								<img src="'.SITEBILL_MAIN_URL.'/img/data/'.$ita['preview'].'" />
							</div>
							<div class="dz-preview-uploaded-item-description" onDblClick="DataImagelist.dz_dblClick(this, '.$primary_key_value.', \''.$table_name.'\', \''.$primary_key.'\', \''.$item_array['name'].'\');">
								'.($ita['title']=='' ? 'Описание' : $ita['title']).'
							</div>
							<div class="dz-preview-uploaded-item-description-editable" style="display: none;">
								<input type="text" value="'.($ita['title']=='' ? 'Описание' : $ita['title']).'" />
								<button class="btn btn-success btn-small save_desc"><i class="icon-white icon-ok"></i></button>
								<button class="btn btn-danger btn-small canc_desc"><i class="icon-white icon-remove"></i></button>
							</div>
							<a href="javascript:void(0);" onClick="DataImagelist.dz_upImage(this, '.$primary_key_value.', \''.$table_name.'\', \''.$primary_key.'\', \''.$item_array['name'].'\');" class="btn btn-small go_up" title="Выше"><i class="icon icon-chevron-left"></i></a>
							<a href="javascript:void(0);" onClick="DataImagelist.dz_deleteImage(this, '.$primary_key_value.', \''.$table_name.'\', \''.$primary_key.'\', \''.$item_array['name'].'\');" class="btn btn-small remove" title="Удалить"><i class="icon icon-remove"></i></a>
							<a href="javascript:void(0);" onClick="DataImagelist.dz_downImage(this, '.$primary_key_value.', \''.$table_name.'\', \''.$primary_key.'\', \''.$item_array['name'].'\');" class="btn btn-small go_down" title="Ниже"><i class="icon icon-chevron-right"></i></a>
							<a href="javascript:void(0);" onClick="DataImagelist.dz_makeMain(this, '.$primary_key_value.', \''.$table_name.'\', \''.$primary_key.'\', \''.$item_array['name'].'\');" class="btn btn-small go_down" title="Сделать главной"><i class="icon icon-star"></i></a>
						</div>
						</li>';
    		}
    		$html.='</ul>';
    		$html.='</div>';
    	}
    	 
    	$collection[]=array(
    			'title'=>$item_array['title'],
    			'name'=>$item_array['name'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'html'=>$html,
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    	 
    	 
    	 
    	//$html.=$this->getDropzonePlugin($this->get_session_key());
    	$answer=new stdClass();
    	$answer->collection=$collection;
    	$answer->scripts=$script_code;
    	//print_r($answer);
    	return $answer;
    	 
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'html'=>$html,
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    }
    
    function compile_uploads_element($item_array){
    	
    	$script_code=array();
    	$collection=array();
    	$script_code[]='<script src="'.SITEBILL_MAIN_URL.'/apps/system/js/dropzone/dropzone.js"></script>';
    	$script_code[]='<link rel="stylesheet" type="text/css" href="'.SITEBILL_MAIN_URL.'/apps/system/js/dropzone/dropzone.css">';
    	$script_code[]= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/system/js/dataimagelist.js"></script>';
    	//$html.='<script src="'.SITEBILL_MAIN_URL.'/apps/system/js/dropzone_sitebill.js"></script>';
    	$params=$item_array['parameters'];
    	
    	if(isset($params['max_file_size']) && 0!=(int)$params['max_file_size']){
    		$max_file_size=(int)$params['max_file_size'];
    	}else{
    		$max_file_size=(int)str_replace('M', '', ini_get('upload_max_filesize'));
    	}
    	
    	$html=$this->getDropzonePlugin($this->get_session_key(), array('element'=>$item_array['name'], 'max_file_size'=>$max_file_size, 'min_img_count'=>(int)$params['min_img_count']));
    	if(is_array($item_array['value']) && count($item_array['value'])>0){
    		$table_name=$item_array['table_name'];
    		$primary_key=$item_array['primary_key'];
    		$primary_key_value=$item_array['primary_key_value'];
    		$class='uploaded_'.md5(time().rand(100, 999));
    		
    		$html.='<div class="dz-preview-uploaded '.$class.'">';
    		$html.='<a class="btn btn-mini btn-warning dz-preview-clear" onClick="DataImagelist.dz_clearImages(this, '.$primary_key_value.', \''.$table_name.'\', \''.$primary_key.'\', \''.$item_array['name'].'\');">Удалить все</a>';
    		$html.='<ul class="dz-preview-uploaded-list">';
    		
    		foreach($item_array['value'] as $ita){
    			$html.='<li class="dz-preview-uploaded-item">
    					<div class="dz-preview-uploaded-item-image-preview">
							<div class="dz-preview-uploaded-item-image">
								<img src="'.SITEBILL_MAIN_URL.'/img/data/'.$ita['preview'].'" />  
							</div>
							<div class="dz-preview-uploaded-item-description" onDblClick="DataImagelist.dz_dblClick(this, '.$primary_key_value.', \''.$table_name.'\', \''.$primary_key.'\', \''.$item_array['name'].'\');">
								'.($ita['title']=='' ? 'Описание' : $ita['title']).'
							</div>
							<div class="dz-preview-uploaded-item-description-editable" style="display: none;">
								<input type="text" value="'.($ita['title']=='' ? 'Описание' : $ita['title']).'" /> 
								<button class="btn btn-success btn-small save_desc"><i class="icon-white icon-ok"></i></button> 
								<button class="btn btn-danger btn-small canc_desc"><i class="icon-white icon-remove"></i></button> 
							</div>
							<a href="javascript:void(0);" onClick="DataImagelist.dz_upImage(this, '.$primary_key_value.', \''.$table_name.'\', \''.$primary_key.'\', \''.$item_array['name'].'\');" class="btn btn-small go_up" title="Выше"><i class="icon icon-chevron-left"></i></a>
							<a href="javascript:void(0);" onClick="DataImagelist.dz_deleteImage(this, '.$primary_key_value.', \''.$table_name.'\', \''.$primary_key.'\', \''.$item_array['name'].'\');" class="btn btn-small remove" title="Удалить"><i class="icon icon-remove"></i></a>
							<a href="javascript:void(0);" onClick="DataImagelist.dz_downImage(this, '.$primary_key_value.', \''.$table_name.'\', \''.$primary_key.'\', \''.$item_array['name'].'\');" class="btn btn-small go_down" title="Ниже"><i class="icon icon-chevron-right"></i></a>
							<a href="javascript:void(0);" onClick="DataImagelist.dz_makeMain(this, '.$primary_key_value.', \''.$table_name.'\', \''.$primary_key.'\', \''.$item_array['name'].'\');" class="btn btn-small go_down" title="Сделать главной"><i class="icon icon-star"></i></a>
						</div>
						</li>';
    		}
    		$html.='</ul>';
    		$html.='</div>';
    	}
    	
    	$collection[]=array(
    			'title'=>$item_array['title'],
    			'hint'=>$item_array['hint'],
    			'name'=>$item_array['name'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'html'=>$html,
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    	
    	
    	
    	//$html.=$this->getDropzonePlugin($this->get_session_key());
    	$answer=new stdClass();
    	$answer->collection=$collection;
    	$answer->scripts=$script_code;
    	//print_r($answer);
    	return $answer;
    	
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'html'=>$html,
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    }
    
    function compile_destination_element($item_array){
    	
    	$str.='<script src="'.SITEBILL_MAIN_URL.'/apps/destination/js/destination.js"></script>';
    	require_once SITEBILL_DOCUMENT_ROOT.'/apps/destination/admin/admin.php';
    	$DA=new destination_admin();
    	$html=$DA->getDestionationFormElementHTML($item_array);
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'html'=>$str.$html,
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    }
    
    function compile_gadres_element($item_array){
    	$params=$item_array['parameters'];
    	$value=htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING);
    	$id=md5(rand(1000,9999).time());
    	$str='<script>$(document).ready(function(){$( "#gadres_'.$id.'" ).autocomplete({
			open: function() { 
	            $(".ui-menu")
	                .width($( this ).width());
	        } ,
			source: function( request, response ) {
				var answer=[];
				var city_id=$( "#gadres_'.$id.'" ).parents("form").eq(0).find("[name=city_id]").val();
				$.ajax({
					url: estate_folder + "/apps/geodata/js/ajax.php",
					type: "POST",
					dataType: "json",
					data: {input: encodeURIComponent(request.term), action: "geocode_me", city_id: city_id},
					success: function(data) {
						$.map(data,function(n,i){
							var o={};
							o.value=n;
							o.label=n;
							answer.push(o);
						});
						response(answer);
					}
				});
	    	},
			minLength: 3,
		});});</script>';
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'html'=>$str.'<input type="hidden" name="gadres['.$item_array['name'].']" value="'.$value.'"><input id="gadres_'.$id.'" type="text" name="'.$item_array['name'].'" value="" placeholder="'.$value.'"'.((isset($params['styles']) && $params['styles']!='') ? ' style="'.$params['styles'].'"' : '').' />',
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    }
    
    function compile_tlocation_element($item_array){
    	
    	
    	$collection=array();
    	$is_script_attached=false;
    	$autocomplete=false;
    	
    	
    	
    	
    	
    	$rets=array();
    	$params=$item_array['parameters'];
    	
    	if(isset($params['autocomplete']) && $params['autocomplete']==1){
    		$autocomplete=true;
    	}
    	
    	if(isset($params['visibles'])){
    		$visibles=explode('|', $params['visibles']);
    	}else{
    		$visibles=array();
    	}
    	
    	if(isset($params['show_names'])){
    		$show_names=(int)$params['show_names'];
    	}else{
    		$show_names=1;
    	}
    	
    	if(isset($params['names'])){
    		$_x=array();
    		$_x=explode('|', $params['names']);
    		
    		if(!empty($_x)){
    			foreach($_x as $v){
    				list($key, $title)=explode(':', $v);
    				$field_names[$key]=$title;
    			}
    		}
    	}else{
    		$field_names=array();
    	}
    	
    	if(isset($params['default_titles'])){
    		$_x=array();
    		$_x=explode('|', $params['default_titles']);
    	
    		if(!empty($_x)){
    			foreach($_x as $v){
    				list($key, $title)=explode(':', $v);
    				$default_titles[$key]=$title;
    			}
    		}
    	}else{
    		$default_titles=array();
    	}
    	
    	$defaults=array();
    	if(isset($params['default_country_id'])){
    		$defaults['country_id']=$params['default_country_id'];
    	}
    	if(isset($params['default_region_id'])){
    		$defaults['region_id']=$params['default_region_id'];
    	}
    	if(isset($params['default_city_id'])){
    		$defaults['city_id']=$params['default_city_id'];
    	}
    	if(isset($params['default_district_id'])){
    		$defaults['district_id']=$params['default_district_id'];
    	}
    	
    	$values=$item_array['value'];
    	if(!isset($values['country_id'])){
    		$values['country_id']=0;
    	}
    	if(!isset($values['region_id'])){
    		$values['region_id']=0;
    	}
    	if(!isset($values['city_id'])){
    		$values['city_id']=0;
    	}
    	if($values['country_id']==0){
    		$values['country_id']=$defaults['country_id'];
    	}
    	if($values['region_id']==0){
    		$values['region_id']=$defaults['region_id'];
    	}
    	if($values['city_id']==0){
    		$values['city_id']=$defaults['city_id'];
    	}
    	
    	$DBC=DBC::getInstance();
    	
    	$uniq_class_name='tlocation_object_'.md5(time().'_'.rand(1000, 9999));
    	$script_code='';
    	if($autocomplete){
    		$script_code.='<link rel="stylesheet" href="'.SITEBILL_MAIN_URL.'/apps/system/js/bootstrap/css/bootstrap-combobox.css" media="screen">';
    		$script_code.='<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/system/js/bootstrap/js/bootstrap-combobox.js"></script>';
    	}
    	$script_code.='<style>.tlocation_object select {display: block; margin: 10px 0;}</style>';
    	$script_code.='<script src="'.SITEBILL_MAIN_URL.'/apps/tlocation/js/form_utils.js"></script>';
    	$script_code.='<script>$(document).ready(function(){TLocationForm.setHandler("'.$uniq_class_name.'", '.(int)$this->getConfigValue('link_street_to_city').''.($autocomplete ? ', 1' : '').')});</script>';
    	/*
    	$rs .= '<style>.tlocation_object select {display: block; margin: 10px 0;}</style>';
    	$rs .= '<script src="'.SITEBILL_MAIN_URL.'/apps/tlocation/js/form_utils.js"></script>';
    	$id='tlo_'.md5(time().'_'.rand(1000, 9999));
    	$rs .= '<script>$(document).ready(function(){setHandler("'.$id.'", '.(int)$this->getConfigValue('link_street_to_city').')});</script>';
    	$rs .= '<div class="tlocation_object" id="'.$id.'">';
    	*/
    	
    	$rs='';
    	if(empty($visibles) || (!empty($visibles) && in_array('country_id', $visibles))){
    		$data=array();
    		$query='SELECT country_id, name FROM '.DB_PREFIX.'_country ORDER BY name ASC';
    		$stmt=$DBC->query($query);
    		if($stmt){
    			while($ar=$DBC->fetch($stmt)){
    				$data[]=$ar;
    			}
    		}
    		/*
    		if(!$is_script_attached){
    			$rs.=$script_code;
    			$is_script_attached=true;
    		}
    		*/
    		$rs .= '<span class="'.$uniq_class_name.'"><select name="country_id">';
    		if($autocomplete){
    			$rs .= '<option></option>';
    		}else{
    			$rs .= '<option value="0" '.$selected.'>'.(isset($default_titles['country_id']) ? $default_titles['country_id'] : '--').'</option>';
    		}
    		
    		/*
    		$rs .= (($show_names && isset($field_names['country_id'])) ? '<label>'.$field_names['country_id'].'</label>' : '').'<select name="country_id">';
    		$rs .= '<option value="0" '.$selected.'>--</option>';
    		*/
    		if(!empty($data)){
    			foreach($data as $d){
    				if($values['country_id']==$d['country_id']){
    					$rs .= '<option value="'.$d['country_id'].'" selected="selected">'.$d['name'].'</option>';
    				}else{
    					$rs .= '<option value="'.$d['country_id'].'">'.$d['name'].'</option>';
    				}
    			}
    		}
    		//$rs .= '</select>';
    		$rs .= '</select></span>';
    		$collection[]=array(
    				'title'=>(($show_names && isset($field_names['country_id'])) ? $field_names['country_id'] : ''),
    				'name'=>'country_id',
    				'required'=>0,
    				'html'=>$rs,
    				'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    		);
    	}
    	
    	$rs='';
    	
    	if(empty($visibles) || (!empty($visibles) && in_array('region_id', $visibles))){
    		$data=array();
    		$stmt=FALSE;
    		
    		if((int)$values['country_id']!=0){
    			$query='SELECT region_id, name FROM '.DB_PREFIX.'_region WHERE country_id=? ORDER BY name ASC';
    			$stmt=$DBC->query($query, array($values['country_id']));
    		}elseif(isset($defaults['country_id']) && (int)$defaults['country_id']!=0){
    			$query='SELECT region_id, name FROM '.DB_PREFIX.'_region WHERE country_id=? ORDER BY name ASC';
    			$stmt=$DBC->query($query, array($defaults['country_id']));
    		}elseif(!empty($visibles) && !in_array('country_id', $visibles)){
    			$query='SELECT region_id, name FROM '.DB_PREFIX.'_region ORDER BY name ASC';
    			$stmt=$DBC->query($query);
    		}
    		//echo $query;
    		if($stmt){
    			while($ar=$DBC->fetch($stmt)){
    				
    				$data[]=$ar;
    			}
    		}
    		/*
    		$rs .= (($show_names && isset($field_names['region_id'])) ? '<label>'.$field_names['region_id'].'</label>' : '').'<select name="region_id">';
    		$rs .= '<option value="0" '.$selected.'>--</option>';
    		*/
    		/*if(!$is_script_attached){
    			$rs.=$script_code;
    			$is_script_attached=true;
    		}*/
    		
    		$rs .= '<span class="'.$uniq_class_name.'"><select name="region_id">';
    		if($autocomplete){
    			$rs .= '<option></option>';
    		}else{
    			$rs .= '<option value="0" '.$selected.'>'.(isset($default_titles['region_id']) ? $default_titles['region_id'] : '--').'</option>';
    		}
    		
    		 
    		if(!empty($data)){
    			foreach($data as $d){
    				if($values['region_id']==$d['region_id']){
    					$rs .= '<option value="'.$d['region_id'].'" selected="selected">'.$d['name'].'</option>';
    				}else{
    					$rs .= '<option value="'.$d['region_id'].'">'.$d['name'].'</option>';
    				}
    			}
    		}
    		//$rs .= '</select>';
    		
    		$rs .= '</select></span>';
    		
    		$collection[]=array(
    				'title'=>(($show_names && isset($field_names['region_id'])) ? $field_names['region_id'] : ''),
    				'name'=>'region_id',
    				'required'=>0,
    				'html'=>$rs,
    				'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    		);
    	}
    	
    	$rs='';
    	
    	if(empty($visibles) || (!empty($visibles) && in_array('city_id', $visibles))){
    		$data=array();
    		$stmt=FALSE;
    		if((int)$values['region_id']!=0){
    			$query='SELECT city_id, name FROM '.DB_PREFIX.'_city WHERE region_id=? ORDER BY name ASC';
    			$stmt=$DBC->query($query, array($values['region_id']));
    		}elseif(isset($defaults['region_id']) && (int)$defaults['region_id']!=0){
    			$query='SELECT city_id, name FROM '.DB_PREFIX.'_city WHERE region_id=? ORDER BY name ASC';
    			$stmt=$DBC->query($query, array($defaults['region_id']));
    		}elseif(!empty($visibles) && !in_array('region_id', $visibles)){
    			$query='SELECT city_id, name FROM '.DB_PREFIX.'_city ORDER BY name ASC';
    			$stmt=$DBC->query($query);
    		}
    	
    		if($stmt){
    			while($ar=$DBC->fetch($stmt)){
    				$data[]=$ar;
    			}
    		}
    	/*
    		$rs .= (($show_names && isset($field_names['city_id'])) ? '<label>'.$field_names['city_id'].'</label>' : '').'<select name="city_id">';
    		$rs .= '<option value="0" '.$selected.'>--</option>';
    		
    		if(!$is_script_attached){
    			$rs.=$script_code;
    			$is_script_attached=true;
    		}
    		*/ 
    		$rs .= '<span class="'.$uniq_class_name.'"><select name="city_id">';
    		if($autocomplete){
    			$rs .= '<option></option>';
    		}else{
    			$rs .= '<option value="0" '.$selected.'>'.(isset($default_titles['city_id']) ? $default_titles['city_id'] : '--').'</option>';
    		}
    		
    		 
    		if(!empty($data)){
    			foreach($data as $d){
    				if($values['city_id']==$d['city_id']){
    					$rs .= '<option value="'.$d['city_id'].'" selected="selected">'.$d['name'].'</option>';
    				}else{
    					$rs .= '<option value="'.$d['city_id'].'">'.$d['name'].'</option>';
    				}
    			}
    		}
    		//$rs .= '</select>';
    		$rs .= '</select></span>';
    		
    		$collection[]=array(
    				'title'=>(($show_names && isset($field_names['city_id'])) ? $field_names['city_id'] : ''),
    				'name'=>'city_id',
    				'required'=>0,
    				'html'=>$rs,
    				'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    		);
    	}
    	
    	$rs='';
    	
    	if(empty($visibles) || (!empty($visibles) && in_array('district_id', $visibles))){
    		$data=array();
    		$stmt=FALSE;
    		if((int)$values['city_id']!=0){
    			$query='SELECT id, name FROM '.DB_PREFIX.'_district WHERE city_id=? ORDER BY name ASC';
    			$stmt=$DBC->query($query, array($values['city_id']));
    		}elseif(isset($defaults['city_id']) && (int)$defaults['city_id']!=0){
    			$query='SELECT id, name FROM '.DB_PREFIX.'_district WHERE city_id=? ORDER BY name ASC';
    			$stmt=$DBC->query($query, array($defaults['city_id']));
    		}elseif(!empty($visibles) && !in_array('city_id', $visibles)){
    			$query='SELECT id, name FROM '.DB_PREFIX.'_district ORDER BY name ASC';
    			$stmt=$DBC->query($query);
    		}
    		 
    		if($stmt){
    			while($ar=$DBC->fetch($stmt)){
    				$data[]=$ar;
    			}
    		}
    		
    		/*if(!$is_script_attached){
    			$rs.=$script_code;
    			$is_script_attached=true;
    		}*/
    		
    		$rs .= '<span class="'.$uniq_class_name.'"><select name="district_id" data-placeholder="'.(isset($default_titles['district_id']) ? $default_titles['district_id'] : '--').'">';
    		if($autocomplete){
    			$rs .= '<option></option>';
    		}else{
    			$rs .= '<option value="0" '.$selected.'>'.(isset($default_titles['district_id']) ? $default_titles['district_id'] : '--').'</option>';
    		}
    		//
    		
    		/* 
    		$rs .= (($show_names && isset($field_names['district_id'])) ? '<label>'.$field_names['district_id'].'</label>' : '').'<select name="district_id">';
    		$rs .= '<option value="0" '.$selected.'>--</option>';
    		 */
    		if(!empty($data)){
    			foreach($data as $d){
    				if($values['district_id']==$d['id']){
    					$rs .= '<option value="'.$d['id'].'" selected="selected">'.$d['name'].'</option>';
    				}else{
    					$rs .= '<option value="'.$d['id'].'">'.$d['name'].'</option>';
    				}
    			}
    		}
    		//$rs .= '</select>';
    		$rs .= '</select></span>';
    		
    		$collection[]=array(
    				'title'=>(($show_names && isset($field_names['district_id'])) ? $field_names['district_id'] : ''),
    				'name'=>'district_id',
    				'required'=>0,
    				'html'=>$rs,
    				'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    		);
    	}
    	
    	$rs='';
    	
    	if(empty($visibles) || (!empty($visibles) && in_array('street_id', $visibles))){
    		$data=array();
    		$stmt=FALSE;
    		if((int)$values['city_id']!=0){
    			$query='SELECT street_id, name FROM '.DB_PREFIX.'_street WHERE city_id=? ORDER BY name ASC';
    			$stmt=$DBC->query($query, array($values['city_id']));
    		}elseif(isset($defaults['city_id']) && (int)$defaults['city_id']!=0){
    			$query='SELECT street_id, name FROM '.DB_PREFIX.'_street WHERE city_id=? ORDER BY name ASC';
    			$stmt=$DBC->query($query, array($defaults['city_id']));
    		}elseif(!empty($visibles) && !in_array('city_id', $visibles)){
    			$query='SELECT street_id, name FROM '.DB_PREFIX.'_street ORDER BY name ASC';
    			$stmt=$DBC->query($query);
    		}
    		 
    		if($stmt){
    			while($ar=$DBC->fetch($stmt)){
    				$data[]=$ar;
    			}
    		}
    		
    		/*if(!$is_script_attached){
    			$rs.=$script_code;
    			$is_script_attached=true;
    		}*/
    		
    		$rs .= '<span class="'.$uniq_class_name.'"><select name="street_id" data-placeholder="'.(isset($default_titles['street_id']) ? $default_titles['street_id'] : '--').'">';
    		
    		if($autocomplete){
    			$rs .= '<option></option>';
    		}else{
    			$rs .= '<option value="0" '.$selected.'>'.(isset($default_titles['street_id']) ? $default_titles['street_id'] : '--').'</option>';
    		}
    		
    		
    		/* 
    		$rs .= (($show_names && isset($field_names['street_id'])) ? '<label>'.$field_names['street_id'].'</label>' : '').'<select name="street_id">';
    		$rs .= '<option value="0" '.$selected.'>--</option>';
    		 */
    		if(!empty($data)){
    			foreach($data as $d){
    				if($values['street_id']==$d['street_id']){
    					$rs .= '<option value="'.$d['street_id'].'" selected="selected">'.$d['name'].'</option>';
    				}else{
    					$rs .= '<option value="'.$d['street_id'].'">'.$d['name'].'</option>';
    				}
    			}
    		}
    		//$rs .= '</select>';
    		$rs .= '</select></span>';
    		 
    		$collection[]=array(
    				'title'=>(($show_names && isset($field_names['street_id'])) ? $field_names['street_id'] : ''),
    				'name'=>'street_id',
    				'required'=>0,
    				'html'=>$rs,
    				'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    		);
    	}
    	
    	/*
    	
    	if(1==$this->getConfigValue('link_street_to_city')){
    		global $smarty;
    		$smarty->assign('link_street_to_city', 1);
    		if(empty($visibles) || (!empty($visibles) && in_array('street_id', $visibles))){
    			$data=array();
    			$stmt=FALSE;
    			if((int)$values['city_id']!=0){
    				$query='SELECT street_id, name FROM '.DB_PREFIX.'_street WHERE city_id=? ORDER BY name ASC';
    				$stmt=$DBC->query($query, array($values['city_id']));
    			}elseif(isset($defaults['city_id']) && (int)$defaults['city_id']!=0){
    				$query='SELECT street_id, name FROM '.DB_PREFIX.'_street WHERE city_id=? ORDER BY name ASC';
    				$stmt=$DBC->query($query, array($defaults['city_id']));
    			}elseif(!empty($visibles) && !in_array('city_id', $visibles)){
    				$query='SELECT street_id, name FROM '.DB_PREFIX.'_street ORDER BY name ASC';
    				$stmt=$DBC->query($query);
    			}
    			 
    			if($stmt){
    				while($ar=$DBC->fetch($stmt)){
    					$data[]=$ar;
    				}
    			}
    			 
    			$rs .= (($show_names && isset($field_names['street_id'])) ? '<label>'.$field_names['street_id'].'</label>' : '').'<select name="street_id">';
    			$rs .= '<option value="0" '.$selected.'>--</option>';
    			 
    			if(!empty($data)){
    				foreach($data as $d){
    					if($values['street_id']==$d['street_id']){
    						$rs .= '<option value="'.$d['street_id'].'" selected="selected">'.$d['name'].'</option>';
    					}else{
    						$rs .= '<option value="'.$d['street_id'].'">'.$d['name'].'</option>';
    					}
    				}
    			}
    			$rs .= '</select>';
    		}
    		 
    	}else{
    		if(empty($visibles) || (!empty($visibles) && in_array('district_id', $visibles))){
    			$data=array();
    			$stmt=FALSE;
    			if((int)$values['city_id']!=0){
    				$query='SELECT id, name FROM '.DB_PREFIX.'_district WHERE city_id=? ORDER BY name ASC';
    				$stmt=$DBC->query($query, array($values['city_id']));
    			}elseif(isset($defaults['city_id']) && (int)$defaults['city_id']!=0){
    				$query='SELECT id, name FROM '.DB_PREFIX.'_district WHERE city_id=? ORDER BY name ASC';
    				$stmt=$DBC->query($query, array($defaults['city_id']));
    			}elseif(!empty($visibles) && !in_array('city_id', $visibles)){
    				$query='SELECT id, name FROM '.DB_PREFIX.'_district ORDER BY name ASC';
    				$stmt=$DBC->query($query);
    			}
    			 
    			if($stmt){
    				while($ar=$DBC->fetch($stmt)){
    					$data[]=$ar;
    				}
    			}
    			 
    			$rs .= (($show_names && isset($field_names['district_id'])) ? '<label>'.$field_names['district_id'].'</label>' : '').'<select name="district_id">';
    			$rs .= '<option value="0" '.$selected.'>--</option>';
    			 
    			if(!empty($data)){
    				foreach($data as $d){
    					if($values['district_id']==$d['id']){
    						$rs .= '<option value="'.$d['id'].'" selected="selected">'.$d['name'].'</option>';
    					}else{
    						$rs .= '<option value="'.$d['id'].'">'.$d['name'].'</option>';
    					}
    				}
    			}
    			$rs .= '</select>';
    		}
    		
    		if(empty($visibles) || (!empty($visibles) && in_array('street_id', $visibles))){
    		
    			$data=array();
    			$stmt=FALSE;
    			if((int)$values['district_id']!=0){
    				$query='SELECT street_id, name FROM '.DB_PREFIX.'_street WHERE district_id=? ORDER BY name ASC';
    				$stmt=$DBC->query($query, array($values['district_id']));
    			}elseif(isset($defaults['district_id']) && (int)$defaults['district_id']!=0){
    				$query='SELECT street_id, name FROM '.DB_PREFIX.'_street WHERE district_id=? ORDER BY name ASC';
    				$stmt=$DBC->query($query, array($defaults['district_id']));
    			}elseif(!empty($visibles) && !in_array('district_id', $visibles)){
    				$query='SELECT street_id, name FROM '.DB_PREFIX.'_street ORDER BY name ASC';
    				$stmt=$DBC->query($query);
    			}
    			 
    			if($stmt){
    				while($ar=$DBC->fetch($stmt)){
    					$data[]=$ar;
    				}
    			}
    			 
    			$rs .= (($show_names && isset($field_names['street_id'])) ? '<label>'.$field_names['street_id'].'</label>' : '').'<select name="street_id">';
    			$rs .= '<option value="0" '.$selected.'>--</option>';
    			 
    			if(!empty($data)){
    				foreach($data as $d){
    					if($values['street_id']==$d['street_id']){
    						$rs .= '<option value="'.$d['street_id'].'" selected="selected">'.$d['name'].'</option>';
    					}else{
    						$rs .= '<option value="'.$d['street_id'].'">'.$d['name'].'</option>';
    					}
    				}
    			}
    			$rs .= '</select>';
    		}
    	}
    	
    	*/
    	
    	$answer=new stdClass();
    	$answer->collection=$collection;
    	$answer->scripts=array($script_code);
    	//print_r($answer);
    	return $answer;
    	
    	$rs .= '</div>';
    	
    	
    	
    	
    	 
    	 
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>0,
    			'html'=>$rs,
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    }
    
    function compile_parameter_element($item_array){
    	
    	//$value=htmlspecialchars($item_array['value']);
    	$html='';
    	$html .= '<script type="text/javascript">';
    	$html .= '$(document).ready(function(){
    		$(document).on("click", ".paramsrow a", function(){
    			$(this).parents(".paramsrow").eq(0).remove();
    			return false;
    		});
    		$("#add_column_params").click(function(){
    			var pr=$(this).parents("#paramsblock").eq(0).find(".paramsrow:last").clone();
    			$(this).before(pr);
    			return false;
    		});
    		
    	});';
    	$html .= '</script>';
    	$html .='<div id="paramsblock">';
    	//print_r($item_array['value']);
    	if(is_array($item_array['value']) && count($item_array['value'])>0){
    		foreach($item_array['value'] as $pk=>$pv){
    			$html .='<div class="paramsrow">';
    			$html .='<input type="text" name="parameters[name][]" value="'.$pk.'" />=<input type="text" name="parameters[value][]" value="'.$pv.'" />';
    			$html .='<a href="javascript:void(0);">x</a>';
    			$html .='</div>';
    		}
    	}
    	$html .='<div class="paramsrow">';
    	$html .='<input type="text" name="parameters[name][]" value="" />=<input type="text" name="parameters[value][]" value="" />';
    	$html .='<a href="javascript:void(0);">x</a>';
    	$html .='</div>';
    	$html .='<button id="add_column_params">Add</button></div>';
    	
    	
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>0,
    			'html'=>$html,
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    }
    
    function compile_attachment_element($item_array){
    	$value=htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING);
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'html'=>'<input type="text" name="'.$item_array['name'].'"  size="'.$item_array['length'].'" maxlength="'.$item_array['maxlength'].'" value="'.$value.'" />',
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    }
    
    function compile_select_box_structure_simple_multiple_element ( $item_array ) {
    	if ( !isset($item_array['values_array']) ) {
    		$item_array['values_array'] = array(0 => 0);
    	}
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
    	$Structure_Manager = new Structure_Manager();
    	
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'html'=>$Structure_Manager->getCategorySelectBoxWithName($item_array['name'], $item_array['values_array'] ),
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    
    	$rs = '<tr  class="row3"  alt="'.$item_array['name'].'">';
    	$rs .= '<td>';
    	$rs .= $item_array['title'];
    	if ( $item_array['required'] == "on" ) {
    		$rs .= " <span style=\"color: red;\">*</span> \n";
    	}
    	$rs .= '</td>';
    	$rs .= '<td>';
    	$rs .= $Structure_Manager->getCategorySelectBoxWithName($item_array['name'], $item_array['values_array'] );
    	$rs .= '</td>';
    	$rs .= '</tr>';
    
    	return $rs;
    }
    
    function compile_grade_element ( $item_array ) {
    	$html='';
    	foreach ( $item_array['grade_values'] as $item_id ) {
    		if ( $item_array['value'] == $item_id ) {
    			$checked = 'checked="checked"';
    		} else {
    			$checked = '';
    		}
    		$html .= '<span>'.$item_id.'</span><input type="radio" name="'.$item_array['name'].'" value="'.$item_id.'" '.$checked.'>&nbsp;&nbsp;&nbsp;';
    	}
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'html'=>$html,
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    }
    
    function compile_geodata_element($item_array){
    	$parameters=$item_array['parameters'];
    	$value=$item_array['value'];
    	$str='';
    	//$str.='<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/system/js/geodata.js"></script>';
    	
    	$map_options=array();
    	
    	if(isset($parameters['map_width']) && (int)$parameters['map_width']!=0){
    		$map_options[]='width: '.(int)$parameters['map_width'];
    	}
    	if(isset($parameters['map_height']) && (int)$parameters['map_height']!=0){
    		$map_options[]='height: '.(int)$parameters['map_height'];
    	}
    	$map_options[]='map_type: '.(1==$this->getConfigValue('use_google_map') ? '\'google\'' : '\'yandex\'');
    	
    	
    	
    	
    	$str.='<div id="geodata" coords="'.$this->getConfigValue('apps.geodata.new_map_center').'" zoom="'.$this->getConfigValue('apps.geodata.map_zoom_default').'">';
    	$str.='<input type="text" geodata="lat" name="'.$item_array['name'].'[lat]" value="'.(isset($value['lat']) ? $value['lat'] : '').'" />';
    	$str.='<input type="text" geodata="lng" name="'.$item_array['name'].'[lng]" value="'.(isset($value['lng']) ? $value['lng'] : '').'" />';
    	$str.='</div>';
    	if(1==$this->getConfigValue('apps.geodata.enable')){
    		$str.='<script src="'.SITEBILL_MAIN_URL.'/apps/system/js/md5.js"></script>';
    		$str.='<script>$(document).ready(function(){$("#geodata").Geodata('.(count($map_options)>0 ? '{'.implode(',',$map_options).'}' : '').');});</script>';
    	}
    	
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'html'=>$str,
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    }
    
    function compile_photo_element($item_array){
    	
    	if ( $item_array['value'] != '' ) {
    		$image_list = '<img src="'.SITEBILL_MAIN_URL.'/img/data/user/'.$item_array['value'].'" border="0"/><br><input type="checkbox" name="delpic" value="yes" /> Удалить фото';
    	}else{
    		$image_list = '';
    	}
    	
    	
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'html'=>'<input type="file" name="'.$item_array['name'].'" />',
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : ''),
    			'image_list'=>$image_list
    	);
    }
    
    function compile_password_element($item_array){
    	//$value=htmlspecialchars($item_array['value']);
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'html'=>'<input type="password" name="'.$item_array['name'].'" value="'.$item_array['value'].'" />',
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    }
    
    function compile_hidden_element($item_array){
    	$value=htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING);
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'html'=>'<input type="hidden" name="'.$item_array['name'].'" value="'.$value.'" />',
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    }
    
    function compile_primary_key_element($item_array){
    	$value=htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING);
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'html'=>'<input type="hidden" name="'.$item_array['name'].'" value="'.$value.'" />',
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    }
    
    function compile_mobilephone_element($item_array){
    	$value=htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING);
    	if(isset($item_array['parameters']['mask']) && $item_array['parameters']['mask']!=''){
    		$mask=$item_array['parameters']['mask'];
    	}else{
    		$mask='h (hhh) hhh-hh-hh';
    	}
    	$id=md5($item_array['name'].'_'.rand(100,999));
    	$string = '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/system/js/jquery.maskedinput.min.js"></script>';
    	$string .= '<script type="text/javascript">
		    	$(document).ready(function() {
    				$.mask.definitions["h"] = "[0-9]"
		    		$("#'.$id.'").mask("'.$mask.'");
		    				//$("#'.$id.'").mask("+38 (hhh) hhh-hh-hh");
      			});
    		</script>';
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'html'=>$string.'<input id="'.$id.'" type="text" name="'.$item_array['name'].'" value="'.$value.'" />',
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    	//return $this->compile_safe_string_element($item_array);
    }
    
    function compile_email_element($item_array){
    	return $this->compile_safe_string_element($item_array);
    }
    
    function compile_spacer_text_element ( $item_array ) {
    	   
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'html'=>$item_array['value'],
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    }
    
    function compile_selectbox_element($item_array){
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'html'=>$this->get_select_box($item_array),
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    }
    
    function compile_checkbox_element($item_array){
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'html'=>$this->get_checkbox($item_array),
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    }
    
    function compile_select_box_by_query_element($item_array, $model=null){
    	
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'html'=>$this->get_single_select_box_by_query($item_array, $model),
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    }
    
    
    
    function compile_select_box_structure_element($item_array){
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
    	$Structure_Manager = new Structure_Manager();
    	
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'html'=>$Structure_Manager->getCategorySelectBoxWithName($item_array['name'], $item_array['value'] ),
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    }
    
    function compile_structure_element($item_array){
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_implements.php');
    	$SM=Structure_Implements::getManager($item_array['entity']);
    	
    	//$equire_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
    	//$Structure_Manager = new Structure_Manager();
    	 
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'html'=>$SM->getCategorySelectBoxWithName($item_array['name'], $item_array['value'], false, $item_array['parameters'] ),
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    }
    
    function compie_select_box_by_query_multiple_element ( $item_array ) {
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'html'=>$this->get_single_select_box_by_query_multiple($item_array),
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    	$rs = '<tr  class="row3"  alt="'.$item_array['name'].'">';
    	$rs .= '<td>';
    	$rs .= $item_array['title'];
    	if ( $item_array['required'] == "on" ) {
    		$rs .= " <span style=\"color: red;\">*</span> \n";
    	}
    	$rs .= '</td>';
    	$rs .= '<td>';
    	$rs .= $this->get_single_select_box_by_query_multiple($item_array);
    	$rs .= '</td>';
    	$rs .= '</tr>';
    
    	return $rs;
    }
    
    function compile_pluploader_element ( $item_array ) {
    	
    	$_count=0;
    	$image_list=$this->getImageListAdmin($item_array['action'], $item_array['table_name'], $item_array['primary_key'], $item_array['primary_key_value'],$_count);
    	 
    	if($this->getConfigValue('photo_per_data')>0 AND $item_array['action']=='data'){
    		if($_count>=$this->getConfigValue('photo_per_data')){
    			return array(
    					'title'=>$item_array['title'],
    					'required'=>($item_array['required'] == "on" ? 1 : 0),
    					'image_list'=>$image_list,
    					'html'=>'',
    					'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    			);
    		}
    	}
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'image_list'=>$image_list,
    			'html'=>$this->getPluploaderPlugin($this->get_session_key()),
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    	
    	
    }
    
    function compile_uploadify_element($item_array){
    	$parameters=$item_array['parameters'];
    	$_count=0;
    	$image_list=$this->getImageListAdmin($item_array['action'], $item_array['table_name'], $item_array['primary_key'], $item_array['primary_key_value'],$_count);
    	
    	if($this->getConfigValue('photo_per_data')>0 AND $item_array['action']=='data'){
    		if($_count>=$this->getConfigValue('photo_per_data')){
    			return array(
	    			'title'=>$item_array['title'],
	    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    				'image_list'=>$image_list,
	    			'html'=>'',
	    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    			);
    		}
    	}
    	return array(
			'title'=>$item_array['title'],
			'required'=>($item_array['required'] == "on" ? 1 : 0),
			'image_list'=>$image_list,
			'html'=>$this->getUploadifyPlugin($this->get_session_key(), $parameters),
			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
		);
    	
    	
    }
    
    function compile_uploadify_file_element ( $item_array ) {
    	$image_list=$this->getFileListAdmin($item_array['action'], $item_array['table_name'], $item_array['primary_key'], $item_array['primary_key_value']);
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'image_list'=>$image_list,
    			'html'=>$this->getUploadifyFilePlugin($this->get_session_key()),
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    
    }
    
    function compile_pluploader_file_element ( $item_array ) {
    	$image_list=$this->getFileListAdmin($item_array['action'], $item_array['table_name'], $item_array['primary_key'], $item_array['primary_key_value']);
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'image_list'=>$image_list,
    			'html'=>$this->getPluploaderPlugin($this->get_session_key()),
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    }
    
    function compile_form_elements($form_data, $ignore_tabs=false){
    	
    	$elements=array();
    	$scripts=array();
    	$default_tab_name=$this->getConfigValue('default_tab_name');
    	$tabs=array();
    	$tabs[$default_tab_name]=$default_tab_name;
    	
    	
    	foreach ( $form_data as $item_id => $item_array ) {
    		if ( !isset($item_array['name']) ) {
    			continue;
    		}
    		 
    		switch ( $item_array['type'] ) {
    			case 'gadres':
    				$rs = $this->compile_gadres_element($item_array);
    				break;
    			case 'destination':
    				$rs = $this->compile_destination_element($item_array);
    				break;
    			case 'uploads':
    				$rs = $this->compile_uploads_element($item_array);
    				break;
    			case 'docuploads':
    				$rs = $this->compile_docuploads_element($item_array);
    				break;
    			case 'tlocation':
    				$rs = $this->compile_tlocation_element($item_array);
    				break;
    			case 'parameter':
    				$rs = $this->compile_parameter_element($item_array);
    				break;
    			case 'price':
    				$rs = $this->compile_price_element($item_array);
    				break;
    			case 'select_box':
    				$rs = $this->compile_selectbox_element($item_array);
    				break;
    			case 'attachment':
    				$rs = $this->compile_attachment_element($item_array);
    				break;
    			case 'geodata':
    				$rs = $this->compile_geodata_element($item_array);
    				break;
    	
    			case 'email':
    				$rs = $this->compile_email_element($item_array);
    				break;
    	
    			case 'mobilephone':
    				$rs = $this->compile_mobilephone_element($item_array);
    				break;
    	
    			case 'select_by_query':
    				$rs = $this->compile_select_box_by_query_element($item_array, $form_data);
    				break;
    	
    			case 'select_by_query_multiple':
    				$rs = $this->compie_select_box_by_query_multiple_element($item_array);
    				break;
    	
    			case 'select_box_structure':
    				$rs = $this->compile_select_box_structure_element($item_array);
    				break;
    	
    			case 'select_box_structure_simple_multiple':
    				$rs = $this->compile_select_box_structure_simple_multiple_element($item_array);
    				break;
    			
    			case 'select_box_structure_multiple_checkbox':
    				$rs = $this->compile_select_box_structure_multiple_checkbox($item_array);
    			break;
    				
    	
    			case 'shop_select_box_structure':
    				$rs = $this->get_shop_select_box_structure_row($item_array);
    				break;
    	
    			case 'service_type_select_box_structure':
    				{
    					$rs = $this->get_service_type_select_box_structure_row($item_array);
    				}
    				break;
    				/*
    				 case 'uploader':
    				$rs .= $this->get_uploader_row($item_array);
    				break;
    	
    				case 'pluploader':
    				$rs .= $this->get_pluploader_row($item_array);
    				break;
    				*/
    			case 'uploadify_image':
    				switch($this->getConfigValue('uploader_type')){
    					case 'pluploader' : {
    						$rs = $this->compile_pluploader_element($item_array);
    						break;
    					}
    					default : {
    						$rs = $this->compile_uploadify_element($item_array);
    					}
    				}
    	
    				break;
    	
    			case 'uploadify_file':
    				switch($this->getConfigValue('uploader_type')){
    					case 'pluploader' : {
    						//$rs = $this->compile_pluploader_element($item_array);
    						$rs = $this->compile_pluploader_file_element($item_array);
    						break;
    					}
    					default : {
    						$rs = $this->compile_uploadify_file_element($item_array);
    					}
    				}
    				//$rs = $this->get_uploadify_file_row($item_array);
    				break;
    	
    			case 'separator':
    				$rs = $this->get_separator_row($item_array);
    				break;
    	
    			case 'checkbox':
    				$rs = $this->compile_checkbox_element($item_array);
    				break;
    	
    			case 'textarea':
    				$rs = $this->compile_textarea_element($item_array);
    				break;
    	
    			case 'textarea_editor':
    				$rs = $this->compile_textarea_editor_element($item_array);
    				break;
    	
    			case 'grade':
    				$rs = $this->compile_grade_element($item_array);
    				break;
    	
    			case 'date':
    				//$rs = $this->get_date_input($item_array);
    				$rs = $this->compile_date_element($item_array);
    				break;
    				
				case 'datetime':
    				$rs = $this->compile_datetime_element($item_array);
					break;
				case 'dtdatetime':
					$rs = $this->compile_dtdatetime_element($item_array);
					break;
				case 'dtdate':
					$rs = $this->compile_dtdate_element($item_array);
					break;
				case 'dttime':
					$rs = $this->compile_dttime_element($item_array);
					break;
    	
    			case 'auto_add_value':
    				$rs = $this->compile_safe_string_element($item_array);
    				break;
    	
    			case 'safe_string':
    				$rs = $this->compile_safe_string_element($item_array);
    				break;
    	
    			case 'password':
    				//$rs = $this->get_password_input($item_array);
    				$rs = $this->compile_password_element($item_array);
    				break;
    	
    			case 'photo':
    				$rs = $this->compile_photo_element($item_array);
    				break;
    	
    			case 'captcha':
    				$rs = $this->compile_captcha_element($item_array);
    				break;
    	
    			case 'spacer_text':
    				$rs = $this->compile_spacer_text_element($item_array);
    				break;
    	
    			case 'hidden':
    				$rs = $this->compile_hidden_element($item_array);
    				break;
    				
    			case 'primary_key':
    					$rs = $this->compile_primary_key_element($item_array);
    					break;
    	
    			case 'values_list':
    				$rs = $this->get_safe_text_input($item_array);
    				break;
    				
    			case 'structure':
    				$rs = $this->compile_structure_element($item_array);
    				break;
    			default:
    				$rs = FALSE;
    				break;
    		}
    		
    		if($rs===FALSE){
    			
    		}elseif(is_object($rs)){
    			if(isset($rs->collection) && count($rs->collection)!=0){
    				
    				foreach($rs->collection as $collection_element){
    					$ce=$collection_element;
    					//$ce['hint']='';
    					$ce['type']=$item_array['type'];
    					//$ce['name']=$item_array['name'];
    					$ce['active_in_topic']=$item_array['active_in_topic'];
    					if($item_array['type']=='hidden' || $item_array['type']=='primary_key'){
    						$elements['private'][$ce['name']]=$ce;
    					}else{
    						if($ce['tab']==''){
    							$ce['tab']=$default_tab_name;
    						}
    						if($ignore_tabs){
    							$elements['public'][$default_tab_name][$ce['name']]=$ce;
    						}else{
    							$elements['public'][$ce['tab']][$ce['name']]=$ce;
    						}
    							
    					}
    					$elements['hash'][$ce['name']]=$ce;
    				}
    			}
    			//
    			if(isset($rs->scripts) && count($rs->scripts)!=0){
    				foreach($rs->scripts as $script_element){
    					$scripts[]=$script_element;
    				}
    			}
    			//print_r($rs);
    		}else{
    			$rs['hint']=(isset($item_array['hint']) ? $item_array['hint'] : '');
    			$rs['name']=$item_array['name'];
    			$rs['active_in_topic']=(isset($item_array['active_in_topic']) ? $item_array['active_in_topic'] : '');
    			$rs['type']=$item_array['type'];
    			if($item_array['type']=='hidden' || $item_array['type']=='primary_key'){
    				$elements['private'][$item_array['name']]=$rs;
    			}else{
    				if($rs['tab']==''){
    					$rs['tab']=$default_tab_name;
    				}
    				if($ignore_tabs){
    					$elements['public'][$default_tab_name][$item_array['name']]=$rs;
    				}else{
    					$elements['public'][$rs['tab']][$item_array['name']]=$rs;
    				}
    					
    			}
    			$elements['hash'][$item_array['name']]=$rs;
    		}
    		
    		
    	}
    	
    	$scripts=array_unique($scripts);
    	$elements['scripts']=implode('', $scripts);
    	$elements['scripts']=$scripts;
    	return $elements;
    }
    
    /**
     * Compile form inputs
     * @param $form_data form data
     * @return string
     */
    function compile_form ( $form_data, $ignore_tabs=false ) {
    	$Sitebill_Registry=Sitebill_Registry::getInstance();
    	
    	 
    	$elements[]=array();
    	$default_tab_name=$this->getConfigValue('default_tab_name');
    	$tabs=array();
    	$tabs[$default_tab_name]=$default_tab_name;
    	
        foreach ( $form_data as $item_id => $item_array ) {
        	$rs='';
        	//echo "type = {$item_array['type']}, name = {$item_array['name']}<br>";
            switch ( $item_array['type'] ) {
            	case 'price':
                    $rs = $this->get_price_input($item_array);
                break;
                case 'tlocation':
                	$rs = $this->get_tlocation($item_array);
                break;
                case 'select_box':
                    $rs = $this->get_select_box_row($item_array);
                break;
                
                case 'email':
                    $rs = $this->get_email_input($item_array);
                break;
                
                case 'mobilephone':
                    $rs = $this->get_mobilephone_input($item_array);
                break;
                
                case 'select_by_query':
                    $rs = $this->get_select_box_by_query_row($item_array);
                break;
                
                case 'select_by_query_multiple':
                    $rs = $this->get_select_box_by_query_multiple_row($item_array);
                break;
                
                case 'select_box_structure':
                    $rs = $this->get_select_box_structure_row($item_array);
                break;
                
                case 'structure':
                	$rs = $this->get_structure_row($item_array);
                break;
                
                case 'select_box_structure_simple_multiple':
                    $rs = $this->get_select_box_structure_simple_multiple_row($item_array);
                break;
                
                case 'shop_select_box_structure':
                    $rs = $this->get_shop_select_box_structure_row($item_array);
                break;
                
                case 'service_type_select_box_structure':
                	{
                	$rs = $this->get_service_type_select_box_structure_row($item_array);
                	}
                break;
                /*
                case 'uploader':
                    $rs .= $this->get_uploader_row($item_array);
                break;
                
                case 'pluploader':
                    $rs .= $this->get_pluploader_row($item_array);
                break;
                */
                case 'uploadify_image':
                	switch($this->getConfigValue('uploader_type')){
                		case 'pluploader' : {
                			$rs = $this->get_pluploader_row($item_array);
                			break;
                		}
                		default : {
                			$rs = $this->get_uploadify_row($item_array);
                		}
                	}
                    
                break;
                
                case 'uploadify_file':
                    $rs = $this->get_uploadify_file_row($item_array);
                break;
                
                case 'separator':
                    $rs = $this->get_separator_row($item_array);
                break;
                
                case 'checkbox':
                    $rs = $this->get_checkbox_box_row($item_array);
                break;
                
                case 'textarea':
                    $rs = $this->get_textarea_row($item_array);
                break;
                
                case 'textarea_editor':
                    $rs = $this->get_textarea_editor_row($item_array);
                break;
                
                case 'grade':
                    $rs = $this->get_grade_row($item_array);
                break;

                case 'date':
                    $rs = $this->get_date_input($item_array);
                break;
                
                case 'auto_add_value':
                    $rs = $this->get_safe_text_input($item_array);
                break;
                
                case 'safe_string':
                    $rs = $this->get_safe_text_input($item_array);
                break;
                
                case 'geodata':
                	$rs = $this->get_geodata_input($item_array);
                break;

                case 'password':
                    $rs = $this->get_password_input($item_array);
                break;
                
                case 'photo':
                    $rs = $this->get_photo_input($item_array);
                break;
                
                case 'captcha':
                    $rs = $this->get_captcha_input($item_array);
                break;
                
                case 'spacer_text':
                    $rs = $this->get_spacer_text($item_array);
                break;
                
                case 'hidden':
                    $rs = $this->get_hidden_input($item_array);
                break;
                
                case 'values_list':
                    $rs = $this->get_safe_text_input($item_array);
                break;
            }
            
            
           // echo $default_tab_name;
           
            
            if(isset($item_array['tab']) && $item_array['tab']!=''){
            	$tabs[$item_array['tab']]=$item_array['tab'];
            	if($rs!=''){
            		$elements[$item_array['tab']][]=$rs;
            	}
            }else{
            	if($rs!=''){
            		$elements[$default_tab_name][]=$rs;
            	}
            }
            
        }
        $rt='';
        
        if($Sitebill_Registry->getFeedback('divide_step_form')){
        	$tabs_count=count($tabs);
        	$current_step=$Sitebill_Registry->getFeedback('step');
        	$Sitebill_Registry->addFeedback('steps',$tabs_count);
        	if($tabs_count>1){
        		$tabs_names=array_keys($tabs);
        	}
        	$tabs_names=array_keys($tabs);
        	        	
        	$rt.='<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/system/js/form_tabs.js"></script>';
        	if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/css/form_tabs.css') ) {
        		$rt.='<link rel="stylesheet" type="text/css" href="'.SITEBILL_MAIN_URL.'/template/frontend/'.$this->getConfigValue('theme').'/css/form_tabs.css" />';
        	} else {
        		$rt.='<link rel="stylesheet" type="text/css" href="'.SITEBILL_MAIN_URL.'/apps/system/css/form_tabs.css" />';
        	}
        	
        	$rt.='<tbody id="form_tab_switcher" style="display:none;">';
        	$rt.='<tr colspan="2"><td>';
        	$ti=1;
        	
        	foreach($tabs as $tab){
        		if($ti>$current_step){
        			$rt.='<span>'.$tab.'</span>';
        		}elseif($ti==$current_step){
        			$rt.='<a href="'.md5($tab).'" class="active_tab">'.$tab.'</a>';
        		}else{
        			$rt.='<a href="'.md5($tab).'">'.$tab.'</a>';
        		}
        		
        		$ti++;
        	}
        	$rt.='</td></tr></tbody>';
        	
        	$ti=1;
        	foreach($tabs as $tab){
        		if($ti>$tabs_count){
        			break;
        		}
        		if($ti==$current_step){
        			$rt.='<tbody class="form_tab" id="'.md5($tab).'">';
        			$rt.='<tr colspan="2"><td>'.$tab.'</td></tr>';
        			if(count($elements[$tab])>0){
        				foreach($elements[$tab] as $el){
        					$rt.=$el;
        				}
        			}
        			$rt.='</tbody>';
        		}else{
        			$rt.='<tbody class="form_tab">';
        			$rt.='<tr colspan="2"><td>'.$tab.'</td></tr>';
        			if(count($elements[$tab])>0){
        				foreach($elements[$tab] as $el){
        					$rt.=$el;
        				}
        			}
        			$rt.='</tbody>';
        		}
        		
        		
        		$ti++;
        	}
        }elseif(count($tabs)>1 && !$ignore_tabs){
        	
        	$rt.='<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/system/js/form_tabs.js"></script>';
        	if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/css/form_tabs.css') ) {
        		$rt.='<link rel="stylesheet" type="text/css" href="'.SITEBILL_MAIN_URL.'/template/frontend/'.$this->getConfigValue('theme').'/css/form_tabs.css" />';
        	} else {
        		$rt.='<link rel="stylesheet" type="text/css" href="'.SITEBILL_MAIN_URL.'/apps/system/css/form_tabs.css" />';
        	}
        	$rt.='<tbody id="form_tab_switcher">';
        	$rt.='<tr colspan="2"><td>';
        	foreach($tabs as $tab){
        		$rt.='<a href="'.md5($tab).'">'.$tab.'</a>';
        	}
        	$rt.='</td></tr></tbody>';
        	
        	foreach($tabs as $tab){
	        	$rt.='<tbody class="form_tab" id="'.md5($tab).'">';
	        	$rt.='<tr colspan="2"><td>'.$tab.'</td></tr>';
	        	if(count($elements[$tab])>0){
	        		foreach($elements[$tab] as $el){
	        			//echo $el;
	        			$rt.=$el;
	        		}
	        	}
	        	$rt.='</tbody>';
	        }
        }elseif(count($tabs)>1){
        	foreach($tabs as $tab){
        		if(count($elements[$tab])>0){
        			foreach($elements[$tab] as $el){
        				$rt.=$el;
        			}
        		}
        	}
        }else{
        	if(count($elements[$default_tab_name])>0){
        		foreach($elements[$default_tab_name] as $el){
        			$rt.=$el;
        		}
        	}
        	
        }
        return $rt;
        //return $rs;
    }
    
    /**
     * Get spacer text
     * @param array $item_array
     * @return string
     */
    function get_spacer_text ( $item_array ) {
        //echo 'spacer!<br>';
        $string .= "<tr>\n";
		$string .= '<td>';
        $string .= $item_array['title'];
        $string .= '</td>';
        $string .= "<td colspan=\"2\">".$item_array['value']."</td>\n";
        $string .= "</tr>\n";
        //echo $item_array['value'].'<br>';
        //echo $string;

        /*Return html code*/
        return $string;
    }
    
    
    /**
     * Get error message row
     * @param string $error_message
     * @return string
     */
    function get_error_message_row ( $error_message ) {
        $rs = '<tr>';
        $rs .= '<td colspan="2">';
        $rs .= '<span class="error"><div class="alert alert-error">'.$error_message.'</div></span>';
        $rs .= '</td>';
        $rs .= '</tr>';
        
        return $rs;
    }
    
    /**
     * Get select box row
     * @param array $item_array
     * @return string
     */
    function get_select_box_by_query_row ( $item_array ) {
        $rs = '<tr  class="row3"  alt="'.$item_array['name'].'">';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        if ( $item_array['required'] == "on" ) {
            $rs .= " <span style=\"color: red;\">*</span> \n";
        }
        $rs .= '</td>';
        $rs .= '<td>';
        $rs .= $this->get_single_select_box_by_query($item_array);
        $rs .= '</td>';
        $rs .= '</tr>';
        
        return $rs;
    }
    
/**
     * Get select box row
     * @param array $item_array
     * @return string
     */
    function get_select_box_by_query_multiple_row ( $item_array ) {
        $rs = '<tr  class="row3"  alt="'.$item_array['name'].'">';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        if ( $item_array['required'] == "on" ) {
            $rs .= " <span style=\"color: red;\">*</span> \n";
        }
        $rs .= '</td>';
        $rs .= '<td>';
        $rs .= $this->get_single_select_box_by_query_multiple($item_array);
        $rs .= '</td>';
        $rs .= '</tr>';
        
        return $rs;
    }
    
    /**
     * Get total in select
     * @param string $key
     * @return int
     */
    function get_total_in_select ( $key ) {
        return $this->total_in_select[$key];
    }
    
    /**
     * Get single select box by query
     * @param array $item_array
     * @return string
     */
    function get_single_select_box_by_query ( $item_array, $model=null ) {
    	
    	/*$links=array(
    		'country_id'=>array(
    			array('linked_element'=>'region_id', 'linked_field'=>'country_id'),
    		),
    		'region_id'=>array(
    			array('linked_element'=>'city_id', 'linked_field'=>'region_id'),
    		),
    		'city_id'=>array(
    			array('linked_element'=>'district_id', 'linked_field'=>'city_id'),
    			array('linked_element'=>'street_id', 'linked_field'=>'city_id'),
    		)
    	);*/
    	
    	if(isset($item_array['parameters'])){
    		$parameters=$item_array['parameters'];
    	}else{
    		$parameters=array();
    	}
    	
    	
    	/*
    	$value_name_parts=array();
    	if(isset($parameters['query_name_parts']) && $parameters['query_name_parts']!=''){
    		$value_name_parts=explode(',', $parameters['query_name_parts']);
    		foreach($value_name_parts as $k=>$v){
    			if(trim($v)==''){
    				unset($value_name_parts[$k]);
    			}else{
    				$value_name_parts[$k]='`'.trim($v).'`';
    			}
    		}
    	}
    	
    	if(count($value_name_parts)>0){
    		$vname='CONCAT_WS(\''.$parameters['query_name_parts_separator'].'\', '.implode(',', $value_name_parts).') AS `'.$parameters['query_name'].'`';
    	}
    	
    	if(isset($parameters['query_order']) && $parameters['query_order']!=''){
    		$p_order='`'.trim($parameters['query_order']).'`'.' '.trim($parameters['query_order_direct']);
    	}
    	
    	$q='SELECT '.trim($parameters['query_key']).', '.$vname.' FROM '.DB_PREFIX.'_'.$item_array['primary_key_table'].' ORDER BY '.$p_order.'<br>';
    	*/
    	
    	if(isset($parameters['linked']) && $parameters['linked']!=''){
    		$linked_elts_str=explode(';', $parameters['linked']);
    	}
    	
    	$links=array();
    	if(!empty($linked_elts_str)){
    		foreach ($linked_elts_str as $str){
    			$x=explode(',', $str);
    			$links[]=array(
    				'linked_element'=>trim($x[0]),
    				'linked_field'=>trim($x[1])
    			);
    		}
    	}
    	$depended_element_name='';
    	if(isset($parameters['depended']) && $parameters['depended']!=''){
    		$depended_element_name=trim($parameters['depended']);
    	}
    	$rs='';
    	
    	if(isset($parameters['autocomplete']) && $parameters['autocomplete']==1){
    		$value='';
    		if($item_array['value']!=''){
    			$DBC=DBC::getInstance();
    			$query='SELECT `'.$item_array['value_name'].'` FROM '.DB_PREFIX.'_'.$item_array['primary_key_table'].' WHERE `'.$item_array['primary_key_name'].'`=?';
    			$stmt=$DBC->query($query, array($item_array['value']));
    			if($stmt){
    				$ar=$DBC->fetch($stmt);
    				$value=$ar[$item_array['value_name']];
    			}
    		}
    		return '<div class="geoautocomplete_block"><input type="text" class="geoautocomplete" name="geoautocomplete['.$item_array['name'].']" value="'.$value.'" pk="'.$item_array['primary_key_name'].'" from="'.$item_array['primary_key_table'].'" /><input type="hidden" name="'.$item_array['name'].'" value="'.$item_array['value'].'" /></div>';
    	}elseif(1==$this->getConfigValue('apps.realty.off_system_ajax')){
    		$selected='';
    		$onchange=array();
    		if(count($links)>0){
    			foreach($links as $lnks){
    				$onchange[]='LinkedElements.refresh(this, \''.$lnks['linked_element'].'\', \''.$lnks['linked_field'].'\');';
    			}
    		}
    		$this->total_in_select[$item_array['name']] = 0;
    		$rs .= '<select name="'.$item_array['name'].'" id="'.$item_array['name'].'" onchange="'.implode(' ', $onchange).' '.(isset($item_array['onchange']) ? $item_array['onchange'] : '').'"'.(isset($item_array['onclick']) ? ' onClick="'.$item_array['onclick'].'"' : ' ').'>';
    		if ( $_SESSION['_lang'] != 'ru' ) {
    			$lang_key = 'title_default_'.$_SESSION['_lang'];
    			if ( $item_array[$lang_key] == '' ) {
    				$item_array['title_default'] = 'select item';
    			}
    		}
    		$rs .= '<option value="'.$item_array['value_default'].'" '.$selected.'>'.$item_array['title_default'].'</option>';
    		//print_r($item_array);
    		$DBC=DBC::getInstance();
    		if($depended_element_name!=''){
    			$depended_value=$model[$depended_element_name]['value'];
    			if((int)$depended_value!=0){
    				$query='SELECT `'.$item_array['primary_key_name'].'`, `'.$item_array['value_name'].'` FROM '.DB_PREFIX.'_'.$item_array['primary_key_table'].' WHERE `'.$depended_element_name.'`=?';
    				$stmt=$DBC->query($query, array((int)$depended_value));
    			}else{
    				$query='SELECT `'.$item_array['primary_key_name'].'`, `'.$item_array['value_name'].'` FROM '.DB_PREFIX.'_'.$item_array['primary_key_table'].' WHERE 1=0';
    				$stmt=$DBC->query($query);
    			}
    		}else{
    			$query=$item_array['query'];
    			$stmt=$DBC->query($query);
    		}
    		
    		if($stmt){
    			while ($ar=$DBC->fetch($stmt)){
    				$this->total_in_select[$item_array['name']]++;
    				$value = $ar[$item_array['value_name']];
    				$value = trim($value);
    				//$value = htmlspecialchars_decode($value);
    				$value = htmlspecialchars($value, ENT_QUOTES, SITE_ENCODING);
    				if ( $ar[$item_array['primary_key_name']] ==  $item_array['value'] ) {
    					$selected = "selected";
    				} else {
    					$selected = "";
    				}
    				$rs .= '<option value="'.$ar[$item_array['primary_key_name']].'" '.$selected.'>'.$value.'</option>';
    			}
    		}
    		/*
    		$this->db->exec($item_array['query']);
    		while ( $this->db->fetch_assoc() ) {
    			$this->total_in_select[$item_array['name']]++;
    			$value = $this->db->row[$item_array['value_name']];
    			$value = trim($value);
    			//$value = htmlspecialchars_decode($value);
    			$value = htmlspecialchars($value, ENT_QUOTES, SITE_ENCODING);
    			if ( $this->db->row[$item_array['primary_key_name']] ==  $item_array['value'] ) {
    				$selected = "selected";
    			} else {
    				$selected = "";
    			}
    			$rs .= '<option value="'.$this->db->row[$item_array['primary_key_name']].'" '.$selected.'>'.$value.'</option>';
    		}*/
    		$rs .= '</select>';
    		
    		
    		
    		return $rs;
    	}else{
    	
    		$combo=false;
    		if(isset($item_array['combo']) && $item_array['combo']==1 && 1==$this->getConfigValue('use_combobox')){
    			$combo=true;
    			$tmp=$this->getRequestValue('tmp');
    			//$ajax_
    			if(isset($item_array['ajax_options']) && count($item_array['ajax_options'])>0){
    				$d=json_encode($item_array['ajax_options']);
    			}else{
    				$d=json_encode(array());
    			}
    			$rs.='<script type="text/javascript">
    			$(document).ready(function(){
					$("select[id='.$item_array['name'].']").mycombobox({tmp_val:\''.$tmp[$item_array['name']].'\',ajax_options:'.$d.'});
				});
    			</script>';
    		}
    		 
    		$this->total_in_select[$item_array['name']] = 0;
    		$rs .= '<div id="'.$item_array['name'].'_div">';
    		
    		//$uniq_class='formelement-'.$item_array['name'].'_'.md5(time().rand(100, 999));
    		//$linksdata=array();
    		$onchange=array();
    		if(isset($item_array['onchange'])){
    			$onchange[]=$item_array['onchange'];
    		}
    		if(isset($parameters['onchange']) && $parameters['onchange']!=''){
    			$onchange[]=$parameters['onchange'];
    		}
    		
    		$rs .= '<select name="'.$item_array['name'].'" id="'.$item_array['name'].'"'.(!empty($onchange) ? ' onchange="'.implode('', $onchange).'"' : '').(isset($item_array['onclick']) ? ' onClick="'.$item_array['onclick'].'"' : '').'>';
    		if ( $_SESSION['_lang'] != 'ru' ) {
    			$lang_key = 'title_default_'.$_SESSION['_lang']; 
    			if ( $item_array[$lang_key] == '' ) {
    				$item_array['title_default'] = 'select item';
    			}
    		}
    		$rs .= '<option value="'.$item_array['value_default'].'">'.$item_array['title_default'].'</option>';
    		$DBC=DBC::getInstance();
    		$query=$item_array['query'];
    		$stmt=$DBC->query($query);
    		if($stmt){
    			while ( $ar=$DBC->fetch($stmt) ) {
    				$this->total_in_select[$item_array['name']]++;
    				$value = $ar[$item_array['value_name']];
    				$value = trim($value);
    				//$value = htmlspecialchars_decode($value);
    				$value = htmlspecialchars($value, ENT_QUOTES, SITE_ENCODING);
    				if ( $ar[$item_array['primary_key_name']] ==  $item_array['value'] ) {
    					$selected = "selected";
    				} else {
    					$selected = "";
    				}
    				$rs .= '<option value="'.$ar[$item_array['primary_key_name']].'" '.$selected.'>'.$value.'</option>';
    			}
    		}
    		
    		$rs .= '</select>';
    		$rs .= '</div>';
    		
    		return $rs;
    	}
    	
    	
    }
    
   
    
/**
     * Get single select box by query
     * @param array $item_array
     * @return string
     */
    function get_single_select_box_by_query_multiple ( $item_array ) {
    	$values=(array)$item_array['values_array'];
    	
        $this->total_in_select[$item_array['name']] = 0;
        $rs .= '<div id="'.$item_array['name'].'_div">';
        $rs .= '<select name="'.$item_array['name'].'[]" id="'.$item_array['name'].'" onchange="'.$item_array['onchange'].'" multiple="multiple">';
        $DBC=DBC::getInstance();
        $query=$item_array['query'];
        $stmt=$DBC->query($query);
       
        if($stmt){
        	while ( $ar=$DBC->fetch($stmt) ) {
        		$this->total_in_select[$item_array['name']]++;
        		$value = $ar[$item_array['value_name']];
        		$value = trim($value);
        		//$value = htmlspecialchars_decode($value);
        		$value = htmlspecialchars($value, ENT_QUOTES, SITE_ENCODING);
        		$selected = '';
        		if ( is_array($values) ) {
        			if ( in_array($ar[$item_array['primary_key_name']], $values)) {
        				$selected = "selected";
        			}
        		}
        		$rs .= '<option value="'.$ar[$item_array['primary_key_name']].'" '.$selected.'>'.$value.'</option>';
        	}
        }
        
        $rs .= '</select>';
        $rs .= '</div>';
        
        return $rs;
    }
    
    /**
     * Get select box row
     * @param array $item_array
     * @return string
     */
    function get_select_box_row ( $item_array ) {
        $rs = '<tr class="row3"  alt="'.$item_array['name'].'">';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        if ( $item_array['required'] == "on" ) {
            $rs .= " <span style=\"color: red;\">*</span> \n";
        }
        $rs .= ((isset($item_array['hint']) && $item_array['hint']!='') ? ' <span class="hint">('.$item_array['hint'].')</span>' : '').'</td>';
        $rs .= '<td>';
        $rs .= $this->get_select_box($item_array);
        $rs .= '</td>';
        $rs .= '</tr>';
        
        return $rs;
    }
    
 	/**
     * Get uploader row
     * @param array $item_array
     * @return string
     */
    function get_uploader_row ( $item_array ) {
        $rs .= '<tr  alt="'.$item_array['name'].'">';
        $rs .= '<td colspan="2">';
        $rs .= '<h2>'.Multilanguage::_('L_PHOTO_1').'</h2>';
        
        
        
        $rs .= '</td>';
        $rs .= '</tr>';
        
        $rs .= '<tr>';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        if ( $item_array['required'] == "on" ) {
            $rs .= " <span style=\"color: red;\">*</span> \n";
        }
        $rs .= '</td>';
        $rs .= '<td>';
        $rs .= $this->getUploaderPlugin($this->get_session_key());
        $rs .= '</td>';
        $rs .= '</tr>';
        
        //echo $rs;
        //exit;
        
        return $rs;
    }
    
/**
     * Get uploader row
     * @param array $item_array
     * @return string
     */
    function get_pluploader_row ( $item_array ) {
        $rs .= '<tr  class="row3">';
        $rs .= '<td colspan="2">';
        $rs .= '<h2>'.Multilanguage::_('L_PHOTO_1').'</h2>';
        
        $rs .= $this->getImageListAdmin($item_array['action'], $item_array['table_name'], $item_array['primary_key'], $item_array['primary_key_value'],$_count);
        
        $rs .= '</td>';
        $rs .= '</tr>';
        
        $rs .= '<tr  class="row3"  alt="'.$item_array['name'].'">';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        if ( $item_array['required'] == "on" ) {
            $rs .= " <span style=\"color: red;\">*</span> \n";
        }
        $rs .= '</td>';
        $rs .= '<td>';
        //$rs .= $this->getPP($this->get_session_key());
        $rs .= $this->getPluploaderPlugin($this->get_session_key());
        $rs .= '</td>';
        $rs .= '</tr>';
        
        //echo $rs;
        //exit;
        
        return $rs;
    }
    
    /**
     * Get uploadify row
     * @param array $item_array
     * @return string
     */
    function get_uploadify_row ( $item_array ) {
        $rs .= '<tr  class="row3">';
        $rs .= '<td colspan="2">';
        $rs .= '<h2>'.Multilanguage::_('L_PHOTO_1').'</h2>';
        
        //$action, $table_name, $key, $record_id
        $_count=0;
        $rs .= $this->getImageListAdmin($item_array['action'], $item_array['table_name'], $item_array['primary_key'], $item_array['primary_key_value'],$_count);
        
        $rs .= '</td>';
        $rs .= '</tr>';
        if($this->getConfigValue('photo_per_data')>0 AND $item_array['action']=='data'){
        	if($_count>=$this->getConfigValue('photo_per_data')){
        		return $rs;
        	}
        }
        $rs .= '<tr  class="row3"  alt="'.$item_array['name'].'">';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        if ( $item_array['required'] == "on" ) {
            $rs .= " <span style=\"color: red;\">*</span> \n";
        }
        $rs .= '</td>';
        $rs .= '<td>';
        $rs .= $this->getUploadifyPlugin($this->get_session_key());
        $rs .= '</td>';
        $rs .= '</tr>';
       
        
        //echo $rs;
        //exit;
        
        return $rs;
    }
    
    /**
     * Get uploadify file row
     * @param array $item_array
     * @return string
     */
    function get_uploadify_file_row ( $item_array ) {
        
        $rs .= '<tr  class="row3"  alt="'.$item_array['name'].'">';
        $rs .= '<td colspan="2">';
        $rs .= '<h2>'.Multilanguage::_('L_ATTACH_FILE').'</h2>';
        
        //$action, $table_name, $key, $record_id
        
        $rs .= $this->getFileListAdmin($item_array['action'], $item_array['table_name'], $item_array['primary_key'], $item_array['primary_key_value']);
        $rs .= '</td>';
        $rs .= '</tr>';
        
        $rs .= '<tr  class="row3">';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        if ( $item_array['required'] == "on" ) {
            $rs .= " <span style=\"color: red;\">*</span> \n";
        }
        $rs .= '</td>';
        $rs .= '<td>';
        $rs .= $this->getUploadifyFilePlugin($this->get_session_key());
        $rs .= '</td>';
        $rs .= '</tr>';
        
        //echo $rs;
        //exit;
        
        return $rs;
    }
    
    
    /**
     * Get separator row
     * @param array $item_array
     * @return string
     */
    function get_separator_row ( $item_array ) {
        
        $rs .= '<tr>';
        $rs .= '<td colspan="2">';
        $rs .= '<h2>'.$item_array['title'].'</h2>';
        $rs .= '</td>';
        $rs .= '</tr>';
        
        return $rs;
    }
    
    
    /**
     * Get select box structure row
     * @param array $item_array
     * @return string
     */
    function get_select_box_structure_row ( $item_array ) {
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        
        $rs = '<tr  class="row3"  alt="'.$item_array['name'].'">';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        if ( $item_array['required'] == "on" ) {
            $rs .= " <span style=\"color: red;\">*</span> \n";
        }
        $rs .= '</td>';
        $rs .= '<td>';
        $rs .= $Structure_Manager->getCategorySelectBoxWithName($item_array['name'], $item_array['value'] );
        $rs .= '</td>';
        $rs .= '</tr>';
        
        return $rs;
    }
    
    function get_structure_row ( $item_array ) {
    	
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_implements.php');
    	$SM=Structure_Implements::getManager($item_array['entity']);
    
    	$rs = '<tr  class="row3"  alt="'.$item_array['name'].'">';
    	$rs .= '<td>';
    	$rs .= $item_array['title'];
    	if ( $item_array['required'] == "on" ) {
    		$rs .= " <span style=\"color: red;\">*</span> \n";
    	}
    	$rs .= '</td>';
    	$rs .= '<td>';
    	$rs .= $SM->getCategorySelectBoxWithName($item_array['name'], $item_array['value'] );
    	$rs .= '</td>';
    	$rs .= '</tr>';
    
    	return $rs;
    }
    
	/**
     * Get select box structure row
     * @param array $item_array
     * @return string
     */
    function get_select_box_structure_simple_multiple_row ( $item_array ) {
        if ( !isset($item_array['values_array']) ) {
            $item_array['values_array'] = array(0 => 0);
        }
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        
        $rs = '<tr  class="row3"  alt="'.$item_array['name'].'">';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        if ( $item_array['required'] == "on" ) {
            $rs .= " <span style=\"color: red;\">*</span> \n";
        }
        $rs .= '</td>';
        $rs .= '<td>';
        $rs .= $Structure_Manager->getCategorySelectBoxWithName($item_array['name'], $item_array['values_array'] );
        $rs .= '</td>';
        $rs .= '</tr>';
        
        return $rs;
    }
    
    
    
	function get_shop_select_box_structure_row ( $item_array ) {
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        
        $rs = '<tr  class="row3"  alt="'.$item_array['name'].'">';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        if ( $item_array['required'] == "on" ) {
            $rs .= " <span style=\"color: red;\">*</span> \n";
        }
        $rs .= '</td>';
        $rs .= '<td>';
        $rs .= $Structure_Manager->getShopCategorySelectBoxWithName($item_array['name'], $item_array['value'] );
        $rs .= '</td>';
        $rs .= '</tr>';
        
        return $rs;
    }
    
    /**
     * Get select box for tree table structure type
     * @param array $item_arrayy
     * @return select tag string
     * @author Kris
     */
    function get_service_type_select_box_structure_row ( $item_array) {
    	
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $rs = '<tr  class="row3"  alt="'.$item_array['name'].'">';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        if ( $item_array['required'] == "on" ) {
            $rs .= " <span style=\"color: red;\">*</span> \n";
        }
        $rs .= '</td>';
        $rs .= '<td>';
        $rs .= $Structure_Manager->getServiceTypesTree_selectBox($item_array['name'], $item_array['value'] );
        $rs .= '</td>';
        $rs .= '</tr>';
        
        return $rs;
    }
    
    
	/**
     * Get checkbox box row
     * @param array $item_array
     * @return string
     */
    function get_checkbox_box_row ( $item_array ) {
        $rs = '<tr  class="row3" alt="'.$item_array['name'].'">';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        if ( $item_array['required'] == "on" ) {
            $rs .= " <span style=\"color: red;\">*</span> \n";
        }
        $rs .= ((isset($item_array['hint']) && $item_array['hint']!='') ? ' <span class="hint">('.$item_array['hint'].')</span>' : '').'</td>';
        $rs .= '<td>';
        $rs .= $this->get_checkbox($item_array);
        if ( $item_array['ajax_popup'] != '' ) {
            $rs .= $item_array['ajax_popup'];
        }
        $rs .= '</td>';
        $rs .= '</tr>';
        
        return $rs;
    }
    
    /**
     * Get textarea row
     * @param array $item_array
     * @return string
     */
    function get_textarea_row ( $item_array ) {
        $rs = '<tr  class="row3"  alt="'.$item_array['name'].'">';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        if ( $item_array['required'] == "on" ) {
            $rs .= " <span style=\"color: red;\">*</span> \n";
        }
        
        if ( $item_array['rows'] == '' ) {
            $item_array['rows'] = 10;
        }
        
        if ( $item_array['cols'] == '' ) {
            $item_array['cols'] = 50;
        }
        
        $rs .= '</td>';
        $rs .= '<td>';
        $rs .= '<textarea name="'.$item_array['name'].'" rows="'.$item_array['rows'].'" cols="'.$item_array['cols'].'">'.$item_array['value'].'</textarea>';
        $rs .= '</td>';
        $rs .= '</tr>';
        
        return $rs;
    }
    
	/**
     * Get textarea with editor row
     * @param array $item_array
     * @return string
     */
    function get_textarea_editor_row ( $item_array ) {
    	sleep(1);
    	$id='id'.time().'_'.rand(0,9);
    	$rs='';
    	if(isset($item_array['editor']) AND ($item_array['editor']!=='editor')){
    		if($this->getConfigValue($item_array['editor'])!=''){
    			$editor_code=$this->getConfigValue($item_array['editor']);
    		}else{
    			$editor_code=$this->getConfigValue('editor');
    		}
    	}else{
    		$editor_code=$this->getConfigValue('editor');
    	}
    	if ( $editor_code == 'ckeditor' ) {
		    $rs .= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/ckeditor/ckeditor.js"></script>';
		    $rs .= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/ckeditor/adapters/jquery.js"></script>';
		    $rs .= '<script type="text/javascript">
		    	$(document).ready(function() {
        			$("textarea#'.$id.'").ckeditor({
		filebrowserBrowseUrl : \'/ckfinder/ckfinder.html\',
        filebrowserImageBrowseUrl : \'/ckfinder/ckfinder.html?Type=Images\',
        filebrowserFlashBrowseUrl : \'/ckfinder/ckfinder.html?Type=Flash\',
        filebrowserUploadUrl : \'/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files\',
        filebrowserImageUploadUrl : \'/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images\',
        filebrowserFlashUploadUrl : \'/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash\'	
    				});
				});
    		</script>';
        } elseif( $editor_code == 'bbeditor' ){
        	$rs .= '<link rel="stylesheet" type="text/css" href="'.SITEBILL_MAIN_URL.'/apps/bbcode/site/js/bbeditor/bbeditor.css" />';
        	
        	//$rs .= '<H5>Preview</H5>';
//$rs .= '<DIV id=test style="BORDER-RIGHT: #c0c0c0 1px solid; PADDING-RIGHT: 3px; BORDER-TOP: #c0c0c0 1px solid; PADDING-LEFT: 3px; PADDING-BOTTOM: 3px; BORDER-LEFT: #c0c0c0 1px solid; WIDTH: 500px; PADDING-TOP: 3px; BORDER-BOTTOM: #c0c0c0 1px solid; HEIGHT: 200px"></DIV>';
        	
        	
        	$rs .= '<script src="'.SITEBILL_MAIN_URL.'/apps/bbcode/site/js/bbeditor/jquery.bbcode.js" type="text/javascript"></script>';
			$rs .= '<script type="text/javascript">
			  $(document).ready(function(){
			    $("textarea#'.$id.'").bbcode({tag_bold:true,tag_italic:true,tag_underline:true,tag_link:true,tag_image:true,button_image:false});
			    process();
			  });
			  
			  var bbcode="";
			  function process()
			  {
			    if (bbcode != $("textarea#'.$id.'").val())
			      {
			        bbcode = $("textarea#'.$id.'").val();
			        $.get("'.SITEBILL_MAIN_URL.'/apps/bbcode/site/js/bbeditor/bbParser.php",
			        {
			          bbcode: bbcode
			        },
			        function(txt){
			          $("#test'.$id.'").html(txt);
			        })
			      }
			    setTimeout("process()", 2000);
			  }
			</script>';
			/*
        	$rs .= '<tr>';
	        $rs .= '<td>';
	        $rs .= '</td>';
	        $rs .= '<td>';
	        $rs .= '<DIV id=test'.$id.' style="BORDER-RIGHT: #c0c0c0 1px solid; PADDING-RIGHT: 3px; BORDER-TOP: #c0c0c0 1px solid; PADDING-LEFT: 3px; PADDING-BOTTOM: 3px; BORDER-LEFT: #c0c0c0 1px solid; WIDTH: 400px; PADDING-TOP: 3px; BORDER-BOTTOM: #c0c0c0 1px solid; HEIGHT: 100px; overflow:scroll"></DIV>';
	        $rs .= '</td>';
	        $rs .= '</tr>';
        	*/
        	
        }else {
            $rs .= '<link rel="stylesheet" type="text/css" href="'.SITEBILL_MAIN_URL.'/js/cleditor/jquery.cleditor.css" />
    		<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/js/cleditor/jquery.cleditor.min.js"></script>
    		<script type="text/javascript">
      			$(document).ready(function() {
        			$("textarea#'.$id.'").cleditor();
				});
    		</script>
        	';
        }
        $rs .= '<tr  class="row3"  alt="'.$item_array['name'].'">';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        if ( $item_array['required'] == "on" ) {
            $rs .= " <span style=\"color: red;\">*</span> \n";
        }
        
        if ( $item_array['rows'] == '' ) {
            $item_array['rows'] = 10;
        }
        
        if ( $item_array['cols'] == '' ) {
            $item_array['cols'] = 50;
        }
        
        $rs .= '</td>';
        $rs .= '<td>';
        $rs .= '<textarea id="'.$id.'" class="input" name="'.$item_array['name'].'" rows="'.$item_array['rows'].'" cols="'.$item_array['cols'].'">'.$item_array['value'].'</textarea>';
        $rs .= '</td>';
        $rs .= '</tr>';
        
        return $rs;
    }
    
    /**
     * Get grade row
     * @param array $item_array
     * @return string
     */
    function get_grade_row ( $item_array ) {
        $rs = '<tr  class="row3"  alt="'.$item_array['name'].'">';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        if ( $item_array['required'] == "on" ) {
            $rs .= " <span style=\"color: red;\">*</span> \n";
        }
        $rs .= '</td>';
        $rs .= '<td>';
        
        foreach ( $item_array['grade_values'] as $item_id ) {
            if ( $item_array['value'] == $item_id ) {
                $checked = 'checked';
            } else {
                $checked = '';
            }
            $rs .= '<span>'.$item_id.'</span><input type="radio" name="'.$item_array['name'].'" value="'.$item_id.'" '.$checked.'>&nbsp;&nbsp;&nbsp;';
        }
        $rs .= '</td>';
        $rs .= '</tr>';
        
        return $rs;
    }
    
    
    /**
     * Get check box
     * @param array $item_array
     * @return string
     */
    function get_checkbox ( $item_array ) {
        $rs = '<input type="checkbox" name="'.$item_array['name'].'" value="'.$item_array['value'].'"';
        if ( $item_array['value'] == 1 ) {
            $rs .= ' checked ';
        }
        $rs .= '/>';
        return $rs;
    }
    
    
    /**
     * Get select box
     * @param array $item_array
     * @return string
     */
    function get_select_box ( $item_array ) {
        
    	$parameters=$item_array['parameters'];
    	if(isset($parameters['multiselect']) && 1==(int)$parameters['multiselect']){
    		foreach ( $item_array['select_data'] as $item_id => $item_value ) {
    			$rs .= '<input type="checkbox" name="'.$item_array['name'].'[]" value="'.$item_id.'"'.((isset($item_array['values_array']) && in_array($item_id, $item_array['values_array'])) ? ' checked="checked"' : '').'>'.$item_value.'<br/>';
    		}
    	}else{
    		$rs = '<select name="'.$item_array['name'].'">';
    		if ( !empty($item_array['select_data']) ) {
    			foreach ( $item_array['select_data'] as $item_id => $item_value ) {
    		
    				if($item_id==='__optgroup'){
    					//echo $item_id.'=__optgroup'.'<br />';;
    					$optgroup_content=$item_value;
    					$rs .= '<optgroup label="'.$optgroup_content['name'].'">';
    					if(is_array($optgroup_content['select_data']) && count($optgroup_content['select_data'])>0){
    						foreach($optgroup_content['select_data'] as $ogi=>$ogv){
    							if ( $ogi ==  $item_array['value'] ) {
    								$selected = "selected";
    							} else {
    								$selected = "";
    							}
    							$rs .= '<option value="'.$ogi.'" '.$selected.'>'.$ogv.'</option>';
    						}
    						$rs .= '</optgroup>';
    					}
    					 
    				}else{
    					//echo $item_id.'!=__optgroup'.'<br />';;
    					if ( $item_id ==  $item_array['value'] ) {
    						$selected = "selected";
    					} else {
    						$selected = "";
    					}
    					$rs .= '<option value="'.$item_id.'" '.$selected.'>'.$item_value.'</option>';
    				}
    		
    			}
    		}
    		$rs .= '</select>';
    	}
    	
        
        return $rs;
        
        
    }
    
    /**
     * Get captcha input
     * @param unknown_type $item_array
     * @return string
     */
function get_captcha_input ( $item_array ) {
		$this->clear_captcha_session_table();
        /*Un-quote slashes*/
        $value = stripslashes( $value );
        /*HTML code*/
		
		$captcha_type=$this->getConfigValue('captcha_type');
    	if($captcha_type==2){
    		return FALSE;
    	}elseif($captcha_type==3){
    		
			
			$string .= "<tr  class=\"row3\" alt=\"".$item_array['name']."\">\n";
        
			$captcha_session_key = $this->generateCaptchaSessionKey();
		
			/*Mark required field with simbol '*' */
			$string .= "<td class=\"$bg_color\">".$item_array['title']." <span style=\"color: red;\">*</span> </td>\n";

			$string .= "<td class=\"$bg_color\"><img id=\"capcha_img\" class=\"capcha_img\" src=\"".SITEBILL_MAIN_URL."/third/kcaptcha/index.php?captcha_session_key=".$captcha_session_key."\" width=\"180\" height=\"80\">";
			$string .= '<br /><a href="javascript:void(0);" id="captcha_refresh" class="captcha_refresh">Обновить картинку</a>';
			$string .= "<br><input type=\"text\" name=\"".$item_array['name']."\" value=\"\" size=\"23\" maxlength=\"".$item_array['maxlength']."\" class=\"$css_name\">";
			$string .= '<input type="hidden" name="captcha_session_key" value="'.$captcha_session_key.'"></td>'."\n";
			$string .= "</tr>\n";
			$string .= '<script src="'.SITEBILL_MAIN_URL.'/apps/system/js/md5.js"></script>';
			
			$string .= '<script type="text/javascript">';
			$string .= '$(document).ready(function(){
				$(".captcha_refresh").click(function(){
					var new_key=new Date().getTime();
					var hash = CryptoJS.MD5(String(new_key));
					var parent=$(this).parents("td").eq(0);
					parent.find(".capcha_img").eq(0).attr("src", estate_folder+\'/third/kcaptcha/index.php?captcha_session_key=\' + hash);
					parent.find("input[name=captcha_session_key]").val(hash);
				});
				
			});';
			$string .= '</script>';
			
    	}else{
    		$string .= "<tr  class=\"row3\" alt=\"".$item_array['name']."\">\n";
        
			$captcha_session_key = $this->generateCaptchaSessionKey();
		
			/*Mark required field with simbol '*' */
			$string .= "<td class=\"$bg_color\">".$item_array['title']." <span style=\"color: red;\">*</span> </td>\n";

			$string .= "<td class=\"$bg_color\"><img id=\"capcha_img\" class=\"capcha_img\" src=\"".SITEBILL_MAIN_URL."/captcha.php?captcha_session_key=".$captcha_session_key."\" width=\"180\" height=\"80\">";
			$string .= '<br /><a href="javascript:void(0);" id="captcha_refresh" class="captcha_refresh">Обновить картинку</a>';
			$string .= "<br><input type=\"text\" name=\"".$item_array['name']."\" value=\"\" size=\"23\" maxlength=\"".$item_array['maxlength']."\" class=\"$css_name\"></td>"."\n";
			$string .= '<input type="hidden" name="captcha_session_key" value="'.$captcha_session_key.'">';
			$string .= "</tr>\n";
			$string .= '<script src="'.SITEBILL_MAIN_URL.'/apps/system/js/md5.js"></script>';
			
			$string .= '<script type="text/javascript">';
			$string .= '$(document).ready(function(){
				$(".captcha_refresh").click(function(){
					var new_key=new Date().getTime();
					var hash = CryptoJS.MD5(String(new_key));
					var parent=$(this).parents("td").eq(0);
					parent.find(".capcha_img").eq(0).attr("src", estate_folder+\'/captcha.php?captcha_session_key=\' + hash);
					parent.find("input[name=captcha_session_key]").val(hash);
				});
				/*$("#captcha_refresh").click(function(){
					var new_key=new Date().getTime();
					var hash = CryptoJS.MD5(String(new_key));
					document.getElementById("capcha_img").src = estate_folder+\'/captcha.php?captcha_session_key=\' + hash;
					$("input[name=captcha_session_key]").val(hash);
				});*/
			});';
			$string .= '</script>';

    		
    		
    		
    	}
    	$this->clear_captcha_session_table();
    	
    	
    	
		
        
        /*Return html code*/
        return $string;
    }
    
    /**
     * Generate captcha session key
     * @param void
     * @return string
     */
    function generateCaptchaSessionKey () {
        return md5(time().rand(9999, 4).'random key captcha string core sitebill');
    }

    /**
     * Get date input
     * @param array $item_array
     * @return string
     */
    function get_date_input ( $item_array ) {
        /*Un-quote slashes*/
        $value = stripslashes( $value );
        /*HTML code*/
        $string .= '
    		<script type="text/javascript">
      			$(document).ready(function() {
					$( "#'.$item_array['name'].'" ).datepicker({
						showOn: "button",
						dateFormat: "dd.mm.yy",
						buttonImage: "'.SITEBILL_MAIN_URL.'/img/calendar.gif",
						buttonImageOnly: true
					});        			
      			});
    		</script>
        ';
        $string .= "<tr  class=\"row3\" alt=\"".$item_array['name']."\">\n";

        /*Mark required field with simbol '*' */
        if ( $item_array['required'] == "on" ) {
            $string .= "<td class=\"$bg_color\">".$item_array['title']." <span style=\"color: red;\">*</span> </td>\n";
        } else {
            $string .= "<td class=\"$bg_color\">".$item_array['title']."</td>\n";
        }
       
    	/*if($item_array['value']==='' || $item_array['value']===0){
    		$item_array['value'] = date('d.m.Y', time());
    		//$item_array['value'] = '';
    	}else*/if(preg_match('/(\d{4,4})-(\d{2,2})-(\d{2,2}) (\d{2,2}):(\d{2,2}):(\d{2,2})/',$item_array['value'])){
    		$item_array['value'] = date('d.m.Y', strtotime($item_array['value']));
    	}elseif(preg_match('/(\d{4,4})-(\d{2,2})-(\d{2,2})/',$item_array['value'])){
    		$item_array['value'] = date('d.m.Y', strtotime($item_array['value']));
    	}elseif($item_array['value']==0 || $item_array['value']==''){
    		
    		$item_array['value'] = '';
    	}else{
    		$item_array['value'] = date('d.m.Y', $item_array['value']);
    		
    	}
        
        $string .= "<td class=\"$bg_color\"><input type=\"text\" name=\"".$item_array['name']."\" id=\"".$item_array['name']."\" value=\"".htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING)."\" size=\"10\" maxlength=\"".$item_array['maxlength']."\" class=\"$css_name\"></td>\n";
        $string .= "</tr>\n";

        /*Return html code*/
        return $string;
    }
    
    function compile_date_element($item_array){
    	$value=htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING);
    	$string .= '
    		<script type="text/javascript">
      			$(document).ready(function() {
					$( "#'.$item_array['name'].'" ).datepicker({
						showOn: "button",
						dateFormat: "dd.mm.yy",
						buttonImage: "'.SITEBILL_MAIN_URL.'/img/calendar.gif",
						buttonImageOnly: true
					});
      			});
    		</script>
        ';
    	//echo $item_array['value'];
    	/*if($item_array['value']==='' || $item_array['value']===0){
    		$item_array['value'] = date('d.m.Y', time());
    		//$item_array['value'] = '';
    	}else*/if(preg_match('/(\d{4,4})-(\d{2,2})-(\d{2,2}) (\d{2,2}):(\d{2,2}):(\d{2,2})/',$item_array['value'])){
    		$item_array['value'] = date('d.m.Y', strtotime($item_array['value']));
    	}elseif(preg_match('/(\d{4,4})-(\d{2,2})-(\d{2,2})/',$item_array['value'])){
    		$item_array['value'] = date('d.m.Y', strtotime($item_array['value']));
    	}elseif($item_array['value']==0 || $item_array['value']==''){
    		
    		$item_array['value'] = '';
    	}else{
    		$item_array['value'] = date('d.m.Y', $item_array['value']);
    		
    	}
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'html'=>$string.'<input type="text" id="'.$item_array['name'].'" name="'.$item_array['name'].'" value="'.$item_array['value'].'" />',
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    }
    
    function compile_dtdatetime_element($item_array){
    	$parameters=$item_array['parameters'];

    	$date_formattype=$this->getConfigValue('date_format');
    	 
    	$formattypes=Sitebill_Datetime::getFormats();
    	
    	if($date_formattype!='' && isset($formattypes[$date_formattype])){
    		$date_formattype=$formattypes[$date_formattype];
    	}else{
    		$date_formattype=$formattypes['standart'];
    	}
    	
    	$pickDate='pickDate: true';
    	$pickTime='pickTime: true';
    	if($parameters['noSeconds']==1){
    		$pickSeconds='pickSeconds: false';
    		$format='format: "'.$date_formattype.' hh:mm"';
    	}else{
    		$format='format: "'.$date_formattype.' hh:mm:ss"';
    	}
    	$tpp=$format.', '.$pickDate.', '.$pickTime.', '.$pickSeconds;
    	$value=htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING);
    	
    	if($value!='' && $value!='now'){
    		$value=Sitebill_Datetime::getDatetimeFormattedFromCanonical($value);
    	}elseif($value=='now'){
    		$value=Sitebill_Datetime::getDatetimeFormattedFromCanonical(date('Y-m-d H:i:s', time()));
    	}else{
    		$value='';
    	}
    	
    	$string .= '<link rel="stylesheet" href="'.SITEBILL_MAIN_URL.'/apps/system/js/bootstrap/css/bootstrap-datetimepicker.min.css" media="screen">';
    	$string .= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/system/js/bootstrap/js/bootstrap-datetimepicker.min.js"></script>';
    	$string .= '
    		<script type="text/javascript">
      			$(document).ready(function() {
					$( "#'.$item_array['name'].'" ).datetimepicker({
						pick12HourFormat: false,
				     	language: "ru",
						'.$tpp.'
    
				    });
      			});
    		</script>
        ';
    	 
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'html'=>$string.'<div id="'.$item_array['name'].'" class="input-append date"><input data-format="" type="text" name="'.$item_array['name'].'" value="'.$value.'"></input><span class="add-on"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i></span></div>',
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    }
    
    function compile_dtdate_element($item_array){
    	$parameters=$item_array['parameters'];
    
    	$date_formattype=$this->getConfigValue('date_format');
    	$date_formattype_code=$this->getConfigValue('date_format');
    	
    	$formattypes=Sitebill_Datetime::getFormats();
    	 
    	if($date_formattype!='' && isset($formattypes[$date_formattype])){
    		$date_formattype=$formattypes[$date_formattype];
    	}else{
    		$date_formattype=$formattypes['standart'];
    	}
    	 
    	$pickDate='pickDate: true';
    	$pickTime='pickTime: false';
    	$format='format: "'.$date_formattype.'"';
    	$tpp=$format.', '.$pickDate.', '.$pickTime;
    	$value=htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING);
    	if($value=='' && $item_array['default_value']=='now'){
    		$value=date('Y-m-d H:i:s', time());
    	}
    	$value=Sitebill_Datetime::getDateFormattedFromCanonical($value);
    	$string .= '<link rel="stylesheet" href="'.SITEBILL_MAIN_URL.'/apps/system/js/bootstrap/css/bootstrap-datetimepicker.min.css" media="screen">';
    	$string .= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/system/js/bootstrap/js/bootstrap-datetimepicker.min.js"></script>';
    	$string .= '
    		<script type="text/javascript">
      			$(document).ready(function() {
					$( "#'.$item_array['name'].'" ).datetimepicker({
						autoclose: true,
						pick12HourFormat: false,
				     	language: "ru",
						'.$tpp.'
    
				    });
      			});
    		</script>
        ';
    
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'html'=>$string.'<div id="'.$item_array['name'].'" class="input-append date"><input data-format-code="'.$date_formattype_code.'" data-format="" type="text" name="'.$item_array['name'].'" value="'.$value.'"></input><span class="add-on"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i></span></div>',
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    }
    
    function compile_dttime_element($item_array){
    	$parameters=$item_array['parameters'];
    
    	
    
    	$pickDate='pickDate: false';
    	$pickTime='pickTime: true';
    	if($parameters['noSeconds']==1){
    			$pickSeconds='pickSeconds: false';
    		$format='format: "hh:mm"';
    	}else{
    		$format='format: "hh:mm:ss"';
    	}
    	$tpp=$format.', '.$pickDate.', '.$pickTime.', '.$pickSeconds;
    	//$value=htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING);
    	$value=htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING);
    	if($value=='' && $item_array['default_value']=='now'){
    		$value=date('Y-m-d H:i:s', time());
    	}
    	$value=Sitebill_Datetime::getTimeFormattedFromCanonical($value);
    	$string .= '<link rel="stylesheet" href="'.SITEBILL_MAIN_URL.'/apps/system/js/bootstrap/css/bootstrap-datetimepicker.min.css" media="screen">';
    	$string .= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/system/js/bootstrap/js/bootstrap-datetimepicker.min.js"></script>';
    	$string .= '
    		<script type="text/javascript">
      			$(document).ready(function() {
					$( "#'.$item_array['name'].'" ).datetimepicker({
						pick12HourFormat: false,
				     	language: "ru",
						'.$tpp.'
    
				    });
      			});
    		</script>
        ';
    
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'html'=>$string.'<div id="'.$item_array['name'].'" class="input-append date"><input data-format="" type="text" name="'.$item_array['name'].'" value="'.$value.'"></input><span class="add-on"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i></span></div>',
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    }
    
    function compile_datetime_element($item_array){
    	$parameters=$item_array['parameters'];
    	
    	
    	$formattypes=array(
    			'standart'=>'yyyy-MM-dd',
    			'eu'=>'dd/MM/yyyy',
    			'us'=>'MM/dd/yyyy',
    			);
    	if(isset($parameters['inFormFormat']) && isset($formattypes[$parameters['inFormFormat']])){
    		$date_formattype=$formattypes[$parameters['inFormFormat']];
    	}else{
    		$date_formattype=$formattypes['standart'];
    	}
    	
    	$dformat=(isset($parameters['format']) ? $parameters['format'] : 'DT');
    	
    	if($dformat!='D' && $dformat!='T'){
    		$dformat='DT';
    	}
    	
    	$pickSeconds='pickSeconds: true';
    	$pickDate='pickDate: true';
    	$pickTime='pickTime: true';
    	if($dformat=='D'){
    		$pickDate='pickDate: true';
    		$pickTime='pickTime: false';
    		$format='format: "'.$date_formattype.'"';
    	}elseif($dformat=='T'){
    		$pickDate='pickDate: false';
    		$pickTime='pickTime: true';
    		if($parameters['noSeconds']==1){
    			$pickSeconds='pickSeconds: false';
    			$format='format: "hh:mm"';
    		}else{
    			$format='format: "hh:mm:ss"';
    		}
    	}else{
    		$pickDate='pickDate: true';
    		$pickTime='pickTime: true';
    		if($parameters['noSeconds']==1){
	   			$pickSeconds='pickSeconds: false';
	   			$format='format: "'.$date_formattype.' hh:mm"';
	   		}else{
	   			$format='format: "'.$date_formattype.' hh:mm:ss"';
	   		}
    	}
    	$tpp=$format.', '.$pickDate.', '.$pickTime.', '.$pickSeconds;
    	$value=htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING);
    	$string .= '<link rel="stylesheet" href="'.SITEBILL_MAIN_URL.'/apps/system/js/bootstrap/css/bootstrap-datetimepicker.min.css" media="screen">';
    	$string .= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/system/js/bootstrap/js/bootstrap-datetimepicker.min.js"></script>';
    	$string .= '
    		<script type="text/javascript">
      			$(document).ready(function() {
					$( "#'.$item_array['name'].'" ).datetimepicker({
						pick12HourFormat: false,
				     	language: "ru",
						'.$tpp.'
						
				    });
      			});
    		</script>
        ';
    	
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'html'=>$string.'<div id="'.$item_array['name'].'" class="input-append date"><input data-format="" type="text" name="'.$item_array['name'].'" value="'.$value.'"></input><span class="add-on"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i></span></div>',
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    }
    
    
    /**
     * Get safe string input
     * @param array  $item_array
     * @return string
     */
    function get_safe_text_input ( $item_array ) {

        
        /*HTML code*/
    	$string='';
        $string .= "<tr class=\"row3\" alt=\"".$item_array['name']."\">\n";

        /*Mark required field with simbol '*' */
        if ( $item_array['required'] == "on" ) {
            $string .= "<td>".$item_array['title']." <span style=\"color: red;\">*</span>".((isset($item_array['hint']) && $item_array['hint']!='') ? ' <span class="hint">('.$item_array['hint'].')</span>' : '')."</td>\n";
        } else {
            $string .= "<td>".$item_array['title'].((isset($item_array['hint']) && $item_array['hint']!='') ? ' <span class="hint">('.$item_array['hint'].')</span>' : '')."</td>\n";
        }

        $string .= '<td><input type="text" name="'.$item_array['name'].'" value="'.htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING).'"'.(isset($item_array['length']) ? ' size="'.$item_array['length'].'"' : '').(isset($item_array['maxlength']) ? ' maxlength="'.$item_array['maxlength'].'"' : '').' /></td>'."\n";
        $string .= '</tr>'."\n";

        /*Return html code*/
        return $string;
    }
    
    function get_geodata_input ( $item_array ) {
    
    	/*Un-quote slashes*/
    	$value = stripslashes( $value );
    	/*HTML code*/
    	$string .= "<tr class=\"row3\" alt=\"".$item_array['name']."\">\n";
    	
    	/*$str.='<div id="geodata">';
    	$str.='<input type="text" geodata="lat" name="'.$item_array['name'].'[lat]"  size="'.$item_array['length'].'" maxlength="'.$item_array['maxlength'].'" value="'.$value['lat'].'" />';
    	$str.='<input type="text" geodata="lng" name="'.$item_array['name'].'[lng]"  size="'.$item_array['length'].'" maxlength="'.$item_array['maxlength'].'" value="'.$value['lng'].'" />';
    	$str.='</div>';
    	$str.='<script src="http://crypto-js.googlecode.com/svn/tags/3.1.2/build/rollups/md5.js"></script>';
    	$str.='<script>$(document).ready(function(){$("#geodata").Geodata();});</script>';
    	*/
    
    	/*Mark required field with simbol '*' */
    	if ( $item_array['required'] == "on" ) {
    		$string .= "<td class=\"$bg_color\">".$item_array['title']." <span style=\"color: red;\">*</span>".((isset($item_array['hint']) && $item_array['hint']!='') ? ' <span class="hint">('.$item_array['hint'].')</span>' : '')."</td>\n";
    	} else {
    		$string .= "<td class=\"$bg_color\">".$item_array['title'].((isset($item_array['hint']) && $item_array['hint']!='') ? ' <span class="hint">('.$item_array['hint'].')</span>' : '')."</td>\n";
    	}
    
    	$string .= "<td class=\"$bg_color\">";
    	$string .= '<div id="geodata" coords="'.$this->getConfigValue('apps.geodata.new_map_center').'">';
    	$string .= "Lat: <input type=\"text\" geodata=\"lat\" name=\"".$item_array['name']."[lat]\" value=\"".(isset($item_array['value']['lat']) ? htmlspecialchars($item_array['value']['lat'], ENT_QUOTES, SITE_ENCODING) : '')."\" size=\"".$item_array['length']."\" />";
    	$string .= "Lng: <input type=\"text\" geodata=\"lng\" name=\"".$item_array['name']."[lng]\" value=\"".(isset($item_array['value']['lng']) ? htmlspecialchars($item_array['value']['lng'], ENT_QUOTES, SITE_ENCODING) : '')."\" size=\"".$item_array['length']."\" />";
    	$string .= '</div>';
    	
    	if(1==$this->getConfigValue('apps.geodata.enable')){
    		$string.='<script src="http://crypto-js.googlecode.com/svn/tags/3.1.2/build/rollups/md5.js"></script>';
    		$string.='<script>$(document).ready(function(){$("#geodata").Geodata();});</script>';
    	}
    	
    	
    	$string .= "</td>\n";
    	$string .= "</tr>\n";
    
    	/*Return html code*/
    	return $string;
    }
    
	/**
     * Get safe string input
     * @param array  $item_array
     * @return string
     */
    function get_price_input ( $item_array ) {
    	if($item_array['value']!=''){
    		$value=number_format((int)str_replace(' ', '', $item_array['value']),0,',',' ');
    	}else{
    		$value='';
    	}
    	$id=md5($item_array['name'].'_'.rand(100,999));
    	//$value=(int)$item_array['value'];
    	
		$string .= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/js/autoNumeric-1.7.5.js"></script>';
        /*$string .= '<script type="text/javascript">
		    	$(document).ready(function() {
		    		'.($value==0 ? '$("input.price_field").autoNumeric({aSep: \' \', vMax: \'999999999999\', vMin: \'0\'});' : '$("input.price_field").autoNumericSet('.$value.');').'
        			
        			
				});
    		</script>';*/
         $string .= '<script type="text/javascript">
		    	$(document).ready(function() {
		    		$("input#'.$id.'").autoNumeric({aSep: \' \', vMax: \'999999999999\', vMin: \'0\'});
        			
        			
				});
    		</script>';
        
        //echo $value;
        
        $string .= "<tr class=\"row3\" alt=\"".$item_array['name']."\">\n";

        /*Mark required field with simbol '*' */
        if ( $item_array['required'] == "on" ) {
            $string .= "<td class=\"$bg_color\">".$item_array['title']." <span style=\"color: red;\">*</span> </td>\n";
        } else {
            $string .= "<td class=\"$bg_color\">".$item_array['title']."</td>\n";
        }

        $string .= "<td class=\"$bg_color\"><input type=\"text\" id=\"".$id."\" name=\"".$item_array['name']."\"  size=\"".$item_array['length']."\" maxlength=\"".$item_array['maxlength']."\" class=\"$css_name\" value=\"$value\" /></td>\n";
        $string .= "</tr>\n";

        /*Return html code*/
        return $string;
    }
    
	/**
     * Get safe string input for email
     * @param array  $item_array
     * @return string
     */
    function get_email_input ( $item_array ) {

        /*Un-quote slashes*/
        $value = stripslashes( $value );
        /*HTML code*/
        $string .= "<tr  class=\"row3\" alt=\"".$item_array['name']."\">\n";

        /*Mark required field with simbol '*' */
        if ( $item_array['required'] == "on" ) {
            $string .= "<td class=\"$bg_color\">".$item_array['title']." <span style=\"color: red;\">*</span> </td>\n";
        } else {
            $string .= "<td class=\"$bg_color\">".$item_array['title']."</td>\n";
        }

        $string .= "<td class=\"$bg_color\"><input type=\"text\" name=\"".$item_array['name']."\" value=\"".htmlspecialchars($item_array['value'])."\" size=\"".$item_array['length']."\" maxlength=\"".$item_array['maxlength']."\" class=\"$css_name\"></td>\n";
        $string .= "</tr>\n";

        /*Return html code*/
        return $string;
    }
    
	/**
     * Get safe string input for mobile phone number
     * @param array  $item_array
     * @return string
     */
    function get_mobilephone_input ( $item_array ) {

        /*Un-quote slashes*/
        $value=htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING);
        $id=md5($item_array['name'].'_'.rand(100,999));
        $string = '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/system/js/jquery.maskedinput.min.js"></script>';
        $string .= '<script type="text/javascript">
		    	$(document).ready(function() {
    				$.mask.definitions["h"] = "[0-9]"
		    		$("#'.$id.'").mask("h (hhh) hhh-hh-hh");
      			});
    		</script>';
        
        /*HTML code*/
        $string .= "<tr  class=\"row3\" alt=\"".$item_array['name']."\">\n";

        /*Mark required field with simbol '*' */
        if ( $item_array['required'] == "on" ) {
            $string .= "<td class=\"$bg_color\">".$item_array['title']." <span style=\"color: red;\">*</span> </td>\n";
        } else {
            $string .= "<td class=\"$bg_color\">".$item_array['title']."</td>\n";
        }

        $string .= "<td class=\"$bg_color\"><input id=\"".$id."\" type=\"text\" name=\"".$item_array['name']."\" value=\"".htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING)."\" size=\"".$item_array['length']."\" maxlength=\"".$item_array['maxlength']."\" class=\"$css_name\"></td>\n";
        $string .= "</tr>\n";

        /*Return html code*/
        return $string;
    }
    
    /**
     * Get password input
     * @param array  $item_array
     * @return string
     */
    function get_password_input ( $item_array ) {

        /*Un-quote slashes*/
        $value = stripslashes( $value );
        /*HTML code*/
        $string .= "<tr  class=\"row3\" alt=\"".$item_array['name']."\">\n";

        /*Mark required field with simbol '*' */
        if ( $item_array['required'] == "on" ) {
            $string .= "<td class=\"$bg_color\">".$item_array['title']." <span style=\"color: red;\">*</span> </td>\n";
        } else {
            $string .= "<td class=\"$bg_color\">".$item_array['title']."</td>\n";
        }

        $string .= "<td class=\"$bg_color\"><input type=\"password\" name=\"".$item_array['name']."\" value=\"\" size=\"".$item_array['length']."\" maxlength=\"".$item_array['maxlength']."\" class=\"$css_name\"></td>\n";
        $string .= "</tr>\n";

        /*Return html code*/
        return $string;
    }
    
    
    /**
     * Get photo input
     * @param array $item_array
     * @return string
     */
    function get_photo_input ( $item_array ) {

        /*Un-quote slashes*/
        $value = stripslashes( $value );
        /*HTML code*/
        $string .= "<tr  class=\"row3\" alt=\"".$item_array['name']."\">\n";

        /*Mark required field with simbol '*' */
        if ( $item_array['required'] == "on" ) {
            $string .= "<td class=\"$bg_color\">".$item_array['title']." <span style=\"color: red;\">*</span> </td>\n";
        } else {
            $string .= "<td class=\"$bg_color\">".$item_array['title']."</td>\n";
        }
        
		$string .= '<td>';
		if ( $item_array['value'] != '' ) {
		    $string .= '<img src="'.SITEBILL_MAIN_URL.'/img/data/user/'.$item_array['value'].'" border="0"/><br>';
		}
		$string .= '<input type="file" name="'.$item_array['name'].'" />';
		$string .= '</td>';

		$string .= "</tr>\n";

        /*Return html code*/
        return $string;
    }
    
	
    
    
	/**
     * Get hidden input
     * @param unknown_type $item_array
     * @return string
     */
    function get_hidden_input ( $item_array ) {

        /*Un-quote slashes*/
        $value = stripslashes( $value );
        /*HTML code*/
        $string .= "<input type=\"hidden\" name=\"".$item_array['name']."\" value=\"".$item_array['value']."\" /></td>\n";
        /*Return html code*/
        return $string;
    }
    
    function get_tlocation($item_array){
    
    		
    	 
    	 
    	$string='';
    	 
    	 
    	 
    
    	$params=$item_array['parameters'];
    	if(isset($params['visibles'])){
    		$visibles=explode('|', $params['visibles']);
    	}else{
    		$visibles=array();
    	}
    
    	if(isset($params['show_names'])){
    		$show_names=(int)$params['show_names'];
    	}else{
    		$show_names=1;
    	}
    
    	if(isset($params['names'])){
    		$_x=array();
    		$_x=explode('|', $params['names']);
    
    		if(!empty($_x)){
    			foreach($_x as $v){
    				list($key, $title)=explode(':', $v);
    				$field_names[$key]=$title;
    			}
    		}
    	}else{
    		$field_names=array();
    	}
    
    
    
    	$defaults=array();
    	if(isset($params['default_country_id'])){
    		$defaults['country_id']=$params['default_country_id'];
    	}
    	if(isset($params['default_region_id'])){
    		$defaults['region_id']=$params['default_region_id'];
    	}
    	if(isset($params['default_city_id'])){
    		$defaults['city_id']=$params['default_city_id'];
    	}
    	if(isset($params['default_district_id'])){
    		$defaults['district_id']=$params['default_district_id'];
    	}
    
    	$values=$item_array['value'];
    	if($values['country_id']==0){
    		$values['country_id']=$defaults['country_id'];
    	}
    	if($values['region_id']==0){
    		$values['region_id']=$defaults['region_id'];
    	}
    	if($values['city_id']==0){
    		$values['city_id']=$defaults['city_id'];
    	}
    
    	$DBC=DBC::getInstance();
    
    	 
    	$uniq_class_name='tlocation_object_'.md5(time().'_'.rand(1000, 9999));
    
    	$script_code='<style>.tlocation_object select {display: block; margin: 10px 0;}</style>';
    	$script_code.='<script src="'.SITEBILL_MAIN_URL.'/apps/tlocation/js/form_utils.js"></script>';
    	$script_code.='<script>$(document).ready(function(){TLocationForm.setHandler("'.$uniq_class_name.'", '.(int)$this->getConfigValue('link_street_to_city').')});</script>';
    	 
    	$string=$script_code;
    
    	$rs='';
    
    	if(empty($visibles) || (!empty($visibles) && in_array('country_id', $visibles))){
    		$data=array();
    		$query='SELECT country_id, name FROM '.DB_PREFIX.'_country ORDER BY name ASC';
    		$stmt=$DBC->query($query);
    		if($stmt){
    			while($ar=$DBC->fetch($stmt)){
    				$data[]=$ar;
    			}
    		}
    
    
    
    		$rs .= '<span class="'.$uniq_class_name.'"><select name="country_id">';
    		$rs .= '<option value="0" '.$selected.'>--</option>';
    
    		if(!empty($data)){
    			foreach($data as $d){
    				if($values['country_id']==$d['country_id']){
    					$rs .= '<option value="'.$d['country_id'].'" selected="selected">'.$d['name'].'</option>';
    				}else{
    					$rs .= '<option value="'.$d['country_id'].'">'.$d['name'].'</option>';
    				}
    			}
    		}
    		$rs .= '</select></span>';
    
    
    
    
    		$string .= '<tr class="row3">';
    		$string .= '<td>'.(($show_names && isset($field_names['country_id'])) ? $field_names['country_id'] : '').'</td>';
    		$string .= '<td>'.$rs.'</td>';
    		$string .= '</tr>';
    
    	}
    	 
    	$rs='';
    
    	if(empty($visibles) || (!empty($visibles) && in_array('region_id', $visibles))){
    		$data=array();
    		$stmt=FALSE;
    
    		if((int)$values['country_id']!=0){
    			$query='SELECT region_id, name FROM '.DB_PREFIX.'_region WHERE country_id=? ORDER BY name ASC';
    			$stmt=$DBC->query($query, array($values['country_id']));
    		}elseif(isset($defaults['country_id']) && (int)$defaults['country_id']!=0){
    			$query='SELECT region_id, name FROM '.DB_PREFIX.'_region WHERE country_id=? ORDER BY name ASC';
    			$stmt=$DBC->query($query, array($defaults['country_id']));
    		}elseif(!empty($visibles) && !in_array('country_id', $visibles)){
    			$query='SELECT region_id, name FROM '.DB_PREFIX.'_region ORDER BY name ASC';
    			$stmt=$DBC->query($query);
    		}
    		//echo $query;
    		if($stmt){
    			while($ar=$DBC->fetch($stmt)){
    
    				$data[]=$ar;
    			}
    		}
    
    
    
    		$rs .= '<span class="'.$uniq_class_name.'"><select name="region_id">';
    		$rs .= '<option value="0" '.$selected.'>--</option>';
    		 
    		if(!empty($data)){
    			foreach($data as $d){
    				if($values['region_id']==$d['region_id']){
    					$rs .= '<option value="'.$d['region_id'].'" selected="selected">'.$d['name'].'</option>';
    				}else{
    					$rs .= '<option value="'.$d['region_id'].'">'.$d['name'].'</option>';
    				}
    			}
    		}
    		$rs .= '</select></span>';
    
    		$string .= '<tr class="row3">';
    		$string .= '<td>'.(($show_names && isset($field_names['region_id'])) ? $field_names['region_id'] : '').'</td>';
    		$string .= '<td>'.$rs.'</td>';
    		$string .= '</tr>';
    
    
    	}
    
    	$rs='';
    
    	if(empty($visibles) || (!empty($visibles) && in_array('city_id', $visibles))){
    		$data=array();
    		$stmt=FALSE;
    		if((int)$values['region_id']!=0){
    			$query='SELECT city_id, name FROM '.DB_PREFIX.'_city WHERE region_id=? ORDER BY name ASC';
    			$stmt=$DBC->query($query, array($values['region_id']));
    		}elseif(isset($defaults['region_id']) && (int)$defaults['region_id']!=0){
    			$query='SELECT city_id, name FROM '.DB_PREFIX.'_city WHERE region_id=? ORDER BY name ASC';
    			$stmt=$DBC->query($query, array($defaults['region_id']));
    		}elseif(!empty($visibles) && !in_array('region_id', $visibles)){
    			$query='SELECT city_id, name FROM '.DB_PREFIX.'_city ORDER BY name ASC';
    			$stmt=$DBC->query($query);
    		}
    		 
    		if($stmt){
    			while($ar=$DBC->fetch($stmt)){
    				$data[]=$ar;
    			}
    		}
    
    
    		 
    		$rs .= '<span class="'.$uniq_class_name.'"><select name="city_id">';
    		$rs .= '<option value="0" '.$selected.'>--</option>';
    		 
    		if(!empty($data)){
    			foreach($data as $d){
    				if($values['city_id']==$d['city_id']){
    					$rs .= '<option value="'.$d['city_id'].'" selected="selected">'.$d['name'].'</option>';
    				}else{
    					$rs .= '<option value="'.$d['city_id'].'">'.$d['name'].'</option>';
    				}
    			}
    		}
    		$rs .= '</select></span>';
    
    		$string .= '<tr class="row3">';
    		$string .= '<td>'.(($show_names && isset($field_names['city_id'])) ? $field_names['city_id'] : '').'</td>';
    		$string .= '<td>'.$rs.'</td>';
    		$string .= '</tr>';
    
    
    	}
    
    	$rs='';
    
    
    
    	if(1==$this->getConfigValue('link_street_to_city')){
    		global $smarty;
    		$smarty->assign('link_street_to_city', 1);
    
    		$rs='';
    
    		if(empty($visibles) || (!empty($visibles) && in_array('district_id', $visibles))){
    			$data=array();
    			$stmt=FALSE;
    			if((int)$values['city_id']!=0){
    				$query='SELECT id, name FROM '.DB_PREFIX.'_district WHERE city_id=? ORDER BY name ASC';
    				$stmt=$DBC->query($query, array($values['city_id']));
    			}elseif(isset($defaults['city_id']) && (int)$defaults['city_id']!=0){
    				$query='SELECT id, name FROM '.DB_PREFIX.'_district WHERE city_id=? ORDER BY name ASC';
    				$stmt=$DBC->query($query, array($defaults['city_id']));
    			}elseif(!empty($visibles) && !in_array('city_id', $visibles)){
    				$query='SELECT id, name FROM '.DB_PREFIX.'_district ORDER BY name ASC';
    				$stmt=$DBC->query($query);
    			}
    
    			if($stmt){
    				while($ar=$DBC->fetch($stmt)){
    					$data[]=$ar;
    				}
    			}
    
    			 
    
    			$rs .= '<span class="'.$uniq_class_name.'"><select name="district_id">';
    			$rs .= '<option value="0" '.$selected.'>--</option>';
    
    			if(!empty($data)){
    				foreach($data as $d){
    					if($values['district_id']==$d['id']){
    						$rs .= '<option value="'.$d['id'].'" selected="selected">'.$d['name'].'</option>';
    					}else{
    						$rs .= '<option value="'.$d['id'].'">'.$d['name'].'</option>';
    					}
    				}
    			}
    			$rs .= '</select></span>';
    			 
    			$string .= '<tr class="row3">';
    			$string .= '<td>'.(($show_names && isset($field_names['district_id'])) ? $field_names['district_id'] : '').'</td>';
    			$string .= '<td>'.$rs.'</td>';
    			$string .= '</tr>';
    
    			 
    		}
    
    		$rs='';
    
    		if(empty($visibles) || (!empty($visibles) && in_array('street_id', $visibles))){
    			$data=array();
    			$stmt=FALSE;
    			if((int)$values['city_id']!=0){
    				$query='SELECT street_id, name FROM '.DB_PREFIX.'_street WHERE city_id=? ORDER BY name ASC';
    				$stmt=$DBC->query($query, array($values['city_id']));
    			}elseif(isset($defaults['city_id']) && (int)$defaults['city_id']!=0){
    				$query='SELECT street_id, name FROM '.DB_PREFIX.'_street WHERE city_id=? ORDER BY name ASC';
    				$stmt=$DBC->query($query, array($defaults['city_id']));
    			}elseif(!empty($visibles) && !in_array('city_id', $visibles)){
    				$query='SELECT street_id, name FROM '.DB_PREFIX.'_street ORDER BY name ASC';
    				$stmt=$DBC->query($query);
    			}
    
    			if($stmt){
    				while($ar=$DBC->fetch($stmt)){
    					$data[]=$ar;
    				}
    			}
    
    			 
    
    			$rs .= '<span class="'.$uniq_class_name.'"><select name="street_id">';
    			$rs .= '<option value="0" '.$selected.'>--</option>';
    
    			if(!empty($data)){
    				foreach($data as $d){
    					if($values['street_id']==$d['street_id']){
    						$rs .= '<option value="'.$d['street_id'].'" selected="selected">'.$d['name'].'</option>';
    					}else{
    						$rs .= '<option value="'.$d['street_id'].'">'.$d['name'].'</option>';
    					}
    				}
    			}
    			$rs .= '</select></span>';
    			 
    			$string .= '<tr class="row3">';
    			$string .= '<td>'.(($show_names && isset($field_names['street_id'])) ? $field_names['street_id'] : '').'</td>';
    			$string .= '<td>'.$rs.'</td>';
    			$string .= '</tr>';
    
    			 
    		}
    		 
    	}else{
    		if(empty($visibles) || (!empty($visibles) && in_array('district_id', $visibles))){
    			$data=array();
    			$stmt=FALSE;
    			if((int)$values['city_id']!=0){
    				$query='SELECT id, name FROM '.DB_PREFIX.'_district WHERE city_id=? ORDER BY name ASC';
    				$stmt=$DBC->query($query, array($values['city_id']));
    			}elseif(isset($defaults['city_id']) && (int)$defaults['city_id']!=0){
    				$query='SELECT id, name FROM '.DB_PREFIX.'_district WHERE city_id=? ORDER BY name ASC';
    				$stmt=$DBC->query($query, array($defaults['city_id']));
    			}elseif(!empty($visibles) && !in_array('city_id', $visibles)){
    				$query='SELECT id, name FROM '.DB_PREFIX.'_district ORDER BY name ASC';
    				$stmt=$DBC->query($query);
    			}
    
    			if($stmt){
    				while($ar=$DBC->fetch($stmt)){
    					$data[]=$ar;
    				}
    			}
    
    			 
    
    			$rs .= '<span class="'.$uniq_class_name.'"><select name="district_id">';
    			$rs .= '<option value="0" '.$selected.'>--</option>';
    
    			if(!empty($data)){
    				foreach($data as $d){
    					if($values['district_id']==$d['id']){
    						$rs .= '<option value="'.$d['id'].'" selected="selected">'.$d['name'].'</option>';
    					}else{
    						$rs .= '<option value="'.$d['id'].'">'.$d['name'].'</option>';
    					}
    				}
    			}
    			$rs .= '</select></span>';
    			 
    			$string .= '<tr class="row3">';
    			$string .= '<td>'.(($show_names && isset($field_names['district_id'])) ? $field_names['district_id'] : '').'</td>';
    			$string .= '<td>'.$rs.'</td>';
    			$string .= '</tr>';
    
    			 
    		}
    
    		$rs='';
    
    		if(empty($visibles) || (!empty($visibles) && in_array('street_id', $visibles))){
    
    			$data=array();
    			$stmt=FALSE;
    			if((int)$values['district_id']!=0){
    				$query='SELECT street_id, name FROM '.DB_PREFIX.'_street WHERE district_id=? ORDER BY name ASC';
    				$stmt=$DBC->query($query, array($values['district_id']));
    			}elseif(isset($defaults['district_id']) && (int)$defaults['district_id']!=0){
    				$query='SELECT street_id, name FROM '.DB_PREFIX.'_street WHERE district_id=? ORDER BY name ASC';
    				$stmt=$DBC->query($query, array($defaults['district_id']));
    			}elseif(!empty($visibles) && !in_array('district_id', $visibles)){
    				$query='SELECT street_id, name FROM '.DB_PREFIX.'_street ORDER BY name ASC';
    				$stmt=$DBC->query($query);
    			}
    
    			if($stmt){
    				while($ar=$DBC->fetch($stmt)){
    					$data[]=$ar;
    				}
    			}
    
    			 
    
    			$rs .= '<span class="'.$uniq_class_name.'"><select name="street_id">';
    			$rs .= '<option value="0" '.$selected.'>--</option>';
    
    			if(!empty($data)){
    				foreach($data as $d){
    					if($values['street_id']==$d['street_id']){
    						$rs .= '<option value="'.$d['street_id'].'" selected="selected">'.$d['name'].'</option>';
    					}else{
    						$rs .= '<option value="'.$d['street_id'].'">'.$d['name'].'</option>';
    					}
    				}
    			}
    			$rs .= '</select></span>';
    			 
    			$string .= '<tr class="row3">';
    			$string .= '<td>'.(($show_names && isset($field_names['street_id'])) ? $field_names['street_id'] : '').'</td>';
    			$string .= '<td>'.$rs.'</td>';
    			$string .= '</tr>';
    
    			 
    		}
    	}
    
    
    
    	 
    
    
    	return $string;
    
    	 
    }
    
    function compile_select_box_structure_multiple_checkbox ( $item_array ) {
    	if ( !isset($item_array['values_array']) ) {
    		$item_array['values_array'] = array(0 => 0);
    	}
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
    	$Structure_Manager = new Structure_Manager();
    	 
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'html'=>$Structure_Manager->getCategoryCheckboxes($item_array['name'], $item_array['values_array'] ),
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    }
    
    function get_select_box_by_query_as_checkboxes ( $item_array, $model=null ) {
    	$rs='';
    	$DBC=DBC::getInstance();
    	$query=$item_array['query'];
    	$stmt=$DBC->query($query);
    	$rs.='<div id="'.$item_array['name'].'" class="select_box_by_query_as_checkboxes">';
    	if(!is_array($item_array['value'])){
    		$item_array['value']=array();
    	}
    	if($stmt){
    		while ( $ar=$DBC->fetch($stmt) ) {
    			$this->total_in_select[$item_array['name']]++;
    			$value = $ar[$item_array['value_name']];
    			$value = trim($value);
    			//$value = htmlspecialchars_decode($value);
    			$value = htmlspecialchars($value, ENT_QUOTES, SITE_ENCODING);
    			 
    			if ( in_array($ar[$item_array['primary_key_name']], $item_array['value'])) {
    				$selected = 'checked="checked"';
    			} else {
    				$selected = '';
    			}
    			$rs.='<div><input type="checkbox"'.$selected.' value="'.$ar[$item_array['primary_key_name']].'" name="'.$item_array['name'].'[]" /><span>'.$value.'</span></div>';
    		}
    	}
    	$rs.='</div>';
    	//$rs .= '</select>';
    	//$rs .= '</div>';
    
    	return $rs;
    }
    
    
    
    
}
?>