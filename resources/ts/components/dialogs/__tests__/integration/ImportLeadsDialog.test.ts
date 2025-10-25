import { describe, it, expect, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import ImportLeadsDialog from '../../leads/ImportLeadsDialog.vue'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

const vuetify = createVuetify({
  components,
  directives,
})

describe('ImportLeadsDialog.vue - File Upload Validation', () => {
  let wrapper: any

  beforeEach(() => {
    wrapper = mount(ImportLeadsDialog, {
      props: {
        isDialogVisible: true,
      },
      global: {
        plugins: [vuetify],
        stubs: {
          DialogCloseBtn: true,
          VFileInput: true,
          VBtn: true,
        },
      },
    })
  })

  describe('Component Mounting', () => {
    it('should mount successfully', () => {
      expect(wrapper.exists()).toBe(true)
    })

    it('should initialize with no file selected', () => {
      expect(wrapper.vm.csvFile).toBeNull()
    })
  })

  describe('File Selection', () => {
    it('should accept CSV file selection', () => {
      const csvFile = new File(['content'], 'leads.csv', { type: 'text/csv' })
      wrapper.vm.csvFile = csvFile

      expect(wrapper.vm.csvFile).toBe(csvFile)
      expect(wrapper.vm.csvFile.name).toBe('leads.csv')
    })

    it('should handle file input change event', () => {
      const csvFile = new File(['content'], 'test.csv', { type: 'text/csv' })
      const event = {
        target: {
          files: [csvFile],
        },
      }

      wrapper.vm.handleFileUpload(event)

      expect(wrapper.vm.csvFile).toBe(csvFile)
    })

    it('should handle no file selected', () => {
      const event = {
        target: {
          files: null,
        },
      }

      wrapper.vm.handleFileUpload(event)

      expect(wrapper.vm.csvFile).toBeNull()
    })
  })

  describe('File Type Validation', () => {
    it('should validate CSV file type', () => {
      const csvFile = new File(['content'], 'leads.csv', { type: 'text/csv' })
      
      // Get the file type validation rule
      const fileTypeRule = wrapper.vm.rules.fileType(['csv'], 'Only CSV files are allowed')
      
      expect(fileTypeRule(csvFile)).toBe(true)
    })

    it('should reject non-CSV files', () => {
      const txtFile = new File(['content'], 'leads.txt', { type: 'text/plain' })
      
      const fileTypeRule = wrapper.vm.rules.fileType(['csv'], 'Only CSV files are allowed')
      
      expect(fileTypeRule(txtFile)).toBe('Only CSV files are allowed')
    })

    it('should reject Excel files', () => {
      const xlsxFile = new File(['content'], 'leads.xlsx', { 
        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' 
      })
      
      const fileTypeRule = wrapper.vm.rules.fileType(['csv'], 'Only CSV files are allowed')
      
      expect(fileTypeRule(xlsxFile)).toBe('Only CSV files are allowed')
    })

    it('should reject PDF files', () => {
      const pdfFile = new File(['content'], 'document.pdf', { type: 'application/pdf' })
      
      const fileTypeRule = wrapper.vm.rules.fileType(['csv'], 'Only CSV files are allowed')
      
      expect(fileTypeRule(pdfFile)).toBe('Only CSV files are allowed')
    })
  })

  describe('File Required Validation', () => {
    it('should require file to be selected', async () => {
      wrapper.vm.csvFile = null

      const formRef = wrapper.vm.formRef
      if (formRef && formRef.validate) {
        const result = await formRef.validate()
        expect(result.valid).toBe(false)
      }
    })

    it('should pass validation with file selected', async () => {
      const csvFile = new File(['content'], 'leads.csv', { type: 'text/csv' })
      wrapper.vm.csvFile = csvFile

      const formRef = wrapper.vm.formRef
      if (formRef && formRef.validate) {
        const result = await formRef.validate()
        expect(result.valid).toBe(true)
      }
    })
  })

  describe('Form Submission', () => {
    it('should not submit without file', async () => {
      wrapper.vm.csvFile = null

      const formRef = wrapper.vm.formRef
      if (formRef && formRef.validate) {
        const result = await formRef.validate()
        expect(result.valid).toBe(false)
      }
    })

    it('should emit import event with file', async () => {
      const csvFile = new File(['content'], 'leads.csv', { type: 'text/csv' })
      wrapper.vm.csvFile = csvFile

      await wrapper.vm.importCSV()

      expect(wrapper.emitted('import')).toBeTruthy()
      expect(wrapper.emitted('import')[0]).toEqual([csvFile])
    })

    it('should close dialog after import', async () => {
      const csvFile = new File(['content'], 'leads.csv', { type: 'text/csv' })
      wrapper.vm.csvFile = csvFile

      await wrapper.vm.importCSV()

      expect(wrapper.emitted('update:isDialogVisible')).toBeTruthy()
      expect(wrapper.emitted('update:isDialogVisible')[0]).toEqual([false])
    })

    it('should reset file after import', async () => {
      const csvFile = new File(['content'], 'leads.csv', { type: 'text/csv' })
      wrapper.vm.csvFile = csvFile

      await wrapper.vm.importCSV()

      expect(wrapper.vm.csvFile).toBeNull()
    })
  })

  describe('Download Example CSV', () => {
    it('should trigger CSV download', () => {
      const createElementSpy = vi.spyOn(document, 'createElement')
      
      wrapper.vm.downloadExampleCSV()

      expect(createElementSpy).toHaveBeenCalledWith('a')
    })
  })

  describe('Dialog Cancel', () => {
    it('should close dialog on cancel', () => {
      wrapper.vm.handleCancel()

      expect(wrapper.emitted('update:isDialogVisible')).toBeTruthy()
      expect(wrapper.emitted('update:isDialogVisible')[0]).toEqual([false])
    })

    it('should clear file on cancel', () => {
      const csvFile = new File(['content'], 'leads.csv', { type: 'text/csv' })
      wrapper.vm.csvFile = csvFile

      wrapper.vm.handleCancel()

      expect(wrapper.vm.csvFile).toBeNull()
    })
  })

  describe('Backend Error Handling', () => {
    it('should handle backend errors', () => {
      const backendError = {
        errors: {
          file: ['Invalid CSV format'],
        },
      }

      wrapper.vm.setBackendErrors(backendError)

      expect(wrapper.vm.backendErrors.file).toBe('Invalid CSV format')
    })

    it('should clear file error on new selection', () => {
      wrapper.vm.backendErrors.file = 'Invalid file'

      wrapper.vm.clearError('file')

      expect(wrapper.vm.backendErrors.file).toBeUndefined()
    })
  })
})

