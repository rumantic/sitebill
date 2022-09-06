var input = document.querySelector("#phone"),
    phone_with_code = document.querySelector("#phone_with_code"),
  errorMsg = document.querySelector("#error-msg"),
  validMsg = document.querySelector("#valid-msg");

// here, the index maps to the error code returned from getValidationError - see readme
var errorMap = ["Invalid number", "Invalid country code", "Too short", "Too long", "Invalid number"];

// initialise plugin
var iti = window.intlTelInput(input, {
  utilsScript: "/template/frontend/realia/plugins/intl-tel-input/js/utils.js?1638200991544",
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
  input.classList.remove("error-intl");
  errorMsg.innerHTML = "";
  errorMsg.classList.add("hide-intl");
  validMsg.classList.add("hide-intl");
};

// on blur: validate
input.addEventListener('blur', function() {
  reset();
  if (input.value.trim()) {
    if (iti.isValidNumber()) {
      phone_with_code.value = iti.getNumber();
      validMsg.classList.remove("hide-intl");
    } else {
      phone_with_code.value = "";
      input.classList.add("error-intl");
      var errorCode = iti.getValidationError();
      errorMsg.innerHTML = errorMap[errorCode];
      errorMsg.classList.remove("hide-intl");
    }
  }
});

// on keyup / change flag: reset
input.addEventListener('change', reset);
input.addEventListener('keyup', reset);
