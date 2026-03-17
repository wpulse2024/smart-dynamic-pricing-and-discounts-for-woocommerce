<template>
  <div class="rule-editor-page">
    <div v-if="loading" class="rule-editor-page__loading">Loading rule…</div>
    <template v-else-if="rule">
      <div class="rule-editor-page__header">
        <router-link to="/rules" class="rule-editor-page__back">← Back to rules</router-link>
        <div class="rule-editor-page__title-row">
          <h1 class="rule-editor-page__title">Dynamic Rule</h1>
          <button type="button" class="rule-editor-page__save-view" @click="save">Save view &gt;</button>
        </div>
        <div v-if="saveError" style="color: red; margin: 8px 0;">{{ saveError }}</div>
        <div v-if="loadError" style="color: red; margin: 8px 0;">{{ loadError }}</div>
      </div>

      <!-- General Options -->
      <section class="rule-editor-card rule-editor-card--general">
        <h2 class="rule-editor-card__title">General Options</h2>
        <div class="rule-editor-card__body">
          <div class="rule-editor-field">
            <label class="rule-editor-field__label">Rule name <span class="rule-editor-field__required">*</span></label>
            <input v-model="form.name" type="text" class="rule-editor-field__input" placeholder="e.g. 10% Discount" />
            <p class="rule-editor-field__help">Enter a name to identify this rule</p>
          </div>
          <div class="rule-editor-field">
            <label class="rule-editor-field__label">Status</label>
            <select v-model="form.status" class="rule-editor-field__select">
              <option value="draft">Draft</option>
              <option value="active">Active</option>
              <option value="disabled">Disabled</option>
            </select>
          </div>
          <div class="rule-editor-field">
            <label class="rule-editor-field__label">Priority</label>
            <input v-model.number="form.priority" type="number" class="rule-editor-field__input rule-editor-field__input--sm" min="0" />
            <p class="rule-editor-field__help">Set the priority for this rule. Priority is a number (1 is the highest priority)</p>
          </div>
        </div>
      </section>

      <!-- Rule Configuration -->
      <section class="rule-editor-card">
        <h2 class="rule-editor-card__title">Rule Configuration</h2>
        <div class="rule-editor-card__body">
          <div class="rule-editor-field">
            <label class="rule-editor-field__label">Discount type</label>
            <select v-model="benefitKind" class="rule-editor-field__select">
              <option value="percent_off">% off</option>
              <option value="fixed_off">Fixed amount off</option>
              <option value="category_discounts">Category discount</option>
              <option value="tiered">Tiered (quantity)</option>
              <option value="x_for_y">X for Y</option>
              <option value="nth_percent_off">Nth unit % off</option>
              <option value="cart_percent_off">Cart % off</option>
              <option value="cart_fixed_off">Cart fixed off</option>
              <option value="free_shipping">Free shipping</option>
              <option value="free_gift">Free gift</option>
            </select>
          </div>
          <!-- Set a category discount (multiple rules) -->
          <template v-if="benefitKind === 'category_discounts'">
            <div class="rule-editor-field">
              <label class="rule-editor-field__label">Set a category discount</label>
              <div
                v-for="(cd, idx) in form.rule.benefit.category_discounts"
                :key="idx"
                class="rule-editor-field__category-discount-row"
              >
                <select v-model="cd.apply_type" class="rule-editor-field__select rule-editor-field__select--sm">
                  <option value="percent">a % discount of</option>
                  <option value="fixed">a fixed amount of</option>
                </select>
                <input
                  v-model.number="cd.value"
                  type="number"
                  class="rule-editor-field__input rule-editor-field__input--xs"
                  min="0"
                  :step="cd.apply_type === 'percent' ? 1 : 0.01"
                  placeholder="0"
                />
                <span class="rule-editor-field__suffix">{{ cd.apply_type === 'percent' ? '%' : '' }}</span>
                <span class="rule-editor-field__suffix">on all products of</span>
                <el-select
                  v-model="cd.category_ids"
                  multiple
                  filterable
                  placeholder="Search for a category..."
                  class="rule-editor-field__el-select rule-editor-field__el-select--inline"
                  @focus="loadCategoriesOnce"
                >
                  <el-option v-for="c in categoryOptions" :key="c.id" :label="c.name" :value="c.id" />
                </el-select>
                <button type="button" class="rule-editor-field__delete-btn" aria-label="Remove rule" @click="removeCategoryDiscount(idx)">
                  <span aria-hidden="true">🗑</span>
                </button>
              </div>
              <button type="button" class="btn btn--outline btn--sm" @click="addCategoryDiscount">+ Add rule</button>
              <p class="rule-editor-field__help">Set the discount to apply to product categories from this rule.</p>
            </div>
          </template>
          <template v-if="benefitKind === 'percent_off' || benefitKind === 'nth_percent_off'">
            <div class="rule-editor-field rule-editor-field--inline">
              <label class="rule-editor-field__label">{{ benefitKind === 'nth_percent_off' ? 'Nth unit' : 'Percent' }}</label>
              <div class="rule-editor-field__row">
                <template v-if="benefitKind === 'nth_percent_off'">
                  <input v-model.number="form.rule.benefit.nth" type="number" class="rule-editor-field__input rule-editor-field__input--xs" min="1" placeholder="2" />
                  <span class="rule-editor-field__suffix">nd unit</span>
                </template>
                <input v-model.number="form.rule.benefit.percent" type="number" class="rule-editor-field__input rule-editor-field__input--xs" min="0" max="100" />
                <span class="rule-editor-field__suffix">% off</span>
              </div>
            </div>
          </template>
          <template v-else-if="benefitKind === 'fixed_off' || benefitKind === 'cart_fixed_off'">
            <div class="rule-editor-field rule-editor-field--inline">
              <label class="rule-editor-field__label">Amount</label>
              <input v-model.number="form.rule.benefit.amount" type="number" class="rule-editor-field__input rule-editor-field__input--xs" min="0" step="0.01" />
            </div>
          </template>
          <template v-else-if="benefitKind === 'cart_percent_off'">
            <div class="rule-editor-field rule-editor-field--inline">
              <label class="rule-editor-field__label">Percent</label>
              <input v-model.number="form.rule.benefit.percent" type="number" class="rule-editor-field__input rule-editor-field__input--xs" min="0" max="100" />
              <span class="rule-editor-field__suffix">% off cart</span>
            </div>
          </template>
          <template v-else-if="benefitKind === 'x_for_y'">
            <div class="rule-editor-field rule-editor-field--inline">
              <label class="rule-editor-field__label">Buy</label>
              <input v-model.number="form.rule.benefit.buy_qty" type="number" class="rule-editor-field__input rule-editor-field__input--xs" min="1" />
              <span class="rule-editor-field__suffix">pay for</span>
              <input v-model.number="form.rule.benefit.pay_qty" type="number" class="rule-editor-field__input rule-editor-field__input--xs" min="0" />
            </div>
          </template>
          <template v-else-if="benefitKind === 'tiered'">
            <div class="rule-editor-field">
              <label class="rule-editor-field__label">Tiers</label>
              <div v-for="(tier, idx) in form.rule.benefit.tiers" :key="idx" class="rule-editor-field__row rule-editor-field__row--tier">
                <input v-model.number="tier.min" type="number" min="0" placeholder="Min" class="rule-editor-field__input rule-editor-field__input--xs" />
                <input v-model.number="tier.max" type="number" min="0" placeholder="Max (0=no limit)" class="rule-editor-field__input rule-editor-field__input--xs" />
                <input v-model.number="tier.percent_off" type="number" min="0" max="100" placeholder="%" class="rule-editor-field__input rule-editor-field__input--xs" />
                <input v-model.number="tier.fixed_off" type="number" min="0" step="0.01" placeholder="Fixed" class="rule-editor-field__input rule-editor-field__input--xs" />
                <button type="button" class="btn btn--outline btn--sm" @click="removeTier(idx)">Remove</button>
              </div>
              <button type="button" class="btn btn--outline btn--sm" @click="addTier">+ Add tier</button>
            </div>
          </template>
          <template v-else-if="benefitKind === 'free_gift'">
            <div class="rule-editor-field">
              <label class="rule-editor-field__label">Gift products</label>
              <el-select
                v-model="form.rule.benefit.product_ids"
                multiple
                filterable
                remote
                :remote-method="searchProducts"
                :loading="productsLoading"
                placeholder="Search and select products"
                value-key="id"
                class="rule-editor-field__el-select"
                @focus="loadProductsIfEmpty"
              >
                <el-option
                  v-for="p in productOptions"
                  :key="p.id"
                  :label="p.name"
                  :value="p.id"
                />
              </el-select>
            </div>
          </template>
          <p v-if="benefitKind !== 'category_discounts'" class="rule-editor-field__help">Set the discount to apply. Leave 0 if not used.</p>

          <!-- Exclude products from this rule -->
          <div class="rule-editor-field rule-editor-field--toggle">
            <label class="rule-editor-field__label">Exclude products from this rule</label>
            <label class="switch">
              <input v-model="form.rule.exclusions.enabled" type="checkbox" />
              <span class="switch__slider"></span>
            </label>
            <p class="rule-editor-field__help">Enable if you want to exclude specific products from this rule.</p>
          </div>
          <template v-if="form.rule.exclusions.enabled">
            <div class="rule-editor-field">
              <label class="rule-editor-field__label">Exclude</label>
              <select v-model="form.rule.exclusions.type" class="rule-editor-field__select">
                <option value="products">Specific products</option>
                <option value="categories">Specific categories</option>
                <option value="tags">Specific tags</option>
              </select>
              <p class="rule-editor-field__help">Choose if you want to exclude some specific products, categories, or tags from this rule.</p>
            </div>
            <div class="rule-editor-field">
              <label class="rule-editor-field__label">
                Choose which {{ form.rule.exclusions.type === 'products' ? 'product(s)' : form.rule.exclusions.type === 'categories' ? 'categor(y/ies)' : 'tag(s)' }} to exclude
                <span class="rule-editor-field__required">*</span>
              </label>
              <el-select
                v-if="form.rule.exclusions.type === 'products'"
                v-model="form.rule.exclusions.ids"
                multiple
                filterable
                remote
                :remote-method="searchExcludeProducts"
                :loading="excludeProductsLoading"
                placeholder="Search for a product..."
                class="rule-editor-field__el-select"
                @focus="loadExcludeProductsOnce"
              >
                <el-option v-for="p in excludeProductOptions" :key="p.id" :label="p.name" :value="p.id" />
              </el-select>
              <el-select
                v-else-if="form.rule.exclusions.type === 'categories'"
                v-model="form.rule.exclusions.ids"
                multiple
                filterable
                placeholder="Search for a category..."
                class="rule-editor-field__el-select"
                @focus="loadCategoriesOnce"
              >
                <el-option v-for="c in categoryOptions" :key="c.id" :label="c.name" :value="c.id" />
              </el-select>
              <el-select
                v-else
                v-model="form.rule.exclusions.ids"
                multiple
                filterable
                placeholder="Search for a tag..."
                class="rule-editor-field__el-select"
                @focus="loadTagsOnce"
              >
                <el-option v-for="t in tagOptions" :key="t.id" :label="t.name" :value="t.id" />
              </el-select>
            </div>
          </template>

          <div class="rule-editor-field rule-editor-field--toggle">
            <label class="rule-editor-field__label">Limit to specific products</label>
            <label class="switch">
              <input v-model="productFilterEnabled" type="checkbox" />
              <span class="switch__slider"></span>
            </label>
            <p class="rule-editor-field__help">Enable to apply this rule only to selected products or categories.</p>
          </div>
          <template v-if="productFilterEnabled">
            <div class="rule-editor-field">
              <label class="rule-editor-field__label">Apply to</label>
              <select v-model="form.rule.targets.type" class="rule-editor-field__select">
                <option value="all">All products</option>
                <option value="products">Specific products</option>
                <option value="categories">Specific categories</option>
              </select>
            </div>
            <div v-if="form.rule.targets.type === 'products'" class="rule-editor-field">
              <label class="rule-editor-field__label">Select products</label>
              <el-select
                v-model="form.rule.targets.products"
                multiple
                filterable
                remote
                :remote-method="searchProductsForTarget"
                :loading="targetProductsLoading"
                placeholder="Search products"
                value-key="id"
                class="rule-editor-field__el-select"
                @focus="loadTargetProductsIfEmpty"
              >
                <el-option
                  v-for="p in targetProductOptions"
                  :key="p.id"
                  :label="p.name"
                  :value="p.id"
                />
              </el-select>
            </div>
            <div v-if="form.rule.targets.type === 'categories'" class="rule-editor-field">
              <label class="rule-editor-field__label">Select categories</label>
              <el-select
                v-model="form.rule.targets.categories"
                multiple
                filterable
                placeholder="Select categories"
                class="rule-editor-field__el-select"
                @focus="loadCategoriesOnce"
              >
                <el-option
                  v-for="c in categoryOptions"
                  :key="c.id"
                  :label="c.name"
                  :value="c.id"
                />
              </el-select>
            </div>
          </template>
        </div>
      </section>

      <!-- Trigger Options -->
      <section class="rule-editor-card">
        <h2 class="rule-editor-card__title">Trigger Options</h2>
        <div class="rule-editor-card__body">
          <div class="rule-editor-field">
            <label class="rule-editor-field__label">Apply discount</label>
            <div class="rule-editor-field__radio-group">
              <label class="rule-editor-field__radio">
                <input v-model="applyDiscountMode" type="radio" value="always" />
                <span>Always</span>
              </label>
              <label class="rule-editor-field__radio">
                <input v-model="applyDiscountMode" type="radio" value="conditions" />
                <span>Only when specific conditions are met</span>
              </label>
            </div>
            <p class="rule-editor-field__help">Choose if the discount will be added automatically or under specific conditions (e.g. cart subtotal, quantity, products in cart).</p>
          </div>
          <template v-if="applyDiscountMode === 'conditions'">
            <div class="rule-editor-field">
              <strong class="rule-editor-field__label">Discount conditions</strong>
              <div class="rule-editor-conditions-list">
                <div
                  v-for="(item, idx) in conditionItems"
                  :key="idx"
                  class="rule-editor-condition-row"
                >
                  <span class="rule-editor-condition-label">{{ conditionTypeLabel(item.type) }}</span>
                  <span class="rule-editor-condition-op">{{ conditionOperatorLabel(item.operator) }}</span>
                  <span class="rule-editor-condition-val">{{ conditionValueSummary(item) }}</span>
                  <button type="button" class="btn btn--outline btn--sm" aria-label="Remove condition" @click="removeConditionItem(idx)">Remove</button>
                </div>
                <div v-if="showAddConditionForm" class="rule-editor-condition-add">
                  <select v-model="newCondition.type" class="rule-editor-field__select rule-editor-field__select--sm">
                    <option value="cart_subtotal">Cart subtotal</option>
                    <option value="cart_quantity">Cart quantity</option>
                    <option value="cart_items_count">Cart line items</option>
                    <option value="total_amount_spent">Total amount spent (customer)</option>
                    <option value="order_count">Number of orders (customer)</option>
                    <option value="product_in_cart">Products in cart</option>
                    <option value="user_role">User role</option>
                    <option value="user_id">User</option>
                    <option value="page">Page</option>
                  </select>
                  <select v-model="newCondition.operator" class="rule-editor-field__select rule-editor-field__select--sm">
                    <template v-if="isNumericConditionType(newCondition.type)">
                      <option value=">=">&#8805;</option>
                      <option value=">">&gt;</option>
                      <option value="<=">&#8804;</option>
                      <option value="<">&lt;</option>
                      <option value="=">=</option>
                    </template>
                    <template v-else-if="newCondition.type === 'page'">
                      <option value="=">is</option>
                    </template>
                    <template v-else>
                      <option value="in">in</option>
                      <option value="not_in">not in</option>
                    </template>
                  </select>
                  <template v-if="isNumericConditionType(newCondition.type)">
                    <input
                      v-model.number="newCondition.numericValue"
                      type="number"
                      min="0"
                      step="0.01"
                      class="rule-editor-field__input rule-editor-field__input--xs"
                      :placeholder="newCondition.type === 'cart_quantity' || newCondition.type === 'cart_items_count' || newCondition.type === 'order_count' ? '0' : '0.00'"
                    />
                  </template>
                  <template v-else-if="newCondition.type === 'page'">
                    <select v-model="newCondition.pageValue" class="rule-editor-field__select rule-editor-field__select--sm">
                      <option value="cart">Cart page</option>
                      <option value="checkout">Checkout page</option>
                      <option value="other">Other</option>
                    </select>
                  </template>
                  <template v-else-if="newCondition.type === 'user_role'">
                    <el-select
                      v-model="newCondition.roleIds"
                      multiple
                      placeholder="Select roles"
                      class="rule-editor-field__el-select rule-editor-field__el-select--inline"
                      @focus="loadRolesOnce"
                    >
                      <el-option v-for="r in roleOptions" :key="r.id" :label="r.name" :value="r.id" />
                    </el-select>
                  </template>
                  <template v-else-if="newCondition.type === 'user_id'">
                    <el-select
                      v-model="newCondition.userIds"
                      multiple
                      filterable
                      remote
                      :remote-method="searchUsers"
                      :loading="usersLoading"
                      placeholder="Search users"
                      value-key="id"
                      class="rule-editor-field__el-select rule-editor-field__el-select--inline"
                      @focus="loadUsersOnce"
                    >
                      <el-option v-for="u in userOptions" :key="u.id" :label="u.name + (u.email ? ' (' + u.email + ')' : '')" :value="u.id" />
                    </el-select>
                  </template>
                  <template v-else-if="newCondition.type === 'product_in_cart'">
                    <el-select
                      v-model="newCondition.productIds"
                      multiple
                      filterable
                      remote
                      :remote-method="searchProducts"
                      :loading="productsLoading"
                      placeholder="Search products"
                      value-key="id"
                      class="rule-editor-field__el-select rule-editor-field__el-select--inline"
                      @focus="loadProductsIfEmpty"
                    >
                      <el-option v-for="p in productOptions" :key="p.id" :label="p.name" :value="p.id" />
                    </el-select>
                  </template>
                  <button type="button" class="btn btn--primary btn--sm" @click="saveNewCondition">Add</button>
                  <button type="button" class="btn btn--outline btn--sm" @click="showAddConditionForm = false">Cancel</button>
                </div>
              </div>
              <button v-if="!showAddConditionForm" type="button" class="btn btn--outline btn--sm" @click="openAddCondition">+ Add condition</button>
            </div>
          </template>
        </div>
      </section>

      <!-- Rule Application -->
      <section class="rule-editor-card">
        <h2 class="rule-editor-card__title">Rule Application</h2>
        <div class="rule-editor-card__body">
          <div class="rule-editor-field">
            <label class="rule-editor-field__label">Apply discount to</label>
            <select v-model="applyTo" class="rule-editor-field__select">
              <option value="all">All users</option>
              <option value="roles">Specific user roles</option>
              <option value="users">Specific users</option>
            </select>
            <p class="rule-editor-field__help">Choose to apply this rule to all users or only to specific users or user roles.</p>
          </div>
          <div v-if="applyTo === 'roles'" class="rule-editor-field">
            <label class="rule-editor-field__label">Select roles</label>
            <el-select
              v-model="selectedRoleIds"
              multiple
              placeholder="Select roles"
              class="rule-editor-field__el-select"
              @focus="loadRolesOnce"
            >
              <el-option v-for="r in roleOptions" :key="r.id" :label="r.name" :value="r.id" />
            </el-select>
          </div>
          <div v-if="applyTo === 'users'" class="rule-editor-field">
            <label class="rule-editor-field__label">Select users</label>
            <el-select
              v-model="selectedUserIds"
              multiple
              filterable
              remote
              :remote-method="searchUsers"
              :loading="usersLoading"
              placeholder="Search users"
              value-key="id"
              class="rule-editor-field__el-select"
              @focus="loadUsersOnce"
            >
              <el-option v-for="u in userOptions" :key="u.id" :label="u.name + (u.email ? ' (' + u.email + ')' : '')" :value="u.id" />
            </el-select>
          </div>

          <div class="rule-editor-field rule-editor-field--toggle">
            <label class="rule-editor-field__label">Exclude users from this discount</label>
            <label class="switch">
              <input v-model="excludeUsersEnabled" type="checkbox" />
              <span class="switch__slider"></span>
            </label>
            <p class="rule-editor-field__help">Enable to exclude specific users or roles from this discount.</p>
          </div>
          <template v-if="excludeUsersEnabled">
            <div class="rule-editor-field">
              <label class="rule-editor-field__label">Exclude by role</label>
              <el-select
                v-model="excludeRoleIds"
                multiple
                placeholder="Select roles to exclude"
                class="rule-editor-field__el-select"
                @focus="loadRolesOnce"
              >
                <el-option v-for="r in roleOptions" :key="r.id" :label="r.name" :value="r.id" />
              </el-select>
            </div>
            <div class="rule-editor-field">
              <label class="rule-editor-field__label">Exclude by user</label>
              <el-select
                v-model="excludeUserIds"
                multiple
                filterable
                remote
                :remote-method="searchUsersExclude"
                :loading="usersExcludeLoading"
                placeholder="Search users to exclude"
                value-key="id"
                class="rule-editor-field__el-select"
                @focus="loadUsersExcludeOnce"
              >
                <el-option v-for="u in userExcludeOptions" :key="u.id" :label="u.name + (u.email ? ' (' + u.email + ')' : '')" :value="u.id" />
              </el-select>
            </div>
          </template>

          <div class="rule-editor-field">
            <label class="rule-editor-field__label">Schedule rule</label>
            <div class="rule-editor-field__radio-group">
              <label class="rule-editor-field__radio">
                <input v-model="scheduleMode" type="radio" value="always" />
                <span>Always active</span>
              </label>
              <label class="rule-editor-field__radio">
                <input v-model="scheduleMode" type="radio" value="schedule" />
                <span>Scheduled</span>
              </label>
            </div>
          </div>
          <template v-if="scheduleMode === 'schedule'">
            <div class="rule-editor-field rule-editor-field--inline">
              <label class="rule-editor-field__label">Start</label>
              <input v-model="form.rule.schedule.start" type="datetime-local" class="rule-editor-field__input" />
            </div>
            <div class="rule-editor-field rule-editor-field--inline">
              <label class="rule-editor-field__label">End</label>
              <input v-model="form.rule.schedule.end" type="datetime-local" class="rule-editor-field__input" />
            </div>
          </template>
        </div>
      </section>

      <!-- Customization -->
      <section class="rule-editor-card">
        <h2 class="rule-editor-card__title">Customization</h2>
        <div class="rule-editor-card__body">
          <div class="rule-editor-field rule-editor-field--toggle">
            <label class="rule-editor-field__label">Show discount badge</label>
            <label class="switch">
              <input v-model="form.rule.meta.show_badge" type="checkbox" />
              <span class="switch__slider"></span>
            </label>
          </div>
          <div class="rule-editor-field rule-editor-field--toggle">
            <label class="rule-editor-field__label">Show discount on shop</label>
            <label class="switch">
              <input v-model="form.rule.meta.show_on_shop" type="checkbox" />
              <span class="switch__slider"></span>
            </label>
          </div>
          <div class="rule-editor-field rule-editor-field--toggle">
            <label class="rule-editor-field__label">Show a custom message</label>
            <label class="switch">
              <input v-model="customMessageEnabled" type="checkbox" />
              <span class="switch__slider"></span>
            </label>
          </div>
          <div v-if="customMessageEnabled" class="rule-editor-field">
            <label class="rule-editor-field__label">Message</label>
            <textarea
              v-model="form.rule.meta.custom_message"
              class="rule-editor-field__textarea"
              rows="4"
              placeholder="Enter your custom message..."
            />
            <p class="rule-editor-field__wordcount">Word count: {{ wordCount }}</p>
          </div>
        </div>
      </section>

      <div class="rule-editor-page__footer">
        <p class="rule-editor-page__note">
          Note: Use [save_amount] and [save_percentage] shortcodes in your message to show saved amount or percentage.
        </p>
        <button type="button" class="btn btn--primary btn--lg" :disabled="saveSaving" @click="save">
          {{ saveSaving ? 'Saving…' : 'Save rule' }}
        </button>
      </div>
    </template>
    <div v-else class="rule-editor-page__error">Rule not found.</div>
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

const defaultRule = () => ({
  targets: { type: 'all', products: [], categories: [] },
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
    targets: { ...defaultRule().targets, ...(r.rule?.targets || {}), products: (r.rule?.targets?.products ?? []).map(Number).filter(Boolean), categories: (r.rule?.targets?.categories ?? []).map(Number).filter(Boolean) },
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
  if (!form.value.rule.meta) form.value.rule.meta = {};
  form.value.rule.meta.show_badge = form.value.rule.meta.show_badge !== false;
  form.value.rule.meta.show_on_shop = form.value.rule.meta.show_on_shop !== false;
  form.value.rule.meta.custom_message = form.value.rule.meta.custom_message || '';
  ensureTargetsArrays();
  productFilterEnabled.value = (form.value.rule.targets?.type || 'all') !== 'all';
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
