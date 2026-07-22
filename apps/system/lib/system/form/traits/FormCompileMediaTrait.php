<?php
trait FormCompileMediaTrait
{
    function compile_docuploads_element($item_array)
    {
        $script_code = array();
        $collection = array();
        if (!defined('NO_DYNAMIC_INCS') || !NO_DYNAMIC_INCS) {
            $script_code[] = '<script src="' . SITEBILL_MAIN_URL . '/apps/system/js/dropzone/dropzone.js?v=2"></script>';
            $script_code[] = '<link rel="stylesheet" type="text/css" href="' . SITEBILL_MAIN_URL . '/apps/system/js/dropzone/dropzone.css?v=1">';
            $script_code[] = '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/system/js/dataimagelist.js?v=2"></script>';
        }

        //$html.='<script src="'.SITEBILL_MAIN_URL.'/apps/system/js/dropzone_sitebill.js"></script>';
        $params = $item_array['parameters'];

        if (isset($params['max_file_size']) && 0 != (int)$params['max_file_size']) {
            $max_file_size = (int)$params['max_file_size'];
        } else {
            $max_file_size = (int)str_replace('M', '', ini_get('upload_max_filesize'));
        }


        $html = $this->getDropzonePlugin($this->get_session_key(),
            array(
                'element' => $item_array,
                'max_file_size' => $max_file_size,
                'type' => 'docupoads',
                'upload_endpoint' => ($params['upload_endpoint'] ?? ''),
                'token' => ($params['token'] ?? ''),
                'min_img_count' => (isset($params['min_img_count']) ? (int)$params['min_img_count'] : 0),
                'max_img_count' => (isset($params['max_img_count']) ? (int)$params['max_img_count'] : 0),
                'accepted' => ($params['accepted'] ?? '')
            ));
        if (is_array($item_array['value']) && count($item_array['value']) > 0) {
            $table_name = $item_array['table_name'];
            $primary_key = $item_array['primary_key'];
            $primary_key_value = $item_array['primary_key_value'];
            $class = 'uploaded_' . md5(time() . rand(100, 999));
            $html .= '<div class="dz-preview-uploaded ' . $class . '">';
            $html .= '<a class="btn btn-mini btn-warning dz-preview-clear" onClick="DataImagelist.dz_clearDocs(this, ' . $primary_key_value . ', \'' . $table_name . '\', \'' . $primary_key . '\', \'' . $item_array['name'] . '\');">' . _e('Удалить все') . '</a>';
            $html .= '<ul class="dz-preview-uploaded-list">';


            foreach ($item_array['value'] as $itk => $ita) {
                if (!empty($ita['remote']) and $ita['remote'] === 'true') {
                    $prefix = '';
                } else {
                    $prefix = SITEBILL_MAIN_URL . '/img/mediadocs/';
                }
                $html .= '<li class="dz-preview-uploaded-item dz-preview-uploaded-item-docs" data-order="' . $itk . '">
                        <div class="dz-preview-uploaded-item-image-preview">
                            <div class="dz-preview-uploaded-item-doc">
                                <a href="' . $prefix . $ita['normal'] . '" target="_blank" download>' . $ita['normal'] . '</a>
                            </div>
                            <div class="dz-preview-uploaded-item-description" onDblClick="DataImagelist.dz_dblClick(this, ' . $primary_key_value . ', \'' . $table_name . '\', \'' . $primary_key . '\', \'' . $item_array['name'] . '\');">
                                ' . ($ita['title'] == '' ? _e('Описание') : $ita['title']) . '
                            </div>
                            <div class="dz-preview-uploaded-item-description-editable" style="display: none;">
                                <input type="text" value="' . ($ita['title'] == '' ? _e('Описание') : $ita['title']) . '" />
                                <button class="btn btn-success btn-small save_desc"><i class="icon-white icon-ok"></i></button>
                                <button class="btn btn-danger btn-small canc_desc"><i class="icon-white icon-remove"></i></button>
                            </div>
                            <a href="javascript:void(0);" onClick="DataImagelist.dz_upImage(this, ' . $primary_key_value . ', \'' . $table_name . '\', \'' . $primary_key . '\', \'' . $item_array['name'] . '\');" class="btn btn-small go_up" title="Выше"><i class="icon icon-chevron-left"></i></a>
                            <a href="javascript:void(0);" onClick="DataImagelist.dz_deleteDoc(this, ' . $primary_key_value . ', \'' . $table_name . '\', \'' . $primary_key . '\', \'' . $item_array['name'] . '\');" class="btn btn-small remove" title="Удалить"><i class="icon icon-remove"></i></a>
                            <a href="javascript:void(0);" onClick="DataImagelist.dz_downImage(this, ' . $primary_key_value . ', \'' . $table_name . '\', \'' . $primary_key . '\', \'' . $item_array['name'] . '\');" class="btn btn-small go_down" title="Ниже"><i class="icon icon-chevron-right"></i></a>
                            <a href="javascript:void(0);" onClick="DataImagelist.dz_makeMain(this, ' . $primary_key_value . ', \'' . $table_name . '\', \'' . $primary_key . '\', \'' . $item_array['name'] . '\');" class="btn btn-small go_down" title="Сделать главной"><i class="icon icon-star"></i></a>
                        </div>
                        </li>';
            }
            $html .= '</ul>';
            $html .= '</div>';
        }

        $collection[] = array(
            'title' => $item_array['title'],
            'name' => $item_array['name'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $html,
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );


        //$html.=$this->getDropzonePlugin($this->get_session_key());
        $answer = new stdClass();
        $answer->collection = $collection;
        $answer->scripts = $script_code;
        //print_r($answer);
        return $answer;

        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $html,
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : '')
        );
    }

    function compile_uploads_element($item_array)
    {
        $script_code = array();
        $collection = array();
        if (!defined('NO_DYNAMIC_INCS') || !NO_DYNAMIC_INCS) {
            $script_code[] = '<script src="' . SITEBILL_MAIN_URL . '/apps/system/js/dropzone/dropzone.js?v=2"></script>';
            $script_code[] = '<link rel="stylesheet" type="text/css" href="' . SITEBILL_MAIN_URL . '/apps/system/js/dropzone/dropzone.css?v=1">';
            $script_code[] = '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/system/js/dataimagelist.js?v=2"></script>';
        }

        $params = $item_array['parameters'];

        if (isset($params['max_file_size']) && 0 != (int)$params['max_file_size']) {
            $max_file_size = (int)$params['max_file_size'];
        } else {
            $max_file_size = (int)str_replace('M', '', ini_get('upload_max_filesize'));
        }

        $table_name = $item_array['table_name'];
        $primary_key = $item_array['primary_key'];
        $primary_key_value = $item_array['primary_key_value'];

        $html = $this->getDropzonePlugin($this->get_session_key(),
            array(
                'element' => $item_array,
                'max_file_size' => $max_file_size,
                'min_img_count' => (isset($params['min_img_count']) ? (int)$params['min_img_count'] : 0),
                'max_img_count' => (isset($params['max_img_count']) ? (int)$params['max_img_count'] : 0)
            ));
        $image_list = array();

        $DBC = DBC::getInstance();

        $image_list = $item_array['value'];

        if ( $this->getConfigValue('apps.sharder.mirroring.enable') ) {
            $data_model = new \system\lib\model\Data_Model_Alias();
            $image_list = $data_model->sharder_mirror($image_list, true);

        }


        $code = $item_array['table_name'] . '.' . $item_array['name'];
        if (isset($params['tagged']) && $params['tagged'] == 1) {
            $tagged = true;
            $taggedlng = false;
        } else {
            $tagged = false;
            $taggedlng = false;
        }

        $imagedescused = true;
        if (isset($params['disableimagedesc']) && $params['disableimagedesc'] == 1) {
            $imagedescused = false;
        }


        $DBC = DBC::getInstance();

        if ($tagged) {
            $query = 'SELECT imagetag_id, name FROM ' . DB_PREFIX . '_imagetag WHERE `code` = ?';
            $stmt = $DBC->query($query, array($code));
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $tags[$ar['imagetag_id']] = $ar['name'];
                }
            }
        }


        /*require_once SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/system/uploader_tagger.php';
        $tagger = new uploader_tagger();
        $tagger->setItem($code);*/

        //print_r($image_list);
        if (is_array($image_list) && count($image_list) > 0) {

            $uploadedid = 'uploaded_' . md5(time() . rand(100, 999));


            $html .= '<script>';
            $html .= '$(document).ready(function(){';
            //$html .= 'DataImagelist.dz_addSortable(\''.$uploadedid.'\', ' . $primary_key_value . ', \'' . $table_name . '\', \'' . $primary_key . '\', \'' . $item_array['name'] . '\')';
            $html .= '});';
            $html .= '</script>';

            $html .= '<div id="' . $uploadedid . '" class="dz-preview-uploaded">';
            $html .= '<a class="btn btn-mini btn-warning dz-preview-clear" onClick="DataImagelist.dz_clearImages(this, ' . $primary_key_value . ', \'' . $table_name . '\', \'' . $primary_key . '\', \'' . $item_array['name'] . '\');">' . Multilanguage::_('L_DROPZONE_DELETEALL') . '</a>';
            if ($this->getConfigValue('apps.downloader.enable') and $table_name == 'data') {
                $html .= '<a class="btn btn-mini btn-success dz-preview-clear" style="margin-left: 120px;" href="' . SITEBILL_MAIN_URL . '/' . $this->getConfigValue('apps.downloader.alias') . '/' . $primary_key_value . '">' . _e('Скачать все фото') . '</a>';
            }

            $html .= '<ul class="dz-preview-uploaded-list">';
            $bootstrap_version = trim($this->getConfigValue('bootstrap_version'));
            $btnsicons = $this->form_decorator->getUploadsFieldControlButtons();
            $btnsclass = $this->form_decorator->getUploadsFieldControlButtonsClass();
            foreach ($image_list as $itk => $ita) {

                if ($tagged) {
                    if (isset($ita['tags'])) {
                        $currenttags = $ita['tags'];
                    } else {
                        $currenttags = array();
                    }

                    $taghtml = '';
                    if (!empty($tags)) {

                        $taghtml .= '<div>';
                        $taghtml .= '<select onchange="DataImagelist.dz_changeTags(this, ' . $primary_key_value . ', \'' . $table_name . '\', \'' . $primary_key . '\', \'' . $item_array['name'] . '\');">';
                        $taghtml .= '<option value="0">Выбрать</option>';
                        foreach ($tags as $tk => $tv) {
                            $taghtml .= '<option value="' . $tk . '"' . (in_array($tk, $currenttags) ? ' selected="selected"' : '') . '>' . $tv . '</option>';
                        }
                        $taghtml .= '</select>';
                        $taghtml .= '</div>';
                    }
                }

                /**
                 * TODO
                 * проверить зависимость размещение селекта тегов
                 */


                if (filter_var($ita['preview'], FILTER_VALIDATE_URL)) {
                    $img_url = $ita['preview'];
                    $normal_img_url = $ita['normal'];
                } else {
                    $img_url = SITEBILL_MAIN_URL . '/img/data/' . $ita['preview'];
                    $normal_img_url = $this->getServerFullUrl(true) . SITEBILL_MAIN_URL . '/img/data/' . $ita['normal'];
                }
                if ($this->getConfigValue('apps.downloader.src_enable')) {
                    $normal_img_url = SITEBILL_MAIN_URL . '/' . $this->getConfigValue('apps.downloader.src_alias') . '/?url=' . $normal_img_url;
                }
                $html .= '<li class="dz-preview-uploaded-item" data-order="' . $itk . '">
    					<div class="dz-preview-uploaded-item-image-preview">
							<div class="dz-preview-uploaded-item-image">
								<a href="' . $normal_img_url . '" download><img src="' . $img_url . '" /></a>
							</div>';
                if ($imagedescused) {
                    $html .= '<div class="get_field_tpl" onDblClick="DataImagelist.dz_dblClick(this, ' . $primary_key_value . ', \'' . $table_name . '\', \'' . $primary_key . '\', \'' . $item_array['name'] . '\');">
								' . ($ita['title'] == '' ? _e('Описание') : $ita['title']) . '
							</div>' . $taghtml . '';

                    $html .= '<div class="dz-preview-uploaded-item-description-editable" style="display: none;">
								<input type="text" value="' . ($ita['title'] == '' ? _e('Описание') : $ita['title']) . '" />
								<button class="btn btn-success btn-small save_desc"><i class="icon-white icon-ok"></i></button>
								<button class="btn btn-danger btn-small canc_desc"><i class="icon-white icon-remove"></i></button>
							</div>';
                }

                $cbuttons = [];



                $cbuttons[] = '<a href="javascript:void(0);" onClick="DataImagelist.dz_upImage(this, ' . $primary_key_value . ', \'' . $table_name . '\', \'' . $primary_key . '\', \'' . $item_array['name'] . '\');" class="'.$btnsclass.' go_up" title="Выше">' . $btnsicons['upImage'] . '</a>';
                $cbuttons[] = '<a href="javascript:void(0);" onClick="DataImagelist.dz_deleteImage(this, ' . $primary_key_value . ', \'' . $table_name . '\', \'' . $primary_key . '\', \'' . $item_array['name'] . '\');" class="'.$btnsclass.' remove" title="Удалить">' . $btnsicons['deleteImage'] . '</a>';
                $cbuttons[] = '<a href="javascript:void(0);" onClick="DataImagelist.dz_downImage(this, ' . $primary_key_value . ', \'' . $table_name . '\', \'' . $primary_key . '\', \'' . $item_array['name'] . '\');" class="'.$btnsclass.' go_down" title="Ниже">' . $btnsicons['downImage'] . '</a>';
                $cbuttons[] = '<a href="javascript:void(0);" onClick="DataImagelist.dz_makeMain(this, ' . $primary_key_value . ', \'' . $table_name . '\', \'' . $primary_key . '\', \'' . $item_array['name'] . '\');" class="'.$btnsclass.' go_down" title="Сделать главной">' . $btnsicons['makeMain'] . '</a>';
                $cbuttons[] = '<a href="javascript:void(0);" onClick="DataImagelist.dz_rotateImage(this, ' . $primary_key_value . ', \'' . $table_name . '\', \'' . $primary_key . '\', \'' . $item_array['name'] . '\', \'ccw\')" class="'.$btnsclass.'" title="Повернуть против часовой стрелки">'.$btnsicons['rotateImageCCW'].'</a>';
                $cbuttons[] = '<a href="javascript:void(0);" onClick="DataImagelist.dz_rotateImage(this, ' . $primary_key_value . ', \'' . $table_name . '\', \'' . $primary_key . '\', \'' . $item_array['name'] . '\', \'cw\')" class="'.$btnsclass.'" title="Повернуть по часовой стрелке">'.$btnsicons['rotateImageCW'].'</a>';

                $html .= implode(' ', $cbuttons);

                $html .= '</div>
						</li>';
            }
            $html .= '</ul>';
            $html .= '</div>';
        }

        $collection[] = array(
            'title' => $item_array['title'],
            'hint' => $item_array['hint'],
            'name' => $item_array['name'],
            'type' => $item_array['type'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $html,
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : '')
        );


        //$html.=$this->getDropzonePlugin($this->get_session_key());
        $answer = new stdClass();
        $answer->collection = $collection;
        $answer->scripts = $script_code;
        //print_r($answer);
        return $answer;

        return array(
            'title' => $item_array['title'],
            'type' => $item_array['type'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $html,
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : '')
        );
    }

    function compile_avatar_element($item_array)
    {

        $table_name = $item_array['table_name'];
        $primary_key = $item_array['primary_key'];
        $primary_key_value = $item_array['primary_key_value'];

        $script_code = array();
        $collection = array();
        if (!defined('NO_DYNAMIC_INCS') || !NO_DYNAMIC_INCS) {
            $script_code[] = '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/system/js/dataimagelist.js?v=2"></script>';
        }

        $html = '';
        $html .= '<div class="frm_avatar">';
        $html .= '<input type="file" name="' . $item_array['name'] . '" />';
        if ($item_array['value'] != '') {
            $html .= '<div class="frm_avatar_im">';
            $html .= '<img src="' . SITEBILL_MAIN_URL . '/img/data/' . $item_array['value'] . '" border="0"/>';
            $html .= '</div>';
            $html .= '<div class="frm_avatar_cntrl">';
            $html .= '<input type="checkbox" class="' . $this->classes['checkbox'] . '" name="delete_avatar[' . $item_array['name'] . ']" value="yes" /> Удалить фото';
            $html .= '<a href="javascript:void(0);" onClick="DataImagelist.av_deleteImage(this, ' . $primary_key_value . ', \'' . $table_name . '\', \'' . $primary_key . '\', \'' . $item_array['name'] . '\');" class="btn btn-small remove" title="Удалить"><i class="icon icon-remove"></i></a>';
            $html .= '</div>';
        }
        $html .= '</div>';
        /* if ( $item_array['value'] != '' ) {
          $image_list = '<img src="'.SITEBILL_MAIN_URL.'/img/data/'.$item_array['value'].'" border="0"/>
          <br>
          <a href="#">Удалить фото</a>
          <input type="checkbox" name="delava['.$item_array['name'].']" value="yes" /> Удалить фото';
          }else{
          $image_list = '';
          } */


        $collection[] = array(
            'title' => $item_array['title'],
            'hint' => $item_array['hint'],
            'name' => $item_array['name'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $html,
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );


        //$html.=$this->getDropzonePlugin($this->get_session_key());
        $answer = new stdClass();
        $answer->collection = $collection;
        $answer->scripts = $script_code;
        //print_r($answer);
        return $answer;


        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => '<input type="file" name="' . $item_array['name'] . '" />',
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'image_list' => $image_list
        );
    }

    function compile_photo_element($item_array)
    {
        $image_list = '';
        if ($item_array['value'] != '') {
            $image_list .= '<div id="photo_element_image_list_deprecated" class="photo_element">';
            $image_list .= '<img src="' . SITEBILL_MAIN_URL . '/img/data/user/' . $item_array['value'] . '" border="0"/>';
            switch ($this->bootstrap_version) {
                case '3' :
                {
                    $image_list .= '<div class="checkbox"><label><input type="checkbox" name="delpic" value="yes">Удалить фото</label></div>';
                    break;
                }
                case '4md' :
                {
                    $image_list .= '<input type="checkbox" id="delpic" name="delpic" value="yes"><label for="delpic">Удалить фото</label>';
                    break;
                }
                case '4' :
                {
                    $image_list .= '<label class="form-check-label"><input type="checkbox" class="form-check-input" name="delpic" value="yes">Удалить фото</label>';
                    break;
                }
                default :
                {
                    $image_list .= '<label class="checkbox"><input type="checkbox" name="delpic" value="yes"> Удалить фото</label>';
                }
            }
            $image_list .= '</div>';
        }
        try {
            if ($this->get_context()) {
                $update_user_id = $this->getRequestValue($this->get_context()->primary_key);
                if ($this->getRequestValue('do') == 'new' or $this->getRequestValue('do') == 'new_done') {
                    $update_user_id = 'new';
                }
            } else {
                $update_user_id = '0';
            }
        } catch (Exception $e) {
            $update_user_id = '0';
        }

        if ($item_array['value'] != '') {
            $image = SITEBILL_MAIN_URL . '/img/data/user/' . $item_array['value'];
        } elseif ($_SESSION['new_avatar_img'] != '') {
            $image = SITEBILL_MAIN_URL . '/img/data/user/' . $_SESSION['new_avatar_img'];
        } else {
            $image = '';
        }

        $image_cropper = '
            <app id="vue_image_cropper">
            <image-cropper 
                update_user_id="' . $update_user_id . '" 
                language="ru" 
                image_url="' . $image . '" 
                width="' . $this->getConfigValue('user_pic_width') . '"
                height="' . $this->getConfigValue('user_pic_height') . '"
                upload_button_title="' . _e('Загрузить новое фото') . '"
                image_delete_title="' . _e('Удалить фото') . '"
                >
            </image-cropper>
            </app>';


        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $image_cropper . '<input id="photo_element_deprecated" type="file" name="' . $item_array['name'] . '" />',
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'image_list' => $image_list,
            'type' => $item_array['type']
        );
    }

    function compile_pluploader_element($item_array)
    {

        $_count = 0;
        $image_list = $this->getImageListAdmin($item_array['action'], $item_array['table_name'], $item_array['primary_key'], $item_array['primary_key_value'], $_count);

        if ($this->getConfigValue('photo_per_data') > 0 and $item_array['action'] == 'data') {
            if ($_count >= $this->getConfigValue('photo_per_data')) {
                return array(
                    'title' => $item_array['title'],
                    'required' => ($item_array['required'] == "on" ? 1 : 0),
                    'image_list' => $image_list,
                    'html' => '',
                    'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
                    'type' => $item_array['type']
                );
            }
        }
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'image_list' => $image_list,
            'html' => $this->getPluploaderPlugin($this->get_session_key()),
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_uploadify_element($item_array)
    {

        $parameters = $item_array['parameters'];
        $_count = 0;
        $image_list = $this->getImageListAdmin($item_array['action'], $item_array['table_name'], $item_array['primary_key'], $item_array['primary_key_value'], $_count);

        if ($this->getConfigValue('photo_per_data') > 0 and $item_array['action'] == 'data') {
            if ($_count >= $this->getConfigValue('photo_per_data')) {
                return array(
                    'title' => $item_array['title'],
                    'required' => ($item_array['required'] == "on" ? 1 : 0),
                    'image_list' => $image_list,
                    'html' => '',
                    'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
                    'type' => $item_array['type']
                );
            }
        }
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'image_list' => $image_list,
            'html' => $this->getUploadifyPlugin($this->get_session_key(), $parameters),
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_uploadify_file_element($item_array)
    {
        $image_list = $this->getFileListAdmin($item_array['action'], $item_array['table_name'], $item_array['primary_key'], $item_array['primary_key_value']);
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'image_list' => $image_list,
            'html' => $this->getUploadifyFilePlugin($this->get_session_key()),
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_pluploader_file_element($item_array)
    {
        $image_list = $this->getFileListAdmin($item_array['action'], $item_array['table_name'], $item_array['primary_key'], $item_array['primary_key_value']);
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'image_list' => $image_list,
            'html' => $this->getPluploaderPlugin($this->get_session_key()),
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_attachment_element($item_array)
    {
        $value = htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING);
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => '<input type="text" name="' . $item_array['name'] . '"  size="' . $item_array['length'] . '" maxlength="' . $item_array['maxlength'] . '" value="' . $value . '" />',
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

}
