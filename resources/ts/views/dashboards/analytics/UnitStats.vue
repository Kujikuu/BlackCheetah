<script setup lang="ts">
interface Props {
  totalUnits?: number
  activeUnits?: number
  inactiveUnits?: number
}

const props = withDefaults(defineProps<Props>(), {
  totalUnits: 0,
  activeUnits: 0,
  inactiveUnits: 0,
})

const activePercentage = computed(() => {
  if (props.totalUnits === 0) return 0
  return Math.round((props.activeUnits / props.totalUnits) * 100)
})

const inactivePercentage = computed(() => {
  if (props.totalUnits === 0) return 0
  return Math.round((props.inactiveUnits / props.totalUnits) * 100)
})
</script>

<template>
  <VCard>
    <VCardText>
      <div class="d-flex align-center justify-space-between">
        <div class="text-body-1">
          Unit Overview
        </div>
        <div class="text-success font-weight-medium">
          {{ activePercentage }}% Active
        </div>
      </div>
      <h4 class="text-h4">
        {{ totalUnits }}
      </h4>
    </VCardText>

    <VCardText>
      <VRow no-gutters>
        <VCol cols="5">
          <div class="py-2">
            <div class="d-flex align-center mb-3">
              <VAvatar
                color="success"
                variant="tonal"
                :size="24"
                rounded
                class="me-2"
              >
                <VIcon
                  size="18"
                  icon="tabler-building-store"
                />
              </VAvatar>
              <span>Active</span>
            </div>
            <h5 class="text-h5">
              {{ activePercentage }}%
            </h5>
            <div class="text-body-2 text-disabled">
              {{ activeUnits }} units
            </div>
          </div>
        </VCol>

        <VCol cols="2">
          <div class="d-flex flex-column align-center justify-center h-100">
            <VDivider
              vertical
              class="mx-auto"
            />

            <VAvatar
              size="24"
              color="rgba(var(--v-theme-on-surface), var(--v-hover-opacity))"
              class="my-2"
            >
              <div class="text-overline text-disabled">
                VS
              </div>
            </VAvatar>

            <VDivider
              vertical
              class="mx-auto"
            />
          </div>
        </VCol>

        <VCol
          cols="5"
          class="text-end"
        >
          <div class="py-2">
            <div class="d-flex align-center justify-end mb-3">
              <span class="me-2">Inactive</span>
              <VAvatar
                color="error"
                variant="tonal"
                :size="24"
                rounded
              >
                <VIcon
                  size="18"
                  icon="tabler-building-off"
                />
              </VAvatar>
            </div>
            <h5 class="text-h5">
              {{ inactivePercentage }}%
            </h5>
            <div class="text-body-2 text-disabled">
              {{ inactiveUnits }} units
            </div>
          </div>
        </VCol>
      </VRow>

      <div class="mt-6">
        <VProgressLinear
          :model-value="activePercentage"
          color="success"
          height="10"
          bg-color="error"
          :rounded-bar="false"
          rounded
        />
      </div>
    </VCardText>
  </VCard>
</template>

