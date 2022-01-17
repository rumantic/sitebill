<?php
/**
 * Обработка условий принятых для маппинга в выгрузках. Основа - ЦИАН2.
 */

class condition_helper {
    
    /**
     * Check group of conditions
     * @param array $cond
     * @param array $data_item
     * @return boolean
     */
    public function checkCondition($cond, $data_item){
        if(!is_array($cond) || empty($cond)){
            return false;
        }
        $result_common = false;
        foreach($cond as $oc){
            $result_or = true;
            if(is_array($oc[0])){
                $result_and = true;
                foreach($oc as $anc){
                    $res = $this->checkOneCondition($anc, $data_item);
                    $result_or = $result_or && $res;
                }
            }else{
                $result_or = $this->checkOneCondition($oc, $data_item);

            }
            $result_common = $result_common || $result_or;
        }
        return $result_common;
    }
    
    /**
     * Check one condition entry
     * @param array $oc
     * @param array $data_item
     * @return boolean
     */
    protected function checkOneCondition($oc, $data_item){
        $f = $oc[0];
        $o = $oc[1];
        $v = $oc[2];
        return $this->isConditionValid($f, $o, $v, $data_item);
    }
    
    /**
     * Is the concrete condition entry valid
     * @param string $f Testing field name
     * @param string $o Type of comparison
     * @param mixed $v Testing value
     * @param array $data_item
     * @return boolean
     */
    protected function isConditionValid($f, $o, $v, $data_item){
        //Возвращаем несовпадение при отсуствии свойства
        if(!isset($data_item[$f])){
            return false;
        }
        switch($o){
            case '=' : {
                $method = 'isConditionEQ';
                $val = $v;
                break;
            }
            case '!=' : {
                $method = 'isConditionNEQ';
                $val = $v;
                break;
            }
            case '>' : {
                $method = 'isConditionGT';
                $val = $v;
                break;
            }
            case '<' : {
                $method = 'isConditionLT';
                $val = $v;
                break;
            }
            case '>=' : {
                $method = 'isConditionGTEQ';
                $val = $v;
                break;
            }
            case '<=' : {
                $method = 'isConditionLTEQ';
                $val = $v;
                break;
            }
            case 'IN' : {
                $method = 'isConditionIN';
                if(false != strpos($v, ',')){
                    $val = explode(',', $v);
                    foreach($val as $k => $x){
                        $val[$k] = trim($x);
                    }
                    $method = 'isConditionINSet';
                }else{
                    list($v1, $v2) = explode('-', $v);
                    $val = array(trim($v1), trim($v2));
                }
                break;
            }
            case '!IN' : {
                $method='isConditionNIN';
                if(false != strpos($v, ',')){
                    $val = explode(',', $v);
                    foreach($val as $k => $x){
                        $val[$k] = trim($x);
                    }
                    $method = 'isConditionNINSet';
                }else{
                    list($v1, $v2) = explode('-', $v);
                    $val = array(trim($v1), trim($v2));
                }
                break;
            }
            case 'EMPTY' : {
                $method = 'isConditionEMPTY';
                $val = '';
                break;
            }
            case 'ZERO' : {
                $method = 'isConditionZERO';
                $val = '';
                break;
            }
            case 'EMPTYZ' : {
                $method = 'isConditionEMPTYZ';
                $val = '';
                break;
            }
            case '!EMPTY' : {
                $method = 'isConditionNEMPTY';
                $val = '';
                break;
            }
            case '!ZERO' : {
                $method = 'isConditionNZERO';
                $val = '';
                break;
            }
            case '!EMPTYZ' : {
                $method = 'isConditionNEMPTYZ';
                $val = '';
                break;
            }
            default : {
                $method = '';
                $val = '';
            }
        }
        if($method != ''){
            return $this->$method($f, $val, $data_item);
        }else{
            return false;
        }
    }
    
    /**
     * Check value is not equal zero (!= 0)
     * @param string $f Testing field name
     * @param mixed $v Testing value
     * @param array $data_item
     * @return boolean
     */
    protected function isConditionNZERO($f, $v, $data_item){
        if(!$this->isConditionZERO($f, $v, $data_item)){
            return true;
        }
        return false;
    }

    /**
     * Check value is not empty string (!= '')
     * @param string $f Testing field name
     * @param mixed $v Testing value
     * @param array $data_item
     * @return boolean
     */
    protected function isConditionNEMPTY($f, $v, $data_item){
        if(!$this->isConditionEMPTY($f, $v, $data_item)){
            return true;
        }
        return false;
    }

    /**
     * Check value is not empty string and not zero (!= '' AND != 0)
     * @param string $f Testing field name
     * @param mixed $v Testing value
     * @param array $data_item
     * @return boolean
     */
    protected function isConditionNEMPTYZ($f, $v, $data_item){
        if(!$this->isConditionEMPTYZ($f, $v, $data_item)){
            return true;
        }
        return false;
    }

    /**
     * Check value is zero (== 0)
     * @param string $f Testing field name
     * @param mixed $v Testing value
     * @param array $data_item
     * @return boolean
     */
    protected function isConditionZERO($f, $v, $data_item){
        if(intval($data_item[$f]['value']) == 0){
            return true;
        }
        return false;
    }

