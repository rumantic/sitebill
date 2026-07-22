<?php
/**
 * Grid Filter Pipeline V2
 *
 * Заменяет 1400-строчный trait PrepareRequestParams.
 * Каждый фильтр — отдельный callable, регистрируемый в pipeline.
 *
 * Паттерны: Pipeline / Chain of Responsibility
 *
 * Оптимизации:
 *  - Фильтры регистрируются лениво (через массив конфигов)
 *  - Выполняются только те, для которых есть параметр в запросе
 *  - Однотипные фильтры (IN-списки, чекбоксы, диапазоны) генерируются автоматически
 */

namespace system\lib\frontend\grid\v2;

class GridFilterPipeline
{
    /** @var GridQueryBuilder */
    private $builder;

    /** @var array */
    private $params;

    /** @var array Модель данных грида */
    private $dataModel;

    /** @var bool */
    private $billingMode = false;

    /** @var bool Флаг premium (из аргумента get_sitebill_adv_core) */
    private $premiumFlag = false;

    /** @var bool */
    private $useCurrency = false;

    /** @var float */
    private $priceKoefficient = 1;

    /** @var string */
    private $prefix;

    /** @var callable[] Зарегистрированные фильтры */
    private $filters = [];

    public function __construct(GridQueryBuilder $builder, array $dataModel = [])
    {
        $this->builder = $builder;
        $this->dataModel = $dataModel;
        $this->prefix = DB_PREFIX;
        $this->registerDefaultFilters();
    }

    public function setParams(array $params): self
    {
        $this->params = $params;
        return $this;
    }

    public function setBillingMode(bool $mode): self
    {
        $this->billingMode = $mode;
        return $this;
    }

    public function setPremiumFlag(bool $premium): self
    {
        $this->premiumFlag = $premium;
        return $this;
    }

    public function setCurrency(bool $use, float $koefficient = 1): self
    {
        $this->useCurrency = $use;
        $this->priceKoefficient = $koefficient;
        return $this;
    }

    /**
     * Зарегистрировать пользовательский фильтр
     *
     * @param string   $name     Имя (для отладки)
     * @param callable $filter   function(array &$params, GridQueryBuilder $builder, array $dataModel): void
     */
    public function addFilter(string $name, callable $filter): self
    {
        $this->filters[$name] = $filter;
        return $this;
    }

    /**
     * Выполнить pipeline: применить все фильтры к билдеру
     * @return array Модифицированные params (после очистки неактивных)
     */
    public function apply(): array
    {
        $params = $this->params;

        foreach ($this->filters as $name => $filter) {
            $filter($params, $this->builder, $this->dataModel);
        }

        return $params;
    }

