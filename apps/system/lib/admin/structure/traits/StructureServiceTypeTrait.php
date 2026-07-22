<?php

trait StructureServiceTypeTrait
{
    function getServiceTypesTree_selectBox($select_name, $current_servicetype_id)
    {
        $rs = '';
        $array = $this->getServiceTypeTree_array(0, 0);

        //echo '<pre>';
        //print_r($array);
        $rs .= '<select name="' . $select_name . '" id="' . $select_name . '" style="width:200px;">';
        $rs .= '<option value="0">' . Multilanguage::_('L_CHOOSE') . '</option>';

        $rs .= $this->getServiceTypesTree_optionItems($array, $current_servicetype_id);

        $rs .= '</select>';
        return $rs;
    }

    function getServiceTypesTree_optionItems($array, $current_servicetype_id)
    {
        $rs = '';
        if (count($array) > 0) {
            foreach ($array as $item) {
                if ($item['id'] == $current_servicetype_id) {
                    $selected = ' selected ';
                } else {
                    $selected = '';
                }
                $rs .= '<option value="' . $item['id'] . '" ' . $selected . '>' . str_repeat('&nbsp;.&nbsp;', $item['level']) . $item['name'] . '</option>';
                if (count($item['child']) > 0) {
                    $rs .= $this->getServiceTypesTree_optionItems($item['child'], $current_servicetype_id);
                }
            }
        }
        return $rs;
    }

    function getServiceTypeTree_array($level, $parent_id)
    {
        $DBC = DBC::getInstance();
        $rs = '';
        $ra = array();
        $query = 'SELECT * FROM ' . DB_PREFIX . '_structure_servicetype WHERE parent_id = ? ORDER BY name';
        $stmt = $DBC->query($query, array($parent_id));
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                //echo 'kk';
                $ra[] = array(
                    'id' => $ar['id'],
                    'parent_id' => $ar['parent_id'],
                    'name' => $ar['name'],
                    'level' => $level,
                    'child' => $this->getServiceTypeTree_array($level + 1, $ar['id']),
                );
            }
        }
        return $ra;
    }
}
