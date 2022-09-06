<div class="language-switcher">
    {foreach item=ln from=$available_langs key=k}
        {if $smarty.session._lang eq $k}
            <div class="current"><a href="#" lang="en"><img src="{$theme_folder}/img/flags/{$k}.png">{if $simplemode != 1} {$ln}{/if}</a></div><!-- /.current -->
        {/if}
    {/foreach}
    <div class="options">
        <ul>
            {foreach item=ln from=$available_langs key=k}
                {if $smarty.session._lang eq $k}
                {else}
                    <li>
                    {if $prefixmode == 1}
                        <a href="{if $k != 'ru'}{formaturl path="" locale=$k}{else}{formaturl path="" monolang=1}{/if}"><img src="{$theme_folder}/img/flags/{$k}.png"></a>
                    {else}
                        {assign var=langpath value='?_lang='|cat:$k}
                        <a href="{formaturl path=$langpath}"><img src="{$theme_folder}/img/flags/{$k}.png"></a>
                    {/if}
                    </li>
                {/if}
            {/foreach}
        </ul>
    </div>
</div>