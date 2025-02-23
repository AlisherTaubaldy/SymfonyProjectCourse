<?php

namespace App\Controller;

use App\Entity\AnswerOption;
use App\Entity\Form;
use App\Entity\Answer;
use App\Entity\Template;
use App\Form\FormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/form')]
class FormController extends AbstractController
{
    #[Route('/{template}', name: 'form_fill', methods: ['GET', 'POST'])]
    public function fillForm(Request $request, Template $template, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Получаем или создаем форму пользователя
        $formEntity = $entityManager->getRepository(Form::class)->findOneBy([
            'user' => $this->getUser(),
            'template' => $template
        ]) ?? new Form();

        $formEntity->setTemplate($template);
        $formEntity->setUser($this->getUser());

        // Загружаем все вопросы
        $questions = $template->getQuestions()->toArray();
        $form = $this->createForm(FormType::class, null, ['questions' => $questions]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($questions as $question) {
                $fieldName = "question_{$question->getId()}";
                $value = $form->get($fieldName)->getData();

                $answer = new Answer();
                $answer->setForm($formEntity);
                $answer->setQuestion($question);
                $answer->setUser($this->getUser());

                if ($question->getType() === 'radio') {
                    $option = $entityManager->getRepository(AnswerOption::class)->find($value);
                    if ($option) {
                        $answer->setSelectedOption($option);
                    }
                } elseif ($question->getType() === 'multiple_choice' && is_array($value)) {
                    foreach ($value as $optionId) {
                        $option = $entityManager->getRepository(AnswerOption::class)->find($optionId);
                        if ($option) {
                            $answer->addSelectedOption($option);
                        }
                    }
                } else {
                    $answer->setValue($value);
                }
                $entityManager->persist($answer);
            }

            $entityManager->persist($formEntity);
            $entityManager->flush();

            return $this->redirectToRoute('form_complete', ['id' => $formEntity->getId()]);
        }


        return $this->render('form/fill.html.twig', [
            'form' => $form->createView(),
            'template' => $template,
            'questions' => $questions,
        ]);
    }

    #[Route('/complete/{id}', name: 'form_complete', methods: ['GET'])]
    public function completeForm(Form $form)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('form/complete.html.twig', [
            'form' => $form
        ]);
    }


}
