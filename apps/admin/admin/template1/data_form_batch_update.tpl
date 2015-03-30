<script type="text/javascript">
{literal}
$(document).ready(function(){
	/*$("#formsubmit").click(function(){
		$(this).hide();
		$('<p class="loading">Сохраняю данные...</p>').insertAfter(this).slideDown("fast");
	});*/
	
	$('.tooltipe_block').popover({trigger: 'hover'});
});
{/literal}
</script>

<div class="alert alert-info">
Отметьте чекбоксы возле имен элементов для которых необходимо установить свойства
</div>



<div class="tabbed_form_block">

{if $form_error ne ''}
	<p class="error">{$form_error}</p>
{/if}
<!-- <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script> -->
<!-- <script type="text/javascript" src="{$estate_folder}/apps/system/js/geodata.js"></script> -->

{$form_elements.form_header}
{if isset($form_elements.scripts) && $form_elements.scripts|count>0}
	{foreach from=$form_elements.scripts item=form_element_script}
		{$form_element_script}
	{/foreach}
{/if}


<script type="text/javascript" src="{$estate_folder}/apps/system/js/form_tabs.js"></script>

{if $form_elements.public|count eq 1}
        

	{foreach from=$form_elements.public key=tab item=tab_elements}
		{foreach from=$tab_elements item=element}
		<div class="form_element control-group" alt="{$element.name}">
			<label class="control-label"><input type="checkbox" name="batch_update[{$element.name}]" value="1"{if isset($selected_fields[$element.name])} checked="checked"{/if} /> {$element.title}{if $element.required eq 1}<span style="color: red;">*</span>{/if}{if $element.hint!=''} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content="{$element.hint}"> <i class="icon-question-sign icon-white"></i></a>{/if}</label>
			
			<div class="form_element_html controls">{$element.html}</div>
			{if $element.image_list ne ''}
				<div style="clear: both;"></div>
				<div>{$element.image_list}</div>
			{/if}
			
		</div>
		{/foreach}
	{/foreach}
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
							<input type="checkbox" name="batch_update[{$element.name}]" value="1"{if isset($selected_fields[$element.name])} checked="checked"{/if} /> 
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
							<input type="checkbox" name="batch_update[{$element.name}]" value="1"{if isset($selected_fields[$element.name])} checked="checked"{/if} /> 
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
		<ul class="nav nav-tabs" id="form_tab">
		{foreach from=$form_elements.public key=tab item=tab_elements}
			{assign var=tab_id value=md5($tab)}
			<li><a href="#{$tab_id}" data-toggle="tab">{$tab}</a></li>
		{/foreach}
		</ul>
		<div class="tab-content">
		{foreach name=tbf from=$form_elements.public key=tab item=tab_elements}
		
			{assign var=tab_id value=md5($tab)}
			<div class="tab-pane fade in{if $smarty.foreach.tbf.iteration==1} active{/if}" id="{$tab_id}">
			
				<!-- <h1>{$tab}</h1> -->
				{foreach from=$tab_elements item=element}
				<div class="form_element control-group" alt="{$element.name}">
					<label class="control-label"><input type="checkbox" name="batch_update[{$element.name}]" value="1"{if isset($selected_fields[$element.name])} checked="checked"{/if} /> {$element.title}{if $element.required eq 1}<span style="color: red;">*</span>{/if}{if $element.hint!=''} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content="{$element.hint}"> <i class="icon-question-sign icon-white"></i></a>{/if}</label>
					<div class="form_element_html controls">{$element.html}</div>
					{if $element.image_list ne ''}
						<div style="clear: both;"></div>
						<div>{$element.image_list}</div>
					{/if}
					
				</div>
				{/foreach}
			</div>
		{/foreach}
		</div>
		
		{literal}
		
		<script>
		 $(document).ready(function(){
				$('#form_tab a:first').tab('show');
			 //$('#form_tab li:first').addClass('active');
		 });
		  
		  
		</script>
		{/literal}
		<!-- .USUAL FORM WITH TABS -->
	{/if}

	
{/if}


<div class="form_element_control">
{$form_elements.controls.apply.html} {$form_elements.controls.back.html} {$form_elements.controls.submit.html}
</div>
{foreach from=$form_elements.private key=tab item=p_element}
	{$p_element.html}
{/foreach}
{$form_elements.form_footer}
</div>