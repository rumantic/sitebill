<?php

require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');

class Config_Form_Generator extends Form_Generator {

    function get_safe_text_input($item_array) {
	/* $string .= "<tr class=\"row3\">\n";
	  if ( $item_array['required'] == "on" ) {
	  $string .= "<td>".$item_array['title']." <span style=\"color: red;\">*</span>".((isset($item_array['hint']) && $item_array['hint']!='') ? ' <span class="hint">('.$item_array['hint'].')</span>' : '')."</td>\n";
	  } else {
	  $string .= "<td>".$item_array['title'].((isset($item_array['hint']) && $item_array['hint']!='') ? ' <span class="hint">('.$item_array['hint'].')</span>' : '')."</td>\n";
	  }
	  $string .= "<td><input type=\"text\" name=\"conf_param_value[".$item_array['name']."]\" value=\"".htmlspecialchars($item_array['value'])."\" size=\"".$item_array['length']."\" maxlength=\"".$item_array['maxlength']."\" /></td>\n";
	  $string .= "<input type=\"hidden\" class=\"sort_order\" value=\"".$item_array['name']."\"  />\n";
	  $string .= "</tr>\n";
	  return $string; */

	$string = '';
	$string .= '<div class="control-group">';
	$string .= '<label class="control-label">' . $item_array['title'] . ($item_array['required'] == "on" ? '<span style=\"color: red;\">*</span>' : '') . ($item_array['hint'] != '' ? ' <span class="help-block">(' . $item_array['hint'] . ')</span>' : '') . '</label>';
	//$string .= '<span class="help-block">'.$item_array['hint'].'</span>';
	$string .= '<div class="controls">';
	$string .= '<input type="text" name="conf_param_value[' . $item_array['name'] . ']" id="' . $item_array['name'] . '" placeholder="' . htmlspecialchars(strip_tags($item_array['title']), ENT_QUOTES, SITE_ENCODING) . '" value="' . htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING) . '" />';
	$string .= '</div>';
	$string .= '<input type="hidden" class="sort_order" value="' . $item_array['name'] . '"  />';
	$string .= '</div>';

