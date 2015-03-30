<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * Account class
 * @author Kondin Dmitry
 */
require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/login.php');
class Account extends Login {
    var $user_id = 0; // Default value of the user ID
    /**
     * Constructor
     */
    function Account () {
        //set debug mode using conf value
        if(isset($config)){
        	$this->setDebugMode($config->debug_mode);
        }
        if ( $this->getDebugMode() ) {
            //echo 'account constructor<br>';
        }
        $this->Login();
        $this->user_id = $this->getSessionUserId();
        /*if($this->USER_isUserAuthorized()){
        	$this->user_id = $this->USER_getUserId();
        }*/
        //$this->initService();
        $this->SiteBill();
        //$this->setTableName('service');
        //$this->setPrimaryKey('service_id');
    }
    
    /**
     * Get user menu
     * @param void
     * @return int
     */
    function get_user_id () {
        return $this->user_id;
    }
    
    /**
     * Return company profile data
     * @param int $user_id
     * @return array
     */
    function get_company_profile ( $user_id ) {
        if ( $this->getConfigValue('apps.company.enable') ) {
            //get company ID
            $query = "select * from ".DB_PREFIX."_user where user_id=$user_id";
            $this->db->exec($query);
            $this->db->fetch_assoc();
            if(isset($this->db->row['company_id'])){
            	$company_id = $this->db->row['company_id'];
            }else{
            	$company_id=0;
            }
            require_once (SITEBILL_DOCUMENT_ROOT.'/apps/company/admin/admin.php');
            $company_admin = new company_admin();
            return $company_admin->load_by_id($company_id);
        }
        return false;
    }
    
    function login_main () {
        global $init;
        $this->checkLogin(  $init->getValue('login'), $init->getValue('password')  );
        //echo "error_message = ".$this->error_message."<br>";
        if ( $this->GetError() ){
            $rs = $this->loginForm();
        }
        return $rs;
    }
    
    /**
     * Get home
     * @param void
     * @return string
     */
    function getHome () {
        //print_r($_SESSION);
        //echo 'user_id = '.$_SESSION['user_id'];
        //$this->getSessionUserId();
        if ( !$this->getSessionUserId() ) {
            $rs = $this->login_main();
            if ( $this->getError() ) {
               return $rs; 
            }
        }
        
        $rs = '<h1>'.Multilanguage::_('PRIVATE_ACCOUNT','system').'</h1>';
        
        $rs .= '<ul>';
        $rs .= '<li><a href="'.SITEBILL_MAIN_URL.'/account/profile">'.Multilanguage::_('PROFILE','system').'</a></li>';
        $rs .= '<li><a href="'.SITEBILL_MAIN_URL.'/account/data">'.Multilanguage::_('MY_ADS','system').'</a></li>';
        $rs .= '</ul>';
        return $rs;
    }
    
    /**
     * Get lock screen
     * @param int $user_id user ID
     * @return string
     */
    function getLockScreen( $user_id ) {
        $rs = sprinf(Multilanguage::_('RECHARGE_FOR_ACCESS','system'),$user_id).'<br>
        <a href="'.SITEBILL_MAIN_URL.'/account/">'.Multilanguage::_('RECHARGE_LC','system').'</a>';
        return $rs;
    }
    
