{literal}
<script>
var LiveSearch={
		collectRequestParams: function(){
			 var form = $('form.partial');
			 return SitebillCore.serializeFormJSON(form);
		},
		blockTargetScreen: function(){
			$('#datascreen').css({'opacity': 0.5});
		},
		freeTargetScreen: function(){
			$('#datascreen').css({'opacity': 1});
		},
		stripSlashes: function(str){
			str = str.replace(/\\'/g, '\'');
	        str = str.replace(/\\"/g, '"');
	        str = str.replace(/\\0/g, '\0');
	        str = str.replace(/\\\\/g, '\\');
	        return str;
		},
		runRefresh: function(mode){
			
			var params=this.collectRequestParams();
			var url_params=this.collectRequestParams();
			this.blockTargetScreen();
			var mode=mode||'';
			
			$.ajax({
				url: estate_folder+'/js/ajax.php',
				data: {action: 'admin_data_getter', params: params, what: 'list'},
				type: 'post',
				dataType: 'json',
				success: function(json){
					if(json){
						$('#datascreen').html('');
						if(mode!='nopager'){
							$('#pager').html(LiveSearch.stripSlashes(json.pager));
							if($('#pager li.active').length==0){
								$('#pager li:first').addClass('active');
							}
						}
						$('#datascreen').html(LiveSearch.stripSlashes(json.grid));
						$('#resultcount span').html(LiveSearch.stripSlashes(json._total_records));
					}
					
					LiveSearch.freeTargetScreen();
					
				}
			});
		},
		buildSlider: function(slider_el_id, min_el_id, max_el_id){
			if(slider_el_id.length==0){
				return;
			}
			var sliderSel=slider_el_id.slider();
				
			//var sliderSel=slider_el_id;
			var minSel=min_el_id;
			var maxSel=max_el_id;
			
			var d=sliderSel.data('slider').getValue();
			//console.log(d);
			min_el_id.val(d[0]);
			max_el_id.val(d[1]);
			
			
			slider_el_id.on('slide', function(){
				var d=slider_el_id.data('slider').getValue();
				min_el_id.val(d[0]);
				max_el_id.val(d[1]);
			}).on('slideStop', function(){
				var d=slider_el_id.data('slider').getValue();
				min_el_id.val(d[0]);
				max_el_id.val(d[1]);
				LiveSearch.runRefresh();
			});
			
			min_el_id.change(function(){
				var a=Number(min_el_id.val());
				var b=Number(max_el_id.val());
				var max=max_el_id.data('sliderMax');
				
				if(a>b){
					var t=a;
					a=b;
					b=t;
					t=null;
				}
				
				if(b>max){
					b=max;
					max_el_id.val(max);
				}
				if(a<0){
					a=0;
					min_el_id.val(0)
				}
				
				var c=[];
				c.push(a);
				c.push(b);
				console.log(c);
				
				slider_el_id.data('slider').setValue(c);
			});
			
			$(max_el_id).change(function(){
				
				var a=Number($(min_el_id).val());
				var b=Number($(max_el_id).val());
				var max=$(slider_el_id).data('sliderMax');
				
				if(a>b){
					var t=a;
					a=b;
					b=t;
					t=null;
				}
				
				if(b>max){
					b=max;
					$(max_el_id).val(max);
				}
				if(a<0){
					a=0;
					$(min_el_id).val(0)
				}
				
				var c=[];
				c.push(a);
				c.push(b);
				
				$(slider_el_id).data('slider').setValue(c);
			});
		}
			
	};
	
$(document).ready(function(){
	
	$('.checkers #city_id').change(function(){
		var val=$(this).val();
		//$('.sftabs input[name=city_id]').val(val);
		var holder=$('#district_id.select_box_by_query_as_checkboxes');
		holder.css({'opacity': 0.5});
		$.ajax({
			url: estate_folder+'/js/ajax.php',
			type: 'post',
			dataType: 'json',
			data: {action: 'get_districts', city_id: val},
			success: function(json){
				var _json=json;
				holder.find('div').remove();
				for(var i in _json.districts){
					var o=$('<div><input type="checkbox" name="district_id[]" value="'+_json.districts[i].id+'"><span>'+_json.districts[i].name+'</span></div>');
					holder.append(o);
				}
				holder.css({'opacity': 1});
				holder.parents('.dholder').eq(0).find('.dholder-selected').text('Выбрано районов: 0');
				LiveSearch.runRefresh();
			}
		});
	});
	
	$('.dholder-selected').click(function(e){
		e.preventDefault();
		$(this).parents('.dholder').eq(0).find('.select_box_by_query_as_checkboxes').slideToggle();
	});
	
	$(document).on('change', '#district_id.select_box_by_query_as_checkboxes input[type=checkbox]', function(){
		var parent=$(this).parents('#district_id.select_box_by_query_as_checkboxes').eq(0);
		var c=parent.find('input[type=checkbox]:checked').length;
		parent.parents('.dholder').eq(0).find('.dholder-selected').text('Выбрано районов: '+c);
		LiveSearch.runRefresh();
	});
	
	$('.grid_check_all_items').change(function(){
		var status=$(this).is(':checked');
		var data_holder=$('#datascreen');
		var checkboxes=data_holder.find('input.grid_check_one');
		
		if(status){
			checkboxes.each(function(){
				$(this).prop('checked', 'checked');
			});
		}else{
			checkboxes.each(function(){
				$(this).prop('checked', false);
			});
		}
	});
	
	$('.delete_checked_user').click(function(e){
		var ids=[];
		var action=$(this).attr('alt');
		$(this).parents('table').eq(0).find('input.grid_check_one:checked').each(function(){
			ids.push($(this).val());
		});
		if(ids.length>0){
			$.ajax({
				url: estate_folder+'/apps/userdata/js/ajax.php',
				data: {action: 'mass_delete', ids: ids},
				type: 'post',
				dataType: 'json',
				success: function(json){
					informerShow('Объекты удалены');
					setTimeout(informerHide, 2000);
					LiveSearch.runRefresh();
				}
			});
		}
		//window.location.replace(estate_folder+'/account/data/?do=mass_delete&ids='+ids.join(','));
		e.preventDefault();
	});
	
	$('.batch_update').click(function(){
		var ids=[];
		var action=$(this).attr('alt');
		$(this).parents('table').eq(0).find('input.grid_check_one:checked').each(function(){
			ids.push('batch_ids[]='+$(this).val());
		});
		if(ids.length>0){
			window.location.replace(estate_folder+'/account/data/?do=batch_update&'+ids.join('&'));
		}
	});
	
	$('.pdf_export').click(function(e){
		var ids=[];
		var action=$(this).attr('alt');
		$('#datascreen input.grid_check_one:checked').each(function(){
			ids.push('exported_ids[]='+$(this).val());
		});
		if(ids.length>0){
			ids.push('order='+$('form.partial [name=order]').val());
			ids.push('asc='+$('form.partial [name=asc]').val());
			ids.push('_owner='+$('form.partial [name=_owner]').val());
			if(confirm('Выгружать с полным описанием')){
				window.open(estate_folder+'/account/data/?do=getpdf&ext=1&'+ids.join('&'));
			}else{
				window.open(estate_folder+'/account/data/?do=getpdf&'+ids.join('&'));
			}
			
		}else{
			informerShow('Не выбран ни один объект');
			setTimeout(informerHide, 2000);
		}
		e.preventDefault();
	});
	
	$('.excell_export').click(function(e){
		var ids=[];
		var action=$(this).attr('alt');
		$('#datascreen input.grid_check_one:checked').each(function(){
			ids.push('exported_ids[]='+$(this).val());
		});
		if(ids.length>0){
			if(confirm('Выгружать с полным описанием')){
				window.open(estate_folder+'/account/data/?do=getexcel&ext=1&'+ids.join('&'));
			}else{
				window.open(estate_folder+'/account/data/?do=getexcel&'+ids.join('&'));
			}
			
		}else{
			informerShow('Не выбран ни один объект');
			setTimeout(informerHide, 2000);
		}
		
		e.preventDefault();
	});
	
	$('.duplicate').click(function(e){
		var ids=[];
		var action=$(this).attr('alt');
		$(this).parents('table').eq(0).find('input.grid_check_one:checked').each(function(){
			ids.push($(this).val());
		});
		if(ids.length>0){
			$.ajax({
				url: estate_folder+'/apps/userdata/js/ajax.php',
				data: {action: 'duplicate', ids: ids},
				type: 'post',
				dataType: 'json',
				success: function(json){
					informerShow('Объекты дублированы');
					setTimeout(informerHide, 2000);
					LiveSearch.runRefresh();
				}
			});
		}
		e.preventDefault();
	});
	
	$('form#main_partial .checkbox_collection_decheck').click(function(e){
		$('form#main_partial .ait_bc input[type=checkbox]').prop('checked', false);
		LiveSearch.runRefresh();
		e.preventDefault();
	});
	
	
	$('#runSearch').click(function(e){
		LiveSearch.runRefresh();
		e.preventDefault();
	});
	
	$('#clearSearch').click(function(e){
		e.preventDefault();
		var form = $('form.partial');
		form.find('select').val('');
		form.find('input[type=text]').val('');
		form.find('input[type=checkbox]').prop('checked', false);
		LiveSearch.runRefresh();
		
	});
	
	LiveSearch.buildSlider($('#price_slider'), $('#price_min'), $('#price'));
	LiveSearch.buildSlider($('#floor_slider'), $('#floor_min'), $('#floor_max'));
	LiveSearch.buildSlider($('#square_slider'), $('#square_min'), $('#square_max'));
	
	$(document).on('click', '#pager a', function(){
		var _this=$(this);
		var p=_this.parents('li').eq(0);
		$('#pager li').removeClass('active');
		if(!p.hasClass('disabled')){
			p.addClass('active');
			$('form#main_partial').eq(0).find('input[name=page]').val(_this.attr('alt'));
			LiveSearch.runRefresh(/*'nopager'*/);
		}
		return false;
	});
	
	$(document).on('click', 'a.sort', function(e){
		var _this=$(this);
		if(_this.hasClass('sort_asc')){
			$('a.sort').removeClass('sort_asc').removeClass('sort_desc');
			var new_sort='desc';
			_this.addClass('sort_desc');
		}else if(_this.hasClass('sort_desc')){
			$('a.sort').removeClass('sort_desc').removeClass('sort_asc');
			var new_sort='asc';
			_this.addClass('sort_asc');
		}else{
			$('a.sort').removeClass('sort_asc').removeClass('sort_desc');
			var new_sort='desc';
			_this.addClass('sort_desc');
		}
		
		var order=_this.attr('alt');
		$('form#main_partial').eq(0).find('input[name=order]').val(order);
		$('form#main_partial').eq(0).find('input[name=asc]').val(new_sort);
		
		/*var p=_this.parents('li').eq(0);
		if(!p.hasClass('disabled')){
			$('form#main_partial').eq(0).find('input[name=page]').val(_this.attr('alt'));
			LiveSearch.runRefresh();
		}*/
		LiveSearch.runRefresh();
		e.preventDefault();
	});
	
	$('.ait_bc_h input[type=checkbox]').change(function(){
		var parent=$(this).parents('.ait_bc').eq(0);
		if($(this).is(':checked')){
			parent.find('.ait_bc input[type=checkbox]').prop('checked', true).change();
		}
	});
	
	$('.checkbox_collection_decheck').click(function(e){
		var parent=$(this).parents('.checkbox_collection').eq(0);
		if(parent.length==1){
			parent.find('.ait_bc input[type=checkbox]').prop('checked', false).change();
		}
		e.preventDefault();
	});
	
	$('form.partial input').change(function(){
		LiveSearch.runRefresh();
	});
	
	$('form.partial select').change(function(){
		LiveSearch.runRefresh();
	});
	/*
	$('#sort_options a').click(function(e){
		var p=$(this).attr('href').split(':');
		$('form#main_partial').eq(0).find('input[name=order]').val(p[0]);
		$('form#main_partial').eq(0).find('input[name=direction]').val(p[1]);
		LiveSearch.runRefresh();
		e.preventDefault();
	});
	*/
	$('#optype_checker a').click(function(){
		$('form#main_partial').eq(0).find('input[name=optype]').val($(this).attr('alt'));
		LiveSearch.runRefresh();
	});

	
	$(document).on('change', '.checkers select', function(){
		LiveSearch.runRefresh();
	});
	
	if($('#realtytype_checker li.active').length==0){
		$('#realtytype_checker a:first').tab('show');
		$('form#main_partial').eq(0).find('input[name=supertopic_id\\[\\]]').val($('#realtytype_checker a:first').attr('alt'));
	}
	
	if($('#optype_checker li.active').length==0){
		$('#optype_checker a:first').tab('show');
		$('form#main_partial').eq(0).find('input[name=optype]').val($('#optype_checker a:first').attr('alt'));
	}
	
	$(document).on('click', '.show_item', function(){
		var href=$(this).attr('data-href');
		window.open(href);
		/*$('#view .view_search').slideToggle();
		$('#view .view_item').slideToggle();*/
	});
	
	$('#accountInformer .accountInformer-close').click(function(e){
		informerHide();
		e.preventDefault();
	});
	
	
	
	LiveSearch.runRefresh();
	
	
	
	
});
</script>
{/literal}
{include file=$user_data_search_form}
<div class="row-fluid" id="view">
		<div class="span12 view_search">
			<div class="row-fluid">
				<div class="span12" id="resultcount">
					Всего найдено <span></span>
				</div>
			</div>
			
			<div class="row-fluid">
				<div class="span12">
					<a target="_blank" class="pdf_export btn btn-success"><i class="icon-white icon-print"></i> PDF</a> 
					<a class="excell_export btn btn-success"><i class="icon-white icon-file"></i> Excell</a> 
				</div>
			</div>
			
			
			<div class="row-fluid">
				<div class="span12">
					<table class="content_main table">
						<thead>
						    <tr>
						    	<td><input type="checkbox" class="grid_check_all" /></td>
						        <th><a href="#" class="sort" alt="date_added">{$L_DATE}</a></th>
						        <th><a href="#" class="sort" alt="id">{$L_ID}</a></th>
						        <th>{$L_PHOTO}</th>
						        <th><a href="#" class="sort" alt="type">{$L_TYPE}</a></th>
						        <th><a href="#" class="sort" alt="city">{$L_CITY}</a></th>
						        <th><a href="#" class="sort" alt="district">{$L_DISTRICT}</a></th>
						        <th><a href="#" class="sort" alt="street">{$L_STREET}</a></th>
						        <th><a href="#" class="sort" alt="price">{$L_PRICE}</a></th>
						        <th>{$L_FLOOR}</th>
						        <th>{$L_PHONE}</th>
						        <th>{$L_SQUARE} м<sup>2</sup></th>
						        {if $admin !=''}
						        <th class="row_title"></th>
						        {/if}
						    </tr>
					    </thead>
					    
					    <tbody id="datascreen">
					    </tbody>
					    
					</table>
				</div>
				<div class="span12 pagination pagination-centered" id="pager">
					
				</div>
			</div>
		</div>
		<div class="span12 view_item">
		</div>
	</div>