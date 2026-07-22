<?php
/**
 * ParameterHandler — Handler for 'parameter' field type.
 *
 * Handles key=value parameter fields that are stored as serialized PHP
 * or JSON depending on the field's 'type' parameter.
 *
 * @package SiteBill\Model\Field
 */

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

require_once __DIR__ . '/AbstractFieldHandler.php';

class ParameterHandler extends AbstractFieldHandler
{
    public function getTypeName(): string
    {
        return 'parameter';
    }

    public function hydrateFromRequest(array &$field, FieldContext $ctx): bool
    {
        $p = $ctx->htmlspecialcharsDecode($ctx->getRequestValue($field['name']));
        $params = [];

        if (is_array($p) && isset($p['name']) && is_array($p['name']) && count($p['name']) > 0) {
            foreach ($p['name'] as $k => $n) {
                $paramname = trim($n);
                $paramvalue = trim($p['value'][$k]);
                if ($paramname != '') {
                    $params[$paramname] = $paramvalue;
                }
            }
        } elseif (is_array($p)) {
            $params = $p;
        }

        $field['value'] = $params;
        return true;
    }

    public function hydrateFromDb(array &$field, array $row, FieldContext $ctx): bool
    {
        $key = $field['name'];
        if (!array_key_exists($key, $row)) {
            return false;
        }
        $raw = $row[$key];
        $parameters = $field['parameters'] ?? [];

        if (isset($parameters['type']) && $parameters['type'] == 'json') {
            $decoded = json_decode($raw, true);
            $field['value'] = is_array($decoded) ? $decoded : [];
        } else {
            $decoded = @unserialize($raw);
            $field['value'] = is_array($decoded) ? $decoded : [];
        }

        return true;
    }

    public function toInsertParts(array $field, FieldContext $ctx): array
    {
        if ($this->isNotable($field)) {
            return $this->skipParts();
        }
        $parameters = $field['parameters'] ?? [];
        $value = $field['value'] ?? [];

        if (isset($parameters['type']) && $parameters['type'] == 'json') {
            $encoded = json_encode($value);
        } else {
            $encoded = serialize($value);
        }

        return $this->simpleInsertParts($field['name'], $encoded);
    }

    public function toEditParts(array $field, FieldContext $ctx): array
    {
        if ($this->isNotable($field)) {
            return ['set' => [], 'values' => []];
        }
        $parameters = $field['parameters'] ?? [];
        $value = $field['value'] ?? [];

        if (isset($parameters['type']) && $parameters['type'] == 'json') {
            $encoded = json_encode($value);
        } else {
            $encoded = serialize($value);
        }

        return $this->simpleEditParts($field['name'], $encoded);
    }
}
