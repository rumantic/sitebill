<?php
/**
 * FileUploadTrait — extracted from SiteBill class (sitebill.php)
 * Auto-generated, do not edit manually.
 */
trait FileUploadTrait
{
    /**
     * Load uploadify images
     * @param string $session_code session code
     * @return array
     */
    function load_uploadify_images($session_code = '', $element_name = '')
    {
        $ra = array();

        $DBC = DBC::getInstance();
        if ($element_name == '') {
            $query = 'SELECT * FROM ' . UPLOADIFY_TABLE . ' WHERE `session_code`=? AND (`element`=? or `element` is null) ORDER BY `uploadify_id`';
            $stmt = $DBC->query($query, array((string)$session_code, ''));
        } else {
            $query = 'SELECT * FROM ' . UPLOADIFY_TABLE . ' WHERE `session_code`=? AND `element`=? ORDER BY `uploadify_id`';
            $stmt = $DBC->query($query, array((string)$session_code, $element_name));
        }
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ra[] = $ar['file_name'];
            }
        }
        if (empty($ra)) {
            return false;
        } else {
            return $ra;
        }
    }

    /**
     * Edit file
     * @param string $action action
     * @param string $table_name table name
     * @param string $key key
     * @param int $record_id record ID
     * @return boolean
     */
    function editFileMulti($action, $table_name, $key, $record_id)
    {
        $path = SITEBILL_DOCUMENT_ROOT . '/img/data/';
        $uploadify_path = SITEBILL_DOCUMENT_ROOT . $this->uploadify_dir;

        $ra = array();
        $images = $this->load_uploadify_images($this->get_session_key());
        if (!$images) {
            return;
        }

        foreach ($images as $image_name) {
            $i++;
            $need_prv = 0;
            $preview_name = '';
            if (!empty($image_name)) {
                $arr = explode('.', $image_name);
                $ext = strtolower(end($arr));
                $preview_name = "file" . uniqid() . '_' . time() . "_" . $i . "." . $ext;
                $prv = "ffile" . uniqid() . '_' . time() . "_" . $i . "." . $ext;
                $preview_name_tmp = "_tmp" . uniqid() . '_' . time() . "_" . $i . "." . $ext;


                list($width, $height) = $this->makeMove($uploadify_path . $image_name, $path . $preview_name);
                $ra[$i]['preview'] = $preview_name;
                $ra[$i]['normal'] = $preview_name;
            }
        }
        $this->add_image_records($ra, $table_name, $key, $record_id);
        $this->delete_uploadify_images($this->get_session_key());
        return $ra;
    }

    function clear_uploadify_table($session_code = '', $anyway = false)
    {
        if (1 == (int)$this->getConfigValue('dontclean_uploadify_table') && !$anyway) {
            return true;
        }

        $postloaded = array();
        if (isset($_POST['_formpostloaded']) && is_array($_POST['_formpostloaded']) && count($_POST['_formpostloaded']) > 0) {
            $_postloaded = $_POST['_formpostloaded'];
            foreach ($_postloaded as $list) {
                $postloaded = array_merge($postloaded, $list);
            }
        }

        $uploadify_path = SITEBILL_DOCUMENT_ROOT . $this->uploadify_dir;
        $DBC = DBC::getInstance();
        $ra = array();
        if ($session_code == '') {
            $query = "SELECT file_name FROM " . UPLOADIFY_TABLE;
            $stmt = $DBC->query($query);
        } else {
            $query = "SELECT file_name FROM " . UPLOADIFY_TABLE . ' WHERE session_code=?';
            $stmt = $DBC->query($query, array($session_code));
        }

        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                if (!in_array($ar['file_name'], $postloaded)) {
                    $ra[] = $ar['file_name'];
                }
            }
        }

        if (count($ra) > 0) {
            foreach ($ra as $image_name) {
                if (is_file($uploadify_path . $image_name)) {
                    unlink($uploadify_path . $image_name);
                }
            }
        }

        if ($session_code == '') {
            $query = "TRUNCATE TABLE " . UPLOADIFY_TABLE;
            $stmt = $DBC->query($query);
        } else {
            if (!empty($postloaded)) {
                $query = 'DELETE FROM ' . UPLOADIFY_TABLE . ' WHERE `session_code`=? AND `file_name` NOT IN (' . implode(',', array_fill(0, count($postloaded), '?')) . ')';
                array_unshift($postloaded, $session_code);
                $stmt = $DBC->query($query, $postloaded);
            } else {
                $query = 'DELETE FROM ' . UPLOADIFY_TABLE . ' WHERE `session_code`=?';
                $stmt = $DBC->query($query, array($session_code));
            }
        }

        return true;
    }

    function clear_captcha_session_table()
    {
        $limit_date = date('Y-m-d H:i:s', (time() - 24 * 3600));
        $DBC = DBC::getInstance();
        $q = 'DELETE FROM ' . DB_PREFIX . '_captcha_session WHERE start_date<?';
        $DBC->query($q, array($limit_date));
        return true;
    }

    function clear_session_table()
    {
        $limit_date_anonim = date('Y-m-d H:i:s', (time() - 24 * 3600));
        $limit_date_user = date('Y-m-d H:i:s', (time() - 90 * 24 * 3600));

        $DBC = DBC::getInstance();

        $q = 'DELETE FROM ' . DB_PREFIX . '_session WHERE start_date<? AND user_id=0';
        $DBC->query($q, array($limit_date_anonim));

        $q = 'DELETE FROM ' . DB_PREFIX . '_session WHERE start_date<? AND user_id <> 0';
        $DBC->query($q, array($limit_date_user));

        return true;
    }

    /**
     * Delete uploadify images
     * @param string $session_code session code
     * @return array
     */
    function delete_uploadify_images($session_code, $element = '')
    {
        $uploadify_path = SITEBILL_DOCUMENT_ROOT . $this->uploadify_dir;
        $DBC = DBC::getInstance();

        $ra = array();
        if ($element != '') {
            $query = 'SELECT file_name FROM ' . UPLOADIFY_TABLE . ' WHERE `session_code`=? AND `element`=?';
            $stmt = $DBC->query($query, array((string)$session_code, $element));
        } else {
            $query = 'SELECT file_name FROM ' . UPLOADIFY_TABLE . ' WHERE `session_code`=?';
            $stmt = $DBC->query($query, array((string)$session_code));
        }


        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ra[] = $ar['file_name'];
            }
        }
        if (count($ra) > 0) {
            foreach ($ra as $image_name) {
                if (is_file($uploadify_path . $image_name)) {
                    unlink($uploadify_path . $image_name);
                }
            }
        }
        if ($element != '') {
            $query = 'DELETE FROM ' . UPLOADIFY_TABLE . ' WHERE `session_code`=? AND `element`=?';
            $stmt = $DBC->query($query, array((string)$session_code, $element));
        } else {
            $query = 'DELETE FROM ' . UPLOADIFY_TABLE . ' WHERE session_code=?';
            $stmt = $DBC->query($query, array((string)$session_code));
        }

        return true;
    }

    /**
     * Delete uploadify image
     * @param string $image_name image_name
     * @return array
     */
    function delete_uploadify_image($image_name)
    {
        $DBC = DBC::getInstance();
        $file_name = $image_name;
        $uploadify_path = SITEBILL_DOCUMENT_ROOT . $this->uploadify_dir;
        $query = 'DELETE FROM ' . UPLOADIFY_TABLE . ' WHERE file_name=?';
        $DBC->query($query, array($file_name));
        unlink($uploadify_path . $file_name);
        return true;
    }

    /**
     * Get Plupload plugin (http://www.plupload.com/)
     * Only html4 version available (not attached files for others)
     * @param string $session_code session code
     * @return string
     */
    function getPluploaderPlugin($session_code)
    {
        $this->clear_uploadify_table($session_code);
        global $folder;
        $rs .= '
    		
    		<style type="text/css">@import url(' . $folder . '/apps/system/js/plupload/jquery.plupload.queue/css/jquery.plupload.queue.css);</style>
			<script type="text/javascript" src="' . $folder . '/apps/system/js/plupload/plupload.full.js"></script>
			<script type="text/javascript" src="' . $folder . '/apps/system/js/plupload/jquery.plupload.queue/jquery.plupload.queue.js">
			<script type="text/javascript" src="http://bp.yahooapis.com/2.4.21/browserplus-min.js"></script>
			<script type="text/javascript" src="' . $folder . '/apps/system/js/plupload/i18n/ru.js"></script>
			<script>        
		       $(function() {
		       		function log(msg){
		       			 $("#log").append(msg + "\n");
		       		
		       		};
		       		
		       		var del=[];
		       
					$("#html4_uploader").pluploadQueue({
						runtimes : \'html4\',
						multiple_queues: true,
						url : "' . $folder . '/apps/system/js/uploadify/uploadify.php?session=' . $session_code . '",
						init : {
							FileUploaded: function(up, file, info) {
								if (info.response.indexOf("wrong_ext") != -1){
									file.status = plupload.FAILED;
									up.trigger("UploadProgress", file);
								}else if(info.response.indexOf("max_file_size") != -1){
									file.status = plupload.FAILED;
									up.trigger("UploadProgress", file);
								}
							},
							
						}
					});
				});  
		    </script>  
			<div id="log"></div>
			<div id="html4_uploader">You browser doesnt support simple upload forms. Are you using Lynx?</div>';
        return $rs;
    }

    /**
     * Get uploadify plugin
     * @param string $session_code session code
     * @return string
     */
    function getUploadifyPlugin($session_code, $params = array())
    {
        $this->clear_uploadify_table($session_code);
        $uploaded_images = $this->load_uploadify_images($session_code);
        global $folder;
        $rs = '';
        $rs .= '
<link href="' . $folder . '/apps/system/js/uploadify/uploadify.css" type="text/css" rel="stylesheet" />
<style>
		#filecollector { overflow: hidden; }
		#filecollector div { width: 100px; display: block; float: left; padding: 5px; margin: 3px; }
		#filecollector div img { width: 100px; border: 1px solid #CFCFCF; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15); border-radius: 5px; margin-bottom: 5px; }
</style>
		
<script type="text/javascript" src="' . $folder . '/apps/system/js/uploadify/jquery.uploadify.js"></script>
<script type="text/javascript">
var uploadedfiles = 0;
var maxQueueSize = 100;
var queueSize = 0;
$(document).ready(function() {
	var max_item_count=' . ((int)$this->getConfigValue('photo_per_data') > 0 ? (int)$this->getConfigValue('photo_per_data') : 1000) . ';
	
	
	
  $(\'#file_upload\').uploadify({
    \'swf\'  : \'' . $folder . '/apps/system/js/uploadify/uploadify.swf\',
    \'uploader\'    : \'' . $folder . '/apps/system/js/uploadify/uploadify.php?session=' . $session_code . '\',
    \'cancelImg\' : \'' . $folder . '/apps/system/js/uploadify/uploadify-cancel.png\',
    \'folder\'    : \'' . $folder . '/cache/upl\',
    \'auto\'      : true,
	\'fileTypeExts\': \'*.jpg;*.jpeg;*.png;*.gif\',
	\'multi\': true,	
	\'queueSizeLimit\': 100,
		\'buttonText\': \'' . ((isset($params['button_name']) && $params['button_name'] != '') ? $params['button_name'] : Multilanguage::_('L_PHOTO')) . '\',	
	\'buttonImg\': \'' . $folder . '/img/button_img_upl.png\',	
    \'onUploadSuccess\': function(fileObj, response, data) {
    					queueSize++;
    					if ( response == \'max_file_size\' ) {
    						alert(\'' . Multilanguage::_('L_MESSAGE_MAX_UPL_SIZE') . ' ' . ini_get('upload_max_filesize') . ' \');
    						return false;
    					}
    					if ( response == \'wrong_ext\' ) {
    						alert(\'' . Multilanguage::_('L_MESSAGE_AVIALABLE_EXTS') . ' *.jpg,*.jpeg,*.png,*.gif\');
    						return false;
    					}
    					if ( response == \'bad_file\' ) {
    						alert(\'bad_file\');
    						return false;
    					}
    					if ( queueSize > maxQueueSize ) {
    						alert(\'' . Multilanguage::_('L_MESSAGE_MAX_FILES_COUNT') . '\');
    						return false;
    					}
    					var imgs_count=$("div.preview_admin").length+$("#filecollector img").length;
    					imgs_count++;
    					if(imgs_count==max_item_count){
    						$(\'#file_uploadUploader\').hide();
						}
    								
    					addFileNotify(queueSize);
    					addFileInCollector(response);
    					
    				}
                    
    });
    
});
function addFileNotify ( queueSize ) {
	$(\'#filenotify\').html( \'Вы успешно загрузили: \' + queueSize + \' файл(ов)\' );
}
function addFileInCollector ( filePath ) {
	var temp=new Array();
	temp=filePath.split(\'/\');
    //								temp=filePath.split(\'||\');
	var f=temp[temp.length-1];
	var cont=$(\'#filecollector\').html();
	cont=cont+\'<div><img src="\'+filePath+\'" /><a class="kill_upl btn btn-mini btn-danger" href="javascript:void(0)" alt="\'+f+\'">X</a></div>\';
    //								cont=cont+\'<div><img src="\'+temp[0]+\'" /><a class="kill_upl btn btn-mini btn-danger" href="javascript:void(0)" alt="\'+f+\'">X</a></div>\';
	$(\'#filecollector\').html(cont);
	
}

$(document).ready(function() {
	$(document).on(\'click\', \'a.kill_upl\',function(){
	
		var imgs_count=$("div.preview_admin").length+$("#filecollector img").length;
		var max_item_count=' . ((int)$this->getConfigValue('photo_per_data') > 0 ? (int)$this->getConfigValue('photo_per_data') : 1000) . ';
		var url=\'/js/ajax.php?action=delete_uploadify_image&img_name=\'+$(this).attr(\'alt\');
		$.getJSON(url,{},function(data){});
		var parent=$(this).parent(\'div\');
		parent.html(\'\');
		parent.remove();
		imgs_count--;
		if(imgs_count<max_item_count){
    		$(\'#file_uploadUploader\').show();
		}
	});
	
		
});

</script>
<input id="file_upload" name="file_upload" type="file" />
<div id="filenotify"></div>
<div id="filecollector">';
        if (false !== $uploaded_images) {
            foreach ($uploaded_images as $uplim) {
                $p = explode('.', $uplim);
                if (in_array(strtolower(end($p)), array('jpg', 'jpeg', 'png', 'gif'))) {
                    $rs .= '<div><img src="' . SITEBILL_MAIN_URL . '/cache/upl/' . $uplim . '"><a class="kill_upl btn btn-mini btn-danger" href="javascript:void(0)" alt="' . $uplim . '">X</a></div>';
                }
            }
        }

        $rs .= '</div>';

        return $rs;
    }

    function getDropzonePlugin($session_code, $params = array())
    {
        $element = $params['element']['name'];
        $type = $params['element']['type'];

        //Проверяем наличие расширяющих правил для max_img_count
        if (isset($params['element']['parameters']['max_img_count_ext']) && '' != $params['element']['parameters']['max_img_count_ext']) {
            $maximgcountextendrules = $params['element']['parameters']['max_img_count_ext'];
        } else {
            $maximgcountextendrules = '';
        }

        $controlledfields = array();
        if ($maximgcountextendrules != '') {
            $rulesparts = explode(':', $maximgcountextendrules);
            $size = intval($rulesparts[0]);
            if ($size > 0 && count($rulesparts) > 1) {
                unset($rulesparts[0]);
                foreach ($rulesparts as $rule) {
                    $oneruleparts = explode(',', $rule);
                    if (count($oneruleparts) == 3) {
                        $controlledfields[$oneruleparts[0]] = $oneruleparts[0];
                    }
                }
            }
        }

        $rs = '';

        $this->clear_uploadify_table($session_code);

        $uploaded_images = $this->load_uploadify_images($session_code, $element);

        $id = 'dz_' . md5(time() . rand(100, 999));
        $Dropzone_name = 'Dropzone_' . md5(time() . rand(100, 999));

        /*if ((int)$params['min_img_count'] != 0) {
            $src = 'var formsubmit=$("#' . $id . '").parents("form").eq(0).find("[name=submit]");
					var vm=formsubmit.data("valid_me");
					if(vm === undefined){
						vm=[];
					}
					vm.push({id:"' . $id . '", count:' . (int)$params['min_img_count'] . '});
					formsubmit.data("valid_me", vm);';
        } else {
            $src = '';
        }*/

        $additional_url_params = array();
        $additional_url_params_string = '';
        //dd($params);

        if (!empty($params['upload_endpoint'])) {
            // https://uploader.sitebill.site/upload
            $upload_endpoint = $params['upload_endpoint'];
            $additional_url_params['token'] = $params['token'];
            $additional_url_params_string = http_build_query($additional_url_params);
        } else {
            $upload_endpoint = SITEBILL_MAIN_URL . '/apps/system/js/uploadify/uploadify.php';
        }


        $rs .= '<script>
    			
    			$(document).ready(function(){
                    if (typeof Dropzone !== "undefined") { Dropzone.autoDiscover = false; }
    			    let accepted = "'.((isset($params['element']) &&
                isset($params['element']['parameters']) &&
                isset($params['element']['parameters']['accepted']) &&
                $params['element']['parameters']['accepted'] != '') ? $params['element']['parameters']['accepted'] : '').'";
                
                let controlledfields = [' . (!empty($controlledfields) ? '\''.implode('\',\'', $controlledfields).'\'' : '') . '];
    			    
    			    var _dzInitFn = function(){
    			    /*var ' . $Dropzone_name . ' = createDropzone("'.$id.'", "'.$params['max_file_size'].'", "'.$element.'", "'.$params['element']['table_name'].'", "'.$params['element']['primary_key_value'].'", "'.$params['element']['primary_key'].'", accepted, controlledfields, '.intval($params['min_img_count']).');*/
    			
    			    let _dzEl_' . $id . ' = document.getElementById("' . $id . '");
    			    if (!_dzEl_' . $id . ' || _dzEl_' . $id . '.dropzone) { return; }
    				let ' . $Dropzone_name . ' = new Dropzone("div#' . $id . '", 
    				{ 
    					maxFilesize: ' . $params['max_file_size'] . ',
						url: "'.$upload_endpoint.'?uploader_type=dropzone&element=' . $element . '&model=' . $params['element']['table_name'] . '&primary_key_value=' . $params['element']['primary_key_value'] . '&primary_key=' . $params['element']['primary_key'] . '",
	    				acceptedFiles: accepted,
						addRemoveLinks: true,
						customparams: {
						    url: \''.$upload_endpoint.'?&uploader_type=dropzone&'.$additional_url_params_string.'\',
						    element: \'' . $element . '\',
						    model: \'' . $params['element']['table_name'] . '\',
						    primary_key_value: \'' . $params['element']['primary_key_value'] . '\',
						    primary_key: \'' . $params['element']['primary_key'] . '\',
						    controls: controlledfields
						}
					});
					$("div#' . $id . ' .dz-remove").click(function(){
							let _this=$(this);
							let url="' . SITEBILL_MAIN_URL . '/js/ajax.php?action=delete_uploadify_image&img_name="+$(this).attr("alt");
								$("#' . $id . ' .postloaded[value=\'"+$(this).attr("alt")+"\']").remove();
								$.getJSON(url,{},function(data){_this.parents(".dz-preview").eq(0).remove()});
    						});
					' . $src . ' 
					' . $Dropzone_name . '.on("complete", function(){
    						if(this.getQueuedFiles().length==0 && this.getUploadingFiles().length==0){
    							let form=$(this.element).parents("form");
								form.find("[name=submit]").show();
							}
    
    				}).on("success", function(file, responce) {
							if(responce.status=="error"){
								$(file.previewElement).remove();
							    if(typeof ' . $Dropzone_name . '_quenue !=\'undefined\' ){
								    ' . $Dropzone_name . '_quenue--;
                                }
                                let form=$(this.element).parents("form");
                                let msg = $(\'<div class="alert">\'+responce.msg+\'</div>\');
								msg.insertBefore($("#' . $id . '"));
								setTimeout(function(){msg.fadeOut(function(){msg.remove();});}, 1500);
							}else{														
								let form=$(this.element).parents("form");														
								let rem=$(file.previewElement).find(".dz-remove");
								let temp=new Array();
								if ( typeof window["s3Update"] === "function" && responce.bucket ) {
								    s3Update(responce, '.$Dropzone_name.');
								} else {
                                    temp=responce.msg.split(\'/\');
                                    let file_name=temp[temp.length-1];
                                    $("#' . $id . '").append($("<input class=\'postloaded\' name=\'_formpostloaded[' . $element . '][]\' type=\'hidden\' value=\'"+file_name+"\'>"));
                                    rem.attr("alt", file_name);
                                    rem.on("click", function(){
                                        var url="' . SITEBILL_MAIN_URL . '/js/ajax.php?action=delete_uploadify_image&img_name="+$(this).attr("alt");
                                        $.getJSON(url,{},function(data){});
                                    });
								}
							}
    				}).on("addedfile", function(file){
    					let form=$(this.element).parents("form");
    					form.find("[name=submit]").hide();
    					this.options.url = this.options.customparams.url + \'&element=\' + this.options.customparams.element + \'&model=\' + this.options.customparams.model + \'&primary_key_value=\' + this.options.customparams.primary_key_value + \'&primary_key=\' + this.options.customparams.primary_key;
    					if(this.options.customparams.controls.length > 0){
    					    for(let i in this.options.customparams.controls){
    					        this.options.url += \'&\'+this.options.customparams.controls[i]+\'=\' + form.find(\'[name=\'+this.options.customparams.controls[i]+\']\').val();
    					    }
    					}
    				});
                                
                    Dropzone.prototype.defaultOptions.dictDefaultMessage = "' . _e('Переместите сюда файлы для загрузки') . '";
                    Dropzone.prototype.defaultOptions.dictFallbackMessage = "' . _e('Ваш браузер не поддерживает опцию drag-n-drop') . '";
                    Dropzone.prototype.defaultOptions.dictFallbackText = "' . _e('Пожалуйста, используйте форму ниже для загрузки файлов') . '";
                    Dropzone.prototype.defaultOptions.dictFileTooBig = "' . _e('Файл слишком большой') . ' ({{filesize}}MiB). ' . _e('Максимальный размер файла') . ': {{maxFilesize}}MiB.";
                    Dropzone.prototype.defaultOptions.dictInvalidFileType = "' . _e('Формат файла не подходит') . '";
                    Dropzone.prototype.defaultOptions.dictResponseError = "' . _e('Ответ сервера с ошибкой') . ' {{statusCode}} code.";
                    Dropzone.prototype.defaultOptions.dictCancelUpload = "' . _e('Отменить загрузку') . '";
                    Dropzone.prototype.defaultOptions.dictCancelUploadConfirmation = "' . _e('Вы уверены, что хотите прервать загрузку?') . '";
                    Dropzone.prototype.defaultOptions.dictRemoveFile = "' . _e('Удалить файл') . '";
                    Dropzone.prototype.defaultOptions.dictMaxFilesExceeded = "' . _e('Исчерпан лимит загрузки файлов') . '";

				};
				if (typeof requestIdleCallback === "function") {
				    requestIdleCallback(_dzInitFn, { timeout: 2000 });
				} else {
				    setTimeout(_dzInitFn, 100);
				}
				});
				</script>';
        $rs .= '<div data-ii="" class="dropzone_outer' . ($type == 'docuploads' ? ' docuploads' : '') . '"><div id="' . $id . '" class="dropzone_inner"><div class="dz-default dz-message"><span><span class="bigger-50 bolder">' . ($type == 'docuploads' ? Multilanguage::_('L_DOCUPLOADS_FILE') : Multilanguage::_('L_UPLOADS_FILE')) . '</span> <br>	<i class="upload-icon icon-cloud-upload blue icon-3x"></i></span></div>';
        if ($this->getConfigValue('apps.realty.additional_dropzone_button')) {
            $rs .= '<a class="btn btn-primary" id="dropzone_add_more_files_' . $id . '"><i class="fa fa-plus"></i> ' . ($type == 'docuploads' ? _e('Добавить файлы') : _e('Добавить фото')) . '</a>';
        }

        if (false !== $uploaded_images) {
            foreach ($uploaded_images as $uplim) {

                $p = explode('.', $uplim);
                if (($type == 'uploads' && in_array(strtolower(end($p)), array('jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif'))) || $type == 'docuploads') {
                    $rs .= '<input class="postloaded" name="_formpostloaded[' . $element . '][]" type="hidden" value="' . $uplim . '">';
                }
            }
        }

        if (false !== $uploaded_images) {
            foreach ($uploaded_images as $uplim) {

                $p = explode('.', $uplim);

                if (($type == 'uploads' && in_array(strtolower(end($p)), array('jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif'))) || $type == 'docuploads') {
                    $rs .= '<div class="dz-preview dz-processing dz-image-preview dz-success">';
                    $rs .= '<div class="dz-details">';
                    $rs .= '<div class="dz-filename">';
                    $rs .= '<span data-dz-name="">' . $uplim . '</span></div>';
                    $rs .= '<div class="dz-size" data-dz-size="">';
                    $rs .= '<strong>0.1</strong> MiB</div>';
                    if ($type == 'uploads') {
                        $rs .= '<img data-dz-thumbnail="" alt="' . $uplim . '" src="' . SITEBILL_MAIN_URL . '/cache/upl/' . $uplim . '">';
                    }

                    $rs .= '</div>  <div class="dz-progress">';
                    $rs .= '<span class="dz-upload" data-dz-uploadprogress="" style="width: 100%;">';
                    $rs .= '</span>';
                    $rs .= '</div>';
                    $rs .= '<div class="dz-success-mark"><span>✔</span></div>  <div class="dz-error-mark"><span>✘</span></div>  <div class="dz-error-message">';
                    $rs .= '<span data-dz-errormessage="">';
                    $rs .= '</span>';
                    $rs .= '</div>';
                    $rs .= '<a class="dz-remove" href="javascript:undefined;" data-dz-remove="" alt="' . $uplim . '">' . _e('Удалить') . '</a>';
                    $rs .= '</div>';
                }
            }
        }
        $rs .= '</div>';
        $rs .= '</div>';

        return $rs;
    }

    /**
     * Get uploadify plugin
     * @param string $session_code session code
     * @return string
     */
    function getUploadifyFilePlugin($session_code, $params = array())
    {
        $this->clear_uploadify_table($session_code);
        $id = md5(time() . random_int(1000, 9999));
        global $folder;

        $rs = '';
        $rs .= '
<link href="' . $folder . '/apps/system/js/uploadify/uploadify.css" type="text/css" rel="stylesheet" />
<script type="text/javascript" src="' . $folder . '/apps/system/js/uploadify/jquery.uploadify.js"></script>
<script type="text/javascript">
var uploadedfiles = 0;
var maxQueueSize = 100;
var queueSize = 0;
$(document).ready(function() {
  $(\'#' . $id . '\').uploadify({
    \'swf\'  : \'' . $folder . '/apps/system/js/uploadify/uploadify.swf\',
    \'uploader\'    : \'' . $folder . '/apps/system/js/uploadify/uploadify.php?file=1&session=' . $session_code . '\',
    \'cancelImg\' : \'' . $folder . '/apps/system/js/uploadify/uploadify-cancel.png\',
    \'folder\'    : \'' . $folder . '/cache/upl\',
    \'auto\'      : true,
	\'fileTypeExts\': \'*.doc;*.pdf;*.zip\',
	\'multi\': true,	
	\'queueSizeLimit\': 100,
	\'buttonText\': \'' . ((isset($params['button_name']) && $params['button_name'] != '') ? $params['button_name'] : Multilanguage::_('L_FILE')) . '\',
	\'buttonImg\': \'' . $folder . '/img/button_img_upl.png\',
    \'onUploadSuccess\': function(fileObj, response, data) {
    					queueSize++;
    					if ( response == \'max_file_size\' ) {
    						alert(\'' . Multilanguage::_('L_MESSAGE_MAX_UPL_SIZE') . ' ' . ini_get('upload_max_filesize') . ' \');
    						return false;
    					}
    					if ( response == \'wrong_ext\' ) {
    						alert(\'' . Multilanguage::_('L_MESSAGE_AVIALABLE_EXTS') . ' png, jpg, tif, jpeg, doc,docx, xls, xlsx, pdf, txt, zip, rar\');
    						return false;
    					}
    					if ( queueSize > maxQueueSize ) {
    						alert(\'' . Multilanguage::_('L_MESSAGE_MAX_FILES_COUNT') . '\');
    						return false;
    					}
    					addFileNotify(queueSize);
    				}

    });
});
function addFileNotify ( queueSize ) {
	$(\'#filenotify\').html( \'Вы успешно загрузили: \' + queueSize + \' файл(ов)\' );
}
</script>
<input id="' . $id . '" name="file_upload" type="file" />
<div id="filenotify"></div>
        ';
        return $rs;
    }

}
