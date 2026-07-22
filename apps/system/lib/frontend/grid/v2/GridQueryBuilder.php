<?php
/**
 * Grid Query Builder V2
 *
 * Строит SQL-запросы для грида. Вместо монолитной конкатенации строк
 * используется builder pattern с чёткими этапами:
 *   select → join → where → order → limit
 *
 * Оптимизации:
 *  - Не генерирует лишние JOIN-ы, если поля не запрошены
 *  - Не делает запрос COUNT отдельно, когда total не нужен
 *  - Подготовленные запросы с параметрами (prepared statements)
 */

namespace system\lib\frontend\grid\v2;

class GridQueryBuilder
{
    /** @var string */
    private $table;

    /** @var string */
    private $prefix;

    /** @var string[] */
    public $selectColumns = [];

    /** @var string[] */
    private $leftJoins = [];

    /** @var string[] */
    private $whereConditions = [];

    /** @var array */
    private $whereValues = [];

    /** @var string */
    private $orderClause = '';

    /** @var int|null */
    private $limitOffset = null;

    /** @var int|null */
    private $limitCount = null;

    /** @var string[] Set для предотвращения дублирования JOIN-ов */
    private $joinKeys = [];

    /**
     * @param string $prefix DB-prefix (e.g. 're')
     * @param string $table  Main table (e.g. 'data')
     */
    public function __construct(string $prefix = '', string $table = 'data')
    {
        $this->prefix = $prefix ?: DB_PREFIX;
        $this->table = $table;
    }

    /**
     * Сброс состояния для повторного использования
     */
    public function reset(): self
    {
        $this->selectColumns = [];
        $this->leftJoins = [];
        $this->whereConditions = [];
        $this->whereValues = [];
        $this->orderClause = '';
        $this->limitOffset = null;
        $this->limitCount = null;
        $this->joinKeys = [];
        return $this;
    }

    /**
     * Имя основной таблицы с префиксом
     */
    public function mainTable(): string
    {
        return $this->prefix . '_' . $this->table;
    }

    // ─── SELECT ─────────────────────────────────────

    public function select(string ...$columns): self
    {
        foreach ($columns as $col) {
            $this->selectColumns[] = $col;
        }
        return $this;
    }

    public function selectAll(): self
    {
        $this->selectColumns[] = $this->mainTable() . '.*';
        return $this;
    }

    public function selectCount(): self
    {
        $this->selectColumns = ['COUNT(' . $this->mainTable() . '.id) AS total'];
        return $this;
    }

    // ─── JOIN ───────────────────────────────────────

    /**
     * Добавить LEFT JOIN с дедупликацией
     */
    public function leftJoin(string $table, string $on, string $alias = ''): self
    {
        $key = $alias ?: $table;
        if (isset($this->joinKeys[$key])) {
            return $this;
        }
        $this->joinKeys[$key] = true;

        $stmt = 'LEFT JOIN ' . $this->prefix . '_' . $table;
        if ($alias !== '') {
            $stmt .= ' ' . $alias;
        }
        $stmt .= ' ON ' . $on;

        $this->leftJoins[] = $stmt;
        return $this;
    }

    /**
     * Добавить произвольный JOIN (raw)
     */
    public function rawJoin(string $joinStatement, string $dedupKey = ''): self
    {
        $key = $dedupKey ?: md5($joinStatement);
        if (isset($this->joinKeys[$key])) {
            return $this;
        }
        $this->joinKeys[$key] = true;
        $this->leftJoins[] = $joinStatement;
        return $this;
    }

    // ─── WHERE ──────────────────────────────────────

    /**
     * Добавить условие WHERE с подготовленными значениями
     */
    public function where(string $condition, ...$values): self
    {
        $this->whereConditions[] = $condition;
        foreach ($values as $v) {
            $this->whereValues[] = $v;
        }
        return $this;
    }

    /**
     * Добавить IN-условие с автоматической генерацией плейсхолдеров
     */
    public function whereIn(string $column, array $values): self
    {
        if (empty($values)) {
            // Невозможное условие
            $this->whereConditions[] = '1=0';
            return $this;
        }

        $placeholders = implode(',', array_fill(0, count($values), '?'));
        $this->whereConditions[] = "({$column} IN ({$placeholders}))";
        $this->whereValues = array_merge($this->whereValues, array_values($values));
        return $this;
    }

    /**
     * Добавить массив условий (bulk)
     */
    public function whereArray(array $conditions, array $values = []): self
    {
        $this->whereConditions = array_merge($this->whereConditions, $conditions);
        $this->whereValues = array_merge($this->whereValues, $values);
        return $this;
    }

    // ─── ORDER ──────────────────────────────────────

    public function orderBy(string $order): self
    {
        $this->orderClause = $order;
        return $this;
    }

    // ─── LIMIT ──────────────────────────────────────

    public function limit(int $offset, int $count): self
    {
        $this->limitOffset = $offset;
        $this->limitCount = $count;
        return $this;
    }

    public function noLimit(): self
    {
        $this->limitOffset = null;
        $this->limitCount = null;
        return $this;
    }

    // ─── BUILD ──────────────────────────────────────

    /**
     * Собрать итоговый SQL
     */
    public function build(): string
    {
        $sql = 'SELECT ' . implode(', ', $this->selectColumns);
        $sql .= ' FROM ' . $this->mainTable();

        if (!empty($this->leftJoins)) {
            $sql .= ' ' . implode(' ', $this->leftJoins);
        }

        if (!empty($this->whereConditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->whereConditions);
        }

        if ($this->orderClause !== '') {
            $sql .= ' ORDER BY ' . $this->orderClause;
        }

        if ($this->limitOffset !== null && $this->limitCount !== null) {
            $sql .= ' LIMIT ' . $this->limitOffset . ', ' . $this->limitCount;
        }

        return $sql;
    }

    /**
     * Получить массив значений для prepared statement
     */
    public function getValues(): array
    {
        return $this->whereValues;
    }

    /**
     * Получить WHERE-часть (для подзапросов и COUNT)
     */
    public function getWhereClause(): string
    {
        if (empty($this->whereConditions)) {
            return '';
        }
        return ' WHERE ' . implode(' AND ', $this->whereConditions);
    }

    /**
     * Получить JOIN-часть
     */
    public function getJoinClause(): string
    {
        if (empty($this->leftJoins)) {
            return '';
        }
        return ' ' . implode(' ', $this->leftJoins);
    }

    /**
     * Собрать COUNT-запрос с текущими WHERE/JOIN
     */
    public function buildCount(): string
    {
        $sql = 'SELECT COUNT(' . $this->mainTable() . '.id) AS total';
        $sql .= ' FROM ' . $this->mainTable();
        $sql .= $this->getJoinClause();
        $sql .= $this->getWhereClause();
        return $sql;
    }

    /**
     * Клонировать текущий state для отдельного запроса (напр. COUNT)
     */
    public function cloneForCount(): self
    {
        $clone = clone $this;
        $clone->selectColumns = ['COUNT(' . $this->mainTable() . '.id) AS total'];
        $clone->orderClause = '';
        $clone->limitOffset = null;
        $clone->limitCount = null;
        return $clone;
    }

    // ─── ГЕТТЕРЫ ────────────────────────────────────

    public function getSelectColumns(): array
    {
        return $this->selectColumns;
    }

    public function setSelectColumns(array $columns): void
    {
        $this->selectColumns = $columns;
    }

    public function getWhereConditions(): array
    {
        return $this->whereConditions;
    }

    public function getLeftJoins(): array
    {
        return $this->leftJoins;
    }
}
