<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Test\Integration\Setup;

use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Exception;
use PHPUnit\Framework\TestCase;

class ExtensionInstallTest extends TestCase
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    protected function setUp(): void
    {
        $objectManager            = Bootstrap::getObjectManager();
        $this->eavConfig          = $objectManager->get(EavConfig::class);
        $this->resourceConnection = $objectManager->get(ResourceConnection::class);
    }

    // -------------------------------------------------------------------------
    // Customer EAV attributes
    // -------------------------------------------------------------------------

    public function testCustomerAttributeCustomerTypeWasCreatedCorrectly(): void
    {
        $attribute = $this->getAttribute('customer', 'customer_type');

        $this->assertNotNull($attribute, 'Customer attribute "customer_type" does not exist.');
        $this->assertEquals('varchar', $attribute->getBackendType());
        $this->assertEquals('Customer Type', $attribute->getStoreLabel());
        $this->assertEquals('text', $attribute->getFrontendInput());
        $this->assertEquals(0, $attribute->getIsRequired());
        $this->assertEquals(1, $attribute->getData('is_visible'));
        $this->assertEquals(0, $attribute->getIsUserDefined());
        $this->assertEquals(999, $attribute->getData('sort_order'));
        $this->assertEquals(['adminhtml_customer'], $attribute->getUsedInForms());
    }

    public function testCustomerAttributeCurrencyCodeWasCreatedCorrectly(): void
    {
        $attribute = $this->getAttribute('customer', 'currency_code');
        if ($attribute === null) {
            $this->fail('Customer attribute "currency_code" does not exist.');
        }

        $this->assertEquals('varchar', $attribute->getBackendType());
        $this->assertEquals('Currency Code', $attribute->getStoreLabel());
        $this->assertEquals('text', $attribute->getFrontendInput());
        $this->assertEquals(0, $attribute->getIsRequired());
        $this->assertEquals(1, $attribute->getData('is_visible'));
        $this->assertEquals(0, $attribute->getIsUserDefined());
        $this->assertEquals(999, $attribute->getData('sort_order'));
        $this->assertEquals(['adminhtml_customer'], $attribute->getUsedInForms());
    }

    // -------------------------------------------------------------------------
    // Product EAV attributes
    // -------------------------------------------------------------------------

    public function testProductAttributeDefaultPriceListCodeWasCreatedCorrectly(): void
    {
        $attribute = $this->getAttribute('catalog_product', 'default_price_list_code');
        if ($attribute === null) {
            $this->fail('Product attribute "default_price_list_code" does not exist.');
        }

        $this->assertEquals('varchar', $attribute->getBackendType());
        $this->assertEquals('Default Price List Code', $attribute->getStoreLabel());
        $this->assertEquals(0, $attribute->getIsRequired());
        $this->assertEquals(1, $attribute->getData('is_visible'));
        $this->assertEquals(0, $attribute->getIsUserDefined());
        $this->assertEquals(0, $attribute->getIsUnique());
    }

    // -------------------------------------------------------------------------
    // Custom tables (db_schema.xml)
    // -------------------------------------------------------------------------

    public function testIccuprTableWasCreated(): void
    {
        $connection = $this->getConnection();
        $table      = $this->resourceConnection->getTableName('ecinternet_sage300pricing_iccupr');

        $this->assertTrue($connection->isTableExists($table), 'ecinternet_sage300pricing_iccupr table should exist.');
        $this->assertTrue($connection->tableColumnExists($table, 'entity_id'),  'iccupr.entity_id column should exist.');
        $this->assertTrue($connection->tableColumnExists($table, 'created_at'), 'iccupr.created_at column should exist.');
        $this->assertTrue($connection->tableColumnExists($table, 'updated_at'), 'iccupr.updated_at column should exist.');
        $this->assertTrue($connection->tableColumnExists($table, 'CUSTNO'),     'iccupr.CUSTNO column should exist.');
        $this->assertTrue($connection->tableColumnExists($table, 'ITEMNO'),     'iccupr.ITEMNO column should exist.');
        $this->assertTrue($connection->tableColumnExists($table, 'PRICELIST'),  'iccupr.PRICELIST column should exist.');
    }

    public function testIcpricTableWasCreated(): void
    {
        $connection = $this->getConnection();
        $table      = $this->resourceConnection->getTableName('ecinternet_sage300pricing_icpric');

        $this->assertTrue($connection->isTableExists($table), 'ecinternet_sage300pricing_icpric table should exist.');
        $this->assertTrue($connection->tableColumnExists($table, 'entity_id'),  'icpric.entity_id column should exist.');
        $this->assertTrue($connection->tableColumnExists($table, 'CURRENCY'),   'icpric.CURRENCY column should exist.');
        $this->assertTrue($connection->tableColumnExists($table, 'ITEMNO'),     'icpric.ITEMNO column should exist.');
        $this->assertTrue($connection->tableColumnExists($table, 'PRICELIST'),  'icpric.PRICELIST column should exist.');
    }

    public function testIcpricpTableWasCreated(): void
    {
        $connection = $this->getConnection();
        $table      = $this->resourceConnection->getTableName('ecinternet_sage300pricing_icpricp');

        $this->assertTrue($connection->isTableExists($table), 'ecinternet_sage300pricing_icpricp table should exist.');
        $this->assertTrue($connection->tableColumnExists($table, 'entity_id'),   'icpricp.entity_id column should exist.');
        $this->assertTrue($connection->tableColumnExists($table, 'CURRENCY'),    'icpricp.CURRENCY column should exist.');
        $this->assertTrue($connection->tableColumnExists($table, 'ITEMNO'),      'icpricp.ITEMNO column should exist.');
        $this->assertTrue($connection->tableColumnExists($table, 'PRICELIST'),   'icpricp.PRICELIST column should exist.');
        $this->assertTrue($connection->tableColumnExists($table, 'DPRICETYPE'),  'icpricp.DPRICETYPE column should exist.');
        $this->assertTrue($connection->tableColumnExists($table, 'UNITPRICE'),   'icpricp.UNITPRICE column should exist.');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function getAttribute(string $entityTypeCode, string $attributeCode)
    {
        try {
            if ($attribute = $this->eavConfig->getAttribute($entityTypeCode, $attributeCode)) {
                if ($attribute->getAttributeId()) {
                    return $attribute;
                }
            }
        } catch (Exception) {
        }

        return null;
    }

    private function getConnection(): AdapterInterface
    {
        return $this->resourceConnection->getConnection();
    }
}
