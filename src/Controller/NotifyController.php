<?php

/**
 * This file was created by the developers from Infifni.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://infifnisoftware.ro and write us
 * an email on contact@infifnisoftware.ro.
 */

declare(strict_types=1);

namespace Infifni\SyliusEuPlatescPlugin\Controller;

use Payum\Core\Exception\LogicException;
use Payum\Core\Payum;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Request\Notify;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class NotifyController
{
    /**
     * @var Payum
     */
    private $payum;

    /**
     * @var PaymentRepositoryInterface
     */
    private $paymentRepository;

    /**
     * @param Payum $payum
     * @param PaymentRepositoryInterface $paymentRepository
     */
    public function __construct(Payum $payum, PaymentRepositoryInterface $paymentRepository)
    {
        $this->payum = $payum;
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws ReplyInterface
     */
    public function doAction(Request $request): Response
    {
        if (null === $paymentId = $request->request->get('invoice_id', null)) {
            throw new LogicException('A parameter invoice_id could not be found.');
        }

        /** @var PaymentInterface $payment */
        $payment = $this->paymentRepository->findOneBy(['id' => $paymentId]);

        if (null === $payment) {
            throw new NotFoundHttpException('Payment not found');
        }

        $hash = null !== $payment ? $payment->getDetails()['ExtraData'] : '';

        if (false === $token = $this->payum->getTokenStorage()->find($hash)) {
            throw new NotFoundHttpException(sprintf('A token with hash `%s` could not be found.', $hash));
        }

        $gateway = $this->payum->getGateway($token->getGatewayName());

        $gateway->execute(new Notify($token));

        return new Response('[accepted]');
    }
}
