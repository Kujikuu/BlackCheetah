<script setup lang="ts">
interface Props {
  isDrawerOpen: boolean
  franchisor?: any
}

interface Emit {
  (e: 'update:isDrawerOpen', value: boolean): void
  (e: 'franchisorData', value: any): void
}

const props = withDefaults(defineProps<Props>(), {
  franchisor: undefined,
})

const emit = defineEmits<Emit>()

const isFormValid = ref(false)
const refForm = ref()

const formData = ref({
  id: null as number | null,
  fullName: '',
  email: '',
  franchiseName: '',
  plan: 'Basic',
  status: 'active',
  avatar: '',
})

const plans = [
  { title: 'Basic', value: 'Basic' },
  { title: 'Pro', value: 'Pro' },
  { title: 'Enterprise', value: 'Enterprise' },
]

const statusOptions = [
  { title: 'Active', value: 'active' },
  { title: 'Pending', value: 'pending' },
  { title: 'Inactive', value: 'inactive' },
]

const resetForm = () => {
  formData.value = {
    id: null,
    fullName: '',
    email: '',
    franchiseName: '',
    plan: 'Basic',
    status: 'active',
    avatar: '',
  }
  refForm.value?.reset()
}

// Watch for franchisor prop changes
watch(() => props.franchisor, newVal => {
  if (newVal) {
    formData.value = {
      id: newVal.id,
      fullName: newVal.fullName,
      email: newVal.email,
      franchiseName: newVal.franchiseName,
      plan: newVal.plan,
      status: newVal.status,
      avatar: newVal.avatar || '',
    }
  }
  else {
    resetForm()
  }
}, { immediate: true })

const onSubmit = () => {
  refForm.value?.validate().then(({ valid }: any) => {
    if (valid) {
      emit('franchisorData', { ...formData.value })
      emit('update:isDrawerOpen', false)
      resetForm()
    }
  })
}

const handleDrawerModelValueUpdate = (val: boolean) => {
  emit('update:isDrawerOpen', val)
  if (!val)
    resetForm()
}
</script>

<template>
  <VNavigationDrawer
    temporary
    location="end"
    :model-value="props.isDrawerOpen"
    width="400"
    @update:model-value="handleDrawerModelValueUpdate"
  >
    <!-- Header -->
    <AppDrawerHeaderSection
      :title="props.franchisor ? 'Edit Franchisor' : 'Add New Franchisor'"
      @cancel="handleDrawerModelValueUpdate(false)"
    />

    <VDivider />

    <VCard flat>
      <VCardText>
        <VForm
          ref="refForm"
          v-model="isFormValid"
          @submit.prevent="onSubmit"
        >
          <VRow>
            <!-- Full Name -->
            <VCol cols="12">
              <AppTextField
                v-model="formData.fullName"
                label="Full Name"
                placeholder="John Doe"
                :rules="[requiredValidator]"
              />
            </VCol>

            <!-- Email -->
            <VCol cols="12">
              <AppTextField
                v-model="formData.email"
                label="Email"
                type="email"
                placeholder="john.doe@example.com"
                :rules="[requiredValidator, emailValidator]"
              />
            </VCol>

            <!-- Franchise Name -->
            <VCol cols="12">
              <AppTextField
                v-model="formData.franchiseName"
                label="Franchise Name"
                placeholder="Acme Corporation"
                :rules="[requiredValidator]"
              />
            </VCol>

            <!-- Plan -->
            <VCol cols="12">
              <AppSelect
                v-model="formData.plan"
                label="Plan"
                :items="plans"
                :rules="[requiredValidator]"
              />
            </VCol>

            <!-- Status -->
            <VCol cols="12">
              <AppSelect
                v-model="formData.status"
                label="Status"
                :items="statusOptions"
                :rules="[requiredValidator]"
              />
            </VCol>

            <!-- Submit and Cancel -->
            <VCol cols="12">
              <VBtn
                type="submit"
                class="me-3"
              >
                {{ props.franchisor ? 'Update' : 'Submit' }}
              </VBtn>
              <VBtn
                variant="outlined"
                color="secondary"
                @click="handleDrawerModelValueUpdate(false)"
              >
                Cancel
              </VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </VNavigationDrawer>
</template>
