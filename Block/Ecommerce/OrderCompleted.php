<?php

declare(strict_types=1);

namespace Indexa\Posthog\Block\Ecommerce;

use Indexa\Posthog\Helper\Data as PosthogHelper;
use Indexa\Posthog\Model\Ecommerce\EventDataBuilder;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\Order;

class OrderCompleted extends Template
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

    /**
     * Disable block cache for success page (order is session-specific).
     *
     * @return int|false
     */
    public function getCacheLifetime()
    {
        return false;
    }

    public function shouldRender(): bool
    {
        if (!$this->posthogHelper->isConfigured()) {
            return false;
        }
        return $this->getOrder() !== null;
    }

    public function getOrder(): ?Order
    {
        $order = $this->checkoutSession->getLastRealOrder();
        return ($order && $order->getId()) ? $order : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOrderCompletedPayload(): array
    {
        $order = $this->getOrder();
        if (!$order) {
            return [];
        }
        return [
            'checkout_id' => (string) $order->getQuoteId(),
            'order_id' => (string) $order->getIncrementId(),
            'affiliation' => $order->getStore()->getName(),
            'total' => round((float) $order->getGrandTotal(), 2),
            'subtotal' => round((float) $order->getSubtotal(), 2),
            'revenue' => round((float) $order->getSubtotal(), 2),
            'shipping' => round((float) $order->getShippingAmount(), 2),
            'tax' => round((float) $order->getTaxAmount(), 2),
            'discount' => round(abs((float) $order->getDiscountAmount()), 2),
            'coupon' => (string) $order->getCouponCode(),
            'currency' => (string) $order->getOrderCurrencyCode(),
            'products' => $this->eventDataBuilder->buildOrderProducts($order),
        ];
    }
}
