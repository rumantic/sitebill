DROP TABLE IF EXISTS `re_apps`;
CREATE TABLE IF NOT EXISTS `re_apps` (
  `apps_id` int(11) NOT NULL AUTO_INCREMENT,
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `backend_menu` tinyint(4) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `href_admin` varchar(255) NOT NULL,
  `admin_path` text,
  `local_admin_path` text,
  `site_path` text,
  `local_site_path` text,
  `preload` tinyint(4) NOT NULL DEFAULT '0',
  `frontend` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`apps_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_banner`;
CREATE TABLE IF NOT EXISTS `re_banner` (
  `banner_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `body` text,
  `catalog_id` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `url` text,
  `description` text NOT NULL,
  PRIMARY KEY (`banner_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_banner_informer`;
CREATE TABLE IF NOT EXISTS `re_banner_informer` (
  `biid` int(11) NOT NULL AUTO_INCREMENT,
  `access_code` varchar(10) NOT NULL,
  `informer_parameters` text NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  PRIMARY KEY (`biid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_bill`;
CREATE TABLE IF NOT EXISTS `re_bill` (
  `bill_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `sum` varchar(255) NOT NULL DEFAULT '0',
  `payment_sum` varchar(255) NOT NULL DEFAULT '0',
  `payment_sum_robokassa` decimal(10,2) NOT NULL,
  `payment_type` varchar(100) NOT NULL,
  `payment_params` text NOT NULL,
  `date` int(11) NOT NULL DEFAULT '0',
  `bdirect` tinyint(4) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `description` text,
  `http_x_real_ip` varchar(255) NOT NULL DEFAULT '',
  `http_referer` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`bill_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_cache`;
CREATE TABLE IF NOT EXISTS `re_cache` (
  `parameter` varchar(200) NOT NULL,
  `value` mediumtext NOT NULL,
  `created_at` int(15) NOT NULL,
  `valid_for` int(15) NOT NULL,
  PRIMARY KEY (`parameter`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_captcha_session`;
CREATE TABLE IF NOT EXISTS `re_captcha_session` (
  `captcha_session_id` int(11) NOT NULL AUTO_INCREMENT,
  `captcha_session_key` varchar(255) DEFAULT NULL,
  `captcha_string` varchar(32) NOT NULL DEFAULT '0',
  `start_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`captcha_session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_city`;
CREATE TABLE IF NOT EXISTS `re_city` (
  `city_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL DEFAULT '',
  `region_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`city_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_client`;
CREATE TABLE IF NOT EXISTS `re_client` (
  `client_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `date` int(11) NOT NULL DEFAULT '0',
  `type_id` varchar(100) NOT NULL,
  `status_id` varchar(255) NOT NULL,
  `fio` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `address` text,
  `order_text` text,
  PRIMARY KEY (`client_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_columns`;
CREATE TABLE IF NOT EXISTS `re_columns` (
  `columns_id` int(11) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `table_id` int(11) NOT NULL DEFAULT '0',
  `group_id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `primary_key_name` varchar(255) DEFAULT NULL,
  `primary_key_table` varchar(255) DEFAULT NULL,
  `value_string` varchar(255) DEFAULT NULL,
  `query` text,
  `value_name` varchar(255) DEFAULT NULL,
  `title_default` varchar(255) DEFAULT NULL,
  `value_default` varchar(255) DEFAULT NULL,
  `value` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `unique` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `value_table` varchar(255) NOT NULL,
  `value_primary_key` varchar(255) NOT NULL,
  `value_field` varchar(255) NOT NULL,
  `assign_to` varchar(255) NOT NULL,
  `dbtype` varchar(255) NOT NULL,
  `table_name` varchar(255) NOT NULL,
  `primary_key` varchar(255) NOT NULL,
  `primary_key_value` varchar(255) NOT NULL,
  `action` varchar(255) NOT NULL,
  `select_data` text NOT NULL,
  `active_in_topic` text,
  `tab` varchar(255) DEFAULT NULL,
  `hint` varchar(255) DEFAULT NULL,
  `entity` varchar(255) DEFAULT NULL,
  `combo` tinyint(1) NOT NULL DEFAULT '0',
  `parameters` text,
  PRIMARY KEY (`columns_id`),
  UNIQUE KEY `column_table` (`table_id`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_comment`;
CREATE TABLE IF NOT EXISTS `re_comment` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `comment_text` text NOT NULL,
  `comment_date` datetime NOT NULL,
  `parent_comment_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `object_type` varchar(255) NOT NULL,
  `object_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `is_published` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY (`comment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_component`;
CREATE TABLE IF NOT EXISTS `re_component` (
  `component_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `sort_order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`component_id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `component_name_idx` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_component_function`;
CREATE TABLE IF NOT EXISTS `re_component_function` (
  `component_function_id` int(11) NOT NULL AUTO_INCREMENT,
  `component_id` int(11) NOT NULL DEFAULT '0',
  `function_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`component_function_id`),
  UNIQUE KEY `cf_key_idx` (`component_id`,`function_id`),
  UNIQUE KEY `cf_idx` (`component_id`,`function_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_config`;
CREATE TABLE IF NOT EXISTS `re_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `config_key` varchar(255) NOT NULL DEFAULT '',
  `value` text,
  `title` text,
  `sort_order` int(10) UNSIGNED DEFAULT '1',
  `vtype` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `config_key_idx` (`config_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_contactus`;
CREATE TABLE IF NOT EXISTS `re_contactus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fio` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `text` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_country`;
CREATE TABLE IF NOT EXISTS `re_country` (
  `country_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL DEFAULT '',
  `url` varchar(200) NOT NULL DEFAULT '',
  `description` text,
  `meta_title` text,
  `meta_description` text,
  `meta_keywords` text,
  PRIMARY KEY (`country_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_currency`;
CREATE TABLE IF NOT EXISTS `re_currency` (
  `currency_id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(3) NOT NULL,
  `name` varchar(30) NOT NULL,
  `sort_order` tinyint(4) NOT NULL,
  `course` varchar(10) NOT NULL,
  `is_default` tinyint(4) NOT NULL DEFAULT '0',
  `is_active` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`currency_id`),
  UNIQUE KEY `code` (`code`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_customentity`;
CREATE TABLE IF NOT EXISTS `re_customentity` (
  `entity_name` varchar(255) NOT NULL,
  `entity_title` varchar(255) NOT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `alias` varchar(255) DEFAULT NULL,
  `list_tpl` varchar(255) DEFAULT NULL,
  `view_tpl` varchar(255) DEFAULT NULL,
  `sortby` varchar(255) DEFAULT NULL,
  `sortorder` varchar(255) DEFAULT NULL,
  `per_page` int(11) DEFAULT NULL,
  `list_title` varchar(255) DEFAULT NULL,
  `view_title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`entity_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_data`;
CREATE TABLE IF NOT EXISTS `re_data` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `type_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `topic_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `country_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `city_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `metro_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `district_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `price` int(10) UNSIGNED DEFAULT '0',
  `text` text,
  `image` text,
  `contact` text,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `agent_tel` text,
  `room_count` int(11) DEFAULT NULL,
  `elite` int(10) UNSIGNED DEFAULT '0',
  `session_id` text NOT NULL,
  `active` int(11) NOT NULL DEFAULT '0',
  `sub_id1` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `sub_id2` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `reviews_count` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `hot` int(10) UNSIGNED DEFAULT '0',
  `floor` int(11) NOT NULL DEFAULT '0',
  `floor_count` int(11) NOT NULL DEFAULT '0',
  `walls` varchar(255) NOT NULL DEFAULT '',
  `balcony` varchar(255) NOT NULL DEFAULT '',
  `square_all` varchar(255) NOT NULL DEFAULT '',
  `square_live` varchar(255) NOT NULL DEFAULT '',
  `square_kitchen` varchar(255) NOT NULL DEFAULT '',
  `bathroom` varchar(255) NOT NULL DEFAULT '',
  `is_telephone` varchar(255) NOT NULL DEFAULT '',
  `furniture` varchar(255) NOT NULL DEFAULT '',
  `plate` varchar(255) NOT NULL DEFAULT 'нет',
  `agent_email` varchar(255) NOT NULL DEFAULT '',
  `number` varchar(255) NOT NULL DEFAULT '',
  `spec` tinyint(4) NOT NULL DEFAULT '0',
  `floor_cover` text,
  `square_room` text,
  `is_kitchen` tinyint(4) NOT NULL DEFAULT '0',
  `region_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `street_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `planning` text NOT NULL,
  `dom` text NOT NULL,
  `flat_number` text NOT NULL,
  `owner` text NOT NULL,
  `source` text NOT NULL,
  `adv_date` text NOT NULL,
  `more1` text NOT NULL,
  `more2` text NOT NULL,
  `more3` text NOT NULL,
  `youtube` text NOT NULL,
  `fio` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(255) NOT NULL DEFAULT '',
  `realty_type_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `view_count` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `best` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `email` varchar(255) NOT NULL DEFAULT '',
  `distance` varchar(255) NOT NULL DEFAULT '',
  `tmp_password` varchar(255) NOT NULL DEFAULT '',
  `ad_mobile_phone` varchar(255) NOT NULL DEFAULT '',
  `ad_stacionary_phone` varchar(255) NOT NULL DEFAULT '',
  `can_call_start` varchar(255) NOT NULL DEFAULT '',
  `can_call_end` varchar(255) NOT NULL DEFAULT '',
  `currency_id` int(10) UNSIGNED NOT NULL DEFAULT '1',
  `premium_status_end` int(11) NOT NULL DEFAULT '0',
  `bold_status_end` int(11) NOT NULL DEFAULT '0',
  `vip_status_end` int(11) NOT NULL DEFAULT '0',
  `meta_title` text,
  `meta_description` text,
  `meta_keywords` text,
  `image_md5` varchar(255) DEFAULT NULL,
  `image_cache` text,
  `image_parsed` int(11) NOT NULL DEFAULT '0',
  `geo_lat` decimal(9,6) DEFAULT NULL,
  `geo_lng` decimal(9,6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kre_data_FKIndex1` (`district_id`),
  KEY `kre_data_FKIndex3` (`topic_id`),
  KEY `re_data_FKIndex4` (`type_id`),
  KEY `re_data_FKIndex5` (`user_id`),
  KEY `sub_id1` (`sub_id1`),
  KEY `sub_id2` (`sub_id2`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_data_get_rent`;
CREATE TABLE IF NOT EXISTS `re_data_get_rent` (
  `data_get_rent_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `type_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `room_type_id` int(11) NOT NULL DEFAULT '0',
  `time_range_id` int(11) NOT NULL DEFAULT '0',
  `district_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `more` varchar(255) NOT NULL DEFAULT '',
  `date_added` int(11) NOT NULL DEFAULT '0',
  `topic_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `city_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `baby` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `pets` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `foreigner` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`data_get_rent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_data_note`;
CREATE TABLE IF NOT EXISTS `re_data_note` (
  `data_note_id` int(11) NOT NULL AUTO_INCREMENT,
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `added_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `message` text,
  PRIMARY KEY (`data_note_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_district`;
CREATE TABLE IF NOT EXISTS `re_district` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` text,
  `short_name1` text,
  `city_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_dna`;
CREATE TABLE IF NOT EXISTS `re_dna` (
  `group_id` int(11) NOT NULL DEFAULT '0',
  `component_id` int(11) NOT NULL DEFAULT '0',
  `function_id` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `dna_key_idx` (`group_id`,`component_id`,`function_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_function`;
CREATE TABLE IF NOT EXISTS `re_function` (
  `function_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `description` text,
  PRIMARY KEY (`function_id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `function_name_idx` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_gallery`;
CREATE TABLE IF NOT EXISTS `re_gallery` (
  `gallery_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` text,
  `short_description` text,
  `long_description` text,
  `create_date` int(11) NOT NULL DEFAULT '0',
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `gallery_type` int(11) NOT NULL DEFAULT '0',
  `type_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`gallery_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_gallery_image`;
CREATE TABLE IF NOT EXISTS `re_gallery_image` (
  `gallery_image_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `gallery_id` int(11) NOT NULL DEFAULT '0',
  `image_id` int(11) NOT NULL DEFAULT '0',
  `sort_order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`gallery_image_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_group`;
CREATE TABLE IF NOT EXISTS `re_group` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `system_name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`group_id`),
  UNIQUE KEY `system_name` (`system_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_hidden_config`;
CREATE TABLE IF NOT EXISTS `re_hidden_config` (
  `config_key` varchar(255) NOT NULL,
  `config_value` text NOT NULL,
  UNIQUE KEY `conf_param` (`config_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_image`;
CREATE TABLE IF NOT EXISTS `re_image` (
  `image_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `normal` varchar(255) NOT NULL DEFAULT '',
  `preview` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`image_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_ipoteka`;
CREATE TABLE IF NOT EXISTS `re_ipoteka` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fio` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `city_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `workage` varchar(255) NOT NULL DEFAULT '',
  `age` varchar(255) NOT NULL DEFAULT '',
  `kredit` varchar(255) NOT NULL DEFAULT '',
  `cost` varchar(255) NOT NULL DEFAULT '',
  `dohod` varchar(255) NOT NULL DEFAULT '',
  `dohod_per_month` varchar(255) NOT NULL DEFAULT '',
  `vznos` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_land`;
CREATE TABLE IF NOT EXISTS `re_land` (
  `land_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `type_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `coord` text,
  `number` varchar(255) NOT NULL DEFAULT '',
  `function` text,
  `location` text,
  `price` int(11) NOT NULL DEFAULT '0',
  `square` float NOT NULL DEFAULT '0',
  `price_per_unit` float NOT NULL DEFAULT '0',
  `description` text,
  PRIMARY KEY (`land_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_logger`;
CREATE TABLE IF NOT EXISTS `re_logger` (
  `logger_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `log_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `apps_name` varchar(255) NOT NULL DEFAULT '',
  `method` varchar(255) NOT NULL DEFAULT '',
  `message` text,
  `type` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `ipaddr` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`logger_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_mailbox`;
CREATE TABLE IF NOT EXISTS `re_mailbox` (
  `mailbox_id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `reciever_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `theme` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `email` varchar(100) NOT NULL,
  `realty_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `creation_date` datetime NOT NULL,
  PRIMARY KEY (`mailbox_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_memorylist`;
CREATE TABLE IF NOT EXISTS `re_memorylist` (
  `memorylist_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`memorylist_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_memorylist_item`;
CREATE TABLE IF NOT EXISTS `re_memorylist_item` (
  `memorylist_id` int(10) UNSIGNED NOT NULL,
  `id` int(10) UNSIGNED NOT NULL,
  UNIQUE KEY `memorylist_id` (`memorylist_id`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_menu`;
CREATE TABLE IF NOT EXISTS `re_menu` (
  `menu_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `tag` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`menu_id`),
  UNIQUE KEY `tag` (`tag`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_menu_structure`;
CREATE TABLE IF NOT EXISTS `re_menu_structure` (
  `menu_structure_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `name_en` varchar(255) NOT NULL DEFAULT '',
  `name_am` varchar(255) NOT NULL DEFAULT '',
  `name_cn` varchar(255) NOT NULL DEFAULT '',
  `name_ua` varchar(255) NOT NULL DEFAULT '',
  `url` text,
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `menu_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`menu_structure_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_metro`;
CREATE TABLE IF NOT EXISTS `re_metro` (
  `metro_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `city_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`metro_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_news`;
CREATE TABLE IF NOT EXISTS `re_news` (
  `news_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `image` text,
  `date` int(11) NOT NULL DEFAULT '0',
  `img` varchar(255) NOT NULL DEFAULT '',
  `img_preview` varchar(255) NOT NULL DEFAULT '',
  `anons` text,
  `meta_h1` text,
  `meta_title` text,
  `meta_description` text,
  `meta_keywords` text,
  `newsalias` varchar(255) NOT NULL DEFAULT '',
  `news_topic_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`news_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_news_topic`;
CREATE TABLE IF NOT EXISTS `re_news_topic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_oauth`;
CREATE TABLE IF NOT EXISTS `re_oauth` (
  `oauth_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `session_key` varchar(255) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `date_login` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`oauth_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_page`;
CREATE TABLE IF NOT EXISTS `re_page` (
  `page_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `uri` varchar(255) NOT NULL DEFAULT '',
  `meta_keywords` text,
  `meta_description` text,
  `body` text,
  `date` int(11) NOT NULL DEFAULT '0',
  `is_service` int(11) NOT NULL DEFAULT '0',
  `template` varchar(255) DEFAULT NULL,
  `meta_title` text,
  PRIMARY KEY (`page_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_password_recovery`;
CREATE TABLE IF NOT EXISTS `re_password_recovery` (
  `pr_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `recovery_code` varchar(50) NOT NULL,
  PRIMARY KEY (`pr_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_realtylogv2`;
CREATE TABLE IF NOT EXISTS `re_realtylogv2` (
  `realtylog_id` int(11) NOT NULL AUTO_INCREMENT,
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `log_data` longtext NOT NULL,
  `action` varchar(255) NOT NULL,
  `log_date` datetime DEFAULT NULL,
  `editor_id` int(11) NOT NULL,
  PRIMARY KEY (`realtylog_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_region`;
CREATE TABLE IF NOT EXISTS `re_region` (
  `region_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `country_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`region_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_request`;
CREATE TABLE IF NOT EXISTS `re_request` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `time` int(10) UNSIGNED DEFAULT NULL,
  `request` text NOT NULL,
  `name` text NOT NULL,
  `contact` text,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_session`;
CREATE TABLE IF NOT EXISTS `re_session` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `ip` varchar(255) DEFAULT NULL,
  `session_key` varchar(32) NOT NULL DEFAULT '0',
  `start_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_street`;
CREATE TABLE IF NOT EXISTS `re_street` (
  `street_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `prefix` varchar(255) DEFAULT NULL,
  `name` text,
  `district_id` int(11) NOT NULL DEFAULT '0',
  `city_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`street_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_table`;
CREATE TABLE IF NOT EXISTS `re_table` (
  `table_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  PRIMARY KEY (`table_id`),
  UNIQUE KEY `table_name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_table_frontgrid`;
CREATE TABLE IF NOT EXISTS `re_table_frontgrid` (
  `frontgrid_id` int(11) NOT NULL AUTO_INCREMENT,
  `topic_id` text NOT NULL,
  `columns` text NOT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`frontgrid_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_table_gridmanager`;
CREATE TABLE IF NOT EXISTS `re_table_gridmanager` (
  `gridmanager_id` int(11) NOT NULL AUTO_INCREMENT,
  `columns_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`gridmanager_id`),
  KEY `column_id` (`columns_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_table_grids`;
CREATE TABLE IF NOT EXISTS `re_table_grids` (
  `action_code` varchar(255) NOT NULL,
  `grid_fields` text NOT NULL,
  UNIQUE KEY `action_code` (`action_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_table_searchform`;
CREATE TABLE IF NOT EXISTS `re_table_searchform` (
  `searchform_id` int(11) NOT NULL AUTO_INCREMENT,
  `topic_id` text,
  `columns` text NOT NULL,
  `title` varchar(255) NOT NULL,
  `title_en` varchar(255) NOT NULL,
  `title_am` varchar(255) NOT NULL,
  `title_cn` varchar(255) NOT NULL,
  `title_ua` varchar(255) NOT NULL,
  PRIMARY KEY (`searchform_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_topic`;
CREATE TABLE IF NOT EXISTS `re_topic` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `published` int(10) UNSIGNED DEFAULT '1',
  `name` text,
  `name_en` text,
  `name_am` text,
  `name_cn` text,
  `name_ua` text,
  `active` int(10) UNSIGNED DEFAULT '0',
  `parent_id` int(10) UNSIGNED DEFAULT '0',
  `order` int(11) DEFAULT '0',
  `sql_where` text,
  `obj_type_id` int(11) NOT NULL DEFAULT '0',
  `def_id1` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `def_id2` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `operation_type_id` int(11) NOT NULL DEFAULT '0',
  `url` text,
  `description` text,
  `meta_title` text,
  `meta_keywords` text,
  `meta_description` text,
  `public_title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent` (`parent_id`),
  KEY `erased` (`active`),
  KEY `name` (`name`(3))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_upload`;
CREATE TABLE IF NOT EXISTS `re_upload` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `data_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `date` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `file_data` mediumblob,
  `file_size` int(10) UNSIGNED DEFAULT NULL,
  `file_name` text,
  `comment_2` text,
  `ip` varchar(15) NOT NULL DEFAULT '',
  `mime` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `re_upload_FKIndex1` (`data_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_uploadify`;
CREATE TABLE IF NOT EXISTS `re_uploadify` (
  `uploadify_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `session_code` varchar(255) NOT NULL DEFAULT '',
  `file_name` varchar(255) NOT NULL DEFAULT '',
  `element` varchar(255) NOT NULL,
  PRIMARY KEY (`uploadify_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_user`;
CREATE TABLE IF NOT EXISTS `re_user` (
  `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `login` varchar(255) NOT NULL,
  `pass` text NOT NULL,
  `active` int(10) UNSIGNED DEFAULT '0',
  `reg_date` datetime DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `fio` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `account` varchar(255) DEFAULT '0',
  `group_id` int(11) NOT NULL DEFAULT '0',
  `company_id` int(11) NOT NULL DEFAULT '0',
  `phone` varchar(200) NOT NULL DEFAULT '',
  `site` varchar(200) NOT NULL DEFAULT '',
  `imgfile` varchar(200) NOT NULL DEFAULT '',
  `mobile` varchar(200) NOT NULL DEFAULT '',
  `icq` varchar(200) NOT NULL DEFAULT '',
  `notify` int(11) NOT NULL DEFAULT '0',
  `auth_hash` varchar(32) DEFAULT NULL,
  `auth_salt` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `login` (`login`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_userlists`;
CREATE TABLE IF NOT EXISTS `re_userlists` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `id` int(10) UNSIGNED NOT NULL,
  `lcode` varchar(5) NOT NULL,
  UNIQUE KEY `user_id` (`lcode`,`user_id`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_user_blocked_logins`;
CREATE TABLE IF NOT EXISTS `re_user_blocked_logins` (
  `login` varchar(255) NOT NULL,
  `blocked_to` datetime NOT NULL,
  `try_count` tinyint(4) NOT NULL DEFAULT '0',
  UNIQUE KEY `login` (`login`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_version`;
CREATE TABLE IF NOT EXISTS `re_version` (
  `version_id` int(11) NOT NULL AUTO_INCREMENT,
  `version` int(11) NOT NULL DEFAULT '0',
  `code` text,
  `name` text,
  PRIMARY KEY (`version_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `re_yandexrealty_assoc`;
CREATE TABLE IF NOT EXISTS `re_yandexrealty_assoc` (
  `topic_id` int(11) NOT NULL,
  `topic_name` varchar(255) NOT NULL,
  `realty_type` tinyint(4) NOT NULL DEFAULT '0',
  `realty_category` tinyint(4) NOT NULL DEFAULT '0',
  `operation_type` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(1, 'Аренда квартир', 1, 1, 2);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(2, 'Продажа квартир', 1, 1, 1);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(3, 'Новостройки', 1, 1, 1);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(4, 'Коммерческая', 2, 21, 1);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(5, 'Дома-участки', 1, 1, 1);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(6, 'Гаражи', 3, 24, 1);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(10, 'Аренда квартир / Секционки / Комнаты', 1, 2, 2);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(11, 'Аренда квартир / Секционки', 1, 2, 2);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(12, 'Аренда квартир / Гостинки', 1, 1, 2);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(13, 'Аренда квартир / 1-комн.', 1, 1, 2);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(14, 'Аренда квартир / 2-комн.', 1, 1, 2);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(15, 'Аренда квартир / 3-комн.', 1, 1, 2);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(16, 'Аренда квартир / 4-комн.', 1, 1, 2);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(17, 'Аренда квартир / Элитное жилье', 1, 1, 2);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(20, 'Продажа квартир / Комнаты', 1, 2, 1);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(21, 'Продажа квартир / Секционки', 1, 2, 1);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(22, 'Продажа квартир / Гостинки', 1, 1, 1);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(23, 'Продажа квартир / 1-комн.', 1, 1, 1);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(24, 'Продажа квартир / 2-комн.', 1, 1, 1);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(25, 'Продажа квартир / 3-комн.', 1, 1, 1);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(26, 'Продажа квартир / 4-комн.', 1, 1, 1);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(27, 'Продажа квартир / Элитное жилье', 1, 1, 1);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(30, 'Новостройки / Комнаты', 1, 2, 1);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(31, 'Новостройки / Секционки', 1, 2, 1);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(32, 'Новостройки / Гостинки', 1, 1, 1);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(33, 'Новостройки / 1-комн.', 1, 1, 1);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(34, 'Новостройки / 2-комн.', 1, 1, 1);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(35, 'Новостройки / 3-комн.', 1, 1, 1);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(36, 'Новостройки / 4-комн.', 1, 1, 1);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(37, 'Новостройки / Элитное жилье', 1, 1, 1);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(40, 'Коммерческая / Аренда', 2, 21, 2);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(41, 'Коммерческая / Продажа', 2, 21, 1);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(50, 'Дома-участки / Дома', 1, 3, 1);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(51, 'Дома-участки / Коттеджи', 1, 8, 1);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(52, 'Дома-участки / Дачи', 1, 14, 1);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(53, 'Дома-участки / Землеотводы', 3, 16, 1);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(54, 'Дома-участки / Участки', 3, 16, 1);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(60, 'Гаражи / Аренда', 3, 24, 2);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(61, 'Гаражи / Продажа', 3, 24, 1);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(4010, 'Коммерческая / Аренда / Офисы', 2, 21, 2);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(4020, 'Коммерческая / Аренда / Торговые площади', 2, 22, 2);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(4110, 'Коммерческая / Продажа / Офисы', 2, 21, 1);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(4120, 'Коммерческая / Продажа / Магазины', 2, 22, 1);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(4130, 'Коммерческая / Продажа / Торговые площади', 2, 22, 1);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(6010, 'Гаражи / Аренда / Гараж', 3, 24, 2);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(6020, 'Гаражи / Аренда / Автобокс', 3, 24, 2);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(6110, 'Гаражи / Продажа / Гараж', 3, 24, 1);
INSERT INTO `re_yandexrealty_assoc` (`topic_id`, `topic_name`, `realty_type`, `realty_category`, `operation_type`) VALUES(6120, 'Гаражи / Продажа / Автобокс', 3, 24, 1);
