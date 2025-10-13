<script lang="ts" setup>
import type { UserProfile } from '@/services/api/account-settings'
import { accountSettingsApi } from '@/services/api/account-settings'

const refInputEl = ref<HTMLElement>()
const isLoading = ref(false)
const isSaving = ref(false)
const isUploadingAvatar = ref(false)
const accountData = ref<UserProfile | null>(null)
const accountDataLocal = ref({
  avatarImg: '',
  name: '',
  email: '',
  role: '',
  phone: '',
  timezone: '(GMT+03:00) Riyadh',
})
const uploadStatus = ref<{ type: 'success' | 'error' | null; message: string }>({ type: null, message: '' })

const timezones = [
  '(GMT+03:00) Riyadh',
  '(GMT+03:00) Kuwait',
  '(GMT+03:00) Baghdad',
  '(GMT+02:00) Cairo',
  '(GMT+01:00) Amsterdam',
  '(GMT+00:00) London',
  '(GMT-05:00) Eastern Time (US & Canada)',
  '(GMT-08:00) Pacific Time (US & Canada)',
]

// Load user profile
const loadProfile = async () => {
  try {
    isLoading.value = true
    const response = await accountSettingsApi.getProfile()
    if (response.success) {
      accountData.value = response.data
      accountDataLocal.value = {
        avatarImg: response.data.avatar || '',
        name: response.data.name,
        email: response.data.email,
        role: getRoleDisplayName(response.data.role),
        phone: response.data.phone || '',
        timezone: response.data.preferences?.timezone || '(GMT+03:00) Riyadh',
      }
    }
  }
  catch (error) {
    console.error('Error loading profile:', error)
  }
  finally {
    isLoading.value = false
  }
}

// Get role display name
const getRoleDisplayName = (role: string): string => {
  const roleMap: Record<string, string> = {
    'admin': 'Administrator',
    'franchisor': 'Franchisor',
    'franchisee': 'Franchisee',
    'unit_manager': 'Unit Manager',
    'sales_associate': 'Sales Associate',
    'employee': 'Employee',
  }
  return roleMap[role] || role
}

const resetForm = () => {
  if (accountData.value) {
    accountDataLocal.value = {
      avatarImg: accountData.value.avatar || '',
      name: accountData.value.name,
      email: accountData.value.email,
      role: getRoleDisplayName(accountData.value.role),
      phone: accountData.value.phone || '',
      timezone: accountData.value.preferences?.timezone || '(GMT+03:00) Riyadh',
    }
  }
}

// changeAvatar function with instant preview
const changeAvatar = async (file: Event) => {
  const { files } = file.target as HTMLInputElement

  if (files && files.length) {
    const selectedFile = files[0]

    // Validate file size (800KB max)
    if (selectedFile.size > 800 * 1024) {
      uploadStatus.value = { type: 'error', message: 'File size must be less than 800KB' }
      setTimeout(() => uploadStatus.value = { type: null, message: '' }, 5000)
      return
    }

    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif']
    if (!allowedTypes.includes(selectedFile.type)) {
      uploadStatus.value = { type: 'error', message: 'Only JPG, PNG, and GIF files are allowed' }
      setTimeout(() => uploadStatus.value = { type: null, message: '' }, 5000)
      return
    }

    try {
      // Show instant preview using FileReader
      const reader = new FileReader()
      reader.onload = (e) => {
        accountDataLocal.value.avatarImg = e.target?.result as string
      }
      reader.readAsDataURL(selectedFile)

      // Upload to server in background
      isUploadingAvatar.value = true
      uploadStatus.value = { type: null, message: 'Uploading...' }

      const response = await accountSettingsApi.uploadAvatar(selectedFile)
      if (response.success) {
        // Update with server URL
        accountDataLocal.value.avatarImg = response.data.avatar
        if (accountData.value) {
          accountData.value.avatar = response.data.avatar
        }

        // Update cookie - create new object to trigger reactivity
        const userData = useCookie<any>('userData')
        if (userData.value) {
          userData.value = {
            ...userData.value,
            avatar: response.data.avatar,
          }
        }

        uploadStatus.value = { type: 'success', message: 'Avatar uploaded successfully!' }
        setTimeout(() => uploadStatus.value = { type: null, message: '' }, 3000)
      }
    }
    catch (error: any) {
      console.error('Error uploading avatar:', error)
      uploadStatus.value = {
        type: 'error',
        message: error.response?.data?.message || 'Failed to upload avatar'
      }
      // Reload original avatar on error
      if (accountData.value?.avatar) {
        accountDataLocal.value.avatarImg = accountData.value.avatar
      }
      setTimeout(() => uploadStatus.value = { type: null, message: '' }, 5000)
    }
    finally {
      isUploadingAvatar.value = false
    }
  }
}

