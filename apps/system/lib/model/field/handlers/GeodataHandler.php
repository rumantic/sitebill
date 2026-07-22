<?php
/**
 * GeodataHandler — Handler for 'geodata' field type.
 *
 * @package SiteBill\Model\Field
 */

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

require_once __DIR__ . '/AbstractFieldHandler.php';

class GeodataHandler extends AbstractFieldHandler
{
    public function getTypeName(): string
    {
        return 'geodata';
    }

    public function hydrateFromRequest(array &$field, FieldContext $ctx): bool
    {
        $name = $field['name'];
        $geodata = $ctx->getRequestValue($name);
        $field['value'] = ['lat' => '', 'lng' => ''];

        if (!is_null($geodata) && is_array($geodata)) {
            if (isset($geodata['lat']) && preg_match('/^(-?)([0-9]?)([0-9])((\.?)(\d*)?)$/', trim($geodata['lat']))) {
                $field['value']['lat'] = trim($geodata['lat']);
            }
            if (isset($geodata['lng']) && preg_match('/^(-?)([0-9]?)([0-9]?)([0-9])((\.?)(\d*)?)$/', trim($geodata['lng']))) {
                $field['value']['lng'] = trim($geodata['lng']);
            }
        }

        return true;
    }

    public function hydrateFromDb(array &$field, array $row, FieldContext $ctx): bool
    {
        $key = $field['name'];
        $field['value'] = [
            'lat' => $row[$key . '_lat'] ?? '',
            'lng' => $row[$key . '_lng'] ?? '',
        ];
        return true;
    }

    public function toInsertParts(array $field, FieldContext $ctx): array
    {
        $key = $field['name'];
        $columns = [];
        $values = [];

        $lat = $field['value']['lat'] ?? '';
        $lng = $field['value']['lng'] ?? '';

        if ($lat !== '') {
            $columns[] = '`' . $key . '_lat`';
            $values[] = $ctx->escape($lat);
        }
        if ($lng !== '') {
            $columns[] = '`' . $key . '_lng`';
            $values[] = $ctx->escape($lng);
        }

        return ['columns' => $columns, 'values' => $values];
    }

    public function toEditParts(array $field, FieldContext $ctx): array
    {
        $key = $field['name'];
        $setParts = [];
        $values = [];

        $lat = $field['value']['lat'] ?? '';
        $lng = $field['value']['lng'] ?? '';

        if ($lat !== '') {
            $setParts[] = '`' . $key . '_lat`=?';
            $values[] = $ctx->escape($lat);
        } else {
            $setParts[] = '`' . $key . '_lat`=NULL';
        }

        if ($lng !== '') {
            $setParts[] = '`' . $key . '_lng`=?';
            $values[] = $ctx->escape($lng);
        } else {
            $setParts[] = '`' . $key . '_lng`=NULL';
        }

        return ['set' => $setParts, 'values' => $values];
    }

    public function validate(array $field, FieldContext $ctx): ?string
    {
        if (isset($field['required']) && $field['required'] === 'on') {
            $lat = $field['value']['lat'] ?? '';
            $lng = $field['value']['lng'] ?? '';
            if ($lat === '' || $lng === '') {
                $title = $field['title'] ?? $field['name'];
                return 'Поле "' . $title . '" обязательно для заполнения';
            }
        }
        return null;
    }
}
