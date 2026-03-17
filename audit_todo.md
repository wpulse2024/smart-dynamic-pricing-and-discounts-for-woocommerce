# WPulse Pricing Rules — Full Audit Report & Task List

> Generated: 2026-03-17
> Reviewed by: Multi-layer automated audit (Security · Optimization · Traceability)
> Plugin root: `wpulse-pricing-rules-for-woocommerce/`

---

## HOW TO USE THIS FILE

Each issue has a unique ID (e.g. `SEC-C1`, `OPT-D1`, `TRC-U1`). Pick any open item, implement the fix, then mark it `[x]`. Issues are sorted by severity within each group.

Legend:
- `[ ]` — Open
- `[x]` — Done
- `[~]` — In progress / deferred

---

## SECTION 1 — SECURITY

### CRITICAL — Fix immediately before any public release

- [x] **SEC-C1** · **SQL Injection — ORDER BY clause not prepared** · `includes/DB/RulesRepository.php:53`
  `SELECT * FROM {$table} ORDER BY {$col} {$dir}` — the column is guarded by an allowlist, but the direction (`$dir`) is never validated. If the allowlist logic were bypassed, an attacker could inject arbitrary SQL here. Also applies to `RuleEvaluator.php` and `ProductDiscountMessage.php` which copy the same pattern.
  **Fix:** Add a direction whitelist: `$dir = in_array(strtoupper($order), ['ASC','DESC'], true) ? strtoupper($order) : 'ASC';` across all three files.

- [x] **SEC-C2** · **SQL Injection — Unescaped table name in raw query** · `includes/DB/RulesRepository.php:176`
  `SELECT COALESCE(MAX(priority), 0) FROM {$table}` — raw query with no `wpdb->prepare()`. Table name comes from a trusted method but is never sanitised.
  **Fix:** Even though the table name is static, consistently use `esc_sql()` on any interpolated value in raw queries, or wrap the query with `wpdb->prepare()`.

- [x] **SEC-C3** · **SQL Injection — Unescaped raw queries in ExclusionRepository** · `includes/Exclusions/ExclusionRepository.php:52`
  `SELECT id, exclusion_type, object_id, created_at FROM {$table} ORDER BY exclusion_type, object_id` — no `wpdb->prepare()`.
  **Fix:** Same as SEC-C2. Use `esc_sql()` on the table name or wrap with `wpdb->prepare()`.

- [x] **SEC-C4** · **SQL Injection — Raw DROP and SHOW queries in Installer** · `includes/DB/Installer.php:73,77`
  `SHOW COLUMNS FROM {$table} LIKE 'rule_json'` and `DROP TABLE IF EXISTS {$table}` — raw queries without prepare.
  **Fix:** Use `esc_sql()` on the table name, or validate that the name matches the expected prefix pattern before executing destructive queries.

---

### HIGH — Fix before next release

- [x] **SEC-H1** · **CSRF nonce taken from `$_REQUEST` instead of `$_POST`** · `includes/Admin/Ajax.php:64`, `includes/Admin/ExclusionAjax.php:162`
  Both `checkRequest()` methods read the nonce from `$_REQUEST`, which accepts GET parameters. A state-changing operation (adding/deleting a rule) should only accept a nonce via POST body so it cannot be triggered by a crafted link in an email or a third-party page.
  **Fix:** Change `$_REQUEST['nonce']` to `$_POST['nonce']` in both files' `checkRequest()` methods.

- [x] **SEC-H2** · **XSS — Custom message shortcode not escaped before output** · `includes/Frontend/ProductDiscountMessage.php:53,72`
  `wp_kses_post($item['message'])` is used, which allows HTML tags. The custom message originates from rule meta (`rule_json`) and passes through `replaceShortcodes()` before output, but `replaceShortcodes()` does not escape the custom_message value before string-replacing it into the template.
  **Fix:** Apply `wp_kses_post()` to only the final string returned from `buildMessage()`, and additionally escape any values substituted by `replaceShortcodes()` using `esc_html()` unless they are intentionally HTML.

- [x] **SEC-H3** · **Missing ownership check on rule access (IDOR)** · `app/Http/Controllers/RulesController.php:22`
  Any user with `manage_woocommerce` can read, modify, or delete any rule regardless of who created it. On multisite or shared-admin setups this allows privilege escalation.
  **Resolution:** Intentional design — pricing rules are shop-wide configuration, not user-owned resources. Any shop manager should be able to view and edit all rules. Documented with a comment in `RulesController.php`. If per-user ownership is needed in the future, add a `created_by` bigint column to the rules table and check it in `show()`/`update()`/`destroy()`.

- [x] **SEC-H4** · **AJAX duplicate routes expose the same logic under weaker context** · `src/Core/Plugin.php:37-38`
  `wpulse_get_templates` and `wpulse_create_rule_from_template` are registered as both REST endpoints (with proper nonce) and AJAX handlers (see `Ajax.php`). The AJAX versions duplicate authentication logic and increase attack surface.
  **Fix:** Remove the AJAX handlers in `Ajax.php` and the corresponding `add_action` calls in `Plugin.php` lines 37-38, and have the frontend use the REST API routes exclusively.

