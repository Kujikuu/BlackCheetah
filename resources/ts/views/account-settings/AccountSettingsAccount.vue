<script lang="ts" setup>
import type { UserProfile } from '@/services/api/account-settings'
import { accountSettingsApi } from '@/services/api/account-settings'

const refInputEl = ref<HTMLElement>()
const isLoading = ref(false)
const isSaving = ref(false)
const accountData = ref<UserProfile | null>(null)
const accountDataLocal = ref({
  avatarImg: '',
  name: '',
  email: '',
  role: '',
  phone: '',
  timezone: '(GMT+03:00) Riyadh',
})

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

// changeAvatar function
const changeAvatar = async (file: Event) => {
  const { files } = file.target as HTMLInputElement

  if (files && files.length) {
    try {
      isSaving.value = true

      // Upload to server
      const response = await accountSettingsApi.uploadAvatar(files[0])
      if (response.success) {
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
      }
    }
    catch (error) {
      console.error('Error uploading avatar:', error)
    }
    finally {
      isSaving.value = false
    }
  }
}

// reset avatar image
const resetAvatar = async () => {
  try {
    isSaving.value = true
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
  }
  catch (error) {
    console.error('Error deleting avatar:', error)
  }
  finally {
    isSaving.value = false
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
        <VCardText class="d-flex">
          <!-- Avatar -->
          <VAvatar rounded size="100" class="me-6" :image="accountDataLocal.avatarImg">
            <span v-if="!accountDataLocal.avatarImg" class="text-5xl font-weight-medium">
              {{ accountDataLocal.name.charAt(0).toUpperCase() }}
            </span>
          </VAvatar>

          <!-- Upload Photo -->
          <div class="d-flex flex-column justify-center gap-4">
            <div class="d-flex flex-wrap gap-2">
              <VBtn color="primary" :loading="isSaving" :disabled="isLoading" @click="refInputEl?.click()">
                <VIcon icon="tabler-cloud-upload" class="d-sm-none" />
                <span class="d-none d-sm-block">Upload new photo</span>
              </VBtn>

              <input ref="refInputEl" type="file" name="file" accept=".jpeg,.png,.jpg,GIF" hidden @input="changeAvatar">

              <VBtn type="reset" color="secondary" variant="outlined" :loading="isSaving" :disabled="isLoading"
                @click="resetAvatar">
                Reset
              </VBtn>
            </div>

            <p class="text-body-1 mb-0">
              Allowed JPG, GIF or PNG. Max size of 800K
            </p>
          </div>
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
