<!DOCTYPE html>
<html lang="en">
    <head>

        {if $smarty.const.SITE_ENCODING != '' }
            <meta charset="{$smarty.const.SITE_ENCODING}" />
        {else}
            <meta charset="windows-1251" />
        {/if}
        <title>CMS Sitebill</title>


        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <!-- basic styles -->

        <link href="{$assets_folder}/assets/css/bootstrap.css" rel="stylesheet" />
        <!-- link href="{$MAIN_URL}/apps/system/js/bootstrap/css/bootstrap.min.css" rel="stylesheet" /-->
        <link href="{$assets_folder}/assets/css/bootstrap-responsive.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="{$assets_folder}/assets/css/font-awesome.min.css" />

        <!--[if IE 7]>
          <link rel="stylesheet" href="assets/css/font-awesome-ie7.min.css" />
        <![endif]-->

        <!-- page specific plugin styles -->

        <!-- fonts -->

        <link rel="stylesheet" href="{$assets_folder}/assets/css/ace-fonts.css" />
        <!-- ace styles -->
        <link rel="stylesheet" href="{$assets_folder}/assets/css/colorbox.css" />
        <link rel="stylesheet" href="{$assets_folder}/assets/css/ace.min.css" />
        <link rel="stylesheet" href="{$assets_folder}/assets/css/ace-responsive.min.css" />
        <link rel="stylesheet" href="{$assets_folder}/assets/css/ace-skins.min.css" />
        <link rel="stylesheet" href="{$assets_folder}/assets/css/styles.css" />
        <!--[if lte IE 8]>
          <link rel="stylesheet" href="assets/css/ace-ie.min.css" />
        <![endif]-->

        <!-- inline styles related to this page -->

        <!-- ace settings handler -->

        <link rel="stylesheet" href="{$MAIN_URL}/apps/admin/admin/template/css/admin.css">

        <script type="text/javascript" src="{$MAIN_URL}/apps/system/js/jquery/jquery.3.3.1.js"></script>
        <script type="text/javascript" src="{$MAIN_URL}/apps/system/js/jquery/jquery-migrate.min.js"></script>

        <script src="{$MAIN_URL}/apps/system/js/bootstrap/js/bootstrap.min.js"></script>

        <script src="{$MAIN_URL}/apps/system/js/bootstrap-editable/js/bootstrap-editable.min.js"></script>
        <link rel="stylesheet" href="{$MAIN_URL}/apps/system/js/bootstrap-editable/css/bootstrap-editable.css" />
        {if $ADMIN_NO_NANOAPI==1}
        {else}
            <link href="https://www.sitebill.ru/css/nano.css" rel="stylesheet" type="text/css" />
            <script src="https://www.sitebill.ru/js/nanoapi.js"></script>
            <script src="https://www.sitebill.ru/js/nanoapi_beta.js"></script>
        {/if}
        <script src="{$MAIN_URL}/js/interface.js"></script>
        <script src="{$MAIN_URL}/js/estate.js"></script>
        <script type="text/javascript" src="{$MAIN_URL}/js/jquery.tablesorter.min.js"></script>
        <link href="{$MAIN_URL}/css/jquery-ui-1.8.custom.css" rel="stylesheet" type="text/css"/>
        <script type="text/javascript" src="{$MAIN_URL}/apps/system/js/jqueryui/jquery-ui.js"></script>
        <script type="text/javascript" src="{$MAIN_URL}/apps/system/js/sitebillcore.js"></script>
        <script type="text/javascript" src="{$MAIN_URL}/apps/system/js/mycombobox.js"></script>
        <script type="text/javascript" src="{$MAIN_URL}/apps/system/js/jquery.cookie.js"></script>
        <link rel="stylesheet" href="{$MAIN_URL}/apps/system/css/jquery-ui.custom.css" />
        <link rel="stylesheet" href="{$MAIN_URL}/apps/system/css/mycombobox.css" />

<!-- <script type="text/javascript" src="{$MAIN_URL}/js/jquery.ui.datepicker.js"></script> -->
        {if $ADMIN_NO_MAP_PROVIDERS==1}
        {else}
            {if $map_type=='yandex'}
                <script type="text/javascript" src="https://api-maps.yandex.ru/2.0-stable/?load=package.standard&lang=ru-RU"></script>
            {else}
                <script type="text/javascript" src="https://maps.google.com/maps/api/js{if $g_api_key!=''}?key={$g_api_key}{/if}"></script>
            {/if}
            {if 1==0}<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=drawing,geometry"></script>{/if}

        {/if}
        <script src="{$assets_folder}/assets/js/ace-extra.min.js"></script>


        <script src="{$assets_folder}/assets/js/jquery-ui-1.10.3.custom.min.js"></script>
        <script src="{$assets_folder}/assets/js/jquery.ui.touch-punch.min.js"></script>
        <script src="{$assets_folder}/assets/js/jquery.slimscroll.min.js"></script>
        <script src="{$assets_folder}/assets/js/jquery.easy-pie-chart.min.js"></script>
        <script src="{$assets_folder}/assets/js/jquery.sparkline.min.js"></script>
        <script src="{$assets_folder}/assets/js/flot/jquery.flot.min.js"></script>
        <script src="{$assets_folder}/assets/js/flot/jquery.flot.pie.min.js"></script>
        <script src="{$assets_folder}/assets/js/flot/jquery.flot.resize.min.js"></script>
        <script src="{$assets_folder}/assets/js/bootstrap-tag.min.js"></script>

        <!-- ace scripts -->

        <script src="{$assets_folder}/assets/js/ace-elements.min.js"></script>
        <script src="{$assets_folder}/assets/js/ace.min.js"></script>

        <link rel="stylesheet" href="{$assets_folder}/css/custom.css" />
        {literal}
            <style>
                .modal.fade{top: -200%;}
                .inline-tags {
                    position: relative;
                    /*overflow-x: hidden;
                    overflow-y: auto;*/
                }
                .inline-tags .tags {
                    width: 40px;
                }
                .inline-tags .tags .tag {
                    padding-left: 22px;
                    padding-right: 9px;
                }
                .inline-tags .tags .tag .close {
                    left: 0;
                    right: auto;
                }
            </style>
        {/literal}



        <script>
            var estate_folder = '{$estate_folder}';
        </script>

    </head>
    <body onload="runDialog('homescript_etown_ru'); {$onload}" class="">
    {if $iframe_mode}
        {include file='main_body_object_only.tpl'}
    {else}
        {include file='main_body_classic.tpl'}
    {/if}
    </body>
</html>
