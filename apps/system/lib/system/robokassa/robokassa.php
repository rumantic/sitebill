<?php
/**
 * Robokassa class
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class Robox extends SiteBill {
    /**
     * Constructor
     */
    function Robox () {
        $this->SiteBill();
    }
    
    /**
     * Main
     */
    function main () {
        global $init, $config;
        if ( preg_match('/result/', $_SERVER['REQUEST_URI']) ) {
            if ( $this->checkBillInfo( $this->getRequestValue('InvId') ) ) {
                //activate bill
                $this->activateBill($this->getRequestValue('InvId'), $this->getRequestValue('OutSum'));
                $rs = Multilanguage::_('PAYMENT_SUCCESS','system');
                $this->writeLog(array('apps_name'=>'robokassa_system', 'method' => __METHOD__, 'message' => "OK".$this->getRequestValue('InvId').'sum = '.$this->getRequestValue('OutSum'), 'type' => NOTICE));
                
                echo "OK".$this->getRequestValue('InvId')."\n";
                if ( $this->getConfigValue('notify_about_payment') ) {
                	/*require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
                		
                	$mailer = new Mailer();*/
                	$subject = $_SERVER['SERVER_NAME'].': Выполнен платеж на сумму '.$this->getRequestValue('OutSum');
                	$to = ($this->getConfigValue('add_notification_email')!='' ? $this->getConfigValue('add_notification_email') : $this->getConfigValue('order_email_acceptor'));
                	$from = $this->getConfigValue('system_email');
                	$body = 'Идентификатор платежа '.$this->getRequestValue('InvId');
                	/*if ( $this->getConfigValue('use_smtp') ) {
                		$mailer->send_smtp($to, $from, $subject, $body, 1);
                	} else {
                		$mailer->send_simple($to, $from, $subject, $body, 1);
                	}*/
                	$this->sendFirmMail($to, $from, $subject, $body);
                }
                exit;
                
            } else {
                $rs = $this->GetErrorMessage();
            }
        } elseif( preg_match('/success/', $_SERVER['REQUEST_URI']) ) {
        	$this->writeLog(array('apps_name'=>'robokassa_system', 'method' => __METHOD__, 'message' => "success ".$this->getRequestValue('OutSum'), 'type' => NOTICE));
        	 
            $rs = sprintf(Multilanguage::_('PAYMEN_ON_SUM_SUCCESS','system'),$this->getRequestValue('OutSum').' руб.')."<br><br>";
            $rs .= sprintf(Multilanguage::_('YOU_ACCOUNT_SUM','system'),$this->getAccountValue( $_SESSION['user_id'] ).' руб.').'<br>';
            $rs .= '<div style="color: green;" align="center"><br>
        		<a href="'.SITEBILL_MAIN_URL.'/account/data/?do=new">'.Multilanguage::_('ADD_AD','system').'</a></div>';
            
        } else {
            $rs = Multilanguage::_('PAYMENT_ERROR','system')."</a>";    
        }
        return '<div id="bigger">'.$rs.'</div>';
    }
    
    
    /**
     * Get shop order
     * @param int $bill_id bill id
     * @return mixed
	 */
    function getShopOrder ( $bill_id ) {
        $query = "select so.code from shop_order so, bill b where so.bill_id=b.bill_id and b.bill_id=$bill_id";
        $this->db->exec($query);
        $this->db->fetch_assoc();
        if ( $this->db->row['code'] != '' ) {
            return $this->db->row['code']; 
        }
        return false;
    }
    
    /**
     * Get account value
     * @param int $user_id
     * @return int
     */
    function getAccountValue( $user_id ) {
    	$account=0;
    	$DBC=DBC::getInstance();
        $query = 'SELECT account FROM '.DB_PREFIX.'_user WHERE user_id=? LIMIT 1';
        $stmt=$DBC->query($query, array((int)$user_id));
        if($stmt){
        	$ar=$DBC->fetch($stmt);
        	$account=$ar['account'];
        }
        return $account;
    }
    
    /**
     * Activate bill
     * @param int $bill_id bill id
     * @param string $OutSum OutSum
     * @return boolean
     */
    function activateBill ( $bill_id, $OutSum ) {
    	$user_id=0;
        $DBC=DBC::getInstance();
        $query = 'SELECT * FROM '.DB_PREFIX.'_bill WHERE bill_id=? LIMIT 1';
        $stmt=$DBC->query($query, array($bill_id));
        if($stmt){
        	$ar=$DBC->fetch($stmt);
        	$user_id=$ar['user_id'];
        	$bill_info=$ar;
        }
        
        $payment_type='recharge';
        
        if(isset($bill_info['payment_type']) && $bill_info['payment_type']!=''){
        	$payment_type=$bill_info['payment_type'];
        }
        
        
        
       	switch($payment_type){
       		case 'buy_tariff' : {
       			if($bill_info['payment_params']!=''){
       				$tariff_params=unserialize($bill_info['payment_params']);
       			}else{
       				$tariff_params=array();
       			}
       			
       			if(isset($tariff_params['tariff_id']) && 0!=(int)$tariff_params['tariff_id']){
       				require_once SITEBILL_DOCUMENT_ROOT.'/apps/billing/admin/admin.php';
       				$BA=new billing_admin();
       				$BA->setTariffToUser((int)$tariff_params['tariff_id'], $user_id);
       				$query = 'UPDATE '.DB_PREFIX.'_bill SET status=1 WHERE bill_id=?';
       				$stmt=$DBC->query($query, array($bill_id));
       			}
       			break;
       		}
       		case 'accesskey_buy' : {
       			require_once SITEBILL_DOCUMENT_ROOT.'/apps/watchlistmanager/admin/admin.php';
    			$WLM=new watchlistmanager_admin();
    			$WLM->activateWatchlist($bill_id);
    			$query = 'UPDATE '.DB_PREFIX.'_bill SET status=1 WHERE bill_id=?';
    			$stmt=$DBC->query($query, array($bill_id));
       			break;
       		}
       		default : {
       			if($user_id!=0){
       				$OutSum=$bill_info['sum'];
       				$account_value = $this->getAccountValue( $user_id );
       				$account_value += $OutSum;
       				 
       				//set new account value
       				$query = 'UPDATE '.DB_PREFIX.'_user SET account=? WHERE user_id=?';
       				$stmt=$DBC->query($query, array($account_value, $user_id));
       				 
       				//set status
       				$query = 'UPDATE '.DB_PREFIX.'_bill SET status=1 WHERE bill_id=?';
       				$stmt=$DBC->query($query, array($bill_id));
       			}
       		}
       		
       	}
       
       	
        
    }
    
    /**
     * Check signature
     * @param string $out_sum out sum
     * @param int $inv_id inv id
     * @param int $shp_item 
     * @param string $crc crc
     * @return boolean
     */
    function checkSignature ( $out_summ, $inv_id, $shp_item, $crc ) {
        
        $mrh_pass2 = $this->getConfigValue('robokassa_password2');
        
        $crc = strtoupper($crc);

        $my_crc = strtoupper(md5("$out_summ:$inv_id:$mrh_pass2"));
        if ($my_crc !=$crc) {
            echo "bad sign\n";
            exit();
        }
        return true;
    }
    
    /**
     * Check bill info
     * @param int $bill_id bill id
     * @return boolean
     */
    function checkBillInfo ( $bill_id ) {
    	$status=0;
    	$DBC=DBC::getInstance();
        $query = 'SELECT `status` FROM '.DB_PREFIX.'_bill WHERE `bill_id`=? LIMIT 1';
        $stmt=$DBC->query($query, array($bill_id));
    	if($stmt){
        	$ar=$DBC->fetch($stmt);
        	$status=$ar['status'];
        }
        if ( $status != 0 ) {
            $this->RiseError(Multilanguage::_('ORDER_PAYED_NOW','system'));
            return false;
        }
        if ( !$this->checkSignature( $_REQUEST["OutSum"], $_REQUEST["InvId"], $_REQUEST["Shp_item"], $_REQUEST["SignatureValue"] ) ) {
            $this->RiseError("bad sign\n");
        }
        return true;
    }
}
?>