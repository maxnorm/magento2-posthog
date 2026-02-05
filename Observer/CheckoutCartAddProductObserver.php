<?php

declare(strict_types=1);

namespace Indexa\Posthog\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Indexa\Posthog\Helper\Data as PosthogHelper;
use Indexa\Posthog\Model\Ecommerce\EventDataBuilder;

class CheckoutCartAddProductObserver implements ObserverInterface
{
    public const SESSION_KEY_PRODUCT_ADDED = 'indexa_posthog_product_added';

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
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        if (!$this->posthogHelper->isConfigured()) {
            return;
        }

        $product = $observer->getEvent()->getData('product');
        $request = $observer->getEvent()->getData('request');
        if (!$product || !$request) {
            return;
        }

        $params = $request->getParams();
        $qty = 1;
        if (isset($params['qty']) && is_numeric($params['qty'])) {
            $qty = (float) $params['qty'];
        }

        $payload = $this->eventDataBuilder->buildProductPayload($product, $qty);
        $payload['cart_id'] = (string) $this->checkoutSession->getQuoteId();
        $this->checkoutSession->setData(self::SESSION_KEY_PRODUCT_ADDED, $payload);
    }
}
