<form id="save_fields_form" method="post" action="{$save_url}">
<div id="fields">
{foreach from=$model_fields item=model_field}
	<div class="field well well-sm">
		<input type="checkbox" name="field[]" value="{$model_field.name}"{if in_array($model_field.name, $used_fields)} checked="checked"{/if}>{$model_field.title} ({$model_field.name})
		<a href="" class="up btn btn-sm btn-info" title="{_e t="Выше (up)"}"><i class="fa fa-arrow-up"></i></a>
		<a href="" class="down btn btn-sm btn-info" title="{_e t="Ниже (down)"}"><i class="fa fa-arrow-down"></i></a>
		<a href="" class="first btn btn-sm btn-info" title="{_e t="Первая (First)"}"><i class="fa fa-fast-backward"></i></a>
		<a href="" class="last btn btn-sm btn-info" title="{_e t="Последняя (Last)"}"><i class="fa fa-fast-forward"></i></a>
	</div>
{/foreach}
</div>
<button id="save_fields" type="submit">{_e t="Сохранить"}</button>
</form>


{literal}
<script>
$('.up').click(function(e){
	e.preventDefault();
	var p=$(this).parents('div.field').eq(0);
	var prev=p.prevAll('div.field').eq(0);
	p.insertBefore(prev);
});
$('.down').click(function(e){
	e.preventDefault();
	var p=$(this).parents('div.field').eq(0);
	var next=p.nextAll('div.field').eq(0);
	p.insertAfter(next);
});
$('.first').click(function(e){
	e.preventDefault();
	var p=$(this).parents('div.field').eq(0);
	var first=$('#fields').find('div.field:first');
	p.insertBefore(first);
});
$('.last').click(function(e){
	e.preventDefault();
	var p=$(this).parents('div.field').eq(0);
	var last=$('#fields').find('div.field:last');
	p.insertAfter(last);
});
</script>
{/literal}
