<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Model\Data;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime;
use ECInternet\Sage300Pricing\Api\Data\IcpricInterface;
use ECInternet\Sage300Pricing\Logger\Logger;
use ECInternet\Sage300Pricing\Model\ResourceModel\Icpricp\CollectionFactory as IcpricpCollection;

/**
 * Icpric data model
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Icpric extends AbstractModel implements IdentityInterface, IcpricInterface
{
    const CACHE_TAG = 'ecinternet_sage300pricing_icpric';

    protected $_cacheTag    = 'ecinternet_sage300pricing_icpric';

    protected $_eventPrefix = 'ecinternet_sage300pricing_icpric';

    protected $_eventObject = 'icpric';

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    private $dateTime;

    /**
     * @var \ECInternet\Sage300Pricing\Logger\Logger
     */
    private $logger;

    /**
     * @var \ECInternet\Sage300Pricing\Model\ResourceModel\Icpricp\Collection
     */
    private $icpricpCollection;

    /**
     * Icpric constructor.
     *
     * @param \Magento\Framework\Model\Context                                         $context
     * @param \Magento\Framework\Registry                                              $registry
     * @param \Magento\Framework\Stdlib\DateTime                                       $dateTime
     * @param \ECInternet\Sage300Pricing\Logger\Logger                                 $logger
     * @param \ECInternet\Sage300Pricing\Model\ResourceModel\Icpricp\CollectionFactory $icpricpCollection
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null             $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null                       $resourceCollection
     * @param array                                                                    $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        DateTime $dateTime,
        Logger $logger,
        IcpricpCollection $icpricpCollection,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {

        $this->dateTime          = $dateTime;
        $this->logger            = $logger;
        $this->icpricpCollection = $icpricpCollection;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init('ECInternet\Sage300Pricing\Model\ResourceModel\Icpric');
    }

    public function beforeSave()
    {
        // Always update (we can use this to verify syncs are running)
        $this->setUpdatedAt($this->dateTime->formatDate(true));

        return parent::beforeSave();
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getId()
    {
        return $this->getData(self::COLUMN_ID);
    }

    public function getUpdatedAt()
    {
        return (string)$this->getData(self::COLUMN_UPDATED_AT);
    }

    public function setUpdatedAt(string $updatedAt)
    {
        $this->setData(self::COLUMN_UPDATED_AT, $updatedAt);
    }

    public function getIsActive()
    {
        return (bool)$this->getData(self::COLUMN_IS_ACTIVE);
    }

    public function setIsActive(bool $isActive)
    {
        $this->setData(self::COLUMN_IS_ACTIVE, $isActive);
    }

    public function getCurrencyCode()
    {
        return (string)$this->getData(self::COLUMN_CURRENCY);
    }

    public function setCurrencyCode(string $currencyCode)
    {
        $this->setData(self::COLUMN_CURRENCY, $currencyCode);
    }

    public function getItemNumber()
    {
        return (string)$this->getData(self::COLUMN_ITEMNO);
    }

    public function setItemNumber(string $itemNumber)
    {
        $this->setData(self::COLUMN_ITEMNO, $itemNumber);
    }

    public function getPriceListCode()
    {
        return (string)$this->getData(self::COLUMN_PRICELIST);
    }

    public function setPriceListCode(string $priceListCode)
    {
        $this->setData(self::COLUMN_PRICELIST, $priceListCode);
    }

    public function getMarkupCost()
    {
        return (float)$this->getData(self::COLUMN_MARKUPCOST);
    }

    public function setMarkupCost(float $markupCost)
    {
        $this->setData(self::COLUMN_MARKUPCOST, $markupCost);
    }

    public function getDiscountMarkupPriceBy()
    {
        return (int)$this->getData(self::COLUMN_PRICEFMT);
    }

    public function setDiscountMarkupPriceBy(int $markupPriceBy)
    {
        $this->setData(self::COLUMN_PRICEFMT, $markupPriceBy);
    }

    public function getDiscountMarkupPercentage1()
    {
        return (float)$this->getData(self::COLUMN_PRCNTLVL1);
    }

    public function setDiscountMarkupPercentage1(float $discountMarkupPercentage1)
    {
        $this->setData(self::COLUMN_PRCNTLVL1, $discountMarkupPercentage1);
    }

    public function getDiscountMarkupPercentage2()
    {
        return (float)$this->getData(self::COLUMN_PRCNTLVL2);
    }

    public function setDiscountMarkupPercentage2(float $discountMarkupPercentage2)
    {
        $this->setData(self::COLUMN_PRCNTLVL2, $discountMarkupPercentage2);
    }

    public function getDiscountMarkupPercentage3()
    {
        return (float)$this->getData(self::COLUMN_PRCNTLVL3);
    }

    public function setDiscountMarkupPercentage3(float $discountMarkupPercentage3)
    {
        $this->setData(self::COLUMN_PRCNTLVL3, $discountMarkupPercentage3);
    }

    public function getDiscountMarkupPercentage4()
    {
        return (float)$this->getData(self::COLUMN_PRCNTLVL4);
    }

    public function setDiscountMarkupPercentage4(float $discountMarkupPercentage4)
    {
        $this->setData(self::COLUMN_PRCNTLVL4, $discountMarkupPercentage4);
    }

    public function getDiscountMarkupPercentage5()
    {
        return (float)$this->getData(self::COLUMN_PRCNTLVL5);
    }

    public function setDiscountMarkupPercentage5(float $discountMarkupPercentage5)
    {
        $this->setData(self::COLUMN_PRCNTLVL5, $discountMarkupPercentage5);
    }

    public function getPriceDeterminedBy()
    {
        return (int)$this->getData(self::COLUMN_PRICEBASE);
    }

    public function setPriceDeterminedBy(int $priceDeterminedBy)
    {
        $this->setData(self::COLUMN_PRICEBASE, $priceDeterminedBy);
    }

    public function getQuantityLevel1()
    {
        return (float)$this->getData(self::COLUMN_PRICEQTY1);
    }

    public function setQuantityLevel1(float $quantityLevel1)
    {
        $this->setData(self::COLUMN_PRICEQTY1, $quantityLevel1);
    }

    public function getQuantityLevel2()
    {
        return (float)$this->getData(self::COLUMN_PRICEQTY2);
    }

    public function setQuantityLevel2(float $quantityLevel2)
    {
        $this->setData(self::COLUMN_PRICEQTY2, $quantityLevel2);
    }

    public function getQuantityLevel3()
    {
        return (float)$this->getData(self::COLUMN_PRICEQTY3);
    }

    public function setQuantityLevel3(float $quantityLevel3)
    {
        $this->setData(self::COLUMN_PRICEQTY3, $quantityLevel3);
    }

    public function getQuantityLevel4()
    {
        return (float)$this->getData(self::COLUMN_PRICEQTY4);
    }

    public function setQuantityLevel4(float $quantityLevel4)
    {
        $this->setData(self::COLUMN_PRICEQTY4, $quantityLevel4);
    }

    public function getQuantityLevel5()
    {
        return (float)$this->getData(self::COLUMN_PRICEQTY5);
    }

    public function setQuantityLevel5(float $quantityLevel5)
    {
        $this->setData(self::COLUMN_PRICEQTY5, $quantityLevel5);
    }

    public function getDiscountMarkupAmount1()
    {
        return (float)$this->getData(self::COLUMN_AMOUNTLVL1);
    }

    public function setDiscountMarkupAmount1(float $discountMarkupAmount1)
    {
        $this->setData(self::COLUMN_AMOUNTLVL1, $discountMarkupAmount1);
    }

    public function getDiscountMarkupAmount2()
    {
        return (float)$this->getData(self::COLUMN_AMOUNTLVL2);
    }

    public function setDiscountMarkupAmount2(float $discountMarkupAmount2)
    {
        $this->setData(self::COLUMN_AMOUNTLVL2, $discountMarkupAmount2);
    }

    public function getDiscountMarkupAmount3()
    {
        return (float)$this->getData(self::COLUMN_AMOUNTLVL3);
    }

    public function setDiscountMarkupAmount3(float $discountMarkupAmount3)
    {
        $this->setData(self::COLUMN_AMOUNTLVL3, $discountMarkupAmount3);
    }

    public function getDiscountMarkupAmount4()
    {
        return (float)$this->getData(self::COLUMN_AMOUNTLVL4);
    }

    public function setDiscountMarkupAmount4(float $discountMarkupAmount4)
    {
        $this->setData(self::COLUMN_AMOUNTLVL4, $discountMarkupAmount4);
    }

    public function getDiscountMarkupAmount5()
    {
        return (float)$this->getData(self::COLUMN_AMOUNTLVL5);
    }

    public function setDiscountMarkupAmount5(float $discountMarkupAmount5)
    {
        $this->setData(self::COLUMN_AMOUNTLVL5, $discountMarkupAmount5);
    }

    /**
     * Is price determined by customer type?
     *
     * @return bool
     */
    public function isPriceDeterminedByCustomerType()
    {
        return $this->getPriceDeterminedBy() == self::PRICE_DETERMINED_BY_CUSTOMER_TYPE;
    }

    /**
     * Is price determined by volume discounts?
     *
     * @return bool
     */
    public function isPriceDeterminedByVolumeDiscounts()
    {
        return $this->getPriceDeterminedBy() == self::PRICE_DETERMINED_BY_VOLUME_DISCOUNTS;
    }

    /**
     * @param int $index
     *
     * @return float|null
     */
    public function getDiscountPercentage(int $index)
    {
        switch ($index) {
            case 1:
                return $this->getDiscountMarkupPercentage1();
            case 2:
                return $this->getDiscountMarkupPercentage2();
            case 3:
                return $this->getDiscountMarkupPercentage3();
            case 4:
                return $this->getDiscountMarkupPercentage4();
            case 5:
                return $this->getDiscountMarkupPercentage5();
            default:
                $this->notice("getDiscountPercentage() - Unexpected percentage level: [$index].");

        }

        return null;
    }

    public function getDiscountAmount(int $index)
    {
        switch ($index) {
            case 1:
                return $this->getDiscountMarkupAmount1();
            case 2:
                return $this->getDiscountMarkupAmount2();
            case 3:
                return $this->getDiscountMarkupAmount3();
            case 4:
                return $this->getDiscountMarkupAmount4();
            case 5:
                return $this->getDiscountMarkupAmount5();
            default:
                $this->notice("getDiscountAmount() - Unexpected amount level: [$index].");
        }

        return null;
    }

    /**
     * Get customer-type pricing
     *
     * @param int $customerType
     *
     * @return float|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerTypePricing(int $customerType)
    {
        $this->log('getCustomerTypePricing()', ['customerType' => $customerType]);

        if ($customerType > 0) {
            if ($this->isDiscountOrMarkupAppliedByPercentage()) {
                $this->log('getCustomerTypePricing()', ['appliedBy' => 'percentage']);

                $percentage = $this->getDiscountMarkupPercentage($customerType);
                $this->log('getCustomerTypePricing()', ['percentage' => $percentage]);

                $unitPrice = $this->getUnitPrice();
                $this->log('getCustomerTypePricing()', ['unitPrice' => $unitPrice]);

                if ($unitPrice !== null) {
                    return $unitPrice * ((100 - $percentage) / 100);
                } else {
                    $this->notice('getCustomerTypePricing() - Unable to determine unit price.');
                }
            } elseif ($this->isDiscountOrMarkupAppliedByAmount()) {
                $this->log('getCustomerTypePricing() - Discount/Markup is applied by amount.');

                $amount = $this->getDiscountMarkupAmount($customerType);
                $this->log('getCustomerTypePricing()', ['amount' => $amount]);

                $unitPrice = $this->getUnitPrice();
                $this->log('getCustomerTypePricing()', ['unitPrice' => $unitPrice]);

                if ($unitPrice !== null) {
                    return $unitPrice - $amount;
                } else {
                    $this->notice('getCustomerTypePricing() - Unable to determine unit price.');
                }
            } else {
                throw new LocalizedException(__('Could not determine how to apply markup / discount.'));
            }
        }

        return null;
    }

    /**
     * Get volume discount price
     *
     * @param float       $qty
     * @param string|null $uom
     *
     * @return float|null
     */
    public function getVolumeDiscountPrice(float $qty, string $uom = null)
    {
        $this->log('getVolumeDiscountPrice()', ['qty' => $qty, 'uom' => $uom]);

        if ($qty === 0.0) {
            $this->log('getVolumeDiscountPrice() - Qty = 0, using qty = 1');
            $qty = 1;
        }

        $qtyLevel = $this->getQuantityLevel($qty);
        $this->log('getVolumeDiscountPrice()', ['qtyLevel' => $qtyLevel]);

        if ($qtyLevel > 0) {
            $unitPrice = $this->getUnitPrice($uom);
            $this->log('getVolumeDiscountPrice()', ['unitPrice' => $unitPrice]);

            if ($unitPrice !== null) {
                if ($this->isDiscountOrMarkupAppliedByPercentage()) {
                    $this->log('getVolumeDiscountPrice()', ['appliedBy' => 'percentage']);

                    $percentage = $this->getDiscountMarkupPercentage($qtyLevel);
                    $this->log('getVolumeDiscountPrice()', ['percentage' => $percentage]);

                    return $unitPrice * ((100 - $percentage) / 100);
                } elseif ($this->isDiscountOrMarkupAppliedByAmount()) {
                    $this->log('getVolumeDiscountPrice()', ['appliedBy' => 'amount']);

                    $amount = $this->getDiscountMarkupAmount($qtyLevel);
                    $this->log('getVolumeDiscountPrice()', ['amount' => $amount]);

                    return $unitPrice - $amount;
                }
            }
        }

        return null;
    }

    /**
     * Get item pricing details
     *
     * @param string|null $uom
     *
     * @return \ECInternet\Sage300Pricing\Model\Data\Icpricp|null
     */
    public function getDetails(string $uom = null)
    {
        //$this->log('getDetails()', ['uom' => $uom]);

        /** @var \ECInternet\Sage300Pricing\Model\ResourceModel\Icpricp\Collection $icpricpCollection */
        $icpricpCollection = $this->icpricpCollection->create()
            ->addFieldToFilter(Icpricp::COLUMN_CURRENCY, ['eq' => $this->getCurrencyCode()])
            ->addFieldToFilter(Icpricp::COLUMN_ITEMNO, ['eq' => $this->getItemNumber()])
            ->addFieldToFilter(Icpricp::COLUMN_PRICELIST, ['eq' => $this->getPriceListCode()])
            ->addFieldToFilter(Icpricp::COLUMN_IS_ACTIVE, ['eq' => true])
            // TODO: turn this into parameter which defaults to PRICE_DETAIL_TYPE_BASE_PRICE_QUANTITY (1)
            ->addFieldToFilter(Icpricp::COLUMN_DPRICETYPE, ['eq' => Icpricp::PRICE_DETAIL_TYPE_BASE_PRICE_QUANTITY]);

        // Copy params for logging
        $params = [
            Icpricp::COLUMN_CURRENCY   => $this->getCurrencyCode(),
            Icpricp::COLUMN_ITEMNO     => $this->getItemNumber(),
            Icpricp::COLUMN_PRICELIST  => $this->getPriceListCode(),
            Icpricp::COLUMN_IS_ACTIVE  => true,
            Icpricp::COLUMN_DPRICETYPE => Icpricp::PRICE_DETAIL_TYPE_BASE_PRICE_QUANTITY
        ];

        // Filter by 'uom' if it's passed in
        if (!empty($uom)) {
            $icpricpCollection = $icpricpCollection
                ->addFieldToFilter(Icpricp::COLUMN_QTYUNIT, ['eq' => $uom]);

            $params[] = [Icpricp::COLUMN_QTYUNIT => $uom];
        }

        $collectionCount = $icpricpCollection->getSize();
        //$this->log('getDetails()', [
        //    'table'           => 'icpricp',
        //    'params'          => $params,
        //    'collectionCount' => $collectionCount
        //]);

        if ($collectionCount === 0) {
            $this->notice('getDetails() - No ICPRICP records found.');

            return null;
        }

        if ($collectionCount > 1) {
            $this->notice('getDetails() - More than one ICPRICP record found.');

            return null;
        }

        /** @var \ECInternet\Sage300Pricing\Model\Data\Icpricp $icpricp */
        $icpricp = $icpricpCollection->getFirstItem();

        if ($icpricp instanceof Icpricp) {
            return $icpricp;
        }

        return null;
    }

    /**
     * @return array
     */
    public function getTierPrices()
    {
        $tierPrices = [];

        if ($this->isPriceDeterminedByVolumeDiscounts()) {
            $quantity1 = $this->getQuantityLevel1();
            $quantity2 = $this->getQuantityLevel2();
            $quantity3 = $this->getQuantityLevel3();
            $quantity4 = $this->getQuantityLevel4();
            $quantity5 = $this->getQuantityLevel5();

            if ($this->isDiscountOrMarkupAppliedByPercentage()) {
                if ($discount1 = $this->getDiscountMarkupPercentage1()) {
                    $tierPrices[] = ['qty' => $quantity1, 'percentage' => $discount1];
                }
                if ($discount2 = $this->getDiscountMarkupPercentage2()) {
                    $tierPrices[] = ['qty' => $quantity2, 'percentage' => $discount2];
                }
                if ($discount3 = $this->getDiscountMarkupPercentage3()) {
                    $tierPrices[] = ['qty' => $quantity3, 'percentage' => $discount3];
                }
                if ($discount4 = $this->getDiscountMarkupPercentage4()) {
                    $tierPrices[] = ['qty' => $quantity4, 'percentage' => $discount4];
                }
                if ($discount5 = $this->getDiscountMarkupPercentage5()) {
                    $tierPrices[] = ['qty' => $quantity5, 'percentage' => $discount5];
                }
            } else {
                if ($discount1 = $this->getDiscountMarkupAmount1()) {
                    $tierPrices[] = ['qty' => $quantity1, 'amount' => $discount1];
                }
                if ($discount2 = $this->getDiscountMarkupAmount2()) {
                    $tierPrices[] = ['qty' => $quantity2, 'amount' => $discount2];
                }
                if ($discount3 = $this->getDiscountMarkupAmount3()) {
                    $tierPrices[] = ['qty' => $quantity3, 'amount' => $discount3];
                }
                if ($discount4 = $this->getDiscountMarkupAmount4()) {
                    $tierPrices[] = ['qty' => $quantity4, 'amount' => $discount4];
                }
                if ($discount5 = $this->getDiscountMarkupAmount5()) {
                    $tierPrices[] = ['qty' => $quantity5, 'amount' => $discount5];
                }
            }
        }

        return $tierPrices;
    }

    /**
     * Get maximum quantity level
     *
     * @return float
     */
    public function getMaxQuantityLevel()
    {
        return max(
            $this->getQuantityLevel1(),
            $this->getQuantityLevel2(),
            $this->getQuantityLevel3(),
            $this->getQuantityLevel4(),
            $this->getQuantityLevel5()
        );
    }

    /**
     * Get unit price
     *
     * @param string|null $uom
     *
     * @return float|null
     */
    public function getUnitPrice(string $uom = null)
    {
        $this->log('getUnitPrice()', ['uom' => $uom]);

        /** @var \ECInternet\Sage300Pricing\Model\Data\Icpricp $details */
        if ($details = $this->getDetails($uom)) {
            return $details->getUnitPrice();
        } else {
            $this->notice('getUnitPrice() - Unable to find Item Pricing Details.');
        }

        return null;
    }

    /**
     * Is discount or markup applied by percentage?
     *
     * @return bool
     */
    private function isDiscountOrMarkupAppliedByPercentage()
    {
        return $this->getDiscountMarkupPriceBy() == self::DISCOUNT_MARKUP_PRICE_BY_PERCENTAGE;
    }

    /**
     * Is discount or markup applied by amount?
     *
     * @return bool
     */
    private function isDiscountOrMarkupAppliedByAmount()
    {
        return $this->getDiscountMarkupPriceBy() == self::DISCOUNT_MARKUP_PRICE_BY_AMOUNT;
    }

    /**
     * Get discount markup percentage
     *
     * @param int $index
     *
     * @return float
     */
    private function getDiscountMarkupPercentage(int $index)
    {
        switch ($index) {
            case 1:
                return $this->getDiscountMarkupPercentage1();
            case 2:
                return $this->getDiscountMarkupPercentage2();
            case 3:
                return $this->getDiscountMarkupPercentage3();
            case 4:
                return $this->getDiscountMarkupPercentage4();
            case 5:
                return $this->getDiscountMarkupPercentage5();
            default:
                $this->notice("getDiscountMarkupPercentage() - Unexpected percentage level: [$index].");

                return 0.0;
        }
    }

    /**
     * Get quantity level
     *
     * @param float $qty
     *
     * @return int|null
     */
    private function getQuantityLevel(float $qty)
    {
        if ($qty >= $this->getQuantityLevel1() && $this->getQuantityLevel1() != 0) {
            return 1;
        }

        if ($qty >= $this->getQuantityLevel2() && $this->getQuantityLevel2() != 0) {
            return 2;
        }

        if ($qty >= $this->getQuantityLevel3() && $this->getQuantityLevel3() != 0) {
            return 3;
        }

        if ($qty >= $this->getQuantityLevel4() && $this->getQuantityLevel4() != 0) {
            return 4;
        }

        if ($qty >= $this->getQuantityLevel5() && $this->getQuantityLevel5() != 0) {
            return 5;
        }

        return null;
    }

    /**
     * Get discount markup amount
     *
     * @param int $index
     *
     * @return float;
     */
    private function getDiscountMarkupAmount(int $index)
    {
        $this->log('getDiscountMarkupAmount()', ['index' => $index]);

        switch ($index) {
            case 1:
                return $this->getDiscountMarkupAmount1();
            case 2:
                return $this->getDiscountMarkupAmount2();
            case 3:
                return $this->getDiscountMarkupAmount3();
            case 4:
                return $this->getDiscountMarkupAmount4();
            case 5:
                return $this->getDiscountMarkupAmount5();
            default:
                $this->notice("getDiscountMarkupAmount() - Unexpected amount level: [$index].");
                return 0.0;
        }
    }

    /**
     * Write to extension log
     *
     * @param string $message
     * @param array  $extra
     *
     * @return void
     */
    private function notice(string $message, array $extra = [])
    {
        $this->logger->notice('Model/Data/Icpric - ' . $message, $extra);
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
        $this->logger->info('Model/Data/Icpric - ' . $message, $extra);
    }
}
