<?php
/**
 * AccountMenuTrait — menu and navigation methods for Account class
 *
 * Extracted methods:
 *   - getHome()
 *   - getLockScreen($user_id)
 *   - getTopMenu()
 *   - getDeveloperMenu($user_id)
 *   - getMainMenu($user_id)
 *   - get_user_data_count($user_id)
 *   - get_company_profile($user_id)
 */
trait AccountMenuTrait
{
    /**
     * Return company profile data
     * @param int $user_id
     * @return array
     */
    function get_company_profile($user_id)
    {
        if ($this->getConfigValue('apps.company.enable')) {
            //get company ID
            $query = 'SELECT * FROM ' . DB_PREFIX . '_user WHERE user_id=?';
            $DBC = DBC::getInstance();
            $stmt = $DBC->query($query, array($user_id));
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                if (isset($ar['company_id'])) {
                    $company_id = $ar['company_id'];
                } else {
                    $company_id = 0;
                }
            }

            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/company/admin/admin.php');
            $company_admin = new company_admin();
            return $company_admin->load_by_id($company_id);
        }
        return false;
    }

    /**
     * Get home
     * @param void
     * @return string
     */
    function getHome()
    {
        //print_r($_SESSION);
        //echo 'user_id = '.$_SESSION['user_id'];
        //$this->getSessionUserId();
        if (!$this->getSessionUserId()) {
            $rs = $this->login_main();
            if ($this->getError()) {
                return $rs;
            }
        }

        $rs = '<h1>' . Multilanguage::_('PRIVATE_ACCOUNT', 'system') . '</h1>';

        $rs .= '<ul>';
        $rs .= '<li><a href="' . SITEBILL_MAIN_URL . '/account/profile">' . Multilanguage::_('PROFILE', 'system') . '</a></li>';
        $rs .= '<li><a href="' . SITEBILL_MAIN_URL . '/account/data">' . Multilanguage::_('MY_ADS', 'system') . '</a></li>';
        $rs .= '</ul>';
        return $rs;
    }

    /**
     * Get lock screen
     * @param int $user_id user ID
     * @return string
     */
    function getLockScreen($user_id)
    {
        $rs = sprinf(Multilanguage::_('RECHARGE_FOR_ACCESS', 'system'), $user_id) . '<br>
        <a href="' . SITEBILL_MAIN_URL . '/account/">' . Multilanguage::_('RECHARGE_LC', 'system') . '</a>';
        return $rs;
    }

    function get_user_data_count($user_id)
    {
        $query = 'SELECT COUNT(id) AS total FROM ' . DB_PREFIX . '_data WHERE user_id=?';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query, array($user_id));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            return $ar['total'];
        }
        return 0;
    }

    /**
     * Get top menu
     * @param void
     * @return string
     */
    function getTopMenu()
    {
        $rs = '<br><a href="' . SITEBILL_MAIN_URL . '/account/balance/?do=add_bill">' . _e('Пополнить счет') . '</a><br>';
        if ($this->getConfigValue('advert_cost') > 0) {
            $rs .= '<br><i>* ' . sprintf(Multilanguage::_('AD_PLACEMENT_COST', 'system'), $this->getConfigValue('advert_cost'), $this->getConfigValue('ue_name')) . '</i>';
        }

        return $rs;
    }

    /**
     * Get menu for developers
     * @param int $user_id user ID
     * @return string
     */
    function getDeveloperMenu($user_id)
    {
        $rs = '';
        $rs .= '<a href="task" class="top_menu">' . Multilanguage::_('MY_TASKS', 'system') . '</a> ';
        $rs .= '<a href="profile" class="top_menu">' . Multilanguage::_('PROFILE', 'system') . '</a> ';

        if ($user_id > 1) {
            /*
              $rs .= '
              <a href="/service" class="top_menu">Услуги</a> |
              <a href="/account" class="top_menu">Счет</a> |
              <a href="/profile" class="top_menu">Личные данные</a>
              ';
             */
        } else {
            $rs = '&nbsp;';
        }
        return $rs;
    }

    /**
     * Get main menu
     * @param int $user_id user ID
     * @return string
     */
    function getMainMenu($user_id)
    {
        $rs = '';
        if ($this->getAccessDefined('project_manager', 'view_list')) {
            $rs .= ' <li><a href="/project" class="mainlevel-son-of-suckerfish-horizontal"><span>Проекты</span></a>';
            if ($this->getAccessDefined('archive_manager', 'view_list')) {
                $rs .= '<ul id="menulist_10-son-of-suckerfish-horizontal">';
                $rs .= '<li class="submenu_top"></li>';
                $rs .= ' <li><a href="project" class="sublevel-son-of-suckerfish-horizontal"><span>Список проектов</span></a></li> ';
                $rs .= ' <li><a href="archive" class="sublevel-son-of-suckerfish-horizontal"><span>Архив</span></a></li> ';
                $rs .= '<li class="submenu_bottom"></li>';
                $rs .= '</ul>';
            }
            $rs .= '</li>';
        }
        if ($this->getAccessDefined('task_manager', 'view_list')) {
            $rs .= ' <li><a href="task" class="mainlevel-son-of-suckerfish-horizontal"><span>Задачи</span></a></li> ';
        }


        if ($this->getAccessDefined('bookkeeper', 'view_list')) {
            $rs .= ' <li><a href="bookkeeper" class="mainlevel-son-of-suckerfish-horizontal"><span>Бухгалтерия</span></a> ';

            if ($this->getAccessDefined('bookkeeper', 'cash_flow')) {
                $rs .= '<ul id="menulist_10-son-of-suckerfish-horizontal">';
                $rs .= '<li class="submenu_top"></li>';
                $rs .= ' <li><a href="bookkeeper/" class="sublevel-son-of-suckerfish-horizontal"><span>Состояния ЛС</span></a></li> ';
                $rs .= ' <li><a href="bookkeeper/cash_flow" class="sublevel-son-of-suckerfish-horizontal"><span>Движение средств</span></a></li> ';
                if ($this->getAccessDefined('bookkeeper', 'product')) {
                    $rs .= ' <li><a href="bookkeeper/product" class="sublevel-son-of-suckerfish-horizontal"><span>Продукты</span></a></li> ';
                }

                $rs .= '<li class="submenu_bottom"></li>';
                $rs .= '</ul>';
            }

            $rs .= '</li>';
        }


        if ($this->getAccessDefined('money', 'cash_flow')) {
            $rs .= ' <li><a href="money" class="mainlevel-son-of-suckerfish-horizontal"><span>Деньги</span></a></li> ';
        }

        if ($this->getAccessDefined('com_service_admin', 'view_list')) {
            $rs .= ' <li><a href="serviceadmin" class="mainlevel-son-of-suckerfish-horizontal"><span>Управление услугами</span></a></li> ';
        }

        if ($this->getAccessDefined('dialog', 'view_list')) {
            $rs .= ' <li><a href="dialog/" class="mainlevel-son-of-suckerfish-horizontal"><span>Редактор диалогов</span></a></li> ';
        }

        $rs .= '<li><a href="profile" class="mainlevel-son-of-suckerfish-horizontal"><span>Мой профиль</span></a></li> ';

        if ($user_id > 1) {
            /*
              $rs .= '
              <a href="/service" class="top_menu">Услуги</a> |
              <a href="/account" class="top_menu">Счет</a> |
              <a href="/profile" class="top_menu">Личные данные</a>
              ';
             */
        } else {
            $rs = '&nbsp;';
        }
        $rs .= '<li><a href="doc/" class="mainlevel-son-of-suckerfish-horizontal"><span>Документация</span></a></li> ';

        return $rs;
    }
}
