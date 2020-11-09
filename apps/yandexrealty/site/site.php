<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * yandexrealty export generator frontend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class yandexrealty_site extends yandexrealty_admin {

    protected $export_mode = 'YANDEX';
    protected $associations = array();
    protected $catalogChains = array();
    protected $category_structure = array();
    protected $form_data_shared = array();
    protected $errors = array();
    protected $contracts = array();
    protected $one_item_query;


    function ch($model_item, $cond, $value){



        $c = array();
        $vals = array();


        switch($model_item['type']){
            case 'date' :
            case 'dtdate' :
            case 'dtdatetime' : {
                $workvalue = $value[0];

                $isvalidvalue = false;
                $offset = 0;
                $isoffesetdecremental = false;

                if($workvalue == 'NOW'){
                    $isvalidvalue = true;
                }elseif(preg_match('/^NOW\+(\d+)$/', $workvalue, $matches)){
                    $isvalidvalue = true;
                    $offset = $matches[1];
                }elseif(preg_match('/^NOW\-(\d+)$/', $workvalue, $matches)){
                    $isvalidvalue = true;
                    $offset = $matches[1];
                    $isoffesetdecremental = true;
                }


                if($isvalidvalue){
                    if($offset > 0){
                        if($isoffesetdecremental){
                            $workvalue = time() - $offset*24*3600;
                        }else{
                            $workvalue = time() + $offset*24*3600;
                        }
                    }else{
                        $workvalue = time();
                    }



                    if($model_item['type'] != 'date'){
                        $workvalue = date('Y-m-d H:i:s', $workvalue);
                    }

                    switch($cond){

                        case '>' : {
                            $c[] = DB_PREFIX.'_data.`'.$model_item['name'].'` > ?';
                            $vals[] = $workvalue;
                            break;
                        }
                        case '>=' : {
                            $c[] = DB_PREFIX.'_data.`'.$model_item['name'].'` >= ?';
                            $vals[] = $workvalue;
                            break;
                        }
                        case '<' : {
                            $c[] = DB_PREFIX.'_data.`'.$model_item['name'].'` < ?';
                            $vals[] = $workvalue;
                            break;
                        }
                        case '<=' : {
                            $c[] = DB_PREFIX.'_data.`'.$model_item['name'].'` <= ?';
                            $vals[] = $workvalue;
                            break;
                        }
                    }


                }

                break;
            }
            case 'textarea' :
            case 'texarea_editor' : {
                switch($cond){
                    case 'SETTED' : {
                        $c[] = DB_PREFIX.'_data.`'.$model_item['name'].'` <> ?';
                        $vals = array_merge($vals, '');
                        break;
                    }
                    case '!SETTED' : {
                        $c[] = DB_PREFIX.'_data.`'.$model_item['name'].'` = ?';
                        $vals = array_merge($vals, '');
                        break;
                    }
                }
                break;
            }
            case 'uploads' :
            case 'docuploads' : {
                switch($cond){
                    case 'SETTED' : {
                        $c[] = DB_PREFIX.'_data.`'.$model_item['name'].'` <> ?';
                        $vals = array_merge($vals, '');
                        break;
                    }
                    case '!SETTED' : {
                        $c[] = DB_PREFIX.'_data.`'.$model_item['name'].'` = ?';
                        $vals = array_merge($vals, '');
                        break;
                    }
                }
                break;
            }
            case 'geodata' : {
                switch($cond){
                    case 'SETTED' : {
                        $c[] = DB_PREFIX.'_data.`'.$model_item['name'].'_lat` IS NOT NULL AND '.DB_PREFIX.'_data.`'.$model_item['name'].'_lng` IS NOT NULL';
                        break;
                    }
                    case '!SETTED' : {
                        $c[] = DB_PREFIX.'_data.`'.$model_item['name'].'_lat` IS NULL AND '.DB_PREFIX.'_data.`'.$model_item['name'].'_lng` IS NULL';
                        break;
                    }
                }
                break;
            }
            case 'select_by_query_multi' : {

                break;
            }
            case 'price' :
            case 'mobilephone' :
            case 'safe_string' :
            case 'primary_key' :
            case 'select_box' :
            case 'select_by_query' : {
                switch($cond){
                    case '=' : {
                        $c[] = DB_PREFIX.'_data.`'.$model_item['name'].'` IN ('.implode(',', array_fill(0, count($value), '?')).')';
                        $vals = array_merge($vals, $value);
                        break;
                    }
                    case '!=' : {
                        $c[] = DB_PREFIX.'_data.`'.$model_item['name'].'` NOT IN ('.implode(',', array_fill(0, count($value)), '?').')';
                        $vals = array_merge($vals, $value);
                        break;
                    }
                    case '>' : {
                        $c[] = DB_PREFIX.'_data.`'.$model_item['name'].'` > ?';
                        $vals[] = $value[0];
                        break;
                    }
                    case '>=' : {
                        $c[] = DB_PREFIX.'_data.`'.$model_item['name'].'` >= ?';
                        $vals[] = $value[0];
                        break;
                    }
                    case '<' : {
                        $c[] = DB_PREFIX.'_data.`'.$model_item['name'].'` < ?';
                        $vals[] = $value[0];
                        break;
                    }
                    case '<=' : {
                        $c[] = DB_PREFIX.'_data.`'.$model_item['name'].'` <= ?';
                        $vals[] = $value[0];
                        break;
                    }
                }
                break;
            }
            case 'select_box_structure' : {
                $topics = $value;
                $list = array();
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
                $Structure_Manager = new Structure_Manager();
                $category_structure = $Structure_Manager->loadCategoryStructure();
                foreach ($topics as $topic_id) {
                    if (intval($topic_id) > 0 && isset($category_structure['catalog'][$topic_id])) {
                        $childs = $Structure_Manager->get_all_childs($topic_id, $category_structure);
                        if (!empty($childs)) {
                            $list = array_merge($list, $childs);
                        }
                        $list[] = intval($topic_id);
                    }
                }
                $list = array_unique($list, SORT_NUMERIC);

                switch($cond){
                    case '=' : {
                        $c[] = DB_PREFIX.'_data.`'.$model_item['name'].'` IN ('.implode(',', array_fill(0, count($list), '?')).')';
                        $vals = array_merge($vals, $list);
                        break;
                    }
                    case '!=' : {
                        $c[] = DB_PREFIX.'_data.`'.$model_item['name'].'` NOT IN ('.implode(',', array_fill(0, count($list)), '?').')';
                        $vals = array_merge($vals, $list);
                        break;
                    }
                }
                break;
            }
            case 'checkbox' : {
                switch($cond){
                    case '=' : {
                        $c[] = DB_PREFIX.'_data.`'.$model_item['name'].'` = ?';
                        $vals[] = $value[0];
                        break;
                    }
                    case '!=' : {
                        $c[] = DB_PREFIX.'_data.`'.$model_item['name'].'` <> ?';
                        $vals[] = $value[0];
                        break;
                    }
                }


                break;
            }
        }

        return array($c, $vals);
    }

    function frontend() {
        $REQUESTURIPATH = Sitebill::getClearRequestURI();
        $alias = trim($this->getConfigValue('apps.yandexrealty.alias'));
        if ($alias == '' && 1 === intval($this->getConfigValue('apps.yandexrealty.disable_standart_entrypoint'))) {
            return false;
        } elseif ($alias == '') {
            $alias = 'yandexrealty';
        }

        if ($REQUESTURIPATH == $alias) {
            header("Content-Type: text/xml");
            //$this->collected_ids=array(8108, 346);
            echo $this->run_export();
            exit();
        }

        if(!empty($task = $this->getTaskByAlias($REQUESTURIPATH))){

            if($task['active'] == 0){
                return false;
            }

            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
            $data_model = new Data_Model();
            $form_data_shared = $data_model->get_kvartira_model(false, true);
            $form_data_shared = $form_data_shared['data'];
            $this->form_data_shared = $form_data_shared;

            $filter_query = '';
            $vals = array();

            $filter = json_decode($task['filter']);

            if(!empty($filter)){
                $or_conditions = array();
                foreach($filter as $k => $filter_or){

                    $and_conditions[$k] = array();

                    foreach($filter_or as $kk => $filter_and){
                        //print_r($filter_and);
                        $field = $filter_and[0];
                        $cond = $filter_and[1];
                        $value = explode(',', $filter_and[2]);

                        if(isset($this->form_data_shared[$field])){

                            $p = $this->ch($this->form_data_shared[$field], $cond, $value);
                            if(is_array($p[0]) && !empty($p[0])){
                                $and_conditions[$k] = array_merge($and_conditions[$k], $p[0]);
                            }
                            if(is_array($p[1]) && !empty($p[1])){
                                $vals = array_merge($vals, $p[1]);
                            }

                        }

                    }
                    if(!empty($and_conditions[$k])){
                        $or_conditions[] = '('.implode(') AND (', $and_conditions[$k]).')';
                    }
                }

                if(!empty($or_conditions)){
                    $filter_query = '('.implode(') OR (', $or_conditions).')';
                }

            }

            if(1 == intval($task['ignoreactivity'])){
                $this->setActivityFiltering(false);
            }else{
                $this->setActivityFiltering(true);
            }

            $limit = '';
            if(0 < intval($task['limit'])){
               $limit = ' LIMIT '.intval($task['limit']);
            }

            $order = '';
            $orderbyfield = trim($task['orderby']);
            $orderdirect = trim($task['orderdirect']);
            if($orderdirect != 'desc'){
                $orderdirect = 'asc';
            }

            if('' != $orderbyfield && isset($this->form_data_shared[$orderbyfield])){
               $order = ' ORDER BY '.$orderbyfield.' '.$orderdirect;
            }


            $query = 'SELECT id FROM '.DB_PREFIX.'_data'.('' != $filter_query ? ' WHERE '.$filter_query : '').$order.$limit;

            //echo $query;
            //print_r($vals);

            $ids = array();

            $DBC = DBC::getInstance();
            $stmt = $DBC->query($query, $vals);
            if($stmt){
                while($ar = $DBC->fetch($stmt)){
                    $ids[] = $ar['id'];
                }

            }

            $this->setExportedIds($ids);

            /*
            if($task['xmltype'] == 1){
                $this->changeExportMode('EST.UA');
            }elseif($task['xmltype'] == 2){
                $this->external_export_mode = 'MEGET.UA';
            }*/

            header("Content-Type: text/xml");
            echo $this->run_export();

            exit();

        }




        /* if($REQUESTURIPATH==$alias){
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
          } */
        return false;
    }

    /* protected function getExportSettings(){
      return array();
      } */

    /* protected function collectOutData(){
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
      } */

    /* protected function getXML($data, $settings){
      return '';
      } */

    public function run_export() {


        $excode = md5(self::getClearRequestURI());
        $cachefile = $this->export_file_storage . '/' . $excode.'.' . $this->export_file;

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $this->form_data_shared = $data_model->get_kvartira_model(false, true);
        $this->form_data_shared = $this->form_data_shared['data'];

        $this->setExportMode();

        $this->setExportType();

        //$this->export_mode='EST.UA';
        //$this->export_mode='MEGET.UA';

        if (isset($_GET['user_id'])) {

            $user_id = -1;
            if (1 == $this->getConfigValue('apps.yandexrealty.allow_personal_feeds') && '' != trim($this->getConfigValue('apps.yandexrealty.allow_personal_feeds_token')) && $_GET['token'] == $this->getConfigValue('apps.yandexrealty.allow_personal_feeds_token')) {
                $DBC = DBC::getInstance();
                if (1 == $this->getConfigValue('use_registration_email_confirm')) {
                    $query = 'SELECT `user_id` FROM ' . DB_PREFIX . '_user WHERE `user_id`=? AND `active`=?';
                    $stmt = $DBC->query($query, array(intval($_GET['user_id']), 1));
                } else {
                    $query = 'SELECT `user_id` FROM ' . DB_PREFIX . '_user WHERE `user_id`=?';
                    $stmt = $DBC->query($query, array(intval($_GET['user_id'])));
                }
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    $user_id = $ar['user_id'];
                }
            }

            $this->exportable_user_id = $user_id;
        }

        $this->remove_old_file($cachefile);
        if (!isset($user_id) && 1 == $this->getConfigValue('apps.yandexrealty.tofile') && file_exists($cachefile)) {
            $this->download_huge_file($cachefile);
            exit;
        }

        $data = $this->collectData();
        if (empty($data)) {
            echo '<?xml version="1.0" encoding="utf-8" ?>' . "\n";
            echo '<realty-feed xmlns="http://webmaster.yandex.ru/schemas/feed/realty/2010-06">' . "\n";
            echo '<generation-date>' . $this->formdate() . '</generation-date>' . "\n";
            echo '</realty-feed>';
            exit();
        }


        if (!isset($user_id) && 1 == (int) $this->getConfigValue('apps.yandexrealty.tofile')) {
            $tofile = true;
        } else {
            $tofile = false;
        }

        $this->associations = $this->loadAssociations();
        $this->fields_associations = $this->loadFieldsAssociations();


        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php';
        $Structure = new Structure_Manager();
        $this->category_structure = $Structure->loadCategoryStructure();
        $x = $Structure->createCatalogChains();
        $this->catalogChains = $x['txt'];



        $image_field = trim($this->getConfigValue('apps.yandexrealty.images_field'));

        $uploadsField = false;
        $hasUploadify = false;


        if('' != $image_field){
            $image_fields = array();
            $image_fields = explode(',', $image_field);

            foreach($image_fields as $imf){
                if (isset($this->form_data_shared[$imf]) && in_array($this->form_data_shared[$imf]['type'], array('uploads', 'uploadify_image'))) {
                    if ($this->form_data_shared[$imf]['type'] == 'uploadify_image') {
                        $hasUploadify = true;
                    } else {
                        $uploadsField[] = $imf;
                    }
                }
            }

        } else {
            foreach ($this->form_data_shared as $model_item) {
                if ($model_item['type'] == 'uploadify_image') {
                    $hasUploadify = true;
                    $uploadsField = false;
                    break;
                } elseif ($uploadsField === false && $model_item['type'] == 'uploads') {
                    $uploadsField = $model_item['name'];
                    break;
                }
            }
        }




        /*if ($image_field != '' && isset($this->form_data_shared[$image_field]) && in_array($this->form_data_shared[$image_field]['type'], array('uploads', 'uploadify_image'))) {
            if ($this->form_data_shared[$image_field]['type'] == 'uploadify_image') {
                $hasUploadify = true;
            } else {
                $uploadsField = $image_field;
            }
        } else {
            foreach ($this->form_data_shared as $model_item) {
                if ($model_item['type'] == 'uploadify_image') {
                    $hasUploadify = true;
                    $uploadsField = false;
                    break;
                } elseif ($uploadsField === false && $model_item['type'] == 'uploads') {
                    $uploadsField = $model_item['name'];
                }
            }
        }*/

        $contracts = array();

        if ('' != trim($this->getConfigValue('apps.yandexrealty.sell'))) {
            $st = explode(':', $this->getConfigValue('apps.yandexrealty.sell'));
            if (count($st) > 1) {
                $stv = explode(',', $st[1]);
                if (count($stv) > 0) {
                    $contracts['sale']['f'] = trim($st[0]);
                    foreach ($stv as $_stv) {
                        $contracts['sale']['v'][] = $_stv;
                    }
                }
            }
        }

        if ('' != trim($this->getConfigValue('apps.yandexrealty.rent'))) {
            $st = explode(':', $this->getConfigValue('apps.yandexrealty.rent'));
            if (count($st) > 1) {
                $stv = explode(',', $st[1]);
                if (count($stv) > 0) {
                    $contracts['rent']['f'] = trim($st[0]);
                    foreach ($stv as $_stv) {
                        $contracts['rent']['v'][] = $_stv;
                    }
                }
            }
        }

        $this->contracts = $contracts;

        $this->mappingCommBldTypes();

        $this->mappingCommTypesConditions();

        $this->mappingRenovationTypesConditions();
        $this->mappingESTUARenovationTypesConditions();
        $this->mappingQualityTypesConditions();

        $this->mappingStudioConditions();
        $this->mappingOpenPlanConditions();
        $this->mappingApartmentConditions();
        $this->mappingGarageTypes();
        $this->mappingNewflatConditions();
        $this->mappingTaxationTypesConditions();
        //var_dump($this->commTypesConditions);
        $this->mappingSpecialCommercialOptionsConditions();

        $this->mappingDealStatusConditions();


        $this->mappingLotType();

        $xml_obj = array();

        if ($tofile) {
            $xml_obj[] = '<?xml version="1.0" encoding="utf-8" ?>' . "\n";
            $xml_obj[] = '<realty-feed xmlns="http://webmaster.yandex.ru/schemas/feed/realty/2010-06">' . "\n";
            $xml_obj[] = '<generation-date>' . $this->formdate() . '</generation-date>' . "\n";

            if ( $this->is_huge_mode() ) {
                $f = fopen($cachefile, 'w');
                fwrite($f, implode("\n", $xml_obj));
                unset($xml_obj);
            }
        }
        echo '<?xml version="1.0" encoding="utf-8" ?>' . "\n";
        echo '<realty-feed xmlns="http://webmaster.yandex.ru/schemas/feed/realty/2010-06">' . "\n";
        echo '<generation-date>' . $this->formdate() . '</generation-date>' . "\n";

        $log = array();
        $this->errors = array();


        $this->contacts_export_mode = intval($this->getConfigValue('apps.yandexrealty.contacts_export_mode'));

        if ($this->contacts_export_mode == 1) {

            require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/users/users_manager.php';
            $UM = new Users_Manager();

            $contacts_str = trim($this->getConfigValue('apps.yandexrealty.contacts_assoc_str'));

            if ($contacts_str == '') {
                $this->contacts_mode['*'] = 2;
            } else {
                $matches = array();
                if (preg_match('/^\*:([1-4])$/', $contacts_str, $matches)) {
                    $this->contacts_mode['*'] = $matches[1];
                } else {
                    $matches_all = array();
                    if (preg_match_all('/((\*|[\d]+):([1-4]))/', $contacts_str, $matches_all)) {
                        foreach ($matches_all[2] as $k => $g) {
                            if ($g == '*') {
                                $this->contacts_mode['*'] = $matches_all[3][$k];
                            } else {
                                $this->contacts_mode[intval($g)] = $matches_all[3][$k];
                            }
                        }
                    } else {
                        $this->contacts_mode['*'] = 2;
                    }
                }
            }

            $groups_assoc_str = trim($this->getConfigValue('apps.yandexrealty.groups_assoc_str'));

            if ($groups_assoc_str == '') {
                $this->group_assoc['*'] = 'o';
            } else {
                $matches = array();
                if (preg_match('/^\*:([oad])$/', $groups_assoc_str, $matches)) {
                    $this->group_assoc['*'] = $matches[1];
                } else {
                    $matches_all = array();
                    if (preg_match_all('/((\*|[\d]+):([oad]))/', $groups_assoc_str, $matches_all)) {
                        foreach ($matches_all[2] as $k => $g) {
                            if ($g == '*') {
                                $this->group_assoc['*'] = trim($matches_all[3][$k]);
                            } else {
                                $this->group_assoc[intval($g)] = trim($matches_all[3][$k]);
                            }
                        }
                    } else {
                        $this->group_assoc['*'] = 'o';
                    }
                }
            }
        }



        /* if(1==(int)$this->getConfigValue('apps.yandexrealty.nonassociated_not_export') && empty($this->associations)){

          }else{ */
        foreach ($data as $data_item) {
            if ( $this->is_huge_mode() ) {
                $data_item = $this->extract_one_item($data_item);
            }

            $xml_collectorp = array();
            $xml_str = '';         

            $data_topic = (int) $data_item['topic_id'];

            $this->presetCommonParams($data_item);

            //!empty($this->associations) && isset($this->associations[$data_topic]) && $this->associations[$data_topic]['realty_type']!=0
            /* if(1==(int)$this->getConfigValue('apps.yandexrealty.nonassociated_not_export') && (!isset($this->associations[$data_topic]) || $this->associations[$data_topic]['realty_type']==0)){
              $this->errors[]=$data_item['id'].' DECLINED: No associations setted';
              $xml_str='--';
              }else{ */
            $xml_collectorp = array();
            $xml_collectorp[] = $this->exInternalId($data_item);

            $xml_collector_item = $this->collectItemXML($data_item, $hasUploadify, $uploadsField);
            $xml_collectorp = array_merge($xml_collectorp, $xml_collector_item);
            $xml_collectorp[] = '</offer>' . "\n";

            foreach ($xml_collectorp as $k => $v) {
                if ($v == '') {
                    unset($xml_collectorp[$k]);
                }
            }

            if (!empty($xml_collectorp)) {
                $xml_str = implode("\n", $xml_collectorp);
            }

            

            if (empty($this->errors) && $xml_str != '') {
                if ($tofile) {
                    $xml_obj[] = $xml_str;
                }
                echo $xml_str;
                $log[] = $data_item['id'] . ' EXPORTED';
            } elseif (!empty($this->errors) && $xml_str != '') {
                foreach ($this->errors as $er) {
                    $log[] = $er;
                }
            }

            if ($tofile && $this->is_huge_mode() && isset($xml_obj)) {
                fwrite($f, implode("\n", $xml_obj));
                unset($xml_obj);
            }

            $this->errors = array();
        }

        if ($tofile and !$this->is_huge_mode()) {
            $xml_obj[] = '</realty-feed>';
            $f = fopen($cachefile, 'w');
            fwrite($f, implode("\n", $xml_obj));
            fclose($f);
        } elseif ($this->is_huge_mode()) {
            $xml_obj[] = '</realty-feed>';
            if ( !is_null($f) ) {
                fwrite($f, implode("\n", $xml_obj));
                fclose($f);
            }
            unset($xml_obj);
        }


        echo '</realty-feed>';
        $this->saveExportLogs($log);
        //print_r($log);
    }

    function hook_data ($data_item) {
        if ( $this->isHookEnabled() ) {
            $data_item = yandex_data_hook($data_item);

        }
        return $data_item;
    }

    protected function collectItemXML($data_item, $hasUploadify, $uploadsField) {

        $xml_collectorp = array();
        $data_item = $this->hook_data($data_item);
        //$xml_collectorp[]=$this->exInternalId($data_item);
        $xml_collectorp[] = $this->exPropertyType($data_item);
        $operational_type = '';
        $xml_collectorp[] = $this->exType($data_item, $operational_type);

        $xml_collectorp[] = $this->exCategory($data_item);
        $xml_collectorp[] = $this->exGarageType($data_item);
        $xml_collectorp[] = $this->exCommercialType($data_item);
        $xml_collectorp[] = $this->exCommercialBuildingType($data_item);
        $xml_collectorp[] = $this->exURL($data_item);
        $xml_collectorp[] = $this->exCadastralNumber($data_item);
        $xml_collectorp[] = $this->exCreationDate($data_item);
        $xml_collectorp[] = $this->exLastUpdateDate($data_item);
        $xml_collectorp[] = $this->exExpireDate($data_item);
        $xml_collectorp[] = $this->exPayedAdv($data_item);
        $xml_collectorp[] = $this->exManuallyAdded($data_item);
        $xml_collectorp[] = $this->exLocation($data_item);
        $xml_collectorp[] = $this->exSalesAgent($data_item);
        $xml_collectorp[] = $this->exPrice($data_item, $operational_type);
        $xml_collectorp[] = $this->exCommission($data_item);
        $xml_collectorp[] = $this->exNotForAgents($data_item);
        $xml_collectorp[] = $this->exHaggle($data_item);
        $xml_collectorp[] = $this->exMortgage($data_item);
        if ($data_item['__operational_type'] == 'rent') {
            $xml_collectorp[] = $this->exPrepayment($data_item);
            $xml_collectorp[] = $this->exRentPflege($data_item);

            $xml_collectorp[] = $this->exCleaningIncluded($data_item);
            $xml_collectorp[] = $this->exElectricityIncluded($data_item);
            $xml_collectorp[] = $this->exUtilitiesIncluded($data_item);
        }

        $xml_collectorp[] = $this->exAgentFee($data_item);



        $xml_collectorp[] = $this->exDealStatus($data_item);

        $xml_collectorp[] = $this->exWithPets($data_item);
        $xml_collectorp[] = $this->exWithChildren($data_item);
        $xml_collectorp[] = $this->exDescription($data_item);
        $xml_collectorp[] = $this->exImages($data_item, $hasUploadify, $uploadsField);
        if ($this->export_mode == 'EST.UA') {
            $xml_collectorp[] = $this->exRenovationESTUA($data_item);
        } else {
            $xml_collectorp[] = $this->exRenovation($data_item);
            $xml_collectorp[] = $this->exQuality($data_item);
        }

        $xml_collectorp[] = $this->exStudio($data_item);
        $xml_collectorp[] = $this->exApartments($data_item);
        $xml_collectorp[] = $this->exArea($data_item);
        $xml_collectorp[] = $this->exLivingSpace($data_item);
        $xml_collectorp[] = $this->exKitchenSpace($data_item);
        $xml_collectorp[] = $this->exLotType($data_item);
        $xml_collectorp[] = $this->exNewFlat($data_item);

        $xml_collectorp[] = $this->exRooms($data_item);
        $xml_collectorp[] = $this->exRoomsType($data_item);
        $xml_collectorp[] = $this->exOpenPlan($data_item);

        $xml_collectorp[] = $this->exRoomsOffered($data_item);
        $xml_collectorp[] = $this->exRoomSpace($data_item);
        $xml_collectorp[] = $this->exPhone($data_item);
        $xml_collectorp[] = $this->exInternet($data_item);
        $xml_collectorp[] = $this->exRoomFurniture($data_item);
        $xml_collectorp[] = $this->exTelevision($data_item);
        $xml_collectorp[] = $this->exWashingMachine($data_item);
        $xml_collectorp[] = $this->exKitchenFurniture($data_item);
        $xml_collectorp[] = $this->exFloorCovering($data_item);
        $xml_collectorp[] = $this->exBathroomUnit($data_item);
        $xml_collectorp[] = $this->exBalcony($data_item);
        $xml_collectorp[] = $this->exRefrigerator($data_item);
        
        $xml_collectorp[] = $this->exFloorCount($data_item);
        $xml_collectorp[] = $this->exFloor($data_item);
        $xml_collectorp[] = $this->exWindowView($data_item);
        
        
        $xml_collectorp[] = $this->exYandexBuildingId($data_item);
        $xml_collectorp[] = $this->exYandexHouseId($data_item);
        $xml_collectorp[] = $this->exBuildingType($data_item);
        $xml_collectorp[] = $this->exBuildingName($data_item);
        $xml_collectorp[] = $this->exBuiltYear($data_item);
        
        if ($data_item['__new_flat'] == 1) {            
            $xml_collectorp[] = $this->exReadyQuarter($data_item);
            $xml_collectorp[] = $this->exBuildingState($data_item);
            $xml_collectorp[] = $this->exBuildingSeries($data_item);
        }
        
        $xml_collectorp[] = $this->exIsElite($data_item);
        $xml_collectorp[] = $this->exRubbishChute($data_item);
        $xml_collectorp[] = $this->exLift($data_item);
        $xml_collectorp[] = $this->exCeilingHeight($data_item);
        $xml_collectorp[] = $this->exAlarm($data_item);
        $xml_collectorp[] = $this->exParking($data_item);
        $xml_collectorp[] = $this->exSauna($data_item);
        $xml_collectorp[] = $this->exHeatingSupply($data_item);
        $xml_collectorp[] = $this->exWaterSupply($data_item);
        $xml_collectorp[] = $this->exSewerageSupply($data_item);
        $xml_collectorp[] = $this->exPmg($data_item);
        $xml_collectorp[] = $this->exKitchen($data_item);
        $xml_collectorp[] = $this->exPool($data_item);
        $xml_collectorp[] = $this->exBilliard($data_item);
        $xml_collectorp[] = $this->exElectricitySupply($data_item);
        $xml_collectorp[] = $this->exGasSupply($data_item);
        $xml_collectorp[] = $this->exToilet($data_item);
        $xml_collectorp[] = $this->exShower($data_item);

        $xml_collectorp[] = $this->exVideoReview($data_item);


        if ($this->export_mode == 'EST.UA') {
            $this->collectCustomEstuaParams($data_item, $xml_collectorp);
        }
        //$xml_collectorp[]='</offer>';
        /* foreach($xml_collectorp as $k=>$v){
          if($v==''){
          unset($xml_collectorp[$k]);
          }
          } */
        return $xml_collectorp;
    }

    protected function collectCustomEstuaParams($data_item, &$xml_collectorp) {

    }

    protected function exElectricitySupply($data_item) {
        if (isset($this->form_data_shared['electricity_supply']) && isset($data_item['electricity_supply'])) {
            if ((int) $data_item['electricity_supply'] == 1) {
                return '<electricity-supply>1</electricity-supply>';
            } else {
                return '<electricity-supply>0</electricity-supply>';
            }
        }
    }

    protected function exGasSupply($data_item) {
        if (isset($this->form_data_shared['gas_supply']) && isset($data_item['gas_supply'])) {
            if ((int) $data_item['gas_supply'] == 1) {
                return '<gas-supply>1</gas-supply>';
            } else {
                return '<gas-supply>0</gas-supply>';
            }
        }
    }

    protected function exToilet($data_item) {
        if (isset($this->form_data_shared['toilet']) && isset($data_item['toilet'])) {
            $mf = $this->form_data_shared['toilet'];
            $di = $data_item['toilet'];
            if ($mf['type'] == 'select_box' && intval($di) != 0 && isset($mf['select_data'][$di])) {
                return '<toilet>' . self::symbolsClear($mf['select_data'][$di]) . '</toilet>';
            } elseif ($mf['type'] != 'select_box' && $di != '') {
                return '<toilet>' . self::symbolsClear($di) . '</toilet>';
            }
        }
        /* if(isset($this->form_data_shared['toilet']) && isset($data_item['toilet']) && $data_item['toilet']!=''){
          return '<toilet>'.self::symbolsClear($data_item['toilet']).'</toilet>';
          } */
    }

    protected function exShower($data_item) {
        if (isset($this->form_data_shared['shower']) && isset($data_item['shower'])) {
            $mf = $this->form_data_shared['shower'];
            $di = $data_item['shower'];
            if ($mf['type'] == 'select_box' && intval($di) != 0 && isset($mf['select_data'][$di])) {
                return '<shower>' . self::symbolsClear($mf['select_data'][$di]) . '</shower>';
            } elseif ($mf['type'] != 'select_box' && $di != '') {
                return '<shower>' . self::symbolsClear($di) . '</shower>';
            }
        }
        /* if(isset($this->form_data_shared['shower']) && isset($data_item['shower']) && $data_item['shower']!=''){
          return '<shower>'.self::symbolsClear($data_item['shower']).'</shower>';
          } */
    }

    protected function exComplexData($data_item) {
        $rs = '';
        if (isset($data_item['building-name']) and $data_item['building-name'] != '') {
            $rs .= '<building-name>' . self::symbolsClear($data_item['building-name']) . '</building-name>';
        }
        if (isset($data_item['building-type']) and $data_item['building-type'] != '') {
            $rs .= '<building-type>' . self::symbolsClear($data_item['building-type']) . '</building-type>';
        }
        if (isset($data_item['renovation']) and $data_item['renovation'] != '') {
            $rs .= '<renovation>' . self::symbolsClear($data_item['renovation']) . '</renovation>';
        }
        if (isset($data_item['built-year']) and $data_item['built-year'] != '') {
            $rs .= '<built-year>' . self::symbolsClear($data_item['built-year']) . '</built-year>';
        }
        return $rs;
    }

    protected function exSauna($data_item) {
        if (isset($this->form_data_shared['sauna']) && isset($data_item['sauna'])) {
            if ((int) $data_item['sauna'] == 1) {
                return '<sauna>1</sauna>';
            } else {
                return '<sauna>0</sauna>';
            }
        }
    }

    protected function exHeatingSupply($data_item) {
        if (isset($this->form_data_shared['heating_supply']) && isset($data_item['heating_supply'])) {
            if ((int) $data_item['heating_supply'] == 1) {
                return '<heating-supply>1</heating-supply>';
            } else {
                return '<heating-supply>0</heating-supply>';
            }
        }
    }

    protected function exWaterSupply($data_item) {
        if (isset($this->form_data_shared['water_supply']) && isset($data_item['water_supply'])) {
            if ((int) $data_item['water_supply'] == 1) {
                return '<water-supply>1</water-supply>';
            } else {
                return '<water-supply>0</water-supply>';
            }
        }
    }

    protected function exSewerageSupply($data_item) {
        if (isset($this->form_data_shared['sewerage_supply']) && isset($data_item['sewerage_supply'])) {
            if ((int) $data_item['sewerage_supply'] == 1) {
                return '<sewerage-supply>1</sewerage-supply>';
            } else {
                return '<sewerage-supply>0</sewerage-supply>';
            }
        }
    }

    protected function exPmg($data_item) {
        if (isset($this->form_data_shared['pmg']) && isset($data_item['pmg'])) {
            if ((int) $data_item['pmg'] == 1) {
                return '<pmg>1</pmg>';
            } else {
                return '<pmg>0</pmg>';
            }
        }
    }

    protected function exKitchen($data_item) {
        if (isset($this->form_data_shared['kitchen']) && isset($data_item['kitchen'])) {
            if ((int) $data_item['kitchen'] == 1) {
                return '<kitchen>1</kitchen>';
            } else {
                return '<kitchen>0</kitchen>';
            }
        }
    }

    protected function exPool($data_item) {
        if (isset($this->form_data_shared['pool']) && isset($data_item['pool'])) {
            if ((int) $data_item['pool'] == 1) {
                return '<pool>1</pool>';
            } else {
                return '<pool>0</pool>';
            }
        }
    }

    protected function exBilliard($data_item) {
        if (isset($this->form_data_shared['billiard']) && isset($data_item['billiard'])) {
            if ((int) $data_item['billiard'] == 1) {
                return '<billiard>1</billiard>';
            } else {
                return '<billiard>0</billiard>';
            }
        }
    }

    protected function exParking($data_item) {
        if (isset($this->form_data_shared['parking']) && isset($data_item['parking'])) {
            if ((int) $data_item['parking'] == 1) {
                return '<parking>1</parking>';
            } else {
                return '<parking>0</parking>';
            }
        }
    }

    protected function exAlarm($data_item) {
        if (isset($this->form_data_shared['alarm']) && isset($data_item['alarm'])) {
            if ((int) $data_item['alarm'] == 1) {
                return '<alarm>1</alarm>';
            } else {
                return '<alarm>0</alarm>';
            }
        }
    }

    protected function exCeilingHeight($data_item) {
        if (isset($this->form_data_shared['ceiling_height']) && isset($data_item['ceiling_height'])) {
            $x = preg_replace('/[^0-9.,]/', '', $data_item['ceiling_height']);
            $x = str_replace(',', '.', $x);
            $x = floatval($x);
            if ($x != 0) {
                return '<ceiling-height>' . $x . '</ceiling-height>';
            }
        }
    }

    protected function exLift($data_item) {
        if (isset($this->form_data_shared['lift']) && isset($data_item['lift'])) {
            if ((int) $data_item['lift'] == 1) {
                return '<lift>1</lift>';
            } else {
                return '<lift>0</lift>';
            }
        }
    }

    protected function exRubbishChute($data_item) {
        if (isset($this->form_data_shared['rubbish_chute']) && isset($data_item['rubbish_chute'])) {
            if ((int) $data_item['rubbish_chute'] == 1) {
                return '<rubbish-chute>1</rubbish-chute>';
            } else {
                return '<rubbish-chute>0</rubbish-chute>';
            }
        }
    }

    protected function exIsElite($data_item) {
        if (isset($this->form_data_shared['elite']) && isset($data_item['elite'])) {
            if ((int) $data_item['elite'] == 1) {
                return '<is-elite>1</is-elite>';
            }
        }
    }

    protected function exBuildingSeries($data_item) {
        if (isset($this->form_data_shared['building_series']) && isset($data_item['building_series']) && $data_item['building_series'] != '') {
            return '<building-series>' . self::symbolsClear($data_item['building_series']) . '</building-series>';
        }
    }

    protected function exBuildingState($data_item) {
        if (isset($this->form_data_shared['building_state']) && isset($data_item['building_state'])) {
            if($this->form_data_shared['building_state']['type']=='select_box'){
                if ($data_item['building_state'] != '0') {
                    return '<building-state>' . self::symbolsClear($data_item['building_state']) . '</building-state>';
                }
            }else{
                if ($data_item['building_state'] != '') {
                    return '<building-state>' . self::symbolsClear($data_item['building_state']) . '</building-state>';
                }
            }
            //return '<building-state>' . self::symbolsClear($data_item['building_state']) . '</building-state>';
        }elseif($this->getConfigValue('apps.yandexrealty.complex_enable') == 1 && isset($data_item['building_state'])){
			if ($data_item['building_state'] != '0' && $data_item['building_state'] != '') {
				return '<building-state>' . self::symbolsClear($data_item['building_state']) . '</building-state>';
			}
		}
        /*if (isset($this->form_data_shared['building_state']) && isset($data_item['building_state']) && $data_item['building_state'] != '') {
            return '<building-state>' . self::symbolsClear($data_item['building_state']) . '</building-state>';
        }*/

        $bY = '';
        $bQ = '';
        if (isset($this->form_data_shared['built_year']) && isset($data_item['built_year']) && $data_item['built_year'] != '') {
            //Выгрузка данных о годе сдачи из "родных" данных объекта
            $x = preg_replace('/[^0-9]/', '', $data_item['built_year']);
            if (preg_match('/([1|2][0-9][0-9][0-9])/', $x, $matches)) {
                $bY = $matches[1];
            }
        } elseif (1 == $this->getConfigValue('apps.yandexrealty.complex_enable') && isset($data_item['built-year']) && $data_item['built-year'] != '') {
            //Выгрузка данных о годе сдачи из данных загруженных из ЖК
            $x = preg_replace('/[^0-9]/', '', $data_item['built-year']);
            if (preg_match('/([1|2][0-9][0-9][0-9])/', $x, $matches)) {
                $bY = $matches[1];
            }
        }
        if (isset($this->form_data_shared['ready_quarter']) && isset($data_item['ready_quarter']) && $data_item['ready_quarter'] != '') {
            //Выгрузка данных о квартале сдачи из "родных" данных объекта
            $x = preg_replace('/[^0-9]/', '', $data_item['ready_quarter']);
            if (preg_match('/([1-4])/', $x, $matches)) {
                $bQ = $matches[1];
            }
        } elseif (1 == $this->getConfigValue('apps.yandexrealty.complex_enable') && isset($data_item['ready_quarter']) && $data_item['ready_quarter'] != '') {
            //Выгрузка данных о квартале сдачи из данных загруженных из ЖК
            $x = preg_replace('/[^0-9]/', '', $data_item['ready_quarter']);
            if (preg_match('/([1-4])/', $x, $matches)) {
                $bQ = $matches[1];
            }
        }
        if ($bY != '' && $bQ != '') {
            $m = $bQ * 3 + 1;
            if ($m > 12) {
                $m = $m - 12;
                $bY += 1;
            }
            $l = strtotime($bY . '-' . ($m < 10 ? '0' . $m : $m) . '-01 00:00:00');
            if ($l < time()) {
                return '<building-state>hand-over</building-state>';
            } else {
                return '<building-state>unfinished</building-state>';
            }
        }
    }

    protected function exBuiltYear($data_item) {
        if (isset($this->form_data_shared['built_year']) && isset($data_item['built_year']) && $data_item['built_year'] != '') {
            //Выгрузка данных о годе сдачи из "родных" данных объекта
            $x = preg_replace('/[^0-9]/', '', $data_item['built_year']);
            if (preg_match('/([1|2][0-9][0-9][0-9])/', $x, $matches)) {
                return '<built-year>' . $matches[1] . '</built-year>';
            }
        } elseif (1 == $this->getConfigValue('apps.yandexrealty.complex_enable') && isset($data_item['built-year']) && $data_item['built-year'] != '') {
            //Выгрузка данных о годе сдачи из данных загруженных из ЖК
            $x = preg_replace('/[^0-9]/', '', $data_item['built-year']);
            if (preg_match('/([1|2][0-9][0-9][0-9])/', $x, $matches)) {
                return '<built-year>' . $matches[1] . '</built-year>';
            }
        }
    }

    protected function exReadyQuarter($data_item) {
        if (isset($this->form_data_shared['ready_quarter']) && isset($data_item['ready_quarter']) && $data_item['ready_quarter'] != '') {
            //Выгрузка данных о квартале сдачи из "родных" данных объекта
            $x = preg_replace('/[^0-9]/', '', $data_item['ready_quarter']);
            if (preg_match('/([1-4])/', $x, $matches)) {
                return '<ready-quarter>' . $matches[1] . '</ready-quarter>';
            }
        } elseif (1 == $this->getConfigValue('apps.yandexrealty.complex_enable') && isset($data_item['ready_quarter']) && $data_item['ready_quarter'] != '') {
            //Выгрузка данных о квартале сдачи из данных загруженных из ЖК
            $x = preg_replace('/[^0-9]/', '', $data_item['ready_quarter']);
            if (preg_match('/([1-4])/', $x, $matches)) {
                return '<ready-quarter>' . $matches[1] . '</ready-quarter>';
            }
        }
    }

    protected function exWindowView($data_item) {
        if (isset($this->form_data_shared['window_view']) && isset($data_item['window_view'])) {
            $mf = $this->form_data_shared['window_view'];
            $di = $data_item['window_view'];
            if ($mf['type'] == 'select_box' && intval($di) != 0 && isset($mf['select_data'][$di])) {
                return '<window-view>' . self::symbolsClear($mf['select_data'][$di]) . '</window-view>';
            } elseif ($mf['type'] != 'select_box' && $di != '') {
                return '<window-view>' . self::symbolsClear($di) . '</window-view>';
            }
        }
        /* if(isset($this->form_data_shared['window_view']) && isset($data_item['window_view']) && $data_item['window_view']!=''){
          return '<window-view>'.self::symbolsClear($data_item['window_view']).'</window-view>';
          } */
    }

    protected function exFloor($data_item) {
        if (isset($this->form_data_shared['floor']) && isset($data_item['floor']) && (int) $data_item['floor'] != 0) {
            return '<floor>' . (int) $data_item['floor'] . '</floor>';
        }
    }

    protected function exFloorCount($data_item) {
        if (isset($this->form_data_shared['floor_count']) && isset($data_item['floor_count']) && (int) $data_item['floor_count'] != 0) {
            return '<floors-total>' . (int) $data_item['floor_count'] . '</floors-total>';
        }
    }

    protected function exBuildingName($data_item) {
        if (isset($this->form_data_shared['building_name']) && isset($data_item['building_name']) && $data_item['building_name'] != '') {
            return '<building-name>' . self::symbolsClear($data_item['building_name']) . '</building-name>';
        }
        //Если не нашли building_name, то пробуем building-name
        if (isset($data_item['building-name']) && $data_item['building-name'] != '') {
            return '<building-name>' . self::symbolsClear($data_item['building-name']) . '</building-name>';
        }
    }

    protected function exBuildingType($data_item) {
        if (isset($this->form_data_shared['building_type']) && isset($data_item['building_type'])) {
            $mf = $this->form_data_shared['building_type'];
            $di = $data_item['building_type'];
            if ($mf['type'] == 'select_box' && $di != '0' && $di != '' && isset($mf['select_data'][$di])) {
                return '<building-type>' . self::symbolsClear($mf['select_data'][$di]) . '</building-type>';
            } elseif ($mf['type'] != 'select_box' && $di != '') {
                return '<building-type>' . self::symbolsClear($di) . '</building-type>';
            }
        } elseif (isset($data_item['building-type']) && $data_item['building-type'] != '') {
            return '<building-type>' . self::symbolsClear($data_item['building-type']) . '</building-type>';
        } elseif (isset($this->form_data_shared['walls']) && isset($data_item['walls'])) {
            $mf = $this->form_data_shared['walls'];
            $di = $data_item['walls'];
            if ($mf['type'] == 'select_box' && $di != '0' && $di != '' && isset($mf['select_data'][$di])) {
                return '<building-type>' . self::symbolsClear($mf['select_data'][$di]) . '</building-type>';
            } elseif ($mf['type'] != 'select_box' && $di != '') {
                return '<building-type>' . self::symbolsClear($di) . '</building-type>';
            }
        }
        /* if(isset($this->form_data_shared['building_type']) && isset($data_item['building_type']) && $data_item['building_type']!=''){
          return '<building-type>'.self::symbolsClear($data_item['building_type']).'</building-type>';
          }elseif(isset($this->form_data_shared['walls']) && isset($data_item['walls']) && $data_item['walls']!=''){
          return '<building-type>'.self::symbolsClear($data_item['walls']).'</building-type>';
          } */
    }

    protected function exRefrigerator($data_item) {
        if (isset($this->form_data_shared['refrigerator']) && isset($data_item['refrigerator'])) {
            if ((int) $data_item['refrigerator'] == 1) {
                return '<refrigerator>1</refrigerator>';
            } else {
                return '<refrigerator>0</refrigerator>';
            }
        }
    }

    protected function exBalcony($data_item) {
        if($this->getConfigValue('apps.yandexrealty.hasbalconyfields')){
            $balkons_field = trim($this->getConfigValue('apps.yandexrealty.hasbalconyfields_balkons'));
            $logias_field = trim($this->getConfigValue('apps.yandexrealty.hasbalconyfields_logias'));
            $balkons = 0;
            $logias = 0;
            if (isset($this->form_data_shared[$balkons_field])) {
                $balkons = intval($data_item[$balkons_field]);
            }
            if (isset($this->form_data_shared[$logias_field])) {
                $logias = intval($data_item[$logias_field]);
            }
            $parts = array();
            if($balkons == 1){
               $parts[]='балкон';
            }elseif($balkons%10 == 1){
                $parts[]=$balkons.' балкон';
            }elseif(in_array($balkons, array(2,3,4)) || in_array($balkons%10, array(2,3,4))){
                $parts[]=$balkons.' балкона';
            }elseif($balkons > 0){
                $parts[]=$balkons.' балконов';
            }

            if($logias == 1){
               $parts[]='лоджия';
            }elseif($logias%10 == 1){
                $parts[]=$logias.' лоджия';
            }elseif(in_array($logias, array(2,3,4)) || in_array($logias%10, array(2,3,4))){
                $parts[]=$logias.' лоджии';
            }elseif($logias > 0){
                $parts[]=$logias.' лоджий';
            }
            if(!empty($parts)){
                return '<balcony>' . implode(', ', $parts) . '</balcony>';
            }
        }else{
            if (isset($this->form_data_shared['balcony']) && isset($data_item['balcony'])) {
                $mf = $this->form_data_shared['balcony'];
                $di = $data_item['balcony'];
                if ($mf['type'] == 'select_box' && intval($di) != 0 && isset($mf['select_data'][$di])) {
                    return '<balcony>' . self::symbolsClear($mf['select_data'][$di]) . '</balcony>';
                } elseif ($mf['type'] != 'select_box' && $di != '') {
                    return '<balcony>' . self::symbolsClear($di) . '</balcony>';
                }
            }
        }
    }

    protected function exBathroomUnit($data_item) {
        if($this->getConfigValue('apps.yandexrealty.bathroomunitfields')){
            $sep_field = trim($this->getConfigValue('apps.yandexrealty.bathroomunitfields_sep'));
            $comb_field = trim($this->getConfigValue('apps.yandexrealty.bathroomunitfields_comb'));
            $separated = 0;
            $combined = 0;
            if (isset($this->form_data_shared[$sep_field])) {
                $separated = intval($data_item[$sep_field]);
            }
            if (isset($this->form_data_shared[$comb_field])) {
                $combined = intval($data_item[$comb_field]);
            }

            if(($separated + $combined) > 1){
                return '<bathroom-unit>' . ($combined + $separated) . '</bathroom-unit>';
            }elseif($separated == 0){
                return '<bathroom-unit>совмещенный</bathroom-unit>';
            }elseif($combined == 0){
                return '<bathroom-unit>раздельный</bathroom-unit>';
            }




        }else{
            if (isset($this->form_data_shared['bathroom_unit']) && isset($data_item['bathroom_unit'])) {
                $mf = $this->form_data_shared['bathroom_unit'];
                $di = $data_item['bathroom_unit'];
                if ($mf['type'] == 'select_box' && intval($di) != 0 && isset($mf['select_data'][$di])) {
                    return '<bathroom-unit>' . self::symbolsClear($mf['select_data'][$di]) . '</bathroom-unit>';
                } elseif ($mf['type'] != 'select_box' && $di != '') {
                    return '<bathroom-unit>' . self::symbolsClear($di) . '</bathroom-unit>';
                }
            }

        }
    }

    protected function exFloorCovering($data_item) {
        if (isset($this->form_data_shared['floor_covering']) && isset($data_item['floor_covering'])) {
            $mf = $this->form_data_shared['floor_covering'];
            $di = $data_item['floor_covering'];
            if ($mf['type'] == 'select_box' && intval($di) != 0 && isset($mf['select_data'][$di])) {
                return '<floor-covering>' . self::symbolsClear($mf['select_data'][$di]) . '</floor-covering>';
            } elseif ($mf['type'] != 'select_box' && $di != '') {
                return '<floor-covering>' . self::symbolsClear($di) . '</floor-covering>';
            }
        }
        /* if(isset($this->form_data_shared['floor_covering']) && isset($data_item['floor_covering']) && $data_item['floor_covering']!=''){
          return '<floor-covering>'.self::symbolsClear($data_item['floor_covering']).'</floor-covering>';
          } */
    }

    protected function exKitchenFurniture($data_item) {
        if (isset($this->form_data_shared['kitchen_furniture']) && isset($data_item['kitchen_furniture'])) {
            if ((int) $data_item['kitchen_furniture'] == 1) {
                return '<kitchen-furniture>1</kitchen-furniture>';
            } else {
                return '<kitchen-furniture>0</kitchen-furniture>';
            }
        }
    }

    protected function exWashingMachine($data_item) {
        if (isset($this->form_data_shared['washing_machine']) && isset($data_item['washing_machine'])) {
            if ((int) $data_item['washing_machine'] == 1) {
                return '<washing-machine>1</washing-machine>';
            } else {
                return '<washing-machine>0</washing-machine>';
            }
        }
    }

    protected function exTelevision($data_item) {
        if (isset($this->form_data_shared['television']) && isset($data_item['television'])) {
            if ((int) $data_item['television'] == 1) {
                return '<television>1</television>';
            } else {
                return '<television>0</television>';
            }
        }
    }

    protected function exPhone($data_item) {
        if (isset($this->form_data_shared['is_telephone']) && isset($data_item['is_telephone'])) {
            if ((int) $data_item['is_telephone'] == 1) {
                return '<phone>1</phone>';
            } else {
                return '<phone>0</phone>';
            }
        }
    }

    protected function exInternet($data_item) {
        if (isset($this->form_data_shared['internet']) && isset($data_item['internet'])) {
            if ((int) $data_item['internet'] == 1) {
                return '<internet>1</internet>';
            } else {
                return '<internet>0</internet>';
            }
        }
    }

    protected function exRoomFurniture($data_item) {
        if (isset($this->form_data_shared['room_furniture']) && isset($data_item['room_furniture'])) {
            if ((int) $data_item['room_furniture'] == 1) {
                return '<room-furniture>1</room-furniture>';
            } else {
                return '<room-furniture>0</room-furniture>';
            }
        }
    }

    protected function exRoomsOffered($data_item) {
        if($data_item['__is_studio'] == 0 && $data_item['__is_openplan'] == 0){
            if(isset($data_item['__rooms_offered']) && 0<intval($data_item['__rooms_offered'])){
                return '<rooms-offered>' . $data_item['__rooms_offered'] . '</rooms-offered>';
            }
        }


        /*$export = false;

        $data_topic = intval($data_item['topic_id']);


        if ($this->export_mode == 'EST.UA') {

        } elseif ($this->export_mode == 'MEGET.UA') {

        } else {
            $yandex_association_id = 0;
            if (!empty($this->associations) && isset($this->associations[$data_topic]) && $this->associations[$data_topic]['realty_category'] != 0) {
                $yandex_association_id = $this->associations[$data_topic]['realty_category'];
                if (in_array($yandex_association_id, array(2,6))) {
                    $export = true;
                }
            }
        }

       // if(intval($data_item['open_plan']) == 1 || )

        if($export){
            $rooms_offered_filed = trim($this->getConfigValue('apps.yandexrealty.rooms_offered_field'));
            if(is_numeric($rooms_offered_filed) && $rooms_offered_filed>0){
                return '<rooms-offered>' . $rooms_offered_filed . '</rooms-offered>';
            }else{
                if($rooms_offered_filed=='' || !isset($this->form_data_shared[$rooms_offered_filed])){
                    $rooms_offered_filed = 'rooms_offered';
                }

                if (isset($this->form_data_shared[$rooms_offered_filed]) && isset($data_item[$rooms_offered_filed]) && intval($data_item[$rooms_offered_filed]) != 0) {
                    return '<rooms-offered>' . intval($data_item[$rooms_offered_filed]) . '</rooms-offered>';
                } else {
                    return '<rooms-offered>' . intval($data_item['room_count']) . '</rooms-offered>';
                }
            }
        }*/
    }

    protected function exRoomSpace($data_item) {
		$ret = array();
		if(isset($data_item['__rooms_offered']) && 0<intval($data_item['__rooms_offered'])){
			$rooms_offered_count = $data_item['__rooms_offered'];
            $rooms_area_field = trim($this->getConfigValue('apps.yandexrealty.rooms_area_field'));
			if (isset($this->form_data_shared[$rooms_area_field]) && isset($data_item[$rooms_area_field]) && trim($data_item[$rooms_area_field]) != '') {
				$area_str = trim($data_item[$rooms_area_field]);
				$rooms_area_field_divider = intval($this->getConfigValue('apps.yandexrealty.rooms_area_field_divider'));
				switch($rooms_area_field_divider){
					case '1' : {
						$rooms_area_field_divider = '-';
						break;
					}
					case '2' : {
						$rooms_area_field_divider = '+';
						break;
					}
					case '3' : {
						$rooms_area_field_divider = ';';
						break;
					}
					case '4' : {
						$rooms_area_field_divider = '+';
						$area_str = str_replace('-', '+', $area_str);
						break;
					}
					default : {
						$rooms_area_field_divider = ' ';
					}
				}
				$area_vals = explode($rooms_area_field_divider, trim($area_str));
				if(!empty($area_vals)){
					for($i=1; $i<=$rooms_offered_count; $i++){
						if(isset($area_vals[$i-1])){
							$ret[] = '<room-space><value>' . trim($area_vals[$i-1]) . '</value><unit>кв. м</unit></room-space>' . "\n";
						}
					}
					/*foreach($area_vals as $area){
						$ret[] = '<room-space><value>' . trim($area) . '</value><unit>кв. м</unit></room-space>' . "\n";
					}*/
				}
			}
        }


        return implode('', $ret);
    }

    protected function exOpenPlan($data_item) {
        if($data_item['__is_openplan'] == 1){
            return '<open-plan>1</open-plan>';
        }
        return '';
        $openplan = false;
        if (!empty($this->openPlanConditions)) {
            $res_cond = false;
            foreach ($this->openPlanConditions as $type_id => $type_conditions_line) {
                $res_variant = false;
                foreach ($type_conditions_line as $type_conditions_variant_item) {

                    if (isset($data_item[$type_conditions_variant_item['f']]) && in_array($data_item[$type_conditions_variant_item['f']], $type_conditions_variant_item['v'])) {
                        $res_variant = $res_variant || true;
                    } else {
                        $res_variant = $res_variant || false;
                    }
                }
                $res_cond = $res_cond || $res_variant;
                if ($res_cond) {
                    $openplan = true;
                    break;
                }
            }
        }
        if(!$openplan){
            if (isset($this->form_data_shared['open_plan']) && isset($data_item['open_plan'])) {
                if ((int) $data_item['open_plan'] == 1) {
                    $openplan = true;
                }
            }
        }
        if ($openplan) {
            return '<open-plan>1</open-plan>';
        }
        return '';
    }

    protected function exStudio($data_item) {
        if($data_item['__is_studio'] == 1){
            return '<studio>1</studio>';
        }
        return '';
        $studio = false;
        if (!empty($this->studioConditions)) {
            $res_cond = false;
            foreach ($this->studioConditions as $type_id => $type_conditions_line) {
                $res_variant = false;
                foreach ($type_conditions_line as $type_conditions_variant_item) {

                    if (isset($data_item[$type_conditions_variant_item['f']]) && in_array($data_item[$type_conditions_variant_item['f']], $type_conditions_variant_item['v'])) {
                        $res_variant = $res_variant || true;
                    } else {
                        $res_variant = $res_variant || false;
                    }
                }
                $res_cond = $res_cond || $res_variant;
                if ($res_cond) {
                    $studio = true;
                    break;
                }
            }
        }
        if ($studio) {
            return '<studio>1</studio>';
        }
        return '';
    }

    protected function exApartments($data_item) {
        $apartment = false;
        if (!empty($this->apartmentConditions)) {
            $res_cond = false;
            foreach ($this->apartmentConditions as $type_id => $type_conditions_line) {
                $res_variant = true;
                foreach ($type_conditions_line as $type_conditions_variant_item) {

                    if (isset($data_item[$type_conditions_variant_item['f']]) && in_array($data_item[$type_conditions_variant_item['f']], $type_conditions_variant_item['v'])) {
                        $res_variant = $res_variant && true;
                    } else {
                        $res_variant = $res_variant && false;
                    }
                }
                $res_cond = $res_cond || $res_variant;
                if ($res_cond) {
                    $apartment = true;
                    break;
                }
            }
        }
        if ($apartment) {
            return '<apartments>1</apartments>';
        }
        return '';
    }

    protected function exRoomsType($data_item) {
        if (isset($this->form_data_shared['rooms_type']) && isset($data_item['rooms_type'])) {
            $mf = $this->form_data_shared['rooms_type'];
            $di = $data_item['rooms_type'];
            if ($mf['type'] == 'select_box' && intval($di) != 0 && isset($mf['select_data'][$di])) {
                return '<rooms-type>' . self::symbolsClear($mf['select_data'][$di]) . '</rooms-type>';
            } elseif ($mf['type'] != 'select_box' && $di != '') {
                return '<rooms-type>' . self::symbolsClear($di) . '</rooms-type>';
            }
        }
    }

    protected function exRooms($data_item) {
        if($data_item['__is_studio'] == 0){
            if (isset($this->form_data_shared['rooms']) && isset($data_item['rooms']) && (int) $data_item['rooms'] != 0) {
                return '<rooms>' . (int) $data_item['rooms'] . '</rooms>';
            } elseif (isset($this->form_data_shared['room_count']) && isset($data_item['room_count']) && (int) $data_item['room_count'] != 0) {
                return '<rooms>' . (int) $data_item['room_count'] . '</rooms>';
            }
        }
    }

    protected function exNewFlat($data_item) {
        if ($data_item['__new_flat'] == 1) {
            return '<new-flat>1</new-flat>';
        }
        return '';
        /* if (isset($this->form_data_shared['new_flat']) && isset($data_item['new_flat'])) {
          if ((int) $data_item['new_flat'] == 1) {
          return '<new-flat>1</new-flat>';
          }
          }
          return ''; */
    }

    protected function exYandexBuildingId($data_item) {
        //echo 'building';
        if (isset($this->form_data_shared[$this->getConfigValue('apps.yandexrealty.yandex_building_id')]) && isset($data_item[$this->getConfigValue('apps.yandexrealty.yandex_building_id')]) && $data_item[$this->getConfigValue('apps.yandexrealty.yandex_building_id')] != '') {
            return '<yandex-building-id>' . $data_item[$this->getConfigValue('apps.yandexrealty.yandex_building_id')] . '</yandex-building-id>';
        }

        if ($this->getConfigValue('apps.yandexrealty.yandex_building_id') == 'complex.yandex_building_id') {
            $DBC = DBC::getInstance();
            $query = 'SELECT yandex_building_id FROM ' . DB_PREFIX . '_complex WHERE complex_id=?';
            $stmt = $DBC->query($query, array($data_item['complex_id']));
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                if ($ar['yandex_building_id'] != '') {
                    return '<yandex-building-id>' . $ar['yandex_building_id'] . '</yandex-building-id>';
                }
            }
        }
        return '';
    }

    protected function exYandexHouseId($data_item) {
        //echo 'building';
        if (isset($this->form_data_shared['yandex_house_id']) && $data_item['yandex_house_id'] != '') {
            return '<yandex-house-id>' . $data_item['yandex_house_id'] . '</yandex-house-id>';
        }

        if ($this->getConfigValue('apps.yandexrealty.complex_enable') == 1 && isset($data_item['yandex_house_id']) && $data_item['yandex_house_id'] != '') {
            return '<yandex-house-id>' . $data_item['yandex_house_id'] . '</yandex-house-id>';
        }
        return '';
    }

    protected function exLotType($data_item) {
        $data_topic = (int) $data_item['topic_id'];
        $export_this = false;
        $export_realty_type = '';

        if ($this->export_mode == 'EST.UA') {
            if (in_array($data_item['__est_ua_type'], $this->Types_Estua_Lots)) {
                $export_this = true;
                $export_realty_type = 'lot';
            } elseif (in_array($data_item['__est_ua_type'], $this->Types_Estua_Houses)) {
                $export_this = true;
                $export_realty_type = 'house';
            }
        } elseif ($this->export_mode == 'MEGET.UA') {
            if (in_array($data_item['__meget_ua_type'], $this->Types_Megetua_Lots)) {
                $export_this = true;
                $export_realty_type = 'lot';
            } elseif (in_array($data_item['__meget_ua_type'], $this->Types_Megetua_Houses)) {
                $export_this = true;
                $export_realty_type = 'house';
            }
        } else {
            $yandex_association_id = 0;
            if (!empty($this->associations) && isset($this->associations[$data_topic]) && $this->associations[$data_topic]['realty_category'] != 0) {
                $yandex_association_id = $yandex_association_id = $this->associations[$data_topic]['realty_category'];
                if (in_array($yandex_association_id, array(4, 15, 16, 25))) {
                    $export_this = true;
                    $export_realty_type = 'lot';
                } elseif (in_array($yandex_association_id, array(3,7,8,10,9,11,12, 13,14))) {
                    $export_this = true;
                    $export_realty_type = 'house';
                }
            }
        }


        $rs = '';

        if (!$export_this) {
            return '';
        }

        if ($export_realty_type == 'lot') {
            $lot_area_field = trim($this->getConfigValue('apps.yandexrealty.lot_area'));

            $meash = 'сот';
            $lot_area_field_dim = trim($this->getConfigValue('apps.yandexrealty.lot_area_dim'));
            if ($lot_area_field_dim == '' && isset($this->form_data_shared['square_unit'])) {
                if ($data_item['square_unit'] == 2) {
                    $meash = 'кв.м';
                } elseif ($data_item['square_unit'] == 3) {
                    $meash = 'га';
                }
            } elseif ($lot_area_field_dim != '') {
                if ($lot_area_field_dim == 'acr') {
                    $meash = 'сот';
                } elseif ($lot_area_field_dim == 'sqm') {
                    $meash = 'кв.м';
                } elseif ($lot_area_field_dim == 'ha') {
                    $meash = 'га';
                }
            }

            if ($lot_area_field == '' && isset($this->form_data_shared['land_area'])) {
                $lot_area_field = 'land_area';
            } elseif ($lot_area_field == '' && isset($this->form_data_shared['lot_area'])) {
                $lot_area_field = 'lot_area';
            }

            if (isset($data_item[$lot_area_field])) {
                $x = preg_replace('/[^0-9.,]/', '', $data_item[$lot_area_field]);
                $x = str_replace(',', '.', $x);
                $x = floatval($x);
                //$x=preg_replace('/[^0-9\.,]/','',$data_item['lot_area']);
                if ($x != 0) {
                    $rs .= '<lot-area>' . "\n";
                    $rs .= '<value>' . $x . '</value>' . "\n";
                    $rs .= '<unit>' . $meash . '</unit>' . "\n";
                    $rs .= '</lot-area>' . "\n";
                }
            }

            if ($this->export_mode == 'MEGET.UA') {
                $name = $this->getMegetuaCategoryName($data_item['__meget_ua_type']);
                if ($name !== '') {
                    $rs .= '<lot-type>' . $name . '</lot-type>';
                }
            } else {

                $lottype = $this->exLotTypeLotType($data_item);
                if($lottype != ''){
                    $rs .= '<lot-type>' . $lottype . '</lot-type>';
                }
                /*$lot_type_field = trim($this->getConfigValue('apps.yandexrealty.lot_type_field'));
                if('' == $lot_type_field){
                    $lot_type_field = 'lot_type';
                }

                if (isset($this->form_data_shared[$lot_type_field]) && isset($data_item[$lot_type_field])) {
                    $mf = $this->form_data_shared[$lot_type_field];
                    $di = $data_item[$lot_type_field];
                    if ($mf['type'] == 'select_box' && intval($di) != 0 && isset($mf['select_data'][$di])) {
                        $rs .= '<lot-type>' . self::symbolsClear($mf['select_data'][$di]) . '</lot-type>';
                    } elseif ($mf['type'] != 'select_box' && $di != '') {
                        $rs .= '<lot-type>' . self::symbolsClear($di) . '</lot-type>';
                    }
                }*/
            }
        } elseif ($export_realty_type == 'house') {

            $has_lot = false;

            $lot_area_field = trim($this->getConfigValue('apps.yandexrealty.add_lot_area'));

            $meash = 'сот';
            $lot_area_field_dim = trim($this->getConfigValue('apps.yandexrealty.add_lot_area_dim'));
            if ($lot_area_field_dim == '' && isset($this->form_data_shared['square_unit'])) {
                if ($data_item['square_unit'] == 2) {
                    $meash = 'кв.м';
                } elseif ($data_item['square_unit'] == 3) {
                    $meash = 'га';
                }
            } elseif ($lot_area_field_dim != '') {
                if ($lot_area_field_dim == 'acr') {
                    $meash = 'сот';
                } elseif ($lot_area_field_dim == 'sqm') {
                    $meash = 'кв.м';
                } elseif ($lot_area_field_dim == 'ha') {
                    $meash = 'га';
                }
            }

            if ($lot_area_field == '' && isset($this->form_data_shared['land_area'])) {
                $lot_area_field = 'land_area';
            } elseif ($lot_area_field == '' && isset($this->form_data_shared['lot_area'])) {
                $lot_area_field = 'lot_area';
            }

            if (isset($data_item[$lot_area_field])) {
                $x = preg_replace('/[^0-9.,]/', '', $data_item[$lot_area_field]);
                $x = str_replace(',', '.', $x);
                $x = floatval($x);
                //$x=preg_replace('/[^0-9\.,]/','',$data_item['lot_area']);
                if ($x != 0) {
                    $rs .= '<lot-area>' . "\n";
                    $rs .= '<value>' . $x . '</value>' . "\n";
                    $rs .= '<unit>' . $meash . '</unit>' . "\n";
                    $rs .= '</lot-area>' . "\n";

                    $has_lot = true;
                }
            }

            if($has_lot){

                $lottype = $this->exLotTypeLotType($data_item);
                if($lottype != ''){
                    $rs .= '<lot-type>' . $lottype . '</lot-type>';
                }
			}

        }

        return $rs;
    }

    protected function exLotTypeLotType($data_item){




        $lotType = false;
        if (!empty($this->lotTypeConditions)) {
            foreach ($this->lotTypeConditions as $lot_type_id => $lot_type_conditions) {
                $res_cond = false;
                foreach ($lot_type_conditions as $lot_type_conditions_variant) {
                    $res_variant = true;
                    foreach ($lot_type_conditions_variant as $lot_type_conditions_variant_item) {
                        if (isset($data_item[$lot_type_conditions_variant_item['f']]) && in_array($data_item[$lot_type_conditions_variant_item['f']], $lot_type_conditions_variant_item['v'])) {
                            $res_variant = $res_variant && true;
                        } else {
                            $res_variant = $res_variant && false;
                        }
                    }
                    $res_cond = $res_cond || $res_variant;
                }
                if ($res_cond) {
                    $lotType = $lot_type_id;
                    break;
                }
            }
        }
        if ($lotType) {
            switch ($lotType) {
                case 'lottype_izhs' : {
                    return 'ИЖС';
                }
                case 'lottype_farm' : {
                    return 'садоводство';
                }
            }
        }

        $lottype = '';
        $lot_type_field = trim($this->getConfigValue('apps.yandexrealty.lot_type_field'));
        if('' == $lot_type_field){
            $lot_type_field = 'lot_type';
        }

        if (isset($this->form_data_shared[$lot_type_field]) && isset($data_item[$lot_type_field])) {
            $mf = $this->form_data_shared[$lot_type_field];
            $di = $data_item[$lot_type_field];
            if ($mf['type'] == 'select_box' && intval($di) != 0 && isset($mf['select_data'][$di])) {
                $lottype = self::symbolsClear($mf['select_data'][$di]);
            } elseif ($mf['type'] != 'select_box' && $di != '') {
                $lottype = self::symbolsClear($di);
            }
        }
        return $lottype;
    }

    protected function exKitchenSpace($data_item) {
        $rs = '';
        $data_topic = (int) $data_item['topic_id'];
        
        $area_field = trim($this->getConfigValue('apps.yandexrealty.areakitchen_field'));
        if('' == $area_field){
            $area_field = 'square_kitchen';
        }

        $export_this = false;

        if ($this->export_mode == 'EST.UA') {
            if (!in_array($data_item['__est_ua_type'], $this->Types_Estua_Lots) && !in_array($data_item['__est_ua_type'], $this->Types_Estua_Commercial)) {
                $export_this = true;
            }
        } elseif ($this->export_mode == 'MEGET.UA') {
            if (!in_array($data_item['__meget_ua_type'], $this->Types_Megetua_Lots) && !in_array($data_item['__meget_ua_type'], $this->Types_Megetua_Commercial)) {
                $export_this = true;
            }
        } else {
            if (!in_array($this->associations[$data_topic]['realty_category'], array(4, 15, 16))) {
                $export_this = true;
            }
        }

        if (!$export_this) {
            return '';
        }

        $x = preg_replace('/[^0-9.,]/', '', $data_item[$area_field]);
        $x = str_replace(',', '.', $x);
        $x = floatval($x);
        if ($x != 0) {
            $rs .= '<kitchen-space>' . "\n";
            $rs .= '<value>' . $x . '</value>' . "\n";
            $rs .= '<unit>кв.м</unit>' . "\n";
            $rs .= '</kitchen-space>';
        }
        return $rs;
    }

    protected function exLivingSpace($data_item) {
        $rs = '';
        $data_topic = (int) $data_item['topic_id'];
        
        $area_field = trim($this->getConfigValue('apps.yandexrealty.arealive_field'));
        if('' == $area_field){
            $area_field = 'square_live';
        }

        $export_this = false;

        if ($this->export_mode == 'EST.UA') {
            if (!in_array($data_item['__est_ua_type'], $this->Types_Estua_Lots) && !in_array($data_item['__est_ua_type'], $this->Types_Estua_Commercial)) {
                $export_this = true;
            }
        } elseif ($this->export_mode == 'MEGET.UA') {
            if (!in_array($data_item['__meget_ua_type'], $this->Types_Megetua_Lots) && !in_array($data_item['__meget_ua_type'], $this->Types_Megetua_Commercial)) {
                $export_this = true;
            }
        } else {
            if (!in_array($this->associations[$data_topic]['realty_category'], array(4, 15, 16))) {
                $export_this = true;
            }
        }

        if (!$export_this) {
            return '';
        }

        $x = preg_replace('/[^0-9.,]/', '', $data_item[$area_field]);
        $x = str_replace(',', '.', $x);
        $x = floatval($x);
        if ($x != 0) {
            $rs .= '<living-space>' . "\n";
            $rs .= '<value>' . $x . '</value>' . "\n";
            $rs .= '<unit>кв.м</unit>' . "\n";
            $rs .= '</living-space>';
        }
        return $rs;
    }

    protected function exArea($data_item) {
        $rs = '';
        $data_topic = (int) $data_item['topic_id'];
        
        $area_field = trim($this->getConfigValue('apps.yandexrealty.area_field'));
        if('' == $area_field){
            $area_field = 'square_all';
        }
        
        if (!in_array($this->associations[$data_topic]['realty_category'], array(4, 15, 16))) {
            $x = preg_replace('/[^0-9.,]/', '', $data_item[$area_field]);
            $x = str_replace(',', '.', $x);
            $x = floatval($x);
            if ($x != 0) {
                $rs .= '<area>' . "\n";
                $rs .= '<value>' . $x . '</value>' . "\n";
                $rs .= '<unit>кв.м</unit>' . "\n";
                $rs .= '</area>';
            }
        }
        return $rs;
    }

    protected function exRenovationESTUA($data_item) {
        $renovation = false;
        if (!empty($this->renovationESTUATypesConditions)) {
            foreach ($this->renovationESTUATypesConditions as $renovation_type_id => $renovation_type_conditions) {
                $res_cond = false;
                foreach ($renovation_type_conditions as $renovation_type_conditions_variant) {
                    $res_variant = true;
                    foreach ($renovation_type_conditions_variant as $renovation_type_conditions_variant_item) {
                        if (isset($data_item[$renovation_type_conditions_variant_item['f']]) && in_array($data_item[$renovation_type_conditions_variant_item['f']], $renovation_type_conditions_variant_item['v'])) {
                            $res_variant = $res_variant && true;
                        } else {
                            $res_variant = $res_variant && false;
                        }
                    }
                    $res_cond = $res_cond || $res_variant;
                }
                if ($res_cond) {
                    $renovation = $renovation_type_id;
                    break;
                }
            }
        }
        if ($renovation) {
            switch ($renovation) {
                case 'notfinished' : {
                        return '<renovation>неоконченный ремонт</renovation>';
                    }
                case 'needcosm' : {
                        return '<renovation>требуется косметический ремонт</renovation>';
                    }
                case 'notbuild' : {
                        return '<renovation>незавершённое строительство</renovation>';
                    }
                case 'without' : {
                        return '<renovation>без ремонта</renovation>';
                    }
                case 'forfinishing' : {
                        return '<renovation>под чистовую отделку</renovation>';
                    }
                case 'needcapital' : {
                        return '<renovation>требуется капитальный ремонт</renovation>';
                    }
                case 'design' : {
                        return '<renovation>дизайнерский ремонт</renovation>';
                    }
                case 'euro' : {
                        return '<renovation>евроремонт</renovation>';
                    }
                case 'cosmetical' : {
                        return '<renovation>косметический ремонт</renovation>';
                    }
                case 'capital' : {
                        return '<renovation>капитальный ремонт</renovation>';
                    }
                case 'afterreconstr' : {
                        return '<renovation>после реконструкции</renovation>';
                    }
                case 'soviet' : {
                        return '<renovation>жилое/советское</renovation>';
                    }
            }
        }

        if (isset($this->form_data_shared['renovation']) && isset($data_item['renovation'])) {
            $mf = $this->form_data_shared['renovation'];
            $di = $data_item['renovation'];
            if ($mf['type'] == 'select_box' && intval($di) != 0 && isset($mf['select_data'][$di])) {
                return '<renovation>' . self::symbolsClear($mf['select_data'][$di]) . '</renovation>';
            } elseif ($mf['type'] != 'select_box' && $di != '') {
                return '<renovation>' . self::symbolsClear($di) . '</renovation>';
            }
        }
    }

    protected function exRenovation($data_item) {
        $renovation = false;
        if (!empty($this->renovationTypesConditions)) {
            foreach ($this->renovationTypesConditions as $renovation_type_id => $renovation_type_conditions) {
                $res_cond = false;
                foreach ($renovation_type_conditions as $renovation_type_conditions_variant) {
                    $res_variant = true;
                    foreach ($renovation_type_conditions_variant as $renovation_type_conditions_variant_item) {
                        if (isset($data_item[$renovation_type_conditions_variant_item['f']]) && in_array($data_item[$renovation_type_conditions_variant_item['f']], $renovation_type_conditions_variant_item['v'])) {
                            $res_variant = $res_variant && true;
                        } else {
                            $res_variant = $res_variant && false;
                        }
                    }
                    $res_cond = $res_cond || $res_variant;
                }
                if ($res_cond) {
                    $renovation = $renovation_type_id;
                    break;
                }
            }
        }
        if ($renovation) {
            switch ($renovation) {
                case 'design' : {
                        return '<renovation>дизайнерский</renovation>';
                    }
                case 'euro' : {
                        return '<renovation>евро</renovation>';
                    }
                case 'withdecor' : {
                        return '<renovation>с отделкой</renovation>';
                    }
                case 'reqrepair' : {
                        return '<renovation>требует ремонта</renovation>';
                    }
                case 'good' : {
                        return '<renovation>хороший</renovation>';
                    }
                case 'patialrep' : {
                        return '<renovation>частичный ремонт</renovation>';
                    }
                case 'roughing' : {
                        return '<renovation>черновая отделка</renovation>';
                    }
            }
        }

        if (isset($this->form_data_shared['renovation']) && isset($data_item['renovation'])) {
            $mf = $this->form_data_shared['renovation'];
            $di = $data_item['renovation'];
            if ($mf['type'] == 'select_box' && intval($di) != 0 && isset($mf['select_data'][$di])) {
                return '<renovation>' . self::symbolsClear($mf['select_data'][$di]) . '</renovation>';
            } elseif ($mf['type'] != 'select_box' && $di != '') {
                return '<renovation>' . self::symbolsClear($di) . '</renovation>';
            }
        }
    }

    protected function exQuality($data_item) {
        $quality = false;
        if (!empty($this->qualityTypesConditions)) {
            foreach ($this->qualityTypesConditions as $quality_type_id => $quality_type_conditions) {
                $res_cond = false;
                foreach ($quality_type_conditions as $quality_type_conditions_variant) {
                    $res_variant = true;
                    foreach ($quality_type_conditions_variant as $quality_type_conditions_variant_item) {
                        if (isset($data_item[$quality_type_conditions_variant_item['f']]) && in_array($data_item[$quality_type_conditions_variant_item['f']], $quality_type_conditions_variant_item['v'])) {
                            $res_variant = $res_variant && true;
                        } else {
                            $res_variant = $res_variant && false;
                        }
                    }
                    $res_cond = $res_cond || $res_variant;
                }
                if ($res_cond) {
                    $quality = $quality_type_id;
                    break;
                }
            }
        }
        if ($quality) {
            switch ($quality) {
                case 'best' : {
                        return '<quality>отличное</quality>';
                    }
                case 'good' : {
                        return '<quality>хорошее</quality>';
                    }
                case 'norm' : {
                        return '<quality>нормальное</quality>';
                    }
                case 'bad' : {
                        return '<quality>плохое</quality>';
                    }
            }
        }
    }

    protected function exPlanningImages($data_item) {

        $imgs = array();

        $images_field = trim($this->getConfigValue('apps.yandexrealty.planning_images'));
        if($images_field != ''){
            $images_field = explode(',', $images_field);
        }

        if(!empty($images_field)){
            foreach($images_field as $if){
                $if = trim($if);
                if($if != '' && isset($this->form_data_shared[$if]) && in_array($this->form_data_shared[$if]['type'], array('uploads')) && isset($data_item[$if]) && $data_item[$if] != '') {
                    $imgs = array_merge($imgs, unserialize($data_item[$if]));
                }
            }
        }

        return $imgs;
    }

    protected function exVideoReview($data_item) {

        $ret = '';

        $videolink = $this->exYoutubeVideoReviewUrl($data_item);
        $onlineshow = $this->exOnlineShow($data_item);

        if($videolink != '' || $onlineshow){
            $ret .= '<video-review>';
            if($videolink != ''){
                $ret .= '<youtube-video-review-url>'.$videolink.'</youtube-video-review-url>';
            }
            if($onlineshow){
                $ret .= '<online-show>1</online-show>';
            }
            $ret .= '</video-review>';
        }

        return $ret;

    }

    protected function exOnlineShow($data_item) {

        /*$videolink = '';

        $video_field = trim($this->getConfigValue('apps.yandexrealty.video_field'));

        if(isset($data_item[$video_field]) && $data_item[$video_field] != ''){
            $videolink = 'https://youtu.be/'.$data_item[$video_field];
        }*/

        return false;

    }

    protected function exYoutubeVideoReviewUrl($data_item) {

        $videolink = '';

        $video_field = trim($this->getConfigValue('apps.yandexrealty.video_field'));

        if(isset($data_item[$video_field]) && $data_item[$video_field] != ''){
            $videolink = 'https://youtu.be/'.$data_item[$video_field];
        }

        return $videolink;

    }

    protected function exImages($data_item, $hasUploadify, $uploadsField) {
        $image_fields = '';
        $imgs = array();

        $pimages = $this->exPlanningImages($data_item);

        if(!empty($pimages)){
            $imgs = array_merge($imgs, $pimages);
        }

        if ($this->getConfigValue('apps.yandexrealty.export_image_cache')) {
            $imgs = unserialize($data_item['image_cache']);
            foreach ($imgs as $v) {
                $rs .= '<image>' . $v . '</image>' . "\n";
            }
            return $rs;
        }
        if ($hasUploadify) {
            $imgids = array();

            $DBC = DBC::getInstance();
            $query = 'SELECT image_id FROM ' . DB_PREFIX . '_data_image WHERE id=' . $data_item['id'];
            $stmt = $DBC->query($query, array($data_item['id']));
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $imgids[] = $ar['image_id'];
                }
            }

            if (count($imgids) > 0) {
                $query = 'SELECT normal, preview FROM ' . DB_PREFIX . '_image WHERE image_id IN (' . implode(',', $imgids) . ')';
                $stmt = $DBC->query($query);
                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        $imgs[] = $ar;
                    }
                }
            }
        } elseif ($uploadsField !== false/* && isset($data_item[$uploadsField]) && $data_item[$uploadsField] != ''*/) {
            if(is_array($uploadsField)){
                foreach($uploadsField as $uplf){
                    if(isset($data_item[$uplf]) && $data_item[$uplf] != ''){
                        $imgs = array_merge($imgs, unserialize($data_item[$uplf]));
                    }
                }
            }else{
                if(isset($data_item[$uploadsField]) && $data_item[$uploadsField] != ''){
                    $imgs = array_merge($imgs, unserialize($data_item[$uploadsField]));
                }
            }

        }

        $rs = '';
        if (is_array($imgs) && count($imgs) > 0) {

            if (1 == (int) $this->getConfigValue('apps.yandexrealty.nowatermark_export') && 1 == (int) $this->getConfigValue('save_without_watermark')) {
                $image_dest = $this->getServerFullUrl() . '/img/data/nowatermark/';
            } else {
                $image_dest = $this->getServerFullUrl() . '/img/data/';
            }

            foreach ($imgs as $v) {
                if ($this->export_mode == 'ETOWN') {
                    $rs .= '<imagefile>' . "\n";
                    $rs .= '<image>' . (isset($v['remote']) && $v['remote'] === 'true' ? '' : $image_dest) . $v['preview'] . '</image>' . "\n";
                    $rs .= '<image>' . (isset($v['remote']) && $v['remote'] === 'true' ? '' : $image_dest) . $v['normal'] . '</image>' . "\n";
                    $rs .= '</imagefile>' . "\n";
                } else {
                    $rs .= '<image>' . (isset($v['remote']) && $v['remote'] === 'true' ? '' : $image_dest) . $v['normal'] . '</image>' . "\n";
                }
            }
        }
        return $rs;
    }

    protected function exDescription($data_item) {
        
        $from = trim($this->getConfigValue('apps.yandexrealty.descriptionfrom'));
        if($from == ''){
            $from = 'text';
        }
        
        $text = '';
        
        if (isset($this->form_data_shared[$from]) && isset($data_item[$from]) && $data_item[$from] != '') {
            $text = $data_item[$from];
        }
        
        //$text = $data_item['text'];
        //var_dump($text);
        $text = SiteBill::iconv(SITE_ENCODING, 'utf-8', $text);

        $text = htmlspecialchars_decode($text);
        $text = strip_tags($text);
        $text = preg_replace('/[[:cntrl:]]/i', '', $text);
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        return '<description>' . $text . '</description>';
    }

    protected function exWithChildren($data_item) {
        if (isset($this->form_data_shared['with_children']) && isset($data_item['with_children'])) {
            if ((int) $data_item['with_children'] == 1) {
                return '<with-children>1</with-children>';
            } else {
                return '<with-children>0</with-children>';
            }
        }
    }

    protected function exWithPets($data_item) {
        if (isset($this->form_data_shared['with_pets']) && isset($data_item['with_pets'])) {
            if ((int) $data_item['with_pets'] == 1) {
                return '<with-pets>1</with-pets>';
            } else {
                return '<with-pets>0</with-pets>';
            }
        }
    }

    protected function exAgentFee($data_item) {
        $rs = '';

        $data_topic = (int) $data_item['topic_id'];
        $this_realty_supertype = intval($this->associations[$data_topic]['realty_type']);
        if ($this_realty_supertype != self::$EXP_TY_COMMERCIAL) {
            $f = trim($this->getConfigValue('apps.yandexrealty.agentfee_from'));
            if($f == ''){
               $f = 'agent_fee';
            }

            $value = '';
            if (isset($this->form_data_shared[$f]) && isset($data_item[$f]) && trim($data_item[$f]) != '' && is_numeric($data_item[$f]) && floatval($data_item[$f]) <= 100) {
                $value = floatval($data_item[$f]);
            }

            if ($value != '') {
                $rs .= '<agent-fee>' . $value . '</agent-fee>';
            }
            return $rs;
        }

    }

    protected function exDealStatus($data_item) {

        $data_item = $this->hook_data($data_item);


        $detected_deal_status = false;

        if($data_item['__property_type'] == 'commercial' && $data_item['__operational_type'] == 'rent'){
            $s = intval($this->getConfigValue('apps.yandexrealty.dealstatus_comrentdefault'));
            if($s == 1){
                $detected_deal_status = 'directrent';
            }elseif($s == 2){
                $detected_deal_status = 'subrent';
            }elseif($s == 3){
                $detected_deal_status = 'saleofleaserights';
            }
        }elseif($data_item['__property_type'] == 'living' && $data_item['__operational_type'] == 'sale' && $data_item['__new_flat'] == 1){
            $s = intval($this->getConfigValue('apps.yandexrealty.dealstatus_salenewdefault'));
            if($s == 1){
                $detected_deal_status = 'n_primarysale';
            }elseif($s == 2){
                $detected_deal_status = 'n_sale';
            }elseif($s == 3){
                $detected_deal_status = 'n_reassignment';
            }
        }elseif($data_item['__property_type'] == 'living' && $data_item['__operational_type'] == 'sale' && $data_item['__new_flat'] == 0){
            $s = intval($this->getConfigValue('apps.yandexrealty.dealstatus_sale'));
            if($s == 1){
                $detected_deal_status = 's_sale';
            }elseif($s == 2){
                $detected_deal_status = 's_primarysaleofsecondary';
            }elseif($s == 3){
                $detected_deal_status = 's_countersale';
            }
        }

        $deal_conditions = array();

        $living_sale = array('s_sale', 's_primarysaleofsecondary', 's_countersale');
        $living_sale_new = array('n_primarysale', 'n_sale', 'n_reassignment');
        $commercial_rent = array('directrent', 'subrent', 'saleofleaserights');

        if($data_item['__property_type'] == 'commercial' && $data_item['__operational_type'] == 'rent'){
            $deal_conditions = $commercial_rent;
        }elseif($data_item['__property_type'] == 'living' && $data_item['__operational_type'] == 'sale' && $data_item['__new_flat'] == 1){
            $deal_conditions = $living_sale_new;
        }elseif($data_item['__property_type'] == 'living' && $data_item['__operational_type'] == 'sale' && $data_item['__new_flat'] == 0){
            $deal_conditions = $living_sale;
        }


        if (!$detected_deal_status && !empty($deal_conditions) && !empty($this->dealStatusConditions)) {

            foreach ($deal_conditions as $quality_type_id) {
                if(isset($this->dealStatusConditions[$quality_type_id])){
                    $quality_type_conditions = $this->dealStatusConditions[$quality_type_id];
                    $res_cond = false;
                    foreach ($quality_type_conditions as $quality_type_conditions_variant) {
                        $res_variant = true;
                        foreach ($quality_type_conditions_variant as $quality_type_conditions_variant_item) {
                            if (isset($data_item[$quality_type_conditions_variant_item['f']]) && in_array($data_item[$quality_type_conditions_variant_item['f']], $quality_type_conditions_variant_item['v'])) {
                                $res_variant = $res_variant && true;
                            } else {
                                $res_variant = $res_variant && false;
                            }
                        }
                        $res_cond = $res_cond || $res_variant;
                    }
                    if ($res_cond) {
                        $detected_deal_status = $quality_type_id;
                        break;
                    }
                }
            }

        }

        if ($detected_deal_status) {
            switch ($detected_deal_status) {
                case 's_sale' : {
                    return '<deal-status>прямая продажа</deal-status>';
                }
                case 's_primarysaleofsecondary' : {
                    return '<deal-status>первичная продажа вторички</deal-status>';
                }
                case 's_countersale' : {
                    return '<deal-status>встречная продажа</deal-status>';
                }
                case 'n_primarysale' : {
                    return '<deal-status>первичная продажа</deal-status>';
                }
                case 'n_sale' : {
                    return '<deal-status>прямая продажа</deal-status>';
                }
                case 'n_reassignment' : {
                    return '<deal-status>переуступка</deal-status>';
                }
                case 'directrent' : {
                    return '<deal-status>direct rent</deal-status>';
                }
                case 'subrent' : {
                    return '<deal-status>subrent</deal-status>';
                }
                case 'saleofleaserights' : {
                    return '<deal-status>sale of lease rights</deal-status>';
                }
            }
        }else{
            if (isset($this->form_data_shared['deal_status']) && isset($data_item['deal_status']) && $data_item['deal_status'] != '') {
                if ($this->form_data_shared['deal_status']['type'] == 'safe_string') {
                    return '<deal-status>' . trim($data_item['deal_status']) . '</deal-status>' . "\n";
                } elseif ($this->form_data_shared['deal_status']['type'] == 'select_box' && $data_item['deal_status'] != '0' && isset($this->form_data_shared['deal_status']['select_data'][$data_item['deal_status']])) {
                    return '<deal-status>' . $this->form_data_shared['deal_status']['select_data'][$data_item['deal_status']] . '</deal-status>' . "\n";
                }
                //return '<deal-status>'.trim($data_item['deal_status']).'</deal-status>';
            } else {
                //TODO: Make this error more softly
                //$this->errors[]=$data_item['id'].' DECLINED: Deal status unknown';
            }
        }















    }

    protected function exRentPflege($data_item) {
        if (isset($this->form_data_shared['rent_pledge']) && isset($data_item['rent_pledge'])) {
            if ((int) $data_item['rent_pledge'] == 1) {
                return '<rent-pledge>1</rent-pledge>';
            } else {
                return '<rent-pledge>0</rent-pledge>';
            }
        }
    }

    protected function exPrepayment($data_item) {
        if (isset($this->form_data_shared['prepayment']) && isset($data_item['prepayment']) && (int) $data_item['prepayment'] != 0) {
            return '<prepayment>' . (int) $data_item['prepayment'] . '</prepayment>';
        }
    }

    protected function exMortgage($data_item) {
        if (isset($this->form_data_shared['mortgage']) && isset($data_item['mortgage'])) {
            if ((int) $data_item['mortgage'] == 1) {
                return '<mortgage>1</mortgage>';
            } else {
                return '<mortgage>0</mortgage>';
            }
        }
    }

    protected function exHaggle($data_item) {
        if (isset($this->form_data_shared['haggle']) && isset($data_item['haggle'])) {
            if ((int) $data_item['haggle'] == 1) {
                return '<haggle>1</haggle>';
            } else {
                return '<haggle>0</haggle>';
            }
        }
    }

    protected function exNotForAgents($data_item) {
        if (isset($this->form_data_shared['not_for_agents']) && isset($data_item['not_for_agents'])) {
            if ((int) $data_item['not_for_agents'] == 1) {
                return '<not-for-agents>1</not-for-agents>';
            } else {
                return '<not-for-agents>0</not-for-agents>';
            }
        }
    }

    protected function exCadastralNumber($data_item) {
        $rs = '';
        $cd_nr_from = trim($this->getConfigValue('apps.yandexrealty.cadastralnr_from'));
        if ($cd_nr_from != '' && isset($this->form_data_shared[$cd_nr_from]) && isset($data_item[$cd_nr_from]) && $data_item[$cd_nr_from] != '') {
            $rs .= '<cadastral-number>' . self::symbolsClear($data_item[$cd_nr_from]) . '</cadastral-number>';
        }
        return $rs;
    }

    protected function exPricePeriod($data_item, $operational_type) {
        $rs = '';
        if (isset($this->form_data_shared['period']) && $this->form_data_shared['period']['type'] == 'select_box') {
            if ($data_item['period'] != '' && $data_item['period'] != '0' && isset($this->form_data_shared['period']['select_data'][$data_item['period']])) {
                $rs .= '<period>' . self::symbolsClear($this->form_data_shared['period']['select_data'][$data_item['period']]) . '</period>' . "\n";
            }
        } elseif (isset($this->form_data_shared['period'])) {
            if ($data_item['period'] != '') {
                $rs .= '<period>' . self::symbolsClear($data_item['period']) . '</period>' . "\n";
            }
        } else {
            $rs .= '<period>месяц</period>' . "\n";
        }
        return $rs;
    }

    protected function exPriceUnit($data_item, $operational_type) {
        $rs = '';
        if (isset($this->form_data_shared['unit']) && isset($data_item['unit']) && $data_item['unit'] != '') {
            $rs .= '<unit>' . self::symbolsClear($data_item['unit']) . '</unit>' . "\n";
        }
        return $rs;
    }

    protected function exPrice($data_item, $operational_type) {
        
        $from = trim($this->getConfigValue('apps.yandexrealty.pricefrom'));
        if($from == ''){
            $from = 'price';
        }
        if('' == self::symbolsClear($data_item[$from])){
            $this->errors[]=$data_item['id'].' DECLINED: Price error';
            return '';
        }
        
        $rs = '<price>' . "\n";
        $rs .= '<value>' . self::symbolsClear($data_item[$from]) . '</value>' . "\n";
        if ('' != $this->getConfigValue('apps.yandexrealty.global_currency_code')) {
            $currency = trim($this->getConfigValue('apps.yandexrealty.global_currency_code'));
        } elseif (isset($this->form_data_shared['currency_id']) && isset($data_item['currency_id']) && intval($data_item['currency_id']) != 0) {
            $currency = $this->checkCurrencyCode($data_item['currency_id']);
        } else {
            $currency = $this->currency;
        }

        $rs .= '<currency>' . $currency . '</currency>' . "\n";

        if ($data_item['__operational_type'] == 'rent') {
            $rs .= $this->exPricePeriod($data_item, $operational_type);
            $rs .= $this->exTaxationForm($data_item);
        }

        $rs .= $this->exPriceUnit($data_item, $operational_type);

        $rs .= '</price>';
        return $rs;
    }

    protected function exCommission($data_item) {
        $rs = '';
        $data_topic = (int) $data_item['topic_id'];
        $this_realty_supertype = intval($this->associations[$data_topic]['realty_type']);
        if ($this_realty_supertype != self::$EXP_TY_COMMERCIAL) {
            return $rs;
        }

        $f = trim($this->getConfigValue('apps.yandexrealty.comission_from'));
        $value = '';
        if (isset($this->form_data_shared[$f]) && isset($data_item[$f]) && trim($data_item[$f]) != '' && is_numeric($data_item[$f]) && floatval($data_item[$f]) <= 100) {
            $value = floatval($data_item[$f]);
        }

        if ($value != '') {
            $rs .= '<commission>' . $value . '</commission>';
        }
        return $rs;
    }

    protected function exSalesAgent($data_item) {

        if ($this->contacts_export_mode == 1) {
            $rs = '<sales-agent>' . "\n";
            $uid = intval($data_item['user_id']);
            if (!isset($this->users_cache[$uid])) {
                $UM = new Users_Manager();
                $this->users_cache[$uid] = $UM->getUserProfileData($uid);
            }
            $user = $this->users_cache[$uid];
            $gid = intval($user['group_id']);

            $user = $this->hook_data($user);


            $contact_export_variant = 0;

            if (count($this->contacts_mode) == 1 && isset($this->contacts_mode['*'])) {
                $contact_export_variant = $this->contacts_mode['*'];
            } elseif (isset($this->contacts_mode[$gid])) {
                $contact_export_variant = $this->contacts_mode[$gid];
            } elseif (isset($this->contacts_mode['*'])) {
                $contact_export_variant = $this->contacts_mode['*'];
            }

            if (count($this->group_assoc) == 1 && isset($this->group_assoc['*'])) {
                $exporter_type = $this->group_assoc['*'];
            } elseif (isset($this->group_assoc[$gid])) {
                $exporter_type = $this->group_assoc[$gid];
            } elseif (isset($this->group_assoc['*'])) {
                $exporter_type = $this->group_assoc['*'];
            }

            $org_name = '';
            if ('' != trim($this->getConfigValue('apps.yandexrealty.organisation_global_name'))) {
                $org_name = self::symbolsClear(trim($this->getConfigValue('apps.yandexrealty.organisation_global_name')));
            }
            if ($org_name == '') {
                $f = trim($this->getConfigValue('apps.yandexrealty.organisation_src'));
                if (isset($user[$f]) && $user[$f] != '') {
                    $org_name = self::symbolsClear($user[$f]);
                }
            }





            if ($exporter_type == 'a') {
                $rs .= '<category>agency</category>' . "\n";
                if ('' != $org_name) {
                    $rs .= '<organization>' . $org_name . '</organization>';
                }
            } elseif ($exporter_type == 'd') {
                $rs .= '<category>developer</category>' . "\n";
                if ('' != $org_name) {
                    $rs .= '<organization>' . $org_name . '</organization>';
                }
            } else {
                $rs .= '<category>owner</category>' . "\n";
            }

            //$rs.='<gid>'.$contact_export_variant.'</gid>'."\n";

            if ($contact_export_variant == 1) {
                $field_f = trim($this->getConfigValue('apps.yandexrealty.data_phone'));
                if ($field_f != '' && isset($data_item[$field_f]) && $data_item[$field_f] != '') {
                    $rs .= '<phone>' . self::symbolsClear($data_item[$field_f]) . '</phone>' . "\n";
                }
                $field_f = trim($this->getConfigValue('apps.yandexrealty.data_email'));
                if ($field_f != '' && isset($data_item[$field_f]) && $data_item[$field_f] != '') {
                    $rs .= '<email>' . self::symbolsClear($data_item[$field_f]) . '</email>' . "\n";
                }
                $field_f = trim($this->getConfigValue('apps.yandexrealty.data_name'));
                if ($field_f != '' && isset($data_item[$field_f]) && $data_item[$field_f] != '') {
                    $rs .= '<name>' . self::symbolsClear($data_item[$field_f]) . '</name>' . "\n";
                }
            } elseif ($contact_export_variant == 2) {
                $field_f = trim($this->getConfigValue('apps.yandexrealty.profile_phone'));
                if ($field_f != '' && isset($user[$field_f]) && $user[$field_f] != '') {
                    $rs .= '<phone>' . self::symbolsClear($user[$field_f]) . '</phone>' . "\n";
                }
                $field_f = trim($this->getConfigValue('apps.yandexrealty.profile_email'));
                if ($field_f != '' && isset($user[$field_f]) && $user[$field_f] != '') {
                    $rs .= '<email>' . self::symbolsClear($user[$field_f]) . '</email>' . "\n";
                }
                $field_f = trim($this->getConfigValue('apps.yandexrealty.profile_name'));
                if ($field_f != '' && isset($user[$field_f]) && $user[$field_f] != '') {
                    $rs .= '<name>' . self::symbolsClear($user[$field_f]) . '</name>' . "\n";
                }
            } elseif ($contact_export_variant == 3) {
                $field_f1 = trim($this->getConfigValue('apps.yandexrealty.data_phone'));
                $field_f2 = trim($this->getConfigValue('apps.yandexrealty.profile_phone'));
                if (isset($data_item[$field_f1]) && $data_item[$field_f1] != '') {
                    $rs .= '<phone>' . self::symbolsClear($data_item[$field_f1]) . '</phone>' . "\n";
                } elseif (isset($user[$field_f2]) && $user[$field_f2] != '') {
                    $rs .= '<phone>' . self::symbolsClear($user[$field_f12]) . '</phone>' . "\n";
                }

                $field_f1 = trim($this->getConfigValue('apps.yandexrealty.data_email'));
                $field_f2 = trim($this->getConfigValue('apps.yandexrealty.profile_email'));
                if (isset($data_item[$field_f1]) && $data_item[$field_f1] != '') {
                    $rs .= '<email>' . self::symbolsClear($data_item[$field_f1]) . '</email>' . "\n";
                } elseif (isset($user[$field_f2]) && $user[$field_f2] != '') {
                    $rs .= '<email>' . self::symbolsClear($user[$field_f12]) . '</email>' . "\n";
                }

                $field_f1 = trim($this->getConfigValue('apps.yandexrealty.data_name'));
                $field_f2 = trim($this->getConfigValue('apps.yandexrealty.profile_name'));
                if (isset($data_item[$field_f1]) && $data_item[$field_f1] != '') {
                    $rs .= '<name>' . self::symbolsClear($data_item[$field_f1]) . '</name>' . "\n";
                } elseif (isset($user[$field_f2]) && $user[$field_f2] != '') {
                    $rs .= '<name>' . self::symbolsClear($user[$field_f12]) . '</name>' . "\n";
                }
            } elseif ($contact_export_variant == 4) {
                $field_f1 = trim($this->getConfigValue('apps.yandexrealty.data_phone'));
                $field_f2 = trim($this->getConfigValue('apps.yandexrealty.profile_phone'));
                if (isset($user[$field_f2]) && $user[$field_f2] != '') {
                    $rs .= '<phone>' . self::symbolsClear($user[$field_f12]) . '</phone>' . "\n";
                } elseif (isset($data_item[$field_f1]) && $data_item[$field_f1] != '') {
                    $rs .= '<phone>' . self::symbolsClear($data_item[$field_f1]) . '</phone>' . "\n";
                }

                $field_f1 = trim($this->getConfigValue('apps.yandexrealty.data_email'));
                $field_f2 = trim($this->getConfigValue('apps.yandexrealty.profile_email'));
                if (isset($user[$field_f2]) && $user[$field_f2] != '') {
                    $rs .= '<email>' . self::symbolsClear($user[$field_f12]) . '</email>' . "\n";
                } elseif (isset($data_item[$field_f1]) && $data_item[$field_f1] != '') {
                    $rs .= '<email>' . self::symbolsClear($data_item[$field_f1]) . '</email>' . "\n";
                }

                $field_f1 = trim($this->getConfigValue('apps.yandexrealty.data_name'));
                $field_f2 = trim($this->getConfigValue('apps.yandexrealty.profile_name'));
                if (isset($user[$field_f2]) && $user[$field_f2] != '') {
                    $rs .= '<name>' . self::symbolsClear($user[$field_f12]) . '</name>' . "\n";
                } elseif (isset($data_item[$field_f1]) && $data_item[$field_f1] != '') {
                    $rs .= '<name>' . self::symbolsClear($data_item[$field_f1]) . '</name>' . "\n";
                }
            }
            $rs .= '</sales-agent>';
        } else {
            $user_id = (int) $data_item['user_id'];
            $rs = '<sales-agent>' . "\n";
            if ($data_item['fio'] != '' && $user_id == $this->getUnregisteredUserId()) {
                $rs .= '<category>owner</category>' . "\n";
                $rs .= '<phone>' . self::symbolsClear($data_item['phone']) . '</phone>' . "\n";
                $rs .= '<email>' . self::symbolsClear($data_item['email']) . '</email>' . "\n";
                $rs .= '<name>' . self::symbolsClear($data_item['fio']) . '</name>' . "\n";
            } else {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/users/users_manager.php';
                $UM = new Users_Manager();
                $user = $UM->getUserProfileData($user_id);

                if ($this->getConfigValue('apps.company.enable') == 1) {
                    if ($user['company_id'] != 0) {
                        require_once SITEBILL_DOCUMENT_ROOT . '/apps/company/admin/admin.php';
                        $CA = new company_admin();
                        $company = $CA->load_by_id($user['company_id']);

                        $rs .= '<phone>' . self::symbolsClear($db->row['agency_agentphone']) . '</phone>' . "\n";
                        $rs .= '<organization>' . self::symbolsClear($company['name']['value']) . '</organization>' . "\n";
                        $rs .= '<category>agency</category>' . "\n";
                        $rs .= '<url>' . self::symbolsClear($company['site']['value']) . '</url>' . "\n";
                        $rs .= '<email>' . self::symbolsClear($company['email']['value']) . '</email>' . "\n";
                        $rs .= '<name>' . self::symbolsClear($company['name']['value']) . '</name>' . "\n";
                        $rs .= '<phone>' . self::symbolsClear($company['phone1']['value']) . '</phone>' . "\n";
                    } else {
                        $rs .= '<category>owner</category>' . "\n";
                        $rs .= '<phone>' . self::symbolsClear($user['phone']) . '</phone>' . "\n";
                        $rs .= '<email>' . self::symbolsClear($user['email']) . '</email>' . "\n";
                        $rs .= '<name>' . self::symbolsClear($user['fio']) . '</name>' . "\n";
                    }
                } else {
                    $rs .= '<category>owner</category>' . "\n";
                    $rs .= '<phone>' . self::symbolsClear($user['phone']) . '</phone>' . "\n";
                    $rs .= '<email>' . self::symbolsClear($user['email']) . '</email>' . "\n";
                    $rs .= '<name>' . self::symbolsClear($user['fio']) . '</name>' . "\n";
                }
            }

            if (isset($this->form_data_shared['partner']) && isset($data_item['partner']) && $data_item['partner'] != '') {
                $rs .= '<partner>' . self::symbolsClear($data_item['partner']) . '</partner>' . "\n";
            }
            $rs .= '</sales-agent>';
        }

        return $rs;
    }

    /**
     * TODO
     * Вариант функции exSalesAgent с заменой номеров
     * проанализировать варианты исползования
     */
    protected function exSalesAgent_Variant($data_item) {
        
        $default_phones_array = array();
            
        $defphonesstr = trim($this->getConfigValue('apps.yandexrealty.defaultphones'));
        if($defphonesstr != ''){
            $defphones = explode("\n", $defphonesstr);
            if(!empty($defphones)){
                foreach ($defphones as $defphone){
                    $defphone = trim($defphone);
                    if(strlen($defphone) > 5){
                        $default_phones_array[] = $defphone;
                    }
                }
            }
        }

        if ($this->contacts_export_mode == 1) {
            $rs = '<sales-agent>' . "\n";
            $uid = intval($data_item['user_id']);
            if (!isset($this->users_cache[$uid])) {
                $UM = new Users_Manager();
                $this->users_cache[$uid] = $UM->getUserProfileData($uid);
            }
            $user = $this->users_cache[$uid];
            $gid = intval($user['group_id']);

            $user = $this->hook_data($user);


            $contact_export_variant = 0;

            if (count($this->contacts_mode) == 1 && isset($this->contacts_mode['*'])) {
                $contact_export_variant = $this->contacts_mode['*'];
            } elseif (isset($this->contacts_mode[$gid])) {
                $contact_export_variant = $this->contacts_mode[$gid];
            } elseif (isset($this->contacts_mode['*'])) {
                $contact_export_variant = $this->contacts_mode['*'];
            }

            if (count($this->group_assoc) == 1 && isset($this->group_assoc['*'])) {
                $exporter_type = $this->group_assoc['*'];
            } elseif (isset($this->group_assoc[$gid])) {
                $exporter_type = $this->group_assoc[$gid];
            } elseif (isset($this->group_assoc['*'])) {
                $exporter_type = $this->group_assoc['*'];
            }

            $org_name = '';
            if ('' != trim($this->getConfigValue('apps.yandexrealty.organisation_global_name'))) {
                $org_name = self::symbolsClear(trim($this->getConfigValue('apps.yandexrealty.organisation_global_name')));
            }
            if ($org_name == '') {
                $f = trim($this->getConfigValue('apps.yandexrealty.organisation_src'));
                if (isset($user[$f]) && $user[$f] != '') {
                    $org_name = self::symbolsClear($user[$f]);
                }
            }

            if ($exporter_type == 'a') {
                $rs .= '<category>agency</category>' . "\n";
                if ('' != $org_name) {
                    $rs .= '<organization>' . $org_name . '</organization>';
                }
            } elseif ($exporter_type == 'd') {
                $rs .= '<category>developer</category>' . "\n";
                if ('' != $org_name) {
                    $rs .= '<organization>' . $org_name . '</organization>';
                }
            } else {
                $rs .= '<category>owner</category>' . "\n";
            }

            //$rs.='<gid>'.$contact_export_variant.'</gid>'."\n";
            
            

            if ($contact_export_variant == 1) {
                if(!empty($default_phones_array)){
                    foreach ($default_phones_array as $default_phone){
                        $rs .= '<phone>' . self::symbolsClear($default_phone) . '</phone>' . "\n";
                    }
                }else{
                    $field_f = trim($this->getConfigValue('apps.yandexrealty.data_phone'));
                    if ($field_f != '' && isset($data_item[$field_f]) && $data_item[$field_f] != '') {
                        $rs .= '<phone>' . self::symbolsClear($data_item[$field_f]) . '</phone>' . "\n";
                    }
                }
                
                $field_f = trim($this->getConfigValue('apps.yandexrealty.data_email'));
                if ($field_f != '' && isset($data_item[$field_f]) && $data_item[$field_f] != '') {
                    $rs .= '<email>' . self::symbolsClear($data_item[$field_f]) . '</email>' . "\n";
                }
                $field_f = trim($this->getConfigValue('apps.yandexrealty.data_name'));
                if ($field_f != '' && isset($data_item[$field_f]) && $data_item[$field_f] != '') {
                    $rs .= '<name>' . self::symbolsClear($data_item[$field_f]) . '</name>' . "\n";
                }
            } elseif ($contact_export_variant == 2) {
                if(!empty($default_phones_array)){
                    foreach ($default_phones_array as $default_phone){
                        $rs .= '<phone>' . self::symbolsClear($default_phone) . '</phone>' . "\n";
                    }
                }else{
                    $field_f = trim($this->getConfigValue('apps.yandexrealty.profile_phone'));
                    if ($field_f != '' && isset($user[$field_f]) && $user[$field_f] != '') {
                        $rs .= '<phone>' . self::symbolsClear($user[$field_f]) . '</phone>' . "\n";
                    }
                }
                
                $field_f = trim($this->getConfigValue('apps.yandexrealty.profile_email'));
                if ($field_f != '' && isset($user[$field_f]) && $user[$field_f] != '') {
                    $rs .= '<email>' . self::symbolsClear($user[$field_f]) . '</email>' . "\n";
                }
                $field_f = trim($this->getConfigValue('apps.yandexrealty.profile_name'));
                if ($field_f != '' && isset($user[$field_f]) && $user[$field_f] != '') {
                    $rs .= '<name>' . self::symbolsClear($user[$field_f]) . '</name>' . "\n";
                }
            } elseif ($contact_export_variant == 3) {
                if(!empty($default_phones_array)){
                    foreach ($default_phones_array as $default_phone){
                        $rs .= '<phone>' . self::symbolsClear($default_phone) . '</phone>' . "\n";
                    }
                }else{
                    $field_f1 = trim($this->getConfigValue('apps.yandexrealty.data_phone'));
                    $field_f2 = trim($this->getConfigValue('apps.yandexrealty.profile_phone'));
                    if (isset($data_item[$field_f1]) && $data_item[$field_f1] != '') {
                        $rs .= '<phone>' . self::symbolsClear($data_item[$field_f1]) . '</phone>' . "\n";
                    } elseif (isset($user[$field_f2]) && $user[$field_f2] != '') {
                        $rs .= '<phone>' . self::symbolsClear($user[$field_f12]) . '</phone>' . "\n";
                    }
                }
                

                $field_f1 = trim($this->getConfigValue('apps.yandexrealty.data_email'));
                $field_f2 = trim($this->getConfigValue('apps.yandexrealty.profile_email'));
                if (isset($data_item[$field_f1]) && $data_item[$field_f1] != '') {
                    $rs .= '<email>' . self::symbolsClear($data_item[$field_f1]) . '</email>' . "\n";
                } elseif (isset($user[$field_f2]) && $user[$field_f2] != '') {
                    $rs .= '<email>' . self::symbolsClear($user[$field_f12]) . '</email>' . "\n";
                }

                $field_f1 = trim($this->getConfigValue('apps.yandexrealty.data_name'));
                $field_f2 = trim($this->getConfigValue('apps.yandexrealty.profile_name'));
                if (isset($data_item[$field_f1]) && $data_item[$field_f1] != '') {
                    $rs .= '<name>' . self::symbolsClear($data_item[$field_f1]) . '</name>' . "\n";
                } elseif (isset($user[$field_f2]) && $user[$field_f2] != '') {
                    $rs .= '<name>' . self::symbolsClear($user[$field_f12]) . '</name>' . "\n";
                }
            } elseif ($contact_export_variant == 4) {
                if(!empty($default_phones_array)){
                    foreach ($default_phones_array as $default_phone){
                        $rs .= '<phone>' . self::symbolsClear($default_phone) . '</phone>' . "\n";
                    }
                }else{
                    $field_f1 = trim($this->getConfigValue('apps.yandexrealty.data_phone'));
                    $field_f2 = trim($this->getConfigValue('apps.yandexrealty.profile_phone'));
                    if (isset($user[$field_f2]) && $user[$field_f2] != '') {
                        $rs .= '<phone>' . self::symbolsClear($user[$field_f12]) . '</phone>' . "\n";
                    } elseif (isset($data_item[$field_f1]) && $data_item[$field_f1] != '') {
                        $rs .= '<phone>' . self::symbolsClear($data_item[$field_f1]) . '</phone>' . "\n";
                    }
                }
                

                $field_f1 = trim($this->getConfigValue('apps.yandexrealty.data_email'));
                $field_f2 = trim($this->getConfigValue('apps.yandexrealty.profile_email'));
                if (isset($user[$field_f2]) && $user[$field_f2] != '') {
                    $rs .= '<email>' . self::symbolsClear($user[$field_f12]) . '</email>' . "\n";
                } elseif (isset($data_item[$field_f1]) && $data_item[$field_f1] != '') {
                    $rs .= '<email>' . self::symbolsClear($data_item[$field_f1]) . '</email>' . "\n";
                }

                $field_f1 = trim($this->getConfigValue('apps.yandexrealty.data_name'));
                $field_f2 = trim($this->getConfigValue('apps.yandexrealty.profile_name'));
                if (isset($user[$field_f2]) && $user[$field_f2] != '') {
                    $rs .= '<name>' . self::symbolsClear($user[$field_f12]) . '</name>' . "\n";
                } elseif (isset($data_item[$field_f1]) && $data_item[$field_f1] != '') {
                    $rs .= '<name>' . self::symbolsClear($data_item[$field_f1]) . '</name>' . "\n";
                }
            }
            $rs .= '</sales-agent>';
        } else {
            $user_id = (int) $data_item['user_id'];
            $rs = '<sales-agent>' . "\n";
            if ($data_item['fio'] != '' && $user_id == $this->getUnregisteredUserId()) {
                $rs .= '<category>owner</category>' . "\n";
                if(!empty($default_phones_array)){
                    foreach ($default_phones_array as $default_phone){
                        $rs .= '<phone>' . self::symbolsClear($default_phone) . '</phone>' . "\n";
                    }
                }else{
                    $rs .= '<phone>' . self::symbolsClear($data_item['phone']) . '</phone>' . "\n";
                }
                
                $rs .= '<email>' . self::symbolsClear($data_item['email']) . '</email>' . "\n";
                $rs .= '<name>' . self::symbolsClear($data_item['fio']) . '</name>' . "\n";
            } else {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/users/users_manager.php';
                $UM = new Users_Manager();
                $user = $UM->getUserProfileData($user_id);

                if ($this->getConfigValue('apps.company.enable') == 1) {
                    if ($user['company_id'] != 0) {
                        require_once SITEBILL_DOCUMENT_ROOT . '/apps/company/admin/admin.php';
                        $CA = new company_admin();
                        $company = $CA->load_by_id($user['company_id']);
                        
                        if(!empty($default_phones_array)){
                            foreach ($default_phones_array as $default_phone){
                                $rs .= '<phone>' . self::symbolsClear($default_phone) . '</phone>' . "\n";
                            }
                        }else{
                            $rs .= '<phone>' . self::symbolsClear($db->row['agency_agentphone']) . '</phone>' . "\n";
                        }

                        
                        $rs .= '<organization>' . self::symbolsClear($company['name']['value']) . '</organization>' . "\n";
                        $rs .= '<category>agency</category>' . "\n";
                        $rs .= '<url>' . self::symbolsClear($company['site']['value']) . '</url>' . "\n";
                        $rs .= '<email>' . self::symbolsClear($company['email']['value']) . '</email>' . "\n";
                        $rs .= '<name>' . self::symbolsClear($company['name']['value']) . '</name>' . "\n";
                        $rs .= '<phone>' . self::symbolsClear($company['phone1']['value']) . '</phone>' . "\n";
                    } else {
                        $rs .= '<category>owner</category>' . "\n";
                        if(!empty($default_phones_array)){
                            foreach ($default_phones_array as $default_phone){
                                $rs .= '<phone>' . self::symbolsClear($default_phone) . '</phone>' . "\n";
                            }
                        }else{
                            $rs .= '<phone>' . self::symbolsClear($user['phone']) . '</phone>' . "\n";
                        }
                        
                        $rs .= '<email>' . self::symbolsClear($user['email']) . '</email>' . "\n";
                        $rs .= '<name>' . self::symbolsClear($user['fio']) . '</name>' . "\n";
                    }
                } else {
                    $rs .= '<category>owner</category>' . "\n";
                    if(!empty($default_phones_array)){
                        foreach ($default_phones_array as $default_phone){
                            $rs .= '<phone>' . self::symbolsClear($default_phone) . '</phone>' . "\n";
                        }
                    }else{
                        $rs .= '<phone>' . self::symbolsClear($user['phone']) . '</phone>' . "\n";
                    }
                    
                    $rs .= '<email>' . self::symbolsClear($user['email']) . '</email>' . "\n";
                    $rs .= '<name>' . self::symbolsClear($user['fio']) . '</name>' . "\n";
                }
            }

            if (isset($this->form_data_shared['partner']) && isset($data_item['partner']) && $data_item['partner'] != '') {
                $rs .= '<partner>' . self::symbolsClear($data_item['partner']) . '</partner>' . "\n";
            }
            $rs .= '</sales-agent>';
        }

        return $rs;
    }

    protected function exLocation($data_item) {

        $data_topic = intval($data_item['topic_id']);
        $this_realty_supertype = intval($this->associations[$data_topic]['realty_type']);

        $rs = '<location>' . "\n";

        $country = trim($this->getConfigValue('apps.yandexrealty.country_global'));
        if ($country == '') {
            if ('' != trim($this->getConfigValue('apps.yandexrealty.country_from'))) {
                $country_from = trim($this->getConfigValue('apps.yandexrealty.country_from'));
            } else {
                $country_from = '';
            }

            if ($country_from != '' && isset($data_item[$country_from])) {
                $country = $data_item[$country_from];
            } else {
                $country = $data_item['country'];
            }
        }

        if ($country == '') {
            $this->errors[] = $data_item['id'] . ' DECLINED: Country unknown';
        } else {
            $rs .= '<country>' . self::symbolsClear($country) . '</country>' . "\n";
        }




        $region = trim($this->getConfigValue('apps.yandexrealty.region_global'));
        if ($region == '') {
            if ('' != trim($this->getConfigValue('apps.yandexrealty.region_from'))) {
                $region_from = trim($this->getConfigValue('apps.yandexrealty.region_from'));
            } else {
                $region_from = '';
            }

            if ($region_from != '' && isset($data_item[$region_from])) {
                $region = $data_item[$region_from];
            } else {
                $region = $data_item['region'];
            }
        }

        if ($region != '') {
            $rs .= '<region>' . self::symbolsClear($region) . '</region>' . "\n";
        }


        /*if ('' != trim($this->getConfigValue('apps.yandexrealty.district_from'))) {
            $district_from = trim($this->getConfigValue('apps.yandexrealty.district_from'));
            $manual_district_value = self::symbolsClear($data_item[$district_from]);
            if ($manual_district_value != '') {
                $rs .= '<district>' . self::symbolsClear($manual_district_value) . '</district>' . "\n";
            }
        }*/

        $district_from_field = trim($this->getConfigValue('apps.yandexrealty.district_from'));
        if ($district_from_field != '') {
            if (isset($this->form_data_shared[$district_from_field]) && $this->form_data_shared[$district_from_field]['type'] == 'select_by_query' && isset($data_item['_' . $district_from_field]) && $data_item['_' . $district_from_field] != '') {
                $rs .= '<district>' . self::symbolsClear($data_item['_' . $district_from_field]) . '</district>' . "\n";
            } elseif (isset($this->form_data_shared[$district_from_field]) && isset($data_item[$district_from_field]) && $data_item[$district_from_field] != '' && $data_item[$district_from_field] != '0') {
                $rs .= '<district>' . self::symbolsClear($data_item[$district_from_field]) . '</district>' . "\n";
            }
        }



        $city = trim($this->getConfigValue('apps.yandexrealty.city_global'));
        if ($city == '') {
            if ('' != trim($this->getConfigValue('apps.yandexrealty.city_from'))) {
                $city_from = trim($this->getConfigValue('apps.yandexrealty.city_from'));
            } else {
                $city_from = '';
            }

            if ($city_from != '' && isset($data_item[$city_from])) {
                $city = $data_item[$city_from];
            } else {
                $city = $data_item['city'];
            }
        }

        if ($city != '') {
            $rs .= '<locality-name>' . self::symbolsClear($city) . '</locality-name>' . "\n";
        }else{
            $this->errors[]=$data_item['id'].' DECLINED: locality-name not setted';
            return '';
        }


        if ($district_from_field !=='district_id' && $data_item['district'] != '') {
            $rs .= '<sub-locality-name>' . self::symbolsClear($data_item['district']) . '</sub-locality-name>' . "\n";
        }

        if ('' != trim($this->getConfigValue('apps.yandexrealty.street_from'))) {
            $street_from = trim($this->getConfigValue('apps.yandexrealty.street_from'));
        } else {
            $street_from = '';
        }

        if ($street_from != '' && isset($data_item[$street_from])) {
            $street = $data_item[$street_from];
        } else {
            $street = $data_item['street'];
        }

        if ($street == '' and isset($data_item['address']) and $data_item['address'] != '') {
            $street = $data_item['address'];
        }


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

        if ($street != '') {
            $rs .= '<address>';
            $rs .= $street;
            $disable_house_number = (int)$this->getConfigValue('apps.yandexrealty.disable_house_number');
            if ($data_item['number'] != '' and !$disable_house_number) {
                $rs .= ', ' . self::symbolsClear($data_item['number']);
            }
            $rs .= '</address>' . "\n";
        }


        if (($this->export_mode == 'EST.UA' && in_array($data_item['__est_ua_type'], $this->Types_Estua_Residential)) || ($this->export_mode == 'MEGET.UA' && in_array($data_item['__meget_ua_type'], $this->Types_Megetua_Residential)) || $this_realty_supertype == self::$EXP_TY_RESIDENTIAL) {
            $apt_nr_from = trim($this->getConfigValue('apps.yandexrealty.aptnr_from'));
            if ($apt_nr_from != '' && isset($this->form_data_shared[$apt_nr_from]) && isset($data_item[$apt_nr_from]) && $data_item[$apt_nr_from] != '') {
                $rs .= '<apartment>' . self::symbolsClear($data_item[$apt_nr_from]) . '</apartment>';
            }
        }



        if ($data_item['metro'] != '' && $data_item['metro'] != '0') {
            $rs .= '<metro>' . "\n";
            $rs .= '<name>' . self::symbolsClear($data_item['metro']) . '</name>' . "\n";
            if (isset($data_item['time_on_transport']) && (int) $data_item['time_on_transport'] != 0) {
                $rs .= '<time-on-transport>' . (int) $data_item['time_on_transport'] . '</time-on-transport>' . "\n";
            }
            if (isset($data_item['time_on_foot']) && (int) $data_item['time_on_foot'] != 0) {
                $rs .= '<time-on-foot>' . (int) $data_item['time_on_foot'] . '</time-on-foot>' . "\n";
            }
            $rs .= '</metro>' . "\n";
        }

        if (isset($this->form_data_shared['railway_station']) && isset($data_item['railway_station']) && $data_item['railway_station'] != '') {
            $rs .= '<railway-station>' . self::symbolsClear($data_item['railway_station']) . '</railway-station>' . "\n";
        }

        $direction_field = trim($this->getConfigValue('apps.yandexrealty.direction_from'));
        if ($direction_field != '') {
            if (isset($this->form_data_shared[$direction_field]) && $this->form_data_shared[$direction_field]['type'] == 'select_by_query' && isset($data_item['_' . $direction_field]) && $data_item['_' . $direction_field] != '') {
                $rs .= '<direction>' . self::symbolsClear($data_item['_' . $direction_field]) . '</direction>' . "\n";
            } elseif (isset($this->form_data_shared[$direction_field]) && isset($data_item[$direction_field]) && $data_item[$direction_field] != '') {
                $rs .= '<direction>' . self::symbolsClear($data_item[$direction_field]) . '</direction>' . "\n";
            }
        } else {
            if (isset($this->form_data_shared['direction']) && isset($data_item['direction']) && $data_item['direction'] != '') {
                $rs .= '<direction>' . self::symbolsClear($data_item['direction']) . '</direction>' . "\n";
            }
        }


        if (isset($this->form_data_shared['distance']) && isset($data_item['distance']) && (int) $data_item['distance'] != '') {
            $rs .= '<distance>' . $data_item['distance'] . '</distance>' . "\n";
        }

        if (isset($this->form_data_shared['geo']) && isset($data_item['geo_lat']) && $data_item['geo_lat'] != '' && isset($data_item['geo_lng']) && $data_item['geo_lng'] != '') {
            $rs .= '<latitude>' . $data_item['geo_lat'] . '</latitude>' . "\n";
            $rs .= '<longitude>' . $data_item['geo_lng'] . '</longitude>' . "\n";
        }

        $rs .= '</location>';
        return $rs;
    }

    protected function exManuallyAdded($data_item) {
        if (isset($this->form_data_shared['manually_added']) && isset($data_item['manually_added'])) {
            if ((int) $data_item['manually_added'] == 1) {
                return '<manually-added>1</manually-added>' . "\n";
            } else {
                return '<manually-added>0</manually-added>' . "\n";
            }
        }
        return '';
    }

    protected function exPayedAdv($data_item) {
        if (isset($this->form_data_shared['payed_adv']) && isset($data_item['payed_adv'])) {
            if ((int) $data_item['payed_adv'] == 1) {
                return '<payed-adv>1</payed-adv>';
            } else {
                return '<payed-adv>0</payed-adv>';
            }
        }
        return '';
    }

    protected function exExpireDate($data_item) {
        if (isset($this->form_data_shared['expire_date']) && isset($data_item['expire_date']) && $data_item['expire_date'] != '' && $data_item['expire_date'] != '0000-00-00 00:00:00') {
            return '<expire-date>' . $this->formdate(strtotime($data_item['expire_date'])) . '</expire-date>';
        }
    }

    protected function exLastUpdateDate($data_item) {
        $luf = trim($this->getConfigValue('apps.yandexrealty.last_upd_field'));
        if (isset($this->form_data_shared[$luf]) && isset($data_item[$luf])) {
            if($this->form_data_shared[$luf]['type'] == 'date' && $data_item[$luf] != '' && $data_item[$luf] != 0){
                return '<last-update-date>' . $this->formdate($data_item[$luf]) . '</last-update-date>';
            }elseif($this->form_data_shared[$luf]['type'] == 'dtdatetime' && $data_item[$luf] != '' && $data_item[$luf] != '0000-00-00 00:00:00'){
                return '<last-update-date>' . $this->formdate(strtotime($data_item[$luf])) . '</last-update-date>';
            }
        }


        $date_timestamp = strtotime($data_item['date_added']);

        if($data_item['__operational_type'] == 'rent'){
            $critical_term = $this->critical_term_rent;
        }else{
            $critical_term = $this->critical_term;
        }

        if ((time() - $date_timestamp) > ($critical_term * 24 * 3600)) {
            return '<last-update-date>' . $this->formdate(time() - (rand($this->min_normal_term, $this->max_normal_term) * 24 * 3600)) . '</last-update-date>';
        } else {
            return '<last-update-date>' . $this->formdate($date_timestamp) . '</last-update-date>';
        }
    }

    protected function exCreationDate($data_item) {
        $date_timestamp = strtotime($data_item['date_added']);
        return '<creation-date>' . $this->formdate($date_timestamp) . '</creation-date>';
    }

    protected function exURL($data_item) {
        $data_topic = (int) $data_item['topic_id'];
        $href = $this->getRealtyHREF($data_item['id'], true, array('topic_id' => $data_item['topic_id'], 'alias' => $data_item['translit_alias']));
        $rs = '<url>' . $href . '</url>';
        return $rs;
    }

    protected function exCategory($data_item) {
        $data_topic = (int) $data_item['topic_id'];
        $this_realty_supertype = intval($this->associations[$data_topic]['realty_type']);
        $rs = '';
        if ($this->export_mode == 'ETOWN') {
            $rs .= '<category>' . self::symbolsClear($this->catalogChains[$data_topic]) . '</category>';
        } elseif ($this->export_mode == 'EST.UA') {
            $name = $this->getEstuaCategoryName($data_item['__est_ua_type']);
            if ($name !== '') {
                $rs .= '<category>' . $name . '</category>';
            } else {
                $this->errors[] = $data_item['id'] . ' DECLINED: EST.UA category unknown';
            }
        } elseif ($this->export_mode == 'MEGET.UA') {
            if (in_array($data_item['__meget_ua_type'], $this->Types_Megetua_Lots)) {
                $rs .= '<category>участок</category>';
            } elseif (in_array($data_item['__meget_ua_type'], $this->Types_Megetua_Houses) && $data_item['__operational_type'] == 'rent') {
                $rs .= '<category>Дома долгосрочно</category>';
            } else {
                $name = $this->getMegetuaCategoryName($data_item['__meget_ua_type']);
                if ($name !== '') {
                    $rs .= '<category>' . $name . '</category>';
                } else {
                    $this->errors[] = $data_item['id'] . ' DECLINED: MEGET.UA category unknown';
                }
            }


            /* if($this_realty_supertype==self::$EXP_TY_COMMERCIAL){
              $rs.='<category>коммерческая</category>';
              } */
        }/* elseif($this_realty_supertype==self::$EXP_TY_RESIDENTIAL){
          if(!empty($this->associations) && isset($this->associations[$data_topic]) && $this->associations[$data_topic]['realty_category']!=0 && isset($this->realty_categories[$this->associations[$data_topic]['realty_category']])){
          $rs.='<category>'.$this->realty_categories[$this->associations[$data_topic]['realty_category']].'</category>';
          }else{
          $this->errors[]=$data_item['id'].' DECLINED: Residential category unknown';
          }
          }elseif($this_realty_supertype==self::$EXP_TY_COMMERCIAL){
          if($this->export_mode=='MEGET.UA'){
          $rs.='<category>коммерческая</category>';
          }else{
          $rs.='<category>коммерческая</category>';
          }

          } */ else {
            if ($this_realty_supertype == self::$EXP_TY_RESIDENTIAL) {
                if (!empty($this->associations) && isset($this->associations[$data_topic]) && $this->associations[$data_topic]['realty_category'] != 0 && isset($this->realty_categories[$this->associations[$data_topic]['realty_category']])) {
                    $rs .= '<category>' . $this->realty_categories[$this->associations[$data_topic]['realty_category']] . '</category>';
                } else {
                    $this->errors[] = $data_item['id'] . ' DECLINED: Residential category unknown';
                }
            } elseif ($this_realty_supertype == self::$EXP_TY_COMMERCIAL) {
                $rs .= '<category>коммерческая</category>';
            }
        }

        /* elseif(!empty($this->associations) && isset($this->associations[$data_topic]) && $this->associations[$data_topic]['realty_category']!=0){
          $rs.='<category>'.$this->realty_categories[$this->associations[$data_topic]['realty_category']].'</category>';
          }else{
          $rs.='<category>'.self::symbolsClear($data_item['topic']).'</category>';
          } */
        return $rs;
    }

    protected function exGarageType($data_item) {
        $data_topic = intval($data_item['topic_id']);
        $ret = '';
        if ($this->associations[$data_topic]['realty_category'] == self::$EXP_T_GARAGE) {

            $garage_category = false;
            if (!empty($this->garage_types)) {
                foreach ($this->garage_types as $renovation_type_id => $renovation_type_conditions) {
                    $res_cond = false;
                    foreach ($renovation_type_conditions as $renovation_type_conditions_variant) {
                        $res_variant = true;
                        foreach ($renovation_type_conditions_variant as $renovation_type_conditions_variant_item) {
                            if (isset($data_item[$renovation_type_conditions_variant_item['f']]) && in_array($data_item[$renovation_type_conditions_variant_item['f']], $renovation_type_conditions_variant_item['v'])) {
                                $res_variant = $res_variant && true;
                            } else {
                                $res_variant = $res_variant && false;
                            }
                        }
                        $res_cond = $res_cond || $res_variant;
                    }
                    if ($res_cond) {
                        $garage_category = $renovation_type_id;
                        break;
                    }
                }
            }

            if ($garage_category) {
                switch ($garage_category) {
                    case 'ga' : {
                            $ret = '<garage-type>гараж</garage-type>';
                            break;
                        }
                    case 'pp' : {
                            $ret = '<garage-type>машиноместо</garage-type>';
                            break;
                        }
                    case 'bx' : {
                            $ret = '<garage-type>бокс</garage-type>';
                            break;
                        }
                }
            }

            if ($ret == '') {
                $this->errors[] = $data_item['id'] . ' DECLINED: Garage type unknown';
            }
        }



        return $ret;
    }

    protected function exCommercialType($data_item) {

        $data_topic = (int) $data_item['topic_id'];
        $this_realty_supertype = intval($this->associations[$data_topic]['realty_type']);
        $rs = '';
        if ($this->export_mode == 'MEGET.UA') {

        } else {
            if ($this_realty_supertype == self::$EXP_TY_COMMERCIAL) {
                $category = 0;
                if (!empty($this->associations) && isset($this->associations[$data_topic]) && $this->associations[$data_topic]['realty_category'] != 0 && isset($this->commercial_names[$this->associations[$data_topic]['realty_category']])) {
                    $category = $this->associations[$data_topic]['realty_category'];
                    //$rs.='<commercial-type>'.$this->commercial_names[$this->associations[$data_topic]['realty_category']].'</commercial-type>'."\n";
                }

                //var_dump();
                //$rs.='<commercial-type1>'.$category.'</commercial-type1>';
                if ($category == 0) {

                    //$this->errors[]=$data_item['id'].' DECLINED: Commercial type unknown';
                    if (!empty($this->commTypesConditions)) {
                        foreach ($this->commTypesConditions as $comm_type_id => $comm_type_conditions) {
                            //var_dump($comm_type_conditions);
                            $res_cond = false;
                            foreach ($comm_type_conditions as $comm_type_conditions_variant) {

                                $res_variant = true;
                                foreach ($comm_type_conditions_variant as $comm_type_conditions_variant_item) {
                                    //var_dump($comm_type_conditions_variant_item);
                                    //var_dump('Compare value '.$data_item[$comm_type_conditions_variant_item['f']].' from field '.$comm_type_conditions_variant_item['f'].' with values '.print_r($comm_type_conditions_variant_item['v'], true));
                                    if (isset($data_item[$comm_type_conditions_variant_item['f']]) && in_array($data_item[$comm_type_conditions_variant_item['f']], $comm_type_conditions_variant_item['v'])) {
                                        $res_variant = $res_variant && true;
                                    } else {
                                        $res_variant = $res_variant && false;
                                    }
                                    //var_dump($res_variant);
                                }
                                $res_cond = $res_cond || $res_variant;
                            }
                            if ($res_cond && isset($this->commercial_names[$comm_type_id])) {
                                //echo '=='.$category.'==';
                                $category = $comm_type_id;
                                break;
                            }
                        }
                    }
                }

                if ($category != 0) {
                    $rs .= '<commercial-type>' . $this->commercial_names[$category] . '</commercial-type>';
                } else {
                    $this->errors[] = $data_item['id'] . ' DECLINED: Commercial type unknown';
                }


                /* if(!empty($this->associations) && isset($this->associations[$data_topic]) && $this->associations[$data_topic]['realty_category']!=0 && isset($this->commercial_names[$this->associations[$data_topic]['realty_category']])){
                  $rs.='<commercial-type>'.$this->commercial_names[$this->associations[$data_topic]['realty_category']].'</commercial-type>'."\n";
                  }else{
                  $this->errors[]=$data_item['id'].' DECLINED: Commercial type unknown';
                  } */
            }
        }

        return $rs;
    }

    protected function exCleaningIncluded($data_item) {
        $data_topic = (int) $data_item['topic_id'];
        $this_realty_supertype = intval($this->associations[$data_topic]['realty_type']);
        $rs = '';

        if (($this->export_mode == 'MEGET.UA' && in_array($data_item['__meget_ua_type'], $this->Types_Megetua_Commercial)) || ($this->export_mode == 'EST.UA' && in_array($data_item['__est_ua_type'], $this->Types_Estua_Commercial)) || ($this->export_mode != 'MEGET.UA' && $this->export_mode != 'EST.UA' && $this_realty_supertype == self::$EXP_TY_COMMERCIAL)) {

            $res_cond = false;
            if (!empty($this->specialCommercialOptionsConditions['rent_cleaning_yes'])) {

                foreach ($this->specialCommercialOptionsConditions['rent_cleaning_yes'] as $quality_type_conditions_line) {
                    $res_variant = true;
                    foreach ($quality_type_conditions_line as $quality_type_conditions_variant_item) {
                        if (isset($data_item[$quality_type_conditions_variant_item['f']]) && in_array($data_item[$quality_type_conditions_variant_item['f']], $quality_type_conditions_variant_item['v'])) {
                            $res_variant = $res_variant && true;
                        } else {
                            $res_variant = $res_variant && false;
                        }
                    }
                    $res_cond = $res_cond || $res_variant;
                    if ($res_cond) {
                        //$quality = $quality_type_id;
                        break;
                    }
                }
            }
            if ($res_cond) {
                return '<cleaning-included>1</cleaning-included>';
            }
        }
        return $rs;
    }

    protected function exUtilitiesIncluded($data_item) {
        $data_topic = (int) $data_item['topic_id'];
        $this_realty_supertype = intval($this->associations[$data_topic]['realty_type']);
        $rs = '';

        if (($this->export_mode == 'MEGET.UA' && in_array($data_item['__meget_ua_type'], $this->Types_Megetua_Commercial)) || ($this->export_mode == 'EST.UA' && in_array($data_item['__est_ua_type'], $this->Types_Estua_Commercial)) || ($this->export_mode != 'MEGET.UA' && $this->export_mode != 'EST.UA' && $this_realty_supertype == self::$EXP_TY_COMMERCIAL)) {

            $res_cond = false;
            if (!empty($this->specialCommercialOptionsConditions['rent_utilities_yes'])) {
                foreach ($this->specialCommercialOptionsConditions['rent_utilities_yes'] as $quality_type_conditions_line) {
                    $res_variant = true;
                    foreach ($quality_type_conditions_line as $quality_type_conditions_variant_item) {
                        if (isset($data_item[$quality_type_conditions_variant_item['f']]) && in_array($data_item[$quality_type_conditions_variant_item['f']], $quality_type_conditions_variant_item['v'])) {
                            $res_variant = $res_variant && true;
                        } else {
                            $res_variant = $res_variant && false;
                        }
                    }
                    $res_cond = $res_cond || $res_variant;
                    if ($res_cond) {
                        //$quality = $quality_type_id;
                        break;
                    }
                }
            }
            if ($res_cond) {
                return '<utilities-included>1</utilities-included>';
            }
        }
        return $rs;
    }

    protected function exElectricityIncluded($data_item) {
        $data_topic = (int) $data_item['topic_id'];
        $this_realty_supertype = intval($this->associations[$data_topic]['realty_type']);
        $rs = '';

        if (($this->export_mode == 'MEGET.UA' && in_array($data_item['__meget_ua_type'], $this->Types_Megetua_Commercial)) || ($this->export_mode == 'EST.UA' && in_array($data_item['__est_ua_type'], $this->Types_Estua_Commercial)) || ($this->export_mode != 'MEGET.UA' && $this->export_mode != 'EST.UA' && $this_realty_supertype == self::$EXP_TY_COMMERCIAL)) {

            $res_cond = false;
            if (!empty($this->specialCommercialOptionsConditions['rent_electricity_yes'])) {
                foreach ($this->specialCommercialOptionsConditions['rent_electricity_yes'] as $quality_type_conditions_line) {
                    $res_variant = true;
                    foreach ($quality_type_conditions_line as $quality_type_conditions_variant_item) {
                        if (isset($data_item[$quality_type_conditions_variant_item['f']]) && in_array($data_item[$quality_type_conditions_variant_item['f']], $quality_type_conditions_variant_item['v'])) {
                            $res_variant = $res_variant && true;
                        } else {
                            $res_variant = $res_variant && false;
                        }
                    }
                    $res_cond = $res_cond || $res_variant;
                    if ($res_cond) {
                        //$quality = $quality_type_id;
                        break;
                    }
                }
            }
            if ($res_cond) {
                return '<electricity-included>1</electricity-included>';
            }
        }
        return $rs;
    }

    protected function exTaxationForm($data_item) {
        $data_topic = (int) $data_item['topic_id'];
        $this_realty_supertype = intval($this->associations[$data_topic]['realty_type']);
        $rs = '';

        if (($this->export_mode == 'MEGET.UA' && in_array($data_item['__meget_ua_type'], $this->Types_Megetua_Commercial)) || ($this->export_mode == 'EST.UA' && in_array($data_item['__est_ua_type'], $this->Types_Estua_Commercial)) || ($this->export_mode != 'MEGET.UA' && $this->export_mode != 'EST.UA' && $this_realty_supertype == self::$EXP_TY_COMMERCIAL)) {

            $quality = false;
            if (!empty($this->taxationTypesConditions)) {
                foreach ($this->taxationTypesConditions as $quality_type_id => $quality_type_conditions) {
                    $res_cond = false;
                    foreach ($quality_type_conditions as $quality_type_conditions_variant) {
                        $res_variant = true;
                        foreach ($quality_type_conditions_variant as $quality_type_conditions_variant_item) {
                            if (isset($data_item[$quality_type_conditions_variant_item['f']]) && in_array($data_item[$quality_type_conditions_variant_item['f']], $quality_type_conditions_variant_item['v'])) {
                                $res_variant = $res_variant && true;
                            } else {
                                $res_variant = $res_variant && false;
                            }
                        }
                        $res_cond = $res_cond || $res_variant;
                    }
                    if ($res_cond) {
                        $quality = $quality_type_id;
                        break;
                    }
                }
            }
            if ($quality) {
                switch ($quality) {
                    case 'nds' : {
                            return '<taxation-form>НДС</taxation-form>';
                        }
                    case 'usn' : {
                            return '<taxation-form>УСН</taxation-form>';
                        }
                }
            }
        }
        return $rs;
    }

    protected function exCommercialBuildingType($data_item) {
        $data_topic = (int) $data_item['topic_id'];
        $this_realty_supertype = intval($this->associations[$data_topic]['realty_type']);
        $rs = '';

        if (($this->export_mode == 'MEGET.UA' && in_array($data_item['__meget_ua_type'], $this->Types_Megetua_Commercial)) || ($this->export_mode == 'EST.UA' && in_array($data_item['__est_ua_type'], $this->Types_Estua_Commercial)) || ($this->export_mode != 'MEGET.UA' && $this->export_mode != 'EST.UA' && $this_realty_supertype == self::$EXP_TY_COMMERCIAL)) {
            if (!empty($this->comm_building_types)) {
                foreach ($this->comm_building_types as $kct => $vct) {
                    if (isset($data_item[$vct[0]]) && in_array($data_item[$vct[0]], $vct[1])) {
                        if ($kct == 'bc') {
                            $rs .= '<commercial-building-type>business center</commercial-building-type>' . "\n";
                        } elseif ($kct == 'db') {
                            $rs .= '<commercial-building-type>detached building</commercial-building-type>' . "\n";
                        } elseif ($kct == 'rb') {
                            $rs .= '<commercial-building-type>residential building</commercial-building-type>' . "\n";
                        } elseif ($kct == 'sc') {
                            $rs .= '<commercial-building-type>shopping center</commercial-building-type>' . "\n";
                        } elseif ($kct == 'wh') {
                            $rs .= '<commercial-building-type>warehouse</commercial-building-type>' . "\n";
                        }
                        break;
                    }
                }
            }
        }
        return $rs;
    }

    protected function exType($data_item, &$operational_type) {
        $data_topic = (int) $data_item['topic_id'];
        $rs = '';

        if ($data_item['__operational_type'] == 'sale') {
            $rs .= '<type>продажа</type>';
            $operational_type = 'sale';
        } elseif ($data_item['__operational_type'] == 'rent') {
            $operational_type = 'rent';
            if ($this->export_mode == 'EST.UA') {
                $rs .= '<type>сдача</type>';
            } else {
                $rs .= '<type>аренда</type>';
            }
        } else {
            $this->errors[] = $data_item['id'] . ' DECLINED: Operational type unknown';
        }

        return $rs;

        if (!empty($this->associations) && isset($this->associations[$data_topic]) && $this->associations[$data_topic]['operation_type'] != 0) {
            $rs .= '<type>' . $this->op_types[$this->associations[$data_topic]['operation_type']] . '</type>';
            if ($this->associations[$data_topic]['operation_type'] == 2) {
                $operational_type = 'rent';
            } else {
                $operational_type = 'sale';
            }
        } else {
            if (isset($this->contracts['sale']) && $this->contracts['sale']['f'] != '' && isset($data_item[$this->contracts['sale']['f']]) && in_array($data_item[$this->contracts['sale']['f']], $this->contracts['sale']['v'])) {
                $rs .= '<type>продажа</type>';
                $operational_type = 'sale';
            } elseif (isset($this->contracts['rent']) && $this->contracts['rent']['f'] != '' && isset($data_item[$this->contracts['rent']['f']]) && in_array($data_item[$this->contracts['rent']['f']], $this->contracts['rent']['v'])) {
                if ($this->export_mode == 'EST.UA') {
                    $rs .= '<type>сдача</type>';
                } else {
                    $rs .= '<type>аренда</type>';
                }

                $operational_type = 'rent';
            }/* elseif(isset($data_item['optype']) && (int)$data_item['optype']==1){
              $rs.='<type>аренда</type>';
              $operational_type='rent';
              } */ else {
                $this->errors[] = $data_item['id'] . ' DECLINED: Operational type unknown';
                $rs .= '<type>продажа</type>';
            }
        }


        /* $operational_type='sale';
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
          } */
        return $rs;
    }

    protected function exPropertyType($data_item) {
        $data_topic = (int) $data_item['topic_id'];
        $this_realty_supertype = intval($this->associations[$data_topic]['realty_type']);
        $rs = '';


        if ($this->export_mode == 'EST.UA') {
            if (in_array($data_item['__est_ua_type'], $this->Types_Estua_Commercial)) {
                $rs .= '<property-type>коммерческая</property-type>';
            } elseif (in_array($data_item['__est_ua_type'], $this->Types_Estua_Residential)) {
                $rs .= '<property-type>жилая</property-type>';
            } elseif (in_array($data_item['__est_ua_type'], $this->Types_Estua_Lots)) {
                $rs .= '<property-type>земельные участки</property-type>';
            } else {
                $this->errors[] = $data_item['id'] . ' DECLINED: EST.UA property-type unknown';
            }
        } elseif ($this->export_mode == 'MEGET.UA') {
            if (in_array($data_item['__meget_ua_type'], $this->Types_Megetua_Commercial)) {
                $rs .= '<property-type>коммерческая</property-type>';
            } elseif (in_array($data_item['__meget_ua_type'], $this->Types_Megetua_Residential) || in_array($data_item['__meget_ua_type'], $this->Types_Megetua_Lots)) {
                $rs .= '<property-type>жилая</property-type>';
            } else {
                $this->errors[] = $data_item['id'] . ' DECLINED: MEGET.UA property-type unknown';
            }
            /* if($this_realty_supertype==self::$EXP_TY_COMMERCIAL){
              $rs.='<category>коммерческая</category>';
              } */
        } else {
            if ($this_realty_supertype == self::$EXP_TY_RESIDENTIAL) {
                $rs .= '<property-type>жилая</property-type>' . "\n";
            } elseif ($this_realty_supertype == self::$EXP_TY_COMMERCIAL) {

            } elseif (isset($data_item['property_type']) && $data_item['property_type'] != '') {
                $rs .= '<property-type>' . self::symbolsClear($data_item['property_type']) . '</property-type>' . "\n";
            } else {
                if (in_array($associations[$data_topic]['realty_category'], array(self::$EXP_T_LOT, self::$EXP_T_LOT_2, self::$EXP_T_LOT_3))) {

                } else {
                    $this->errors[] = $data_item['id'] . ' DECLINED: property-type unknown';
                }
            }
        }


        /* if($this_realty_supertype==self::$EXP_TY_RESIDENTIAL){
          $rs.='<property-type>жилая</property-type>'."\n";
          }elseif($this_realty_supertype==self::$EXP_TY_COMMERCIAL){
          if($this->export_mode=='MEGET.UA'){
          $rs.='<property-type>коммерческая</property-type>';
          }elseif($this->export_mode=='EST.UA'){
          $rs.='<property-type>коммерческая</property-type>';
          }
          //$rs.='<category>коммерческая</category>'."\n";
          }elseif(isset($data_item['property_type']) && $data_item['property_type']!=''){
          $rs.='<property-type>'.self::symbolsClear($data_item['property_type']).'</property-type>'."\n";
          }else{
          $this->errors[]=$data_item['id'].' DECLINED: Supertype unknown';
          } */
        /* if(!empty($this->associations) && isset($this->associations[$data_topic]) && $this->associations[$data_topic]['realty_type']!=0){
          $rs.='<property-type>'.$this->realty_types[$this->associations[$data_topic]['realty_type']].'</property-type>';
          }elseif(isset($data_item['property_type']) && $data_item['property_type']!=''){
          $rs.='<property-type>'.self::symbolsClear($data_item['property_type']).'</property-type>';
          }else{
          $rs.='<property-type>жилая</property-type>';
          } */
        return $rs;
    }

    protected function exInternalId($data_item) {
        return '<offer internal-id="' . (int) $data_item['id'] . '">';
    }

    protected function setExportType() {
        $this->export_type = mb_strtolower($this->getRequestValue('type'), SITE_ENCODING);
    }

    protected function remove_old_file($cachefile) {
        if ( !file_exists($cachefile) ) {
            return false;
        }
        if ( !$this->is_good_file($cachefile) ) {
            return unlink($cachefile);
        }
        if (1 == $this->getConfigValue('apps.yandexrealty.tofile') && file_exists($cachefile)) {
            if ((time() - filemtime($cachefile) ) > $this->getConfigValue('apps.yandexrealty.filetime')) {
                return unlink($cachefile);
            }
        }
        return false;
    }

    protected function collectData( $only_ids = false ) {

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data_shared = $data_model->get_kvartira_model(false, true);
        $form_data_shared = $form_data_shared['data'];

        $select = array();
        $leftjoin = array();


        if ($this->getConfigValue('currency_enable') == 1) {
            $select[] = 'cur.name AS currency';
            $leftjoin[] = 'LEFT JOIN ' . DB_PREFIX . '_currency cur ON cur.currency_id=dt.currency_id';
        }

        if ($this->getConfigValue('apps.yandexrealty.complex_enable') == 1) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
            $ATH = new Admin_Table_Helper();
            $complex_form_data = $ATH->load_model('complex', false);
            $complex_form_data = $complex_form_data['complex'];
            $select[] = 'complex.name AS `building-name`';
            if (isset($complex_form_data['tip_construct'])) {
                $select[] = 'complex.tip_construct AS `building-type`';
            }
            if (isset($complex_form_data['decoration'])) {
                $select[] = 'complex.decoration AS `renovation`';
            }
            if (isset($complex_form_data['built_year'])) {
                $select[] = 'complex.built_year AS `built-year`';
            }
            if (isset($complex_form_data['ready_quarter'])) {
                $select[] = 'complex.ready_quarter AS `ready_quarter`';
            }
            if (isset($complex_form_data['deadline'])) {
                $select[] = 'complex.deadline AS `built-year`';
            }
            if (isset($complex_form_data['building_state'])) {
                $select[] = 'complex.building_state AS `building_state`';
            }
            if (isset($complex_form_data['yandex_house_id'])) {
                $select[] = 'complex.yandex_house_id AS `yandex_house_id`';
            }



            $leftjoin[] = 'LEFT JOIN ' . DB_PREFIX . '_complex complex ON complex.complex_id=dt.complex_id';
            if ($this->getConfigValue('apps.yandexrealty.complex_yandexrealty_export') == 1) {
                $where[] = 'complex.yandexrealty_export=1';
            }
        }

        if (isset($this->form_data_shared['topic_id'])) {
            $select[] = 'tp.name AS topic';
            $leftjoin[] = 'LEFT JOIN ' . DB_PREFIX . '_topic tp ON tp.id=dt.topic_id';
        }

        if (isset($this->form_data_shared['country_id'])) {
            $select[] = 'cr.name AS country';
            $leftjoin[] = 'LEFT JOIN ' . DB_PREFIX . '_country cr ON dt.country_id=cr.country_id';
        }

        if (isset($this->form_data_shared['region_id'])) {
            $select[] = 're.name AS region';
            $leftjoin[] = 'LEFT JOIN ' . DB_PREFIX . '_region re ON dt.region_id=re.region_id';
        }

        if (isset($this->form_data_shared['city_id'])) {
            $select[] = 'ct.name AS city';
            $leftjoin[] = 'LEFT JOIN ' . DB_PREFIX . '_city ct ON dt.city_id=ct.city_id';
        }

        if (isset($this->form_data_shared['district_id'])) {
            $select[] = 'ds.name AS district';
            $leftjoin[] = 'LEFT JOIN ' . DB_PREFIX . '_district ds ON dt.district_id=ds.id';
        }

        if (isset($this->form_data_shared['rayon'])) {
            $select[] = 'ds.name AS rayon';
            $leftjoin[] = 'LEFT JOIN ' . DB_PREFIX . '_rayon ds ON dt.rayon=ds.rayon_id';
        }

        if (isset($this->form_data_shared['raion_id'])) {
            $select[] = 'ra.name AS raion';
            $leftjoin[] = 'LEFT JOIN ' . DB_PREFIX . '_raion ra ON dt.raion_id=ra.raion_id';
        }


        if (isset($this->form_data_shared['street_id'])) {
            $select[] = 'st.name AS street';
            $leftjoin[] = 'LEFT JOIN ' . DB_PREFIX . '_street st ON st.street_id=dt.street_id';
        }

        if (isset($this->form_data_shared['metro_id'])) {
            $select[] = 'mt.name AS metro';
            $leftjoin[] = 'LEFT JOIN ' . DB_PREFIX . '_metro mt ON dt.metro_id=mt.metro_id';
        }

        $direction_field = $this->getConfigValue('apps.yandexrealty.direction_from');

        if ($direction_field != '' && isset($this->form_data_shared[$direction_field])) {
            if ($this->form_data_shared[$direction_field]['type'] == 'select_by_query') {
                $el = $this->form_data_shared[$direction_field];
                $table_id = 'x' . rand(100, 999);
                $select[] = $table_id . '.`' . $el['value_name'] . '` AS _' . $direction_field;
                $leftjoin[] = 'LEFT JOIN ' . DB_PREFIX . '_' . $el['primary_key_table'] . ' ' . $table_id . ' ON dt.`' . $direction_field . '`=' . $table_id . '.`' . $el['primary_key_name'] . '`';
                //var_dump($el);
            }
        }

        $district_from = $this->getConfigValue('apps.yandexrealty.district_from');

        if ($district_from != '' && isset($this->form_data_shared[$district_from])) {
            if ($this->form_data_shared[$district_from]['type'] == 'select_by_query') {
                $el = $this->form_data_shared[$district_from];
                $table_id = 'x' . rand(100, 999);
                $select[] = $table_id . '.`' . $el['value_name'] . '` AS _' . $district_from;
                $leftjoin[] = 'LEFT JOIN ' . DB_PREFIX . '_' . $el['primary_key_table'] . ' ' . $table_id . ' ON dt.`' . $district_from . '`=' . $table_id . '.`' . $el['primary_key_name'] . '`';
                //var_dump($el);
            }
        }

        /*if ('' != trim($this->getConfigValue('apps.yandexrealty.district_from'))) {
            $district_from = trim($this->getConfigValue('apps.yandexrealty.district_from'));
            $manual_district_value = self::symbolsClear($data_item[$district_from]);
            if ($manual_district_value != '') {
                $rs .= '<district>' . self::symbolsClear($manual_district_value) . '</district>' . "\n";
            }
        }*/
        if ( $this->is_huge_mode() ) {
            $select_one = $select;
            $select_one[] = 'dt.*';
            $basic_query_one = $this->compile_basic_query($select_one, $leftjoin);

            $select[] = 'dt.id';
        } else {
            $select[] = 'dt.*';
        }
        $basic_query = $this->compile_basic_query($select, $leftjoin);



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

        $data = array();

        $tasks = array();
        if ($this->export_type != '') {
            $DBC = DBC::getInstance();
            $query = 'SELECT * FROM ' . DB_PREFIX . '_yandexrealty_task WHERE task_label=?';
            $stmt = $DBC->query($query, array($this->export_type));
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $tasks[] = $ar;
                }
            }
        }
        //print_r($tasks);

        if (!empty($tasks)) {

            $unions = array();

            foreach ($tasks as $task) {
                parse_str($task['filter_params'], $filter_params);
                //print_r($filter_params);
                $where = array();
                $where[] = 'dt.active=1';
                $sorts = array();
                $limit = false;
                if (count($filter_params) > 0) {
                    foreach ($filter_params as $filter_param_key => $filter_param_value) {
                        $where[] = 'dt.' . $filter_param_key . '=' . $filter_param_value;
                    }
                }
                if (0 != (int) $task['max_limit_params']) {
                    $limit = (int) $task['max_limit_params'];
                }
                if (0 != (int) $task['use_date_filtering']) {
                    $max_days = (int) $this->getConfigValue('apps.yandexrealty.days_interval');
                    if ($max_days == 0) {
                        $max_date = date('Y-m-d', 0);
                    } else {
                        $max_date = date('Y-m-d', time() - $max_days * 3600 * 24);
                    }
                    $where[] = 'dt.date_added > ' . $max_date;
                }
                if ('' != $task['order_params']) {
                    $order_params = array();
                    preg_match_all('/([a-z0-9_]+):(asc|desc)/i', $task['order_params'], $order_params);
                    if (isset($order_params[1])) {
                        foreach ($order_params[1] as $k => $v) {
                            $sorts[] = 'dt.' . $v . ' ' . ($order_params[2][$k] == 'asc' ? 'ASC' : 'DESC');
                        }
                    }
                }
                $unions[] = array(
                    'where' => $where,
                    'sorts' => $sorts,
                    'limit' => $limit,
                );
            }

            foreach ($unions as $union) {
                $queries[] = $basic_query . (!empty($union['where']) ? ' WHERE ' . implode(' AND ', $union['where']) : '') . (!empty($union['sorts']) ? ' ORDER BY ' . implode(', ', $union['sorts']) : '') . ($union['limit'] ? ' LIMIT ' . $union['limit'] : '');
            }

            $data = array();

            if (@count($queries) > 0) {
                foreach ($queries as $query) {
                    $stmt = $DBC->query($query);
                    if ($stmt) {
                        while ($ar = $DBC->fetch($stmt)) {
                            $data[$ar['id']] = $ar;
                        }
                    }
                }
            }
        } else {
            $DBC = DBC::getInstance();
            if (!is_null($this->exported_ids)) {
                $where[] = 'dt.id IN (' . implode(',', $this->exported_ids) . ')';
                if ($this->activity_filtering) {
                    $where[] = 'dt.active=1';
                    if (1 == (int) $this->getConfigValue('apps.realty.use_predeleting')) {
                        $where[] = 'dt.`archived`<>1';
                    }
                }
                if ($this->time_filtering) {
                    $max_days = (int) $this->getConfigValue('apps.yandexrealty.days_interval');
                    if ($max_days == 0) {
                        $max_date = date('Y-m-d', 0);
                    } else {
                        $max_date = date('Y-m-d', time() - $max_days * 3600 * 24);
                    }
                    $where[] = 'dt.date_added > \'' . $max_date . '\'';
                }
                if ($this->field_filtering) {
                    if ('' !== trim($this->getConfigValue('apps.yandexrealty.filtering_checkbox_name'))) {
                        $where[] = 'dt.' . trim($this->getConfigValue('apps.yandexrealty.filtering_checkbox_name')) . '=1';
                    }
                }
            } elseif (!is_null($this->exported_conditions)) {
                foreach ($this->exported_conditions as $query) {
                    $where[] = $query;
                }
                if ($this->activity_filtering) {
                    $where[] = 'dt.active=1';
                }
                if ($this->time_filtering) {
                    $max_days = (int) $this->getConfigValue('apps.yandexrealty.days_interval');
                    if ($max_days == 0) {
                        $max_date = date('Y-m-d', 0);
                    } else {
                        $max_date = date('Y-m-d', time() - $max_days * 3600 * 24);
                    }
                    $where[] = 'dt.date_added > \'' . $max_date . '\'';
                }
                if ($this->field_filtering) {
                    if ('' !== trim($this->getConfigValue('apps.yandexrealty.filtering_checkbox_name'))) {
                        $where[] = 'dt.' . trim($this->getConfigValue('apps.yandexrealty.filtering_checkbox_name')) . '=1';
                    }
                }
            } else {
                $max_days = (int) $this->getConfigValue('apps.yandexrealty.days_interval');
                if ($max_days == 0) {
                    $max_date = date('Y-m-d', 0);
                } else {
                    $max_date = date('Y-m-d', time() - $max_days * 3600 * 24);
                }

                $where[] = 'dt.active=1';
                if (1 == (int) $this->getConfigValue('apps.realty.use_predeleting')) {
                    $where[] = 'dt.`archived`<>1';
                }
                $where[] = 'dt.date_added > \'' . $max_date . '\'';

                if ('' !== trim($this->getConfigValue('apps.yandexrealty.filtering_checkbox_name'))) {
                    $where[] = 'dt.' . trim($this->getConfigValue('apps.yandexrealty.filtering_checkbox_name')) . '=1';
                }

                if (!is_null($this->exportable_user_id)) {
                    $where[] = '`user_id` = ' . $this->exportable_user_id;
                }
            }




            //Максимальный возраст объявления 6-месяцев
            //if(isset)

            $query = $basic_query . (!empty($where) ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY dt.date_added DESC';

            //Формируем запрос для одного объявления
            if ( $this->is_huge_mode() ) {
                $where[] = 'dt.id=?';
                $one_item_query = $basic_query_one . (!empty($where) ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY dt.date_added DESC';

                $this->set_one_item_query($one_item_query);
            }


            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $data[] = $ar;
                }
            }
        }

        return $data;

        /* $filter_params=array();
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
          return $data; */
    }

    protected function set_one_item_query($one_item_query) {
        $this->one_item_query = $one_item_query;
    }

    protected function get_one_item_query() {
        return $this->one_item_query;
    }

    protected function is_huge_mode() {
        return true;
    }

    protected function compile_basic_query(array $select, array $leftjoin) {
        return 'SELECT ' . implode(', ', $select) . ' FROM ' . DB_PREFIX . '_data dt ' . (!empty($leftjoin) ? implode(' ', $leftjoin) : '');
    }

    protected function extract_one_item($data_item)
    {
        $DBC = DBC::getInstance();
        $query = $this->get_one_item_query();
        $stmt = $DBC->query($query, array($data_item['id']));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            return $ar;
        }
        return $data_item;
    }

    protected function download_huge_file ($file) {
        if (is_file($file))
        {
            $chunkSize = 1024 * 1024;
            $handle = fopen($file, 'rb');
            while (!feof($handle))
            {
                $buffer = fread($handle, $chunkSize);
                echo $buffer;
                ob_flush();
                flush();
            }
            fclose($handle);
        }
    }

    protected function is_good_file ($file) {
        $last_line = $this->get_last_string($file);
        if ( $last_line != 'realty-feed>' ) {
            $this->sendFirmMail('report@etown.ru', 'info@etown.ru', 'Bad yandex xml '.$this->getServerFullUrl(true), self::getClearRequestURI().' '.$file);
            return false;
        }
        return true;
    }

    protected function get_last_string ($file) {
        if ( !file_exists($file) ) {
            return '';
        }
        $fp = @fopen($file, "r");
        $pos = -12;
        fseek($fp, $pos, SEEK_END);
        $lastline = fgets($fp);
        fclose($fp);
        return $lastline;
    }

}
