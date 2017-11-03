{literal}
<style>
.condcol-ex {
	display: none;
}
.condline-ex {
	display: none;
}
.condline {
    border: 1px solid #eee;
    padding: 10px;
    margin-bottom: 10px;
}
.mapperitem {
    border-bottom: 1px dashed #7D7D7D;
    margin-bottom: 10px;
    padding: 10px 0 10px 0;
}
section {
	padding: 10px 20px;
    margin-bottom: 20px;
	background-color: rgb(241, 241, 241);
}
</style>
<script>
$(document).ready(function(e){
	$(document).on('click', '.add_and', function(e){
		var ex=$('.condcol-ex').clone();
		var name='field'+$(this).attr('data-fname');
		var line=$(this).attr('data-lc');
		var col=parseInt($(this).attr('data-cc'));
		$(this).attr('data-cc', col+1);
		name=name+'['+line+']['+col+']';
		ex.find('.f0').attr('name', name+'[0]').removeClass('f0');
		ex.find('.f1').attr('name', name+'[1]').removeClass('f1');
		ex.find('.f2').attr('name', name+'[2]').removeClass('f2');
		ex.removeClass('condcol-ex');
		ex.insertBefore($(this));
		e.preventDefault();
	});
	$(document).on('click', '.add_or', function(e){
		var ex=$('.condline-ex').clone();
		var name=$(this).attr('data-fname');
		ex.find('.add_and').attr('data-fname', name);
		name='field'+name;
		var line=parseInt($(this).attr('data-lc'), 10);
		var col=0;
		$(this).attr('data-lc', line+1);
		name=name+'['+line+']['+col+']';
		
		ex.find('.add_and').attr('data-lc', line);
		//ex.find('.add_and').attr('data-lc', line);
		ex.find('.f0').attr('name', name+'[0]').removeClass('f0');
		ex.find('.f1').attr('name', name+'[1]').removeClass('f1');
		ex.find('.f2').attr('name', name+'[2]').removeClass('f2');
		ex.removeClass('condline-ex');
		ex.insertBefore($(this));
		e.preventDefault();
	});
	$(document).on('click', '.condcol .rem', function(e){
		e.preventDefault();
		$(this).parents('.condcol').eq(0).remove();
	});
	//$('section:even').css({'background-color': '#f5f3f3'});
});
</script>
{/literal}

<div class="condcol condcol-ex">
	<input type="text" class="f0" name="" value="" />
	<select class="f1" name="">
		{foreach from=$condops item=condop key=condopkey}
			<option value="{$condopkey}">{$condop}</option>
		{/foreach}
	</select>
	<input class="f2" type="text" name="" value="" />
	<a href="#" class="rem btn btn-danger btn-mini"><i class="icon-white icon-remove"></i></a>
</div>

<div class="condline condline-ex">
	<div class="condcol">
		<input type="text" class="f0" name="" value="" />
		<select class="f1" name="">
			{foreach from=$condops item=condop key=condopkey}
				<option value="{$condopkey}">{$condop}</option>
			{/foreach}
		</select>
		<input class="f2" type="text" name="" value="" />
		<a href="#" class="rem btn btn-danger btn-mini"><i class="icon-white icon-remove"></i></a>
	</div>
	<a href="#" class="add_and" data-fname="" data-lc="0" data-cc="1">Добавить И условие</a>
</div>








