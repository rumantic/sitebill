<div data-kt-menu-trigger="click" class="menu-item menu-accordion">
    <span class="menu-link">
		@if($item['icon'])
            <span class="menu-bullet">
				<i class="{{$item['icon']}}"></i>
			</span>
        @endif
        <span class="menu-title">{{$item['title']}}</span>
        <span class="menu-arrow"></span>
    </span>
    <div class="menu-sub menu-sub-accordion menu-active-bg @if($item['active']) show @endif">
        @foreach($item['childs'] as $child_item)
            @include('apps.admin3.resources.views.layout.partials.aside.menu.item', ['item' => $child_item])
        @endforeach


    </div>
</div>
