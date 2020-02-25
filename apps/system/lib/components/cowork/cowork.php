<?php
class Cowork extends Sitebill {
	public function getNavigation(){
		$points=array(
			array('title'=>'Главная', 'href'=>'/'),
			array('title'=>'Каталог', 'href'=>'#', 'childs'=>array())	
		);
		return $points;
	}
	
	public function main(){
        global $smarty;
        $do= $this->getRequestValue('do');
        switch($do){
            case 'list' : {
                /*Список всех Стажеров с Наставниками*/
                $smarty->assign('list', $this->getList());
                $ret=$smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/components/cowork/list.tpl');
                break;
            }
            case 'off' : {
                /*Отключение Наставника от Стажера*/
                $user_id=intval($this->getRequestValue('user_id'));
                $this->deleteCoworkerFromUser($user_id);
                header('location: '.SITEBILL_MAIN_URL.'/admin/?action=cowork&do=list');
                exit();
                $smarty->assign('list', $this->getList());
                $ret=$smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/components/cowork/list.tpl');
                break;
            }
            case 'off_full' : {
                /*Отключение Наставника от Стажера и отключение Наставника от всех объектов*/
                $user_id=intval($this->getRequestValue('user_id'));
                $DBC=DBC::getInstance();
                $query='SELECT `parent_user_id` FROM '.DB_PREFIX.'_user WHERE `user_id`=?';
                $stmt=$DBC->query($query, array($user_id));
                if($stmt){
                    while($ar=$DBC->fetch($stmt)){
                        $coworker_id=$ar['parent_user_id'];
                    }
                }
                $this->deleteCoworkerFromUser($user_id);
                $this->fireCoworker($coworker_id);
                header('location: '.SITEBILL_MAIN_URL.'/admin/?action=cowork&do=list');
                exit();
                break;
            }
            case 'add' : {
                /*Отключение пары Наставник-Стажер*/
                if(strtolower($_SERVER['REQUEST_METHOD'])=='post'){
                    $user_id=intval($this->getRequestValue('user_id'));
                    $parent_user_id=intval($this->getRequestValue('parent_user_id'));
                    $res=$this->setCoworkerToUser($user_id, $parent_user_id);
                    if($res){
                        $ret='Наставник присвоен.';
                    }else{
                        $ret='Ошибка.';
                    }
                    /*header('location: '.SITEBILL_MAIN_URL.'/admin/?action=cowork&do=list');
                    exit();*/
                }else{
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
                    $ATH = new Admin_Table_Helper();
                    $data = $ATH->load_model('data', false);   

                    $form_data['user_id'] = $data['data']['user_id'];
                    $form_data['user_id']['query'] = 'SELECT * FROM '.DB_PREFIX.'_user WHERE parent_user_id=0 ORDER BY `'.$form_data['user_id']['value_name'].'` ASC';
                    $form_data['parent_user_id'] = $data['data']['user_id'];
                    $form_data['parent_user_id']['name'] = 'parent_user_id';
                    if($this->getConfigValue('curator_mode_chainsallow')){
                        $form_data['parent_user_id']['query'] = 'SELECT * FROM '.DB_PREFIX.'_user ORDER BY `'.$form_data['user_id']['value_name'].'` ASC';
                    }else{
                        $form_data['parent_user_id']['query'] = 'SELECT * FROM '.DB_PREFIX.'_user WHERE parent_user_id=0 ORDER BY `'.$form_data['user_id']['value_name'].'` ASC';
                    }
                    



                    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');
                    $form_generator = new Form_Generator();
                    $el = $form_generator->compile_form_elements($form_data);
                    $smarty->assign('el', $el);
                    $ret=$smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/components/cowork/add.tpl');
                }
                
                break;
            }
            default : {
                $ret=$smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/components/cowork/main.tpl');
            }
        }
        return $this->getMenu().$ret;
        
	}
    
    public function getMenu(){
        return '<div class="row-fluid" style="margin-bottom: 30px;">
        <div class="span12" style="text-align: center">
            <a href="'.SITEBILL_MAIN_URL.'/admin/?action=cowork&do=list" class="btn">Список Стажер-Наставник</a>
            <a href="'.SITEBILL_MAIN_URL.'/admin/?action=cowork&do=add" class="btn">Добавить Наставника</a>
            <!--<a href="" class="btn">Отключить всех Наставников от Стажеров</a>
            <a href="" class="btn">Очистить таблицу сотрудничества</a>-->
        </div>
    </div>';
    }

