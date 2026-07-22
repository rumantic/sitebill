<?php
/**
 * FieldTypeRegistry — Registry of field type handlers.
 *
 * Manages FieldTypeHandler instances by type name.
 * Falls through to legacy code for unregistered types.
 *
 * @package SiteBill\Model\Field
 */

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

require_once __DIR__ . '/FieldTypeHandler.php';
require_once __DIR__ . '/FieldContext.php';

class FieldTypeRegistry
{
    /** @var FieldTypeHandler[] type_name => handler */
    private $handlers = [];

    /** @var FieldTypeRegistry|null Singleton */
    private static $instance = null;

    /**
     * Get singleton instance with default handlers registered.
     *
     * @return FieldTypeRegistry
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance->registerDefaults();
        }
        return self::$instance;
    }

    /**
     * Register a handler for a field type.
     *
     * @param FieldTypeHandler $handler
     * @return void
     */
    public function register(FieldTypeHandler $handler): void
    {
        $this->handlers[$handler->getTypeName()] = $handler;
    }

    /**
     * Check if a handler exists for the given type.
     *
     * @param string $type
     * @return bool
     */
    public function has(string $type): bool
    {
        return isset($this->handlers[$type]);
    }

    /**
     * Get the handler for a field type.
     *
     * @param string $type
     * @return FieldTypeHandler|null
     */
    public function get(string $type): ?FieldTypeHandler
    {
        return $this->handlers[$type] ?? null;
    }

    /**
     * Get all registered type names.
     *
     * @return string[]
     */
    public function getRegisteredTypes(): array
    {
        return array_keys($this->handlers);
    }

    /**
     * Register the default set of handlers.
     */
    private function registerDefaults(): void
    {
        $dir = __DIR__ . '/handlers/';
        if (!is_dir($dir)) {
            return;
        }
        $files = glob($dir . '*Handler.php');
        foreach ($files as $file) {
            require_once $file;
            $class = basename($file, '.php');
            if (class_exists($class) && !($class === 'AbstractFieldHandler')) {
                $handler = new $class();
                if ($handler instanceof FieldTypeHandler) {
                    $this->register($handler);
                }
            }
        }
    }
}
