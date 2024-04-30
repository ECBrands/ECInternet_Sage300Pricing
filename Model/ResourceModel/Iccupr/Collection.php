<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Model\ResourceModel\Iccupr;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Iccupr Collection
 */
class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    protected $_eventPrefix = 'ecinternet_sage300pricing_iccupr_collection';

    protected $_eventObject = 'iccupr_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'ECInternet\Sage300Pricing\Model\Data\Iccupr',
            'ECInternet\Sage300Pricing\Model\ResourceModel\Iccupr'
        );
    }
}
