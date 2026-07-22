<?php
/**
 * UserDataGridTrait — grid/list display methods extracted from User_Data_Manager.
 *
 * Methods: grid_e, get_data_grid, getOfferList
 */
trait UserDataGridTrait
{
    /**
     * Return grid
     * @param int $user_id user id
     * @param int $current_category_id current category id
     * @return string
     */
    function grid_e($user_id, $current_category_id)
    {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $category_tree = $Structure_Manager->get_category_tree_control($current_category_id, $user_id);

        $rs = '';
        $rs .= '<div class="grids">';

        if (1 == $this->getConfigValue('show_cattree_left')) {

            $rs .= '<table border="0" width="99%" cellpadding="0" cellspacing="0">';

            $rs .= '<tr>';
            $rs .= '<td style="vertical-align: top;" id="lk_tree">';
            $rs .= $category_tree;
            $rs .= '</td>';
            $rs .= '<td style="vertical-align: top;">';
            $rs .= $this->get_data_grid($user_id, $current_category_id);
            $rs .= '</td>';
            $rs .= '</tr>';

            $rs .= '</table>';
        } else {
            $this->template->assert('category_tree_account', $category_tree);
            $rs .= $this->get_data_grid($user_id, $current_category_id);
        }

        $rs .= '</div>';
        return $rs;
    }

    /**
     * Get data grid
     * @param int $user_id
     * @return string
     */
    function get_data_grid($user_id, $current_category_id = false)
    {

        $FM = new frontend_main();
        $params = $FM->gatherRequestParams();

        $params['user_id'] = $user_id;

        $grid_constructor = $this->_getGridConstructor();

        $params['active'] = $this->getRequestValue('active');
        $params['id'] = (int)$this->getRequestValue('id');

        if ((int)$this->getRequestValue('page_limit') != 0) {
            $params['page_limit'] = (int)$this->getRequestValue('page_limit');
        } else {
            if (0 !== (int)$this->getConfigValue('per_page_account')) {
                $params['page_limit'] = (int)$this->getConfigValue('per_page_account');
            }
        }
        //dd($params['page_limit']);
        $params['admin'] = true;
        if ($this->getRequestValue('srch_export_cian') == 'on' || $this->getRequestValue('srch_export_cian') == '1') {
            $params['srch_export_cian'] = 1;
        }

        $coworked = array();
        if (1 == $this->getConfigValue('enable_curator_mode')) {

            $DBC = DBC::getInstance();

            if (1 == $this->getConfigValue('curator_mode_fullaccess')) {
                $query = 'SELECT user_id FROM ' . DB_PREFIX . '_user WHERE parent_user_id=?';
                $stmt = $DBC->query($query, array($user_id));
                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        $coworked[] = $ar['user_id'];
                    }
                }
                $params['coworked_users'] = $coworked;
            } else {
                $query = 'SELECT id FROM ' . DB_PREFIX . '_cowork WHERE coworker_id=? AND object_type=?';
                $stmt = $DBC->query($query, array($user_id, 'data'));
                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        $coworked[] = $ar['id'];
                    }
                }
                $params['coworked_ids'] = $coworked;
            }
        }

        $params['pager_url'] = 'account/data';

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $category_structure = $Structure_Manager->loadCategoryStructure();

        $res = $grid_constructor->get_sitebill_adv_ext($params);
        if (!empty($res) && $this->getConfigValue('apps.mailbox.enable') == 1) {
            $ids = array();
            foreach ($res as $i => $d) {
                $ids[$d[$this->primary_key]] = $i;
                $res[$i]['_mailbox_cnt']['l'] = SITEBILL_MAIN_URL . '/mailbox/?realty_id=' . $d[$this->primary_key];
            }
            $DBC = DBC::getInstance();
            $query = 'SELECT COUNT(mailbox_id) AS _cnt, realty_id, `status` FROM ' . DB_PREFIX . '_mailbox WHERE realty_id IN (' . implode(',', array_keys($ids)) . ') GROUP BY realty_id, `status`';
            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    if ($ar['status'] == 1) {
                        $res[$ids[$ar['realty_id']]]['_mailbox_cnt']['r'] = $ar['_cnt'];
                        $res[$ids[$ar['realty_id']]]['_mailbox_cnt']['t'] += $ar['_cnt'];
                    } else {
                        $res[$ids[$ar['realty_id']]]['_mailbox_cnt']['u'] = $ar['_cnt'];
                        $res[$ids[$ar['realty_id']]]['_mailbox_cnt']['t'] += $ar['_cnt'];
                    }
                }
            }
        }
        $this->template->assign('grid_items', $res);
        $this->template->assign('admin', 1);
        $this->template->assign('topic_id', $params['topic_id']);

        return $this->template->fetch(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/realty_grid_account.tpl');

        //global $smarty;
        //return $smarty->fetch("realty_grid_account.tpl");

        //return $this->template->fetch(SITEBILL_DOCUMENT_ROOT."/template/frontend/realia/realty_grid_account.tpl");
    }

    // TODO Check is used anuwhere
    /**
     * Get offer list
     * @param int $user_id
     * @param mixed $current_category_id
     * @return mixed
     */
    function getOfferList($user_id, $current_category_id = false)
    {
        $ret = array();
        if ($current_category_id) {
            $query = 'SELECT * FROM ' . DB_PREFIX . '_data WHERE user_id=' . $user_id . ' and topic_id = ' . $current_category_id;
        } else {
            $query = 'SELECT * FROM ' . DB_PREFIX . '_data WHERE user_id=' . $user_id;
        }
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ret[] = $ar;
            }
        }
        return $ret;
    }
}
