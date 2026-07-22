<?php
/**
 * UserDataWatermarkTrait — watermark/photo protection methods extracted from User_Data_Manager.
 *
 * Methods: get_photos, protectImagesByWatermark, formatAnswer_protect_images,
 *          formatAnswer_deprotect_images, _deprotect_imagesAction, _protect_imagesAction,
 *          _exportPhotoAction, _exportPhotoClearAction
 */
trait UserDataWatermarkTrait
{
    /**
     * Return zip-archive of object photos
     * @param int $id Object ID
     * @param bool $clearprotect Determines whether protected by watermark photos are returned or not
     * @return bool|void
     */
    function get_photos($id, $clearprotect = false)
    {

        $DBC = DBC::getInstance();

        $query = 'SELECT image FROM ' . DB_PREFIX . '_data WHERE id = ? AND user_id = ? AND image <> ?';
        $stmt = $DBC->query($query, array($id, $_SESSION['user_id'], ''));
        if (!$stmt) {
            exit();
        }
        $ar = $DBC->fetch($stmt);
        $images = unserialize($ar['image'], ['allowed_classes' => false]);

        if (empty($images)) {
            return false;
        }


        $zip = new ZipArchive();
        $zip_name = "photos_" . $id . '_' . time() . ".zip";
        $zip->open($zip_name, ZIPARCHIVE::CREATE);

        $exported = array();

        if ($clearprotect && 1 === (int)$this->getConfigValue('watermark_user_control')) {
            $fold = $this->notwatermarked_folder;
            if ($this->nowatermark_folder_with_id) {
                $fold = $fold . $id . '/';
            }
            foreach ($images as $photo) {
                if (file_exists($fold . $photo['normal'])) {
                    $exported[] = array($fold . $photo['normal'], $photo['normal']);
                } else {
                    $exported[] = array(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $photo['normal'], $photo['normal']);
                }
            }
        } elseif ($clearprotect && 0 === (int)$this->getConfigValue('watermark_user_control')) {
            $fold = SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/';

            foreach ($images as $photo) {
                if (file_exists($fold . $photo['normal'])) {
                    $exported[] = array($fold . $photo['normal'], $photo['normal']);
                } else {
                    $exported[] = array(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $photo['normal'], $photo['normal']);
                }
            }
        } else {
            $j = 0;
            foreach ($images as $photo) {
                $j++;
                if ($photo['remote'] === 'true') {
                    $pathinfo = pathinfo($photo['normal']);
                    $file_name = $j . '.' . $pathinfo['extension'];
                    $exported[] = array($photo['normal'], $photo['normal'], 1);
                } else {
                    $exported[] = array(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $photo['normal'], $photo['normal']);
                }
            }
        }

        foreach ($exported as $exp) {
            if (isset($exp[2]) && $exp[2] == 1) {
                $zip->addFromString($exp[0], file_get_contents($exp[1]));
            } else {
                $zip->addFile($exp[0], $exp[1]);
            }
        }

        $zip->close();
        if (file_exists($zip_name)) {
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private", false);
            header('Content-type: application/zip');
            header('Content-Disposition: attachment; filename="' . $zip_name . '"');
            readfile($zip_name);
            unlink($zip_name);
        }
        exit();
    }

    public function protectImagesByWatermark($id)
    {

        $images = array();

        $fold = $this->notwatermarked_folder;

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;

        $form_data[$this->table_name] = $data_model->init_model_data_from_db($this->table_name, 'id', $id, $form_data['data']);

        $fields = array();
        foreach ($form_data[$this->table_name] as $name => $item) {
            if ($item['type'] === 'uploads') {
                if (is_array($item['value']) && !empty($item['value'])) {
                    foreach ($item['value'] as $val) {
                        $images[] = $val['normal'];
                    }
                }
            }
        }

        $donecount = 0;

        if (!empty($images)) {

            if ($this->nowatermark_folder_with_id) {
                $copy_path = $fold . $id . '/';
                mkdir($copy_path);
            } else {
                $copy_path = $fold;
            }

            foreach ($images as $image) {
                if (file_exists($copy_path . $image)) {
                    continue;
                }

                if(false !== strpos($image, '/')){

                    $subs = explode('/', $image);
                    $fldsrs = array_slice($subs, 0, -1);

                    if(!is_dir($copy_path.DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, $fldsrs))){
                        $realpath = $copy_path;
                        foreach ($fldsrs as $f){
                            $realpath = $realpath.DIRECTORY_SEPARATOR.$f;
                            if(!is_dir($realpath)){
                                mkdir($realpath);
                            }
                        }
                    }

                }

                copy(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $image, $copy_path . $image);

                $Watermark = $this->createWatermarkInstance(true);
                $Watermark->printWatermark(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $image);

                $donecount += 1;
            }
        }

