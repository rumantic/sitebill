<div data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-placement="right-start" class="menu-item menu-lg-down-accordion">
	<span class="menu-link py-3">
		@if($item['icon'])
			<span class="menu-bullet">
				<i class="{{$item['icon']}}"></i>
			</span>
		@endif
        <span class="menu-title">{{$item['title']}}</span>
        <span class="menu-arrow"></span>
	</span>

    <div class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-active-bg py-lg-4 w-lg-225px">
		@foreach($item['childs'] as $child_item)
			@include('layout.partials.header.apps_menu.item', ['item' => $child_item])
		@endforeach
    </div>
</div>
