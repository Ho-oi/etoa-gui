<?php

$app = new \Silex\Application([
    'debug' => isset($debug) ? $debug : false,
    'app.environment' => isset($environment) ? $environment : 'production',
    'app.root' => dirname(__DIR__),
    'app.config_dir' => sprintf('%s/htdocs/config/', dirname(__DIR__)),
    'db.options.file' => 'db.conf',
]);
$app->register(new \EtoA\Core\MonologServiceProvider());
$app->register(new Silex\Provider\ServiceControllerServiceProvider());

// register error handler
//\Monolog\ErrorHandler::register($app['logger']);

$app->register(new \LittleCubicleGames\Quests\ServiceProvider());

$app->register(new \EtoA\Building\BuidingServiceProvider());
$app->register(new \EtoA\Core\DoctrineServiceProvider());
$app->register(new \EtoA\Core\ParamConverterServiceProvider());
$app->register(new \EtoA\Core\SessionServiceProvider());
$app->register(new \EtoA\Defense\DefenseServiceProvider());
$app->register(new \EtoA\Missile\MissileServiceProvider());
$app->register(new \EtoA\Race\RaceServiceProvider());
$app->register(new \EtoA\Planet\PlanetServiceProvider());
$app->register($questProvider = new \EtoA\Quest\QuestServiceProvider(), [
    'etoa.quests.enabled' => isset($questSystemEnabled) ? $questSystemEnabled : true,
    'cubicle.quests.slots' => [
        [
            'id' => 'test',
            'registry' => 'test',
        ],
    ],
    'cubicle.quests.quests' => require __DIR__ . '/../data/quests.php',
]);
$app->register(new \EtoA\Ship\ShipServiceProvider());
$app->register(new \EtoA\Technology\TechnologyServiceProvider());
$app->register($tutorialProvider = new \EtoA\Tutorial\TutorialServiceProvider());
$app->register(new \EtoA\User\UserServiceProvider());

$app->mount('/', $questProvider);
$app->mount('/', $tutorialProvider);

return $app;
