<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use ECInternet\Sage300Pricing\Model\Data\Iccupr;
use ECInternet\Sage300Pricing\Model\Data\Icpric;
use ECInternet\Sage300Pricing\Model\Data\Icpricp;
use ECInternet\Sage300Pricing\Model\ResourceModel\Iccupr\CollectionFactory as IccuprCollectionFactory;
use ECInternet\Sage300Pricing\Model\ResourceModel\Icpric\CollectionFactory as IcpricCollectionFactory;
use ECInternet\Sage300Pricing\Model\ResourceModel\Icpricp\CollectionFactory as IcpricpCollectionFactory;

/**
 * Sage300PricingData adminhtml block
 */
class Sage300PricingData extends Template
{
    /**
     * @var string
     */
    protected $_template = 'ECInternet_Sage300Pricing::product/sage300PricingData.phtml';

    /**
     * @var \Magento\Framework\Registry
     */
    private $_coreRegistry;

    /**
     * @var \ECInternet\Sage300Pricing\Model\ResourceModel\Iccupr\CollectionFactory
     */
    private $_iccuprCollectionFactory;

    /**
     * @var \ECInternet\Sage300Pricing\Model\ResourceModel\Icpric\CollectionFactory
     */
    private $_icpricCollectionFactory;

    /**
     * @var \ECInternet\Sage300Pricing\Model\ResourceModel\Icpricp\CollectionFactory
     */
    private $_icpricpCollectionFactory;

    /**
     * Sage300PricingData constructor.
     *
     * @param \Magento\Backend\Block\Template\Context                                  $context
     * @param \ECInternet\Sage300Pricing\Model\ResourceModel\Iccupr\CollectionFactory  $iccuprCollectionFactory
     * @param \ECInternet\Sage300Pricing\Model\ResourceModel\Icpric\CollectionFactory  $icpricCollectionFactory
     * @param \ECInternet\Sage300Pricing\Model\ResourceModel\Icpricp\CollectionFactory $icpricpCollectionFactory
     * @param \Magento\Framework\Registry                                              $registry
     */
    public function __construct(
        Context $context,
        IccuprCollectionFactory $iccuprCollectionFactory,
        IcpricCollectionFactory $icpricCollectionFactory,
        IcpricpCollectionFactory $icpricpCollectionFactory,
        Registry $registry
    ) {
        $this->_iccuprCollectionFactory  = $iccuprCollectionFactory;
        $this->_icpricCollectionFactory  = $icpricCollectionFactory;
        $this->_icpricpCollectionFactory = $icpricpCollectionFactory;
        $this->_coreRegistry             = $registry;

        parent::__construct($context);
    }

    /**
     * Get Iccupr collection
     *
     * @return \ECInternet\Sage300Pricing\Model\ResourceModel\Iccupr\Collection
     */
    public function getIccuprCollection()
    {
        return $this->_iccuprCollectionFactory->create()
            ->addFieldToFilter(Iccupr::COLUMN_ITEMNO, $this->getSku());
    }

    /**
     * Get Icpric collection
     *
     * @return \ECInternet\Sage300Pricing\Model\ResourceModel\Icpric\Collection
     */
    public function getIcpricCollection()
    {
        return $this->_icpricCollectionFactory->create()
            ->addFieldToFilter(Icpric::COLUMN_ITEMNO, $this->getSku());
    }

    /**
     * Get Icpricp collection
     *
     * @return \ECInternet\Sage300Pricing\Model\ResourceModel\Icpricp\Collection
     */
    public function getIcpricpCollection()
    {
        return $this->_icpricpCollectionFactory->create()
            ->addFieldToFilter(Icpricp::COLUMN_ITEMNO, $this->getSku());
    }

    /**
     * Get product sku
     *
     * @return string
     */
    private function getSku()
    {
        return $this->getProduct()->getSku();
    }

    /**
     * Retrieve currently edited product object.
     *
     * @return \Magento\Catalog\Model\Product
     */
    private function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }
}
