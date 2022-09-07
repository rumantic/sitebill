<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>{if $meta_title != ''}{$meta_title}{else}{$title}{/if}</title>
    <script type="text/javascript">
    var estate_folder = '{$estate_folder}';
    </script>
    <meta name="description" content="{$meta_description}" />
    <meta name="keywords" content="{$meta_keywords}" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {if isset($_socialtags)}{$_socialtags}{/if}
    {if isset($canonicalurl)}<link rel="canonical" href="{$canonicalurl}"/>{/if}

    <link rel="stylesheet" href="{$estate_folder}/apps/admin/admin/template1/assets/css/font-awesome.min.css" />
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,700,300&subset=latin,cyrillic' rel='stylesheet' type='text/css'>
    <link rel="shortcut icon" href="{$theme_folder}/img/favicon.png" type="image/png">
    <link rel="stylesheet" href="{$theme_folder}/css/bootstrap.css" type="text/css">
    <link rel="stylesheet" href="{$theme_folder}/css/bootstrap-responsive.css" type="text/css">
    <link rel="stylesheet" href="{$theme_folder}/libraries/chosen/chosen.css" type="text/css">
    <link rel="stylesheet" href="{$theme_folder}/libraries/bootstrap-fileupload/bootstrap-fileupload.css" type="text/css">
    <link rel="stylesheet" href="{$theme_folder}/libraries/jquery-ui/css/ui-lightness/jquery-ui-1.10.2.custom.min.css" type="text/css">
    <link rel="stylesheet" href="{$theme_folder}/css/realia-blue.css" type="text/css">
    <link rel="stylesheet" href="{$theme_folder}/css/style.css" type="text/css">
    <link rel="stylesheet" href="{$theme_folder}/css/bootstrap.corrections.css" type="text/css">
    <link rel="stylesheet" href="{$theme_folder}/css/prettyPhoto.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    {if $map_type == 'yandex'}
        <script type="text/javascript" src="https://api-maps.yandex.ru/2.0-stable/?load=package.standard&lang=ru-RU"></script>
    {elseif $map_type == 'leaflet_osm'}
        <link rel="stylesheet" type="text/css" href="{$estate_folder}/apps/system/js/leaflet/leaflet.css" />
        <script type="text/javascript" src="{$estate_folder}/apps/system/js/leaflet/leaflet.js"></script>
    {elseif $map_type == 'google'}
        <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3&sensor=true&key={$google_api_key}{if {getConfig key='apps.geodata.use_google_places_api'} eq 1}&libraries=places{/if}"></script>
    {/if}

    <script type="text/javascript" src="{$theme_folder}/js/jquery.js"></script>
    <script type="text/javascript" src="{$theme_folder}/js/jquery.ezmark.js"></script>
    <script type="text/javascript" src="{$theme_folder}/js/jquery.currency.js"></script>
    <script type="text/javascript" src="{$theme_folder}/js/jquery.cookie.js"></script>
    <script type="text/javascript" src="{$theme_folder}/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="{$theme_folder}/js/carousel.js"></script>
    <script type="text/javascript" src="{$theme_folder}/libraries/jquery-ui/js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="{$theme_folder}/libraries/chosen/chosen.jquery.min.js"></script>
    <script type="text/javascript" src="{$theme_folder}/libraries/iosslider/_src/jquery.iosslider.min.js"></script>
    <script type="text/javascript" src="{$theme_folder}/libraries/bootstrap-fileupload/bootstrap-fileupload.js"></script>
    <script type="text/javascript" src="{$theme_folder}/js/realia.js"></script>
    <script type="text/javascript" src="{$estate_folder}/apps/client/js/clientorderajax.js"></script>
    <script type="text/javascript" src="{$estate_folder}/js/estate.js"></script>
    <script type="text/javascript" src="{$estate_folder}/apps/system/js/sitebillcore.js"></script>
    <script type="text/javascript" src="{$theme_folder}/js/interface.js"></script>
    <script src="{$theme_folder}/js/jquery.prettyPhoto.js"></script>
    <link rel="stylesheet" href="{$theme_folder}/plugins/intl-tel-input/css/intlTelInput.min.css">
    <link rel="stylesheet" href="{$theme_folder}/plugins/intl-tel-input/css/isValidNumber.css">

        {literal}
        <script type="text/javascript" >
            $(document).ready(function () {
                $("a[rel^='prettyPhoto']").prettyPhoto({
                    social_tools: false,
                    deeplinking: false,
                    theme: 'light_square'});
            });


        </script>
        <script type="text/javascript">
            $(document).ready(function () {
                $(window).scroll(function () {
                    if ($(this).scrollTop() > 100) {
                        $('.scrollup').fadeIn();
                    } else {
                        $('.scrollup').fadeOut();
                    }
                });
                $('.scrollup').click(function () {
                    $("html, body").animate({scrollTop: 0}, 600);
                    return false;
                });
            });
        </script>
        {/literal}
    {if isset($debugbarRenderer)}
        {$debugbarRenderer->renderHead()}
    {/if}

</head>
    {assign var="lang_topic_name" value="name_{$smarty.session._lang}"}
