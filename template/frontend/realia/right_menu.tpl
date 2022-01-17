{section name=i loop=$right_menu}
    <div id="right-nav">
        <ul class="top-level">
            <li><a href="{$right_menu[i].url}" class="todo">{$right_menu[i].name}</a></li>
        </ul>
    </div>
{/section}
