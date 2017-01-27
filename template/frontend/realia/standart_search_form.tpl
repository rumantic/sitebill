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
var max_price={/literal}{if $max_price ne ''}{$max_price}{else}0{/if}{literal};
var price_from=Number({/literal}{if isset($price_min)}{$price_min}{else}0{/if}{literal});
var price_for=Number({/literal}{if (isset($price)) && ($price ne '')}{$price}{else}{$max_price}{/if}{literal});
{/literal}
</script>
{$ajax_functions}
{foreach from=$scripts item=script}
	{*$script*}
{/foreach}
<div class="property-filter pull-right">
    <div class="content">
{if $mysearch_on==1}
   {if $mysearch_save_form_file ne ''}
      {include file=$mysearch_save_form_file}
   {/if}
{/if}
    
        <form method="get" action="{$estate_folder}/">
        {*$currency_list*}
            <div class="type control-group">
                <label class="control-label" for="inputType">
                    {$L_TYPE}
                </label>
                <div class="controls">
                	{$structure_box}
                    
                </div><!-- /.controls -->
            </div><!-- /.control-group -->

            {if $country_list != ''}
            <div class="type control-group">
                <label class="control-label" for="inputType">
                    {$L_COUNTRY}
                </label>
                <div class="controls">
                	{$country_list}
                </div><!-- /.controls -->
            </div><!-- /.control-group -->
            {/if}
            
            {if $region_list != ''}
            <div class="type control-group">
                <label class="control-label" for="inputType">
                    {$L_REGION}
                </label>
                <div class="controls">
                	{$region_list}
                </div><!-- /.controls -->
            </div><!-- /.control-group -->
            {/if}
            
            {if $city_list != ''}
            <div class="type control-group">
                <label class="control-label" for="inputType">
                    {$L_CITY}
                </label>
                <div class="controls">
                	{$city_list}
                </div><!-- /.controls -->
            </div><!-- /.control-group -->
            {/if}
            
            {if $district_list != ''}
            <div class="type control-group">
                <label class="control-label" for="inputType">
                    {$L_DISTRICT}
                </label>
                <div class="controls">
                	{$district_list}
                </div><!-- /.controls -->
            </div><!-- /.control-group -->
            {/if}
            
            {if $street_list != ''}
            <div class="type control-group">
                <label class="control-label" for="inputType">
                    {$L_STREET}
                </label>
                <div class="controls">
                	{$street_list}
                </div><!-- /.controls -->
            </div><!-- /.control-group -->
            {/if}
            
            {if $metro_list != ''}
            <div class="type control-group">
                <label class="control-label" for="inputType">
                    {$L_METRO}
                </label>
                <div class="controls">
                	{$metro_list}
                </div><!-- /.controls -->
            </div><!-- /.control-group -->
            {/if}
<!-- 
            <div class="rent control-group">
                <div class="controls">
                    <label class="checkbox" for="inputRent">
                        <input type="checkbox" name="optype[]" value="1"id="inputRent"> Аренда
                    </label>
                </div>
            </div>

            <div class="sale control-group">
                <div class="controls">
                    <label class="checkbox" for="inputSale">
                        <input type="checkbox" name="optype[]" value="2" id="inputSale"> Продажа
                    </label>
                </div>
            </div>
  -->           
            <div class="beds control-group">
                <div class="controls">
                    <input type="text" class="price_from price_field" name="price_min" value="{if isset($price_min)}{$price_min|number_format:0:'':' '}{else}0{/if}"/>
                </div><!-- /.controls -->
            </div><!-- /.control-group -->

            <div class="baths control-group">
                <div class="controls">
                    <input type="text" class="price_for price_field" name="price" value="{if isset($price) && $price!=0}{$price|number_format:0:'':' '}{else}{$max_price|number_format:0:'':' '}{/if}"/>
                </div><!-- /.controls -->
            </div><!-- /.control-group -->
            
            <div class="price-value control-group">
                <div class="controls">
                    <div class="slider"></div>
                </div><!-- /.controls -->
            </div>
            
            <div class="type control-group">
                <label class="control-label" for="inputType">
                    {$L_ROOMS1}
                </label>
                <div class="controls">
					{if isset($smarty.request.room_count) && is_array($smarty.request.room_count)}
					<div class="ch_small"><input class="checkbox" type="checkbox" name="room_count[]" value="1"{if in_array(1,$smarty.request.room_count)} checked="checked"{/if} /> <label class="ch">1</label></div> 
					<div class="ch_small"><input class="checkbox" type="checkbox" name="room_count[]" value="2"{if in_array(2,$smarty.request.room_count)} checked="checked"{/if} /> <label class="ch">2</label></div> 
					<div class="ch_small"><input class="checkbox" type="checkbox" name="room_count[]" value="3"{if in_array(3,$smarty.request.room_count)} checked="checked"{/if} /> <label class="ch">3</label></div>  
					<div class="ch_small"><input class="checkbox" type="checkbox" name="room_count[]" value="4"{if in_array(4,$smarty.request.room_count)} checked="checked"{/if} /> <label class="ch">4+</label> </div> 
					{else if isset($smarty.request.room_count)}
					<div class="ch_small"><input class="checkbox" type="checkbox" name="room_count[]" value="1"{if $smarty.request.room_count==1} checked="checked"{/if} /> <label class="ch">1</label></div>  
					<div class="ch_small"><input class="checkbox" type="checkbox" name="room_count[]" value="2"{if $smarty.request.room_count==2} checked="checked"{/if} /> <label class="ch">2</label></div>  
					<div class="ch_small"><input class="checkbox" type="checkbox" name="room_count[]" value="3"{if $smarty.request.room_count==3} checked="checked"{/if} /> <label class="ch">3</label></div>  
					<div class="ch_small"><input class="checkbox" type="checkbox" name="room_count[]" value="4"{if $smarty.request.room_count>3} checked="checked"{/if} /> <label class="ch">4+</label></div>  
					{else}
					<div class="ch_small"><input class="checkbox" type="checkbox" name="room_count[]" value="1" /> <label class="ch">1</label></div>  
					<div class="ch_small"><input class="checkbox" type="checkbox" name="room_count[]" value="2" /> <label class="ch">2</label></div>  
					<div class="ch_small"><input class="checkbox" type="checkbox" name="room_count[]" value="3" /> <label class="ch">3</label></div>  
					<div class="ch_small"><input class="checkbox" type="checkbox" name="room_count[]" value="4" /> <label class="ch">4+</label></div>  
					{/if}				
				</div>
            </div>
            
            

            <div class="form-actions">
                <input type="submit" value="{$L_GOSEARCH}" class="btn btn-primary btn-large">
            </div><!-- /.form-actions -->
        </form>
    </div><!-- /.content -->
</div><!-- /.property-filter -->