<?php
/**
 * AccountBillingTrait — billing, payments, and financial methods for Account class
 *
 * Extracted methods:
 *   - payself($bill_id)
 *   - get_pay_buttons_list($bill_id, $bill_sum, $bill_payment_sum, $is_recharge)
 *   - get_account_balance_buy_button($bill_id)
 *   - jumpToRobokassa($bill_id, $bill_sum)
 *   - get_amount_robokassa($bill_id)
 *   - get_bill_sum($bill_id)
 *   - get_order_title($bill_id, $bill_sum)
 *   - get_robokassa_button($bill_id, $bill_sum)
 *   - getPayMethodsList()
 *   - addBill($user_id, $sum, $bill_description, $payment_sum, $bill_direct)
 *   - addPay($user_id, $pay)
 *   - getBillForm()
 *   - getAccountValue($user_id)
 *   - doServicePay($user_id, $service_id)
 *   - getServiceCost($service_id)
 *   - minusMoney($user_id, $money)
 *   - plusMoney($user_id, $money)
 */
trait AccountBillingTrait
{
    function payself($bill_id)
    {
        $DBC = DBC::getInstance();
        $query = 'SELECT * FROM ' . DB_PREFIX . '_bill WHERE bill_id=? LIMIT 1';
        $stmt = $DBC->query($query, array($bill_id));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $user_id = $ar['user_id'];
            $bill_info = $ar;
        }
        if ($bill_info['status'] == 1) {
            return _e('Счет уже оплачен');
        }

        $account_status = $this->getAccountValue($user_id);
        if ($account_status < $bill_info['sum']) {
            return _e('На балансе недостаточно средств для оплаты счета');
        }


        $this->minusMoney($user_id, $bill_info['sum']);

        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/robokassa/robokassa.php';
        $Robox = new Robox();
        $Robox->activateBill($bill_id, '');

