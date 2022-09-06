$(document).ready(function () {
    $('#_lang').change(function () {
        $(this).parents('form').eq(0).submit();
    });

    $('#prettyLogin').on('hidden', function () {
        $(this).find('[type=text]').val('');
        $(this).find('[type=password]').val('');
        $(this).find('.error_mark').hide();
        $(this).find('.error').hide();
    })

    $('#prettyLogin #login_button').click(function () {
        var form = $(this).parents('form').eq(0);
        var login = form.find('[name=login]').val();
        var password = form.find('[name=password]').val();
        var rememberme = form.find('[name=rememberme]').prop('checked');
        var errorblock = form.find('.error');
        errorblock.hide();

        if (rememberme) {
            rememberme = 1;
        } else {
            rememberme = 0;
        }


        if (login == '' || password == '') {
            errorblock.show();
        } else {
            $.getJSON(estate_folder + '/js/ajax.php?action=ajax_login', {
                login: login,
                password: password,
                rememberme: rememberme
            }, function (data) {
                if (data.response.body == 'Authorized') {
                    let new_location = document.location.href.replace('remind/', '');
                    new_location = new_location.replace('remind', '')
                    document.location.href = new_location;
                } else {
                    errorblock.show();
                }
            });
        }

        return false;
    });

    $('#prettyLogin #confirm_code').click(function () {
        var data = [];
        var form = $(this).parents('form').eq(0);
        var sms_code = form.find('input[name=sms_code]').val();
        var errormsg = form.find('.error');
        errormsg.html('');


        $.ajax({
            type: 'post',
            url: estate_folder + '/js/ajax.php?action=ajax_activate_sms',
            data: 'activation_code=' + sms_code,
            success: function (text) {
                console.log(text);
                if (text == 'wrong_sms_code') {
                    errormsg.append($('<p>Неверный код активации</p>')).show();


                } else {
                    $('#prettyLogin').modal('hide');
                    $('#prettyRegisterOk').modal('show');
                }
            }
        });

        return false;
    });



    $('#prettyLogin #send_sms_confirm_code').click(function (e) {
        var data = {};
        var form = $(this).parents('form').eq(0);
        var user_id = form.find('input[name=user_id]').val();
        var confirm_code = form.find('input[name=confirm_code]').val();
        var errormsg = form.find('.error');
        var step_one = $('#step_one');
        var step_two = $('#step_two');
        var step_three = $('#step_three');
        errormsg.html('');
        data = {
            action: 'register',
            do: 'check_sms_confirm_code',
            user_id: user_id,
            confirm_code: confirm_code,
            anonymous: true
        };

        console.log(data);

        $.ajax({
            type: 'post',
            url: estate_folder + '/apps/api/rest.php',
            data: data,
            success: function (response) {
                const result = $.parseJSON(response);
                if ( result.state == 'error' ) {
                    errormsg.append($('<p>'+result.message+'</p>')).show();
                } else {
                    step_two.hide();
                    step_three.fadeIn();
                    setTimeout(function () {
                        location.reload(true);
                    }, 1000);
                }

                console.log(result);
            }
        });

        return false;
    });


    $('#prettyLogin #send_sms').click(function (e) {
        var data = {};
        var form = $(this).parents('form').eq(0);
        var phone_number = form.find('input[name=phone_number]').val();
        var phone_with_code = form.find('input[name=phone_with_code]').val();
        var password = form.find('input[name=password]').val();
        var fio = form.find('input[name=fio]').val();
        var user_id_input = form.find('input[name=user_id]');
        var errormsg = form.find('.error');
        var step_one = $('#step_one');
        var step_two = $('#step_two');
        var step_three = $('#step_three');
        errormsg.html('');
        data = {
            action: 'register',
            do: 'register_phone_number',
            phone_number: phone_with_code.replace("+", ""),
            password: password,
            fio: fio,
            anonymous: true
        };

        $.ajax({
            type: 'post',
            url: estate_folder + '/apps/api/rest.php',
            data: data,
            success: function (response) {
                const result = $.parseJSON(response);
                if ( result.state == 'error' ) {
                    errormsg.append($('<p>'+result.message+'</p>')).show();
                } else {
                    user_id_input.val(result.data.user_id);
                    step_one.hide();
                    step_two.fadeIn();
                }

                console.log(result);
            }
        });

        return false;
    });


    $('#prettyLogin #register_button').click(function () {
        var errors = false;

        var form = $(this).parents('form').eq(0);
        var errormsg = form.find('.error');
        var errorblock = form.find('.error_mark');
        var els = form.find('div.el:has(span.required)');
        errorblock.hide();
        errormsg.text('').hide();
        form.find('div.el:has(span.required)').each(function () {
            var field = $(this).find('input');
            if (field.attr('type') == 'checkbox' && !field.prop('checked')) {
                errors = true;
                $(this).find('.error_mark').show();
            } else if (field.attr('type') != 'checkbox' && field.val() == '') {
                errors = true;
                $(this).find('.error_mark').show();
            }

        });
        var login = form.find('input[name=login]').val();
        login = login.trim();


        if (login != '') {
            var re = /^([a-zA-Z0-9-_@\.]*)$/i;
            found = login.match(re);
            if (found === null) {
                errors = true;
                errormsg.append($('<p>Логин может содержать только латинские буквы, цифры, подчеркивание, тире</p>')).show();
            }
        }


        var password = form.find('input[name=newpass]').val();
        var password_retype = form.find('input[name=newpass_retype]').val();
        if (password && password_retype) {
            if (password != '' && password_retype != '' && password != password_retype) {
                errors = true;
                errormsg.append($('<p>Пароли не совпадают</p>')).show();
                form.find('input[name=newpass]').nextAll('.error_mark').eq(0).show();
                form.find('input[name=newpass_retype]').nextAll('.error_mark').eq(0).show();
            }
        }
        var email = form.find('input[name=email]').val();
        if (email != '' && !SitebillCore.isValidEmail(email)) {
            errors = true;
            errormsg.append($('<p>Укажите правильный email</p>')).show();
        }


        if (!errors) {
            var data = [];
            form.find('div.el').each(function () {
                var field = $(this).find('input').each(function () {
                    if ($(this).attr('name') == 'mobile' && $('#mobile_phone_with_code')) {
                        data.push($(this).attr('name') + '=' + $('#mobile_phone_with_code').val());
                    } else {
                        data.push($(this).attr('name') + '=' + $(this).val());
                    }
                });
                var field = $(this).find('select').each(function () {
                    data.push($(this).attr('name') + '=' + $(this).val());
                });
                var field = $(this).find('textarea').each(function () {
                    data.push($(this).attr('name') + '=' + $(this).val());
                });

            });
            $.ajax({
                type: 'post',
                url: estate_folder + '/js/ajax.php?action=ajax_register',
                data: data.join('&'),
                success: function (text) {
                    if (text == 'confirm_sms_code') {
                        $('#register_1').hide();
                        $('#confirm_sms_code_block').show();
                        $('#confirm_sms_code_block').css('display', 'block');

                    } else if (text != 'ok') {
                        errormsg.append($('<p>' + text + '</p>')).show();
                    } else {
                        $('#prettyLogin').modal('hide');
                        $('#prettyRegisterOk').modal('show');
                    }
                }
            });

        }
        return false;
    });

    $('#prettyRegisterOk .let_me_login').click(function () {
        $('#prettyRegisterOk').modal('hide');
        $('#prettyLogin a[href="#profile"]').tab('show');
        $('#prettyLogin').modal('show');
    });

    if ($('#search_forms_tabs').length > 0) {
        var active = $('#search_forms_tabs li.active');
        if (active.length == 0) {
            var first = $('#search_forms_tabs li:first');
            var idn = first.find('a').attr('href').replace('#', '');
            first.addClass('active');
            $('#' + idn).addClass('active');
        }
    }

    var wh = $(window).height();
    $('#prettyLogin .tab-content').css({'max-height': (0.55 * wh) + 'px'});

    $(document).on('click', 'a.fav-add', function (e) {
        var o = $(this);
        var id = o.attr('alt');
        if (id) {
            $.ajax({
                url: estate_folder + "/js/ajax.php?action=add_to_favorites",
                data: 'id=' + id,
                type: "POST",
                dataType: "json",
                success: function (json) {
                    if (json.response.body == 'OK') {
                        var favcount = $('#favorites_count');
                        if (favcount.length > 0) {
                            favcount.text(new Number($('#favorites_count').text()) + 1);
                        }
                        o.removeClass('fav-add').addClass('fav-rem');
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {

                }
            });
        }
        e.preventDefaults();
    });

    $(document).on('click', 'a.fav-rem', function (e) {
        var o = $(this);
        var id = o.attr('alt');
        if (id) {
            $.ajax({
                url: estate_folder + "/js/ajax.php?action=remove_from_favorites",
                data: 'id=' + id,
                type: "POST",
                dataType: "json",
                success: function (json) {
                    if (json.response.body == 'OK') {
                        var favcount = $('#favorites_count');
                        if (favcount.length > 0) {
                            favcount.text(new Number($('#favorites_count').text()) - 1);
                        }
                        o.removeClass('fav-rem').addClass('fav-add');
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {

                }
            });
        }
        e.preventDefaults();
    });
});
