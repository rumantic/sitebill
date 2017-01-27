    function complete_load (primary_key, model_name, file_name) {
	//console.log('complete_load, '+primary_key+', '+model_name+', '+file_name);

	var datastr=[];
	$('.xls_row_title').find('select.field').each(function(){
    		datastr.push('assoc_array['+$(this).attr('name')+']='+$(this).val());
    	});

	//console.log('ca =');
	//console.log(ca);
	//console.log('datastr' + datastr);

    		
    	//var ret = '';
    	//for(var i=0;i<ca.length;i++){
    		//ret += ca[i] + '=' + $("#" + ca[i]).val() + '&';
        //}
	//console.log('ret' + ret);

	var url=estate_folder + '/js/ajax.php?action=dropzone_xls&do=import&primary_key='+primary_key+'&model_name='+model_name+'&file_name='+file_name + '&' +datastr.join('&');
	//console.log('url = '+url);
	$(".loading").css("display", "block");
	$("#button_block").css("display", "none");
	$("#uploads_result").html("");
	$.getJSON(url,{},function(data){
    		$("#uploads_result").html("");
    		$("#uploads_result").append(data.content);
		$(".loading").css("display", "none");
	});
    }
    $(document).ready(function(){
    	$(".applied").sortable({
    	}).disableSelection();
    	
    	$('#formsubmit').click(function(){
    		var checkboxes=$(this).parents('form').eq(0).find('tbody.applied input[type=checkbox]:checked');
    		if(checkboxes.length==0){
    			alert('Необходимо выбрать хотя бы одну колонку для экспорта');
    			return false;
    		}
    	});
    	
    	
        
    	
        	
    	$('.field').live('change',function(){
        	$('.sql_button').hide();
        	$('#sql_log').html('');
    		var datastr=[];
    		var parent=$(this).parents('tr').eq(0);
    		parent.find('select.field').each(function(){
    			datastr.push('assoc_array['+$(this).attr('name')+']='+$(this).val());
    		});

		//console.log('datastr = ' + datastr);

	var datastr1=[];
	//var test = $('.xls_row_title').find('select.field');
	//console.log('test = '+test);
	//console.log(test);
	$('.xls_row_title').find('select.field').each(function(){
		//console.log(1);
    		datastr1.push('assoc_array['+$(this).attr('name')+']='+$(this).val());
    	});

		//console.log('datastr1 = ' + datastr1);
    		
    		//var ret = '';
    		//for(var i=0;i<ca.length;i++){
    			//ret += ca[i] + '=' + $("#" + ca[i]).val() + '&';
        	//}
        	
        	$('#excel').html('Идет загрузка <img src="' + estate_folder + '/img/loading.gif" border="0" width="16" height="16"/>');
    		$.ajax({
    			url: estate_folder + '/js/ajax.php',
    			data: 'action=dropzone_xls&do=parse_xls&model_name='+model_name+'&primary_key='+primary_key+'&file_name='+file_name + '&' +datastr.join('&'),
    			type: "POST",
    			success: function(json){
    		    	$('.sql_button').show();
    				$('#uploads_result').html('');
        			$('#uploads_result').append(json);
        		},
    		});
    	});
    	
    	
    	
    	
    
    });
   
    //console.log($(".applied"));
    //$(".applied").sortable({
    //}).disableSelection();
    
    
	
	
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
