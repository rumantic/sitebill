<!--begin::Aside-->
<div id="kt_aside" class="aside aside-light aside-hoverable" data-kt-drawer="true" data-kt-drawer-name="aside" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="{default:'200px', '300px': '250px'}" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_aside_mobile_toggle">
    @include('apps.admin3.resources.views.layout.partials.aside.brand')
    <!--begin::Aside menu-->
    <div class="aside-menu flex-column-fluid">
        <!--begin::Aside Menu-->
        <div class="hover-scroll-overlay-y my-5 my-lg-5" id="kt_aside_menu_wrapper" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_aside_logo, #kt_aside_footer" data-kt-scroll-wrappers="#kt_aside_menu" data-kt-scroll-offset="0">
            @include('apps.admin3.resources.views.layout.partials.aside.menu.generator')
        </div>
        <!--end::Aside Menu-->
    </div>
    <!--end::Aside menu-->
</div>
<!--end::Aside-->
@push('scripts')
    <script>
        /**
         * Store user settins
         * @param Object settings
         * @param function callback - function get an responce as parameter
         */
        function store_user_settings(settings, callback = false){
            const body = {
                action: 'config',
                do: 'store_user_settings',
                params: settings
            };
            $.ajax({
                url: estate_folder+'/apps/api/rest.php',
                data: body,
                type: 'post',
                dataType: 'json',
                success: function(json){
                    if(false !== callback){
                        callback(json);
                    }
                }
            });
        }
        $(document).ready(function () {
            var toggleElement = document.querySelector("#kt_aside_toggle");
            var toggle = KTToggle.getInstance(toggleElement);
            toggle.on("kt.toggle.changed", function() {
                let settings = {
                    admin_sidebar_hide: 0
                };
                if($("body").attr('data-kt-aside-minimize') === 'on'){
                    settings.admin_sidebar_hide = 1;
                }
                store_user_settings(settings);
            });
        });

    </script>
@endpush