<?php

/**
 * Copyright © Indexa. All rights reserved.
 */

declare(strict_types=1);

namespace Indexa\Posthog\Block\Ecommerce;

use Indexa\Posthog\Helper\Data as PosthogHelper;
use Magento\Framework\View\Element\Template;
use Magento\Search\Model\QueryFactory;

class ProductsSearched extends Template
{
    /**
     * @var PosthogHelper
     */
    private $posthogHelper;

    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @param Template\Context $context
     * @param PosthogHelper $posthogHelper
     * @param QueryFactory $queryFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        PosthogHelper $posthogHelper,
        QueryFactory $queryFactory,
        array $data = []
    ) {
        $this->posthogHelper = $posthogHelper;
        $this->queryFactory = $queryFactory;
        parent::__construct($context, $data);
    }

    public function shouldRender(): bool
    {
        return $this->posthogHelper->isConfigured() && $this->getSearchQuery() !== '';
    }

    /**
     * Search query text (raw, trimmed). Empty string when not on search or no query.
     */
    public function getSearchQuery(): string
    {
        try {
            $query = $this->queryFactory->get();
            $text = $query->getQueryText();
            return is_string($text) ? trim($text) : '';
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Payload for PostHog "Products Searched" (ecommerce spec: property "query").
     *
     * @return array<string, mixed>
     */
    public function getProductsSearchedPayload(): array
    {
        $query = $this->getSearchQuery();
        if ($query === '') {
            return [];
        }
        return ['query' => $query];
    }
}
