# WPulse Pricing Rules for WooCommerce — Feature Specification

> **Purpose:** This document is the single source of truth for all features — implemented and planned.
> Each feature is written as a self-contained spec so it can be handed directly to an LLM as an implementation prompt.
>
> **Stack:** PHP 7.4+, WooCommerce 5+, WordPress 5+, Vue 3 + Element Plus (admin SPA), Vite build, REST API, MySQL/MariaDB.
>
> **Key files:**
> - Engine: `includes/Engine/RuleEngine.php`, `ConditionEvaluator.php`, `TargetMatcher.php`
> - Benefits: `includes/Engine/Benefits/*.php`
> - Context: `includes/Engine/Context.php`
> - DB: `includes/DB/RulesRepository.php`, `Installer.php`
> - REST: `app/Http/Controllers/`, `routes/api.php`
> - Admin SPA: `admin/views/`, `admin/components/`
> - Frontend: `includes/Frontend/ProductDiscountMessage.php`

---

## Status Legend

| Status | Meaning |
|---|---|
| ✅ DONE | Fully implemented and working |
| 🚧 PARTIAL | Exists in code but incomplete / not wired up |
| 📋 PLANNED | Not yet built — spec is ready to implement |

---

## PART 1 — DISCOUNT / BENEFIT TYPES

### 1.1 Percent Off (per product) ✅ DONE
**File:** `includes/Engine/Benefits/PercentOff.php`
**Kind key:** `percent_off`
Apply a fixed percentage discount to each matching product's price in the cart.
- `benefit.percent` (float) — e.g. `20` = 20% off
- Stores original price in WC session to avoid double-discount on re-calc
- Applied via `$item->set_price()` inside `woocommerce_before_calculate_totals`

---

### 1.2 Fixed Amount Off (per product) ✅ DONE
**File:** `includes/Engine/Benefits/PercentOff.php`
**Kind key:** `fixed_off`
Subtract a fixed currency amount from each matching product's unit price.
- `benefit.amount` (float) — e.g. `5` = £5 off
- Price floored at 0 (never goes negative)
- Same session-restore mechanism as percent_off

---

### 1.3 Tiered Quantity Discount ✅ DONE
**File:** `includes/Engine/Benefits/Tiered.php`
**Kind key:** `tiered`
Different discount levels based on quantity of a product in the cart.
- `benefit.tiers` array: `[{ min, max, percent_off, fixed_off }]`
- `max` can be `null` meaning "and above"
- Each tier can use percent OR fixed (not both)
- Only the deepest matching tier applies
- Example: qty 2–4 = 5%, qty 5–9 = 10%, qty 10+ = 15%

---

### 1.4 Buy X Pay Y (BOGO / 3-for-2) ✅ DONE
**File:** `includes/Engine/Benefits/XForY.php`
**Kind key:** `x_for_y`
Classic "buy N get M free" deal — cheapest items in each group are discounted.
- `benefit.buy_qty` (int) — items to buy at full price
- `benefit.pay_qty` (int) — items to actually pay for
- Cheapest units in each group get 100% off
- Repeating: groups recalculate per multiple (e.g. 3-for-2 with qty 6 gives 2 free)
- Works on products sorted cheapest-first within the target set

---

### 1.5 Nth Unit Percent Off ✅ DONE
**File:** `includes/Engine/Benefits/NthPercentOff.php`
**Kind key:** `nth_percent_off`
Apply a percentage discount to every Nth unit of a product.
- `benefit.nth` (int) — which unit position gets the discount (e.g. 2 = every 2nd unit)
- `benefit.percent` (float) — discount on that unit
- Example: 50% off 2nd unit — buy 4, units 2 & 4 get 50% off

---

### 1.6 Category-Level Discounts ✅ DONE
**File:** `includes/Engine/Benefits/CategoryDiscounts.php`
**Kind key:** `category_discounts`
Apply different discount rates to different product categories in a single rule.
- `benefit.category_discounts` array: `[{ apply_type: 'percent'|'fixed', value, category_ids[] }]`
- A product is matched to the first category group it belongs to
- Useful for "10% off electronics, 20% off clothing" in one rule

---

### 1.7 Cart Percentage Discount ✅ DONE
**File:** `includes/Engine/Benefits/CartDiscount.php`
**Kind key:** `cart_percent_off`
Apply a percentage discount to the entire cart as a negative fee.
- `benefit.percent` (float) — cart-level percentage
- Applied via `woocommerce_cart_calculate_fees` as a named negative fee
- Fee label: rule name or "Discount"
- Does NOT modify line-item prices — shows as separate fee line

---

### 1.8 Cart Fixed Discount ✅ DONE
**File:** `includes/Engine/Benefits/CartDiscount.php`
**Kind key:** `cart_fixed_off`
Subtract a fixed currency amount from the cart total as a negative fee.
- `benefit.amount` (float)
- Applied as named negative fee, same as cart_percent_off

---

### 1.9 Free Shipping ✅ DONE
**File:** `includes/Engine/Benefits/FreeShipping.php`
**Kind key:** `free_shipping`
Eliminate all shipping costs when rule conditions are met.
- Sets `WC()->session->set('wpulse_free_shipping', true)`
- `woocommerce_package_rates` filter zeros out all shipping method costs
- No product target needed — typically used with cart condition (subtotal ≥ threshold)

