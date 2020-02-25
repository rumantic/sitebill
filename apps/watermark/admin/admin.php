<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Watermark Printer admin backend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class watermark_admin extends Object_Manager {

    /**
     * Constructor
     */
    function __construct() {
        $this->app_title = 'Watermark';
        $this->action = 'watermark';

        $this->SiteBill();
        Multilanguage::appendAppDictionary('watermark');
        if ($this->getConfigValue('apps.watermark.enable') == 1) {
            $this->watermark_position = '';
            if (!in_array($this->getConfigValue('apps.watermark.position'), array('center', 'top-left', 'top-right', 'bottom-left', 'bottom-right'))) {
                $this->watermark_position = 'center';
            } else {
                $this->watermark_position = $this->getConfigValue('apps.watermark.position');
            }
            $this->watermark_offset_left = (int) $this->getConfigValue('apps.watermark.offset_left');
            $this->watermark_offset_top = (int) $this->getConfigValue('apps.watermark.offset_top');
            $this->watermark_offset_right = (int) $this->getConfigValue('apps.watermark.offset_right');
            $this->watermark_offset_bottom = (int) $this->getConfigValue('apps.watermark.offset_bottom');

            if ($this->getConfigValue('apps.watermark.image') != '') {
                $this->watermark_image = SITEBILL_DOCUMENT_ROOT . '/img/watermark/' . $this->getConfigValue('apps.watermark.image');
            } else {
                $this->watermark_image = SITEBILL_DOCUMENT_ROOT . '/apps/watermark/admin/img/watermark.gif';
            }
        }
    }

    function printWatermark($image_destination) {
        echo 'deprecated method';
        return;
        /*

        if ($this->getConfigValue('apps.watermark.enable') != 1) {
            return;
        }


        if ($image_destination == '') {
            return;
        }

        $image = '';
        $ext = '';
        $watermark = '';

        $parts = array();

        $parts = explode('.', $this->watermark_image);
        $ext = strtolower($parts[count($parts) - 1]);
        switch ($ext) {
            case 'jpg' : {
                    $watermark = imagecreatefromjpeg($this->watermark_image);
                    break;
                }
            case 'gif' : {
                    $watermark = imagecreatefromgif($this->watermark_image);
                    break;
                }
            case 'png' : {
                    $watermark = imagecreatefrompng($this->watermark_image);
                    break;
                }
            case 'jpeg' : {
                    $watermark = imagecreatefromjpeg($this->watermark_image);
                    break;
                }
        }

        if ($watermark == '') {
            return;
        }

        //image prepare
        $ext = '';
        $parts = array();
        $parts = explode('.', $image_destination);
        $ext = strtolower($parts[count($parts) - 1]);
        switch ($ext) {
            case 'jpg' : {
                    $image = imagecreatefromjpeg($image_destination);
                    break;
                }
            case 'gif' : {
                    $image = imagecreatefromgif($image_destination);
                    break;
                }
            case 'png' : {
                    $image = imagecreatefrompng($image_destination);
                    break;
                }
            case 'jpeg' : {
                    $image = imagecreatefromjpeg($image_destination);
                    break;
                }
        }
        if ($image == '') {
            return;
        }

        $watermark_width = imagesx($watermark);
        $watermark_height = imagesy($watermark);

        $size = getimagesize($image_destination);

        switch ($this->watermark_position) {
            case 'bottom-right' : {
                    $dest_x = $size[0] - $watermark_width - $this->watermark_offset_right;
                    $dest_y = $size[1] - $watermark_height - $this->watermark_offset_bottom;
                    break;
                }
            case 'top-right' : {
                    $dest_x = $size[0] - $watermark_width - $this->watermark_offset_right;
                    $dest_y = $this->watermark_offset_top;
                    break;
                }
            case 'top-left' : {
                    $dest_x = $this->watermark_offset_left;
                    $dest_y = $this->watermark_offset_top;
                    break;
                }
            case 'bottom-left' : {
                    $dest_x = $this->watermark_offset_left;
                    $dest_y = $size[1] - $watermark_height - $this->watermark_offset_bottom;
                    break;
                }
            default : {
                    $dest_x = ceil(($size[0] - $watermark_width) / 2);
                    $dest_y = ceil(($size[1] - $watermark_height) / 2);
                }
        }


        imagealphablending($image, true);
        imagealphablending($watermark, true);
        $pct = $this->getConfigValue('apps.watermark.opacity');
        if ($pct == '') {
            $pct = 50;
        }
        imagecopymerge($image, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height, $pct);

        switch ($ext) {
            case 'jpg' : {
                    imagejpeg($image, $image_destination);
                    break;
                }
            case 'gif' : {
                    imagegif($image, $image_destination);
                    break;
                }
            case 'png' : {
                    imagepng($image, $image_destination);
                    break;
                }
            case 'jpeg' : {
                    imagejpeg($image, $image_destination);
                    break;
                }
        }

        imagejpeg($image, $image_destination);

        imagedestroy($image);
        imagedestroy($watermark);
        return TRUE;
         * 
         */
    }

    function main() {
        $rs .= $this->get_app_title_bar();
        $rs .= Multilanguage::_('APPLICATION_NAME', 'watermark') . ' ';
        if ($this->getConfigValue('is_watermark')) {
            $rs .= Multilanguage::_('APP_ON', 'watermark');
        } else {
            $rs .= Multilanguage::_('APP_OFF', 'watermark');
        }
        return $rs;
    }

}
