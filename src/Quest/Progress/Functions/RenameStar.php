<?php

namespace EtoA\Quest\Progress\Functions;

use EtoA\Galaxy\Event\StarRename;
use LittleCubicleGames\Quests\Entity\TaskInterface;
use LittleCubicleGames\Quests\Progress\Functions\EventHandlerFunctionInterface;

class RenameStar implements EventHandlerFunctionInterface
{
    const NAME = 'rename-star';

    public function handle(TaskInterface $task, StarRename $event)
    {
        return $task->getProgress() + 1;
    }

    public function getEventMap()
    {
        return [StarRename::RENAME_SUCCESS => 'handle'];
    }
}
