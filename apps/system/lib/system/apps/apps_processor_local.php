<?php
class apps_processor_local extends SiteBill_Krascap {
	function local_apps_processor () {
		$this->apps_dir = SITEBILL_DOCUMENT_ROOT.'/apps';

		if ( preg_match('/simpleeditor/', $_SERVER['REQUEST_URI']) ) {
			$this->run_apps('fasteditor');
		} elseif ( preg_match('/freeorder/', $_SERVER['REQUEST_URI']) ) {
			$this->run_apps('freeorder');
		} elseif (preg_match('/\/news/', $_SERVER['REQUEST_URI'])) {
			$this->run_apps('news');
		}elseif ( $this->run_apps('page') ) {
				
		} else {
			$this->run_apps('realtypro');
		}
	}

	/**
	 * Set executed apps
	 * @param string $apps_name
	 */
	private function set_executed_apps ( $apps_name ) {
		$this->apps_executed[] = $apps_name;
	}

	/**
	 * Get executed apps
	 * @return Array
	 */
	function get_executed_apps () {
		return $this->apps_executed;
	}


	function run_apps ( $app_dir ) {
		if ( is_dir($this->apps_dir.'/'.$app_dir) and !preg_match('/\./', $app_dir) ) {
			if ( is_file($this->apps_dir.'/'.$app_dir.'/site/site.php') ) {
				require_once ($this->apps_dir.'/'.$app_dir.'/admin/admin.php');
				require_once ($this->apps_dir.'/'.$app_dir.'/site/site.php');
				$app_class_name = $app_dir.'_site';
				//echo $app_class_name.'<br>';
				$app_class_inst = new $app_class_name;
				if ( $app_class_inst->frontend() ) {
					$this->set_executed_apps($app_class_name);
					//closedir($dh);
					return true;
				}
			}
		}
		return false;
	}
}