        protected function getList(){
		$ret=array();
		$DBC=DBC::getInstance();
		$query='SELECT `user_id` FROM '.DB_PREFIX.'_user WHERE `parent_user_id`>0';
		$stmt=$DBC->query($query);
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$ret[]=$ar['user_id'];
			}
		}
        
        if(!empty($ret)){
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
            $data_model = new Data_Model();
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
            $ATH = new Admin_Table_Helper();
            $form_data = $ATH->load_model('user', false);
            $ret=$data_model->init_model_data_from_db_multi('user', 'user_id', $ret, $form_data['user'], true);
        }
       
		return $ret;
	}
    
    /*
     * Delete coworker from concrete user
     * Not from user objects
     * @param string $object table name
     * @param int $id object id
     * @param int $coworker_id coworker user id
     * @return boolean
     */
    public function setCoworkerToUser($user_id, $parent_user_id) {
        $DBC = DBC::getInstance();
        $query = 'UPDATE ' . DB_PREFIX . '_user SET `parent_user_id`=? WHERE `user_id`=?';
        $stmt = $DBC->query($query, array($parent_user_id, $user_id));
        if ($stmt) {
            return true;
        }
        return false;
    }
    
    /*
     * Delete coworker from concrete user
     * Not from user objects
     * @param string $object table name
     * @param int $id object id
     * @param int $coworker_id coworker user id
     * @return boolean
     */
    public function deleteCoworkerFromUser($user_id) {
        $DBC = DBC::getInstance();
        $query = 'UPDATE ' . DB_PREFIX . '_user SET `parent_user_id`=? WHERE `user_id`=?';
        $stmt = $DBC->query($query, array(0, $user_id));
        if ($stmt) {
            return true;
        }
        return false;
    }
	

	/*
     * Set coworker to concrete object
     * @param string $object table name
     * @param int $id object id
     * @param int $coworker_id coworker user id
     * @return boolean
     */
    public function setCoworkerToObject($object, $id, $coworker_id) {
        $DBC = DBC::getInstance();
        $query = 'INSERT INTO ' . DB_PREFIX . '_cowork (`id`, `coworker_id`, `object_type`) VALUES (?,?,?) ON DUPLICATE KEY UPDATE `coworker_id`=?';
        $stmt = $DBC->query($query, array($id, $coworker_id, $object, $coworker_id));
        echo $DBC->getLastError();
        if ($stmt) {
            return true;
        }
        return false;
    }

    /*
     * Delete concrete coworker from concrete object
     * @param string $object table name
     * @param int $id object id
     * @param int $coworker_id coworker user id
     * @return boolean
     */
    public function deleteCoworkerFromObject($object, $id, $coworker_id) {
        $DBC = DBC::getInstance();
        $query = 'DELETE FROM ' . DB_PREFIX . '_cowork WHERE `id`=? AND `coworker_id`=? AND `object_type`=?';
        $stmt = $DBC->query($query, array($id, $coworker_id, $object));
        if ($stmt) {
            return true;
        }
        return false;
    }

    /*
     * Delete coworker from all objects
     * @param int $coworker_id coworker user id
     * @return int count of cleared objects
     */
    public function fireCoworker($coworker_id) {
        $DBC = DBC::getInstance();
        $rows=0;
        $query = 'DELETE FROM ' . DB_PREFIX . '_cowork WHERE `coworker_id`=?';
        $stmt = $DBC->query($query, array($coworker_id), $rows);
        if ($stmt) {
            return $rows;
        }
        return $rows;
    }

    /*
     * Delete all coworker from concrete object
     * @param string $object table name
     * @param int $id object id
     * @return int count of fired coworkers
     */
    public function clearObjectCoworkers($object, $id) {
        $DBC = DBC::getInstance();
        $rows=0;
        $query = 'DELETE FROM ' . DB_PREFIX . '_cowork WHERE `id`=? AND `object_type`=?';
        $stmt = $DBC->query($query, array($id, $object), $rows);
        if ($stmt) {
            return $rows;
        }
        return $rows;
    }

}