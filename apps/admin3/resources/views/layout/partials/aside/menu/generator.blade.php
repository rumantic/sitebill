<!--begin::Menu-->
<div class="menu menu-column menu-title-gray-800 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-500" id="#kt_aside_menu" data-kt-menu="true">
    @foreach($aside_menu as $item)
        @if(!empty($item['childs']))
            @include('apps.admin3.resources.views.layout.partials.aside.menu.submenu', ['item' => $item])
        @else
            @include('apps.admin3.resources.views.layout.partials.aside.menu.item', ['item' => $item])
        @endif
    @endforeach
</div>
<!--end::Menu-->
