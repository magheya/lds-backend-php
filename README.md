# LDS Backend PHP API Documentation

This documentation provides comprehensive information about the Site Association PHP backend API, including setup instructions, authentication details, and endpoint documentation for integrating with JavaScript clients.

## Table of Contents
- [Overview](#overview)
- [Project Structure](#project-structure)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
  - [Using Apache (XAMPP/WAMP)](#using-apache-xamppwamp)
  - [Using PHP Built-in Server](#using-php-built-in-server)
  - [Environment Setup](#environment-setup)
- [Database Setup](#database-setup)
  - [Database Schema](#database-schema)
  - [Seeding Test Data](#seeding-test-data)
- [Authentication](#authentication)
  - [Token-Based Authentication](#token-based-authentication)
  - [Admin User Management](#admin-user-management)
- [API Endpoints](#api-endpoints)
  - [Products](#products)
  - [Events](#events)
  - [Registrations](#registrations)
  - [Donations](#donations)
  - [Messages](#messages)
  - [Orders](#orders)
  - [Activities](#activities)
- [Error Handling](#error-handling)
- [File Uploads](#file-uploads)
- [Frontend Integration](#frontend-integration)
  - [JavaScript Examples](#javascript-examples)
  - [CORS Configuration](#cors-configuration)
- [Testing](#testing)
  - [Using Postman](#using-postman)
  - [API Debugging](#api-debugging)
- [Deployment](#deployment)
  - [Production Considerations](#production-considerations)
  - [Security Best Practices](#security-best-practices)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)
- [License](#license)

## Overview

This API serves as the backend for the Site Association project, providing endpoints for managing products, events, registrations, donations, messages, and orders. It includes token-based authentication for admin-only endpoints and handles file uploads for images.

The API is built with pure PHP (no framework) and uses SQLite for data storage, making it easy to deploy without complex database setup. It follows RESTful principles and is designed to be integrated with JavaScript frontends.

## Project Structure

The project follows a simple structure:

```
lds-backend-php/
├── api/                    # API core files
│   ├── auth.php            # Authentication logic
│   ├── data-access.php     # Database operations
│   ├── database.php        # Database connection
│   ├── index.php           # Main API entry point
│   ├── init.php            # Database initialization
│   ├── upload-handler.php  # File upload handling
│   └── logs/               # Request and error logs
├── assets/                 # Static assets
│   └── uploads/            # Uploaded files (images)
├── db/                     # Database files
│   └── association.db      # SQLite database
├── README.md               # Documentation
└── seed.php                # Database seeding script
```

## Prerequisites

- PHP 7.4 or higher
- SQLite with PHP PDO extension enabled
- PHP GD library (for image processing)
- Apache or Nginx web server (optional, can use PHP built-in server)
- Write permissions for the web server on `db` and `assets/uploads` directories

### Required PHP Extensions

- PDO and PDO_SQLite
- GD (for image processing)
- JSON
- FileInfo

## Installation

### Using Apache (XAMPP/WAMP)

1. Clone or download this repository to your web server directory:
   ```bash
   git clone https://github.com/yourusername/lds-backend-php.git
   ```
   
   For XAMPP: `C:\xampp\htdocs\lds-backend-php`
   For WAMP: `C:\wamp\www\lds-backend-php`

2. Make sure the web server user has write permissions for `db` and `assets/uploads` directories:
   
   ```bash
   # Linux/macOS
   chmod -R 755 .
   chmod -R 777 db assets/uploads
   
   # Windows (via PowerShell with admin rights)
   icacls "db" /grant "Everyone:(OI)(CI)F" /T
   icacls "assets\uploads" /grant "Everyone:(OI)(CI)F" /T
   ```

3. Access the API via: `http://localhost/lds-backend-php/api/`

### Using PHP Built-in Server

You can run the API using PHP's built-in web server for development purposes:

1. Navigate to the project directory:
   ```bash
   cd path/to/lds-backend-php
   ```

2. Start the PHP built-in server:
   ```bash
   # Linux/macOS
   php -S localhost:8080 -t .
   
   # Windows
   php -S localhost:8080 -t .
   ```

3. Access the API via: `http://localhost:8080/api/`

### Environment Setup

1. The database will be automatically initialized on the first request to the API.

2. A default admin user will be created with:
   - Username: `admin`
   - Password: `admin123`

3. You can seed the database with test data by running:
   ```bash
   # Linux/macOS
   php seed.php
   
   # Windows
   php seed.php
   ```

## Database Setup

The application uses SQLite which doesn't require a separate database server. The database file will be created automatically in the `db` directory.

### Database Schema

The API uses SQLite and automatically creates the following tables:

- **products**: Stores product information with name, price, description, etc.
- **product_sizes**: Stores available sizes for each product
- **events**: Stores event information with name, date, description, etc.
- **registrations**: Stores event registration information
- **donations**: Stores donation information
- **messages**: Stores contact messages from users
- **orders**: Stores order information
- **order_items**: Stores individual items within orders
- **admins**: Stores admin user information
- **auth_tokens**: Stores authentication tokens for admin users

You can view the complete schema in the `init.php` file, which contains all the table creation statements.

### Seeding Test Data

To populate the database with sample data for testing, run the seeding script:

```bash
# Linux/macOS
php seed.php

# Windows
php seed.php
```

This will create sample products, events, registrations, donations, messages, and orders in the database. The script is designed to be idempotent, so it won't duplicate data if run multiple times.

## Authentication

The API uses token-based authentication to protect admin endpoints.

### Token-Based Authentication

Authentication is implemented using secure tokens that are stored in the database and validated on each request to a protected endpoint.

#### Login

```
POST /api/admin/login
```

**Request Body**:
```json
{
    "username": "admin",
    "password": "admin123"
}
```

**Response**:
```json
{
    "user_id": 1,
    "username": "admin",
    "role": "admin",
    "token": "your_token_here",
    "expires_at": "2025-05-12 15:30:45"
}
```

#### Using Authentication in Requests

For endpoints that require admin access, include the token in the Authorization header:

```
Authorization: Bearer your_token_here
```

#### Token Expiration

Tokens automatically expire after 24 hours. You can modify the expiration time in the `auth.php` file by changing the `$tokenExpiration` property.

### Admin User Management

The initial admin user is created automatically. Currently, admin user management is handled directly in the database. In a future update, endpoints for admin user management will be added.

## Error Handling

The API uses consistent error response structures to make error handling on the client side easier.

### Error Response Format

```json
{
    "error": true,
    "message": "Error message describing the problem",
    "code": 400
}
```

### Common HTTP Status Codes

- **200 OK**: The request was successful
- **201 Created**: Resource successfully created
- **400 Bad Request**: Invalid input or missing required fields
- **401 Unauthorized**: Authentication required or authentication failed
- **403 Forbidden**: Authentication successful but insufficient permissions
- **404 Not Found**: Resource not found
- **405 Method Not Allowed**: HTTP method not supported for this endpoint
- **500 Internal Server Error**: Server-side error

### Debugging Errors

The API writes detailed logs to the `api/logs/` directory, which can be used to debug issues. The logs include information about requests, responses, and any errors that occur.

## File Uploads

The API supports file uploads for product and event images. Files are stored in the `assets/uploads/` directory and the filename is stored in the database.

### Upload Endpoints

File uploads are handled as part of creating or updating products and events. Use `multipart/form-data` as the content type when uploading files.

```
POST /api/admin/products
PUT /api/admin/products/{id}
POST /api/admin/events
PUT /api/admin/events/{id}
```

### Upload Limits

The default upload size limit is determined by your PHP configuration (`upload_max_filesize` and `post_max_size` in php.ini). The API accepts only image files with the following extensions: jpg, jpeg, png, gif.

## Frontend Integration

### JavaScript Examples

#### Fetching Products

```javascript
async function fetchProducts() {
    try {
        const response = await fetch('http://localhost:8080/api/products');
        
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        
        const products = await response.json();
        return products;
    } catch (error) {
        console.error('Error fetching products:', error);
        throw error;
    }
}
```

#### Admin Authentication

```javascript
// Login function
async function login(username, password) {
    try {
        const response = await fetch('http://localhost:8080/api/admin/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ username, password })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        
        const data = await response.json();
        
        // Store token in localStorage
        localStorage.setItem('authToken', data.token);
        localStorage.setItem('userData', JSON.stringify({
            username: data.username,
            role: data.role,
            expiresAt: data.expires_at
        }));
        
        return data;
    } catch (error) {
        console.error('Login error:', error);
        throw error;
    }
}

// Function for authenticated API calls
async function fetchWithAuth(url, options = {}) {
    const token = localStorage.getItem('authToken');
    
    if (!token) {
        throw new Error('No authentication token found');
    }
    
    const authOptions = {
        ...options,
        headers: {
            ...options.headers,
            'Authorization': `Bearer ${token}`
        }
    };
    
    return fetch(url, authOptions);
}
```

#### Creating an Order

```javascript
async function createOrder(orderData) {
    try {
        const response = await fetch('http://localhost:8080/api/orders', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(orderData)
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error('Error creating order:', error);
        throw error;
    }
}

// Example order data
const orderData = {
    customer_name: "John Doe",
    customer_email: "john@example.com",
    customer_phone: "123-456-7890",
    shipping_address: "123 Main St, Anytown, USA",
    payment_method: "credit_card",
    items: [
        {
            product_id: 1,
            quantity: 2,
            size: "L"
        },
        {
            product_id: 2,
            quantity: 1
        }
    ]
};
```

#### Uploading Files

```javascript
async function uploadProductWithImage(productData, imageFile) {
    const formData = new FormData();
    
    // Add product data
    Object.entries(productData).forEach(([key, value]) => {
        if (key === 'sizes' && Array.isArray(value)) {
            formData.append('sizes', JSON.stringify(value));
        } else {
            formData.append(key, value);
        }
    });
    
    // Add image file
    formData.append('image', imageFile);
    
    try {
        const response = await fetchWithAuth('http://localhost:8080/api/admin/products', {
            method: 'POST',
            body: formData
            // No Content-Type header - it will be set automatically with boundary
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error('Error uploading product:', error);
        throw error;
    }
}
```

### CORS Configuration

The API includes CORS headers to allow cross-origin requests. The allowed origins are configured in the `index.php` file:

```php
$allowedOrigins = [
    'http://localhost:8000',    // Your frontend dev server
    'http://localhost:3000',    // Alternative frontend port
    'http://127.0.0.1:8000',    // Alternative localhost notation
    'http://127.0.0.1:3000',
    'https://yourdomain.com'    // Add your production domain
];
```

If you need to allow requests from additional origins, add them to this array.

## Testing

### Using Postman

A Postman collection file is provided in the repository: `api/LDS_Backend_API.postman_collection.json`. This collection contains pre-configured requests for all API endpoints.

#### Importing the Collection

1. Open Postman
2. Click on "Import" in the top left
3. Choose "File" and select the `LDS_Backend_API.postman_collection.json` file
4. Click "Import"

#### Setting up the Environment

Create a Postman environment with the following variables:

- `baseUrl`: The base URL of your API (e.g., `http://localhost:8080/api` or `http://localhost/lds-backend-php/api`)
- `adminToken`: This will be automatically set after a successful login

#### Running Tests

1. Run the "Login" request in the Auth folder to get an authentication token
2. The token will be automatically stored in the `adminToken` environment variable
3. You can now run other requests that require authentication

### API Debugging

The API includes detailed logging to help with debugging. Logs are stored in the `api/logs/` directory.

To enable additional debug output, you can modify the `debug` property in the `auth.php` file:

```php
private $debug = true; // Set to false in production
```

You can also add your own debug statements using:

```php
error_log('Debug message');
```

## Deployment

### Production Considerations

When deploying the API to a production environment, consider the following:

1. **Security**: Set appropriate file permissions, use HTTPS, and update the default admin credentials
2. **Performance**: Consider adding caching for frequently accessed resources
3. **Logging**: Disable debug logging in production by setting `$debug = false` in `auth.php`
4. **CORS**: Update the allowed origins in `index.php` to include only your production domains

### Security Best Practices

1. **Change Default Credentials**: Immediately change the default admin username and password
2. **Use HTTPS**: Configure your web server to use SSL/TLS encryption
3. **Apply Proper Permissions**: Set restrictive file permissions to prevent unauthorized access
4. **Regular Updates**: Keep PHP and all extensions up to date
5. **Input Validation**: The API includes input validation, but additional validation on the frontend is recommended
6. **Rate Limiting**: Consider implementing rate limiting for public endpoints
7. **Secure Headers**: Configure security headers like Content-Security-Policy

### Registrations

#### Register for an Event

```
POST /api/registrations
```

**Request Body**:
```json
{
    "event_id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "123-456-7890",
    "number_of_tickets": 2,
    "payment_method": "credit_card",
    "notes": "Vegetarian meal preference"
}
```

**Response**:
```json
{
    "id": 1,
    "event_id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "123-456-7890",
    "number_of_tickets": 2,
    "payment_method": "credit_card",
    "notes": "Vegetarian meal preference",
    "created_at": "2023-06-18 10:15:30"
}
```

#### List All Registrations (Admin only)

```
GET /api/admin/registrations
```

**Headers**:
```
Authorization: Bearer your_token_here
```

**Response**:
```json
[
    {
        "id": 1,
        "event_id": 1,
        "event_name": "Annual Fundraiser",
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "123-456-7890",
        "number_of_tickets": 2,
        "payment_method": "credit_card",
        "notes": "Vegetarian meal preference",
        "created_at": "2023-06-18 10:15:30"
    },
    {
        "id": 2,
        "event_id": 2,
        "event_name": "Community Clean-up",
        "name": "Jane Smith",
        "email": "jane@example.com",
        "phone": "098-765-4321",
        "number_of_tickets": 1,
        "payment_method": "cash",
        "notes": "",
        "created_at": "2023-06-18 11:30:45"
    }
]
```

#### Get Registration by ID (Admin only)

```
GET /api/admin/registrations/{id}
```

**Headers**:
```
Authorization: Bearer your_token_here
```

**Response**:
```json
{
    "id": 1,
    "event_id": 1,
    "event_name": "Annual Fundraiser",
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "123-456-7890",
    "number_of_tickets": 2,
    "payment_method": "credit_card",
    "notes": "Vegetarian meal preference",
    "created_at": "2023-06-18 10:15:30"
}
```

### Donations

#### Make a Donation

```
POST /api/donations
```

**Request Body**:
```json
{
    "name": "Sarah Johnson",
    "email": "sarah@example.com",
    "amount": 50.00,
    "payment_method": "credit_card",
    "anonymous": false,
    "notes": "Monthly donation"
}
```

**Response**:
```json
{
    "id": 1,
    "name": "Sarah Johnson",
    "email": "sarah@example.com",
    "amount": 50.00,
    "payment_method": "credit_card",
    "anonymous": false,
    "notes": "Monthly donation",
    "created_at": "2023-06-19 09:45:15"
}
```

#### List All Donations (Admin only)

```
GET /api/admin/donations
```

**Headers**:
```
Authorization: Bearer your_token_here
```

**Response**:
```json
[
    {
        "id": 1,
        "name": "Sarah Johnson",
        "email": "sarah@example.com",
        "amount": 50.00,
        "payment_method": "credit_card",
        "anonymous": false,
        "notes": "Monthly donation",
        "created_at": "2023-06-19 09:45:15"
    },
    {
        "id": 2,
        "name": "Anonymous",
        "email": "anon@example.com",
        "amount": 100.00,
        "payment_method": "paypal",
        "anonymous": true,
        "notes": "",
        "created_at": "2023-06-19 10:30:22"
    }
]
```

### Orders

#### Create a New Order

```
POST /api/orders
```

**Request Body**:
```json
{
    "customer_name": "Alex Green",
    "customer_email": "alex@example.com",
    "customer_phone": "555-123-4567",
    "shipping_address": "123 Main St, Anytown, USA",
    "payment_method": "credit_card",
    "items": [
        {
            "product_id": 1,
            "quantity": 2,
            "size": "L"
        },
        {
            "product_id": 2,
            "quantity": 1
        }
    ]
}
```

**Response**:
```json
{
    "id": 1,
    "customer_name": "Alex Green",
    "customer_email": "alex@example.com",
    "customer_phone": "555-123-4567",
    "shipping_address": "123 Main St, Anytown, USA",
    "payment_method": "credit_card",
    "total": 64.97,
    "status": "pending",
    "items": [
        {
            "product_id": 1,
            "product_name": "T-Shirt",
            "quantity": 2,
            "size": "L",
            "price": 19.99,
            "subtotal": 39.98
        },
        {
            "product_id": 2,
            "product_name": "Water Bottle",
            "quantity": 1,
            "price": 24.99,
            "subtotal": 24.99
        }
    ],
    "created_at": "2023-06-21 11:45:30"
}
```

#### List All Orders (Admin only)

```
GET /api/admin/orders
```

**Headers**:
```
Authorization: Bearer your_token_here
```

**Response**:
```json
[
    {
        "id": 1,
        "customer_name": "Alex Green",
        "customer_email": "alex@example.com",
        "customer_phone": "555-123-4567",
        "shipping_address": "123 Main St, Anytown, USA",
        "payment_method": "credit_card",
        "total": 64.97,
        "status": "pending",
        "created_at": "2023-06-21 11:45:30"
    },
    {
        "id": 2,
        "customer_name": "Taylor Smith",
        "customer_email": "taylor@example.com",
        "customer_phone": "555-987-6543",
        "shipping_address": "456 Oak Ave, Othertown, USA",
        "payment_method": "paypal",
        "total": 39.99,
        "status": "shipped",
        "created_at": "2023-06-21 13:20:15"
    }
]
```

#### Get Order by ID (Admin only or matching customer email)

```
GET /api/orders/{id}
```

**Query Parameters** (for customer access):
```
email=customer@example.com
```

**Headers** (for admin access):
```
Authorization: Bearer your_token_here
```

**Response**:
```json
{
    "id": 1,
    "customer_name": "Alex Green",
    "customer_email": "alex@example.com",
    "customer_phone": "555-123-4567",
    "shipping_address": "123 Main St, Anytown, USA",
    "payment_method": "credit_card",
    "total": 64.97,
    "status": "pending",
    "items": [
        {
            "product_id": 1,
            "product_name": "T-Shirt",
            "quantity": 2,
            "size": "L",
            "price": 19.99,
            "subtotal": 39.98
        },
        {
            "product_id": 2,
            "product_name": "Water Bottle",
            "quantity": 1,
            "price": 24.99,
            "subtotal": 24.99
        }
    ],
    "created_at": "2023-06-21 11:45:30"
}
```

#### Update Order Status (Admin only)

```
PUT /api/admin/orders/{id}/status
```

**Headers**:
```
Authorization: Bearer your_token_here
Content-Type: application/json
```

**Request Body**:
```json
{
    "status": "shipped"
}
```

**Response**:
```json
{
    "id": 1,
    "status": "shipped",
    "updated_at": "2023-06-21 14:30:45"
}
```

## Error Handling

The API returns appropriate HTTP status codes and error messages in a consistent format:

```json
{
    "error": true,
    "message": "Error message describing the problem",
    "code": 400
}
```

Common error codes:
- 400: Bad Request - Invalid input
- 401: Unauthorized - Authentication required
- 403: Forbidden - Insufficient permissions
- 404: Not Found - Resource not found
- 500: Internal Server Error - Server-side error

## File Uploads

Some endpoints support file uploads for images:

### Upload Product Image (Admin only)

```
POST /api/admin/products/upload-image
```

**Headers**:
```
Authorization: Bearer your_token_here
Content-Type: multipart/form-data
```

**Form Data**:
```
image: [file]
```

**Response**:
```json
{
    "filename": "product_1687352410.jpg",
    "path": "/assets/uploads/product_1687352410.jpg"
}
```

### Upload Event Image (Admin only)

```
POST /api/admin/events/upload-image
```

**Headers**:
```
Authorization: Bearer your_token_here
Content-Type: multipart/form-data
```

**Form Data**:
```
image: [file]
```

**Response**:
```json
{
    "filename": "event_1687352615.jpg",
    "path": "/assets/uploads/event_1687352615.jpg"
}
```

## Frontend Integration Examples

### Vanilla JavaScript Example: Admin Login

```javascript
// Function to handle admin login
async function adminLogin(username, password) {
    try {
        const response = await fetch('http://your-domain.com/api/admin/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ username, password })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        
        const data = await response.json();
        
        // Store the token in localStorage for later use
        localStorage.setItem('adminToken', data.token);
        
        return data;
    } catch (error) {
        console.error('Login error:', error);
        throw error;
    }
}

// Function to make authenticated API calls
async function fetchWithAuth(url, options = {}) {
    const token = localStorage.getItem('adminToken');
    
    if (!token) {
        throw new Error('No authentication token found');
    }
    
    const authOptions = {
        ...options,
        headers: {
            ...options.headers,
            'Authorization': `Bearer ${token}`
        }
    };
    
    return fetch(url, authOptions);
}

// Example: Get all admin messages
async function getAdminMessages() {
    try {
        const response = await fetchWithAuth('http://your-domain.com/api/admin/messages');
        
        if (!response.ok) {
            if (response.status === 401) {
                // Handle unauthorized access (e.g., redirect to login)
                window.location.href = '/admin-login.html';
                return;
            }
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        
        const messages = await response.json();
        displayMessages(messages);
    } catch (error) {
        console.error('Error fetching messages:', error);
        displayError('Failed to load messages. Please try again later.');
    }
}

// Login form event handler
document.getElementById('login-form').addEventListener('submit', async function(event) {
    event.preventDefault();
    
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    
    try {
        const loginResult = await adminLogin(username, password);
        // Redirect to admin dashboard on successful login
        window.location.href = '/admin-dashboard.html';
    } catch (error) {
        document.getElementById('login-error').textContent = 'Invalid username or password';
    }
});
```

### Vanilla JavaScript Example: Creating an Order

```javascript
// Function to create a new order
async function createOrder(orderData) {
    try {
        const response = await fetch('http://your-domain.com/api/orders', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(orderData)
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        
        const order = await response.json();
        return order;
    } catch (error) {
        console.error('Error creating order:', error);
        throw error;
    }
}

// Function to handle checkout form submission
document.getElementById('checkout-form').addEventListener('submit', async function(event) {
    event.preventDefault();
    
    // Get cart items from localStorage
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    
    if (cart.length === 0) {
        alert('Your cart is empty');
        return;
    }
    
    const orderData = {
        customer_name: document.getElementById('name').value,
        customer_email: document.getElementById('email').value,
        customer_phone: document.getElementById('phone').value,
        shipping_address: document.getElementById('address').value,
        payment_method: document.querySelector('input[name="payment"]:checked').value,
        items: cart.map(item => ({
            product_id: item.id,
            quantity: item.quantity,
            size: item.size
        }))
    };
    
    try {
        const order = await createOrder(orderData);
        
        // Clear cart after successful order
        localStorage.removeItem('cart');
        
        // Show order confirmation
        window.location.href = `/order-confirmation.html?orderId=${order.id}`;
    } catch (error) {
        document.getElementById('checkout-error').textContent = 
            'Failed to process your order. Please check your information and try again.';
    }
});
```

## Testing with Postman

A Postman collection file is provided in the repository: `LDS_Backend_API.postman_collection.json`.

### Importing the Collection

1. Open Postman
2. Click on the "Import" button
3. Drag and drop the `LDS_Backend_API.postman_collection.json` file or browse to locate it
4. Click "Import"

### Collection Structure

The collection is organized into folders:

- **Auth**: Login, logout, and check-auth endpoints
- **Products**: Product-related endpoints
- **Events**: Event-related endpoints
- **Registrations**: Registration-related endpoints
- **Donations**: Donation-related endpoints
- **Messages**: Message-related endpoints
- **Orders**: Order-related endpoints
- **File Uploads**: Image upload endpoints

### Environment Variables

Create a Postman environment with these variables:

- `baseUrl`: The base URL of your API (e.g., `http://localhost/lds-backend-php/api`)
- `adminToken`: Will be automatically set after successful login

### Running the Tests

1. First, run the "Login" request in the Auth folder to authenticate
2. The login response will automatically set the `adminToken` variable
3. Run other requests as needed

## Security Considerations

1. **Change Default Admin Credentials**: After installation, change the default admin password.

2. **HTTPS**: Configure your web server to use HTTPS to encrypt data in transit.

3. **CORS**: The API has CORS headers configured, but you may need to adjust them based on your frontend domain.

4. **Token Expiration**: Admin tokens will expire after 24 hours of inactivity.

5. **Input Validation**: All input data is validated, but be sure to validate on the frontend as well.

## Troubleshooting

### Common Issues

#### API Returns 500 Error

- Check PHP error logs (usually in Apache's `error.log`)
- Ensure the database directory is writable
- Verify that the SQLite PDO extension is enabled in PHP

#### File Upload Issues

- Check that `assets/uploads` directory exists and is writable
- Ensure your PHP configuration allows file uploads (`file_uploads = On` in php.ini)
- Check the `upload_max_filesize` and `post_max_size` values in php.ini

#### Authentication Problems

- Clear browser cookies and localStorage
- Make sure you're including the token correctly in the Authorization header
- Check that your session is not expired

### Getting Help

If you encounter issues not covered here, please:

1. Check the full error message in the API response
2. Look at the server error logs
3. Create an issue in the project repository with detailed information about the problem and steps to reproduce it

---

This documentation is maintained as part of the Site Association PHP backend project. For updates, check the project repository.
```