# Kipay API Reference

This document provides comprehensive information about the Kipay Payment Gateway API. Use this reference to integrate Kipay with your applications.

## API Base URL

- **Test Environment**: `https://api.test.kipay.com`
- **Production Environment**: `https://api.kipay.com`

## Authentication

All API requests require authentication using an API key. You can obtain your API key from the Kipay dashboard under Settings > API Keys.

Include your API key in the request header:

```
X-API-Key: your_api_key_here
```

## Error Handling

The API uses conventional HTTP response codes to indicate the success or failure of requests:

- `200 OK` - Request succeeded
- `201 Created` - Resource created successfully
- `400 Bad Request` - Invalid request parameters
- `401 Unauthorized` - Authentication failed
- `403 Forbidden` - Insufficient permissions
- `404 Not Found` - Resource not found
- `422 Unprocessable Entity` - Validation error
- `500 Internal Server Error` - Server error

All error responses include a JSON body with the following structure:

```json
{
  "status": "error",
  "message": "Error description",
  "errors": {
    "field_name": ["Error message for this field"]
  }
}
```

## API Endpoints

### Transactions

#### Initialize Transaction

Initializes a new payment transaction.

- **URL**: `/api/transactions/initialize`
- **Method**: `POST`
- **Headers**:
  - `Content-Type: application/json`
  - `X-API-Key: your_api_key_here`

**Request Body**:

```json
{
  "amount": 10000,
  "email": "customer@example.com",
  "payment_channel_id": 1,
  "description": "Payment for Order #12345",
  "currency": "KSH",
  "first_name": "John",
  "last_name": "Doe",
  "phone": "+2548012345678",
  "metadata": {
    "order_id": "12345",
    "custom_field": "value"
  }
}
```

**Required Fields**:
- `amount`: Payment amount (numeric, without currency symbol)
- `email`: Customer email address
- `payment_channel_id`: ID of the payment channel to use

**Optional Fields**:
- `description`: Payment description
- `currency`: Payment currency (default: KSH)
- `first_name`: Customer first name
- `last_name`: Customer last name
- `phone`: Customer phone number
- `metadata`: Additional data for the transaction (JSON object)

**Response (Success)**:

```json
{
  "status": "success",
  "transaction": {
    "id": 123,
    "reference": "KIPAY16789012345",
    "amount": 10000,
    "currency": "KSH",
    "description": "Payment for Order #12345",
    "status": "pending",
    "created_at": "2023-01-15T14:30:00Z"
  },
  "authorization_url": "https://checkout.kipay.com/KIPAY16789012345",
  "access_code": "ACE_123456789",
  "reference": "KIPAY16789012345"
}
```

#### Verify Transaction

Verifies the status of a transaction.

- **URL**: `/api/transactions/verify/{reference}`
- **Method**: `GET`
- **Headers**:
  - `X-API-Key: your_api_key_here`

**Parameters**:
- `reference`: Transaction reference code

**Response (Success)**:

```json
{
  "status": "success",
  "transaction": {
    "id": 123,
    "reference": "KIPAY16789012345",
    "amount": 10000,
    "currency": "KSH",
    "description": "Payment for Order #12345",
    "status": "completed",
    "payment_method": "card",
    "created_at": "2023-01-15T14:30:00Z"
  }
}
```

#### Get Transaction Details

Retrieves detailed information about a transaction.

- **URL**: `/api/transactions/get/{reference}`
- **Method**: `GET`
- **Headers**:
  - `X-API-Key: your_api_key_here`

**Parameters**:
- `reference`: Transaction reference code

**Response (Success)**:

```json
{
  "status": "success",
  "transaction": {
    "id": 123,
    "reference": "KIPAY16789012345",
    "amount": 10000,
    "currency": "KSH",
    "description": "Payment for Order #12345",
    "status": "completed",
    "payment_method": "card",
    "customer_id": 456,
    "payment_channel_id": 1,
    "metadata": {
      "order_id": "12345"
    },
    "logs": [
      {
        "id": 1,
        "transaction_id": 123,
        "status": "pending",
        "message": "Transaction initiated",
        "created_at": "2023-01-15T14:30:00Z"
      },
      {
        "id": 2,
        "transaction_id": 123,
        "status": "completed",
        "message": "Payment successful",
        "created_at": "2023-01-15T14:35:00Z"
      }
    ],
    "created_at": "2023-01-15T14:30:00Z",
    "updated_at": "2023-01-15T14:35:00Z"
  }
}
```

