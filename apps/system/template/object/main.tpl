<div class="row-fluid">
    <div class="col-xs-12">
        <!-- PAGE CONTENT BEGINS -->
        <div class="tabbable">
            <!-- #section:top_menu -->

            <ul class="nav nav-tabs padding-18 tab-size-bigger" id="myTab">
                {section name=i loop=$top_menu_items}
                    <li class="{if $top_menu_items[i]['action'] == $object_action}active{/if}">
                        <a href="{$top_menu_items[i]['href']}" aria-expanded="true">
                            {if $top_menu_items[i]['icon'] != ''}
                                <i class="blue ace-icon {$top_menu_items[i]['icon']} bigger-120"></i>
                            {/if}
                            {$top_menu_items[i]['title']}
                        </a>
                    </li>
                {/section}
            </ul>

            <!-- /section:top_menu -->
            <div class="tab-content no-border padding-24">
                <div id="faq-tab-1" class="tab-pane fade active in">
                    {section name=i loop=$do_buttons}
                        <a href="?action={$object_action}&do={$do_buttons[i]['do']}" class="btn btn-primary">{$do_buttons[i]['title']}</a>
                    {/section}
                    {$extended_items}
                    {$object_action_result}
                </div>
            </div>
        </div>

        <!-- PAGE CONTENT ENDS -->
    </div><!-- /.col -->
</div>

