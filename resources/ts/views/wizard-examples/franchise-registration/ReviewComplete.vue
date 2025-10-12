<script setup lang="ts">
interface ReviewCompleteData {
  termsAccepted: boolean
}

interface Props {
  formData: ReviewCompleteData
  allFormData: any
}

interface Emit {
  (e: 'update:formData', value: ReviewCompleteData): void
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
      Review & Complete
    </div>
    <p class="text-body-1 mb-6">
      Please review all information before submitting your franchise registration
    </p>

    <!-- Review Summary -->
    <VExpansionPanels class="mb-6">
      <VExpansionPanel title="Personal Information">
        <VExpansionPanelText>
          <VRow>
            <VCol cols="6">
              <strong>Contact:</strong> {{ allFormData.personalInfo.contactNumber || 'Not provided' }}
            </VCol>
            <VCol cols="6">
              <strong>Country:</strong> {{ allFormData.personalInfo.country || 'Not provided' }}
            </VCol>
            <VCol cols="6">
              <strong>State:</strong> {{ allFormData.personalInfo.state || 'Not provided' }}
            </VCol>
            <VCol cols="6">
              <strong>City:</strong> {{ allFormData.personalInfo.city || 'Not provided' }}
            </VCol>
            <VCol cols="12">
              <strong>Address:</strong> {{ allFormData.personalInfo.address || 'Not provided' }}
            </VCol>
          </VRow>
        </VExpansionPanelText>
      </VExpansionPanel>

      <VExpansionPanel title="Franchise Details">
        <VExpansionPanelText>
          <VRow>
            <VCol cols="6">
              <strong>Franchise Name:</strong> {{ allFormData.franchiseDetails?.franchiseDetails?.franchiseName || 'Not provided' }}
            </VCol>
            <VCol cols="6">
              <strong>Website:</strong> {{ allFormData.franchiseDetails?.franchiseDetails?.website || 'Not provided' }}
            </VCol>
            <VCol cols="6">
              <strong>Legal Entity:</strong> {{ allFormData.franchiseDetails?.legalDetails?.legalEntityName || 'Not provided' }}
            </VCol>
            <VCol cols="6">
              <strong>Business Structure:</strong> {{ allFormData.franchiseDetails?.legalDetails?.businessStructure || 'Not provided' }}
            </VCol>
            <VCol cols="6">
              <strong>Industry:</strong> {{ allFormData.franchiseDetails?.legalDetails?.industry || 'Not provided' }}
            </VCol>
            <VCol cols="6">
              <strong>Funding Amount:</strong> {{ allFormData.franchiseDetails?.legalDetails?.fundingAmount || 'Not provided' }}
            </VCol>
          </VRow>
        </VExpansionPanelText>
      </VExpansionPanel>

      <VExpansionPanel title="Documents">
        <VExpansionPanelText>
          <VList>
            <VListItem>
              <VListItemTitle>
                <VIcon
                  :icon="allFormData.documents.fdd ? 'tabler-check' : 'tabler-x'"
                  :color="allFormData.documents.fdd ? 'success' : 'error'"
                  class="me-2"
                />
                Franchise Disclosure Document (FDD)
                <VChip
                  v-if="!allFormData.documents.fdd"
                  color="error"
                  size="small"
                  class="ms-2"
                >
                  Required
                </VChip>
              </VListItemTitle>
            </VListItem>
            <VListItem>
              <VListItemTitle>
                <VIcon
                  :icon="allFormData.documents.franchiseAgreement ? 'tabler-check' : 'tabler-x'"
                  :color="allFormData.documents.franchiseAgreement ? 'success' : 'error'"
                  class="me-2"
                />
                Franchise Agreement
                <VChip
                  v-if="!allFormData.documents.franchiseAgreement"
                  color="error"
                  size="small"
                  class="ms-2"
                >
                  Required
                </VChip>
              </VListItemTitle>
            </VListItem>
            <VListItem>
              <VListItemTitle>
                <VIcon
                  :icon="allFormData.documents.operationsManual ? 'tabler-check' : 'tabler-minus'"
                  :color="allFormData.documents.operationsManual ? 'success' : 'secondary'"
                  class="me-2"
                />
                Operations Manual
                <VChip
                  color="secondary"
                  size="small"
                  class="ms-2"
                >
                  Optional
                </VChip>
              </VListItemTitle>
            </VListItem>
            <VListItem>
              <VListItemTitle>
                <VIcon
                  :icon="allFormData.documents.brandGuidelines ? 'tabler-check' : 'tabler-minus'"
                  :color="allFormData.documents.brandGuidelines ? 'success' : 'secondary'"
                  class="me-2"
                />
                Brand Guidelines
                <VChip
                  color="secondary"
                  size="small"
                  class="ms-2"
                >
                  Optional
                </VChip>
              </VListItemTitle>
            </VListItem>
            <VListItem>
              <VListItemTitle>
                <VIcon
                  :icon="allFormData.documents.legalDocuments ? 'tabler-check' : 'tabler-minus'"
                  :color="allFormData.documents.legalDocuments ? 'success' : 'secondary'"
                  class="me-2"
                />
                Additional Legal Documents
                <VChip
                  color="secondary"
                  size="small"
                  class="ms-2"
                >
                  Optional
                </VChip>
              </VListItemTitle>
            </VListItem>
          </VList>
        </VExpansionPanelText>
      </VExpansionPanel>
    </VExpansionPanels>

    <!-- Terms & Conditions -->
    <VCard
      variant="outlined"
      class="mb-4"
    >
      <VCardText
        class="pa-4"
        style="max-height: 300px; overflow-y: auto;"
      >
        <h4 class="text-h6 mb-3">
          Franchise Registration Terms & Conditions
        </h4>
        <p class="mb-3">
          By completing this registration, you agree to the following terms and conditions:
        </p>
        <ul class="mb-3">
          <li>All information provided is accurate and complete</li>
          <li>You have the legal authority to enter into franchise agreements</li>
          <li>You understand the financial commitments involved</li>
          <li>You agree to comply with all franchise requirements and standards</li>
          <li>You acknowledge receipt of the Franchise Disclosure Document</li>
          <li>You understand this registration is subject to review and approval</li>
          <li>You agree to maintain confidentiality of proprietary information</li>
        </ul>
        <p class="mb-0">
          <strong>Important:</strong> This registration does not constitute a binding franchise agreement.
          Final agreements will be executed separately following review and approval by our franchise team.
        </p>
      </VCardText>
    </VCard>

    <VCheckbox
      v-model="localFormData.termsAccepted"
      label="I have read and agree to the terms and conditions"
      required
      color="primary"
    />

    <VAlert
      v-if="!localFormData.termsAccepted"
      type="warning"
      variant="tonal"
      class="mt-4"
    >
      <VAlertTitle>Agreement Required</VAlertTitle>
      You must accept the terms and conditions to complete your franchise registration.
    </VAlert>
  </div>
</template>
