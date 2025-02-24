<?php
namespace App\Controller;

use App\Entity\Question;
use App\Entity\Template;
use App\Form\QuestionType;
use App\Form\TemplateType;
use App\Repository\TemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/templates')]
class TemplateController extends AbstractController
{
    #[Route('/', name: 'template_index', methods: ['GET'])]
    public function index(TemplateRepository $templateRepository): Response
    {
        $user = $this->getUser();

        if ($this->isGranted('ROLE_ADMIN')) {
            $templates = $templateRepository->findBy([], ['createdAt' => 'DESC']);
        } else {
            $templates = $templateRepository->findBy(['author' => $user], ['createdAt' => 'DESC']);
        }

        return $this->render('template/index.html.twig', [
            'templates' => $templates,
        ]);
    }

    #[Route('/new', name: 'template_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $template = new Template();
        $form = $this->createForm(TemplateType::class, $template);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $template->setAuthor($this->getUser());
            $entityManager->persist($template);
            $entityManager->flush();

            return $this->redirectToRoute('template_index');
        }

        return $this->render('template/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'template_edit', methods: ['GET', 'POST'])]
    public function show(Request $request, Template $template, EntityManagerInterface $entityManager): Response
    {

        if (!$this->isGranted('ROLE_ADMIN') && $template->getAuthor() !== $this->getUser()) {
            $this->addFlash('error', 'Вы не имеете доступа к этому шаблону.');
            return $this->redirectToRoute('template_index');
        }

        $question = new Question();
        $question->setTemplate($template);

        $form = $this->createForm(QuestionType::class, $question, [
            'submit_label' => 'Добавить вопрос',
            'template' => $template,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($question);
            $entityManager->flush();

            return $this->redirectToRoute('template_show', ['id' => $template->getId()]);
        }

        return $this->render('template/show.html.twig', [
            'template' => $template,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'template_delete', methods: ['POST'])]
    public function delete(Template $template, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();

        if (!$user || (!$this->isGranted('ROLE_ADMIN') && $template->getAuthor() !== $user)) {
            return new JsonResponse(['success' => false, 'error' => 'Доступ запрещен'], 403);
        }

        $entityManager->remove($template);
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }
}
