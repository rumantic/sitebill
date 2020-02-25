    function complete_load (session_code) {
        $('#excel').html('Идет загрузка <img src="' + estate_folder + '/img/loading.gif" border="0" width="16" height="16"/>');
        $('.sql_button').hide();
        $('#sql_log').html('');

        $.ajax({
            url: estate_folder + '/apps/excelfree/js/ajax.php',
            data: 'action=excel&session_code=' + session_code,
            type: "POST",
            success: function(json){
                $('#excel').html('');
                $('#excel').append(json);
                $('.sql_button').show();
            },
        });

    }
    $(document).ready(function(){
        $(".applied").sortable({}).disableSelection();

        $('#formsubmit').click(function(){
            var checkboxes=$(this).parents('form').eq(0).find('tbody.applied input[type=checkbox]:checked');
            if(checkboxes.length==0){
                alert('Необходимо выбрать хотя бы одну колонку для экспорта');
                return false;
            }
        });


        $(document).on('click', '.sql', function(){
            var datastr=[];
            var parent=$(this).parents('tr').eq(0);
            $(".title").find('select.field').each(function(){
                datastr.push('assoc_array['+$(this).attr('name')+']='+$(this).val());
            });

            var ret = '';
            for(var i=0;i<ca.length;i++){
                ret += ca[i] + '=' + $("#" + ca[i]).val() + '&';
            }
            $('.sql_button').hide();

            $('#sql_log').html('Обновление базы, может занят несколько минут... <img src="' + estate_folder + '/img/loading.gif" border="0" width="16" height="16"/>');
            $.ajax({
                url: estate_folder + '/apps/excelfree/js/ajax.php',
                data: 'action=sql_exec&' + ret +'&'+datastr.join('&'),
                type: "POST",
                success: function(json){
                    $('#sql_log').html('');
                    $('#excel').html('');
                    $('#filenotify').html('');

                    $('#sql_log').append(json);
                },
            });
        });

        $(document).on('change', '.field', function(){	
            $('.sql_button').hide();
            $('#sql_log').html('');
            var datastr=[];
            var parent=$(this).parents('tr').eq(0);
            parent.find('select.field').each(function(){
                datastr.push('assoc_array['+$(this).attr('name')+']='+$(this).val());
            });

            var ret = '';
            for(var i=0;i<ca.length;i++){
                ret += ca[i] + '=' + $("#" + ca[i]).val() + '&';
            }

            $('#excel').html('Идет загрузка <img src="' + estate_folder + '/img/loading.gif" border="0" width="16" height="16"/>');
            $.ajax({
                url: estate_folder + '/apps/excelfree/js/ajax.php',
                data: 'action=excel&' + ret +'&'+datastr.join('&'),
                type: "POST",
                success: function(json){
                    $('.sql_button').show();
                    $('#excel').html('');
                    $('#excel').append(json);
                },
            });
        });





    });

    $.fn.serializeObject = function()
    {
        var o = {};
        var a = this.serializeArray();
        $.each(a, function() {
            if (o[this.name] !== undefined) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };	



    $.fn.serializeMyObject = function()
    {
        var o = [];
        var a = this.serializeArray();
        //console.log(a);
        $.each(a, function() {
            if(this.name.indexOf('[')!=-1){
                reg=/([A-Za-z0-9_]*)\[([A-Za-z0-9_]*)\]/;
                var name=reg.exec(this.name);
                //console.log(name);
                if(o[name[1]]!==undefined){
                    if(o[name[1]][name[2]]!==undefined){
                        o[name[1]][name[2]].push(this.value || '');
                    }else{
                        o[name[1]][name[2]] = this.value || '';
                    }


                }else{
                    o[name[1]]=[];
                    o[name[1]][name[2]] = this.value || '';
                }
            }else{
                if(o[this.name]!==undefined){
                    o[this.name].push(this.value || '');

                }else{
                    o[this.name] = this.value || '';
                }
            }
        });
        return o;
    };



$.fn.serializeObjectX = function()
{
    var o = {};
    var a = this.serializeArray();
    $.each(a, function() {
        if(this.name.indexOf('[')!=-1){
                reg=/([A-Za-z0-9_]*)\[([A-Za-z0-9_]*)\]/;
                var name=reg.exec(this.name);
                //console.log(name);
                if(o[name[1]]!==undefined){
                    if(o[name[1]][name[2]]!==undefined){
                        o[name[1]][name[2]].push(this.value || '');
                    }else{
                        o[name[1]][name[2]] = this.value || '';
                    }


                }else{
                    o[name[1]]={};
                    o[name[1]][name[2]] = this.value || '';
                }
            }else{
                if (o[this.name] !== undefined) {
                    if (!o[this.name].push) {
                        o[this.name] = [o[this.name]];
                    }
                    o[this.name].push(this.value || '');
                } else {
                    o[this.name] = this.value || '';
                }
            }
    });
    return o;
};