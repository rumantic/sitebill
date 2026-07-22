<?php

trait StructureVehicleTrait
{
    /**
     * Load mark structure
     * @param void
     * @return array
     */
    function load_mark_structure()
    {
        $query = "SELECT * FROM " . DB_PREFIX . "_mark order by `name` ";
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ret['mark'][$ar['mark_id']] = $ar;
                $ret['childs'][$ar['parent_id']][] = $ar['mark_id'];
            }
        }
        return $ret;
    }

    /**
     * Load coachwork structure
     * @param void
     * @return array
     */
    function load_coachwork_structure()
    {
        $ret = array();
        $query = "SELECT * FROM " . DB_PREFIX . "_coachwork order by `name` ";
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ret['coachwork'][$ar['coachwork_id']] = $ar;
                $ret['childs'][$ar['parent_id']][] = $ar['coachwork_id'];
            }
        }
        return $ret;
    }

    /**
     * Load model structure
     * @param void
     * @return array
     */
    function load_model_structure()
    {
        $query = "SELECT * FROM " . DB_PREFIX . "_model order by `name` ";
        //echo $query;
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ret['model'][$ar['model_id']] = $ar;
                $ret['childs'][$ar['mark_id']][] = $ar['model_id'];
            }
        }
        return $ret;
    }

    /**
     * Load modification structure
     * @param void
     * @return array
     */
    function load_modification_structure()
    {
        $query = "SELECT * FROM " . DB_PREFIX . "_modification order by `name` ";
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ret['modification'][$ar['modification_id']] = $ar;
                $ret['childs'][$ar['model_id']][] = $ar['modification_id'];
            }
        }
        return $ret;
    }

    /**
     * Get mark select box
     * @param int $current_mark_id mark ID
     * @param mixed $ajax_function ajax function
     * @return string
     */
    function getMarkSelectBox($current_mark_id, $ajax_function = false)
    {

        $category_structure = $this->loadCategoryStructure();
        $mark_structure = $this->load_mark_structure();

        $level = 1;
        $rs = '';
        $rs .= '<div id="mark_id_div">';
        if ($ajax_function) {
            $rs .= '<select name="mark_id" id="mark_id" onchange="' . $ajax_function . '">';
        } else {
            $rs .= '<select name="mark_id" id="mark_id">';
        }
        $rs .= '<option value="0">..</option>';
        foreach ($category_structure['childs'][0] as $item_id => $categoryID) {
            if ($current_category_id == $categoryID) {
                $selected = " selected ";
            } else {
                $selected = "";
            }

            $rs .= '<option disabled>' . str_repeat(' . ', $level) . $category_structure['catalog'][$categoryID]['name'] . '</option>';
            $rs .= $this->get_mark_option_items($categoryID, $mark_structure, $level, $current_mark_id);
            $rs .= $this->getChildNodes($categoryID, $category_structure, $level + 1, $current_category_id);
        }
        $rs .= '</select>';
        $rs .= '</div>';
        return $rs;
    }

    /**
     * Get flat mark select box
     * @param int $categoryID category ID
     * @param int $current_mark_id selected mark_id
     * @return string
     */
    function get_flat_mark_select_box($categoryID, $current_mark_id)
    {
        $mark_structure = $this->load_mark_structure();
        $rs = '';
        $rs .= '<div id="mark_id_div">';
        $rs .= '<select name="mark_id" id="mark_id" onchange="update_model_list()">';
        $rs .= '<option value="0">' . Multilanguage::_('L_CHOOSE_MARK') . '</option>';
        if (is_array($mark_structure['childs'][$categoryID])) {
            foreach ($mark_structure['childs'][$categoryID] as $mark_id) {
                if ($current_mark_id == $mark_id) {
                    $selected = " selected ";
                } else {
                    $selected = "";
                }
                $rs .= '<option value="' . $mark_id . '" ' . $selected . '>' . $mark_structure['mark'][$mark_id]['name'] . '</option>';
            }
        }
        $rs .= '</select>';
        $rs .= '</div>';
        return $rs;
    }

    /**
     * Get flat coachwork select box
     * @param int $categoryID category ID
     * @param int $current_coachwork_id selected coachwork_id
     * @return string
     */
    function get_flat_coachwork_select_box($categoryID, $current_coachwork_id)
    {
        $coachwork_structure = $this->load_coachwork_structure();
        $rs = '';
        $rs .= '<div id="coachwork_id_div">';
        $rs .= '<select name="coachwork_id" id="coachwork_id">';
        $rs .= '<option value="0">' . Multilanguage::_('L_CHOOSE_BODYTYPE') . '</option>';
        if (is_array($coachwork_structure['childs'][$categoryID])) {
            foreach ($coachwork_structure['childs'][$categoryID] as $coachwork_id) {
                if ($current_coachwork_id == $coachwork_id) {
                    $selected = " selected ";
                } else {
                    $selected = "";
                }
                $rs .= '<option value="' . $coachwork_id . '" ' . $selected . '>' . $coachwork_structure['coachwork'][$coachwork_id]['name'] . '</option>';
            }
        }
        $rs .= '</select>';
        $rs .= '</div>';
        return $rs;
    }

    /**
     * Get flat model select box
     * @param int $mark_id mark ID
     * @param int $current_model_id selected model_id
     * @return string
     */
    function get_flat_model_select_box($mark_id, $current_model_id)
    {
        $model_structure = $this->load_model_structure();
        $rs = '';
        $rs .= '<div id="model_id_div">';
        $rs .= '<select name="model_id" id="model_id" onchange="update_modification_list()">';
        $rs .= '<option value="0">' . Multilanguage::_('L_CHOOSE_MODEL') . '</option>';
        if (is_array($model_structure['childs'][$mark_id])) {
            foreach ($model_structure['childs'][$mark_id] as $model_id) {
                if ($current_model_id == $model_id) {
                    $selected = " selected ";
                } else {
                    $selected = "";
                }
                $rs .= '<option value="' . $model_id . '" ' . $selected . '>' . $model_structure['model'][$model_id]['name'] . '</option>';
                //$rs .= '<option value="'.$model_id.'" '.$selected.'>'.str_repeat(' _ ', $level+1).$model_id.'</option>';
                //$rs .= $this->get_model_option_items( $model_structure, $current_model_id );
            }
        }
        $rs .= '</select>';
        $rs .= '</div>';
        return $rs;
    }

    /**
     * Get flat modification select box
     * @param int $model_id model ID
     * @param int $current_modification_id selected modification_id
     * @return string
     */
    function get_flat_modification_select_box($model_id, $current_modification_id)
    {
        $modification_structure = $this->load_modification_structure();
        $rs = '';
        $rs .= '<div id="modification_id_div">';
        $rs .= '<select name="modification_id" id="modification_id">';
        $rs .= '<option value="0">' . Multilanguage::_('L_CHOOSE_MODIFICATION') . '</option>';
        if (is_array($modification_structure['childs'][$model_id])) {
            foreach ($modification_structure['childs'][$model_id] as $modification_id) {
                if ($current_modification_id == $modification_id) {
                    $selected = " selected ";
                } else {
                    $selected = "";
                }
                $rs .= '<option value="' . $modification_id . '" ' . $selected . '>' . $modification_structure['modification'][$modification_id]['name'] . '</option>';
                //$rs .= '<option value="'.$model_id.'" '.$selected.'>'.str_repeat(' _ ', $level+1).$model_id.'</option>';
                //$rs .= $this->get_model_option_items( $model_structure, $current_model_id );
            }
        }
        $rs .= '</select>';
        $rs .= '</div>';
        return $rs;
    }

    /**
     * Get mark select box
     * @param int $current_mark_id mark ID
     * @return string
     */
    function getModelSelectBox($current_mark_id)
    {
        //echo '$current_category_id = '.$current_category_id;
        $category_structure = $this->loadCategoryStructure();
        $mark_structure = $this->load_mark_structure();
        $model_structure = $this->load_model_structure();
        //echo '<pre>';
        //print_r($model_structure);
        $level = 1;
        $rs = '';
        $rs .= '<div id="model_id_div">';
        $rs .= '<select name="model_id">';
        $rs .= '<option value="0">..</option>';
        foreach ($category_structure['childs'][0] as $item_id => $categoryID) {
            //echo $categoryID.'<br>';
            //echo 'items = '.$items.'<br>';
            if ($current_category_id == $categoryID) {
                $selected = " selected ";
            } else {
                $selected = "";
            }

            $rs .= '<option disabled>' . str_repeat(' . ', $level) . $category_structure['catalog'][$categoryID]['name'] . '</option>';
            $rs .= $this->get_mark_and_model_option_items($categoryID, $mark_structure, $level, $current_mark_id, $model_structure);
            $rs .= $this->getChildNodes($categoryID, $category_structure, $level + 1, $current_category_id);
        }
        $rs .= '</select>';
        $rs .= '</div>';
        return $rs;
    }

    /**
     * Get mark option items
     * @param int $categoryID category ID
     * @param array $mark_structure mark structure
     * @param int $level
     * @param int $current_mark_id selected mark_id
     * @return string
     */
    function get_mark_option_items($categoryID, $mark_structure, $level, $current_mark_id)
    {
        $rs = '';
        if (is_array($mark_structure['childs'][$categoryID])) {
            foreach ($mark_structure['childs'][$categoryID] as $mark_id) {
                if ($current_mark_id == $mark_id) {
                    $selected = " selected ";
                } else {
                    $selected = "";
                }
                $rs .= '<option value="' . $mark_id . '" ' . $selected . '>' . str_repeat(' _ ', $level + 1) . $mark_structure['mark'][$mark_id]['name'] . '</option>';
            }
        }
        return $rs;
    }

    /**
     * Get mark and model option items
     * @param int $categoryID category ID
     * @param array $mark_structure mark structure
     * @param int $level
     * @param int $current_model_id selected model_id
     * @param array $model_structure
     * @return string
     */
    function get_mark_and_model_option_items($categoryID, $mark_structure, $level, $current_model_id, $model_structure)
    {
        $rs = '';
        if (is_array($mark_structure['childs'][$categoryID])) {
            foreach ($mark_structure['childs'][$categoryID] as $mark_id) {
                $rs .= '<option disabled>' . str_repeat(' _ ', $level + 1) . $mark_structure['mark'][$mark_id]['name'] . '</option>';
                $rs .= $this->get_model_option_items($model_structure, $current_model_id, $mark_id, $level);
            }
        }
        return $rs;
    }

    /**
     * Get model option items
     * @param array $model_structure model structure
     * @param int $current_model_id current model id
     * @param int $mark_id
     * @param int $level level
     * @return string
     */
    function get_model_option_items($model_structure, $current_model_id, $mark_id, $level)
    {
        $rs = '';
        if (is_array($model_structure['childs'][$mark_id])) {
            foreach ($model_structure['childs'][$mark_id] as $model_id) {
                if ($current_model_id == $model_id) {
                    $selected = " selected ";
                } else {
                    $selected = "";
                }
                $rs .= '<option value="' . $model_id . '" ' . $selected . '>' . str_repeat(' * ', $level + 2) . $model_structure['model'][$model_id]['name'] . '</option>';
            }
        }
        return $rs;
    }
}
