<div class="menu-item">
    <a class="menu-link @if($item['active']) active @endif" href="{{\bridge\Helpers\Helpers::normalize_admin_href($item['href'])}}">
        @if($item['icon'])
            <span class="menu-bullet">
				<i class="{{$item['icon']}}"></i>
			</span>
        @else
            <span class="menu-bullet">
            	<span class="bullet bullet-dot"></span>
            </span>
        @endif
        <span class="menu-title">{{$item['title']}}</span>
    </a>
</div>

