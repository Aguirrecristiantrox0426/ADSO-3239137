
# RENT-TOOLS API Contract

**Project:** RENT-TOOLS  
**Document:** API Contract  
**Version:** 1.1.1  
**Status:** Draft  
**Architecture:** REST API  
**Backend:** Laravel  
**Frontend:** React  
**Authentication:** Laravel Sanctum

---

## 1. Introduction

This document defines the API contract for RENT-TOOLS.

It describes the endpoints, HTTP methods, request parameters, response structures, status codes, authentication requirements, authorization rules, and validation rules required for communication between the React frontend and the Laravel backend.

The API supports:

- Authentication and user account management.
- Role-based access control.
- Tool management.
- Customer management.
- Advisor management.
- Rental management.
- Return management.
- Inventory availability.

The system does not process electronic payments.

## 2. API General Standards

### 2.1. Base URL

The API must use the following versioned base path:

`/api/v1`

Example:

`http://localhost:8000/api/v1/tools`

The production base URL will be configured according to the deployment environment.

### 2.2. Communication Format

The API must use:

- HTTP or HTTPS for communication.
- JSON for request and response bodies.
- UTF-8 character encoding.
- REST conventions for resource management.
- ISO 8601 date and timestamp formats.
- UTC for date and time storage and processing.

### 2.3. Request Headers

JSON requests must include:

`Content-Type: application/json`

Clients requesting JSON responses must include:

`Accept: application/json`

Protected endpoints must require a valid Laravel Sanctum authentication token.

Authenticated requests must include:

`Authorization: Bearer <token>`

### 2.4. Resource Identification

Each resource must be identified by a unique numeric ID.

The API must validate resource identifiers before processing requests.

### 2.5. Authentication and Authorization Standards

The API uses Laravel Sanctum for token-based authentication.

The system supports two authenticated roles:

- `administrator`
- `advisor`

Customers are business records and do not have application accounts, authentication credentials, passwords, or roles.

The backend must authenticate protected requests and verify the permissions associated with the authenticated user's role.

Frontend restrictions must not replace backend authorization.

The backend is responsible for enforcing all business rules and access restrictions.

## 3. API Endpoints

The following endpoints define the API contract.

### 3.1. Authentication Management

| Method | Endpoint | Description | Authentication |
|---|---|---|---|
| POST | `/auth/login` | Authenticate a user and generate a token | Public |
| POST | `/auth/logout` | Revoke the current authentication token | Required |
| GET | `/auth/me` | Retrieve the authenticated user's information and role | Required |

#### Login Request

`POST /api/v1/auth/login`

Example:

```json
{
  "email": "admin@example.com",
  "password": "password"
}
```

Successful response:

```json
{
  "success": true,
  "message": "Authentication successful.",
  "data": {
    "user": {
      "id": 1,
      "name": "System Administrator",
      "email": "admin@example.com",
      "role": "administrator"
    },
    "token": "generated-sanctum-token"
  }
}
```

The backend must:

- Validate the email and password.
- Verify that the user account exists.
- Verify that the account is active.
- Verify the password securely.
- Generate a Laravel Sanctum token when authentication succeeds.
- Return the authenticated user's information and assigned role.
- Never return the user's password or password hash.

Invalid credentials must return HTTP `401 Unauthorized`.

Deactivated accounts must not be allowed to authenticate.

Customers must not be allowed to authenticate because they do not have application accounts.

#### Logout Request

`POST /api/v1/auth/logout`

Required header:

`Authorization: Bearer <token>`

The backend must revoke the current authenticated token.

Successful response:

```json
{
  "success": true,
  "message": "Session closed successfully.",
  "data": null
}
```

#### Authenticated User Request

`GET /api/v1/auth/me`

Required header:

`Authorization: Bearer <token>`

Successful response:

```json
{
  "success": true,
  "message": "Authenticated user retrieved successfully.",
  "data": {
    "id": 1,
    "name": "System Administrator",
    "email": "admin@example.com",
    "role": "administrator"
  }
}
```

The backend must return only information authorized for the authenticated user.

### 3.2. User Account Management

User account management is restricted to Administrators.

| Method | Endpoint | Description | Required Role |
|---|---|---|---|
| GET | `/users` | List user accounts | Administrator |
| GET | `/users/{id}` | Retrieve a user account | Administrator |
| POST | `/users` | Create a user account | Administrator |
| PUT | `/users/{id}` | Update a user account | Administrator |
| DELETE | `/users/{id}` | Logically deactivate a user account | Administrator |

