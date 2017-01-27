var LinkedElements={
	refresh: function(el, linked_el_id, linked_field, model_name){
		var _this=$(el);
		var holded_form=_this.parents('form').eq(0);
		var connected_element=holded_form.find('select#'+linked_el_id).eq(0);
		LinkedElements.setEmpty(connected_element);
		connected_element.trigger('change');
		if('function' == typeof refresher_linked_global_callback){
			refresher_linked_global_callback(connected_element);
		}
		
		/*connected_element.find('option').remove();
		var opt=$('<option>', {'value': ''});
		opt.text('--');
		connected_element.append(opt);*/
		var value=_this.val();
		
		
		if(value!=''){
			$.ajax({
				type: 'post',
				url: estate_folder+'/js/ajax.php',
				data: {action: 'get_options', frommodelfield: linked_el_id, byfield: linked_field, value: value, model: model_name},
				dataType: 'json',
				success: function(json){
					if(json.length>0){
						for(var i in json){
							var opt=$('<option>', {'value': json[i].id});
							opt.text(json[i].name);
							connected_element.append(opt);
						}
					}
					connected_element.trigger('change');
					if('function' == typeof refresher_linked_global_callback){
						refresher_linked_global_callback(connected_element);
					}
				}
			});
			//connected_element.css({'border': '1px solid Green'});
		}
		
	},
	setEmpty: function(el){
		el.find('option').remove();
		var opt=$('<option>', {'value': ''});
		opt.text('--');
		el.append(opt);
	},
	setHandler: function(el_class, connected_el_class, linked_field){
		var _this=$('.'+el_class);
		var holded_form=_this.parents('form').eq(0);
		
		
		if(holded_form.length>0){
			
		}
		var connected_element=holded_form.find('.'+connected_el_class);
		
		
		_this.change(function(){
			console.log(connected_element);
			connected_element.find('option').remove();
			var opt=$('<option>', {'value': ''});
			opt.text('--');
			connected_element.append(opt);
			
			var value=_this.val();
			var connected_modelfield=connected_element.attr('data-modelfield');
			$.ajax({
				type: 'post',
				url: estate_folder+'/js/ajax.php',
				data: {action: 'get_options', frommodelfield: connected_modelfield, byfield: linked_field, value: value},
				dataType: 'json',
				success: function(json){
					
					if(json.length>0){
						for(var i in json){
							var opt=$('<option>', {'value': json[i].id});
							opt.text(json[i].name);
							connected_element.append(opt);
						}
					}
					connected_element.trigger('change');
				}
			});
			holded_form.find('.'+connected_el_class).css({'border': '1px solid Green'});
		});
	},
	setHandler2: function(el){
		var _this=$(el);
		//console.log('----');
		//console.log(_this);
		var holded_form=_this.parents('form').eq(0);
		var linked_options_string=_this.attr('data-linkeddata');
		if(linked_options_string==''){
			return;
		}
		
		var linked_options_pairs=linked_options_string.split(';');
		var linked_options=[];
		for(var i in linked_options_pairs){
			var p=linked_options_pairs[i].split(':');
			linked_options.push(p);
		}
		
		
		for(var i in linked_options){
			var connected_element=holded_form.find('select[name='+linked_options[i][0]+']').eq(0);
			//console.log('Append to '+_this.attr('id')+' '+linked_options[i][0]);
			
			
			_this.change(function(){
				var _connected_element=connected_element;
				_connected_element.find('option').remove();
				var opt=$('<option>', {'value': ''});
				opt.text('--');
				_connected_element.append(opt);
				var lo=linked_options[i];
				var value=_this.val();
				//var connected_modelfield=connected_element.attr('data-modelfield');
				if(value!=''){
					$.ajax({
						type: 'post',
						url: estate_folder+'/js/ajax.php',
						data: {action: 'get_options', frommodelfield: lo[0], byfield: lo[1], value: value},
						dataType: 'json',
						success: function(json){
							console.log(1);
							if(json.length>0){
								for(var i in json){
									var opt=$('<option>', {'value': json[i].id});
									opt.text(json[i].name);
									_connected_element.append(opt);
								}
							}
							_connected_element.trigger('change');
						}
					});
					_connected_element.css({'border': '1px solid Green'});
				}
				
			});
		}
		
		/*if(holded_form.length>0){
			
		}*/
		/*var connected_element=holded_form.find('.'+connected_el_class);
		
		
		_this.change(function(){
			console.log(connected_element);
			connected_element.find('option').remove();
			var opt=$('<option>', {'value': ''});
			opt.text('--');
			connected_element.append(opt);
			
			var value=_this.val();
			var connected_modelfield=connected_element.attr('data-modelfield');
			$.ajax({
				type: 'post',
				url: estate_folder+'/js/ajax.php',
				data: {action: 'get_options', frommodelfield: connected_modelfield, byfield: linked_field, value: value},
				dataType: 'json',
				success: function(json){
					
					if(json.length>0){
						for(var i in json){
							var opt=$('<option>', {'value': json[i].id});
							opt.text(json[i].name);
							connected_element.append(opt);
						}
					}
					connected_element.trigger('change');
				}
			});
			holded_form.find('.'+connected_el_class).css({'border': '1px solid Green'});
		});*/
	}
}

