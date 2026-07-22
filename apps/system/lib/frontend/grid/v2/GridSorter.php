<?php
/**
 * Grid Sorter V2
 *
 * Заменяет метод prepareSortOrder.
 * Паттерн: Strategy — каждый вариант сортировки является стратегией.
 *
 * Оптимизации:
 *  - Map-based dispatch вместо switch-case
 *  - Нет дублирования логики
 *  - Легко расширяется
 */

namespace system\lib\frontend\grid\v2;

class GridSorter
{
    /** @var string */
    private $prefix;

    /** @var array field => SQL expression */
    private $sortStrategies = [];

    /** @var string */
    private $defaultSort;

    public function __construct()
    {
        $this->prefix = DB_PREFIX;
        $this->initStrategies();
        $this->resolveDefaultSort();
    }

    /**
     * Определить ORDER BY на основании параметров
     *
     * @param array $params Request params
     * @param bool  $random Random order
     * @param bool  $premium Premium sort
     * @return string ORDER BY expression (без ключевого слова ORDER BY)
     */
    public function resolve(array $params, bool $random = false, bool $premium = false): string
    {
        if ($random) {
            return 'RAND()';
        }

        // Приоритет: config-defined sorts → request order → premium → default
        $configSort = $this->resolveConfigSort();
        if ($configSort !== null) {
            $this->defaultSort = $configSort;
        }

        if (isset($params['order'])) {
            return $this->resolveByParam($params);
        }

        if ($premium) {
            return "{$this->prefix}_data.premium_status_end ASC";
        }

        return $this->defaultSort;
    }

    /**
     * Нужен ли JOIN для текущей сортировки?
     * Возвращает массив JOIN-ов, которые нужно добавить.
     */
    public function getRequiredJoins(array $params): array
    {
        $joins = [];
        $order = $params['order'] ?? '';

        $joinMap = [
            'city' => [
                'table' => 'city',
                'on' => "{$this->prefix}_city.city_id={$this->prefix}_data.city_id",
                'select' => "{$this->prefix}_city.name as city"
            ],
            'district' => [
                'table' => 'district',
                'on' => "{$this->prefix}_district.id={$this->prefix}_data.district_id",
                'select' => "{$this->prefix}_district.name as district"
            ],
            'metro' => [
                'table' => 'metro',
                'on' => "{$this->prefix}_metro.metro_id={$this->prefix}_data.metro_id",
                'select' => "{$this->prefix}_metro.name as metro"
            ],
            'street' => [
                'table' => 'street',
                'on' => "{$this->prefix}_street.street_id={$this->prefix}_data.street_id",
                'select' => "{$this->prefix}_street.name as street"
            ],
            'type' => [
                'table' => 'topic',
                'on' => "{$this->prefix}_topic.id={$this->prefix}_data.topic_id",
                'select' => "{$this->prefix}_topic.name AS type_sh"
            ],
        ];

        if (isset($joinMap[$order])) {
            $joins[] = $joinMap[$order];
        }

        return $joins;
    }

    // ─── PRIVATE ────────────────────────────────────

    private function initStrategies(): void
    {
        $p = $this->prefix;

        $this->sortStrategies = [
            'id'          => fn($asc) => "id {$asc}",
            'type'        => fn($asc) => "type_sh {$asc}",
            'street'      => fn($asc) => "street {$asc}",
            'square_all'  => fn($asc) => "{$p}_data.square_all*1 {$asc}",
            'floor'       => fn($asc) => "{$p}_data.floor*1 {$asc}",
            'district'    => fn($asc) => "district {$asc}",
            'metro'       => fn($asc) => "metro {$asc}",
            'city'        => fn($asc) => "city {$asc}",
            'date_added'  => fn($asc) => "{$p}_data.date_added {$asc}",
            'price'       => fn($asc) => "price_ue {$asc}",
            'popular'     => fn($asc) => "{$p}_data.view_count {$asc}",
            // Shortcuts (ignore asc param)
            'priceup'     => fn($asc) => "price_ue ASC",
            'pricedown'   => fn($asc) => "price_ue DESC",
            'popularup'   => fn($asc) => "{$p}_data.view_count ASC",
            'populardown' => fn($asc) => "{$p}_data.view_count DESC",
            'dateup'      => fn($asc) => "{$p}_data.date_added ASC",
            'datedown'    => fn($asc) => "{$p}_data.date_added DESC",
        ];
    }

    private function resolveDefaultSort(): void
    {
        $sitebill = new \SiteBill();
        $field = trim($sitebill->getConfigValue('apps.realty.updated_at_field'));
        if ($field === '') {
            $field = 'date_added';
        }
        $this->defaultSort = "{$this->prefix}_data.`{$field}` DESC, {$this->prefix}_data.id DESC";
    }

    private function resolveConfigSort(): ?string
    {
        $sitebill = new \SiteBill();
        $configSorts = $sitebill->getConfigValue('apps.realty.sorts');
        if ($configSorts === '' || $configSorts === null) {
            return null;
        }

        $p = $this->prefix;

        switch ($configSorts) {
            case 'priceup':
                return "{$p}_data.price_ue ASC";
            case 'pricedown':
                return "{$p}_data.price_ue DESC";
        }

        // Parse pattern: field|direction;field|direction
        preg_match_all('/([a-z0-9_]+)\|(asc|desc)[;]?/i', $configSorts, $matches);
        if (empty($matches[0])) {
            return null;
        }

        $parts = [];
        foreach ($matches[1] as $k => $fkey) {
            $dir = $matches[2][$k];
            if (!in_array(strtolower($dir), ['asc', 'desc'])) {
                continue;
            }

            if (isset($this->sortStrategies[$fkey])) {
                $parts[] = ($this->sortStrategies[$fkey])($dir);
            } else {
                $parts[] = "{$p}_data.`{$fkey}` {$dir}";
            }
        }

        return !empty($parts) ? implode(', ', $parts) : null;
    }

    private function resolveByParam(array $params): string
    {
        $order = $params['order'];
        $asc = $this->resolveDirection($params);

        if (isset($this->sortStrategies[$order])) {
            return ($this->sortStrategies[$order])($asc);
        }

        // Custom _sortmodel field
        if (isset($params['_sortmodel']) && $params['_sortmodel'] == 1) {
            return "{$this->prefix}_data.`{$order}` {$asc}";
        }

        return $this->defaultSort;
    }

    private function resolveDirection(array $params): string
    {
        if (!isset($params['asc'])) {
            return 'desc';
        }
        return in_array($params['asc'], ['asc', 'desc']) ? $params['asc'] : 'desc';
    }
}
