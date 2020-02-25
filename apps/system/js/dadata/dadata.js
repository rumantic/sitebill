function join(arr /*, separator */) {
    var separator = arguments.length > 1 ? arguments[1] : ", ";
    return arr.filter(function (n) {
        return n
    }).join(separator);
}

function geoQuality(qc_geo) {
    var localization = {
        "0": "точные",
        "1": "ближайший дом",
        "2": "улица",
        "3": "населенный пункт",
        "4": "город"
    };
    return localization[qc_geo] || qc_geo;
}

function geoLink(address) {
    return join(["<a target=\"_blank\" href=\"",
        "https://maps.yandex.ru/?text=",
        address.geo_lat, ",", address.geo_lon, "\">",
        address.geo_lat, ", ", address.geo_lon, "</a>"], "");
}

function showPostalCode(address) {
    $("#postal_code").val(address.postal_code);
}

function showRegion(address) {
    $("input[name='geoautocomplete[region_id]']").val(address.region_with_type);
}

function showCity(address) {
    //console.log(address);
    if (address.settlement_with_type !== null) {
        $("input[name='geoautocomplete[city_id]']").val(address.settlement_with_type);
    } else {
        $("input[name='geoautocomplete[city_id]']").val(join([
            join([address.city], " ")
        ]));

    }
}

function showDistrict(address) {
    $("input[name='geoautocomplete[district_id]']").val(
            join([address.city_district], " ")
            );
}
function showStreet(address) {
    $("input[name='geoautocomplete[street_id]']").val(
            join([address.street], " ")
            );
}


function showHouse(address) {
    //console.log(address);
    $("input[name='number']").val(join([
        join([address.house], " "),
        join([address.block_type, address.block], " ")
    ]));
}

function showFlat(address) {
    $("#flat").val(
            join([address.flat_type, address.flat], " ")
            );
}

function showGeo(address) {
    //if (address.qc_geo == "0") {
    $("input[name='geo[lat]']").val(address.geo_lat);
    $("input[name='geo[lng]']").val(address.geo_lon).trigger('change');
    //}
}

function showSelected(suggestion) {
    var address = suggestion.data;
    showPostalCode(address);
    showRegion(address);
    showCity(address);
    showDistrict(address);
    showStreet(address);
    showHouse(address);
    showFlat(address);
    showGeo(address);
}

