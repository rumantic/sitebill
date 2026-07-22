<?php
namespace system\traits\admin;

trait ObjectManagerMain {
    /**
     * @var $entity \Object_Manager
     */
    protected $entity;

    function main() {
        $do = $this->getRequestValue('do');
        $action = '_' . $do . 'Action';

        $context = $this->getEntity();
        if ( !isset($context) ) {
            $context = $this;
        }

        if (!method_exists($context, $action)) {
            $action = '_defaultAction';
        }

        $rs_action = $context->$action();

        //$rs_top = $this->get_app_title_bar();
        if ( !self::admin3_compatible() ) {
            //$rs_top .= '<div class="page-header">'.$this->getTopMenu().'</div>';
        }
        $rs = '<div class="row-fluid">';
        $rs .= '<div class="col-xs-12">';
        //$rs .= $rs_top;
        $rs .= $rs_action;
        $rs .= '</div>';
        $rs .= '</div>';
        //dd($this->get_top_menu_items());
        $this->template->assign('top_menu_items', $this->get_top_menu_items());
        $this->template->assign('do_buttons', $context->getDoButtons());
        $this->template->assign('toolbar', $this->get_app_title_bar());
        $this->template->assign('extended_items', $context->get_extended_items());
        $this->template->assign('object_action', $this->getRequestValue('action'));
        $this->template->assign('object_action_result', $rs_action);

        return $this->template->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/system/template/object/main.tpl');
    }

    /**
     * @param $entity \Object_Manager
     * @return void
     */
    function setEntity ( $entity ) {
        $this->entity = $entity;
    }

    /**
     * @return \Object_Manager
     */
    function getEntity ( ) {
        return $this->entity;
    }

}
