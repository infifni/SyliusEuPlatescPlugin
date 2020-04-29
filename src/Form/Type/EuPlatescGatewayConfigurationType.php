<?php

declare(strict_types=1);

/**
 * This file was created by the developers from Infifni.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://infifnisoftware.ro and write us
 * an email on contact@infifnisoftware.ro.
 */

namespace Infifni\SyliusEuPlatescPlugin\Form\Type;

use Infifni\SyliusEuPlatescPlugin\Bridge\EuPlatescBridgeInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotEqualTo;

final class EuPlatescGatewayConfigurationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'environment',
                ChoiceType::class,
                [
                    'choices' => [
                        'infifni.sylius_euplatesc_plugin.sandbox' => EuPlatescBridgeInterface::TEST_ENVIRONMENT,
                        'infifni.sylius_euplatesc_plugin.secure' => EuPlatescBridgeInterface::LIVE_ENVIRONMENT,
                    ],
                    'label' => 'infifni.sylius_euplatesc_plugin.environment',
                ]
            )
            ->add(
                'merchantId',
                TextType::class,
                [
                    'label' => 'infifni.sylius_euplatesc_plugin.merchant_id',
                    'constraints' => [
                        new NotBlank(
                            [
                                'message' => 'infifni.sylius_euplatesc_plugin.gateway_configuration.merchant_id.not_blank',
                                'groups' => ['sylius'],
                            ]
                        )
                    ],
                    'data' => EuPlatescBridgeInterface::TEST_MERCHANT_ID,
                ]
            )
            ->add(
                'merchantKey',
                TextType::class,
                [
                    'label' => 'infifni.sylius_euplatesc_plugin.merchant_key',
                    'constraints' => [
                        new NotBlank(
                            [
                                'message' => 'infifni.sylius_euplatesc_plugin.gateway_configuration.merchant_key.not_blank',
                                'groups' => ['sylius'],
                            ]
                        ),
                    ],
                    'data' => EuPlatescBridgeInterface::TEST_MERCHANT_KEY,
                ]
            );

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'preSubmitFormEventAction']);
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmitFormEventAction(FormEvent $event): void
    {
        $formData = $event->getData();
        $form = $event->getForm();
        if (EuPlatescBridgeInterface::TEST_ENVIRONMENT === $formData['environment']) {
            $form
                ->add(
                    'merchantId',
                    TextType::class,
                    [
                        'label' => 'infifni.sylius_euplatesc_plugin.merchant_id',
                        'constraints' => [
                            new EqualTo([
                                'value' => EuPlatescBridgeInterface::TEST_MERCHANT_ID,
                                'groups' => ['sylius'],
                            ])
                        ]
                    ]
                )
                ->add(
                    'merchantKey',
                    TextType::class,
                    [
                        'label' => 'infifni.sylius_euplatesc_plugin.merchant_key',
                        'constraints' => [
                            new NotBlank(
                                [
                                    'message' => 'infifni.sylius_euplatesc_plugin.gateway_configuration.merchant_key.not_blank',
                                    'groups' => ['sylius'],
                                ]
                            ),
                            new EqualTo([
                                'value' => EuPlatescBridgeInterface::TEST_MERCHANT_KEY,
                                'groups' => ['sylius'],
                            ])
                        ]
                    ]
                );
        }
        if (EuPlatescBridgeInterface::LIVE_ENVIRONMENT === $formData['environment']) {
            $form
                ->add(
                    'merchantId',
                    TextType::class,
                    [
                        'label' => 'infifni.sylius_euplatesc_plugin.merchant_id',
                        'constraints' => [
                            new NotBlank(
                                [
                                    'message' => 'infifni.sylius_euplatesc_plugin.gateway_configuration.merchant_id.not_blank',
                                    'groups' => ['sylius'],
                                ]
                            ),
                            new NotEqualTo([
                                'value' => EuPlatescBridgeInterface::TEST_MERCHANT_ID,
                                'groups' => ['sylius'],
                            ])
                        ]
                    ]
                )
                ->add(
                    'merchantKey',
                    TextType::class,
                    [
                        'label' => 'infifni.sylius_euplatesc_plugin.merchant_key',
                        'constraints' => [
                            new NotBlank(
                                [
                                    'message' => 'infifni.sylius_euplatesc_plugin.gateway_configuration.merchant_key.not_blank',
                                    'groups' => ['sylius'],
                                ]
                            ),
                            new NotEqualTo([
                                'value' => EuPlatescBridgeInterface::TEST_MERCHANT_KEY,
                                'groups' => ['sylius'],
                            ])
                        ]
                    ]
                );
        }
    }
}
