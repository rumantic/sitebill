<?php


namespace menu\api;

use api\aliases\API_common_alias;
use system\lib\system\apps\traits\ContextTrait;
use Illuminate\Database\Capsule\Manager as Capsule;



class menu extends API_common_alias
{
    use ContextTrait;
    function load_menus () {
        $DBC = \DBC::getInstance();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/menu/menu_structure_manager.php');
        $menu_structure_manager = new \Menu_Structure_Manager();


        $query = "SELECT ms.*, m.tag, m.name as menu_title FROM " . DB_PREFIX . "_menu m, " . DB_PREFIX . "_menu_structure ms WHERE m.menu_id=ms.menu_id ORDER BY ms.sort_order";
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $menu_item = $menu_structure_manager->load_by_id($ar['menu_structure_id']);

                $ra[$ar['tag']][$menu_item['action_code']['value']] = array(
                    'title' => $menu_item['name']['value'],
                    'href' => $menu_item['url']['value'],
                    'icon' => $menu_item['icon']['value'],
                    'active' => $_REQUEST['action'] == $menu_item['action_code']['value'] ? 1 : 0,
                );
            }
        }
        return $ra;
    }
}
