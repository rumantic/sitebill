<?php
/**
 * Class Page_Navigator
 * @author Abushyk Kostyantyn <abushyk@gmail.com>
*/
class Page_Navigator {
	private $pre_pages=3; //pages count befor current
	private $post_pages=3; //pages count after current
	private $item_container_open='<span class="text">';  //container open tag for pager item
	private $item_container_close='</span>';  //container close tag for pager item
	private $active_item_container_open='<span class="active" style="background-color:Red">';
	private $active_item_container_close='</span>';
	private $settings=array();
	private $pager_items_array=array();
	
	/**
	 * Enter description here ...
	 * @param integer $total_records
	 * @param integer $current_page
	 * @param integer $per_page
	 * @param string $url
	 * @param array $settings
	 */
	public function __construct($total_records,$current_page=1,$per_page=10,$url='',$settings=array()){
		$this->makePager($total_records,$current_page,$per_page,$url,$settings);
		
	}
	
	private function makePagerArray($total_records,$current_page,$per_page,$url,$settings){
		
	}
	
	private function makePager($total_records,$current_page,$per_page,$url,$settings){
		if($url==''){
			$url=$_SERVER['REQUEST_URI'];
		}
		$this->initSettings($settings);
		$pages_count=ceil($total_records/$per_page);
		
		
		$requestdata=array();
		$requestdata=$this->explodeUrl($url);
		//print_r($url);
		//print_r($requestdata);
		
		$p_prew=$current_page-1;
		$p_next=$current_page+1;
		
		if($current_page==1){
			$first_page_navigation.='&laquo;&laquo; '.Multilanguage::_('L_TO_START');
			$fpn['text']='&laquo;&laquo;';
			$fpn['href']=$requestdata['url'].'?page=1';
		}else{
			$first_page_navigation.='<a href="'.$requestdata['url'].($requestdata['params']? '?'.$requestdata['params'] : '').'" title="'.Multilanguage::_('L_TO_START').'">&laquo;&laquo; '.Multilanguage::_('L_TO_START').'</a>';
			$fpn['text']='&laquo;&laquo;';
			$fpn['href']=$requestdata['url'].($requestdata['params']? '?'.$requestdata['params'] : '');
		}

		if($current_page==$pages_count){
			$last_page_navigation.=Multilanguage::_('L_TO_END').' &raquo;&raquo;';
			$lpn['text']='&raquo;&raquo;';
			$lpn['href']='';
		}else{
			$last_page_navigation.='<a href="'.$requestdata['url'].'?page='.$pages_count.($requestdata['params'] ? '&'.$requestdata['params'] : '').'" title="'.Multilanguage::_('L_TO_END').'">'.Multilanguage::_('L_TO_END').' &raquo;&raquo;</a>';
			$lpn['text']='&raquo;&raquo;';
			$lpn['href']=$requestdata['url'].'?page='.$pages_count.($requestdata['params'] ? '&'.$requestdata['params'] : '');
		}

		if($p_prew<1){
			$prev_page_navigation.='&laquo;';
			$ppn['text']='&laquo;';
			$ppn['href']='';
		}else{
			$prev_page_navigation.='<a href="'.$requestdata['url'].'?page='.$p_prew.($requestdata['params']? '&'.$requestdata['params'] : '').'" title="'.Multilanguage::_('L_TO_PREV').'">&laquo; '.Multilanguage::_('L_TO_PREV').'</a>';
			$ppn['text']='&laquo;';
			$ppn['href']=$requestdata['url'].'?page='.$p_prew.($requestdata['params']? '&'.$requestdata['params'] : '');
		}

		if($p_next>$pages_count){
			$next_page_navigation.=Multilanguage::_('L_TO_NEXT').' &raquo;';
			$npn['text']='&raquo;';
			$npn['href']='';
		}else{
			$next_page_navigation.='<a href="'.$requestdata['url'].'?page='.$p_next.($requestdata['params']? '&'.$requestdata['params'] : '').'" title="'.Multilanguage::_('L_TO_NEXT').'">'.Multilanguage::_('L_TO_NEXT').' &raquo;</a>';
			$npn['text']='&raquo;';
			$npn['href']=$requestdata['url'].'?page='.$p_next.($requestdata['params']? '&'.$requestdata['params'] : '');
		}
		
		$ret=array();
		//$this->pager_items_array[]=$first_page_navigation;
		//$this->pager_items_array[]=$prev_page_navigation;
		
		$this->pager_items_array[]=$fpn;
		$this->pager_items_array[]=$ppn;
		
		$start_page=$current_page-$this->settings['pre_pages'];
		$end_page=$current_page+$this->settings['post_pages'];
		
		
		
		if($start_page<=1){
			$start_page=1;
			$lineprefix='';
		}else{
			$lineprefix='...';
		}

		if($end_page>=$pages_count){
			$end_page=$pages_count;
			$linepostfix='';
		}
		else{
			$linepostfix='...';
		}
		
		if($lineprefix!=''){
			//$this->pager_items_array[]=$lineprefix;
			$this->pager_items_array[]=array('text'=>$lineprefix, 'href'=>'', 'current'=>'0');
		}
		
		
		for($i=$start_page;$i<=$end_page;$i++){
			if($i==$current_page){
				$this->pager_items_array[]=array('text'=>$i, 'href'=>'', 'current'=>'1');
				//$this->pager_items_array[]=$this->settings['active_item_container_open'].$i.$this->settings['active_item_container_close'];
			}else{
				$this->pager_items_array[]=array('text'=>$i, 'href'=>$requestdata['url'].'?page='.$i.($requestdata['params']? '&'.$requestdata['params'] : ''), 'current'=>'0');
				//$this->pager_items_array[]='<a href="'.$requestdata['url'].'?page='.$i.($requestdata['params']? '&'.$requestdata['params'] : '').'">'.$i.'</a>';
			}
		}
		
		if($linepostfix!=''){
			//$this->pager_items_array[]=$linepostfix;
			$this->pager_items_array[]=array('text'=>$linepostfix, 'href'=>'', 'current'=>'0');
		}
		
		//$this->pager_items_array[]=$next_page_navigation;
		//$this->pager_items_array[]=$last_page_navigation;
		$this->pager_items_array[]=$npn;
		$this->pager_items_array[]=$lpn;
	}
	
