<?php
/**
 * DataPermissionTrait — Permission-gated CRUD actions for Data_Manager.
 *
 * Extracted from data_manager.php during refactoring.
 * Methods: checkOwning(), _editAction(), _edit_doneAction(), _new_doneAction(),
 *          _delete_finalAction(), _restoreAction(), _deleteAction()
 */
trait DataPermissionTrait
{
    function checkOwning($id, $user_id) {
        $DBC = DBC::getInstance();
        $query = 'SELECT COUNT(' . $this->primary_key . ') AS _cnt FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE `' . $this->primary_key . '`=? AND user_id=?';
        $stmt = $DBC->query($query, array($id, $user_id));
        $res = false;
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ((int) $ar['_cnt'] === 1) {
                $res = true;
            }
        }
        return $res;
    }

    protected function _editAction() {
        if ((1 === (int) $this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name'] !== 'admin') && (1 === (int) $this->getConfigValue('data_adv_share_access'))) {
            $user_id = (int) $_SESSION['user_id_value'];
            if ($this->checkOwning($this->getRequestValue($this->primary_key), $user_id)) {
                return parent::_editAction();
            }
        } else {
            return parent::_editAction();
        }
        return '';
    }

    protected function _edit_doneAction() {
        if ((1 === (int) $this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name'] !== 'admin') && (1 === (int) $this->getConfigValue('data_adv_share_access'))) {
            $skip_check_owning = false;
            if ($this->getConfigValue('data_adv_share_access_extended') != '') {
                $extended_user_list = explode(',', $this->getConfigValue('data_adv_share_access_extended'));
                if (!in_array($this->getRequestValue('user_id'), $extended_user_list)) {
                    $user_id = (int) $_SESSION['user_id_value'];
                } else {
                    $user_id = $this->getRequestValue('user_id');
                    $skip_check_owning = true;
                }
            } else {
                $user_id = (int) $_SESSION['user_id_value'];
            }
            $this->setRequestValue('user_id', $user_id);

            $_POST['user_id'] = $user_id;
            if ($skip_check_owning) {
                return parent::_edit_doneAction();
            } elseif ($this->checkOwning($this->getRequestValue($this->primary_key), $user_id)) {
                return parent::_edit_doneAction();
            }
        } else {
            return parent::_edit_doneAction();
        }
        return '';
    }

    protected function _new_doneAction() {
        if ( isset($_SESSION['user_id_value']) ) {
            $user_id = (int) $_SESSION['user_id_value'];
        } else {
            $user_id = (int) $_SESSION['user_id'];
        }
        if ((1 === (int) $this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name'] !== 'admin') && (1 === (int) $this->getConfigValue('data_adv_share_access'))) {
            $this->setRequestValue('user_id', $user_id);
            $_POST['user_id'] = $user_id;
        }
        if ( $this->getConfigValue('apps.products.limit_add_data')  ) {
            require_once (SITEBILL_DOCUMENT_ROOT.'/apps/api/classes/class.common.php');
            require_once (SITEBILL_DOCUMENT_ROOT.'/apps/cart/api/class.cart.php');
            $api_cart = new API_cart();
            $user_limit = (int)$api_cart->get_user_limit($user_id, 'exclusive');
            if ( $user_limit < 1 ) {
                $this->riseError('Превышен лимит эксклюзивов');
                return false;
            }
        }
        $status = parent::_new_doneAction();
        if ( $status and $this->get_new_record_id() and $this->getConfigValue('apps.products.limit_add_data') ) {
            $increment = new \userproducts\modules\increment();
            $increment->decrement_limit('user', $user_id, 'exclusive', 1);
        }
        return $status;
    }

    protected function _delete_finalAction() {
        if ((int)$this->getConfigValue('apps.realty.use_predeleting') !== 1) {
            return '';
        }
        if ( !$this->get_permission_instance()->get_access($this->getSessionUserId(), 'data', 'delete_final') ) {
            return _e('Доступ запрещен').'<br>'._e('Проверьте права доступа группы: ').'data.delete_final';
        }

        if ((1 === (int) $this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name'] !== 'admin') && (1 === (int) $this->getConfigValue('data_adv_share_access'))) {
            $user_id = (int) $_SESSION['user_id_value'];
            $id = (int)$this->getRequestValue($this->primary_key);
            if ($this->checkOwning($id, $user_id)) {
                return parent::_deleteAction();
            }
        } else {
            return parent::_deleteAction();
        }
        return '';
    }

    /*
     * Restore adv from archive to actual base
     */

    protected function _restoreAction() {
        if (intval($this->getConfigValue('apps.realty.use_predeleting')) !== 1) {
            return '';
        }
        $id = intval($this->getRequestValue($this->primary_key));
        if ((1 === (int) $this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name'] !== 'admin') && (1 === (int) $this->getConfigValue('data_adv_share_access'))) {
            $user_id = (int) $_SESSION['user_id_value'];
            if ($this->checkOwning($id, $user_id)) {
                $DBC = DBC::getInstance();
                $query = 'UPDATE ' . DB_PREFIX . '_data SET `archived`=0 WHERE `id`=?';
                $stmt = $DBC->query($query, array($id));
            }
        } else {
            $DBC = DBC::getInstance();
            $query = 'UPDATE ' . DB_PREFIX . '_data SET `archived`=0 WHERE `id`=?';
            $stmt = $DBC->query($query, array($id));
        }
        if ($this->isRedirectDisabled()) {
            return true;
        }
        header('location: ' . SITEBILL_MAIN_URL . '/admin/?archived=1');
        exit();
        return '';
    }

    protected function _deleteAction() {
        $id = intval($this->getRequestValue($this->primary_key));
        if ((1 === (int) $this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name'] !== 'admin') && (1 === (int) $this->getConfigValue('data_adv_share_access'))) {
            $user_id = (int) $_SESSION['user_id_value'];
            if ($this->checkOwning($id, $user_id)) {
                if (1 == (int) $this->getConfigValue('apps.realty.use_predeleting') && isset($this->data_model['data']['archived'])) {
                    $DBC = DBC::getInstance();
                    $query = 'UPDATE ' . DB_PREFIX . '_data SET `archived`=1 WHERE `id`=?';
                    $stmt = $DBC->query($query, array($id));
                    if ($this->isRedirectDisabled()) {
                        return true;
                    }

                    header('location: ' . SITEBILL_MAIN_URL . '/admin/?action=' . $this->action);
                    exit();
                } else {
                    return parent::_deleteAction();
                }
            }
        } else {
            if (1 == (int) $this->getConfigValue('apps.realty.use_predeleting') && isset($this->data_model['data']['archived'])) {
                $DBC = DBC::getInstance();
                $query = 'UPDATE ' . DB_PREFIX . '_data SET `archived`=1 WHERE `id`=?';
                $stmt = $DBC->query($query, array($id));
                if ($this->isRedirectDisabled()) {
                    return true;
                }

                header('location: ' . SITEBILL_MAIN_URL . '/admin/?action=' . $this->action);
                exit();
                return $this->grid();
            } else {
                return parent::_deleteAction();
            }
        }
        return '';
    }
}
