// Task Management Constants for Consistency

export const TASK_CATEGORIES = [
  'Operations',
  'Training',
  'Maintenance',
  'Marketing',
  'Finance',
  'HR',
  'Quality Control',
  'Customer Service',
] as const

// @deprecated - This constant is no longer used for task assignment.
// Task assignment now uses actual user data from the useTaskUsers composable.
// Kept for potential future reference or other use cases.
export const USER_ROLES = [
  'Store Manager',
  'Assistant Manager',
  'HR Manager',
  'Technician',
  'Marketing Coordinator',
  'Finance Officer',
  'Quality Inspector',
  'Customer Service Rep',
] as const

export const PRIORITY_OPTIONS = [
  { title: 'Low', value: 'low' },
  { title: 'Medium', value: 'medium' },
  { title: 'High', value: 'high' },
] as const

export const STATUS_OPTIONS = [
  { title: 'Pending', value: 'pending' },
  { title: 'In Progress', value: 'in_progress' },
  { title: 'Completed', value: 'completed' },
] as const

export const TASK_HEADERS = [
  { title: 'Task Info', key: 'taskInfo' },
  { title: 'Category', key: 'category' },
  { title: 'Assigned To', key: 'assignedTo' },
  { title: 'Unit', key: 'unitName' },
  { title: 'Start Date', key: 'startDate' },
  { title: 'Due Date', key: 'dueDate' },
  { title: 'Priority', key: 'priority' },
  { title: 'Status', key: 'status' },
  { title: 'Actions', key: 'actions', sortable: false },
] as const
