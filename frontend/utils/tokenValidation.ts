/**
 * Utility functions for JWT token validation
 */

interface TokenValidationResult {
  isValid: boolean
  isExpired: boolean
  error?: string
}

/**
 * Validates JWT token by checking its expiration time
 * This is a client-side validation that doesn't require API calls
 */
export function validateTokenExpiration(token: string): TokenValidationResult {
  try {
    if (!token) {
      return { isValid: false, isExpired: true, error: 'No token provided' }
    }

    // Decode JWT token (without verification - just for expiration check)
    const parts = token.split('.')
    if (parts.length !== 3) {
      return { isValid: false, isExpired: true, error: 'Invalid token format' }
    }

    const payload = JSON.parse(atob(parts[1]))
    const currentTime = Math.floor(Date.now() / 1000)
    
    // Check if token is expired
    if (payload.exp && payload.exp < currentTime) {
      return { isValid: false, isExpired: true, error: 'Token expired' }
    }

    return { isValid: true, isExpired: false }
  } catch (error) {
    return { isValid: false, isExpired: true, error: 'Token validation failed' }
  }
}

/**
 * Validates JWT token by making an API call to the server
 * This provides server-side validation
 */
export async function validateTokenWithAPI(token: string): Promise<TokenValidationResult> {
  try {
    if (!token) {
      return { isValid: false, isExpired: true, error: 'No token provided' }
    }

    const config = useRuntimeConfig()
    await $fetch(`${config.public.apiBase}/user`, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    })

    return { isValid: true, isExpired: false }
  } catch (error: any) {
    if (error.status === 401 || error.statusCode === 401) {
      return { isValid: false, isExpired: true, error: 'Token expired or invalid' }
    }
    
    return { isValid: false, isExpired: false, error: 'Token validation failed' }
  }
}

/**
 * Comprehensive token validation that combines client-side and server-side checks
 * Uses client-side check first for performance, then server-side for security
 */
export async function validateToken(token: string, skipServerValidation = false): Promise<TokenValidationResult> {
  // First, do a quick client-side expiration check
  const clientValidation = validateTokenExpiration(token)
  
  if (!clientValidation.isValid) {
    return clientValidation
  }

  // If client-side validation passes and we're not skipping server validation,
  // do a server-side validation for security
  if (!skipServerValidation) {
    return await validateTokenWithAPI(token)
  }

  return clientValidation
}
