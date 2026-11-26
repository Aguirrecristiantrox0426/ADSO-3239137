
# RENT-TOOLS Software Requirements Specification

**Project:** RENT-TOOLS  
**Document:** Software Requirements Specification  
**Version:** 1.1.0  
**Status:** Draft  
**Domain:** Construction Equipment Rental Management

---

## 1. Project Overview

RENT-TOOLS is a software system designed to manage the rental of construction tools and equipment.

The system will support the main operational processes of a construction equipment rental business, including tool management, customer management, advisor management, user account management, authentication, authorization, rentals, returns, and availability control.

The solution will consist of a web application, a backend API, a relational database, infrastructure configuration, automated tests, and a public static website.

The system will have two authenticated user roles: Administrator and Advisor. Customers will be registered as business entities and will not have accounts or direct access to the application.

## 2. Problem Statement

Managing construction equipment rentals requires reliable control over inventory, customers, rental periods, returns, equipment availability, and the users responsible for operating the system.

Without a centralized system, rental operations may experience problems such as inaccurate inventory information, duplicate rentals, insufficient historical records, unauthorized operations, and difficulties identifying overdue rentals.

RENT-TOOLS aims to organize these processes through a centralized application that enforces business rules, controls access according to user roles, and preserves the integrity of operational information.

## 3. Project Objectives

### 3.1 General Objective

Develop a web-based system to manage construction tool rentals, ensuring accurate inventory control, customer management, rental tracking, return management, and secure access according to assigned user roles.

### 3.2 Specific Objectives

- Manage construction tools and their inventory information.
- Register and manage customer information.
- Manage advisors responsible for rental operations.
- Manage administrator and advisor user accounts.
- Authenticate users before granting access to protected functionality.
- Authorize system operations according to the user's assigned role.
- Register and control rental transactions.
- Record tool returns and rental history.
- Calculate and control tool availability.
- Enforce rental restrictions and business rules.
- Provide a public website with information about the system.
- Verify system behavior through automated tests.

## 4. Project Scope

### 4.1 Included Features

The project includes the following functional areas:

1. **Authentication:** Authenticate administrators and advisors using individual accounts.
2. **User Account Management:** Allow administrators to create, consult, update, and logically deactivate user accounts.
3. **Role-Based Authorization:** Restrict protected operations according to Administrator and Advisor roles.
4. **Tool Management:** Register, consult, update, and logically deactivate construction tools, according to role permissions.
5. **Customer Management:** Register and manage customer information.
6. **Advisor Management:** Manage the advisors responsible for rental operations.
7. **Rental Management:** Register rentals and validate customer and inventory restrictions.
8. **Return Management:** Record returns and update tool availability.
9. **Availability Management:** Maintain accurate information about total and available units.
10. **Historical Records:** Preserve rental and return information.
11. **Public Website:** Provide a static website presenting the system.
12. **Automated Testing:** Verify business rules, authentication, authorization, API behavior, and operational processes.

### 4.2 Excluded Features

The implementation must remain within the scope established by the improvement plan.

The following features are excluded from the initial implementation:

- Customer accounts and customer authentication.
- Direct customer access to the internal management application.
- Online payments.
- Features not explicitly included in the approved requirements.

Features not explicitly included in the approved requirements must not be considered part of the initial implementation.

## 5. User Roles

The system defines two authenticated user roles. Each account must be associated with one of these roles.

### 5.1 Administrator

The Administrator is responsible for managing system accounts and supervising the operation of RENT-TOOLS.

The Administrator has permission to:

- Create administrator and advisor accounts.
- Consult, update, and logically deactivate user accounts.
- Register, consult, update, and logically deactivate tools.
- Manage tool categories.
- Register, consult, and update customer information.
- Manage advisor information.
- Register, consult, and manage rentals.
- Register, consult, and manage returns.
- Consult general operational information and dashboard statistics.
- Logically deactivate customer records.
- Reactivate previously deactivated customer records.


Only the Administrator may create user accounts and assign user roles.

### 5.2 Advisor

The Advisor is responsible for carrying out the daily rental operations.

The Advisor has permission to:

