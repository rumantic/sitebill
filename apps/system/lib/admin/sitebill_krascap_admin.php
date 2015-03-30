<?php
/**
 * SiteBill krascap admin class
 * @author Kondin Dmitriy <kondin@etown.ru>
 */

class SiteBill_Krascap_Admin extends SiteBill_Krascap {
    /**
     * Constructor
     */
    function SiteBill_Krascap_Admin() {
        global $sitebill_document_root;
        
        $this->SiteBill();
        //$this->template->setTemplateFile(SITEBILL_DOCUMENT_ROOT.'/template/frontend/estate/admin.html');
    }
    
    /**
     * Main
     * @param void
     * @return string
     */
    function main () {
        switch ( $this->getRequestValue('do') ) {
            case 'load_done':
                $csv_strings = $this->loadData();
                if ( !$csv_strings ) {
                    $rs = $this->getLoadForm();
                } else {
                    $rs = $this->importData( $csv_strings );
                    if ( $this->getError() ) {
                        $rs = $this->getLoadForm();
                    }
                }
            break;
            
            default:
                $rs = $this->getLoadForm();
            break;
        }
		$this->template->assert('base', SITEBILL_ADMIN_BASE);
        $this->template->assert('main', $rs);
        $this->template->render();
        $rs = $this->template->toHTML();
        return $rs;
    }
    
    /**
     * Clear items
     * @param int $topic_id topic id
     * @return boolean
     */
    function clearItems ( $topic_id ) {
        $query = "delete from re_data where topic_id=$topic_id and hot <> 1 and img1 = '' and img2 = '' and img3 = '' and img4 = '' and img5 = ''";
        //echo $query;
        $this->db->exec($query);
    }
    
    /**
     * Import data
     * @param array $csv_strings csv string
     * @return boolean
     */
    function importData ( $csv_string ) {
        $record_number = 0;
        $error_number = 0;
        if ( $this->getRequestValue('clear') == 'yes' ) {
            $this->clearItems( $this->getRequestValue('topic_id') );
        }
        foreach ( $csv_string as $key => $string ) {
            $items = explode("\t", $string);
            $items_count = count($items);
            if ( $items_count == 17 ) {
                $added = false;
                if ( $this->getRequestValue('topic_id') == 2 ) {
                    $added = $this->addRecord( $items, $record_number );
                    if ( $this->getError() ) {
                        return false;
                    }
                } elseif ( $this->getRequestValue('topic_id') == 1 ) {
                    $added = $this->addRentRecord( $items, $record_number );
                    if ( $this->getError() ) {
                        return false;
                    }
                }
                if ( $added === true ) {
                    $record_number++;
                } else {
                    $error_number++;
                }
            }
            //$rs .= "Каждая строка по $items_count элементов<br>";
        }
        //$rs .= "Каждая строка по $items_count элементов<br>";
        $rs = sprintf(Multilanguage::_('L_MESSAGE_SUCCESSFULLY_UPLOADED_N_STRINGS'),$record_number);
        $rs .= Multilanguage::_('L_MESSAGE_RECORDS_SKIPED_BY_ERROR').' '.$error_number.'<br>';
        return $rs;
    }
    
    /**
     * Get disrtict ID by name
     * @param string $name name
     * @return int
     */
    function getDistrictIdByName ( $name ) {
        $query = "select id from re_district where name like '%$name%'";
        $this->db->exec($query);
        $this->db->fetch_assoc();
        if ( $this->db->row['id'] > 0 ) {
            return $this->db->row['id'];
        } else {
            //$this->riseError('Район не определен');
            return false;
        }
    }
    
