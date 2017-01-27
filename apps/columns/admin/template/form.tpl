<script src="{$estate_folder}/apps/columns/js/interface.js"></script>
<script type="text/javascript">
var langs={$langsjs};
{literal}
$(document).ready(function(){
	$('.tooltipe_block').popover({trigger: 'hover'});
});
{/literal}
</script>

{if isset($form_elements.scripts) && $form_elements.scripts|count>0}
	{foreach from=$form_elements.scripts item=form_element_script}
		{$form_element_script}
	{/foreach}
{/if}

{assign var=hash value=$form_elements.hash}

{if $form_error ne ''}
	<p class="error">{$form_error}</p>
{/if}
	
<form method="post" id="column_form" class="form-horizontal" action="{$estate_folder}/admin/" enctype="multipart/form-data">
	
	<legend>Колонка</legend>
	

	<input type="hidden" name="action" value="columns" />
	
	{assign var=current_el value=$form_elements.hash.table_id}
	<div class="form_element control-group" alt="{$current_el.name}">
		<label class="control-label">{$current_el.title}{if $current_el.required eq 1}<span style="color: red;">*</span>{/if} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content="Имя модели содержащей данное поле"> <i class="icon-question-sign icon-white"></i></a></label>
		<div class="form_element_html controls">{$current_el.html}
		</div>
	</div>
	
	{assign var=current_el value=$form_elements.hash.type}
	<div class="form_element control-group" alt="{$current_el.name}">
		<label class="control-label">{$current_el.title}{if $current_el.required eq 1}<span style="color: red;">*</span>{/if} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content="Тип поля модели, который определяет его функционал, особенности, принцип отображения и видимость на формах и при автовыводах"> <i class="icon-question-sign icon-white"></i></a></label>
		<div class="form_element_html controls">{$current_el.html}
		</div>
	</div>
	
	{assign var=current_el value=$form_elements.hash.active}
	<div class="form_element control-group" alt="{$current_el.name}">
		<label class="control-label">{$current_el.title}{if $current_el.required eq 1}<span style="color: red;">*</span>{/if} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content="Активность элемента. Деактивированное состояние исключает элемент из модели, но не удаляет его физически, что позволяет, при потребности, быстро вернуть его в состав модели."> <i class="icon-question-sign icon-white"></i></a></label>
		<div class="form_element_html controls">{$current_el.html}
		</div>
	</div>
	
	{assign var=current_el value=$form_elements.hash.name}
	<div class="form_element control-group" alt="{$current_el.name}">
		<label class="control-label">{$current_el.title}{if $current_el.required eq 1}<span style="color: red;">*</span>{/if} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content="Системное имя поля. Может состоять из цифр, латинских букв и подчеркивания. Не должно содержать пробелы или начинаться с числа или подчеркивания. Должно быть длиннее 3-х символов. Фактически это имя, в большинстве случаев, является именем соответствующей колонки в таблице БД."> <i class="icon-question-sign icon-white"></i></a></label>
		<div class="form_element_html controls">{$current_el.html}
		</div>
	</div>
		
	{assign var=current_el value=$form_elements.hash.title}
	<div class="form_element control-group" alt="{$current_el.name}">
		<label class="control-label">{$current_el.title}{if $current_el.required eq 1}<span style="color: red;">*</span>{/if} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content="Публичное имя элемента модели. Будет отображаться на сайте и в формах."> <i class="icon-question-sign icon-white"></i></a></label>
		<div class="form_element_html controls">{$current_el.html}
		</div>
	</div>
	
	{foreach $langs item=lang}
		{assign var=langt value='title_'|cat:$lang}
		{if isset($hash[$langt])}
		<div class="form_element control-group" alt="{$hash[$langt].name}">
			<label class="control-label">{$hash[$langt].title}{if $hash[$langt].required eq 1}<span style="color: red;">*</span>{/if}</label>
			<div class="form_element_html controls">{$hash[$langt].html}</div>
		</div>
		{/if}
	{/foreach}
	
	{assign var=current_el value=$form_elements.hash.group_id}
	<div class="form_element control-group" alt="{$current_el.name}">
		<label class="control-label">{$current_el.title}{if $current_el.required eq 1}<span style="color: red;">*</span>{/if} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content="Ограничение видимости элемента в разрезе группы пользователя. Если вам необходимо ограничить видимость элемента конкретной группой или группами - выберите их из списка. Неавторизированные пользователи (те, которые не имеют группы) смогут видеть только элементы для которых не установлено право видимости ни на одну группу. Группа Незарегистрированные не является аналогом пользователя-гостя и установка прав видимости для нее не влияет на видимость элементов для анонимных пользователей."> <i class="icon-question-sign icon-white"></i></a></label>
		<div class="form_element_html controls">{$current_el.html}
		</div>
	</div>
	
	{if 1==0}
	{assign var=current_el value=$form_elements.hash.optype}
	<div class="form_element control-group" alt="{$current_el.name}">
		<label class="control-label">{$current_el.title}{if $current_el.required eq 1}<span style="color: red;">*</span>{/if} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content=""> <i class="icon-question-sign icon-white"></i></a></label>
		<div class="form_element_html controls">{$current_el.html}
		</div>
	</div>
	{/if}	

	{assign var=current_el value=$form_elements.hash.active_in_topic}
	<div class="form_element control-group" alt="{$current_el.name}">
		<label class="control-label">{$current_el.title}{if $current_el.required eq 1}<span style="color: red;">*</span>{/if} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content="Определяет видимость элемента в зависимости от выбраного пункта Структуры"> <i class="icon-question-sign icon-white"></i></a></label>
		<div class="form_element_html controls">{$current_el.html}
		</div>
	</div>
	
	{assign var=current_el value=$form_elements.hash.hint}
	<div class="form_element control-group" alt="{$current_el.name}">
		<label class="control-label">{$current_el.title}{if $current_el.required eq 1}<span style="color: red;">*</span>{/if} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content="Подсказка к элементву в виде простого текста"> <i class="icon-question-sign icon-white"></i></a></label>
		<div class="form_element_html controls">{$current_el.html}
		</div>
	</div>
	
	{foreach $langs item=lang}
		{assign var=langt value='hint_'|cat:$lang}
		{if isset($hash[$langt])}
		<div class="form_element control-group" alt="{$hash[$langt].name}">
			<label class="control-label">{$hash[$langt].title}{if $hash[$langt].required eq 1}<span style="color: red;">*</span>{/if}</label>
			<div class="form_element_html controls">{$hash[$langt].html}</div>
		</div>
		{/if}
	{/foreach}
	
	{assign var=current_el value=$form_elements.hash.value}
	<div class="form_element control-group" alt="{$current_el.name}">
		<label class="control-label">{$current_el.title}{if $current_el.required eq 1}<span style="color: red;">*</span>{/if} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content="Значение по-умолчанию, которое будет устанавливаться для элемента в случае его незаполнения пользователем"> <i class="icon-question-sign icon-white"></i></a></label>
		<div class="form_element_html controls">{$current_el.html}
		</div>
	</div>
	
	{foreach $langs item=lang}
		{assign var=langt value='value_'|cat:$lang}
		{if isset($hash[$langt])}
		<div class="form_element control-group" alt="{$hash[$langt].name}">
			<label class="control-label">{$hash[$langt].title}{if $hash[$langt].required eq 1}<span style="color: red;">*</span>{/if}</label>
			<div class="form_element_html controls">{$hash[$langt].html}</div>
		</div>
		{/if}
	{/foreach}
	
	{assign var=current_el value=$form_elements.hash.primary_key_table}
	<div class="form_element control-group" alt="{$current_el.name}">
		<label class="control-label">{$current_el.title}{if $current_el.required eq 1}<span style="color: red;">*</span>{/if} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content="Внешняя таблица-хранилище значений-вариантов для поля select_by_query"> <i class="icon-question-sign icon-white"></i></a></label>
		<div class="form_element_html controls">{$current_el.html}
		</div>
	</div>
	
	{assign var=current_el value=$form_elements.hash.primary_key_name}
	<div class="form_element control-group" alt="{$current_el.name}">
		<label class="control-label">{$current_el.title}{if $current_el.required eq 1}<span style="color: red;">*</span>{/if} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content="Название поля внешней таблицы-хранилища которое служит ключем для связи с текущим полем (для поля select_by_query)"> <i class="icon-question-sign icon-white"></i></a></label>
		<div class="form_element_html controls">{$current_el.html}
		</div>
	</div>
	{assign var=current_el value=$form_elements.hash.value_name}
	<div class="form_element control-group" alt="{$current_el.name}">
		<label class="control-label">{$current_el.title}{if $current_el.required eq 1}<span style="color: red;">*</span>{/if} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content="Название поля внешней таблицы-хранилища которое служит хранилищем значения для связи с текущим полем (для поля select_by_query)"> <i class="icon-question-sign icon-white"></i></a></label>
		<div class="form_element_html controls">{$current_el.html}
		</div>
	</div>
	
	{assign var=current_el value=$form_elements.hash.query}
	<div class="form_element control-group" alt="{$current_el.name}">
		<label class="control-label">{$current_el.title}{if $current_el.required eq 1}<span style="color: red;">*</span>{/if} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content="Формальный запрос к внешней таблице на выборку списка вариантов значений поля"> <i class="icon-question-sign icon-white"></i></a></label>
		<div class="form_element_html controls">{$current_el.html}
		</div>
	</div>
	
	{assign var=current_el value=$form_elements.hash.title_default}
	<div class="form_element control-group" alt="{$current_el.name}">
		<label class="control-label">{$current_el.title}{if $current_el.required eq 1}<span style="color: red;">*</span>{/if} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content="Текстовое название нулевого элемента списка выбора вариантов. Будет отображаться как опция отсутствия выбора."> <i class="icon-question-sign icon-white"></i></a></label>
		<div class="form_element_html controls">{$current_el.html}
		</div>
	</div>
	
	{foreach $langs item=lang}
		{assign var=langt value='title_default_'|cat:$lang}
		{if isset($hash[$langt])}
		<div class="form_element control-group" alt="{$hash[$langt].name}">
			<label class="control-label">{$hash[$langt].title}{if $hash[$langt].required eq 1}<span style="color: red;">*</span>{/if}</label>
			<div class="form_element_html controls">{$hash[$langt].html}</div>
		</div>
		{/if}
	{/foreach}
	
	{assign var=current_el value=$form_elements.hash.value_default}
	<div class="form_element control-group" alt="{$current_el.name}">
		<label class="control-label">{$current_el.title}{if $current_el.required eq 1}<span style="color: red;">*</span>{/if} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content="Значение нулевого элемента списка выбора вариантов. Должно быть 0 для обозначения отсутствия выбора."> <i class="icon-question-sign icon-white"></i></a></label>
		<div class="form_element_html controls">{$current_el.html}
		</div>
	</div>
	
	{assign var=current_el value=$form_elements.hash.value_table}
	<div class="form_element control-group" alt="{$current_el.name}">
		<label class="control-label">{$current_el.title}{if $current_el.required eq 1}<span style="color: red;">*</span>{/if}</label>
		<div class="form_element_html controls">{$current_el.html}</div>
	</div>
	
	{assign var=current_el value=$form_elements.hash.value_primary_key}
	<div class="form_element control-group" alt="{$current_el.name}">
		<label class="control-label">{$current_el.title}{if $current_el.required eq 1}<span style="color: red;">*</span>{/if}</label>
		<div class="form_element_html controls">{$current_el.html}</div>
	</div>
	
	{assign var=current_el value=$form_elements.hash.value_field}
	<div class="form_element control-group" alt="{$current_el.name}">
		<label class="control-label">{$current_el.title}{if $current_el.required eq 1}<span style="color: red;">*</span>{/if}</label>
		<div class="form_element_html controls">{$current_el.html}</div>
	</div>
	
	{assign var=current_el value=$form_elements.hash.assign_to}
	<div class="form_element control-group" alt="{$current_el.name}">
		<label class="control-label">{$current_el.title}{if $current_el.required eq 1}<span style="color: red;">*</span>{/if}</label>
		<div class="form_element_html controls">{$current_el.html}</div>
	</div>
	
	{assign var=current_el value=$form_elements.hash.dbtype}
	<div class="form_element control-group" alt="{$current_el.name}">
		<label class="control-label">{$current_el.title}{if $current_el.required eq 1}<span style="color: red;">*</span>{/if} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content="Указывает обязательность хранения значения поля в таблице БД. Для вспомогательных полей не требующих сохранения или обрабатывающихся отдельно (напр. captcha) должно быть разотмечено."> <i class="icon-question-sign icon-white"></i></a></label>
		<div class="form_element_html controls">{$current_el.html}
		</div>
	</div>
	
	{assign var=current_el value=$form_elements.hash.select_data}
	<div class="form_element control-group" alt="{$current_el.name}">
		<label class="control-label">{$current_el.title}{if $current_el.required eq 1}<span style="color: red;">*</span>{/if} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content="Набор опций выбора в формате пар {literal}{key~~value}{/literal}. key - оптимально числовое значение. Для нулевого значения key равен нулю, либо пара не указывается."> <i class="icon-question-sign icon-white"></i></a></label>
		<div class="form_element_html controls">{$current_el.html}
		</div>
	</div>
	
	{foreach $langs item=lang}
		{assign var=langt value='select_data_'|cat:$lang}
		{if isset($hash[$langt])}
		<div class="form_element control-group" alt="{$hash[$langt].name}">
			<label class="control-label">{$hash[$langt].title}{if $hash[$langt].required eq 1}<span style="color: red;">*</span>{/if}</label>
			<div class="form_element_html controls">{$hash[$langt].html}</div>
		</div>
		{/if}
	{/foreach}
	
	{assign var=current_el value=$form_elements.hash.table_name}
	<div class="form_element control-group" alt="{$current_el.name}">
		<label class="control-label">{$current_el.title}{if $current_el.required eq 1}<span style="color: red;">*</span>{/if} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content="Cпецифично для полей типа uploadify."> <i class="icon-question-sign icon-white"></i></a></label>
		<div class="form_element_html controls">{$current_el.html}
		</div>
	</div>
	
	{assign var=current_el value=$form_elements.hash.primary_key}
	<div class="form_element control-group" alt="{$current_el.name}">
		<label class="control-label">{$current_el.title}{if $current_el.required eq 1}<span style="color: red;">*</span>{/if} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content="Cпецифично для полей типа uploadify."> <i class="icon-question-sign icon-white"></i></a></label>
		<div class="form_element_html controls">{$current_el.html}
		</div>
	</div>
	
	{assign var=current_el value=$form_elements.hash.primary_key_value}
	<div class="form_element control-group" alt="{$current_el.name}">
		<label class="control-label">{$current_el.title}{if $current_el.required eq 1}<span style="color: red;">*</span>{/if} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content="Cпецифично для полей типа uploadify."> <i class="icon-question-sign icon-white"></i></a></label>
		<div class="form_element_html controls">{$current_el.html}
		</div>
	</div>
	
	{assign var=current_el value=$form_elements.hash.action}
	<div class="form_element control-group" alt="{$current_el.name}">
		<label class="control-label">{$current_el.title}{if $current_el.required eq 1}<span style="color: red;">*</span>{/if}</label>
		<div class="form_element_html controls">{$current_el.html}</div>
	</div>
	
	{assign var=current_el value=$form_elements.hash.entity}
	<div class="form_element control-group" alt="{$current_el.name}">
		<label class="control-label">{$current_el.title}{if $current_el.required eq 1}<span style="color: red;">*</span>{/if}</label>
		<div class="form_element_html controls">{$current_el.html}</div>
	</div>
	
	{assign var=current_el value=$form_elements.hash.combo}
	<div class="form_element control-group" alt="{$current_el.name}">
		<label class="control-label">{$current_el.title}{if $current_el.required eq 1}<span style="color: red;">*</span>{/if} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content="Использование combobox-виджета для элементов выпадающего списка. В связи со сложность адаптации к новым шаблонам поддержка прекращена. Не поддерживается."> <i class="icon-question-sign icon-white"></i></a></label>
		<div class="form_element_html controls">{$current_el.html}
		</div>
	</div>
	
	{assign var=current_el value=$form_elements.hash.required}
	<div class="form_element control-group" alt="{$current_el.name}">
		<label class="control-label">{$current_el.title}{if $current_el.required eq 1}<span style="color: red;">*</span>{/if} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content="Признак обязательности значения. Неуказанным значением считается: для текстовых полей - пустое значение, для полей выбора - нулевой выбор. Не имеет значения для чекбоков и контролируется дополнительными функциями для полей хранения изображений."> <i class="icon-question-sign icon-white"></i></a></label>
		<div class="form_element_html controls">{$current_el.html}
		</div>
	</div>
	
	{assign var=current_el value=$form_elements.hash.unique}
	<div class="form_element control-group" alt="{$current_el.name}">
		<label class="control-label">{$current_el.title}{if $current_el.required eq 1}<span style="color: red;">*</span>{/if} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content="Признак контроля уникальности значения. Не используется."> <i class="icon-question-sign icon-white"></i></a></label>
		<div class="form_element_html controls">{$current_el.html}
		</div>
	</div>
	
	
	
	{assign var=current_el value=$form_elements.hash.parameters}
	<div class="form_element control-group" alt="{$current_el.name}">
		<label class="control-label">{$current_el.title}{if $current_el.required eq 1}<span style="color: red;">*</span>{/if} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content="Набор параметров точной настройки элемента."> <i class="icon-question-sign icon-white"></i></a></label>
		<div class="form_element_html controls">{$current_el.html}
			{if 1==0}
			<div id="params_setter">
				<ul>
					<li><a href="" data-task="uploads_full">Полный набор для uploads</a></li>
					<li><a href="" data-task="link_dep">Linked\Depended</a></li>
					<li><a href="" data-task="autocomplete_set">Autocomplete</a></li>
					<li><a href="" data-task="allow_html">Allow HTML</a></li>
					
					<li><a href="" data-task="geodata_map_size">Размеры карты</a></li>
					<li><a href="" data-task="ta_ined">Textarea Lined(+fields)</a></li>
					<li><a href="" data-task="ta_cr">Textarea Cols+Rows</a></li>
					<li><a href="" data-task="dt_format">DT date format</a></li>
					<li><a href="" data-task="styles">Styles</a></li>
					<li><a href="" data-task="mask">Маска ввода для сотовых</a></li>
					<li><a href="" data-task="onchange">onchange для select_by_query</a></li>
					<li><a href="" data-task="rules">Rules</a></li>
				</ul>
				
			</div>
			{/if}
		</div>
		
	</div>
	{literal}
	<script>
	$(document).ready(function(){
   		//$(document).on("click", ".paramsrow a", function(){$(this).parents(".paramsrow").eq(0).remove();return false;});
   		$("#params_setter a").click(function(e){
   			e.preventDefault();
   			var $this=$(this);
   			var p=$this.parents('.form_element_html').eq(0).find('#paramsblock');
   			var task=$this.data('task');
   			var c=p.find(".paramsrow:last");
   			var pr=c.clone();
   			var new_elements=[];
   			$hasAct=false;
   			
   			var exp=[];
			p.find('.paramsrow').each(function(){
				var v=$(this).find('input:first').val();
				if(v!=''){
					exp.push(v);
				}
			});
   			//console.log(task);
   			if(task=='uploads_full'){
   				if(-1===$.inArray('norm_width', exp)){
   					pr.find('input').eq(0).val('norm_width');
   	   				new_elements.push(pr);
   	   				pr=pr.clone();
   				}
   				if(-1===$.inArray('norm_height', exp)){
   					pr.find('input').eq(0).val('norm_height');
   	   				new_elements.push(pr);
   	   				pr=pr.clone();
   				}
   				if(-1===$.inArray('prev_width', exp)){
   					pr.find('input').eq(0).val('prev_width');
   	   				new_elements.push(pr);
   	   				pr=pr.clone();
   				}
   				if(-1===$.inArray('prev_height', exp)){
   					pr.find('input').eq(0).val('prev_height');
   	   				new_elements.push(pr);
   	   				pr=pr.clone();
   				}
   				if(-1===$.inArray('preview_smart_resizing', exp)){
   					pr.find('input').eq(0).val('preview_smart_resizing');
   					pr.find('input').eq(1).val(1);
   	   				new_elements.push(pr);
   	   			}
   				
   				hasAct=true;
   			}else if(task=='dt_format'){
   				if(-1===$.inArray('format', exp)){
   					pr.find('input').eq(0).val('format');
   					new_elements.push(pr);
   					pr=pr.clone();
   	   			}
   	   			if(-1===$.inArray('inFormFormat', exp)){
					pr.find('input').eq(0).val('inFormFormat');
					new_elements.push(pr);
					pr=pr.clone();
	   			}
	   			if(-1===$.inArray('noSeconds', exp)){
					pr.find('input').eq(0).val('noSeconds');
					new_elements.push(pr);
	   			}
   				hasAct=true;
   			}else if(task=='onchange'){
   				if(-1===$.inArray('onchange', exp)){
   					pr.find('input').eq(0).val('onchange');
   	   				new_elements.push(pr);
   	   			}
   				hasAct=true;
   			}else if(task=='styles'){
   				if(-1===$.inArray('styles', exp)){
   					pr.find('input').eq(0).val('styles');
   	   				new_elements.push(pr);
   	   			}
   				hasAct=true;
   			}else if(task=='mask'){
   				if(-1===$.inArray('mask', exp)){
   					pr.find('input').eq(0).val('mask');
   	   				new_elements.push(pr);
   	   			}
   				hasAct=true;
   			}else if(task=='allow_html'){
   				if(-1===$.inArray('allow_htmltags', exp)){
   					pr.find('input').eq(0).val('allow_htmltags');
   					pr.find('input').eq(1).val(1);
   	   				new_elements.push(pr);
   	   			}
   				hasAct=true;
   			}else if(task=='link_dep'){
   				if(-1===$.inArray('linked', exp)){
   					pr.find('input').eq(0).val('linked');
   	   				new_elements.push(pr);
   	   				pr=pr.clone();
   	   			}
   				if(-1===$.inArray('depended', exp)){
   					pr.find('input').eq(0).val('depended');
   	   				new_elements.push(pr);
   	   			}
   				
   				hasAct=true;
   			}else if(task=='autocomplete_set'){
   				if(-1===$.inArray('autocomplete', exp)){
   					pr.find('input').eq(0).val('autocomplete');
   	   				pr.find('input').eq(1).val(1);
   	   				new_elements.push(pr);
   	   				pr=pr.clone();
   	   			}
   				if(-1===$.inArray('autocomplete_notappend', exp)){
   					pr.find('input').eq(0).val('autocomplete_notappend');
	   				pr.find('input').eq(1).val(1);
	   				new_elements.push(pr);
   	   			}
   				hasAct=true;
   			}else if(task=='geodata_map_size'){
   				
   				
   				if(-1===$.inArray('map_width', exp)){
   					pr.find('input').eq(0).val('map_width');
   	   				pr.find('input').eq(1).val('300');
   	   				new_elements.push(pr);
   	   				pr=pr.clone();
   				}
   				if(-1===$.inArray('map_height', exp)){
   					pr.find('input').eq(0).val('map_height');
   	   				pr.find('input').eq(1).val('300');
   	   				new_elements.push(pr);
   				}
   				

   				
   				hasAct=true;
   			}
   			
   			if(hasAct && new_elements.length>0){
   				for(var i=0, l=new_elements.length; i<l; i++){
   					new_elements[i].insertBefore(c);
   				}
   			}
   			
   			return false;
   		});
   	});
	</script>
    {/literal}
    	
	
	{assign var=current_el value=$form_elements.hash.tab}
	<div class="form_element control-group" alt="{$current_el.name}">
		<label class="control-label">{$current_el.title}{if $current_el.required eq 1}<span style="color: red;">*</span>{/if} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content="Закладка на форме в которой будет размещен элемент."> <i class="icon-question-sign icon-white"></i></a></label>
		<div class="form_element_html controls">{$current_el.html}
		</div>
	</div>
	
	{foreach $langs item=lang}
		{assign var=langt value='tab_'|cat:$lang}
		{if isset($hash[$langt])}
		<div class="form_element control-group" alt="{$hash[$langt].name}">
			<label class="control-label">{$hash[$langt].title}{if $hash[$langt].required eq 1}<span style="color: red;">*</span>{/if}</label>
			<div class="form_element_html controls">{$hash[$langt].html}</div>
		</div>
		{/if}
	{/foreach}
	
	<div class="form_element_control">
	{$form_elements.controls.apply.html} {$form_elements.controls.back.html} {$form_elements.controls.submit.html}
	</div>
	{foreach from=$form_elements.private key=tab item=p_element}
		{$p_element.html}
	{/foreach}
	
</form>