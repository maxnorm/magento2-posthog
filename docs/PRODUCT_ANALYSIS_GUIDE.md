# Product Analysis guide — Indexa_Posthog

This guide shows how to use PostHog **Product analytics** with the events sent by **Indexa_Posthog**: insight ideas, step-by-step setup, and suggested dashboard layouts.

For event and property details, see [EVENT_REFERENCE.md](EVENT_REFERENCE.md).

---

## 1. Core insights to build

### 1.1 Conversion funnel (must-have)

**Goal:** See how many users move from viewing a product to adding to cart, starting checkout, and completing an order.

| Insight type | Steps / series |
|--------------|----------------|
| **Funnel** | 1. Product Viewed → 2. Product Added → 3. Checkout Started → 4. Order Completed |

**How to create in PostHog:**

1. **Product analytics** → **New** → **New funnel**.
2. Add steps in order: **Product Viewed**, **Product Added**, **Checkout Started**, **Order Completed**.
3. (Optional) **Break down by** `category` or `sku` to see which products/categories convert best.
4. Save (e.g. name: “Ecommerce conversion funnel”) and add to a dashboard.

**What to look at:** Drop-off between steps (e.g. many “Product Viewed” but few “Product Added” → improve PDP or add-to-cart UX).

---

### 1.2 List → View → Add funnel

**Goal:** Understand how category/search listings drive product views and add-to-cart.

| Insight type | Steps / series |
|--------------|----------------|
| **Funnel** | 1. Product List Viewed → 2. Product Viewed → 3. Product Added |

**Break down by:** `category` (or `list_id`) to compare performance by category/search.

**What to look at:** Which lists drive the most PDP views and adds; which have low view→add rate.

---

### 1.3 Trends (volume over time)

**Goal:** Track event volume and revenue over time.

| Insight type | What to add |
|--------------|-------------|
| **Trends** | Series: **Product Viewed**, **Product Added**, **Order Completed** (total count). |
| **Trends** | Series: **Order Completed** with **Math**: Total revenue (use property `revenue` or `total`). |

**Break down by:** `category`, `sku`, or `currency` as needed.

---

### 1.4 Search analytics

**Goal:** See what users search for and how search leads to conversion.

| Insight type | What to add |
|--------------|-------------|
| **Trends** | Event: **Products Searched**; **Break down by** `query` (or group similar queries). |
| **Funnel** | Products Searched → Product List Viewed (or Product Viewed) → Product Added → Order Completed. |

**What to look at:** Top queries, zero-result or low-conversion queries (improve search or merchandising).

---

### 1.5 Cart and checkout health

**Goal:** See cart and checkout engagement.

| Insight type | What to add |
|--------------|-------------|
| **Trends** | **Cart Viewed**, **Checkout Started**, **Order Completed** (counts over time). |
| **Funnel** | Cart Viewed → Checkout Started → Order Completed. |

**What to look at:** Cart abandonment (Cart Viewed vs Checkout Started), checkout abandonment (Checkout Started vs Order Completed).

---

### 1.6 Product performance

**Goal:** Top products by views, adds, and orders.

| Insight type | What to add |
|--------------|-------------|
| **Trends** | Event: **Product Viewed** (or **Product Added**, **Order Completed**); **Break down by** `sku` or `name`. |
| **Table** | Event: **Order Completed**; **Break down by** `sku`; sort by count or by revenue (if available in breakdown). |

Use **Break down by** `category` or `brand` for category/brand comparison.

---

### 1.7 Retention (optional)

**Goal:** Do users who view products come back to purchase?

| Insight type | What to add |
|--------------|-------------|
| **Retention** | First event: **Product Viewed** (or **Product Added**). Return event: **Order Completed**. |

---

### 1.8 User paths (optional)

**Goal:** See common paths from listing → PDP → cart → checkout.

| Insight type | What to add |
|--------------|-------------|
| **Paths** | Start: **Product List Viewed** or **Product Viewed**. End: **Order Completed** (optional). |

---

## 2. Step-by-step: main conversion funnel

1. In PostHog go to **Product analytics** → **New** → **New funnel**.
2. **Step 1:** Event = `Product Viewed`. (No filter needed for basic funnel.)
3. **Step 2:** Event = `Product Added`.
4. **Step 3:** Event = `Checkout Started`.
5. **Step 4:** Event = `Order Completed`.
6. **Break down by** (optional): Property = `category` or `sku`.
7. **Save** and name the insight (e.g. “Ecommerce conversion funnel”).
8. **Add to dashboard** (create a new dashboard or choose existing).

