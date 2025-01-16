<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Icpric resource model
 */
class Icpric extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('ecinternet_sage300pricing_icpric', 'entity_id');
    }
}
