<?php
/**
 * Install manager 
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
class Install_Manager extends SiteBill {
    /**
     * Constructor
     */
    function __construct() {
        $this->SiteBill();
    }
    
    /**
     * Main
     * @param void
     * @return boolean
     */
    function main () {
        if ( !$this->check_catalogs_and_permissions() ) {
            return false;
        }
        
        /*
        if ( !$this->install_database() ) {
            return false;
        }
        */
        return true;
    }
    
    function update_1 () {
    	$query_data[] = "alter table re_topic add column description text";
    	$query_data[] = "alter table re_data add column `planning` text NOT NULL";
    	$query_data[] = "alter table re_data add column `dom` text NOT NULL";
    	$query_data[] = "alter table re_data add column `flat_number` text NOT NULL";
    	$query_data[] = "alter table re_data add column `owner` text NOT NULL";
    	$query_data[] = "alter table re_data add column `source` text NOT NULL";
    	$query_data[] = "alter table re_data add column `adv_date` text NOT NULL";
    	$query_data[] = "alter table re_data add column `more1` text NOT NULL";
    	$query_data[] = "alter table re_data add column `more2` text NOT NULL";
    	$query_data[] = "alter table re_data add column `more3` text NOT NULL";
    	$query_data[] = "alter table re_data add column `youtube` text NOT NULL";
    	$query_data[] = "alter table re_data add column `fio` varchar(255) NOT NULL DEFAULT ''";
    	$query_data[] = "alter table re_data add column `email` varchar(255) NOT NULL DEFAULT ''";
    	$query_data[] = "alter table re_data add column `tmp_password` varchar(255) NOT NULL DEFAULT ''";
    	$query_data[] = "alter table re_data add column `phone` varchar(255) NOT NULL DEFAULT ''";
    	$query_data[] = "alter table re_data add column `realty_type_id` int(10) unsigned NOT NULL DEFAULT '0'";
    	$query_data[] = "alter table re_data add column `view_count` int(10) unsigned NOT NULL DEFAULT '0'";
    	$query_data[] = "alter table re_data add column `best` int(10) unsigned NOT NULL DEFAULT '0'";
    	
    	$query_data[] = "alter table re_data add column `ad_mobile_phone` varchar(255) NOT NULL DEFAULT ''";
    	$query_data[] = "alter table re_data add column `ad_stacionary_phone` varchar(255) NOT NULL DEFAULT ''";
    	$query_data[] = "alter table re_data add column `can_call_start` varchar(255) NOT NULL DEFAULT ''";
    	$query_data[] = "alter table re_data add column `can_call_end` varchar(255) NOT NULL DEFAULT ''";
    	
    	$query_data[] = "alter table re_data add column `meta_title` text";
    	$query_data[] = "alter table re_data add column `meta_keywords` text";
    	$query_data[] = "alter table re_data add column `meta_description` text";
    	 
    	$query_data[] = "alter table re_realtylog add column `ad_mobile_phone` varchar(255) NOT NULL DEFAULT ''";
    	$query_data[] = "alter table re_realtylog add column `ad_stacionary_phone` varchar(255) NOT NULL DEFAULT ''";
    	$query_data[] = "alter table re_realtylog add column `can_call_start` varchar(255) NOT NULL DEFAULT ''";
    	$query_data[] = "alter table re_realtylog add column `can_call_end` varchar(255) NOT NULL DEFAULT ''";
    	 
    	 
    	$query_data[] = "ALTER TABLE re_data CHANGE date_added date_added TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";

    	$query_data[] = "CREATE TABLE IF NOT EXISTS `re_password_recovery` (  `pr_id` int(11) NOT NULL AUTO_INCREMENT,  `user_id` int(11) NOT NULL,  `recovery_code` varchar(50) NOT NULL,  PRIMARY KEY (`pr_id`)) ENGINE=MyISAM  DEFAULT CHARSET=cp1251";
    	$query_data[] = "CREATE TABLE IF NOT EXISTS `re_news_image` (  `news_image_id` int(10) unsigned NOT NULL AUTO_INCREMENT,  `news_id` int(11) NOT NULL DEFAULT '0',  `image_id` int(11) NOT NULL DEFAULT '0',  `sort_order` int(11) NOT NULL DEFAULT '0',  PRIMARY KEY (`news_image_id`)) ENGINE=MyISAM  DEFAULT CHARSET=cp1251";
    	
    	$query_data[] = "ALTER TABLE re_group add unique index gname_idx (system_name)";
    	$query_data[] = "ALTER TABLE re_user add unique index login_idx (login(10))";

    	$query_data[] = "alter table re_config add column `sort_order` int(10) unsigned NOT NULL DEFAULT '1'";
    	 
    	$query_data[] = "INSERT INTO re_group (group_id,name,system_name) VALUES (4,'Незарегистрированные','_unregistered');";
    	$query_data[] = "INSERT INTO re_user (login,fio,group_id, reg_date, email) VALUES ('_unregistered','Незарегистрированный',4, '".date('Y-m-d H:i:s').".', 'ne-udalyat@etot-account.ru');";
    	
    	echo '<h3>'.Multilanguage::_('SQL_NOW','system').'</h3>';
    	foreach ( $query_data as $query ) {
    		$this->db->exec($query);
    		if ( !$this->db->success ) {
    		    echo Multilanguage::_('ERROR_ON_SQL_RUN','system').': '.$query.', <b>'.$this->db->error.'</b><br>';
    		} else {
    		    echo Multilanguage::_('QUERY_SUCCESS','system').': '.$query.'<br>';
    		}
    	}
    	 
    }
    
    /**
     * Check catalog structure and permissions
     * @param void
     * @return boolean
     */
    function check_catalogs_and_permissions () {
        global $ETOWN_LANG;
        $error_folder_stack = array();
        $error = false;
        $check_folders = array('/cache/compile', '/cache/upl', '/img/data', '/img/data/user');
        
        foreach ( $check_folders as $folder ) {
            if ( !is_writable(SITEBILL_DOCUMENT_ROOT.$folder) ) {
                $error_folder_stack[] =  SITEBILL_DOCUMENT_ROOT.$folder;
                $error = true;
            }
        }
        if ( $error ) {
            $this->riseError($ETOWN_LANG->folder_not_writeble.'<br>'.implode('<br>', $error_folder_stack));
            return false;
        }
        return true;
    }
    
    /**
     * Install database
     * @param
     * @return
     */
    function install_database () {
        if ( !$this->parse_sql_file(SITEBILL_DOCUMENT_ROOT.'/install/estate.sql') ) {
            return false;
        }
        if ( !$this->install_default_data() ) {
            return false;
        }
        //$this->db->exec($sql);
        //echo $sql;
        return true;
    }
    
    /**
     * Install default data
     * @param
     * @return
     */
    function install_default_data ( $main_url = '' ) {
        
        /*
        $query = "select count(user_id) as cid from ".DB_PREFIX."_user";
        $this->db->exec($query);
        $this->db->fetch_assoc();
        if ( $this->db->row['cid'] == 0 ) {
            $query = "INSERT INTO `".DB_PREFIX."_user` (`user_id`, `login`, `pass`, `active`, `reg_date`, `password`, `fio`, `email`, `account`, `group_id`, `phone`, `site`, `imgfile`) VALUES
        	(1, 'admin', '', 0, NULL, '21232f297a57a5a743894a0e4a801fc3', 'Super admin', 'admin@etown.ru', '0', 0, '234-34-34', 'http://www.sitebill.ru', 'img5504_1313724442_997.jpg')";
            $this->db->exec($query);
        }
        */
        
        $query = "select count(id) as cid from ".DB_PREFIX."_type";
        $this->db->exec($query);
        $this->db->fetch_assoc();
        if ( $this->db->row['cid'] == 0 ) {
            $install_default = 1;
            $query = "INSERT INTO `re_type` (`id`, `name`, `short_name1`) VALUES
(1, 'дом', '\$type_sh = ''дом'';'),
(2, 'квартира', '\$type_sh = $rc . ''-комн.'';'),
(3, 'комната', '\$type_sh = ''комната'';'),
(4, 'гостинка', '\$type_sh = ''гостинка'';'),
(5, 'секционка', '\$type_sh = ''секция'';'),
(8, 'гараж', '\$type_sh = ''гараж'';'),
(9, 'офис', '\$type_sh = ''офис'';'),
(10, 'торговая площадь', '\$type_sh = ''торг.пл.'';'),
(11, 'магазин', '\$type_sh = ''магазин'';'),
(12, 'коттедж', '\$type_sh = ''коттедж'';'),
(13, 'дача', '\$type_sh = ''дача'';'),
(14, 'земельный участок', '\$type_sh = ''зем.уч.'';'),
(15, 'землеотвод', '\$type_sh = ''землеотвод'';')";
            $this->db->exec($query);
        }
        
        $query = "select count(id) as cid from ".DB_PREFIX."_topic";
        $this->db->exec($query);
        $this->db->fetch_assoc();
        if ( $this->db->row['cid'] == 0 ) {
            $query = "
INSERT INTO `".DB_PREFIX."_topic` (`id`, `name`, `active`, `parent_id`, `order`, `sql_where`, `obj_type_id`, `def_id1`, `def_id2`, `operation_type_id`) VALUES
(1, 'Аренда квартир', 1, 0, 10, NULL, 0, 0, 0, 0),
(2, 'Продажа квартир', 1, 0, 20, NULL, 0, 0, 0, 0),
(3, 'Новостройки', 1, 0, 30, NULL, 0, 0, 0, 0),
(4, 'Коммерческая', 1, 0, 40, NULL, 0, 40, 4010, 0),
(5, 'Дома-участки', 1, 0, 50, NULL, 0, 0, 0, 0),
(6, 'Гаражи', 1, 0, 60, NULL, 8, 61, 0, 0),
(10, 'Комнаты', 2, 11, 10, 'type_id = 3', 0, 0, 0, 1),
(11, 'Секционки', 2, 1, 20, 'type_id = 5', 0, 0, 0, 1),
(12, 'Гостинки', 2, 1, 30, 'type_id = 4', 0, 0, 0, 0),
(13, '1-комн.', 2, 1, 40, 'room_count = 1', 0, 0, 0, 0),
(14, '2-комн.', 2, 1, 50, 'room_count = 2', 0, 0, 0, 0),
(15, '3-комн.', 2, 1, 60, 'room_count = 3', 0, 0, 0, 0),
(16, '4-комн.', 2, 1, 70, 'room_count = 4', 0, 0, 0, 0),
(17, 'Элитное жилье', 2, 1, 80, 'elite = 1', 0, 0, 0, 1),
(20, 'Комнаты', 2, 2, 10, 'type_id = 3', 0, 0, 0, 0),
(21, 'Секционки', 2, 2, 20, 'type_id = 5', 0, 0, 0, 0),
(22, 'Гостинки', 2, 2, 30, 'type_id = 4', 0, 0, 0, 0),
(23, '1-комн.', 2, 2, 40, 'room_count = 1', 0, 0, 0, 0),
(24, '2-комн.', 2, 2, 50, 'room_count = 2', 0, 0, 0, 0),
(25, '3-комн.', 2, 2, 60, 'room_count = 3', 0, 0, 0, 0),
(26, '4-комн.', 2, 2, 70, 'room_count = 4', 0, 0, 0, 0),
(27, 'Элитное жилье', 2, 2, 80, 'elite = 1', 0, 0, 0, 0),
(30, 'Комнаты', 2, 3, 10, 'type_id = 3', 0, 0, 0, 0),
(31, 'Секционки', 2, 3, 20, 'type_id = 5', 0, 0, 0, 0),
(32, 'Гостинки', 2, 3, 30, 'type_id = 4', 0, 0, 0, 0),
(33, '1-комн.', 2, 3, 40, 'room_count = 1', 0, 0, 0, 0),
(34, '2-комн.', 2, 3, 50, 'room_count = 2', 0, 0, 0, 0),
(35, '3-комн.', 2, 3, 60, 'room_count = 3', 0, 0, 0, 0),
(36, '4-комн.', 2, 3, 70, 'room_count = 4', 0, 0, 0, 0),
(37, 'Элитное жилье', 2, 3, 80, 'elite = 1', 0, 0, 0, 0),
(40, 'Аренда', 2, 4, 10, NULL, 0, 0, 4010, 0),
(41, 'Продажа', 2, 4, 20, NULL, 0, 0, 4110, 0),
(4010, 'Офисы', 3, 40, 10, 'sub_id2 = 4010', 0, 0, 0, 0),
(4020, 'Торговые площади', 3, 40, 20, 'sub_id2 = 4020', 0, 0, 0, 0),
(4110, 'Офисы', 3, 41, 10, 'sub_id2 = 4110', 0, 0, 0, 0),
(4120, 'Магазины', 3, 41, 20, 'sub_id2 = 4120', 0, 0, 0, 0),
(4130, 'Торговые площади', 3, 41, 30, 'sub_id2 = 4130', 0, 0, 0, 0),
(50, 'Дома', 2, 5, 10, '((sub_id1 = 50) or (type_id=1))', 0, 0, 0, 0),
(51, 'Коттеджи', 2, 5, 20, '((sub_id1 = 51) or (type_id=12))', 0, 0, 0, 0),
(52, 'Дачи', 2, 5, 30, '((sub_id1 = 52) or (type_id=13))', 0, 0, 0, 0),
(53, 'Землеотводы', 2, 5, 40, '((sub_id1 = 53) or (type_id=15))', 0, 0, 0, 0),
(54, 'Участки', 2, 5, 50, '((sub_id1 = 54) or (type_id=14))', 0, 0, 0, 0),
(60, 'Аренда', 2, 6, 10, 'sub_id1 = 60', 0, 0, 0, 0),
(61, 'Продажа', 2, 6, 20, 'sub_id1 = 61', 0, 0, 0, 0),
(6010, 'Гараж', 3, 60, 10, 'sub_id2 = 6010', 0, 0, 0, 0),
(6020, 'Автобокс', 3, 60, 20, 'sub_id2 = 6020', 0, 0, 0, 0),
(6110, 'Гараж', 3, 61, 10, 'sub_id2 = 6110', 0, 0, 0, 0),
(6120, 'Автобокс', 3, 61, 20, 'sub_id2 = 6120', 0, 0, 0, 0)            
            ";
            $this->db->exec($query);
        }
        
        if ($install_default) {
            $query_data = array();
            $query_data[] = "INSERT INTO re_city (city_id,name,region_id) VALUES (1,'Москва',4)";
            $query_data[] = "INSERT INTO re_city (city_id,name,region_id) VALUES (2,'Киев',5)";

            $query_data[] = "INSERT INTO re_country (country_id,name) VALUES (1,'Россия')";
            $query_data[] = "INSERT INTO re_country (country_id,name) VALUES (2,'Украина')";

            $query_data[] = "INSERT INTO re_district (id,name,short_name1,city_id) VALUES (19,'Восточный',null,1)";
            $query_data[] = "INSERT INTO re_district (id,name,short_name1,city_id) VALUES (20,'Одесский',null,2)";

            $query_data[] = "INSERT INTO re_metro (metro_id,name,city_id) VALUES (1,'Курская',1)";
            $query_data[] = "INSERT INTO re_metro (metro_id,name,city_id) VALUES (2,'Крымская',2)";

            $query_data[] = "INSERT INTO re_region (region_id,name,country_id) VALUES (4,'Москва',1)";
            $query_data[] = "INSERT INTO re_region (region_id,name,country_id) VALUES (5,'Киев',2)";

            $query_data[] = "INSERT INTO re_street (street_id,prefix,name,district_id) VALUES (942,null,'проспект Мира',19)";
            $query_data[] = "INSERT INTO re_street (street_id,prefix,name,district_id) VALUES (943,null,'Гоголя',20)";

            $query_data[] = "INSERT INTO re_news (title,description,date,img,img_preview,anons) VALUES ('Установка успешна','Демо-версия активна 30 дней. Вы можете ознакомиться с функциями движка.',".time().",'','','Поздравляем с успешной установкой движка')";

            $query_data[] = "INSERT INTO re_data (id,user_id,type_id,topic_id,country_id,city_id,metro_id,district_id,street,price,text,contact,date_added,agent_tel,room_count,elite,session_id,active,sub_id1,sub_id2,reviews_count,hot,floor,floor_count,walls,balcony,square_all,square_live,square_kitchen,bathroom,is_telephone,furniture,plate,agent_email,number,spec,floor_cover,square_room,is_kitchen,region_id,street_id, geo_lat, geo_lng) VALUES (1,1,0,25,1,1,1,19,'',5000000,'окна выходят в зеленый дворик',null,{ts '".date('Y-m-d H:i:s.')."'},null,3,0,'',1,0,0,0,1,8,16,'кирпич','есть','80','60','20','раздельный','1','1','электро','','46',0,null,null,0,4,942,55.781296,37.634074)";
            $query_data[] = "INSERT INTO re_data (id,user_id,type_id,topic_id,country_id,city_id,metro_id,district_id,street,price,text,contact,date_added,agent_tel,room_count,elite,session_id,active,sub_id1,sub_id2,reviews_count,hot,floor,floor_count,walls,balcony,square_all,square_live,square_kitchen,bathroom,is_telephone,furniture,plate,agent_email,number,spec,floor_cover,square_room,is_kitchen,region_id,street_id, geo_lat, geo_lng) VALUES (2,1,0,24,2,2,2,20,'',2000000,'квартира в новом районе с развитой инфраструктурой',null,{ts '".date('Y-m-d H:i:s.')."'},null,2,0,'',1,0,0,0,1,5,10,'монолит','есть','70','50','20','раздельный','1','1','газ','','123',0,null,null,0,5,943,50.353835,30.690573)";
            
            $query_data[] = "INSERT INTO re_image (image_id,normal,preview) VALUES (62,'img5976_1326002261_1.jpg','prv5976_1326002261_1.jpg')";
            $query_data[] = "INSERT INTO re_image (image_id,normal,preview) VALUES (63,'img5976_1326002261_2.jpg','prv5976_1326002261_2.jpg')";
            $query_data[] = "INSERT INTO re_image (image_id,normal,preview) VALUES (64,'img5976_1326002261_3.jpg','prv5976_1326002261_3.jpg')";
            $query_data[] = "INSERT INTO re_image (image_id,normal,preview) VALUES (65,'img5976_1326002261_4.jpg','prv5976_1326002261_4.jpg')";
            $query_data[] = "INSERT INTO re_image (image_id,normal,preview) VALUES (66,'img5976_1326002399_1.jpg','prv5976_1326002399_1.jpg')";
            $query_data[] = "INSERT INTO re_image (image_id,normal,preview) VALUES (67,'img5976_1326002399_2.jpg','prv5976_1326002399_2.jpg')";
            $query_data[] = "INSERT INTO re_image (image_id,normal,preview) VALUES (68,'img5976_1326002399_3.jpg','prv5976_1326002399_3.jpg')";
            $query_data[] = "INSERT INTO re_image (image_id,normal,preview) VALUES (69,'img5976_1326002399_4.jpg','prv5976_1326002399_4.jpg')";
            
            $query_data[] = "INSERT INTO re_data_image (data_image_id,id,image_id,sort_order) VALUES (135,1,62,63)";
            $query_data[] = "INSERT INTO re_data_image (data_image_id,id,image_id,sort_order) VALUES (136,1,63,64)";
            $query_data[] = "INSERT INTO re_data_image (data_image_id,id,image_id,sort_order) VALUES (137,1,64,65)";
            $query_data[] = "INSERT INTO re_data_image (data_image_id,id,image_id,sort_order) VALUES (138,1,65,62)";
            $query_data[] = "INSERT INTO re_data_image (data_image_id,id,image_id,sort_order) VALUES (139,2,66,67)";
            $query_data[] = "INSERT INTO re_data_image (data_image_id,id,image_id,sort_order) VALUES (140,2,67,68)";
            $query_data[] = "INSERT INTO re_data_image (data_image_id,id,image_id,sort_order) VALUES (141,2,68,69)";
            $query_data[] = "INSERT INTO re_data_image (data_image_id,id,image_id,sort_order) VALUES (142,2,69,66)";
            
            $query_data[] = "INSERT INTO re_menu (menu_id,name,sort_order,tag) VALUES (21,'Верхнее меню',0,'right_menu')";

            $query_data[] = "INSERT INTO re_menu_structure (menu_structure_id,name,url,sort_order,menu_id) VALUES (68,'Продать или сдать квартиру','".$main_url."/add/',0,21);";
            $query_data[] = "INSERT INTO re_menu_structure (menu_structure_id,name,url,sort_order,menu_id) VALUES (69,'Снять квартиру','".$main_url."/getrent/',0,21);";
            $query_data[] = "INSERT INTO re_menu_structure (menu_structure_id,name,url,sort_order,menu_id) VALUES (70,'Оформить ипотеку','".$main_url."/ipotekaorder/',0,21);";
            $query_data[] = "INSERT INTO re_menu_structure (menu_structure_id,name,url,sort_order,menu_id) VALUES (71,'Связаться с нами','".$main_url."/contactus/',0,21);";
            
            $query_data[] = "INSERT INTO re_group (group_id,name,system_name) VALUES (1,'Администраторы','admin');";
            $query_data[] = "INSERT INTO re_group (group_id,name,system_name) VALUES (2,'Агентство','agency');";
            $query_data[] = "INSERT INTO re_group (group_id,name,system_name) VALUES (3,'Риелтор','realtor');";
            $query_data[] = "INSERT INTO re_group (group_id,name,system_name) VALUES (4,'Незарегистрированные','_unregistered');";
            $query_data[] = "INSERT INTO re_user (login,fio,group_id, reg_date, email) VALUES ('_unregistered','Незарегистрированный',4, '".date('Y-m-d H:i:s').".', 'ne-udalyat@etot-account.ru');";
            
            $query_data[] = "INSERT INTO re_function (function_id,name,sort_order,description) VALUES (1,'login',0,'Разрешение на вход');";
            $query_data[] = "INSERT INTO re_component (component_id,name,sort_order) VALUES (1,'admin_panel',0);";
            $query_data[] = "INSERT INTO re_component_function (component_function_id,component_id,function_id) VALUES (1,1,1);";
            $query_data[] = "INSERT INTO re_dna (group_id,component_id,function_id) VALUES (1,1,1);";
            
            $query_data[] = "INSERT INTO `re_currency` (`currency_id`, `code`, `name`, `sort_order`, `course`, `is_default`, `is_active`) VALUES
            (1, 'RUR', 'р.', 1, '1', 1, 1),
            (2, 'USD', '$', 2, '30', 0, 1),
            (3, 'EUR', '&euro;', 7, '40', 0, 1);";            

            

            foreach ( $query_data as $query ) {
                $this->db->exec($query);
            }
            
        }
        
        
        return true;
    }
    
    /**
     * Parse sql file
     * @param string $filename
     * @return
     */
    function parse_sql_file ( $filename ) {
        global $ETOWN_LANG;
        
        $handle = fopen($filename, "r");
        if (!$handle ) {
            $this->riseError($ETOWN_LANG->unalble_to_open_file.' '.$filename);
            return false;
        }
        $data = fread($handle, filesize($filename));
        fclose($handle);
        $queries = explode('#', $data);
        //echo '<pre>';
        //print_r($queries);
        foreach ( $queries as $query ) {
            $this->db->exec($query);
        }
        if ( !$this->get_admin_user() ) {
            $query = "INSERT INTO re_user (user_id,login,pass,active,reg_date,password,fio,email,account,group_id,phone,site,imgfile,mobile,icq) VALUES (1,'admin','admin',0,null,'21232f297a57a5a743894a0e4a801fc3','Администратор','kondin@etown.ru','0',1,'Телефон','http://www.sitebill.ru','img5976_1326002028_902.jpg','','73072365')";            
            $this->db->exec($query);
        }
        return true;
        
        //echo $data;
    }

    /**
     * Get admin user
     * @return boolean
     */
    function get_admin_user () {
        $query = "select * from ".DB_PREFIX."_user where login='admin'";
        $this->db->exec($query);
        $this->db->fetch_assoc();
        if ( $this->db->row['user_id'] > 0 ) {
            return $this->db->row['user_id'];
        }
        return false;
    }
}
?>
