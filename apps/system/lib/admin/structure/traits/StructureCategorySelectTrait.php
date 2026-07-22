<?php

trait StructureCategorySelectTrait
{
    /**
     * Get category select box
     * @param int $current_category_id category ID
     * @param mixed $ajax_function
     * @return string
     */
    function getCategorySelectBox($current_category_id, $ajax_function = false)
    {
        $category_structure = $this->loadCategoryStructure();
        $level = 1;
        $rs = '';
        if ($ajax_function) {
            $rs .= '<select name="parent_id" id="parent_id" onchange="' . $ajax_function . '">';
        } else {
            $rs .= '<select name="parent_id">';
        }
        $rs .= '<option value="0">' . Multilanguage::_('L_CHOOSE_TOPIC') . '</option>';
        if (isset($category_structure['childs'][0]) && count($category_structure['childs'][0]) > 0) {
            foreach ($category_structure['childs'][0] as $item_id => $categoryID) {
                //echo $categoryID.'<br>';
                //echo 'items = '.$items.'<br>';
                if ($current_category_id == $categoryID) {
                    $selected = " selected ";
                } else {
                    $selected = "";
                }

                /* if($this->getConfigValue('disable_root_structure_select')==1){
                  $disabled=' disabled="disabled"';
                  }else{
                  $disabled='';
                  } */

                $rs .= '<option value="' . $categoryID . '" ' . $selected . $disabled . '>' . str_repeat(' . ', $level) . $category_structure['catalog'][$categoryID]['name'] . '</option>';
                $rs .= $this->getChildNodes($categoryID, $category_structure, $level + 1, $current_category_id);
            }
        }

        $rs .= '</select>';
        return $rs;
    }

    function getLevel($category_structure, $pid, $level, &$leveled, $selected_id, &$now_selected, &$find_more)
    {
        $fm = $find_more;
        if (isset($category_structure['childs'][$pid]) && count($category_structure['childs'][$pid]) > 0) {
            $i = 0;
            foreach ($category_structure['childs'][$pid] as $item_id => $categoryID) {
                $leveled[$level][$pid][$i] = array($category_structure['catalog'][$categoryID]['id'], $category_structure['catalog'][$categoryID]['name'], 0);
                //$ob=
                if ($categoryID == $selected_id) {
                    //$ob[2]=1;
                    $leveled[$level][$pid][$i][2] = 1;
                    $fm = false;
                    $now_selected = true;
                }

                $this->getLevel($category_structure, $categoryID, $level + 1, $leveled, $selected_id, $now_selected, $fm);
                if ($fm && $now_selected) {
                    //$ob[2]=1;
                    $leveled[$level][$pid][$i][2] = 1;
                    $fm = false;
                }

                $i++;
            }
        }
    }

    function getCategorySelectBoxLeveled($name, $selected, $options = array(), $model_item = array())
    {
        $category_structure = $this->loadCategoryStructure($this->getConfigValue('use_topic_publish_status'));
        //$selected=intval($this->getRequestValue('topic_id'));
        $now_selected = false;
        $find_more = true;
        $leveled = array();
        $pid = 0;
        $level = 1;

        if (!defined('ADMIN_MODE')) {
            $bootstrap_version = trim($this->getConfigValue('bootstrap_version'));
            if ($bootstrap_version == '3') {
                $classes = 'form-control';
            } elseif ($bootstrap_version == '4') {
                $classes = 'mdb-select';
            } elseif ($bootstrap_version == '4md') {
                $classes = 'mdb-select';
            } else {
                $classes = '';
            }
        } else {
            $classes = '';
        }

        $this->getLevel($category_structure, $pid, $level, $leveled, $selected, $now_selected, $find_more);
        $rt = '';
        $rt .= '<div class="leveled">';
        $rt .= '<input type="hidden" name="' . $name . '" id="' . $name . '" value="' . $selected . '">';
        foreach ($leveled as $lev => $it) {
            $active = 0;
            $rt .= '<div class="level" data-level="' . $lev . '">';
            foreach ($it as $pid => $pvals) {
                $rt .= '<div data-id="' . $pid . '" class="levelitem levelitem_' . $pid . '" style="display: none;">';
                if ($lev > 1) {
                    $rt .= _e('Подтип недвижимости') . ' ';
                    if (isset($model_item) and $model_item['required'] == 'on') {
                        $rt .= '<span style="color: red;">*</span> ';
                    }
                }
                $rt .= '<select' . ($classes != '' ? ' class="' . $classes . '"' : '') . '>';

                $tname = '';
                if ($lev == 1) {
                    $tname = (isset($options['zerotitle']) && $options['zerotitle'] != '' ? $options['zerotitle'] : '--');
                } else {
                    $tname = (isset($options['nonzerotitle']) && $options['nonzerotitle'] != '' ? $options['nonzerotitle'] : '--');
                }

                $rt .= '<option value="0">' . $tname . '</option>';
                foreach ($pvals as $pval) {
                    $rt .= '<option value="' . $pval[0] . '"' . ($pval[2] == 1 ? ' selected="selected"' : '') . '>' . $pval[1] . '</option>';
                }
                $rt .= '</select>';
                $rt .= '</div>';
            }

            $rt .= '</div>';
        }
        $rt .= '</div>';
        return $rt;
    }

