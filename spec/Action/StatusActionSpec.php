<?php

/**
 * This file was created by the developers from Infifni.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://infifnisoftware.ro and write us
 * an email on contact@infifnisoftware.ro.
 */

declare(strict_types=1);

namespace spec\Infifni\SyliusEuPlatescPlugin\Action;

use Infifni\SyliusEuPlatescPlugin\Action\StatusAction;
use Infifni\SyliusEuPlatescPlugin\Bridge\EuPlatescBridgeInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\GetStatusInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\PaymentInterface;

final class StatusActionSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(StatusAction::class);
    }

    function it_implements_action_interface(): void
    {
        $this->shouldHaveType(ActionInterface::class);
    }

    function it_implements_gateway_aware_interface(): void
    {
        $this->shouldHaveType(GatewayAwareInterface::class);
    }

    function it_executes(
        GetStatusInterface $request,
        PaymentInterface $payment,
        GatewayInterface $gateway
    ): void
    {
        $httpRequest = new GetHttpRequest();

        $gateway->execute($httpRequest);

        $this->setGateway($gateway);

        $payment->getDetails()->willReturn([
            'euplatesc_status' => EuPlatescBridgeInterface::FAILED_STATUS
        ]);

        $request->getModel()->willReturn($payment);

        $request->markFailed()->shouldBeCalled();

        $this->execute($request);
    }

    function it_supports_only_get_status_request_and_array_access(
        GetStatusInterface $request,
        PaymentInterface $payment
    ): void
    {
        $request->getModel()->willReturn($payment);

        $this->supports($request)->shouldReturn(true);
    }
}
