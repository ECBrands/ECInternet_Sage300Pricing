<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Api\Data;

interface IccuprInterface
{
    const COLUMN_ID                         = 'entity_id';

    const COLUMN_UPDATED_AT                 = 'updated_at';

    const COLUMN_IS_ACTIVE                  = 'is_active';

    const COLUMN_CUSTNO                     = 'CUSTNO';

    const COLUMN_ITEMNO                     = 'ITEMNO';

    const COLUMN_PRICELIST                  = 'PRICELIST';

    const COLUMN_EXPIRE                     = 'EXPIRE';

    const COLUMN_PRICETYPE                  = 'PRICETYPE';

    const COLUMN_CUSTTYPE                   = 'CUSTTYPE';

    const COLUMN_DISCPER                    = 'DISCPER';

    const COLUMN_DISCAMT                    = 'DISCAMT';

    const COLUMN_PLUSAMT                    = 'PLUSAMT';

    const COLUMN_PLUSPER                    = 'PLUSPER';

    const COLUMN_FIXPRICE                   = 'FIXPRICE';

    const COLUMN_STARTDATE                  = 'STARTDATE';

    const PRICE_TYPE_CUSTOMER_TYPE          = 1;

    const PRICE_TYPE_DISCOUNT_PERCENTAGE    = 2;

    const PRICE_TYPE_DISCOUNT_AMOUNT        = 3;

    const PRICE_TYPE_COST_PLUS_A_PERCENTAGE = 4;

    const PRICE_TYPE_COST_PLUS_FIXED_AMOUNT = 5;

    const PRICE_TYPE_FIXED_PRICE            = 6;

    /**
     * Get ID
     *
     * @return mixed
     */
    public function getId();

    /**
     * Set Updated At
     *
     * @param string $updatedAt
     *
     * @return void
     */
    public function setUpdatedAt(string $updatedAt);

    /**
     * Get IsActive
     *
     * @return bool
     */
    public function getIsActive();

    /**
     * Set IsActive
     *
     * @param bool $isActive
     *
     * @return void
     */
    public function setIsActive(bool $isActive);

    /**
     * @return string
     */
    public function getCustomerNumber();

    /**
     * @param string $customerNumber
     *
     * @return void
     */
    public function setCustomerNumber(string $customerNumber);

    /**
     * @return string
     */
    public function getItemNumber();

    /**
     * @param string $itemNumber
     *
     * @return void
     */
    public function setItemNumber(string $itemNumber);

    /**
     * @return string
     */
    public function getPriceList();

    /**
     * @param string $priceList
     *
     * @return void
     */
    public function setPriceList(string $priceList);

    /**
     * Get Expiration Date - EXPIRE
     *
     * @return string
     */
    public function getExpirationDate();

    /**
     * Set Expiration Date - EXPIRE
     *
     * @param string $expirationDate
     *
     * @return void
     */
    public function setExpirationDate(string $expirationDate);

    /**
     * Get Price Type - PRICETYPE
     *
     * @return int
     */
    public function getPriceType();

    /**
     * Set Price Type - PRICETYPE
     *
     * @param int $priceType
     *
     * @return void
     */
    public function setPriceType(int $priceType);

    /**
     * Get Customer Type - CUSTTYPE
     *
     * @return int
     */
    public function getCustomerType();

    /**
     * Set Customer Type - CUSTTYPE
     *
     * @param int $customerType
     *
     * @return void
     */
    public function setCustomerType(int $customerType);

    /**
     * Get Discount Percentage
     *
     * @return float
     */
    public function getDiscountPercentage();

    /**
     * @param float $discountPercentage
     *
     * @return void
     */
    public function setDiscountPercentage(float $discountPercentage);
    /**
     * Get Discount Amount
     *
     * @return float
     */
    public function getDiscountAmount();

    /**
     * @param float $discountAmount
     *
     * @return void
     */
    public function setDiscountAmount(float $discountAmount);

    /**
     * Get Plus Amount
     *
     * @return float
     */
    public function getPlusAmount();

    /**
     * @param float $plusAmount
     *
     * @return void
     */
    public function setPlusAmount(float $plusAmount);

    /**
     * Get Plus Percentage
     *
     * @return float
     */
    public function getPlusPercentage();

    /**
     * @param float $plusPercentage
     *
     * @return void
     */
    public function setPlusPercentage(float $plusPercentage);

    /**
     * Get Fixed Price
     *
     * @return float
     */
    public function getFixedPrice();

    /**
     * @param float $fixedPrice
     *
     * @return void
     */
    public function setFixedPrice(float $fixedPrice);

    /**
     * Get Start Date
     *
     * @return string
     */
    public function getStartDate();

    /**
     * @param string $startDate
     *
     * @return void
     */
    public function setStartDate(string $startDate);

    /**
     * Is current date within date range?
     *
     * @return bool
     */
    public function isValidToday();
}
