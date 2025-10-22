<script setup lang="ts">
import AddNoteDialog from '@/components/dialogs/notes/AddNoteDialog.vue'
import EditNoteDialog from '@/components/dialogs/notes/EditNoteDialog.vue'
import ViewNoteDialog from '@/components/dialogs/notes/ViewNoteDialog.vue'
import DeleteNoteDialog from '@/components/dialogs/notes/DeleteNoteDialog.vue'
import { type Lead, leadApi, notesApi, usersApi } from '@/services/api'
import { avatarText } from '@core/utils/formatters'
import { useCountries } from '@/composables/useCountries'
import { useSaudiProvinces } from '@/composables/useSaudiProvinces'

const route = useRoute()
const leadId = computed(() => Number(route.params.id))

const currentTab = ref('overview')
const isAddNoteDialogVisible = ref(false)
const isViewNoteDialogVisible = ref(false)
const isEditNoteDialogVisible = ref(false)
const isDeleteNoteDialogVisible = ref(false)
const selectedNote = ref<any>(null)
const noteToDelete = ref<number | null>(null)
const isEditMode = ref(false)

// Lead data from API
const leadData = ref<Lead | null>(null)
const isLoadingLead = ref(false)

// Fetch lead data
const fetchLeadData = async () => {
  try {
    isLoadingLead.value = true

    const response = await leadApi.getLead(leadId.value)

    if (response.success && response.data)
      leadData.value = response.data
  }
  catch (error) {
    console.error('Error fetching lead data:', error)

    // TODO: Show error toast
  }
  finally {
    isLoadingLead.value = false
  }
}

// Fetch lead data on component mount
onMounted(() => {
  fetchLeadData()
})

// Notes data from API
const notesData = ref<any[]>([])
const isLoadingNotes = ref(false)

// Fetch notes from API
const fetchNotes = async () => {
  if (!leadId.value)
    return

  try {
    isLoadingNotes.value = true

    const response = await notesApi.getNotes({ lead_id: leadId.value })

    if (response.success) {
      notesData.value = response.data.map((note: any) => ({
        id: note.id,
        title: note.title,
        description: note.description,
        createdBy: note.user?.name || 'Unknown',
        createdAt: note.created_at,
        attachments: note.attachments || [],
      }))
    }
  }
  catch (error) {
    console.error('Error fetching notes:', error)

    // TODO: Show error toast
  }
  finally {
    isLoadingNotes.value = false
  }
}

// Fetch notes when component mounts and when leadId changes
watch(leadId, () => {
  if (leadId.value)
    fetchNotes()
}, { immediate: true })

const tabs = [
  { title: 'Overview', value: 'overview', icon: 'tabler-file-text' },
  { title: 'Notes', value: 'notes', icon: 'tabler-notes' },
]

