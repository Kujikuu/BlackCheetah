<script setup lang="ts">
const featuresData = [
  {
    title: 'Lead Management',
    desc: 'Track and nurture franchise leads from initial contact to conversion with powerful CRM tools.',
    icon: 'tabler-users-group',
  },
  {
    title: 'Unit Operations',
    desc: 'Monitor and manage all franchise units with real-time performance tracking and operational insights.',
    icon: 'tabler-building-store',
  },
  {
    title: 'Financial Overview',
    desc: 'Complete financial management including revenue tracking, royalty calculations, and financial reporting.',
    icon: 'tabler-report-money',
  },
  {
    title: 'Task Management',
    desc: 'Assign and track tasks across your franchise network ensuring smooth operations and accountability.',
    icon: 'tabler-checkbox',
  },
  {
    title: 'Performance Analytics',
    desc: 'Comprehensive analytics and KPIs to measure franchise performance and identify growth opportunities.',
    icon: 'tabler-chart-line',
  },
  {
    title: 'Multi-Role Support',
    desc: 'Purpose-built dashboards for admins, franchisors, franchisees, and brokers with role-specific features.',
    icon: 'tabler-user-shield',
  },
]
</script>

<template>
  <VContainer id="features">
    <div class="feature-cards">
      <div class="headers d-flex justify-center flex-column align-center mb-12">
        <VChip
          label
          color="primary"
          class="mb-4"
          size="small"
        >
          Platform Features
        </VChip>
        <div class="d-flex text-h4 mb-1 align-center flex-wrap justify-center">
          <div class="position-relative me-2">
            <div class="section-title">
              Everything you need
            </div>
          </div>
          to manage your franchise
        </div>
        <p class="text-body-1 mb-0">
          Powerful tools designed specifically for franchise management and growth.
        </p>
      </div>
      <VRow>
        <VCol
          v-for="(data, index) in featuresData"
          :key="index"
          cols="12"
          md="4"
          sm="6"
        >
          <div class="d-flex flex-column align-center justify-center gap-4 mx-auto">
            <VIcon
              :icon="data.icon"
              size="64"
              color="primary"
            />
            <div class="text-center">
              <h5 class="text-h5 mb-2">
                {{ data.title }}
              </h5>
              <p
                class="text-body-1 mb-6"
                style="max-inline-size: 310px;"
              >
                {{ data.desc }}
              </p>
            </div>
          </div>
        </VCol>
      </VRow>
    </div>
  </VContainer>
</template>

<style lang="scss" scoped>
.feature-cards {
  margin-block: 3.75rem;
}

.section-title::after {
  position: absolute;
  background: url("@images/front-pages/icons/section-title-icon.png") no-repeat left bottom;
  background-size: contain;
  block-size: 100%;
  content: "";
  inline-size: 120%;
  inset-block-end: 0;
  inset-inline-start: -12%;
}

.section-title {
  font-size: 24px;
  font-weight: 800;
  line-height: 36px;
}

.feature {
  .v-avatar {
    &.v-avatar--variant-outlined {
      border: 2px solid rgba(var(--v-theme-primary), 0.32);

      &:hover {
        background: rgba(var(--v-theme-primary), 0.16);
        cursor: pointer;
      }
    }
  }
}
</style>
