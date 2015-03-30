<?php
/**
 * Updater class
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class Updater extends SiteBill {
    /**
     * Constructor
     * @param void
     * @return void
     */
    function __construct() {
        $this->SiteBill();
    }
    
    /**
     * Main
     */
    function main () {
        $version = $this->get_version();
        //$rs = '<a href="#" onclick="run_command(\'update&user_id='.$this->getRequestValue('user_id').'\', \'cp1251\', \''.$_SERVER['SERVER_NAME'].'\', \''.$_SESSION['session_key'].'\'); return false;">Загрузить страницу обновлений</a>';
        $rs .= '
<script>
$(document).ready(function() {
	run_command(\'update&version='.$version.'&user_id='.$this->getRequestValue('user_id').'\', \'cp1251\', \''.$_SERVER['SERVER_NAME'].'\', \''.$_SESSION['session_key'].'\');
});    
</script>    
        ';
        $rs .= '<div id="admin_area">';
        $rs .= sprintf(Multilanguage::_('REGISTER_FOR_ACCESS','system'),'<a href="http://www.sitebill.ru/">sitebill.ru</a>');
        
        $rs .= '</div>';
        
        return $rs;
        
        
        return $rs;
    }
    
    /**
     * Get version
     */
    function get_version () {
        return 1;
        /*
        $query = "select version from ".DB_PREFIX."_version order by version_id desc limit 1";
        $this->db->exec($query);
        $this->db->fetch_assoc();
        if ( $this->db->row['version'] == '' ) {
            return 1;
        }
        return $this->db->row['version'];
        */
    }
    
}
?>
