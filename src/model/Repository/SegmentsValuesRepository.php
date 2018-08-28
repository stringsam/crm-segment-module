<?php

namespace Crm\SegmentModule\Repository;

use Crm\ApplicationModule\Repository;
use Nette\Database\Table\IRow;

class SegmentsValuesRepository extends Repository
{
    protected $tableName = 'segments_values';

    public function add(IRow $segment, $date, $value)
    {
        return $this->insert([
            'segment_id' => $segment->id,
            'date' => $date,
            'value' => $value,
        ]);
    }
}