    /**
     * Check value is empty string (== '')
     * Поддерживает скалярные и массивные виды value
     * @param string $f Testing field name
     * @param mixed $v Testing value
     * @param array $data_item
     * @return boolean
     */
    protected function isConditionEMPTY($f, $v, $data_item){
        if(is_array($data_item[$f]['value']) && empty($data_item[$f]['value'])){
            return true;
        }else{
            if(strval($data_item[$f]['value']) == ''){
                return true;
            }
        }
        return false;
    }

    /**
     * Check value is empty string or zero (== '' OR == 0)
     * @param string $f Testing field name
     * @param mixed $v Testing value
     * @param array $data_item
     * @return boolean
     */
    protected function isConditionEMPTYZ($f, $v, $data_item){
        if(strval($data_item[$f]['value']) == '' || intval($data_item[$f]['value']) == 0){
            return true;
        }
        return false;
    }

    /**
     * Check value is equal (== $v)
     * Поддерживает скалярные и массивные виды value
     * @param string $f Testing field name
     * @param mixed $v Testing value
     * @param array $data_item
     * @return boolean
     */
    protected function isConditionEQ($f, $v, $data_item){
        if(is_array($data_item[$f]['value'])){
            if(in_array($v, $data_item[$f]['value'])){
                return true;
            }
        }else{
            if($data_item[$f]['value'] == $v){
                return true;
            }
        }
        return false;
    }

    /**
     * Check value is in set (in_array)
     * Поддерживает скалярные и массивные виды value
     * @param string $f Testing field name
     * @param mixed $v Testing value
     * @param array $data_item
     * @return boolean
     */
    protected function isConditionINSet($f, $v, $data_item){
        if(is_array($data_item[$f]['value']) && empty($data_item[$f]['value'])){
            return false;
        }
        if(is_array($data_item[$f]['value'])){
            foreach ($data_item[$f]['value'] as $item) {
                if(in_array($item, $v)){
                    return true;
                }
            }
        }else{
            if(in_array($data_item[$f]['value'], $v)){
                return true;
            }
        }
        return false;
    }

    /**
     * Check value is not in set (!in_array)
     * @param string $f Testing field name
     * @param mixed $v Testing value
     * @param array $data_item
     * @return boolean
     */
    protected function isConditionNINSet($f, $v, $data_item){
        return !$this->isConditionINSet($f, $v, $data_item);
    }

    /**
     * Check value is not equal (!= $v)
     * @param string $f Testing field name
     * @param mixed $v Testing value
     * @param array $data_item
     * @return boolean
     */
    protected function isConditionNEQ($f, $v, $data_item){
        if(!$this->isConditionEQ($f, $v, $data_item)){
            return true;
        }
        return false;
    }

    /**
     * Check value is great than (> $v)
     * @param string $f Testing field name
     * @param mixed $v Testing value
     * @param array $data_item
     * @return boolean
     */
    protected function isConditionGT($f, $v, $data_item){
        if($data_item[$f]['value'] > $v){
            return true;
        }
        return false;
    }

    /**
     * Check value is less than (< $v)
     * @param string $f Testing field name
     * @param mixed $v Testing value
     * @param array $data_item
     * @return boolean
     */
    protected function isConditionLT($f, $v, $data_item){
        if($data_item[$f]['value'] < $v){
            return true;
        }
        return false;
    }

    /**
     * Check value is great less or equal (<= $v)
     * @param string $f Testing field name
     * @param mixed $v Testing value
     * @param array $data_item
     * @return boolean
     */
    protected function isConditionLTEQ($f, $v, $data_item){
        if($this->isConditionEQ($f, $v, $data_item) || $this->isConditionLT($f, $v, $data_item)){
            return true;
        }
        return false;
    }

    /**
     * Check value is great than or equal (>= $v)
     * @param string $f Testing field name
     * @param mixed $v Testing value
     * @param array $data_item
     * @return boolean
     */
    protected function isConditionGTEQ($f, $v, $data_item){
        if($this->isConditionEQ($f, $v, $data_item) || $this->isConditionGT($f, $v, $data_item)){
            return true;
        }
        return false;
    }

    /**
     * Check value is in diapasone (>= $v1 AND <=$v2)
     * @param string $f Testing field name
     * @param array $v Testing value
     * @param array $data_item
     * @return boolean
     */
    protected function isConditionIN($f, $v, $data_item){
        if($this->isConditionGTEQ($f, $v[0], $data_item) && $this->isConditionLTEQ($f, $v[1], $data_item)){
            return true;
        }
        return false;
    }
    
    /**
     * Check value is not in diapasone (!(>= $v1 AND <=$v2))
     * @param string $f Testing field name
     * @param array $v Testing value
     * @param array $data_item
     * @return boolean
     */
    protected function isConditionNIN($f, $v, $data_item){
        if(!$this->isConditionIN($f, $v, $data_item)){
            return true;
        }
        return false;
    }
    
}