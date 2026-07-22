<div class="ActiveMapListBlock-items-item">
    <div class="ActiveMapListBlock-item-root-do">
        <a class="ActiveMapListBlock-item-link" target="_blank" href="{$balloon_item['href']}">
            <div class="ActiveMapListBlock-item-image">
                {if $balloon_item['image'] and $balloon_item['image'][0]}
                    <img src="{mediaincpath data=$balloon_item['image'][0] type='preview'}"/>
                {else}
                    <img src="{$estate_folder}/img/no_foto.png"/>
                {/if}
            </div>

            <div class="ActiveMapListBlock-item-description">
                <h3 class="ActiveMapListBlock-item-title">{$balloon_item['type_sh']}</h3>
                <div class="ActiveMapListBlock-item-price">{$balloon_item['price']} {$balloon_item['currency_name']}</div>
                <div class="ActiveMapListBlock-item-address">{$balloon_item['city']}</div>
            </div>
        </a>
    </div>
</div>

