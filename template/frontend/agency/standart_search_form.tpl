
		<div class="search_cont">
			{if $mysearch_on==1}
				{if $mysearch_save_form_file ne ''}
				{include file=$mysearch_save_form_file}
				{/if}
			{/if}
			<div id="simple_search"{if isset($smarty.request.extended_search)} style="display:none;"{/if}>
			<form method="get" action="{$estate_folder}/index.php">
				<table border="0" cellspacing="0" cellpadding="0" width="500">
					<tbody>
						<tr>
							<td class="sch" >
							<table border="0" cellpadding="2" cellspacing="0">
							    <tr>
							        <td colspan="4">{$structure_box}</td>
							    </tr>
								<tr>
			
									<td>{$L_PRICE} {$L_FROM}</td>
									<td><div class="select_box_td"><input type="text" class="price_from price_field" name="price_min" value="{if isset($price_min)}{$price_min|number_format:0:'':' '}{else}0{/if}"/></div></td>
									<td>{$L_TO}</td>
			                        <td> <div class="select_box_td"><input type="text" class="price_for price_field" name="price" value="{if isset($price) && $price!=0}{$price|number_format:0:'':' '}{else}{$max_price|number_format:0:'':' '}{/if}"/></div></td>
			
								</tr>
								<tr>
			
									<td colspan="4" class="slider_block"><div class="slider"></div></td>
			
								</tr>
								</table>
                                <a class="search_page_toggle" href="#1">{$L_ADVSEARCH}</a>							
							</td>
				
							<td class="sch" >
							
							<table border="0" cellpadding="2" cellspacing="0" align="right">
			                    {if $country_list ne ''}
			                    <tr>
			                        <td>{$L_COUNTRY}</td><td><div class="select_box_td">{$country_list}</div></td>
			                    </tr>
			                    {/if}
			                    {if $region_list ne ''}
			                    <tr>
			                        <td>{$L_REGION}</td><td><div class="select_box_td">{$region_list}</div></td>
			                    </tr>
			                    {/if}
			                    {if $city_list ne ''}
			                    <tr>
			                        <td>{$L_CITY}</td><td><div class="select_box_td">{$city_list}</div></td>
			                    </tr>
			                    {/if}
			                    {if $district_list ne ''}
			                    <tr>
			                        <td>{$L_DISTRICT}</td><td><div class="select_box_td">{$district_list}</div></td>
			                    </tr>
			                    {/if}
			                    {if $metro_list ne ''}
			                    <tr>
			                        <td>{$L_METRO}</td><td><div class="select_box_td">{$metro_list}</div></td>
			                    </tr>
			                    {/if}
			                    {if $street_list ne ''}
			                    <tr>
			                        <td>{$L_STREET}</td><td><div class="select_box_td">{$street_list}</div></td>
			                    </tr>
			                    {/if}
							</table>
							</td>
							<td class="bts">
							<input type="submit" name="search" value="{$L_GO_FIND}" class="btn btn-primary" />
							</td>
						</tr>
					</tbody>
				</table>
			</form>
			</div>
			<div id="extended_search"{if !isset($smarty.request.extended_search)} style="display:none;"{/if}>
			<form method="get" action="{$estate_folder}/index.php">
				<input type="hidden" name="extended_search" value="1" />
				<table border="0" cellspacing="0" cellpadding="0" width="500">
					<tbody>
						<tr>
							<td class="sch" >
							
								<table border="0" cellpadding="2" cellspacing="0">
								<tr>
								   <td colspan="4">{$structure_box}</td>
								</tr>
								<tr>
									<td>{$L_FLOOR} {$L_FROM}</td>
									<td><div class="select_box_td"><input type="text" name="floor_min" value="{if (isset($smarty.request.floor_min) && $smarty.request.floor_min!=0)}{$smarty.request.floor_min}{/if}" /></div></td>
									<td>{$L_TO}</td>
									<td><div class="select_box_td"><input type="text" name="floor_max" value="{if (isset($smarty.request.floor_max) && $smarty.request.floor_max!=0)}{$smarty.request.floor_max}{/if}" /></div></td>
								</tr>
								<tr>
									<td>{$L_FLOORS} {$L_FROM}</td>
									<td><div class="select_box_td"><input type="text" name="floor_count_min" value="{if (isset($smarty.request.floor_count_min) && $smarty.request.floor_count_min!=0)}{$smarty.request.floor_count_min}{/if}" /></div></td>
									<td>{$L_TO}</td>
									<td><div class="select_box_td"><input type="text" name="floor_count_max" value="{if (isset($smarty.request.floor_count_max) && $smarty.request.floor_count_max!=0)}{$smarty.request.floor_count_max}{/if}" /></div></td>
								</tr>
								<tr>
									<td>{$L_SQUARE_SHORT} {$L_FROM}</td>
									<td><div class="select_box_td"><input type="text" name="square_min" value="{if (isset($smarty.request.square_min) && $smarty.request.square_min!=0)}{$smarty.request.square_min}{/if}" /></div></td>
									<td>{$L_TO}</td>
			                        <td><div class="select_box_td"><input type="text" name="square_max" value="{if (isset($smarty.request.square_max) && $smarty.request.square_max!=0)}{$smarty.request.square_max}{/if}" /></div></td>						
								</tr>
								<tr>
									<td>{$L_PRICE} {$L_FROM}</td>
									<td><div class="select_box_td"><input type="text" class="price_from price_field" name="price_min" value="{if isset($price_min)}{$price_min|number_format:0:'':' '}{else}0{/if}"/></div></td>
			                        <td>{$L_TO}</td>
									<td><div class="select_box_td"><input type="text" class="price_for price_field" name="price" value="{if isset($price) && $price!=0}{$price|number_format:0:'':' '}{else}{$max_price|number_format:0:'':' '}{/if}"/></div></td>
								</tr>
			
			                    <tr>
			                        <td class="slider_block" colspan="4"><div class="slider"></div></td>
			                    </tr>
			
								</table>
							</td>
				
							<td class="sch" style="vertical-align: top;" align="right">
							
								<table border="0" cellpadding="2" cellspacing="0">
								{if $region_list ne ''}
								<tr>
									<td>{$L_REGION}</td><td><div class="select_box_td">{$region_list}</div></td>
								</tr>
								{/if}
								{if $city_list ne ''}
								<tr>
									<td>{$L_CITY}</td><td><div class="select_box_td">{$city_list}</div></td>
								</tr>
								{/if}
								{if $district_list ne ''}
								<tr>
									<td>{$L_DISTRICT}</td><td><div class="select_box_td">{$district_list}</div></td>
								</tr>
								{/if}
			                    {if $metro_list ne ''}
								<tr>
									<td>{$L_METRO}</td><td><div class="select_box_td">{$metro_list}</div></td>
								</tr>
								{/if}
			                    {if $street_list ne ''}
								<tr>
									<td>{$L_STREET}</td><td><div class="select_box_td">{$street_list}</div></td>
								</tr>
								{/if}
								<tr>
									<td>{$L_ROOMS1}</td>
									<td> 
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
									<input type="checkbox" name="room_count[]" value="1" /> 1 
									<input type="checkbox" name="room_count[]" value="2" /> 2 
									<input type="checkbox" name="room_count[]" value="3" /> 3 
									<input type="checkbox" name="room_count[]" value="4" /> 4+ 
									{/if}
									</td>
								</tr>
								<tr>
									<td>{$L_FURNITURE}</td><td><div class="select_box_td"><input type="checkbox" name="is_furniture"{if isset($smarty.request.is_furniture)} checked="checked"{/if} value="1" /></div></td>
                                </tr>
                                <tr>
                                    <td>{$L_PHONE}</td><td><div class="select_box_td"><input type="checkbox" name="is_phone"{if isset($smarty.request.is_phone)} checked="checked"{/if} value="1" /></div></td>
                                </tr>
                                <tr>
                                    <td>{$L_HASPHOTO}</td><td><div class="select_box_td"><input type="checkbox" name="has_photo"{if isset($smarty.request.has_photo)} checked="checked"{/if} value="1" /></div></td>
                                </tr>                                   
								</table>
							</td>
						</tr>
						
						<tr>
							<td class="sch"><a class="search_page_toggle" href="#">{$L_TURNOFF}</a></td>
							<td class="sch_button"><input type="submit" name="search" value="{$L_GO_FIND}"  class="btn btn-primary" /></td>
						</tr>
					</tbody>
				</table>
			</form>
			</div>
			</div>