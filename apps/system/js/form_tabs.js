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
	
	$('.leveled').each(function(){
		$(this).StructureLvl();
	});
	//console.log($('.leveled'));
	//$('.leveled').StructureLvl();
	
});



(function($){
	if (typeof StructureLvl === 'undefined' || !$.isFunction(StructureLvl)) {
		jQuery.fn.StructureLvl = function(){
			var el = $(this);
			if(el.length==0){
				return;
			}
			if(el.data('leveled')=='leveled'){
				return el;
			}
			el.data('leveled', 'leveled');
			var inp=el.find('input[type=hidden]');
			el.find('select').change(function(){
				reset($(this));
			});
			initEl();
			
			function initEl(){
				el.find('select').each(function(){
					var tid=parseInt($(this).find('option:selected').attr('value'), 10);
					if(tid>0){
						$(this).show();
						el.find('select#t_'+tid).show();
					}else{
						el.find('select#t_0').show();
					}
				});
			};
			function reset(sel){
				var tid=parseInt(sel.val(), 10);
				if(isNaN(tid)){
					tid=0;
				}
				var level=sel.parents('.level').eq(0);
				level.nextAll('.level').find('select').val(0).hide();
				if(tid>0){
					el.find('select#t_'+tid).show();
					setVal(tid);
				}else{
					var prev_el=parseInt(sel.attr('id').replace('t_', ''), 10);
					setVal(prev_el);
				}
				
			};
			function setVal(val){
				inp.val(val);
				inp.trigger('change');
			};
			
			return el;
		};
	}
	
})(jQuery);

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