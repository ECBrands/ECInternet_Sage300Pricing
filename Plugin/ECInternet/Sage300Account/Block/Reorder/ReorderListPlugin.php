<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Plugin\ECInternet\Sage300Account\Block\Reorder;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use ECInternet\Sage300Account\Block\Reorder\ReorderList;
use ECInternet\Sage300Account\Helper\Uom as UomHelper;
use ECInternet\Sage300Account\Logger\Logger;
use ECInternet\Sage300Pricing\Api\IcpricpRepositoryInterface;

class ReorderListPlugin
{
    const DEFAULT_CURRENCY_CODE = 'USD';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \ECInternet\Sage300Account\Helper\Uom
     */
    private $uomHelper;

    /**
     * @var \ECInternet\Sage300Account\Logger\Logger
     */
    private $logger;

    /**
     * @var \ECInternet\Sage300Pricing\Api\IcpricpRepositoryInterface
     */
    private $icpricpRepository;

    /**
     * ReorderListPlugin constructor.
     *
     * @param \Magento\Store\Model\StoreManagerInterface                $storeManager
     * @param \ECInternet\Sage300Account\Helper\Uom                     $uomHelper
     * @param \ECInternet\Sage300Account\Logger\Logger                  $logger
     * @param \ECInternet\Sage300Pricing\Api\IcpricpRepositoryInterface $icpricpRepository
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        UomHelper $uomHelper,
        Logger $logger,
        IcpricpRepositoryInterface $icpricpRepository
    ) {
        $this->storeManager      = $storeManager;
        $this->uomHelper         = $uomHelper;
        $this->logger            = $logger;
        $this->icpricpRepository = $icpricpRepository;
    }

    public function aroundGetUom(
        /** @noinspection PhpUnusedParameterInspection */ ReorderList $subject,
        callable $proceed,
        Product $product
    ) {
        // Cache sku
        $sku = $product->getSku();

        if ($currencyCode = $this->getStoreCurrencyCode()) {
            if ($priceListCode = $this->getPriceListCode($product)) {
                if ($itemDetailPricing = $this->icpricpRepository->get($currencyCode, $sku, $priceListCode)) {
                    if ($quantityOrWeightUnit = $itemDetailPricing->getQuantityOrWeightUnit()) {
                        if ($uomLabel = $this->getUomLabel($quantityOrWeightUnit)) {
                            return $uomLabel;
                        } else {
                            $this->log('aroundGetUom()', ['sku' => $sku, 'result' => 'Blank uomLabel.']);
                        }
                    } else {
                        $this->log('aroundGetUom()', ['sku' => $sku, 'result' => 'ICPRIC.getQuantityOrWeightUnit returned falsy.']);
                    }
                } else {
                    $this->log('aroundGetUom()', ['sku' => $sku, 'result' => 'Could not find itemDetailPricing record.']);
                }
            } else {
                $this->log('aroundGetUom()', ['sku' => $sku, 'result' => 'Could not find priceListCode for product.']);
            }
        } else {
            $this->log('aroundGetUom()', ['sku' => $sku, 'result' => 'Could not get storeCurrencyCode.']);
        }

        return $proceed($product);
    }

    /**
     * Get current store currency code
     *
     * @return string
     */
    private function getStoreCurrencyCode()
    {
        try {
            return $this->storeManager->getStore()->getCurrentCurrencyCode();
        } catch (NoSuchEntityException $e) {
            $this->log('getStoreCurrencyCode()', ['exception' => $e->getMessage()]);
        }

        return self::DEFAULT_CURRENCY_CODE;
    }

    private function getUomLabel(string $uom)
    {
        if ($translatedUom = $this->uomHelper->translateUom($uom)) {
            return $translatedUom;
        }

        return $uom;
    }

    /**
     * Pull 'default_price_list_code' attribute data from Product
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return string|null
     */
    private function getPriceListCode(
        Product $product
    ) {
        $this->log('getPriceListCode()', ['sku' => $product->getSku()]);

        if ($defaultPriceListCode = $product->getCustomAttribute('default_price_list_code')) {
            if ($defaultPriceListCodeValue = $defaultPriceListCode->getValue()) {
                return $defaultPriceListCodeValue;
            } else {
                $this->log("getPriceListCode() - Product missing 'default_price_list_code' attribute value");
            }
        } else {
            $this->log("getPriceListCode() - Product missing 'default_price_list_code' attribute");
        }

        return null;
    }

    /**
     * Write to extension log
     *
     * @param string $message
     * @param array  $extra
     *
     * @return void
     */
    private function log(string $message, array $extra = [])
    {
        $this->logger->info('Plugin/ECInternet/Sage300Account/Block/Reorder/ReorderListPlugin - ' . $message, $extra);
    }
}
