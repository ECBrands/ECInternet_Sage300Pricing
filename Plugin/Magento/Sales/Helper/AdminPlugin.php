<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Plugin\Magento\Sales\Helper;

use Magento\Framework\DataObject;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Helper\Admin;
use Magento\Sales\Model\Order;

/**
 * Plugin for Magento\Sales\Helper\Admin
 */
class AdminPlugin
{
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * DataPlugin constructor.
     *
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Get price
     *
     * @param \Magento\Sales\Helper\Admin   $admin
     * @param string                        $result
     * @param \Magento\Framework\DataObject $dataObject
     * @param float                         $basePrice
     * @param float                         $price
     * @param bool                          $strong
     * @param string                        $separator
     *
     * @return string
     * @noinspection PhpMissingParamTypeInspection
     * @noinspection PhpUnusedParameterInspection
     */
    public function afterDisplayPrices(
        Admin $admin,
        $result,
        DataObject $dataObject,
        $basePrice,
        $price,
        $strong = false,
        $separator = '<br/>'
    ) {
        $order = $dataObject instanceof Order
            ? $dataObject
            : $dataObject->getData('order');

        $value = $order
            ? $order->formatPrice($price)
            : $this->priceCurrency->format($price);

        if ($strong) {
            $value = '<strong>' . $value . '</strong>';
        }

        return $value;
    }
}
