# Feature Landscape: Class Code Generation

**Domain:** Business reference code/identifier generation systems
**Project:** WeCoza Classes Plugin - Class Code Simplification
**Researched:** 2026-01-22

## Table Stakes

Features users expect from auto-generated reference codes. Missing any of these = product feels incomplete or broken.

| Feature | Why Expected | Complexity | Notes |
|---------|--------------|------------|-------|
| **Uniqueness guarantee** | Duplicate codes cause data integrity failures, confusion, lost records | **Medium** | Must handle concurrent generation, database constraints |
| **Sequential numbering** | Audit trails, chronological tracking, gap detection for compliance | **Low** | Standard in invoicing, order systems - tax authorities require this |
| **Consistent format** | Users learn the pattern, validation becomes predictable | **Low** | Fixed length and structure (e.g., ABC1234 always 7 chars) |
| **Human-readable** | Codes must be verbally communicable, writable without confusion | **Low** | Short length (6-8 chars optimal), clear structure |
| **Automatic generation** | Manual entry introduces errors, delays, inconsistency | **Medium** | Server-side generation to access database state |
| **No gaps in sequence** | Regulatory compliance (VAT, tax audits), prevents fraud suspicion | **Medium** | Voided codes must be marked, not deleted or skipped |
| **Immediate availability** | Code appears on form submission without delay | **Low** | Generated during save operation, returned to UI |
| **Persistence** | Once assigned, code never changes (immutable identifier) | **Low** | No edit/update functionality for codes |
| **Collision detection** | System must detect and prevent duplicate generation | **Medium** | Database unique constraints, transaction handling |
| **Backward compatibility** | Existing codes remain valid when format changes | **Low** | Old format codes continue to work alongside new |

## Differentiators

Features that enhance UX beyond baseline. Not expected, but users appreciate them.

| Feature | Value Proposition | Complexity | Notes |
|---------|-------------------|------------|-------|
| **Context prefix** | Instant recognition of code category (client, type) | **Low** | 3-letter client abbreviation (AGR, REA, etc.) |
| **Memorable format** | Easy to remember, spell over phone, write on paper | **Low** | Pattern like AGR1234 vs 11-REALLL-RLN-2025-06-25-02-14 |
| **Ambiguity avoidance** | Exclude confusing characters (O/0, I/1/l) | **Low** | Use uppercase letters only, skip I, O, Q |
| **Length optimization** | Short codes (7 chars) vs long (30+ chars) improve usability | **Low** | Research shows 6-8 chars is optimal for manual entry |
| **Visual scanning** | Pattern makes codes easy to spot in lists, logs | **Low** | Fixed-width font friendly, distinct segments |
| **Sortability** | Alpha prefix + numeric suffix creates natural sort order | **Low** | Clients group together, sequential within client |
| **Pronounceability** | Can say "AGR twelve thirty-four" clearly | **Low** | Alternating letters/numbers, no complex clusters |
| **Code preview** | Show generated code before final save | **Medium** | Helps catch generation errors before commit |
| **Bulk uniqueness check** | Validate multiple codes at once during data import | **Medium** | Performance optimization for batch operations |
| **Friendly error messages** | "Code already exists" vs "Duplicate key violation" | **Low** | User-facing language, suggest next steps |

## Anti-Features

Features to explicitly NOT build. Common mistakes in reference code systems.

| Anti-Feature | Why Avoid | What to Do Instead |
|--------------|-----------|-------------------|
| **Manual code entry** | Introduces typos, duplicates, inconsistency | Auto-generate only, disable input field |
| **Editable codes** | Breaks referential integrity, audit trails | Make codes immutable after creation |
| **Client-based numbering** | Creates confusion ("Why is this ABC0500 and that ABC0012?"), complicates querying | Use global sequential counter across all clients |
| **Date/time in code** | Makes codes long, hard to communicate, ties to creation time not business process | Use sequential numbers, store creation timestamp separately |
| **Lowercase letters** | Increases O/0, I/l/1 confusion | Uppercase only for letters in code |
| **Complex delimiters** | Hyphens, underscores break verbal communication | Simple concatenation (AGR1234 not AGR-1234) |
| **Variable length** | Breaks visual scanning, sorting, validation | Fixed length with zero-padding (0001 not 1) |
| **Special characters** | Database escaping issues, URL encoding problems | Alphanumeric only (A-Z, 0-9) |
| **Cryptographic IDs** | UUID/GUID are 32+ chars, not human-friendly | Short sequential codes for user-facing IDs |
| **Per-reset numbering** | Annual/monthly resets create confusion, limit scale | Monotonic counter that never resets |
| **Semantic encoding** | Trying to encode all metadata in code (product+region+date+type) | Keep codes simple, store metadata in separate fields |
| **Gap filling** | Reusing deleted/voided numbers breaks audit trails | Mark as voided, continue sequence forward |

