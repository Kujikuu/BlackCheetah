<script setup lang="ts">
import AddNoteModal from '@/components/franchisor/AddNoteModal.vue'

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

// Mock lead data - Replace with actual API call
const leadData = ref({
  id: leadId.value,
  firstName: 'John',
  lastName: 'Smith',
  email: 'john.smith@example.com',
  phone: '+1 234-567-8900',
  company: 'Tech Corp',
  country: 'USA',
  state: 'California',
  city: 'San Francisco',
  source: 'Website',
  status: 'qualified',
  owner: 'Sarah Johnson',
  lastContacted: '2024-01-15',
  scheduledMeeting: '2024-01-20',
  note: 'Interested in franchise opportunities in the Bay Area',
})

// Mock notes data - Replace with actual API call
const notesData = ref([
  {
    id: 1,
    title: 'Initial Contact',
    description: 'Had a great initial conversation with John. He expressed strong interest in opening a franchise in San Francisco. Discussed investment requirements and timeline. He mentioned having prior business experience in the food industry and is looking for a proven franchise model. Scheduled a follow-up meeting for next week to discuss location options and financial projections.',
    createdBy: 'Sarah Johnson',
    createdAt: '2024-01-15T10:30:00',
  },
  {
    id: 2,
    title: 'Follow-up Meeting',
    description: 'Met with John to review franchise agreement and discuss potential locations. He is very interested in the downtown area.',
    createdBy: 'Sarah Johnson',
    createdAt: '2024-01-18T14:00:00',
  },
  {
    id: 3,
    title: 'Financial Review',
    description: 'Reviewed financial projections and investment requirements. John confirmed he has the necessary capital.',
    createdBy: 'John Smith',
    createdAt: '2024-01-20T11:15:00',
  },
])

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

  // TODO: Implement API call
  const index = notesData.value.findIndex(note => note.id === noteToDelete.value)
  if (index !== -1)
    notesData.value.splice(index, 1)

  isDeleteNoteDialogVisible.value = false
  noteToDelete.value = null
}

const onNoteUpdated = () => {
  // TODO: Refresh notes from API
  console.log('Note updated, refreshing list')
  isEditNoteModalVisible.value = false
}

const onNoteAdded = () => {
  // TODO: Refresh notes from API
  console.log('Note added, refreshing list')
}

const toggleEditMode = () => {
  isEditMode.value = !isEditMode.value
}

const saveLead = async () => {
  // TODO: Implement API call to save lead
  console.log('Saving lead:', leadData.value)
  isEditMode.value = false
}
</script>

<template>
  <section>
    <!-- 👉 Lead Header -->
    <VCard class="mb-6">
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
        <VCard>
          <VCardText>
            <VRow>
              <VCol
                cols="12"
                md="6"
              >
                <h6 class="text-h6 mb-4">
                  Contact Information
                </h6>
                <VRow>
                  <VCol cols="12">
                    <AppTextField
                      v-model="leadData.email"
                      label="Email"
                      :readonly="!isEditMode"
                    />
                  </VCol>
                  <VCol cols="12">
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
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <h6 class="text-h6 mb-4">
                  Location
                </h6>
                <VRow>
                  <VCol cols="12">
                    <AppTextField
                      v-model="leadData.city"
                      label="City"
                      :readonly="!isEditMode"
                    />
                  </VCol>
                  <VCol cols="12">
                    <AppTextField
                      v-model="leadData.state"
                      label="State"
                      :readonly="!isEditMode"
                    />
                  </VCol>
                  <VCol cols="12">
                    <AppTextField
                      v-model="leadData.country"
                      label="Country"
                      :readonly="!isEditMode"
                    />
                  </VCol>
                </VRow>
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <h6 class="text-h6 mb-4">
                  Lead Details
                </h6>
                <VRow>
                  <VCol cols="12">
                    <AppTextField
                      v-model="leadData.source"
                      label="Source"
                      :readonly="!isEditMode"
                    />
                  </VCol>
                  <VCol cols="12">
                    <AppTextField
                      v-model="leadData.owner"
                      label="Owner"
                      :readonly="!isEditMode"
                    />
                  </VCol>
                </VRow>
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <h6 class="text-h6 mb-4">
                  Timeline
                </h6>
                <VRow>
                  <VCol cols="12">
                    <AppTextField
                      v-model="leadData.lastContacted"
                      label="Last Contacted"
                      :readonly="!isEditMode"
                    />
                  </VCol>
                  <VCol cols="12">
                    <AppTextField
                      v-model="leadData.scheduledMeeting"
                      label="Scheduled Meeting"
                      :readonly="!isEditMode"
                    />
                  </VCol>
                </VRow>
              </VCol>

              <VCol cols="12">
                <h6 class="text-h6 mb-4">
                  Notes
                </h6>
                <AppTextarea
                  v-model="leadData.note"
                  label="Additional Notes"
                  rows="4"
                  :readonly="!isEditMode"
                />
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
              <VBtn
                color="primary"
                prepend-icon="tabler-plus"
                @click="isAddNoteModalVisible = true"
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

    <!-- 👉 Add Note Modal -->
    <AddNoteModal
      v-model:is-dialog-visible="isAddNoteModalVisible"
      :lead-id="leadId"
      @note-added="onNoteAdded"
    />

    <!-- 👉 View Note Modal -->
    <VDialog
      v-model="isViewNoteModalVisible"
      max-width="700"
    >
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
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isViewNoteModalVisible = false"
          >
            Close
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- 👉 Edit Note Modal -->
    <VDialog
      v-model="isEditNoteModalVisible"
      max-width="700"
    >
      <VCard v-if="selectedNote">
        <VCardItem>
          <VCardTitle>Edit Note</VCardTitle>
        </VCardItem>

        <VCardText>
          <VForm @submit.prevent="onNoteUpdated">
            <VRow>
              <VCol cols="12">
                <AppTextField
                  v-model="selectedNote.title"
                  label="Title"
                  placeholder="Enter note title"
                />
              </VCol>

              <VCol cols="12">
                <AppTextarea
                  v-model="selectedNote.description"
                  label="Description"
                  placeholder="Enter note description..."
                  rows="6"
                />
              </VCol>

              <VCol cols="12">
                <VFileInput
                  label="Attachments"
                  multiple
                  prepend-icon="tabler-paperclip"
                  placeholder="Upload files"
                  chips
                  show-size
                />
              </VCol>
            </VRow>
          </VForm>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isEditNoteModalVisible = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            @click="onNoteUpdated"
          >
            Update Note
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- 👉 Delete Note Confirmation Dialog -->
    <VDialog
      v-model="isDeleteNoteDialogVisible"
      max-width="500"
    >
      <VCard>
        <VCardItem>
          <VCardTitle>Confirm Delete</VCardTitle>
        </VCardItem>

        <VCardText>
          Are you sure you want to delete this note? This action cannot be undone.
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isDeleteNoteDialogVisible = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="error"
            @click="deleteNote"
          >
            Delete
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </section>
</template>
