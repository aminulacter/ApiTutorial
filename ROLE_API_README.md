# Role API Documentation

This document describes the Role API endpoints for managing roles and their permissions in the Laravel application.

## Authentication

All Role API endpoints require JWT authentication. Include the JWT token in the Authorization header:

```
Authorization: Bearer {your-jwt-token}
```

## Endpoints

### 1. Get Roles List
- **URL**: `GET /api/roles`
- **Description**: Retrieve a paginated list of roles
- **Query Parameters**:
  - `search` (optional): Search in name or display_name
  - `sort_by` (optional): Sort field (name, display_name, created_at, updated_at)
  - `sort_order` (optional): Sort order (asc, desc)
  - `per_page` (optional): Number of items per page
  - `with_permissions` (optional): Include role permissions in response
- **Required Permission**: `roles.view`

### 2. Create Role
- **URL**: `POST /api/roles`
- **Description**: Create a new role with optional permission assignments
- **Request Body**:
  ```json
  {
    "name": "manager",
    "display_name": "Manager",
    "description": "Manager role with limited access",
    "permissions": [1, 2, 3]
  }
  ```
- **Required Permission**: `roles.create`

### 3. Get Specific Role
- **URL**: `GET /api/roles/{id}`
- **Description**: Retrieve details of a specific role
- **Query Parameters**:
  - `with_permissions` (optional): Include role permissions in response
- **Required Permission**: `roles.view`

### 4. Update Role
- **URL**: `PUT /api/roles/{id}`
- **Description**: Update an existing role with optional permission assignments
- **Request Body**:
  ```json
  {
    "name": "senior_manager",
    "display_name": "Senior Manager",
    "description": "Senior manager role with extended access",
    "permissions": [1, 2, 4, 5]
  }
  ```
- **Required Permission**: `roles.update`

### 5. Delete Role
- **URL**: `DELETE /api/roles/{id}`
- **Description**: Delete a role and remove all permission assignments
- **Required Permission**: `roles.delete`

### 6. Attach Permissions to Role
- **URL**: `POST /api/roles/{id}/attach-permissions`
- **Description**: Attach additional permissions to a role without removing existing ones
- **Request Body**:
  ```json
  {
    "permissions": [1, 2, 3]
  }
  ```
- **Required Permission**: `roles.assign_permissions`

### 7. Detach Permissions from Role
- **URL**: `DELETE /api/roles/{id}/detach-permissions`
- **Description**: Remove specific permissions from a role
- **Request Body**:
  ```json
  {
    "permissions": [1, 2, 3]
  }
  ```
- **Required Permission**: `roles.remove_permissions`

## Response Format

All API responses follow this format:

### Success Response
```json
{
  "code": 200,
  "status": "success",
  "message": "Operation completed successfully",
  "data": {
    // Response data here
  }
}
```

### Error Response
```json
{
  "code": 403,
  "status": "error",
  "message": "You are not authorized to perform this action",
  "errors": []
}
```

## Role Resource Structure

```json
{
  "id": 1,
  "name": "admin",
  "display_name": "Administrator",
  "description": "Administrator role with full access",
  "created_at": "2024-01-15T10:30:00.000000Z",
  "updated_at": "2024-01-15T10:30:00.000000Z",
  "permissions": [
    {
      "id": 1,
      "name": "users.view",
      "display_name": "View Users",
      "description": "Permission to view users"
    }
  ],
  "users_count": 5,
  "permissions_count": 10
}
```

## Permission Management in Role Update

The `PUT /api/roles/{id}` endpoint supports permission management as part of the role update operation.

### Permission Update Behavior

When updating a role with permissions:

1. **Complete Permission Replacement**: The `permissions` array completely replaces the role's existing permissions
2. **Empty Array**: Passing an empty `permissions` array removes all permissions from the role
3. **Null/Omitted**: If `permissions` is not provided, existing permissions remain unchanged

### Examples

#### Update Role with New Permissions
```bash
PUT /api/roles/1
{
  "name": "senior_manager",
  "display_name": "Senior Manager",
  "permissions": [1, 2, 3, 4]
}
```
This will:
- Update the role's name and display_name
- Replace all existing permissions with permissions [1, 2, 3, 4]

#### Update Only Permissions (Keep Other Data Unchanged)
```bash
PUT /api/roles/1
{
  "name": "manager",
  "display_name": "Manager",
  "permissions": [2, 4, 5]
}
```
This will:
- Keep the role's name and display_name unchanged
- Replace all existing permissions with permissions [2, 4, 5]

#### Remove All Permissions
```bash
PUT /api/roles/1
{
  "name": "basic_role",
  "display_name": "Basic Role",
  "permissions": []
}
```
This will:
- Update the role's name and display_name
- Remove all permissions from the role

#### Update Role Data Only (Keep Permissions Unchanged)
```bash
PUT /api/roles/1
{
  "name": "updated_manager",
  "display_name": "Updated Manager"
}
```
This will:
- Update the role's name and display_name
- Keep all existing permissions unchanged

## Attach vs Detach vs Update Permissions

### Attach Permissions (`POST /api/roles/{id}/attach-permissions`)
- **Purpose**: Add permissions to existing ones
- **Behavior**: Uses `syncWithoutDetaching()` - keeps existing permissions and adds new ones
- **Use Case**: When you want to grant additional permissions without removing current ones

### Detach Permissions (`DELETE /api/roles/{id}/detach-permissions`)
- **Purpose**: Remove specific permissions
- **Behavior**: Uses `detach()` - removes only specified permissions
- **Use Case**: When you want to revoke specific permissions

### Update Permissions (`PUT /api/roles/{id}` with permissions)
- **Purpose**: Replace all permissions
- **Behavior**: Uses `sync()` - completely replaces existing permissions
- **Use Case**: When you want to redefine the entire permission set

## Required Permissions

- `roles.view`: View roles list and individual roles
- `roles.create`: Create new roles
- `roles.update`: Update existing roles
- `roles.delete`: Delete roles
- `roles.assign_permissions`: Attach permissions to roles
- `roles.remove_permissions`: Detach permissions from roles

## Notes

- Role names must be unique
- Display names are optional but recommended for better UX
- Permissions are validated to ensure they exist
- All operations are wrapped in database transactions
- Role deletion removes all permission assignments
- Permission operations are atomic (all succeed or all fail)
