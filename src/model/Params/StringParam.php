<?php

namespace Crm\SegmentModule\Params;

class StringParam extends BaseParam
{
    protected $type = 'string';

    public function value(): string
    {
        return $this->data;
    }

    public function isValid($data): Validation
    {
        $result = is_string($data);
        if (!$result) {
            return new Validation("Invalid string data '{$data}'");
        }
        return new Validation();
    }

    public function equals(BaseParam $param): bool
    {
        if (!($param instanceof static)) {
            throw new \Exception("Cannot compare " . get_class($param) . ' with StringParam');
        }
        return $param->value() == $this->value();
    }
}
