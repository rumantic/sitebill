{if $api->name == 'user'}
<span
        class="api_call_holder"
        data-api="{$api->name}"
        data-primary-key="user_id"
        data-primary-key-value="{$model_item['user_id']['value']}"
        data-method="{$api->method}"
        data-params="{$api->params}">
</span>
{/if}
