<!--begin::Menu item-->
<div class="menu-item px-5">
    <a href="../../demo1/dist/pages/projects/list.html" class="menu-link px-5">
        <span class="menu-text">My Projects</span>
        <span class="menu-badge">
															<span class="badge badge-light-danger badge-circle fw-bolder fs-7">3</span>
														</span>
    </a>
</div>
<!--end::Menu item-->
<!--begin::Menu item-->
<div class="menu-item px-5" data-kt-menu-trigger="hover" data-kt-menu-placement="left-start" data-kt-menu-flip="bottom">
    <a href="#" class="menu-link px-5">
        <span class="menu-title">My Subscription</span>
        <span class="menu-arrow"></span>
    </a>
    <!--begin::Menu sub-->
    <div class="menu-sub menu-sub-dropdown w-175px py-4">
        <!--begin::Menu item-->
        <div class="menu-item px-3">
            <a href="../../demo1/dist/account/referrals.html" class="menu-link px-5">Referrals</a>
        </div>
        <!--end::Menu item-->
        <!--begin::Menu item-->
        <div class="menu-item px-3">
            <a href="../../demo1/dist/account/billing.html" class="menu-link px-5">Billing</a>
        </div>
        <!--end::Menu item-->
        <!--begin::Menu item-->
        <div class="menu-item px-3">
            <a href="../../demo1/dist/account/statements.html" class="menu-link px-5">Payments</a>
        </div>
        <!--end::Menu item-->
        <!--begin::Menu item-->
        <div class="menu-item px-3">
            <a href="../../demo1/dist/account/statements.html" class="menu-link d-flex flex-stack px-5">Statements
                <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="View your statements"></i></a>
        </div>
        <!--end::Menu item-->
        <!--begin::Menu separator-->
        <div class="separator my-2"></div>
        <!--end::Menu separator-->
        <!--begin::Menu item-->
        <div class="menu-item px-3">
            <div class="menu-content px-3">
                <label class="form-check form-switch form-check-custom form-check-solid">
                    <input class="form-check-input w-30px h-20px" type="checkbox" value="1" checked="checked" name="notifications" />
                    <span class="form-check-label text-muted fs-7">Notifications</span>
                </label>
            </div>
        </div>
        <!--end::Menu item-->
    </div>
    <!--end::Menu sub-->
</div>
<!--end::Menu item-->
<!--begin::Menu item-->
<div class="menu-item px-5">
    <a href="../../demo1/dist/account/statements.html" class="menu-link px-5">My Statements</a>
</div>
<!--end::Menu item-->
<!--begin::Menu separator-->
<div class="separator my-2"></div>
<!--end::Menu separator-->
@include('apps.admin3.resources.views.layout.partials.header.language_menu')

<!--begin::Menu item-->
<div class="menu-item px-5 my-1">
    <a href="../../demo1/dist/account/settings.html" class="menu-link px-5">Account Settings</a>
</div>
<!--end::Menu item-->
