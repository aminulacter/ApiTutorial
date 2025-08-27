# Product API - Swagger Documentation

This document provides comprehensive Swagger/OpenAPI 3.0 documentation for the Product API.

## 📋 Overview

The Product API provides endpoints for managing products with the following features:
- ✅ JWT Authentication
- ✅ CRUD Operations (Create, Read, Update, Delete)
- ✅ Image Upload Support
- ✅ Advanced Filtering & Search
- ✅ Pagination
- ✅ Role-based Authorization
- ✅ Validation & Error Handling

## 🚀 Quick Start

### 1. **View Swagger Documentation**

You can view the interactive Swagger documentation using one of these methods:

#### Option A: Swagger UI (Recommended)
1. Copy the contents of `swagger.json`
2. Go to [Swagger Editor](https://editor.swagger.io/)
3. Paste the JSON content
4. View the interactive documentation

#### Option B: Local Swagger UI
1. Install Swagger UI locally
2. Serve the `swagger.json` file
3. Access the documentation in your browser

### 2. **Authentication**

All endpoints require JWT authentication:

```bash
# Get JWT token first
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "password": "password"}'

# Use token in subsequent requests
curl -X GET http://localhost:8000/api/products \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

## 📚 API Endpoints

### **Products**

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/products` | List products with filtering | ✅ |
| POST | `/api/products` | Create new product | ✅ |
| GET | `/api/products/{id}` | Get specific product | ✅ |
| PUT | `/api/products/{id}` | Update product | ✅ |
| DELETE | `/api/products/{id}` | Delete product | ✅ |
| GET | `/api/products/categories` | Get available categories | ✅ |
| GET | `/api/products/brands` | Get available brands | ✅ |

## 🔍 Query Parameters

### **Product Listing (`GET /api/products`)**

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `category` | string | Filter by category | `Electronics` |
| `brand` | string | Filter by brand | `Apple` |
| `min_price` | number | Minimum price | `10.00` |
| `max_price` | number | Maximum price | `1000.00` |
| `in_stock` | string | Stock availability | `true` or `false` |
| `is_active` | boolean | Active status | `true` |
| `search` | string | Search in name/description/SKU | `iPhone` |
| `sort_by` | string | Sort field | `name`, `price`, `created_at` |
| `sort_order` | string | Sort order | `asc` or `desc` |
| `per_page` | integer | Items per page | `10` |

### **Example Requests**

```bash
# Get all products
GET /api/products

# Filter by category and price range
GET /api/products?category=Electronics&min_price=100&max_price=500

# Search and sort
GET /api/products?search=iPhone&sort_by=price&sort_order=asc

# Pagination
GET /api/products?page=2&per_page=20
```

## 📝 Request/Response Examples

### **Create Product**

```bash
curl -X POST http://localhost:8000/api/products \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -F "name=iPhone 15 Pro" \
  -F "description=Latest iPhone" \
  -F "price=999.99" \
  -F "stock=100" \
  -F "category=Electronics" \
  -F "brand=Apple" \
  -F "image=@product-image.jpg"
```

**Response:**
```json
{
  "code": 200,
  "status": "success",
  "message": "Product created successfully",
  "data": {
    "id": 1,
    "name": "iPhone 15 Pro",
    "description": "Latest iPhone",
    "price": 999.99,
    "stock": 100,
    "sku": null,
    "image": "products/1234567890_abc123.jpg",
    "category": "Electronics",
    "brand": "Apple",
    "is_active": true,
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T10:30:00.000000Z"
  }
}
```

### **Update Product**

```bash
curl -X PUT http://localhost:8000/api/products/1 \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -F "name=iPhone 15 Pro Max" \
  -F "price=1099.99" \
  -F "stock=50" \
  -F "image=@new-image.jpg"
```

### **List Products with Filters**

```bash
curl -X GET "http://localhost:8000/api/products?category=Electronics&min_price=500&sort_by=price&sort_order=desc" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

**Response:**
```json
{
  "code": 200,
  "status": "success",
  "message": "Products fetched successfully",
  "data": {
    "products": [
      {
        "id": 1,
        "name": "iPhone 15 Pro",
        "price": 999.99,
        "stock": 100,
        "category": "Electronics",
        "brand": "Apple",
        "is_active": true,
        "created_at": "2024-01-15T10:30:00.000000Z",
        "updated_at": "2024-01-15T10:30:00.000000Z"
      }
    ],
    "current_page": 1,
    "last_page": 5,
    "per_page": 10,
    "total": 50,
    "next_page_url": "http://localhost:8000/api/products?page=2",
    "prev_page_url": null,
    "filters_applied": {
      "category": "Electronics",
      "min_price": "500",
      "sort_by": "price",
      "sort_order": "desc"
    }
  }
}
```

## ⚠️ Error Responses

### **Validation Error (400)**
```json
{
  "code": 422,
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "name": ["Product name is required."],
    "price": ["Product price must be a valid number."],
    "stock": ["Product stock cannot be negative."]
  }
}
```

### **Unauthorized Error (403)**
```json
{
  "code": 403,
  "status": "error",
  "message": "You are not authorized to create products"
}
```

### **Not Found Error (404)**
```json
{
  "code": 404,
  "status": "error",
  "message": "Resource not found"
}
```

### **Server Error (500)**
```json
{
  "code": 500,
  "status": "error",
  "message": "Error creating product",
  "errors": ["Database connection failed"]
}
```

## 🔐 Authentication & Authorization

### **JWT Token Format**
```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

