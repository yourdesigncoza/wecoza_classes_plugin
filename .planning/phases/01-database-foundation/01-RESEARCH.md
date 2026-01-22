# Phase 1: Database Foundation - Research

**Researched:** 2026-01-22
**Domain:** PostgreSQL sequence-based sequential numbering with uniqueness constraints
**Confidence:** HIGH

## Summary

PostgreSQL sequences are the standard solution for generating sequential numbers in a race-condition-free manner. They provide atomic, non-blocking operations capable of 2+ million values per second. Combined with UNIQUE constraints, they guarantee both uniqueness and performance.

The existing WeCoza Classes Plugin infrastructure already supports all required capabilities: PDO-based DatabaseService with transaction methods, migration pattern via SQL files, and external DigitalOcean managed PostgreSQL database. The classes table currently has a TEXT field `class_code` with a B-tree index but no unique constraint or sequence.

For backward compatibility with existing beta site data, the migration requires a two-phase approach: (1) create unique index concurrently to avoid blocking production operations, then (2) add constraint using that index during a brief maintenance window.

**Primary recommendation:** Use PostgreSQL native SEQUENCE objects with nextval() for code generation. Sequences are transactionally isolated (no race conditions), highly performant (sub-millisecond), and the industry-standard solution for this exact use case.

## Standard Stack

The established tools for PostgreSQL sequential numbering:

### Core
| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| PostgreSQL SEQUENCE | 9.0+ | Atomic sequential number generation | Built-in database primitive, transactionally safe, handles concurrency automatically |
| UNIQUE constraint | 9.0+ | Enforce column uniqueness | Creates B-tree index automatically, provides database-level guarantee |
| B-tree index | 9.0+ | Query performance on indexed columns | Default index type, optimal for equality and range queries |
| CREATE INDEX CONCURRENTLY | 9.2+ | Non-blocking index creation | Allows production migrations without downtime |

### Supporting
| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| PDO::beginTransaction() | PHP 5.1+ | Transaction management | Wrap multi-statement operations for atomicity |
| ALTER TABLE...USING INDEX | 9.1+ | Convert index to constraint | Zero-downtime migration pattern for production tables |
| UNLOGGED sequences | 9.1+ | Faster non-replicated sequences | Only if sequence values aren't critical (not recommended for business data) |

### Alternatives Considered
| Instead of | Could Use | Tradeoff |
|------------|-----------|----------|
| SEQUENCE | UUID v4 | UUIDs sacrifice sequential ordering and readability for global uniqueness without coordination |
| SEQUENCE | Application-level counter | Hand-rolled counters require complex locking logic, reduce concurrency, introduce single points of failure |
| SEQUENCE | SELECT FOR UPDATE + table counter | Creates serialization bottleneck, dramatically reduces throughput under concurrent load |

**Installation:**
```sql
-- Sequences are built into PostgreSQL core (no installation needed)
-- Verify version supports all features:
SELECT version();  -- Should be PostgreSQL 9.2+ for CONCURRENTLY
```

## Architecture Patterns

### Recommended Migration Structure
```
includes/migrations/
├── 001_add_class_code_sequence.sql       # Create sequence
├── 002_add_class_code_unique_index.sql   # Create unique index (CONCURRENTLY)
├── 003_add_class_code_constraint.sql     # Add constraint using index
└── 004_backfill_class_codes.sql          # Populate existing records (if needed)
```

### Pattern 1: Sequence-Based Code Generation
**What:** Use PostgreSQL sequence with nextval() to generate sequential numbers atomically
**When to use:** Any time you need guaranteed-unique sequential numbers with high concurrency

**Example:**
```sql
-- Source: https://www.postgresql.org/docs/current/sql-createsequence.html
-- Create sequence starting at 1001 (preserving existing codes below 1000)
CREATE SEQUENCE IF NOT EXISTS class_code_seq
    AS bigint
    INCREMENT BY 1
    START WITH 1001
    CACHE 1
    NO CYCLE
    OWNED BY classes.class_code;

-- Use in application via PDO
$db = DatabaseService::getInstance();
$stmt = $db->query("SELECT nextval('class_code_seq') AS code_number");
$result = $stmt->fetch();
$sequentialNumber = $result['code_number'];
```

### Pattern 2: Non-Blocking Unique Constraint Addition
**What:** Create index concurrently first, then convert to constraint to avoid locking production tables
**When to use:** Adding uniqueness to tables with existing data in production environments

