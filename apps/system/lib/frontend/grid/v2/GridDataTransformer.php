<?php
/**
 * Grid Data Transformer V2
 *
 * Заменяет trait TransformGridData.
 *
 * Оптимизации:
 *  - Batch-загрузка связанных данных (1 запрос на справочник, а не N+1)
 *  - Кеширование справочников в памяти (static cache)
 *  - Одна итерация по массиву данных, а не множественный проход
 */

namespace system\lib\frontend\grid\v2;

class GridDataTransformer
{
    /** @var array static cache для справочников */
    private static $lookupCache = [];

    /** @var array */
    private $model;

    /** @var bool */
    private $useLangs = false;

    /** @var string */
    private $langPostfix = '';

    /** @var bool */
    private $collectUserInfo = false;

    /** @var array */
    private $userInfoFields = ['phone', 'login', 'fio'];

    /** @var array */
    private $categoryStructure = [];

    /** @var array */
    private $chains = [];

    public function __construct(array $model = [])
    {
        $this->model = $model;

        $sitebill = new \SiteBill();
        if (1 === intval($sitebill->getConfigValue('apps.language.use_langs'))) {
            $this->useLangs = true;
            $this->langPostfix = $sitebill->getLangPostfix($sitebill->getCurrentLang());
        }

        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php';
        $SM = new \Structure_Manager();
        $this->categoryStructure = $SM->loadCategoryStructure();
        $this->chains = $SM->createCatalogChains();

        $fieldsStr = trim($sitebill->getConfigValue('core.listing.add_user_info_fields'));
        if ($fieldsStr !== '') {
            $this->userInfoFields = array_filter(array_map('trim', explode(',', $fieldsStr)));
        }
    }

    public function setCollectUserInfo(bool $collect): self
    {
        $this->collectUserInfo = $collect;
        return $this;
    }

