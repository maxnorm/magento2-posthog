PostHog Event reference

This document lists every event sent by **Indexa_Posthog** and all properties included in each. Use it for building PostHog insights, filters, breakdowns, and dashboards.

Event names and property names match [PostHog’s ecommerce events spec](https://posthog.com/docs/data/event-spec/ecommerce-events) where applicable.

---

## Events overview

| Event name | Page / trigger | Main use |
|------------|----------------|----------|
| Products Searched | Search results | Search analytics, intent |
| Product List Viewed | Category or search listing | List → view → add funnel, top products by list |
| Product Viewed | Product detail page | PDP engagement, product performance |
| Product Added | After add to cart | Add-to-cart rate, product affinity |
| Product Removed | After remove from cart | Cart abandonment, product issues |
| Cart Viewed | Cart page | Cart engagement |
| Checkout Started | First checkout step | Checkout funnel start |
| Order Completed | Order success page | Revenue, conversion, order-level analytics |

---

## Products Searched

**When:** User lands on the catalog search result page (with a search query).

| Property | Type | Description |
|----------|------|-------------|
| `query` | string | Search query text (trimmed). |

**Example:** `{ "query": "running shoes" }`

---

## Product List Viewed

**When:** User lands on a category listing page or search result listing (with products).

| Property | Type | Description |
|----------|------|-------------|
| `list_id` | string | Category ID, or `"search"` for search results. |
| `category` | string | Category name, or search query for search results. |
| `products` | array | List of product objects (see [Product object](#product-object) below). Up to 50 products; each includes `position` (1-based). |

**Example:** `{ "list_id": "42", "category": "Running", "products": [ { "product_id": "101", "sku": "RUN-01", "name": "...", "price": 99.99, "position": 1, ... }, ... ] }`

---

## Product Viewed

**When:** User views a product detail page.

| Property | Type | Description |
|----------|------|-------------|
| All [product object](#product-object) properties | — | Single product; `quantity` is 1. |

**Example:** `{ "product_id": "101", "sku": "RUN-01", "category": "Running", "name": "...", "brand": "...", "variant": "...", "price": 99.99, "quantity": 1, "value": 99.99, "url": "...", "image_url": "...", "currency": "USD" }`

---

## Product Added

**When:** User adds a product to the cart (after the action completes).

| Property | Type | Description |
|----------|------|-------------|
| All [product object](#product-object) properties | — | Product and quantity added. |
| `cart_id` | string | Quote ID (cart identifier). |

---

## Product Removed

**When:** User removes a product from the cart.

| Property | Type | Description |
|----------|------|-------------|
| All [product object](#product-object) properties | — | Product and quantity removed. |
| `cart_id` | string | Quote ID. |

---

## Cart Viewed

**When:** User views the cart page (cart has at least one item).

| Property | Type | Description |
|----------|------|-------------|
| `cart_id` | string | Quote ID. |
| `products` | array | List of [product objects](#product-object) (with `cart_id` on each). |

---

## Checkout Started

**When:** User reaches the first checkout step and the quote has items.

| Property | Type | Description |
|----------|------|-------------|
| `order_id` | string | Quote ID (used as checkout/session identifier). |
| `affiliation` | string | Store name. |
| `value` | number | Cart total (grand total or subtotal). |
| `revenue` | number | Same as `value`. |
| `currency` | string | Quote currency code. |
| `products` | array | List of [product objects](#product-object) from the quote. |
| `shipping` | number | Present if shipping total > 0. |
| `tax` | number | Present if tax total > 0. |
| `discount` | number | Present if discount > 0. |
| `coupon` | string | Present if a coupon code is applied. |

---

## Order Completed

**When:** User lands on the order success page after placing an order.

| Property | Type | Description |
|----------|------|-------------|
| `checkout_id` | string | Quote ID. |
| `order_id` | string | Order increment ID. |
| `affiliation` | string | Store name. |
| `total` | number | Order grand total. |
| `subtotal` | number | Order subtotal. |
| `revenue` | number | Subtotal (revenue). |
| `shipping` | number | Shipping amount. |
| `tax` | number | Tax amount. |
| `discount` | number | Discount amount (absolute value). |
| `coupon` | string | Coupon code, if used. |
| `currency` | string | Order currency code. |
| `products` | array | List of [order item objects](#order-item-object) (product_id, sku, name, variant, price, quantity, etc.). |

---

## Product object

Used in **Product Viewed**, **Product Added**, **Product Removed**, **Product List Viewed** (inside `products[]`), **Cart Viewed**, and **Checkout Started**.

Properties are omitted when empty or null.

| Property | Type | Description |
|----------|------|-------------|
| `product_id` | string | Product ID. |
| `sku` | string | SKU. |
| `category` | string | Name of first category. |
| `name` | string | Product name. |
| `brand` | string | Brand (attribute or raw). |
| `variant` | string | Variant info (e.g. configurable options). |
| `price` | number | Unit price. |
| `quantity` | number | Quantity. |
| `value` | number | price × quantity. |
| `position` | number | 1-based position (list views only). |
| `url` | string | Product URL. |
| `image_url` | string | Base image URL. |
| `currency` | string | Store currency code. |
| `cart_id` | string | Quote ID (cart/checkout events only). |

---

## Order item object

Used in **Order Completed** inside `products[]`. Fewer fields than the full product object (no URL, image, category from catalog).

| Property | Type | Description |
|----------|------|-------------|
| `product_id` | string | Product ID or SKU. |
| `sku` | string | SKU. |
| `name` | string | Item name. |
| `brand` | string | Often empty. |
| `variant` | string | Configurable options summary. |
| `price` | number | Unit price. |
| `quantity` | number | Qty ordered. |

---

## Using this reference in PostHog

- **Filters:** Use property names (e.g. `category`, `sku`, `query`) in insight filters.
- **Breakdowns:** Break down by `category`, `sku`, `brand`, or `list_id` for product/list analysis.
- **Funnels:** Use the event names exactly as above (e.g. `Product Viewed` → `Product Added` → `Checkout Started` → `Order Completed`).
- **Trends:** Select events and optionally break down by a property.
- **Revenue:** Use `revenue`, `total`, `value`, and `currency` from **Checkout Started** and **Order Completed**.
