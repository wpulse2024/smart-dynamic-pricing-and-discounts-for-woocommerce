<template>
  <div class="rp-page">

    <!-- ── Loading ── -->
    <div v-if="loading" class="rp-state">
      <div class="rp-spinner"></div>
      <p class="rp-state__text">Loading rule…</p>
    </div>

    <!-- ── Not found ── -->
    <div v-else-if="!rule" class="rp-state rp-state--error">
      <svg class="rp-state__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <p class="rp-state__text">{{ loadError || 'Rule not found.' }}</p>
      <router-link to="/rules" class="rp-btn rp-btn--outline rp-btn--sm">Back to rules</router-link>
    </div>

    <!-- ── Editor ── -->
    <template v-else>

      <!-- Header -->
      <div class="rp-header">
        <router-link to="/rules" class="rp-header__back">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
          Pricing Rules
        </router-link>
        <div class="rp-header__row">
          <h1 class="rp-header__title">{{ form.name || 'Untitled Rule' }}</h1>
          <span :class="['rp-status-pill', `rp-status-pill--${form.status}`]">
            {{ form.status === 'active' ? 'Active' : form.status === 'disabled' ? 'Disabled' : 'Draft' }}
          </span>
        </div>
        <div v-if="saveError" class="rp-alert rp-alert--error">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          {{ saveError }}
        </div>
      </div>

      <!-- Two-column layout -->
      <div class="rp-layout">

        <!-- ─────────── Main column ─────────── -->
        <div class="rp-layout__main">

          <!-- 1 · Discount Type -->
          <section class="rp-card">
            <div class="rp-card__header">
              <div class="rp-card__header-icon rp-card__header-icon--blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
              </div>
              <h2 class="rp-card__title">Discount Type</h2>
            </div>
            <div class="rp-card__body">

              <!-- Visual type selector grid -->
              <div class="rp-type-grid">
                <button v-for="t in discountTypes" :key="t.value" type="button"
                  :class="['rp-type-btn', { 'rp-type-btn--active': benefitKind === t.value }]"
                  @click="benefitKind = t.value"
                >
                  <div class="rp-type-btn__icon-wrap">
                    <span class="rp-type-btn__icon" v-html="t.icon"></span>
                  </div>
                  <div class="rp-type-btn__content">
                    <span class="rp-type-btn__label">{{ t.label }}</span>
                    <span class="rp-type-btn__sub">{{ t.sub }}</span>
                  </div>
                </button>
              </div>

              <!-- Config panel per type -->
              <div class="rp-config-panel">

                <!-- percent_off / nth_percent_off -->
                <template v-if="benefitKind === 'percent_off' || benefitKind === 'nth_percent_off'">
                  <div class="rp-field-group rp-field-group--inline">
                    <template v-if="benefitKind === 'nth_percent_off'">
                      <div class="rp-field">
                        <label class="rp-field__label">Every Nth unit</label>
                        <div class="rp-input-affixed">
                          <input v-model.number="form.rule.benefit.nth" type="number" class="rp-input" min="1" placeholder="2" />
                          <span class="rp-input-affixed__suf">th</span>
                        </div>
                      </div>
                    </template>
                    <div class="rp-field">
                      <label class="rp-field__label">Discount</label>
                      <div class="rp-input-affixed">
                        <input v-model.number="form.rule.benefit.percent" type="number" class="rp-input" min="0" max="100" placeholder="20" />
                        <span class="rp-input-affixed__suf">%</span>
                      </div>
                    </div>
                  </div>
                </template>

                <!-- fixed_off / cart_fixed_off -->
                <template v-else-if="benefitKind === 'fixed_off' || benefitKind === 'cart_fixed_off'">
                  <div class="rp-field">
                    <label class="rp-field__label">Amount off</label>
                    <div class="rp-input-affixed">
                      <span class="rp-input-affixed__pre">$</span>
                      <input v-model.number="form.rule.benefit.amount" type="number" class="rp-input" min="0" step="0.01" placeholder="10.00" />
                    </div>
                  </div>
                </template>

                <!-- cart_percent_off -->
                <template v-else-if="benefitKind === 'cart_percent_off'">
                  <div class="rp-field">
                    <label class="rp-field__label">Cart discount</label>
                    <div class="rp-input-affixed">
                      <input v-model.number="form.rule.benefit.percent" type="number" class="rp-input" min="0" max="100" placeholder="10" />
                      <span class="rp-input-affixed__suf">% off entire cart</span>
                    </div>
                  </div>
                </template>

                <!-- x_for_y -->
                <template v-else-if="benefitKind === 'x_for_y'">
                  <div class="rp-field-group rp-field-group--inline rp-field-group--aligned">
                    <div class="rp-field">
                      <label class="rp-field__label">Customer buys</label>
                      <div class="rp-input-affixed">
                        <input v-model.number="form.rule.benefit.buy_qty" type="number" class="rp-input" min="1" placeholder="3" />
                        <span class="rp-input-affixed__suf">units</span>
                      </div>
                    </div>
                    <div class="rp-field-group__sep">and pays for</div>
                    <div class="rp-field">
                      <label class="rp-field__label">Customer pays for</label>
                      <div class="rp-input-affixed">
                        <input v-model.number="form.rule.benefit.pay_qty" type="number" class="rp-input" min="0" placeholder="2" />
                        <span class="rp-input-affixed__suf">units</span>
                      </div>
                    </div>
                  </div>
                </template>

                <!-- tiered -->
                <template v-else-if="benefitKind === 'tiered'">
                  <div class="rp-field">
                    <label class="rp-field__label">Quantity tiers</label>
                    <div class="rp-tier-table-wrap">
                      <table class="rp-tier-table">
                        <thead>
                          <tr>
                            <th>Min qty</th>
                            <th>Max qty <span class="rp-tier-table__hint">(0 = no limit)</span></th>
                            <th>% off</th>
                            <th>Fixed off</th>
                            <th></th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="(tier, idx) in form.rule.benefit.tiers" :key="idx">
                            <td><input v-model.number="tier.min" type="number" min="0" class="rp-input rp-input--xs" placeholder="1" /></td>
                            <td><input v-model.number="tier.max" type="number" min="0" class="rp-input rp-input--xs" placeholder="0" /></td>
                            <td>
                              <div class="rp-input-affixed">
                                <input v-model.number="tier.percent_off" type="number" min="0" max="100" class="rp-input rp-input--xs" placeholder="10" />
                                <span class="rp-input-affixed__suf">%</span>
                              </div>
                            </td>
                            <td>
                              <div class="rp-input-affixed">
                                <span class="rp-input-affixed__pre">$</span>
                                <input v-model.number="tier.fixed_off" type="number" min="0" step="0.01" class="rp-input rp-input--xs" placeholder="0" />
                              </div>
                            </td>
                            <td>
                              <button type="button" class="rp-icon-btn rp-icon-btn--danger" @click="removeTier(idx)" aria-label="Remove tier">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                              </button>
                            </td>
                          </tr>
                          <tr v-if="!form.rule.benefit.tiers.length">
                            <td colspan="5" class="rp-tier-table__empty">No tiers yet — add one below.</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                    <button type="button" class="rp-btn rp-btn--outline rp-btn--sm" @click="addTier">+ Add tier</button>
                  </div>
                </template>

                <!-- category_discounts -->
                <template v-else-if="benefitKind === 'category_discounts'">
                  <div class="rp-field">
                    <label class="rp-field__label">Category rules</label>
                    <div class="rp-cat-rows">
                      <div v-for="(cd, idx) in form.rule.benefit.category_discounts" :key="idx" class="rp-cat-row">
                        <select v-model="cd.apply_type" class="rp-select rp-select--sm">
                          <option value="percent">% discount of</option>
                          <option value="fixed">Fixed amount of</option>
                        </select>
                        <div class="rp-input-affixed">
                          <span v-if="cd.apply_type === 'fixed'" class="rp-input-affixed__pre">$</span>
                          <input v-model.number="cd.value" type="number" class="rp-input rp-input--xs" min="0" :step="cd.apply_type === 'percent' ? 1 : 0.01" placeholder="10" />
                          <span v-if="cd.apply_type === 'percent'" class="rp-input-affixed__suf">%</span>
                        </div>
                        <span class="rp-cat-row__sep">on categories</span>
                        <el-select v-model="cd.category_ids" multiple filterable placeholder="Select categories" class="rp-el-select rp-el-select--inline" @focus="loadCategoriesOnce">
                          <el-option v-for="c in categoryOptions" :key="c.id" :label="c.name" :value="c.id" />
                        </el-select>
                        <button type="button" class="rp-icon-btn rp-icon-btn--danger" @click="removeCategoryDiscount(idx)" aria-label="Remove">
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                      </div>
                    </div>
                    <button type="button" class="rp-btn rp-btn--outline rp-btn--sm" @click="addCategoryDiscount">+ Add category rule</button>
                    <p class="rp-field__help">Each row applies a separate discount to specific categories.</p>
                  </div>
                </template>

                <!-- free_gift -->
                <template v-else-if="benefitKind === 'free_gift'">
                  <div class="rp-field">
                    <label class="rp-field__label">Gift product(s)</label>
                    <el-select v-model="form.rule.benefit.product_ids" multiple filterable remote :remote-method="searchProducts" :loading="productsLoading" placeholder="Search products to add as gift…" value-key="id" class="rp-el-select" @focus="loadProductsIfEmpty">
                      <el-option v-for="p in productOptions" :key="p.id" :label="p.name" :value="p.id" />
                    </el-select>
                    <p class="rp-field__help">These products will be added to the cart automatically at no cost.</p>
                  </div>
                </template>

                <!-- fixed_price -->
                <template v-else-if="benefitKind === 'fixed_price'">
                  <div class="rp-field-group rp-field-group--inline">
                    <div class="rp-field">
                      <label class="rp-field__label">Fixed unit price</label>
                      <div class="rp-input-affixed">
                        <span class="rp-input-affixed__pre">$</span>
                        <input v-model.number="form.rule.benefit.price" type="number" class="rp-input" min="0" step="0.01" placeholder="9.99" />
                      </div>
                    </div>
                    <div class="rp-field">
                      <label class="rp-field__label">Apply to</label>
                      <select v-model="form.rule.benefit.apply_to" class="rp-select">
                        <option value="all">All matching products</option>
                        <option value="lowest">Cheapest matching only</option>
                        <option value="highest">Most expensive only</option>
                      </select>
                    </div>
                  </div>
                  <div class="rp-toggle-row">
                    <label class="rp-toggle">
                      <input v-model="form.rule.benefit.force" type="checkbox" />
                      <span class="rp-toggle__track"></span>
                    </label>
                    <div class="rp-toggle-row__body">
                      <span class="rp-toggle-row__label">Always override price</span>
                      <p class="rp-field__help">By default the fixed price only applies when it is lower than the original (discount only). Enable to always set the price regardless.</p>
                    </div>
                  </div>
                </template>

                <!-- free_shipping — no extra config needed -->
                <template v-else-if="benefitKind === 'free_shipping'">
                  <div class="rp-info-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    <p>When this rule matches, all shipping costs are set to <strong>$0.00</strong>. Use conditions below to set a cart-subtotal threshold.</p>
                  </div>
                </template>

              </div><!-- /rp-config-panel -->
            </div>
          </section>

          <!-- 2 · Apply To (targets + exclusions) -->
          <section class="rp-card">
            <div class="rp-card__header">
              <div class="rp-card__header-icon rp-card__header-icon--purple">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
              </div>
              <h2 class="rp-card__title">Apply To</h2>
              <p class="rp-card__subtitle">Which products receive this discount</p>
            </div>
            <div class="rp-card__body">

              <!-- Product filter toggle -->
              <div class="rp-toggle-row">
                <label class="rp-toggle">
                  <input v-model="productFilterEnabled" type="checkbox" />
                  <span class="rp-toggle__track"></span>
                </label>
                <div class="rp-toggle-row__body">
                  <span class="rp-toggle-row__label">Limit to specific products</span>
                  <p class="rp-field__help">Off = applies to every product in the store.</p>
                </div>
              </div>

              <template v-if="productFilterEnabled">
                <div class="rp-field rp-field--indent">
                  <label class="rp-field__label">Target type</label>
                  <div class="rp-segmented">
                    <button v-for="opt in targetTypes" :key="opt.value" type="button"
                      :class="['rp-segmented__btn', { 'rp-segmented__btn--active': form.rule.targets.type === opt.value }]"
                      @click="form.rule.targets.type = opt.value"
                    >{{ opt.label }}</button>
                  </div>
                </div>

                <div v-if="form.rule.targets.type === 'products'" class="rp-field rp-field--indent">
                  <label class="rp-field__label">Select products</label>
                  <el-select v-model="form.rule.targets.products" multiple filterable remote :remote-method="searchProductsForTarget" :loading="targetProductsLoading" placeholder="Search products…" value-key="id" class="rp-el-select" @focus="loadTargetProductsIfEmpty">
                    <el-option v-for="p in targetProductOptions" :key="p.id" :label="p.name" :value="p.id" />
                  </el-select>
                </div>

                <div v-if="form.rule.targets.type === 'categories'" class="rp-field rp-field--indent">
                  <label class="rp-field__label">Select categories</label>
                  <el-select v-model="form.rule.targets.categories" multiple filterable placeholder="Select categories…" class="rp-el-select" @focus="loadCategoriesOnce">
                    <el-option v-for="c in categoryOptions" :key="c.id" :label="c.name" :value="c.id" />
                  </el-select>
                </div>

                <template v-if="form.rule.targets.type === 'variations'">
                  <div class="rp-field rp-field--indent">
                    <label class="rp-field__label">Variable product</label>
                    <el-select v-model="variationParentId" filterable remote :remote-method="searchVariableProducts" :loading="variableProductsLoading" placeholder="Search variable product…" clearable class="rp-el-select" @focus="loadVariableProductsIfEmpty" @change="onVariationParentChange">
                      <el-option v-for="p in variableProductOptions" :key="p.id" :label="p.name" :value="p.id" />
                    </el-select>
                  </div>
                  <div v-if="variationParentId" class="rp-field rp-field--indent">
                    <label class="rp-field__label">Select variations</label>
                    <el-select v-model="form.rule.targets.variations" multiple filterable :loading="variationsLoading" placeholder="Select variations…" class="rp-el-select">
                      <el-option v-for="v in variationOptions" :key="v.id" :label="v.name" :value="v.id" />
                    </el-select>
                  </div>
                </template>
              </template>

              <div class="rp-divider"></div>

              <!-- Exclusions -->
              <div class="rp-toggle-row">
                <label class="rp-toggle">
                  <input v-model="form.rule.exclusions.enabled" type="checkbox" />
                  <span class="rp-toggle__track"></span>
                </label>
                <div class="rp-toggle-row__body">
                  <span class="rp-toggle-row__label">Exclude specific products</span>
                  <p class="rp-field__help">Remove certain products, categories, or tags from this rule.</p>
                </div>
              </div>

              <template v-if="form.rule.exclusions.enabled">
                <div class="rp-field-group rp-field-group--inline rp-field--indent">
                  <div class="rp-field">
                    <label class="rp-field__label">Exclude by</label>
                    <select v-model="form.rule.exclusions.type" class="rp-select">
                      <option value="products">Specific products</option>
                      <option value="categories">Specific categories</option>
                      <option value="tags">Specific tags</option>
                    </select>
                  </div>
                  <div class="rp-field rp-field--grow">
                    <label class="rp-field__label">
                      {{ form.rule.exclusions.type === 'products' ? 'Products' : form.rule.exclusions.type === 'categories' ? 'Categories' : 'Tags' }}
                      <span class="rp-field__req">*</span>
                    </label>
                    <el-select v-if="form.rule.exclusions.type === 'products'" v-model="form.rule.exclusions.ids" multiple filterable remote :remote-method="searchExcludeProducts" :loading="excludeProductsLoading" placeholder="Search products…" class="rp-el-select" @focus="loadExcludeProductsOnce">
                      <el-option v-for="p in excludeProductOptions" :key="p.id" :label="p.name" :value="p.id" />
                    </el-select>
                    <el-select v-else-if="form.rule.exclusions.type === 'categories'" v-model="form.rule.exclusions.ids" multiple filterable placeholder="Search categories…" class="rp-el-select" @focus="loadCategoriesOnce">
                      <el-option v-for="c in categoryOptions" :key="c.id" :label="c.name" :value="c.id" />
                    </el-select>
                    <el-select v-else v-model="form.rule.exclusions.ids" multiple filterable placeholder="Search tags…" class="rp-el-select" @focus="loadTagsOnce">
                      <el-option v-for="t in tagOptions" :key="t.id" :label="t.name" :value="t.id" />
                    </el-select>
                  </div>
                </div>
              </template>

            </div>
          </section>

          <!-- 3 · Conditions -->
          <section class="rp-card">
            <div class="rp-card__header">
              <div class="rp-card__header-icon rp-card__header-icon--amber">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
              </div>
              <h2 class="rp-card__title">Conditions</h2>
              <p class="rp-card__subtitle">When should this discount trigger?</p>
            </div>
            <div class="rp-card__body">

              <div class="rp-segmented rp-segmented--sm">
                <button type="button" :class="['rp-segmented__btn', { 'rp-segmented__btn--active': applyDiscountMode === 'always' }]" @click="applyDiscountMode = 'always'">Always</button>
                <button type="button" :class="['rp-segmented__btn', { 'rp-segmented__btn--active': applyDiscountMode === 'conditions' }]" @click="applyDiscountMode = 'conditions'">Only when conditions are met</button>
              </div>

              <template v-if="applyDiscountMode === 'conditions'">
                <!-- Active condition pills -->
                <div v-if="conditionItems.length" class="rp-condition-list">
                  <div v-for="(item, idx) in conditionItems" :key="idx" class="rp-condition-pill">
                    <span class="rp-condition-pill__type">{{ conditionTypeLabel(item.type) }}</span>
                    <span class="rp-condition-pill__op">{{ conditionOperatorLabel(item.operator) }}</span>
                    <span class="rp-condition-pill__val">{{ conditionValueSummary(item) }}</span>
                    <button type="button" class="rp-condition-pill__del" @click="removeConditionItem(idx)" aria-label="Remove condition">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                  </div>
                </div>
                <p v-else class="rp-empty-note">No conditions yet — rule applies to all carts. Add one below.</p>

                <!-- Add condition form -->
                <div v-if="showAddConditionForm" class="rp-condition-form">
                  <div class="rp-condition-form__row">
                    <select v-model="newCondition.type" class="rp-select rp-select--sm">
                      <option value="cart_subtotal">Cart subtotal</option>
                      <option value="cart_quantity">Cart quantity</option>
                      <option value="cart_items_count">Cart line items</option>
                      <option value="total_amount_spent">Total spent (customer)</option>
                      <option value="order_count">Order count (customer)</option>
                      <option value="product_in_cart">Product in cart</option>
                      <option value="user_role">User role</option>
                      <option value="user_id">User</option>
                      <option value="page">Page</option>
                    </select>

                    <select v-model="newCondition.operator" class="rp-select rp-select--sm">
                      <template v-if="isNumericConditionType(newCondition.type)">
                        <option value=">=">≥ (at least)</option>
                        <option value=">"> > (more than)</option>
                        <option value="<=">≤ (at most)</option>
                        <option value="<"> &lt; (less than)</option>
                        <option value="=">= (exactly)</option>
                      </template>
                      <template v-else-if="newCondition.type === 'page'">
                        <option value="=">is</option>
                      </template>
                      <template v-else>
                        <option value="in">includes</option>
                        <option value="not_in">excludes</option>
                      </template>
                    </select>

                    <!-- Value input per type -->
                    <template v-if="isNumericConditionType(newCondition.type)">
                      <div class="rp-input-affixed">
                        <span v-if="newCondition.type === 'cart_subtotal' || newCondition.type === 'total_amount_spent'" class="rp-input-affixed__pre">$</span>
                        <input v-model.number="newCondition.numericValue" type="number" min="0" step="0.01" class="rp-input rp-input--sm" placeholder="0" />
                      </div>
                    </template>
                    <template v-else-if="newCondition.type === 'page'">
                      <select v-model="newCondition.pageValue" class="rp-select rp-select--sm">
                        <option value="cart">Cart</option>
                        <option value="checkout">Checkout</option>
                        <option value="other">Other</option>
                      </select>
                    </template>
                    <template v-else-if="newCondition.type === 'user_role'">
                      <el-select v-model="newCondition.roleIds" multiple placeholder="Select roles" class="rp-el-select rp-el-select--inline" @focus="loadRolesOnce">
                        <el-option v-for="r in roleOptions" :key="r.id" :label="r.name" :value="r.id" />
                      </el-select>
                    </template>
                    <template v-else-if="newCondition.type === 'user_id'">
                      <el-select v-model="newCondition.userIds" multiple filterable remote :remote-method="searchUsers" :loading="usersLoading" placeholder="Search users" value-key="id" class="rp-el-select rp-el-select--inline" @focus="loadUsersOnce">
                        <el-option v-for="u in userOptions" :key="u.id" :label="u.name + (u.email ? ` (${u.email})` : '')" :value="u.id" />
                      </el-select>
                    </template>
                    <template v-else-if="newCondition.type === 'product_in_cart'">
                      <el-select v-model="newCondition.productIds" multiple filterable remote :remote-method="searchProducts" :loading="productsLoading" placeholder="Search products" value-key="id" class="rp-el-select rp-el-select--inline" @focus="loadProductsIfEmpty">
                        <el-option v-for="p in productOptions" :key="p.id" :label="p.name" :value="p.id" />
                      </el-select>
                    </template>
                  </div>
                  <div class="rp-condition-form__actions">
                    <button type="button" class="rp-btn rp-btn--primary rp-btn--sm" @click="saveNewCondition">Add condition</button>
                    <button type="button" class="rp-btn rp-btn--ghost rp-btn--sm" @click="showAddConditionForm = false">Cancel</button>
                  </div>
                </div>

                <button v-if="!showAddConditionForm" type="button" class="rp-btn rp-btn--outline rp-btn--sm" @click="openAddCondition">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                  Add condition
                </button>
              </template>

            </div>
          </section>

          <!-- 4 · Users & Schedule -->
          <section class="rp-card">
            <div class="rp-card__header">
              <div class="rp-card__header-icon rp-card__header-icon--teal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              </div>
              <h2 class="rp-card__title">Users &amp; Schedule</h2>
            </div>
            <div class="rp-card__body">

              <!-- Who -->
              <div class="rp-field">
                <label class="rp-field__label">Apply to</label>
                <div class="rp-segmented rp-segmented--sm">
                  <button type="button" :class="['rp-segmented__btn', { 'rp-segmented__btn--active': applyTo === 'all' }]" @click="applyTo = 'all'">All users</button>
                  <button type="button" :class="['rp-segmented__btn', { 'rp-segmented__btn--active': applyTo === 'roles' }]" @click="applyTo = 'roles'">Specific roles</button>
                  <button type="button" :class="['rp-segmented__btn', { 'rp-segmented__btn--active': applyTo === 'users' }]" @click="applyTo = 'users'">Specific users</button>
                </div>
              </div>

              <div v-if="applyTo === 'roles'" class="rp-field rp-field--indent">
                <label class="rp-field__label">Roles</label>
                <el-select v-model="selectedRoleIds" multiple placeholder="Select roles" class="rp-el-select" @focus="loadRolesOnce">
                  <el-option v-for="r in roleOptions" :key="r.id" :label="r.name" :value="r.id" />
                </el-select>
              </div>

              <div v-if="applyTo === 'users'" class="rp-field rp-field--indent">
                <label class="rp-field__label">Users</label>
                <el-select v-model="selectedUserIds" multiple filterable remote :remote-method="searchUsers" :loading="usersLoading" placeholder="Search users" value-key="id" class="rp-el-select" @focus="loadUsersOnce">
                  <el-option v-for="u in userOptions" :key="u.id" :label="u.name + (u.email ? ` (${u.email})` : '')" :value="u.id" />
                </el-select>
              </div>

              <div class="rp-divider"></div>

              <!-- Exclude users -->
              <div class="rp-toggle-row">
                <label class="rp-toggle">
                  <input v-model="excludeUsersEnabled" type="checkbox" />
                  <span class="rp-toggle__track"></span>
                </label>
                <div class="rp-toggle-row__body">
                  <span class="rp-toggle-row__label">Exclude specific users or roles</span>
                  <p class="rp-field__help">Prevent certain users or roles from receiving this discount.</p>
                </div>
              </div>

              <template v-if="excludeUsersEnabled">
                <div class="rp-field rp-field--indent">
                  <label class="rp-field__label">Exclude roles</label>
                  <el-select v-model="excludeRoleIds" multiple placeholder="Select roles to exclude" class="rp-el-select" @focus="loadRolesOnce">
                    <el-option v-for="r in roleOptions" :key="r.id" :label="r.name" :value="r.id" />
                  </el-select>
                </div>
                <div class="rp-field rp-field--indent">
                  <label class="rp-field__label">Exclude users</label>
                  <el-select v-model="excludeUserIds" multiple filterable remote :remote-method="searchUsersExclude" :loading="usersExcludeLoading" placeholder="Search users to exclude" value-key="id" class="rp-el-select" @focus="loadUsersExcludeOnce">
                    <el-option v-for="u in userExcludeOptions" :key="u.id" :label="u.name + (u.email ? ` (${u.email})` : '')" :value="u.id" />
                  </el-select>
                </div>
              </template>

              <div class="rp-divider"></div>

              <!-- Schedule -->
              <div class="rp-field">
                <label class="rp-field__label">Schedule</label>
                <div class="rp-segmented rp-segmented--sm">
                  <button type="button" :class="['rp-segmented__btn', { 'rp-segmented__btn--active': scheduleMode === 'always' }]" @click="scheduleMode = 'always'">Always active</button>
                  <button type="button" :class="['rp-segmented__btn', { 'rp-segmented__btn--active': scheduleMode === 'schedule' }]" @click="scheduleMode = 'schedule'">Set date range</button>
                </div>
              </div>

              <template v-if="scheduleMode === 'schedule'">
                <div class="rp-field-group rp-field-group--inline rp-field--indent">
                  <div class="rp-field">
                    <label class="rp-field__label">Start</label>
                    <input v-model="form.rule.schedule.start" type="datetime-local" class="rp-input" />
                  </div>
                  <div class="rp-field">
                    <label class="rp-field__label">End</label>
                    <input v-model="form.rule.schedule.end" type="datetime-local" class="rp-input" />
                  </div>
                </div>
              </template>

            </div>
          </section>

          <!-- 5 · Display -->
          <section class="rp-card">
            <div class="rp-card__header">
              <div class="rp-card__header-icon rp-card__header-icon--rose">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </div>
              <h2 class="rp-card__title">Display</h2>
              <p class="rp-card__subtitle">How the discount appears on your storefront</p>
            </div>
            <div class="rp-card__body">

              <div class="rp-toggle-row">
                <label class="rp-toggle">
                  <input v-model="form.rule.meta.show_badge" type="checkbox" />
                  <span class="rp-toggle__track"></span>
                </label>
                <div class="rp-toggle-row__body">
                  <span class="rp-toggle-row__label">Show badge on product page</span>
                  <p class="rp-field__help">Display a discount message below the product title on single product pages.</p>
                </div>
              </div>

              <div class="rp-toggle-row">
                <label class="rp-toggle">
                  <input v-model="form.rule.meta.show_on_shop" type="checkbox" />
                  <span class="rp-toggle__track"></span>
                </label>
                <div class="rp-toggle-row__body">
                  <span class="rp-toggle-row__label">Show badge on shop / archive pages</span>
                  <p class="rp-field__help">Show a compact badge under the product title in shop and category listings.</p>
                </div>
              </div>

              <div class="rp-toggle-row">
                <label class="rp-toggle">
                  <input v-model="customMessageEnabled" type="checkbox" />
                  <span class="rp-toggle__track"></span>
                </label>
                <div class="rp-toggle-row__body">
                  <span class="rp-toggle-row__label">Custom message</span>
                  <p class="rp-field__help">Override the auto-generated badge text with your own message.</p>
                </div>
              </div>

              <template v-if="customMessageEnabled">
                <div class="rp-field rp-field--indent">
                  <label class="rp-field__label">Message text</label>
                  <textarea v-model="form.rule.meta.custom_message" class="rp-textarea" rows="3" placeholder="e.g. Limited-time offer — save big!" />
                  <div class="rp-field__meta-row">
                    <span class="rp-field__help">Use <code>[save_amount]</code> or <code>[save_percentage]</code> shortcodes.</span>
                    <span class="rp-wordcount">{{ wordCount }} {{ wordCount === 1 ? 'word' : 'words' }}</span>
                  </div>
                </div>
              </template>

            </div>
          </section>

        </div><!-- /rp-layout__main -->

        <!-- ─────────── Sidebar ─────────── -->
        <div class="rp-layout__sidebar">
          <div class="rp-pub-card">

            <!-- Rule name -->
            <div class="rp-pub-field">
              <label class="rp-pub-field__label">Rule name <span class="rp-field__req">*</span></label>
              <input v-model="form.name" type="text" class="rp-input rp-input--full" placeholder="e.g. Summer Sale 20% Off" />
            </div>

            <!-- Status -->
            <div class="rp-pub-field">
              <label class="rp-pub-field__label">Status</label>
              <div class="rp-status-select-wrap">
                <span :class="['rp-status-dot', `rp-status-dot--${form.status}`]"></span>
                <select v-model="form.status" class="rp-select rp-select--full">
                  <option value="draft">Draft</option>
                  <option value="active">Active</option>
                  <option value="disabled">Disabled</option>
                </select>
              </div>
            </div>

            <!-- Priority -->
            <div class="rp-pub-field">
              <label class="rp-pub-field__label">Priority</label>
              <input v-model.number="form.priority" type="number" class="rp-input rp-input--sm" min="0" />
              <p class="rp-field__help">Lower number = applied first (1 is highest).</p>
            </div>

            <!-- Save -->
            <button type="button" class="rp-btn rp-btn--primary rp-btn--full" :disabled="saveSaving" @click="save">
              <svg v-if="!saveSaving" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
              <div v-else class="rp-spinner rp-spinner--sm"></div>
              {{ saveSaving ? 'Saving…' : 'Save rule' }}
            </button>

          </div>

          <!-- Shortcode reference -->
          <div class="rp-info-card">
            <h3 class="rp-info-card__title">Shortcodes</h3>
            <div class="rp-shortcode-list">
              <div class="rp-shortcode"><code>[save_amount]</code><span>Saved value in currency</span></div>
              <div class="rp-shortcode"><code>[save_percentage]</code><span>Saved percentage</span></div>
            </div>
          </div>

        </div><!-- /rp-layout__sidebar -->

      </div><!-- /rp-layout -->

    </template>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { api } from '../api';