        return _e('Оплата со счета успешна');
    }

    function get_pay_buttons_list($bill_id, $bill_sum, $bill_payment_sum, $is_recharge = false)
    {
        $rs = _e('К оплате: ') . $this->get_bill_sum($bill_id) . ' ' . $this->getConfigValue('ue_name');
        if ($this->getConfigValue('apps.cryptonator.enable')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/cryptonator/admin/admin.php');
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/cryptonator/site/site.php');
            $cryptonator_site = new cryptonator_site();

            $rs .= $cryptonator_site->get_pay_button($bill_id, $bill_sum);
        }

        if ($this->getConfigValue('apps.yookassa.enable')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/yookassa/admin/admin.php');
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/yookassa/site/site.php');
            $yookassa_site = new yookassa_site();

            $rs .= $yookassa_site->get_pay_button($bill_id, $bill_sum);
        }

        if ($this->getConfigValue('apps.freekassa.enabled')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/freekassa/admin/admin.php');
            $freekassa = new freekassa_admin();

            $rs .= $freekassa->getPayForm($bill_id, $bill_sum);
        }

        if ($this->getConfigValue('apps.clickuz.enable')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/clickuz/admin/admin.php');
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/clickuz/site/site.php');
            $clickuz_site = new clickuz_site();

            $rs .= $clickuz_site->get_pay_button($bill_id, $bill_sum);
        }
        if ($this->getConfigValue('apps.interkassa.enable')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/interkassa/admin/admin.php');
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/interkassa/site/site.php');
            $iterkassa_site = new interkassa_site();

            $rs .= $iterkassa_site->get_pay_button($bill_id, $bill_sum);
        }
        if ($this->getConfigValue('apps.paypal.enable')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/paypal/admin/admin.php');
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/paypal/site/site.php');
            $paypal_site = new paypal_site();

            $rs .= $paypal_site->get_pay_button($bill_id, $bill_sum, $bill_payment_sum);
        }
        if ($this->getConfigValue('apps.portmanataz.enable')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/portmanataz/admin/admin.php');
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/portmanataz/site/site.php');
            $portmanataz_site = new portmanataz_site();
            $rs .= $portmanataz_site->get_pay_button($bill_id);
        }
        if ($this->getConfigValue('apps.woywouz.enable')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/woywouz/admin/admin.php');
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/woywouz/site/site.php');
            $woywouz_site = new woywouz_site();
            $rs .= $woywouz_site->get_pay_button($bill_id);
        }
        /* if ( $this->getConfigValue('apps.eccgimi.enable') ) {
          require_once (SITEBILL_DOCUMENT_ROOT.'/apps/eccgimi/admin/admin.php');
          require_once (SITEBILL_DOCUMENT_ROOT.'/apps/eccgimi/site/site.php');
          $eccgimi_site = new eccgimi_site();
          $rs .= $eccgimi_site->get_pay_button($bill_id);
          } */
        if ($this->getConfigValue('robokassa_pay_enable')) {
            $rs .= $this->get_robokassa_button($bill_id);
        }
        if ($this->getSessionUserId() > 0 && !$is_recharge && $bill_sum <= $this->getAccountValue($this->getSessionUserId())) {
            $rs .= $this->get_account_balance_buy_button($bill_id);
        }
        return $rs;
    }

    function get_account_balance_buy_button($bill_id)
    {
        $rs = '<a href="' . SITEBILL_MAIN_URL . '/account/balance/?do=payself&bill_id=' . $bill_id . '" class="btn btn-success">' . _e('Оплатить с баланса, на балансе сейчас: ') . $this->getAccountValue($this->getSessionUserId()) . ' ' . $this->getConfigValue('ue_name') . '</a>';
        return $rs;
    }

    /**
     * Jump to robokassa
     * @param
     * @return
     */
    function jumpToRobokassa($bill_id, $bill_sum = '')
    {
        //echo "bill_id = $bill_id, bill_sum = $bill_sum";


        if ($bill_sum == '') {
            $DBC = DBC::getInstance();
            $query = 'SELECT * FROM ' . DB_PREFIX . '_bill WHERE bill_id=? LIMIT 1';
            $stmt = $DBC->query($query, array($bill_id));
            if (!$stmt) {
                return '';
            }
            $bill_info = $DBC->fetch($stmt);
            $out_summ = $bill_info['payment_sum_robokassa'];
        } else {
            $out_summ = $bill_sum;
        }
        /*
          $mrh_login = $this->getConfigValue('robokassa_login');
          $mrh_pass1 = $this->getConfigValue('robokassa_password1');
          $inv_id = $bill_id;
          $crc  = md5("$mrh_login:$out_summ:$inv_id:$mrh_pass1");
         */
        $rs = sprintf(Multilanguage::_('YOU_HAVE_ORDER', 'system'), (string)$out_summ, $this->getConfigValue('ue_name')) . '<br>';
        require_once SITEBILL_DOCUMENT_ROOT . '//apps/system/lib/system/robokassa/robokassa.php';
        $Robox = new Robox();
        $rs .= $Robox->getRoboForm($bill_id);
        /*
          $rs .= "<form action=\"".$this->getConfigValue('robokassa_server')."\" method=\"POST\">";
          $rs .=
          "<input type=\"hidden\" name=\"MrchLogin\" value=\"$mrh_login\">".
          "<input type=\"hidden\" name=\"OutSum\" value=\"$out_summ\">".
          "<input type=\"hidden\" name=\"InvId\" value=\"$inv_id\">".
          "<input type=\"hidden\" name=\"SignatureValue\" value=\"$crc\">".
          "<input type=\"submit\" value=\"".Multilanguage::_('L_TEXT_PAY')."\">";
          $rs .= '</form>';
         */
        return $rs;
    }

    function get_amount_robokassa($bill_id)
    {
        $DBC = DBC::getInstance();
        $query = 'SELECT * FROM ' . DB_PREFIX . '_bill WHERE bill_id=? LIMIT 1';
        $stmt = $DBC->query($query, array($bill_id));
        if (!$stmt) {
            return '';
        }
        $bill_info = $DBC->fetch($stmt);
        return $bill_info['payment_sum_robokassa'];
    }

    function get_bill_sum($bill_id)
    {
        $DBC = DBC::getInstance();
        $query = 'SELECT * FROM ' . DB_PREFIX . '_bill WHERE bill_id=? LIMIT 1';
        $stmt = $DBC->query($query, array($bill_id));
        if (!$stmt) {
            return '';
        }
        $bill_info = $DBC->fetch($stmt);
        return $bill_info['sum'];
        /*
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/billing/admin/bill_object.php';
        $bill_object = new bill_object();
        $bill_info = $bill_object->load_by_id($bill_id);
        return $bill_info['sum']['value'];
         *
         */
    }

    function get_order_title($bill_id, $bill_sum = '')
    {
        if ($bill_sum == '') {
            $out_summ = $this->get_amount_robokassa($bill_id);
        } else {
            $out_summ = $bill_sum;
        }
        return sprintf(Multilanguage::_('YOU_HAVE_ORDER', 'system'), (string)$out_summ, $this->getConfigValue('ue_name')) . '<br>';
    }

    function get_robokassa_button($bill_id, $bill_sum = '')
    {
        require_once SITEBILL_DOCUMENT_ROOT . '//apps/system/lib/system/robokassa/robokassa.php';
        $Robox = new Robox();
        return $Robox->getRoboForm($bill_id);
    }

    /**
     * Get pay methods list
     * @param void
     * @return string
     */
    function getPayMethodsList()
    {

        return '';

        $rs .= '<select name="IncCurrLabel">';
        $rs .= '
	<optgroup label="' . Multilanguage::_('PAY_BY_ELMONEY', 'system') . '">
		<option value="YandexMerchantOceanR">Яндекс.Деньги</option> 
		<option value="WMRM" selected="selected">WMR</option> 
		<option value="WMZM">WMZ</option> 
		<option value="WMEM">WME</option> 
		<option value="WMUM">WMU</option> 
		<option value="WMBM">WMB</option> 
		<option value="MailRuR">Деньги@Mail.Ru</option> 
		<option value="EasyPayB">EasyPay</option> 
		<option value="QiwiR">QIWI Кошелек</option> 
		<option value="MoneyMailR">MoneyMail</option> 
		<option value="RuPayR">RUR RBK Money</option> 
		<option value="TeleMoneyR">RUR TeleMoney</option> 
		<option value="WebCredsR">RUR WebCreds</option> 
		<option value="ZPaymentR">RUR Z-Payment</option> 
		<option value="VKontakteMerchantR">RUR ВКонтакте</option> 
		<option value="W1R">RUR Единый Кошелек</option> 
	</optgroup>
	<optgroup label="' . Multilanguage::_('PAY_BY_CELL', 'system') . '">
		<option value="MtsR">МТС</option>
		<option value="MPBeelineR">Билайн</option>
	</optgroup>
	<optgroup label="' . Multilanguage::_('PAY_BY_BANKCARD', 'system') . '">
		<option value="BANKOCEAN2R">Банковской картой</option>
	</optgroup>
	<optgroup label="' . Multilanguage::_('PAY_BY_PLATEZH', 'system') . '">
		<option value="OceanBankR">RUR Океан Банк</option>
	</optgroup>
	<optgroup label="' . Multilanguage::_('PAY_BY_IBOX', 'system') . '">
		<option value="TerminalsAbsolutplatR">Absolutplat</option>
		<option value="TerminalsPinpayR">Pinpay</option>
		<option value="QiwiR">QIWI</option>
		<option value="TerminalsComepayR">Кампэй</option>
		<option value="TerminalsMElementR">Мобил Элемент</option>
		<option value="TerminalsNovoplatR">Новоплат</option>
		<option value="TerminalsUnikassaR">Уникасса</option>
		<option value="ElecsnetR">Элекснет</option>
	</optgroup>
	<optgroup label="' . Multilanguage::_('PAY_BY_CONTACT', 'system') . '">
		<option value="ContactR">RUR Contact</option>
	</optgroup>
	<optgroup label="' . Multilanguage::_('PAY_BY_ATM', 'system') . '">
		<option value="VTB24R">RUR ВТБ24</option>
		<option value="TerminalsPkbR">Петрокоммерц</option>
	</optgroup>
	<optgroup label="' . Multilanguage::_('PAY_BY_EURONET', 'system') . '">
		<option value="RapidaInR">RUR Евросеть</option>
	</optgroup>
	<optgroup label="' . Multilanguage::_('PAY_BY_INETBANK', 'system') . '">
		<option value="AlfaBankR">Альфа-Клик</option>
	</optgroup>
        ';
        $rs .= '</select>';
        $rs .= '<br>';
        return $rs;
    }

    /**
     * Add bill
     * @param int $user_id user ID
     * @param string $sum sum
     * @return boolean
     */
    function addBill($user_id, $sum, $bill_description = '', $payment_sum = '', $bill_direct = '')
    {
        $time = time();
        $ip = defined('HTTP_X_FORWARDED_FOR') ? getenv(HTTP_X_FORWARDED_FOR): '';
        if ($ip == '') {
            $ip = ($_SERVER['REMOTE_ADDR']?$_SERVER['REMOTE_ADDR']:'');
        }
        if ($payment_sum == '') {
            $payment_sum = $sum;
        }
        if ($bill_direct == '') {
            $bill_direct = 1;
        }
        if ('' != $this->getConfigValue('robokassa_koef')) {
            $k = $this->getConfigValue('robokassa_koef');
        } else {
            $k = 1;
        }
        $bill_payment_sum_robo = $sum * $k;
        $query = 'INSERT INTO ' . DB_PREFIX . '_bill (user_id, sum, date, status, description, http_x_real_ip, payment_sum, payment_sum_robokassa) values (?, ?, ?, 0, ?, ?, ?, ?)';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query, array($user_id, $sum, $time, $bill_description, $ip, $payment_sum, $bill_payment_sum_robo));
        if ($stmt) {
            return $DBC->lastInsertId();
        } else {
            $this->writeLog($DBC->getLastError());
        }
    }

    /**
     * Add pay
     * @param int $user_id user ID
     * @param string $pay pay
     * @return boolean
     */
    function addPay($user_id, $pay)
    {
        $account_value = $this->getAccountValue($user_id);
        $account_value += $pay;
        $query = 'UPDATE `' . DB_PREFIX . '_user` SET `account`=? WHERE `user_id`=?';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query, array($account_value, $user_id));
        return true;
    }

    /**
     * Get bill form
     * @param void
     * @return string
     */
    function getBillForm()
    {

        $resp = array();

        $resp['action'] = SITEBILL_MAIN_URL . '/account/balance/?do=add_bill_done';
        $resp['method'] = 'post';
        $resp['msg'] = sprintf(Multilanguage::_('INPUT_PAYMENT_SUM', 'system'), $this->getConfigValue('ue_name'));
        $resp['error'] = '';
        if ($this->getError()) {
            $resp['error'] = $this->GetErrorMessage();
        }
        $resp['pretext'] = Multilanguage::_('PAYMENT_SUM', 'system');
        $resp['field_name'] = 'bill';
        $resp['field_value'] = $this->getRequestValue('bill');
        $resp['hidden_fileds'][] = '<input type="hidden" name="do" value="add_bill_done">';
        $resp['submit_name'] = Multilanguage::_('L_TEXT_NEXT');

        $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/account_balance_add.tpl';
        //var_dump(file_exists($tpl));
        if (file_exists($tpl)) {
            global $smarty;
            $smarty->assign('account_balance_add_data', $resp);
            $rs = $smarty->fetch($tpl);
        } else {
            $rs = '';
            $rs .= '<form action="' . $resp['action'] . '" method="' . $resp['method'] . '">';
            $rs .= '<p>' . $resp['msg'] . '</p>';
            if ($this->getError()) {
                $rs .= '<p><span class="error">' . $resp['error'] . '</span></p>';
            }
            $rs .= $resp['pretext'] . ': <input type="text" name="' . $resp['field_name'] . '" value="' . $resp['field_value'] . '">';
            $rs .= '<input type="hidden" name="do" value="add_bill_done">';
            $rs .= '<input type="submit" value="' . Multilanguage::_('L_TEXT_NEXT') . '">';
            $rs .= '</form>';
        }


        return $rs;
    }

    /**
     * Get account value
     * @param int $user_id user ID
     * @return string
     */
    function getAccountValue($user_id)
    {
        $query = 'SELECT `account` FROM ' . DB_PREFIX . '_user WHERE `user_id`=?';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query, array($user_id));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            return $ar['account'];
        }
        return 0;
    }

    /**
     * Do service pay for user
     * @param int $user_id user ID
     * @param int $service_id service ID
     * @return string
     */
    function doServicePay($user_id, $service_id)
    {
        if ($this->getDebugMode()) {
            echo "Account->doServicePay(user_id = $user_id, service_id = $service_id)<br>";
        }
        //Get service cost
        $service_cost = $this->getServiceCost($service_id);
        //Minus service cost from user account status
        $this->minusMoney($user_id, $service_cost);
    }

    /**
     * Get service cost
     * @param int $service_id service ID
     * @return int
     */
    function getServiceCost($service_id)
    {
        $query = 'SELECT `cost` FROM ' . DB_PREFIX . '_service WHERE `service_id` = ?';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query, array($service_id));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($this->getDebugMode()) {
                echo "Account->getServiceCost(service_id = $service_id, cost = " . $ar['cost'] . ")<br>";
            }
            return $ar['cost'];
        }
    }

    /**
     * Minus money
     * @param int $user_id user ID
     * @param int $money money
     * @return boolean
     */
    function minusMoney($user_id, $money)
    {
        if ($this->getDebugMode()) {
            //echo "Account->minusMoney(user_id = $user_id, money = $money)<br>";
        }
        $account_status = $this->getAccountValue($user_id);
        $account_status = $account_status - $money;
        $query = 'UPDATE ' . DB_PREFIX . '_user SET `account` = ? WHERE `user_id` = ?';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query, array($account_status, $user_id));
    }

    /**
     * Plus money
     * @param int $user_id user ID
     * @param int $money money
     * @return boolean
     */
    function plusMoney($user_id, $money)
    {
        if ($this->getDebugMode()) {
            //echo "Account->minusMoney(user_id = $user_id, money = $money)<br>";
        }
        //get previous account value
        $account_status = $this->getAccountValue($user_id);
        $account_status = $account_status + $money;
        $query = "update " . DB_PREFIX . "_user set account = $account_status where user_id = $user_id";
        $this->db->exec($query);
    }
}
