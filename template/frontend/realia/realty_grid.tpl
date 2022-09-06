<script src="{$estate_folder}/apps/system/js/json2.js" type="text/javascript"></script>

<div class="row">
    <div class="span9">


        {if $grid_items|count==0}
            <h1 class="page-header">{_e t="Ничего не удалось найти"}</h1>
        {else}
            <h1 class="page-header">{$title}</h1>

            {if $smarty.request.page == 1 or $smarty.request.page == '' }
                <span itemprop="description">{$description}</span>
            {/if}


            {assign var="lang_topic_name" value="name_{$smarty.session._lang}"}

            <div id="map" style="margin: 10px 0;">
                <iframe src="{$estate_folder}/js/ajax.php?action=iframe_map&{$QUERY_STRING}&topic_id={$smarty.request.topic_id}" style="border: 0px;" border="0" width="100%" height="100%"></iframe>
            </div>

            {if $smarty.session.grid_type eq 'thumbs'}
                {include file='realty_grid_thumbs.tpl'}
            {else}
                {include file='realty_grid_list.tpl'}
            {/if}

            {$pager}

        {/if}
    </div>

    <div class="sidebar span3">
        {include file='search_form.tpl'}
        <br/>
        {include file='right_special.tpl'}

    </div>
</div>