### **Required Permissions**

| Action | Required Permission |
|--------|-------------------|
| View Products | `products.view` |
| Create Products | `products.create` |
| Update Products | `products.edit` |
| Delete Products | `products.create` (Note: This might be a bug in the policy) |

### **Role-based Access**
- **Admin Role**: Full access to all operations
- **User Role**: Limited access based on permissions

## 📁 File Upload

### **Supported Image Formats**
- JPEG (.jpg, .jpeg)
- PNG (.png)
- GIF (.gif)
- SVG (.svg)

### **File Size Limit**
- Maximum: 2MB (2048 KB)

### **Image Storage**
- Images are stored in `storage/app/public/products/`
- File naming: `{timestamp}_{uniqid}.{extension}`
- Example: `products/1234567890_abc123.jpg`

## 🧪 Testing

The API includes comprehensive unit tests. Run them with:

```bash
# Run all ProductController tests
php artisan test tests/Feature/ProductControllerTest.php

# Run specific test
php artisan test --filter=test_store_product_success
```

## 📋 Validation Rules

### **Product Creation/Update**

| Field | Rules | Description |
|-------|-------|-------------|
| `name` | required, string, max:255 | Product name |
| `description` | nullable, string, max:1000 | Product description |
| `price` | required, numeric, min:0, max:999999.99 | Product price |
| `stock` | required, integer, min:0, max:999999 | Stock quantity |
| `sku` | nullable, string, max:50, unique | Stock Keeping Unit |
| `image` | nullable, image, mimes:jpeg,png,jpg,gif,svg, max:2048 | Product image |
| `category` | nullable, string, max:100 | Product category |
| `brand` | nullable, string, max:100 | Product brand |
| `is_active` | boolean | Active status |

## 🔧 Configuration

### **Environment Variables**
```env
# JWT Configuration
JWT_SECRET=your-jwt-secret
JWT_TTL=60

# File Storage
FILESYSTEM_DISK=public
```

### **Storage Configuration**
Make sure to create a symbolic link for public storage:
```bash
php artisan storage:link
```

## 📖 Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [JWT Authentication](https://jwt-auth.readthedocs.io/)
- [OpenAPI Specification](https://swagger.io/specification/)
- [Swagger UI](https://swagger.io/tools/swagger-ui/)

## 🤝 Support

For API support or questions:
- Email: support@example.com
- Documentation: https://example.com/docs
- Issues: https://github.com/your-repo/issues

---

**Note**: This API follows RESTful conventions and returns consistent JSON responses with HTTP status codes and error details in the response body.
