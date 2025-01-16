<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Modal;
use ECInternet\Sage300Pricing\Helper\Data;

/**
 * SagePricing data provider
 */
class SagePricing extends AbstractModifier
{
    const SAGEPRICING_MODAL_LINK     = 'sagepricing_modal_link';

    const SAGEPRICING_MODAL_INDEX    = 'sagepricing_modal';

    const PRICING_SECTION_NAME       = 'section';

    /**
     * @var \Magento\Catalog\Model\Locator\LocatorInterface
     */
    private $_locator;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $_urlBuilder;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    private $_layoutFactory;

    /**
     * @var array
     */
    private $_meta = [];

    /**
     * @var \ECInternet\Sage300Pricing\Helper\Data
     */
    private $_helper;

    /**
     * SagePricing constructor.
     *
     * @param \Magento\Catalog\Model\Locator\LocatorInterface $locator
     * @param \Magento\Framework\UrlInterface                 $urlBuilder
     * @param \Magento\Framework\View\LayoutFactory           $layoutFactory
     * @param \ECInternet\Sage300Pricing\Helper\Data          $helper
     */
    public function __construct(
        LocatorInterface $locator,
        UrlInterface $urlBuilder,
        LayoutFactory $layoutFactory,
        Data $helper
    ) {
        $this->_locator       = $locator;
        $this->_urlBuilder    = $urlBuilder;
        $this->_layoutFactory = $layoutFactory;
        $this->_helper        = $helper;
    }

    /**
     * @inheritDoc
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function modifyMeta(array $meta)
    {
        $this->_meta = $meta;

        if ($this->_helper->isModuleEnabled()) {
            $this->addSagePricingModal();
            $this->addSagePricingModalLink(50);
        }

        return $this->_meta;
    }

    /**
     * Add SagePricing modal
     *
     * @return void
     */
    private function addSagePricingModal()
    {
        $this->_meta = array_merge_recursive(
            $this->_meta,
            [
                static::SAGEPRICING_MODAL_INDEX => $this->getModalConfig(),
            ]
        );
    }

    /**
     * Get modal configuration
     *
     * @return array
     */
    private function getModalConfig()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Modal::NAME,
                        'dataScope'     => '',
                        'provider'      => static::FORM_NAME . '.product_form_data_source',
                        'ns'            => static::FORM_NAME,
                        'options'       => [
                            'title'   => __($this->getAdminPricingTitle()),
                            'buttons' => [
                                [
                                    'text'    => __('Close'),
                                    'class'   => 'action-primary', // additional class
                                    'actions' => [
                                        /*[
                                            'targetName' => 'index = product_form', // Element selector
                                            'actionName' => 'save', // Save parent form (product)
                                        ],*/
                                        'closeModal', // method name
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'children'  => [
                'content' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'autoRender'         => false,
                                'componentType'      => 'container',
                                'dataScope'          => 'data.product', // save data in the product data
                                'externalProvider'   => 'data.product_data_source',
                                'ns'                 => static::FORM_NAME,
                                'render_url'         => $this->_urlBuilder->getUrl('mui/index/render'),
                                'realTimeLink'       => true,
                                'behaviourType'      => 'edit',
                                'externalFilterMode' => true,
                                'currentProductId'   => $this->_locator->getProduct()->getId(),
                            ],
                        ],
                    ],
                    'children'  => [
                        'section' => $this->getPricingSection(10),
                    ],
                ],
            ],
        ];
    }

    /**
     * Get pricing section
     *
     * @param int $sortOrder
     *
     * @return array
     */
    private function getPricingSection(int $sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label'         => null,
                        'formElement'   => Container::NAME,
                        'componentType' => Container::NAME,
                        'template'      => 'ui/form/components/complex',
                        'sortOrder'     => $sortOrder,
                        'content'       => $this->_layoutFactory->create()->createBlock(
                            'ECInternet\Sage300Pricing\Block\Adminhtml\Sage300PricingData'
                        )->toHtml(),
                    ],
                ],
            ],
            'children'  => [],
        ];
    }

    /**
     * Add SagePricing modal link
     *
     * @param int $sortOrder
     */
    private function addSagePricingModalLink(int $sortOrder)
    {
        $this->_meta = array_replace_recursive(
            $this->_meta,
            [
                static::DEFAULT_GENERAL_PANEL => [
                    'children' => [
                        static::CONTAINER_PREFIX . ProductAttributeInterface::CODE_PRICE => [
                            'children' => [
                                static::SAGEPRICING_MODAL_LINK => [
                                    'arguments' => [
                                        'data' => [
                                            'config' => [
                                                'title'              => __($this->getAdminPricingTitle()),
                                                'formElement'        => Container::NAME,
                                                'componentType'      => Container::NAME,
                                                'component'          => 'Magento_Ui/js/form/components/button',
                                                'template'           => 'ui/form/components/button/container',
                                                'actions'            => [
                                                    [
                                                        'targetName' => 'ns=' . static::FORM_NAME . ', index='
                                                            . static::SAGEPRICING_MODAL_INDEX, // selector
                                                        'actionName' => 'openModal', // method name
                                                    ],
                                                ],
                                                'displayAsLink'      => true,
                                                'additionalForGroup' => true,
                                                'sortOrder'          => $sortOrder,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
    }

    private function getAdminPricingTitle()
    {
        $value = $this->_helper->getAdminPricingTitle();

        return empty($value) ? 'Sage 300 Pricing Data' : $value;
    }
}
