{$top_menu}

<div class="row-fluid" id="form_data">

<form class="form-horizontal">
	<input type="hidden" value="{$form_id}" id="form_id" />
  <div class="control-group">
    <label class="control-label">Раздел</label>
    <div class="controls">
      {$topic_select_box}
    </div>
  </div>
  <div class="control-group">
    <label class="control-label">Имя формы</label>
    <div class="controls">
      <input type="text" value="{$form_title}" id="form_title" />
    </div>
  </div>

  <div class="control-group">
    <label class="control-label">Имя формы (en)</label>
    <div class="controls">
      <input type="text" value="{$form_title_en}" id="form_title_en" />
    </div>
  </div>
  
  <div class="control-group">
    <div class="controls">
      <input type="submit" class="btn btn-primary" id="save_selection" name="submit" value="Сохранить">
    </div>
  </div>
</form>


</div>
<div id="search_form_composer" class="row-fluid">
<div id="selected_columns" class="span8 connect">
<h4>Выбранные поля</h4>
<ul>
{foreach from=$selected_columns item=sc}
<li alt="{$sc.name}"><label>{$sc.title}</label>{$sc.html}</li>
{/foreach}
</ul>
</div>
<div id="available_columns" class="span4 connect">
<h4>Доступные поля</h4>
{*$available_columns*}
<ul>
{foreach from=$available_columns item=ac}
<li alt="{$ac.name}"><label>{$ac.title}</label>{$ac.html}</li>
{/foreach}
</ul>
</div>
</div>





{literal}
<script>
$(document).ready(function(){
	
	/*$('#search_form_topic').change(function(){
		window.location.href='/admin/?action=table&section=search_forms&search_form_topic='+$(this).val();
	});*/
	
	
	$('#selected_columns ul').height($('#available_columns ul').height());
	
	
	$('#available_columns ul, #selected_columns ul').sortable({
		connectWith: ".connect ul"
		
	});
	
	$('#save_selection').click(function(){
        console.log('save click');
	
		var fields=[];
		var topic_id=$('#search_form_topic').val();
		var form_id=$('#form_id').val();
		var form_title=$('#form_title').val();
        var form_title_en=$('#form_title_en').val();
		var selctedli=$('#selected_columns ul li');
		if(selctedli.length>0){
			selctedli.each(function(){
				fields.push($(this).attr('alt'))
			});
		}
		if(form_title==''){
			return false;
		}
		$.ajax({
			type: 'post',
			url: estate_folder+'/apps/table/js/ajax.php?action=save_search_form',
			data: {fields: fields, topic_id: topic_id, form_title: form_title, form_id: form_id, form_title_en: form_title_en},
			success: function(){
				//alert('Saved');
				$('#successSaving').modal();
			}
		});
		/*if(topic_id!='' && topic_id!=0){
			
		}*/
		return false;
	});
});
</script>
<style>
  #selected_columns, #available_columns { list-style-type: none; margin: 0; padding: 0 0 2.5em; float: left; margin-right: 10px; }
  #selected_columns li, #available_columns li { margin: 0 5px 5px 5px; padding: 5px; font-size: 1.2em; }
  #selected_columns ul {
  	/*min-height: 500px;*/
  	border: 1px solid #eee;
  	
  }
  #selected_columns ul, #available_columns ul {
  	list-style: none;
  	margin: 0;
  }
  </style>
{/literal}

<div class="modal hide" id="successSaving" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
    <h3 id="myModalLabel">Сохранение формы</h3>
  </div>
  <div class="modal-body">
    <p>Форма была сохранена</p>
    <p>Хотите вернуться к списку форм или продолжить редактирование текущей формы?</p>
    <p><a href="javascript:void(0);" onClick="window.location.href='/admin/index.php?action=table&section=search_forms';" class="btn ptn-primary">Вернуться к списку форм</a></p>
    <p><a href="javascript:void(0);" onClick="$('#successSaving').modal('hide');" class="btn ptn-primary">Продолжить редактирование текущей формы</a></p>
  </div>
</div>