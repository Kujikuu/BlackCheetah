<script setup lang="ts">
import { ref, computed } from 'vue'
import { financialApi } from '@/services/api'
import { useFormValidation } from '@/composables/useFormValidation'
import { useStoreFinancialDataValidation } from '@/validation/financialValidation'

interface AddDataForm {
  product: string
  dateOfSale: string
  unitPrice: number
  quantitySold: number
  expenseCategory: string
  dateOfExpense: string
  amount: number
  description: string
}

interface Props {
  isDialogVisible: boolean
}

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'dataAdded'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const formRef = ref()
const isLoading = ref(false)
const addDataCategory = ref<'sales' | 'expense'>('sales')

const { backendErrors, setBackendErrors, clearError } = useFormValidation()

// Separate validation rules for sales and expense to avoid type issues
const salesValidationRules = useStoreFinancialDataValidation('sales')
const expenseValidationRules = useStoreFinancialDataValidation('expense')

const addDataForm = ref<AddDataForm>({
  product: '',
  dateOfSale: '',
  unitPrice: 0,
  quantitySold: 0,
  expenseCategory: '',
  dateOfExpense: '',
  amount: 0,
  description: '',
})

const dialogValue = computed({
  get: () => props.isDialogVisible,
  set: val => emit('update:isDialogVisible', val),
})

const submitAddData = async () => {
  // Validate form
  const { valid } = await formRef.value.validate()
  if (!valid) return

  isLoading.value = true
  try {
    if (addDataCategory.value === 'sales') {
      await financialApi.createSale({
        product: addDataForm.value.product,
        unitPrice: addDataForm.value.unitPrice,
        quantity: addDataForm.value.quantitySold,
        date: addDataForm.value.dateOfSale,
      } as any)
    }
    else {
      await financialApi.createExpense({
        expenseCategory: addDataForm.value.expenseCategory,
        amount: addDataForm.value.amount,
        description: addDataForm.value.description,
        date: addDataForm.value.dateOfExpense,
      } as any)
    }

    // Reset form
    addDataForm.value = {
      product: '',
      dateOfSale: '',
      unitPrice: 0,
      quantitySold: 0,
      expenseCategory: '',
      dateOfExpense: '',
      amount: 0,
      description: '',
    }

    // Reset category to default
    addDataCategory.value = 'sales'

    emit('dataAdded')
    dialogValue.value = false
  }
  catch (error: any) {
    console.error('Error adding data:', error)
    setBackendErrors(error)
  }
  finally {
    isLoading.value = false
  }
}

const handleClose = () => {
  dialogValue.value = false
}
</script>

<template>
  <VDialog
    v-model="dialogValue"
    max-width="600"
  >
    <DialogCloseBtn @click="handleClose" />
    <VCard title="Add New Data">
      <VCardText>
        <VForm ref="formRef">
          <VSelect
            v-model="addDataCategory"
            :items="[
              { title: 'Sales', value: 'sales' },
              { title: 'Expense', value: 'expense' },
            ]"
            label="Category"
            class="mb-4"
          />

          <!-- Sales Fields -->
          <div v-if="addDataCategory === 'sales'">
            <VTextField
              v-model="addDataForm.product"
              label="Product"
              class="mb-4"
              :rules="(salesValidationRules as any).product"
              :error-messages="backendErrors.product"
              @input="clearError('product')"
            />
            <VTextField
              v-model="addDataForm.dateOfSale"
              label="Date of Sale"
              type="date"
              class="mb-4"
              :rules="(salesValidationRules as any).date"
              :error-messages="backendErrors.date"
              @input="clearError('date')"
            />
            <VTextField
              v-model="addDataForm.unitPrice"
              label="Unit Price"
              type="number"
              prefix="SAR"
              class="mb-4"
              :rules="(salesValidationRules as any).unitPrice"
              :error-messages="backendErrors.unitPrice"
              @input="clearError('unitPrice')"
            />
            <VTextField
              v-model="addDataForm.quantitySold"
              label="Quantity Sold"
              type="number"
              class="mb-4"
              :rules="(salesValidationRules as any).quantitySold"
              :error-messages="backendErrors.quantitySold"
              @input="clearError('quantitySold')"
            />
          </div>

          <!-- Expense Fields -->
          <div v-if="addDataCategory === 'expense'">
            <VTextField
              v-model="addDataForm.expenseCategory"
              label="Expense Category"
              class="mb-4"
              :rules="(expenseValidationRules as any).expenseCategory"
              :error-messages="backendErrors.expenseCategory"
              @input="clearError('expenseCategory')"
            />
            <VTextField
              v-model="addDataForm.dateOfExpense"
              label="Date of Expense"
              type="date"
              class="mb-4"
              :rules="(expenseValidationRules as any).date"
              :error-messages="backendErrors.date"
              @input="clearError('date')"
            />
            <VTextField
              v-model="addDataForm.amount"
              label="Amount"
              type="number"
              prefix="SAR"
              class="mb-4"
              :rules="(expenseValidationRules as any).amount"
              :error-messages="backendErrors.amount"
              @input="clearError('amount')"
            />
            <VTextField
              v-model="addDataForm.description"
              label="Description"
              class="mb-4"
              :rules="(expenseValidationRules as any).description"
              :error-messages="backendErrors.description"
              @input="clearError('description')"
            />
          </div>
        </VForm>
      </VCardText>
      <VCardActions>
        <VSpacer />
        <VBtn
          variant="outlined"
          @click="handleClose"
        >
          Cancel
        </VBtn>
        <VBtn
          color="primary"
          :loading="isLoading"
          @click="submitAddData"
        >
          Add Data
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