    /**
     * Основная трансформация данных грида
     *
     * @param array $rows Массив строк из БД
     * @return array Обогащённый массив
     */
    public function transform(array $rows): array
    {
        if (empty($rows)) {
            return $rows;
        }

        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php';
        $dataModel = new \Data_Model();
        $modelData = $this->model ?: $dataModel->get_kvartira_model(false, true)['data'] ?? [];

        $ids = array_column($rows, 'id');

        // ── Detect image field type ──────────────────
        $hasUploadify = false;
        $hasUploads = false;
        $uploadsElement = '';
        foreach ($modelData as $k => $v) {
            if (($v['type'] ?? '') === 'uploadify_image') {
                $hasUploadify = true;
                break;
            } elseif (($v['type'] ?? '') === 'uploads') {
                $hasUploads = true;
                $uploadsElement = $v['name'];
                break;
            }
        }

        // ── BATCH: загрузка справочников ────────────
        $lookups = $this->batchLoadLookups($rows, $modelData, $dataModel);

        // ── BATCH: user info ────────────────────────
        $userInfoMap = [];
        if ($this->collectUserInfo) {
            $userInfoMap = $this->batchLoadUserInfo($rows);
        }

        // ── BATCH: images (uploadify_image only) ────
        $images = $hasUploadify ? $this->batchLoadImages($ids, $modelData, $dataModel) : [];

        // ── BATCH: multiple fields ──────────────────
        $multipleData = $this->batchLoadMultipleFields($ids, $modelData);

        // ── SINGLE PASS: обогащение ─────────────────
        $sitebill = new \SiteBill();
        $billing = file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/billing/lib/billing.php')
            && $sitebill->getConfigValue('apps.billing.enable') == 1;

        foreach ($rows as $idx => &$row) {
            // Справочники
            $lookupFields = [
                'country_id'  => ['table' => 'country',  'pk' => 'country_id', 'name' => 'name', 'alias' => 'country'],
                'region_id'   => ['table' => 'region',   'pk' => 'region_id',  'name' => 'name', 'alias' => 'region'],
                'district_id' => ['table' => 'district', 'pk' => 'id',         'name' => 'name', 'alias' => 'district'],
                'street_id'   => ['table' => 'street',   'pk' => 'street_id',  'name' => 'name', 'alias' => 'street'],
                'city_id'     => ['table' => 'city',     'pk' => 'city_id',    'name' => 'name', 'alias' => 'city'],
                'metro_id'    => ['table' => 'metro',    'pk' => 'metro_id',   'name' => 'name', 'alias' => 'metro'],
            ];

            foreach ($lookupFields as $field => $cfg) {
                if (isset($modelData[$field]) && isset($row[$field]) && $row[$field] > 0) {
                    $fname = $cfg['name'];
                    if ($this->useLangs) {
                        $params = $modelData[$field]['parameters'] ?? [];
                        if (!isset($params['no_ml']) || intval($params['no_ml']) === 0) {
                            $fname .= $this->langPostfix;
                        }
                    }
                    $cacheKey = $cfg['table'] . '.' . $cfg['pk'] . '.' . $fname . '.' . $row[$field];
                    $row[$cfg['alias']] = $lookups[$cacheKey] ?? '';
                }
            }

            // User
            if (isset($row['user_id']) && $row['user_id'] > 0) {
                if ($this->collectUserInfo && isset($userInfoMap[$row['user_id']])) {
                    $row['_user_info'] = $userInfoMap[$row['user_id']];
                }
                $row['user'] = $lookups['user.user_id.fio.' . $row['user_id']]
                    ?? $lookups['user.user_id.login.' . $row['user_id']]
                    ?? '';
            }

            // Currency
            if (isset($row['currency_id']) && $row['currency_id'] > 0) {
                $row['currency'] = $lookups['currency.currency_id.code.' . $row['currency_id']] ?? '';
            }

            // Select boxes
            foreach ($modelData as $k => $v) {
                if (isset($v['type']) && $v['type'] == 'select_box') {
                    $row['_' . $k . '_'] = $row[$k] ?? '';
                    $row[$k] = $modelData[$k]['select_data'][$row[$k]] ?? '';
                }
            }

            // Topic info
            $row['topic_info'] = $this->categoryStructure['catalog'][$row['topic_id']] ?? null;
            $row['type_sh'] = $row['topic_info']['name'] ?? '';

            if ($this->useLangs && $row['topic_info'] !== null) {
                $fname = 'name' . $this->langPostfix;
                $row['topic_info'][$fname] = $row['topic_info'][$fname] ?? '';
            }

            // Category chain & breadcrumbs
            $row['_chain'] = $this->chains['ar'][$row['topic_id']] ?? null;
            $row['path'] = $this->getCategoryBreadcrumbsString($row);

            // Dates
            if (isset($row['date_added'])) {
                $ts = strtotime($row['date_added']);
                $row['date'] = date('d.m', $ts);
                $row['datetime'] = date('d.m H:i', $ts);
                $row['_posted_days'] = ceil((time() - $ts) / 86400);
            }

            // Strip tags from text
            if (isset($row['text']) && !$sitebill->getConfigValue('template.franch.grid.full_text_description')) {
                $row['text'] = strip_tags($row['text']);
            }

            // HREF
            $row['href'] = $sitebill->getRealtyHREF($row['id'], false, [
                'topic_id' => $row['topic_id'],
                'alias' => $row['translit_alias'] ?? ''
            ]);

            // Billing statuses
            if ($billing) {
                if (isset($row['premium_status_end']) && $row['premium_status_end'] > time()) {
                    $row['premium_status'] = 1;
                }
                if (isset($row['vip_status_end']) && $row['vip_status_end'] > time()) {
                    $row['vip_status'] = 1;
                }
                if (isset($row['bold_status_end']) && $row['bold_status_end'] > time()) {
                    $row['bold_status'] = 1;
                }
            }

            // Multilang fields
            if ($this->useLangs) {
                foreach ($modelData as $k => $v) {
                    if (in_array($v['type'] ?? '', ['safe_string', 'textarea', 'textarea_editor'])) {
                        $mlKey = $k . $this->langPostfix;
                        if (isset($row[$mlKey]) && $row[$mlKey] !== '') {
                            $row[$k] = $row[$mlKey];
                        }
                    }
                }
            }

            // Images
            if ($hasUploadify && isset($images[$row['id']])) {
                $row['img'] = $images[$row['id']];
            } elseif ($hasUploads) {
                if (isset($row[$uploadsElement]) && $row[$uploadsElement] != '') {
                    $ims = @unserialize($row[$uploadsElement]);
                    if (is_array($ims) && count($ims) > 0) {
                        $ims = $dataModel->sharder_mirror($ims, true);
                        $row['img'] = $ims;
                    }
                } elseif (isset($row['image_cache']) && $row['image_cache'] != '') {
                    $cacheData = @unserialize($row['image_cache']);
                    if (is_array($cacheData) && count($cacheData) > 0) {
                        $i = 0;
                        $row['img'] = [];
                        foreach ($cacheData as $cache_item) {
                            if ($sitebill->getConfigValue('apps.sharder.mirroring.enable')) {
                                $cache_item = str_replace(
                                    $sitebill->getConfigValue('apps.sharder.mirroring.find'),
                                    $sitebill->getConfigValue('apps.sharder.mirroring.replace'),
                                    $cache_item
                                );
                            }
                            $row['img'][$i] = [
                                'preview' => $cache_item,
                                'normal' => $cache_item,
                                'remote' => 'true',
                            ];
                            if (!is_array($row['image'] ?? null) || $row['image'] === '') {
                                $row['image'] = [];
                            }
                            if (!is_array($row['image'][$i] ?? null)) {
                                $row['image'][$i] = [
                                    'preview' => $cache_item,
                                    'normal' => $cache_item,
                                    'remote' => 'true',
                                ];
                            }
                            $i++;
                        }
                    }
                }
            }

            // Multiple fields
            if (isset($multipleData['values'][$row['id']])) {
                foreach ($multipleData['values'][$row['id']] as $fieldName => $fieldValues) {
                    $row[$fieldName] = [
                        $fieldValues,
                        $this->resolveMultipleLabels($fieldName, $fieldValues, $multipleData['labels'])
                    ];
                }
            }
        }
        unset($row);

        // Final pass: unserialize all uploads-type fields + sharder_mirror
        if ($hasUploads) {
            foreach ($rows as &$row) {
                foreach ($modelData as $k => $v) {
                    if (isset($v['type']) && $v['type'] === 'uploads') {
                        if (isset($row[$k]) && is_scalar($row[$k])) {
                            $row[$k] = @unserialize($row[$k]);
                            $row[$k] = $dataModel->sharder_mirror($row[$k], true);
                        }
                    }
                }
            }
            unset($row);
        }

        return $rows;
    }

