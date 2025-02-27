<?php

namespace App\Controller;

use App\Entity\AnswerOption;
use App\Entity\Question;
use App\Entity\Template;
use App\Form\QuestionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/question')]
class QuestionController extends AbstractController
{
    #[Route('/new/{template}', name: 'question_new', methods: ['POST'])]
    public function new(Request $request, Template $template, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();

        if (!$this->isGranted('ROLE_ADMIN') && $template->getAuthor() !== $user) {
            return new JsonResponse(['success' => false, 'error' => 'Доступ запрещен'], 403);
        }

        $question = new Question();
        $question->setTemplate($template);

        $form = $this->createForm(QuestionType::class, $question, ['template' => $template]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($question);

            $this->handleAnswerOptions($request, $question, $entityManager);

            $entityManager->flush();
            return new JsonResponse(['success' => true]);
        }

        return new JsonResponse(['success' => false], 400);
    }

    #[Route('/delete/{id}', name: 'question_delete', methods: ['POST'])]
    public function delete(Question $question, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        $template = $question->getTemplate();

        if (!$this->isGranted('ROLE_ADMIN') && $template->getAuthor() !== $user) {
            return new JsonResponse(['success' => false, 'error' => 'Доступ запрещен'], 403);
        }
        $entityManager->remove($question);
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/edit/{id}', name: 'question_edit', methods: ['POST'])]
    public function edit(Request $request, Question $question, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        $template = $question->getTemplate();

        if (!$this->isGranted('ROLE_ADMIN') && $template->getAuthor() !== $user) {
            return new JsonResponse(['success' => false, 'error' => 'Доступ запрещен'], 403);
        }

        $form = $this->createForm(QuestionType::class, $question, ['template' => $question->getTemplate()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($question->getOptions() as $existingOption) {
                $entityManager->remove($existingOption);
            }
            $entityManager->flush();

            $this->handleAnswerOptions($request, $question, $entityManager);

            $entityManager->flush();
            return new JsonResponse(['success' => true]);
        }

        return new JsonResponse(['success' => false], 400);
    }

    #[Route('/get/{id}', name: 'question_get', methods: ['GET'])]
    public function getQuestion(Question $question): JsonResponse
    {
        return $this->json([
            'id' => $question->getId(),
            'title' => $question->getTitle(),
            'description' => $question->getDescription(),
            'type' => $question->getType(),
            'options' => array_map(fn($option) => [
                'id' => $option->getId(),
                'value' => $option->getValue(),
                'is_correct' => $option->isCorrect(),
            ], $question->getOptions()->toArray()),
        ]);
    }

    private function handleAnswerOptions(Request $request, Question $question, EntityManagerInterface $entityManager): void
    {
        $optionsData = $request->request->all()['options'] ?? [];
        $correctAnswers = $request->request->all()['is_correct'] ?? [];

        foreach ($optionsData as $index => $optionValue) {
            if (!empty($optionValue)) {
                $option = new AnswerOption();
                $option->setValue($optionValue);
                $option->setCorrect(in_array((string) $index, $correctAnswers, true));
                $option->setQuestion($question);
                $entityManager->persist($option);
            }
        }
    }

}
