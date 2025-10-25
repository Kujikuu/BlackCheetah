<script setup lang="ts">
import home from '@images/svg/home.svg'
import office from '@images/svg/office.svg'
import { useCountries } from '@/composables/useCountries'
import { useSaudiProvinces } from '@/composables/useSaudiProvinces'
import { useDisplay } from 'vuetify'
import { useFormValidation } from '@/composables/useFormValidation'
import { useValidationRules } from '@/composables/useValidationRules'

interface BillingAddress {
  firstName: string | undefined
  lastName: string | undefined
  nationality: string | null
  addressLine1: string
  addressLine2: string
  landmark: string
  contact: string
  city: string
  state: string
  zipCode: number | null
}
interface Props {
  billingAddress?: BillingAddress
  isDialogVisible: boolean
}
interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'submit', value: BillingAddress): void
}

const props = withDefaults(defineProps<Props>(), {
  billingAddress: () => ({
    firstName: '',
    lastName: '',
    nationality: null,
    addressLine1: '',
    addressLine2: '',
    landmark: '',
    contact: '',
    city: '',
    state: '',
    zipCode: null,
  }),
})

const emit = defineEmits<Emit>()

const formRef = ref()
const { backendErrors, setBackendErrors, clearError } = useFormValidation()
const rules = useValidationRules()

const billingAddress = ref<BillingAddress>(structuredClone(toRaw(props.billingAddress)))

const resetForm = () => {
  emit('update:isDialogVisible', false)
  billingAddress.value = structuredClone(toRaw(props.billingAddress))
}

const onFormSubmit = async () => {
  // Validate form
  const { valid } = await formRef.value.validate()
  if (!valid) return

  try {
    emit('update:isDialogVisible', false)
    emit('submit', billingAddress.value)
  }
  catch (error: any) {
    setBackendErrors(error)
  }
}

const selectedAddress = ref('Home')

const addressTypes = [
  {
    icon: { icon: home, size: '28' },
    title: 'Home',
    desc: 'Delivery Time (9am - 9pm)',
    value: 'Home',
  },
  {
    icon: { icon: office, size: '28' },
    title: 'Office',
    desc: 'Delivery Time (9am - 5pm)',
    value: 'Office',
  },
]

// Get countries from composable
const { countries: nationalityOptions, isLoading: isLoadingCountries } = useCountries()

// Get Saudi provinces and cities
const { provinces, getCitiesForProvince, isLoading: isLoadingProvinces } = useSaudiProvinces()

// Available cities based on selected province
const availableCities = computed(() => getCitiesForProvince(billingAddress.value.state || ''))

const { smAndUp } = useDisplay()
</script>