function update_child_list ( id, context ) {
	if(context===undefined || context===null){
		return;
	}
	
	var parent=$(context).parents('form').eq(0);
	var c=parent.find('#'+ id);
	//console.log(c);
	var multiselect=false;
	if(c.attr('multiple')=='multiple'){
		multiselect=true;
	}
	
	parent.find('[id='+ id +'_div]').html('<div id="select_box_loading"></div>');
	
	var it=parent.find('#country_id');
	if(it.length!=0){
		var country_id = parseInt(it.val());
	}else{
		var country_id = 0;
	}
	
	var it=parent.find('#region_id');
	if(it.length!=0){
		var region_id = parseInt(it.val());
	}else{
		var region_id = 0;
	}
	
	var it=parent.find('#city_id');
	if(it.length!=0){
		var city_id = parseInt(it.val());
	}else{
		var city_id = 0;
	}
	
	var it=parent.find('#district_id');
	if(it.length!=0){
		var district_id = parseInt(it.val());
	}else{
		var district_id = 0;
	}
	
	var it=parent.find('#metro_id');
	if(it.length!=0){
		var metro_id = parseInt(it.val());
	}else{
		var metro_id = 0;
	}
	
	var it=parent.find('#street_id');
	if(it.length!=0){
		var street_id = parseInt(it.val());
	}else{
		var street_id = 0;
	}
	
	
	var parts=[];
	parts.push('action=get_'+ id);
	if(country_id !== undefined && !isNaN(country_id) && country_id!=0){
		parts.push('country_id='+ country_id);
	}
	if(region_id!==undefined && !isNaN(region_id) && region_id!=0){
		parts.push('region_id='+ region_id);
	}
	if(city_id!==undefined && !isNaN(city_id) && city_id!=0){
		parts.push('city_id='+ city_id);
	}
	if(district_id!==undefined && !isNaN(district_id) && district_id!=0){
		parts.push('district_id='+ district_id);
	}
	if(metro_id!==undefined && !isNaN(metro_id) && metro_id!=0){
		parts.push('metro_id='+ metro_id);
	}
	if(street_id!==undefined && !isNaN(street_id) && street_id!=0){
		parts.push('street_id='+ street_id);
	}
	/*if(multiselect){
		parts.push('multiselect=1');
	}*/
	parts.push('callback=?');
	
	//var url = estate_folder+'/js/ajax.php?action=get_'+ id +'&country_id='+ country_id  +'&region_id='+ region_id +'&city_id='+ city_id +'&district_id='+ district_id +'&metro_id='+ metro_id +'&street_id='+ street_id +(multiselect ? '&multiselect=1' : '')+'&callback=?';
	var url=estate_folder+'/js/ajax.php?'+parts.join('&');
		
	jQuery.ajax({
		url: url, 
		dataType: 'json', 
		type: "get",
		/*timeout: 2000,*/
		success: function(json){
			parent.find('[id='+ id +'_div]').replaceWith(json.response.body).fadeIn('slow', function() {
				if('function' == typeof refresher_global_callback){
					refresher_global_callback(parent.find('[id='+ id +'_div] select'));
				}
			}); 
		},
		error: function(){}
	});

}

function update_child_list_without_district ( id, context ) {
	if(context===undefined || context===null){
		return;
	}
	
	var parent=$(context).parents('form').eq(0);
	
	parent.find('[id='+ id +'_div]').html('<div id="select_box_loading"></div>');
	
	var country_id = parent.find('#country_id').val();
	var region_id = parent.find('#region_id').val();
	var city_id = parent.find('#city_id').val();
	//var district_id = parent.find('#district_id').val();
	var metro_id = parent.find('#metro_id').val();
	var street_id = parent.find('#street_id').val();

	var url = estate_folder+'/js/ajax.php?action=get_'+ id +'&country_id='+ country_id  +'&region_id='+ region_id +'&city_id='+ city_id +'&metro_id='+ metro_id +'&street_id='+ street_id + '&callback=?';

	jQuery.ajax({
		url: url, 
		dataType: 'json', 
		type: "get",
		/*timeout: 2000,*/
		success: function(json){
			parent.find('[id='+ id +'_div]').replaceWith(json.response.body).fadeIn('slow', function() {}); 
		},
		error: function(){/*alert("error");*/}
	});

}