- Consult tools and their availability.
- Register new tools.
- Update existing tool information.
- Register, consult, and update customer information.
- Consult advisor information relevant to operations.
- Register and manage rentals.
- Register and consult returns.
- Consult operational information and authorized dashboard statistics.


The Advisor cannot:

- Create, update, or deactivate user accounts.
- Assign or change user roles.
- Logically deactivate tools.
- Logically deactivate customer records.
- Reactivate previously deactivated customer records.
- Access administrative user management functionality.


### 5.3 Customer as a Business Entity

A customer represents a person who requests construction tools for rental.

Customers are registered and managed by authorized system users.

A customer:

- Does not have a system user account.
- Does not authenticate into the application.
- Does not have a system role.
- Has rental and return information associated with their customer record.

The system must maintain customer information and enforce restrictions associated with active and overdue rentals.

## 6. User Stories

The following ten user stories define the agreed functional scope. Acceptance is verified through the functional requirements, business rules, API contract, tasks, and automated tests referenced in the traceability matrix.

| ID | User story | Acceptance reference |
|---|---|---|
| US-01 | As an Administrator, I want to manage user accounts and roles so that access is assigned according to responsibilities. | FR-05 to FR-08, NFR-03 |
| US-02 | As an authenticated user, I want to sign in and sign out securely so that protected operations use a valid session. | FR-05, FR-20, NFR-03 |
| US-03 | As an Administrator, I want to manage tool categories so that the catalogue is organized consistently. | FR-01, FR-02 |
| US-04 | As an Administrator or Advisor, I want to register, consult, and update tools so that their inventory is available for rentals. | FR-01, FR-02, FR-14 |
| US-05 | As an Administrator or Advisor, I want to register, consult, and update customers without creating credentials for them so that commercial records are maintained. | FR-03, FR-15 |
| US-06 | As an Administrator, I want to deactivate or reactivate a customer logically so that history is preserved while new rentals are controlled. | FR-03, FR-15 |
| US-07 | As an authorized user, I want to manage advisor records so that rental and return operations identify their responsible advisor. | FR-04, FR-08 |
| US-08 | As an authorized user, I want to register and consult rentals so that customer limits, due dates, and availability are enforced. | FR-09 to FR-14 |
| US-09 | As an authorized user, I want to register partial or complete returns so that availability and rental status are updated without losing history. | FR-13 to FR-16 |
| US-10 | As a visitor, I want to access a public static site that explains RENT-TOOLS so that I can learn about the service without accessing private data. | FR-18 |

## 7. Functional Requirements

### FR-01: Tool Management

The system shall allow administrators and advisors to register, consult, and update construction tools according to their assigned permissions.

### FR-02: Logical Deactivation of Tools

The system shall allow only administrators to logically deactivate catalog records without physically deleting historical information.


### FR-03: Customer Management

The system shall allow administrators and advisors to register, consult, and update customer information.

The system shall allow only Administrators to logically deactivate and reactivate customer records.

Advisors shall not be permitted to deactivate or reactivate customer records.

Customer deactivation must preserve the customer record and its historical rental and return relationships.

The application shall not physically delete customer records.

The backend shall enforce these permissions independently of frontend restrictions.


### FR-04: Advisor Management

The system shall allow administrators to manage advisor information and associate advisor records with their corresponding user accounts.

### FR-05: User Authentication

The system shall authenticate administrators and advisors using individual credentials before granting access to protected functionality.


### FR-06: User Account Management

The system shall allow only Administrators to create, consult, update, and logically deactivate user accounts.

Only Administrators shall be permitted to create authenticated user accounts and manage their account status.

The system shall prevent Advisors from creating, updating, or deactivating user accounts.

User account deactivation must be logical and must preserve historical information associated with the account.

Deactivated user accounts must not be allowed to authenticate or access protected system resources.

The application shall not physically delete user accounts through normal administrative operations.

The backend shall enforce these restrictions independently of frontend visibility controls.


### FR-07: Role Assignment

The system shall allow only administrators to assign the Administrator or Advisor role when creating or updating user accounts.


### FR-08: Role-Based Authorization

The system shall restrict protected operations according to the authenticated user's assigned role.

The system shall support two authenticated roles: Administrator and Advisor.

