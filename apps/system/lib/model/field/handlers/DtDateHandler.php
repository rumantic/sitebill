<?php
/**
 * DtDateHandler — Handler for 'dtdate' field type.
 *
 * @package SiteBill\Model\Field
 */

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

require_once __DIR__ . '/AbstractFieldHandler.php';

class DtDateHandler extends AbstractFieldHandler
{
    public function getTypeName(): string
    {
        return 'dtdate';
    }

    public function hydrateFromRequest(array &$field, FieldContext $ctx): bool
    {
        $name = $field['name'];
        $val = $ctx->stripAndDecode($ctx->getRequestValue($name));

        if ($val == '' && ($field['value'] ?? '') == 'now') {
            $val = date('Y-m-d 00:00:00', time());
        } else {
            if (preg_match('/^\d\d\d\d-\d\d-\d\d$/', $val)) {
                $val .= ' 00:00:00';
            } else {
                if (class_exists('Sitebill_Datetime')) {
                    $val = Sitebill_Datetime::getDateCanonicalFromFormat($val);
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
