<div class="widget our-agents">
    <div class="title">
        <h2>{$L_OURAGENTS}</h2>
    </div>

    <div class="content">
        {foreach from=$agentslist_items item=agentslist_item}
            <div class="agent">
                <div class="image">
                    {if $agentslist_item.imgfile!=''}
                        <img src="{$estate_folder}/img/data/user/{$agentslist_item.imgfile}">
                    {else}
                        <img src="{$estate_folder}/template/frontend/{$current_theme_name}/img/userplaceholder.png">
                    {/if}
                </div>
                <div class="name"><a href="{$estate_folder}/user{$agentslist_item.user_id}.html">{$agentslist_item.fio}</div>
                <div class="phone">{$agentslist_item.phone}</div>
                <div class="email"><a href="mailto:{$agentslist_item.email}">{$agentslist_item.email}</a></div>
            </div>
        {/foreach}
    </div>
</div>
