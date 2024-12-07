<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Pricing;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use ECInternet\Pricing\Api\Data\PricingSystemInterface;
use ECInternet\Sage300Account\Helper\Uom as UomHelper;
use ECInternet\Sage300Pricing\Api\IccuprRepositoryInterface;
use ECInternet\Sage300Pricing\Api\IcpricRepositoryInterface;
use ECInternet\Sage300Pricing\Helper\Customer as CustomerHelper;
use ECInternet\Sage300Pricing\Helper\Data;
use ECInternet\Sage300Pricing\Helper\Quote as QuoteHelper;
use ECInternet\Sage300Pricing\Logger\Logger;
use ECInternet\Sage300Pricing\Model\Config;
use ECInternet\Sage300Pricing\Model\Data\Iccupr;
use Exception;

class Sage300 implements PricingSystemInterface
{
    const PRICING_SYSTEM_NAME                = 'sage300';

    const CUSTOMER_ATTRIBUTE_CURRENCY_CODE   = 'currency_code';

    const CUSTOMER_ATTRIBUTE_CUSTOMER_TYPE   = 'customer_type';

    const CUSTOMER_ATTRIBUTE_CUSTOMER_NUMBER = 'customer_number';

    /**
     * @var \ECInternet\Sage300Account\Helper\Uom
     */
    private $uomHelper;

    /**
     * @var \ECInternet\Sage300Pricing\Api\IccuprRepositoryInterface
     */
    private $iccuprRepository;

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
     * @var \ECInternet\Sage300Pricing\Helper\Quote
     */
    private $quoteHelper;

    /**
     * @var \ECInternet\Sage300Pricing\Logger\Logger
     */
    private $logger;

    /**
     * @var \ECInternet\Sage300Pricing\Model\Config
     */
    private $config;

    /**
     * Sage300 constructor.
     *
     * @param \ECInternet\Sage300Account\Helper\Uom                    $uomHelper
     * @param \ECInternet\Sage300Pricing\Api\IccuprRepositoryInterface $iccuprRepository
     * @param \ECInternet\Sage300Pricing\Api\IcpricRepositoryInterface $icpricRepository
     * @param \ECInternet\Sage300Pricing\Helper\Customer               $customerHelper
     * @param \ECInternet\Sage300Pricing\Helper\Data                   $helper
     * @param \ECInternet\Sage300Pricing\Helper\Quote                  $quoteHelper
     * @param \ECInternet\Sage300Pricing\Logger\Logger                 $logger
     * @param \ECInternet\Sage300Pricing\Model\Config                  $config
     */
    public function __construct(
        UomHelper $uomHelper,
        IccuprRepositoryInterface $iccuprRepository,
        IcpricRepositoryInterface $icpricRepository,
        CustomerHelper $customerHelper,
        Data $helper,
        QuoteHelper $quoteHelper,
        Logger $logger,
        Config $config
    ) {
        $this->uomHelper        = $uomHelper;
        $this->iccuprRepository = $iccuprRepository;
        $this->icpricRepository = $icpricRepository;
        $this->customerHelper   = $customerHelper;
        $this->helper           = $helper;
        $this->quoteHelper      = $quoteHelper;
        $this->logger           = $logger;
        $this->config           = $config;
    }

    public function getName()
    {
        return self::PRICING_SYSTEM_NAME;
    }

    public function getPrice(string $sku, float $quantity = 1.0)
    {
        $this->log('getPrice()', ['sku' => $sku, 'quantity' => $quantity]);

        if ($customer = $this->customerHelper->getSessionCustomerInterface()) {
            try {
                return $this->getCustomPrice($customer, $sku, $quantity);
            } catch (LocalizedException $e) {
                $this->log('getPrice()', ['sku' => $sku, 'quantity' => $quantity, 'exception' => $e->getMessage()]);
            }
        } else {
            $this->log('getPrice() - SessionCustomerInterface not found.');
        }

        return null;
    }

