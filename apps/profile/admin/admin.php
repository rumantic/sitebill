<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Profile admin backend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class profile_admin extends Object_Manager {
    /**
     * Constructor
     */
    function __construct() {
        parent::__construct();
        $this->table_name = 'profile';
        $this->action = 'profile';
        $this->primary_key = 'user_id';
        $this->app_title = _e('Профиль');

    }

    function grid($params = array(), $default_params = array())
    {
        if (self::$replace_grid_with_angular) {
            return $this->angular_grid();
        }
    }
}
