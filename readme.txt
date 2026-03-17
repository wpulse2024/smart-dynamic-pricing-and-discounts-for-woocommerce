=== WPulse Pricing Rules for WooCommerce ===
Contributors: wpulse, dasnitesh780, chadni54
Tags: woocommerce dynamic pricing, bulk discount, tiered pricing, BOGO, pricing rules
Requires at least: 5.0
Tested up to: 6.9.1
Stable tag: 1.1.0
Requires PHP: 7.4
WC requires at least: 5.0
WC tested up to: 9.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create dynamic pricing rules, quantity discounts, BOGO deals, role-based pricing, and cart promotions for WooCommerce — 100% free.

== Description ==

**WPulse Pricing Rules for WooCommerce** is the **free, open-source alternative to premium dynamic pricing plugins** like YITH Dynamic Pricing & Discounts and Dynamic Pricing & Discounts by RightPress. Build powerful, flexible discount rules with zero subscription fees.

Whether you need **quantity-based discounts**, **tiered bulk pricing**, **BOGO deals**, **user role pricing**, **cart promotions**, **free shipping rules**, or **free gift products** — WPulse handles them all from a clean, modern admin interface.

🎥 **See it in action:**

[youtube https://www.youtube.com/watch?v=KNTHoxCiqJQ]

---

### Why WPulse Pricing Rules?

Most WooCommerce dynamic pricing plugins lock essential features behind expensive yearly plans. WPulse gives you the **same core rule engine — completely free**:

* ✅ Tiered / bulk quantity discounts
* ✅ BOGO (Buy One Get One) and X for Y deals
* ✅ Role-based pricing for wholesale or VIP customers
* ✅ Cart subtotal and quantity conditions
* ✅ Free shipping rules
* ✅ Free gift products added automatically
* ✅ Per-category discount rates
* ✅ Global exclusion list
* ✅ Scheduled rules with start/end dates
* ✅ Priority-based rule stacking
* ✅ Discount badge on product and shop pages
* ✅ [save_amount] and [save_percentage] shortcodes in messages

---

### Discount Types

**WPulse Pricing Rules** supports 9 distinct discount types out of the box:

* **Percent off** — Percentage discount on individual products or the entire cart.
* **Fixed amount off** — Fixed £/$ discount per product or on the cart total.
* **Tiered quantity pricing** — Different discount per quantity range (e.g. 5% for 2–4 items, 10% for 5–9, 15% for 10+). Perfect for bulk discount pricing.
* **X for Y** — Classic BOGO-style: buy X pay for Y (e.g. Buy 3, Pay for 2).
* **Nth unit % off** — Percentage off every Nth item (e.g. 50% off the 2nd unit).
* **Cart % discount** — Percentage off the cart when conditions are met.
* **Cart fixed discount** — Fixed amount off the cart total.
* **Free shipping** — Free shipping when conditions are met (replaces or overrides the shipping rate).
* **Free gift** — Automatically add one or more free products when conditions are met.
* **Category discounts** — Different percent or fixed discount per product category in one rule.

---

### Rule Conditions

Target the right customers at the right time:

* **Cart conditions** — Cart subtotal (min/max), cart quantity (min/max), number of distinct line items.
* **Customer conditions** — User role (e.g. Wholesale, Subscriber), specific user, total amount spent, number of past orders.
* **Product conditions** — Specific products in cart.
* **Page conditions** — Cart page, checkout page.
* **Coupon condition** — Coupon applied to cart.
* **Shipping condition** — Customer's shipping country.
* **Schedule** — Optional start date, end date, and days of the week per rule.

---

### Targeting & Exclusions

Choose exactly which products a rule applies to:

* **Apply to all products** — Rule applies store-wide.
* **Specific products** — Target individual product IDs.
* **Specific categories** — Target one or more product categories.
* **Per-rule exclusions** — Exclude selected products, categories, or tags from a specific rule.
* **Global Exclusion List** — A single screen to exclude products from every rule at once (useful for sale items, bundles, etc.).

---

### Templates

Start faster with built-in rule templates:

| Template | Discount Type |
|---|---|
| 3 for 2 | X for Y |
| 2 for 1 (BOGO) | X for Y |
| 50% off the 2nd unit | Nth % off |
| Tiered bulk discount | Tiered |
| Black Friday % off | Percent off |
| Wholesale / role pricing | Role-based |
| Free gift on cart | Free gift |
| Cart subtotal discount | Cart % off |
| Free shipping on cart | Free shipping |
| Checkout deal | Cart fixed off |
| Category discount | Category discounts |

Or start from scratch — the full editor is available for any discount type.

---

### Admin Interface

* **Vue 3 single-page app** — Fast, reactive admin with no full-page reloads.
* **Rules list** — Sort, filter by type/status, search by name, bulk enable/disable/delete.
* **Rule editor** — Inline editing with live validation and error feedback.
* **Templates modal** — Pick a starting template in one click.
* **Exclusion List** — Searchable product, category, and tag exclusion manager.

---

### Upcoming Features 🚀

* Shortcodes and Gutenberg blocks for discount messaging.
* Import/export rules (CSV / JSON).
* Additional condition types and benefit options.
* Styling options for frontend discount messages.
* Compatibility with major page builders.

---

== Installation ==

**From WordPress admin (recommended):**

1. Go to **Plugins → Add New**.
2. Search for **WPulse Pricing Rules**.
3. Click **Install Now**, then **Activate**.
4. Go to **Pricing Rules** in the WordPress admin menu.

**Manual installation:**

1. Download the plugin zip file.
2. Upload to `/wp-content/plugins/wpulse-pricing-rules-for-woocommerce/`.
3. Activate from **Plugins → Installed Plugins**.
4. Go to **Pricing Rules** to create your first rule.

**Requirements:**

* WordPress 5.0 or higher
* WooCommerce 5.0 or higher
* PHP 7.4 or higher

---

== Frequently Asked Questions ==

= Is this a free alternative to YITH WooCommerce Dynamic Pricing & Discounts? =

Yes. WPulse Pricing Rules provides the core dynamic pricing features found in premium plugins — tiered discounts, BOGO, role-based pricing, cart promotions, free shipping rules, and free gifts — completely free with no pro plan required.

= Does it support bulk / quantity-based discounts? =

Yes. The **Tiered pricing** discount type lets you set different discount percentages or fixed amounts per quantity range. For example: 5% off for 2–4 items, 10% off for 5–9, 15% off for 10 or more.

= Can I offer different prices to wholesale or B2B customers? =

Yes. Use the **User role** condition to restrict rules to specific roles (e.g. Wholesale, B2B, Subscriber). Combine with any discount type to give role-based pricing without a separate plugin.

= How does rule priority work? =

Each rule has a numeric priority. The engine applies the highest-priority rule that matches the current cart and customer. Only one discount message is shown on product and shop pages — the one rule the customer will actually receive.

= Can I schedule discount rules to run during a specific date range? =

Yes. Every rule has an optional schedule with a start date, end date, and optional days of the week. Use it for flash sales, seasonal promotions, or recurring weekly deals.

= Does it work with variable products? =

Yes. Rules apply to the matched cart items regardless of whether the product is simple or variable. Both product-level and cart-level discounts work with variable products.

= Can I exclude certain products from all discounts? =

Yes. The **Global Exclusion List** (under **Pricing Rules → Exclusions**) lets you exclude specific products, categories, or tags from every rule at once. You can also add per-rule exclusions in the rule editor.

= Will it conflict with WooCommerce coupons? =

Rules and coupons work independently. You can use the **Coupon applied** condition to fire a rule only when a specific coupon is present, or run rules without coupons entirely.

= Can I show the discount on the shop / archive page? =

Yes. Enable "Show on shop" in the rule's display settings. A compact badge appears under the price on the shop/archive listing. On single product pages, the full discount message appears automatically.

= Is the plugin compatible with WooCommerce HPOS (High-Performance Order Storage)? =

Yes. The plugin declares compatibility with WooCommerce custom order tables (HPOS).

= Where can I get support? =

Open a thread on the [WordPress.org support forum](https://wordpress.org/support/plugin/wpulse-pricing-rules-for-woocommerce/). We respond to every thread.

---

== Screenshots ==

1. Rules list with filters, search, and bulk actions (enable, disable, delete).
2. Templates modal — choose a template or start from scratch.
3. Rule editor — discount type, targets, conditions, schedule, and display settings.
4. Discount types: percent off, tiered, X for Y, Nth unit, cart discount, free shipping, free gift.
5. Conditions panel — cart subtotal, user role, customer order count, shipping country, schedule.
6. Global Exclusion List — exclude products, categories, and tags from all rules.
7. Help & Documentation page with overview video and quick-start guide.
8. Discount message on single product page (highest-priority applicable rule only).
9. Discount badge on shop/archive page when "Show on shop" is enabled.

---

== Changelog ==

= 1.1.0 – 2026-03-17 =

**Security**
* Fixed SQL injection risk: applied `esc_sql()` to all raw table-name interpolations in RulesRepository, ExclusionRepository, and Installer queries (SEC-C1–C4).
* Fixed CSRF: nonces are now read strictly from `$_POST` (not `$_REQUEST`) across all AJAX handlers (SEC-H1).
* Fixed XSS: custom rule messages are sanitized with `wp_kses_post()` at the point of return, not just at output (SEC-H2).
* Removed duplicate AJAX routes for template creation — frontend now uses REST API exclusively, reducing attack surface (SEC-H4).
* Added transient-based rate limiting (60 requests/min per user) on product, category, and tag search AJAX endpoints (SEC-H5).
* Applied `sanitize_text_field(wp_unslash())` to user and product search parameters in EditorDataController (SEC-M1, SEC-M2).
* Wrapped `wp_safe_redirect()` URL with `esc_url()` in EditRulePage (SEC-M4).
* DB errors in `Model::find()` are now logged via `error_log()` instead of silently returning null (SEC-M7).
* Added null/empty price guard in `Context::fromCart()` — products with no price set are skipped and logged rather than treated as free (SEC-L2).
* Added `KEY status (status)` index to the rules table via safe dbDelta migration — improves engine query performance on status filtering (SEC-L3).

**Performance**
* Introduced `RuleSchedule` shared utility class with static memoization — eliminates duplicate `wp_get_post_terms()` calls across RuleEngine, Context, TargetMatcher, and ProductDiscountMessage.
* Rule JSON is now decoded once per request in RuleEngine and stored in a static cache — eliminates repeated `json_decode()` in the line-item loop, gift loop, and fees hook.
* Static `$cached_rules` in ProductDiscountMessage — eliminates one DB query per product on shop/archive pages (was N queries for N products).
* Static customer data cache in `Context` — WC_Customer data (order count, total spent) fetched once per user per request instead of on every cart recalculation.
* `ExclusionRepository::getByType()` now runs a direct `WHERE exclusion_type = %s` query instead of loading all rows and filtering in PHP.
* `RulesRepository::all()` now accepts optional `$limit` and `$offset` parameters for pagination (backward compatible — existing callers unaffected).
* Discounted prices are now rounded to `wc_get_price_decimals()` in all benefit handlers (PercentOff, Tiered, XForY, NthPercentOff, CategoryDiscounts).

**Code Quality**
* Refactored `RuleEngine::onBeforeCalculateTotals()` (was 76 lines) into `prepareCart()`, `applyLineRules()`, and `applyGiftRules()` private methods.
* Replaced 8-case `applyBenefit()` switch with a static benefit handler registry — new benefit types can be added without touching the central method.
* Replaced route dispatcher switch in `Route::mapAction()` with dynamic `method_exists()` dispatch.
* Extracted `buildCommonContext()` in `Context` — eliminates ~40 lines of duplicated customer data fetching between `fromCart()` and `forProductPage()`.
* Extracted `getTaxonomyOptions()` in `EditorDataController` — `categories()` and `tags()` share one implementation.
* Extracted `searchTaxonomy()` in `ExclusionAjax` — `searchCategories()` and `searchTags()` share one implementation.
* Removed dead code: `RuleEvaluator` (431-line legacy class), `Ajax::register()`, `PricingRule::createTable()`.

**Bug Fixes**
* `RulesController::store()` now returns a decoded `rule` key (consistent with `show()`, `update()`, and `index()`). Previously returned the raw `rule_json` string.
* `FreeGift::apply()` now checks `ExclusionService::isProductExcluded()` before adding the gift product to the cart — respects the global exclusion list.
* `ExclusionService` static cache is now reset at the start of each `woocommerce_before_calculate_totals` call — prevents stale cache in object-cache or queue-worker environments.
* `createFromTemplate` and `createFromScratch` responses now include a `hash` key (`#/rules/edit/{id}`) for correct Vue Router navigation.

**UI / JavaScript**
* Rule editor now shows an inline error message when a rule fails to load or save (previously silent).
* Bulk enable/disable/delete now reports partial failures: "X of Y rules could not be updated" (previously silent on partial failure).
* Templates modal now navigates via Vue Router hash (`res.hash`) instead of a full admin URL — fixes navigation on sites with non-standard WordPress installs.
* Standardized search `per_page` to 50 across all product, user, category, and tag search calls in the rule editor.
* `ExclusionListView` now loads up to 100 entries for categories and tags on initial load (matching products).
* JSON parse errors in the API client are now logged to the browser console instead of swallowed silently.

---

= 1.0.0 – 2026-02-14 =
* Initial release.
* Dynamic pricing rules with templates (3 for 2, BOGO, tiered, user role, cart discount, free shipping, free gift, and more).
* Conditions: cart subtotal/quantity, user role, user, page, products in cart, schedule.
* Targeting: all products, specific products or categories; per-rule and global exclusions.
* Single applicable rule message on product and shop (priority-based).
* Vue 3 admin: rule list, editor, filters, bulk actions, Help page with overview video.
* WooCommerce cart and checkout integration (line-item and cart discounts, free shipping, free gifts).

---

== Upgrade Notice ==

= 1.1.0 =
Security, performance, and reliability release. Fixes SQL injection hardening, CSRF/XSS improvements, removes duplicate AJAX routes, and adds request-scoped caching throughout the rule engine. Fully backward compatible — no action required on upgrade.

= 1.0.0 =
First stable release of **WPulse Pricing Rules for WooCommerce**.
