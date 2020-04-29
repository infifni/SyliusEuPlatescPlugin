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

use ArrayAccess;
use Infifni\SyliusEuPlatescPlugin\Action\CaptureAction;
use Infifni\SyliusEuPlatescPlugin\Bridge\EuPlatescBridgeInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Security\GenericTokenFactory;
use Payum\Core\Security\TokenInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\PaymentInterface;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\GatewayAwareInterface;

final class CaptureActionSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(CaptureAction::class);
    }

    public function it_implements_action_interface(): void
    {
        $this->shouldHaveType(ActionInterface::class);
    }

    public function it_implements_generic_token_factory_aware(): void
    {
        $this->shouldHaveType(GenericTokenFactoryAwareInterface::class);
    }

    public function it_implements_gateway_aware_interface(): void
    {
        $this->shouldHaveType(GatewayAwareInterface::class);
    }

    public function it_executes(
        Capture $request,
        ArrayObject $arrayObject,
        PaymentInterface $payment,
        TokenInterface $token,
        TokenInterface $notifyToken,
        Payum $payum,
        GenericTokenFactory $genericTokenFactory,
        GatewayInterface $gateway
    ): void
    {
        $this->setGateway($gateway);

        $notifyToken->getHash()->willReturn('test');

        $token->getTargetUrl()->willReturn('url');
        $token->getGatewayName()->willReturn('test');
        $token->getDetails()->willReturn([]);
        $token->getHash()->willReturn('test');

        $genericTokenFactory->createNotifyToken('test', [])->willReturn($notifyToken);

        $this->setGenericTokenFactory($genericTokenFactory);

        $payum->getTokenFactory()->willReturn($genericTokenFactory);

        $arrayObject->toUnsafeArray()->willReturn([]);
        $arrayObject->offsetExists('euplatesc_status')->shouldBeCalled();
        $arrayObject->offsetSet('ExtraData', 'test')->shouldBeCalled();
        $arrayObject->offsetSet('euplatesc_status', EuPlatescBridgeInterface::CREATED_STATUS)->shouldBeCalled();
        $request->getModel()->willReturn($arrayObject);
        $request->getFirstModel()->willReturn($payment);
        $request->getToken()->willReturn($token);

        $this
            ->shouldThrow(HttpPostRedirect::class)
            ->during('execute', [$request])
        ;
    }

    public function it_supports_only_capture_request_and_array_access(
        Capture $request,
        ArrayAccess $arrayAccess
    ): void
    {
        $request->getModel()->willReturn($arrayAccess);

        $this->supports($request)->shouldReturn(true);
    }
}
