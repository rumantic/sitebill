<?php
/**
 * ObjectNotificationTrait — UI notification (gritter) methods extracted from Object_Manager.
 *
 * Methods: gritterSuccess, gritterError, gritterMessage, get_smarty_template_dir
 *
 * @see Object_Manager
 */

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

trait ObjectNotificationTrait
{
    protected function gritterSuccess($title, $message, $sticky = 'false', $time = 10000)
    {
        return $this->gritterMessage($title, $message, 'gritter-success', $sticky, $time);
    }

    protected function gritterError($title, $message, $sticky = 'false', $time = 10000)
    {
        return $this->gritterMessage($title, $message, 'gritter-error', $sticky, $time);
    }

    protected function gritterMessage($title, $message, $class_name = 'gritter-success', $sticky = 'false', $time = 10000)
    {
        $rs = "
            <script type=\"text/javascript\">
            $(document).ready(function () {
                    
                                $.gritter.add({
                                    title: '$title',
                                    text: '$message',
                                    sticky: $sticky,
                                    time: '$time',
                                    class_name: '$class_name'
                                });
            });
            </script>
        ";
        return $rs;
    }

    function get_smarty_template_dir($mode = 'admin')
    {
        global $smarty;
        if ($mode == 'admin') {
            return SITEBILL_DOCUMENT_ROOT . '/apps/admin/admin/template1';
        }
        if (is_array($smarty->template_dir)) {
            return $smarty->template_dir[0];
        }
        return $smarty->template_dir;
    }
}