**Example:**
```sql
-- Source: https://www.postgresql.org/docs/current/sql-createindex.html
-- Step 1: Create unique index without blocking writes (takes longer but non-blocking)
CREATE UNIQUE INDEX CONCURRENTLY idx_classes_class_code_unique
ON classes (class_code);

-- Step 2: Add constraint using existing index (brief lock)
-- Source: https://www.postgresql.org/docs/current/sql-altertable.html
ALTER TABLE classes
ADD CONSTRAINT class_code_unique
UNIQUE USING INDEX idx_classes_class_code_unique;
```

### Pattern 3: Transaction-Safe Code Generation and Insert
**What:** Generate sequence value inside transaction, use for insert, commit atomically
**When to use:** Every class creation operation

**Example:**
```php
// Source: Existing DatabaseService.php methods + PostgreSQL transaction patterns
$db = DatabaseService::getInstance();
try {
    $db->beginTransaction();

    // Generate sequential number
    $stmt = $db->query("SELECT nextval('class_code_seq') AS code_number");
    $codeNumber = $stmt->fetch()['code_number'];

    // Format with prefix (application logic, not database)
    $classCode = 'CLS-' . str_pad($codeNumber, 4, '0', STR_PAD_LEFT);

    // Insert class with generated code
    $stmt = $db->prepare("INSERT INTO classes (class_code, ...) VALUES (?, ...)");
    $stmt->execute([$classCode, ...]);

    $db->commit();
} catch (\Exception $e) {
    $db->rollback();
    throw $e;
}
```

### Pattern 4: Backward-Compatible Migration (Expand-Migrate-Contract)
**What:** Add new constraints alongside existing data, migrate gradually, remove old patterns
**When to use:** Modifying production databases with existing records

**Example:**
```sql
-- Phase 1: EXPAND - Add sequence and index (non-blocking)
CREATE SEQUENCE IF NOT EXISTS class_code_seq START WITH 1001;
CREATE UNIQUE INDEX CONCURRENTLY idx_classes_class_code_unique ON classes (class_code);

-- Phase 2: MIGRATE - Backfill existing NULLs (if any)
UPDATE classes SET class_code = 'CLS-' || LPAD(nextval('class_code_seq')::text, 4, '0')
WHERE class_code IS NULL OR class_code = '';

-- Phase 3: CONTRACT - Add constraint, application starts using sequence
ALTER TABLE classes ADD CONSTRAINT class_code_unique UNIQUE USING INDEX idx_classes_class_code_unique;
```

### Anti-Patterns to Avoid

- **Using application-level locks for code generation:** Database sequences are atomic by design; application-level locking adds complexity without benefit and reduces concurrency
- **SELECT MAX(code) + 1 pattern:** Creates race conditions between SELECT and INSERT; two concurrent requests can select same MAX value
- **Large CACHE values (>1) when sequential order matters:** CACHE allocates values in chunks to sessions; sessions can use values out-of-order, creating visible gaps in sequence
- **Creating unique constraint directly on production table:** ALTER TABLE ADD CONSTRAINT takes ACCESS EXCLUSIVE lock, blocking all operations during index build
- **Checking for duplicates in application before insert:** Race condition between check and insert; use database UNIQUE constraint as single source of truth

## Don't Hand-Roll

Problems that look simple but have existing solutions:

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| Sequential number generation | Application counter with locks/mutex | PostgreSQL SEQUENCE | Sequences are atomic, handle concurrency automatically, survive server restarts, replicate correctly |
| Uniqueness validation | Application-level duplicate checks | UNIQUE constraint | Race conditions between check and insert; database constraint enforces at commit time |
| Gapless numbering | Custom table-based counter | Accept gaps or use LOCK TABLE pattern | Gapless sequences require serializing all inserts, eliminating concurrency benefits |
| Transaction rollback handling | Manual sequence value tracking | Let sequences handle gaps | nextval() is non-transactional by design; gaps from rollbacks are acceptable and expected |
| Index creation on production | Direct ALTER TABLE with index build | CREATE INDEX CONCURRENTLY | Non-blocking approach prevents production downtime during migration |

**Key insight:** PostgreSQL sequences are specifically designed for high-concurrency sequential ID generation. Hand-rolling solutions reintroduces race conditions, reduces throughput, and adds maintenance burden. The database handles atomicity, isolation, and performance optimization automatically.

## Common Pitfalls

