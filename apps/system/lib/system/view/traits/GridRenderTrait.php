<?php
/**
 * GridRenderTrait — Main grid rendering for Common_Grid.
 *
 * Manages: construct_grid, _construct_grid, get_icon, docuploads_block,
 * uploads_image_wrapper, iframe_modal_control, check_column_activity_in_topic.
 */
trait GridRenderTrait
{
    function construct_grid($control_params = false, $disable_mass_delete = false)
    {
        // Автоматически добавляем кнопку active_toggle, если модель содержит поле active
        if (
            !in_array('active_toggle', $this->grid_controls) &&
            isset($this->grid_object->data_model[$this->grid_object->table_name]['active'])
        ) {
            $this->grid_controls[] = 'active_toggle';
        }

        if ( !in_array('delete', $this->grid_controls) ) {
            $disable_mass_delete = true;
        }

        //Регистрируем hook для обработки элементов грида при выводе
        if (function_exists('BeforePrintGridItem')) {
            $BeforePrintGridItem = true;
        } else {
            $BeforePrintGridItem = false;
        }

        if ($this->grid_query != '') {

            return $this->_construct_grid($control_params, $disable_mass_delete);
        } else {
            if ( $this->grid_object->primary_key == '' ) {
                return _e('Для таблицы').' '.$this->grid_object->primary_key.' '._e('не задан ').'primary_key';
            }


            $DBC = DBC::getInstance();
            $pager_params = array();
            $sort_params = array();

            $pager_params = $this->pager_params;
            foreach ($pager_params as $key => $value) {
                if ($key != 'per_page') {
                    if ($key == 'page') {
                        //$sort_params[]='page=1';
                    } else {
                        $sort_params[] = $key . '=' . $value;
                    }
                }
            }

            $table_and_prefix = DB_PREFIX . '_' . $this->grid_object->table_name;

            $sort_params[] = 'page=1';
            if ($this->getRequestValue('_sortby') == '') {
                $sortby = $this->grid_object->primary_key;
            } else {
                $sortby = $this->getRequestValue('_sortby');
            }
            $sortdir = $this->getRequestValue('_sortdir');
            if ($sortdir == '') {
                $sortdir = 'DESC';
            }
            $pager_params['_sortby'] = $sortby;
            $pager_params['_sortdir'] = $sortdir;
            $sortby = $table_and_prefix . '.' . $sortby;

            $where = array();

            if (!empty($this->conditions)) {
                foreach ($this->conditions as $key => $value) {
                    if (is_numeric($key)) {
                        $sub = array();
                        foreach ($value as $k => $subv) {
                            if (is_array($subv)) {
                                $sub[] = '(`' . $table_and_prefix . '`.`' . $k . '` IN (' . implode(', ', $subv) . '))';
                            } else {
                                $sub[] = '(`' . $table_and_prefix . '`.`' . $k . '`=\'' . $subv . '\')';
                                //$sort_params[] = $k . '=' . $subv;
                            }
                        }
                        $where[] = '(' . implode(' OR ', $sub) . ')';
                    } else {
                        if (is_array($value)) {
                            $where[] = '(`' . $table_and_prefix . '`.`' . $key . '` in (\'' . implode('\', \'', $value) . '\'))';
                        } else {
                            $where[] = '(`' . $table_and_prefix . '`.`' . $key . '`=\'' . $value . '\')';
                            $sort_params[] = $table_and_prefix . '.' . $key . '=' . $value;
                        }
                    }

                }
                $pager_params = array_merge($pager_params, $this->conditions);
            }
            if (!empty($this->conditions_sql)) {
                foreach ($this->conditions_sql as $sql_condition) {
                    $where[] = $sql_condition;
                }
            }
            $left_join_tables = ' ';
            if (!empty($this->conditions_left_join)) {
                $left_join_tables = implode(' ', $this->conditions_left_join['tables']);
            }

            $tagged_params = $this->add_tags_params();
            $where = $this->add_tagged_parms_to_where($where, $tagged_params, $this->grid_object->table_name);

            $query_no_limit = 'SELECT `' . DB_PREFIX . '_' . $this->grid_object->table_name . '`.* FROM ' . DB_PREFIX . '_' . $this->grid_object->table_name . $left_join_tables . (!empty($where) ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY ' . $sortby . ' ' . $sortdir . ' ';
            $query_no_limit_total_count = 'SELECT count(`' . DB_PREFIX . '_' . $this->grid_object->table_name . '`' . '.`' . $this->grid_object->primary_key . '`) as total FROM ' . DB_PREFIX . '_' . $this->grid_object->table_name . $left_join_tables . (!empty($where) ? ' WHERE ' . implode(' AND ', $where) : '');
            $query = 'SELECT `' . DB_PREFIX . '_' . $this->grid_object->table_name . '`.* FROM ' . DB_PREFIX . '_' . $this->grid_object->table_name . $left_join_tables . (!empty($where) ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY ' . $sortby . ' ' . $sortdir . ' ' . (isset($this->per_page) ? 'LIMIT ' . (($this->current_page - 1) * $this->per_page) . ', ' . $this->per_page : '');
            //$this->writeLog($query);
            //$this->writeLog($query_no_limit);
            //$this->writeLog($query_no_limit_total_count);


            $result = $this->get_query_cache_value($query_no_limit_total_count, array());
            $total_count = 0;
            if ($result['result'] === true) {
                $total_count = $result['value'];
            } else {
                $stmt = $DBC->query($query_no_limit_total_count);
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    $total_count = $ar['total'];
                    $this->insert_query_cache_value($query_no_limit_total_count, array(), $total_count);
                }
            }


            $this->set_total_count($total_count);

            $this->set_grid_query($query_no_limit);
            //echo $total_count;
            $stmt = $DBC->query($query);
            if (!$stmt && $this->current_page != 1) {
                $this->current_page = 1;
                $query = 'SELECT `' . DB_PREFIX . '_' . $this->grid_object->table_name . '`.* FROM ' . DB_PREFIX . '_' . $this->grid_object->table_name . $left_join_tables . (!empty($where) ? ' WHERE ' . implode('AND', $where) : '') . ' ORDER BY ' . $sortby . ' ' . $sortdir . ' ' . (isset($this->per_page) ? 'LIMIT ' . (($this->current_page - 1) * $this->per_page) . ', ' . $this->per_page : '');
                $stmt = $DBC->query($query);
            }
            $empty_data = false;
            if (!$stmt) {
                //return false;
                $empty_data = true;
            }

            if (!$empty_data) {
                while ($ar = $DBC->fetch($stmt)) {
                    $ra[$ar[$this->grid_object->primary_key]] = $ar;
                }
            } else {
                $ra = array();
            }

            if (!empty($ra) && $this->grid_object->table_name == 'data') {
                if (1 == intval($this->getConfigValue('use_topic_actual_days'))) {
                    $DBC = DBC::getInstance();
                    $topic_actuals = array();

                    $query = 'SELECT id, actual_days FROM ' . DB_PREFIX . '_topic';
                    $stmt = $DBC->query($query);
                    if ($stmt) {
                        while ($ar = $DBC->fetch($stmt)) {
                            $topic_actuals[$ar['id']] = $ar['actual_days'];
                        }
                    }
                    foreach ($ra as $k => $v) {
                        $actual_adv_days = floor((time() - strtotime($v['date_added'])) / (24 * 3600));
                        if (isset($topic_actuals[$v['topic_id']]) && intval($topic_actuals[$v['topic_id']]) > 0 && $actual_adv_days > $topic_actuals[$v['topic_id']]) {
                            $ra[$k]['_classes'] = 'actuality_expired';
                        }

                    }
                }
            }


            //Отсюда начинаем формировать таблицу со всеми подключаемыми плагинами
            $rs = '';
            $rs .= $this->get_pre_header();


            $rs .= '<div class="' . $this->css('grid_wrapper') . '">';
            $rs .= '<table class="' . $this->css('table') . '">';
            $rs .= '<thead class="' . $this->css('thead') . '">';
            $rs .= '<tr>';
            if (!$disable_mass_delete) {
                $rs .= '<th class="' . $this->css('th') . '"><input type="checkbox" class="grid_check_all ' . $this->css('checkbox') . '" /></td>';
            }
            //echo $sortby;
            $sort_url = @$this->grid_url;
            if ($sort_url == '') {
                $sort_url = SITEBILL_MAIN_URL . '/admin/index.php';
            }

            foreach ($this->grid_items as $item_id => $item_name) {
                if (!isset($this->grid_object->data_model[$this->grid_object->table_name][$item_name])) {
                    unset($this->grid_items[$item_id]);
                }
            }


            foreach ($this->grid_items as $item_id => $item_name) {

                $rs .= '<th ';
                if ($this->grid_object->data_model[$this->grid_object->table_name][$item_name]['type'] == 'primary_key') {
                    $rs .= 'width="1%"';
                }
                if ($sortby == $table_and_prefix . '.' . $item_name) {
                    if (strtolower($sortdir) == 'asc') {
                        $sortdirn = 'desc';
                        $sorted = $this->css('sort_asc');
                    } else {
                        $sortdirn = 'asc';
                        $sorted = $this->css('sort_desc');
                    }
                } else {
                    $sortdirn = 'asc';
                    $sorted = '';
                }
                $_sort = $sort_params;

                $_sort[] = '_sortby=' . $item_name;
                $_sort[] = '_sortdir=' . $sortdirn;
                $s = '<a class="' . $this->css('sort_link') . ' ' . $sorted . '" href="' . $sort_url . '?' . implode('&', $_sort) . '">' . $this->grid_object->data_model[$this->grid_object->table_name][$item_name]['title'] . '</a>';

                if ($this->grid_object->data_model[$this->grid_object->table_name][$item_name]['type'] == 'price') {
                    $tags_input = '<div class="ranged-tags" data-field="' . $item_name . '">
    						<div class="ranged-tags-title"></div>
    						<div class="ranged-tags-params" style="display: none;">
    						<input name="' . $item_name . '[min]" type="text" class="tagged_input ' . $this->css('ranged_input') . '" value="' . $_SESSION['model_tags'][$this->grid_object->table_name]['tags_array'][$item_name]['min'] . '">
    						<input name="' . $item_name . '[max]" type="text" class="tagged_input ' . $this->css('ranged_input') . '" value="' . $_SESSION['model_tags'][$this->grid_object->table_name]['tags_array'][$item_name]['max'] . '">
    						<a href="#" class="' . $this->css('ranged_btn_danger') . ' clear" title="очистить фильтр"><i class="' . $this->css('icon_delete') . '"></i></a>
    						<a href="#" class="' . $this->css('ranged_btn_success') . ' apply" title="применить фильтр"><i class="' . $this->css('icon_check') . '"></i></a>
    						<a href="#" class="' . $this->css('ranged_btn_cancel') . ' cancel" title="скрыть окно фильтра"><i class="fa fa-power-off"></i></a>
    						</div>
    						</div>';
                    $rs .= '>' . $s . $tags_input . '</th>';
                } else {
                    $tags_input = $this->get_tags_input($item_name);
                    $rs .= '>' . $s . $tags_input . '</th>';
                }
            }


            if (count($this->grid_controls) > 0) {
                $rs .= '<th class="' . $this->css('th') . '" width="1%"><a class="' . $this->css('clear_link') . ' tags-clear" href="">' . _e('Очистить') . '</a></th>';
            }
            $rs .= '</tr>';
            $rs .= '</thead>';
            $rs .= '<tbody class="' . $this->css('tbody') . '">';

            if (count($ra) > 0) {


                foreach ($ra as $primary_key_value => $item_array) {
                    $ids[] = $primary_key_value;
                }

                $row_datas = $this->grid_object->load_by_id($ids);
                $row_datas = $this->grid_object->applyGCompose($row_datas);

                //echo count($row_datas);


                $checked_accesses = array();
                //var_dump($row_data);
                //exit();
                foreach ($ra as $primary_key_value => $item_array) {
                    //$row_data = $this->grid_object->load_by_id($primary_key_value);
                    $row_data = $row_datas[$primary_key_value];
                    if ($BeforePrintGridItem) {
                        $row_data = BeforePrintGridItem($row_data, $control_params);
                    }
                    if ( !isset($check_control_name) ) {
                        $check_control_name = '';
                    }
                    $has_access = $this->check_access(
                        $this->grid_object->action,
                        $this->get_render_user_id(),
                        $check_control_name,
                        $this->grid_object->primary_key,
                        $primary_key_value);

                    $tr_class = ((isset($item_array['active']) && $item_array['active'] == 0) ?
                            $this->css('tr_inactive') :
                            $this->css('tr')) .
                        ((isset($item_array['_classes']) && $item_array['_classes'] != '') ?
                            ' ' . $item_array['_classes'] : '');

                    if (
                        $this->getConfigValue('apps.data.allow_postponded') and
                        isset($row_data['postponded_to']) and
                        strtotime($row_data['postponded_to']['value']) > time()
                    ) {
                        $tr_class = $this->css('tr_warning');

                    }

                    $rs .= '<tr class="' . $tr_class . '">';

                    $grid_counter = 0;
                    if (!$disable_mass_delete) {
                        $grid_counter = 1;
                        $rs .= '<td class="' . $this->css('td') . '"><input type="checkbox" class="grid_check_one ' . $this->css('checkbox') . '" value="' . $primary_key_value . '" /></td>';
                    }

                    foreach ($this->grid_items as $item_id => $item_name) {
                        $grid_counter++;

                        if (isset($row_data[$item_name]['parameters']['only_owner_access']) && $row_data[$item_name]['parameters']['only_owner_access'] == 1) {
                            if (!$has_access and $_SESSION['current_user_group_name'] != 'admin') {
                                $row_data[$item_name]['value'] = _e('скрыто');
                                $row_data[$item_name]['value_string'] = _e('скрыто');
                            }
                            //echo '<pre>';
                            //print_r($row_data[$item_name]);
                            //print_r($this->grid_object->data_model[$this->grid_object->table_name]);
                            //echo '</pre>';
                        }
                        if (is_array($row_data[$item_name]) and
                            isset($row_data[$item_name]['parameters']) and
                            is_array($row_data[$item_name]['parameters']) and
                            isset($row_data[$item_name]['parameters']['api_version']) ) {

                            $this->template->assign('api', $row_data[$item_name]['api']);
                            $this->template->assign('model_item', $row_data);

                            $row_data[$item_name]['value_string'] = $row_data[$item_name]['value_string'].
                                $this->template->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/system/fields/api_call.tpl');

                        }
                        if (isset($row_data[$item_name]['parameters']) and
                            is_array($row_data[$item_name]['parameters']) and
                            isset($row_data[$item_name]['parameters']['whatstapp_decorator']) and
                            $row_data[$item_name]['parameters']['whatstapp_decorator'] == 1 ) {
                            $row_data[$item_name]['value'] = '<a href="https://wa.me/'.$row_data[$item_name]['value'].'" target="_blank">'.$row_data[$item_name]['value'].'</a>';
                        }


                        /* if($row_data[$item_name]['name']=='name'){
                          $a=' class="editable_name_field" data-key="'.$this->grid_object->primary_key.'" data-fid="'.$primary_key_value.'" data-tbl="'.$this->grid_object->action.'"';
                          }else{
                          $a='';
                          } */
                        $a = '';

                        if ($row_data[$item_name]['type'] == 'money') {
                            $rs .= '<td>' . $row_data[$item_name]['value_string'] . '</td>';
                        } elseif ($row_data[$item_name]['type'] == 'primary_key' and $this->grid_object->table_name == 'data') {
                            $rs .= '<td><a href="'.$this->getRealtyHREF($row_data[$item_name]['value']).'" target="_blank">' . $row_data[$item_name]['value'] . '</a></td>';
                        } elseif ($row_data[$item_name]['type'] == 'select_by_query') {
                            $rs .= '<td>' . ($row_data[$item_name]['value'] != 0 ? $row_data[$item_name]['value_string'] . ' <small>('.$row_data[$item_name]['value'].')</small>' : '') . '</td>';
                        } elseif ($row_data[$item_name]['type'] == 'select_by_query_multi') {
                            $rs .= '<td>' . implode(', ', $row_data[$item_name]['value_string']) . '</td>';
                        } elseif ($row_data[$item_name]['type'] == 'structure') {
                            $rs .= '<td>' . $row_data[$item_name]['value_string'] . '</td>';
                        } elseif ($row_data[$item_name]['type'] == 'select_box_structure') {
                            $rs .= '<td>' . $row_data[$item_name]['value_string'] . '</td>';
                        } elseif ($row_data[$item_name]['type'] == 'date') {
                            $rs .= '<td  >' . $row_data[$item_name]['value_string'] . '</td>';
                        } elseif ($row_data[$item_name]['type'] == 'client_id') {
                            $rs .= '<td>' . $row_data[$item_name]['value_string'] . '</td>';
                        } elseif ($row_data[$item_name]['type'] == 'select_box') {
                            if (isset($row_data[$item_name]['parameters']) && isset($row_data[$item_name]['parameters']['multiselect']) && $row_data[$item_name]['parameters']['multiselect'] == 1) {
                                $rs .= '<td>' . (!empty($row_data[$item_name]['value_variants_array']) ? implode('<br>', $row_data[$item_name]['value_variants_array']) : '') . '</td>';
                            } else {
                                $rs .= '<td>' . $row_data[$item_name]['select_data'][$row_data[$item_name]['value']] . '</td>';
                            }
                        } elseif ($row_data[$item_name]['type'] == 'photo') {
                            if ($row_data[$item_name]['value'] != '') {
                                $rs .= '<td><img width="100" src="/img/data/user/' . $row_data[$item_name]['value'] . '"></td>';
                            } else {
                                $rs .= '<td></td>';
                            }
                        } elseif ($row_data[$item_name]['type'] == 'checkbox') {
                            $rs .= '<td>' . ($row_data[$item_name]['value'] == 1 ? '<img src="' . SITEBILL_MAIN_URL . '/apps/admin/admin/template/img/radio_yes.png">' : '<img src="' . SITEBILL_MAIN_URL . '/apps/admin/admin/template/img/radio_no.png">') . '</td>';
                        } elseif ($row_data[$item_name]['type'] == 'uploads') {
                            $rs .= '<td>';
                            if (is_array($row_data[$item_name]['value']) and count($row_data[$item_name]['value']) > 0) {
                                $rs .= $this->uploads_image_wrapper($row_data, $item_name, $primary_key_value);
                            }
                            $rs .= '</td>';
                        } elseif ($row_data[$item_name]['type'] == 'docuploads') {
                            $rs .= '<td>' .
                                $this->docuploads_block($row_data[$item_name]['value']).
                                '</td>';
                        } else {
                            if (is_array($row_data[$item_name]['value'])) {
                                $rs .= '<td>' . implode(';', $row_data[$item_name]['value']) . '</td>';
                            } else {
                                $rs .= '<td' . $a . '>' . $this->reducer_text($row_data[$item_name]['value']) . '</td>';
                            }
                        }
                    }

                    if (count($this->grid_controls) > 0) {
                        $rs .= '<td nowrap class="' . $this->css('td_controls') . ' ' . ((isset($item_array['active']) && $item_array['active'] == 0) ? $this->css('tr_inactive') : '') . ' account-grid-controls">';
                        foreach ($this->grid_controls as $control_id => $control_name) {
                            if (is_array($control_name)) {
                                $check_control_name = $control_name['name'];
                            } else {
                                $check_control_name = $control_name;
                            }
                            if ($control_name == 'memorylist') {
                                $rs .= $this->compile_memory_control($primary_key_value);
                                continue;
                            } elseif ($control_name == 'fast_preview') {
                                $rs .= ' <button data-id="' . $primary_key_value . '" class="fast_preview ' . $this->css('btn_mini') . '"><i class="' . $this->css('icon_eye') . '"></i></button> ';
                                continue;
                            } elseif ($control_name == 'active_toggle') {
                                $active_val = isset($item_array['active']) ? intval($item_array['active']) : 1;
                                $btn_class  = $active_val ? $this->css('btn_active_on') : $this->css('btn_active_off');
                                $title_text = $active_val ? _e('выключить') : _e('включить');
                                $rs .= ' <a title="' . $title_text . '" data-id="' . $primary_key_value . '" data-model="' . $this->grid_object->table_name . '" data-active="' . $active_val . '" data-class-on="' . $this->css('btn_active_on') . '" data-class-off="' . $this->css('btn_active_off') . '" data-inactive-tr="' . $this->css('tr_inactive') . '" class="active_toggle_grid ' . $btn_class . '"><i class="' . $this->css('icon_active') . '"></i></a> ';
                                continue;
                            }

                            if (!$has_access) {
                                continue;
                            }
                            if ($control_name == 'view') {
                                $control_params_view_string = '';
                                if (!empty($control_params['view'])) {
                                    $control_params_view_string = $control_params['view'];
                                }
                                /* if(!empty($this->controls_params)){
                                  $control_params_view_string.='&'.http_build_query($this->controls_params);
                                  } */
                                $rs .= ' <a href="?action=' . $this->grid_object->action . '&do=view&' . $this->grid_object->primary_key . '=' . $primary_key_value . $control_params_view_string . '" class="' . $this->css('btn_view') . '"><i class="' . $this->css('icon_view') . '"></i></a> ';
                            } elseif ($control_name == 'edit') {
                                $control_params_edit_string = '';
                                if (!empty($control_params['edit'])) {
                                    $control_params_edit_string = $control_params['edit'];
                                }
                                /* if(!empty($this->controls_params)){
                                  $control_params_edit_string.='&'.http_build_query($this->controls_params);
                                  } */
                                $rs .= ' <a href="?action=' . $this->grid_object->action . '&do=edit&' . $this->grid_object->primary_key . '=' . $primary_key_value . $control_params_edit_string . '" class="' . $this->css('btn_edit') . '"><i class="' . $this->css('icon_edit') . '"></i></a> ';
                            } elseif ($control_name == 'delete') {
                                $control_params_delete_string = '';
                                if (!empty($control_params['delete'])) {
                                    $control_params_delete_string = $control_params['delete'];
                                }
                                /* if(!empty($this->controls_params)){
                                  $control_params_delete_string.='&'.http_build_query($this->controls_params);
                                  } */
                                $rs .= ' <a href="?action=' . $this->grid_object->action . '&do=delete&' . $this->grid_object->primary_key . '=' . $primary_key_value . $control_params_delete_string . '" onclick="if ( confirm(\'' . _e('Действительно хотите удалить запись?') . '\') ) {return true;} else {return false;}" class="' . $this->css('btn_delete') . '"><i class="' . $this->css('icon_delete') . '"></i></a> ';
                            } elseif ($control_name == 'reservation') {
                                if (  $this->getConfigValue('apps.reservation.control_button_in_grid') ) {
                                    $rs .= ' <a href="' . SITEBILL_MAIN_URL . '/account/reservation/my/' . $primary_key_value . self::$_trslashes . '" class="' . $this->css('btn_reservation') . '"><i class="' . $this->css('icon_money') . '" aria-hidden="true"></i></a> ';
                                }
                            } elseif ($control_name == 'apps.data.controls_js') {
                                $this->template->assign('grid_item', $row_data);
                                $this->template->assign('disable_td_wrap', true);
                                $rs .= $this->template->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/admin/admin/template1/controls.tpl');
                            } else {
                                $control_params_oth_string = '';

                                /* if(!empty($this->controls_params)){
                                  $control_params_oth_string.='&'.http_build_query($this->controls_params);
                                  } */
                                if (is_array($control_name)) {
                                    if ($control_name['type'] == 'iframe_modal') {
                                        $rs .= $this->iframe_modal_control($this->grid_object->primary_key, $primary_key_value, $control_name);
                                    } elseif($control_name['type'] == 'conditional'){
                                        // условная кнопка со стандартным генератором ссылки
                                        $res = call_user_func(array($control_name['object'], $control_name['func']), $row_data);
                                        if($res && isset($control_name['variants']['1'])){
                                            $cdata = $control_name['variants']['1'];
                                            $rs .= ' <a href="?action=' . $this->grid_object->action . '&do=' . $cdata['name'] . '&' . $this->grid_object->primary_key . '=' . $primary_key_value . $control_params_oth_string . '" class="btn ' . ($cdata['btnclass'] != '' ? $cdata['btnclass'] : 'btn-warning') . '"><i class="icon-white ' . ($cdata['btnicon'] != '' ? $cdata['btnicon'] : 'icon-tasks') . '"></i>' . ($cdata['btntext'] != '' ? ' ' . $cdata['btntext'] : '') . '</a> ';
                                        }elseif(!$res && isset($control_name['variants']['0'])){
                                            $cdata = $control_name['variants']['0'];
                                            $rs .= ' <a href="?action=' . $this->grid_object->action . '&do=' . $cdata['name'] . '&' . $this->grid_object->primary_key . '=' . $primary_key_value . $control_params_oth_string . '" class="btn ' . ($cdata['btnclass'] != '' ? $cdata['btnclass'] : 'btn-warning') . '"><i class="icon-white ' . ($cdata['btnicon'] != '' ? $cdata['btnicon'] : 'icon-tasks') . '"></i>' . ($cdata['btntext'] != '' ? ' ' . $cdata['btntext'] : '') . '</a> ';
                                        }

                                    } elseif($control_name['type'] == 'custom'){
                                        // пользовательская кнопка с генератором, определенным в локализации
                                        $rs .= call_user_func(array($control_name['object'], $control_name['func']), $row_data);
                                    } else {
                                        $rs .= ' <a href="?action=' . $this->grid_object->action . '&do=' . $control_name['name'] . '&' . $this->grid_object->primary_key . '=' . $primary_key_value . $control_params_oth_string . '" class="btn ' . ($control_name['btnclass'] != '' ? $control_name['btnclass'] : 'btn-warning') . '"><i class="icon-white ' . ($control_name['btnicon'] != '' ? $control_name['btnicon'] : 'icon-tasks') . '"></i>' . ($control_name['btntext'] != '' ? ' ' . $control_name['btntext'] : '') . '</a> ';
                                    }
                                } else {
                                    $rs .= ' <a href="?action=' . $this->grid_object->action . '&do=' . $control_name . '&' . $this->grid_object->primary_key . '=' . $primary_key_value . $control_params_oth_string . '" class="' . $this->css('btn_warning') . '"><i class="'.$this->get_icon($control_name).'"></i></a> ';
                                }
                            }
                            /*if (is_array($control_name)) {
                                foreach ($control_name as $custom_control_name => $custom_control_string) {
                                    $custom_code = str_replace('{primary_key_value}', $primary_key_value, $custom_control_string);
                                    $rs .= $custom_code . ' ';
                                }
                            }*/
                        }
                        $rs .= '</td>';
                    }
                    $rs .= '</tr>';
                    $admin_mode = false;
                    if (defined('ADMIN_MODE') and ADMIN_MODE == 1) {
                        $admin_mode = true;
                    }

                    if ((!$admin_mode && $this->grid_object->table_name == 'complex' && intval($this->getConfigValue('apps.complex.use_billing')) == 1) || ($this->getConfigValue('apps.billing.enable') and $this->grid_object->table_name == 'data' and $this->check_access($this->grid_object->action, $this->get_render_user_id(), 'edit', $this->grid_object->primary_key, $primary_key_value))) {

                        $rs .= '<tr>';
                        $rs .= '<td colspan="' . (count($this->grid_controls) + $grid_counter) . '">' . $this->billing_controls($row_data) . '</td>';
                        $rs .= '</tr>';
                    }

                }

                if (!$disable_mass_delete || $this->batchUpdate || $this->batchActivate) {
                    $rs .= '<tr><td colspan="' . (count($this->grid_controls) + $grid_counter) . '">';
                    if (!$disable_mass_delete) {
                        if ($this->massDeleteUrl != '') {
                            $rs .= '<button data-url="' . $this->massDeleteUrl . '" class="mass_delete ' . $this->css('btn_danger') . '"><i class="' . $this->css('icon_delete') . '"></i> ' . Multilanguage::_('L_DELETE_CHECKED') . '</button> ';
                        } else {
                            $rs .= '<button alt="' . $this->grid_object->action . '" class="delete_checked ' . $this->css('btn_danger') . '"><i class="' . $this->css('icon_delete') . '"></i> ' . Multilanguage::_('L_DELETE_CHECKED') . '</button> ';
                        }
                    }
                    if ($this->batchUpdate) {
                        $rs .= '<button alt="' . $this->grid_object->action . '" class="batch_update ' . $this->css('btn_inverse') . '"><i class="fa fa-th"></i> ' . _e('Пакетная обработка') . '</button> ';
                    }
                    if ($this->batchActivate) {
                        $rs .= '<button alt="' . $this->grid_object->action . '" data-action="activate" class="mass_action ' . $this->css('btn_inverse') . '">' . _e('Активировать') . '</button> ';
                        $rs .= '<button alt="' . $this->grid_object->action . '" data-action="deactivate" class="mass_action ' . $this->css('btn_inverse') . '">' . _e('Архивировать') . '</button> ';
                    }
                    $rs .= '</td></tr>';
                }
                /*if (!$disable_mass_delete) {
                    $rs .= '<tr><td colspan="' . (count($this->grid_controls) + $grid_counter) . '"><button alt="' . $this->grid_object->action . '" class="delete_checked btn btn-danger"><i class="icon-white icon-remove"></i> ' . Multilanguage::_('L_DELETE_CHECKED') . '</button></td></tr>';
                }*/
                $query = 'SELECT COUNT(' . $this->grid_object->primary_key . ') AS _cnt FROM ' . DB_PREFIX . '_' . $this->grid_object->table_name . (!empty($where) ? ' WHERE ' . implode('AND', $where) : '');

                $result = $this->get_query_cache_value($query, array());
                if ($result['result'] === true) {
                    $total = $result['value'];
                } else {
                    $stmt = $DBC->query($query);
                    if ($stmt) {
                        $ar = $DBC->fetch($stmt);
                        $total = $ar['_cnt'];
                        $this->insert_query_cache_value($query, array(), $total);
                    } else {
                        $total = 0;
                    }
                }

                $page_links_list = $this->get_page_links_list($this->current_page, $total, $this->per_page, $pager_params);
                if ($page_links_list != '') {
                    $rs .= '<tr><td colspan="' . (count($this->grid_controls) + $grid_counter) . '" class="' . $this->css('pager') . '"><div align="center">';
                    $rs .= $page_links_list;
                    $rs .= '</div></td></tr>';
                }

            } else {
                $rs .= '<tr>';
                $rs .= '<td colspan="' . (count($this->grid_controls) + count($this->grid_items)) . '"><p align="center" class="' . $this->css('alert_empty') . '">' . _e('Ничего не найдено') . '</p></td>';
                $rs .= '</tr>';
            }

            $rs .= '</tbody>';

            $rs .= '</table>';
            $rs .= '</div>';

            if ($this->grid_object->table_name == 'complex' && intval($this->getConfigValue('apps.complex.use_billing')) == 1) {
                $rs .= $this->getB();
            }
            $rs .= $this->get_tooltip_script();
            $rs .= $this->get_active_toggle_script();


            return $rs;
        }
    }

    protected function get_icon ( $action ) {
        if ($this->isTailwind()) {
            $icons = [
                'login_as' => 'fa fa-key',
                'default' => 'fa fa-tasks'
            ];
        } else {
            $icons = [
                'login_as' => 'icon-white fa-key',
                'default' => 'icon-white icon-tasks'
            ];
        }
        if ( isset($icons[$action]) ) {
            return $icons[$action];
        } else {
            return $icons['default'];
        }
    }

    protected function get_active_toggle_script() {
        foreach ($this->grid_controls as $ctrl) {
            if ($ctrl === 'active_toggle') {
                return '
<script>
$(document).on("click", ".active_toggle_grid", function(e) {
    e.preventDefault();
    var _this = $(this);
    var id = _this.data("id");
    var model = _this.data("model");
    var active = parseInt(_this.attr("data-active"));
    var newActive = (active === 1) ? 0 : 1;
    var classOn = _this.data("class-on");
    var classOff = _this.data("class-off");
    var inactiveTr = _this.data("inactive-tr");
    var ql_items = {active: newActive};
    $.ajax({
        url: estate_folder + "/apps/api/rest.php",
        data: {
            action: "model",
            do: "graphql_update",
            model_name: model,
            only_ql: true,
            key_value: id,
            ql_items: ql_items
        },
        type: "post",
        dataType: "json",
        success: function(result) {
            if (result.state === "success") {
                _this.attr("data-active", newActive);
                if (newActive === 0) {
                    _this.attr("title", "включить");
                    _this.removeClass(classOn).addClass(classOff);
                    _this.parents("tr").eq(0).addClass(inactiveTr);
                } else {
                    _this.attr("title", "выключить");
                    _this.removeClass(classOff).addClass(classOn);
                    _this.parents("tr").eq(0).removeClass(inactiveTr);
                }
            }
        }
    });
});
</script>
';
            }
        }
        return '';
    }

    private function docuploads_block ( $docuploads )
    {
        $rs = '';
        if ( is_array($docuploads) and count($docuploads) > 0 ) {
            foreach ( $docuploads as $item ) {
                $rs .= '<a href="'.$this->createUrlTpl('/img/mediadocs/'.$item['normal']).'">'.$item['normal'].'</a></br>';
            }
        }
        return $rs;
    }

    private function uploads_image_wrapper ($row_data, $item_name, $primary_key_value) {
        $counter = 0;
        $rs = '<div class="colorboxed" style="max-width: 150px;" data-cbxid="'.$primary_key_value.'">';
        $rs .= '<div class="photoslider'.$primary_key_value.'">';
        $style = '';
        foreach ($row_data[$item_name]['value'] as $vv) {
            $normal_url = $this->createMediaIncPath($vv);
            $prev_url = $this->createMediaIncPath($vv, 'preview');
            if ( $counter > 0 ) {
                $style = 'style="display: none;"';
            }
            $rs .= '<a '.$style.' href="'.$normal_url.'" data-thumb="'.$prev_url.'"></a>';
            $counter++;
        }
        $rs .= '</div>';
        $rs .= '</div>';

        return $rs;
    }

    private function iframe_modal_control($primary_key, $primary_key_value, $params)
    {
        if (!$this->API_standalone_runner) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/api/classes/class.common.php');
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/api/classes/class.standalone_runner.php');
            $this->API_standalone_runner = new API_standalone_runner();
        }
        $params['object_id'] = $primary_key_value;
        $params['primary_key'] = $primary_key;
        $params['primary_key_value'] = $primary_key_value;
        return $this->API_standalone_runner->_iframe_button(
            $params['name'],
            $params['modal_title'],
            $params['component'],
            $params
        );
    }