The backend API shall enforce authorization independently of frontend visibility or navigation restrictions.

Only Administrators shall be permitted to create, consult, update, and logically deactivate user accounts, assign or change user roles, manage tool categories, logically deactivate tools, and logically deactivate or reactivate customer records.

Advisors shall be permitted to perform authorized operational activities, including registering and updating tools and customers, consulting information, and managing rentals and returns.

Advisors shall not be permitted to create or manage user accounts, assign or change roles, deactivate tools, or deactivate or reactivate customer records.

Unauthenticated requests to protected resources shall return HTTP 401 Unauthorized.

Authenticated users attempting to perform operations outside their assigned permissions shall receive HTTP 403 Forbidden.

The backend shall validate the authenticated user's permissions before executing every protected operation.



### FR-09: Rental Registration

The system shall allow an Advisor or Administrator to register a rental after validating customer restrictions and tool availability.

When an Advisor registers a rental, the backend shall automatically associate the operation with the authenticated Advisor.

When an Administrator registers a rental, the system shall allow the selection of an active Advisor responsible for the operation.

The system shall validate that the customer is active and eligible to receive a rental before completing the operation.

The system shall validate tool availability before confirming the rental.

The system shall preserve the relationship between the rental, the customer, the responsible Advisor, and the rented tools.



### FR-10: Maximum Rental Limit

The system shall prevent a customer from having more than three physical tool units rented simultaneously without returning them.

The system shall calculate the limit based on the total number of physical tool units currently pending return across all active rentals associated with the customer.

The system shall reject a new rental operation if the requested tool units would cause the customer to exceed the maximum limit of three pending units.

The backend shall validate this restriction before confirming the rental operation.

The system shall update the customer's pending rental quantity when tools are successfully returned.


### FR-11: Rental Period Validation

The system shall enforce a maximum rental period of seven calendar days.

The system shall validate the requested rental start date and expected return date before confirming a rental.

The system shall reject rental operations when the requested rental period exceeds seven calendar days.

The backend shall enforce this restriction before saving the rental.

The system shall calculate and preserve the expected return date for each rental.

### FR-12: Overdue Rental Restriction

The system shall prevent customers with overdue rentals from receiving additional tools until all overdue tools have been returned.

The system shall identify an overdue rental when its expected return date has passed and the corresponding tools have not been returned.

The system shall validate the customer's overdue rental status before confirming a new rental operation.

The backend shall enforce this restriction before registering the rental.

The system shall remove the overdue restriction when all overdue tools associated with the customer have been successfully returned.

### FR-13: Return Registration

The system shall allow Administrators and Advisors to register the return of rented tool units.

The system shall associate each return with its corresponding rental and customer.

The system shall record the return date and the quantity of tool units returned.

The system shall validate that the quantity being returned does not exceed the number of tool units pending return in the corresponding rental.

After a successful return, the system shall update the rental information and increase the available quantity of the corresponding tool.

The system shall preserve the return history associated with the rental and customer.

The backend shall validate and process each return operation to maintain inventory consistency.

### FR-14: Inventory Availability

The system shall maintain the total quantity and available quantity of each registered tool.

The system shall ensure that the available quantity never becomes negative or exceeds the total quantity registered for a tool.

When a rental is successfully registered, the system shall decrease the available quantity according to the number of tool units rented.

When a return is successfully registered, the system shall increase the available quantity according to the number of tool units returned.

The system shall validate tool availability before confirming a rental operation.

The backend shall enforce inventory consistency when processing rental and return operations.

The system shall prevent concurrent operations from producing an available quantity greater than the total quantity or lower than zero.

### FR-15: Historical Information

The system shall preserve rental and return records and the relevant information associated with each transaction.

The system shall maintain the relationship between each rental, the customer, the responsible Advisor, and the rented tool units.

The system shall preserve the rental date, expected return date, actual return date, and quantities involved in each transaction.

The system shall retain historical rental and return information when a customer, tool, or user account is logically deactivated.

The system shall not physically delete rental or return records through normal application operations.

The system shall restrict access to historical information according to the authenticated user's role and permissions.

### FR-16: Concurrency Control

The system shall prevent simultaneous rental operations from assigning the same available tool units to multiple customers.

