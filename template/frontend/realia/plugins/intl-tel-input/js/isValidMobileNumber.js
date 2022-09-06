var mobile_input = document.querySelector("#simple_register input[name=mobile]"),
    mobile_phone_with_code = document.querySelector("#mobile_phone_with_code"),
    mobile_errorMsg = document.querySelector("#mobile_error-msg"),
    mobile_validMsg = document.querySelector("#mobile_valid-msg");

// here, the index maps to the error code returned from getValidationError - see readme
var mobile_errorMap = ["Invalid number", "Invalid country code", "Too short", "Too long", "Invalid number"];

var mobile_iti = window.intlTelInput(mobile_input, {
    utilsScript: "/template/frontend/realia/plugins/intl-tel-input/js/utils.js?1638200991544",
    initialCountry: "auto",
    hiddenInput: "full",
    separateDialCode: true,
    geoIpLookup: function(success, failure) {
        $.get("https://ipinfo.io", function() {}, "jsonp").always(function(resp) {
            var countryCode = (resp && resp.country) ? resp.country : "us";
            success(countryCode);
        });
    },
});

var mobile_reset = function() {
    mobile_input.classList.remove("error-intl");
    mobile_errorMsg.innerHTML = "";
    mobile_errorMsg.classList.add("hide-intl");
    mobile_validMsg.classList.add("hide-intl");
};

// on blur: validate
mobile_input.addEventListener('blur', function() {
    mobile_reset();
    if (mobile_input.value.trim()) {
        if (mobile_iti.isValidNumber()) {
            mobile_phone_with_code.value = mobile_iti.getNumber();
            mobile_validMsg.classList.remove("hide-intl");
        } else {
            mobile_phone_with_code.value = "";
            mobile_input.classList.add("error-intl");
            var errorCode = mobile_iti.getValidationError();
            mobile_errorMsg.innerHTML = mobile_errorMap[errorCode];
            mobile_errorMsg.classList.remove("hide-intl");
        }
    }
});

// on keyup / change flag: reset
mobile_input.addEventListener('change', mobile_reset);
mobile_input.addEventListener('keyup', mobile_reset);
