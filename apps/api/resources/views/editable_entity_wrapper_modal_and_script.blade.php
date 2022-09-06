@php
    $modal_id = 'entity_editor_wrapper_modal';
@endphp

@push('scripts')
    <style>
        .editable_entity_wrapper {
            border: 1px solid green;
            position: relative;
        }
        .standalone .modal-dialog,
        .standalone .modal-content {
            height: 90%;
        }

        .standalone .modal-body {
            /* 100% = dialog height, 120px = header + footer */
            max-height: calc(100% - 120px);
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
        .editable_entity_wrapper .editable_entity_wrapper_ctrl {
            position: absolute;
            height: 30px;
            width: 30px;
            text-align: center;
            background: #eee;
            display: none;
            top: -25px;
            font-size: 14px;
            font-weight: normal;
            cursor: pointer;
            line-height: 30px;

        }

    </style>
    <script type="text/javascript">
        $(document).ready(function () {
            $(".editable_entity_wrapper").hover(
                function () {
                    $(this).find('.editable_entity_wrapper_ctrl').show();
                },
                function () {
                    $(this).find('.editable_entity_wrapper_ctrl').hide();
                }
            );
            $(".editable_entity_wrapper .editable_entity_wrapper_ctrl").click(function(e){
                e.stopPropagation();
                let component_params = {
                    component: $(this).attr('data-entity-name'),
                    form_mode: true,
                    table_name: $(this).attr('data-entity-name'),
                    success_message: 'Запись обновлена успешно. Можете обновить страницу.',
                    primary_key: '',
                    entity_uri: $(this).attr('data-entity-uri'),
                    only_field_name: $(this).attr('data-entity-key'),
                };
                ($(this).attr('data-entity-name') == 'light_config'?delete(component_params['form_mode']):'');
                $('#{{$modal_id}}_iframe').attr(
                    'src', '{{$estate_folder}}/apps/api/rest.php?action=standalone_runner&do=run&modal_id={{$modal_id}}&' + jQuery.param( component_params )
                );

                $('#{{$modal_id}}').modal('toggle');
                return false;
            });
        });
    </script>
@endpush

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
