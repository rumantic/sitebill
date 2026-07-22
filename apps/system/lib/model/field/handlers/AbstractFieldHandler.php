<?php
/**
 * AbstractFieldHandler — Base class for field type handlers.
 *
 * Provides common helper methods for insert/edit SQL generation and validation.
 *
 * @package SiteBill\Model\Field
 */

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

require_once __DIR__ . '/../FieldTypeHandler.php';
require_once __DIR__ . '/../FieldContext.php';

abstract class AbstractFieldHandler implements FieldTypeHandler
{
    /**
     * Default insert parts: one column, one value placeholder.
     *
     * @param string $key Column name
     * @param mixed $value Column value
     * @return array
     */
    protected function simpleInsertParts(string $key, $value): array
    {
        return [
            'columns' => ['`' . $key . '`'],
            'values' => [$value],
        ];
    }

    /**
     * Default edit parts: one SET clause, one value.
     *
     * @param string $key Column name
     * @param mixed $value Column value
     * @return array
     */
    protected function simpleEditParts(string $key, $value): array
    {
        return [
            'set' => ['`' . $key . '`=?'],
            'values' => [$value],
        ];
    }

    /**
     * Return empty parts (skip this field in query).
     *
     * @return array
     */
    protected function skipParts(): array
    {
        return ['columns' => [], 'values' => []];
    }

    /**
     * Check if a field should be skipped due to dbtype=notable.
     *
     * @param array $field
     * @return bool
     */
    protected function isNotable(array $field): bool
    {
        return isset($field['dbtype']) && ($field['dbtype'] === 'notable' || $field['dbtype'] === '0');
    }

    /**
     * Default validation: check required fields.
     *
     * @param array $field
     * @param FieldContext $ctx
     * @return string|null
     */
    public function validate(array $field, FieldContext $ctx): ?string
    {
        if (isset($field['required']) && $field['required'] === 'on') {
            $value = $field['value'] ?? '';
            if ($value === '' || $value === null) {
                $title = $field['title'] ?? $field['name'];
                return 'Поле "' . $title . '" обязательно для заполнения';
            }
        }
        return null;
    }

    /**
     * Default hydrateFromDb: read from row by field name.
     *
     * @param array &$field
     * @param array $row
     * @param FieldContext $ctx
     * @return bool
     */
    public function hydrateFromDb(array &$field, array $row, FieldContext $ctx): bool
    {
        $key = $field['name'];
        if (array_key_exists($key, $row)) {
            $field['value'] = $row[$key];
            return true;
        }
        return false;
    }
}
