<?php

namespace EtoA\Quest\Progress\InitFunctions;

use EtoA\User\UserRepository;
use LittleCubicleGames\Quests\Entity\QuestInterface;
use LittleCubicleGames\Quests\Entity\TaskInterface;
use LittleCubicleGames\Quests\Progress\Functions\InitProgressHandlerFunctionInterface;

class HaveSpecialistType implements InitProgressHandlerFunctionInterface
{
    const NAME = 'have-specialist-type';

    /** @var UserRepository */
    private $userRepository;
    /** @var int */
    private $specialistId;

    public function __construct($attributes, UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->specialistId = $attributes['specialist_id'];
    }

    public function initProgress(QuestInterface $quest, TaskInterface $task)
    {
        return (int)($this->userRepository->getSpecialistId($quest->getUser()) === $this->specialistId);
    }
}
