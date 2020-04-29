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

use Doctrine\Common\Collections\ArrayCollection;
use Infifni\SyliusEuPlatescPlugin\Action\ConvertPaymentAction;
use Infifni\SyliusEuPlatescPlugin\Bridge\EuPlatescBridgeInterface;
use Payum\Core\Action\ActionInterface;
use PhpSpec\ObjectBehavior;
use Payum\Core\Request\Convert;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;

final class ConvertPaymentActionSpec extends ObjectBehavior
{
    public function let(EuPlatescBridgeInterface $euPlatescBridge): void
    {
        $this->beConstructedWith($euPlatescBridge);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ConvertPaymentAction::class);
    }

    public function it_implements_action_interface(): void
    {
        $this->shouldHaveType(ActionInterface::class);
    }

    public function it_executes(
        Convert $request,
        AddressInterface $billingAddress,
        AddressInterface $shippingAddress,
        PaymentInterface $payment,
        OrderInterface $order,
        CustomerInterface $customer,
        EuPlatescBridgeInterface $euPlatescBridge
    ): void
    {
        $euPlatescBridge->setAuthorizationData('testmid', 'testmkey');

        $customer->getEmail()->willReturn('contact@infifnisoftware.ro');
        $customer->getId()->willReturn(1);

        $shippingAddress->getId()->willReturn(1);
        $shippingAddress->getFirstName()->willReturn('Laurențiu');
        $shippingAddress->getLastName()->willReturn('Cocoș');
        $shippingAddress->getCompany()->willReturn('S.C. Infifni Dezvoltare Software S.R.L.');
        $shippingAddress->getCountryCode()->willReturn('RO');
        $shippingAddress->getCity()->willReturn('Ghighișeni');
        $shippingAddress->getProvinceName()->willReturn('Bihor');
        $shippingAddress->getPostcode()->willReturn('417417');
        $shippingAddress->getStreet()->willReturn('Ghighișeni 103');
        $shippingAddress->getPhoneNumber()->willReturn('0774425731');
        $shippingAddress->getCustomer()->willReturn($customer);

        $billingAddress->getId()->willReturn(1);
        $billingAddress->getFirstName()->willReturn('Laurențiu');
        $billingAddress->getLastName()->willReturn('Cocoș');
        $billingAddress->getCompany()->willReturn('S.C. Infifni Dezvoltare Software S.R.L.');
        $billingAddress->getCountryCode()->willReturn('RO');
        $billingAddress->getCity()->willReturn('Ghighișeni');
        $billingAddress->getProvinceName()->willReturn('Bihor');
        $billingAddress->getPostcode()->willReturn('417417');
        $billingAddress->getStreet()->willReturn('Ghighișeni 103');
        $billingAddress->getPhoneNumber()->willReturn('0774425731');
        $billingAddress->getCustomer()->willReturn($customer);

        $order->getNumber()->willReturn(000001);
        $order->getCustomer()->willReturn($customer);
        $order->getBillingAddress()->willReturn($billingAddress);
        $order->getShippingAddress()->willReturn($shippingAddress);
        $order->getItems()->willReturn(new ArrayCollection([1, 2]));

        $payment->getOrder()->willReturn($order);
        $payment->getId()->willReturn(1);
        $payment->getAmount()->willReturn(100);
        $payment->getCurrencyCode()->willReturn('RON');

        $hashData = [
            'amount' => 1,
            'curr' => 'RON',
            'invoice_id' => 1,
            'order_desc' => 'Order 000001 with 2 items.',
            'merch_id' => 'test',
            'timestamp' => '20200425080400',
            'nonce' => '09f640b9727c4e65e98c40ebc7988222'
        ];
        $euPlatescBridge->getDataForHmac($order, $payment)->willReturn($hashData);
        $euPlatescBridge->hmacFormat($hashData)->willReturn('testfphash');

        $request->getSource()->willReturn($payment);
        $request->getTo()->willReturn('array');
        $request->setResult([
            'fname' => 'Laurențiu',
            'lname' => 'Cocoș',
            'country' => 'RO',
            'company' => 'S.C. Infifni Dezvoltare Software S.R.L.',
            'city' => 'Ghighișeni',
            'state' => 'Bihor',
            'zip_code' => '417417',
            'add' => 'Ghighișeni 103',
            'email' => 'contact@infifnisoftware.ro',
            'phone' => '0774425731',
            'fax' => '',
            'amount' => 1,
            'curr' => 'RON',
            'invoice_id' => 1,
            'order_desc' => 'Order 000001 with 2 items.',
            'merch_id' => 'test',
            'timestamp' => '20200425080400',
            'nonce' => '09f640b9727c4e65e98c40ebc7988222',
            'fp_hash' => 'testfphash',
            'sfname' => 'Laurențiu',
            'slname' => 'Cocoș',
            'scountry' => 'RO',
            'scompany' => 'S.C. Infifni Dezvoltare Software S.R.L.',
            'scity' => 'Ghighișeni',
            'sstate' => 'Bihor',
            'szip_code' => '417417',
            'sadd' => 'Ghighișeni 103',
            'semail' => 'contact@infifnisoftware.ro',
            'sphone' => '0774425731',
            'sfax' => '',
        ])->shouldBeCalled();

        $this->execute($request);
    }

    public function it_supports_only_convert_request_payment_source_and_array_to(
        Convert $request,
        PaymentInterface $payment
    ): void
    {
        $request->getSource()->willReturn($payment);
        $request->getTo()->willReturn('array');

        $this->supports($request)->shouldReturn(true);
    }
}
