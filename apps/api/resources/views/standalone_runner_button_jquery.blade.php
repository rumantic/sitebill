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

title="{{$modal_title}}"
{{$href_item}}
 id="{{$modal_id}}_button">
@if(isset($btnicon) and $btnicon != '')
    <i class="icon-white {{ isset($btnicon) ? $btnicon : 'icon-tasks' }}"></i>
@endif
{{$button_title}}
@if(isset($btnicon_last) and $btnicon_last != '')
    <i class="icon-white {{ isset($btnicon_last) ? $btnicon_last : 'icon-tasks' }}"></i>
@endif
</{{$button_type}}>

@push('modals')

    <div class="modal fade" id="{{$modal_id}}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content {{$params['modal_content_class']}}">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $modal_title }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="{{$modal_id}}_modalModalform">
                    <div id="{{$modal_id}}_jquery"></div>
                </div>
            </div>
        </div>
    </div>

@endpush

@push('scripts')
    <style>
        .standalone .modal-dialog,
        .standalone .modal-content {
        }

        .standalone .modal-body {
        }
    </style>
    <script type="text/javascript">

        $(document).ready(function () {
            $('#{{$modal_id}}_button').on( "click", function() {
                $('#{{$modal_id}}').modal('toggle');
                ClientOrder.init_form('{{$modal_id}}_jquery', '{{$params['model']}}', {
                    label: '{{$params['label']}}',
                    local_template: '{{$params['local_template']}}',
                    button_title: '{{$button_title}}',
                    button_class: 'btn btn-lg btn-dark px-9 rounded-3 w-100'
                }, true);
            } );
        });
    </script>
@endpush
