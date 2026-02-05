<?php

declare(strict_types=1);

namespace Indexa\Posthog\Block\Ecommerce;

use Indexa\Posthog\Helper\Data as PosthogHelper;
use Indexa\Posthog\Model\Ecommerce\EventDataBuilder;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\View\Element\Template;

class CheckoutStarted extends Template
{
    /**
     * @var PosthogHelper
     */
    private $posthogHelper;

    /**
     * @var EventDataBuilder
     */
    private $eventDataBuilder;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @param Template\Context $context
     * @param PosthogHelper $posthogHelper
     * @param EventDataBuilder $eventDataBuilder
     * @param CheckoutSession $checkoutSession
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        PosthogHelper $posthogHelper,
        EventDataBuilder $eventDataBuilder,
        CheckoutSession $checkoutSession,
        array $data = []
    ) {
        $this->posthogHelper = $posthogHelper;
        $this->eventDataBuilder = $eventDataBuilder;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context, $data);
    }

    public function shouldRender(): bool
    {
        $quote = $this->checkoutSession->getQuote();
        return $this->posthogHelper->isConfigured() && $quote->getItemsCount() > 0;
    }

    /**
     * @return array<string, mixed>
     */
    public function getCheckoutStartedPayload(): array
    {
        $quote = $this->checkoutSession->getQuote();
        $totals = $quote->getTotals();
        $grandTotal = $totals['grand_total'] ?? null;
        $subtotal = $totals['subtotal'] ?? null;
        $value = $grandTotal ? (float) $grandTotal->getValue() : ($subtotal ? (float) $subtotal->getValue() : 0);
        $shipping = isset($totals['shipping']) ? (float) $totals['shipping']->getValue() : 0;
        $tax = isset($totals['tax']) ? (float) $totals['tax']->getValue() : 0;
        $discount = 0;
        if (isset($totals['discount'])) {
            $discount = abs((float) $totals['discount']->getValue());
        }
        $payload = [
            'order_id' => (string) $quote->getId(),
            'affiliation' => $this->_storeManager->getStore()->getName(),
            'value' => round($value, 2),
            'revenue' => round($value, 2),
            'currency' => $quote->getQuoteCurrencyCode() ?: $this->_storeManager->getStore()->getCurrentCurrencyCode(),
            'products' => $this->eventDataBuilder->buildQuoteProducts($quote),
        ];
        if ($shipping > 0) {
            $payload['shipping'] = round($shipping, 2);
        }
        if ($tax > 0) {
            $payload['tax'] = round($tax, 2);
        }
        if ($discount > 0) {
            $payload['discount'] = round($discount, 2);
        }
        $coupon = $quote->getCouponCode();
        if ($coupon !== null && $coupon !== '') {
            $payload['coupon'] = (string) $coupon;
        }
        return $payload;
    }
}
