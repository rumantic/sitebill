@extends('apps.admin3.resources.views.layout.default')
@section('content')
    <!--begin::Row-->
    <div class="row gy-5 g-xl-8 bootstrap2-iso">
        <!--begin::Col-->
        <div class="col-xxl-12">
            @php
                $res = extract_scripts_and_styles($legacy_content);
            @endphp
            {!! $res['content'] !!}
        </div>
        <!--end::Col-->
    </div>
    <!--end::Row-->
@endsection
@push('scripts')
    @php
        $assets_folder = $MAIN_URL.'/apps/admin/admin/template1';
    @endphp
    <link rel="stylesheet" href="{{$assets_folder}}/assets/css/font-awesome.min.css" />

    <link rel="stylesheet" href="{{$assets_folder}}/assets/css/iso/bootstrap-iso.css" />
    <link rel="stylesheet" href="{{$assets_folder}}/assets/css/iso/ace-iso.css" />

    <link rel="stylesheet" href="{{$assets_folder}}/assets/css/ace-fonts.css" />
    <!-- ace styles -->
    <link rel="stylesheet" href="{{$assets_folder}}/assets/css/colorbox.css" />
    <link rel="stylesheet" href="{{$assets_folder}}/assets/css/uncompressed/ace-migrate.css" />
    <link rel="stylesheet" href="{{$assets_folder}}/assets/css/ace-responsive.min.css" />
    <link rel="stylesheet" href="{{$assets_folder}}/assets/css/ace-skins.min.css" />
    <link rel="stylesheet" href="{{$assets_folder}}/assets/css/styles.css" />
    <!--[if lte IE 8]>
    <link rel="stylesheet" href="{{$assets_folder}}/assets/css/ace-ie.min.css" />
    <![endif]-->

    <script src="{{$MAIN_URL}}/apps/system/js/bootstrap/js/bootstrap.min.js"></script>

    <script src="{{$MAIN_URL}}/apps/system/js/bootstrap-editable/js/bootstrap-editable.min.js"></script>
    <link rel="stylesheet" href="{{$MAIN_URL}}/apps/system/js/bootstrap-editable/css/bootstrap-editable.css" />

    <script src="{{$MAIN_URL}}/js/interface.js"></script>
    <script src="{{$MAIN_URL}}/js/estate.js"></script>
    <script type="text/javascript" src="{{$MAIN_URL}}/js/jquery.tablesorter.min.js"></script>

    <link href="{{$MAIN_URL}}/css/jquery-ui-1.8.custom.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript" src="{{$MAIN_URL}}/apps/system/js/jqueryui/jquery-ui.js"></script>
    <script type="text/javascript" src="{{$MAIN_URL}}/apps/system/js/sitebillcore.js"></script>
    <script type="text/javascript" src="{{$MAIN_URL}}/apps/system/js/mycombobox.js"></script>
    <script type="text/javascript" src="{{$MAIN_URL}}/apps/system/js/jquery.cookie.js"></script>
    <link rel="stylesheet" href="{{$MAIN_URL}}/apps/system/css/jquery-ui.custom.css" />
    <link rel="stylesheet" href="{{$MAIN_URL}}/apps/system/css/mycombobox.css" />
    <link rel="stylesheet" href="{{$MAIN_URL}}/apps/admin/admin/template1/assets/css/jquery.gritter.css" />

    <script src="{{$assets_folder}}/assets/js/ace-extra.min.js"></script>


    <script src="{{$assets_folder}}/assets/js/jquery-ui-1.10.3.custom.min.js"></script>
    <script src="{{$assets_folder}}/assets/js/jquery.ui.touch-punch.min.js"></script>
    <script src="{{$assets_folder}}/assets/js/jquery.slimscroll.min.js"></script>
    <script src="{{$assets_folder}}/assets/js/jquery.easy-pie-chart.min.js"></script>
    <script src="{{$assets_folder}}/assets/js/jquery.sparkline.min.js"></script>
    <script src="{{$assets_folder}}/assets/js/flot/jquery.flot.min.js"></script>
    <script src="{{$assets_folder}}/assets/js/flot/jquery.flot.pie.min.js"></script>
    <script src="{{$assets_folder}}/assets/js/flot/jquery.flot.resize.min.js"></script>
    <script src="{{$assets_folder}}/assets/js/bootstrap-tag.min.js"></script>
    <script src="{{$assets_folder}}/assets/js/jquery.gritter.min.js"></script>
    <script src="{{$assets_folder}}/assets/js/bootbox.min.js"></script>


    <!-- ace scripts -->

    <script src="{{$assets_folder}}/assets/js/ace-elements.min.js"></script>
    <script src="{{$assets_folder}}/assets/js/ace.min.js"></script>

    <link rel="stylesheet" href="{{$assets_folder}}/css/custom.css" />
    <style>
        .modal.fade{top: -200%;}
        .inline-tags {
            position: relative;
            /*overflow-x: hidden;
            overflow-y: auto;*/
        }
        .inline-tags .tags {
            width: 40px;
        }
        .inline-tags .tags .tag {
            padding-left: 22px;
            padding-right: 9px;
        }
        .inline-tags .tags .tag .close {
            left: 0;
            right: auto;
        }
    </style>

    @if($res['js'])
        @foreach($res['js'] as $js_item)
            {!! $js_item !!}
        @endforeach
    @endif
    <!--script src="{{$MAIN_URL}}/apps/vue/dist/js/main.js"></script-->

@endpush
