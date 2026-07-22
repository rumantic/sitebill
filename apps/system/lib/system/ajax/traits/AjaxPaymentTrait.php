<?php
/**
 * AjaxPaymentTrait — extracted from Ajax_Server (ajax_server.php)
 */
trait AjaxPaymentTrait
{
    function make_special_payment()
    {
        $current_account = 0;
        $user_id = $this->getSessionUserId();
        $realty_id = (int)$this->getRequestValue('realty_id');
        $days = (int)$this->getRequestValue('days');
        //$per_day=abs($this->getRequestValue('per_day'));
        $per_day = 0;
        $payment_type = $this->getRequestValue('payment_type');
        $object_name = trim($this->getRequestValue('object_name'));
        $object_key = trim($this->getRequestValue('object_key'));
        if ($object_name != 'complex') {
            $object_name = 'data';
        } else {
            $object_name = 'complex';
        }

        $is_custom_status = false;
        $used_custom_status = array();


        if ($object_name == 'data') {

            if ($this->getConfigValue('apps.billing.enable')) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/billing/admin/admin.php';
                $billing = new billing_admin();

                $custom_statuses = $billing->loadCustomStatuses();


            }
            switch ($payment_type) {
                case 'vip' :
                {
                    $per_day = $this->getConfigValue('vip_cost');
                    break;
                }
                case 'premium' :
                {
                    $per_day = $this->getConfigValue('premium_cost');
                    break;
                }
                case 'bold' :
                {
                    $per_day = $this->getConfigValue('bold_cost');
                    break;
                }
                case 'bold_map' :
                {
                    $per_day = $this->getConfigValue('bold_map_cost');
                    break;
                }
                case 'buy_ups' :
                {
                    $per_day = $this->getConfigValue('ups_price');
                    break;
                }
                case 'make_up' :
                {
                    $per_day = $this->getConfigValue('ups_price');
                    $days = 1;
                    break;
                }
                default:
                {

                    if (isset($custom_statuses[$payment_type])) {
                        $is_custom_status = true;
                        $used_custom_status = $custom_statuses[$payment_type];
                        $per_day = floatval($used_custom_status['price']);
                    }
                }
            }
        } else {
            switch ($payment_type) {
                case 'vip' :
                {
                    $per_day = $this->getConfigValue('apps.complex.complex_vip_cost');
                    break;
                }
                case 'premium' :
                {
                    $per_day = $this->getConfigValue('apps.complex.complex_premium_cost');
                    break;
                }
                case 'bold' :
                {
                    $per_day = $this->getConfigValue('apps.complex.complex_bold_cost');
                    break;
                }
            }
        }

        $sum = $days * $per_day;
        if ($sum == 0) {
            echo 'error';
            exit;
        }
        //if()

        $error_only_active_data = 'Платный статус можно применить только к активным объектам. Активируйте объявление или дождитесь модерации.';

        if ($user_id != 0 && $days > 0 && (in_array($payment_type, array('vip', 'premium', 'bold', 'bold_map', 'buy_ups', 'make_up')) || $is_custom_status)) {

            if ($payment_type != 'buy_ups' && $realty_id == 0) {
                echo 'error';
                exit;
            }

            $DBC = DBC::getInstance();

            $query = 'SELECT account FROM ' . DB_PREFIX . '_user WHERE user_id=? LIMIT 1';
            $stmt = $DBC->query($query, array($user_id));
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                $current_account = $ar['account'];
            }

