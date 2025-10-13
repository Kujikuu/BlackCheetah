<script lang="ts" setup>
import { accountSettingsApi } from '@/services/api/account-settings'

const isCurrentPasswordVisible = ref(false)
const isNewPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)
const currentPassword = ref('')
const newPassword = ref('')
const confirmPassword = ref('')
const isSaving = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const passwordRequirements = [
  'Minimum 8 characters long - the more, the better',
  'At least one lowercase character',
  'At least one uppercase character',
  'At least one number, symbol, or whitespace character',
]

const onFormSubmit = async () => {
  errorMessage.value = ''
  successMessage.value = ''

  // Validate passwords match
  if (newPassword.value !== confirmPassword.value) {
    errorMessage.value = 'New passwords do not match'
    return
  }

  // Validate password length
  if (newPassword.value.length < 8) {
    errorMessage.value = 'Password must be at least 8 characters long'
    return
  }

  try {
    isSaving.value = true

    await accountSettingsApi.updatePassword({
      current_password: currentPassword.value,
      password: newPassword.value,
      password_confirmation: confirmPassword.value,
    })

    successMessage.value = 'Password updated successfully'

    // Reset form
    currentPassword.value = ''
    newPassword.value = ''
    confirmPassword.value = ''
  }
  catch (error: any) {
    if (error.response?.data?.message) {
      errorMessage.value = error.response.data.message
    } else {
      errorMessage.value = 'Failed to update password. Please try again.'
    }
    console.error('Error updating password:', error)
  }
  finally {
    isSaving.value = false
  }
}

const resetForm = () => {
  currentPassword.value = ''
  newPassword.value = ''
  confirmPassword.value = ''
  errorMessage.value = ''
  successMessage.value = ''
}
</script>

<template>
  <VRow>
    <!-- Change Password -->
    <VCol cols="12">
      <VCard title="Change Password">
        <VCardText>
          <!-- Success Message -->
          <VAlert v-if="successMessage" variant="tonal" color="success" class="mb-6" closable
            @click:close="successMessage = ''">
            {{ successMessage }}
          </VAlert>

          <!-- Error Message -->
          <VAlert v-if="errorMessage" variant="tonal" color="error" class="mb-6" closable
            @click:close="errorMessage = ''">
            {{ errorMessage }}
          </VAlert>

          <VAlert variant="tonal" color="warning" class="mb-6">
            <VAlertTitle class="mb-1">
              Ensure that these requirements are met
            </VAlertTitle>
            <span>Minimum 8 characters long, uppercase & symbol</span>
          </VAlert>

          <VForm @submit.prevent="onFormSubmit">
            <VRow>
              <!-- Current Password -->
              <VCol cols="12" md="6">
                <AppTextField v-model="currentPassword" :type="isCurrentPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isCurrentPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  label="Current Password" placeholder="············"
                  @click:append-inner="isCurrentPasswordVisible = !isCurrentPasswordVisible" />
              </VCol>
            </VRow>

            <VRow>
              <!-- New Password -->
              <VCol cols="12" md="6">
                <AppTextField v-model="newPassword" :type="isNewPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isNewPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'" label="New Password"
                  placeholder="············" @click:append-inner="isNewPasswordVisible = !isNewPasswordVisible" />
              </VCol>

              <!-- Confirm New Password -->
              <VCol cols="12" md="6">
                <AppTextField v-model="confirmPassword" :type="isConfirmPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isConfirmPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  label="Confirm New Password" placeholder="············"
                  @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible" />
              </VCol>

              <!-- Password Requirements -->
              <VCol cols="12">
                <h6 class="text-h6 text-medium-emphasis mb-4">
                  Password Requirements:
                </h6>

                <ul class="d-flex flex-column gap-y-2">
                  <li v-for="item in passwordRequirements" :key="item" class="d-flex align-center">
                    <div>
                      <VIcon size="8" icon="tabler-circle" class="me-2" />
                      <span class="text-base">{{ item }}</span>
                    </div>
                  </li>
                </ul>
              </VCol>

              <!-- Action Buttons -->
              <VCol cols="12" class="d-flex flex-wrap gap-4">
                <VBtn type="submit" :loading="isSaving">
                  Save changes
                </VBtn>

                <VBtn type="reset" color="secondary" variant="outlined" :disabled="isSaving" @click="resetForm">
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
