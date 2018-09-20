<?php

namespace Crm\SegmentModule\Api;

use Crm\ApiModule\Api\ApiHandler;
use Crm\ApiModule\Api\JsonResponse;
use Crm\ApiModule\Authorization\ApiAuthorizationInterface;
use Crm\ApiModule\Params\InputParam;
use Crm\ApiModule\Params\ParamsProcessor;
use Crm\SegmentModule\Repository\SegmentsRepository;
use Nette\Database\Table\ActiveRow;
use Nette\Http\Response;

class ListApiHandler extends ApiHandler
{
    private $segmentsRepository;

    public function __construct(SegmentsRepository $segmentsRepository)
    {
        $this->segmentsRepository = $segmentsRepository;
    }

    /**
     * @inheritdoc
     */
    public function params()
    {
        return [
            new InputParam(InputParam::TYPE_GET, 'group_id', InputParam::OPTIONAL),
        ];
    }

    /**
     * @param ApiAuthorizationInterface $authorization
     * @return \Nette\Application\IResponse
     */
    public function handle(ApiAuthorizationInterface $authorization)
    {
        $paramsProcessor = new ParamsProcessor($this->params());
        if ($paramsProcessor->isError()) {
            $response = new JsonResponse(['status' => 'error', 'message' => 'Invalid params']);
            $response->setHttpCode(Response::S400_BAD_REQUEST);
            return $response;
        }
        $params = $paramsProcessor->getValues();

        $table = $this->segmentsRepository->getTable();
        if (isset($params['group_id'])) {
            $table = $table->where(['segment_group_id' => $params['group_id']]);
        }

        $segments = [];
        /** @var ActiveRow $segment */
        foreach ($table->fetchAll() as $segment) {
            if ($segment->table_name != 'users') {
                continue;
            }
            $segments[] = [
                'code' => $segment->code,
                'name' => $segment->name,
                'group' => [
                    'id' => $segment->segment_group->id,
                    'name' => $segment->segment_group->name,
                    'sorting' => $segment->segment_group->sorting,
                ],
            ];
        }

        $response = new JsonResponse(['status' => 'ok', 'segments' => $segments]);
        $response->setHttpCode(Response::S200_OK);

        return $response;
    }
}
