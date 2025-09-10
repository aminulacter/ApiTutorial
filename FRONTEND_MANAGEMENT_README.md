# Frontend User & Role Management

This document describes the frontend pages and functionality for managing users and roles in the Nuxt.js application.

## Pages Created

### User Management Pages

#### 1. Users List (`/users`)
- **File**: `frontend/pages/users/index.vue`
- **Features**:
  - Paginated list of users with search and filtering
  - User information display (name, email, roles, verification status)
  - Role badges for each user
  - Actions: View, Edit, Delete (with permission checks)
  - Delete confirmation modal
  - Responsive design with mobile-friendly pagination

#### 2. Create User (`/users/create`)
- **File**: `frontend/pages/users/create.vue`
- **Features**:
  - Form for creating new users
  - Fields: Name, Email, Password, Password Confirmation
  - Role selection with checkboxes
  - Form validation (client-side and server-side error handling)
  - Permission-based access control

#### 3. User Details (`/users/[id]`)
- **File**: `frontend/pages/users/[id].vue`
- **Features**:
  - Detailed user information display
  - Role management with attach/detach functionality
  - Permission summary
  - Role management modal
  - Quick actions for editing and role management

#### 4. Edit User (`/users/[id]/edit`)
- **File**: `frontend/pages/users/[id]/edit.vue`
- **Features**:
  - Form for updating user information
  - Optional password update (leave blank to keep current)
  - Role management with checkboxes
  - Form validation and error handling

### Role Management Pages

#### 1. Roles List (`/roles`)
- **File**: `frontend/pages/roles/index.vue`
- **Features**:
  - Paginated list of roles with search and filtering
  - Role information display (name, display name, description)
  - Permission and user counts
  - Actions: View, Edit, Delete (with permission checks)
  - Delete confirmation modal

#### 2. Create Role (`/roles/create`)
- **File**: `frontend/pages/roles/create.vue`
- **Features**:
  - Form for creating new roles
  - Fields: Name, Display Name, Description
  - Permission selection with checkboxes
  - Form validation and error handling

#### 3. Role Details (`/roles/[id]`)
- **File**: `frontend/pages/roles/[id].vue`
- **Features**:
  - Detailed role information display
  - Permission management with attach/detach functionality
  - Role statistics (permission count, user count)
  - Permission management modal
  - Quick actions for editing and permission management

#### 4. Edit Role (`/roles/[id]/edit`)
- **File**: `frontend/pages/roles/[id]/edit.vue`
- **Features**:
  - Form for updating role information
  - Permission management with checkboxes
  - Form validation and error handling

## Stores Created

### Users Store (`frontend/stores/users.ts`)
- **Features**:
  - User CRUD operations (create, read, update, delete)
  - Role assignment and removal
  - Pagination and filtering
  - Error handling
  - Loading states

### Roles Store (`frontend/stores/roles.ts`)
- **Features**:
  - Role CRUD operations (create, read, update, delete)
  - Permission attachment and detachment
  - Permission fetching (mock data for now)
  - Pagination and filtering
  - Error handling
  - Loading states

## Navigation Updates

### Main Navigation (`frontend/app.vue`)
- Added Users and Roles navigation links
- Permission-based visibility for navigation items
- Updated branding to "Admin Panel"

### Dashboard (`frontend/pages/index.vue`)
- Added user and role statistics cards
- Quick action buttons for user and role management
- Permission-based visibility for actions

## Key Features

### Permission-Based Access Control
- All pages and actions check user permissions
- Navigation items only show if user has required permissions
- Action buttons are conditionally rendered based on permissions

### Responsive Design
- Mobile-friendly layouts
- Responsive tables and forms
- Touch-friendly buttons and interactions

### User Experience
- Loading states for all async operations
- Error handling with user-friendly messages
- Confirmation modals for destructive actions
- Form validation with real-time feedback
- Debounced search functionality

### Role Management
- **Attach Permissions**: Add permissions to existing roles without removing current ones
- **Detach Permissions**: Remove specific permissions from roles
- **Update Permissions**: Replace all permissions (used in edit forms)
- **Role Assignment**: Assign/remove roles from users

### User Management
- **Role Assignment**: Assign/remove roles from users
- **Password Management**: Optional password updates
- **User Verification**: Display email verification status

## API Integration

All pages integrate with the Laravel backend API:
- JWT authentication for all requests
- Proper error handling for API responses
- Loading states during API calls
- Form validation with server-side error display

## Styling

Uses Tailwind CSS with custom component classes:
- `.btn-primary`, `.btn-secondary`, `.btn-danger` for buttons
- `.input-field` for form inputs
- `.card` for content containers
- Responsive grid layouts
- Consistent spacing and typography

## Security

- All routes protected by authentication middleware
- Permission checks on all sensitive operations
- CSRF protection through JWT tokens
- Input validation and sanitization

## Future Enhancements

1. **Real Permission API**: Replace mock permissions with actual API endpoint
2. **Bulk Operations**: Add bulk user/role operations
3. **Advanced Filtering**: More sophisticated filtering options
4. **Audit Logs**: Track user and role changes
5. **Email Notifications**: Notify users of role changes
6. **Role Templates**: Predefined role templates for common use cases