---

### 1.10 Free Gift (Auto-Add Product) ✅ DONE
**File:** `includes/Engine/Benefits/FreeGift.php`
**Kind key:** `free_gift`
Automatically add one or more free products to the cart when conditions are met.
- `benefit.product_ids` (int[]) — products to add
- `benefit.quantity` (int) — how many of each
- Gift items tagged with cart item meta `_wpulse_is_gift = true`
- Two-phase handling: remove stale gifts (priority 9000), re-add qualifying gifts (priority 9999)
- Gifts priced at 0.00, displayed separately in cart

---

### 1.11 Fixed Price (Set Price To) ✅ DONE
**File:** `includes/Engine/Benefits/FixedPrice.php`
**Kind key:** `fixed_price`
Override the product price to a specific amount, regardless of the original price.
- `benefit.price` (float) — the target price
- If original price > target, price is set to target; if lower, no change (optional flag to always set)
- `benefit.apply_to` — `'lowest'` | `'highest'` | `'all'` for multi-item scenarios
- Useful for "all items in this category = $9.99"
- Must use session-based original price restore (same pattern as PercentOff)

---

### 1.12 Bulk Fixed Price (Price Breaks) 📋 PLANNED
**File to create:** `includes/Engine/Benefits/BulkFixedPrice.php`
**Kind key:** `bulk_fixed_price`
Set specific unit prices at quantity breakpoints (not discounts — absolute prices).
- `benefit.price_breaks` array: `[{ min_qty, max_qty, unit_price }]`
- Example: 1–4 units = $10 each; 5–9 = $8 each; 10+ = $6 each
- Price table shown on product page (see Feature 7.3)
- Common in wholesale/B2B scenarios

---

### 1.13 Bundle Pricing 📋 PLANNED
**File to create:** `includes/Engine/Benefits/BundleDiscount.php`
**Kind key:** `bundle_discount`
Discount applied only when a specific combination of products are ALL in the cart together.
- `benefit.bundle_products` (int[]) — all must be present
- `benefit.discount_type` — `'percent'` | `'fixed'`
- `benefit.value` — discount amount
- `benefit.apply_to` — `'all'`|`'cheapest'`|`'most_expensive'` item in bundle
- Condition: all bundle_products must appear in cart (check in ConditionEvaluator or TargetMatcher)

---

### 1.14 Order Bump / Upsell Offer 📋 PLANNED
**Description:** Show a special one-time offer during checkout — "Add this item for only $X."
- Triggered by a rule with `kind: 'order_bump'`
- `benefit.offer_product_id` — product to offer
- `benefit.discounted_price` — price at which it's offered
- `benefit.bump_title`, `benefit.bump_description`, `benefit.bump_image_id`
- Rendered via `woocommerce_review_order_before_submit` hook in checkout
- JS checkbox adds product to cart via AJAX with special discount meta
- Must prevent bump from triggering its own rules (mark with `_wpulse_is_bump`)

---

## PART 2 — RULE CONDITIONS

All conditions live in `includes/Engine/ConditionEvaluator.php` and are stored in `rule_json → conditions.groups[].items[]`.
Each item: `{ type, operator, value }`. Groups are OR'd; items within a group use the group's `logic` (AND or OR).

### 2.1 Cart Subtotal ✅ DONE
**Key:** `cart_subtotal`
**Operators:** `>=`, `>`, `<=`, `<`, `=`
Compare cart subtotal (before discounts) against a threshold value.

---

### 2.2 Cart Quantity (Total Items) ✅ DONE
**Key:** `cart_quantity`
**Operators:** `>=`, `>`, `<=`, `<`, `=`
Total number of units in the cart (sum of all line quantities).

---

### 2.3 Cart Items Count (Distinct Lines) ✅ DONE
**Key:** `cart_items_count`
**Operators:** `>=`, `>`, `<=`, `<`, `=`
Number of distinct product lines (not total units).

---

### 2.4 Customer Lifetime Spend ✅ DONE
**Key:** `total_amount_spent`
**Operators:** `>=`, `>`, `<=`, `<`, `=`
Total money spent by this customer across all completed/processing orders.
- Fetched from `wc_get_customer_total_spent()`
- Cached per-request in `$customer_cache`

---

### 2.5 Customer Order Count ✅ DONE
**Key:** `order_count`
**Operators:** `>=`, `>`, `<=`, `<`, `=`
Number of previous orders placed by this customer.
- Fetched from `wc_get_customer_order_count()`

---

### 2.6 Product in Cart ✅ DONE
**Key:** `product_in_cart`
**Operators:** `in`, `not_in`
**Value:** array of product IDs
Check if specific products are currently in the cart.

---

### 2.7 User Role ✅ DONE
**Key:** `user_role`
**Operators:** `in`, `not_in`
**Value:** array of role slugs
Match against the current user's WordPress roles. Guests have no roles (empty array).

---

### 2.8 Specific User ✅ DONE
**Key:** `user_id`
**Operators:** `in`, `not_in`
**Value:** array of user IDs
Target or exclude specific individual users by ID.

---

