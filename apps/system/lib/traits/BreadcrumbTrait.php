<?php
/**
 * BreadcrumbTrait — extracted from SiteBill class (sitebill.php)
 * Auto-generated, do not edit manually.
 */
trait BreadcrumbTrait
{
    /**
     * Get breadcrumbs
     * @param array $items
     * @return string
     */
    function get_breadcrumbs($items)
    {
        if (count($items) > 0) {
            $this->template->assert('breadcrumbs_array', $items);
            return implode(' / ', $items);
        }
        return '';
    }

    /**
     * Get category breadcrumbs
     * @param array $params
     * @param array $category_structure
     * @param string $url
     * @return string
     */
    function get_category_breadcrumbs($params, $category_structure, $url = '')
    {
        $rs = '';


        if (!isset($params['topic_id']) || is_array($params['topic_id'])) {
            return $rs;
        }

        if ((int)$params['topic_id'] == 0) {
            return $rs;
        }
        if (!isset($category_structure['catalog'][$params['topic_id']])) {
            return $rs;
        }


        //foreach ( $category_structure['childs'][0] as $item_id => $catalog_id ) {

        $path = '';
        if ($category_structure['catalog'][$params['topic_id']]['url'] != '') {
            $path = rtrim($url, '/') . '/' . $category_structure['catalog'][$params['topic_id']]['url'];
        } else {
            $path = rtrim($url, '/') . '/' . '/topic' . $params['topic_id'] . '.html';
        }

        $ra[] = '<a itemprop="item" title="' . $category_structure['catalog'][$params['topic_id']]['name'] . '" href="' . $this->createUrlTpl($path) . '"><span itemprop="name">' . $category_structure['catalog'][$params['topic_id']]['name'] . '</span></a>';


        $parent_category_id = $category_structure['catalog'][$params['topic_id']]['parent_id'];
        while ($category_structure['catalog'][$parent_category_id]['parent_id'] != 0) {
            if ($j++ > 100) {
                return;
            }

            $path = '';
            if (isset($category_structure['catalog'][$parent_category_id]) && $category_structure['catalog'][$parent_category_id]['url'] != '') {
                $path = rtrim($url, '/') . '/' . $category_structure['catalog'][$parent_category_id]['url'];
            } else {
                $path = rtrim($url, '/') . '/' . '/topic' . $parent_category_id . '.html';
            }

            $ra[] = '<a itemprop="item" title="' . $category_structure['catalog'][$parent_category_id]['name'] . '" href="' . $this->createUrlTpl($path) . '"><span itemprop="name">' . $category_structure['catalog'][$parent_category_id]['name'] . '</span></a>';

            $parent_category_id = $category_structure['catalog'][$parent_category_id]['parent_id'];
        }
        if (isset($category_structure['catalog'][$parent_category_id]) && $category_structure['catalog'][$parent_category_id]['name'] != '') {
            $path = '';
            if ($category_structure['catalog'][$parent_category_id]['url'] != '') {
                $path = rtrim($url, '/') . '/' . $category_structure['catalog'][$parent_category_id]['url'];
            } else {
                $path = rtrim($url, '/') . '/' . '/topic' . $parent_category_id . '.html';
            }

            $ra[] = '<a itemprop="item" title="' . $category_structure['catalog'][$parent_category_id]['name'] . '" href="' . $this->createUrlTpl($path) . '"><span itemprop="name">' . $category_structure['catalog'][$parent_category_id]['name'] . '</span></a>';

        }
        if (Multilanguage::is_set('LT_BC_HOME', '_template')) {
            $ra[] = '<a itemprop="item" title="' . Multilanguage::_('LT_BC_HOME', '_template') . '" href="' . $this->createUrlTpl('') . '"><span itemprop="name">' . Multilanguage::_('LT_BC_HOME', '_template') . '</span></a>';
        } else {
            $ra[] = '<a itemprop="item" title="' . Multilanguage::_('L_HOME') . '" href="' . $this->createUrlTpl('') . '"><span itemprop="name">' . Multilanguage::_('L_HOME') . '</span></a>';
        }
        //$ra[]='<a href="'.SITEBILL_MAIN_URL.'/">'.Multilanguage::_('L_HOME').'</a>';
        $breadcrumbs_array = array_reverse($ra);
        $position = 1;
        foreach ($breadcrumbs_array as $item) {
            $li_breadcrumbs[] = '<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">' . $item . '<meta itemprop="position" content="' . $position . '" /></span>';
            $position++;
        }
        $rs = implode(' / ', $li_breadcrumbs);
        $rs_result = '<div itemscope itemtype="https://schema.org/BreadcrumbList">' . $rs . '</div>';

        $this->template->assert('breadcrumbs_array', $breadcrumbs_array);

        return $rs_result;
    }

