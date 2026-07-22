<?php
namespace memorylist\admin\traits;

trait PdfTrait
{
    function pdfList( $ids, $stuff = false, $user_id = 0, $downloaded_filename = 'Подборка' ) {
        global $smarty;

        $data = $this->init_exported_data($ids);

        if(!empty($data)){
            $data = $this->adopt_model_to_pdfexport($data);
        }
        $user_id = $user_id ? $user_id : 1;

        if ( $user_id != 0 ) {
            require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/users/user_object_manager.php');
            $user_object_manager = new \User_Object_Manager();
            $user_data = $user_object_manager->load_by_id($user_id);
            $this->template->assign('user_data', $user_data);

        }

        $tpl = '';
        $tplfile = 'pdf_memory_list_grid_client.tpl';
        if($stuff){
            $tplfile = 'pdf_memory_list_grid_stuff.tpl';
        }
/*
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/memorylist/site/template/' . $tplfile)) {
            $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/memorylist/site/template/' . $tplfile;
        } elseif (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/local/apps/memorylist/site/template/' . $tplfile)) {
            $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/local/apps/memorylist/site/template/' . $tplfile;
        } else {
            $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/memorylist/site/template/' . $tplfile;
        }
*/
        $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/local/apps/pdfreport/admin/template/realty_grid.tpl';

        // тут надо идти по data


        $this->template->assign('_core_folder', SITEBILL_DOCUMENT_ROOT);
        $this->template->assign('grid_items', $data);
        $this->template->assign('site_url', $this->getServerFullUrl());

        $html = $smarty->fetch($tpl);

        $dompdfoptions = new \Dompdf\Options();
        $dompdfoptions->set('isRemoteEnabled', TRUE);
        $dompdfoptions->set('defaultPaperOrientation', 'landscape');

        $dompdf = new \Dompdf\Dompdf($dompdfoptions);
        $dompdf->loadHtml($html);
        $dompdf->render();
        $output = $dompdf->output();
        header("Content-type: application/pdf");
        header("Content-Disposition:attachment;filename=\"$downloaded_filename.pdf\"");
        echo $output;
        exit();
    }
}
