<?php
namespace bridge\traits\navigation;

trait BreadCrumbsTrait {
    function compose_breadcrumbs_legacy_array ( $breadcrumbs ) {
        foreach ($breadcrumbs as $bc) {
            if ($bc['href'] != '') {
                $bc_ar[] = '<a href="' . $bc['href'] . '">' . $bc['title'] . '</a>';
            } else {
                $bc_ar[] = $bc['title'];
            }
        }
        return $bc_ar;
    }

}
