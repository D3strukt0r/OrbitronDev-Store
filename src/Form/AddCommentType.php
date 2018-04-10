<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class AddCommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('rating', HiddenType::class, [
                'data' => 0,
            ])
            ->add('comment', TextareaType::class, [
                'attr' => [
                    'placeholder' => 'service_product.form_comment.comment.placeholder',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'service_product.form_comment.comment.constraints.not_blank']),
                ],
            ])
            ->add('send', SubmitType::class, [
                'label' => 'service_product.form_comment.send.label',
            ]);
    }
}
