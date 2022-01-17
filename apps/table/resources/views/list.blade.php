@extends('layout.default')
@php
    $firstletters = [];
    foreach($tables as $table){
        $firstletters[$table['_firstletter']] = $table['_firstletter'];
    }
@endphp
@section('content')

    {{$testval}}

    <div class="row">
        <div class="col-md-12">
            <div class="firstletters">
                @foreach($firstletters as $firstletter)
                    <div class="firstletter" data-letter="{{$firstletter}}">{{$firstletter}}</div>
                @endforeach
            </div>
        </div>
    </div>


    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-5 g-xl-9">
        @foreach($tables as $table)
            <div class="col-md-4 tablecard" data-letter="{{$table['_firstletter']}}">
                <!--begin::Card-->
                <div class="card card-flush h-md-100">
                    <!--begin::Card header-->
                    <div class="card-header">
                        <!--begin::Card title-->
                        <div class="card-title">
                            <h2>{{$table['name']}}</h2>
                        </div>
                        <!--end::Card title-->
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-1">
                        @if($table['description'] != '')
                            <div class="fw-bolder text-gray-600 mb-5">{{$table['description']}}</div>
                        @endif
                        <!--end::Permissions-->
                    </div>
                    <!--end::Card body-->
                    <!--begin::Card footer-->
                    <div class="card-footer flex-wrap pt-0">
                        <a href="/admin3/tables/{{$table['table_id']}}" class="btn btn-icon btn-active-light-primary w-30px h-30px"><i class="fa fa-list"></i></a>
                        <a href="/admin3/tables/{{$table['table_id']}}/edit" class="btn btn-icon btn-active-light-primary w-30px h-30px"><i class="fa fa-edit"></i></a>
                        <a href="/admin3/tables/{{$table['table_id']}}/delete" onclick="if ( confirm('Действительно хотите удалить запись?') ) {return true;} else {return false;}" class="btn btn-icon btn-active-light-primary w-30px h-30px"><i class="fa fa-times"></i></a>
                        @if($table['_customentityhandler'] == 1)
                            <a class="btn btn-icon btn-active-light-primary w-30px h-30px btn-success" href="/admin3/tables/{{$table['table_id']}}/handleredit" title="Зарегистрированный обработчик"><i class="fa fa-asterisk"></i></a>
                        @else
                            <a class="btn btn-icon btn-active-light-primary w-30px h-30px" href="/admin3/tables/{{$table['table_id']}}/handlercreate" title="Обработчик по-умолчанию"><i class="fa fa-asterisk"></i></a>
                        @endif

                        @if($table['_tableexists'] == 1)
                            <a class="btn btn-icon btn-active-light-primary w-30px h-30px" href="?action=table&do=structure&subdo=update_table&table_name={{$table['name']}}" title="Обновить таблицу" ><i class="icon-white icon-repeat"></i></a>
                        @else
                            <a class="btn btn-icon btn-active-light-primary w-30px h-30px" href="' . SITEBILL_MAIN_URL . '/admin/?action=table&do=structure&subdo=create_table&table_name={{$table['name']}}" title="Создать таблицу"><i class="icon-white icon-list-alt"></i></a>
                        @endif

                    </div>
                    <!--end::Card footer-->
                </div>
                <!--end::Card-->
            </div>



        @endforeach

    </div>


    <style>
        .firstletters {
            margin: 5px 0 15px 0;
        }
        .firstletters .firstletter {
            display: inline-block;
            color: gray;
            font-size: 18px;
            text-transform: uppercase;
            width: 28px;
            height: 28px;
            line-height: 28px;
            text-align: center;
            cursor: pointer;
        }
        .firstletters .firstletter.active {
            background: #ffffff;
            color: black;
        }
    </style>

@endsection
@push('scripts')
    <script>
        function showTableBlocksByLetters(letters) {
            if(letters.length == 0){
                $('.tablecard').show();
            }else{
                $('.tablecard').each(function () {
                    if(-1 === letters.indexOf($(this).data('letter'))){
                        $(this).hide();
                    }else{
                        $(this).show();
                    }
                });
            }
        }
        $(document).ready(function(){
            $('.firstletter').click(function () {
                if($(this).hasClass('active')){
                    $(this).removeClass('active');
                }else{
                    $(this).addClass('active')
                }
                var letters = [];
                $(this).parents('.firstletters').eq(0).find('.firstletter').each(function () {
                    if($(this).hasClass('active')){
                        letters.push($(this).data('letter'));
                    }
                });
                showTableBlocksByLetters(letters);
            });
        });


    </script>
@endpush
