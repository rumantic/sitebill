<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * yandexrealty export generator frontend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class yandexrealty_site extends yandexrealty_admin {
	
	protected $export_mode='YANDEX';
	
	protected $associations=array();
	protected $catalogChains=array();
	protected $category_structure=array();
	protected $form_data_shared=array();
	protected $errors=array();

	function frontend () {
		$REQUESTURIPATH=Sitebill::getClearRequestURI();
		$alias=trim($this->getConfigValue('apps.yandexrealty.alias'));
		
		if($alias=='' && 1===intval($this->getConfigValue('apps.yandexrealty.disable_standart_entrypoint'))){
			return false;
		}elseif($alias==''){
			$alias='yandexrealty';
		}

		if($REQUESTURIPATH==$alias){
			header("Content-Type: text/xml");
			echo $this->run_export();
			exit();
		}
		
	

		
		/*if($REQUESTURIPATH==$alias){
			if(isset($_GET['to'])){
				$addr=$_REQUEST['to'];
				$addr=str_replace('..', '', $addr);
				$addr=trim($addr, '/');
				$pass=trim($this->getConfigValue('apps.yandexrealty.target_export_pass'));
				$reqpass=trim($_REQUEST['pass']);
				if($pass=='' || $pass!=$reqpass){
					return false;
				}
				
				$rs=$this->get_export();
				$storage=SITEBILL_DOCUMENT_ROOT.'/'.$addr;
				$f=fopen($storage, 'w');
				fwrite($f,$this->file_header.$this->file_start.$this->file_gen_date.$rs.$this->file_end);
				fclose($f);
				echo 'Выгружено в http://'.$_SERVER['HTTP_HOST'].'/'.$addr;
			}else{
				header("Content-Type: text/xml");
				echo $this->run_export();
			}
			
			exit();
		}elseif($REQUESTURIPATH==$alias.'/out'){
			$data=$this->collectOutData();
			$settings=$this->getExportSettings();
			$xml=$this->getXML($data, $settings);
			echo $xml;
		}*/
		return false;
	}
	
	protected function getExportSettings(){
		return array();
	}
	
	protected function collectOutData(){
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		$form_data_shared = $data_model->get_kvartira_model(false, true);
		$form_data_shared=$form_data_shared['data'];
		
		foreach($form_data_shared as $k=>$v){
			unset($form_data_shared[$k]['sort_order']);
			unset($form_data_shared[$k]['table_id']);
			unset($form_data_shared[$k]['group_id']);
			unset($form_data_shared[$k]['active_in_topic']);
			unset($form_data_shared[$k]['assign_to']);
			unset($form_data_shared[$k]['tab']);
			unset($form_data_shared[$k]['hint']);
		}
		
		$ms=array();
		
		$DBC=DBC::getInstance();
		$query='SELECT id FROM '.DB_PREFIX.'_data WHERE active=1 LIMIT 1';
		$stmt=$DBC->query($query);
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$ids[]=$ar['id'];
			}
		}
		
		if(count($ids)>0){
			$ms=$data_model->init_model_data_from_db_multi('data', 'id', $ids, $form_data_shared);
		}
		
		if(count($ms)>0){
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/users/user_object_manager.php');
			$user_object_manager = new User_Object_Manager();
			$form_user = $user_object_manager->get_user_model(true);
			
			foreach($form_user as $k=>$v){
				unset($form_user[$k]['sort_order']);
				unset($form_user[$k]['table_id']);
				unset($form_user[$k]['group_id']);
				unset($form_user[$k]['active_in_topic']);
				unset($form_user[$k]['assign_to']);
				unset($form_user[$k]['tab']);
				unset($form_user[$k]['hint']);
			}
			
			$users=array();
			foreach($ms as $k=>$r){
				$users[$r['user_id']['value']]=$r['user_id']['value'];
			}
			
			if(!empty($users)){
				foreach($users as $u){
					$mu[$u]=$data_model->init_model_data_from_db('user', 'user_id', $u, $form_user['user'], true);
				}
			}
			
			foreach($ms as $k=>$r){
				if(isset($mu[$r['user_id']['value']])){
					$ms[$k]['_user_data']=$mu[$r['user_id']['value']];
				}
			}
			
			
		}
		echo '<pre>';
		print_r($ms);
	}
	
	protected function getXML($data, $settings){
		return '';
	}
	
	public function get_export(){
		$this->setExportMode();
	
		$this->setExportType();
		
		$data=$this->collectData();
		$tofile=false;
	
	
		$this->associations=$this->loadAssociations();
	
		require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php';
		$Structure=new Structure_Manager();
		$this->category_structure = $Structure->loadCategoryStructure();
		$x=$Structure->createCatalogChains();
		$this->catalogChains=$x['txt'];
	
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		$this->form_data_shared = $data_model->get_kvartira_model(false, true);
		$this->form_data_shared = $this->form_data_shared['data'];
	
		$image_field=trim($this->getConfigValue('apps.yandexrealty.images_field'));
			
		$uploadsField=false;
		$hasUploadify=false;
			
		if($image_field!='' && isset($this->form_data_shared[$image_field]) && in_array($this->form_data_shared[$image_field]['type'], array('uploads', 'uploadify_image'))){
			if($this->form_data_shared[$image_field]['type']=='uploadify_image'){
				$hasUploadify=true;
			}else{
				$uploadsField=$image_field;
			}
		}else{
			foreach($this->form_data_shared as $model_item){
				if($model_item['type']=='uploadify_image'){
					$hasUploadify=true;
					$uploadsField=false;
					break;
				}elseif($uploadsField===false && $model_item['type']=='uploads'){
					$uploadsField=$model_item['name'];
				}
			}
		}
	
		$xml_obj=array();
	
		if($tofile){
			$xml_obj[]='<?xml version="1.0" encoding="utf-8" ?>';
			$xml_obj[]='<realty-feed xmlns="http://webmaster.yandex.ru/schemas/feed/realty/2010-06">';
			$xml_obj[]='<generation-date>'.$this->formdate().'</generation-date>';
		}
		
		$log=array();
		$this->errors=array();
	
		
		foreach($data as $data_item){
			$xml_collectorp=array();
			$xml_str='';
				
			if($data_item['price'] > 0 AND $data_item['city'] !== ''){
				$data_topic=(int)$data_item['topic_id'];
				//!empty($this->associations) && isset($this->associations[$data_topic]) && $this->associations[$data_topic]['realty_type']!=0
				if(1==(int)$this->getConfigValue('apps.yandexrealty.nonassociated_not_export') && (!isset($this->associations[$data_topic]) || $this->associations[$data_topic]['realty_type']==0)){
					continue;
				}
	
				$xml_collectorp[]=$this->exInternalId($data_item);
				$xml_collectorp[]=$this->exPropertyType($data_item);
				$operational_type='';
				$xml_collectorp[]=$this->exType($data_item, $operational_type);
				$xml_collectorp[]=$this->exCategory($data_item);
				$xml_collectorp[]=$this->exURL($data_item);
				$xml_collectorp[]=$this->exCreationDate($data_item);
				$xml_collectorp[]=$this->exLastUpdateDate($data_item);
				$xml_collectorp[]=$this->exExpireDate($data_item);
				$xml_collectorp[]=$this->exPayedAdv($data_item);
				$xml_collectorp[]=$this->exManuallyAdded($data_item);
				$xml_collectorp[]=$this->exLocation($data_item);
				$xml_collectorp[]=$this->exSalesAgent($data_item);
				$xml_collectorp[]=$this->exPrice($data_item, $operational_type);
				$xml_collectorp[]=$this->exNotForAgents($data_item);
				$xml_collectorp[]=$this->exHaggle($data_item);
				$xml_collectorp[]=$this->exMortgage($data_item);
				$xml_collectorp[]=$this->exPrepayment($data_item);
				$xml_collectorp[]=$this->exRentPflege($data_item);
				$xml_collectorp[]=$this->exAgentFee($data_item);
				$xml_collectorp[]=$this->exWithPets($data_item);
				$xml_collectorp[]=$this->exWithChildren($data_item);
				$xml_collectorp[]=$this->exDescription($data_item);
				$xml_collectorp[]=$this->exImages($data_item, $hasUploadify, $uploadsField);
				$xml_collectorp[]=$this->exRenovation($data_item);
				$xml_collectorp[]=$this->exArea($data_item);
				$xml_collectorp[]=$this->exLivingSpace($data_item);
				$xml_collectorp[]=$this->exKitchenSpace($data_item);
				$xml_collectorp[]=$this->exLotType($data_item);
				$xml_collectorp[]=$this->exNewFlat($data_item);
				$xml_collectorp[]=$this->exRooms($data_item);
				$xml_collectorp[]=$this->exRoomsType($data_item);
				$xml_collectorp[]=$this->exOpenPlan($data_item);
				$xml_collectorp[]=$this->exRoomsOffered($data_item);
				$xml_collectorp[]=$this->exPhone($data_item);
				$xml_collectorp[]=$this->exInternet($data_item);
				$xml_collectorp[]=$this->exRoomFurniture($data_item);
				$xml_collectorp[]=$this->exTelevision($data_item);
				$xml_collectorp[]=$this->exWashingMachine($data_item);
				$xml_collectorp[]=$this->exKitchenFurniture($data_item);
				$xml_collectorp[]=$this->exFloorCovering($data_item);
				$xml_collectorp[]=$this->exBathroomUnit($data_item);
				$xml_collectorp[]=$this->exBalcony($data_item);
				$xml_collectorp[]=$this->exRefrigerator($data_item);
				$xml_collectorp[]=$this->exBuildingType($data_item);
				$xml_collectorp[]=$this->exBuildingName($data_item);
				$xml_collectorp[]=$this->exFloorCount($data_item);
				$xml_collectorp[]=$this->exFloor($data_item);
				$xml_collectorp[]=$this->exWindowView($data_item);
				$xml_collectorp[]=$this->exReadyQuarter($data_item);
				$xml_collectorp[]=$this->exBuiltYear($data_item);
				$xml_collectorp[]=$this->exBuildingState($data_item);
				$xml_collectorp[]=$this->exBuildingSeries($data_item);
				$xml_collectorp[]=$this->exIsElite($data_item);
				$xml_collectorp[]=$this->exRubbishChute($data_item);
				$xml_collectorp[]=$this->exLift($data_item);
				$xml_collectorp[]=$this->exCeilingHeight($data_item);
				$xml_collectorp[]=$this->exAlarm($data_item);
				$xml_collectorp[]=$this->exParking($data_item);
				$xml_collectorp[]=$this->exSauna($data_item);
				$xml_collectorp[]=$this->exHeatingSupply($data_item);
				$xml_collectorp[]=$this->exWaterSupply($data_item);
				$xml_collectorp[]=$this->exSewerageSupply($data_item);
				$xml_collectorp[]=$this->exPmg($data_item);
				$xml_collectorp[]=$this->exKitchen($data_item);
				$xml_collectorp[]=$this->exPool($data_item);
				$xml_collectorp[]=$this->exBilliard($data_item);
				$xml_collectorp[]=$this->exElectricitySupply($data_item);
				$xml_collectorp[]=$this->exGasSupply($data_item);
				$xml_collectorp[]=$this->exToilet($data_item);
				$xml_collectorp[]=$this->exShower($data_item);
	
				$xml_collectorp[]='</offer>';
				foreach($xml_collectorp as $k=>$v){
					if($v==''){
						unset($xml_collectorp[$k]);
					}
				}
	
				if(!empty($xml_collectorp)){
					$xml_str=implode("\n", $xml_collectorp);
				}
	
			}else{
				$xml_str='';
				$this->errors[]=$data_item['id'].' DECLINED: No price or city name';
			}
				
				
				
				
			if(empty($this->errors) && $xml_str!=''){
				$xml_obj[]=$xml_str;
			}
			$this->errors=array();
		}
		
	
		return implode("\n", $xml_obj);
	
	}
	
	public function run_export(){
		$this->setExportMode();
		
		$this->setExportType();
		$this->remove_old_file();
		$data=$this->collectData();
		if(empty($data)){
			echo '<?xml version="1.0" encoding="utf-8" ?>';
			echo '<realty-feed xmlns="http://webmaster.yandex.ru/schemas/feed/realty/2010-06">';
			echo '<generation-date>'.$this->formdate().'</generation-date>';
			echo '</realty-feed>';
			exit();
		}
		
		if(1==$this->getConfigValue('apps.yandexrealty.tofile') && file_exists($this->export_file_storage.'/'.$this->export_file)){
			return file_get_contents($this->export_file_storage.'/'.$this->export_file);
		}
		
		if(1==(int)$this->getConfigValue('apps.yandexrealty.tofile')){
			$tofile=true;
		}else{
			$tofile=false;
		}
		
		$this->associations=$this->loadAssociations();
		
		require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php';
		$Structure=new Structure_Manager();
		$this->category_structure = $Structure->loadCategoryStructure();
		$x=$Structure->createCatalogChains();
		$this->catalogChains=$x['txt'];
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		$this->form_data_shared = $data_model->get_kvartira_model(false, true);
		$this->form_data_shared = $this->form_data_shared['data'];
		
		$image_field=trim($this->getConfigValue('apps.yandexrealty.images_field'));
		 
		$uploadsField=false;
		$hasUploadify=false;
		 
		if($image_field!='' && isset($this->form_data_shared[$image_field]) && in_array($this->form_data_shared[$image_field]['type'], array('uploads', 'uploadify_image'))){
			if($this->form_data_shared[$image_field]['type']=='uploadify_image'){
				$hasUploadify=true;
			}else{
				$uploadsField=$image_field;
			}
		}else{
			foreach($this->form_data_shared as $model_item){
				if($model_item['type']=='uploadify_image'){
					$hasUploadify=true;
					$uploadsField=false;
					break;
				}elseif($uploadsField===false && $model_item['type']=='uploads'){
					$uploadsField=$model_item['name'];
				}
			}
		}
		
		$xml_obj=array();
		
		if($tofile){
			$xml_obj[]='<?xml version="1.0" encoding="utf-8" ?>';
			$xml_obj[]='<realty-feed xmlns="http://webmaster.yandex.ru/schemas/feed/realty/2010-06">';
			$xml_obj[]='<generation-date>'.$this->formdate().'</generation-date>';
		}
		echo '<?xml version="1.0" encoding="utf-8" ?>';
		echo '<realty-feed xmlns="http://webmaster.yandex.ru/schemas/feed/realty/2010-06">';
		echo '<generation-date>'.$this->formdate().'</generation-date>';
		
		$log=array();
		$this->errors=array();
		
		
		$this->contacts_export_mode=intval($this->getConfigValue('apps.yandexrealty.contacts_export_mode'));
				 
		if($this->contacts_export_mode==1){
		
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/users/users_manager.php';
			$UM=new Users_Manager();
		
			$contacts_str=trim($this->getConfigValue('apps.yandexrealty.contacts_assoc_str'));
		
			if($contacts_str==''){
				$this->contacts_mode['*']=2;
			}else{
				$matches=array();
				if(preg_match('/^\*:([1-4])$/', $contacts_str, $matches)){
					$this->contacts_mode['*']=$matches[1];
				}else{
					$matches_all=array();
					if(preg_match_all('/((\*|[\d]+):([1-4]))/', $contacts_str, $matches_all)){
						foreach ($matches_all[2] as $k=>$g){
							if($g=='*'){
								$this->contacts_mode['*']=$matches_all[3][$k];
							}else{
								$this->contacts_mode[intval($g)]=$matches_all[3][$k];
							}
						}
					}else{
						$this->contacts_mode['*']=2;
					}
				}
			}
		
			$groups_assoc_str=trim($this->getConfigValue('apps.yandexrealty.groups_assoc_str'));
		
			if($groups_assoc_str==''){
				$this->group_assoc['*']='o';
			}else{
				$matches=array();
				if(preg_match('/^\*:([oad])$/', $groups_assoc_str, $matches)){
					$this->group_assoc['*']=$matches[1];
				}else{
					$matches_all=array();
					if(preg_match_all('/((\*|[\d]+):([oad]))/', $groups_assoc_str, $matches_all)){
						foreach ($matches_all[2] as $k=>$g){
							if($g=='*'){
								$this->group_assoc['*']=trim($matches_all[3][$k]);
							}else{
								$this->group_assoc[intval($g)]=trim($matches_all[3][$k]);
							}
							
						}
					}else{
						$this->group_assoc['*']='o';
					}
				}
			}
		}
		
		
		
		/*if(1==(int)$this->getConfigValue('apps.yandexrealty.nonassociated_not_export') && empty($this->associations)){
				
		}else{*/
			foreach($data as $data_item){
				$xml_collectorp=array();
				$xml_str='';
				if($data_item['price'] > 0 AND $data_item['city'] !== ''){
					$data_topic=(int)$data_item['topic_id'];
					
					//!empty($this->associations) && isset($this->associations[$data_topic]) && $this->associations[$data_topic]['realty_type']!=0
					if(1==(int)$this->getConfigValue('apps.yandexrealty.nonassociated_not_export') && (!isset($this->associations[$data_topic]) || $this->associations[$data_topic]['realty_type']==0)){
						continue;
					}
					$xml_collectorp=array();
					$xml_collectorp[]=$this->exInternalId($data_item);
					$xml_collector_item=$this->collectItemXML($data_item, $hasUploadify, $uploadsField);
					$xml_collectorp=array_merge($xml_collectorp, $xml_collector_item);
					$xml_collectorp[]='</offer>';
				/*	$xml_collectorp[]=$this->exInternalId($data_item);
					$xml_collectorp[]=$this->exPropertyType($data_item);
					$operational_type='';
					$xml_collectorp[]=$this->exType($data_item, $operational_type);
					$xml_collectorp[]=$this->exCategory($data_item);
					$xml_collectorp[]=$this->exURL($data_item);
					$xml_collectorp[]=$this->exCreationDate($data_item);
					$xml_collectorp[]=$this->exLastUpdateDate($data_item);
					$xml_collectorp[]=$this->exExpireDate($data_item);
					$xml_collectorp[]=$this->exPayedAdv($data_item);
					$xml_collectorp[]=$this->exManuallyAdded($data_item);
					$xml_collectorp[]=$this->exLocation($data_item);
					$xml_collectorp[]=$this->exSalesAgent($data_item);
					$xml_collectorp[]=$this->exPrice($data_item, $operational_type);
					$xml_collectorp[]=$this->exNotForAgents($data_item);
					$xml_collectorp[]=$this->exHaggle($data_item);
					$xml_collectorp[]=$this->exMortgage($data_item);
					$xml_collectorp[]=$this->exPrepayment($data_item);
					$xml_collectorp[]=$this->exRentPflege($data_item);
					$xml_collectorp[]=$this->exAgentFee($data_item);
					$xml_collectorp[]=$this->exDealStatus($data_item);
					$xml_collectorp[]=$this->exWithPets($data_item);
					$xml_collectorp[]=$this->exWithChildren($data_item);
					$xml_collectorp[]=$this->exDescription($data_item);
					$xml_collectorp[]=$this->exImages($data_item, $hasUploadify, $uploadsField);
					$xml_collectorp[]=$this->exRenovation($data_item);
					$xml_collectorp[]=$this->exArea($data_item);
					$xml_collectorp[]=$this->exLivingSpace($data_item);
					$xml_collectorp[]=$this->exKitchenSpace($data_item);
					$xml_collectorp[]=$this->exLotType($data_item);
					$xml_collectorp[]=$this->exNewFlat($data_item);
					$xml_collectorp[]=$this->exRooms($data_item);
					$xml_collectorp[]=$this->exRoomsType($data_item);
					$xml_collectorp[]=$this->exOpenPlan($data_item);
					$xml_collectorp[]=$this->exRoomsOffered($data_item);
					$xml_collectorp[]=$this->exPhone($data_item);
					$xml_collectorp[]=$this->exInternet($data_item);
					$xml_collectorp[]=$this->exRoomFurniture($data_item);
					$xml_collectorp[]=$this->exTelevision($data_item);
					$xml_collectorp[]=$this->exWashingMachine($data_item);
					$xml_collectorp[]=$this->exKitchenFurniture($data_item);
					$xml_collectorp[]=$this->exFloorCovering($data_item);
					$xml_collectorp[]=$this->exBathroomUnit($data_item);
					$xml_collectorp[]=$this->exBalcony($data_item);
					$xml_collectorp[]=$this->exRefrigerator($data_item);
					$xml_collectorp[]=$this->exBuildingType($data_item);
					$xml_collectorp[]=$this->exBuildingName($data_item);
					$xml_collectorp[]=$this->exFloorCount($data_item);
					$xml_collectorp[]=$this->exFloor($data_item);
					$xml_collectorp[]=$this->exWindowView($data_item);
					$xml_collectorp[]=$this->exReadyQuarter($data_item);
					$xml_collectorp[]=$this->exBuiltYear($data_item);
					$xml_collectorp[]=$this->exBuildingState($data_item);
					$xml_collectorp[]=$this->exBuildingSeries($data_item);
					$xml_collectorp[]=$this->exIsElite($data_item);
					$xml_collectorp[]=$this->exRubbishChute($data_item);
					$xml_collectorp[]=$this->exLift($data_item);
					$xml_collectorp[]=$this->exCeilingHeight($data_item);
					$xml_collectorp[]=$this->exAlarm($data_item);
					$xml_collectorp[]=$this->exParking($data_item);
					$xml_collectorp[]=$this->exSauna($data_item);
					$xml_collectorp[]=$this->exHeatingSupply($data_item);
					$xml_collectorp[]=$this->exWaterSupply($data_item);
					$xml_collectorp[]=$this->exSewerageSupply($data_item);
					$xml_collectorp[]=$this->exPmg($data_item);
					$xml_collectorp[]=$this->exKitchen($data_item);
					$xml_collectorp[]=$this->exPool($data_item);
					$xml_collectorp[]=$this->exBilliard($data_item);
					$xml_collectorp[]=$this->exElectricitySupply($data_item);
					$xml_collectorp[]=$this->exGasSupply($data_item);
					$xml_collectorp[]=$this->exToilet($data_item);
					$xml_collectorp[]=$this->exShower($data_item);
					if ( $this->getConfigValue('apps.yandexrealty.complex_enable') ) {
						$xml_collectorp[]=$this->exComplexData($data_item);
					}
						
					$xml_collectorp[]='</offer>';*/
					foreach($xml_collectorp as $k=>$v){
						if($v==''){
							unset($xml_collectorp[$k]);
						}
					}
						
					if(!empty($xml_collectorp)){
						$xml_str=implode("\n", $xml_collectorp);
					}
						
						
					/*if(empty($errors)){
					 if($tofile){
					$xml_obj[]=implode("\n", $xml_collectorp);
					}
					echo implode("\n", $xml_collectorp);
					$log[]=$data_item['id'].' EXPORTED';
					}else{
						
					}*/
				}else{
					$xml_str='';
					$this->errors[]=$data_item['id'].' DECLINED: No price or city name';
				}
					
					
					
					
				if(empty($this->errors) && $xml_str!=''){
					if($tofile){
						$xml_obj[]=$xml_str;
					}
					echo $xml_str;
					$log[]=$data_item['id'].' EXPORTED';
				}elseif(!empty($this->errors) && $xml_str!=''){
					foreach($this->errors as $er){
						$log[]=$er;
					}
				}
				$this->errors=array();
			}
		/*}*/
		
		
		if($tofile){
			$xml_obj[]='</realty-feed>';
			$f=fopen($this->export_file_storage.'/'.$this->export_file, 'w');
			fwrite($f, implode("\n", $xml_obj));
			fclose($f);
		}
		
		
		echo '</realty-feed>';
		
		//print_r($log);
	}
	
	protected function collectItemXML($data_item, $hasUploadify, $uploadsField){
		$xml_collectorp=array();
		//$xml_collectorp[]=$this->exInternalId($data_item);
		$xml_collectorp[]=$this->exPropertyType($data_item);
		$operational_type='';
		$xml_collectorp[]=$this->exType($data_item, $operational_type);
		$xml_collectorp[]=$this->exCategory($data_item);
		$xml_collectorp[]=$this->exCommercialType($data_item);
		$xml_collectorp[]=$this->exURL($data_item);
		$xml_collectorp[]=$this->exCreationDate($data_item);
		$xml_collectorp[]=$this->exLastUpdateDate($data_item);
		$xml_collectorp[]=$this->exExpireDate($data_item);
		$xml_collectorp[]=$this->exPayedAdv($data_item);
		$xml_collectorp[]=$this->exManuallyAdded($data_item);
		$xml_collectorp[]=$this->exLocation($data_item);
		$xml_collectorp[]=$this->exSalesAgent($data_item);
		$xml_collectorp[]=$this->exPrice($data_item, $operational_type);
		$xml_collectorp[]=$this->exNotForAgents($data_item);
		$xml_collectorp[]=$this->exHaggle($data_item);
		$xml_collectorp[]=$this->exMortgage($data_item);
		$xml_collectorp[]=$this->exPrepayment($data_item);
		$xml_collectorp[]=$this->exRentPflege($data_item);
		$xml_collectorp[]=$this->exAgentFee($data_item);
		$xml_collectorp[]=$this->exDealStatus($data_item);
		$xml_collectorp[]=$this->exWithPets($data_item);
		$xml_collectorp[]=$this->exWithChildren($data_item);
		$xml_collectorp[]=$this->exDescription($data_item);
		$xml_collectorp[]=$this->exImages($data_item, $hasUploadify, $uploadsField);
		$xml_collectorp[]=$this->exRenovation($data_item);
		$xml_collectorp[]=$this->exArea($data_item);
		$xml_collectorp[]=$this->exLivingSpace($data_item);
		$xml_collectorp[]=$this->exKitchenSpace($data_item);
		$xml_collectorp[]=$this->exLotType($data_item);
		$xml_collectorp[]=$this->exNewFlat($data_item);
		$xml_collectorp[]=$this->exRooms($data_item);
		$xml_collectorp[]=$this->exRoomsType($data_item);
		$xml_collectorp[]=$this->exOpenPlan($data_item);
		$xml_collectorp[]=$this->exRoomsOffered($data_item);
		$xml_collectorp[]=$this->exPhone($data_item);
		$xml_collectorp[]=$this->exInternet($data_item);
		$xml_collectorp[]=$this->exRoomFurniture($data_item);
		$xml_collectorp[]=$this->exTelevision($data_item);
		$xml_collectorp[]=$this->exWashingMachine($data_item);
		$xml_collectorp[]=$this->exKitchenFurniture($data_item);
		$xml_collectorp[]=$this->exFloorCovering($data_item);
		$xml_collectorp[]=$this->exBathroomUnit($data_item);
		$xml_collectorp[]=$this->exBalcony($data_item);
		$xml_collectorp[]=$this->exRefrigerator($data_item);
		$xml_collectorp[]=$this->exBuildingType($data_item);
		$xml_collectorp[]=$this->exBuildingName($data_item);
		$xml_collectorp[]=$this->exFloorCount($data_item);
		$xml_collectorp[]=$this->exFloor($data_item);
		$xml_collectorp[]=$this->exWindowView($data_item);
		$xml_collectorp[]=$this->exReadyQuarter($data_item);
		$xml_collectorp[]=$this->exBuiltYear($data_item);
		$xml_collectorp[]=$this->exBuildingState($data_item);
		$xml_collectorp[]=$this->exBuildingSeries($data_item);
		$xml_collectorp[]=$this->exIsElite($data_item);
		$xml_collectorp[]=$this->exRubbishChute($data_item);
		$xml_collectorp[]=$this->exLift($data_item);
		$xml_collectorp[]=$this->exCeilingHeight($data_item);
		$xml_collectorp[]=$this->exAlarm($data_item);
		$xml_collectorp[]=$this->exParking($data_item);
		$xml_collectorp[]=$this->exSauna($data_item);
		$xml_collectorp[]=$this->exHeatingSupply($data_item);
		$xml_collectorp[]=$this->exWaterSupply($data_item);
		$xml_collectorp[]=$this->exSewerageSupply($data_item);
		$xml_collectorp[]=$this->exPmg($data_item);
		$xml_collectorp[]=$this->exKitchen($data_item);
		$xml_collectorp[]=$this->exPool($data_item);
		$xml_collectorp[]=$this->exBilliard($data_item);
		$xml_collectorp[]=$this->exElectricitySupply($data_item);
		$xml_collectorp[]=$this->exGasSupply($data_item);
		$xml_collectorp[]=$this->exToilet($data_item);
		$xml_collectorp[]=$this->exShower($data_item);
		//$xml_collectorp[]='</offer>';
		/*foreach($xml_collectorp as $k=>$v){
			if($v==''){
				unset($xml_collectorp[$k]);
			}
		}*/
		return $xml_collectorp;
	}
	
	protected function exElectricitySupply($data_item){
		if(isset($this->form_data_shared['electricity_supply']) && isset($data_item['electricity_supply'])){
			if((int)$data_item['electricity_supply']==1){
				return '<electricity-supply>1</electricity-supply>';
			}else{
				return '<electricity-supply>0</electricity-supply>';
			}
		}
	}
	
	protected function exGasSupply($data_item){
		if(isset($this->form_data_shared['gas_supply']) && isset($data_item['gas_supply'])){
			if((int)$data_item['gas_supply']==1){
				return '<gas-supply>1</gas-supply>';
			}else{
				return '<gas-supply>0</gas-supply>';
			}
		}
	}
	
	protected function exToilet($data_item){
		if(isset($this->form_data_shared['toilet']) && isset($data_item['toilet'])){
			$mf=$this->form_data_shared['toilet'];
			$di=$data_item['toilet'];
			if($mf['type']=='select_box' && intval($di)!=0 && isset($mf['select_data'][$di])){
				return '<toilet>'.self::symbolsClear($mf['select_data'][$di]).'</toilet>';
			}elseif($mf['type']!='select_box' &&  $di!=''){
				return '<toilet>'.self::symbolsClear($di).'</toilet>';
			}
		}
		/*if(isset($this->form_data_shared['toilet']) && isset($data_item['toilet']) && $data_item['toilet']!=''){
			return '<toilet>'.self::symbolsClear($data_item['toilet']).'</toilet>';
		}*/
	}
	
	protected function exShower($data_item){
		if(isset($this->form_data_shared['shower']) && isset($data_item['shower'])){
			$mf=$this->form_data_shared['shower'];
			$di=$data_item['shower'];
			if($mf['type']=='select_box' && intval($di)!=0 && isset($mf['select_data'][$di])){
				return '<shower>'.self::symbolsClear($mf['select_data'][$di]).'</shower>';
			}elseif($mf['type']!='select_box' &&  $di!=''){
				return '<shower>'.self::symbolsClear($di).'</shower>';
			}
		}
		/*if(isset($this->form_data_shared['shower']) && isset($data_item['shower']) && $data_item['shower']!=''){
			return '<shower>'.self::symbolsClear($data_item['shower']).'</shower>';
		}*/
	}
	
	protected function exComplexData($data_item){
		$rs = '';
		if(isset($data_item['building-name']) and $data_item['building-name'] != ''){
			$rs .= '<building-name>'.self::symbolsClear($data_item['building-name']).'</building-name>';
		}
		if(isset($data_item['building-type']) and $data_item['building-type'] != ''){
			$rs .= '<building-type>'.self::symbolsClear($data_item['building-type']).'</building-type>';
		}
		if(isset($data_item['renovation']) and $data_item['renovation'] != ''){
			$rs .= '<renovation>'.self::symbolsClear($data_item['renovation']).'</renovation>';
		}
		if(isset($data_item['built-year']) and $data_item['built-year'] != ''){
			$rs .= '<built-year>'.self::symbolsClear($data_item['built-year']).'</built-year>';
		}
		return $rs;
	}
	
	
	protected function exSauna($data_item){
		if(isset($this->form_data_shared['sauna']) && isset($data_item['sauna'])){
			if((int)$data_item['sauna']==1){
				return '<sauna>1</sauna>';
			}else{
				return '<sauna>0</sauna>';
			}
		}
	}
	
	protected function exHeatingSupply($data_item){
		if(isset($this->form_data_shared['heating_supply']) && isset($data_item['heating_supply'])){
			if((int)$data_item['heating_supply']==1){
				return '<heating-supply>1</heating-supply>';
			}else{
				return '<heating-supply>0</heating-supply>';
			}
		}
	}
	
	protected function exWaterSupply($data_item){
		if(isset($this->form_data_shared['water_supply']) && isset($data_item['water_supply'])){
			if((int)$data_item['water_supply']==1){
				return '<water-supply>1</water-supply>';
			}else{
				return '<water-supply>0</water-supply>';
			}
		}
	}
	
	protected function exSewerageSupply($data_item){
		if(isset($this->form_data_shared['sewerage_supply']) && isset($data_item['sewerage_supply'])){
			if((int)$data_item['sewerage_supply']==1){
				return '<sewerage-supply>1</sewerage-supply>';
			}else{
				return '<sewerage-supply>0</sewerage-supply>';
			}
		}
	}
	
	protected function exPmg($data_item){
		if(isset($this->form_data_shared['pmg']) && isset($data_item['pmg'])){
			if((int)$data_item['pmg']==1){
				return '<pmg>1</pmg>';
			}else{
				return '<pmg>0</pmg>';
			}
		}
	}
	
	protected function exKitchen($data_item){
		if(isset($this->form_data_shared['kitchen']) && isset($data_item['kitchen'])){
			if((int)$data_item['kitchen']==1){
				return '<kitchen>1</kitchen>';
			}else{
				return '<kitchen>0</kitchen>';
			}
		}
	}
	
	protected function exPool($data_item){
		if(isset($this->form_data_shared['pool']) && isset($data_item['pool'])){
			if((int)$data_item['pool']==1){
				return '<pool>1</pool>';
			}else{
				return '<pool>0</pool>';
			}
		}
	}
	
	protected function exBilliard($data_item){
		if(isset($this->form_data_shared['billiard']) && isset($data_item['billiard'])){
			if((int)$data_item['billiard']==1){
				return '<billiard>1</billiard>';
			}else{
				return '<billiard>0</billiard>';
			}
		}
	}

	protected function exParking($data_item){
		if(isset($this->form_data_shared['parking']) && isset($data_item['parking'])){
			if((int)$data_item['parking']==1){
				return '<parking>1</parking>';
			}else{
				return '<parking>0</parking>';
			}
		}
	}
	
	protected function exAlarm($data_item){
		if(isset($this->form_data_shared['alarm']) && isset($data_item['alarm'])){
			if((int)$data_item['alarm']==1){
				return '<alarm>1</alarm>';
			}else{
				return '<alarm>0</alarm>';
			}
		}
	}
	
	protected function exCeilingHeight($data_item){
		if(isset($this->form_data_shared['ceiling_height']) && isset($data_item['ceiling_height'])){
			$x=preg_replace('/[^0-9.,]/','',$data_item['ceiling_height']);
			$x=str_replace(',', '.', $x);
			$x=floatval($x);
			if($x!=0){
				return '<ceiling-height>'.$x.'</ceiling-height>';
			}
		}
	}
	
	protected function exLift($data_item){
		if(isset($this->form_data_shared['lift']) && isset($data_item['lift'])){
			if((int)$data_item['lift']==1){
				return '<lift>1</lift>';
			}else{
				return '<lift>0</lift>';
			}
		}
	}
	
	protected function exRubbishChute($data_item){
		if(isset($this->form_data_shared['rubbish_chute']) && isset($data_item['rubbish_chute'])){
			if((int)$data_item['rubbish_chute']==1){
				return '<rubbish-chute>1</rubbish-chute>';
			}else{
				return '<rubbish-chute>0</rubbish-chute>';
			}
		}
	}
	
	protected function exIsElite($data_item){
		if(isset($this->form_data_shared['elite']) && isset($data_item['elite'])){
			if((int)$data_item['elite']==1){
				return '<is-elite>1</is-elite>';
			}
		}
	}
	
	protected function exBuildingSeries($data_item){
		if(isset($this->form_data_shared['building_series']) && isset($data_item['building_series']) && $data_item['building_series']!=''){
			return '<building-series>'.self::symbolsClear($data_item['building_series']).'</building-series>';
		}
	}
	
	protected function exBuildingState($data_item){
		if(isset($this->form_data_shared['building_state']) && isset($data_item['building_state']) && $data_item['building_state']!=''){
			return '<building-state>'.self::symbolsClear($data_item['building_state']).'</building-state>';
		}
	}
	
	protected function exBuiltYear($data_item){
		if(isset($this->form_data_shared['built_year']) && isset($data_item['built_year']) && $data_item['built_year']!=''){
			$x=preg_replace('/[^0-9]/', '', $data_item['built_year']);
			if(preg_match('/([1|2][0-9][0-9][0-9])/', $x, $matches)){
				return '<built-year>'.$matches[1].'</built-year>';
			}
		}
	}
	
	protected function exReadyQuarter($data_item){
		if(isset($this->form_data_shared['ready_quarter']) && isset($data_item['ready_quarter']) && $data_item['ready_quarter']!=''){
			$x=preg_replace('/[^0-9]/', '', $data_item['ready_quarter']);
			if(preg_match('/([1-4])/', $x, $matches)){
				return '<ready-quarter>'.$matches[1].'</ready-quarter>';
			}
		}
	}
	
	protected function exWindowView($data_item){
		if(isset($this->form_data_shared['window_view']) && isset($data_item['window_view'])){
			$mf=$this->form_data_shared['window_view'];
			$di=$data_item['window_view'];
			if($mf['type']=='select_box' && intval($di)!=0 && isset($mf['select_data'][$di])){
				return '<window-view>'.self::symbolsClear($mf['select_data'][$di]).'</window-view>';
			}elseif($mf['type']!='select_box' &&  $di!=''){
				return '<window-view>'.self::symbolsClear($di).'</window-view>';
			}
		}
		/*if(isset($this->form_data_shared['window_view']) && isset($data_item['window_view']) && $data_item['window_view']!=''){
			return '<window-view>'.self::symbolsClear($data_item['window_view']).'</window-view>';
		}*/
	}
	
	protected function exFloor($data_item){
		if(isset($this->form_data_shared['floor']) && isset($data_item['floor']) && (int)$data_item['floor']!=0){
			return '<floor>'.(int)$data_item['floor'].'</floor>';
		}
	}
	
	protected function exFloorCount($data_item){
		if(isset($this->form_data_shared['floor_count']) && isset($data_item['floor_count']) && (int)$data_item['floor_count']!=0){
			return '<floors-total>'.(int)$data_item['floor_count'].'</floors-total>';
		}
	}
	
	protected function exBuildingName($data_item){
		if(isset($this->form_data_shared['building_name']) && isset($data_item['building_name']) && $data_item['building_name']!=''){
			return '<building-name>'.self::symbolsClear($data_item['building_name']).'</building-name>';
		}
	}
	
	protected function exBuildingType($data_item){
		if(isset($this->form_data_shared['building_type']) && isset($data_item['building_type'])){
			$mf=$this->form_data_shared['building_type'];
			$di=$data_item['building_type'];
			if($mf['type']=='select_box' && intval($di)!=0 && isset($mf['select_data'][$di])){
				return '<building-type>'.self::symbolsClear($mf['select_data'][$di]).'</building-type>';
			}elseif($mf['type']!='select_box' &&  $di!=''){
				return '<building-type>'.self::symbolsClear($di).'</building-type>';
			}
		}elseif(isset($this->form_data_shared['walls']) && isset($data_item['walls'])){
			$mf=$this->form_data_shared['walls'];
			$di=$data_item['walls'];
			if($mf['type']=='select_box' && intval($di)!=0 && isset($mf['select_data'][$di])){
				return '<building-type>'.self::symbolsClear($mf['select_data'][$di]).'</building-type>';
			}elseif($mf['type']!='select_box' &&  $di!=''){
				return '<building-type>'.self::symbolsClear($di).'</building-type>';
			}
		}
		/*if(isset($this->form_data_shared['building_type']) && isset($data_item['building_type']) && $data_item['building_type']!=''){
			return '<building-type>'.self::symbolsClear($data_item['building_type']).'</building-type>';
		}elseif(isset($this->form_data_shared['walls']) && isset($data_item['walls']) && $data_item['walls']!=''){
			return '<building-type>'.self::symbolsClear($data_item['walls']).'</building-type>';
		}*/
	}
		
	protected function exRefrigerator($data_item){
		if(isset($this->form_data_shared['refrigerator']) && isset($data_item['refrigerator'])){
			if((int)$data_item['refrigerator']==1){
				return '<refrigerator>1</refrigerator>';
			}else{
				return '<refrigerator>0</refrigerator>';
			}
		}
	}
	
	protected function exBalcony($data_item){
		if(isset($this->form_data_shared['balcony']) && isset($data_item['balcony'])){
			$mf=$this->form_data_shared['balcony'];
			$di=$data_item['balcony'];
			if($mf['type']=='select_box' && intval($di)!=0 && isset($mf['select_data'][$di])){
				return '<balcony>'.self::symbolsClear($mf['select_data'][$di]).'</balcony>';
			}elseif($mf['type']!='select_box' &&  $di!=''){
				return '<balcony>'.self::symbolsClear($di).'</balcony>';
			}
		}
		/*if(isset($this->form_data_shared['balcony']) && isset($data_item['balcony']) && $data_item['balcony']!=''){
			return '<balcony>'.self::symbolsClear($data_item['balcony']).'</balcony>';
		}*/
	}
	
	protected function exBathroomUnit($data_item){
		if(isset($this->form_data_shared['bathroom_unit']) && isset($data_item['bathroom_unit'])){
			$mf=$this->form_data_shared['bathroom_unit'];
			$di=$data_item['bathroom_unit'];
			if($mf['type']=='select_box' && intval($di)!=0 && isset($mf['select_data'][$di])){
				return '<bathroom-unit>'.self::symbolsClear($mf['select_data'][$di]).'</bathroom-unit>';
			}elseif($mf['type']!='select_box' &&  $di!=''){
				return '<bathroom-unit>'.self::symbolsClear($di).'</bathroom-unit>';
			}
		}
		/*if(isset($this->form_data_shared['bathroom_unit']) && isset($data_item['bathroom_unit']) && $data_item['bathroom_unit']!=''){
			return '<bathroom-unit>'.self::symbolsClear($data_item['bathroom_unit']).'</bathroom-unit>';
		}*/
	}
	
	protected function exFloorCovering($data_item){
		if(isset($this->form_data_shared['floor_covering']) && isset($data_item['floor_covering'])){
			$mf=$this->form_data_shared['floor_covering'];
			$di=$data_item['floor_covering'];
			if($mf['type']=='select_box' && intval($di)!=0 && isset($mf['select_data'][$di])){
				return '<floor-covering>'.self::symbolsClear($mf['select_data'][$di]).'</floor-covering>';
			}elseif($mf['type']!='select_box' &&  $di!=''){
				return '<floor-covering>'.self::symbolsClear($di).'</floor-covering>';
			}
		}
		/*if(isset($this->form_data_shared['floor_covering']) && isset($data_item['floor_covering']) && $data_item['floor_covering']!=''){
			return '<floor-covering>'.self::symbolsClear($data_item['floor_covering']).'</floor-covering>';
		}*/
	}
	
	protected function exKitchenFurniture($data_item){
		if(isset($this->form_data_shared['kitchen_furniture']) && isset($data_item['kitchen_furniture'])){
			if((int)$data_item['kitchen_furniture']==1){
				return '<kitchen-furniture>1</kitchen-furniture>';
			}else{
				return '<kitchen-furniture>0</kitchen-furniture>';
			}
		}
	}
	
	protected function exWashingMachine($data_item){
		if(isset($this->form_data_shared['washing_machine']) && isset($data_item['washing_machine'])){
			if((int)$data_item['washing_machine']==1){
				return '<washing-machine>1</washing-machine>';
			}else{
				return '<washing-machine>0</washing-machine>';
			}
		}
	}
	
	protected function exTelevision($data_item){
		if(isset($this->form_data_shared['television']) && isset($data_item['television'])){
			if((int)$data_item['television']==1){
				return '<television>1</television>';
			}else{
				return '<television>0</television>';
			}
		}
	}
	
	protected function exPhone($data_item){
		if(isset($this->form_data_shared['is_telephone']) && isset($data_item['is_telephone'])){
			if((int)$data_item['is_telephone']==1){
				return '<phone>1</phone>';
			}else{
				return '<phone>0</phone>';
			}
		}
	}
	
	protected function exInternet($data_item){
		if(isset($this->form_data_shared['internet']) && isset($data_item['internet'])){
			if((int)$data_item['internet']==1){
				return '<internet>1</internet>';
			}else{
				return '<internet>0</internet>';
			}
		}
	}
	
	protected function exRoomFurniture($data_item){
		if(isset($this->form_data_shared['room_furniture']) && isset($data_item['room_furniture'])){
			if((int)$data_item['room_furniture']==1){
				return '<room-furniture>1</room-furniture>';
			}else{
				return '<room-furniture>0</room-furniture>';
			}
		}
	}
	
	
	
	protected function exRoomsOffered($data_item){
		if(isset($this->form_data_shared['rooms_offered']) && isset($data_item['rooms_offered']) && (int)$data_item['rooms_offered']!=0){
			return '<rooms-offered>'.(int)$data_item['rooms_offered'].'</rooms-offered>';
		}else{
			return '<rooms-offered>'.(int)$data_item['room_count'].'</rooms-offered>';
		}
	}
	
	protected function exOpenPlan($data_item){
		if(isset($this->form_data_shared['open_plan']) && isset($data_item['open_plan'])){
			if((int)$data_item['open_plan']==1){
				return '<open-plan>1</open-plan>';
			}
		}
		return '';
	}
	
	protected function exRoomsType($data_item){
		if(isset($this->form_data_shared['rooms_type']) && isset($data_item['rooms_type'])){
			$mf=$this->form_data_shared['rooms_type'];
			$di=$data_item['rooms_type'];
			if($mf['type']=='select_box' && intval($di)!=0 && isset($mf['select_data'][$di])){
				return '<rooms-type>'.self::symbolsClear($mf['select_data'][$di]).'</rooms-type>';
			}elseif($mf['type']!='select_box' &&  $di!=''){
				return '<rooms-type>'.self::symbolsClear($di).'</rooms-type>';
			}
		}
	}
	
	protected function exRooms($data_item){
		if(isset($this->form_data_shared['rooms']) && isset($data_item['rooms']) && (int)$data_item['rooms']!=0){
			return '<rooms>'.(int)$data_item['rooms'].'</rooms>';
		}elseif(isset($this->form_data_shared['room_count']) && isset($data_item['room_count']) && (int)$data_item['room_count']!=0){
			return '<rooms>'.(int)$data_item['room_count'].'</rooms>';
		}
	}
	
	protected function exNewFlat($data_item){
		if(isset($this->form_data_shared['new_flat']) && isset($data_item['new_flat'])){
			if((int)$data_item['new_flat']==1){
				return '<new-flat>1</new-flat>';
			}
		}
		
		return '';
	}
	
	protected function exLotType($data_item){
		$data_topic=(int)$data_item['topic_id'];
		$rs='';
		if(!empty($this->associations) && isset($this->associations[$data_topic]) && $this->associations[$data_topic]['realty_category']!=0){
			
			if(in_array($this->associations[$data_topic]['realty_category'], array(4, 15, 16, 25))){
				$lot_area_field=trim($this->getConfigValue('apps.yandexrealty.lot_area'));
					
				$meash='сот';
				$lot_area_field_dim=trim($this->getConfigValue('apps.yandexrealty.lot_area_dim'));
				if($lot_area_field_dim=='' && isset($this->form_data_shared['square_unit'])){
					if($data_item['square_unit']==2){
						$meash='кв.м';
					}elseif($data_item['square_unit']==3){
						$meash='га';
					}
				}elseif($lot_area_field_dim!=''){
					if($lot_area_field_dim=='acr'){
						$meash='сот';
					}elseif($lot_area_field_dim=='sqm'){
						$meash='кв.м';
					}elseif($lot_area_field_dim=='ha'){
						$meash='га';
					}
				}
				
				if($lot_area_field=='' && isset($this->form_data_shared['land_area'])){
					$lot_area_field='land_area';
				}elseif($lot_area_field=='' && isset($this->form_data_shared['lot_area'])){
					$lot_area_field='lot_area';
				}
				
				if(isset($data_item[$lot_area_field])){
					$x=preg_replace('/[^0-9.,]/','',$data_item[$lot_area_field]);
					$x=str_replace(',', '.', $x);
					$x=floatval($x);
					//$x=preg_replace('/[^0-9\.,]/','',$data_item['lot_area']);
					if($x!=0){
						$rs.='<lot-area>'."\n";
						$rs.='<value>'.$x.'</value>'."\n";
						$rs.='<unit>'.$meash.'</unit>'."\n";
						$rs.='</lot-area>'."\n";
					}
				}
			}elseif(in_array($this->associations[$data_topic]['realty_category'], array(12, 13))){
				$lot_area_field=trim($this->getConfigValue('apps.yandexrealty.add_lot_area'));
					
				$meash='сот';
				$lot_area_field_dim=trim($this->getConfigValue('apps.yandexrealty.add_lot_area_dim'));
				if($lot_area_field_dim=='' && isset($this->form_data_shared['square_unit'])){
					if($data_item['square_unit']==2){
						$meash='кв.м';
					}elseif($data_item['square_unit']==3){
						$meash='га';
					}
				}elseif($lot_area_field_dim!=''){
					if($lot_area_field_dim=='acr'){
						$meash='сот';
					}elseif($lot_area_field_dim=='sqm'){
						$meash='кв.м';
					}elseif($lot_area_field_dim=='ha'){
						$meash='га';
					}
				}
				
				if($lot_area_field=='' && isset($this->form_data_shared['land_area'])){
					$lot_area_field='land_area';
				}elseif($lot_area_field=='' && isset($this->form_data_shared['lot_area'])){
					$lot_area_field='lot_area';
				}
					
				if(isset($data_item[$lot_area_field])){
					$x=preg_replace('/[^0-9.,]/','',$data_item[$lot_area_field]);
					$x=str_replace(',', '.', $x);
					$x=floatval($x);
					//$x=preg_replace('/[^0-9\.,]/','',$data_item['lot_area']);
					if($x!=0){
						$rs.='<lot-area>'."\n";
						$rs.='<value>'.$x.'</value>'."\n";
						$rs.='<unit>'.$meash.'</unit>'."\n";
						$rs.='</lot-area>'."\n";
					}
				}
			}
			
			if(in_array($this->associations[$data_topic]['realty_category'], array(4, 12, 13, 15, 16))){
				if(isset($this->form_data_shared['lot_type']) && isset($data_item['lot_type'])){
					$mf=$this->form_data_shared['lot_type'];
					$di=$data_item['lot_type'];
					if($mf['type']=='select_box' && intval($di)!=0 && isset($mf['select_data'][$di])){
						$rs.='<lot-type>'.self::symbolsClear($mf['select_data'][$di]).'</lot-type>';
					}elseif($mf['type']!='select_box' &&  $di!=''){
						$rs.='<lot-type>'.self::symbolsClear($di).'</lot-type>';
					}
				}
			}
			
			
			
			/*if(in_array($this->associations[$data_topic]['realty_category'], array(4, 12, 13, 15, 16))){
				$meash='сот';
				if(isset($this->form_data_shared['square_unit'])){
					if($data_item['square_unit']==2){
						$meash='кв.м';
					}elseif($data_item['square_unit']==3){
						$meash='га';
					}
				}
				
				if(isset($this->form_data_shared['land_area']) && isset($data_item['land_area'])){
					$x=preg_replace('/[^0-9.,]/','',$data_item['land_area']);
					$x=str_replace(',', '.', $x);
					$x=floatval($x);
					if($x!=0){
						$rs.='<lot-area>'."\n";
						$rs.='<value>'.$x.'</value>'."\n";
						$rs.='<unit>'.$meash.'</unit>'."\n";
						$rs.='</lot-area>'."\n";
					}
				}elseif(isset($this->form_data_shared['lot_area']) && isset($data_item['lot_area'])){
					$x=preg_replace('/[^0-9.,]/','',$data_item['lot_area']);
					$x=str_replace(',', '.', $x);
					$x=floatval($x);
					if($x!=0){
						$rs.='<lot-area>'."\n";
						$rs.='<value>'.$x.'</value>'."\n";
						$rs.='<unit>'.$meash.'</unit>'."\n";
						$rs.='</lot-area>'."\n";
					}
				}
				if(isset($this->form_data_shared['lot_type']) && isset($data_item['lot_type'])){
					$mf=$this->form_data_shared['lot_type'];
					$di=$data_item['lot_type'];
					if($mf['type']=='select_box' && intval($di)!=0 && isset($mf['select_data'][$di])){
						$rs.='<lot-type>'.self::symbolsClear($mf['select_data'][$di]).'</lot-type>';
					}elseif($mf['type']!='select_box' &&  $di!=''){
						$rs.='<lot-type>'.self::symbolsClear($di).'</lot-type>';
					}
				}
			}*/
		}
		return $rs;
	}
	
