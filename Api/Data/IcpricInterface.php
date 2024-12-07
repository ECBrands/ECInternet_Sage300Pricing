<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Api\Data;

interface IcpricInterface
{
    public const COLUMN_ID                            = 'entity_id';

    public const COLUMN_UPDATED_AT                    = 'updated_at';

    public const COLUMN_IS_ACTIVE                     = 'is_active';

    public const COLUMN_CURRENCY                      = 'CURRENCY';

    public const COLUMN_ITEMNO                        = 'ITEMNO';

    public const COLUMN_PRICELIST                     = 'PRICELIST';

    public const COLUMN_MARKUPCOST                    = 'MARKUPCOST';

    public const COLUMN_PRICEFMT                      = 'PRICEFMT';

    public const COLUMN_PRCNTLVL1                     = 'PRCNTLVL1';

    public const COLUMN_PRCNTLVL2                     = 'PRCNTLVL2';

    public const COLUMN_PRCNTLVL3                     = 'PRCNTLVL3';

    public const COLUMN_PRCNTLVL4                     = 'PRCNTLVL4';

    public const COLUMN_PRCNTLVL5                     = 'PRCNTLVL5';

    public const COLUMN_PRICEBASE                     = 'PRICEBASE';

    public const COLUMN_PRICEQTY1                     = 'PRICEQTY1';

    public const COLUMN_PRICEQTY2                     = 'PRICEQTY2';

    public const COLUMN_PRICEQTY3                     = 'PRICEQTY3';

    public const COLUMN_PRICEQTY4                     = 'PRICEQTY4';

    public const COLUMN_PRICEQTY5                     = 'PRICEQTY5';

    public const COLUMN_AMOUNTLVL1                    = 'AMOUNTLVL1';

    public const COLUMN_AMOUNTLVL2                    = 'AMOUNTLVL2';

    public const COLUMN_AMOUNTLVL3                    = 'AMOUNTLVL3';

    public const COLUMN_AMOUNTLVL4                    = 'AMOUNTLVL4';

    public const COLUMN_AMOUNTLVL5                    = 'AMOUNTLVL5';

    public const DISCOUNT_MARKUP_PRICE_BY_PERCENTAGE  = 1;

    public const DISCOUNT_MARKUP_PRICE_BY_AMOUNT      = 2;

    public const PRICE_DETERMINED_BY_CUSTOMER_TYPE    = 1;

    public const PRICE_DETERMINED_BY_VOLUME_DISCOUNTS = 2;

    /**
     * Get ID
     *
     * @return mixed
     */
    public function getId();

    /**
     * Get Updated At
     *
     * @return string
     */
    public function getUpdatedAt();

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
     * Get Markup Cost
     *
     * @return float
     */
    public function getMarkupCost();

    /**
     * Set Markup Cost
     *
     * @param float $markupCost
     *
     * @return void
     */
    public function setMarkupCost(float $markupCost);

    /**
     * Get Discount Markup Price By - PRICEFMT
     *
     * @return int
     */
    public function getDiscountMarkupPriceBy();

    /**
     * Set Discount Markup Price By
     *
     * @param int $markupPriceBy
     *
     * @return void
     */
    public function setDiscountMarkupPriceBy(int $markupPriceBy);

    /**
     * Get Discount Markup Percentage 1
     *
     * @return float
     */
    public function getDiscountMarkupPercentage1();

    /**
     * Set Discount Markup Percentage 1
     *
     * @param float $discountMarkupPercentage1
     *
     * @return void
     */
    public function setDiscountMarkupPercentage1(float $discountMarkupPercentage1);

    /**
     * Get Discount Markup Percentage 2
     *
     * @return float
     */
    public function getDiscountMarkupPercentage2();

    /**
     * Set Discount Markup Percentage 2
     *
     * @param float $discountMarkupPercentage2
     *
     * @return void
     */
    public function setDiscountMarkupPercentage2(float $discountMarkupPercentage2);

    /**
     * Get Discount Markup Percentage 3
     *
     * @return float
     */
    public function getDiscountMarkupPercentage3();

    /**
     * Set Discount Markup Percentage 3
     *
     * @param float $discountMarkupPercentage3
     *
     * @return void
     */
    public function setDiscountMarkupPercentage3(float $discountMarkupPercentage3);