## Feature Dependencies

```
Code Generation Flow:
├── Uniqueness Guarantee (foundational)
│   ├── Sequential Numbering
│   ├── Collision Detection
│   └── Database Constraints
│
├── Human Readability (UX layer)
│   ├── Context Prefix (client abbreviation)
│   ├── Length Optimization (7 chars)
│   ├── Ambiguity Avoidance (no O, I)
│   └── Consistent Format (ABC####)
│
└── System Integration (persistence layer)
    ├── Automatic Generation (server-side)
    ├── Immediate Availability (AJAX response)
    ├── Backward Compatibility (old codes remain)
    └── No Gaps in Sequence (voided codes marked)
```

**Critical path for MVP:**
1. Uniqueness Guarantee → Sequential Numbering → Collision Detection
2. Context Prefix (client name extraction) → Consistent Format
3. Automatic Generation (server-side) → Immediate Availability

## Edge Cases to Handle

### Client Name Variations

| Scenario | Input | Expected Output | Handling Strategy |
|----------|-------|----------------|-------------------|
| Short name (< 3 chars) | "AB Consulting" | `AB_1234` or `ABC1234` | Pad with underscore OR use first 3 chars of next word |
| Special characters | "O'Reilly & Co." | `ORE1234` | Strip non-alphanumeric, take first 3 letters |
| All lowercase | "agrinet solutions" | `AGR1234` | Uppercase conversion |
| Numbers in name | "3M Company" | `COM1234` | Skip leading numbers, use letters |
| Unicode/accents | "Résumé Inc" | `RES1234` | Transliterate to ASCII |
| Multiple words | "Real Estate Leaders" | `REA1234` | Take first letter of first 3 words OR first 3 of first word |
| Very long name | "International Business Machines" | `INT1234` | First 3 letters only |
| Duplicate prefixes | Two clients both generate "AGR" | `AGR1234`, `AGR1235` | Sequential number differentiates |

**Recommendation for WeCoza:** Take first 3 uppercase letters from client name field, skip non-alphanumeric. If < 3 letters available, pad with 'X' (e.g., "AB" → "ABX1234").

### Sequential Number Rollover

| Scenario | Current Max | Next Code | Handling Strategy |
|----------|-------------|-----------|-------------------|
| Approaching limit | 9998 | 9999 | **Monitor and plan:** Track utilization, alert at 90% |
| At limit (4 digits) | 9999 | **Error** | **Expand to 5 digits:** AGR10000 (accept length change) |
| Database empty (first class) | None | 0001 | Start at 1, zero-pad to 4 digits |
| Reset request | 5432 | Still 5433 | **Never reset:** Monotonic increase only |

**Recommendation for WeCoza:** 4-digit counter supports 10,000 classes. Beta site unlikely to hit limit. If needed, expand to 5 digits rather than reset.

### Concurrent Generation (Race Conditions)

| Scenario | Problem | Prevention Strategy |
|----------|---------|---------------------|
| Two users create class simultaneously | Both query MAX = 1234, both generate 1235 | **Database transaction:** SELECT MAX() FOR UPDATE + INSERT in single transaction |
| High-frequency creation | Sequential queries cause bottlenecks | **Acceptable:** Class creation is low-frequency (not thousands/second) |
| Database connection failure | Code generated but not saved | **Rollback transaction:** Code never assigned if class insert fails |
| Duplicate detection timing | Code generated, then duplicate appears before insert | **Unique constraint:** Database enforces uniqueness, catch exception |

**Recommendation for WeCoza:** Use database transaction with FOR UPDATE lock. WordPress/PHP environment is single-threaded per request, limiting race window.

### Voided/Deleted Codes

| Scenario | What Happens | Expected Behavior |
|----------|--------------|-------------------|
| Class deleted (soft delete) | Code ABC1234 no longer in use | Mark class as deleted, code remains in sequence, not reused |
| Class deleted (hard delete) | Code ABC1234 removed from database | **Gap in sequence is acceptable** (audit requirement: gaps indicate deletions) |
| Class creation fails | Code ABC1235 reserved but class not saved | **Gap is acceptable** (edge case, rare occurrence) |
| Restore deleted class | Soft-deleted class restored | Original code ABC1234 comes back active |

