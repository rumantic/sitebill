<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * API_data — API для работы с объектами (таблица data).
 *
 * Методы:
 *   do=counts — вернуть счётчики всех вкладок меню одним запросом.
 */
class API_data extends API_Common
{
    /**
     * GET /apps/api/rest.php?action=data&do=counts
     *
     * Возвращает счётчики всех вкладок верхнего меню одним вызовом.
     * Перед каждым запросом проверяется наличие поля в динамической модели
     * (аналогично DataMenuTrait / DataCountTrait):
     *   - billing-счётчики — только если billing включён в конфиге
     *   - archived       — только если поле присутствует в модели
     *   - hot            — только если поле присутствует в модели
     */
    public function _counts()
    {
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php';

        $DBC    = DBC::getInstance();
        $prefix = DB_PREFIX;

        // Загружаем модель — единственный источник правды о полях таблицы
        $dataModelObj = new Data_Model();
        $model = $dataModelObj->get_kvartira_model(false);
        $fields = $model['data'] ?? [];

        $usePredel   = 1 === (int) $this->getConfigValue('apps.realty.use_predeleting');
        $billingMode = file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/billing/lib/billing.php')
                       && 1 == $this->getConfigValue('apps.billing.enable');

        $q = function (string $sql) use ($DBC): int {
            if ($this->getConfigValue('query_cache_enable')) {
                $cacheKey = 'data_cnt_' . md5($sql);
                $stmt = $DBC->query(
                    "SELECT `value` FROM " . DB_PREFIX . "_cache WHERE `parameter`=? AND `valid_for`>?",
                    [$cacheKey, time()]
                );
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    if ($ar !== false && isset($ar['value'])) {
                        return (int) $ar['value'];
                    }
                }
            }
            $stmt = $DBC->query($sql);
            $total = 0;
            if ($stmt) {
                $ar    = $DBC->fetch($stmt);
                $total = (int) ($ar['total'] ?? 0);
            }
            if ($this->getConfigValue('query_cache_enable')) {
                $cacheKey = 'data_cnt_' . md5($sql);
                $ttl = (int) $this->getConfigValue('query_cache_time') ?: 600;
                $DBC->query(
                    "INSERT INTO " . DB_PREFIX . "_cache (`parameter`,`value`,`created_at`,`valid_for`) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`),`created_at`=VALUES(`created_at`),`valid_for`=VALUES(`valid_for`)",
                    [$cacheKey, $total, time(), time() + $ttl]
                );
            }
            return $total;
        };

        $now = time();
        $counts = [];

        // all
        $counts['all'] = $q("SELECT COUNT(id) AS total FROM {$prefix}_data");

        // active
        if ($usePredel && isset($fields['archived'])) {
            $counts['active'] = $q("SELECT COUNT(id) AS total FROM {$prefix}_data WHERE active=1 AND archived=0");
        } else {
            $counts['active'] = $q("SELECT COUNT(id) AS total FROM {$prefix}_data WHERE active=1");
        }

        // notactive
        if ($usePredel && isset($fields['archived'])) {
            $counts['notactive'] = $q("SELECT COUNT(id) AS total FROM {$prefix}_data WHERE active=0 AND archived=0");
        } else {
            $counts['notactive'] = $q("SELECT COUNT(id) AS total FROM {$prefix}_data WHERE active=0");
        }

        // hot — только если поле есть в модели
        if (isset($fields['hot'])) {
            $counts['hot'] = $q("SELECT COUNT(id) AS total FROM {$prefix}_data WHERE hot=1");
        }

        // archived — только если поле есть в модели и use_predeleting включён
        if ($usePredel && isset($fields['archived'])) {
            $counts['archived'] = $q("SELECT COUNT(id) AS total FROM {$prefix}_data WHERE archived=1");
        }

        // billing-статусы — только если модуль billing включён
        if ($billingMode) {
            if (isset($fields['vip_status_end'])) {
                $counts['vip'] = $q("SELECT COUNT(id) AS total FROM {$prefix}_data WHERE vip_status_end<>0 AND vip_status_end>={$now}");
            }
            if (isset($fields['premium_status_end'])) {
                $counts['premium'] = $q("SELECT COUNT(id) AS total FROM {$prefix}_data WHERE premium_status_end<>0 AND premium_status_end>={$now}");
            }
            if (isset($fields['bold_status_end'])) {
                $counts['bold'] = $q("SELECT COUNT(id) AS total FROM {$prefix}_data WHERE bold_status_end<>0 AND bold_status_end>={$now}");
            }
        }

        // CRM-статусы — только если поле status_id есть в модели
        if (isset($fields['status_id'])) {
            $counts['free']      = $q("SELECT COUNT(id) AS total FROM {$prefix}_data WHERE status_id='free'");
            $counts['no_answer'] = $q("SELECT COUNT(id) AS total FROM {$prefix}_data WHERE status_id='no_answer'");
            $counts['call']      = $q("SELECT COUNT(id) AS total FROM {$prefix}_data WHERE status_id='call'");
            $counts['actual']    = $q("SELECT COUNT(id) AS total FROM {$prefix}_data WHERE status_id='actual'");
        }

        // realtylogv2 архив — отдельная таблица событий удаления
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/realtylogv2/admin/admin.php')) {
            $counts['realtylogv2_deleted'] = $q(
                "SELECT COUNT(realtylog_id) AS total FROM {$prefix}_realtylogv2 WHERE `action`='delete'"
            );
        }

        return $this->request_success(['counts' => $counts]);
    }

    /**
     * Инвалидирует кэш счётчиков re_data (параметры с префиксом data_cnt_).
     * Вызывается из data_admin::add_data / edit_data / delete_data.
     */
    public static function invalidate_counts_cache(): void
    {
        $DBC = DBC::getInstance();
        // MySQL escapes '_' как wildcard в LIKE, поэтому экранируем
        $DBC->query("DELETE FROM " . DB_PREFIX . "_cache WHERE `parameter` LIKE 'data\\_cnt\\_%'");
    }
}
