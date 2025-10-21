import { $api } from '@/utils/api'
import { getEndpoint } from '@/utils/api-router'
import type { ApiResponse } from '@/types/api'

// Note types
export interface Note {
  id: number
  title: string
  description: string
  user?: {
    id: number
    name: string
    email: string
  }
  created_at: string
  updated_at: string
  attachments?: NoteAttachment[]
  lead_id?: number
}

export interface NoteAttachment {
  id: number
  filename: string
  original_name: string
  mime_type: string
  size: number
  url: string
}

export interface CreateNotePayload {
  title: string
  description: string
  attachments?: File[]
  lead_id?: number
}

export interface UpdateNotePayload {
  id: number
  title?: string
  description?: string
  attachments?: File[]
}

export interface NotesFilters {
  lead_id?: number
  page?: number
  per_page?: number
}

export class NotesApi {
  private getBaseUrl(): string {
    return '/v1/notes'
  }

  /**
   * Get notes list with optional filters
   */
  async getNotes(filters?: NotesFilters): Promise<ApiResponse<Note[]>> {
    return await $api(this.getBaseUrl(), {
      method: 'GET',
      query: filters,
    })
  }

  /**
   * Get a single note by ID
   */
  async getNote(id: number): Promise<ApiResponse<Note>> {
    return await $api(`${this.getBaseUrl()}/${id}`)
  }

  /**
   * Create a new note
   */
  async createNote(payload: CreateNotePayload): Promise<ApiResponse<Note>> {
    const formData = new FormData()
    
    formData.append('title', payload.title)
    formData.append('description', payload.description)
    
    if (payload.lead_id) {
      formData.append('lead_id', payload.lead_id.toString())
    }

    if (payload.attachments && payload.attachments.length > 0) {
      payload.attachments.forEach((file, index) => {
        formData.append(`attachments[${index}]`, file)
      })
    }

    return await $api(this.getBaseUrl(), {
      method: 'POST',
      body: formData,
    })
  }

  /**
   * Update an existing note
   */
  async updateNote(payload: UpdateNotePayload): Promise<ApiResponse<Note>> {
    const formData = new FormData()
    
    if (payload.title) formData.append('title', payload.title)
    if (payload.description) formData.append('description', payload.description)

    if (payload.attachments && payload.attachments.length > 0) {
      payload.attachments.forEach((file, index) => {
        formData.append(`attachments[${index}]`, file)
      })
    }

    return await $api(`${this.getBaseUrl()}/${payload.id}`, {
      method: 'PUT',
      body: formData,
    })
  }

  /**
   * Delete a note
   */
  async deleteNote(id: number): Promise<ApiResponse<null>> {
    return await $api(`${this.getBaseUrl()}/${id}`, {
      method: 'DELETE',
    })
  }

  /**
   * Delete a note attachment
   */
  async deleteNoteAttachment(noteId: number, attachmentIndex: number): Promise<ApiResponse<null>> {
    return await $api(`${this.getBaseUrl()}/${noteId}/attachments/${attachmentIndex}`, {
      method: 'DELETE',
    })
  }

  /**
   * Update note with FormData (for file uploads)
   */
  async updateNoteWithFormData(noteId: number, formData: FormData): Promise<ApiResponse<Note>> {
    return await $api(`${this.getBaseUrl()}/${noteId}`, {
      method: 'POST',
      body: formData,
    })
  }
}

export const notesApi = new NotesApi()