### 2.9 Page Type ✅ DONE
**Key:** `page`
**Operators:** `=`
**Values:** `'cart'`, `'checkout'`, `'other'`
Restrict rule to specific page context.

---

### 2.10 Shipping Country ✅ DONE
**Key:** `shipping_country`
**Operators:** `in`, `not_in`
**Value:** array of ISO country codes (e.g. `['US', 'CA']`)
Match against the customer's selected shipping country.

---

### 2.11 Coupon Applied ✅ DONE
**Key:** `coupon`
**Operators:** `in`, `not_in`
**Value:** array of coupon codes
Check if specific WooCommerce coupons are currently applied to the cart.

---

### 2.12 Customer Is Guest / Logged In 📋 PLANNED
**Key:** `user_auth`
**Operators:** `=`
**Values:** `'guest'` | `'logged_in'`
Simple check: is the current visitor a logged-in WordPress user or a guest?
- In ConditionEvaluator, check `is_user_logged_in()`
- Must also work in product-page context (no cart)

---

### 2.13 Customer Email Domain 📋 PLANNED
**Key:** `email_domain`
**Operators:** `in`, `not_in`
**Value:** array of domains (e.g. `['company.com', 'acme.org']`)
Match against the logged-in user's email domain — useful for B2B company-level discounts.
- Get email from `wp_get_current_user()->user_email`
- Extract domain: `substr(strrchr($email, '@'), 1)`
- On checkout, also check billing email field for guests

---

### 2.14 Day of Week 📋 PLANNED
**Key:** `day_of_week`
**Operators:** `in`, `not_in`
**Values:** `['monday', 'tuesday', ...]`
Apply rules only on specific days of the week — e.g. "Tuesday discount."
- Use `strtolower(date('l'))` in store timezone (`wp_timezone()`)
- Stored as array of lowercase day names

---

### 2.15 Time of Day 📋 PLANNED
**Key:** `time_of_day`
**Operators:** `between`
**Value:** `{ from: 'HH:MM', to: 'HH:MM' }` (24h format)
Happy-hour pricing — apply only during a time window.
- Compare against store local time via `wp_timezone()`
- Admin UI: two time-picker inputs

---

### 2.16 Cart Contains Product from Category 📋 PLANNED
**Key:** `category_in_cart`
**Operators:** `in`, `not_in`
**Value:** array of category IDs
True if any cart item belongs to the specified categories.
- Iterate cart lines, check `$line['data']->get_category_ids()`

---

### 2.17 First Order (New Customer) 📋 PLANNED
**Key:** `is_first_order`
**Operators:** `=`
**Values:** `true` | `false`
True if the customer has zero previous completed orders.
- Check `wc_get_customer_order_count(user_id) === 0`
- For guests, check by billing email during checkout
- On product page with no session billing email: show message but don't apply discount (display note)

---

### 2.18 Product Quantity in Cart 📋 PLANNED
**Key:** `product_quantity_in_cart`
**Operators:** `>=`, `>`, `<=`, `<`, `=`
**Value:** `{ product_id, quantity }` — check quantity of a SPECIFIC product in cart.
- More precise than `cart_quantity` which is total
- Example: "Buy 3 of Product X" as a trigger condition

---

### 2.19 Shipping State / Region 📋 PLANNED
**Key:** `shipping_state`
**Operators:** `in`, `not_in`
**Value:** array of state codes (e.g. `['CA', 'NY']`)
Similar to shipping country but at the state/province level.
- Get from `WC()->customer->get_shipping_state()`

---

### 2.20 Has Purchased Product Before 📋 PLANNED
**Key:** `previously_purchased`
**Operators:** `in`, `not_in`
**Value:** array of product IDs
True if customer has previously purchased (completed order) any of the listed products.
- Query orders via `wc_get_orders()` with customer filter
- Cache result per customer per request
- Only available for logged-in users (skip for guests with empty check)

---

## PART 3 — RULE TARGETS

Targets control WHICH products the benefit applies to. Stored in `rule_json → targets`.

### 3.1 All Products ✅ DONE
**Type:** `all`
Rule benefit applies to every product in the cart / store.

---

### 3.2 Specific Products ✅ DONE
**Type:** `products`
**Field:** `targets.products` (int[])
Apply only to listed product IDs. Supports simple products and variable parents.

---

### 3.3 Product Categories ✅ DONE
**Type:** `categories`
**Field:** `targets.categories` (int[])
Apply to all products belonging to the listed category IDs (including child categories).

---

### 3.4 Product Tags ✅ DONE
**Type:** `tags`
**Field:** `targets.tags` (int[])
Apply to all products with any of the listed tag IDs.

---

### 3.5 Product Variations ✅ DONE
**Type:** `variations`
**Field:** `targets.variations` (int[])
Apply only to specific variation IDs of variable products. Added in v1.1.1.

---

### 3.6 Product Attributes 📋 PLANNED
**Type:** `attributes`
**Field:** `targets.attributes` (array of `{ taxonomy, term_ids[] }`)
Target products by custom WooCommerce attributes (e.g. `pa_color = red, blue`).
- In TargetMatcher, get `$product->get_attributes()` and match against term IDs
- Admin UI: attribute taxonomy selector + term multi-select (dynamic based on chosen taxonomy)
- EditorDataController needs new endpoint: `GET /editor/attributes` and `GET /editor/attribute-terms?taxonomy=pa_color`

