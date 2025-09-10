# User API Documentation

This document describes the User API endpoints for managing users in the Laravel application.

## Authentication

All User API endpoints require JWT authentication. Include the JWT token in the Authorization header:

```
Authorization: Bearer {your-jwt-token}
```

## Endpoints

### 1. Get Users List
- **URL**: `GET /api/users`
- **Description**: Retrieve a paginated list of users
- **Query Parameters**:
  - `search` (optional): Search in name or email
  - `role` (optional): Filter by role name
  - `sort_by` (optional): Sort field (name, email, created_at, updated_at)
  - `sort_order` (optional): Sort order (asc, desc)
  - `per_page` (optional): Number of items per page
  - `with_roles` (optional): Include user roles in response
- **Required Permission**: `users.view`

### 2. Create User
- **URL**: `POST /api/users`
- **Description**: Create a new user
- **Request Body**:
  ```json
  {
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "roles": [1, 2]
  }
  ```
- **Required Permission**: `users.create`

### 3. Get Specific User
- **URL**: `GET /api/users/{id}`
- **Description**: Retrieve details of a specific user
- **Query Parameters**:
  - `with_roles` (optional): Include user roles in response
- **Required Permission**: `users.view` or own profile

### 4. Update User
- **URL**: `PUT /api/users/{id}`
- **Description**: Update an existing user
- **Request Body**:
  ```json
  {
    "name": "John Smith",
    "email": "johnsmith@example.com",
    "password": "newpassword123",
    "password_confirmation": "newpassword123",
    "roles": [1, 3]
  }
  ```
- **Required Permission**: `users.update` or own profile

### 5. Delete User
- **URL**: `DELETE /api/users/{id}`
- **Description**: Delete a user (users cannot delete themselves)
- **Required Permission**: `users.delete`

### 6. Assign Roles to User
- **URL**: `POST /api/users/{id}/assign-roles`
- **Description**: Assign additional roles to a user
- **Request Body**:
  ```json
  {
    "roles": [1, 2]
  }
  ```
- **Required Permission**: `users.assign_roles`

### 7. Remove Roles from User
- **URL**: `DELETE /api/users/{id}/remove-roles`
- **Description**: Remove specific roles from a user
- **Request Body**:
  ```json
  {
    "roles": [1, 2]
  }
  ```
- **Required Permission**: `users.remove_roles`

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

## User Resource Structure

```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "email_verified_at": "2024-01-15T10:30:00.000000Z",
  "created_at": "2024-01-15T10:30:00.000000Z",
  "updated_at": "2024-01-15T10:30:00.000000Z",
  "roles": [
    {
      "id": 1,
      "name": "admin",
      "display_name": "Administrator",
      "permissions": [
        {
          "id": 1,
          "name": "users.view",
          "display_name": "View Users"
        }
      ]
    }
  ],
  "permissions": [
    {
      "id": 1,
      "name": "users.view",
      "display_name": "View Users"
    }
  ]
}
```

## Required Permissions

- `users.view`: View users list and individual users
- `users.create`: Create new users
- `users.update`: Update existing users
- `users.delete`: Delete users
- `users.assign_roles`: Assign roles to users
- `users.remove_roles`: Remove roles from users

## Notes

- Users can always view and update their own profile regardless of permissions
- Users cannot delete themselves
- Password confirmation is required when creating or updating passwords
- Email addresses must be unique
- Passwords must be at least 8 characters long

## Role Management in User Update

The `PUT /api/users/{id}` endpoint supports role management as part of the user update operation. This provides a convenient way to update both user data and roles in a single API call.

### Role Update Behavior

When updating a user with roles:

1. **Complete Role Replacement**: The `roles` array completely replaces the user's existing roles
2. **Empty Array**: Passing an empty `roles` array removes all roles from the user
3. **Null/Omitted**: If `roles` is not provided, existing roles remain unchanged

### Examples

#### Update User with New Roles
```bash
PUT /api/users/1
{
  "name": "John Smith",
  "email": "johnsmith@example.com",
  "roles": [1, 2, 3]
}
```
This will:
- Update the user's name and email
- Replace all existing roles with roles [1, 2, 3]

#### Update Only Roles (Keep Other Data Unchanged)
```bash
PUT /api/users/1
{
  "name": "John Smith",
  "email": "johnsmith@example.com",
  "roles": [2, 4]
}
```
This will:
- Keep the user's name and email unchanged
- Replace all existing roles with roles [2, 4]

#### Remove All Roles
```bash
PUT /api/users/1
{
  "name": "John Smith",
  "email": "johnsmith@example.com",
  "roles": []
}
```
This will:
- Update the user's name and email
- Remove all roles from the user

#### Update User Data Only (Keep Roles Unchanged)
```bash
PUT /api/users/1
{
  "name": "John Smith",
  "email": "johnsmith@example.com"
}
```
This will:
- Update the user's name and email
- Keep all existing roles unchanged

### Response

The response includes the updated user with their current roles:

```json
{
  "code": 200,
  "status": "success",
  "message": "User updated successfully",
  "data": {
    "id": 1,
    "name": "John Smith",
    "email": "johnsmith@example.com",
    "roles": [
      {
        "id": 2,
        "name": "manager",
        "display_name": "Manager",
        "permissions": [...]
      },
      {
        "id": 4,
        "name": "editor",
        "display_name": "Editor",
        "permissions": [...]
      }
    ],
    "permissions": [...]
  }
}
```

### Benefits

- **Single API Call**: Update user data and roles in one request
- **Atomic Operation**: All changes are wrapped in a database transaction
- **Flexible**: Can update just data, just roles, or both
- **Consistent**: Uses the same validation and authorization as other endpoints
