<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // ✅ Добавляем CSRF-токен отдельно
        $builder->add('_token', HiddenType::class, [
            'mapped' => false,
        ]);

        foreach ($options['questions'] as $question) {
            $fieldName = "question_{$question->getId()}";

            switch ($question->getType()) {
                case 'radio':
                case 'multiple_choice':
                    $choices = [];
                    $valueCount = [];

                    foreach ($question->getOptions() as $option) {
                        $value = $option->getValue();
                        if (!isset($valueCount[$value])) {
                            $valueCount[$value] = 0;
                        }
                        $valueCount[$value]++;
                        $choiceKey = $valueCount[$value] > 1 ? $value . ' #' . $option->getId() : $value;
                        $choices[$choiceKey] = $option->getId();
                    }

                    $builder->add($fieldName, ChoiceType::class, [
                        'label' => $question->getTitle() ?: false,
                        'help' => $question->getDescription(),
                        'choices' => $choices,
                        'expanded' => true, // Радио-кнопки и чекбоксы
                        'multiple' => $question->getType() === 'multiple_choice',
                    ]);
                    break;

                case 'boolean': // ✅ "Да/Нет" вопрос
                    $builder->add($fieldName, ChoiceType::class, [
                        'label' => $question->getTitle() ?: false,
                        'help' => $question->getDescription(),
                        'choices' => [
                            'Да' => 'yes',
                            'Нет' => 'no'
                        ],
                        'expanded' => true,
                        'required' => false,
                        'attr' => ['class' => 'form-check-input'],
                    ]);
                    break;

                case 'number': // ✅ Числовой ввод
                    $builder->add($fieldName, IntegerType::class, [
                        'label' => $question->getTitle() ?: false,
                        'help' => $question->getDescription(),
                        'required' => false,
                        'attr' => ['class' => 'form-control', 'min' => 0],
                    ]);
                    break;

                default: // ✅ Обычный текстовый вопрос
                    $builder->add($fieldName, TextType::class, [
                        'label' => $question->getTitle() ?: false,
                        'help' => $question->getDescription(),
                        'required' => false,
                        'attr' => ['class' => 'form-control'],
                    ]);
                    break;
            }
        }

        // ✅ Добавляем кнопку сабмита
        $builder->add('submit', SubmitType::class, [
            'label' => 'Отправить ответы',
            'attr' => ['class' => 'btn btn-primary'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'questions' => [],
        ]);
    }
}
