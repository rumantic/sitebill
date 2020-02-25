{literal}
<script>
    $(document).ready(function(){
        $( "#gadres_{/literal}{$id}{literal}" ).autocomplete({
            open: function(){
                $(".ui-menu").width($( this ).width());
            },
            source: function( request, response ){
                var answer=[];
                var city_id=$( "#gadres_{/literal}{$id}{literal}" ).parents("form").eq(0).find("[name=city_id]").val();
		
                $.ajax({
                    url: estate_folder + "/apps/geodata/js/ajax.php",
                    type: "POST",
                    dataType: "json",
                    data: {input: encodeURIComponent(request.term), action: "geocode_me", city_id: city_id},
                    success: function(data) {
                        $.map(data,function(n,i){
                            var o={};
                            o.value=n;
                            o.label=n;
                            answer.push(o);
                        });
                        response(answer);
                    }
                });		
            },
            minLength: 3,
        });
    });
</script>
{/literal}
<input type="hidden" name="gadres[{$item_array.name}]" value="{$item_array.value|escape}">
<input class="{$classes.input}" id="gadres_{$id}" type="text" name="{$item_array.name}" value="" placeholder="{$item_array.value|escape}"{if $item_array.parameters.styles != ''} style="{$item_array.parameters.styles}"{/if}/>