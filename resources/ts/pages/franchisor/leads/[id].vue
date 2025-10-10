<script setup lang="ts">
import AddNoteModal from '@/components/franchisor/AddNoteModal.vue'
import { avatarText } from '@core/utils/formatters'

const route = useRoute('franchisor-lead-view-id')
const leadId = computed(() => Number(route.params.id))

const currentTab = ref('overview')
const isAddNoteModalVisible = ref(false)
const isViewNoteModalVisible = ref(false)
const isEditNoteModalVisible = ref(false)
const isDeleteNoteDialogVisible = ref(false)
const selectedNote = ref<any>(null)
const noteToDelete = ref<number | null>(null)
const isEditMode = ref(false)

// Lead data from API
const leadData = ref<any>(null)
const isLoadingLead = ref(false)

// Fetch lead data
const fetchLeadData = async () => {
  try {
    isLoadingLead.value = true
    const response = await $api(`/v1/franchisor/leads/${leadId.value}`, {
      method: 'GET',
    })

    if (response.success) {
      // Map API response to frontend format
      leadData.value = {
        id: response.data.id,
        firstName: response.data.first_name,
        lastName: response.data.last_name,
        email: response.data.email,
        phone: response.data.phone,
        company: response.data.company_name || response.data.company,
        country: response.data.country,
        state: response.data.state || response.data.address,
        city: response.data.city,
        source: response.data.lead_source,
        status: response.data.status,
        owner: response.data.assignedUser?.name || 'Unassigned',
        lastContacted: response.data.last_contact_date,
        scheduledMeeting: response.data.next_follow_up_date,
        note: response.data.notes,
        priority: response.data.priority,
        estimatedInvestment: response.data.estimated_investment,
        franchiseFeeQuoted: response.data.franchise_fee_quoted,
        expectedDecisionDate: response.data.expected_decision_date,
        contactAttempts: response.data.contact_attempts,
        interests: response.data.interests,
        documents: response.data.documents,
      }
    }
  } catch (error) {
    console.error('Error fetching lead data:', error)
    // TODO: Show error toast
  } finally {
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
  if (!leadId.value) return

  try {
    isLoadingNotes.value = true
    const response = await $api('/v1/notes', {
      method: 'GET',
      query: { lead_id: leadId.value },
    })

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
  } catch (error) {
    console.error('Error fetching notes:', error)
    // TODO: Show error toast
  } finally {
    isLoadingNotes.value = false
  }
}

// Fetch notes when component mounts and when leadId changes
watch(leadId, () => {
  if (leadId.value) {
    fetchNotes()
  }
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
  isViewNoteModalVisible.value = true
}

const editNote = (note: any) => {
  selectedNote.value = { ...note }
  isEditNoteModalVisible.value = true
}

const confirmDeleteNote = (noteId: number) => {
  noteToDelete.value = noteId
  isDeleteNoteDialogVisible.value = true
}

const deleteNote = async () => {
  if (noteToDelete.value === null) return

  try {
    const response = await $api(`/v1/notes/${noteToDelete.value}`, {
      method: 'DELETE',
    })

    if (response.success) {
      // Refresh notes from API
      await fetchNotes()
      console.log('Note deleted successfully')
      // TODO: Show success toast
    } else {
      console.error('Failed to delete note:', response.message)
      // TODO: Show error toast
    }
  } catch (error) {
    console.error('Error deleting note:', error)
    // TODO: Show error toast
  }

  isDeleteNoteDialogVisible.value = false
  noteToDelete.value = null
}

const onNoteUpdated = async () => {
  if (!selectedNote.value) return

  try {
    const response = await $api(`/v1/notes/${selectedNote.value.id}`, {
      method: 'PUT',
      body: {
        title: selectedNote.value.title,
        description: selectedNote.value.description,
        lead_id: leadId.value,
      },
    })

    if (response.success) {
      // Refresh notes from API
      await fetchNotes()
      isEditNoteModalVisible.value = false
      console.log('Note updated successfully')
      // TODO: Show success toast
    } else {
      console.error('Failed to update note:', response.message)
      // TODO: Show error toast
    }
  } catch (error) {
    console.error('Error updating note:', error)
    // TODO: Show error toast
  }
}

const onNoteAdded = async () => {
  // Refresh notes from API
  await fetchNotes()
  console.log('Note added successfully')
}

const toggleEditMode = () => {
  isEditMode.value = !isEditMode.value
}

const isSavingLead = ref(false)

const saveLead = async () => {
  if (!leadData.value) return

  try {
    isSavingLead.value = true

    // Map frontend data to API format
    const updateData = {
      first_name: leadData.value.firstName,
      last_name: leadData.value.lastName,
      email: leadData.value.email,
      phone: leadData.value.phone,
      company: leadData.value.company,
      country: leadData.value.country,
      state: leadData.value.state,
      city: leadData.value.city,
      source: leadData.value.source,
      status: leadData.value.status,
      notes: leadData.value.note,
      priority: leadData.value.priority,
      estimated_investment: leadData.value.estimatedInvestment,
      franchise_fee_quoted: leadData.value.franchiseFeeQuoted,
      expected_decision_date: leadData.value.expectedDecisionDate,
    }

    const response = await $api(`/v1/franchisor/leads/${leadId.value}`, {
      method: 'PUT',
      body: updateData,
    })

    if (response.success) {
      // Update local data with response
      await fetchLeadData()
      isEditMode.value = false
      // TODO: Show success toast
      console.log('Lead updated successfully')
    }
  } catch (error) {
    console.error('Error updating lead:', error)
    // TODO: Show error toast
  } finally {
    isSavingLead.value = false
  }
}
</script>

<template>
  <section>
    <!-- 👉 Loading State -->
    <VCard v-if="isLoadingLead" class="mb-6">
      <VCardText class="text-center py-10">
        <VProgressCircular indeterminate color="primary" />
        <div class="mt-4">Loading lead data...</div>
      </VCardText>
    </VCard>

    <!-- 👉 Lead Header -->
    <VCard v-else-if="leadData" class="mb-6">
      <VCardText>
        <div class="d-flex justify-space-between align-center flex-wrap gap-4">
          <div class="d-flex align-center gap-4">
            <VAvatar size="80" variant="tonal" color="primary">
              <span class="text-h4">{{ avatarText(`${leadData.firstName} ${leadData.lastName}`) }}</span>
            </VAvatar>
            <div>
              <h4 class="text-h4 mb-1">
                {{ leadData.firstName }} {{ leadData.lastName }}
              </h4>
              <div class="text-body-1 mb-1">
                {{ leadData.company }}
              </div>
              <VChip :color="leadData.status === 'qualified' ? 'success' : 'error'" size="small" label
                class="text-capitalize">
                {{ leadData.status }}
              </VChip>
            </div>
          </div>
          <div class="d-flex gap-2">
            <VBtn v-if="!isEditMode" color="primary" prepend-icon="tabler-pencil" @click="toggleEditMode">
              Edit
            </VBtn>
            <VBtn v-if="isEditMode" color="success" prepend-icon="tabler-check" :loading="isSavingLead"
              :disabled="isSavingLead" @click="saveLead">
              Save
            </VBtn>
            <VBtn v-if="isEditMode" color="secondary" variant="tonal" @click="toggleEditMode">
              Cancel
            </VBtn>
          </div>
        </div>
      </VCardText>
    </VCard>

    <!-- 👉 Tabs -->
    <template v-if="leadData">
      <VTabs v-model="currentTab" class="mb-6">
        <VTab v-for="tab in tabs" :key="tab.value" :value="tab.value">
          <VIcon :icon="tab.icon" start />
          {{ tab.title }}
        </VTab>
      </VTabs>

      <VWindow v-model="currentTab" class="disable-tab-transition">
        <!-- Overview Tab -->
        <VWindowItem value="overview">
          <VCard>
            <VCardText>
              <VRow>
                <VCol cols="12" md="6">
                  <h6 class="text-h6 mb-4">
                    Contact Information
                  </h6>
                  <VRow>
                    <VCol cols="12">
                      <AppTextField v-model="leadData.email" label="Email" :readonly="!isEditMode" />
                    </VCol>
                    <VCol cols="12">
                      <AppTextField v-model="leadData.phone" label="Phone" :readonly="!isEditMode" />
                    </VCol>
                    <VCol cols="12">
                      <AppTextField v-model="leadData.company" label="Company" :readonly="!isEditMode" />
                    </VCol>
                  </VRow>
                </VCol>

                <VCol cols="12" md="6">
                  <h6 class="text-h6 mb-4">
                    Location
                  </h6>
                  <VRow>
                    <VCol cols="12">
                      <AppTextField v-model="leadData.city" label="City" :readonly="!isEditMode" />
                    </VCol>
                    <VCol cols="12">
                      <AppTextField v-model="leadData.state" label="State" :readonly="!isEditMode" />
                    </VCol>
                    <VCol cols="12">
                      <AppTextField v-model="leadData.country" label="Country" :readonly="!isEditMode" />
                    </VCol>
                  </VRow>
                </VCol>

                <VCol cols="12" md="6">
                  <h6 class="text-h6 mb-4">
                    Lead Details
                  </h6>
                  <VRow>
                    <VCol cols="12">
                      <AppTextField v-model="leadData.source" label="Source" :readonly="!isEditMode" />
                    </VCol>
                    <VCol cols="12">
                      <AppTextField v-model="leadData.owner" label="Owner" :readonly="!isEditMode" />
                    </VCol>
                  </VRow>
                </VCol>

                <VCol cols="12" md="6">
                  <h6 class="text-h6 mb-4">
                    Timeline
                  </h6>
                  <VRow>
                    <VCol cols="12">
                      <AppTextField v-model="leadData.lastContacted" label="Last Contacted" :readonly="!isEditMode" />
                    </VCol>
                    <VCol cols="12">
                      <AppTextField v-model="leadData.scheduledMeeting" label="Scheduled Meeting"
                        :readonly="!isEditMode" />
                    </VCol>
                  </VRow>
                </VCol>

                <VCol cols="12">
                  <h6 class="text-h6 mb-4">
                    Notes
                  </h6>
                  <AppTextarea v-model="leadData.note" label="Additional Notes" rows="4" :readonly="!isEditMode" />
                </VCol>
              </VRow>
            </VCardText>
          </VCard>
        </VWindowItem>

        <!-- Notes Tab -->
        <VWindowItem value="notes">
          <VCard>
            <VCardItem class="pb-4">
              <VCardTitle>Notes</VCardTitle>
              <template #append>
                <VBtn color="primary" prepend-icon="tabler-plus" @click="isAddNoteModalVisible = true">
                  Add Note
                </VBtn>
              </template>
            </VCardItem>

            <VDivider />

            <VCardText>
              <VRow>
                <VCol v-for="note in notesData" :key="note.id" cols="12">
                  <VCard variant="tonal" class="mb-4">
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
                          <VBtn icon size="small" variant="text" @click="editNote(note)">
                            <VIcon icon="tabler-pencil" />
                          </VBtn>
                          <VBtn icon size="small" variant="text" color="error" @click="confirmDeleteNote(note.id)">
                            <VIcon icon="tabler-trash" />
                          </VBtn>
                        </div>
                      </div>
                      <p class="text-body-2 mb-2">
                        {{ getExcerpt(note.description) }}
                      </p>
                      <VBtn variant="text" size="small" @click="viewNote(note)">
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
    <AddNoteModal v-model:is-dialog-visible="isAddNoteModalVisible" :lead-id="leadId" @note-added="onNoteAdded" />

    <!-- 👉 View Note Modal -->
    <VDialog v-model="isViewNoteModalVisible" max-width="700">
      <VCard v-if="selectedNote">
        <VCardItem>
          <VCardTitle>{{ selectedNote.title }}</VCardTitle>
          <VCardSubtitle>
            Created by {{ selectedNote.createdBy }} at {{ formatDateTime(selectedNote.createdAt) }}
          </VCardSubtitle>
        </VCardItem>

        <VCardText>
          <p class="text-body-1">
            {{ selectedNote.description }}
          </p>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn color="secondary" variant="tonal" @click="isViewNoteModalVisible = false">
            Close
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- 👉 Edit Note Modal -->
    <VDialog v-model="isEditNoteModalVisible" max-width="700">
      <VCard v-if="selectedNote">
        <VCardItem>
          <VCardTitle>Edit Note</VCardTitle>
        </VCardItem>

        <VCardText>
          <VForm @submit.prevent="onNoteUpdated">
            <VRow>
              <VCol cols="12">
                <AppTextField v-model="selectedNote.title" label="Title" placeholder="Enter note title" />
              </VCol>

              <VCol cols="12">
                <AppTextarea v-model="selectedNote.description" label="Description"
                  placeholder="Enter note description..." rows="6" />
              </VCol>

              <VCol cols="12">
                <VFileInput label="Attachments" multiple prepend-icon="tabler-paperclip" placeholder="Upload files"
                  chips show-size />
              </VCol>
            </VRow>
          </VForm>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn color="secondary" variant="tonal" @click="isEditNoteModalVisible = false">
            Cancel
          </VBtn>
          <VBtn color="primary" @click="onNoteUpdated">
            Update Note
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- 👉 Delete Note Confirmation Dialog -->
    <VDialog v-model="isDeleteNoteDialogVisible" max-width="500">
      <VCard>
        <VCardItem>
          <VCardTitle>Confirm Delete</VCardTitle>
        </VCardItem>

        <VCardText>
          Are you sure you want to delete this note? This action cannot be undone.
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn color="secondary" variant="tonal" @click="isDeleteNoteDialogVisible = false">
            Cancel
          </VBtn>
          <VBtn color="error" @click="deleteNote">
            Delete
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </section>
</template>
