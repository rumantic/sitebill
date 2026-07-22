<?php
/**
 * MobilephoneHandler — Handler for 'mobilephone' field type.
 *
 * @package SiteBill\Model\Field
 */

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

require_once __DIR__ . '/AbstractFieldHandler.php';

class MobilephoneHandler extends AbstractFieldHandler
{
    public function getTypeName(): string
    {
        return 'mobilephone';
    }

    public function hydrateFromRequest(array &$field, FieldContext $ctx): bool
    {
        $name = $field['name'];
        $rawValue = $ctx->getRequestValue($name);
        // Strip everything except digits
        $field['value'] = preg_replace('/\D/', '', (string)$rawValue);
        return true;
    }

    public function toInsertParts(array $field, FieldContext $ctx): array
    {
        if ($this->isNotable($field)) {
            return $this->skipParts();
        }
        $value = $field['value'] ?? '';
        return $this->simpleInsertParts($field['name'], $ctx->escape((string)$value));
    }

    public function toEditParts(array $field, FieldContext $ctx): array
    {
        if ($this->isNotable($field)) {
            return ['set' => [], 'values' => []];
        }
        $value = $field['value'] ?? '';
        return $this->simpleEditParts($field['name'], $ctx->escape((string)$value));
    }
}