---

### 3.7 Products on Sale 📋 PLANNED
**Type:** `on_sale`
**Field:** `targets.on_sale` (bool — `true` = target sale items, `false` = exclude sale items)
Apply rule only to products that are currently on WooCommerce sale.
- Check `$product->is_on_sale()`
- Also useful as an exclusion: "Don't apply to products already on sale"
- Often combined with per-rule exclusions — can be an exclusion flag rather than a target type

---

### 3.8 Product Type 📋 PLANNED
**Type:** `product_type`
**Field:** `targets.product_types` (string[]) — `['simple', 'variable', 'subscription', 'bundle']`
Apply rule only to specific WooCommerce product types.
- `$product->get_type()`
- Useful for "discount only on variable products" or "bundle products always 10% off"

---

### 3.9 Custom Product Field (Meta) 📋 PLANNED
**Type:** `product_meta`
**Field:** `targets.meta` (array of `{ key, operator, value }`)
Target products by custom post meta value.
- `get_post_meta($product_id, $key, true)` then compare
- Operators: `=`, `!=`, `contains`, `starts_with`
- Use case: custom fields like `_vendor_id`, `_brand`, `_wholesale_only`

---

## PART 4 — EXCLUSIONS

### 4.1 Per-Rule Product Exclusions ✅ DONE
**Field:** `rule_json → exclusions`
Exclude specific products, categories, or tags from a single rule.
- `exclusions.enabled` (bool)
- `exclusions.type` — `'products'` | `'categories'` | `'tags'`
- `exclusions.ids` (int[])

---

### 4.2 Global Exclusion List ✅ DONE
**Table:** `wp_wpulse_exclusions`
**Files:** `includes/Exclusions/ExclusionService.php`, `ExclusionRepository.php`
Exclude products, categories, or tags from ALL rules simultaneously.
- Admin page: `Pricing Rules → Exclusions`
- Search by name, add/remove
- `ExclusionService::isGloballyExcluded($product_id)` returns bool

---

### 4.3 Exclude On-Sale Products Globally 📋 PLANNED
**Setting:** Global plugin setting (not per-rule) stored in `wp_options` as `wpulse_exclude_on_sale`
When enabled, products that are on WooCommerce sale are automatically skipped by ALL rules.
- Check in `TargetMatcher::lineMatchesTargets()` before applying any benefit
- Admin: checkbox in a new "Settings" page or in the Exclusions page
- Display info next to the setting explaining what "on sale" means in WC context

---

### 4.4 Exclude Specific User Roles Globally 📋 PLANNED
**Setting:** `wpulse_excluded_roles` (array) in wp_options
Prevent all pricing rules from applying to specified user roles.
- Check roles early in `RuleEngine::applyRules()` before iterating rules
- Use case: exclude "administrator", "shop_manager" from seeing discounts during testing

---

## PART 5 — RULE SCHEDULING & LIMITS

### 5.1 Date Range Schedule ✅ DONE
**Fields:** `rule_json → schedule.start`, `schedule.end` (ISO 8601 date strings)
Rule only applies between the start and end dates.
- Compared in `RuleSchedule::isActive()`
- Dates are optional; omitting means "no limit"

---

### 5.2 Usage Limits (Max Uses) 🚧 PARTIAL
**Fields:** `rule_json → limits.max_uses`, `limits.max_uses_per_user`
Fields exist in schema but are not enforced by the engine.

**To implement:**
- Add `use_count` column to `wp_wpulse_pricing_rules` table (int, default 0)
- Add `wp_wpulse_rule_usage` table: `{ id, rule_id, user_id, order_id, used_at }`
- Increment count on `woocommerce_checkout_order_processed` hook
- In RuleEngine, skip rule if `use_count >= max_uses`
- Per-user: query rule_usage table for current user + rule_id count
- Handle guest users by session or billing email

---

### 5.3 Recurring Schedule (Days of Week / Time) 📋 PLANNED
**Fields:** `rule_json → schedule.recurring` (object)
Allow rules to repeat on specific days/times — beyond a simple date range.
- `recurring.days` — array of day names (`['monday', 'friday']`)
- `recurring.time_from` — `'HH:MM'` (store timezone)
- `recurring.time_to` — `'HH:MM'`
- Check in RuleSchedule after date-range check
- Admin UI: day-of-week checkboxes + time range pickers in the Schedule tab

---

### 5.4 Maximum Discount Cap 📋 PLANNED
**Field:** `rule_json → limits.max_discount_amount` (float)
Cap the total discount this rule can provide, regardless of cart size.
- For cart-level discounts: if computed fee > cap, use cap
- For line-item discounts: once cumulative savings from this rule reaches cap, stop discounting additional items
- `limits.max_discount_per_item` — separate per-item cap option

---

### 5.5 Minimum Order Amount Requirement 📋 PLANNED
**Field:** `rule_json → limits.min_order_amount` (float)
Alternative way to set a minimum subtotal (simpler than building a condition group).
- Shortcut stored separately from conditions
- Checked before conditions are evaluated
- Admin UI: simple input at top of rule editor as a "quick setting"

---

