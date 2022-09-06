<?php
namespace yandexrealty\lib;

class CommonExporter {
    /**
     * @var $fields_associations
     */
    private $fields_associations;

    /**
     * @var \condition_helper
     */
    private $condition_helper;

    /**
     * @var $method_rules_array
     */
    private $method_rules_array;

    public function __construct ( $fields_associations, $method_rules_array) {
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/components/condition_helper/condition_helper.php';
        $this->condition_helper = new \condition_helper();

        $this->fields_associations = $fields_associations;
        $this->method_rules_array = $method_rules_array;
    }

    private function isValidExtendedParam ( $name ) {
        $extended_request_items_array = explode(',', $_REQUEST['extended']);
        if ( is_array($extended_request_items_array) and  in_array($name, $extended_request_items_array) ) {
            return true;
        }
        return false;
    }

    public function compileRules ($data_item) {
        $xml_collectorp = array();
        foreach ($this->method_rules_array as $method) {
            if (is_array($method)) {
                if ( isset($method['extended']) and $method['extended'] ) {
                    if ( !$this->isValidExtendedParam($method['name']) ) {
                        continue;
                    }
                }
                
                switch ( $method['type'] ) {
                    case \system\types\model\Dictionary::SELECT_BOX:
                        $xml_collectorp[] = $this->exSelectBox($data_item, $method);
                        break;
                    case \system\types\model\Dictionary::SAFE_STRING:
                        $xml_collectorp[] = $this->exSafeString($data_item, $method);
                        break;
                }
            }
        }
        return $xml_collectorp;
    }

    private function exSafeString($data_item, $method_array)
    {
        if ( isset($data_item[$this->fields_associations[$method_array['name']]]['value']) ) {
            $data_value = $data_item[$this->fields_associations[$method_array['name']]]['value'];
        } elseif  ( isset($this->fields_associations[$method_array['name'] . '_default']) ) {
            $data_value = $this->fields_associations[$method_array['name'] . '_default'];
        }

        if (isset($data_value) and $data_value != '' and $data_value != 0) {
            return '<' . $method_array['name'] . '>' . self::symbolsClear($data_value) . '</' . $method_array['name'] . '>';
        }
    }

    static function symbolsClear($text)
    {
        $text = preg_replace('/[[:cntrl:]]/i', '', $text);
        $text = str_replace(array('"', '&', '>', '<', '\''), array('&quot;', '&amp;', '&gt;', '&lt;', '&apos;'), $text);
        $text = \Sitebill::iconv(SITE_ENCODING, 'utf-8', $text);
        return $text;
    }



    private function exSelectBox($data_item, $method_array)
    {
        foreach ($method_array['variants'] as $type => $typename) {
            if (is_array($this->fields_associations[$type]) &&
                !empty($this->fields_associations[$type]) &&
                $this->condition_helper->checkCondition($this->fields_associations[$type], $data_item)) {
                return '<' . $method_array['name'] . '>' . $this->option_wrapper($typename, $method_array) . '</' . $method_array['name'] . '>';
            }
        }

        $default = trim($this->fields_associations[$method_array['name'] . '_default']);

        if (isset($method_array['variants'][$default])) {
            return '<' . $method_array['name'] . '>' . $this->option_wrapper($method_array['variants'][$default], $method_array) . '</' . $method_array['name'] . '>';
        }

        return '';
    }

    private function option_wrapper($value, $method_array)
    {
        if ($method_array['multi']) {
            return '<Option>' . $value . '</Option>';
        }
        return $value;
    }
}
