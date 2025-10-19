<script setup lang="ts">
interface Props {
  isDialogOpen: boolean
  user?: any
  userType?: string
}

interface Emit {
  (e: 'update:isDialogOpen', value: boolean): void
  (e: 'edit'): void
}

const props = withDefaults(defineProps<Props>(), {
  user: undefined,
  userType: 'User',
})

const emit = defineEmits<Emit>()

const handleClose = () => {
  emit('update:isDialogOpen', false)
}

const handleEdit = () => {
  emit('edit')
  emit('update:isDialogOpen', false)
}

const resolveUserStatusVariant = (stat: string) => {
  const statLowerCase = stat?.toLowerCase() || ''
  if (statLowerCase === 'pending')
    return 'warning'
  if (statLowerCase === 'active')
    return 'success'
  if (statLowerCase === 'inactive')
    return 'secondary'

  return 'primary'
}

const resolveUserPlanVariant = (plan: string) => {
  const planLowerCase = plan?.toLowerCase() || ''
  if (planLowerCase === 'basic')
    return 'primary'
  if (planLowerCase === 'pro')
    return 'success'
  if (planLowerCase === 'enterprise')
    return 'warning'

  return 'primary'
}
</script>

<template>
  <VDialog
    :model-value="props.isDialogOpen"
    max-width="600"
    @update:model-value="(val: boolean) => emit('update:isDialogOpen', val)"
  >
    <DialogCloseBtn @click="handleClose" />
    <VCard v-if="props.user" title="User Details">
      <VDivider />

      <VCardText>
        <VRow>
          <VCol cols="12">
            <div class="d-flex align-center gap-x-4 mb-6">
              <VAvatar
                size="64"
                :variant="!props.user.avatar ? 'tonal' : undefined"
                color="primary"
              >
                <VImg
                  v-if="props.user.avatar"
                  :src="props.user.avatar"
                />
                <span
                  v-else
                  class="text-h4"
                >{{ avatarText(props.user.fullName) }}</span>
              </VAvatar>
              <div>
                <h6 class="text-h6 mb-1">
                  {{ props.user.fullName }}
                </h6>
                <div class="text-body-2 text-medium-emphasis">
                  {{ props.user.email }}
                </div>
              </div>
            </div>
          </VCol>

          <!-- Full Name -->
          <VCol
            cols="12"
            md="6"
          >
            <div class="text-body-2 text-medium-emphasis mb-1">
              Full Name
            </div>
            <div class="text-body-1 font-weight-medium">
              {{ props.user.fullName }}
            </div>
          </VCol>

          <!-- Email -->
          <VCol
            cols="12"
            md="6"
          >
            <div class="text-body-2 text-medium-emphasis mb-1">
              Email
            </div>
            <div class="text-body-1">
              {{ props.user.email }}
            </div>
          </VCol>

          <!-- Phone (if exists) -->
          <VCol
            v-if="props.user.phone"
            cols="12"
            md="6"
          >
            <div class="text-body-2 text-medium-emphasis mb-1">
              Phone
            </div>
            <div class="text-body-1">
              {{ props.user.phone }}
            </div>
          </VCol>

          <!-- Franchise Name (for franchisors) -->
          <VCol
            v-if="props.user.franchiseName"
            cols="12"
            md="6"
          >
            <div class="text-body-2 text-medium-emphasis mb-1">
              Franchise Name
            </div>
            <div class="text-body-1">
              {{ props.user.franchiseName }}
            </div>
          </VCol>

          <!-- Location (for franchisees) -->
          <VCol
            v-if="props.user.location"
            cols="12"
            md="6"
          >
            <div class="text-body-2 text-medium-emphasis mb-1">
              Location
            </div>
            <div class="text-body-1">
              {{ props.user.location }}
            </div>
          </VCol>

          <!-- City (for sales) -->
          <VCol
            v-if="props.user.city"
            cols="12"
            md="6"
          >
            <div class="text-body-2 text-medium-emphasis mb-1">
              City
            </div>
            <div class="text-body-1">
              {{ props.user.city }}
            </div>
          </VCol>

          <!-- Plan (for franchisors) -->
          <VCol
            v-if="props.user.plan"
            cols="12"
            md="6"
          >
            <div class="text-body-2 text-medium-emphasis mb-1">
              Plan
            </div>
            <VChip
              :color="resolveUserPlanVariant(props.user.plan)"
              size="small"
              label
              class="text-capitalize"
            >
              {{ props.user.plan }}
            </VChip>
          </VCol>

          <!-- Last Login (if exists) -->
          <VCol
            v-if="props.user.lastLogin"
            cols="12"
            md="6"
          >
            <div class="text-body-2 text-medium-emphasis mb-1">
              Last Login
            </div>
            <div class="text-body-1">
              {{ props.user.lastLogin }}
            </div>
          </VCol>

          <!-- Status -->
          <VCol
            cols="12"
            md="6"
          >
            <div class="text-body-2 text-medium-emphasis mb-1">
              Status
            </div>
            <VChip
              :color="resolveUserStatusVariant(props.user.status)"
              size="small"
              label
              class="text-capitalize"
            >
              {{ props.user.status }}
            </VChip>
          </VCol>

          <!-- Joined Date -->
          <VCol
            cols="12"
            md="6"
          >
            <div class="text-body-2 text-medium-emphasis mb-1">
              Joined Date
            </div>
            <div class="text-body-1">
              {{ props.user.joinedDate }}
            </div>
          </VCol>
        </VRow>
      </VCardText>

      <VDivider />

      <VCardActions>
        <VSpacer />
        <VBtn
          variant="outlined"
          @click="handleClose"
        >
          Close
        </VBtn>
        <VBtn
          color="primary"
          @click="handleEdit"
        >
          Edit {{ props.userType }}
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
