<?php

namespace EtoA\Core;

use EtoA\User\ChatUser;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class SessionServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{
    public function register(Container $pimple)
    {
    }

    public function boot(Application $app)
    {
        $app->before(function (Request $request) {
            $session = \UserSession::getInstance();
            if (strpos($request->attributes->get('_route'), 'api.chat') === 0) {
                $currentUser = $this->validateChatUser($session);
            } else {
                $currentUser = $this->validateUser($session);
            }

            $request->attributes->set('currentUser', $currentUser);
        }, Application::EARLY_EVENT);
    }

    private function validateChatUser(\UserSession $session)
    {
        if (!$session->chatValidate()) {
            throw new AccessDeniedHttpException();
        }

        return new ChatUser($session->user_id, $session->user_nick);
    }

    private function validateUser(\UserSession $session)
    {
        if (!$session->validate(0)) {
            throw new AccessDeniedHttpException();
        }

        global $cu;
        if (isset($cu)) {
            $currentUser = $cu;
        } else {
            $currentUser = new \CurrentUser(\UserSession::getInstance()->user_id);
        }

        if (!$currentUser->isValid) {
            throw new AccessDeniedHttpException();
        }

        return $currentUser;
    }
}
