<?php
class Config_Mask {
	
	public static $themes_array=null;
	
	public function get_model(){
		
		$data_model = array();
        

        /*
        $data_model['apps.news.user_enable_access_type']['name'] = 'apps.news.user_enable_access_type';
		$data_model['apps.news.user_enable_access_type']['type'] = 'select_box';
		$data_model['apps.news.user_enable_access_type']['select_data'] = array('0'=>'никому', '1'=>'не установлено', '2'=>'определенным группам', '3'=>'по признаку в профиле');
		*/


		$data_model['add_notification_email']['name'] = 'add_notification_email';
		$data_model['add_notification_email']['title'] = 'E-mail для получения уведомлений о новых объявлениях (при отсутствии изпользуется order_email_acceptor)';
		$data_model['add_notification_email']['value'] = '';
		$data_model['add_notification_email']['type'] = 'safe_string';
		
		$data_model['advert_cost']['name'] = 'advert_cost';
		$data_model['advert_cost']['title'] = 'Стоимость размещения одного простого объявления';
		$data_model['advert_cost']['value'] = '';
		$data_model['advert_cost']['type'] = 'safe_string';
		
		$data_model['ajax_auth_form']['name'] = 'ajax_auth_form';
		$data_model['ajax_auth_form']['title'] = 'Сервер авторизации sitebill.ru';
		$data_model['ajax_auth_form']['value'] = '';
		$data_model['ajax_auth_form']['type'] = 'safe_string';
		/*

		
		$data_model['allow_register_admin']['name'] = 'allow_register_admin';
		$data_model['allow_register_admin']['title'] = 'Разрешить регистрацию в админ. панели';
		$data_model['allow_register_admin']['value'] = '';
		$data_model['allow_register_admin']['type'] = 'checkbox';
		

		
		$data_model['app.billing.enable']['name'] = 'app.billing.enable';
		$data_model['app.billing.enable']['title'] = 'Включить Billing.Apps';
		$data_model['app.billing.enable']['value'] = '';
		$data_model['app.billing.enable']['type'] = 'checkbox';
		
		$data_model['apps.account.enable']['name'] = 'apps.account.enable';
		$data_model['apps.account.enable']['title'] = 'Активировать приложение apps.account';
		$data_model['apps.account.enable']['value'] = '';
		$data_model['apps.account.enable']['type'] = 'checkbox';

		
		$data_model['apps.cache.enable']['name'] = 'apps.cache.enable';
		$data_model['apps.cache.enable']['title'] = 'Включить кеш';
		$data_model['apps.cache.enable']['value'] = '';
		$data_model['apps.cache.enable']['type'] = 'checkbox';

		*/

		
		$data_model['apps.fasteditor.email_send_password_text']['name'] = 'apps.fasteditor.email_send_password_text';
		$data_model['apps.fasteditor.email_send_password_text']['title'] = 'Текст сообщения на почту с паролем для редактирования ( {password} указывает место размещения пароля в тексте сообщения)';
		$data_model['apps.fasteditor.email_send_password_text']['value'] = '';
		$data_model['apps.fasteditor.email_send_password_text']['type'] = 'safe_string';

		$data_model['apps.fasteditor.sms_send_password_text']['name'] = 'apps.fasteditor.sms_send_password_text';
		$data_model['apps.fasteditor.sms_send_password_text']['title'] = 'Текст sms сообщения с паролем для редактирования ( {password} указывает место размещения пароля в тексте сообщения)';
		$data_model['apps.fasteditor.sms_send_password_text']['value'] = '';
		$data_model['apps.fasteditor.sms_send_password_text']['type'] = 'safe_string';
		
		$data_model['apps.fasteditor.sms_send_password_text_long']['name'] = 'apps.fasteditor.sms_send_password_text_long';
		$data_model['apps.fasteditor.sms_send_password_text_long']['title'] = '(Длинное) Текст sms сообщения с паролем для редактирования ( {password} указывает место размещения пароля в тексте сообщения)';
		$data_model['apps.fasteditor.sms_send_password_text_long']['value'] = '';
		$data_model['apps.fasteditor.sms_send_password_text_long']['type'] = 'safe_string';



		
		$data_model['apps.newsparser_rbc.portion']['name'] = 'apps.newsparser_rbc.portion';
		$data_model['apps.newsparser_rbc.portion']['title'] = 'Количество новостей обрабатываемых за один проход';
		$data_model['apps.newsparser_rbc.portion']['value'] = '';
		$data_model['apps.newsparser_rbc.portion']['type'] = 'safe_string';

		$data_model['apps.realtybuyorder.text_after_send']['name'] = 'apps.realtybuyorder.text_after_send';
		$data_model['apps.realtybuyorder.text_after_send']['title'] = 'Текст после заказа через Realtybuyorder';
		$data_model['apps.realtybuyorder.text_after_send']['value'] = '';
		$data_model['apps.realtybuyorder.text_after_send']['type'] = 'safe_string';

		$data_model['apps.realtypro.admin.items_per_page']['name'] = 'apps.realtypro.admin.items_per_page';
		$data_model['apps.realtypro.admin.items_per_page']['title'] = 'Недвижимость. Админка. Количество позиций на странице';
		$data_model['apps.realtypro.admin.items_per_page']['value'] = '';
		$data_model['apps.realtypro.admin.items_per_page']['type'] = 'safe_string';
		/*

		
		$data_model['apps.realtypro.youtube']['name'] = 'apps.realtypro.youtube';
		$data_model['apps.realtypro.youtube']['title'] = 'Разрешить youtube-ролики в объявлении';
		$data_model['apps.realtypro.youtube']['value'] = '';
		$data_model['apps.realtypro.youtube']['type'] = 'checkbox';
		
		$data_model['apps.realtypro.youtube']['name'] = 'apps.realtypro.youtube';
		$data_model['apps.realtypro.youtube']['title'] = 'Включить авторегистрацию';
		$data_model['apps.realtypro.youtube']['value'] = '';
		$data_model['apps.realtypro.youtube']['type'] = 'checkbox';

		*/
		$data_model['apps.registersms.first_category_price']['name'] = 'apps.registersms.first_category_price';
		$data_model['apps.registersms.first_category_price']['title'] = 'Стоимость первой категории';
		$data_model['apps.registersms.first_category_price']['value'] = '';
		$data_model['apps.registersms.first_category_price']['type'] = 'safe_string';
		
		$data_model['apps.registersms.next_category_price']['name'] = 'apps.registersms.next_category_price';
		$data_model['apps.registersms.next_category_price']['title'] = 'Стоимость категорий после первой';
		$data_model['apps.registersms.next_category_price']['value'] = '';
		$data_model['apps.registersms.next_category_price']['type'] = 'safe_string';

		$data_model['apps.shop.admin.products_per_page']['name'] = 'apps.shop.admin.products_per_page';
		$data_model['apps.shop.admin.products_per_page']['title'] = 'Магазин. Количество продуктов на странице в админке';
		$data_model['apps.shop.admin.products_per_page']['value'] = '';
		$data_model['apps.shop.admin.products_per_page']['type'] = 'safe_string';

		$data_model['apps.shop.current_city_id']['name'] = 'apps.shop.current_city_id';
		$data_model['apps.shop.current_city_id']['title'] = 'ID текущего города';
		$data_model['apps.shop.current_city_id']['value'] = '';
		$data_model['apps.shop.current_city_id']['type'] = 'safe_string';

		$data_model['apps.shop.front.products_per_page']['name'] = 'apps.shop.front.products_per_page';
		$data_model['apps.shop.front.products_per_page']['title'] = 'Магазин. Количество продуктов на странице в ЛК пользователя';
		$data_model['apps.shop.front.products_per_page']['value'] = '';
		$data_model['apps.shop.front.products_per_page']['type'] = 'safe_string';
		
		$data_model['apps.shop.mail_title']['name'] = 'apps.shop.mail_title';
		$data_model['apps.shop.mail_title']['title'] = 'Название магазина (будет указано в заголовке писем о заказах)';
		$data_model['apps.shop.mail_title']['value'] = '';
		$data_model['apps.shop.mail_title']['type'] = 'safe_string';
		
		$data_model['apps.shop.recipients_list']['name'] = 'apps.shop.recipients_list';
		$data_model['apps.shop.recipients_list']['title'] = 'Магазин. Список уведомляемых получателей при добавлении объявления пользователем';
		$data_model['apps.shop.recipients_list']['value'] = '';
		$data_model['apps.shop.recipients_list']['type'] = 'safe_string';
		/*
		$data_model['apps.shop.user_limit_enable']['name'] = 'apps.shop.user_limit_enable';
		$data_model['apps.shop.user_limit_enable']['title'] = 'Активировать режим временных ограничений пользовательских публикаций';
		$data_model['apps.shop.user_limit_enable']['value'] = '';
		$data_model['apps.shop.user_limit_enable']['type'] = 'checkbox';
		*/

		$data_model['apps.sitemap.priority.menu']['name'] = 'apps.sitemap.priority.menu';
		$data_model['apps.sitemap.priority.menu']['title'] = 'Приоритетность URL дополнительных меню относительно других URL на Вашем сайте. Диапазон от 0,0 до 1,0';
		$data_model['apps.sitemap.priority.menu']['value'] = '';
		$data_model['apps.sitemap.priority.menu']['type'] = 'safe_string';
		
		$data_model['apps.sitemap.priority.news']['name'] = 'apps.sitemap.priority.news';
		$data_model['apps.sitemap.priority.news']['title'] = 'Приоритетность URL раздела новостей относительно других URL на Вашем сайте. Диапазон от 0,0 до 1,0';
		$data_model['apps.sitemap.priority.news']['value'] = '';
		$data_model['apps.sitemap.priority.news']['type'] = 'safe_string';
		
		$data_model['apps.sitemap.priority.page']['name'] = 'apps.sitemap.priority.page';
		$data_model['apps.sitemap.priority.page']['title'] = 'Приоритетность URL статических страниц относительно других URL на Вашем сайте. Диапазон от 0,0 до 1,0';
		$data_model['apps.sitemap.priority.page']['value'] = '';
		$data_model['apps.sitemap.priority.page']['type'] = 'safe_string';
		
		$data_model['apps.sitemap.priority.topic']['name'] = 'apps.sitemap.priority.topic';
		$data_model['apps.sitemap.priority.topic']['title'] = 'Приоритетность URL категорий относительно других URL на Вашем сайте. Диапазон от 0,0 до 1,0';
		$data_model['apps.sitemap.priority.topic']['value'] = '';
		$data_model['apps.sitemap.priority.topic']['type'] = 'safe_string';
		
		$data_model['apps.sms.apikey']['name'] = 'apps.sms.apikey';
		$data_model['apps.sms.apikey']['title'] = 'Имя отправителя в SMS отправленных через SMSPilot';
		$data_model['apps.sms.apikey']['value'] = '';
		$data_model['apps.sms.apikey']['type'] = 'safe_string';
		
		$data_model['apps.sms.max_uses']['name'] = 'apps.sms.max_uses';
		$data_model['apps.sms.max_uses']['title'] = 'Количество использований SMS-напоминания (0 или ничего - без ограничений)';
		$data_model['apps.sms.max_uses']['value'] = '';
		$data_model['apps.sms.max_uses']['type'] = 'safe_string';
		
		$data_model['apps.sms.sender']['name'] = 'apps.sms.sender';
		$data_model['apps.sms.sender']['title'] = 'SMSPilot API ключ. Можно получить по адресу http://www.smspilot.ru/apikey.php';
		$data_model['apps.sms.sender']['value'] = 'XXXXXXXXXXXXYYYYYYYYYYYYZZZZZZZZXXXXXXXXXXXXYYYYYYYYYYYYZZZZZZZZ';
		$data_model['apps.sms.sender']['type'] = 'safe_string';

		/*
		$data_model['apps.watermark.enable']['name'] = 'apps.watermark.enable';
		$data_model['apps.watermark.enable']['title'] = 'Включить приложение Apps.WatermarkPrinter';
		$data_model['apps.watermark.enable']['value'] = '0';
		$data_model['apps.watermark.enable']['type'] = 'checkbox';
		*/
		$data_model['apps.watermark.image']['name'] = 'apps.watermark.image';
		$data_model['apps.watermark.image']['title'] = 'Название файла изображения для водяного знака, путь до картинок /img/watermark/';
		$data_model['apps.watermark.image']['value'] = 'watermark.gif';
		$data_model['apps.watermark.image']['type'] = 'safe_string';
		
		$data_model['apps.watermark.offset_bottom']['name'] = 'apps.watermark.offset_bottom';
		$data_model['apps.watermark.offset_bottom']['title'] = 'Отступ принта снизу, px';
		$data_model['apps.watermark.offset_bottom']['value'] = '5';
		$data_model['apps.watermark.offset_bottom']['type'] = 'safe_string';
		
		$data_model['apps.watermark.offset_left']['name'] = 'apps.watermark.offset_left';
		$data_model['apps.watermark.offset_left']['title'] = 'Отступ принта слева, px';
		$data_model['apps.watermark.offset_left']['value'] = '5';
		$data_model['apps.watermark.offset_left']['type'] = 'safe_string';
		
		$data_model['apps.watermark.offset_right']['name'] = 'apps.watermark.offset_right';
		$data_model['apps.watermark.offset_right']['title'] = 'Отступ принта справа, px';
		$data_model['apps.watermark.offset_right']['value'] = '5';
		$data_model['apps.watermark.offset_right']['type'] = 'safe_string';
		
		$data_model['apps.watermark.offset_top']['name'] = 'apps.watermark.offset_top';
		$data_model['apps.watermark.offset_top']['title'] = 'Отступ принта сверху, px';
		$data_model['apps.watermark.offset_top']['value'] = '5';
		$data_model['apps.watermark.offset_top']['type'] = 'safe_string';

		$data_model['apps.yandexrealty_parser.category_for_all']['name'] = 'apps.yandexrealty_parser.category_for_all';
		$data_model['apps.yandexrealty_parser.category_for_all']['title'] = 'ID категории, которая будет сопоставлена добавляемой записи в случае apps.yandexrealty_parser.allow_create_new_category=0';
		$data_model['apps.yandexrealty_parser.category_for_all']['value'] = '1000';
		$data_model['apps.yandexrealty_parser.category_for_all']['type'] = 'safe_string';

		$data_model['apps.yandexrealty_parser.default_user_id']['name'] = 'apps.yandexrealty_parser.default_user_id';
		$data_model['apps.yandexrealty_parser.default_user_id']['title'] = 'ID пользователя по умолчанию. Если 0, то ID пользователя будет браться из таблицы доменов. Если не 0, то в качестве user_id для позиции будет использоваться это значение.';
		$data_model['apps.yandexrealty_parser.default_user_id']['value'] = '0';
		$data_model['apps.yandexrealty_parser.default_user_id']['type'] = 'safe_string';
		
		$data_model['apps.yml.company_name']['name'] = 'apps.yml.company_name';
		$data_model['apps.yml.company_name']['title'] = 'Полное наименование компании';
		$data_model['apps.yml.company_name']['value'] = 'Some Company';
		$data_model['apps.yml.company_name']['type'] = 'safe_string';

		$data_model['apps.yml.local_delivery_cost']['name'] = 'apps.yml.local_delivery_cost';
		$data_model['apps.yml.local_delivery_cost']['title'] = 'Cтоимость доставки для своего региона';
		$data_model['apps.yml.local_delivery_cost']['value'] = '';
		$data_model['apps.yml.local_delivery_cost']['type'] = 'safe_string';

		$data_model['apps.yml.shop_development_team']['name'] = 'apps.yml.shop_development_team';
		$data_model['apps.yml.shop_development_team']['title'] = 'Наименование агентства, которое оказывает техническую поддержку интернет-магазину';
		$data_model['apps.yml.shop_development_team']['value'] = 'Some Dev Team';
		$data_model['apps.yml.shop_development_team']['type'] = 'safe_string';
		
		$data_model['apps.yml.shop_development_team_email']['name'] = 'apps.yml.shop_development_team_email';
		$data_model['apps.yml.shop_development_team_email']['title'] = 'Контактный адрес разработчиков CMS';
		$data_model['apps.yml.shop_development_team_email']['value'] = 'Some Email';
		$data_model['apps.yml.shop_development_team_email']['type'] = 'safe_string';
		
		$data_model['apps.yml.shop_name']['name'] = 'apps.yml.shop_name';
		$data_model['apps.yml.shop_name']['title'] = 'Короткое название магазина';
		$data_model['apps.yml.shop_name']['value'] = 'Some Shop';
		$data_model['apps.yml.shop_name']['type'] = 'safe_string';
		
		$data_model['apps.yml.shop_platform_name']['name'] = 'apps.yml.shop_platform_name';
		$data_model['apps.yml.shop_platform_name']['title'] = 'Система управления контентом';
		$data_model['apps.yml.shop_platform_name']['value'] = 'Some CMS';
		$data_model['apps.yml.shop_platform_name']['type'] = 'safe_string';
		
		$data_model['apps.yml.shop_platform_version']['name'] = 'apps.yml.shop_platform_version';
		$data_model['apps.yml.shop_platform_version']['title'] = 'Версия CMS';
		$data_model['apps.yml.shop_platform_version']['value'] = '1.0';
		$data_model['apps.yml.shop_platform_version']['type'] = 'safe_string';

		$data_model['app_gallery_photos_per_page']['name'] = 'app_gallery_photos_per_page';
		$data_model['app_gallery_photos_per_page']['title'] = 'Галерея: Количество фотографий на страницу';
		$data_model['app_gallery_photos_per_page']['value'] = '5';
		$data_model['app_gallery_photos_per_page']['type'] = 'safe_string';
		/*
		$data_model['autoreg_enable']['name'] = 'autoreg_enable';
		$data_model['autoreg_enable']['title'] = 'Включить авторегистрацию';
		$data_model['autoreg_enable']['value'] = '0';
		$data_model['autoreg_enable']['type'] = 'checkbox';
		*/
		$data_model['city']['name'] = 'city';
		$data_model['city']['title'] = 'Город (для отображения на карте yandex)';
		$data_model['city']['value'] = 'Красноярск';
		$data_model['city']['type'] = 'safe_string';

		$data_model['common_per_page']['name'] = 'common_per_page';
		$data_model['common_per_page']['title'] = 'Количество позиций на страницу';
		$data_model['common_per_page']['value'] = '10';
		$data_model['common_per_page']['type'] = 'safe_string';

		$data_model['default_topic']['name'] = 'default_topic';
		$data_model['default_topic']['title'] = 'Категория по-умолчанию (выводится на главной)';
		$data_model['default_topic']['value'] = '2';
		$data_model['default_topic']['type'] = 'safe_string';

		$data_model['editor1']['name'] = 'editor1';
		$data_model['editor1']['title'] = 'WYSIWYG-редактор1';
		$data_model['editor1']['value'] = 'bbeditor';
		$data_model['editor1']['type'] = 'safe_string';

		$data_model['hide_topic_list']['name'] = 'hide_topic_list';
		$data_model['hide_topic_list']['title'] = 'Список идентификаторов категорий, в которых контакты в объявлениях спрятаны от простых пользователей';
		$data_model['hide_topic_list']['value'] = '14';
		$data_model['hide_topic_list']['type'] = 'safe_string';

		$data_model['license_key']['name'] = 'license_key';
		$data_model['license_key']['title'] = 'Лицензионный ключ';
		$data_model['license_key']['value'] = '4dcf-51-61d8ffe5-a02720-535d39-62cc843e';
		$data_model['license_key']['type'] = 'safe_string';

        /*
		$data_model['more_fields_in_lk']['name'] = 'more_fields_in_lk';
		$data_model['more_fields_in_lk']['title'] = 'Дополнительные поля в личном кабинете риелтора';
		$data_model['more_fields_in_lk']['value'] = '0';
		$data_model['more_fields_in_lk']['type'] = 'checkbox';
		*/
		$data_model['news_count_in_column']['name'] = 'news_count_in_column';
		$data_model['news_count_in_column']['title'] = 'Количество новостей в колонке';
		$data_model['news_count_in_column']['value'] = '5';
		$data_model['news_count_in_column']['type'] = 'safe_string';

		$data_model['order_email_acceptor']['name'] = 'order_email_acceptor';
		$data_model['order_email_acceptor']['title'] = 'Email на который будут приходить заявки с сайта';
		$data_model['order_email_acceptor']['value'] = 'kondin@etown.ru';
		$data_model['order_email_acceptor']['type'] = 'safe_string';
		
		$data_model['per_page']['name'] = 'per_page';
		$data_model['per_page']['title'] = 'Количество объявлений на одну страницу';
		$data_model['per_page']['value'] = '5';
		$data_model['per_page']['type'] = 'safe_string';
		
		$data_model['per_page_admin']['name'] = 'per_page_admin';
		$data_model['per_page_admin']['title'] = 'Количество объявлений на страницу в админке';
		$data_model['per_page_admin']['value'] = '20';
		$data_model['per_page_admin']['type'] = 'safe_string';
		
		$data_model['photo_per_data']['name'] = 'photo_per_data';
		$data_model['photo_per_data']['title'] = 'Количество изображений для одного объекта (0 или ничего - без ограничений)';
		$data_model['photo_per_data']['value'] = '0';
		$data_model['photo_per_data']['type'] = 'safe_string';

		$data_model['post_form_agreement_text']['name'] = 'post_form_agreement_text';
		$data_model['post_form_agreement_text']['title'] = 'Текст соглашения после формы';
		$data_model['post_form_agreement_text']['value'] = 'Я, ознакомлен(а), что данная заявка будет доставлена по всем Агентствам недвижимости которые зарегистрированы на сайте.';
		$data_model['post_form_agreement_text']['type'] = 'safe_string';
		
		$data_model['post_form_agreement_text_add']['name'] = 'post_form_agreement_text_add';
		$data_model['post_form_agreement_text_add']['title'] = 'Текст соглашения после формы добавления объявления';
		$data_model['post_form_agreement_text_add']['value'] = 'Я,  ознакомлен(а) с Пользовательским соглашением';
		$data_model['post_form_agreement_text_add']['type'] = 'safe_string';

		$data_model['robokassa_login']['name'] = 'robokassa_login';
		$data_model['robokassa_login']['title'] = 'Логин для robokassa.ru';
		$data_model['robokassa_login']['value'] = 'robokassa_login';
		$data_model['robokassa_login']['type'] = 'safe_string';
		
		$data_model['robokassa_password1']['name'] = 'robokassa_password1';
		$data_model['robokassa_password1']['title'] = 'Пароль 1 для robokassa.ru';
		$data_model['robokassa_password1']['value'] = 'robokassa_password1';
		$data_model['robokassa_password1']['type'] = 'safe_string';
		
		$data_model['robokassa_password2']['name'] = 'robokassa_password2';
		$data_model['robokassa_password2']['title'] = 'Пароль 2 для robokassa.ru';
		$data_model['robokassa_password2']['value'] = 'robokassa_password2';
		$data_model['robokassa_password2']['type'] = 'safe_string';
		
		$data_model['robokassa_server']['name'] = 'robokassa_server';
		$data_model['robokassa_server']['title'] = 'Адрес службы приема платежей robokassa.ru';
		$data_model['robokassa_server']['value'] = 'http://test.robokassa.ru/Index.aspx';
		$data_model['robokassa_server']['type'] = 'safe_string';

		$data_model['shop_product_image_big_height']['name'] = 'shop_product_image_big_height';
		$data_model['shop_product_image_big_height']['title'] = 'Высота большой картинки товара';
		$data_model['shop_product_image_big_height']['value'] = '600';
		$data_model['shop_product_image_big_height']['type'] = 'safe_string';
		
		$data_model['shop_product_image_big_width']['name'] = 'shop_product_image_big_width';
		$data_model['shop_product_image_big_width']['title'] = 'Ширина большой картинки товара';
		$data_model['shop_product_image_big_width']['value'] = '800';
		$data_model['shop_product_image_big_width']['type'] = 'safe_string';
		
		$data_model['shop_product_image_preview_height']['name'] = 'shop_product_image_preview_height';
		$data_model['shop_product_image_preview_height']['title'] = 'Высота маленькой картинки товара';
		$data_model['shop_product_image_preview_height']['value'] = '180';
		$data_model['shop_product_image_preview_height']['type'] = 'safe_string';
		
		$data_model['shop_product_image_preview_width']['name'] = 'shop_product_image_preview_width';
		$data_model['shop_product_image_preview_width']['title'] = 'Ширина маленькой картинки товара';
		$data_model['shop_product_image_preview_width']['value'] = '180';
		$data_model['shop_product_image_preview_width']['type'] = 'safe_string';
		/*
		$data_model['show_admin_helper']['name'] = 'show_admin_helper';
		$data_model['show_admin_helper']['title'] = 'Выводить помощника в админке';
		$data_model['show_admin_helper']['value'] = '1';
		$data_model['show_admin_helper']['type'] = 'checkbox';
		*/
		$data_model['site_title']['name'] = 'site_title';
		$data_model['site_title']['title'] = 'Заголовок сайта';
		$data_model['site_title']['value'] = 'Агентство недвижимости';
		$data_model['site_title']['type'] = 'safe_string';
		
		$data_model['smtp1_from']['name'] = 'smtp1_from';
		$data_model['smtp1_from']['title'] = 'SMTP-от кого 
		(это поле должно соответствовать имени и адресу домена)';
		$data_model['smtp1_from']['value'] = 'rumantic.coder@yandex.ru';
		$data_model['smtp1_from']['type'] = 'safe_string';
		
		$data_model['smtp1_login']['name'] = 'smtp1_login';
		$data_model['smtp1_login']['title'] = 'SMTP-login';
		$data_model['smtp1_login']['value'] = 'rumantic.coder';
		$data_model['smtp1_login']['type'] = 'safe_string';
		
		$data_model['smtp1_password']['name'] = 'smtp1_password';
		$data_model['smtp1_password']['title'] = 'SMTP-password';
		$data_model['smtp1_password']['value'] = '123456';
		$data_model['smtp1_password']['type'] = 'safe_string';
		
		$data_model['smtp1_port']['name'] = 'smtp1_port';
		$data_model['smtp1_port']['title'] = 'SMTP-port';
		$data_model['smtp1_port']['value'] = '587';
		$data_model['smtp1_port']['type'] = 'safe_string';
		
		$data_model['smtp1_server']['name'] = 'smtp1_server';
		$data_model['smtp1_server']['title'] = 'SMTP-сервер для отправки заявок';
		$data_model['smtp1_server']['value'] = 'smtp.yandex.ru';
		$data_model['smtp1_server']['type'] = 'safe_string';
		
		$data_model['special_advert_cost']['name'] = 'special_advert_cost';
		$data_model['special_advert_cost']['title'] = 'Стоимость размещения одного специального предложения';
		$data_model['special_advert_cost']['value'] = '0';
		$data_model['special_advert_cost']['type'] = 'safe_string';

		$data_model['template.agency.logo']['name'] = 'template.agency.logo';
		$data_model['template.agency.logo']['title'] = 'Шаблон Agency. Файл логотипа.';
		$data_model['template.agency.logo']['value'] = 'logo_1353447247.gif';
		$data_model['template.agency.logo']['type'] = 'safe_string';
		
		$data_model['template.agency.test_param']['name'] = 'template.agency.test_param';
		$data_model['template.agency.test_param']['title'] = 'Тестовый параметр';
		$data_model['template.agency.test_param']['value'] = 'werwerwerwerwerwer';
		$data_model['template.agency.test_param']['type'] = 'safe_string';
		
		$data_model['template.test_agency.logo']['name'] = 'template.test_agency.logo';
		$data_model['template.test_agency.logo']['title'] = 'Шаблон test_Agency. Файл логотипа.';
		$data_model['template.test_agency.logo']['value'] = 'logo_1353446731.gif';
		$data_model['template.test_agency.logo']['type'] = 'safe_string';
		
		$data_model['theme']['name'] = 'theme';
		$data_model['theme']['title'] = 'Тема оформления';
		$data_model['theme']['value'] = 'agency';
		$data_model['theme']['type'] = 'select_box';
		$data_model['theme']['select_data'] = $this->get_themes_array();

		$data_model['update1']['name'] = 'update1';
		$data_model['update1']['title'] = 'update1';
		$data_model['update1']['value'] = '1';
		$data_model['update1']['type'] = 'safe_string';

		$data_model['yandex_map_key']['name'] = 'yandex_map_key';
		$data_model['yandex_map_key']['title'] = 'Ключ карты яндекс (получить можно тут: http://api.yandex.ru/maps/form.xml)';
		$data_model['yandex_map_key']['value'] = 'AOgaLU0BAAAALVWJIAMAB4e9K6YyAg5jTVAvW3Es0yuVgU8AAAAAAAAAAACaL6S_CdTksT02kqGPG3wuXFoqVQ==';
		$data_model['yandex_map_key']['type'] = 'safe_string';
		/*
		$data_model['allow_topic_images']['name'] = 'allow_topic_images';
		$data_model['allow_topic_images']['title'] = 'Разрешить картинки в разделах';
		$data_model['allow_topic_images']['value'] = '0';
		$data_model['allow_topic_images']['type'] = 'checkbox';
		*/
		$data_model['default_tab_name']['name'] = 'default_tab_name';
		$data_model['default_tab_name']['title'] = 'Название закладки формы по-умолчанию';
		$data_model['default_tab_name']['value'] = 'Основное';
		$data_model['default_tab_name']['type'] = 'safe_string';
		/*
		$data_model['divide_step_form']['name'] = 'divide_step_form';
		$data_model['divide_step_form']['title'] = 'Делить формы на шаги';
		$data_model['divide_step_form']['value'] = '0';
		$data_model['divide_step_form']['type'] = 'checkbox';
		*/

		$data_model['apps.sitemap.priority.data']['name'] = 'apps.sitemap.priority.data';
		$data_model['apps.sitemap.priority.data']['title'] = 'Приоритетность URL <b>объявлений</b> относительно других URL на Вашем сайте. Диапазон от 0.0 до 1.0';
		$data_model['apps.sitemap.priority.data']['value'] = '';
		$data_model['apps.sitemap.priority.data']['type'] = 'safe_string';
		
		$data_model['apps.sitemap.priority.company']['name'] = 'apps.sitemap.priority.company';
		$data_model['apps.sitemap.priority.company']['title'] = 'Приоритетность URL <b>компании</b> относительно других URL на Вашем сайте. Диапазон от 0,0 до 1,0';
		$data_model['apps.sitemap.priority.company']['value'] = '';
		$data_model['apps.sitemap.priority.company']['type'] = 'safe_string';

        /*
		$data_model['link_metro_to_district']['name'] = 'link_metro_to_district';
		$data_model['link_metro_to_district']['title'] = 'Привязать метро к районам';
		$data_model['link_metro_to_district']['value'] = '0';
		$data_model['link_metro_to_district']['type'] = 'checkbox';

		$data_model['use_custom_addform']['name'] = 'use_custom_addform';
		$data_model['use_custom_addform']['title'] = 'Использовать локальную форму добавления объявлений';
		$data_model['use_custom_addform']['value'] = '0';
		$data_model['use_custom_addform']['type'] = 'checkbox';
		*/
		/*

		
		$data_model['apps.geodata.on_home']['name'] = 'apps.geodata.on_home';
		$data_model['apps.geodata.on_home']['title'] = 'Выводить карту на главной странице';
		$data_model['apps.geodata.on_home']['value'] = '0';
		$data_model['apps.geodata.on_home']['type'] = 'checkbox';

		
		$data_model['apps.getrent.enable']['name'] = 'apps.getrent.enable';
		$data_model['apps.getrent.enable']['title'] = 'Включить приложение Заявки на аренду';
		$data_model['apps.getrent.enable']['value'] = '0';
		$data_model['apps.getrent.enable']['type'] = 'checkbox';

		*/
		
		return $data_model;
	}
	
	function get_themes_array () {
		if(is_null(self::$themes_array)){
			$template_dir = SITEBILL_DOCUMENT_ROOT.'/template/frontend';
			$template_array = array();
			if (is_dir($template_dir)) {
				if ($dh = opendir($template_dir)) {
					while (($current_template_dir = readdir($dh)) !== false) {
						//var_dump($current_template_dir);
						//if ( is_dir($template_dir.'/'.$current_template_dir) and !preg_match('/\./', $current_template_dir) ) {
						if ( is_dir($template_dir.'/'.$current_template_dir) && $current_template_dir!='.' && $current_template_dir!='..' && !preg_match('/^\./', $current_template_dir)) {
							$template_array[$current_template_dir] = $current_template_dir;
						}
					}
					closedir($dh);
				}
                asort($template_array);
				self::$themes_array=$template_array;
			}
		}else{
			$template_array=self::$themes_array;
		}
		
		return $template_array;
	}
	
}