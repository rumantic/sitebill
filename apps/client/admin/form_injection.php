<?php
namespace client\admin;

class Form_Injection {

    /**
     * @var array
     */
    private $client_info_additional;
    private $client_name;

    function get_client_info_additional () {
        return $this->client_info_additional;
    }

    function get_client_name () {
        return $this->client_name;
    }

    function get_content ($item, \Form_Generator $context = null, $model = null) {
        if ( $context != null ) {
            $ra = $context->compile_select_box_by_query_element($item, $model);
            $client_info = $this->get_client_info($item);
            return $ra['html'].$client_info;
        } else {
            $client_info = $this->get_client_info($item, true);
            return $client_info;
        }
    }

    function get_client_info ($item, $full_contact_list = false) {
        $DBC = \DBC::getInstance();

        $query = 'select cli.id as current_client_id, cli.*, con.* 
                  from '.DB_PREFIX.'_client cli, '.DB_PREFIX.'_contact con 
                  where cli.id=con.client_id and con.id=?';
        //echo $query.'<br>';
        $stmt = $DBC->query($query, array($item['value']));
        if ( $stmt ) {
            $ar = $DBC->fetch($stmt);
            $client_id = $ar['current_client_id'];
            $client_info .= '<a href="?action=client&do=edit&id='.$client_id.'" target="_blank">'.$ar['name'].' ('.$ar['type'].')</a>';
            $this->client_name = $ar['name'];
        }

        // Получаем дополнительные контакты
        $query = "select con.* from ".DB_PREFIX."_contact con where con.client_id=?";
        $stmt = $DBC->query($query, array($client_id));
        if ( $stmt ) {
            while ( $ar = $DBC->fetch($stmt) ) {
                if ($ar['id'] != $item['value'] or $full_contact_list) {
                    $this->client_info_additional[] = $ar['contact'];
                }
            }
        }
        if ( is_array($this->client_info_additional) ) {
            $client_info .= ' '.implode(', ', $this->client_info_additional);
        }


        return $client_info;
    }
}
