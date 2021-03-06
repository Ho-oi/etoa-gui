<?php

namespace EtoA\Quest\Progress\Functions;

use EtoA\Fleet\Event\FleetLaunch;
use LittleCubicleGames\Quests\Entity\TaskInterface;
use LittleCubicleGames\Quests\Progress\Functions\EventHandlerFunctionInterface;

class LaunchFleet implements EventHandlerFunctionInterface
{
    const NAME = 'launch-fleet';

    public function handle(TaskInterface $task, FleetLaunch $event)
    {
        return $task->getProgress() + 1;
    }

    public function getEventMap()
    {
        return [FleetLaunch::LAUNCH_SUCCESS => 'handle'];
    }
}
