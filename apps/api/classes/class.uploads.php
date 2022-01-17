<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Uploads REST class
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class API_uploads extends API_Common {
    public function _load_level () {
        $model_name = $this->request->get('model_name');
        $table = $model_name;
        $field_name = $this->request->get('field_name');
        $current_position = (int)$this->request->get('current_position');
        $key = $this->request->get('key');
        $key_value = (int)$this->request->get('key_value');
        $user_id = $this->get_my_user_id();

        try {
            $level = $this->get_uploads($field_name, $table, $key, $key_value, $user_id, $current_position);
        } catch (Exception $e) {
            $this->writeLog($e->getMessage());
        }

        if ( $level ) {
            $ret = array(
                'status' => 'ok',
                'message' => 'level loaded successfully',
                'level' => $level
            );
        } else {
            $ret = array(
                'status' => 'error',
                'message' => 'level update failed',
                'request' => $this->request()->all()
            );
        }
        return $this->json_string($ret);
    }

    public function _update_level () {
        $model_name = $this->request->get('model_name');
        $table = $model_name;

        $field_name = $this->request->get('field_name');
        $current_position = (int) $this->request->get('current_position');
        $key = $this->request->get('key');
        $key_value = (int) $this->request->get('key_value');
        $level = $this->request->get('level');
        $user_id = $this->get_my_user_id();

        //$result = $this->create_connection($data_layer_name, $data_dot_in, $data_dot_out);
        $this->writeLog('update level');
        $this->writeArrayLog($level);

        try {
            $result = $this->update_uploads($field_name, $table, $key, $key_value, $user_id, $current_position, $level);
        } catch (Exception $e) {
            $error = $e->getMessage();
            $this->writeLog($e->getMessage());
        }




        if ( $result ) {
            $ret = array(
                'status' => 'ok',
                'message' => 'level updated successfully',
            );
        } else {
            $ret = array(
                'status' => 'error',
                'message' => 'level update failed: '.$error,
                'request' => $this->request()->all()
            );
        }
        return $this->json_string($ret);
    }

    private function get_uploads ($field_name, $table, $key, $key_value, $user_id, $current_position) {
        $DBC = \DBC::getInstance();
        $admin_mode = true;

        if ($admin_mode) {
            $query = 'SELECT `' . $field_name . '` FROM `' . DB_PREFIX . '_' . $table . '` WHERE `' . $key . '`=? LIMIT 1';
            $stmt = $DBC->query($query, array($key_value));
        } else {
            $query = 'SELECT `' . $field_name . '` FROM `' . DB_PREFIX . '_' . $table . '` WHERE `' . $key . '`=? AND user_id=? LIMIT 1';
            $stmt = $DBC->query($query, array($key_value, $user_id));
        }

        if (!$stmt) {
            throw new Exception('Cant find record: '.$DBC->getLastError());
        }
        $ar = $DBC->fetch($stmt);
        if ($ar[$field_name] == '') {
            throw new Exception('Empty uploads '.$field_name.' '.$DBC->getLastError());
        }
        $uploads = unserialize($ar[$field_name]);
        if (!isset($uploads[$current_position])) {
            throw new Exception('Empty uploads '.$field_name.' for current_position '.$current_position.': '.$DBC->getLastError());
        }
        $uploads[$current_position]['level']['mapwidth'] = str_replace('px', '',$uploads[$current_position]['normal_params']['width']);
        $uploads[$current_position]['level']['mapheight'] = str_replace('px', '',$uploads[$current_position]['normal_params']['height']);
        return $uploads[$current_position]['level'];
    }

    private function update_uploads ($field_name, $table, $key, $key_value, $user_id, $current_position, $level) {
        $DBC = \DBC::getInstance();
        $admin_mode = true;

        if ($admin_mode) {
            $query = 'SELECT `' . $field_name . '` FROM `' . DB_PREFIX . '_' . $table . '` WHERE `' . $key . '`=? LIMIT 1';
            $stmt = $DBC->query($query, array($key_value));
        } else {
            $query = 'SELECT `' . $field_name . '` FROM `' . DB_PREFIX . '_' . $table . '` WHERE `' . $key . '`=? AND user_id=? LIMIT 1';
            $stmt = $DBC->query($query, array($key_value, $user_id));
        }

        if (!$stmt) {
            throw new Exception('Cant find record: '.$DBC->getLastError());
        }
        $ar = $DBC->fetch($stmt);
        if ($ar[$field_name] == '') {
            throw new Exception('Empty uploads '.$field_name.' '.$DBC->getLastError().'. Возможно вы редактируете картинки из кэша');
        }
        $uploads = unserialize($ar[$field_name]);
        if (!isset($uploads[$current_position])) {
            throw new Exception('Empty uploads '.$field_name.' for current_position '.$current_position.': '.$DBC->getLastError());
        }
        $uploads[$current_position]['level'] = $level;
        $this->writeArrayLog($uploads);

        $query = 'UPDATE `' . DB_PREFIX . '_' . $table . '` SET `' . $field_name . '`=? WHERE `' . $key . '`=?';
        $stmt = $DBC->query($query, array(serialize($uploads), $key_value));
        if ($stmt) {
            return true;
        }
        return true;
        //throw new Exception('Cant update '.$field_name.' for current_position '.$current_position.': '.$DBC->getLastError());
    }

}
