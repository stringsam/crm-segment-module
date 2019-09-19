<?php

use Phinx\Migration\AbstractMigration;

class SegmentSoftDelete extends AbstractMigration
{
    public function change()
    {
        $this->table('segments')
            ->addColumn('deleted_at', 'timestamp', ['null' => true])
            ->update();
    }
}
