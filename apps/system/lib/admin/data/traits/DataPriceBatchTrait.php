<?php
/**
 * DataPriceBatchTrait — Batch price editing actions for Data_Manager.
 *
 * Extracted from data_manager.php during refactoring.
 * Methods: _batch_field_editAction(), _weditAction()
 */
trait DataPriceBatchTrait
{
    protected function _batch_field_editAction() {
        $field = $this->getRequestValue('field');
        if (!isset($this->data_model[$this->table_name][$field]) || $this->data_model[$this->table_name][$field]['type'] !== 'price') {
            return '';
        }
        $ids = $this->getRequestValue('id');
        if (!is_array($ids) || empty($ids)) {
            return '';
        }
        if ($_SESSION['current_user_group_name'] !== 'admin') {
            foreach ($ids as $k => $id) {
                if (!$this->checkOwning($id, $_SESSION['user_id'])) {
                    unset($ids[$k]);
                }
            }
        }

        if (empty($ids)) {
            return '';
        }

        if (strtolower($_SERVER['REQUEST_METHOD']) === 'post') {

            if (!isset($_POST['step']) || empty($_POST['step'])) {
                return '---';
            }

            $vals = array();
            $DBC = DBC::getInstance();

            $ids = array_map('intval', $ids);
            $query = 'SELECT `id`, `' . $field . '` FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE id IN (' . implode(',', $ids) . ')';

            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $vals[$ar['id']] = $ar[$field];
                }
            }

            if (empty($vals)) {
                return '---';
            }



            foreach ($vals as $id => $price) {

                foreach ($_POST['step'] as $step) {

                    if (isset($step['perc_diff'])) {
                        $val = $step['perc_diff'];
                        $dir = $step['perc_diff_dir'];
                        if ($dir != 'minus') {
                            $dir = 'plus';
                        }
                        if ($val > 0) {
                            if ($dir == 'minus') {
                                $price = $price - ($price * $val / 100);
                            } else {
                                $price = $price + ($price * $val / 100);
                            }
                        }
                    } elseif (isset($step['round'])) {
                        $val = $step['round'];
                        $dir = $step['round_dir'];
                        if ($dir != 'min' && $dir != 'max') {
                            $dir = 'near';
                        }
                        if ($val > 0) {
                            $k = 1;
                            switch ($dir) {
                                case 'max' : {
                                        $k = ceil($price / $val);
                                        break;
                                    }
                                case 'min' : {
                                        $k = floor($price / $val);
                                        break;
                                    }
                                case 'near' : {
                                        if (($price % $val) >= $val / 2) {
                                            $k = floor($price / $val) + 1;
                                        } else {
                                            $k = floor($price / $val);
                                        }
                                        break;
                                    }
                            }
                            $price = $k * $val;
                        }
                    } elseif (isset($step['summ_diff'])) {
                        $val = $step['summ_diff'];
                        $dir = $step['summ_diff_dir'];
                        if ($dir != 'minus') {
                            $dir = 'plus';
                        }
                        if ($val > 0) {
                            if ($dir == 'minus') {
                                $price = $price - $val;
                            } else {
                                $price = $price + $val;
                            }
                        }
                    }
                    $vals[$id] = $price;
                }
            }
            $query = 'UPDATE ' . DB_PREFIX . '_' . $this->table_name . ' SET `' . $field . '`=? WHERE `id`=?';
            foreach ($vals as $id => $price) {
                $stmt = $DBC->query($query, array($price, $id));
            }

