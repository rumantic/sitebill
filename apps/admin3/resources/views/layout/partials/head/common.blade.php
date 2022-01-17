<!-- Mobile Metas -->
<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1.0, shrink-to-fit=no">
@if($sitebill)
<base href="{{SITEBILL_MAIN_URL}}/{{$sitebill->getConfigValue('apps.admin3.alias')}}?{{$_SERVER['QUERY_STRING']}}">
@endif
<title>@if (store('meta_title') != ''){{store('meta_title')}}@else{{store('title')}}@endif</title>
<meta name="description" content="{{store('meta_description')}}" />
<meta name="keywords" content="{{store('meta_keywords')}}" />
@if (store('_socialtags')){!! store('_socialtags') !!}@endif
@if (store('canonicalurl')){{store('canonicalurl')}}@endif

<!-- LANGUAGES SECTION -->
@if(store('languagedata'))
    @if(store('languagedata')['hreflangs'] && !empty(store('languagedata')['hreflangs']))
        @foreach(store('languagedata')['hreflangs'] as $hreflangdata)
            <link rel="alternate" hreflang="{{$hreflangdata['hreflang']}}" href="{{$hreflangdata['href']}}">
        @endforeach
    @endif
@endif
<!-- .LANGUAGES SECTION -->

<script type="text/javascript">
    var estate_folder = '{{$estate_folder}}';
</script>

<!-- Google fonts -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />

<!--begin::Page Vendor Stylesheets(used by this page)-->
<link href="{{$template_root}}assets/plugins/custom/fullcalendar/fullcalendar.bundle.css" rel="stylesheet" type="text/css" />
<!--end::Page Vendor Stylesheets-->
<!--begin::Global Stylesheets Bundle(used by all pages)-->
<link href="{{$template_root}}assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
<link href="{{$template_root}}assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
<link href="{{$template_root}}assets/css/fix.css" rel="stylesheet" type="text/css" />
<!--end::Global Stylesheets Bundle-->

<!-- Favicons -->
<link rel="icon" href="{{$template_root}}assets/media/logos/favicon_a.ico">

@if(store('map_type') == 'yandex')
    <script>
        var yandex_map_version = '2.1';
    </script>
    <script type="text/javascript" src="https://api-maps.yandex.ru/2.1/?lang=ru-RU&apikey={{store('y_api_key')}}"></script>
@elseif(store('map_type') == 'leaflet_osm')
    <link rel="stylesheet" type="text/css" href="{{$estate_folder}}/apps/system/js/leaflet/leaflet.css" />
    <script type="text/javascript" src="{{$estate_folder}}/apps/system/js/leaflet/leaflet.js"></script>
@elseif(store('map_type') == 'google')
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3&amp;sensor=true&key={{store('google_api_key')}}"></script>
@endif
<link rel="stylesheet" href="{{$estate_folder}}/apps/admin/admin/template1/assets/css/font-awesome.min.css" />


@if(DEBUG_ENABLED)
    {!!\Sitebill::getdebugbarRenderer()->renderHead()!!}
@endif