## PART 6 — RULE STACKING & PRIORITY

### 6.1 Priority-Based Processing ✅ DONE
**Field:** `rule_json → priority` (int)
Rules are applied in ascending priority order (lower number = applied first).
- Default: 10
- Rules with same priority: applied in creation order (ID ascending)

---

### 6.2 Stop Processing Further Rules ✅ DONE
**Field:** `rule_json → stacking.stop_processing` (bool)
When true, no rules with lower priority (higher number) are evaluated after this rule matches.

---

### 6.3 Exclusive Rule (Cannot Stack with Others) ✅ DONE
**Field:** `rule_json → stacking.can_stack_with_other_rules` (bool)
When false, if this rule applies, all other simultaneously applied rules are skipped.

---

### 6.4 Stacking Groups 📋 PLANNED
**Field:** `rule_json → stacking.group` (string — slug)
Allow rules in the same named group to stack, but prevent stacking between groups.
- Example: group `'seasonal'` rules stack with each other, but not with group `'loyalty'`
- In RuleEngine: track which groups have applied; if a rule's group is different from all applied groups, allow unless cross-group stacking is disabled
- Global setting: `wpulse_cross_group_stacking` (bool)

---

### 6.5 Coupon + Rule Stacking Control 📋 PLANNED
**Field:** `rule_json → stacking.allow_with_coupons` (bool, default true)
If false, this rule is skipped when any WooCommerce coupon is applied to the cart.
- Check `WC()->cart->get_applied_coupons()` is empty before applying rule
- Global setting option: `wpulse_rules_disable_with_any_coupon` (bool)

---

## PART 7 — FRONTEND DISPLAY

### 7.1 Single Product Page Discount Message ✅ DONE
**File:** `includes/Frontend/ProductDiscountMessage.php`
**Hook:** `woocommerce_single_product_summary` (priority 11)
Show a message below the product title/price on single product pages.
- Finds highest-priority matching rule using product-page context
- Auto-generates message from rule type or uses `meta.custom_message`
- Shortcodes: `[save_amount]`, `[save_percentage]`
- Controllable via `meta.show_badge` (bool)

---

### 7.2 Shop/Archive Loop Badge ✅ DONE
**File:** `includes/Frontend/ProductDiscountMessage.php`
**Hook:** `woocommerce_after_shop_loop_item_title` (priority 15)
Compact discount badge displayed under product title in shop/category loops.
- Only shown if `meta.show_on_shop = true`
- Styled as a pill badge (blue bg, white text)

---

### 7.3 Tiered Pricing Table on Product Page 📋 PLANNED
**File to create:** `includes/Frontend/PricingTable.php`
Display a quantity-discount table below the price on single product pages for tiered rules.
- Only renders for products matching a `tiered` or `bulk_fixed_price` rule
- Table rows: Quantity | Price per unit | You save
- Hook: `woocommerce_single_product_summary` (priority 25, after main message)
- Controlled by `meta.show_pricing_table` (bool)
- Responsive styling; should honor active theme CSS variables where possible
- Format example:
  ```
  | Qty   | Unit Price | Savings |
  |-------|------------|---------|
  | 1–4   | $10.00     | —       |
  | 5–9   | $9.00      | 10%     |
  | 10+   | $8.00      | 20%     |
  ```

---

### 7.4 Cart Item Discount Label 📋 PLANNED
**Description:** Show a small label next to each discounted item in the cart/checkout table.
- Hook: `woocommerce_cart_item_name` filter
- Append HTML badge: `<span class="wpulse-saved-label">Save 20%</span>`
- Only show if rule has `meta.show_cart_label = true`
- Must not break cart item subtotal display

---

### 7.5 Countdown Timer (Flash Sale) 📋 PLANNED
**Description:** Show a countdown timer on product page / shop when the rule has an `end` date.
- Rendered via a lightweight JS snippet (no heavy framework)
- Counts down to `schedule.end` in store timezone
- Configurable: `meta.show_countdown` (bool), `meta.countdown_label` (string)
- Auto-hides when timer reaches zero
- Example: "Flash sale ends in 2h 35m 10s"

---

### 7.6 Cart Notice / Promotional Banner 📋 PLANNED
**Description:** Display an informational notice in the cart when the customer is close to unlocking a discount.
- Hook: `woocommerce_before_cart_table`
- Example: "Spend $15 more to get 10% off your order!"
- Logic: find highest-priority rule with `cart_subtotal >= X` condition that is NOT yet active
- Compute gap: threshold - current subtotal
- Rule must have `meta.show_cart_notice = true`
- Admin: `meta.cart_notice_template` (string with `[amount_needed]` shortcode)

---

### 7.7 Discount Badge on Product Images 📋 PLANNED
**Description:** Overlay a "SALE" or "20% OFF" ribbon/badge on product images.
- Hook: `woocommerce_product_get_image` filter (or `post_thumbnail_html`)
- Only apply if a matching active rule exists for the product
- Badge content: auto-generated or `meta.badge_text` (string)
- Style: ribbon (top-left corner) or circle badge (top-right)
- `meta.badge_style` — `'ribbon'` | `'circle'` | `'none'`
- Must be lazy — don't query rules for every product image on a large shop page; use transients keyed by product_id

---

