$(document).ready(function(){
	TLocation.init();
	$(document).on('click', '.tlocation_chain .ww', function(){
		TLocation.clearLoader();
		TLocation.getCountryList();
		return false;
	});
	
	$(document).on('click', '.tlocation_chain .country', function(){
		TLocation.clearLoader();
		TLocation.getCountryList();
		$(this).text('');
		return false;
	});
	
	$(document).on('click', '.tlocation_chain .region', function(){
		return false;
	});
	
	$(document).on('click', '.loader_block .country', function(){
		TLocation.clearLoader();
		TLocation.setChain('country', this);
		TLocation.getRegionList(this);
		return false;
	});
	
	
});

TLocation={
	container: null,
	loader: null,
	chain: null,
	init: function(){
		if($('#tlocation_block_container').length!=0){
			TLocation.container=$('#tlocation_block_container');
			TLocation.loader=TLocation.container.find('.loader_block');
			TLocation.chain=TLocation.container.find('.tlocation_chain');
		}
	},
	clearLoader: function(){
		TLocation.loader.html('');
	},
	setChain: function(chain_part_class, el){
		TLocation.chain.find('.'+chain_part_class).text($(el).text());
	},
	getRegionList: function(el){
		var country_id=$(el).attr('country_id');
		$.ajax({
			url: estate_folder+'/apps/tlocation/js/ajax.php',
			type: 'post',
			dataType: 'json',
			data: {action: 'region_select_list', country_id: country_id},
			success: function(json){
				if(json.length>0){
					for(var i in json){
						var country=$('<a href=""></a>').addClass('region');
						country.text(json[i].name);
						TLocation.loader.append(country);
					}
				}
			}
		});
	},
	getCountryList: function(){
		$.ajax({
			url: estate_folder+'/apps/tlocation/js/ajax.php',
			type: 'post',
			dataType: 'json',
			data: {action: 'country_select_list'},
			success: function(json){
				if(json.length>0){
					for(var i in json){
						var country=$('<a href=""></a>').addClass('country');
						country.attr('country_id',json[i].country_id);
						country.text(json[i].name);
						TLocation.loader.append(country);
					}
				}
			}
		});
	}
};