#### User Registration Request

`POST /api/v1/users`

Example:

```json
{
  "name": "Maria Rodriguez",
  "email": "maria@example.com",
  "password": "secure-password",
  "role": "advisor"
}
```

The backend must:

- Verify that the authenticated user has the Administrator role.
- Validate the required account information.
- Verify that the email is unique.
- Accept only `administrator` or `advisor` as role values.
- Securely hash the password.
- Create the account with the assigned role.
- Avoid exposing password information in the response.

Only an Administrator may create user accounts and assign roles.

Advisors must not be allowed to create accounts, change roles, or deactivate user accounts.

User account deactivation must be logical. Historical rental and return records must remain preserved.

An account that has been logically deactivated must not be allowed to authenticate.

### 3.3. Tool Management

| Method | Endpoint | Description | Required Role |
|---|---|---|---|
| GET | `/tools/availability` | Retrieve available tool quantities | Administrator, Advisor |
| GET | `/tools/{id}/availability` | Retrieve availability for a specific tool | Administrator, Advisor |
| GET | `/tools` | List active tools | Administrator, Advisor |
| GET | `/tools/{id}` | Retrieve a tool by ID | Administrator, Advisor |
| POST | `/tools` | Register a new tool | Administrator, Advisor |
| PUT | `/tools/{id}` | Update a tool | Administrator, Advisor |
| DELETE | `/tools/{id}` | Logically deactivate a tool | Administrator |

Static routes such as `/tools/availability` must be registered before dynamic routes such as `/tools/{id}` to avoid route conflicts.

#### Tool Registration Request

`POST /api/v1/tools`

Example:

```json
{
  "name": "Electric Drill",
  "description": "Professional electric drill",
  "total_quantity": 10
}
```

The backend must initialize `available_quantity` according to the registered total quantity.

For a new tool, the initial available quantity must be equal to the total quantity.

Tool updates must preserve historical rental information.

Tools with rental history must not be physically deleted.

Administrators may register, update, and logically deactivate tools.

Advisors may register and update tools but cannot logically deactivate them.

If an Advisor attempts to deactivate a tool, the backend must return HTTP `403 Forbidden`.

### 3.4. Category Management

Categories organize the tool catalogue. Every category endpoint requires authentication and the `administrator` role. The specification requires these routes; they must not be reported as implemented until the API provides them.

| Method | Endpoint | Description | Required Role |
|---|---|---|---|
| GET | `/categories` | List active categories | Administrator |
| POST | `/categories` | Create a category | Administrator |
| PUT | `/categories/{id}` | Update a category | Administrator |
| DELETE | `/categories/{id}` | Logically deactivate a category | Administrator |

`POST /api/v1/categories` requires a unique `name` and accepts an optional `description`. It returns `201 Created` for a valid creation, `422 Unprocessable Entity` for invalid input, `401 Unauthorized` without a valid token, and `403 Forbidden` for an Advisor. A referenced category must be logically deactivated rather than physically deleted.

### 3.5. Customer Management

| Method | Endpoint | Description | Required Role |
|---|---|---|---|
| GET | `/customers` | List customers | Administrator, Advisor |
| GET | `/customers/{id}` | Retrieve a customer by ID | Administrator, Advisor |
| POST | `/customers` | Register a customer | Administrator, Advisor |
| PUT | `/customers/{id}` | Update a customer | Administrator, Advisor |
| DELETE | `/customers/{id}` | Logically deactivate a customer | Administrator |
| PATCH | `/customers/{id}/reactivate` | Reactivate a deactivated customer | Administrator |

#### Customer Registration Request

`POST /api/v1/customers`

Example:

```json
{
  "full_name": "Juan Perez",
  "identification_number": "123456789",
  "phone": "3001234567",
  "email": "juan@example.com"
}
```

The identification number must be unique.

Customer records do not create user accounts or authentication credentials.

Administrators and Advisors may register, consult, and update customer information.

Only Administrators may logically deactivate or reactivate customers.

Customer deactivation must use the `deleted_at` attribute. It must not physically delete the database record.

When a customer is deactivated:

- The customer record must remain stored.
- Historical rentals and returns must remain available.
- The customer must not be allowed to receive new rentals.
- Existing rental information must not be deleted or modified.
- The operation must be recorded using logical deletion.