## PART 8 — ADMIN INTERFACE

### 8.1 Rules List Page ✅ DONE
**File:** `admin/views/RulesView.vue`
Table of all rules with Name, Type, Status, Priority columns.
Filters: by Type, by Status. Search by name. Bulk: enable/disable/delete.

---

### 8.2 Rule Editor ✅ DONE
**File:** `admin/views/RuleEditorView.vue`
Full rule configuration form with tabs: Discount Type, Targets, Conditions, Exclusions, Schedule, Advanced.

---

### 8.3 Template Modal ✅ DONE
**File:** `admin/components/TemplatesModal.vue`
11 pre-built templates + 6 "start from scratch" options for quick rule creation.

---

### 8.4 Global Exclusion Page ✅ DONE
**File:** `admin/views/ExclusionListView.vue`
Search + manage global exclusions (products, categories, tags).

---

### 8.5 Help Page ✅ DONE
**File:** `admin/views/HelpView.vue`
Video overview + quick-start guide.

---

### 8.6 Duplicate Rule 📋 PLANNED
**Description:** One-click rule duplication from the rules list.
- Button in each rule's action row: "Duplicate"
- REST endpoint: `POST /rules/{id}/duplicate`
- Creates a copy with name prefixed "Copy of …", status = `'draft'`, same priority
- Controller: RulesController — new `duplicate()` method
- Admin: add button to rule row actions in RulesView.vue

---

### 8.7 Import / Export Rules 📋 PLANNED
**Description:** Export rules to JSON file; import from JSON file.
- Export: `GET /rules/export` — returns JSON download of all (or selected) rules
- Import: `POST /rules/import` — accepts JSON body, validates schema, inserts rules as drafts
- Admin UI: "Export All" button + "Import" button with file picker in RulesView.vue
- Validation: check required fields, sanitize all values before insert
- Conflict resolution: imported rules always start as `draft`; IDs are reassigned

---

### 8.8 Rule Usage Analytics 📋 PLANNED
**Description:** Track and display how often each rule has applied and how much it has saved customers.
- Requires `wp_wpulse_rule_usage` table (see Feature 5.2)
- Admin UI: Analytics column in rules list (saves count, total discount amount)
- Detail view: "Rule Statistics" panel in rule editor showing time-series chart
- Hook: `woocommerce_checkout_order_processed` — record usage per rule per order
- Storage: `rule_id, order_id, user_id, discount_amount, applied_at`
- Summary query: `SUM(discount_amount)`, `COUNT(*)` grouped by rule_id

---

### 8.9 Plugin Settings Page 📋 PLANNED
**Description:** A global settings page at `Pricing Rules → Settings`.
Settings to include:
- `Exclude on-sale products from all rules` (bool) → `wpulse_exclude_on_sale`
- `Excluded user roles` (multi-select) → `wpulse_excluded_roles`
- `Disable rules when coupon applied` (bool) → `wpulse_disable_with_coupon`
- `Default rule priority` (int) → `wpulse_default_priority`
- `Cart notice display` (bool) → `wpulse_show_cart_notices`
- `Badge style` (select) → `wpulse_badge_style`
- `Currency display` (use WC currency settings — informational)
- Saved to `wp_options` via `update_option('wpulse_settings', $data)`
- REST endpoint: `GET/POST /settings`
- Admin Vue view: `admin/views/SettingsView.vue`

---

### 8.10 Rule Version History 📋 PLANNED
**Description:** Keep a changelog of edits to each rule.
- `wp_wpulse_rule_revisions` table: `{ id, rule_id, rule_json_snapshot, changed_by, changed_at }`
- On each `PUT /rules/{id}`, save current state as a revision before updating
- Admin UI: "History" tab in rule editor showing list of snapshots with timestamp + user
- "Restore" button to roll back to a previous version
- Keep max 20 revisions per rule (prune oldest on each save)

---

### 8.11 Drag-and-Drop Rule Priority 📋 PLANNED
**Description:** Reorder rules visually in the list by dragging rows — priority auto-updates.
- Use Vue Draggable (Sortable.js-based) in RulesView.vue
- After drag, send `POST /rules/reorder` with ordered array of IDs
- Backend updates `priority` field sequentially (10, 20, 30…)
- Only show drag handle when no filters/search are active

---

## PART 9 — REST API

### 9.1 Core CRUD Endpoints ✅ DONE
```
GET     /wp-json/wpulse-pricing-rules/v1/rules
POST    /wp-json/wpulse-pricing-rules/v1/rules
GET     /wp-json/wpulse-pricing-rules/v1/rules/{id}
PUT     /wp-json/wpulse-pricing-rules/v1/rules/{id}
DELETE  /wp-json/wpulse-pricing-rules/v1/rules/{id}
POST    /wp-json/wpulse-pricing-rules/v1/rules/from-template
GET     /wp-json/wpulse-pricing-rules/v1/templates
```

---

### 9.2 Editor Data Endpoints ✅ DONE
```
GET /editor/roles
GET /editor/users?search=
GET /editor/categories
GET /editor/tags
GET /editor/products?search=
GET /editor/variable-products?search=
GET /editor/variations?product_id=
```

---

