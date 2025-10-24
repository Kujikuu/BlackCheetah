<script setup lang="ts">
interface DocumentUploadData {
  fdd: File | null
  franchiseAgreement: File | null
  financialStudy: File | null
  franchiseKit: File | null
  additionalDocuments: File[] | null
}

interface Props {
  formData: DocumentUploadData
}

interface Emit {
  (e: 'update:formData', value: DocumentUploadData): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const localFormData = computed({
  get: () => props.formData,
  set: val => emit('update:formData', val),
})
</script>

<template>
  <div>
    <div class="text-h4 mb-1">
      Upload Documents
    </div>
    <p class="text-body-1 mb-6">
      Please upload all required franchise documents
    </p>

    <VForm>
      <VRow>
        <VCol cols="12">
          <VFileInput
            v-model="localFormData.fdd"
            label="Franchise Disclosure Document (FDD)"
            accept=".pdf,.doc,.docx"
            prepend-icon="tabler-file-text"
            required
            hint="Required document - PDF, DOC, or DOCX format"
            persistent-hint
          />
        </VCol>
        <VCol cols="12">
          <VFileInput
            v-model="localFormData.franchiseAgreement"
            label="Franchise Agreement"
            accept=".pdf,.doc,.docx"
            prepend-icon="tabler-file-text"
            required
            hint="Required document - PDF, DOC, or DOCX format"
            persistent-hint
          />
        </VCol>
        <VCol cols="12">
          <VFileInput
            v-model="localFormData.financialStudy"
            label="Financial Study"
            accept=".pdf,.doc,.docx"
            prepend-icon="tabler-report-money"
            required
            hint="Required document - PDF, DOC, or DOCX format"
            persistent-hint
          />
        </VCol>
        <VCol cols="12">
          <VFileInput
            v-model="localFormData.franchiseKit"
            label="Franchise Kit"
            accept=".pdf,.doc,.docx"
            prepend-icon="tabler-briefcase"
            required
            hint="Required document - PDF, DOC, or DOCX format"
            persistent-hint
          />
        </VCol>
        <VCol cols="12">
          <VFileInput
            v-model="localFormData.additionalDocuments"
            label="Additional Documents"
            accept=".pdf,.doc,.docx"
            prepend-icon="tabler-files"
            multiple
            hint="Optional - Multiple files allowed"
            persistent-hint
          />
        </VCol>
      </VRow>

      <VAlert
        type="info"
        variant="tonal"
        class="mt-4"
      >
        <VAlertTitle>Document Requirements</VAlertTitle>
        <ul class="mt-2">
          <li>All documents must be in PDF, DOC, or DOCX format</li>
          <li>Maximum file size: 10MB per document</li>
          <li>All 4 static documents (FDD, Franchise Agreement, Financial Study, Franchise Kit) are required</li>
          <li>Additional documents are optional and help expedite the review process</li>
        </ul>
      </VAlert>
    </VForm>
  </div>
</template>
