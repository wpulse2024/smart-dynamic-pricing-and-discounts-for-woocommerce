<template>
  <Teleport to="body">
    <div
      v-if="visible"
      class="wpulse_pricing_templates_modal"
      role="dialog"
      aria-modal="true"
      aria-labelledby="wpulse-modal-title"
      @keydown.esc="close"
    >
      <div class="wpulse_pricing_templates_modal__backdrop" @click="close" />
      <div class="wpulse_pricing_templates_modal__box">
        <button
          type="button"
          class="wpulse_pricing_templates_modal__close"
          aria-label="Close"
          @click="close"
        >
          <span aria-hidden="true">&times;</span>
        </button>

        <div class="wpulse_pricing_templates_modal__inner">
          <!-- Left: Start from a template -->
          <div class="wpulse_pricing_templates_modal__section">
            <h2 id="wpulse-modal-title" class="wpulse_pricing_templates_modal__heading">
              Start from a template
            </h2>
            <div class="wpulse_pricing_templates_modal__grid">
              <button
                v-for="t in templates"
                :key="t.id"
                type="button"
                class="wpulse_pricing_templates_modal__card"
                @click="createFromTemplate(t.id)"
              >
                <span class="wpulse_pricing_templates_modal__card-icon">
                  <span v-if="t.icon" class="dashicons" :class="t.icon" />
                </span>
                <span class="wpulse_pricing_templates_modal__card-title">{{ t.title }}</span>
              </button>
            </div>
          </div>

          <!-- Right: Or start from scratch -->
          <div class="wpulse_pricing_templates_modal__section wpulse_pricing_templates_modal__section--scratch">
            <h2 class="wpulse_pricing_templates_modal__heading">
              Or start from scratch
            </h2>
            <ul class="wpulse_pricing_templates_modal__scratch-list">
              <li v-for="s in scratch" :key="s.id">
                <button
                  type="button"
                  class="wpulse_pricing_templates_modal__scratch-link"
                  @click="createFromScratch(s.id)"
                >
                  {{ s.label }}
                  <span class="wpulse_pricing_templates_modal__scratch-arrow">&rarr;</span>
                </button>
              </li>
            </ul>
          </div>
        </div>

        <div v-if="loading" class="wpulse_pricing_templates_modal__loading">Creating rule…</div>
        <div v-if="error" class="wpulse_pricing_templates_modal__error">{{ error }}</div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue';
import { api } from '../api';

const props = defineProps({
  visible: { type: Boolean, default: false },
});

const emit = defineEmits(['close']);

const templates = ref([]);
const scratch = ref([]);
const loading = ref(false);
const error = ref('');

function close() {
  if (!loading.value) {
    error.value = '';
    emit('close');
  }
}

async function load() {
  try {
    const data = await api.get('templates');
    templates.value = data.templates || [];
    scratch.value = data.scratch || [];
  } catch (e) {
    error.value = e?.message || 'Failed to load templates';
  }
}

watch(() => props.visible, (v) => {
  if (v) {
    load();
  }
});

async function createFromTemplate(templateId) {
  loading.value = true;
  error.value = '';
  try {
    const res = await api.post('rules/from-template', { template_id: templateId });
    if (res?.edit_url) {
      window.location.href = res.edit_url;
      return;
    }
    error.value = 'Could not create rule';
  } catch (e) {
    error.value = e?.message || 'Could not create rule';
  } finally {
    loading.value = false;
  }
}

async function createFromScratch(scratchType) {
  loading.value = true;
  error.value = '';
  try {
    const res = await api.post('rules/from-template', { scratch_type: scratchType });
    if (res?.edit_url) {
      window.location.href = res.edit_url;
      return;
    }
    error.value = 'Could not create rule';
  } catch (e) {
    error.value = e?.message || 'Could not create rule';
  } finally {
    loading.value = false;
  }
}
</script>
