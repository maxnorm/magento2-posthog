<?php

declare(strict_types=1);

namespace Indexa\Posthog\Block\Ecommerce;

use Indexa\Posthog\Helper\Data as PosthogHelper;
use Indexa\Posthog\Model\Ecommerce\EventDataBuilder;
use Magento\Catalog\Block\Product\Context as ProductContext;
use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class ProductViewed extends Template
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
     * @var Registry
     */
    private $registry;

    /**
     * @param ProductContext $context
     * @param PosthogHelper $posthogHelper
     * @param EventDataBuilder $eventDataBuilder
     * @param array $data
     */
    public function __construct(
        ProductContext $context,
        PosthogHelper $posthogHelper,
        EventDataBuilder $eventDataBuilder,
        array $data = []
    ) {
        $this->posthogHelper = $posthogHelper;
        $this->eventDataBuilder = $eventDataBuilder;
        $this->registry = $context->getRegistry();
        parent::__construct($context, $data);
    }

    public function shouldRender(): bool
    {
        return $this->posthogHelper->isConfigured() && $this->getCurrentProduct() !== null;
    }

    public function getCurrentProduct(): ?Product
    {
        $product = $this->registry->registry('current_product');
        return $product instanceof Product ? $product : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function getProductViewedPayload(): array
    {
        $product = $this->getCurrentProduct();
        if (!$product) {
            return [];
        }
        return $this->eventDataBuilder->buildProductPayload($product, 1);
    }
}
