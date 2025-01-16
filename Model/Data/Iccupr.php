<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Model\Data;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime;
use ECInternet\Sage300Pricing\Api\Data\IccuprInterface;

/**
 * Iccupr data model
 */
class Iccupr extends AbstractModel implements IdentityInterface, IccuprInterface
{
    const CACHE_TAG = 'ecinternet_sage300pricing_iccupr';

    protected $_cacheTag    = self::CACHE_TAG;

    protected $_eventPrefix = 'ecinternet_sage300pricing_iccupr';

    protected $_eventObject = 'iccupr';

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    private $dateTime;

    /**
     * Iccupr constructor.
     *
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
        $this->_init('ECInternet\Sage300Pricing\Model\ResourceModel\Iccupr');
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

    public function setUpdatedAt($updatedAt)
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

    public function getCustomerNumber()
    {
        return (string)$this->getData(self::COLUMN_CUSTNO);
    }

    public function setCustomerNumber(string $customerNumber)
    {
        $this->setData(self::COLUMN_CUSTNO, $customerNumber);
    }

    public function getItemNumber()
    {
        return (string)$this->getData(self::COLUMN_ITEMNO);
    }

    public function setItemNumber(string $itemNumber)
    {
        $this->setData(self::COLUMN_ITEMNO, $itemNumber);
    }

    public function getPriceList()
    {
        return (string)$this->getData(self::COLUMN_PRICELIST);
    }

    public function setPriceList(string $priceList)
    {
        $this->setData(self::COLUMN_PRICELIST, $priceList);
    }

    public function getExpirationDate()
    {
        return (string)$this->getData(self::COLUMN_EXPIRE);
    }

    public function setExpirationDate(string $expirationDate)
    {
        $this->setData(self::COLUMN_EXPIRE, $expirationDate);
    }

    public function getPriceType()
    {
        return (int)$this->getData(self::COLUMN_PRICETYPE);
    }

    public function setPriceType(int $priceType)
    {
        $this->setData(self::COLUMN_PRICETYPE, $priceType);
    }

    public function getCustomerType()
    {
        return (int)$this->getData(self::COLUMN_CUSTTYPE);
    }

    public function setCustomerType(int $customerType)
    {
        $this->setData(self::COLUMN_CUSTTYPE, $customerType);
    }

    public function getDiscountPercentage()
    {
        return (float)$this->getData(self::COLUMN_DISCPER);
    }

    public function setDiscountPercentage(float $discountPercentage)
    {
        $this->setData(self::COLUMN_DISCPER, $discountPercentage);
    }

    public function getDiscountAmount()
    {
        return (float)$this->getData(self::COLUMN_DISCAMT);
    }

    public function setDiscountAmount(float $discountAmount)
    {
        $this->setData(self::COLUMN_DISCAMT, $discountAmount);
    }

    public function getPlusAmount()
    {
        return (float)$this->getData(self::COLUMN_PLUSAMT);
    }

    public function setPlusAmount(float $plusAmount)
    {
        $this->setData(self::COLUMN_PLUSAMT, $plusAmount);
    }

    public function getPlusPercentage()
    {
        return (float)$this->getData(self::COLUMN_PLUSPER);
    }

    public function setPlusPercentage(float $plusPercentage)
    {
        $this->setData(self::COLUMN_PLUSPER, $plusPercentage);
    }

    public function getFixedPrice()
    {
        return (float)$this->getData(self::COLUMN_FIXPRICE);
    }

    public function setFixedPrice(float $fixedPrice)
    {
        $this->setData(self::COLUMN_FIXPRICE, $fixedPrice);
    }

    public function getStartDate()
    {
        return (string)$this->getData(self::COLUMN_STARTDATE);
    }

    public function setStartDate(string $startDate)
    {
        $this->setData(self::COLUMN_STARTDATE, $startDate);
    }

    public function isValidToday()
    {
        // YYYYMMDD format for easier sorting
        $today = date('Ymd');

        // Start date is after today --> Invalid
        if ($this->getStartDate() > $today) {
            return false;
        }

        // Expiration date is not zero (meaning it's set) and it's today or in the past --> Invalid
        if ($this->getExpirationDate() <> 0 && $this->getExpirationDate() <= $today) {
            return false;
        }

        return true;
    }
}
