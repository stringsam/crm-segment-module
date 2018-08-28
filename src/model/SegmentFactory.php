<?php

namespace Crm\SegmentModule;

use Crm\SegmentModule\Repository\SegmentsRepository;
use Nette\Database\Context;
use Nette\UnexpectedValueException;

class SegmentFactory
{
    private $segmentsRepository;

    private $context;

    public function __construct(Context $context, SegmentsRepository $segmentsRepository)
    {
        $this->context = $context;
        $this->segmentsRepository = $segmentsRepository;
    }

    public function buildSegment($segmentIdentifier)
    {
        $segmentRow = $this->segmentsRepository->findBy('code', $segmentIdentifier);
        if (!$segmentRow) {
            throw new UnexpectedValueException("Segment $segmentIdentifier doesnt exists");
        }
        $query = new SegmentQuery($segmentRow->query_string, $segmentRow->table_name, $segmentRow->fields);
        $segment = new Segment($this->context, $query);
        return $segment;
    }
}
