# Project Research Summary

**Project:** Sequential Class Code Generation - WordPress/PostgreSQL Plugin Enhancement
**Domain:** Server-side sequential ID generation with prefix extraction
**Researched:** 2026-01-22
**Confidence:** HIGH

## Executive Summary

This project adds server-side sequential class code generation to an existing WordPress plugin with external PostgreSQL database. The system needs to replace client-side timestamp-based codes (e.g., "11-REALLL-RLN-2025-06-25-02-14") with simple sequential codes (e.g., "AGR1234") while maintaining backward compatibility with existing data. The recommended approach uses PostgreSQL sequences with RETURNING clause for atomic operations, AJAX endpoints for server-side generation, and native PHP string functions for client name abbreviation.

The research reveals this is a well-understood problem domain with established patterns. The critical success factor is preventing race conditions during concurrent code generation - two users creating classes simultaneously must never receive duplicate codes. PostgreSQL sequences provide the most reliable solution, offering atomic `nextval()` operations that are far superior to MAX()+1 queries which suffer from race conditions and performance degradation at scale. The architecture leverages existing plugin infrastructure (AJAX endpoints, DatabaseService, ClassController) requiring minimal new code.

Key risks center on edge cases in client name processing (multibyte characters, short names, special characters) and maintaining data integrity during the migration from client-side to server-side generation. The business accepts gaps in sequence numbers (no gapless requirement), which significantly simplifies implementation and improves performance. The beta site has existing classes with old format codes that must remain unchanged during updates - strict separation between create and update operations is essential.

## Key Findings

### Recommended Stack

The implementation requires no new dependencies - all necessary infrastructure exists in the current WordPress/PostgreSQL stack. PostgreSQL sequences provide the foundation for race-condition-free sequential numbering, with native database support for atomic `nextval()` operations. The existing DatabaseService singleton already handles PDO connections and transactions, requiring only minor enhancement for sequence queries.

**Core technologies:**
- **PostgreSQL Sequences**: Native atomic counter generation - eliminates race conditions, handles concurrency better than MAX() queries, accepts gaps in numbering for performance
- **PDO with RETURNING clause**: Single-query insert with ID retrieval - prevents lastInsertId() race conditions with sequences, guarantees correct row ownership in concurrent scenarios
- **WordPress AJAX (admin-ajax.php)**: Code generation endpoint - already used throughout plugin (15+ endpoints), proven pattern with nonce security, no new authentication needed
- **Native PHP String Functions**: Client name abbreviation - zero dependencies, sufficient for English client names, mb_substr() polyfill if Unicode support needed

**Critical version compatibility:**
- PHP 8.1.2 supports PDO PostgreSQL driver with sequences
- PostgreSQL 11+ provides sequences, RETURNING clause, advisory locks
- WordPress 5.0+ AJAX patterns align with existing plugin architecture
- mb_ucfirst requires PHP 8.4+ (not available), use mb_substr + mb_strtoupper polyfill

### Expected Features

Research identified clear table stakes vs differentiators for reference code generation systems. The business requirement is "simple codes" not "strictly ordered codes" - accepting gaps in sequences dramatically simplifies implementation.

**Must have (table stakes):**
- Uniqueness guarantee - duplicate codes cause data integrity failures, database constraints required
- Sequential numbering - audit trails and chronological tracking, tax authorities expect this
- Consistent format - fixed length ABC#### pattern (7 chars total), predictable validation
- Human-readable - verbally communicable codes, short length (6-8 chars optimal)
- Automatic generation - server-side only to prevent client tampering and race conditions
- Immediate availability - code appears on form submission without delay
- Persistence - once assigned, codes never change (immutable identifiers)
- Backward compatibility - existing timestamp-based codes remain valid

**Should have (competitive):**
- Context prefix - 3-letter client abbreviation for instant recognition (AGR, REA, etc.)
- Memorable format - AGR1234 vs 11-REALLL-RLN-2025-06-25-02-14 improves usability
- Length optimization - 7-character codes easier to communicate than 30+ character codes
- Sortability - alpha prefix + numeric suffix creates natural sort order
- Friendly error messages - user-facing language when duplicates detected

