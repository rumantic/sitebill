<?php
/**
 * PaginationTrait — extracted from SiteBill class (sitebill.php)
 * Auto-generated, do not edit manually.
 */
trait PaginationTrait
{
    function get_page_links_list_default($page, $total, $per_page, $params)
    {
        if ($total <= $per_page) {
            return '';
        }
        if (isset($params['page_url']) && $params['page_url'] != '') {
            $url = SITEBILL_MAIN_URL . '/' . $params['page_url'];
            unset($params['page_url']);
        } else {
            $url = '';
        }
        $pairs = array();
        unset($params['page']);
        if (count($params) > 0) {
            foreach ($params as $key => $value) {
                if (is_array($value)) {
                    if (count($value) > 0) {
                        foreach ($value as $v) {
                            if ($v != '') {
                                $pairs[] = $key . '[]=' . $v;
                            }
                        }
                    }
                } elseif ($value != '') {
                    $pairs[] = "$key=$value";
                }
            }
        }
        if (count($pairs) > 0) {
            $url = $url . '?' . implode('&', $pairs);
        } else {
            $url = $url;
        }

        $current_page = $page;
        if ($current_page == '') {
            $current_page = 1;
        } else {
            $current_page = (int)$current_page;
        }

        $limit = $per_page;

        $total_pages = ceil($total / $limit);
        $page_navigation = '';
        $first_page_navigation = '';
        $last_page_navigation = '';
        $start_page_navigation = '';
        $end_page_navigation = '';
        $p_prew = $current_page - 1;
        $p_next = $current_page + 1;

        $last_number_page = '<li><a rel="nofollow" href="' . $url . (false !== strpos($url, '?') ? '&page=' . $total_pages : '?page=' . $total_pages) . '" class="pagenav"><strong>' . $total_pages . '</strong></a></li>';

        if ($current_page == 1) {
            $first_page_navigation .= '<li><span class="pagenav">&laquo;&laquo; </span></li>';
        } else {
            $first_page_navigation .= '<li><a rel="nofollow" href="' . $url . (false !== strpos($url, '?') ? '&page=1' : '?page=1') . '" class="pagenav" title="в начало">&laquo;&laquo; </a></li>';
        }

        if ($current_page == $total_pages) {
            $last_page_navigation .= '<li><span class="pagenav"> &raquo;&raquo;</span></li>';
            $last_number_page = '';
        } else {
            $last_page_navigation .= '<li><a rel="nofollow" href="' . $url . (false !== strpos($url, '?') ? '&page=' . $total_pages : '?page=' . $total_pages) . '" class="pagenav" title="в конец"> &raquo;&raquo;</a></li>';
        }

        if ($p_prew < 1) {
            $start_page_navigation .= '<li><span class="pagenav">&laquo; </span></li>';
        } else {
            $start_page_navigation .= '<li><a rel="nofollow" href="' . $url . (false !== strpos($url, '?') ? '&page=' . $p_prew : '?page=' . $p_prew) . '" class="pagenav" title="предыдущая">&laquo; </a></li>';
        }

        if ($p_next > $total_pages) {
            $end_page_navigation .= '<li><span class="pagenav"> &raquo;</span></li>';
        } else {
            $end_page_navigation .= '<li><a rel="nofollow" href="' . $url . (false !== strpos($url, '?') ? '&page=' . $p_next : '?page=' . $p_next) . '" class="pagenav" title="следующая"> &raquo;</a></li>';
        }


        $linestart = $current_page - 7;
        $lineend = $current_page + 7;

        if ($linestart <= 1) {
            $linestart = 1;
            $lineprefix = '';
        } else {
            $lineprefix = '<li>...</li>';
        }

        if ($lineend >= $total_pages) {
            $lineend = $total_pages;
            $last_number_page = '';
            $linepostfix = '';
        } else {
            $linepostfix = '<li>...</li>';
        }

        for ($i = $linestart; $i <= $lineend; $i++) {
            if ($current_page == $i) {
                $page_navigation .= '<li><span class="pagenav"> ' . $i . ' </span></li>';
            } else {
                $page_navigation .= '<li><a rel="nofollow" href="' . $url . (false !== strpos($url, '?') ? '&page=' . $i : '?page=' . $i) . '" class="pagenav"><strong>' . $i . '</strong></a></li>';
            }
        }
        $page_navigation = '<ul class="pagination">' . $first_page_navigation . $start_page_navigation . $lineprefix . $page_navigation . $linepostfix . $end_page_navigation . $last_number_page . $last_page_navigation . '</ul>';
        return $page_navigation;
    }

