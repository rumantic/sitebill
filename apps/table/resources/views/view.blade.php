@extends('layout.default')
@section('content')
    <div class="card">
        <div class="card-body pt-0"
            <div id="kt_table_users_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer" id="kt_table_users" role="grid">

                        <tbody class="applied">
                        @foreach($columns as $column)
                            <tr class="odd">
                                <!--begin::Checkbox-->
                                <td><div class="dd-move"><i class="fa fa-arrows" aria-hidden="true"></i></div></td>
                                <td>
                                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" value="1">
                                    </div>
                                </td>
                                <!--end::Checkbox-->
                                <!--begin::User=-->
                                <td class="d-flex align-items-center">

                                    <!--begin::User details-->
                                    <div class="d-flex flex-column">
                                        <a href="/metronic8/demo1/../demo1/apps/user-management/users/view.html" class="text-gray-800 text-hover-primary mb-1">{{$column['title']}}</a>
                                        <span>{{$column['name']}}</span>
                                    </div>
                                    <!--begin::User details-->
                                </td>
                                @if($column['required'] == 1)
                                    <td><a class="state_change btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1 btn-warning" href="derequired" title="Обязательное" alt="{{$column['columns_id']}}"><i class="icon-white icon-ok-circle"></i></a></td>
                                @else
                                    <td><a class="state_change btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" href="required" title="Обязательное" alt="{{$column['columns_id']}}"><i class="icon-white icon-ok-circle"></i></a></td>
                                @endif

                                @if($column['unique'] == 1)
                                    <td>Уникальное</td>
                                @else
                                    <td></td>
                                @endif


                                <!--end::User=-->
                                <!--begin::Role=-->
                                <td>
                                    @if(count($column['_groupnames']) > 0)

                                        Ограничений по группам: <span data-bs-toggle="tooltip" data-bs-trigger="hover" title="{{implode(', ', $column['_groupnames'])}}">
														{{count($column['_groupnames'])}}
													</span>
                                    @else
                                        Ограничений по группам: н/д
                                    @endif
                                </td>
                                <!--end::Role=-->
                                <!--begin::Last login=-->
                                <td data-order="2021-08-15T00:50:30+03:00">
                                    @php
                                        $icon_class = '';
                                        switch ($column['type']) {
                                            case 'checkbox' : {
                                                    $icon_class = 'icon-check';
                                                    break;
                                                }
                                            case 'geodata' :
                                            case 'gadres' : {
                                                    $icon_class = 'icon-map-marker';
                                                    break;
                                                }
                                            case 'safe_string' :
                                            case 'mobilephone' : {
                                                    $icon_class = 'icon-font';
                                                    break;
                                                }
                                            case 'uploads' :
                                            case 'uploadify_image' : {
                                                    $icon_class = 'icon-picture';
                                                    break;
                                                }
                                            case 'date' :
                                            case 'dtdate' :
                                            case 'dtdatetime' :
                                            case 'dttime' : {
                                                    $icon_class = 'icon-calendar';
                                                    break;
                                                }
                                            case 'select_box' :
                                            case 'select_box_structure' :
                                            case 'select_by_query' :
                                            case 'structure' : {
                                                    $icon_class = 'icon-tasks';
                                                    break;
                                                }
                                            case 'primary_key' : {
                                                    $icon_class = 'icon-filter';
                                                    break;
                                                }
                                            case 'textarea' :
                                            case 'textarea_editor' : {
                                                    $icon_class = 'icon-comment';
                                                    break;
                                                }
                                        }
                                    @endphp

                                    <i class="icon {{$icon_class}}"></i> {{$column['type']}}
                                </td>
                                <!--end::Last login=-->
                                <!--begin::Two step=-->
                                <td></td>
                                <!--end::Two step=-->
                                <!--begin::Joined-->
                                <!--begin::Joined-->
                                <!--begin::Action=-->
                                <td class="text-end">

                                    <a href="/admin3/tables/?action=columns&do=edit&table_name={{$column['name']}}&columns_id={{$column['columns_id']}}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" title="Редактировать"><i class="icon-white icon-pencil"></i></a>
                                    <a href="/admin3/tables/?action=columns&do=delete&table_name={{$column['name']}}&columns_id={{$column['columns_id']}}" title="Удалить" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" onclick="if ( confirm(\'Действительно хотите удалить запись?\') ) {return true;} else {return false;}"><i class="icon-white icon-remove"></i></a>
                                    @if($column['active'] == 0)
                                        <a class="state_change btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" href="activate" title="Активация" alt="' . $primary_key_value . '"><i class="icon-white icon-off"></i></a>
                                    @else
                                        <a class="state_change btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1 btn-warning" href="deactivate" title="Активация" alt="' . $primary_key_value . '"><i class="icon-white icon-off"></i></a>
                                    @endif

                                    @if(1 === intval($sitebill->getConfigValue('apps.language.use_langs')))
                                        <a class="btn btn-bg-light btn-active-color-primary btn-sm me-1" href="/admin3/tables/?action=columns&do=add_lang_fields&columns_id={{$column['columns_id']}}" title="Добавить\проверить мультиязычные поля для этого поля"><i class="icon-white icon-plus"></i> ML</a>
                                    @endif



                                    <!--begin::Menu-->
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-125px py-4" data-kt-menu="true">
                                        <!--begin::Menu item-->
                                        <div class="menu-item px-3">
                                            <a href="/metronic8/demo1/../demo1/apps/user-management/users/view.html" class="menu-link px-3">Edit</a>
                                        </div>
                                        <!--end::Menu item-->
                                        <!--begin::Menu item-->
                                        <div class="menu-item px-3">
                                            <a href="#" class="menu-link px-3" data-kt-users-table-filter="delete_row">Delete</a>
                                        </div>
                                        <!--end::Menu item-->
                                    </div>
                                    <!--end::Menu-->
                                </td>
                                <!--end::Action=-->
                            </tr>
                        @endforeach
                        </tbody>

                    </table>
                </div>

            </div>
        </div>
    </div>

@endsection
@push('scripts')
    <script>
        $(document).ready(function(){
            /*
     * применение сортировки к полям модели
     */
            $(".applied").sortable({
                handle: ".dd-move",
                stop: function (event, ui) {
                    var parent = $(ui.item).parents('.accordion-group').eq(0);
                    if (parent.length != 1) {
                        parent = $(ui.item).parents('table').eq(0);
                    }
                    if (parent.length == 1) {
                        var childs = parent.find('.column');
                        if (childs.length > 0) {
                            var ids = [];
                            var count = childs.length;
                            if (count > 0) {
                                for (var i = 0; i < count; i++) {
                                    var alt = $(childs[i]).attr('alt');
                                    if (alt != '') {
                                        ids.push(alt);
                                    }
                                }
                            }
                            if (ids.length > 0) {
                                $.ajax({
                                    url: estate_folder + '/apps/table/js/ajax.php',
                                    type: 'POST',
                                    dataType: 'text',
                                    data: 'action=reorder_columns&ids=' + ids.join(','),
                                    success: function (data) {
                                        //alert('Сортировка сохранена');
                                    }
                                });
                            }
                        }

                    }
                }
            }).disableSelection();
        });


    </script>
@endpush
