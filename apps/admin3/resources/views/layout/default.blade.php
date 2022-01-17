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
<body id="kt_body" class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled toolbar-fixed aside-enabled aside-fixed" {{ (isset($user_config['admin_sidebar_hide']) && $user_config['admin_sidebar_hide'] == 1) ? 'data-kt-aside-minimize=on' : ''}} style="--kt-toolbar-height:55px;--kt-toolbar-height-tablet-and-mobile:55px">
<!--begin::Main-->
<!--begin::Root-->
<div class="d-flex flex-column flex-root">
    <!--begin::Page-->
    <div class="page d-flex flex-row flex-column-fluid">
        @include('apps.admin3.resources.views.layout.partials.aside.aside')
        <!--begin::Wrapper-->
        <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
            @include('apps.admin3.resources.views.layout.partials.header.header')
            <!--begin::Content-->
            <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
                @include('apps.admin3.resources.views.layout.partials.toolbar.toolbar')

                    <!--begin::Container-->
                    <div id="kt_content_container" class="container-fluid">
                        @yield('content')
                    </div>
                    <!--end::Container-->
            </div>
            <!--end::Content-->
        </div>
        <!--end::Wrapper-->
    </div>
    <!--end::Page-->
</div>
<!--end::Root-->
<!--begin::Drawers-->
<!--end::Drawers-->
<!--begin::Modals-->
@include('apps.admin3.resources.views.layout.partials.modals.create_app')
<!--end::Modals-->
@include('apps.admin3.resources.views.layout.base.scrolltop')

<!--end::Main-->

@include('apps.admin3.resources.views.layout.base._vendor_js')
@stack('scripts')

@if(DEBUG_ENABLED)
    {!!\Sitebill::getdebugbarRenderer()->render()!!}
@endif

</body>
<!--end::Body-->
</html>
