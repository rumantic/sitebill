<?php
/**
 * TlocationHandler — Handler for 'tlocation' field type.
 *
 * Tree-location fields store multiple FK columns (country_id, region_id,
 * city_id, district_id, street_id) from a single compound field.
 * The 'visibles' parameter filters which columns to persist.
 *
 * Note: tlocation fields have dbtype='notable' but their sub-columns
 * ARE stored in the database table directly.
 *
 * @package SiteBill\Model\Field
 */

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

require_once __DIR__ . '/AbstractFieldHandler.php';

class TlocationHandler extends AbstractFieldHandler
{
    /**
     * Standard tlocation sub-keys.
     */
    private const SUB_KEYS = ['country_id', 'region_id', 'city_id', 'district_id', 'street_id'];

    public function getTypeName(): string
    {
        return 'tlocation';
    }

    public function hydrateFromRequest(array &$field, FieldContext $ctx): bool
    {
        foreach (self::SUB_KEYS as $subKey) {
            $field['value'][$subKey] = (int)$ctx->getRequestValue($subKey);
        }
        return true;
    }

    public function hydrateFromDb(array &$field, array $row, FieldContext $ctx): bool
    {
        foreach (self::SUB_KEYS as $subKey) {
            $field['value'][$subKey] = isset($row[$subKey]) ? (int)$row[$subKey] : 0;
        }
        return true;
    }

    public function toInsertParts(array $field, FieldContext $ctx): array
    {
        $visibles = $this->getVisibles($field);
        $columns = [];
        $values = [];

        if (!empty($field['value'])) {
            foreach ($field['value'] as $k => $v) {
                if (!empty($visibles)) {
                    if (in_array($k, $visibles)) {
                        $columns[] = '`' . $k . '`';
                        $values[] = (int)$v;
                    }
                } else {
                    $columns[] = '`' . $k . '`';
                    $values[] = (int)$v;
                }
            }
        }

        return ['columns' => $columns, 'values' => $values];
    }

    public function toEditParts(array $field, FieldContext $ctx): array
    {
        $visibles = $this->getVisibles($field);
        $setParts = [];
        $values = [];

        if (!empty($field['value'])) {
            foreach ($field['value'] as $k => $v) {
                if (!empty($visibles)) {
                    if (in_array($k, $visibles)) {
                        $setParts[] = '`' . $k . '`=?';
                        $values[] = (int)$v;
                    }
                } else {
                    $setParts[] = '`' . $k . '`=?';
                    $values[] = (int)$v;
                }
            }
        }

        return ['set' => $setParts, 'values' => $values];
    }

    /**
     * Extract visible columns from field parameters.
     *
     * @param array $field
     * @return array
     */
    private function getVisibles(array $field): array
    {
        if (isset($field['parameters']['visibles'])) {
            return explode('|', $field['parameters']['visibles']);
        }
        return [];
    }
}
