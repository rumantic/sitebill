$(document).ready(function(){
	$('.form_tab').hide().first().show();
	$('#form_tab_switcher a').removeClass('active_tab_link').first().addClass('active_tab_link');
	
	$('#form_tab_switcher a').click(function(){
		//console.log($(this).attr('href'));
		var tab_id=$(this).attr('href');
		$('.form_tab').hide();
		$('#'+tab_id).show();
		$('#form_tab_switcher a').removeClass('active_tab_link');
		$(this).addClass('active_tab_link');
		return false;
	});
	
	$('#form_tab_switcher a.active_tab').trigger('click');
	
	$('a.go_to_step').click(function(){
		var form=$('form#step_form');
		var step=$(this).attr('alt');
		var action=form.attr('action');
		var expr = /step(\d+)/;
		action=action.replace(expr,'step'+step);
		form.attr('action',action);
		
		var old_action=form.find('input[name=do]').val();
		if(old_action=='new'){
			form.find('input[name=do]').val('new');
		}else if(old_action=='edit'){
			form.find('input[name=do]').val('edit');
		}else if(old_action=='edit_done'){
			form.find('input[name=do]').val('edit');
		}else if(old_action=='new_done'){
			form.find('input[name=do]').val('new');
		}else{
			form.find('input[name=do]').val('new');
		}
		//form.find('#formsubmit').parents('form').eq(0).submit();
		
		form.find('#formsubmit').removeAttr("disabled").trigger('click');
		
		return false;
	});
	
	$('#formsubmit_back').click(function(){
		var form=$(this).parents('form').eq(0);
		var step=$(this).attr('alt');
		var action=form.attr('action');
		var expr = /step(\d+)/;
		action=action.replace(expr,'step'+step);
		form.attr('action',action);
		
		var old_action=form.find('input[name=do]').val();
		if(old_action=='new'){
			form.find('input[name=do]').val('new');
		}else if(old_action=='edit'){
			form.find('input[name=do]').val('edit');
		}else if(old_action=='edit_done'){
			form.find('input[name=do]').val('edit');
		}else if(old_action=='new_done'){
			form.find('input[name=do]').val('new');
		}else{
			form.find('input[name=do]').val('new');
		}
		//return false;
		//form.find('input[name=do]').val('new');
		form.submit();
		//form.find('#formsubmit').trigger('click');
		
		return false;
	});
	
	var form_field_view_topic=new Array();
	
	$.ajax({
		url: estate_folder+'/js/ajax.php?action=get_form_fields_rules',
		dataType: 'json',
		success: function(json){
			form_field_view_topic=json;
			//console.log(form_field_view_topic);
			checkFormFieldsVisibility($('#topic_id').val(),form_field_view_topic,$('#topic_id').parents('form').eq(0));
		}
	});
	
	$('#topic_id').change(function(){
		var current_topic_id=$(this).val();
		var parent=$(this).parents('form').eq(0);
		checkFormFieldsVisibility(current_topic_id,form_field_view_topic,parent);
	})
	
	
	
	
	
});

function checkFormFieldsVisibility(current_topic_id,topic_array,context){
	if(current_topic_id!='' && current_topic_id!=0){
		for(var key in topic_array){
			if(topic_array[key][0]!='all'){
				if($.inArray(current_topic_id,topic_array[key])!==-1){
					context.find('[alt='+key+']').show();
				}else{
					context.find('[alt='+key+']').hide();
				}
			}else{
				context.find('[alt='+key+']').show();
			}
		}
	}else if(current_topic_id==0){
		for(var key in topic_array){
			if(topic_array[key][0]!='all'){
				context.find('[alt='+key+']').hide();
			}
		}
	}
}