const formatDate = (dateString: string) => {
  const date = new Date(dateString)

  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

const formatDateTime = (dateString: string) => {
  const date = new Date(dateString)

  return date.toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const getExcerpt = (text: string, maxLength: number = 150) => {
  if (text.length <= maxLength)
    return text

  return `${text.substring(0, maxLength)}...`
}

const viewNote = (note: any) => {
  selectedNote.value = note
  isViewNoteDialogVisible.value = true
}

const editNote = (note: any) => {
  selectedNote.value = { ...note }
  isEditNoteDialogVisible.value = true
}

const confirmDeleteNote = (noteId: number) => {
  noteToDelete.value = noteId
  isDeleteNoteDialogVisible.value = true
}

const deleteNote = async () => {
  if (noteToDelete.value === null)
    return

  try {
    const response = await notesApi.deleteNote(noteToDelete.value)

    if (response.success) {
      // Refresh notes from API
      await fetchNotes()
      console.log('Note deleted successfully')

      // TODO: Show success toast
    }
    else {
      console.error('Failed to delete note:', response.message)

      // TODO: Show error toast
    }
  }
  catch (error) {
    console.error('Error deleting note:', error)

    // TODO: Show error toast
  }

  isDeleteNoteDialogVisible.value = false
  noteToDelete.value = null
}

const onNoteUpdated = async () => {
  // Refresh notes from API
  await fetchNotes()
  isEditNoteDialogVisible.value = false
  console.log('Note updated successfully')
}

const onNoteAdded = async () => {
  // Refresh notes from API
  await fetchNotes()
  console.log('Note added successfully')
}

const onNoteDeleted = async () => {
  // Refresh notes from API
  await fetchNotes()
  isDeleteNoteDialogVisible.value = false
  noteToDelete.value = null
  console.log('Note deleted successfully')
}

const toggleEditMode = () => {
  isEditMode.value = !isEditMode.value
}

const isSavingLead = ref(false)

const saveLead = async () => {
  if (!leadData.value)
    return

  try {
    isSavingLead.value = true

    const response = await leadApi.updateLead(leadId.value, leadData.value)

    if (response.success) {
      // Update local data with response
      await fetchLeadData()
      isEditMode.value = false

      // TODO: Show success toast
      console.log('Lead updated successfully')
    }
  }
  catch (error) {
    console.error('Error updating lead:', error)

    // TODO: Show error toast
  }
  finally {
    isSavingLead.value = false
  }
}

// Sales associates for owner selection
const salesAssociates = ref<Array<{ title: string; value: string }>>([])

const fetchSalesAssociates = async () => {
  try {
    const response = await usersApi.getSalesAssociates()

    if (response.success) {
      // API returns paginated data, handle both array and paginated response
      const associatesArray = Array.isArray(response.data) ? response.data : response.data.data || []
      
      salesAssociates.value = associatesArray.map((user: any) => ({
        title: user.name,
        value: user.name,
      }))
    }
  }
  catch (error) {
    console.error('Error fetching sales associates:', error)
  }
}

// Fetch sales associates on mount
onMounted(() => {
  fetchSalesAssociates()
})

// Dropdown options
// Get countries from composable
const { countries: nationalityOptions, isLoading: isLoadingCountries } = useCountries()

// Get Saudi provinces and cities
const { provinces, getCitiesForProvince, isLoading: isLoadingProvinces } = useSaudiProvinces()

// Available cities based on selected province
const availableCities = computed(() => {
  if (!leadData.value) return []
  return getCitiesForProvince(leadData.value.state || '')
})

// Watch province changes to clear city
watch(() => leadData.value?.state, () => {
  if (leadData.value)
    leadData.value.city = ''
})

const leadSources = [
  { title: 'Website', value: 'website' },
  { title: 'Referral', value: 'referral' },
  { title: 'Social Media', value: 'social_media' },
  { title: 'Advertisement', value: 'advertisement' },
  { title: 'Cold Call', value: 'cold_call' },
  { title: 'Event', value: 'event' },
  { title: 'Other', value: 'other' },
]

const leadStatuses = [
  { title: 'New', value: 'new' },
  { title: 'Contacted', value: 'contacted' },
  { title: 'Qualified', value: 'qualified' },
  { title: 'Unqualified', value: 'unqualified' },
  { title: 'Converted', value: 'converted' },
  { title: 'Lost', value: 'lost' },
]

const priorities = [
  { title: 'Low', value: 'low' },
  { title: 'Medium', value: 'medium' },
  { title: 'High', value: 'high' },
  { title: 'Urgent', value: 'urgent' },
]
</script>

<template>
  <section>
    <!-- 👉 Loading State -->
    <VCard
      v-if="isLoadingLead"
      class="mb-6"
    >
      <VCardText class="text-center py-10">
        <VProgressCircular
          indeterminate
          color="primary"
        />
        <div class="mt-4">
          Loading lead data...
        </div>
      </VCardText>
    </VCard>

    <!-- 👉 Lead Header -->
    <VCard
      v-else-if="leadData"
      class="mb-6"
    >
      <VCardText>
        <div class="d-flex justify-space-between align-center flex-wrap gap-4">
          <div class="d-flex align-center gap-4">
            <VAvatar
              size="80"
              variant="tonal"
              color="primary"
            >
              <span class="text-h4">{{ avatarText(`${leadData.firstName} ${leadData.lastName}`) }}</span>
            </VAvatar>
            <div>
              <h4 class="text-h4 mb-1">
                {{ leadData.firstName }} {{ leadData.lastName }}
              </h4>
              <div class="text-body-1 mb-1">
                {{ leadData.company }}
              </div>
              <VChip
                :color="leadData.status === 'qualified' ? 'success' : 'error'"
                size="small"
                label
                class="text-capitalize"
              >
                {{ leadData.status }}
              </VChip>
            </div>
          </div>
          <div class="d-flex gap-2">
            <VBtn
              v-if="!isEditMode"
              color="primary"
              prepend-icon="tabler-pencil"
              @click="toggleEditMode"
            >
              Edit
            </VBtn>
            <VBtn
              v-if="isEditMode"
              color="success"
              prepend-icon="tabler-check"
              :loading="isSavingLead"
              :disabled="isSavingLead"
              @click="saveLead"
            >
              Save
            </VBtn>
            <VBtn
              v-if="isEditMode"
              color="secondary"
              variant="tonal"
              @click="toggleEditMode"
            >
              Cancel
            </VBtn>
          </div>
        </div>
      </VCardText>
    </VCard>

    <!-- 👉 Tabs -->
    <template v-if="leadData">
      <VTabs
        v-model="currentTab"
        class="mb-6"
      >
        <VTab
          v-for="tab in tabs"
          :key="tab.value"
          :value="tab.value"
        >
          <VIcon
            :icon="tab.icon"
            start
          />
          {{ tab.title }}
        </VTab>
      </VTabs>

      <VWindow
        v-model="currentTab"
        class="disable-tab-transition"
      >
        <!-- Overview Tab -->
        <VWindowItem value="overview">
          <VRow>
            <!-- Left Column -->
            <VCol
              cols="12"
              lg="8"
            >
              <!-- Personal & Contact Information -->
              <VCard class="mb-6">
                <VCardItem>
                  <VCardTitle>Personal & Contact Information</VCardTitle>
                </VCardItem>
                <VDivider />
                <VCardText>
                  <VRow>
                    <VCol
                      cols="12"
                      md="6"
                    >
                      <AppTextField
                        v-model="leadData.firstName"
                        label="First Name"
                        :readonly="!isEditMode"
                      />
                    </VCol>
                    <VCol
                      cols="12"
                      md="6"
                    >
                      <AppTextField
                        v-model="leadData.lastName"
                        label="Last Name"
                        :readonly="!isEditMode"
                      />
                    </VCol>
                    <VCol
                      cols="12"
                      md="6"
                    >
                      <AppTextField
                        v-model="leadData.email"
                        label="Email"
                        :readonly="!isEditMode"
                      />
                    </VCol>
                    <VCol
                      cols="12"
                      md="6"
                    >
                      <AppTextField
                        v-model="leadData.phone"
                        label="Phone"
                        :readonly="!isEditMode"
                      />
                    </VCol>
                    <VCol cols="12">
                      <AppTextField
                        v-model="leadData.company"
                        label="Company"
                        :readonly="!isEditMode"
                      />
                    </VCol>
                  </VRow>
                </VCardText>
              </VCard>

              <!-- Location Information -->
              <VCard class="mb-6">
                <VCardItem>
                  <VCardTitle>Location</VCardTitle>
                </VCardItem>
                <VDivider />
                <VCardText>
                  <VRow>
                    <VCol
                      cols="12"
                      md="4"
                    >
                      <AppSelect
                        v-if="isEditMode"
                        v-model="leadData.nationality"
                        label="Nationality"
                        placeholder="Select Nationality"
                        :items="nationalityOptions"
                        :loading="isLoadingCountries"
                        clearable
                      />
                      <AppTextField
                        v-else
                        v-model="leadData.nationality"
                        label="Nationality"
                        readonly
                      />
                    </VCol>
                    <VCol
                      cols="12"
                      md="4"
                    >
                      <AppSelect
                        v-if="isEditMode"
                        v-model="leadData.state"
                        label="Province"
                        placeholder="Select Province"
                        :items="provinces"
                        :loading="isLoadingProvinces"
                        clearable
                      />
                      <AppTextField
                        v-else
                        v-model="leadData.state"
                        label="Province"
                        readonly
                      />
                    </VCol>
                    <VCol
                      cols="12"
                      md="4"
                    >
                      <AppSelect
                        v-if="isEditMode"
                        v-model="leadData.city"
                        label="City"
                        placeholder="Select City"
                        :items="availableCities"
                        :disabled="!leadData.state"
                        clearable
                      />
                      <AppTextField
                        v-else
                        v-model="leadData.city"
                        label="City"
                        readonly
                      />
                    </VCol>
                  </VRow>
                </VCardText>
              </VCard>

              <!-- Financial Details -->
              <VCard class="mb-6">
                <VCardItem>
                  <VCardTitle>Financial Details</VCardTitle>
                </VCardItem>
                <VDivider />
                <VCardText>
                  <VRow>
                    <VCol
                      cols="12"
                      md="4"
                    >
                      <AppTextField
                        v-model="leadData.estimatedInvestment"
                        label="Estimated Investment"
                        type="number"
                        prefix="SAR"
                        :readonly="!isEditMode"
                      />
                    </VCol>
                    <VCol
                      cols="12"
                      md="4"
                    >
                      <AppTextField
                        v-model="leadData.franchiseFeeQuoted"
                        label="Franchise Fee Quoted"
                        type="number"
                        prefix="SAR"
                        :readonly="!isEditMode"
                      />
                    </VCol>
                    <VCol
                      cols="12"
                      md="4"
                    >
                      <AppDateTimePicker
                        v-if="isEditMode"
                        v-model="leadData.expectedDecisionDate"
                        label="Expected Decision Date"
                        placeholder="Select Date"
                      />
                      <AppTextField
                        v-else
                        v-model="leadData.expectedDecisionDate"
                        label="Expected Decision Date"
                        readonly
                      />
                    </VCol>
                  </VRow>
                </VCardText>
              </VCard>

              <!-- Additional Notes -->
              <VCard>
                <VCardItem>
                  <VCardTitle>Additional Notes</VCardTitle>
                </VCardItem>
                <VDivider />
                <VCardText>
                  <AppTextarea
                    v-model="leadData.note"
                    label="Notes"
                    placeholder="Add any additional notes about this lead..."
                    rows="4"
                    :readonly="!isEditMode"
                  />
                </VCardText>
              </VCard>
            </VCol>

            <!-- Right Column -->
            <VCol
              cols="12"
              lg="4"
            >
              <!-- Lead Management -->
              <VCard class="mb-6">
                <VCardItem>
                  <VCardTitle>Lead Management</VCardTitle>
                </VCardItem>
                <VDivider />
                <VCardText>
                  <VRow>
                    <VCol cols="12">
                      <AppSelect
                        v-if="isEditMode"
                        v-model="leadData.status"
                        label="Status"
                        placeholder="Select Status"
                        :items="leadStatuses"
                      />
                      <AppTextField
                        v-else
                        v-model="leadData.status"
                        label="Status"
                        readonly
                        class="text-capitalize"
                      />
                    </VCol>
                    <VCol cols="12">
                      <AppSelect
                        v-if="isEditMode"
                        v-model="leadData.priority"
                        label="Priority"
                        placeholder="Select Priority"
                        :items="priorities"
                      />
                      <AppTextField
                        v-else
                        v-model="leadData.priority"
                        label="Priority"
                        readonly
                        class="text-capitalize"
                      />
                    </VCol>
                    <VCol cols="12">
                      <AppSelect
                        v-if="isEditMode"
                        v-model="leadData.source"
                        label="Source"
                        placeholder="Select Source"
                        :items="leadSources"
                      />
                      <AppTextField
                        v-else
                        v-model="leadData.source"
                        label="Source"
                        readonly
                      />
                    </VCol>
                    <VCol cols="12">
                      <AppSelect
                        v-if="isEditMode"
                        v-model="leadData.owner"
                        label="Assigned To"
                        placeholder="Select Owner"
                        :items="salesAssociates"
                      />
                      <AppTextField
                        v-else
                        v-model="leadData.owner"
                        label="Assigned To"
                        readonly
                      />
                    </VCol>
                  </VRow>
                </VCardText>
              </VCard>

              <!-- Timeline & Engagement -->
              <VCard>
                <VCardItem>
                  <VCardTitle>Timeline & Engagement</VCardTitle>
                </VCardItem>
                <VDivider />
                <VCardText>
                  <VRow>
                    <VCol cols="12">
                      <AppDateTimePicker
                        v-if="isEditMode"
                        v-model="leadData.lastContacted"
                        label="Last Contacted"
                        placeholder="Select Date"
                      />
                      <AppTextField
                        v-else
                        v-model="leadData.lastContacted"
                        label="Last Contacted"
                        readonly
                      />
                    </VCol>
                    <VCol cols="12">
                      <AppDateTimePicker
                        v-if="isEditMode"
                        v-model="leadData.scheduledMeeting"
                        label="Next Follow-up"
                        placeholder="Select Date"
                      />
                      <AppTextField
                        v-else
                        v-model="leadData.scheduledMeeting"
                        label="Next Follow-up"
                        readonly
                      />
                    </VCol>
                    <VCol cols="12">
                      <AppTextField
                        v-model="leadData.contactAttempts"
                        label="Contact Attempts"
                        type="number"
                        :readonly="!isEditMode"
                      />
                    </VCol>
                  </VRow>
                </VCardText>
              </VCard>
            </VCol>
          </VRow>
        </VWindowItem>

        <!-- Notes Tab -->
        <VWindowItem value="notes">
          <VCard>
            <VCardItem class="pb-4">
              <VCardTitle>Notes</VCardTitle>
              <template #append>
                <VBtn
                  color="primary"
                  prepend-icon="tabler-plus"
                  @click="isAddNoteDialogVisible = true"
                >
                  Add Note
                </VBtn>
              </template>
            </VCardItem>

            <VDivider />

            <VCardText>
              <VRow>
                <VCol
                  v-for="note in notesData"
                  :key="note.id"
                  cols="12"
                >
                  <VCard
                    variant="tonal"
                    class="mb-4"
                  >
                    <VCardText>
                      <div class="d-flex justify-space-between align-start mb-2">
                        <div>
                          <div class="text-caption text-disabled mb-1">
                            Created by {{ note.createdBy }} at {{ formatDateTime(note.createdAt) }}
                          </div>
                          <h6 class="text-h6 mb-2">
                            {{ note.title }}
                          </h6>
                        </div>
                        <div class="d-flex gap-2">
                          <VBtn
                            icon
                            size="small"
                            variant="text"
                            @click="editNote(note)"
                          >
                            <VIcon icon="tabler-pencil" />
                          </VBtn>
                          <VBtn
                            icon
                            size="small"
                            variant="text"
                            color="error"
                            @click="confirmDeleteNote(note.id)"
                          >
                            <VIcon icon="tabler-trash" />
                          </VBtn>
                        </div>
                      </div>
                      <p class="text-body-2 mb-2">
                        {{ getExcerpt(note.description) }}
                      </p>
                      <VBtn
                        variant="text"
                        size="small"
                        @click="viewNote(note)"
                      >
                        Read More
                      </VBtn>
                    </VCardText>
                  </VCard>
                </VCol>
              </VRow>
            </VCardText>
          </VCard>
        </VWindowItem>
      </VWindow>
    </template>

    <!-- 👉 Add Note Modal -->
    <AddNoteDialog
      v-model:is-dialog-visible="isAddNoteDialogVisible"
      :lead-id="leadId"
      @note-added="onNoteAdded"
    />

    <!-- 👉 View Note Modal -->
    <ViewNoteDialog
      v-model:is-dialog-visible="isViewNoteDialogVisible"
      :note="selectedNote"
    />

    <!-- 👉 Edit Note Modal -->
    <EditNoteDialog
      v-model:is-dialog-visible="isEditNoteDialogVisible"
      :note="selectedNote"
      @note-updated="onNoteUpdated"
    />

    <!-- 👉 Delete Note Dialog -->
    <DeleteNoteDialog
      v-model:is-dialog-visible="isDeleteNoteDialogVisible"
      :note-id="noteToDelete"
      @note-deleted="onNoteDeleted"
    />
  </section>
</template>
