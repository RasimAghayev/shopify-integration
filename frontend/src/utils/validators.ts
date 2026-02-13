export function isValidSku(sku: string): boolean {
  if (!sku || sku.trim() === '') return false
  return /^[A-Z0-9\-_]{1,50}$/i.test(sku.trim())
}

export function isValidShopifyId(id: string): boolean {
  if (!id || id.trim() === '') return false
  return /^\d+$/.test(id.trim())
}

export function isValidPrice(price: number): boolean {
  return price >= 0 && Number.isFinite(price)
}

export function isValidQuantity(quantity: number): boolean {
  return quantity >= 0 && Number.isInteger(quantity)
}

export function isValidEmail(email: string): boolean {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

export function isNotEmpty(value: string): boolean {
  return value !== null && value !== undefined && value.trim() !== ''
}

export function validateRequired(value: unknown, fieldName: string): string | null {
  if (value === null || value === undefined || value === '') {
    return `${fieldName} is required`
  }
  return null
}

export function validateMinLength(value: string, min: number, fieldName: string): string | null {
  if (value.length < min) {
    return `${fieldName} must be at least ${min} characters`
  }
  return null
}

export function validateMaxLength(value: string, max: number, fieldName: string): string | null {
  if (value.length > max) {
    return `${fieldName} cannot exceed ${max} characters`
  }
  return null
}
