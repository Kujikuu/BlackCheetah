<script setup lang="ts">
const selectedFilter = ref('all')

const filters = [
  { title: 'All', value: 'all' },
  { title: 'Completed', value: 'completed' },
  { title: 'Scheduled', value: 'scheduled' },
  { title: 'Overdue', value: 'overdue' },
]

// Mock timeline data - Replace with actual API call
const timelineItems = ref([
  {
    id: 1,
    title: 'Store Opening - Downtown Location',
    description: 'Grand opening ceremony completed with 200+ attendees. All permits and licenses verified.',
    week: 'Week 1',
    date: '2 weeks ago',
    status: 'completed',
    icon: 'tabler-building-store',
    color: 'success',
  },
  {
    id: 2,
    title: 'Staff Training Program - Phase 1',
    description: 'Initial training for 15 staff members covering customer service, POS systems, and brand standards.',
    week: 'Week 2',
    date: '1 week ago',
    status: 'completed',
    icon: 'tabler-school',
    color: 'success',
  },
  {
    id: 3,
    title: 'Marketing Campaign Launch',
    description: 'Social media campaign and local advertising initiated. Target reach: 50,000 potential customers.',
    week: 'Week 3',
    date: '3 days ago',
    status: 'completed',
    icon: 'tabler-speakerphone',
    color: 'success',
  },
  {
    id: 4,
    title: 'Inventory Management System Setup',
    description: 'Implementation of new inventory tracking system across all franchise locations.',
    week: 'Week 4',
    date: 'Today',
    status: 'scheduled',
    icon: 'tabler-package',
    color: 'info',
  },
  {
    id: 5,
    title: 'Quality Assurance Inspection',
    description: 'Scheduled inspection of all operational standards and compliance requirements.',
    week: 'Week 5',
    date: 'In 4 days',
    status: 'scheduled',
    icon: 'tabler-clipboard-check',
    color: 'info',
  },
  {
    id: 6,
    title: 'Financial Review Meeting',
    description: 'Quarterly financial performance review with all franchisees. Budget planning for Q2.',
    week: 'Week 6',
    date: 'In 10 days',
    status: 'scheduled',
    icon: 'tabler-report-money',
    color: 'info',
  },
  {
    id: 7,
    title: 'Equipment Maintenance - Mall Location',
    description: 'Scheduled maintenance was missed. Requires immediate attention to prevent operational issues.',
    week: 'Week 2',
    date: '5 days overdue',
    status: 'overdue',
    icon: 'tabler-tools',
    color: 'error',
  },
  {
    id: 8,
    title: 'License Renewal - City Center Store',
    description: 'Business license renewal deadline approaching. Documentation pending submission.',
    week: 'Week 3',
    date: '2 days overdue',
    status: 'overdue',
    icon: 'tabler-file-certificate',
    color: 'error',
  },
])

const filteredTimelineItems = computed(() => {
  if (selectedFilter.value === 'all')
    return timelineItems.value
  return timelineItems.value.filter(item => item.status === selectedFilter.value)
})

const stats = ref([
  { title: 'Total Milestones', value: '48', icon: 'tabler-flag', color: 'primary' },
  { title: 'Completed', value: '32', icon: 'tabler-circle-check', color: 'success' },
  { title: 'Scheduled', value: '12', icon: 'tabler-calendar', color: 'info' },
  { title: 'Overdue', value: '4', icon: 'tabler-alert-triangle', color: 'error' },
])
</script>

<template>
  <section>
    <!-- 👉 Stats Cards -->
    <VRow class="mb-6">
      <VCol
        v-for="stat in stats"
        :key="stat.title"
        cols="12"
        sm="6"
        md="3"
      >
        <VCard>
          <VCardText>
            <div class="d-flex justify-space-between align-center">
              <div>
                <div class="text-body-1 text-high-emphasis mb-1">
                  {{ stat.title }}
                </div>
                <h4 class="text-h4">
                  {{ stat.value }}
                </h4>
              </div>
              <VAvatar
                :color="stat.color"
                variant="tonal"
                rounded
                size="42"
              >
                <VIcon
                  :icon="stat.icon"
                  size="26"
                />
              </VAvatar>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- 👉 Timeline Card -->
    <VCard>
      <VCardItem>
        <VCardTitle>Development Timeline</VCardTitle>
        <template #append>
          <div style="inline-size: 10rem;">
            <AppSelect
              v-model="selectedFilter"
              :items="filters"
              placeholder="Filter"
            />
          </div>
        </template>
      </VCardItem>

      <VCardText>
        <VTimeline
          align="start"
          line-inset="19"
          truncate-line="start"
          justify="center"
          :density="$vuetify.display.smAndDown ? 'compact' : 'default'"
          class="mt-4"
        >
          <VTimelineItem
            v-for="item in filteredTimelineItems"
            :key="item.id"
            fill-dot
            size="small"
          >
            <template #opposite>
              <div class="d-flex flex-column">
                <span class="app-timeline-meta text-sm font-weight-medium">
                  {{ item.week }}
                </span>
                <span class="app-timeline-meta text-xs">
                  {{ item.date }}
                </span>
              </div>
            </template>
            <template #icon>
              <div class="v-timeline-avatar-wrapper rounded-circle">
                <VAvatar
                  size="32"
                  :color="item.color"
                  variant="tonal"
                >
                  <VIcon
                    :icon="item.icon"
                    size="20"
                  />
                </VAvatar>
              </div>
            </template>
            <VCard class="mb-6 mt-n4">
              <VCardItem class="pb-4">
                <VCardTitle class="d-flex align-center justify-space-between">
                  <span>{{ item.title }}</span>
                  <VChip
                    :color="item.color"
                    size="small"
                    label
                    class="text-capitalize"
                  >
                    {{ item.status }}
                  </VChip>
                </VCardTitle>
              </VCardItem>
              <VCardText>
                <p class="app-timeline-text mb-0">
                  {{ item.description }}
                </p>
              </VCardText>
              <VCardActions>
                <VBtn
                  variant="tonal"
                  size="small"
                >
                  View Details
                </VBtn>
                <VBtn
                  v-if="item.status === 'overdue'"
                  variant="tonal"
                  color="error"
                  size="small"
                >
                  Take Action
                </VBtn>
                <VBtn
                  v-if="item.status === 'scheduled'"
                  variant="tonal"
                  color="info"
                  size="small"
                >
                  Reschedule
                </VBtn>
              </VCardActions>
            </VCard>
          </VTimelineItem>
        </VTimeline>
      </VCardText>
    </VCard>
  </section>
</template>

<style lang="scss" scoped>
.v-timeline-avatar-wrapper {
  background-color: rgb(var(--v-theme-surface));
}
</style>
