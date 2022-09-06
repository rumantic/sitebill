@extends('layout.only_content')
@php
    $template_root = store('template_root');
    $estate_folder = SITEBILL_MAIN_URL;
@endphp
@section('content')
    <div class="widget-box collapsed">
        <div class="widget-header">
            <h4 class="widget-title">Поиск</h4>

            <div class="widget-toolbar">
                <a href="#" data-action="collapse">
                    <i class="ace-icon fa fa-chevron-down"></i>
                </a>
            </div>
        </div>

        <div class="widget-body">
            <div class="widget-main">
                <div>
                    <label for="form-field-8">Владелец</label>
                    {!! $user_select_box !!}
                </div>
            </div>
        </div>
    </div>
@endsection

