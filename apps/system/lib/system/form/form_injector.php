<?php
namespace system\lib\system\form;

class Form_Injector {
    function __construct() {

    }

    function compile ( $item, \Form_Generator $context = null, $model = null ) {
        switch ( $item['name'] ) {
            case 'renovation':
                $form_injection = new \renovation\admin\Form_Injection();
                break;
            case 'booking':
                $form_injection = new \reservation\admin\Form_Injection();
                break;
            case 'contact_id':
                $form_injection = new \client\admin\Form_Injection();
                break;
        }
        if ( isset($form_injection) ) {
            return $form_injection->get_content($item, $context, $model);
        }
        return false;
    }
}
