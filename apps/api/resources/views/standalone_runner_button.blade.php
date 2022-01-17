@php
$button_type = $button_type ? $button_type : 'button';
if ( $button_type == 'button' ) {
    $button_bootstrap_class = 'btn btn-small';
    $href_item = '';
} else {
    $href_item = 'href="#"';
}
@endphp

<{{$button_type}} class="{{$button_bootstrap_class}} {{ isset($btnclass) ? $btnclass : 'btn-warning' }}"
   data-toggle="modal"
   title="{{$modal_title}}"
   {{$href_item}}
   data-target="#{{$modal_id}}" id="{{$modal_id}}_button">
    <i class="icon-white {{ isset($btnicon) ? $btnicon : 'icon-tasks' }}"></i>
    {{$button_title}}
</{{$button_type}}>

@push('modals')

    <div class="modal fade standalone" id="{{$modal_id}}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $modal_title }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="{{$modal_id}}_modalModalform">
                    <iframe
                            width="100%"
                            border="0"
                            id="{{$modal_id}}_iframe"
                            src="">

                    </iframe>
                </div>
            </div>
        </div>
    </div>

@endpush

@push('scripts')
    <style>
        .standalone .modal-dialog,
        .standalone .modal-content {
            height: 97%;
        }

        .standalone .modal-body {
            /* 100% = dialog height, 120px = header + footer */
            max-height: calc(100% - 20px);
            overflow-y: hidden;
            padding: 0;
            margin: 0;
        }
        #{{$modal_id}}_iframe {
            height: 100%;
            border: 0px;
            padding: 0;
            margin: 0;
        }
    </style>
    <script type="text/javascript">
        $(document).ready(function () {
            let params_string = '{{$params_string}}'.replace(/&amp;/g, '&');
            params_string.replace(/&amp;/g, '&');
            $('#{{$modal_id}}_button').click(function () {
                    $('#{{$modal_id}}_iframe').attr(
                        'src', '{{$estate_folder}}/apps/api/rest.php?action=standalone_runner&do=run&modal_id={{$modal_id}}&' + params_string
                    );
                }
            );
        });
    </script>
@endpush
