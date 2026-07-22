<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
class news_update extends SiteBill {
    /**
     * Construct
     */
    function __construct() {
        parent::__construct();
    }

    function main () {
        $query_data[] = "ALTER TABLE ".DB_PREFIX."_news ADD COLUMN news_topic_id INT(11)";

        $query_data[] = "ALTER TABLE ".DB_PREFIX."_news ADD COLUMN meta_h1 text";
        $query_data[] = "ALTER TABLE ".DB_PREFIX."_news ADD COLUMN meta_title text";
        $query_data[] = "ALTER TABLE ".DB_PREFIX."_news ADD COLUMN meta_description text";
        $query_data[] = "ALTER TABLE ".DB_PREFIX."_news ADD COLUMN meta_keywords text";
        $query_data[] = "ALTER TABLE ".DB_PREFIX."_news ADD COLUMN newsalias text";

        $query_data[] = "ALTER TABLE ".DB_PREFIX."_news ADD COLUMN user_id INT(11)";

        $query_data[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."_news_topic` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `name` varchar(255) NOT NULL,
				  `url` varchar(255) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=".DB_ENCODING." ;";


        $rs = '<h3>'.Multilanguage::_('SQL_NOW','system').'</h3>';
        $DBC=DBC::getInstance();

        foreach ( $query_data as $query ) {
        	$success=false;
        	$stmt=$DBC->query($query, array(), $rows, $success);
        	if ( !$success ) {
        		$rs .= Multilanguage::_('ERROR_ON_SQL_RUN','system').': '.$query.'<br>';
        	} else {
        		$rs .= Multilanguage::_('QUERY_SUCCESS','system').': '.$query.'<br>';
        	}
        }

        // Удаление устаревших файлов
        $file = SITEBILL_DOCUMENT_ROOT.'/apps/news/js/ajax.php';
        $dir = SITEBILL_DOCUMENT_ROOT.'/apps/news/js';
        if(file_exists($file)){
            $rs .= 'Удаляем файл "' . $file . '".'.'<br>';
            unlink($file);
            if(file_exists($file)){
                $rs .= 'Файл "' . $file . '" не удален. Удалите его самостоятельно.'.'<br>';
            }else{
                $rs .= 'Файл "' . $file . '" удален успешно.'.'<br>';
                $rs .= 'Удаляем директорию "' . $dir . '".'.'<br>';
                rmdir($dir);
                if(is_dir($dir)){
                    $rs .= 'Директория "' . $dir . '" не удалена. Удалите ее самостоятельно.'.'<br>';
                }else{
                    $rs .= 'Директория "' . $dir . '" удалена успешно.'.'<br>';
                }
            }
        }
        return $rs;
    }
}
