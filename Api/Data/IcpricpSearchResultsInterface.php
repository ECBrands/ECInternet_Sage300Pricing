<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Api\Data;

interface IcpricpSearchResultsInterface
{
    /**
     * Get items
     *
     * @return \ECInternet\Sage300Pricing\Api\Data\IcpricpInterface[]
     */
    public function getItems();

    /**
     * Set items
     *
     * @param \ECInternet\Sage300Pricing\Api\Data\IcpricpInterface[] $items
     *
     * @return void
     */
    public function setItems(array $items);
}
