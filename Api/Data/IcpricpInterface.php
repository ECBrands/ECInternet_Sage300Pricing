<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Api\Data;

interface IcpricpInterface
{
    const COLUMN_ID                             = 'entity_id';

    const COLUMN_UPDATED_AT                     = 'updated_at';

    const COLUMN_IS_ACTIVE                      = 'is_active';

    const COLUMN_CURRENCY                       = 'CURRENCY';

    const COLUMN_ITEMNO                         = 'ITEMNO';

    const COLUMN_PRICELIST                      = 'PRICELIST';

    const COLUMN_DPRICETYPE                     = 'DPRICETYPE';

    const COLUMN_QTYUNIT                        = 'QTYUNIT';

    const COLUMN_WEIGHTUNIT                     = 'WEIGHTUNIT';

    const COLUMN_UNITPRICE                      = 'UNITPRICE';

    const COLUMN_CONVERSION                     = 'CONVERSION';

    const PRICE_DETAIL_TYPE_BASE_PRICE_QUANTITY = 1;

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
     * Get Currency Code
     *
     * @return string
     */
    public function getCurrencyCode();

    /**
     * Set Currency Code
     *
     * @param string $currencyCode
     *
     * @return void
     */
    public function setCurrencyCode(string $currencyCode);

    /**
     * Get Item Number
     *
     * @return string
     */
    public function getItemNumber();

    /**
     * Set Item Number
     *
     * @param string $itemNumber
     *
     * @return void
     */
    public function setItemNumber(string $itemNumber);

    /**
     * Get Price List Code
     *
     * @return string
     */
    public function getPriceListCode();

    /**
     * Set Price List Code
     *
     * @param string $priceListCode
     *
     * @return void
     */
    public function setPriceListCode(string $priceListCode);

    /**
     * Get Price Detail Type
     *
     * @return int
     */
    public function getPriceDetailType();

    /**
     * Set Price Detail Type
     *
     * @param int $priceDetailType
     *
     * @return void
     */
    public function setPriceDetailType(int $priceDetailType);

    /**
     * Get Quantity Unit
     *
     * @return string
     */
    public function getQuantityUnit();

    /**
     * Set Quantity Unit
     *
     * @param string $quantityUnit
     *
     * @return void
     */
    public function setQuantityUnit(string $quantityUnit);

    /**
     * Get Weight Unit
     *
     * @return string
     */
    public function getWeightUnit();

    /**
     * Set Weight Unit
     *
     * @param string $weightUnit
     *
     * @return void
     */
    public function setWeightUnit(string $weightUnit);

    /**
     * Get Unit Price
     *
     * @return float
     */
    public function getUnitPrice();

    /**
     * Set Unit Price
     *
     * @param float $unitPrice
     *
     * @return void
     */
    public function setUnitPrice(float $unitPrice);

    /**
     * Get Conversion Factor
     *
     * @return float
     */
    public function getConversionFactor();

    /**
     * Set Conversion Factor
     *
     * @param float $conversionFactor
     *
     * @return void
     */
    public function setConversionFactor(float $conversionFactor);

    /**
     * Get Quantity or Weight Unit
     *
     * @return string
     */
    public function getQuantityOrWeightUnit();
}
