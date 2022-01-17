<div id="left_menu">
    <ul>
        {section name=i loop=$left_menu}
            <li><a href="{$left_menu[i].url}">{$left_menu[i].name}</a></li>
            {/section}
    </ul>
</div>