    /**
     * Get type by name for rent
     * @param string $name name
     * @return int
     */
    function getTypeByNameForRent ( $name ) {
        $rent_type_hash = array();
        $rent_type_hash['гостинка'] = 12; 
        $rent_type_hash['комната'] = 10; 
        $rent_type_hash['секционка'] = 11; 
        $rent_type_hash['1'] = 13; // 1-комн. квартира 
        $rent_type_hash['2'] = 14; // 2-комн. квартира 
        $rent_type_hash['3'] = 15; // 3-комн. квартира 
        $rent_type_hash['4'] = 16; // 4-комн. квартира 
        $rent_type_hash['5'] = 16; // 5-комн. квартира

        if ( $rent_type_hash[$name] == '' ) {
            return $rent_type_hash['1']; 
        } else {
            return $rent_type_hash[$name]; 
        }
    }
    
    /**
     * Get rent room count
     * @param string $name name
     * @return int
     */
    function getRentRoomCount ( $name ) {
        $rent_room_count_hash = array();
        $rent_room_count_hash['гостинка'] = 0; 
        $rent_room_count_hash['комната'] = 0; 
        $rent_room_count_hash['секционка'] = 0; 
        $rent_room_count_hash['1'] = 1; // 1-комн. квартира 
        $rent_room_count_hash['2'] = 2; // 2-комн. квартира 
        $rent_room_count_hash['3'] = 3; // 3-комн. квартира 
        $rent_room_count_hash['4'] = 4; // 4-комн. квартира 
        $rent_room_count_hash['5'] = 5; // 5-комн. квартира

        if ( $rent_room_count_hash[$name] == '' ) {
            return $rent_room_count_hash['1']; 
        } else {
            return $rent_room_count_hash[$name]; 
        }
    }
    
    /**
     * Add rent record
     * @param array $items items
     * @param int $record_number record number
     * @return boolean
     */
    function addRentRecord ( $items, $record_number = 0 ) {
        if ( $record_number == 0 ) {
            if ( !$this->checkRentFormat($items) ) {
                $this->riseError(Multilanguage::_('L_ERROR_BAD_FILE_FORMAT'));
                return false;
            } else {
                return true;
            }
        }
        //echo '<pre>';
        //print_r($items);
        //echo '</pre>';
        
        $type_id = $this->getTypeByNameForRent($items[0]);
        //$type = $this->getRequestValue('type_id');
        $topic_id = 1;
        $topic_id1 = 0;
        $topic_id2 = 0;
        $distr = $this->getDistrictIdByName($items[1]);
        if ( !$distr ) {
            return false;
        }
        $price = $items[13]*1000;
        $description = $items[16];
        $agent_tel = $items[14];
        $contact = $items[15];
        $room_count = $this->getRentRoomCount($items[0]);
        $street = $items[3];
        $elite = 0;
        $hot = 0;
        $sessid = '';
        $active = 1;
        $floor = $items[5];
        $floor_count = $items[6];
        
        //$balcony = $items[8];
        $walls = $items[4];
        $square_all = $items[8];
        $square_live = $items[9];
        $square_kitchen = $items[10];
        $bathroom = $items[11];
        
        
        $query = "
            insert 
                into re_data 
                        (`type_id`, `topic_id`, `sub_id1`,  `sub_id2`,  `district_id`, `price`, `text`,         `contact`,  `agent_tel`,  `room_count`,  `street`, `elite`, `session_id`, `active`, `date_added`, `hot`, `floor`,  `floor_count`,  `balcony`,  `square_all`,  `square_live`,  `square_kitchen`,  `bathroom`,  `walls`)
                values  ($type_id,  $topic_id,  $topic_id1, $topic_id2, $distr,        $price, '$description', '$contact', '$agent_tel','$room_count', '$street', $elite, '$sessid',     $active,   now(),       $hot, '$floor', '$floor_count', '$balcony', '$square_all', '$square_live', '$square_kitchen', '$bathroom', '$walls')
            ";
        //echo $query."<br>";
        $this->db->exec($query);
        
        return true;
    }
    
