<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Template;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/comment')]
class CommentController extends AbstractController
{
    #[Route('/{id}', name: 'template_comment', methods: ['POST'])]
    public function commentTemplate(Template $template, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        $content = $request->request->get('content');

        if (!$content) {
            return new JsonResponse(['error' => 'Комментарий не может быть пустым.'], 400);
        }

        $comment = new Comment();
        $comment->setUser($user);
        $comment->setTemplate($template);
        $comment->setContent($content);

        $entityManager->persist($comment);
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'comments' => count($template->getComments())]);
    }
}