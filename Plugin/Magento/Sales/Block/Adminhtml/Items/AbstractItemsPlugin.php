<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Plugin\Magento\Sales\Block\Adminhtml\Items;

use Magento\Sales\Block\Adminhtml\Items\AbstractItems;

/**
 * Plugin for Magento\Sales\Block\Adminhtml\Items\AbstractItems
 */
class AbstractItemsPlugin
{
    /**
     * Don't show the base currency, show the currency that was actually used
     *
     * @param \Magento\Sales\Block\Adminhtml\Items\AbstractItems $subject
     * @param string                                             $result
     * @param float                                              $basePrice
     * @param float                                              $price
     * @param int                                                $precision
     * @param false                                              $strong
     * @param string                                             $separator
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @noinspection PhpMissingParamTypeInspection
     * @noinspection PhpUnusedParameterInspection
     */
    public function afterDisplayRoundedPrices(
        AbstractItems $subject,
        $result,
        $basePrice,
        $price,
        $precision = 2,
        $strong = false,
        $separator = '<br />'
    ) {
        $value = $subject->getOrder()->formatPricePrecision($price, $precision);
        if ($strong) {
            $value = '<strong>' . $value . '</strong>';
        }

        return $value;
    }
}
