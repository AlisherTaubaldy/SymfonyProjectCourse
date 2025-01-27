<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @see https://symfony.com/doc/current/security/custom_authenticator.html
 */
class UserAuthenticator extends AbstractAuthenticator
{
    private EntityManagerInterface $entityManager;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator, EntityManagerInterface $entityManager)
    {
        $this->urlGenerator = $urlGenerator;
        $this->entityManager = $entityManager;
    }

    /**
     * This method checks if the request is a POST request to the /login path
     */
    public function supports(Request $request): ?bool
    {
        return $request->getPathInfo() === '/login' && $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('_email');
        $password = $request->request->get('_password');

        // Get the user from the database
        $user = $this->getUserByEmail($email);

        // If the user is not found, throw an authentication exception
        if (!$user) {
            throw new CustomUserMessageAuthenticationException('User not found.');
        }

        // If the user is blocked, throw an authentication exception
        if ($user->getStatus() === 'blocked') {
            throw new CustomUserMessageAuthenticationException('Your account is blocked.');
        }

        // Create a SelfValidatingPassport with only the UserBadge for identifying the user
        return new SelfValidatingPassport(
            new UserBadge($email)  // UserBadge is used to identify the user by email
        );
    }

    /**
     * Handles successful authentication
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $email = $request->request->get('_email');
        $password = $request->request->get('_password');

        // Get the user from the database
        $user = $this->getUserByEmail($email);

        if ($user) {
            // Update lastLogin field
            $user->setLastLogin(new \DateTime());  // Set current datetime
            $this->entityManager->persist($user);  // Mark the user entity as changed
            $this->entityManager->flush();  // Save the changes to the database
        }
        // Redirect the user to a dashboard or home page after successful authentication
        return new RedirectResponse($this->urlGenerator->generate('app_users')); // Замените на нужный маршрут
    }

    /**
     * Handles failed authentication
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        // В случае неудачной аутентификации перенаправляем обратно на страницу логина с ошибкой
        return new RedirectResponse($this->urlGenerator->generate('app_login')); // Замените на маршрут страницы входа
    }

    /**
     * Helper method to retrieve the user by their email from the database
     */
    private function getUserByEmail(string $email): ?User
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
    }
}
