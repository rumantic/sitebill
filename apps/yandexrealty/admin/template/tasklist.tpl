{literal}
<script>
	$(document).ready(function(){
        $('.state-switch').change(function(e){
			e.preventDefault();
            var _this = $(this);
            var status = 0;
            if(_this.is(':checked')){
                var status = 1;
            }
            
            var data={};
            data.yandexrealty_task_id=_this.data('id');           
            data._app='yandexrealty';
            data.action='set_activity';
            data.status=status;
            
            $.ajax({
                url: estate_folder+'/js/ajax.php',
                type: 'post',
                dataType: 'json',
                data: data,
                success: function(json){
                    if(json.status == 1){
                        return true;
                    }
                    _this.prop('checked', !_this.prop('checked'));
                    return false;
                }
            });
            
		});
	});
</script>
{/literal}
<a class="btn btn-success btn-mini" href="{$estate_folder}/admin/?action=yandexrealty&do=task&subdo=new">Создать новую задачу</a> 

<table class="table table-striped table-bordered table-hover">
    <thead>
        <tr>
            <th>ID</th>
            <th></th>
            <th>Информация</th>
            <th>Управление</th>
        </tr>
    </thead>
    {foreach from=$tasks item=task}
        <tr>
            <td>{$task.yandexrealty_task_id}</td>
            <td>
                <p><b>Адрес:</b> <span class="badge badge-info">{formaturl path=$task.alias abs=1 monolang=1}</span></p>
                Ссылка <a target="_blank" href="{formaturl path=$task.alias abs=1 monolang=1}">{formaturl path=$task.alias abs=1 monolang=1}</a>
                <p>
                    <b>Состояние:</b> 
                    <label class="inline">
                        <input id="id-pills-stacked"{if $task.active==1} checked="checked"{/if} type="checkbox" class="state-switch ace ace-switch ace-switch-3" data-id="{$task.yandexrealty_task_id}">
                        <span class="lbl"></span>
                    </label>
                </p>
                
            </td>
      
            <td>
                <div>
                    <blockquote style="width: 300px;">{$task.remark}</blockquote>
                    
                </div>
            </td>
            
            <td>
                <a class="btn btn-primary btn-mini" href="{$estate_folder}/admin/?action=yandexrealty&do=task&subdo=edit&yandexrealty_task_id={$task.yandexrealty_task_id}"><i class="icon-pencil"></i> Изменить</a> 
                <a class="btn btn-danger btn-mini" href="{$estate_folder}/admin/?action=yandexrealty&do=task&subdo=delete&yandexrealty_task_id={$task.yandexrealty_task_id}"><i class="icon-pencil"></i> Удалить</a> 

            </td>
        </tr>
        {/foreach}
    		
    		</table>
