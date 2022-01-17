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
  width: 100%;
  margin-bottom: 20px;
}
.data_grid_item td, .data_grid_item th {
  padding: 5px 10px;
  border: 1px solid Gray;
}
.data_grid_item .rgallery  td {
  padding: 10px;
  border: 0;
}

.rgallery .rgallery-item {
  width: 110px;
  height: 80px;
  overflow: hidden;
}
.rgallery .rgallery-item img {
  width: 110px;
  max-width: 110px;
}
</style>
{/literal}
</head>
<body>
{if $user_data}
<table class="data_grid_item">
  <tr>
    <th style="background-color: #eee;">Ваш менеджер: {$user_data.fio.value}</th>
    <th>Телефон: <a href="tel:{$user_data.phone.value}">{$user_data.phone.value}</a></th>
  </tr>
</table>
{/if}

{foreach $grid_items item=item key=key}
  {if $item.id.value != ''}
<table class="data_grid_item">
    <tr>
        <th style="background-color: #eee;">{$item.id.value}</th>
        <th>{$item.topic_id.value_string}</th>
        <th>{if floatval($item.price.value) != 0}{floatval($item.price.value)|number_format:0:".":" "}{if isset($item.currency_id) && $item.currency_id.value != 0}{$item.currency_id.value_string}{/if}{/if}</th>
      </tr>

  <tr>
    <td>Адрес</td>
    <td colspan="2">
      {assign var=c value=array()}
      {if $item.region_id.value > 0}
        {append var=c value=$item.region_id.value_string}
      {/if}
      {if $item.city_id.value > 0}
        {append var=c value=$item.city_id.value_string}
      {/if}
      {if $item.district_id.value > 0}
        {append var=c value=$item.district_id.value_string}
      {/if}
      {if $item.street_id.value > 0}
        {append var=c value=$item.street_id.value_string}
      {/if}
      {if $item.number.value != ''}
        {append var=c value=$item.number.value}
      {/if}
      {if $item.flat_nr.value != ''}
        {append var=c value='кв. '|cat:$item.flat_nr.value}
      {/if}
      {if !empty($c)}{$c|implode:", "}{/if}


    </td>
  </tr>
  {if $item.text.value != ''}
    <tr>
      <td>Описание</td>
      <td colspan="2">{$item.text.value}</td>
    </tr>
  {/if}
    {if is_array($item.image.value)}
    <tr>
      <td colspan="3">
        <table class="rgallery">
          <tr>
            {foreach from=$item.image.value item=image name=j}
              <td>
              {if $image.remote === 'true'}
                <a href="{$item._href}" target="_blank"><div class="rgallery-item"><img src="{mediaincpath data=$image type='preview' src=2}" /></div></a>
              {else}
                <div class="rgallery-item"><img src="{mediaincpath data=$image type='preview' src=2}" /></div>
              {/if}
            </td>
            {if $smarty.foreach.j.iteration%5 == 0}
              </tr><tr>
            {/if}
            {/foreach}
          </tr>
        </table>
      </td>
  </tr>
  {/if}

</table>
  {/if}
{/foreach}

</body>
</html>