#### List Transactions

Retrieves a paginated list of transactions.

- **URL**: `/api/transactions/list`
- **Method**: `GET`
- **Headers**:
  - `X-API-Key: your_api_key_here`

**Query Parameters**:
- `page`: Page number (default: 1)
- `limit`: Number of items per page (default: 20, max: 100)
- `status`: Filter by status (`pending`, `processing`, `completed`, `failed`, `refunded`, `cancelled`)
- `payment_method`: Filter by payment method
- `currency`: Filter by currency
- `date_from`: Filter by start date (format: YYYY-MM-DD)
- `date_to`: Filter by end date (format: YYYY-MM-DD)
- `amount_min`: Filter by minimum amount
- `amount_max`: Filter by maximum amount
- `search`: Search term for reference, description, or customer email

**Response (Success)**:

```json
{
  "status": "success",
  "data": [
    {
      "id": 123,
      "reference": "KIPAY16789012345",
      "amount": 10000,
      "currency": "KSH",
      "status": "completed",
      "payment_method": "card",
      "description": "Payment for Order #12345",
      "customer_email": "customer@example.com",
      "created_at": "2023-01-15T14:30:00Z"
    },
    {
      "id": 124,
      "reference": "KIPAY16789012346",
      "amount": 15000,
      "currency": "KSH",
      "status": "pending",
      "payment_method": null,
      "description": "Payment for Order #12346",
      "customer_email": "customer2@example.com",
      "created_at": "2023-01-15T15:30:00Z"
    }
  ],
  "total": 250,
  "page": 1,
  "limit": 20,
  "pages": 13
}
```

#### Get Transaction Summary

Retrieves summary statistics for transactions.

- **URL**: `/api/transactions/summary`
- **Method**: `GET`
- **Headers**:
  - `X-API-Key: your_api_key_here`

**Query Parameters**:
- `period`: Time period (`today`, `week`, `month`, `year`, `all`, default: `all`)

**Response (Success)**:

```json
{
  "status": "success",
  "total_transactions": 250,
  "successful_transactions": 200,
  "failed_transactions": 30,
  "pending_transactions": 20,
  "total_amount": 2500000,
  "successful_amount": 2000000
}
```

#### Export Transactions

Exports transactions to CSV format.

- **URL**: `/api/transactions/export`
- **Method**: `GET`
- **Headers**:
  - `X-API-Key: your_api_key_here`

**Query Parameters**:
Same as "List Transactions" endpoint.

**Response**:
Returns a CSV file download.

#### Get Transaction Chart Data

Retrieves data for transaction charts.

- **URL**: `/api/transactions/chart`
- **Method**: `GET`
- **Headers**:
  - `X-API-Key: your_api_key_here`

**Query Parameters**:
- `period`: Time period (`week`, `month`, `year`, default: `week`)

**Response (Success)**:

```json
{
  "status": "success",
  "data": [
    {
      "label": "2023-01-10",
      "completed_amount": 150000,
      "failed_amount": 20000,
      "pending_amount": 5000,
      "total_transactions": 15
    },
    {
      "label": "2023-01-11",
      "completed_amount": 180000,
      "failed_amount": 10000,
      "pending_amount": 0,
      "total_transactions": 12
    }
  ]
}
```

### Payment Channels

#### Create Payment Channel

Creates a new payment channel.

- **URL**: `/api/payment-channels/create`
- **Method**: `POST`
- **Headers**:
  - `Content-Type: application/json`
  - `X-API-Key: your_api_key_here`

**Request Body**:

```json
{
  "name": "My Paystack Channel",
  "provider": "paystack",
  "config": {
    "public_key": "pk_test_your_paystack_public_key",
    "secret_key": "sk_test_your_paystack_secret_key",
    "test_mode": true
  },
  "fees_config": {
    "fixed_fee": 100,
    "percentage_fee": 1.5,
    "cap": 2000
  },
  "is_active": true
}
```

**Required Fields**:
- `name`: Channel name
- `provider`: Payment provider (`paystack`, `flutterwave`, `stripe`, or `manual`)
- `config`: Provider-specific configuration (varies by provider)

**Optional Fields**:
- `fees_config`: Fee configuration
- `is_active`: Whether the channel is active (default: true)
- `is_default`: Whether this is the default channel (default: false)

