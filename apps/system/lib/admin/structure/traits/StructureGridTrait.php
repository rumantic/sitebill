<?php

trait StructureGridTrait
{
    /**
     * Load data structure
     * @param int $user_id
     * @return array
     */
    function load_data_structure($user_id, $params = array(), $search_params = array())
    {
        return null;
        $where_array = array();

        if ($this->get_context() != NULL) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/grid.php');
            $common_grid = new Common_Grid($this->get_context());
            $common_grid->set_action('data');
            $common_grid->set_grid_table('data');

            $tagged_params = $common_grid->add_tags_params();
            $where_array = $common_grid->add_tagged_parms_to_where($where_array, $tagged_params, 'data');
        }

        if ($user_id == 0) {
            if (isset($params['active']) && $params['active'] == 1) {
                $where_array[] = DB_PREFIX . '_data.`active`=1';
            } elseif (isset($params['active']) && $params['active'] == 'notactive') {
                $where_array[] = DB_PREFIX . '_data.`active`=0';
            }

            if (isset($params['hot']) && $params['hot'] == 1) {
                $where_array[] = DB_PREFIX . '_data.`hot`=1';
            }

            if (count($search_params) > 0) {
                foreach ($search_params as $v) {
                    $where_array[] = DB_PREFIX . '_data.`' . $v . '`';
                }
            }
            /*
              if(isset($params['realty_type_id'])){
              $where_array[] = 're_data.realty_type_id='.(int)$params['realty_type_id'];
              }
             */
            $where = '';
            if (count($where_array) > 0) {
                $where = ' WHERE ' . implode(' AND ', $where_array);
            }
            //$query = "SELECT id, topic_id FROM ".DB_PREFIX."_data ".$where;
            $query = "SELECT COUNT(id) as total, topic_id FROM " . DB_PREFIX . "_data " . $where . " GROUP BY topic_id";
        } else {
            $where = ' 1 = 1 ';
            if (count($where_array) > 0) {
                $where = implode(' AND ', $where_array);
            }

            // $query = "SELECT id, topic_id FROM ".DB_PREFIX."_data  where user_id = $user_id";
            $query = "SELECT COUNT(id) as total, topic_id FROM " . DB_PREFIX . "_data  where " . $where . " AND user_id = $user_id GROUP BY topic_id";
        }
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {

                $ret['data'][$user_id][$ar['topic_id']] = $ar['total'];
            }
        }

        return $ret;
    }

    /**
     * Load data structure for shop
     * @param int $user_id
     * @return array
     */
    function load_data_structure_shop($user_id, $params = array())
    {
        $where_array = array();
        //echo '<pre>';
        //print_r($params);
        //echo '</pre>';
        $language_id = ((int)$this->getRequestValue('language_id') == 0 ? 0 : (int)$this->getRequestValue('language_id'));


        if ($user_id == 0) {

            //$enable_publication_limit=$this->getConfigValue('apps.shop.user_limit_enable');

            if ($params['enable_publication_limit'] == 1) {
                $where_array[] = '((' . DB_PREFIX . '_shop_product.product_add_date+' . DB_PREFIX . '_user.publication_limit*24*3600)>' . time() . ')';
            }

            if ($params['active'] == 1) {
                $where_array[] = DB_PREFIX . '_shop_product.active=1';
            } elseif ($params['active'] == 'notactive') {
                $where_array[] = DB_PREFIX . '_shop_product.active=0';
            }

            if (isset($params['city_id'])) {
                $where_array[] = '(' . DB_PREFIX . '_shop_product.city_id=' . $params['city_id'] . ')';
            }

            $where_array[] = DB_PREFIX . '_shop_product.language_id=' . $language_id;

            if (count($where_array) > 0) {
                $where = ' WHERE ' . implode(' AND ', $where_array);
            }
            //$query = "SELECT product_id, category_id FROM ".DB_PREFIX."_shop_product ".$where;
            $query = "SELECT COUNT(" . DB_PREFIX . "_shop_product.product_id) as total, " . DB_PREFIX . "_shop_product.category_id FROM " . DB_PREFIX . "_shop_product LEFT JOIN " . DB_PREFIX . "_user ON " . DB_PREFIX . "_shop_product.user_id=" . DB_PREFIX . "_user.user_id " . $where . " GROUP BY " . DB_PREFIX . "_shop_product.category_id";
        } else {

            if ($params['active'] == 1) {
                $where_array[] = DB_PREFIX . '_shop_product.active=1';
            } elseif ($params['active'] == 'notactive') {
                $where_array[] = DB_PREFIX . '_shop_product.active=0';
            } elseif ($params['archived'] == 1) {
                $where_array[] = '((' . DB_PREFIX . '_shop_product.product_add_date+' . DB_PREFIX . '_user.publication_limit*24*3600)<' . time() . ')';
            } elseif ($params['archived'] == 'notarchived') {
                $where_array[] = '((' . DB_PREFIX . '_shop_product.product_add_date+' . DB_PREFIX . '_user.publication_limit*24*3600)>' . time() . ')';
            }

            $where_array[] = DB_PREFIX . '_shop_product.user_id = ' . $user_id;

            $where = ' WHERE ' . implode(' AND ', $where_array);
            //$query = "SELECT product_id, category_id FROM ".DB_PREFIX."_shop_product  where user_id = $user_id";
            $query = "SELECT COUNT(product_id) as total, category_id FROM " . DB_PREFIX . "_shop_product LEFT JOIN " . DB_PREFIX . "_user ON " . DB_PREFIX . "_shop_product.user_id=" . DB_PREFIX . "_user.user_id   " . $where . " GROUP BY category_id";
        }
        //echo $query.'<br>';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {

                $ret['data'][$user_id][$ar['category_id']] = $ar['total'];
            }
        }
        return $ret;
    }

    /**
     * Load data structure for price
     * @param int $user_id
     * @return array
     */
    function load_data_structure_price($user_id, $params = array())
    {
        $where_array = array();
        $language_id = ((int)$this->getRequestValue('language_id') == 0 ? 0 : (int)$this->getRequestValue('language_id'));

        if (count($where_array) > 0) {
            $where = ' WHERE ' . implode(' AND ', $where_array);
        }
        $query = "SELECT COUNT(" . DB_PREFIX . "_price.price_id) as total, " . DB_PREFIX . "_price.category_id FROM " . DB_PREFIX . "_price GROUP BY " . DB_PREFIX . "_price.category_id";
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {

                $ret['data'][$user_id][$ar['category_id']] = $ar['total'];
            }
        }
        return $ret;
    }

    function getCategoryTreeModern($current_category_id)
    {
        $this->template->assign('structure_grid_allow_drag', 1);
        $this->template->assign('use_topic_publish_status', intval($this->getConfigValue('use_topic_publish_status')));
        return $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/system/template/structure_grid.tpl');
    }

    /**
     * Get category tree
     * @param int $current_category_id category ID
     * @return string
     */
    function getCategoryTree($current_category_id)
    {
        //echo '$current_category_id = '.$current_category_id;
        $category_structure = $this->loadCategoryStructure();
        $level = 0;
        $rs = '';
        $rs .= '<form method="post">';
        $rs .= '<table border="0"  class="table table-hover">';
        $rs .= '<tr>';
        $rs .= '<td class="row_title"></td>';
        $rs .= '<td class="row_title"></td>';
        $rs .= '<td class="row_title"><input type="submit" value="' . Multilanguage::_('RESORT_ITEMS', 'system') . '" name="submit" class="btn btn-info"/></td>';
        $rs .= '<td class="row_title"></td>';
        $rs .= '</tr>';
        $rs .= '<tr>';
        $rs .= '<td class="row_title">' . Multilanguage::_('L_TEXT_TITLE') . '</td>';
        $rs .= '<td class="row_title">' . Multilanguage::_('URL_NAME', 'system') . '</td>';
        $rs .= '<td class="row_title">' . Multilanguage::_('SORT_ORDER', 'system') . '</td>';
        $rs .= '<td class="row_title"></td>';
        $rs .= '</tr>';
        if (count($category_structure) > 0) {
            foreach ($category_structure['childs'][0] as $item_id => $catalog_id) {
                //echo $catalog_id.'<br>';
                $rs .= $this->get_row($catalog_id, $category_structure, $level, 'row1');
                $rs .= $this->getChildNodesRow($catalog_id, $category_structure, $level + 1, $current_category_id);
            }
        }
        $rs .= '<tr>';
        $rs .= '<input type="hidden" name="action" value="structure" />';
        $rs .= '<input type="hidden" name="do" value="reorder_topics" />';
        $rs .= '<td class="row_title"></td>';
        $rs .= '<td class="row_title"></td>';
        $rs .= '<td class="row_title"><input type="submit" value="' . Multilanguage::_('RESORT_ITEMS', 'system') . '" name="submit" class="btn btn-info"/></td>';
        $rs .= '<td class="row_title"></td>';

        $rs .= '</tr>';
        $rs .= '</table>';
        $rs .= '</form>';
        return $rs;
    }

    /**
     * Get category tree control
     * @param int $current_category_id category ID
     * @param int $user_id
     * @param boolean $control
     * @param array $params
     * @return string
     */
    function get_category_tree_control($current_category_id, $user_id, $control = false, $params = array(), $search_params = array())
    {
        // @todo: $user_id нужно добавить проверку на массив в этом значении и генерировать контрол в соответствии с массивом
        // user_id

        $category_structure = $this->loadCategoryStructure();
        $data_structure = $this->load_data_structure($user_id, $params, $search_params);
        //print_r($data_structure);
        if (is_array($category_structure['catalog']) && count($category_structure['catalog']) > 0) {
            foreach ($category_structure['catalog'] as $cat_point) {
                $ch = 0;
                $this->getChildsItemsCount($cat_point['id'], $category_structure['childs'], $data_structure['data'][$user_id], $ch);
                if (!isset($data_structure['data'][$user_id][$cat_point['id']])) {
                    $data_structure['data'][$user_id][$cat_point['id']] = 0;
                }
                $data_structure['data'][$user_id][$cat_point['id']] += $ch;
            }
        }
        unset($params['active']);
        unset($params['hot']);

        $level = 0;
        $rs = '';
        $rs .= '<table border="0" width="100%" class="table table-hover">';
        if (is_array($category_structure['childs'][0]) && count($category_structure['childs'][0]) > 0) {
            foreach ($category_structure['childs'][0] as $item_id => $catalog_id) {
                //echo $catalog_id.'<br>';
                $rs .= $this->get_row_control($catalog_id, $category_structure, $level, 'row1', $user_id, $control, $data_structure, $current_category_id, $params);
                $rs .= $this->get_child_nodes_row_control($catalog_id, $category_structure, $level + 1, $current_category_id, $user_id, $control, $data_structure, $params);
            }
        }
        $rs .= '</table>';
        return $rs;
    }

    /**
     * Get category tree control for shop
     * @param int $current_category_id category ID
     * @param int $user_id
     * @param boolean $control
     * @param array $params
     * @return string
     */
    function get_category_tree_control_shop($current_category_id, $user_id, $control = false, $params = array())
    {
        //print_r($params);
        //echo '$current_category_id = '.$current_category_id;
        $category_structure = $this->loadCategoryStructure();
        $data_structure = $this->load_data_structure_shop($user_id, $params);
        //echo '<pre>';
        //print_r($data_structure);
        //print_r($category_structure);

        foreach ($category_structure['catalog'] as $cat_point) {
            $ch = 0;
            $this->getChildsItemsCount($cat_point['id'], $category_structure['childs'], $data_structure['data'][$user_id], $ch);

            $data_structure['data'][$user_id][$cat_point['id']] += $ch;
        }


        $level = 0;
        $rs = '';
        $rs .= '<table border="0">';
        $rs .= '<tr>';
        $rs .= '<td class="row_title">' . Multilanguage::_('L_TEXT_TITLE') . '</td>';
        $rs .= '<td class="row_title"></td>';
        $rs .= '</tr>';
        foreach ($category_structure['childs'][0] as $item_id => $catalog_id) {
            //echo $catalog_id.'<br>';
            $rs .= $this->get_row_control($catalog_id, $category_structure, $level, 'row1', $user_id, $control, $data_structure, $current_category_id, $params);
            $rs .= $this->get_child_nodes_row_control($catalog_id, $category_structure, $level + 1, $current_category_id, $user_id, $control, $data_structure, $params);
        }
        $rs .= '</table>';
        return $rs;
    }

    /**
     * Get category tree control for price
     * @param int $current_category_id category ID
     * @param int $user_id
     * @param boolean $control
     * @param array $params
     * @return string
     */
    function get_category_tree_control_price($current_category_id, $user_id, $control = false, $params = array())
    {
        //print_r($params);
        //echo '$current_category_id = '.$current_category_id;
        $category_structure = $this->loadCategoryStructure();
        $data_structure = $this->load_data_structure_price($user_id, $params);
        //echo '<pre>';
        //print_r($data_structure);
        //print_r($category_structure);

        foreach ($category_structure['catalog'] as $cat_point) {
            $ch = 0;
            $this->getChildsItemsCount($cat_point['id'], $category_structure['childs'], $data_structure['data'][$user_id], $ch);

            $data_structure['data'][$user_id][$cat_point['id']] += $ch;
        }


        $level = 0;
        $rs = '';
        $rs .= '<table border="0">';
        $rs .= '<tr>';
        $rs .= '<td class="row_title">' . Multilanguage::_('L_TEXT_TITLE') . '</td>';
        $rs .= '<td class="row_title"></td>';
        $rs .= '</tr>';
        foreach ($category_structure['childs'][0] as $item_id => $catalog_id) {
            //echo $catalog_id.'<br>';
            $rs .= $this->get_row_control($catalog_id, $category_structure, $level, 'row1', $user_id, $control, $data_structure, $current_category_id, $params);
            $rs .= $this->get_child_nodes_row_control($catalog_id, $category_structure, $level + 1, $current_category_id, $user_id, $control, $data_structure, $params);
        }
        $rs .= '</table>';
        return $rs;
    }

    /**
     * Get row
     * @param int $categoryID
     * @param array $category_structure
     * @param int $level
     * @param string $row_class
     */
    function get_row($categoryID, $category_structure, $level, $row_class)
    {
        $rs = '';
        $rs .= '<tr>';
        $rs .= '<td class="' . $row_class . '">' . str_repeat('&nbsp;.&nbsp;', $level) . $category_structure['catalog'][$categoryID]['name'] . '</td>';
        if ($category_structure['catalog'][$categoryID]['url'] == '') {
            $rs .= '<td class="' . $row_class . '">' . 'topic' . $categoryID . '.html</td>';
        } else {
            $rs .= '<td class="' . $row_class . '">' . $category_structure['catalog'][$categoryID]['url'] . '</td>';
        }
        $edit_icon = ' <a href="?action=structure&do=edit&id=' . $categoryID . '" class="btn btn-info"><i class="icon-white icon-pencil"></i></a> ';
        $delete_icon = ' <a href="?action=structure&do=delete&id=' . $categoryID . '" onclick="if ( confirm(\'' . Multilanguage::_('L_MESSAGE_REALLY_WANT_DELETE') . '\') ) {return true;} else {return false;}" class="btn btn-danger"><i class="icon-white icon-remove"></i></a> ';

        $rs .= '<td class="' . $row_class . '"><input type="text" size="4" name="order[' . $categoryID . ']" value="' . $category_structure['catalog'][$categoryID]['order'] . '"/></td>';
        $rs .= '<td class="' . $row_class . '">' . $edit_icon . $delete_icon . '</td>';
        $rs .= '</tr>';

        return $rs;
    }

    /**
     * Get row control
     * @param int $categoryID
     * @param array $category_structure
     * @param int $level
     * @param string $row_class
     * @param int $user_id
     * @param boolean $control
     * @param array $data_structure
     * @param int $current_category_id
     * @param array $params
     * @return string
     */
    function get_row_control($categoryID, $category_structure, $level, $row_class, $user_id, $control = false, $data_structure, $current_category_id, $params = array())
    {
        //echo '<pre>';
        //print_r($params);
        //echo '</pre>';
        $rs = '';

        if (((int)$this->getConfigValue('hide_empty_catalog') != 0) and ((int)$data_structure['data'][$user_id][$categoryID] == 0)) {
            return '';
        }


        if (count($params) > 0) {
            $add_url = '&' . implode('&', $params);
        }

        //echo "add_url = ".$add_url;
        $rs .= '<tr>';
        if ($categoryID == $current_category_id) {
            $row_class = 'active';
        }
        $subclass = '';
        if ($category_structure['catalog'][$categoryID]['parent_id'] == 0) {
            $subclass = 'maincat';
        }
        $rs .= '<td class="' . $row_class . ' ' . $subclass . '"><a href="?topic_id=' . $categoryID . '' . $add_url . '">' . str_repeat('&nbsp;.&nbsp;', $level) . $category_structure['catalog'][$categoryID]['name'] . '</a> (' . (int)$data_structure['data'][$user_id][$categoryID] . ') <small>id:' . $categoryID . '</small></td>';

        if ($control) {
            $edit_icon = '<a href="?action=structure&do=edit&id=' . $categoryID . '"><img src="' . SITEBILL_MAIN_URL . '/img/edit.gif" border="0" width="16" height="16" alt="редактировать" title="редактировать"></a>';
            $delete_icon = '<a href="?action=structure&do=delete&id=' . $categoryID . '" onclick="if ( confirm(\'' . Multilanguage::_('L_MESSAGE_REALLY_WANT_DELETE') . '\') ) {return true;} else {return false;}"><img src="' . SITEBILL_MAIN_URL . '/img/delete.gif" border="0" width="16" height="16" alt="удалить" title="удалить"></a>';
        }


        if ($control) {
            $rs .= '<td class="' . $row_class . '">' . $edit_icon . $delete_icon . '</td>';
        }
        $rs .= '</tr>';


        return $rs;
    }

    /**
     * Get child nodes
     * @param $categoryID
     * @param $category_structure
     * @param $level
     * @param $current_category_id
     */
    function getChildNodesRow($categoryID, $category_structure, $level, $current_category_id)
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
            $this->j++;
            if (ceil($this->j / 2) > floor($this->j / 2)) {
                $row_class = "row1";
            } else {
                $this->j = 0;
                $row_class = "row2";
            }

            //print_r($category_structure['catalog'][$child_id]);
            $rs .= '<tr>';
            $rs .= '<td class="' . $row_class . '">' . str_repeat('&nbsp;.&nbsp;', $level) . $category_structure['catalog'][$child_id]['name'] . '</td>';

            if ($category_structure['catalog'][$child_id]['url'] == '') {
                $rs .= '<td class="' . $row_class . '">' . 'topic' . $category_structure['catalog'][$child_id]['id'] . '.html</td>';
            } else {
                $rs .= '<td class="' . $row_class . '">' . $category_structure['catalog'][$child_id]['url'] . '</td>';
            }


            $edit_icon = ' <a href="?action=structure&do=edit&id=' . $child_id . '" class="btn btn-info"><i class="icon-white icon-pencil"></i></a> ';
            $delete_icon = ' <a href="?action=structure&do=delete&id=' . $child_id . '" onclick="if ( confirm(\'' . Multilanguage::_('L_MESSAGE_REALLY_WANT_DELETE') . '\') ) {return true;} else {return false;}"  class="btn btn-danger"><i class="icon-white icon-remove"></i></a> ';


            $rs .= '<td class="' . $row_class . '"><input type="text" size="5" name="order[' . $child_id . ']" value="' . $category_structure['catalog'][$child_id]['order'] . '"/></td>';
            $rs .= '<td class="' . $row_class . '">' . $edit_icon . $delete_icon . '</td>';
            $rs .= '</tr>';
            //$rs .= '<option value="'.$child_id.'" '.$selected.'>'.str_repeat(' . ', $level).$category_structure['catalog'][$child_id]['name'].'</option>';
            //print_r($category_structure['childs'][$child_id]);
            if (count($category_structure['childs'][$child_id]) > 0) {
                $rs .= $this->getChildNodesRow($child_id, $category_structure, $level + 1, $current_category_id);
            }
        }
        return $rs;
    }

    /**
     * Get child nodes control
     * @param $categoryID
     * @param $category_structure
     * @param $level
     * @param $current_category_id
     */
    function get_child_nodes_row_control($categoryID, $category_structure, $level, $current_category_id, $user_id, $control = false, $data_structure, $params = array())
    {
        $rs = '';
        if (!isset($category_structure['childs'][$categoryID]) || !is_array($category_structure['childs'][$categoryID])) {
            return '';
        }


        if (count($params) > 0) {
            $add_url = '&' . implode('&', $params);
        }

        foreach ($category_structure['childs'][$categoryID] as $child_id) {


            if ((0 != $this->getConfigValue('hide_empty_catalog')) and (0 == $data_structure['data'][$user_id][$child_id])) {
                $rs .= '';
            } else {

                if ($current_category_id == $child_id) {
                    $selected = " selected ";
                } else {
                    $selected = "";
                }
                $this->j++;
                if (ceil($this->j / 2) > floor($this->j / 2)) {
                    $row_class = "row1";
                } else {
                    $this->j = 0;
                    $row_class = "row2";
                }

                //print_r($category_structure['catalog'][$child_id]);
                //print_r($data_structure['data'][$user_id]);
                //echo "category_id = $child_id, count = ".$data_structure['data'][$user_id][$child_id].'<br>';
                $rs .= '<tr>';

                if ($child_id == $current_category_id) {
                    $row_class = 'active';
                }
                $rs .= '<td class="' . $row_class . '"><a href="?topic_id=' . $child_id . '' . $add_url . '">' . str_repeat('&nbsp;.&nbsp;', $level) . $category_structure['catalog'][$child_id]['name'] . '</a> (' . (int)$data_structure['data'][$user_id][$child_id] . ')' . ' <small>id:' . $child_id . '</small></td>';
                if ($control) {
                    $edit_icon = '<a href="?action=structure&do=edit&id=' . $child_id . '"><img src="' . SITEBILL_MAIN_URL . '/apps/admin/admin/template/img/edit.png" border="0"  alt="редактировать" title="редактировать"></a>';
                    $delete_icon = '<a href="?action=structure&do=delete&id=' . $child_id . '" onclick="if ( confirm(\'' . Multilanguage::_('L_MESSAGE_REALLY_WANT_DELETE') . '\') ) {return true;} else {return false;}"><img src="' . SITEBILL_MAIN_URL . '/apps/admin/admin/template/img/delete.png" border="0" width="16" height="16" alt="удалить" title="удалить"></a>';
                }


                if ($control) {
                    $rs .= '<td class="' . $row_class . '">' . $edit_icon . $delete_icon . '</td>';
                }

                $rs .= '</tr>';
                //$rs .= '<option value="'.$child_id.'" '.$selected.'>'.str_repeat(' . ', $level).$category_structure['catalog'][$child_id]['name'].'</option>';
                //print_r($category_structure['childs'][$child_id]);
                if (isset($category_structure['childs'][$child_id]) && count($category_structure['childs'][$child_id]) > 0) {
                    $rs .= $this->get_child_nodes_row_control($child_id, $category_structure, $level + 1, $current_category_id, $user_id, $control, $data_structure, $params);
                }
            }
        }
        return $rs;
    }
}