### Pitfall 1: Assuming Sequences are Gapless
**What goes wrong:** Expecting sequential numbers without gaps; discovering gaps after transaction rollbacks or failed inserts
**Why it happens:** nextval() is never rolled back - once a value is obtained, it's consumed even if the transaction aborts. This is intentional for performance and concurrency.
**How to avoid:** Document that gaps are expected and acceptable. Design UI/reporting to not rely on gapless sequences. Use CACHE 1 to minimize (but not eliminate) gaps.
**Warning signs:** Business requirements specify "no gaps allowed" or "sequential invoice numbering" - flag these as requiring different architecture (e.g., LOCK TABLE pattern with severe concurrency tradeoffs)

### Pitfall 2: Adding Unique Constraint Directly to Production Table
**What goes wrong:** ALTER TABLE ADD CONSTRAINT locks table for entire index build duration; production writes blocked for minutes to hours on large tables
**Why it happens:** Constraints require indexes; building index takes time proportional to table size; standard index build acquires ACCESS EXCLUSIVE lock
**How to avoid:** Use two-step pattern: CREATE UNIQUE INDEX CONCURRENTLY (non-blocking), then ALTER TABLE ADD CONSTRAINT USING INDEX (brief lock)
**Warning signs:** Migration runs against live production table without CONCURRENTLY keyword; no mention of maintenance window for constraint addition

### Pitfall 3: Forgetting OWNED BY Clause
**What goes wrong:** Sequence remains after dropping table/column, causing namespace pollution and confusion
**Why it happens:** Sequences are independent objects by default; no automatic cleanup without OWNED BY
**How to avoid:** Always specify OWNED BY table.column when creating sequences tied to specific tables
**Warning signs:** DROP TABLE fails to remove sequence; orphaned sequences accumulate in database

### Pitfall 4: Using SELECT FOR UPDATE for Sequential Numbering
**What goes wrong:** Serializes all code generation requests; throughput drops to single-transaction-at-a-time; deadlock risks
**Why it happens:** Misunderstanding that row-level locks are needed for atomic operations; sequences already provide atomicity
**How to avoid:** Use sequences for ID generation, reserve SELECT FOR UPDATE only for protecting complex business logic during updates
**Warning signs:** Code contains "SELECT MAX(class_code) ... FOR UPDATE" or table locking for counter increments

### Pitfall 5: Not Validating Existing Data Before Adding Constraint
**What goes wrong:** CREATE UNIQUE INDEX or ADD CONSTRAINT fails midway through; invalid index left behind; production blocked
**Why it happens:** Existing data contains duplicates; constraint enforcement discovers violations during index build
**How to avoid:** Run validation query BEFORE migration: `SELECT class_code, COUNT(*) FROM classes GROUP BY class_code HAVING COUNT(*) > 1;` Fix duplicates first.
**Warning signs:** Migration has no pre-flight validation step; no rollback plan if constraint addition fails

### Pitfall 6: Caching Sequence Values in Application
**What goes wrong:** Application caches nextval() results across requests; duplicates generated after cache refresh or server restart
**Why it happens:** Attempt to reduce database round-trips; misunderstanding that nextval() is extremely fast (~0.5 microseconds)
**How to avoid:** Call nextval() for each new record; sequence performance makes caching unnecessary and dangerous
**Warning signs:** Sequence values stored in application memory/cache; batch allocation of sequence ranges

## Code Examples

Verified patterns from official sources:

### Creating Production-Ready Sequence
```sql
-- Source: https://www.postgresql.org/docs/current/sql-createsequence.html
CREATE SEQUENCE IF NOT EXISTS class_code_seq
    AS bigint                    -- 8-byte integer (max: 9 quintillion)
    INCREMENT BY 1               -- Increment by 1 each call
    START WITH 1001              -- Start after existing manual codes
    MINVALUE 1                   -- Prevent negative values
    NO MAXVALUE                  -- Use bigint maximum
    CACHE 1                      -- No caching (strict ordering, minimal gaps)
    NO CYCLE                     -- Error when exhausted (don't wrap around)
    OWNED BY classes.class_code; -- Auto-drop with column
```

