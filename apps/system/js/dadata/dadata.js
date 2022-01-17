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
    var el = $("input[name='geoautocomplete[region_id]']");
    if(el.length > 0){
        el.each(function(){
            $(this).val(
                join([address.region_with_type], " ")
            );
            $(this).parents('.geoautocomplete_block').eq(0).find("input[name='region_id']").val('');
        });
    }
}

function showCity(address) {
    var cname = '';
    if (address.settlement_with_type !== null) {
        cname = address.settlement_with_type;
    } else {
        cname = join([address.city], " ");
    }
    var el = $("input[name='geoautocomplete[city_id]']");
    if(el.length > 0){
        el.each(function(){
            $(this).val(cname);
            $(this).parents('.geoautocomplete_block').eq(0).find("input[name='city_id']").val('');
        });
    }else{
        var el = $("select[name='city_id']");
        if(el.length > 0){
            el.each(function(){

                var sel = $(this);
                sel.find('option').each(function(){
                    if(cname == $(this).text()){
                        sel.val($(this).attr('value'));
                    }
                });
            });
        }
    }
}

function showDistrict(address) {
    var cname = join([address.city_district], " ");
    var el = $("input[name='geoautocomplete[district_id]']");
    if(el.length > 0){
        el.each(function(){
            $(this).val(cname);
            $(this).parents('.geoautocomplete_block').eq(0).find("input[name='district_id']").val('');
        });
    }else{
        var el = $("select[name='district_id']");
        if(el.length > 0){
            el.each(function(){
                var sel = $(this);
                sel.find('option').each(function(){
                    if(cname == $(this).text()){
                        sel.val($(this).attr('value'));
                    }
                });
            });
        }
    }
}
function showStreet(address) {
    var cname = join([address.street], " ");
    var el = $("input[name='geoautocomplete[street_id]']");
    if(el.length > 0){
        el.each(function(){
            $(this).val(cname);
            $(this).parents('.geoautocomplete_block').eq(0).find("input[name='street_id']").val('');
        });
    }else{
        var el = $("select[name='street_id']");
        if(el.length > 0){
            el.each(function(){
                var sel = $(this);
                sel.val(0);
                sel.find('option').each(function(){
                    if(cname == $(this).text()){
                        sel.val($(this).attr('value'));
                    }
                });
            });
        }
    }
    /*$("input[name='geoautocomplete[street_id]']").val(
            join([address.street], " ")
            );*/
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

    $("input[name='kvartira']").val(
        address.flat
    );

    $("input[name='flat']").val(
        address.flat
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

