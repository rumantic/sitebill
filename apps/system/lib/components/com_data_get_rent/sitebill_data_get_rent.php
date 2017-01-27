<?php
/**
 * Estate.sitebill.ru data get rent class 
 * @author Kondin Dmitriy <kondin@etown.ru>
 * @url http://www.sitebill.ru
 */
class Sitebill_Data_Get_Rent extends SiteBill_Krascap {
    var $room_item = array();
    var $time_range_item = array();
    
    /**
     * Constructor
     */
    function Sitebill_Data_Get_Rent() {
        $this->room_item[1] = 'Комната на подселение';
        $this->room_item[2] = 'Гостинка';
        $this->room_item[3] = '1-ком.';
        $this->room_item[4] = '2-ком.';
        $this->room_item[5] = '3-ком.';
        $this->room_item[6] = '4-ком.';

        $this->time_range_item[1] = 'Длительный срок';
        $this->time_range_item[2] = 'Короткое время';
        
        $this->SiteBill();
    }
    
    /**
     * Main
     * @param void
     * @return string
     */
    function main () {
        $data = $this->initDataFromRequest();
        if ( $this->getRequestValue('do') == 'add_done' ) {
            if ( $this->checkData( $data ) ) {
                $this->addRecord( $data );
                $rs = 'Ваше объявление успешно добавлено, менеджер свяжется с вами';
                return $rs;
            }
        }
        $rs = $this->getRentForm( $data );
        return $rs;
    }
    
    /**
     * Check data
     * @param array $data data
     * @return boolean
	 */
    function checkData ( $data ) {
        if ( $data['name'] == '' ) {
            $this->riseError('Укажите ваше имя');
            return false;
        }
        if ( $data['phone'] == '' ) {
            $this->riseError('Укажите ваш телефон');
            return false;
        }
         if ( $data['captcha_code'] != $_SESSION[$this->getRequestValue('captcha_session_key')] ) {
         	 $this->riseError('Неверно указано значение защитного кода');
            return false;
         }
         
        return true;
    }
    
    /**
     * Init data from request
     * @param void
     * @return array
     */
    function initDataFromRequest () {
        $data = array();
        $data['room_type_id'] = $this->getRequestValue('room_type_id');
        $data['time_range_id'] = $this->getRequestValue('time_range_id');
        $data['district_id'] = $this->getRequestValue('district_id');
        $data['name'] = $this->getRequestValue('name');
        $data['phone'] = $this->getRequestValue('phone');
        $data['email'] = $this->getRequestValue('email');
        $data['more'] = $this->getRequestValue('more');
        $data['captcha_code'] = $this->getRequestValue('captcha_code');
        return $data;
    }
    
    /**
     * Add record
     * @param array $data data
     * @return boolean
     */
    function addRecord ( $data ) {
        $time_now = time();
        
        $query = "insert into ".DB_PREFIX."_data_get_rent 
        	(room_type_id, time_range_id, district_id, name, phone, email, more, date_added) 
        		values 
        	(".$data['room_type_id'].", ".$data['time_range_id'].", ".$data['district_id'].", '".$data['name']."', '".$data['phone']."', '".$data['email']."', '".$data['more']."', ".$time_now.")";
        $DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
        
    }

    /**
     * Get room select box
     * @param int $room_type_id room type id
     * @return string
     */
    function getRoomSelectBox ( $room_type_id ) {
        $rs .= '<select name="room_type_id" class="getrent_selectbox">';
        foreach ( $this->room_item as $item_id => $item_title ) {
            if ( $room_type_id == $item_id ) {
                $selected = "selected";
            } else {
                $selected = '';
            }
            $rs .= '<option value="'.$item_id.'" '.$selected.'>'.$item_title.'</option>';
        }
        $rs .= '</select>';
        return $rs;
    }
    
    /**
     * Get room type title by ID
     * @param int $room_type_id room type id
     * @return string
     */
    function getRoomTitleByID ( $room_type_id ) {
        return $this->room_item[$room_type_id];
    }
    
    /**
     * Get time range title by ID
     * @param int $time_range_id time range id
     * @return string
     */
    function getTimeRangeTitleByID ( $time_range_id ) {
        return $this->time_range_item[$time_range_id];
    }
    
    /**
     * Get district title by ID
     * @param int $district_id district id
     * @return string
     */
    function getDistrictTitleByID ( $district_id ) {
        global $__db_prefix;
        
        $query = "select * from ".DB_PREFIX."_district where id=$district_id";
        $DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
		if($stmt){
			$ar=$DBC->fetch($stmt);
			if ( $ar['name'] == '' ) {
				return 'Любой';
			}else{
				return $ar['name'];
			}
		}
		return 'Любой';
    }

    /**
     * Get time range select box
     * @param int $time_range_id time range id
     * @return string
     */
    function getTimeRangeSelectBox ( $time_range_id ) {

        $rs .= '<select name="time_range_id" class="getrent_selectbox">';
        foreach ( $this->time_range_item as $item_id => $item_title ) {
            if ( $time_range_id == $item_id ) {
                $selected = "selected";
            } else {
                $selected = '';
            }
            $rs .= '<option value="'.$item_id.'" '.$selected.'>'.$item_title.'</option>';
        }
        $rs .= '</select>';
        return $rs;
    }
    
