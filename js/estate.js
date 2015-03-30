var globalFns = {};
function globalInit(){
	for(var i in globalFns)
	{
		window[i](globalFns[i]);
	}
}
function assertGlobalFn(key,param)
{
	globalFns[key] = param;
}

$(document).ready(function(){
	
	$('.grid_check_all').change(function(){
		var status=$(this).is(':checked');
		var checkboxes=$(this).parents('table').eq(0).find('input.grid_check_one');
		
		if(status){
			checkboxes.each(function(){
				$(this).attr('checked','checked');
			});
		}else{
			checkboxes.each(function(){
				$(this).attr('checked',false);
			});
		}
	});
	
	$('.delete_checked').click(function(){
		var ids=[];
		var action=$(this).attr('alt');
		$(this).parents('table').eq(0).find('input.grid_check_one:checked').each(function(){
			ids.push($(this).val());
		});
		//console.log(ids);
		window.location.replace(estate_folder+'/admin/index.php?action='+action+'&do=mass_delete&ids='+ids.join(','));
		//
	});
	
	$('a#getmyfavorites').click(function(){
		var container=$('#tabs-services-favorites');
		container.html('Загрузка...');
		$.ajax({
	        url: estate_folder + "/js/ajax.php?action=get_my_favorites",
	        type: "POST",
	        dataType: "json",
	        success: function(json){
				var decoded = $("<div/>").html(json.response.body).text();
				container.html('');
				container.append(decoded);
			}
	    });
		return true;
	});
	
	

	$('a#specialoffers').click(function(){
		var container=$('#tabs-services-special');
		container.html('Загрузка...');
		$.ajax({
	        url: estate_folder + "/js/ajax.php?action=get_specialoffers",
	        type: "POST",
	        dataType: "json",
	        success: function(json){
				var decoded = $("<div/>").html(json.response.body).text();
				container.html('');
				container.append(decoded);
			}
	    });
		return true;
	});
	
	/*$('a.add_to_favorites').on('click',function(){
		var o=$(this);
		var id=o.attr('alt');
		if(id){
			$.ajax({
		        url: estate_folder + "/js/ajax.php?action=add_to_favorites",
		        data: 'id='+id,
		        type: "POST",
		        dataType: "json",
		        beforeSend: function(){
				
				},
		        success: function(json){
		        	if(json.response.body=='OK'){
		        		$('span#favorites_count').text(new Number($('span#favorites_count').text())+1);
		        		o.removeClass('add_to_favorites');
		        		o.attr('title', 'Удалить из избранных');
		        		o.attr('class','remove_from_favorites').attr('href','#remove_from_favorites');
		        	}
		        },
		        error: function(xhr, ajaxOptions, thrownError){
		        	
				}
		    });
		}
		return false;
	});*/
	
	/*$('a.remove_from_favorites').on('click',function(){
		var o=$(this);
		var id=o.attr('alt');
		if(id){
			$.ajax({
		        url: estate_folder + "/js/ajax.php?action=remove_from_favorites",
		        data: 'id='+id,
		        type: "POST",
		        dataType: "json",
		        beforeSend: function(){
				
				},
		        success: function(json){
		        	if(json.response.body=='OK'){
		        		$('span#favorites_count').text(new Number($('span#favorites_count').text())-1);
		        		o.removeClass('remove_from_favorites');
		        		o.attr('title', 'Добавить в избранное');
		        		o.attr('class','add_to_favorites').attr('href','#add_to_favorites');
		        	}
		        },
		        error: function(xhr, ajaxOptions, thrownError){
		        	
				}
		    });
			
			
			
		}
		return false;
	});*/
	
	$(document).on('click','a.add_to_favorites',function(){
		var o=$(this);
		var id=o.attr('alt');
		if(id){
			$.ajax({
		        url: estate_folder + "/js/ajax.php?action=add_to_favorites",
		        data: 'id='+id,
		        type: "POST",
		        dataType: "json",
		        beforeSend: function(){
				
				},
		        success: function(json){
		        	if(json.response.body=='OK'){
		        		$('#favorites_count').text(new Number($('#favorites_count').text())+1);
		        		o.removeClass('add_to_favorites');
		        		o.attr('title', 'Удалить из избранных');
		        		o.attr('class','remove_from_favorites').attr('href','#remove_from_favorites');
		        	}
		        },
		        error: function(xhr, ajaxOptions, thrownError){
		        	
				}
		    });
		}
		return false;
	});
	
	$(document).on('click','a.remove_from_favorites',function(){
		var o=$(this);
		var id=o.attr('alt');
		if(id){
			$.ajax({
		        url: estate_folder + "/js/ajax.php?action=remove_from_favorites",
		        data: 'id='+id,
		        type: "POST",
		        dataType: "json",
		        beforeSend: function(){
				
				},
		        success: function(json){
		        	if(json.response.body=='OK'){
		        		$('#favorites_count').text(new Number($('#favorites_count').text())-1);
		        		o.removeClass('remove_from_favorites');
		        		o.attr('title', 'Добавить в избранное');
		        		o.attr('class','add_to_favorites').attr('href','#add_to_favorites');
		        	}
		        },
		        error: function(xhr, ajaxOptions, thrownError){
		        	
				}
		    });
		}
		return false;
	});
	
	/*$('#tabs-services-favorites a.remove_from_favorites').on('click',function(){
		var o=$(this);
		var id=o.attr('alt');
		o.parents('tr').eq(0).remove();
		$('table.content_main a[alt='+id+']').attr('class','add_to_favorites').attr('href','#add_to_favorites');
	});*/
	
	$(document).on('click','#tabs-services-favorites a.remove_from_favorites',function(){
		var o=$(this);
		var id=o.attr('alt');
		o.parents('tr').eq(0).remove();
		$('table.content_main a[alt='+id+']').attr('class','add_to_favorites').attr('href','#add_to_favorites');
	});
	
});