    /*
     * тестовая функция для кастомизации крошек
     */
    function get_category_breadcrumbs_test($params, $category_structure, $url = '')
    {
        $rs = '';
        $bc_array = array();

        if (!isset($params['topic_id']) || is_array($params['topic_id'])) {
            return $rs;
        }

        if ((int)$params['topic_id'] == 0) {
            return $rs;
        }
        if (!isset($category_structure['catalog'][$params['topic_id']])) {
            return $rs;
        }


        //foreach ( $category_structure['childs'][0] as $item_id => $catalog_id ) {
        if ($category_structure['catalog'][$params['topic_id']]['url'] != '') {
            $ra[] = '<a href="' . rtrim($url, '/') . '/' . $category_structure['catalog'][$params['topic_id']]['url'] . (false === strpos($category_structure['catalog'][$params['topic_id']]['url'], '.') ? self::$_trslashes : '') . '">' . $category_structure['catalog'][$params['topic_id']]['name'] . '</a>';
            $bc_array[] = array(
                'href' => SITEBILL_MAIN_URL . '/' . $category_structure['catalog'][$params['topic_id']]['url'] . (false === strpos($category_structure['catalog'][$params['topic_id']]['url'], '.') ? self::$_trslashes : ''),
                'name' => $category_structure['catalog'][$params['topic_id']]['name']
            );

        } else {
            $ra[] = '<a href="' . rtrim($url, '/') . '/topic' . $params['topic_id'] . '.html">' . $category_structure['catalog'][$params['topic_id']]['name'] . '</a>';
            $bc_array[] = array(
                'href' => SITEBILL_MAIN_URL . '/topic' . $params['topic_id'] . '.html',
                'name' => $category_structure['catalog'][$params['topic_id']]['name']
            );
        }

        $parent_category_id = $category_structure['catalog'][$params['topic_id']]['parent_id'];
        while ($category_structure['catalog'][$parent_category_id]['parent_id'] != 0) {
            if ($j++ > 100) {
                return;
            }
            if (isset($category_structure['catalog'][$parent_category_id]) && $category_structure['catalog'][$parent_category_id]['url'] != '') {
                $ra[] = '<a href="' . rtrim($url, '/') . '/' . $category_structure['catalog'][$parent_category_id]['url'] . (false === strpos($category_structure['catalog'][$parent_category_id]['url'], '.') ? self::$_trslashes : '') . '">' . $category_structure['catalog'][$parent_category_id]['name'] . '</a>';
                $bc_array[] = array(
                    'href' => SITEBILL_MAIN_URL . '/' . $category_structure['catalog'][$parent_category_id]['url'] . (false === strpos($category_structure['catalog'][$parent_category_id]['url'], '.') ? self::$_trslashes : ''),
                    'name' => $category_structure['catalog'][$parent_category_id]['name']
                );
            } else {
                $ra[] = '<a href="' . rtrim($url, '/') . '/topic' . $parent_category_id . '.html">' . $category_structure['catalog'][$parent_category_id]['name'] . '</a>';
                $bc_array[] = array(
                    'href' => SITEBILL_MAIN_URL . '/topic' . $parent_category_id . '.html',
                    'name' => $category_structure['catalog'][$parent_category_id]['name']
                );
            }
            $parent_category_id = $category_structure['catalog'][$parent_category_id]['parent_id'];
        }
        if (isset($category_structure['catalog'][$parent_category_id]) && $category_structure['catalog'][$parent_category_id]['name'] != '') {
            if ($category_structure['catalog'][$parent_category_id]['url'] != '') {
                $ra[] = '<a href="' . rtrim($url, '/') . '/' . $category_structure['catalog'][$parent_category_id]['url'] . (false === strpos($category_structure['catalog'][$parent_category_id]['url'], '.') ? self::$_trslashes : '1') . '">' . $category_structure['catalog'][$parent_category_id]['name'] . '</a>';
                $bc_array[] = array(
                    'href' => SITEBILL_MAIN_URL . '/' . $category_structure['catalog'][$parent_category_id]['url'] . (false === strpos($category_structure['catalog'][$parent_category_id]['url'], '.') ? self::$_trslashes : ''),
                    'name' => $category_structure['catalog'][$parent_category_id]['name']
                );

            } else {
                $ra[] = '<a href="' . rtrim($url, '/') . '/topic' . $parent_category_id . '.html">' . $category_structure['catalog'][$parent_category_id]['name'] . '</a>';
                $bc_array[] = array(
                    'href' => SITEBILL_MAIN_URL . '/topic' . $parent_category_id . '.html',
                    'name' => $category_structure['catalog'][$parent_category_id]['name']
                );

            }
        }
        if (Multilanguage::is_set('LT_BC_HOME', '_template')) {
            $ra[] = '<a href="' . SITEBILL_MAIN_URL . '/">' . Multilanguage::_('LT_BC_HOME', '_template') . '</a>';
            $bc_array[] = array(
                'href' => SITEBILL_MAIN_URL . '/',
                'name' => Multilanguage::_('LT_BC_HOME', '_template')
            );
        } else {
            $ra[] = '<a href="' . SITEBILL_MAIN_URL . '/">' . Multilanguage::_('L_HOME') . '</a>';
            $bc_array[] = array(
                'href' => SITEBILL_MAIN_URL . '/',
                'name' => Multilanguage::_('L_HOME')
            );
        }
        $bc_array = array_reverse($bc_array);
        //print_r($bc_array);
        //$ra[]='<a href="'.SITEBILL_MAIN_URL.'/">'.Multilanguage::_('L_HOME').'</a>';
        $rs = implode(' / ', array_reverse($ra));
        return $rs;
    }