<form action="{$estate_folder}/admin/" method="post">
	<input type="hidden" name="action" value="yandexrealty" />
	<input type="hidden" name="do" value="mapper" />
	
	<fieldset>
		<section id="s_managername">
			<legend>EST.UA типы</legend>
			
			<div class="mapperitem">
				<label>Тип "автомойка"</label>
				{include file=$mapper_yes_tpl fname='[t_carwash]' setdata=$cassocc.t_carwash}
			</div>
			
			<div class="mapperitem">
				<label>Тип "автосервис"</label>
				{include file=$mapper_yes_tpl fname='[t_carservice]' setdata=$cassocc.t_carservice}
			</div>
			
			<div class="mapperitem">
				<label>Тип "апартамент"</label>
				{include file=$mapper_yes_tpl fname='[t_apartment]' setdata=$cassocc.t_apartment}
			</div>
			
			<div class="mapperitem">
				<label>Тип "аптека"</label>
				{include file=$mapper_yes_tpl fname='[t_pharmacy]' setdata=$cassocc.t_pharmacy}
			</div>
			
			<div class="mapperitem">
				<label>Тип "база отдыха"</label>
				{include file=$mapper_yes_tpl fname='[t_recreationcenter]' setdata=$cassocc.t_recreationcenter}
			</div>
			
			<div class="mapperitem">
				<label>Тип "баня"</label>
				{include file=$mapper_yes_tpl fname='[t_bathhouse]' setdata=$cassocc.t_bathhouse}
			</div>
			
			<div class="mapperitem">
				<label>Тип "бизнес центр"</label>
				{include file=$mapper_yes_tpl fname='[t_bussinesscenter]' setdata=$cassocc.t_bussinesscenter}
			</div>
			<div class="mapperitem">
				<label>Тип "бильярдный клуб"</label>
				{include file=$mapper_yes_tpl fname='[t_billiard]' setdata=$cassocc.t_billiard}
			</div>
			<div class="mapperitem">
				<label>Тип "гараж"</label>
				{include file=$mapper_yes_tpl fname='[t_garage]' setdata=$cassocc.t_garage}
			</div>
			<div class="mapperitem">
				<label>Тип "гостиница"</label>
				{include file=$mapper_yes_tpl fname='[t_gostinitsa]' setdata=$cassocc.t_gostinitsa}
			</div>

			<div class="mapperitem">
				<label>Тип "дача"</label>
				{include file=$mapper_yes_tpl fname='[t_countryhouse]' setdata=$cassocc.t_countryhouse}
			</div>
			<div class="mapperitem">
				<label>Тип "дом"</label>
				{include file=$mapper_yes_tpl fname='[t_house]' setdata=$cassocc.t_house}
			</div>
			<div class="mapperitem">
				<label>Тип "заправка"</label>
				{include file=$mapper_yes_tpl fname='[t_gasstation]' setdata=$cassocc.t_gasstation}
			</div>
			<div class="mapperitem">
				<label>Тип "здание"</label>
				{include file=$mapper_yes_tpl fname='[t_building]' setdata=$cassocc.t_building}
			</div>
			<div class="mapperitem">
				<label>Тип "кафе"</label>
				{include file=$mapper_yes_tpl fname='[t_cafe]' setdata=$cassocc.t_cafe}
			</div>
			<div class="mapperitem">
				<label>Тип "квартира"</label>
				{include file=$mapper_yes_tpl fname='[t_flat]' setdata=$cassocc.t_flat}
			</div>
			
			
			<div class="mapperitem">
				<label>Тип "клиника"</label>
				{include file=$mapper_yes_tpl fname='[t_clinic]' setdata=$cassocc.t_clinic}
			</div>
<div class="mapperitem">
				<label>Тип "комната"</label>
				{include file=$mapper_yes_tpl fname='[t_room]' setdata=$cassocc.t_room}
			</div>
<div class="mapperitem">
				<label>Тип "магазин"</label>
				{include file=$mapper_yes_tpl fname='[t_shop]' setdata=$cassocc.t_shop}
			</div>
<div class="mapperitem">
				<label>Тип "мед. кабинет"</label>
				{include file=$mapper_yes_tpl fname='[t_medcabinet]' setdata=$cassocc.t_medcabinet}
			</div>
<div class="mapperitem">
				<label>Тип "нефтебаза"</label>
				{include file=$mapper_yes_tpl fname='[t_oilbase]' setdata=$cassocc.t_oilbase}
			</div>
<div class="mapperitem">
				<label>Тип "ночной клуб"</label>
				{include file=$mapper_yes_tpl fname='[t_nightclub]' setdata=$cassocc.t_nightclub}
			</div>
