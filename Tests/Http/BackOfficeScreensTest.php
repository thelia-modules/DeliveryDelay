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

namespace DeliveryDelay\Tests\Http;

use DeliveryDelay\Model\CarrierDelayQuery;
use DeliveryDelay\Model\ProductDelayQuery;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Model\Admin;
use Thelia\Model\ModuleQuery;
use Thelia\Model\Product;
use Thelia\Model\ProfileModule;
use Thelia\Test\FixtureFactory;
use Thelia\Test\WebIntegrationTestCase;
use Thelia\Tests\Support\BackOffice\AdminSessionInjector;
use Thelia\Tests\Support\Delivery\RegistersDeliveryDateTestCarrier;

/**
 * The configuration page and the tab of the product sheet, through the real back office.
 */
final class BackOfficeScreensTest extends WebIntegrationTestCase
{
    use RegistersDeliveryDateTestCarrier;

    private const CONFIGURATION_URL = '/admin/module/DeliveryDelay';

    private ?AdminSessionInjector $injector = null;

    private int $carrierId = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->injector = new AdminSessionInjector();
        $this->getService(EventDispatcherInterface::class)->addSubscriber($this->injector);
        $this->carrierId = (int) $this->registerDeliveryDateTestCarrier(static::getContainer(), $this->getPropelConnection())->getId();
    }

    protected function tearDown(): void
    {
        $this->injector?->clear();

        parent::tearDown();
    }

    public function testThePageListsTheActiveDeliveryModulesAndLinksTheCalendarOfTheCore(): void
    {
        $this->loginAs($this->factory()->admin());

        $this->assertPageRenders(self::CONFIGURATION_URL);
        $crawler = $this->client->getCrawler();

        self::assertSame(1, $crawler->filter('[data-testid="delivery-delay-config-form"]')->count());
        self::assertContains((string) $this->carrierId, $this->rowModuleIds($crawler), 'The carrier has a row.');
        self::assertStringContainsString(
            '/admin/configuration/delivery-dates',
            (string) $crawler->filter('[data-testid="delivery-delay-calendar-link"]')->attr('href'),
        );
        self::assertSame(1, $crawler->filter('[data-testid="delivery-delay-config-save"]')->count());
    }

    public function testSavingARowWritesTheDelaysAndAnEmptyRowRemovesThem(): void
    {
        $this->loginAs($this->factory()->admin());

        $form = $this->configurationForm();
        $this->fillRow($form, ['preparationDays' => '2', 'transitMinDays' => '1', 'transitMaxDays' => '3', 'cutoffTime' => '14:30']);
        $this->client->submit($form);

        self::assertSame(302, $this->client->getResponse()->getStatusCode());
        $row = CarrierDelayQuery::create()->findOneByModuleId($this->carrierId);
        self::assertNotNull($row);
        self::assertSame([2, 1, 3, '14:30'], [$row->getPreparationDays(), $row->getTransitMinDays(), $row->getTransitMaxDays(), $row->getCutoffTime('H:i')]);

        $this->client->followRedirect();
        self::assertStringContainsString('The delays are saved.', $this->client->getCrawler()->filter('[data-testid="bo-flash-success"]')->text());

        $form = $this->configurationForm();
        self::assertSame('14:30', $form[$this->fieldOf($form, 'cutoffTime')]->getValue(), 'The saved row is shown back.');
        $this->fillRow($form, ['preparationDays' => '', 'transitMinDays' => '', 'transitMaxDays' => '', 'cutoffTime' => '']);
        $this->client->submit($form);

        self::assertNull(CarrierDelayQuery::create()->findOneByModuleId($this->carrierId), 'An empty row means no delays.');
    }

    public function testNoCutoffTimeAndNoPreparationAreStoredAsNullAndZero(): void
    {
        $this->loginAs($this->factory()->admin());

        $form = $this->configurationForm();
        $this->fillRow($form, ['preparationDays' => '', 'transitMinDays' => '0', 'transitMaxDays' => '0', 'cutoffTime' => '']);
        $this->client->submit($form);

        $row = CarrierDelayQuery::create()->findOneByModuleId($this->carrierId);
        self::assertNotNull($row);
        self::assertSame(0, $row->getPreparationDays());
        self::assertNull($row->getCutoffTime());
    }

    /**
     * @param array<string, string> $values
     */
    #[DataProvider('invalidRows')]
    public function testAnInvalidRowIsRefusedAndNothingIsWritten(array $values, string $message): void
    {
        $this->loginAs($this->factory()->admin());

        $this->client->submit($this->fillRow($this->configurationForm(), $values));
        $this->client->followRedirect();

        self::assertNull(CarrierDelayQuery::create()->findOneByModuleId($this->carrierId));
        self::assertStringContainsString($message, $this->client->getCrawler()->filter('[data-testid="bo-flash-error"]')->text());
    }

    /**
     * @return iterable<string, array{0: array<string, string>, 1: string}>
     */
    public static function invalidRows(): iterable
    {
        yield 'minimum above maximum' => [['transitMinDays' => '5', 'transitMaxDays' => '2'], 'must not exceed'];
        yield 'negative preparation' => [['preparationDays' => '-1', 'transitMinDays' => '1', 'transitMaxDays' => '2'], 'zero or more'];
        yield 'one transit time missing' => [['transitMinDays' => '1', 'transitMaxDays' => ''], 'Both transit times'];
        yield 'cut-off time not a time' => [['transitMinDays' => '1', 'transitMaxDays' => '2', 'cutoffTime' => '25:99'], 'HH:MM'];
    }

    public function testARowForAModuleThatIsNotADeliveryModuleIsIgnored(): void
    {
        $this->loginAs($this->factory()->admin());
        $payment = ModuleQuery::create()->findOneByCode('Cheque');
        self::assertNotNull($payment);

        $form = $this->configurationForm();
        $this->fillRow($form, ['transitMinDays' => '1', 'transitMaxDays' => '2']);
        $form[$this->fieldOf($form, 'moduleId')]->setValue((string) $payment->getId());
        $this->client->submit($form);

        self::assertSame(0, CarrierDelayQuery::create()->count());
    }

    public function testTheCarrierOfTheProductPageIsSaved(): void
    {
        $this->loginAs($this->factory()->admin());

        $form = $this->configurationForm();
        $form['configuration[productPageModuleId]']->setValue((string) $this->carrierId);
        $this->client->submit($form);

        self::assertSame((string) $this->carrierId, \Thelia\Model\ConfigQuery::read(\DeliveryDelay\DeliveryDelay::CONFIG_PRODUCT_PAGE_MODULE));
    }

    public function testTheProductTabSavesZeroAsAValueAndEmptyAsNotFilledIn(): void
    {
        $this->loginAs($this->factory()->admin());
        $product = $this->product();

        $form = $this->productForm($product);
        $form['product_delays[availabilityDays]'] = '0';
        $form['product_delays[restockDays]'] = '';
        $this->client->submit($form);

        $row = ProductDelayQuery::create()->findOneByProductId($product->getId());
        self::assertNotNull($row);
        self::assertSame(0, $row->getAvailabilityDays(), 'Zero is a value.');
        self::assertNull($row->getRestockDays(), 'Empty is not filled in.');

        $form = $this->productForm($product);
        self::assertSame('0', $form['product_delays[availabilityDays]']->getValue(), 'Zero is shown back as zero.');
        self::assertSame('', $form['product_delays[restockDays]']->getValue());
        $form['product_delays[availabilityDays]'] = '';
        $this->client->submit($form);

        self::assertNull(ProductDelayQuery::create()->findOneByProductId($product->getId()), 'Both empty: no row.');
    }

    public function testAnInvalidProductDelayIsRefused(): void
    {
        $this->loginAs($this->factory()->admin());
        $product = $this->product();

        $form = $this->productForm($product);
        $form['product_delays[restockDays]'] = '-3';
        $this->client->submit($form);
        $this->client->followRedirect();

        self::assertNull(ProductDelayQuery::create()->findOneByProductId($product->getId()));
        self::assertStringContainsString('zero or more', $this->client->getCrawler()->filter('[data-testid="bo-flash-error"]')->text());
    }

    public function testAnAdministratorWhoOnlyMaySeeTheModuleCannotWrite(): void
    {
        $this->loginAs($this->administratorOf([AccessManager::VIEW]));
        $product = $this->product();

        $this->assertPageRenders(self::CONFIGURATION_URL);
        self::assertSame(0, $this->client->getCrawler()->filter('[data-testid="delivery-delay-config-save"]')->count(), 'No save button without the right to update.');

        $this->client->request('POST', '/admin/modules/DeliveryDelay/save', ['configuration' => ['carriers' => [['moduleId' => $this->carrierId, 'transitMinDays' => 1, 'transitMaxDays' => 2]]]]);
        self::assertSame(403, $this->client->getResponse()->getStatusCode());
        self::assertSame(0, CarrierDelayQuery::create()->count());

        $this->client->request('POST', '/admin/modules/DeliveryDelay/product/'.$product->getId().'/save', ['product_delays' => ['availabilityDays' => 3]]);
        self::assertSame(403, $this->client->getResponse()->getStatusCode());
        self::assertNull(ProductDelayQuery::create()->findOneByProductId($product->getId()));
    }

    public function testAnAdministratorWhoMayUpdateTheModuleCanWrite(): void
    {
        $this->loginAs($this->administratorOf([AccessManager::VIEW, AccessManager::UPDATE]));

        $this->assertPageRenders(self::CONFIGURATION_URL);
        self::assertSame(1, $this->client->getCrawler()->filter('[data-testid="delivery-delay-config-save"]')->count());
    }

    private function product(): Product
    {
        $factory = $this->factory();

        return $factory->product($factory->category(), $factory->taxRule(), $factory->currency());
    }

    private function configurationForm(): Form
    {
        $this->assertPageRenders(self::CONFIGURATION_URL);

        return $this->client->getCrawler()->filter('[data-testid="delivery-delay-config-save"]')->form();
    }

    private function productForm(Product $product): Form
    {
        $this->assertPageRenders('/admin/products/update?product_id='.$product->getId());

        return $this->client->getCrawler()->filter('[data-testid="delivery-delay-product-save"]')->form();
    }

    /**
     * @param array<string, string> $values the fields of the row of the carrier
     */
    private function fillRow(Form $form, array $values): Form
    {
        foreach ($values as $field => $value) {
            $form[$this->fieldOf($form, $field)] = $value;
        }

        return $form;
    }

    /**
     * The name of a field in the row of the test carrier.
     */
    private function fieldOf(Form $form, string $field): string
    {
        foreach ($form->all() as $name => $input) {
            if (1 === preg_match('/^configuration\[carriers\]\[(\d+)\]\[moduleId\]$/', $name, $matches) && (string) $this->carrierId === $input->getValue()) {
                return \sprintf('configuration[carriers][%s][%s]', $matches[1], $field);
            }
        }

        self::fail('The carrier has no row.');
    }

    /**
     * @return list<string>
     */
    private function rowModuleIds(Crawler $crawler): array
    {
        return $crawler->filter('[data-testid="delivery-delay-carrier-row"] input[type="hidden"]')->each(
            static fn (Crawler $input): string => (string) $input->attr('value'),
        );
    }

    /**
     * @param list<string> $accesses what the profile may do with the module
     */
    private function administratorOf(array $accesses): Admin
    {
        $factory = $this->factory();
        $admin = $factory->restrictedAdmin([AdminResources::MODULE => $accesses, AdminResources::PRODUCT => [AccessManager::VIEW, AccessManager::UPDATE]]);

        $access = new AccessManager(0);
        $access->build($accesses);
        (new ProfileModule())
            ->setProfileId($admin->getProfileId())
            ->setModuleId((int) ModuleQuery::create()->findOneByCode('DeliveryDelay')->getId())
            ->setAccess($access->getAccessValue())
            ->save($this->getPropelConnection());

        return $admin;
    }

    /**
     * Deliberately not createFixtureFactory(): that helper pushes a synthetic request when the
     * stack is empty, which would then be the main request the security context reads from.
     */
    private function factory(): FixtureFactory
    {
        return new FixtureFactory($this->getPropelConnection());
    }

    private function loginAs(Admin $admin): void
    {
        $admin->eraseCredentials();
        $this->injector?->setAdmin($admin);
    }
}
