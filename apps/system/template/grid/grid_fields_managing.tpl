<form id="save_fields_form" method="post" action="{$save_url}">
	<div id="fields">
		<div class="dd dd-draghandle">
			<ol class="dd-list" id="grid_list2">
				{foreach from=$model_fields item=model_field}
					<li class="dd-item dd2-item">
						<div class="dd-handle dd2-handle">
							<i class="normal-icon icon-move pink bigger-130"></i>
						</div>
						<div class="dd2-content">
							<b>{$model_field.title}</b> ({$model_field.name})
							<div class="pull-right">
								<label>
									<input name="field[]" value="{$model_field.name}" type="checkbox" class="ace control-activity"{if in_array($model_field.name, $used_fields)} checked="checked"{/if}>
									<span class="lbl"> </span>
								</label>
							</div>
						</div>
					</li>
				{/foreach}

			</ol>
		</div>
	</div>
	<button class="btn btn-primary" id="save_fields" type="submit">{_e t="Сохранить"}</button>
</form>

{literal}
<script>
	$(document).ready(function () {
		$('#grid_list2').sortable({
			handle: ".dd-handle"
		}).disableSelection();
	});
</script>
{/literal}
