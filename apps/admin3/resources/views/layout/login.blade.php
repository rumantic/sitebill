@php
    $template_root = '/apps/admin3/resources/';
    $estate_folder = SITEBILL_MAIN_URL;
@endphp
        <!doctype html>
<html>
<head lang="{{(store('languagedata') && store('languagedata')['lang']) ? store('languagedata')['lang'] : ''}}">
    @include('apps.admin3.resources.views.layout.partials.head.common')
</head>
<!--begin::Body-->
<body id="kt_body" class="bg-body">
    @yield('content')

@include('apps.admin3.resources.views.layout.base._vendor_js')
@stack('scripts')

@if(DEBUG_ENABLED)
    {!!\Sitebill::getdebugbarRenderer()->render()!!}
@endif

</body>
<!--end::Body-->
</html>
