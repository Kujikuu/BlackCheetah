<script setup lang="ts">
import type { Lead } from '@/services/api'

interface Props {
  isDialogVisible: boolean
  lead: Lead | null
}

interface Emits {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'editLead', lead: Lead): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const dialogValue = computed({
  get: () => props.isDialogVisible,
  set: val => emit('update:isDialogVisible', val),
})

const resolveStatusVariant = (stat: string) => {
  const statLowerCase = stat.toLowerCase()
  if (statLowerCase === 'new')
    return 'info'
  if (statLowerCase === 'contacted')
    return 'primary'
  if (statLowerCase === 'qualified')
    return 'success'
  if (statLowerCase === 'closed_won')
    return 'success'
  if (statLowerCase === 'closed_lost')
    return 'error'
  
  return 'primary'
}

const resolvePriorityVariant = (priority: string) => {
  const priorityLowerCase = priority.toLowerCase()
  if (priorityLowerCase === 'low')
    return 'success'
  if (priorityLowerCase === 'medium')
    return 'warning'
  if (priorityLowerCase === 'high')
    return 'error'
  if (priorityLowerCase === 'urgent')
    return 'error'
  
  return 'secondary'
}

const formatSource = (source: string) => {
  return source.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const handleEditLead = () => {
  if (props.lead) {
    emit('editLead', props.lead)
    dialogValue.value = false
  }
}

const handleClose = () => {
  dialogValue.value = false
}
</script>

<template>
  <VDialog
    v-model="dialogValue"
    max-width="900"
    scrollable
  >
    <DialogCloseBtn @click="handleClose" />
    <VCard
      v-if="lead"
      title="Lead Details"
    >
      <VCardText>
        <VRow>
          <!-- First Name -->
          <VCol
            cols="12"
            md="6"
          >
            <div class="mb-4">
              <div class="text-sm text-disabled mb-1">
                First Name
              </div>
              <div class="text-body-1 font-weight-medium">
                {{ lead.firstName }}
              </div>
            </div>
          </VCol>

          <!-- Last Name -->
          <VCol
            cols="12"
            md="6"
          >
            <div class="mb-4">
              <div class="text-sm text-disabled mb-1">
                Last Name
              </div>
              <div class="text-body-1 font-weight-medium">
                {{ lead.lastName }}
              </div>
            </div>
          </VCol>

          <!-- Email -->
          <VCol
            cols="12"
            md="6"
          >
            <div class="mb-4">
              <div class="text-sm text-disabled mb-1">
                Email
              </div>
              <div class="text-body-1">
                <a :href="`mailto:${lead.email}`">{{ lead.email }}</a>
              </div>
            </div>
          </VCol>

          <!-- Phone -->
          <VCol
            cols="12"
            md="6"
          >
            <div class="mb-4">
              <div class="text-sm text-disabled mb-1">
                Phone
              </div>
              <div class="text-body-1">
                <a :href="`tel:${lead.phone}`">{{ lead.phone }}</a>
              </div>
            </div>
          </VCol>

          <!-- Company -->
          <VCol
            v-if="lead.company"
            cols="12"
            md="6"
          >
            <div class="mb-4">
              <div class="text-sm text-disabled mb-1">
                Company
              </div>
              <div class="text-body-1">
                {{ lead.company }}
              </div>
            </div>
          </VCol>

          <!-- Job Title -->
          <VCol
            v-if="lead.jobTitle"
            cols="12"
            md="6"
          >
            <div class="mb-4">
              <div class="text-sm text-disabled mb-1">
                Job Title
              </div>
              <div class="text-body-1">
                {{ lead.jobTitle }}
              </div>
            </div>
          </VCol>

          <!-- Nationality -->
          <VCol
            cols="12"
            md="6"
          >
            <div class="mb-4">
              <div class="text-sm text-disabled mb-1">
                Nationality
              </div>
              <div class="text-body-1">
                {{ lead.nationality || 'N/A' }}
              </div>
            </div>
          </VCol>

          <!-- Province/State -->
          <VCol
            cols="12"
            md="6"
          >
            <div class="mb-4">
              <div class="text-sm text-disabled mb-1">
                Province
              </div>
              <div class="text-body-1">
                {{ lead.state || 'N/A' }}
              </div>
            </div>
          </VCol>

          <!-- City -->
          <VCol
            cols="12"
            md="6"
          >
            <div class="mb-4">
              <div class="text-sm text-disabled mb-1">
                City
              </div>
              <div class="text-body-1">
                {{ lead.city }}
              </div>
            </div>
          </VCol>

          <!-- Source -->
          <VCol
            cols="12"
            md="6"
          >
            <div class="mb-4">
              <div class="text-sm text-disabled mb-1">
                Lead Source
              </div>
              <div class="text-body-1">
                {{ formatSource(lead.source) }}
              </div>
            </div>
          </VCol>

          <!-- Status -->
          <VCol
            cols="12"
            md="6"
          >
            <div class="mb-4">
              <div class="text-sm text-disabled mb-1">
                Status
              </div>
              <VChip
                :color="resolveStatusVariant(lead.status)"
                size="small"
                label
                class="text-capitalize"
              >
                {{ lead.status.replace(/_/g, ' ') }}
              </VChip>
            </div>
          </VCol>

          <!-- Priority -->
          <VCol
            v-if="lead.priority"
            cols="12"
            md="6"
          >
            <div class="mb-4">
              <div class="text-sm text-disabled mb-1">
                Priority
              </div>
              <VChip
                :color="resolvePriorityVariant(lead.priority)"
                size="small"
                label
                class="text-capitalize"
              >
                {{ lead.priority }}
              </VChip>
            </div>
          </VCol>

          <!-- Owner -->
          <VCol
            cols="12"
            md="6"
          >
            <div class="mb-4">
              <div class="text-sm text-disabled mb-1">
                Assigned To
              </div>
              <div class="text-body-1">
                {{ lead.owner }}
              </div>
            </div>
          </VCol>

          <!-- Last Contacted -->
          <VCol
            v-if="lead.lastContacted"
            cols="12"
            md="6"
          >
            <div class="mb-4">
              <div class="text-sm text-disabled mb-1">
                Last Contacted
              </div>
              <div class="text-body-1">
                {{ lead.lastContacted }}
              </div>
            </div>
          </VCol>

          <!-- Scheduled Meeting -->
          <VCol
            v-if="lead.scheduledMeeting"
            cols="12"
            md="6"
          >
            <div class="mb-4">
              <div class="text-sm text-disabled mb-1">
                Scheduled Meeting
              </div>
              <div class="text-body-1">
                {{ lead.scheduledMeeting }}
              </div>
            </div>
          </VCol>

          <!-- Notes -->
          <VCol
            v-if="lead.note"
            cols="12"
          >
            <div class="mb-4">
              <div class="text-sm text-disabled mb-1">
                Notes
              </div>
              <div class="text-body-1">
                {{ lead.note }}
              </div>
            </div>
          </VCol>
        </VRow>
      </VCardText>

      <VCardActions>
        <VSpacer />
        <VBtn
          color="secondary"
          variant="tonal"
          @click="handleClose"
        >
          Close
        </VBtn>
        <VBtn
          color="primary"
          @click="handleEditLead"
        >
          <VIcon
            icon="tabler-edit"
            class="me-1"
          />
          Edit
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