    // ─── BATCH LOADERS ──────────────────────────────

    /**
     * Батч-загрузка справочников
     */
    private function batchLoadLookups(array $rows, array $modelData, \Data_Model $dm): array
    {
        $result = [];

        $lookupFields = [
            'country_id'  => ['table' => 'country',  'pk' => 'country_id', 'name' => 'name'],
            'region_id'   => ['table' => 'region',   'pk' => 'region_id',  'name' => 'name'],
            'district_id' => ['table' => 'district', 'pk' => 'id',         'name' => 'name'],
            'street_id'   => ['table' => 'street',   'pk' => 'street_id',  'name' => 'name'],
            'city_id'     => ['table' => 'city',     'pk' => 'city_id',    'name' => 'name'],
            'metro_id'    => ['table' => 'metro',    'pk' => 'metro_id',   'name' => 'name'],
        ];

        foreach ($lookupFields as $field => $cfg) {
            if (!isset($modelData[$field])) continue;

            $ids = array_unique(array_filter(array_column($rows, $field), function($v) { return $v > 0; }));
            if (empty($ids)) continue;

            $fname = $cfg['name'];
            if ($this->useLangs) {
                $params = $modelData[$field]['parameters'] ?? [];
                if (!isset($params['no_ml']) || intval($params['no_ml']) === 0) {
                    $fname .= $this->langPostfix;
                }
            }

            $cachePrefix = $cfg['table'] . '.' . $cfg['pk'] . '.' . $fname . '.';

            // Проверяем, какие ID уже в кэше
            $missingIds = [];
            foreach ($ids as $id) {
                if (!isset(self::$lookupCache[$cachePrefix . $id])) {
                    $missingIds[] = $id;
                }
            }

            // Загружаем только отсутствующие
            if (!empty($missingIds)) {
                $DBC = \DBC::getInstance();
                $placeholders = implode(',', array_fill(0, count($missingIds), '?'));
                $query = "SELECT `{$cfg['pk']}`, `{$fname}` FROM " . DB_PREFIX . "_{$cfg['table']} WHERE `{$cfg['pk']}` IN ({$placeholders})";
                $stmt = $DBC->query($query, $missingIds);
                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        self::$lookupCache[$cachePrefix . $ar[$cfg['pk']]] = $ar[$fname] ?? '';
                    }
                }
            }

