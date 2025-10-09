<script setup lang="ts">
definePage({
  meta: {
    layout: 'blank',
    public: true,
  },
})

const debugInfo = ref({
  viteApiBaseUrl: import.meta.env.VITE_API_BASE_URL,
  apiBaseUrl: '',
  testResult: '',
  error: '',
})

const testApiCall = async () => {
  try {
    debugInfo.value.error = ''
    debugInfo.value.testResult = 'Testing...'
    
    console.log('Environment variables:', import.meta.env)
    console.log('VITE_API_BASE_URL:', import.meta.env.VITE_API_BASE_URL)
    
    // Test the exact same call as the login page
    console.log('Making API call to:', '/auth/login')
    console.log('With base URL:', import.meta.env.VITE_API_BASE_URL || '/api')
    
    const resp = await $api<{ accessToken: string; userData: any; userAbilityRules: any[] }>(
      '/auth/login',
      {
        method: 'POST',
        body: {
          email: 'john.smith@example.com',
          password: 'password',
        },
      },
    )
    
    console.log('API response:', resp)
    debugInfo.value.testResult = JSON.stringify(resp, null, 2)
  } catch (e: any) {
    console.error('Login error details:', e)
    console.error('Error type:', typeof e)
    console.error('Error constructor:', e.constructor.name)
    console.error('Error keys:', Object.keys(e))
    
    debugInfo.value.error = JSON.stringify({
      message: e.message,
      name: e.name,
      stack: e.stack,
      data: e.data,
      response: e.response,
      status: e.status,
      statusText: e.statusText,
      cause: e.cause,
      toString: e.toString(),
    }, null, 2)
  }
}

const testWithFetch = async () => {
  try {
    debugInfo.value.error = ''
    debugInfo.value.testResult = 'Testing with fetch...'
    
    const response = await fetch('https://blackcheetah.test/api/auth/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({
        email: 'john.smith@example.com',
        password: 'password',
      }),
    })
    
    const data = await response.json()
    console.log('Fetch response:', data)
    debugInfo.value.testResult = JSON.stringify(data, null, 2)
  } catch (e: any) {
    console.error('Fetch error:', e)
    debugInfo.value.error = JSON.stringify({
      message: e.message,
      name: e.name,
      stack: e.stack,
    }, null, 2)
  }
}

onMounted(() => {
  debugInfo.value.apiBaseUrl = import.meta.env.VITE_API_BASE_URL || '/api'
})
</script>

<template>
  <div class="pa-8">
    <h1>Login Debug Page</h1>
    
    <VCard class="mb-4">
      <VCardTitle>Environment Variables</VCardTitle>
      <VCardText>
        <pre>{{ JSON.stringify(debugInfo, null, 2) }}</pre>
      </VCardText>
    </VCard>
    
    <VBtn @click="testApiCall" color="primary" class="mr-2">
      Test $api Call
    </VBtn>
    
    <VBtn @click="testWithFetch" color="secondary">
      Test with Fetch
    </VBtn>
    
    <VCard v-if="debugInfo.testResult" class="mt-4">
      <VCardTitle>API Response</VCardTitle>
      <VCardText>
        <pre>{{ debugInfo.testResult }}</pre>
      </VCardText>
    </VCard>
    
    <VCard v-if="debugInfo.error" class="mt-4" color="error">
      <VCardTitle>Error</VCardTitle>
      <VCardText>
        <pre>{{ debugInfo.error }}</pre>
      </VCardText>
    </VCard>
  </div>
</template>