<?php
/**
 * UserDataAliasTrait — translit alias helper methods extracted from User_Data_Manager.
 *
 * Methods: createTranslitAliasByFields, makeUniqueAlias, saveTranslitAlias
 */
trait UserDataAliasTrait
{
    protected function createTranslitAliasByFields($id, $fields_for_alias)
    {
        $alias = '';
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data_shared = $data_model->get_kvartira_model(false, true);

        $form_data_shared = $data_model->init_model_data_from_db('data', 'id', $id, $form_data_shared['data'], true);
        $values = array();
        foreach ($fields_for_alias as $v) {
            $key = trim($v);
            if (isset($form_data_shared[$key])) {
                if (($form_data_shared[$key]['type'] == 'select_box_structure' || $form_data_shared[$key]['type'] == 'select_by_query' || $form_data_shared[$key]['type'] == 'select_box') && $form_data_shared[trim($v)]['value_string'] != '') {
                    $values[] = $form_data_shared[trim($v)]['value_string'];
                } elseif ($form_data_shared[trim($v)]['value'] != '') {
                    $values[] = $form_data_shared[trim($v)]['value'];
                }
            }
        }
        if (!empty($values)) {
            foreach ($values as $k => $v) {
                $values[$k] = $this->transliteMe($v);
            }
            $alias = implode('-', $values);
        }
        $unwanted_array = array('Š' => 'S', 'š' => 's', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
            'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U',
            'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y');
        $alias = strtr($alias, $unwanted_array);
        return $alias;
    }

    protected function makeUniqueAlias($alias, $id)
    {
        $is_similar_alias_exists = false;
        $DBC = DBC::getInstance();
        $query = "SELECT COUNT(*) AS cnt FROM " . DB_PREFIX . "_data WHERE translit_alias=? AND id<>? ORDER BY translit_alias DESC LIMIT 1";
        $stmt = $DBC->query($query, array($alias, $id));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ((int)$ar['cnt'] > 0) {
                $is_similar_alias_exists = true;
            }
        }

        if ($is_similar_alias_exists) {
            $query = "SELECT translit_alias FROM " . DB_PREFIX . "_data WHERE translit_alias LIKE '" . $alias . "%' AND id<>? ORDER BY translit_alias DESC LIMIT 1";
            $stmt = $DBC->query($query, array($id));
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                if (preg_match('/' . $alias . '-(\d+)/', $ar['translit_alias'], $matches)) {
                    $alias .= '-' . ((int)$matches[1] + 1);
                } else {
                    $alias .= '-1';
                }
            }
        }
        //echo $alias;
        return $alias;
    }

    protected function saveTranslitAlias($id)
    {
        $new_alias = '';
        $old_alias = '';
        if (1 === (int)$this->getConfigValue('apps.seo.allow_custom_realty_aliases')) {
            $DBC = DBC::getInstance();
            $query = 'SELECT translit_alias FROM re_data WHERE re_data.id=? LIMIT 1';
            $stmt = $DBC->query($query, array($id));
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                $old_alias = $ar['translit_alias'];
            }

            if ($old_alias == '') {
                if ('' != $this->getConfigValue('apps.seo.allow_custom_realty_aliase_fields')) {
                    $fields = explode(',', $this->getConfigValue('apps.seo.allow_custom_realty_aliase_fields'));
                    foreach ($fields as $k => $v) {
                        $fields[$k] = trim($v);
                    }
                    $new_alias = $this->createTranslitAliasByFields($id, $fields);
                }

                if ('' != $new_alias) {
                    $new_alias = $this->makeUniqueAlias($new_alias, $id);
                }
            } else {
                return;
            }
        }

        if ($new_alias == '') {
            $DBC = DBC::getInstance();
            $new_alias = $this->createTranslitAliasByFields($id, array('city_id', 'street_id', 'number'));
            if ('' != $new_alias) {
                $new_alias = $this->makeUniqueAlias($new_alias, $id);
            }
        }

        $query = 'UPDATE re_data SET translit_alias=? WHERE id=?';
        $stmt = $DBC->query($query, array($new_alias, $id));
    }
}
