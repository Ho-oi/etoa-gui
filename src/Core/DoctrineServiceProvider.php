<?php

namespace EtoA\Core;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Query\QueryBuilder;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class DoctrineServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['db.default_options'] = [
            'driver' => 'pdo_mysql',
            'dbname' => null,
            'host' => 'localhost',
            'user' => 'root',
            'password' => null,
        ];

        $pimple['db.config'] = function (Container $pimple) {
            $configuration = new Configuration();
            $configuration->setSQLLogger(new SqlLogger($pimple['logger']));

            return $configuration;
        };

        $pimple['db.event_manager'] = function () {
            new EventManager();
        };

        $pimple['db'] = function (Container $pimple) {
            if (!isset($pimple['db.options']) && isset($pimple['db.options.file'])) {
                $config = json_decode(file_get_contents($pimple['app.config_dir'].$pimple['db.options.file']), true);
                if (json_last_error() != JSON_ERROR_NONE) {
                    throw new \InvalidArgumentException(sprintf(
                        'Failed to parse config file %s (JSON error %s)!',
                        $pimple['db.options.file'],
                        json_last_error_msg()
                    ));
                }

                $pimple['db.options'] = $config;
            } elseif (!isset($pimple['db.options'])) {
                $pimple['db.options'] = [];
            }

            $options = array_replace($pimple['db.default_options'], $pimple['db.options']);
            if ($pimple['app.environment'] === 'testing') {
                $options['dbname'] = $options['dbname'] . '_test';
            }

            \DBManager::getInstance()->setDatabaseConfig($options);

            return DriverManager::getConnection($options, $pimple['db.config'], $pimple['db.event_manager']);
        };

        $pimple['db.querybuilder'] = function (Container $pimple) {
            return new QueryBuilder($pimple['db']);
        };

        $pimple['db.cache'] = function () {
            return new ArrayCache();
        };
    }
}
