<?php

namespace EtoA\Race;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class RaceServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $pimple)
    {
        $pimple['etoa.race.datarepository'] = function (Container $pimple) {
            return new RaceDataRepository($pimple['db']);
        };
    }
}
