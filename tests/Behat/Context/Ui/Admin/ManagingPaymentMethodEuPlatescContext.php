<?php

/**
 * This file was created by the developers from Infifni.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://infifnisoftware.ro and write us
 * an email on contact@infifnisoftware.ro.
 */

declare(strict_types=1);

namespace Tests\Infifni\SyliusEuPlatescPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use FriendsOfBehat\PageObjectExtension\Page\SymfonyPageInterface;
use Sylius\Behat\Service\Resolver\CurrentPageResolverInterface;
use Tests\Infifni\SyliusEuPlatescPlugin\Behat\Page\Admin\PaymentMethod\CreatePageInterface;

final class ManagingPaymentMethodEuPlatescContext implements Context
{
    /** @var CurrentPageResolverInterface */
    private $currentPageResolver;

    /** @var CreatePageInterface */
    private $createPage;

    public function __construct(
        CurrentPageResolverInterface $currentPageResolver,
        CreatePageInterface $createPage
    ) {
        $this->createPage = $createPage;
        $this->currentPageResolver = $currentPageResolver;
    }

    /**
     * @Given I want to create a new EuPlatesc payment method
     */
    public function iWantToCreateANewEuPlatescPaymentMethod(): void
    {
        $this->createPage->open(['factory' => 'euplatesc']);
    }

    /**
     * @When I configure it with test EuPlatesc credentials
     */
    public function iConfigureItWithTestEuPlatescCredentials(): void
    {
        $this->resolveCurrentPage()->setEnvironment('sandbox');
    }

    /**
     * @return CreatePageInterface|SymfonyPageInterface
     */
    private function resolveCurrentPage(): SymfonyPageInterface
    {
        return $this->currentPageResolver->getCurrentPageWithForm([
            $this->createPage,
        ]);
    }
}