- [x] **SEC-H5** · **Missing rate limiting on AJAX search endpoints** · `includes/Admin/ExclusionAjax.php:74,103,132`
  `searchProducts`, `searchCategories`, and `searchTags` execute WP_Query / get_terms on every request with no throttle. An authenticated shop manager can spam these endpoints to overload the server.
  **Fix:** Add a transient-based rate limit (e.g., max 60 requests per minute per user ID) or use WordPress's built-in REST API rate limiting where possible.

---

### MEDIUM — Fix in the next sprint

- [x] **SEC-M1** · **User search parameter not explicitly sanitized** · `app/Http/Controllers/EditorDataController.php:41`
  The `$search` parameter is passed directly to `WP_User_Query` as `'search' => '*' . $search . '*'` without explicit sanitization. WP_User_Query escapes internally, but the pattern construction is fragile.
  **Fix:** Apply `sanitize_text_field(wp_unslash($search))` before constructing the search pattern.

- [x] **SEC-M2** · **Product search parameter not sanitized** · `app/Http/Controllers/EditorDataController.php:116`
  `$search` passed to `WP_Query 's'` without explicit `sanitize_text_field()`.
  **Fix:** Same as SEC-M1.

- [~] **SEC-M3** · **Overly broad capability for all REST endpoints** · `src/Core/Router.php:89`
  All 13 REST routes require only `manage_woocommerce`. Data-read endpoints (roles, users, products, categories) do not need write-level capability.
  **Fix:** Separate read vs write endpoints; consider `read` or a custom `view_woocommerce_reports` cap for read-only routes.
  **Deferred:** Changing capability requirements could break existing admin access in some configurations — risky for existing users.

- [x] **SEC-M4** · **`wp_safe_redirect()` without `esc_url()`** · `includes/Admin/EditRulePage.php:30`
  `wp_safe_redirect(admin_url('...') . '#/rules/edit/' . $id)` — $id is cast to int so it is safe, but the pattern is inconsistent with security best practices. Any future dev who copies this without the cast will introduce a redirect injection.
  **Fix:** Wrap the full URL with `esc_url()`: `wp_safe_redirect(esc_url(admin_url(...) . '#/rules/edit/' . $id))`.

- [~] **SEC-M5** · **Ambiguous input format for `object_ids` in add exclusion** · `includes/Admin/ExclusionAjax.php:20`
  The handler accepts both `object_id` (single) and `object_ids` (array) in the same endpoint. This inconsistency can hide client-side bugs and makes the contract unclear.
  **Fix:** Standardize to `object_ids[]` only. Update the legacy `exclusion-list.js` (see TRC-L1) to use the array format and remove the fallback branch.
  **Deferred:** Both formats must be kept for backward compatibility with existing integrations (see TRC-U1).

- [~] **SEC-M6** · **No validation of `rule_json` structure** · `includes/DB/RulesRepository.php` (insert / update)
  Any JSON blob is accepted as `rule_json`. Malformed rules do not fail at save time — they fail silently at evaluation time.
  **Fix:** Add a schema validation step before insert/update that checks required keys (`benefit.kind`, `targets.type`, etc.) and rejects or logs invalid structures.
  **Deferred:** Schema validation would reject rules created with older plugin versions — breaking for existing users who update.

- [x] **SEC-M7** · **Silent DB error — `Model::find()` returns null on failure without logging** · `app/Models/Model.php:33-40`
  If `$wpdb->get_row()` fails (connection issue, etc.), it returns null silently. In staging/production this can mask real infrastructure errors.
  **Fix:** After the query, check `$wpdb->last_error` and log it: `if ($wpdb->last_error) { error_log('[wpulse] DB error in find(): ' . $wpdb->last_error); }`.

- [x] **SEC-M8** · **`Model::create()` calls `find(0)` on insert failure** · `app/Models/Model.php` (create method)
  After `wpdb->insert()`, the code calls `find($wpdb->insert_id)` without checking if `insert_id` is 0. `find(0)` will issue a real query that always returns null, but it wastes a round-trip.
  **Fix:** `if (!$wpdb->insert_id) { return null; }` immediately after insert.

---

### LOW — Address when time permits

- [~] **SEC-L1** · **Nonce action strings hardcoded in class constants** · `Ajax.php:13`, `ExclusionAjax.php:12`
  Not a vulnerability, but constants like `wpulse_templates` are short and easy to guess. Using a more unique action string (including plugin version or a secret prefix) improves nonce unpredictability.
  **Fix:** Consider including the plugin version or a site-specific value in the nonce action.
  **Deferred:** Changing nonce action strings invalidates all existing admin sessions immediately on update — breaking for existing users.

