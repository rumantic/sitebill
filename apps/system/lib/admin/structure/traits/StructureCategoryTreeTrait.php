<?php

trait StructureCategoryTreeTrait
{
    /**
     * Возвращает ассоциативный массив соответствий id категорий и составным иерархическим урлам
     * @return array
     *
     */
    function loadCategoriesUrls()
    {
        $ret = $this->createCategoriesUrls();
        return $ret;
    }

    /**
     * Создает ассоциативный массив соответствий id категорий и составным иерархическим урлам
     * @return array
     */
    private function createCategoriesUrls()
    {

        if (self::$_category_urls === NULL) {
            $ret = array();
            $_ret = array();
            $DBC = DBC::getInstance();

            $query = 'SELECT id, parent_id, url AS name FROM ' . DB_PREFIX . '_topic';
            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $categories[$ar['id']] = $ar['name'];
                    $items[$ar['id']] = $ar['parent_id'];
                    $points[] = $ar['id'];
                }
            }


            if (is_array($points) && count($points) > 0) {
                if (1 == $this->getConfigValue('apps.seo.level_enable')) {
                    foreach ($points as $p) {
                        $chain = array();
                        $chain[] = $categories[$p];
                        $this->appendParent($p, $items, $chain, $categories);
                        $_ret[$p]['chain_parts'] = $chain;
                    }

                    foreach ($_ret as $k => $r) {
                        $ret[$k] = implode('/', $r['chain_parts']);
                    }
                } else {
                    foreach ($points as $p) {
                        $ret[$p] = $categories[$p];
                    }
                }
            }
            self::$_category_urls = $ret;
        }

        return self::$_category_urls;
    }

    /**
     * Ищет транслитерированный урл предка для конкретного элемента
     */
    private function appendParent($child_id, &$items, &$chain, $categories)
    {
        if ((int)$items[$child_id] !== 0) {
            array_unshift($chain, $categories[$items[$child_id]]);
            $this->appendParent($items[$child_id], $items, $chain, $categories);
        }
    }

    function createCatalogChains($structure = null)
    {
        $ret = array();
        $points = array();

        if(!is_null($structure)){
            foreach($structure['catalog'] as $item){
                $categories[$item['id']] = mb_strtolower($item['name'], 'utf-8');
                $items[$item['id']] = $item['parent_id'];
                $points[] = $item['id'];
            }
        }else{
            $fname = 'name';
            if (1 === (int)$this->getConfigValue('apps.language.use_langs')) {
                $postfix = $this->getLangPostfix($this->getCurrentLang());
                $fname .= '_' . $postfix;
            }
    
            $query = 'SELECT id, parent_id, LOWER(`' . $fname . '`) AS name FROM ' . DB_PREFIX . '_topic';
            $DBC = DBC::getInstance();
            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $categories[$ar['id']] = $ar['name'];
                    $items[$ar['id']] = $ar['parent_id'];
                    $points[] = $ar['id'];
                }
            }
        }
        
        $ret_num = [];
        if (!empty($points)) {
            foreach ($points as $p) {
                $chain = $categories[$p];
                $chain_num = $p;
                $this->findParent($p, $items, $chain, $chain_num, $categories);
                $ret[$p] = $chain;
                $ret_num[$p] = $chain_num;
            }
        }
        $ret_arr = array();
        if (!empty($ret_num)) {
            foreach ($ret_num as $k => $v) {
                $ret_arr[$k] = explode('|', $v);
            }
        }
        return array('txt' => $ret, 'num' => $ret_num, 'ar' => $ret_arr);
    }

    function findParent($child_id, &$items, &$chain, &$chain_num, $categories)
    {
        if ((int)$items[$child_id] !== 0) {
            //echo $child_id.' has parent '.$items[$child_id].'<br>';;
            $chain = $categories[$items[$child_id]] . '|' . $chain;
            $chain_num = $items[$child_id] . '|' . $chain_num;
            $this->findParent($items[$child_id], $items, $chain, $chain_num, $categories);
        }
    }

    function convertToNestedArray($structure, $parent_id = 0, $level = 0)
    {
        $nested = array();
        $level++;
        if ($level > 999) {
            echo 'to many levels in structure';
            exit;
        }
        foreach ($structure['childs'][$parent_id] as $item_id => $topic_id) {
            $structure['catalog'][$topic_id]['level'] = $level;
            $params['topic_id'] = $topic_id;
            $structure['catalog'][$topic_id]['breadcrumbs'] = $this->get_category_breadcrumbs_string($params, $structure, SITEBILL_MAIN_URL . '/');
            $structure['catalog'][$topic_id]['value'] = $structure['catalog'][$topic_id]['breadcrumbs'];

            array_push($nested, $structure['catalog'][$topic_id]);
            if (is_array($structure['childs'][$topic_id]) && count($structure['childs'][$topic_id]) > 0) {
                $tmp = $this->convertToNestedArray($structure, $topic_id, $level);
                foreach ($tmp as $tmp_item_id => $tmp_array) {
                    array_push($nested, $tmp_array);
                }
            }
        }
        return $nested;
    }

    /**
     * Load category structure
     * @param $load_published true/false - параметр определяет загружать ли категории по статусу активности. Если true - то будут загружены только активные. Если false - то будут загружены все категории
     * @return array
     */
    function loadCategoryStructure($load_published = false)
    {
        $where_active_condition = '';
        if ($load_published) {
            if (self::$_category_structure_published !== NULL) {
                return self::$_category_structure_published;
            }
        } else {
            if (self::$_category_structure !== NULL) {
                return self::$_category_structure;
            }
        }
        $DBC = DBC::getInstance();

        $postfix = $this->getLangPostfix($this->getCurrentLang());


        if ($load_published) {
            $where_active_condition = ' WHERE t.`published`=1 ';
        }

        //$query = "SELECT t.* FROM " . DB_PREFIX . "_topic t " . $where_active_condition . " ORDER BY parent_id ASC, `order` ASC, name ASC  ";
        $query = "SELECT t.* FROM " . DB_PREFIX . "_topic t " . $where_active_condition . " ORDER BY `order` ";

        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                if(isset($ar['name' . $postfix]) && $ar['name' . $postfix] != ''){
                    $ar['name'] = $ar['name' . $postfix];
                }
                $ret['catalog'][$ar['id']] = $ar;
                $ret['childs'][$ar['parent_id']][] = $ar['id'];
            }
        }

        if (1 == $this->getConfigValue('apps.seo.level_enable')) {
            $urls = $this->loadCategoriesUrls();
            if (is_array($ret['catalog']) && count($ret['catalog']) > 0) {
                foreach ($ret['catalog'] as $k => $v) {
                    $ret['catalog'][$k]['url'] = $urls[$v['id']];
                }
            }
        }

        if (1 == $this->getConfigValue('allow_topic_images')) {
            if (is_array($ret['catalog']) && count($ret['catalog']) > 0) {
                foreach ($ret['catalog'] as $k => $v) {
                    $query = "select i.* from " . DB_PREFIX . "_topic_image as li, " . DB_PREFIX . "_image as i where li.id=$k and li.image_id=i.image_id order by li.sort_order";
                    $stmt = $DBC->query($query);
                    if ($stmt) {
                        while ($ar = $DBC->fetch($stmt)) {
                            $ret['catalog'][$k]['images'][] = $ar;
                        }
                    }
                }
            }
        }

        $current = $this->urlAnalizer();
        if ($current !== FALSE) {
            $this->findCurrent($ret, $current);
        }
        if ($load_published) {
            self::$_category_structure_published = $ret;
        } else {
            self::$_category_structure = $ret;
        }
        return $ret;
    }

    /**
     * Возвращает массив всех вложенных категорий для искомой
     * @param int $category_id category ID
     * @param array $category_structure structure data array
     * @return array
     */
    function get_all_childs($category_id, $category_structure)
    {
        $ra = array();
        //echo 'category_id = '.$category_id.'<br>';
        if (isset($category_structure['childs'][$category_id]) && count($category_structure['childs'][$category_id]) > 0) {
            $ra = $category_structure['childs'][$category_id];

            foreach ($category_structure['childs'][$category_id] as $item_id => $child_id) {
                if (isset($category_structure['childs'][$child_id]) && count($category_structure['childs'][$child_id]) > 0) {
                    $ra = array_merge($ra, $this->get_all_childs($child_id, $category_structure));
                }
            }
        }
        return $ra;
    }

    /**
     * Определяем объект в контексте которого запускается класс
     * Нужно для передачи объекта при генерации дерева категорий с подсчетом количества объявлений
     * из тегов
     * @param object $context_object
     */
    function set_context($context_object)
    {
        $this->context_object = $context_object;
    }

    /**
     * Возвращает контекст
     * @return object
     */
    function get_context()
    {
        return $this->context_object;
    }

    function findCurrent(&$structure, $active)
    {
        foreach ($structure['childs'] as $k => $v) {
            foreach ($v as $vv) {
                if ($vv == $active) {
                    $structure['catalog'][$vv]['current'] = 1;
                    if ($k != 0) {
                        $structure['catalog'][$k]['current'] = 1;
                        $this->findCurrent($structure, $k);
                        return;
                    }
                }
            }
        }
    }

    function getChildsItemsCount($id, $category_structure_childs, $data_structure, &$ret)
    {
        //echo '1Call with id='.$id.' <br />';
        if (isset($category_structure_childs[$id]) && count($category_structure_childs[$id]) > 0) {
            foreach ($category_structure_childs[$id] as $v) {
                //echo '2$v='.$v.' <br />';
                //echo '2a$count='.$data_structure[$v].' <br />';
                if (isset($data_structure[$v])) {
                    $ret += $data_structure[$v];
                }

                $this->getChildsItemsCount($v, $category_structure_childs, $data_structure, $ret);
            }
        }
        //echo '3$ret='.$ret.' <br />';
        //return $data_structure['data'][$user_id][$id];
    }

    public static function has_child($id)
    {
        $DBC = DBC::getInstance();
        $query = 'SELECT COUNT(id) AS cnt FROM ' . DB_PREFIX . '_topic WHERE parent_id=?';
        $stmt = $DBC->query($query, array($id));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            return $ar['cnt'] > 0 ? true : false;
        }
        return false;
    }
}
