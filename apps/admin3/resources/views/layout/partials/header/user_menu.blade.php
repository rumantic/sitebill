<!--begin::User-->
<div class="d-flex align-items-center ms-1 ms-lg-3" id="kt_header_user_menu_toggle">
    <!--begin::Menu wrapper-->
    <div class="cursor-pointer symbol symbol-30px symbol-md-40px" data-kt-menu-trigger="click" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end" data-kt-menu-flip="bottom">
        @if(store('current_user_info')['imgfile']['value'])
            <img src="{{$estate_folder}}/img/data/user/{{store('current_user_info')['imgfile']['value']}}" />
        @else
            <img src="{{$template_root}}assets/media/avatars/blank.png" alt="metronic" />
        @endif
    </div>
    <!--begin::Menu-->
    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-primary fw-bold py-4 fs-6 w-275px" data-kt-menu="true">
        <!--begin::Menu item-->
        <div class="menu-item px-3">
            <div class="menu-content d-flex align-items-center px-3">
                <!--begin::Avatar-->
                <div class="symbol symbol-50px me-5">
                    @if(store('current_user_info')['imgfile']['value'])
                        <img src="{{$estate_folder}}/img/data/user/{{store('current_user_info')['imgfile']['value']}}" />
                    @else
                        <img src="{{$template_root}}assets/media/avatars/blank.png" />
                    @endif
                </div>
                <!--end::Avatar-->
                <!--begin::Username-->
                <div class="d-flex flex-column">
                    <div class="fw-bolder d-flex align-items-center fs-5">
                        @if(store('current_user_info')['fio']['value'])
                            {{store('current_user_info')['fio']['value']}}
                        @else
                            {{store('current_user_info')['login']['value']}}
                        @endif
                        <span class="badge badge-light-success fw-bolder fs-8 px-2 py-1 ms-2">
                            @if(store('current_user_info')['group_id']['value_string'] == 'Администраторы')
                                Админ
                            @else
                                {{store('current_user_info')['group_id']['value_string']}}
                            @endif
                        </span></div>
                    <a href="mailto:{{store('current_user_info')['email']['value']}}" class="fw-bold text-muted text-hover-primary fs-7">
                        {{store('current_user_info')['email']['value']}}
                    </a>
                </div>
                <!--end::Username-->
            </div>
        </div>
        <!--end::Menu item-->
        <!--begin::Menu separator-->
        <div class="separator my-2"></div>
        <!--end::Menu separator-->
        <!--begin::Menu item-->
        <div class="menu-item px-5">
            <a href="?action=profile" class="menu-link px-5">{{_e('L_MY_PROFILE')}}</a>
        </div>
        <!--end::Menu item-->
        {{--@include('apps.admin3.resources.views.layout.partials.header.user_menu_in_future')--}}


        <!--begin::Menu item-->
        <div class="menu-item px-5">
            <a href="?action=logout" class="menu-link px-5">{{_e('L_LOGOUT_BUTTON')}}</a>
        </div>
        <!--end::Menu item-->
    </div>
    <!--end::Menu-->
    <!--end::Menu wrapper-->
</div>
<!--end::User -->