            $last_account = $current_account - $sum;
            if ($last_account < 0) {
                $html = Multilanguage::_('INCUFFICIENT_BALANCE', 'system') . '. <a href="' . SITEBILL_MAIN_URL . '/account/balance/?do=add_bill">' . Multilanguage::_('RECHARGE_BALANCE', 'system') . '</a>';
            } else {
                if ($payment_type == 'vip') {

                    if ($object_name == 'complex') {
                        $query = 'SELECT `complex_id`, `vip_status_end` FROM ' . DB_PREFIX . '_complex WHERE complex_id=?' . (intval($this->getConfigValue('apps.complex.activity_status_enable')) == 1 ? ' AND `active`=1' : '');
                    } else {
                        $query = 'SELECT `id`, `vip_status_end` FROM ' . DB_PREFIX . '_data WHERE `id`=? AND `active`=1';
                    }
                    $stmt = $DBC->query($query, array($realty_id));

                    if (!$stmt) {
                        return 'error:' . $error_only_active_data;
                    }

                    $ar = $DBC->fetch($stmt);
                    $prev_status_end = $ar['vip_status_end'];

                    $query = 'INSERT INTO ' . DB_PREFIX . '_bill (`user_id`, `sum`, `date`, `description`, `status`) VALUES (?, ?, ?, ?, ?)';
                    $rem = 'Оплата VIP состояния объявления ID=' . $realty_id . ' на срок ' . $days . ' дней';
                    if ($object_name == 'complex') {
                        $rem = 'Оплата VIP состояния объекта (ЖК) ID=' . $realty_id . ' на срок ' . $days . ' дней';
                    }
                    $stmt = $DBC->query($query, array((int)$user_id, $sum, time(), $rem, 1));


                    if (!$stmt) {
                        return 'error';
                    }

                    $query = 'UPDATE ' . DB_PREFIX . '_user SET account=? WHERE user_id=?';
                    $stmt = $DBC->query($query, array($last_account, $user_id));

                    if (!$stmt) {
                        return 'error';
                    }

                    if ($prev_status_end < time()) {
                        $new_status_end = time() + $days * 86400;
                    } else {
                        $new_status_end = $prev_status_end + $days * 86400;
                    }
                    if ($object_name == 'complex') {
                        $query = 'UPDATE ' . DB_PREFIX . '_complex SET vip_status_end=? WHERE complex_id=?';
                    } else {
                        $query = 'UPDATE ' . DB_PREFIX . '_data SET vip_status_end=? WHERE id=?';
                    }

                    $stmt = $DBC->query($query, array($new_status_end, $realty_id));

                    if (!$stmt) {
                        return 'error';
                    }
                    $this->refresh_realty_date_added($realty_id);


                    $html = Multilanguage::_('VIP_STATUS_APPLIED', 'system');
                } elseif ($payment_type == 'premium') {

                    if ($object_name == 'complex') {
                        $query = 'SELECT `complex_id`, `premium_status_end` FROM ' . DB_PREFIX . '_complex WHERE complex_id=?' . (intval($this->getConfigValue('apps.complex.activity_status_enable')) == 1 ? ' AND `active`=1' : '');
                    } else {
                        $query = 'SELECT `id`, `premium_status_end` FROM ' . DB_PREFIX . '_data WHERE `id`=? AND `active`=1';
                    }
                    $stmt = $DBC->query($query, array($realty_id));

                    if (!$stmt) {
                        return 'error:' . $error_only_active_data;
                    }

                    $ar = $DBC->fetch($stmt);
                    $prev_status_end = $ar['premium_status_end'];

                    $query = 'INSERT INTO ' . DB_PREFIX . '_bill (`user_id`, `sum`, `date`, `description`, `status`) VALUES (?, ?, ?, ?, ?)';
                    $rem = 'Оплата Премиум состояния объявления ID=' . $realty_id . ' на срок ' . $days . ' дней';
                    if ($object_name == 'complex') {
                        $rem = 'Оплата Премиум состояния объекта (ЖК) ID=' . $realty_id . ' на срок ' . $days . ' дней';
                    }

                    $stmt = $DBC->query($query, array((int)$user_id, $sum, time(), $rem, 1));

                    if (!$stmt) {
                        return 'error';
                    }

                    $query = 'UPDATE ' . DB_PREFIX . '_user SET account=? WHERE user_id=?';
                    $stmt = $DBC->query($query, array($last_account, $user_id));

                    if (!$stmt) {
                        return 'error';
                    }

                    if ($prev_status_end < time()) {
                        $new_status_end = time() + $days * 86400;
                    } else {
                        $new_status_end = $prev_status_end + $days * 86400;
                    }

                    if ($object_name == 'complex') {
                        $query = 'UPDATE ' . DB_PREFIX . '_complex SET premium_status_end=? WHERE complex_id=?';
                    } else {
                        $query = 'UPDATE ' . DB_PREFIX . '_data SET premium_status_end=? WHERE id=?';
                    }

                    $stmt = $DBC->query($query, array($new_status_end, $realty_id));

                    if (!$stmt) {
                        return 'error';
                    }
                    $this->refresh_realty_date_added($realty_id);


                    $html = Multilanguage::_('PREMIUM_STATUS_APPLIED', 'system');
                } elseif ($payment_type == 'bold') {

                    if ($object_name == 'complex') {
                        $query = 'SELECT `complex_id`, `bold_status_end` FROM ' . DB_PREFIX . '_complex WHERE complex_id=?' . (intval($this->getConfigValue('apps.complex.activity_status_enable')) == 1 ? ' AND `active`=1' : '');
                    } else {
                        $query = 'SELECT id, `bold_status_end` FROM ' . DB_PREFIX . '_data WHERE id=? AND active=1';
                    }
                    $stmt = $DBC->query($query, array($realty_id));

                    if (!$stmt) {
                        return 'error:' . $error_only_active_data;
                    }

                    $ar = $DBC->fetch($stmt);
                    $prev_status_end = $ar['bold_status_end'];

                    $query = 'INSERT INTO ' . DB_PREFIX . '_bill (`user_id`, `sum`, `date`, `description`, `status`) VALUES (?, ?, ?, ?, ?)';
                    $rem = 'Оплата выделенного состояния объявления ID=' . $realty_id . ' на срок ' . $days . ' дней';
                    if ($object_name == 'complex') {
                        $rem = 'Оплата выделенного состояния объекта (ЖК) ID=' . $realty_id . ' на срок ' . $days . ' дней';
                    }
                    $stmt = $DBC->query($query, array((int)$user_id, $sum, time(), $rem, 1));

                    if (!$stmt) {
                        return 'error';
                    }

                    $query = 'UPDATE ' . DB_PREFIX . '_user SET account=? WHERE user_id=?';
                    $stmt = $DBC->query($query, array($last_account, $user_id));

                    if (!$stmt) {
                        return 'error';
                    }

                    if ($prev_status_end < time()) {
                        $new_status_end = time() + $days * 86400;
                    } else {
                        $new_status_end = $prev_status_end + $days * 86400;
                    }

                    if ($object_name == 'complex') {
                        $query = 'UPDATE ' . DB_PREFIX . '_complex SET bold_status_end=? WHERE complex_id=?';
                    } else {
                        $query = 'UPDATE ' . DB_PREFIX . '_data SET bold_status_end=? WHERE id=?';
                    }
                    $stmt = $DBC->query($query, array($new_status_end, $realty_id));

                    if (!$stmt) {
                        return 'error';
                    }
                    $this->refresh_realty_date_added($realty_id);


                    //$html = 'Выделенный статус присвоен';
                    $html = Multilanguage::_('BOLD_STATUS_APPLIED', 'system');
                } elseif ($payment_type == 'bold_map' && $object_name == 'data') {

                    $query = 'SELECT id, `bold_status_map_end` FROM ' . DB_PREFIX . '_data WHERE id=? AND active=1';
                    $stmt = $DBC->query($query, array($realty_id));

                    if (!$stmt) {
                        return 'error:' . $error_only_active_data;
                    }

                    $ar = $DBC->fetch($stmt);
                    $prev_status_end = $ar['bold_status_map_end'];

                    $query = 'INSERT INTO ' . DB_PREFIX . '_bill (`user_id`, `sum`, `date`, `description`, `status`) VALUES (?, ?, ?, ?, ?)';
                    $stmt = $DBC->query($query, array((int)$user_id, $sum, time(), 'Оплата выделенного на карте состояния объявления ID=' . $realty_id . ' на срок ' . $days . ' дней', 1));

                    if (!$stmt) {
                        return 'error';
                    }

                    $query = 'UPDATE ' . DB_PREFIX . '_user SET account=? WHERE user_id=?';
                    $stmt = $DBC->query($query, array($last_account, $user_id));

                    if (!$stmt) {
                        return 'error';
                    }

                    if ($prev_status_end < time()) {
                        $new_status_end = time() + $days * 86400;
                    } else {
                        $new_status_end = $prev_status_end + $days * 86400;
                    }

                    $query = 'UPDATE ' . DB_PREFIX . '_data SET bold_status_map_end=? WHERE id=?';
                    $stmt = $DBC->query($query, array($new_status_end, $realty_id));

                    if (!$stmt) {
                        return 'error';
                    }
                    $this->refresh_realty_date_added($realty_id);


                    $html = 'Выделенный на карте статус присвоен';
                } elseif ($payment_type == 'buy_ups' && $object_name == 'data') {
                    $query = 'INSERT INTO ' . DB_PREFIX . '_bill (`user_id`, `sum`, `date`, `description`, `status`) VALUES (?, ?, ?, ?, ?)';
                    $stmt = $DBC->query($query, array((int)$user_id, $sum, time(), 'Покупка пакета подъемов в количестве ' . $days, 1));
                    if (!$stmt) {
                        echo 'error';
                    }
                    $query = 'UPDATE ' . DB_PREFIX . '_user SET account=? WHERE user_id=?';
                    $stmt = $DBC->query($query, array($last_account, $user_id));

                    if (!$stmt) {
                        echo 'error';
                    }

                    $query = 'SELECT COUNT(user_id) AS cnt FROM ' . DB_PREFIX . '_upper_packet WHERE user_id=?';
                    $stmt = $DBC->query($query, array($user_id));
                    if ($stmt) {
                        $ar = $DBC->fetch($stmt);
                        if ($ar['cnt'] > 0) {
                            $query = 'UPDATE ' . DB_PREFIX . '_upper_packet SET quantity=quantity+' . $days . ' WHERE user_id=?';
                            $stmt = $DBC->query($query, array($user_id));
                        } else {
                            $query = 'INSERT INTO ' . DB_PREFIX . '_upper_packet (`quantity`,`user_id`) VALUES (?, ?)';
                            $stmt = $DBC->query($query, array($days, $user_id));
                        }
                    }

                    $html = 'Пакет подъемов оплачен';
                } elseif ($payment_type == 'make_up' && $object_name == 'data') {
                    $query = 'INSERT INTO ' . DB_PREFIX . '_bill (`user_id`, `sum`, `date`, `description`, `status`) VALUES (?, ?, ?, ?, ?)';
                    $stmt = $DBC->query($query, array((int)$user_id, $sum, time(), 'Поднятие объявления ID: ' . $realty_id, 1));
                    if (!$stmt) {
                        echo 'error';
                    }
                    $query = 'UPDATE ' . DB_PREFIX . '_user SET account=? WHERE user_id=?';
                    $stmt = $DBC->query($query, array($last_account, $user_id));

                    if (!$stmt) {
                        echo 'error';
                    }

                    $query = 'UPDATE ' . DB_PREFIX . '_data SET date_added=? WHERE user_id=? AND id=?';
                    $stmt = $DBC->query($query, array(date('Y-m-d H:i:s', time()), $user_id, $realty_id));

                    $html = 'Поднятие выполнено';
                } elseif ($is_custom_status && $object_name == 'data') {

                    $status_field_name = $used_custom_status['field_name'];


                    $status_bill_msg = $used_custom_status['bill_msg'];

                    $status_bill_msg .= ' (ID: ' . $realty_id . ', ' . $days . ' дней)';

                    $status_done_msg = $used_custom_status['done_msg'];

                    //$status_bill_msg = 'Оплата выделенного на карте состояния объявления ID=' . $realty_id . ' на срок ' . $days . ' дней';
                    //$status_done_msg = 'Выделенный на карте статус присвоен';

                    $query = 'SELECT `id`, `' . $status_field_name . '` FROM ' . DB_PREFIX . '_data WHERE `id` = ? AND `active` = 1';
                    $stmt = $DBC->query($query, array($realty_id));

                    if (!$stmt) {
                        return 'error:' . $error_only_active_data;
                    }

                    $ar = $DBC->fetch($stmt);
                    $prev_status_end = $ar[$status_field_name];
                    if ($prev_status_end == '' || $prev_status_end == '0000-00-00 00:00:00') {
                        $prev_status_end = time() - 10;
                    } else {
                        $prev_status_end = strtotime($prev_status_end);
                    }

                    $query = 'INSERT INTO ' . DB_PREFIX . '_bill (`user_id`, `sum`, `date`, `description`, `status`) VALUES (?, ?, ?, ?, ?)';
                    $stmt = $DBC->query($query, array((int)$user_id, $sum, time(), $status_bill_msg, 1));

                    if (!$stmt) {
                        echo 'error';
                    }

                    $query = 'UPDATE ' . DB_PREFIX . '_user SET `account` = ? WHERE `user_id` = ?';
                    $stmt = $DBC->query($query, array($last_account, $user_id));

                    if (!$stmt) {
                        echo 'error';
                    }

                    if ($prev_status_end < time()) {
                        $new_status_end = time() + $days * 86400;
                    } else {
                        $new_status_end = $prev_status_end + $days * 86400;
                    }

                    $query = 'UPDATE ' . DB_PREFIX . '_data SET `' . $status_field_name . '` = ? WHERE `id` = ?';
                    $stmt = $DBC->query($query, array(date('Y-m-d H:i:s', $new_status_end), $realty_id));

                    if (!$stmt) {
                        echo 'error';
                    }
                    $this->refresh_realty_date_added($realty_id);

                    $html = $status_done_msg;
                    //return json_encode(array('status'=>1, 'msg'=>$html, 'new_status'=>date('Y-m-d H:i', $new_status_end)));
                    //exit();
                } else {
                    return 'error';
                }
            }
            echo $html;
        } else {
            echo 'error';
        }
        exit;
    }

    function load_product_data($product_id)
    {
        $DBC = DBC::getInstance();
        $query = 'SELECT * FROM ' . DB_PREFIX . '_shop_product WHERE product_id=? LIMIT 1';
        $stmt = $DBC->query($query, array($product_id));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            return $ar;
        }
        return false;
    }

}
