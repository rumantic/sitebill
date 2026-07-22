<?php
/**
 * FieldTypeHandler — Interface for field type strategies.
 *
 * Each field type (safe_string, checkbox, price, geodata, etc.)
 * implements this interface to handle:
 * - Hydration from HTTP request
 * - Hydration from DB row
 * - SQL query generation (insert/edit parts)
 * - Validation
 *
 * This replaces the massive if/elseif chains in DataInitTrait, CrudQueryTrait,
 * and DataValidationTrait.
 *
 * @package SiteBill\Model\Field
 */

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

interface FieldTypeHandler
{
    /**
     * Hydrate a field's value from HTTP request data.
     *
     * Modifies $field in-place (sets $field['value'], etc.).
     *
     * @param array &$field The field definition from model_array (by reference)
     * @param FieldContext $ctx Access to getRequestValue, getConfigValue, etc.
     * @return bool True if handled, false to fall through to legacy code
     */
    public function hydrateFromRequest(array &$field, FieldContext $ctx): bool;

    /**
     * Hydrate a field's value from a database row.
     *
     * @param array &$field The field definition
     * @param array $row The database row (column => value)
     * @param FieldContext $ctx
     * @return bool True if handled
     */
    public function hydrateFromDb(array &$field, array $row, FieldContext $ctx): bool;

    /**
     * Get SQL parts for prepared INSERT query.
     *
     * @param array $field The field definition (with value already set)
     * @param FieldContext $ctx
     * @return array ['columns' => string[], 'values' => mixed[]] or empty array to skip
     */
    public function toInsertParts(array $field, FieldContext $ctx): array;

    /**
     * Get SQL parts for prepared UPDATE query.
     *
     * @param array $field The field definition (with value already set)
     * @param FieldContext $ctx
     * @return array ['set' => string[], 'values' => mixed[]] or empty array to skip
     */
    public function toEditParts(array $field, FieldContext $ctx): array;

    /**
     * Validate the field value.
     *
     * @param array $field The field definition
     * @param FieldContext $ctx
     * @return string|null Error message or null if valid
     */
    public function validate(array $field, FieldContext $ctx): ?string;

    /**
     * Get the type name this handler manages.
     *
     * @return string
     */
    public function getTypeName(): string;
}
