<?php
/**
 * TextareaHandler — Handler for 'textarea' field type.
 *
 * @package SiteBill\Model\Field
 */

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

require_once __DIR__ . '/AbstractFieldHandler.php';

class TextareaHandler extends AbstractFieldHandler
{
    public function getTypeName(): string
    {
        return 'textarea';
    }

    public function hydrateFromRequest(array &$field, FieldContext $ctx): bool
    {
        $name = $field['name'];
        $parameters = $field['parameters'] ?? [];

        if (is_array($parameters) && ((isset($parameters['allow_htmltags']) && (int)$parameters['allow_htmltags'] == 1) || (isset($parameters['html']) && (int)$parameters['html'] == 1))) {
            // Allow HTML tags
            $field['value'] = $ctx->htmlspecialcharsDecode($ctx->getRequestValue($name));
        } elseif (is_array($parameters) && !empty($parameters['serialize_array']) && $parameters['serialize_array'] == 1) {
            // Serialize array from delimiter
            $rawValue = $ctx->getRequestValue($name);
            if ($rawValue != '') {
                $delimiter = $ctx->getConfigValue('apps.excel.images_delimiter');
                $exploded = explode($delimiter, $rawValue);
                if (is_array($exploded) && count($exploded) > 0) {
                    $field['value'] = serialize($exploded);
                }
            }
        } elseif (is_array($parameters) && !empty($parameters['structure_chain']) && $parameters['structure_chain'] == 1) {
            // Structure chain lookup
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/excelfree/admin/data_manager_export.php';
            $dme = new Data_Manager_Export();
            $field['value'] = $dme->getTopicIdFromChain($ctx->getRequestValue($name));
        } else {
            // Plain text — strip tags
            $field['value'] = $ctx->stripAndDecode($ctx->getRequestValue($name));
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
        return $this->simpleInsertParts($field['name'], $ctx->escape($value));
    }

    public function toEditParts(array $field, FieldContext $ctx): array
    {
        if ($this->isNotable($field)) {
            return ['set' => [], 'values' => []];
        }
        $value = $field['value'] ?? '';
        $value = preg_replace('/<script.*\/script>/', '', (string)$value);
        return $this->simpleEditParts($field['name'], $ctx->escape($value));
    }
}
