<?php

declare(strict_types=1);

namespace Indexa\Posthog\Block\Ecommerce;

use Indexa\Posthog\Helper\Data as PosthogHelper;
use Indexa\Posthog\Observer\CheckoutCartAddProductObserver;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\View\Element\Template;

class SessionEvents extends Template
{
    /**
     * Session key for product removed (set by observer).
     */
    public const SESSION_KEY_PRODUCT_REMOVED = 'indexa_posthog_product_removed';

    /**
     * @var PosthogHelper
     */
    private $posthogHelper;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @param Template\Context $context
     * @param PosthogHelper $posthogHelper
     * @param CheckoutSession $checkoutSession
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        PosthogHelper $posthogHelper,
        CheckoutSession $checkoutSession,
        array $data = []
    ) {
        $this->posthogHelper = $posthogHelper;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context, $data);
    }

    public function shouldRender(): bool
    {
        if (!$this->posthogHelper->isConfigured()) {
            return false;
        }
        $hasAdded = $this->checkoutSession->getData(CheckoutCartAddProductObserver::SESSION_KEY_PRODUCT_ADDED);
        $hasRemoved = $this->checkoutSession->getData(self::SESSION_KEY_PRODUCT_REMOVED);
        return is_array($hasAdded) || is_array($hasRemoved);
    }

    /**
     * Get and clear Product Added payload from session.
     *
     * @return array<string, mixed>|null
     */
    public function getProductAddedPayload(): ?array
    {
        $payload = $this->checkoutSession->getData(CheckoutCartAddProductObserver::SESSION_KEY_PRODUCT_ADDED);
        $this->checkoutSession->unsetData(CheckoutCartAddProductObserver::SESSION_KEY_PRODUCT_ADDED);
        return is_array($payload) ? $payload : null;
    }

    /**
     * Get and clear Product Removed payload from session.
     *
     * @return array<string, mixed>|null
     */
    public function getProductRemovedPayload(): ?array
    {
        $payload = $this->checkoutSession->getData(self::SESSION_KEY_PRODUCT_REMOVED);
        $this->checkoutSession->unsetData(self::SESSION_KEY_PRODUCT_REMOVED);
        return is_array($payload) ? $payload : null;
    }
}
