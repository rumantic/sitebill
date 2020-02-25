<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="{$_core_folder}/apps/system/js/bootstrap/css/bootstrap.min.css" media="screen">
{literal}
<style>
@page {
  margin: 20px;
}
body {
	font-family: "verdana"; font-size: 12px;
}
.data_grid_item {
  border: 1px solid #eee; width: 100%; margin-bottom: 20px;
}
.data_grid_item td {
  border: 1px solid #eee;
  padding: 5px;
}
.rgallery img {
  width: 100px;

}
</style>
{/literal}
</head>
<body>
{foreach $grid_items item=item key=key}
<table class="data_grid_item">
  <tr>
    <td><strong>{$item.id.value}</strong></td>
    <td>{$item.topic_id.value_string}</td>
  </tr>
  {if is_array($item.image.value)}
  <tr>
    <td colspan="2">
      <div class="rgallery">
          {foreach from=$item.image.value item=image}

            {if $image.remote === 'true'}
              <a href="{$image.preview}"><img src="{$image.preview}" /></a>
      			{else}
      				<img src="{$_core_folder}/img/data/{$image.preview}" />
      			{/if}
          {/foreach}
      </div>
    </td>
  </tr>
  {/if}
</table>
{/foreach}


</body>
</html>
