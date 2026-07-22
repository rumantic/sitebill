<?php
/**
 * ViewSeoTrait — SEO metadata, breadcrumbs, and title formatting for Kvartira_View.
 *
 * Manages: getMetaData, formatTitle, getPublicMetaData, getBreadcrumbs.
 */
trait ViewSeoTrait
{
    protected function getBreadcrumbs($params)
    {
        /* if(!is_null($this->realty)){
          $p['topic_id']=intval($this->realty['topic_id']['value']);
          require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
          $Structure_Manager = new Structure_Manager();
          $category_structure = $Structure_Manager->loadCategoryStructure();

          $cch=$Structure_Manager->createCatalogChains();
          //echo intval($this->realty['topic_id']['value']);
          //print_r($cch);

          $bclevels=array(
          array('topic_id', 'optype', 'country_id'),
          array('topic_id', 'optype'),
          array('topic_id'),
          );

          $bc=array_reverse($bclevels);
          $bread_crumbs=array();
          $bclevels=array();
          $bclevels_str=array();

          foreach($bc as $level){
          $parts=array();
          $params=array();
          foreach($level as $point){
          $parts[]=$this->realty[$point]['value_string'];
          $params[$point]=$this->realty[$point]['value'];
          }
          $bclevels[]=array(
          'title'=>implode(', ', $parts),
          'params'=>$params,
          'href'=>SITEBILL_MAIN_URL.'/?'.http_build_query($params)
          );
          $bclevels_str[]='<a href="'.SITEBILL_MAIN_URL.'/?'.http_build_query($params).'">'.implode(', ', $parts).'</a>';
          //$bread_crumbs[]=
          }

          $bclevels_str[]=$this->realty_title;

          return implode(' / ', $bclevels_str);
          print_r($bclevels);
          return $this->get_category_breadcrumbs( $params, $category_structure, SITEBILL_MAIN_URL.'/' );
          } */
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $category_structure = $Structure_Manager->loadCategoryStructure();
        return $this->get_category_breadcrumbs($params, $category_structure, SITEBILL_MAIN_URL . '/');
    }

    public function getPublicMetaData($form_data, $hasTlocation = false, $tlocationElement = '')
    {
        $result = $this->get_query_cache_value(json_encode($form_data).$hasTlocation.$tlocationElement, array());
        if ( $result['result'] === true ) {
            $info = json_decode($result['value'], true);
        } else {
            $info = $this->getMetaData($form_data, $hasTlocation, $tlocationElement);
            $this->insert_query_cache_value(json_encode($form_data).$hasTlocation.$tlocationElement, array(), json_encode($info));
        }
        return $info;
    }

    public function formatTitle($form_data, $title_str)
    {
        if ($title_str == '') {
            return '';
        }

        preg_match_all('/{([^}]+)}/', $title_str, $matches);

        $str_parts = array();
        if (count($matches[1]) > 0) {
            foreach ($matches[1] as $key => $keyval) {
                if ($keyval == '!topic_path') {
                    $params['topic_id'] = $form_data['topic_id']['value'];
                    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
                    $Structure_Manager = new Structure_Manager();
                    $category_structure = $Structure_Manager->loadCategoryStructure();
                    $str_parts[$key] = $this->get_category_breadcrumbs_string($params, $category_structure, SITEBILL_MAIN_URL . '/');
                } elseif (isset($form_data[$keyval])) {
                    if (in_array($form_data[$keyval]['type'], array('select_box', 'select_by_query', 'select_box_structure'))) {
                        $str_parts[$key] = $form_data[$keyval]['value_string'];
                    } elseif ($form_data[$keyval]['type'] == 'price') {
                        $str_parts[$key] = number_format($form_data[$keyval]['value'], 0, ',', ' ');
                    } else {
                        $str_parts[$key] = $form_data[$keyval]['value'];
                    }
                } else {
                    $str_parts[$key] = '';
                }
            }

            $keys = array();

            foreach ($matches[1] as $key => $keyval) {
                $keys[$key] = '{' . $keyval . '}';
            }

            $title_str = str_replace($keys, $str_parts, $title_str);

            $title = $title_str;
        }
        return $title;
    }

