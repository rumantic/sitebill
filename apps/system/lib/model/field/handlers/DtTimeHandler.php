<?php
/**
 * DtTimeHandler — Handler for 'dttime' field type.
 *
 * Handles time-only fields with '0000-00-00 H:i:s' format.
 *
 * @package SiteBill\Model\Field
 */

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

require_once __DIR__ . '/AbstractFieldHandler.php';

class DtTimeHandler extends AbstractFieldHandler
{
    public function getTypeName(): string
    {
        return 'dttime';
    }

    public function hydrateFromRequest(array &$field, FieldContext $ctx): bool
    {
        $val = $ctx->stripAndDecode($ctx->getRequestValue($field['name']));

        if ($val == '' && ($field['value'] ?? '') == 'now') {
            $val = date('0000-00-00 H:i:s', time());
        } else {
            if (class_exists('Sitebill_Datetime')) {
                $val = Sitebill_Datetime::getTimeCanonicalFromFormat($val);
            }
        }

        $field['value'] = $val;
        return true;
    }

    public function toInsertParts(array $field, FieldContext $ctx): array
    {
        if ($this->isNotable($field)) {
            return $this->skipParts();
        }
        return $this->simpleInsertParts($field['name'], $field['value']);
    }

    public function toEditParts(array $field, FieldContext $ctx): array
    {
        if ($this->isNotable($field)) {
            return ['set' => [], 'values' => []];
        }
        return $this->simpleEditParts($field['name'], $field['value']);
    }
}
