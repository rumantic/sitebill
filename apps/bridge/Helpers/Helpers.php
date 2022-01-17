<?php
namespace bridge\Helpers;


class Helpers
{
    private static $angular_dist_files;
    private static $entity_storage;

    public static function include($entity_name, $method, $params) {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/api/classes/class.common.php');
        $api_common = new \API_Common();
        $entity_manager =  $api_common->init_custom_model_object($entity_name);

        if ( method_exists($entity_manager, $method) ) {
            return $entity_manager->$method($params);
        } else {
            throw new \Exception('method not defined');
        }
    }
    public static function normalize_admin_href ( $href ) {
        if ( !preg_match('/'.\SConfig::getConfigValue('apps.admin3.alias').'/', $href) ) {
            $href = str_replace('admin', \SConfig::getConfigValue('apps.admin3.alias'), $href);
        }
        return str_replace('index.php', '', $href);
    }

    public static function get_angular_file ( $prefix ) {
        if ( empty(self::$angular_dist_files) ) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/angular/admin/admin.php');
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/angular/site/site.php');
            $angular_site = new \angular_site();
            self::$angular_dist_files = $angular_site->load_dist_files_list();
        }
        if (!empty(self::$angular_dist_files['dist_files_prefixes'][$prefix])) {
            return self::$angular_dist_files['dist_files_prefixes'][$prefix];
        }
        return '';
    }

    public static function fetch_entity ($name, $uri, $key, $value_key = 'value') {
        try {
            if ( empty(self::$entity_storage[$name]['instance']) ) {
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/api/classes/class.common.php');
                $api_common = new \API_Common();
                self::$entity_storage[$name]['instance'] =  $api_common->init_custom_model_object($name);
            }

            if ( empty(self::$entity_storage[$name][$uri]) and is_object(self::$entity_storage[$name]['instance']) ) {
                $primary_key_value = self::$entity_storage[$name]['instance']->get_id_by_filter('uri', $uri);

                if ( !$primary_key_value ) {
                    return "entity $name with uri = $uri not found";
                }


                self::$entity_storage[$name][$uri]['primary_key_value'] = $primary_key_value;
                self::$entity_storage[$name][$uri]['data'] = self::$entity_storage[$name]['instance']->load_by_id($primary_key_value);
            }
            if ( !empty(self::$entity_storage[$name][$uri]['data'][$key]) ) {
                if (is_array(self::$entity_storage[$name][$uri]['data'][$key][$value_key]) and self::$entity_storage[$name][$uri]['data'][$key]['type'] == 'uploads') {
                    return self::$entity_storage[$name]['instance']->createMediaIncPath(
                        self::$entity_storage[$name][$uri]['data'][$key][$value_key][0],
                        'normal',
                        1
                    );

                } elseif ( !empty(self::$entity_storage[$name][$uri]['data'][$key][$value_key]) ) {
                    return self::$entity_storage[$name][$uri]['data'][$key][$value_key];
                }
            } else {
            }
            return '';
        } catch (\Exception $e) {
        }
        return "entity $name not found";
    }

    public static function entity ($name, $uri, $key, $value_key = 'value') {
        $entity_content = self::fetch_entity($name, $uri, $key, $value_key);
        return $entity_content;
    }

    public static function editor_wrapper ($wrapped_value, $name, $uri, $key, $value_key = 'value') {
        if ( \SConfig::getConfigValueStatic('editor_mode') ) {
            return "<div class=\"editable_entity_wrapper\">
            <div 
                data-entity-name=\"$name\" 
                data-entity-uri=\"$uri\" 
                data-entity-key=\"$key\" 
                data-entity-key-value=\"$value_key\" 
                class=\"editable_entity_wrapper_ctrl\" 
                style=\"display: none;\">
                    <i class=\"fa fa-edit\"></i>
             </div>".$wrapped_value.
                '</div>';
        }
        return $wrapped_value;

    }

    public static function entity_editable ($name, $uri, $key, $value_key = 'value') {
        $entity_content = self::fetch_entity($name, $uri, $key, $value_key);
        if ( filter_var($entity_content, FILTER_VALIDATE_URL) ) {
            if ( !\SConfig::getConfigValueStatic('editor_mode') ) {
                return '';
            }
            $entity_content = '<img src="'.$entity_content.'" width="50">';
        }
        return self::editor_wrapper($entity_content, $name, $uri, $key, $value_key);
    }

}

