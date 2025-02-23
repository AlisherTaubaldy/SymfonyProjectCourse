<?php

namespace App\Form;

use App\Entity\Question;
use App\Entity\Template;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Template $template */
        $template = $options['template'];

        $questionTypes = match ($template->getTopic()) {
            'Тест' => [
                'Один из списка (Radio)' => 'radio',
                'Несколько из списка (Checkboxes)' => 'multiple_choice',
                'Короткий текст' => 'text',
                'Число' => 'number',
                'Да/Нет (Чекбокс)' => 'boolean',
            ],
            default => [
                'Короткий текст' => 'text',
                'Длинный текст' => 'textarea',
                'Число' => 'number',
                'Один из списка (Radio)' => 'radio',
                'Несколько из списка (Checkboxes)' => 'multiple_choice',
                'Да/Нет (Чекбокс)' => 'boolean',
            ],
        };

        $builder
            ->add('title', TextType::class, [
                'label' => 'Текст вопроса',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Описание',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 4],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Тип вопроса',
                'choices' => $questionTypes,
                'attr' => [
                    'class' => 'form-select question-type',
                ],
            ])
            ->add('options', CollectionType::class, [
                'entry_type' => TextType::class,
                'label' => 'Варианты ответов',
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'constraints' => [new Valid()],
                'attr' => ['class' => 'question-options'],
                'data' => $options['existing_options'] ?? [],
                'mapped' => true,
            ])
            ->add('save', SubmitType::class, [
                'label' => $options['submit_label'],
                'attr' => ['class' => 'btn btn-primary'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'submit_label' => 'Сохранить',
            'data_class' => Question::class,
            'template' => null,
            'existing_options' => [],
        ]);
    }
}
