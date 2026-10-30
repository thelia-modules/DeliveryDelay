<?php

declare(strict_types=1);

/*
 * This file is part of the Thelia package.
 * http://www.thelia.net
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DeliveryDelay\Tests\Integration;

use Symfony\Component\Mime\Email;
use Thelia\Model\ConfigQuery;

final class OrderConfirmationEmailTest extends DeliveryDelayTestCase
{
    private ?string $storeEmailBefore = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storeEmailBefore = ConfigQuery::read('store_email');
        ConfigQuery::write('store_email', 'shop@example.com');
        ConfigQuery::resetCache();
    }

    protected function tearDown(): void
    {
        if (null === $this->storeEmailBefore) {
            ConfigQuery::create()->filterByName('store_email')->delete();
        } else {
            ConfigQuery::write('store_email', $this->storeEmailBefore);
        }
        ConfigQuery::resetCache();

        parent::tearDown();
    }

    public function testTheConfirmationEmailPrintsTheFrozenInterval(): void
    {
        $this->setDelays($this->carrier, 1, 2, 4);

        $order = $this->placeOrder($this->product(10));

        $email = $this->confirmationOf($order->getRef());
        $html = (string) $email->getHtmlBody();
        $text = (string) $email->getTextBody();

        self::assertStringContainsString('Livraison estimée : entre le jeudi 5 novembre 2026 et le samedi 7 novembre 2026', $html);
        self::assertStringContainsString('Livraison estimée : entre le jeudi 5 novembre 2026 et le samedi 7 novembre 2026', $text);
    }

    public function testASingleDateIsPrintedOnce(): void
    {
        $this->setDelays($this->carrier, 1, 2, 2);

        $order = $this->placeOrder($this->product(10), 'en_US');

        $html = (string) $this->confirmationOf($order->getRef())->getHtmlBody();

        self::assertStringContainsString('Estimated delivery: Thursday, November 5, 2026', $html);
        self::assertStringNotContainsString('between', $html);
    }

    public function testTheEmailKeepsTheDeliveryAddressNextToTheEstimate(): void
    {
        $this->setDelays($this->carrier, 1, 2, 2);

        $order = $this->placeOrder($this->product(10));

        $html = (string) $this->confirmationOf($order->getRef())->getHtmlBody();

        self::assertStringContainsString('Delivery address', $html);
        self::assertStringContainsString('Main Street', $html);
    }

    public function testAnOrderWithoutAnEstimateGetsTheUsualEmail(): void
    {
        $order = $this->placeOrder($this->product(10));

        $email = $this->confirmationOf($order->getRef());

        self::assertStringNotContainsString('Livraison estimée', (string) $email->getHtmlBody());
        self::assertStringNotContainsString('Livraison estimée', (string) $email->getTextBody());
    }

    private function confirmationOf(string $reference): Email
    {
        foreach (self::getMailerMessages() as $message) {
            if ($message instanceof Email && str_contains((string) $message->getSubject(), $reference)) {
                return $message;
            }
        }

        self::fail('No confirmation e-mail was sent for '.$reference.'.');
    }
}