**Recommendation for WeCoza:** Soft delete preferred (mark class as deleted). Gaps in sequence are audit-trail friendly (regulators expect this).

### Migration from Old Format

| Scenario | Current Code | New Code | Handling Strategy |
|----------|--------------|----------|-------------------|
| Existing classes | `11-REALLL-RLN-2025-06-25-02-14` | Keep as-is | **Backward compatibility:** Old codes remain unchanged |
| New classes | N/A | `AGR1234` | Use new format only for new creations |
| Code validation | Must accept both formats | Regex: `^[A-Z]{3}\d{4}$` OR `^\d+-\w+-\w+-[\d-]+$` | Two validation patterns |
| Display/sorting | Mixed formats in lists | Sort by creation date, not code | **Recommendation:** Add "Code Format" indicator to UI |
| Search functionality | Users search for old format | Match exact string, case-insensitive | No special parsing needed |

**Recommendation for WeCoza:** Coexistence strategy. No migration. Old codes valid indefinitely. New format only for post-deployment creations.

### Database State Issues

| Scenario | Problem | Prevention Strategy |
|----------|---------|---------------------|
| MAX query returns NULL | No existing classes with new format | **Default to 0:** Start sequence at 1 |
| MAX query includes old format | Old codes don't match numeric pattern | **Filter query:** WHERE class_code ~ '^[A-Z]{3}\d{4}$' |
| Corrupted code in database | Someone manually edited to "TEST123" | **Validation on read:** Skip invalid codes in MAX query |
| Mixed client prefixes | ABC1234, XYZ1235, ABC1236 - what's next? | **Global counter:** MAX across all prefixes = 1236, next = 1237 |

**Recommendation for WeCoza:** Query MAX(CAST(SUBSTRING(class_code, 4, 4) AS INTEGER)) WHERE class_code ~ '^[A-Z]{3}\d{4}$' to extract numeric portion safely.

## MVP Recommendation

For MVP, prioritize these features in order:

### Phase 1: Core Generation (Week 1)
1. **Automatic server-side generation** - Move from JavaScript to PHP
2. **Sequential numbering** - Query MAX(), increment, zero-pad
3. **Context prefix** - Extract first 3 letters from client name
4. **Consistent format** - ABC#### with validation
5. **Uniqueness guarantee** - Database unique constraint + try/catch

### Phase 2: Edge Case Handling (Week 2)
1. **Client name variations** - Handle special chars, short names, unicode
2. **Collision detection** - Transaction with FOR UPDATE lock
3. **Voided codes** - Soft delete strategy, gaps allowed
4. **Backward compatibility** - Coexist with old format codes

### Phase 3: User Experience (Week 3)
1. **Friendly error messages** - "Code already exists, please retry"
2. **Code preview** - Show generated code before save (optional)
3. **Visual indicators** - Display format type (old vs new) in lists

## Defer to Post-MVP

Features to explicitly postpone:

- **Ambiguity avoidance** (I/O/Q exclusion) - Low priority, uppercase already helps
- **Code format customization** - Out of scope per PROJECT.md
- **Bulk code generation** - Not needed for single-class-at-a-time workflow
- **Rollover expansion to 5 digits** - Address when approaching 9000 classes
- **Manual override** - Explicitly out of scope per PROJECT.md
- **Per-client numbering** - User explicitly rejected this
- **Annual reset** - User explicitly wants global monotonic counter

## Source Attribution

**HIGH Confidence (verified with authoritative sources):**
- Sequential numbering for audit compliance
- No gaps requirement for tax authorities (EU VAT, HMRC)
- Uniqueness as foundational requirement
- Confusing character pairs (O/0, I/1/l)
- Soft delete vs hard delete patterns

**MEDIUM Confidence (multiple credible sources agree):**
- 6-8 character optimal length for manual entry
- Immutability of codes after assignment
- Server-side generation for database-dependent sequences
- Race condition prevention with database locks

**LOW Confidence (single source or inferred):**
- Exact character confusion percentages (50% for l/1, O/0, Z/2, 1/7)
- Pronounceability patterns for reference codes
- Specific rollover strategies at 10K limit

## Verification Notes

**Cross-verified findings:**
- Invoice numbering systems require sequential, no-gap patterns (verified across 5+ sources)
- Tax audit requirements for invoice sequences confirmed (UK HMRC, EU regulations, general accounting standards)
- Character confusion research (Bell Labs study, medical ID research, VIN standards)
- Race condition prevention patterns (database locking, atomic operations)