    protected function getMetaData($form_data, $hasTlocation = false, $tlocationElement = '')
    {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $category_structure = $Structure_Manager->loadCategoryStructure();


        $title = '';
        $meta_title = '';
        $meta_description = '';
        $meta_keywords = '';
        $params['topic_id'] = $form_data['topic_id']['value'];

        $title_str = trim($this->getConfigValue('apps.realty.title_preg'));

        if ($title_str != '') {
            //$title_str='';


            preg_match_all('/{([^}]+)}/', $title_str, $matches);

            $str_parts = array();
            if (count($matches[1]) > 0) {
                foreach ($matches[1] as $key => $keyval) {
                    if ($keyval == '!topic_path') {
                        $str_parts[$key] = $this->get_category_breadcrumbs_string($params, $category_structure, SITEBILL_MAIN_URL . '/');
                    } elseif (isset($form_data[$keyval])) {
                        if (in_array($form_data[$keyval]['type'], array('select_box', 'select_by_query', 'select_box_structure'))) {
                            $str_parts[$key] = $form_data[$keyval]['value_string'];
                        } elseif ($form_data[$keyval]['type'] == 'price') {
                            if ($form_data[$keyval]['value'] != floor($form_data[$keyval]['value'])) {
                                $str_parts[$key] = number_format($form_data[$keyval]['value'], 2, '.', ' ');
                            } else {
                                $str_parts[$key] = number_format($form_data[$keyval]['value'], 0, '.', ' ');
                            }

                        } else {
                            $str_parts[$key] = $form_data[$keyval]['value'];
                        }
                    } else {
                        $str_parts[$key] = '';
                    }
                }

                $keys = array();

                foreach ($matches[1] as $key => $keyval) {
                    $keys[$key] = '{' . $keyval . '}';
                }

                $title_str = str_replace($keys, $str_parts, $title_str);

                $title = $title_str;
            }
        }
        if ($title == '' || $title_str == '') {
            $title_parts = array();
            if ($hasTlocation) {
                $title_parts[] = $this->get_category_breadcrumbs_string($params, $category_structure, SITEBILL_MAIN_URL . '/');
                if ($form_data[$tlocationElement]['tlocation_string'] != '') {
                    $title_parts[] = $form_data[$tlocationElement]['tlocation_string'];
                }
                if (0 != (int)$form_data['price']['value']) {
                    $title_parts[] = number_format($form_data['price']['value'], 0, ',', ' ');
                }
                if (!empty($title_parts)) {
                    $title = implode(', ', $title_parts);
                }
            } else {
                $title_parts[] = $this->get_category_breadcrumbs_string($params, $category_structure, SITEBILL_MAIN_URL . '/');
                if (isset($form_data['country_id']) && $form_data['country_id']['value_string'] != '') {
                    $title_parts[] = $form_data['country_id']['value_string'];
                }
                if (isset($form_data['region_id']) && $form_data['region_id']['value_string'] != '') {
                    $title_parts[] = $form_data['region_id']['value_string'];
                }
                if ($form_data['city_id']['value_string'] != '') {
                    $title_parts[] = $form_data['city_id']['value_string'];
                }
                if ($form_data['street_id']['value_string'] != '') {
                    $title_parts[] = $form_data['street_id']['value_string'];
                }
                if (0 != (int)$form_data['price']['value']) {
                    if (1 == $this->getConfigValue('currency_enable') && isset($form_data['currency_id']) && $form_data['currency_id']['value'] > 0) {
                        $title_parts[] = number_format($form_data['price']['value'], 0, ',', ' ') . ' ' . $form_data['currency_id']['value_string'];
                    } else {
                        $title_parts[] = number_format($form_data['price']['value'], 0, ',', ' ');
                    }
                }
                if (!empty($title_parts)) {
                    $title = implode(', ', $title_parts);
                }
            }
        }


        $this->realty_title = $title;


        if ($form_data['meta_title']['value'] == '') {
            $meta_title = $title;
        } else {
            $meta_title = $form_data['meta_title']['value'];
        }

        if ($form_data['meta_description']['value'] != '') {
            $meta_description = $form_data['meta_description']['value'];
        }

        if ($form_data['meta_keywords']['value'] != '') {
            $meta_keywords = $form_data['meta_keywords']['value'];
        }


        /*
          if($form_data['meta_title']['value']==''){
          $this->template->assign('title', $form_data['topic_id']['value_string'].", ".$form_data['city_id']['value_string'].", ".$form_data['street_id']['value_string'].', цена: '.$form_data['price']['value'].' '.($form_data['currency_id']['value_string']=='' ? Multilanguage::_('L_RUR_SHORT') : $form_data['currency_id']['value_string']).' | '.$this->getConfigValue('site_title') );
          }else{
          $this->template->assign('title', $form_data['meta_title']['value']);
          }

          if($form_data['meta_description']['value']!=''){
          $this->template->assign('meta_description', $form_data['meta_description']['value']);
          }else{
          $this->template->assign('meta_description', $form_data['text']['value'].' '.$this->getConfigValue('site_title'));
          }

          if($form_data['meta_keywords']['value']!=''){
          $this->template->assign('meta_keywords', $form_data['meta_keywords']['value']);
          }else{
          $kw=array();


          $kw[]=$this->getConfigValue('meta_keywords_main');
          $kw[]=$form_data['optype']['value_string'];
          $kw[]=$form_data['topic_id']['value_string'];
          $kw[]=$form_data['city_id']['value_string'];
          $kw[]=$form_data['district_id']['value_string'];
          if($form_data['room_count']['value']>0){
          $kw[]='комнат '.$form_data['room_count']['value'];
          }
          $kw=array_filter($kw);
          if(count($kw)>0){
          $this->template->assign('meta_keywords', implode(', ', $kw));
          }else{
          $this->template->assign('meta_keywords', $this->getConfigValue('meta_keywords_main'));
          }
          }
         */


        return array(
            'title' => $title,
            'meta_title' => $meta_title,
            'meta_description' => $meta_description,
            'meta_keywords' => $meta_keywords
        );
    }
}
