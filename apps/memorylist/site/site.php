<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Memorylist frontend - работаем со сохраненными списками пользователей
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class memorylist_site extends memorylist_admin
{
    public function get_save_sharelist_button () {
        $grid_constructor = $this->_getGridConstructor();
        $query_stack = $grid_constructor->get_query_stack('main_grid', 'all_ids_in_grid');
        if ( $query_stack ) {
            $DBC = DBC::getInstance();
            $stmt = $DBC->query($query_stack['query'], $query_stack['where_value_prepared'], $success);
            if ( $stmt ) {
                while ($ar = $DBC->fetch($stmt)) {
                    $ids[] = $ar['id'];
                }
                if ( is_array($ids) ) {
                    $this->template->assign('sharelist_id', $this->generate_sharelist_id($query_stack));
                    $this->template->assign('ids', implode(',', $ids));
                    return $this->template->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/memorylist/site/template/save_sharelist_button.tpl');
                }
            }
        }
        return false;
    }

    function generate_sharelist_id ($query_stack) {
        return md5($this->get_session_key().$query_stack['query'].implode('', $query_stack['where_value_prepared']));
    }

    function frontend()
    {
        $REQUESTURIPATH = Sitebill::getClearRequestURI();

        if (preg_match('/^sharelist(\/(.*)?)?$/', $REQUESTURIPATH, $matches)) {
            return $this->sharelist($matches[2]);
        }

        if (preg_match('/^memorylist(\/(.*)?)?$/', $REQUESTURIPATH)) {
            $uid = (int)$_SESSION['user_id'];
            if ($uid == 0 or !isset($uid)) {
                $this->go_to_login();
            }

            require_once SITEBILL_DOCUMENT_ROOT . '/apps/memorylist/admin/memory_list.php';
            $ML = new Memory_List();

            $breadcrumbs[] = '<a href="' . SITEBILL_MAIN_URL . '/">' . Multilanguage::_('L_HOME') . '</a>';
            $breadcrumbs[] = '<a href="' . SITEBILL_MAIN_URL . '/account/">' . _e('Личный кабинет') . '</a>';
            $breadcrumbs[] = '<a href="' . SITEBILL_MAIN_URL . '/account/data/">' . _e('Объявления') . '</a>';
            $breadcrumbs[] = Multilanguage::_('APP_NAME', 'memorylist');
            $this->template->assert('title', Multilanguage::_('APP_NAME', 'memorylist'));
            $this->template->assert('breadcrumbs', implode(' / ', $breadcrumbs));

            if ($this->getRequestValue('do') == 'getpdf') {
                $ML->_getpdfAction();
            } elseif ($this->getRequestValue('do') == 'showfilter') {
                $this->template->assign('main', $ML->showfilter());
            } elseif ($this->getRequestValue('do') == 'delete') {
                $id = intval($this->getRequestValue('filter_id'));
                $ML->deleteMemorylist($id);
                $this->template->assign('main', $ML->grid());
            } else {
                $this->template->assign('main', $ML->grid());
            }
            return true;
        }
        return false;
    }

    public function sharelist($sharelist_id)
    {
        //$this->template->assign('main', 'sharelist');
        if ( $this->request()->get('do') == 'new_done' ) {
            $this->add_share_items($this->request()->get('ids'));
            $this->add_my_sharelist_id($sharelist_id);
        }
        if ( $this->request()->get('do') == 'delete_done' ) {
            $this->delete_share_item($this->request()->get('data_id'));
        }

        if ( $this->is_my_sharelist_id($sharelist_id) ) {
            $this->register_delete_action($sharelist_id);
        }
        $this->share_grid($sharelist_id);
        return true;
    }

    protected function register_delete_action ($sharelist_id) {
        global $smarty;
        if (!isset($smarty->registered_plugins['function']['_sharelist_delete_button'])) {
            $this->template->assign('sharelist_can_delete', true);
            $this->template->assign('sharelist_id', $sharelist_id);
            $smarty->registerPlugin('function', '_sharelist_delete_button', array(&$this, '_sharelist_delete_button'));
        }
    }

    public function _sharelist_delete_button ($params) {
        $this->template->assign('sharelist_data_id', $params['data_id']);
        $this->template->assign('sharelist_id', $params['sharelist_id']);
        return $this->template->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/memorylist/site/template/delete_sharelist_button.tpl');
    }

    protected function add_my_sharelist_id ($sharelist_id) {
        $_SESSION['sharelist_id'][$sharelist_id] = 'my';
    }

    protected function is_my_sharelist_id ($sharelist_id) {
        if ( isset($_SESSION['sharelist_id']) and isset($_SESSION['sharelist_id'][$sharelist_id]) ) {
            return true;
        }
        return false;
    }

    protected function add_share_items($ids) {
        if ( is_scalar($ids) ) {
            $ids = explode(',', $ids);
        }

        $memorylist_id =  $this->ML->get_domain_memory_list_id (
            $this->request()->get('sharelist_id'),
            $this->getSessionUserId(),
            1,
            'Подборка из поиска' );
        $this->ML->appendItems($memorylist_id, $ids);
    }

    protected function delete_share_item($id) {
        $memorylist_id =  $this->ML->get_domain_memory_list_id (
            $this->request()->get('sharelist_id'),
            $this->getSessionUserId(),
            1,
            'Подборка из поиска' );
        $this->ML->delete_item($memorylist_id, $id);
    }

    protected function push_share_ids ($ids) {

    }

    protected function get_items_by_domain_id ( $domain_id ) {

    }

    protected function share_grid($sharelist_id)
    {
        $this->setGridViewType();
        $grid_constructor = $this->_getGridConstructor();
        $params['page'] = $this->getRequestValue('page');
        $params['asc'] = $this->getRequestValue('asc');
        $params['order'] = $this->getRequestValue('order');

        $params['favorites'] = $this->ML->select_data_ids_by_deal_id($this->getSessionUserId(), $sharelist_id, 1);
        if ( is_array($params['favorites']) and count($params['favorites']) == 0 ) {
            $params['favorites'] = array(-1);
        }

        $this->template->assert('onlyspecial', $params['onlyspecial']);
        $grid_constructor->set_myfavorites_uri('sharelist/'.$sharelist_id);

        $grid_constructor->main($params);
        $this->template->assert('breadcrumbs', $this->get_breadcrumbs(array('<a href="' . $this->createUrlTpl('') . '">' . Multilanguage::_('L_HOME') . '</a>', _e('Рекомендуем'))));
        return true;
    }

    protected function setGridViewType()
    {
        if (in_array($this->getRequestValue('grid_type'), array('thumbs', 'list', 'lth'))) {
            $_SESSION['grid_type'] = $this->getRequestValue('grid_type');
        } else {
            if (!isset($_SESSION['grid_type'])) {
                if ($this->getConfigValue('grid_type') != '') {
                    $_SESSION['grid_type'] = $this->getConfigValue('grid_type');
                } else {
                    $_SESSION['grid_type'] = 'list';
                }
            }
        }
    }

}
