<?php

namespace EtoA\Quest;

use EtoA\Quest\Entity\Quest;
use EtoA\WebTestCase;
use LittleCubicleGames\Quests\Workflow\QuestDefinitionInterface;

class QuestControllerTest extends WebTestCase
{
    public function testAdvance()
    {
        $userId = 1;
        $quest = new Quest(null, 99, $userId, 'merchant', QuestDefinitionInterface::STATE_AVAILABLE, []);
        $this->app['etoa.quest.repository']->save($quest);
        $this->app['cubicle.quests.quests'] = [99 => ['id' => 99, 'task' => ['operator' => 'equal-to', 'value' => 10, 'type' => 'hire-specialist']]];

        $this->loginUser($userId);

        $client = self::createClient();
        $client->request('PUT', sprintf('/api/quests/%s/advance/%s', $quest->getId(), QuestDefinitionInterface::TRANSITION_START));

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('ok', $data['status']);
        $this->assertSame(QuestDefinitionInterface::STATE_IN_PROGRESS, $data['state']);
    }
}
