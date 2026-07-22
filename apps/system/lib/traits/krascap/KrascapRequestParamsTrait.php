<?php
/**
 * KrascapRequestParamsTrait — Request parameter gathering methods extracted from SiteBill_Krascap.
 *
 * Methods: gatherRequestParams, safeRequestParams, initDataFromRequest, setGridViewType
 */
trait KrascapRequestParamsTrait
{
    public function gatherRequestParams()
    {

        $REQUESTURIPATH = SiteBill::getClearRequestURI();
        $params = array();

        if (NULL !== $this->getRequestValue('id')) {
            if (is_array($this->getRequestValue('id'))) {
                $params['id'] = $this->getRequestValue('id');
            } else {
                $params['id'] = (int)$this->getRequestValue('id');
            }
        }

        if (NULL !== $this->getRequestValue('wlocation')) {
            $params['wlocation'] = $this->safeRequestParams($this->getRequestValue('wlocation'));
        }
        if (NULL !== $this->getRequestValue('loc')) {
            $params['loc'] = $this->safeRequestParams($this->getRequestValue('loc'));
        }
        if (NULL !== $this->getRequestValue('topic_id')) {
            $params['topic_id'] = $this->safeRequestParams($this->getRequestValue('topic_id'));
        }
        if (NULL !== $this->getRequestValue('order')) {
            $params['order'] = $this->getRequestValue('order');
        }
        if (NULL !== $this->getRequestValue('region_id')) {
            $params['region_id'] = $this->safeRequestParams($this->getRequestValue('region_id'));
        }
        if (NULL !== $this->getRequestValue('city_id')) {
            $params['city_id'] = $this->safeRequestParams($this->getRequestValue('city_id'));
        }
        if (NULL !== $this->getRequestValue('district_id')) {
            $params['district_id'] = $this->safeRequestParams($this->getRequestValue('district_id'));
        }
        if (NULL !== $this->getRequestValue('country_id')) {
            $params['country_id'] = $this->safeRequestParams($this->getRequestValue('country_id'));
        }
        if (NULL !== $this->getRequestValue('metro_id')) {
            $params['metro_id'] = $this->safeRequestParams($this->getRequestValue('metro_id'));
        }

        if (NULL !== $this->getRequestValue('street_id')) {
            $params['street_id'] = $this->safeRequestParams($this->getRequestValue('street_id'));
        }


        if ($this->getConfigValue('apps.complex.enable') && NULL !== $this->getRequestValue('complex_id')) {
            $params['complex_id'] = $this->safeRequestParams($this->getRequestValue('complex_id'));
        }
        if (NULL !== $this->getRequestValue('page')) {
            $params['page'] = (int)$this->getRequestValue('page');
        }
        if (NULL !== $this->getRequestValue('spec')) {
            $params['spec'] = $this->getRequestValue('spec');
        }
        if (NULL !== $this->getRequestValue('owner')) {
            $params['owner'] = (int)$this->getRequestValue('owner');
        }
        if (NULL !== $this->getRequestValue('asc')) {
            $params['asc'] = $this->getRequestValue('asc');
        }


        if (NULL !== $this->getRequestValue('user_id')) {
            $params['user_id'] = $this->getRequestValue('user_id');
        }

        if (NULL !== $this->getRequestValue('currency_id')) {
            $params['currency_id'] = (int)$this->getRequestValue('currency_id');
        }
        if (NULL !== $this->getRequestValue('price')) {
            $params['price'] = (int)str_replace(' ', '', $this->getRequestValue('price'));
            $this->template->assert('price', $params['price']);
        }

        if (NULL !== $this->getRequestValue('price_min')) {
            $params['price_min'] = (int)str_replace(' ', '', $this->getRequestValue('price_min'));
            $this->template->assert('price_min', $params['price_min']);
        }

        if (NULL !== $this->getRequestValue('price_pm')) {
            $params['price_pm'] = (int)str_replace(' ', '', $this->getRequestValue('price_pm'));
            $this->template->assert('price_pm', $params['price_pm']);
        }

        if (NULL !== $this->getRequestValue('price_pm_min')) {
            $params['price_pm_min'] = (int)str_replace(' ', '', $this->getRequestValue('price_pm_min'));
            $this->template->assert('price_pm_min', $params['price_pm_min']);
        }

        if (NULL !== $this->getRequestValue('house_number')) {
            $params['house_number'] = $this->getRequestValue('house_number');
            $this->template->assert('house_number', $params['house_number']);
        }

        if (NULL !== $this->getRequestValue('onlyspecial')) {
            $params['onlyspecial'] = $this->getRequestValue('onlyspecial');
            $this->template->assert('onlyspecial', $params['onlyspecial']);
        }

        if (NULL !== $this->getRequestValue('floor')) {
            $params['floor'] = (int)$this->getRequestValue('floor');
        }

        if (NULL !== $this->getRequestValue('floor_count')) {
            $params['floor_count'] = (int)$this->getRequestValue('floor_count');
        }

        if (NULL !== $this->getRequestValue('floor_min')) {
            $params['floor_min'] = (int)$this->getRequestValue('floor_min');
        }

        if (NULL !== $this->getRequestValue('floor_max')) {
            $params['floor_max'] = (int)$this->getRequestValue('floor_max');
        }

        if (NULL !== $this->getRequestValue('floor_count_min')) {
            $params['floor_count_min'] = (int)$this->getRequestValue('floor_count_min');
        }

        if (NULL !== $this->getRequestValue('floor_count_max')) {
            $params['floor_count_max'] = (int)$this->getRequestValue('floor_count_max');
        }

        if (NULL !== $this->getRequestValue('not_first_floor')) {
            $params['not_first_floor'] = (int)$this->getRequestValue('not_first_floor');
        }

        if (NULL !== $this->getRequestValue('not_last_floor')) {
            $params['not_last_floor'] = (int)$this->getRequestValue('not_last_floor');
        }


        if (NULL !== $this->getRequestValue('square_min')) {
            $params['square_min'] = (int)str_replace(' ', '', $this->getRequestValue('square_min'));
        }

        if (NULL !== $this->getRequestValue('square_max')) {
            $params['square_max'] = (int)str_replace(' ', '', $this->getRequestValue('square_max'));
        }

        if (NULL !== $this->getRequestValue('live_square_min')) {
            $params['live_square_min'] = (int)str_replace(' ', '', $this->getRequestValue('live_square_min'));
        }

        if (NULL !== $this->getRequestValue('kitchen_square_min')) {
            $params['kitchen_square_min'] = (int)str_replace(' ', '', $this->getRequestValue('kitchen_square_min'));
        }

        if (NULL !== $this->getRequestValue('kitchen_square_max')) {
            $params['kitchen_square_max'] = (int)str_replace(' ', '', $this->getRequestValue('kitchen_square_max'));
        }

        if (NULL !== $this->getRequestValue('live_square_max')) {
            $params['live_square_max'] = (int)str_replace(' ', '', $this->getRequestValue('live_square_max'));
        }

        if (NULL !== $this->getRequestValue('is_phone')) {
            $params['is_phone'] = (int)$this->getRequestValue('is_phone');
        }

        if (NULL !== $this->getRequestValue('is_balkony')) {
            $params['is_balkony'] = (int)$this->getRequestValue('is_balkony');
        }

        if (NULL !== $this->getRequestValue('is_sanitary')) {
            $params['is_sanitary'] = (int)$this->getRequestValue('is_sanitary');
        }


        if (NULL !== $this->getRequestValue('status')) {
            $params['status'] = (int)$this->getRequestValue('status');
        }


        if (NULL !== $this->getRequestValue('nout_from_sale')) {
            $params['nout_from_sale'] = (int)$this->getRequestValue('nout_from_sale');
        }

        if (NULL !== $this->getRequestValue('nwith_null_params')) {
            $params['nwith_null_params'] = (int)$this->getRequestValue('nwith_null_params');
        }

        if (NULL !== $this->getRequestValue('by_ipoteka')) {
            $params['by_ipoteka'] = (int)$this->getRequestValue('by_ipoteka');
        }

        if (NULL !== $this->getRequestValue('new_only')) {
            $params['new_only'] = (int)$this->getRequestValue('new_only');
        }

        if (NULL !== $this->getRequestValue('is_furniture')) {
            $params['is_furniture'] = (int)$this->getRequestValue('is_furniture');
        }

        if (NULL !== $this->getRequestValue('has_photo')) {
            $params['has_photo'] = (int)$this->getRequestValue('has_photo');
        }

        if (NULL !== $this->getRequestValue('is_internet')) {
            $params['is_internet'] = (int)$this->getRequestValue('is_internet');
        }

        if (NULL !== $this->getRequestValue('room_count')) {
            $params['room_count'] = $this->getRequestValue('room_count');
        }

        if (NULL !== $this->getRequestValue('optype') && null !== $this->getRequestValue('optype')) {
            $params['optype'] = $this->safeRequestParams($this->getRequestValue('optype'));
        }

        if (NULL !== $this->getRequestValue('minbeds')) {
            $params['minbeds'] = (int)$this->getRequestValue('minbeds');
        }

        if (NULL !== $this->getRequestValue('minbaths')) {
            $params['minbaths'] = (int)$this->getRequestValue('minbaths');
        }

        if (NULL !== $this->getRequestValue('uniq_id')) {
            $params['uniq_id'] = (int)$this->getRequestValue('uniq_id');
        }


        if (1 == (int)$this->getRequestValue('export_afy')) {
            $params['export_afy'] = 1;
        }
        if (1 == (int)$this->getRequestValue('export_cian')) {
            $params['export_cian'] = 1;
        }

        if (NULL !== $this->getRequestValue('extended_search')) {
            $params['extended_search'] = $this->getRequestValue('extended_search');
        }
        if (NULL !== $this->getRequestValue('search')) {
            $params['search'] = $this->getRequestValue('search');
        }
        if (NULL !== $this->getRequestValue('srch_word')) {
            $params['srch_word'] = $this->getRequestValue('srch_word');
        }


        if (0 != (int)$this->getRequestValue('page_limit')) {
            $params['page_limit'] = (int)$this->getRequestValue('page_limit');
        }

        if (NULL !== $this->getRequestValue('geocoords')) {
            $params['geocoords'] = preg_replace('/[^0-9.+-:]/', '', $this->getRequestValue('geocoords'));
            if ($params['geocoords'] == '') {
                unset($params['geocoords']);
            }
        }


        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/billing/lib/billing.php') && $this->getConfigValue('apps.billing.enable') == 1) {
            if (NULL !== $this->getRequestValue('vip_status')) {
                $params['vip_status'] = (int)$this->getRequestValue('vip_status');
            }
            if (NULL !== $this->getRequestValue('premium_status')) {
                $params['premium_status'] = (int)$this->getRequestValue('premium_status');
            }
            if (NULL !== $this->getRequestValue('bold_status')) {
                $params['bold_status'] = (int)$this->getRequestValue('bold_status');
            }
        }
        if (NULL !== $this->getRequestValue('dv_ipoteka')) {
            $params['dv_ipoteka'] = $this->getRequestValue('dv_ipoteka');
        }
        if (NULL !== $this->getRequestValue('start_date')) {
            $params['start_date'] = $this->getRequestValue('start_date');
        }
        if (NULL !== $this->getRequestValue('end_date')) {
            $params['end_date'] = $this->getRequestValue('end_date');
        }

