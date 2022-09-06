<?php

class dz_imagework extends Ajax_Server
{
    function work()
    {
        $what = $this->getRequestValue('what');

        $table = $this->getRequestValue('model_name');
        $field_name = $this->getRequestValue('field_name');
        $sortorder = $this->getRequestValue('sortorder');
        $key = $this->getRequestValue('key');
        $key_value = (int)$this->getRequestValue('key_value');
        $current_position = (int)$this->getRequestValue('current_position');
        $reorder = $this->getRequestValue('reorder');
        $rot_dir = $this->getRequestValue('rot_dir');
        $doc_mode = (int)$this->getRequestValue('doc_mode') == 1 ? true : false;

        $tags = $this->getRequestValue('tags');
        if (is_array($tags)) {
            $tags = array_filter($tags, function ($tg) {
                return (intval($tg) > 0 ? true : false);
            });
        }

        //Переопределения для fake_config
        if ($table == 'fake_config') {
            $fake_config = new \api\entities\fake_config();

            $key_value = $fake_config->get_native_config_record_id($field_name);
            $table = 'config';
            $field_name = 'value';
        }


        $user_id = $this->getSessionUserId();
        $admin_mode = false;

        if ($user_id == 0) {
            return 'error';
        }
        $DBC = DBC::getInstance();
        $query = 'SELECT system_name FROM ' . DB_PREFIX . '_group WHERE group_id=(SELECT group_id FROM ' . DB_PREFIX . '_user WHERE user_id=? LIMIT 1)';
        $stmt = $DBC->query($query, array($user_id));
        if (!$stmt) {
            return 'error';
        }
        $ar = $DBC->fetch($stmt);
        if ($ar['system_name'] == 'admin') {
            $admin_mode = true;
        } else {
            $admin_mode = $this->check_access(
                $table,
                $user_id,
                'edit',
                $this->getRequestValue('key'),
                (int)$this->getRequestValue('key_value')
            );
        }

        switch ($what) {
            case 'resort' :
            {
                if (!is_array($sortorder) || empty($sortorder)) {
                    return $this->getErrorResponceJSON();
                }

                $DBC = DBC::getInstance();
                if ($admin_mode) {
                    $query = 'SELECT `' . $field_name . '` FROM `' . DB_PREFIX . '_' . $table . '` WHERE `' . $key . '`=? LIMIT 1';
                    $stmt = $DBC->query($query, array($key_value));
                } else {
                    $query = 'SELECT `' . $field_name . '` FROM `' . DB_PREFIX . '_' . $table . '` WHERE `' . $key . '`=? AND user_id=? LIMIT 1';
                    $stmt = $DBC->query($query, array($key_value, $user_id));
                }

                if (!$stmt) {
                    return $this->getErrorResponceJSON();
                }
                $ar = $DBC->fetch($stmt);
                if ($ar[$field_name] == '') {
                    return $this->getErrorResponceJSON();
                }
                $uploads = unserialize($ar[$field_name]);

                $newarray = array();

                foreach ($sortorder as $v) {
                    $newarray[] = $uploads[$v];
                }

                $uploads = $newarray;
                $query = 'UPDATE `' . DB_PREFIX . '_' . $table . '` SET `' . $field_name . '`=? WHERE `' . $key . '`=?';
                $stmt = $DBC->query($query, array(serialize($uploads), $key_value));
                if ($stmt) {
                    return $this->getSuccessResponceJSON();
                }
                return $this->getErrorResponceJSON();
                break;
            }
            case 'reorder' :
            {
                if ($reorder == 'up') {
                    $new_position = $current_position - 1;
                } elseif ($reorder == 'down') {
                    $new_position = $current_position + 1;
                }
                $DBC = DBC::getInstance();
                if ($admin_mode) {
                    $query = 'SELECT `' . $field_name . '` FROM `' . DB_PREFIX . '_' . $table . '` WHERE `' . $key . '`=? LIMIT 1';
                    $stmt = $DBC->query($query, array($key_value));
                } else {
                    $query = 'SELECT `' . $field_name . '` FROM `' . DB_PREFIX . '_' . $table . '` WHERE `' . $key . '`=? AND user_id=? LIMIT 1';
                    $stmt = $DBC->query($query, array($key_value, $user_id));
                }

                if (!$stmt) {
                    return $this->getErrorResponceJSON();
                }
                $ar = $DBC->fetch($stmt);
                if ($ar[$field_name] == '') {
                    return $this->getErrorResponceJSON();
                }
                $uploads = unserialize($ar[$field_name]);
                if (!isset($uploads[$current_position]) || !isset($uploads[$new_position])) {
                    return $this->getErrorResponceJSON();
                }
                $temp = $uploads[$current_position];
                $uploads[$current_position] = $uploads[$new_position];
                $uploads[$new_position] = $temp;
                $query = 'UPDATE `' . DB_PREFIX . '_' . $table . '` SET `' . $field_name . '`=? WHERE `' . $key . '`=?';
                $stmt = $DBC->query($query, array(serialize($uploads), $key_value));
                if ($stmt) {
                    return $this->getSuccessResponceJSON();
                }
                return $this->getErrorResponceJSON();
                break;
            }
            case 'rotate' :
            {
                //Признак необходимости смены имени картинки (на сервере используется кеширование или по иным причинам)
                //Установка системно или через параметры
                $needrename = false;

                $DBC = DBC::getInstance();
                $query = 'SELECT * FROM ' . DB_PREFIX . '_columns WHERE `name`=? AND `type`=? AND `table_id`=(SELECT `table_id` FROM ' . DB_PREFIX . '_table WHERE `name`=? LIMIT 1)';
                $stmt = $DBC->query($query, array($field_name, 'uploads', $table));

                if (!$stmt and $table != 'user' and $field_name != 'imgfile') {
                    return 'error';
                }
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                }
                if ($ar['parameters'] != '') {
                    $parameters = unserialize($ar['parameters']);
                } else {
                    $parameters = array();
                }

                if (!isset($parameters['norm_width'])) {
                    $big_width = $this->getConfigValue($table . '_image_big_width');
                    if ($big_width == '') {
                        $big_width = $this->getConfigValue('news_image_big_width');
                    }
                    $parameters['norm_width'] = $big_width;
                }

                if (!isset($parameters['norm_height'])) {
                    $big_height = $this->getConfigValue($table . '_image_big_height');
                    if ($big_height == '') {
                        $big_height = $this->getConfigValue('news_image_big_height');
                    }
                    $parameters['norm_height'] = $big_height;
                }

                if (!isset($parameters['prev_width'])) {
                    $preview_width = $this->getConfigValue($table . '_image_preview_width');
                    if ($preview_width == '') {
                        $preview_width = $this->getConfigValue('news_image_preview_width');
                    }
                    $parameters['prev_width'] = $preview_width;
                }

                if (!isset($parameters['prev_height'])) {
                    $preview_height = $this->getConfigValue($table . '_image_preview_height');
                    if ($preview_height == '') {
                        $preview_height = $this->getConfigValue('news_image_preview_height');
                    }
                    $parameters['prev_height'] = $preview_height;
                }

                if (!isset($parameters['preview_smart_resizing'])) {
                    if (1 === intval($this->getConfigValue('apps.realty.preview_smart_resizing')) && $table == 'data') {
                        $parameters['preview_smart_resizing'] = 1;
                    }
                }

                if ($admin_mode) {
                    $query = 'SELECT `' . $field_name . '` FROM `' . DB_PREFIX . '_' . $table . '` WHERE `' . $key . '`=? LIMIT 1';
                    $stmt = $DBC->query($query, array($key_value));
                } else {
                    $query = 'SELECT `' . $field_name . '` FROM `' . DB_PREFIX . '_' . $table . '` WHERE `' . $key . '`=? AND user_id=? LIMIT 1';
                    $stmt = $DBC->query($query, array($key_value, $user_id));
                }


                if (!$stmt) {
                    return $this->getErrorResponceJSON();
                }
                $ar = $DBC->fetch($stmt);
                if ($ar[$field_name] == '') {
                    return $this->getErrorResponceJSON();
                }
                $uploads = unserialize($ar[$field_name]);

                if ($table == 'user' and $field_name == 'imgfile') {
                    $uploads[0]['normal'] = 'user/' . $ar[$field_name];
                }


                if (!isset($uploads[$current_position])) {
                    return $this->getErrorResponceJSON();
                }


                $rot_image = $uploads[$current_position];

                if ($rot_dir == 'ccw') {
                    $degree = 90;
                } else {
                    $degree = -90;
                }

                $is_watermark = false;
                if ($table == 'data' && $this->getConfigValue('is_watermark')) {
                    $is_watermark = true;
                }

                $res = $this->rotateImage2($rot_image, $is_watermark, $degree, $parameters);
                if ($res) {

                    if ($needrename) {

                        $target_image_name = $rot_image['normal'];
                        $target_preview_name = $rot_image['preview'];

                        $code = md5(time() . rand(100, 999));


                        $filepath = explode('/', $target_image_name);
                        $filename = array_pop($filepath);
                        $arr = explode('.', $filename);
                        $ext = end($arr);

                        $normalimagename = (!empty($filepath) ? implode('/', $filepath) : '') . '/img' . $code . '.' . $ext;
                        $previewimagename = (!empty($filepath) ? implode('/', $filepath) : '') . '/prv' . $code . '.' . $ext;

                        rename(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $target_image_name, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $normalimagename);
                        rename(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $target_preview_name, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $previewimagename);

                        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/' . $target_image_name)) {
                            rename(SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/' . $target_image_name, SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/' . $normalimagename);
                        }

                        $uploads[$current_position]['normal'] = $normalimagename;
                        $uploads[$current_position]['preview'] = $previewimagename;

                        $query = 'UPDATE  `' . DB_PREFIX . '_' . $table . '` SET `' . $field_name . '` = ? WHERE `' . $key . '`=?';
                        $stmt = $DBC->query($query, array(serialize($uploads), $key_value));

                        return $this->getSuccessResponceJSON(array('imgsrc' => SITEBILL_MAIN_URL . '/img/data/' . $previewimagename));
                    } else {
                        return $this->getSuccessResponceJSON();
                    }


                }
                return $this->getErrorResponceJSON();

                break;
            }
            case 'delete' :
            {
                if ($admin_mode) {
                    $query = 'SELECT `' . $field_name . '` FROM `' . DB_PREFIX . '_' . $table . '` WHERE `' . $key . '`=? LIMIT 1';
                    $stmt = $DBC->query($query, array($key_value));
                } else {
                    $query = 'SELECT `' . $field_name . '` FROM `' . DB_PREFIX . '_' . $table . '` WHERE `' . $key . '`=? AND user_id=? LIMIT 1';
                    $stmt = $DBC->query($query, array($key_value, $user_id));
                }

                if (!$stmt) {
                    return json_encode($responce);
                }
                $ar = $DBC->fetch($stmt);
                if ($ar[$field_name] == '') {
                    return $this->getErrorResponceJSON();
                }
                $uploads = unserialize($ar[$field_name]);
                if ($table == 'user' and $field_name == 'imgfile') {
                    $uploads[0]['normal'] = $ar[$field_name];
                }
                if (!isset($uploads[$current_position])) {
                    return $this->getErrorResponceJSON();
                }

                if ($doc_mode) {
                    @unlink(SITEBILL_DOCUMENT_ROOT . '/img/mediadocs/' . $uploads[$current_position]['normal']);
                } else {
                    if ($uploads[$current_position]['remote'] === 'true') {
                        if ($this->getConfigValue('apps.sharder.api_key')) {
                            if (!is_object($this->sharder)) {
                                $this->sharder = new \sharder\lib\sharder();
                            }
                            $this->sharder->remove_remote_files(array($uploads[$current_position]['preview'], $uploads[$current_position]['normal']), $this->getServerFullUrl(true));
                        }
                    } else {
                        if (defined('STR_MEDIA') && STR_MEDIA == Sitebill::MEDIA_SAVE_FOLDER) {
                            $preview = $uploads[$current_position]['preview'];
                            $normal = $uploads[$current_position]['normal'];

                            $user_prefix = '';
                            if ($table == 'user' and $field_name == 'imgfile') {
                                $user_prefix = 'user/';
                            }

                            @unlink(MEDIA_FOLDER . '/' . $user_prefix . $preview);
                            @unlink(MEDIA_FOLDER . '/' . $user_prefix . $normal);
                            @unlink(MEDIA_FOLDER . '/nowatermark/' . $user_prefix . $normal);
                        } else {
                            $path = SITEBILL_DOCUMENT_ROOT . $this->storage_dir;
                            if ($table == 'user' and $field_name == 'imgfile') {
                                $path .= 'user/';
                            }

                            $preview = $uploads[$current_position]['preview'];
                            $normal = $uploads[$current_position]['normal'];
                            @unlink($path . $preview);
                            @unlink($path . $normal);
                            @unlink($path . 'nowatermark/' . $normal);
                        }
                    }
                }

                unset($uploads[$current_position]);
                $uploads = array_values($uploads);
                if (count($uploads) == 0) {
                    $nuploads = '';
                } else {
                    $nuploads = serialize($uploads);
                }
                $query = 'UPDATE `' . DB_PREFIX . '_' . $table . '` SET `' . $field_name . '`=? WHERE `' . $key . '`=?';
                $stmt = $DBC->query($query, array($nuploads, $key_value));
                if ($stmt) {
                    return $this->getSuccessResponceJSON();
                }
                return $this->getErrorResponceJSON();
                break;
            }
            case 'delete_all' :
            {
                return $this->delete_all($table, $key, $key_value, $field_name, $user_id, $admin_mode, $doc_mode);
                break;
            }
            case 'make_main' :
            {
                $DBC = DBC::getInstance();
                if ($admin_mode) {
                    $query = 'SELECT `' . $field_name . '` FROM `' . DB_PREFIX . '_' . $table . '` WHERE `' . $key . '`=? LIMIT 1';
                    $stmt = $DBC->query($query, array($key_value));
                } else {
                    $query = 'SELECT `' . $field_name . '` FROM `' . DB_PREFIX . '_' . $table . '` WHERE `' . $key . '`=? AND user_id=? LIMIT 1';
                    $stmt = $DBC->query($query, array($key_value, $user_id));
                }
                if (!$stmt) {
                    return $this->getErrorResponceJSON();
                }
                $ar = $DBC->fetch($stmt);
                if ($ar[$field_name] == '') {
                    return $this->getErrorResponceJSON();
                }
                $uploads = unserialize($ar[$field_name]);
                if (!isset($uploads[$current_position])) {
                    return $this->getErrorResponceJSON();
                }
                $temp = $uploads[$current_position];
                unset($uploads[$current_position]);
                array_unshift($uploads, $temp);
                $uploads = array_values($uploads);
                $query = 'UPDATE `' . DB_PREFIX . '_' . $table . '` SET `' . $field_name . '`=? WHERE `' . $key . '`=?';
                $stmt = $DBC->query($query, array(serialize($uploads), $key_value));
                if ($stmt) {
                    return $this->getSuccessResponceJSON();
                }
                return $this->getErrorResponceJSON();
                break;
            }
            case 'set_tags' :
            {
                $DBC = DBC::getInstance();
                if ($admin_mode) {
                    $query = 'SELECT `' . $field_name . '` FROM `' . DB_PREFIX . '_' . $table . '` WHERE `' . $key . '`=? LIMIT 1';
                    $stmt = $DBC->query($query, array($key_value));
                } else {
                    $query = 'SELECT `' . $field_name . '` FROM `' . DB_PREFIX . '_' . $table . '` WHERE `' . $key . '`=? AND user_id=? LIMIT 1';
                    $stmt = $DBC->query($query, array($key_value, $user_id));
                }
                if (!$stmt) {
                    return false;
                }
                $ar = $DBC->fetch($stmt);
                if ($ar[$field_name] == '') {
                    return false;
                }
                $uploads = unserialize($ar[$field_name]);
                if (!isset($uploads[$current_position])) {
                    return false;
                }
                if (empty($tags)) {
                    unset($uploads[$current_position]['tags']);
                } else {
                    $uploads[$current_position]['tags'] = $tags;
                }


                $query = 'UPDATE `' . DB_PREFIX . '_' . $table . '` SET `' . $field_name . '`=? WHERE `' . $key . '`=?';
                $stmt = $DBC->query($query, array(serialize($uploads), $key_value));
                if ($stmt) {
                    return $title;
                }
                exit();
                break;
            }
            case 'change_title' :
            {
                $title = htmlspecialchars($this->getRequestValue('title'));
                $title = substr($title, 0, 100);

                if ($admin_mode) {
                    $query = 'SELECT `' . $field_name . '` FROM `' . DB_PREFIX . '_' . $table . '` WHERE `' . $key . '`=? LIMIT 1';
                    $stmt = $DBC->query($query, array($key_value));
                } else {
                    $query = 'SELECT `' . $field_name . '` FROM `' . DB_PREFIX . '_' . $table . '` WHERE `' . $key . '`=? AND user_id=? LIMIT 1';
                    $stmt = $DBC->query($query, array($key_value, $user_id));
                }
                if (!$stmt) {
                    return false;
                }
                $ar = $DBC->fetch($stmt);
                if ($ar[$field_name] == '') {
                    return false;
                }
                $uploads = unserialize($ar[$field_name]);
                if (!isset($uploads[$current_position])) {
                    return false;
                }
                $uploads[$current_position]['title'] = $title;
                $query = 'UPDATE `' . DB_PREFIX . '_' . $table . '` SET `' . $field_name . '`=? WHERE `' . $key . '`=?';
                $stmt = $DBC->query($query, array(serialize($uploads), $key_value));
                if ($stmt) {
                    return $title;
                }
                exit();
                break;
            }
        }
    }

    function delete_all ($table, $key, $key_value, $field_name, $user_id, $admin_mode, $doc_mode) {
        $DBC = DBC::getInstance();

        if ($admin_mode) {
            $query = 'SELECT `' . $field_name . '` FROM `' . DB_PREFIX . '_' . $table . '` WHERE `' . $key . '`=? LIMIT 1';
            $stmt = $DBC->query($query, array($key_value));
        } else {
            $query = 'SELECT `' . $field_name . '` FROM `' . DB_PREFIX . '_' . $table . '` WHERE `' . $key . '`=? AND user_id=? LIMIT 1';
            $stmt = $DBC->query($query, array($key_value, $user_id));
        }

        if (!$stmt) {
            return $this->getErrorResponceJSON();
        }
        $ar = $DBC->fetch($stmt);
        if ($ar[$field_name] == '') {
            return $this->getErrorResponceJSON();
        }


        $uploads = unserialize($ar[$field_name]);

        if ($table == 'user' and $field_name == 'imgfile') {
            $uploads[0]['normal'] = $ar[$field_name];
        }


        if ($doc_mode) {
            foreach ($uploads as $upl) {
                @unlink(SITEBILL_DOCUMENT_ROOT . '/img/mediadocs/' . $upl['normal']);
            }
        } else {
            $remote_files = array();
            foreach ($uploads as $upl) {
                if ($upl['remote'] === 'true') {
                    //shard
                    array_push($remote_files, $upl['preview']);
                    array_push($remote_files, $upl['normal']);
                } else {
                    if (defined('STR_MEDIA') && STR_MEDIA == Sitebill::MEDIA_SAVE_FOLDER) {

                        $user_prefix = '';
                        if ($table == 'user' and $field_name == 'imgfile') {
                            $user_prefix = 'user/';
                        }

                        $preview = $upl['preview'];
                        $normal = $upl['normal'];
                        @unlink(MEDIA_FOLDER . '/' . $user_prefix . $preview);
                        @unlink(MEDIA_FOLDER . '/' . $user_prefix . $normal);
                        @unlink(MEDIA_FOLDER . '/nowatermark/' . $user_prefix . $normal);
                    } else {
                        $path = SITEBILL_DOCUMENT_ROOT . $this->storage_dir;
                        if ($table == 'user' and $field_name == 'imgfile') {
                            $path .= 'user/';
                        }

                        $preview = $upl['preview'];
                        $normal = $upl['normal'];
                        @unlink($path . $preview);
                        @unlink($path . $normal);
                        @unlink($path . 'nowatermark/' . $normal);
                    }
                }
            }
            if ($this->getConfigValue('apps.sharder.api_key') and count($remote_files) > 0) {
                if (!is_object($this->sharder)) {
                    $this->sharder = new \sharder\lib\sharder();
                }
                $this->sharder->remove_remote_files($remote_files, $this->getServerFullUrl(true));
            }

        }


        if ($admin_mode) {
            $query = 'UPDATE `' . DB_PREFIX . '_' . $table . '` SET `' . $field_name . '`=\'\' WHERE `' . $key . '`=? LIMIT 1';
            $stmt = $DBC->query($query, array($key_value));
        } else {
            $query = 'UPDATE `' . DB_PREFIX . '_' . $table . '` SET `' . $field_name . '`=\'\' WHERE `' . $key . '`=? AND user_id=? LIMIT 1';
            $stmt = $DBC->query($query, array($key_value, $user_id));
        }
        return $this->getSuccessResponceJSON();
    }
}
