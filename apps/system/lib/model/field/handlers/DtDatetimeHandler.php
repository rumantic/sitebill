<?php
/**
 * DtDatetimeHandler — Handler for 'dtdatetime' field type.
 *
 * @package SiteBill\Model\Field
 */

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

require_once __DIR__ . '/AbstractFieldHandler.php';

class DtDatetimeHandler extends AbstractFieldHandler
{
    public function getTypeName(): string
    {
        return 'dtdatetime';
    }

    public function hydrateFromRequest(array &$field, FieldContext $ctx): bool
    {
        $name = $field['name'];
        $val = $ctx->stripAndDecode($ctx->getRequestValue($name));

        if ($val == '' && ($field['value'] ?? '') == 'now') {
            $val = date('Y-m-d H:i:s', time());
        } else {
            if (preg_match('/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/', $val)) {
                // Already canonical
            } elseif (preg_match('/^\d\d\d\d-\d\d-\d\d$/', $val)) {
                $val .= ' 00:00:00';
            } else {
                if (class_exists('Sitebill_Datetime')) {
                    $val = Sitebill_Datetime::getDatetimeCanonicalFromFormat($val);
                }
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
        return $this->simpleInsertParts($field['name'], $field['value'] ?? '');
    }

    public function toEditParts(array $field, FieldContext $ctx): array
    {
        if ($this->isNotable($field)) {
            return ['set' => [], 'values' => []];
        }
        return $this->simpleEditParts($field['name'], $field['value'] ?? '');
    }
}
