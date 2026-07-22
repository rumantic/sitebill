<?php
/**
 * ImageTrait — delegation layer to ImageProcessor service
 *
 * GD/image processing methods delegated to ImageProcessor (apps/system/lib/service/).
 * DB-dependent CRUD operations remain in this trait.
 *
 * Для нового кода рекомендуется использовать ImageProcessor напрямую:
 *   $processor = new ImageProcessor($config);
 *   $processor->makePreview($src, $dst, 800, 600, 'jpg', 'smart');
 */
require_once __DIR__ . '/../service/ImageProcessor.php';

trait ImageTrait
{
    /** @var ImageProcessor|null Lazy-loaded service instance */
    private $imageProcessor;

    /**
     * Get or create ImageProcessor service instance
     * @return ImageProcessor
     */
    protected function getImageProcessor(): ImageProcessor
    {
        if ($this->imageProcessor === null) {
            $this->imageProcessor = new ImageProcessor($this);
        }
        return $this->imageProcessor;
    }

    /**
     * Edit image
     * @param string $action action
     * @param string $table_name table name
     * @param string $key key
     * @param int $record_id record ID
     * @return boolean
     */
    function editImageMulti($action, $table_name, $key, $record_id, $name_template = '')
    {
        if (!isset($record_id) or $record_id == 0) {
            return false;
        }
        $path = SITEBILL_DOCUMENT_ROOT . '/img/data/';
        $uploadify_path = SITEBILL_DOCUMENT_ROOT . $this->uploadify_dir;
        $session_key = (string)$this->get_session_key();
        $ra = array();
        //update image
        $images = $this->load_uploadify_images($session_key);
        if (!$images) {
            //Попробуем получить фото из внешнего запроса
            $images = $this->getExternalUploadifyImageArray();
            if (!$images) {
                return false;
            }
        }

        if ($action == 'data') {
            $DBC = DBC::getInstance();

            $avial_count = (int)$this->getConfigValue('photo_per_data');
            if ($avial_count == 0) {
                $avial_count = 1000;
            } else {
                $loaded = 0;
                $query = 'SELECT COUNT(data_image_id) AS cnt FROM ' . DB_PREFIX . '_' . $table_name . '_image WHERE ' . $key . '=' . $record_id;
                $stmt = $DBC->query($query);
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    $loaded = (int)$ar['cnt'];
                }
                $avial_count = $avial_count - $loaded;
                if ($avial_count < 1) {
                    $this->delete_uploadify_images($session_key);
                    return false;
                }
            }

            if (count($images) > $avial_count) {
                $images = array_slice($images, 0, $avial_count);
            }
        }


        foreach ($images as $image_name) {
            $i++;
            $need_prv = 0;
            $preview_name = '';
            if (!empty($image_name)) {
                $arr = explode('.', $image_name);
                $ext = strtolower($arr[count($arr) - 1]);

                $this->rotateImageToNormalPosition($uploadify_path . $image_name);

                if ((1 == $this->getConfigValue('seo_photo_name_enable')) and ($name_template != '')) {
                    $name_template = substr($name_template, 0, 150);
                    if ($i == 0) {
                        $preview_name_no_ext = $name_template;
                        $prv_no_ext = $name_template . "_prev";
                    } else {
                        $preview_name_no_ext = $name_template . "_" . $i;
                        $prv_no_ext = $name_template . "_prev" . $i;
                    }

                    if (file_exists($path . $preview_name_no_ext . "." . $ext)) {
                        $rand = rand(0, 1000);
                        while (file_exists($path . $preview_name_no_ext . "_" . $rand . "." . $ext)) {
                            $rand = rand(0, 1000);
                        }
                        $preview_name = $preview_name_no_ext . "_" . $rand . "." . $ext;
                        $prv = $prv_no_ext . "_" . $rand . "." . $ext;
                    } else {
                        $preview_name = $preview_name_no_ext . "." . $ext;
                        $prv = $prv_no_ext . "." . $ext;
                    }
                } else {
                    $preview_name = "img" . uniqid() . '_' . time() . "_" . $i . "." . $ext;
                    $prv = "prv" . uniqid() . '_' . time() . "_" . $i . "." . $ext;
                    $preview_name_tmp = "_tmp" . uniqid() . '_' . time() . "_" . $i . "." . $ext;
                }

                if (in_array($ext, array('jpg', 'jpeg', 'gif', 'png'))) {

                    $big_width = $this->getConfigValue($action . '_image_big_width');
                    if ($big_width == '') {
                        $big_width = $this->getConfigValue('news_image_big_width');
                    }
                    $big_height = $this->getConfigValue($action . '_image_big_height');
                    if ($big_height == '') {
                        $big_height = $this->getConfigValue('news_image_big_height');
                    }

                    $preview_width = $this->getConfigValue($action . '_image_preview_width');
                    if ($preview_width == '') {
                        $preview_width = $this->getConfigValue('news_image_preview_width');
                    }
                    $preview_height = $this->getConfigValue($action . '_image_preview_height');
                    if ($preview_height == '') {
                        $preview_height = $this->getConfigValue('news_image_preview_height');
                    }

                    if (defined('STR_MEDIA') && STR_MEDIA == Sitebill::MEDIA_SAVE_FOLDER) {
                        if (defined('STR_MEDIA_FOLDERFDAYS') && STR_MEDIA_FOLDERFDAYS === 1) {
                            $foldeformat = 'Ymd';
                        } else {
                            $foldeformat = 'Ym';
                        }
                        $folder_name = date($foldeformat, time());
                        $locs = MEDIA_FOLDER . '/' . $folder_name;
                        if (!is_dir($locs)) {
                            if (!mkdir($locs) && !is_dir($locs)) {
                                throw new \RuntimeException(sprintf('Directory "%s" was not created', $locs));
                            }
                        }
                        $preview_name = $folder_name . '/' . $preview_name;
                        $prv = $folder_name . '/' . $prv;
                    }

                    $rn = $this->makePreview($uploadify_path . $image_name, $path . $preview_name, $big_width, $big_height, $ext, 1);
                    if (1 == $this->getConfigValue('apps.realty.preview_smart_resizing') && $action == 'data') {
                        $rp = $this->makePreview($uploadify_path . $image_name, $path . $prv, $preview_width, $preview_height, $ext, 'smart');
                    } else {
                        $rp = $this->makePreview($uploadify_path . $image_name, $path . $prv, $preview_width, $preview_height, $ext, 'width');
                    }
                    if ($rp && $rn) {
                        if (1 == $this->getConfigValue('apps.watermark.printanywhere')) {
                            $this->doWatermark($path . $preview_name, $path . $prv);
                        }

                        /* На случай, если сервер выставляет на загруженные файлы права 0600 */
                        chmod($path . $preview_name, 0644);
                        chmod($path . $prv, 0644);
                        /**/

                        $ra[$i]['preview'] = $prv;
                        $ra[$i]['normal'] = $preview_name;
                    }
                }
            }
        }

