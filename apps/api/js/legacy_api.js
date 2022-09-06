$(document).ready(function () {
    $('.api_call_holder').each(function(){
        var _this = $(this);
        var api = _this.data('api');
        var action = _this.data('api');
        var primary_key = _this.data('primary-key');
        var primary_key_value = _this.data('primary-key-value');
        var method = _this.data('method');
        var params = _this.data('params');

        var body = {
          api: api,
          primary_key: primary_key,
          primary_key_value: primary_key_value,
          action: action,
          do: method,
          params: params
        };

        $.ajax({
            url: estate_folder+'/apps/api/rest.php',
            data: body,
            type: 'post',
            dataType: 'text',
            success: function(text){
                const result = $.parseJSON(text);
                if(result.state == 'success'){
                    _this.append(result.data);
                }
            }
        });
    });
});
