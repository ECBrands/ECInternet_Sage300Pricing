<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use ECInternet\Sage300Pricing\Logger\Logger;
use Exception;

/**
 * Helper
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Data extends AbstractHelper
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
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context      $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \ECInternet\Sage300Pricing\Logger\Logger   $logger
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Logger $logger
    ) {
        parent::__construct($context);

        $this->storeManager = $storeManager;
        $this->logger       = $logger;
    }

    /**
     * Get the currency code for the current store
     *
     * @return string|null
     */
    public function getCurrentStoreCurrencyCode()
    {
        try {
            return $this->storeManager->getStore()->getCurrentCurrency()->getCode();
        } catch (Exception $e) {
            $this->log('getCurrentStoreCurrencyCode()', ['exception' => $e->getMessage()]);
        }

        return null;
    }

    private function log(string $message, array $extra = [])
    {
        $this->logger->info('Helper/Data - ' . $message, $extra);
    }
}
