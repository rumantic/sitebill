<?php
namespace client\Listeners;

use client\Events\ClientVisitEvent;

class SendVisitEmail {
    public function handle(ClientVisitEvent $event)
    {
        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/client/admin/admin.php');
        $client_admin = new \client_admin();
        $form_data = $client_admin->load_by_id($event->getClientId());

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/view.php');
        $table_view = new \Table_View();

        $email_body = '<p><a href="'.$client_admin->getServerFullUrl().'/admin/?action=client&do=view&client_id='.
            $event->getClientId().'">Просмотр заявки в админке</a></p>';

        $email_body .= '<table border="1" cellpadding="2" cellspacing="2" style="border: 1px solid gray;">';
        $email_body .= $table_view->compile_view($form_data);
        $email_body .= '</table>';

        $client_admin->sendFirmMail(
            $client_admin->getConfigValue('order_email_acceptor'),
            $client_admin->getConfigValue('system_email'),
            _e('Запись на просмотр объекта недвижимости'),
            $email_body
        );
        return true;
    }
}

