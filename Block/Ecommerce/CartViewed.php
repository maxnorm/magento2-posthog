<?php

declare(strict_types=1);

namespace Indexa\Posthog\Block\Ecommerce;

use Indexa\Posthog\Helper\Data as PosthogHelper;
use Indexa\Posthog\Model\Ecommerce\EventDataBuilder;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\View\Element\Template;

class CartViewed extends Template
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
        return $this->posthogHelper->isConfigured() && $this->checkoutSession->getQuote()->getItemsCount() > 0;
    }

    /**
     * @return array{cart_id: string, products: array<int, array<string, mixed>>}
     */
    public function getCartViewedPayload(): array
    {
        $quote = $this->checkoutSession->getQuote();
        return [
            'cart_id' => (string) $quote->getId(),
            'products' => $this->eventDataBuilder->buildQuoteProducts($quote),
        ];
    }
}
