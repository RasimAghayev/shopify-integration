import { describe, it, expect } from 'vitest'
import {
  formatPrice,
  formatDate,
  formatDateTime,
  formatNumber,
  formatPercentage,
  truncate
} from '~/utils/formatters'

describe('formatters', () => {
  describe('formatPrice', () => {
    it('formats USD price correctly', () => {
      const price = { amount: 1999, currency: 'USD' }
      expect(formatPrice(price)).toBe('$19.99')
    })

    it('formats zero price', () => {
      const price = { amount: 0, currency: 'USD' }
      expect(formatPrice(price)).toBe('$0.00')
    })

    it('formats large price', () => {
      const price = { amount: 99999, currency: 'USD' }
      expect(formatPrice(price)).toBe('$999.99')
    })

    it('formats EUR price correctly', () => {
      const price = { amount: 2500, currency: 'EUR' }
      const result = formatPrice(price)
      expect(result).toContain('25')
    })
  })

  describe('formatDate', () => {
    it('formats ISO date string', () => {
      const result = formatDate('2024-01-15T10:30:00Z')
      expect(result).toContain('Jan')
      expect(result).toContain('15')
      expect(result).toContain('2024')
    })
  })

  describe('formatDateTime', () => {
    it('formats ISO datetime string with time', () => {
      const result = formatDateTime('2024-01-15T10:30:00Z')
      expect(result).toContain('Jan')
      expect(result).toContain('15')
      expect(result).toContain('2024')
    })
  })

  describe('formatNumber', () => {
    it('formats number with thousand separators', () => {
      expect(formatNumber(1000)).toBe('1,000')
      expect(formatNumber(1000000)).toBe('1,000,000')
    })

    it('formats small numbers without separators', () => {
      expect(formatNumber(100)).toBe('100')
    })

    it('formats zero', () => {
      expect(formatNumber(0)).toBe('0')
    })
  })

  describe('formatPercentage', () => {
    it('formats percentage with one decimal', () => {
      expect(formatPercentage(50)).toBe('50.0%')
      expect(formatPercentage(33.333)).toBe('33.3%')
    })

    it('formats zero percentage', () => {
      expect(formatPercentage(0)).toBe('0.0%')
    })

    it('formats 100 percentage', () => {
      expect(formatPercentage(100)).toBe('100.0%')
    })
  })

  describe('truncate', () => {
    it('truncates long text', () => {
      const text = 'This is a very long text that should be truncated'
      expect(truncate(text, 20)).toBe('This is a very long ...')
    })

    it('does not truncate short text', () => {
      const text = 'Short text'
      expect(truncate(text, 20)).toBe('Short text')
    })

    it('handles exact length', () => {
      const text = 'Exact'
      expect(truncate(text, 5)).toBe('Exact')
    })

    it('handles empty string', () => {
      expect(truncate('', 10)).toBe('')
    })
  })
})
