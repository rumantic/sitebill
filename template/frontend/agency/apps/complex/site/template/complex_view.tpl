<script type="text/javascript" src="{$estate_folder}/template/frontend/{$current_theme_name}/js/gallery.js"></script>
<script src="{$estate_folder}/apps/system/js/realtymap.js" type="text/javascript"></script>

<script>
var map_type='{$map_type}';
</script>

{if $complex.geo.value.lat!='' && $complex.geo.value.lng!=''}
<script>
var mar={};
mar.lat={$complex.geo.value.lat};
mar.lng={$complex.geo.value.lng};
var markers_array=[];
markers_array.push(mar);
</script>
{/if}
{literal}
<style>
.partnavs {

}
.partnavs li {
display: inline-block;
}
.partnavs li a {
padding: 10px;
display: block;
background-color: #70af1a;
text-decoration: none;
color: white;
}
.partnavs li.active a {
background-color: white;
color: #70af1a;
}
.complex-option {
padding: 4px;
font-size: 14px;
}

.complex-option label {
display: inline-block;
font-weight: bold;
margin-bottom: 5px;
}
.complex-option span {
display: inline-block;
margin-left: 10px;
}
.is_developer_price {
margin: 10px auto;
text-align: center;
background-color: white;
padding: 10px;
color: Red;
font-weight: bold;
}


.zakon {
float: right;
font-size: 18px;
color: White;
font-weight: 700;
}
.customlink {
background-color: #06a7ea;
color: white;
display: block;
padding: 10px;
text-align: center;
margin: 5px auto;
width: 220px;
}
.complex-option-block {
margin: 10px 0 20px 0;
}
.customlink:hover {
color: white;
}
a.wrp {
width: 300px;
height: 200px;
display: inline-block;
overflow: hidden;
border: 1px solid silver;
padding: 2px;
}


.address_line {
line-height: 140%;
margin: 10px 0;
font-family: PT Sans Narrow;
font-size: 160%;
font-weight: 700;
color: #70af1a;
}
#tab-plannings .thumbnails .pldesc {
width: 100%;
color: black;
text-align: center;
margin-top: 5px;
}
.complex-view .p-property .p-imgs {
width: 100%;
}
.complex-view .p-property .p-imgs .rg-thumbs {
width: 100%;
}
.complex-view .p-property .p-det {
width: 49%;
margin-left: 5%;
}
.complex-opts-pretty {
float: left;
width: 100%;
color: white;
font-size: 120%;
border: 0;
padding: 0;
margin-top: 20px;
}
.complex-opts-pretty-item {
margin: 5px 0;
/* width: 48%; */
/* float: left; */
}
.shaded {
background-color: rgba(210, 213, 218, 0.43);
}
</style>
<script>
	$(document).ready(function(){
		$('.tabb').not(':first').hide();
		$('.complex-option:odd').addClass('shaded');
		$('.partnavs a').click(function(e){
			e.preventDefault();
			$('.tabb').hide();
			var id=$(this).attr('href');
			$('#'+id).show();
		});
		
		
		
		
		$("a.gal1").lightBox();
		
		var RM=new RealtyMap();
		var marker_2=estate_folder + '/template/frontend/agency/img/mapmarker_tealhard.png';
		RM.initSimpleMap('property-map', map_type, markers_array, {defaultZoom: 14, marker_icon: marker_2});
	});
 

</script>
{/literal}



<h1>{$title}</h1>