<div class="mapperitem">
				<label>Тип "общепит"</label>
				{include file=$mapper_yes_tpl fname='[t_catering]' setdata=$cassocc.t_catering}
			</div>
<div class="mapperitem">
				<label>Тип "отель"</label>
				{include file=$mapper_yes_tpl fname='[t_hotel]' setdata=$cassocc.t_hotel}
			</div>
<div class="mapperitem">
				<label>Тип "офис"</label>
				{include file=$mapper_yes_tpl fname='[t_ofice]' setdata=$cassocc.t_ofice}
			</div>
			
			
			<div class="mapperitem">
				<label>Тип "пансионат"</label>
				{include file=$mapper_yes_tpl fname='[t_pansion]' setdata=$cassocc.t_pansion}
			</div>
<div class="mapperitem">
				<label>Тип "парикмахерская"</label>
				{include file=$mapper_yes_tpl fname='[t_freseur]' setdata=$cassocc.t_freseur}
			</div>
<div class="mapperitem">
				<label>Тип "паркинг"</label>
				{include file=$mapper_yes_tpl fname='[t_parking]' setdata=$cassocc.t_parking}
			</div>
<div class="mapperitem">
				<label>Тип "парковка"</label>
				{include file=$mapper_yes_tpl fname='[t_parkovka]' setdata=$cassocc.t_parkovka}
			</div>
<div class="mapperitem">
				<label>Тип "паркоместо"</label>
				{include file=$mapper_yes_tpl fname='[t_parkingplace]' setdata=$cassocc.t_parkingplace}
			</div>
<div class="mapperitem">
				<label>Тип "помещение свободного назначения"</label>
				{include file=$mapper_yes_tpl fname='[t_freeuse]' setdata=$cassocc.t_freeuse}
			</div>
<div class="mapperitem">
				<label>Тип "производство и промышленность"</label>
				{include file=$mapper_yes_tpl fname='[t_manufacturing]' setdata=$cassocc.t_manufacturing}
			</div>
<div class="mapperitem">
				<label>Тип "ресторан"</label>
				{include file=$mapper_yes_tpl fname='[t_restaurant]' setdata=$cassocc.t_restaurant}
			</div>
<div class="mapperitem">
				<label>Тип "салон красоты"</label>
				{include file=$mapper_yes_tpl fname='[t_beautysalon]' setdata=$cassocc.t_beautysalon}
			</div>
<div class="mapperitem">
				<label>Тип "санаторий"</label>
				{include file=$mapper_yes_tpl fname='[t_sanatorium]' setdata=$cassocc.t_sanatorium}
			</div>
<div class="mapperitem">
				<label>Тип "сауна"</label>
				{include file=$mapper_yes_tpl fname='[t_sauna]' setdata=$cassocc.t_sauna}
			</div>
<div class="mapperitem">
				<label>Тип "склад"</label>
				{include file=$mapper_yes_tpl fname='[t_warehouse]' setdata=$cassocc.t_warehouse}
			</div>
			
			
			<div class="mapperitem">
				<label>Тип "спортзал"</label>
				{include file=$mapper_yes_tpl fname='[t_sporthall]' setdata=$cassocc.t_sporthall}
			</div>
<div class="mapperitem">
				<label>Тип "стоянка"</label>
				{include file=$mapper_yes_tpl fname='[t_stoyanka]' setdata=$cassocc.t_stoyanka}
			</div>
<div class="mapperitem">
				<label>Тип "СТО"</label>
				{include file=$mapper_yes_tpl fname='[t_sto]' setdata=$cassocc.t_sto}
			</div>
<div class="mapperitem">
				<label>Тип "таунхаус"</label>
				{include file=$mapper_yes_tpl fname='[t_townhouse]' setdata=$cassocc.t_townhouse}
			</div>
<div class="mapperitem">
				<label>Тип "торговый центр"</label>
				{include file=$mapper_yes_tpl fname='[t_mall]' setdata=$cassocc.t_mall}
			</div>