    /**
     * Main
     * @param void
     * @return string
     */
    function main () {
        $rs = '';
        global $init;
        
        if ( $this->user_id == 0 ) {
            $rs = $this->login_main();
            if ( $this->getError() ) {
               return $rs; 
            }
        }
        
        switch ( $init->getValue('do', 'default') ) {
            case 'add_bill_done':
                $bill_sum = $init->getValue('bill');
                $bill_sum=preg_replace('/[^0-9\.,]/', '', $bill_sum);
                if ( !isset($bill_sum) or $bill_sum == '' ) {
                    $this->riseError(sprintf(Multilanguage::_('SUM_MUST_BE_MORE','system'),'0'));
                    $rs .= $this->getBillForm();
                    return $rs;    
                }
                $bill_name='Пополнение счета на '.$bill_sum;
                if ( $this->getConfigValue('apps.paypal.enable') ) {
                	$bill_payment_sum=number_format($bill_sum/$this->getConfigValue('apps.paypal.usd_coef'), 2);
                }else{
                	$bill_payment_sum=$bill_sum;
                }
                
                $bill_id = $this->addBill( $this->getSessionUserId(),  $bill_sum, $bill_name, $bill_payment_sum, 1);
                if ( $this->getError() ) {
                    return $this->getBillForm();
                }
                if ( $this->getConfigValue('apps.clickuz.enable') ) {
                	require_once (SITEBILL_DOCUMENT_ROOT.'/apps/clickuz/admin/admin.php');
                	require_once (SITEBILL_DOCUMENT_ROOT.'/apps/clickuz/site/site.php');
                	$clickuz_site = new clickuz_site();
                	 
                	$rs .= $clickuz_site->get_pay_button($bill_id, $bill_sum);
                }
                if ( $this->getConfigValue('apps.paypal.enable') ) {
                	require_once (SITEBILL_DOCUMENT_ROOT.'/apps/paypal/admin/admin.php');
                	require_once (SITEBILL_DOCUMENT_ROOT.'/apps/paypal/site/site.php');
                	$paypal_site = new paypal_site();
                
                	$rs .= $paypal_site->get_pay_button($bill_id, $bill_sum, $bill_payment_sum);
                }
                $rs .= $this->jumpToRobokassa($bill_id);
                return $rs;
            break;
            
            case 'add_bill':
                $rs .= $this->getBillForm();
                return $rs;    
            break;
            
            default:
        }
        
        $rs .= sprintf(Multilanguage::_('ACCOUNT_STATE','system'),$this->getAccountValue( $this->getSessionUserId() )).' '.$this->getConfigValue('ue_name');
        $rs .= $this->getTopMenu();
        return $rs;
    }
    
    /**
     * Jump to robokassa
     * @param
     * @return
     */
    function jumpToRobokassa ( $bill_id, $bill_sum='' ) {
        //echo "bill_id = $bill_id, bill_sum = $bill_sum";
        if($bill_sum==''){
        	$DBC=DBC::getInstance();
        	$query='SELECT * FROM '.DB_PREFIX.'_bill WHERE bill_id=? LIMIT 1';
        	$stmt=$DBC->query($query, array($bill_id));
        	if(!$stmt){
        		return '';
        	}
        	$bill_info=$DBC->fetch($stmt);
        	$out_summ = $bill_info['payment_sum_robokassa'];
        }else{
        	$out_summ = $bill_sum;
        }
    	
    	
        global $config;
        
        $mrh_login = $this->getConfigValue('robokassa_login');
        $mrh_pass1 = $this->getConfigValue('robokassa_password1');
        
        //echo " mrh_login = $mrh_login, mrh_pass1 = $mrh_pass1";

        // номер заказа
        // number of order
        $inv_id = $bill_id;

		// формирование подписи
        // generate signature
        //$crc  = md5("$mrh_login:$out_summ:$inv_id:$mrh_pass1:Shp_item=$shp_item");
        //$crc  = md5("$mrh_login:$out_summ:$inv_id:$mrh_pass1");
        $crc  = md5("$mrh_login:$out_summ:$inv_id:$mrh_pass1");
        
        $rs = sprintf(Multilanguage::_('YOU_HAVE_ORDER','system'),(string)$out_summ, $this->getConfigValue('ue_name')).'<br>';

        // http://test.robokassa.ru/Index.aspx
        $rs .= "<form action=\"".$this->getConfigValue('robokassa_server')."\" method=\"POST\">";
        //$rs .= "<form action='https://merchant.roboxchange.com/Index.aspx' method=POST>";
        //$rs .= Multilanguage::_('SELECT_PAYMENT_TYPE','system').': '.$this->getPayMethodsList().'<br>';
        
        $rs .=
   "<input type=\"hidden\" name=\"MrchLogin\" value=\"$mrh_login\">".
   "<input type=\"hidden\" name=\"OutSum\" value=\"$out_summ\">".
   "<input type=\"hidden\" name=\"InvId\" value=\"$inv_id\">".
   "<input type=\"hidden\" name=\"SignatureValue\" value=\"$crc\">".
   "<input type=\"submit\" value=\"".Multilanguage::_('L_TEXT_PAY')."\">";
        $rs .= '</form>';
        
        return $rs;        
    }
    