    public function getPriceForQuoteItem(
        QuoteItem $quoteItem
    ) {
        /** @var \Magento\Quote\Model\Quote $quote */
        if ($quote = $quoteItem->getQuote()) {
            $this->log('getPriceForQuoteItem()', [
                'quoteId'     => $quote->getId(),
                'quoteItem'   => $quoteItem->getSku(),
                'quoteItemId' => $quoteItem->getId()
            ]);

            $qty = $quoteItem->getQty();
            if (!is_numeric($qty)) {
                $this->log('getPriceForQuoteItem() - $item->getQty() did not return a numeric value.');

                return null;
            }

            try {
                return $this->getPrice((string)$quoteItem->getSku(), (float)$qty);
            } catch (Exception $e) {
                $this->log('getPriceForQuoteItem()', ['exception' => $e->getMessage()]);
            }
        }

        return null;
    }

    /**
     * Get product price by sku for current customer
     *
     * @param string     $sku
     * @param float|null $qty
     *
     * @return float|null
     */
    public function getCurrentCustomerPrice(string $sku, float $qty = null)
    {
        $this->log('getCurrentCustomerPrice()', ['sku' => $sku, 'qty' => $qty]);

        if ($customerId = $this->customerHelper->getSessionCustomer()->getId()) {
            $this->log('getCurrentCustomerPrice()', ['customerId' => $customerId]);

            /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
            if ($customer = $this->customerHelper->getCustomerById((int)$customerId)) {
                $customerNumber = $this->customerHelper->getCustomerNumber($customer);
                $this->log('getCurrentCustomerPrice()', ['customerNumber' => $customerNumber]);

                if (!empty($customerNumber)) {
                    try {
                        return $this->getPrice($sku);
                    } catch (Exception $e) {
                        $this->log('getCurrentCustomerPrice()', ['exception' => $e->getMessage()]);
                    }
                } else {
                    try {
                        return $this->getGuestPrice($sku);
                    } catch (Exception $e) {
                        $this->log('getCurrentCustomerPrice()', ['exception' => $e->getMessage()]);
                    }
                }
            }
        } else {
            $this->log('Current customer is not logged in.');

            try {
                return $this->getGuestPrice($sku);
            } catch (Exception $e) {
                $this->log('getCurrentCustomerPrice()', ['exception' => $e->getMessage()]);
            }
        }

        return null;
    }

    /**
     * Get product price for guest
     *
     * @param string $sku
     *
     * @return float|null
     */
    public function getGuestPrice(string $sku)
    {
        $this->log('getGuestPrice()', ['sku' => $sku]);

        $currencyCode = $this->helper->getCurrentStoreCurrencyCode();
        $this->log('getGuestPrice()', ['currencyCode' => $currencyCode]);
        if ($currencyCode === null) {
            $this->log('getGuestPrice() - Could not lookup the current store currency code');

            return null;
        }

        $customerGroup = $this->customerHelper->getDefaultGroupCodeByCurrency();
        $this->log('getGuestPrice()', ['customerGroupCode' => $customerGroup]);
        if (empty($customerGroup)) {
            $this->log('getGuestPrice() - Could not find a valid CustomerGroup for the current currency, exiting.');

            return null;
        }

        $uom = $this->uomHelper->getUomText($sku, $customerGroup);
        $this->log('getGuestPrice()', ['uom' => $uom]);

        if ($itemPricingRecord = $this->getActiveItemPricingRecord($currencyCode, $sku, $customerGroup)) {
            if ($itemPricingDetailsRecord = $itemPricingRecord->getDetails($uom)) {
                return $itemPricingDetailsRecord->getUnitPrice();
            } else {
                $this->log("getGuestPrice() - Could not find item detail pricing record WHERE currency_code = '$currencyCode' AND sku = '$sku' AND customer_group = '$customerGroup'.");
            }
        } else {
            $this->log("getGuestPrice() - Could not find item pricing record WHERE currency_code = '$currencyCode' AND sku = '$sku' AND customer_group = '$customerGroup'.");
        }

        return null;
    }

