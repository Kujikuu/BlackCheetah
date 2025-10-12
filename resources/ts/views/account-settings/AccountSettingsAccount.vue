<script lang="ts" setup>
const accountData = {
  avatarImg: '',
  firstName: 'Admin',
  lastName: 'User',
  email: 'admin@blackcheetah.com',
  role: 'Administrator',
  phone: '+966 50 123 4567',
  timezone: '(GMT+03:00) Riyadh',
}

const refInputEl = ref<HTMLElement>()
const accountDataLocal = ref(structuredClone(accountData))

const resetForm = () => {
  accountDataLocal.value = structuredClone(accountData)
}

// changeAvatar function
const changeAvatar = (file: Event) => {
  const fileReader = new FileReader()
  const { files } = file.target as HTMLInputElement

  if (files && files.length) {
    fileReader.readAsDataURL(files[0])
    fileReader.onload = () => {
      if (typeof fileReader.result === 'string')
        accountDataLocal.value.avatarImg = fileReader.result
    }
  }
}

// reset avatar image
const resetAvatar = () => {
  accountDataLocal.value.avatarImg = accountData.avatarImg
}

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

const onFormSubmit = () => {
  console.log('Account data updated:', accountDataLocal.value)

  // Implement API call here
}
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard title="Profile Details">
        <VCardText class="d-flex">
          <!-- Avatar -->
          <VAvatar
            rounded
            size="100"
            class="me-6"
            :image="accountDataLocal.avatarImg"
          >
            <span
              v-if="!accountDataLocal.avatarImg"
              class="text-5xl font-weight-medium"
            >
              {{ avatarText(`${accountDataLocal.firstName} ${accountDataLocal.lastName}`) }}
            </span>
          </VAvatar>

          <!-- Upload Photo -->
          <div class="d-flex flex-column justify-center gap-4">
            <div class="d-flex flex-wrap gap-2">
              <VBtn
                color="primary"
                @click="refInputEl?.click()"
              >
                <VIcon
                  icon="tabler-cloud-upload"
                  class="d-sm-none"
                />
                <span class="d-none d-sm-block">Upload new photo</span>
              </VBtn>

              <input
                ref="refInputEl"
                type="file"
                name="file"
                accept=".jpeg,.png,.jpg,GIF"
                hidden
                @input="changeAvatar"
              >

              <VBtn
                type="reset"
                color="secondary"
                variant="outlined"
                @click="resetAvatar"
              >
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
              <!-- First Name -->
              <VCol
                md="6"
                cols="12"
              >
                <AppTextField
                  v-model="accountDataLocal.firstName"
                  label="First Name"
                  placeholder="John"
                />
              </VCol>

              <!-- Last Name -->
              <VCol
                md="6"
                cols="12"
              >
                <AppTextField
                  v-model="accountDataLocal.lastName"
                  label="Last Name"
                  placeholder="Doe"
                />
              </VCol>

              <!-- Email -->
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="accountDataLocal.email"
                  label="Email"
                  type="email"
                  placeholder="admin@example.com"
                />
              </VCol>

              <!-- Role -->
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="accountDataLocal.role"
                  label="Role"
                  placeholder="Administrator"
                  readonly
                />
              </VCol>

              <!-- Phone -->
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="accountDataLocal.phone"
                  label="Phone Number"
                  placeholder="+966 50 123 4567"
                />
              </VCol>

              <!-- Timezone -->
              <VCol
                cols="12"
                md="6"
              >
                <AppSelect
                  v-model="accountDataLocal.timezone"
                  label="Timezone"
                  :items="timezones"
                  placeholder="Select Timezone"
                />
              </VCol>

              <!-- Form Actions -->
              <VCol
                cols="12"
                class="d-flex flex-wrap gap-4"
              >
                <VBtn @click="onFormSubmit">
                  Save changes
                </VBtn>

                <VBtn
                  color="secondary"
                  variant="outlined"
                  type="reset"
                  @click.prevent="resetForm"
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
