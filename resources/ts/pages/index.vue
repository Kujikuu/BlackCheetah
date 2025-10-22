<script setup lang="ts">
// This is the root route handler that redirects users to their role-specific dashboard

const router = useRouter()
const userData = useCookie('userData')

// Redirect based on user role
const redirectToDashboard = () => {
  // Check both the cookie value and ensure it's not just an empty object
  const userDataValue = userData.value
  if (!userDataValue || !userDataValue.id || !userDataValue.role) {
    // If no valid user data, redirect to login
    router.push('/login')

    return
  }

  const role = userDataValue.role

  switch (role) {
    case 'admin':
      router.push('/admin/dashboard')
      break
    case 'franchisor':
      router.push('/franchisor')
      break
    case 'franchisee':
      router.push('/franchisee/dashboard/sales') // Default to broker dashboard
      break
    case 'broker':
      router.push('/brokers/lead-management')
      break
    default:
    // Fallback to login if role is unknown
      router.push('/login')
  }
}

// Redirect immediately when component is mounted
onMounted(async () => {
  // Wait a bit for cookies to be available
  await new Promise(resolve => setTimeout(resolve, 50))
  redirectToDashboard()
})

// Also redirect if userData changes (e.g., after login)
watch(userData, newUserData => {
  if (newUserData)
    redirectToDashboard()
}, { immediate: true })
</script>

<template>
  <div class="d-flex align-center justify-center min-h-screen">
    <div class="text-center">
      <VProgressCircular
        indeterminate
        color="primary"
        size="64"
      />
      <p class="mt-4 text-body-1">
        Redirecting to your dashboard...
      </p>
    </div>
  </div>
</template>
