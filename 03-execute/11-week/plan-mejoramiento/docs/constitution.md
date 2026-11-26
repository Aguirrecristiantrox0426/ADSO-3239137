# RENT-TOOLS Constitution

**Project:** RENT-TOOLS  
**Version:** 1.1.1  
**Status:** Draft  
**Project type:** Academic software development project  
**Domain:** Construction equipment rental management

---

## 1. Project Purpose

RENT-TOOLS is a software system designed to manage the rental of construction tools and equipment.

The system aims to organize and control the main operations related to tool management, customers, advisors, rentals, returns, and availability.

The solution will provide a web application supported by a backend API, a database, infrastructure configuration, and a public website presenting the product.

## 2. Technology Principles

The project must follow the technologies established in the improvement plan:

- **Frontend:** React.
- **Backend:** PHP with Laravel.
- **Authentication:** Laravel Sanctum using API tokens.
- **Database:** A relational database managed through containers.
- **Infrastructure:** Docker and container-based configuration.
- **Public website:** Static website using web technologies.
- **Testing:** Automated tests.

All components must be organized in their corresponding project directories.

## 3. Architecture and Separation of Responsibilities

The system must maintain a clear separation of responsibilities.

The project is organized into the following components:

- `docs/`: Technical documentation, specifications, architecture, and decisions.
- `api/`: Backend application, business logic, authentication, authorization, and API endpoints.
- `app/`: React frontend application.
- `page/`: Public static website.
- `infra/`: Database, Docker, and infrastructure configuration.
- `tool/`: Supporting tools, automation, and data seeding through the API.

Each component must have a defined responsibility and communicate through documented interfaces.

The backend API is the authoritative layer for business rules, authentication, and authorization. Frontend restrictions must not be considered a substitute for backend security controls.

## 4. Business Rules Integrity

The system must enforce the following business rules:

1. A customer may have a maximum of three tools rented simultaneously without returning them.
2. Each rental has a maximum rental period of seven days.
3. Customers with overdue rentals cannot receive additional tools until the overdue tools are returned.
4. Available units must always remain between zero and the total number of units.
5. Historical information associated with a rental must remain unchanged when catalog information is modified.
6. Rental and return records must be preserved as historical records. Catalog records must use logical deletion instead of physical deletion.
7. Customer records may be logically deactivated only by an Administrator. Customer records must not be physically deleted through the application.
8. Deactivating a customer must preserve the customer's historical rentals and returns.

These rules must be enforced by the backend and verified through automated tests.

## 5. Data Integrity and Concurrency

The system must guarantee data consistency during rental and return operations.

The following principles are mandatory:

- Availability must never become negative.
- Rental operations must validate the current availability of tools.
- Concurrent requests must not allow the same available unit to be rented more than once.
- Database transactions must protect operations that modify availability and rental records.
- Conflicting operations must return HTTP `409 Conflict` when the requested resource is no longer available.
- Historical rental information must preserve the relevant data from the moment of the operation.

## 6. User Roles and Access Control

The system must implement role-based access control with two authenticated roles:

- **Administrator:** Responsible for administrative management and overall system operations.
- **Advisor:** Responsible for authorized operational activities related to tools, customers, rentals, and returns.

Customers are business entities registered in the system. They do not have user accounts, authentication credentials, or access roles.

### 6.1 Administrator Permissions

The Administrator is authorized to:

- Create Administrator and Advisor accounts.
- Consult, update, and logically deactivate user accounts.
- Assign roles when creating user accounts.
- Register, consult, and update tools.
- Logically deactivate tools.
- Manage tool categories.
- Register, consult, and update customers.
- Logically deactivate customer records.
- Consult and manage advisor information.
- Register and manage rentals.
- Register and consult returns.
- Consult general dashboard information and statistics.

### 6.2 Advisor Permissions

The Advisor is authorized to:

- Authenticate and access authorized application modules.
- Consult tools and their availability.
- Register new tools.
- Update existing tool information.
- Register, consult, and update customers.
- Consult advisor information required for authorized operational activities.
- Register and manage rentals according to business rules.
- Register and consult returns.
- Consult authorized operational information and dashboard data.

The Advisor is not authorized to:

- Create user accounts.
- Assign or change user roles.
- Update or deactivate user accounts.
- Logically deactivate tools.
- Logically deactivate customer records.
- Perform administrative operations restricted to the Administrator.

Customer deactivation is exclusively reserved for the Administrator. Advisors may register, consult, and update customer records, but they must not deactivate them.

If an Advisor attempts to deactivate a customer through the API, the backend must reject the request with HTTP `403 Forbidden`.

Customer deactivation must be logical. The application must preserve customer records and their historical rental and return relationships. Physical deletion of customer records through the application is not permitted.