If an Advisor attempts to deactivate or reactivate a customer, the backend must return HTTP `403 Forbidden`.

#### Customer Deactivation Request

`DELETE /api/v1/customers/{id}`

Required role: Administrator.

The backend must:

- Verify that the authenticated user has the Administrator role.
- Verify that the customer exists.
- Set the customer's `deleted_at` attribute.
- Preserve all historical rental and return records.
- Prevent physical deletion of the customer record.

Successful response:

```json
{
  "success": true,
  "message": "Customer deactivated successfully.",
  "data": null
}
```

#### Customer Reactivation Request

`PATCH /api/v1/customers/{id}/reactivate`

Required role: Administrator.

The backend must:

- Verify that the authenticated user has the Administrator role.
- Verify that the customer exists.
- Verify that the customer is currently deactivated.
- Set the customer's `deleted_at` attribute to `null`.
- Preserve historical rental and return records.

Successful response:

```json
{
  "success": true,
  "message": "Customer reactivated successfully.",
  "data": {
    "id": 1,
    "full_name": "Juan Perez",
    "identification_number": "123456789",
    "phone": "3001234567",
    "email": "juan@example.com"
  }
}
```

A deactivated customer must not be associated with a new rental until an Administrator reactivates the record.

### 3.6. Advisor Management

| Method | Endpoint | Description | Required Role |
|---|---|---|---|
| GET | `/advisors` | List advisors | Administrator, Advisor |
| GET | `/advisors/{id}` | Retrieve an advisor by ID | Administrator, Advisor |
| POST | `/advisors` | Register an advisor | Administrator |
| PUT | `/advisors/{id}` | Update an advisor | Administrator |
| DELETE | `/advisors/{id}` | Logically deactivate an advisor | Administrator |

#### Advisor Registration Request

`POST /api/v1/advisors`

Example:

```json
{
  "full_name": "Maria Rodriguez",
  "identification_number": "987654321",
  "email": "maria@example.com"
}
```

The identification number must be unique.

Advisor records associated with historical operations must be preserved.

When an advisor is authorized to access the application, the corresponding user account must have the `advisor` role.

The creation of an advisor business record and the creation of an authenticated user account are separate operations.

Only Administrators may create, update, or deactivate advisor records.

Advisors may consult advisor information required for operational activities.

When an Advisor consults the advisor list or an advisor detail, the API must return only the operational information necessary for the application.

The API must not expose passwords, authentication tokens, or unnecessary administrative information.

### 3.7. Rental Management

| Method | Endpoint | Description | Required Role |
|---|---|---|---|
| GET | `/rentals` | List rentals | Administrator, Advisor |
| GET | `/rentals/{id}` | Retrieve a rental by ID | Administrator, Advisor |
| POST | `/rentals` | Register a rental | Administrator, Advisor |
| GET | `/rentals/{id}/details` | Retrieve rental details | Administrator, Advisor |

#### Rental Registration Request

`POST /api/v1/rentals`

Example for an Advisor:

```json
{
  "customer_id": 1,
  "rental_details": [
    {
      "tool_id": 1,
      "quantity": 2
    }
  ]
}
```

Example for an Administrator:

```json
{
  "customer_id": 1,
  "advisor_id": 1,
  "rental_details": [
    {
      "tool_id": 1,
      "quantity": 2
    }
  ]
}
```

The backend must:

- Validate that the customer exists and is eligible for a new rental.
- Reject deactivated customers.
- Validate that the advisor exists and is authorized to perform the operation.
- Verify tool availability.
- Enforce the maximum of three outstanding physical tool units per customer.
- Set the rental date and due date using UTC.
- Enforce the maximum rental period of seven calendar days.
- Record the rental and its details.
- Decrease available inventory quantities.
- Execute the operation within a database transaction.

The backend must calculate the due date according to the rental date and the seven-day limit.

#### Advisor Identification

When an Advisor registers a rental, the backend must identify the authenticated Advisor through the associated user account and advisor record.

The Advisor must not be allowed to register a rental on behalf of another Advisor.

If an Advisor submits an `advisor_id` that does not correspond to their authenticated account, the backend must reject the request with HTTP `403 Forbidden`.

For an Administrator, the `advisor_id` may be provided to identify the Advisor responsible for the operation, provided that the selected Advisor is active and authorized.

The backend must not trust the `advisor_id` received from the frontend without validating it.

