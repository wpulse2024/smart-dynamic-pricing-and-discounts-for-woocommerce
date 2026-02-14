<template>
  <div class="dynamic-rules-page">
    <!-- Header -->
    <div class="page-header">
      <div class="page-header__content">
        <h1 class="page-header__title">Dynamic Rules</h1>
        <button type="button" class="btn btn--primary" @click="showTemplatesModal = true">
          <svg class="btn__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
          </svg>
          Add new rule
        </button>
      </div>
      <p class="page-header__subtitle">
        A list of all deals, price rules and discounts created for your shop
      </p>
    </div>

    <!-- Filters -->
    <div class="filters-card">
      <div class="filters-card__row">
        <select v-model="bulkAction" class="select">
          <option value="">Bulk actions</option>
          <option value="delete">Delete</option>
          <option value="enable">Enable</option>
          <option value="disable">Disable</option>
        </select>
        <button
          type="button"
          class="btn btn--outline btn--sm"
          :disabled="!bulkAction || selectedIds.size === 0"
          @click="applyBulkAction"
        >
          Apply
        </button>

        <select v-model="filterType" class="select">
          <option value="">Filter by type...</option>
          <option v-for="opt in typeFilterOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
        </select>

        <select v-model="filterStatus" class="select">
          <option value="">Filter by status...</option>
          <option value="active">Active</option>
          <option value="draft">Draft</option>
          <option value="disabled">Disabled</option>
        </select>

        <button type="button" class="btn btn--outline btn--sm" @click="clearFilters">
          <svg class="btn__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
          </svg>
          Clear filters
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
        <span class="filters-card__count">{{ filteredRules.length }} items</span>
      </div>
    </div>

    <!-- Table -->
    <div class="table-container">
      <table class="rules-table">
        <thead class="rules-table__head">
          <tr>
            <th class="rules-table__th rules-table__th--checkbox">
              <input
                type="checkbox"
                class="checkbox"
                :checked="isAllFilteredSelected"
                :indeterminate="selectedIds.size > 0 && !isAllFilteredSelected"
                @change="toggleSelectAllFiltered"
              />
            </th>
            <th class="rules-table__th">Rule name</th>
            <th class="rules-table__th">Type</th>
            <th class="rules-table__th">Priority</th>
            <th class="rules-table__th">Status</th>
            <th class="rules-table__th">Enable</th>
          </tr>
        </thead>
        <tbody class="rules-table__body">
          <tr v-for="rule in filteredRules" :key="rule.id" class="rules-table__row">
            <td class="rules-table__td rules-table__td--checkbox">
              <input
                type="checkbox"
                class="checkbox"
                :checked="selectedIds.has(rule.id)"
                @change="toggleSelect(rule.id)"
              />
            </td>
            <td class="rules-table__td">
              <router-link :to="'/rules/edit/' + rule.id" class="rules-table__link">{{ rule.name }}</router-link>
            </td>
            <td class="rules-table__td">{{ typeLabel(rule.type) }}</td>
            <td class="rules-table__td">{{ rule.priority }}</td>
            <td class="rules-table__td">
              <span class="badge" :class="badgeClass(rule.status)">{{ rule.status }}</span>
            </td>
            <td class="rules-table__td">
              <label class="switch">
                <input
                  type="checkbox"
                  :checked="rule.status === 'active'"
                  @change="toggleRule(rule)"
                />
                <span class="switch__slider"></span>
              </label>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <TemplatesModal :visible="showTemplatesModal" @close="showTemplatesModal = false" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { api } from '../api';
import TemplatesModal from '../components/TemplatesModal.vue';

const searchQuery = ref('');
const filterType = ref('');
const filterStatus = ref('');
const bulkAction = ref('');
const showTemplatesModal = ref(false);
const rules = ref([]);
const selectedIds = ref(new Set());
const bulkApplying = ref(false);

const typeFilterOptions = [
  { value: 'quantity_discount', label: 'Quantity discount' },
  { value: 'category_discount', label: 'Category discount' },
  { value: 'gift', label: 'Gift' },
  { value: 'global_discount', label: 'Global discount' },
  { value: 'cart_discount', label: 'Cart discount' },
  { value: 'free_shipping', label: 'Free shipping' },
  { value: 'user_role_discount', label: 'User role discount' },
  { value: 'checkout_deal', label: 'Checkout deal' },
];

const filteredRules = computed(() => {
  let list = rules.value;
  const q = (searchQuery.value || '').trim().toLowerCase();
  if (q) {
    list = list.filter((r) => (r.name || '').toLowerCase().includes(q));
  }
  if (filterType.value) {
    list = list.filter((r) => (r.type || '') === filterType.value);
  }
  if (filterStatus.value) {
    list = list.filter((r) => (r.status || '') === filterStatus.value);
  }
  return list;
});

const isAllFilteredSelected = computed(() => {
  const filtered = filteredRules.value;
  if (!filtered.length) return false;
  return filtered.every((r) => selectedIds.value.has(r.id));
});

function typeLabel(type) {
  const opt = typeFilterOptions.find((o) => o.value === type);
  return opt ? opt.label : (type || '').replace(/_/g, ' ');
}

function badgeClass(status) {
  if (status === 'active') return 'badge--success';
  if (status === 'disabled') return 'badge--error';
  return 'badge--warning';
}

function toggleSelect(id) {
  const next = new Set(selectedIds.value);
  if (next.has(id)) next.delete(id);
  else next.add(id);
  selectedIds.value = next;
}

function toggleSelectAllFiltered(e) {
  const checked = e.target.checked;
  const filtered = filteredRules.value;
  const next = new Set(selectedIds.value);
  if (checked) {
    filtered.forEach((r) => next.add(r.id));
  } else {
    filtered.forEach((r) => next.delete(r.id));
  }
  selectedIds.value = next;
}

function clearFilters() {
  filterType.value = '';
  filterStatus.value = '';
  searchQuery.value = '';
}

async function applyBulkAction() {
  const action = bulkAction.value;
  const ids = Array.from(selectedIds.value);
  if (!action || !ids.length) return;
  bulkApplying.value = true;
  try {
    if (action === 'delete') {
      await Promise.all(ids.map((id) => api.delete(`rules/${id}`)));
      rules.value = rules.value.filter((r) => !ids.includes(r.id));
    } else if (action === 'enable' || action === 'disable') {
      const status = action === 'enable' ? 'active' : 'disabled';
      await Promise.all(ids.map((id) => api.patch(`rules/${id}`, { status })));
      rules.value.forEach((r) => {
        if (ids.includes(r.id)) r.status = status;
      });
    }
    selectedIds.value = new Set();
    bulkAction.value = '';
  } catch (_) {}
  bulkApplying.value = false;
}

function toggleRule(rule) {
  const next = rule.status === 'active' ? 'disabled' : 'active';
  api.patch(`rules/${rule.id}`, { status: next }).then(() => {
    rule.status = next;
  }).catch(() => {});
}

function loadRules() {
  api.get('rules').then((data) => {
    rules.value = Array.isArray(data) ? data : (data?.rules || []);
  }).catch(() => {
    rules.value = [];
  });
}

onMounted(() => {
  loadRules();
});
</script>