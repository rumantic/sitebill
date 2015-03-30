<?php
/**
 *
 * @version $Id: pageNavigation.class.php 1526 2008-09-15 19:21:43Z soeren_nb $
 * @package VirtueMart
 * @subpackage classes
 * @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */

/**
 * Page navigation support class
 * @package code from VirtueMart
 */
class Prosto_Page_Navigation {
  /** @var int The record number to start dislpaying from */
  var $limitstart = null;
  /** @var int Number of rows to display per page */
  var $limit = null;
  /** @var int Total number of rows */
  var $total = null;

  function Prosto_Page_Navigation( $total, $limitstart, $limit ) {
    $this->total = intval( $total );
    $this->limitstart = max( $limitstart, 0 );
    $this->limit = max( $limit, 1 );
    if ($this->limit > $this->total) {
      $this->limitstart = 0;
    }
    if (($this->limit-1)*$this->limitstart > $this->total) {
      $this->limitstart -= $this->limitstart % $this->limit;
    }
  }
  /**
   * Writes the html limit # input box
   * Modified by shumisha to handle SEF URLs 2008-06-28
   */
  function writeLimitBox ( $link = '') {
    echo $this->getLimitBox( $link);
  }
  /**
   * Modified by shumisha to handle SEF URLs 2008-06-28
   * @return string The html for the limit # input box
   */
  function getLimitBox ( $link = '') {
    $limits = array();

    if (!empty($link) && strpos( 'limitstart=', $link) === false) {  // insert limitstart in url if missing // shumisha
      $link .= '&limitstart='.$this->limitstart;
    }
    for ($i=5; $i <= 30; $i+=5) {
      if (empty( $link)) {
        $limits[$i] = $i;
      } else {
        $limits[vmRoute($link.'&limit='.$i)] = $i;
      }
    }
    if (empty( $link)) {
      $limits[50] = 50;
    } else {
      $limits[vmRoute($link.'&limit=50')] = 50;
    }

    // build the html select list
    if (empty( $link)) {
    $html = ps_html::selectList( 'limit', $this->limit, $limits, 1, '',  'onchange="this.form.submit();"' );
    } else {
      $current = vmRoute($link.'&limit='.$this->limit);
      $html = ps_html::selectList( 'limit', $current, $limits, 1, '',  'onchange="location.href=this.value"' );
    }
    $html .= "\n<input type=\"hidden\" name=\"limitstart\" value=\"$this->limitstart\" />";
    return $html;
  }

  function writePagesCounter() {
    echo $this->getPagesCounter();
  }
  /**
   * @return string The html for the pages counter, eg, Results 1-10 of x
   */
  function getPagesCounter() {
    $html = '';
    $from_result = $this->limitstart+1;
    if ($this->limitstart + $this->limit < $this->total) {
      $to_result = $this->limitstart + $this->limit;
    } else {
      $to_result = $this->total;
    }
    if ($this->total > 0) {
      $html .= $GLOBALS['VM_LANG']->_('PN_RESULTS')." $from_result - $to_result ".$GLOBALS['VM_LANG']->_('PN_OF')." $this->total";
    } else {
      //$html .= "\nNo records found.";
    }
    return $html;
  }
  /**
   * Writes the html for the pages counter, eg, Results 1-10 of x
   */
  function writePagesLinks($link='') {
    echo $this->getPagesLinks($link);
  }
  
