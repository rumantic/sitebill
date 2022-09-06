<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Robokassa class
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class Robox extends SiteBill
{

    /**
     * Constructor
     */
    function Robox()
    {
        parent::__construct();
    }

    /**
     * Main
     */
    function main()
    {
        /* if(preg_match('/robotestpay\/(\d+)/', $_SERVER['REQUEST_URI'], $match)){
          $this->activateBill($match[1], 10);
          exit();
          } */
        if (preg_match('/result/', $_SERVER['REQUEST_URI'])) {

            if (1 == intval($this->getConfigValue('apps.freekassa.enabled')) && 1 == intval($this->getConfigValue('apps.freekassa.overrobo'))) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/freekassa/admin/admin.php';
                $FK = new freekassa_admin();
                $merchant_id = $this->getRequestValue('MERCHANT_ID');
                $amount = $this->getRequestValue('AMOUNT');
                $inv_id = intval($this->getRequestValue('MERCHANT_ORDER_ID'));


                if ($FK->checkPaymentInfo($inv_id, $amount, $merchant_id)) {
                    $this->activateBill($inv_id, $amount);
                    $rs = Multilanguage::_('PAYMENT_SUCCESS', 'system');
                    $this->writeLog(array('apps_name' => 'freekassa', 'method' => __METHOD__, 'message' => "OK" . $inv_id . 'sum = ' . $amount, 'type' => NOTICE));
                    echo "YES";
                    if ($this->getConfigValue('notify_about_payment')) {
                        $subject = $_SERVER['SERVER_NAME'] . ': Выполнен платеж на сумму ' . $amount;
                        $to = ($this->getConfigValue('add_notification_email') != '' ? $this->getConfigValue('add_notification_email') : $this->getConfigValue('order_email_acceptor'));
                        $from = $this->getConfigValue('system_email');
                        $body = 'Идентификатор платежа ' . $inv_id;
                        $this->sendFirmMail($to, $from, $subject, $body);
                    }
                } else {
                    echo $FK->GetErrorMessage();
                }
                exit();
            } elseif ($this->checkBillInfo($this->getRequestValue('InvId'))) {
                $inv_id = intval($this->getRequestValue('InvId'));
                $this->activateBill($this->getRequestValue('InvId'), $this->getRequestValue('OutSum'));
                $rs = Multilanguage::_('PAYMENT_SUCCESS', 'system');
                $this->writeLog(array('apps_name' => 'robokassa_system', 'method' => __METHOD__, 'message' => "OK" . $this->getRequestValue('InvId') . 'sum = ' . $this->getRequestValue('OutSum'), 'type' => NOTICE));

                echo "OK" . $this->getRequestValue('InvId') . "\n";
                if ($this->getConfigValue('notify_about_payment')) {
                    $subject = $_SERVER['SERVER_NAME'] . ': Выполнен платеж на сумму ' . $this->getRequestValue('OutSum');
                    $to = ($this->getConfigValue('add_notification_email') != '' ? $this->getConfigValue('add_notification_email') : $this->getConfigValue('order_email_acceptor'));
                    $from = $this->getConfigValue('system_email');
                    $body = 'Идентификатор платежа ' . $this->getRequestValue('InvId');
                    $this->sendFirmMail($to, $from, $subject, $body);
                }
                exit;
            } else {
                $rs = $this->GetErrorMessage();
            }
        } elseif (preg_match('/success/', $_SERVER['REQUEST_URI'])) {
            if (1 == intval($this->getConfigValue('robokassa_by_frekassa'))) {
                $this->writeLog(array('apps_name' => 'robokassa_system', 'method' => __METHOD__, 'message' => "success " . $this->getRequestValue('OutSum'), 'type' => NOTICE));
                $DBC = DBC::getInstance();
                $query = 'SELECT * FROM ' . DB_PREFIX . '_bill WHERE bill_id=?';
                $stmt = $DBC->query($query, array(intval($this->getRequestValue('MERCHANT_ORDER_ID'))));
                if ($stmt) {
                    $bill_info = $DBC->fetch($stmt);
                    $out_summ = $bill_info['payment_sum_robokassa'];
                    if (floatval($bill_info['payment_sum_robokassa']) == 0) {
                        $out_summ = $bill_info['sum'];
                    }
                    $rs = sprintf(Multilanguage::_('PAYMEN_ON_SUM_SUCCESS', 'system'), $out_summ . ' руб.') . "<br><br>";
                    $rs .= sprintf(Multilanguage::_('YOU_ACCOUNT_SUM', 'system'), $this->getAccountValue($_SESSION['user_id']) . ' руб.') . '<br>';
                    $rs .= '<div style="color: green;" align="center"><br><a href="' . SITEBILL_MAIN_URL . '/account/data/?do=new">' . Multilanguage::_('ADD_AD', 'system') . '</a></div>';
                }
            } else {
                $this->writeLog(array('apps_name' => 'robokassa_system', 'method' => __METHOD__, 'message' => "success " . $this->getRequestValue('OutSum'), 'type' => NOTICE));

                $rs = sprintf(Multilanguage::_('PAYMEN_ON_SUM_SUCCESS', 'system'), $this->getRequestValue('OutSum') . ' руб.') . "<br><br>";
                $rs .= sprintf(Multilanguage::_('YOU_ACCOUNT_SUM', 'system'), $this->getAccountValue($_SESSION['user_id']) . ' руб.') . '<br>';
                $rs .= '<div style="color: green;" align="center"><br>
        		<a href="' . SITEBILL_MAIN_URL . '/account/data/?do=new">' . Multilanguage::_('ADD_AD', 'system') . '</a></div>';
            }
            $sum = $this->getRequestValue('OutSum');

            $bill_id = $this->get_bill_id_from_request();
            if ( $bill_id ) {
                $bill_array = $this->load_bill_by_id($bill_id);
                if ( $bill_array and $bill_array->sum ) {
                    $sum = $bill_array->sum;
                }

            }

            $success_template_response = $this->get_template_success_response(
                $sum,
                $this->getAccountValue($_SESSION['user_id'])
            );

            if ($success_template_response) {
                return $success_template_response;
            }
        } else {
            $rs = Multilanguage::_('PAYMENT_ERROR', 'system') . "</a>";
        }
        return '<div id="bigger">' . $rs . '</div>';
    }

    function get_bill_id_from_request () {
        if ( $this->request()->get('bill_id') != '' ) {
            return $this->request()->get('bill_id');
        } elseif ( $this->request()->get('MERCHANT_ORDER_ID') != '' ) {
            return $this->request()->get('MERCHANT_ORDER_ID');
        }
        return false;
    }

    function load_bill_by_id ( $bill_id ) {
        $query_result = Capsule::table('bill')
            ->where('bill_id', '=', $bill_id)
            ->first();
        if ( $query_result->bill_id > 0 ) {
            return $query_result;
        }
        return false;
    }

    function get_template_success_response($sum, $balance)
    {
        $template_file = SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/robokassa/success_pay.tpl';
        $local_template_file = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/success_pay.tpl';
        if (file_exists($local_template_file)) {
            $template_file = $local_template_file;
        }

        $this->template->assign('accessor_enable', $this->getConfigValue('apps.accessorc.enable'));
        $this->template->assign('currency', $this->getConfigValue('ue_name'));
        $this->template->assign('sum', round($sum, 2));
        $this->template->assign('balance', $balance);
        return $this->template->fetch($template_file);
    }

    /**
     * Кнопка оплаты для второй версии счетов. Счета хранятся в таблице invoices
     * @param type $invoice_id
     * @return string
     */
    function get_invoice_pay_button($invoice_id)
    {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/invoices/admin/admin.php');
        $invoices_admin = new invoices_admin();
        $invoice_info = $invoices_admin->load_by_id($invoice_id);
        if (!$invoice_info) {
            return 'Счет не найден';
        }

        $payment_amount = $invoice_info['total']['value'];

        return $this->robokassa_pay_button_generator($invoice_id, number_format($payment_amount, 2, '.', ''));
    }


    public function getRoboForm($bill_id, $bill_sum = '')
    {
        if (1 == intval($this->getConfigValue('apps.freekassa.enabled')) && 1 == intval($this->getConfigValue('apps.freekassa.overrobo'))) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/freekassa/admin/admin.php';
            $FK = new freekassa_admin();
            return $FK->getPayForm($bill_id, $bill_sum);
        }

        if ($bill_sum == '') {
            $DBC = DBC::getInstance();
            $query = 'SELECT * FROM ' . DB_PREFIX . '_bill WHERE bill_id=? AND `status`=0 LIMIT 1';
            $stmt = $DBC->query($query, array($bill_id));
            if (!$stmt) {
                return '';
            }
            $bill_info = $DBC->fetch($stmt);
            $out_summ = $bill_info['payment_sum_robokassa'];
            if (floatval($bill_info['payment_sum_robokassa']) == 0) {
                $out_summ = $bill_info['sum'];
            }
        } else {
            $out_summ = $bill_sum;
        }


        if (1 == intval($this->getConfigValue('robokassa_by_frekassa'))) {
            if (preg_match('/\.(00)$/', $out_summ)) {
                $out_summ = preg_replace('/(\.00)$/', '', $out_summ);
            } elseif (preg_match('/\.[1-9]0$/', $out_summ)) {
                $out_summ = preg_replace('/(0)$/', '', $out_summ);
            }
            $rs = '<form action="' . $this->getConfigValue('robokassa_server') . '" method="GET">';
            $mrh_login = $this->getConfigValue('robokassa_login');
            $mrh_pass1 = $this->getConfigValue('robokassa_password1');
            $inv_id = $bill_id;
            $crc = md5($mrh_login . ':' . $out_summ . ':' . $inv_id . ':' . $mrh_pass1);
            //$crc  = md5($mrh_login.':'.$out_summ.':'.$mrh_pass1.':'.$inv_id);

            $rs .= '<input type="hidden" name="MrchLogin" value="' . $mrh_login . '">';
            $rs .= '<input type="hidden" name="OutSum" value="' . $out_summ . '">';
            $rs .= '<input type="hidden" name="InvId" value="' . $inv_id . '">';
            $rs .= '<input type="hidden" name="SignatureValue" value="' . $crc . '">';
            $rs .= '<input type="submit" value="' . _e('Оплатить с помощью Robokassa') . '">';

            $rs .= '</form>';
            $mrh_pass2 = $this->getConfigValue('robokassa_password2');
            $my_crc = md5($out_summ . ':' . $inv_id . ':' . $mrh_pass2);

            //$rs .='http://'.$_SERVER['HTTP_HOST'].'/robox/result/?OutSum='.$out_summ.'&InvId='.$inv_id.'&SignatureValue='.$my_crc;


            return $rs;
        } else {
            //$rs .= '<a href="'.SITEBILL_MAIN_URL.'/robox/resulttest/'.$bill_id.'">Test Pay</a>';
            return $this->robokassa_pay_button_generator($bill_id, $out_summ);
        }
    }

    function robokassa_pay_button_generator($bill_id, $out_summ)
    {
        $test_mode = intval($this->getConfigValue('robokassa_testmode'));
        $rs = '<form action="' . $this->getConfigValue('robokassa_server') . '" method="POST">';
        if ($test_mode == 1 && '' != trim($this->getConfigValue('robokassa_testpassword1'))) {
            $mrh_login = $this->getConfigValue('robokassa_login');
            $mrh_pass1 = $this->getConfigValue('robokassa_testpassword1');
            $inv_id = $bill_id;
            $crc = md5($mrh_login . ':' . $out_summ . ':' . $inv_id . ':' . $mrh_pass1);

            $rs .= '<input type="hidden" name="IsTest" value="1">';
            $rs .= '<input type="hidden" name="MrchLogin" value="' . $mrh_login . '">';
            $rs .= '<input type="hidden" name="OutSum" value="' . $out_summ . '">';
            $rs .= '<input type="hidden" name="InvId" value="' . $inv_id . '">';
            $rs .= '<input type="hidden" name="SignatureValue" value="' . $crc . '">';
            $rs .= '<input type="submit" value="' . _e('Оплатить с помощью Robokassa') . '">';
        } elseif ($test_mode == 1 && '' == trim($this->getConfigValue('robokassa_testpassword1'))) {

        } else {
            $mrh_login = $this->getConfigValue('robokassa_login');
            $mrh_pass1 = $this->getConfigValue('robokassa_password1');
            $inv_id = $bill_id;
            $crc = md5($mrh_login . ':' . $out_summ . ':' . $inv_id . ':' . $mrh_pass1);

            $rs .= '<input type="hidden" name="MrchLogin" value="' . $mrh_login . '">';
            $rs .= '<input type="hidden" name="OutSum" value="' . $out_summ . '">';
            $rs .= '<input type="hidden" name="InvId" value="' . $inv_id . '">';
            $rs .= '<input type="hidden" name="SignatureValue" value="' . $crc . '">';
            $rs .= '<input type="submit" value="' . _e('Оплатить с помощью Robokassa') . '">';
        }
        $rs .= '</form>';
        return $rs;
    }

    /**
     * Get shop order
     * @param int $bill_id bill id
     * @return mixed
     */
    function getShopOrder($bill_id)
    {
        $query = 'SELECT so.code FROM shop_order so, bill b WHERE so.bill_id=b.bill_id AND b.bill_id=?';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query, array($bill_id));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($ar['code'] != '') {
                return $ar['code'];
            }
        }
        return false;
    }

    /**
     * Get account value
     * @param int $user_id
     * @return int
     */
    function getAccountValue($user_id)
    {
        $account = 0;
        $DBC = DBC::getInstance();
        $query = 'SELECT account FROM ' . DB_PREFIX . '_user WHERE user_id=? LIMIT 1';
        $stmt = $DBC->query($query, array((int)$user_id));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $account = $ar['account'];
        }
        return $account;
    }

    function closeBillAsPayed($bill_id)
    {
        $DBC = DBC::getInstance();
        $query = 'UPDATE ' . DB_PREFIX . '_bill SET status=1 WHERE bill_id=?';
        $stmt = $DBC->query($query, array($bill_id));
        if ($stmt) {
            return true;
        }
        return false;
    }

    function setPaymentParam ( $bill_id, $key, $value ) {
        $payment_params = $this->getPaymentParamsArray($bill_id);
        $payment_params[$key] = $value;
        $this->setPaymentParamsArray($bill_id, $payment_params);
    }

    function getPaymentParam ( $bill_id, $key ) {
        $payment_params = $this->getPaymentParamsArray($bill_id);
        if ( isset($payment_params[$key]) ) {
            return $payment_params[$key];
        }
        return false;
    }
    function setPaymentParamsArray ( $bill_id, $payment_params ) {
        $query_result = Capsule::table('bill')
            ->where('bill_id', '=', $bill_id )
            ->update(['payment_params' => serialize($payment_params)]);
    }

    function getPaymentParamsArray ( $bill_id) {
        $query_result = Capsule::table('bill')
            ->selectRaw(
                'payment_params'
            )
            ->where('bill_id', '=', $bill_id)
            ->first();
        if ($query_result->payment_params != '') {
            $payment_params = @unserialize($query_result->payment_params);
            if ( count($payment_params) > 0 ) {
                return $payment_params;
            }
        }
        return array();
    }


    /**
     * Activate bill
     * @param int $bill_id bill id
     * @param string $OutSum OutSum
     * @return boolean
     */
    function activateBill($bill_id, $OutSum)
    {
        $this->writeLog('Активация счета #'.$bill_id);

        $user_id = 0;

        //Сначала проверим есть ли такой invoice_id в новой таблице invoices
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/invoices/admin/admin.php')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/invoices/admin/admin.php');
            $invoices_admin = new invoices_admin();
            $invoice_info = $invoices_admin->load_by_id($bill_id);
            if (!$invoice_info) {
            } else {
                return $invoices_admin->pay_invoice($bill_id);
            }
        }

        $DBC = DBC::getInstance();
        $query = 'SELECT * FROM ' . DB_PREFIX . '_bill WHERE bill_id=? LIMIT 1';
        $stmt = $DBC->query($query, array($bill_id));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $user_id = $ar['user_id'];
            $bill_info = $ar;
        }

        $payment_type = 'recharge';

        if (isset($bill_info['payment_type']) && $bill_info['payment_type'] != '') {
            $payment_type = $bill_info['payment_type'];
        }

        if (isset($bill_info['payment_params']) && $bill_info['payment_params'] != '') {
            $payment_params = unserialize($bill_info['payment_params']);
        }

        switch ($payment_type) {
            case 'buy_tariff' :
            {
                if ($bill_info['payment_params'] != '') {
                    $tariff_params = unserialize($bill_info['payment_params']);
                } else {
                    $tariff_params = array();
                }

                $OutSum = $bill_info['sum'];
                $account_value = $this->getAccountValue($user_id);
                $account_value += $OutSum;

                //set new account value
                $query = 'UPDATE ' . DB_PREFIX . '_user SET account=? WHERE user_id=?';
                $stmt = $DBC->query($query, array($account_value, $user_id));


                if (isset($tariff_params['tariff_id']) && 0 != (int)$tariff_params['tariff_id']) {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/billing/admin/admin.php';
                    $BA = new billing_admin();
                    $BA->setTariffToUser((int)$tariff_params['tariff_id'], $user_id);
                    $query = 'UPDATE ' . DB_PREFIX . '_bill SET status=1 WHERE bill_id=?';
                    $stmt = $DBC->query($query, array($bill_id));
                }
                break;
            }
            case 'accesskey_buy' :
            {
                if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/local_watchlist_manager.php') ) {
                    require_once SITEBILL_DOCUMENT_ROOT.'/apps/userdata/admin/user_watchlistmanager.php';
                    require_once SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/local_watchlist_manager.php';
                    $WLM=new Local_Watchlist_Manager();
                } else {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/watchlistmanager/admin/admin.php';
                    $WLM = new watchlistmanager_admin();
                }
                $WLM->activateWatchlist($bill_id);
                $query = 'UPDATE ' . DB_PREFIX . '_bill SET status=1 WHERE bill_id=?';
                $stmt = $DBC->query($query, array($bill_id));
                break;
            }
            case 'status_set' :
            {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/billing/admin/admin.php';
                $WLM = new billing_admin();
                $WLM->setNewStatus($payment_params['id'], $payment_params['type'], ($payment_params['days'] * 86400));
                $query = 'UPDATE ' . DB_PREFIX . '_bill SET status=1 WHERE bill_id=?';
                $stmt = $DBC->query($query, array($bill_id));
                break;
            }
            case 'reservation' :
            {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/reservation/admin/admin.php';
                $WLM = new reservation_admin();
                if ($WLM->checkReservationActivationAbilityByPaymentId($bill_id)) {
                    $res = $WLM->activateReservation($bill_id);
                    if ($res) {
                        $this->closeBillAsPayed($bill_id);
                    }
                }
                break;
            }
            default :
            {
                if ($user_id != 0) {
                    $OutSum = $bill_info['sum'];
                    $account_value = $this->getAccountValue($user_id);
                    $account_value += $OutSum;

                    //set new account value
                    $query = 'UPDATE ' . DB_PREFIX . '_user SET account=? WHERE user_id=?';
                    $stmt = $DBC->query($query, array($account_value, $user_id));

                    //set status
                    $query = 'UPDATE ' . DB_PREFIX . '_bill SET status=1 WHERE bill_id=?';
                    $stmt = $DBC->query($query, array($bill_id));
                }
            }
        }
    }

    /**
     * Check signature
     * @param string $out_sum out sum
     * @param int $inv_id inv id
     * @param int $shp_item
     * @param string $crc crc
     * @return boolean
     */
    function checkSignature($out_summ, $inv_id, $shp_item, $crc)
    {
        if (1 == intval($this->getConfigValue('robokassa_by_frekassa'))) {
            $mrh_pass2 = $this->getConfigValue('robokassa_password2');

            if ($mrh_pass2 == '') {
                echo "bad sign\n";
                exit();
            }

            $crc = strtoupper($crc);

            $my_crc = strtoupper(md5($out_summ . ':' . $inv_id . ':' . $mrh_pass2));
            if ($my_crc != $crc) {
                echo "bad sign\n";
                exit();
            }
            return true;
        } else {
            $test_mode = intval($this->getConfigValue('robokassa_testmode'));
            if ($test_mode == 1) {
                $mrh_pass2 = $this->getConfigValue('robokassa_testpassword2');
            } else {
                $mrh_pass2 = $this->getConfigValue('robokassa_password2');
            }

            if ($mrh_pass2 == '') {
                echo "bad sign\n";
                exit();
            }

            $crc = strtoupper($crc);

            $my_crc = strtoupper(md5($out_summ . ':' . $inv_id . ':' . $mrh_pass2));
            if ($my_crc != $crc) {
                echo "bad sign\n";
                exit();
            }
            return true;
        }
    }

    /**
     * Check bill info
     * @param int $bill_id bill id
     * @return boolean
     */
    function checkBillInfo($bill_id)
    {
        $status = 0;
        $invoice_processing = false;

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/invoices/admin/admin.php')) {
            $invoice_id = $bill_id;
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/invoices/admin/admin.php');
            $invoices_admin = new invoices_admin();
            $invoice_info = $invoices_admin->load_by_id($invoice_id);
            if (!$invoice_info) {
            }
            $invoice_processing = true;
        }

        $DBC = DBC::getInstance();
        $query = 'SELECT `status`, `payment_type` FROM ' . DB_PREFIX . '_bill WHERE `bill_id`=? LIMIT 1';
        $stmt = $DBC->query($query, array($bill_id));
        if ($stmt and !$invoice_processing) {
            $ar = $DBC->fetch($stmt);
            $status = $ar['status'];
            $type = $ar['payment_type'];
        } elseif ($invoice_processing) {
            if ($invoice_info['status']['value'] == 'unpaid') {
                $status = 0;
            } else {
                $status = 1;
            }
            $type = 'invoice';
        } else {
            $this->riseError(Multilanguage::_('UNABLE_COMPLETE_PAYMENT', 'system'));
            return false;
        }
        if ($status != 0) {
            $this->riseError(Multilanguage::_('ORDER_PAYED_NOW', 'system'));
            return false;
        }
        if ($type == 'reservation') {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/reservation/admin/admin.php';
            $WLM = new reservation_admin();
            $res = $WLM->checkReservationActivationAbilityByPaymentId($bill_id);
            if (!$res) {
                $this->riseError(Multilanguage::_('UNABLE_COMPLETE_PAYMENT', 'system'));
                return false;
            }
        }
        if (!$this->checkSignature($_REQUEST["OutSum"], $_REQUEST["InvId"], $_REQUEST["Shp_item"], $_REQUEST["SignatureValue"])) {
            $this->RiseError("bad sign\n");
            return false;
        }
        return true;
    }

    /*
     * In progress
     */

    function createNewBill($user_id, $sum, $bill_name)
    {

    }

    /*
     * In progress
     */

    function getPaymentSystemsList($bill_id)
    {

        $DBC = DBC::getInstance();
        $query = 'SELECT * FROM ' . DB_PREFIX . '_bill WHERE bill_id=? AND `status`=0 LIMIT 1';
        $stmt = $DBC->query($query, array($bill_id));
        if ($stmt) {
            $bill_info = $DBC->fetch($stmt);
        } else {
            return '';
        }
        if ($this->getConfigValue('apps.clickuz.enable')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/clickuz/admin/admin.php');
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/clickuz/site/site.php');
            $clickuz_site = new clickuz_site();

            $form .= $clickuz_site->get_pay_button($bill_id, $bill_info['sum']);
        }
        if ($this->getConfigValue('apps.interkassa.enable')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/interkassa/admin/admin.php');
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/interkassa/site/site.php');
            $iterkassa_site = new interkassa_site();

            $form .= $iterkassa_site->get_pay_button($bill_id, $bill_info['sum']);
        }
        if ($this->getConfigValue('apps.paypal.enable')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/paypal/admin/admin.php');
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/paypal/site/site.php');
            $paypal_site = new paypal_site();

            $form .= $paypal_site->get_pay_button($bill_id, $bill_info['sum'], $bill_info['payment_sum']);
        }
        if ($this->getConfigValue('apps.eccgimi.enable')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/eccgimi/admin/admin.php');
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/eccgimi/site/site.php');
            $eccgimi_site = new eccgimi_site();
            $form .= $eccgimi_site->get_pay_button($bill_id);
        }
        if ($this->getConfigValue('robokassa_pay_enable')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/robokassa/robokassa.php';
            $Robox = new Robox();
            $form .= $Robox->getRoboForm($bill_id);
        }

        return $form;
    }

}