#### Rental Business Rules

The backend must reject a rental when:

- The customer does not exist.
- The customer is deactivated.
- The customer has an overdue rental with outstanding tools.
- The rental would exceed three outstanding physical tool units.
- The requested tool does not exist or is deactivated.
- The requested quantity is greater than the available quantity.
- The rental period exceeds seven calendar days.
- The Advisor is not authorized to perform the operation.

The backend must preserve the rental history even when associated catalog records are later updated or deactivated.

### 3.8. Return Management

| Method | Endpoint | Description | Required Role |
|---|---|---|---|
| GET | `/returns` | List return operations | Administrator, Advisor |
| GET | `/returns/{id}` | Retrieve a return by ID | Administrator, Advisor |
| POST | `/returns` | Register a return | Administrator, Advisor |

#### Return Registration Request

`POST /api/v1/returns`

Example for an Advisor:

```json
{
  "rental_id": 1,
  "return_details": [
    {
      "rental_detail_id": 1,
      "returned_quantity": 1
    }
  ]
}
```

Example for an Administrator:

```json
{
  "rental_id": 1,
  "advisor_id": 1,
  "return_details": [
    {
      "rental_detail_id": 1,
      "returned_quantity": 1
    }
  ]
}
```

The backend must:

- Verify that the rental exists.
- Verify that the advisor is authorized.
- Verify that every rental detail belongs to the specified rental.
- Validate the outstanding quantity of each rental detail.
- Prevent returning more units than were originally rented.
- Register the return operation.
- Increase available inventory quantities.
- Update the rental status when necessary.
- Execute the operation within a database transaction.

A rental may have multiple return operations.

#### Advisor Identification

When an Advisor registers a return, the backend must identify the authenticated Advisor through the associated user account and advisor record.

The Advisor must not be allowed to register a return on behalf of another Advisor.

If an Advisor submits an `advisor_id` that does not correspond to their authenticated account, the backend must reject the request with HTTP `403 Forbidden`.

For an Administrator, the `advisor_id` may be provided to identify the Advisor responsible for the operation, provided that the selected Advisor is active and authorized.

The backend must validate the advisor association before registering the return.

### 3.9. Availability Management

| Method | Endpoint | Description | Required Role |
|---|---|---|---|
| GET | `/tools/availability` | Retrieve available tool quantities | Administrator, Advisor |
| GET | `/tools/{id}/availability` | Retrieve availability for a specific tool | Administrator, Advisor |

Availability responses must include:

- Tool ID.
- Tool name.
- Total quantity.
- Available quantity.

Example response:

```json
{
  "success": true,
  "message": "Tool availability retrieved successfully.",
  "data": {
    "id": 1,
    "name": "Electric Drill",
    "total_quantity": 10,
    "available_quantity": 8
  }
}
```

The backend must return updated availability information after successful rental and return operations.

## 4. Standard Response Structures

### 4.1. Successful Response

Successful requests must return a JSON response containing the operation result.

Example:

```json
{
  "success": true,
  "message": "Tool registered successfully.",
  "data": {
    "id": 1,
    "name": "Electric Drill",
    "total_quantity": 10,
    "available_quantity": 10
  }
}
```

### 4.2. Collection Response

Requests returning collections may include pagination metadata.

Example:

```json
{
  "success": true,
  "message": "Tools retrieved successfully.",
  "data": [
    {
      "id": 1,
      "name": "Electric Drill",
      "total_quantity": 10,
      "available_quantity": 10
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 1
  }
}
```

### 4.3. Error Response

Errors must return a consistent JSON structure.

Example:

```json
{
  "success": false,
  "message": "The request could not be processed.",
  "errors": {}
}
```

Validation errors may include field-specific information in the `errors` object.

Example:

```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "The email field must be unique."
    ]
  }
}
```

### 4.4. Authentication Error Response

Requests without valid authentication must return HTTP `401 Unauthorized`.

Example:

```json
{
  "success": false,
  "message": "Unauthenticated.",
  "errors": {}
}
```

### 4.5. Authorization Error Response

Authenticated users without the required permissions must receive HTTP `403 Forbidden`.

Example:

```json
{
  "success": false,
  "message": "You do not have permission to perform this action.",
  "errors": {}
}
```

This response must be used when an Advisor attempts to perform an Administrator-only operation, including customer deactivation or reactivation.

## 5. HTTP Status Codes

