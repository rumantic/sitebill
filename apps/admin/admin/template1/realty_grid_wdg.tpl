<script src="{$assets_folder}/assets/js/jquery.colorbox-min.js"></script>
{include file="controls_js.tpl"}
{literal}
    <script type="text/javascript">
        var fast_previews = [];
        var column_values_for_tags = [];
        var datastr = {};


        function setColorboxWrapper(id) {
            var $overflow = '';
            var colorbox_params = {
                rel: 'colorbox' + id,
                reposition: true,
                scalePhotos: true,
                scrolling: false,
                previous: '<i class="ace-icon fa fa-arrow-left"></i>',
                next: '<i class="ace-icon fa fa-arrow-right"></i>',
                close: '&times;',
                current: '{current} of {total}',
                maxWidth: '100%',
                maxHeight: '100%',
                onOpen: function () {
                    $overflow = document.body.style.overflow;
                    document.body.style.overflow = 'hidden';
                },
                onClosed: function () {
                    document.body.style.overflow = $overflow;
                },
                onComplete: function () {
                    $.colorbox.resize();
                }
            };

            $('.ace-thumbnails [data-rel="colorbox' + id + '"]').colorbox(colorbox_params);
        }

        $(document).ready(function () {
            
            $('.colorboxed').each(function (item) {
                setColorboxWrapper($(this).data('cbxid'));
            });

            $('.go_up').click(function () {
                var id = $(this).attr('alt');
                var tr = $(this).parents('tr').eq(0);
                $.getJSON(estate_folder + '/js/ajax.php?action=go_up&id=' + id, {}, function (data) {
                    if (data.response.body != '') {
                        tr.find('td').eq(1).html(data.response.body);
                        tr.parents('table').eq(0).find('tr.row3').eq(0).before(tr);
                    }
                });
            });


            $('#search_toggle').click(function () {
                $('#search_form_block').toggle();
                $('#srch_date_from').datepicker({dateFormat: 'yy-mm-dd'});
                $('#srch_date_to').datepicker({dateFormat: 'yy-mm-dd'});

            });
            $('#reset').click(function () {
                $(this).parents('form').eq(0).find('input[type=text]').each(function () {
                    this.value = '';
                });
                $(this).parents('form').submit();
            });


            $('#grid_control_panel select[name=cp_optype]').change(function () {

                var operation = $(this).val();
                if (operation != '') {
                    $.ajax({
                        url: estate_folder + '/js/ajax.php',
                        data: {action: 'get_form_element', element: operation},
                        dataType: 'html',
                        success: function (html) {
                            $('#grid_control_panel_content').html(html);
                            $('#grid_control_panel button#run').show();
                        }
                    });
                }
            });


            /*
             $('#grid_control_panel button#run').click(function(){
         
             var cp=$('#grid_control_panel');
             var action=$(this).attr('alt');
             var operation=cp.find('select[name=cp_optype]').val();
         
             if(operation!=''){
             var field=null;
             if(cp.find('#grid_control_panel_content select').length!=0){
             var field=cp.find('#grid_control_panel_content select');
             }else if(cp.find('#grid_control_panel_content input').length!=0){
             var field=cp.find('#grid_control_panel_content input');
             if(field.attr('type')=='checkbox' && field.is(':checked')){
             field.val('1');
             }
         
             }
             if(field!==null){
             var cat_id=field.val();
             }
             var checked=[];
             $('.grid_check_one:checked').each(function(){
             checked.push(this.value);
             });
             if(checked.length>0){
             window.location.replace(estate_folder+'/admin/index.php?action='+action+'&do=change_param&new_param_value='+cat_id+'&param_name='+operation+'&ids='+checked.join(','));
             }
         
             }
             return false;
             });
             */
            $('.batch_update').click(function () {
                var ids = [];
                var action = $(this).attr('alt');
                $(this).parents('table').eq(0).find('input.grid_check_one:checked').each(function () {
                    ids.push($(this).val());
                });
                window.location.replace(estate_folder + '/admin/?action=' + action + '&do=batch_update&batch_ids=' + ids.join(','));
            });
            
            $('.batch_field_edit').click(function (e) {
                e.preventDefault();
                var ids = [];
                var field = $(this).data('field');
                var action = $(this).data('action');
                $(this).parents('table').eq(0).find('input.grid_check_one:checked').each(function () {
                    ids.push('id[]='+$(this).val());
                });
                if(ids.length==0){
                    return;
                }
                var url=estate_folder + '/admin/?action='+action+'&do=batch_field_edit&' + ids.join('&')+'&field='+field;
                //console.log(url);
                window.location.replace(url);
            });

            $('.duplicate').click(function () {
                var ids = [];
                var action = $(this).attr('alt');
                $(this).parents('table').eq(0).find('input.grid_check_one:checked').each(function () {
                    ids.push($(this).val());
                });
                if (ids.length > 0) {
                    if (confirm("Дублировать с картинками?")) {
                        window.location.replace(estate_folder + '/admin/?action=' + action + '&do=duplicate&duplicate_images=1&ids=' + ids.join(','));
                    } else {
                        window.location.replace(estate_folder + '/admin/?action=' + action + '&do=duplicate&ids=' + ids.join(','));
                    }
                }
                //window.location.replace(estate_folder+'/admin/index.php?action='+action+'&do=duplicate&ids='+ids.join(','));
            });
            $('.tooltipe_block').popover({trigger: 'hover'});
            $("#cboxLoadingGraphic").append("<i class='ace-icon fa fa-spinner orange'></i>");//let's add a custom loading icon
            $('.fast_preview').click(function () {
                var id = $(this).data('id');
                if (fast_previews[id] === undefined) {
                    $.ajax({
                        url: estate_folder + '/js/ajax.php?action=fast_preview&id=' + id,
                        dataType: 'html',
                        success: function (html) {
                            fast_previews[id] = html;
                            $('#fast_preview_modal').find('.modal-body').html(html);
                            $('#fast_preview_modal').find('.newwin').attr('href', estate_folder + '/admin/?action=data&do=view&id=' + id);
                            $('#fast_preview_modal').modal('show');
                        }
                    });
                } else {
                    $('#fast_preview_modal').find('.modal-body').html(fast_previews[id]);
                    $('#fast_preview_modal').find('.newwin').attr('href', estate_folder + '/admin/?action=data&do=view&id=' + id);
                    $('#fast_preview_modal').modal('show');
                }
            });
            $('.fast_comment').click(function () {
                var id = $(this).data('id');
                $('#fast_comment_modal').modal('show');
                /*if(fast_previews[id]===undefined){
                 $.ajax({
                 url: estate_folder+'/js/ajax.php?action=fast_preview&id='+id,
                 dataType: 'html',
                 success: function(html){
                 fast_previews[id]=html;
                 $('#fast_preview_modal').find('.modal-body').html(html);
                 $('#fast_preview_modal').modal('show');
                 }
                 });
                 }else{
                 $('#fast_preview_modal').find('.modal-body').html(fast_previews[id]);
                 $('#fast_preview_modal').modal('show');
                 }*/
            });
            $('.tagged').each(function () {
                var tag_input = $(this);
                var tag_array = [];
                var this_id = tag_input.attr('id')
                try {
                    tag_input.tag({
                        placeholder: tag_input.attr('placeholder'),
                        source: function (query, process) {
                            //console.log(query);
                            column_name = tag_input.attr('name');
                            $.ajax({url: estate_folder + '/js/ajax.php?action=get_tags&column_name=' + column_name + '&model_name=data&query=' + query}).done(function (result_items) {
                                process(result_items);
                            });
                        }
                    });
                    var tag_obj = tag_input.data('tag');
                    if (typeof column_values_for_tags[this_id] != 'undefined' && column_values_for_tags[this_id].length > 0) {
                        for (var i in column_values_for_tags[this_id]) {
                            tag_obj.add(column_values_for_tags[this_id][i]);
                            tag_array.push(column_values_for_tags[this_id][i]);
                            datastr[this_id] = tag_array;
                        }
                    }
                } catch (e) {
                    tag_input.after('<textarea id="' + tag_input.attr('id') + '" name="' + tag_input.attr('name') + '" rows="3">' + tag_input.val() + '</textarea>').remove();
                }
                tag_input.on('added', function (e, value) {
                    tag_array.push(value);
                    datastr[$(this).attr('name')] = tag_array;
                    $.ajax({url: estate_folder + '/js/ajax.php?action=get_tags&do=set&tags_array=' + JSON.stringify(datastr)}).done(function (result_items) {
                        window.location.href = window.location.href;
                    });
                })
                tag_input.on('removed', function (e, value) {
                    var item_index = datastr[$(this).attr('name')].indexOf(value);
                    datastr[$(this).attr('name')].splice(item_index, 1);
                    $.ajax({url: estate_folder + '/js/ajax.php?action=get_tags&do=set&tags_array=' + JSON.stringify(datastr)}).done(function (result_items) {
                        window.location.href = window.location.href;
                    });
                })
            });

            $('.ranged-tags').each(function (e) {
                var _this = $(this);
                var name = _this.data('field');
                _this.find('.ranged-tags-title').click(function (e) {
                    e.preventDefault();
                    _this.find('.ranged-tags-params').fadeToggle();
                });
                _this.find('.cancel').click(function (e) {
                    e.preventDefault();
                    _this.find('.ranged-tags-params').fadeToggle();
                });
                var min = null;
                var max = null;
                var txt = '{/literal}{_e t="не задано"}{literal}';

                _this.find('input').each(function (e) {
                    var iname = $(this).attr('name');
                    var val = $(this).val();
                    var tag_array = {};


                    var reg = /(.*)\[(.*)\]/;
                    var matches = $(this).attr('name').match(reg);
                    //console.log($(this).attr('name'));
                    if (typeof datastr[name] != 'undefined') {
                        tag_array = datastr[name];
                    }
                    if (val != '') {
                        tag_array[matches[2]] = val;
                    } else {
                        delete tag_array[matches[2]];
                    }
                    datastr[name] = tag_array;
                    if (iname == name + '[min]' && val != '') {
                        min = val;
                    }
                    if (iname == name + '[max]' && val != '') {
                        max = val;
                    }



                });

                if (min !== null && max !== null) {
                    var txt = min + ' - ' + max;
                } else if (min !== null) {
                    var txt = 'от ' + min;
                } else if (max !== null) {
                    var txt = 'до ' + max;
                }
                _this.find('.ranged-tags-title').html(txt);

                _this.find('.apply').click(function (e) {
                    e.preventDefault();
                    var tag_array = {};
                    var reg = /(.*)\[(.*)\]/;
                    if (typeof datastr[name] != 'undefined') {
                        tag_array = datastr[name];
                    }
                    _this.find('input').each(function () {
                        var val = $(this).val();
                        var matches = $(this).attr('name').match(reg);
                        if (typeof datastr[name] != 'undefined') {
                            tag_array = datastr[name];
                        }
                        if (val != '') {
                            tag_array[matches[2]] = val;
                        } else {
                            delete tag_array[matches[2]];
                        }

                        datastr[name] = tag_array;
                    });
                    $.ajax({url: estate_folder + '/js/ajax.php?action=get_tags&do=set&tags_array=' + JSON.stringify(datastr)}).done(function (result_items) {
                        location.reload();
                    });
                });

                _this.find('.clear').click(function (e) {
                    e.preventDefault();
                    if (typeof datastr[name] != 'undefined') {
                        tag_array = datastr[name];
                        delete datastr[name];
                    }
                    $.ajax({url: estate_folder + '/js/ajax.php?action=get_tags&do=set&tags_array=' + JSON.stringify(datastr)}).done(function (result_items) {
                        location.reload();
                    });
                });

            });

        });
    </script>
{/literal}


