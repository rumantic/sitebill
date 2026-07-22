<?php
/**
 * DataMenuTrait — Top menu and search form methods for Data_Manager.
 *
 * Extracted from data_manager.php during refactoring.
 * Methods: getTopMenu(), getAdditionalSearchForm(), getUserSelectBox(), getFilters()
 */
trait DataMenuTrait
{
    /**
     * Get top menu
     * @param void
     * @return string
     */
    function getTopMenu() {
        global $smarty;
        if ($this->billing_mode_on) {
            $smarty->assign('billing_mode_on', 1);
        }
        if (isset($this->data_model['data']['status_id'])) {
            $smarty->assign('free_count', $this->get_count('free'));
            $smarty->assign('no_answer_count', $this->get_count('no_answer'));
            $smarty->assign('call_count', $this->get_count('call'));
            $smarty->assign('actual_count', $this->get_count('actual'));
        }

        if (1 == (int) $this->getConfigValue('apps.realty.use_predeleting') && isset($this->data_model['data']['archived'])) {
            $smarty->assign('archived_count', $this->get_count('archived'));
        }

        if (file_exists(SITEBILL_DOCUMENT_ROOT . "/template/frontend/" . $this->getConfigValue('theme') . "/apps/admin/template/data_top_menu.tpl.html")) {
            $tpl = SITEBILL_DOCUMENT_ROOT . "/template/frontend/" . $this->getConfigValue('theme') . "/apps/admin/template/data_top_menu.tpl.html";
        } elseif (file_exists($this->getAdminTplFolder() . '/data_top_menu.tpl.html')) {
            $tpl = $this->getAdminTplFolder() . '/data_top_menu.tpl.html';
        } else {
            $tpl = '';
        }


        if ($tpl != '') {
            $smarty->assign('user_select_box', $this->getUserSelectBox());
            $smarty->assign('active_items_count', $this->get_count(1));
            $smarty->assign('notactive_items_count', $this->get_count('notactive'));
            if ($this->billing_mode_on) {
                $billing_mode_on_counts = array();
                $billing_mode_on_counts['vip'] = $this->get_count('vip');
                $billing_mode_on_counts['premium'] = $this->get_count('premium');
                $billing_mode_on_counts['bold'] = $this->get_count('bold');
                $smarty->assign('billing_mode_on_counts', $billing_mode_on_counts);
                $billing_mode_on_statuses['vip'] = (int) $this->getRequestValue('vip_status');

                $billing_mode_on_statuses['premium'] = (int) $this->getRequestValue('premium_status');

                $billing_mode_on_statuses['bold'] = (int) $this->getRequestValue('bold_status');
                $smarty->assign('billing_mode_on_statuses', $billing_mode_on_statuses);
            } else {
                $smarty->assign('hot_items_count', $this->get_count('hot'));
            }

            $smarty->assign('all_items_count', $this->get_count('all'));


            $smarty->assign('active', $this->getRequestValue('active'));
            $smarty->assign('hot', $this->getRequestValue('hot'));

            $rs = $smarty->fetch($tpl);
        } else {
            $rs = '';
            $rs .= '<table border="0">';
            $rs .= '<tr>';
            $rs .= '<td>';
            $rs .= '<a href="?action=' . $this->action . '&do=new" class="btn btn-success">' . Multilanguage::_('L_ADD_RECORD_BUTTON') . '</a>';
            $rs .= '</td>';
            $rs .= '<td>';
            $rs .= '&nbsp;&nbsp;&nbsp;&nbsp;';
            if ($this->getRequestValue('active') == 1) {
                $rs .= '<b>' . Multilanguage::_('ACTIVE_ITEMS', 'system') . ' (' . $this->get_count(1) . ')</b> | ';
            } else {
                $rs .= '<a href="?action=' . $this->action . '&active=1">' . Multilanguage::_('ACTIVE_ITEMS', 'system') . ' (' . $this->get_count(1) . ')</a> | ';
            }
            if ($this->getRequestValue('active') === 'notactive') {
                $rs .= '<b>' . Multilanguage::_('NOTACTIVE_ITEMS', 'system') . ' (' . $this->get_count('notactive') . ')</b> | ';
            } else {
                $rs .= '<a href="?action=' . $this->action . '&active=notactive">' . Multilanguage::_('NOTACTIVE_ITEMS', 'system') . ' (' . $this->get_count('notactive') . ')</a> | ';
            }
            if ($this->getRequestValue('hot') == 1) {
                $rs .= '<b>' . ($this->getConfigValue('theme') === 'albostar' ? Multilanguage::_('EDITED_ITEMS', 'system') : Multilanguage::_('HOT_ITEMS', 'system')) . ' (' . $this->get_count('hot') . ')</b> | ';
            } else {
                $rs .= '<a href="?action=' . $this->action . '&hot=1">' . ($this->getConfigValue('theme') === 'albostar' ? Multilanguage::_('EDITED_ITEMS', 'system') : Multilanguage::_('HOT_ITEMS', 'system')) . ' (' . $this->get_count('hot') . ')</a> | ';
            }

            if ($this->getRequestValue('active') == '' && $this->getRequestValue('hot') != 1) {
                $rs .= '<b>Все (' . $this->get_count('all') . ')</b>  ';
            } else {
                $rs .= '<a href="?action=' . $this->action . '">Все (' . $this->get_count('all') . ')</a>  ';
            }

            $rs .= '</td>';
            $rs .= '<td>';
            $rs .= '' . $this->getAdditionalSearchForm();
            $rs .= '</td>';
            $rs .= '</tr>';
            $rs .= '</table>';
        }
        return $rs;
    }

