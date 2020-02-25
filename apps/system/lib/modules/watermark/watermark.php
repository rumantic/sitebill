<?php

/**
 * Watermark class
 * Print watermark on image
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru 
 */
class Watermark extends SiteBill {

    private $settings = array();
    private $watermark_image;
    private $watermark_position = 'center';
    private $watermark_offset_top = 5;
    private $watermark_offset_bottom = 5;
    private $watermark_offset_left = 5;
    private $watermark_offset_right = 5;

    /**
     * Constructor
     * Initializing class parameters
     */
    public function __construct() {
        $this->loadSettings();
        //$settings=simplexml_load_file(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/watermark/watermark.xml');
        //echo '<pre>';
        //print_r(parse_ini_file(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/watermark/watermark.ini'));
        //echo $settings->settings->parameter[0]['name'];
        $this->watermark_image = SITEBILL_DOCUMENT_ROOT . '/img/watermark/watermark.gif';
    }

    /**
     * Set watermark position on image
     * @param string $position enum('center','top-left','top-right','bottom-left','bottom-right')
     */
    public function setPosition($position) {
        if (in_array($position, array('center', 'top-left', 'top-right', 'bottom-left', 'bottom-right'))) {
            $this->watermark_position = $position;
        }
    }

    /**
     * Set watermarks offsets on image for positions not equals 'center' in px
     * @param array $offsets_array array of offsets ({left|top|right|bottom} or {left|right,top|bottom} or {left|right,top,bottom} or {left,top,right,bottom})
     */
    public function setOffsets($offsets_array) {
        $count = count($offsets_array);
        switch ($count) {
            case '1' : {
                    $this->watermark_offset_bottom = $this->watermark_offset_left = $this->watermark_offset_right = $this->watermark_offset_top = $offsets_array[0];
                    break;
                }
            case '2' : {
                    $this->watermark_offset_left = $this->watermark_offset_right = $offsets_array[0];
                    $this->watermark_offset_bottom = $this->watermark_offset_top = $offsets_array[1];
                    break;
                }
            case '3' : {
                    $this->watermark_offset_left = $this->watermark_offset_right = $offsets_array[0];
                    $this->watermark_offset_top = $offsets_array[1];
                    $this->watermark_offset_bottom = $offsets_array[2];

                    break;
                }
            case '4' : {
                    $this->watermark_offset_left = $offsets_array[0];
                    $this->watermark_offset_top = $offsets_array[1];
                    $this->watermark_offset_right = $offsets_array[2];
                    $this->watermark_offset_bottom = $offsets_array[3];
                    break;
                }
        }
    }

    /**
     * Print watermark
     * @param string $image_destination image path
     */
    public function printWatermark($image_destination) {
        return;
        //Это устаревший метод. комментируем
        /*

          if($image_destination==''){
          return;
          }

          $image='';
          $ext='';
          $watermark='';

          //watermark preparing
          $parts=array();
          $parts=explode('.',$this->watermark_image);
          $ext=strtolower($parts[count($parts)-1]);
          switch($ext){
          case 'jpg' : {
          $watermark = imagecreatefromjpeg($this->watermark_image);
          break;
          }
          case 'gif' : {
          $watermark = imagecreatefromgif($this->watermark_image);
          break;
          }
          case 'png' : {
          $watermark = imagecreatefrompngf($this->watermark_image);
          break;
          }
          case 'jpeg' : {
          $watermark = imagecreatefromjpeg($this->watermark_image);
          break;
          }
          }

          if($watermark==''){
          return;
          }

          //image prepare
          $ext='';
          $parts=array();
          $parts=explode('.',$image_destination);
          $ext=strtolower($parts[count($parts)-1]);
          switch($ext){
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
          if($image==''){
          return;
          }

          $watermark_width = imagesx($watermark);
          $watermark_height = imagesy($watermark);

          $size = getimagesize($image_destination);

          switch($this->watermark_position){
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
          $dest_x = ceil(($size[0] - $watermark_width)/2);
          $dest_y = ceil(($size[1] - $watermark_height)/2);
          }
          }


          imagealphablending($image, true);
          imagealphablending($watermark, true);

          imagecopymerge($image, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height,50);

          switch($ext){
          case 'jpg' : {
          imagejpeg($image,$image_destination);
          break;
          }
          case 'gif' : {
          imagegif($image,$image_destination);
          break;
          }
          case 'png' : {
          imagepng($image,$image_destination);
          break;
          }
          case 'jpeg' : {
          imagejpeg($image,$image_destination);
          break;
          }
          }

          imagejpeg($image,$image_destination);

          imagedestroy($image);
          imagedestroy($watermark);
          return TRUE;
         * 
         */
    }

