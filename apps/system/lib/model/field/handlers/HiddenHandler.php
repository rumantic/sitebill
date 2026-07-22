<?php
/**
 * HiddenHandler — Handler for 'hidden' field type.
 *
 * Hidden fields behave like safe_string but without rules processing.
 *
 * @package SiteBill\Model\Field
 */

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

require_once __DIR__ . '/AbstractFieldHandler.php';

class HiddenHandler extends AbstractFieldHandler
{
    public function getTypeName(): string
    {
        return 'hidden';
    }

    public function hydrateFromRequest(array &$field, FieldContext $ctx): bool
    {
        $name = $field['name'];
        $rawValue = $ctx->getRequestValue($name);

        if ($rawValue !== null) {
            if (!is_array($rawValue)) {
                $field['value'] = $ctx->stripAndDecode($rawValue);
            } else {
                $field['value'] = $rawValue;
            }
        }

        return true;
    }

    public function toInsertParts(array $field, FieldContext $ctx): array
    {
        if ($this->isNotable($field)) {
            return $this->skipParts();
        }
        $value = $field['value'] ?? '';
        if (is_array($value)) {
            $value = '';
        }
        $value = preg_replace('/<script.*\/script>/', '', $value);
        return $this->simpleInsertParts($field['name'], $ctx->escape($value));
    }

    public function toEditParts(array $field, FieldContext $ctx): array
    {
        if ($this->isNotable($field)) {
            return ['set' => [], 'values' => []];
        }
        $value = $field['value'] ?? '';
        if (is_array($value)) {
            $value = '';
        }
        $value = preg_replace('/<script.*\/script>/', '', $value);
        return $this->simpleEditParts($field['name'], $ctx->escape($value));
    }
}