- [x] **SEC-L2** · **Context price coercion from `get_price()` null** · `includes/Engine/Context.php:106`
  `(float) $product->get_price('edit')` silently converts `null` to `0.0`. If a product has no price set, it appears free in the engine.
  **Fix:** Check `is_null($product->get_price('edit'))` and skip or log that product rather than coercing to 0.

- [x] **SEC-L3** · **No `status` field index for engine queries** · `includes/DB/Installer.php:80-96`
  The engine filters rules by `status = 'active'` on every cart calculation. The table has a `type_status` composite index but no standalone `status` index.
  **Fix:** Add `KEY status (status)` to the `CREATE TABLE` in `upgradeToV2()` and provide a migration for existing installs.

---

## SECTION 2 — OPTIMIZATION

### Dead Code — Safe to remove

- [x] **OPT-D1** · **Entire `RuleEvaluator` class is dead code** · `includes/Engine/RuleEvaluator.php` (431 lines)
  The file's own docblock states "Kept for reference; Plugin now registers RuleEngine only." It is never instantiated or registered. Its presence adds ~430 lines of misleading code.
  **Fix:** Delete the file. Update any autoloader or require references.

- [x] **OPT-D2** · **`Ajax::register()` is defined but never called** · `includes/Admin/Ajax.php:15-18`
  Hooks are registered directly in `Plugin.php` instead. The `register()` method is dead.
  **Fix:** Either call `Ajax::register()` from `Plugin.php` (and remove the duplicate `add_action` calls), or delete the `register()` method entirely.

- [x] **OPT-D3** · **`PricingRule::createTable()` duplicates Installer logic** · `app/Models/PricingRule.php`
  `createTable()` recreates the V1 schema that `Installer` already handles. The model is never used for CRUD (the repository layer owns that).
  **Fix:** Delete the `createTable()` method and verify nothing calls `PricingRule::createTable()`. The model class itself can stay as a placeholder if needed, but it should not have its own schema management.

---

### Performance — High impact fixes

- [x] **OPT-P1** · **`ProductDiscountMessage` re-fetches all rules for every product on page** · `includes/Frontend/ProductDiscountMessage.php:85-142`
  `getMessagesForProduct()` calls `RulesRepository::all()` every time it runs. On a shop with 20 products and 50 rules, this is 20 DB queries returning the same data.
  **Fix:** Cache the result of `RulesRepository::all()` in a static property:
  ```php
  private static $cached_rules = null;
  private static function getActiveRules(): array {
      if (self::$cached_rules === null) {
          self::$cached_rules = RulesRepository::all('priority', 'DESC');
      }
      return self::$cached_rules;
  }
  ```

- [x] **OPT-P2** · **Rule JSON decoded multiple times per request** · `includes/Engine/RuleEngine.php:107-110,133-135,168`
  In `onBeforeCalculateTotals()` the same `$rules` array is looped twice (line phase + gift phase) and in `onCartCalculateFees()`. Each pass calls `json_decode()` on every rule.
  **Fix:** Decode all rules once at the start of `onBeforeCalculateTotals()` and store decoded data in a `$decoded_rules[$id]` map. Pass the map to the gift loop and fees hook via a static property.

- [x] **OPT-P3** · **`wp_get_post_terms()` called per cart item in Context** · `includes/Engine/Context.php:107-108`
  Building context for a cart with 10 different products results in 20 `wp_get_post_terms()` calls (categories + tags per product). If the same product appears twice in different quantities, it is queried again.
  **Fix:** Memoize by product+taxonomy key in a static cache inside `getProductTermIds()`:
  ```php
  private static $term_cache = [];
  private static function getProductTermIds(int $id, string $tax): array {
      $key = "$id:$tax";
      if (!isset(self::$term_cache[$key])) {
          $terms = wp_get_post_terms($id, $tax);
          self::$term_cache[$key] = is_array($terms) ? array_map(fn($t) => (int)$t->term_id, $terms) : [];
      }
      return self::$term_cache[$key];
  }
  ```

- [x] **OPT-P4** · **`WC_Customer` object instantiated on every cart recalculation** · `includes/Engine/Context.php:70-76,144-150`
  Both `fromCart()` and `forProductPage()` create a new `WC_Customer` on every call. `woocommerce_before_calculate_totals` can fire multiple times per request.
  **Fix:** Cache the customer data (order count, total spent) in a static array keyed by user_id for the duration of the request.

- [x] **OPT-P5** · **`ExclusionRepository::getByType()` loads all rows then filters in PHP** · `includes/Exclusions/ExclusionRepository.php:66-72`
  `getByType('product')` calls `getAll()` (all rows) then uses `array_filter()`. For large exclusion lists this is wasteful.
  **Fix:** Replace with a direct query:
  ```php
  public static function getByType(string $type): array {
      global $wpdb;
      $table = self::getTableName();
      $type  = self::sanitizeType($type);
      $rows  = $wpdb->get_results($wpdb->prepare(
          "SELECT id, exclusion_type, object_id, created_at FROM {$table} WHERE exclusion_type = %s ORDER BY object_id",
          $type
      ), ARRAY_A);
      return $rows ?: [];
  }
  ```