    /**
     * Get Discount Markup Percentage 4
     *
     * @return float
     */
    public function getDiscountMarkupPercentage4();

    /**
     * Set Discount Markup Percentage 4
     *
     * @param float $discountMarkupPercentage4
     *
     * @return void
     */
    public function setDiscountMarkupPercentage4(float $discountMarkupPercentage4);

    /**
     * Get Discount Markup Percentage 5
     *
     * @return float
     */
    public function getDiscountMarkupPercentage5();

    /**
     * Set Discount Markup Percentage 5
     *
     * @param float $discountMarkupPercentage5
     *
     * @return void
     */
    public function setDiscountMarkupPercentage5(float $discountMarkupPercentage5);

    /**
     * Get Price Determined By - PRICEBASE
     *
     * @return int
     */
    public function getPriceDeterminedBy();

    /**
     * Set Price Determined By
     *
     * @param int $priceDeterminedBy
     *
     * @return void
     */
    public function setPriceDeterminedBy(int $priceDeterminedBy);

    /**
     * Get Quantity Level 1
     *
     * @return float
     */
    public function getQuantityLevel1();

    /**
     * Set Quantity Level 1
     *
     * @param float $quantityLevel1
     *
     * @return void
     */
    public function setQuantityLevel1(float $quantityLevel1);

    /**
     * Get Quantity Level 2
     *
     * @return float
     */
    public function getQuantityLevel2();

    /**
     * Set Quantity Level 2
     *
     * @param float $quantityLevel2
     *
     * @return void
     */
    public function setQuantityLevel2(float $quantityLevel2);

    /**
     * Get Quantity Level 3
     *
     * @return float
     */
    public function getQuantityLevel3();

    /**
     * Set Quantity Level 3
     *
     * @param float $quantityLevel3
     *
     * @return void
     */
    public function setQuantityLevel3(float $quantityLevel3);

    /**
     * Get Quantity Level 4
     *
     * @return float
     */
    public function getQuantityLevel4();

    /**
     * Set Quantity Level 4
     *
     * @param float $quantityLevel4
     *
     * @return void
     */
    public function setQuantityLevel4(float $quantityLevel4);

    /**
     * Get Quantity Level 5
     *
     * @return float
     */
    public function getQuantityLevel5();

    /**
     * Set Quantity Level 5
     *
     * @param float $quantityLevel5
     *
     * @return void
     */
    public function setQuantityLevel5(float $quantityLevel5);

    /**
     * Get Discount/Markup Amount 1
     *
     * @return float
     */
    public function getDiscountMarkupAmount1();

    /**
     * Get Discount/Markup Amount 1
     *
     * @param float $discountMarkupAmount1
     *
     * @return void
     */
    public function setDiscountMarkupAmount1(float $discountMarkupAmount1);

    /**
     * Get Discount/Markup Amount 2
     *
     * @return float
     */
    public function getDiscountMarkupAmount2();

    /**
     * Get Discount/Markup Amount 2
     *
     * @param float $discountMarkupAmount2
     *
     * @return void
     */
    public function setDiscountMarkupAmount2(float $discountMarkupAmount2);

    /**
     * Get Discount/Markup Amount 3
     *
     * @return float
     */
    public function getDiscountMarkupAmount3();

    /**
     * Get Discount/Markup Amount 3
     *
     * @param float $discountMarkupAmount3
     *
     * @return void
     */
    public function setDiscountMarkupAmount3(float $discountMarkupAmount3);

    /**
     * Get Discount/Markup Amount 4
     *
     * @return float
     */
    public function getDiscountMarkupAmount4();

    /**
     * Get Discount/Markup Amount 4
     *
     * @param float $discountMarkupAmount4
     *
     * @return void
     */
    public function setDiscountMarkupAmount4(float $discountMarkupAmount4);

    /**
     * Get Discount/Markup Amount 5
     *
     * @return float
     */
    public function getDiscountMarkupAmount5();

    /**
     * Get Discount/Markup Amount 5
     *
     * @param float $discountMarkupAmount5
     *
     * @return void
     */
    public function setDiscountMarkupAmount5(float $discountMarkupAmount5);
}
