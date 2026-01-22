# Requirements: WeCoza Classes Plugin - Class Code Simplification

**Defined:** 2026-01-22
**Core Value:** Class codes must be simple, memorable, and easy to communicate between users

## v1 Requirements

Requirements for initial release. Each maps to roadmap phases.

### Database Foundation

- [ ] **DB-01**: Create PostgreSQL sequence for sequential numbering (class_code_seq)
- [ ] **DB-02**: Add unique constraint on class_code field to prevent duplicates
- [ ] **DB-03**: Add database index on class_code field for query performance
- [ ] **DB-04**: Implement transaction-safe code generation (SELECT FOR UPDATE or sequence)

### Code Generation

- [ ] **GEN-01**: Implement server-side generateClassCode() method in ClassController
- [ ] **GEN-02**: Extract first 3 uppercase letters from client name for prefix
- [ ] **GEN-03**: Use mb_substr() for UTF-8 safe string extraction
- [ ] **GEN-04**: Query PostgreSQL sequence (nextval) for sequential number
- [ ] **GEN-05**: Format code as [ABC][0001] - 3 letters + 4-digit padded number
- [ ] **GEN-06**: Generate code only on class creation (never on update)
- [ ] **GEN-07**: Integrate code generation into saveClassAjax endpoint before database INSERT

### JavaScript Migration

- [ ] **JS-01**: Remove generateClassCode() function from class-types.js (lines 237-250)
- [ ] **JS-02**: Remove regenerateClassCode() calls from event listeners
- [ ] **JS-03**: Make class_code input field read-only with visual indicator
- [ ] **JS-04**: Display server-generated code in success message after class creation

### Backward Compatibility

- [ ] **BC-01**: Existing classes retain their current codes unchanged
- [ ] **BC-02**: Code generation logic only applies to new class creation
- [ ] **BC-03**: Update flow does not regenerate codes for existing classes

## v2 Requirements

Deferred to future release. Tracked but not in current roadmap.

### Edge Case Handling

- **EDGE-01**: Handle client names shorter than 3 characters (padding strategy)
- **EDGE-02**: Handle duplicate client abbreviations (collision detection)
- **EDGE-03**: Handle client names with only special characters or numbers
- **EDGE-04**: Handle emoji or complex Unicode in client names

### Monitoring & Observability

- **MON-01**: Log all code generation events for troubleshooting
- **MON-02**: Add error logging for sequence failures
- **MON-03**: Monitor code generation performance metrics
- **MON-04**: Track fallback usage if sequence unavailable

## Out of Scope

Explicitly excluded. Documented to prevent scope creep.

| Feature | Reason |
|---------|--------|
| Migrate existing class codes to new format | Beta site - avoid data migration risk and complexity |
| Per-client sequential numbering (AGR0001-AGR9999) | User explicitly requested global sequential counter |
| Manual code entry or override | Auto-generation only maintains consistency and uniqueness |
| Customizable code format | Fixed format ensures consistency across all users |
| Code regeneration on class update | Immutability after assignment is critical for audit trail |
| Handling all possible Unicode edge cases | Deferred to v2 - focus on common Latin characters in v1 |

## Traceability

Which phases cover which requirements. Updated during roadmap creation.

| Requirement | Phase | Status |
|-------------|-------|--------|
| DB-01 | Pending | Pending |
| DB-02 | Pending | Pending |
| DB-03 | Pending | Pending |
| DB-04 | Pending | Pending |
| GEN-01 | Pending | Pending |
| GEN-02 | Pending | Pending |
| GEN-03 | Pending | Pending |
| GEN-04 | Pending | Pending |
| GEN-05 | Pending | Pending |
| GEN-06 | Pending | Pending |
| GEN-07 | Pending | Pending |
| JS-01 | Pending | Pending |
| JS-02 | Pending | Pending |
| JS-03 | Pending | Pending |
| JS-04 | Pending | Pending |
| BC-01 | Pending | Pending |
| BC-02 | Pending | Pending |
| BC-03 | Pending | Pending |

**Coverage:**
- v1 requirements: 18 total
- Mapped to phases: 0 (pending roadmap creation)
- Unmapped: 18 ⚠️

---
*Requirements defined: 2026-01-22*
*Last updated: 2026-01-22 after initial definition*
