<?php

trait StructureLinkerTrait
{
    function saveLinkerAssociations($rules)
    {
        foreach ($_POST['topic'] as $topic_id => $link_topic_id) {
            $this->update_topic_links($topic_id, $link_topic_id, $_POST['params_topic'][$topic_id]);
        }
    }

    function update_topic_links($topic_id, $link_topic_id, $params)
    {
        $DBC = DBC::getInstance();
        //echo "topic_id = $topic_id, link_topic_id = $link_topic_id, params = $params<br>";
        $query = 'SELECT id FROM ' . DB_PREFIX . '_topic_links WHERE topic_id=?';
        $stmt = $DBC->query($query, array($topic_id), $row, $success);
        //echo $DBC->getLastError();

        if ($stmt) {
            //echo 'exist<br>';
            $ar = $DBC->fetch($stmt);
            if ($ar['id'] > 0) {
                $query = 'update ' . DB_PREFIX . '_topic_links SET topic_id=?, link_topic_id=?, params=? where topic_id=?';
                $stmt = $DBC->query($query, array($topic_id, $link_topic_id, $params, $topic_id), $row, $success);
                if (!$success) {
                    echo $DBC->getLastError();
                }
            }
        } else {
            //echo $DBC->getLastError().'<br>';
            //echo 'not exist<br>';

            $query = 'insert into ' . DB_PREFIX . '_topic_links (topic_id, link_topic_id, params) values (?, ?, ?)';
            $stmt = $DBC->query($query, array($topic_id, $link_topic_id, $params));
        }
        return true;
    }

