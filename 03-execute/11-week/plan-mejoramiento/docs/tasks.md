# RENT-TOOLS Implementation Tasks

**Project:** RENT-TOOLS
**Version:** 1.2.0
**Status:** Draft
**Total tasks:** 23

## 1. Purpose

This is the complete, closed task list required by the academic activity. Authentication, authorization, and security verification are integrated into applicable backend, frontend, and testing tasks; they are not extra tasks. A task is complete only when its criterion is met and its stated evidence exists. Planned verification must never be reported as executed.

## 2. Task register

| ID and name | Description | Phase | Dependencies | Acceptance criterion | Related requirements | Component | Completion evidence |
|---|---|---|---|---|---|---|---|
| TASK-01: Define project documentation | Maintain constitution, specification, plan, architecture, data model, contract, tasks, and traceability. | Documentation | None | Required Markdown is coherent and traceable. | US-01–US-10; FR-01–FR-20; NFR-01–NFR-05 | `docs` | Reviewed documents and traceability matrix. |
| TASK-02: Record architectural decisions | Maintain ADRs for architecture, persistence, concurrency, RBAC, and authentication. | Documentation | TASK-01 | At least three ADRs contain context, decision, alternatives, consequences, status, and justification. | FR-05–FR-08, FR-16; NFR-01–NFR-04 | `docs/adr` | ADR review. |
| TASK-03: Configure development environment | Define local prerequisites and safe environment variables. | Infrastructure | TASK-01, TASK-02 | Setup is reproducible and exposes no secrets. | NFR-03, NFR-04 | `infra`, `docs` | Sanitized setup guide. |
| TASK-04: Configure Docker infrastructure | Configure application services and relational database containers. | Infrastructure | TASK-03 | One documented command starts required services. | NFR-01, NFR-04 | `infra` | Docker configuration and startup record. |
| TASK-05: Create relational data model | Implement entities, foreign keys, categories, logical deletion, snapshots, and constraints. | Infrastructure | TASK-01, TASK-02 | Schema preserves history and prevents invalid references. | FR-01–FR-04, FR-15; BR-04–BR-06; NFR-01 | `api/database` | Migrations and relationship tests. |
| TASK-06: Initialize Laravel API and identity controls | Configure API foundation, Sanctum, user status, sessions, account administration, and backend role checks. | Backend API | TASK-03, TASK-05 | Active Administrator/Advisor accounts authenticate; protected routes return 401/403; tokens are revocable. | US-01, US-02; FR-05–FR-08, FR-20; NFR-03 | `api` | Routes, middleware/policies, authentication tests. |
| TASK-07: Implement category and tool management | Implement authorized category and tool catalogue operations, availability, updates, and tool deactivation. | Backend API | TASK-05, TASK-06 | Administrators manage categories and deactivate tools; Advisors receive 403 when deactivating. | US-03, US-04; FR-01, FR-02, FR-14; BR-04; NFR-01, NFR-03 | `api` | API permission and availability tests. |
| TASK-08: Implement customer management | Implement customer registration, consultation, update, logical deactivation, and reactivation without accounts. | Backend API | TASK-05, TASK-06 | Administrators deactivate/reactivate; Advisors only register, consult, and update; history remains. | US-05, US-06; FR-03, FR-15; BR-06; NFR-01, NFR-03 | `api` | API 403 and history tests. |
| TASK-09: Implement advisor management | Implement advisor records and responsible-advisor identification. | Backend API | TASK-05, TASK-06 | Administrative management is protected and operational data excludes credentials. | US-07; FR-04, FR-08; NFR-01, NFR-03 | `api` | API tests and response review. |
| TASK-10: Implement rental management | Implement rental registration and consultation with authenticated advisor identity and eligibility validations. | Backend API | TASK-07–TASK-09 | Enforces active customer, three-unit, seven-day, overdue, availability, and advisor rules. | US-08; FR-09–FR-12; BR-01–BR-04; NFR-01–NFR-03 | `api` | Valid and rejected rental tests. |
| TASK-11: Implement return management | Implement partial and complete returns, outstanding validation, status changes, and attribution. | Backend API | TASK-10 | Returns do not exceed outstanding quantities, update availability, and preserve history. | US-09; FR-13–FR-16; BR-04–BR-06; NFR-01–NFR-03 | `api` | Partial, complete, and rejected return tests. |
| TASK-12: Implement transactions and conflicts | Apply transactions, locking or equivalent control, uniform validation, and status responses. | Backend API | TASK-10, TASK-11 | Conflicting inventory requests preserve bounds and return 409; rules remain backend-enforced. | FR-16, FR-17; BR-01–BR-06; NFR-01–NFR-03 | `api` | Concurrency and HTTP assertions. |
| TASK-13: Initialize React and sessions | Configure React, protected navigation, token use, logout, and role-aware UI. | React application | TASK-06, TASK-17 | Login/logout and revoked sessions work; frontend never replaces backend authorization. | US-01, US-02; FR-05–FR-08, FR-20; NFR-03, NFR-04 | `app` | Session tests or manual verification. |
| TASK-14: Implement category and tool interfaces | Build catalogue and availability views with role-appropriate actions. | React application | TASK-07, TASK-13 | Only Administrator UI exposes category/tool deactivation controls. | US-03, US-04; FR-01, FR-02, FR-14; NFR-03, NFR-04 | `app` | API integration check. |
| TASK-15: Implement customer and advisor interfaces | Build customer/advisor views, including logical status and history. | React application | TASK-08, TASK-09, TASK-13 | No customer login; only Administrator UI exposes customer deactivation/reactivation. | US-05–US-07; FR-03, FR-04, FR-15; BR-06; NFR-03, NFR-04 | `app` | Role-based UI verification. |
| TASK-16: Implement rental and return interfaces | Build forms and views for rentals, returns, availability, restrictions, and conflicts. | React application | TASK-10–TASK-13 | Communicates API validation and supports partial returns without bypassing the API. | US-08, US-09; FR-09–FR-17; BR-01–BR-06; NFR-03, NFR-04 | `app` | UI/API integration verification. |
| TASK-17: Create public website | Create an independent static site that describes RENT-TOOLS. | Public website | TASK-01 | Public content contains no private operational data. | US-10; FR-18; NFR-03, NFR-04 | `page` | Static-site review. |
| TASK-18: Implement responsive public presentation | Apply responsive, usable public-site styling and navigation. | Public website | TASK-17 | Content and navigation work on supported desktop and mobile viewports. | US-10; FR-18; NFR-04 | `page` | Responsive verification. |
| TASK-19: Implement API seed utilities | Seed development data through documented API operations. | Utilities | TASK-06–TASK-11 | Uses valid roles/active customers, no real secrets, and no direct database insertion. | FR-17; NFR-01, NFR-03 | `tool` | Seed utility output and guide. |
| TASK-20: Document execution commands | Document setup, Docker, migration, seeding, test, authentication, and authorization commands. | Utilities | TASK-03, TASK-04, TASK-06, TASK-19 | Instructions are safe, reproducible, and do not claim unrun results. | FR-17, FR-19, FR-20; NFR-03–NFR-05 | `docs` | Operations-document review. |
| TASK-21: Implement automated backend tests | Test endpoints, six business rules, identity controls, logical deletion, history, and concurrency. | Testing | TASK-06–TASK-12 | Covers allowed/denied operations and 401/403/409 responses. | FR-01–FR-17, FR-20; BR-01–BR-06; NFR-01–NFR-03, NFR-05 | `api/tests` | Test files and command output. |
| TASK-22: Complete required test suite | Organize all automated checks and confirm at least fifteen test files. | Testing | TASK-13–TASK-18, TASK-21 | Test inventory maps to contract; results distinguish executed from planned checks. | FR-19; NFR-05 | `api/tests`, `app`, `docs` | Test inventory and report. |
| TASK-23: Perform final verification | Verify delivery against specification, contract, tasks, and deployment documentation. | Testing | TASK-01–TASK-22 | Checks startup, endpoint conformance, roles, public site, history, and documentation; reports unresolved defects. | US-01–US-10; FR-01–FR-20; BR-01–BR-06; NFR-01–NFR-05 | All components | Final verification report. |

## 3. Dependency and rule traceability

Documentation and ADRs precede technical work. Data and identity controls precede protected APIs; APIs precede their React interfaces. Testing follows implementation and final verification depends on all preceding tasks.

| Rule | Implementation tasks | Verification tasks |
|---|---|---|
| BR-01: maximum three units | TASK-10, TASK-16 | TASK-21, TASK-23 |
| BR-02: maximum seven days | TASK-10, TASK-16 | TASK-21, TASK-23 |
| BR-03: overdue restriction | TASK-10, TASK-16 | TASK-21, TASK-23 |
| BR-04: inventory bounds | TASK-05, TASK-07, TASK-11, TASK-12 | TASK-21, TASK-23 |
| BR-05: historical snapshots | TASK-05, TASK-10, TASK-11 | TASK-21, TASK-23 |
| BR-06: logical history preservation | TASK-05, TASK-07–TASK-11 | TASK-21, TASK-23 |

## 4. Scope restrictions

Tasks exclude payments, scheduling, customer authentication, and all functionality outside `spec.md`. This task list authorizes no Git commit or push.