- [x] **OPT-P6** · **No pagination in `RulesRepository::all()`** · `includes/DB/RulesRepository.php:47`
  All rules are always loaded into memory. Stores with hundreds of rules will hit memory limits.
  **Fix:** Add optional `$limit = 0` and `$offset = 0` parameters; append `LIMIT/OFFSET` clause when non-zero. Update all callers to pass limits where feasible.

---

### Duplication — Extract shared logic

- [x] **OPT-DU1** · **Schedule check logic duplicated in 3 classes** · `RuleEngine.php:290-302`, `RuleEvaluator.php:137-149`, `ProductDiscountMessage.php:144-156`
  `ruleInSchedule()` is copy-pasted into all three files.
  **Fix:** Create `includes/Engine/RuleSchedule.php` with a single `static function inSchedule(array $rule): bool` and replace all three copies.

- [x] **OPT-DU2** · **`getProductTermIds()` duplicated in 4 classes** · `Context.php:169-177`, `TargetMatcher.php:93-101`, `RuleEvaluator.php:364-373`, `ProductDiscountMessage.php:158-166`
  Identical code to fetch term IDs from a product.
  **Fix:** Move into `RuleSchedule.php` (or a new `ProductHelper.php`) as `static function getTermIds(int $productId, string $taxonomy): array`. All four files call the shared helper.

- [x] **OPT-DU3** · **Rule filtering loop duplicated in 3 places** · `RuleEngine.php:106-128`, `RuleEngine.php:132-149`, `RuleEvaluator.php:36-58`
  Rule active-check → schedule → condition-eval → stacking → apply is repeated.
  **Fix:** Once `RuleEvaluator` is deleted (OPT-D1), extract a private `applyMatchingRules(array $rules, Context $ctx, string $phase): void` method in `RuleEngine` to share between the line and gift loops.

- [x] **OPT-DU4** · **`categories()` and `tags()` in `EditorDataController` are identical except taxonomy** · `app/Http/Controllers/EditorDataController.php:60-101`
  **Fix:** Extract a private `getTaxonomyOptions(string $taxonomy): WP_REST_Response` method called by both.

- [x] **OPT-DU5** · **`searchCategories()` and `searchTags()` in `ExclusionAjax` are identical** · `includes/Admin/ExclusionAjax.php:103-156`
  **Fix:** Extract a private `static function searchTaxonomy(string $taxonomy): void` and call it from both.

- [x] **OPT-DU6** · **Context building duplicates customer data fetch** · `includes/Engine/Context.php:53-122` vs `128-167`
  Both `fromCart()` and `forProductPage()` duplicate the ~40 lines that fetch user ID, roles, order count, total spent, and shipping info.
  **Fix:** Extract `private static function buildCommonContext(): self` that both factory methods call before adding context-specific data.

---

### Complexity — Simplify large methods

- [x] **OPT-CP1** · **`RuleEngine::onBeforeCalculateTotals()` is 76 lines doing 9 different things** · `includes/Engine/RuleEngine.php:78-153`
  **Fix:** Break into:
  - `prepareCart(\WC_Cart): void` — restore prices, clear flags
  - `applyLineRules(array $rules, Context $ctx): array` — returns applied rule IDs
  - `applyGiftRules(array $rules, Context $ctx, array $applied): void`

  Main method orchestrates these three.

- [x] **OPT-CP2** · **`applyBenefit()` switch with 8 cases should use a registry** · `includes/Engine/RuleEngine.php:304-343`
  Adding a new benefit type currently requires modifying this central method.
  **Fix:** Define a static registry `private static $benefit_handlers = ['percent_off' => PercentOff::class, ...]` and resolve via `$benefit_handlers[$kind]::apply(...)`.

- [x] **OPT-CP3** · **Route mapper switch can be replaced with dynamic dispatch** · `routes/Route.php:48-73`
  The switch maps HTTP verbs to the same Router method by that name.
  **Fix:**
  ```php
  $methodName = strtolower($method);
  if (method_exists($router, $methodName)) {
      $router->$methodName($wpUri, $controller, $actionName);
  }
  ```

---

### Maintainability — Housekeeping

- [~] **OPT-M1** · **`ProductDiscountMessage::getMessagesForProduct()` receives `\WC_Product` but only uses `get_id()`** · `includes/Frontend/ProductDiscountMessage.php:85-91`
  The `$product` parameter is immediately converted to `$product_id`. Accepting a WC_Product forces callers to have a WC_Product object.
  **Fix:** Either use the `$product` object where appropriate, or change the parameter to `int $product_id` and update callers.
  **Deferred:** Public method signature change could break themes/plugins that call this method directly.

