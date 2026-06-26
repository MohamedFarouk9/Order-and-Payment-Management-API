# Order and Payment Management API

A Laravel-based API for managing orders, confirming orders, and processing payments with production-ready JWT authentication and extensible payment gateway support.

## Overview

This API includes:

- User registration and login using JWT authentication.
- Order creation, update, confirmation, retrieval, and deletion.
- Payment processing for confirmed orders only.
- A customizable payment gateway manager with support for multiple payment methods.
- Seeders for users, orders, and payments.
- A Postman collection for API testing and documentation.

## Setup Instructions

1. Clone the repository:

```bash
git clone <repo-url>
cd Order-and-Payment-Management-API/Order-and-Payment-Management-API
```

2. Install PHP dependencies:

```bash
composer install
```

3. Copy the environment file and update settings:

```bash
cp .env.example .env
```

4. Generate the application key and JWT secret:

```bash
php artisan key:generate
php artisan jwt:secret --force
```

5. Configure database settings in `.env`.

6. Run migrations and seed sample data:

```bash
php artisan migrate:fresh --seed
```

7. Start the application:

```bash
php artisan serve
```

8. Import the Postman collection:

- `Order-and-Payment-Management-API.postman_collection.json`
- Set environment variables:
  - `baseUrl` → `http://localhost:8000`
  - `token` → leave empty initially
- Execute `Register` or `Login`, then use the saved `token` for protected endpoints.

## Authentication

Authentication uses `tymon/jwt-auth` with the `api` guard configured for JWT. Requests to protected routes must include:

```http
Authorization: Bearer <token>
```

### Example seeded accounts

- `test@example.com` / `password`
- `alice@example.com` / `password`
- `bob@example.com` / `password`

## Payment Gateway Extensibility

The payment gateway layer is built using a strategy pattern via `App\Services\PaymentGateway\PaymentGatewayManager`.

### How it works

- `PaymentGatewayManager` registers available gateways.
- `PaymentController` delegates payment processing to the manager.
- Each gateway implements `PaymentGatewayInterface`.

### Adding a new gateway

1. Create a new gateway class implementing `App\Services\PaymentGateway\PaymentGatewayInterface`.
2. Implement methods for `process()`, `refund()`, `isAvailable()`, and `getName()`.
3. Register the gateway in `PaymentGatewayManager::registerGateways()`:

```php
$this->register('stripe', new StripeGateway());
```

4. Call the new payment method from the API using `payment_method: stripe`.

### Why this is extensible

- New payment methods do not require changes to the controller or business logic.
- Gateway selection happens at runtime by name.
- You can enable or disable gateways independently.

## API Documentation

A Postman collection is included: `Order-and-Payment-Management-API.postman_collection.json`.

It contains requests for:

- Authentication: register, login, me, refresh, logout
- Orders: list, create, get, update, confirm, delete
- Payments: process payments, list order payments, list payments, get payment by id, get available methods

The collection is configured to store the JWT token automatically after successful authentication.

## Additional Notes and Assumptions

- Orders can only be paid after they are confirmed.
- Payment processing enforces idempotency through `idempotency_key`.
- The seeders create realistic sample data and stable test users.
- The API expects JSON requests and returns JSON responses.
- For production, use HTTPS and keep `JWT_SECRET` secure.
- The current gateway implementations are stubbed for demo purposes and should be replaced with real payment provider logic in production.

## Directory Structure

Key files:

- `app/Http/Controllers/AuthController.php`
- `app/Http/Controllers/OrderController.php`
- `app/Http/Controllers/PaymentController.php`
- `app/Services/AuthService.php`
- `app/Services/PaymentGateway/PaymentGatewayManager.php`
- `database/seeders/DatabaseSeeder.php`
- `Order-and-Payment-Management-API.postman_collection.json`

## License

This project is provided under the MIT license.
