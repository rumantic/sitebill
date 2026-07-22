{literal}
<script type="text/javascript">
$(document).ready(function () {
    $(document).on('click', '.mailbox_complaint_send_complaint', function(e){
        e.preventDefault();
        if ( !confirm('Действительно занести в черный список? Отменить нельзя') ) {
            return false;
        }
        var _this=$(this);
        var id=_this.data('id');
        var complaint_id=_this.attr('data-complaint-id');

        const body = {
            _app: 'mailbox',
            action: 'send_complaint',
            id: id,
            ignore_captcha: 1,
            complaint_id: complaint_id,
        };

        $.ajax({
            url: estate_folder+'/js/ajax.php',
            data: body,
            type: 'post',
            dataType: 'text',
            success: function(text){
                const result = $.parseJSON(text);
                if(result.status === 1){
                    _this.removeClass('bg-gray-400').addClass('bg-pink-500');
                }
            }
        });
    });

    $(document).on('click', '.active_toggle', function(e){
        e.preventDefault();
        var _this=$(this);
        var id=_this.data('id');
        var active=_this.attr('data-active');
        if (active == 1) {
            active = 0;
        } else {
            active = 1;
        }

        const body = {
            action: 'model',
            do: 'graphql_update',
            model_name: 'data',
            only_ql: true,
            key_value: id,
            ql_items: {active: active},
        };

        $.ajax({
            url: estate_folder+'/apps/api/rest.php',
            data: body,
            type: 'post',
            dataType: 'text',
            success: function(text){
                const result = $.parseJSON(text);
                if(result.state == 'success'){
                    if ( body.ql_items.active == 0 ) {
                        _this.attr('data-active', 0);
                        _this.attr('title', 'включить');
                        _this.removeClass('bg-green-500').addClass('bg-red-500');
                        _this.parents('tr').eq(0).addClass('opacity-50');
                    } else {
                        _this.attr('title', 'выключить');
                        _this.attr('data-active', 1);
                        _this.removeClass('bg-red-500').addClass('bg-green-500');
                        _this.parents('tr').eq(0).removeClass('opacity-50');
                    }
                }
            }
        });
    });

    $(document).on('click', '.change_data_id', function(e){
        e.preventDefault();
        var _this=$(this);
        var id=_this.data('id');

        const body = {
            action: 'model',
            do: 'change_data_id',
            model_name: 'data',
            id: id,
        };

        $.ajax({
            url: estate_folder+'/apps/api/rest.php',
            data: body,
            type: 'post',
            dataType: 'text',
            success: function(text){
                const result = $.parseJSON(text);
                console.log(result);
                if(result.state == 'success'){
                    $.gritter.add({
                        title: 'Операция успешна',
                        text: 'ID Объекта обновлен: ' + result.message,
                        sticky: false,
                        time: '5000',
                        class_name: 'gritter-success'
                    });
                    $('#grid_placer_' + id).html(result.message);
                } else {
                    $.gritter.add({
                        title: 'Ошибка',
                        text: 'ID Объекта не обновлен: ' + result.message,
                        sticky: false,
                        time: '5000',
                        class_name: 'gritter-error'
                    });
                }
            }
        });
    });

    $(document).on('click', '.active_toggle_any', function(e){
        e.preventDefault();
        var _this=$(this);
        var id=_this.data('id');
        var active=_this.attr('data-active');
        var key_name=_this.attr('data-key-name');
        if (active == 1) {
            active = 0;
        } else {
            active = 1;
        }

        const body = {
            action: 'model',
            do: 'graphql_update',
            model_name: 'data',
            only_ql: true,
            key_value: id,
            ql_items: {[key_name]:active},
        };

        $.ajax({
            url: estate_folder+'/apps/api/rest.php',
            data: body,
            type: 'post',
            dataType: 'text',
            success: function(text){
                const result = $.parseJSON(text);
                if(result.state == 'success'){
                    if ( body.ql_items[key_name] == 0 ) {
                        _this.attr('data-active', 0);
                        _this.attr('title', 'включить');
                        _this.removeClass('bg-green-500').addClass('bg-red-500');
                    } else {
                        _this.attr('title', 'выключить');
                        _this.attr('data-active', 1);
                        _this.removeClass('bg-red-500').addClass('bg-green-500');
                    }
                }
            }
        });
    });
});
</script>
{/literal}
