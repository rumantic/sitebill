<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * downloader fronend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class downloader_site extends downloader_admin {

    function frontend() {
        if (
            $this->getConfigValue('apps.downloader.src_enable') and
            preg_match('/^' . $this->getConfigValue('apps.downloader.src_alias') . '$/', self::getClearRequestURI(), $matches)
        ) {
            $this->download_from_url($this->getRequestValue('url'));
        }

        if ( !$this->getConfigValue('apps.downloader.enable') ) {
            return false;
        }

        if (preg_match('/^' . $this->getConfigValue('apps.downloader.alias') . '\/(\d+)$/', self::getClearRequestURI(), $matches)) {
            $realty_id = $matches[1];
            return $this->download($realty_id);
        }
        return false;
    }

    function download_from_url ($url) {
        //$url = 'https://sh3.sitebill.ru/storage/banzai72.ru/5/ce/5ce284af9a260863619056a3f0c4846f.jpg';
        $host = parse_url($url, PHP_URL_HOST);
        if ( $host != 'sh3.sitebill.ru' and $host != 'sh4.sitebill.ru' and $host != $_SERVER['HTTP_HOST'] ) {
            echo 'bad file url = '.$url.', you can download files only from this domain '.$_SERVER['HTTP_HOST'];
            exit;
        }

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header('Content-type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($url).'"');
        readfile($url);
        exit;
    }

    function download ( $id ) {
        $image_field='image'; //указываем системное имя поля с картинками в нашей модели
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data_shared = $data_model->get_kvartira_model(false, true);
        $form_data_shared=$form_data_shared['data'];

        $fields=array('id', $image_field, 'active');
        foreach($form_data_shared as $k=>$v){
            if(!in_array($k, $fields)){
                unset($form_data_shared[$k]);
            }
        }

        $form_data_shared = $data_model->init_model_data_from_db ( 'data', 'id', $id, $form_data_shared, true );

        //Если такого объекта нет - отбрасываем
        if(!$form_data_shared){
            return false;
        }

        //Если объект неактивен - отбрасываем
        if($form_data_shared['active']['value']!=1){
            //return false;
        }

        $images=array();
        if($form_data_shared[$image_field]['type']=='uploads' && is_array($form_data_shared[$image_field]['value']) && count($form_data_shared[$image_field]['value'])>0){
            $images=$form_data_shared[$image_field]['value'];
        }elseif($form_data_shared[$image_field]['type']=='uploadify_image' && is_array($form_data_shared[$image_field]['image_array']) && count($form_data_shared[$image_field]['image_array'])>0){
            $images=$form_data_shared[$image_field]['image_array'];
        }

        //Если картинок нет - отбрасываем
        if(empty($images)){
            return false;
        }

        $zip = new ZipArchive();
        $zip_name = "photos_".$id.'_'.time().".zip";
        $zip->open($zip_name, ZIPARCHIVE::CREATE);
        $j = 0;
        foreach($images as $photo){
            $j++;
            if ( $photo['remote'] === 'true' ) {
                $pathinfo = pathinfo($photo['normal']);
                $file_name = $j.'.'.$pathinfo['extension'];
                $zip->addFromString($file_name, file_get_contents($photo['normal']));
            } else {
                $zip->addFile(SITEBILL_DOCUMENT_ROOT.'/img/data/'.$photo['normal'], $photo['normal']);
            }
        }
        $zip->close();
        if(file_exists($zip_name)){
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private", false);
            header('Content-type: application/zip');
            header('Content-Disposition: attachment; filename="'.$zip_name.'"');
            readfile($zip_name);
            unlink($zip_name);
        }
        exit();
    }
}
