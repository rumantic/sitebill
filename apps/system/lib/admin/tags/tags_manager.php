<?php
/**
 * Tags manager
 * @author Abushyk Kostyantyn <abushyk@gmail.com>
*/
class Tags_Manager extends SiteBill {

	public function __construct(){
		$this->SiteBill();
	}
	
	public function prepareTags($tagsString){
		$temp=array();
		$tags=array();
		$tags_id=array();
		$temp=explode(',',$tagsString);
		foreach($temp as $v){
			$x=trim($v);
			if($x!=''){
				$tags[]=$x;
			}
		}
		$tagList=$this->getTagList();
		if(count($tags)!=0){
			if ( is_array($tags) ) {
				foreach($tags as $t){
					if(!in_array($t,$tagList)){
						$this->addTag($t);
					}
				}
			}
			
			$query="SELECT * FROM ".DB_PREFIX."_tag WHERE tag_name IN ('".implode('\',\'',$tags)."')";
			$DBC=DBC::getInstance();
			$stmt=$DBC->query($query);
			if($stmt){
				while($row=$DBC->fetch($stmt)){
					$tags_id[]=$row['tag_id'];
				}
			}
		}
		return $tags_id;
		print_r($tags_id);
	}
	
	public function connectTags($id,$tagsId,$mode='news',$primary_value='news'){
		$table=DB_PREFIX.'_'.$mode.'_tag';
		$field=$primary_value.'_id';
		if(count($tagsId)!=0){
			$query="DELETE FROM ".$table." WHERE ".$field."=".$id;
			$DBC=DBC::getInstance();
			$stmt=$DBC->query($query);
			foreach($tagsId as $tid){
				$query="INSERT INTO ".$table." (".$field.",tag_id) VALUES ('".$id."','".$tid."')";
				$stmt=$DBC->query($query);
			}
		}
	}
	
	public function disconnectTags($id,$mode='news',$primary_value='news'){
		$table=DB_PREFIX.'_'.$mode.'_tag';
		$field=$primary_value.'_id';
		$query="DELETE FROM ".$table." WHERE ".$field."=".$id;
		$DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
	}
	
	private function addTag($tagName){
		$query="INSERT INTO ".DB_PREFIX."_tag (tag_name) VALUES ('".$tagName."')";
		$DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
		if($stmt){
			return $DBC->lastInsertId();
		}else{
			return FALSE;
		}
	}
	
	private function getTagList(){
		$tagsList=array();
		$query="SELECT * FROM ".DB_PREFIX."_tag";
		$DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
		if($stmt){
			while($row=$DBC->fetch($stmt)){
				$tagList[]=$row['tag_name'];
			}
			return $tagList;
		}else{
			return $tagList;
		}
	}
	
	public function getRandomTags($count=2){
		$tagList=array();
		$query='SELECT * FROM '.DB_PREFIX.'_tag ORDER BY RAND() LIMIT 0, '.$count;
		$DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
		if($stmt){
			while($row=$DBC->fetch($stmt)){
				$tagList[]=$row;
			}
		}
        return $tagList;
	}
	
	public function getTagGroupMinPrice($tagList){
		$ret=0;
		if(count($tagList)>0){
			foreach($tagList as $tl){
				$tags_ids[]=$tl['tag_id'];
			}
			$query='SELECT MIN(product_price) AS min_price FROM '.DB_PREFIX.'_shop_product WHERE product_id IN (SELECT DISTINCT shop_product_id FROM '.DB_PREFIX.'_shop_product_tag WHERE tag_id IN('.implode(',',$tags_ids).')) AND active=1';
			$DBC=DBC::getInstance();
			$stmt=$DBC->query($query);
			if($stmt){
				$row=$DBC->fetch($stmt);
				$ret=(int)$row['min_price'];
			}
		}
		return $ret;
	}
	
	public function getNewsTags($id){
		$tagList=array();
		$query = "SELECT ".DB_PREFIX."_tag.tag_name, ".DB_PREFIX."_tag.tag_id FROM ".DB_PREFIX."_tag LEFT JOIN ".DB_PREFIX."_news_tag ON ".DB_PREFIX."_news_tag.tag_id=".DB_PREFIX."_tag.tag_id WHERE ".DB_PREFIX."_news_tag.news_id=".$id;
		$DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
		if($stmt){
			while($row=$DBC->fetch($stmt)){
				$tagList[]='<a href="javascript:void(0)">'.$row['tag_name'].'</a>';
			}
		}
        return $tagList;
	}
	
	public function getTagName($id){
		$ret='';
		$query='SELECT tag_name FROM '.DB_PREFIX.'_tag WHERE tag_id='.$id;
		$DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
		if($stmt){
			$row=$DBC->fetch($stmt);
			$ret=$row['tag_name'];
		}
		return $ret;
	}

}