    /**
     * Get pay methods list
     * @param void
     * @return string
     */
    function getPayMethodsList () {
    	
    	return '';
    	
        $rs .= '<select name="IncCurrLabel">';
        $rs .= '
	<optgroup label="'.Multilanguage::_('PAY_BY_ELMONEY','system').'">
		<option value="YandexMerchantOceanR">Яндекс.Деньги</option> 
		<option value="WMRM" selected="selected">WMR</option> 
		<option value="WMZM">WMZ</option> 
		<option value="WMEM">WME</option> 
		<option value="WMUM">WMU</option> 
		<option value="WMBM">WMB</option> 
		<option value="MailRuR">Деньги@Mail.Ru</option> 
		<option value="EasyPayB">EasyPay</option> 
		<option value="QiwiR">QIWI Кошелек</option> 
		<option value="MoneyMailR">MoneyMail</option> 
		<option value="RuPayR">RUR RBK Money</option> 
		<option value="TeleMoneyR">RUR TeleMoney</option> 
		<option value="WebCredsR">RUR WebCreds</option> 
		<option value="ZPaymentR">RUR Z-Payment</option> 
		<option value="VKontakteMerchantR">RUR ВКонтакте</option> 
		<option value="W1R">RUR Единый Кошелек</option> 
	</optgroup>
	<optgroup label="'.Multilanguage::_('PAY_BY_CELL','system').'">
		<option value="MtsR">МТС</option>
		<option value="MPBeelineR">Билайн</option>
	</optgroup>
	<optgroup label="'.Multilanguage::_('PAY_BY_BANKCARD','system').'">
		<option value="BANKOCEAN2R">Банковской картой</option>
	</optgroup>
	<optgroup label="'.Multilanguage::_('PAY_BY_PLATEZH','system').'">
		<option value="OceanBankR">RUR Океан Банк</option>
	</optgroup>
	<optgroup label="'.Multilanguage::_('PAY_BY_IBOX','system').'">
		<option value="TerminalsAbsolutplatR">Absolutplat</option>
		<option value="TerminalsPinpayR">Pinpay</option>
		<option value="QiwiR">QIWI</option>
		<option value="TerminalsComepayR">Кампэй</option>
		<option value="TerminalsMElementR">Мобил Элемент</option>
		<option value="TerminalsNovoplatR">Новоплат</option>
		<option value="TerminalsUnikassaR">Уникасса</option>
		<option value="ElecsnetR">Элекснет</option>
	</optgroup>
	<optgroup label="'.Multilanguage::_('PAY_BY_CONTACT','system').'">
		<option value="ContactR">RUR Contact</option>
	</optgroup>
	<optgroup label="'.Multilanguage::_('PAY_BY_ATM','system').'">
		<option value="VTB24R">RUR ВТБ24</option>
		<option value="TerminalsPkbR">Петрокоммерц</option>
	</optgroup>
	<optgroup label="'.Multilanguage::_('PAY_BY_EURONET','system').'">
		<option value="RapidaInR">RUR Евросеть</option>
	</optgroup>
	<optgroup label="'.Multilanguage::_('PAY_BY_INETBANK','system').'">
		<option value="AlfaBankR">Альфа-Клик</option>
	</optgroup>
        ';
        $rs .= '</select>';
        $rs .= '<br>';
        return $rs;
    }
    
    /**
     * Add bill 
     * @param int $user_id user ID
     * @param string $sum sum
     * @return boolean
     */
    function addBill ( $user_id, $sum, $bill_description='', $payment_sum='', $bill_direct='' ) {
        $time = time();
        $ip=getenv(HTTP_X_FORWARDED_FOR);
        if($ip==''){
        	$ip=$_SERVER['REMOTE_ADDR'];
        }
        if($payment_sum==''){
        	$payment_sum=$sum;
        }
        if($bill_direct==''){
        	$bill_direct=1;
        }
        if(''!=$this->getConfigValue('robokassa_koef')){
        	$k=$this->getConfigValue('robokassa_koef');
        }else{
        	$k=1;
        }
        $bill_payment_sum_robo=$sum*$k;
        $query = 'INSERT INTO '.DB_PREFIX.'_bill (user_id, sum, date, status, description, http_x_real_ip, payment_sum, payment_sum_robokassa) values (?, ?, ?, 0, ?, ?, ?, ?)';
        $DBC=DBC::getInstance();
        $stmt=$DBC->query($query, array($user_id, $sum, $time, $bill_description, $ip, $payment_sum, $bill_payment_sum_robo));
        if($stmt){
        	return $DBC->lastInsertId();
        }
    }
    
