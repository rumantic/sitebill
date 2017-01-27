<?php
/**
 * SiteBill auth class 
 * @author Kondin Dmitriy <kondin@etown.ru>
 *
 */ 
class Sitebill_Auth extends SiteBill {
    /**
     * Constructor
     */
    function Sitebill_Auth() {
        $this->SiteBill();
    }
    
    /**
     * Main
     */
    function main () {
        if ( $_SESSION['user_id'] == '' ) {
            if ( $this->checkAuth($this->getRequestValue('login'), $this->getRequestValue('password')) ) {
                $_SESSION['user_id'] = 'true';
                return true;
            }
            $this->riseError('not login');
            return $this->getAuthForm();
        }
        return true;
    }
    
    /**
     * Check auth
     * @param string $login login
     * @param string $password password
     * @return boolean
     */
    function checkAuth ( $login, $password ) {
        $password = md5($password);
        
        $sql = "SELECT user_id FROM ".DB_PREFIX."_user WHERE login=? and password=? and group_id = ?";
        $DBC=DBC::getInstance();
        $stmt=$DBC->query($sql, array($login, $password, $this->getGroupID('admin')));
        if($stmt){
        	$ar=$DBC->fetch($stmt);
        	if ( $ar['user_id'] > 0 ) {
        		return true;
        	}
        }
        return false;
    }
    
    /**
     * Get group ID
     * @param string
     * @return int
     */
    function getGroupID ( $group_name ) {
        return 1;
    }
    
    /**
     * Get auth form
     * @param
     * @return
     */
    function getAuthForm () {
        $rs .= '
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
        
                    <link rel=stylesheet type="text/css" href="css/admin.css">
        <p>&nbsp;</p>        
                                    <table border="0" cellpadding="0" cellspacing="0" align="center">
                                        <tr>
                                            <td class="special">
                                                <h1>РђРІС‚РѕСЂРёР·Р°С†РёСЏ</h1><br>
        ';
        $rs .= '<form method="post">';
        $rs .= '<table border="0">';
        $rs .= '<tr>';
        $rs .= '<td class="special">Login </td>';
        $rs .= '<td class="special"><input type="text" name="login" value=""></td>';
        $rs .= '</tr>';
        $rs .= '<tr>';
        $rs .= '<td class="special">Password </td>';
        $rs .= '<td class="special"><input type="password" name="password" value=""></td>';
        $rs .= '</tr>';
        $rs .= '<tr>';
        $rs .= '<td class="special"></td>';
        $rs .= '<td class="special"><input type="submit" value="Р’С…РѕРґ"></td>';
        $rs .= '</tr>';
        $rs .= '</table>';
        $rs .= '</form>';
        $rs .= '
                                            </td>
                                        </tr>
                                    </table>
        ';
        return $rs;
    }
    
    
}
?>