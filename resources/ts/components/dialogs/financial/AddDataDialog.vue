<script setup lang="ts">
import { ref, computed } from 'vue'
import { financialApi } from '@/services/api'
import { useValidation } from '@/composables/useValidation'

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

const isLoading = ref(false)
const addDataCategory = ref<'sales' | 'expense'>('sales')
const formRef = ref()

// Use validation composable
const { 
  requiredTextRules, 
  requiredTextWithLengthRules,
  positiveNumberRules,
  pastDateRules,
  validateForm 
} = useValidation()

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
  // Validate form before submission
  const isValid = await validateForm(formRef)
  if (!isValid) return

  isLoading.value = true
  try {
    if (addDataCategory.value === 'sales') {
      await financialApi.createSale({
        product: addDataForm.value.product,
        unit_price: addDataForm.value.unitPrice,
        quantity: addDataForm.value.quantitySold,
        date: addDataForm.value.dateOfSale,
        customer_name: 'Walk-in Customer', // Default value for now
        customer_email: 'customer@example.com', // Default value for now
        franchise_id: 1, // TODO: Get from user context
        unit_id: 1, // TODO: Get from user context or form
      })
    }
    else {
      await financialApi.createExpense({
        expense_category: addDataForm.value.expenseCategory,
        amount: addDataForm.value.amount,
        description: addDataForm.value.description,
        date: addDataForm.value.dateOfExpense,
      })
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
  catch (error) {
    console.error('Error adding data:', error)
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
            :rules="requiredTextRules"
            required
            class="mb-4"
          />

          <!-- Sales Fields -->
          <div v-if="addDataCategory === 'sales'">
            <VTextField
              v-model="addDataForm.product"
              label="Product"
              :rules="requiredTextWithLengthRules(2, 100)"
              required
              class="mb-4"
            />
            <VTextField
              v-model="addDataForm.dateOfSale"
              label="Date of Sale"
              type="date"
              :rules="pastDateRules"
              required
              class="mb-4"
            />
            <VTextField
              v-model="addDataForm.unitPrice"
              label="Unit Price"
              type="number"
              prefix="SAR"
              :rules="positiveNumberRules"
              required
              class="mb-4"
            />
            <VTextField
              v-model="addDataForm.quantitySold"
              label="Quantity Sold"
              type="number"
              :rules="positiveNumberRules"
              required
              class="mb-4"
            />
          </div>

          <!-- Expense Fields -->
          <div v-if="addDataCategory === 'expense'">
            <VTextField
              v-model="addDataForm.expenseCategory"
              label="Expense Category"
              :rules="requiredTextWithLengthRules(2, 100)"
              required
              class="mb-4"
            />
            <VTextField
              v-model="addDataForm.dateOfExpense"
              label="Date of Expense"
              type="date"
              :rules="pastDateRules"
              required
              class="mb-4"
            />
            <VTextField
              v-model="addDataForm.amount"
              label="Amount"
              type="number"
              prefix="SAR"
              :rules="positiveNumberRules"
              required
              class="mb-4"
            />
            <VTextField
              v-model="addDataForm.description"
              label="Description"
              :rules="requiredTextWithLengthRules(5, 500)"
              required
              class="mb-4"
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
