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

use Infifni\SyliusEuPlatescPlugin\Bridge\EuPlatescBridgeInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Payum;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Request\Notify;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

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
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param Payum $payum
     * @param PaymentRepositoryInterface $paymentRepository
     * @param TranslatorInterface $translator
     * @param RouterInterface $router
     */
    public function __construct(
        Payum $payum,
        PaymentRepositoryInterface $paymentRepository,
        TranslatorInterface $translator,
        RouterInterface $router
    ) {
        $this->payum = $payum;
        $this->paymentRepository = $paymentRepository;
        $this->translator = $translator;
        $this->router = $router;
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

        /** @var PaymentInterface $payment */
        $payment = $this->paymentRepository->findOneBy(['id' => $paymentId]);
        $paymentDetails = $payment->getDetails();
        if (EuPlatescBridgeInterface::COMPLETED_STATUS === $paymentDetails['euplatesc_status']) {
            return new Response(
                $this->translator->trans('ui.notify.transaction_successful', [
                    '%url%' => $this->router->generate('sylius_shop_account_dashboard')
                ])
            );
        }

        return new Response(
            $this->translator->trans('ui.notify.transaction_failed', [
                '%url%' => $this->router->generate('sylius_shop_account_dashboard')
            ])
        );
    }
}