    /**
     * Get product price for Customer
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param string                                       $sku
     * @param float|null                                   $qtyOverride
     *
     * @return float|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomPrice(
        CustomerInterface $customer,
        string $sku,
        float $qtyOverride = null
    ) {
        $this->log('getCustomPrice() - ---------------------------------------');

        $this->log('getCustomPrice()', [
            'customerId'  => $customer->getId(),
            'sku'         => $sku,
            'qtyOverride' => $qtyOverride
        ]);

        // PRICING PRIORITIES
        // 1. Contract pricing (ICCUPR).
        // 2. CustomerType logic.
        // 3. TierPrice logic.
        // 4. Pricelist/SKU/UOM logic on shipping address.
        // 5. Pricelist logic on customer group.
        // 6. Base price

        $customerNumber = $this->customerHelper->getCustomerNumber($customer);
        $this->log('getCustomPrice()', ['customerNumber' => $customerNumber]);

        $currencyCode = $this->helper->getCurrentStoreCurrencyCode();
        $this->log('getCustomPrice()', ['currencyCode' => $currencyCode]);

        $customerGroup = $this->customerHelper->getCustomerGroupCode($customer);
        $this->log('getCustomPrice()', ['customerGroupCode' => $customerGroup]);
        if ($customerGroup === null) {
            $this->log('getCustomPrice() - Unable to determine customer group.');
            return null;
        }

        $uom = $this->uomHelper->getUomText($sku, $customerGroup);
        $this->log('getCustomPrice()', ['uom' => $uom]);

        // CONTRACT PRICING - Look for ICCUPR record, so we can find how to calculate price for customer.
        $contractPrice = !empty($customerNumber)
            ? $this->getContractPrice($customerNumber, $sku, $customerGroup, $currencyCode)
            : null;

        // CUSTOMER TYPE PRICING - Check ICPRIC record for PRICEBASE == 1
        $customerTypePrice = $this->getCustomerTypePrice($customer, $currencyCode, $sku, $customerGroup, $uom);

        // TIER PRICE - Check ICPRIC record for PRICEBASE == 2
        $volumeDiscountPrice = $this->getVolumeDiscountPrice($currencyCode, $sku, $customerGroup);

        // PRICE LIST VALUE BASED ON SHIP-TO
        $shippingAddressPrice = $this->getShippingAddressBasedPrice($customer, $currencyCode, $sku, $uom);

        // PRICE LIST VALUE BASED ON CUSTOMER GROUP
        $customerGroupPrice = $this->getCustomerGroupPrice($currencyCode, $sku, $customerGroup, $uom);

        $this->log('getCustomPrice()', [
            'contractPrice'        => $contractPrice,
            'customerTypePrice'    => $customerTypePrice,
            'volumeDiscountPrice'  => $volumeDiscountPrice,
            'shippingAddressPrice' => $shippingAddressPrice,
            'customerGroupPrice'   => $customerGroupPrice
        ]);

        if ($this->config->shouldUseLowestPrice()) {
            $prices = [
                $contractPrice,
                $customerTypePrice,
                $volumeDiscountPrice,
                $shippingAddressPrice,
                $customerGroupPrice
            ];

            $this->log('getCustomPrice() - Returning lowest price.');
            $this->log('getCustomPrice() - ---------------------------------------' . PHP_EOL);

            return $this->getLowestPrice($prices);
        } else {
            if ($contractPrice !== null) {
                $this->log('getCustomPrice() - Returning contract price.');
                $this->log('getCustomPrice() - ---------------------------------------' . PHP_EOL);

                return $contractPrice;
            } elseif ($customerTypePrice !== null) {
                $this->log('getCustomPrice() - Returning customer-type price.');
                $this->log('getCustomPrice() - ---------------------------------------' . PHP_EOL);

                return $customerTypePrice;
            } elseif ($volumeDiscountPrice !== null) {
                $this->log('getCustomPrice() - Returning volume-discount price.');
                $this->log('getCustomPrice() - ---------------------------------------' . PHP_EOL);

                return $volumeDiscountPrice;
            } elseif ($customerGroupPrice !== null) {
                $this->log('getCustomPrice() - Returning customer group price.');
                $this->log('getCustomPrice() - ---------------------------------------' . PHP_EOL);

                return $customerGroupPrice;
            }
        }

        $this->log('getCustomPrice() - Returning null.');
        $this->log('getCustomPrice() - ---------------------------------------' . PHP_EOL);

        // 6 -- DEFAULT TO MAGENTO PRICE
        return null;
    }

    /**
     * Get Unit Price
     *
     * @param string      $currencyCode
     * @param string      $itemNumber
     * @param string      $pricelist
     * @param string|null $uom
     *
     * @return float|null
     */
    public function getUnitPrice(
        string $currencyCode,
        string $itemNumber,
        string $pricelist,
        string $uom = null
    ) {
        $this->log('getUnitPrice()', [
            'currencyCode' => $currencyCode,
            'itemNumber'   => $itemNumber,
            'pricelist'    => $pricelist,
            'uom'          => $uom
        ]);

        if ($itemPricing = $this->getActiveItemPricingRecord($currencyCode, $itemNumber, $pricelist)) {
            return $itemPricing->getUnitPrice($uom);
        }

        return null;
    }