The backend shall validate tool availability at the moment the rental operation is processed.

The system shall process inventory updates in a way that preserves data consistency when multiple rental or return operations occur simultaneously.

If a rental operation conflicts with another operation because the requested quantity is no longer available, the system shall reject the conflicting operation.

The system shall return HTTP 409 Conflict when an inventory concurrency conflict prevents a rental operation from being completed.

The system shall ensure that concurrent operations do not cause the available quantity to become negative or exceed the total registered quantity.

### FR-17: API Communication

The backend shall expose documented API endpoints to support communication with the React frontend and supporting tools.

The API shall use a consistent response structure for successful operations and errors.

Protected endpoints shall require a valid authentication token and the appropriate role permissions.

The backend shall return HTTP 401 Unauthorized when a request to a protected endpoint does not include valid authentication.

The backend shall return HTTP 403 Forbidden when an authenticated user attempts to access an operation that is not permitted for their role.

The backend shall validate incoming data before processing requests and return appropriate validation errors when the submitted data is invalid.

The API shall return HTTP 409 Conflict when an operation cannot be completed because of an inventory concurrency conflict.

### FR-18: Public Website

The project shall provide a static public website presenting the RENT-TOOLS system.

The public website shall provide general information about the system, its purpose, and its main features.

The public website shall be accessible without authentication.

The public website shall not expose private customer information, internal business records, or protected application resources.

The public website shall not provide access to administrative or operational functionalities.

The public website shall be responsive and accessible through commonly used web browsers.

### FR-19: Automated Verification

The project shall include automated tests to verify the functional requirements, authentication, authorization, business rules, data integrity, and API responses.

The system shall include automated tests for Administrator and Advisor permissions.

The system shall verify that unauthenticated requests to protected endpoints return HTTP 401 Unauthorized.

The system shall verify that authenticated users without the required permissions receive HTTP 403 Forbidden.

The system shall include tests for the maximum rental limit of three physical tool units per customer, the maximum rental period of seven days, and the restriction applied to customers with overdue rentals.

The system shall include tests for rental registration, return registration, inventory availability, and concurrent rental operations.

The system shall verify that inventory conflicts return HTTP 409 Conflict.

The system shall include tests to ensure that logical deactivation preserves customer, tool, user account, rental, and return historical information.

The automated tests shall be repeatable and executable as part of the project's verification process.
### FR-20: Session and Token Management

The system shall use Laravel Sanctum to issue and validate authentication tokens for Administrators and Advisors.

The system shall require a valid authentication token when accessing protected API endpoints.

The system shall allow authenticated users to terminate their sessions by revoking their active authentication token.

After a token has been revoked, the system shall reject subsequent requests that use that token.

The system shall provide an authenticated endpoint to retrieve the current user's account information and assigned role.

The system shall prevent logically deactivated user accounts from authenticating or accessing protected resources.

The backend shall validate authentication tokens independently of frontend session management.

## 8. Business Rules

The system must enforce the following business rules:

| BR-01 | A customer may have a maximum of three physical tool units pending return at the same time. The limit shall be calculated based on the total number of units pending return across all active rentals associated with the customer. |
| BR-02 | Each rental shall have a maximum duration of seven calendar days. The expected return date shall be recorded when the rental is registered, and the backend shall reject rental periods exceeding this limit. |
| BR-03 | Customers with overdue rentals shall not receive additional tools until all overdue tools have been successfully returned. A rental is considered overdue when its expected return date has passed and the corresponding tools remain pending return. |
| BR-04 | The available quantity of a tool must always remain between zero and its total registered quantity. Successful rentals shall decrease availability, and successful returns shall increase it. The backend shall preserve inventory consistency during concurrent operations. |
| BR-05 | Historical information associated with a rental or return must not change when tool catalog information is modified. Transaction records shall preserve the relevant information as it existed when the operation was registered. |
| BR-06 | Rental and return records must be preserved. Tool, customer, and user account deactivation must be logical and must preserve associated historical information. The application shall not physically delete customer records or rental and return history through normal operations. |

Authorization, account management, and customer identity boundaries are functional constraints specified in FR-03 and FR-05 to FR-08; they are not additional business rules.

