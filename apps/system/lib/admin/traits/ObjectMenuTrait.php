<?php
/**
 * ObjectMenuTrait — Top menu, breadcrumbs, and app title bar methods extracted from Object_Manager.
 *
 * Methods: getTopMenu, compile_top_menu, add_top_menu_item, get_top_menu_items,
 *          get_app_title_bar, add_breadcrumbs_title_item, get_breadcrumbs_title_array,
 *          set_extended_items, get_extended_items
 *
 * @see Object_Manager
 */

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

trait ObjectMenuTrait
{
    /**
     * @var array
     */
    private $breadcrumbs_title;

    /**
     * @var array
     */
    private $top_menu_items = array();

    /**
     * @var string
     */
    private $extended_items = '';

    function getTopMenu()
    {
        $this->add_top_menu_item(
            '?action=' . $this->action . '&do=new',
            Multilanguage::_('L_ADD_RECORD_BUTTON'),
            'btn btn-primary',
            'first'
        );
        return $this->compile_top_menu();
    }

    function compile_top_menu()
    {
        $top_menu_items = $this->get_top_menu_items();
        $rs = '';

        if (is_array($top_menu_items) and count($top_menu_items) > 0) {
            foreach ($top_menu_items as $item) {
                $rs .= '<a href="' . $item['href'] . '" class="' . $item['class'] . '">' . $item['title'] . '</a> ';
            }
        }
        $rs .= $this->get_extended_items();
        return $rs;
    }

    function add_top_menu_item($href, $title, $class = 'btn btn-primary', $position = 'next', $action = '', $icon = '')
    {
        $item = [
            'href' => $href,
            'title' => $title,
            'class' => $class,
            'action' => $action,
            'icon' => $icon
        ];
        if ($position == 'first') {
            array_unshift($this->top_menu_items, $item);
        } else {
            $this->top_menu_items[] = $item;
        }
    }

    function get_top_menu_items()
    {
        return $this->top_menu_items;
    }

    function get_app_title_bar()
    {
        $breadcrumbs = array();
        $breadcrumbs[] = array('href' => '#', 'title' => Multilanguage::_('L_ADMIN_MENU_APPLICATIONS'));

        if (!empty($this->app_title)) {
            $breadcrumbs[] = array('href' => '?action=' . $this->action . '', 'title' => $this->app_title);
        } else {
            $breadcrumbs[] = array('href' => '?action=' . $this->action . '', 'title' => $this->action);
        }
        if ($this->get_breadcrumbs_title_array()) {
            $breadcrumbs = array_merge($breadcrumbs, $this->get_breadcrumbs_title_array());
        }
        $help_link = '<a href="' . SITEBILL_MAIN_URL . '?action=' . $this->action . '&do=help">Help</a>';
        $this->template->assign('help_link', $help_link);
        $this->template->assign('breadcrumbs_array', $breadcrumbs);
        $this->template->assign('app_title', $this->app_title);
        return '';
    }

    function add_breadcrumbs_title_item($title, $href)
    {
        $this->breadcrumbs_title[] = array('href' => $href . '', 'title' => $title);
    }

    function get_breadcrumbs_title_array()
    {
        if (is_array($this->breadcrumbs_title) and count($this->breadcrumbs_title) > 0) {
            return $this->breadcrumbs_title;
        }
        return false;
    }

    function set_extended_items($items)
    {
        $this->extended_items = $items;
    }

    function get_extended_items()
    {
        return $this->extended_items;
    }
}
