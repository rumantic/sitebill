<?php
/**
 * CrudQueryTrait — extracted from Data_Model class (model.php)
 * Auto-generated, do not edit manually.
 */
trait CrudQueryTrait
{
    /**
     * Get insert query
     * @param string $table_name table name
     *
     * @param array $model_array
     * @param int $language_id
     * @return boolean
     */
    function get_insert_query($table_name, $model_array, $language_id = 0)
    {
        $set = array();
        $values = array();
        unset($model_array['image']);

        foreach ($model_array as $key => $item_array) {
            if (!isset($item_array['type'])) {
                $item_array['type'] = '';
            }

            if ($item_array['type'] == 'primary_key') {
                $primary_key = $item_array['name'];

                //echo "primary_key = $primary_key<br>";
                //echo "value = ".$model_array[$primary_key]['value'];
                continue;
            }

            if ($item_array['type'] == 'separator') {
                continue;
            }

            if ($item_array['type'] == 'spacer_text') {
                continue;
            }

            if ($item_array['type'] == 'uploads' || $item_array['type'] == 'docuploads') {
                continue;
            }

            if ($item_array['type'] == 'photo') {
                continue;
            }
            if ($item_array['type'] == 'datetime') {
                $set[] = '`' . $key . '`';
                $values[] = "'" . Sitebill_Datetime::getDatetimeCanonicalFromFormat($item_array['value'], $item_array['parameters']) . "'";
                continue;
            }
            if ($item_array['type'] == 'dtdatetime') {
                $set[] = "`" . $key . "`";
                //$values[]="'".Sitebill_Datetime::getDatetimeCanonicalFromFormat($item_array['value'], $item_array['parameters'])."'";
                $values[] = "'" . $item_array['value'] . "'";
                continue;
            }
            if ($item_array['type'] == 'dtdate') {
                $set[] = "`" . $key . "`";
                //$values[]="'".Sitebill_Datetime::getDateCanonicalFromFormat($item_array['value'], $item_array['parameters'])."'";
                $values[] = "'" . $item_array['value'] . "'";
                continue;
            }
            if ($item_array['type'] == 'dttime') {
                $set[] = "`" . $key . "`";
                //$values[]="'".Sitebill_Datetime::getTimeCanonicalFromFormat($item_array['value'], $item_array['parameters'])."'";
                $values[] = "'" . $item_array['value'] . "'";
                continue;
            }
            if ($item_array['dbtype'] == 'notable' || $item_array['dbtype'] == '0') {

                if ($item_array['type'] == 'tlocation') {

                    if (isset($item_array['parameters']['visibles'])) {
                        $visibles = explode('|', $item_array['parameters']['visibles']);
                    } else {
                        $visibles = array();
                    }


                    if (!empty($item_array['value'])) {
                        foreach ($item_array['value'] as $k => $v) {
                            if (!empty($visibles)) {
                                if (in_array($k, $visibles)) {
                                    $set[] = '`' . $k . '`';
                                    $values[] = "'" . (int)$v . "'";
                                }
                            } else {
                                $set[] = '`' . $k . '`';
                                $values[] = "'" . (int)$v . "'";
                            }
                        }
                    }
                }
                continue;
            }

            if ($item_array['type'] == 'geodata') {
                $set[] = '`' . $key . '_lat`';
                if ($item_array['value']['lat'] == '') {
                    $values[] = "NULL";
                } else {
                    $values[] = "'" . $this->escape($item_array['value']['lat']) . "'";
                }

                $set[] = '`' . $key . '_lng`';

                if ($item_array['value']['lng'] == '') {
                    $values[] = "NULL";
                } else {
                    $values[] = "'" . $this->escape($item_array['value']['lng']) . "'";
                }
                continue;
            }

            $set[] = '`' . $key . '`';
            $item_array['value'] = preg_replace('/<script.*\/script>/', '', $item_array['value']);
            $values[] = "'" . $this->escape($item_array['value']) . "'";
        }
        //echo "primary_key = $primary_key<br>";
        //echo '$this->getRequestValue($primary_key) = '.$this->getRequestValue($primary_key).'<br>';
        if ($language_id > 0) {
            $set[] = '`language_id`';
            $values[] = "'" . $language_id . "'";
            $set[] = '`link_id`';
            $values[] = "'" . $this->getRequestValue($primary_key) . "'";
        }
        $query = "insert into $table_name (" . implode(' , ', $set) . ") values (" . implode(' , ', $values) . ")";
        //echo $query;
        return $query;
    }

