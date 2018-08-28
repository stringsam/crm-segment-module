<?php

namespace Crm\SegmentModule\Repository;

use Crm\ApplicationModule\Repository;
use Nette\Utils\DateTime;

class SegmentGroupsRepository extends Repository
{
    protected $tableName = 'segment_groups';

    public function all()
    {
        return $this->getTable()->order('sorting ASC');
    }

    public function add($name, $sorting = 100)
    {
        return $this->insert([
            'name' => $name,
            'sorting' => $sorting,
            'created_at' => new DateTime(),
        ]);
    }

    public function exists($name)
    {
        return $this->getTable()->where(['name' => $name])->count('*') > 0;
    }

    public function load($name)
    {
        return $this->getTable()->where(['name' => $name])->fetch();
    }
}