	public function getPagerArray(){
		return $this->pager_items_array;
	}
	
	public function drawPager($mode='text',$delimiter=' '){
		
		switch($mode){
			case 'text' : {
				$ret=array();
				foreach($this->pager_items_array as $pi){
					//print_r($pi);
					$ret[]=$this->settings['item_container_open'].($pi['href']!='' ? '<a href="'.$pi['href'].'">'.$pi['text'].'</a>' : $pi['text']).$this->settings['item_container_close'];
				}
				return implode($delimiter,$ret);
				break;
			}
			case 'array' : {
				return $this->pager_items_array;
				break;
			}
			case 'array_only_numbers' : {
				$ret=array();
				$last_element=count($this->pager_items_array)-1;
				$pre_last_element=count($this->pager_items_array)-2;
				foreach($this->pager_items_array as $k=>$pi){
					if($k==0 OR $k==1 OR $k==$last_element OR $k==$pre_last_element){
						
					}else{
						$ret[]=$pi;
					}
				}
				return $ret;
				break;
			}
			default : {
				
			}
		}
	}
	
	private function explodeUrl($url){
		$path=parse_url($url);
		$items=$this->parse_query($url);
		//print_r($items);
		unset($items['page']);
		$exp_url=$path['path'];
		$ra=array();
		if(count($items)>0){
			foreach ( $items as $key => $value ) {
				if(($value=='')OR($key=='')){

				}else{
					$ra[] = $key.'='.urlencode($value);
				}
			}
			if(count($ra)>0){
				$exp_params=implode('&', $ra);
			}else{
				$exp_params=FALSE;
			}
		}else{
			$exp_params=FALSE;
		}
		$ret['url']=$exp_url;
		$ret['params']=$exp_params;
		return $ret;
	}
	