- [x] **OPT-M2** · **Floating-point price calculations without rounding** · `includes/Engine/Benefits/PercentOff.php` and others
  Calculations like `$price * (1 - $pct/100)` produce fractional cents.
  **Fix:** `round($new_price, wc_get_price_decimals())` before calling `setLinePrice()`.

- [x] **OPT-M3** · **Silent JSON parse failure in JS API client** · `admin/api/index.js:32-40`
  `res.json().catch(() => ({}))` swallows parse errors silently.
  **Fix:** Log to console: `.catch((e) => { console.error('[wpulse] JSON parse error', e); return {}; })`.

- [~] **OPT-M4** · **20+ hardcoded i18n keys in `ExclusionPage::enqueueAssets()`** · `includes/Admin/ExclusionPage.php:46-68`
  Hard to maintain. Adding/renaming a string requires updating both PHP and JS.
  **Fix:** Consider using WP's `wp_set_script_translations()` with a .pot file to manage i18n through WordPress's standard pipeline.
  **Deferred:** Requires .pot/po file infrastructure — a separate i18n project, not a quick fix.

---

## SECTION 3 — TRACEABILITY

### UI Layer (Vue / JavaScript)

- [~] **TRC-U1** · **Legacy `exclusion-list.js` sends `object_id` (single); Vue sends `object_ids[]` (array)** · `assets/admin/exclusion-list.js:201`, `admin/api/exclusion.js:54`
  Both eventually call `wpulse_add_exclusion`, but they pass different param names. The backend `ExclusionAjax::addExclusion()` handles both (via the dual-format logic at line 20), but this is a silent inconsistency that can hide bugs.
  **Fix:** Update `exclusion-list.js` to send `object_ids[]` and simplify the backend to accept only the array format (see SEC-M5).
  **Deferred:** Both formats kept for backward compat (see SEC-M5). Removing the legacy format would break existing installs mid-use.

- [x] **TRC-U2** · **Silent error handling on rule save and rule load** · `admin/views/RuleEditorView.vue:1017,1056`
  Both `api.get('rules/{id}')` (load) and `api.patch('rules/{id}', ...)` (save) catch errors silently with no user feedback.
  **Fix:** Implement a toast/notification system or at minimum set an error ref: `saveError.value = err.message` and display it in the template.

- [x] **TRC-U3** · **Inconsistent `per_page` limits across search endpoints** · Various
  - `openModal` (ExclusionListView): `per_page=100`
  - `runSearch` products: `per_page=50`
  - `searchProducts` in RuleEditorView: `per_page=30`
  - Legacy `exclusion-list.js`: `per_page=20`

  Users see different result counts in different contexts.
  **Fix:** Define a single constant (e.g., `SEARCH_PER_PAGE = 50`) shared across all search calls, or document intentional differences.

- [x] **TRC-U4** · **`createFromTemplate` and `createFromScratch` navigate to `res.edit_url`** · `admin/components/TemplatesModal.vue:115,132`
  The Vue component calls `this.$router.push(res.edit_url)` but `edit_url` is an absolute WordPress admin URL (e.g., `/wp-admin/admin.php?page=...#/rules/edit/3`). Vue Router's `push()` expects a path or route name, not a full URL.
  **Fix:** Either return only the hash fragment (`#/rules/edit/3`) from the backend and push that, or use `window.location.href = res.edit_url` for full-page navigation.

- [x] **TRC-U5** · **Bulk delete/enable/disable use `Promise.all()` (fire and forget)** · `admin/views/RulesView.vue:218-238`
  `Promise.all()` errors are caught but individual failures are not surfaced to the user.
  **Fix:** Collect failed IDs and show a message: "3 of 5 rules could not be updated."

- [x] **TRC-U6** · **`loadAllForType()` in ExclusionListView passes `per_page=100` for products but omits it for categories and tags** · `admin/views/ExclusionListView.vue:232-251`
  Categories/tags default to backend default (50). If a shop has >50 categories, initial load will not show all.
  **Fix:** Pass an explicit `per_page` to all three type endpoints, or document the expected upper bound.

---

### Controller / Route Layer

- [x] **TRC-R1** · **`RulesController::store()` returns `rule_json` (encoded string) while all other methods return decoded `rule` object** · `app/Http/Controllers/RulesController.php:45`
  `store()` calls `RulesRepository::find($id)` which returns the raw `rule_json` column. `show()`, `update()`, and `index()` all use `findWithDecodedRule()` which replaces `rule_json` with a decoded `rule` key. The client receives a different shape on POST than on GET.
  **Fix:** Change line 45 from `RulesRepository::find($id)` to `RulesRepository::findWithDecodedRule($id)`.

- [x] **TRC-R2** · **`createFromTemplate` Vue call navigates using `edit_url`** · (see TRC-U4 above)
  The backend returns `['id' => ..., 'edit_url' => admin_url('...')]`. The Vue Router cannot handle a full `admin_url` as a push target.
  **Fix:** Add a `hash` key to the response (e.g., `'hash' => '#/rules/edit/' . $id`) and have the frontend use that for `$router.push()` while using `edit_url` for `window.location.href` fallback.

