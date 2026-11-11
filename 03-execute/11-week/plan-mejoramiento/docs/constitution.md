# RENT-TOOLS Constitution

**Project:** RENT-TOOLS
**Version:** 1.0.0
**Status:** Approved
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
- **Database:** A relational database managed through containers.
- **Infrastructure:** Docker and container-based configuration.
- **Public website:** Static website using web technologies.
- **Testing:** Automated tests.

All components must be organized in their corresponding project directories.

## 3. Architecture and Separation of Responsibilities

The system must maintain a clear separation of responsibilities.

The project is organized into the following components:

- `docs/`: Technical documentation, specifications, architecture, and decisions.
- `api/`: Backend application, business logic, and API endpoints.
- `app/`: React frontend application.
- `page/`: Public static website.
- `infra/`: Database, Docker, and infrastructure configuration.
- `tool/`: Supporting tools, automation, and data seeding through the API.

Each component must have a defined responsibility and communicate through documented interfaces.

## 4. Business Rules Integrity

The system must enforce the following business rules:

1. A customer may have a maximum of three tools rented simultaneously without returning them.
2. Each rental has a maximum rental period of seven days.
3. Customers with overdue rentals cannot receive additional tools until the overdue tools are returned.
4. Available units must always remain between zero and the total number of units.
5. Historical information associated with a rental must remain unchanged when catalog information is modified.
6. Rental and return records must be preserved as historical records. Catalog records must use logical deletion instead of physical deletion.

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

## 6. Security Principles

The application must follow basic software security principles:

- Validate all incoming data.
- Protect access to restricted operations.
- Apply authorization according to the user's assigned role.
- Avoid exposing sensitive information.
- Store configuration and credentials outside the source code.
- Never commit `.env` files or private credentials to the repository.

## 7. Date and Time Management

All dates and timestamps stored and processed by the system must use UTC.

The application must maintain consistent date handling across the backend, database, and frontend.

User-facing messages must be displayed in Spanish.

## 8. Software Quality

All components must follow these quality principles:

- Maintain readable and organized source code.
- Use meaningful names for variables, classes, methods, and files.
- Avoid unnecessary code duplication.
- Keep responsibilities separated.
- Document important technical decisions.
- Maintain consistency between specifications and implementation.
- Avoid implementing functionality outside the established project scope without justification.

## 9. Testing Principles

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

The project must contain at least 15 test files, as established in the improvement plan.

## 10. Documentation Principles

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

## 11. Version Control and Commit Standards

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

## 12. Compliance and Project Scope

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
**Version:** 1.0.0
