<?php

/**
 * This file was created by the developers from Infifni.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://infifnisoftware.ro and write us
 * an email on contact@infifnisoftware.ro.
 */

declare(strict_types=1);

namespace spec\Infifni\SyliusEuPlatescPlugin\Bridge;

use Doctrine\Common\Collections\ArrayCollection;
use Infifni\SyliusEuPlatescPlugin\Bridge\EuPlatescBridgeInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;

class EuPlatescBridgeSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(EuPlatescBridgeInterface::class);
    }

    public function it_implements_euplatesc_bridge_interface(): void
    {
        $this->shouldHaveType(EuPlatescBridgeInterface::class);
    }

    public function it_set_authorization_data(): void
    {
        $this->setAuthorizationData('test', '0ae123');
    }

    public function it_hmac_format(): void
    {
        $this->setAuthorizationData('test', '0ae123');
        $this
            ->hmacFormat([
                'amount' => 1,
                'curr' => 'RON',
                'invoice_id' => 1,
                'order_desc' => 'Order 000001 with 2 items.',
                'merch_id' => 'test',
                'timestamp' => '20200425080400',
                'nonce' => '09f640b9727c4e65e98c40ebc7988222'
            ])
            ->shouldReturn('55C24C1074AF78B71F9EB1C8ECAA1476');
    }

    public function it_get_data_for_hmac(
        OrderInterface $order,
        PaymentInterface $payment
    ): void
    {
        $this->setAuthorizationData('test', '0ae123');
        $order->getNumber()->willReturn('000001');
        $order->getItems()->willReturn(new ArrayCollection([1, 2]));
        $payment->getId()->willReturn(1);
        $payment->getCurrencyCode()->willReturn('RON');
        $payment->getAmount()->willReturn(100);

        $this
            ->getDataForHmac($order, $payment)
            ->shouldHaveKeyWithValue('amount', '1');
        $this
            ->getDataForHmac($order, $payment)
            ->shouldHaveKeyWithValue('curr', 'RON');
        $this
            ->getDataForHmac($order, $payment)
            ->shouldHaveKeyWithValue('invoice_id', '1');
        $this
            ->getDataForHmac($order, $payment)
            ->shouldHaveKeyWithValue('order_desc', 'Order 000001 with 2 items.');
        $this
            ->getDataForHmac($order, $payment)
            ->shouldHaveKeyWithValue('merch_id', 'test');
    }
}