### Safe Migration: Add Unique Constraint to Existing Table
```sql
-- Source: https://www.postgresql.org/docs/current/sql-createindex.html
-- Step 1: Create unique index without blocking table (slow but safe)
CREATE UNIQUE INDEX CONCURRENTLY idx_classes_class_code_unique
ON classes (class_code)
WHERE class_code IS NOT NULL;  -- Optional: exclude NULLs if allowing them

-- Verify index is valid (CONCURRENTLY can create invalid index on failure)
SELECT schemaname, tablename, indexname, indexdef
FROM pg_indexes
WHERE indexname = 'idx_classes_class_code_unique';

-- Step 2: Add constraint using existing index (brief lock)
-- Source: https://www.postgresql.org/docs/current/sql-altertable.html
ALTER TABLE classes
ADD CONSTRAINT class_code_unique
UNIQUE USING INDEX idx_classes_class_code_unique;
```

### Using nextval() in PHP PDO
```php
// Source: DatabaseService.php + https://www.postgresql.org/docs/current/functions-sequence.html
$db = DatabaseService::getInstance();

// Simple usage - get next value
$stmt = $db->query("SELECT nextval('class_code_seq') AS code_number");
$row = $stmt->fetch();
$sequentialNumber = $row['code_number'];  // e.g., 1001

// With transaction (recommended for consistency)
try {
    $db->beginTransaction();

    $stmt = $db->query("SELECT nextval('class_code_seq') AS code_number");
    $codeNumber = $stmt->fetch()['code_number'];

    // Application formats the code (prefix, padding)
    $classCode = 'CLS-' . str_pad($codeNumber, 4, '0', STR_PAD_LEFT);

    // Insert class with generated code
    $insertStmt = $db->prepare(
        "INSERT INTO classes (class_code, client_id, class_type, ...)
         VALUES (?, ?, ?, ...)"
    );
    $insertStmt->execute([$classCode, $clientId, $classType, ...]);

    $db->commit();

} catch (\Exception $e) {
    if ($db->inTransaction()) {
        $db->rollback();
    }
    error_log("Class creation failed: " . $e->getMessage());
    throw $e;
}
```

### Checking Current Sequence Value
```sql
-- Source: https://www.postgresql.org/docs/current/functions-sequence.html
-- Get current value (last value returned by nextval in this session)
SELECT currval('class_code_seq');

-- Get last value set by any session (examine sequence state)
SELECT last_value FROM class_code_seq;

-- Set sequence to specific value (e.g., after data migration)
SELECT setval('class_code_seq', 1000);  -- Next nextval returns 1001
```

### Pre-Migration Validation
```sql
-- Check for duplicate class_code values that would violate uniqueness
SELECT class_code, COUNT(*) as duplicate_count
FROM classes
WHERE class_code IS NOT NULL
GROUP BY class_code
HAVING COUNT(*) > 1
ORDER BY duplicate_count DESC;

-- Check for NULL class_code values
SELECT COUNT(*) as null_count
FROM classes
WHERE class_code IS NULL OR class_code = '';

-- Preview how many records will be affected
SELECT
    COUNT(*) as total_records,
    COUNT(DISTINCT class_code) as unique_codes,
    COUNT(*) - COUNT(DISTINCT class_code) as duplicates
FROM classes
WHERE class_code IS NOT NULL;
```

### Monitoring Query Performance
```sql
-- Test query performance on class_code field (should be <1ms with index)
EXPLAIN ANALYZE
SELECT * FROM classes WHERE class_code = 'CLS-0001';

-- Check index usage statistics
SELECT
    schemaname,
    tablename,
    indexname,
    idx_scan,
    idx_tup_read,
    idx_tup_fetch
FROM pg_stat_user_indexes
WHERE tablename = 'classes' AND indexname LIKE '%class_code%';
```

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| Manual code assignment | Sequence-based generation | PostgreSQL 7.1 (2001) | Eliminates race conditions, improves concurrency |
| Application-level counters | Database sequences | PostgreSQL 7.3 (2002) | Centralized atomic operations, survives app restarts |
| Blocking index creation | CREATE INDEX CONCURRENTLY | PostgreSQL 9.2 (2012) | Zero-downtime migrations on production tables |
| CACHE 1 for all sequences | CACHE 20-100 for high-throughput IDs | PostgreSQL 9.1+ best practices | Better performance when strict ordering not required |
| AUTO INCREMENT (MySQL pattern) | SERIAL/SEQUENCE (PostgreSQL native) | N/A - different databases | PostgreSQL offers more control and flexibility |

**Deprecated/outdated:**
- **SERIAL pseudo-type for new schemas**: IDENTITY columns (PostgreSQL 10+) offer better standards compliance, though SERIAL remains fully supported and widely used
- **pg_get_serial_sequence()**: Direct sequence name management preferred in modern schemas with explicit OWNED BY relationships
- **Non-concurrent constraint addition**: CREATE INDEX CONCURRENTLY pattern now standard for production databases

