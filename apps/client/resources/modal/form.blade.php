@php
    $formgeneratedid = 'clientform_'.md5($entity_name.time().rand(100,999));
@endphp

@push('scripts')
<script>
    var userid = {{$user_id}}
    $(document).ready(function(){
        $('#{{$formgeneratedid}}_modal').click(function(){
            ClientOrder.init_form('{{$formgeneratedid}}_modalModalform', '{{$entity_name}}', {'user_id': userid}, '{{$entity_name}}');
            $('#{{$formgeneratedid}}_modalModal').modal('show');
        });
    });
</script>
@endpush


@push('modals')
    <div class="modal fade" id="{{$formgeneratedid}}_modalModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $modal_title }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="{{$formgeneratedid}}_modalModalform"></div>
            </div>
        </div>
    </div>
@endpush


<button id="{{$formgeneratedid}}_modal" class="btn btn-primary btn-block">{{$modal_button_title}}</button>
