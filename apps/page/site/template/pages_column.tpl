{if $PagesColumnRecords|count>0}
<div class="PagesColumnRecords">
	<h5 class="PagesColumnRecords-title"><a href="{$estate_folder}/blog/">Блог</a></h5>

	<ul class="nav nav-pills nav-stacked">
		{section name=i loop=$PagesColumnRecords}
			<li><a href="{$PagesColumnRecords[i].href}">{$PagesColumnRecords[i].title}</a></li>
		{/section}
	</ul>
</div>
{/if}