| Status Code | Meaning | Usage |
|---|---|---|
| 200 | OK | Successful retrieval or update |
| 201 | Created | Resource successfully created |
| 204 | No Content | Successful operation without response body |
| 400 | Bad Request | Invalid request structure |
| 401 | Unauthorized | Missing or invalid authentication |
| 403 | Forbidden | Authenticated user does not have permission |
| 404 | Not Found | Requested resource does not exist |
| 409 | Conflict | Inventory or business rule conflict |
| 422 | Unprocessable Entity | Validation failure |
| 500 | Internal Server Error | Unexpected server error |

The API must use HTTP status codes consistently.

Examples:

- `401 Unauthorized`: Missing or invalid authentication token.
- `403 Forbidden`: An Advisor attempts to deactivate a customer.
- `404 Not Found`: The requested customer, tool, rental, or return does not exist.
- `409 Conflict`: Inventory is no longer available or a concurrent operation prevents the rental.
- `422 Unprocessable Entity`: A request violates a validation rule.

## 6. Business Rule Validation

The backend must enforce the following rules:

1. A customer cannot have more than three outstanding physical tool units.
2. The maximum rental period is seven calendar days.
3. Customers with overdue outstanding rentals cannot receive additional tools until the overdue tools are returned.
4. Available inventory must never be negative.
5. Available inventory must never exceed total inventory.
6. Returned quantities cannot exceed outstanding rented quantities.
7. Historical rental and return information must remain unchanged when catalog information is updated.
8. Catalog records must use logical deletion.
9. Rental and return operations must preserve historical records.
10. All dates and timestamps must use UTC.
11. Only Administrators may create user accounts.
12. Only Administrators may assign or change user roles.
13. Only Administrators may logically deactivate user accounts.
14. Advisors may register and update tools but cannot logically deactivate them.
15. Customers must not have authenticated user accounts.
16. Deactivated user accounts must not be allowed to authenticate.
17. Administrators and Advisors may register, consult, and update customer records.
18. Only Administrators may logically deactivate customers.
19. Only Administrators may reactivate customers.
20. Advisors attempting to deactivate or reactivate customers must receive HTTP `403 Forbidden`.
21. Customer records must not be physically deleted through the application.
22. Deactivated customers must not receive new rentals until reactivated by an Administrator.
23. Customer deactivation must preserve all historical rental and return records.
24. Advisor rental and return operations must be associated with the authenticated Advisor.
25. Advisors must not perform operations on behalf of another Advisor.
26. The backend must validate all submitted advisor identifiers.
27. Only Administrators may create, update, or deactivate advisor records.
28. Advisors may consult only the operational advisor information required for their work.

Business rules must be validated on the backend, even when the frontend performs preliminary validations.

## 7. Concurrency and Inventory Protection

The API must protect inventory operations against simultaneous requests.

When two Advisors attempt to rent the last available unit:

- The backend must allow only one operation to complete successfully.
- The other request must receive HTTP `409 Conflict`.
- Inventory quantities must remain consistent.
- Failed operations must not leave incomplete rental records or incorrect inventory quantities.

Database transactions and appropriate record-locking mechanisms must protect critical operations.

The backend must also protect the maximum outstanding quantity restriction per customer against simultaneous rental requests.

When a rental or return operation fails, all related database changes must be rolled back.

## 8. Security and Access Control

The API must enforce authentication and authorization for protected operations.

### 8.1. Authentication

Laravel Sanctum must manage API token authentication.

The backend must:

- Validate the submitted credentials during login.
- Generate an authentication token for valid users.
- Require valid tokens for protected endpoints.
- Identify the authenticated user.
- Support token revocation through logout.
- Reject missing or invalid tokens with HTTP `401 Unauthorized`.
- Prevent deactivated accounts from authenticating.
- Never expose password hashes or raw authentication tokens in API responses.

Protected requests must include:

`Authorization: Bearer <token>`

Only user accounts with the `administrator` or `advisor` role may authenticate.

Customers do not have authentication credentials or application accounts.

### 8.2. Role-Based Access Control

The system must implement two authenticated roles:

