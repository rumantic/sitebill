var TLocationForm = {
	autocomplete : false,
	setHandler : function(el_class, link_street_to_city, is_autocomplete){
		if(is_autocomplete==1){
			this.autocomplete=true;
		}
		//console.log(2);
		//var block=$('.tlocation_object');
		/*var block=$('#'+el_id);
		var country_select=block.find('[name=country_id]');
		var region_select=block.find('[name=region_id]');
		var city_select=block.find('[name=city_id]');
		var district_select=block.find('[name=district_id]');
		var street_select=block.find('[name=street_id]');*/
		
		
		var country_select=$('.'+el_class+' [name=country_id]');
		var region_select=$('.'+el_class+' [name=region_id]');
		var city_select=$('.'+el_class+' [name=city_id]');
		var district_select=$('.'+el_class+' [name=district_id]');
		var street_select=$('.'+el_class+' [name=street_id]');
		
		if(this.autocomplete){
			country_select.combobox();
			region_select.combobox();
			city_select.combobox();
			district_select.combobox();
			street_select.combobox();
		}
		
		
		country_select.change(function(){
			TLocationForm.clearSelect(region_select);
			TLocationForm.clearSelect(city_select);
			TLocationForm.clearSelect(district_select);
			TLocationForm.clearSelect(street_select);
			var country_id=$(this).val();
			if(country_id!=0){
				$.ajax({
					url: estate_folder+'/apps/tlocation/js/ajax.php',
					type: 'post',
					dataType: 'json',
					data: {action: 'region_select_list', country_id: country_id},
					success: function(json){
						if(json.length>0){
							for(var i in json){
								var option=$('<option value="'+json[i].region_id+'">'+json[i].name+'</option>');
								region_select.append(option);
							}
						}
						TLocationForm.updateSelect(region_select);
						TLocationForm.updateSelect(city_select);
						TLocationForm.updateSelect(district_select);
						TLocationForm.updateSelect(street_select);
					}
				});
			}else{
				TLocationForm.updateSelect(region_select);
				TLocationForm.updateSelect(city_select);
				TLocationForm.updateSelect(district_select);
				TLocationForm.updateSelect(street_select);
			}
		});
		region_select.change(function(){
			city_select.attr('disabled', 'disabled');
			TLocationForm.clearSelect(city_select);
			TLocationForm.clearSelect(district_select);
			TLocationForm.clearSelect(street_select);
			var region_id=$(this).val();
			if(region_id!=0){
				$.ajax({
					url: estate_folder+'/apps/tlocation/js/ajax.php',
					type: 'post',
					dataType: 'json',
					data: {action: 'city_select_list', region_id: region_id},
					success: function(json){
						if(json.length>0){
							for(var i in json){
								var option=$('<option value="'+json[i].city_id+'">'+json[i].name+'</option>');
								city_select.append(option);
							}
						}
						city_select.attr('disabled', false);
						TLocationForm.updateSelect(city_select);
						TLocationForm.updateSelect(district_select);
						TLocationForm.updateSelect(street_select);
					}
				});
			}else{
				TLocationForm.updateSelect(city_select);
				TLocationForm.updateSelect(district_select);
				TLocationForm.updateSelect(street_select);
			}
		});
		city_select.change(function(){
			if(link_street_to_city==1){
				TLocationForm.clearSelect(district_select);
				TLocationForm.clearSelect(street_select);
				var city_id=$(this).val();
				if(city_id!=0){
					$.ajax({
						url: estate_folder+'/apps/tlocation/js/ajax.php',
						type: 'post',
						dataType: 'json',
						data: {action: 'street_select_list', city_id: city_id},
						success: function(json){
							if(json.length>0){
								for(var i in json){
									var option=$('<option value="'+json[i].street_id+'">'+json[i].name+'</option>');
									street_select.append(option);
								}
							}
							TLocationForm.updateSelect(street_select);
						}
					});
				}
				
				if(city_id!=0){
					$.ajax({
						url: estate_folder+'/apps/tlocation/js/ajax.php',
						type: 'post',
						dataType: 'json',
						data: {action: 'district_select_list', city_id: city_id},
						success: function(json){
							if(json.length>0){
								for(var i in json){
									var option=$('<option value="'+json[i].district_id+'">'+json[i].name+'</option>');
									district_select.append(option);
								}
							}
							TLocationForm.updateSelect(district_select);
							TLocationForm.updateSelect(street_select);
						}
					});
				}else{
					TLocationForm.updateSelect(district_select);
					TLocationForm.updateSelect(street_select);
				}
				
				
			}else{
				TLocationForm.clearSelect(district_select);
				TLocationForm.clearSelect(street_select);
				var city_id=$(this).val();
				if(city_id!=0){
					$.ajax({
						url: estate_folder+'/apps/tlocation/js/ajax.php',
						type: 'post',
						dataType: 'json',
						data: {action: 'district_select_list', city_id: city_id},
						success: function(json){
							if(json.length>0){
								for(var i in json){
									var option=$('<option value="'+json[i].district_id+'">'+json[i].name+'</option>');
									district_select.append(option);
								}
							}
							TLocationForm.updateSelect(district_select);
							TLocationForm.updateSelect(street_select);
						}
					});
				}else{
					TLocationForm.updateSelect(district_select);
					TLocationForm.updateSelect(street_select);
				}
			}
			
			
		});
		district_select.change(function(){
			if(link_street_to_city!=1){
				TLocationForm.clearSelect(street_select);
				var district_id=$(this).val();
				if(district_id!=0){
					$.ajax({
						url: estate_folder+'/apps/tlocation/js/ajax.php',
						type: 'post',
						dataType: 'json',
						data: {action: 'street_select_list', district_id: district_id},
						success: function(json){
							if(json.length>0){
								for(var i in json){
									var option=$('<option value="'+json[i].street_id+'">'+json[i].name+'</option>');
									street_select.append(option);
								}
							}
							TLocationForm.updateSelect(street_select);
						}
					});
				}else{
					TLocationForm.updateSelect(street_select);
				}
			}
			
		});
		street_select.change(function(){
			//console.log('refresh street');
		});
	},
	clearSelect : function(el){
		el.find('option').each(function(){
			if($(this).attr('value')!=0){
				$(this).remove();
			}
		});
		if(this.autocomplete){
			el.data('combobox').clearElement();
			//el.data('combobox').toggle();
			el.data('combobox').clearTarget();
		}
		
	},
	updateSelect : function(el){
		if(this.autocomplete){
			el.data('combobox').refresh();
		}
	}
};












