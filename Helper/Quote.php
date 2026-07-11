<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Sage300Pricing\Helper;

use Magento\Backend\Model\Session\Quote as QuoteSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use ECInternet\Sage300Pricing\Logger\Logger;
use Exception;

class Quote extends AbstractHelper
{
    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    private $quoteSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \ECInternet\Sage300Pricing\Logger\Logger
     */
    private $logger;

    public function __construct(
        Context $context,
        QuoteSession $quoteSession,
        CheckoutSession $checkoutSession,
        Logger $logger
    ) {
        parent::__construct($context);

        $this->quoteSession    = $quoteSession;
        $this->checkoutSession = $checkoutSession;
        $this->logger          = $logger;
    }

    /**
     * Get count of current CartItemInterface objects in cart by sku
     *
     * @param string $sku
     *
     * @return float
     */
    public function getCurrentItemQuantity(string $sku)
    {
        $this->log('getCurrentItemQuantity()', ['sku' => $sku]);

        /** @var \Magento\Quote\Model\Quote $quote */
        if ($quote = $this->getCurrentQuote()) {
            $this->log('getCurrentItemQuantity()', ['quoteId' => $quote->getId()]);

            /** @var \Magento\Quote\Api\Data\CartItemInterface[]|null $cartItems */
            if ($cartItems = $quote->getItems()) {
                if (is_array($cartItems) || is_object($cartItems)) {
                    foreach ($cartItems as $cartItem) {
                        if ($cartItem->getSku() == $sku) {
                            return $cartItem->getQty();
                        }
                    }
                }
            }
        }

        return 0.0;
    }

    /**
     * Get the current Quote from QuoteSession or CheckoutSession
     *
     * @return \Magento\Quote\Model\Quote|null
     */
    private function getCurrentQuote()
    {
        if ($quote = $this->getCurrentFrontendQuote()) {
            if ($quote->getId()) {
                return $quote;
            } else {
                $this->log('getCurrentQuote() - Frontend quote did not have Id, testing backend quote...');
            }
        } else {
            $this->log('getCurrentQuote() - Unable to load frontend, testing backend quote...');
        }

        if ($quote = $this->getCurrentBackendQuote()) {
            if ($quote->getId()) {
                return $quote;
            } else {
                $this->log('getCurrentQuote() - Backend quote did not have Id, returning null.');
            }
        } else {
            $this->log('getCurrentQuote() - Unable to load backend quote, returning null.');
        }

        return null;
    }

    /**
     * Get checkout quote instance by current session
     *
     * @return \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote|null
     */
    private function getCurrentFrontendQuote()
    {
        try {
            return $this->checkoutSession->getQuote();
        } catch (Exception $e) {
            $this->log('getCurrentFrontendQuote()', ['exception' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Retrieve quote model object
     *
     * @return \Magento\Quote\Model\Quote
     */
    private function getCurrentBackendQuote()
    {
        return $this->quoteSession->getQuote();
    }

    private function log(string $message, array $extra = [])
    {
        $this->logger->info('Helper/Quote - ' . $message, $extra);
    }
}
