<script setup lang="ts">
interface Attachment {
  name: string
  path: string
  size: number
  type: string
}

interface Note {
  id: number
  title: string
  description: string
  createdBy: string
  createdAt: string
  attachments: Attachment[]
}

interface Props {
  isDialogVisible: boolean
  note: Note | null
}

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const dialogValue = computed({
  get: () => props.isDialogVisible,
  set: val => emit('update:isDialogVisible', val),
})

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

const formatFileSize = (bytes: number): string => {
  if (bytes === 0)
    return '0 Bytes'

  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))

  return `${Number.parseFloat((bytes / k ** i).toFixed(2))} ${sizes[i]}`
}

const downloadAttachment = (attachment: Attachment) => {
  // Create download link
  const link = document.createElement('a')

  link.href = `/storage/${attachment.path}`
  link.download = attachment.name
  link.target = '_blank'
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

const getFileIcon = (type: string): string => {
  if (type.startsWith('image/'))
    return 'tabler-photo'
  if (type.startsWith('video/'))
    return 'tabler-video'
  if (type.startsWith('audio/'))
    return 'tabler-music'
  if (type.includes('pdf'))
    return 'tabler-file-type-pdf'
  if (type.includes('word') || type.includes('document'))
    return 'tabler-file-type-doc'
  if (type.includes('excel') || type.includes('spreadsheet'))
    return 'tabler-file-type-xls'
  if (type.includes('zip') || type.includes('compressed'))
    return 'tabler-file-zip'

  return 'tabler-file'
}
</script>

<template>
  <VDialog
    v-model="dialogValue"
    max-width="800"
  >
    <DialogCloseBtn @click="dialogValue = false" />
    <VCard v-if="note" title="Note Details">
      <VCardText>
        <div class="mb-6">
          <p class="text-body-1 whitespace-pre-wrap">
            {{ note.description }}
          </p>
          <p class="text-body-2 text-medium-emphasis">
            Created by {{ note.createdBy }} at {{ formatDateTime(note.createdAt) }}
          </p>
        </div>

        <!-- Attachments Section -->
        <div v-if="note.attachments && note.attachments.length > 0">
          <h6 class="text-h6 mb-3">
            Attachments ({{ note.attachments.length }})
          </h6>
          <VRow>
            <VCol
              v-for="(attachment, index) in note.attachments"
              :key="index"
              cols="12"
              md="6"
            >
              <VCard
                variant="tonal"
                class="d-flex align-center pa-3"
              >
                <VIcon
                  :icon="getFileIcon(attachment.type)"
                  size="32"
                  class="me-3"
                />
                <div class="flex-grow-1 me-3">
                  <div class="text-body-2 font-weight-medium">
                    {{ attachment.name }}
                  </div>
                  <div class="text-caption text-disabled">
                    {{ formatFileSize(attachment.size) }}
                  </div>
                </div>
                <VBtn
                  icon
                  size="small"
                  variant="text"
                  color="primary"
                  @click="downloadAttachment(attachment)"
                >
                  <VIcon icon="tabler-download" />
                </VBtn>
              </VCard>
            </VCol>
          </VRow>
        </div>
      </VCardText>

      <VDivider />

      <VCardActions>
        <VSpacer />
        <VBtn
          color="secondary"
          variant="tonal"
          @click="dialogValue = false"
        >
          Close
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<style scoped>
.whitespace-pre-wrap {
    white-space: pre-wrap;
    word-wrap: break-word;
}
</style>