    function get_prepared_insert_query($table_name, $model_array, $language_id = 0)
    {

        $set = array();
        $values = array();
        unset($model_array['image']);
        $qparts = array();
        $qvals = array();

        foreach ($model_array as $key => $item_array) {
            if (!isset($item_array['type'])) {
                $item_array['type'] = '';
            }

            if ($item_array['type'] == 'primary_key') {
                $primary_key = $item_array['name'];
                continue;
            }

            if ($item_array['type'] == 'separator') {
                continue;
            }

            if ($item_array['type'] == 'select_by_query_multi') {
                continue;
            }

            if ($item_array['type'] == 'spacer_text') {
                continue;
            }

            if ($item_array['type'] == 'uploads') {
                continue;
            }

            if ($item_array['type'] == 'photo') {
                continue;
            }

            // --- FieldTypeHandler dispatch for INSERT ---
            $__ft = $item_array['type'] ?? '';
            if ($__ft !== '') {
                static $__insertRegistry = null;
                static $__insertCtx = null;
                if ($__insertRegistry === null) {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/field/FieldTypeRegistry.php';
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/field/SiteBillFieldContext.php';
                    $__insertRegistry = FieldTypeRegistry::getInstance();
                    $__insertCtx = new SiteBillFieldContext($this);
                }
                if ($__insertRegistry->has($__ft)) {
                    $__parts = $__insertRegistry->get($__ft)->toInsertParts($item_array, $__insertCtx);
                    if (!empty($__parts['columns'])) {
                        foreach ($__parts['columns'] as $i => $col) {
                            $qparts[] = $col;
                            $qvals[] = $__parts['values'][$i];
                        }
                    }
                    continue;
                }
            }
            // --- End FieldTypeHandler dispatch ---

            if ($item_array['type'] == 'parameter') {
                $qparts[] = '`' . $key . '`';

                if (isset($item_array['parameters']) && isset($item_array['parameters']['type']) && $item_array['parameters']['type'] == 'json') {
                    $qvals[] = json_encode($item_array['value']);
                } else {
                    $qvals[] = serialize($item_array['value']);
                }
                continue;
            }

            if ($item_array['type'] == 'datetime') {
                $qparts[] = '`' . $key . '`';
                $qvals[] = Sitebill_Datetime::getDatetimeCanonicalFromFormat($item_array['value'], $item_array['parameters']);
                continue;
            }
            if ($item_array['type'] == 'dtdatetime') {
                $qparts[] = "`" . $key . "`";
                $qvals[] = $item_array['value'];
                continue;
            }
            if ($item_array['type'] == 'dtdate') {
                $qparts[] = "`" . $key . "`";
                $qvals[] = $item_array['value'];
                continue;
            }
            if ($item_array['type'] == 'dttime') {
                $qparts[] = "`" . $key . "`";
                $qvals[] = $item_array['value'];
                continue;
            }
            if (isset($item_array['dbtype']) && ($item_array['dbtype'] == 'notable' || $item_array['dbtype'] == '0')) {
                if ($item_array['type'] == 'tlocation') {

                    if (isset($item_array['parameters']['visibles'])) {
                        $visibles = explode('|', $item_array['parameters']['visibles']);
                    } else {
                        $visibles = array();
                    }


                    if (!empty($item_array['value'])) {
                        foreach ($item_array['value'] as $k => $v) {
                            if (!empty($visibles)) {
                                if (in_array($k, $visibles)) {
                                    $qparts[] = "`" . $k . "`";
                                    $qvals[] = (int)$v;
                                }
                            } else {
                                $qparts[] = "`" . $k . "`";
                                $qvals[] = (int)$v;
                            }
                        }
                    }
                }
                continue;
            }

            if ($item_array['type'] == 'geodata') {
                //$qparts[] = "`".$key."_lat`";
                //$qvals[] = $item_array['value'];
                if (!isset($item_array['value']['lat']) || $item_array['value']['lat'] == '') {
                    //$values[] = "NULL";
                } else {
                    $qparts[] = "`" . $key . "_lat`";
                    $qvals[] = $this->escape($item_array['value']['lat']);
                }

                //$set[] = '`'.$key.'_lng`';

                if (!isset($item_array['value']['lng']) || $item_array['value']['lng'] == '') {
                    //$values[] = "NULL";
                } else {
                    $qparts[] = "`" . $key . "_lng`";
                    $qvals[] = $this->escape($item_array['value']['lng']);
                    //$values[] = "'".$this->escape($item_array['value']['lng'])."'";
                }
                continue;
            }
            $item_array['value'] = preg_replace('/<script.*\/script>/', '', $item_array['value']);
            //$values[] = "'".$this->escape($item_array['value'])."'";
            $qparts[] = "`" . $key . "`";
            $qvals[] = $this->escape($item_array['value']);
        }
        //echo "primary_key = $primary_key<br>";
        //echo '$this->getRequestValue($primary_key) = '.$this->getRequestValue($primary_key).'<br>';
        if ($language_id > 0) {
            //$set[] = '`language_id`';
            //$values[] = "'".$language_id."'";
            $qparts[] = "`language_id`";
            $qvals[] = $language_id;
            //$set[] = '`link_id`';
            //$values[] = "'".$this->getRequestValue($primary_key)."'";
            $qparts[] = "`link_id`";
            $qvals[] = $this->getRequestValue($primary_key);
        }
        //print_r($qparts);
        //print_r($qvals);
        //echo count($qvals);
        $count = count($qvals);
        //debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $query = 'INSERT INTO ' . $table_name . ' (' . implode(' , ', $qparts) . ') VALUES (' . implode(', ', array_fill(0, $count, '?')) . ')';

        return array('q' => $query, 'p' => $qvals);
        return $query;
    }

