# ADR-002: Relational Database Design

**Project:** RENT-TOOLS  
**Document:** Architecture Decision Record  
**Status:** Accepted  
**Date:** 2026-11-11  
**Decision Type:** Database Design  

---

## 1. Context

RENT-TOOLS manages related information about construction tools, customers, advisors, rentals, rental details, returns, and return details.

The system requires consistent relationships between these entities and must preserve historical transaction information.

Inventory operations must also maintain data integrity when multiple users perform operations simultaneously.

## 2. Decision

The project will use a relational database model.

The database will organize information into related tables using primary keys, foreign keys, and integrity constraints.

The database engine will be selected and configured during the infrastructure implementation phase.

## 3. Reasons for the Decision

A relational database model supports:

- Structured information management.
- Relationships between business entities.
- Referential integrity through foreign keys.
- Data consistency through constraints.
- Transactions for critical business operations.
- Reliable storage of rental and return history.

## 4. Data Organization

The initial data model includes the following entities:

| Entity | Responsibility |
|---|---|
| tools | Construction tool catalog and inventory |
| customers | Customer information |
| advisors | Advisor information |
| rentals | Main rental transactions |
| rental_details | Tools and quantities included in rentals |
| returns | Return operations |
| return_details | Quantities included in return operations |

The relationships and attributes are defined in `data-model.md`.

## 5. Data Integrity

The database design must support the following conditions:

- Available quantities cannot be negative.
- Available quantities cannot exceed total quantities.
- Rental detail quantities must be greater than zero.
- Returned quantities must be greater than zero.
- Returned quantities cannot exceed outstanding rented quantities.
- Historical transactions must remain preserved.
- Referenced historical records must not be physically deleted.

## 6. Transaction Management

Rental and return operations must use database transactions.

A rental operation must record its transaction details and update inventory consistently.

A return operation must record returned quantities and restore inventory consistently.

If an operation fails, its database changes must be rolled back to prevent inconsistent information.

## 7. Alternatives Considered

A non-relational data model was considered as an alternative.

A relational model was selected because RENT-TOOLS requires structured relationships, referential integrity, and transactional consistency across rental and inventory operations.

No specific database engine is selected by this ADR.

## 8. Consequences

### Positive Consequences

- Business relationships are explicitly represented.
- Referential integrity can be enforced.
- Critical operations can use database transactions.
- Historical information can be preserved consistently.
- The model supports structured queries and reporting.

### Considerations

- Database relationships must be configured correctly.
- Foreign key constraints must be maintained.
- Transaction boundaries must be carefully implemented.
- The selected database engine must support the required transactional and concurrency mechanisms.

## 9. Related Documents

- `constitution.md`
- `architecture.md`
- `data-model.md`
- `plan.md`

## 10. Decision Status

This decision is accepted for the initial implementation of RENT-TOOLS.

The specific database engine will be documented when the infrastructure technology is finalized.

---

**End of ADR-002**

**Project:** RENT-TOOLS  
**Decision:** Relational Database Design