/*

function setHandler(el_id, link_street_to_city){
	
	var block=$('#'+el_id);
	var country_select=block.find('[name=country_id]');
	var region_select=block.find('[name=region_id]');
	var city_select=block.find('[name=city_id]');
	var district_select=block.find('[name=district_id]');
	var street_select=block.find('[name=street_id]');
	
	
	country_select.change(function(){
		clearSelect(region_select);
		clearSelect(city_select);
		clearSelect(district_select);
		clearSelect(street_select);
		var country_id=$(this).val();
		if(country_id!=0){
			$.ajax({
				url: estate_folder+'/apps/tlocation/js/ajax.php',
				type: 'post',
				dataType: 'json',
				data: {action: 'region_select_list', country_id: country_id},
				success: function(json){
					if(json.length>0){
						for(var i in json){
							var option=$('<option value="'+json[i].region_id+'">'+json[i].name+'</option>');
							region_select.append(option);
						}
					}
				}
			});
		}
	});
	region_select.change(function(){
		city_select.attr('disabled', 'disabled');
		clearSelect(city_select);
		clearSelect(district_select);
		clearSelect(street_select);
		var region_id=$(this).val();
		if(region_id!=0){
			$.ajax({
				url: estate_folder+'/apps/tlocation/js/ajax.php',
				type: 'post',
				dataType: 'json',
				data: {action: 'city_select_list', region_id: region_id},
				success: function(json){
					if(json.length>0){
						for(var i in json){
							var option=$('<option value="'+json[i].city_id+'">'+json[i].name+'</option>');
							city_select.append(option);
						}
					}
					city_select.attr('disabled', false);
				}
			});
		}
	});
	city_select.change(function(){
		if(link_street_to_city==1){
			clearSelect(district_select);
			clearSelect(street_select);
			var city_id=$(this).val();
			if(city_id!=0){
				$.ajax({
					url: estate_folder+'/apps/tlocation/js/ajax.php',
					type: 'post',
					dataType: 'json',
					data: {action: 'street_select_list', city_id: city_id},
					success: function(json){
						if(json.length>0){
							for(var i in json){
								var option=$('<option value="'+json[i].street_id+'">'+json[i].name+'</option>');
								street_select.append(option);
							}
						}
					}
				});
				$.ajax({
					url: estate_folder+'/apps/tlocation/js/ajax.php',
					type: 'post',
					dataType: 'json',
					data: {action: 'district_select_list', city_id: city_id},
					success: function(json){
						if(json.length>0){
							for(var i in json){
								var option=$('<option value="'+json[i].district_id+'">'+json[i].name+'</option>');
								district_select.append(option);
							}
						}
					}
				});
			}
		}else{
			clearSelect(district_select);
			clearSelect(street_select);
			var city_id=$(this).val();
			if(city_id!=0){
				$.ajax({
					url: estate_folder+'/apps/tlocation/js/ajax.php',
					type: 'post',
					dataType: 'json',
					data: {action: 'district_select_list', city_id: city_id},
					success: function(json){
						if(json.length>0){
							for(var i in json){
								var option=$('<option value="'+json[i].district_id+'">'+json[i].name+'</option>');
								district_select.append(option);
							}
						}
					}
				});
			}
		}
		
	});
	district_select.change(function(){
		if(link_street_to_city!=1){
			clearSelect(street_select);
			var district_id=$(this).val();
			if(district_id!=0){
				$.ajax({
					url: estate_folder+'/apps/tlocation/js/ajax.php',
					type: 'post',
					dataType: 'json',
					data: {action: 'street_select_list', district_id: district_id},
					success: function(json){
						if(json.length>0){
							for(var i in json){
								var option=$('<option value="'+json[i].street_id+'">'+json[i].name+'</option>');
								street_select.append(option);
							}
						}
					}
				});
			}
		}
		
	});
	street_select.change(function(){
		
	});
}

*/


$(document).ready(function(){
	
	
});

function clearSelect(el){
	$(el).find('option').each(function(){
		if($(this).attr('value')!=0){
			$(this).remove();
		}
	});
}