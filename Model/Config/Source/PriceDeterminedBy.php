<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class PriceDeterminedBy implements OptionSourceInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 1,
                'label' => 'Customer Type'
            ],
            [
                'value' => 2,
                'label' => 'Volume Discounts'
            ]
        ];
    }
}