### 9.3 Planned API Endpoints 📋 PLANNED
```
POST   /rules/{id}/duplicate          → Rule duplication (8.6)
GET    /rules/export                  → JSON export (8.7)
POST   /rules/import                  → JSON import (8.7)
POST   /rules/reorder                 → Bulk priority update (8.11)
GET    /rules/{id}/stats              → Usage analytics (8.8)
GET    /settings                      → Plugin settings (8.9)
POST   /settings                      → Save settings (8.9)
GET    /rules/{id}/revisions          → Version history list (8.10)
POST   /rules/{id}/revisions/{rev}/restore → Restore revision (8.10)
GET    /editor/attributes             → WC attribute taxonomies (3.6)
GET    /editor/attribute-terms?taxonomy= → Terms for attribute (3.6)
```

---

## PART 10 — INTEGRATIONS

### 10.1 WooCommerce HPOS Compatibility ✅ DONE
Declared via `before_woocommerce_init` → `FeaturesUtil::declare_compatibility('custom_order_tables', true)`.

---

### 10.2 WooCommerce Subscriptions Integration 📋 PLANNED
**Description:** Apply rules to WooCommerce Subscriptions products (renewal pricing, sign-up discounts).
- Detect if WC Subscriptions is active: `class_exists('WC_Subscriptions')`
- New condition type: `subscription_status` — `active`, `on-hold`, `cancelled`
- New condition: `subscription_product_in_cart` — cart contains a subscription product
- Discount on renewal orders: hook `wcs_renewal_order_created` to apply rule discounts
- Sign-up fee discount: target `_sign_up_fee` meta; new benefit field `apply_to_signup` (bool)

---

### 10.3 WooCommerce Points & Rewards 📋 PLANNED
**Description:** Conditionally apply rules based on customer reward points balance.
- Condition type: `reward_points`
- Operators: `>=`, `<=`
- Value: point threshold
- Check via WooCommerce Points and Rewards plugin API: `WC_Points_Rewards_Manager::get_users_points($user_id)`
- Guard with `class_exists('WC_Points_Rewards_Manager')` check

---

### 10.4 WPML / Polylang Multi-Language Support 📋 PLANNED
**Description:** Rule names and custom messages are translatable; product/category IDs remain global.
- Store `meta.custom_message` per language using `pll_register_string()` (Polylang) or `icl_register_string()` (WPML)
- In `ProductDiscountMessage`, retrieve translated string via `pll__()` / `icl_t()`
- Admin: language switcher in rule editor for translatable fields
- Product/category search in editor should show items in current admin language

---

### 10.5 Multi-Currency Support 📋 PLANNED
**Description:** Allow fixed-amount thresholds and discounts to be set per currency.
- Detect active currency from WooCommerce Currency Switcher (Aelia) or WPML WC Multilingual
- `benefit.amount_by_currency` (object): `{ USD: 10, GBP: 8, EUR: 9 }`
- `conditions.cart_subtotal.value_by_currency` (object) — per-currency thresholds
- Fallback: if currency not listed, convert from base currency using WC exchange rate
- Guard with detection of an active multi-currency plugin

---

### 10.6 Wholesale Suite / B2B Roles 📋 PLANNED
**Description:** Pre-built wholesale pricing mode using user roles + tiered pricing.
- New template: "Wholesale Tiered Pricing" — pre-configures role condition + tiered benefit
- New condition: `wholesale_level` — integrates with Wholesale Suite plugin's level system
- New target type: `wholesale_only` — quick toggle to limit rule to wholesale users
- Compatible with WooCommerce Wholesale Prices, WooCommerce B2B plugins

---

## PART 11 — PERFORMANCE & RELIABILITY

### 11.1 Session-Based Price Restore ✅ DONE
Original prices stored in WC session to prevent double-discounting on recalculation.

---

### 11.2 Cart Signature Recursion Guard ✅ DONE
Prevents infinite recalculation loops using a hash of cart contents.

---

### 11.3 Request-Scoped Caching ✅ DONE
`$decoded_rules`, `$customer_cache`, `$term_cache` arrays reset once per request.

---

### 11.4 Transient-Based Rule Caching 📋 PLANNED
**Description:** Cache the full decoded + filtered active rule list in a WordPress transient.
- Transient key: `wpulse_active_rules`
- TTL: 5 minutes (or cleared immediately on any rule save/delete)
- Skip transient if WP_DEBUG is true
- Clear on: `save_post`, `deleted_post` for rule post types, or any rule REST mutation
- Benefit: eliminates DB query on every cart calculation for stores with many rules

---

### 11.5 Background Rule Evaluation (Large Stores) 📋 PLANNED
**Description:** For stores with 50+ rules, precompute applicable rules per product using a WP-Cron job.
- WP-Cron job: `wpulse_precompute_rules` — runs every 15 minutes
- Stores results in transients keyed by `wpulse_product_{id}_rules`
- RuleEngine reads precomputed list before falling back to live evaluation
- Invalidated when rules are saved/deleted
- Admin: toggle in Settings; progress indicator showing last computed time

---

## PART 12 — DEVELOPER API

### 12.1 Filters for Rule Output 📋 PLANNED
Expose WordPress filters so developers can customize rule behavior without hacking core files.