	return $string;
    }

    function get_textarea_row($item_array) {

	$string = '';
	if ($item_array['rows'] == '') {
	    $item_array['rows'] = 10;
	}

	if ($item_array['cols'] == '') {
	    $item_array['cols'] = 50;
	}

	$string .= '<div class="control-group">';
	$string .= '<label class="control-label">' . $item_array['title'] . ($item_array['required'] == "on" ? '<span style=\"color: red;\">*</span>' : '') . ($item_array['hint'] != '' ? ' <span class="help-block">(' . $item_array['hint'] . ')</span>' : '') . '</label>';
	$string .= '<div class="controls">';
	$string .= '<textarea name="conf_param_value[' . $item_array['name'] . ']" id="' . $item_array['name'] . '" rows="' . $item_array['rows'] . '" cols="' . $item_array['cols'] . '" >' . $item_array['value'] . '</textarea>';
	$string .= '</div>';
	$string .= '<input type="hidden" class="sort_order" value="' . $item_array['name'] . '"  />';
	$string .= '</div>';
	return $string;
    }

    function get_select_box($item_array) {
	/* $rs = '<select name="conf_param_value['.$item_array['name'].']">';
	  if ( !empty($item_array['select_data']) ) {
	  foreach ( $item_array['select_data'] as $item_id => $item_value ) {
	  if ( $item_id ==  $item_array['value'] ) {
	  $selected = "selected";
	  } else {
	  $selected = "";
	  }
	  $rs .= '<option value="'.$item_id.'" '.$selected.'>'.$item_value.'</option>';
	  }
	  }
	  $rs .= '</select>';
	  return $rs; */

	$string = '';
	$string .= '<div class="control-group">';
	$string .= '<label class="control-label">' . $item_array['title'] . ($item_array['required'] == "on" ? '<span style=\"color: red;\">*</span>' : '') . ($item_array['hint'] != '' ? ' <span class="help-block">(' . $item_array['hint'] . ')</span>' : '') . '</label>';
	//$string .= '<span class="help-block">'.$item_array['hint'].'</span>';
	$string .= '<div class="controls">';
	$string .= '<select name="conf_param_value[' . $item_array['name'] . ']">';
	if (!empty($item_array['select_data'])) {
	    foreach ($item_array['select_data'] as $item_id => $item_value) {
		if ($item_id == $item_array['value']) {
		    $selected = "selected";
		} else {
		    $selected = "";
		}
		$string .= '<option value="' . $item_id . '" ' . $selected . '>' . $item_value . '</option>';
	    }
	}
	$string .= '</select>';
	//$string .= '<input type="text" name="conf_param_value['.$item_array['name'].']" id="'.$item_array['name'].'" placeholder="'.$item_array['title'].'" value="'.htmlspecialchars($item_array['value']).'" />';
	$string .= '</div>';
	$string .= '<input type="hidden" class="sort_order" value="' . $item_array['name'] . '"  />';
	$string .= '</div>';
	if ( $item_array['title'] == 'Тип WYSIWYG-редактора' ) {
	    $string .= '<div class="control-group"><h3>Расширенные параметры</h3></div>';
	}
	

	return $string;
    }

    function get_checkbox($item_array) {
	/* $rs = '<input type="checkbox" name="conf_param_value['.$item_array['name'].']" value="0" checked="checked" style="display:none;" />';
	  $rs .= '<input type="checkbox" name="conf_param_value['.$item_array['name'].']" value="1"';
	  if ( $item_array['value'] == 1 ) {
	  $rs .= ' checked="checked" ';
	  }
	  $rs .= '/>';
	  return $rs; */

	$string = '';
	$string .= '<div class="control-group">';
	$string .= '<label class="control-label">' . $item_array['title'] . ($item_array['required'] == "on" ? '<span style=\"color: red;\">*</span>' : '') . ($item_array['hint'] != '' ? ' <span class="help-block">(' . $item_array['hint'] . ')</span>' : '') . '</label>';
	//$string .= '<span class="help-block">'.$item_array['hint'].'</span>';
	$string .= '<div class="controls">';
	$string .= '<input type="checkbox" name="conf_param_value[' . $item_array['name'] . ']" value="0" checked="checked" style="display:none;" />';
	$string .= '<input type="checkbox" name="conf_param_value[' . $item_array['name'] . ']" value="1" ' . ($item_array['value'] == 1 ? 'checked="checked"' : '') . ' />';
	$string .= '</div>';
	$string .= '<input type="hidden" class="sort_order" value="' . $item_array['name'] . '"  />';
	$string .= '</div>';

	return $string;
    }

    function get_checkbox_box_row($item_array) {
	return $this->get_checkbox($item_array);
	/* $rs = '<tr  class="row3">';
	  $rs .= '<td>';
	  $rs .= $item_array['title'];
	  if ( $item_array['required'] == "on" ) {
	  $rs .= " <span style=\"color: red;\">*</span> \n";
	  }
	  $rs .= ((isset($item_array['hint']) && $item_array['hint']!='') ? ' <span class="hint">('.$item_array['hint'].')</span>' : '').'</td>';
	  $rs .= '<td>';
	  $rs .= $this->get_checkbox($item_array);
	  if ( $item_array['ajax_popup'] != '' ) {
	  $rs .= $item_array['ajax_popup'];
	  }
	  $rs .= '</td>';
	  $rs .= "<input type=\"hidden\" class=\"sort_order\" value=\"".$item_array['name']."\"  />\n";

	  $rs .= '</tr>';

	  return $rs; */
    }

    function get_select_box_row($item_array) {
	return $this->get_select_box($item_array);
	/* $rs = '<tr class="row3">';
	  $rs .= '<td>';
	  $rs .= $item_array['title'];
	  if ( $item_array['required'] == "on" ) {
	  $rs .= " <span style=\"color: red;\">*</span> \n";
	  }
	  $rs .= ((isset($item_array['hint']) && $item_array['hint']!='') ? ' <span class="hint">('.$item_array['hint'].')</span>' : '').'</td>';
	  $rs .= '<td>';
	  $rs .= $this->get_select_box($item_array);
	  $rs .= '</td>';
	  $rs .= "<input type=\"hidden\" class=\"sort_order\" value=\"".$item_array['name']."\"  />\n";

	  $rs .= '</tr>';

	  return $rs; */
    }

}
