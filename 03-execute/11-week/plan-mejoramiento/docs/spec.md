# RENT-TOOLS Software Requirements Specification

**Project:** RENT-TOOLS  
**Document:** Software Requirements Specification  
**Version:** 1.0.0  
**Status:** Draft  
**Domain:** Construction Equipment Rental Management

---

## 1. Project Overview

RENT-TOOLS is a software system designed to manage the rental of construction tools and equipment.

The system will support the main operational processes of a construction equipment rental business, including tool management, customer management, advisor management, rentals, returns, and availability control.

The solution will consist of a web application, a backend API, a relational database, infrastructure configuration, automated tests, and a public static website.

## 2. Problem Statement

Managing construction equipment rentals requires reliable control over inventory, customers, rental periods, returns, and equipment availability.

Without a centralized system, rental operations may experience problems such as inaccurate inventory information, duplicate rentals, insufficient historical records, and difficulties identifying overdue rentals.

RENT-TOOLS aims to organize these processes through a centralized application that enforces business rules and preserves the integrity of operational information.

## 3. Project Objectives

### 3.1 General Objective

Develop a web-based system to manage construction tool rentals, ensuring accurate inventory control, customer management, rental tracking, and return management.

### 3.2 Specific Objectives

- Manage construction tools and their inventory information.
- Register and manage customer information.
- Manage advisors responsible for rental operations.
- Register and control rental transactions.
- Record tool returns and rental history.
- Calculate and control tool availability.
- Enforce rental restrictions and business rules.
- Provide a public website with information about the system.
- Verify system behavior through automated tests.

## 4. Project Scope

### 4.1 Included Features

The project includes the following functional areas:

1. **Tool Management:** Register, consult, update, and logically deactivate construction tools.
2. **Customer Management:** Register and manage customer information.
3. **Advisor Management:** Manage the advisors responsible for rental operations.
4. **Rental Management:** Register rentals and validate customer and inventory restrictions.
5. **Return Management:** Record returns and update tool availability.
6. **Availability Management:** Maintain accurate information about total and available units.
7. **Historical Records:** Preserve rental and return information.
8. **Public Website:** Provide a static website presenting the system.
9. **Automated Testing:** Verify business rules, API behavior, and operational processes.

### 4.2 Excluded Features

The implementation must remain within the scope established by the improvement plan.

Features not explicitly included in the approved requirements must not be considered part of the initial implementation.

## 5. User Roles

The system identifies the following operational roles:

### 5.1 Customer

The customer is the person who requests construction tools for rental.

The system must maintain customer information and enforce restrictions associated with active and overdue rentals.

### 5.2 Advisor

The advisor is responsible for managing rental and return operations.

The advisor interacts with the system to register transactions and consult relevant information.

### 5.3 System Administrator

The administrator is responsible for managing system information and maintaining the application's operational configuration.

Access to restricted operations must be controlled according to assigned permissions.

## 6. Functional Requirements

### FR-01: Tool Management

The system shall allow authorized users to register, consult, and update construction tools.

### FR-02: Logical Deactivation of Tools

The system shall allow catalog records to be logically deactivated without physically deleting historical information.

### FR-03: Customer Management

The system shall allow authorized users to register, consult, and update customer information.

### FR-04: Advisor Management

The system shall allow the management of advisors responsible for rental operations.

### FR-05: Rental Registration

The system shall allow an advisor to register a rental after validating customer restrictions and tool availability.

### FR-06: Maximum Rental Limit

The system shall prevent a customer from having more than three tools rented simultaneously without returning them.

### FR-07: Rental Period Validation

The system shall enforce a maximum rental period of seven days.

### FR-08: Overdue Rental Restriction

The system shall prevent customers with overdue rentals from receiving additional tools until the overdue tools have been returned.

### FR-09: Return Registration

The system shall allow authorized users to register tool returns and update the corresponding availability information.

### FR-10: Inventory Availability

The system shall maintain the total and available units of each tool, ensuring that available units never become negative or exceed the total number of units.

### FR-11: Historical Information

The system shall preserve rental and return records and the relevant information associated with each transaction.

### FR-12: Concurrency Control

The system shall prevent simultaneous rental operations from assigning the same last available unit to multiple customers.

When a conflicting operation occurs, the system shall return HTTP 409 Conflict.

### FR-13: API Communication

The backend shall expose documented API endpoints to support communication with the frontend and supporting tools.

### FR-14: Public Website

The project shall provide a static public website presenting the RENT-TOOLS system.

### FR-15: Automated Verification

The project shall include automated tests to verify functional requirements, business rules, data integrity, and API responses.

## 7. Business Rules

The system must enforce the following business rules:

| ID | Business Rule |
|---|---|
| BR-01 | A customer may have a maximum of three tools rented simultaneously without returning them. |
| BR-02 | Each rental has a maximum duration of seven days. |
| BR-03 | Customers with overdue rentals cannot receive additional tools until the overdue tools are returned. |
| BR-04 | Available units must always remain between zero and the total number of units. |
| BR-05 | Historical information associated with a rental must not change when catalog information is modified. |
| BR-06 | Rental and return records must be preserved. Catalog records must use logical deletion. |

## 8. Non-Functional Requirements

### NFR-01: Data Integrity

The system must preserve the consistency of inventory, rental, return, and historical information.

### NFR-02: Concurrency

The system must protect rental operations against simultaneous requests that could assign the same available unit more than once.

### NFR-03: Security

The system must validate incoming information, restrict protected operations, and avoid exposing sensitive credentials.

### NFR-04: Maintainability

The source code must be organized into components with clearly defined responsibilities and documented interfaces.

### NFR-05: Testability

The application must support automated testing. The project must contain at least fifteen test files, according to the improvement plan.

## 9. Technical Constraints

The implementation must use the technologies established in the improvement plan:

| Component | Technology |
|---|---|
| Frontend | React |
| Backend | PHP with Laravel |
| Database | Relational database |
| Infrastructure | Docker |
| Public Website | Static web technologies |
| Verification | Automated tests |

The application must use container-based infrastructure configuration.

## 10. Date and Time Requirements

- Dates and timestamps must be stored and processed using UTC.
- Date handling must remain consistent across the backend, database, and frontend.
- User-facing messages must be displayed in Spanish.

## 11. Data and Historical Requirements

The system must preserve the information required to reconstruct rental and return operations.

Modifying a tool in the catalog must not modify historical information associated with previously registered rentals.

Rental and return records must remain available as historical records.

Catalog deactivation must use logical deletion.

## 12. Acceptance Criteria

The system will be considered compliant with this specification when:

- Authorized users can manage tools, customers, and advisors.
- Rental operations enforce the maximum of three tools per customer.
- Rental operations enforce the seven-day maximum period.
- Customers with overdue rentals cannot receive additional tools.
- Inventory availability remains within valid limits.
- Returns update the corresponding availability information.
- Historical rental information remains unchanged after catalog modifications.
- Concurrent requests cannot rent the same last available unit.
- Conflicting rental operations return HTTP 409 Conflict.
- The public static website is available.
- Automated tests verify the defined business rules and system behavior.
- The project includes at least fifteen test files.

## 13. Requirements Traceability

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

Changes to requirements must be documented and justified.

---

**End of Document**

**Project:** RENT-TOOLS  
**Document:** Software Requirements Specification  
**Version:** 1.0.0