    private function check_column_activity_in_topic ( $column_name, $row_data ) {
        if ( !isset($this->grid_object->data_model_shared) ) {
            return true;
        }
        $active_in_topic = $this->grid_object->data_model_shared[$this->grid_object->table_name][$column_name]['active_in_topic'];
        if ( $active_in_topic != '' ) {
            $active_in_topic_array = explode(',', $active_in_topic);
            if ( is_array($active_in_topic_array) and count($active_in_topic_array) > 0 and $active_in_topic_array[0] != 0 ) {
                if ( isset($row_data['topic_id']) and !in_array($row_data['topic_id']['value'], $active_in_topic_array)) {
                    return false;
                }
            }
        }
        return true;
    }

    private function _construct_grid($control_params = false, $disable_mass_delete = false)
    {
        $ra = array();
        $query = $this->grid_query . ' ' . (isset($this->per_page) ? 'LIMIT ' . (($this->current_page - 1) * $this->per_page) . ', ' . $this->per_page : '');

        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if (!$stmt && $this->current_page != 1) {
            $this->current_page = 1;
            $query = $this->grid_query . ' ' . (isset($this->per_page) ? 'LIMIT ' . (($this->current_page - 1) * $this->per_page) . ', ' . $this->per_page : '');
            $stmt = $DBC->query($query);
        }

        if (!$stmt) {
            return false;
        }

        while ($ar = $DBC->fetch($stmt)) {
            $ra[$ar[$this->grid_object->primary_key]] = $ar;
        }

        if (count($ra) > 0) {
            $rs = '<div class="' . $this->css('grid_wrapper') . '"><table border="0" width="99%" class="' . $this->css('table') . '">';
            $rs .= '<thead class="' . $this->css('thead') . '">';
            $rs .= '<tr>';
            if (!$disable_mass_delete) {
                $rs .= '<th class="' . $this->css('th') . '" width="1%"><input type="checkbox" class="grid_check_all ' . $this->css('checkbox') . '" /></td>';
            }
            foreach ($this->grid_items as $item_id => $item_name) {

                $rs .= '<th class="' . $this->css('th') . '"';
                if ($this->grid_object->data_model[$this->grid_object->table_name][$item_name]['type'] == 'primary_key') {
                    $rs .= 'width="1%"';
                }
                $rs .= '>' . $this->grid_object->data_model[$this->grid_object->table_name][$item_name]['title'] . '</th>';
            }
            if (count($this->grid_controls) > 0) {
                $rs .= '<th class="' . $this->css('th') . '" width="1%"></th>';
            }
            $rs .= '</tr>';
            $rs .= '</thead>';
            $rs .= '<tbody class="' . $this->css('tbody') . '">';
            foreach ($ra as $primary_key_value => $item_array) {
                $row_data = $this->grid_object->load_by_id($primary_key_value);
                $rs .= '<tr class="' . $this->css('tr') . '">';
                if (!$disable_mass_delete) {
                    $rs .= '<td class="' . $this->css('td') . '"><input type="checkbox" class="grid_check_one ' . $this->css('checkbox') . '" value="' . $primary_key_value . '" /></td>';
                }
                $grid_counter = 0;
                foreach ($this->grid_items as $item_id => $item_name) {
                    $grid_counter++;
                    if ($this->grid_items_render_objects[$item_name]) {
                        $rs .= '<td>' . $this->grid_items_render_objects[$item_name]->fetch_template($item_name, $row_data) . '</td>';
                    } elseif ($row_data[$item_name]['type'] == 'select_by_query') {
                        $rs .= '<td>' . ($row_data[$item_name]['value'] != 0 ? $row_data[$item_name]['value_string'] . ' (<small>'.$row_data[$item_name]['value'].'</small>)' : '') . '</td>';
                    } elseif ($row_data[$item_name]['type'] == 'structure') {
                        $rs .= '<td>' . $row_data[$item_name]['value_string'] . '</td>';
                    } elseif ($row_data[$item_name]['type'] == 'date') {
                        $rs .= '<td  >' . $row_data[$item_name]['value_string'] . '</td>';
                    } elseif ($row_data[$item_name]['type'] == 'select_box') {
                        if (isset($row_data[$item_name]['parameters']) && isset($row_data[$item_name]['parameters']['multiselect']) && $row_data[$item_name]['parameters']['multiselect'] == 1) {
                            $rs .= '<td>' . (!empty($row_data[$item_name]['value_variants_array']) ? implode('<br>', $row_data[$item_name]['value_variants_array']) : '') . '</td>';
                        } else {
                            $rs .= '<td>' . $row_data[$item_name]['select_data'][$row_data[$item_name]['value']] . '</td>';
                        }
                    } elseif ($row_data[$item_name]['type'] == 'checkbox') {
                        $rs .= '<td>' . ($row_data[$item_name]['value'] == 1 ? '<img src="' . SITEBILL_MAIN_URL . '/apps/admin/admin/template/img/radio_yes.png">' : '<img src="' . SITEBILL_MAIN_URL . '/apps/admin/admin/template/img/radio_no.png">') . '</td>';
                    } else {
                        if (is_array($row_data[$item_name]['value'])) {
                            $rs .= '<td>' . implode(';', $row_data[$item_name]['value']) . '</td>';
                        } else {
                            $rs .= '<td>' . $row_data[$item_name]['value'] . '</td>';
                        }
                    }
                }
                if (count($this->grid_controls) > 0) {
                    $rs .= '<td nowrap class="' . $this->css('td_controls') . '">';

                    foreach ($this->grid_controls as $control_id => $control_name) {
                        if ($control_name == 'view') {
                            if (!empty($control_params['view'])) {
                                $control_params_edit_string = $control_params['view'];
                            }
                            $rs .= ' <a href="?action=' . $this->grid_object->action . '&do=view&' . $this->grid_object->primary_key . '=' . $primary_key_value . $control_params_edit_string . '" class="' . $this->css('btn_view') . '"><i class="' . $this->css('icon_view') . '"></i></a> ';
                        }
                        if ($control_name == 'edit') {
                            if (!empty($control_params['edit'])) {
                                $control_params_edit_string = $control_params['edit'];
                            }
                            $rs .= ' <a href="?action=' . $this->grid_object->action . '&do=edit&' . $this->grid_object->primary_key . '=' . $primary_key_value . $control_params_edit_string . '" class="' . $this->css('btn_edit') . '"><i class="' . $this->css('icon_edit') . '"></i></a> ';
                        }
                        if ($control_name == 'delete') {
                            if (!empty($control_params['delete'])) {
                                $control_params_delete_string = $control_params['delete'];
                            }

                            $rs .= ' <a href="?action=' . $this->grid_object->action . '&do=delete&' . $this->grid_object->primary_key . '=' . $primary_key_value . $control_params_delete_string . '" onclick="if ( confirm(\'' . _e('Действительно хотите удалить запись?') . '\') ) {return true;} else {return false;}" class="' . $this->css('btn_delete') . '"><i class="' . $this->css('icon_delete') . '"></i></a> ';
                        }
                        if ($control_name == 'active_toggle') {
                            $active_val = isset($item_array['active']) ? intval($item_array['active']) : 1;
                            $btn_class  = $active_val ? $this->css('btn_active_on') : $this->css('btn_active_off');
                            $title_text = $active_val ? _e('выключить') : _e('включить');
                            $rs .= ' <a title="' . $title_text . '" data-id="' . $primary_key_value . '" data-model="' . $this->grid_object->table_name . '" data-active="' . $active_val . '" data-class-on="' . $this->css('btn_active_on') . '" data-class-off="' . $this->css('btn_active_off') . '" data-inactive-tr="' . $this->css('tr_inactive') . '" class="active_toggle_grid ' . $btn_class . '"><i class="' . $this->css('icon_active') . '"></i></a> ';
                        }
                        if (is_array($control_name)) {
                            $rs .= ' <a href="?action=' . $this->grid_object->action . '&do=' . $control_name['name'] . '&' . $this->grid_object->primary_key . '=' . $primary_key_value . $control_params_oth_string . '" class="' . ($control_name['btnclass'] != '' ? $control_name['btnclass'] : $this->css('btn_warning')) . '"><i class="' . ($control_name['btnicon'] != '' ? $control_name['btnicon'] : $this->css('icon_tasks')) . '"></i>' . ($control_name['btntext'] != '' ? ' ' . $control_name['btntext'] : '') . '</a> ';
                        }
                    }
                    $rs .= '</td>';
                }

                $rs .= '</tr>';
            }
            if (!$disable_mass_delete) {
                $rs .= '<tr><td colspan="' . (count($this->grid_controls) + $grid_counter) . '"><button alt="' . $this->grid_object->action . '" class="delete_checked ' . $this->css('btn_danger') . '"><i class="' . $this->css('icon_delete') . '"></i> ' . Multilanguage::_('L_DELETE_CHECKED') . '</button></td></tr>';
            }
            $rs .= '<tr><td colspan="' . (count($this->grid_controls) + $grid_counter) . '" class="' . $this->css('pager') . '"><div align="center">' . $this->getPager() . '</div></td></tr>';

            $rs .= '</tbody>';

            $rs .= '</table></div>';
            $rs .= $this->get_active_toggle_script();
        } else {
            $rs .= '<br><br>' . _e('Записей не найдено');
        }
        return $rs;
    }
}
