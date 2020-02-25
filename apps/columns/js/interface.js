$(document).ready(function () {
    $.fn.columnform = function () {

        var form = this;

        var sql_template = 'SELECT * FROM re_[table]';
        var select = this.find('select[name=type]');

        var query_field = form.find('[name=query]');
        query_field.after('<div class="showquery">Показать запрос</div>')
        query_field.hide();
        $(document).on('click', '.showquery', function(){
            $(this).text('Спрятать запрос').removeClass('showquery').addClass('hidequery');
            query_field.show();
        });
        $(document).on('click', '.hidequery', function(){
            $(this).text('Показать запрос').removeClass('hidequery').addClass('showquery');
            query_field.hide();
        });

        /*form.find('select, input').on('change', function () {
            displayPreview();
        });*/


        var controls = [];
        var common_fields = ['name', 'title', 'value', 'type', 'required', 'unique', 'dbtype', 'active_in_topic', 'active_in_topic[]', 'tab', 'hint', 'parameters[name][]', 'parameters[value][]', 'uaction'];
        if (langs !== undefined) {
            for (var i = 0, l = langs.length; i < l; i++) {
                common_fields.push('title_' + langs[i]);
                common_fields.push('hint_' + langs[i]);
                common_fields.push('tab_' + langs[i]);
                //common_fields.push('hint_'+list[i]);
                //common_fields.push('hint_'+list[i]);
            }
        }

        controls['gadres'] = common_fields;
        controls['select_entity'] = common_fields;
        controls['date'] = common_fields;
        controls['uploads'] = common_fields.concat(['table_name', 'primary_key']);
        controls['docuploads'] = common_fields.concat(['table_name', 'primary_key']);
        controls['captcha'] = common_fields;
        //controls['datetime']=common_fields;
        controls['dtdatetime'] = common_fields;
        controls['dtdate'] = common_fields;
        controls['dttime'] = common_fields;
        controls['primary_key'] = common_fields;
        controls['password'] = common_fields;
        controls['photo'] = common_fields;
        controls['safe_string'] = common_fields;
        controls['hidden'] = common_fields;
        controls['checkbox'] = common_fields;
        controls['structure'] = common_fields.concat(['entity']);
        controls['select_box_structure'] = common_fields.concat(['value_string', 'title_default']);
        if (langs !== undefined) {
            for (var i = 0, l = langs.length; i < l; i++) {
                controls['select_box_structure'].push('title_default_' + langs[i]);
            }
        }
        controls['select_by_query'] = common_fields.concat(['primary_key_name', 'primary_key_table', 'value_string', 'query', 'value_name', 'title_default', 'value_default', 'combo']);
        if (langs !== undefined) {
            for (var i = 0, l = langs.length; i < l; i++) {
                controls['select_by_query'].push('title_default_' + langs[i]);
            }
        }
        controls['select_box'] = common_fields.concat(['select_data']);
        if (langs !== undefined) {
            for (var i = 0, l = langs.length; i < l; i++) {
                controls['select_box'].push('select_data_' + langs[i]);
            }
        }
        controls['grade'] = common_fields.concat(['select_data']);
        if (langs !== undefined) {
            for (var i = 0, l = langs.length; i < l; i++) {
                controls['grade'].push('select_data_' + langs[i]);
            }
        }
        controls['select_by_query_multi'] = common_fields.concat(['primary_key_name', 'primary_key_table', 'value_string', 'query', 'value_name', 'title_default', 'value_default']);
        if (langs !== undefined) {
            for (var i = 0, l = langs.length; i < l; i++) {
                controls['select_by_query_multi'].push('title_default_' + langs[i]);
            }
        }
        controls['auto_add_value'] = common_fields.concat(['value_table', 'value_primary_key', 'value_field', 'assign_to']);
        controls['price'] = common_fields;
        controls['textarea'] = common_fields;
        controls['textarea_editor'] = common_fields;
        controls['uploadify_image'] = common_fields.concat(['primary_key', 'primary_key_value', 'action', 'table_name']);
        controls['mobilephone'] = common_fields;
        controls['geodata'] = common_fields;
        controls['attachment'] = common_fields;
        controls['tlocation'] = common_fields;


        //var x=['name','title','value','primary_key_name','primary_key_table','value_string','query','value_name','title_default','value_default','type','required','unique','sort_order','value_table','value_primary_key','value_field','assign_to','dbtype','table_name','primary_key','primary_key_value','action','select_data'];
        function inArray(needle, haystack) {
            var length = haystack.length;
            for (var i = 0; i < length; i++) {
                if (haystack[i] == needle)
                    return true;
            }
            return false;
        }

        form.find('select[name=primary_key_table]').change(function () {
            var table_name = $(this).val();
            $.ajax({
                url: estate_folder + '/apps/table/js/ajax.php',
                type: 'POST',
                dataType: 'html',
                data: 'action=get_table_fields_select&table_name=' + table_name,
                success: function (data) {
                    var sb1 = $('<select>').attr('name', 'primary_key_name').append($(data));
                    var sb2 = $('<select>').attr('name', 'value_name').append($(data));

                    form.find('[name=primary_key_name]').replaceWith(sb1);
                    form.find('[name=value_name]').replaceWith(sb2);
                    form.find('[name=query]').val(sql_template.replace('[table]', table_name));
                    //form.find('input[name=value_name]').replaceWith(sb2);
                }
            });
        });

        form.find('.ait_bc_h input[type=checkbox]').change(function () {
            var parent = $(this).parents('.ait_bc').eq(0);
            if ($(this).is(':checked')) {
                parent.find('input[type=checkbox]').prop('checked', true);
            }
        });

        form.find('.checkbox_collection_decheck').click(function () {
            form.find('.ait_bc input[type=checkbox]').prop('checked', false);
            return false;
        });

        var prepare = function (new_val) {
            var inputs = form.find('input').not(':submit').not('input[name=active]');
            var selects = form.find('select').not('#group_id').not('#table_id');
            var textareas = form.find('textarea').not('#group_id').not('#table_id');
            inputs.each(function () {
                $(this).parents('tr').show();
                $(this).parents('.form_element').show();
                if (!inArray($(this).attr('name'), controls[new_val])) {
                    $(this).parents('tr').hide();
                    $(this).parents('.form_element').hide();
                }
            });
            selects.each(function () {
                $(this).parents('tr').show();
                $(this).parents('.form_element').show();
                //console.log(this);
                if (!inArray($(this).attr('name'), controls[new_val])) {
                    $(this).parents('tr').hide();
                    $(this).parents('.form_element').hide();
                }
            });
            textareas.each(function () {
                $(this).parents('tr').show();
                $(this).parents('.form_element').show();
                //console.log(this);
                if (!inArray($(this).attr('name'), controls[new_val])) {
                    $(this).parents('tr').hide();
                    $(this).parents('.form_element').hide();
                }
            });

        }

        var displayPreview = function () {
            var data = [];
            var inputs = form.find('input').not(':submit').not('input[name=active]');
            var selects = form.find('select').not('#group_id').not('#table_id');
            inputs.each(function () {
                data.push($(this).attr('name') + ':' + $(this).val());
            });
            selects.each(function () {
                data.push($(this).attr('name') + ':' + $(this).val());
            });
            $.ajax({
                url: estate_folder + '/apps/table/js/ajax.php',
                type: 'POST',
                dataType: 'html',
                data: 'action=get_preview&data=' + data.join('|'),
                success: function (data) {
                    $('#element_preview_c').html('<table>' + data + '</table>');
                    $('#_element_preview').html('<table>' + data + '</table>');
                }
            });
        }

        prepare(select.val());

        select.change(function () {
            prepare($(this).val());
        });


        //displayPreview();
        return this;

    };

    $('#column_form').columnform();

});