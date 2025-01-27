<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class AuthController extends AbstractController
{

    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();

            // Валидация данных
            if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
                $this->addFlash('error', 'All fields are required.');
                return $this->redirectToRoute('app_register');
            }

            // Проверяем, существует ли пользователь с таким email
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
            if ($existingUser) {
                $this->addFlash('error', 'Email is already in use.');
                return $this->redirectToRoute('app_register');
            }

            // Создаем нового пользователя
            $user = new User();
            $user->setName($data['name']);
            $user->setEmail($data['email']);
            $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setStatus('active');

            // Сохраняем в базе данных
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Registration successful! You can now log in.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('auth/register.html.twig');
    }

    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function showLoginForm(AuthenticationUtils $authenticationUtils): Response
    {
        // Получаем последнюю ошибку входа
        $error = $authenticationUtils->getLastAuthenticationError();
        // Последний введенный email
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Этот метод Symfony обрабатывает автоматически
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }


}
