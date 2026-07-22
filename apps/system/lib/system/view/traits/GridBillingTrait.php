<?php
/**
 * GridBillingTrait — Billing modal and controls for Common_Grid.
 *
 * Manages: getB (billing modal HTML), billing_controls (VIP/premium/bold status).
 */
trait GridBillingTrait
{
    private function getB()
    {
        $status_cost = array();
        $custom_statuses = array();
        if ($this->grid_object->table_name == 'complex') {
            $status_cost['vip'] = floatval($this->getConfigValue('apps.complex.complex_vip_cost'));
            $status_cost['premium'] = floatval($this->getConfigValue('apps.complex.complex_premium_cost'));
            $status_cost['bold'] = floatval($this->getConfigValue('apps.complex.complex_bold_cost'));
        }


        $ret = '';
        $ret .= '<div class="modal fade" class="makeSpec" id="makeSpec" tabindex="-1" role="dialog" aria-labelledby="makeSpecOk" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
    <h3 id="makeSpecModalLabel">
    	<span class="spec_title spec_title_premium">' . Multilanguage::_('BPREMIUM_MAKE_TIT', 'system') . '</span>
    	<span class="spec_title spec_title_bold">' . Multilanguage::_('BBOLD_MAKE_TIT', 'system') . '</span>
    	<span class="spec_title spec_title_vip">' . Multilanguage::_('BVIP_MAKE_TIT', 'system') . '</span>
    </h3>
  </div>
  <div class="modal-body">
  	<form class="form-horizontal">
  		<input type="hidden" value="" name="realty_id" />
  		<input type="hidden" value="" name="per_day_price" />
  		<input type="hidden" value="" name="type" />
        <input type="hidden" value="' . $this->grid_object->table_name . '" name="object_name" />
            <input type="hidden" value="' . $this->grid_object->primary_key . '" name="object_key" />
  		
  		<input type="hidden" value="' . $status_cost['premium'] . '" id="pdp_premium" />
  		<input type="hidden" value="' . $status_cost['vip'] . '" id="pdp_vip" />
  		<input type="hidden" value="' . $status_cost['bold'] . '" id="pdp_bold" />
		  <div class="control-group">
		    <label class="control-label">' . Multilanguage::_('B_MAKE_DAYS', 'system') . '</label>
		    <div class="controls">
		      <input type="text" value="1" name="days" />
		    </div>
		  </div>
		  <div class="control-group">
		    <label class="control-label">' . Multilanguage::_('$L_PRICE', 'system') . '</label>
		    <div class="controls">
		      <span class="calc_price"></span>
		    </div>
		  </div>
	</form>
	<div class="answer" style="display: none;"></div>
  </div>
  <div class="modal-footer">
  	<button class="btn use_own">' . Multilanguage::_('B_MAKE_USEPACKETS', 'system') . '</button>
	<button class="btn ok">' . Multilanguage::_('OK_NM', 'system') . '</button>
    <button class="btn" data-dismiss="modal" aria-hidden="true">' . Multilanguage::_('CANCEL_NM', 'system') . '</button>
  </div>
</div>';

        $ret .= '<script src="' . SITEBILL_MAIN_URL . '/apps/billing/js/grid_billing.js"></script>';
        return $ret;
    }

