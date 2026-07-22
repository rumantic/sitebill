<?php
/**
 * DataNotificationTrait — Notification, watermark, and VIP date methods for Data_Manager.
 *
 * Extracted from data_manager.php during refactoring.
 * Methods: notifyEmailAboutActivation(), do_watermark(), prepareVipStatsDateValue()
 */
trait DataNotificationTrait
{
    public function notifyEmailAboutActivation($n_id, $n_email, $data = array()) {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $DBC = DBC::getInstance();
        $SM = new Structure_Manager();

        $category_structure = $SM->loadCategoryStructure();
        if (1 == $this->getConfigValue('apps.seo.data_alias_enable')) {
            $query = 'SELECT translit_alias, topic_id FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE ' . $this->primary_key . '=? LIMIT 1';
        } else {
            $query = 'SELECT topic_id FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE ' . $this->primary_key . '=? LIMIT 1';
        }

        $stmt = $DBC->query($query, array($n_id));
        if ($stmt) {
            $seo_data = $DBC->fetch($stmt);
        } else {
            $seo_data = array();
        }

        $href = $this->getRealtyHREF($n_id, true, array('topic_id' => $seo_data['topic_id'], 'alias' => $seo_data['translit_alias']));
        $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/system/template/mails/reguser_pub_notify.tpl';
        global $smarty;
        if (isset($data['fio']) && $data['fio'] != '') {
            $smarty->assign('mail_fio', $data['fio']);
            $smarty->assign('fio', $data['fio']);
        } else {
            $smarty->assign('mail_fio', '');
            $smarty->assign('fio', '');
        }
        $smarty->assign('href', $href);
        $smarty->assign('edit_url', $this->getServerFullUrl() . '/account/data/?do=edit&id=' . $n_id);
        $smarty->assign('mail_adv_id', $n_id);

        $smarty->assign('mail_signature', $this->getConfigValue('email_signature'));
        if (file_exists($tpl)) {
            $body = $smarty->fetch($tpl);
        } else {
            $body = Multilanguage::_('YOUR_AD_PUBLISHED', 'system') . '<br />';
            $body .= Multilanguage::_('AD_LINK', 'system') . ' <a href="' . $href . '">' . $href . '</a><br />';
        }
        $subject = $_SERVER['SERVER_NAME'] . ': ' . Multilanguage::_('YOUR_AD_PUBLISHED_SUBJ', 'system');
        $from = $this->getConfigValue('system_email');

        $this->template->assign('HTTP_HOST', $_SERVER['HTTP_HOST']);
        $email_template_fetched = $this->fetch_email_template('data_moderate_success');

        if ($email_template_fetched) {
            $subject = $email_template_fetched['subject'];
            $body = $email_template_fetched['message'];

            $message_array['apps_name'] = 'need_moderate';
            $message_array['method'] = __METHOD__;
            $message_array['message'] = "subject = $subject, message = $body";
            $message_array['type'] = '';
            //$this->writeLog($message_array);
        }

        $this->sendFirmMail($n_email, $from, $subject, $body);
    }

