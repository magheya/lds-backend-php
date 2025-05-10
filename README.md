# LDS Backend PHP API Documentation

This documentation provides comprehensive information about the Site Association PHP backend API, including setup instructions, authentication details, and endpoint documentation for integrating with JavaScript clients.

## Table of Contents
- [Overview](#overview)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Database Schema](#database-schema)
- [Authentication](#authentication)
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
- [Frontend Integration Examples](#frontend-integration-examples)
- [Testing with Postman](#testing-with-postman)
- [Security Considerations](#security-considerations)
- [Troubleshooting](#troubleshooting)

## Overview

This API serves as the backend for the Site Association project, providing endpoints for managing products, events, registrations, donations, messages, and orders. It includes authentication for admin-only endpoints and handles file uploads for images.

## Prerequisites

- PHP 7.4+
- SQLite with PHP PDO extension
- Apache or Nginx web server
- Write permissions for the web server on `db` and `assets/uploads` directories

## Installation

1. Clone or download this repository to your web server directory.
2. Configure your web server to point to this directory.
3. Make sure the web server user has write permissions for `db` and `assets/uploads` directories.
   
   ```bash
   chmod -R 755 .
   chmod -R 777 db assets/uploads
   ```

4. The database will be automatically initialized on the first request to the API.
5. A default admin user will be created with:
   - Username: `admin`
   - Password: `admin123`

## Database Schema

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

## Authentication

The API uses session-based token authentication for admin-only endpoints.

### Login

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
    "username": "admin",
    "role": "admin",
    "token": "your_token_here"
}
```

### Using Authentication in Requests

For endpoints that require admin access, include the token in the Authorization header:

```
Authorization: Bearer your_token_here
```

## API Endpoints

### Products

#### List All Products

```
GET /api/products
```

**Response**:
```json
[
    {
        "id": 1,
        "name": "T-Shirt",
        "description": "Cotton t-shirt with logo",
        "price": 19.99,
        "image": "t-shirt.jpg",
        "sizes": ["S", "M", "L", "XL"],
        "created_at": "2023-06-15 10:30:45"
    },
    {
        "id": 2,
        "name": "Water Bottle",
        "description": "Stainless steel water bottle",
        "price": 24.99,
        "image": "bottle.jpg",
        "sizes": [],
        "created_at": "2023-06-15 11:45:22"
    }
]
```

#### Get Product by ID

```
GET /api/products/{id}
```

**Response**:
```json
{
    "id": 1,
    "name": "T-Shirt",
    "description": "Cotton t-shirt with logo",
    "price": 19.99,
    "image": "t-shirt.jpg",
    "sizes": ["S", "M", "L", "XL"],
    "created_at": "2023-06-15 10:30:45"
}
```

#### Create Product (Admin only)

```
POST /api/admin/products
```

**Headers**:
```
Authorization: Bearer your_token_here
Content-Type: application/json
```

**Request Body**:
```json
{
    "name": "Hoodie",
    "description": "Warm hoodie with logo",
    "price": 39.99,
    "image": "hoodie.jpg",
    "sizes": ["S", "M", "L", "XL", "XXL"]
}
```

**Response**:
```json
{
    "id": 3,
    "name": "Hoodie",
    "description": "Warm hoodie with logo",
    "price": 39.99,
    "image": "hoodie.jpg",
    "sizes": ["S", "M", "L", "XL", "XXL"],
    "created_at": "2023-06-15 14:22:10"
}
```

#### Update Product (Admin only)

```
PUT /api/admin/products/{id}
```

**Headers**:
```
Authorization: Bearer your_token_here
Content-Type: application/json
```

**Request Body**:
```json
{
    "name": "Premium Hoodie",
    "description": "Warm hoodie with embroidered logo",
    "price": 44.99,
    "image": "hoodie.jpg",
    "sizes": ["S", "M", "L", "XL", "XXL"]
}
```

**Response**:
```json
{
    "id": 3,
    "name": "Premium Hoodie",
    "description": "Warm hoodie with embroidered logo",
    "price": 44.99,
    "image": "hoodie.jpg",
    "sizes": ["S", "M", "L", "XL", "XXL"],
    "updated_at": "2023-06-15 15:10:45"
}
```

#### Delete Product (Admin only)

```
DELETE /api/admin/products/{id}
```

**Headers**:
```
Authorization: Bearer your_token_here
```

**Response**:
```json
{
    "success": true,
    "message": "Product deleted successfully"
}
```

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