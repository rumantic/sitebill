<?php
/**
 * Zapros frontend 
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class Zapros_Frontend extends Zapros_Manager {
    /** 
     * Main
     * @param void
     * @return string
     */
    function main_frontend () {
	    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    
	    $this->data_model['zapros']['date_created']['type'] = 'hidden';
	    $this->data_model['zapros']['date_solved']['type'] = 'hidden';
	    unset($this->data_model['zapros']['publish']);
	    
	    $form_data = $this->data_model;
		switch( $this->getRequestValue('do') ){
			case 'new_done' : {
        		
	            $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
	            //echo '<pre>';
	            //print_r($form_data[$this->table_name]);
	            //echo '</pre>';
			    
			    if ( !$this->check_data( $form_data[$this->table_name] ) ) {
			        $rs = $this->get_manual_form($form_data[$this->table_name], 'new');
			        
			    } else {
			        $zapros_id = $this->add_data($form_data[$this->table_name], $this->getRequestValue('language_id'));
	                $files = $this->editFileMulti($this->action, $this->table_name, $this->primary_key, $zapros_id);
			        
			        $rs .= $this->thanks($zapros_id, $form_data[$this->table_name]['email']['value']);
			    }
				break;
			}
			
			default : {
			    $form_data[$this->table_name]['date_created']['value'] = time();
			    $rs = $this->get_manual_form($form_data[$this->table_name]);
				break;
			}
		}
		return $rs;
    }
    
    function get_manual_form ( $form_data=array(), $do = 'new', $language_id = 0 ) {
	    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_generator.php');
		$form_generator = new Form_Generator();
        
		$rs = '<script src="/template/frontend/orders/js/form.js"></script>';
		
        $rs .= '
        <div id="zform">
		<form method="post" action="index.php">
		<table border="0">';
        
		if ( $this->getError() ) {
		    $rs .= $form_generator->get_error_message_row($this->GetErrorMessage());
		}
        
		$rs .= '<input type="hidden" name="date_created" value="'.time().'" />
		<input type="hidden" name="date_solved" value="'.time().'" />
		<tr>
			<td colspan="2">Я, <input type="text" name="fio" value="'.$form_data['fio']['value'].'" size="40" maxlength="" class="">, проживающий в Новгородской области по адресу </td>
		</tr>
		<tr>
			<td colspan="2" class="">Адрес: <input onkeydown="check_address(this.value)" onkeyup="check_address(this.value)" onblur="check_address(this.value)" onfocus="check_address(this.value)"  type="text" name="address" style="width: 450px;" value="'.$form_data['address']['value'].'" size="40" maxlength="" class=""><span id="acheck"></span></td>
		</tr>
		<tr>
			<td colspan="2" class="">мой телефон <input type="text" style="width: 350px;" name="phone" value="'.$form_data['phone']['value'].'" size="20" maxlength="" class="">, хочу обратиться к Вам</td>
		</tr>	
		<tr>
			<td colspan="2" class="">с запросом по поводу <input type="text" style="width: 450px;" name="subject" value="'.$form_data['subject']['value'].'" size="40" maxlength="" class=""></td>
		</tr>	
		<tr>
        	<td colspan="2"><textarea name="body" style="width: 630px;" rows="10" cols="50">'.$form_data['body']['value'].'</textarea></td>
        </tr>';
        
        $rs .= $form_generator->get_uploadify_file_row($form_data['image']);
        
		$rs .= '<tr>
			<td>Прошу держать меня в курсе через</td>
			<td><input type="checkbox" name="notify_post" value="0"/> Почту России по вышеуказанному адресу проживания</td>
		</tr>
		<tr>
			<td class=""></td>
			<td class=""><input type="checkbox" name="notify_email" value="0"/> <input type="text" style="width: 200px;" name="email" value="" size="40" maxlength="" class=""> электронную почту </td>
		</tr>
		<tr>
			<td colspan="2"> Против открытой публикации этого запроса в интернете не возражаю</td>
		</tr>
';
		
        $rs .= '';
		
        
        $rs .= '
		<tr>
			<td>Разрешаю публиковать</td>
			<td><input type="checkbox" name="can_publish" value="0"/></td>
		</tr>
			<input type="hidden" name="do" value="new_done">
			<input type="hidden" name="zapros_id" value="">
			<input type="hidden" name="action" value="zapros">
			<input type="hidden" name="language_id" value="0">
		<tr>
			<td></td>
			<td><input type="submit" name="submit" value="Сохранить"></td>
		</tr>
		</table>
		</form>
		</div>        
        ';
        
        return $rs;
    }
    
	/**
	 * Get form for edit or new record
	 * @param array $form_data
	 * @param string $do
	 * @param int $language_id
	 * @return string
	 */
	function get_form ( $form_data=array(), $do = 'new', $language_id = 0 ) {
		
	    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_generator.php');
		$form_generator = new Form_Generator();
		
        $rs .= $this->get_ajax_functions();
		
		$rs .= '<form method="post" action="index.php">';
        $rs .= '<table>';
		if ( $this->getError() ) {
		    $rs .= $form_generator->get_error_message_row($this->GetErrorMessage());
		}
		$rs .= $form_generator->compile_form($form_data);
		
		if ( $do == 'new' ) {
		    $rs .= '<input type="hidden" name="do" value="new_done">';
		    $rs .= '<input type="hidden" name="'.$this->primary_key.'" value="'.$this->getRequestValue($this->primary_key).'">';
		} else {
		    $rs .= '<input type="hidden" name="do" value="edit_done">';
		    $rs .= '<input type="hidden" name="'.$this->primary_key.'" value="'.$form_data[$this->primary_key]['value'].'">';
		}
		$rs .= '<input type="hidden" name="action" value="'.$this->action.'">';
		$rs .= '<input type="hidden" name="language_id" value="'.$language_id.'">';
		
		$rs .= '<tr>';
		$rs .= '<td></td>';
		$rs .= '<td><input type="submit" name="submit" value="Сохранить"></td>';
		$rs .= '</tr>';
		$rs .= '</table>';
		$rs .= '</form>';
		
		return $rs;
		
	}
    
    function get_view () {
        $zapros_id = $this->getIDfromURI($_SERVER['REQUEST_URI']);
        $query = "select * from ".DB_PREFIX."_zapros where zapros_id=$zapros_id and publish=1";
        //echo $query;
        $this->db->exec($query);
        $this->db->fetch_assoc();
        $this->db->row['date'] = date('d.m.Y', $this->db->row['date_created']); 
        $this->db->row['date_solved'] = date('d.m.Y', $this->db->row['date_solved']); 
        //print_r($this->db->row);
        return $this->db->row;
        //echo $zapros_id;
    }
    
    /**
     * Get ID from URI
     * @param string $uri uri
     * @return int
     */
    function getIDfromURI ( $uri ) {
    	preg_match('/otvet(\d+).html/s', $uri, $matches);
		//company\/realty(\d+).html/s
    	if ( $matches[1] > 0 ) {
    		return $matches[1];
    	}
    	return false;
    }
    
    
    /**
     * Get list
     */
    function get_list () {
        $query = "select * from ".DB_PREFIX."_zapros where publish=1 and is_solved=1 order by date_solved desc";
        $this->db->exec($query);
        $ra = array();
        while ( $this->db->fetch_assoc() ) {
            $this->db->row['date'] = date('d.m.Y', $this->db->row['date_created']);
            $this->db->row['date_solved'] = date('d.m.Y', $this->db->row['date_solved']);
            $ra[] = $this->db->row;
        }
        //echo '<pre>';
        //print_r($ra);
        //echo '</pre>';
        return $ra;
    }
    
    /**
     * Return thanks message
     * @param int $zapros_id
     * @param string $email
     * @return string
     */
    function thanks ( $zapros_id, $email ) {
        $rs = '<h1>Ваш запрос отправлен, спасибо</h1>
        Вашему запросу присвоен код ЗАПР-'.$zapros_id.'<br>
        Уведомление о решении будет выслано Вам Почтой России, электронной почтой на адрес '.$email.', а также появиться на этой странице<br>
        Ссылка на эту страницу появиться на сайте приемной только после успешного решения, поэтому, пожалуйста, добавьте ее в закладки своего браузера.<br>
        <a href="/">Вернуться на главную</a> 
        ';
        
        
        return $rs;
    }
}
?>