        if (NULL !== $this->getRequestValue('rent_period')) {
            $params['rent_period'] = $this->getRequestValue('rent_period');
        }

        if (NULL !== $this->getRequestValue('added_in_days')) {
            $params['added_in_days'] = intval($this->getRequestValue('added_in_days'));
        }

        $params = $this->gatherParamsFromSconfig($params);


        /* if($REQUESTURIPATH=='find'){
          $params['pager_url']=$REQUESTURIPATH;
          } */

        return $params;
    }

    function safeRequestParams($params)
    {
        return $params;
    }

    /**
     * Init data from request
     * @param void
     * @return array
     */
    function initDataFromRequest()
    {
        $data_array['type_id'] = $this->getRequestValue('type_id');
        $data_array['topic_id'] = $this->getRequestValue('topic_id');

        $data_array['tid'] = $this->getRequestValue('tid');
        $data_array['tid1'] = $this->getRequestValue('tid1');
        $data_array['tid2'] = $this->getRequestValue('tid2');

        $data_array['country_id'] = $this->getRequestValue('country_id');
        $data_array['new_country'] = $this->getRequestValue('new_country');

        $data_array['city_id'] = $this->getRequestValue('city_id');
        $data_array['new_city'] = $this->getRequestValue('new_city');

        $data_array['metro_id'] = $this->getRequestValue('metro_id');
        $data_array['new_metro'] = $this->getRequestValue('new_metro');

        $data_array['district_id'] = $this->getRequestValue('district_id');
        $data_array['new_district'] = $this->getRequestValue('new_district');

        $data_array['street'] = $this->getRequestValue('street');
        $data_array['street_id'] = $this->getRequestValue('street_id');
        $data_array['new_street'] = $this->getRequestValue('new_street');

        $data_array['price'] = $this->getRequestValue('price');
        $data_array['contact'] = $this->getRequestValue('contact');
        $data_array['agent_tel'] = $this->getRequestValue('agent_tel');
        $data_array['agent_email'] = $this->getRequestValue('agent_email');

        if ($this->getRequestValue('room_count') != '') {
            $data_array['room_count'] = $this->getRequestValue('room_count');
        } else {
            $data_array['room_count'] = 0;
        }

        //elite
        if ($this->getRequestValue('elite') == 1) {
            $data_array['elite'] = 1;
        } else {
            $data_array['elite'] = 0;
        }

        //active
        if ($this->getRequestValue('active') == 1) {
            $data_array['active'] = 1;
        } else {
            $data_array['active'] = 0;
        }

        //hot
        if ($this->getRequestValue('hot') == 1) {
            $data_array['hot'] = 1;
        } else {
            $data_array['hot'] = 0;
        }

        if ($this->getRequestValue('floor') != '') {
            $data_array['floor'] = $this->getRequestValue('floor');
        } else {
            $data_array['floor'] = 0;
        }

        if ($this->getRequestValue('floor_count') != '') {
            $data_array['floor_count'] = $this->getRequestValue('floor_count');
        } else {
            $data_array['floor_count'] = 0;
        }

        $data_array['walls'] = $this->getRequestValue('walls');
        $data_array['balcony'] = $this->getRequestValue('balcony');
        $data_array['square_all'] = $this->getRequestValue('square_all');
        $data_array['square_live'] = $this->getRequestValue('square_live');
        $data_array['square_kitchen'] = $this->getRequestValue('square_kitchen');
        $data_array['bathroom'] = $this->getRequestValue('bathroom');

        $data_array['text'] = $this->getRequestValue('text');
        $data_array['id'] = $this->getRequestValue('id');
        $data_array['is_telephone'] = $this->getRequestValue('is_telephone');
        $data_array['furniture'] = $this->getRequestValue('furniture');
        $data_array['plate'] = $this->getRequestValue('plate');
        $data_array['number'] = $this->getRequestValue('number');
        return $data_array;
    }

    protected function setGridViewType()
    {

        if (in_array($this->getRequestValue('grid_type'), array('thumbs', 'list'))) {
            $_SESSION['grid_type'] = $this->getRequestValue('grid_type');
        } else {
            if (!isset($_SESSION['grid_type'])) {
                if ($this->getConfigValue('grid_type') != '') {
                    $_SESSION['grid_type'] = $this->getConfigValue('grid_type');
                } else {
                    $_SESSION['grid_type'] = 'list';
                }
            }
        }
    }
}
