import { describe, it, expect } from 'vitest'
import {
  isValidSku,
  isValidShopifyId,
  isValidPrice,
  isValidQuantity,
  isValidEmail,
  isNotEmpty,
  validateRequired,
  validateMinLength,
  validateMaxLength
} from '~/utils/validators'

describe('validators', () => {
  describe('isValidSku', () => {
    it('returns true for valid SKU', () => {
      expect(isValidSku('ABC-123')).toBe(true)
      expect(isValidSku('PRODUCT_001')).toBe(true)
      expect(isValidSku('SKU123')).toBe(true)
    })

    it('returns false for empty SKU', () => {
      expect(isValidSku('')).toBe(false)
      expect(isValidSku('   ')).toBe(false)
    })

    it('returns false for invalid characters', () => {
      expect(isValidSku('SKU@123')).toBe(false)
      expect(isValidSku('SKU 123')).toBe(false)
    })

    it('returns false for SKU exceeding 50 characters', () => {
      const longSku = 'A'.repeat(51)
      expect(isValidSku(longSku)).toBe(false)
    })

    it('returns true for SKU at max length', () => {
      const maxSku = 'A'.repeat(50)
      expect(isValidSku(maxSku)).toBe(true)
    })
  })

  describe('isValidShopifyId', () => {
    it('returns true for valid numeric ID', () => {
      expect(isValidShopifyId('123456789')).toBe(true)
      expect(isValidShopifyId('1')).toBe(true)
    })

    it('returns false for non-numeric ID', () => {
      expect(isValidShopifyId('abc123')).toBe(false)
      expect(isValidShopifyId('123-456')).toBe(false)
    })

    it('returns false for empty ID', () => {
      expect(isValidShopifyId('')).toBe(false)
      expect(isValidShopifyId('   ')).toBe(false)
    })
  })

  describe('isValidPrice', () => {
    it('returns true for valid price', () => {
      expect(isValidPrice(0)).toBe(true)
      expect(isValidPrice(100)).toBe(true)
      expect(isValidPrice(99.99)).toBe(true)
    })

    it('returns false for negative price', () => {
      expect(isValidPrice(-1)).toBe(false)
      expect(isValidPrice(-100)).toBe(false)
    })

    it('returns false for non-finite price', () => {
      expect(isValidPrice(Infinity)).toBe(false)
      expect(isValidPrice(NaN)).toBe(false)
    })
  })

  describe('isValidQuantity', () => {
    it('returns true for valid quantity', () => {
      expect(isValidQuantity(0)).toBe(true)
      expect(isValidQuantity(100)).toBe(true)
    })

    it('returns false for negative quantity', () => {
      expect(isValidQuantity(-1)).toBe(false)
    })

    it('returns false for non-integer quantity', () => {
      expect(isValidQuantity(1.5)).toBe(false)
      expect(isValidQuantity(99.99)).toBe(false)
    })
  })

  describe('isValidEmail', () => {
    it('returns true for valid email', () => {
      expect(isValidEmail('test@example.com')).toBe(true)
      expect(isValidEmail('user.name@domain.org')).toBe(true)
    })

    it('returns false for invalid email', () => {
      expect(isValidEmail('invalid')).toBe(false)
      expect(isValidEmail('test@')).toBe(false)
      expect(isValidEmail('@domain.com')).toBe(false)
    })
  })

  describe('isNotEmpty', () => {
    it('returns true for non-empty string', () => {
      expect(isNotEmpty('hello')).toBe(true)
      expect(isNotEmpty('  hello  ')).toBe(true)
    })

    it('returns false for empty or whitespace string', () => {
      expect(isNotEmpty('')).toBe(false)
      expect(isNotEmpty('   ')).toBe(false)
    })
  })

  describe('validateRequired', () => {
    it('returns null for valid value', () => {
      expect(validateRequired('value', 'Field')).toBeNull()
      expect(validateRequired(123, 'Field')).toBeNull()
    })

    it('returns error message for empty value', () => {
      expect(validateRequired('', 'Name')).toBe('Name is required')
      expect(validateRequired(null, 'Email')).toBe('Email is required')
      expect(validateRequired(undefined, 'Phone')).toBe('Phone is required')
    })
  })

  describe('validateMinLength', () => {
    it('returns null for valid length', () => {
      expect(validateMinLength('hello', 3, 'Field')).toBeNull()
      expect(validateMinLength('abc', 3, 'Field')).toBeNull()
    })

    it('returns error for short value', () => {
      expect(validateMinLength('ab', 3, 'Name')).toBe('Name must be at least 3 characters')
    })
  })

  describe('validateMaxLength', () => {
    it('returns null for valid length', () => {
      expect(validateMaxLength('hello', 10, 'Field')).toBeNull()
      expect(validateMaxLength('hi', 10, 'Field')).toBeNull()
    })

    it('returns error for long value', () => {
      expect(validateMaxLength('hello world', 5, 'Title')).toBe('Title cannot exceed 5 characters')
    })
  })
})
