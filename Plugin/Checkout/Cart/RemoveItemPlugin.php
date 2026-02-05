<?php

declare(strict_types=1);

namespace Indexa\Posthog\Plugin\Checkout\Cart;

use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session as CheckoutSession;
use Indexa\Posthog\Helper\Data as PosthogHelper;
use Indexa\Posthog\Model\Ecommerce\EventDataBuilder;

class RemoveItemPlugin
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
     * @param PosthogHelper $posthogHelper
     * @param EventDataBuilder $eventDataBuilder
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        PosthogHelper $posthogHelper,
        EventDataBuilder $eventDataBuilder,
        CheckoutSession $checkoutSession
    ) {
        $this->posthogHelper = $posthogHelper;
        $this->eventDataBuilder = $eventDataBuilder;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Before removeItem: capture the item for Product Removed event.
     *
     * @param Cart $subject
     * @param int $itemId
     * @return array
     */
    public function beforeRemoveItem(Cart $subject, $itemId): array
    {
        if (!$this->posthogHelper->isConfigured()) {
            return [$itemId];
        }

        $quote = $subject->getQuote();
        $item = $quote->getItemById((int) $itemId);
        if ($item && $item->getProduct()) {
            $payload = $this->eventDataBuilder->buildFromQuoteItem($item);
            $payload['cart_id'] = (string) $quote->getId();
            $this->checkoutSession->setData(\Indexa\Posthog\Block\Ecommerce\SessionEvents::SESSION_KEY_PRODUCT_REMOVED, $payload);
        }

        return [$itemId];
    }
}
