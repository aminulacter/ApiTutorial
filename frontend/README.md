# Nuxt Frontend - Product Management System

A modern, responsive Nuxt 3 application for managing products with authentication and CRUD operations.

## Features

### Authentication
- **Login/Register**: User authentication with JWT tokens
- **Protected Routes**: Middleware-based route protection
- **Persistent Sessions**: Token storage in localStorage with automatic restoration
- **Role-based Access**: Support for user roles and permissions

### Product Management
- **Dashboard**: Overview with product statistics and recent products
- **Product Listing**: Grid view with advanced filtering and search
- **Product Creation**: Form with image upload support
- **Product Editing**: Inline editing with form validation
- **Product Deletion**: Confirmation modal for safe deletion
- **Image Upload**: Support for product images with validation

### Advanced Features
- **Real-time Search**: Debounced search functionality
- **Advanced Filtering**: Category, brand, price range, stock status
- **Pagination**: Server-side pagination with navigation
- **Responsive Design**: Mobile-first design with Tailwind CSS
- **Loading States**: Skeleton loading and spinners
- **Error Handling**: Comprehensive error messages and validation

## Tech Stack

- **Framework**: Nuxt 3
- **Language**: TypeScript
- **Styling**: Tailwind CSS
- **State Management**: Pinia
- **HTTP Client**: Nuxt's built-in $fetch
- **Icons**: Heroicons
- **UI Components**: Headless UI

## Project Structure

```
frontend/
├── assets/
│   └── css/
│       └── main.css          # Global styles and Tailwind imports
├── components/               # Reusable Vue components
├── middleware/
│   └── auth.ts              # Authentication middleware
├── pages/
│   ├── index.vue            # Dashboard page
│   ├── login.vue            # Login page
│   ├── register.vue         # Registration page
│   └── products/
│       ├── index.vue        # Products listing
│       ├── create.vue       # Create product
│       └── [id].vue         # Product detail/edit
├── stores/
│   ├── auth.ts             # Authentication store with localStorage persistence
│   └── products.ts         # Products store
├── app.vue                 # Main app layout
├── nuxt.config.ts          # Nuxt configuration
├── package.json            # Dependencies
├── tailwind.config.js      # Tailwind configuration
└── README.md               # This file
```

## Getting Started

### Prerequisites

- Node.js 16+ 
- npm or yarn
- Laravel API running on `http://localhost:8000`

### Installation

1. **Install dependencies**:
   ```bash
   cd frontend
   npm install
   ```

2. **Environment Setup**:
   Create a `.env` file in the frontend directory:
   ```env
   API_BASE=http://localhost:8000/api
   ```

3. **Start development server**:
   ```bash
   npm run dev
   ```

4. **Build for production**:
   ```bash
   npm run build
   ```

## API Integration

The frontend integrates with the Laravel API endpoints:

### Authentication Endpoints
- `POST /api/register` - User registration
- `POST /api/login` - User login
- `POST /api/logout` - User logout
- `GET /api/user` - Get current user

### Product Endpoints
- `GET /api/products` - List products with filtering
- `POST /api/products` - Create product
- `GET /api/products/{id}` - Get product details
- `POST /api/products/{id}` - Update product
- `DELETE /api/products/{id}` - Delete product
- `GET /api/products/categories` - Get categories
- `GET /api/products/brands` - Get brands

## State Management

### Auth Store (`stores/auth.ts`)
Manages user authentication state with localStorage persistence:
- **User information**: Stored in localStorage as `auth_user`
- **JWT token**: Stored in localStorage as `auth_token`
- **Roles and permissions**: Stored in localStorage as `auth_roles` and `auth_permissions`
- **Login/logout/register actions**: Handle API calls and localStorage updates
- **Automatic restoration**: On app startup, restores auth state from localStorage
- **Token validation**: Verifies stored token by calling `/api/user` endpoint

### Products Store (`stores/products.ts`)
Manages product data:
- Product list with pagination
- Current product details
- CRUD operations
- Filtering and search

## Token Persistence

The application implements robust token persistence using localStorage:

### How It Works
1. **Login/Register**: Token and user data are stored in localStorage
2. **Page Refresh**: Auth store automatically restores data from localStorage
3. **Token Validation**: On restoration, validates token with API call
4. **Logout**: Clears all localStorage data

### localStorage Keys
- `auth_token`: JWT token
- `auth_user`: User information (JSON string)
- `auth_roles`: User roles (JSON string)
- `auth_permissions`: User permissions (JSON string)

### Benefits
- **No login required on page refresh**: User stays logged in
- **Automatic token validation**: Ensures stored token is still valid
- **Secure logout**: Properly clears all stored data
- **Cross-tab persistence**: Auth state persists across browser tabs

## Styling

The application uses Tailwind CSS with custom components:

### Custom Classes
- `.btn-primary` - Primary button styling
- `.btn-secondary` - Secondary button styling
- `.btn-danger` - Danger button styling
- `.input-field` - Form input styling
- `.card` - Card container styling

### Color Scheme
- Primary: Blue (`primary-600`)
- Success: Green
- Warning: Yellow
- Danger: Red
- Neutral: Gray

## Features in Detail

### Authentication Flow
1. User visits protected route
2. Middleware checks authentication
3. Redirects to login if not authenticated
4. After login, stores token in localStorage
5. Redirects to intended page
6. On page refresh, automatically restores auth state

### Product Management Flow
1. **Listing**: Fetches products with pagination and filters
2. **Creation**: Form validation and image upload
3. **Editing**: Inline form with current data
4. **Deletion**: Confirmation modal for safety

### Image Upload
- Supports PNG, JPG, GIF formats
- 2MB file size limit
- Client-side validation
- Preview functionality
- FormData submission

## Development

### Adding New Features
1. Create new page in `pages/` directory
2. Add route protection with middleware if needed
3. Create store actions for API calls
4. Add components for reusable UI elements

### Styling Guidelines
- Use Tailwind utility classes
- Create custom components for repeated patterns
- Follow mobile-first responsive design
- Maintain consistent spacing and colors

### Error Handling
- Use try-catch blocks for API calls
- Display user-friendly error messages
- Validate forms on both client and server
- Handle network errors gracefully

## Deployment

### Build Process
```bash
npm run build
npm run generate  # For static hosting
```

### Environment Variables
- `API_BASE`: Backend API URL
- `NODE_ENV`: Environment (development/production)

## Contributing

1. Follow Vue 3 Composition API patterns
2. Use TypeScript for type safety
3. Write clean, readable code
4. Test all features thoroughly
5. Update documentation as needed

## Troubleshooting

### Common Issues

**API Connection Errors**
- Check if Laravel API is running
- Verify API_BASE environment variable
- Check CORS configuration in Laravel

**Authentication Issues**
- Clear localStorage and re-login
- Check JWT token expiration
- Verify API authentication middleware

**Token Persistence Issues**
- Check browser localStorage support
- Verify localStorage is not disabled
- Check for browser privacy settings blocking localStorage

**Image Upload Problems**
- Check file size limits
- Verify supported formats
- Check server storage permissions

## License

This project is part of the Product Management System tutorial.