        $this->add_image_records($ra, $table_name, $key, $record_id);
        $this->delete_uploadify_images($this->get_session_key());
        return $ra;
    }

    /**
     * Create watermark printer instance
     * @param bool $allow_local Allow to use localized version (used for printing on adverts media)
     * @return Watermark
     */
    function createWatermarkInstance($allow_local = false){
        return $this->getImageProcessor()->createWatermarkInstance($allow_local);
    }

    function doWatermark($normal_image, $preview_image) {
        if (!$this->watermark_inst) {
            $this->watermark_inst = $this->getImageProcessor()->createWatermarkInstance();
        }
        return $this->getImageProcessor()->doWatermark($normal_image, $preview_image, $this->watermark_inst);
    }

    /**
     * Эта функция устанавливает массив с картинками для эмитации загрузки картинок в UPLOADIFY
     * Используется в APPS.API для загрузки картинок из мобильного приложения
     * @param $_image_array - массив с картинками
     * @return void
     */
    function setExternalUploadifyImageArray($_image_array)
    {
        $this->external_uploadify_image_array = $_image_array;
    }

    function getExternalUploadifyImageArray()
    {
        return $this->external_uploadify_image_array;
    }

    function get_docuploads_extensions()
    {
        return ImageProcessor::getDocUploadsExtensions();
    }

    /**
     * Rotate image to normal position if need (by exif-data)
     * @param string $image path to image
     */
    function rotateImageToNormalPosition($image){
        $this->getImageProcessor()->rotateImageToNormalPosition($image);
    }

    function appendDocUploads($table, $field, $pk_field, $record_id)
    {
        $field_name = $field['name'];
        $parameters = $field['parameters'];
        $session_key = (string)$this->get_session_key();
        $action = $table;
        if (!isset($record_id) || $record_id == 0) {
            return false;
        }

        $DBC = DBC::getInstance();

        $path = SITEBILL_DOCUMENT_ROOT . '/img/mediadocs/';
        $uploadify_path = SITEBILL_DOCUMENT_ROOT . $this->uploadify_dir;

        $ra = array();
        $uploads = $this->load_uploadify_images($session_key, $field_name);
        if (!$uploads) {
            return false;
        }


        $query = 'SELECT `' . $field_name . '` FROM ' . DB_PREFIX . '_' . $table . ' WHERE `' . $pk_field . '`=? LIMIT 1';

        $stmt = $DBC->query($query, array($record_id));
        if (!$stmt) {
            return false;
        }
        $ar = $DBC->fetch($stmt);

        if ($ar[$field_name] === '') {
            $attached_yet = array();
        } else {
            $attached_yet = unserialize($ar[$field_name]);
        }
        //print_r($attached_yet);
        $i = 0;
        $max_filesize = (int)str_replace('M', '', ini_get('upload_max_filesize'));
        if (isset($parameters['max_file_size']) && (int)$parameters['max_file_size'] != 0) {
            $max_filesize = (int)$parameters['max_file_size'];
        }
        if (isset($parameters['accepted'])) {
            $av = explode(',', $parameters['accepted']);
        }
        $allowed_exts = $this->get_docuploads_extensions();
        if (!empty($av)) {
            foreach ($av as $k => $v) {
                $v = trim(ltrim($v, '.'));
                if ($v == '') {
                    unset($av[$k]);
                } else {
                    $av[$k] = $v;
                }
            }
        }
        if (!empty($av)) {
            $allowed_exts = $av;
        }

        foreach ($uploads as $image_name) {
            $i++;

            if (!empty($image_name)) {

                $arr = explode('.', $image_name);
                $ext = strtolower(end($arr));

                if (!in_array($ext, $allowed_exts)) {
                    continue;
                }
                $filesize = filesize($uploadify_path . $image_name) / (1024 * 1024);
                if ($filesize > $max_filesize) {
                    continue;
                }
                if ($this->getConfigValue('use_native_file_name_on_uploadify')) {
                    $path_parts = pathinfo($image_name);
                    $file_name = $path_parts['filename'] . '.' . $path_parts['extension'];
                } else {
                    $file_name = "doc" . uniqid() . '_' . time() . '_' . $i . '.' . $ext;
                }
                $file_index = 1;
                while (file_exists($path . $file_name)) {
                    $i++;
                    if ($this->getConfigValue('use_native_file_name_on_uploadify')) {
                        $file_name = $path_parts['filename'] . '(' . $file_index . ')' . '.' . $path_parts['extension'];
                    } else {
                        $file_name = "doc" . uniqid() . '_' . time() . '_' . $i . '.' . $ext;
                    }
                    $file_index++;
                }

                if ( !is_dir($path) ) {
                    if ( !mkdir($path)) {
                        throw new \Exception('Cant create directory '.$path);
                    }
                }
                if ( !is_dir($path) ) {
                    throw new \Exception('Directory '.$path.' does not exist');
                }


                if (copy($uploadify_path . $image_name, $path . $file_name)) {
                    chmod($path . $file_name, 0644);
                    /**/
                    $ra[$i]['preview'] = '';
                    $ra[$i]['normal'] = $file_name;

                    $attached_yet[] = array('preview' => '', 'normal' => $file_name, 'type' => 'doc', 'mime' => $ext);
                }
            }
        }

        $query = 'UPDATE ' . DB_PREFIX . '_' . $table . ' SET `' . $field_name . '`=? WHERE `' . $pk_field . '`=?';
        if (count($attached_yet) > 0) {
            $stmt = $DBC->query($query, array(serialize($attached_yet), $record_id));
        } else {
            $stmt = $DBC->query($query, array('', $record_id));
        }
        //$this->add_image_records($ra, $table_name, $key, $record_id);
        $this->delete_uploadify_images($session_key, $field_name);
        return $ra;
    }

    function appendUploads($table, $field, $pk_field, $record_id, $name_template = '')
    {
        $field_name = $field['name'];
        $uploadify_field_name = $field_name;
        if (isset($field['uploadify_field_name'])) {
            $uploadify_field_name = $field['uploadify_field_name'];
        }
        $parameters = $field['parameters'];
        $session_key = (string)$this->get_session_key();


        $action = $table;
        if (!isset($record_id) || $record_id == 0) {
            //$this->riseError('record id is null');
            return false;
        }

        $DBC = DBC::getInstance();

        $path = SITEBILL_DOCUMENT_ROOT . '/img/data/';
        $uploadify_path = SITEBILL_DOCUMENT_ROOT . $this->uploadify_dir;

        $ra = array();
        $uploads = $this->load_uploadify_images($session_key, $uploadify_field_name);
        if (!$uploads) {
            $uploads = $this->getExternalUploadifyImageArray();
            if (!$uploads) {
                return false;
            }
        }

        // Max image count per field
        $max_img_count = -1;
        if (isset($parameters['max_img_count']) && $parameters['max_img_count'] != '') {
            $max_img_count = (int)$parameters['max_img_count'];
        }

        // Rules for determining max image count per field
        $maximgcountextendrules = '';
        if (isset($parameters['max_img_count_ext']) && '' != $parameters['max_img_count_ext']) {
            $maximgcountextendrules = $parameters['max_img_count_ext'];
        }

        $controlledfields = array();
        $maxsizerules = array();
        if ($maximgcountextendrules != '') {
            $rulesparts = explode(':', $maximgcountextendrules);
            $size = (int)$rulesparts[0];
            if ($size > 0 && count($rulesparts) > 1) {
                unset($rulesparts[0]);
                $conditions = array();
                foreach ($rulesparts as $rule) {
                    $oneruleparts = explode(',', $rule);
                    if (count($oneruleparts) === 3) {
                        $controlledfields[$oneruleparts[0]] = 0;
                        $conditions[] = $oneruleparts;
                    }
                }
                $maxsizerules[] = array(
                    'size' => $size,
                    'conditions' => $conditions
                );
            }
        }

        $selectedfields = array();

        $selectedfields[] = '`' . $field_name . '`';
        if (!empty($controlledfields)) {
            foreach ($controlledfields as $controlledfield => $name) {
                $selectedfields[] = '`' . $controlledfield . '`';
            }
        }

        // Include image_cache for data table to support migration from cache to regular uploads
        $has_image_cache = ($table === 'data' && $field_name === 'image');
        if ($has_image_cache) {
            $selectedfields[] = '`image_cache`';
        }

        //$query = 'SELECT `' . $field_name . '` FROM ' . DB_PREFIX . '_' . $table . ' WHERE `' . $pk_field . '`=? LIMIT 1';
        $query = 'SELECT ' . implode(', ', $selectedfields) . ' FROM ' . DB_PREFIX . '_' . $table . ' WHERE `' . $pk_field . '`=? LIMIT 1';

        $stmt = $DBC->query($query, array($record_id));
        if (!$stmt) {
            $this->riseError('query = ' . $query . ', db error: ' . $DBC->getLastError());
            return false;
        }
        $advertdata = $DBC->fetch($stmt);

        if ($advertdata[$field_name] === '') {
            // When regular image field is empty but image_cache has data (from parser),
            // migrate cached images into regular uploads format so they are preserved
            // alongside newly uploaded photos.
            if ($has_image_cache && !empty($advertdata['image_cache'])) {
                $image_cache_array = @unserialize($advertdata['image_cache'], ['allowed_classes' => false]);
                if (is_array($image_cache_array) && count($image_cache_array) > 0) {
                    $attached_yet = $this->convertImageCacheToUploads($image_cache_array);
                } else {
                    $attached_yet = array();
                }
            } else {
                $attached_yet = array();
            }
        } else {
            $attached_yet = unserialize($advertdata[$field_name], ['allowed_classes' => false]);
        }


        $i = 0;
        $max_filesize = (int)str_replace('M', '', ini_get('upload_max_filesize'));
        if (isset($parameters['max_file_size']) && (int)$parameters['max_file_size'] != 0) {
            $max_filesize = (int)$parameters['max_file_size'];
        }

        if ($max_img_count > -1) {
            if (!empty($maxsizerules)) {
                foreach ($maxsizerules as $maxsizerule) {
                    $condsok = true;
                    foreach ($maxsizerule['conditions'] as $condition) {
                        $operand = $condition[1];
                        $field = $condition[0];
                        $value = $condition[2];
                        switch ($operand) {
                            case 'eq' :
                            {
                                if ($advertdata[$field] != $value) {
                                    $condsok = false;
                                }
                                break;
                            }
                            case 'neq' :
                            {
                                if ($advertdata[$field] == $value) {
                                    $condsok = false;
                                }
                                break;
                            }
                            case 'gt' :
                            {
                                if ($advertdata[$field] <= $value) {
                                    $condsok = false;
                                }
                                break;
                            }
                            case 'lt' :
                            {
                                if ($advertdata[$field] >= $value) {
                                    $condsok = false;
                                }
                                break;
                            }
                        }
                    }

                    if ($condsok) {
                        $max_img_count = $maxsizerule['size'];
                        break;
                    }

                }
            }
        }


        if ($max_img_count > -1) {
            $last_count = $max_img_count - count($attached_yet);
            if ($last_count > 0) {
                $uploads = array_slice($uploads, 0, $last_count);
            } else {
                $uploads = array();
            }
        }

        if (!empty($uploads)) {

            $folder_name = '';
            if (defined('STR_MEDIA') && STR_MEDIA == Sitebill::MEDIA_SAVE_FOLDER) {
                if (defined('STR_MEDIA_FOLDERFDAYS') && STR_MEDIA_FOLDERFDAYS === 1) {
                    $foldeformat = 'Ymd';
                } else {
                    $foldeformat = 'Ym';
                }
                $folder_name = date($foldeformat, time());
                $locs = MEDIA_FOLDER . '/' . $folder_name;
                if (!is_dir($locs)) {
                    mkdir($locs);
                }
                $preview_name = $folder_name . '/' . $preview_name;
                $prv = $folder_name . '/' . $prv;
            } elseif (defined('STR_MEDIA_DIVIDED') && STR_MEDIA_DIVIDED == 1) {
                $fold1 = rand(0, 99);
                $fold2 = rand(0, 99);
                if ($fold1 < 10) {
                    $fold1 = '0' . $fold1;
                }
                if ($fold2 < 10) {
                    $fold2 = '0' . $fold2;
                }
                $folder_name = $fold1 . '/' . $fold2;
                $locs = MEDIA_FOLDER . '/' . $fold1;
                if (!is_dir($locs)) {
                    mkdir($locs);
                }
                $locs = MEDIA_FOLDER . '/' . $fold1 . '/' . $fold2;
                if (!is_dir($locs)) {
                    mkdir($locs);
                }
                /*
                 * Вариант вложенных папок для стандартных настроек
                 * папки от /000/000/ до /1f4/1f4/
                 * 500 вариантов / 500 вариантов
                 * в итоге не более 500 вариантов папок на одном уровне
                $fold1 = dechex(rand(0, 500));
                $fold2 = dechex(rand(0, 500));
                if(strlen($fold1) == 1){
                    $fold1 = '00' . $fold1;
                }elseif(strlen($fold1) == 2){
                    $fold1 = '0' . $fold1;
                }
                if(strlen($fold2) < 2){
                    $fold2 = '0' . $fold2;
                }elseif(strlen($fold2) == 2){
                    $fold1 = '0' . $fold2;
                }
                $folder_name = $fold1 . '/' . $fold2;
                $locs = MEDIA_FOLDER . '/' . $fold1;
                if (!is_dir($locs)) {
                    mkdir($locs);
                }
                $locs = MEDIA_FOLDER . '/' . $fold1 . '/' . $fold2;
                if (!is_dir($locs)) {
                    mkdir($locs);
                }
                */

            } else {
                $folder_name = '';
            }

            $uniq_file_name = uniqid() . '_' . time();

            foreach ($uploads as $imgkey => $image_name) {
                $filesize = filesize($uploadify_path . $image_name) / (1024 * 1024);
                if ($filesize > $max_filesize) {
                    continue;
                }
                if (!empty($image_name)) {
                    $arr = explode('.', $image_name);
                    $ext = strtolower(end($arr));

                    $this->rotateImageToNormalPosition($uploadify_path . $image_name);

                    if ((1 == $this->getConfigValue('seo_photo_name_enable')) and ($name_template != '')) {
                        $name_template = substr($name_template, 0, 150);
                        if ($imgkey == 0) {
                            $preview_name_no_ext = $name_template;
                            $prv_no_ext = $name_template . "_prev";
                        } else {
                            $preview_name_no_ext = $name_template . "_" . $imgkey;
                            $prv_no_ext = $name_template . "_prev" . $imgkey;
                        }

                        if (file_exists($path . $preview_name_no_ext . "." . $ext)) {
                            $rand = rand(0, 1000);
                            while (file_exists($path . $preview_name_no_ext . "_" . $rand . "." . $ext)) {
                                $rand = rand(0, 1000);
                            }
                            $preview_name = $preview_name_no_ext . "_" . $rand . "." . $ext;
                            $prv = $prv_no_ext . "_" . $rand . "." . $ext;
                        } else {
                            $preview_name = $preview_name_no_ext . "." . $ext;
                            $prv = $prv_no_ext . "." . $ext;
                        }
                    } else {
                        $preview_name = 'img' . $uniq_file_name . '_' . ($imgkey+1) . '.' . $ext;
                        $prv = 'prv' . $uniq_file_name . '_' . ($imgkey+1) . '.' . $ext;
                    }

                    if (in_array($ext, array('jpg', 'jpeg', 'gif', 'png', 'webp', 'jfif'))) {

                        list($big_width, $big_height, $preview_width, $preview_height) = $this->getUploadImageSizeParameters($action, $parameters);

                        if ($folder_name != '') {
                            $preview_name = $folder_name . '/' . $preview_name;
                            $prv = $folder_name . '/' . $prv;
                        }

                        if (isset($parameters['normal_smart_resizing']) && 1 === (int)$parameters['normal_smart_resizing']) {
                            $rn = $this->makePreview($uploadify_path . $image_name, $path . $preview_name, $big_width, $big_height, $ext, 'smart');
                        } else {
                            $rn = $this->makePreview($uploadify_path . $image_name, $path . $preview_name, $big_width, $big_height, $ext, 1);
                        }

                        $preview_smart_resizing = false;
                        if (isset($parameters['preview_smart_resizing'])) {
                            if (1 === (int)$parameters['preview_smart_resizing']) {
                                $preview_smart_resizing = true;
                            } else {
                                $preview_smart_resizing = false;
                            }
                        } elseif (1 == $this->getConfigValue('apps.realty.preview_smart_resizing') && $action == 'data') {
                            $preview_smart_resizing = true;
                        }

                        if ($preview_smart_resizing) {
                            $rp = $this->makePreview($uploadify_path . $image_name, $path . $prv, $preview_width, $preview_height, $ext, 'smart');
                        } else {
                            $rp = $this->makePreview($uploadify_path . $image_name, $path . $prv, $preview_width, $preview_height, $ext, 'width');
                        }


                        if ($rn && $rp) {
                            if (/*$table !=='data' && */1 === (int)$this->getConfigValue('apps.watermark.printanywhere')) {
                                $this->doWatermark($path . $preview_name, $path . $prv);
                            }

                            /* На случай, если сервер выставляет на загруженные файлы права 0600 */
                            chmod($path . $preview_name, 0644);
                            chmod($path . $prv, 0644);
                            /**/
                            $ra[$imgkey]['preview'] = $prv;
                            $ra[$imgkey]['normal'] = $preview_name;
                        }
                        $preview_params = $this->get_image_info($path . $prv);
                        $normal_params = $this->get_image_info($path . $preview_name);

                    } elseif (in_array($ext, array('svg'))) {
                        if ($folder_name != '') {
                            $preview_name = $folder_name . '/' . $image_name;
                        } else {
                            $preview_name = $image_name;
                        }
                        $prv = $preview_name;
                        $this->makeMove($uploadify_path . $image_name, $path . $preview_name);

                        $ra[$imgkey]['preview'] = $preview_name;
                        $ra[$imgkey]['normal'] = $preview_name;
                        $rn = true;
                        $rp = true;
                        $preview_params = $this->get_svg_info($path . $preview_name);
                        $normal_params = $this->get_svg_info($path . $preview_name);
                    }
                    if ($rn && $rp) {
                        if ($this->getConfigValue('apps.sharder.enable')) {
                            $shard_result = $this->sharding(array($preview_name, $prv));
                            if ($shard_result) {
                                list($preview_name, $prv) = $shard_result;
                                $remote = 'true';
                            }
                        } else {
                            $remote = 0;
                        }
                        $attached_yet[] = array(
                            'preview' => $prv,
                            'normal' => $preview_name,
                            'type' => 'graphic',
                            'mime' => $ext,
                            'remote' => $remote,
                            'preview_params' => $preview_params,
                            'normal_params' => $normal_params,
                        );
                    }
                }
            }

            $query = 'UPDATE ' . DB_PREFIX . '_' . $table . ' SET `' . $field_name . '`=? WHERE `' . $pk_field . '`=?';
            if (is_array($attached_yet) and count($attached_yet) > 0) {
                $stmt = $DBC->query($query, array(serialize($attached_yet), $record_id));
            } else {
                $stmt = $DBC->query($query, array('', $record_id));
            }

            // After saving combined images, clear image_cache to avoid stale display in grids
            if ($has_image_cache && !empty($advertdata['image_cache'])) {
                $clear_cache_query = 'UPDATE ' . DB_PREFIX . '_' . $table . ' SET `image_cache`=?, `image_parsed`=1 WHERE `' . $pk_field . '`=?';
                $DBC->query($clear_cache_query, array('', $record_id));
            }
        } else {
            // $this->riseError('empty uploads');
        }

        $this->delete_uploadify_images($session_key, $uploadify_field_name);
        return $ra;
    }

    /**
     * Convert image_cache URL array to uploads-compatible format.
     * image_cache stores plain remote URLs (from parser).
     * Uploads format requires array of [preview, normal, remote] entries.
     *
     * @param array $image_array Array of remote image URLs
     * @return array Uploads-format array
     */
    protected function convertImageCacheToUploads($image_array)
    {
        $result = array();
        foreach ($image_array as $image) {
            if (empty($image)) {
                continue;
            }

            // Apply mirror domain substitution if configured (sharder mirroring)
            if ($this->getConfigValue('apps.sharder.mirroring.enable')) {
                $image = str_replace(
                    $this->getConfigValue('apps.sharder.mirroring.find'),
                    $this->getConfigValue('apps.sharder.mirroring.replace'),
                    $image
                );
            }

            // Generate smaller preview for avito 640x480 images
            if (preg_match('/640x480/', $image) && preg_match('/avito/', $image)) {
                $preview = str_replace('640x480', '208x156', $image);
            } else {
                $preview = $image;
            }

            $result[] = array(
                'preview' => $preview,
                'normal' => $image,
                'remote' => 'true',
            );
        }
        return $result;
    }

    /**
     * Return sizes for uplod media from config params or model property parameters
     * @param string $action Action\Model name
     * @param array $parameters Model property parameters array
     * @return array Detected sizes
     */
    protected function getUploadImageSizeParameters($action, $parameters = []){
        return $this->getImageProcessor()->getUploadImageSizeParameters($action, $parameters);
    }

    /**
     * Return array of sizes for svg-media
     * @param string $svg_file_name Target file
     * @return array
     */
    function get_svg_info($svg_file_name)
    {
        return $this->getImageProcessor()->getSvgInfo($svg_file_name);
    }

    /**
     * Return array of sizes for graphic media
     * @param string $file_name Target file
     * @return array
     */
    function get_image_info($file_name)
    {
        return $this->getImageProcessor()->getImageInfo($file_name);
    }

    function sharding($files)
    {
        if ($this->getConfigValue('apps.sharder.enable')) {
            if (!is_object($this->sharder)) {
                $this->sharder = new \sharder\lib\sharder();
            }

            $result = $this->sharder->shard($files, $this->getServerFullUrl(true));
            if ($this->sharder->getError()) {
                $this->riseError('Error on sharding: ' . $this->sharder->getError());
                return false;
            }
            return $result;
        }
        return $files;
    }

    /**
     * Rotate target media
     * @param string $source_image Path to media
     * @param string $destination Path to destination
     * @param integer $degree Degrees to rotate
     * @return string|void
     */
    function rotateImageInDestination($source_image, $destination, $degree)
    {
        return $this->getImageProcessor()->rotateImageInDestination($source_image, $destination, $degree);
    }

    /**
     * Add image data records
     * @param array $images images
     * @param string $table_name table name
     * @param string $key key
     * @param int $record_id record id
     * @return boolean
     */
    function add_image_records($images, $table_name, $key, $record_id)
    {

        $DBC = DBC::getInstance();
        foreach ($images as $item_id => $item_array) {
            $query = 'INSERT INTO ' . IMAGE_TABLE . ' (normal, preview) VALUES (?, ?)';
            $stmt = $DBC->query($query, array($item_array['normal'], $item_array['preview']));
            if ($stmt) {
                $image_id = $DBC->lastInsertId();
                $this->add_table_image_record($table_name, $key, $record_id, $image_id);
            }
        }
    }

    /**
     * Add table_image record
     * @param int $record_id record id
     * @param int $image_id image id
     * @return boolean
     */
    function add_table_image_record($table_name, $key, $record_id, $image_id)
    {
        $DBC = DBC::getInstance();
        $query = 'INSERT INTO ' . DB_PREFIX . '_' . $table_name . '_image (' . $key . ', image_id, sort_order) values (?, ?, ?)';
        $DBC->query($query, array($record_id, $image_id, $image_id));
        return true;
    }

    /**
     * Delete image
     * @param string $table_name table name
     * @param int $image_id image id
     * @return boolean
     */
    function deleteImage($table_name, $image_id)
    {
        $DBC = DBC::getInstance();
        $query = 'DELETE FROM ' . DB_PREFIX . '_' . $table_name . '_image WHERE image_id=?';
        $DBC->query($query, array($image_id));

        $this->deleteImageFiles($image_id);

        $query = 'DELETE FROM ' . IMAGE_TABLE . ' WHERE image_id=?';
        $DBC->query($query, array($image_id));
        return true;
    }

    function makeImageMain($action, $image_id, $key, $key_value)
    {
        $DBC = DBC::getInstance();
        $query = 'SELECT image_id FROM ' . DB_PREFIX . '_' . $action . '_image WHERE `' . $key . '`=? ORDER BY sort_order';
        $stmt = $DBC->query($query, array($key_value));
        $imgs = array();
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $imgs[] = $ar['image_id'];
            }
        }

        if (!empty($imgs)) {
            $imgids = array_flip($imgs);
            if (isset($imgids[$image_id])) {
                unset($imgs[$imgids[$image_id]]);
                array_unshift($imgs, $image_id);
            }
            $query = 'UPDATE ' . DB_PREFIX . '_' . $action . '_image SET sort_order=? WHERE image_id=?';
            foreach ($imgs as $k => $v) {
                $DBC->query($query, array($k + 1, $v));
            }
        }
    }

    function rotateImage2($thisimage, $isWatermark, $degree, $parameters)
    {

        if ($thisimage['normal'] == '') {
            return '';
        }

        $arr = explode('.', $thisimage['normal']);
        $ext = end($arr);

        if ($isWatermark && file_exists(SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/' . $thisimage['normal'])) {
            $source_image = SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/' . $thisimage['normal'];
        } elseif (file_exists(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['normal'])) {
            $source_image = SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['normal'];
        } else {
            $source_image = '';
        }

        $target_image_name = $thisimage['normal'];
        $target_preview_name = $thisimage['preview'];

        if ($source_image == '') {
            return '';
        }

        $source_preview = SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['preview'];

        $big_sizes = getimagesize($source_image);
        $prev_sizes = getimagesize($source_preview);

        if ($ext == 'jpg' || $ext == 'jpeg') {
            $source_image_res = imagecreatefromjpeg($source_image);
        } elseif ($ext == 'png') {
            $source_image_res = imagecreatefrompng($source_image);
        } elseif ($ext == 'gif') {
            $source_image_res = imagecreatefromgif($source_image);
        }


        $preview_width = $parameters['prev_width'];
        $preview_height = $parameters['prev_height'];

        if (1 == $parameters['preview_smart_resizing']) {
            $preview_mode = 'smart';
        } else {
            $preview_mode = 'width';
        }


        if ($isWatermark) {
            if ($ext == 'jpg' || $ext == 'jpeg') {
                $im = imagerotate($source_image_res, $degree, 0);
                @imagejpeg($im, SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/' . $target_image_name, (int)$this->getConfigValue('jpeg_quality'));
                imagejpeg($im, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $target_image_name, (int)$this->getConfigValue('jpeg_quality'));
            } elseif ($ext == 'png') {
                $im = imagerotate($source_image_res, $degree, 0);
                @imagepng($im, SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/' . $target_image_name, (int)$this->getConfigValue('png_quality'));
                imagepng($im, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $target_image_name, (int)$this->getConfigValue('png_quality'));
            } elseif ($ext == 'gif') {
                $im = imagerotate($source_image_res, $degree, 0);
                @imagegif($im, SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/' . $target_image_name);
                imagegif($im, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $target_image_name);
            } elseif ($ext == 'webp') {
                $im = imagerotate($source_image_res, $degree, 0);
                @imagewebp($im, SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/' . $target_image_name);
                imagewebp($im, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $target_image_name);
            }

            $rp = $this->makePreview($source_image, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $target_preview_name, $preview_width, $preview_height, $ext, $preview_mode);

            require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/watermark/watermark.php';
            $watermark_inst = new Watermark();
            $watermark_inst->setPosition($this->getConfigValue('apps.watermark.position'));
            $watermark_inst->setOffsets(array(
                $this->getConfigValue('apps.watermark.offset_left'),
                $this->getConfigValue('apps.watermark.offset_top'),
                $this->getConfigValue('apps.watermark.offset_right'),
                $this->getConfigValue('apps.watermark.offset_bottom')
            ));

            $watermark_inst->printWatermark(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $target_image_name);

        } else {
            if ($ext == 'jpg' || $ext == 'jpeg') {
                $im = imagerotate($source_image_res, $degree, 0);
                imagejpeg($im, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $target_image_name, (int)$this->getConfigValue('jpeg_quality'));
            } elseif ($ext == 'png') {
                $im = imagerotate($source_image_res, $degree, 0);
                imagepng($im, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $target_image_name, (int)$this->getConfigValue('png_quality'));
            } elseif ($ext == 'gif') {
                $im = imagerotate($source_image_res, $degree, 0);
                imagegif($im, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $target_image_name);
            } elseif ($ext == 'webp') {
                $im = imagerotate($source_image_res, $degree, 0);
                imagewebp($im, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $target_image_name);
            }
            $rp = $this->makePreview($source_image, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $target_preview_name, $preview_width, $preview_height, $ext, $preview_mode);
        }

        return true;
    }

    function rotateImage($action, $image_id, $key, $key_value, $rot_dir)
    {
        if ($rot_dir == 'ccw') {
            $degree = 90;
        } else {
            $degree = -90;
        }

        $DBC = DBC::getInstance();
        $query = 'SELECT normal, preview FROM ' . DB_PREFIX . '_image WHERE `image_id`=? LIMIT 1';
        $normal = '';
        $stmt = $DBC->query($query, array($image_id));
        $imgs = array();
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $thisimage = $ar;
        }

        if ($thisimage['normal'] == '') {
            return '';
        }

        $arr = explode('.', $thisimage['normal']);
        $ext = end($arr);

        $hasWatermark = false;
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/' . $thisimage['normal'])) {
            $source_image = SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/' . $thisimage['normal'];
            $hasWatermark = true;
        } elseif (file_exists(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['normal'])) {
            $source_image = SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['normal'];
        } else {
            $source_image = '';
        }

        if ($source_image == '') {
            return '';
        }

        $source_preview = SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['preview'];

        $big_sizes = getimagesize($source_image);
        $prev_sizes = getimagesize($source_preview);

        if ($ext == 'jpg' || $ext == 'jpeg') {
            $source_image_res = imagecreatefromjpeg($source_image);
        } elseif ($ext == 'png') {
            $source_image_res = imagecreatefrompng($source_image);
        } elseif ($ext == 'gif') {
            $source_image_res = imagecreatefromgif($source_image);
        } elseif ($ext == 'webp') {
            $source_image_res = imagecreatefromwebp($source_image);
        }

        $preview_width = $this->getConfigValue($action . '_image_preview_width');
        if ($preview_width == '') {
            $preview_width = $this->getConfigValue('news_image_preview_width');
        }
        $preview_height = $this->getConfigValue($action . '_image_preview_height');
        if ($preview_height == '') {
            $preview_height = $this->getConfigValue('news_image_preview_height');
        }
        if (1 == $this->getConfigValue('apps.realty.preview_smart_resizing') && $action == 'data') {
            $preview_mode = 'smart';
        } else {
            $preview_mode = 'width';
        }

        if ($hasWatermark) {
            if ($ext == 'jpg' || $ext == 'jpeg') {
                $im = imagerotate($source_image_res, $degree, 0);
                imagejpeg($im, SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/' . $thisimage['normal'], (int)$this->getConfigValue('jpeg_quality'));
                imagejpeg($im, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['normal'], 100);
            } elseif ($ext == 'png') {
                $im = imagerotate($source_image_res, $degree, 0);
                imagepng($im, SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/' . $thisimage['normal'], (int)$this->getConfigValue('png_quality'));
                imagepng($im, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['normal'], (int)$this->getConfigValue('png_quality'));
            } elseif ($ext == 'gif') {
                $im = imagerotate($source_image_res, $degree, 0);
                imagegif($im, SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/' . $thisimage['normal']);
                imagegif($im, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['normal']);
            } elseif ($ext == 'webp') {
                $im = imagerotate($source_image_res, $degree, 0);
                imagewebp($im, SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/' . $thisimage['normal']);
                imagewebp($im, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['normal']);
            }

            $rp = $this->makePreview(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['normal'], SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['preview'], $preview_width, $preview_height, $ext, 'smart');
        } else {
            if ($ext == 'jpg' || $ext == 'jpeg') {
                $im = imagerotate($source_image_res, $degree, 0);
                imagejpeg($im, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['normal'], (int)$this->getConfigValue('jpeg_quality'));
            } elseif ($ext == 'png') {
                $im = imagerotate($source_image_res, $degree, 0);
                imagepng($im, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['normal'], (int)$this->getConfigValue('png_quality'));
            } elseif ($ext == 'gif') {
                $im = imagerotate($source_image_res, $degree, 0);
                imagegif($im, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['normal']);
            } elseif ($ext == 'webp') {
                $im = imagerotate($source_image_res, $degree, 0);
                imagewebp($im, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['normal']);
            }
            $rp = $this->makePreview(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['normal'], SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['preview'], $preview_width, $preview_height, $ext, 'smart');
        }

        return;
    }

    /**
     * Reorder image
     * @param $action
     * @param $image_id
     * @param $key
     * @param $key_value
     * @param $direction
     * @return mixed
     */

    function reorderImage($action, $image_id, $key, $key_value, $direction)
    {
        $DBC = DBC::getInstance();
        $query = 'SELECT ' . $action . '_image_id, sort_order FROM ' . DB_PREFIX . '_' . $action . '_image WHERE image_id=?';
        $stmt = $DBC->query($query, array($image_id));
        $rr = array();
        if (!$stmt) {
            return;
        }
        $rr = $DBC->fetch($stmt);
        $record_image_id = $rr[$action . '_image_id'];
        $sort_order = $rr['sort_order'];

        if ($direction == 'down') {
            $query = 'SELECT ' . $action . '_image_id, sort_order FROM ' . DB_PREFIX . '_' . $action . '_image WHERE sort_order > ? AND `' . $key . '` = ? ORDER BY sort_order ASC';
            $stmt = $DBC->query($query, array($sort_order, $key_value));
            if (!$stmt) {
                return;
            }
            $rr = $DBC->fetch($stmt);
            $next_record_image_id = (int)$rr[$action . '_image_id'];
            if ($next_record_image_id == 0) {
                return;
            }
            $next_sort_order = $rr['sort_order'];

            $query = 'UPDATE ' . DB_PREFIX . '_' . $action . '_image SET sort_order=? WHERE ' . $action . '_image_id=?';
            $stmt = $DBC->query($query, array($next_sort_order, $record_image_id));

            $query = 'UPDATE ' . DB_PREFIX . '_' . $action . '_image SET sort_order=? WHERE ' . $action . '_image_id=?';
            $stmt = $DBC->query($query, array($sort_order, $next_record_image_id));
        }

        if ($direction == 'up') {
            $query = 'SELECT ' . $action . '_image_id, sort_order FROM ' . DB_PREFIX . '_' . $action . '_image WHERE sort_order < ? AND `' . $key . '` = ? ORDER BY sort_order DESC';
            $stmt = $DBC->query($query, array($sort_order, $key_value));
            if (!$stmt) {
                return;
            }
            $rr = $DBC->fetch($stmt);
            $next_record_image_id = (int)$rr[$action . '_image_id'];
            if ($next_record_image_id == 0) {
                return;
            }
            $next_sort_order = $rr['sort_order'];
            $query = 'UPDATE ' . DB_PREFIX . '_' . $action . '_image SET sort_order=? WHERE ' . $action . '_image_id=?';
            $stmt = $DBC->query($query, array($next_sort_order, $record_image_id));

            $query = 'UPDATE ' . DB_PREFIX . '_' . $action . '_image SET sort_order=? WHERE ' . $action . '_image_id=?';
            $stmt = $DBC->query($query, array($sort_order, $next_record_image_id));
        }
    }

    function reorderTopics($orderArray)
    {
        if (count($orderArray) > 0) {
            $DBC = DBC::getInstance();
            $query = 'UPDATE ' . DB_PREFIX . '_topic SET `order`=? WHERE id=?';
            foreach ($orderArray as $k => $v) {
                $DBC->query($query, array((int)$v, (int)$k));
            }
        }
    }

    /**
     * Delete image files
     * @param $image_id image id
     * @return boolean
     */
    function deleteImageFiles($image_id)
    {
        $path = SITEBILL_DOCUMENT_ROOT . $this->storage_dir;
        $DBC = DBC::getInstance();
        $query = 'SELECT * FROM ' . IMAGE_TABLE . ' WHERE image_id=?';
        $stmt = $DBC->query($query, array((int)$image_id));
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                if (defined('STR_MEDIA') && STR_MEDIA == Sitebill::MEDIA_SAVE_FOLDER) {
                    $preview = $ar['preview'];
                    $normal = $ar['normal'];
                    @unlink(MEDIA_FOLDER . '/' . $preview);
                    @unlink(MEDIA_FOLDER . '/' . $normal);
                    @unlink(MEDIA_FOLDER . '/nowatermark/' . $normal);
                } else {
                    $preview = $ar['preview'];
                    $normal = $ar['normal'];
                    @unlink($path . $preview);
                    @unlink($path . $normal);
                    @unlink($path . 'nowatermark/' . $normal);
                }
            }
        }
        return true;
    }

    /**
     * Get image list admin
     * @param string $action action
     * @param string $table_name table name
     * @param string $key key
     * @param int $record_id record id
     * @return string
     */
    function getImageListAdmin($action, $table_name, $key, $record_id, &$callback_count = NULL, $no_controls = false)
    {

        if (SITEBILL_MAIN_URL != '') {
            $url = SITEBILL_MAIN_URL . '/' . $this->storage_dir;
        } else {
            $url = $this->storage_dir;
        }

        $record_id = (int)$record_id;

        if ($record_id == 0) {
            return '';
        }


        //$query = "SELECT i.* FROM ".DB_PREFIX."_".$table_name."_image AS li, ".IMAGE_TABLE." AS i WHERE li.".$key."=$record_id AND li.image_id=i.image_id ORDER BY li.sort_order";
        $query = 'SELECT i.* FROM ' . DB_PREFIX . '_' . $table_name . '_image AS li, ' . IMAGE_TABLE . ' AS i WHERE li.' . $key . '=? AND li.image_id=i.image_id ORDER BY li.sort_order';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query, array($record_id));
        if ($stmt) {
            $i = 0;
            $rs .= '<style>
    			.preview_admin { float: left; min-height: 250px; padding: 5px; margin: 5px; }
    			.preview_admin td > img { width: 100px; border: 1px solid #CFCFCF;
	box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
	border-radius: 5px;
	margin-bottom: 5px;}
    
    			</style>';

            $rs .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/system/js/dataimagelist.js?v=1"></script>';
            $rs .= '<script type="text/javascript">DataImagelist.attachDblclick();</script>';


            while ($ar = $DBC->fetch($stmt)) {

                $rs .= '<div class="preview_admin">
    		<table border="0" id="data_gallery">';

                if (isset($ar['title'])) {
                    $rs .= '<tr><td class="field_tab" style="height:20px; border: 1px solid gray;" alt="' . $ar['image_id'] . '">' . $ar['title'] . '<td></tr>';
                }
                if (isset($ar['description'])) {
                    $rs .= '<tr><td class="field_tab_description" style="height:20px; border: 1px solid gray;" alt="' . $ar['image_id'] . '">' . $ar['description'] . '<td></tr>';
                }


                $rs .= '<tr>
    		<td>
    		<br />
    		<img src="' . $url . '' . $ar['preview'] . '" border="0" align="left"/><br>
    		</td>';
                $rs .= '</tr>';

                $rs .= '<tr>';
                $rs .= '<td>';
                $rs .= '<a href="javascript:void(0);" onClick="DataImagelist.deleteImage(this,' . $ar['image_id'] . ',' . $record_id . ',\'' . $table_name . '\',\'' . $key . '\')"><img src="' . SITEBILL_MAIN_URL . '/apps/admin/admin/template/img/delete.png" width="16" border="0" alt="удалить" title="удалить"></a>
    		<a href="javascript:void(0);" onClick="DataImagelist.upImage(this,' . $ar['image_id'] . ',' . $record_id . ',\'' . $table_name . '\',\'' . $key . '\')"><img src="' . SITEBILL_MAIN_URL . '/img/up.gif" border="0" alt="наверх" title="наверх"></a>
    		<a href="javascript:void(0);" onClick="DataImagelist.downImage(this,' . $ar['image_id'] . ',' . $record_id . ',\'' . $table_name . '\',\'' . $key . '\')"><img src="' . SITEBILL_MAIN_URL . '/img/down1.gif" border="0" alt="вниз" title="вниз"></a>
    		<a href="javascript:void(0);" onClick="DataImagelist.makeMain(this,' . $ar['image_id'] . ',' . $record_id . ',\'' . $table_name . '\',\'' . $key . '\')">Сделать главной</a>
    		<!--<a href="javascript:void(0);" onClick="DataImagelist.rotateImage(this,' . $ar['image_id'] . ',' . $record_id . ',\'' . $table_name . '\',\'' . $key . '\', \'ccw\')"><img src="' . SITEBILL_MAIN_URL . '/apps/admin/admin/template/img/rotccw.png" border="0" alt="наверх" title="Повернуть против часовой стрелки"></a>
    		<a href="javascript:void(0);" onClick="DataImagelist.rotateImage(this,' . $ar['image_id'] . ',' . $record_id . ',\'' . $table_name . '\',\'' . $key . '\', \'cw\')"><img src="' . SITEBILL_MAIN_URL . '/apps/admin/admin/template/img/rotcw.png" border="0" alt="наверх" title="Повернуть по часовой стрелке"></a>-->
    				</td>
    		</tr>';

                $rs .= '</table>
    		</div>';
                //$rs .= '<div style="clear: both;"></div>';
                $i++;
            }
            if ($callback_count !== NULL) {
                $callback_count = $i;
            }
        }
        return $rs;
    }

    /**
     * Get file list admin
     * @param string $action action
     * @param string $table_name table name
     * @param string $key key
     * @param int $record_id record id
     * @return string
     */
    function getFileListAdmin($action, $table_name, $key, $record_id)
    {
        if (SITEBILL_MAIN_URL != '') {
            $url = SITEBILL_MAIN_URL . '/' . $this->storage_dir;
        } else {
            $url = $this->storage_dir;
        }
        $record_id = (int)$record_id;
        $DBC = DBC::getInstance();
        $query = 'SELECT i.* FROM ' . DB_PREFIX . '_' . $table_name . '_image AS li, ' . IMAGE_TABLE . ' AS i WHERE li.' . $key . '=? AND li.image_id=i.image_id ORDER BY li.sort_order';
        $stmt = $DBC->query($query, array($record_id));
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                /* $up_link = '?action='.$action.'&do=edit&'.$key.'='.$record_id.'&subdo=up_image&image_id='.$ar['image_id'];
                  $down_link = '?action='.$action.'&do=edit&'.$key.'='.$record_id.'&subdo=down_image&image_id='.$ar['image_id'];


                  $up_link_img = '<a href="'.$up_link.'"><img src="'.SITEBILL_MAIN_URL.'/img/up.gif" border="0" alt="наверх" title="наверх"></a>';
                  $down_link_img = '<a href="'.$down_link.'"><img src="'.SITEBILL_MAIN_URL.'/img/down1.gif" border="0" alt="вниз" title="вниз"></a>';
                 */
                $delete_link = '?action=' . $action . '&do=edit&' . $key . '=' . $record_id . '&subdo=delete_image&image_id=' . $ar['image_id'];
                $rs .= '<div class="preview_admin" style="padding: 2px; border: 1px solid gray;">
    		<table border="0">
    		<tr>
    		<td>
    		<a href="' . $url . $ar['preview'] . '" target="_blank"><img src="/img/file.png" border="0" align="left"/> ' . $ar['preview'] . '</a><br>
    		</td>
    		<td>
    		<a href="' . $delete_link . '" onclick="return confirm(\'' . Multilanguage::_('L_MESSAGE_REALLY_WANT_DELETE') . '\');">' . Multilanguage::_('L_DELETE_LC') . '</a>
    		
    		</td>
    		</tr>
    		</table>
    		</div>';
                $rs .= '<div style="clear: both;"></div>';
            }
        }
        return $rs;
    }

    /**
     * Get image list admin
     * @param string $action action
     * @param string $table_name table name
     * @param string $key key
     * @param int $record_id record id
     * @param int $limit limit value
     * @return string
     */
    function get_image_array($action, $table_name, $key, $record_id, $limit = 0)
    {
        return array();
    }

    /**
     * Make preview — delegated to ImageProcessor
     * @param string $src address of source image
     * @param string $dst address of target image
     * @param int $width width of final image
     * @param int $height height of final image
     * @param string $ext source image extension
     * @param int|string $md resizing mode
     * @param string $final_ext extension of final image
     * @return array|false
     */
    function makePreview($src, $dst, $width, $height, $ext = 'jpg', $md = 0, $final_ext = '')
    {
        return $this->getImageProcessor()->makePreview($src, $dst, $width, $height, $ext, $md, $final_ext);
    }

    /**
     * Make move
     * @param string $src
     * @param string $dst
     */
    function makeMove($src, $dst)
    {
        $this->getImageProcessor()->makeMove($src, $dst);
    }

}
