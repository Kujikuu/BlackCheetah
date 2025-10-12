<script setup lang="ts">
interface Props {
  isDrawerOpen: boolean
  salesUser?: any
}

interface Emit {
  (e: 'update:isDrawerOpen', value: boolean): void
  (e: 'salesUserData', value: any): void
}

const props = withDefaults(defineProps<Props>(), {
  salesUser: undefined,
})

const emit = defineEmits<Emit>()

const isFormValid = ref(false)
const refForm = ref()

const formData = ref({
  id: null as number | null,
  fullName: '',
  email: '',
  phone: '',
  city: '',
  status: 'active',
  avatar: '',
})

const statusOptions = [
  { title: 'Active', value: 'active' },
  { title: 'Pending', value: 'pending' },
  { title: 'Inactive', value: 'inactive' },
]

const cities = [
  { title: 'Riyadh', value: 'Riyadh' },
  { title: 'Jeddah', value: 'Jeddah' },
  { title: 'Makkah', value: 'Makkah' },
  { title: 'Madinah', value: 'Madinah' },
  { title: 'Dammam', value: 'Dammam' },
  { title: 'Khobar', value: 'Khobar' },
  { title: 'Tabuk', value: 'Tabuk' },
]

const resetForm = () => {
  formData.value = {
    id: null,
    fullName: '',
    email: '',
    phone: '',
    city: '',
    status: 'active',
    avatar: '',
  }
  refForm.value?.reset()
}

// Watch for salesUser prop changes
watch(() => props.salesUser, newVal => {
  if (newVal) {
    formData.value = {
      id: newVal.id,
      fullName: newVal.fullName,
      email: newVal.email,
      phone: newVal.phone,
      city: newVal.city,
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
      emit('salesUserData', { ...formData.value })
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
      :title="props.salesUser ? 'Edit Sales User' : 'Add New Sales User'"
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
                placeholder="Mike Johnson"
                :rules="[requiredValidator]"
              />
            </VCol>

            <!-- Email -->
            <VCol cols="12">
              <AppTextField
                v-model="formData.email"
                label="Email"
                type="email"
                placeholder="mike.j@example.com"
                :rules="[requiredValidator, emailValidator]"
              />
            </VCol>

            <!-- Phone -->
            <VCol cols="12">
              <AppTextField
                v-model="formData.phone"
                label="Phone"
                placeholder="+966 50 123 4567"
                :rules="[requiredValidator]"
              />
            </VCol>

            <!-- City -->
            <VCol cols="12">
              <AppSelect
                v-model="formData.city"
                label="City"
                :items="cities"
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
                {{ props.salesUser ? 'Update' : 'Submit' }}
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
