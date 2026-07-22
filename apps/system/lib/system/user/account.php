<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * Account class
 * @author Kondin Dmitry
 */
require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/login.php');

require_once __DIR__ . '/traits/AccountBillingTrait.php';
require_once __DIR__ . '/traits/AccountMenuTrait.php';

class Account extends Login
{
    use AccountBillingTrait;
    use AccountMenuTrait;

    var $user_id = 0; // Default value of the user ID

    /**
     * Constructor
     */

    function Account()
    {
        //set debug mode using conf value
        if (isset($config)) {
            $this->setDebugMode($config->debug_mode);
        }
        if ($this->getDebugMode()) {
            //echo 'account constructor<br>';
        }
        $this->Login();
        $this->user_id = $this->getSessionUserId();
        /* if($this->USER_isUserAuthorized()){
          $this->user_id = $this->USER_getUserId();
          } */
        //$this->initService();
        parent::__construct();
        //$this->setTableName('service');
        //$this->setPrimaryKey('service_id');
    }

    /**
     * Get user menu
     * @param void
     * @return int
     */
    function get_user_id()
    {
        return $this->user_id;
    }

    function login_main()
    {
        $login = $this->getRequestValue('login');
        $password = $this->getRequestValue('password');
        $this->checkLogin($login, $password);
        //echo "error_message = ".$this->error_message."<br>";
        if ($this->GetError()) {
            $rs = $this->loginForm();
        }
        return $rs;
    }

    /**
     * Main
     * @param void
     * @return string
     */
    function main()
    {
        $rs = '';


        if ($this->user_id == 0) {
            $rs = $this->login_main();
            if ($this->getError()) {
                return $rs;
            }
        }
        $do = $this->getRequestValue('do');


        switch ($do) {
            case 'add_bill_done':
                $bill_sum = $this->getRequestValue('bill');
                $bill_sum = preg_replace('/[^0-9\.,]/', '', $bill_sum);
                $rs .= $this->get_order_title($bill_id, $bill_sum);

                if ($this->getConfigValue('min_payment_sum') > 0 and $bill_sum < $this->getConfigValue('min_payment_sum')) {
                    $this->riseError(sprintf(Multilanguage::_('SUM_MUST_BE_MORE', 'system'), $this->getConfigValue('min_payment_sum')));
                    $rs .= $this->getBillForm();
                    return $rs;
                }

                if (!isset($bill_sum) or $bill_sum == '') {
                    $this->riseError(sprintf(Multilanguage::_('SUM_MUST_BE_MORE', 'system'), '0'));
                    $rs .= $this->getBillForm();
                    return $rs;
                }


                $bill_name = 'Пополнение счета на ' . $bill_sum;
                if ($this->getConfigValue('apps.paypal.enable')) {
                    $bill_payment_sum = number_format($bill_sum / $this->getConfigValue('apps.paypal.usd_coef'), 2);
                } else {
                    $bill_payment_sum = $bill_sum;
                }

                $bill_id = $this->addBill($this->getSessionUserId(), $bill_sum, $bill_name, $bill_payment_sum, 1);
                if ($this->getError()) {
                    return $this->getBillForm();
                }
                $rs .= $this->get_pay_buttons_list($bill_id, $bill_sum, $bill_payment_sum, true);

                return $rs;
                break;
            case 'payself':
                $rs .= $this->payself($this->getRequestValue('bill_id'));
                return $rs;
                break;
            case 'add_bill':
                $this->template->assert('title', 'Пополнение баланса');
                $rs .= $this->getBillForm();
                return $rs;
                break;

            default:
            {
                $resp = array();

                $resp['value'] = floatval($this->getAccountValue($this->getSessionUserId()));
                $resp['currency'] = $this->getConfigValue('ue_name');
                $resp['msg'] = sprintf(Multilanguage::_('ACCOUNT_STATE', 'system'), floatval($this->getAccountValue($this->getSessionUserId()))) . ' ' . $this->getConfigValue('ue_name');
                $resp['recharge_href'] = SITEBILL_MAIN_URL . '/account/balance/?do=add_bill';
                $resp['recharge_link'] = '<a href="' . SITEBILL_MAIN_URL . '/account/balance/?do=add_bill">Пополнить счет</a>';


                $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/account_balance_info.tpl';
                //var_dump(file_exists($tpl));
                if (file_exists($tpl)) {
                    global $smarty;
                    $smarty->assign('account_balance_data', $resp);
                    $rs = $smarty->fetch($tpl);
                } else {
                    $rs .= sprintf(Multilanguage::_('ACCOUNT_STATE', 'system'), $this->getAccountValue($this->getSessionUserId())) . ' ' . $this->getConfigValue('ue_name');
                    $rs .= $this->getTopMenu();
                }

                //$this->template->assert
                $this->template->assert('title', 'Баланс');
            }
        }


        return $rs;
    }

}
