# RENT-TOOLS API Contract

**Project:** RENT-TOOLS  
**Document:** API Contract  
**Version:** 1.0.0  
**Status:** Draft  
**Architecture:** REST API  
**Backend:** Laravel  
**Frontend:** React  

---

## 1. Introduction

This document defines the API contract for RENT-TOOLS.

It describes the endpoints, HTTP methods, request parameters, response structures, status codes, and validation rules required for communication between the React frontend and the Laravel backend.

The API supports tool management, customer management, advisor management, rentals, returns, and inventory availability.

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

Protected endpoints must require valid authentication credentials according to the project's security implementation.

### 2.4. Resource Identification

Each resource must be identified by a unique numeric ID.

The API must validate resource identifiers before processing requests.

## 3. API Endpoints

The following endpoints define the initial API contract.

### 3.1. Tool Management

| Method | Endpoint | Description |
|---|---|---|
| GET | `/tools` | List active tools |
| GET | `/tools/{id}` | Retrieve a tool by ID |
| POST | `/tools` | Register a new tool |
| PUT | `/tools/{id}` | Update a tool |
| DELETE | `/tools/{id}` | Logically delete a tool |


Tool registration request example:

```json
{
  "name": "Electric Drill",
  "description": "Professional electric drill",
  "total_quantity": 10
}
```

The backend must initialize `available_quantity` according to the registered total quantity.

Tool updates must preserve historical rental information.

Tools with rental history must not be physically deleted.

### 3.2. Customer Management

| Method | Endpoint | Description |
|---|---|---|
| GET | `/customers` | List customers |
| GET | `/customers/{id}` | Retrieve a customer by ID |
| POST | `/customers` | Register a customer |
| PUT | `/customers/{id}` | Update a customer |
| DELETE | `/customers/{id}` | Logically delete a customer |

Customer registration request example:

```json
{
  "full_name": "Juan Perez",
  "identification_number": "123456789",
  "phone": "3001234567",
  "email": "juan@example.com"
}
```

The identification number must be unique.

Customers associated with historical rentals must not be physically deleted.

### 3.3. Advisor Management

| Method | Endpoint | Description |
|---|---|---|
| GET | `/advisors` | List advisors |
| GET | `/advisors/{id}` | Retrieve an advisor by ID |
| POST | `/advisors` | Register an advisor |
| PUT | `/advisors/{id}` | Update an advisor |
| DELETE | `/advisors/{id}` | Logically delete an advisor |

Advisor registration request example:

```json
{
  "full_name": "Maria Rodriguez",
  "identification_number": "987654321",
  "email": "maria@example.com"
}
```

The identification number must be unique.

Advisor records associated with historical operations must be preserved.

### 3.4. Rental Management

| Method | Endpoint | Description |
|---|---|---|
| GET | `/rentals` | List rentals |
| GET | `/rentals/{id}` | Retrieve a rental by ID |
| POST | `/rentals` | Register a rental |
| GET | `/rentals/{id}/details` | Retrieve rental details |

Rental registration request example:

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
- Validate that the advisor exists and is authorized to perform the operation.
- Verify tool availability.
- Enforce the maximum of three outstanding physical tool units per customer.
- Set the rental date and due date using UTC.
- Enforce the maximum rental period of seven calendar days.
- Record the rental and its details.
- Decrease available inventory quantities.
- Execute the operation within a database transaction.

The backend must calculate the due date according to the rental date and the seven-day limit.

### 3.5. Return Management

| Method | Endpoint | Description |
|---|---|---|
| GET | `/returns` | List return operations |
| GET | `/returns/{id}` | Retrieve a return by ID |
| POST | `/returns` | Register a return |

Return registration request example:

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

### 3.6. Availability Management

| Method | Endpoint | Description |
|---|---|---|
| GET | `/tools/availability` | Retrieve available tool quantities |
| GET | `/tools/{id}/availability` | Retrieve availability for a specific tool |

Availability responses must include:

- Tool ID.
- Tool name.
- Total quantity.
- Available quantity.

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

## 5. HTTP Status Codes

| Status Code | Meaning | Usage |
|---|---|---|
| 200 | OK | Successful retrieval or update |
| 201 | Created | Resource successfully created |
| 204 | No Content | Successful operation without response body |
| 400 | Bad Request | Invalid request structure |
| 401 | Unauthorized | Missing or invalid authentication |
| 403 | Forbidden | User does not have permission |
| 404 | Not Found | Requested resource does not exist |
| 409 | Conflict | Inventory or business rule conflict |
| 422 | Unprocessable Entity | Validation failure |
| 500 | Internal Server Error | Unexpected server error |

The API must use HTTP status codes consistently.

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

Business rules must be validated on the backend, even when the frontend performs preliminary validations.

## 7. Concurrency and Inventory Protection

The API must protect inventory operations against simultaneous requests.

When two advisors attempt to rent the last available unit:

- The backend must allow only one operation to complete successfully.
- The other request must receive HTTP `409 Conflict`.
- Inventory quantities must remain consistent.
- Failed operations must not leave incomplete rental records or incorrect inventory quantities.

Database transactions and appropriate record-locking mechanisms must protect critical operations.

## 8. Security and Access Control

The API must enforce authentication and authorization for protected operations.

The backend must:

- Validate authentication credentials.
- Restrict operations according to assigned permissions.
- Validate incoming data.
- Prevent unauthorized access to protected resources.
- Avoid exposing sensitive information in error responses.
- Use HTTPS in production.

The exact authentication mechanism and role definitions must follow the project's security architecture.

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

## 10. Scope Limitations

The initial API scope does not include:

- Electronic payments.
- Online payment processing.
- Automatic payment collection.
- Scheduling or appointment management.
- Features not defined in the project requirements.

Any additional functionality must be evaluated and documented before implementation.

---

**End of Document**

**Project:** RENT-TOOLS  
**Document:** API Contract  
**Version:** 1.0.0

