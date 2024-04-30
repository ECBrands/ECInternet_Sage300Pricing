<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Plugin\Magento\Framework\Webapi\Validator;

use Magento\Framework\Webapi\Validator\EntityArrayValidator;

class EntityArrayValidatorPlugin
{
    private $allowedClasses = [
        '\ECInternet\Sage300Pricing\Api\Data\IccuprInterface',
        '\ECInternet\Sage300Pricing\Api\Data\IcpricInterface',
        '\ECInternet\Sage300Pricing\Api\Data\IcpricpInterface'
    ];

    /**
     * Skip complex array-type validation for our custom interfaces
     *
     * @param EntityArrayValidator $subject
     * @param callable             $proceed
     * @param string               $className
     * @param array                $items
     *
     * @return void
     */
    public function aroundValidateComplexArrayType(
        /* @noinspection PhpUnusedParameterInspection */ EntityArrayValidator $subject,
        callable $proceed,
        string $className,
        array $items
    ) {
        if (!in_array($className, $this->allowedClasses)) {
            $proceed($className, $items);
        }
    }
}
