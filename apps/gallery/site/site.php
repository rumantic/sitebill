<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * Gallery admin backend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/gallery/admin/admin.php');
class gallery_site extends gallery_admin {
    /**
     *
     */
    function frontend () {
    	$breadcrumbs=array();
    	$breadcrumbs[]='<a href="/">'.Multilanguage::_('L_HOME').'</a>';
        if ( preg_match('/gallery/', $_SERVER['REQUEST_URI']) ) {
            if ( preg_match('/photo/', $_SERVER['REQUEST_URI']) ) {
    	        preg_match('/photo(\d+)/s', $_SERVER['REQUEST_URI'], $matches);
            	if ( $matches[1] > 0 ) {
            		$breadcrumbs[]='<a href="/gallery/">'.Multilanguage::_('GALLERY','gallery').'</a>';
            		$gallery=$this->get_gallery($matches[1]);


            		$breadcrumbs[]=$gallery['title'];
            		$this->template->assert('title', $gallery['title']);

            		$page=(int)$this->getRequestValue('page');
            		if($page==0){
            			$page=1;
            		}
                    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/config/admin/admin.php');
                    $config_manager = new config_admin();
            		if ( !$config_manager->check_config_item('app_gallery_photos_per_page') ) {
			            $config_manager->addParamToConfig('app_gallery_photos_per_page', '5', 'Галерея: Количество фотографий на страницу');
			        	$per_page=5;
			        }else{
			        	$per_page=$this->getConfigValue('app_gallery_photos_per_page');
			        }

    	            $photo_list = $this->get_photo_list($matches[1]);
    	            //echo $this->get_page_links_list($page, count($photo_list), $per_page, array());
    	            $this->template->assert('breadcrumbs', implode(' / ',$breadcrumbs));

                    $this->template->assert('photo_list', array_slice($photo_list,(($page-1)*$per_page),$per_page));
                    $this->template->assert('pager', $this->get_page_links_list($page, count($photo_list), $per_page, array()));
                    $this->set_apps_template('gallery', $this->getConfigValue('theme'), 'main_file_tpl', 'photo_list.tpl');

                    return true;
    	        }
            } else {
            	$breadcrumbs[]=Multilanguage::_('GALLERY','gallery');
                $gallery_list = $this->get_gallery_list();
                $this->template->assert('title', Multilanguage::_('GALLERY','gallery'));
                $this->template->assert('breadcrumbs', implode(' / ',$breadcrumbs));
                $this->template->assert('gallery_list', $gallery_list);
                $this->set_apps_template('gallery', $this->getConfigValue('theme'), 'main_file_tpl', 'gallery_list.tpl');

            }
            return true;
        }
        return false;
    }

    function get_photo_list ( $gallery_id ) {
        $gallery_id = intval($gallery_id);
        $ra = $this->get_image_array('gallery', 'gallery', 'gallery_id', $gallery_id);
        return $ra;
    }

    /**
     * Get image list admin
     * @param string $action action
     * @param string $table_name table name
     * @param string $key key
     * @param int $record_id record id
     * @param int $limit limit value
     * @return string
     */
    function get_image_array($action, $table_name, $key, $record_id, $limit = 0) {
        $DBC = DBC::getInstance();
        $url = $this->storage_dir;
        $ra = array();
        $record_id = (int) $record_id;
        $query = 'SELECT i.* FROM ' . DB_PREFIX . '_' . $table_name . '_image AS li, ' . IMAGE_TABLE . ' AS i WHERE li.' . $key . '=? AND li.image_id=i.image_id ORDER BY li.sort_order';

        if ($limit > 0) {
            $query .= ' LIMIT ?';
        }


        if ($limit > 0) {
            $stmt = $DBC->query($query, array($record_id, $limit));
        } else {
            $stmt = $DBC->query($query, array($record_id));
        }

        if ($stmt) {
            $i = 0;
            while ($ar = $DBC->fetch($stmt)) {
                $ra[$i]['preview'] = $ar['preview'];
                $ra[$i]['normal'] = $ar['normal'];

                $ra[$i]['title'] = $ar['title'];
                $ra[$i]['description'] = $ar['description'];

                $ra[$i]['img_preview'] = $url . '' . $ar['preview'];
                $ra[$i]['img_normal'] = $url . '' . $ar['normal'];
                $i++;
            }
        }

        return $ra;
    }


    /**
     * Get gallery list
     * @param void
     * @return array
     */
    function get_gallery_list() {
    	$DBC=DBC::getInstance();
    	$ra=array();
        $query = "select * from ".DB_PREFIX."_gallery order by gallery_id asc";
        $stmt=$DBC->query($query);
        if($stmt){
        	while($ar=$DBC->fetch($stmt)){
        		$ra[] = $ar;
        	}
        }
        if(!empty($ra)){
        	foreach ( $ra as $item_id => $item_array ) {
        		//get first image
        		$query = 'SELECT i.* FROM '.DB_PREFIX.'_gallery_image gi, '.DB_PREFIX.'_image i WHERE gi.gallery_id='.$item_array['gallery_id'].' AND gi.image_id=i.image_id LIMIT 1';
        		$stmt=$DBC->query($query);
	        	if($stmt){
		        	$ra[$item_id]['image'] = $DBC->fetch($stmt);
		        }
        	}
        }
        return $ra;
    }

    function get_gallery($gallery_id){
    	$DBC=DBC::getInstance();
    	$ra=array();
    	$query = 'SELECT * FROM '.DB_PREFIX.'_gallery WHERE gallery_id=?';
    	$stmt=$DBC->query($query, array($gallery_id));
    	if($stmt){
    		$ra=$DBC->fetch($stmt);
    	}
    	return $ra;
    }

    function gallery_view ($params) {
        $gallery_id = $this->get_id_by_filter('label', $params['label']);
        if ( $gallery_id ) {
            $gallery_data = $this->load_by_id($gallery_id);

            return $this->view('gallery', ['image' => $gallery_data['image']['value'], 'galleryid' => 'appgallery'.$gallery_id, 'sitebill' => new \SiteBill()]);
        }
        return '<div class="alert alert-warning">Gallery with label '.$params['label'].' not found</div>';
    }

    function gallery_fotorama_full_pack ( $params ) {
        return $this->view('gallery_fotorama_full_pack', [
            'image' => $params['image'],
            'galleryid' => $params['galleryid'],
            'sitebill' => new \SiteBill()
        ]);
    }

    function test_blade ($params) {
        return $this->view('test', ['test_param' => $params['test_param']]);
    }

}