## 9. Non-Functional Requirements

### NFR-01: Data Integrity

The system shall preserve the consistency and integrity of inventory, rental, return, customer, and user account information.

The backend shall validate data before creating or updating records.

The system shall maintain valid relationships between customers, rentals, returns, tools, and the users responsible for rental operations.

The system shall prevent available tool quantities from becoming negative or exceeding the total registered quantity.

Rental and return operations shall preserve historical information, even when related customer, tool, or user account records are logically deactivated.

The system shall process related database changes consistently to prevent incomplete rental or return transactions.

### NFR-02: Concurrency

The system shall protect rental and return operations against simultaneous requests that could compromise inventory consistency.

The backend shall validate tool availability when processing each rental operation.

The system shall prevent multiple concurrent rental operations from assigning the same available physical tool units to different customers.

The system shall process availability validation and inventory updates consistently to prevent available quantities from becoming negative or exceeding the total registered quantity.

When an inventory concurrency conflict prevents a rental operation from completing, the API shall return HTTP 409 Conflict.

The system shall preserve data consistency when concurrent rental and return operations are processed.

### NFR-03: Security

The system shall:

- Validate incoming information before processing requests.
- Require valid authentication tokens for access to protected API endpoints.
- Use Laravel Sanctum for authentication token issuance and validation.
- Protect user credentials using secure password hashing.
- Avoid exposing passwords, authentication tokens, or other sensitive information in API responses.
- Enforce authorization on the backend independently of frontend visibility or navigation restrictions.
- Restrict protected operations according to the authenticated user's assigned role.
- Return HTTP 401 Unauthorized when a request to a protected endpoint does not contain valid authentication.
- Return HTTP 403 Forbidden when an authenticated user attempts to perform an operation without the required permissions.
- Prevent logically deactivated user accounts from authenticating or accessing protected resources.
- Allow authenticated users to terminate their sessions by revoking their active authentication tokens.
- Ensure that revoked authentication tokens cannot be used to access protected resources.
- Enforce least privilege: only Administrators can manage accounts, roles, categories, tool deactivation, and customer deactivation or reactivation; Advisors are limited to their authorized operational activities.
- Treat customers exclusively as business records without accounts, credentials, or application roles.

### NFR-04: Maintainability

The source code must be organized into components with clearly defined responsibilities and documented interfaces.

### NFR-05: Testability

The application shall support automated testing to verify functional requirements, security restrictions, business rules, and data integrity.

The project shall contain at least fifteen automated test files, according to the improvement plan.

The automated tests shall verify authentication and authorization for Administrator and Advisor roles.

The tests shall verify that unauthenticated requests to protected endpoints return HTTP 401 Unauthorized.

The tests shall verify that authenticated users without the required permissions receive HTTP 403 Forbidden.

The tests shall cover the maximum rental limit of three physical tool units per customer, the maximum rental period of seven calendar days, and restrictions for customers with overdue rentals.

The tests shall verify rental registration, return registration, inventory availability, and concurrent operations.

The tests shall verify that inventory concurrency conflicts return HTTP 409 Conflict.

The tests shall verify that logical deactivation preserves customer, tool, user account, rental, and return historical information.

Automated tests shall be repeatable and executable as part of the project's verification process.

## 10. Technical Constraints

The implementation must use the technologies established in the improvement plan:

| Component | Technology |
|---|---|
| Frontend | React |
| Backend | PHP with Laravel |
| Authentication | Laravel Sanctum |
| Database | Relational database |
| Infrastructure | Docker |
| Public Website | Static web technologies |
| Verification | Automated tests |

The application must use container-based infrastructure configuration.

## 11. Date and Time Requirements

- Dates and timestamps must be stored and processed using UTC.
- Date handling must remain consistent across the backend, database, and frontend.
- Rental duration must be calculated using the rental start date and expected return date, respecting the maximum period of seven calendar days.
- The frontend must display dates and times consistently to users.
- User-facing messages must be displayed in Spanish.

## 12. Data and Historical Requirements

The system must preserve the information required to reconstruct rental and return operations.

Modifying a tool in the catalog must not modify historical information associated with previously registered rentals.

