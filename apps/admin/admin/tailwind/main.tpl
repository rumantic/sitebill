<!DOCTYPE html>
<html lang="en" class="h-full">
    <head>
        {assign var='bversion' value='tw'}
        {$SystemJSvars}
        {if $smarty.const.SITE_ENCODING != '' }
            <meta charset="{$smarty.const.SITE_ENCODING}" />
        {else}
            <meta charset="windows-1251" />
        {/if}
        <title>{if $smarty.const.BRANDING==1}{getConfig key='site_title'}{else}CMS Sitebill{/if}</title>

        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <!-- Tailwind CSS via CDN -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        colors: {
                            accent: {
                                50: '#eff4ff',
                                100: '#dbe6fe',
                                200: '#bfd4fe',
                                300: '#93b8fd',
                                400: '#6090fa',
                                500: '#3b6ff6',
                                600: '#2563eb',
                                700: '#1d4ed8',
                                800: '#1e40af',
                                900: '#1e3a8a',
                                DEFAULT: '#2563eb'
                            },
                            ink: '#0f172a'
                        },
                        fontFamily: {
                            sans: ['Geist', 'Segoe UI', 'system-ui', 'sans-serif'],
                            mono: ['Geist Mono', 'ui-monospace', 'monospace']
                        },
                        boxShadow: {
                            pop: '0 12px 32px -12px rgba(15, 23, 42, 0.18)'
                        }
                    }
                }
            }
        </script>

        <!-- Font Awesome -->
        <link rel="stylesheet" href="{$MAIN_URL}/apps/admin/admin/template1/assets/css/font-awesome.min.css" />

        <!-- jQuery -->
        <script type="text/javascript" src="{$MAIN_URL}/apps/system/js/jquery/jquery.3.3.1.js"></script>
        <script type="text/javascript" src="{$MAIN_URL}/apps/system/js/jquery/jquery-migrate.min.js"></script>
        <script src="{$MAIN_URL}/apps/system/js/jquery.ui.touch-punch.min.js"></script>

        <!-- Bootstrap JS (needed for legacy components) -->
        <script src="{$MAIN_URL}/apps/system/js/bootstrap/js/bootstrap.min.js"></script>
        <!-- Minimal Bootstrap CSS for legacy JS components compatibility -->
        <link href="{$MAIN_URL}/apps/system/js/bootstrap/css/bootstrap.min.css" rel="stylesheet" />

        <!-- Bootstrap editable -->
        <script src="{$MAIN_URL}/apps/system/js/bootstrap-editable/js/bootstrap-editable.min.js"></script>
        <link rel="stylesheet" href="{$MAIN_URL}/apps/system/js/bootstrap-editable/css/bootstrap-editable.css" />

        {if $loadnanoapi}
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

        <script>
            var yandex_map_version = '2.1';
        </script>

        {if $ADMIN_NO_MAP_PROVIDERS==1}
        {else}
            {if $map_type=='yandex'}
                <script type="text/javascript" src="https://api-maps.yandex.ru/2.1/?lang=ru-RU{if $y_api_key!=''}&apikey={$y_api_key}{/if}{if {getConfig key='apps.geodata.yandex_suggest_apikey'} ne ''}&suggest_apikey={getConfig key='apps.geodata.yandex_suggest_apikey'}{/if}"></script>
            {elseif $map_type=='google'}
                <script type="text/javascript" src="https://maps.google.com/maps/api/js{if $g_api_key!=''}?key={$g_api_key}{/if}{if {getConfig key='apps.geodata.use_google_places_api'} eq 1}&libraries=places{/if}"></script>
            {elseif $map_type=='leaflet_osm'}
                <link rel="stylesheet" type="text/css" href="{$estate_folder}/apps/system/js/leaflet/leaflet.css" />
                <script type="text/javascript" src="{$estate_folder}/apps/system/js/leaflet/leaflet.js"></script>
            {/if}
        {/if}

        <!-- Tailwind template custom styles -->
        <link rel="stylesheet" href="{$assets_folder}/css/custom.css" />

        {literal}
            <style>
                /* Legacy compatibility overrides for Tailwind template */
                .modal.fade { top: -200%; }
                .inline-tags { position: relative; }
                .inline-tags .tags { width: 40px; }
                .inline-tags .tags .tag { padding-left: 22px; padding-right: 9px; }
                .inline-tags .tags .tag .close { left: 0; right: auto; }

                /* Sidebar transition */
                .sidebar-collapsed .sidebar-panel { width: 0; overflow: hidden; }
                .sidebar-collapsed .main-panel { margin-left: 0; }

                /* Override Bootstrap styles that conflict with Tailwind */
                .tw-admin .btn { all: unset; cursor: pointer; }
            </style>
        {/literal}

        <script>
            var estate_folder = '{$estate_folder}';
        </script>
        {if isset($debugbarRenderer)}
            {$debugbarRenderer->renderHead()}
        {/if}
    </head>
    <body onload="{if $loadnanoapi}runDialog('homescript_etown_ru'); {/if}{$onload}" class="h-full bg-slate-50 font-sans antialiased tw-admin">
    {if $iframe_mode}
        {include file='main_body_object_only.tpl'}
    {else}
        {include file='main_body_classic.tpl'}
    {/if}
    {if isset($debugbarRenderer)}
        {$debugbarRenderer->render()}
    {/if}
        <notifications/>
    <script src="{$MAIN_URL}/apps/vue/dist/js/main.js"></script>
    <script src="{$MAIN_URL}/apps/api/js/legacy_api.js"></script>

    </body>
</html>
