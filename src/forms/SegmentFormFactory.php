<?php

namespace Crm\SegmentModule\Forms;

use Crm\SegmentModule\Criteria\Generator;
use Crm\SegmentModule\Repository\SegmentGroupsRepository;
use Crm\SegmentModule\Repository\SegmentsRepository;
use Kdyby\Translation\Translator;
use Nette\Application\UI\Form;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Tomaj\Form\Renderer\BootstrapRenderer;

class SegmentFormFactory
{
    private $segmentsRepository;

    private $segmentGroupsRepository;

    public $onUpdate;

    public $onSave;

    private $generator;

    private $translator;

    public function __construct(
        SegmentsRepository $segmentsRepository,
        SegmentGroupsRepository $segmentGroupsRepository,
        Generator $generator,
        Translator $translator
    ) {
        $this->segmentsRepository = $segmentsRepository;
        $this->segmentGroupsRepository = $segmentGroupsRepository;
        $this->generator = $generator;
        $this->translator = $translator;
    }

    public function create($id)
    {
        $defaults = [];
        if (isset($id)) {
            $segment = $this->segmentsRepository->find($id);
            $defaults = $segment->toArray();
        }

        $form = new Form;

        $form->setRenderer(new BootstrapRenderer());
        $form->setTranslator($this->translator);
        $form->addProtection();

        $form->addText('name', 'segment.fields.name')
            ->setRequired('segment.required.name')
            ->setAttribute('placeholder', 'segment.placeholder.name');

        $form->addSelect('version', 'segment.fields.version', ['1' => '1', '2' => '2'])
            ->setRequired('segment.required.name');

        $form->addText('code', 'segment.fields.code')
            ->setRequired('segment.required.code')
            ->setAttribute('placeholder', 'segment.placeholder.code');

        $form->addSelect('segment_group_id', 'segment.fields.segment_group_id', $this->segmentGroupsRepository->all()->fetchPairs('id', 'name'));

        $form->addText('table_name', 'segment.fields.table_name')
            ->setRequired('segment.required.table_name')
            ->setAttribute('placeholder', 'segment.placeholder.table_name');

        $form->addTextArea('query_string', 'segment.fields.query_string', 30, 10)
            ->getControlPrototype()
                ->addAttributes(['class' => 'ace', 'data-lang' => 'sql']);

        $form->addTextArea('fields', 'segment.fields.query_fields', 30, 3)
            ->setRequired()
            ->getControlPrototype()
                ->addAttributes(['class' => 'ace', 'data-lang' => 'sql']);

        $form->addHidden('segment_id', $id);

        $form->addTextArea('criteria', 'segment.fields.criteria', 30, 8);

        $form->setDefaults($defaults);

        $form->addSubmit('send', $this->translator->translate('system.save'))
            ->getControlPrototype()
            ->setName('button')
            ->setHtml('<i class="fa fa-save"></i> ' . $this->translator->translate('system.save'));

        $form->onSuccess[] = [$this, 'formSucceeded'];
        return $form;
    }

    public function formSucceeded($form, $values)
    {
        $id = $values['segment_id'];
        unset($values['segment_id']);

        if (isset($values['criteria']) && $values['criteria']) {
            try {
                $parsedCriteria = Json::decode($values['criteria'], Json::FORCE_ARRAY);
            } catch (JsonException $ex) {
                $form['criteria']->addError($ex->getMessage());
                return;
            }

            $values['query_string'] = $this->generator->process($values['table_name'], $parsedCriteria);
        }

        if ($id) {
            $row = $this->segmentsRepository->find($id);
            $this->segmentsRepository->update($row, $values);
            $this->onUpdate->__invoke($row);
        } else {
            $group = $this->segmentGroupsRepository->find($values['segment_group_id']);
            $row = $this->segmentsRepository->add($values['name'], $values['version'], $values['code'], $values['table_name'], $values['fields'], $values['query_string'], $group, $values['criteria'] ? $values['criteria'] : null);
            $this->onSave->__invoke($row);
        }
    }
}
