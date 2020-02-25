<?php

class front_gridmanager_admin extends table_admin {

    public function __construct() {
        parent::__construct();
        if (!$this->helper->check_table_exist('table_frontgrid')) {
            $this->install();
        }
        $this->table_name = 'table_frontgrid';
        $this->primary_key = 'frontgrid_id';

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/front_grid_model.php');
        $Object = new Front_Grid_Model();
        $this->data_model = $Object->get_model();
    }

    function install() {
        $query = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "_table_frontgrid` (
              `frontgrid_id` int(11) NOT NULL AUTO_INCREMENT,
              `topic_id` text NOT NULL,
              `columns` text NOT NULL,
                `title` VARCHAR(255) NOT NULL, 
              PRIMARY KEY (`frontgrid_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=" . DB_ENCODING . ";";
        $DBC = DBC::getInstance();
        $success = false;
        $stmt = $DBC->query($query, array(), $rows, $success);
        if (!$success) {
            $rs = Multilanguage::_('L_APPLICATION_INSTALLED_ERROR');
        } else {
            $rs = Multilanguage::_('L_APPLICATION_INSTALLED');
        }
        return $rs;
    }

    function getTopMenu () {
        $rs=parent::getTopMenu();
        $rs .= '<a href="?action=table&section=front_gridmanager&do=new" class="btn btn-primary">Новая сетка</a> ';
        return $rs;
    }

    function main () {
        $rs = $this->getTopMenu();
        switch($this->getRequestValue('do')){
            case 'edit' : {
                $rs.=$this->editGrid($this->getRequestValue($this->primary_key));
                break;
            }
            case 'new' : {
                $rs.=$this->editGrid(0);
                break;
            }
            case 'mass_delete' : {
                $id_array=array();
                $ids=trim($this->getRequestValue('ids'));
                if($ids!=''){
                    $id_array=explode(',',$ids);
                }
                $rs.=$this->mass_delete_data($this->table_name, $this->primary_key, $id_array);
                break;
            }
            case 'delete' : {
                $this->delete_data($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key));
                if ( $this->getError() ) {
                    $rs .= '<div align="center">'.Multilanguage::_('L_ERROR_ON_DELETE').': '.$this->GetErrorMessage().'<br>';
                    $rs .= '<a href="?action='.$this->action.'">ОК</a>';
                    $rs .= '</div>';
                } else {
                    $rs .= $this->grid();
                }


                break;
            }
            default : {
                $rs.=$this->grid();
            }
        }

        $rs_new = $this->get_app_title_bar();
        $rs_new .= $rs;
        return $rs_new;
    }

    public function getCustomGrid($topics){

        if(empty($topics)){
            return false;
        }
        $all_grids=array();
        $customGridID=0;
        $DBC=DBC::getInstance();
        $stmt=$DBC->query('SELECT '.$this->primary_key.', topic_id FROM '.DB_PREFIX.'_table_frontgrid');
        if($stmt){
            while($ar=$DBC->fetch($stmt)){
                $all_grids[$ar[$this->primary_key]]['topics']=explode(',', $ar['topic_id']);
            }
        }

        if(empty($all_grids)){
            return false;
        }
        if(count($topics)==1){
            foreach ($all_grids as $grid_id=>$grid_topics){
                //echo $topics[0];
                //print_r($gris_topics);
                if(in_array($topics[0], $grid_topics['topics'])){
                    $customGridID=$grid_id;
                    break;
                }
            }
        }else{
            foreach ($all_grids as $grid_id=>$grid_topics){
                if(count(array_intersect($topics, $grid_topics['topics']))==count($topics)){
                    $customGridID=$grid_id;
                    break;
                }
            }
        }
        //var_dump($customGridID);
        if($customGridID==0){
            return false;
        }
        $stmt=$DBC->query('SELECT columns FROM '.DB_PREFIX.'_table_frontgrid WHERE '.$this->primary_key.'=? LIMIT 1', array($customGridID));
        if(!$stmt){
            return false;
        }
        $ar=$DBC->fetch($stmt);
        //print_r($ar);
        return unserialize($ar['columns']);
    }

    private function editGrid($id){
        $selected_cols=array();


        $model_data = $this->helper->load_model('data');



        foreach($model_data['data'] as $k=>$v){
            $fields[$k]=array('title'=>$v['title']);
        }



        if($id!=0){
            $DBC=DBC::getInstance();
            $stmt=$DBC->query('SELECT * FROM '.DB_PREFIX.'_table_frontgrid WHERE '.$this->primary_key.'=? LIMIT 1', array($id));
            if($stmt){
                $form_data=$DBC->fetch($stmt);
                if($form_data['topic_id']!=''){
                    $form_data['topic_id']=explode(',', $form_data['topic_id']);
                }else{
                    $form_data['topic_id']=array();
                }
                $selected_cols=unserialize($form_data['columns']);
                //print_r($selected_cols);
                //print_r($form_data);

                //foreach()

                foreach($selected_cols as $f=>$d){
                    if(isset($fields[$f])){
                        unset($fields[$f]);
                    }
                    if(isset($d['attached']) && !empty($d['attached'])){
                        foreach($d['attached'] as $a){
                            if(isset($fields[$a['name']])){
                                unset($fields[$a['name']]);
                            }	
                        }
                    }
                }
                /*
                foreach($fields as $f=>$d){
                    if(isset($selected_cols[$f])){
                        unset($fields[$f]);
                    }
                    if(isset($d['attached']) && !empty($d['attached'])){
                        foreach($d['attached'] as $a){

                        }
                    }
                }
                */	

                //$columns_ids=$this->_getColumnsNameIds();

                /*foreach($selected_columns_ids as $k=>$sc){
                    if(isset($all_form_fields[$columns_ids[$sc]])){
                        $selected_cols[]=$all_form_fields[$columns_ids[$sc]];
                        unset($all_form_fields[$columns_ids[$sc]]);
                    }
                }*/


            }
        }else{
            $form_data['frontgrid_id']=0;
            $form_data['title']='';
            $form_data['topic_id']=array();
        }

        $this->template->assert('model_fields', $fields);

        include_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php';
        $SM=new Structure_Manager();
        $this->template->assert('topic_select_box', $SM->getCategorySelectBoxWithName('search_form_topic', (array)$form_data['topic_id']));
        $this->template->assert('grid_id', $form_data['frontgrid_id']);
        $this->template->assert('grid_title', $form_data['title']);
        //$this->template->assert('available_columns', $avial_cols);
        $this->template->assert('selected_columns', $selected_cols);
        global $smarty;
        return $smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/template/front_grid_manager.tpl');




        return 'Правим сетку '.$id;
        $form_data=array();
        $avial_cols=array();
        $selected_cols=array();

        $model_data = $this->helper->load_model('data');
        $all_form_fields=$this->get_test_form($model_data['data']);

        if($id!=0){
            $DBC=DBC::getInstance();
            $stmt=$DBC->query('SELECT * FROM '.DB_PREFIX.'_table_searchform WHERE '.$this->primary_key.'=? LIMIT 1', array($id));
            if($stmt){
                $form_data=$DBC->fetch($stmt);
                if($form_data['topic_id']!=''){
                    $form_data['topic_id']=explode(',', $form_data['topic_id']);
                }else{
                    $form_data['topic_id']=array();
                }
                $selected_columns_ids=unserialize($form_data['columns']);


                $columns_ids=$this->_getColumnsNameIds();

                foreach($selected_columns_ids as $k=>$sc){
                    if(isset($all_form_fields[$columns_ids[$sc]])){
                        $selected_cols[]=$all_form_fields[$columns_ids[$sc]];
                        unset($all_form_fields[$columns_ids[$sc]]);
                    }
                }


            }
        }else{
            $form_data['searchform_id']=0;
            $form_data['title']='';
            $form_data['topic_id']=array();
        }
        $avial_cols=$all_form_fields;
        include_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php';
        $SM=new Structure_Manager();
        $this->template->assert('topic_select_box', $SM->getCategorySelectBoxWithName('search_form_topic', (array)$form_data['topic_id']));
        $this->template->assert('form_id', $form_data['searchform_id']);
        $this->template->assert('form_title', $form_data['title']);
        $this->template->assert('available_columns', $avial_cols);
        $this->template->assert('selected_columns', $selected_cols);
        global $smarty;
        return $smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/template/search_form_main.tpl');
    }

    function grid ($params=array(), $default_params=array()) {
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/grid.php');
        $common_grid = new Common_Grid($this);


        $common_grid->add_grid_item($this->primary_key);
        $common_grid->add_grid_item('title');

        $common_grid->add_grid_control('edit');
        $common_grid->add_grid_control('delete');

        $common_grid->setPagerParams(array('action'=>$this->action,'page'=>$this->getRequestValue('page'),'per_page'=>$this->getConfigValue('common_per_page')));

        $common_grid->set_grid_query("select * from ".DB_PREFIX."_".$this->table_name." order by ".$this->primary_key." asc");
        $rs = $common_grid->construct_grid();
        return $rs;
    }

    public function save_front_grid(){
        $DBC=DBC::getInstance();
        $columns_ids=array();
        $grid_id=(int)$this->getRequestValue('grid_id');
        $topic_id=$this->getRequestValue('topic_id');

        if(!is_array($topic_id) && $topic_id!=''){
            $topic_id=(array)$topic_id;
        }

        $all_topics=array();
        $all_topics=$topic_id;

        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/sitebill_krascap.php');
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $category_structure = $Structure_Manager->loadCategoryStructure();

        foreach($topic_id as $t){
            $childs = $Structure_Manager->get_all_childs($t, $category_structure);
            if ( count($childs) > 0 ) {
                $all_topics=array_merge($all_topics, $childs);
            }
        }


        if(!empty($all_topics)){
            $topic_id=implode(',', $all_topics);
        }else{
            $topic_id='';
        }		

        $grid_title= $this->getRequestValue('grid_title');

        $grid_title=SiteBill::iconv('utf-8', SITE_ENCODING, $grid_title);
        $fields=str_replace('\\', '', htmlspecialchars_decode($this->getRequestValue('fields')));
        $columns=json_decode($fields, true);
        if(count($fields)==0){
            $query="DELETE FROM ".DB_PREFIX."_table_frontgrid WHERE `frontgrid_id`=".$grid_id;
            $stmt=$DBC->query($query);
            return;
        }

        foreach($columns as $col){
            $columns_array[$col['name']]=$col;
        }

        $query="DELETE FROM ".DB_PREFIX."_table_frontgrid WHERE `frontgrid_id`=".$grid_id;
        $stmt=$DBC->query($query);


        if($grid_id==0){
            $DBC->query("INSERT INTO ".DB_PREFIX."_table_frontgrid (`topic_id`, `columns`, `title`) VALUES (?,?,?)",array($topic_id, serialize($columns_array), $grid_title));
        }else{
            $DBC->query("INSERT INTO ".DB_PREFIX."_table_frontgrid (`frontgrid_id`, `topic_id`, `columns`, `title`) VALUES (?,?,?,?)",array($grid_id, $topic_id, serialize($columns_array), $grid_title));
        }
    }
	
}