### 6.3 Account Creation and Role Assignment

Only an authenticated Administrator may create user accounts.

The Administrator must assign the appropriate role when creating an account. Users must not be able to assign or modify their own roles.

The system must reject unauthorized account creation and role modification attempts.

### 6.4 Authorization Enforcement

All protected operations must validate the authenticated user's role and permissions in the backend API.

React may hide or disable interface elements according to the user's role, but these visual restrictions do not replace backend authorization.

Unauthorized requests must be rejected even when they are sent directly to the API.

Customer deactivation endpoints must be accessible only to authenticated Administrators.

The backend must return HTTP `403 Forbidden` when an authenticated Advisor attempts to execute a customer deactivation operation.

## 7. Authentication and Session Security

The application must use Laravel Sanctum for API authentication through tokens.

The following principles are mandatory:

- Protected API requests must require valid authentication.
- Authenticated requests must transmit the token using the `Authorization: Bearer` header.
- The backend must identify the authenticated user and verify the assigned role.
- Invalid or missing authentication must return HTTP `401 Unauthorized`.
- Authenticated users without the required permissions must receive HTTP `403 Forbidden`.
- Users must be able to terminate their authenticated session through the logout operation.
- Tokens must be managed and revoked according to the authentication flow.
- Passwords must never be stored in plain text.
- Authentication credentials and private configuration must not be exposed to the frontend or repository.

## 8. Security Principles

The application must follow basic software security principles:

- Validate all incoming data.
- Protect access to restricted operations.
- Apply authorization according to the user's assigned role.
- Enforce permissions in the backend independently of frontend restrictions.
- Avoid exposing sensitive information.
- Store configuration and credentials outside the source code.
- Never commit `.env` files or private credentials to the repository.
- Prevent unauthorized account creation and role modification.
- Protect authentication tokens and password information.
- Restrict customer deactivation operations to the Administrator role.
- Reject unauthorized customer deactivation attempts with HTTP `403 Forbidden`.

## 9. Date and Time Management

All dates and timestamps stored and processed by the system must use UTC.

The application must maintain consistent date handling across the backend, database, and frontend.

User-facing messages must be displayed in Spanish.

## 10. Software Quality

All components must follow these quality principles:

- Maintain readable and organized source code.
- Use meaningful names for variables, classes, methods, and files.
- Avoid unnecessary code duplication.
- Keep responsibilities separated.
- Document important technical decisions.
- Maintain consistency between specifications and implementation.
- Avoid implementing functionality outside the established project scope without justification.
- Keep authentication and authorization logic centralized in the backend.

## 11. Testing Principles

Automated testing is mandatory.

Tests must verify:

- Business rules.
- Rental and return operations.
- Tool availability.
- Customer restrictions.
- Data validation.
- API responses.
- Error handling.
- Concurrent rental operations.
- Successful authentication.
- Rejection of invalid authentication.
- Access permitted according to the assigned role.
- Access denied when the role does not have the required permission.
- Restrictions on account creation and role assignment.
- Restrictions on tool deactivation by Advisors.
- Successful customer deactivation by Administrators.
- Rejection of customer deactivation attempts by Advisors with HTTP `403 Forbidden`.
- Preservation of customer rental and return history after logical deactivation.
- Prevention of physical customer deletion through the application.

The project must contain at least 15 test files, as established in the improvement plan.

## 12. Documentation Principles

Technical documentation must remain consistent with the implemented system.

The documentation must include:

- Project constitution.
- Functional and non-functional specifications.
- Implementation plan.
- System architecture.
- Data model.
- API contract.
- Task breakdown.
- At least three Architecture Decision Records (ADR).

Every significant architectural decision must be documented and justified.

Changes to authentication, roles, permissions, or business rules must be reflected in the corresponding technical documentation.

## 13. Version Control and Commit Standards

Git must be used to maintain the project's development history.

The following practices are mandatory:

- Work must be performed in the learner's own fork.
- Changes must be committed progressively.
- Commits must represent small and meaningful changes.
- Development evidence must be distributed across at least eight different days.
- Each project subdirectory must have at least four commits.
- Commit messages must describe the changes clearly.
- Published commit history must not be rewritten.

Sensitive files, generated dependencies, and unnecessary build artifacts must not be committed.

## 14. Compliance and Project Scope

All development activities must comply with the requirements established in the improvement plan.

The implementation must maintain traceability between:

- Business requirements.
- User stories.
- Acceptance criteria.
- Technical tasks.
- Source code.
- Automated tests.

Any change to the established scope must be documented and justified.

---

**End of Constitution**

**Project:** RENT-TOOLS  
**Document:** Constitution  
**Version:** 1.1.1