            foreach ($ids as $id) {
                $result[$cachePrefix . $id] = self::$lookupCache[$cachePrefix . $id] ?? '';
            }
        }

        // Users (fio + login)
        $userIds = array_unique(array_filter(array_column($rows, 'user_id'), function($v) { return $v > 0; }));
        if (!empty($userIds)) {
            $missing = [];
            foreach ($userIds as $uid) {
                if (!isset(self::$lookupCache['user.user_id.fio.' . $uid])) {
                    $missing[] = $uid;
                }
            }
            if (!empty($missing)) {
                $DBC = \DBC::getInstance();
                $ph = implode(',', array_fill(0, count($missing), '?'));
                $stmt = $DBC->query("SELECT user_id, fio, login FROM " . DB_PREFIX . "_user WHERE user_id IN ({$ph})", $missing);
                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        self::$lookupCache['user.user_id.fio.' . $ar['user_id']] = $ar['fio'];
                        self::$lookupCache['user.user_id.login.' . $ar['user_id']] = $ar['login'];
                    }
                }
            }
            foreach ($userIds as $uid) {
                $result['user.user_id.fio.' . $uid] = self::$lookupCache['user.user_id.fio.' . $uid] ?? '';
                $result['user.user_id.login.' . $uid] = self::$lookupCache['user.user_id.login.' . $uid] ?? '';
            }
        }

        // Currency
        $currencyIds = array_unique(array_filter(array_column($rows, 'currency_id'), function($v) { return $v > 0; }));
        if (!empty($currencyIds)) {
            $missing = array_filter($currencyIds, function($id) {
                return !isset(self::$lookupCache['currency.currency_id.code.' . $id]);
            });
            if (!empty($missing)) {
                $DBC = \DBC::getInstance();
                $ph = implode(',', array_fill(0, count($missing), '?'));
                $stmt = $DBC->query("SELECT currency_id, code FROM " . DB_PREFIX . "_currency WHERE currency_id IN ({$ph})", array_values($missing));
                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        self::$lookupCache['currency.currency_id.code.' . $ar['currency_id']] = $ar['code'];
                    }
                }
            }
            foreach ($currencyIds as $cid) {
                $result['currency.currency_id.code.' . $cid] = self::$lookupCache['currency.currency_id.code.' . $cid] ?? '';
            }
        }

        return $result;
    }

    /**
     * Батч-загрузка user info
     */
    private function batchLoadUserInfo(array $rows): array
    {
        $userIds = array_unique(array_filter(array_column($rows, 'user_id'), function($v) { return $v > 0; }));
        if (empty($userIds)) return [];

        $DBC = \DBC::getInstance();
        $fields = '`' . implode('`,`', $this->userInfoFields) . '`';
        $ph = implode(',', array_fill(0, count($userIds), '?'));

        $result = [];
        $stmt = $DBC->query("SELECT user_id, {$fields} FROM " . DB_PREFIX . "_user WHERE user_id IN ({$ph})", $userIds);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $result[$ar['user_id']] = $ar;
            }
        }
        return $result;
    }

    /**
     * Батч-загрузка изображений
     */
    private function batchLoadImages(array $ids, array $modelData, \Data_Model $dm): array
    {
        if (empty($ids)) return [];

        $hasUploadify = false;
        $hasUploads = false;
        $uploadsElement = '';

        foreach ($modelData as $k => $v) {
            if (($v['type'] ?? '') === 'uploadify_image') {
                $hasUploadify = true;
                break;
            } elseif (($v['type'] ?? '') === 'uploads') {
                $hasUploads = true;
                $uploadsElement = $v['name'];
                break;
            }
        }

        if (!$hasUploadify) return [];

        $DBC = \DBC::getInstance();
        $sitebill = new \SiteBill();
        $iurl = $sitebill->storage_dir ?? '';

        $ph = implode(',', $ids);
        $query = 'SELECT li.id, i.* FROM ' . DB_PREFIX . '_data_image li LEFT JOIN ' . IMAGE_TABLE . ' i USING(image_id) WHERE li.id IN (' . $ph . ') ORDER BY li.sort_order ASC';
        $stmt = $DBC->query($query);
        $images = [];
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ar['img_preview'] = $iurl . $ar['preview'];
                $ar['img_normal'] = $iurl . $ar['normal'];
                $images[$ar['id']][] = $ar;
            }
        }
        return $images;
    }

    /**
     * Батч-загрузка M2M-полей
     */
    private function batchLoadMultipleFields(array $ids, array $modelData): array
    {
        $multiFields = [];
        foreach ($modelData as $k => $v) {
            if (($v['type'] ?? '') === 'select_by_query_multi') {
                $multiFields[] = $k;
            }
        }

        if (empty($multiFields) || empty($ids)) {
            return ['values' => [], 'labels' => []];
        }

        $DBC = \DBC::getInstance();
        $phFields = implode(',', array_fill(0, count($multiFields), '?'));
        $phIds = implode(',', array_fill(0, count($ids), '?'));

        $query = "SELECT primary_id, field_name, field_value FROM " . DB_PREFIX . "_multiple_field WHERE table_name=? AND field_name IN ({$phFields}) AND primary_id IN ({$phIds})";
        $qParams = array_merge(['data'], $multiFields, $ids);

        $values = [];
        $labelKeys = [];
        $stmt = $DBC->query($query, $qParams);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $values[$ar['primary_id']][$ar['field_name']][] = $ar['field_value'];
                $labelKeys[$ar['field_name']][$ar['field_value']] = '';
            }
        }

        // Загрузка названий для значений
        $labels = [];
        foreach ($labelKeys as $fieldName => $keyMap) {
            if (!isset($modelData[$fieldName])) continue;
            $meta = $modelData[$fieldName];
            $name = $meta['value_name'];
            $pk = $meta['primary_key_name'];
            $table = $meta['primary_key_table'];

            $fIds = array_keys($keyMap);
            if (empty($fIds)) continue;

            $ph = implode(',', $fIds);
            $stmt = $DBC->query("SELECT `{$pk}`, `{$name}` FROM " . DB_PREFIX . "_{$table} WHERE `{$pk}` IN ({$ph})");
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $labels[$fieldName][$ar[$pk]] = $ar[$name];
                }
            }
        }

        return ['values' => $values, 'labels' => $labels];
    }

    private function resolveMultipleLabels(string $fieldName, array $values, array $allLabels): array
    {
        $result = [];
        foreach ($values as $v) {
            $result[$v] = $allLabels[$fieldName][$v] ?? '';
        }
        return $result;
    }

    private function getCategoryBreadcrumbsString(array $row): string
    {
        if (!isset($row['topic_id']) || !isset($this->categoryStructure['catalog'][$row['topic_id']])) {
            return '';
        }

        $names = [];
        $topicId = $row['topic_id'];
        $maxDepth = 20;

        while ($topicId > 0 && $maxDepth-- > 0) {
            if (!isset($this->categoryStructure['catalog'][$topicId])) break;
            $cat = $this->categoryStructure['catalog'][$topicId];
            $names[] = $cat['name'] ?? '';
            $topicId = $cat['parent_id'] ?? 0;
        }

        return implode(' / ', array_reverse($names));
    }

    /**
     * Очистить статический кэш справочников (для тестов)
     */
    public static function clearCache(): void
    {
        self::$lookupCache = [];
    }
}
