<div class="main-container container-fluid">
    <div class="breadcrumbs" id="breadcrumbs">

        <ul class="breadcrumb">
            {foreach from=$breadcrumbs_array item=crumb name=bread}
                {if $smarty.foreach.bread.first}<i class="icon-home home-icon"></i>{/if}
                <li {if $smarty.foreach.bread.last}class="active"{/if}><a href="{$crumb.href}">{$crumb.title}</a>{if !$smarty.foreach.bread.last} <span class="divider"><i class="icon-angle-right arrow-icon"></i></span>{/if}</li>
            {/foreach}
        </ul>
        <!-- div class="pull-right">{if $help_link!=''}{$help_link}{/if}</div-->
    </div>
        <div class="page-content">
            {$content}
        </div>
</div>