    private function billing_controls($row_data)
    {

        $custom_statuses = array();

        if ($this->grid_object->table_name == 'data') {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/billing/admin/admin.php';
            $billing = new billing_admin();

            $custom_statuses = $billing->loadCustomStatuses();

        }

        $DBC = DBC::getInstance();
        $current_statuses = array();

        $status_fileds = array();

        $status_fileds[] = 'vip_status_end';
        $status_fileds[] = 'premium_status_end';
        $status_fileds[] = 'bold_status_end';

        if (!empty($custom_statuses)) {
            foreach ($custom_statuses as $key => $custom_status) {
                $status_fileds[] = $custom_status['field_name'];
            }
        }


        $query = 'SELECT ' . implode(', ', $status_fileds) . ' FROM ' . DB_PREFIX . '_' . $this->grid_object->table_name . ' WHERE `' . $this->grid_object->primary_key . '` = ? LIMIT 1';

        $stmt = $DBC->query($query, array($row_data[$this->grid_object->primary_key]['value']));
        if ($stmt) {
            $current_statuses = $DBC->fetch($stmt);
        }

        // Фильтр по active_in_topic + shared_model
        if ( $this->check_column_activity_in_topic('vip_status_end', $row_data) ) {
            if ($current_statuses['vip_status_end'] > time()) {
                $rs .= ' <span class="vb btn btn-small btn-info btn-disabled"><i class="icon-star icon-black"></i> ' . Multilanguage::_('GB_BVIP_TO', 'system') . ' ' . date('d.m.Y H:i', $current_statuses['vip_status_end']) . '</span>';
            } else {
                $rs .= ' <a class="btn btn-small make_spec" data-type="vip" data-object="' . $this->grid_object->table_name . '" alt="' . $row_data[$this->grid_object->primary_key]['value'] . '"><i class="icon-star icon-black"></i> ' . Multilanguage::_('GB_BVIP_MAKE', 'system') . '</a>';
            }
        }

        if ( $this->check_column_activity_in_topic('premium_status_end', $row_data) ) {
            if ($current_statuses['premium_status_end'] > time()) {
                $rs .= ' <span class="vb btn btn-small btn-info btn-disabled"><i class="icon-fire icon-black"></i> ' . Multilanguage::_('GB_BPREMIUM_TO', 'system') . ' ' . date('d.m.Y H:i', $current_statuses['premium_status_end']) . '</span>';
            } else {
                $rs .= ' <a class="btn btn-small make_spec" data-type="premium" data-object="' . $this->grid_object->table_name . '" alt="' . $row_data[$this->grid_object->primary_key]['value'] . '"><i class="icon-fire icon-black"></i> ' . Multilanguage::_('GB_BPREMIUM_MAKE', 'system') . '</a>';
            }
        }

        if ( $this->check_column_activity_in_topic('bold_status_end', $row_data) ) {
            if ($current_statuses['bold_status_end'] > time()) {
                $rs .= ' <span class="vb btn btn-small btn-info btn-disabled"><i class="icon-heart icon-black"></i> ' . Multilanguage::_('GB_BBOLD_TO', 'system') . ' ' . date('d.m.Y H:i', $current_statuses['bold_status_end']) . '</span>';
            } else {
                $rs .= ' <a class="btn btn-small make_spec" data-type="bold" data-object="' . $this->grid_object->table_name . '" alt="' . $row_data[$this->grid_object->primary_key]['value'] . '"><i class="icon-heart icon-black"></i> ' . Multilanguage::_('GB_BBOLD_MAKE', 'system') . '</a>';
            }
        }
        if ($this->grid_object->table_name == 'data' && $_SESSION['billing']['upps_left'] > 0 or $_SESSION['billing']['packs_left'] > 0) {
            $rs .= '<a class="btn btn-small go_up" href="' . SITEBILL_MAIN_URL . '/upper/realty' . $row_data['id']['value'] . '/"><i class="icon-arrow-up icon-black"></i> Поднять</a>';
        }


        if (!empty($custom_statuses)) {
            foreach ($custom_statuses as $key => $custom_status) {
                if (strtotime($current_statuses[$custom_status['field_name']]) > time()) {
                    $text = sprintf($custom_status['grid_btn_selected'], date('d.m.Y H:i', strtotime($current_statuses[$custom_status['field_name']])));
                    $rs .= ' <span class="btn btn-small btn-info btn-disabled">' . ($custom_status['faicon_class'] != '' ? '<i class="' . $custom_status['faicon_class'] . '"></i> ' : '') . $text . '</span>';
                } else {
                    $rs .= ' <a class="btn btn-small make_spec" data-type="' . $key . '" data-object="' . $this->grid_object->table_name . '" alt="' . $row_data[$this->grid_object->primary_key]['value'] . '">' . ($custom_status['faicon_class'] != '' ? '<i class="' . $custom_status['faicon_class'] . '"></i> ' : '') . ' ' . $custom_status['grid_btn_title'] . '</a>';
                }
            }
        }


        return $rs;
    }
}
