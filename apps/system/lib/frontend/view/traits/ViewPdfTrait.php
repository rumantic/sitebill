<?php
/**
 * ViewPdfTrait — PDF generation and filename handling for Kvartira_View.
 *
 * Manages: makePDF, set_pdffilename, get_pdffilename, getPDFFileName_public, getPDFFileName.
 */
trait ViewPdfTrait
{
    private function set_pdffilename($pdffilename)
    {
        $this->pdffilename = $pdffilename;
    }

    public function get_pdffilename()
    {
        return $this->pdffilename;
    }

    public function getPDFFileName_public($form_data_shared)
    {
        return $this->getPDFFileName($form_data_shared);
    }

    protected function getPDFFileName($form_data_shared)
    {
        return '';
    }

    protected function makePDF($realty_id, $title)
    {
        $hasAccessor = false;
        if (isset($_SESSION['Accessor'])) {
            $this->template->assign('accessor_mode', 1);
            $hasAccessor = true;
        }

        if (1 == (int)$this->getConfigValue('apps.pdfreport.enabled') && isset($_GET['format']) && $_GET['format'] == 'pdf') {
            /* try{
              $x=new Accessor();
              }catch(Exception $e){
              echo 'no';
              } */

            /* if(class_exists('Accessor')){
              if($_SESSION['Accessor']['viewOptions']['mode']!='opened'){
              $this->template->assign('accessor_mode', 1);
              }
              }else{
              //echo 'no';
              } */

            //var_dump();
            /* $test_accessor_val='template.'.$this->getConfigValue('theme').'.free_mode';
              $test_accessor_module=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/accessor.php';
              $test_accessor_module=str_replace('/', DIRECTORY_SEPARATOR, $test_accessor_module);
              if(in_array($test_accessor_module, get_included_files()) && 1!=$this->getConfigValue($test_accessor_val)){
              //$this->template->assign('accessor_on', 1);

              if($_SESSION['Accessor']['viewOptions']['mode']!='opened'){
              $this->template->assign('user_data', array());
              }

              } */


            $_tpl_code = '';
            if (isset($_GET['tpl']) && $_GET['tpl'] != '') {
                $_tpl = $_GET['tpl'];
                if (preg_match('/[^0-9a-zA-Z_-]/', $_tpl)) {
                    $_tpl = '';
                } else {
                    $_tpl_code = '_' . $_tpl;
                }
                $_tpl = $_tpl . '.tpl';
            } else {
                $_tpl = '';
            }


            $pdfpageurl = Sitebill::getClearRequestURI();
            $pdfpageurl = $this->getServerFullUrl() . '/' . $pdfpageurl;
            $pdfpageurl .= ' | ' . date('d-m-Y H:i');
            $this->template->assign('pdfpageurl', $pdfpageurl);


            $this->template->assign('google_api_key', $this->getConfigValue('google_api_key'));


            $this->template->assign('_core_folder', SITEBILL_DOCUMENT_ROOT);
            $pdf_file_name = 'realty' . $realty_id . $_tpl_code . '.pdf';
            $pdf_file_storage = SITEBILL_DOCUMENT_ROOT . '/cache/';

            $pdfpageorient = 'portrait';
            if (1 == intval($this->getConfigValue('apps.pdfreport.data_page_orient'))) {
                $pdfpageorient = 'landscape';
            }

            if (0 == (int)$this->getConfigValue('apps.pdfreport.use_cache') || $hasAccessor) {
                global $smarty;
                if ($_tpl != '' && file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/pdfreport/' . $_tpl)) {
                    $html = $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/pdfreport/' . $_tpl);
                } elseif ($_tpl != '' && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/pdfreport/admin/template/' . $_tpl)) {
                    $html = $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/pdfreport/admin/template/' . $_tpl);
                } elseif (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/pdfreport/realty_view.tpl')) {
                    $html = $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/pdfreport/realty_view.tpl');
                } elseif (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/pdfreport/site/template/realty_view.tpl')) {
                    $html = $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/pdfreport/site/template/realty_view.tpl');
                } elseif (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/local/apps/pdfreport/admin/template/realty_view.tpl')) {
                    $html = $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/template/frontend/local/apps/pdfreport/admin/template/realty_view.tpl');
                } else {
                    $html = $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/pdfreport/admin/template/realty_view.tpl');
                }

                $dompdfoptions = new \Dompdf\Options();
                $dompdfoptions->set('isRemoteEnabled', TRUE);
                $dompdfoptions->set('defaultPaperOrientation', $pdfpageorient);
                $dompdfoptions->set('defaultPaperSize', 'A4');

                $dompdf = new \Dompdf\Dompdf($dompdfoptions);
                $dompdf->loadHtml($html);

                //$dompdf = new DOMPDF();
                //$dompdf->load_html($html);
                $dompdf->render();

                $output = $dompdf->output();
            } else {

                if (file_exists($pdf_file_storage . $pdf_file_name)) {
                    $output = file_get_contents(SITEBILL_DOCUMENT_ROOT . '/cache/' . $pdf_file_name);
                } else {
                    require_once(SITEBILL_DOCUMENT_ROOT . "/apps/pdfreport/lib/dompdf/dompdf_config.inc.php");
                    global $smarty;
                    if ($_tpl != '' && file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/pdfreport/' . $_tpl)) {
                        $html = $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/pdfreport/' . $_tpl);
                    } elseif (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/pdfreport/realty_view.tpl')) {
                        $html = $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/pdfreport/realty_view.tpl');
                    } elseif (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/pdfreport/site/template/realty_view.tpl')) {
                        $html = $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/pdfreport/site/template/realty_view.tpl');
                    } elseif (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/local/apps/pdfreport/admin/template/realty_view.tpl')) {
                        $html = $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/template/frontend/local/apps/pdfreport/admin/template/realty_view.tpl');
                    } else {
                        $html = $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/pdfreport/admin/template/realty_view.tpl');
                    }
                    $dompdfoptions = new \Dompdf\Options();
                    $dompdfoptions->set('isRemoteEnabled', TRUE);
                    $dompdfoptions->set('defaultPaperOrientation', $pdfpageorient);
                    $dompdfoptions->set('defaultPaperSize', 'A4');
                    $dompdf = new \Dompdf\Dompdf($dompdfoptions);
                    $dompdf->loadHtml($html);
                    $dompdf->render();

                    $output = $dompdf->output();
                    file_put_contents(SITEBILL_DOCUMENT_ROOT . '/cache/' . $pdf_file_name, $output);
                }
            }
            header("Content-type: application/pdf");
            //echo $output;
            header('Content-Disposition: attachment; filename="' . $title . '.pdf"');

            echo $output;
            exit();
        }
    }
}
