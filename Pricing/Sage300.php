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
use ECInternet\Sage300Pricing\Helper\Quote as QuoteHelper;
use ECInternet\Sage300Pricing\Logger\Logger;
use ECInternet\Sage300Pricing\Model\Data\Iccupr;
use Exception;

class Sage300 implements PricingSystemInterface
{
    const PRICING_SYSTEM_NAME                = 'sage300';

    const CUSTOMER_ATTRIBUTE_CURRENCY_CODE   = 'currency_code';

    const CUSTOMER_ATTRIBUTE_CUSTOMER_TYPE   = 'customer_type';

    const CUSTOMER_ATTRIBUTE_CUSTOMER_NUMBER = 'customer_number';

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
     * @var \ECInternet\Sage300Pricing\Helper\Quote
     */
    private $quoteHelper;

    /**
     * @var \ECInternet\Sage300Pricing\Logger\Logger
     */
    private $logger;

    public function __construct(
        UomHelper $uomHelper,
        IccuprRepositoryInterface $iccuprRepository,
        IcpricRepositoryInterface $icpricRepository,
        CustomerHelper $customerHelper,
        QuoteHelper $quoteHelper,
        Logger $logger
    ) {
        $this->uomHelper        = $uomHelper;
        $this->iccuprRepository = $iccuprRepository;
        $this->icpricRepository = $icpricRepository;
        $this->customerHelper   = $customerHelper;
        $this->quoteHelper      = $quoteHelper;
        $this->logger           = $logger;
    }

    public function getName()
    {
        return self::PRICING_SYSTEM_NAME;
    }

