{literal}
<script type="text/javascript">
$(document).ready(function(){
	$('.tooltipe_block').popover({trigger: 'hover'});
});
</script>
{/literal}
{if $bootstrap_version=='3' && $smarty.const.ADMIN_MODE!=1}
{literal}
<style>

</style>
{/literal}
{/if}

<div class="alert alert-info">
{_e t="Отметьте чекбоксы возле имен элементов для которых необходимо установить свойства"}
</div>

<div class="tabbed_form_block">
{if $form_error ne ''}
	<p class="error">{$form_error}</p>
{/if}

{$form_elements.form_header}
{if isset($form_elements.scripts) && $form_elements.scripts|count>0}
	{foreach from=$form_elements.scripts item=form_element_script}
		{$form_element_script}
	{/foreach}
{/if}
<script type="text/javascript" src="{$estate_folder}/apps/system/js/form_tabs.js?v=1"></script>
{if $form_elements.public|count eq 1}
    <div class="tab-content tab-margin-top">
    {foreach from=$form_elements.public key=tab item=tab_elements}
		{if $bootstrap_version=='3' && $smarty.const.ADMIN_MODE!=1}
            <div class="row">
					{foreach from=$tab_elements item=element}
					<div class="{if $element.type=='textarea' or $element.type=='textarea_editor' or $element.type=='uploads' or $element.type=='docuploads' or $element.type=='geodata'}col-sm-12{else}col-sm-12{/if}">
                    {if $element.type=='captcha'}
						<div class="form-group" alt="{$element.name}">
						{$element.title}{if $element.required eq 1}<span style="color: red;">*</span>{/if}{if $element.hint!=''} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-xs" data-content="{$element.hint}"> <i class="fa fa-question"></i></a>{/if}
						{if $element.html_array.src ne ''}<img id="capcha_img" class="capcha_img" src="{$element.html_array.src}" width="180" height="80" /><br/>{/if}
						{$element.html_array.refresh}
						
						{$element.html_array.input}
						{$element.html_array.hidden}
						{$element.html_array.js_string}
						</div>
					{else if  $element.type=='geodata'}
						{if 1==0}
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
						{/if}
						{$element.html}
					{else if  $element.type=='checkbox'}
						<div class="checkbox" alt="{$element.name}">
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

	<!-- USUAL FORM WITH TABS -->
		<ul class="nav nav-tabs" id="form_tab" role="tablist">
		{foreach name=tbf from=$form_elements.public key=tab item=tab_elements}
			{assign var=tab_id value=md5($tab)}
			<li role="presentation" {if $smarty.foreach.tbf.iteration==1}class="active"{/if}><a href="#t{$tab_id}" aria-controls="{$tab_id}" role="tab" data-toggle="tab">{$tab}</a></li>
		{/foreach}
		</ul>
		
		<div class="tab-content">
		{foreach name=tbf from=$form_elements.public key=tab item=tab_elements}
		
			{assign var=tab_id value=md5($tab)}
			<div role="tabpanel" class="tab-pane fade in{if $smarty.foreach.tbf.iteration==1} active{/if}" id="t{$tab_id}">
				{if $bootstrap_version=='3' && $smarty.const.ADMIN_MODE!=1}
                   <div style="overflow: hidden;">
					
					{foreach from=$tab_elements item=element}
                        
                       
					<div class="{if $element.type=='textarea' or $element.type=='textarea_editor' or $element.type=='uploads' or $element.type=='docuploads' or $element.type=='geodata'}col-md-12{else}col-md-12{/if}" alt="{$element.name}">
                        
                    {if $element.type=='captcha'}
                        <div class="form-group">
                            <div class="col-sm-1 control-label">
                                <input type="checkbox" name="batch_update[{$element.name}]" value="1"{if isset($selected_fields[$element.name])} checked="checked"{/if} />
                            </div>
							<label for="{$element.id}" class="col-sm-2 control-label">                                
                                {$element.title}
                                {if $element.required eq 1}<span style="color: red;">*</span>{/if}
                                {if $element.hint!=''} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-xs" data-content="{$element.hint}"> <i class="fa fa-question"></i></a>{/if}
                            </label>
							<div class="col-sm-9">
                                <img id="capcha_img" class="capcha_img" src="{$element.html_array.src}" width="180" height="80" /><br/>
                                {$element.html_array.refresh}

                                {$element.html_array.input}
                                {$element.html_array.hidden}
                                {$element.html_array.js_string}
                            </div>
						</div>
				
					{else if  $element.type=='geodata'}
						<div class="form-group">
                            <div class="col-sm-1 control-label">
                                <input type="checkbox" name="batch_update[{$element.name}]" value="1"{if isset($selected_fields[$element.name])} checked="checked"{/if} />
                            </div>
							<label for="{$element.id}" class="col-sm-2 control-label">                                
                                {$element.title}
                                {if $element.required eq 1}<span style="color: red;">*</span>{/if}
                                {if $element.hint!=''} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-xs" data-content="{$element.hint}"> <i class="fa fa-question"></i></a>{/if}
                            </label>
							<div class="col-sm-9">
                                {$element.html}
                            </div>
						</div>
						
					{else if  $element.type=='checkbox'}
                        <div class="form-group">
                            <div class="col-sm-1 control-label"><input type="checkbox" name="batch_update[{$element.name}]" value="1"{if isset($selected_fields[$element.name])} checked="checked"{/if} /></div>
							<label for="{$element.id}" class="col-sm-2 control-label">
                                
                                {$element.title}{if $element.required eq 1}<span style="color: red;">*</span>{/if}{if $element.hint!=''} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-xs" data-content="{$element.hint}"> <i class="fa fa-question"></i></a>{/if}</label>
							<div class="col-sm-9">
                                <div class="checkbox">
                                    <label>
                                        {$element.html}
                                    </label>
                                </div>
                            </div>
						</div>
						
					{else}
						<div class="form-group">
                            <div class="col-sm-1 control-label">
                                <input type="checkbox" name="batch_update[{$element.name}]" value="1"{if isset($selected_fields[$element.name])} checked="checked"{/if} />
                            </div>
							<label for="{$element.id}" class="col-sm-2 control-label">
                                
                                {$element.title}{if $element.required eq 1}<span style="color: red;">*</span>{/if}{if $element.hint!=''} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-xs" data-content="{$element.hint}"> <i class="fa fa-question"></i></a>{/if}</label>
							<div class="col-sm-9">
                                {$element.html}
							{if $element.image_list ne ''}
								<div style="clear: both;"></div>
								<div>{$element.image_list}</div>
							{/if}
                            </div>
						</div>
					{/if}
					</div>
                    
					{/foreach}
					
					</div>
				{elseif $bootstrap_version=='4md'}
					{foreach from=$tab_elements item=element}
					<div class="form_element form-group" alt="{$element.name}">
						<label for="{$element.id}" class="form-check-label">
                            <input type="checkbox" name="batch_update[{$element.name}]" value="1"{if isset($selected_fields[$element.name])} checked="checked"{/if} />
                            {$element.html} {$element.title}{if $element.required eq 1}<span style="color: red;">*</span>{/if}</label>
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
						<label class="control-label">
                            <input type="checkbox" name="batch_update[{$element.name}]" value="1"{if isset($selected_fields[$element.name])} checked="checked"{/if} />
                            {$element.title}{if $element.required eq 1}<span style="color: red;">*</span>{/if}{if $element.hint!=''} <a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content="{$element.hint}"> <i class="icon-question-sign icon-white"></i></a>{/if}</label>
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
		
		{literal}
		<script>
		 $(document).ready(function(){$('#form_tab a:first').tab('show');});
		</script>
		{/literal}
		<!-- .USUAL FORM WITH TABS -->
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