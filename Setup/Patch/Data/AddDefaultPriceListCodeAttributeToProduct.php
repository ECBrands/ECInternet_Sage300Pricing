<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * @SuppressWarnings(PHPMD.LongClassName)
 */
class AddDefaultPriceListCodeAttributeToProduct implements DataPatchInterface
{
    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $setup;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $setup
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->setup           = $setup;
    }

    public static function getDependencies(): array
    {
        return [AddCurrencyCodeAttributeToCustomer::class];
    }

    public function getAliases(): array
    {
        return [];
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function apply(): void
    {
        $this->setup->getConnection()->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->setup]);
        $eavSetup->addAttribute(Product::ENTITY, 'default_price_list_code', [
            'type'                    => 'varchar',
            'label'                   => 'Default Price List Code',
            'global'                  => ScopedAttributeInterface::SCOPE_GLOBAL,
            'visible'                 => true,
            'required'                => false,
            'user_defined'            => false,
            'default'                 => '',
            'searchable'              => false,
            'filterable'              => false,
            'comparable'              => false,
            'visible_on_front'        => false,
            'used_in_product_listing' => false,
            'unique'                  => false,
            'apply_to'                => '',
            'nullable'                => true,
        ]);

        $this->setup->getConnection()->endSetup();
    }
}
