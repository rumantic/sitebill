<?php
/**
 * RegisterValidationTrait — data validation for Register_Using_Model
 *
 * Extracted methods:
 *   - check_data($form_data)
 *   - checkemaildomain($email)
 *   - checkBlockedEmails($email)
 *   - checkLoginQuality($login, &$msg)
 *   - checkPasswordQuality($password, &$msg)
 */
trait RegisterValidationTrait
{
    /**
     * Check data
     * @param array $form_data
     * @return boolean
     */
    function check_data($form_data)
    {
        //var_dump($form_data['newpass']['value']);


        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        if (!$data_model->check_data($form_data)) {
            $this->riseError($data_model->GetErrorMessage());
            return false;
        }

        if (isset($form_data['email']) && $form_data['email']['value'] != '') {
            $email = $form_data['email']['value'];
            if (strlen($email) < 5) {
                $this->riseError(Multilanguage::_('REG_EMAIL_INVAL', 'system'));
                return false;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->riseError(Multilanguage::_('REG_EMAIL_INVAL', 'system'));
                return false;
            }
        }

        if ($this->getRequestValue('do') != 'edit_done') {
            if (!$this->checkEmail($form_data['email']['value'])) {
                $this->riseError(Multilanguage::_('REG_EMAIL_YET_REG', 'system'));
                return false;
            }
        } else {
            if (!$this->checkDiffEmail($form_data['email']['value'], $form_data['user_id']['value'])) {
                $this->riseError(Multilanguage::_('REG_EMAIL_YET_REG', 'system'));
                return false;
            }
        }

        if ($form_data['email']['value'] != '' && !$this->checkemaildomain($form_data['email']['value'])) {

            $this->riseError(Multilanguage::_('REG_EMAIL_NOT_GOOD_DOMAIN', 'system'));
            return false;
        }

        if ($form_data['email']['value'] != '' && !$this->checkBlockedEmails($form_data['email']['value'])) {
            $this->riseError(Multilanguage::_('REG_EMAIL_NOT_GOOD_BOX', 'system'));
            return false;
        }

        if (isset($form_data['login'])) {
            if ($form_data['login']['value'] == '') {
                $this->riseError(Multilanguage::_('REG_SET_LOGIN', 'system'));
                return false;
            }

            if (!preg_match('/^([a-zA-Z0-9-_@\.]*)$/', $form_data['login']['value'])) {
                $this->riseError(Multilanguage::_('REG_LOGIN_REQ_3', 'system'));
                return false;
            }

            /* if(preg_match('/^(vk|tw|gl|fb|ok)([0-9]*)$/', $form_data['login']['value'])){
              $this->riseError(Multilanguage::_('REG_LOGIN_USED', 'system'));
              return false;
              } */

            if (!$this->checkLogin($form_data['login']['value'])) {
                $this->riseError(Multilanguage::_('REG_LOGIN_USED', 'system'));
                return false;
            }
        }

        if ($form_data['newpass']['value'] != '') {

            if (!$this->checkPasswordQuality($form_data['newpass']['value'], $errormsg)) {
                $this->riseError($errormsg);
                return false;
            }

            if ($form_data['newpass']['value'] != $form_data['newpass_retype']['value']) {
                $this->riseError(Multilanguage::_('PASSWORDS_NOT_EQUAL', 'system'));
                return false;
            }
        }

        return true;
    }

    function checkLoginQuality($login, &$msg)
    {

    }

