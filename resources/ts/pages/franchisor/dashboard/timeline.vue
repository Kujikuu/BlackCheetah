<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useDisplay } from 'vuetify'

// 👉 Router composable
const router = useRouter()

// 👉 API composable
const { data: timelineApiData, execute: fetchTimelineData, isFetching: isLoading } = useApi('/v1/franchisor/dashboard/timeline')

// 👉 Vuetify composables
const { smAndDown } = useDisplay()

const selectedFilter = ref('all')

const filters = [
  { title: 'All', value: 'all' },
  { title: 'Completed', value: 'completed' },
  { title: 'Scheduled', value: 'scheduled' },
  { title: 'Overdue', value: 'overdue' },
]

// 👉 Timeline Item Interface
interface TimelineItem {
  id: number
  title: string
  description: string
  week: string
  date: string
  status: string
  icon: string
  color: string
}

// 👉 Stats Interface
interface Stats {
  title: string
  value: string
  icon: string
  color: string
}

// 👉 API Response Interface
interface ApiResponse {
  success: boolean
  data: {
    stats: {
      total_milestones: number
      completed: number
      scheduled: number
      overdue: number
    }
    timeline: Array<{
      id: number
      title: string
      description: string
      week: string
      date: string
      status: string
      icon: string
      created_at: string
    }>
  }
}

// 👉 Reactive data
const timelineItems = ref<TimelineItem[]>([])

const stats = ref<Stats[]>([
  { title: 'Total Milestones', value: '0', icon: 'tabler-flag', color: 'primary' },
  { title: 'Completed', value: '0', icon: 'tabler-circle-check', color: 'success' },
  { title: 'Scheduled', value: '0', icon: 'tabler-calendar', color: 'info' },
  { title: 'Overdue', value: '0', icon: 'tabler-alert-triangle', color: 'error' },
])

// 👉 Helper function to determine color based on status
const getStatusColor = (status: string): string => {
  switch (status.toLowerCase()) {
    case 'completed':
      return 'success'
    case 'scheduled':
      return 'info'
    case 'overdue':
      return 'error'
    default:
      return 'primary'
  }
}

// 👉 Helper function to format date
const formatDate = (dateString: string): string => {
  const date = new Date(dateString)
  const now = new Date()
  const diffTime = now.getTime() - date.getTime()
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))

  if (diffDays === 0)
    return 'Today'
  if (diffDays === 1)
    return '1 day ago'
  if (diffDays > 1 && diffDays <= 7)
    return `${diffDays} days ago`
  if (diffDays > 7 && diffDays <= 14)
    return `${Math.ceil(diffDays / 7)} week ago`
  if (diffDays > 14)
    return `${Math.ceil(diffDays / 7)} weeks ago`
  if (diffDays < 0) {
    const futureDays = Math.abs(diffDays)
    if (futureDays === 1)
      return 'In 1 day'
    if (futureDays <= 7)
      return `In ${futureDays} days`

    return `In ${Math.ceil(futureDays / 7)} weeks`
  }

  return dateString
}

// 👉 Watch for API data changes
watch(timelineApiData, newData => {
  const apiData = newData as ApiResponse
  if (apiData?.success && apiData?.data) {
    const data = apiData.data

    // Update timeline items with null/undefined checks
    timelineItems.value = Array.isArray(data.timeline)
      ? data.timeline.map(item => ({
        id: item.id,
        title: item.title,
        description: item.description,
        week: item.week,
        date: item.date || formatDate(item.created_at),
        status: item.status,
        icon: item.icon || 'tabler-calendar',
        color: getStatusColor(item.status),
      }))
      : []

    // Update stats with proper null/undefined checks
    if (data.stats) {
      stats.value = [
        { title: 'Total Milestones', value: (data.stats.total_milestones || 0).toLocaleString(), icon: 'tabler-flag', color: 'primary' },
        { title: 'Completed', value: (data.stats.completed || 0).toLocaleString(), icon: 'tabler-circle-check', color: 'success' },
        { title: 'Scheduled', value: (data.stats.scheduled || 0).toLocaleString(), icon: 'tabler-calendar', color: 'info' },
        { title: 'Overdue', value: (data.stats.overdue || 0).toLocaleString(), icon: 'tabler-alert-triangle', color: 'error' },
      ]
    }
  }
}, { immediate: true })

const filteredTimelineItems = computed(() => {
  if (selectedFilter.value === 'all')
    return timelineItems.value

  return timelineItems.value.filter(item => item.status === selectedFilter.value)
})

// 👉 Action handlers
const viewDetails = (item: TimelineItem) => {
  // Navigate to appropriate detail page based on item type
  if (item.description.includes('Lead')) {
    // Navigate to leads page with specific lead
    router.push('/franchisor/dashboard/leads')
  }
  else if (item.description.includes('Task')) {
    // Navigate to operations/tasks page
    router.push('/franchisor/dashboard/operations')
  }
  else if (item.description.includes('Technical')) {
    // Navigate to technical requests page
    router.push('/franchisor/dashboard/operations')
  }
}

const takeAction = (item: TimelineItem) => {
  // Show action dialog or navigate to action page
  if (item.description.includes('Lead'))
    router.push('/franchisor/dashboard/leads')
  else if (item.description.includes('Task'))
    router.push('/franchisor/dashboard/operations')
  else
    router.push('/franchisor/dashboard/operations')
}

const reschedule = (item: TimelineItem) => {
  // Show reschedule dialog or navigate to reschedule page
  if (item.description.includes('Lead'))
    router.push('/franchisor/dashboard/leads')
  else if (item.description.includes('Task'))
    router.push('/franchisor/dashboard/operations')
  else
    router.push('/franchisor/dashboard/operations')
}

// 👉 Fetch data on component mount
onMounted(() => {
  fetchTimelineData()
})
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
          :density="smAndDown ? 'compact' : 'default'"
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
                  @click="viewDetails(item)"
                >
                  View Details
                </VBtn>
                <VBtn
                  v-if="item.status === 'overdue'"
                  variant="tonal"
                  color="error"
                  size="small"
                  @click="takeAction(item)"
                >
                  Take Action
                </VBtn>
                <VBtn
                  v-if="item.status === 'scheduled'"
                  variant="tonal"
                  color="info"
                  size="small"
                  @click="reschedule(item)"
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
