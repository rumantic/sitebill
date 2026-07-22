<?php
/**
 * FieldContext — Context interface for field type handlers.
 *
 * Provides access to the host object's methods (getRequestValue, getConfigValue, etc.)
 * without coupling handlers to the SiteBill/Data_Model class hierarchy.
 *
 * @package SiteBill\Model\Field
 */

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

interface FieldContext
{
    /**
     * Get a value from the HTTP request.
     *
     * @param string $name Parameter name
     * @return mixed
     */
    public function getRequestValue(string $name);

    /**
     * Get a configuration value.
     *
     * @param string $key Config key
     * @return mixed
     */
    public function getConfigValue(string $key);

    /**
     * Escape a string for safe output/storage.
     *
     * @param string $value
     * @return string
     */
    public function escape(string $value): string;

    /**
     * Decode HTML special characters (recursive).
     *
     * @param mixed $value
     * @return mixed
     */
    public function htmlspecialcharsDecode($value);

    /**
     * Strip HTML tags and decode HTML entities.
     *
     * @param mixed $value
     * @return string
     */
    public function stripAndDecode($value): string;

    /**
     * Get current language code.
     *
     * @return string
     */
    public function getCurrentLang(): string;

    /**
     * Get language postfix for column name.
     *
     * @param string $lang Language code
     * @return string
     */
    public function getLangPostfix(string $lang): string;
}