    function checkPasswordQuality($password, &$msg)
    {
        $min_pass_length = (int)$this->getConfigValue('register_minpasslength');
        $max_pass_length = (int)$this->getConfigValue('register_maxpasslength');
        $min_pass_length = ($min_pass_length == 0 ? 5 : $min_pass_length);
        $max_pass_length = ($max_pass_length == 0 ? 32 : $max_pass_length);
        if ($this->getConfigValue('register_passstregth') == '-1') {
            return true;
        }
        $pass_control_type = (int)$this->getConfigValue('register_passstregth');


        $pass_count = mb_strlen($password, SITE_ENCODING);

        if ($pass_count < $min_pass_length) {
            $msg = sprintf(Multilanguage::_('MIN_PASSWORD_LENGTH', 'system'), $min_pass_length);
            return false;
        }
        if ($pass_count > $max_pass_length) {
            $msg = sprintf(Multilanguage::_('MAX_PASSWORD_LENGTH', 'system'), $max_pass_length);
            return false;
        }


        if (preg_match_all('/(\d)/', $password, $dig_match)) {
            $pass_dig_count = count($dig_match[1]);
        } else {
            $pass_dig_count = 0;
        }

        if (preg_match_all('/([a-zа-яё])/u', $password, $smlet_match)) {
            $pass_smlet_count = count($smlet_match[1]);
        } else {
            $pass_smlet_count = 0;
        }

        if (preg_match_all('/([A-ZА-ЯЁ])/u', $password, $bglet_match)) {
            $pass_bglet_count = count($bglet_match[1]);
        } else {
            $pass_bglet_count = 0;
        }

        $pass_nonlet_count = $pass_count - $pass_dig_count - $pass_smlet_count - $pass_bglet_count;

        if ($pass_dig_count == $pass_count) {
            $first = (string)$password[0];
            $simpass = '';
            for ($i = 1; $i <= $pass_count; $i++) {
                $simpass .= $first;
            }

            if ($simpass == $password) {
                $msg = Multilanguage::_('REG_BAD_PASS', 'system');
                return false;
            }

            $simpass = '';
            for ($i = 0; $i < $pass_count; $i++) {
                $simpass .= (string)($first + $i);
            }

            if ($simpass == $password) {
                $msg = Multilanguage::_('REG_BAD_PASS', 'system');
                return false;
            }
        }

        $first = (string)$password[0];
        $simpass = '';
        for ($i = 1; $i <= $pass_count; $i++) {
            $simpass .= $first;
        }

        if ($simpass == $password) {
            $msg = Multilanguage::_('REG_BAD_PASS', 'system');
            return false;
        }

        if ($pass_control_type == 0) {

        } elseif ($pass_control_type == 1) {
            if ($pass_dig_count == $pass_count || $pass_dig_count == 0) {
                $msg = Multilanguage::_('REG_BAD_PASS', 'system') . '. ' . Multilanguage::_('REG_BAD_PASS_REQ1', 'system') . '.';
                return false;
            }
        } elseif ($pass_control_type == 2) {
            if ($pass_dig_count == 0 || $pass_smlet_count == 0 || $pass_bglet_count == 0) {
                $msg = Multilanguage::_('REG_BAD_PASS', 'system') . '. ' . Multilanguage::_('REG_BAD_PASS_REQ2', 'system') . '.';
                return false;
            }
        } elseif ($pass_control_type == 3) {
            if ($pass_dig_count == 0 || $pass_smlet_count == 0 || $pass_bglet_count == 0 || $pass_nonlet_count == 0) {
                $msg = Multilanguage::_('REG_BAD_PASS', 'system') . '. ' . Multilanguage::_('REG_BAD_PASS_REQ3', 'system') . '.';
                return false;
            }
        }
        return true;
    }

    function checkemaildomain($email)
    {
        list($box, $domain) = explode('@', $email);
        if ($domain == '') {
            return false;
        }
        $DBC = DBC::getInstance();
        $q = "SELECT * FROM " . DB_PREFIX . "_register_disable WHERE LOWER(`domain`)=?";
        $stmt = $DBC->query($q, array(mb_strtolower($domain, 'utf-8')));
        if ($stmt) {
            return false;
        }
        return true;
    }

    function checkBlockedEmails($email)
    {
        $DBC = DBC::getInstance();
        $q = 'SELECT * FROM ' . DB_PREFIX . '_register_disable_email WHERE LOWER(`email`)=?';
        $stmt = $DBC->query($q, array(mb_strtolower($email, 'utf-8')));
        if ($stmt) {
            return false;
        }
        return true;
    }
}
