<div class="tab-pane" id="t1000">
    <legend>Автоматическое сопоставление</legend>
    {if is_array($propertyDictionaryExtended)}
        {foreach from=$propertyDictionaryExtended key=propertyKey item=propertyItem}
            <fieldset style="border: 3px solid green; padding: 8px; margin-top: 8px;">


                {if isset($propertyItem) && is_array($propertyItem)}

                    <h2>{$propertyItem.title} (<a href="{$propertyItem.href}" target="_blank">{$propertyKey}</a>)</h2>
                    {if $propertyItem.type == 'safe_string'}
                        <div class="mapperitem">
                            <label>Название колонки модели данных (data), из которой поступают значения для выгрузки</label>
                            <input type="text" name="field[{$propertyKey}]" value="{$cassocc[$propertyKey]}">
                        </div>
                        <div class="mapperitem">
                            <label>Значение по-умолчанию</label>
                            {assign var="default_key" value=$propertyKey|cat:'_default'}
                            <input type="text" name="field[{$default_key}]" value="{$cassocc[$default_key]}">
                        </div>

                        {assign var="default_key" value=$propertyKey|cat:'_default'}

                    {else}
                        {if isset($propertyItem.variants) and is_array($propertyItem.variants)}
                            {foreach from=$propertyItem.variants key=Key item=Name}
                                <div class="mapperitem">
                                    <label>{$Name} ({$Key})</label>
                                    {include file=$SITEBILL_DOCUMENT_ROOT|cat:'/apps/system/template/mapper/mapper_yes.tpl' fname='['|cat:$Key|cat:']' setdata=$cassocc[$Key]}
                                </div>
                            {/foreach}
                        {/if}

                        <div class="mapperitem">
                            <label>{$propertyItem.title} - Значение по-умолчанию</label>
                            <select name="field[{$propertyKey}_default]">
                                <option value="">выбрать</option>
                                {assign var="default_key" value=$propertyKey|cat:'_default'}
                                {foreach from=$propertyItem.variants key=Key item=Name}
                                    <option value="{$Key}"{if $cassocc[$default_key] == $Key} selected="selected"{/if}>{$Name}</option>
                                {/foreach}
                            </select>
                        </div>
                    {/if}

                {/if}

            </fieldset>
        {/foreach}
    {/if}
</div>
