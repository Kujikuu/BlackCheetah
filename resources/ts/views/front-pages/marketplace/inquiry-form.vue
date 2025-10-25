<script setup lang="ts">
import { marketplaceApi, type InquiryPayload } from '@/services/api'
import { SaudiRiyal } from 'lucide-vue-next'

interface Props {
  inquiryType: 'franchise' | 'property'
  itemId: number
  title?: string
}

interface Emit {
  (e: 'inquirySubmitted'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const isSubmitting = ref(false)

const inquiryForm = ref<InquiryPayload>({
  name: '',
  email: '',
  phone: '',
  inquiry_type: props.inquiryType,
  franchise_id: props.inquiryType === 'franchise' ? props.itemId : undefined,
  property_id: props.inquiryType === 'property' ? props.itemId : undefined,
  message: '',
  investment_budget: '',
  preferred_location: '',
  timeline: '',
})

const submitInquiry = async () => {
  try {
    isSubmitting.value = true

    if (props.inquiryType === 'franchise') {
      inquiryForm.value.franchise_id = props.itemId
      inquiryForm.value.property_id = undefined
    }
    else {
      inquiryForm.value.property_id = props.itemId
      inquiryForm.value.franchise_id = undefined
    }

    const response = await marketplaceApi.submitInquiry(inquiryForm.value)

    if (response.success) {
      // Reset form
      inquiryForm.value = {
        name: '',
        email: '',
        phone: '',
        inquiry_type: props.inquiryType,
        franchise_id: props.inquiryType === 'franchise' ? props.itemId : undefined,
        property_id: props.inquiryType === 'property' ? props.itemId : undefined,
        message: '',
        investment_budget: '',
        preferred_location: '',
        timeline: '',
      }
      emit('inquirySubmitted')
    }
  }
  catch (error) {
    console.error('Error submitting inquiry:', error)
  }
  finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <VCard class="sticky-card" :title="title || 'Interested? Get in Touch'">
    <VCardText>
      <p class="text-sm mb-4">
        Fill out the form below and we'll get back to you with more information.
      </p>

      <VForm @submit.prevent="submitInquiry">
        <VTextField
          v-model="inquiryForm.name"
          label="Full Name"
          placeholder="Enter your name"
          prepend-inner-icon="tabler-user"
          class="mb-3"
          required
        />

        <VTextField
          v-model="inquiryForm.email"
          label="Email"
          placeholder="Enter your email"
          prepend-inner-icon="tabler-mail"
          type="email"
          class="mb-3"
          required
        />

        <VTextField
          v-model="inquiryForm.phone"
          label="Phone"
          placeholder="Enter your phone"
          prepend-inner-icon="tabler-phone"
          class="mb-3"
          required
        />

        <VTextField
          v-if="inquiryType === 'franchise'"
          v-model="inquiryForm.investment_budget"
          label="Investment Budget (Optional)"
          placeholder="e.g., SAR 100,000 - SAR 500,000"
          :prepend-inner-icon="SaudiRiyal"
          class="mb-3"
        />

        <VTextField
          v-if="inquiryType === 'franchise'"
          v-model="inquiryForm.preferred_location"
          label="Preferred Location (Optional)"
          placeholder="e.g., Riyadh, Saudi Arabia"
          prepend-inner-icon="tabler-map-pin"
          class="mb-3"
        />

        <VTextField
          v-model="inquiryForm.timeline"
          label="Timeline (Optional)"
          :placeholder="inquiryType === 'franchise' ? 'e.g., 3-6 months' : 'e.g., ASAP, 3 months'"
          prepend-inner-icon="tabler-calendar"
          class="mb-3"
        />

        <VTextarea
          v-model="inquiryForm.message"
          label="Message"
          :placeholder="inquiryType === 'franchise' ? 'Tell us about your interest...' : 'Tell us about your requirements...'"
          rows="4"
          class="mb-3"
          required
        />

        <VBtn
          type="submit"
          color="primary"
          block
          size="large"
          :loading="isSubmitting"
        >
          <VIcon icon="tabler-send" class="me-2" />
          Send Inquiry
        </VBtn>
      </VForm>
    </VCardText>
  </VCard>
</template>

<style lang="scss" scoped>
.sticky-card {
  position: sticky;
  top: 100px;
}
</style>

