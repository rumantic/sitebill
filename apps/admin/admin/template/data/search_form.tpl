<form class="partial form-inline" id="main_partial">
	
	<div class="row-fluid">
		<div class="span3">
			{$topic_list.html}
		</div>
		<div class="span3 checkers">
			{if $country_list != ''}
			{$country_list}
			{/if}
			{if $region_list != ''}
			{$region_list}
			{/if}
			{if $city_list != ''}
			{$city_list}
			{/if}
			
			{if $district_list != ''}
			<div class="dholder"><a href="javascript:void(0);" class="dholder-selected btn">Выбрано районов: 0</a>{$district_list}</div>
			
			{/if}
			{if $street_list != ''}
			{$street_list}
			{/if}
		</div>
		<div class="span6">
			<table class="table">
				
				
				<tr>
					<td>
						Цена
					</td>
					<td colspan="2">
						<input type="text" id="price_slider" data-slider-min="0" data-slider-max="{$extendedSearchFormParams.price.over}" data-slider-tooltip="show" data-slider-value="[{$extendedSearchFormParams.price.min},{$extendedSearchFormParams.price.max}]" />
					</td>
				</tr>
				<tr>
					<td>
						
					</td>
					<td>
						<input type="text" id="price_min" name="price_min" class="input-small">
					</td>
					<td>
						<input type="text" id="price" name="price" class="input-small">
					</td>
				</tr>
				<tr>
					<td>
						Площадь
					</td>
					<td colspan="2">
						<input type="text" id="square_slider" data-slider-min="1" data-slider-max="{$extendedSearchFormParams.max_square.over}" data-slider-tooltip="show" data-slider-value="[{$extendedSearchFormParams.max_square.min},{$extendedSearchFormParams.max_square.max}]" />
					</td>
				</tr>
				<tr>
					<td>
						
					</td>
					<td>
						<input type="text" id="square_min" name="square_min" class="input-small"> 
					</td>
					<td>
						<input type="text" id="square_max" name="square_max" class="input-small">
					</td>
				</tr>
				<tr>
					<td>
						Этаж
					</td>
					<td colspan="2">
						<input type="text" id="floor_slider" data-slider-min="1" data-slider-max="{$extendedSearchFormParams.max_floor.over}" data-slider-tooltip="show" data-slider-value="[{$extendedSearchFormParams.max_floor.min},{$extendedSearchFormParams.max_floor.max}]" />
					</td>
				</tr>
				<tr>
					<td>
						
					</td>
					<td>
						<input type="text" id="floor_min" name="floor_min" class="input-small"> 
					</td>
					<td>
						<input type="text" id="floor_max" name="floor_max" class="input-small">
					</td>
				</tr>
				<tr>
					<td>
						
					</td>
					
					
					<td colspan="2">
						<label class="checkbox">
							<input type="checkbox" name="room_count[]" value="1"> 1
					    </label>
					    <label class="checkbox">
							<input type="checkbox" name="room_count[]" value="2"> 2
					    </label>
					    <label class="checkbox">
							<input type="checkbox" name="room_count[]" value="3"> 3
					    </label>
					    <label class="checkbox">
							<input type="checkbox" name="room_count[]" value="4"> 4
					    </label>
					    <label class="checkbox">
							<input type="checkbox" name="room_count[]" value="5"> 5+
					    </label>
					</td>
				</tr>
				<tr>
					
					<td>
						<label class="checkbox">
							<input type="checkbox" name="has_photo" value="1"> Только с фото
					    </label>
					    <label class="checkbox">
							<input type="checkbox" name="export_cian" value="1"> Выгрузка в ЦИАН
					    </label>
					    <label class="checkbox">
							<input type="checkbox" name="export_afy" value="1"> Выгрузка в AFY
					    </label>
					</td>
					<td>
						{if $mode=='my'}
						<label class="checkbox">
							<input type="checkbox" name="active" value="notactive" /> Только неактивные
					    </label>
					    {/if}
					</td>
				</tr>
			</table>
		</div>
	</div>
	
	
	Количество на страницу <input type="text" name="page_limit" class="input-small">
	
	<button id="runSearch">Обновить поиск</button> <button id="clearSearch">Сбросить все параметры</button>
	
	
	<!-- <input type="checkbox" name="optype" checked="checked" value="{$QUERYPARAMS.optype}" style="display: none;" /> -->
	<input type="checkbox" name="supertopic_id[]" checked="checked" value="{$QUERYPARAMS.supertopic_id}" style="display: none;" />
	<input type="text" name="order" value="{$QUERYPARAMS.order}" style="display: none;" />
	<input type="text" name="asc" value="{$QUERYPARAMS.direction}" style="display: none;" />
	<input type="text" name="page" value="{if $QUERYPARAMS.page ne ''}{$QUERYPARAMS.page}{else}1{/if}" style="display: none;" />
	
	</form>
