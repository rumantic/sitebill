<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Angular admin backend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class angular_admin extends Object_Manager {
    /**
     * @var string
     */
    protected $dir = SITEBILL_DOCUMENT_ROOT.'/apps/angular/dist';

    function link_dist_files_to_root ($dist_files) {
        $rs = '';
        if ( is_array($dist_files) ) {
            foreach ($dist_files as $file) {
                if ( $file != 'index.html' ) {
                    if ( symlink($this->dir.'/'.$file, SITEBILL_DOCUMENT_ROOT.'/'.$file) ) {
                        $rs .= 'symlink created successfully: '.$file.'<br>'."\n";
                    } else {
                        $rs .= 'symlink create failed: '.$file.'<br>'."\n";
                    }
                }
            }
        }
        return $rs;
    }

    private function remove_file ( $file ) {
        if ( unlink($file) ) {
            $rs = 'removed successfully: '.$file.'<br>'."\n";
        } else {
            $rs = 'remove failed: '.$file.'<br>'."\n";
        }
        return $rs;
    }

    function delete_dist_files ( $dist_files ) {
        $rs = '';
        if ( is_array($dist_files) ) {
            foreach ($dist_files as $file) {
                if ( $file != 'index.html' ) {
                    if ( is_link(SITEBILL_DOCUMENT_ROOT.'/'.$file) ) {
                        $rs .= $this->remove_file(SITEBILL_DOCUMENT_ROOT.'/'.$file);
                    }
                    if ( is_file($this->dir.'/'.$file) ) {
                        $rs .= $this->remove_file($this->dir.'/'.$file);
                    }
                }
            }
        }
        return $rs;
    }

    function load_dist_files_list() {
        if (is_dir($this->dir)) {
            if ($dh = opendir($this->dir)) {
                while (($item = readdir($dh)) !== false) {
                    if (is_file($this->dir . '/' . $item) and ! preg_match('/^\./', $item)) {
                        $result['dist_files'][] = $item;
                        list($prefix, $random, $extension) = explode('.', $item);
                        if ( !in_array($item, array('index.php', 'install.php', '.htaccess')) ) {
                            $result['dist_files_prefixes'][$prefix] = $item;
                        }

                    }
                }
                closedir($dh);
            }
        }
        return $result;

    }
}