**Defer (v2+):**
- Ambiguity avoidance (I/O/Q exclusion) - low priority, uppercase already helps
- Code format customization - out of scope per PROJECT.md
- Bulk code generation - not needed for single-class-at-a-time workflow
- Per-client numbering - user explicitly rejected this in favor of global counter
- Annual reset - user wants monotonic counter that never resets
- Manual override capability - explicitly out of scope

### Architecture Approach

The existing WordPress plugin uses clean MVC architecture with external PostgreSQL database. Code generation integrates into the existing ClassController::saveClassAjax() method after form data processing and before database persistence. The pattern follows server-side generation with database transactions to ensure atomicity.

**Major components:**
1. **ClassController** - Add generateClassCode() method, integrate into saveClassAjax() after line 695 (processFormData), enforce create-only generation (never regenerate on update)
2. **DatabaseService** - Optional enhancement for getNextSequential() helper method, already provides transaction support with beginTransaction()/commit()/rollback()
3. **PostgreSQL Database** - Create new sequence class_code_seq, add unique constraint on class_code column, add index for performance
4. **JavaScript (class-types.js)** - Remove client-side generateClassCode() function (lines 237-250), convert to display-only mode for server-returned codes
5. **class-capture.js** - Update AJAX success handler to display server-generated code, make input read-only after generation

**Integration pattern:**
```
User submits form → WordPress AJAX → ClassController::saveClassAjax()
  → generateClassCode(client_id, client_name)
    → Extract prefix from client_name (mb_substr, 3 letters)
    → Query nextval('class_code_seq')
    → Format: PREFIX + zero-padded number (AGR0001)
  → setClassCode(generated_code)
  → save() with RETURNING clause
  → Return JSON with class_code to client
```

**Transaction safety:**
- Wrap SELECT + INSERT in transaction with SELECT FOR UPDATE
- Use RETURNING clause in INSERT to get class_id atomically
- Catch unique constraint violations, retry with next number if collision occurs

### Critical Pitfalls

Research identified several critical mistakes that must be avoided. The top pitfalls center on race conditions, character encoding, and data integrity during migration.

1. **Race Condition with MAX()+1 Pattern** - Two concurrent requests both query MAX(class_code), get same result, insert duplicate sequential numbers. MVCC in PostgreSQL means each transaction sees snapshot at transaction start. Prevention: Use PostgreSQL SEQUENCE with nextval() (atomic, transaction-safe) OR SELECT FOR UPDATE table lock OR advisory locks. Sequences recommended - eliminates race conditions entirely without locking overhead.

2. **Multibyte Character Substring Corruption** - Using substr() to extract 3 characters from UTF-8 client names cuts multibyte characters in half, producing garbled abbreviations. Names with accents (café), emoji (🏢), Cyrillic break substring operations. Prevention: Use mb_substr($name, 0, 3, 'UTF-8') and mb_strtoupper() for all string operations. Validate encoding with mb_check_encoding(). Test with diverse Unicode: emoji, accents, Cyrillic, Chinese.

