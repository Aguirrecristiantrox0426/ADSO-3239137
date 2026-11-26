
# ADR-005: Authentication with Laravel Sanctum

**Project:** RENT-TOOLS  
**Document:** Architecture Decision Record  
**Status:** Accepted  
**Date:** 2026-11-24  
**Decision Type:** Authentication and Security

---

## 1. Context

RENT-TOOLS requires an authentication mechanism to protect access to the system's operational and administrative functionalities.

The application will support two authenticated roles:

- Administrator.
- Advisor.

Only Administrators can create user accounts and assign roles.

Customers are registered as business records and do not require login credentials or authenticated accounts.

The system requires a mechanism to validate user credentials, issue authentication tokens, protect API endpoints, and terminate authenticated sessions.

The authentication mechanism must integrate with the Laravel backend and the React frontend.

Additionally, accounts that have been logically deactivated must not be allowed to authenticate. Authentication must verify that the account is active before issuing an access token.

## 2. Decision

The project will use Laravel Sanctum to implement token-based authentication for the REST API.

The authentication process will follow these steps:

1. The user submits their credentials through the login interface.
2. The React application sends the credentials to the Laravel authentication endpoint.
3. The backend validates the credentials.
4. The backend verifies that the user account is active.
5. If the credentials are valid and the account is active, Laravel Sanctum generates an authentication token.
6. The backend returns the token and the authenticated user's relevant information, including their assigned role.
7. The frontend includes the token in subsequent protected API requests using the Bearer authentication scheme.
8. The backend validates the token before allowing access to protected resources.
9. The backend also verifies the user's authorization permissions before executing restricted operations.
10. When the user logs out, the backend revokes the current authentication token.

The API will expose the following authentication endpoints:

| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/v1/auth/login` | Authenticate a user and issue a token. |
| POST | `/api/v1/auth/logout` | Revoke the current authentication token. |
| GET | `/api/v1/auth/me` | Retrieve the authenticated user's information. |

Protected requests must include the following HTTP header:

```http
Authorization: Bearer {token}
```

The backend will validate authentication through Laravel Sanctum middleware and enforce authorization through the RBAC mechanism defined in ADR-004.

Unauthenticated requests to protected resources must return HTTP `401 Unauthorized`.

Authenticated users without the required permissions must receive HTTP `403 Forbidden`.

Only Administrators may create user accounts and assign or change roles.

Customers will not have authenticated accounts, passwords, or access tokens.

Deactivated user accounts must not be allowed to authenticate. The backend must reject login attempts from inactive accounts.

Logout must revoke the current token without requiring the deletion of the user's account or other authentication records.

## 3. Reasons for the Decision

Laravel Sanctum was selected because it provides:

- Native integration with Laravel.
- Token-based authentication for API applications.
- Support for protecting API routes.
- Token revocation capabilities.
- Integration with Laravel authentication mechanisms.
- A straightforward approach for React and Laravel communication.
- Support for automated authentication testing.
- A suitable authentication mechanism for the initial project scope.
- Separation between authentication and role-based authorization.

## 4. Consequences

### Positive Consequences

- Authentication is centralized in the Laravel backend.
- Protected API endpoints require valid authentication tokens.
- The frontend can authenticate users and include tokens in API requests.
- Logout can revoke the current token.
- The authenticated user's role can be used to determine available frontend actions.
- Authentication and authorization can be tested independently.
- Customers do not need system credentials.
- Only Administrators can create accounts and assign roles.
- Deactivated accounts cannot access protected resources through new authentication sessions.
- Authentication integrates directly with the Laravel REST API.

### Limitations and Considerations

- Authentication tokens must be handled securely.
- Protected routes must use the appropriate authentication middleware.
- Passwords must be stored using secure hashing.
- Invalid or missing tokens must be rejected.
- Deactivated accounts must not be allowed to authenticate.
- Token revocation must be performed during logout.
- The frontend must handle expired or invalid authentication sessions.
- Authentication does not replace role-based authorization.
- Sensitive authentication data must not be exposed in application logs or version control.
- The backend must validate account status before issuing tokens.
- The implementation must prevent unauthorized users from creating accounts or changing roles.
- Authentication failures must return consistent HTTP status codes.

## 5. Alternatives Considered

### Custom Token Authentication

Implementing a custom token authentication mechanism was considered.

This alternative was not selected because Laravel Sanctum already provides token management and integration with Laravel's authentication system.

### JWT Authentication

JSON Web Token (JWT) authentication was considered as an alternative.

This alternative was not selected because the project can meet its initial API authentication requirements using Laravel Sanctum without introducing an additional authentication package and token lifecycle implementation.

### Session-Only Authentication

A session-based authentication approach was considered.

The project selected Sanctum personal access tokens to establish a clear Bearer token authentication contract for the React frontend and Laravel REST API.

## 6. Related Documents

- `constitution.md`
- `spec.md`
- `plan.md`
- `architecture.md`
- `data-model.md`
- `api-contract.md`
- `tasks.md`
- `ADR-001-architecture-and-technology.md`
- `ADR-004-role-based-access-control.md`

## 7. Decision Status

This decision is accepted for the RENT-TOOLS specification. Authentication tests must verify it before it is reported as implemented.

Any future modification to the authentication mechanism or token management strategy must be documented through a new Architecture Decision Record.

---

**End of ADR-005**

**Project:** RENT-TOOLS  
**Decision:** Authentication with Laravel Sanctum
