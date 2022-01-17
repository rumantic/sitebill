<script src="{$estate_folder}/apps/system/js/realtymap.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="{$estate_folder}/css/jquery.lightbox-0.5.css" media="screen">
<script type="text/javascript" src="{$estate_folder}/js/jquery.lightbox-0.5.js"></script>
<script>var map_type='{$map_type}';</script>
<script>
var markers_array=[];
</script>
{if $complex.geo.value.lat!='' && $complex.geo.value.lng!=''}
<script>
var mar={};
mar.lat={$complex.geo.value.lat};
mar.lng={$complex.geo.value.lng};

markers_array.push(mar);
</script>
{/if}
{literal}
<style>

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
.complex-option-block {
margin: 10px 0 20px 0;
}

#tab-plannings .thumbnails .pldesc {
width: 100%;
color: black;
text-align: center;
margin-top: 5px;
}

.shaded {
background-color: rgba(210, 213, 218, 0.43);
}
#property-map img {
max-width: none;
}
</style>
<script>
	$(document).ready(function(){
		$('.tabb').not(':first').hide();
		$('.complex-option:odd').addClass('shaded');
		$('.lbgallery').lightBox();
		$('.plgal').lightBox();
		if(markers_array.length>0){
			var RM=new RealtyMap();
			var marker_2=estate_folder + '/template/frontend/agency/img/mapmarker_tealhard.png';
			RM.initSimpleMap('property-map', map_type, markers_array, {defaultZoom: 14, marker_icon: marker_2, marker_size: [40,40]});
		}


		//$("#complexobjs").tablesorter();

		if($('.carousel.property  .content ul').length>0){
			$('.carousel.property  .content ul').carouFredSel({
				scroll: {
					items: 1
				},
				auto: false,
				next: {
					button: '.carousel.property  .content .carousel-next',
					key: 'right'
				},
				prev: {
					button: '.carousel.property  .content .carousel-prev',
					key: 'left'
				}
			});
			 $('.carousel.property .content li img').on({
		            click: function(e) {
		            	e.preventDefault();
		                var src = $(this).parents('a').eq(0).attr('href');
		                $('#imcounter .cur').text($(this).data('nr'));

		                var img = $(this).closest('.carousel.property').find('.preview img');
		                var img_a = img.parents('a').eq(0);
		                img.attr('src', src);
		                img_a.attr('href', src);
		                $('.carousel.property .content li').each(function() {
		                    $(this).removeClass('active');
		                });
		                $(this).closest('li').addClass('active');
		            }
		        });

			$('.carousel.property  ul li:first').addClass('active');
		}
	});


</script>
{/literal}

{if 1==0}{$complex.documents.value|print_r}
{foreach from=$complex.documents.value item=doc}
<a href="{$estate_folder}/img/mediadocs/{$doc.normal}">{if $doc.title ne ''}{$doc.title}{else}{$doc.normal}{/if}</a>
{/foreach}{/if}
<div class="row-fluid">
	<div class="span8">
		{if $complex.image.type=='uploads'}
			{assign var='photo' value=$complex.image.value}
			{if $complex.image.value|count>0}
			{/if}
		{else}
			{if $complex.image.image_array|count>0}
			{assign var='photo' value=$complex.image.image_array}
			{/if}
		{/if}
		{if $photo|count>0}
		<div class="carousel property ">
			<div class="preview">
				<a href="{mediaincpath data=$photo[0] type='normal'}" class="lbgallery" title="Фото" ><img src="{mediaincpath data=$photo[0] type='normal'}" alt=""></a>
			</div>
			{if $photo|count>1}
			<div class="content">
				<a class="carousel-prev" href="#">Previous</a>
				<a class="carousel-next" href="#">Next</a>
				<ul>
				{section name=j loop=$photo}
				<li>
				  <a href="{mediaincpath data=$photo[j] type='normal'}"><img src="{mediaincpath data=$photo[j] type='preview'}" /></a>
				</li>
				{/section}
				</ul>
			</div>
			{/if}
		</div>
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

			</div>


		</div>
	</div>

