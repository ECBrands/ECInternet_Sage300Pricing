<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Block\Catalog\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Json\EncoderInterface as JsonEncoderInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Url\EncoderInterface as UrlEncoderInterface;
use ECInternet\Sage300Account\Helper\Data as Sage300AccountHelper;
use ECInternet\Sage300Pricing\Api\IcpricRepositoryInterface;
use ECInternet\Sage300Pricing\Helper\Customer as CustomerHelper;
use ECInternet\Sage300Pricing\Helper\Data;
use ECInternet\Sage300Pricing\Logger\Logger;
use Exception;

/**
 * Catalog Product View Block
 */
class View extends \Magento\Catalog\Block\Product\View
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \ECInternet\Sage300Account\Helper\Data
     */
    private $sage300AccountHelper;

    /**
     * @var \ECInternet\Sage300Pricing\Api\IcpricRepositoryInterface
     */
    private $icpricRepository;

    /**
     * @var \ECInternet\Sage300Pricing\Helper\Customer
     */
    private $customerHelper;

    /**
     * @var \ECInternet\Sage300Pricing\Helper\Data
     */
    private $helper;

    /**
     * @var \ECInternet\Sage300Pricing\Logger\Logger
     */
    private $logger;

    /**
     * View constructor.
     *
     * @param \Magento\Catalog\Block\Product\Context                   $context
     * @param \Magento\Catalog\Api\ProductRepositoryInterface          $productRepository
     * @param \Magento\Catalog\Helper\Product                          $productHelper
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface      $productTypeConfig
     * @param \Magento\Customer\Model\Session                          $customerSession
     * @param \Magento\Framework\Json\EncoderInterface                 $jsonEncoder
     * @param \Magento\Framework\Locale\FormatInterface                $localeFormat
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface        $priceCurrency
     * @param \Magento\Framework\Stdlib\StringUtils                    $string
     * @param \Magento\Customer\Api\CustomerRepositoryInterface        $customerRepository
     * @param \Magento\Framework\Url\EncoderInterface                  $urlEncoder
     * @param \ECInternet\Sage300Account\Helper\Data                   $sage300AccountHelper
     * @param \ECInternet\Sage300Pricing\Api\IcpricRepositoryInterface $icpricRepository
     * @param \ECInternet\Sage300Pricing\Helper\Data                   $helper
     * @param \ECInternet\Sage300Pricing\Helper\Customer               $customerHelper
     * @param \ECInternet\Sage300Pricing\Logger\Logger                 $logger
     * @param array                                                    $data
     */
    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        ProductHelper $productHelper,
        ConfigInterface $productTypeConfig,
        CustomerSession $customerSession,
        JsonEncoderInterface $jsonEncoder,
        FormatInterface $localeFormat,
        PriceCurrencyInterface $priceCurrency,
        StringUtils $string,
        CustomerRepositoryInterface $customerRepository,
        UrlEncoderInterface $urlEncoder,
        Sage300AccountHelper $sage300AccountHelper,
        IcpricRepositoryInterface $icpricRepository,
        Data $helper,
        CustomerHelper $customerHelper,
        Logger $logger,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );

        // Don't use per Magento - https://developer.adobe.com/commerce/php/development/cache/page/private-content/
        //$this->_isScopePrivate = true;

        $this->customerRepository   = $customerRepository;
        $this->sage300AccountHelper = $sage300AccountHelper;
        $this->icpricRepository     = $icpricRepository;
        $this->customerHelper       = $customerHelper;
        $this->helper               = $helper;
        $this->logger               = $logger;
    }

    /**
     * Should we show the Block?
     *
     * @return bool
     */
    public function showTierPriceBlock()
    {
        return $this->helper->isModuleEnabled();
    }

    public function getTierPriceHeaderTitle()
    {
        return 'Bulk Discount';
    }

    public function getTierPrices()
    {
        $this->log('getTierPrices()');

        $tierPrices = [];

        /** @var \Magento\Catalog\Model\Product $product */
        if ($product = $this->getProduct()) {
            /** @var float $productPrice */
            /** @noinspection PhpCastIsUnnecessaryInspection */
            $productPrice = (float)$product->getPrice(); // cast is needed because magento lies

            if ($pricingRecord = $this->getPricingRecord($product)) {
                $tierPriceArray = $pricingRecord->getTierPrices();

                for ($i = 0; $i < count($tierPriceArray); $i++) {
                    $tierPrice = $tierPriceArray[$i];

                    $percentSavings = $tierPrice['percentage'] ??
                        $this->getPercentSavings($productPrice, $productPrice - $tierPrice['amount']);

                    $tierPrices[] = [
                        'qty'            => $tierPrice['qty'],
                        'percentSavings' => $percentSavings
                    ];
                }
            } else {
                $this->log('getTierPrices() - No pricing record');
            }
        } else {
            $this->log('getTierPrices() - No product');
        }

        return $tierPrices;
    }

    public function getTierPriceDiscountMessage(array $tierPrice)
    {
        return sprintf(
            'Purchase %d units to get a %d%% discount',
            $tierPrice['qty'],
            (float)$tierPrice['percentSavings']
        );
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return \ECInternet\Sage300Pricing\Api\Data\IcpricInterface|null
     */
    private function getPricingRecord(
        Product $product
    ) {
        $this->log('getPricingRecord()');

        $currencyCode = $this->getCurrencyCode();
        $this->log('getPricingRecord()', ['currencyCode' => $currencyCode]);
        if (empty($currencyCode)) {
            $this->log('getPricingRecord() - empty currencyCode');
            return null;
        }

        $pricelist = $this->getPricelist();
        $this->log('getPricingRecord()', ['pricelist' => $pricelist]);
        if (empty($pricelist)) {
            $this->log('getPricingRecord() - empty pricelist');
            return null;
        }

        return $this->icpricRepository->get($currencyCode, $product->getSku(), $pricelist);
    }

    private function getCurrencyCode()
    {
        $this->log('getCurrencyCode()');

        return $this->customerHelper->getCurrentStoreCurrencyCode();
    }

    private function getPricelist()
    {
        $this->log('getPricelist()');

        if ($customer = $this->getCurrentCustomer()) {
            $customerGroupId = $customer->getGroupId();
            if (is_numeric($customerGroupId)) {
                try {
                    return $this->sage300AccountHelper->getCustomerGroupCode((int)$customerGroupId);
                } catch (Exception $e) {
                    $this->log('getPricelist()', ['error' => $e->getMessage()]);
                }
            }
        }

        return null;
    }

    private function getCurrentCustomer()
    {
        try {
            return $this->customerRepository->getById($this->getCustomerId());
        } catch (Exception $e) {
            $this->log('getCurrentCustomer()', ['exception' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * @param float $basePrice
     * @param float $discountedPrice
     *
     * @return float
     */
    private function getPercentSavings(float $basePrice, float $discountedPrice)
    {
        return round(100 - round(($discountedPrice / $basePrice) * 100, 2));
    }

    public function log(string $message, array $extra = [])
    {
        $this->logger->info('Block/Catalog/Product/View - ' . $message, $extra);
    }
}
