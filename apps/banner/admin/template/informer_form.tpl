<form action="{$estate_folder}/admin/?action=banner&do=informers" method="post">
	<fieldset>
		<legend>Название формы</legend>
		<label>Домен</label>
		<input type="text" name="informer_parameters[domain]" value="{$bi.informer_parameters.domain}">
		<span class="help-block">Домен на котором будет работать информер. Указывается без 'http://', но с 'www.', если оно используется</span>
		
		<label>Код</label>
		<input type="text" name="access_code" value="{$bi.access_code}">
		<span class="help-block">Буквенно цифровой код. Если не будет указан, система сгенерирует его сама</span>
		
		<label>Отображение</label>
		<input type="text" name="informer_parameters[view_type]" value="{$bi.informer_parameters.view_type}">
		<span class="help-block">Варианты: vs (верт. слайдер), hs, hs2 (гор. слайдер)</span>
		
		<label>Количество результатов</label>
		<input type="text" name="informer_parameters[num]" value="{$bi.informer_parameters.num}">
		<span class="help-block">Длинна списка возвращаемого результата</span>
		
		<label class="checkbox">
		<input type="checkbox" name="informer_parameters[cache_on]" value="1"{if $bi.informer_parameters.cache_on==1} checked="checked"{/if}> Кешировать
		</label>
		<span class="help-block">Настройка неиспользуема в данный момент</span>
		
		<label>Время жизни кеша в минутах</label>
		<input type="text" name="informer_parameters[cache_time]" value="{$bi.informer_parameters.cache_time}">
		<span class="help-block">Настройка неиспользуема в данный момент</span>
		
		<label class="checkbox">
		<input type="checkbox" name="is_active" value="1"{if $bi.is_active==1} checked="checked"{/if}> Активный
		</label>
		<span class="help-block">Устанавливает активность информера</span>
		
		
		<label class="checkbox">
		<input type="checkbox" name="informer_parameters[is_priv]" value="1"{if $bi.informer_parameters.is_priv==1} checked="checked"{/if}> Привилегии
		</label>
		<span class="help-block">Настройка неиспользуема в данный момент</span>
		
		<label>Источник выборки</label>
		<input type="text" name="informer_parameters[source]" value="{$bi.informer_parameters.source}">
		<span class="help-block">Варианты: data, complex</span>
		
		<label>Поле фото</label>
		<input type="text" name="informer_parameters[photofield]" value="{$bi.informer_parameters.photofield}">
		<span class="help-block">Системное имя поля с изображением</span>
		
		<label>Формат подписи</label>
		<input type="text" name="informer_parameters[textblock]" value="{$bi.informer_parameters.textblock}">
		<span class="help-block">Формат</span>
		
		<label>Фильтры</label>
		<textarea name="informer_parameters[filters]">{$bi.informer_parameters.filters}</textarea>
		<span class="help-block">Варианты: data, complex</span>
		
		<label class="checkbox">
		<input type="checkbox" name="informer_parameters[autoslide]" value="1"{if $bi.informer_parameters.autoslide==1} checked="checked"{/if}> Автослайдинг
		</label>
		<span class="help-block">Автослайдинг</span>
		
		<label>Видимых элементов</label>
		<input type="text" name="informer_parameters[visels]" value="{$bi.informer_parameters.visels}">
		<span class="help-block">Видимых элементов</span>
		
		<label>Ширина элемента</label>
		<input type="text" name="informer_parameters[ewidth]" value="{$bi.informer_parameters.ewidth}">
		<span class="help-block">Ширина элемента</span>
		
		<label>Высота элемента</label>
		<input type="text" name="informer_parameters[eheight]" value="{$bi.informer_parameters.eheight}">
		<span class="help-block">Высота элемента</span>
		
		<button type="submit" class="btn" name="submit">Отправить</button>
		
		<input type="hidden" name="biid" value="{$bi.biid}">
		{if 0===(int)$bi.biid}
		<input type="hidden" name="subdo" value="new">
		{else}
		<input type="hidden" name="subdo" value="edit">
		{/if}
	</fieldset>
</form>