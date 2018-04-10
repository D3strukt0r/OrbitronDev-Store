<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class AddProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'admin.form.create_product.name.label',
                'constraints' => [
                    new NotBlank(['message' => 'create_product.name.not_blank']),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'admin.form.create_product.description.label',
                'constraints' => [
                    new NotBlank(['message' => 'create_product.description.not_blank']),
                ],
            ])
            ->add('short_description', TextareaType::class, [
                'label' => 'admin.form.create_product.short_description.label',
                'constraints' => [
                    new NotBlank(['message' => 'create_product.short_description.not_blank']),
                ],
            ])
            ->add('price', TextType::class, [
                'label' => 'admin.form.create_product.price.label',
                'constraints' => [
                    new NotBlank(['message' => 'create_product.price.not_blank']),
                ],
            ])
            ->add('sale_price', TextType::class, [
                'label' => 'admin.form.create_product.sale_price.label',
                'required' => false,
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'admin.form.create_product.stock.label',
                'constraints' => [
                    new NotBlank(['message' => 'create_product.stock.not_blank']),
                ],
            ])
            ->add('send', SubmitType::class, [
                'label' => 'admin.form.create_product.send.label',
            ]);
    }
}
