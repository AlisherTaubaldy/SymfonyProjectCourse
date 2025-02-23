<?php
namespace App\Controller;

use App\Entity\Question;
use App\Entity\Template;
use App\Form\QuestionType;
use App\Form\TemplateType;
use App\Repository\TemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/templates')]
class TemplateController extends AbstractController
{
    #[Route('/', name: 'template_index', methods: ['GET'])]
    public function index(TemplateRepository $templateRepository): Response
    {
        $templates = $templateRepository->findBy([], ['createdAt' => 'DESC']);
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

    #[Route('/{id}', name: 'template_show', methods: ['GET', 'POST'])]
    public function show(Request $request, Template $template, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

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


    #[Route('/{id}/edit', name: 'template_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Template $template, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('edit', $template);

        $form = $this->createForm(TemplateType::class, $template);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('template_index');
        }

        return $this->render('template/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'template_delete', methods: ['POST'])]
    public function delete(Request $request, Template $template, EntityManagerInterface $entityManager): Response
    {
        // Проверяем, может ли пользователь удалить шаблон
        if (!$this->isGranted('ROLE_ADMIN') && $this->getUser() !== $template->getAuthor()) {
            $this->addFlash('danger', 'Вы не можете удалить этот шаблон.');
            return $this->redirectToRoute('template_show', ['id' => $template->getId()]);
        }

        if ($this->isCsrfTokenValid('delete'.$template->getId(), $request->request->get('_token'))) {
            $entityManager->remove($template);
            $entityManager->flush();
            $this->addFlash('success', 'Шаблон успешно удалён.');
        }

        return $this->redirectToRoute('template_index'); // Перенаправление на главную страницу
    }

}
