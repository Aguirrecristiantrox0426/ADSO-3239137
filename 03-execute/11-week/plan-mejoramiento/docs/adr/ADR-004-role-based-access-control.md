
# ADR-004: Role-Based Access Control (RBAC)

**Project:** RENT-TOOLS  
**Document:** Architecture Decision Record  
**Status:** Accepted  
**Date:** 2026-11-24  
**Decision Type:** Security and Authorization

## 1. Context

RENT-TOOLS requires an authorization mechanism to control access to system functionalities according to the responsibilities of each authenticated user.

The system must distinguish between two authenticated roles:

- Administrator.
- Advisor.

Administrators are responsible for managing user accounts, assigning roles, managing the catalog, and performing administrative operations.

Advisors are responsible for operational activities such as consulting tools, registering and updating tools, managing customers, and processing rentals and returns.

Customers are business records and do not have authenticated system accounts. They must not have login credentials or authenticated roles.

Customer records may be logically deactivated when they should no longer be used in new operations. This action is restricted to Administrators. Advisors may register, consult, and update customers, but they must not deactivate them.

Customer deactivation must preserve historical rentals and returns. Customer records must not be physically deleted through the application.

The authorization mechanism must protect backend operations independently of the frontend interface.

## 2. Decision

The project will implement Role-Based Access Control (RBAC) with two authenticated roles: `administrator` and `advisor`.

Permissions will be distributed as follows:

| Functionality | Administrator | Advisor |
|---|---|---|
| Create user accounts | Yes | No |
| Consult user accounts | Yes | No |
| Update user accounts | Yes | No |
| Deactivate user accounts | Yes | No |
| Assign or change user roles | Yes | No |
| Register tools | Yes | Yes |
| Consult tools | Yes | Yes |
| Update tools | Yes | Yes |
| Deactivate tools | Yes | No |
| Manage tool categories | Yes | No |
| Register customers | Yes | Yes |
| Consult customers | Yes | Yes |
| Update customers | Yes | Yes |
| Logically deactivate customers | Yes | No |
| Manage rentals | Yes | Yes |
| Register and consult returns | Yes | Yes |
| Consult authorized operational information | Yes | Yes |

Customer deactivation is a logical operation. Only an authenticated Administrator may execute it.

An authenticated Advisor attempting to deactivate a customer must receive HTTP `403 Forbidden`.

The operation must preserve the customer record and its historical rental and return relationships. Physical deletion of customer records through the application is not permitted.

The backend will enforce permissions through authorization middleware, policies, gates, or an equivalent Laravel authorization mechanism.

The frontend will adapt navigation and available actions according to the authenticated user's role. However, frontend restrictions will not replace backend authorization.

Unauthenticated requests to protected resources must return HTTP `401 Unauthorized`.

Authenticated users without the required permissions must receive HTTP `403 Forbidden`.

Customers will not be registered as authenticated users and will not receive system credentials.

## 3. Reasons for the Decision

The selected authorization model provides:

- Clear separation of administrative and operational responsibilities.
- Centralized permission management.
- Protection of restricted backend operations.
- Reduced risk of unauthorized account, role, and customer deactivation operations.
- Consistent authorization rules across API endpoints.
- Support for automated authorization testing.
- A simpler initial role model that matches the project requirements.
- Preservation of customer and rental history through logical deactivation rather than physical deletion.
- Separation between authenticated system users and customer business records.

## 4. Consequences

### Positive Consequences

- Administrator and Advisor permissions are clearly defined.
- Only Administrators can create accounts and assign roles.
- Advisors can perform authorized operational activities.
- Advisors cannot deactivate tools or customers, or manage user accounts.
- Only Administrators can logically deactivate customer records.
- Customer history remains available after deactivation.
- Backend authorization provides protection independently of frontend behavior.
- Automated tests can verify allowed and denied operations.
- Customers remain separate from authenticated system users.

### Limitations and Considerations

- Each protected endpoint must define its required permissions.
- Authorization rules must be maintained when new functionalities are added.
- Frontend visibility restrictions must remain consistent with backend permissions.
- Changes to user roles must be restricted to Administrators.
- Customers must remain separate from authenticated system users.
- Customer deactivation must be logical and restricted to the Administrator role.
- The application must not physically delete customer records.
- Authorization failures must return consistent HTTP status codes.
- The backend must validate permissions even when requests are sent directly to the API.

## 5. Alternatives Considered

### Single Administrator Role

A system with only one administrative role was considered.

This alternative was not selected because Advisors require access to operational functionalities without receiving administrative permissions.

### Three or More Authenticated Roles

A model with additional roles, including Customer, was considered.

This alternative was not selected because customers only require business records for rental operations and do not require authenticated access in the initial project scope.

### Frontend-Only Authorization

Restricting access by hiding interface elements was considered.

This alternative was rejected because frontend restrictions can be bypassed through direct API requests. Authorization must be enforced by the backend.

## 6. Related Documents

- `constitution.md`
- `spec.md`
- `plan.md`
- `architecture.md`
- `data-model.md`
- `api-contract.md`
- `tasks.md`
- `ADR-001-architecture-and-technology.md`
- `ADR-005-authentication-with-laravel-sanctum.md`

## 7. Decision Status

This decision is accepted for the RENT-TOOLS specification. Backend authorization tests must verify it before it is reported as implemented.

Any future modification to the role structure or permission model must be documented through a new Architecture Decision Record.

---

**Project:** RENT-TOOLS  
**Decision:** Role-Based Access Control (RBAC)
