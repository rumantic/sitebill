$(document).ready(function(){
    var fixHelper = function(e, ui) {
        ui.children().each(function() {
            $(this).width($(this).width());
        });
        return ui;
    };
    
    var appendEditableHandlerToField = function(el, field_name){
    	$(el).editable({
    		ajaxOptions: {dataType: "json"}, 
    		url: estate_folder+"/apps/table/js/ajax.php?action=change_column_field&field_name="+field_name, 
    		success: function(response, newValue) {
    			if(!response.success) {
    				return response.msg;
    			}
    		}
    	});
    };
    
    $.fn.editable.defaults.mode = 'inline';
    
    $('.addeditable').each(function(){
    	appendEditableHandlerToField(this,'title');
    });
    
    $(".applied").sortable({
    	stop: function( event, ui ) {
    		var parent=$(ui.item).parents('.accordion-group').eq(0);
    		if(parent.length!=1){
    			parent=$(ui.item).parents('table').eq(0);
    		}
    		if(parent.length==1){
    			var childs=parent.find('.column');
    			if(childs.length>0){
    				var ids=[];
    				var count=childs.length;
    				if(count>0){
    					for(var i=0; i<count; i++){
    						var alt=$(childs[i]).attr('alt');
    						if(alt!=''){
    							ids.push(alt);
    						}
    					}
    				}
    				if(ids.length>0){
    					$.ajax({
    						url: estate_folder + '/apps/table/js/ajax.php',
    						type: 'POST',
    						dataType: 'text',
    						data: 'action=reorder_columns&ids='+ids.join(','),
    						success: function(data) {
    							//alert('Сортировка сохранена');
    						}
    					});
    				}
    			}
    			
    		}
    	}
    }).disableSelection();
    
    $('.delete_checked_columns').click(function(){
    	var parent_table=$(this).parents('table').eq(0);
    	var action='columns';
    	var ids=[];
    	parent_table.find('.checker:checked').each(function(){
    		ids.push($(this).val());
    	});
    	if(ids.length>0){
    		window.location.replace(estate_folder+'/admin/index.php?action='+action+'&do=mass_delete&ids='+ids.join(','));
		}
    	//console.log(ids);
    	return false;
    });
    
    $('.activity_set_columns').click(function(){
    	
    	var parent_table=$(this).parents('.columns_list').eq(0);
    	var action='columns';
    	var ids=[];
    	
    	parent_table.find('.checker:checked').each(function(){
    		ids.push('batch_ids[]='+$(this).val());
    	});
    	
    	if(ids.length>0){
    		window.location.replace(estate_folder+'/admin/index.php?action='+action+'&do=mass_activity_set&'+ids.join('&'));
		}
    	return false;
    });
    
    $('.field_tab').dblclick(function(){
    	var _this=$(this);
    	var content_element=$(this).find('span').clone();
    	var content='';
    	//console.log(content_element.hasClass('undefined'));
    	if(content_element.hasClass('undefined')){
    		content='';
    	}else{
    		content=content_element.text();
    	}
    	
    	
    	
    	var id=$(this).attr('alt');
    	_this.html('');
    	var new_content=$('<input type="text" value="'+content+'" />').blur(function(){
    		var c=new_content.val();
    		$.ajax({
    			url: estate_folder+'/apps/table/js/ajax.php',
    			type: 'POST',
    			dataType: 'text',
    			data: 'action=change_field_tab&tab_name='+c+'&id='+id,
    			success: function(data) {
    				if(data==''){
    					_this.html('');
    					_this.append(content_element);
    				}else if(data=='Не указано'){
    					_this.html('');
    					_this.append(content_element.text(data).removeClass('defined').addClass('undefined'));
    				}else{
    					_this.html('');
    					_this.append(content_element.text(data).removeClass('undefined').addClass('defined'));
    				}
    				//_this.html(c);
    			},
    			error: function(){
    				_this.append(content_element);
    			}
    		});
    		
    		//_this.html(c);
    	});
    	_this.append(new_content);
    	new_content.focus();
    	return false;
    });
    
    $('a.state_change').click(function(){
    	var ops=$(this).attr('href');
    	var id=$(this).attr('alt');
    	var a=this;
    	//$(this).find('img').attr('src',estate_folder+'/apps/admin/admin/template/img/active.png');
    	
    	$.ajax({
			url: estate_folder+'/apps/table/js/ajax.php',
			type: 'POST',
			dataType: 'text',
			data: 'action=change_column_state&operation='+ops+'&id='+id,
			success: function(data) {
				if(data=='activated'){
		    		/*$(a).addClass('btn-warning');
		    		$(a).attr('href','deactivate');
		    		$(a).parents('tr').eq(0).removeClass('row3notactive').addClass('row3');*/
		    		changeStateToActive(a);
		    	}else if(data=='deactivated'){
		    		/*$(a).removeClass('btn-warning');
		    		$(a).attr('href','activate');
		    		$(a).parents('tr').eq(0).removeClass('row3').addClass('row3notactive');*/
		    		changeStateToNotactive(a);
		    	}else if(data=='required'){
		    		changeStateToRequired(a);
		    	}else if(data=='derequired'){
		    		changeStateToNotrequired(a);
		    	}
			}
		});
    	return false;
    });
	
	
	$('.update_table').live('click',function(){
		var parent=$(this).parents('.accordion-group').eq(0);
		if(parent.length!=1){
			parent=$(this).parents('tr').eq(0);
		}
		if(parent.length==1){
			var childs=parent.find('.column');
			if(childs.length>0){
				var ids=[];
				var count=childs.length;
				if(count>0){
					for(var i=0; i<count; i++){
						var alt=$(childs[i]).attr('alt');
						if(alt!=''){
							ids.push(alt);
						}
					}
				}
				if(ids.length>0){
					$.ajax({
						url: estate_folder + '/apps/table/js/ajax.php',
						type: 'POST',
						dataType: 'text',
						data: 'action=reorder_columns&ids='+ids.join(','),
						success: function(data) {
							alert('Сортировка сохранена');
						}
					});
				}
			}
			
		}
		return false;
	});
	
	$('.columns_list .show_groups').click(function(e){
		e.preventDefault();
		var cid=$(this).data('cid');
		var _this=$(this);
		$.ajax({
			url: estate_folder + '/apps/table/js/ajax.php',
			type: 'POST',
			dataType: 'json',
			data: 'action=show_groups&columns_id='+cid,
			success: function(json) {
				if(typeof json != 'object'){
					return;
				}
				$('#group_ed input[name=cid]').val(cid);
				$('#group_ed .group_ed_ch input').each(function(){
					var val=$(this).val();
					
					if(json.indexOf(val)===-1){
						$(this).prop('checked', false);
					}else{
						$(this).prop('checked', true);
					}
					$('#group_ed').modal('show');
				});
			}
		});
	});
	
	$('#group_ed .ok').click(function(e){
		e.preventDefault();
		var ids=[];
		$('#group_ed .group_ed_ch input:checked').each(function(){
			ids.push($(this).val());
		});
		var data={};
		data.ids=ids;
		data.action='change_groups';
		data.columns_id=$('#group_ed input[name=cid]').val();
		$.ajax({
			url: estate_folder + '/apps/table/js/ajax.php',
			type: 'POST',
			dataType: 'json',
			data: data,
			success: function(json) {
				
			}
		});
	});
	
	$('#group_ed').on('hide', function () {
		$('#group_ed input[name=cid]').val('');
		$('#group_ed .group_ed_ch input').each(function(){
			$(this).prop('checked', false);
		});
	})
});

function changeStateToNotactive(a){
	$(a).removeClass('btn-warning');
	$(a).attr('href','activate');
	$(a).parents('tr').eq(0).removeClass('row3').addClass('row3notactive');
}

function changeStateToActive(a){
	$(a).addClass('btn-warning');
	$(a).attr('href','deactivate');
	$(a).parents('tr').eq(0).removeClass('row3notactive').addClass('row3');
}

function changeStateToNotrequired(a){
	$(a).removeClass('btn-warning');
	$(a).attr('href','required');
}

function changeStateToRequired(a){
	$(a).addClass('btn-warning');
	$(a).attr('href','derequired');
}