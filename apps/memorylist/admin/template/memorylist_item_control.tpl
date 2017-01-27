<div class="memorylist_controls memorylist_controls_{$id}" alt="">

{if isset($items_in_memory[$id])}
	{foreach from=$items_in_memory[$id] item=item_memorylist key=itemid}
		<a href="#" data-listid="{$item_memorylist.memorylist_id}" data-itemid="{$id}" class="remove_from_memory_list btn btn-mini">{$item_memorylist.title} <i class="icon icon-remove"></i></a><br />
	{/foreach}
{/if}
<a href="#" class="add_to_memory_list btn btn-mini" data-itemid="{$id}"><i class="icon icon-plus"></i> Добавить в список</a>
</div>

