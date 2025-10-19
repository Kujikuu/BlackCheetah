<script setup lang="ts">
import { ref, computed } from 'vue'
import { financialApi } from '@/services/api/financial'

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
          />
          <VTextField
            v-model="addDataForm.dateOfSale"
            label="Date of Sale"
            type="date"
            class="mb-4"
          />
          <VTextField
            v-model="addDataForm.unitPrice"
            label="Unit Price"
            type="number"
            prefix="SAR"
            class="mb-4"
          />
          <VTextField
            v-model="addDataForm.quantitySold"
            label="Quantity Sold"
            type="number"
            class="mb-4"
          />
        </div>

        <!-- Expense Fields -->
        <div v-if="addDataCategory === 'expense'">
          <VTextField
            v-model="addDataForm.expenseCategory"
            label="Expense Category"
            class="mb-4"
          />
          <VTextField
            v-model="addDataForm.dateOfExpense"
            label="Date of Expense"
            type="date"
            class="mb-4"
          />
          <VTextField
            v-model="addDataForm.amount"
            label="Amount"
            type="number"
            prefix="SAR"
            class="mb-4"
          />
          <VTextField
            v-model="addDataForm.description"
            label="Description"
            class="mb-4"
          />
        </div>
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
