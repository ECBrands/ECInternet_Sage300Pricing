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
use ECInternet\Sage300Pricing\Api\Data\IcpricInterface;
use ECInternet\Sage300Pricing\Api\Data\IcpricSearchResultsInterfaceFactory;
use ECInternet\Sage300Pricing\Api\IcpricRepositoryInterface;
use ECInternet\Sage300Pricing\Logger\Logger;
use ECInternet\Sage300Pricing\Model\Data\Icpric;
use ECInternet\Sage300Pricing\Model\ResourceModel\Icpric as IcpricResource;
use ECInternet\Sage300Pricing\Model\ResourceModel\Icpric\CollectionFactory as IcpricCollectionFactory;
use Exception;

/**
 * Icpric model repository
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class IcpricRepository implements IcpricRepositoryInterface
{
    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var \ECInternet\Sage300Pricing\Api\Data\IcpricSearchResultsInterfaceFactory
     */
    private $icpricSearchResultsFactory;

    /**
     * @var \ECInternet\Sage300Pricing\Logger\Logger
     */
    private $logger;

    /**
     * @var \ECInternet\Sage300Pricing\Model\ResourceModel\Icpric
     */
    private $resourceModel;

    /**
     * @var \ECInternet\Sage300Pricing\Model\ResourceModel\Icpric\CollectionFactory
     */
    private $icpricCollectionFactory;

    /**
     * IcpricRepository constructor.
     *
     * @param \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface      $collectionProcessor
     * @param \ECInternet\Sage300Pricing\Api\Data\IcpricSearchResultsInterfaceFactory $icpricSearchResultsFactory
     * @param \ECInternet\Sage300Pricing\Logger\Logger                                $logger
     * @param \ECInternet\Sage300Pricing\Model\ResourceModel\Icpric                   $resourceModel
     * @param \ECInternet\Sage300Pricing\Model\ResourceModel\Icpric\CollectionFactory $icpricCollectionFactory
     */
    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        IcpricSearchResultsInterfaceFactory $icpricSearchResultsFactory,
        Logger $logger,
        IcpricResource $resourceModel,
        IcpricCollectionFactory $icpricCollectionFactory
    ) {
        $this->collectionProcessor        = $collectionProcessor;
        $this->icpricSearchResultsFactory = $icpricSearchResultsFactory;
        $this->logger                     = $logger;
        $this->resourceModel              = $resourceModel;
        $this->icpricCollectionFactory    = $icpricCollectionFactory;
    }

    public function save(
        IcpricInterface $icpric
    ) {
        $this->log('save()', ['icpric' => $icpric->getData()]);

        $this->validate($icpric);

        // If we find existing, grab the ID and set on incoming record
        if ($this->doesRecordExist($icpric)) {
            $model = $this->get($icpric->getCurrencyCode(), $icpric->getItemNumber(), $icpric->getPriceListCode());
            $icpric->setId($model->getId());
        }

        try {
            $this->resourceModel->save($icpric);
        } catch (Exception $e) {
            $this->log('save()', [
                'class'     => get_class($e),
                'exception' => $e->getMessage(),
                'trace'     => $e->getTraceAsString()
            ]);

            throw $e;
        }

        return $icpric;
    }

    public function bulkSave(
        array $icpricArray
    ) {
        $this->log('bulkSave()');

        $results = [];

        foreach ($icpricArray as $icpric) {
            $this->log('bulkSave()', ['icpric' => $icpric->getData()]);

            try {
                $this->save($icpric);
                $results[] = true;
            } catch (Exception $e) {
                $this->log('bulkSave()', ['exception' => $e->getMessage()]);
                $results[] = false;
            }
        }

        return $results;
    }

    public function get(
        string $currencyCode,
        string $itemNumber,
        string $pricelist
    ) {
        $this->log('get()', [
            'currencyCode' => $currencyCode,
            'itemNumber'   => $itemNumber,
            'pricelist'    => $pricelist
        ]);

        if (empty($currencyCode)) {
            $this->log("get() - Blank 'currencyCode'");

            return null;
        }

        if (empty($itemNumber)) {
            $this->log("get() - Blank 'itemNumber'");

            return null;
        }

        if (empty($pricelist)) {
            $this->log("get() - Blank 'pricelist'");

            return null;
        }

        /** @var \ECInternet\Sage300Pricing\Model\ResourceModel\Icpric\Collection $collection */
        $collection = $this->icpricCollectionFactory->create()
            ->addFieldToFilter(Data\Icpric::COLUMN_CURRENCY, $currencyCode)
            ->addFieldToFilter(Data\Icpric::COLUMN_ITEMNO, $itemNumber)
            ->addFieldToFilter(Data\Icpric::COLUMN_PRICELIST, $pricelist);

        $collectionCount = $collection->getSize();
        $this->log('get()', [
            'select'          => $collection->getSelect(),
            'collectionCount' => $collectionCount
        ]);

        // If we find no records, log it and return.
        if ($collectionCount === 0) {
            $this->log('get() - No ICPRIC records found.');

            return null;
        }

        // If we find multiple records, log and return.
        if ($collectionCount > 1) {
            $this->log('get() - More than one ICPRIC record found.');

            return null;
        }

        /** @var \ECInternet\Sage300Pricing\Model\Data\Icpric $icpric */
        $icpric = $collection->getFirstItem();
        if ($icpric instanceof Icpric) {
            return $icpric;
        }

        return null;
    }

    public function getById(int $id)
    {
        $collection = $this->icpricCollectionFactory->create()
            ->addFieldToFilter(Icpric::COLUMN_ID, ['eq' => $id]);

        if ($collection->getSize() === 1) {
            /** @var \ECInternet\Sage300Pricing\Model\Data\Icpric $icpric */
            $icpric = $collection->getFirstItem();
            if ($icpric instanceof Icpric) {
                return $icpric;
            }
        }

        return null;
    }

    public function getList(
        SearchCriteriaInterface $searchCriteria
    ) {
        /** @var \ECInternet\Sage300Pricing\Model\ResourceModel\Icpric\Collection $collection */
        $collection = $this->icpricCollectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var \ECInternet\Sage300Pricing\Api\Data\IcpricSearchResultsInterface $searchResults */
        $searchResults = $this->icpricSearchResultsFactory->create();

        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setTotalCount($collection->getSize());

        /** @noinspection PhpParamsInspection */
        $searchResults->setItems($collection->getItems());

        return $searchResults;
    }

    public function delete(string $currencyCode, string $itemNumber, string $pricelist)
    {
        if ($icpric = $this->get($currencyCode, $itemNumber, $pricelist)) {
            try {
                $this->resourceModel->delete($icpric);

                return true;
            } catch (Exception $e) {
                $this->log('delete()', [
                    'currencyCode' => $currencyCode,
                    'itemNumber'   => $itemNumber,
                    'pricelist'    => $pricelist,
                    'exception'    => $e->getMessage()
                ]);
            }
        }

        return false;
    }

    public function deleteById(int $icpricId)
    {
        if ($iccupr = $this->getById($icpricId)) {
            try {
                $this->resourceModel->delete($iccupr);
                return true;
            } catch (Exception $e) {
                $this->log('deleteById()', [
                    'icpricId'  => $icpricId,
                    'exception' => $e->getMessage()
                ]);
            }
        }

        return false;
    }

    /**
     * Validate ICPRIC record
     *
     * @param \ECInternet\Sage300Pricing\Api\Data\IcpricInterface $icpric
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function validate(
        IcpricInterface $icpric
    ) {
        if (empty($icpric->getCurrencyCode())) {
            throw new LocalizedException(__('CurrencyCode not set'));
        }

        if (empty($icpric->getItemNumber())) {
            throw new LocalizedException(__('ItemNumber not set'));
        }

        if (empty($icpric->getPriceListCode())) {
            throw new LocalizedException(__('Pricelist not set'));
        }
    }

    /**
     * Does ICPRIC record exist?
     *
     * @param \ECInternet\Sage300Pricing\Api\Data\IcpricInterface $icpric
     *
     * @return bool
     */
    private function doesRecordExist(
        IcpricInterface $icpric
    ) {
        $this->log('doesRecordExist()', [
            Data\Icpric::COLUMN_CURRENCY  => $icpric->getCurrencyCode(),
            Data\Icpric::COLUMN_ITEMNO    => $icpric->getItemNumber(),
            Data\Icpric::COLUMN_PRICELIST => $icpric->getPriceListCode()
        ]);

        /** @var \ECInternet\Sage300Pricing\Model\ResourceModel\Icpric\Collection $collection */
        $collection = $this->icpricCollectionFactory->create()
            ->addFieldToFilter(Data\Icpric::COLUMN_CURRENCY, $icpric->getCurrencyCode())
            ->addFieldToFilter(Data\Icpric::COLUMN_ITEMNO, $icpric->getItemNumber())
            ->addFieldToFilter(Data\Icpric::COLUMN_PRICELIST, $icpric->getPriceListCode());

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
        $this->logger->info('Model/IcpricRepository - ' . $message, $extra);
    }
}
