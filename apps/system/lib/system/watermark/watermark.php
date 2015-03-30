<?php
/**
 * Watermark class
 * Print watermark on image
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru 
 */
class Watermark extends SiteBill {
	private $settings=array();
	private $watermark_image;
	private $watermark_position='bottom-right';
	private $watermark_offset_top=5;
	private $watermark_offset_bottom=5;
	private $watermark_offset_left=5;
	private $watermark_offset_right=5;
	
	
	/**
     * Constructor
     * Initializing class parameters
     */
	public function __construct(){
		$this->SiteBill();
		if ( is_file(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/watermark/watermark.ini') ) {
			$this->settings=parse_ini_file(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/watermark/watermark.ini');
		}
		
		if ( $this->getConfigValue('apps.watermark.image') !== FALSE && $this->getConfigValue('apps.watermark.image') != '') {
			$this->watermark_image=SITEBILL_DOCUMENT_ROOT.'/img/watermark/'.$this->getConfigValue('apps.watermark.image');
		} else {
			$this->watermark_image=SITEBILL_DOCUMENT_ROOT.'/img/watermark/watermark.gif';
		}
	}
	
	/**
	 * Set watermark position on image
	 * @param string $position enum('center','top-left','top-right','bottom-left','bottom-right')
	 */
	public function setPosition($position){
		if(in_array($position,array('center','top-left','top-right','bottom-left','bottom-right'))){
			$this->watermark_position=$position;
		}else{
			$this->watermark_position='bottom-left';
		}
	}
	
	/**
	 * Set watermarks offsets on image for positions not equals 'center' in px
	 * @param array $offsets_array array of offsets ({left|top|right|bottom} or {left|right,top|bottom} or {left|right,top,bottom} or {left,top,right,bottom})
	 */
	public function setOffsets($offsets_array){
		$count=count($offsets_array);
		switch($count){
			case '1' : {
				$this->watermark_offset_bottom=$this->watermark_offset_left=$this->watermark_offset_right=$this->watermark_offset_top=$offsets_array[0];
				break;
			}
			case '2' : {
				$this->watermark_offset_left=$this->watermark_offset_right=$offsets_array[0];
				$this->watermark_offset_bottom=$this->watermark_offset_top=$offsets_array[1];
				break;
			}
			case '3' : {
				$this->watermark_offset_left=$this->watermark_offset_right=$offsets_array[0];
				$this->watermark_offset_top=$offsets_array[1];
				$this->watermark_offset_bottom=$offsets_array[2];
				
				break;
			}
			case '4' : {
				$this->watermark_offset_left=$offsets_array[0];
				$this->watermark_offset_top=$offsets_array[1];
				$this->watermark_offset_right=$offsets_array[2];
				$this->watermark_offset_bottom=$offsets_array[3];
				break;
			}
		}
		
	}
	
	/**
	 * Print watermark
	 * @param string $image_destination image path
	 */
	public function printWatermark($image_destination){
		
		if($image_destination==''){
			return;
		}
		
		$image='';
		$ext='';
		$watermark='';
		
		//watermark preparing
		$parts=array();
		//echo '$this->watermark_image = '.$this->watermark_image.'<br>';
		
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
				$watermark = imagecreatefrompng($this->watermark_image);
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
		$pct=$this->getConfigValue('apps.watermark.opacity');
		if($pct==''){
			$pct=50;
		}
		imagecopymerge($image, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height, $pct);
		
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
	}
	
	public function loadSettings(){
		$settings=simplexml_load_file(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/watermark/watermark.xml');
		echo '<pre>';
		echo $_SERVER['PHP_SELF'].'<br>';
		echo __FILE__.'<br>';
		echo __DIR__.'<br>';
		print_r($settings);
	}
	
	public function getSettings(){
		return parse_ini_file(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/watermark/watermark.ini');
	}
	
	public function saveSettings($settings){
		foreach($settings as $k=>&$v){
			$v=$k.'='.$v;
		}
		echo implode("\r\n",$settings);
	}
	
	public function configure(){
		//print_r($this->settings);
		if(isset($_POST['submit'])){
			print_r($_POST['watermark']);
			$this->settings=$_POST['watermark'];
			$this->saveSettings($this->settings);
		}else{
			$ret='';
			$ret.='<form method="post">';
			$ret.='<table>';
			$ret.='<tr><td>image</td><td><input type="text" name="watermark[image]" value="'.$this->settings['image'].'" /></td></tr>';
			$ret.='<tr><td>position</td><td><input type="text" name="watermark[position]" value="'.$this->settings['position'].'" /></td></tr>';
			$ret.='<tr><td>offset_top</td><td><input type="text" name="watermark[offset_top]" value="'.$this->settings['offset_top'].'" /></td></tr>';
			$ret.='<tr><td>offset_bottom</td><td><input type="text" name="watermark[offset_bottom]" value="'.$this->settings['offset_bottom'].'" /></td></tr>';
			$ret.='<tr><td>offset_left</td><td><input type="text" name="watermark[offset_left]" value="'.$this->settings['offset_left'].'" /></td></tr>';
			$ret.='<tr><td>offset_right</td><td><input type="text" name="watermark[offset_right]" value="'.$this->settings['offset_right'].'" /></td></tr>';
			$ret.='<tr><td colspan="2"><input type="submit" name="submit" value="submit" /></td></tr>';
			$ret.='</table>';
			$ret.='</form>';
			echo $ret;
		}
		print_r($this->settings);
		
	}
	
}