<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class PriceDetailType implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 1,
                'label' => 'Base Price Quantity'
            ],
            [
                'value' => 2,
                'label' => 'Sale Price Quantity'
            ],
            [
                'value' => 3,
                'label' => 'Base Price Weight'
            ],
            [
                'value' => 4,
                'label' => 'Sale Price Weight'
            ],
            [
                'value' => 5,
                'label' => 'Base Price Using Cost'
            ],
            [
                'value' => 6,
                'label' => 'Sale Price Using Cost'
            ]
        ];
    }
}
