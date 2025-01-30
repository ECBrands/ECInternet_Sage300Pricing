<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Plugin\Magento\ConfigurableProduct\Block\Product\View\Type;

use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable;
use Magento\Customer\Model\Session as CustomerSession;

class ConfigurablePlugin
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    public function __construct(
        CustomerSession $customerSession
    ) {
        $this->customerSession = $customerSession;
    }

    /**
     * @param \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject
     * @param array                                                             $result
     *
     * @return array
     */
    public function afterGetCacheKeyInfo(Configurable $subject, array $result): array
    {
        $result[] = $this->customerSession->getCustomerId();

        return $result;
    }
}
