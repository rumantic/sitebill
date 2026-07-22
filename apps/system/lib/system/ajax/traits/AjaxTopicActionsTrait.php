<?php
/**
 * AjaxTopicActionsTrait — extracted from Ajax_Server (ajax_server.php)
 */
trait AjaxTopicActionsTrait
{
    protected function _save_topic_sortAjaxAction()
    {
        $result = array(
            'status' => 0,
            'message' => 'Access denied'
        );
        if ($this->ajax_user_mode == 'admin') {
            $ids = array();
            $parent_id = intval($this->getRequestValue('parent_topic_id'));
            $ids = $this->getRequestValue('child_topics');

            if (!empty($ids)) {
                $ids = array_filter($ids, function ($el) {
                    return intval($el) > 0;
                });
            }

            //$ids = explode(',', $this->getRequestValue('child_topics'));
            if (!empty($ids) && !in_array($parent_id, $ids)) {
                ksort($ids);
                $DBC = DBC::getInstance();
                $query = 'UPDATE ' . DB_PREFIX . '_topic SET `parent_id`=?, `order`=? WHERE `id`=?';
                foreach ($ids as $k => $id) {
                    $stmt = $DBC->query($query, array($parent_id, $k, $id));
                }
                $result['message'] = 'OK';
                $result['status'] = 1;
                $result['newsort'] = array_flip($ids);

            } else {
                $result['message'] = 'Invalid data';
            }
        }
        return json_encode($result);
        exit();
    }

    protected function _save_rubric_sortAjaxAction()
    {
        if ($this->ajax_user_mode == 'admin') {
            $ids = array();
            $parent_id = (int)$this->getRequestValue('parent_topic_id');
            $ids = explode(',', $this->getRequestValue('child_topics'));
            if (!empty($ids) && !in_array($parent_id, $ids)) {
                $DBC = DBC::getInstance();
                $query = 'UPDATE ' . DB_PREFIX . '_rubricator_point SET `parent_point_id`=?, `sort_order`=? WHERE `rubricator_point_id`=?';
                foreach ($ids as $k => $id) {
                    $stmt = $DBC->query($query, array($parent_id, $k, $id));
                }
            }
        }
        exit();
    }