**Response (Success)**:

```json
{
  "status": "success",
  "message": "Payment channel created successfully",
  "channel": {
    "id": 1,
    "name": "My Paystack Channel",
    "provider": "paystack",
    "is_active": true,
    "is_default": false,
    "created_at": "2023-01-15T14:30:00Z"
  }
}
```

#### Update Payment Channel

Updates an existing payment channel.

- **URL**: `/api/payment-channels/update/{id}`
- **Method**: `PUT` or `PATCH`
- **Headers**:
  - `Content-Type: application/json`
  - `X-API-Key: your_api_key_here`

**Parameters**:
- `id`: Payment channel ID

**Request Body**:
Same as "Create Payment Channel" with fields to update.

**Response (Success)**:

```json
{
  "status": "success",
  "message": "Payment channel updated successfully",
  "channel": {
    "id": 1,
    "name": "Updated Paystack Channel",
    "provider": "paystack",
    "is_active": true,
    "is_default": false,
    "created_at": "2023-01-15T14:30:00Z",
    "updated_at": "2023-01-16T10:15:00Z"
  }
}
```

#### Delete Payment Channel

Deletes a payment channel.

- **URL**: `/api/payment-channels/delete/{id}`
- **Method**: `DELETE`
- **Headers**:
  - `X-API-Key: your_api_key_here`

**Parameters**:
- `id`: Payment channel ID

**Response (Success)**:

```json
{
  "status": "success",
  "message": "Payment channel deleted successfully"
}
```

#### Get Payment Channel

Retrieves details of a payment channel.

- **URL**: `/api/payment-channels/get/{id}`
- **Method**: `GET`
- **Headers**:
  - `X-API-Key: your_api_key_here`

**Parameters**:
- `id`: Payment channel ID

**Response (Success)**:

```json
{
  "status": "success",
  "channel": {
    "id": 1,
    "name": "My Paystack Channel",
    "provider": "paystack",
    "is_active": true,
    "is_default": false,
    "created_at": "2023-01-15T14:30:00Z",
    "updated_at": "2023-01-15T14:30:00Z"
  }
}
```

#### List Payment Channels

Retrieves a list of payment channels.

- **URL**: `/api/payment-channels/list`
- **Method**: `GET`
- **Headers**:
  - `X-API-Key: your_api_key_here`

**Query Parameters**:
- `active_only`: Show only active channels (`true` or `false`, default: `false`)

**Response (Success)**:

```json
{
  "status": "success",
  "channels": [
    {
      "id": 1,
      "name": "My Paystack Channel",
      "provider": "paystack",
      "is_active": true,
      "is_default": true,
      "created_at": "2023-01-15T14:30:00Z"
    },
    {
      "id": 2,
      "name": "Manual Payment",
      "provider": "manual",
      "is_active": true,
      "is_default": false,
      "created_at": "2023-01-16T10:15:00Z"
    }
  ]
}
```

#### Get Public Configuration

Retrieves public configuration for a payment channel.

- **URL**: `/api/payment-channels/config/{id}`
- **Method**: `GET`

**Parameters**:
- `id`: Payment channel ID

**Response (Success)**:

```json
{
  "status": "success",
  "config": {
    "id": 1,
    "name": "My Paystack Channel",
    "provider": "paystack",
    "public_key": "pk_test_your_paystack_public_key"
  }
}
```

#### Set Default Payment Channel

Sets a payment channel as the default.

- **URL**: `/api/payment-channels/set-default/{id}`
- **Method**: `PUT` or `PATCH`
- **Headers**:
  - `X-API-Key: your_api_key_here`

**Parameters**:
- `id`: Payment channel ID

**Response (Success)**:

```json
{
  "status": "success",
  "message": "Payment channel set as default successfully"
}
```

#### Get Supported Providers

Retrieves a list of supported payment providers.

- **URL**: `/api/payment-channels/providers`
- **Method**: `GET`
- **Headers**:
  - `X-API-Key: your_api_key_here`

**Response (Success)**:

```json
{
  "status": "success",
  "providers": [
    {
      "id": "paystack",
      "name": "Paystack"
    },
    {
      "id": "flutterwave",
      "name": "Flutterwave"
    },
    {
      "id": "stripe",
      "name": "Stripe"
    },
    {
      "id": "manual",
      "name": "Manual Payment"
    }
  ]
}
```

