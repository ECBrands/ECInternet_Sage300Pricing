<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use ECInternet\Sage300Pricing\Model\Config;

/**
 * Observer for 'layout_generate_blocks_after' event
 */
class LayoutGenerateBlocksAfter implements ObserverInterface
{
    /**
     * @var \ECInternet\Sage300Pricing\Model\Config
     */
    private $config;

    /**
     * LayoutGenerateBlocksAfter constructor.
     *
     * @param \ECInternet\Sage300Pricing\Model\Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Remove tier pricing block if we're showing our tier price block
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->config->shouldShowTierPrices()) {
            if ($layout = $observer->getData('layout')) {
                $layout->unsetElement('product.price.tier');
            }
        }
    }
}
