<template>
  <div class="rules-view">
    <el-card>
      <template #header>
        <span>Pricing rules</span>
        <el-button type="primary" class="header-btn" @click="openDialog()">
          <el-icon><Plus /></el-icon>
          Add rule
        </el-button>
      </template>
      <el-table v-loading="loading" :data="rules" stripe>
        <el-table-column prop="name" label="Name" min-width="140" />
        <el-table-column prop="type" label="Type" width="100" />
        <el-table-column prop="value" label="Value" width="100" />
        <el-table-column prop="min_quantity" label="Min qty" width="90" />
        <el-table-column prop="max_quantity" label="Max qty" width="90" />
        <el-table-column prop="active" label="Active" width="80">
          <template #default="{ row }">
            <el-tag :type="row.active ? 'success' : 'info'" size="small">
              {{ row.active ? 'Yes' : 'No' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="Actions" width="140" fixed="right">
          <template #default="{ row }">
            <el-button link type="primary" size="small" @click="openDialog(row)">Edit</el-button>
            <el-button link type="danger" size="small" @click="confirmDelete(row)">Delete</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <el-dialog v-model="dialogVisible" :title="editingId ? 'Edit rule' : 'Add rule'" width="500px">
      <el-form :model="form" label-width="120px">
        <el-form-item label="Name">
          <el-input v-model="form.name" placeholder="Rule name" />
        </el-form-item>
        <el-form-item label="Type">
          <el-select v-model="form.type" placeholder="Type">
            <el-option label="Percentage" value="percentage" />
            <el-option label="Fixed" value="fixed" />
          </el-select>
        </el-form-item>
        <el-form-item label="Value">
          <el-input-number v-model="form.value" :min="0" :precision="2" />
        </el-form-item>
        <el-form-item label="Min quantity">
          <el-input-number v-model="form.min_quantity" :min="0" placeholder="Optional" clearable />
        </el-form-item>
        <el-form-item label="Max quantity">
          <el-input-number v-model="form.max_quantity" :min="0" placeholder="Optional" clearable />
        </el-form-item>
        <el-form-item label="Active">
          <el-switch v-model="form.active" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">Cancel</el-button>
        <el-button type="primary" @click="saveRule">Save</el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="deleteDialogVisible" title="Delete rule" width="400px">
      <p>Are you sure you want to delete this rule?</p>
      <template #footer>
        <el-button @click="deleteDialogVisible = false">Cancel</el-button>
        <el-button type="danger" @click="deleteRule">Delete</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import { Plus } from '@element-plus/icons-vue';
import { api } from '../api';

const loading = ref(false);
const rules = ref([]);
const dialogVisible = ref(false);
const deleteDialogVisible = ref(false);
const editingId = ref(null);
const deletingId = ref(null);

const form = reactive({
  name: '',
  type: 'percentage',
  value: 0,
  min_quantity: null,
  max_quantity: null,
  active: true,
});

async function fetchRules() {
  loading.value = true;
  try {
    const res = await api.get('rules');
    rules.value = Array.isArray(res) ? res : (res.data || []);
  } catch (e) {
    ElMessage.error(e?.message || 'Failed to load rules');
  } finally {
    loading.value = false;
  }
}

function openDialog(row = null) {
  editingId.value = row ? row.id : null;
  if (row) {
    form.name = row.name;
    form.type = row.type || 'percentage';
    form.value = Number(row.value) || 0;
    form.min_quantity = row.min_quantity != null ? Number(row.min_quantity) : null;
    form.max_quantity = row.max_quantity != null ? Number(row.max_quantity) : null;
    form.active = !!row.active;
  } else {
    form.name = '';
    form.type = 'percentage';
    form.value = 0;
    form.min_quantity = null;
    form.max_quantity = null;
    form.active = true;
  }
  dialogVisible.value = true;
}

async function saveRule() {
  const payload = {
    name: form.name,
    type: form.type,
    value: form.value,
    min_quantity: form.min_quantity,
    max_quantity: form.max_quantity,
    active: form.active,
  };
  try {
    if (editingId.value) {
      await api.put(`rules/${editingId.value}`, payload);
      ElMessage.success('Rule updated');
    } else {
      await api.post('rules', payload);
      ElMessage.success('Rule created');
    }
    dialogVisible.value = false;
    fetchRules();
  } catch (e) {
    ElMessage.error(e?.message || 'Failed to save');
  }
}

function confirmDelete(row) {
  deletingId.value = row.id;
  deleteDialogVisible.value = true;
}

async function deleteRule() {
  if (!deletingId.value) return;
  try {
    await api.delete(`rules/${deletingId.value}`);
    ElMessage.success('Rule deleted');
    deleteDialogVisible.value = false;
    deletingId.value = null;
    fetchRules();
  } catch (e) {
    ElMessage.error(e?.message || 'Failed to delete');
  }
}

onMounted(() => fetchRules());
</script>

<style lang="scss" scoped>
.rules-view {
  .header-btn {
    float: right;
  }
  .el-card__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
}
</style>
