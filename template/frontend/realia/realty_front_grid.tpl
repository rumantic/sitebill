realty_front_grid.tpl
{*$grid_header|print_r*}
<table class="table">
    <thead>
        <tr>
            {foreach from=$grid_header item=head_item}
                <th>{$head_item.title}{if $head_item.sortable==1} <a href="{$url}&order={$head_item.name}&asc=asc">U</a> <a href="{$url}&order={$head_item.name}&asc=desc">D</a>{/if}</th>
                {/foreach}

        </tr>
    </thead>
    {foreach from=$grid_items item=grid_item}
        <tr>
            {foreach from=$grid_header item=head_item}
                <td>
                    {if $head_item.linked==1}
                        <a href="{$grid_item.href}">{$grid_item[$head_item.name]}</a>
                    {else}
                        {$grid_item[$head_item.name]}
                    {/if}
                </td>
            {/foreach}

        </tr>
    {/foreach}
</table>
{$pager}