            return '<div class="alert">Изменения применены. Изменено объектов: ' . count($vals) . '</div>';
        } else {
            global $smarty;
            $vals = array();
            $DBC = DBC::getInstance();
            $ids = array_map('intval', $ids);
            $query = 'SELECT `' . $field . '` FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE id IN (' . implode(',', $ids) . ') LIMIT 100';


            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $vals[] = $ar[$field];
                }
            }
            if (empty($vals)) {
                return '';
            }
            $smarty->assign('field_name', $field);
            $smarty->assign('ids', $ids);
            $smarty->assign('field_vals', json_encode($vals));
            return $smarty->fetch($this->getAdminTplFolder() . '/batch_field_edit.tpl');
        }
    }

    protected function _weditAction() {
        //print_r($this->data_model);
        $field = $this->getRequestValue('field');
        if (!isset($this->data_model[$this->table_name][$field]) || $this->data_model[$this->table_name][$field]['type'] !== 'price') {
            return '';
        }

        if (strtolower($_SERVER['REQUEST_METHOD']) === 'post') {

            $topic_id = (int)$this->getRequestValue('topic_id');
            if (!isset($_POST['step']) || empty($_POST['step'])) {
                return '---';
            }

            $vals = array();
            $DBC = DBC::getInstance();

            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
            $Structure_Manager = new Structure_Manager();
            if ($topic_id > 0) {
                $category_structure = $Structure_Manager->loadCategoryStructure();
                $c = $Structure_Manager->get_all_childs($topic_id, $category_structure);
                $c[] = $topic_id;
                $query = 'SELECT `id`, `' . $field . '` FROM ' . DB_PREFIX . '_' . $this->table_name . (count($c) > 0 ? ' WHERE topic_id IN (' . implode(',', $c) . ')' : '');
            } else {
                $query = 'SELECT `id`, `' . $field . '` FROM ' . DB_PREFIX . '_' . $this->table_name;
            }


            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $vals[$ar['id']] = $ar[$field];
                }
            }

            if (empty($vals)) {
                return '---';
            }



            foreach ($vals as $id => $price) {

                foreach ($_POST['step'] as $step) {

                    if (isset($step['perc_diff'])) {
                        $val = $step['perc_diff'];
                        $dir = $step['perc_diff_dir'];
                        if ($dir != 'minus') {
                            $dir = 'plus';
                        }
                        if ($val > 0) {
                            if ($dir == 'minus') {
                                $price = $price - ($price * $val / 100);
                            } else {
                                $price = $price + ($price * $val / 100);
                            }
                        }
                    } elseif (isset($step['round'])) {
                        $val = $step['round'];
                        $dir = $step['round_dir'];
                        if ($dir != 'min' && $dir != 'max') {
                            $dir = 'near';
                        }
                        if ($val > 0) {
                            $k = 1;
                            switch ($dir) {
                                case 'max' : {
                                        $k = ceil($price / $val);
                                        break;
                                    }
                                case 'min' : {
                                        $k = floor($price / $val);
                                        break;
                                    }
                                case 'near' : {
                                        if (($price % $val) >= $val / 2) {
                                            $k = floor($price / $val) + 1;
                                        } else {
                                            $k = floor($price / $val);
                                        }
                                        break;
                                    }
                            }
                            $price = $k * $val;
                        }
                    } elseif (isset($step['summ_diff'])) {
                        $val = $step['summ_diff'];
                        $dir = $step['summ_diff_dir'];
                        if ($dir != 'minus') {
                            $dir = 'plus';
                        }
                        if ($val > 0) {
                            if ($dir == 'minus') {
                                $price = $price - $val;
                            } else {
                                $price = $price + $val;
                            }
                        }
                    }
                    $vals[$id] = $price;
                }
            }
            $query = 'UPDATE ' . DB_PREFIX . '_' . $this->table_name . ' SET `' . $field . '`=? WHERE `id`=?';
            foreach ($vals as $id => $price) {
                $stmt = $DBC->query($query, array($price, $id));
            }

            return 'Изменения применены. Изменено объектов: ' . count($vals);
        } else {
            global $smarty;
            $topic_id = intval($this->getRequestValue('topic_id'));

            $vals = array();
            $DBC = DBC::getInstance();

            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
            $Structure_Manager = new Structure_Manager();
            if ($topic_id > 0) {
                $category_structure = $Structure_Manager->loadCategoryStructure();
                $c = $Structure_Manager->get_all_childs($topic_id, $category_structure);
                $c[] = $topic_id;
                $query = 'SELECT `' . $field . '` FROM ' . DB_PREFIX . '_' . $this->table_name . (count($c) > 0 ? ' WHERE topic_id IN (' . implode(',', $c) . ')' : '') . ' LIMIT 100';
            } else {
                $query = 'SELECT `' . $field . '` FROM ' . DB_PREFIX . '_' . $this->table_name . ' LIMIT 100';
            }


            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $vals[] = $ar[$field];
                }
            }
            if (empty($vals)) {
                $smarty->assign('no_val_avial', 1);
            }
            $smarty->assign('topic_id', $topic_id);
            $smarty->assign('field_name', $field);
            $smarty->assign('structure_box', $Structure_Manager->getCategorySelectBoxWithName('topic_id', $topic_id));
            $smarty->assign('field_vals', json_encode($vals));
            return $smarty->fetch($this->getAdminTplFolder() . '/wedit.tpl');
        }
    }
}
