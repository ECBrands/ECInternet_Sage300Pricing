<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\LocalizedException;
use ECInternet\Sage300Pricing\Api\Data\IccuprInterface;
use ECInternet\Sage300Pricing\Api\Data\IccuprSearchResultsInterfaceFactory;
use ECInternet\Sage300Pricing\Api\IccuprRepositoryInterface;
use ECInternet\Sage300Pricing\Logger\Logger;
use ECInternet\Sage300Pricing\Model\Data\Iccupr;
use ECInternet\Sage300Pricing\Model\ResourceModel\Iccupr as IccuprResource;
use ECInternet\Sage300Pricing\Model\ResourceModel\Iccupr\CollectionFactory as IccuprCollectionFactory;
use Exception;

/**
 * Iccupr model repository
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class IccuprRepository implements IccuprRepositoryInterface
{
    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var \ECInternet\Sage300Pricing\Api\Data\IccuprSearchResultsInterfaceFactory
     */
    private $iccuprSearchResultsFactory;

    /**
     * @var \ECInternet\Sage300Pricing\Logger\Logger
     */
    private $logger;

    /**
     * @var \ECInternet\Sage300Pricing\Model\ResourceModel\Iccupr
     */
    private $resourceModel;

    /**
     * @var \ECInternet\Sage300Pricing\Model\ResourceModel\Iccupr\CollectionFactory
     */
    private $iccuprCollectionFactory;

    /**
     * IccuprRepository constructor.
     *
     * @param \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface      $collectionProcessor
     * @param \ECInternet\Sage300Pricing\Api\Data\IccuprSearchResultsInterfaceFactory $iccuprSearchResultsFactory
     * @param \ECInternet\Sage300Pricing\Logger\Logger                                $logger
     * @param \ECInternet\Sage300Pricing\Model\ResourceModel\Iccupr                   $resourceModel
     * @param \ECInternet\Sage300Pricing\Model\ResourceModel\Iccupr\CollectionFactory $iccuprCollectionFactory
     */
    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        IccuprSearchResultsInterfaceFactory $iccuprSearchResultsFactory,
        Logger $logger,
        IccuprResource $resourceModel,
        IccuprCollectionFactory $iccuprCollectionFactory
    ) {
        $this->collectionProcessor        = $collectionProcessor;
        $this->iccuprSearchResultsFactory = $iccuprSearchResultsFactory;
        $this->logger                     = $logger;
        $this->resourceModel              = $resourceModel;
        $this->iccuprCollectionFactory    = $iccuprCollectionFactory;
    }

    public function save(
        IccuprInterface $iccupr
    ) {
        //$this->log('save()', ['iccupr' => $iccupr->getData()]);

        $this->validate($iccupr);

        // If we find existing, grab the ID and set on incoming record
        if ($this->doesRecordExist($iccupr)) {
            $model = $this->get($iccupr->getCustomerNumber(), $iccupr->getItemNumber(), $iccupr->getPriceList());
            $iccupr->setId($model->getId());
        }

        try {
            $this->resourceModel->save($iccupr);
        } catch (Exception $e) {
            $this->log('save()', [
                'class'     => get_class($e),
                'exception' => $e->getMessage(),
                'trace'     => $e->getTraceAsString()
            ]);

            throw $e;
        }

        return $iccupr;
    }

    public function bulkSave(
        array $iccuprArray
    ) {
        //$this->log('bulkSave()');

        $results = [];

        foreach ($iccuprArray as $iccupr) {
            //$this->log('bulkSave()', ['iccupr' => $iccupr->getData()]);

            try {
                $this->save($iccupr);
                $results[] = true;
            } catch (Exception $e) {
                $this->log('bulkSave()', ['exception' => $e->getMessage()]);
                $results[] = false;
            }
        }

        return $results;
    }

    public function get(
        string $customerNumber,
        string $itemNumber,
        string $pricelist
    ) {
        //$this->log('get()', [
        //    'customerNumber' => $customerNumber,
        //    'itemNumber'     => $itemNumber,
        //    'pricelist'      => $pricelist
        //]);

        if ($customerNumber == null) {
            $this->log("get() - Blank 'customerNumber'");

            return null;
        }

        if ($itemNumber == null) {
            $this->log("get() - Blank 'itemNumber'");

            return null;
        }

        if ($pricelist == null) {
            $this->log("get() - Blank 'pricelist'");

            return null;
        }

        // It appears that you get 1 contract price record per: [IDCUST]-[ITEMNO]-[PRICELIST] combination.
        // Because of this, we get that one record, and then examine whether the date is within range or not.
        /** @var \ECInternet\Sage300Pricing\Model\ResourceModel\Iccupr\Collection $collection */
        $collection = $this->iccuprCollectionFactory->create()
            ->addFieldToFilter(Iccupr::COLUMN_CUSTNO, ['eq' => $customerNumber])
            ->addFieldToFilter(Iccupr::COLUMN_ITEMNO, ['eq' => $itemNumber])
            ->addFieldToFilter(Iccupr::COLUMN_PRICELIST, ['eq' => $pricelist]);

        $collectionCount = $collection->getSize();
        //$this->log('get()', [
        //    'select'          => $collection->getSelect(),
        //    'collectionCount' => $collectionCount
        //]);

        // If we find no records, log it and return.
        if ($collectionCount === 0) {
            $this->log('get() - No ICCUPR records found.');

            return null;
        }

        // If we find multiple records, log and return.
        if ($collectionCount > 1) {
            $this->log('get() - More than one ICCUPR record found.');

            return null;
        }

        /** @var \ECInternet\Sage300Pricing\Model\Data\Iccupr $iccupr */
        $iccupr = $collection->getFirstItem();
        if ($iccupr instanceof Iccupr) {
            return $iccupr;
        }

        return null;
    }

    public function getById(int $id)
    {
        $collection = $this->iccuprCollectionFactory->create()
            ->addFieldToFilter(Iccupr::COLUMN_ID, ['eq' => $id]);

        if ($collection->getSize() === 1) {
            /** @var \ECInternet\Sage300Pricing\Model\Data\Iccupr $iccupr */
            $iccupr = $collection->getFirstItem();
            if ($iccupr instanceof Iccupr) {
                return $iccupr;
            }
        }

        return null;
    }

    public function getList(
        SearchCriteriaInterface $searchCriteria
    ) {
        /** @var \ECInternet\Sage300Pricing\Model\ResourceModel\Iccupr\Collection $collection */
        $collection = $this->iccuprCollectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var \ECInternet\Sage300Pricing\Api\Data\IccuprSearchResultsInterface $searchResults */
        $searchResults = $this->iccuprSearchResultsFactory->create();

        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setTotalCount($collection->getSize());

        /** @noinspection PhpParamsInspection */
        $searchResults->setItems($collection->getItems());

        return $searchResults;
    }

    public function delete(string $customerNumber, string $itemNumber, string $pricelist)
    {
        if ($iccupr = $this->get($customerNumber, $itemNumber, $pricelist)) {
            try {
                $this->resourceModel->delete($iccupr);

                return true;
            } catch (Exception $e) {
                $this->log('delete()', [
                    'customerNumber' => $customerNumber,
                    'itemNumber'     => $itemNumber,
                    'pricelist'      => $pricelist,
                    'exception'      => $e->getMessage()
                ]);
            }
        }

        return false;
    }

    public function deleteById(int $iccuprId)
    {
        if ($iccupr = $this->getById($iccuprId)) {
            try {
                $this->resourceModel->delete($iccupr);
                return true;
            } catch (Exception $e) {
                $this->log('deleteById()', [
                    'iccuprId'  => $iccuprId,
                    'exception' => $e->getMessage()
                ]);
            }
        }

        return false;
    }

    /**
     * Validate ICCUPR record
     *
     * @param \ECInternet\Sage300Pricing\Api\Data\IccuprInterface $iccupr
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function validate(
        IccuprInterface $iccupr
    ) {
        if (empty($iccupr->getCustomerNumber())) {
            throw new LocalizedException(__('CustomerNumber not set'));
        }

        if (empty($iccupr->getItemNumber())) {
            throw new LocalizedException(__('ItemNumber not set'));
        }

        if (empty($iccupr->getPriceList())) {
            throw new LocalizedException(__('PriceList not set'));
        }
    }

    /**
     * Does POPORH record exist?
     *
     * @param \ECInternet\Sage300Pricing\Api\Data\IccuprInterface $iccupr
     *
     * @return bool
     */
    protected function doesRecordExist(
        IccuprInterface $iccupr
    ) {
        /*
        $this->log('doesRecordExist()', [
            Iccupr::COLUMN_CUSTNO    => $iccupr->getCustomerNumber(),
            Iccupr::COLUMN_ITEMNO    => $iccupr->getItemNumber(),
            Iccupr::COLUMN_PRICELIST => $iccupr->getPriceList()
        ]);
        */

        /** @var \ECInternet\Sage300Pricing\Model\ResourceModel\Iccupr\Collection $collection */
        $collection = $this->iccuprCollectionFactory->create()
            ->addFieldToFilter(Iccupr::COLUMN_CUSTNO, $iccupr->getCustomerNumber())
            ->addFieldToFilter(Iccupr::COLUMN_ITEMNO, $iccupr->getItemNumber())
            ->addFieldToFilter(Iccupr::COLUMN_PRICELIST, $iccupr->getPriceList());

        return $collection->getSize() > 0;
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
        $this->logger->info('Model/IccuprRepository - ' . $message, $extra);
    }
}
