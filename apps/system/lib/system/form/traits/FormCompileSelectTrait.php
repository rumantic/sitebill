<?php
trait FormCompileSelectTrait
{
    function compile_selectbox_element($item_array)
    {
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $this->get_select_box($item_array),
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_radiogroup_element($item_array)
    {
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $this->get_radiogroup($item_array),
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    /* function compile_separator_element($item_array){
      return array(
      'title'=>$item_array['title'],
      'html'=>'',
      'type'=>'separator',
      'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
      );
      } */

    function compile_separator_element($item_array)
    {
        return array(
            'title' => $item_array['title'],
            'required' => 0,
            'html' => '<h2>' . $item_array['title'] . '</h2>',
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_checkbox_element($item_array)
    {
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $this->get_checkbox($item_array),
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'id' => $this->form_id . '_' . $item_array['name'],
            'type' => $item_array['type']
        );
    }

    function compile_select_entity_element($item_array, $model = null)
    {
        $rs = '';
        if (isset($item_array['parameters'])) {
            $parameters = $item_array['parameters'];
        } else {
            $parameters = array();
        }

        $model = $parameters['model'];
        $value_name = $parameters['value_name'];
        if ($value_name == '') {
            $value_name = 'name';
        }

        $form_data = array();

        require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
        $ATH = new Admin_Table_Helper();
        $form_data = $ATH->load_model($model, false);
        foreach ($form_data[$model] as $it) {
            if ($it['type'] == 'primary_key') {
                $primary_key_name = $it['name'];
                break;
            }
        }
        //$primary_key_name='flatplanning_id';
        //$primary_key_name='flatplanning_id';

        $DBC = DBC::getInstance();
        $query = 'SELECT * FROM ' . DB_PREFIX . '_' . $model;
        $stmt = $DBC->query($query);
        $rs .= '<select class="' . $this->classes['select'] . '" name="' . $item_array['name'] . '" id="' . $item_array['name'] . '">';
        $rs .= '<option value="0">--</option>';
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $value = $ar[$value_name];
                $value = htmlspecialchars($value, ENT_QUOTES, SITE_ENCODING);
                $rs .= '<option value="' . $ar[$primary_key_name] . '" ' . $selected . '>' . $value . '</option>';
            }
        }
        $rs .= '</select>';

        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $rs,
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_select_box_by_query_element($item_array, $model = null)
    {

        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $this->get_single_select_box_by_query($item_array, $model),
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_select_box_structure_element($item_array)
    {
        if (isset($item_array['parameters']) && isset($item_array['parameters']['type'])) {
            $type = $item_array['parameters']['type'];
        } else {
            $type = '';
        }
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        if ($item_array['title_default'] != '') {
            $zero_title = $item_array['title_default'];
        } else {
            $zero_title = '';
        }

        if (isset($item_array['parameters']['nonzerotitle'])) {
            $nonzerotitle = $item_array['parameters']['nonzerotitle'];
        } else {
            $nonzerotitle = '';
        }

        $html = '';
        if ($type == 'leveled') {
            $html = $Structure_Manager->getCategorySelectBoxLeveled(
                $item_array['name'],
                $item_array['value'],
                array('zerotitle' => $zero_title, 'nonzerotitle' => $nonzerotitle),
                $item_array
            );
        } else {
            $html = $Structure_Manager->getCategorySelectBoxWithName($item_array['name'], $item_array['value'], false, array(), $zero_title);
        }
        //echo $zero_title;
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $html,
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_structure_element($item_array)
    {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_implements.php');
        $SM = Structure_Implements::getManager($item_array['entity']);

        //$equire_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
        //$Structure_Manager = new Structure_Manager();


        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $SM->getCategorySelectBoxWithName($item_array['name'], $item_array['value'], false, $item_array['parameters'], $zero_title),
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_select_by_query_multi_element($item_array, $model = null)
    {
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $this->get_select_by_query_multi($item_array, $model),
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_injector_element($item_array, $model)
    {
        $form_injector = new \system\lib\system\form\Form_Injector();

        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $form_injector->compile($item_array, $this, $model),
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_select_box_by_query_multiple_element($item_array)
    {
        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $this->get_single_select_box_by_query_multiple($item_array),
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_select_box_structure_simple_multiple_element($item_array)
    {
        if (!isset($item_array['values_array'])) {
            $item_array['values_array'] = array(0 => 0);
        }
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();

        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $Structure_Manager->getCategorySelectBoxWithName($item_array['name'], $item_array['values_array']),
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );

        $rs = '<tr  class="row3"  alt="' . $item_array['name'] . '">';
        $rs .= '<td>';
        $rs .= $item_array['title'];
        if ($item_array['required'] == "on") {
            $rs .= " <span style=\"color: red;\">*</span> \n";
        }
        $rs .= '</td>';
        $rs .= '<td>';
        $rs .= $Structure_Manager->getCategorySelectBoxWithName($item_array['name'], $item_array['values_array']);
        $rs .= '</td>';
        $rs .= '</tr>';

        return $rs;
    }

    function compile_select_box_structure_multiple_checkbox($item_array)
    {
        if (!isset($item_array['values_array'])) {
            $item_array['values_array'] = array(0 => 0);
        }
        if (!is_array($item_array['values_array'])) {
            $item_array['values_array'] = (array)$item_array['values_array'];
        }
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();

        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $Structure_Manager->getCategoryCheckboxes($item_array['name'], $item_array['values_array']),
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

    function compile_grade_element($item_array)
    {
        $html = '';

        $vals = array();

        if (isset($item_array['grade_values'])) {
            $vals = $item_array['grade_values'];
        } elseif (isset($item_array['select_data'])) {
            $vals = $item_array['select_data'];
        }

        if (!empty($vals)) {
            foreach ($vals as $item_id => $item_id_name) {
                if ($item_array['value'] == $item_id) {
                    $checked = 'checked="checked"';
                } else {
                    $checked = '';
                }
                $html .= '<span>' . $item_id_name . '</span><input type="radio" name="' . $item_array['name'] . '" value="' . $item_id . '" ' . $checked . '>&nbsp;&nbsp;&nbsp;';
            }
        } else {
            $html .= '<input class="' . $this->classes['input'] . '" type="text" name="' . $item_array['name'] . '" value="' . $item_array['value'] . '" />';
        }

        return array(
            'title' => $item_array['title'],
            'required' => ($item_array['required'] == "on" ? 1 : 0),
            'html' => $html,
            'tab' => (isset($item_array['tab']) ? $item_array['tab'] : ''),
            'type' => $item_array['type']
        );
    }

}
