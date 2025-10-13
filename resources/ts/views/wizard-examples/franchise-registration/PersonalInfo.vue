<script setup lang="ts">
interface PersonalInfoData {
  contactNumber: string
  country: string
  state: string
  city: string
  address: string
}

interface Props {
  formData: PersonalInfoData
}

interface Emit {
  (e: 'update:formData', value: PersonalInfoData): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const localFormData = computed({
  get: () => props.formData,
  set: val => emit('update:formData', val),
})

// 👉 Options
const countries = [
  { title: 'Saudi Arabia', value: 'Saudi Arabia' },
  { title: 'United Arab Emirates', value: 'United Arab Emirates' },
  { title: 'Qatar', value: 'Qatar' },
  { title: 'Kuwait', value: 'Kuwait' },
  { title: 'Oman', value: 'Oman' },
  { title: 'Bahrain', value: 'Bahrain' },
  { title: 'Jordan', value: 'Jordan' },
  { title: 'Lebanon', value: 'Lebanon' },
  { title: 'Egypt', value: 'Egypt' },
  { title: 'Iraq', value: 'Iraq' },
  { title: 'Syria', value: 'Syria' },
  { title: 'Palestine', value: 'Palestine' },
  { title: 'Yemen', value: 'Yemen' },
]
</script>

<template>
  <div>
    <div class="text-h4 mb-1">
      Personal Information
    </div>
    <p class="text-body-1 mb-6">
      Please provide your personal contact details
    </p>

    <VForm>
      <VRow>
        <VCol cols="12" md="6">
          <AppTextField v-model="localFormData.contactNumber" label="Contact Number"
            placeholder="Enter your contact number" required />
        </VCol>
        <VCol cols="12" md="6">
          <AppSelect v-model="localFormData.country" label="Country" :items="countries" placeholder="Select country"
            required />
        </VCol>
        <VCol cols="12" md="6">
          <AppTextField v-model="localFormData.state" label="State/Province" placeholder="Enter state or province" />
        </VCol>
        <VCol cols="12" md="6">
          <AppTextField v-model="localFormData.city" label="City" placeholder="Enter city" required />
        </VCol>
        <VCol cols="12">
          <AppTextarea v-model="localFormData.address" label="Address" placeholder="Enter your full address" rows="3" />
        </VCol>
      </VRow>
    </VForm>
  </div>
</template>