// reset avatar image
const resetAvatar = async () => {
  try {
    isUploadingAvatar.value = true
    uploadStatus.value = { type: null, message: 'Removing avatar...' }

    await accountSettingsApi.deleteAvatar()
    accountDataLocal.value.avatarImg = ''
    if (accountData.value) {
      accountData.value.avatar = null
    }

    // Update cookie - create new object to trigger reactivity
    const userData = useCookie<any>('userData')
    if (userData.value) {
      userData.value = {
        ...userData.value,
        avatar: null,
      }
    }

    uploadStatus.value = { type: 'success', message: 'Avatar removed successfully!' }
    setTimeout(() => uploadStatus.value = { type: null, message: '' }, 3000)
  }
  catch (error: any) {
    console.error('Error deleting avatar:', error)
    uploadStatus.value = {
      type: 'error',
      message: error.response?.data?.message || 'Failed to remove avatar'
    }
    setTimeout(() => uploadStatus.value = { type: null, message: '' }, 5000)
  }
  finally {
    isUploadingAvatar.value = false
  }
}

const onFormSubmit = async () => {
  try {
    isSaving.value = true

    const response = await accountSettingsApi.updateProfile({
      name: accountDataLocal.value.name,
      phone: accountDataLocal.value.phone,
      preferences: {
        timezone: accountDataLocal.value.timezone,
      },
    })

    if (response.success) {
      accountData.value = response.data

      // Update cookie - create new object to trigger reactivity
      const userData = useCookie<any>('userData')
      if (userData.value) {
        userData.value = {
          ...userData.value,
          name: response.data.name,
          fullName: response.data.name,
        }
      }
    }
  }
  catch (error) {
    console.error('Error updating profile:', error)
  }
  finally {
    isSaving.value = false
  }
}

// Load profile on mount
onMounted(() => {
  loadProfile()
})
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard title="Profile Details" :loading="isLoading">
        <VCardText class="d-flex flex-column gap-4">
          <div class="d-flex align-center">
            <!-- Avatar with loading overlay -->
            <div class="position-relative me-6">
              <VAvatar rounded size="100" :image="accountDataLocal.avatarImg">
                <span v-if="!accountDataLocal.avatarImg" class="text-5xl font-weight-medium">
                  {{ accountDataLocal.name.charAt(0).toUpperCase() }}
                </span>
              </VAvatar>
              <!-- Loading overlay -->
              <div v-if="isUploadingAvatar"
                class="position-absolute top-0 start-0 w-100 h-100 d-flex align-center justify-center"
                style="background: rgba(0,0,0,0.5); border-radius: 8px;">
                <VProgressCircular indeterminate color="white" size="32" />
              </div>
            </div>

            <!-- Upload Photo -->
            <div class="d-flex flex-column justify-center gap-4">
              <div class="d-flex flex-wrap gap-2">
                <VBtn color="primary" :loading="isUploadingAvatar" :disabled="isLoading || isUploadingAvatar"
                  @click="refInputEl?.click()">
                  <VIcon icon="tabler-cloud-upload" class="d-sm-none" />
                  <span class="d-none d-sm-block">Upload new photo</span>
                </VBtn>

                <input ref="refInputEl" type="file" name="file" accept=".jpeg,.png,.jpg,.gif" hidden
                  @input="changeAvatar">

                <VBtn type="reset" color="secondary" variant="outlined" :loading="isUploadingAvatar"
                  :disabled="isLoading || isUploadingAvatar || !accountDataLocal.avatarImg" @click="resetAvatar">
                  Reset
                </VBtn>
              </div>

              <p class="text-body-1 mb-0">
                Allowed JPG, GIF or PNG. Max size of 800K
              </p>
            </div>
          </div>

          <!-- Upload Status Alert -->
          <VAlert v-if="uploadStatus.message" :type="uploadStatus.type || 'info'" variant="tonal" closable
            @click:close="uploadStatus = { type: null, message: '' }">
            {{ uploadStatus.message }}
          </VAlert>
        </VCardText>

        <VDivider />

        <VCardText>
          <!-- Form -->
          <VForm class="mt-6">
            <VRow>
              <!-- Full Name -->
              <VCol cols="12" md="12">
                <AppTextField v-model="accountDataLocal.name" label="Full Name" placeholder="John Doe" />
              </VCol>

              <!-- Email -->
              <VCol cols="12" md="6">
                <AppTextField v-model="accountDataLocal.email" label="Email" type="email"
                  placeholder="admin@example.com" readonly persistent-hint />
              </VCol>

              <!-- Role -->
              <VCol cols="12" md="6">
                <AppTextField v-model="accountDataLocal.role" label="Role" placeholder="Administrator" readonly />
              </VCol>

              <!-- Phone -->
              <VCol cols="12" md="6">
                <AppTextField v-model="accountDataLocal.phone" label="Phone Number" placeholder="+966 50 123 4567" />
              </VCol>

              <!-- Timezone -->
              <VCol cols="12" md="6">
                <AppSelect v-model="accountDataLocal.timezone" label="Timezone" :items="timezones"
                  placeholder="Select Timezone" />
              </VCol>

              <!-- Form Actions -->
              <VCol cols="12" class="d-flex flex-wrap gap-4">
                <VBtn :loading="isSaving" :disabled="isLoading" @click="onFormSubmit">
                  Save changes
                </VBtn>

                <VBtn color="secondary" variant="outlined" type="reset" :disabled="isLoading || isSaving"
                  @click.prevent="resetForm">
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
