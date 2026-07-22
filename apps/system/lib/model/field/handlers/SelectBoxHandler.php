<?php
/**
 * SelectBoxHandler — Handler for 'select_box' field type.
 *
 * Handles both single and multi-select modes.
 * In multiselect mode, values are joined with commas.
 *
 * @package SiteBill\Model\Field
 */

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

require_once __DIR__ . '/AbstractFieldHandler.php';

class SelectBoxHandler extends AbstractFieldHandler
{
    public function getTypeName(): string
    {
        return 'select_box';
    }

    public function hydrateFromRequest(array &$field, FieldContext $ctx): bool
    {
        $parameters = $field['parameters'] ?? [];

        if (isset($parameters['multiselect']) && 1 == (int)$parameters['multiselect']) {
            $field['values_array'] = (array)$ctx->getRequestValue($field['name']);
            if (is_array($field['values_array']) && count($field['values_array']) != 0) {
                $field['value'] = implode(',', $field['values_array']);
            }
        } else {
            $field['value'] = $ctx->getRequestValue($field['name']);
            if (!is_array($field['value'])) {
                if (isset($field['select_data'][$field['value']])) {
                    $field['value_string'] = $field['select_data'][$field['value']];
                } else {
                    $field['value_string'] = '';
                }
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
            $value = implode(',', $value);
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
            $value = implode(',', $value);
        }
        $value = preg_replace('/<script.*\/script>/', '', $value);
        return $this->simpleEditParts($field['name'], $ctx->escape($value));
    }
}
