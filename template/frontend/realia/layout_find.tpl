<script>
{literal}

var LiveSearch={
		collectRequestParams: function(){
			 var form = $('form#live_search_form');
			 console.log(SitebillCore.serializeFormJSON(form));
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
			//var url_params=this.collectRequestParams();
			this.blockTargetScreen();
			var mode=mode||'';
			
			$.ajax({
				url: estate_folder+'/js/ajax.php',
				data: {action: 'find', params: params, what: 'list', local_ajax: '1'},
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
	};

$(document).ready(function(){
	$('form#live_search_form').append($('<input name="page" type="hidden" value="1">'));
	$('form#live_search_form').append($('<input name="order" type="hidden" value="">'));
	$('form#live_search_form').append($('<input name="asc" type="hidden" value="">'));
	LiveSearch.runRefresh();
	
	$('#find form#live_search_form').submit(function(e){
		e.preventDefault();
		LiveSearch.runRefresh();
	});
	$(document).on('click', '#pager a', function(){
		var _this=$(this);
		var p=_this.parents('li').eq(0);
		$('#pager li').removeClass('active');
		if(!p.hasClass('disabled')){
			p.addClass('active');
			$('form#live_search_form').eq(0).find('input[name=page]').val(_this.attr('alt'));
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
		$('form#live_search_form').eq(0).find('input[name=order]').val(order);
		$('form#live_search_form').eq(0).find('input[name=asc]').val(new_sort);
		LiveSearch.runRefresh();
		e.preventDefault();
	});
});
{/literal}
</script>
<div class="container">
    <div id="find">
    	<div class="row">
			<div class="span9">
				<div id="resultcount">
					Всего найдено <span></span>
				</div>
				<table class="content_main table">
					<thead>
					    <tr>
					    	<th><a href="#" class="sort" alt="date_added">{$L_DATE}</a></th>
					        <th><a href="#" class="sort" alt="id">{$L_ID}</a></th>
					        <th><a href="#" class="sort" alt="type">{$L_TYPE}</a></th>
					        <th><a href="#" class="sort" alt="city">{$L_CITY}</a></th>
					        <th><a href="#" class="sort" alt="district">{$L_DISTRICT}</a></th>
					        <th><a href="#" class="sort" alt="street">{$L_STREET}</a></th>
					        <th><a href="#" class="sort" alt="price">{$L_PRICE}</a></th>
					        <th><a href="#" class="sort" alt="floor">{$L_FLOOR}</a></th>
					        <th><a href="#" class="sort" alt="square_all">{$L_SQUARE} м<sup>2</sup></a></th>
					    </tr>
				    </thead>
				</table>
    			<div id="datascreen"></div>
    			<div id="pager" class="span12 pagination pagination-centered"></div>
    		</div>
    		
    		<div class="sidebar span3">
				
				{include file='search_form.tpl'}
				<br>
				{include file='right_special.tpl'}
			</div>
    	</div>
    </div>
</div>