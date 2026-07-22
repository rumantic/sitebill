{literal}
    <script>
        $(document).ready(function () {
            $('.tooltipe_block').popover({trigger: 'hover'});
        });
    </script>
{/literal}

{if !isset($formwideclass)}
{assign var=formwideclass value="col-span-12"}
{/if}
{if !isset($formsemiclass)}
{assign var=formsemiclass value="col-span-6"}
{/if}

<div class="tabbed_form_block card overflow-hidden">
    {if $form_error ne ''}
        <div class="bg-red-50 border-l-4 border-red-400 text-red-700 p-4 mb-4 rounded">
            {$form_error}
        </div>
    {/if}

    {$form_elements.form_header}

    {if isset($form_elements.scripts) && $form_elements.scripts|count>0}
        {foreach from=$form_elements.scripts item=form_element_script}
            {$form_element_script}
        {/foreach}
    {/if}

    <script type="text/javascript" src="{$estate_folder}/apps/system/js/form_tabs.js?v=2"></script>

    {if $form_elements.public|count eq 1}
        <!-- Single tab — no tab switcher needed -->
        <div class="p-6">
            {foreach from=$form_elements.public key=tab item=tab_elements}
                <div class="grid grid-cols-12 gap-4">
                    {foreach from=$tab_elements item=element}
                        <div class="{if $element.type=='textarea' or $element.type=='textarea_editor' or $element.type=='uploads' or $element.type=='docuploads' or $element.type=='geodata'}col-span-12{else}col-span-12 md:col-span-6{/if}">
                            {if $element.type=='captcha'}
                                <div class="mb-4" data-field="{$element.name}">
                                    <label class="block text-[13.5px] font-medium text-slate-600 mb-1">
                                        {$element.title}{if $element.required eq 1}<span class="text-red-500 ml-0.5">*</span>{/if}
                                        {if $element.hint!=''}<button type="button" class="tooltipe_block ml-1 inline-flex items-center justify-center w-4 h-4 rounded-full bg-slate-300 text-white text-xs" data-content="{$element.hint}">?</button>{/if}
                                    </label>
                                    {if $element.html_array.src ne ''}<img id="capcha_img" class="capcha_img rounded mb-2" src="{$element.html_array.src}" width="180" height="80" /><br/>{/if}
                                    {$element.html_array.refresh}
                                    {$element.html_array.input}
                                    {$element.html_array.hidden}
                                    {$element.html_array.js_string}
                                </div>
                            {else if $element.type=='geodata'}
                                {$element.map_js_string}
                                {$element.map_div_open}
                                <div class="grid grid-cols-2 gap-4 mb-3">
                                    <div class="geo-input-group flex items-center bg-white border border-slate-200 rounded-[10px] transition focus-within:border-accent focus-within:ring-2 focus-within:ring-accent/20">
                                        <span class="pl-3 pr-1 text-slate-400"><i class="fa fa-map-marker"></i></span>
                                        {$element.map_lat_input}
                                    </div>
                                    <div class="geo-input-group flex items-center bg-white border border-slate-200 rounded-[10px] transition focus-within:border-accent focus-within:ring-2 focus-within:ring-accent/20">
                                        <span class="pl-3 pr-1 text-slate-400"><i class="fa fa-map-marker"></i></span>
                                        {$element.map_lng_input}
                                    </div>
                                </div>
                                <div class="rounded-xl overflow-hidden border border-slate-200 shadow-sm">
                                    {$element.map_div_map}
                                </div>
                                {$element.map_div_close}
                            {else if $element.type=='checkbox'}
                                <div class="flex items-center space-x-2 py-2" data-field="{$element.name}">
                                    {$element.html}
                                    <label class="text-[13.5px] text-slate-600">{$element.title}</label>
                                </div>
                            {else if $element.type=='separator'}
                                <div class="pt-4 pb-2" data-field="{$element.name}">
                                    <h5 class="text-[12px] font-semibold uppercase tracking-wide text-slate-400 mb-1.5">{$element.title}</h5>
                                    <div class="h-px bg-slate-100"></div>
                                </div>
                            {else}
                                <div class="mb-4" data-field="{$element.name}">
                                    <label for="{$element.id}" class="block text-sm font-medium text-gray-700 mb-1">
                                        {$element.title}{if $element.required eq 1}<span class="text-red-500 ml-0.5">*</span>{/if}
                                        {if $element.hint!=''}<button type="button" class="tooltipe_block ml-1 inline-flex items-center justify-center w-4 h-4 rounded-full bg-gray-300 text-white text-xs" data-content="{$element.hint}">?</button>{/if}
                                    </label>
                                    <div class="form_element_html">{$element.html}</div>
                                    {if $element.image_list ne ''}
                                        <div class="mt-2">{$element.image_list}</div>
                                    {/if}
                                </div>
                            {/if}
                        </div>
                    {/foreach}
                </div>
            {/foreach}
        </div>
    {else}
        {if $divide_by_step==1}
            <!-- DIVIDED BY STEPS FORM -->
            <script type="text/javascript" src="{$estate_folder}/apps/system/js/form_tabs.js"></script>
            <link rel="stylesheet" type="text/css" href="{$estate_folder}/apps/system/css/form_tabs.css" />
            <link rel="stylesheet" type="text/css" href="{$estate_folder}/apps/system/css/form_tabs_divided.css" />

            <!-- Steps indicator -->
            <div class="flex items-center justify-center space-x-2 p-4 bg-slate-50 border-b border-slate-200">
                {foreach name=tab_foreach from=$form_elements.public key=tab item=tab_elements}
                    {assign var=tab_id value=md5($tab)}
                    {if $smarty.foreach.tab_foreach.iteration > 1}
                        <div class="w-8 h-0.5 bg-slate-300"></div>
                    {/if}
                    {if $smarty.foreach.tab_foreach.iteration > $current_step}
                        <span class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-slate-400 bg-slate-100 rounded-full">{$tab}</span>
                    {elseif $smarty.foreach.tab_foreach.iteration == $current_step}
                        <span class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-accent rounded-full">{$tab}</span>
                    {else}
                        <a href="/add/step{$smarty.foreach.tab_foreach.iteration}" class="go_to_step inline-flex items-center px-3 py-1.5 text-xs font-medium text-green-700 bg-green-100 rounded-full hover:bg-green-200" alt="{$smarty.foreach.tab_foreach.iteration}">{$tab}</a>
                    {/if}
                {/foreach}
            </div>

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

            {foreach name=tab_foreach_els from=$form_elements.public key=tab item=tab_elements}
                {assign var=tab_id value=md5($tab)}
                {if $smarty.foreach.tab_foreach_els.iteration==$current_step}
                    <div class="p-6 form_tab" id="{$tab_id}">
                        {foreach from=$tab_elements item=element}
                            <div class="mb-4" data-field="{$element.name}">
                                <label class="block text-[13.5px] font-medium text-slate-600 mb-1">
                                    {$element.title}{if $element.required eq 1}<span class="text-red-500 ml-0.5">*</span>{/if}
                                    {if $element.hint!=''}<button type="button" class="tooltipe_block ml-1 inline-flex items-center justify-center w-4 h-4 rounded-full bg-slate-300 text-white text-xs" data-content="{$element.hint}">?</button>{/if}
                                </label>
                                <div class="form_element_html">{$element.html}</div>
                                {if $element.image_list ne ''}
                                    <div class="mt-2">{$element.image_list}</div>
                                {/if}
                            </div>
                        {/foreach}
                    </div>
                {else}
                    <div class="form_tab hidden">
                        {foreach from=$tab_elements item=element}
                            <div class="mb-4" data-field="{$element.name}">
                                <label class="block text-[13.5px] font-medium text-slate-600 mb-1">
                                    {$element.title}{if $element.required eq 1}<span class="text-red-500 ml-0.5">*</span>{/if}
                                    {if $element.hint!=''}<button type="button" class="tooltipe_block ml-1 inline-flex items-center justify-center w-4 h-4 rounded-full bg-slate-300 text-white text-xs" data-content="{$element.hint}">?</button>{/if}
                                </label>
                                <div class="form_element_html">{$element.html}</div>
                                {if $element.image_list ne ''}
                                    <div class="mt-2">{$element.image_list}</div>
                                {/if}
                            </div>
                        {/foreach}
                    </div>
                {/if}
            {/foreach}
            <!-- .DIVIDED BY STEPS FORM -->
        {else}
            <!-- USUAL FORM WITH TABS -->
            <div class="border-b border-slate-200">
                <nav class="flex overflow-x-auto scroll-thin" id="form_tab" role="tablist">
                    {foreach name=tbf from=$form_elements.public key=tab item=tab_elements}
                        {assign var=tab_id value=md5($tab)}
                        <a class="nav-link tab {if $smarty.foreach.tbf.iteration==1}active{/if} relative px-4 py-3.5 text-[13.5px] font-medium whitespace-nowrap transition-colors
                            {if $smarty.foreach.tbf.iteration==1}text-accent-700{else}text-slate-500 hover:text-slate-700{/if}"
                           href="#t{$tab_id}" aria-controls="{$tab_id}" role="tab" data-toggle="tab">
                            {$tab}
                            <span class="tab-line absolute left-3 right-3 -bottom-px h-[2px] bg-accent rounded-full"></span>
                        </a>
                    {/foreach}
                </nav>
            </div>

            <div class="tab-content">
                {foreach name=tbf from=$form_elements.public key=tab item=tab_elements}
                    {assign var=tab_id value=md5($tab)}
                    <div role="tabpanel" class="tab-pane fade {if $smarty.foreach.tbf.iteration==1}in show active{/if} p-6" id="t{$tab_id}">
                        <div class="grid grid-cols-12 gap-4">
                            {foreach from=$tab_elements item=element}
                                <div class="{if $element.type=='textarea' or $element.type=='textarea_editor' or $element.type=='uploads' or $element.type=='docuploads' or $element.type=='geodata'}col-span-12{else}col-span-12 md:col-span-6{/if}">
                                    {if $element.type=='captcha'}
                                        <div class="mb-4" data-field="{$element.name}">
                                            <label class="block text-[13.5px] font-medium text-slate-600 mb-1">
                                                {$element.title}{if $element.required eq 1}<span class="text-red-500 ml-0.5">*</span>{/if}
                                            </label>
                                            {if $element.html_array.src ne ''}<img id="capcha_img" class="capcha_img rounded mb-2" src="{$element.html_array.src}" width="180" height="80" /><br/>{/if}
                                            {$element.html_array.refresh}
                                            {$element.html_array.input}
                                            {$element.html_array.hidden}
                                            {$element.html_array.js_string}
                                        </div>
                                    {else if $element.type=='geodata'}
                                        {$element.map_js_string}
                                        {$element.map_div_open}
                                        <div class="grid grid-cols-2 gap-4 mb-3">
                                            <div class="geo-input-group flex items-center bg-white border border-slate-200 rounded-[10px] transition focus-within:border-accent focus-within:ring-2 focus-within:ring-accent/20">
                                                <span class="pl-3 pr-1 text-slate-400"><i class="fa fa-map-marker"></i></span>
                                                {$element.map_lat_input}
                                            </div>
                                            <div class="geo-input-group flex items-center bg-white border border-slate-200 rounded-[10px] transition focus-within:border-accent focus-within:ring-2 focus-within:ring-accent/20">
                                                <span class="pl-3 pr-1 text-slate-400"><i class="fa fa-map-marker"></i></span>
                                                {$element.map_lng_input}
                                            </div>
                                        </div>
                                        <div class="rounded-xl overflow-hidden border border-slate-200 shadow-sm">
                                            {$element.map_div_map}
                                        </div>
                                        {$element.map_div_close}
                                    {else if $element.type=='checkbox'}
                                        <div class="flex items-center space-x-2 py-2" data-field="{$element.name}">
                                            {$element.html}
                                            <label class="text-[13.5px] text-slate-600">{$element.title}</label>
                                        </div>
                                    {else if $element.type=='separator'}
                                        <div class="pt-4 pb-2" data-field="{$element.name}">
                                            <h5 class="text-[12px] font-semibold uppercase tracking-wide text-slate-400 mb-1.5">{$element.title}</h5>
                                            <div class="h-px bg-slate-100"></div>
                                        </div>
                                    {else}
                                        <div class="mb-4" data-field="{$element.name}">
                                            <label for="{$element.id}" class="block text-[13.5px] font-medium text-slate-600 mb-1">
                                                {$element.title}{if $element.required eq 1}<span class="text-red-500 ml-0.5">*</span>{/if}
                                                {if $element.hint!=''}<button type="button" class="tooltipe_block ml-1 inline-flex items-center justify-center w-4 h-4 rounded-full bg-slate-300 text-white text-xs" data-content="{$element.hint}">?</button>{/if}
                                            </label>
                                            <div class="form_element_html">{$element.html}</div>
                                            {if $element.image_list ne ''}
                                                <div class="mt-2">{$element.image_list}</div>
                                            {/if}
                                        </div>
                                    {/if}
                                </div>
                            {/foreach}
                        </div>
                    </div>
                {/foreach}
            </div>
            <!-- .USUAL FORM WITH TABS -->
        {/if}
    {/if}

    {if isset($form_elements.agreement_block)}
        <div class="px-6">{$form_elements.agreement_block}</div>
    {/if}
    {if isset($form_elements.pre_controls)}
        <div class="px-6">{$form_elements.pre_controls}</div>
    {/if}

    <!-- Form controls -->
    <div class="sticky bottom-0 z-10 flex flex-wrap items-center gap-3 px-6 py-4 bg-white/95 backdrop-blur border-t border-slate-200 rounded-b-2xl">
        {$form_elements.controls.submit.html} {$form_elements.controls.apply.html} {$form_elements.controls.back.html}
    </div>

    {foreach from=$form_elements.private key=tab item=p_element}
        {$p_element.html}
    {/foreach}
    {$form_elements.form_footer}
</div>
