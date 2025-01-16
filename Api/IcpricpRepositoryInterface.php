<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use ECInternet\Sage300Pricing\Api\Data\IcpricpInterface;

interface IcpricpRepositoryInterface
{
    /**
     * Save ICPRICP record
     *
     * @param \ECInternet\Sage300Pricing\Api\Data\IcpricpInterface $icpricp
     *
     * @return \ECInternet\Sage300Pricing\Api\Data\IcpricpInterface
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(IcpricpInterface $icpricp);

    /**
     * Bulk save ICPRICP records
     *
     * @param \ECInternet\Sage300Pricing\Api\Data\IcpricpInterface[] $icpricpArray
     *
     * @return bool[]
     */
    public function bulkSave(array $icpricpArray);

    /**
     * Get ICPRICP record
     *
     * @param string $currencyCode
     * @param string $itemNumber
     * @param string $pricelist
     * @param int    $priceDetailType
     * @param string $quantityUnit
     * @param string $weightUnit
     *
     * @return \ECInternet\Sage300Pricing\Api\Data\IcpricpInterface|null
     */
    public function get(
        string $currencyCode,
        string $itemNumber,
        string $pricelist,
        int $priceDetailType,
        string $quantityUnit,
        string $weightUnit
    );

    /**
     * Get ICPRICP by internal id
     *
     * @param int $id
     *
     * @return \ECInternet\Sage300Pricing\Api\Data\IcpricpInterface|null
     */
    public function getById(int $id);

    /**
     * Get list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \ECInternet\Sage300Pricing\Api\Data\IcpricpSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete ICPRIC record
     *
     * @param string $currencyCode
     * @param string $itemNumber
     * @param string $pricelist
     * @param int    $priceDetailType
     * @param string $quantityUnit
     * @param string $weightUnit
     *
     * @return bool
     */
    public function delete(
        string $currencyCode,
        string $itemNumber,
        string $pricelist,
        int $priceDetailType,
        string $quantityUnit,
        string $weightUnit
    );

    /**
     * Delete ICPRICP record by ID
     *
     * @param int $icpricpId
     *
     * @return bool
     */
    public function deleteById(int $icpricpId);
}
