<?php

namespace EtoA\Planet\Event;

use Symfony\Component\EventDispatcher\Event;

class PlanetRename extends Event
{
    const RENAME_SUCCESS = 'planet.rename.success';
}
