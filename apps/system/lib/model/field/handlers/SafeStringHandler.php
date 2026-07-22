<?php
/**
 * SafeStringHandler — Handler for 'safe_string' field type.
 *
 * @package SiteBill\Model\Field
 */

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

require_once __DIR__ . '/AbstractFieldHandler.php';

class SafeStringHandler extends AbstractFieldHandler
{
    public function getTypeName(): string
    {
        return 'safe_string';
    }

    public function hydrateFromRequest(array &$field, FieldContext $ctx): bool
    {
        $name = $field['name'];
        $rawValue = $ctx->getRequestValue($name);

        if (!is_array($rawValue)) {
            $sval = $ctx->stripAndDecode($rawValue);

            // Apply rules if defined
            $parameters = $field['parameters'] ?? [];
            if (isset($parameters['rules']) && $parameters['rules'] != '') {
                $rules = $this->parseRules($parameters['rules']);
                $type = $rules['Type'] ?? 'string';
                if ($type === 'decimal' && $sval !== '') {
                    $sval = str_replace(',', '.', $sval);
                }
            }

            $field['value'] = $sval;
        } else {
            $xvalue = $rawValue;
            if (!empty($xvalue)) {
                foreach ($xvalue as $xk => $xv) {
                    $xvalue[$xk] = strip_tags(htmlspecialchars_decode($xv));
                }
            }
            $field['value'] = $xvalue;
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

    /**
     * Parse rules string like "Type:decimal,MaxLength:50" into associative array.
     *
     * @param string $rulesString
     * @return array
     */
    private function parseRules(string $rulesString): array
    {
        $rules = [];
        $parts = explode(',', $rulesString);
        foreach ($parts as $part) {
            $x = explode(':', trim($part));
            $rules[trim($x[0])] = isset($x[1]) ? trim($x[1]) : '';
        }
        return $rules;
    }
}