    /**
     * Get edit query
     * @param string $table_name table name
     * @param string $primary_key_name primary key name
     * @param int $primary_key_value primary key
     * @param array $model_array
     * @param int $language_id
     * @return boolean
     */
    function get_edit_query($table_name, $primary_key_name, $primary_key_value, $model_array, $language_id = 0)
    {
        unset($model_array['image']);

        //$set = array();
        //$values = array();
        $pairs = array();

        foreach ($model_array as $key => $item_array) {
            if (!isset($item_array['type'])) {
                $item_array['type'] = '';
            }

            if ($item_array['type'] == 'primary_key') {
                $primary_key = $item_array['name'];
                continue;
            }

            if ($item_array['type'] == 'separator') {
                continue;
            }

            if ($item_array['type'] == 'spacer_text') {
                continue;
            }

            if ($item_array['type'] == 'uploads') {
                continue;
            }

            if ($item_array['type'] == 'photo') {
                continue;
            }
            if ($item_array['type'] == 'datetime') {
                $pairs[] = "`" . $key . "` = '" . Sitebill_Datetime::getDatetimeCanonicalFromFormat($item_array['value'], $item_array['parameters']) . "'";
                continue;
            }
            if ($item_array['type'] == 'dtdatetime') {
                //$pairs[] = "`".$key."` = '".Sitebill_Datetime::getDatetimeCanonicalFromFormat($item_array['value'], $item_array['parameters'])."'";
                $pairs[] = "`" . $key . "` = '" . $item_array['value'] . "'";

                continue;
            }
            if ($item_array['type'] == 'dtdate') {
                //$pairs[] = "`".$key."` = '".Sitebill_Datetime::getDateCanonicalFromFormat($item_array['value'], $item_array['parameters'])."'";
                $pairs[] = "`" . $key . "` = '" . $item_array['value'] . "'";

                continue;
            }
            if ($item_array['type'] == 'dttime') {
                //$pairs[] = "`".$key."` = '".Sitebill_Datetime::getTimeCanonicalFromFormat($item_array['value'], $item_array['parameters'])."'";
                $pairs[] = "`" . $key . "` = '" . $item_array['value'] . "'";

                continue;
            }
            if ($item_array['dbtype'] == 'notable' || $item_array['dbtype'] == '0') {
                if ($item_array['type'] == 'tlocation') {

                    if (isset($item_array['parameters']['visibles'])) {
                        $visibles = explode('|', $item_array['parameters']['visibles']);
                    } else {
                        $visibles = array();
                    }

                    if (!empty($item_array['value'])) {
                        foreach ($item_array['value'] as $k => $v) {
                            if (!empty($visibles)) {
                                if (in_array($k, $visibles)) {
                                    $pairs[] = '`' . $k . '` = ' . (int)$v;
                                }
                            } else {
                                $pairs[] = '`' . $k . '` = ' . (int)$v;
                            }
                        }
                    }
                }
                continue;
            }
            if ($item_array['type'] == 'geodata') {
                if ($item_array['value']['lat'] == '') {
                    $pairs[] = '`' . $key . '_lat` = NULL';
                } else {
                    $pairs[] = '`' . $key . '_lat` = ' . "'" . $this->escape($item_array['value']['lat']) . "'";
                }

                if ($item_array['value']['lng'] == '') {
                    $pairs[] = '`' . $key . '_lng` = NULL';
                } else {
                    $pairs[] = '`' . $key . '_lng` = ' . "'" . $this->escape($item_array['value']['lng']) . "'";
                }


                continue;
            }


            $item_array['value'] = preg_replace('/<script.*\/script>/', '', $item_array['value']);
            $pairs[] = '`' . $key . '` = ' . "'" . $this->escape($item_array['value']) . "'";
        }
        if ($language_id > 0) {

            $pairs[] = '`language_id` = ' . "'" . $language_id . "'";
            $pairs[] = '`link_id` = ' . "'" . $this->getRequestValue($primary_key) . "'";
            $query = 'UPDATE `' . $table_name . '` SET ' . implode(', ', $pairs) . ' WHERE `link_id`=' . $primary_key_value;
        } else {
            $query = 'UPDATE `' . $table_name . '` SET ' . implode(', ', $pairs) . ' WHERE `' . $primary_key_name . '`=' . $primary_key_value;
        }

        //echo $query;
        return $query;
    }