**Gaps in research:**
- Limited 2026-specific innovations in reference code generation (field is mature, practices stable)
- No definitive UX research on 7-char vs 8-char codes for verbal communication (inferred from password/ID research)
- WordPress-specific code generation patterns (research focused on general business systems)

**Assumptions requiring validation:**
- WeCoza's class creation volume is low-frequency (< 10/minute) - makes simple MAX() query acceptable
- PostgreSQL handles FOR UPDATE locking correctly - should verify in testing
- Client name extraction logic handles 95%+ of real client names - may need iteration

## Sources

### Sequential Numbering & Audit Requirements
- [Invoice Number Guide - Hello Bonsai](https://www.hellobonsai.com/blog/what-is-invoice-number)
- [How to Number Invoices - Invoice Simple](https://www.invoicesimple.com/blog/invoice-number)
- [Sequential Invoice Numbering - Fonoa](https://www.fonoa.com/blog/what-is-sequential-invoice-numbering)
- [Invoice Numbering Best Practices - Quick Bill Maker](https://www.quickbillmaker.com/blog/invoice-numbering-guide)
- [Reference Numbers in Accounting - Bizmanualz](https://www.bizmanualz.com/improve-accounting/reference-numbers-accounting.html)

### Unique ID Generation & Collision Detection
- [Distributed Global Unique ID Generation - Medium](https://medium.com/@sandeep4.verma/system-design-distributed-global-unique-id-generation-d6a440cc8e5)
- [UUID Collision Probability - ITU Online](https://www.ituonline.com/tech-definitions/what-is-uuid-collision/)
- [Comparing UUID, CUID, and Nanoid - DEV Community](https://dev.to/turck/comparing-uuid-cuid-and-nanoid-a-developers-guide-50c)
- [Design a Unique ID Generator - System Design Handbook](https://www.systemdesignhandbook.com/guides/design-a-unique-id-generator-in-distributed-systems/)

### Human-Readable Identifiers
- [Human-Readable IDs - GitHub](https://github.com/unleashlive/human-readable-ids)
- [How to Generate Human-Friendly Identifiers - Connect2id](https://connect2id.com/blog/how-to-generate-human-friendly-identifiers)
- [Proquints: Pronounceable Identifiers - arXiv](https://arxiv.org/html/0901.4016)

### Character Ambiguity
- [Avoiding Confusion With Alphanumeric Characters - PMC](https://pmc.ncbi.nlm.nih.gov/articles/PMC3541865/)
- [Understanding Visually Ambiguous Characters in IDs - Gajus](https://gajus.com/blog/avoiding-visually-ambiguous-characters-in-ids)
- [Misidentification of Alphanumeric Symbols - PMC](https://pmc.ncbi.nlm.nih.gov/articles/PMC5614409/)
- [Confusing Alphanumeric Characters - Sheepdog Guides](https://sheepdogguides.com/dt4v.htm)

### Race Conditions & Concurrency
- [Race Condition: A Comprehensive Guide - Shadecoder](https://www.shadecoder.com/topics/race-condition-a-comprehensive-guide-for-2025)
- [Race Conditions: The Silent Threat - Medium](https://medium.com/@arunseetharaman/race-conditions-the-silent-threat-in-concurrent-systems-11c440bd115d)
- [What is a Race Condition? - Snyk Learn](https://learn.snyk.io/lesson/race-condition/)
- [Race Condition Vulnerability - Imperva](https://www.imperva.com/learn/application-security/race-condition/)

### Soft Delete Patterns
- [To Delete or to Soft Delete - Jmix](https://www.jmix.io/blog/to-delete-or-to-soft-delete-that-is-the-question/)
- [Hard Delete vs Soft Delete - Medium](https://medium.com/@AlexanderObregon/hard-delete-vs-soft-delete-logic-in-spring-boot-services-747798a601f9)
- [Implementing Soft Delete with EF Core - Milan Jovanovic](https://www.milanjovanovic.tech/blog/implementing-soft-delete-with-ef-core)

### Prefix & Naming Patterns
- [Prefix and Suffix for Extensions - Microsoft Learn](https://learn.microsoft.com/en-us/dynamics365/business-central/dev-itpro/compliance/apptest-prefix-suffix)
- [What is a Company Prefix? - GS1 US](https://www.gs1us.org/upcs-barcodes-prefixes/what-is-a-prefix)
- [Special Characters Handling in Database - Guidance Share](https://www.guidanceshare.com/wiki/How_to_Handle_Special_Characters_with_Dynamic_SQL)