    public function getPrice(string $sku, float $quantity = 1.0)
    {
        $this->log('getPrice()', ['sku' => $sku, 'quantity' => $quantity]);

        if ($customer = $this->customerHelper->getSessionCustomerInterface()) {
            $this->log('getPrice() - SessionCustomerInterface found.');

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
            $this->log('getPriceForQuoteItem()', ['quoteId' => $quote->getId()]);

            /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
            $customer = $quote->getCustomer();
            $this->log('getPriceForQuoteItem()', ['customer' => $customer->getId()]);

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

        $currencyCode = $this->customerHelper->getCurrentStoreCurrencyCode();
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

        $currencyCode = $this->customerHelper->getCurrentStoreCurrencyCode();
        $this->log('getCustomPrice()', ['currencyCode' => $currencyCode]);

        $customerGroup = $this->customerHelper->getCustomerGroupCode($customer);
        $this->log('getCustomPrice()', ['customerGroupCode' => $customerGroup]);
        if ($customerGroup === null) {
            $this->log('getCustomPrice() - Unable to determine customer group.');
            return null;
        }

        $uom = $this->uomHelper->getUomText($sku, $customerGroup);
        $this->log('getCustomPrice()', ['uom' => $uom]);

        /** @var \ECInternet\Sage300Pricing\Api\Data\IcpricInterface $itemPricingRecord */
        $itemPricingRecord = $this->getActiveItemPricingRecord($currencyCode, $sku, $customerGroup);
        if (!$itemPricingRecord) {
            $this->log("getCustomPrice() - Could not find ICPRIC record WHERE currency_code = '$currencyCode' AND sku = '$sku' AND customer_group = '$customerGroup'.");
        }

        // Assume 0 if missing ICPRIC record.
        $markupCost = $itemPricingRecord ? $itemPricingRecord->getMarkupCost() : 0;

        // 1 -- CONTRACT PRICING
        //   -- Look for ICCUPR record, so we can find how to calculate price for customer.
        if (!empty($customerNumber)) {
            $this->log('getCustomPrice() - --CONTRACT PRICING CHECK--');

            $contractPricingRecord = $this->getActiveContractPricingRecord((string)$customerNumber, $sku, $customerGroup);
            if ($contractPricingRecord !== null) {
                $contractPriceType = $contractPricingRecord->getPriceType();
                switch ($contractPriceType) {
                    case Iccupr::PRICE_TYPE_CUSTOMER_TYPE:
                        $contractPricingCustomerType = $contractPricingRecord->getCustomerType();
                        $this->log("getCustomPrice() - ICCUPR.CUSTTYPE = [$contractPricingCustomerType]");

                        $customerType = $this->customerHelper->getCustomerType($customer);
                        $this->log("getCustomPrice() - Customer has 'customer_type' = [$customerType].");
                        if ($customerType !== null) {
                            if ($customerType != '') {
                                if ($customerType > 0) {
                                    return $itemPricingRecord->getCustomerTypePricing($customerType);
                                } else {
                                    if ($itemPricingDetailsRecord = $itemPricingRecord->getDetails()) {
                                        return $itemPricingDetailsRecord->getUnitPrice();
                                    } else {
                                        throw new LocalizedException(
                                            __('Unable to calculate price due to missing ICPRICP record.')
                                        );
                                    }
                                }
                            }
                        }
                        break;

                    case Iccupr::PRICE_TYPE_DISCOUNT_PERCENTAGE:
                        if ($itemPricingRecord && $itemPricingDetailsRecord = $itemPricingRecord->getDetails()) {
                            return $itemPricingDetailsRecord->getUnitPrice() * (1 - ($contractPricingRecord->getDiscountPercentage() / 100));
                        } else {
                            $this->log('getCustomPrice() - Unable to calculate price due to missing ICPRICP record.');

                            return null;
                        }

                    case Iccupr::PRICE_TYPE_DISCOUNT_AMOUNT:
                        if ($itemPricingRecord && $itemPricingDetailsRecord = $itemPricingRecord->getDetails()) {
                            return $itemPricingDetailsRecord->getUnitPrice() - $contractPricingRecord->getDiscountAmount();
                        } else {
                            $this->log('getCustomPrice() - Unable to calculate price due to missing ICPRICP record.');

                            return null;
                        }

                    case Iccupr::PRICE_TYPE_COST_PLUS_A_PERCENTAGE:
                        return $markupCost + (1 + $contractPricingRecord->getPlusPercentage());

                    case Iccupr::PRICE_TYPE_COST_PLUS_FIXED_AMOUNT:
                        return $markupCost + $contractPricingRecord->getPlusAmount();

                    case Iccupr::PRICE_TYPE_FIXED_PRICE:
                        return $contractPricingRecord->getFixedPrice();

                    default:
                        throw new LocalizedException(
                            __("Invalid 'price type' value found: [$contractPriceType].")
                        );
                }
            } else {
                $this->log('getCustomPrice() - Contract price record NOT found.');
            }
        } else {
            $this->log("getCustomPrice() - Could not find '" . self::CUSTOMER_ATTRIBUTE_CUSTOMER_NUMBER . "' attribute value for Customer, contract pricing check skipped.");
        }

        // 2 -- CUSTOMER TYPE PRICING
        //   -- Check ICPRIC record for PRICEBASE == 1
        $this->log('getCustomPrice() - --CUSTOMER TYPE PRICING CHECK--');
        if ($itemPricingRecord && $itemPricingRecord->isPriceDeterminedByCustomerType()) {
            $this->log('getCustomPrice() - Price determined by CustomerType.  [ICPRIC].[PRICEBASE] = 1');

            $customerType = $this->customerHelper->getCustomerType($customer);
            $this->log("getCustomPrice() - Customer has 'customer_type' = [$customerType].");
            if ($customerType !== null) {
                if ($customerType != '') {
                    if ($customerType > 0) {
                        return $itemPricingRecord->getCustomerTypePricing($customerType);
                    } else {
                        if ($itemPricingDetailsRecord = $itemPricingRecord->getDetails()) {
                            return $itemPricingDetailsRecord->getUnitPrice();
                        } else {
                            throw new LocalizedException(
                                __('Unable to calculate price due to missing ICPRICP record.')
                            );
                        }
                    }
                } else {
                    $this->log("getCustomPrice() - Customer has blank 'customer_type' attribute.");
                }
            } else {
                $this->log("getCustomPrice() - Customer does not have 'customer_type' attribute set.");
            }
        } else {
            $this->log('getCustomPrice() - Price NOT determined by CustomerType.');
        }

        // 3 -- TIER PRICE LOGIC
        //   -- Check ICPRIC record for PRICEBASE == 2
        $this->log('getCustomPrice() - --TIER PRICING CHECK--');
        if ($itemPricingRecord && $itemPricingRecord->isPriceDeterminedByVolumeDiscounts()) {
            $this->log('getCustomPrice() - Price determined by VolumeDiscounts.');

            /** @var float $qty */
            $qty = ($qtyOverride !== null)
                ? $qtyOverride
                : $this->quoteHelper->getCurrentItemQuantity($sku);

            $this->log('getCustomPrice()', ['qty' => $qty, 'uom' => $uom]);

            $volumeDiscountPrice = $itemPricingRecord->getVolumeDiscountPrice($qty, $uom);
            if ($volumeDiscountPrice !== null) {
                return $volumeDiscountPrice;
            } else {
                $this->log('getCustomPrice() - Item quantity does not match Quantity Breaks.');
            }
        } else {
            $this->log('getCustomPrice() - Price NOT determined by VolumeDiscounts.');
        }

        // 4 -- PRICE LIST VALUE BASED ON SHIP-TO
        $this->log('getCustomPrice() - --PRICE LIST PRICING CHECK ON SHIPPING ADDRESS--');
        /** @var \Magento\Customer\Api\Data\AddressInterface $customerDefaultShippingAddress */
        $customerDefaultShippingAddress = $this->customerHelper->getCustomerDefaultShippingAddress($customer);
        if ($customerDefaultShippingAddress) {
            if ($defaultShippingAddressPricelist = $customerDefaultShippingAddress->getCustomAttribute('customer_pricelist')) {
                if ($defaultShippingAddressPricelistValue = $defaultShippingAddressPricelist->getValue()) {
                    /** @var \ECInternet\Sage300Pricing\Api\Data\IcpricInterface $pricingRecord */
                    if ($pricingRecord = $this->getActiveItemPricingRecord((string)$currencyCode, $sku, (string)$defaultShippingAddressPricelistValue)) {
                        /** @var \ECInternet\Sage300Pricing\Model\Data\Icpricp $pricingDetailRecord */
                        if ($pricingDetailRecord = $pricingRecord->getDetails($uom)) {
                            $this->log('getCustomPrice() - Returning ICPRICP.UNITPRICE');

                            return $pricingDetailRecord->getUnitPrice();
                        } else {
                            $this->log('getCustomPrice() - Could not find pricing detail record (ICPRICP)');
                        }
                    } else {
                        $this->log('getCustomPrice() - Could not find pricing record (ICPRIC)');
                    }
                } else {
                    $this->log("getCustomPrice() - 'customer_pricelist' attribute does not have a value.");
                }
            } else {
                $this->log("getCustomPrice() - 'customer_pricelist' attribute not found on default shipping address.");
            }
        } else {
            $this->log('getCustomPrice() - Default shipping address not found for customer.');
        }

        // 5 -- PRICE LIST VALUE BASED ON CUSTOMER GROUP
        $this->log('getCustomPrice() - --PRICE LIST PRICING CHECK ON CUSTOMER RECORD--');
        if ($itemPricingRecord) {
            if ($itemPricingDetailsRecord = $itemPricingRecord->getDetails($uom)) {
                $this->log('getCustomPrice() - Returning ICPRICP.UNITPRICE');

                return $itemPricingDetailsRecord->getUnitPrice();
            } else {
                $this->log('getCustomPrice() - Could not find pricing detail record (ICPRICP)');
            }
        } else {
            $this->log('getCustomPrice() - Price NOT determined by Item Pricing (ICPRIC) record.');
        }

        $this->log('getCustomPrice() - Returning null --> defaulting to Magento price');

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

        $iccupr = $this->iccuprRepository->get($customerNumber, $itemNumber, $pricelist);
        if ($iccupr !== null) {
            if ($iccupr->getIsActive()) {
                if ($iccupr->isValidToday()) {
                    return $iccupr;
                } else {
                    $this->log('getActiveContractPricingRecord() - Contract pricing record is not valid today.');
                }
            } else {
                $this->log('getActiveContractPricingRecord() - Contract pricing record is not active.');
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
     * @return \ECInternet\Sage300Pricing\Model\Data\Icpric|null
     */
    private function getActiveItemPricingRecord(string $currencyCode, string $itemNumber, string $pricelist)
    {
        $this->log('getActiveItemPricingRecord()', [
            'currencyCode' => $currencyCode,
            'itemNumber'   => $itemNumber,
            'pricelist'    => $pricelist
        ]);

        if ($icpric = $this->icpricRepository->get($currencyCode, $itemNumber, $pricelist)) {
            if ($icpric->getIsActive()) {
                return $icpric;
            } else {
                $this->log('getActiveItemPricingRecord() - ICPRIC record is not active');
            }
        } else {
            $this->log('getActiveItemPricingRecord() - ICPRIC record not found');
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
        $this->logger->info('Pricing/Sage300 - ' . $message, $extra);
    }
}
