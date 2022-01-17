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

    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,700,300&subset=latin,cyrillic' rel='stylesheet' type='text/css'>
    <link rel="shortcut icon" href="{$estate_folder}/template/frontend/{$current_theme_name}/img/favicon.png" type="image/png">
    <link rel="stylesheet" href="{$estate_folder}/template/frontend/{$current_theme_name}/css/bootstrap.css" type="text/css">
    <link rel="stylesheet" href="{$estate_folder}/template/frontend/{$current_theme_name}/css/bootstrap-responsive.css" type="text/css">
    <link rel="stylesheet" href="{$estate_folder}/template/frontend/{$current_theme_name}/libraries/chosen/chosen.css" type="text/css">
    <link rel="stylesheet" href="{$estate_folder}/template/frontend/{$current_theme_name}/libraries/bootstrap-fileupload/bootstrap-fileupload.css" type="text/css">
    <link rel="stylesheet" href="{$estate_folder}/template/frontend/{$current_theme_name}/libraries/jquery-ui/css/ui-lightness/jquery-ui-1.10.2.custom.min.css" type="text/css">
    {if 1==0}<link rel="stylesheet" href="{$estate_folder}/template/frontend/{$current_theme_name}/libraries/jquery-ui/css/custom-theme/jquery-ui-1.10.0.custom.css" type="text/css">{/if}
    <link rel="stylesheet" href="{$estate_folder}/template/frontend/{$current_theme_name}/css/realia-blue.css" type="text/css">
    <link rel="stylesheet" href="{$estate_folder}/template/frontend/{$current_theme_name}/css/bootstrap.corrections.css" type="text/css">
    <link rel="stylesheet" href="{$estate_folder}/template/frontend/{$current_theme_name}/css/prettyPhoto.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    {if $map_type == 'yandex'}
        <script type="text/javascript" src="https://api-maps.yandex.ru/2.0-stable/?load=package.standard&lang=ru-RU"></script>
    {elseif $map_type == 'leaflet_osm'}
        <link rel="stylesheet" type="text/css" href="{$estate_folder}/apps/system/js/leaflet/leaflet.css" />
        <script type="text/javascript" src="{$estate_folder}/apps/system/js/leaflet/leaflet.js"></script>
    {elseif $map_type == 'google'}
        <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3&amp;sensor=true&key={$google_api_key}"></script>
    {/if}
    <!-- <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=drawing"></script> -->

    <script type="text/javascript" src="{$estate_folder}/template/frontend/{$current_theme_name}/js/jquery.js"></script>
    <script type="text/javascript" src="{$estate_folder}/template/frontend/{$current_theme_name}/js/jquery.ezmark.js"></script>
    <script type="text/javascript" src="{$estate_folder}/template/frontend/{$current_theme_name}/js/jquery.currency.js"></script>
    <script type="text/javascript" src="{$estate_folder}/template/frontend/{$current_theme_name}/js/jquery.cookie.js"></script>
    <!-- <script type="text/javascript" src="{$estate_folder}/template/frontend/{$current_theme_name}/js/retina.js"></script> -->
    <script type="text/javascript" src="{$estate_folder}/template/frontend/{$current_theme_name}/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="{$estate_folder}/template/frontend/{$current_theme_name}/js/carousel.js"></script>
    <!-- <script type="text/javascript" src="{$estate_folder}/template/frontend/{$current_theme_name}/js/gmap3.min.js"></script>
    <script type="text/javascript" src="{$estate_folder}/template/frontend/{$current_theme_name}/js/gmap3.infobox.min.js"></script> -->
    <script type="text/javascript" src="{$estate_folder}/template/frontend/{$current_theme_name}/libraries/jquery-ui/js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="{$estate_folder}/template/frontend/{$current_theme_name}/libraries/chosen/chosen.jquery.min.js"></script>
    <script type="text/javascript" src="{$estate_folder}/template/frontend/{$current_theme_name}/libraries/iosslider/_src/jquery.iosslider.min.js"></script>
    <script type="text/javascript" src="{$estate_folder}/template/frontend/{$current_theme_name}/libraries/bootstrap-fileupload/bootstrap-fileupload.js"></script>
    <script type="text/javascript" src="{$estate_folder}/template/frontend/{$current_theme_name}/js/realia.js"></script>
    <script type="text/javascript" src="{$estate_folder}/apps/client/js/clientorderajax.js"></script>
    <script type="text/javascript" src="{$estate_folder}/js/estate.js"></script>
    <script type="text/javascript" src="{$estate_folder}/apps/system/js/sitebillcore.js"></script>
    <script type="text/javascript" src="{$estate_folder}/template/frontend/{$current_theme_name}/js/interface.js"></script>
    {if 1==0}<link rel="stylesheet" href="{$estate_folder}/template/frontend/{$current_theme_name}/css/jqueryslidemenu.css" type="text/css">
        <script type="text/javascript" src="{$estate_folder}/template/frontend/{$current_theme_name}/js/jqueryslidemenu.js"></script>{/if}
        <script src="{$estate_folder}/template/frontend/{$current_theme_name}/js/jquery.prettyPhoto.js"></script>
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