    public function loadSettings() {
        $this->settings = parse_ini_file(__DIR__ . '/watermark.ini', TRUE);
        //echo '<pre>';
        //print_r($this->settings);
    }

    public function getSettings() {
        return parse_ini_file(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/watermark/watermark.ini');
    }

    public function saveSettings() {
        $str = '';
        foreach ($this->settings as $k => $v) {
            $str .= '[' . $k . ']' . "\r\n";
            $str .= 'name=' . $v['name'] . "\r\n";
            $str .= 'value=' . $v['value'] . "\r\n";
            $str .= 'options=' . $v['options'] . "\r\n";
            $str .= 'type=' . $v['type'] . "\r\n";
        }
        $f = fopen(__DIR__ . '/watermark.ini', 'w');
        fwrite($f, $str);
        fclose($f);
        //echo implode("\r\n",$settings);
    }

    public function configure() {
        //print_r($this->settings);
        if (isset($_POST['submit'])) {
            print_r($_FILES);
            if (is_uploaded_file($_FILES['image']['tmp_name'])) {
                move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/' . strtolower($_FILES['image']['name']));
                $_POST['watermark']['image'] = strtolower($_FILES['image']['name']);
            } else {
                echo(Multilanguage::_('L_ERROR_CANT_UPLOAD_FILE'));
            }


            foreach ($_POST['watermark'] as $k => $v) {
                $this->settings[$k]['value'] = $v;
            }
            //print_r($_POST['watermark']);
            //print_r($this->settings);
            $this->saveSettings();
            //$this->settings=$_POST['watermark'];
            //$this->saveSettings($this->settings);
        } else {
            $ret = '';
            $ret .= '<form method="post" enctype="multipart/form-data">';
            $ret .= '<table>';
            foreach ($this->settings as $k => $v) {
                $ret .= '<tr><td>' . $v['name'] . '</td><td>' . $this->buildFormElement($k, $v) . '</td></tr>';
            }
            $ret .= '<tr><td colspan="2"><input type="submit" name="submit" value="submit" /></td></tr>';
            $ret .= '</table>';
            $ret .= '</form>';
            echo $ret;
        }
        //print_r($this->settings);
    }

    private function buildFormElement($k, $v) {
        $ret = '';
        switch ($v['type']) {
            case 'select' : {
                    $ret .= '<select name="watermark[' . $k . ']">';
                    $options = explode(',', $v['options']);
                    foreach ($options as $o) {
                        if ($o == $v['value']) {
                            $ret .= '<option value="' . $o . '" selected="selected">' . $o . '</option>';
                        } else {
                            $ret .= '<option value="' . $o . '">' . $o . '</option>';
                        }
                    }
                    $ret .= '</select>';
                    break;
                }
            case 'file' : {
                    $ret = ($v['value'] != '' ? '<img src="/lib/modules/watermark/' . $v['value'] . '" width="50px" />' : '' ) . '<input type="file" name="' . $k . '" />';
                    break;
                }
            default : {
                    $ret = '<input type="text" name="watermark[' . $k . ']" value="' . $v['value'] . '" />';
                }
        }
        return $ret;
    }

}