</div>

{if isset($complexobjs) && $complexobjs|count>0 && 1==0}
<script type="text/javascript" src="{$estate_folder}/template/frontend/{$current_theme_name}/libraries/tablesorter/jquery.tablesorter.js"></script>

<table class="table tablesorter" id="complexobjs">
	<thead>
		<tr>
			{foreach from=$complexobjs_comlumns item=complexobjs_comlumn}
				<th>{$complexobjs_comlumn.t}</th>
			{/foreach}
			<th></th>
		</tr>
	</thead>
	<tbody>
	{foreach from=$complexobjs item=complexobj}
			<tr>
			{foreach from=$complexobjs_comlumns item=complexobjs_comlumn}
				{if $complexobjs_comlumn.n=='price_pm' && intval($complexobj[$complexobjs_comlumn.n].value_string)==0}
					{assign var=pp value=$complexobj.price.value_string}
					{assign var=ps value=$complexobj.square_all.value_string}
					{if $pp!='' && $ps!=''}
					<td>{intval($pp/$ps)}</td>
					{else}
					<td></td>
					{/if}
				{else}
				<td>
				{if $complexobjs_comlumn.ty=='price' && intval($complexobj[$complexobjs_comlumn.n].value)!=0}
					{$complexobj[$complexobjs_comlumn.n].value|number_format:0:",":" "}
				{else}
					{$complexobj[$complexobjs_comlumn.n].value_string}
				{/if}

				</td>
				{/if}
			{/foreach}
			</tr>
		{/foreach}
	</tbody>
</table>

{/if}


