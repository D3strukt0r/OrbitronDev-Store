<?php

namespace App\Form;

use App\Form\Type\ReCaptchaType;
use App\Service\StoreHelper;
use App\Validator\Constraints\ReCaptchaTrue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Expression;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class NewStoreType extends AbstractType
{
    private $helper;

    public function __construct(StoreHelper $helper)
    {
        $this->helper = $helper;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'new_store.form.name.label',
                    'constraints' => [
                        new NotBlank(['message' => 'new_store.name.not_blank']),
                        new Length(
                            [
                                'min' => StoreHelper::$settings['store']['name']['min_length'],
                                'minMessage' => 'new_store.name.min_length',
                            ]
                        ),
                    ],
                ]
            )
            ->add(
                'url',
                TextType::class,
                [
                    'label' => 'new_store.form.url.label',
                    'constraints' => [
                        new NotBlank(['message' => 'new_store.url.not_blank']),
                        new Length(
                            [
                                'min' => StoreHelper::$settings['store']['url']['min_length'],
                                'minMessage' => 'new_store.url.min_length',
                            ]
                        ),
                        new Regex(
                            [
                                'pattern' => '/[^a-zA-Z_\-0-9]/i',
                                'message' => 'new_store.url.regex',
                                'match' => false,
                            ]
                        ),
                        new Expression(
                            [
                                'expression' => 'value not in ["new-store", "admin", "login", "login-check", "logout", "user", "setup"]',
                                'message' => 'new_store.url.not_equal_to',
                            ]
                        ),
                        new Callback(
                            function ($object, ExecutionContextInterface $context, $payload) {
                                if ($this->helper->urlExists($object)) {
                                    $context->addViolation('new_store.url.already_in_use');
                                }
                            }
                        ),
                    ],
                ]
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'label' => 'new_store.form.email.label',
                    'constraints' => [
                        new NotBlank(['message' => 'new_store.email.not_blank']),
                        new Email(
                            [
                                'mode' => 'strict',
                                //'checkMX' => true, // Has been removed
                                'message' => 'new_store.email.valid_email',
                            ]
                        ),
                    ],
                ]
            )
            ->add(
                'language',
                ChoiceType::class,
                [
                    'label' => 'new_store.form.language.label',
                    'choices' => [
                        'new_store.form.language.options.en' => 'en',
                        'new_store.form.language.options.de' => 'de',
                    ],
                    'data' => 'en',
                ]
            )
            ->add(
                'currency',
                ChoiceType::class,
                [
                    'label' => 'new_store.form.currency.label',
                    'choices' => [
                        'new_store.form.currency.options.usd' => 'USD',
                        'new_store.form.currency.options.eur' => 'EUR',
                    ],
                    'data' => 'USD',
                ]
            )
            ->add(
                'recaptcha',
                ReCaptchaType::class,
                [
                    'attr' => [
                        'options' => [
                            'theme' => 'light',
                            'type' => 'image',
                            'size' => 'normal',
                            'defer' => true,
                            'async' => true,
                        ],
                    ],
                    'mapped' => false,
                    'constraints' => [
                        new ReCaptchaTrue(),
                    ],
                ]
            )
            ->add(
                'send',
                SubmitType::class,
                [
                    'label' => 'new_store.form.send.label',
                ]
            )
        ;
    }
}
