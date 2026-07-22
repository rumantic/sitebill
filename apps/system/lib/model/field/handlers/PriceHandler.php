<?php
/**
 * PriceHandler — Handler for 'price' field type.
 *
 * @package SiteBill\Model\Field
 */

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

require_once __DIR__ . '/AbstractFieldHandler.php';

class PriceHandler extends AbstractFieldHandler
{
    public function getTypeName(): string
    {
        return 'price';
    }

    public function hydrateFromRequest(array &$field, FieldContext $ctx): bool
    {
        $name = $field['name'];
        $rawValue = $ctx->getRequestValue($name);
        // Strip everything except digits, dots, and commas
        $field['value'] = preg_replace('/[^0-9.,]/', '', (string)$rawValue);
        return true;
    }

    public function toInsertParts(array $field, FieldContext $ctx): array
    {
        if ($this->isNotable($field)) {
            return $this->skipParts();
        }
        $value = $field['value'] ?? 0;
        // Normalize: remove commas, ensure numeric
        $value = str_replace(',', '.', (string)$value);
        return $this->simpleInsertParts($field['name'], $ctx->escape($value));
    }

    public function toEditParts(array $field, FieldContext $ctx): array
    {
        if ($this->isNotable($field)) {
            return ['set' => [], 'values' => []];
        }
        $value = $field['value'] ?? 0;
        $value = str_replace(',', '.', (string)$value);
        return $this->simpleEditParts($field['name'], $ctx->escape($value));
    }
}
