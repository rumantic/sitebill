<?php
require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php';
class columns_Model extends Data_Model {

	public function __construct(){
		parent::__construct();
	}

	public function get_model(){
		$form_data = array();

		$form_data['columns']['columns_id']['name'] = 'columns_id';
		$form_data['columns']['columns_id']['title'] = 'ID';
		$form_data['columns']['columns_id']['value'] = 0;
		$form_data['columns']['columns_id']['type'] = 'primary_key';
		$form_data['columns']['columns_id']['required'] = 'off';
		$form_data['columns']['columns_id']['unique'] = 'off';

		$form_data['columns']['active']['name'] = 'active';
		$form_data['columns']['active']['title'] = _e('Колонка активна');
		$form_data['columns']['active']['value'] = 1;
		$form_data['columns']['active']['type'] = 'checkbox';
		$form_data['columns']['active']['required'] = 'off';
		$form_data['columns']['active']['unique'] = 'off';
        $form_data['columns']['active']['fxFlex'] = 100;

		$form_data['columns']['sort_order']['name'] = 'sort_order';
		$form_data['columns']['sort_order']['title'] = _e('Порядок сортировки');
		$form_data['columns']['sort_order']['value'] = 0;
		$form_data['columns']['sort_order']['type'] = 'hidden';
		$form_data['columns']['sort_order']['required'] = 'off';
		$form_data['columns']['sort_order']['unique'] = 'off';

		$form_data['columns']['table_id']['name'] = 'table_id';
		$form_data['columns']['table_id']['primary_key_name'] = 'table_id';
		$form_data['columns']['table_id']['primary_key_table'] = 'table';
		$form_data['columns']['table_id']['title'] = _e('Таблица');
		$form_data['columns']['table_id']['value_string'] = '';
		$form_data['columns']['table_id']['value'] = 0;
		$form_data['columns']['table_id']['length'] = 40;
		$form_data['columns']['table_id']['type'] = 'select_by_query';
		$form_data['columns']['table_id']['query'] = 'select * from '.DB_PREFIX.'_table order by name';
		$form_data['columns']['table_id']['value_name'] = 'name';
		$form_data['columns']['table_id']['title_default'] = _e('выбрать таблицу');
		$form_data['columns']['table_id']['value_default'] = 0;
		$form_data['columns']['table_id']['required'] = 'on';
		$form_data['columns']['table_id']['unique'] = 'off';
        $form_data['columns']['table_id']['tab'] = _e('Параметры');
        $form_data['columns']['table_id']['fxFlex'] = 100;

		$form_data['columns']['type']['name'] = 'type';
		$form_data['columns']['type']['title'] = _e('Тип записи');
		$form_data['columns']['type']['value'] = '';
		$form_data['columns']['type']['type'] = 'select_box';
        $form_data['columns']['type']['fxFlex'] = 100;

		$seld=array(
				/*'avatar' => 'avatar',*/
				'primary_key' => 'primary_key ('._e('ключевое поле').')',
				'safe_string' => 'safe_string ('._e('строка').')',
				'hidden' => 'hidden ('._e('скрытое поле').')',
				'checkbox' => 'checkbox ('._e('галочка').')',
				'select_box_structure' => 'select_box_structure ('._e('выбор типа из списка').')',
				'select_by_query' => 'select_by_query ('._e('выбор из списка записей из базы').')',
				'select_entity' => 'select_entity',
				'select_box' => 'select_box ('._e('выбор из списка статичных значений').')',
				'auto_add_value' => 'auto_add_value',
				'price' => 'price ('._e('цена').')',
				'textarea' => 'textarea ('._e('поле ввода текста').')',
				'uploadify_image' => 'uploadify_image ('._e('устарело').')',
				'uploadify_file' => 'uploadify_file ('._e('устарело').')',
				'mobilephone' => 'mobilephone ('._e('мобильный телефон').')',
				'password' => 'password ('._e('пароль').')',
				'photo' => 'photo ('._e('фото - аватар').')',
				'geodata' => 'geodata ('._e('координаты').')',
				'structure' => 'structure ('._e('структура').')',
				'textarea_editor' => 'textarea_editor ('._e('поле ввода текста с редактором').')',
				'date'=>'date ('._e('дата').' DD.MM.YYYY)',
				'attachment'=>'attachment ('._e('вложение').')',
				'tlocation'=>'tlocation ('._e('связанные списки географии').')',
				'captcha'=>'captcha ('._e('защитный код').')',
				'dtdatetime'=>'dtdatetime ('._e('дата и время').' DD.MM.YYYY H:i:s)',
				'dtdate'=>'dtdate ('._e('дата').' DD.MM.YYYY)',
				'dttime'=>'dttime ('._e('время').' H:i:s)',
				'uploads'=>'uploads ('._e('загрузка фотографий').')',
				'gadres' => 'gadres',
				'client_id' => 'client_id ('._e('связь с таблицей клиентов').')',
				'grade'=>'grade ('._e('оценка').')',
				'docuploads'=>'docuploads ('._e('загрузка документов').')',
				'select_by_query_multi'=>'select_by_query_multi ('._e('выбор из списка записей из базы с множественным выбором').')',
				'separator'=>'separator ('._e('визуальный разделитель').')',
				'injector'=>'injector ('._e('код из компонента').')',
                'parameter'=>'parameter ('._e('свободный ввод параметров').' key=value)',
				'compose'=>'compose ('._e('Объединение колонок и функций').')',
                'youtube'=>'youtube ('._e('Код видео Youtube').')'
				);
		asort($seld);
		$form_data['columns']['type']['select_data'] = $seld;
        $select_data_indexed = array();
        foreach ( $seld as $key_s => $value_s ) {
            array_push($select_data_indexed, array('id'=>$key_s, 'value' => $value_s));
        }
        $form_data['columns']['type']['select_data_indexed'] = $select_data_indexed;

		$form_data['columns']['type']['required'] = 'on';
		$form_data['columns']['type']['unique'] = 'off';

		$form_data['columns']['group_id']['name'] = 'group_id';
		$form_data['columns']['group_id']['primary_key_name'] = 'group_id';
		$form_data['columns']['group_id']['primary_key_table'] = 'group';
		$form_data['columns']['group_id']['title'] = _e('Доступен для групп (по-умолчанию доступно всем)');
		$form_data['columns']['group_id']['value_string'] = '';
		$form_data['columns']['group_id']['value'] = 0;
		$form_data['columns']['group_id']['length'] = 40;
		$form_data['columns']['group_id']['type'] = 'select_by_query_multiple';
		$form_data['columns']['group_id']['query'] = 'select * from '.DB_PREFIX.'_group order by name';
		$form_data['columns']['group_id']['value_name'] = 'name';
		$form_data['columns']['group_id']['title_default'] = _e('выбрать группу');
		$form_data['columns']['group_id']['value_default'] = 0;
		$form_data['columns']['group_id']['required'] = 'off';
		$form_data['columns']['group_id']['unique'] = 'off';
        $form_data['columns']['group_id']['tab'] = _e('Доступы и активность');
        $form_data['columns']['group_id']['fxFlex'] = 100;



		$form_data['columns']['active_in_topic']['name'] = 'active_in_topic';
		$form_data['columns']['active_in_topic']['title'] = _e('Активно в категории (по-умолчанию активно везде)');
		$form_data['columns']['active_in_topic']['value_string'] = '';
		$form_data['columns']['active_in_topic']['value'] = 0;
		$form_data['columns']['active_in_topic']['length'] = 40;
		//$form_data['columns']['active_in_topic']['type'] = 'select_box_structure_multiple';
		$form_data['columns']['active_in_topic']['type'] = 'select_box_structure_multiple_checkbox';
		$form_data['columns']['active_in_topic']['required'] = 'off';
		$form_data['columns']['active_in_topic']['unique'] = 'off';
        $form_data['columns']['active_in_topic']['tab'] = _e('Доступы и активность');
        $form_data['columns']['active_in_topic']['fxFlex'] = 100;


		if(defined('DEVMODE')){
			if(1===intval($this->getConfigValue('apps.table.additional_filtering'))){
				$form_data['columns']['active_in_optype']['name'] = 'active_in_optype';
				$form_data['columns']['active_in_optype']['title'] = 'Активно в категории 2';
				$form_data['columns']['active_in_optype']['value'] = '';
				$form_data['columns']['active_in_optype']['type'] = 'hidden';
				$form_data['columns']['active_in_optype']['required'] = 'off';
				$form_data['columns']['active_in_optype']['unique'] = 'off';
			}
		}





		$form_data['columns']['name']['name'] = 'name';
		$form_data['columns']['name']['title'] = _e('Название колонки (системное только латиница)');
		$form_data['columns']['name']['value'] = '';
		$form_data['columns']['name']['type'] = 'safe_string';
		$form_data['columns']['name']['required'] = 'on';
		$form_data['columns']['name']['unique'] = 'off';

		$langs=Multilanguage::availableLanguages();

        $default_lng = '';
		if(1 == $this->getConfigValue('apps.language.use_default_as_ru')){
			$default_lng = 'ru';
		}elseif('' != trim($this->getConfigValue('apps.language.use_as_default'))){
			$default_lng = trim($this->getConfigValue('apps.language.use_as_default'));
		}

		if($default_lng != ''){
			foreach ($langs as $k => $lng) {
				if ($lng == $default_lng) {
					unset($langs[$k]);
					break;
				}
			}
		}
		//print_r($langs);

		$form_data['columns']['title']['name'] = 'title';
		$form_data['columns']['title']['title'] = _e('Название колонки (для человека)');
		$form_data['columns']['title']['value'] = '';
		$form_data['columns']['title']['type'] = 'safe_string';
		$form_data['columns']['title']['required'] = 'on';
		$form_data['columns']['title']['unique'] = 'off';

		if(count($langs)>0){
			foreach ($langs as $lang){
				$form_data['columns']['title_'.$lang]['name'] = 'title_'.$lang;
				$form_data['columns']['title_'.$lang]['title'] = _e('Название колонки (для человека) ').' '.$lang;
				$form_data['columns']['title_'.$lang]['value'] = '';
				$form_data['columns']['title_'.$lang]['type'] = 'safe_string';
				$form_data['columns']['title_'.$lang]['required'] = 'off';
				$form_data['columns']['title_'.$lang]['unique'] = 'off';
			}
		}




		$form_data['columns']['hint']['name'] = 'hint';
		$form_data['columns']['hint']['title'] = _e('Подсказка (для человека)');
		$form_data['columns']['hint']['value'] = '';
		$form_data['columns']['hint']['type'] = 'safe_string';
		$form_data['columns']['hint']['required'] = 'off';
		$form_data['columns']['hint']['unique'] = 'off';
        $form_data['columns']['hint']['fxFlex'] = 100;


		if(count($langs)>0){
			foreach ($langs as $lang){
				$form_data['columns']['hint_'.$lang]['name'] = 'hint_'.$lang;
				$form_data['columns']['hint_'.$lang]['title'] = _e('Подсказка (для человека) ').' '.$lang;
				$form_data['columns']['hint_'.$lang]['value'] = '';
				$form_data['columns']['hint_'.$lang]['type'] = 'safe_string';
				$form_data['columns']['hint_'.$lang]['required'] = 'off';
				$form_data['columns']['hint_'.$lang]['unique'] = 'off';
			}
		}

		$form_data['columns']['value']['name'] = 'value';
		$form_data['columns']['value']['title'] = _e('Значение по-умолчанию');
		$form_data['columns']['value']['value'] = '';
		$form_data['columns']['value']['type'] = 'safe_string';
		$form_data['columns']['value']['required'] = 'off';
		$form_data['columns']['value']['unique'] = 'off';
        $form_data['columns']['value']['tab'] = _e('Параметры');




		/*$form_data['columns']['primary_key_table']['name'] = 'primary_key_table';
		$form_data['columns']['primary_key_table']['title'] = 'Название таблицы из которой получаем данные для связки';
		$form_data['columns']['primary_key_table']['value'] = '';
		$form_data['columns']['primary_key_table']['type'] = 'safe_string';
		$form_data['columns']['primary_key_table']['required'] = 'off';
		$form_data['columns']['primary_key_table']['unique'] = 'off';*/

		$form_data['columns']['primary_key_table']['name'] = 'primary_key_table';
		$form_data['columns']['primary_key_table']['title'] = _e('Название таблицы из которой получаем данные для связки');
		$form_data['columns']['primary_key_table']['value'] = '';
		$form_data['columns']['primary_key_table']['type'] = 'select_box';
		$form_data['columns']['primary_key_table']['select_data'] = array();
		$form_data['columns']['primary_key_table']['required'] = 'off';
		$form_data['columns']['primary_key_table']['unique'] = 'off';
        $form_data['columns']['primary_key_table']['fxFlex'] = 100;
        $form_data['columns']['primary_key_table']['api'] = new api\types\method('api', 'table', 'get_tables_list');


		/*$form_data['columns']['primary_key_name']['name'] = 'primary_key_name';
		$form_data['columns']['primary_key_name']['title'] = 'Название ключа связки с другой таблицей';
		$form_data['columns']['primary_key_name']['value'] = '';
		$form_data['columns']['primary_key_name']['type'] = 'safe_string';
		$form_data['columns']['primary_key_name']['required'] = 'off';
		$form_data['columns']['primary_key_name']['unique'] = 'off';*/

		$form_data['columns']['primary_key_name']['name'] = 'primary_key_name';
		$form_data['columns']['primary_key_name']['title'] = _e('Название ключа связки с другой таблицей');
		$form_data['columns']['primary_key_name']['value'] = '';
		$form_data['columns']['primary_key_name']['type'] = 'select_box';
		$form_data['columns']['primary_key_name']['select_data'] = array();
		$form_data['columns']['primary_key_name']['required'] = 'off';
		$form_data['columns']['primary_key_name']['unique'] = 'off';
        $form_data['columns']['primary_key_name']['api'] = new api\types\method('api', 'table', 'get_columns_list');


		/*$form_data['columns']['value_name']['name'] = 'value_name';
		$form_data['columns']['value_name']['title'] = 'Название переменной для select_box';
		$form_data['columns']['value_name']['value'] = '';
		$form_data['columns']['value_name']['type'] = 'safe_string';
		$form_data['columns']['value_name']['required'] = 'off';
		$form_data['columns']['value_name']['unique'] = 'off';*/

		$form_data['columns']['value_name']['name'] = 'value_name';
		$form_data['columns']['value_name']['title'] = _e('Название переменной для select_box');
		$form_data['columns']['value_name']['value'] = '';
		$form_data['columns']['value_name']['type'] = 'select_box';
		$form_data['columns']['value_name']['select_data'] = array();
		$form_data['columns']['value_name']['required'] = 'off';
		$form_data['columns']['value_name']['unique'] = 'off';
        $form_data['columns']['value_name']['api'] = new api\types\method('api', 'table', 'get_columns_list');


		/*$form_data['columns']['value_string']['name'] = 'value_string';
		$form_data['columns']['value_string']['title'] = 'Строковое значение получаемое при выборке select';
		$form_data['columns']['value_string']['value'] = '';
		$form_data['columns']['value_string']['type'] = 'safe_string';
		$form_data['columns']['value_string']['required'] = 'off';
		$form_data['columns']['value_string']['unique'] = 'off';*/

		$form_data['columns']['query']['name'] = 'query';
		$form_data['columns']['query']['title'] = _e('SQL-запрос для получения списка записей из связанной таблицы');
		$form_data['columns']['query']['value'] = '';
		$form_data['columns']['query']['type'] = 'safe_string';
		$form_data['columns']['query']['required'] = 'off';
		$form_data['columns']['query']['unique'] = 'off';



		$form_data['columns']['title_default']['name'] = 'title_default';
		$form_data['columns']['title_default']['title'] = _e('Заголовок строчки в select_box по-умолчанию');
		$form_data['columns']['title_default']['value'] = '';
		$form_data['columns']['title_default']['type'] = 'safe_string';
		$form_data['columns']['title_default']['required'] = 'off';
		$form_data['columns']['title_default']['unique'] = 'off';

		if(count($langs)>0){
			foreach ($langs as $lang){
				$form_data['columns']['title_default_'.$lang]['name'] = 'title_default_'.$lang;
				$form_data['columns']['title_default_'.$lang]['title'] = _e('Заголовок строчки в select_box по-умолчанию ').' '.$lang;
				$form_data['columns']['title_default_'.$lang]['value'] = '';
				$form_data['columns']['title_default_'.$lang]['type'] = 'safe_string';
				$form_data['columns']['title_default_'.$lang]['required'] = 'off';
				$form_data['columns']['title_default_'.$lang]['unique'] = 'off';
			}
		}

		$form_data['columns']['value_default']['name'] = 'value_default';
		$form_data['columns']['value_default']['title'] = _e('Значение строчки в select_box по-умолчанию');
		$form_data['columns']['value_default']['value'] = '';
		$form_data['columns']['value_default']['type'] = 'safe_string';
		$form_data['columns']['value_default']['required'] = 'off';
		$form_data['columns']['value_default']['unique'] = 'off';

		$form_data['columns']['value_table']['name'] = 'value_table';
		$form_data['columns']['value_table']['title'] = 'value_table';
		$form_data['columns']['value_table']['value'] = '';
		$form_data['columns']['value_table']['type'] = 'safe_string';
		$form_data['columns']['value_table']['required'] = 'off';
		$form_data['columns']['value_table']['unique'] = 'off';

		$form_data['columns']['value_primary_key']['name'] = 'value_primary_key';
		$form_data['columns']['value_primary_key']['title'] = 'value_primary_key';
		$form_data['columns']['value_primary_key']['value'] = '';
		$form_data['columns']['value_primary_key']['type'] = 'safe_string';
		$form_data['columns']['value_primary_key']['required'] = 'off';
		$form_data['columns']['value_primary_key']['unique'] = 'off';

		$form_data['columns']['value_field']['name'] = 'value_field';
		$form_data['columns']['value_field']['title'] = 'value_field';
		$form_data['columns']['value_field']['value'] = '';
		$form_data['columns']['value_field']['type'] = 'safe_string';
		$form_data['columns']['value_field']['required'] = 'off';
		$form_data['columns']['value_field']['unique'] = 'off';

		$form_data['columns']['assign_to']['name'] = 'assign_to';
		$form_data['columns']['assign_to']['title'] = 'assign_to';
		$form_data['columns']['assign_to']['value'] = '';
		$form_data['columns']['assign_to']['type'] = 'safe_string';
		$form_data['columns']['assign_to']['required'] = 'off';
		$form_data['columns']['assign_to']['unique'] = 'off';

		$form_data['columns']['dbtype']['name'] = 'dbtype';
		$form_data['columns']['dbtype']['title'] = _e('SQL-тип поля (\'notable\' - вспомогательное, \'\' - основное)');
		$form_data['columns']['dbtype']['value'] = '';
		$form_data['columns']['dbtype']['type'] = 'safe_string';
		$form_data['columns']['dbtype']['required'] = 'off';
		$form_data['columns']['dbtype']['unique'] = 'off';
        $form_data['columns']['dbtype']['fxFlex'] = 100;


		$form_data['columns']['dbtype']['name'] = 'dbtype';
		$form_data['columns']['dbtype']['title'] = _e('Хранить значение поля в таблице');
		$form_data['columns']['dbtype']['value'] = 1;
		$form_data['columns']['dbtype']['type'] = 'checkbox';
		$form_data['columns']['dbtype']['required'] = 'off';
		$form_data['columns']['dbtype']['unique'] = 'off';
        $form_data['columns']['dbtype']['fxFlex'] = 100;

		$form_data['columns']['select_data']['name'] = 'select_data';
		$form_data['columns']['select_data']['title'] = _e('Набор опций выбора в формате пар').' {key~~value}';
		$form_data['columns']['select_data']['value'] = '';
		$form_data['columns']['select_data']['type'] = 'textarea';
		$form_data['columns']['select_data']['required'] = 'off';
		$form_data['columns']['select_data']['unique'] = 'off';

		if(count($langs)>0){
			foreach ($langs as $lang){
				$form_data['columns']['select_data_'.$lang]['name'] = 'select_data_'.$lang;
				$form_data['columns']['select_data_'.$lang]['title'] = _e('Набор опций выбора в формате пар'). ' {key~~value} '.' '.$lang;
				$form_data['columns']['select_data_'.$lang]['value'] = '';
				$form_data['columns']['select_data_'.$lang]['type'] = 'textarea';
				$form_data['columns']['select_data_'.$lang]['required'] = 'off';
				$form_data['columns']['select_data_'.$lang]['unique'] = 'off';
			}
		}

        /*
         * Устаревшие поля. Костя, они нужны тебе?
		$form_data['columns']['table_name']['name'] = 'table_name';
		$form_data['columns']['table_name']['title'] = _e('Uploadify image: имя таблицы');
		$form_data['columns']['table_name']['value'] = '';
		$form_data['columns']['table_name']['type'] = 'safe_string';
		$form_data['columns']['table_name']['required'] = 'off';
		$form_data['columns']['table_name']['unique'] = 'off';

		$form_data['columns']['primary_key']['name'] = 'primary_key';
		$form_data['columns']['primary_key']['title'] = _e('Uploadify image: имя первичного ключа объекта к которому привязываются изображения');
		$form_data['columns']['primary_key']['value'] = '';
		$form_data['columns']['primary_key']['type'] = 'safe_string';
		$form_data['columns']['primary_key']['required'] = 'off';
		$form_data['columns']['primary_key']['unique'] = 'off';

		$form_data['columns']['primary_key_value']['name'] = 'primary_key_value';
		$form_data['columns']['primary_key_value']['title'] = _e('Uploadify image: значение первичного ключа');
		$form_data['columns']['primary_key_value']['value'] = '';
		$form_data['columns']['primary_key_value']['type'] = 'safe_string';
		$form_data['columns']['primary_key_value']['required'] = 'off';
		$form_data['columns']['primary_key_value']['unique'] = 'off';

		$form_data['columns']['action']['name'] = 'action';
		$form_data['columns']['action']['title'] = 'Uploadify image: имя action';
		$form_data['columns']['action']['value'] = '';
		$form_data['columns']['action']['type'] = 'safe_string';
		$form_data['columns']['action']['required'] = 'off';
		$form_data['columns']['action']['unique'] = 'off';
        */

		$form_data['columns']['entity']['name'] = 'entity';
		$form_data['columns']['entity']['title'] = _e('Сущность структуры');
		$form_data['columns']['entity']['value'] = '';
		$form_data['columns']['entity']['type'] = 'safe_string';
		$form_data['columns']['entity']['required'] = 'off';
		$form_data['columns']['entity']['unique'] = 'off';

		/*
         * Устаревшие поля. Костя, они нужны тебе?
        $form_data['columns']['combo']['name'] = 'combo';
		$form_data['columns']['combo']['title'] = _e('Использовать комбобокс-виджет');
		$form_data['columns']['combo']['value'] = 0;
		$form_data['columns']['combo']['type'] = 'checkbox';
		$form_data['columns']['combo']['required'] = 'off';
		$form_data['columns']['combo']['unique'] = 'off';
		*/


		$form_data['columns']['required']['name'] = 'required';
		$form_data['columns']['required']['title'] = _e('Обязательное поле');
		$form_data['columns']['required']['value'] = 0;
		$form_data['columns']['required']['type'] = 'checkbox';
		$form_data['columns']['required']['required'] = 'off';
		$form_data['columns']['required']['unique'] = 'off';
        $form_data['columns']['required']['tab'] = _e('Параметры');
        $form_data['columns']['required']['fxFlex'] = 100;


		$form_data['columns']['unique']['name'] = 'unique';
		$form_data['columns']['unique']['title'] = _e('Уникальное поле');
		$form_data['columns']['unique']['value'] = 0;
		$form_data['columns']['unique']['type'] = 'checkbox';
		$form_data['columns']['unique']['required'] = 'off';
		$form_data['columns']['unique']['unique'] = 'off';
        $form_data['columns']['unique']['tab'] = _e('Параметры');
        $form_data['columns']['unique']['fxFlex'] = 100;

		/*$form_data['columns']['is_ml']['name'] = 'is_ml';
		$form_data['columns']['is_ml']['title'] = 'Мультиязычность';
		$form_data['columns']['is_ml']['value'] = 0;
		$form_data['columns']['is_ml']['type'] = 'checkbox';
		$form_data['columns']['is_ml']['required'] = 'off';
		$form_data['columns']['is_ml']['unique'] = 'off';*/

		$form_data['columns']['parameters']['name'] = 'parameters';
		$form_data['columns']['parameters']['title'] = _e('Параметры');
		$form_data['columns']['parameters']['value'] = 0;
		$form_data['columns']['parameters']['type'] = 'parameter';
		$form_data['columns']['parameters']['required'] = 'off';
		$form_data['columns']['parameters']['unique'] = 'off';
        $form_data['columns']['parameters']['tab'] = _e('Параметры');
        $form_data['columns']['parameters']['fxFlex'] = 100;

		$form_data['columns']['tab']['name'] = 'tab';
		$form_data['columns']['tab']['title'] = _e('Имя вкладки в форме. Если не указано, то размешается во вкладке по-умолчанию');
		$form_data['columns']['tab']['value'] = '';
		$form_data['columns']['tab']['type'] = 'safe_string';
		$form_data['columns']['tab']['required'] = 'off';
		$form_data['columns']['tab']['unique'] = 'off';
        $form_data['columns']['tab']['fxFlex'] = 100;

		if(count($langs)>0){
			foreach ($langs as $lang){
				$form_data['columns']['tab_'.$lang]['name'] = 'tab_'.$lang;
				$form_data['columns']['tab_'.$lang]['title'] = _e('Имя вкладки в форме ').' '.$lang;
				$form_data['columns']['tab_'.$lang]['value'] = '';
				$form_data['columns']['tab_'.$lang]['type'] = 'safe_string';
				$form_data['columns']['tab_'.$lang]['required'] = 'off';
				$form_data['columns']['tab_'.$lang]['unique'] = 'off';
			}
		}


		return $form_data;
	}
}
