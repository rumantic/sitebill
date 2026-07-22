<?php
/**
 * CheckboxHandler — Handler for 'checkbox' field type.
 *
 * @package SiteBill\Model\Field
 */

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

require_once __DIR__ . '/AbstractFieldHandler.php';

class CheckboxHandler extends AbstractFieldHandler
{
    public function getTypeName(): string
    {
        return 'checkbox';
    }

    public function hydrateFromRequest(array &$field, FieldContext $ctx): bool
    {
        $name = $field['name'];
        $rawValue = $ctx->getRequestValue($name);

        if ($rawValue !== null && intval($rawValue) !== 0) {
            $field['value'] = 1;
        } else {
            $field['value'] = 0;
        }

        return true;
    }

    public function toInsertParts(array $field, FieldContext $ctx): array
    {
        if ($this->isNotable($field)) {
            return $this->skipParts();
        }
        return $this->simpleInsertParts($field['name'], (int)($field['value'] ?? 0));
    }

    public function toEditParts(array $field, FieldContext $ctx): array
    {
        if ($this->isNotable($field)) {
            return ['set' => [], 'values' => []];
        }
        return $this->simpleEditParts($field['name'], (int)($field['value'] ?? 0));
    }

    public function validate(array $field, FieldContext $ctx): ?string
    {
        // Checkboxes are always valid (0 or 1)
        return null;
    }
}