    function vmRoute( $nonSefUrl) {
        $url = $nonSefUrl;
	    return $url;
	}
    

  
  /**
   * @return string The html links for pages, eg, previous, next, 1 2 3 ... x
   */
  function getPagesLinks($link='') {
    global $VM_LANG;
     
    $displayed_pages = 10;
    $total_pages = ceil( $this->total / $this->limit );
    $this_page = ceil( ($this->limitstart+1) / $this->limit );
    $start_loop = (floor(($this_page-1)/$displayed_pages))*$displayed_pages+1;
    if ($start_loop + $displayed_pages - 1 < $total_pages) {
      $stop_loop = $start_loop + $displayed_pages - 1;
    } else {
      $stop_loop = $total_pages;
    }
    //echo 'test';
    $html = '<ul class="pagination">';
    if ($this_page > 1) {
      $page = ($this_page - 2) * $this->limit;
      if( $link != '') {
        $html .= "\n<li><a href=\"".$this->vmRoute($link.'&limit='.$this->limit.'&limitstart=0')."\" class=\"pagenav\" >&laquo;&laquo; ".'РІ РЅР°С‡Р°Р»Рѕ'."</a></li>";
        $html .= "\n<li><a href=\"".$this->vmRoute($link.'&limit='.$this->limit.'&limitstart='.$page)."\" class=\"pagenav\" >&laquo; ".'РїСЂРµРґС‹РґСѓС‰Р°СЏ'."</a></li>";
      } else {
        $html .= "\n<li><a href=\"#beg\" class=\"pagenav\" title=\"".'РІ РЅР°С‡Р°Р»Рѕ'."\" onclick=\"javascript: document.adminForm.limitstart.value=0; document.adminForm.submit();return false;\">&laquo;&laquo; ".'РІ РЅР°С‡Р°Р»Рѕ'."</a></li>";
        $html .= "\n<li><a href=\"#prev\" class=\"pagenav\" title=\"".'РїСЂРµРґС‹РґСѓС‰Р°СЏ'."\" onclick=\"javascript: document.adminForm.limitstart.value=$page; document.adminForm.submit();return false;\">&laquo; ".'РїСЂРµРґС‹РґСѓС‰Р°СЏ'."</a></li>";
      }
    } else {
      $html .= "\n<li><span class=\"pagenav\">&laquo;&laquo; ".'РІ РЅР°С‡Р°Р»Рѕ'."</span></li>";
      $html .= "\n<li><span class=\"pagenav\">&laquo; ".'РїСЂРµРґС‹РґСѓС‰Р°СЏ'."</span></li>";
    }

    for ($i=$start_loop; $i <= $stop_loop; $i++) {
      $page = ($i - 1) * $this->limit;
      if ($i == $this_page) {
        $html .= "\n<li><span class=\"pagenav\"> $i </span></li>";
      } else {
        if( $link != '') {
          $html .= "\n<li><a href=\"".$this->vmRoute($link.'&limit='.$this->limit.'&limitstart='.$page)."\" class=\"pagenav\"><strong>$i</strong></a></li>";
        } else {
          $html .= "\n<li><a href=\"#$i\" class=\"pagenav\" onclick=\"javascript: document.adminForm.limitstart.value=$page; document.adminForm.submit();return false;\"><strong>$i</strong></a></li>";
        }
      }
    }
    //echo 'test';

    if ($this_page < $total_pages) {
      $page = $this_page * $this->limit;
      $end_page = ($total_pages-1) * $this->limit;
      if( $link != '') {
        $html .= "\n<li><a href=\"".$this->vmRoute($link.'&limit='.$this->limit.'&limitstart='.$page)."\" class=\"pagenav\" title=\"".'СЃР»РµРґСѓСЋС‰Р°СЏ'."\"> ".'СЃР»РµРґСѓСЋС‰Р°СЏ'." &raquo;</a></li>";
        $html .= "\n<li><a href=\"".$this->vmRoute($link.'&limit='.$this->limit.'&limitstart='.$end_page)."\" class=\"pagenav\" title=\"".'РІ РєРѕРЅРµС†'."\"> ".'РІ РєРѕРЅРµС†'." &raquo;&raquo;</a></li>";
      } else {
        $html .= "\n<li><a href=\"#next\" class=\"pagenav\" title=\"".'СЃР»РµРґСѓСЋС‰Р°СЏ'."\" onclick=\"javascript: document.adminForm.limitstart.value=$page; document.adminForm.submit();return false;\"> ".'СЃР»РµРґСѓСЋС‰Р°СЏ'." &raquo;</a></li>";
        $html .= "\n<li><a href=\"#end\" class=\"pagenav\" title=\"".'РІ РєРѕРЅРµС†'."\" onclick=\"javascript: document.adminForm.limitstart.value=$end_page; document.adminForm.submit();return false;\"> ".'РІ РєРѕРЅРµС†'." &raquo;&raquo;</a></li>";
      }
    } else {
      $html .= "\n<li><span class=\"pagenav\">".'СЃР»РµРґСѓСЋС‰Р°СЏ'." &raquo;</span></li>";
      $html .= "\n<li><span class=\"pagenav\">".'РІ РєРѕРЅРµС†'." &raquo;&raquo;</span></li>";
    }
    $html .= "\n</ul>";
    return $html;
  }

  function getListFooter() {
    $html = '<table class="adminlist">';
    if( $this->total > $this->limit || $this->limitstart > 0) {

      $html .= '<tr><th colspan="3">';

      $html .= $this->getPagesLinks();
      $html .= '</th></tr>';
    }
    $html .= '<tr><td nowrap="nowrap" width="48%" align="right">'.$GLOBALS['VM_LANG']->_('PN_DISPLAY_NR').'</td>';
    $html .= '<td>' .$this->getLimitBox() . '</td>';
    $html .= '<td nowrap="nowrap" width="48%" align="left">' . $this->getPagesCounter() . '</td>';
    $html .= '</tr></table>';
  		return $html;
  }
  /**
   * @param int The row index
   * @return int
   */
  function rowNumber( $i ) {
    return $i + 1 + $this->limitstart;
  }
  /**
   * @param int The row index
   * @param string The task to fire
   * @param string The alt text for the icon
   * @return string
   */
  function orderUpIcon( $i, $condition=true, $task='orderup', $alt='', $page, $func ) {
    global $mosConfig_live_site, $VM_LANG;
    if( $alt == '') {
      $alt = JText::_('CMN_ORDER_UP');
    }
    if (($i > 0 || ($i+$this->limitstart > 0)) && $condition) {
      return '<a href="#reorder" onclick="return vm_listItemTask(\'cb'.$i.'\',\''.$task.'\', \'adminForm\', \''.$page.'\', \''.$func.'\')" title="'.$alt.'">
				<img src="'.$mosConfig_live_site.'/administrator/images/uparrow.png" width="12" height="12" border="0" alt="'.$alt.'" />
			</a>';
  		} else {
  		  return '&nbsp;';
  		}
  }
  /**
   * @param int The row index
   * @param int The number of items in the list
   * @param string The task to fire
   * @param string The alt text for the icon
   * @return string
   */
  function orderDownIcon( $i, $n, $condition=true, $task='orderdown', $alt='', $page, $func ) {
    global $mosConfig_live_site, $VM_LANG;
    if( $alt == '') {
      $alt = JText::_('CMN_ORDER_DOWN');
    }
    if (($i < $n-1 || $i+$this->limitstart < $this->total-1) && $condition) {
      return '<a href="#reorder" onclick="return vm_listItemTask(\'cb'.$i.'\',\''.$task.'\', \'adminForm\', \''.$page.'\', \''.$func.'\')" title="'.$alt.'">
				<img src="'.$mosConfig_live_site.'/administrator/images/downarrow.png" width="12" height="12" border="0" alt="'.$alt.'" />
			</a>';
  		} else {
  		  return '&nbsp;';
  		}
  }
}
?>