    /**
     * Get page links list
     * @param int $cur_page current page number
     * @param int $total
     * @param int $per_page
     * @param array $params
     * @return array
     */
    function get_page_links_list($page, $total, $per_page, $params)
    {

        if (defined('ADMIN_MODE')) {
            return $this->get_page_links_list_default($page, $total, $per_page, $params);
        }

        $pager_settings = array();
        $pager_settings['draw_all_pages'] = intval($this->getConfigValue('core.listing.pager_draw_all'));
        $pager_settings['draw_all_pages_max'] = intval($this->getConfigValue('core.listing.pager_draw_all_max'));
        $pager_settings['active_page_offset'] = intval($this->getConfigValue('core.listing.pager_page_offset'));
        $pager_settings['show_end_links'] = intval($this->getConfigValue('core.listing.pager_end_buttons'));
        $pager_settings['show_prev_links'] = intval($this->getConfigValue('core.listing.pager_prev_buttons'));
        $pager_settings['show_prefixes'] = intval($this->getConfigValue('core.listing.pager_show_prefixes'));

        if ($total <= $per_page) {
            return '';
        }

        if (isset($params['page_url']) && $params['page_url'] != '') {
            //$url = SITEBILL_MAIN_URL . '/' . $params['page_url'] . '/?';
            $url = $params['page_url'] . (false === strpos($params['page_url'], '.') ? '/' : '') . '?';
        } else {
            //$url = SITEBILL_MAIN_URL . '/?';
            $url = '?';
        }

        unset($params['page_url']);
        unset($params['page']);

        if (count($params) > 0) {
            $pager_params_string = urldecode(http_build_query($params));
        } else {
            $pager_params_string = '';
        }


        $current_page = $page;
        if ($current_page == '') {
            $current_page = 1;
        } else {
            $current_page = (int)$current_page;
        }

        $limit = $per_page;

        $total_pages = ceil($total / $limit);
        if ($total_pages <= $pager_settings['draw_all_pages_max']) {
            $pager_settings['draw_all_pages'] = 1;
        }
        $pages_count = ceil($total / $limit);
        if ($total_pages < 2) {
            return '';
        }

        $ret = array();

        $p_prew = $current_page - 1;
        $p_next = $current_page + 1;

        if ($current_page == 1) {
            $fpn['text'] = '&laquo;&laquo;';
            $fpn['href'] = $this->createUrlTpl($url . 'page=1' . ($pager_params_string != '' ? '&' . $pager_params_string : ''));
        } else {
            $fpn['text'] = '&laquo;&laquo;';
            $fpn['href'] = $this->createUrlTpl($url . 'page=1' . ($pager_params_string != '' ? '&' . $pager_params_string : ''));
        }

        $ret['fpn'] = $fpn;

        if ($current_page == $total_pages) {
            $lpn['text'] = '&raquo;&raquo;';
            $lpn['href'] = '';
        } else {
            $lpn['text'] = '&raquo;&raquo;';
            $lpn['href'] = $this->createUrlTpl($url . 'page=' . $total_pages . ($pager_params_string != '' ? '&' . $pager_params_string : ''));
        }

        $ret['lpn'] = $lpn;

        if ($p_prew < 1) {
            $ppn['text'] = '&laquo;';
            $ppn['href'] = '';
        } else {
            $ppn['text'] = '&laquo;';
            $ppn['href'] = $this->createUrlTpl($url . 'page=' . $p_prew . ($pager_params_string != '' ? '&' . $pager_params_string : ''));
            $ppn['go_page'] = $p_prew;
        }

        $ret['ppn'] = $ppn;

        if ($p_next > $total_pages) {
            $npn['text'] = '&raquo;';
            $npn['href'] = '';
        } else {
            $npn['text'] = '&raquo;';
            $npn['href'] = $this->createUrlTpl($url . 'page=' . $p_next . ($pager_params_string != '' ? '&' . $pager_params_string : ''));
            $npn['go_page'] = $p_next;
        }

        $ret['npn'] = $npn;

        $start_page = $current_page - $pager_settings['active_page_offset'];
        $end_page = $current_page + $pager_settings['active_page_offset'];

        if ($start_page <= 1) {
            $pager_settings['left_prefix'] = 0;
            $pager_settings['start'] = 1;
        } else {
            $pager_settings['left_prefix'] = 0;
            if ($pager_settings['show_prefixes'] == 1) {
                $pager_settings['left_prefix'] = 1;
            }
            $pager_settings['start'] = $start_page;
        }

        if ($end_page >= $total_pages) {
            $pager_settings['right_prefix'] = 0;
            $pager_settings['end'] = $total_pages;
        } else {
            $pager_settings['right_prefix'] = 0;
            if ($pager_settings['show_prefixes'] == 1) {
                $pager_settings['right_prefix'] = 1;
            }
            $pager_settings['end'] = $end_page;
        }

        for ($i = 1; $i <= $total_pages; $i++) {
            if ($i == $current_page) {
                $ret['pages'][$i] = array('text' => $i, 'href' => '', 'current' => '1');
            } else {
                $ret['pages'][$i] = array('text' => $i, 'href' => $this->createUrlTpl($url . 'page=' . $i . ($pager_params_string != '' ? '&' . $pager_params_string : '')), 'current' => '0');
            }
        }

        $ret['current_page'] = $current_page;
        $ret['total_pages'] = $total_pages;

        global $smarty;
        $this->template->assign('pager_settings', $pager_settings);
        $this->template->assign('paging', $ret);
        $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/common_pager.tpl';
        if (!file_exists($tpl)) {
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/system/template/common_pager_' . $this->getConfigValue('theme') . '.tpl')) {
                $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/system/template/common_pager_' . $this->getConfigValue('theme') . '.tpl';
            } else {
                $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/system/template/common_pager.tpl';
            }
        }
        return $this->template->fetch($tpl);
    }

}