    /**
     * Check rent format
     * @param array $items items
     * @return boolean
     */
    function checkRentFormat ( $items ) {
        //echo '<pre>';
        //print_r($items);
        //echo '</pre>';
        
        if ( $items[0] != 'Комн' ) {
            return false;
        }
        if ( $items[1] != 'Район' ) {
            return false;
        }
        if ( $items[2] != 'Микрорайон' ) {
            return false;
        }
        if ( $items[3] != 'Улица' ) {
            return false;
        }
        if ( $items[4] != 'Дом' ) {
            return false;
        }
        if ( $items[5] != 'этаж' ) {
            return false;
        }
        if ( $items[6] != 'этажность' ) {
            return false;
        }
        if ( $items[7] != 'Б/Л' ) {
            return false;
        }
        if ( $items[8] != 'общая площадь' ) {
            return false;
        }
        if ( $items[9] != 'жилая площадь' ) {
            return false;
        }
        if ( $items[10] != 'кухня площадь' ) {
            return false;
        }
        if ( $items[11] != 'С.уз.' ) {
            return false;
        }
        if ( $items[12] != 'Срок сдачи' ) {
            return false;
        }
        if ( $items[13] != 'Цена тыс.руб.' ) {
            return false;
        }
        if ( $items[14] != 'Телефон' ) {
            return false;
        }
        if ( $items[15] != 'Контактное лицо' ) {
            return false;
        }
        return true;
    }
    
    /**
     * Check sales format
     * @param array $items items
     * @return boolean
     */
    function checkSalesFormat ( $items ) {
        if ( $items[0] != 'кол. ком.' ) {
	    echo 0;
            return false;
        }
        if ( $items[1] != 'адм. район' ) {
	    echo 1;
            return false;
        }
        if ( $items[2] != 'ориентир' ) {
	    echo 2;
            return false;
        }
        if ( $items[3] != 'улица' ) {
	    echo 3;
            return false;
        }
        if ( $items[4] != 'м. стен' ) {
	    echo 4;
            return false;
        }
        if ( $items[5] != 'этаж' ) {
	    echo 5;
            return false;
        }
        if ( $items[6] != 'этажность' ) {
	    echo 6;
            return false;
        }
        if ( $items[7] != 'тип дома' ) {
	    echo 7;
            return false;
        }
        if ( $items[8] != 'балкон' ) {
	    echo 8;
            return false;
        }
        if ( $items[9] != 'пл. общая' ) {
	    echo 9;
            return false;
        }
        if ( $items[10] != 'пл. жилая' ) {
	    echo 10;
            return false;
        }
        if ( $items[11] != 'пл. кухни' ) {
	    echo 11;
            return false;
        }
        if ( $items[12] != 'сан. узел' ) {
	    echo 12;
            return false;
        }
        if ( $items[13] != 'цена' ) {
	    echo 13;
            return false;
        }
        if ( $items[14] != 'риэлтор (т. контактный)' ) {
	    echo 14;
            return false;
        }
        if ( $items[15] != 'риэлтор (ФИО)' ) {
	    echo 15;
            return false;
        }
        return true;
    }
    
    /**
     * Get type by name
     * @param string $name name
     * @return int
     */
    function getTypeByName ( $name ) {
        $type_hash = array();
        $type_hash['Гос'] = 22; // гостинка
        $type_hash['Инд'] = 22; // дом
        $type_hash['Ком'] = 20; // комната
        $type_hash['общ'] = 20; // общ - комната
        $type_hash['Лн'] = 22; // Лн - квартира
        $type_hash['НП'] = 22; // НП - квартира
        $type_hash['Ст'] = 22; // Ст - квартира
        $type_hash['Ул'] = 22; // Ул - квартира
        $type_hash['Хр'] = 22; // Хр - квартира
        $type_hash['Сек'] = 21; // Сек - секционка
        $type_hash[''] = 22; // Пустое поле по-умолчанию - квартира
        if ( $type_hash[$name] == '' ) {
            return 2;
        } else {
            return $type_hash[$name];
        }
    }
    