    function get_prepared_edit_query($table_name, $primary_key_name, $primary_key_value, $model_array, $language_id = 0)
    {
        unset($model_array['image']);
        $qparts = array();
        $qvals = array();
        foreach ($model_array as $key => $item_array) {
            if (!isset($item_array['type'])) {
                $item_array['type'] = '';
            }

            if ($item_array['type'] == 'primary_key') {
                $primary_key = $item_array['name'];
                continue;
            }

            if ($item_array['type'] == 'separator') {
                continue;
            }

            if ($item_array['type'] == 'spacer_text') {
                continue;
            }

            if ($item_array['type'] == 'uploads' || $item_array['type'] == 'docuploads') {
                continue;
            }

            if ($item_array['type'] == 'avatar') {
                continue;
            }

            if ($item_array['type'] == 'photo') {
                continue;
            }
            if ($item_array['type'] == 'select_by_query_multi') {
                continue;
            }

            // --- FieldTypeHandler dispatch for EDIT ---
            $__ft2 = $item_array['type'] ?? '';
            if ($__ft2 !== '') {
                static $__editRegistry = null;
                static $__editCtx = null;
                if ($__editRegistry === null) {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/field/FieldTypeRegistry.php';
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/field/SiteBillFieldContext.php';
                    $__editRegistry = FieldTypeRegistry::getInstance();
                    $__editCtx = new SiteBillFieldContext($this);
                }
                if ($__editRegistry->has($__ft2)) {
                    $__parts2 = $__editRegistry->get($__ft2)->toEditParts($item_array, $__editCtx);
                    if (!empty($__parts2['set'])) {
                        foreach ($__parts2['set'] as $i => $setPart) {
                            $qparts[] = $setPart;
                        }
                        foreach ($__parts2['values'] as $val) {
                            $qvals[] = $val;
                        }
                    }
                    continue;
                }
            }
            // --- End FieldTypeHandler dispatch ---

            if ($item_array['type'] == 'parameter') {
                $qparts[] = '`' . $key . '`=?';
                if (isset($item_array['parameters']) && isset($item_array['parameters']['type']) && $item_array['parameters']['type'] == 'json') {
                    $qvals[] = json_encode($item_array['value']);
                } else {
                    $qvals[] = serialize($item_array['value']);
                }
                continue;
            }

            if ($item_array['type'] == 'datetime') {
                $qparts[] = '`' . $key . '`=?';
                $qvals[] = Sitebill_Datetime::getDatetimeCanonicalFromFormat($item_array['value'], $item_array['parameters']);
                continue;
            }
            if ($item_array['type'] == 'dtdatetime') {
                $qparts[] = '`' . $key . '`=?';
                $qvals[] = $item_array['value'];
                continue;
            }
            if ($item_array['type'] == 'dtdate') {
                $qparts[] = '`' . $key . '`=?';
                $qvals[] = $item_array['value'];
                continue;
            }
            if ($item_array['type'] == 'dttime') {
                $qparts[] = '`' . $key . '`=?';
                $qvals[] = $item_array['value'];
                continue;
            }
            if (isset($item_array['dbtype']) && ($item_array['dbtype'] == 'notable' || $item_array['dbtype'] == '0')) {
                if ($item_array['type'] == 'tlocation') {

                    if (isset($item_array['parameters']['visibles'])) {
                        $visibles = explode('|', $item_array['parameters']['visibles']);
                    } else {
                        $visibles = array();
                    }

                    if (!empty($item_array['value'])) {
                        foreach ($item_array['value'] as $k => $v) {
                            if (!empty($visibles)) {
                                if (in_array($k, $visibles)) {
                                    $qparts[] = '`' . $k . '`=?';
                                    $qvals[] = (int)$v;
                                }
                            } else {
                                $qparts[] = '`' . $k . '`=?';
                                $qvals[] = (int)$v;
                            }
                        }
                    }
                }
                continue;
            }
            if ($item_array['type'] == 'geodata') {

                if (@$item_array['value']['lat'] == '') {
                    $qparts[] = '`' . $key . '_lat`=NULL';
                } else {
                    $qparts[] = '`' . $key . '_lat`=?';
                    $qvals[] = $this->escape($item_array['value']['lat']);
                }

                if (@$item_array['value']['lng'] == '') {
                    $qparts[] = '`' . $key . '_lng`=NULL';
                } else {
                    $qparts[] = '`' . $key . '_lng`=?';
                    $qvals[] = $this->escape($item_array['value']['lng']);
                }


                continue;
            }


            $item_array['value'] = preg_replace('/<script.*\/script>/', '', $item_array['value']);
            $qparts[] = '`' . $key . '`=?';
            $qvals[] = $this->escape($item_array['value']);
        }
        if ($language_id > 0) {

            $qparts[] = '`language_id`=?';
            $qvals[] = $language_id;

            $qparts[] = '`link_id`=?';
            $qvals[] = $this->getRequestValue($primary_key);

            $query = 'UPDATE `' . $table_name . '` SET ' . implode(', ', $qparts) . ' WHERE `link_id`=' . $primary_key_value;
        } else {
            $query = 'UPDATE `' . $table_name . '` SET ' . implode(', ', $qparts) . ' WHERE `' . $primary_key_name . '`=' . $primary_key_value;
        }

        return array('q' => $query, 'p' => $qvals);
    }

    public function add_new_record($table, $field, $primary_key, $value)
    {
        $DBC = DBC::getInstance();
        $query = 'INSERT INTO ' . DB_PREFIX . '_' . $table . ' (`' . $field . '`) VALUES (?)';
        //echo $query.'<br>';
        $stmt = $DBC->query($query, array($value));
        $this->writeLog($DBC->getLastError());
        if ($stmt) {
            return true;
        }
        return false;
    }

}
