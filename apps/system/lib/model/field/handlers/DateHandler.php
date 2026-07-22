<?php
/**
 * DateHandler — Handler for legacy 'date' field type.
 *
 * Supports 'date', 'datetime', and default (unix timestamp) format types
 * via the 'formattype' parameter.
 *
 * @package SiteBill\Model\Field
 */

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

require_once __DIR__ . '/AbstractFieldHandler.php';

class DateHandler extends AbstractFieldHandler
{
    public function getTypeName(): string
    {
        return 'date';
    }

    public function hydrateFromRequest(array &$field, FieldContext $ctx): bool
    {
        $raw = $ctx->getRequestValue($field['name']);
        $parameters = $field['parameters'] ?? [];

        if (isset($parameters['formattype']) && $parameters['formattype'] == 'date') {
            $field['value'] = date('Y-m-d', strtotime($raw));
        } elseif (isset($parameters['formattype']) && $parameters['formattype'] == 'datetime') {
            $field['value'] = date('Y-m-d H:i:s', strtotime($raw));
        } else {
            $field['value'] = strtotime($raw);
        }

        return true;
    }

    public function toInsertParts(array $field, FieldContext $ctx): array
    {
        if ($this->isNotable($field)) {
            return $this->skipParts();
        }
        $value = $field['value'] ?? '';
        $value = preg_replace('/<script.*\/script>/', '', (string)$value);
        return $this->simpleInsertParts($field['name'], $ctx->escape((string)$value));
    }

    public function toEditParts(array $field, FieldContext $ctx): array
    {
        if ($this->isNotable($field)) {
            return ['set' => [], 'values' => []];
        }
        $value = $field['value'] ?? '';
        $value = preg_replace('/<script.*\/script>/', '', (string)$value);
        return $this->simpleEditParts($field['name'], $ctx->escape((string)$value));
    }
}