    /**
     * Calculate contract price
     *
     * @param string $customerNumber
     * @param string $sku
     * @param string $customerGroup
     * @param string $currencyCode
     *
     * @return float|int|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getContractPrice(
        string $customerNumber,
        string $sku,
        string $customerGroup,
        string $currencyCode
    ) {
        $this->log('getContractPrice()', [
            'customerNumber' => $customerNumber,
            'sku'            => $sku,
            'customerGroup'  => $customerGroup,
            'currencyCode'   => $currencyCode
        ]);

        // Assume 0 if missing ICPRIC record.
        $markupCost = $this->getMarkupCost($currencyCode, $sku, $customerGroup);
        $this->log('getContractPrice()', ['markupCost' => $markupCost]);

        /** @var \ECInternet\Sage300Pricing\Api\Data\IcpricInterface $itemPricingRecord */
        $itemPricingRecord = $this->getActiveItemPricingRecord($currencyCode, $sku, $customerGroup);

        /** @var \ECInternet\Sage300Pricing\Api\Data\Iccuprinterface $activeContractPricingRecord */
        if ($activeContractPricingRecord = $this->getActiveContractPricingRecord($customerNumber, $sku, $customerGroup)) {
            // Cache our price type
            $contractPriceType = $activeContractPricingRecord->getPriceType();
            $this->log("getContractPrice() - ICCUPR.PRICETYPE = [$contractPriceType]");

            switch ($contractPriceType) {
                case Iccupr::PRICE_TYPE_CUSTOMER_TYPE:
                    if ($itemPricingRecord) {
                        $contractPricingCustomerType = $activeContractPricingRecord->getCustomerType();
                        $this->log("getContractPrice() - ICCUPR.CUSTTYPE = [$contractPricingCustomerType]");

                        if ($contractPricingCustomerType !== null) {
                            if (is_numeric($contractPricingCustomerType)) {
                                $customerTypePrice = $itemPricingRecord->getCustomerTypePricing((int)$contractPricingCustomerType);
                                if ($customerTypePrice !== null) {
                                    $this->log('getContractPrice() - Returning customer-type contract price.');
                                    $this->log('getContractPrice() - ---------------------------------------');

                                    return $customerTypePrice;
                                } else {
                                    $this->log('getContractPrice() - Unable to calculate customer-type price.');
                                }
                            } else {
                                $this->log('getContractPrice() - Unable to calculate price due to non-numeric [ICCUPR].[CUSTTYPE] value.');
                            }
                        } else {
                            $this->log('getContractPrice() - Unable to calculate price due to missing [ICCUPR].[CUSTTYPE] value.');
                        }
                    } else {
                        $this->log('getContractPrice() - Unable to calculate price due to missing ICPRIC record.');
                    }

                    break;

                case Iccupr::PRICE_TYPE_DISCOUNT_PERCENTAGE:
                    if ($itemPricingRecord) {
                        if ($itemPricingDetailsRecord = $itemPricingRecord->getDetails()) {
                            $this->log('getContractPrice() - Returning percentage discount contract price.');
                            $this->log('getContractPrice() - ---------------------------------------');

                            return $itemPricingDetailsRecord->getUnitPrice() * (1 - ($activeContractPricingRecord->getDiscountPercentage() / 100));
                        } else {
                            $this->log('getContractPrice() - Unable to calculate price due to missing ICPRICP record.');
                        }
                    } else {
                        $this->log('getContractPrice() - Unable to calculate price due to missing ICPRIC record.');
                    }

                    break;

                case Iccupr::PRICE_TYPE_DISCOUNT_AMOUNT:
                    if ($itemPricingRecord) {
                        if ($itemPricingDetailsRecord = $itemPricingRecord->getDetails()) {
                            $this->log('getContractPrice() - Returning amount discount contract price.');
                            $this->log('getContractPrice() - ---------------------------------------');

                            return $itemPricingDetailsRecord->getUnitPrice() - $activeContractPricingRecord->getDiscountAmount();
                        } else {
                            $this->log('getContractPrice() - Unable to calculate price due to missing ICPRICP record.');
                        }
                    } else {
                        $this->log('getContractPrice() - Unable to calculate price due to missing ICPRIC record.');
                    }

                    break;

                case Iccupr::PRICE_TYPE_COST_PLUS_A_PERCENTAGE:
                    $this->log('getContractPrice() - Returning percentage plus contract price.');
                    $this->log('getContractPrice() - ---------------------------------------');

                    return $markupCost + (1 + $activeContractPricingRecord->getPlusPercentage());

                case Iccupr::PRICE_TYPE_COST_PLUS_FIXED_AMOUNT:
                    $this->log('getContractPrice() - Returning amount plus contract price.');
                    $this->log('getContractPrice() - ---------------------------------------');

                    return $markupCost + $activeContractPricingRecord->getPlusAmount();

                case Iccupr::PRICE_TYPE_FIXED_PRICE:
                    $this->log('getCustomPrice() - Returning fixed-price contract price.');
                    $this->log('getCustomPrice() - ---------------------------------------');

                    return $activeContractPricingRecord->getFixedPrice();

                default:
                    throw new LocalizedException(
                        __("Invalid 'price type' value found: [$contractPriceType].")
                    );
            }
        } else {
            $this->log('getContractPrice() - Unable to calculate contract price due to missing ICCUPR record.');
        }

