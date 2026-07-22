@extends('layout.default')
@php
    $template_root = store('template_root');
    $estate_folder = SITEBILL_MAIN_URL;
@endphp
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <iframe src="{{$estate_folder}}/js/ajax.php?action=iframe_map" style="border: 0; min-height: 600px;" border="0" width="100%" height="100%"></iframe>
            </div>
        </div>
    </div>
@endsection

