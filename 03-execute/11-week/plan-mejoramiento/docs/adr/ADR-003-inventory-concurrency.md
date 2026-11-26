# ADR-003: Inventory Concurrency and Transaction Management

**Project:** RENT-TOOLS  
**Document:** Architecture Decision Record  
**Status:** Accepted  
**Date:** 2026-11-11  
**Decision Type:** Data Integrity and Concurrency  

---

## 1. Context

RENT-TOOLS allows multiple advisors to register tool rentals.

Two or more advisors may attempt to rent the same tool at the same time.

If only one unit is available, concurrent requests could cause the system to assign the same physical inventory unit more than once.

The system must preserve inventory consistency and prevent available quantities from becoming negative.

## 2. Decision

All critical rental and return operations must execute within database transactions.

The backend must implement database locking or an equivalent concurrency control mechanism to protect inventory updates.

The implementation must ensure that inventory availability is validated and updated atomically.

## 3. Rental Operation

When a rental request is received, the backend must:

1. Start a database transaction.
2. Lock the relevant inventory records or use an equivalent concurrency mechanism.
3. Validate the requested quantities against current availability.
4. Validate customer rental restrictions.
5. Register the rental and its details.
6. Decrease available inventory quantities.
7. Commit the transaction if all operations succeed.
8. Roll back the transaction if any operation fails.

## 4. Return Operation

When a return request is received, the backend must:

1. Start a database transaction.
2. Validate the rental and its details.
3. Lock the relevant inventory records or use an equivalent concurrency mechanism.
4. Validate outstanding quantities.
5. Register the return and its details.
6. Increase available inventory quantities.
7. Commit the transaction if all operations succeed.
8. Roll back the transaction if any operation fails.

## 5. Concurrent Rental Requests

When two advisors attempt to rent the last available unit:

- Only one transaction may successfully complete.
- The other transaction must detect that the requested quantity is no longer available.
- The unsuccessful request must receive HTTP `409 Conflict`.
- Inventory quantities must remain consistent.

The backend must not rely only on frontend validation to prevent concurrent inventory conflicts.

## 6. Inventory Integrity

The following condition must always remain true:

`0 <= available_quantity <= total_quantity`

All inventory modifications must be performed through validated backend operations.

Failed transactions must not leave partial inventory updates or incomplete rental records.

## 7. Alternatives Considered

Application-level validation without database concurrency control was considered.

This alternative was rejected because two simultaneous requests could both validate the same available quantity before either request updates the database.

Database transactions combined with record locking or an equivalent concurrency mechanism were selected to protect critical operations.

## 8. Consequences

### Positive Consequences

- Prevents simultaneous requests from assigning the same available unit.
- Maintains inventory consistency.
- Protects rental and return operations from partial updates.
- Supports reliable error handling for inventory conflicts.

### Considerations

- Database locking must be implemented carefully.
- Transactions should remain as short as possible.
- The selected database engine must support the required transaction and locking mechanisms.
- Concurrent operations must be tested.

## 9. Related Documents

- `constitution.md`
- `architecture.md`
- `data-model.md`
- `api-contract.md`
- `tasks.md`

## 10. Decision Status

This decision is accepted for the initial implementation of RENT-TOOLS.

The specific locking mechanism will be defined during backend implementation according to the selected database engine.

---

**End of ADR-003**

**Project:** RENT-TOOLS  
**Decision:** Inventory Concurrency and Transaction Management
