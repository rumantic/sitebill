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

.logo-rgallery .rgallery-item {
  width: 180px;
  overflow: hidden;
}
.logo-rgallery .rgallery-item img {
  width: 180px;
  max-width: 180px;
}

.plan-rgallery .rgallery-item {
  width: 650px;
  height: 300px;
  overflow: hidden;
}
.plan-rgallery .rgallery-item img {
  width: 650px;
  max-width: 650px;
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
        <th style="background-color: #eee;">
          ID: {$item.id.value}<br> <a href="{$item._href}?format=pdf">скачать полную презентацию</a>
        </th>
        <th>{$item.topic_id.value_string}</th>
        <th>{if floatval($item.price.value) != 0}{floatval($item.price.value)|number_format:0:".":" "}{if isset($item.currency_id) && $item.currency_id.value != 0}{$item.currency_id.value_string}{/if}{/if}</th>
        <th>Площадь: {$item.square_all.value} кв.м.</th>
      </tr>

  <tr>
    <td>Адрес</td>
    <td colspan="3">
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
      <td>
        {if $item.dvadcatpyatchasov.value == '1'}
          <div class="logo-rgallery">
            <div class="rgallery-item">
              <img src="{$_core_folder}/template/frontend/novosel/img/logo/logo-25-180.png">
            </div>
          </div>
      	{/if}
      </td>
      <td colspan="3">{$item.text.value}</td>
    </tr>
  {/if}
    {if is_array($item.image.value)}
    <tr>
      <td colspan="4">
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

  {if $item.dvadcatpyatchasov.value == '1' and is_array($item.planningf.value)}
  <tr>
    <td colspan="4">
      <table class="plan-rgallery">
        <tr>
          {foreach from=$item.planningf.value item=image name=j}
            <td>
            {if $image.remote === 'true'}
              <a href="{$item._href}" target="_blank"><div class="rgallery-item"><img src="{mediaincpath data=$image type='normal' src=2}" /></div></a>
            {else}
              <div class="rgallery-item"><img src="{mediaincpath data=$image type='normal' src=2}" /></div>
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
