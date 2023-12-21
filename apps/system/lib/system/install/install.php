<?php

/**
 * Install manager
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
class Install_Manager
{
    /**
     * @var SiteBill
     */
    private $sitebill;

    protected $login_field = 'Имя пользователя';
    protected $password_field = 'Пароль';
    protected $login_button = 'Войти';
    protected $add_service_link = 'Включить услугу';
    protected $folder_not_writeble = 'Нет прав на запись для каталога(ов).<br> Установите права на запись (0777) для: ';
    protected $unalble_to_open_file = 'Не могу открыть файл';

    /**
     * Constructor
     */
    function __construct()
    {
    }

    /**
     * Main
     * @param void
     * @return boolean
     */
    function main()
    {
        if (!$this->check_catalogs_and_permissions()) {
            return false;
        }

        /*
        if ( !$this->install_database() ) {
            return false;
        }
        */
        return true;
    }

    function initSitebill()
    {
        $this->sitebill = new SiteBill();
    }

    function GetErrorMessage()
    {
        return $this->sitebill->GetErrorMessage();
    }

    function update_1()
    {
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
        $query_data[] = "INSERT INTO re_user (login,fio,group_id, reg_date, email) VALUES ('_unregistered','Незарегистрированный',4, '" . date('Y-m-d H:i:s') . ".', 'ne-udalyat@etot-account.ru');";

        echo '<h3>' . Multilanguage::_('SQL_NOW', 'system') . '</h3>';
        $DBC = DBC::getInstance();
        foreach ($query_data as $query) {
            $stmt = $DBC->query($query);
            if (!$stmt) {
                echo Multilanguage::_('ERROR_ON_SQL_RUN', 'system') . ': ' . $query . '<br>';
            } else {
                echo Multilanguage::_('QUERY_SUCCESS', 'system') . ': ' . $query . '<br>';
            }
        }

    }

    /**
     * Check catalog structure and permissions
     * @param void
     * @return boolean
     */
    function check_catalogs_and_permissions()
    {
        $error_folder_stack = array();
        $error = false;
        $check_folders = array('/cache/compile', '/cache/upl', '/img/data', '/img/data/user');

        foreach ($check_folders as $folder) {
            if (!is_writable(SITEBILL_DOCUMENT_ROOT . $folder)) {
                $error_folder_stack[] = SITEBILL_DOCUMENT_ROOT . $folder;
                $error = true;
            }
        }
        if ($error) {
            $this->initSitebill();
            $this->sitebill->riseError($this->folder_not_writeble . '<br>' . implode('<br>', $error_folder_stack));
            return false;
        }
        return true;
    }

    /**
     * Install database
     * @param
     * @return
     */
    function install_database()
    {
        $this->initSitebill();

        if (!$this->parse_sql_file(SITEBILL_DOCUMENT_ROOT . '/install/estate.sql')) {
            return false;
        }
        if (!$this->install_default_data()) {
            return false;
        }
        return true;
    }

    /**
     * Install default data
     * @param
     * @return
     */
    function install_default_data($main_url = '', $installtestdata = 1)
    {


        $DBC = DBC::getInstance();

        $query_data = [];

        $query_data[] = "INSERT INTO re_group (group_id,name,system_name) VALUES (1,'Администраторы','admin');";

        $query_data[] = "INSERT INTO re_function (function_id,name,sort_order,description) VALUES (1,'login',0,'Разрешение на вход');";
        $query_data[] = "INSERT INTO re_component (component_id,name,sort_order) VALUES (1,'admin_panel',0);";
        $query_data[] = "INSERT INTO re_component_function (component_function_id,component_id,function_id) VALUES (1,1,1);";
        $query_data[] = "INSERT INTO re_dna (group_id,component_id,function_id) VALUES (1,1,1);";

        if($installtestdata == 1){
            $query = "select count(id) as cid from " . DB_PREFIX . "_topic";
            $stmt = $DBC->query($query);
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                if ($ar['cid'] == 0) {
                    $install_default = 1;
                    $query = "
					INSERT INTO `" . DB_PREFIX . "_topic` (`id`, `name`, `active`, `parent_id`, `order`) VALUES
					(1, 'Аренда квартир', 1, 0, 10),
					(2, 'Продажа квартир', 1, 0, 20),
					(3, 'Новостройки', 1, 0, 30),
					(4, 'Коммерческая', 1, 0, 40),
					(5, 'Дома-участки', 1, 0, 50),
					(6, 'Гаражи', 1, 0, 60),
					(10, 'Комнаты', 2, 11, 10),
					(11, 'Секционки', 2, 1, 20),
					(12, 'Гостинки', 2, 1, 30),
					(13, '1-комн.', 2, 1, 40),
					(14, '2-комн.', 2, 1, 50),
					(15, '3-комн.', 2, 1, 60),
					(16, '4-комн.', 2, 1, 70),
					(17, 'Элитное жилье', 2, 1, 80),
					(20, 'Комнаты', 2, 2, 10),
					(21, 'Секционки', 2, 2, 20),
					(22, 'Гостинки', 2, 2, 30),
					(23, '1-комн.', 2, 2, 40),
					(24, '2-комн.', 2, 2, 50),
					(25, '3-комн.', 2, 2, 60),
					(26, '4-комн.', 2, 2, 70),
					(27, 'Элитное жилье', 2, 2, 80),
					(30, 'Комнаты', 2, 3, 10),
					(31, 'Секционки', 2, 3, 20),
					(32, 'Гостинки', 2, 3, 30),
					(33, '1-комн.', 2, 3, 40),
					(34, '2-комн.', 2, 3, 50),
					(35, '3-комн.', 2, 3, 60),
					(36, '4-комн.', 2, 3, 70),
					(37, 'Элитное жилье', 2, 3, 80),
					(40, 'Аренда', 2, 4, 10),
					(41, 'Продажа', 2, 4, 20),
					(4010, 'Офисы', 3, 40, 10),
					(4020, 'Торговые площади', 3, 40, 20),
					(4110, 'Офисы', 3, 41, 10),
					(4120, 'Магазины', 3, 41, 20),
					(4130, 'Торговые площади', 3, 41, 30),
					(50, 'Дома', 2, 5, 10),
					(51, 'Коттеджи', 2, 5, 20),
					(52, 'Дачи', 2, 5, 30),
					(53, 'Землеотводы', 2, 5, 40),
					(54, 'Участки', 2, 5, 50),
					(60, 'Аренда', 2, 6, 10),
					(61, 'Продажа', 2, 6, 20),
					(6010, 'Гараж', 3, 60, 10),
					(6020, 'Автобокс', 3, 60, 10),
					(6110, 'Гараж', 3, 61, 10),
					(6120, 'Автобокс', 3, 61, 20)            
					            ";
                    $stmt = $DBC->query($query);
                }
            }
//echo 'запуск install_manager sql<br>';

            if ($install_default) {
                //echo 'список sql<br>';

                $query_data[] = "INSERT INTO re_city (city_id,name,region_id) VALUES (1,'Москва',1)";
                $query_data[] = "INSERT INTO re_city (city_id,name,region_id) VALUES (2,'Киев',2)";
                $query_data[] = "INSERT INTO re_city (city_id,name,region_id) VALUES (3,'Красноярск',3)";

                $query_data[] = "INSERT INTO re_country (country_id,name) VALUES (1,'Россия')";
                $query_data[] = "INSERT INTO re_country (country_id,name) VALUES (2,'Украина')";

                $query_data[] = "INSERT INTO re_district (id,name,short_name1,city_id) VALUES (1,'Восточный',null,1)";
                $query_data[] = "INSERT INTO re_district (id,name,short_name1,city_id) VALUES (2,'Одесский',null,2)";
                $query_data[] = "INSERT INTO re_district (id,name,short_name1,city_id) VALUES (3,'Советский',null,3)";

                $query_data[] = "INSERT INTO re_metro (metro_id,name,city_id) VALUES (1,'Курская',1)";
                $query_data[] = "INSERT INTO re_metro (metro_id,name,city_id) VALUES (2,'Крымская',2)";

                $query_data[] = "INSERT INTO re_region (region_id,name,country_id) VALUES (1,'Москва',1)";
                $query_data[] = "INSERT INTO re_region (region_id,name,country_id) VALUES (2,'Киев',2)";
                $query_data[] = "INSERT INTO re_region (region_id,name,country_id) VALUES (3,'Красноярский край',1)";

                $query_data[] = "INSERT INTO re_street (street_id,prefix,name,district_id) VALUES (1,null,'проспект Мира',1)";
                $query_data[] = "INSERT INTO re_street (street_id,prefix,name,district_id) VALUES (2,null,'Гоголя',2)";
                $query_data[] = "INSERT INTO re_street (street_id,prefix,name,district_id) VALUES (3,null,'Авиаторов',3)";

                $query_data[] = "INSERT INTO re_news (title,description,date,img,img_preview,anons) VALUES ('Установка успешна','Демо-версия активна 30 дней. Вы можете ознакомиться с функциями движка.'," . time() . ",'','','Поздравляем с успешной установкой движка')";

                $query_data[] = "INSERT INTO re_data (id,user_id,type_id,topic_id,country_id,city_id,metro_id,district_id,price,text,contact,date_added,agent_tel,room_count,elite,session_id,active,sub_id1,sub_id2,reviews_count,hot,floor,floor_count,walls,balcony,square_all,square_live,square_kitchen,bathroom,is_telephone,furniture,plate,agent_email,number,spec,floor_cover,square_room,is_kitchen,region_id,street_id, geo_lat, geo_lng, image) VALUES (1,1,0,25,1,1,1,1,10000000,'Окна выходят в зеленый дворик',null,{ts '" . date('Y-m-d H:i:s.') . "'},null,3,0,'',1,0,0,0,1,8,16,'кирпич','есть','80','60','20','раздельный','1','1','электро','','46',0,null,null,0,1,1,55.781296,37.634074, '" . 'a:3:{i:0;a:4:{s:7:"preview";s:33:"prv5886e8b224565_1485236402_1.jpg";s:6:"normal";s:33:"img5886e8b224565_1485236402_1.jpg";s:4:"type";s:7:"graphic";s:4:"mime";s:3:"jpg";}i:1;a:4:{s:7:"preview";s:33:"prv5886e8b24fc25_1485236402_2.jpg";s:6:"normal";s:33:"img5886e8b24fc25_1485236402_2.jpg";s:4:"type";s:7:"graphic";s:4:"mime";s:3:"jpg";}i:2;a:4:{s:7:"preview";s:33:"prv5886e8b275906_1485236402_3.jpg";s:6:"normal";s:33:"img5886e8b275906_1485236402_3.jpg";s:4:"type";s:7:"graphic";s:4:"mime";s:3:"jpg";}}' . "')";
                $query_data[] = "INSERT INTO re_data (id,user_id,type_id,topic_id,country_id,city_id,metro_id,district_id,price,text,contact,date_added,agent_tel,room_count,elite,session_id,active,sub_id1,sub_id2,reviews_count,hot,floor,floor_count,walls,balcony,square_all,square_live,square_kitchen,bathroom,is_telephone,furniture,plate,agent_email,number,spec,floor_cover,square_room,is_kitchen,region_id,street_id, geo_lat, geo_lng, image) VALUES (2,1,0,24,2,2,2,2,7000000,'Квартира в новом районе с развитой инфраструктурой',null,{ts '" . date('Y-m-d H:i:s.') . "'},null,2,0,'',1,0,0,0,1,5,10,'монолит','есть','70','50','20','раздельный','1','1','газ','','21',0,null,null,0,2,2,50.354024,30.690814, '" . 'a:3:{i:0;a:4:{s:7:"preview";s:33:"prv5886e83213fae_1485236274_1.jpg";s:6:"normal";s:33:"img5886e83213fae_1485236274_1.jpg";s:4:"type";s:7:"graphic";s:4:"mime";s:3:"jpg";}i:1;a:4:{s:7:"preview";s:33:"prv5886e83240190_1485236274_2.jpg";s:6:"normal";s:33:"img5886e83240190_1485236274_2.jpg";s:4:"type";s:7:"graphic";s:4:"mime";s:3:"jpg";}i:2;a:4:{s:7:"preview";s:33:"prv5886e832671b5_1485236274_3.jpg";s:6:"normal";s:33:"img5886e832671b5_1485236274_3.jpg";s:4:"type";s:7:"graphic";s:4:"mime";s:3:"jpg";}}' . "')";
                $query_data[] = "INSERT INTO re_data (id,user_id,type_id,topic_id,country_id,city_id,metro_id,district_id,price,text,contact,date_added,agent_tel,room_count,elite,session_id,active,sub_id1,sub_id2,reviews_count,hot,floor,floor_count,walls,balcony,square_all,square_live,square_kitchen,bathroom,is_telephone,furniture,plate,agent_email,number,spec,floor_cover,square_room,is_kitchen,region_id,street_id, geo_lat, geo_lng, image) VALUES (3,1,0,24,1,3,0,3,5000000,'Квартира в элитном доме. Рядом большой ТЦ, школы и детские сады.',null,{ts '" . date('Y-m-d H:i:s.') . "'},null,2,0,'',1,0,0,0,1,5,10,'монолит','есть','70','50','20','раздельный','1','1','газ','','41',0,null,null,0,3,3,56.048614,92.911570, '" . 'a:3:{i:0;a:4:{s:7:"preview";s:33:"prv5886e7ca9964d_1485236170_1.jpg";s:6:"normal";s:33:"img5886e7ca9964d_1485236170_1.jpg";s:4:"type";s:7:"graphic";s:4:"mime";s:3:"jpg";}i:1;a:4:{s:7:"preview";s:33:"prv5886e7cadb784_1485236170_2.jpg";s:6:"normal";s:33:"img5886e7cadb784_1485236170_2.jpg";s:4:"type";s:7:"graphic";s:4:"mime";s:3:"jpg";}i:2;a:4:{s:7:"preview";s:33:"prv5886e7cb1330e_1485236171_3.jpg";s:6:"normal";s:33:"img5886e7cb1330e_1485236171_3.jpg";s:4:"type";s:7:"graphic";s:4:"mime";s:3:"jpg";}}' . "')";

                $query_data[] = "INSERT INTO re_menu (menu_id,name,sort_order,tag) VALUES (21,'Верхнее меню',0,'right_menu')";

                $query_data[] = "INSERT INTO re_menu_structure (menu_structure_id,name,url,sort_order,menu_id) VALUES (68,'Продать или сдать квартиру','/add/',0,21);";
                $query_data[] = "INSERT INTO re_menu_structure (menu_structure_id,name,url,sort_order,menu_id) VALUES (69,'Снять квартиру','/getrent/',0,21);";
                $query_data[] = "INSERT INTO re_menu_structure (menu_structure_id,name,url,sort_order,menu_id) VALUES (70,'Оформить ипотеку','/ipotekaorder/',0,21);";
                $query_data[] = "INSERT INTO re_menu_structure (menu_structure_id,name,url,sort_order,menu_id) VALUES (71,'Связаться с нами','/contactus/',0,21);";

                $query_data[] = "INSERT INTO re_group (group_id,name,system_name) VALUES (2,'Агентство','agency');";
                $query_data[] = "INSERT INTO re_group (group_id,name,system_name) VALUES (3,'Риелтор','realtor');";
                $query_data[] = "INSERT INTO re_group (group_id,name,system_name) VALUES (4,'Незарегистрированные','_unregistered');";
                $query_data[] = "INSERT INTO re_group (group_id,name,system_name) VALUES (5,'Клиенты','client');";
                $query_data[] = "INSERT INTO re_user (login,fio,group_id, reg_date, email) VALUES ('_unregistered','Незарегистрированный',4, '" . date('Y-m-d H:i:s') . ".', 'ne-udalyat@etot-account.ru');";



                $query_data[] = "INSERT INTO re_dna (group_id,component_id,function_id) VALUES (2,1,1);";
                $query_data[] = "INSERT INTO re_dna (group_id,component_id,function_id) VALUES (3,1,1);";
                $query_data[] = "INSERT INTO re_dna (group_id,component_id,function_id) VALUES (2,2,2);";
                $query_data[] = "INSERT INTO re_dna (group_id,component_id,function_id) VALUES (3,2,2);";

                $query_data[] = "INSERT INTO `re_currency` (`currency_id`, `code`, `name`, `sort_order`, `course`, `is_default`, `is_active`) VALUES
                (1, 'RUR', 'р.', 1, '1', 1, 1),
                (2, 'USD', '$', 2, '30', 0, 1),
                (3, 'EUR', '€', 7, '40', 0, 1);";
            }
        }


        if(!empty($query_data)){
            foreach ($query_data as $query) {
                $stmt = $DBC->query($query, array(), $success);
                if (!$stmt) {
                    echo $DBC->getLastError() . '<br>';
                }
            }
        }

        if($installtestdata != 1){
            $install_photos = [
                'prv5886e8b224565_1485236402_1.jpg',
                'img5886e8b224565_1485236402_1.jpg',
                'prv5886e8b24fc25_1485236402_2.jpg',
                'img5886e8b24fc25_1485236402_2.jpg',
                'prv5886e8b275906_1485236402_3.jpg',
                'img5886e8b275906_1485236402_3.jpg',
                'prv5886e83213fae_1485236274_1.jpg',
                'img5886e83213fae_1485236274_1.jpg',
                'prv5886e83240190_1485236274_2.jpg',
                'img5886e83240190_1485236274_2.jpg',
                'prv5886e832671b5_1485236274_3.jpg',
                'img5886e832671b5_1485236274_3.jpg',
                'prv5886e7ca9964d_1485236170_1.jpg',
                'img5886e7ca9964d_1485236170_1.jpg',
                'prv5886e7cadb784_1485236170_2.jpg',
                'img5886e7cadb784_1485236170_2.jpg',
                'prv5886e7cb1330e_1485236171_3.jpg',
                'img5886e7cb1330e_1485236171_3.jpg'
            ];

            foreach ($install_photos as $iphoto){
                unlink(SITEBILL_DOCUMENT_ROOT.'/img/data/'.$iphoto);
            }
        }


        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/form/contactus.php');
        $contactus_form = new contactus_Form();


        return true;
    }

    /**
     * Parse sql file
     * @param string $filename
     * @return
     */
    function parse_sql_file($filename)
    {
        $DBC = DBC::getInstance();
        $handle = fopen($filename, "r");
        if (!$handle) {
            $this->sitebill->riseError($this->unalble_to_open_file . ' ' . $filename);
            return false;
        }
        $data = fread($handle, filesize($filename));
        fclose($handle);
        $queries = explode('#', $data);
        foreach ($queries as $query) {
            $stmt = $DBC->query($query);
        }
        if (!$this->get_admin_user()) {
            $query = "INSERT INTO re_user (user_id,login,pass,active,reg_date,password,fio,email,account,group_id,phone,site,imgfile,mobile,icq) VALUES (1,'admin','admin',0,null,'21232f297a57a5a743894a0e4a801fc3','Администратор','kondin@etown.ru','0',1,'Телефон','http://www.sitebill.ru','','','73072365')";
            $stmt = $DBC->query($query);
        }
        return true;
    }

    /**
     * Get admin user
     * @return boolean
     */
    function get_admin_user()
    {
        $DBC = DBC::getInstance();
        $query = "select * from " . DB_PREFIX . "_user where login='admin'";
        $stmt = $DBC->query($query);
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($ar['user_id'] > 0) {
                return $ar['user_id'];
            }
        }
        return false;
    }
}