#### Get Provider Requirements

Retrieves configuration requirements for a payment provider.

- **URL**: `/api/payment-channels/provider-requirements/{provider}`
- **Method**: `GET`
- **Headers**:
  - `X-API-Key: your_api_key_here`

**Parameters**:
- `provider`: Provider ID (e.g., `paystack`)

**Response (Success)**:

```json
{
  "status": "success",
  "requirements": {
    "provider": "paystack",
    "name": "Paystack",
    "required_fields": [
      "public_key",
      "secret_key"
    ],
    "optional_fields": [
      "test_mode",
      "webhook_url"
    ]
  }
}
```

### Customers

#### Create Customer

Creates a new customer.

- **URL**: `/api/customers/create`
- **Method**: `POST`
- **Headers**:
  - `Content-Type: application/json`
  - `X-API-Key: your_api_key_here`

**Request Body**:

```json
{
  "email": "customer@example.com",
  "first_name": "John",
  "last_name": "Doe",
  "phone": "+2548012345678",
  "address": "123 Main St",
  "city": "Lagos",
  "state": "Lagos",
  "country": "Nigeria",
  "postal_code": "100001",
  "metadata": {
    "custom_field": "value"
  }
}
```

**Required Fields**:
- `email`: Customer email address

**Optional Fields**:
- `first_name`: First name
- `last_name`: Last name
- `phone`: Phone number
- `address`: Street address
- `city`: City
- `state`: State/province
- `country`: Country
- `postal_code`: Postal/ZIP code
- `metadata`: Additional customer data (JSON object)

**Response (Success)**:

```json
{
  "status": "success",
  "message": "Customer created successfully",
  "customer": {
    "id": 1,
    "email": "customer@example.com",
    "first_name": "John",
    "last_name": "Doe",
    "phone": "+2548012345678",
    "created_at": "2023-01-15T14:30:00Z"
  }
}
```

#### Update Customer

Updates an existing customer.

- **URL**: `/api/customers/update/{id}`
- **Method**: `PUT` or `PATCH`
- **Headers**:
  - `Content-Type: application/json`
  - `X-API-Key: your_api_key_here`

**Parameters**:
- `id`: Customer ID

**Request Body**:
Same as "Create Customer" with fields to update.

**Response (Success)**:

```json
{
  "status": "success",
  "message": "Customer updated successfully",
  "customer": {
    "id": 1,
    "email": "customer@example.com",
    "first_name": "John",
    "last_name": "Doe",
    "phone": "+2548012345678",
    "updated_at": "2023-01-16T10:15:00Z"
  }
}
```

#### Delete Customer

Deletes a customer.

- **URL**: `/api/customers/delete/{id}`
- **Method**: `DELETE`
- **Headers**:
  - `X-API-Key: your_api_key_here`

**Parameters**:
- `id`: Customer ID

**Response (Success)**:

```json
{
  "status": "success",
  "message": "Customer deleted successfully"
}
```

#### Get Customer

Retrieves customer details.

- **URL**: `/api/customers/get/{id}`
- **Method**: `GET`
- **Headers**:
  - `X-API-Key: your_api_key_here`

**Parameters**:
- `id`: Customer ID

**Response (Success)**:

```json
{
  "status": "success",
  "customer": {
    "id": 1,
    "email": "customer@example.com",
    "first_name": "John",
    "last_name": "Doe",
    "phone": "+2548012345678",
    "address": "123 Main St",
    "city": "Lagos",
    "state": "Lagos",
    "country": "Nigeria",
    "postal_code": "100001",
    "metadata": {
      "custom_field": "value"
    },
    "created_at": "2023-01-15T14:30:00Z",
    "updated_at": "2023-01-15T14:30:00Z"
  }
}
```

#### List Customers

Retrieves a paginated list of customers.

- **URL**: `/api/customers/list`
- **Method**: `GET`
- **Headers**:
  - `X-API-Key: your_api_key_here`

**Query Parameters**:
- `page`: Page number (default: 1)
- `limit`: Number of items per page (default: 20, max: 100)
- `search`: Search term for email, name, or phone
- `country`: Filter by country
- `date_from`: Filter by start date (format: YYYY-MM-DD)
- `date_to`: Filter by end date (format: YYYY-MM-DD)

**Response (Success)**:

