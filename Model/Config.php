<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    const CONFIG_PATH_ENABLED               = 'sage300pricing/general/enable';

    const CONFIG_PATH_ADMIN_PRICING_DISPLAY = 'sage300pricing/display/admin_pricing_title';

    const CONFIG_PATH_USE_LOWEST_PRICE      = 'sage300pricing/calculation/use_lowest_price';

    const CONFIG_PATH_SHOW_TIER_PRICES      = 'sage300pricing/tier_prices/show_tiers';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Config constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Is module enabled?
     *
     * @return bool
     */
    public function isModuleEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::CONFIG_PATH_ENABLED);
    }

    /**
     * @return string
     */
    public function getAdminPricingTitle()
    {
        return (string)$this->scopeConfig->getValue(self::CONFIG_PATH_ADMIN_PRICING_DISPLAY);
    }

    /**
     * @return bool
     */
    public function shouldUseLowestPrice()
    {
        return $this->scopeConfig->isSetFlag(self::CONFIG_PATH_USE_LOWEST_PRICE);
    }

        /**
     * Should we show our custom tier price display?
     *
     * @return bool
     */
    public function shouldShowTierPrices()
    {
        return $this->scopeConfig->isSetFlag(self::CONFIG_PATH_SHOW_TIER_PRICES);
    }
}
