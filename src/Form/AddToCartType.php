<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class AddToCartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('product_count', IntegerType::class, [
                'label'       => 'service_product.form_cart.product_count.label',
                'constraints' => [
                    new NotBlank(['message' => 'service_product.form_cart.product_count.constraints.not_blank']),
                    new Type([
                        'type'    => 'int',
                        'message' => 'service_product.form_cart.product_count.constraints.not_an_int',
                    ]),
                ],
                'disabled'    => $options['disable_fields'] ? true : false,
            ])
            ->add('send', SubmitType::class, [
                'label'    => 'service_product.form_cart.send.label',
                'disabled' => $options['disable_fields'] ? true : false,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'disable_fields' => null,
        ]);
    }
}