```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "email": "customer@example.com",
      "first_name": "John",
      "last_name": "Doe",
      "phone": "+2548012345678",
      "country": "Nigeria",
      "transaction_count": 5,
      "total_spent": 50000,
      "created_at": "2023-01-15T14:30:00Z"
    },
    {
      "id": 2,
      "email": "customer2@example.com",
      "first_name": "Jane",
      "last_name": "Smith",
      "phone": "+2548023456789",
      "country": "Ghana",
      "transaction_count": 2,
      "total_spent": 25000,
      "created_at": "2023-01-16T10:15:00Z"
    }
  ],
  "total": 50,
  "page": 1,
  "limit": 20,
  "pages": 3
}
```

#### Search Customers

Searches for customers by email, name, or phone.

- **URL**: `/api/customers/search`
- **Method**: `GET`
- **Headers**:
  - `X-API-Key: your_api_key_here`

**Query Parameters**:
- `q`: Search term
- `limit`: Maximum number of results (default: 10, max: 50)

**Response (Success)**:

```json
{
  "status": "success",
  "customers": [
    {
      "id": 1,
      "email": "customer@example.com",
      "first_name": "John",
      "last_name": "Doe",
      "phone": "+2548012345678"
    },
    {
      "id": 5,
      "email": "johndoe@example.com",
      "first_name": "John",
      "last_name": "Doe Jr",
      "phone": "+2548087654321"
    }
  ]
}
```

#### Get Customer Transactions

Retrieves a customer's transactions.

- **URL**: `/api/customers/transactions/{id}`
- **Method**: `GET`
- **Headers**:
  - `X-API-Key: your_api_key_here`

**Parameters**:
- `id`: Customer ID

**Query Parameters**:
- `page`: Page number (default: 1)
- `limit`: Number of items per page (default: 20, max: 100)
- `status`: Filter by status
- `payment_method`: Filter by payment method
- `currency`: Filter by currency
- `date_from`: Filter by start date (format: YYYY-MM-DD)
- `date_to`: Filter by end date (format: YYYY-MM-DD)

**Response (Success)**:

```json
{
  "status": "success",
  "data": [
    {
      "id": 123,
      "reference": "KIPAY16789012345",
      "amount": 10000,
      "currency": "KSH",
      "status": "completed",
      "payment_method": "card",
      "description": "Payment for Order #12345",
      "created_at": "2023-01-15T14:30:00Z"
    },
    {
      "id": 125,
      "reference": "KIPAY16789012347",
      "amount": 5000,
      "currency": "KSH",
      "status": "completed",
      "payment_method": "bank_transfer",
      "description": "Payment for Order #12347",
      "created_at": "2023-01-17T11:45:00Z"
    }
  ],
  "total": 5,
  "page": 1,
  "limit": 20,
  "pages": 1
}
```

## Webhooks

Kipay uses webhooks to notify your application about events that happen on your account, such as successful payments or failed transactions.

### Webhook Endpoints

- **Paystack**: `/webhook/paystack`
- **Flutterwave**: `/webhook/flutterwave`
- **Stripe**: `/webhook/stripe`

### Webhook Events

Kipay forwards the following events from payment providers:

#### Paystack Events
- `charge.success`: Payment was successful
- `charge.failed`: Payment failed
- `transfer.success`: Transfer was successful
- `transfer.failed`: Transfer failed
- `subscription.create`: New subscription created
- `subscription.disable`: Subscription disabled
- `invoice.create`: New invoice created
- `invoice.payment_failed`: Invoice payment failed

### Webhook Security

Webhooks include a signature header for verification:

- **Paystack**: `X-Paystack-Signature`
- **Flutterwave**: `verif-hash`
- **Stripe**: `Stripe-Signature`

Always verify webhook signatures to ensure the request is legitimate.

## Testing

### Test Cards

You can use the following test cards for testing payments:

#### Paystack Test Cards
- **Successful Payment**: 4084 0840 8408 4081, CVV: 408, Expiry: any future date
- **Failed Payment**: 4084 0840 8408 4082, CVV: 408, Expiry: any future date
- **Requires Authentication**: 4084 0840 8408 4083, CVV: 408, Expiry: any future date

#### Test OTP
- Use `123456` for any OTP prompt during testing

### Test Bank Accounts

#### Paystack Test Bank Accounts
- **Bank**: Zenith Bank
- **Account Number**: 0000000000
- **OTP**: 123456