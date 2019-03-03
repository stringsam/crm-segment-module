<?php

namespace Crm\SegmentModule\Commands;

use Crm\SegmentModule\Repository\SegmentsValuesRepository;
use DateInterval;
use Nette\Utils\DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CompressSegmentsValues extends Command
{
    const COMPRESSION_THRESHOLD = '3 months';

    private $segmentsValuesRepository;

    public function __construct(
        SegmentsValuesRepository $segmentsValuesRepository
    ) {
        parent::__construct();
        $this->segmentsValuesRepository = $segmentsValuesRepository;
    }

    protected function configure()
    {
        $this->setName('segment:compress_segments_values');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('');
        $output->writeln('<info>***** COMPRESSING SEGMENTS VALUES *****</info>');
        $output->writeln('');

        $min = $this->segmentsValuesRepository->getTable()->select('MIN(`date`) min_date')->fetch();
        if (!$min) {
            $output->writeln('No segments values to compress, quitting.');
            return;
        }

        /** @var DateTime $minDate */
        $minDate = $min->min_date;
        $maxDate = (new DateTime())->sub(DateInterval::createFromDateString(self::COMPRESSION_THRESHOLD));

        $output->writeln('Checking if segments_values table contains uncompressed values');

        // Bisect search to select earliest date where compression should start
        $earliestUncompressedDate = $this->findEarliestUncompressed(clone $minDate, clone $maxDate);
        if (!$earliestUncompressedDate) {
            $output->writeln('No segments values to compress, quitting.');
            return;
        }

        $totalDeleted = $this->compress($earliestUncompressedDate, $maxDate, $output);

        $output->writeln("Compressing finished, $totalDeleted record(s) deleted.");
    }

    private function findEarliestUncompressed(DateTime $left, DateTime $right)
    {
        $left->setTime(0, 0, 0, 0);
        $right->setTime(0, 0, 0, 0);

        if ($left > $right) {
            return false;
        }

        if ($left == $right && !$this->hasCompression($left)) {
            return $left;
        }

        // Compute mid date
        $midPoint = (int) (($left->getTimestamp() + $right->getTimestamp())/2);
        $midDate = DateTime::from($midPoint)->setTime(0, 0, 0, 0);

        $oneDay = DateInterval::createFromDateString('1 day');

        // Check if mid date has compressed values and decide which half-interval to explore next
        if ($this->hasCompression($midDate)) {
            return $this->findEarliestUncompressed($midDate->add($oneDay), $right);
        }
        return $this->findEarliestUncompressed($left, $midDate);
    }

    private function hasCompression(DateTime $midDate): bool
    {
        $uncompressedCount = $this->segmentsValuesRepository->getTable()
            ->select('COUNT(*)')
            ->where('DATE(`date`) = ?', $midDate->format('Y-m-d'))
            ->group('HOUR(`date`), segment_id')
            ->having('COUNT(*) > 1')
            ->count();

        return $uncompressedCount === 0;
    }

    private function compress(DateTime $start, DateTime $end, OutputInterface $output): int
    {
        $deleteSql = <<<SQL
delete s1 from segments_values s1
left join (
    
    select min(id) as id
    from    segments_values
    where `date` >= ? and `date` <= ?
    group by DATE(`date`), HOUR(`date`), segment_id

) s2 on s1.id = s2.id
where `date` >= ? and `date` <= ? and s2.id is null
SQL;

        $iteratorStart = clone $start;
        $interval = DateInterval::createFromDateString('2 weeks');

        $totalDeleted = 0;

        while ($iteratorStart < $end) {
            $iteratorEnd = (clone $iteratorStart)->add($interval);

            $output->writeln("Compressing values between $iteratorStart and $iteratorEnd");

            $results = $this->segmentsValuesRepository
                ->getDatabase()
                ->query($deleteSql, $iteratorStart, $iteratorEnd, $iteratorStart, $iteratorEnd);
            $totalDeleted += $results->getRowCount();

            $iteratorStart->add($interval);
        }

        return $totalDeleted;
    }
}
