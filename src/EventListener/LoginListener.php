<?php
namespace App\EventListener;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Psr\Log\LoggerInterface;

class LoginListener
{
    private UrlGeneratorInterface $urlGenerator;
    private LoggerInterface $logger;

    public function __construct(UrlGeneratorInterface $urlGenerator, LoggerInterface $logger)
    {
        $this->urlGenerator = $urlGenerator;
        $this->logger = $logger;
    }

    public function __invoke(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();  // Получаем пользователя, который только что успешно вошел

        // Логирование успешного входа
        $this->logger->info("User login attempt: {$user->getEmail()}");

        // Проверяем статус пользователя
        if ($user instanceof User && $user->getStatus() === 'blocked') {
            // Логирование попытки входа заблокированного пользователя
            $this->logger->warning("Blocked user attempted to log in: {$user->getEmail()}");

            // Перенаправляем на страницу логина с ошибкой
            $errorUrl = $this->urlGenerator->generate('app_login');
            $response = new RedirectResponse($errorUrl);
            $event->setResponse($response);  // Устанавливаем перенаправление
        } else {
            // Логирование успешного входа для активных пользователей
            $this->logger->info("User successfully logged in: {$user->getEmail()}");
        }
    }
}