<div class="modal hide fade" id="fast_preview_modal">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3>Быстрый просмотр <a target="_blank" class="btn btn-success newwin" href="#">открыть в новом окне</a></h3>
    </div>
    <div class="modal-body"></div>
    <div class="modal-footer"></div>
</div>

<div class="modal hide fade" id="fast_comment_modal">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3>Быстрый просмотр</h3>
    </div>
    <div class="modal-body"></div>
    <div class="modal-footer"></div>
</div>

<div class="navbar">
    <div class="navbar-inner">
        <div class="container">
            <div class="nav total_find" style="color: #fff; font-size: 24px; padding: 10px 20px 10px 5px; font-size: 20px; font-weight: 200;">Найдено: {$_total_records}</div>
            
            <div class="nav pull-right">
                <div align="right"><a href="?action=data&do=import" title="Загрузить записи в формате Excel" class="btn btn-info btn-xs "><i class="icon-white icon-upload"></i> </a> <a href="#search" id="search_toggle" class="btn btn-info"><i class="icon-white icon-search"></i> {$L_ADVSEARCH}</a></div>
                <div id="search_form_block" {if $smarty.request.submit_search_form_block eq ''}style="display:none;"{/if} class="spacer-top">
                    <form action="?action=data" method="get">
                        <table>
                            <tr><td>{$L_WORD}</td><td> <input type="text" name="srch_word" value="{$smarty.request.srch_word}" /></td></tr>
                            <tr><td>{$L_PHONE}</td><td> <input type="text" name="srch_phone" value="{$smarty.request.srch_phone}" /></td></tr>
                            <tr><td>{$L_ID}</td><td> <input type="text" name="srch_id" value="{$smarty.request.srch_id}" /></td></tr>
                                    {if $show_uniq_id}
                                <tr><td>UNIQ_ID</td><td> <input type="text" name="uniq_id" value="{$smarty.request.uniq_id}" /></td></tr>
                                    {/if}
                            <tr><td>{$L_DATE} {$L_FROM}</td><td> <input type="text" name="srch_date_from" id="srch_date_from" value="{$smarty.request.srch_date_from}" /></td></tr>
                            <tr><td>{$L_DATE} {$L_TO}</td><td> <input type="text" name="srch_date_to" id="srch_date_to" value="{$smarty.request.srch_date_to}" /></td></tr>
                            {$custom_admin_search_fields}
                            <tr><td></td>
                                <td align="right">
                                    <input type="submit" name="submit_search_form_block" value="{$L_GO_FIND}" class="btn btn-primary" />
                                    <input type="button" id="reset" value="{$L_RESET}" class="btn btn-warning" /></td></tr>
                        </table>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<table class="table table-bordered dataTable new_admin_grid" >
    <thead>
        <tr>
            <th><input type="checkbox" class="grid_check_all ace" /><label for="grid_check_all" class="lbl"></label></th>
            <!-- th class="row_title"></th-->
            {if $admin_grid_leftbuttons==1}
                <th class="row_title"></th>
                {/if}
                {foreach from=$grid_data_columns item=grid_data_column}	
                <th {if $smarty.request.order eq $grid_data_column}class="sorting_{if $smarty.request.asc eq 'desc'}desc{else}asc{/if}"{else}class="sorting"{/if}  >
                    {if !in_array($core_model[$grid_data_column].type, array('uploads', 'docuploads', 'uploadify_image'))}
                        <!-- #section:plugins/input.tag-input -->
                        {if $core_model[$grid_data_column].type == 'price' || (isset($core_model[$grid_data_column]._rules) && ($core_model[$grid_data_column]._rules.Type=='int' || $core_model[$grid_data_column]._rules.Type=='decimal'))}

                            <div class="inline-tags">
                                {if 1==0} //сюда добавить выпадающий див для выбора параметров цены (от и до)
                                    //изначально пользователь видет Цена и рядом иконку редактирования
                                    //После нажатия выпадает форма для ввода диапазона от и до
                                    //После выбора диапазонов, к цене приписываем Цена от .. до ...{/if}
                                    <div class="ranged-tags" data-field="{$grid_data_column}">
                                        <div class="ranged-tags-title"></div>
                                        <div class="ranged-tags-params" style="display: none;">
                                            <input name="{$grid_data_column}[min]" type="text" class="tagged_input" value="{$smarty.session.tags_array[$grid_data_column].min}">
                                            <input name="{$grid_data_column}[max]" type="text" class="tagged_input" value="{$smarty.session.tags_array[$grid_data_column].max}">
                                            <a href="#" class="btn btn-danger clear" title="очистить фильтр"><i class="icon-remove"></i></a>
                                            <a href="#" class="btn btn-success apply" title="применить фильтр"><i class="icon-ok"></i></a>
                                            <a href="#" class="btn cancel" title="скрыть окно фильтра"><i class="icon-off"></i></a>
                                        </div>
                                    </div>
                                    {*$smarty.session.tags_array|print_r*}
                                </div>

                            {else}

                                <div class="inline-tags">
                                    <input type="text" name="{$grid_data_column}" id="{$grid_data_column}" class="input-tag tagged {$core_model[$grid_data_column].type}" value="" placeholder="..." />
                                </div>
                            {/if}
                            {if is_array($smarty.session.tags_array.{$grid_data_column})}
                                <script>
                                    if (column_values_for_tags['{$grid_data_column}'] === undefined) {
                                        column_values_for_tags['{$grid_data_column}'] = [];
                                    }
                                        {if !isset($smarty.session.tags_array.{$grid_data_column}['0'])}
                                    column_values_for_tags['{$grid_data_column}'] ={};
                                            {foreach from=$smarty.session.tags_array.{$grid_data_column} item=column_value key=column_value_key}
                                    column_values_for_tags['{$grid_data_column}']['{$column_value_key}'] = '{$column_value}';
                                        {/foreach}
                                    {else}
                                            {foreach from=$smarty.session.tags_array.{$grid_data_column} item=column_value}
                                    column_values_for_tags['{$grid_data_column}'].push('{$column_value}');
                                        {/foreach}
                                    {/if}

                                    //console.log(column_values_for_tags);
                                </script>
                            {/if}
                            <!-- /section:plugins/input.tag-input -->
                        {/if}
                        <a href="?action=data&admin=1&order={$grid_data_column}&asc={if $smarty.request.asc eq 'desc'}asc{else}desc{/if}">{if $core_model[$grid_data_column].title != ''}{$core_model[$grid_data_column].title}{else}{$grid_data_column}{/if}</a>
                    </th>
                    {/foreach}
                        {if $admin_grid_leftbuttons==0}
                            <th class="row_title"></th>
                            {/if}
                    </tr>
                    <tr>

                    </tr>
                </thead>
                {section name=i loop=$grid_items}
                    <tr valign="top" class="{if $grid_items[i].hot.value}row3hot{/if}{if intval($grid_items[i].status_id.value)>0} row_status_id{$grid_items[i].status_id.value}{/if}{if $grid_items[i].active.value == 0} notactive{/if}{if intval($grid_items[i].archived.value) === 1} archived{/if}{if $grid_items[i]._classes ne ''} {$grid_items[i]._classes}{/if}">

                        <td><input id="grid_check_all_{$grid_items[i].id.value}" type="checkbox" class="grid_check_one ace" value="{$grid_items[i].id.value}" /><label for="grid_check_all_{$grid_items[i].id.value}" class="lbl"></label></td>
                        <!-- td>
                                <button data-id="{$grid_items[i].id.value}" class="fast_preview btn btn-danger"><i class="icon-white icon-eye-open"></i></button> 
                                <button data-id="{$grid_items[i].id.value}" class="fast_comment btn btn-info"><i class="icon-white icon-eye-open"></i></button>
                        </td-->
                        {if $admin_grid_leftbuttons==1}
                            {include file="controls.tpl" grid_item=$grid_items[i]}
                        {/if}


                        {foreach from=$grid_data_columns item=grid_data_column}	
                            {if $grid_items[i][$grid_data_column].type=='uploadify_image' && is_array($grid_items[i][$grid_data_column].image_array) && $grid_items[i][$grid_data_column].image_array|count>0}

                                <td>
                                    <ul class="ace-thumbnails clearfix">
                                        <li>
                                            <a href="{$estate_folder}/img/data/{$grid_items[i][$grid_data_column].image_array[0].normal}">
                                                <img src="{$estate_folder}/img/data/{$grid_items[i][$grid_data_column].image_array[0].preview}" style="width: 40px; height: 40px;" />
                                            </a>
                                            <div class="tags">
                                                <span class="label-holder">
                                                    <span class="label label-info">{$grid_items[i][$grid_data_column].image_array|count}</span>
                                                </span>
                                            </div>
                                            <div class="tools tools-top">
                                                <a href="{$estate_folder}/img/data/{$grid_items[i][$grid_data_column].image_array[0].normal}"  data-rel="colorbox{$grid_items[i].id.value}" class="colorboxed" data-cbxid="{$grid_items[i].id.value}">
                                                    <i class="ace-icon fa fa-search-plus"></i>
                                                </a>
                                            </div>
                                        </li>
                                        {foreach from=$grid_items[i][$grid_data_column].image_array item=image key=k}
                                            {if $k != 0}
                                                <li style="display: none;">
                                                    <a href="{$estate_folder}/img/data/{$image.normal}"  data-rel="colorbox{$grid_items[i].id.value}"><img src="{$estate_folder}/img/data/{$image.preview}" width="50" /></a>
                                                </li>
                                            {/if}
                                        {/foreach}
                                    </ul>
                                </td>
                            {elseif $grid_items[i][$grid_data_column].type=='uploads'}
                                <td>
                                    {if is_array($grid_items[i][$grid_data_column].value) && !empty($grid_items[i][$grid_data_column].value)}
                                    <ul class="ace-thumbnails clearfix">
                                        <li>
                                            <a href="{if $grid_items[i][$grid_data_column].value[0].remote === 'true'}{$grid_items[i][$grid_data_column].value[0].normal}{else}{$estate_folder}/img/data/{$grid_items[i][$grid_data_column].value[0].normal}{/if}">
                                                <img src="{if $grid_items[i][$grid_data_column].value[0].remote === 'true'}{$grid_items[i][$grid_data_column].value[0].normal}{else}{$estate_folder}/img/data/{$grid_items[i][$grid_data_column].value[0].preview}{/if}" style="min-width: 40px; max-width: 100px;" />
                                            </a>
                                            <div class="tags">
                                                <span class="label-holder">
                                                    <span class="label label-info">{$grid_items[i][$grid_data_column].value|count}</span>
                                                </span>
                                            </div>
                                            <div class="tools tools-top">
                                                <a href="{if $grid_items[i][$grid_data_column].value[0].remote === 'true'}{$grid_items[i][$grid_data_column].value[0].normal}{else}{$estate_folder}/img/data/{$grid_items[i][$grid_data_column].value[0].normal}{/if}"  data-rel="colorbox{$grid_items[i].id.value}" class="colorboxed" data-cbxid="{$grid_items[i].id.value}">
                                                    <i class="ace-icon fa fa-search-plus"></i>
                                                </a>
                                            </div>
                                        </li>
                                        {foreach from=$grid_items[i][$grid_data_column].value item=image key=k}
                                            {if $k != 0}
                                                <li style="display: none;">
                                                    <a href="{if $image.remote === 'true'}{$image.normal}{else}{$estate_folder}/img/data/{$image.normal}{/if}"  data-rel="colorbox{$grid_items[i].id.value}"><img src="{if $image.remote === 'true'}{$image.normal}{else}{$estate_folder}/img/data/{$image.preview}{/if}" width="50" /></a>
                                                </li>
                                            {/if}
                                        {/foreach}
                                    </ul>
                                    {/if}
                                </td>
                            {elseif $grid_items[i][$grid_data_column].type=='geodata' && is_array($grid_items[i][$grid_data_column].value)}
                                <td>{$grid_items[i][$grid_data_column].value_string.lat}, {$grid_items[i][$grid_data_column].value_string.lng}</td>
                            {elseif $grid_items[i][$grid_data_column].type=='checkbox'}
                                <td><input type="radio" disabled="disabled" {if $grid_items[i][$grid_data_column].value==1}checked="checked"{/if}></td>
                            {elseif $grid_items[i][$grid_data_column].type=='primary_key'}
                                <td><a href="{$grid_items[i]._href}" target="_blank">{$grid_items[i][$grid_data_column].value_string}</a></td>
                            {elseif $grid_items[i][$grid_data_column].type=='select_by_query_multi'}
                                <td>{$grid_items[i][$grid_data_column].value_string|implode:', '}</td>
                            {else}
                                <td>{$grid_items[i][$grid_data_column].value_string}</td>
                            {/if}
                        {/foreach}

                        {if $admin_grid_leftbuttons==0}
                            {if $admin !=''}
                                {include file="controls.tpl" grid_item=$grid_items[i]}
                            {/if}
                        {/if}
                    </tr>
                {/section}
                <tfooter>
                <tr>
                    <td colspan="{3+$grid_data_columns|count}">
                        <button alt="data" class="delete_checked btn btn-danger"><i class="icon-white icon-remove"></i> {$L_DELETE_CHECKED}</button>
                        <button alt="data" class="batch_update btn btn-inverse"><i class="icon-white icon-th"></i> Пакетная обработка <sup>(beta)</sup></button> 
                        <button alt="data" class="duplicate btn btn-inverse"><i class="icon-white icon-th"></i> Дублировать <sup>(beta)</sup></button>
                        <div class="btn-group">
                            <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">Групповая обработка значений <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                {foreach from=$core_model item=column}
                                    {if $column.type=='price'}
                                    <li><a data-action="data" data-field="{$column.name}" href="#" class=" batch_field_edit">"{$column.title}" ({$column.name})</a></li>
                                    {/if}
                                {/foreach}
                            </ul>
                        </div>
                    </td>
                </tr>

                {if $pager != ''}
                    <tr>
                        <td colspan="{3+$grid_data_columns|count}" class="pager"><div align="center">{$pager}</div></td>
                    </tr>
                {/if}
                </tfooter>
            </table>
