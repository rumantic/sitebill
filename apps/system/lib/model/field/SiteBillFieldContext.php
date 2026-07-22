<?php
/**
 * SiteBillFieldContext — Adapter bridging SiteBill/Data_Model to FieldContext interface.
 *
 * Wraps the host object's methods so FieldTypeHandler implementations
 * can access them without direct coupling to the SiteBill class hierarchy.
 *
 * @package SiteBill\Model\Field
 */

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

require_once __DIR__ . '/FieldContext.php';

class SiteBillFieldContext implements FieldContext
{
    /** @var object The host object (SiteBill, Data_Model, etc.) */
    private $host;

    /**
     * @param object $host Any object with getRequestValue, getConfigValue, escape, etc.
     */
    public function __construct($host)
    {
        $this->host = $host;
    }

    public function getRequestValue(string $name)
    {
        return $this->host->getRequestValue($name);
    }

    public function getConfigValue(string $key)
    {
        return $this->host->getConfigValue($key);
    }

    public function escape(string $value): string
    {
        return $this->host->escape($value);
    }

    public function htmlspecialcharsDecode($value)
    {
        return $this->host->htmlspecialchars_decode($value);
    }

    public function stripAndDecode($value): string
    {
        return strip_tags($this->host->htmlspecialchars_decode($value));
    }

    public function getCurrentLang(): string
    {
        if (method_exists($this->host, 'getCurrentLang')) {
            return (string)$this->host->getCurrentLang();
        }
        return '';
    }

    public function getLangPostfix(string $lang): string
    {
        if (method_exists($this->host, 'getLangPostfix')) {
            return (string)$this->host->getLangPostfix($lang);
        }
        return '_' . $lang;
    }
}
