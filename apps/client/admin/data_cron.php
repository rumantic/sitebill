<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * Data cron
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class data_cron extends Object_Manager {
	function run () {
		//@todo: тут выполняем процедуры для перевода заявок из одного data.status_id в другой
		//видимо надо будет еще добавить даты
		
		$DBC=DBC::getInstance();
		
		//из актуальных в прозвон
		$actual_limit=3;
		$max_actual_term=date('Y-m-d H:i:s', time()-$actual_limit*24*3600);
		
		$query='UPDATE '.DB_PREFIX.'_data SET `status_id`=?, `status_change`=? WHERE `status_id`=1 AND `status_change`<=?';
		$stmt=$DBC->query($query, array(2, date('Y-m-d H:i:s', time()), $max_actual_term));
		
		//из безстатусных в прозвон
		
		$query='UPDATE '.DB_PREFIX.'_data SET `status_id`=?, `status_change`=? WHERE `status_id`=0';
		$stmt=$DBC->query($query, array(2, date('Y-m-d H:i:s', time())));
		
		//из архива в прозвон
		$to_restore=array();
		$archive_limit=30;
		$max_archive_term=date('Y-m-d H:i:s', time()-$archive_limit*24*3600);
		$query='SELECT `realtylog_id`, `id` FROM '.DB_PREFIX.'_realtylogv2 WHERE `log_date`<=? AND `action`=?';
		$stmt=$DBC->query($query, array($max_archive_term, 'delete'));
		if($stmt){
			while ($ar=$DBC->fetch($stmt)) {
				$to_restore[]=$ar;
			}
		}
		
		if(!empty($to_restore)){
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/realtylogv2/admin/admin.php';
			$RL=new realtylogv2_admin();
			foreach($to_restore as $v){
				$RL->restoreLog($v['realtylog_id']);
				$query='UPDATE '.DB_PREFIX.'_data SET `status_id`=?, `status_change`=? WHERE `id`=?';
				$stmt=$DBC->query($query, array(2, date('Y-m-d H:i:s', time()), $v['id']));
			}
		}
	}
}