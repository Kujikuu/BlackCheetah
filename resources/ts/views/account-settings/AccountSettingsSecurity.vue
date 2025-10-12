<script lang="ts" setup>
const isCurrentPasswordVisible = ref(false)
const isNewPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)
const currentPassword = ref('')
const newPassword = ref('')
const confirmPassword = ref('')

const passwordRequirements = [
  'Minimum 8 characters long - the more, the better',
  'At least one lowercase character',
  'At least one uppercase character',
  'At least one number, symbol, or whitespace character',
]

const onFormSubmit = () => {
  // Validate passwords match
  if (newPassword.value !== confirmPassword.value) {
    console.error('Passwords do not match')

    return
  }

  console.log('Password change requested')

  // Implement API call here

  // Reset form
  currentPassword.value = ''
  newPassword.value = ''
  confirmPassword.value = ''
}

const resetForm = () => {
  currentPassword.value = ''
  newPassword.value = ''
  confirmPassword.value = ''
}
</script>

<template>
  <VRow>
    <!-- Change Password -->
    <VCol cols="12">
      <VCard title="Change Password">
        <VCardText>
          <VAlert
            variant="tonal"
            color="warning"
            class="mb-6"
          >
            <VAlertTitle class="mb-1">
              Ensure that these requirements are met
            </VAlertTitle>
            <span>Minimum 8 characters long, uppercase & symbol</span>
          </VAlert>

          <VForm @submit.prevent="onFormSubmit">
            <VRow>
              <!-- Current Password -->
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="currentPassword"
                  :type="isCurrentPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isCurrentPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  label="Current Password"
                  placeholder="············"
                  @click:append-inner="isCurrentPasswordVisible = !isCurrentPasswordVisible"
                />
              </VCol>
            </VRow>

            <VRow>
              <!-- New Password -->
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="newPassword"
                  :type="isNewPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isNewPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  label="New Password"
                  placeholder="············"
                  @click:append-inner="isNewPasswordVisible = !isNewPasswordVisible"
                />
              </VCol>

              <!-- Confirm New Password -->
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="confirmPassword"
                  :type="isConfirmPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isConfirmPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  label="Confirm New Password"
                  placeholder="············"
                  @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
                />
              </VCol>

              <!-- Password Requirements -->
              <VCol cols="12">
                <h6 class="text-h6 text-medium-emphasis mb-4">
                  Password Requirements:
                </h6>

                <ul class="d-flex flex-column gap-y-2">
                  <li
                    v-for="item in passwordRequirements"
                    :key="item"
                    class="d-flex align-center"
                  >
                    <div>
                      <VIcon
                        size="8"
                        icon="tabler-circle"
                        class="me-2"
                      />
                      <span class="text-base">{{ item }}</span>
                    </div>
                  </li>
                </ul>
              </VCol>

              <!-- Action Buttons -->
              <VCol
                cols="12"
                class="d-flex flex-wrap gap-4"
              >
                <VBtn type="submit">
                  Save changes
                </VBtn>

                <VBtn
                  type="reset"
                  color="secondary"
                  variant="outlined"
                  @click="resetForm"
                >
                  Reset
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
