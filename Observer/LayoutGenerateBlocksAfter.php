<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use ECInternet\Sage300Pricing\Helper\Data;

/**
 * Observer for 'layout_generate_blocks_after' event
 */
class LayoutGenerateBlocksAfter implements ObserverInterface
{
    /**
     * @var \ECInternet\Sage300Pricing\Helper\Data
     */
    private $helper;

    /**
     * LayoutGenerateBlocksAfter constructor.
     *
     * @param \ECInternet\Sage300Pricing\Helper\Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Remove tier pricing block if we're showing our tier price block
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->helper->shouldShowTierPrices()) {
            if ($layout = $observer->getData('layout')) {
                $layout->unsetElement('product.price.tier');
            }
        }
    }
}