3. **Client Names Shorter Than 3 Letters** - Names like "AB Ltd" or "X Corp" produce 2-character or 1-character prefixes, breaking fixed format (XXX####). Prevention: Extract letters only with preg_match_all('/[a-zA-Z]/u'), pad with 'X' using str_pad() to ensure 3 characters (e.g., "AB" → "ABX1234"). Alternative: Use generic "CLI" prefix for short names. Future: Validate client names contain 3+ letters during client creation.

4. **Breaking Existing Classes During Migration** - Update operations inadvertently regenerate codes for existing classes, corrupting historical data on live beta site. Prevention: Strict separation of create vs update - only generate codes when empty($_POST['class_id']). Never call generateClassCode() for existing classes. Optional database trigger to prevent class_code updates. Comprehensive testing of all update paths.

5. **MAX() Performance Degradation at Scale** - Querying MAX(class_code) on every creation becomes slow as table grows (1000+ rows). At 10K rows, MAX() queries take 100-300ms, causing user-facing slowdown. Prevention: Add index on class_code column (CREATE INDEX idx_classes_class_code). Consider pattern prefix index (text_pattern_ops) for faster LIKE queries. Long-term: Migrate to SEQUENCE eliminates MAX() entirely.

## Implications for Roadmap

Based on research, the implementation naturally divides into 4 phases with clear dependencies and risk mitigation.

### Phase 1: Database Foundation
**Rationale:** Database schema changes must come first - sequences and constraints are prerequisites for code generation logic.
**Delivers:** PostgreSQL sequence, unique constraint, performance indexes
**Addresses:** Uniqueness guarantee (table stakes), sequential numbering (table stakes)
**Avoids:** Race conditions (Pitfall 1), performance issues (Pitfall 5)

**Tasks:**
- Create class_code_seq sequence (START WITH 1)
- Add unique constraint on class_code column
- Add index on class_code for MAX() query performance
- Optional: Add pattern prefix index (text_pattern_ops)
- Test sequence behavior with concurrent connections

### Phase 2: Server-Side Code Generation
**Rationale:** Core generation logic builds on database foundation, must handle all edge cases before exposing to users.
**Delivers:** generateClassCode() method with prefix extraction, sequence integration, format validation
**Uses:** PostgreSQL sequences (from STACK.md), native PHP string functions (from STACK.md)
**Addresses:** Automatic generation (table stakes), context prefix (differentiator), consistent format (table stakes)
**Avoids:** Multibyte corruption (Pitfall 2), short names (Pitfall 3)

**Tasks:**
- Implement extractPrefix() with mb_substr(), handle special characters, pad short names
- Implement generateClassCode() using nextval('class_code_seq')
- Add format validation (regex: ^[A-Z]{3}[0-9]{4}$)
- Add comprehensive logging for troubleshooting
- Unit tests with edge cases: short names, Unicode, special characters

### Phase 3: Controller Integration
**Rationale:** Integration into existing AJAX workflow requires careful separation of create vs update to protect existing data.
**Delivers:** Modified saveClassAjax() with code generation, update protection, error handling
**Implements:** ClassController modifications (from ARCHITECTURE.md)
**Addresses:** Immediate availability (table stakes), backward compatibility (table stakes)
**Avoids:** Breaking existing classes (Pitfall 4), missing update/create separation

**Tasks:**
- Integrate generateClassCode() into ClassController::saveClassAjax() after line 695
- Enforce create-only generation (check empty($_POST['class_id']))
- Strip class_code from POST data for security
- Add try/catch for unique constraint violations with retry logic
- Add duplicate detection recovery (moderate pitfall 6)
- Test all update paths don't trigger generation

### Phase 4: Frontend Integration & Testing
**Rationale:** Frontend changes are final step, enable comprehensive end-to-end testing with real UI.
**Delivers:** Updated JavaScript, read-only display, comprehensive testing
**Addresses:** Visual scanning (differentiator), friendly error messages (differentiator)
**Avoids:** Client-side generation anti-patterns, case sensitivity issues (minor pitfall 11)

**Tasks:**
- Remove generateClassCode() from class-types.js (lines 237-250)
- Update class-capture.js success handler to display server code
- Make class_code input read-only after generation
- Test concurrent creation (multiple browser tabs)
- Test with UTF-8 client names (emoji, accents, Cyrillic)
- Test backward compatibility with old format codes
- Performance testing under load (10+ concurrent requests)

### Phase Ordering Rationale

- **Database First**: Sequences and constraints are foundational - code generation depends on these primitives. Cannot test generation without sequence in place.
- **Server Logic Before Frontend**: Core generation logic must be bulletproof before exposing to UI. Unit testing server-side is easier than debugging through AJAX calls.
- **Integration Before Testing**: Controller integration enables end-to-end testing. Trying to test without full integration misses race conditions and transaction issues.
- **Sequential Dependencies**: Each phase builds on previous - cannot skip or reorder without breaking dependencies.

### Research Flags

Phases likely needing deeper research during planning:
- **None identified** - This is a well-documented problem domain with established patterns. All phases have clear implementation paths based on research.

Phases with standard patterns (skip research-phase):
- **Phase 1**: Standard PostgreSQL sequence creation, well-documented in official docs
- **Phase 2**: Standard PHP string manipulation patterns, multibyte handling documented
- **Phase 3**: Existing ClassController pattern, similar to current saveClassAjax() implementation
- **Phase 4**: Standard WordPress AJAX response handling, existing pattern in plugin

## Confidence Assessment

| Area | Confidence | Notes |
|------|------------|-------|
| Stack | HIGH | PostgreSQL sequences well-documented in official docs, existing DatabaseService handles PDO, no new dependencies required |
| Features | HIGH | Reference code generation is mature domain, table stakes verified across multiple invoice/order systems, business requirements clear |
| Architecture | HIGH | Direct codebase analysis of existing plugin, integration points identified with line numbers, MVC pattern well-established |
| Pitfalls | HIGH | Race conditions documented in PostgreSQL community, UTF-8 issues confirmed in PHP manual, real-world examples from WordPress plugins |

**Overall confidence:** HIGH

Research is based on official documentation (PostgreSQL, PHP, WordPress), direct codebase analysis, and multiple corroborating sources for pitfalls. The problem domain (sequential ID generation) is well-understood with established patterns. No novel or experimental techniques required.

### Gaps to Address

While confidence is high, some areas need validation during implementation:

- **Client name diversity on beta site** - Research assumed English client names. During Phase 2, audit actual client names in database for Unicode characters, very short names, or unexpected patterns. May need to expand mb_substr() polyfill if non-Latin scripts present.

- **Concurrent usage patterns** - Research assumes low-frequency class creation (<10/minute). During Phase 4 testing, measure actual concurrent load on beta site. If higher than expected, may need to add pattern-specific sequence sharding (separate sequences per client prefix) to reduce lock contention.

- **Existing code format validation** - Research identified old format codes (11-REALLL-RLN-2025-06-25-02-14) should coexist with new format. During Phase 1, validate that database check constraint allows both patterns without breaking existing data: `CHECK (class_code ~ '^[A-Z]{3}[0-9]{4}$' OR class_code NOT ~ '^[A-Z]{3}')`.

- **Performance baseline** - No current metrics for class creation time. During Phase 4, establish baseline performance (e.g., class creation < 500ms total) to detect degradation. Monitor MAX() query time if sequence migration is delayed.

## Sources

### Primary (HIGH confidence)
- **PostgreSQL Official Documentation** - Sequence manipulation functions, explicit locking (SELECT FOR UPDATE), transaction isolation, RETURNING clause behavior
- **PHP Official Manual** - mb_substr(), mb_strtoupper(), mb_check_encoding(), PDO lastInsertId() issues with PostgreSQL, string function reference
- **WordPress Developer Documentation** - AJAX handling (admin-ajax.php), nonce verification, wp_send_json_success/error patterns
- **Codebase Analysis** - ClassController.php (lines 652-801), ClassModel.php (lines 324-326), class-types.js (lines 237-250), DatabaseService.php, schema/wecoza_db_schema_bu_oct_22.sql (line 1673)

### Secondary (MEDIUM confidence)
- **Generating Truly Sequential IDs in PostgreSQL** (cloudscale.ch) - Advisory locks approach for strictly sequential IDs without gaps, trade-offs vs sequences
- **WordPress AJAX Best Practices** (DEV Community, CyberPanel) - AJAX patterns in WordPress plugins, security considerations
- **Winning Race Conditions with PostgreSQL** (DEV Community) - Real-world race condition examples, SELECT FOR UPDATE patterns
- **The Fun that is UTF-8 Support in PHP** (blog.martinfjordvald.com) - PHP multibyte string handling, common pitfalls
- **Invoice Numbering Best Practices** (Hello Bonsai, Invoice Simple, Fonoa, Quick Bill Maker) - Sequential numbering requirements for audit compliance, tax authority expectations
- **Human-Readable IDs** (Connect2id, GitHub) - Pattern research for pronounceable/memorable identifiers

### Tertiary (LOW confidence)
- **PostgreSQL Scalable Sequences** (DEV.to AWS Heroes) - Performance characteristics at scale, needs validation with actual load testing
- **Generate 3-letter abbreviation in PHP** (GitHub Gist) - Community pattern examples, not production-tested
- **Patchstack Academy Race Conditions** - WordPress-specific examples, limited detail on PostgreSQL specifics

---
*Research completed: 2026-01-22*
*Ready for roadmap: yes*
