<?php

/**
 * Swagger/OpenAPI 3.0 Definitions for Product API
 * 
 * This file contains all the schema definitions and components for the Product API
 * Add these definitions to your main swagger.json or swagger.yaml file
 */

return [
    'components' => [
        'schemas' => [
            'Product' => [
                'type' => 'object',
                'required' => ['id', 'name', 'price', 'stock', 'is_active', 'created_at', 'updated_at'],
                'properties' => [
                    'id' => [
                        'type' => 'integer',
                        'example' => 1,
                        'description' => 'Unique product identifier'
                    ],
                    'name' => [
                        'type' => 'string',
                        'maxLength' => 255,
                        'example' => 'iPhone 15 Pro',
                        'description' => 'Product name'
                    ],
                    'description' => [
                        'type' => 'string',
                        'maxLength' => 1000,
                        'nullable' => true,
                        'example' => 'Latest iPhone with advanced features',
                        'description' => 'Product description'
                    ],
                    'price' => [
                        'type' => 'number',
                        'format' => 'float',
                        'minimum' => 0,
                        'maximum' => 999999.99,
                        'example' => 999.99,
                        'description' => 'Product price'
                    ],
                    'stock' => [
                        'type' => 'integer',
                        'minimum' => 0,
                        'maximum' => 999999,
                        'example' => 100,
                        'description' => 'Available stock quantity'
                    ],
                    'sku' => [
                        'type' => 'string',
                        'maxLength' => 50,
                        'nullable' => true,
                        'example' => 'IPH15PRO-001',
                        'description' => 'Stock Keeping Unit'
                    ],
                    'image' => [
                        'type' => 'string',
                        'nullable' => true,
                        'example' => 'products/1234567890_abc123.jpg',
                        'description' => 'Product image file path'
                    ],
                    'category' => [
                        'type' => 'string',
                        'maxLength' => 100,
                        'nullable' => true,
                        'example' => 'Electronics',
                        'description' => 'Product category'
                    ],
                    'brand' => [
                        'type' => 'string',
                        'maxLength' => 100,
                        'nullable' => true,
                        'example' => 'Apple',
                        'description' => 'Product brand'
                    ],
                    'is_active' => [
                        'type' => 'boolean',
                        'example' => true,
                        'description' => 'Product active status'
                    ],
                    'created_at' => [
                        'type' => 'string',
                        'format' => 'date-time',
                        'example' => '2024-01-15T10:30:00.000000Z',
                        'description' => 'Product creation timestamp'
                    ],
                    'updated_at' => [
                        'type' => 'string',
                        'format' => 'date-time',
                        'example' => '2024-01-15T10:30:00.000000Z',
                        'description' => 'Product last update timestamp'
                    ]
                ]
            ],
            'ProductCreate' => [
                'type' => 'object',
                'required' => ['name', 'price', 'stock'],
                'properties' => [
                    'name' => [
                        'type' => 'string',
                        'maxLength' => 255,
                        'example' => 'iPhone 15 Pro',
                        'description' => 'Product name'
                    ],
                    'description' => [
                        'type' => 'string',
                        'maxLength' => 1000,
                        'nullable' => true,
                        'example' => 'Latest iPhone with advanced features',
                        'description' => 'Product description'
                    ],
                    'price' => [
                        'type' => 'number',
                        'format' => 'float',
                        'minimum' => 0,
                        'maximum' => 999999.99,
                        'example' => 999.99,
                        'description' => 'Product price'
                    ],
                    'stock' => [
                        'type' => 'integer',
                        'minimum' => 0,
                        'maximum' => 999999,
                        'example' => 100,
                        'description' => 'Available stock quantity'
                    ],
                    'sku' => [
                        'type' => 'string',
                        'maxLength' => 50,
                        'nullable' => true,
                        'example' => 'IPH15PRO-001',
                        'description' => 'Stock Keeping Unit (must be unique)'
                    ],
                    'image' => [
                        'type' => 'string',
                        'format' => 'binary',
                        'description' => 'Product image file (jpeg, png, jpg, gif, svg, max 2MB)'
                    ],
                    'category' => [
                        'type' => 'string',
                        'maxLength' => 100,
                        'nullable' => true,
                        'example' => 'Electronics',
                        'description' => 'Product category'
                    ],
                    'brand' => [
                        'type' => 'string',
                        'maxLength' => 100,
                        'nullable' => true,
                        'example' => 'Apple',
                        'description' => 'Product brand'
                    ],
                    'is_active' => [
                        'type' => 'boolean',
                        'example' => true,
                        'description' => 'Product active status'
                    ]
                ]
            ],
            'ProductUpdate' => [
                'type' => 'object',
                'required' => ['name', 'price', 'stock'],
                'properties' => [
                    'name' => [
                        'type' => 'string',
                        'maxLength' => 255,
                        'example' => 'iPhone 15 Pro Max',
                        'description' => 'Product name'
                    ],
                    'description' => [
                        'type' => 'string',
                        'maxLength' => 1000,
                        'nullable' => true,
                        'example' => 'Updated description',
                        'description' => 'Product description'
                    ],
                    'price' => [
                        'type' => 'number',
                        'format' => 'float',
                        'minimum' => 0,
                        'maximum' => 999999.99,
                        'example' => 1099.99,
                        'description' => 'Product price'
                    ],
                    'stock' => [
                        'type' => 'integer',
                        'minimum' => 0,
                        'maximum' => 999999,
                        'example' => 50,
                        'description' => 'Available stock quantity'
                    ],
                    'sku' => [
                        'type' => 'string',
                        'maxLength' => 50,
                        'nullable' => true,
                        'example' => 'IPH15PROMAX-001',
                        'description' => 'Stock Keeping Unit (must be unique)'
                    ],
                    'image' => [
                        'type' => 'string',
                        'format' => 'binary',
                        'description' => 'New product image file (jpeg, png, jpg, gif, svg, max 2MB)'
                    ],
                    'category' => [
                        'type' => 'string',
                        'maxLength' => 100,
                        'nullable' => true,
                        'example' => 'Electronics',
                        'description' => 'Product category'
                    ],
                    'brand' => [
                        'type' => 'string',
                        'maxLength' => 100,
                        'nullable' => true,
                        'example' => 'Apple',
                        'description' => 'Product brand'
                    ],
                    'is_active' => [
                        'type' => 'boolean',
                        'example' => true,
                        'description' => 'Product active status'
                    ]
                ]
            ],
            'ProductList' => [
                'type' => 'object',
                'properties' => [
                    'products' => [
                        'type' => 'array',
                        'items' => ['$ref' => '#/components/schemas/Product']
                    ],
                    'current_page' => [
                        'type' => 'integer',
                        'example' => 1,
                        'description' => 'Current page number'
                    ],
                    'last_page' => [
                        'type' => 'integer',
                        'example' => 5,
                        'description' => 'Last page number'
                    ],
                    'per_page' => [
                        'type' => 'integer',
                        'example' => 10,
                        'description' => 'Items per page'
                    ],
                    'total' => [
                        'type' => 'integer',
                        'example' => 50,
                        'description' => 'Total number of items'
                    ],
                    'next_page_url' => [
                        'type' => 'string',
                        'nullable' => true,
                        'example' => 'http://api.example.com/products?page=2',
                        'description' => 'URL for next page'
                    ],
                    'prev_page_url' => [
                        'type' => 'string',
                        'nullable' => true,
                        'example' => null,
                        'description' => 'URL for previous page'
                    ],
                    'filters_applied' => [
                        'type' => 'object',
                        'description' => 'Applied filters',
                        'properties' => [
                            'category' => ['type' => 'string'],
                            'brand' => ['type' => 'string'],
                            'min_price' => ['type' => 'number'],
                            'max_price' => ['type' => 'number'],
                            'in_stock' => ['type' => 'string'],
                            'is_active' => ['type' => 'boolean'],
                            'search' => ['type' => 'string'],
                            'sort_by' => ['type' => 'string'],
                            'sort_order' => ['type' => 'string']
                        ]
                    ]
                ]
            ],
            'ApiResponse' => [
                'type' => 'object',
                'properties' => [
                    'code' => [
                        'type' => 'integer',
                        'example' => 200,
                        'description' => 'Response code'
                    ],
                    'status' => [
                        'type' => 'string',
                        'enum' => ['success', 'error'],
                        'example' => 'success',
                        'description' => 'Response status'
                    ],
                    'message' => [
                        'type' => 'string',
                        'example' => 'Operation completed successfully',
                        'description' => 'Response message'
                    ],
                    'data' => [
                        'type' => 'object',
                        'description' => 'Response data (varies by endpoint)'
                    ],
                    'errors' => [
                        'type' => 'array',
                        'items' => ['type' => 'string'],
                        'description' => 'Error details (only present on error responses)'
                    ]
                ]
            ],
            'ValidationError' => [
                'type' => 'object',
                'properties' => [
                    'code' => [
                        'type' => 'integer',
                        'example' => 422,
                        'description' => 'Validation error code'
                    ],
                    'status' => [
                        'type' => 'string',
                        'example' => 'error',
                        'description' => 'Error status'
                    ],
                    'message' => [
                        'type' => 'string',
                        'example' => 'Validation failed',
                        'description' => 'Error message'
                    ],
                    'errors' => [
                        'type' => 'object',
                        'description' => 'Field-specific validation errors',
                        'additionalProperties' => [
                            'type' => 'array',
                            'items' => ['type' => 'string']
                        ]
                    ]
                ]
            ],
            'UnauthorizedError' => [
                'type' => 'object',
                'properties' => [
                    'code' => [
                        'type' => 'integer',
                        'example' => 403,
                        'description' => 'Unauthorized error code'
                    ],
                    'status' => [
                        'type' => 'string',
                        'example' => 'error',
                        'description' => 'Error status'
                    ],
                    'message' => [
                        'type' => 'string',
                        'example' => 'You are not authorized to perform this action',
                        'description' => 'Error message'
                    ]
                ]
            ],
            'NotFoundError' => [
                'type' => 'object',
                'properties' => [
                    'code' => [
                        'type' => 'integer',
                        'example' => 404,
                        'description' => 'Not found error code'
                    ],
                    'status' => [
                        'type' => 'string',
                        'example' => 'error',
                        'description' => 'Error status'
                    ],
                    'message' => [
                        'type' => 'string',
                        'example' => 'Resource not found',
                        'description' => 'Error message'
                    ]
                ]
            ],
            'ServerError' => [
                'type' => 'object',
                'properties' => [
                    'code' => [
                        'type' => 'integer',
                        'example' => 500,
                        'description' => 'Server error code'
                    ],
                    'status' => [
                        'type' => 'string',
                        'example' => 'error',
                        'description' => 'Error status'
                    ],
                    'message' => [
                        'type' => 'string',
                        'example' => 'Internal server error',
                        'description' => 'Error message'
                    ],
                    'errors' => [
                        'type' => 'array',
                        'items' => ['type' => 'string'],
                        'description' => 'Error details'
                    ]
                ]
            ]
        ],
        'securitySchemes' => [
            'bearerAuth' => [
                'type' => 'http',
                'scheme' => 'bearer',
                'bearerFormat' => 'JWT',
                'description' => 'Enter JWT token in the format: Bearer {token}'
            ]
        ],
        'parameters' => [
            'ProductId' => [
                'name' => 'id',
                'in' => 'path',
                'description' => 'Product ID',
                'required' => true,
                'schema' => [
                    'type' => 'integer',
                    'example' => 1
                ]
            ],
            'Page' => [
                'name' => 'page',
                'in' => 'query',
                'description' => 'Page number',
                'required' => false,
                'schema' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'default' => 1,
                    'example' => 1
                ]
            ],
            'PerPage' => [
                'name' => 'per_page',
                'in' => 'query',
                'description' => 'Number of items per page',
                'required' => false,
                'schema' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 100,
                    'default' => 10,
                    'example' => 10
                ]
            ],
            'SortBy' => [
                'name' => 'sort_by',
                'in' => 'query',
                'description' => 'Field to sort by',
                'required' => false,
                'schema' => [
                    'type' => 'string',
                    'enum' => ['name', 'price', 'created_at', 'updated_at'],
                    'default' => 'created_at',
                    'example' => 'created_at'
                ]
            ],
            'SortOrder' => [
                'name' => 'sort_order',
                'in' => 'query',
                'description' => 'Sort order',
                'required' => false,
                'schema' => [
                    'type' => 'string',
                    'enum' => ['asc', 'desc'],
                    'default' => 'desc',
                    'example' => 'desc'
                ]
            ]
        ]
    ],
    'tags' => [
        [
            'name' => 'Products',
            'description' => 'Product management operations',
            'externalDocs' => [
                'description' => 'Find out more about products',
                'url' => 'https://example.com/docs/products'
            ]
        ]
    ]
];
