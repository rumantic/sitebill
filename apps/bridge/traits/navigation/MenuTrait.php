<?php
namespace bridge\traits\navigation;

trait MenuTrait {
    /**
     * @var array
     */
    private $menu = array();

    function getMenu ( $menu_name ) {
        return $this->menu[$menu_name];
    }

    function addNavigationPoint( $menu_name, $point ) {
        $this->menu[$menu_name][] = $point;
    }

    function buildNavigationFromArray($menu_array) {
        if ( !is_array($menu_array) ) {
            return false;
        }
        $result = array();
        foreach ( $menu_array as $item ) {
            switch ( $item['type'] ) {
                case 'topic':
                    $result = array_merge($result, $this->buidTopicNavigation());
                    break;
                default:
                    $result[] = array(
                        'title' => $item['title'],
                        'href' => (filter_var($item['href'], FILTER_VALIDATE_URL)?$item['href']:$this->createUrlTpl($item['href'])),
                        'childs' => $this->buildNavigationFromArray($item['childs']),
                    );
            }
        }
        return $result;
    }

    function load_local_menu_config ($config_name = '' ) {
        if (
            $config_name != '' and
            file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/local/'.$config_name.'/config/menu.php')
        ) {
            $local_menu_config = SITEBILL_DOCUMENT_ROOT.'/template/frontend/local/'.$config_name.'/config/menu.php';
        } else {
            $local_menu_config = SITEBILL_DOCUMENT_ROOT.'/template/frontend/local/config/menu.php';
        }
        if ( file_exists($local_menu_config) ) {
            return include($local_menu_config);
        }
        return false;
    }

    function buidTopicNavigation(){
        $result_points = array();
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_implements.php');
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $SM = new \Structure_Manager();
        $category_structure = $SM->loadCategoryStructure($this->getConfigValue('use_topic_publish_status'));

        foreach($category_structure['childs'][0] as $item_id => $categoryID){
            $point = array();
            if ($category_structure['catalog'][$categoryID]['url'] != '') {
                if (preg_match('/^http/', $category_structure['catalog'][$categoryID]['url'])) {
                    $url = $category_structure['catalog'][$categoryID]['url'];
                } else {
                    $url = $this->createUrlTpl($category_structure['catalog'][$categoryID]['url']);
                }
            } else {
                $url = $this->createUrlTpl('topic' . $categoryID . '.html');
            }
            $point['title'] = $category_structure['catalog'][$categoryID]['name'];
            $point['href'] = $url;
            if(isset($category_structure['childs'][$categoryID]) && !empty($category_structure['childs'][$categoryID])){
                $point['childs'] = $this->getTopicNavigationChilds($category_structure, $categoryID);
            }
            $result_points[] = $point;
        }
        return $result_points;
    }


    function getTopicNavigationChilds($category_structure, $ID){
        $list = array();
        foreach($category_structure['childs'][$ID] as $item_id => $categoryID){
            $point = array();
            if ($category_structure['catalog'][$categoryID]['url'] != '') {
                if (preg_match('/^http/', $category_structure['catalog'][$categoryID]['url'])) {
                    $url = $category_structure['catalog'][$categoryID]['url'];
                } else {
                    $url = $this->createUrlTpl($category_structure['catalog'][$categoryID]['url']);
                }
            } else {
                $url = $this->createUrlTpl('topic' . $categoryID . '.html');
            }
            $point['title'] = $category_structure['catalog'][$categoryID]['name'];
            $point['href'] = $url;

            if(isset($category_structure['childs'][$categoryID]) && !empty($category_structure['childs'][$categoryID])){
                $point['childs'] = $this->getTopicNavigationChilds($category_structure, $categoryID);
            }

            $list[] = $point;
        }
        return $list;
    }

}
