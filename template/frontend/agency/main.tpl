{if $is_underconstruction_mode==1}
	{include file='main_closed.tpl'}
{else}
{include file="header.tpl"}
<body>
	{if $smarty.session.user_id eq ''}
        {include file="login_register.tpl.html"}
    {/if}
	{if $apps_page_view}
	{include file="layout_minimum.tpl"}
	{else}
	{include file=$_layout}
	{/if}
{*$profiler*}
{$dashboard}
</body>
</html>
{/if}