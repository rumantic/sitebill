@extends('apps.admin3.resources.views.layout.default')
@section('content')
    <div class="row g-5 g-xl-8">
            @include('apps.table.resources.views.table', ['component' => empty($component) ? $sitebill->getConfigValue('apps.admin3.default_app') : $component])
    </div>

@endsection
@push('scripts')
    @php
        $assets_folder = $MAIN_URL.'/apps/admin/admin/template1';
    @endphp
@endpush