```php
// Filter applicable rules before engine processes them
apply_filters('wpulse/active_rules', $rules, $context);

// Filter whether a rule condition passes
apply_filters('wpulse/condition_passes', $result, $condition, $context);

// Filter computed discount amount
apply_filters('wpulse/discount_amount', $amount, $rule, $product, $context);

// Filter frontend message
apply_filters('wpulse/discount_message', $message, $rule, $product_id);

// Filter cart notice message
apply_filters('wpulse/cart_notice', $notice, $rule, $gap_amount);
```

---

### 12.2 Action Hooks for Rule Events 📋 PLANNED
```php
// Fired when a rule is applied to a cart item
do_action('wpulse/rule_applied', $rule, $cart_item_key, $discount_amount);

// Fired when a free gift is added
do_action('wpulse/gift_added', $rule, $product_id, $quantity);

// Fired when free shipping is granted
do_action('wpulse/free_shipping_granted', $rule);

// Fired when a rule is skipped (with reason)
do_action('wpulse/rule_skipped', $rule, $reason, $context);
```

---

### 12.3 Custom Condition Type API 📋 PLANNED
**Description:** Allow third-party plugins to register custom condition types.
```php
// Register custom condition
add_filter('wpulse/condition_types', function($types) {
    $types['my_custom_condition'] = [
        'label'     => 'My Custom Condition',
        'operators' => ['=', 'in'],
        'evaluate'  => 'MyPlugin::evaluateCondition', // callable
    ];
    return $types;
});
```
- RuleEngine reads registered condition types; falls back to built-ins
- Callable receives `($condition_item, $context)` and returns bool
- Admin: condition types from filter automatically appear in editor dropdown

---

### 12.4 Custom Benefit Type API 📋 PLANNED
**Description:** Allow third-party plugins to register custom discount types.
```php
add_filter('wpulse/benefit_handlers', function($handlers) {
    $handlers['my_custom_discount'] = 'MyPlugin\Benefits\MyDiscount';
    return $handlers;
});
```
- Handler class must implement `static apply(array $row, array $rule_data, Context $context): void`
- Admin: custom types appear in the benefit type selector in the editor

---

## PART 13 — SECURITY

### 13.1 REST API Authentication ✅ DONE
WP nonce verification (`X-WP-Nonce` header) + `manage_woocommerce` capability check on all endpoints.

### 13.2 AJAX Security ✅ DONE
Nonce verification + capability check on all AJAX handlers.

### 13.3 SQL Injection Prevention ✅ DONE
All queries use `$wpdb->prepare()` with parameterized values.

### 13.4 XSS Prevention ✅ DONE
Output escaped with `wp_kses_post()`. Admin SPA uses Vue's default HTML escaping.

### 13.5 CSRF Protection ✅ DONE
All state-changing operations require nonce. WP REST API nonce auto-included in SPA via `wpApiSettings.nonce`.

### 13.6 Input Validation & Rate Limiting 📋 PLANNED
- Validate `rule_json` schema on `PUT /rules/{id}` — reject unknown keys, validate type constraints
- Rate limit rule-creation endpoint: max 100 rules per store (configurable via filter)
- Sanitize `benefit.percent` to 0–100 range; `benefit.amount` to positive float
- Reject negative `priority` values
- Log security events to WC logger: `wc_get_logger()->warning('wpulse', ...)`

---

## PART 14 — QUICK REFERENCE: IMPLEMENTING A NEW FEATURE

When implementing any `📋 PLANNED` feature, follow this checklist:

### Backend (PHP)
1. **New benefit type:** Create `includes/Engine/Benefits/NewType.php` implementing `static apply()`. Register in `RuleEngine.php`'s `$benefit_handlers` map.
2. **New condition type:** Add `case 'new_type':` block in `ConditionEvaluator::evaluateItem()`. Add to Context if new data needed.
3. **New target type:** Add handling in `TargetMatcher::lineMatchesTargets()` and `productPageMatchesTargets()`.
4. **New DB table:** Add `CREATE TABLE` in `Installer::createTables()`. Bump `WPULSE_DB_VERSION` constant. Create Repository class in `includes/DB/`.
5. **New REST endpoint:** Add route in `routes/api.php`. Add method to appropriate controller in `app/Http/Controllers/`.
6. **New setting:** Store in `wp_options` via `update_option('wpulse_settings', ...)`. Read with `get_option('wpulse_settings', [])`.

### Frontend (Vue 3)
7. **New condition in editor:** Add to condition type dropdown options in `RuleEditorView.vue`. Add value input component matching the new type.
8. **New benefit UI:** Add tab/section in benefit configuration panel in `RuleEditorView.vue`.
9. **New admin page:** Create `admin/views/NewView.vue`. Register route in `admin/router/index.js`. Add to `Sidebar.vue` nav.
10. **New API call:** Add function to `admin/api/index.js` using the existing fetch wrapper.

### Testing checklist
- Enable rule, add products to cart, verify discount applied correctly
- Test with guest (no user ID) and logged-in user
- Test with multiple rules — verify stacking/stop behavior
- Check product page message renders
- Check shop loop badge renders
- Test schedule: set past end date → rule should not apply
- Check no JS console errors in admin SPA

---

*Last updated: 2026-03-26 | Plugin version: 1.1.1*
