<?php

namespace App\EventListener;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class CheckBlockedUserListener
{
    private $security;
    private $entityManager;

    public function __construct(Security $security, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        // Получаем текущий запрос
        $request = $event->getRequest();

        // Если это страница входа, то не нужно проверять
        if ($request->getPathInfo() === '/login') {
            return;
        }

        // Получаем текущего пользователя
        $user = $this->security->getUser();

        // Если пользователь не авторизован, пропускаем проверку
        if (!$user) {
            return;
        }

        // Проверяем статус пользователя
        if ($user->getStatus() === 'blocked') {
            throw new AccessDeniedHttpException('Your account is blocked.');
        }
    }
}
