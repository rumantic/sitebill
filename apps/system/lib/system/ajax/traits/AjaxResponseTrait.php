<?php
/**
 * AjaxResponseTrait — extracted from Ajax_Server (ajax_server.php)
 */
trait AjaxResponseTrait
{
    function getSuccessResponceJSON($params = array())
    {
        $responce = array(
            'status' => 1
        );
        if (!empty($params)) {
            $responce = array_merge($responce, $params);
        }
        return json_encode($responce);
    }

    function getErrorResponceJSON($params = array())
    {
        $responce = array(
            'status' => 0
        );
        if (!empty($params)) {
            $responce = array_merge($responce, $params);
        }
        return json_encode($responce);
    }

    private function _getOptionsData($key, $field, $table, $fieldby, $value, $parameters = array())
    {
        $fname = $field;
        if (1 === intval($this->getConfigValue('apps.language.use_langs')) && (!isset($parameters['no_ml']) || 0 === intval($parameters['no_ml']))) {
            $fname .= $this->getLangPostfix($this->getCurrentLang());
        }

        $ret = array();
        $DBC = DBC::getInstance();
        if (isset($parameters['use_query']) && $parameters['use_query'] != '') {
            $query = $parameters['use_query'];
            if ($_REQUEST['debug'] == 1) var_dump($query);
            $stmt = $DBC->query($query, array($value));
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    if ($ar[$fieldby] == $value) $ret[] = array('id' => $ar[$key], 'name' => $ar[$fname]);
                }
            }
        } else {
            $query = 'SELECT `' . $key . '` AS id, `' . $fname . '` AS name FROM ' . DB_PREFIX . '_' . $table . ' WHERE `' . $fieldby . '` = ?' . ((isset($parameters['addwhere']) && $parameters['addwhere'] != '') ? ' AND ' . $parameters['addwhere'] : '') . '';
            //echo $query;
            $sorts = array();
            if (isset($parameters['sort']) && $parameters['sort'] != '') {
                if (isset($parameters['sort_dir']) && $parameters['sort_dir'] == 'desc') {
                    $sorts[] = '`' . $parameters['sort'] . '` DESC';
                } else {
                    $sorts[] = '`' . $parameters['sort'] . '` ASC';
                }
            }
            if (isset($parameters['sort2']) && $parameters['sort2'] != '') {
                if (isset($parameters['sort_dir2']) && $parameters['sort_dir2'] == 'desc') {
                    $sorts[] = '`' . $parameters['sort2'] . '` DESC';
                } else {
                    $sorts[] = '`' . $parameters['sort2'] . '` ASC';
                }
            }

            if (!empty($sorts)) {
                $query = $query . ' ORDER BY ' . implode(',', $sorts);
            } else {
                $query = $query . ' ORDER BY `' . $field . '` ASC';
            }

            if ($_REQUEST['debug'] == 1) var_dump($query);
            $stmt = $DBC->query($query, array($value));
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $ret[] = $ar;
                }
            }
        }

        /*if(isset($parameters['mode']) && $parameters['mode'] == 'checkbox'){
            $str = array();
            foreach($ret as $r){
                $str[] = '<div class="multiselect_set_item"'.($parameters['data_field']>'' ? ' data-'.$parameters['data_field'].'="'.$r['id'].'"' : '').'><label><input type="checkbox" name="' . $key . '[]" value="' . $r['id'] . '"><span>' . $r['name'] . '</span></label></div>';
            }
            return json_encode(array('html' => implode('', $str)));
        }*/
        return $ret;
        return json_encode($ret);
    }

    function array_to_json($array)
    {

        if (!is_array($array)) {
            return false;
        }

        $associative = count(array_diff(array_keys($array), array_keys(array_keys($array))));
        if ($associative) {

            $construct = array();
            foreach ($array as $key => $value) {

                // We first copy each key/value pair into a staging array,
                // formatting each key and value properly as we go.
                // Format the key:
                if (is_numeric($key)) {
                    $key = "key_$key";
                }
                $key = "\"" . addslashes($key) . "\"";

                // Format the value:
                if (is_array($value)) {
                    $value = array_to_json($value);
                } else if (!is_numeric($value) || is_string($value)) {
                    $value = "\"" . addslashes($value) . "\"";
                }

                // Add to staging array:
                $construct[] = "$key: $value";
            }

            // Then we collapse the staging array into the JSON form:
            $result = "{ " . implode(", ", $construct) . " }";
        } else { // If the array is a vector (not associative):
            $construct = array();
            foreach ($array as $value) {

                // Format the value:
                if (is_array($value)) {
                    $value = $this->array_to_json($value);
                } else if (!is_numeric($value) || is_string($value)) {
                    $value = "'" . addslashes($value) . "'";
                }

                // Add to staging array:
                $construct[] = $value;
            }

            // Then we collapse the staging array into the JSON form:
            $result = "[ " . implode(", ", $construct) . " ]";
        }

        return $result;
    }

}
