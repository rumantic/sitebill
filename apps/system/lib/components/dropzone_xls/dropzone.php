<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php');
require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/components/ajax_common.php');
class DropZone extends ajax_common {
	private $context;
	private $excel_free_admin;
	private $data_manager_export;
	
	function set_context ( $context ) {
		$this->context = $context;
	}
	
	function get_context () {
		//$this->writeLog(__METHOD__.var_export($this->context, true));
		return $this->context;
	}
	
	function compile_uploads_element($item_array){
		 
		$script_code=array();
		$collection=array();
		$script_code[]='<script src="'.SITEBILL_MAIN_URL.'/apps/system/js/dropzone/dropzone.js"></script>';
		$script_code[]='<link rel="stylesheet" type="text/css" href="'.SITEBILL_MAIN_URL.'/apps/system/js/dropzone/dropzone.css">';
		$script_code[]= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/system/js/dataimagelist.js"></script>';
		//$html.='<script src="'.SITEBILL_MAIN_URL.'/apps/system/js/dropzone_sitebill.js"></script>';
		$params=$item_array['parameters'];
		//$this->writeLog(__METHOD__.'table name = '.$this->get_context()->table_name);
		
		$params['model_name'] = $this->get_context()->table_name;
		$params['primary_key'] = $this->get_context()->primary_key;
		
		if(isset($params['max_file_size']) && 0!=(int)$params['max_file_size']){
			$max_file_size=(int)$params['max_file_size'];
		}else{
			$max_file_size=(int)str_replace('M', '', ini_get('upload_max_filesize'));
		}
		 
		$html=$this->getDropzonePlugin($this->get_session_key(), array('element'=>$item_array['name'], 'model_name'=>$params['model_name'], 'primary_key'=>$params['primary_key'], 'max_file_size'=>$max_file_size, 'min_img_count'=>(int)$params['min_img_count']));
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
	
	function ajax () {
		
		
		//$this->writeLog('dropzone_ajax');
		//$this->writeLog(var_export($_FILES, true));
		if ( $this->getRequestValue('do') == 'delete' ) {
			//$this->writeLog('dropzone_ajax_delete');
				
			$img_name=$this->getRequestValue('file_name');
			$this->delete_uploadify_image($img_name);
				
		} elseif ( $this->getRequestValue('do') == 'parse_xls' ) {
			$file_name=$this->getRequestValue('file_name');
				
			//$this->writeLog('dropzone_parse_xls '.$file_name);
			
			$html_table = $this->xls_parser($file_name);

			//$this->writeLog('$html_table = '.$html_table);
			
			//$this->writeLog(json_encode(array('status'=>'OK', 'content'=>$html_table)));
			return $html_table;	
			//echo json_encode(array('status'=>'OK', 'content'=>$html_table));
			//exit;
		} elseif ( $this->getRequestValue('do') == 'import' ) {
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json');
			echo json_encode(array('status'=>'OK', 'content'=>$this->run_import()));
			exit;
		} else {
			$file_mode = 'excel';
			if (!empty($_FILES)) {
			
				$file_container_name='file';
				$tempFile = $_FILES[$file_container_name]['tmp_name'];
				$targetPath = $_SERVER['DOCUMENT_ROOT'] .'/'.SITEBILL_MAIN_URL.'/cache/upl' . '/';
			
				$path_parts = pathinfo($_FILES[$file_container_name]['name']);
			
				$ext = $path_parts['extension'];
			
				if ( ($_FILES[$file_container_name]['size'] / 1000000) >   ( (int)str_replace('M', '', ini_get('upload_max_filesize')) ) ) {
					//if ( 1 ) {
					echo 'max_file_size';
					return;
				}
				if ( $file_mode == 'excel' ) {
					$avail_ext = array('xls','xlsx');
					if ( !in_array(strtolower($ext), $avail_ext) ) {
						echo 'wrong_ext';
						return;
					}
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
			
				move_uploaded_file($tempFile,$targetFile);
				$this->clear_uploadify_table($this->get_session_key(), true);
				$this->addFile($_REQUEST['session'], $preview_name_tmp);
				
				header('HTTP/1.1 200 OK');
				header('Content-Type: application/json');
				echo json_encode(array('status'=>'OK', 'file'=>$preview_name_tmp));
				exit;
				
			}
		}

		
		return 'dropzone_ajax';
	}
	
	function run_import () {
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/excelfree/admin/admin.php');
		require_once SITEBILL_DOCUMENT_ROOT.'/apps/excelfree/admin/data_manager_export.php';
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/components/dropzone_xls/dropzone_model_adapter.php');
		
		$excel_free_admin = new excelfree_admin();
		$model_with_table_name = $this->get_model_with_table_name();
		
		$assoc_array=((isset($_POST['assoc_array']) && count($_POST['assoc_array'])>0) ? $_POST['assoc_array'] : NULL);
		if($assoc_array===NULL){
			$assoc_array=((isset($_GET['assoc_array']) && count($_GET['assoc_array'])>0) ? $_GET['assoc_array'] : NULL);
		}
		
		//$this->writeLog(__METHOD__.', $assoc_array = '.var_export($assoc_array, true));
		
		$mapper = $this->mapper();
		
		if($assoc_array===NULL){
			$assoc_array=$mapper['data']['fields'];
		} else {
			foreach ( $assoc_array as $key => $value ) {
				$assoc_array[$key] = SiteBill::iconv('utf-8', SITE_ENCODING, $value);
				if ( $value == '_not_defined' ) {
					unset($model_with_table_name[$this->get_table_name()][$key]);
				}
			}
		}
		//$this->writeLog(__METHOD__.', model_with_table_name = <pre>'.var_export($model_with_table_name, true).'</pre>');
		
		$this->data_manager_export = new dropzone_model_adapter($this->get_table_name(), $this->get_action(), $this->get_primary_key(), $model_with_table_name); 
		$file_name = $this->getRequestValue('file_name');
		if ( $file_name ) {
			$data = $excel_free_admin->load_xls($file_name);
			$data = $this->remapping($data);
			
				
			$rs .= $this->sql_exec($this->get_table_name(), $data, $mapper, $assoc_array);
			$this->delete_uploadify_image($file_name);
			return $rs;
		}
	
	}
	
	/**
	 * Метод выполняет генерацию SQL-запросов и их выполнение
	 * В зависимости от того, есть ли значение в таблице с таким ключом, генерируется INSERT или UPDATE запрос
	 * Возвращаем результаты выполнения каждого SQL-запроса
	 * @param string $table_name - название таблицы
	 * @param array $data - данные для загрузки
	 * @param array $mapper - ассоциативный массив с маппингом полей
	 * @param array $assoc_array - перемаппированый массив, с учетом перестановок
	 * @return string
	 */
	function sql_exec( $table_name, $data, $mapper, $assoc_array ) {
		
		
		$keys = array_keys($mapper[$table_name]['fields']);
		$primary_key = $this->get_primary_key();
		unset($data[1]);
		
		//$this->writeLog(__METHOD__.'__data'.var_export($data, true).', table_name = '.$table_name.', primary_key = '.$primary_key);
		//$this->writeLog(__METHOD__.'__request before'.var_export($_REQUEST, true));
		
		foreach ( $data as $data_id => $data_array ) {
			//$this->writeLog(__METHOD__.'__data <pre>'.var_export($data_array, true).'</pre>');
			//$this->writeLog(__METHOD__.'__$assoc_array <pre>'.var_export($assoc_array, true).'</pre>');
			//$this->writeLog(__METHOD__.'__primary_key '.$primary_key.'');
			
			//$this->writeLog(__METHOD__.'__POST '.var_export($_POST, true));
			$primary_key_value = $data_array[$primary_key];
			$this->setRequestValue($primary_key, $data_array[$primary_key]);
			//$_GET[$primary_key] = $data_array[$primary_key];
			//$this->writeLog(__METHOD__.'__POST after '.var_export($_POST, true));
				
			unset($data_array[$primary_key]);
			$this->data_manager_export->init_request_from_xls($assoc_array, $data_array);
			$data_array[$primary_key] = $primary_key_value;
			$this->setRequestValue($primary_key, $primary_key_value);
			
			//$this->writeLog(__METHOD__.'__request'.var_export($_POST, true));
				
			//$this->writeLog(__METHOD__.'__data'.var_export($data, true).', table_name = '.$table_name);
			//$this->writeLog(__METHOD__.'__65');
				
			if ( $this->data_manager_export->is_record_exist($data_array, $assoc_array) ) {
				//$this->writeLog(__METHOD__.'__update');
                            if ( $this->need_check_access($table_name) ) {
                                if ( !$this->check_access($table_name, $this->get_check_access_user_id($table_name), 'edit', $primary_key, $data_array[$primary_key] ) ) {
                                    $rs .= 'ID = '.$data_array[$primary_key].', '.Multilanguage::_('L_ACCESS_DENIED').'<br>';
                                } else {
                                    $rs .= $this->data_manager_export->edit();
                                }
                            } else {
                                $rs .= $this->data_manager_export->edit();
                            }
				
			} else {
				//$this->writeLog(__METHOD__.'__insert');
				//$this->writeLog(__METHOD__.'__model = '.var_export($this->data_manager_export->data_model, true));
				
				$rs .= $this->data_manager_export->insert();
				//$this->writeLog(__METHOD__.'__69');
				
			}
			
			//$this->writeLog(__METHOD__.'__7');
				
		}
		return $rs;
	
	}
        
        
	
	
	function mapper () {
		$data_model = $this->get_model();
	
		//$mapper['data']['primary_key'] = 'id';
		foreach ( $data_model as $key => $item_a ) {
			$mapper['data']['fields'][$key] = $key;
		}
		 
		return $mapper;
	}
	
	function xls_parser ( $file_name ) {
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/excelfree/admin/admin.php');
		$excel_free_admin = new excelfree_admin();
		
		
		if ( $file_name != '' ) {
				
			$mapper = $this->mapper();
			$this->writeLog(__METHOD__.'_4');
			//$this->writeLog('mapper = '.var_export($mapper, true));
				
			$data = $excel_free_admin->load_xls($file_name);
			//$this->writeLog(__METHOD__.'_data = '.var_export($data, true));
				
			
			 
			$assoc_array=((isset($_POST['assoc_array']) && count($_POST['assoc_array'])>0) ? $_POST['assoc_array'] : NULL);
			if ($assoc_array===NULL){
				$assoc_array=((isset($_GET['assoc_array']) && count($_GET['assoc_array'])>0) ? $_GET['assoc_array'] : NULL);
			}
					
			if ($assoc_array===NULL){
				$assoc_array = $mapper['data']['fields'];
			} else {
				foreach ( $assoc_array as $key => $value ) {
					$assoc_array[$key] = SiteBill::iconv('utf-8', SITE_ENCODING, $value);
				}
			}
	
			$rs = $this->template_select( $this->getRequestValue('excel_template_id') );
			
			//$this->writeLog('mapper = '.var_export($mapper, true));
			
			$rs .= '<table class="table table-striped table-bordered table-hover dataTable">';
			$rs .= '<tr>';
			foreach ( $mapper['data']['fields'] as $item_id => $item ) {
				$rs .= '<td class="row_title">'.$item.'</td>';
			}
			$rs .= '</tr>';
	
			$data = $this->remapping($data);
	
			$rs .= '<tr  class="xls_row_title">';
			$columns = array();
			foreach ( $assoc_array as $item_id => $item ) {
				$value = $data[1][$item];
				if ( $value != '' ) {
					//$columns[] = '\''.$value.'\'';
					$columns[] = '\''.$item.'\'';
				} elseif ( $value = $this->get_key_by_title($item, $data[1]) ) {
					$assoc_array[$item_id] = $value;
					$columns[] = '\''.$value.'\'';
				}
				$rs .= '<td>'.$this->select_box($item_id, $data[1], $value ).'</td>';
			}
	
			$rs .= '</tr>';
			unset($data[1]);
			 
			 
			foreach ( $data as $row_id => $data_item ) {
				$rs .= '<tr>';
				foreach ( $mapper['data']['fields'] as $item_id => $item ) {
					$assoc_key = $assoc_array[$item];
					if ( strlen($data[$row_id][$assoc_key]) > 21 ) {
						$concat_dots = '...';
					}else {
						$concat_dots = '';
					}
						
					$data[$row_id][$assoc_key] = mb_substr($data[$row_id][$assoc_key], 0, 20).$concat_dots;
					$rs .= '<td>'.strip_tags($data[$row_id][$assoc_key]).'</td>';
				}
				$rs .= '</tr>';
				if ( $j++ > 10 ) {
					break;
				}
			}
			 
			$rs .= '</tr>';
			$rs .= '</table>';
			
			//$rs .= '<script>';
			//$rs .= 'var ca = new Array('.implode(',', $columns).')';
			//$rs .= '</script>';
			//$this->writeLog($rs);
			return $rs;
			return var_export($data, true);
			 
		}
	}
	
	function select_box ( $field_key, $data, $current_value  = '' ) {
		$max_length = 30;
		$rs .= '<select name="'.$field_key.'" id="'.$field_key.'" class="field">';
		$rs .= '<option value="_not_defined">'.Multilanguage::_('L_NO_CONFORMITY','excelfree').'</option>';
		foreach ( $data as $letter => $key ) {
			if ( !empty($key) ) {
				$selected = '';
				if ( $current_value == $key ) {
					$selected = 'selected';
				} elseif ( $key == $this->getRequestValue($field_key) and !empty($key)) {
					$selected = 'selected';
				} else {
					$selected = '';
				}
				if ( strlen($key) > $max_length ) {
					$print_key = mb_substr($key, 0, $max_length).'...';
				} else {
					$print_key = $key;
				}
				$rs .= '<option value="'.$letter.'" '.$selected.'>'.$print_key.'</option>';
			}
		}
		$rs .= '</select>';
		 
		return $rs;
	}
	
	
	function template_select( $excel_template_id = false ) {
		return $rs;
	}
	
	
	
	
	function remapping ( $data ) {
		//$this->writeLog(__METHOD__.", data = ".var_export($data, true));
		$header = $data[1];
		foreach ( $data as $item_id => $item_a ) {
			foreach ( $item_a as $letter => $value ) {
				$title = SiteBill::iconv('utf-8', SITE_ENCODING, $header[$letter]);
				$key = $this->get_key_by_title_only_model($title);
				if($key){
					$data[$item_id][$key] = SiteBill::iconv('utf-8', SITE_ENCODING, $value);
				}else{
					$data[$item_id][$title] = SiteBill::iconv('utf-8', SITE_ENCODING, $value);
				}
	
				unset($data[$item_id][$letter]);
			}
		}
		return $data;
	}
	
	
	/**
	 * Add file
	 * @param string $session_code session code
	 * @param string $targetFile target file
	 * @return boolean
	 */
	function addFile ( $session_code, $targetFile ) {
		$query = "insert into ".UPLOADIFY_TABLE." (session_code, file_name) values ('$session_code', '$targetFile')";
		$DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
		return true;
	}
	
	
	function getDropzonePlugin ( $session_code, $params=array() ) {
		$element=$params['element'];
		$model_name = $params['model_name'];
		$primary_key = $params['primary_key'];
	
		$this->clear_uploadify_table($session_code);
		 
		$uploaded_images=$this->load_uploadify_images($session_code, $element);
		$id='dz_'.md5(time().rand(100, 999));
		$Dropzone_name='Dropzone_'.md5(time().rand(100, 999));
		 
		if((int)$params['min_img_count']!=0){
			$src='var formsubmit=$("#'.$id.'").parents("form").eq(0).find("[name=submit]");
					var vm=formsubmit.data("valid_me");
					if(vm === undefined){
						vm=[];
					}
					vm.push({id:"'.$id.'", count:'.(int)$params['min_img_count'].'});
					formsubmit.data("valid_me", vm);';
		}else{
			$src='';
		}
		 
		 
		$rs.='<script>
				var model_name=\''.$model_name.'\';
				var primary_key=\''.$primary_key.'\';
				var file_name;
						
						
    							
						
    			$(document).ready(function(){
						
					$("#ok_button").on("click", function(){
    					complete_load (primary_key, model_name, file_name);
    				});
    				var '.$Dropzone_name.' = new Dropzone("div#'.$id.'",
    				{
    					maxFilesize: '.$params['max_file_size'].',
						url: "'.SITEBILL_MAIN_URL.'/js/ajax.php?action=dropzone_xls&uploader_type=dropzone&model_name='.$model_name.'&session='.$session_code.'&element='.$element.'",
	    				addRemoveLinks: true
					});
					$("div#'.$id.' .dz-remove").click(function(){
							var _this=$(this);
							//console.log(22);
    								var url="'.SITEBILL_MAIN_URL.'/js/ajax.php?action=dropzone_xls&do=delete&file_name="+$(this).attr("alt");
									$.getJSON(url,{},function(data){_this.parents(".dz-preview").eq(0).remove()});
    							});
					'.$src.'
					'.$Dropzone_name.'.on("complete", function(){
    						if(this.getQueuedFiles().length==0 && this.getUploadingFiles().length==0){
    							var form=$(this.element).parents("form");
    							form.find("[name=submit]").prop("disabled", false);
	
    						}
							$(".loading").css("display", "block");
							
	
    				}).on("success", function(file, responce) {
							if(responce.status=="error"){
								$(file.previewElement).remove();
								'.$Dropzone_name.'_quenue--;
							}else{
										
								var rem=$(file.previewElement).find(".dz-remove");
								var ok_button=$("#ok_button");
								var temp=new Array();
										
								//temp=responce.msg.split(\'/\');
										
								//console.log(responce);
								file_name=responce.file;
								//console.log(file_name);
								rem.attr("alt", file_name);
								rem.on("click", function(){
    								var url="'.SITEBILL_MAIN_URL.'/js/ajax.php?action=dropzone_xls&do=delete&file_name="+file_name;
    								$("#uploads_result").html("");
									$.getJSON(url,{},function(data){});
   									$("#button_block").css("display", "none");
    							});
    							var url_ajax="'.SITEBILL_MAIN_URL.'/js/ajax.php?action=dropzone_xls&do=parse_xls&model_name='.$model_name.'&file_name="+file_name;
								//console.log(url_ajax);
    									
								$.ajax({
    									url: url_ajax,
    									type: "GET",
    									success: function (html) {
											$(".loading").css("display", "none");
    										$("#uploads_result").html("");
											//console.log(html);
    									
    										$("#uploads_result").append(html);
    										$("#button_block").css("display", "block");
										}
    							});
    							
							}
	
    				}).on("addedfile", function(file){
    					var form=$(this.element).parents("form");
    					form.find("[name=submit]").prop("disabled", true);
    				
    				});
				});
				</script>';
		$rs .= '<script src="'.SITEBILL_MAIN_URL.'/apps/system/lib/components/dropzone_xls/utils.js"></script>';
		$rs.='<div class="dropzone_outer"><div id="'.$id.'" class="dropzone_inner"><div class="dz-default dz-message"><span><span class="bigger-50 bolder"><i class="icon-caret-right red"></i> '.Multilanguage::_('L_UPLOADS_FILE').'</span> <br> 				<i class="upload-icon icon-cloud-upload blue icon-3x"></i></span></div>';
		$rs.='</div></div>';
		 
		return $rs;
	}
}