    protected function _topic_sourceAjaxAction()
    {
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
        $ATH = new Admin_Table_Helper();
        $form_data = $ATH->load_model('topic', true);
        $form_data = $form_data['topic'];

        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php';
        $id = (int)$this->getRequestValue('id');
        $DBC = DBC::getInstance();
        $result = array();

        $fields = [
            '`id`',
            '`url`',
            '`order`'
        ];

        if ($this->getConfigValue('use_topic_publish_status')) {
            $fields[] = '`published`';
        }

        $namenativefield = 'name';
        $postfix = $this->getLangPostfix($this->getCurrentLang());

        if(isset($form_data[$namenativefield.$postfix])){
            $fields[] = '`'.$namenativefield.$postfix.'` AS name';
        }else{
            $fields[] = '`'.$namenativefield.'`';
        }

        $query = 'SELECT '.implode(', ', $fields).' FROM ' . DB_PREFIX . '_topic WHERE `parent_id`=? ORDER BY `order` ASC, `name` ASC';

        $stmt = $DBC->query($query, array($id));
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $node = array();
                $node['id'] = $ar['id'];
                $node['text'] = SiteBill::iconv(SITE_ENCODING, 'utf-8', $ar['name']);
                if ($ar['url'] != '') {
                    $node['url'] = SiteBill::iconv(SITE_ENCODING, 'utf-8', $ar['url']);
                } else {
                    $node['url'] = '';
                }
                $node['order'] = $ar['order'];
                $node['state'] = Structure_Manager::has_child($ar['id']) ? 'closed' : 'open';
                if ($this->getConfigValue('use_topic_publish_status')) {
                    $node['published'] = $ar['published'];
                }
                array_push($result, $node);
            }
        }

        echo json_encode($result);
        exit();
    }

    protected function _set_realty_statusAjaxAction()
    {
        $id = (int)$this->getRequestValue('id');
        $status = (int)$this->getRequestValue('status');

        $need_send_message = 0;

        if (1 === (int)$this->getConfigValue('notify_about_publishing') || 1 === (int)$this->getConfigValue('apps.twitter.enable')) {
            $DBC = DBC::getInstance();
            $query = 'SELECT active, email, user_id, fio FROM ' . DB_PREFIX . '_data WHERE `id`=?';

            $stmt = $DBC->query($query, array($id));
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                $current_active_status = $ar['active'];
                $email = $ar['email'];
                $phone = $ar['phone'];
                $fio = $ar['fio'];
                $owner_id = $ar['user_id'];
            }

            if ($current_active_status == 0 and $status == 1) {
                $need_send_message = 1;
            }
        }

        $DBC = DBC::getInstance();
        if ($this->ajax_user_mode == 'admin') {
            $query = 'UPDATE ' . DB_PREFIX . '_data SET `active`=? WHERE `id`=?';
            $stmt = $DBC->query($query, array($status, $id));
        } elseif ($this->ajax_user_mode == 'user') {
            $access_allow = false;
            if ($this->getConfigValue('check_permissions') && (1 != (int)$this->getConfigValue('data_adv_share_access'))) {
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php');
                $permission = new Permission();
                if ($permission->get_access($_SESSION['user_id_value'], 'data', 'access')) {
                    $access_allow = true;
                }
            }

            if ($access_allow) {
                $query = 'UPDATE ' . DB_PREFIX . '_data SET `active`=? WHERE `id`=?';
                $stmt = $DBC->query($query, array($status, $id));
            } else {
                $query = 'UPDATE ' . DB_PREFIX . '_data SET `active`=? WHERE `id`=? AND user_id=?';
                $stmt = $DBC->query($query, array($status, $id, $this->ajax_controller_user_id));
            }
        } else {
            return 'ERROR';
        }

        if ($stmt) {
            if ($need_send_message == 1 && $email != '') {
                if ($owner_id > 0) {
                    $DBC = DBC::getInstance();
                    $query = 'SELECT email, user_id, fio, group_id, login FROM ' . DB_PREFIX . '_user WHERE user_id=?';
                    $stmt = $DBC->query($query, array($owner_id));
                    if ($stmt) {
                        $ar = $DBC->fetch($stmt);
                        if ($ar['login'] != '_unregistered') {
                            $email = $ar['email'];
                            $phone = $ar['phone'];
                            $fio = $ar['fio'];
                        }
                    }
                }
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/data/data_manager.php';
                $DM = new Data_Manager();
                $DM->notifyEmailAboutActivation($id, $email, array('fio' => $fio));
            }
            return 'OK';
        } else {
            return 'ERROR';
        }
        exit();
    }

    protected function _topic_publishAjaxAction()
    {
        if ($this->ajax_user_mode != 'admin') {
            echo json_encode(array('status' => 0, 'message' => 'have no access'));
            exit();
        }
        $use_topic_publish_status = intval($this->getConfigValue('use_topic_publish_status'));
        if (!$use_topic_publish_status) {
            echo json_encode(array('status' => 0, 'message' => 'option disabled'));
            exit();
        }

        $status = intval($this->getRequestValue('status'));
        $id = intval($this->getRequestValue('id'));

        $DBC = DBC::getInstance();
        $query = 'SELECT published FROM ' . DB_PREFIX . '_topic WHERE id = ?';
        $stmt = $DBC->query($query, array($id));
        if (!$stmt) {
            echo json_encode(array('status' => 0, 'message' => 'item not found'));
            exit();
        }
        $ar = $DBC->fetch($stmt);
        $prevstatus = intval($ar['published']);

        if ($prevstatus == $status) {
            echo json_encode(array('status' => 0, 'message' => 'no changes required'));
            exit();
        }

        $query = 'UPDATE ' . DB_PREFIX . '_topic SET published = ? WHERE id = ?';
        $stmt = $DBC->query($query, array($status, $id));
        if (!$stmt) {
            echo json_encode(array('status' => 0, 'message' => 'it is impossible to change the status'));
            exit();
        }

        echo json_encode(array('status' => 1, 'message' => '', 'newstatus' => $status));
        exit();

    }

    protected function _topic_deleteAjaxAction()
    {
        if ($this->ajax_user_mode != 'admin') {
            echo json_encode(array('status' => 0, 'message' => 'have no access'));
            exit();
        }
        $clear_option = (string)$this->getRequestValue('clear_option');
        $clear_advs = (string)$this->getRequestValue('clear_advs');
        $id = (int)$this->getRequestValue('id');

        if ($clear_option === '' && $clear_advs === '') {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php';
            $Structure_Manager = new Structure_Manager();

            $message = '';
            $status = 1;
            $DBC = DBC::getInstance();

            $category_structure = $Structure_Manager->loadCategoryStructure();
            if (count($category_structure['childs'][$id]) > 0) {
                $message .= Multilanguage::_('CATEGORY_HAS_CHILDS', 'system') . '<br>';
                $status = 0;
            }

            $query = 'SELECT COUNT(*) AS rs FROM ' . DB_PREFIX . '_data WHERE topic_id=?';
            $stmt = $DBC->query($query, array($id));
            $ar = $DBC->fetch($stmt);
            if ($ar['rs'] != 0) {
                $message .= Multilanguage::_('NOT_EMPTY_CATEGORY', 'system') . '<br>';
                $status = 0;
            }
            if ($status == 1) {
                $Structure_Manager->deleteRecord($id);
            }
            $result = array('status' => $status, 'message' => $message);
        } else {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php';
            $Structure_Manager = new Structure_Manager();
            $Structure_Manager->deleteTopicItem($id, $clear_option, $clear_advs);
            $message = '';
            $status = 1;
            $result = array('status' => $status, 'message' => $message);
        }
        echo json_encode($result);
        exit();
    }

    protected function _add_noteAjaxAction()
    {
        $id = (int)$this->getRequestValue('id');
        $note = trim(strip_tags($this->getRequestValue('note')));


        $DBC = DBC::getInstance();
        if ($this->ajax_user_mode == 'admin') {
            $query = 'INSERT INTO ' . DB_PREFIX . '_data_note (id, added_at, message, user_id) VALUES (?,?,?,?)';
            $stmt = $DBC->query($query, array($id, date('Y-m-d H:i:s', time()), $note, $this->ajax_controller_user_id));
            //$this->writeLog($DBC->getLastError());
        } else {
            return json_encode(array('status' => 0));
        }

        if ($stmt) {
            $note_id = $DBC->lastInsertId();
            $ret = '<div class="itemdiv commentdiv">
									<div class="body">
										<div class="name">
											<a href="#">Я</a>
										</div>

										<div class="time">
											<i class="ace-icon fa fa-clock-o"></i>
											<span class="green">' . date('Y-m-d H:i:s', time()) . '</span>
										</div>

										<div class="text">
											<i class="ace-icon fa fa-quote-left"></i>' . nl2br($note) . '
										</div>
									</div>

									<div class="tools">
										<div class="action-buttons bigger-125">
											<a href="#" class="delete_note" data-id="' . $note_id . '">
												<i class="ace-icon fa fa-trash-o red"></i>
											</a>
										</div>
									</div>
								</div>';
            return json_encode(array('status' => 1, 'note' => $note, 'note_id' => $note_id, 'html' => $ret));
        } else {
            return json_encode(array('status' => 0));
        }
        exit();
    }

    protected function _delete_noteAjaxAction()
    {
        $note_id = (int)$this->getRequestValue('note_id');
        //$note=trim(strip_tags($this->getRequestValue('note')));


        $DBC = DBC::getInstance();
        if ($this->ajax_user_mode == 'admin') {
            $query = 'DELETE FROM ' . DB_PREFIX . '_data_note WHERE data_note_id=?';
            $stmt = $DBC->query($query, array($note_id));
        } else {
            $query = 'DELETE FROM ' . DB_PREFIX . '_data_note WHERE data_note_id=? AND user_id=?';
            $stmt = $DBC->query($query, array($note_id, $this->ajax_controller_user_id));
        }

        if ($stmt) {
            return json_encode(array('status' => 1));
        } else {
            return json_encode(array('status' => 0));
        }
        exit();
    }

    function refresh_realty_date_added ($realty_id) {
        $DBC = DBC::getInstance();
        $query = 'UPDATE ' . DB_PREFIX . '_data SET `date_added` = ? WHERE `id` = ?';
        $stmt = $DBC->query($query, array(date('Y-m-d H:i:s'), $realty_id));
        $log_message = __METHOD__.' realty_id = '.$realty_id;
        if ( $DBC->getLastError() ) {
            $log_message.= ' error: '.$DBC->getLastError();
        }
        $this->writeLog($log_message);
    }

}
