<?php

namespace Crm\SegmentModule\Api;

use Crm\ApiModule\Api\JsonResponse;
use Crm\ApiModule\Authorization\ApiAuthorizationInterface;
use Crm\ApiModule\Params\InputParam;
use Crm\ApiModule\Params\ParamsProcessor;
use Crm\ApiModule\Api\ApiHandler;
use Crm\SegmentModule\Criteria\Generator;
use Crm\SegmentModule\Repository\SegmentGroupsRepository;
use Crm\SegmentModule\Segment;
use Crm\SegmentModule\SegmentQuery;
use Nette\Database\Context;
use Nette\Http\Response;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

class CountsHandler extends ApiHandler
{
    private $segmentGroupsRepository;

    private $generator;

    private $context;

    public function __construct(
        SegmentGroupsRepository $segmentGroupsRepository,
        Context $context,
        Generator $generator
    ) {
        $this->segmentGroupsRepository = $segmentGroupsRepository;
        $this->context = $context;
        $this->generator = $generator;
    }

    public function params()
    {
        return [
            new InputParam(InputParam::TYPE_GET, 'table_name', InputParam::REQUIRED),
        ];
    }

    public function handle(ApiAuthorizationInterface $authorization)
    {
        $paramsProcessor = new ParamsProcessor($this->params());
        if ($paramsProcessor->isError()) {
            $response = new JsonResponse(['status' => 'error', 'message' => 'Invalid params']);
            $response->setHttpCode(Response::S400_BAD_REQUEST);
            return $response;
        }
        $params = $paramsProcessor->getValues();

        $inputJson = file_get_contents("php://input");
        if (!$inputJson) {
            $response = new JsonResponse(['status' => 'error', 'message' => 'Empty post input']);
            $response->setHttpCode(Response::S400_BAD_REQUEST);
            return $response;
        }

        try {
            $jsonData = Json::decode($inputJson, JSON::FORCE_ARRAY);
        } catch (JsonException $e) {
            $response = new JsonResponse(['status' => 'error', 'message' => 'Wrong json format']);
            $response->setHttpCode(Response::S400_BAD_REQUEST);
            return $response;
        }

        $queryString = $this->generator->process($params['table_name'], $jsonData);

        $query = new SegmentQuery($queryString, $params['table_name'], $params['table_name'] . '.id');
        $segment = new Segment($this->context, $query);
        $count = $segment->totalCount();

        $response = new JsonResponse(['status' => 'ok', 'count' => $count]);
        $response->setHttpCode(Response::S200_OK);

        return $response;
    }
}