    function load_topic_links()
    {
        $DBC = DBC::getInstance();
        $ra = array();
        $query = 'SELECT * FROM ' . DB_PREFIX . '_topic_links';
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ra[$ar['topic_id']]['link_topic_id'] = $ar['link_topic_id'];
                $ra[$ar['topic_id']]['params'] = $ar['params'];
            }
        }
        return $ra;
    }

    function getCategoryTreeLinker()
    {
        $topic_links_hash = $this->load_topic_links();
        //echo '$current_category_id = '.$current_category_id;
        $category_structure = $this->loadCategoryStructure();
        $data_structure = $this->load_data_structure(0);

        //echo '<pre>';
        //print_r($category_structure);
        $level = 0;
        $rs = '';
        $rs .= '<form method="post">';
        $rs .= '<table class="table table-striped table-bordered table-hover">';
        $rs .= '<tr>';
        $rs .= '<td class="row_title" colspan="4"><input type="submit" value="' . Multilanguage::_('L_TEXT_SAVE') . '" name="submit" /></td>';
        $rs .= '</tr>';
        $rs .= '<tr>';
        $rs .= '<td class="row_title" width="20%">' . Multilanguage::_('L_TEXT_TITLE') . '</td>';
        $rs .= '<td class="row_title" width="10%">' . Multilanguage::_('OPERATION_TYPE', 'system') . '</td>';
        $rs .= '<td class="row_title" width="80%">' . Multilanguage::_('PARAMS', 'system') . '</td>';
        $rs .= '</tr>';
        if (count($category_structure) > 0) {
            foreach ($category_structure['childs'][0] as $item_id => $catalog_id) {
                $rs .= $this->getRowLinker($catalog_id, $category_structure, $level, 'row1', $topic_links_hash, $data_structure);
                $rs .= $this->getChildNodesRowLinker($catalog_id, $category_structure, $level + 1, $current_category_id, $topic_links_hash, $data_structure);
            }
        }
        $rs .= '<tr>';
        $rs .= '<input type="hidden" name="action" value="structure" />';
        $rs .= '<input type="hidden" name="do" value="linker" />';
        $rs .= '<td class="row_title" colspan="4"><input type="submit" value="' . Multilanguage::_('L_TEXT_SAVE') . '" name="submit" /></td>';

        $rs .= '</tr>';
        $rs .= '</table>';
        $rs .= '</form>';
        return $rs;
    }

    function getRowLinker($categoryID, $category_structure, $level, $row_class, $topic_links_hash, $data_structure = false)
    {
        $rs = '';
        if (isset($data_structure['data'][0][$categoryID])) {
            $has_data = ' warning ';
        } else {
            $has_data = '';
        }

        $rs .= '<tr class="' . $has_data . '">';
        $rs .= '<td class="' . $row_class . '">' . str_repeat('&nbsp;.&nbsp;', $level) . $category_structure['catalog'][$categoryID]['name'] . ' <strong>[' . $categoryID . ']</strong>' . ' d = ' . $data_structure['data'][0][$categoryID] . '</td>';

        $params['ignore_published_status'] = 1;
        $true_categoryID = $categoryID;
        if ($topic_links_hash[$categoryID]['link_topic_id'] != '') {
            $categoryID = $topic_links_hash[$categoryID]['link_topic_id'];
        }

        if ($categoryID != $true_categoryID) {
            $changed_style = 'style="background-color: green;"';
        } else {
            $changed_style = '';
        }

        $rs .= '<td class="' . $row_class . '" ' . $changed_style . '>' . $this->getCategorySelectBoxWithName('topic[' . $true_categoryID . ']', $categoryID, false, $params) . '</td>';
        $rs .= '<td class="' . $row_class . '"><textarea name="params_topic[' . $true_categoryID . ']">' . $topic_links_hash[$true_categoryID]['params'] . '</textarea></td>';

        $rs .= '</tr>';

        return $rs;
    }

    function getChildNodesRowLinker($categoryID, $category_structure, $level, $current_category_id, $topic_links_hash, $data_structure = false)
    {
        if (!is_array($category_structure['childs'][$categoryID])) {
            return '';
        }
        $params['ignore_published_status'] = 1;
        foreach ($category_structure['childs'][$categoryID] as $child_id) {
            if ($category_structure['catalog'][$child_id]['published'] == 1) {
                // Чтобы проще видеть, в линкере отображаем только не опубликованные ветки
                // В рабочей версии закоменчено, пока включать через код, потом выключать
                //continue;
            }
            if ($topic_links_hash[$child_id]['link_topic_id'] != '') {
                $tmp_child_id = $topic_links_hash[$child_id]['link_topic_id'];
            } else {
                $tmp_child_id = $child_id;
            }

            if ($child_id != $tmp_child_id) {
                $changed_style = 'style="background-color: green;"';
            } else {
                $changed_style = '';
            }


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
            if ($category_structure['catalog'][$child_id]['published'] == 0 and isset($data_structure['data'][0][$child_id]) and $data_structure['data'][0][$child_id] > 100) {
                $has_data = ' error ';
            } elseif ($category_structure['catalog'][$child_id]['published'] == 0 and isset($data_structure['data'][0][$child_id])) {
                $has_data = ' warning ';
            } else {
                $has_data = '';
            }

            $rs .= '<tr class="' . $has_data . '">';
            $rs .= '<td class="' . $row_class . '">' .
                str_repeat('&nbsp;.&nbsp;', $level) . $category_structure['catalog'][$child_id]['name'] .
                ' <strong>[' . $child_id . ']</strong>' .
                ' d = ' . $data_structure['data'][0][$child_id] .
                ' p = ' . $category_structure['catalog'][$child_id]['published'] . '</td>';


            $rs .= '<td class="' . $row_class . '" ' . $changed_style . '>' . $this->getCategorySelectBoxWithName('topic[' . $child_id . ']', $tmp_child_id, false, $params) . '</td>';
            $rs .= '<td class="' . $row_class . '"><textarea type="text" name="params_topic[' . $child_id . ']">' . $topic_links_hash[$child_id]['params'] . '</textarea></td>';


            $rs .= '</tr>';
            if (is_array($category_structure['childs'][$child_id]) and count($category_structure['childs'][$child_id]) > 0) {
                $rs .= $this->getChildNodesRowLinker($child_id, $category_structure, $level + 1, $current_category_id, $topic_links_hash, $data_structure);
            }
        }
        return $rs;
    }
}
