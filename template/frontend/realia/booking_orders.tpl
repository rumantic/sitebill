{if ($data.Price_per_1_day.value == '' or $data.Price_per_1_day.value == 0) and $data.Price_for_1_year_rent.value > 0}
    <script>
        {literal}
        $(document).ready(function () {
            ClientOrder.init_form('booking_form', 'booking_orders', {url:'https://{/literal}{$smarty.server.HTTP_HOST}{literal}/realty{/literal}{$realty_id}{literal}'});
        });
        {/literal}
    </script>
    <div class="properties-rows">
        <div class="row">
            <div class="property span9">
                <h3 style="padding: 16px;">{_e t="Забронировать"}</h3>
                <div style="padding: 16px;" id="booking_form"></div>
            </div>
        </div>
    </div>
{/if}
