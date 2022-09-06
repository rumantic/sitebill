{assign var='linecount' value=0}
{foreach from=$setdata item=condline}
	{assign var='colcount' value=0}
	<div class="condline">
		{foreach from=$condline item=condcol}
		<div class="condcol">
		<input type="text" name="field{$fname}[{$linecount}][{$colcount}][0]" value="{$condcol[0]}" />
		<select name="field{$fname}[{$linecount}][{$colcount}][1]">
			{foreach from=$condops item=condop key=condopkey}
				<option{if $condcol[1]==$condopkey} selected="selected"{/if} value="{$condopkey}">{$condop}</option>
			{/foreach}
		</select>

		<input type="text" name="field{$fname}[{$linecount}][{$colcount}][2]" value="{$condcol[2]}" />
		<a href="#" class="rem btn btn-danger btn-mini"><i class="icon-white icon-remove"></i></a>
		</div>
		{assign var='colcount' value=$colcount+1}
		{/foreach}
		<a href="#" class="add_and" data-fname="{$fname}" data-lc="{$linecount}" data-cc="{$colcount}">Добавить И условие</a>
	</div>
	{assign var='linecount' value=$linecount+1}
{/foreach}

<a href="#" class="add_or" data-fname="{$fname}" data-lc="{$linecount}">Добавить ИЛИ условие</a>