protected function exKitchenSpace($data_item){
		$rs='';
		$data_topic=(int)$data_item['topic_id'];
		if(!in_array($this->associations[$data_topic]['realty_category'], array(4, 15, 16))){
			$x=preg_replace('/[^0-9.,]/','',$data_item['square_kitchen']);
			$x=str_replace(',', '.', $x);
			$x=floatval($x);
			if($x!=0){
				$rs.='<kitchen-space>'."\n";
				$rs.='<value>'.$x.'</value>'."\n";
				$rs.='<unit>кв.м</unit>'."\n";
				$rs.='</kitchen-space>';
			}
		}
		return $rs;
	}
	
	protected function exLivingSpace($data_item){
		$rs='';
		$data_topic=(int)$data_item['topic_id'];
		if(!in_array($this->associations[$data_topic]['realty_category'], array(4, 15, 16))){
			$x=preg_replace('/[^0-9.,]/','',$data_item['square_live']);
			$x=str_replace(',', '.', $x);
			$x=floatval($x);
			if($x!=0){
				$rs.='<living-space>'."\n";
				$rs.='<value>'.$x.'</value>'."\n";
				$rs.='<unit>кв.м</unit>'."\n";
				$rs.='</living-space>';
			}
		}
		return $rs;
	}
	
	protected function exArea($data_item){
		$rs='';
		$data_topic=(int)$data_item['topic_id'];
		if(!in_array($this->associations[$data_topic]['realty_category'], array(4, 15, 16))){
			$x=preg_replace('/[^0-9.,]/','',$data_item['square_all']);
			$x=str_replace(',', '.', $x);
			$x=floatval($x);
			if($x!=0){
				$rs.='<area>'."\n";
				$rs.='<value>'.$x.'</value>'."\n";
				$rs.='<unit>кв.м</unit>'."\n";
				$rs.='</area>';
			}
		}
	    return $rs;
	}
	
	protected function exRenovation($data_item){
		
		if(isset($this->form_data_shared['renovation']) && isset($data_item['renovation'])){
			$mf=$this->form_data_shared['renovation'];
			$di=$data_item['renovation'];
			if($mf['type']=='select_box' && intval($di)!=0 && isset($mf['select_data'][$di])){
				return '<renovation>'.self::symbolsClear($mf['select_data'][$di]).'</renovation>';
			}elseif($mf['type']!='select_box' &&  $di!=''){
				return '<renovation>'.self::symbolsClear($di).'</renovation>';
			}
			//return '<renovation>'.self::symbolsClear($data_item['renovation']).'</renovation>';
		}
		
	}
	
	protected function exImages($data_item, $hasUploadify, $uploadsField){
		$imgs=array();
		
		if($hasUploadify){
			$imgids=array();
			
			$DBC=DBC::getInstance();
			$query='SELECT image_id FROM '.DB_PREFIX.'_data_image WHERE id='.$data_item['id'];
			$stmt=$DBC->query($query, array($data_item['id']));
			if($stmt){
				while($ar=$DBC->fetch($stmt)){
					$imgids[]=$ar['image_id'];
				}
			}
			
			if(count($imgids)>0){
				$query='SELECT normal, preview FROM '.DB_PREFIX.'_image WHERE image_id IN ('.implode(',',$imgids).')';
				$stmt=$DBC->query($query);
				if($stmt){
					while($ar=$DBC->fetch($stmt)){
						$imgs[]=$ar;
					}
				}
			}
		}elseif($uploadsField!==false && isset($data_item[$uploadsField]) && $data_item[$uploadsField]!=''){
			$imgs=unserialize($data_item[$uploadsField]);
		}
		
		$rs='';
		if(count($imgs)>0){
			
			if(1==(int)$this->getConfigValue('apps.yandexrealty.nowatermark_export') && 1==(int)$this->getConfigValue('save_without_watermark')){
				$image_dest=$this->getServerFullUrl().'/img/data/nowatermark/';
			}else{
				$image_dest=$this->getServerFullUrl().'/img/data/';
			}
			
			foreach($imgs as $v){
				if($this->export_mode=='ETOWN'){
					$rs.='<imagefile>'."\n";
					$rs.='<image>'.$image_dest.$v['preview'].'</image>'."\n";
					$rs.='<image>'.$image_dest.$v['normal'].'</image>'."\n";
					$rs.='</imagefile>'."\n";
				}else{
					$rs.='<image>'.$image_dest.$v['normal'].'</image>'."\n";
				}
			}
		}
		return $rs;
	}
	
	protected function exDescription($data_item){
		return '<description>'.htmlspecialchars(strip_tags($data_item['text']), ENT_QUOTES, SITE_ENCODING).'</description>';
	}
	
	protected function exWithChildren($data_item){
		if(isset($this->form_data_shared['with_children']) && isset($data_item['with_children'])){
			if((int)$data_item['with_children']==1){
				return '<with-children>1</with-children>';
			}else{
				return '<with-children>0</with-children>';
			}
		}
	}
	
	protected function exWithPets($data_item){
		if(isset($this->form_data_shared['with_pets']) && isset($data_item['with_pets'])){
			if((int)$data_item['with_pets']==1){
				return '<with-pets>1</with-pets>';
			}else{
				return '<with-pets>0</with-pets>';
			}
		}
	}
	
	protected function exAgentFee($data_item){
		if(isset($this->form_data_shared['agent_fee']) && isset($data_item['agent_fee']) && (int)$data_item['agent_fee']!=0){
			return '<agent-fee>'.(int)$data_item['agent_fee'].'</agent-fee>';
		}
	}
	
	protected function exDealStatus($data_item){
		if(isset($this->form_data_shared['deal_status']) && isset($data_item['deal_status']) && $data_item['deal_status'] != ''){
			if($form_data_shared['deal_status']['type']=='safe_string'){
				return '<deal-status>'.trim($data_item['deal_status']).'</deal-status>'."\n";
			}elseif($form_data_shared['deal_status']['type']=='select_box' && $data_item['deal_status']!=0 && isset($form_data_shared['deal_status']['select_data'][$data_item['deal_status']])){
				return '<deal-status>'.$form_data_shared['deal_status']['select_data'][$data_item['deal_status']].'</deal-status>'."\n";
			}
			//return '<deal-status>'.trim($data_item['deal_status']).'</deal-status>';
		}else{
			//TODO: Make this error more softly
			//$this->errors[]=$data_item['id'].' DECLINED: Deal status unknown';
		}
	}
	
	protected function exRentPflege($data_item){
		if(isset($this->form_data_shared['rent_pledge']) && isset($data_item['rent_pledge'])){
			if((int)$data_item['rent_pledge']==1){
				return '<rent-pledge>1</rent-pledge>';
			}else{
				return '<rent-pledge>0</rent-pledge>';
			}
		}
	}
		
	protected function exPrepayment($data_item){
		if(isset($this->form_data_shared['prepayment']) && isset($data_item['prepayment']) && (int)$data_item['prepayment']!=0){
			return '<prepayment>'.(int)$data_item['prepayment'].'</prepayment>';
		}
	}
	
	protected function exMortgage($data_item){
		if(isset($this->form_data_shared['mortgage']) && isset($data_item['mortgage'])){
			if((int)$data_item['mortgage']==1){
				return '<mortgage>1</mortgage>';
			}else{
				return '<mortgage>0</mortgage>';
			}
		}
	}
	
	protected function exHaggle($data_item){
		if(isset($this->form_data_shared['haggle']) && isset($data_item['haggle'])){
			if((int)$data_item['haggle']==1){
				return '<haggle>1</haggle>';
			}else{
				return '<haggle>0</haggle>';
			}
		}
	}
	
	protected function exNotForAgents($data_item){
		if(isset($this->form_data_shared['not_for_agents']) && isset($data_item['not_for_agents'])){
			if((int)$data_item['not_for_agents']==1){
				return '<not-for-agents>1</not-for-agents>';
			}else{
				return '<not-for-agents>0</not-for-agents>';
			}
		}
	}
	
	protected function exPrice($data_item, $operational_type){
		$rs='<price>'."\n";
		$rs.='<value>'.self::symbolsClear($data_item['price']).'</value>'."\n";
		if(isset($this->form_data_shared['currency_id']) && isset($data_item['currency']) && $data_item['currency']!=''){
			$currency=self::currencyCheck($data_item['currency']);
		}else{
			$currency=$this->currency;
		}
		$rs.='<currency>'.$currency.'</currency>'."\n";
		
		if($operational_type=='rent' && isset($data_item['period']) && $data_item['period']!=''){
			$rs.='<period>'.self::symbolsClear($data_item['period']).'</period>'."\n";
		}
		if(isset($this->form_data_shared['unit']) && isset($data_item['unit']) && $data_item['unit']!=''){
			$rs.='<unit>'.self::symbolsClear($data_item['unit']).'</unit>'."\n";
		}
		$rs.='</price>';
		return $rs;
	}
	
	protected function exSalesAgent($data_item){
		
		if($this->contacts_export_mode==1){
			$rs='<sales-agent>'."\n";
			$uid=intval($data_item['user_id']);
			if(!isset($this->users_cache[$uid])){
				$UM=new Users_Manager();
				$this->users_cache[$uid]=$UM->getUserProfileData($uid);
			}
			$user=$this->users_cache[$uid];
			$gid=intval($user['group_id']);
				
			$contact_export_variant=0;
				
			if(count($this->contacts_mode)==1 && isset($this->contacts_mode['*'])){
				$contact_export_variant=$this->contacts_mode['*'];
			}elseif(isset($this->contacts_mode[$gid])){
				$contact_export_variant=$this->contacts_mode[$gid];
			}elseif(isset($this->contacts_mode['*'])){
				$contact_export_variant=$this->contacts_mode['*'];
			}
				
			if(count($this->group_assoc)==1 && isset($this->group_assoc['*'])){
				$exporter_type=$this->group_assoc['*'];
			}elseif(isset($this->group_assoc[$gid])){
				$exporter_type=$this->group_assoc[$gid];
			}elseif(isset($this->group_assoc['*'])){
				$exporter_type=$this->group_assoc['*'];
			}
			
			
			
			
			if($exporter_type=='a'){
				$rs.='<category>agency</category>'."\n";
			}elseif($exporter_type=='d'){
				$rs.='<category>developer</category>'."\n";
			}else{
				$rs.='<category>owner</category>'."\n";
			}
				
			//$rs.='<gid>'.$gid.'</gid>'."\n";
				
			if($contact_export_variant==1){
				$rs.='<phone>'.self::symbolsClear($data_item['phone']).'</phone>'."\n";
				$rs.='<email>'.self::symbolsClear($data_item['email']).'</email>'."\n";
				$rs.='<name>'.self::symbolsClear($data_item['fio']).'</name>'."\n";
			}elseif($contact_export_variant==2){
				$rs.='<phone>'.self::symbolsClear($user['phone']).'</phone>'."\n";
				$rs.='<email>'.self::symbolsClear($user['email']).'</email>'."\n";
				$rs.='<name>'.self::symbolsClear($user['fio']).'</name>'."\n";
			}elseif($contact_export_variant==3){
				$rs.='<phone>'.(''!==self::symbolsClear($data_item['phone']) ? self::symbolsClear($data_item['phone']) : self::symbolsClear($user['phone'])).'</phone>'."\n";
				$rs.='<email>'.(''!==self::symbolsClear($data_item['email']) ? self::symbolsClear($data_item['email']) : self::symbolsClear($user['email'])).'</email>'."\n";
				$rs.='<name>'.(''!==self::symbolsClear($data_item['fio']) ? self::symbolsClear($data_item['fio']) : self::symbolsClear($user['fio'])).'</name>'."\n";
			}elseif($contact_export_variant==4){
				$rs.='<phone>'.(''!==self::symbolsClear($user['phone']) ? self::symbolsClear($user['phone']) : self::symbolsClear($data_item['phone'])).'</phone>'."\n";
				$rs.='<email>'.(''!==self::symbolsClear($user['email']) ? self::symbolsClear($user['email']) : self::symbolsClear($data_item['email'])).'</email>'."\n";
				$rs.='<name>'.(''!==self::symbolsClear($user['fio']) ? self::symbolsClear($user['fio']) : self::symbolsClear($data_item['fio'])).'</name>'."\n";
			}
			$rs.='</sales-agent>'."\n";
		}else{
			$user_id=(int)$data_item['user_id'];
			$rs='<sales-agent>'."\n";
			if($data_item['fio']!='' && $user_id==$this->getUnregisteredUserId()){
				$rs.='<category>owner</category>'."\n";
				$rs.='<phone>'.self::symbolsClear($data_item['phone']).'</phone>'."\n";
				$rs.='<email>'.self::symbolsClear($data_item['email']).'</email>'."\n";
				$rs.='<name>'.self::symbolsClear($data_item['fio']).'</name>'."\n";
			}else{
				require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/users/users_manager.php';
				$UM=new Users_Manager();
				$user=$UM->getUserProfileData($user_id);
			
				if($this->getConfigValue('apps.company.enable')==1){
					if($user['company_id']!=0){
						require_once SITEBILL_DOCUMENT_ROOT.'/apps/company/admin/admin.php';
						$CA=new company_admin();
						$company=$CA->load_by_id($user['company_id']);
							
						$rs.='<phone>'.self::symbolsClear($db->row['agency_agentphone']).'</phone>'."\n";
						$rs.='<organization>'.self::symbolsClear($company['name']['value']).'</organization>'."\n";
						$rs.='<category>agency</category>'."\n";
						$rs.='<url>'.self::symbolsClear($company['site']['value']).'</url>'."\n";
						$rs.='<email>'.self::symbolsClear($company['email']['value']).'</email>'."\n";
						$rs.='<name>'.self::symbolsClear($company['name']['value']).'</name>'."\n";
						$rs.='<phone>'.self::symbolsClear($company['phone1']['value']).'</phone>'."\n";
					}else{
						$rs.='<category>owner</category>'."\n";
						$rs.='<phone>'.self::symbolsClear($user['phone']).'</phone>'."\n";
						$rs.='<email>'.self::symbolsClear($user['email']).'</email>'."\n";
						$rs.='<name>'.self::symbolsClear($user['fio']).'</name>'."\n";
					}
				}else{
					$rs.='<category>owner</category>'."\n";
					$rs.='<phone>'.self::symbolsClear($user['phone']).'</phone>'."\n";
					$rs.='<email>'.self::symbolsClear($user['email']).'</email>'."\n";
					$rs.='<name>'.self::symbolsClear($user['fio']).'</name>'."\n";
				}
			
			
			
			}
			
			if(isset($this->form_data_shared['partner']) && isset($data_item['partner']) && $data_item['partner']!=''){
				$rs.='<partner>'.self::symbolsClear($data_item['partner']).'</partner>'."\n";
			}
			$rs.='</sales-agent>';
		}
		
		return $rs;
	}
	
	protected function exLocation($data_item){
		$rs='<location>'."\n";
		
		$country=trim($this->getConfigValue('apps.yandexrealty.country_global'));
		if($country==''){
			if(''!=trim($this->getConfigValue('apps.yandexrealty.country_from'))){
				$country_from=trim($this->getConfigValue('apps.yandexrealty.country_from'));
			}else{
				$country_from='';
			}
			
			if($country_from!='' && isset($data_item[$country_from])){
				$country=$data_item[$country_from];
			}else{
				$country=$data_item['country'];
			}
		}
		
		if($country==''){
			$this->errors[]=$data_item['id'].' DECLINED: Country unknown';
		}else{
			$rs.='<country>'.self::symbolsClear($country).'</country>'."\n";
		}
		
		
		
		
		$region=trim($this->getConfigValue('apps.yandexrealty.region_global'));
		if($region==''){
			if(''!=trim($this->getConfigValue('apps.yandexrealty.region_from'))){
				$region_from=trim($this->getConfigValue('apps.yandexrealty.region_from'));
			}else{
				$region_from='';
			}
				
			if($region_from!='' && isset($data_item[$region_from])){
				$region=$data_item[$region_from];
			}else{
				$region=$data_item['region'];
			}
		}
		
		if($region!=''){
			$rs.='<region>'.self::symbolsClear($region).'</region>'."\n";
		}
		
		
		$city=trim($this->getConfigValue('apps.yandexrealty.city_global'));
		if($city==''){
			if(''!=trim($this->getConfigValue('apps.yandexrealty.city_from'))){
				$city_from=trim($this->getConfigValue('apps.yandexrealty.city_from'));
			}else{
				$city_from='';
			}
			
			if($city_from!='' && isset($data_item[$city_from])){
				$city=$data_item[$city_from];
			}else{
				$city=$data_item['city'];
			}
		}
		
		if($city!=''){
			$rs.='<locality-name>'.self::symbolsClear($city).'</locality-name>'."\n";
		}
		
	
		if($data_item['district']!=''){
			$rs.='<sub-locality-name>'.self::symbolsClear($data_item['district']).'</sub-locality-name>'."\n";
		}
		
		if(''!=trim($this->getConfigValue('apps.yandexrealty.street_from'))){
			$street_from=trim($this->getConfigValue('apps.yandexrealty.street_from'));
		}else{
			$street_from='';
		}
		
		if($street_from!='' && isset($data_item[$street_from])){
			$street=$data_item[$street_from];
		}else{
			$street=$data_item['street'];
		}
		
		$rs.='<address>';
		$street = str_replace('шос.', 'шоссе', $street);
		$street = str_replace('ул.', 'улица', $street);
		$street = str_replace('пр.', 'проспект', $street);
		$street = str_replace('наб.', 'набережная', $street);
		$street = str_replace('бул.', 'бульвар', $street);
		$street = str_replace('пер.', 'переулок', $street);
		$street = str_replace('свх.', 'совхоз', $street);
		$street = str_replace('прд.', 'проезд', $street);
		$street = str_replace('дер.', 'деревня', $street);
		$street = str_replace('пос.', 'поселок', $street);
		$street = str_replace('ст.', 'станция', $street);
		$street = str_replace('сад-во', 'садоводство', $street);
		$street = str_replace('пгт.', 'поселок', $street);
		$street = str_replace('алл.', 'аллея', $street);
		$street = str_replace('пл.', 'площадь', $street);
		$street = str_replace('мкр.', 'микрорайон', $street);
			
		$rs.= $street;
		if($data_item['number']!=''){
			$rs.=', '.self::symbolsClear($data_item['number']);
		}
		$rs.='</address>'."\n";
		if($data_item['metro']!=''){
			$rs.='<metro>'."\n";
				$rs.='<name>'.self::symbolsClear($data_item['metro']).'</name>'."\n";
				if(isset($data_item['time_on_transport']) && (int)$data_item['time_on_transport']!=0){
					$rs.='<time-on-transport>'.(int)$data_item['time_on_transport'].'</time-on-transport>'."\n";
				}
				if(isset($data_item['time_on_foot']) && (int)$data_item['time_on_foot']!=0){
					$rs.='<time-on-foot>'.(int)$data_item['time_on_foot'].'</time-on-foot>'."\n";
				}
			$rs.='</metro>'."\n";
		}
		
		if(isset($this->form_data_shared['railway_station']) && isset($data_item['railway_station']) && $data_item['railway_station']!=''){
			$rs.='<railway-station>'.self::symbolsClear($data_item['railway_station']).'</railway-station>'."\n";
		}
		
		if(isset($this->form_data_shared['direction']) && isset($data_item['direction']) && $data_item['direction']!=''){
			$rs.='<direction>'.self::symbolsClear($data_item['direction']).'</direction>'."\n";
		}
		
		if(isset($this->form_data_shared['distance']) && isset($data_item['distance']) && (int)$data_item['distance']!=''){
			$rs.='<distance>'.$data_item['distance'].'</distance>'."\n";
		}
		
		if(isset($this->form_data_shared['geo']) && isset($data_item['geo_lat']) && $data_item['geo_lat']!='' && isset($data_item['geo_lng']) && $data_item['geo_lng']!=''){
			$rs.='<latitude>'.$data_item['geo_lat'].'</latitude>'."\n";
			$rs.='<longitude>'.$data_item['geo_lng'].'</longitude>'."\n";
		}
		
		$rs.='</location>';
		return $rs;
	}
	
	
	protected function exManuallyAdded($data_item){
		if(isset($this->form_data_shared['manually_added']) && isset($data_item['manually_added'])){
			if((int)$data_item['manually_added']==1){
				return '<manually-added>1</manually-added>'."\n";
			}else{
				return '<manually-added>0</manually-added>'."\n";
			}
		}
		return '';
	}
	
	protected function exPayedAdv($data_item){
		if(isset($this->form_data_shared['payed_adv']) && isset($data_item['payed_adv'])){
			if((int)$data_item['payed_adv']==1){
				return '<payed-adv>1</payed-adv>';
			}else{
				return '<payed-adv>0</payed-adv>';
			}
		}
		return '';
	}
	
	protected function exExpireDate($data_item){
		if(isset($this->form_data_shared['expire_date']) && isset($data_item['expire_date']) && $data_item['expire_date']!='' &&  $data_item['expire_date']!='0000-00-00 00:00:00'){
			return '<expire-date>'.$this->formdate(strtotime($data_item['expire_date'])).'</expire-date>';
		}
	}
	
	protected function exLastUpdateDate($data_item){
		$date_timestamp=strtotime($data_item['date_added']);
		if((time()-$date_timestamp)>($this->critical_term*24*3600)){
			return '<last-update-date>'.$this->formdate(time()-(rand($this->min_normal_term, $this->max_normal_term)*24*3600)).'</last-update-date>';
		}else{
			return '<last-update-date>'.$this->formdate($date_timestamp).'</last-update-date>';
		}
	}
	
	protected function exCreationDate($data_item){
		$date_timestamp=strtotime($data_item['date_added']);
		return '<creation-date>'.$this->formdate($date_timestamp).'</creation-date>';
	}
	
	protected function exURL($data_item){
		$data_topic=(int)$data_item['topic_id'];
		$href=$this->getRealtyHREF($data_item['id'], true, array('topic_id'=>$data_item['topic_id'], 'alias'=>$data_item['translit_alias']));
		$rs='<url>'.$href.'</url>';
		return $rs;
		/*$parent_category_url='';
		$href='';
		if(1==$this->getConfigValue('apps.seo.level_enable')){
			 
			if($this->category_structure['catalog'][$data_topic]['url']!=''){
				$parent_category_url=trim($this->category_structure['catalog'][$data_topic]['url'], '/').'/';
			}
		}
		if(1==$this->getConfigValue('apps.seo.data_alias_enable') && $data_item['translit_alias']!=''){
			$href='/'.$parent_category_url.urlencode($data_item['translit_alias']);
		}elseif(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
			$href='/'.$parent_category_url.'realty'.$data_item['id'].'.html';
		}else{
			$href='/'.$parent_category_url.'realty'.$data_item['id'];
		}

		$rs='<url>'.$this->getServerFullUrl().self::symbolsClear($href).'</url>';
		return $rs;*/
	}
	
	protected function exCategory($data_item){
		$data_topic=(int)$data_item['topic_id'];
		$this_realty_supertype=intval($this->associations[$data_topic]['realty_type']);
		$rs='';
		if($this->export_mode=='ETOWN'){
			$rs.='<category>'.self::symbolsClear($this->catalogChains[$data_topic]).'</category>';
		}elseif($this_realty_supertype==self::$EXP_TY_RESIDENTIAL){
			if(!empty($this->associations) && isset($this->associations[$data_topic]) && $this->associations[$data_topic]['realty_category']!=0 && isset($this->realty_categories[$this->associations[$data_topic]['realty_category']])){
				$rs.='<category>'.$this->realty_categories[$this->associations[$data_topic]['realty_category']].'</category>'."\n";
			}else{
				$this->errors[]=$data_item['id'].' DECLINED: Residential category unknown';
			}
		}elseif($this_realty_supertype==self::$EXP_TY_COMMERCIAL){
			$rs.='<category>коммерческая</category>'."\n";
		}/*elseif(!empty($this->associations) && isset($this->associations[$data_topic]) && $this->associations[$data_topic]['realty_category']!=0){
			$rs.='<category>'.$this->realty_categories[$this->associations[$data_topic]['realty_category']].'</category>';
		}else{
			$rs.='<category>'.self::symbolsClear($data_item['topic']).'</category>';
		}*/
		return $rs;
	}
	
	protected function exCommercialType($data_item){
		$data_topic=(int)$data_item['topic_id'];
		$this_realty_supertype=intval($this->associations[$data_topic]['realty_type']);
		$rs='';
		if($this_realty_supertype==self::$EXP_TY_COMMERCIAL){
			if(!empty($this->associations) && isset($this->associations[$data_topic]) && $this->associations[$data_topic]['realty_category']!=0 && isset($this->commercial_names[$this->associations[$data_topic]['realty_category']])){
				$rs.='<commercial-type>'.$this->commercial_names[$this->associations[$data_topic]['realty_category']].'</commercial-type>'."\n";
			}else{
				$this->errors[]=$data_item['id'].' DECLINED: Commercial type unknown';
			}
		}
		return $rs;
	}
	
	protected function exType($data_item, &$operational_type){
		$data_topic=(int)$data_item['topic_id'];
		$rs='';
		
		
		
		$operational_type='sale';
		if(!empty($this->associations) && isset($this->associations[$data_topic]) && $this->associations[$data_topic]['operation_type']!=0){
			$rs.='<type>'.$this->op_types[$this->associations[$data_topic]['operation_type']].'</type>';
			if($this->associations[$data_topic]['operation_type']==2){
				$operational_type='rent';
			}
			
		}else{
			$st=explode(':', $this->getConfigValue('apps.yandexrealty.sell'));
			$rt=explode(':', $this->getConfigValue('apps.yandexrealty.rent'));
			$selltype_field=trim($st[0]);
			$selltype_value=trim($st[1]);
			$renttype_field=trim($rt[0]);
			$renttype_value=trim($rt[1]);
			
			if($selltype_field!='' && $selltype_value!='' && isset($data_item[$selltype_field]) && $data_item[$selltype_field]==$selltype_value){
				$rs.='<type>продажа</type>';
			}elseif($renttype_field!='' && $renttype_value!='' && isset($data_item[$renttype_field]) && $data_item[$renttype_field]==$renttype_value){
				$rs.='<type>аренда</type>';
				$operational_type='rent';
			}elseif(isset($data_item['optype']) && (int)$data_item['optype']==1){
				$rs.='<type>аренда</type>';
				$operational_type='rent';
			}else{
				$this->errors[]=$data_item['id'].' DECLINED: Operational type unknown';
				$rs.='<type>продажа</type>';
			}
		}
		return $rs;
	}
	
	protected function exPropertyType($data_item){
		$data_topic=(int)$data_item['topic_id'];
		$this_realty_supertype=intval($this->associations[$data_topic]['realty_type']);
		$rs='';
		if($this_realty_supertype==self::$EXP_TY_RESIDENTIAL){
			$rs.='<property-type>жилая</property-type>'."\n";
		}elseif($this_realty_supertype==self::$EXP_TY_COMMERCIAL){
			//$rs.='<category>коммерческая</category>'."\n";
		}elseif(isset($data_item['property_type']) && $data_item['property_type']!=''){
			$rs.='<property-type>'.self::symbolsClear($data_item['property_type']).'</property-type>'."\n";
		}else{
			$this->errors[]=$data_item['id'].' DECLINED: Supertype unknown';
		}
		/*if(!empty($this->associations) && isset($this->associations[$data_topic]) && $this->associations[$data_topic]['realty_type']!=0){
			$rs.='<property-type>'.$this->realty_types[$this->associations[$data_topic]['realty_type']].'</property-type>';
		}elseif(isset($data_item['property_type']) && $data_item['property_type']!=''){
			$rs.='<property-type>'.self::symbolsClear($data_item['property_type']).'</property-type>';
		}else{
			$rs.='<property-type>жилая</property-type>';
		}*/
		return $rs;
	}
	
	protected function exInternalId($data_item){
		return '<offer internal-id="'.(int)$data_item['id'].'">';
	}
	
	protected function setExportType(){
		$this->export_type=mb_strtolower($this->getRequestValue('type'), SITE_ENCODING);
	}
	
	protected function setExportMode(){
		$this->export_mode='YANDEX';
	}
	
	protected function remove_old_file () {
		if(1==$this->getConfigValue('apps.yandexrealty.tofile') && file_exists($this->export_file_storage.'/'.$this->export_file)){
			if ( (time() - filemtime( $this->export_file_storage.'/'.$this->export_file ) ) > $this->getConfigValue('apps.yandexrealty.filetime') ) {
				return unlink($this->export_file_storage.'/'.$this->export_file);
			}
		}
		return false;
	}
	
	protected function collectData(){
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		$form_data_shared = $data_model->get_kvartira_model(false, true);
		$form_data_shared=$form_data_shared['data'];
			
		$select=array();
		$leftjoin=array();
			
		$select[]='dt.*';
			
		if($this->getConfigValue('currency_enable')==1){
			$select[]='cur.name AS currency';
			$leftjoin[]='LEFT JOIN '.DB_PREFIX.'_currency cur ON cur.currency_id=dt.currency_id';
		}

		if($this->getConfigValue('apps.yandexrealty.complex_enable')==1){
			$select[]='complex.name AS `building-name`';
			$select[]='complex.tip_construct AS `building-type`';
			$select[]='complex.decoration AS `renovation`';
			$select[]='complex.deadline AS `built-year`';
			$leftjoin[]='LEFT JOIN '.DB_PREFIX.'_complex complex ON complex.complex_id=dt.complex_id';
			if($this->getConfigValue('apps.yandexrealty.complex_yandexrealty_export')==1){
				$where[]='complex.yandexrealty_export=1';
			}
			
		}
			
		if(isset($form_data_shared['topic_id'])){
			$select[]='tp.name AS topic';
			$leftjoin[]='LEFT JOIN '.DB_PREFIX.'_topic tp ON tp.id=dt.topic_id';
		}
			
		if(isset($form_data_shared['country_id'])){
			$select[]='cr.name AS country';
			$leftjoin[]='LEFT JOIN '.DB_PREFIX.'_country cr USING(country_id)';
		}
			
		if(isset($form_data_shared['region_id'])){
			$select[]='re.name AS region';
			$leftjoin[]='LEFT JOIN '.DB_PREFIX.'_region re USING(region_id)';
		}
			
		if(isset($form_data_shared['city_id'])){
			$select[]='ct.name AS city';
			$leftjoin[]='LEFT JOIN '.DB_PREFIX.'_city ct ON dt.city_id=ct.city_id';
		}
			
		if(isset($form_data_shared['district_id'])){
			$select[]='ds.name AS district';
			$leftjoin[]='LEFT JOIN '.DB_PREFIX.'_district ds ON dt.district_id=ds.id';
		}
			
		if(isset($form_data_shared['street_id'])){
			$select[]='st.name AS street';
			$leftjoin[]='LEFT JOIN '.DB_PREFIX.'_street st ON st.street_id=dt.street_id';
		}
			
		if(isset($form_data_shared['metro_id'])){
			$select[]='mt.name AS metro';
			$leftjoin[]='LEFT JOIN '.DB_PREFIX.'_metro mt ON dt.metro_id=mt.metro_id';
		}
		
		$basic_query='SELECT '.implode(', ', $select).' FROM '.DB_PREFIX.'_data dt '.(!empty($leftjoin) ? implode(' ', $leftjoin) : '');
			
		/*
		$basic_query='SELECT
				dt.*,
				tp.name AS topic,
				ct.name AS city,
				ds.name AS district,
				cr.name AS country,
				st.name AS street,
				mt.name AS metro
				'.($this->getConfigValue('currency_enable')==1 ? ', cur.name AS currency' : '').'
				FROM '.DB_PREFIX.'_data dt
				LEFT JOIN '.DB_PREFIX.'_topic tp ON tp.id=dt.topic_id
				LEFT JOIN '.DB_PREFIX.'_city ct ON dt.city_id=ct.city_id
				LEFT JOIN '.DB_PREFIX.'_district ds ON dt.district_id=ds.id
				LEFT JOIN '.DB_PREFIX.'_metro mt ON dt.metro_id=mt.metro_id
				LEFT JOIN '.DB_PREFIX.'_street st ON st.street_id=dt.street_id
				LEFT JOIN '.DB_PREFIX.'_country cr ON cr.country_id=dt.country_id
				'.($this->getConfigValue('currency_enable')==1 ? 'LEFT JOIN '.DB_PREFIX.'_currency cur ON cur.currency_id=dt.currency_id' : '').'';
		 
		 */
		//echo $this->export_type;
		 
		$data=array();
		 
		$tasks=array();
		if($this->export_type!=''){
			$DBC=DBC::getInstance();
			$query='SELECT * FROM '.DB_PREFIX.'_yandexrealty_task WHERE task_label=?';
			$stmt=$DBC->query($query, array($this->export_type));
			if($stmt){
				while($ar=$DBC->fetch($stmt)){
					$tasks[]=$ar;
				}
			}
		}
		//print_r($tasks);
		 
		if(!empty($tasks)){
		
			$unions=array();
		
			foreach($tasks as $task){
				parse_str($task['filter_params'], $filter_params);
				//print_r($filter_params);
				$where=array();
				$where[]='dt.active=1';
				$sorts=array();
				$limit=false;
				if(count($filter_params)>0){
					foreach ($filter_params as $filter_param_key=>$filter_param_value){
						$where[]='dt.'.$filter_param_key.'='.$filter_param_value;
					}
				}
				if(0!=(int)$task['max_limit_params']){
					$limit=(int)$task['max_limit_params'];
				}
				if(0!=(int)$task['use_date_filtering']){
					$max_days=(int)$this->getConfigValue('apps.yandexrealty.days_interval');
					if($max_days==0){
						$max_date = date('Y-m-d', 0 );
					}else{
						$max_date = date('Y-m-d', time()- $max_days*3600*24 );
					}
					$where[]='dt.date_added > '.$max_date;
				}
				if(''!=$task['order_params']){
					$order_params=array();
					preg_match_all('/([a-z0-9_]+):(asc|desc)/i', $task['order_params'], $order_params);
					if(isset($order_params[1])){
						foreach ($order_params[1] as $k=>$v){
							$sorts[]='dt.'.$v.' '.($order_params[2][$k]=='asc' ? 'ASC' : 'DESC');
						}
					}
		
				}
				$unions[]=array(
						'where'=>$where,
						'sorts'=>$sorts,
						'limit'=>$limit,
				);
			}
		
			foreach ($unions as $union){
				$queries[]=$basic_query.(!empty($union['where']) ? ' WHERE '.implode(' AND ', $union['where']) : '').(!empty($union['sorts']) ? ' ORDER BY '.implode(', ', $union['sorts']) : '').($union['limit'] ? ' LIMIT '.$union['limit'] : '');
			}
		
			$data=array();
		
			if(count($queries)>0){
				foreach ($queries as $query){
					$stmt=$DBC->query($query);
					if($stmt){
						while($ar=$DBC->fetch($stmt)){
							$data[$ar['id']]=$ar;
						}
					}
				}
			}
		}else{
			$DBC=DBC::getInstance();
		
			 
			//Максимальный возраст объявления 6-месяцев
			$max_days=(int)$this->getConfigValue('apps.yandexrealty.days_interval');
			if($max_days==0){
				$max_date = date('Y-m-d', 0 );
			}else{
				$max_date = date('Y-m-d', time()- $max_days*3600*24 );
			}
			 
			$where[]='dt.active=1';
			$where[]='dt.date_added > \''.$max_date.'\'';
			
			if(''!==trim($this->getConfigValue('apps.yandexrealty.filtering_checkbox_name'))){
				$where[]='dt.'.trim($this->getConfigValue('apps.yandexrealty.filtering_checkbox_name')).'=1';
			}
		
			$query=$basic_query.(!empty($where) ? ' WHERE '.implode(' AND ', $where) : '').' ORDER BY dt.date_added DESC';
			
			$stmt=$DBC->query($query);
			if($stmt){
				while($ar=$DBC->fetch($stmt)){
					$data[]=$ar;
				}
			}
		}
		
		return $data;
		
		/*$filter_params=array();
		$data=array();
		$max_days=(int)$this->getConfigValue('apps.yandexrealty.days_interval');
		if($max_days==0){
			$max_date = date('Y-m-d', 0 );
		}else{
			$max_date = date('Y-m-d', time()- $max_days*3600*24 );
		}
		
		$DBC=DBC::getInstance();
		
		$where=array();
		$where_data=array();
		
		$where[]='dt.active=1';
		
		$where[]='dt.date_added>?';
		$where_data[]=$max_date;
		
		if(isset($filter_params['topic_id'])){
			if(is_array($filter_params['topic_id'])){
				$topic_id=(int)$params['topic_id'];
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
				$Structure_Manager = new Structure_Manager();
				$category_structure = $Structure_Manager->loadCategoryStructure();
				$childs = $Structure_Manager->get_all_childs($topic_id, $category_structure);
				if ( count($childs) > 0 ) {
					array_push($childs, $topic_id);
					$where_array[] = DB_PREFIX.'_data.topic_id IN ('.implode(' , ', $childs).') ';
					$str_a=array();
					foreach($childs as $a){
						$str_a[]='?';
					}
					$where_array_prepared[]='('.DB_PREFIX.'_data.topic_id IN ('.implode(',', $str_a).'))';
					$where_value_prepared=array_merge($where_value_prepared, $childs);
				} else {
					$where_array[] = 're_data.topic_id='.$topic_id;
					$where_array_prepared[]='('.DB_PREFIX.'_data.topic_id=?)';
					$where_value_prepared[]=$topic_id;
				}
			}
		}
		 
		//$query='SELECT id FROM '.DB_PREFIX.'_data WHERE active=1 AND date_added>? ORDER BY date_added DESC';
		$query='SELECT
				dt.*,
				tp.name AS topic,
				ct.name AS city,
				rg.name AS region, 
				ds.name AS district,
				cr.name AS country,
				st.name AS street,
				mt.name AS metro
				'.($this->getConfigValue('currency_enable')==1 ? ', cur.name AS currency' : '').'
				FROM '.DB_PREFIX.'_data dt
				LEFT JOIN '.DB_PREFIX.'_topic tp ON tp.id=dt.topic_id 
				LEFT JOIN '.DB_PREFIX.'_region rg ON rg.region_id=dt.region_id
				LEFT JOIN '.DB_PREFIX.'_city ct ON dt.city_id=ct.city_id
				LEFT JOIN '.DB_PREFIX.'_district ds ON dt.district_id=ds.id
				LEFT JOIN '.DB_PREFIX.'_metro mt ON dt.metro_id=mt.metro_id
				LEFT JOIN '.DB_PREFIX.'_street st ON st.street_id=dt.street_id
				LEFT JOIN '.DB_PREFIX.'_country cr ON cr.country_id=dt.country_id
				'.($this->getConfigValue('currency_enable')==1 ? 'LEFT JOIN '.DB_PREFIX.'_currency cur ON cur.currency_id=dt.currency_id' : '').'
				WHERE dt.active=1 and dt.date_added>? ORDER BY dt.date_added DESC';
		$stmt=$DBC->query($query, array($max_date));
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$data[]=$ar;
			}
		}
		return $data;*/
	}
}