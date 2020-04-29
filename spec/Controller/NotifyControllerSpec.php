<?php

/**
 * This file was created by the developers from Infifni.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://infifnisoftware.ro and write us
 * an email on contact@infifnisoftware.ro.
 */

declare(strict_types=1);

namespace spec\Infifni\SyliusEuPlatescPlugin\Controller;

use Infifni\SyliusEuPlatescPlugin\Controller\NotifyController;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;

final class NotifyControllerSpec extends ObjectBehavior
{
    function let(Payum $payum, PaymentRepositoryInterface $paymentRepository): void
    {
        $this->beConstructedWith($payum, $paymentRepository);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(NotifyController::class);
    }

    function it_executes(
        PaymentRepositoryInterface $paymentRepository,
        PaymentInterface $payment,
        Payum $payum,
        StorageInterface $storage,
        TokenInterface $token,
        GatewayInterface $gateway
    ): void
    {
        $request = new Request([], ['invoice_id' => 1]);

        $payment->getDetails()->willReturn([
            'ExtraData' => 'test'
        ]);

        $paymentRepository->findOneBy(['id' => 1])->willReturn($payment);

        $token->getGatewayName()->willReturn('euplatesc');

        $storage->find('test')->willReturn($token);

        $payum->getTokenStorage()->willReturn($storage);
        $payum->getGateway('euplatesc')->willReturn($gateway);

        $this->doAction($request);
    }
}