function complete_load (primary_key, model_name, file_name) {
    
    $(".loading").css("display", "block");
	$("#button_block").css("display", "none");
	
    var qdata={};
    qdata.assoc_array={};
	$('.xls_row_title').find('select.field').each(function(){
        qdata.assoc_array[$(this).attr('name')]=$(this).val();
    });	
    $("#uploads_result").html("");
    qdata.action='dropzone_xls';
    qdata.model_name=model_name;
    qdata.primary_key=primary_key;
    qdata.file_name=file_name;
    qdata.do='import';
    $.ajax({
        url: estate_folder + '/js/ajax.php',
    	data: qdata,		
        type: "POST",
        dataType: 'json',
        success: function(json){
            $("#uploads_result").html("");
    		$("#uploads_result").append(json.content);
            $(".loading").css("display", "none");
        },
        error: function(){
            $("#uploads_result").html("");
    		$("#uploads_result").append('json.content');
            $(".loading").css("display", "none");
        }
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

    
    //$('.field').live('change',function(){
    $(document).on('change', '.field',function(){
        $('.sql_button').hide();
        $('#sql_log').html('');
        var qdata={};
        qdata.assoc_array={};
        var parent=$(this).parents('tr').eq(0);
        parent.find('select.field').each(function(){
            qdata.assoc_array[$(this).attr('name')]=$(this).val();
        });
        
        qdata.action='dropzone_xls';
        qdata.model_name=model_name;
        qdata.primary_key=primary_key;
        qdata.file_name=file_name;
        qdata.do='parse_xls';
        $('#excel').html('Идет загрузка <img src="' + estate_folder + '/img/loading.gif" border="0" width="16" height="16"/>');
        $.ajax({
            url: estate_folder + '/js/ajax.php',
            data: qdata,		
            type: "POST",
            success: function(json){
                $('.sql_button').show();
                $('#uploads_result').html('');
                $('#uploads_result').append(json);
            },
        });
    });
});