<div class="row-fluid">
	<div class="span4">
		{if $complex.image.type=='uploads'}
			{if $complex.image.value|count>0}
			{section name=j loop=$complex.image.value}
			<a href="{$estate_folder}/img/data/{$complex.image.value[j].normal}" title="<a target='_blank' href='{$estate_folder}/img/data/{$complex.image.value[j].normal}'>Download</a>"><img src="{$estate_folder}/img/data/{$complex.image.value[j].preview}" /></a>
			{/section}
			{/if}
		{else}
			{if $complex.image.image_array|count>0}
			{section name=j loop=$complex.image.image_array}
			<a href="{$estate_folder}/img/data/{$complex.image.image_array[j].normal}" title="<a target='_blank' href='{$estate_folder}/img/data/{$complex.image.image_array[j].normal}'>Download</a>"><img src="{$estate_folder}/img/data/{$complex.image.image_array[j].preview}" /></a>
			{/section}
			{/if}
		{/if}
	</div>
	<div class="span4">
		<h3>{if 0!=(int)$complex.price_pm_from.value}{$complex.price_pm_from.value|number_format:0:",":" "}/м2{else}--{/if}</h3>
		<div class="det-det clearfix">
			
			
			
			
		
			
			
			<div class="complex-option-block">
			{if $complex.district_id.value!=''}
			<div class="complex-option"><label>{$complex.district_id.title}</label><span>{$complex.district_id.value_string}</span></div>
			{/if}
			
			
			{if $complex.street_id.value!=''}
			<div class="complex-option"><label>{$complex.street_id.title}</label><span>{$complex.street_id.value_string}</span></div>
			{/if}
			
			{if $complex.lexx.value!=''}
			<div class="complex-option"><label>{$complex.lexx.title}</label><span>{$complex.lexx.value}</span></div>
			{/if}
			{if $complex.tip_construct.value!=''}
			<div class="complex-option"><label>{$complex.tip_construct.title}:</label><span>{$complex.tip_construct.value}</span></div>
			{/if}
			{if $complex.floor_count.value!=''}
			<div class="complex-option"><label>{$complex.floor_count.title}:</label><span>{$complex.floor_count.value}</span></div>
			{/if}
			{if $complex.deadline.value!=''}
			<div class="complex-option"><label>{$complex.deadline.title}:</label><span>{if $complex.is_ready.value==1}Сдан: {/if}{$complex.deadline.value}</span></div>
			{/if}
			{if $complex.otdelka.value!=''}
			<div class="complex-option"><label>{$complex.otdelka.title}:</label><span>{$complex.otdelka.value}</span></div>
			{/if}
			
			{if $complex.sales.value!=''}
			<div class="complex-option"><label>Отдел продаж:</label><span>{$complex.sales.value} {$complex.salesname.value} </span></div>
			{/if}
			
			
			
			
			
			{if $complex.building_type.value!=''}
			<div class="complex-option"><label>Тип дома:</label><span>{$complex.building_type.value}</span></div>
			{/if}
			{if $complex.sea_dist.value!='' && $complex.sea_dist.value!='0'}
			<div class="complex-option"><label>До моря:</label><span>{$complex.sea_dist.value} м</span></div>
			{/if}
			
			
			{if $complex.windows.value!='' && $complex.windows.value!='0'}
			<div class="complex-option"><label>{$complex.windows.title}:</label><span>{$complex.windows.value}</span></div>
			{/if}
			
			
			{if $complex.ceil_height.value!='' && $complex.ceil_height.value!='0'}
			<div class="complex-option"><label>{$complex.ceil_height.title}:</label><span>{$complex.ceil_height.value}</span></div>
			{/if}
			{if $complex.glassing.value!='' && $complex.glassing.value!='0'}
			<div class="complex-option"><label>{$complex.glassing.title}:</label><span>{$complex.glassing.value}</span></div>
			{/if}
			
			{if $complex.doors.value!='' && $complex.doors.value!='0'}
			<div class="complex-option"><label>{$complex.doors.title}:</label><span>{$complex.doors.value}</span></div>
			{/if}
			{if $complex.heating.value!='' && $complex.heating.value!='0'}
			<div class="complex-option"><label>{$complex.heating.title}:</label><span>{$complex.heating.value}</span></div>
			{/if}
			{if $complex.electricity.value!='' && $complex.electricity.value!='0'}
			<div class="complex-option"><label>{$complex.electricity.title}:</label><span>{$complex.electricity.value}</span></div>
			{/if}
			
			{if $complex.elevators.value!='' && $complex.elevators.value!='0'}
			<div class="complex-option"><label>{$complex.elevators.title}:</label><span>{$complex.elevators.value}</span></div>
			{/if}
			{if $complex.infrastructure.value!='' && $complex.infrastructure.value!='0'}
			<div class="complex-option"><label>{$complex.infrastructure.title}:</label><span>{$complex.infrastructure.value}</span></div>
			{/if}
			
			{if $complex.parking.value!='' && $complex.parking.value!='0'}
			<div class="complex-option"><label>{$complex.parking.title}:</label><span>{$complex.parking.value}</span></div>
			{/if}
			{if $complex.balkons.value!='' && $complex.balkons.value!='0'}
			<div class="complex-option"><label>{$complex.balkons.title}:</label><span>{$complex.balkons.value}</span></div>
			{/if}
			</div>
			
	
		</div>
	</div>
	<div class="span4">
		{if $complex.geo.value.lat!='' && $complex.geo.value.lng!=''}
			<h3>На карте</h3>
			<div id="property-map" style="height: 300px;"></div>
		{/if}
	</div>
</div>

<ul class="nav nav-tabs">
  <li class="active"><a href="#tab-general" data-toggle="tab">О проекте</a></li>
  {if $grid_items|count > 0}<li><a href="#tab-objects" data-toggle="tab">Объекты</a></li>{/if}
  <li><a href="#tab-plannings" data-toggle="tab">Планировки квартир</a></li>
  <li><a href="#tab-sale" data-toggle="tab">Условия покупки</a></li>
  <li><a href="#tab-ipoteca" data-toggle="tab">Ипотека</a></li>
  <li><a href="#tab-prices" data-toggle="tab">Цены и прайсы</a></li>