    /**
     * Get district list
     * @param int $district_id district ID
     * @param string $class class
     * @return string
     */
    function getDistrictList( $district_id = '', $class="" ) {
        $query = "select * from re_district order by id";
        $DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
        $rs = '<select name="district_id" class="'.$class.'" onChange="upd_streetlist()">';
        $rs .= '<option value="0">Любой</option>';
        if($stmt){
        	while($ar=$DBC->fetch($stmt)){
        		if ( $district_id == $ar['id'] ) {
	                $selected = 'selected';
	            } else {
	                $selected = '';
	            }
	            $rs .= '<option value="'.$ar['id'].'" '.$selected.'>'.$ar['name'].'</option>';
        	}
        }
        $rs .= '</select>';
        return $rs;
    }
    
    
    
    /**
     * Get rent form
     * @param array $data data
     * @return string
     */
    function getRentForm ( $data ) {
        $rs = '<form action="'.SITEBILL_MAIN_URL.'/getrent/" method="post">';
        $rs .= '<div id="getrent_form">';
        $rs .= '<table border="0">';
        $rs .= '<tr>';
        $rs .= '<td colspan="2"><h1>Снять квартиру</h1></td>';
        $rs .= '</tr>';

        if ( $this->getError() ) {
            $rs .= '<tr>';
            $rs .= '<td colspan="2"><span class="error">'.$this->getError().'</span></td>';
            $rs .= '</tr>';
        }
        
        $rs .= '<tr>';
        $rs .= '<td>Кол.во комнат</td><td>'.$this->getRoomSelectBox( $data['room_type_id'] ).'</td>';
        $rs .= '</tr>';
        $rs .= '<tr>';
        $rs .= '<td>Снять на</td><td>'.$this->getTimeRangeSelectBox( $data['time_range_id'] ).'</td>';
        $rs .= '</tr>';
        $rs .= '<tr>';
        $rs .= '<td>Район</td><td>'.$this->getDistrictList( $data['district_id'] , 'getrent_selectbox').'</td>';
        $rs .= '</tr>';
        $rs .= '<tr>';
        $rs .= '<td>Ваше имя <span class="error">*</span></td><td><input type="text" name="name" value="'. $data['name'] .'" class="getrent_form_input"></td>';
        $rs .= '</tr>';
        $rs .= '<tr>';
        $rs .= '<td>Телефон <span class="error">*</span></td><td><input type="text" name="phone" value="'. $data['phone'] .'" class="getrent_form_input"></td>';
        $rs .= '</tr>';
        $rs .= '<tr>';
        $rs .= '<td>E-mail</td><td><input type="text" name="email" value="'. $data['email'] .'" class="getrent_form_input"></td>';
        $rs .= '</tr>';
        $rs .= '<tr>';
        $rs .= '<td style="vertical-align: top;">Дополнительные пожелания</td><td><textarea name="more" rows="7" cols="20" class="getrent_form_textarea">'.$data['more'].'</textarea></td>';
        $rs .= '</tr>';
        $captcha_session_key = md5(time().rand(9999, 4).'random key captcha string core sitebill');
        
        $rs .= '<tr>';
        $rs .= '<td style="vertical-align: top;"></td>';
        $rs .= '<td><img src="'.SITEBILL_MAIN_URL.'/captcha.php?captcha_session_key='.$captcha_session_key.'" width="180" height="80">';
        $rs .= '</tr>';
        
        $rs .= '<tr>';
        $rs .= '<td style="vertical-align: top;">'.Multilanguage::_('CAPTCHA_TITLE', 'system').' <span style="color: red;">*</span> </td>';
        $rs .= '<td><input type="text" name="captcha_code" value="" class="getrent_form_input" /></td>';
        $rs .= '</tr>';
        
        $rs .= '<input type="hidden" name="captcha_session_key" value="'.$captcha_session_key.'">';
        $rs .= '</tr>';
        
        
        
        if($this->getConfigValue('post_form_agreement_enable')==1){
        	
        	$rs .= '<script type="text/javascript">';
        	$rs.='$(document).ready(function(){';
        	$rs.='	if($("#i_am_agree_in_form").attr("checked")){';
        		
        	$rs.='	}else{';
        	$rs.='		$("#getrent_submit").attr("disabled","disabled");';
        	$rs.='	}';
        	
        	$rs.='	$("#i_am_agree_in_form").change(function(){';
        	$rs.='			if($(this).attr("checked")){';
        	$rs.='				$("#getrent_submit").removeAttr("disabled");';
        	
        	$rs.='			}else{';
        	$rs.='				$("#getrent_submit").attr("disabled","disabled");';
        	
        	$rs.='			}';
        	
        	
        	//$rs.=' $("#getrent_submit").toggle();';
        	
        	$rs.='});';
        	
        	$rs.='});';
        	$rs .= '</script>';
        	
        	$rs .= '</script>';
        	$rs .= '<tr>';
	        $rs .= '<td><input type="checkbox" id="i_am_agree_in_form" /></td><td>'.$this->getConfigValue('post_form_agreement_text').'</td>';
	        $rs .= '</tr>';
        }
        
        $rs .= '<tr>';
        $rs .= '<input type="hidden" name="do" value="add_done">';
        $rs .= '<td></td><td><input type="submit" value="Отправить" id="getrent_submit"></td>';
        $rs .= '</tr>';
        $rs .= '</table>';
        $rs .= '</div>';
        $rs .= '</form>';

        return $rs;
    }
    
}