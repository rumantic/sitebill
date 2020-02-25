<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

interface grid_item_renderer {

    public function fetch_template($item_name, $row_data);
}