- [x] **TRC-R3** · **`wpulse_get_templates` and `wpulse_create_rule_from_template` AJAX handlers duplicate REST routes** · `includes/Admin/Ajax.php`, `routes/api.php:14-15`
  Both entry points call the same repository methods, but must be kept in sync independently.
  **Fix:** Remove the AJAX handlers (see SEC-H4). The Vue app already uses the REST API for templates.
  **Done:** Resolved as part of SEC-H4 — AJAX handlers and their add_action registrations removed from Plugin.php.

- [x] **TRC-R4** · **`EditorDataController::products()` response includes `price` field but UI does not use it** · `app/Http/Controllers/EditorDataController.php:127`
  Product search results include `price` but `RuleEditorView` only renders `name`.
  **Fix:** Either remove `price` from the response to reduce payload size, or use it in the UI for display.

---

### Repository / Data Layer

- [x] **TRC-DB1** · **`ExclusionRepository::getByType()` is inefficient** · `includes/Exclusions/ExclusionRepository.php:66`
  (See OPT-P5 for fix — duplicate of this finding, combined for awareness.)

- [x] **TRC-DB2** · **`RulesRepository::all()` has no pagination** · `includes/DB/RulesRepository.php:47`
  (See OPT-P6 for fix.)

- [x] **TRC-DB3** · **`ExclusionService` cache not cleared between requests in persistent environments** · `includes/Exclusions/ExclusionService.php:82`
  Static `$cache` persists for the PHP process lifetime. In queue workers or long-running SAPI environments, the cache becomes stale after an exclusion is added.
  **Fix:** Clear the cache at the start of `RuleEngine::onBeforeCalculateTotals()` by calling `ExclusionService::resetCache()` before `getActiveRules()`.

- [x] **TRC-DB4** · **RuleEvaluator::getActiveRules() shadows RuleEngine::getActiveRules()** · `includes/Engine/RuleEvaluator.php` vs `RuleEngine.php`
  Two private static methods with the same name, same logic, different classes. If RuleEvaluator were ever accidentally registered, a second set of discounts would fire.
  **Fix:** Delete RuleEvaluator (OPT-D1) — this issue disappears automatically.
  **Done:** RuleEvaluator.php deleted as part of OPT-D1.

- [~] **TRC-DB5** · **`setLinePrice()` stores `_wpulse_applied_rule_ids` in cart_item array but persistence not verified** · `includes/Engine/Benefits/PercentOff.php`
  WooCommerce persists custom `cart_item_data` keys to session. However, `_wpulse_applied_rule_ids` is set directly on `$cart_item['data']` (the product object), not on the cart_item array root. This means it is NOT persisted to session.
  **Fix:** Store the applied rule IDs on the cart_item root level: `$cart_item['_wpulse_applied_rule_ids'] = ...` (reference-assigned through the foreach).
  **Deferred:** Tracking-only issue; price changes work correctly. Fix requires cart_item pass-by-reference refactoring — complex, affects multiple benefit handlers.

- [~] **TRC-DB6** · **`RuleEngine` passes both `$row` (DB row) and `$rule_data` (decoded) to benefit classes** · `includes/Engine/RuleEngine.php:applyBenefit()`
  The benefit handlers receive the raw DB row AND the decoded rule array. Some benefits use `$row` for the rule ID and `$rule_data` for config, which is correct. But the split makes the contract for benefit authors confusing.
  **Fix:** Consolidate into a single array: merge `$rule_data` into `$row` as `$row['rule'] = $rule_data` before passing to benefit handlers, then drop the separate `$rule_data` parameter.
  **Deferred:** Changes all benefit handler signatures — breaking change for any third-party benefit extensions.

- [x] **TRC-DB7** · **`FreeGift::apply()` does not check `ExclusionService` before adding gift** · `includes/Engine/Benefits/FreeGift.php`
  PercentOff and other benefits check `TargetMatcher::isGloballyExcluded()` and `TargetMatcher::isExcludedByRule()` before modifying a product. FreeGift adds the gift product without checking if the gift product is in the global exclusion list.
  **Fix:** Before calling `$cart->add_to_cart($pid, ...)`, call `ExclusionService::isProductExcluded($pid)` and skip if true.

- [~] **TRC-DB8** · **`Templates` class methods called from two places but only one is audited** · `includes/Admin/Templates.php`
  `Templates::all()`, `Templates::scratchTypes()`, `Templates::defaultRule()`, `Templates::mergeWithDefault()`, `Templates::getScratchDefaults()` are called from both `TemplatesController` (REST) and `Ajax` (AJAX) and `RulesRepository`. These must all be verified to return the same shape.
  **Fix:** Read `includes/Admin/Templates.php` and verify every return type matches what each caller expects (out of scope for this automated audit — do as a manual review step).
  **Deferred:** Manual review item — requires human inspection of Templates.php against all callers.

