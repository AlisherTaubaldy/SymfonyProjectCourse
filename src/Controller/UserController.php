<?php


namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/users', name: 'app_users', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Получаем список всех пользователей
        $users = $entityManager->getRepository(User::class)
            ->findBy([], ['lastLogin' => 'DESC']);  // Сортировка по lastLogin DESC

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/users/action', name: 'app_users_action', methods: ['POST'])]
    public function handleUserActions(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Получаем выбранные ID пользователей из POST-запроса
        $userIds = $request->request->all('user_ids');
        $action = $request->request->get('action');

        if (empty($userIds)) {
            $this->addFlash('error', 'No users selected.');
            return $this->redirectToRoute('app_users'); // Предположим, маршрут списка пользователей
        }

        $currentUser = $this->getUser();

        // Проверяем, заблокирован ли текущий пользователь, который пытается выполнить действия
        if ($currentUser && $currentUser->getStatus() === 'blocked') {
            $this->addFlash('error', 'Your account is blocked. Please log in again.');
            return $this->redirectToRoute('app_login'); // Перенаправление на страницу входа
        }

        // Получаем пользователей из базы данных
        $users = $entityManager->getRepository(User::class)->findBy(['id' => $userIds]);

        switch ($action) {
            case 'block':
                foreach ($users as $user) {
                    $user->setStatus('blocked');
                }
                $this->addFlash('success', 'Selected users have been blocked.');
                break;

            case 'unblock':
                foreach ($users as $user) {
                    $user->setStatus('active');
                }
                $this->addFlash('success', 'Selected users have been unblocked.');
                break;

            case 'delete':
                foreach ($users as $user) {
                    $entityManager->remove($user);
                }
                $this->addFlash('success', 'Selected users have been deleted.');
                break;

            default:
                $this->addFlash('error', 'Invalid action.');
                return $this->redirectToRoute('app_users');
        }

        // Применяем изменения в базе данных
        $entityManager->flush();

        return $this->redirectToRoute('app_users');
    }

}
