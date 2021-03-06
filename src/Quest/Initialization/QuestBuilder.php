<?php

namespace EtoA\Quest\Initialization;

use EtoA\Quest\Entity\Task;
use LittleCubicleGames\Quests\Definition\Quest\Quest;
use LittleCubicleGames\Quests\Definition\Slot\Slot;
use LittleCubicleGames\Quests\Initialization\QuestBuilderInterface;
use LittleCubicleGames\Quests\Workflow\QuestDefinitionInterface;

class QuestBuilder implements QuestBuilderInterface
{
    public function buildQuest(Quest $quest, Slot $slot, $userId)
    {
        $tasks = [];
        foreach ($quest->getTaskIds() as $taskId) {
            $tasks[] = new Task(null, $taskId, 0);
        }

        return new \EtoA\Quest\Entity\Quest(null, $quest->getId(), $userId, $slot->getId(), QuestDefinitionInterface::STATE_AVAILABLE, $tasks);
    }
}
