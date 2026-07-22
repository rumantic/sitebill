<?php
/**
 * ObjectAvatarTrait — Legacy avatar handling methods extracted from Object_Manager.
 *
 * Methods: attachAvatars, clearAvatarElement
 *
 * @see Object_Manager
 */

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

trait ObjectAvatarTrait
{
    protected function attachAvatars($model, $table, $key_name, $key_val)
    {
        foreach ($model[$table] as $k => $v) {
            if ($v['type'] == 'avatar' && isset($_FILES[$k]) && $_FILES[$k]['error'] == 0) {
                $parameters = $v['parameters'];

                if (isset($parameters['width']) && (int) $parameters['width'] != 0) {
                    $width = (int) $parameters['width'];
                } else {
                    $width = 250;
                }

                if (isset($parameters['height']) && (int) $parameters['height'] != 0) {
                    $height = (int) $parameters['height'];
                } else {
                    $height = 150;
                }

                if (!in_array($_FILES[$k]['type'], array('image/jpeg', 'image/pjpeg', 'image/gif', 'image/png'))) {
                    // Invalid image type, skip
                } else {
                    $fprts = explode('.', $_FILES[$k]['name']);
                    $ext = strtolower(end($fprts));
                    $name = md5(time() . rand(10, 99)) . '.' . $ext;

                    if (!move_uploaded_file($_FILES[$k]['tmp_name'], SITEBILL_DOCUMENT_ROOT . '/img/data/' . $name)) {
                        // Upload failed, skip
                    } else {
                        $res = $this->makePreview(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $name, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $name, $width, $height, $ext, 'f');
                        if ($res !== false) {
                            $DBC = DBC::getInstance();
                            $query = 'UPDATE ' . DB_PREFIX . '_' . $table . ' SET `' . $k . '`=? WHERE `' . $key_name . '`=?';
                            $stmt = $DBC->query($query, array($name, $key_val));
                        }
                    }
                }
            }
        }
    }

    protected function clearAvatarElement($table, $el, $key_name, $key_val)
    {
        $DBC = DBC::getInstance();
        $query = 'SELECT `' . $el . '` FROM ' . DB_PREFIX . '_' . $table . ' WHERE `' . $key_name . '`=?';
        $stmt = $DBC->query($query, array($key_val));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $fname = $ar[$el];
            @unlink(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $fname);
            $query = 'UPDATE ' . DB_PREFIX . '_' . $table . ' SET `' . $el . '`=? WHERE `' . $key_name . '`=?';
            $stmt = $DBC->query($query, array('', $key_val));
        }
    }
}
