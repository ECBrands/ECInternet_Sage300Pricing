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
use ECInternet\Sage300Pricing\Model\Config;

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
    private $locator;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var \ECInternet\Sage300Pricing\Model\Config
     */
    private $config;

    /**
     * @var array
     */
    private $meta = [];

    /**
     * SagePricing constructor.
     *
     * @param \Magento\Catalog\Model\Locator\LocatorInterface $locator
     * @param \Magento\Framework\UrlInterface                 $urlBuilder
     * @param \Magento\Framework\View\LayoutFactory           $layoutFactory
     * @param \ECInternet\Sage300Pricing\Model\Config         $config
     */
    public function __construct(
        LocatorInterface $locator,
        UrlInterface $urlBuilder,
        LayoutFactory $layoutFactory,
        Config $config
    ) {
        $this->locator       = $locator;
        $this->urlBuilder    = $urlBuilder;
        $this->layoutFactory = $layoutFactory;
        $this->config        = $config;
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
        $this->meta = $meta;

        if ($this->config->isModuleEnabled()) {
            $this->addSagePricingModal();
            $this->addSagePricingModalLink(50);
        }

        return $this->meta;
    }

    /**
     * Add SagePricing modal
     *
     * @return void
     */
    private function addSagePricingModal()
    {
        $this->meta = array_merge_recursive(
            $this->meta,
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
                                'render_url'         => $this->urlBuilder->getUrl('mui/index/render'),
                                'realTimeLink'       => true,
                                'behaviourType'      => 'edit',
                                'externalFilterMode' => true,
                                'currentProductId'   => $this->locator->getProduct()->getId(),
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
                        'content'       => $this->layoutFactory->create()->createBlock(
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
        $this->meta = array_replace_recursive(
            $this->meta,
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
        $value = $this->config->getAdminPricingTitle();

        return empty($value) ? 'Sage 300 Pricing Data' : $value;
    }
}