</ul>
 
<div class="tab-content">
	<div class="tab-pane active" id="tab-general">
		{if isset($complex.description) && $complex.description.value != ''}
		<h3>Описание</h3>
		<div class="property-description">
		  {$complex.description.value}
		</div>
		{/if}
		
		
	</div>
	<div class="tab-pane" id="tab-objects">
		<h3>Объекты</h3>
				 <div class="list-products clearfix">   
				{section name=i loop=$grid_items}
					<div class="product clearfix">
						<div class="l-image float-left">
							{if $grid_items[i].img != '' } 
							<img src="{$estate_folder}/img/data/{$grid_items[i].img[0].preview}">
							{else}
							<img src="{$estate_folder}/template/frontend/pure/img/no_foto_270x200.png">
							{/if}
							<div class="l-image-hover">
							{if $grid_items[i].img != '' }
								<a href="{$estate_folder}/img/data/{$grid_items[i].img[0].normal}" class="l-lupa"></a>
							{/if}
							<a href="{$grid_items[i].href}" class="l-link"><!-- --></a>
							</div>
						</div>
						<div class="l-image-shadow float-left"><!-- --></div>
						<div class="l-description float-left">
							<div class="l-title"><a href="{$grid_items[i].href}">
								{if $grid_items[i].city ne ''} {$grid_items[i].city}{if $grid_items[i].street ne ''}, {$grid_items[i].street}{if $grid_items[i].number ne ''}, {$grid_items[i].number}{/if}{/if}{else} {if $grid_items[i].street ne ''} {$grid_items[i].street}{if $grid_items[i].number ne ''}, {$grid_items[i].number}{/if} {/if} {/if}</a>
							</div>
							<div class="l-city">Тип: <span>{$grid_items[i].type_sh}</span></div>
							<div class="l-desc">{$grid_items[i].text|strip_tags|truncate:100}</div>
							<div class="l-features clearfix">
								{if $grid_items[i].room_count ne '' && $grid_items[i].room_count ne '0'}<div class="l-bedrooms">Комнат: {$grid_items[i].room_count}</div>{/if}
								{if $grid_items[i].square_all ne '' && $grid_items[i].square_all ne '0'}<div class="l-area">{$grid_items[i].square_all} м<sup>2</sup></div>{/if}
								<div class="l-type">{$grid_items[i].optype}</div>
							</div>
						</div>
						<div class="l-details float-left">
							<div class="l-price">{$grid_items[i].price|number_format:0:",":" "} {if $grid_items[i].currency_name != ''}{$grid_items[i].currency_name}{/if}</div>
							<div class="l-view"><a href="{$grid_items[i].href}">{$LT_VIEW_DETAILS}</a></div>
						</div>           
					</div>
				{/section}
				</div>
	</div>
	<div class="tab-pane" id="tab-plannings">
		{if $complex.plan_flat.value|count>0}
				<ul class="thumbnails">
				{foreach from=$complex.plan_flat.value item=presitem}
					<li class="span2">
					<a class="gal1 thumbnail" href="{$estate_folder}/img/data/{$presitem.normal}">
					<img src="{$estate_folder}/img/data/{$presitem.preview}" alt="{$presitem.title}">
					</a>
					<div class="pldesc">{$presitem.title}</div>
					</li>
				{/foreach}
				</ul>
				{/if}
				{if $complex.planning_info.value!=''}
				<div>
					{$complex.planning_info.value}
				</div>
				{/if}
	</div>
	<div class="tab-pane" id="tab-sale">
		<h3>Покупка</h3>
				{$complex.sale_condition.value}
	</div>
	<div class="tab-pane" id="tab-ipoteca">
		{if isset($complex.ipoteka_desc) && $complex.ipoteka_desc.value != ''}
			<h3>{$complex.ipoteka_desc.title}</h3>
			<div class="property-description">
			{$complex.ipoteka_desc.value}
			</div>
			{/if}
	</div>
	<div class="tab-pane" id="tab-prices">
	{if isset($complex.prices_desc) && $complex.prices_desc.value != ''}
			<h3>{$complex.prices_desc.title}</h3>
			<div class="property-description">
			{$complex.prices_desc.value}
			</div>
			{/if}
			{if $price_matrix|count>0}
			<h3>Варианты</h3>
				<table class="table">
				{foreach from=$price_matrix item=price_matrix_item}
				<tr><td>{$price_matrix_item[0]}</td><td>{$price_matrix_item[1]}</td><td>{$price_matrix_item[2]}</td></tr>
				{/foreach}
				</table>
			{/if}
	</div>
</div>