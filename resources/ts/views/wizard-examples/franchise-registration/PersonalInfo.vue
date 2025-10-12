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
  { title: 'United States', value: 'United States' },
  { title: 'Canada', value: 'Canada' },
  { title: 'United Kingdom', value: 'United Kingdom' },
  { title: 'Australia', value: 'Australia' },
  { title: 'Germany', value: 'Germany' },
  { title: 'France', value: 'France' },
  { title: 'Japan', value: 'Japan' },
  { title: 'Brazil', value: 'Brazil' },
  { title: 'India', value: 'India' },
  { title: 'Mexico', value: 'Mexico' },
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
        <VCol
          cols="12"
          md="6"
        >
          <AppTextField
            v-model="localFormData.contactNumber"
            label="Contact Number"
            placeholder="Enter your contact number"
            required
          />
        </VCol>
        <VCol
          cols="12"
          md="6"
        >
          <AppSelect
            v-model="localFormData.country"
            label="Country"
            :items="countries"
            placeholder="Select country"
            required
          />
        </VCol>
        <VCol
          cols="12"
          md="6"
        >
          <AppTextField
            v-model="localFormData.state"
            label="State/Province"
            placeholder="Enter state or province"
          />
        </VCol>
        <VCol
          cols="12"
          md="6"
        >
          <AppTextField
            v-model="localFormData.city"
            label="City"
            placeholder="Enter city"
            required
          />
        </VCol>
        <VCol cols="12">
          <AppTextarea
            v-model="localFormData.address"
            label="Address"
            placeholder="Enter your full address"
            rows="3"
          />
        </VCol>
      </VRow>
    </VForm>
  </div>
</template>
