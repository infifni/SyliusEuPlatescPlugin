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
use Infifni\SyliusEuPlatescPlugin\Action\NotifyAction;
use Infifni\SyliusEuPlatescPlugin\Bridge\EuPlatescBridgeInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\PaymentInterface;

final class NotifyActionSpec extends ObjectBehavior
{
    public function let(EuPlatescBridgeInterface $euPlatescBridge): void
    {
        $this->beConstructedWith($euPlatescBridge);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(NotifyAction::class);
    }

    function it_implements_action_interface(): void
    {
        $this->shouldHaveType(ActionInterface::class);
    }

    function it_implements_api_aware_interface(): void
    {
        $this->shouldHaveType(ApiAwareInterface::class);
    }

    function it_implements_gateway_aware_interface(): void
    {
        $this->shouldHaveType(GatewayAwareInterface::class);
    }

    function it_executes(
        EuPlatescBridgeInterface $euPlatescBridge,
        Notify $request,
        ArrayObject $arrayObject,
        GatewayInterface $gateway,
        PaymentInterface $payment
    ): void
    {
        $euPlatescBridge->setAuthorizationData('testmid', 'testmkey');

        $httpRequest = new GetHttpRequest();
        $httpRequestData = [
            'amount' => '1',
            'curr' => 'RON',
            'invoice_id' => '1',
            'ep_id' => 'testepid',
            'merch_id' => 'testmid',
            'action' => '0',
            'message' => 'msg',
            'approval' => 'approved',
            'timestamp' => '10200425080400',
            'nonce' => '19f640b9727c4e65e98c40ebc7988222',
            'fp_hash' => 'fphash',
        ];
        $gateway->execute($httpRequest)->will(function ($args) use ($httpRequestData) {
            $args[0]->request = $httpRequestData;
        });
        $this->setGateway($gateway);

        unset($httpRequestData['fp_hash']);
        $euPlatescBridge->hmacFormat($httpRequestData)->willReturn('fphash');

        $arrayObject->offsetSet('ep_id', 'testepid')->shouldBeCalled();
        $arrayObject->offsetSet('action', '0')->shouldBeCalled();
        $arrayObject->offsetSet('message', 'msg')->shouldBeCalled();
        $arrayObject->offsetSet('approval', 'approved')->shouldBeCalled();
        $arrayObject
            ->offsetSet('euplatesc_status', EuPlatescBridgeInterface::COMPLETED_STATUS)
            ->shouldBeCalled();
        $request->getModel()->willReturn($arrayObject);
        $request->getFirstModel()->willReturn($payment);

        $this->execute($request);
    }

    function it_supports_only_notify_request_and_array_access(
        Notify $request,
        ArrayAccess $arrayAccess
    ): void
    {
        $request->getModel()->willReturn($arrayAccess);

        $this->supports($request)->shouldReturn(true);
    }
}
