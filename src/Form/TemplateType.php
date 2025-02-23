<?php

namespace App\Form;

use App\Entity\Template;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TemplateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Название шаблона',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('description', TextType::class, [
                'label' => 'Описание',
                'attr' => ['class' => 'form-control'],
                'required' => false,
            ])
            ->add('topic', ChoiceType::class, [
                'label' => 'Категория',
                'choices' => [
                    'Опрос' => 'Опрос',
                    'Тест' => 'Тест',
                    'Образование' => 'Образование',
                    'Другое' => 'Другое',
                ],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('isPublic', ChoiceType::class, [
                'label' => 'Доступ',
                'choices' => [
                    'Публичный' => true,
                    'Приватный' => false,
                ],
                'expanded' => true,
            ])
            ->add('save', SubmitType::class, [
                'label' => $options['submit_label'], // ✅ Динамическое название кнопки
                'attr' => ['class' => 'btn btn-primary'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Template::class,
            'submit_label' => 'Сохранить', // ✅ Можно передавать в контроллере
        ]);
    }
}
