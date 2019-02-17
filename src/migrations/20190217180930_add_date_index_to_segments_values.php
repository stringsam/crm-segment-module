<?php


use Phinx\Migration\AbstractMigration;

class AddDateIndexToSegmentsValues extends AbstractMigration
{
    public function change()
    {
        $this->table('segments_values')
            ->addIndex('date')
            ->save();
    }
}
