<?php

namespace EtoA\Core;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class MonologServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['logger'] = function (Container $pimple) {
            return $pimple['monolog'];
        };

        $pimple['monolog'] = function (Container $pimple) {
            $logger = new Logger('etoa');
            $logger->pushHandler($pimple['monolog.handler']);
            $logger->pushProcessor($pimple['monolog.processor.uid']);

            return $logger;
        };

        $pimple['monolog.formatter'] = function () {
            $output = '%channel%[%extra.uid%].%level_name%: %message% %context% %extra%';

            return new LineFormatter($output);
        };

        $pimple['monolog.handler'] = function (Container $pimple) {
            return new FingersCrossedHandler(
                $pimple['monolog.syslog.handler'],
                $pimple['debug'] ? Logger::DEBUG : Logger::WARNING
            );
        };

        $pimple['monolog.syslog.handler'] = function (Container $pimple) {
            $syslogHandler = new SyslogHandler('etoa', LOG_SYSLOG, Logger::DEBUG);
            $syslogHandler->setFormatter($pimple['monolog.formatter']);

            return $syslogHandler;
        };

        $pimple['monolog.processor.uid'] = function () {
            return new UidProcessor();
        };
    }
}
