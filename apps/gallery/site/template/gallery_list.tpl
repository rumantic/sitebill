<link rel="stylesheet" type="text/css" href="{$estate_folder}/apps/gallery/site/template/style.css" />

<!-- <div id="breadcrumbs">{$breadcrumbs}</div> -->
<h1>{$title}</h1>
<div id="gallerys">
	{section name=i loop=$gallery_list}
	<div class="item">
		<div class="title"><a href="{$estate_folder}/gallery_photo{$gallery_list[i].gallery_id}/">{$gallery_list[i].title}</a></div>
		<div class="image"><a href="{$estate_folder}/gallery_photo{$gallery_list[i].gallery_id}/"><img src="{$estate_folder}/img/data/{$gallery_list[i].image.preview}" width="206" border="0" alt=""></a></div>
	</div>
	{/section}
</div>