	private function parse_query($var){
		$var  = parse_url($var, PHP_URL_QUERY);
		$var  = html_entity_decode($var);
		$var  = explode('&', $var);
		$arr  = array();

		foreach($var as $val){
			$x = explode('=', $val);
			$arr[$x[0]] = $x[1];
		}
		unset($val, $x, $var);
		return $arr;
	}
	
	private function initSettings($settings){
		$this->settings['pre_pages']=$this->pre_pages;
		$this->settings['post_pages']=$this->post_pages;
		$this->settings['item_container_open']=$this->item_container_open;
		$this->settings['item_container_close']=$this->item_container_close;
		$this->settings['active_item_container_open']=$this->active_item_container_open;
		$this->settings['active_item_container_close']=$this->active_item_container_close;
		$this->settings=array_merge($this->settings,$settings);
		
		return TRUE;
	}
	
	public static function getPagingArray($total_records, $current_page=1, $per_page=10, $pager_params=array(), $url='', $settings=array()){
		//echo $current_page;
        if($current_page == 0){
            $current_page = 1;
        }
		
		if($url==''){
			//$url=$_SERVER['REQUEST_URI'];
			$url=SITEBILL_MAIN_URL.'/?';
		}else{
			$url=SITEBILL_MAIN_URL.'/'.trim($url, '/').'/?';
		}
		
		//print_r($pager_params);
		unset($pager_params['page']);
		/*$pairs=array();
		if(count($pager_params)>0){
			foreach ( $pager_params as $key => $value ) {
				if(is_array($value)){
					if(count($value)>0){
						foreach($value as $v){
							if($v!=''){
								$pairs[] = $key.'[]='.$v;
							}
						}
					}
				}elseif ( $value != '' ) {
					$pairs[] = $key."=".$value;
				}
			}
		}
		if(!empty($pairs)){
			$pager_params_string=implode('&', $pairs);
		}else{
			$pager_params_string='';
		}*/
		
		if(!empty($pager_params)){
			$pager_params_string=urldecode(http_build_query($pager_params));
		}else{
			$pager_params_string='';
		}

		$pages_count=ceil($total_records/$per_page);
		
		$ret=array();
		
		
		$requestdata=array();
		//$requestdata=$this->explodeUrl($url);
		//print_r($url);
		//print_r($requestdata);
		
		$p_prew=$current_page-1;
		$p_next=$current_page+1;
		
		if($current_page==1){
			//$first_page_navigation.='&laquo;&laquo; '.Multilanguage::_('L_TO_START');
			$fpn['text']='&laquo;&laquo;';
			$fpn['href']=$url.'page=1'.($pager_params_string!='' ? '&'.$pager_params_string : '');
		}else{
			//$first_page_navigation.='<a href="'.$url.'page=1'.($pager_params_string!='' ? '&'.$pager_params_string : '').'" title="'.Multilanguage::_('L_TO_START').'">&laquo;&laquo; '.Multilanguage::_('L_TO_START').'</a>';
			$fpn['text']='&laquo;&laquo;';
			$fpn['href']=$url.'page=1'.($pager_params_string!='' ? '&'.$pager_params_string : '');
		}
		
		$ret['fpn']=$fpn;
		/*echo '---------';
		echo $pages_count;
		echo '---------';*/
		if($current_page==$pages_count){
			//$last_page_navigation.=Multilanguage::_('L_TO_END').' &raquo;&raquo;';
			$lpn['text']='&raquo;&raquo;';
			$lpn['href']='';
		}else{
			//$last_page_navigation.='<a href="'.$url.'page='.$pages_count.($pager_params_string!='' ? '&'.$pager_params_string : '').'" title="'.Multilanguage::_('L_TO_END').'">'.Multilanguage::_('L_TO_END').' &raquo;&raquo;</a>';
			$lpn['text']='&raquo;&raquo;';
			$lpn['href']=$url.'page='.$pages_count.($pager_params_string!='' ? '&'.$pager_params_string : '');
		}
		
		$ret['lpn']=$lpn;
		
		if($p_prew<1){
			//$prev_page_navigation.='&laquo;';
			$ppn['text']='&laquo;';
			$ppn['href']='';
		}else{
			//$prev_page_navigation.='<a href="'.$url.'page='.$p_prew.($pager_params_string!='' ? '&'.$pager_params_string : '').'" title="'.Multilanguage::_('L_TO_PREV').'">&laquo; '.Multilanguage::_('L_TO_PREV').'</a>';
			$ppn['text']='&laquo;';
			$ppn['href']=$url.'page='.$p_prew.($pager_params_string!='' ? '&'.$pager_params_string : '');
		}
		
		$ret['ppn']=$ppn;
		
		if($p_next>$pages_count){
			//$next_page_navigation.=Multilanguage::_('L_TO_NEXT').' &raquo;';
			$npn['text']='&raquo;';
			$npn['href']='';
		}else{
			//$next_page_navigation.='<a href="'.$url.'page='.$p_next.($pager_params_string!='' ? '&'.$pager_params_string : '').'" title="'.Multilanguage::_('L_TO_NEXT').'">'.Multilanguage::_('L_TO_NEXT').' &raquo;</a>';
			$npn['text']='&raquo;';
			$npn['href']=$url.'page='.$p_next.($pager_params_string!='' ? '&'.$pager_params_string : '');
		}
		
		$ret['npn']=$npn;
		
		//$ret=array();
		//$this->pager_items_array[]=$first_page_navigation;
		//$this->pager_items_array[]=$prev_page_navigation;
		
		//$this->pager_items_array[]=$fpn;
		//$this->pager_items_array[]=$ppn;
		
		//$start_page=$current_page-$this->settings['pre_pages'];
		//$end_page=$current_page+$this->settings['post_pages'];
		
		$start_page=1;
		$end_page=$pages_count;
		
		if($start_page<=1){
			$start_page=1;
			$lineprefix='';
		}else{
			$lineprefix='...';
		}
		
		if($end_page>=$pages_count){
			$end_page=$pages_count;
			$linepostfix='';
		}
		else{
			$linepostfix='...';
		}
		/*
		if($lineprefix!=''){
			//$this->pager_items_array[]=$lineprefix;
			$this->pager_items_array[]=array('text'=>$lineprefix, 'href'=>'', 'current'=>'0');
		}
		*/
		
		for($i=$start_page;$i<=$end_page;$i++){
			if($i==$current_page){
				//$this->pager_items_array[]=array('text'=>$i, 'href'=>'', 'current'=>'1');
				$ret['pages'][$i]=array('text'=>$i, 'href'=>'', 'current'=>'1');
				//$this->pager_items_array[]=$this->settings['active_item_container_open'].$i.$this->settings['active_item_container_close'];
			}else{
				//$this->pager_items_array[]=array('text'=>$i, 'href'=>$requestdata['url'].'?page='.$i.($requestdata['params']? '&'.$requestdata['params'] : ''), 'current'=>'0');
				$ret['pages'][$i]=array('text'=>$i, 'href'=>$url.'page='.$i.($pager_params_string!='' ? '&'.$pager_params_string : ''), 'current'=>'0');
			}
		}
		
		$ret['current_page']=$current_page;
		$ret['total_pages']=$pages_count;
		
		
		return $ret;
		
		if($linepostfix!=''){
			//$this->pager_items_array[]=$linepostfix;
			$this->pager_items_array[]=array('text'=>$linepostfix, 'href'=>'', 'current'=>'0');
		}
		
		//$this->pager_items_array[]=$next_page_navigation;
		//$this->pager_items_array[]=$last_page_navigation;
		$this->pager_items_array[]=$npn;
		$this->pager_items_array[]=$lpn;
	}
}