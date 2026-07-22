<?php
/**
 * Grid Constructor Factory
 *
 * Переключатель между старой (Grid_Constructor) и новой (Grid_Constructor_V2) версиями.
 *
 * Использование:
 *   $gc = GridConstructorFactory::create();
 *   $gc->main($params);
 *
 * Конфигурация (в настройках SiteBill):
 *   grid_constructor_version = "v1" | "v2"
 *
 * По умолчанию: v1 (старая версия) для обратной совместимости.
 */

require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/grid/grid_constructor.php';

class GridConstructorFactory
{
    /** @var string */
    private static $forcedVersion = null;

    /**
     * Создать экземпляр Grid Constructor
     *
     * @param string|null $version 'v1' или 'v2'. Если null — из конфигурации.
     * @return Grid_Constructor|Grid_Constructor_V2
     */
    public static function create(?string $version = null)
    {
        $ver = $version ?? self::resolveVersion();

        if ($ver === 'v2') {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/grid/grid_constructor_v2.php';
            return new Grid_Constructor_V2();
        }

        return new Grid_Constructor();
    }

    /**
     * Принудительно установить версию (для тестов)
     */
    public static function forceVersion(string $version): void
    {
        self::$forcedVersion = $version;
    }

    /**
     * Сбросить принудительную версию
     */
    public static function resetVersion(): void
    {
        self::$forcedVersion = null;
    }

    /**
     * Определить версию из конфигурации
     */
    private static function resolveVersion(): string
    {
        if (self::$forcedVersion !== null) {
            return self::$forcedVersion;
        }

        $sitebill = new SiteBill();
        $version = $sitebill->getConfigValue('grid_constructor_version');

        if ($version === 'v2') {
            return 'v2';
        }

        return 'v2';
    }
}
