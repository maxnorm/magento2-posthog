<?php

/**
 * Copyright © Indexa. All rights reserved.
 */

declare(strict_types=1);

namespace Indexa\Posthog\Model\Ecommerce;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;

class EventDataBuilder
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlBuilder
     * @param ImageHelper $imageHelper
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder,
        ImageHelper $imageHelper,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        $this->imageHelper = $imageHelper;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Build Product Viewed / Product Added single product payload (PostHog spec).
     *
     * @param ProductInterface $product
     * @param float|int $quantity
     * @return array<string, mixed>
     */
    public function buildProductPayload(ProductInterface $product, $quantity = 1): array
    {
        $price = (float) ($product->getFinalPrice() ?? $product->getPrice() ?? 0);
        $qty = (int) $quantity;
        $value = round($price * $qty, 2);
        $categoryName = $this->getProductCategoryName($product);
        $url = $product->getProductUrl();
        $imageUrl = '';
        $currency = '';
        try {
            $currency = (string) $this->storeManager->getStore()->getCurrentCurrencyCode();
        } catch (\Throwable $e) {
            // ignore
        }
        try {
            $imageUrl = $this->imageHelper->getUrl($product, 'product_base_image');
        } catch (\Throwable $e) {
            // ignore
        }

        return array_filter([
            'product_id' => (string) $product->getId(),
            'sku' => (string) $product->getSku(),
            'category' => $categoryName,
            'name' => (string) $product->getName(),
            'brand' => $this->getBrand($product),
            'variant' => (string) ($product->getData('variant') ?? ''),
            'price' => round($price, 2),
            'quantity' => $qty,
            'position' => null,
            'value' => $value,
            'url' => $url ?: '',
            'image_url' => $imageUrl,
            'currency' => $currency,
        ], static function ($v) {
            return $v !== null && $v !== '';
        });
    }

    /**
     * Build single product payload for Product List Viewed (same as buildProductPayload with position).
     *
     * @param ProductInterface $product
     * @param int $position 1-based index in the list
     * @param float|int $quantity
     * @return array<string, mixed>
     */
    public function buildProductPayloadForList(ProductInterface $product, int $position, $quantity = 1): array
    {
        $payload = $this->buildProductPayload($product, $quantity);
        $payload['position'] = $position;
        return $payload;
    }

    /**
     * Build payload from a quote item (for Cart Viewed, Product Added, etc.).
     *
     * @param QuoteItem $item
     * @return array<string, mixed>
     */
    public function buildFromQuoteItem(QuoteItem $item): array
    {
        $product = $item->getProduct();
        if (!$product) {
            return [];
        }
        $payload = $this->buildProductPayload($product, (float) $item->getQty());
        $payload['cart_id'] = (string) $item->getQuoteId();
        return $payload;
    }

    /**
     * Build payload from an order item (for Order Completed, etc.).
     *
     * @param OrderItemInterface $item
     * @return array<string, mixed>
     */
    public function buildFromOrderItem(OrderItemInterface $item): array
    {
        $productId = $item->getProductId();
        $productOptions = $item->getProductOptions();
        $variant = '';
        if (is_array($productOptions) && isset($productOptions['attributes_info']) && is_array($productOptions['attributes_info'])) {
            $labels = array_column($productOptions['attributes_info'], 'label');
            $variant = implode(', ', array_map('strval', $labels));
        } elseif (is_array($productOptions) && isset($productOptions['attributes_info'])) {
            $variant = (string) $productOptions['attributes_info'];
        }
        $payload = [
            'product_id' => (string) ($productId ?? $item->getSku()),
            'sku' => (string) $item->getSku(),
            'category' => '',
            'name' => (string) $item->getName(),
            'brand' => '',
            'variant' => $variant,
            'price' => round((float) $item->getPrice(), 2),
            'quantity' => (int) $item->getQtyOrdered(),
            'position' => null,
            'url' => '',
            'image_url' => '',
        ];
        return array_filter($payload, static function ($v) {
            return $v !== null && $v !== '';
        });
    }

    /**
     * Build products array from order for Order Completed.
     *
     * @param Order $order
     * @return array<int, array<string, mixed>>
     */
    public function buildOrderProducts(Order $order): array
    {
        $products = [];
        foreach ($order->getAllVisibleItems() as $item) {
            $products[] = $this->buildFromOrderItem($item);
        }
        return $products;
    }

    /**
     * Build products array from quote for Cart Viewed / Checkout Started.
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return array<int, array<string, mixed>>
     */
    public function buildQuoteProducts(\Magento\Quote\Model\Quote $quote): array
    {
        $products = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            $products[] = $this->buildFromQuoteItem($item);
        }
        return $products;
    }

    private function getProductCategoryName(ProductInterface $product): string
    {
        if (!method_exists($product, 'getCategoryIds')) {
            return '';
        }
        $categoryIds = $product->getCategoryIds();
        if (empty($categoryIds) || !is_array($categoryIds)) {
            return '';
        }
        try {
            $categoryId = (int) reset($categoryIds);
            $category = $this->categoryRepository->get($categoryId);
            return (string) $category->getName();
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function getBrand(ProductInterface $product): string
    {
        $raw = $product->getData('brand');
        if ($raw === null || $raw === '' || $raw === false) {
            return '';
        }
        try {
            $text = $product->getAttributeText('brand');
            return $text ? (string) $text : (string) $raw;
        } catch (\Throwable $e) {
            return (string) $raw;
        }
    }
}
