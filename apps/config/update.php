<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
class config_update extends SiteBill {

    /**
     * Construct
     */
    function __construct() {
        $this->sitebill();
    }

    function main() {

        $rs = '';

        $DBC = DBC::getInstance();
        $query_data[] = "ALTER TABLE " . DB_PREFIX . "_config ADD COLUMN `vtype` INT(11) DEFAULT 0";
        $query_data[] = "ALTER TABLE " . DB_PREFIX . "_config ADD COLUMN `public` INT(11) DEFAULT 0";
        $query_data[] = "ALTER TABLE " . DB_PREFIX . "_config ADD COLUMN `params` TEXT DEFAULT ''";
        foreach ($query_data as $query) {
            $success = false;
            $stmt = $DBC->query($query, array(), $rows, $success);
            if (!$success) {
                $rs .= Multilanguage::_('ERROR_ON_SQL_RUN', 'system') . ': ' . $query . '<br>';
            } else {
                $rs .= Multilanguage::_('QUERY_SUCCESS', 'system') . ': ' . $query . '<br>';
            }
        }

        $rs .= Multilanguage::_('UPDATE_CONFIG_SUCCESS', 'system') . '<br>';

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/config/admin/admin.php');
        $config_admin = new config_admin();
        $config_admin->check_config_structure();
        $config_admin->install_hidden_config();
        // Устанавливаем параметрам публичный доступ
        $config_admin->set_public_access('allow_register_account');
        $config_admin->set_public_access('allow_remind_password');


        //Меняем тип полей-чекбоксов на правильное значение vtype
        $checkboxfields = array(
            'core.listing.pager_draw_all',
            'core.listing.pager_end_buttons',
            'core.listing.pager_prev_buttons',
            'core.listing.pager_show_prefixes',
            'currency_enable',
            'allow_login_account',
            'allow_register_account',
            'allow_remind_password',
            'use_captcha_admin_entry',
            'work_on_https',
            'moderate_first',
            'hide_contact_input_user_data',
            'use_realty_view_counter',
            'enable_special_in_account',
            'enable_curator_mode',
            'enable_coworker_mode',
            'curator_mode_fullaccess',
            'curator_mode_chainsallow',
            'use_new_realty_grid',
            'notify_admin_about_register',
            'notify_about_added_realty',
            'filter_double_data',
            'check_permissions',
            'allow_user_email_change',
            'use_registration_email_confirm',
            'use_registration_sms_confirm',
            'registration_notice',
            'notify_about_publishing',
            'post_form_agreement_enable',
            'post_form_agreement_enable_note',
            'register_form_agreement_enable',
            'register_form_agreement_enable_ch',
            'is_watermark',
            'hide_empty_catalog',
            'user_account_enable',
            'seo_photo_name_enable',
            'autocomplete_distinct',
            'set_cookie_subdomenal',
            'apps.realty.allow_notactive_direct',
            'user_pic_smart',
            'classic_local_grid',
            'classic_local_view',
            'robokassa_pay_enable',
            'robokassa_by_frekassa',
            'dontclean_uploadify_table',
            'apps.realty.update_date_added',
            'disable_guest_add',
            'apps.realty.similar_grid',
            'core.listing.add_user_info',
            'admin_grid_leftbuttons',
            'data_adv_share_access',
            'data_adv_share_access_user_list_strict',
            'data_adv_share_access_can_view_all',
            'apps.realty.use_predeleting',
            'apps.realty.archived_notactive',
            'add_pagenumber_title',
            'is_underconstruction',
            'notify_about_payment',
            'apps.realty.off_system_ajax',
            'disable_mail_additionals',
            'save_without_watermark',
            'apps.realty.preview_smart_resizing',
            'block_user_search_forms',
            'block_user_front_grids',
            'show_up_icon',
            'show_cattree_left',
            'ignore_free_from_parameter',
            'disable_root_structure_select',
            'use_combobox',
            'apps.socialauth.vk.enable',
            'apps.socialauth.fb.enable',
            'apps.accountsms.enable',
            'apps.registersms.enable',
            'apps.yandexrealty_parser.default_activity_status',
            'apps.yandexrealty_parser.allow_create_new_category',
            'apps.twitter.enable',
            'apps.realtypro.show_contact.enable',
            'apps.watermark.enable',
            'apps.watermark.preview_enable',
            'apps.watermark.printanywhere',
            'apps.shoplog.enable',
            'apps.rabota.enable',
            'apps.freeorder.enable',
            'apps.shopstat.enable',
            'apps.orderhistory.enable',
            'apps.fasteditor.enable',
            'apps.shop.city_enable',
            'apps.realtybuyorder.enable',
            'apps.realtycsv.enable',
            'apps.realtylog.enable',
            'apps.shop.enable',
            'apps.page.enable',
            'apps.realtypro.youtube',
            'apps.news.enable',
            'apps.plan.enable',
            'apps.balcony.enable',
            'apps.sanuzel.enable',
            'apps.billing.enable',
            'apps.realtyspecial.enable',
            'apps.realtypro.enable',
            'apps.company.enable',
            'apps.company.best',
            'apps.realty.ajax_region_refresh',
            'apps.realty.ajax_city_refresh',
            'apps.realty.ajax_district_refresh',
            'apps.realty.ajax_metro_refresh',
            'apps.realty.ajax_street_refresh',
            'country_in_form',
            'region_in_form',
            'city_in_form',
            'metro_in_form',
            'district_in_form',
            'street_in_form',
            'optype_in_form',
            'link_street_to_city',
            'user_add_street_enable',
            'allow_callme_timelimits',
            'allow_additional_stationary_number',
            'allow_additional_mobile_number',
            'ajax_form_in_admin',
            'ajax_form_in_user',
            'allow_tags_search_frontend',
            'robokassa_testmode',
            'use_smtp',
            'use_smtp_ssl',
            'show_demo_banners',
            'use_topic_publish_status',
            'use_topic_linker',
            'email_as_login',
            'query_cache_enable',
            'use_native_file_name_on_uploadify',
            'apps_cache_disable',
            'sql_paranoid_mode',
            'dadata_autocomplete_force',
            'apps.realty.enable_guest_mode',
            'apps.realty.enable_toolbar',
            'apps.realty.enable_navbar',
            'apps.realty.show_home_icon',
            'apps.realty.search_string_parser.enable',
            'apps.realty.min_filter_reset_count',
            'apps.realty.grid.enable_grouping',
            'apps.realty.data.disable_edit',
            'apps.realty.data.global_freeze_default_columns_list',
            'apps.realty.data.global_disable_refresh_button',
            'use_vue',
            'allow_register_admin',
            'apps.account.enable',
            'app.billing.enable',
            'apps.agentphones.enable',
            'apps.cache.enable',
            'apps.company.timelimit',
            'apps.complaint.enable',
            'apps.faq.enable',
            'apps.mapviewer.enable',
            'apps.rss.enable',
            'apps.search.enable',
            'apps.shop.user_limit_enable',
            'apps.sms.test_mode',
            'apps.yml.store',
            'autoreg_enable',
            'more_fields_in_lk',
            'show_admin_helper',
            'apps.seo.level_enable',
            'apps.seo.html_prefix_enable',
            'allow_topic_images',
            'divide_step_form',
            'apps.geodata.enable',
            'apps.geodata.geocode_partial',
            'apps.mailbox.enable',
            'apps.mysearch.enable',
            'apps.sitemap.data_enable',
            'apps.sitemap.company_enable',
            'apps.booking.enable',
            'link_metro_to_district',
            'use_custom_addform',
            'apps.client.enable',
            'apps.comment.enable',
            'apps.company.profile_in_lk',
            'apps.geodata.on_home',
            'apps.geodata.show_grid_map',
            'apps.getrent.enable',
            'apps.news.use_news_topics',
            'apps.accessorc.enable',
            'apps.accessorc.free_mode',
            'apps.accessorc.use_captcha',
            'apps.accessorc.use_phone',
            'apps.accessorc.keys_ranged',
            'apps.accessorc.simple_check',
            'apps.admin3.enable',
            'apps.adsapiruparser.withphotoonly',
            'apps.adsapiruparser.store_parsing_story',
            'apps.adsapiruparser.setfeeddate',
            'apps.adsapiruparser.filter_doubles',
            'apps.adsapiruparser.autoclear',
            'apps.adsapiruparser.update_advs',
            'apps.adsapiruparser.avitobigpic'
        );

        $query = 'UPDATE '.DB_PREFIX.'_config SET `vtype` = 1 WHERE `config_key` IN ('.implode(', ', array_fill(0, count($checkboxfields), '?')).')';
        $stmt = $DBC->query($query, array_values($checkboxfields));

        $knownselectboxes = array(
            'apps.language.autotrans_api' => array('0'=>'Google', '1'=>'Yandex'),
            'apps.yandexrealty.newflat' => array('0'=>'полю new_flat', '1'=>'из приложения ЖК', '2'=>'другому полю'),
            'captcha_type' => array('0'=>'стандартная', '2'=>'игнорировать капчу', '3'=>'KCaptcha', '4'=>'reCaptcha'),
            'add_pagenumber_title_place' => array('0'=>'заголовок на странице','1'=>'МЕТА-заголовок','2'=>'во все заголовки'),
            'apps.sitemap.changefreq.menu' => array('1'=>'всегда','2'=>'ежечасно','3'=>'ежедневно','4'=>'еженедельно','5'=>'ежемесячно','6'=>'ежегодно','7'=>'никогда'),
            'apps.sitemap.changefreq.news' => array('1'=>'всегда','2'=>'ежечасно','3'=>'ежедневно','4'=>'еженедельно','5'=>'ежемесячно','6'=>'ежегодно','7'=>'никогда'),
            'apps.sitemap.changefreq.page' => array('1'=>'всегда','2'=>'ежечасно','3'=>'ежедневно','4'=>'еженедельно','5'=>'ежемесячно','6'=>'ежегодно','7'=>'никогда'),
            'apps.sitemap.changefreq.topic' => array('1'=>'всегда','2'=>'ежечасно','3'=>'ежедневно','4'=>'еженедельно','5'=>'ежемесячно','6'=>'ежегодно','7'=>'никогда'),
            'apps.watermark.position' => array('center'=>'центр','top-left'=>'верх-лево','top-right'=>'верх-право','bottom-left'=>'низ-лево','bottom-right'=>'низ-право'),
            'apps.yml.delivery' => array('true'=>'true','false'=>'false'),
            'apps.yml.pickup' => array('true'=>'true','false'=>'false'),
            'editor' => array('cleditor'=>'cleditor','ckeditor'=>'ckeditor','codemirror'=>'codemirror'),
            'menu_type' => array('purecss'=>'purecss','slidemenu'=>'slidemenu','megamenu'=>'megamenu'),
            'uploader_type' => array('uploadify'=>'uploadify','pluploader'=>'pluploader'),
            'use_google_map' => array('0'=>'Yаndex','1'=>'Google','2'=>'Leaflet OSM'),
            'apps.sitemap.changefreq.data' => array('1'=>'всегда','2'=>'ежечасно','3'=>'ежедневно','4'=>'еженедельно','5'=>'ежемесячно','6'=>'ежегодно','7'=>'никогда'),
            'apps.sitemap.changefreq.company' => array('1'=>'всегда','2'=>'ежечасно','3'=>'ежедневно','4'=>'еженедельно','5'=>'ежемесячно','6'=>'ежегодно','7'=>'никогда'),
            'disable_root_structure_select' => array('0'=>'не блокировать','1'=>'только верхний уровень','2'=>'все не крайние разделы'),
            'date_format' => array('standart'=>'standart','eu'=>'EU','us'=>'US'),
        );




        if(!empty($knownselectboxes)){

            $query = 'SELECT `config_key`, `params` FROM '.DB_PREFIX.'_config WHERE `config_key` IN ('.implode(', ', array_fill(0, count($knownselectboxes), '?')).')';
            $stmt = $DBC->query($query, array_keys($knownselectboxes));
            if($stmt){
                while($ar = $DBC->fetch($stmt)){
                    if($ar['params'] != ''){
                        $ar['params'] = json_decode($ar['params'], true);
                    }else{
                        $ar['params'] = array();
                    }
                    $existing[$ar['config_key']] = $ar['params'];
                }
            }


            $query = 'UPDATE '.DB_PREFIX.'_config SET `vtype` = ?, `params` = ? WHERE `config_key` = ?';
            foreach ($knownselectboxes as $name => $selectdata){
                if(isset($existing[$name])){
                    $existing[$name]['params']['select_data'] = $selectdata;
                    $stmt = $DBC->query($query, array(2, json_encode($existing[$name]['params']), $name));
                }
            }
        }

        return $rs;
    }

}
