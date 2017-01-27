<?php
/**
 * Rent order class
 * @author Kondin Dmitriy <kondin@etown.ru>
 * @url http://www.sitebill.ru
 */
class Rent_Order extends Sitebill_Data_Get_Rent {
    /**
     * Constructor
     */
    function __construct() {
        $this->Sitebill_Data_Get_Rent();
    }
    
    /**
     * Main
     * @param void
     * @return string
     */
    function main () {
        switch ( $this->getRequestValue('do') ) {
            case 'delete':
                $this->deleteRecord($this->getRequestValue('data_get_rent_id'));
                $rs .= $this->grid();
            break;

            default:
                $rs .= $this->grid();
        }                
        return $rs;
    }
    
    /**
     * Delete record
     * @param int $record_id record ID
     * @return boolean
     */
    function deleteRecord ( $record_id ) {
        $query = "delete from ".DB_PREFIX."_data_get_rent where data_get_rent_id=$record_id";
        $DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
        return true;
    }
    
    /**
     * Grid
     * @param void
     * @return string
     */
    function grid () {
        global $_SESSION;
        $ra=array();
        $query = "select * from ".DB_PREFIX."_data_get_rent order by date_added desc";
        $DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
		if($stmt){
			while ( $ar=$DBC->fetch($stmt) ) {
				$ra[] = $ar;
			}
		}
        
        if ( count($ra) < 1 ) {
            return Multilanguage::_('NO_RENT_APPLICATIONS','system');
        }
        
        $rs = '<div align="left"><table border="0" width="90%">';
        $rs .= '<td class="row_title">'.Multilanguage::_('L_DATE').'</td>';
        $rs .= '<td class="row_title">'.Multilanguage::_('L_FIO').'</td>';
        $rs .= '<td class="row_title">'.Multilanguage::_('L_PHONE').'</td>';
        $rs .= '<td class="row_title">Email</td>';
        $rs .= '<td class="row_title">'.Multilanguage::_('L_ROOM_COUNT').'</td>';
        $rs .= '<td class="row_title">'.Multilanguage::_('RENT_ON','system').'</td>';
        $rs .= '<td class="row_title">'.Multilanguage::_('L_DISTRICT').'</td>';
        $rs .= '<td class="row_title">'.Multilanguage::_('WISHES','system').'</td>';
        $rs .= '<td class="row_title"></td>';
        $rs .= '</tr>';
        
        foreach ( $ra as $item_id => $item_array ) {
            $j++;
            if ( ceil($j/2) > floor($j/2)  ) {
                $row_class = "row1";
            } else {
                $j = 0;
                $row_class = "row2";
            }
            $rs .= '<tr>';
            $rs .= '<td class="'.$row_class.'">'.date('d.m.Y H:i',$item_array['date_added']).'</td>';
            $rs .= '<td class="'.$row_class.'" nowrap width="10%">'.$item_array['name'].'</td>';
            $rs .= '<td class="'.$row_class.'">'.$item_array['phone'].'</td>';
            $rs .= '<td class="'.$row_class.'">'.$item_array['email'].'</td>';
            $rs .= '<td class="'.$row_class.'">'.$this->getRoomTitleByID($item_array['room_type_id']).'</td>';
            $rs .= '<td class="'.$row_class.'">'.$this->getTimeRangeTitleByID($item_array['time_range_id']).'</td>';
            $rs .= '<td class="'.$row_class.'">'.$this->getDistrictTitleByID($item_array['district_id']).'</td>';
            $rs .= '<td class="'.$row_class.'">'.$item_array['more'].'</td>';
            $rs .= '<td width="1%" nowrap><a href="?action=rent_order&do=delete&data_get_rent_id='.$item_array['data_get_rent_id'].'" onclick="if ( confirm(\''.Multilanguage::_('L_MESSAGE_REALLY_WANT_DELETE').'\') ) {return true;} else {return false;}"><img src="'.SITEBILL_MAIN_URL.'/img/delete.gif" border="0"></a></td>';
            $rs .= '</tr>';
        }
        $rs .= '</table></div>';
        return $rs;
    }
}