var streetlist = [];
function init_streetlist()
{
	streetlist = [];
	$("form select[name=street_id]").children().eq(0).nextAll().each(function(){
		streetlist.push($(this).html());
	});
	
	$(document).ready(function(){
	
	$("#istreet").autocompleteArray(streetlist,
		{
			delay:10,
			minChars:1,
			matchSubset:1,
			autoFill:true,
			maxItemsToShow:10
		}
	);

	});

}


function addToFavorites(id){
	$.ajax({
        url: estate_folder + "/js/ajax.php?action=add_to_favorites",
        data: 'id='+id,
        type: "POST",
        dataType: "json",
        beforeSend: function(){
		
		},
        success: function(json){
        	if(json.response.body=='OK'){
        		$('span#favorites_count').text(new Number($('span#favorites_count').text())+1);
        		$('#fav_'+id).hide();
        	}
        },
        error: function(xhr, ajaxOptions, thrownError){
        	
		}
    });
	return false;
}

function removeFromFavorites(id){
	$.ajax({
        url: estate_folder + "/js/ajax.php?action=remove_from_favorites",
        data: 'id='+id,
        type: "POST",
        dataType: "json",
        beforeSend: function(){
		
		},
        success: function(json){
        	if(json.response.body=='OK'){
        		$('span#favorites_count').text(new Number($('span#favorites_count').text())-1);
        		$('#fav_'+id).hide();
        	}
        },
        error: function(xhr, ajaxOptions, thrownError){
        	
		}
    });
	return false;
}

function upd_streetlist()
{
}

function showYandexMap(){
	$('#YMapsIDpmap').hide();
	$('#YMapsID').show();
}

function showPMap(){
	$('#YMapsID').hide();
	$('#YMapsIDpmap').show();
}

function get_search_form () {
	$.ajax({
        url: estate_folder + "/js/ajax.php?action=get_search_form",
        data: 'text=1',
        type: "POST",
        dataType: "json",
        beforeSend: function(){
			$("#search_main").html('');
			$("#search_main").html('<img src="' + estate_folder + '/img/loading.gif">');
        },
        success: function(json){
        	$("#search_main").html('');
        	//$("#search_main").append('test');
			var decoded = $("<div/>").html(json.response.body).text();
        	$("#search_main").append(decoded);
		},
        error: function(xhr, ajaxOptions, thrownError){
			alert(xhr.status);
        	alert(thrownError);			        
        }
    });
}

function get_favorites(el){
	var alt=$(el).attr('alt');
	var panel=$(el).parents('.jv_tablago2').eq(0).find(".jv_tabs_panel div[class=jv_lago2_content][alt='"+alt+"'] .custom");
	//var panel=$(el).parents('.jv_tablago2');
	panel.html('Загрузка...');
	$.ajax({
        url: estate_folder + "/js/ajax.php?action=get_my_favorites",
        type: "POST",
        dataType: "json",
        success: function(json){
			var decoded = $("<div/>").html(json.response.body).text();
			panel.html('');
			panel.append(decoded);
		}
    });
	return true;
}

function get_specialoffers(el){
	var alt=$(el).attr('alt');
	var panel=$(el).parents('.jv_tablago2').eq(0).find(".jv_tabs_panel div[class=jv_lago2_content][alt='"+alt+"'] .custom");
	//var panel=$(el).parents('.jv_tablago2');
	panel.html('Загрузка...');
	$.ajax({
        url: estate_folder + "/js/ajax.php?action=get_specialoffers",
        type: "POST",
        dataType: "json",
        success: function(json){
			var decoded = $("<div/>").html(json.response.body).text();
			panel.html('');
			panel.append(decoded);
		}
    });
	return true;
}