    function getAdditionalSearchForm() {
        $query = 'select * from re_user order by fio';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        $ret = '';
        if ($stmt) {
            $ret .= '<form method="post">';
            $ret .= '<select name="user_id" style="width: 200px;" onchange="this.form.submit()">';
            $ret .= '<option value="">' . Multilanguage::_('L_CHOOSE_USER') . '</option>';
            while ($ar = $DBC->fetch($stmt)) {
                if ($this->getRequestValue('user_id') == $ar['user_id']) {
                    $ret .= '<option value="' . $ar['user_id'] . '" selected="selected">' . $ar['login'] . ' (' . $ar['fio'] . ')</option>';
                } else {
                    $ret .= '<option value="' . $ar['user_id'] . '">' . $ar['login'] . ' (' . $ar['fio'] . ')</option>';
                }
            }
            $ret .= '</select>';
            $ret .= '<input type="hidden" name="action" value="' . $this->action . '">';
            $ret .= '<input type="submit" name="submit" value="' . Multilanguage::_('L_TEXT_SELECT') . '">';
            $ret .= '</form>';
        }
        return $ret;
    }

    function getUserSelectBox() {
        $query = 'select * from re_user order by fio';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        $ret = '';
        if ($stmt) {
            $ret .= '<select name="user_id" onchange="this.form.submit()">';
            $ret .= '<option value="">' . Multilanguage::_('L_CHOOSE_USER') . '</option>';
            while ($ar = $DBC->fetch($stmt)) {
                if ( isset($ar['hide_from_search']) and $ar['hide_from_search'] == 1 ) {

                } else {
                    if ($this->getRequestValue('user_id') == $ar['user_id']) {
                        $ret .= '<option value="' . $ar['user_id'] . '" selected="selected">' . $ar['login'] . ' (' . $ar['fio'] . ')</option>';
                    } else {
                        $ret .= '<option value="' . $ar['user_id'] . '">' . $ar['login'] . ' (' . $ar['fio'] . ')</option>';
                    }
                }
            }
            $ret .= '</select>';
        }

        return $ret;
    }

    private function getFilters() {
        $result = array();
        if ( class_exists('\Template\local\admin\data\GridFilters') ) {
            $LocalGridFilters = new \Template\local\admin\data\GridFilters();
            $result = $LocalGridFilters->getMethodsResult('filter');
        }
        return $result;
    }
}