<div class="mapperitem">
				<label>Тип "участок для объектов отдыха и здоровья"</label>
				{include file=$mapper_yes_tpl fname='[t_lot_recr]' setdata=$cassocc.t_lot_recr}
			</div>
<div class="mapperitem">
				<label>Тип "участок для сельского хозяйства"</label>
				{include file=$mapper_yes_tpl fname='[t_lot_agro]' setdata=$cassocc.t_lot_agro}
			</div>
<div class="mapperitem">
				<label>Тип "участок для строительства жилья"</label>
				{include file=$mapper_yes_tpl fname='[t_lot_residentbuilding]' setdata=$cassocc.t_lot_residentbuilding}
			</div>
<div class="mapperitem">
				<label>Тип "участок для строительства коммерческих объектов"</label>
				{include file=$mapper_yes_tpl fname='[t_lot_commercialbuilding]' setdata=$cassocc.t_lot_commercialbuilding}
			</div>
<div class="mapperitem">
				<label>Тип "фитнес клуб"</label>
				{include file=$mapper_yes_tpl fname='[t_fitnes]' setdata=$cassocc.t_fitnes}
			</div>
<div class="mapperitem">
				<label>Тип "хостел"</label>
				{include file=$mapper_yes_tpl fname='[t_hostel]' setdata=$cassocc.t_hostel}
			</div>