## Open Questions

Things that couldn't be fully resolved:

1. **Current class_code data distribution**
   - What we know: Field exists as TEXT, has B-tree index, likely contains existing codes
   - What's unclear: Are there NULL values? Duplicates? What format/pattern do existing codes follow?
   - Recommendation: Run pre-migration validation queries to inventory existing data before creating sequence. May need to set START WITH to max(existing_code) + 1

2. **Desired class_code format**
   - What we know: Database sequence generates numbers (1001, 1002, 1003...)
   - What's unclear: Does application need prefix ('CLS-')? Padding (0001)? These are formatting concerns, not database concerns
   - Recommendation: Database stores/validates numeric sequential part only OR formatted string. Planner should clarify if format logic lives in application or database

3. **Beta site maintenance window availability**
   - What we know: CREATE INDEX CONCURRENTLY is non-blocking; final constraint addition needs brief lock
   - What's unclear: Is there a maintenance window for the 2-5 second ACCESS EXCLUSIVE lock during constraint addition?
   - Recommendation: If no maintenance window available, stop at CREATE UNIQUE INDEX CONCURRENTLY (provides uniqueness without constraint metadata); add constraint later

4. **Handling existing duplicate codes (if any)**
   - What we know: UNIQUE constraint will fail if duplicates exist
   - What's unclear: Business rules for resolving duplicates (rename? merge? delete?)
   - Recommendation: Pre-migration validation query will reveal if this is a concern. If duplicates found, business stakeholder decision needed before proceeding

## Sources

### Primary (HIGH confidence)
- [PostgreSQL CREATE SEQUENCE Documentation](https://www.postgresql.org/docs/current/sql-createsequence.html) - Official syntax, options, and behavior
- [PostgreSQL Sequence Functions](https://www.postgresql.org/docs/current/functions-sequence.html) - nextval(), currval(), setval() specifications
- [PostgreSQL UNIQUE Constraints](https://www.postgresql.org/docs/current/ddl-constraints.html) - Constraint syntax and automatic index creation
- [PostgreSQL ALTER TABLE](https://www.postgresql.org/docs/current/sql-altertable.html) - Adding constraints with existing indexes
- [PostgreSQL CREATE INDEX](https://www.postgresql.org/docs/current/sql-createindex.html) - CONCURRENTLY option for non-blocking builds
- DatabaseService.php - Existing transaction support methods (beginTransaction, commit, rollback)
- schema/wecoza_db_schema_bu_oct_22.sql - Current table structure with class_code TEXT field

### Secondary (MEDIUM confidence)
- [Winning Race Conditions With PostgreSQL](https://dev.to/mistval/winning-race-conditions-with-postgresql-54gn) - SELECT FOR UPDATE vs sequences comparison
- [How to safely create unique indexes in PostgreSQL](https://medium.com/dovetail-engineering/how-to-safely-create-unique-indexes-in-postgresql-e35980e6beb5) - CONCURRENTLY pattern for production
- [PostgreSQL PHP: Transaction](https://neon.com/postgresql/postgresql-php/transaction) - PDO transaction patterns with PostgreSQL
- [Backward compatible database changes](https://planetscale.com/blog/backward-compatible-databases-changes) - Expand-migrate-contract pattern

### Tertiary (LOW confidence)
- [Scalable Sequence for PostgreSQL](https://dev.to/aws-heroes/scalable-sequence-for-postgresql-34o7) - Performance benchmarks showing 2.1M ops/sec
- [PostgreSQL Sequence Cache](https://villim.github.io/postgresql-sequence-cache) - CACHE behavior and performance impact
- [No-gap sequence in PostgreSQL](https://dev.to/yugabyte/no-gap-sequence-in-postgresql-and-yugabytedb-3feo) - Gapless alternatives (when required)

## Metadata

**Confidence breakdown:**
- Standard stack: HIGH - PostgreSQL sequences are the documented, standard solution with 20+ years of production use
- Architecture: HIGH - Official documentation provides complete syntax, behavior, and migration patterns
- Pitfalls: HIGH - Well-documented failure modes from official docs and production experience articles

**Research date:** 2026-01-22
**Valid until:** 2026-04-22 (90 days) - PostgreSQL core sequence behavior stable; CONCURRENTLY index creation mature since 2012
