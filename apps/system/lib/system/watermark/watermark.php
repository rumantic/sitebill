<?php

/**
 * Watermark class
 * Print watermark on image
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class Watermark extends SiteBill
{

    protected $settings = array();
    protected $watermark_image;
    protected $watermark_image_preview;
    protected $watermark_position = 'bottom-right';
    protected $watermark_offset_top = 5;
    protected $watermark_offset_bottom = 5;
    protected $watermark_offset_left = 5;
    protected $watermark_offset_right = 5;

    /**
     * Constructor
     * Initializing class parameters
     */
    public function __construct()
    {
        parent::__construct();
        if ($this->getConfigValue('apps.watermark.image') !== FALSE && $this->getConfigValue('apps.watermark.image') != '') {
            $this->watermark_image = SITEBILL_DOCUMENT_ROOT . '/img/watermark/' . $this->getConfigValue('apps.watermark.image');
        } else {
            $this->watermark_image = SITEBILL_DOCUMENT_ROOT . '/img/watermark/watermark.gif';
        }
        if ($this->getConfigValue('apps.watermark.image_preview') !== FALSE && $this->getConfigValue('apps.watermark.image_preview') != '') {
            $this->watermark_image_preview = SITEBILL_DOCUMENT_ROOT . '/img/watermark/' . $this->getConfigValue('apps.watermark.image_preview');
        }
    }

    /**
     * Set watermark position on image
     * @param string $position enum('center','top-left','top-right','bottom-left','bottom-right')
     */
    public function setPosition($position)
    {
        if (in_array($position, array('center', 'top-left', 'top-right', 'bottom-left', 'bottom-right'))) {
            $this->watermark_position = $position;
        } else {
            $this->watermark_position = 'bottom-left';
        }
    }

    /**
     * Set watermarks offsets on image for positions not equals 'center' in px
     * @param array $offsets_array array of offsets ({left|top|right|bottom} or {left|right,top|bottom} or {left|right,top,bottom} or {left,top,right,bottom})
     */
    public function setOffsets($offsets_array)
    {
        $count = count($offsets_array);
        switch ($count) {
            case '1' :
            {
                $this->watermark_offset_bottom = $this->watermark_offset_left = $this->watermark_offset_right = $this->watermark_offset_top = (int)$offsets_array[0];
                break;
            }
            case '2' :
            {
                $this->watermark_offset_left = $this->watermark_offset_right = (int)$offsets_array[0];
                $this->watermark_offset_bottom = $this->watermark_offset_top = (int)$offsets_array[1];
                break;
            }
            case '3' :
            {
                $this->watermark_offset_left = $this->watermark_offset_right = (int)$offsets_array[0];
                $this->watermark_offset_top = (int)$offsets_array[1];
                $this->watermark_offset_bottom = (int)$offsets_array[2];

                break;
            }
            case '4' :
            {
                if($offsets_array[0] !== ''){
                    $this->watermark_offset_left = (int)$offsets_array[0];
                }
                if($offsets_array[1] !== ''){
                    $this->watermark_offset_top = (int)$offsets_array[1];
                }
                if($offsets_array[2] !== ''){
                    $this->watermark_offset_right = (int)$offsets_array[2];
                }
                if($offsets_array[3] !== ''){
                    $this->watermark_offset_bottom = (int)$offsets_array[3];
                }
                break;
            }
        }
    }

    /**
     * Print watermark
     * @param string $image_destination image path
     * @param boolean $preview - если true, тогда при наложении водяного знака попробуем найти водяной знак с префиксом preview_
     */
    public function printWatermark($image_destination, $preview = false)
    {

        if ($image_destination == '' or !file_exists($image_destination)) {
            $this->writeLog('Ошибка наложения водяного знака. Файл не найден '.$image_destination);
            return;
        }

        $image = '';
        $watermark = '';

        //watermark preparing

        if ( $preview and file_exists($this->watermark_image_preview) ) {
            $watermark_image = $this->watermark_image_preview;
        } elseif ( file_exists($this->watermark_image) ) {
            $watermark_image = $this->watermark_image;
        } else {
            echo 'Watermark file not found '.$this->watermark_image."<br>";
            return false;
        }

        $parts = explode('.', $watermark_image);
        $wext = strtolower(end($parts));
        switch ($wext) {
            case 'jpg' :
            {
                $watermark = imagecreatefromjpeg($watermark_image);
                break;
            }
            case 'gif' :
            {
                $watermark = imagecreatefromgif($watermark_image);
                break;
            }
            case 'png' :
            {
                $watermark = imagecreatefrompng($watermark_image);
                break;
            }
            case 'jpeg' :
            {
                $watermark = imagecreatefromjpeg($watermark_image);
                break;
            }
        }

        if ($watermark == '') {
            return;
        }

        //image prepare
        $parts = explode('.', $image_destination);
        $ext = strtolower(end($parts));
        switch ($ext) {
            case 'jpg' :
            {
                $image = imagecreatefromjpeg($image_destination);
                break;
            }
            case 'gif' :
            {
                $image = imagecreatefromgif($image_destination);
                break;
            }
            case 'png' :
            {
                $image = imagecreatefrompng($image_destination);
                break;
            }
            case 'jpeg' :
            {
                $image = imagecreatefromjpeg($image_destination);
                break;
            }
            case 'webp' :
            {
                $image = imagecreatefromwebp($image_destination);
                break;
            }
        }
        if ($image == '') {
            return;
        }

        $watermark_width = imagesx($watermark);
        $watermark_height = imagesy($watermark);

        $size = array(imagesx($image), imagesy($image));

        switch ($this->watermark_position) {
            case 'bottom-right' :
            {
                $dest_x = $size[0] - $watermark_width - $this->watermark_offset_right;
                $dest_y = $size[1] - $watermark_height - $this->watermark_offset_bottom;
                break;
            }
            case 'top-right' :
            {
                $dest_x = $size[0] - $watermark_width - $this->watermark_offset_right;
                $dest_y = $this->watermark_offset_top;
                break;
            }
            case 'top-left' :
            {
                $dest_x = $this->watermark_offset_left;
                $dest_y = $this->watermark_offset_top;
                break;
            }
            case 'bottom-left' :
            {
                $dest_x = $this->watermark_offset_left;
                $dest_y = $size[1] - $watermark_height - $this->watermark_offset_bottom;
                break;
            }
            default :
            {
                $dest_x = ceil(($size[0] - $watermark_width) / 2);
                $dest_y = ceil(($size[1] - $watermark_height) / 2);
            }
        }

        //imagealphablending($image, true);
        //imagealphablending($watermark, false);
        //imageSaveAlpha($watermark, true);

        $pct = $this->getConfigValue('apps.watermark.opacity');
        if ($pct == '') {
            $pct = 50;
        }

        if($ext=='png'){
            $tmp_img = imageCreateTrueColor($size[0], $size[1]);
            $trans_colour = imagecolorallocate($tmp_img, 255, 255, 255);
            imagefill($tmp_img, 0, 0, $trans_colour);
            imagecopy($tmp_img, $image, 0, 0, 0, 0, $size[0], $size[1]);
            $image=$tmp_img;
        }

        if ($wext == 'png') {
            $this->imagecopymerge_alpha($image, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height, $pct);
        } else {
            imagecopymerge($image, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height, $pct);
        }

        switch ($ext) {
            case 'jpg' :
            {
                imagejpeg($image, $image_destination);
                break;
            }
            case 'gif' :
            {
                imagegif($image, $image_destination);
                break;
            }
            case 'png' :
            {
                imagepng($image, $image_destination);
                break;
            }
            case 'jpeg' :
            {
                imagejpeg($image, $image_destination);
                break;
            }
            case 'webp' :
            {
                imagewebp($image, $image_destination);
                break;
            }
        }

        //imagejpeg($image,$image_destination);

        imagedestroy($image);
        imagedestroy($watermark);
        return TRUE;
    }

    private function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct)
    {
        if (!isset($pct)) {
            return false;
        }
        $pct /= 100;
        $w = imagesx($src_im);
        $h = imagesy($src_im);
        imagealphablending($src_im, false);
        $minalpha = 127;
        for ($x = 0; $x < $w; $x++)
            for ($y = 0; $y < $h; $y++) {
                $alpha = ( imagecolorat($src_im, $x, $y) >> 24 ) & 0xFF;
                if ($alpha < $minalpha) {
                    $minalpha = $alpha;
                }
            }
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $colorxy = imagecolorat($src_im, $x, $y);
                $alpha = ( $colorxy >> 24 ) & 0xFF;
                if ($minalpha !== 127) {
                    $alpha = 127 + 127 * $pct * ( $alpha - 127 ) / ( 127 - $minalpha );
                } else {
                    $alpha += 127 * $pct;
                }
                $alphacolorxy = imagecolorallocatealpha($src_im, ( $colorxy >> 16 ) & 0xFF, ( $colorxy >> 8 ) & 0xFF, $colorxy & 0xFF, $alpha);
                if (!imagesetpixel($src_im, $x, $y, $alphacolorxy)) {
                    return false;
                }
            }
        }
        imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
    }
}