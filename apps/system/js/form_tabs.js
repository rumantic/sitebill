$(document).ready(function () {
    $('.form_tab').hide().first().show();
    $('#form_tab_switcher a').removeClass('active_tab_link').first().addClass('active_tab_link');

    $('#form_tab_switcher a').click(function () {
        //console.log($(this).attr('href'));
        var tab_id = $(this).attr('href');
        $('.form_tab').hide();
        $('#' + tab_id).show();
        $('#form_tab_switcher a').removeClass('active_tab_link');
        $(this).addClass('active_tab_link');
        return false;
    });

    $('#form_tab_switcher a.active_tab').trigger('click');

    $('a.go_to_step').click(function () {
        var form = $('form#step_form');
        var step = $(this).attr('alt');
        var action = form.attr('action');
        var expr = /step(\d+)/;
        action = action.replace(expr, 'step' + step);
        form.attr('action', action);

        var old_action = form.find('input[name=do]').val();
        if (old_action == 'new') {
            form.find('input[name=do]').val('new');
        } else if (old_action == 'edit') {
            form.find('input[name=do]').val('edit');
        } else if (old_action == 'edit_done') {
            form.find('input[name=do]').val('edit');
        } else if (old_action == 'new_done') {
            form.find('input[name=do]').val('new');
        } else {
            form.find('input[name=do]').val('new');
        }
        //form.find('#formsubmit').parents('form').eq(0).submit();

        form.find('#formsubmit').removeAttr("disabled").trigger('click');

        return false;
    });

    $('#formsubmit_back').click(function () {
        var form = $(this).parents('form').eq(0);
        var step = $(this).attr('alt');
        var action = form.attr('action');
        var expr = /step(\d+)/;
        action = action.replace(expr, 'step' + step);
        form.attr('action', action);

        var old_action = form.find('input[name=do]').val();
        if (old_action == 'new') {
            form.find('input[name=do]').val('new');
        } else if (old_action == 'edit') {
            form.find('input[name=do]').val('edit');
        } else if (old_action == 'edit_done') {
            form.find('input[name=do]').val('edit');
        } else if (old_action == 'new_done') {
            form.find('input[name=do]').val('new');
        } else {
            form.find('input[name=do]').val('new');
        }
        //return false;
        //form.find('input[name=do]').val('new');
        form.submit();
        //form.find('#formsubmit').trigger('click');

        return false;
    });

    var form_field_view_topic = {};

    $.ajax({
        url: estate_folder + '/js/ajax.php?action=get_form_fields_rules',
        dataType: 'json',
        success: function (json) {
            form_field_view_topic = json;
            checkFormFieldsVisibility($('#topic_id').val(), form_field_view_topic, $('#topic_id').parents('form').eq(0));
        }
    });

    $('form #topic_id').each(function () {
        var parent = $(this).parents('form').eq(0);
        $(this).change(function () {
            var current_topic_id = $(this).val();
            checkFormFieldsVisibility(current_topic_id, form_field_view_topic, parent);
        });
        if (parent.find('[name=optype]').length > 0) {
            parent.find('[name=optype]').change(function () {
                var current_topic_id = $(this).val();
                var parent = $(this).parents('form').eq(0);
                checkFormFieldsVisibility(current_topic_id, form_field_view_topic, parent);
            });
        }
    });


    $('.leveled').each(function () {
        $(this).StructureLvl();
    });

    $('.f_intval').keyup(function () {
        var v = $(this).val();
        v = parseInt(v, 10);
        if (isNaN(v) || v < 1) {
            v = '';
        }
        $(this).val(v);
    });
    $('.f_decimal').keyup(function () {
        var v = $(this).val();
        v = parseFloat(v, 10);
        if (isNaN(v) || v < 1) {
            v = '';
        }
        $(this).val(v);
    });

});



(function ($) {
    if (typeof StructureLvl === 'undefined' || !$.isFunction(StructureLvl)) {
        jQuery.fn.StructureLvl = function () {
            var el = $(this);
            if (el.length == 0) {
                return;
            }
            if (el.data('leveled') == 'leveled') {
                return el;
            }
            el.data('leveled', 'leveled');
            var inp = el.find('input[type=hidden]');
            el.find('select').change(function () {
                reset($(this));
            });
            initEl();

            function initEl(){
				el.find('select').each(function(){
                    var tid=parseInt($(this).find('option:selected').attr('value'), 10);
                    if(tid>0){
						$(this).parents('.levelitem').eq(0).show();
						el.find('.levelitem_'+tid).show();
					}else{
						el.find('.levelitem_0').show();
					}
                });
			};
			function reset(sel){
				var tid=parseInt(sel.val(), 10);
				if(isNaN(tid)){
					tid=0;
				}
                var level=sel.parents('.level').eq(0);
				//level.nextAll('.level').find('select').val(0).hide();
                level.nextAll('.level').find('.levelitem').val(0).hide();
				if(tid>0){
					el.find('.levelitem_'+tid).show();
					setVal(tid);
				}else{
					var prev_el = sel.parents('.levelitem').eq(0);
                    if(prev_el.length > 0){
                       setVal(parseInt(prev_el.data('id'), 10));
                    }
				}
				
			};
			function setVal(val){
				inp.val(val);
				inp.trigger('change');
			};

            return el;
        };
    }

})(jQuery);

function checkFormFieldsVisibility(current_topic_id, topic_array, context) {
    var current_topic_id = context.find('#topic_id').val();
    if (isNaN(current_topic_id)) {
        current_topic_id = 0;
    }
    current_topic_id = String(current_topic_id);

    var current_optype_id = context.find('[name=optype]').val();
    if (isNaN(current_optype_id)) {
        current_optype_id = 0;
    }
    current_optype_id = String(current_optype_id);

    //console.log(topic_array);

    if (typeof topic_array != 'undefined') {
        for (var key in topic_array) {
            var vis = true;
            if (topic_array[key].topic_id[0] == 'all') {
                vis = vis && true;
            } else if ($.inArray(current_topic_id, topic_array[key].topic_id) === -1) {
                vis = vis && false;
            }
            if (typeof topic_array[key].optype != 'undefined') {
                if (topic_array[key].optype[0] == 'all') {
                    vis = vis && true;
                } else if ($.inArray(current_optype_id, topic_array[key].optype) === -1) {
                    vis = vis && false;
                }
            }
            if (!vis) {
                context.find('[alt=' + key + ']').hide();
            } else {
                context.find('[alt=' + key + ']').show();
            }
        }
    }

}