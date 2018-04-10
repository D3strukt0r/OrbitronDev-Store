<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class AddVoucherType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'admin.form.create_voucher.code.label',
                'constraints' => [
                    new NotBlank(['message' => 'create_voucher.code.not_blank']),
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'admin.form.create_voucher.type.label',
                'choices' => [
                    'admin.form.create_voucher.amount.options.percentage' => 0,
                    'admin.form.create_voucher.amount.options.exact' => 1,
                ],
                'expanded' => true, // radio buttons
                'multiple' => false,
                'data' => 0,
            ])
            ->add('amount', NumberType::class, [
                'label' => 'admin.form.create_voucher.amount.label',
                'constraints' => [
                    new NotBlank(['message' => 'create_voucher.amount.not_blank']),
                ],
            ])
            ->add('send', SubmitType::class, [
                'label' => 'admin.form.create_voucher.send.label',
            ]);
    }
}