    /**
     * Регистрация стандартных фильтров
     */
    private function registerDefaultFilters(): void
    {
        $prefix = $this->prefix;
        $self = $this;

        // ── Active / Archived ──────────────────────
        $this->filters['active'] = function (&$params, GridQueryBuilder $b, $model) use ($prefix) {
            if (!isset($params['admin']) || $params['admin'] != 1) {
                $b->where("({$prefix}_data.`active`=1)");
                if (isset($model['archived'])) {
                    $sitebill = new \SiteBill();
                    if (1 == (int)$sitebill->getConfigValue('apps.realty.use_predeleting')) {
                        $b->where("({$prefix}_data.`archived`<>1)");
                    }
                }
            } else {
                $sitebill = new \SiteBill();
                if (1 == (int)$sitebill->getConfigValue('apps.realty.use_predeleting') && isset($model['archived'])) {
                    if (isset($params['archived']) && $params['archived'] == 1) {
                        $b->where("({$prefix}_data.`archived`=1)");
                    } else {
                        $b->where("({$prefix}_data.`archived`=0)");
                    }
                }
                if (isset($params['active']) && $params['active'] == 1) {
                    $b->where("({$prefix}_data.`active`=1)");
                } elseif (isset($params['active']) && $params['active'] == 'notactive') {
                    $b->where("({$prefix}_data.`active`=0)");
                }
            }
        };

        // ── Topic ID ───────────────────────────────
        $this->filters['topic_id'] = function (&$params, GridQueryBuilder $b, $model) use ($prefix) {
            if (!isset($params['topic_id'])) {
                return;
            }
            $topics = is_array($params['topic_id']) ? $params['topic_id'] : (array)$params['topic_id'];
            if (empty($topics)) {
                unset($params['topic_id']);
                return;
            }

            $list = [];
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
            $SM = new \Structure_Manager();
            $cs = $SM->loadCategoryStructure();

            foreach ($topics as $tid) {
                if (intval($tid) > 0 && isset($cs['catalog'][$tid])) {
                    $childs = $SM->get_all_childs($tid, $cs);
                    if (!empty($childs)) {
                        $list = array_merge($list, $childs);
                    }
                    $list[] = intval($tid);
                }
            }

            if (!empty($list)) {
                $list = array_unique($list, SORT_NUMERIC);
                $b->whereIn("{$prefix}_data.topic_id", $list);
            } else {
                unset($params['topic_id']);
            }
        };

        // ── IN-list фильтры (generic) ──────────────
        $inListFilters = [
            'city_id'              => 'city_id',
            'region_id'            => 'region_id',
            'district_id'          => 'district_id',
            'metro_id'             => 'metro_id',
            'street_id'            => 'street_id',
            'complex_id'           => 'complex_id',
            'complex_building_id'  => 'complex_building_id',
        ];

        foreach ($inListFilters as $paramName => $column) {
            $this->filters[$paramName] = function (&$params, GridQueryBuilder $b, $model) use ($prefix, $paramName, $column) {
                if (!isset($params[$paramName]) || $params[$paramName] == 0) {
                    unset($params[$paramName]);
                    return;
                }

                if (is_array($params[$paramName])) {
                    $values = array_filter(array_map('intval', $params[$paramName]));
                    if (!empty($values)) {
                        $b->whereIn("{$prefix}_data.{$column}", $values);
                    } else {
                        unset($params[$paramName]);
                    }
                } else {
                    $val = intval($params[$paramName]);
                    if ($val > 0) {
                        $b->where("({$prefix}_data.{$column}=?)", $val);
                    } else {
                        unset($params[$paramName]);
                    }
                }
            };
        }

        // ── ID filter ──────────────────────────────
        $this->filters['id'] = function (&$params, GridQueryBuilder $b, $model) use ($prefix) {
            if (!isset($params['id'])) return;

            if (is_array($params['id'])) {
                $values = array_filter(array_map('intval', $params['id']));
                if (!empty($values)) {
                    $b->whereIn("{$prefix}_data.id", $values);
                } else {
                    unset($params['id']);
                }
            } else {
                $val = intval($params['id']);
                if ($val > 0) {
                    $b->where("({$prefix}_data.id=?)", $val);
                } else {
                    unset($params['id']);
                }
            }
        };

        // ── User ID ────────────────────────────────
        $this->filters['user_id'] = function (&$params, GridQueryBuilder $b, $model) use ($prefix) {
            if (isset($_SESSION['user_domain_owner']) && (int)$_SESSION['user_domain_owner']['user_id'] != 0) {
                $b->where("({$prefix}_data.user_id=?)", (int)$_SESSION['user_domain_owner']['user_id']);
                return;
            }
            if (!isset($params['user_id'])) return;

            if (isset($params['coworked_ids']) && !empty($params['coworked_ids'])) {
                $ph = implode(',', array_fill(0, count($params['coworked_ids']), '?'));
                $b->where("({$prefix}_data.user_id=? OR {$prefix}_data.id IN ({$ph}))", (int)$params['user_id'], ...$params['coworked_ids']);
            } elseif (isset($params['coworked_users']) && !empty($params['coworked_users'])) {
                $ph = implode(',', array_fill(0, count($params['coworked_users']), '?'));
                $b->where("({$prefix}_data.user_id=? OR {$prefix}_data.user_id IN ({$ph}))", (int)$params['user_id'], ...$params['coworked_users']);
            } else {
                if (is_array($params['user_id'])) {
                    $b->whereIn("{$prefix}_data.user_id", array_map('intval', $params['user_id']));
                } else {
                    if ((int)$params['user_id'] > 0) {
                        $b->where("({$prefix}_data.user_id=?)", (int)$params['user_id']);
                    }
                }
            }
        };

        // ── Price Range ────────────────────────────
        $this->filters['price_range'] = function (&$params, GridQueryBuilder $b, $model) use ($prefix, $self) {
            // Price max
            if (isset($params['price']) && $params['price'] != 0) {
                $price = (int)str_replace(' ', '', $params['price']);
                if ($self->useCurrency) {
                    $b->where("((({$prefix}_data.price*{$prefix}_currency.course)/{$self->priceKoefficient})<=?)", $price);
                } else {
                    $b->where("({$prefix}_data.price<=?)", $price);
                }
            } else {
                unset($params['price']);
            }
            // Price min
            if (isset($params['price_min']) && $params['price_min'] != 0) {
                $price = (int)str_replace(' ', '', $params['price_min']);
                if ($self->useCurrency) {
                    $b->where("((({$prefix}_data.price*{$prefix}_currency.course)/{$self->priceKoefficient})>=?)", $price);
                } else {
                    $b->where("({$prefix}_data.price>=?)", $price);
                }
            } else {
                unset($params['price_min']);
            }
        };

        // ── Square Range ───────────────────────────
        $this->filters['square_range'] = function (&$params, GridQueryBuilder $b, $model) use ($prefix) {
            if (isset($params['square_min']) && (int)$params['square_min'] != 0) {
                $b->where("({$prefix}_data.square_all*1 >= ?)", preg_replace('/[^\d.,]/', '', $params['square_min']));
            } else {
                unset($params['square_min']);
            }
            if (isset($params['square_max']) && (int)$params['square_max'] != 0) {
                $b->where("({$prefix}_data.square_all*1 <= ?)", preg_replace('/[^\d.,]/', '', $params['square_max']));
            } else {
                unset($params['square_max']);
            }
        };

        // ── Floor Range ────────────────────────────
        $this->filters['floor_range'] = function (&$params, GridQueryBuilder $b, $model) use ($prefix) {
            if (isset($params['floor_min']) && (int)$params['floor_min'] != 0) {
                $b->where("({$prefix}_data.floor*1 >= ?)", (int)$params['floor_min']);
            } else {
                unset($params['floor_min']);
            }
            if (isset($params['floor_max']) && (int)$params['floor_max'] != 0) {
                $b->where("({$prefix}_data.floor*1 <= ?)", (int)$params['floor_max']);
            } else {
                unset($params['floor_max']);
            }
            if (isset($params['not_first_floor']) && (int)$params['not_first_floor'] == 1) {
                $b->where("({$prefix}_data.floor*1 > 1)");
            } else {
                unset($params['not_first_floor']);
            }
            if (isset($params['not_last_floor']) && (int)$params['not_last_floor'] == 1) {
                $b->where("({$prefix}_data.floor*1 > 0 AND {$prefix}_data.floor*1 <> {$prefix}_data.floor_count*1)");
            } else {
                unset($params['not_last_floor']);
            }
        };

        // ── Room count ─────────────────────────────
        $this->filters['room_count'] = function (&$params, GridQueryBuilder $b, $model) use ($prefix) {
            if (!isset($params['room_count'])) return;

            if (is_array($params['room_count']) && count($params['room_count']) > 0) {
                $sub = [];
                $vals = [];
                foreach ($params['room_count'] as $rq) {
                    if ($rq == 4) {
                        $sub[] = "({$prefix}_data.room_count>3)";
                    } elseif ((int)$rq != 0) {
                        $sub[] = "({$prefix}_data.room_count=?)";
                        $vals[] = (int)$rq;
                    }
                }
                if (!empty($sub)) {
                    $b->where('(' . implode(' OR ', $sub) . ')', ...$vals);
                }
            } elseif ((int)$params['room_count'] != 0) {
                $b->where("({$prefix}_data.room_count=?)", (int)$params['room_count']);
            } else {
                unset($params['room_count']);
            }
        };

        // ── Checkbox filters (generic) ─────────────
        $checkboxFilters = [
            'hot', 'is_phone' => 'is_telephone', 'is_internet' => 'is_internet',
            'is_furniture' => 'furniture',
            'infra_greenzone', 'infra_sea', 'infra_sport',
            'infra_clinic', 'infra_terminal', 'infra_airport',
            'infra_bank', 'infra_restaurant',
        ];

        foreach ($checkboxFilters as $paramName => $column) {
            if (is_int($paramName)) {
                $paramName = $column;
            }
            $this->filters['cb_' . $paramName] = function (&$params, GridQueryBuilder $b, $model) use ($prefix, $paramName, $column) {
                if (isset($params[$paramName]) && (int)$params[$paramName] == 1) {
                    $b->where("({$prefix}_data.{$column}=1)");
                } elseif (isset($params[$paramName]) && (int)$params[$paramName] == -1) {
                    $b->where("({$prefix}_data.{$column} <> 1)");
                } else {
                    unset($params[$paramName]);
                }
            };
        }

        // ── Favorites ──────────────────────────────
        $this->filters['favorites'] = function (&$params, GridQueryBuilder $b, $model) use ($prefix) {
            if (!isset($params['favorites']) || empty($params['favorites'])) return;
            $values = array_filter(array_map('intval', $params['favorites']));
            if (!empty($values)) {
                $b->whereIn("{$prefix}_data.id", $values);
            }
        };

        // ── Date filters ───────────────────────────
        $this->filters['date_filters'] = function (&$params, GridQueryBuilder $b, $model) use ($prefix) {
            if (isset($params['added_in_days']) && (int)$params['added_in_days'] != 0) {
                $dateLimit = date('Y-m-d H:i:s', time() - (int)$params['added_in_days'] * 86400);
                $b->where("({$prefix}_data.date_added>=?)", $dateLimit);
            } else {
                unset($params['added_in_days']);
            }

            if (isset($params['srch_date_from'])) {
                if (preg_match('/^(\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?)$/', $params['srch_date_from'])) {
                    $b->where("({$prefix}_data.date_added>=?)", $params['srch_date_from']);
                } else {
                    unset($params['srch_date_from']);
                }
            }

            if (isset($params['srch_date_to'])) {
                if (preg_match('/^(\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?)$/', $params['srch_date_to'])) {
                    $b->where("({$prefix}_data.date_added<=?)", $params['srch_date_to']);
                } else {
                    unset($params['srch_date_to']);
                }
            }
        };

        // ── Search word / phone ────────────────────
        $this->filters['search_text'] = function (&$params, GridQueryBuilder $b, $model) use ($prefix) {
            // Phone search
            if (isset($params['srch_phone']) && trim($params['srch_phone']) !== '') {
                $phone = preg_replace('/[^\d]/', '', $params['srch_phone']);
                $sub = [];
                $vals = [];
                $sub[] = "({$prefix}_data.phone LIKE ?)";
                $vals[] = '%' . $phone . '%';

                $sitebill = new \SiteBill();
                if ($sitebill->getConfigValue('allow_additional_mobile_number')) {
                    $sub[] = "({$prefix}_data.ad_mobile_phone LIKE ?)";
                    $vals[] = '%' . $phone . '%';
                }
                if ($sitebill->getConfigValue('allow_additional_stationary_number')) {
                    $sub[] = "({$prefix}_data.ad_stacionary_phone LIKE ?)";
                    $vals[] = '%' . $phone . '%';
                }
                $b->where('(' . implode(' OR ', $sub) . ')', ...$vals);
            } else {
                unset($params['srch_phone']);
            }

            // Word search
            if (isset($params['srch_word']) && $params['srch_word'] !== null) {
                $word = htmlspecialchars($params['srch_word']);
                if ($word !== '') {
                    $b->where("({$prefix}_data.text LIKE ?)", '%' . $word . '%');
                }
            } else {
                unset($params['srch_word']);
            }
        };

        // ── Has photo ──────────────────────────────
        $this->filters['has_photo'] = function (&$params, GridQueryBuilder $b, $model) use ($prefix) {
            if (!isset($params['has_photo']) || (int)$params['has_photo'] != 1) {
                unset($params['has_photo']);
                return;
            }

            $hasUploadify = false;
            $uploadsFields = [];
            foreach ($model as $item) {
                if (!isset($item['type'])) continue;
                if ($item['type'] == 'uploadify_image') {
                    $hasUploadify = true;
                    break;
                } elseif ($item['type'] == 'uploads') {
                    $uploadsFields[] = $item['name'];
                }
            }

            if ($hasUploadify) {
                $b->where("((SELECT COUNT(*) FROM {$prefix}_data_image WHERE id={$prefix}_data.id)>0)");
            } elseif (!empty($uploadsFields)) {
                $sub = [];
                foreach ($uploadsFields as $uf) {
                    $sub[] = "{$prefix}_data.`{$uf}`<>''";
                }
                $b->where('(' . implode(' OR ', $sub) . ')');
            }
        };

        // ── Geo bounds ─────────────────────────────
        $this->filters['geo_bounds'] = function (&$params, GridQueryBuilder $b, $model) use ($prefix) {
            if (isset($params['map_bounds'])) {
                $b->where(
                    "(({$prefix}_data.geo_lat BETWEEN ? AND ?) AND ({$prefix}_data.geo_lng BETWEEN ? AND ?))",
                    $params['map_bounds'][0][0], $params['map_bounds'][1][0],
                    $params['map_bounds'][0][1], $params['map_bounds'][1][1]
                );
                unset($params['map_bounds']);
            }

            if (isset($params['polylineString']) && is_scalar($params['polylineString'])) {
                $coords = array_map('floatval', explode(',', $params['polylineString']));
                $lats = [];
                $lngs = [];
                for ($i = 0; $i < count($coords) - 1; $i += 2) {
                    $lats[] = $coords[$i];
                    $lngs[] = $coords[$i + 1];
                }
                if (!empty($lats)) {
                    $b->where(
                        "({$prefix}_data.geo_lat >= ? AND {$prefix}_data.geo_lat <= ? AND {$prefix}_data.geo_lng >= ? AND {$prefix}_data.geo_lng <= ?)",
                        min($lats), max($lats), min($lngs), max($lngs)
                    );
                }
                unset($params['polylineString']);
            }

            if (isset($params['geocoords'])) {
                if (preg_match('/([-]?\d{2,3}\.\d{6}),([-]?\d{2,3}\.\d{6}):([-]?\d{2,3}\.\d{6}),([-]?\d{2,3}\.\d{6})/', $params['geocoords'], $m)) {
                    $b->where("({$prefix}_data.geo_lat IS NOT NULL AND {$prefix}_data.geo_lng IS NOT NULL)");
                    $b->where("({$prefix}_data.geo_lat >=? AND {$prefix}_data.geo_lat <= ? AND {$prefix}_data.geo_lng >=? AND {$prefix}_data.geo_lng <= ?)",
                        $m[1], $m[3], $m[2], $m[4]
                    );
                }
            } elseif (isset($params['has_geo']) && (int)$params['has_geo'] == 1) {
                $b->where("({$prefix}_data.geo_lat IS NOT NULL AND {$prefix}_data.geo_lng IS NOT NULL)");
            } else {
                unset($params['has_geo']);
            }
        };

        // ── spec / onlyspecial (hot=1) ──────────────
        $this->filters['spec'] = function (&$params, GridQueryBuilder $b, $model) use ($prefix) {
            if (isset($params['spec']) && $params['spec'] != '') {
                $b->where("({$prefix}_data.hot=1)");
            } else {
                unset($params['spec']);
            }
        };

        $this->filters['onlyspecial'] = function (&$params, GridQueryBuilder $b, $model) use ($prefix) {
            if (isset($params['onlyspecial']) && (int)$params['onlyspecial'] > 0) {
                $b->where("({$prefix}_data.hot=1)");
            } else {
                unset($params['onlyspecial']);
            }
        };

        // ── only_img (для 3columns темы) ───────────
        $this->filters['only_img'] = function (&$params, GridQueryBuilder $b, $model) use ($prefix) {
            if (isset($params['only_img']) && $params['only_img']) {
                $b->rawJoin('INNER JOIN ' . $prefix . '_data_image i ON ' . $prefix . '_data.id=i.id', 'data_image_only_img');
            }
        };

        // ── Company timelimit ──────────────────────
        $this->filters['company_timelimit'] = function (&$params, GridQueryBuilder $b, $model) use ($prefix, $self) {
            $sitebill = new \SiteBill();
            if ($sitebill->getConfigValue('apps.company.timelimit')) {
                $current_time = time();
                $b->rawJoin('LEFT JOIN ' . $prefix . '_user u USING(user_id)', 'user_timelimit');
                $b->rawJoin('LEFT JOIN ' . $prefix . '_company c ON u.company_id=c.company_id', 'company_timelimit');
                $b->where('(c.start_date<=?)', $current_time);
                $b->where('(c.end_date >=?)', $current_time);
            }
        };

        // ── Billing premium/vip ────────────────────
        $this->filters['billing_status'] = function (&$params, GridQueryBuilder $b, $model) use ($prefix, $self) {
            if (!$self->billingMode) return;
            // Фильтры VIP/Premium/Bold по статусу (из URL/request)
            $statusFields = ['vip_status', 'premium_status', 'bold_status'];
            foreach ($statusFields as $sf) {
                if (isset($params[$sf]) && (int)$params[$sf] != 0) {
                    $time = strtotime(date('Y-m-d H:00:00', time() + 3600));
                    $field = $sf . '_end';
                    $b->where("({$prefix}_data.{$field}<>0 AND {$prefix}_data.{$field} >= ?)", $time);
                } else {
                    unset($params[$sf]);
                }
            }
        };

        // ── Billing vip/premium param + premium flag ─
        $this->filters['billing_vip_premium'] = function (&$params, GridQueryBuilder $b, $model) use ($prefix, $self) {
            if (!$self->billingMode) return;

            $_time = strtotime(date('Y-m-d H:00:00', time() + 3600));

            if ($self->premiumFlag) {
                // premium flag from get_sitebill_adv_ext_base(premium=true)
                $b->where("({$prefix}_data.premium_status_end >= ?)", $_time);
            } elseif (isset($params['vip']) && $params['vip'] == 1) {
                // VIP block (grid_vip_right)
                $b->where("({$prefix}_data.vip_status_end<>0 AND {$prefix}_data.vip_status_end >= ?)", $_time);
            } elseif (isset($params['premium']) && $params['premium'] == 1) {
                // Premium param
                $b->where("({$prefix}_data.premium_status_end<>0 AND {$prefix}_data.premium_status_end >= ?)", $_time);
            } elseif (isset($params['admin']) && $params['admin'] == 1) {
                // admin mode — no billing filter
            } else {
                // Default: exclude premium items from regular grid
                $sitebill = new \SiteBill();
                if (!isset($params['no_premium_filtering']) && 1 != $sitebill->getConfigValue('apps.billing.disable_premium_popup')) {
                    $b->where("({$prefix}_data.premium_status_end < ?)", $_time);
                }
            }
        };

        // ── SConfig searchable_params ──────────────
        $this->filters['sconfig_params'] = function (&$params, GridQueryBuilder $b, $model) use ($prefix) {
            if (\SConfig::getConfigValueStatic('searchable_params') !== null && is_array(\SConfig::getConfigValueStatic('searchable_params'))) {
                foreach (\SConfig::getConfigValueStatic('searchable_params') as $param) {
                    if (isset($params[$param]) && (int)$params[$param] == 1) {
                        $b->where("({$prefix}_data.{$param}=1)");
                    } elseif (isset($params[$param]) && (int)$params[$param] == -1) {
                        $b->where("({$prefix}_data.{$param} <> 1)");
                    } else {
                        unset($params[$param]);
                    }
                }
            }
        };

        // ── Project-specific range & select filters (samara-zaselim) ─
        $this->filters['project_ranges'] = function (&$params, GridQueryBuilder $b, $model) use ($prefix) {
            // razmeshenie_chel range
            if (isset($params['razmeshenie_chel_from']) && (int)$params['razmeshenie_chel_from'] > 0) {
                $b->where("({$prefix}_data.razmeshenie_chel >= ?)", (int)$params['razmeshenie_chel_from']);
            } else {
                unset($params['razmeshenie_chel_from']);
            }
            if (isset($params['razmeshenie_chel_to']) && (int)$params['razmeshenie_chel_to'] > 0) {
                $b->where("({$prefix}_data.razmeshenie_chel <= ?)", (int)$params['razmeshenie_chel_to']);
            } else {
                unset($params['razmeshenie_chel_to']);
            }

            // maxpersons range
            if (isset($params['maxpersons_from']) && (int)$params['maxpersons_from'] > 0) {
                $b->where("({$prefix}_data.maxpersons >= ?)", (int)$params['maxpersons_from']);
            } else {
                unset($params['maxpersons_from']);
            }
            if (isset($params['maxpersons_to']) && (int)$params['maxpersons_to'] > 0) {
                $b->where("({$prefix}_data.maxpersons <= ?)", (int)$params['maxpersons_to']);
            } else {
                unset($params['maxpersons_to']);
            }

            // besedkamax
            if (isset($params['besedkamax_from']) && (int)$params['besedkamax_from'] > 0) {
                $b->where("({$prefix}_data.besedkamax >= ?)", (int)$params['besedkamax_from']);
            } else {
                unset($params['besedkamax_from']);
            }

            // city_distance range
            if (isset($params['distance_from']) && (int)$params['distance_from'] > 0) {
                $b->where("({$prefix}_data.city_distance >= ?)", (int)$params['distance_from']);
            } else {
                unset($params['distance_from']);
            }
            if (isset($params['distance_to']) && (int)$params['distance_to'] > 0) {
                $b->where("({$prefix}_data.city_distance <= ?)", (int)$params['distance_to']);
            } else {
                unset($params['distance_to']);
            }

            // rooms_from (минимальное число комнат)
            if (isset($params['rooms_from']) && (int)$params['rooms_from'] > 0) {
                $b->where("({$prefix}_data.room_count >= ?)", (int)$params['rooms_from']);
            } else {
                unset($params['rooms_from']);
            }

            // klass_jilia (точное совпадение)
            if (isset($params['klass_jilia']) && (int)$params['klass_jilia'] > 0) {
                $b->where("({$prefix}_data.klass_jilia=?)", (int)$params['klass_jilia']);
            } else {
                unset($params['klass_jilia']);
            }

            // rc (точное число комнат, альтернативный параметр)
            if (isset($params['rc']) && (int)$params['rc'] > 0) {
                $b->where("({$prefix}_data.room_count=?)", (int)$params['rc']);
            } else {
                unset($params['rc']);
            }

            // naberegnay (select)
            if (isset($params['naberegnay']) && (int)$params['naberegnay'] > 0) {
                $b->where("({$prefix}_data.naberegnay=?)", (int)$params['naberegnay']);
            } else {
                unset($params['naberegnay']);
            }
        };

        // ── Reservation exclusion (start_date / end_date) ──
        $this->filters['reservation_dates'] = function (&$params, GridQueryBuilder $b, $model) use ($prefix) {
            // Нормализуем даты: 2026-8-5 → 2026-08-05
            foreach (['start_date', 'end_date'] as $key) {
                if (isset($params[$key]) && preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $params[$key], $m)) {
                    $params[$key] = sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
                }
            }
            if (
                isset($params['start_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $params['start_date']) &&
                isset($params['end_date'])   && preg_match('/^\d{4}-\d{2}-\d{2}$/', $params['end_date'])
            ) {
                $vsd = $params['start_date'] . ' 12:00:00';
                $ved = $params['end_date']   . ' 12:00:00';

                $DBC = \DBC::getInstance();
                $stmt = $DBC->query(
                    'SELECT object_id FROM ' . $prefix . '_reservation WHERE is_validated=1 AND NOT(start_date>=? OR end_date<=?)',
                    [$ved, $vsd]
                );

                $ids = [];
                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        $ids[] = (int)$ar['object_id'];
                    }
                }

                if (!empty($ids)) {
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));
                    $b->where("({$prefix}_data.id NOT IN ({$placeholders}))", ...$ids);
                }
            } else {
                unset($params['start_date'], $params['end_date']);
            }
        };

        // ── pricetype (price_weekdays / price_weekends / etc.) ─
        $this->filters['pricetype'] = function (&$params, GridQueryBuilder $b, $model) use ($prefix) {
            if (!isset($params['pricetype'])) {
                return;
            }
            $priceMin = isset($params['price_from']) ? (int)$params['price_from'] : 0;
            $priceMax = isset($params['price_to'])   ? (int)$params['price_to']   : 0;

            $fieldMap = [
                '1' => 'price_weekdays',
                '2' => 'price_weekends',
                '3' => 'price_newyear',
                '4' => 'price_month',
            ];

            $pricefield = $fieldMap[(string)$params['pricetype']] ?? '';

            if ($pricefield !== '') {
                if ($priceMin > 0) {
                    $b->where("({$prefix}_data.`{$pricefield}`*1 >= ?)", $priceMin);
                }
                if ($priceMax > 0) {
                    $b->where("({$prefix}_data.`{$pricefield}`*1 <= ?)", $priceMax);
                }
            }

            // Если pricetype задан, стандартный price-фильтр pipeline не должен мешать
            unset($params['price_from'], $params['price_to']);
        };

        // ── Model-based checkbox fields (generic) ──
        // Handles any checkbox field defined in the data model (e.g. billiard, sauna, tennis, etc.)
        // Fields already handled by dedicated filters (active, archived) are skipped.
        $skippedCheckboxFields = ['active', 'archived'];
        $this->filters['model_checkboxes'] = function (&$params, GridQueryBuilder $b, $model) use ($prefix, $skippedCheckboxFields) {
            if (empty($model)) {
                return;
            }
            foreach ($model as $fieldName => $fieldDef) {
                if (!isset($fieldDef['type']) || $fieldDef['type'] !== 'checkbox') {
                    continue;
                }
                if (in_array($fieldName, $skippedCheckboxFields, true)) {
                    continue;
                }
                if (!isset($params[$fieldName])) {
                    continue;
                }
                $val = (int)$params[$fieldName];
                if ($val === 1) {
                    $b->where("({$prefix}_data.`{$fieldName}`=1)");
                } elseif ($val === -1) {
                    $b->where("({$prefix}_data.`{$fieldName}` <> 1)");
                } else {
                    unset($params[$fieldName]);
                }
            }
        };
    }
}
