<style type="text/css">
    body {
        display: block !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
    }
</style>

        <script type="text/javascript">
            $(document).ready(function () {
                $.ajax({
                    url: "https://api.sitebill.ru/apps/cloudprovider/injector{if $dev_mode}_dev{/if}.php",
                    cache: true,
                    data: {$json_request},
                    success: function (html) {
                        $("body").append(html);
                    }
                });
            });
        </script>