<ul class="nav nav-tabs">
  <li class="active"><a href="#tab-general" data-toggle="tab">О проекте</a></li>
  <li><a href="#tab-objects" data-toggle="tab">Объекты</a></li>
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

		{if $apps_mapplic_on==1}
			<div class="row">
				<div class="span12">
					<div class="mapplic-wrapper" data-table="complex" data-key="complex_id" data-field-name="image" data-key-value="{$complex.complex_id.value}"></div>
					{literal}
						<script>
							$(document).ready(function () {
								var table = $('.mapplic-wrapper').attr('data-table');
								var key = $('.mapplic-wrapper').attr('data-key');
								var field_name = $('.mapplic-wrapper').attr('data-field-name');
								var key_value = $('.mapplic-wrapper').attr('data-key-value');
								$('.mapplic-wrapper').load(
										estate_folder + '/apps/api/rest.php?action=mapplic&anonymous=1&do=get_panel&key_value=' + key_value +
										'&table=' + table +
										'&key=' + key +
										'&field_name=' + field_name
								);
							});
						</script>
					{/literal}
				</div>
			</div>
		{/if}



	</div>
	<div class="tab-pane" id="tab-objects">
		<h3>Объекты</h3>
			<div class="properties-rows">
				<div class="row">
			        {section name=i loop=$grid_items}
			        <div class="property span9{if $grid_items[i].bold_status==1} grid_list_bold{/if}{if $grid_items[i].premium_status==1} grid_list_premium{/if}{if $grid_items[i].vip_status==1} grid_list_vip{/if}">
			            <div class="row">
			                <div class="image span3">
			                    <div class="content">
			                        <a href="{$grid_items[i].href}"></a>
			                        {if $grid_items[i].img != '' }
			                        <img src="{mediaincpath data=$grid_items[i].img[0] type='preview'}" class="previewi">
			                        {else}
			                        <img src="{$estate_folder}/template/frontend/realia/img/no_foto_270x200.png" class="previewi">
			                        {/if}
			                    </div><!-- /.content -->
			                </div><!-- /.image -->

			                <div class="body span6">
			                    <div class="title-price row">
			                        <div class="title span4">
			                            <h2>
			                            	{if isset($smarty.session.favorites)}
									            {if in_array($grid_items[i].id,$smarty.session.favorites)}
									                <a class="fav-rem" alt="{$grid_items[i].id}" title="{$L_DELETEFROMFAVORITES}" href="#remove_from_favorites"></a>
									            {else}
									                <a class="fav-add" alt="{$grid_items[i].id}" title="{$L_ADDTOFAVORITES}" href="#add_to_favorites"></a>
									            {/if}
									        {else}
									        	<a class="fav-add" alt="{$grid_items[i].id}" title="{$L_ADDTOFAVORITES}" href="#add_to_favorites"></a>
									        {/if}
			                                <a href="{$grid_items[i].href}">
			                                {if $grid_items[i].city ne ''} {$grid_items[i].city}{if
			                    $grid_items[i].street ne ''}, {$grid_items[i].street}{if
			                    $grid_items[i].number ne ''}, {$grid_items[i].number}{/if}{/if}
			                    {else} {if $grid_items[i].street ne ''} {$grid_items[i].street}{if
			                    $grid_items[i].number ne ''}, {$grid_items[i].number}{/if} {/if}
			                    {/if}
			                                </a>
			                            </h2>
			                        </div><!-- /.title -->
			                        {if $grid_items[i].price_discount > 0}
			                        <div class="price">
			                        {$grid_items[i].price_discount|number_format:0:",":" "} {if $grid_items[i].currency_name != ''}{$grid_items[i].currency_name}{/if}
			                        <div class="price_discount_list">{$grid_items[i].price|number_format:0:",":" "} {if $grid_items[i].currency_name != ''}{$grid_items[i].currency_name}{/if}</div>
			                        </div><!-- /.price -->
			                        {else}
			                        <div class="price">{$grid_items[i].price|number_format:0:",":" "} {if $grid_items[i].currency_name != ''}{$grid_items[i].currency_name}{/if}</div>
			                        {/if}
			                    </div><!-- /.title -->

			                    <div class="location">{if $grid_items[i].topic_info.$lang_topic_name != ''}{$grid_items[i].topic_info.$lang_topic_name}{else}{$grid_items[i].type_sh}{/if}</div><!-- /.location -->
			                    <p>
			                    {if $grid_items[i].$lang_data_text != ''}
			                    {$grid_items[i].$lang_data_text|strip_tags|truncate:200}
			                    {else}
			                    {$grid_items[i].text|strip_tags|truncate:200}
			                    {/if}
			                    </p>
			                    <div class="area">
			                        <span class="key">{$L_SQUARE} м<sup>2</sup>:</span><!-- /.key -->
			                        <span class="value">{$grid_items[i].square_all}/{$grid_items[i].square_live}/{$grid_items[i].square_kitchen}</span><!-- /.value -->
			                    </div><!-- /.area -->
			                    <div class="area">
			                        <span class="key">{$L_FLOOR}:</span><!-- /.key -->
			                        <span class="value">{$grid_items[i].floor}/{$grid_items[i].floor_count}</span><!-- /.value -->
			                    </div><!-- /.area -->
			               </div><!-- /.body -->
			            </div><!-- /.property -->
			        </div><!-- /.row -->
			        {/section}
			    </div>
			</div>

	</div>
	<div class="tab-pane" id="tab-plannings">
		{if $complex.plan_flat.value|count>0}
				<ul class="thumbnails">
				{foreach from=$complex.plan_flat.value item=presitem}
					<li class="span2">
					<a class="plgal" class="thumbnail" href="{mediaincpath data=$presitem type='normal'}">
					<img src="{mediaincpath data=$presitem type='preview'}" alt="{$presitem.title}">
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

{if $complex.geo.value.lat!='' && $complex.geo.value.lng!=''}
<div class="row-fluid">
	<div class="span12">
		<h3>На карте</h3>
		<div id="property-map" style="height: 300px;"></div>
	</div>
</div>
{/if}
