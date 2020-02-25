<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Stat REST class
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class API_stat extends API_Common {

    public function _common() {
        $DBC = DBC::getInstance();
        $ra = array(
            'data_total' => 0,
            'data_notactive' => 0,
            'user_total' => 0,
            'client_total' => 0
        );

        //получаем количество всех заявок
        $query = 'SELECT count(id) as total FROM ' . DB_PREFIX . '_data';
        $stmt = $DBC->query($query);
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $ra['data_total'] = $ar['total'];
        }

        //получаем количество неактивных заявок
        $query = 'SELECT count(id) as notactive FROM ' . DB_PREFIX . '_data WHERE active=0';
        $stmt = $DBC->query($query);
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $ra['data_notactive'] = $ar['notactive'];
        }

        //получаем количество пользователей
        $query = 'SELECT count(user_id) as user_total FROM ' . DB_PREFIX . '_user';
        $stmt = $DBC->query($query);
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $ra['user_total'] = $ar['user_total'];
        }

        //получаем количество заявок из таблицы client
        $query = 'SELECT count(client_id) as client_total FROM ' . DB_PREFIX . '_client';
        $stmt = $DBC->query($query);
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $ra['client_total'] = $ar['client_total'];
        }
        $ra['success'] = 1;



        return $this->json_string($ra);
    }

    public function _load_file() {
        $upload_result = false;
        $realty_id = (int) $this->getRequestValue('realty_id');
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $data_model->get_kvartira_model($this->getConfigValue('ajax_form_in_admin'));

        //$this->writeLog(array('apps_name'=>'apps.api', 'method' => __METHOD__, 'message' => 'files = '.var_export($_FILES, true).', realty_id = '.$realty_id, 'type' => NOTICE));

        if (move_uploaded_file($_FILES['uploadedfile']['tmp_name'], SITEBILL_DOCUMENT_ROOT . "/cache/upl/" . $_FILES['uploadedfile']['name'])) {
            $upload_result = true;
            $images = array(0 => $_FILES['uploadedfile']['name']);
            //$this->writeLog(array('apps_name'=>'apps.api', 'method' => __METHOD__, 'message' => 'images = '.var_export($images, true), 'type' => NOTICE));

            $imgs = array();
            $updated = false;


            //$this->writeLog(array('apps_name'=>'apps.api', 'method' => __METHOD__, 'message' => 'form_data = '.var_export($form_data, true), 'type' => NOTICE));
            $this->setExternalUploadifyImageArray($images);

            foreach ($form_data['data'] as $form_item) {
                if ($form_item['type'] == 'uploads') {

                    //$this->writeLog(array('apps_name'=>'apps.api', 'method' => __METHOD__, 'message' => 'start uploads', 'type' => NOTICE));

                    $ims = $this->appendUploads('data', $form_item, 'id', $realty_id);
                    $updated = true;
                }
            }
            if (!$updated) {
                //$this->writeLog(array('apps_name'=>'apps.api', 'method' => __METHOD__, 'message' => 'start image multi', 'type' => NOTICE));
                $ims = $this->editImageMulti('data', 'data', 'id', $realty_id);
            }
            if ($this->getExternalUploadifyImageArray()) {
                $this->delete_external_images_from_upl($this->getExternalUploadifyImageArray());
            }
        }

        $result_array['success'] = $upload_result;
        $ra['result'] = $upload_result;
        $ra['result_image'] = $ims;
        $ra['success'] = $upload_result;
        return $this->json_string($ra);
    }

    public function delete_external_images_from_upl($images) {
        foreach ($images as $key => $image_name) {
            if (is_file(SITEBILL_DOCUMENT_ROOT . "/cache/upl/" . $image_name)) {
                @unlink(SITEBILL_DOCUMENT_ROOT . "/cache/upl/" . $image_name);
            }
        }
        return true;
    }

    public function _realty_view() {
        $realty_id = $this->getRequestValue('realty_id');
        $_REQUEST['REST_API'] = 1;
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/realtyview/admin/admin.php');
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/realtyview/site/site.php');
        $realtyview_site = new realtyview_site();
        $realtyview_site->setRealtyID($realty_id);
        $realtyview_site->showRealty();
        $static_data = Static_Data::getInstance();
        $result_array['success'] = 1;
        $result_array['data'] = $static_data::get_data();

        if ($result_array['data']['user_id']['value'] > 0) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/users/user_object_manager.php');
            $user_object_manager = new User_Object_Manager();
            $result_array['user'] = $user_object_manager->load_by_id($result_array['data']['user_id']['value']);
        }
        return $this->json_string($result_array);
    }

    public function _get_city_list($to_array = false) {
        $result_array = array();
        $DBC = DBC::getInstance();
        $query = "select distinct(d.city_id), c.name from " . DB_PREFIX . "_data d, " . DB_PREFIX . "_city c where d.city_id=c.city_id and c.name <> '' order by c.name";
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ra[] = $ar;
            }
        }
        if ($to_array) {
            return $ra;
        }
        $result_array['success'] = 1;
        $result_array['city_list'] = $ra;
        return $this->json_string($result_array);
    }

    public function _data_grid() {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $Structure_Manager->getCategorySelectBoxWithName('topic_id', $this->getRequestValue('topic_id'));
        $_SERVER['REQUEST_URI'] = '/';
        $_REQUEST['REST_API'] = 1;

        $sitebill_krascap = new SiteBill_Krascap();
        $sitebill_krascap->grid_adv();
        $static_data = Static_Data::getInstance();
        $result_array['success'] = 1;
        $result_array['data'] = $static_data::get_data();
        $result_array['params'] = $static_data::get_params();
        return $this->json_string($result_array);
    }

    public function _my_data_grid() {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $Structure_Manager->getCategorySelectBoxWithName('topic_id', $this->getRequestValue('topic_id'));
        $_SERVER['REQUEST_URI'] = '/';
        $_REQUEST['REST_API'] = 1;
        $this->setRequestValue('user_id', $this->get_my_user_id());

        $sitebill_krascap = new SiteBill_Krascap();
        $sitebill_krascap->grid_adv();
        $static_data = Static_Data::getInstance();
        $result_array['success'] = 1;
        $result_array['data'] = $static_data::get_data();
        $result_array['params'] = $static_data::get_params();
        return $this->json_string($result_array);
    }

}
