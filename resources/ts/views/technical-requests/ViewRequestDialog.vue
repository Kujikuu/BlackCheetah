<script setup lang="ts">
// Props
interface Props {
  visible: boolean
  request: any | null
  showEditButton?: boolean
  isAdmin?: boolean
  resolvePriorityVariant: (priority: string) => { color: string; icon: string }
  resolveStatusVariant: (status: string) => string
  formatStatus: (status: string) => string
  getFileIcon: (fileName: string) => string
}

// Emits
interface Emits {
  (e: 'update:visible', value: boolean): void
  (e: 'edit', request: any): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// Close dialog
const closeDialog = () => {
  emit('update:visible', false)
}

// Handle edit request
const handleEditRequest = () => {
  emit('edit', props.request)
}

// Download attachment
const downloadAttachment = (attachment: any) => {
  if (attachment.url && attachment.url !== '#')
    window.open(attachment.url, '_blank')
}

</script>

<template>
  <VDialog :model-value="visible" max-width="800" @update:model-value="emit('update:visible', $event)">
    <DialogCloseBtn @click="closeDialog" />
    <VCard v-if="request" title="Request Details">
      <VCardText class="pa-4 pa-sm-6">
        <VRow>
          <VCol cols="12" sm="6">
            <div class="text-body-2 text-medium-emphasis mb-1">
              Request ID
            </div>
            <div class="text-body-1 font-weight-medium">
              {{ request.requestId }}
            </div>
          </VCol>

          <VCol cols="12" sm="6">
            <div class="text-body-2 text-medium-emphasis mb-1">
              Date
            </div>
            <div class="text-body-1">
              {{ request.date }}
            </div>
          </VCol>

          <!-- Requester (Admin only) -->
          <VCol v-if="isAdmin" cols="12">
            <div class="text-body-2 text-medium-emphasis mb-1">
              Requester
            </div>
            <div class="d-flex align-center flex-wrap">
              <VAvatar
                v-if="request.userAvatar"
                size="32"
                class="me-3 mb-2"
              >
                <VImg :src="request.userAvatar" />
              </VAvatar>
              <VAvatar
                v-else
                size="32"
                class="me-3 mb-2"
                color="primary"
              >
                <span class="text-sm font-weight-medium">
                  {{ request.userName?.charAt(0)?.toUpperCase() }}
                </span>
              </VAvatar>
              <div class="flex-grow-1 min-width-0">
                <div class="text-body-1 font-weight-medium text-truncate">
                  {{ request.userName }}
                </div>
                <div class="text-sm text-medium-emphasis text-truncate">
                  {{ request.userEmail }}
                </div>
              </div>
            </div>
          </VCol>

          <VCol cols="12" sm="6">
            <div class="text-body-2 text-medium-emphasis mb-1">
              Priority
            </div>
            <VChip 
              :color="props.resolvePriorityVariant(request.priority).color" 
              size="small" 
              label
              class="text-capitalize"
            >
              <VIcon 
                :icon="props.resolvePriorityVariant(request.priority).icon" 
                size="16" 
                class="me-1" 
              />
              {{ request.priority }}
            </VChip>
          </VCol>

          <VCol cols="12" sm="6">
            <div class="text-body-2 text-medium-emphasis mb-1">
              Status
            </div>
            <VChip 
              :color="props.resolveStatusVariant(request.status)" 
              size="small" 
              label 
              class="text-capitalize"
            >
              {{ props.formatStatus(request.status) }}
            </VChip>
          </VCol>

          <VCol cols="12">
            <div class="text-body-2 text-medium-emphasis mb-1">
              Category
            </div>
            <div class="text-body-1">
              {{ request.category }}
            </div>
          </VCol>

          <VCol cols="12">
            <div class="text-body-2 text-medium-emphasis mb-1">
              Subject
            </div>
            <div class="text-body-1 font-weight-medium" style="word-wrap: break-word; overflow-wrap: break-word;">
              {{ request.subject }}
            </div>
          </VCol>

          <VCol cols="12">
            <div class="text-body-2 text-medium-emphasis mb-1">
              Description
            </div>
            <div class="text-body-1" style="word-wrap: break-word; overflow-wrap: break-word; white-space: pre-wrap;">
              {{ request.description }}
            </div>
          </VCol>

          <!-- Attachments -->
          <VCol v-if="request.attachments && request.attachments.length > 0" cols="12">
            <div class="text-body-2 text-medium-emphasis mb-2">
              Attachments ({{ request.attachments.length }})
            </div>
            <VList lines="two" density="compact" class="pa-0">
              <VListItem 
                v-for="(attachment, index) in request.attachments" 
                :key="index" 
                class="px-0"
              >
                <template #prepend>
                  <VAvatar color="primary" variant="tonal" size="40" class="me-2">
                    <VIcon :icon="props.getFileIcon(attachment.name)" />
                  </VAvatar>
                </template>

                <div class="flex-grow-1 min-width-0">
                  <VListItemTitle class="font-weight-medium text-truncate">
                    {{ attachment.name }}
                  </VListItemTitle>
                  <VListItemSubtitle class="text-truncate">
                    {{ attachment.size }}
                  </VListItemSubtitle>
                </div>

                <template #append>
                  <VBtn 
                    icon 
                    variant="text" 
                    size="small" 
                    color="primary" 
                    class="flex-shrink-0"
                    @click="downloadAttachment(attachment)"
                  >
                    <VIcon icon="tabler-download" />
                    <VTooltip activator="parent" location="top">
                      Download
                    </VTooltip>
                  </VBtn>
                </template>
              </VListItem>
            </VList>
          </VCol>
        </VRow>
      </VCardText>

      <VDivider />

      <VCardActions class="pa-4 pa-sm-6">
        <VSpacer />
        <div class="d-flex gap-2 flex-wrap justify-end" style="flex-basis: 100%;">
          <VBtn variant="outlined" @click="closeDialog">
            Close
          </VBtn>
          <VBtn 
            v-if="showEditButton" 
            color="primary" 
            @click="handleEditRequest"
          >
            <VIcon icon="tabler-edit" class="me-1" />
            Edit Request
          </VBtn>
        </div>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
