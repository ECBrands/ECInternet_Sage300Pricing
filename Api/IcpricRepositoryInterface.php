<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use ECInternet\Sage300Pricing\Api\Data\IcpricInterface;

interface IcpricRepositoryInterface
{
    /**
     * Save ICPRIC record
     *
     * @param \ECInternet\Sage300Pricing\Api\Data\IcpricInterface $icpric
     *
     * @return \ECInternet\Sage300Pricing\Api\Data\IcpricInterface
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(IcpricInterface $icpric);

    /**
     * Bulk save ICPRIC records
     *
     * @param \ECInternet\Sage300Pricing\Api\Data\IcpricInterface[] $icpricArray
     *
     * @return bool[]
     */
    public function bulkSave(array $icpricArray);

    /**
     * Get ICPRIC record
     *
     * @param string $currencyCode
     * @param string $itemNumber
     * @param string $pricelist
     *
     * @return \ECInternet\Sage300Pricing\Api\Data\IcpricInterface|null
     */
    public function get(string $currencyCode, string $itemNumber, string $pricelist);

    /**
     * Get ICPRIC by internal id
     *
     * @param int $id
     *
     * @return \ECInternet\Sage300Pricing\Api\Data\IcpricInterface|null
     */
    public function getById(int $id);

    /**
     * Get list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \ECInternet\Sage300Pricing\Api\Data\IcpricSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete ICPRIC record
     *
     * @param string $currencyCode
     * @param string $itemNumber
     * @param string $pricelist
     *
     * @return bool
     */
    public function delete(string $currencyCode, string $itemNumber, string $pricelist);

    /**
     * Delete ICPRIC record by ID
     *
     * @param int $icpricId
     *
     * @return bool
     */
    public function deleteById(int $icpricId);
}