| Permission | Administrator | Advisor |
|---|---|---|
| Authenticate | Yes | Yes |
| View authorized modules | Yes | Yes |
| Create user accounts | Yes | No |
| Update user accounts | Yes | No |
| Deactivate user accounts | Yes | No |
| Assign or change roles | Yes | No |
| Register tools | Yes | Yes |
| Update tools | Yes | Yes |
| Deactivate tools | Yes | No |
| Register customers | Yes | Yes |
| Consult customers | Yes | Yes |
| Update customers | Yes | Yes |
| Deactivate customers | Yes | No |
| Reactivate customers | Yes | No |
| Create advisor records | Yes | No |
| Update advisor records | Yes | No |
| Deactivate advisor records | Yes | No |
| Consult operational advisor information | Yes | Yes |
| Register rentals | Yes | Yes |
| Consult rentals | Yes | Yes |
| Register returns | Yes | Yes |
| Consult returns | Yes | Yes |
| View authorized operational information | Yes | Yes |

The backend must validate permissions for every protected operation.

The frontend may adapt its navigation and available actions according to the role, but it must not be considered the security enforcement layer.

### 8.3. Account Creation and Role Assignment

Only an authenticated Administrator may create user accounts and assign roles.

The API must reject attempts by Advisors to:

- Create user accounts.
- Change user roles.
- Deactivate user accounts.
- Perform restricted administrative operations.

Customers must be registered through customer management endpoints without creating authentication accounts.

Customer records must not be assigned application roles.

### 8.4. Customer Deactivation and Reactivation Authorization

Customer deactivation and reactivation are Administrator-only operations.

The backend must:

- Verify the authenticated user's role before executing the operation.
- Allow only Administrators to deactivate customers.
- Allow only Administrators to reactivate customers.
- Return HTTP `403 Forbidden` when an Advisor attempts either operation.
- Preserve customer rental and return history.
- Prevent physical deletion of customer records.
- Prevent deactivated customers from receiving new rentals.

The frontend must hide or disable customer deactivation and reactivation actions for Advisors.

However, hiding these actions in the frontend does not replace backend authorization.

### 8.5. Rental and Return Authorization

The backend must associate rental and return operations with the authenticated Advisor when the request is made by an Advisor.

An Advisor must not be able to modify the advisor identification to perform operations on behalf of another Advisor.

Administrators may select an active Advisor for an authorized operation.

The backend must validate that the selected Advisor exists and is active.

### 8.6. Security Requirements

The backend must:

- Validate incoming data.
- Restrict operations according to assigned permissions.
- Prevent unauthorized access to protected resources.
- Avoid exposing passwords, password hashes, or authentication tokens in inappropriate responses.
- Keep credentials and private configuration outside the repository.
- Use HTTPS in production.
- Validate all user-submitted identifiers.
- Apply authorization checks on every protected endpoint.
- Protect database operations through transactions where necessary.
- Preserve historical business information.
- Prevent unauthorized customer deactivation.
- Prevent unauthorized customer reactivation.

## 9. API Validation Requirements

The API implementation will be considered compliant when:

- All documented endpoints are implemented.
- HTTP methods follow the defined contract.
- Requests and responses use JSON.
- Resource identifiers are validated.
- Validation errors use consistent response structures.
- Business rules are enforced by the backend.
- Inventory changes use database transactions.
- Concurrent rental requests are handled correctly.
- Historical records are preserved.
- UTC date and time handling is maintained.
- HTTP status codes are returned consistently.
- Laravel Sanctum authentication is implemented.
- Protected endpoints reject missing or invalid tokens.
- Role-based authorization is enforced by the backend.
- Only Administrators can create user accounts and assign roles.
- Advisors cannot deactivate tools.
- Customers can be registered without authentication accounts.
- Administrators can deactivate and reactivate customers.
- Advisors can register, consult, and update customers but cannot deactivate or reactivate them.
- Customer deactivation attempts by Advisors return HTTP `403 Forbidden`.
- Physical deletion of customer records is prevented through the application.
- Deactivated customers cannot receive new rentals.
- Customer rental and return history is preserved after deactivation.
- Advisor rental and return operations are associated with the authenticated Advisor.
- Advisors cannot perform rental or return operations on behalf of other Advisors.
- Automated tests verify allowed and denied operations for each role.

## 10. Scope Limitations

The initial API scope does not include:

- Electronic payments.
- Online payment processing.
- Automatic payment collection.
- Scheduling or appointment management.
- Customer authentication or customer accounts.
- Features not defined in the project requirements.

Any additional functionality must be evaluated and documented before implementation.

---

**End of Document**

**Project:** RENT-TOOLS  
**Document:** API Contract  
**Version:** 1.1.1
