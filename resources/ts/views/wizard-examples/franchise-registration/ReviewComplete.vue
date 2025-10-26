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
              <strong>Contact Number:</strong> {{ allFormData.personalInfo.contactNumber || 'Not provided' }}
            </VCol>
            <VCol cols="6">
              <strong>Nationality:</strong> {{ allFormData.personalInfo.nationality || 'Not provided' }}
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
          <div class="mb-4">
            <h6 class="text-subtitle-1 mb-2 text-primary">
              Basic Information
            </h6>
            <VRow>
              <VCol cols="12" md="6">
                <strong>Franchise Name:</strong> {{ allFormData.franchiseDetails?.franchiseDetails?.franchiseName || 'Not provided' }}
              </VCol>
              <VCol cols="12" md="6">
                <strong>Website:</strong> {{ allFormData.franchiseDetails?.franchiseDetails?.website || 'Not provided' }}
              </VCol>
              <VCol cols="12" md="6">
                <strong>Logo:</strong> 
                <VChip
                  :color="allFormData.franchiseDetails?.franchiseDetails?.logo ? 'success' : 'secondary'"
                  size="small"
                  class="ms-2"
                >
                  {{ allFormData.franchiseDetails?.franchiseDetails?.logo ? 'Uploaded' : 'Not uploaded' }}
                </VChip>
              </VCol>
            </VRow>
          </div>

          <VDivider class="my-4" />

          <div class="mb-4">
            <h6 class="text-subtitle-1 mb-2 text-primary">
              Legal Details
            </h6>
            <VRow>
              <VCol cols="12" md="6">
                <strong>Legal Entity Name:</strong> {{ allFormData.franchiseDetails?.legalDetails?.legalEntityName || 'Not provided' }}
              </VCol>
              <VCol cols="12" md="6">
                <strong>Business Structure:</strong> {{ allFormData.franchiseDetails?.legalDetails?.businessStructure || 'Not provided' }}
              </VCol>
              <VCol cols="12" md="6">
                <strong>Tax ID:</strong> {{ allFormData.franchiseDetails?.legalDetails?.taxId || 'Not provided' }}
              </VCol>
              <VCol cols="12" md="6">
                <strong>Industry:</strong> {{ allFormData.franchiseDetails?.legalDetails?.industry || 'Not provided' }}
              </VCol>
              <VCol cols="12" md="6">
                <strong>Funding Amount:</strong> {{ allFormData.franchiseDetails?.legalDetails?.fundingAmount || 'Not provided' }}
              </VCol>
              <VCol cols="12" md="6">
                <strong>Funding Source:</strong> {{ allFormData.franchiseDetails?.legalDetails?.fundingSource || 'Not provided' }}
              </VCol>
            </VRow>
          </div>

          <VDivider class="my-4" />

          <div>
            <h6 class="text-subtitle-1 mb-2 text-primary">
              Contact Details
            </h6>
            <VRow>
              <VCol cols="12" md="6">
                <strong>Contact Number:</strong> {{ allFormData.franchiseDetails?.contactDetails?.contactNumber || 'Not provided' }}
              </VCol>
              <VCol cols="12" md="6">
                <strong>Email:</strong> {{ allFormData.franchiseDetails?.contactDetails?.email || 'Not provided' }}
              </VCol>
              <VCol cols="12" md="6">
                <strong>Nationality:</strong> {{ allFormData.franchiseDetails?.contactDetails?.nationality || 'Not provided' }}
              </VCol>
              <VCol cols="12" md="6">
                <strong>State:</strong> {{ allFormData.franchiseDetails?.contactDetails?.state || 'Not provided' }}
              </VCol>
              <VCol cols="12" md="6">
                <strong>City:</strong> {{ allFormData.franchiseDetails?.contactDetails?.city || 'Not provided' }}
              </VCol>
              <VCol cols="12">
                <strong>Address:</strong> {{ allFormData.franchiseDetails?.contactDetails?.address || 'Not provided' }}
              </VCol>
            </VRow>
          </div>

          <VDivider class="my-4" />

          <div>
            <h6 class="text-subtitle-1 mb-2 text-primary">
              Financial Details
            </h6>
            <VRow>
              <VCol cols="12" md="6">
                <strong>Franchise Fee:</strong> {{ allFormData.franchiseDetails?.financialDetails?.franchiseFee ? `$${allFormData.franchiseDetails.financialDetails.franchiseFee}` : 'Not provided' }}
              </VCol>
              <VCol cols="12" md="6">
                <strong>Royalty Percentage:</strong> {{ allFormData.franchiseDetails?.financialDetails?.royaltyPercentage ? `${allFormData.franchiseDetails.financialDetails.royaltyPercentage}%` : 'Not provided' }}
              </VCol>
              <VCol cols="12" md="6">
                <strong>Marketing Fee Percentage:</strong> {{ allFormData.franchiseDetails?.financialDetails?.marketingFeePercentage ? `${allFormData.franchiseDetails.financialDetails.marketingFeePercentage}%` : 'Not provided' }}
              </VCol>
            </VRow>
          </div>
        </VExpansionPanelText>
      </VExpansionPanel>

      <VExpansionPanel title="Documents">
        <VExpansionPanelText>
          <VList>
            <VListItem>
              <VListItemTitle>
                <VIcon
                  :icon="allFormData.documents?.fdd ? 'tabler-check' : 'tabler-minus'"
                  :color="allFormData.documents?.fdd ? 'success' : 'secondary'"
                  class="me-2"
                />
                Franchise Disclosure Document (FDD)
                <VChip
                  v-if="allFormData.documents?.fdd"
                  color="success"
                  size="small"
                  class="ms-2"
                >
                  {{ allFormData.documents.fdd?.name || 'Uploaded' }}
                </VChip>
                <VChip
                  v-else
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
                  :icon="allFormData.documents?.franchiseAgreement ? 'tabler-check' : 'tabler-minus'"
                  :color="allFormData.documents?.franchiseAgreement ? 'success' : 'secondary'"
                  class="me-2"
                />
                Franchise Agreement
                <VChip
                  v-if="allFormData.documents?.franchiseAgreement"
                  color="success"
                  size="small"
                  class="ms-2"
                >
                  {{ allFormData.documents.franchiseAgreement?.name || 'Uploaded' }}
                </VChip>
                <VChip
                  v-else
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
                  :icon="allFormData.documents?.financialStudy ? 'tabler-check' : 'tabler-minus'"
                  :color="allFormData.documents?.financialStudy ? 'success' : 'secondary'"
                  class="me-2"
                />
                Financial Study
                <VChip
                  v-if="allFormData.documents?.financialStudy"
                  color="success"
                  size="small"
                  class="ms-2"
                >
                  {{ allFormData.documents.financialStudy?.name || 'Uploaded' }}
                </VChip>
                <VChip
                  v-else
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
                  :icon="allFormData.documents?.franchiseKit ? 'tabler-check' : 'tabler-minus'"
                  :color="allFormData.documents?.franchiseKit ? 'success' : 'secondary'"
                  class="me-2"
                />
                Franchise Kit
                <VChip
                  v-if="allFormData.documents?.franchiseKit"
                  color="success"
                  size="small"
                  class="ms-2"
                >
                  {{ allFormData.documents.franchiseKit?.name || 'Uploaded' }}
                </VChip>
                <VChip
                  v-else
                  color="secondary"
                  size="small"
                  class="ms-2"
                >
                  Optional
                </VChip>
              </VListItemTitle>
            </VListItem>
            <VListItem v-if="allFormData.documents?.additionalDocuments && allFormData.documents.additionalDocuments.length > 0">
              <VListItemTitle>
                <VIcon
                  icon="tabler-check"
                  color="success"
                  class="me-2"
                />
                Additional Documents
                <VChip
                  color="success"
                  size="small"
                  class="ms-2"
                >
                  {{ allFormData.documents.additionalDocuments.length }} file(s)
                </VChip>
              </VListItemTitle>
            </VListItem>
          </VList>
          
          <VAlert
            v-if="!allFormData.documents?.fdd && !allFormData.documents?.franchiseAgreement && !allFormData.documents?.financialStudy && !allFormData.documents?.franchiseKit"
            type="info"
            variant="tonal"
            class="mt-4"
          >
            <VAlertTitle>Documents Optional</VAlertTitle>
            You can upload documents now or add them later from your dashboard after registration.
          </VAlert>
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