    /**
     * Get type_id by room count
     * @param int $topic_id topic ID
     * @param int $room_count room count
     * @return int
     */
    function getTypeIDByRoomCount ($topic_id, $room_count ) {
        $query = "select id from re_topic where parent_id=$topic_id and sql_where = 'room_count = $room_count'";
        $this->db->exec($query);
        $this->db->fetch_assoc();
        if ( $this->db->row['id'] > 0 ) {
            return $this->db->row['id']; 
        }
        return false;
    }
    
    /**
     * Add record
     * @param array $items items
     * @param int $record_number record number
     * @return boolean
     */
    function addRecord ( $items, $record_number = 0 ) {
        if ( $record_number == 0 ) {
            if ( !$this->checkSalesFormat($items) ) {
                $this->riseError(Multilanguage::_('L_ERROR_BAD_FILE_FORMAT'));
                return false;
            } else {
                return true;
            }
        }
        //echo '<pre>';
        //print_r($items);
        //echo '</pre>';
        
        //$type = $this->getTypeByName($items[7]);
        //$type = $this->getRequestValue('type_id');
        $topic_id = 2;
        $type_id = $this->getTypeIDByRoomCount($topic_id, $items[0]);
        if ( !$type_id ) {
            $type_id = $this->getTypeByName($items[7]);
        }
        $topic_id1 = 0;
        $topic_id2 = 0;
        $distr = $this->getDistrictIdByName($items[1]);
        if ( $this->getError() ) {
            return false;
        }
        $price = $items[13];
        $description = $items[16];
        $contact = $items[15];
        $agent_tel = $items[14];
        $room_count = $items[0];
        $street = $items[3];
        $walls = $items[4];
        $floor = $items[5];
        $floor_count = $items[6];
        $balcony = $items[8];
        $square_all = $items[9];
        $square_live = $items[10];
        $square_kitchen = $items[11];
        $bathroom = $items[12];
        
        $elite = 0;
        $hot = 0;
        $sessid = '';
        $active = 1;
        $query = "
            insert 
                into re_data 
                        (`type_id`, `topic_id`, `sub_id1`, `sub_id2`,  `district_id`, `price`, `text`,         `contact`,  `agent_tel`, `room_count`,  `street`, `elite`, `session_id`, `active`, `date_added`, `hot`, `walls`,  `floor`,  `floor_count`,  `balcony`,  `square_all`,  `square_live`,  `square_kitchen`,  `bathroom`)
                values  ($type_id,  $topic_id,  $topic_id1, $topic_id2, $distr,       $price, '$description', '$contact', '$agent_tel', '$room_count', '$street', $elite, '$sessid',     $active,   now(),       $hot, '$walls', '$floor', '$floor_count', '$balcony', '$square_all', '$square_live', '$square_kitchen', '$bathroom')
            ";
        //echo $query."<br>";
        $this->db->exec($query);
        //exit;
        return true;
    }
    
    //-----------------------------------------------------------------------------
    function gramm_correct ($txt) {
        $txt = preg_replace("/([\w]+)([\s]*)([,.!:)]+)/", "\$1\$3 ", $txt);
        $txt = preg_replace("/([\d]+)([\s]*)([,]+)([\s]*)([\d]+)/", "\$1\$3\$5", $txt);
        $txt = preg_replace("/([\s]+)/", " ", $txt);
        return($txt);
    }

