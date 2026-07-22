<?php
/**
 * ObjectAliasTrait — Translit alias generation methods extracted from Object_Manager.
 *
 * Methods: createTranslitAliasByFields, makeUniqueAlias, saveTranslitAlias
 *
 * @see Object_Manager
 */

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

trait ObjectAliasTrait
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
            if ((int) $ar['cnt'] > 0) {
                $is_similar_alias_exists = true;
            }
        }

        if ($is_similar_alias_exists) {
            $is_alias_cathed = false;
            $query = "SELECT translit_alias FROM " . DB_PREFIX . "_data WHERE translit_alias LIKE '" . $alias . "%' AND id<>? ORDER BY LENGTH(translit_alias) DESC, translit_alias DESC";
            $stmt = $DBC->query($query, array($id));
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    if (preg_match('/' . $alias . '-(\d+)$/', $ar['translit_alias'], $matches)) {
                        $alias .= '-' . ((int) $matches[1] + 1);
                        $is_alias_cathed = true;
                        break;
                    }
                }
            }
            if (!$is_alias_cathed) {
                $alias .= '-1';
            }
        }
        return $alias;
    }

    protected function saveTranslitAlias($id)
    {
        $new_alias = '';
        if (1 == $this->getConfigValue('apps.seo.allow_custom_realty_aliases')) {
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
