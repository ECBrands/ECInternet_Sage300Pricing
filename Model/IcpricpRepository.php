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
use ECInternet\Sage300Pricing\Api\Data\IcpricpInterface;
use ECInternet\Sage300Pricing\Api\Data\IcpricpSearchResultsInterfaceFactory;
use ECInternet\Sage300Pricing\Api\IcpricpRepositoryInterface;
use ECInternet\Sage300Pricing\Logger\Logger;
use ECInternet\Sage300Pricing\Model\Data\Icpricp;
use ECInternet\Sage300Pricing\Model\ResourceModel\Icpricp as IcpricpResource;
use ECInternet\Sage300Pricing\Model\ResourceModel\Icpricp\CollectionFactory as IcpricpCollectionFactory;
use Exception;

/**
 * Icpricp model repository
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class IcpricpRepository implements IcpricpRepositoryInterface
{
    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var \ECInternet\Sage300Pricing\Api\Data\IcpricpSearchResultsInterfaceFactory
     */
    private $icpricpSearchResultsFactory;

    /**
     * @var \ECInternet\Sage300Pricing\Logger\Logger
     */
    private $logger;

    /**
     * @var \ECInternet\Sage300Pricing\Model\ResourceModel\Icpricp
     */
    private $resourceModel;

    /**
     * @var \ECInternet\Sage300Pricing\Model\ResourceModel\Icpricp\CollectionFactory
     */
    private $icpricpCollectionFactory;

    /**
     * IcpricpRepository constructor.
     *
     * @param \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface       $collectionProcessor
     * @param \ECInternet\Sage300Pricing\Api\Data\IcpricpSearchResultsInterfaceFactory $icpricpSearchResultsFactory
     * @param \ECInternet\Sage300Pricing\Logger\Logger                                 $logger
     * @param \ECInternet\Sage300Pricing\Model\ResourceModel\Icpricp                   $resourceModel
     * @param \ECInternet\Sage300Pricing\Model\ResourceModel\Icpricp\CollectionFactory $icpricpCollectionFactory
     */
    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        IcpricpSearchResultsInterfaceFactory $icpricpSearchResultsFactory,
        Logger $logger,
        IcpricpResource $resourceModel,
        IcpricpCollectionFactory $icpricpCollectionFactory
    ) {
        $this->collectionProcessor         = $collectionProcessor;
        $this->icpricpSearchResultsFactory = $icpricpSearchResultsFactory;
        $this->logger                      = $logger;
        $this->resourceModel               = $resourceModel;
        $this->icpricpCollectionFactory    = $icpricpCollectionFactory;
    }

    public function save(
        IcpricpInterface $icpricp
    ) {
        //$this->log('save()', ['icpricp' => $icpricp->getData()]);

        $this->validate($icpricp);

        // If we find existing, grab the ID and set on incoming record
        if ($this->doesRecordExist($icpricp)) {
            $model = $this->get(
                $icpricp->getCurrencyCode(),
                $icpricp->getItemNumber(),
                $icpricp->getPriceListCode(),
                $icpricp->getPriceDetailType(),
                $icpricp->getQuantityUnit(),
                $icpricp->getWeightUnit()
            );
            $icpricp->setId($model->getId());
        }

        try {
            $this->resourceModel->save($icpricp);
        } catch (Exception $e) {
            $this->log('save()', [
                'class'     => get_class($e),
                'exception' => $e->getMessage(),
                'trace'     => $e->getTraceAsString()
            ]);

            throw $e;
        }

        return $icpricp;
    }

    public function bulkSave(
        array $icpricpArray
    ) {
        //$this->log('bulkSave()');

        $results = [];

        foreach ($icpricpArray as $icpricp) {
            //$this->log('bulkSave()', ['icpricp' => $icpricp->getData()]);

            try {
                $this->save($icpricp);
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
        string $pricelist,
        int $priceDetailType = 1,
        string $quantityUnit = '',
        string $weightUnit = ''
    ) {
        //$this->log('get()', [
        //    'currencyCode'    => $currencyCode,
        //    'itemNumber'      => $itemNumber,
        //    'pricelist'       => $pricelist,
        //    'priceDetailType' => $priceDetailType,
        //    'quantityUnit'    => $quantityUnit,
        //    'weightUnit'      => $weightUnit
        //]);

        /** @var \ECInternet\Sage300Pricing\Model\ResourceModel\Icpricp\Collection $collection */
        $collection = $this->icpricpCollectionFactory->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter(Data\Icpricp::COLUMN_CURRENCY, $currencyCode)
            ->addFieldToFilter(Data\Icpricp::COLUMN_ITEMNO, $itemNumber)
            ->addFieldToFilter(Data\Icpricp::COLUMN_PRICELIST, $pricelist)
            ->addFieldToFilter(Data\Icpricp::COLUMN_DPRICETYPE, $priceDetailType)
            ->addFieldToFilter(
                [
                    Data\Icpricp::COLUMN_QTYUNIT,
                    Data\Icpricp::COLUMN_WEIGHTUNIT
                ],
                [
                    $quantityUnit,
                    $weightUnit
                ]
            );

        $collectionCount = $collection->getSize();
        //$this->log('get()', [
        //    'select'          => $collection->getSelect(),
        //    'collectionCount' => $collectionCount
        //]);

        // If we find no records, log it and return.
        if ($collectionCount === 0) {
            $this->log('get() - No ICPRICP records found.');

            return null;
        }

        // If we find multiple records, log and return.
        if ($collectionCount > 1) {
            $this->log('get() - More than one ICPRICP record found.');

            return null;
        }

        /** @var \ECInternet\Sage300Pricing\Model\Data\Icpric $icpricp */
        $icpricp = $collection->getFirstItem();
        if ($icpricp instanceof Icpricp) {
            return $icpricp;
        }

        return null;
    }

    public function getById(int $id)
    {
        $collection = $this->icpricpCollectionFactory->create()
            ->addFieldToFilter(Icpricp::COLUMN_ID, ['eq' => $id]);

        if ($collection->getSize() === 1) {
            /** @var \ECInternet\Sage300Pricing\Model\Data\Icpricp $icpricp */
            $icpricp = $collection->getFirstItem();
            if ($icpricp instanceof Icpricp) {
                return $icpricp;
            }
        }

        return null;
    }

    public function getList(
        SearchCriteriaInterface $searchCriteria
    ) {
        /** @var \ECInternet\Sage300Pricing\Model\ResourceModel\Icpricp\Collection $collection */
        $collection = $this->icpricpCollectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var \ECInternet\Sage300Pricing\Api\Data\IcpricpSearchResultsInterface $searchResults */
        $searchResults = $this->icpricpSearchResultsFactory->create();

        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setTotalCount($collection->getSize());

        /** @noinspection PhpParamsInspection */
        $searchResults->setItems($collection->getItems());

        return $searchResults;
    }

    public function delete(
        string $currencyCode,
        string $itemNumber,
        string $pricelist,
        int $priceDetailType,
        string $quantityUnit,
        string $weightUnit
    ) {
        if ($icpricp = $this->get($currencyCode, $itemNumber, $pricelist, $priceDetailType, $quantityUnit, $weightUnit)) {
            try {
                $this->resourceModel->delete($icpricp);

                return true;
            } catch (Exception $e) {
                $this->log('delete()', [
                    'currencyCode'    => $currencyCode,
                    'itemNumber'      => $itemNumber,
                    'pricelist'       => $pricelist,
                    'priceDetailType' => $priceDetailType,
                    'quantityUnit'    => $quantityUnit,
                    'weightUnit'      => $weightUnit,
                    'exception'       => $e->getMessage()
                ]);
            }
        }

        return false;
    }

    public function deleteById(int $icpricpId)
    {
        if ($icpricp = $this->getById($icpricpId)) {
            try {
                $this->resourceModel->delete($icpricp);
                return true;
            } catch (Exception $e) {
                $this->log('deleteById()', [
                    'icpricpId' => $icpricpId,
                    'exception' => $e->getMessage()
                ]);
            }
        }

        return false;
    }

    /**
     * Validate ICPRICP record
     *
     * @param \ECInternet\Sage300Pricing\Api\Data\IcpricpInterface $icpricp
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function validate(
        IcpricpInterface $icpricp
    ) {
        if (empty($icpricp->getCurrencyCode())) {
            throw new LocalizedException(__('CurrencyCode not set'));
        }

        if (empty($icpricp->getItemNumber())) {
            throw new LocalizedException(__('ItemNumber not set'));
        }

        if (empty($icpricp->getPriceListCode())) {
            throw new LocalizedException(__('Pricelist not set'));
        }

        if (empty($icpricp->getPriceDetailType())) {
            throw new LocalizedException(__('PriceDetailType not set'));
        }

        if (empty($icpricp->getQuantityUnit() && empty($icpricp->getWeightUnit()))) {
            throw new LocalizedException(__("Either 'QTYUNIT' or 'WEIGHTUNIT' must be set to a value"));
        }

        if (!empty($icpricp->getQuantityUnit() && !empty($icpricp->getWeightUnit()))) {
            throw new LocalizedException(__("'QTYUNIT' and 'WEIGHTUNIT' cannot both be set to a value"));
        }
    }

    /**
     * Does ICPRICP record exist?
     *
     * @param \ECInternet\Sage300Pricing\Api\Data\IcpricpInterface $icpricp
     *
     * @return bool
     */
    protected function doesRecordExist(
        IcpricpInterface $icpricp
    ) {
        /*
        $this->log('doesRecordExist()', [
            Data\Icpricp::COLUMN_CURRENCY   => $icpricp->getCurrencyCode(),
            Data\Icpricp::COLUMN_ITEMNO     => $icpricp->getItemNumber(),
            Data\Icpricp::COLUMN_PRICELIST  => $icpricp->getPriceListCode(),
            Data\Icpricp::COLUMN_DPRICETYPE => $icpricp->getPriceDetailType(),
            Data\Icpricp::COLUMN_QTYUNIT    => $icpricp->getQuantityUnit(),
            Data\Icpricp::COLUMN_WEIGHTUNIT => $icpricp->getWeightUnit()
        ]);
        */

        /** @var \ECInternet\Sage300Pricing\Model\ResourceModel\Icpricp\Collection $collection */
        $collection = $this->icpricpCollectionFactory->create()
            ->addFieldToFilter(Data\Icpricp::COLUMN_CURRENCY, $icpricp->getCurrencyCode())
            ->addFieldToFilter(Data\Icpricp::COLUMN_ITEMNO, $icpricp->getItemNumber())
            ->addFieldToFilter(Data\Icpricp::COLUMN_PRICELIST, $icpricp->getPriceListCode())
            ->addFieldToFilter(Data\Icpricp::COLUMN_DPRICETYPE, $icpricp->getPriceDetailType())
            ->addFieldToFilter(
                [
                    Data\Icpricp::COLUMN_QTYUNIT,
                    Data\Icpricp::COLUMN_WEIGHTUNIT
                ],
                [
                    $icpricp->getQuantityUnit(),
                    $icpricp->getWeightUnit()
                ]
            );

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
        $this->logger->info('Model/IcpricpRepository - ' . $message, $extra);
    }
}
