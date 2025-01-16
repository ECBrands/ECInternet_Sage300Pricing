<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use ECInternet\Sage300Pricing\Api\Data\IccuprInterface;

interface IccuprRepositoryInterface
{
    /**
     * Save ICCUPR record
     *
     * @param \ECInternet\Sage300Pricing\Api\Data\IccuprInterface $iccupr
     *
     * @return \ECInternet\Sage300Pricing\Api\Data\IccuprInterface
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(IccuprInterface $iccupr);

    /**
     * Bulk save ICCUPR records
     *
     * @param \ECInternet\Sage300Pricing\Api\Data\IccuprInterface[] $iccuprArray
     *
     * @return bool[]
     */
    public function bulkSave(array $iccuprArray);

    /**
     * Get ICCUPR record
     *
     * @param string $customerNumber
     * @param string $itemNumber
     * @param string $pricelist
     *
     * @return \ECInternet\Sage300Pricing\Api\Data\IccuprInterface|null
     */
    public function get(string $customerNumber, string $itemNumber, string $pricelist);

    /**
     * Get ICCUPR record by ID
     *
     * @param int $id
     *
     * @return \ECInternet\Sage300Pricing\Api\Data\IccuprInterface|null
     */
    public function getById(int $id);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \ECInternet\Sage300Pricing\Api\Data\IccuprSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete ICCUPR record
     *
     * @param string $customerNumber
     * @param string $itemNumber
     * @param string $pricelist
     *
     * @return bool
     */
    public function delete(string $customerNumber, string $itemNumber, string $pricelist);

    /**
     * Delete ICCUPR record by ID
     *
     * @param int $iccuprId
     *
     * @return bool
     */
    public function deleteById(int $iccuprId);
}
