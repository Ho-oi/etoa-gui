<?php

namespace EtoA\Quest\Progress\InitFunctions;

use EtoA\Technology\TechnologyRepository;
use LittleCubicleGames\Quests\Entity\QuestInterface;
use LittleCubicleGames\Quests\Entity\TaskInterface;
use LittleCubicleGames\Quests\Progress\Functions\InitProgressHandlerFunctionInterface;

class HaveTechnologyLevel implements InitProgressHandlerFunctionInterface
{
    const NAME = 'have-technology-level';

    /** @var TechnologyRepository */
    private $technologyRepository;
    /** @var int */
    private $buildingId;

    public function __construct(array $attributes, TechnologyRepository $technologyRepository)
    {
        $this->technologyRepository = $technologyRepository;
        $this->buildingId = $attributes['technology_id'];
    }

    public function initProgress(QuestInterface $quest, TaskInterface $task)
    {
        return $this->technologyRepository->getTechnologyLevel($quest->getUser(), $this->buildingId);
    }
}
