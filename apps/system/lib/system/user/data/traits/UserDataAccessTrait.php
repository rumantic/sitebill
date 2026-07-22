<?php
/**
 * UserDataAccessTrait — access control & validation methods extracted from User_Data_Manager.
 *
 * Methods: check_access_to_data, check_access_to_aggregated_data,
 *          getNonUniqIds, checkUniquety, removeTemporaryFields
 */
trait UserDataAccessTrait
{
    function getNonUniqIds($form_data)
    {
        $ids = array();
        $unque_fields = trim($this->getConfigValue('apps.realty.uniq_params'));

        $id = 0;
        if (intval($form_data['id']['value']) != 0) {
            $id = intval($form_data['id']['value']);
        }

        $fields = array();
        if ('' !== $unque_fields) {
            $matches = array();
            preg_match_all('/([^,\s]+)/i', $unque_fields, $matches);
            if (!empty($matches[1])) {
                $fields = $matches[1];
            }
        }

        $where = array();
        $where_val = array();

        if (!empty($fields)) {
            foreach ($fields as $f) {
                if (isset($form_data[$f])) {
                    if ($form_data[$f]['dbtype'] == 1 || ($form_data[$f]['dbtype'] != 'notable' && $form_data[$f]['dbtype'] != '0')) {
                        $where[] = '`' . $f . '`=?';
                        $where_val[] = $form_data[$f]['value'];
                    }
                }
            }
            if ($id > 0) {
                $where[] = '`id`<>?';
                $where_val[] = $id;
            }
        } elseif (isset($form_data['city_id']) && isset($form_data['street_id']) && isset($form_data['number'])) {
            $where[] = '`city_id`=?';
            $where_val[] = (int)$form_data['city_id']['value'];
            $where[] = '`street_id`=?';
            $where_val[] = (int)$form_data['street_id']['value'];
            $where[] = '`number`=?';
            $where_val[] = $form_data['number']['value'];
            if ($id > 0) {
                $where[] = '`id`<>?';
                $where_val[] = $id;
            }
        } else {
            return $ids;
        }

        $DBC = DBC::getInstance();

        $query = 'SELECT id FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE ' . implode(' AND ', $where);

        $stmt = $DBC->query($query, $where_val);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ids[] = $ar['id'];
            }
        }

        return $ids;
    }

    function checkUniquety($form_data)
    {
        $uns = $this->getNonUniqIds($form_data);
        if (count($uns) > 0) {
            $this->riseError(Multilanguage::_('ADVUNIQUETY_ERROR', 'system') . ' (' . implode(',', $uns) . ')');
            return FALSE;
        }
        return TRUE;
    }

    protected function removeTemporaryFields(&$model, $remove_this_names = array())
    {
        if (is_array($remove_this_names) && count($remove_this_names) > 0) {
            foreach ($remove_this_names as $r) {
                unset($model[$r]);
            }
        }
        return $model;
    }

    /**
     * Check access to data
     * @param int $user_id
     * @param int $data_id
     * @return boolean
     */
    function check_access_to_data($user_id, $data_id)
    {
        $DBC = DBC::getInstance();
        $enable_curator_mode = false;
        if (1 == $this->getConfigValue('enable_curator_mode')) {
            $enable_curator_mode = true;
            $has_access = 0;

            if (1 === intval($this->getConfigValue('curator_mode_fullaccess'))) {

                $query = 'SELECT COUNT(d.id) AS _cnt FROM ' . DB_PREFIX . '_data d LEFT JOIN ' . DB_PREFIX . '_user u USING(user_id) WHERE d.id=? AND u.parent_user_id=?';
                $stmt = $DBC->query($query, array($data_id, $user_id));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    if ($ar['_cnt'] > 0) {
                        $has_access = 1;
                    }
                }
            } else {
                $query = 'SELECT COUNT(id) AS _cnt FROM ' . DB_PREFIX . '_cowork WHERE coworker_id=? AND object_type=? AND id=?';
                $stmt = $DBC->query($query, array($user_id, 'data', $data_id));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    if ($ar['_cnt'] > 0) {
                        $has_access = 1;
                    }
                }
            }


        }

        $where = array();
        $where_val = array();

        $where[] = '`id`=?';
        $where_val[] = $data_id;
        if (1 == (int)$this->getConfigValue('apps.realty.use_predeleting')) {
            $where[] = '`archived`=0';
        }

        if ($enable_curator_mode) {
            $where[] = '(`user_id`=? OR (`user_id`!=? AND 1=' . $has_access . '))';
            $where_val[] = $user_id;
            $where_val[] = $user_id;
        } else {
            $where[] = '`user_id`=?';
            $where_val[] = $user_id;
        }

        /* if (1 == (int)$this->getConfigValue('apps.realty.use_predeleting')) {
          $query = "SELECT id FROM " . DB_PREFIX . "_data WHERE user_id=? AND id=? AND archived=0";
          } else {
          $query = "SELECT id FROM " . DB_PREFIX . "_data WHERE user_id=? AND id=?";
          }
          $stmt = $DBC->query($query, array($user_id, $data_id)); */

        $query = 'SELECT id FROM ' . DB_PREFIX . '_data WHERE ' . implode(' AND ', $where);
        $stmt = $DBC->query($query, $where_val);

        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($ar['id'] > 0) {
                return true;
            }
        }
        return false;
    }

    // TODO Проверить используемость
    /**
     * Check access to data
     * @param int $user_id
     * @param int $data_id
     * @return boolean
     */
    function check_access_to_aggregated_data($user_id, $data_id)
    {
        $DBC = DBC::getInstance();

        $query = 'SELECT user_id FROM ' . DB_PREFIX . '_user WHERE puser_id=?';
        if (1 == (int)$this->getConfigValue('apps.realty.use_predeleting')) {
            $query = "SELECT id FROM " . DB_PREFIX . "_data WHERE user_id IN (SELECT user_id FROM " . DB_PREFIX . "_user WHERE puser_id=? OR user_id=?) AND id=? AND archived=0";
        } else {
            $query = "SELECT id FROM " . DB_PREFIX . "_data WHERE user_id IN (SELECT user_id FROM " . DB_PREFIX . "_user WHERE puser_id=? OR user_id=?) AND id=?";
        }

        $stmt = $DBC->query($query, array($user_id, $user_id, $data_id));


        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($ar['id'] > 0) {
                return true;
            }
        }
        return false;
    }
}
