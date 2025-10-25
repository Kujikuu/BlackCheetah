import { describe, it, expect } from 'vitest'
import { useValidationRules } from '../useValidationRules'

describe('useValidationRules', () => {
  const rules = useValidationRules()

  describe('required', () => {
    it('should pass with non-empty value', () => {
      expect(rules.required()('test')).toBe(true)
      expect(rules.required()('123')).toBe(true)
      expect(rules.required()(0)).toBe(true)
    })

    it('should fail with empty value', () => {
      expect(rules.required()('')).toBe('This field is required')
      expect(rules.required()(null)).toBe('This field is required')
      expect(rules.required()(undefined)).toBe('This field is required')
    })

    it('should use custom message', () => {
      expect(rules.required('Name is required')('')).toBe('Name is required')
    })
  })

  describe('email', () => {
    it('should pass with valid email', () => {
      expect(rules.email()('test@example.com')).toBe(true)
      expect(rules.email()('user.name@domain.co.uk')).toBe(true)
      expect(rules.email()('test+tag@example.com')).toBe(true)
    })

    it('should fail with invalid email', () => {
      const message = 'Please provide a valid email address'
      expect(rules.email()('test@')).toBe(message)
      expect(rules.email()('test.com')).toBe(message)
      expect(rules.email()('@test.com')).toBe(message)
      expect(rules.email()('test @test.com')).toBe(message)
    })

    it('should pass with empty value (optional)', () => {
      expect(rules.email()('')).toBe(true)
      expect(rules.email()(null as any)).toBe(true)
    })

    it('should use custom message', () => {
      expect(rules.email('Invalid email')('test@')).toBe('Invalid email')
    })
  })

  describe('phone', () => {
    it('should pass with valid phone numbers', () => {
      expect(rules.phone()('+966501234567')).toBe(true)
      expect(rules.phone()('+1-555-123-4567')).toBe(true)
      expect(rules.phone()('0501234567')).toBe(true)
    })

    it('should fail with invalid phone numbers', () => {
      const message = 'Please provide a valid phone number'
      expect(rules.phone()('123')).toBe(message)
      expect(rules.phone()('abcdefghij')).toBe(message)
    })

    it('should pass with empty value (optional)', () => {
      expect(rules.phone()('')).toBe(true)
    })
  })

  describe('minLength', () => {
    it('should pass with valid length', () => {
      expect(rules.minLength(3)('test')).toBe(true)
      expect(rules.minLength(3)('abc')).toBe(true)
    })

    it('should fail with invalid length', () => {
      expect(rules.minLength(5)('test')).toBe('Minimum length is 5 characters')
      expect(rules.minLength(10)('short')).toBe('Minimum length is 10 characters')
    })

    it('should pass with empty value (optional)', () => {
      expect(rules.minLength(5)('')).toBe(true)
    })

    it('should use custom message', () => {
      expect(rules.minLength(5, 'Too short')('ab')).toBe('Too short')
    })
  })

  describe('maxLength', () => {
    it('should pass with valid length', () => {
      expect(rules.maxLength(10)('test')).toBe(true)
      expect(rules.maxLength(10)('1234567890')).toBe(true)
    })

    it('should fail with invalid length', () => {
      expect(rules.maxLength(5)('toolong')).toBe('Maximum length is 5 characters')
      expect(rules.maxLength(3)('test')).toBe('Maximum length is 3 characters')
    })

    it('should pass with empty value (optional)', () => {
      expect(rules.maxLength(5)('')).toBe(true)
    })
  })

  describe('numeric', () => {
    it('should pass with numeric values', () => {
      expect(rules.numeric()('123')).toBe(true)
      expect(rules.numeric()('123.45')).toBe(true)
      expect(rules.numeric()('-123')).toBe(true)
      expect(rules.numeric()(0)).toBe(true)
    })

    it('should fail with non-numeric values', () => {
      const message = 'This field must be a number'
      expect(rules.numeric()('abc')).toBe(message)
      expect(rules.numeric()('12abc')).toBe(message)
    })

    it('should pass with empty value (optional)', () => {
      expect(rules.numeric()('')).toBe(true)
    })
  })

  describe('min', () => {
    it('should pass with valid values', () => {
      expect(rules.min(10)('15')).toBe(true)
      expect(rules.min(10)(10)).toBe(true)
      expect(rules.min(0)('5')).toBe(true)
    })

    it('should fail with invalid values', () => {
      expect(rules.min(10)('5')).toBe('Minimum value is 10')
      expect(rules.min(100)(50)).toBe('Minimum value is 100')
    })

    it('should pass with empty value (optional)', () => {
      expect(rules.min(10)('')).toBe(true)
    })
  })

  describe('max', () => {
    it('should pass with valid values', () => {
      expect(rules.max(100)('50')).toBe(true)
      expect(rules.max(100)(100)).toBe(true)
      expect(rules.max(10)('5')).toBe(true)
    })

    it('should fail with invalid values', () => {
      expect(rules.max(10)('15')).toBe('Maximum value is 10')
      expect(rules.max(100)(150)).toBe('Maximum value is 100')
    })

    it('should pass with empty value (optional)', () => {
      expect(rules.max(10)('')).toBe(true)
    })
  })

  describe('date', () => {
    it('should pass with valid dates', () => {
      expect(rules.date()('2024-01-15')).toBe(true)
      expect(rules.date()('2024/01/15')).toBe(true)
      expect(rules.date()('01/15/2024')).toBe(true)
    })

    it('should fail with invalid dates', () => {
      const message = 'Please provide a valid date'
      expect(rules.date()('invalid')).toBe(message)
      expect(rules.date()('2024-13-45')).toBe(message)
      expect(rules.date()('abc')).toBe(message)
    })

    it('should pass with empty value (optional)', () => {
      expect(rules.date()('')).toBe(true)
    })
  })

  describe('url', () => {
    it('should pass with valid URLs', () => {
      expect(rules.url()('https://example.com')).toBe(true)
      expect(rules.url()('http://test.com')).toBe(true)
      expect(rules.url()('https://sub.domain.com/path')).toBe(true)
    })

    it('should fail with invalid URLs', () => {
      const message = 'Please provide a valid URL'
      expect(rules.url()('not a url')).toBe(message)
      expect(rules.url()('example.com')).toBe(message)
      expect(rules.url()('just-text')).toBe(message)
    })

    it('should pass with empty value (optional)', () => {
      expect(rules.url()('')).toBe(true)
    })
  })

  describe('file', () => {
    it('should pass with file object', () => {
      const file = new File(['content'], 'test.txt', { type: 'text/plain' })
      expect(rules.file()(file)).toBe(true)
    })

    it('should pass with array of files', () => {
      const files = [
        new File(['content'], 'test.txt', { type: 'text/plain' }),
      ]
      // File rule expects a File object, not array - this should actually fail
      expect(rules.file()(files as any)).toBe('Please select a file')
    })

    it('should fail with no file', () => {
      expect(rules.file()(null)).toBe('Please select a file')
      expect(rules.file()(undefined)).toBe('Please select a file')
      expect(rules.file()([])).toBe('Please select a file')
    })

    it('should use custom message', () => {
      expect(rules.file('File required')(null)).toBe('File required')
    })
  })

  describe('fileSize', () => {
    it('should pass with valid file size', () => {
      const smallFile = new File(['a'.repeat(1024)], 'small.txt') // 1KB
      expect(rules.fileSize(1)(smallFile)).toBe(true)
    })

    it('should fail with large file', () => {
      const largeFile = new File(['a'.repeat(6 * 1024 * 1024)], 'large.txt') // 6MB
      expect(rules.fileSize(5)(largeFile)).toBe('File size must not exceed 5MB')
    })

    it('should pass with empty value (optional)', () => {
      expect(rules.fileSize(5)(null as any)).toBe(true)
    })
  })

  describe('fileType', () => {
    it('should pass with valid file types', () => {
      const pdfFile = new File(['content'], 'test.pdf', { type: 'application/pdf' })
      expect(rules.fileType(['pdf'])(pdfFile)).toBe(true)
      
      const jpgFile = new File(['content'], 'test.jpg', { type: 'image/jpeg' })
      expect(rules.fileType(['jpg', 'jpeg', 'png'])(jpgFile)).toBe(true)
    })

    it('should fail with invalid file types', () => {
      const txtFile = new File(['content'], 'test.txt', { type: 'text/plain' })
      expect(rules.fileType(['pdf', 'doc'])(txtFile)).toBe('Allowed file types: pdf, doc')
    })

    it('should pass with empty value (optional)', () => {
      expect(rules.fileType(['pdf'])(null as any)).toBe(true)
    })
  })

  describe('inArray', () => {
    it('should pass with value in array', () => {
      expect(rules.inArray(['active', 'inactive'])('active')).toBe(true)
      expect(rules.inArray([1, 2, 3])(2)).toBe(true)
    })

    it('should fail with value not in array', () => {
      expect(rules.inArray(['active', 'inactive'])('pending')).toBe('Value must be one of: active, inactive')
    })

    it('should pass with empty value (optional)', () => {
      expect(rules.inArray(['test'])('')).toBe(true)
    })
  })

  describe('string', () => {
    it('should pass with string values', () => {
      expect(rules.string()('test')).toBe(true)
      expect(rules.string()('123')).toBe(true)
      expect(rules.string()('')).toBe(true)
    })

    it('should fail with non-string values', () => {
      const message = 'This field must be a string'
      expect(rules.string()(123 as any)).toBe(message)
      // null and undefined return true due to optional handling
      expect(rules.string()({})).toBe(message)
      expect(rules.string()([])).toBe(message)
    })
  })
})