<div class="mapperitem">
				<label>Тип "шиномонтаж"</label>
				{include file=$mapper_yes_tpl fname='[t_tirefitting]' setdata=$cassocc.t_tirefitting}
			</div>
		
		</section>
		
		<section id="s_managername">
			<legend>MEGET.UA типы</legend>
			
			<div class="mapperitem">
				<label>Тип "комнаты"</label>
				{include file=$mapper_yes_tpl fname='[m_room]' setdata=$cassocc.m_room}
			</div>
			
			<div class="mapperitem">
				<label>Тип "1-ком."</label>
				{include file=$mapper_yes_tpl fname='[m_1r_flat]' setdata=$cassocc.m_1r_flat}
			</div>
			
			<div class="mapperitem">
				<label>Тип "2-ком."</label>
				{include file=$mapper_yes_tpl fname='[m_2r_flat]' setdata=$cassocc.m_2r_flat}
			</div>
			
			<div class="mapperitem">
				<label>Тип "3-ком."</label>
				{include file=$mapper_yes_tpl fname='[m_3r_flat]' setdata=$cassocc.m_3r_flat}
			</div>
			
			<div class="mapperitem">
				<label>Тип "4-ком.+"</label>
				{include file=$mapper_yes_tpl fname='[m_4r_flat]' setdata=$cassocc.m_4r_flat}
			</div>
			
			<div class="mapperitem">
				<label>Тип "Дачи"</label>
				{include file=$mapper_yes_tpl fname='[m_countryhouse]' setdata=$cassocc.m_countryhouse}
			</div>
			
			<div class="mapperitem">
				<label>Тип "койко-местa"</label>
				{include file=$mapper_yes_tpl fname='[m_koykomesto]' setdata=$cassocc.m_koykomesto}
			</div>
			<div class="mapperitem">
				<label>Тип "Таунхаус"</label>
				{include file=$mapper_yes_tpl fname='[m_townhouse]' setdata=$cassocc.m_townhouse}
			</div>
			<div class="mapperitem">
				<label>Тип "дуплекс"</label>
				{include file=$mapper_yes_tpl fname='[m_duplex]' setdata=$cassocc.m_duplex}
			</div>
			<div class="mapperitem">
				<label>Тип "Квартиры под офис"</label>
				{include file=$mapper_yes_tpl fname='[m_flam_for_office]' setdata=$cassocc.m_flam_for_office}
			</div>

			<div class="mapperitem">
				<label>Тип "Часть дома, полдома"</label>
				{include file=$mapper_yes_tpl fname='[m_housepart]' setdata=$cassocc.m_housepart}
			</div>
			<div class="mapperitem">
				<label>Тип "Дома"</label>
				{include file=$mapper_yes_tpl fname='[m_house]' setdata=$cassocc.m_house}
			</div>
			<div class="mapperitem">
				<label>Тип "коттеджи"</label>
				{include file=$mapper_yes_tpl fname='[m_cottage]' setdata=$cassocc.m_cottage}
			</div>
			<div class="mapperitem">
				<label>Тип "земля под застройку"</label>
				{include file=$mapper_yes_tpl fname='[m_land_constr]' setdata=$cassocc.m_land_constr}
			</div>
			<div class="mapperitem">
				<label>Тип "дачные участки"</label>
				{include file=$mapper_yes_tpl fname='[m_country_yard]' setdata=$cassocc.m_country_yard}
			</div>
			<div class="mapperitem">
				<label>Тип "хостел"</label>
				{include file=$mapper_yes_tpl fname='[m_hostel]' setdata=$cassocc.m_hostel}
			</div>
			
			
			<div class="mapperitem">
				<label>Тип "общежитие"</label>
				{include file=$mapper_yes_tpl fname='[m_dormitory]' setdata=$cassocc.m_dormitory}
			</div>
			<div class="mapperitem">
				<label>Тип "Офисы"</label>
				{include file=$mapper_yes_tpl fname='[m_office]' setdata=$cassocc.m_office}
			</div>
			<div class="mapperitem">
				<label>Тип "земли ОСГ"</label>
				{include file=$mapper_yes_tpl fname='[m_land_osg]' setdata=$cassocc.m_land_osg}
			</div>
			<div class="mapperitem">
				<label>Тип "Магазины"</label>
				{include file=$mapper_yes_tpl fname='[m_shop]' setdata=$cassocc.m_shop}
			</div>
			<div class="mapperitem">
				<label>Тип "коммерческие земли"</label>
				{include file=$mapper_yes_tpl fname='[m_land_commercial]' setdata=$cassocc.m_land_commercial}
			</div>
			<div class="mapperitem">
				<label>Тип "Особняки"</label>
				{include file=$mapper_yes_tpl fname='[m_mansion]' setdata=$cassocc.m_mansion}
			</div>
			<div class="mapperitem">
				<label>Тип "Рестораны/кафе"</label>
				{include file=$mapper_yes_tpl fname='[m_restauranm_cafe]' setdata=$cassocc.m_restauranm_cafe}
			</div>
			<div class="mapperitem">
				<label>Тип "Салоны"</label>
				{include file=$mapper_yes_tpl fname='[m_salun]' setdata=$cassocc.m_salun}
			</div>
			<div class="mapperitem">
				<label>Тип "Здания"</label>
				{include file=$mapper_yes_tpl fname='[m_building]' setdata=$cassocc.m_building}
			</div>
			
			
			<div class="mapperitem">
				<label>Тип "Склады"</label>
				{include file=$mapper_yes_tpl fname='[m_warehouse]' setdata=$cassocc.m_warehouse}
			</div>
			<div class="mapperitem">
				<label>Тип "Имущ.компл"</label>
				{include file=$mapper_yes_tpl fname='[m_imcompl]' setdata=$cassocc.m_imcompl}
			</div>
			<div class="mapperitem">
				<label>Тип "Гаражи"</label>
				{include file=$mapper_yes_tpl fname='[m_garage]' setdata=$cassocc.m_garage}
			</div>
			<div class="mapperitem">
				<label>Тип "МАФ"</label>
				{include file=$mapper_yes_tpl fname='[m_maf]' setdata=$cassocc.m_maf}
			</div>
			<div class="mapperitem">
				<label>Тип "Прочее"</label>
				{include file=$mapper_yes_tpl fname='[m_other]' setdata=$cassocc.m_other}
			</div>

		
		</section>
	
		
		<button type="submit" class="btn" name="submit">Отправить</button>
	</fieldset>
	
</form>