<?php
$json_request = json_encode($_REQUEST);
?>
<html>
    <head>
        <title>Sitebill Cloud Runner</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script type="text/javascript" src="https://api.sitebill.ru/apps/system/js/jquery/jquery.3.3.1.js"></script>
        <script type="text/javascript" src="https://api.sitebill.ru/apps/system/js/jquery/jquery-migrate.min.js"></script>        
        <script type="text/javascript">
            $(document).ready(function () {
                $.ajax({
                    url: "https://api.sitebill.ru/apps/cloudprovider/json.php",
                    cache: true,
                    data: <?php echo $json_request;?>,
                    success: function (html) {
                        $("#cloud_wrapper").append(html);
                    }
                });
            });
        </script>
    </head>
    <body>
        <div id="cloud_wrapper" style="width: 100%;"></div>
    </body>
</html>