function update_child_list_multiple ( id ) {
	jQuery('#'+ id +'_div').html('<div id="select_box_loading"></div>');
	
	var country_id = jQuery('#country_id').val();
	var region_id = jQuery('#region_id').val();
	var city_id = jQuery('#city_id').val();
	var district_id = jQuery('#district_id').val();
	var metro_id = jQuery('#metro_id').val();
	var street_id = jQuery('#street_id').val();
	
	var url = estate_folder+'/js/ajax.php?action=get_'+ id +'&country_id='+ country_id  +'&region_id='+ region_id +'&city_id='+ city_id +'&district_id='+ district_id +'&metro_id='+ metro_id +'&street_id='+ street_id +'&multiple_mode=yes' + '&callback=?';
	
	jQuery.getJSON(url, {}, function(json){
	
	jQuery('#'+ id +'_div').replaceWith(json.response.body);
	jQuery('#'+ id +'_div').fadeIn('slow', function() {
	    // Animation complete.
	});         
	});
}

function set_empty ( id, context ) {
	if(context===undefined || context===null){
		return;
	}
	var parent=$(context).parents('form').eq(0);
	parent.find('#'+ id +'_div').html('');
}

function update_mkrn_list(id, context){
	if(context===undefined || context===null){
		return;
	}
	
	var parent=$(context).parents('form').eq(0);
	
	parent.find('[id='+ id +'_div]').html('<div id="select_box_loading"></div>');
	
	var district_id = parent.find('#district_id').val();
	
	var url = estate_folder+'/template/frontend/realia/main/ajax/ajax.php?action=get_mkrn'+'&district_id='+ district_id + '&callback=?';

	jQuery.ajax({
		url: url, 
		dataType: 'json', 
		type: "get",
		/*timeout: 2000,*/
		success: function(json){
			parent.find('[id='+ id +'_div]').replaceWith(json.response.body).fadeIn('slow', function() {}); 
		},
		error: function(){/*alert("error");*/}
	});
}
/*
function addRangeSlider(el_id){
	$('#'+el_id).slider().on('slide', function(ev){
		var slider=$(this);
		var value=slider.slider('getValue');
		console.log(value.val());
	});
}
*/
$(document).ready(function(){
	/*$('._linkedelement').each(function(){
		LinkedElements.setHandler2(this);
	});*/
	
	
	$('.geoautocomplete').each(function(){
		var _this=$(this);
		var parent=$(this).parents('.geoautocomplete_block').eq(0);
		var _hidden=parent.find('input[type=hidden]');
		var _pk=$(this).attr('pk');
		var _table=$(this).attr('from');
		var _table=$(this).attr('from');
		var _depel=$(this).data('depel');
		var _depelkey=$(this).data('depelkey');
		
		
		$(this).autocomplete({
			open: function() { 
	            $('.ui-menu').width(_this.width());
	            /**/
	        } ,
			source: function( request, response ) {
				var answer=[];
				if(_depel!=''){
					var dep_val=parent.parents('form').eq(0).find('[name='+_depel+']').val();
				}else{
					var dep_val=0;
				}
				$.ajax({
					url: estate_folder+'/apps/tlocation/js/ajax.php',
					type: 'GET',
					dataType: 'json',
					data: 'action=get_geolist&from='+_table+'&term='+encodeURIComponent(request.term)+'&depelkey='+_depelkey+'&dep_val='+dep_val,
					success: function(json) {
						
						$.map(json,function(n,i){
							
							var o={};
							o.id=n[_pk];
							o.value=n.name;
							o.label=n.name;
							answer.push(o);
						});
						response(answer);
					}
				});
	    	},
	    	response: function( event, ui ) {
				_hidden.val('');
			},
			minLength: 1,
			select: function( event, ui ) {
				event.stopPropagation();
				_hidden.val(ui.item.id);
				//_hidden.trigger('change');
			},
			change: function( event, ui ) {
				_hidden.trigger('change');
				if(ui.item===null){
					_hidden.val('');
					
				}
			},
		});
		
		/*$(this).input(function(){
			_hidden.val('');
		});*/
		
		/*$(this).keydown(function(){
			_hidden.val('');
		});*/
	});
});