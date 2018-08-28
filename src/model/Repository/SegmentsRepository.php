<?php

namespace Crm\SegmentModule\Repository;

use Crm\ApplicationModule\Repository;
use DateTime;
use Nette\Database\Context;
use Nette\Database\Table\IRow;

class SegmentsRepository extends Repository
{
    protected $tableName = 'segments';

    public function __construct(Context $database)
    {
        parent::__construct($database);
    }

    public function all()
    {
        return $this->getTable()->order('name ASC');
    }

    public function add($name, $version, $code, $tableName, $fields, $queryString, IRow $group, $criteria = null)
    {
        $id = $this->insert([
            'name' => $name,
            'code' => $code,
            'version' => $version,
            'fields' => $fields,
            'query_string' => $queryString,
            'table_name' => $tableName,
            'created_at' => new DateTime(),
            'updated_at' => new DateTime(),
            'cache_count' => 0,
            'segment_group_id' => $group->id,
            'criteria' => $criteria,
        ]);
        return $this->find($id);
    }

    public function update(IRow &$row, $data)
    {
        $data['updated_at'] = new DateTime();
        return parent::update($row, $data);
    }

    public function exists($code)
    {
        return $this->getTable()->where('code', $code)->count('*') > 0;
    }

    public function findByCode($code)
    {
        return $this->getTable()->where('code', $code)->limit(1)->fetch();
    }
}
