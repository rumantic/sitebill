<?php
namespace data\helpers;

class CuratorHelper {

    private static $child_user_ids;

    public static function getChildUserIds ($user_id ) {
        if ( is_array(self::$child_user_ids) and  count(self::$child_user_ids) > 0 ) {
            return self::$child_user_ids;
        }
        $DBC = \DBC::getInstance();
        $query = 'SELECT user_id FROM  `' . DB_PREFIX . '_user` WHERE parent_user_id = ?';
        $stmt = $DBC->query($query, array($user_id));
        if ($stmt) {
            while ( $ar = $DBC->fetch($stmt) ) {
                self::$child_user_ids[] = $ar['user_id'];
            }

        }
        return self::$child_user_ids;
    }

}
