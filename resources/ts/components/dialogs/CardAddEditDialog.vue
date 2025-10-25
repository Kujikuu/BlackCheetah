<script setup lang="ts">
import { useFormValidation } from '@/composables/useFormValidation'
import { useValidationRules } from '@/composables/useValidationRules'

interface Details {
  number: string | number
  name: string
  expiry: string
  cvv: string
  isPrimary: boolean
  type: string
}
interface Emit {
  (e: 'submit', value: Details): void
  (e: 'update:isDialogVisible', value: boolean): void
}

interface Props {
  cardDetails?: Details
  isDialogVisible: boolean
}

const props = withDefaults(defineProps<Props>(), {
  cardDetails: () => ({
    number: '',
    name: '',
    expiry: '',
    cvv: '',
    isPrimary: false,
    type: '',
  }),
})

const emit = defineEmits<Emit>()

const formRef = ref()
const { backendErrors, setBackendErrors, clearError } = useFormValidation()
const rules = useValidationRules()

const cardDetails = ref<Details>(structuredClone(toRaw(props.cardDetails)))

watch(() => props, () => {
  cardDetails.value = structuredClone(toRaw(props.cardDetails))
})

const formSubmit = async () => {
  // Validate form
  const { valid } = await formRef.value.validate()
  if (!valid) return

  try {
    emit('submit', cardDetails.value)
  }
  catch (error: any) {
    setBackendErrors(error)
  }
}

// Custom validation rules for credit card
const cardNumberRule = (value: string | number) => {
  const str = value.toString().replace(/\s/g, '')
  if (!str) return 'Card number is required'
  if (!/^\d{13,19}$/.test(str)) return 'Card number must be 13-19 digits'
  return true
}

const expiryRule = (value: string) => {
  if (!value) return 'Expiry date is required'
  if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(value)) return 'Expiry must be in MM/YY format'
  return true
}

const cvvRule = (value: string) => {
  if (!value) return 'CVV is required'
  if (!/^\d{3,4}$/.test(value)) return 'CVV must be 3 or 4 digits'
  return true
}

const dialogModelValueUpdate = (val: boolean) => {
  emit('update:isDialogVisible', val)
}
</script>

<template>
  <VDialog
    :width="$vuetify.display.smAndDown ? 'auto' : 600"
    :model-value="props.isDialogVisible"
    @update:model-value="dialogModelValueUpdate"
  >
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="dialogModelValueUpdate(false)" />

    <VCard class="pa-2 pa-sm-10">
      <!-- 👉 Title -->
      <VCardItem class="text-center">
        <VCardTitle>
          <h4 class="text-h4 mb-2">
            {{ props.cardDetails.name ? 'Edit Card' : 'Add New Card' }}
          </h4>
        </VCardTitle>
        <p class="text-body-1 mb-0">
          {{ props.cardDetails.name ? 'Edit your saved card details' : 'Add card for future billing' }}
        </p>
      </VCardItem>

      <VCardText class="pt-6">
        <VForm ref="formRef" @submit.prevent="formSubmit">
          <VRow>
            <!-- 👉 Card Number -->
            <VCol cols="12">
              <AppTextField
                v-model="cardDetails.number"
                label="Card Number"
                placeholder="1356 3215 6548 7898"
                type="text"
                :rules="[cardNumberRule]"
                :error-messages="backendErrors.number"
                @input="clearError('number')"
              />
            </VCol>

            <!-- 👉 Card Name -->
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="cardDetails.name"
                label="Name"
                placeholder="Name on Card"
                :rules="[rules.required('Name is required'), rules.maxLength(100, 'Name must not exceed 100 characters')]"
                :error-messages="backendErrors.name"
                @input="clearError('name')"
              />
            </VCol>

            <!-- 👉 Card Expiry -->
            <VCol
              cols="12"
              md="3"
            >
              <AppTextField
                v-model="cardDetails.expiry"
                label="Expiry Date"
                placeholder="MM/YY"
                :rules="[expiryRule]"
                :error-messages="backendErrors.expiry"
                @input="clearError('expiry')"
              />
            </VCol>

            <!-- 👉 Card CVV -->
            <VCol
              cols="12"
              md="3"
            >
              <AppTextField
                v-model="cardDetails.cvv"
                type="text"
                label="CVV Code"
                placeholder="654"
                :rules="[cvvRule]"
                :error-messages="backendErrors.cvv"
                @input="clearError('cvv')"
              />
            </VCol>

            <!-- 👉 Card Primary Set -->
            <VCol cols="12">
              <VSwitch
                v-model="cardDetails.isPrimary"
                label="Save Card for future billing?"
              />
            </VCol>

            <!-- 👉 Card actions -->
            <VCol
              cols="12"
              class="text-center"
            >
              <VBtn
                class="me-4"
                type="submit"
                @click="formSubmit"
              >
                Submit
              </VBtn>
              <VBtn
                color="secondary"
                variant="tonal"
                @click="$emit('update:isDialogVisible', false)"
              >
                Cancel
              </VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </VDialog>
</template>
