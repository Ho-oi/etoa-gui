<?php

namespace EtoA;

use Doctrine\DBAL\Connection;

abstract class WebTestCase extends \Silex\WebTestCase
{
    use DbTestTrait;

    /** @var Connection */
    protected $connection;

    public function createApplication()
    {
        include_once dirname(__DIR__) . '/htdocs/inc/mysqli_polyfill.php';
        $app = $this->setupApplication();
        $this->connection = $app['db'];
        \mysql_connect($this->connection->getHost(), $this->connection->getUsername(), $this->connection->getPassword(), $this->connection->getDatabase());

        require_once __DIR__ . '/../htdocs/inc/bootstrap.inc.php';
        \Config::restoreDefaults();

        return $app;
    }

    public function loginUser($userId)
    {
        $loginTime = time();

        $this->connection
            ->createQueryBuilder()
            ->insert('users')
            ->values([
                'user_id' => ':userId',
                'user_setup' => ':setup',
            ])->setParameters([
                'userId' => $userId,
                'setup' => 1,
            ])->execute();

        $this->connection
            ->createQueryBuilder()
            ->insert('tutorial_user_progress')
            ->values([
                'tup_user_id' => ':userId',
                'tup_tutorial_id' => ':tutorialId',
                'tup_closed' => ':closed',
            ])->setParameters([
                'userId' => $userId,
                'tutorialId' => 2,
                'closed' => 1,
            ])->execute();

        $_SESSION = [];
        $_SESSION['user_id'] = 1;
        $_SESSION['time_login'] = $loginTime;
        $_SESSION['time_action'] = $loginTime;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] = 'testing';
        $this->connection
            ->createQueryBuilder()
            ->insert('user_sessions')
            ->values([
                'id' => ':sessionId',
                'user_id' => ':userId',
                'time_login' => ':loginTime',
                'user_agent' => ':userAgent',
            ])->setParameters([
                'sessionId' => session_id(),
                'userId' => $userId,
                'loginTime' => $loginTime,
                'userAgent' => $userAgent,
            ])->execute();
    }
}
