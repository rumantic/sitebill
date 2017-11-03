<?php
class Config_Mask {
	
	public static $themes_array=null;
	
	public function get_model(){
		
		$data_model = array();
		
		
		
		
		$data_model['add_pagenumber_title_place']['name'] = 'add_pagenumber_title_place';
		$data_model['add_pagenumber_title_place']['title'] = 'Куда добавлять кличество страниц в заголовке';
		$data_model['add_pagenumber_title_place']['value'] = '';
		$data_model['add_pagenumber_title_place']['type'] = 'select_box';
		$data_model['add_pagenumber_title_place']['select_data'] = array('0'=>'заголовок на странице','1'=>'МЕТА-заголовок','2'=>'во все заголовки');
		
		$data_model['captcha_type']['name'] = 'captcha_type';
		$data_model['captcha_type']['title'] = 'Тип капчи';
		$data_model['captcha_type']['value'] = '';
		$data_model['captcha_type']['type'] = 'select_box';
		$data_model['captcha_type']['select_data'] = array('0'=>'стандартная', '2'=>'игнорировать капчу', '3'=>'KCaptcha');
		
		
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
		
		$data_model['ajax_form_in_admin']['name'] = 'ajax_form_in_admin';
		$data_model['ajax_form_in_admin']['title'] = 'Режим ajax в формах администратора';
		$data_model['ajax_form_in_admin']['value'] = '1';
		$data_model['ajax_form_in_admin']['type'] = 'checkbox';

		$data_model['registration_notice']['name'] = 'registration_notice';
		$data_model['registration_notice']['title'] = 'Уведомлять пользователя о регистрации';
		$data_model['registration_notice']['value'] = '';
		$data_model['registration_notice']['type'] = 'checkbox';
		
		$data_model['ajax_form_in_user']['name'] = 'ajax_form_in_user';
		$data_model['ajax_form_in_user']['title'] = 'Режим ajax в формах личного кабинета';
		$data_model['ajax_form_in_user']['value'] = '';
		$data_model['ajax_form_in_user']['type'] = 'checkbox';
		
		$data_model['allow_additional_mobile_number']['name'] = 'allow_additional_mobile_number';
		$data_model['allow_additional_mobile_number']['title'] = 'Добавить дополнительный номер мобильного телефона';
		$data_model['allow_additional_mobile_number']['value'] = '';
		$data_model['allow_additional_mobile_number']['type'] = 'checkbox';
		
		$data_model['allow_additional_stationary_number']['name'] = 'allow_additional_stationary_number';
		$data_model['allow_additional_stationary_number']['title'] = 'Добавить дополнительный номер городского телефона';
		$data_model['allow_additional_stationary_number']['value'] = '';
		$data_model['allow_additional_stationary_number']['type'] = 'checkbox';
		
		$data_model['allow_callme_timelimits']['name'] = 'allow_callme_timelimits';
		$data_model['allow_callme_timelimits']['title'] = 'Добавить возможность указания допустимого для звонка времени';
		$data_model['allow_callme_timelimits']['value'] = '';
		$data_model['allow_callme_timelimits']['type'] = 'checkbox';
		
		$data_model['allow_register_account']['name'] = 'allow_register_account';
		$data_model['allow_register_account']['title'] = 'Разрешить регистрацию на сайте';
		$data_model['allow_register_account']['value'] = '';
		$data_model['allow_register_account']['type'] = 'checkbox';
		
		$data_model['allow_register_admin']['name'] = 'allow_register_admin';
		$data_model['allow_register_admin']['title'] = 'Разрешить регистрацию в админ. панели';
		$data_model['allow_register_admin']['value'] = '';
		$data_model['allow_register_admin']['type'] = 'checkbox';
		
		$data_model['allow_remind_password']['name'] = 'allow_remind_password';
		$data_model['allow_remind_password']['title'] = 'Разрешить напоминание пароля';
		$data_model['allow_remind_password']['value'] = '';
		$data_model['allow_remind_password']['type'] = 'checkbox';
		
		$data_model['app.billing.enable']['name'] = 'app.billing.enable';
		$data_model['app.billing.enable']['title'] = 'Включить Billing.Apps';
		$data_model['app.billing.enable']['value'] = '';
		$data_model['app.billing.enable']['type'] = 'checkbox';
		
		$data_model['apps.account.enable']['name'] = 'apps.account.enable';
		$data_model['apps.account.enable']['title'] = 'Активировать приложение apps.account';
		$data_model['apps.account.enable']['value'] = '';
		$data_model['apps.account.enable']['type'] = 'checkbox';
		
		$data_model['apps.accountsms.enable']['name'] = 'apps.accountsms.enable';
		$data_model['apps.accountsms.enable']['title'] = 'Включить кабинет accountsms';
		$data_model['apps.accountsms.enable']['value'] = '';
		$data_model['apps.accountsms.enable']['type'] = 'checkbox';
		
		$data_model['apps.agentphones.enable']['name'] = 'apps.agentphones.enable';
		$data_model['apps.agentphones.enable']['title'] = 'Включить приложение Agentphones';
		$data_model['apps.agentphones.enable']['value'] = '';
		$data_model['apps.agentphones.enable']['type'] = 'checkbox';
		
		$data_model['apps.balcony.enable']['name'] = 'apps.balcony.enable';
		$data_model['apps.balcony.enable']['title'] = 'Включить Balcony.Apps';
		$data_model['apps.balcony.enable']['value'] = '';
		$data_model['apps.balcony.enable']['type'] = 'checkbox';
		
		$data_model['apps.billing.enable']['name'] = 'apps.billing.enable';
		$data_model['apps.billing.enable']['title'] = 'Включить Billing.Apps';
		$data_model['apps.billing.enable']['value'] = '';
		$data_model['apps.billing.enable']['type'] = 'checkbox';
		
		$data_model['apps.cache.enable']['name'] = 'apps.cache.enable';
		$data_model['apps.cache.enable']['title'] = 'Включить кеш';
		$data_model['apps.cache.enable']['value'] = '';
		$data_model['apps.cache.enable']['type'] = 'checkbox';
		
		$data_model['apps.company.best']['name'] = 'apps.company.best';
		$data_model['apps.company.best']['title'] = 'Использовать лучшие предложения Company.Apps';
		$data_model['apps.company.best']['value'] = '';
		$data_model['apps.company.best']['type'] = 'checkbox';
		
		$data_model['apps.company.enable']['name'] = 'apps.company.enable';
		$data_model['apps.company.enable']['title'] = 'Включить Company.Apps';
		$data_model['apps.company.enable']['value'] = '';
		$data_model['apps.company.enable']['type'] = 'checkbox';
		
		$data_model['apps.company.timelimit']['name'] = 'apps.company.timelimit';
		$data_model['apps.company.timelimit']['title'] = 'Скрывать объявления компаний у которых закончился доступ в ЛК';
		$data_model['apps.company.timelimit']['value'] = '';
		$data_model['apps.company.timelimit']['type'] = 'checkbox';
		
		$data_model['apps.complaint.enable']['name'] = 'apps.complaint.enable';
		$data_model['apps.complaint.enable']['title'] = 'Включить приложение жалоба на организацию';
		$data_model['apps.complaint.enable']['value'] = '';
		$data_model['apps.complaint.enable']['type'] = 'checkbox';
		
		$data_model['apps.complaint.per_page']['name'] = 'apps.complaint.per_page';
		$data_model['apps.complaint.per_page']['title'] = 'Жалоб на странице в выводе приложения';
		$data_model['apps.complaint.per_page']['value'] = '';
		$data_model['apps.complaint.per_page']['type'] = 'safe_string';
		
		$data_model['apps.faq.alias']['name'] = 'apps.faq.alias';
		$data_model['apps.faq.alias']['title'] = 'Алиас приложения';
		$data_model['apps.faq.alias']['value'] = '';
		$data_model['apps.faq.alias']['type'] = 'safe_string';
		
		$data_model['apps.faq.enable']['name'] = 'apps.faq.enable';
		$data_model['apps.faq.enable']['title'] = 'Включить приложение Вопросы и Ответы';
		$data_model['apps.faq.enable']['value'] = '';
		$data_model['apps.faq.enable']['type'] = 'checkbox';
		
		$data_model['apps.faq.q_per_page']['name'] = 'apps.faq.q_per_page';
		$data_model['apps.faq.q_per_page']['title'] = 'Вопросов на странице в выводе приложения';
		$data_model['apps.faq.q_per_page']['value'] = '';
		$data_model['apps.faq.q_per_page']['type'] = 'safe_string';
		
		$data_model['apps.fasteditor.email_send_password_text']['name'] = 'apps.fasteditor.email_send_password_text';
		$data_model['apps.fasteditor.email_send_password_text']['title'] = 'Текст сообщения на почту с паролем для редактирования ( {password} указывает место размещения пароля в тексте сообщения)';
		$data_model['apps.fasteditor.email_send_password_text']['value'] = '';
		$data_model['apps.fasteditor.email_send_password_text']['type'] = 'safe_string';
		
		$data_model['apps.fasteditor.enable']['name'] = 'apps.fasteditor.enable';
		$data_model['apps.fasteditor.enable']['title'] = 'Включить Apps.FastEditor';
		$data_model['apps.fasteditor.enable']['value'] = '';
		$data_model['apps.fasteditor.enable']['type'] = 'checkbox';
		
		$data_model['apps.fasteditor.sms_send_password_text']['name'] = 'apps.fasteditor.sms_send_password_text';
		$data_model['apps.fasteditor.sms_send_password_text']['title'] = 'Текст sms сообщения с паролем для редактирования ( {password} указывает место размещения пароля в тексте сообщения)';
		$data_model['apps.fasteditor.sms_send_password_text']['value'] = '';
		$data_model['apps.fasteditor.sms_send_password_text']['type'] = 'safe_string';
		
		$data_model['apps.fasteditor.sms_send_password_text_long']['name'] = 'apps.fasteditor.sms_send_password_text_long';
		$data_model['apps.fasteditor.sms_send_password_text_long']['title'] = '(Длинное) Текст sms сообщения с паролем для редактирования ( {password} указывает место размещения пароля в тексте сообщения)';
		$data_model['apps.fasteditor.sms_send_password_text_long']['value'] = '';
		$data_model['apps.fasteditor.sms_send_password_text_long']['type'] = 'safe_string';
		
		$data_model['apps.freeorder.enable']['name'] = 'apps.freeorder.enable';
		$data_model['apps.freeorder.enable']['title'] = 'Включить Apps.Freeorder';
		$data_model['apps.freeorder.enable']['value'] = '';
		$data_model['apps.freeorder.enable']['type'] = 'checkbox';
		
		$data_model['apps.freeorder.notification_email']['name'] = 'apps.freeorder.notification_email';
		$data_model['apps.freeorder.notification_email']['title'] = 'E-mail для получения уведомлений о новых заявках через Apps.Freeorder (при отсутствии изпользуется order_email_acceptor)';
		$data_model['apps.freeorder.notification_email']['value'] = '';
		$data_model['apps.freeorder.notification_email']['type'] = 'safe_string';
		
		$data_model['apps.mapviewer.enable']['name'] = 'apps.mapviewer.enable';
		$data_model['apps.mapviewer.enable']['title'] = 'Включить приложение Map Viewer';
		$data_model['apps.mapviewer.enable']['value'] = '';
		$data_model['apps.mapviewer.enable']['type'] = 'checkbox';
		
		$data_model['apps.news.enable']['name'] = 'apps.news.enable';
		$data_model['apps.news.enable']['title'] = 'Включить News.Apps';
		$data_model['apps.news.enable']['value'] = '';
		$data_model['apps.news.enable']['type'] = 'checkbox';
		
		$data_model['apps.news.front.per_page']['name'] = 'apps.news.front.per_page';
		$data_model['apps.news.front.per_page']['title'] = 'Количество новостей на страницу';
		$data_model['apps.news.front.per_page']['value'] = '';
		$data_model['apps.news.front.per_page']['type'] = 'safe_string';
		
		$data_model['apps.news.news_line.per_page']['name'] = 'apps.news.news_line.per_page';
		$data_model['apps.news.news_line.per_page']['title'] = 'Количество новостей в новостном блоке на главной странице';
		$data_model['apps.news.news_line.per_page']['value'] = '';
		$data_model['apps.news.news_line.per_page']['type'] = 'safe_string';
		
		$data_model['apps.newsparser_rbc.portion']['name'] = 'apps.newsparser_rbc.portion';
		$data_model['apps.newsparser_rbc.portion']['title'] = 'Количество новостей обрабатываемых за один проход';
		$data_model['apps.newsparser_rbc.portion']['value'] = '';
		$data_model['apps.newsparser_rbc.portion']['type'] = 'safe_string';
		
		$data_model['apps.orderhistory.enable']['name'] = 'apps.orderhistory.enable';
		$data_model['apps.orderhistory.enable']['title'] = 'Включить Apps.Orderhistory';
		$data_model['apps.orderhistory.enable']['value'] = '';
		$data_model['apps.orderhistory.enable']['type'] = 'checkbox';
		
		$data_model['apps.page.enable']['name'] = 'apps.page.enable';
		$data_model['apps.page.enable']['title'] = 'Включить Apps.Page';
		$data_model['apps.page.enable']['value'] = '';
		$data_model['apps.page.enable']['type'] = 'checkbox';
		
		$data_model['apps.plan.enable']['name'] = 'apps.plan.enable';
		$data_model['apps.plan.enable']['title'] = 'Включить Plan.Apps';
		$data_model['apps.plan.enable']['value'] = '';
		$data_model['apps.plan.enable']['type'] = 'checkbox';
		
		$data_model['apps.rabota.enable']['name'] = 'apps.rabota.enable';
		$data_model['apps.rabota.enable']['title'] = 'Включить приложение Apps.Rabota';
		$data_model['apps.rabota.enable']['value'] = '';
		$data_model['apps.rabota.enable']['type'] = 'checkbox';
		
		$data_model['apps.realty.ajax_city_refresh']['name'] = 'apps.realty.ajax_city_refresh';
		$data_model['apps.realty.ajax_city_refresh']['title'] = 'Ajax - обновление города';
		$data_model['apps.realty.ajax_city_refresh']['value'] = '';
		$data_model['apps.realty.ajax_city_refresh']['type'] = 'checkbox';
		
		$data_model['apps.realty.ajax_district_refresh']['name'] = 'apps.realty.ajax_district_refresh';
		$data_model['apps.realty.ajax_district_refresh']['title'] = 'Ajax - обновление района';
		$data_model['apps.realty.ajax_district_refresh']['value'] = '';
		$data_model['apps.realty.ajax_district_refresh']['type'] = 'checkbox';
		
		$data_model['apps.realty.ajax_metro_refresh']['name'] = 'apps.realty.ajax_metro_refresh';
		$data_model['apps.realty.ajax_metro_refresh']['title'] = 'Ajax - обновление метро';
		$data_model['apps.realty.ajax_metro_refresh']['value'] = '';
		$data_model['apps.realty.ajax_metro_refresh']['type'] = 'checkbox';
		
		$data_model['apps.realty.ajax_region_refresh']['name'] = 'apps.realty.ajax_region_refresh';
		$data_model['apps.realty.ajax_region_refresh']['title'] = 'Ajax - обновление региона';
		$data_model['apps.realty.ajax_region_refresh']['value'] = '';
		$data_model['apps.realty.ajax_region_refresh']['type'] = 'checkbox';
		
		$data_model['apps.realty.ajax_street_refresh']['name'] = 'apps.realty.ajax_street_refresh';
		$data_model['apps.realty.ajax_street_refresh']['title'] = 'Ajax - обновление улицы';
		$data_model['apps.realty.ajax_street_refresh']['value'] = '';
		$data_model['apps.realty.ajax_street_refresh']['type'] = 'checkbox';
		
		$data_model['apps.realtybuyorder.enable']['name'] = 'apps.realtybuyorder.enable';
		$data_model['apps.realtybuyorder.enable']['title'] = 'Включить Realtybuyorder';
		$data_model['apps.realtybuyorder.enable']['value'] = '';
		$data_model['apps.realtybuyorder.enable']['type'] = 'checkbox';
		
		$data_model['apps.realtybuyorder.text_after_send']['name'] = 'apps.realtybuyorder.text_after_send';
		$data_model['apps.realtybuyorder.text_after_send']['title'] = 'Текст после заказа через Realtybuyorder';
		$data_model['apps.realtybuyorder.text_after_send']['value'] = '';
		$data_model['apps.realtybuyorder.text_after_send']['type'] = 'safe_string';
		
		$data_model['apps.realtycsv.enable']['name'] = 'apps.realtycsv.enable';
		$data_model['apps.realtycsv.enable']['title'] = 'Включить Apps.RealtyCSV';
		$data_model['apps.realtycsv.enable']['value'] = '';
		$data_model['apps.realtycsv.enable']['type'] = 'checkbox';
		
		$data_model['apps.realtylog.enable']['name'] = 'apps.realtylog.enable';
		$data_model['apps.realtylog.enable']['title'] = 'Включить Apps.Realtylog';
		$data_model['apps.realtylog.enable']['value'] = '';
		$data_model['apps.realtylog.enable']['type'] = 'checkbox';
		
		$data_model['apps.realtypro.admin.items_per_page']['name'] = 'apps.realtypro.admin.items_per_page';
		$data_model['apps.realtypro.admin.items_per_page']['title'] = 'Недвижимость. Админка. Количество позиций на странице';
		$data_model['apps.realtypro.admin.items_per_page']['value'] = '';
		$data_model['apps.realtypro.admin.items_per_page']['type'] = 'safe_string';
		
		$data_model['apps.realtypro.enable']['name'] = 'apps.realtypro.enable';
		$data_model['apps.realtypro.enable']['title'] = 'Включить RealtyPro.Apps';
		$data_model['apps.realtypro.enable']['value'] = '';
		$data_model['apps.realtypro.enable']['type'] = 'checkbox';
		
		$data_model['apps.realtypro.show_contact.enable']['name'] = 'apps.realtypro.show_contact.enable';
		$data_model['apps.realtypro.show_contact.enable']['title'] = 'Включить показ контактов объявления';
		$data_model['apps.realtypro.show_contact.enable']['value'] = '';
		$data_model['apps.realtypro.show_contact.enable']['type'] = 'checkbox';
		
		$data_model['apps.realtypro.youtube']['name'] = 'apps.realtypro.youtube';
		$data_model['apps.realtypro.youtube']['title'] = 'Разрешить youtube-ролики в объявлении';
		$data_model['apps.realtypro.youtube']['value'] = '';
		$data_model['apps.realtypro.youtube']['type'] = 'checkbox';
		
		$data_model['apps.realtypro.youtube']['name'] = 'apps.realtypro.youtube';
		$data_model['apps.realtypro.youtube']['title'] = 'Включить авторегистрацию';
		$data_model['apps.realtypro.youtube']['value'] = '';
		$data_model['apps.realtypro.youtube']['type'] = 'checkbox';
		
		$data_model['apps.realtyspecial.enable']['name'] = 'apps.realtyspecial.enable';
		$data_model['apps.realtyspecial.enable']['title'] = 'Включить RealtySpecial.Apps';
		$data_model['apps.realtyspecial.enable']['value'] = '';
		$data_model['apps.realtyspecial.enable']['type'] = 'checkbox';
		
		$data_model['apps.registersms.enable']['name'] = 'apps.registersms.enable';
		$data_model['apps.registersms.enable']['title'] = 'Включить регистрацию через SMS';
		$data_model['apps.registersms.enable']['value'] = '';
		$data_model['apps.registersms.enable']['type'] = 'checkbox';
		
		$data_model['apps.registersms.first_category_price']['name'] = 'apps.registersms.first_category_price';
		$data_model['apps.registersms.first_category_price']['title'] = 'Стоимость первой категории';
		$data_model['apps.registersms.first_category_price']['value'] = '';
		$data_model['apps.registersms.first_category_price']['type'] = 'safe_string';
		
		$data_model['apps.registersms.next_category_price']['name'] = 'apps.registersms.next_category_price';
		$data_model['apps.registersms.next_category_price']['title'] = 'Стоимость категорий после первой';
		$data_model['apps.registersms.next_category_price']['value'] = '';
		$data_model['apps.registersms.next_category_price']['type'] = 'safe_string';
		
		$data_model['apps.rss.description']['name'] = 'apps.rss.description';
		$data_model['apps.rss.description']['title'] = 'Описание RSS канала';
		$data_model['apps.rss.description']['value'] = '';
		$data_model['apps.rss.description']['type'] = 'safe_string';
		
		$data_model['apps.rss.editor_email']['name'] = 'apps.rss.editor_email';
		$data_model['apps.rss.editor_email']['title'] = 'Адрес электронной почты лица, ответственного за редакционное содержание';
		$data_model['apps.rss.editor_email']['value'] = '';
		$data_model['apps.rss.editor_email']['type'] = 'safe_string';
		
		$data_model['apps.rss.enable']['name'] = 'apps.rss.enable';
		$data_model['apps.rss.enable']['title'] = 'Включить экспорт RSS';
		$data_model['apps.rss.enable']['value'] = '';
		$data_model['apps.rss.enable']['type'] = 'checkbox';
		
		$data_model['apps.rss.generator']['name'] = 'apps.rss.generator';
		$data_model['apps.rss.generator']['title'] = 'Название генератора RSS канала';
		$data_model['apps.rss.generator']['value'] = '';
		$data_model['apps.rss.generator']['type'] = 'safe_string';
		
		$data_model['apps.rss.language']['name'] = 'apps.rss.language';
		$data_model['apps.rss.language']['title'] = 'Код языка канала согласно http://cyber.law.harvard.edu/rss/languages.html';
		$data_model['apps.rss.language']['value'] = '';
		$data_model['apps.rss.language']['type'] = 'safe_string';
		
		$data_model['apps.rss.length']['name'] = 'apps.rss.length';
		$data_model['apps.rss.length']['title'] = 'Длинна RSS канала';
		$data_model['apps.rss.length']['value'] = '';
		$data_model['apps.rss.length']['type'] = 'safe_string';
		
		$data_model['apps.rss.title']['name'] = 'apps.rss.title';
		$data_model['apps.rss.title']['title'] = 'Название RSS канала';
		$data_model['apps.rss.title']['value'] = '';
		$data_model['apps.rss.title']['type'] = 'safe_string';
		
		$data_model['apps.rss.webmaster_email']['name'] = 'apps.rss.webmaster_email';
		$data_model['apps.rss.webmaster_email']['title'] = 'Адрес электронной почты лица, ответственного за технические вопросы, касающиеся канала';
		$data_model['apps.rss.webmaster_email']['value'] = '';
		$data_model['apps.rss.webmaster_email']['type'] = 'safe_string';
		
		$data_model['apps.sanuzel.enable']['name'] = 'apps.sanuzel.enable';
		$data_model['apps.sanuzel.enable']['title'] = 'Включить Sanuzel.Apps';
		$data_model['apps.sanuzel.enable']['value'] = '';
		$data_model['apps.sanuzel.enable']['type'] = 'checkbox';
		
		$data_model['apps.search.alias']['name'] = 'apps.search.alias';
		$data_model['apps.search.alias']['title'] = 'Алиас приложения';
		$data_model['apps.search.alias']['value'] = '';
		$data_model['apps.search.alias']['type'] = 'safe_string';
		
		$data_model['apps.search.enable']['name'] = 'apps.search.enable';
		$data_model['apps.search.enable']['title'] = 'Включить приложение Живой поиск';
		$data_model['apps.search.enable']['value'] = '';
		$data_model['apps.search.enable']['type'] = 'checkbox';
		
		$data_model['apps.search.records_number']['name'] = 'apps.search.records_number';
		$data_model['apps.search.records_number']['title'] = 'Число строк подсказок';
		$data_model['apps.search.records_number']['value'] = '';
		$data_model['apps.search.records_number']['type'] = 'safe_string';
		
		$data_model['apps.shop.admin.products_per_page']['name'] = 'apps.shop.admin.products_per_page';
		$data_model['apps.shop.admin.products_per_page']['title'] = 'Магазин. Количество продуктов на странице в админке';
		$data_model['apps.shop.admin.products_per_page']['value'] = '';
		$data_model['apps.shop.admin.products_per_page']['type'] = 'safe_string';
		
		$data_model['apps.shop.city_enable']['name'] = 'apps.shop.city_enable';
		$data_model['apps.shop.city_enable']['title'] = 'Указание города в свойствах товара';
		$data_model['apps.shop.city_enable']['value'] = '';
		$data_model['apps.shop.city_enable']['type'] = 'checkbox';
		
		$data_model['apps.shop.current_city_id']['name'] = 'apps.shop.current_city_id';
		$data_model['apps.shop.current_city_id']['title'] = 'ID текущего города';
		$data_model['apps.shop.current_city_id']['value'] = '';
		$data_model['apps.shop.current_city_id']['type'] = 'safe_string';
		
		$data_model['apps.shop.enable']['name'] = 'apps.shop.enable';
		$data_model['apps.shop.enable']['title'] = 'Включить Apps.Shop';
		$data_model['apps.shop.enable']['value'] = '';
		$data_model['apps.shop.enable']['type'] = 'checkbox';
		
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
		
		$data_model['apps.shop.user_limit_enable']['name'] = 'apps.shop.user_limit_enable';
		$data_model['apps.shop.user_limit_enable']['title'] = 'Активировать режим временных ограничений пользовательских публикаций';
		$data_model['apps.shop.user_limit_enable']['value'] = '';
		$data_model['apps.shop.user_limit_enable']['type'] = 'checkbox';
		
		$data_model['apps.shoplog.enable']['name'] = 'apps.shoplog.enable';
		$data_model['apps.shoplog.enable']['title'] = 'Включить приложение Apps.Shoplog';
		$data_model['apps.shoplog.enable']['value'] = '';
		$data_model['apps.shoplog.enable']['type'] = 'checkbox';
		
		$data_model['apps.shopstat.enable']['name'] = 'apps.shopstat.enable';
		$data_model['apps.shopstat.enable']['title'] = 'Включить Apps.Shopstat';
		$data_model['apps.shopstat.enable']['value'] = '';
		$data_model['apps.shopstat.enable']['type'] = 'checkbox';
		
		$data_model['apps.sitemap.changefreq.menu']['name'] = 'apps.sitemap.changefreq.menu';
		$data_model['apps.sitemap.changefreq.menu']['title'] = 'Вероятная частота изменения вспомогательных меню. Одно из числовых значений (1-всегда, 2-ежечасно, 3-ежедневно, 4-еженедельно, 5-ежемесячно, 6-ежегодно, 7-никогда)';
		$data_model['apps.sitemap.changefreq.menu']['value'] = '';
		$data_model['apps.sitemap.changefreq.menu']['type'] = 'select_box';
		$data_model['apps.sitemap.changefreq.menu']['select_data'] = array('1'=>'всегда','2'=>'ежечасно','3'=>'ежедневно','4'=>'еженедельно','5'=>'ежемесячно','6'=>'ежегодно','7'=>'никогда');
		
		$data_model['apps.sitemap.changefreq.news']['name'] = 'apps.sitemap.changefreq.news';
		$data_model['apps.sitemap.changefreq.news']['title'] = 'Вероятная частота изменения страницы раздела новостей. Одно из числовых значений (1-всегда, 2-ежечасно, 3-ежедневно, 4-еженедельно, 5-ежемесячно, 6-ежегодно, 7-никогда)';
		$data_model['apps.sitemap.changefreq.news']['value'] = '';
		$data_model['apps.sitemap.changefreq.news']['type'] = 'select_box';
		$data_model['apps.sitemap.changefreq.news']['select_data'] = array('1'=>'всегда','2'=>'ежечасно','3'=>'ежедневно','4'=>'еженедельно','5'=>'ежемесячно','6'=>'ежегодно','7'=>'никогда');
		
		$data_model['apps.sitemap.changefreq.page']['name'] = 'apps.sitemap.changefreq.page';
		$data_model['apps.sitemap.changefreq.page']['title'] = 'Вероятная частота изменения статической страницы. Одно из числовых значений (1-всегда, 2-ежечасно, 3-ежедневно, 4-еженедельно, 5-ежемесячно, 6-ежегодно, 7-никогда)';
		$data_model['apps.sitemap.changefreq.page']['value'] = '';
		$data_model['apps.sitemap.changefreq.page']['type'] = 'select_box';
		$data_model['apps.sitemap.changefreq.page']['select_data'] = array('1'=>'всегда','2'=>'ежечасно','3'=>'ежедневно','4'=>'еженедельно','5'=>'ежемесячно','6'=>'ежегодно','7'=>'никогда');
		
		$data_model['apps.sitemap.changefreq.topic']['name'] = 'apps.sitemap.changefreq.topic';
		$data_model['apps.sitemap.changefreq.topic']['title'] = 'Вероятная частота изменения страницы категории. Одно из числовых значений (1-всегда, 2-ежечасно, 3-ежедневно, 4-еженедельно, 5-ежемесячно, 6-ежегодно, 7-никогда)';
		$data_model['apps.sitemap.changefreq.topic']['value'] = '';
		$data_model['apps.sitemap.changefreq.topic']['type'] = 'select_box';
		$data_model['apps.sitemap.changefreq.topic']['select_data'] = array('1'=>'всегда','2'=>'ежечасно','3'=>'ежедневно','4'=>'еженедельно','5'=>'ежемесячно','6'=>'ежегодно','7'=>'никогда');
		
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
		
		$data_model['apps.sms.test_mode']['name'] = 'apps.sms.test_mode';
		$data_model['apps.sms.test_mode']['title'] = 'Работать в тестовом режиме';
		$data_model['apps.sms.test_mode']['value'] = '1';
		$data_model['apps.sms.test_mode']['type'] = 'checkbox';
		
		$data_model['apps.twitter.consumer_key']['name'] = 'apps.twitter.consumer_key';
		$data_model['apps.twitter.consumer_key']['title'] = 'Consumer_key';
		$data_model['apps.twitter.consumer_key']['value'] = '';
		$data_model['apps.twitter.consumer_key']['type'] = 'safe_string';
		
		$data_model['apps.twitter.consumer_secret']['name'] = 'apps.twitter.consumer_secret';
		$data_model['apps.twitter.consumer_secret']['title'] = 'Consumer_secret';
		$data_model['apps.twitter.consumer_secret']['value'] = '';
		$data_model['apps.twitter.consumer_secret']['type'] = 'safe_string';
		
		$data_model['apps.twitter.enable']['name'] = 'apps.twitter.enable';
		$data_model['apps.twitter.enable']['title'] = 'Включить приложение Apps.Twitter';
		$data_model['apps.twitter.enable']['value'] = '0';
		$data_model['apps.twitter.enable']['type'] = 'checkbox';
		
		$data_model['apps.twitter.user_secret']['name'] = 'apps.twitter.user_secret';
		$data_model['apps.twitter.user_secret']['title'] = 'Access token secret';
		$data_model['apps.twitter.user_secret']['value'] = '';
		$data_model['apps.twitter.user_secret']['type'] = 'safe_string';
		
		$data_model['apps.twitter.user_token']['name'] = 'apps.twitter.user_token';
		$data_model['apps.twitter.user_token']['title'] = 'Access token';
		$data_model['apps.twitter.user_token']['value'] = '';
		$data_model['apps.twitter.user_token']['type'] = 'safe_string';
		
		$data_model['apps.watermark.enable']['name'] = 'apps.watermark.enable';
		$data_model['apps.watermark.enable']['title'] = 'Включить приложение Apps.WatermarkPrinter';
		$data_model['apps.watermark.enable']['value'] = '0';
		$data_model['apps.watermark.enable']['type'] = 'checkbox';
		
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
		
		$data_model['apps.watermark.position']['name'] = 'apps.watermark.position';
		$data_model['apps.watermark.position']['title'] = 'Расположение принта (center|top-left|top-right|bottom-left|bottom-right)';
		$data_model['apps.watermark.position']['value'] = 'center';
		$data_model['apps.watermark.position']['type'] = 'select_box';
		$data_model['apps.watermark.position']['select_data'] = array('center'=>'center','top-left'=>'top-left','top-right'=>'top-right','bottom-left'=>'bottom-left','bottom-right'=>'bottom-right');
		
		$data_model['apps.yandexrealty_parser.allow_create_new_category']['name'] = 'apps.yandexrealty_parser.allow_create_new_category';
		$data_model['apps.yandexrealty_parser.allow_create_new_category']['title'] = 'Разрешить создание цепочек категорий в случае отсутствия подходящей';
		$data_model['apps.yandexrealty_parser.allow_create_new_category']['value'] = '1';
		$data_model['apps.yandexrealty_parser.allow_create_new_category']['type'] = 'checkbox';
		
		$data_model['apps.yandexrealty_parser.category_for_all']['name'] = 'apps.yandexrealty_parser.category_for_all';
		$data_model['apps.yandexrealty_parser.category_for_all']['title'] = 'ID категории, которая будет сопоставлена добавляемой записи в случае apps.yandexrealty_parser.allow_create_new_category=0';
		$data_model['apps.yandexrealty_parser.category_for_all']['value'] = '1000';
		$data_model['apps.yandexrealty_parser.category_for_all']['type'] = 'safe_string';
		
		$data_model['apps.yandexrealty_parser.default_activity_status']['name'] = 'apps.yandexrealty_parser.default_activity_status';
		$data_model['apps.yandexrealty_parser.default_activity_status']['title'] = 'Статус активности для добавляемых записей';
		$data_model['apps.yandexrealty_parser.default_activity_status']['value'] = '1';
		$data_model['apps.yandexrealty_parser.default_activity_status']['type'] = 'checkbox';
		
		$data_model['apps.yandexrealty_parser.default_user_id']['name'] = 'apps.yandexrealty_parser.default_user_id';
		$data_model['apps.yandexrealty_parser.default_user_id']['title'] = 'ID пользователя по умолчанию. Если 0, то ID пользователя будет браться из таблицы доменов. Если не 0, то в качестве user_id для позиции будет использоваться это значение.';
		$data_model['apps.yandexrealty_parser.default_user_id']['value'] = '0';
		$data_model['apps.yandexrealty_parser.default_user_id']['type'] = 'safe_string';
		
		$data_model['apps.yml.company_name']['name'] = 'apps.yml.company_name';
		$data_model['apps.yml.company_name']['title'] = 'Полное наименование компании';
		$data_model['apps.yml.company_name']['value'] = 'Some Company';
		$data_model['apps.yml.company_name']['type'] = 'safe_string';
		
		$data_model['apps.yml.delivery']['name'] = 'apps.yml.delivery';
		$data_model['apps.yml.delivery']['title'] = 'Возможность доставки товара на условиях, которые указываются в партнерском интерфейсе http://partner.market.yandex.ru на странице "редактирование" (true/false).';
		$data_model['apps.yml.delivery']['value'] = 'true';
		$data_model['apps.yml.delivery']['type'] = 'select_box';
		$data_model['apps.yml.delivery']['select_data'] = array('true'=>'true','false'=>'false');
		
		$data_model['apps.yml.local_delivery_cost']['name'] = 'apps.yml.local_delivery_cost';
		$data_model['apps.yml.local_delivery_cost']['title'] = 'Cтоимость доставки для своего региона';
		$data_model['apps.yml.local_delivery_cost']['value'] = '';
		$data_model['apps.yml.local_delivery_cost']['type'] = 'safe_string';
		
		$data_model['apps.yml.pickup']['name'] = 'apps.yml.pickup';
		$data_model['apps.yml.pickup']['title'] = 'Возможность предварительно заказать товар и забрать его в точке продаж (true/false).';
		$data_model['apps.yml.pickup']['value'] = 'false';
		$data_model['apps.yml.pickup']['type'] = 'select_box';
		$data_model['apps.yml.pickup']['select_data'] = array('true'=>'true','false'=>'false');
		
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
		
		$data_model['apps.yml.store']['name'] = 'apps.yml.store';
		$data_model['apps.yml.store']['title'] = 'Возможность приобрести товар в точке продаж без предварительного заказа по интернету (true/false).';
		$data_model['apps.yml.store']['value'] = '0';
		$data_model['apps.yml.store']['type'] = 'checkbox';
		
		$data_model['app_gallery_photos_per_page']['name'] = 'app_gallery_photos_per_page';
		$data_model['app_gallery_photos_per_page']['title'] = 'Галерея: Количество фотографий на страницу';
		$data_model['app_gallery_photos_per_page']['value'] = '5';
		$data_model['app_gallery_photos_per_page']['type'] = 'safe_string';
		
		$data_model['autoreg_enable']['name'] = 'autoreg_enable';
		$data_model['autoreg_enable']['title'] = 'Включить авторегистрацию';
		$data_model['autoreg_enable']['value'] = '0';
		$data_model['autoreg_enable']['type'] = 'checkbox';
		
		$data_model['city']['name'] = 'city';
		$data_model['city']['title'] = 'Город (для отображения на карте yandex)';
		$data_model['city']['value'] = 'Красноярск';
		$data_model['city']['type'] = 'safe_string';
		
		$data_model['city_in_form']['name'] = 'city_in_form';
		$data_model['city_in_form']['title'] = 'Выбор города в форме объявления';
		$data_model['city_in_form']['value'] = '1';
		$data_model['city_in_form']['type'] = 'checkbox';
		
		$data_model['common_per_page']['name'] = 'common_per_page';
		$data_model['common_per_page']['title'] = 'Количество позиций на страницу';
		$data_model['common_per_page']['value'] = '10';
		$data_model['common_per_page']['type'] = 'safe_string';
		
		$data_model['country_in_form']['name'] = 'country_in_form';
		$data_model['country_in_form']['title'] = 'Выбор страны в форме объявления';
		$data_model['country_in_form']['value'] = '0';
		$data_model['country_in_form']['type'] = 'checkbox';
		
		$data_model['currency_enable']['name'] = 'currency_enable';
		$data_model['currency_enable']['title'] = 'Включить поддержку нескольких валют';
		$data_model['currency_enable']['value'] = '1';
		$data_model['currency_enable']['type'] = 'checkbox';
		
		$data_model['default_topic']['name'] = 'default_topic';
		$data_model['default_topic']['title'] = 'Категория по-умолчанию (выводится на главной)';
		$data_model['default_topic']['value'] = '2';
		$data_model['default_topic']['type'] = 'safe_string';
		
		$data_model['district_in_form']['name'] = 'district_in_form';
		$data_model['district_in_form']['title'] = 'Выбор района в форме объявления';
		$data_model['district_in_form']['value'] = '1';
		$data_model['district_in_form']['type'] = 'checkbox';
		
		$data_model['editor']['name'] = 'editor';
		$data_model['editor']['title'] = 'WYSIWYG-редактор';
		$data_model['editor']['value'] = 'cleditor';
		$data_model['editor']['type'] = 'select_box';
		$data_model['editor']['select_data'] = array('cleditor'=>'cleditor','ckeditor'=>'ckeditor','bbeditor'=>'bbeditor');
		
		$data_model['editor1']['name'] = 'editor1';
		$data_model['editor1']['title'] = 'WYSIWYG-редактор1';
		$data_model['editor1']['value'] = 'bbeditor';
		$data_model['editor1']['type'] = 'safe_string';
		
		$data_model['hide_empty_catalog']['name'] = 'hide_empty_catalog';
		$data_model['hide_empty_catalog']['title'] = 'Прятать каталоги без содержимого';
		$data_model['hide_empty_catalog']['value'] = '0';
		$data_model['hide_empty_catalog']['type'] = 'checkbox';
		
		$data_model['hide_topic_list']['name'] = 'hide_topic_list';
		$data_model['hide_topic_list']['title'] = 'Список идентификаторов категорий, в которых контакты в объявлениях спрятаны от простых пользователей';
		$data_model['hide_topic_list']['value'] = '14';
		$data_model['hide_topic_list']['type'] = 'safe_string';
		
		$data_model['is_watermark']['name'] = 'is_watermark';
		$data_model['is_watermark']['title'] = 'Использовать watermark на фотографиях';
		$data_model['is_watermark']['value'] = '0';
		$data_model['is_watermark']['type'] = 'checkbox';
		
		$data_model['license_key']['name'] = 'license_key';
		$data_model['license_key']['title'] = 'Лицензионный ключ';
		$data_model['license_key']['value'] = '4dcf-51-61d8ffe5-a02720-535d39-62cc843e';
		$data_model['license_key']['type'] = 'safe_string';
		
		$data_model['link_street_to_city']['name'] = 'link_street_to_city';
		$data_model['link_street_to_city']['title'] = 'Включить привязку улиц к городу';
		$data_model['link_street_to_city']['value'] = '0';
		$data_model['link_street_to_city']['type'] = 'checkbox';
		
		$data_model['menu_type']['name'] = 'menu_type';
		$data_model['menu_type']['title'] = 'Тип верхнего меню (purecss/slidemenu/megamenu)';
		$data_model['menu_type']['value'] = 'purecss';
		$data_model['menu_type']['type'] = 'select_box';
		$data_model['menu_type']['select_data'] = array('purecss'=>'purecss','slidemenu'=>'slidemenu','megamenu'=>'megamenu');
		
		$data_model['metro_in_form']['name'] = 'metro_in_form';
		$data_model['metro_in_form']['title'] = 'Выбор метро в форме объявления';
		$data_model['metro_in_form']['value'] = '1';
		$data_model['metro_in_form']['type'] = 'checkbox';
		
		$data_model['more_fields_in_lk']['name'] = 'more_fields_in_lk';
		$data_model['more_fields_in_lk']['title'] = 'Дополнительные поля в личном кабинете риелтора';
		$data_model['more_fields_in_lk']['value'] = '0';
		$data_model['more_fields_in_lk']['type'] = 'checkbox';
		
		$data_model['news_count_in_column']['name'] = 'news_count_in_column';
		$data_model['news_count_in_column']['title'] = 'Количество новостей в колонке';
		$data_model['news_count_in_column']['value'] = '5';
		$data_model['news_count_in_column']['type'] = 'safe_string';
		
		$data_model['notify_about_publishing']['name'] = 'notify_about_publishing';
		$data_model['notify_about_publishing']['title'] = 'Уведомлять пользователя о публикации его объявления после модерации.';
		$data_model['notify_about_publishing']['value'] = '0';
		$data_model['notify_about_publishing']['type'] = 'checkbox';
		
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
		
		$data_model['post_form_agreement_enable']['name'] = 'post_form_agreement_enable';
		$data_model['post_form_agreement_enable']['title'] = 'Активировать выдачу соглашения после формы';
		$data_model['post_form_agreement_enable']['value'] = '0';
		$data_model['post_form_agreement_enable']['type'] = 'checkbox';
		
		$data_model['post_form_agreement_text']['name'] = 'post_form_agreement_text';
		$data_model['post_form_agreement_text']['title'] = 'Текст соглашения после формы';
		$data_model['post_form_agreement_text']['value'] = 'Я, ознакомлен(а), что данная заявка будет доставлена по всем Агентствам недвижимости которые зарегистрированы на сайте.';
		$data_model['post_form_agreement_text']['type'] = 'safe_string';
		
		$data_model['post_form_agreement_text_add']['name'] = 'post_form_agreement_text_add';
		$data_model['post_form_agreement_text_add']['title'] = 'Текст соглашения после формы добавления объявления';
		$data_model['post_form_agreement_text_add']['value'] = 'Я,  ознакомлен(а) с Пользовательским соглашением';
		$data_model['post_form_agreement_text_add']['type'] = 'safe_string';
		
		$data_model['region_in_form']['name'] = 'region_in_form';
		$data_model['region_in_form']['title'] = 'Выбор региона в форме объявления';
		$data_model['region_in_form']['value'] = '1';
		$data_model['region_in_form']['type'] = 'checkbox';
		
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
		
		$data_model['seo_photo_name_enable']['name'] = 'seo_photo_name_enable';
		$data_model['seo_photo_name_enable']['title'] = 'Включить SEO-оптимизацию названий изображений';
		$data_model['seo_photo_name_enable']['value'] = '0';
		$data_model['seo_photo_name_enable']['type'] = 'checkbox';
		
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
		
		$data_model['show_admin_helper']['name'] = 'show_admin_helper';
		$data_model['show_admin_helper']['title'] = 'Выводить помощника в админке';
		$data_model['show_admin_helper']['value'] = '1';
		$data_model['show_admin_helper']['type'] = 'checkbox';
		
		$data_model['show_demo_banners']['name'] = 'show_demo_banners';
		$data_model['show_demo_banners']['title'] = 'Показывать рекламу';
		$data_model['show_demo_banners']['value'] = '1';
		$data_model['show_demo_banners']['type'] = 'checkbox';
		
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
		
		$data_model['street_in_form']['name'] = 'street_in_form';
		$data_model['street_in_form']['title'] = 'Выбор улицы в форме объявления';
		$data_model['street_in_form']['value'] = '1';
		$data_model['street_in_form']['type'] = 'checkbox';
		
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
		
		$data_model['uploader_type']['name'] = 'uploader_type';
		$data_model['uploader_type']['title'] = 'Тип апплоадера для загрузки картинок. При неуказанном значении по умолчанию используется Uploadify';
		$data_model['uploader_type']['value'] = '';
		$data_model['uploader_type']['type'] = 'select_box';
		$data_model['uploader_type']['select_data'] = array('uploadify'=>'uploadify','pluploader'=>'pluploader');
		
		$data_model['user_account_enable']['name'] = 'user_account_enable';
		$data_model['user_account_enable']['title'] = 'Редактировать лицевой счет пользователя в админке';
		$data_model['user_account_enable']['value'] = '0';
		$data_model['user_account_enable']['type'] = 'checkbox';
		
		$data_model['user_add_street_enable']['name'] = 'user_add_street_enable';
		$data_model['user_add_street_enable']['title'] = 'Пользователи могут добавлять улицы';
		$data_model['user_add_street_enable']['value'] = '1';
		$data_model['user_add_street_enable']['type'] = 'checkbox';
		
		$data_model['use_google_map']['name'] = 'use_google_map';
		$data_model['use_google_map']['title'] = 'Использовать карту Google';
		$data_model['use_google_map']['value'] = '0';
		$data_model['use_google_map']['type'] = 'checkbox';
		
		$data_model['use_smtp']['name'] = 'use_smtp';
		$data_model['use_smtp']['title'] = 'Отправка почты через smtp';
		$data_model['use_smtp']['value'] = '0';
		$data_model['use_smtp']['type'] = 'checkbox';
		
		$data_model['yandex_map_key']['name'] = 'yandex_map_key';
		$data_model['yandex_map_key']['title'] = 'Ключ карты яндекс (получить можно тут: http://api.yandex.ru/maps/form.xml)';
		$data_model['yandex_map_key']['value'] = 'AOgaLU0BAAAALVWJIAMAB4e9K6YyAg5jTVAvW3Es0yuVgU8AAAAAAAAAAACaL6S_CdTksT02kqGPG3wuXFoqVQ==';
		$data_model['yandex_map_key']['type'] = 'safe_string';
		
		$data_model['apps.seo.level_enable']['name'] = 'apps.seo.level_enable';
		$data_model['apps.seo.level_enable']['title'] = 'Включить SEO-режим с многоуровневым URL для каталогов';
		$data_model['apps.seo.level_enable']['value'] = '0';
		$data_model['apps.seo.level_enable']['type'] = 'checkbox';
		
		$data_model['apps.seo.html_prefix_enable']['name'] = 'apps.seo.html_prefix_enable';
		$data_model['apps.seo.html_prefix_enable']['title'] = 'Включить .html постфиксы в конце URL объявлений';
		$data_model['apps.seo.html_prefix_enable']['value'] = '0';
		$data_model['apps.seo.html_prefix_enable']['type'] = 'checkbox';
		
		$data_model['allow_topic_images']['name'] = 'allow_topic_images';
		$data_model['allow_topic_images']['title'] = 'Разрешить картинки в разделах';
		$data_model['allow_topic_images']['value'] = '0';
		$data_model['allow_topic_images']['type'] = 'checkbox';
		
		$data_model['default_tab_name']['name'] = 'default_tab_name';
		$data_model['default_tab_name']['title'] = 'Название закладки формы по-умолчанию';
		$data_model['default_tab_name']['value'] = 'Основное';
		$data_model['default_tab_name']['type'] = 'safe_string';
		
		$data_model['use_registration_email_confirm']['name'] = 'use_registration_email_confirm';
		$data_model['use_registration_email_confirm']['title'] = 'Использовать активацию аккаунта по email при регистрации';
		$data_model['use_registration_email_confirm']['value'] = '0';
		$data_model['use_registration_email_confirm']['type'] = 'checkbox';
		
		$data_model['divide_step_form']['name'] = 'divide_step_form';
		$data_model['divide_step_form']['title'] = 'Делить формы на шаги';
		$data_model['divide_step_form']['value'] = '0';
		$data_model['divide_step_form']['type'] = 'checkbox';
		
		$data_model['filter_double_data']['name'] = 'filter_double_data';
		$data_model['filter_double_data']['title'] = 'Не допускать добавления дубликатов данных';
		$data_model['filter_double_data']['value'] = '0';
		$data_model['filter_double_data']['type'] = 'checkbox';
		
		$data_model['apps.geodata.enable']['name'] = 'apps.geodata.enable';
		$data_model['apps.geodata.enable']['title'] = 'Включить приложение GeoData';
		$data_model['apps.geodata.enable']['value'] = '0';
		$data_model['apps.geodata.enable']['type'] = 'checkbox';
		
		$data_model['apps.geodata.geocode_partial']['name'] = 'apps.geodata.geocode_partial';
		$data_model['apps.geodata.geocode_partial']['title'] = 'Геокодировать неполные данные';
		$data_model['apps.geodata.geocode_partial']['value'] = '0';
		$data_model['apps.geodata.geocode_partial']['type'] = 'checkbox';
		
		$data_model['apps.mailbox.enable']['name'] = 'apps.mailbox.enable';
		$data_model['apps.mailbox.enable']['title'] = 'Включить приложение Mailbox';
		$data_model['apps.mailbox.enable']['value'] = '0';
		$data_model['apps.mailbox.enable']['type'] = 'checkbox';
		
		$data_model['apps.mysearch.enable']['name'] = 'apps.mysearch.enable';
		$data_model['apps.mysearch.enable']['title'] = 'Включить приложение Мой поиск';
		$data_model['apps.mysearch.enable']['value'] = '0';
		$data_model['apps.mysearch.enable']['type'] = 'checkbox';
		
		$data_model['apps.sitemap.changefreq.data']['name'] = 'apps.sitemap.changefreq.data';
		$data_model['apps.sitemap.changefreq.data']['title'] = 'Вероятная частота изменения <b>объявления</b>. Одно из числовых значений (1-всегда, 2-ежечасно, 3-ежедневно, 4-еженедельно, 5-ежемесячно, 6-ежегодно, 7-никогда)';
		$data_model['apps.sitemap.changefreq.data']['value'] = '';
		$data_model['apps.sitemap.changefreq.data']['type'] = 'select_box';
		$data_model['apps.sitemap.changefreq.data']['select_data'] = array('1'=>'всегда','2'=>'ежечасно','3'=>'ежедневно','4'=>'еженедельно','5'=>'ежемесячно','6'=>'ежегодно','7'=>'никогда');
		
		$data_model['apps.sitemap.changefreq.company']['name'] = 'apps.sitemap.changefreq.company';
		$data_model['apps.sitemap.changefreq.company']['title'] = 'Вероятная частота изменения страницы компании. Одно из числовых значений (1-всегда, 2-ежечасно, 3-ежедневно, 4-еженедельно, 5-ежемесячно, 6-ежегодно, 7-никогда)';
		$data_model['apps.sitemap.changefreq.company']['value'] = '';
		$data_model['apps.sitemap.changefreq.company']['type'] = 'select_box';
		$data_model['apps.sitemap.changefreq.company']['select_data'] = array('1'=>'всегда','2'=>'ежечасно','3'=>'ежедневно','4'=>'еженедельно','5'=>'ежемесячно','6'=>'ежегодно','7'=>'никогда');
		
		$data_model['apps.sitemap.priority.data']['name'] = 'apps.sitemap.priority.data';
		$data_model['apps.sitemap.priority.data']['title'] = 'Приоритетность URL <b>объявлений</b> относительно других URL на Вашем сайте. Диапазон от 0.0 до 1.0';
		$data_model['apps.sitemap.priority.data']['value'] = '';
		$data_model['apps.sitemap.priority.data']['type'] = 'safe_string';
		
		$data_model['apps.sitemap.priority.company']['name'] = 'apps.sitemap.priority.company';
		$data_model['apps.sitemap.priority.company']['title'] = 'Приоритетность URL <b>компании</b> относительно других URL на Вашем сайте. Диапазон от 0,0 до 1,0';
		$data_model['apps.sitemap.priority.company']['value'] = '';
		$data_model['apps.sitemap.priority.company']['type'] = 'safe_string';
		
		$data_model['apps.sitemap.data_enable']['name'] = 'apps.sitemap.data_enable';
		$data_model['apps.sitemap.data_enable']['title'] = 'Выводить объявления в sitemap';
		$data_model['apps.sitemap.data_enable']['value'] = '0';
		$data_model['apps.sitemap.data_enable']['type'] = 'checkbox';
		
		$data_model['apps.sitemap.company_enable']['name'] = 'apps.sitemap.company_enable';
		$data_model['apps.sitemap.company_enable']['title'] = 'Выводить компании в sitemap';
		$data_model['apps.sitemap.company_enable']['value'] = '0';
		$data_model['apps.sitemap.company_enable']['type'] = 'checkbox';
		
		$data_model['apps.booking.enable']['name'] = 'apps.booking.enable';
		$data_model['apps.booking.enable']['title'] = 'Включить приложение Booking';
		$data_model['apps.booking.enable']['value'] = '0';
		$data_model['apps.booking.enable']['type'] = 'checkbox';
		
		$data_model['use_combobox']['name'] = 'use_combobox';
		$data_model['use_combobox']['title'] = 'Использовать combobox в элементах select';
		$data_model['use_combobox']['value'] = '0';
		$data_model['use_combobox']['type'] = 'checkbox';
		
		$data_model['disable_root_structure_select']['name'] = 'disable_root_structure_select';
		$data_model['disable_root_structure_select']['title'] = 'Блокировать корневые элементы в селектбоксах структуры';
		$data_model['disable_root_structure_select']['value'] = '0';
		$data_model['disable_root_structure_select']['type'] = 'select_box';
		$data_model['disable_root_structure_select']['select_data'] = array('0'=>'не блокировать','1'=>'только верхний уровень','2'=>'все не крайние разделы');
		
		$data_model['ignore_free_from_parameter']['name'] = 'ignore_free_from_parameter';
		$data_model['ignore_free_from_parameter']['title'] = 'Игнорировать свободно с';
		$data_model['ignore_free_from_parameter']['value'] = '0';
		$data_model['ignore_free_from_parameter']['type'] = 'checkbox';
		
		$data_model['show_cattree_left']['name'] = 'show_cattree_left';
		$data_model['show_cattree_left']['title'] = 'Выводить дерево каталогов слева в списке объявлений';
		$data_model['show_cattree_left']['value'] = '0';
		$data_model['show_cattree_left']['type'] = 'checkbox';
		
		$data_model['use_new_realty_grid']['name'] = 'use_new_realty_grid';
		$data_model['use_new_realty_grid']['title'] = 'Использовать настраиваемую сетку в выводе в админке (тестовый режим)';
		$data_model['use_new_realty_grid']['value'] = '0';
		$data_model['use_new_realty_grid']['type'] = 'checkbox';
		
		$data_model['link_metro_to_district']['name'] = 'link_metro_to_district';
		$data_model['link_metro_to_district']['title'] = 'Привязать метро к районам';
		$data_model['link_metro_to_district']['value'] = '0';
		$data_model['link_metro_to_district']['type'] = 'checkbox';
		
		$data_model['notify_admin_about_register']['name'] = 'notify_admin_about_register';
		$data_model['notify_admin_about_register']['title'] = 'Уведомлять администратора о новой регистрации пользователя';
		$data_model['notify_admin_about_register']['value'] = '0';
		$data_model['notify_admin_about_register']['type'] = 'checkbox';
				
		$data_model['enable_special_in_account']['name'] = 'enable_special_in_account';
		$data_model['enable_special_in_account']['title'] = 'В личном кабинете доступна галочка спец.размещений';
		$data_model['enable_special_in_account']['value'] = '0';
		$data_model['enable_special_in_account']['type'] = 'checkbox';
				
		$data_model['use_custom_addform']['name'] = 'use_custom_addform';
		$data_model['use_custom_addform']['title'] = 'Использовать локальную форму добавления объявлений';
		$data_model['use_custom_addform']['value'] = '0';
		$data_model['use_custom_addform']['type'] = 'checkbox';
		
		$data_model['show_up_icon']['name'] = 'show_up_icon';
		$data_model['show_up_icon']['title'] = 'Админ может поднимать объявления';
		$data_model['show_up_icon']['value'] = '0';
		$data_model['show_up_icon']['type'] = 'checkbox';
		
		$data_model['block_user_search_forms']['name'] = 'block_user_search_forms';
		$data_model['block_user_search_forms']['title'] = 'Блокировать формы поиска пользователя';
		$data_model['block_user_search_forms']['value'] = '0';
		$data_model['block_user_search_forms']['type'] = 'checkbox';
		
		$data_model['block_user_front_grids']['name'] = 'block_user_front_grids';
		$data_model['block_user_front_grids']['title'] = 'Блокировать фронтальные сетки пользователя';
		$data_model['block_user_front_grids']['value'] = '0';
		$data_model['block_user_front_grids']['type'] = 'checkbox';
		
		$data_model['apps.client.enable']['name'] = 'apps.client.enable';
		$data_model['apps.client.enable']['title'] = 'Включить приложение';
		$data_model['apps.client.enable']['value'] = '0';
		$data_model['apps.client.enable']['type'] = 'checkbox';
		
		$data_model['apps.comment.enable']['name'] = 'apps.comment.enable';
		$data_model['apps.comment.enable']['title'] = 'Включить приложение Комментарии';
		$data_model['apps.comment.enable']['value'] = '0';
		$data_model['apps.comment.enable']['type'] = 'checkbox';
		
		$data_model['apps.company.profile_in_lk']['name'] = 'apps.company.profile_in_lk';
		$data_model['apps.company.profile_in_lk']['title'] = 'Выводить закладку профиля компания в личном кабинете';
		$data_model['apps.company.profile_in_lk']['value'] = '0';
		$data_model['apps.company.profile_in_lk']['type'] = 'checkbox';
		
		$data_model['apps.geodata.on_home']['name'] = 'apps.geodata.on_home';
		$data_model['apps.geodata.on_home']['title'] = 'Выводить карту на главной странице';
		$data_model['apps.geodata.on_home']['value'] = '0';
		$data_model['apps.geodata.on_home']['type'] = 'checkbox';
		
		$data_model['apps.geodata.show_grid_map']['name'] = 'apps.geodata.show_grid_map';
		$data_model['apps.geodata.show_grid_map']['title'] = 'Выводить карту вместе со списком объявлений';
		$data_model['apps.geodata.show_grid_map']['value'] = '0';
		$data_model['apps.geodata.show_grid_map']['type'] = 'checkbox';
		
		$data_model['apps.getrent.enable']['name'] = 'apps.getrent.enable';
		$data_model['apps.getrent.enable']['title'] = 'Включить приложение Заявки на аренду';
		$data_model['apps.getrent.enable']['value'] = '0';
		$data_model['apps.getrent.enable']['type'] = 'checkbox';
		
		$data_model['apps.news.use_news_topics']['name'] = 'apps.news.use_news_topics';
		$data_model['apps.news.use_news_topics']['title'] = 'Использовать категории для новостей';
		$data_model['apps.news.use_news_topics']['value'] = '0';
		$data_model['apps.news.use_news_topics']['type'] = 'checkbox';
		
		$data_model['date_format']['name'] = 'date_format';
		$data_model['date_format']['title'] = 'Формат даты';
		$data_model['date_format']['value'] = '';
		$data_model['date_format']['type'] = 'select_box';
		$data_model['date_format']['select_data'] = array('standart'=>'standart','eu'=>'EU','us'=>'US');
		
		$data_model['save_without_watermark']['name'] = 'save_without_watermark';
		$data_model['save_without_watermark']['title'] = 'Сохранять копию изображений без водяного знака';
		$data_model['save_without_watermark']['value'] = '0';
		$data_model['save_without_watermark']['type'] = 'checkbox';
		
		$data_model['check_permissions']['name'] = 'check_permissions';
		$data_model['check_permissions']['title'] = 'Разделение прав доступа для групп. Группа администраторов (admin) имеет доступ ко всем функциям без учета прав доступа.';
		$data_model['check_permissions']['value'] = '0';
		$data_model['check_permissions']['type'] = 'checkbox';
		
		
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
				self::$themes_array=$template_array;
			}
		}else{
			$template_array=self::$themes_array;
		}
		
		return $template_array;
	}
	
}