<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Helper;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use ECInternet\Sage300Pricing\Logger\Logger;
use Exception;

class Customer extends AbstractHelper
{
    const CONFIG_PATH_GUEST_PRICEGROUP       = 'sage300pricing/group_prices/guest_pricegroup';

    const CUSTOMER_ATTRIBUTE_CUSTOMER_NUMBER = 'customer_number';

    const CUSTOMER_ATTRIBUTE_CUSTOMER_TYPE   = 'customer_type';

    const CUSTOMER_ATTRIBUTE_CURRENCY_CODE   = 'currency_code';

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    private $customerGroupRepository;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $customerSessionFactory;

    /**
     * @var \ECInternet\Sage300Pricing\Logger\Logger
     */
    private $logger;

    public function __construct(
        Context $context,
        AddressRepositoryInterface $addressRepository,
        CustomerRepositoryInterface $customerRepository,
        GroupRepositoryInterface $groupRepository,
        CustomerSessionFactory $customerSessionFactory,
        Logger $logger
    ) {
        parent::__construct($context);

        $this->addressRepository       = $addressRepository;
        $this->customerRepository      = $customerRepository;
        $this->customerGroupRepository = $groupRepository;
        $this->customerSessionFactory  = $customerSessionFactory;
        $this->logger                  = $logger;
    }

    /**
     * Get the default group code
     *
     * @return string|null
     */
    public function getDefaultGroupCodeByCurrency()
    {
        $this->log('getDefaultGroupCodeByCurrency()');

        //EC Internet: Intentionally a STUB, this is to be overridden by a client specific module.
        return null;
    }

    /**
     * Can the current customer change the site currency?
     *
     * @return bool
     */
    public function canChangeSiteCurrency()
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $this->getSessionCustomer();

        // Don't allow customers to change their currency if they already have one defined.
        return !$customer->hasData(self::CUSTOMER_ATTRIBUTE_CURRENCY_CODE);
    }

    public function getSessionCustomerInterface()
    {
        if ($sessionCustomer = $this->getSessionCustomer()) {
            if ($sessionCustomerId = $sessionCustomer->getId()) {
                if (is_numeric($sessionCustomerId)) {
                    if ($customer = $this->getCustomerById((int)$sessionCustomerId)) {
                        return $customer;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Get 'customer_number' value
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return mixed|null
     */
    public function getCustomerNumber(
        CustomerInterface $customer
    ) {
        return $this->getCustomerAttributeValue($customer, self::CUSTOMER_ATTRIBUTE_CUSTOMER_NUMBER);
    }

    /**
     * Get 'customer_type' value
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return mixed|null
     */
    public function getCustomerType(
        CustomerInterface $customer
    ) {
        return $this->getCustomerAttributeValue($customer, self::CUSTOMER_ATTRIBUTE_CUSTOMER_TYPE);
    }

    /**
     * Get Customer by id
     *
     * @param int $customerId
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    public function getCustomerById(int $customerId)
    {
        try {
            return $this->customerRepository->getById($customerId);
        } catch (Exception $e) {
            $this->log('getCustomerById()', ['customerId' => $customerId, 'exception' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Get CustomerGroup code for Customer
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return string|null
     */
    public function getCustomerGroupCode(
        CustomerInterface $customer
    ) {
        $customerGroupId = $this->getCustomerGroupId($customer);
        if (is_numeric($customerGroupId)) {
            $customerGroup = $this->getCustomerGroupById((int)$customerGroupId);
            if ($customerGroup !== null) {
                return $customerGroup->getCode();
            }
        }

        return null;
    }

    /**
     * Get default shipping address of Customer
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return \Magento\Customer\Api\Data\AddressInterface|null
     */
    public function getCustomerDefaultShippingAddress(
        CustomerInterface $customer
    ) {
        if ($shippingAddressId = $customer->getDefaultShipping()) {
            try {
                return $this->addressRepository->getById($shippingAddressId);
            } catch (LocalizedException $e) {
                $this->log("getDefaultShippingAddress() - Cannot lookup address [$shippingAddressId] - {$e->getMessage()}");
            }
        }

        return null;
    }

    /**
     * Get Customer attribute value
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param string                                       $attributeCode
     *
     * @return mixed|null
     */
    private function getCustomerAttributeValue(
        CustomerInterface $customer,
        string $attributeCode
    ) {
        if ($attribute = $customer->getCustomAttribute($attributeCode)) {
            if ($value = $attribute->getValue()) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Get CustomerGroup id
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return int
     */
    private function getCustomerGroupId(
        CustomerInterface $customer
    ) {
        // Null customerId check
        if ($customer->getId() === null) {
            $this->log('getCustomerGroupId() - Null CustomerId, using GuestPriceId...');

            return $this->getGuestPriceGroupId();
        }

        // Null customerGroupId check
        $customerGroupId = $customer->getGroupId();
        if ($customerGroupId === null) {
            $this->log('getCustomerGroupId() - Null CustomerGroupId, using GuestPriceId...');

            return $this->getGuestPriceGroupId();
        }

        return $customerGroupId;
    }

    /**
     * Get CustomerGroup id for guest
     *
     * @return mixed
     */
    private function getGuestPriceGroupId()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_GUEST_PRICEGROUP);
    }

    /**
     * Retrieve CustomerGroup by ID
     *
     * @param int $customerGroupId
     *
     * @return \Magento\Customer\Api\Data\GroupInterface|null
     */
    private function getCustomerGroupById(int $customerGroupId)
    {
        try {
            return $this->customerGroupRepository->getById($customerGroupId);
        } catch (Exception $e) {
            $this->log('getCustomerGroupById()', [
                'customerGroupId' => $customerGroupId,
                'exception'       => $e->getMessage()
            ]);
        }

        return null;
    }

    /**
     * Retrieve customer model object
     *
     * @return \Magento\Customer\Model\Customer
     */
    public function getSessionCustomer()
    {
        return $this->getCustomerSession()->getCustomer();
    }

    /**
     * Get Customer session
     *
     * @return \Magento\Customer\Model\Session
     */
    private function getCustomerSession()
    {
        return $this->customerSessionFactory->create();
    }

    private function log(string $message, array $extra = [])
    {
        $this->logger->info('Helper/Customer - ' . $message, $extra);
    }
}
