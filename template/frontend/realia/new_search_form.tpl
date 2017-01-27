<script type="text/javascript" src="{$estate_folder}/template/frontend/{$current_theme_name}/js/search_form.js"></script>
<script>
{literal}
$(document).ready(function(){
	$(document).on('change', '.property-filter select[name=region_id]', function(){
		setTimeout(function(){
			$(".property-filter select[name=city_id]").chosen({
				disable_search_threshold: 10
			});
			$(".property-filter select[name=street_id]").chosen({
				disable_search_threshold: 10
			});
		}, 500);
	});
	$(document).on('change', '.property-filter select[name=city_id]', function(){
		setTimeout(function(){
			$(".property-filter select[name=street_id]").chosen({
				disable_search_threshold: 10
			});
		}, 500);
	});
});
{/literal}
</script>
<div class="property-filter">
   <div class="content">    
			{if $mysearch_on==1}
				{if $mysearch_save_form_file ne ''}
				{include file=$mysearch_save_form_file}
				{/if}
			{/if}
			<div id="simple_search"{if isset($smarty.request.extended_search)} style="display:none;"{/if}>
			<form method="get" action="{$estate_folder}/index.php">
				<div class="span3">
					<div class="type control-group">
						<label class="control-label" for="inputType">
						 Тип
						</label>
						 <div class="controls">
						{$structure_box}
						</div>
					</div>
					<div class="beds control-group">
						<label class="control-label" for="inputType">
						 Цена от
						</label>
						<div class="controls">
						<input type="text" class="price_from price_field" name="price_min" value="{if isset($price_min)}{$price_min|number_format:0:'':' '}{else}0{/if}"/>
						</div>
					</div>
					<div class="baths control-group">
						<label class="control-label" for="inputType">
						 Цена до
						</label>
						 <div class="controls">
						 <input type="text" class="price_for price_field" name="price" value="{if isset($price) && $price!=0}{$price|number_format:0:'':' '}{else}{$max_price|number_format:0:'':' '}{/if}"/>
						 </div>
					 </div>
					 <div class="price-value control-group">
						 <div class="controls">
						 <div class="slider"></div>
						 </div>
					 </div>
					 <a class="search_page_toggle" href="#1">{$L_ADVSEARCH}</a>
				</div>
				<div class="span3">
					{if $country_list != ''}
					 <div class="type control-group">
						 <div class="controls">
						{$country_list}
						</div>
					</div>
					{/if}
					{if $region_list != ''}
					 <div class="type control-group">
						<div class="controls">
						{$region_list}
						</div>
					 </div>
					{/if}
					{if $city_list != ''}
					 <div class="type control-group">
						<div class="controls">
						{$city_list}
						</div>
					 </div>
					{/if}
                    {if $district_list != ''}
					 <div class="type control-group">
						<div class="controls">
						{$district_list}
						</div>
					 </div>
					{/if}
					{if $street_list != ''}
					 <div class="type control-group">
						<div class="controls">
						{$street_list}
						</div>
					 </div>
					{/if}
					{if $metro_list != ''}
					 <div class="type control-group">
						<div class="controls">
						{$metro_list}
						</div>
					 </div>
					{/if}
				</div>
				<div class="span2">
					<div class="type control-group">
						<label class="control-label" for="inputType">
						{$L_ROOMS1}
						 </label>
						<div class="controls">
						{if isset($smarty.request.room_count) && is_array($smarty.request.room_count)}
						<input class="checkbox" type="checkbox" name="room_count[]" value="1"{if in_array(1,$smarty.request.room_count)} checked="checked"{/if} /> <label class="ch">1</label>
						<input class="checkbox" type="checkbox" name="room_count[]" value="2"{if in_array(2,$smarty.request.room_count)} checked="checked"{/if} /> <label class="ch">2</label>
						<input class="checkbox" type="checkbox" name="room_count[]" value="3"{if in_array(3,$smarty.request.room_count)} checked="checked"{/if} /> <label class="ch">3</label>
						<input class="checkbox" type="checkbox" name="room_count[]" value="4"{if in_array(4,$smarty.request.room_count)} checked="checked"{/if} /> <label class="ch">4+</label>
						{else if isset($smarty.request.room_count)}
						<input class="checkbox" type="checkbox" name="room_count[]" value="1"{if $smarty.request.room_count==1} checked="checked"{/if} /> <label class="ch">1</label>
						<input class="checkbox" type="checkbox" name="room_count[]" value="2"{if $smarty.request.room_count==2} checked="checked"{/if} /> <label class="ch">2</label>
						<input class="checkbox" type="checkbox" name="room_count[]" value="3"{if $smarty.request.room_count==3} checked="checked"{/if} /> <label class="ch">3</label>
						<input class="checkbox" type="checkbox" name="room_count[]" value="4"{if $smarty.request.room_count>3} checked="checked"{/if} /> <label class="ch">4+</label>
						{else}
						<input class="checkbox" type="checkbox" name="room_count[]" value="1" /> <label class="ch">1</label>
						<input class="checkbox" type="checkbox" name="room_count[]" value="2" /> <label class="ch">2</label>
						<input class="checkbox" type="checkbox" name="room_count[]" value="3" /> <label class="ch">3</label>
						<input class="checkbox" type="checkbox" name="room_count[]" value="4" /> <label class="ch">4+</label>
						{/if}
						</div>
					</div>
					<div class="form-actions">
						 <input type="submit" value="{$L_GOSEARCH}" class="btn btn-primary btn-large">
					</div>
				</div>
			</form>
			</div>
			<div id="extended_search"{if !isset($smarty.request.extended_search)} style="display:none;"{/if}>
			<form method="get" action="{$estate_folder}/index.php">
				<input type="hidden" name="extended_search" value="1" />
                <div class="span4">
					<div class="type control-group">
						<label class="control-label" for="inputType">
						 Тип
						</label>
						 <div class="controls">
						{$structure_box}
						</div>
					</div>
                  
                  <div class="baths control-group">
						<label class="control-label" for="inputType">
						 Этаж от
						</label>
						 <div class="controls">
						 <input type="text" name="floor_min" value="{if (isset($smarty.request.floor_min) && $smarty.request.floor_min!=0)}{$smarty.request.floor_min}{/if}" />
						 </div>
					 </div>
                  
                     <div class="beds control-group">
						<label class="control-label" for="inputType">
						 Этаж до
						</label>
						<div class="controls">
						<input type="text" name="floor_max" value="{if (isset($smarty.request.floor_max) && $smarty.request.floor_max!=0)}{$smarty.request.floor_max}{/if}" />
						</div>
					</div>
                   
                     <div class="beds control-group">
						<label class="control-label" for="inputType">
						 Этажей от
						</label>
						<div class="controls">
						<input type="text" name="floor_count_min" value="{if (isset($smarty.request.floor_count_min) && $smarty.request.floor_count_min!=0)}{$smarty.request.floor_count_min}{/if}" />
						</div>
					</div>
					<div class="baths control-group">
						<label class="control-label" for="inputType">
						 Этажей до
						</label>
						 <div class="controls">
						 <input type="text" name="floor_count_max" value="{if (isset($smarty.request.floor_count_max) && $smarty.request.floor_count_max!=0)}{$smarty.request.floor_count_max}{/if}" />
						 </div>
					 </div>
                     <div class="beds control-group">
						<label class="control-label" for="inputType">
						 Пл. от
						</label>
						<div class="controls">
						<input type="text" name="square_min" value="{if (isset($smarty.request.square_min) && $smarty.request.square_min!=0)}{$smarty.request.square_min}{/if}" />
						</div>
					</div>
					<div class="baths control-group">
						<label class="control-label" for="inputType">
						 Пл. до
						</label>
						 <div class="controls">
						 <input type="text" name="square_max" value="{if (isset($smarty.request.square_max) && $smarty.request.square_max!=0)}{$smarty.request.square_max}{/if}" />
						 </div>
					 </div>
					<div class="beds control-group">
						<label class="control-label" for="inputType">
						 Цена от
						</label>
						<div class="controls">
						<input type="text" class="price_from price_field" name="price_min" value="{if isset($price_min)}{$price_min|number_format:0:'':' '}{else}0{/if}"/>
						</div>
					</div>
					<div class="baths control-group">
						<label class="control-label" for="inputType">
						 Цена до
						</label>
						 <div class="controls">
						 <input type="text" class="price_for price_field" name="price" value="{if isset($price) && $price!=0}{$price|number_format:0:'':' '}{else}{$max_price|number_format:0:'':' '}{/if}"/>
						 </div>
					 </div>
					 <div class="price-value control-group">
						 <div class="controls">
						 <div class="slider"></div>
						 </div>
					 </div>
                   </div>
                     <div class="span2">
					{if $country_list ne ''}
					 <div class="type control-group">
						 <div class="controls">
						{$country_list}
						</div>
					</div>
					{/if}
					{if $region_list ne ''}
					 <div class="type control-group">
						<div class="controls">
						{$region_list}
						</div>
					 </div>
					{/if}
					{if $city_list ne ''}
					 <div class="type control-group">
						<div class="controls">
						{$city_list}
						</div>
					 </div>
					{/if}
                    {if $district_list ne ''}
					 <div class="type control-group">
						<div class="controls">
						{$district_list}
						</div>
					 </div>
					{/if}
					{if $street_list ne ''}
					 <div class="type control-group">
						<div class="controls">
						{$street_list}
						</div>
					 </div>
					{/if}
					{if $metro_list ne ''}
					 <div class="type control-group">
						<div class="controls">
						{$metro_list}
						</div>
					 </div>
					{/if}
				</div>
                <div class="span2">
					<div class="type control-group">
						<label class="control-label" for="inputType">
						{$L_ROOMS1}
						 </label>
						<div class="controls">
						{if isset($smarty.request.room_count) && is_array($smarty.request.room_count)}
									<input type="checkbox" name="room_count[]" value="1"{if in_array(1,$smarty.request.room_count)} checked="checked"{/if} /> 1
									<input type="checkbox" name="room_count[]" value="2"{if in_array(2,$smarty.request.room_count)} checked="checked"{/if} /> 2
									<input type="checkbox" name="room_count[]" value="3"{if in_array(3,$smarty.request.room_count)} checked="checked"{/if} /> 3
									<input type="checkbox" name="room_count[]" value="4"{if in_array(4,$smarty.request.room_count)} checked="checked"{/if} /> 4+
									{else if isset($smarty.request.room_count)}
									<input type="checkbox" name="room_count[]" value="1"{if $smarty.request.room_count==1} checked="checked"{/if} /> 1
									<input type="checkbox" name="room_count[]" value="2"{if $smarty.request.room_count==2} checked="checked"{/if} /> 2
									<input type="checkbox" name="room_count[]" value="3"{if $smarty.request.room_count==3} checked="checked"{/if} /> 3
									<input type="checkbox" name="room_count[]" value="4"{if $smarty.request.room_count>3} checked="checked"{/if} /> 4+
									{else}
                                    <label class="control-label" for="inputType">
									<input class="checkbox" type="checkbox" name="room_count[]" value="1" /> <label class="ch">1</label>
						<input class="checkbox" type="checkbox" name="room_count[]" value="2" /> <label class="ch">2</label>
						<input class="checkbox" type="checkbox" name="room_count[]" value="3" /> <label class="ch">3</label>
						<input class="checkbox" type="checkbox" name="room_count[]" value="4" /> <label class="ch">4+</label>
                                    </label>
									{/if}
						</div>          
                        <div class="controls">
                          <input type="checkbox" name="is_furniture"{if isset($smarty.request.is_furniture)} checked="checked"{/if} value="1" />
                         <label class="control-label" for="inputType">
						{$L_FURNITURE}
						 </label> 
                        </div>
                        <div class="controls">
                          <input type="checkbox" name="is_phone"{if isset($smarty.request.is_phone)} checked="checked"{/if} value="1" />
                         <label class="control-label" for="inputType">
						{$L_PHONE}
						 </label> 
                        </div>
                        <div class="controls">
                          <input type="checkbox" name="has_photo"{if isset($smarty.request.has_photo)} checked="checked"{/if} value="1" />
                         <label class="control-label" for="inputType">
						{$L_HASPHOTO}
						 </label>
                        </div>
					</div>
                  <div class="type control-group">
                    <div class="form-actions">
						 <input type="submit" value="{$L_GO_FIND}" class="btn btn-primary btn-large">
					</div>
                  <br>
                   <div class="hidden_advance_search"> 
                  <a class="search_page_toggle" href="#">{$L_TURNOFF}</a>
                    </div>
                    </div>
				</div>
			</form>
			</div>
  </div>
</div>