    /**
     * Get category breadcrumbs
     * @param array $params
     * @param array $category_structure
     * @param string $url
     * @return string
     */
    function get_category_breadcrumbs_string($params, $category_structure, $url = '')
    {
        $rs = '';
        $ra = array();
        $parent_category_id = 0;
        $j = 0;
        if (isset($category_structure['catalog'][$params['topic_id']])) {
            $ra[] = '' . $category_structure['catalog'][$params['topic_id']]['name'] . '';
            $parent_category_id = $category_structure['catalog'][$params['topic_id']]['parent_id'];
        }


        while (isset($category_structure['catalog'][$parent_category_id]['parent_id']) && $category_structure['catalog'][$parent_category_id]['parent_id'] != 0) {
            if ($j++ > 100) {
                return;
            }
            $ra[] = '' . $category_structure['catalog'][$parent_category_id]['name'] . '';
            $parent_category_id = $category_structure['catalog'][$parent_category_id]['parent_id'];
        }
        if (isset($category_structure['catalog'][$parent_category_id]['name']) && $category_structure['catalog'][$parent_category_id]['name'] != '') {
            $ra[] = '' . $category_structure['catalog'][$parent_category_id]['name'] . '';
        }
        $this->set_breadcrumbs_array(array_reverse($ra));
        $rs = implode(' / ', array_reverse($ra));
        return $rs;
    }

    function set_breadcrumbs_array($breadcrumbs_array = array())
    {
        $this->breadcrumbs_array = $breadcrumbs_array;
    }

    function get_breadcrumbs_array()
    {
        return $this->breadcrumbs_array;
    }

    /**
     * Formatting breadcrumbs string from array for template
     * @param array $bcarray Array of items [href, title, name]
     * @return string
     */
    function breadcrumbs2tpl($bcarray){
        $bc = [];
        foreach ($bcarray as $b){
            if($b['href'] != ''){
                $bc[] = '<a href="' . $b['href'] . '"'.(isset($b['title']) && $b['title'] != '' ? ' title="'.$b['title'].'"' : '').'>'.$b['name'].'</a>';
            }else{
                $bc[] = $b['name'];
            }
        }
        return $this->get_breadcrumbs($bc);
    }

}
