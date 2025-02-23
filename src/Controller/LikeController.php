<?php

namespace App\Controller;

use App\Entity\Like;
use App\Entity\Template;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/like')]
class LikeController extends AbstractController
{
    #[Route('/{id}', name: 'template_like', methods: ['POST'])]
    public function likeTemplate(Template $template, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();

        // Проверяем, лайкал ли уже пользователь этот шаблон
        $existingLike = $entityManager->getRepository(Like::class)->findOneBy([
            'user' => $user,
            'template' => $template
        ]);

        if ($existingLike) {
            $entityManager->remove($existingLike);
            $entityManager->flush();
            return new JsonResponse(['liked' => false, 'likes' => count($template->getLikes())]);
        }

        $like = new Like();
        $like->setUser($user);
        $like->setTemplate($template);

        $entityManager->persist($like);
        $entityManager->flush();

        return new JsonResponse(['liked' => true, 'likes' => count($template->getLikes())]);
    }
}