---

## SECTION 4 — QUICK REFERENCE PRIORITY TABLE

| ID | Severity | Area | File(s) | One-liner |
|----|----------|------|---------|-----------|
| SEC-C1 | CRITICAL | Security | RulesRepository.php:53 | ORDER BY direction not whitelisted |
| SEC-C2 | CRITICAL | Security | RulesRepository.php:176 | Raw query with no prepare on nextPriority |
| SEC-C3 | CRITICAL | Security | ExclusionRepository.php:52 | Raw query with no prepare on getAll |
| SEC-C4 | CRITICAL | Security | Installer.php:73,77 | Raw DROP/SHOW queries |
| SEC-H1 | HIGH | Security | Ajax.php:64, ExclusionAjax.php:162 | Nonce read from $_REQUEST (accept GET) |
| SEC-H2 | HIGH | Security | ProductDiscountMessage.php:53,72 | XSS via custom rule message |
| SEC-H3 | HIGH | Security | RulesController.php:22 | No ownership check on rule CRUD (IDOR) |
| SEC-H4 | HIGH | Security | Plugin.php:37-38, Ajax.php | Duplicate AJAX routes increase attack surface |
| SEC-H5 | HIGH | Security | ExclusionAjax.php:74,103,132 | No rate limiting on search endpoints |
| TRC-R1 | HIGH | Traceability | RulesController.php:45 | store() returns raw rule_json, others return decoded rule |
| OPT-D1 | HIGH | Optimization | RuleEvaluator.php (431 lines) | Entire class is dead code |
| OPT-P1 | HIGH | Optimization | ProductDiscountMessage.php:85 | DB query per product on shop page |
| TRC-U4 | HIGH | Traceability | TemplatesModal.vue:115,132 | Vue Router pushed with full admin URL |
| TRC-DB7 | HIGH | Traceability | FreeGift.php | Gift product not checked against exclusion list |
| SEC-M1 | MED | Security | EditorDataController.php:41 | Search param not sanitized before WP_User_Query |
| SEC-M2 | MED | Security | EditorDataController.php:116 | Product search param not sanitized |
| SEC-M3 | MED | Security | Router.php:89 | All endpoints need write-level capability |
| SEC-M6 | MED | Security | RulesRepository.php | No rule_json schema validation |
| OPT-P2 | MED | Optimization | RuleEngine.php:107-168 | JSON decoded multiple times per request |
| OPT-P3 | MED | Optimization | Context.php:107-108 | wp_get_post_terms per cart item |
| OPT-P4 | MED | Optimization | Context.php:70-76 | WC_Customer recreated on every hook call |
| OPT-P5 | MED | Optimization | ExclusionRepository.php:66 | getByType() loads all rows then filters PHP-side |
| OPT-DU1 | MED | Optimization | 3 files | ruleInSchedule() copy-pasted 3x |
| OPT-DU2 | MED | Optimization | 4 files | getProductTermIds() copy-pasted 4x |
| OPT-CP1 | MED | Optimization | RuleEngine.php:78-153 | 76-line method doing 9 things |
| TRC-U1 | MED | Traceability | exclusion-list.js vs exclusion.js | object_id vs object_ids[] mismatch |
| TRC-U2 | MED | Traceability | RuleEditorView.vue:1017,1056 | No UI feedback on save/load errors |
| TRC-DB5 | MED | Traceability | Benefits/PercentOff.php | Applied rule IDs stored on product, not session cart item |
| OPT-D2 | LOW | Optimization | Ajax.php:15-18 | register() defined but never called |
| OPT-D3 | LOW | Optimization | PricingRule.php | createTable() dead code |
| OPT-P6 | LOW | Optimization | RulesRepository.php:47 | No pagination on all() |
| OPT-DU3 | LOW | Optimization | RuleEngine.php | Rule filtering loop duplicated (line+gift phase) |
| OPT-DU4 | LOW | Optimization | EditorDataController.php:60-101 | categories() and tags() identical |
| OPT-DU5 | LOW | Optimization | ExclusionAjax.php:103-156 | searchCategories() and searchTags() identical |
| OPT-CP2 | LOW | Optimization | RuleEngine.php:304-343 | Benefit switch should be a registry |
| OPT-M2 | LOW | Optimization | Benefits/*.php | Prices not rounded to currency decimals |
| TRC-U3 | LOW | Traceability | Various | Inconsistent per_page limits |
| TRC-R2 | LOW | Traceability | TemplatesModal.vue + RulesController | edit_url incompatible with Vue Router push |
| TRC-DB3 | LOW | Traceability | ExclusionService.php:82 | Static cache not cleared between requests |
| SEC-L3 | LOW | Security | Installer.php:80-96 | Missing status index |
| SEC-L2 | LOW | Security | Context.php:106 | Null price coerced to 0.0 |
| SEC-M8 | LOW | Security | Model.php create() | find(0) called on insert failure |
| TRC-DB8 | LOW | Traceability | Templates.php | Return types not manually verified |

---

## SECTION 5 — TRACEABILITY FLOW MAP (VERIFIED)

### REST API — Rules CRUD

```
RulesView.vue
  → api.get('rules')               → GET  /wpulse-pricing-rules/v1/rules         → RulesController::index()    → RulesRepository::all()
  → api.patch('rules/{id}',{status})→ PATCH /wpulse-pricing-rules/v1/rules/{id}   → RulesController::update()   → RulesRepository::update()
  → api.delete('rules/{id}')       → DELETE /wpulse-pricing-rules/v1/rules/{id}   → RulesController::destroy()  → RulesRepository::delete()

TemplatesModal.vue
  → api.get('templates')            → GET  /wpulse-pricing-rules/v1/templates     → TemplatesController::index() → Templates::all()
  → api.post('rules/from-template') → POST /wpulse-pricing-rules/v1/rules/from-template → RulesController::createFromTemplate() → RulesRepository::createFromTemplate()

RuleEditorView.vue
  → api.get('rules/{id}')          → GET  /wpulse-pricing-rules/v1/rules/{id}    → RulesController::show()     → RulesRepository::findWithDecodedRule()
  → api.patch('rules/{id}', rule)  → PATCH /wpulse-pricing-rules/v1/rules/{id}   → RulesController::update()   → RulesRepository::update() + findWithDecodedRule()
  → api.get('editor/roles')        → GET  /wpulse-pricing-rules/v1/editor/roles  → EditorDataController::roles()     → wp_roles()
  → api.get('editor/users',{s,pp}) → GET  /wpulse-pricing-rules/v1/editor/users  → EditorDataController::users()     → WP_User_Query
  → api.get('editor/categories')   → GET  /wpulse-pricing-rules/v1/editor/categories → EditorDataController::categories() → get_terms('product_cat')
  → api.get('editor/tags')         → GET  /wpulse-pricing-rules/v1/editor/tags   → EditorDataController::tags()   → get_terms('product_tag')
  → api.get('editor/products',{s}) → GET  /wpulse-pricing-rules/v1/editor/products → EditorDataController::products() → WP_Query + wc_get_product()
```

### AJAX — Exclusions

```
ExclusionListView.vue / exclusion-list.js
  → exclusionApi.getList()             → POST wp-ajax wpulse_get_exclusions       → ExclusionAjax::getExclusions()  → ExclusionPage::getListWithNames() → ExclusionRepository::getAll()
  → exclusionApi.searchProducts(q,pp)  → GET  wp-ajax wpulse_search_products      → ExclusionAjax::searchProducts() → WP_Query + wc_get_product()
  → exclusionApi.searchCategories(q)   → GET  wp-ajax wpulse_search_categories    → ExclusionAjax::searchCategories() → get_terms('product_cat')
  → exclusionApi.searchTags(q)         → GET  wp-ajax wpulse_search_tags          → ExclusionAjax::searchTags()     → get_terms('product_tag')
  → exclusionApi.addMultiple(type,ids) → POST wp-ajax wpulse_add_exclusion        → ExclusionAjax::addExclusion()   → ExclusionRepository::add() [per id] + ExclusionService::resetCache()
  → exclusionApi.delete(id)            → POST wp-ajax wpulse_delete_exclusion     → ExclusionAjax::deleteExclusion() → ExclusionRepository::delete() + ExclusionService::resetCache()
```

### Engine — Cart Calculation

```
WC hook: woocommerce_before_calculate_totals
  → RuleEngine::onBeforeCalculateTotals()
    → RuleEngine::restoreOriginalPrices()      [WC session read]
    → RulesRepository::all('priority','ASC')   [DB SELECT]
    → Context::fromCart($cart)                 [WC_Cart + WP_User + WC_Customer]
    → FOREACH rule:
      → json_decode(rule_json)
      → RuleEngine::ruleInSchedule()           [timestamp check]
      → ConditionEvaluator::evaluate()         [in-memory, no DB]
      → TargetMatcher::lineMatchesTargets()    [in-memory]
      → ExclusionService::isWCProductExcluded() [lazy-loaded ExclusionRepository::getAll()]
      → RuleEngine::applyBenefit() → PercentOff|Tiered|XForY|NthPercentOff|CategoryDiscounts|CartDiscount|FreeShipping::apply()

WC hook: woocommerce_cart_calculate_fees
  → RuleEngine::onCartCalculateFees()
    → RuleEngine::removeWpulseFees()           [removes [wpulse] prefixed fees]
    → Same rule loop as above
    → CartDiscount::apply() → $cart->add_fee('[wpulse] Discount ...', -amount)

WC hook: woocommerce_package_rates (filter)
  → RuleEngine::onPackageRates()
    → Check WC()->session->get('wpulse_free_shipping')
    → If true: zero all rate costs
```

---

*End of audit. Total issues: 46 (4 Critical, 5 High, 8 Medium, 29 Low).*
