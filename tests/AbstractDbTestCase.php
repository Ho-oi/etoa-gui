<?php

namespace EtoA;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Pimple\Container;

class AbstractDbTestCase extends TestCase
{
    use DbTestTrait;

    /** @var Container */
    protected $app;
    /** @var Connection */
    protected $connection;

    protected function setUp()
    {
        parent::setUp();

        $this->app = $this->setupApplication();
        $this->connection = $this->app['db'];
    }
}
