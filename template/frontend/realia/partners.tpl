{if $partners_array and $partners_array['items']|count>0}
<div class="container">
    <div class="partners">
        <h2 class="page-header">{_e t="Наши партнеры"}</h2>
        <div class="content">
            {foreach from=$partners_array['items'] item=item}
                {if is_array($item.image.value) and count($item.image.value) > 0}
                    <div class="partner">
                        <img src="{mediaincpath data=$item.image.value[0] type='preview'}" alt="{$item.name.value}">
                    </div><!-- /.partner -->
                {/if}
            {/foreach}
        </div><!-- /.content -->
    </div>
</div>
{/if}