const route = useRoute();
const ruleId = computed(() => route.params.id);
const loading = ref(true);
const rule = ref(null);
const saveSaving = ref(false);
const saveError = ref(null);
const loadError = ref(null);

const productFilterEnabled = ref(false);
const excludeUsersEnabled = ref(false);
const scheduleMode = ref('always');
const customMessageEnabled = ref(false);
const applyTo = ref('all');
const applyDiscountMode = ref('always');
const showAddConditionForm = ref(false);
const newCondition = ref({
  type: 'cart_subtotal',
  operator: '>=',
  numericValue: 0,
  pageValue: 'cart',
  roleIds: [],
  userIds: [],
  productIds: [],
});

const roleOptions = ref([]);
const userOptions = ref([]);
const userExcludeOptions = ref([]);
const categoryOptions = ref([]);
const tagOptions = ref([]);
const productOptions = ref([]);
const targetProductOptions = ref([]);
const excludeProductOptions = ref([]);
const usersLoading = ref(false);
const excludeProductsLoading = ref(false);
const usersExcludeLoading = ref(false);
const productsLoading = ref(false);
const targetProductsLoading = ref(false);
const variableProductOptions = ref([]);
const variableProductsLoading = ref(false);
const variationOptions = ref([]);
const variationsLoading = ref(false);
const variationParentId = ref(null);