<template>
  <VDialog :width="smAndUp ? 'auto' : 900" :model-value="props.isDialogVisible"
    @update:model-value="val => $emit('update:isDialogVisible', val)">
    <!-- 👉 Dialog close btn -->
    <DialogCloseBtn @click="$emit('update:isDialogVisible', false)" />

    <VCard v-if="props.billingAddress" class="pa-sm-10 pa-2">
      <VCardText>
        <!-- 👉 Title -->
        <h4 class="text-h4 text-center mb-2">
          {{ (props.billingAddress.addressLine1 || props.billingAddress.addressLine2) ? 'Edit' : 'Add New' }} Address
        </h4>
        <p class="text-body-1 text-center mb-6">
          Add new address for express delivery
        </p>

        <div class="d-flex mb-6">
          <CustomRadiosWithIcon v-model:selected-radio="selectedAddress" :radio-content="addressTypes"
            :grid-column="{ sm: '6', cols: '12' }" />
        </div>

        <!-- 👉 Form -->
        <VForm ref="formRef" @submit.prevent="onFormSubmit">
          <VRow>
            <!-- 👉 First Name -->
            <VCol cols="12" md="6">
              <AppTextField 
                v-model="billingAddress.firstName" 
                label="First Name" 
                placeholder="John"
                :rules="[rules.required('First name is required'), rules.maxLength(50)]"
                :error-messages="backendErrors.firstName"
                @input="clearError('firstName')"
              />
            </VCol>

            <!-- 👉 Last Name -->
            <VCol cols="12" md="6">
              <AppTextField 
                v-model="billingAddress.lastName" 
                label="Last Name" 
                placeholder="Doe"
                :rules="[rules.required('Last name is required'), rules.maxLength(50)]"
                :error-messages="backendErrors.lastName"
                @input="clearError('lastName')"
              />
            </VCol>

            <!-- 👉 Select Nationality -->
            <VCol cols="12">
              <AppSelect 
                v-model="billingAddress.nationality" 
                label="Select Nationality"
                placeholder="Select Nationality" 
                :items="nationalityOptions" 
                :loading="isLoadingCountries" 
                clearable
                :rules="[rules.required('Nationality is required')]"
                :error-messages="backendErrors.nationality"
                @update:model-value="clearError('nationality')"
              />
            </VCol>

            <!-- 👉 Address Line 1 -->
            <VCol cols="12">
              <AppTextField 
                v-model="billingAddress.addressLine1" 
                label="Address Line 1"
                placeholder="12, Business Park"
                :rules="[rules.required('Address line 1 is required'), rules.maxLength(200)]"
                :error-messages="backendErrors.addressLine1"
                @input="clearError('addressLine1')"
              />
            </VCol>

            <!-- 👉 Address Line 2 -->
            <VCol cols="12">
              <AppTextField 
                v-model="billingAddress.addressLine2" 
                label="Address Line 2" 
                placeholder="Mall Road"
                :rules="[rules.maxLength(200)]"
                :error-messages="backendErrors.addressLine2"
                @input="clearError('addressLine2')"
              />
            </VCol>

            <!-- 👉 Landmark -->
            <VCol cols="12" md="6">
              <AppTextField 
                v-model="billingAddress.landmark" 
                label="Landmark" 
                placeholder="Nr. Hard Rock Cafe"
                :rules="[rules.maxLength(100)]"
                :error-messages="backendErrors.landmark"
                @input="clearError('landmark')"
              />
            </VCol>

            <!-- 👉 City -->
            <VCol cols="12" md="6">
              <AppSelect 
                v-model="billingAddress.city" 
                label="City" 
                placeholder="Select City" 
                :items="availableCities"
                :disabled="!billingAddress.state" 
                clearable 
                required
                :rules="[rules.required('City is required')]"
                :error-messages="backendErrors.city"
                @update:model-value="clearError('city')"
              />
            </VCol>

            <!-- 👉 State -->
            <VCol cols="12" md="6">
              <AppSelect 
                v-model="billingAddress.state" 
                label="Province" 
                placeholder="Select Province"
                :items="provinces" 
                :loading="isLoadingProvinces" 
                clearable 
                required
                :rules="[rules.required('Province is required')]"
                :error-messages="backendErrors.state"
                @update:model-value="clearError('state')"
              />
            </VCol>

            <!-- 👉 Zip Code -->
            <VCol cols="12" md="6">
              <AppTextField 
                v-model="billingAddress.zipCode" 
                label="Zip Code" 
                placeholder="99950" 
                type="number"
                :rules="[rules.numeric('Zip code must be numeric')]"
                :error-messages="backendErrors.zipCode"
                @input="clearError('zipCode')"
              />
            </VCol>

            <VCol cols="12">
              <VSwitch label="Use as a billing address?" />
            </VCol>

            <!-- 👉 Submit and Cancel button -->
            <VCol cols="12" class="text-center">
              <VBtn type="submit" class="me-3">
                submit
              </VBtn>

              <VBtn variant="tonal" color="secondary" @click="resetForm">
                Cancel
              </VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </VDialog>
</template>
