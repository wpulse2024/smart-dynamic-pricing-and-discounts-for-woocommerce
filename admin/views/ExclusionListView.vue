<template>
  <div class="exclusion-list-page">
    <!-- Header -->
    <div class="page-header">
      <div class="page-header__content">
        <h1 class="page-header__title">Exclusion List</h1>
        <button type="button" class="btn btn--primary" @click="openModal">
          <svg class="btn__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
          </svg>
          Add exclusion
        </button>
      </div>
      <p class="page-header__subtitle">
        A list of products to exclude from all rules
      </p>
    </div>

    <!-- Empty state -->
    <div v-if="!loading && list.length === 0" class="exclusion-list-page__empty">
      <div class="exclusion-list-page__empty-icon" aria-hidden="true"></div>
      <p class="exclusion-list-page__empty-title">Your exclusion list is empty...</p>
      <p class="exclusion-list-page__empty-help">
        Click on the "Add exclusion" button to exclude a product, a category or a tag!
      </p>
      <button type="button" class="btn btn--primary" @click="openModal">Add exclusion</button>
    </div>

    <!-- Table -->
    <div v-else-if="!loading && list.length > 0" class="exclusion-list-page__table-wrap">
      <div class="exclusion-list-page__toolbar">
        <button type="button" class="btn btn--primary" @click="openModal">Add exclusion</button>
      </div>
      <div class="table-container">
        <table class="rules-table">
          <thead class="rules-table__head">
            <tr>
              <th class="rules-table__th">Type</th>
              <th class="rules-table__th">Name</th>
              <th class="rules-table__th rules-table__th--actions">Remove</th>
            </tr>
          </thead>
          <tbody class="rules-table__body">
            <tr v-for="row in list" :key="row.id" class="rules-table__row">
              <td class="rules-table__td">{{ row.type_label }}</td>
              <td class="rules-table__td">{{ row.name }}</td>
              <td class="rules-table__td">
                <button
                  type="button"
                  class="btn btn--outline btn--sm"
                  :disabled="removingId === row.id"
                  @click="remove(row.id)"
                >
                  {{ removingId === row.id ? '…' : 'Remove' }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Loading -->
    <div v-else class="exclusion-list-page__loading">Loading...</div>

    <!-- Modal -->
    <Teleport to="body">
      <div
        v-if="modalOpen"
        class="exclusion-list-page__modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="exclusion-modal-title"
        @keydown.esc="closeModal"
      >
        <div class="exclusion-list-page__modal-backdrop" @click="closeModal" />
        <div class="exclusion-list-page__modal-box">
          <h2 id="exclusion-modal-title" class="exclusion-list-page__modal-title">
            Add an exclusion to the list
          </h2>
          <div class="exclusion-list-page__modal-body">
            <div class="exclusion-list-page__field">
              <label for="exclusion-type">Exclusion type</label>
              <select
                id="exclusion-type"
                v-model="form.type"
                class="exclusion-list-page__select"
                @change="onTypeChange"
              >
                <option value="product">Product</option>
                <option value="category">Category</option>
                <option value="tag">Tag</option>
              </select>
            </div>
            <div class="exclusion-list-page__field">
              <label>{{ selectorLabel }}</label>
              <input
                v-model="searchQuery"
                type="text"
                class="exclusion-list-page__input"
                :placeholder="searchPlaceholder"
                autocomplete="off"
                @input="onSearchInput"
                @focus="onSearchFocus"
              />
              <ul
                v-if="searchResults.length > 0 || searchLoading"
                class="exclusion-list-page__results"
              >
                <li v-if="searchLoading" class="exclusion-list-page__results-loading">Loading...</li>
                <li
                  v-for="item in searchResults"
                  v-else
                  :key="item.id"
                  class="exclusion-list-page__result-item"
                  :class="{ 'exclusion-list-page__result-item--selected': isSelected(item.id) }"
                  @click="toggleItem(item)"
                >
                  <span class="exclusion-list-page__result-check" aria-hidden="true">
                    {{ isSelected(item.id) ? '✓' : '' }}
                  </span>
                  {{ item.name }}
                </li>
              </ul>
              <div v-if="selectedItems.length > 0" class="exclusion-list-page__chips">
                <span
                  v-for="item in selectedItems"
                  :key="item.id"
                  class="exclusion-list-page__chip"
                >
                  {{ item.name }}
                  <button
                    type="button"
                    class="exclusion-list-page__chip-remove"
                    aria-label="Remove"
                    @click="deselectItem(item.id)"
                  >
                    ×
                  </button>
                </span>
              </div>
            </div>
            <p v-if="modalError" class="exclusion-list-page__modal-error">{{ modalError }}</p>
          </div>
          <div class="exclusion-list-page__modal-footer">
            <button type="button" class="btn btn--outline" @click="closeModal">Cancel</button>
            <button
              type="button"
              class="btn btn--primary"
              :disabled="submitLoading || selectedItems.length === 0"
              @click="submitAdd"
            >
              {{ submitLoading ? 'Adding…' : 'Add the exclusion to the list' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { exclusionApi } from '../api/exclusion';

const list = ref([]);
const loading = ref(true);
const removingId = ref(null);

const modalOpen = ref(false);
const form = ref({ type: 'product' });
const searchQuery = ref('');
const searchResults = ref([]);
const searchLoading = ref(false);
const searchDebounce = ref(null);
const allItemsForType = ref([]); // cached full list for current type (shown when search empty)
const selectedItems = ref([]); // { id, name }[] multi-select
const modalError = ref('');
const submitLoading = ref(false);

const selectorLabel = computed(() => {
  if (form.value.type === 'product') return 'Choose products to add';
  if (form.value.type === 'category') return 'Choose categories to add';
  return 'Choose tags to add';
});

const searchPlaceholder = computed(() => {
  if (form.value.type === 'product') return 'Search for a product...';
  if (form.value.type === 'category') return 'Search for a category...';
  return 'Search for a tag...';
});

function fetchList() {
  loading.value = true;
  exclusionApi
    .getList()
    .then((data) => {
      list.value = Array.isArray(data) ? data : [];
    })
    .catch(() => {
      list.value = [];
    })
    .finally(() => {
      loading.value = false;
    });
}

function openModal() {
  modalOpen.value = true;
  form.value = { type: 'product' };
  searchQuery.value = '';
  searchResults.value = [];
  allItemsForType.value = [];
  selectedItems.value = [];
  modalError.value = '';
  loadAllForType();
}

function closeModal() {
  modalOpen.value = false;
}

function onTypeChange() {
  searchQuery.value = '';
  selectedItems.value = [];
  modalError.value = '';
  loadAllForType();
}

/** Load all items for current type and show in the select dropdown under the type box */
function loadAllForType() {
  const type = form.value.type;
  searchLoading.value = true;
  searchResults.value = [];

  const done = (data) => {
    const items = Array.isArray(data) ? data : [];
    allItemsForType.value = items;
    searchResults.value = items;
    searchLoading.value = false;
  };

  if (type === 'product') {
    exclusionApi.searchProducts('', 100).then(done).catch(() => { searchLoading.value = false; });
  } else if (type === 'category') {
    exclusionApi.searchCategories('').then(done).catch(() => { searchLoading.value = false; });
  } else {
    exclusionApi.searchTags('').then(done).catch(() => { searchLoading.value = false; });
  }
}

function runSearch() {
  const q = searchQuery.value.trim();
  const type = form.value.type;
  searchLoading.value = true;
  searchResults.value = [];

  const done = (data) => {
    searchResults.value = Array.isArray(data) ? data : [];
    searchLoading.value = false;
  };

  if (type === 'product') {
    exclusionApi.searchProducts(q, 50).then(done).catch(() => { searchLoading.value = false; });
  } else if (type === 'category') {
    exclusionApi.searchCategories(q).then(done).catch(() => { searchLoading.value = false; });
  } else {
    exclusionApi.searchTags(q).then(done).catch(() => { searchLoading.value = false; });
  }
}

function onSearchInput() {
  clearTimeout(searchDebounce.value);
  const q = searchQuery.value.trim();
  if (q.length === 0) {
    searchResults.value = [...allItemsForType.value];
    return;
  }
  searchDebounce.value = setTimeout(runSearch, 300);
}

function onSearchFocus() {
  if (searchQuery.value.trim().length > 0) {
    runSearch();
  } else if (searchResults.value.length === 0 && !searchLoading.value) {
    loadAllForType();
  }
}

function isSelected(id) {
  return selectedItems.value.some((s) => s.id === id);
}

function toggleItem(item) {
  if (isSelected(item.id)) {
    selectedItems.value = selectedItems.value.filter((s) => s.id !== item.id);
  } else {
    selectedItems.value = [...selectedItems.value, { id: item.id, name: item.name }];
  }
}

function deselectItem(id) {
  selectedItems.value = selectedItems.value.filter((s) => s.id !== id);
}

function submitAdd() {
  if (selectedItems.value.length === 0) {
    modalError.value = 'Please select at least one item.';
    return;
  }
  modalError.value = '';
  submitLoading.value = true;
  const ids = selectedItems.value.map((s) => s.id);
  exclusionApi
    .addMultiple(form.value.type, ids)
    .then(() => {
      closeModal();
      fetchList();
    })
    .catch((err) => {
      modalError.value = err?.data?.message || err?.message || 'An error occurred.';
    })
    .finally(() => {
      submitLoading.value = false;
    });
}

function remove(id) {
  removingId.value = id;
  exclusionApi
    .delete(id)
    .then(() => fetchList())
    .catch(() => {})
    .finally(() => {
      removingId.value = null;
    });
}

onMounted(() => {
  fetchList();
});
</script>

<style scoped lang="scss">
.exclusion-list-page {
  max-width: 900px;
  margin: 0 auto;
  padding: 24px;
}

.page-header {
  margin-bottom: 24px;

  &__content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
  }

  &__title {
    font-size: 28px;
    font-weight: 600;
    color: #111827;
    margin: 0;
  }

  &__subtitle {
    font-size: 14px;
    color: #6b7280;
    margin: 0;
  }
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  font-size: 14px;
  font-weight: 500;
  border: 1px solid transparent;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
  background: none;

  &__icon {
    width: 16px;
    height: 16px;
  }

  &--primary {
    background-color: #2563eb;
    color: #fff;

    &:hover:not(:disabled) {
      background-color: #1d4ed8;
    }
  }

  &--outline {
    border-color: #d1d5db;
    color: #374151;

    &:hover:not(:disabled) {
      background-color: #f9fafb;
    }
  }

  &--sm {
    padding: 6px 12px;
    font-size: 13px;
  }
}

.exclusion-list-page__empty {
  text-align: center;
  padding: 48px 24px;
  background: #f9fafb;
  border: 1px dashed #e5e7eb;
  border-radius: 8px;
}

.exclusion-list-page__empty-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto 16px;
  background: #e5e7eb;
  border-radius: 50%;
  opacity: 0.8;
}

.exclusion-list-page__empty-title {
  font-size: 18px;
  font-weight: 600;
  color: #111827;
  margin: 0 0 8px;
}

.exclusion-list-page__empty-help {
  font-size: 14px;
  color: #6b7280;
  margin: 0 0 20px;
}

.exclusion-list-page__table-wrap {
  margin-top: 16px;
}

.exclusion-list-page__toolbar {
  margin-bottom: 16px;
}

.table-container {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  overflow: hidden;
}

.rules-table {
  width: 100%;
  border-collapse: collapse;

  &__head {
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
  }

  &__th {
    padding: 12px 16px;
    text-align: left;
    font-size: 12px;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;

    &--actions {
      width: 100px;
    }
  }

  &__body .rules-table__row {
    border-bottom: 1px solid #f3f4f6;

    &:last-child {
      border-bottom: none;
    }
  }

  &__td {
    padding: 12px 16px;
    font-size: 14px;
    color: #374151;
  }
}

.exclusion-list-page__loading {
  padding: 24px;
  text-align: center;
  color: #6b7280;
}

/* Modal */
.exclusion-list-page__modal {
  position: fixed;
  inset: 0;
  z-index: 100000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  box-sizing: border-box;
}

.exclusion-list-page__modal-backdrop {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  cursor: pointer;
}

.exclusion-list-page__modal-box {
  position: relative;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  width: 100%;
  max-width: 480px;
  max-height: 90vh;
  overflow: auto;
}

.exclusion-list-page__modal-title {
  margin: 0;
  padding: 20px 24px 12px;
  font-size: 18px;
  font-weight: 600;
  color: #111827;
  border-bottom: 1px solid #e5e7eb;
}

.exclusion-list-page__modal-body {
  padding: 20px 24px;
}

.exclusion-list-page__field {
  margin-bottom: 16px;

  label {
    display: block;
    font-weight: 500;
    color: #374151;
    margin-bottom: 6px;
    font-size: 14px;
  }
}

.exclusion-list-page__select,
.exclusion-list-page__input {
  width: 100%;
  padding: 8px 12px;
  font-size: 14px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  background: #fff;
  box-sizing: border-box;
}

.exclusion-list-page__input:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
}

