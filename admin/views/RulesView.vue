<template>
  <div class="dynamic-rules-page">
    <!-- Header -->
    <div class="page-header">
      <div class="page-header__content">
        <h1 class="page-header__title">Dynamic Rules</h1>
        <button class="btn btn--primary">
          <svg class="btn__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
          </svg>
          Add Rule
        </button>
      </div>
      <p class="page-header__subtitle">
        A list of all deals, price rules and discounts created for your shop
      </p>
    </div>

    <!-- Filters -->
    <div class="filters-card">
      <div class="filters-card__row">
        <select class="select">
          <option>Bulk actions</option>
          <option>Delete</option>
          <option>Enable</option>
          <option>Disable</option>
        </select>
        <button class="btn btn--outline btn--sm">Apply</button>
        
        <select class="select">
          <option>Filter by type...</option>
          <option>Category discount</option>
          <option>Buy 1 Get 1</option>
        </select>
        
        <select class="select">
          <option>Filter by status...</option>
          <option>Active</option>
          <option>Inactive</option>
        </select>
        
        <button class="btn btn--outline btn--sm">
          <svg class="btn__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
          </svg>
          Filter
        </button>
        
        <div class="spacer"></div>
        
        <div class="search-box">
          <svg class="search-box__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"></circle>
            <path d="m21 21-4.35-4.35"></path>
          </svg>
          <input
            type="text"
            class="search-box__input"
            placeholder="Search rule"
            v-model="searchQuery"
          />
        </div>
      </div>
      <div class="filters-card__footer">
        <span class="filters-card__count">16 items</span>
      </div>
    </div>

    <!-- Table -->
    <div class="table-container">
      <table class="rules-table">
        <thead class="rules-table__head">
          <tr>
            <th class="rules-table__th rules-table__th--checkbox">
              <input type="checkbox" class="checkbox" />
            </th>
            <th class="rules-table__th">Rule name</th>
            <th class="rules-table__th">Type</th>
            <th class="rules-table__th">Priority</th>
            <th class="rules-table__th">Status</th>
            <th class="rules-table__th">Enable</th>
          </tr>
        </thead>
        <tbody class="rules-table__body">
          <tr v-for="rule in rules" :key="rule.id" class="rules-table__row">
            <td class="rules-table__td rules-table__td--checkbox">
              <input type="checkbox" class="checkbox" />
            </td>
            <td class="rules-table__td">
              <a href="#" class="rules-table__link">{{ rule.name }}</a>
            </td>
            <td class="rules-table__td">{{ rule.type }}</td>
            <td class="rules-table__td">{{ rule.priority }}</td>
            <td class="rules-table__td">
              <span class="badge badge--warning">{{ rule.status }}</span>
            </td>
            <td class="rules-table__td">
              <label class="switch">
                <input
                  type="checkbox"
                  :checked="rule.enabled"
                  @change="toggleRule(rule.id)"
                />
                <span class="switch__slider"></span>
              </label>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

interface Rule {
  id: string;
  name: string;
  type: string;
  priority: number;
  status: 'Active' | 'Inactive';
  enabled: boolean;
}

const searchQuery = ref('');

const rules = ref<Rule[]>([
  {
    id: '1',
    name: '10% Discount',
    type: 'Category discount',
    priority: 1,
    status: 'Active',
    enabled: true,
  },
  {
    id: '2',
    name: 'BOGO',
    type: 'Buy 1 Get 1',
    priority: 2,
    status: 'Active',
    enabled: true,
  },
  {
    id: '3',
    name: 'Free Shipping',
    type: 'Cart discount',
    priority: 3,
    status: 'Active',
    enabled: false,
  },
]);

const toggleRule = (id: string) => {
  const rule = rules.value.find((r) => r.id === id);
  if (rule) {
    rule.enabled = !rule.enabled;
  }
};
</script>