    //-----------------------------------------------------------------------------
    function add_info($active = 1) {
        global $topic_id, $topic_id1, $topic_id2, $jump_page;   

        $id = $_REQUEST['id'];
        $room_count = (int)($_REQUEST['room_count']);
        $distr = (int)($_REQUEST['district']);
        $hot = $elite = 0;
        $sessid = '';
        if (isset($_REQUEST['elite']))
        {
            $elite = 1;
        }
        if (isset($_REQUEST['hot']))
        {
            $hot = 1;
        }
        $street = mysql_real_escape_string($_REQUEST['street']);
        $type = (int)($_REQUEST['type']);
        $price = $_REQUEST['price'] * $_REQUEST['price_type'];
        $description = $this->gramm_correct(mysql_real_escape_string($_REQUEST['description']));
        $contact = mysql_real_escape_string($_REQUEST['contact']);
        $date_added = date("Y-m-d H:i:s");
        if ($active == 0)
        {
            $sessid = session_id();
        }

        if ($id <= 0)
        {
            $query = "
                insert 
                    into re_data 
                    (`type_id`, `topic_id`, `sub_id1`, `sub_id2`, `district_id`, `price`, `text`, `contact`, `room_count`, `street`, `elite`, `session_id`, `active`, `date_added`, `hot`)
                    values  ($type, $topic_id, $topic_id1, $topic_id2, $distr, $price, '$description', '$contact', '$room_count', '$street', $elite, '$sessid', $active, '$date_added', $hot)
            ";
        }
        else
        {
            $query = "
                update 
                    re_data 
                    set
                        `type_id` = $type,
                        `topic_id` = $topic_id,
                        `sub_id1` = $topic_id1,
                        `sub_id2` = $topic_id2,
                        `district_id` = '$distr', 
                        `price` = $price, 
                        `street` = '$street', 
                        `elite` = $elite, 
                        `text` = '$description', 
                        `contact` = '$contact',
                        `room_count` =  '$room_count',
                        `active` = $active,
                        `hot` = $hot
                    where id = $id
            ";
        }
        $res = mysql_query($sql) or die ($sql . ' - ' . mysql_error());
        if ($id <= 0)
        {
            $id = $_REQUEST['id'] = mysql_insert_id();
        }
    }
    
    
    
    /**
     * Check data
     * @param void
     * @return boolean
     */
    function loadData () {
        if ( !is_uploaded_file($_FILES['csv']['tmp_name']) ) {
            $this->riseError(Multilanguage::_('L_ERROR_CANT_UPLOAD_FILE'));
            return false;
        }
        $content = file_get_contents($_FILES['csv']['tmp_name']);
        $this->csv_strings = explode("\n", $content);
        
        if ( !is_array($this->csv_strings) ) {
            $this->riseError(Multilanguage::_('L_ERROR_BAD_FILE_FORMAT'));
            return false;
        }
        
        return $this->csv_strings;
    }
    
    /**
     * Get load form
     * @param void
     * @return string
     */
    function getLoadForm () {
        
        $rs .= '<form method="post" action="index.php" name="rentform" enctype="multipart/form-data">';
        $rs .= '<table border="0">';
        
        $rs .= '<tr>';
        $rs .= '<td colspan="2" style="text-align: center;"><b>'.sprintf(Multilanguage::_('L_NEED_REQUIERD_FIELDS').'<span class="error">*</span>').'</b></td>';
        $rs .= '</tr>';
        
        if ( $this->GetError() ) {
            $rs .= '<tr>';
            $rs .= '<td></td>';
            $rs .= '<td><span class="error">'.$this->GetError().'</span></td>';
            $rs .= '</tr>';
        }
        
        $rs .= '<tr>';
        $rs .= '<td class="left_column">'.Multilanguage::_('CSV_FORMAT_FILE','system').'<span class="error">*</span>:</td>';
        $rs .= '<td><input type="file" name="csv"></td>';
        $rs .= '</tr>';
        
        $rs .= '<tr>';
        $rs .= '<td class="left_column">'.Multilanguage::_('DELETE_1','system').':</td>';
        $rs .= '<td><input type="checkbox" name="clear" value="yes"></td>';
        $rs .= '</tr>';
        
        $rs .= '<tr>';
        $rs .= '<td></td>';
        $rs .= '<input type="hidden" name="do" value="load_done">';
        $rs .= '<input type="hidden" name="topic_id" value="'.$this->getRequestValue('topic_id').'">';
        
        $rs .= '<td><input type="submit" value="'.Multilanguage::_('L_TEXT_LOAD').'"></td>';
        $rs .= '</tr>';
        $rs .= '</table>';
        $rs .= '</form>';
        
        return $rs;
    }
}
?>