        return null;
    }

    /**
     * Calculate customer-type price
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param string                                       $currencyCode
     * @param string                                       $sku
     * @param string                                       $customerGroup
     * @param string                                       $uom
     *
     * @return float|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getCustomerTypePrice(
        CustomerInterface $customer,
        string $currencyCode,
        string $sku,
        string $customerGroup,
        string $uom
    ) {
        $this->log('getCustomerTypePrice()', [
            'currencyCode'  => $currencyCode,
            'sku'           => $sku,
            'customerGroup' => $customerGroup,
            'uom'           => $uom
        ]);

        // We need active ICPRIC record to look for customer's 'customer_type' index.
        if ($activeItemPricingRecord = $this->getActiveItemPricingRecord($currencyCode, $sku, $customerGroup)) {
            $customerType = $this->customerHelper->getCustomerType($customer);
            $this->log('getCustomerTypePrice()', ['customerType' => $customerType]);

            if (is_numeric($customerType)) {
                if ((int)$customerType > 0) {
                    $this->log('getCustomerTypePrice() - Returning customer-type price.');
                    $this->log('getCustomerTypePrice() - ---------------------------------------');

                    return $activeItemPricingRecord->getCustomerTypePricing((int)$customerType);
                }
            } else {
                $this->log('getCustomerTypePrice() - Unable to calculate price due to non-numeric customer_type value.');
            }
        } else {
            $this->log('getCustomerTypePrice() - Unable to calculate customer-type price due to missing ICPRIC record.');
        }

        return null;
    }

    /**
     * Calculate tier price
     *
     * @param string      $currencyCode
     * @param string      $sku
     * @param string      $customerGroup
     * @param string|null $uom
     * @param float|null  $qtyOverride
     *
     * @return float|int|null
     */
    private function getVolumeDiscountPrice(
        string $currencyCode,
        string $sku,
        string $customerGroup,
        string $uom = null,
        float $qtyOverride = null,
    ) {
        $this->log('getVolumeDiscountPrice()', [
            'currencyCode'  => $currencyCode,
            'sku'           => $sku,
            'customerGroup' => $customerGroup,
            'uom'           => $uom,
            'qtyOverride'   => $qtyOverride
        ]);

        /** @var \ECInternet\Sage300Pricing\Api\Data\IcpricInterface $activeItemPricingRecord */
        if ($activeItemPricingRecord = $this->getActiveItemPricingRecord($currencyCode, $sku, $customerGroup)) {
            if ($activeItemPricingRecord->isPriceDeterminedByVolumeDiscounts()) {
                $this->log('getVolumeDiscountPrice() - Price determined by volume discounts.  [ICPRIC].[PRICEBASE] = 2');

                // Used passed-in value or pull from current quote (if it exists)
                $qty = ($qtyOverride !== null) ? $qtyOverride : $this->quoteHelper->getCurrentItemQuantity($sku);
                $this->log('getVolumeDiscountPrice()', ['qty' => $qty]);

                $volumeDiscountPrice = $activeItemPricingRecord->getVolumeDiscountPrice($qty, $uom);
                if ($volumeDiscountPrice !== null) {
                    return $volumeDiscountPrice;
                } else {
                    $this->log('getVolumeDiscountPrice() - Unable to calculate volume discount price due to non-matching quantity breaks.');
                }
            } else {
                $this->log('getVolumeDiscountPrice() - Price NOT determined by volume discounts.');
            }
        } else {
            $this->log('getVolumeDiscountPrice() - Unable to calculate volume discount price due to missing ICPRIC record.');
        }

        return null;
    }

    /**
     * Calculate shipping address based price
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param string                                       $currencyCode
     * @param string                                       $sku
     * @param string|null                                  $uom
     *
     * @return float|null
     */
    private function getShippingAddressBasedPrice(
        CustomerInterface $customer,
        string $currencyCode,
        string $sku,
        string $uom = null
    ) {
        $this->log('getShippingAddressBasedPrice()', [
            'currencyCode' => $currencyCode,
            'sku'          => $sku,
            'uom'          => $uom
        ]);

        if ($customerDefaultShippingAddress = $this->customerHelper->getCustomerDefaultShippingAddress($customer)) {
            if ($defaultShippingAddressPricelist = $customerDefaultShippingAddress->getCustomAttribute('customer_pricelist')) {
                if ($defaultShippingAddressPricelistValue = $defaultShippingAddressPricelist->getValue()) {
                    /** @var \ECInternet\Sage300Pricing\Api\Data\IcpricInterface $pricingRecord */
                    if ($pricingRecord = $this->getActiveItemPricingRecord($currencyCode, $sku, (string)$defaultShippingAddressPricelistValue)) {
                        /** @var \ECInternet\Sage300Pricing\Model\Data\Icpricp $pricingDetailRecord */
                        if ($pricingDetailRecord = $pricingRecord->getDetails($uom)) {
                            $this->log('getShippingAddressBasedPrice() - Returning ICPRICP.UNITPRICE');
                            $this->log('getShippingAddressBasedPrice() - ---------------------------------------');

                            return $pricingDetailRecord->getUnitPrice();
                        } else {
                            $this->log('getShippingAddressBasedPrice() - Could not find pricing detail record (ICPRICP)');
                        }
                    } else {
                        $this->log('getShippingAddressBasedPrice() - Could not find pricing record (ICPRIC)');
                    }
                } else {
                    $this->log("getShippingAddressBasedPrice() - 'customer_pricelist' attribute does not have a value.");
                }
            } else {
                $this->log("getShippingAddressBasedPrice() - 'customer_pricelist' attribute not found on default shipping address.");
            }
        } else {
            $this->log('getShippingAddressBasedPrice() - Default shipping address not found for customer.');
        }

        return null;
    }

    private function getCustomerGroupPrice(
        string $currencyCode,
        string $sku,
        string $customerGroup,
        string $uom
    ) {
        $this->log('getCustomerGroupPrice()', [
            'currencyCode'  => $currencyCode,
            'sku'           => $sku,
            'customerGroup' => $customerGroup,
            'uom'           => $uom
        ]);

        if ($itemPricingRecord = $this->getActiveItemPricingRecord($currencyCode, $sku, $customerGroup)) {
            if ($itemPricingDetailsRecord = $itemPricingRecord->getDetails($uom)) {
                return $itemPricingDetailsRecord->getUnitPrice();
            } else {
                $this->log('getCustomPrice() - Unable to calculate customer group price due to missing ICPRICP record.');
            }
        } else {
            $this->log('getCustomPrice() - Unable to calculate customer group price due to missing ICPRIC record.');
        }

        return null;
    }

    /**
     * Get the ContractPricing (ICCUPR) record if it exists and is active
     *
     * @param string $customerNumber
     * @param string $itemNumber
     * @param string $pricelist
     *
     * @return \ECInternet\Sage300Pricing\Api\Data\IccuprInterface|null
     */
    private function getActiveContractPricingRecord(string $customerNumber, string $itemNumber, string $pricelist)
    {
        $this->log('getActiveContractPricingRecord()', [
            'customerNumber' => $customerNumber,
            'itemNumber'     => $itemNumber,
            'pricelist'      => $pricelist
        ]);

        // TODO: Add mechanism to check if we've already fetched this record.

        $iccupr = $this->iccuprRepository->get($customerNumber, $itemNumber, $pricelist);
        if ($iccupr !== null) {
            if ($iccupr->getIsActive()) {
                if ($iccupr->isValidToday()) {
                    return $iccupr;
                } else {
                    $this->log('getActiveContractPricingRecord() - Contract pricing record is not valid today.');
                }
            } else {
                $this->log('getActiveContractPricingRecord() - Contract pricing record found, but inactive.');
            }
        } else {
            $this->log('getActiveContractPricingRecord() - Could not find contract pricing record.');
        }

        return null;
    }

    /**
     * Get the ItemPricing (ICPRIC) record if it exists and is active
     *
     * @param string $currencyCode
     * @param string $itemNumber
     * @param string $pricelist
     *
     * @return \ECInternet\Sage300Pricing\Api\Data\IcpricInterface|null
     */
    private function getActiveItemPricingRecord(string $currencyCode, string $itemNumber, string $pricelist)
    {
        //$this->log('getActiveItemPricingRecord()', [
        //    'currencyCode' => $currencyCode,
        //    'itemNumber'   => $itemNumber,
        //    'pricelist'    => $pricelist
        //]);

        // TODO: Add mechanism to check if we've already fetched this record.

        if ($icpric = $this->icpricRepository->get($currencyCode, $itemNumber, $pricelist)) {
            if ($icpric->getIsActive()) {
                return $icpric;
            } else {
                $this->log('getActiveItemPricingRecord() - ICPRIC record found, but inactive');
            }
        } else {
            $this->log('getActiveItemPricingRecord() - ICPRIC record not found');
        }

        return null;
    }

    /**
     * @param string $currencyCode
     * @param string $sku
     * @param string $customerGroup
     *
     * @return float
     */
    private function getMarkupCost(string $currencyCode, string $sku, string $customerGroup)
    {
        if ($itemPricingRecord = $this->getActiveItemPricingRecord($currencyCode, $sku, $customerGroup)) {
            return $itemPricingRecord->getMarkupCost();
        }

        return 0.0;
    }

    /**
     * @param float[] $prices
     *
     * @return float|null
     */
    private function getLowestPrice(array $prices)
    {
        $lowestPrice = null;

        foreach ($prices as $price) {
            if ($price !== null) {
                if ($lowestPrice === null || $price < $lowestPrice) {
                    $lowestPrice = $price;
                }
            }
        }

        return $lowestPrice;
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
        $this->logger->info('Pricing/Sage300 - ' . $message, $extra);
    }
}
