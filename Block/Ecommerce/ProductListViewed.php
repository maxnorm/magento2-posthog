<?php

/**
 * Copyright © Indexa. All rights reserved.
 */

declare(strict_types=1);

namespace Indexa\Posthog\Block\Ecommerce;

use Indexa\Posthog\Helper\Data as PosthogHelper;
use Indexa\Posthog\Model\Ecommerce\EventDataBuilder;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Category;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Search\Model\QueryFactory;

class ProductListViewed extends Template
{
    private const LIST_VIEW_MAX_PRODUCTS = 50;

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
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @param Template\Context $context
     * @param PosthogHelper $posthogHelper
     * @param EventDataBuilder $eventDataBuilder
     * @param Registry $registry
     * @param QueryFactory $queryFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        PosthogHelper $posthogHelper,
        EventDataBuilder $eventDataBuilder,
        Registry $registry,
        QueryFactory $queryFactory,
        array $data = []
    ) {
        $this->posthogHelper = $posthogHelper;
        $this->eventDataBuilder = $eventDataBuilder;
        $this->registry = $registry;
        $this->queryFactory = $queryFactory;
        parent::__construct($context, $data);
    }

    public function shouldRender(): bool
    {
        if (!$this->posthogHelper->isConfigured()) {
            return false;
        }
        $payload = $this->getProductListViewedPayload();
        return !empty($payload['products']);
    }

    /**
     * Payload for PostHog "Product List Viewed" (list_id, category, products[]).
     *
     * @return array{list_id: string, category: string, products: array<int, array<string, mixed>>}
     */
    public function getProductListViewedPayload(): array
    {
        $listBlock = $this->getListBlock();
        if ($listBlock === null) {
            return ['list_id' => '', 'category' => '', 'products' => []];
        }

        $collection = $listBlock->getLoadedProductCollection();
        if ($collection === null || $collection->count() === 0) {
            return ['list_id' => '', 'category' => '', 'products' => []];
        }

        $listId = '';
        $categoryName = '';

        $currentCategory = $this->registry->registry('current_category');
        if ($currentCategory instanceof Category) {
            $listId = (string) $currentCategory->getId();
            $categoryName = (string) $currentCategory->getName();
        } else {
            $listId = 'search';
            $categoryName = 'Search';
            try {
                $query = $this->queryFactory->get();
                $queryText = $query->getQueryText();
                if (is_string($queryText) && trim($queryText) !== '') {
                    $categoryName = trim($queryText);
                }
            } catch (\Throwable $e) {
                // keep "Search"
            }
        }

        $products = [];
        $position = 0;
        $limit = self::LIST_VIEW_MAX_PRODUCTS;

        foreach ($collection as $product) {
            if ($position >= $limit) {
                break;
            }
            $position++;
            $item = $this->eventDataBuilder->buildProductPayloadForList($product, $position);
            if (!empty($item)) {
                $products[] = $item;
            }
        }

        return [
            'list_id' => $listId,
            'category' => $categoryName,
            'products' => $products,
        ];
    }

    /**
     * Get the product list block (category or search). Null if not on a list page.
     */
    private function getListBlock(): ?ListProduct
    {
        $layout = $this->getLayout();
        if ($layout === null) {
            return null;
        }

        $currentCategory = $this->registry->registry('current_category');
        if ($currentCategory instanceof Category) {
            $block = $layout->getBlock('category.products.list');
            return $block instanceof ListProduct ? $block : null;
        }

        $block = $layout->getBlock('search_result_list');
        return $block instanceof ListProduct ? $block : null;
    }
}
