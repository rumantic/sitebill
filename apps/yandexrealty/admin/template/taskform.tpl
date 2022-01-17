<form method="post" action="{$estate_folder}/admin/" class="">
    <input type="hidden" name="action" value="yandexrealty">
    <input type="hidden" name="do" value="task">
    <input type="hidden" name="subdo" value="{$subdo}">
    <input type="hidden" name="yandexrealty_task_id" value="{intval($taskdata.yandexrealty_task_id)}">

    <fieldset>
        <div class="controls">
            <label class="checkbox">
                <input type="checkbox" name="taskdata[active]" value="1"{if $taskdata.active == 1} checked="checked"{/if}> Активен
            </label>
            <span class="help-block">Фид активен и может быть запущен</span>
        </div>
        <div class="controls">
            <label class="checkbox">
                <input type="checkbox" name="taskdata[ignoreactivity]" value="1"{if $taskdata.ignoreactivity == 1} checked="checked"{/if}> Игнорировать активность при выгрузке
            </label>
            <span class="help-block">Позволяет выгружать неактивные объекты</span>
        </div>
        
        <div class="control-group">
            <label class="control-label">Длина фида</label>
            <div class="controls">
              <input type="text" name="taskdata[limit]" value="{$taskdata.limit}">
              <span class="help-block">Количество записей получаемых в одном запросе. При неуказании - 1000</span>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">Алиас</label>
            <div class="controls">
              <input type="text" name="taskdata[alias]" value="{$taskdata.alias}">
              <span class="help-block">Ссылка</span>
            </div>
            
        </div>
        <!--     
        <div class="control-group">
            <label class="control-label">Тип фида</label>
            <div class="controls">
              <select name="xmltype">
                  <option value="0">Yandex</option>
                  <option value="1"{if $taskdata.xmltype == '1'} selected="selected"{/if}>EST.UA</option>
                  <option value="2"{if $taskdata.xmltype == '2'} selected="selected"{/if}>MEGET.UA</option>
              </select>
            </div>
        </div>
        -->      
        <div class="control-group">
            <label class="control-label">Сортировка</label>
            <div class="controls">
              <input type="text" name="taskdata[orderby]" value="{$taskdata.orderby}">
              <span class="help-block">Системное имя поля сортировки</span>
            </div>
            
        </div>
        <div class="control-group">
            <label class="control-label">Направление сортировки</label>
            <div class="controls">
              <select name="taskdata[orderdirect]">
                  <option value="asc">по возрастанию значений</option>
                  <option value="desc"{if $taskdata.orderdirect == 'desc'} selected="selected"{/if}>по спаданию значений</option>
              </select>
            </div>
        </div>
        
        
              
        <div class="control-group">
            <label class="control-label">Примечание или описание</label>
            <div class="controls">
                <textarea name="taskdata[remark]">{$taskdata.remark}</textarea>
            </div>
        </div>

    </fieldset>
    
    
    
    
      
            
            
            
    <fieldset>
        <legend>Параметры</legend>
        {include file=$mapper_yes_tpl fname='[filter]' setdata=$taskdata.filter}
    </fieldset>	
    
    <button type="submit" class="btn">Сохранить</button>
</form>

<div class="condcol condcol-ex">
	<input type="text" class="f0" name="" value="" />
	<select class="f1" name="">
		{foreach from=$condops item=condop key=condopkey}
			<option value="{$condopkey}">{$condop}</option>
		{/foreach}
	</select>
	<input class="f2" type="text" name="" value="" />
	<a href="#" class="rem btn btn-danger btn-mini"><i class="icon-white icon-remove"></i></a>
</div>

<div class="condline condline-ex">
	<div class="condcol">
		<input type="text" class="f0" name="" value="" />
		<select class="f1" name="">
			{foreach from=$condops item=condop key=condopkey}
				<option value="{$condopkey}">{$condop}</option>
			{/foreach}
		</select>
		<input class="f2" type="text" name="" value="" />
		<a href="#" class="rem btn btn-danger btn-mini"><i class="icon-white icon-remove"></i></a>
	</div>
	<a href="#" class="add_and" data-fname="" data-lc="0" data-cc="1">Добавить И условие</a>
</div>
            
            
{literal}
<style>
.condcol-ex {
	display: none;
}
.condline-ex {
	display: none;
}
.condline {
    border: 1px solid #eee;
    padding: 10px;
    margin-bottom: 10px;
}
.mapperitem {
    border-bottom: 1px dashed #7D7D7D;
    margin-bottom: 10px;
    padding: 10px 0 10px 0;
}
section {
	padding: 10px 20px;
    margin-bottom: 20px;
	background-color: rgb(241, 241, 241);
}
</style>
<script>
$(document).ready(function(e){
	$(document).on('click', '.add_and', function(e){
		var ex=$('.condcol-ex').clone();
		var name='field'+$(this).attr('data-fname');
		var line=$(this).attr('data-lc');
		var col=parseInt($(this).attr('data-cc'));
		$(this).attr('data-cc', col+1);
		name=name+'['+line+']['+col+']';
		ex.find('.f0').attr('name', name+'[0]').removeClass('f0');
		ex.find('.f1').attr('name', name+'[1]').removeClass('f1');
		ex.find('.f2').attr('name', name+'[2]').removeClass('f2');
		ex.removeClass('condcol-ex');
		ex.insertBefore($(this));
		e.preventDefault();
	});
	$(document).on('click', '.add_or', function(e){
		var ex=$('.condline-ex').clone();
		var name=$(this).attr('data-fname');
		ex.find('.add_and').attr('data-fname', name);
		name='field'+name;
		var line=parseInt($(this).attr('data-lc'), 10);
		var col=0;
		$(this).attr('data-lc', line+1);
		name=name+'['+line+']['+col+']';
		
		ex.find('.add_and').attr('data-lc', line);
		//ex.find('.add_and').attr('data-lc', line);
		ex.find('.f0').attr('name', name+'[0]').removeClass('f0');
		ex.find('.f1').attr('name', name+'[1]').removeClass('f1');
		ex.find('.f2').attr('name', name+'[2]').removeClass('f2');
		ex.removeClass('condline-ex');
		ex.insertBefore($(this));
		e.preventDefault();
	});
	$(document).on('click', '.condcol .rem', function(e){
        e.preventDefault();
        if(confirm('Удалить это условие? Восстановление будет невозможно.')){
            $(this).parents('.condcol').eq(0).remove();
        }
	});
});
</script>
{/literal}