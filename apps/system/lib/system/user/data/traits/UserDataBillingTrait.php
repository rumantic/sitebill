<?php
/**
 * UserDataBillingTrait — billing/date helper methods extracted from User_Data_Manager.
 *
 * Methods: checkAdvAbonent, setAdvAbonent, setStatusDate, setUpdatedAtDate
 */
trait UserDataBillingTrait
{
    public function checkAdvAbonent($user_id = 0, $id = 0)
    {
        if ($user_id === 0) {
            $user_id = $this->getSessionUserId();
        }

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/billing/lib/billing.php')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/billing/lib/billing.php');
            $billing = new Billing();
            return $billing->checkAdvAbonent($user_id, $id);
        }
        return true;
    }

    protected function setAdvAbonent($id, $user_id = 0)
    {
        if ($user_id === 0) {
            $user_id = $this->getSessionUserId();
        }

        if ($this->getConfigValue('apps.billing.enable')) {
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/tariff/tariff.xml') and $this->getConfigValue('apps.tariff.enable') and file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/billing/billing.xml')) {
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/billing/lib/billing.php');
                $billing = new Billing();
                $billing->setAdvAbonentState($user_id, $id);
            }
        }
    }


    public function setStatusDate($id, $date = '')
    {
        $DBC = DBC::getInstance();
        if ($date == '') {
            $date = date('Y-m-d H:i:s', time());
        }
        $query = 'UPDATE ' . DB_PREFIX . '_' . $this->table_name . ' SET status_change=? WHERE `' . $this->primary_key . '`=?';
        $stmt = $DBC->query($query, array($date, $id));
    }

    public function setUpdatedAtDate($id)
    {
        $field = trim($this->getConfigValue('apps.realty.updated_at_field'));
        /*
         * $type = 1|0 - 1-date, 0-dtdatetime
         */
        $type = intval($this->getConfigValue('apps.realty.updated_at_field_type'));
        $update_date_added = intval($this->getConfigValue('apps.realty.update_date_added'));

        if ($field == '' && 1 === $update_date_added) {
            $field = 'date_added';
            $type = 0;
        }


        if ($field == '' || $type > 1) {
            return false;
        }

        $DBC = DBC::getInstance();
        if ($type == 1) {
            $date = time();
        } else {
            $date = date('Y-m-d H:i:s', time());
        }
        $query = 'UPDATE ' . DB_PREFIX . '_' . $this->table_name . ' SET `' . $field . '`=? WHERE `' . $this->primary_key . '`=?';
        $stmt = $DBC->query($query, array($date, $id));
        if ($stmt) {
            return true;
        }
        return false;
    }
}
