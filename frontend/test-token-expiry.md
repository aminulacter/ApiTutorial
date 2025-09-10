# Token Expiration Implementation

## What was implemented:

1. **Global API Composable (`composables/useApi.ts`)**:
   - Centralized API calls with automatic token handling
   - Detects 401 Unauthorized responses (token expiration)
   - Automatically logs out user and redirects to login page
   - Provides consistent error handling across the app

2. **Updated Auth Store (`stores/auth.ts`)**:
   - Enhanced `fetchUser()` method to handle token expiration
   - Improved `initializeAuth()` to redirect to login if token is invalid
   - Better error handling for expired tokens

3. **Updated All Stores**:
   - **Products Store**: Now uses `useApi()` composable
   - **Users Store**: Now uses `useApi()` composable  
   - **Roles Store**: Now uses `useApi()` composable
   - All API calls now automatically handle token expiration

## How it works:

1. **Token Expiration Detection**: When any API call returns a 401 status code, the `useApi()` composable detects this as token expiration.

2. **Automatic Logout**: Upon detecting token expiration:
   - Clears all authentication data from the store
   - Removes tokens from localStorage
   - Redirects user to login page

3. **User Experience**: Users will be automatically logged out and redirected to login when their token expires, preventing them from making failed API calls.

## Benefits:

- **Automatic**: No manual intervention needed
- **Consistent**: All API calls handle token expiration the same way
- **User-friendly**: Users are redirected to login instead of seeing errors
- **Secure**: Expired tokens are immediately cleared from storage

## Testing:

To test token expiration:
1. Login to the application
2. Wait for token to expire (or manually expire it on the backend)
3. Try to perform any action (view products, users, etc.)
4. User should be automatically logged out and redirected to login page

The implementation ensures that users are never stuck with expired tokens and provides a smooth experience when tokens expire.
