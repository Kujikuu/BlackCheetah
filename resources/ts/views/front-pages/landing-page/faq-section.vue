<script setup lang="ts">
import boyWithLogo from '@images/front-pages/landing-page/faq-boy-with-logos.png'

const faqData = [
  {
    question: 'What is BlackCheetah?',
    answer: 'BlackCheetah is a comprehensive franchise management platform designed to streamline operations for franchisors, franchisees, and brokers. It provides tools for lead management, financial tracking, performance analytics, task management, and more—all in one centralized system.',
  },
  {
    question: 'Who can use BlackCheetah?',
    answer: 'BlackCheetah is designed for multiple user roles: Franchisors can manage their entire franchise network, Franchisees can monitor their unit operations and performance, Brokers can track and nurture leads, and Admins can oversee the entire system. Each role has a customized dashboard with relevant features.',
  },
  {
    question: 'How does lead management work?',
    answer: 'Our lead management system allows you to track potential franchisees from first contact through conversion. You can assign leads to brokers, track communication history, set follow-up reminders, monitor lead scores, and generate conversion reports. All lead data is centralized and accessible in real-time.',
  },
  {
    question: 'Can I track multiple franchise locations?',
    answer: 'Yes! BlackCheetah supports unlimited franchise units on our Enterprise plan. You can monitor performance, track financials, manage tasks, and view analytics for each location individually or across your entire network. The system provides both unit-level and network-wide insights.',
  },
  {
    question: 'What financial features are included?',
    answer: 'BlackCheetah includes comprehensive financial management tools: revenue tracking, automated royalty calculations, expense management, financial reporting, transaction history, and customizable financial dashboards. You can generate detailed reports for individual units or your entire franchise network.',
  },
  // {
  //   question: 'Is there mobile access?',
  //   answer: 'Yes, BlackCheetah is fully responsive and accessible from any device. Whether you\'re using a desktop, tablet, or smartphone, you\'ll have full access to all features. This allows franchisees and franchisors to manage operations on the go.',
  // },
  // {
  //   question: 'How secure is my data?',
  //   answer: 'We take security seriously. BlackCheetah uses industry-standard encryption, secure data centers, regular backups, and role-based access controls. All data is encrypted in transit and at rest. We comply with international data protection regulations to ensure your franchise data is safe and secure.',
  // },
  {
    question: 'Do you offer training and support?',
    answer: 'Yes! All plans include onboarding support and access to our comprehensive documentation. Professional and Enterprise plans include priority support via email and chat. Enterprise customers also receive a dedicated account manager and 24/7 phone support to ensure your success.',
  },
]
</script>

<template>
  <div id="faq">
    <VContainer>
      <!-- 👉 Header  -->
      <div class="faq-section">
        <div class="headers d-flex justify-center flex-column align-center">
          <VChip
            label
            color="primary"
            size="small"
            class="mb-4"
          >
            FAQ
          </VChip>
          <h4 class="d-flex align-center text-h4 mb-1 flex-wrap justify-center">
            Frequently Asked
            <div class="position-relative ms-2">
              <div class="section-title">
                questions
              </div>
            </div>
          </h4>
          <p class="text-body-1 mb-0">
            Browse through these FAQs to find answers to commonly asked questions.
          </p>
        </div>
        <VRow>
          <VCol
            cols="12"
            md="5"
          >
            <div class="pt-10 d-flex align-center justify-center h-100">
              <VImg
                :src="boyWithLogo"
                height="330"
                width="330"
              />
            </div>
          </VCol>
          <VCol
            cols="12"
            md="7"
          >
            <VExpansionPanels class="pt-16">
              <VExpansionPanel
                v-for="faq in faqData"
                :key="faq.question"
              >
                <VExpansionPanelTitle>
                  {{ faq.question }}
                </VExpansionPanelTitle>
                <VExpansionPanelText>
                  {{ faq.answer }}
                </VExpansionPanelText>
              </VExpansionPanel>
            </VExpansionPanels>
          </VCol>
        </VRow>
      </div>
    </VContainer>
  </div>
</template>

<style lang="scss" scoped>
.faq-section {
  margin-block: 5.25rem;
}

@media (max-width: 600px) {
  .faq-section {
    margin-block: 4rem;
  }
}

#faq {
  border-radius: 3.75rem 3.75rem 0 0;
  background-color: rgba(var(--v-theme-background));
}

.section-title {
  font-size: 24px;
  font-weight: 800;
  line-height: 36px;
}

.section-title::after {
  position: absolute;
  background: url("../../../assets/images/front-pages/icons/section-title-icon.png") no-repeat left bottom;
  background-size: contain;
  block-size: 100%;
  content: "";
  font-weight: 800;
  inline-size: 130%;
  inset-block-end: 12%;
  inset-inline-start: -12%;
}
</style>
