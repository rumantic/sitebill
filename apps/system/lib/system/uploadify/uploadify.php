<?php
/**
 * Uploadify class
 * Store data into UPLOADIFY table
 */
if (!defined('UPLOADIFY_TABLE')) {
    define('UPLOADIFY_TABLE', DB_PREFIX . '_uploadify');
}

class Sitebill_Uploadify extends Sitebill
{
    /**
     * Constructor
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Main
     * @param boolean $file_mode
     * @return string
     */
    function main($file_mode = false)
    {

        if (1 == $this->getRequestValue('simple_mode')) {
            $simple_mode = true;
        } else {
            $simple_mode = false;
        }

        $uploader_type = $this->getConfigValue('uploader_type');
        if ('dropzone' == $this->getRequestValue('uploader_type')) {
            $uploader_type = 'dropzone';
        }

        if (!empty($_FILES)) {

            switch ($uploader_type) {
                case 'pluploader' :
                {
                    $file_container_name = 'file';
                    break;
                }
                case 'dropzone' :
                {
                    $file_container_name = 'file';
                    break;
                }
                default :
                {
                    $file_container_name = 'Filedata';
                }
            }

            $tempFile = $_FILES[$file_container_name]['tmp_name'];
            $targetPath = SITEBILL_DOCUMENT_ROOT . '/cache/upl/';

            if ($_FILES[$file_container_name]['error'] > 0) {
                header('HTTP/1.1 200 OK');
                header('Content-Type: application/json');
                echo json_encode(array('status' => 'error', 'msg' => 'Ошибка загрузки ' . $_FILES[$file_container_name]['error']));
                return;
            }

            if (!is_uploaded_file($tempFile)) {
                header('HTTP/1.1 200 OK');
                header('Content-Type: application/json');
                echo json_encode(array('status' => 'error', 'msg' => 'Файл не был загружен'));
                return;
            }

            $arr = explode('.', $_FILES[$file_container_name]['name']);
            $file_name_without_ext = $arr[0];
            $ext = strtolower(end($arr));

            if (!$this->isMimeGood($tempFile, $ext, $mime)) {
                if ($uploader_type == 'dropzone') {
                    header('HTTP/1.1 200 OK');
                    header('Content-Type: application/json');
                    echo json_encode(array('status' => 'error', 'msg' => 'bad_file ' . $mime));
                } else {
                    echo 'bad_file';
                }
                return;
            }


            if (($_FILES[$file_container_name]['size'] / 1000000) > ((int)str_replace('M', '', ini_get('upload_max_filesize')))) {
                if ($uploader_type == 'dropzone') {
                    header('HTTP/1.1 200 OK');
                    header('Content-Type: application/json');
                    echo json_encode(array('status' => 'error', 'msg' => 'Недопустимый размер файла'));
                } else {
                    echo 'max_file_size';
                }
                return;
            }
            if ($uploader_type == 'dropzone' && '' != $this->getRequestValue('model')) {
                $DBC = DBC::getInstance();
                $model_name = $this->getRequestValue('model');
                $parameters = array();

                if ( $model_name != 'fake_config' ) {
                    $query = 'SELECT * FROM ' . DB_PREFIX . '_columns WHERE name=? AND table_id=(SELECT table_id FROM ' . DB_PREFIX . '_table WHERE name=? LIMIT 1)';
                    $stmt = $DBC->query($query, array($this->getRequestValue('element'), $this->getRequestValue('model')));
                    if (!$stmt) {
                        header('HTTP/1.1 200 OK');
                        header('Content-Type: application/json');
                        echo json_encode(array('status' => 'error', 'msg' => 'Недопустимый тип файла или несуществующая модель данных'));
                        return;
                    }
                    $ar = $DBC->fetch($stmt);
                    if (isset($ar) && $ar['parameters'] != '') {
                        $parameters = unserialize($ar['parameters']);
                    }
                } else {
                    //Параметры для логотипа (fake_config)
                    // $parameters['max_img_count'] = 1;
                }


                if (isset($parameters['max_file_size']) && '' != $parameters['max_file_size']) {
                    $maxsize = (floatval(str_replace('M', '', $parameters['max_file_size'])) * 1024 * 1024);
                    if ($_FILES[$file_container_name]['size'] > $maxsize) {
                        header('HTTP/1.1 200 OK');
                        header('Content-Type: application/json');
                        echo json_encode(array('status' => 'error', 'msg' => 'Недопустимый размер файла'));
                    }
                }

                if ($ar['type'] == 'docuploads') {
                    $allowed_exts = $this->get_docuploads_extensions();

                    if (isset($parameters['accepted']) and $parameters['accepted'] != '') {
                        $av = explode(',', $parameters['accepted']);
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
                    }
                } else {

                    $needcontrolminsize = false;
                    if (isset($parameters['minsizepx'])) {
                        $size = strtolower($parameters['minsizepx']);
                        if (preg_match('/^(\d+)x(\d+)$/', $size, $matches)) {
                            $min_w = $matches[1];
                            $min_h = $matches[2];
                            $needcontrolminsize = true;
                        } elseif (preg_match('/^(\d+)$/', $size, $matches)) {
                            $min_w = $min_h = $matches[1];
                            $needcontrolminsize = true;
                        }
                    }

                    if ($needcontrolminsize) {
                        $imdata = getimagesize($tempFile);
                        if (false !== $imdata) {
                            if ($imdata[0] < $min_w || $imdata[1] < $min_h) {
                                header('HTTP/1.1 200 OK');
                                header('Content-Type: application/json');
                                echo json_encode(array('status' => 'error', 'msg' => 'Минимальный размер изображения ' . $min_w . ' х ' . $min_h));
                                return;
                            }
                        }
                    }

                    if (isset($parameters['max_img_count']) && $parameters['max_img_count'] != '') {
                        $max_img_count = intval($parameters['max_img_count']);
                    } else {
                        $max_img_count = -1;
                    }

                    //Проверяем наличие расширяющих правил для max_img_count
                    if (isset($parameters['max_img_count_ext']) && '' != $parameters['max_img_count_ext']) {
                        $maximgcountextendrules = $parameters['max_img_count_ext'];
                    } else {
                        $maximgcountextendrules = '';
                    }
                    $controlledfields = array();
                    $maxsizerules = array();
                    if ($maximgcountextendrules != '') {
                        $rulesparts = explode(':', $maximgcountextendrules);
                        $size = intval($rulesparts[0]);
                        if ($size > 0 && count($rulesparts) > 1) {
                            unset($rulesparts[0]);
                            $conditions = array();
                            foreach ($rulesparts as $rule) {
                                $oneruleparts = explode(',', $rule);
                                if (count($oneruleparts) == 3) {
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

                    if ($max_img_count > -1) {

                        $checkmaxsizebyvalue = $max_img_count;

                        $element = $this->getRequestValue('element');
                        $model = $this->getRequestValue('model');
                        $primary_key = $this->getRequestValue('primary_key');
                        $primary_key_value = intval($this->getRequestValue('primary_key_value'));

                        if (!empty($controlledfields)) {
                            foreach ($controlledfields as $fk => $fv) {
                                $controlledfields[$fk] = intval($this->getRequestValue($fk));
                            }
                        }

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
                                            if ($controlledfields[$field] != $value) {
                                                $condsok = false;
                                            }
                                            break;
                                        }
                                        case 'neq' :
                                        {
                                            if ($controlledfields[$field] == $value) {
                                                $condsok = false;
                                            }
                                            break;
                                        }
                                        case 'gt' :
                                        {
                                            if ($controlledfields[$field] <= $value) {
                                                $condsok = false;
                                            }
                                            break;
                                        }
                                        case 'lt' :
                                        {
                                            if ($controlledfields[$field] >= $value) {
                                                $condsok = false;
                                            }
                                            break;
                                        }
                                    }
                                }

                                if ($condsok) {
                                    $checkmaxsizebyvalue = $maxsizerule['size'];
                                    break;
                                }

                            }
                        }

                        $attached_yet = array();

                        $DBC = DBC::getInstance();

                        if ($primary_key_value > 0) {
                            $query = 'SELECT `' . $element . '` FROM ' . DB_PREFIX . '_' . $model . ' WHERE `' . $primary_key . '`=? LIMIT 1';
                            $stmt = $DBC->query($query, array($primary_key_value));
                            if ($stmt) {
                                $ar = $DBC->fetch($stmt);
                                if ($ar[$element] != '') {
                                    $attached_yet = unserialize($ar[$element]);
                                }
                            }
                        }


                        $quenue = 0;

                        $query = 'SELECT COUNT(*) AS _cnt FROM ' . UPLOADIFY_TABLE . ' WHERE `session_code`=? AND `element`=?';
                        //$stmt=$DBC->query($query, array($_REQUEST['session'], $element));
                        $stmt = $DBC->query($query, array($this->get_session_key(), $element));

                        if ($stmt) {
                            $ar = $DBC->fetch($stmt);
                            $quenue = $ar['_cnt'];
                        }

                        $last_count = $checkmaxsizebyvalue - count($attached_yet) - $quenue;

                        if ($last_count < 1) {
                            header('HTTP/1.1 200 OK');
                            header('Content-Type: application/json');
                            echo json_encode(array('status' => 'error', 'msg' => 'Максимальное количество файлов ' . $checkmaxsizebyvalue));
                            return;
                        }

                    }

                    $allowed_exts = array('jpg', 'png', 'gif', 'jpeg', 'webp', 'svg');
                }

                if (!in_array($ext, $allowed_exts)) {
                    header('HTTP/1.1 200 OK');
                    header('Content-Type: application/json');
                    echo json_encode(array('status' => 'error', 'msg' => 'Недопустимый тип файла'));
                    return;
                }

            } elseif ($file_mode == 'excel') {
                $avail_ext = array('xls', 'xlsx');
                if (!in_array(strtolower($ext), $avail_ext)) {
                    if ($uploader_type == 'dropzone') {
                        header('HTTP/1.1 200 OK');
                        header('Content-Type: application/json');
                        echo json_encode(array('status' => 'error', 'msg' => 'Недопустимый тип файла'));
                    } else {
                        echo 'wrong_ext';
                    }
                    return;
                }
            } elseif ($file_mode) {
                $avail_ext = array('png', 'jpg', 'jpeg', 'webp', 'doc', 'docx', 'xls', 'xlsx', 'pdf', 'zip', 'rar', 'csv');
                if (!in_array(strtolower($ext), $avail_ext)) {
                    if ($uploader_type == 'dropzone') {
                        header('HTTP/1.1 200 OK');
                        header('Content-Type: application/json');
                        echo json_encode(array('status' => 'error', 'msg' => 'Недопустимый тип файла'));
                    } else {
                        echo 'wrong_ext';
                    }
                    return;
                }
            } elseif (!in_array(strtolower($ext), array('jpg', 'png', 'gif', 'jpeg', 'svg'))) {
                if ($uploader_type == 'dropzone') {
                    header('HTTP/1.1 200 OK');
                    header('Content-Type: application/json');
                    echo json_encode(array('status' => 'error', 'msg' => 'Недопустимый тип файла'));
                    //echo 'Недопустимый тип файла';
                } else {
                    echo 'wrong_ext';
                }
                return;
            }


            $j = 1;
            if ($this->getConfigValue('use_native_file_name_on_uploadify')) {
                //$preview_name_tmp=$this->transliteMe($file_name_without_ext).".".$ext;
                $preview_name_tmp = $file_name_without_ext .'.'. $ext;
            } else {
                $preview_name_tmp = "jpg_" . uniqid() . '_' . time() . "_" . $j . "." . $ext;
            }
            $targetFile = str_replace('//', '/', $targetPath) . $preview_name_tmp;

            while (file_exists($targetFile)) {
                $j++;
                if ($this->getConfigValue('use_native_file_name_on_uploadify')) {
                    $preview_name_tmp = $file_name_without_ext . "(".$j.")." . $ext;
                } else {
                    $preview_name_tmp = "jpg_" . uniqid() . '_' . time() . "_" . $j . "." . $ext;
                }
                $targetFile = str_replace('//', '/', $targetPath) . $preview_name_tmp;
            }

            if ($uploader_type == 'dropzone') {
                header('HTTP/1.1 200 OK');
                header('Content-Type: application/json');
                echo json_encode(array('status' => 'OK', 'msg' => SITEBILL_MAIN_URL . str_replace(SITEBILL_DOCUMENT_ROOT, '', $targetFile)));
            } else {
                echo SITEBILL_MAIN_URL . str_replace(SITEBILL_DOCUMENT_ROOT, '', $targetFile);
            }

            move_uploaded_file($tempFile, $targetFile);
            /* На случай, если сервер выставляет на загруженные файлы права 0600*/
            chmod($targetFile, 0755);
            /**/

        }
        if (!$simple_mode) {
            $session = $this->get_session_key();
            if ($uploader_type == 'dropzone') {
                $element = $this->getRequestValue('element');
                $this->addFile($session, $preview_name_tmp, $element);
            } else {
                $this->addFile($session, $preview_name_tmp);
            }
        }
    }

    /**
     * Add file
     * @param string $session_code session code
     * @param string $targetFile target file
     * @return boolean
     */
    function addFile($session_code, $targetFile, $element_name = '')
    {
        $DBC = DBC::getInstance();
        if ($element_name != '') {
            $query = 'INSERT INTO ' . UPLOADIFY_TABLE . ' (`session_code`, `file_name`, `element`) VALUES (?, ?, ?)';
            $stmt = $DBC->query($query, array($session_code, $targetFile, $element_name));
        } else {
            $query = 'INSERT INTO ' . UPLOADIFY_TABLE . ' (`session_code`, `file_name`) VALUES (?, ?)';
            $stmt = $DBC->query($query, array($session_code, $targetFile));
        }
        if ($stmt) {
            return true;
        } else {
            return false;
        }

    }

    function isMimeGood($tempFile, $ext, &$mime)
    {
        $ext = strtolower($ext);
        if (function_exists('finfo_open') && function_exists('finfo_file') && function_exists('finfo_close')) {
            $fileinfo = finfo_open(FILEINFO_MIME);
            $output = finfo_file($fileinfo, $tempFile);
            finfo_close($fileinfo);
            if ($output != '') {
                list($mct) = explode("; ", $output);
            }
            $mime = $mct;
        } elseif (function_exists('mime_content_type')) {
            $mct = mime_content_type($tempFile);
            $mime = $mct;
        } else {
            $mct = '';
            $fh = fopen($tempFile, 'rb');
            $bytes6 = fread($fh, 6);
            if ($ext == 'png' && $bytes6 == "\x89PNG\x0d\x0a") {
                $mct = 'image/png';
            }
            if ($ext == 'webp') {
                $mct = 'image/webp';
            }
            if ($ext == 'jpg' && substr($bytes6, 0, 3) == "\xff\xd8\xff") {
                $mct = 'image/jpeg';
            }
            if ($ext == 'jpeg' && substr($bytes6, 0, 3) == "\xff\xd8\xff") {
                $mct = 'image/jpeg';
            }
            if ($ext == 'gif' && ($bytes6 == "GIF87a" || $bytes6 == "GIF89a")) {
                $mct = 'image/gif';
            }
            if ($ext == 'pdf' && substr($bytes6, 0, 4) == "%PDF") {
                $mct = 'application/pdf';
            }
            if ($ext == 'xlsx') {
                //$mct='application/vnd.ms-excel';
                $mct = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            }
            if ($ext == 'xls' && $bytes6 == "\xd0\xcf\x11\xe0\xa1\xb1") {
                $mct = 'application/vnd.ms-excel';
            }
            if ($ext == 'docx') {
                //$mct='application/msword';
                $mct = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
            }
            if ($ext == 'doc' && $bytes6 == "\xd0\xcf\x11\xe0\xa1\xb1") {
                $mct = 'application/msword';
            }
            if ($ext == 'csv') {
                $mct = 'text/plain';
            }
            if ($ext == 'txt') {
                $mct = 'text/plain';
            }
            if ($ext == 'xml') {
                $mct = 'application/xml';
            }
            if ($ext == 'zip' && substr($bytes6, 0, 2) == "PK") {
                $mct = 'application/zip';
            }
            if ($ext == 'rar' && substr($bytes6, 0, 4) == "Rar!") {
                $mct = 'application/x-rar';
            }

            $mime = $mct;
        }

        if ($ext == 'png' && $mct == 'image/png') {
            return true;
        } elseif ($ext == 'webp' && $mct == 'image/webp') {
            return true;
        } elseif ($ext == 'svg' && $mct == 'image/svg+xml') {
            return true;
        } elseif ($ext == 'jpg' && $mct == 'image/jpeg') {
            return true;
        } elseif ($ext == 'jpeg' && $mct == 'image/jpeg') {
            return true;
        } elseif ($ext == 'gif' && $mct == 'image/gif') {
            return true;
        } elseif ($ext == 'pdf' && $mct == 'application/pdf') {
            return true;
            /*}elseif($ext=='xlsx' && $mct=='application/vnd.ms-excel'){*/
        } elseif ($ext == 'xlsx' && $mct == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
            return true;
        } elseif ($ext == 'xls' && $mct == 'application/vnd.ms-excel') {
            return true;
            //}elseif($ext=='docx' && $mct=='application/msword'){
        } elseif ($ext == 'docx' && $mct == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
            return true;
        } elseif ($ext == 'doc' && $mct == 'application/msword') {
            return true;
        } elseif ($ext == 'csv' && $mct == 'text/plain') {
            return true;
        } elseif ($ext == 'txt' && $mct == 'text/plain') {
            return true;
        } elseif ($ext == 'xml' && $mct == 'application/xml') {
            return true;
        } elseif ($ext == 'zip' && $mct == 'application/zip') {
            return true;
        } elseif ($ext == 'rar' && $mct == 'application/x-rar') {
            return true;
        } elseif ( $ext == 'mp4' && $mct == 'video/mp4' ) {
            return true;
        }
        return false;
    }
}