    /**
     * Add pay
     * @param int $user_id user ID
     * @param string $pay pay
     * @return boolean
     */
    function addPay ( $user_id, $pay ) {
        //Get current account value
        $account_value = $this->getAccountValue( $user_id );
        //Add pay
        $account_value += $pay;
        //Update account value
        $query = "update system_user set account='$account_value' where user_id=$user_id";
        $this->db->exec($query);
        return true;
    }
    
    /**
     * Get bill form
     * @param void
     * @return string
     */
    function getBillForm () {

        $rs = '';
        $rs .= '<form action="'.SITEBILL_MAIN_URL.'/account/balance/?do=add_bill_done" method="post">';
        $rs .= '<p>'.sprintf(Multilanguage::_('INPUT_PAYMENT_SUM','system'),$this->getConfigValue('ue_name')).'</p>';
        if ( $this->getError() ) {
            $rs .= '<p><span class="error">'.$this->GetErrorMessage().'</span></p>';
        }
        $rs .= Multilanguage::_('PAYMENT_SUM','system').': <input type="text" name="bill" value="'.$this->getRequestValue('bill').'">';
        $rs .= '<input type="hidden" name="do" value="add_bill_done">';
        $rs .= '<input type="submit" value="'.Multilanguage::_('L_TEXT_NEXT').'">';
        $rs .= '</form>';

        return $rs;
    }
    
    /**
     * Get account value
     * @param int $user_id user ID
     * @return string
     */
    function getAccountValue ( $user_id ) {
        $query = "select account from ".DB_PREFIX."_user where user_id=$user_id";
        //echo $query;
        $this->db->exec($query);
        $this->db->fetch_assoc();
        return $this->db->row['account'];
    }
    
    function get_user_data_count ( $user_id ) {
    	$query = "select count(id) as total from ".DB_PREFIX."_data where user_id=$user_id";
    	//echo $query;
    	$this->db->exec($query);
    	$this->db->fetch_assoc();
    	return $this->db->row['total'];
    }
    
    /**
     * Get top menu
     * @param void
     * @return string
     */
    function getTopMenu () {
        $rs = '<br><a href="'.SITEBILL_MAIN_URL.'/account/balance/?do=add_bill">Пополнить счет</a><br>';
        $rs .= '<br><i>* '.sprintf(Multilanguage::_('AD_PLACEMENT_COST','system'),$this->getConfigValue('advert_cost'), $this->getConfigValue('ue_name')).'</i>';
        return $rs;
    }
    
    /**
     * Get menu for developers
     * @param int $user_id user ID
     * @return string
     */
    function getDeveloperMenu ( $user_id ) {
        $rs = '';
        $rs .= '<a href="task" class="top_menu">'.Multilanguage::_('MY_TASKS','system').'</a> ';
        $rs .= '<a href="profile" class="top_menu">'.Multilanguage::_('PROFILE','system').'</a> ';
        
        if ( $user_id > 1 ) {
            /*
            $rs .= '
             <a href="/service" class="top_menu">Услуги</a> | 
             <a href="/account" class="top_menu">Счет</a> |
             <a href="/profile" class="top_menu">Личные данные</a>
            ';
            */
            
        } else {
            $rs = '&nbsp;';
        }
        return $rs;
    }
    
