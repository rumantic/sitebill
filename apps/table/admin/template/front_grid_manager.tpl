{literal}
<script>
$(document).ready(function(){
	
	$('ul#selected_columns').height($('ul#available_columns').height());
	
	$('ul.attached').sortable({
		connectWith: "ul.connect",
	});
	
	//$('ul.attached').sortable('refresh');
	
	//$(document).on()
	
	$('ul#available_columns').sortable({
		connectWith: "ul.connect",
		receive: function(event, ui){
			//console.log(ui.sender);
			$(ui.item).find('.setdata').remove();
			$(ui.item).text($(ui.item).attr('original_title'));
			/*if($(ui.sender).attr('id')=='selected_columns'){
				$(ui.item).find('.setdata').remove();
				$(ui.item).text($(ui.item).attr('original_title'));
			}else{
				
			}*/
			//return false;
		}
	});
	
	$('ul#selected_columns').sortable({
		connectWith: "ul.connect",
		receive: function(event, ui){
			//console.log(ui.sender);
			var div=$('#adel_tpl').clone().attr('id', '');
			div.find('input[name=title]').val($(ui.item).find('label').text());
			div.find('ul.attached').sortable({
				connectWith: "ul.connect",
			});
			$(ui.item).append(div);
			/*if($(ui.sender).attr('id')=='available_columns'){
				var div=$('#adel_tpl').clone().attr('id', '');
				div.find('input[name=title]').val($(ui.item).find('label').text());
				div.find('ul.attached').sortable({
					connectWith: "ul.connect",
				});
				$(ui.item).append(div);
				
			}*/
			//return false;
		}
	});
	
	/*$('ul#available_columns, ul#selected_columns').sortable({
		connectWith: "ul.connect",
		receive: function(event, ui){
			//console.log(ui.sender);
			if($(ui.sender).attr('id')=='available_columns'){
				var div=$('#adel_tpl').clone().attr('id', '');
				div.find('input[name=title]').val($(ui.item).find('label').text());
				$(ui.item).append(div);
				
			}else{
				$(ui.item).find('.setdata').remove();
			}
			
		}
	});*/
	
	$('#save_selection').click(function(){
		var fields=[];
		var topic_id=$('#search_form_topic').val();
		var grid_id=$('#grid_id').val();
		var grid_title=$('#grid_title').val();
		var selctedli=$('ul#selected_columns > li');
		
		
		
		
		if(selctedli.length>0){
			selctedli.each(function(){
				var o={};
				var name=$(this).attr('alt');
				o.title=$(this).find('[name=title]').val();
				o.name=$(this).attr('alt');
				o.original_title=$(this).attr('original_title');
				o.sortable=($(this).find('[name=sortable]').is(':checked') ? '1' : '0');
				o.linked=($(this).find('[name=linked]').is(':checked') ? '1' : '0');
				o.separator=$(this).find('[name=separator]').val();
				var attached=[];
				$(this).find('ul.attached li').each(function(){
					//console.log($(this).attr('alt'));
					//console.log($(this).text());
					var att={};
					att.name=$(this).attr('alt');
					att.title=$(this).text();
					attached.push(att);
				});
				o.attached=attached;
				fields.push(o);
			});
		}
		
		
		
		if(grid_title==''){
			return false;
		}
		$.ajax({
			type: 'post',
			url: estate_folder+'/apps/table/js/ajax.php?action=save_front_grid',
			data: {fields: JSON.stringify(fields), topic_id: topic_id, grid_title: grid_title, grid_id: grid_id},
			success: function(){
				$('#successSaving').modal();
				return false;
			}
		});
		/*if(topic_id!='' && topic_id!=0){
			
		}*/
		return false;
	});
});
</script>
<style>
	#search_form_topic {height: 200px;}
  #selected_columns, #available_columns { list-style-type: none; margin: 0; padding: 0 0 2.5em; float: left; margin-right: 10px; }
  #selected_columns li, #available_columns li { margin: 5px; padding: 5px; font-size: 1.2em; }
  #selected_columns li > label, #available_columns li > label {font-weight: bold;}
  #selected_columns li {border: 1px solid #eee;}
  ul#selected_columns {
  	/*min-height: 500px;*/
  	border: 1px solid #eee;
  	
  }
  #selected_columns, #available_columns {
  	list-style: none;
  	margin: 0 auto;
  	width: 100%;
  }
  #adel_tpl {display: none;}
  .setdata label {display: inline-block;}
  .setdata .attached {min-height: 100px; border: 1px solid #eee; list-style: none;}
  .setdata .columninfo {width: 350px; display: inline-block;}
  .setdata .attachedinfo {margin: 10px 0; display: inline-block; width: 250px;}
  </style>
{/literal}
<div id="adel_tpl" class="setdata">
	<div class="columninfo">
		<label>Сортировать</label> <input type="checkbox" name="sortable" /><br />
		<label>Ссылка</label> <input type="checkbox" name="linked" /><br />
		<label>Имя колонки</label> <input type="text" name="title" />
	</div>
	<div class="attachedinfo">
		<label>Разделитель</label> <input type="text" name="separator" value="" /><br />
		<ul class="attached connect" >
		</ul>
	</div>
</div>



<h3>Редактор сеток</h3>
<form class="form-horizontal">
	<input type="hidden" value="{$grid_id}" id="grid_id" />
	<div class="row-fluid">
		<div class="span12">
			<div class="control-group">
				<div class="controls">
					{$topic_select_box}
				</div>
			</div>
			
			<div class="control-group">
			    <label class="control-label">Имя сетки</label>
			    <div class="controls">
			      <input type="text" value="{$grid_title}" id="grid_title" />
			    </div>
			  </div>
			  
			  <div class="control-group">
					<div class="controls">
						<a class="btn" href="javascript:void(0);" id="save_selection">Сохранить</a>
					</div>
				</div>
		</div>
		
	</div>
	
	<div class="row-fluid">
		<div class="span8">
			<h4>Сетка</h4>
			<ul id="selected_columns" class="connect">
			{foreach from=$selected_columns item=sc}
			{*$sc|print_r*}
			<li alt="{$sc.name}" original_title="{$sc.original_title}">
				<label>{$sc.title}</label>
				<div class="setdata">
					<div class="columninfo">
						<label>Сортировать</label> <input type="checkbox" name="sortable"{if $sc.sortable==1} checked="checked"{/if} /><br />
						<label>Ссылка</label> <input type="checkbox" name="linked"{if $sc.linked==1} checked="checked"{/if} /><br />
						<label>Имя колонки</label> <input type="text" name="title"{if $sc.title!=''} value="{$sc.title}"{/if} />
					</div>
					<div class="attachedinfo">
						<label>Разделитель</label> <input type="text" name="separator" value="{$sc.separator}" /><br />
						<ul class="attached connect" >
						{if !empty($sc.attached)}
						{foreach from=$sc.attached item=attached}
						<li alt="{$attached.name}" original_title="{$attached.title}">{$attached.title}</li>
						{/foreach}
						{/if}
						</ul>
					</div>
				</div>
			</li>
			{/foreach}
			</ul>
		</div>
		<div class="span4 ">
			
			<h4>Поля</h4>
			<ul id="available_columns" class="connect connect2">
			{foreach from=$model_fields key=fkey item=field}
			<li alt="{$fkey}" original_title="{$field.title}"><label>{$field.title}</label></li>
			{/foreach}
			</ul>
		</div>
	</div>
</form>

<div class="modal hide" id="successSaving" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
    <h3 id="myModalLabel">Сохранение сетки</h3>
  </div>
  <div class="modal-body">
    <p>Форма была сохранена</p>
    <p>Хотите вернуться к списку сеток или продолжить редактирование текущей сетки?</p>
    <p><a href="javascript:void(0);" onClick="window.location.href='/admin/index.php?action=table&section=front_gridmanager';" class="btn ptn-primary">Вернуться к списку сеток</a></p>
    <p><a href="javascript:void(0);" onClick="$('#successSaving').modal('hide');" class="btn ptn-primary">Продолжить редактирование текущей сетки</a></p>
  </div>
</div>
