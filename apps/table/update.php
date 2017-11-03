<?php
class table_update extends SiteBill {
    /**
     * Construct
     */
    function __construct() {
        $this->sitebill();
    }
    
    function main () {
        $query_data[] = "alter table ".DB_PREFIX."_columns add column active_in_topic varchar(255)";
        $query_data[] = "alter table ".DB_PREFIX."_columns add column tab varchar(255)";
        $query_data[] = "alter table ".DB_PREFIX."_columns add column hint varchar(255)";
        $query_data[] = "alter table ".DB_PREFIX."_columns modify active_in_topic TEXT";
        $query_data[] = "alter table ".DB_PREFIX."_columns add column entity varchar(255)";
        $query_data[] = "alter table ".DB_PREFIX."_columns add column combo tinyint(1) NOT NULL DEFAULT '0'";
        $query_data[] = "alter table ".DB_PREFIX."_columns add column parameters text";
        $query_data[] = "alter table ".DB_PREFIX."_table_searchform modify topic_id text";
        $query_data[] = "alter table ".DB_PREFIX."_table_searchform add column title_en text";
        
        $query_data[] = "alter table ".DB_PREFIX."_customentity add column is_public tinyint(1) NOT NULL DEFAULT '0'";
        $query_data[] = "alter table ".DB_PREFIX."_customentity add column alias varchar(255)";
        $query_data[] = "alter table ".DB_PREFIX."_customentity add column list_tpl varchar(255)";
        $query_data[] = "alter table ".DB_PREFIX."_customentity add column view_tpl varchar(255)";
        $query_data[] = "alter table ".DB_PREFIX."_customentity add column sortby varchar(255)";
        $query_data[] = "alter table ".DB_PREFIX."_customentity add column sortorder varchar(255)";
        $query_data[] = "alter table ".DB_PREFIX."_customentity add column per_page int(11)";
        $query_data[] = "alter table ".DB_PREFIX."_customentity add column list_title varchar(255)";
        $query_data[] = "alter table ".DB_PREFIX."_customentity add column view_title varchar(255)";
        
    	require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/sitebill_krascap.php';
		require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php';
		$Structure_Manager=new Structure_Manager();
		$category_structure=$Structure_Manager->loadCategoryStructure();
		
		$columns=array();
		$query='SELECT columns_id, active_in_topic FROM '.DB_PREFIX.'_columns WHERE 1=1';
		$DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				if($ar['active_in_topic']!=0){
					$columns[$ar['columns_id']]=explode(',', $ar['active_in_topic']);
				}
			}
		}
		
		$update_query='UPDATE '.DB_PREFIX.'_columns SET active_in_topic=? WHERE columns_id=?';
		
		if(count($columns)>0){
			foreach($columns as $cid=>$cait){
				$childs=array();
				foreach($cait as $c){
					$childs=array_merge($childs, $Structure_Manager->get_all_childs($c, $category_structure));
					$childs[]=$c;
				}
				$stmt=$DBC->query($update_query, array(implode(',', array_unique($childs)), $cid));
			}
		}
        
        $rs = '<h3>'.Multilanguage::_('SQL_NOW','system').'</h3>';
        
        foreach ( $query_data as $query ) {
        	$success=false;
        	$stmt=$DBC->query($query, array(), $rows, $success);
        	if ( !$success ) {
        		$rs .= Multilanguage::_('ERROR_ON_SQL_RUN','system').': '.$query.'<br>';
        	} else {
        		$rs .= Multilanguage::_('QUERY_SUCCESS','system').': '.$query.'<br>';
        	}
        }
        return $rs;
    }
}
