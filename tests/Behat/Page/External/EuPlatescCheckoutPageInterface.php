<?php

/**
 * This file was created by the developers from Infifni.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://infifnisoftware.ro and write us
 * an email on contact@infifnisoftware.ro.
 */

declare(strict_types=1);

namespace Tests\Infifni\SyliusEuPlatescPlugin\Behat\Page\External;

use FriendsOfBehat\PageObjectExtension\Page\PageInterface;

interface EuPlatescCheckoutPageInterface extends PageInterface
{
    public function pay(): void;

    public function failedPayment(): void;
}
