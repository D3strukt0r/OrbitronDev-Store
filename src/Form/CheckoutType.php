<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CheckoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'service_checkout.form.name.label',
                    'attr' => [
                        'value' => $options['name'],
                    ],
                    'constraints' => [
                        new NotBlank(['message' => 'service_checkout.form.name.constraints.not_blank']),
                    ],
                ]
            )
            ->add(
                'email',
                TextType::class,
                [
                    'label' => 'service_checkout.form.email.label',
                    'attr' => [
                        'value' => $options['email'],
                    ],
                    'constraints' => [
                        new NotBlank(['message' => 'service_checkout.form.email.constraints.not_blank']),
                    ],
                ]
            )
            ->add(
                'phone',
                TextType::class,
                [
                    'label' => 'service_checkout.form.phone.label',
                    'constraints' => [
                        new NotBlank(['message' => 'service_checkout.form.phone.constraints.not_blank']),
                    ],
                ]
            )
            ->add(
                'location_street',
                TextType::class,
                [
                    'label' => 'service_checkout.form.location_street.label',
                    'attr' => [
                        'value' => $options['location_street'],
                    ],
                    'constraints' => [
                        new NotBlank(['message' => 'service_checkout.form.location_street.constraints.not_blank']),
                    ],
                ]
            )
            ->add(
                'location_street_number',
                TextType::class,
                [
                    'label' => 'service_checkout.form.location_street_number.label',
                    'attr' => [
                        'value' => $options['location_street_number'],
                    ],
                    'constraints' => [
                        new NotBlank(
                            ['message' => 'service_checkout.form.location_street_number.constraints.not_blank']
                        ),
                    ],
                ]
            )
            ->add(
                'location_postal_code',
                TextType::class,
                [
                    'label' => 'service_checkout.form.location_postal_code.label',
                    'attr' => [
                        'value' => $options['location_postal_code'],
                    ],
                    'constraints' => [
                        new NotBlank(['message' => 'service_checkout.form.location_postal_code.constraints.not_blank']),
                    ],
                ]
            )
            ->add(
                'location_city',
                TextType::class,
                [
                    'label' => 'service_checkout.form.location_city.label',
                    'attr' => [
                        'value' => $options['location_city'],
                    ],
                    'constraints' => [
                        new NotBlank(['message' => 'service_checkout.form.location_city.constraints.not_blank']),
                    ],
                ]
            )
            ->add(
                'location_country',
                TextType::class,
                [
                    'label' => 'service_checkout.form.location_country.label',
                    'attr' => [
                        'value' => $options['location_country'],
                    ],
                    'constraints' => [
                        new NotBlank(['message' => 'service_checkout.form.location_country.constraints.not_blank']),
                    ],
                ]
            )
            ->add(
                'send',
                SubmitType::class,
                [
                    'label' => 'service_checkout.form.send.label',
                ]
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'name' => '',
                'email' => '',
                'location_street' => '',
                'location_street_number' => '',
                'location_postal_code' => '',
                'location_city' => '',
                'location_country' => '',
            ]
        );
    }
}
