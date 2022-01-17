<script>
    var sort_links = [];
    var core_link = '{$estate_folder}/{$url}';
        function run() {
            var inputSortBy = $('#inputSortBy option:selected');
            var inputOrder = $('#inputOrder option:selected');
            core_link = core_link + '&order=' + inputSortBy.attr('data-id') + '&asc=' + inputOrder.attr('data-id');
            window.location = core_link;
        }
        ;
    {literal}
        $(document).ready(function () {
            $('#inputSortBy').change(function () {
                run();
            });
            $('#inputOrder').change(function () {
                run();
            });
        });

    {/literal}
</script>

<div class="filter">
    <div class="spec_grid_info">
        {$L_FIND_TOTAL}: <b>{$_total_records}</b>
        <div class="viewtype_buttons">
            <a href="{$estate_folder}/{$url}&grid_type=list" class="list_view{if $smarty.session.grid_type eq 'list'} active{/if}" rel="nofollow"><i class="icon-align-justify"></i></a>
            <a href="{$estate_folder}/{$url}&grid_type=thumbs" class="thumbs_view{if $smarty.session.grid_type eq 'thumbs'} active{/if}" rel="nofollow"><i class="icon-th"></i></a>
        </div>
    </div>
    <form action="?" method="get" class="form-horizontal">
        <div class="control-group">
            <div class="controls">
                <select id="inputSortBy">
                    <option data-id="">{$LT_SORTBY}</option>
                    <option data-id="type"{if $smarty.request.order=='type'} selected="selected"{/if}>{$L_TYPE}</option>
                    <option data-id="city"{if $smarty.request.order=='city'} selected="selected"{/if}>{$L_CITY}</option>
                    <option data-id="district"{if $smarty.request.order=='district'} selected="selected"{/if}>{$L_DISTRICT}</option>
                    <option data-id="street"{if $smarty.request.order=='street'} selected="selected"{/if}>{$L_STREET}</option>
                    <option data-id="price"{if $smarty.request.order=='price'} selected="selected"{/if}>{$L_PRICE}</option>
                </select>
            </div><!-- /.controls -->
        </div><!-- /.control-group -->

        <div class="control-group">
            <div class="controls">
                <select id="inputOrder">
                    <option data-id=""><i class="icon-search"></i>{$LT_ORDER}</option>
                    <option data-id="asc"{if $smarty.request.asc=='asc'} selected="selected"{/if}><i class="icon-search"></i>{$LT_ORDER_UP}</option>
                    <option data-id="desc"{if $smarty.request.asc=='desc'} selected="selected"{/if}>{$LT_ORDER_DOWN}</option>
                </select>
            </div><!-- /.controls -->
        </div><!-- /.control-group -->
    </form>
</div><!-- /.filter -->