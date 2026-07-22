<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <table class="w-full">
        <tr>
            {if $tree != ''}
                <td colspan="2" class="p-4 border-b border-gray-200">{$tabs}</td>
            {else}
                <td class="p-4 border-b border-gray-200">{$tabs}</td>
            {/if}
        </tr>
        <tr>
            {if $tree != ''}
                <td class="align-top w-52 p-4 border-r border-gray-200">{$tree}</td>
            {/if}
            <td class="align-top p-4">{$grid}</td>
        </tr>
    </table>
</div>
