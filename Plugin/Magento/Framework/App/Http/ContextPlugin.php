<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Plugin\Magento\Framework\App\Http;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;
use Magento\Framework\App\Http\Context;
use ECInternet\Sage300Pricing\Logger\Logger;
use Exception;

/**
 * Plugin for Magento\Framework\App\Http\Context
 */
class ContextPlugin
{
    private const CUSTOMER_ATTRIBUTE_CUSTOMER_NUMBER = 'customer_number';

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $customerSessionFactory;

    /**
     * @var \ECInternet\Sage300Pricing\Logger\Logger
     */
    private $logger;

    /**
     * ContextPlugin constructor.
     *
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Model\SessionFactory            $customerSessionFactory
     * @param \ECInternet\Sage300Pricing\Logger\Logger          $logger
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CustomerSessionFactory $customerSessionFactory,
        Logger $logger
    ) {
        $this->customerRepository     = $customerRepository;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->logger                 = $logger;
    }

    public function beforeGetVaryString(Context $subject)
    {
        if ($customerNumber = $this->getCustomerNumber()) {
            $this->log('beforeGetVaryString()', ['customer_number' => $customerNumber]);

            $subject->setValue('CONTEXT_CUSTOMER_NUMBER', $customerNumber, 0);
        }
    }

    /**
     * Get 'customer_number' attribute value
     *
     * @return string|null
     */
    private function getCustomerNumber()
    {
        if ($customer = $this->getCustomer()) {
            if ($customerNumberAttribute = $customer->getCustomAttribute(self::CUSTOMER_ATTRIBUTE_CUSTOMER_NUMBER)) {
                return (string)$customerNumberAttribute->getValue();
            }
        }

        return null;
    }

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    private function getCustomer()
    {
        if ($customerId = $this->customerSessionFactory->create()->getCustomerId()) {
            try {
                return $this->customerRepository->getById($customerId);
            } catch (Exception $e) {
                $this->log('getCustomer()', [
                    'customerId' => $customerId,
                    'exception'  => $e->getMessage()
                ]);
            }
        } else {
            $this->log('getCustomer() - customerSession->getCustomerId() is null');
        }

        return null;
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
        $this->logger->info('Plugin/Magento/Framework/App/Http/ContextPlugin - ' . $message, $extra);
    }
}