    /**
     * Get category select box
     * @param string $name name
     * @param int $current_category_id category ID
     * @param mixed $ajax_function
     * @return string
     */
    function getCategorySelectBoxWithName($name, $current_category_id, $ajax_function = false, $parameters = array(), $zero_title = '')
    {
        //echo '$current_category_id = '.$current_category_id;
        $core_level_symbol = $this->getConfigValue('core_level_symbol');
        $core_level_symbol = str_replace('#', ' ', $core_level_symbol);

        if (!defined('ADMIN_MODE')) {

            if (isset($parameters['classes'])) {
                $classes = $parameters['classes'];
            } else {
                $bootstrap_version = trim($this->getConfigValue('bootstrap_version'));
                if ($bootstrap_version == '3') {
                    $classes = 'form-control';
                } elseif ($bootstrap_version == '4') {
                    $classes = 'mdb-select';
                } elseif ($bootstrap_version == '4md') {
                    $classes = 'mdb-select';
                } else {
                    $classes = '';
                }
            }


        } else {
            $classes = '';
        }

        //$start=68;
        $start = 0;

        if (isset($parameters['ignore_published_status']) && $parameters['ignore_published_status']) {
            $category_structure = $this->loadCategoryStructure();
        } else {
            $category_structure = $this->loadCategoryStructure($this->getConfigValue('use_topic_publish_status'));
        }
        if (isset($parameters['only_top_level']) && $parameters['only_top_level']) {
            $tmp = $category_structure['childs'][0];
            unset($category_structure['childs']);
            $category_structure['childs'][0] = $tmp;
        }

        if (isset($parameters['enabled_ids']) && '' != trim($parameters['enabled_ids'])) {
            preg_match_all('/(\d+)/', $parameters['enabled_ids'], $matches);
            if (isset($matches[1]) && is_array($matches[1]) && !empty($matches[1])) {
                foreach ($category_structure['childs'][0] as $k => $v) {
                    if (!in_array($v, $matches[1])) {
                        unset($category_structure['childs'][0][$k]);
                    }
                }
            }
        }
        //echo '<pre>';
        //print_r($category_structure);

        $level = 1;
        $rs = '';
        $multiple = false;
        if (is_array($current_category_id)) {
            $multiple = true;
        }
        if ($ajax_function) {
            $rs .= '<select name="' . $name . ($multiple ? '[]' : '') . '" id="' . $name . '"' . ($classes != '' ? ' class="' . $classes . '"' : '') . ' onchange="' . $ajax_function . '"' . ($multiple ? ' multiple="multiple"' : '') . '>';
        } else {
            $rs .= '<select name="' . $name . ($multiple ? '[]' : '') . '"' . ($multiple ? ' multiple="multiple"' : '') . ' id="' . $name . '"' . ($classes != '' ? ' class="' . $classes . '"' : '') . '>';
        }

        if ($zero_title == '') {
            $title_default = Multilanguage::_('L_CHOOSE_TOPIC');
        } else {
            $title_default = $zero_title;
        }

        if (!$multiple) {
            $rs .= '<option class="rootlevel rootlevel_0" value="' . ($start != 0 ? $start : '0') . '">' . $title_default . '</option>';
        }
        if (isset($category_structure['childs'][$start]) && count($category_structure['childs'][$start]) > 0) {
            foreach ($category_structure['childs'][$start] as $item_id => $categoryID) {
                $superparent = $categoryID;
                if ($multiple) {
                    if (in_array($categoryID, $current_category_id)) {
                        $selected = " selected ";
                    } else {
                        $selected = "";
                    }
                } else {
                    if ($current_category_id == $categoryID) {
                        $selected = " selected ";
                    } else {
                        $selected = "";
                    }
                }

                if (($this->getConfigValue('disable_root_structure_select') == 1 || $this->getConfigValue('disable_root_structure_select') == 3) && isset($_SESSION['allow_disable_root_structure_select']) && $_SESSION['allow_disable_root_structure_select'] === true) {
                    $disabled = ' disabled="disabled" style="background-color:#eee;"';
                } elseif ($this->getConfigValue('disable_root_structure_select') == 2 && isset($category_structure['childs'][$categoryID]) && is_array($category_structure['childs'][$categoryID]) && isset($_SESSION['allow_disable_root_structure_select']) && $_SESSION['allow_disable_root_structure_select'] === true) {
                    $disabled = ' disabled="disabled" style="background-color:#eee;"';
                } else {
                    $disabled = '';
                }


                if (function_exists('BeforPrintOptionName_getCategorySelectBoxWithName')) {
                    $option_title = BeforPrintOptionName_getCategorySelectBoxWithName($category_structure['catalog'][$categoryID]['name']);
                } else {
                    $option_title = $category_structure['catalog'][$categoryID]['name'];
                }
                $rs .= '<option class="rootlevel rootlevel_' . $level . '" data-superparent="' . $superparent . '" value="' . $categoryID . '" ' . $selected . $disabled . '>' . str_repeat($core_level_symbol, $level) . $option_title . '</option>';
                $rs .= $this->getChildNodes($categoryID, $category_structure, $level + 1, $current_category_id, $superparent);
            }
        }

        $_SESSION['allow_disable_root_structure_select'] = false;
        $rs .= '</select>';
        return $rs;
    }

