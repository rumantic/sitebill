<?php

/**
 * PureCSS menu class
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class PureCSS_Menu extends Structure_Manager {

    /**
     * Constructor
     */
    function __construct() {
        $this->SiteBill();
    }

    /**
     * Get menu
     * @param
     * @return
     */
    function get_menu() {
        $category_structure = $this->loadCategoryStructure($this->getConfigValue('use_topic_publish_status'));
        $level = 1;
        $rs = '<ul class="pureCssMenu pureCssMenum" style="border: 0px;">';
        foreach ($category_structure['childs'][0] as $item_id => $categoryID) {

            if (isset($category_structure['childs'][$categoryID]) && count($category_structure['childs'][$categoryID]) > 0) {
                $name = '<span>' . $category_structure['catalog'][$categoryID]['name'] . '</span>';
            } else {
                $name = $category_structure['catalog'][$categoryID]['name'];
            }
            
            $href = '';
            
            if ($category_structure['catalog'][$categoryID]['url'] != '') {
                if (preg_match('/^http/', $category_structure['catalog'][$categoryID]['url'])) {
                    $href = $category_structure['catalog'][$categoryID]['url'];
                } else {
                    $href = $this->createUrlTpl($category_structure['catalog'][$categoryID]['url']);
                }
            } else {
                $href = $this->createUrlTpl('topic' . $categoryID . '.html');
            }

            $rs .= '<li class="pureCssMenui"><a class="pureCssMenui' . ((isset($category_structure['catalog'][$categoryID]['current']) && $category_structure['catalog'][$categoryID]['current'] == 1) ? ' current' : '') . '" href="' . $href . '">' . $name . '</a>';
            
            if (isset($category_structure['childs'][$categoryID]) && count($category_structure['childs'][$categoryID]) > 0) {
                $rs .= $this->getChildNodes($categoryID, $category_structure, $level + 1, $current_category_id = 0);
            }
            $rs .= '</li>';
        }
        $rs .= '</ul>';
        return $rs;
    }

    /**
     * Get child nodes
     * @param $categoryID
     * @param $category_structure
     * @param $level
     * @param $current_category_id
     */
    function getChildNodes($categoryID, $category_structure, $level, $current_category_id, $superparent = 0) {
        $rs = '';
        if (!is_array($category_structure['childs'][$categoryID])) {
            return '';
        }
        $rs .= '<ul  class="pureCssMenum">';
        foreach ($category_structure['childs'][$categoryID] as $child_id) {
            // $rs .= '<li><a href="'.SITEBILL_MAIN_URL.'/?topic_id='.$child_id.'">'.$category_structure['catalog'][$child_id]['name'].'</a>';
            if (!empty($category_structure['childs'][$child_id]) && count($category_structure['childs'][$child_id]) > 0) {
                $name = '<span>' . $category_structure['catalog'][$child_id]['name'] . '</span>';
            } else {
                $name = $category_structure['catalog'][$child_id]['name'];
            }
            
            $href = '';
            
            if ($category_structure['catalog'][$child_id]['url'] != '') {
                if (preg_match('/^http/', $category_structure['catalog'][$child_id]['url'])) {
                    $href = $category_structure['catalog'][$categoryID]['url'];
                } else {
                    $href = $this->createUrlTpl($category_structure['catalog'][$child_id]['url']);
                }
            } else {
                $href = $this->createUrlTpl('topic' . $child_id . '.html');
            }

            $rs .= '<li class="pureCssMenui"><a class="pureCssMenui' . ((isset($category_structure['catalog'][$child_id]['current']) && $category_structure['catalog'][$child_id]['current'] == 1) ? ' current' : '') . '" href="' . $href . '">' . $name . '</a>';
            
            if (!empty($category_structure['childs'][$child_id]) AND count($category_structure['childs'][$child_id]) > 0) {
                $rs .= $this->getChildNodes($child_id, $category_structure, $level + 1, $current_category_id);
            }
            $rs .= '</li>';
        }
        $rs .= '</ul>';
        return $rs;
    }

}

?>