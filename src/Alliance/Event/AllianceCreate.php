<?php

namespace EtoA\Alliance\Event;

use Symfony\Component\EventDispatcher\Event;

class AllianceCreate extends Event
{
    const CREATE_SUCCESS = 'alliance.create.success';
}
