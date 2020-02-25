{literal}
<script type="text/javascript">
$(document).ready(function () {
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
                    //console.log('success');
                    if ( body.ql_items.active == 0 ) {
                        _this.attr('data-active', 0);
                        _this.attr('title', 'включить');
                        _this.removeClass('btn-success').addClass('btn-danger');
                        _this.parents('tr').eq(0).addClass('notactive');
                    } else {
                        _this.attr('title', 'выключить');
                        _this.attr('data-active', 1);
                        _this.removeClass('btn-danger').addClass('btn-success');
                        _this.parents('tr').eq(0).removeClass('notactive');
                    }
                }
            }
        });
    });
});
</script>
{/literal}
