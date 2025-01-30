<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Controller\Adminhtml\Iccupr;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Adminhtml Iccupr controller
 */
class Index extends Action implements HttpGetActionInterface
{
    private const MENU_ID = 'ECInternet_Sage300Pricing::iccupr';

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Index constructor.
     *
     * @param \Magento\Backend\App\Action\Context        $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Load the page defined in view/adminhtml/layout/pricing_iccupr_index.xml
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        // Active menu
        $resultPage->setActiveMenu(static::MENU_ID);

        // Page title
        $resultPage->getConfig()->getTitle()->prepend(__('ICCUPR'));

        // Breadcrumbs
        $resultPage->addBreadcrumb(__('ECInternet'), __('ECInternet'));
        $resultPage->addBreadcrumb(__('Sage300Account'), __('ICCUPR'));

        return $resultPage;
    }
}
