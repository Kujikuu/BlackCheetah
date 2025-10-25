/**
 * Tasks Validation Rules
 * Maps StoreTaskRequest, UpdateTaskRequest, and UpdateTaskStatusRequest backend validation rules
 */

import { useValidationRules } from '@/composables/useValidationRules'

const rules = useValidationRules()

// Task category options
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

// Task priority options
export const TASK_PRIORITIES = [
  'low',
  'medium',
  'high',
] as const

// Task status options (for create/update)
export const TASK_STATUSES = [
  'pending',
  'in_progress',
  'completed',
] as const

// Task status options (for status change - includes more options)
export const TASK_STATUSES_FULL = [
  'pending',
  'in_progress',
  'completed',
  'cancelled',
  'on_hold',
] as const

/**
 * Validation rules for creating a new task (StoreTaskRequest)
 */
export function useStoreTaskValidation() {
  return {
    title: [
      rules.required('Task title is required'),
      rules.string(),
      rules.maxLength(255, 'Task title cannot exceed 255 characters'),
    ],
    description: [
      rules.required('Task description is required'),
      rules.string(),
    ],
    category: [
      rules.required('Task category is required'),
      rules.inArray(TASK_CATEGORIES as unknown as string[], 'Invalid task category selected'),
    ],
    priority: [
      rules.required('Task priority is required'),
      rules.inArray(TASK_PRIORITIES as unknown as string[], 'Invalid priority level selected'),
    ],
    status: [
      rules.required('Task status is required'),
      rules.inArray(TASK_STATUSES as unknown as string[], 'Invalid task status selected'),
    ],
    assignedTo: [
      // Note: nullable on backend, but may be required by component logic
    ],
    dueDate: [
      rules.date('Due date must be a valid date'),
      rules.afterOrEqualToday('Due date must be today or in the future'),
    ],
    startDate: [
      rules.date('Start date must be a valid date'),
    ],
    estimatedHours: [
      rules.numeric('Estimated hours must be a number'),
      rules.min(0, 'Estimated hours cannot be negative'),
    ],
    actualHours: [
      rules.numeric('Actual hours must be a number'),
      rules.min(0, 'Actual hours cannot be negative'),
    ],
    notes: [
      rules.string(),
    ],
  }
}

/**
 * Validation rules for updating a task (UpdateTaskRequest)
 */
export function useUpdateTaskValidation() {
  return {
    title: [
      rules.string(),
      rules.maxLength(255, 'Task title cannot exceed 255 characters'),
    ],
    description: [
      rules.string(),
    ],
    category: [
      rules.inArray(TASK_CATEGORIES as unknown as string[], 'Invalid task category selected'),
    ],
    priority: [
      rules.inArray(TASK_PRIORITIES as unknown as string[], 'Invalid priority level selected'),
    ],
    status: [
      rules.inArray(TASK_STATUSES as unknown as string[], 'Invalid task status selected'),
    ],
    assignedTo: [
      // Nullable
    ],
    dueDate: [
      rules.date('Due date must be a valid date'),
    ],
    startDate: [
      rules.date('Start date must be a valid date'),
    ],
    estimatedHours: [
      rules.numeric('Estimated hours must be a number'),
      rules.min(0, 'Estimated hours cannot be negative'),
    ],
    actualHours: [
      rules.numeric('Actual hours must be a number'),
      rules.min(0, 'Actual hours cannot be negative'),
    ],
    notes: [
      rules.string(),
    ],
  }
}

/**
 * Validation rules for updating task status (UpdateTaskStatusRequest)
 */
export function useUpdateTaskStatusValidation() {
  return {
    status: [
      rules.required('Task status is required'),
      rules.inArray(TASK_STATUSES_FULL as unknown as string[], 'Status must be one of: pending, in_progress, completed, cancelled, on_hold'),
    ],
  }
}

/**
 * Validation rules for unit tasks (CreateUnitTaskRequest / UpdateUnitTaskRequest)
 * These follow similar rules to regular tasks
 */
export function useCreateUnitTaskValidation() {
  return useStoreTaskValidation()
}

export function useUpdateUnitTaskValidation() {
  return useUpdateTaskValidation()
}