Rental and return records must remain available as historical records and must not be physically deleted through normal application operations.

Tool, customer, and user account deactivation must use logical deactivation and preserve their associated historical information.

Only Administrators may logically deactivate or reactivate customer records.

Only Administrators may create, update, and logically deactivate user accounts, as well as assign or change user roles.

User accounts must be associated with an assigned role.

Advisor user accounts must be associated with the corresponding advisor business record when applicable.

Customer records must remain independent of system user accounts and must not have authentication credentials or application roles.

The system must preserve the relationships between customers, tools, rentals, returns, and the users responsible for rental operations.

Deactivated user accounts must not be allowed to authenticate or access protected system resources.

## 13. Acceptance Criteria

The system will be considered compliant with this specification when:

- Administrators and Advisors can authenticate using valid credentials.
- Invalid credentials are rejected.
- Only Administrators can create, consult, update, and logically deactivate user accounts.
- Only Administrators can assign or change user roles.
- The system supports the Administrator and Advisor authenticated roles.
- Administrators can manage tools, customers, categories, rentals, and returns according to their permissions.
- Only Administrators can logically deactivate or reactivate customer records.
- Only Administrators can logically deactivate tools.
- Advisors can register, consult, and update tools but cannot deactivate them.
- Advisors can register, consult, and update customer records but cannot deactivate or reactivate them.
- Advisors can perform authorized rental and return operations.
- Customers cannot authenticate into the application or have application roles.
- Protected API operations reject unauthenticated requests with HTTP 401 Unauthorized.
- Authenticated users attempting operations without the required permissions receive HTTP 403 Forbidden.
- Deactivated user accounts cannot authenticate or access protected resources.
- Authenticated users can terminate their sessions, and revoked tokens cannot be reused.
- Rental operations enforce the maximum of three physical tool units pending return per customer.
- Rental operations enforce the maximum rental period of seven calendar days.
- Customers with overdue rentals cannot receive additional tools until all overdue tools have been returned.
- Inventory availability remains between zero and the total registered quantity.
- Successful returns update the corresponding available inventory quantity.
- Rental and return records preserve their historical information after catalog or account deactivation.
- Historical rental information remains unchanged after catalog modifications.
- Concurrent requests cannot rent the same last available physical tool unit.
- Conflicting rental operations return HTTP 409 Conflict.
- The public static website is available without authentication.
- The public website does not expose private customer information or protected application resources.
- Automated tests verify the defined business rules, authentication, authorization, and system behavior.
- The project includes at least fifteen automated test files.

## 14. Requirements Traceability

Requirements must maintain traceability throughout the development process.

The project must establish relationships between:

- Functional requirements.
- Non-functional requirements.
- Business rules.
- User stories.
- Acceptance criteria.
- Technical tasks.
- Implementation components.
- Automated tests.

Each requirement must be traceable to the corresponding implementation tasks and verification activities.

| Scope item | Functional requirements | Rules / quality | Tasks | Evidence |
|---|---|---|---|---|
| Authentication and roles | FR-05 to FR-08, FR-20 | NFR-03 | TASK-06, TASK-13, TASK-21, TASK-22 | API and frontend authentication tests |
| Catalogue and categories | FR-01, FR-02, FR-14 | BR-04, NFR-01 | TASK-07, TASK-14, TASK-21 | API tests and availability responses |
| Customers and history | FR-03, FR-15 | BR-05, BR-06, NFR-01 | TASK-08, TASK-15, TASK-21 | logical-deactivation and history tests |
| Rentals and returns | FR-09 to FR-16 | BR-01 to BR-06, NFR-01, NFR-02 | TASK-10 to TASK-12, TASK-16, TASK-21 | rental, return, conflict, and integrity tests |
| Public site | FR-18 | NFR-04 | TASK-17, TASK-18 | manual responsive verification |
| Test suite | FR-19 | NFR-05 | TASK-21 to TASK-23 | test-file inventory and execution report |

Changes to requirements must be documented and justified.

The traceability information must be updated when requirements, implementation tasks, or automated tests change.

---

**End of Document**

**Project:** RENT-TOOLS  
**Document:** Software Requirements Specification  
**Version:** 1.2.0