        return $donecount;
    }

    public function formatAnswer_protect_images($status, $code, $updated_photo_count = 0)
    {
        if ($status == 0) {
            switch ($code) {
                case 'denied' :
                {
                    return 'Ошибка. Доступ запрещен.';
                    break;
                }
                case 'protected' :
                {
                    return 'Ошибка. Защита уже включена.';
                    break;
                }
            }
        } else {
            return 'Защита включена. Обработано ' . $updated_photo_count . ' фото';
        }
    }

    public function formatAnswer_deprotect_images($status, $code, $updated_photo_count = 0, $restored_photo_count = 0)
    {
        if ($status == 0) {
            switch ($code) {
                case 'denied' :
                {
                    return 'Ошибка. Доступ запрещен.';
                    break;
                }
                case 'protected' :
                {
                    return 'Ошибка. Защита не использовалась для этого объекта.';
                    break;
                }
            }
        } else {
            return 'Защита выключена. Восстановлено ' . $restored_photo_count . ' из ' . $updated_photo_count . ' фото';
        }
    }

    protected function _deprotect_imagesAction()
    {

        if (1 === (int)$this->getConfigValue('is_watermark')) {
            return $this->formatAnswer_deprotect_images(0, 'denied');
        }
        if (0 === (int)$this->getConfigValue('watermark_user_control')) {
            return $this->formatAnswer_deprotect_images(0, 'denied');
        }

        $user_id = (int)$_SESSION['user_id'];
        $id = (int)$this->getRequestValue('id');

        if (!$this->check_access_to_data($user_id, $id)) {
            return Multilanguage::_('L_ACCESS_DENIED');
        }

        $need_clear_watermark = false;

        $DBC = DBC::getInstance();
        $query = 'SELECT watermark_images FROM ' . DB_PREFIX . '_data WHERE id=?';
        $stmt = $DBC->query($query, array($id));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($ar['watermark_images'] == 0) {
                return $this->formatAnswer_deprotect_images(0, 'nonprotected');
            } else {
                $need_clear_watermark = true;
            }

        } else {
            return $this->formatAnswer_deprotect_images(0, 'denied');
        }

        if (!$need_clear_watermark) {
            return $this->formatAnswer_deprotect_images(0, 'denied');
        }


        $fold = $this->notwatermarked_folder;


        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;

        $form_data[$this->table_name] = $data_model->init_model_data_from_db($this->table_name, 'id', $id, $form_data['data']);


        foreach ($form_data[$this->table_name] as $name => $item) {
            if ($item['type'] === 'uploads') {
                if (is_array($item['value']) && !empty($item['value'])) {
                    foreach ($item['value'] as $val) {
                        $images[] = $val['normal'];
                    }
                }
            }
        }

        $query = 'UPDATE ' . DB_PREFIX . '_data SET watermark_images=0 WHERE id=?';
        $stmt = $DBC->query($query, array($id));
        if (!$stmt) {
            return $this->formatAnswer_deprotect_images(0, 'denied');
        }

        $restored_count = 0;

        if (!empty($images)) {
            if ($this->nowatermark_folder_with_id) {
                $dest = $fold . $id . '/';
            } else {
                $dest = $fold;
            }

            foreach ($images as $image) {
                if (file_exists($dest . $image)) {
                    copy($dest . $image, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $image);
                    @unlink($dest . $image);
                    $restored_count++;
                }
            }
        }

        return $this->formatAnswer_deprotect_images(1, 'done', count($images), $restored_count);

    }

    protected function _protect_imagesAction()
    {

        $status = 0;
        $error_msg = '';

        if (1 == intval($this->getConfigValue('is_watermark'))) {
            return $this->formatAnswer_protect_images(0, 'denied');
        }
        if (0 == intval($this->getConfigValue('watermark_user_control'))) {
            return $this->formatAnswer_protect_images(0, 'denied');
        }
        $need_watermark = false;

        $user_id = (int)$_SESSION['user_id'];
        $id = (int)$this->getRequestValue('id');

        if (!$this->check_access_to_data($user_id, $id)) {
            return Multilanguage::_('L_ACCESS_DENIED');
        }

        $DBC = DBC::getInstance();
        $query = 'SELECT watermark_images FROM ' . DB_PREFIX . '_data WHERE id=?';
        $stmt = $DBC->query($query, array($id));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($ar['watermark_images'] == 1) {
                return $this->formatAnswer_protect_images(0, 'protected');
            } else {
                $need_watermark = true;
            }

        } else {
            return $this->formatAnswer_protect_images(0, 'denied');
        }


        if (!$need_watermark) {
            return $this->formatAnswer_protect_images(0, 'denied');
        }


        //$fold = $this->notwatermarked_folder;


        $query = 'UPDATE ' . DB_PREFIX . '_data SET watermark_images=1 WHERE id=?';
        $stmt = $DBC->query($query, array($id));
        if (!$stmt) {
            return $this->formatAnswer_protect_images(0, 'denied');
        }

        $resp = $this->protectImagesByWatermark($id);

        return $this->formatAnswer_protect_images(1, 'done', $resp);


    }

    /**
     * Выдача фотографий объекта с вотермарком в zip
     */
    protected function _exportPhotoAction()
    {
        $id = (int)$this->getRequestValue('id');
        $user_id = (int)$_SESSION['user_id'];

        if (!$this->check_access_to_data($user_id, $id)) {
            return Multilanguage::_('L_ACCESS_DENIED');
        }
        $this->get_photos($id);
    }

    /**
     * Выдача фотографий объекта без вотермарка в zip, если таковые есть
     */
    protected function _exportPhotoClearAction()
    {
        $id = (int)$this->getRequestValue('id');
        $user_id = (int)$_SESSION['user_id'];

        if (!$this->check_access_to_data($user_id, $id)) {
            return Multilanguage::_('L_ACCESS_DENIED');
        }
        $this->get_photos($id, true);
    }
}