---

## 3. Step-by-step: trends (views and orders)

1. **Product analytics** → **New** → **New trends**.
2. **Series 1:** Event = `Product Viewed`, Math = Total.
3. **+ Add series:** Event = `Product Added`, Math = Total.
4. **+ Add series:** Event = `Order Completed`, Math = Total.
5. **Break down by** (optional): `category` or `sku`.
6. Save and add to dashboard.

---

## 4. Suggested dashboard layouts

Use one dashboard (e.g. **“Ecommerce overview”**) and arrange insights as below. All insight types refer to PostHog Product analytics.

### 4.1 Layout A — Executive overview

| Row | Insight | Purpose |
|-----|--------|--------|
| 1 | **Funnel:** Product Viewed → Product Added → Checkout Started → Order Completed | Main conversion at a glance |
| 2 | **Trends:** Product Viewed, Product Added, Order Completed (over time) | Volume trend |
| 3 | **Trends:** Order Completed, Math = Sum of `revenue` (or `total`) | Revenue over time |

### 4.2 Layout B — Product and list performance

| Row | Insight | Purpose |
|-----|--------|--------|
| 1 | **Funnel:** Product List Viewed → Product Viewed → Product Added | List effectiveness |
| 2 | **Trends:** Product Viewed, broken down by `category` | Views by category |
| 3 | **Trends:** Product Added or Order Completed, broken down by `sku` | Top products by add/order |
| 4 | **Trends:** Products Searched, broken down by `query` | Top search terms |

### 4.3 Layout C — Cart and checkout

| Row | Insight | Purpose |
|-----|--------|--------|
| 1 | **Funnel:** Cart Viewed → Checkout Started → Order Completed | Cart/checkout conversion |
| 2 | **Trends:** Cart Viewed, Checkout Started, Order Completed | Volume by stage |
| 3 | **Funnel:** Product Viewed → Product Added → Cart Viewed | PDP → cart |

### 4.4 Layout D — Combined (single dashboard)

Combine the most important tiles:

1. **Row 1:** Main conversion funnel (Product Viewed → … → Order Completed).
2. **Row 2:** Two trends — (a) Product Viewed / Product Added / Order Completed counts, (b) Order Completed revenue.
3. **Row 3:** List funnel (Product List Viewed → Product Viewed → Product Added), optional breakdown by `category`.
4. **Row 4:** Cart/checkout funnel (Cart Viewed → Checkout Started → Order Completed).
5. **Row 5:** Search trend (Products Searched, breakdown by `query`) or top products (Order Completed by `sku`).

Adjust rows and sizes in the dashboard so key metrics are above the fold.

---

## 5. Filters and breakdowns (quick reference)

- **Event properties** (see [EVENT_REFERENCE.md](EVENT_REFERENCE.md)): `product_id`, `sku`, `category`, `name`, `brand`, `variant`, `price`, `quantity`, `url`, `image_url`, `currency`, `cart_id`, `query`, `list_id`, `order_id`, `revenue`, `total`, etc.
- **Useful filters:** e.g. `category` equals “Running”, `sku` contains “SHOE”, `revenue` > 0.
- **Useful breakdowns:** `category`, `sku`, `brand`, `list_id`, `query` (for Products Searched).

---

## 6. API-based creation (optional)

To create insights or dashboards programmatically, use the [PostHog Insights API](https://posthog.com/docs/api/insights) and [Dashboard templates API](https://posthog.com/docs/api/dashboard-templates). Example payloads for funnel and trends are in the project root doc: [posthog-ecommerce-templates.md](../../../docs/posthog-ecommerce-templates.md).

---

## 7. Best practices

- **Separate projects** for development, staging, and production so test traffic doesn’t pollute production analytics.
- **Filter internal users** in PostHog (e.g. exclude internal IPs or test emails) so reports reflect real customers.
- **Reverse proxy** PostHog (and set **API Host** in the module) to reduce ad-blocker impact.
- **Cookieless:** With cookieless mode, unique users are estimated via PostHog’s cookieless server hash; enable it in **Project Settings → Web analytics**.

For more on the module (config, consent, events), see the [README](../README.md).