// UI metadata — static, not reactive
const discountTypes = [
  { value: 'percent_off',     label: '% Off',            sub: 'Per product',    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>' },
  { value: 'fixed_off',       label: 'Fixed Off',        sub: 'Per product',    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>' },
  { value: 'fixed_price',     label: 'Fixed Price',      sub: 'Set price to',   icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M8 12h8"/><path d="M12 8v8"/></svg>' },
  { value: 'tiered',          label: 'Tiered Qty',       sub: 'More = bigger',  icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>' },
  { value: 'x_for_y',         label: 'Buy X Pay Y',      sub: 'Classic BOGO',   icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>' },
  { value: 'nth_percent_off', label: 'Nth Unit %',       sub: 'Every Nth item', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="6" height="4"/><rect x="9" y="3" width="6" height="4" opacity=".4"/><rect x="16" y="3" width="6" height="4"/><path d="M5 7v5m7-5v5"/><line x1="2" y1="17" x2="22" y2="17"/></svg>' },
  { value: 'category_discounts', label: 'By Category',  sub: 'Per category',   icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>' },
  { value: 'cart_percent_off', label: 'Cart % Off',     sub: 'Entire cart',    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>' },
  { value: 'cart_fixed_off',  label: 'Cart Fixed Off',  sub: 'Entire cart',    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/><line x1="14" y1="10" x2="18" y2="14"/></svg>' },
  { value: 'free_shipping',   label: 'Free Shipping',   sub: 'Zero ship cost', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>' },
  { value: 'free_gift',       label: 'Free Gift',       sub: 'Auto-add item',  icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>' },
];

const targetTypes = [
  { value: 'all', label: 'All products' },
  { value: 'products', label: 'Specific products' },
  { value: 'categories', label: 'Categories' },
  { value: 'variations', label: 'Variations' },
];

const defaultRule = () => ({
  targets: { type: 'all', products: [], categories: [], variations: [] },
  conditions: { groups: [{ logic: 'and', items: [] }] },
  benefit: {
    kind: 'percent_off',
    percent: 0,
    amount: 0,
    tiers: [],
    buy_qty: 2,
    pay_qty: 1,
    nth: 2,
    product_ids: [],
    category_discounts: [],
    price: 0,
    apply_to: 'all',
    force: false,
  },
  exclusions: { enabled: false, type: 'products', ids: [] },
  limits: { max_uses: 0, max_uses_per_user: 0 },
  stacking: { stop_processing: false, can_stack_with_other_rules: true },
  schedule: { start: '', end: '' },
  meta: { show_badge: true, show_on_shop: true, custom_message: '' },
});

const form = ref({
  name: '',
  status: 'draft',
  priority: 10,
  rule: defaultRule(),
});

const benefitKind = computed({
  get: () => form.value.rule.benefit?.kind || 'percent_off',
  set: (v) => {
    if (!form.value.rule.benefit) form.value.rule.benefit = {};
    form.value.rule.benefit.kind = v;
    if (v === 'tiered' && (!form.value.rule.benefit.tiers || !form.value.rule.benefit.tiers.length)) {
      form.value.rule.benefit.tiers = [{ min: 2, max: 0, percent_off: 10, fixed_off: 0 }];
    }
    if (v === 'free_gift' && !Array.isArray(form.value.rule.benefit.product_ids)) {
      form.value.rule.benefit.product_ids = [];
    }
    if (v === 'category_discounts') {
      if (!Array.isArray(form.value.rule.benefit.category_discounts)) {
        form.value.rule.benefit.category_discounts = [];
      }
      if (!form.value.rule.benefit.category_discounts.length) {
        form.value.rule.benefit.category_discounts.push({ apply_type: 'percent', value: 10, category_ids: [] });
      }
    }
    if (v === 'fixed_price') {
      if (form.value.rule.benefit.price === undefined) form.value.rule.benefit.price = 0;
      if (!form.value.rule.benefit.apply_to) form.value.rule.benefit.apply_to = 'all';
      if (form.value.rule.benefit.force === undefined) form.value.rule.benefit.force = false;
    }
  },
});

const selectedRoleIds = computed({
  get: () => getConditionValue('user_role', 'in') || [],
  set: (v) => setConditionItem('user_role', 'in', v),
});

const selectedUserIds = computed({
  get: () => getConditionValue('user_id', 'in') || [],
  set: (v) => setConditionItem('user_id', 'in', v),
});

const excludeRoleIds = computed({
  get: () => getConditionValue('user_role', 'not_in') || [],
  set: (v) => setConditionItem('user_role', 'not_in', v),
});

const excludeUserIds = computed({
  get: () => getConditionValue('user_id', 'not_in') || [],
  set: (v) => setConditionItem('user_id', 'not_in', v),
});

const conditionItems = computed(() => {
  const groups = form.value.rule.conditions?.groups || [];
  const items = groups[0]?.items || [];
  return items;
});

const CONDITION_TYPE_LABELS = {
  cart_subtotal: 'Cart subtotal',
  cart_quantity: 'Cart quantity',
  cart_items_count: 'Cart line items',
  total_amount_spent: 'Total amount spent',
  order_count: 'Number of orders',
  product_in_cart: 'Products in cart',
  user_role: 'User role',
  user_id: 'User',
  page: 'Page',
  coupon: 'Coupon',
  shipping_country: 'Shipping country',
};

function conditionTypeLabel(type) {
  return CONDITION_TYPE_LABELS[type] || type;
}

function conditionOperatorLabel(op) {
  const map = { '>=': '≥', '>': '>', '<=': '≤', '<': '<', '=': '=', in: 'in', not_in: 'not in' };
  return map[op] || op;
}

function conditionValueSummary(item) {
  const v = item.value;
  if (item.type === 'page') return v || '—';
  if (item.type === 'user_role' || item.type === 'user_id' || item.type === 'product_in_cart') {
    const arr = Array.isArray(v) ? v : [];
    return arr.length ? arr.length + ' selected' : '—';
  }
  if (typeof v === 'number' || (typeof v === 'string' && /^-?[\d.]+$/.test(v))) return v;
  return v != null ? String(v) : '—';
}

function isNumericConditionType(type) {
  return ['cart_subtotal', 'cart_quantity', 'cart_items_count', 'total_amount_spent', 'order_count'].includes(type);
}

function ensureConditionsGroups() {
  if (!form.value.rule.conditions) form.value.rule.conditions = { groups: [] };
  if (!form.value.rule.conditions.groups.length) form.value.rule.conditions.groups.push({ logic: 'and', items: [] });
}

function removeConditionItem(idx) {
  ensureConditionsGroups();
  form.value.rule.conditions.groups[0].items.splice(idx, 1);
}

function openAddCondition() {
  newCondition.value = {
    type: 'cart_subtotal',
    operator: '>=',
    numericValue: 0,
    pageValue: 'cart',
    roleIds: [],
    userIds: [],
    productIds: [],
  };
  showAddConditionForm.value = true;
}

function saveNewCondition() {
  ensureConditionsGroups();
  const n = newCondition.value;
  let value;
  if (n.type === 'page') value = n.pageValue;
  else if (n.type === 'user_role') value = n.roleIds?.length ? n.roleIds : null;
  else if (n.type === 'user_id') value = n.userIds?.length ? n.userIds : null;
  else if (n.type === 'product_in_cart') value = n.productIds?.length ? n.productIds : null;
  else if (isNumericConditionType(n.type)) value = n.numericValue;
  else value = null;
  if (value === null && isNumericConditionType(n.type)) value = 0;
  if (value === null && n.type === 'page') value = 'cart';
  if ((value === null || (Array.isArray(value) && !value.length)) && !isNumericConditionType(n.type) && n.type !== 'page') return;
  form.value.rule.conditions.groups[0].items.push({ type: n.type, operator: n.operator, value });
  showAddConditionForm.value = false;
}

function ensureTargetsArrays() {
  if (!Array.isArray(form.value.rule.targets.products)) form.value.rule.targets.products = [];
  if (!Array.isArray(form.value.rule.targets.categories)) form.value.rule.targets.categories = [];
  if (!Array.isArray(form.value.rule.targets.variations)) form.value.rule.targets.variations = [];
}

function getConditionValue(type, op) {
  const groups = form.value.rule.conditions?.groups || [];
  for (const g of groups) {
    const items = g.items || [];
    for (const it of items) {
      if ((it.type === type) && (it.operator === op)) return Array.isArray(it.value) ? it.value : [];
    }
  }
  return [];
}

function setConditionItem(type, op, value) {
  const groups = form.value.rule.conditions.groups;
  if (!groups.length) groups.push({ logic: 'and', items: [] });
  const items = groups[0].items;
  const existing = items.findIndex((it) => it.type === type && it.operator === op);
  if (existing >= 0) {
    if (!value || !value.length) items.splice(existing, 1);
    else items[existing].value = value;
  } else if (value && value.length) {
    items.push({ type, operator: op, value });
  }
}

function addTier() {
  if (!form.value.rule.benefit.tiers) form.value.rule.benefit.tiers = [];
  form.value.rule.benefit.tiers.push({ min: 0, max: 0, percent_off: 0, fixed_off: 0 });
}

function removeTier(idx) {
  form.value.rule.benefit.tiers.splice(idx, 1);
}

function addCategoryDiscount() {
  if (!form.value.rule.benefit.category_discounts) form.value.rule.benefit.category_discounts = [];
  form.value.rule.benefit.category_discounts.push({ apply_type: 'percent', value: 10, category_ids: [] });
}

function removeCategoryDiscount(idx) {
  form.value.rule.benefit.category_discounts.splice(idx, 1);
}

let rolesLoaded = false;
async function loadRolesOnce() {
  if (rolesLoaded) return;
  rolesLoaded = true;
  try {
    const data = await api.get('editor/roles');
    roleOptions.value = Array.isArray(data) ? data : [];
  } catch (_) {
    roleOptions.value = [];
  }
}

let categoriesLoaded = false;
async function loadCategoriesOnce() {
  if (categoriesLoaded) return;
  categoriesLoaded = true;
  try {
    const data = await api.get('editor/categories');
    categoryOptions.value = Array.isArray(data) ? data : [];
  } catch (_) {
    categoryOptions.value = [];
  }
}

async function searchUsers(q) {
  usersLoading.value = true;
  try {
    const data = await api.get('editor/users', { search: q || '', per_page: 50 });
    userOptions.value = Array.isArray(data) ? data : [];
  } catch (_) {
    userOptions.value = [];
  } finally {
    usersLoading.value = false;
  }
}

async function loadUsersOnce() {
  if (userOptions.value.length) return;
  await searchUsers('');
}

async function searchUsersExclude(q) {
  usersExcludeLoading.value = true;
  try {
    const data = await api.get('editor/users', { search: q || '', per_page: 50 });
    userExcludeOptions.value = Array.isArray(data) ? data : [];
  } catch (_) {
    userExcludeOptions.value = [];
  } finally {
    usersExcludeLoading.value = false;
  }
}

async function loadUsersExcludeOnce() {
  if (userExcludeOptions.value.length) return;
  await searchUsersExclude('');
}

async function searchProducts(q) {
  productsLoading.value = true;
  try {
    const data = await api.get('editor/products', { search: q || '', per_page: 50 });
    productOptions.value = Array.isArray(data) ? data : [];
  } catch (_) {
    productOptions.value = [];
  } finally {
    productsLoading.value = false;
  }
}

async function loadProductsIfEmpty() {
  if (productOptions.value.length === 0) await searchProducts('');
}

async function searchProductsForTarget(q) {
  targetProductsLoading.value = true;
  try {
    const data = await api.get('editor/products', { search: q || '', per_page: 50 });
    targetProductOptions.value = Array.isArray(data) ? data : [];
  } catch (_) {
    targetProductOptions.value = [];
  } finally {
    targetProductsLoading.value = false;
  }
}

async function loadTargetProductsIfEmpty() {
  if (targetProductOptions.value.length === 0) await searchProductsForTarget('');
}

let tagsLoaded = false;
async function loadTagsOnce() {
  if (tagsLoaded) return;
  tagsLoaded = true;
  try {
    const data = await api.get('editor/tags');
    tagOptions.value = Array.isArray(data) ? data : [];
  } catch (_) {
    tagOptions.value = [];
  }
}

async function searchExcludeProducts(q) {
  excludeProductsLoading.value = true;
  try {
    const data = await api.get('editor/products', { search: q || '', per_page: 50 });
    excludeProductOptions.value = Array.isArray(data) ? data : [];
  } catch (_) {
    excludeProductOptions.value = [];
  } finally {
    excludeProductsLoading.value = false;
  }
}

async function loadExcludeProductsOnce() {
  if (excludeProductOptions.value.length === 0) await searchExcludeProducts('');
}

async function searchVariableProducts(q) {
  variableProductsLoading.value = true;
  try {
    const data = await api.get('editor/variable-products', { search: q || '', per_page: 50 });
    variableProductOptions.value = Array.isArray(data) ? data : [];
  } catch (_) {
    variableProductOptions.value = [];
  } finally {
    variableProductsLoading.value = false;
  }
}

async function loadVariableProductsIfEmpty() {
  if (variableProductOptions.value.length === 0) await searchVariableProducts('');
}

async function loadVariationsForProduct(productId) {
  if (!productId) {
    variationOptions.value = [];
    return;
  }
  variationsLoading.value = true;
  try {
    const data = await api.get('editor/variations', { product_id: productId });
    variationOptions.value = Array.isArray(data) ? data : [];
  } catch (_) {
    variationOptions.value = [];
  } finally {
    variationsLoading.value = false;
  }
}

async function onVariationParentChange(newParentId) {
  form.value.rule.targets.variations = [];
  await loadVariationsForProduct(newParentId);
}

async function restoreVariationParent(variationIds) {
  if (!variationIds || !variationIds.length) return;
  // Load variable products to populate the dropdown, then find which parent owns these variations
  await loadVariableProductsIfEmpty();
  // Load variations for each variable product candidate until we find the parent
  for (const p of variableProductOptions.value) {
    try {
      const data = await api.get('editor/variations', { product_id: p.id });
      const ids = Array.isArray(data) ? data.map((v) => v.id) : [];
      if (variationIds.some((vid) => ids.includes(vid))) {
        variationParentId.value = p.id;
        variationOptions.value = data;
        return;
      }
    } catch (_) {}
  }
}

const wordCount = computed(() => {
  const t = form.value.rule?.meta?.custom_message || '';
  return t.trim() ? t.split(/\s+/).length : 0;
});

function assignFromRule(r) {
  rule.value = r;
  form.value.name = r.name || '';
  form.value.status = r.status || 'draft';
  form.value.priority = r.priority ?? 10;
  form.value.rule = {
    ...defaultRule(),
    ...(r.rule || {}),
    targets: { ...defaultRule().targets, ...(r.rule?.targets || {}), products: (r.rule?.targets?.products ?? []).map(Number).filter(Boolean), categories: (r.rule?.targets?.categories ?? []).map(Number).filter(Boolean), variations: (r.rule?.targets?.variations ?? []).map(Number).filter(Boolean) },
    benefit: {
      ...defaultRule().benefit,
      ...(r.rule?.benefit || {}),
      tiers: r.rule?.benefit?.tiers ?? [],
      product_ids: (r.rule?.benefit?.product_ids ?? []).map(Number).filter(Boolean),
      category_discounts: (r.rule?.benefit?.category_discounts ?? []).map((cd) => ({
        apply_type: cd.apply_type || 'percent',
        value: Number(cd.value) || 0,
        category_ids: (cd.category_ids ?? []).map(Number).filter(Boolean),
      })),
    },
    exclusions: {
      enabled: !!(r.rule?.exclusions?.enabled),
      type: r.rule?.exclusions?.type || 'products',
      ids: (r.rule?.exclusions?.ids ?? []).map(Number).filter(Boolean),
    },
    schedule: { ...defaultRule().schedule, ...(r.rule?.schedule || {}) },
    meta: { ...defaultRule().meta, ...(r.rule?.meta || {}) },
  };
  if (!form.value.rule.benefit.tiers || !form.value.rule.benefit.tiers.length) form.value.rule.benefit.tiers = [];
  if (!Array.isArray(form.value.rule.benefit.product_ids)) form.value.rule.benefit.product_ids = [];
  form.value.rule.benefit.product_ids = form.value.rule.benefit.product_ids.map(Number).filter(Boolean);
  if (!Array.isArray(form.value.rule.benefit.category_discounts)) form.value.rule.benefit.category_discounts = [];
  form.value.rule.targets.products = form.value.rule.targets.products.map(Number).filter(Boolean);
  form.value.rule.targets.categories = form.value.rule.targets.categories.map(Number).filter(Boolean);
  form.value.rule.targets.variations = (r.rule?.targets?.variations ?? []).map(Number).filter(Boolean);
  if (!form.value.rule.meta) form.value.rule.meta = {};
  form.value.rule.meta.show_badge = form.value.rule.meta.show_badge !== false;
  form.value.rule.meta.show_on_shop = form.value.rule.meta.show_on_shop !== false;
  form.value.rule.meta.custom_message = form.value.rule.meta.custom_message || '';
  ensureTargetsArrays();
  productFilterEnabled.value = (form.value.rule.targets?.type || 'all') !== 'all';
  if (form.value.rule.targets?.type === 'variations' && form.value.rule.targets.variations.length) {
    restoreVariationParent(form.value.rule.targets.variations);
  }
  excludeUsersEnabled.value = (getConditionValue('user_role', 'not_in').length > 0 || getConditionValue('user_id', 'not_in').length > 0);
  scheduleMode.value = (form.value.rule.schedule?.start || form.value.rule.schedule?.end) ? 'schedule' : 'always';
  customMessageEnabled.value = !!(form.value.rule.meta?.custom_message || '').trim();
  if (getConditionValue('user_role', 'in').length) applyTo.value = 'roles';
  else if (getConditionValue('user_id', 'in').length) applyTo.value = 'users';
  else applyTo.value = 'all';
  const condItems = form.value.rule.conditions?.groups?.[0]?.items || [];
  applyDiscountMode.value = condItems.length ? 'conditions' : 'always';
  rolesLoaded = false;
  categoriesLoaded = false;
  tagsLoaded = false;
  variationParentId.value = null;
  variationOptions.value = [];
  loadRolesOnce();
  loadCategoriesOnce();
}

onMounted(async () => {
  const id = ruleId.value;
  if (!id) {
    loading.value = false;
    return;
  }
  loading.value = true;
  try {
    const data = await api.get(`rules/${id}`);
    assignFromRule(data);
  } catch (err) {
    rule.value = null;
    loadError.value = err?.message || 'Failed to load rule.';
  } finally {
    loading.value = false;
  }
});

watch(ruleId, async (id) => {
  if (!id) return;
  loading.value = true;
  try {
    const data = await api.get(`rules/${id}`);
    assignFromRule(data);
  } catch (err) {
    rule.value = null;
    loadError.value = err?.message || 'Failed to load rule.';
  } finally {
    loading.value = false;
  }
}, { immediate: false });

async function save() {
  if (!rule.value) return;
  saveError.value = null;
  saveSaving.value = true;
  ensureTargetsArrays();
  if (applyTo.value === 'all') {
    setConditionItem('user_role', 'in', []);
    setConditionItem('user_id', 'in', []);
  }
  const payload = {
    name: form.value.name,
    status: form.value.status,
    priority: form.value.priority,
    rule: {
      ...form.value.rule,
      schedule: scheduleMode.value === 'always' ? { start: '', end: '' } : form.value.rule.schedule,
    },
  };
  try {
    await api.patch(`rules/${ruleId.value}`, payload);
    assignFromRule({ ...rule.value, ...payload, rule: payload.rule });
  } catch (err) {
    saveError.value = err?.message || 'Failed to save rule.';
  }
  saveSaving.value = false;
}
</script>
