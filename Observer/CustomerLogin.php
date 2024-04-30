<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use ECInternet\Sage300Pricing\Helper\Customer as CustomerHelper;
use ECInternet\Sage300Pricing\Logger\Logger;
use Exception;

/**
 * Observer for 'customer_login' event
 */
class CustomerLogin implements ObserverInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \ECInternet\Sage300Pricing\Logger\Logger
     */
    private $logger;

    /**
     * CustomerLogin constructor.
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \ECInternet\Sage300Pricing\Logger\Logger   $logger
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Logger $logger
    ) {
        $this->storeManager = $storeManager;
        $this->logger       = $logger;
    }

    /**
     * Set the store's currency code to that of the Customer
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     */
    public function execute(
        EventObserver $observer
    ) {
        $this->log('execute()');

        /** @var \Magento\Customer\Model\Customer $customer */
        if ($customer = $observer->getEvent()->getData('customer')) {
            // Get the customer's currency code.
            $currencyCode = $customer->getData(CustomerHelper::CUSTOMER_ATTRIBUTE_CURRENCY_CODE);
            if (!empty($currencyCode)) {
                $currencyCode = strtoupper(trim($currencyCode));

                // Set the current store to use the customer's currency code.
                try {
                    $this->storeManager->getStore()->setCurrentCurrencyCode($currencyCode);
                } catch (Exception $e) {
                    $this->log('execute() - Unable to set current currency code: ' . $e->getMessage());
                }
            }
        }

        return $this;
    }

    /**
     * Write to extension log
     *
     * @param string $message
     */
    private function log(string $message)
    {
        $this->logger->info('Observer/CustomerLogin - ' . $message);
    }
}