    function getCategoryCheckboxes($name, $current_category_id, $ajax_function = false)
    {
        $category_structure = $this->loadCategoryStructure();
        $rs = '';
        if (isset($category_structure['childs'][0]) && count($category_structure['childs'][0]) > 0) {
            $rs .= '<style></style>';
            $rs .= '<div class="checkbox_collection">';
            $rs .= '<a href="#" class="checkbox_collection_decheck">Очистить все</a>';
            foreach ($category_structure['childs'][0] as $item_id => $categoryID) {

                $rs .= '<div class="ait_bc">';
                $rs .= '<div class="ait_bc_h"><input name="' . $name . '[]" value="' . $category_structure['catalog'][$categoryID]['id'] . '" type="checkbox"' . (in_array($categoryID, $current_category_id) ? ' checked="checked"' : '') . ' /> ' . $category_structure['catalog'][$categoryID]['name'] . '</div>';
                $rs .= $this->getChildNodesCheckboxes($name, $categoryID, $category_structure, $current_category_id);
                $rs .= '</div>';
            }
            $rs .= '</div>';
        }
        return $rs;
    }

    function getChildNodesCheckboxes($name, $categoryID, $category_structure, $current_category_id)
    {
        $rs = '';
        if (isset($category_structure['childs'][$categoryID]) && count($category_structure['childs'][$categoryID]) > 0) {
            foreach ($category_structure['childs'][$categoryID] as $child_id) {
                $rs .= '<div class="ait_bc">';
                $rs .= '<div class="ait_bc_h"><input name="' . $name . '[]" value="' . $category_structure['catalog'][$child_id]['id'] . '" type="checkbox"' . (in_array($child_id, $current_category_id) ? ' checked="checked"' : '') . ' /> ' . $category_structure['catalog'][$child_id]['name'] . '</div>';

                if (isset($category_structure['childs'][$child_id])) {
                    if (count($category_structure['childs'][$child_id]) > 0) {
                        $rs .= $this->getChildNodesCheckboxes($name, $child_id, $category_structure, $current_category_id);
                    }
                }
                $rs .= '</div>';
            }
        }
        return $rs;
    }

    function getShopCategorySelectBoxWithName($name, $current_category_id, $ajax_function = false)
    {
        //echo '$current_category_id = '.$current_category_id;
        $category_structure = $this->loadShopCategoryStructure();
        //echo '<pre>';
        //print_r($category_structure);
        $level = 1;
        $rs = '';
        if ($ajax_function) {
            $rs .= '<select name="' . $name . '" id="' . $name . '" onchange="' . $ajax_function . '">';
        } else {
            $rs .= '<select name="' . $name . '">';
        }
        $rs .= '<option value="0">' . Multilanguage::_('L_CHOOSE_TOPIC') . '</option>';
        foreach ($category_structure['childs'][0] as $item_id => $categoryID) {
            //echo $categoryID.'<br>';
            //echo 'items = '.$items.'<br>';
            if ($current_category_id == $categoryID) {
                $selected = " selected ";
            } else {
                $selected = "";
            }

            $rs .= '<option value="' . $categoryID . '" ' . $selected . '>' . str_repeat(' . ', $level) . $category_structure['catalog'][$categoryID]['category_name'] . '</option>';
            $rs .= $this->getShopChildNodes($categoryID, $category_structure, $level + 1, $current_category_id);
        }
        $rs .= '</select>';
        return $rs;
    }