    function do_watermark($imgs, $position = '', $offset_left = '', $offset_top = '', $offset_right = '', $offset_bottom = '') {
        $filespath = SITEBILL_DOCUMENT_ROOT . '/img/data/';
        $Watermark = $this->createWatermarkInstance(true);
        if($position !== ''){
            $Watermark->setPosition($position);
        }
        if($offset_left !== '' || $offset_top !== '' || $offset_right !== '' || $offset_bottom !== ''){
            $Watermark->setOffsets(array(
                $offset_left,
                $offset_top,
                $offset_right,
                $offset_bottom
            ));
        }
        $preview_width = $this->getConfigValue('data_image_preview_width');
        if ($preview_width == '') {
            $preview_width = $this->getConfigValue('news_image_preview_width');
        }
        $preview_height = $this->getConfigValue('data_image_preview_height');
        if ($preview_height == '') {
            $preview_height = $this->getConfigValue('news_image_preview_height');
        }


        if (defined('STR_MEDIA') && STR_MEDIA == Sitebill::MEDIA_SAVE_FOLDER) {
            /*
             * TODO
             * перенести создание папки под сохранение копий безвотермарка внутрь условия требования такого сохранения
             */
            $copy_folder = MEDIA_FOLDER . '/nowatermark/';
            if ( !is_dir($copy_folder) ) {
                mkdir($copy_folder);
            }
            if (defined('STR_MEDIA_FOLDERFDAYS') && STR_MEDIA_FOLDERFDAYS === 1) {
                $foldeformat = 'Ymd';
            } else {
                $foldeformat = 'Ym';
            }
            $folder_name = date($foldeformat, time());
            $locs = $copy_folder . '/' . $folder_name;
            if (!is_dir($locs)) {
                mkdir($locs);
            }
            if (1 == $this->getConfigValue('save_without_watermark') && !empty($imgs)) {
                $copy_folder = SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark';
                foreach ($imgs as $v) {
                    if ( file_exists($filespath . $v['normal']) ) {
                        copy($filespath . $v['normal'], $copy_folder . '/' . $v['normal']);
                    }
                }
            }
            if (!empty($imgs)) {
                foreach ($imgs as $v) {
                    $Watermark->printWatermark(MEDIA_FOLDER . '/' . $v['normal']);
                    if ($this->getConfigValue('apps.watermark.preview_enable')) {
                        $Watermark->printWatermark($filespath . $v['preview'], true);
                        //$this->makePreview(MEDIA_FOLDER . '/' . $v['preview'], MEDIA_FOLDER . '/' . $v['preview'], $preview_width, $preview_height);
                    }
                }
            }
        } else {
            if (1 == $this->getConfigValue('save_without_watermark') && !empty($imgs)) {
                $copy_folder = SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/';
                foreach ($imgs as $v) {
                    /*
                      Обработчик создания папок для варианта с размещением изображений в подпапках
                      $path_parts=explode('/', $v['normal']);
                      if(count($path_parts)==3){
                      $locs=$copy_folder.$path_parts[0];
                      if (!is_dir($locs)) {
                      mkdir($locs);
                      }
                      $locs = $copy_folder.$path_parts[0].'/'.$path_parts[1];
                      if (!is_dir($locs)) {
                      mkdir($locs);
                      }
                      }
                     */
                    copy($filespath . $v['normal'], $copy_folder . $v['normal']);
                }
            }
            if (!empty($imgs)) {
                foreach ($imgs as $v) {
                    $Watermark->printWatermark($filespath . $v['normal']);
                    if ($this->getConfigValue('apps.watermark.preview_enable')) {
                        $Watermark->printWatermark($filespath . $v['preview'], true);
                        //$this->makePreview(MEDIA_FOLDER . '/' . $v['preview'], MEDIA_FOLDER . '/' . $v['preview'], $preview_width, $preview_height);
                    }
                }
            }
        }
    }

    private function prepareVipStatsDateValue($current_vip_timestamp, $new_vip_timestamp) {
        $ret = 0;
        if ($current_vip_timestamp < time()) {
            $current_vip_timestamp = 0;
        }

        if ($current_vip_timestamp != 0) {
            $olddate = date('d.m.Y', $current_vip_timestamp);
            $oldtime = date('H:i:s', $current_vip_timestamp);
            $newdate = date('d.m.Y', $new_vip_timestamp);
            if ($newdate != $olddate) {
                $ret = strtotime($newdate . ' ' . $oldtime);
            } else {
                $ret = FALSE;
            }
        } else {
            if ($new_vip_timestamp == '' || $new_vip_timestamp == 0) {
                $ret = 0;
            } else {
                $newdate = date('d.m.Y', $new_vip_timestamp);
                $ret = strtotime($newdate . ' ' . date('H:i:s', time()));
            }
        }
        return $ret;
    }
}