.exclusion-list-page__results {
  list-style: none;
  margin: 8px 0 0;
  padding: 0;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  max-height: 200px;
  overflow-y: auto;
  background: #fff;
}

.exclusion-list-page__results-loading {
  padding: 12px;
  color: #6b7280;
  font-size: 14px;
}

.exclusion-list-page__result-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 12px;
  font-size: 14px;
  cursor: pointer;
  border-bottom: 1px solid #f3f4f6;

  &:last-child {
    border-bottom: none;
  }

  &:hover {
    background: #f9fafb;
  }

  &--selected {
    background: #eff6ff;

    .exclusion-list-page__result-check {
      color: #2563eb;
    }
  }
}

.exclusion-list-page__result-check {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 18px;
  height: 18px;
  flex-shrink: 0;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 12px;
  color: transparent;
}

.exclusion-list-page__chips {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 12px;
}

.exclusion-list-page__chip {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px 4px 10px;
  font-size: 13px;
  color: #374151;
  background: #f3f4f6;
  border-radius: 6px;
}

.exclusion-list-page__chip-remove {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 20px;
  height: 20px;
  padding: 0;
  font-size: 18px;
  line-height: 1;
  color: #6b7280;
  background: none;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: color 0.15s, background 0.15s;

  &:hover {
    color: #111827;
    background: #e5e7eb;
  }
}

.exclusion-list-page__modal-error {
  margin-top: 12px;
  color: #dc2626;
  font-size: 13px;
}

.exclusion-list-page__modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 16px 24px 20px;
  border-top: 1px solid #e5e7eb;
}
</style>
