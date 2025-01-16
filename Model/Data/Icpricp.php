<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Model\Data;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime;
use ECInternet\Sage300Pricing\Api\Data\IcpricpInterface;

/**
 * Icpricp data model
 */
class Icpricp extends AbstractModel implements IdentityInterface, IcpricpInterface
{
    const CACHE_TAG = 'ecinternet_sage300pricing_icpricp';

    protected $_cacheTag    = 'ecinternet_sage300pricing_icpricp';

    protected $_eventPrefix = 'ecinternet_sage300pricing_icpricp';

    protected $_eventObject = 'icpricp';

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    private $dateTime;

    /**
     * @param \Magento\Framework\Model\Context                             $context
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Magento\Framework\Stdlib\DateTime                           $dateTime
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null           $resourceCollection
     * @param array                                                        $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        DateTime $dateTime,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->dateTime = $dateTime;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init('ECInternet\Sage300Pricing\Model\ResourceModel\Icpricp');
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

    public function getPriceDetailType()
    {
        return (int)$this->getData(self::COLUMN_DPRICETYPE);
    }

    public function setPriceDetailType(int $priceDetailType)
    {
        $this->setData(self::COLUMN_DPRICETYPE, $priceDetailType);
    }

    public function getQuantityUnit()
    {
        return (string)$this->getData(self::COLUMN_QTYUNIT);
    }

    public function setQuantityUnit(string $quantityUnit)
    {
        $this->setData(self::COLUMN_QTYUNIT, $quantityUnit);
    }

    public function getWeightUnit()
    {
        return (string)$this->getData(self::COLUMN_WEIGHTUNIT);
    }

    public function setWeightUnit(string $weightUnit)
    {
        $this->setData(self::COLUMN_WEIGHTUNIT, $weightUnit);
    }

    public function getUnitPrice()
    {
        return (float)$this->getData(self::COLUMN_UNITPRICE);
    }

    public function setUnitPrice(float $unitPrice)
    {
        $this->setData(self::COLUMN_UNITPRICE, $unitPrice);
    }

    public function getConversionFactor()
    {
        return (float)$this->getData(self::COLUMN_CONVERSION);
    }

    public function setConversionFactor(float $conversionFactor)
    {
        $this->setData(self::COLUMN_CONVERSION, $conversionFactor);
    }

    /**
     * @inheriDoc
     *
     * @throws LocalizedException
     */
    public function getQuantityOrWeightUnit()
    {
        if ($qtyUnit = $this->getQuantityUnit()) {
            return $qtyUnit;
        } elseif ($weightUnit = $this->getWeightUnit()) {
            return $weightUnit;
        }

        throw new LocalizedException(__('Unable to determine quantity/weight unit'));
    }
}
