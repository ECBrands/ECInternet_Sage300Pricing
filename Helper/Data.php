<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Helper
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Data extends AbstractHelper
{
    const CONFIG_PATH_ENABLED               = 'sage300pricing/general/enable';

    const CONFIG_PATH_ADMIN_PRICING_DISPLAY = 'sage300pricing/display/admin_pricing_title';

    const CONFIG_PATH_SHOW_TIER_PRICES      = 'sage300pricing/tier_prices/show_tiers';

    /**
     * Is module enabled?
     *
     * @return bool
     */
    public function isModuleEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::CONFIG_PATH_ENABLED);
    }

    public function getAdminPricingTitle()
    {
        return (string)$this->scopeConfig->getValue(self::CONFIG_PATH_ADMIN_PRICING_DISPLAY);
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
