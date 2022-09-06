<style>
    #{{$id}}error-msg {
        color: red;
    }
    #{{$id}}valid-msg {
        color: #00C900;
    }
    input.error-intl {
        border: 1px solid #FF7C7C;
    }
    .hide-intl {
        display: none;
    }
</style>
<script>
    var input{{$id}} = document.querySelector("#{{$id}}_intl"),
            phone_with_code{{$id}} = document.querySelector("#{{$id}}"),
            errorMsg{{$id}} = document.querySelector("#{{$id}}error-msg"),
            validMsg{{$id}} = document.querySelector("#{{$id}}valid-msg"),
            confirm_mobile_div{{$id}} = document.querySelector("#{{$id}}_confirm_mobile");
    var confirm_mobile_flag = {{$item_array['parameters']['confirm_mobile'] == 1? 'true': 'false'}};

    // here, the index maps to the error code returned from getValidationError - see readme
    var errorMap = [
        "{{_e('Неправильный номер')}}",
        "{{_e('Неверный код страны')}}",
        "{{_e('Слишком короткий')}}",
        "{{_e('Слишком длинный')}}",
        "{{_e('Неправильный номер')}}"
    ];

    // initialise plugin
    var iti{{$id}} = window.intlTelInput(input{{$id}}, {
        utilsScript: "{{SITEBILL_MAIN_URL}}/apps/system/js/intl-tel-input/js/utils.js",
        initialCountry: "auto",
        separateDialCode: true,
        hiddenInput: "full",
        geoIpLookup: function(success, failure) {
            $.get("https://ipinfo.io", function() {}, "jsonp").always(function(resp) {
                var countryCode = (resp && resp.country) ? resp.country : "us";
                success(countryCode);
            });
        },
    });

    var reset = function() {
        input{{$id}}.classList.remove("error-intl");
        errorMsg{{$id}}.innerHTML = "";
        errorMsg{{$id}}.classList.add("hide-intl");
        validMsg{{$id}}.classList.add("hide-intl");
    };

    var send_confirm_code = function() {
        let phone = iti{{$id}}.getNumber().replace("+", "");
        let code = document.querySelector("#{{$id}}_confirm_code").value;
        let confirm_code_error = document.querySelector("#{{$id}}_confirm_code_error");

        let body = {
            action: 'register',
            do: 'simple_validate_confirm_code',
            phone_number: phone,
            code: code,
            anonymous: true
        };
        confirm_code_error.innerHTML = '';

        $.ajax({
            url: estate_folder+'/apps/api/rest.php',
            data: body,
            type: 'post',
            dataType: 'text',
            success: function(text){
                const result = $.parseJSON(text);
                if ( result.state == 'success' ) {
                    confirm_mobile_div{{$id}}.innerHTML = '<span style="color: green;">'+'{{_e('Номер подтвержден')}}'+'</span>';
                    phone_with_code{{$id}}.value = phone;
                } else {
                    confirm_code_error.innerHTML = result.message;
                }
            }
        });


    }

    var send_sms = function(event) {
        let phone_with_code = iti{{$id}}.getNumber().replace("+", "");

        let body = {
            action: 'register',
            do: 'simple_send_confirm_code',
            phone_number: phone_with_code,
            anonymous: true
        };

        $.ajax({
            url: estate_folder+'/apps/api/rest.php',
            data: body,
            type: 'post',
            dataType: 'text',
            success: function(text){
                const result = $.parseJSON(text);
                if ( result.state == 'success' ) {
                    confirm_mobile_div{{$id}}.innerHTML =
                        '<div style="margin-top: 8px;">' +
                        '<input type="text" name="confirm_code" id="{{$id}}_confirm_code" placeholder="{{_e('Введите код из SMS')}}"> ' +
                        '<a class="btn btn-success btn-small" id="{{$id}}_confirm_code_button">{{_e('Подтвердить')}}</a>' +
                        '</div>' +
                        '<div style="margin-top: 8px; color: red;" id="{{$id}}_confirm_code_error"></div>'
                    ;

                    let {{$id}}_confirm_code_button = document.querySelector("#{{$id}}_confirm_code_button");
                    {{$id}}_confirm_code_button.addEventListener('click', send_confirm_code);

                } else {
                    confirm_mobile_div{{$id}}.innerHTML = result.message;
                }
            }
        });
    };

    // on blur: validate
    input{{$id}}.addEventListener('blur', function() {
        reset();
        if (input{{$id}}.value.trim()) {
            if (iti{{$id}}.isValidNumber()) {
                if ( confirm_mobile_flag ) {
                    confirm_mobile_div{{$id}}.innerHTML = '<a class="btn btn-info btn-small" id="{{$id}}_confirm_send_button" style="margin-top: 8px;">{{_e('Подтвердить номер по SMS')}}</a>';
                    let {{$id}}_confirm_send_button = document.querySelector("#{{$id}}_confirm_send_button");
                    {{$id}}_confirm_send_button.addEventListener('click', send_sms);
                } else {
                    phone_with_code{{$id}}.value = iti{{$id}}.getNumber().replace("+", "");
                    validMsg{{$id}}.classList.remove("hide-intl");
                }
            } else {
                phone_with_code{{$id}}.value = "";
                input{{$id}}.classList.add("error-intl");
                var errorCode = iti{{$id}}.getValidationError();
                errorMsg{{$id}}.innerHTML = errorMap[errorCode];
                errorMsg{{$id}}.classList.remove("hide-intl");
            }
        }
    });

    // on keyup / change flag: reset
    input{{$id}}.addEventListener('change', reset);
    input{{$id}}.addEventListener('keyup', reset);
</script>
