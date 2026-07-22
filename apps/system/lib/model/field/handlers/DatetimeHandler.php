<?php
/**
 * DatetimeHandler — Handler for legacy 'datetime' field type.
 *
 * Uses Sitebill_Datetime::getDatetimeCanonicalFromFormat() to produce
 * canonical datetime representation for both hydration and SQL.
 *
 * @package SiteBill\Model\Field
 */

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

require_once __DIR__ . '/AbstractFieldHandler.php';

class DatetimeHandler extends AbstractFieldHandler
{
    public function getTypeName(): string
    {
        return 'datetime';
    }

    public function hydrateFromRequest(array &$field, FieldContext $ctx): bool
    {
        $field['value'] = $ctx->stripAndDecode($ctx->getRequestValue($field['name']));
        return true;
    }

    public function toInsertParts(array $field, FieldContext $ctx): array
    {
        if ($this->isNotable($field)) {
            return $this->skipParts();
        }
        $parameters = $field['parameters'] ?? [];
        $canonical = Sitebill_Datetime::getDatetimeCanonicalFromFormat($field['value'], $parameters);
        return $this->simpleInsertParts($field['name'], $canonical);
    }

    public function toEditParts(array $field, FieldContext $ctx): array
    {
        if ($this->isNotable($field)) {
            return ['set' => [], 'values' => []];
        }
        $parameters = $field['parameters'] ?? [];
        $canonical = Sitebill_Datetime::getDatetimeCanonicalFromFormat($field['value'], $parameters);
        return $this->simpleEditParts($field['name'], $canonical);
    }
}