    /**
     * Get main menu 
     * @param int $user_id user ID
     * @return string
     */
    function getMainMenu ( $user_id ) {
        $rs = '';
        if ( $this->getAccessDefined('project_manager', 'view_list') ) {
            $rs .= ' <li><a href="/project" class="mainlevel-son-of-suckerfish-horizontal"><span>Проекты</span></a>';
            if ( $this->getAccessDefined('archive_manager', 'view_list') ) {
                $rs .= '<ul id="menulist_10-son-of-suckerfish-horizontal">';
                $rs .= '<li class="submenu_top"></li>';
                $rs .= ' <li><a href="project" class="sublevel-son-of-suckerfish-horizontal"><span>Список проектов</span></a></li> ';
                $rs .= ' <li><a href="archive" class="sublevel-son-of-suckerfish-horizontal"><span>Архив</span></a></li> ';
                $rs .= '<li class="submenu_bottom"></li>';
                $rs .= '</ul>';
            }
            $rs .= '</li>';
            
        }
        if ( $this->getAccessDefined('task_manager', 'view_list') ) {
            $rs .= ' <li><a href="task" class="mainlevel-son-of-suckerfish-horizontal"><span>Задачи</span></a></li> ';
        }
            
            
        if ( $this->getAccessDefined('bookkeeper', 'view_list') ) {
            $rs .= ' <li><a href="bookkeeper" class="mainlevel-son-of-suckerfish-horizontal"><span>Бухгалтерия</span></a> ';
            
            if ( $this->getAccessDefined('bookkeeper', 'cash_flow') ) {
                $rs .= '<ul id="menulist_10-son-of-suckerfish-horizontal">';
                $rs .= '<li class="submenu_top"></li>';
                $rs .= ' <li><a href="bookkeeper/" class="sublevel-son-of-suckerfish-horizontal"><span>Состояния ЛС</span></a></li> ';
                $rs .= ' <li><a href="bookkeeper/cash_flow" class="sublevel-son-of-suckerfish-horizontal"><span>Движение средств</span></a></li> ';
                if ( $this->getAccessDefined('bookkeeper', 'product') ) {
                    $rs .= ' <li><a href="bookkeeper/product" class="sublevel-son-of-suckerfish-horizontal"><span>Продукты</span></a></li> ';
                }
                
                $rs .= '<li class="submenu_bottom"></li>';
                $rs .= '</ul>';
            }
            
            $rs .= '</li>';
        }
        
        
        if ( $this->getAccessDefined('money', 'cash_flow') ) {
            $rs .= ' <li><a href="money" class="mainlevel-son-of-suckerfish-horizontal"><span>Деньги</span></a></li> ';
        }
        
        if ( $this->getAccessDefined('com_service_admin', 'view_list') ) {
            $rs .= ' <li><a href="serviceadmin" class="mainlevel-son-of-suckerfish-horizontal"><span>Управление услугами</span></a></li> ';
        }
        
        if ( $this->getAccessDefined('dialog', 'view_list') ) {
            $rs .= ' <li><a href="dialog/" class="mainlevel-son-of-suckerfish-horizontal"><span>Редактор диалогов</span></a></li> ';
        }
        
        $rs .= '<li><a href="profile" class="mainlevel-son-of-suckerfish-horizontal"><span>Мой профиль</span></a></li> ';
        
        if ( $user_id > 1 ) {
            /*
            $rs .= '
             <a href="/service" class="top_menu">Услуги</a> | 
             <a href="/account" class="top_menu">Счет</a> |
             <a href="/profile" class="top_menu">Личные данные</a>
            ';
            */
            
        } else {
            $rs = '&nbsp;';
        }
        $rs .= '<li><a href="doc/" class="mainlevel-son-of-suckerfish-horizontal"><span>Документация</span></a></li> ';
        
        return $rs;
    }
    
    /**
     * Do service pay for user 
     * @param int $user_id user ID
     * @param int $service_id service ID
     * @return string
     */
    function doServicePay ( $user_id, $service_id ) {
        if ( $this->getDebugMode() ) {
            echo "Account->doServicePay(user_id = $user_id, service_id = $service_id)<br>";
        }
        //Get service cost
        $service_cost = $this->getServiceCost($service_id);
        //Minus service cost from user account status
        $this->minusMoney($user_id, $service_cost);
    }
    
    /**
     * Get service cost
     * @param int $service_id service ID
     * @return int
     */
    function getServiceCost ( $service_id ) {
        $query = "select cost from service where service_id = $service_id";
        $this->db->exec($query);
        $this->db->fetch_assoc();
        
        if ( $this->getDebugMode() ) {
            echo "Account->getServiceCost(service_id = $service_id, cost = ".$this->db->row['cost'].")<br>";
        }
        
        return $this->db->row['cost'];
    }
    
    /**
     * Minus money
     * @param int $user_id user ID
     * @param int $money money
     * @return boolean
     */
    function minusMoney ($user_id, $money) {
        if ( $this->getDebugMode() ) {
            //echo "Account->minusMoney(user_id = $user_id, money = $money)<br>";
        }
        //get previous account value
        $account_status = $this->getAccountValue($user_id);
        $account_status = $account_status - $money;
        $query = "update ".DB_PREFIX."_user set account = $account_status where user_id = $user_id";
        $this->db->exec($query);
    }
}
?>