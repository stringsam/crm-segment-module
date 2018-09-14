<?php

namespace Crm\SegmentModule\Params;

class NumberParam extends BaseParam
{
    protected $type = 'number';

    public function escapedConditions($key): array
    {
        $where = [];
        foreach ($this->data as $operator => $value) {
            if ($operator == 'gt') {
                $where[] = " {$key} > {$value} ";
            } elseif ($operator == 'gte') {
                $where[] = " {$key} >= {$value} ";
            } elseif ($operator == 'lt') {
                $where[] = " {$key} < {$value} ";
            } elseif ($operator == 'lte') {
                $where[] = " {$key} <= {$value} ";
            } elseif ($operator == 'eq') {
                $where[] = " {$key} = {$value} ";
            }
        }
        return $where;
    }

    public function title(): string
    {
        $title = '';
        foreach ($this->data as $operator => $value) {
            if ($operator == 'gt') {
                $title .= " greater {$value}";
            } elseif ($operator == 'gte') {
                $where[] = " greater {$value} (inclusive)";
            } elseif ($operator == 'lt') {
                $where[] = " under {$value}";
            } elseif ($operator == 'lte') {
                $where[] = " under {$value} (inclusive)";
            } elseif ($operator == 'eq') {
                $where[] = " equal {$value}";
            }
        }
        return $title;
    }

    public function isValid($data): Validation
    {
        foreach ($data as $operator => $value) {
            if (!in_array($operator, ['gt', 'gte', 'lt', 'lte', 'eq'])) {
                return new Validation("Invalid operator '{$operator}'");
            }
            if (!is_int($value)) {
                return new Validation("Invalid number format '{value}'");
            }
        }
        return new Validation();
    }

    public function equals(BaseParam $param): bool
    {
        if (!($param instanceof static)) {
            throw new \Exception("Cannot compare " . get_class($param) . ' with NumberParam');
        }
        return $param->escapedConditions($param->key()) === $this->escapedConditions($this->key());
    }
}