    /**
     * Get child nodes
     * @param $categoryID
     * @param $category_structure
     * @param $level
     * @param $current_category_id
     */
    function getChildNodes($categoryID, $category_structure, $level, $current_category_id, $superparent = 0)
    {
        $level_symbol = $this->getConfigValue('level_symbol');
        $level_symbol = str_replace('#', ' ', $level_symbol);

        $core_level_symbol = $this->getConfigValue('core_level_symbol');
        $core_level_symbol = str_replace('#', ' ', $core_level_symbol);

        $rs = '';
        if (!isset($category_structure['childs'][$categoryID]) || !is_array($category_structure['childs'][$categoryID])) {
            return '';
        }
        $multiple = false;
        if (is_array($current_category_id)) {
            $multiple = true;
        }
        foreach ($category_structure['childs'][$categoryID] as $child_id) {
            if ($multiple) {
                if (in_array($child_id, $current_category_id)) {
                    $selected = " selected ";
                } else {
                    $selected = "";
                }
            } else {
                if ($current_category_id == $child_id) {
                    $selected = " selected ";
                } else {
                    $selected = "";
                }
            }

            if (($this->getConfigValue('disable_root_structure_select') == 2 || $this->getConfigValue('disable_root_structure_select') == 3) && isset($category_structure['childs'][$child_id]) && is_array($category_structure['childs'][$child_id]) && $_SESSION['allow_disable_root_structure_select'] === true) {
                $disabled = ' disabled="disabled" style="background-color:#eee;"';
            } else {
                $disabled = '';
            }

            if ($core_level_symbol == '') {
                $offset_level = $level - 1;
            } else {
                $offset_level = $level;
            }
            $rs .= '<option class="rootlevel rootlevel_' . $level . '" data-superparent="' . $superparent . '" data-parent="' . $categoryID . '" data-level="' . $level . '" value="' . $child_id . '" data-value="' . $category_structure['catalog'][$child_id]['name'] . '" ' . $selected . $disabled . '>' . str_repeat($level_symbol, $offset_level) . $category_structure['catalog'][$child_id]['name'] . '</option>';
            if (isset($category_structure['childs'][$child_id])) {
                if (count($category_structure['childs'][$child_id]) > 0) {
                    $rs .= $this->getChildNodes($child_id, $category_structure, $level + 1, $current_category_id, $superparent);
                }
            }
        }
        return $rs;


        ////////////////////////////////////////////
        if (!is_array($category_structure['childs'][$categoryID])) {
            return '';
        }
        foreach ($category_structure['childs'][$categoryID] as $child_id) {
            if ($current_category_id == $child_id) {
                $selected = " selected ";
            } else {
                $selected = "";
            }
            //print_r($category_structure['catalog'][$child_id]);
            $rs .= '<option value="' . $child_id . '" ' . $selected . '>' . str_repeat(' . ', $level) . $category_structure['catalog'][$child_id]['name'] . '</option>';
            //print_r($category_structure['childs'][$child_id]);
            if (count($category_structure['childs'][$child_id]) > 0) {
                $rs .= $this->getChildNodes($child_id, $category_structure, $level + 1, $current_category_id);
            }
        }
        return $rs;
    }

    function getShopChildNodes($categoryID, $category_structure, $level, $current_category_id)
    {
        if (!is_array($category_structure['childs'][$categoryID])) {
            return '';
        }
        foreach ($category_structure['childs'][$categoryID] as $child_id) {
            if ($current_category_id == $child_id) {
                $selected = " selected ";
            } else {
                $selected = "";
            }
            $rs .= '<option value="' . $child_id . '" ' . $selected . '>' . str_repeat(' . ', $level) . $category_structure['catalog'][$child_id]['category_name'] . '</option>';

            if (count($category_structure['childs'][$child_id]) > 0) {
                $rs .= $this->getShopChildNodes($child_id, $category_structure, $level + 1, $current_category_id);
            }
        }
        return $rs;


        ////////////////////////////////////////////
        if (!is_array($category_structure['childs'][$categoryID])) {
            return '';
        }
        foreach ($category_structure['childs'][$categoryID] as $child_id) {
            if ($current_category_id == $child_id) {
                $selected = " selected ";
            } else {
                $selected = "";
            }
            //print_r($category_structure['catalog'][$child_id]);
            $rs .= '<option value="' . $child_id . '" ' . $selected . '>' . str_repeat(' . ', $level) . $category_structure['catalog'][$child_id]['category_name'] . '</option>';
            //print_r($category_structure['childs'][$child_id]);
            if (count($category_structure['childs'][$child_id]) > 0) {
                $rs .= $this->getShopChildNodes($child_id, $category_structure, $level + 1, $current_category_id);
            }
        }
        return $rs;
    }
}
