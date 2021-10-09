<?php

namespace App\Core\Database;

use App\Core\App;

class QueryBuilder
{
    public const SELECT_TYPE = 'select';
    public const INSERT_TYPE = 'insert';
    public const UPDATE_TYPE = 'update';
    public const DELETE_TYPE = 'delete';


    private Database $db;
    private string $type;
    private array $fields;
    private ?string $table = null;
    private ?string $alias = null;
    private array $join = [];
    private array $on = [];
    private array $values = [];
    private array $where = [];
    private ?string $limit = null;
    private ?string $order = null;


    public function __construct()
    {
        $app = new App();
        $this->db = $app->getDb();
    }


    public function select(string ...$fields): self
    {
        $this->fields = $fields;
        $this->type = self::SELECT_TYPE;
        return $this;
    }


    public function from(string $table, ?string $alias = null): self
    {
        if ($alias) {
            $this->alias = $alias;
        }
        $this->table = $table;

        return $this;
    }


    public function join(string $table, array $on): self
    {
        $this->join[] = $table;
        $this->on[] = $on;

        return $this;
    }


    public function insert(string $table): self
    {
        $this->table = $table;
        $this->type = self::INSERT_TYPE;
        return $this;
    }


    public function update(string $table): self
    {
        $this->table = $table;
        $this->type = self::UPDATE_TYPE;
        return $this;
    }


    public function delete(string $table): self
    {
        $this->table = $table;
        $this->type = self::DELETE_TYPE;
        return $this;
    }


    public function setValues(array $values): self
    {
        $this->values = $values;
        return $this;
    }


    public function where(string ...$condition): self
    {
        $this->where = array_merge($this->where, $condition);
        return $this;
    }


    public function limit(int $offset, ?int $limit = null): self
    {
        if ($limit) {
            $this->limit = $offset.', '.$limit;
        } else {
            $this->limit = (string)$offset;
        }
        return $this;
    }


    public function orderBy(string $field, ?string $order = null): self
    {
        if ($order) {
            $this->order = $field.' '.$order;
        } else {
            $this->order = $field;
        }

        return $this;
    }


    private function getQuery(): string
    {
        $parts = ['SELECT'];
        if ($this->fields) {
            $parts[] = join(', ', $this->fields);
        } else {
            $parts[] = '*';
        }

        $parts[] = 'FROM';
        $parts[] = $this->buildFrom();

        for ($i = 0; $i < count($this->join); $i++) {
            $relatedTable = ($i === 0) ? $this->table : $this->join[$i-1];
            $parts[] = 'JOIN';
            $parts[] = $this->join[$i];
            $parts[] = 'ON '.$relatedTable.'.'.$this->on[$i][0].'='.$this->join[$i].'.'.$this->on[$i][1];
        }

        if (!empty($this->where)) {
            $parts[] = 'WHERE';
            $parts[] = '('.join(') AND (', $this->where).')';
        }

        if (!empty($this->limit)) {
            $parts[] = 'LIMIT '.$this->limit;
        }

        if (!empty($this->order)) {
            $parts[] = 'ORDER BY '.$this->order;
        }

        return join(' ', $parts);
    }


    private function getInsert(): array
    {
        $parts = ['INSERT INTO'];
        $parts[] = $this->table;

        $fields = [];
        $replacements = [];
        $values = [];
        foreach ($this->values as $key => $value) {
            $fields[] = $key;
            $replacements[] = '?';
            $values[$key] = $value;
        }

        $parts[] = '('.join(', ', $fields).')';
        $parts[] = 'VALUES';
        $parts[] = '('.join(', ', $replacements).')';

        return [join(' ', $parts), $values];
    }


    private function getUpdate(): array
    {
        $parts = ['UPDATE'];
        $parts[] = $this->table;

        $fields = [];
        $values = [];
        foreach ($this->values as $key => $value) {
            $fields[] = $key.' = ?';
            $values[$key] = $value;
        }

        $parts[] = 'SET';
        $parts[] = join(', ', $fields);

        if (!empty($this->where)) {
            $parts[] = 'WHERE';
            $parts[] = '('.join(') AND (', $this->where).')';
        }

        return [join(' ', $parts), $values];
    }


    private function getDelete(): string
    {
        $parts = ['DELETE FROM'];
        $parts[] = $this->table;

        $parts[] = 'WHERE';
        $parts[] = '('.join(') AND (', $this->where).')';

        return join(' ', $parts);
    }


    private function buildFrom(): string
    {
        if ($this->alias !== null) {
            $from = $this->table.' AS '.$this->alias;
        } else {
            $from = $this->table;
        }

        return $from;
    }


    /**
     * @return array|bool|int|mixed|null
     */
    public function execute(string $class = null)
    {
        if ($this->type === self::SELECT_TYPE) {
            $query = $this->getQuery();
            if ($this->limit == 1) {
                return $this->db->query($query, $class, true);
            } else {
                return $this->db->query($query, $class);
            }
        } elseif ($this->type === self::INSERT_TYPE) {
            $query = $this->getInsert();
            $this->db->prepareBool($query[0], $query[1]);
            return $this->db->lastInsertId();
        } elseif ($this->type === self::UPDATE_TYPE) {
            $query = $this->getUpdate();
            return $this->db->prepareBool($query[0], $query[1]);
        } elseif ($this->type === self::DELETE_TYPE) {
            $query = $this->getDelete();
            return $this->db->prepareBool($query);
        }

        return null;
    }
}
