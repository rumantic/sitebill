<?php
namespace system\traits;

trait NotifyTrait
{
    function sendNotifyWithFormData ($subject, $form_data, $to = '') {
        if ( $to == '' ) {
            $to = $this->getConfigValue('order_email_acceptor');
        }
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/view.php');

        $table_view = new \Table_View();
        $order_table = '';
        $order_table .= '<table border="1" cellpadding="2" cellspacing="2" style="border: 1px solid gray;">';
        $table_view->setAbsoluteUrls();
        $order_table .= $table_view->compile_view($form_data);
        $order_table .= '</table>';
        $subject = $_SERVER['SERVER_NAME'] . ': '.$subject;
        $from = $this->getConfigValue('system_email');
        $this->sendFirmMail($to, $from, $subject, $order_table);
        return true;
    }
}

