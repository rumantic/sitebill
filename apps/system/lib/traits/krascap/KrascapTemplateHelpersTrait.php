<?php
/**
 * KrascapTemplateHelpersTrait — Template helper and utility methods extracted from SiteBill_Krascap.
 *
 * Methods: get_breadcrumbs, getUpdateDate, getTopicTitle, getTopicFullInfo,
 *          extractMetaFromRawData, appendPageNumberTail, getRealtyWrap, getBannerWrap,
 *          getLast, getCityListTr, getTopicListTr, validPage, recordHasPhoto, getRealtyListAsExcell
 */
trait KrascapTemplateHelpersTrait
{
    function get_breadcrumbs($items)
    {
        if (count($items) > 0) {
            return implode(' / ', $items);
        }
        return '';
    }

    /**
     * Get update date
     * @param void
     * @return string
     */
    function getUpdateDate()
    {
        $rs = '<b>' . Multilanguage::_('L_MESSAGE_DB_UPDATED') . ': ' . date('d.m.Y') . '</b>';
        return $rs;
    }

    /**
     * Get topic title
     * @param int $topic_id topic ID
     * @return string
     */
    function getTopicTitle($topic_id)
    {
        $DBC = DBC::getInstance();
        $query = 'SELECT name FROM ' . DB_PREFIX . '_topic WHERE id=? LIMIT 1';
        $stmt = $DBC->query($query, array($topic_id));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            return $ar['name'];
        }
        return '';
    }

    /**
     * Get topic full info
     * @param int $topic_id topic ID
     * @return array
     */
    function getTopicFullInfo($topic_id)
    {
        $DBC = DBC::getInstance();
        $query = 'SELECT * FROM ' . DB_PREFIX . '_topic WHERE id=? LIMIT 1';
        $stmt = $DBC->query($query, array($topic_id));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            return $ar;
        }
        return array();
    }

    function extractMetaFromRawData($objectinfo)
    {

        $responce = array(
            'meta_title' => '',
            'title' => '',
            'description' => '',
            'meta_description' => '',
            'meta_keywords' => ''
        );

        $lang_postfix = $this->getLangPostfix($this->getCurrentLang());

        $titlefields = array('public_title', 'title', 'name');
        foreach ($titlefields as $titlefield) {
            if (isset($objectinfo[$titlefield . $lang_postfix]) && $objectinfo[$titlefield . $lang_postfix] != '') {
                $responce['title'] = $objectinfo[$titlefield . $lang_postfix];
                break;
            } elseif (isset($objectinfo[$titlefield]) && $objectinfo[$titlefield] != '') {
                $responce['title'] = $objectinfo[$titlefield];
                break;
            }
        }

        if (isset($objectinfo['meta_title' . $lang_postfix]) && $objectinfo['meta_title' . $lang_postfix] != '') {
            $responce['meta_title'] = $objectinfo['meta_title' . $lang_postfix];
        } else {
            $responce['meta_title'] = $objectinfo['meta_title'];
        }

        if (isset($objectinfo['description' . $lang_postfix]) && $objectinfo['description' . $lang_postfix] != '') {
            $responce['description'] = $objectinfo['description' . $lang_postfix];
        } elseif ($objectinfo['description'] != '') {
            $responce['description'] = $objectinfo['description'];
        }

        if (isset($objectinfo['meta_description' . $lang_postfix]) && $objectinfo['meta_description' . $lang_postfix] != '') {
            $responce['meta_description'] = $objectinfo['meta_description' . $lang_postfix];
        } elseif ($objectinfo['meta_description'] != '') {
            $responce['meta_description'] = $objectinfo['meta_description'];
        }

        if (isset($objectinfo['meta_keywords' . $lang_postfix]) && $objectinfo['meta_keywords' . $lang_postfix] != '') {
            $responce['meta_keywords'] = $objectinfo['meta_keywords' . $lang_postfix];
        } elseif ($objectinfo['meta_keywords'] != '') {
            $responce['meta_keywords'] = $objectinfo['meta_keywords'];
        }
        return $responce;

    }

    /**
     * Добавление хвоста вида [страница N] в тексты заголовков
     * @param array $metadata массив метаданных страницы
     * @param int $page номер страницы
     */
    private function appendPageNumberTail($metadata, $page = 0)
    {

        if ($page > 1 && 1 == $this->getConfigValue('add_pagenumber_title')) {
            $pagenumber_title_place = intval($this->getConfigValue('add_pagenumber_title_place'));
            if (isset($metadata['title']) && $metadata['title'] != '' && ($pagenumber_title_place == 0 || $pagenumber_title_place == 2)) {
                $metadata['title'] .= ' [' . Multilanguage::_('L_PAGE') . ' ' . $page . ']';
            }
            if (isset($metadata['meta_title']) && $metadata['meta_title'] != '' && ($pagenumber_title_place == 1 || $pagenumber_title_place == 2)) {
                $metadata['meta_title'] .= ' [' . Multilanguage::_('L_PAGE') . ' ' . $page . ']';
            }
        }
        return $metadata;
    }

    function getRealtyWrap($data)
    {
        $ret = '<div class="itm">';
        $ret .= '<a href="' . SITEBILL_MAIN_URL . '/realty' . $data['id'] . '.html">';
        $ret .= '<div class="itm_img"><img src="' . $data['prev'] . '" /></div>';
        $ret .= ($data['topic_name'] != '' ? $data['topic_name'] . '</br>' : '');
        $ret .= ($data['city_name'] != '' ? Multilanguage::_('L_TEXT_CITY_1') . ' <b>' . $data['city_name'] . '</b></br>' : '');
        $ret .= ($data['district_name'] != '' ? Multilanguage::_('L_TEXT_DISTRICT') . ' <b>' . $data['district_name'] . '</b></br>' : '');
        $ret .= '<span class="price">' . number_format($data['price'], 0, ',', ' ') . ' руб.</span>';
        $ret .= '</a>';
        $ret .= '</div>';
        return $ret;
    }

    function getBannerWrap($data)
    {
        $ret = '<div class="itm"><a href="' . SITEBILL_MAIN_URL . $data['href'] . '"><div class="itm_img"><img src="' . SITEBILL_MAIN_URL . $data['src'] . '" /></div></a></div>';
        return $ret;
    }

    function getLast($count = 10)
    {
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/banner/banner.php')) {
            include_once(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/banner/banner.php');
        } else {
            $banners = array(
                array('src' => '/template/frontend/albostar/img/banners3.png', 'href' => '/baner3.html'),
                array('src' => '/template/frontend/albostar/img/banners2.png', 'href' => '/baner2.html'),
                array('src' => '/template/frontend/albostar/img/banners1.png', 'href' => '/baner1.html')
            );
        }
        $DBC = DBC::getInstance();
        $ret = array();
        $query = 'SELECT MAX( i.image_id ) AS image_id, i.id FROM ' . DB_PREFIX . '_data_image i, ' . DB_PREFIX . '_data d WHERE (d.id = i.id AND d.active =1) GROUP BY i.id ORDER BY i.id DESC LIMIT 0 , ?';
        $stmt = $DBC->query($query, array($count));
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $data[$ar['id']] = $ar['image_id'];
            }
        }

        if (count($data) > 0) {
            $dids = array_keys($data);
            //$iids=
            $query = 'SELECT d.id, d.price, t.name AS topic_name, c.name AS city_name, ds.name AS district_name FROM ' . DB_PREFIX . '_data d LEFT JOIN ' . DB_PREFIX . '_topic t ON d.topic_id=t.id LEFT JOIN ' . DB_PREFIX . '_city c ON d.city_id=c.city_id LEFT JOIN ' . DB_PREFIX . '_district ds ON d.district_id=ds.id WHERE d.id IN (' . implode(',', $dids) . ')';
            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $ret[$ar['id']] = $ar;
                }
            }

            $flip_data = array_flip($data);
            //echo $query;
            foreach ($flip_data as $img_not_need => $data_id) {
                $query = 'SELECT i.image_id, i.preview FROM ' . DB_PREFIX . '_image i, ' . DB_PREFIX . '_data_image d WHERE d.id=' . $data_id . ' and d.image_id=i.image_id order by d.sort_order limit 1';
                $stmt = $DBC->query($query);
                $ar = $DBC->fetch($stmt);

                $ret[$data_id]['prev'] = SITEBILL_MAIN_URL . '/img/data/' . $ar['preview'];
            }
        }
        $countd = count($ret);
        foreach ($ret as $r) {
            $temp[] = $r;
        }
        unset($ret);
        $ret = $temp;
        unset($temp);
        if ($countd == 0) {
            foreach ($banners as $b) {
                $ret_b[] = $this->getBannerWrap($b);
            }
        } elseif ($countd < 3) {
            $mid = floor($countd / 2);
            $ret_b[] = $this->getBannerWrap($banners[0]);

            for ($i = 0; $i < $mid; $i++) {
                $ret_b[] = $this->getRealtyWrap($ret[$i]);
            }
            $ret_b[] = $this->getBannerWrap($banners[1]);
            for ($i = $mid; $i < $countd; $i++) {
                $ret_b[] = $this->getRealtyWrap($ret[$i]);
            }
            $ret_b[] = $this->getBannerWrap($banners[2]);
        } else {
            $mid = floor($countd / 3);
            for ($i = 0; $i < $mid; $i++) {
                $ret_b[] = $this->getRealtyWrap($ret[$i]);
            }
            $ret_b[] = $this->getBannerWrap($banners[0]);
            for ($i = $mid; $i < $mid * 2; $i++) {
                $ret_b[] = $this->getRealtyWrap($ret[$i]);
            }
            $ret_b[] = $this->getBannerWrap($banners[1]);
            for ($i = $mid * 2; $i < $countd; $i++) {
                $ret_b[] = $this->getRealtyWrap($ret[$i]);
            }
            $ret_b[] = $this->getBannerWrap($banners[2]);
        }


        //echo '<pre>';
        //print_r($ret);
        //print_r($ret_b);
        return $ret_b;
    }

    function getCityListTr()
    {
        $city = array();
        $translite_names = array();
        if (1 == $this->getConfigValue('apps.geodata.enable')) {
            $query = 'SELECT city_id, name, translit_name, geo_lat, geo_lng FROM ' . DB_PREFIX . '_city';
        } else {
            $query = 'SELECT city_id, name, translit_name FROM ' . DB_PREFIX . '_city';
        }
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $city[] = $ar;
                $translite_names[$ar['city_id']] = $ar['translit_name'];
            }
        }

        if (count($city) > 0) {
            $query = 'UPDATE ' . DB_PREFIX . '_city SET translit_name=? WHERE city_id=?';
            foreach ($city as &$c) {
                if ($c['translit_name'] == '') {
                    $_tn = $this->transliteMe($c['name']);
                    if (in_array($_tn, $translite_names)) {
                        $_tn = $_tn . '_' . rand(10, 99);
                    }

                    $stmt = $DBC->query($query, array($_tn, $c['city_id']));
                    if ($stmt) {
                        //$ar=$DBC->fetch($stmt);
                        $translite_names[] = $_tn;
                        $c['translit_name'] = $_tn;
                    }
                }
            }
        }
        return $city;
    }

    function getTopicListTr()
    {
        $DBC = DBC::getInstance();
        $topic = array();
        $translite_names = array();
        $query = 'SELECT id, name, translit_name FROM ' . DB_PREFIX . '_topic';
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $topic[] = $ar;
                $translite_names[$ar['id']] = $ar['translit_name'];
            }
        }

        if (count($topic) > 0) {
            $query = 'UPDATE ' . DB_PREFIX . '_topic SET translit_name=? WHERE id=?';
            foreach ($topic as &$c) {
                if ($c['translit_name'] == '') {
                    $_tn = $this->transliteMe($c['name']);
                    if (in_array($_tn, $translite_names)) {
                        $_tn = $_tn . '_' . $c['id'];
                    }
                    $stmt = $DBC->query($query, array($_tn, $c['id']));
                    if ($stmt) {
                        $ar = $DBC->fetch($stmt);
                        $translite_names[] = $_tn;
                        $c['translit_name'] = $_tn;
                    }
                }
            }
        }
        return $topic;
    }

    /**
     * Valid page
     * @param int $array_count array count
     * @param int $counter counter
     * @param int $page page
     * @return boolean
     */
    function validPage($array_count, $counter, $page = 1)
    {
        //global $per_page;
        $per_page = $this->getConfigValue('per_page');
        //echo "page = $page, counter = $counter, per_page = $per_page";
        if ($page == '') {
            $page = 1;
        }
        if (($counter > $per_page * ($page - 1)) and ($counter <= $per_page * $page)) {
            return true;
        }
        return false;
    }

    /**
     * If record has photo then return true else false
     * @param int $record_id record ID
     * @return boolean
     */
    function recordHasPhoto($record_id)
    {
        for ($index = 0; $index <= $this->image_number; $index++) {
            if ($this->getPreviewImage($record_id, $index)) {
                return true;
            }
        }
    }

    /*
     * функция для возврата списка в виде єксель-таблицы
     */
    function getRealtyListAsExcell($data)
    {
        $objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $column = 0;

        foreach ($data['data'] as $item_id => $data_item_a) {
            $row = $item_id + 2;
            $column = 0;

            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column, $row, SiteBill::iconv(SITE_ENCODING, 'utf-8', $data_item_a['id']));
            $column += 1;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column, $row, SiteBill::iconv(SITE_ENCODING, 'utf-8', $data_item_a['price']));
            $column += 1;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column, $row, SiteBill::iconv(SITE_ENCODING, 'utf-8', $data_item_a['currency']));
            $column += 1;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column, $row, SiteBill::iconv(SITE_ENCODING, 'utf-8', $data_item_a['price_ue']));
            $column += 1;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column, $row, SiteBill::iconv(SITE_ENCODING, 'utf-8', $data_item_a['city']));
        }

        $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($objPHPExcel);
        $xlsx_file_name = "data" . date('Y-m-d_H_i') . ".xlsx";
        $xlsx_output_file = SITEBILL_DOCUMENT_ROOT . "/cache/upl/" . $xlsx_file_name;
        $objWriter->save($xlsx_output_file);

        $handle = fopen($xlsx_output_file, "r");
        $contents = fread($handle, filesize($xlsx_output_file));
        header("Content-type: application/octet-stream");
        header("Content-disposition: attachment; filename=" . $xlsx_file_name . "");
        echo $contents;
        exit;
    }
}
