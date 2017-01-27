{literal}
<script>
$(document).ready(function(){$('.tooltipe_block').popover({trigger: 'hover'});});
</script>
{/literal}

{if $bootstrap_version=='3' && $smarty.const.ADMIN_MODE!=1}
{literal}
<style>
.form-horizontal .form-group {margin-left: 0; margin-right: 0;}
</style>
{/literal}
{/if}

<div class="tabbed_form_block">
{if $form_error ne ''}
	<div class="error">{$form_error}</div>
{/if}

{$form_elements.form_header}

{if isset($form_elements.scripts) && $form_elements.scripts|count>0}
	{foreach from=$form_elements.scripts item=form_element_script}
		{$form_element_script}
	{/foreach}
{/if}

<script type="text/javascript" src="{$estate_folder}/apps/system/js/form_tabs.js"></script>
{if $form_elements.public|count eq 1}
	<div class="tab-content-single tab-margin-top">
    {foreach from=$form_elements.public key=tab item=tab_elements}
 		
			{if $bootstrap_version=='3' && $smarty.const.ADMIN_MODE!=1}
					<div class="box">
					<div class="row">
					{foreach from=$tab_elements item=element}
					<div class="{if $element.type=='textarea' or $element.type=='textarea_editor' or $element.type=='uploads' or $element.type=='docuploads' or $element.type=='geodata'}col-sm-12{else}col-sm-6{/if}">
                    {if $element.type=='captcha'}
						<div class="form-group" alt="{$element.name}">
						{$element.title}{if $element.required eq 1}<span style="color: red;">*</span>{/if}{if $element.hint!=''} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-xs" data-content="{$element.hint}"> <i class="fa fa-question"></i></a>{/if}
						<img id="capcha_img" class="capcha_img" src="{$element.html_array.src}" width="180" height="80" /><br/>
						{$element.html_array.refresh}
						
						{$element.html_array.input}
						{$element.html_array.hidden}
						{$element.html_array.js_string}
						</div>
					{else if  $element.type=='geodata'}
						
						{$element.map_js_string}
						{$element.map_div_open}
						<div class="row">
							<div class="col-md-6">
							    <div class="input-group">
							        <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
							        {$element.map_lat_input}
							    </div>
							</div>
							<div class="col-md-6">
							    <div class="input-group">
							        <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
							        {$element.map_lng_input}
							    </div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
							    {*$element.map_div*}{$element.map_div_map}
							</div>
						</div>
						{$element.map_div_close}
						
						{*$element.html*}
					{else if  $element.type=='checkbox'}
						<div class="checkbox">
							<label>
								{$element.html} {$element.title}
							</label>
						</div>
					{else}
						<div class="form-group" alt="{$element.name}">
							<label for="{$element.id}">{$element.title}{if $element.required eq 1}<span style="color: red;">*</span>{/if}{if $element.hint!=''} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-xs" data-content="{$element.hint}"> <i class="fa fa-question"></i></a>{/if}</label>
							{$element.html}
							{if $element.image_list ne ''}
								<div style="clear: both;"></div>
								<div>{$element.image_list}</div>
							{/if}
						</div>
					{/if}
					</div>
					{/foreach}
					</div>
					</div>
				{elseif $bootstrap_version=='4md' && $smarty.const.ADMIN_MODE!=1}
					{foreach from=$tab_elements item=element}
					<div class="form_element form-group" alt="{$element.name}">
						<label for="{$element.id}" class="form-check-label">{$element.html} {$element.title}{if $element.required eq 1}<span style="color: red;">*</span>{/if}</label>
						{if $element.hint!=''}<p class="help-block">{$element.hint}</p>{/if}
						{if $element.image_list ne ''}
							<div style="clear: both;"></div>
							<div>{$element.image_list}</div>
						{/if}
					</div>
					{/foreach}
				{else}
					{foreach from=$tab_elements item=element}
					<div class="form_element control-group" alt="{$element.name}">
						<label class="control-label">{$element.title}{if $element.required eq 1}<span style="color: red;">*</span>{/if}{if $element.hint!=''} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content="{$element.hint}"> <i class="icon-question-sign icon-white"></i></a>{/if}</label>
						<div class="form_element_html controls">{$element.html}</div>
						{if $element.image_list ne ''}
							<div style="clear: both;"></div>
							<div>{$element.image_list}</div>
						{/if}
					</div>
					{/foreach}
				{/if}
		
	{/foreach}
	</div>
{else}
	{if $divide_by_step==1}
		<!-- DIVIDED BY STEPS FORM -->
		<script type="text/javascript" src="{$estate_folder}/apps/system/js/form_tabs.js"></script>
		<link rel="stylesheet" type="text/css" href="{$estate_folder}/apps/system/css/form_tabs.css" />
		<link rel="stylesheet" type="text/css" href="{$estate_folder}/apps/system/css/form_tabs_divided.css" />
		
		<div id="form_tab_switcher" style="display:none;">
		{foreach name=tab_foreach from=$form_elements.public key=tab item=tab_elements}
			{assign var=tab_id value=md5($tab)}
			{if $smarty.foreach.tab_foreach.iteration>$current_step}
				<span>{$tab}</span>
			{elseif $smarty.foreach.tab_foreach.iteration==$current_step}
				<a href="{$tab_id}" class="active_tab">{$tab}</a>
			{else}
				<a href="{$tab_id}">{$tab}</a>
			{/if}
		{/foreach}
		</div>
		
		<div class="steps">
		{foreach name=tab_foreach from=$form_elements.public key=tab item=tab_elements}
			{assign var=tab_id value=md5($tab)}
			{if $smarty.foreach.tab_foreach.iteration>$current_step}
				<div class="future">{$tab}</div>
			{elseif $smarty.foreach.tab_foreach.iteration==$current_step}
				<div class="current"><a class="go_to_step" alt="{$smarty.foreach.tab_foreach.iteration}" href="/add/step{$smarty.foreach.tab_foreach.iteration}">{$tab}</a></div>
			{else}
				<div class="done"><a class="go_to_step" alt="{$smarty.foreach.tab_foreach.iteration}" href="/add/step{$smarty.foreach.tab_foreach.iteration}">{$tab}</a></div>
			{/if}
		{/foreach}
		</div>
		
		{foreach name=tab_foreach_els from=$form_elements.public key=tab item=tab_elements}
			{assign var=tab_id value=md5($tab)}
			{if $smarty.foreach.tab_foreach_els.iteration==$current_step}
			<div class="form_tab" id="{$tab_id}">
				{foreach from=$tab_elements item=element}
					<div class="form_element" alt="{$element.name}">
						<div class="form_element_title">
							{$element.title}
							{if $element.required eq 1}
								<span style="color: red;">*</span>
							{/if}
							{if $element.hint!=''} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content="{$element.hint}"> <i class="icon-question-sign icon-white"></i></a>{/if}
						</div>
						<div class="form_element_html">{$element.html}</div>
						{if $element.image_list ne ''}
							<div style="clear: both;"></div>
							<div>{$element.image_list}</div>
						{/if}
					</div>
					{/foreach}
				</div>
			{else}
				<div class="form_tab">
					{foreach from=$tab_elements item=element}
					<div class="form_element" alt="{$element.name}">
						<div class="form_element_title">
							{$element.title}
							{if $element.required eq 1}
								<span style="color: red;">*</span>
							{/if}
							{if $element.hint!=''} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content="{$element.hint}"> <i class="icon-question-sign icon-white"></i></a>{/if}
						</div>
						<div class="form_element_html">{$element.html}</div>
						{if $element.image_list ne ''}
							<div style="clear: both;"></div>
							<div>{$element.image_list}</div>
						{/if}
					</div>
					{/foreach}
				</div>
			{/if}
		{/foreach}
		<!-- .DIVIDED BY STEPS FORM -->
	{else}
		<!-- USUAL FORM WITH TABS -->

  
		<ul class="nav nav-tabs" id="form_tab" role="tablist">
		{foreach name=tbf from=$form_elements.public key=tab item=tab_elements}
			{assign var=tab_id value=md5($tab)}
			<li role="presentation" {if $smarty.foreach.tbf.iteration==1}class="active"{/if}><a href="#t{$tab_id}" aria-controls="{$tab_id}" role="tab" data-toggle="tab">{$tab}</a></li>
		{/foreach}
		</ul>
		
		<div class="tab-content tab-margin-top">
		{foreach name=tbf from=$form_elements.public key=tab item=tab_elements}
		
			{assign var=tab_id value=md5($tab)}
			<div role="tabpanel" class="tab-pane fade in{if $smarty.foreach.tbf.iteration==1} active{/if}" id="t{$tab_id}">
				{if $bootstrap_version=='3' && $smarty.const.ADMIN_MODE!=1}
					<div class="box">
					<div class="row">
					{foreach from=$tab_elements item=element}
					<div class="{if $element.type=='textarea' or $element.type=='textarea_editor' or $element.type=='uploads' or $element.type=='docuploads' or $element.type=='geodata'}col-sm-12{else}col-sm-6{/if}">
                    {if $element.type=='captcha'}
						<div class="form-group" alt="{$element.name}">
						{$element.title}{if $element.required eq 1}<span style="color: red;">*</span>{/if}{if $element.hint!=''} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-xs" data-content="{$element.hint}"> <i class="fa fa-question"></i></a>{/if}
						<img id="capcha_img" class="capcha_img" src="{$element.html_array.src}" width="180" height="80" /><br/>
						{$element.html_array.refresh}
						
						{$element.html_array.input}
						{$element.html_array.hidden}
						{$element.html_array.js_string}
						</div>
					{else if  $element.type=='geodata'}
						
						{$element.map_js_string}
						{$element.map_div_open}
						<div class="row">
							<div class="col-md-6">
							    <div class="input-group">
							        <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
							        {$element.map_lat_input}
							    </div>
							</div>
							<div class="col-md-6">
							    <div class="input-group">
							        <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
							        {$element.map_lng_input}
							    </div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
							    {*$element.map_div*}{$element.map_div_map}
							</div>
						</div>
						{$element.map_div_close}
						
						{*$element.html*}
					{else if  $element.type=='checkbox'}
						<div class="checkbox">
							<label>
								{$element.html} {$element.title}
							</label>
						</div>
					{else}
						<div class="form-group" alt="{$element.name}">
							<label for="{$element.id}">{$element.title}{if $element.required eq 1}<span style="color: red;">*</span>{/if}{if $element.hint!=''} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-xs" data-content="{$element.hint}"> <i class="fa fa-question"></i></a>{/if}</label>
							{$element.html}
							{if $element.image_list ne ''}
								<div style="clear: both;"></div>
								<div>{$element.image_list}</div>
							{/if}
						</div>
					{/if}
					</div>
					{/foreach}
					</div>
					</div>
				{elseif $bootstrap_version=='4md'}
					{foreach from=$tab_elements item=element}
					<div class="form_element form-group" alt="{$element.name}">
						<label for="{$element.id}" class="form-check-label">{$element.html} {$element.title}{if $element.required eq 1}<span style="color: red;">*</span>{/if}</label>
						{if $element.hint!=''}<p class="help-block">{$element.hint}</p>{/if}
						{if $element.image_list ne ''}
							<div style="clear: both;"></div>
							<div>{$element.image_list}</div>
						{/if}
					</div>
					{/foreach}
				{else}
					{foreach from=$tab_elements item=element}
					<div class="form_element control-group" alt="{$element.name}">
						<label class="control-label">{$element.title}{if $element.required eq 1}<span style="color: red;">*</span>{/if}{if $element.hint!=''} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content="{$element.hint}"> <i class="icon-question-sign icon-white"></i></a>{/if}</label>
						<div class="form_element_html controls">{$element.html}</div>
						{if $element.image_list ne ''}
							<div style="clear: both;"></div>
							<div>{$element.image_list}</div>
						{/if}
					</div>
					{/foreach}
				{/if}

			</div>
		{/foreach}
		</div>

		<!-- .USUAL FORM WITH TABS -->
	{/if}
{/if}
{if isset($form_elements.pre_controls)}
	{$form_elements.pre_controls}
{/if}
<div class="form_element_control">
{$form_elements.controls.apply.html} {$form_elements.controls.back.html} {$form_elements.controls.submit.html}
</div>

	{foreach from=$form_elements.private key=tab item=p_element}
		{$p_element.html}
	{/foreach}
	{$form_elements.form_footer}
</div>