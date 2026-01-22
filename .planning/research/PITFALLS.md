# Domain Pitfalls: Sequential Code Generation

**Domain:** PHP/PostgreSQL Sequential ID Generation with String Prefixes
**Researched:** 2026-01-22
**Implementation:** PHP 7.4+, PostgreSQL, WordPress AJAX, Beta site with existing data

## Critical Pitfalls

Mistakes that cause duplicate codes, data corruption, or require rewrites.

### Pitfall 1: Race Condition with MAX()+1 Pattern

**What goes wrong:** Two concurrent AJAX requests both query `MAX(class_code)`, get the same result, and insert duplicate sequential numbers.

**Why it happens:** MVCC (Multi-Version Concurrency Control) in PostgreSQL means each transaction sees a snapshot of the database at transaction start. The time window between `SELECT MAX()` and `INSERT` is large enough for another transaction to insert a conflicting row.

**Real-world scenario:**
```
Time    Transaction A                Transaction B
----    ------------------          ------------------
T1      BEGIN
T2      SELECT MAX(code) -> 1234    BEGIN
T3                                  SELECT MAX(code) -> 1234
T4      INSERT 'AGR1235'
T5                                  INSERT 'AGR1235' <- DUPLICATE!
T6      COMMIT                      COMMIT
```

**Consequences:**
- Duplicate class codes in database
- Primary key violation if `class_code` has unique constraint
- Silent data corruption if no constraint exists
- User confusion when multiple classes share same code

**Prevention:**

**Option 1: PostgreSQL SEQUENCE (Recommended)**
```sql
-- Migration: Create sequence
CREATE SEQUENCE class_code_seq START 1;

-- PHP: Use sequence for numbering
$seq = $db->query("SELECT nextval('class_code_seq')")->fetchColumn();
$code = $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
```
**Why:** Sequences are atomic and transaction-safe. Multiple transactions get different values without race conditions.

**Option 2: SELECT FOR UPDATE (Table Lock)**
```php
// PHP with transaction
$db->beginTransaction();
$stmt = $db->query("SELECT MAX(class_code) FROM classes FOR UPDATE");
$maxCode = $stmt->fetchColumn();
$nextNum = extractNumber($maxCode) + 1;
$db->exec("INSERT INTO classes (class_code, ...) VALUES ('AGR$nextNum', ...)");
$db->commit();
```
**Why:** `FOR UPDATE` locks the table/rows until transaction commits, preventing concurrent modifications.
**Cost:** Disk writes for lock, reduced concurrency, all class creation serialized.

**Option 3: Advisory Locks (Application-Level)**
```php
// PHP with advisory lock
$db->query("SELECT pg_advisory_xact_lock(12345)"); // Lock ID for class codes
$maxCode = $db->query("SELECT MAX(class_code) FROM classes")->fetchColumn();
$nextNum = extractNumber($maxCode) + 1;
$db->exec("INSERT INTO classes (class_code, ...) VALUES ('AGR$nextNum', ...)");
// Lock released at transaction end
```
**Why:** Lighter than `FOR UPDATE`, no disk writes, but requires application coordination.

**Detection warning signs:**
- Duplicate class codes appearing in database
- Intermittent "unique constraint violation" errors under load
- Production logs showing concurrent AJAX requests to save_class endpoint
- QA testing with multiple browser tabs creating classes simultaneously

**Phase assignment:** Phase 2 (Core Implementation) - Must be addressed BEFORE first code generation.

---

### Pitfall 2: Multibyte Character Substring Corruption

**What goes wrong:** Using `substr()` to extract 3 characters from UTF-8 client names cuts multibyte characters in half, producing garbled abbreviations or wrong length prefixes.

**Why it happens:** PHP's `substr()` operates on bytes, not characters. UTF-8 uses 1-4 bytes per character. Names with accented characters (café), emoji (🏢), or non-Latin scripts break substring operations.

**Real-world examples:**
```php
// WRONG - byte-based substring
$name = "Café Industries";
$prefix = strtoupper(substr($name, 0, 3)); // "CAF" - cuts é in half, may show "CAF�"

$name = "🏢 Business Corp";
$prefix = strtoupper(substr($name, 0, 3)); // Corrupted - emoji is 4 bytes

$name = "Zürich AG";
$prefix = strtoupper(substr($name, 0, 3)); // "ZÃ¼" - only 2 visible chars

// RIGHT - character-based substring
$prefix = strtoupper(mb_substr($name, 0, 3, 'UTF-8')); // "CAF", "BUS", "ZÜR"
```

**Consequences:**
- Corrupted class codes stored in database
- Class codes with wrong length (fewer than 3 visible chars)
- Database encoding errors if character corruption propagates
- Unprofessional appearance in reports and user interface

**Prevention:**
```php
// REQUIRED: Use mb_substr() for multibyte safety
function generatePrefix($clientName) {
    // Strip leading non-letter characters
    $clean = preg_replace('/^[^a-zA-Z]+/u', '', $clientName);

    // Extract first 3 letters using multibyte-aware function
    $prefix = mb_substr($clean, 0, 3, 'UTF-8');
    $prefix = mb_strtoupper($prefix, 'UTF-8');

    // Validate encoding
    if (!mb_check_encoding($prefix, 'UTF-8')) {
        throw new Exception("Invalid UTF-8 encoding in client name");
    }

    return $prefix;
}
```

**Additional safeguards:**
- Validate client names contain at least 3 letters during client creation
- Test with diverse Unicode: emoji (🏢), accents (café), Cyrillic (Москва), Chinese (北京)
- Add database constraint: `CHECK (length(class_code) = 7)` to catch length violations

**Detection warning signs:**
- Class codes with fewer than 7 characters
- Garbled characters in class code prefixes
- Database warnings about invalid UTF-8 sequences
- Errors when exporting class data to CSV/PDF

**Phase assignment:** Phase 2 (Core Implementation) - Must handle during prefix extraction.

---

### Pitfall 3: Client Names Shorter Than 3 Letters

**What goes wrong:** Client names like "AB Ltd" or "X Corp" produce 2-character or 1-character prefixes instead of required 3 characters, breaking the fixed format (XXX####).

**Why it happens:** Project requirements assume all client names have at least 3 letters. Real-world data has edge cases: single-letter brands (X, Q), two-letter acronyms (AB, IT), or names starting with numbers/symbols.

**Real-world examples:**
```
Client Name          Naive Extraction     Problem
-----------          ----------------     -------
"AB Ltd"             "AB"                 Only 2 chars
"X Corporation"      "X"                  Only 1 char
"3M Company"         "3M"                 Starts with number
"& Partners"         "&"                  Special character
"  ABC  "            " A" or "AB"         Leading whitespace
```

**Consequences:**
- Variable-length class codes break parsing logic
- Database constraint violations if `CHECK (length(class_code) = 7)` exists
- User confusion when codes don't match expected format
- Sorting/filtering issues when codes have inconsistent format

**Prevention strategies:**

**Strategy 1: Pad with fallback character (Recommended for MVP)**
```php
function generatePrefix($clientName) {
    // Extract letters only
    preg_match_all('/[a-zA-Z]/u', $clientName, $matches);
    $letters = implode('', $matches[0]);

    // Take first 3 or pad with 'X'
    $prefix = mb_substr($letters, 0, 3, 'UTF-8');
    $prefix = str_pad($prefix, 3, 'X', STR_PAD_RIGHT);

    return mb_strtoupper($prefix, 'UTF-8');
}

// Examples:
// "AB Ltd"         -> "ABX1234"
// "X Corp"         -> "XXX1234"
// "ABC Industries" -> "ABC1234"
```

**Strategy 2: Use full name fallback**
```php
function generatePrefix($clientName) {
    preg_match_all('/[a-zA-Z]/u', $clientName, $matches);
    $letters = implode('', $matches[0]);

    if (mb_strlen($letters, 'UTF-8') < 3) {
        // Fallback: Use "CLI" (CLIent) prefix for short names
        return 'CLI';
    }

    return mb_strtoupper(mb_substr($letters, 0, 3, 'UTF-8'), 'UTF-8');
}

// Examples:
// "AB Ltd"         -> "CLI1234"
// "X Corp"         -> "CLI1234"
// "ABC Industries" -> "ABC1234"
```

**Strategy 3: Validation during client creation (Future enhancement)**
```php
// In client creation form validation
function validateClientName($name) {
    preg_match_all('/[a-zA-Z]/u', $name, $matches);
    $letterCount = count($matches[0]);

    if ($letterCount < 3) {
        throw new ValidationException(
            "Client name must contain at least 3 letters for class code generation"
        );
    }
}
```

**Detection warning signs:**
- Class codes shorter than 7 characters
- Multiple classes with "XXX" or "CLI" prefixes
- User reports of "weird" class codes
- Exception logs about padding operations

**Phase assignment:**
- Phase 2 (Core Implementation): Implement padding strategy
- Phase 4 (Testing & Refinement): Add client name validation
- Out of scope for MVP: Retroactive client name requirements

---

### Pitfall 4: MAX() Performance Degradation at Scale

**What goes wrong:** Querying `MAX(class_code)` on every class creation becomes slow as the classes table grows (1000+ rows), especially with JSONB columns and complex indexes.

**Why it happens:**
- `MAX()` requires full table scan if no index exists on `class_code`
- Even with index, MAX() still needs to scan index to find highest value
- Sequential scan locks escalate with concurrent transactions
- JSONB columns inflate row size, slowing sequential scans

**Performance benchmark (from research):**
```
Table Size      MAX() Query Time    Concurrent Load
----------      ----------------    ---------------
100 rows        < 10ms              No issues
1,000 rows      20-50ms             Slight delays
10,000 rows     100-300ms           Noticeable lag
100,000 rows    500ms-2s            User-facing slowdown
```

**Real-world scenario:**
Beta site with 200 classes sees no issues. Production with 5,000+ classes per year experiences:
- Slow class creation forms (1-2 second lag)
- Timeout errors during high-traffic periods
- Increased database CPU usage
- Frustrated users

**Consequences:**
- Poor user experience (slow form submissions)
- AJAX timeout errors (WordPress default: 30 seconds)
- Database server CPU spikes during concurrent class creation
- Cascade failures if multiple operations timeout

**Prevention:**

**Immediate (Phase 2):**
```sql
-- Add index on class_code for faster MAX() queries
CREATE INDEX idx_classes_class_code ON classes(class_code);
```
**Impact:** Reduces MAX() time by 80-90% for indexed column.

**Short-term (Phase 4 - if performance issues detected):**
```sql
-- Partial index (only new format codes for even faster queries)
CREATE INDEX idx_classes_code_new_format
ON classes(class_code)
WHERE class_code ~ '^[A-Z]{3}[0-9]{4}$';
```

**Long-term (Future milestone - if scaling to 10K+ classes):**
```sql
-- Migrate to PostgreSQL SEQUENCE (eliminates MAX() entirely)
CREATE SEQUENCE class_code_seq START 1;

-- One-time migration to sync sequence with existing max
SELECT setval('class_code_seq',
    (SELECT MAX(CAST(substring(class_code, 4) AS integer))
     FROM classes
     WHERE class_code ~ '^[A-Z]{3}[0-9]{4}$')
);
```

**Monitoring strategy:**
```php
// Add timing logs to detect performance degradation
$startTime = microtime(true);
$maxCode = $db->query("SELECT MAX(class_code) FROM classes")->fetchColumn();
$queryTime = (microtime(true) - $startTime) * 1000;

if ($queryTime > 100) {
    error_log("SLOW MAX() query: {$queryTime}ms for class code generation");
}
```

**Detection warning signs:**
- Class creation AJAX requests taking > 2 seconds
- Database slow query logs showing MAX(class_code) queries
- Increased database CPU usage during business hours
- User complaints about "slow" class creation

**Phase assignment:**
- Phase 2: Add basic index on `class_code`
- Phase 4: Monitor query performance, add logging
- Future milestone: Migrate to SEQUENCE if scaling beyond 10K classes

---

### Pitfall 5: Breaking Existing Classes During Migration

**What goes wrong:** Code changes inadvertently modify existing class codes during update operations, corrupting historical data on a live beta site.

**Why it happens:**
- Update operations run code generation logic even for existing classes
- No differentiation between "create new class" and "update existing class"
- Eager execution of code generation in model constructors or setters
- Missing "code already exists" checks before generation

**Real-world scenario:**
```php
// DANGEROUS: Code generation in setter runs on every update
public function setClassCode($code = null) {
    if (empty($code)) {
        $this->classCode = $this->generateNewCode(); // <- RUNS ON UPDATE!
    } else {
        $this->classCode = $code;
    }
}

// User edits class to change start date
// Update logic calls setClassCode() with existing code
// But if $code is accidentally null/empty, generates NEW code
// Old code: "11-REALLL-RLN-2025-06-25-02-14"
// New code: "AGR1235"
// Historical references broken
```

**Consequences:**
- Existing classes lose their original codes
- Reports and exports reference wrong class codes
- User confusion when familiar codes disappear
- Broken links if class codes used in URLs or external systems
- Audit trail corruption if codes track to external databases

**Prevention:**

**Strict separation of create vs update:**
```php
// ClassController.php
public function ajaxSaveClass() {
    $isNewClass = empty($_POST['class_id']);

    if ($isNewClass) {
        // ONLY generate code for new classes
        $classData['class_code'] = $this->generateClassCode($clientId, $clientName);
    } else {
        // NEVER regenerate code for existing classes
        $existingClass = ClassModel::find($_POST['class_id']);
        $classData['class_code'] = $existingClass->getClassCode(); // Keep existing
    }

    // Continue with save/update
}
```

**Database-level protection:**
```sql
-- Add trigger to prevent class_code updates (optional, for paranoid safety)
CREATE OR REPLACE FUNCTION prevent_class_code_update()
RETURNS TRIGGER AS $$
BEGIN
    IF OLD.class_code IS NOT NULL AND NEW.class_code != OLD.class_code THEN
        RAISE EXCEPTION 'Cannot change class_code for existing class %', OLD.class_id;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_prevent_code_update
BEFORE UPDATE ON classes
FOR EACH ROW
EXECUTE FUNCTION prevent_class_code_update();
```

**Code generation safeguards:**
```php
private function generateClassCode($clientId, $clientName) {
    // NEVER call this for existing classes - validation
    if (!empty($this->id)) {
        throw new Exception("Cannot generate code for existing class {$this->id}");
    }

    // Generate code logic...
}
```

**Testing protocol:**
```php
// Required tests before deployment
function testUpdateDoesNotChangeCode() {
    // Create class with old format code
    $class = new ClassModel([
        'class_code' => '11-REALLL-RLN-2025-06-25-02-14',
        'client_id' => 123,
        // ... other fields
    ]);
    $class->save();

    // Update class (change start date)
    $class->setOriginalStartDate('2025-07-01');
    $class->save();

    // VERIFY: Code unchanged
    $reloaded = ClassModel::find($class->getId());
    assert($reloaded->getClassCode() === '11-REALLL-RLN-2025-06-25-02-14');
}
```

**Detection warning signs:**
- Class codes changing in database after update operations
- User reports of "missing" class codes
- Discrepancy between class codes in backups vs current database
- Exception logs about "code already exists" during updates

**Phase assignment:**
- Phase 1 (Planning & Discovery): Document update vs create workflows
- Phase 2 (Core Implementation): Enforce strict create-only generation
- Phase 3 (Integration): Test all update paths don't trigger generation
- Phase 4 (Testing): Comprehensive update regression tests

---

## Moderate Pitfalls

Mistakes that cause technical debt, reduced maintainability, or operational issues.

### Pitfall 6: No Duplicate Detection Recovery

**What goes wrong:** If a duplicate code somehow gets generated (race condition, migration error, manual data entry), the system has no way to detect or recover.

**Prevention:**
```sql
-- Add unique constraint (catches duplicates at database level)
ALTER TABLE classes ADD CONSTRAINT uk_class_code UNIQUE (class_code);

-- PHP: Catch and retry with next number
try {
    $db->exec("INSERT INTO classes (class_code, ...) VALUES ('AGR1234', ...)");
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'unique constraint') !== false) {
        // Log for investigation
        error_log("Duplicate class code detected: AGR1234");

        // Retry with next sequential number
        $nextCode = $this->getNextAvailableCode($prefix);
        $db->exec("INSERT INTO classes (class_code, ...) VALUES ('$nextCode', ...)");
    }
}
```

**Detection:** Monitor database constraint violation logs.

**Phase assignment:** Phase 3 (Integration) - Add after core generation works.

---

### Pitfall 7: Hardcoded Prefix Extraction Logic

**What goes wrong:** Prefix extraction logic scattered across multiple functions/files makes it difficult to change extraction rules later.

**Prevention:**
```php
// WRONG: Logic duplicated everywhere
$prefix = strtoupper(substr($clientName, 0, 3));

// RIGHT: Centralized with clear business rules
class ClassCodeGenerator {
    public static function extractPrefix($clientName) {
        // Single source of truth for prefix logic
        // Easy to modify extraction rules in one place
        preg_match_all('/[a-zA-Z]/u', $clientName, $matches);
        $letters = implode('', $matches[0]);
        $prefix = mb_substr($letters, 0, 3, 'UTF-8');
        return str_pad(mb_strtoupper($prefix, 'UTF-8'), 3, 'X', STR_PAD_RIGHT);
    }
}
```

**Phase assignment:** Phase 2 (Core Implementation) - Establish pattern from start.

---

### Pitfall 8: Missing Code Format Validation

**What goes wrong:** Code stored in database doesn't match expected format due to bugs, manual edits, or data imports.

**Prevention:**
```php
// Validate format before saving
function validateClassCode($code) {
    if (!preg_match('/^[A-Z]{3}[0-9]{4}$/', $code)) {
        throw new ValidationException(
            "Invalid class code format: '$code'. Expected format: XXX#### (e.g., AGR1234)"
        );
    }
}

// Database constraint
ALTER TABLE classes ADD CONSTRAINT chk_code_format
CHECK (class_code ~ '^[A-Z]{3}[0-9]{4}$' OR class_code NOT ~ '^[A-Z]{3}');
-- Allows old format codes but enforces format for new ones
```

**Phase assignment:** Phase 3 (Integration) - Add validation layer.

---

### Pitfall 9: No Logging for Troubleshooting

**What goes wrong:** When duplicate codes or format issues occur, no audit trail exists to debug what happened.

**Prevention:**
```php
function generateClassCode($clientId, $clientName) {
    $prefix = $this->extractPrefix($clientName);
    $maxCode = $this->getMaxSequentialNumber();
    $nextNum = $maxCode + 1;
    $code = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

    // LOG every code generation with context
    error_log(sprintf(
        "Generated class code: %s | Client: %s (%d) | Prefix: %s | Seq: %d | Time: %s",
        $code, $clientName, $clientId, $prefix, $nextNum, date('Y-m-d H:i:s')
    ));

    return $code;
}
```

**Phase assignment:** Phase 2 (Core Implementation) - Add during initial development.

---

### Pitfall 10: Client Name Changes Breaking Lookups

**What goes wrong:** If a client name changes after classes are created, prefix no longer matches client name, confusing users.

**Prevention:**
- Document that class codes are immutable snapshots
- Add client name history tracking (out of scope for MVP)
- Consider adding `client_name_at_creation` field to classes table

**Phase assignment:** Document in Phase 1, defer implementation to future enhancement.

---

## Minor Pitfalls

Mistakes that cause minor annoyance but are easily fixable.

### Pitfall 11: Case Sensitivity in Code Lookups

**What goes wrong:** Users search for "agr1234" but database stores "AGR1234", no results found.

**Prevention:**
```php
// Always uppercase user input before searching
$searchCode = mb_strtoupper(trim($_GET['class_code']), 'UTF-8');
$stmt = $db->prepare("SELECT * FROM classes WHERE class_code = ?");
$stmt->execute([$searchCode]);
```

**Phase assignment:** Phase 3 (Integration) - Add to search functionality.

---

### Pitfall 12: Leading/Trailing Whitespace in Client Names

**What goes wrong:** Client name "  ABC Ltd  " extracts prefix " AB" or "AB " with spaces.

**Prevention:**
```php
function extractPrefix($clientName) {
    $clientName = trim($clientName); // Remove leading/trailing spaces
    // Continue with extraction...
}
```

**Phase assignment:** Phase 2 (Core Implementation) - Add to prefix extraction.

---

### Pitfall 13: Special Characters Breaking Regex Patterns

**What goes wrong:** Client names with regex metacharacters (*, +, ?) break pattern matching.

**Prevention:**
```php
// Use preg_match_all with character class (safe)
preg_match_all('/[a-zA-Z]/u', $clientName, $matches);

// NOT preg_match with unescaped input (dangerous)
```

**Phase assignment:** Phase 2 (Core Implementation) - Use safe patterns.

---

## Phase-Specific Warnings

| Phase Topic | Likely Pitfall | Mitigation |
|-------------|---------------|------------|
| Phase 1: Planning | Underestimating race condition risk | Research PostgreSQL locking mechanisms early |
| Phase 2: Code Generation | Using substr() instead of mb_substr() | Establish multibyte string handling standard |
| Phase 2: Code Generation | MAX()+1 without transaction safety | Implement SELECT FOR UPDATE or advisory locks |
| Phase 3: Integration | Update operations regenerating codes | Enforce strict create-only generation checks |
| Phase 3: AJAX Handlers | Concurrent requests causing duplicates | Test with parallel requests (Chrome DevTools) |
| Phase 4: Testing | Not testing with UTF-8 client names | Create test dataset with emoji, accents, Cyrillic |
| Phase 4: Testing | Missing edge case for short names | Add test cases: "AB", "X", "3M", "& Co" |
| Phase 4: Performance | No index on class_code column | Add index before deploying to production |
| Phase 5: Deployment | Beta site has concurrent users | Deploy during low-traffic window, monitor logs |
| Future Enhancement | Scale beyond 10K classes | Plan migration to SEQUENCE when approaching 5K classes |

---

## Research Confidence Levels

| Area | Confidence | Source | Notes |
|------|------------|--------|-------|
| Race condition patterns | HIGH | PostgreSQL official docs, DEV Community articles | Well-documented PostgreSQL behavior |
| MAX()+1 performance issues | MEDIUM | CYBERTEC blog, community discussions | Confirmed by multiple sources, varies by table size |
| UTF-8 substring issues | HIGH | PHP official manual, toptal.com guide | Standard PHP multibyte issue |
| WordPress AJAX concurrency | MEDIUM | Patchstack Academy, WooCommerce issues | Real-world examples from major plugins |
| Client name edge cases | MEDIUM | Derived from PHP string handling research | Logical inference from PHP behavior |
| Database locking strategies | HIGH | PostgreSQL documentation | Official guidance |

---

## Sources

**Race Conditions & Concurrency:**
- [Winning Race Conditions With PostgreSQL](https://dev.to/mistval/winning-race-conditions-with-postgresql-54gn)
- [PostgreSQL: RE: serial type; race conditions](https://www.postgresql.org/message-id/08CD1781F85AD4118E0800A0C9B8580B0949E6@NEZU)
- [Preventing Postgres SQL Race Conditions with SELECT FOR UPDATE](https://on-systems.tech/blog/128-preventing-read-committed-sql-concurrency-errors/)
- [FAQ: Using Sequences in PostgreSQL](https://www.neilconway.org/docs/sequences/)

**Performance & Locking:**
- [PostgreSQL Documentation: Explicit Locking](https://www.postgresql.org/docs/current/explicit-locking.html)
- [Advisory locks in Postgres](https://medium.com/thefreshwrites/advisory-locks-in-postgres-1f993647d061)
- [Gaps in sequences in PostgreSQL, causes and remedies](https://www.cybertec-postgresql.com/en/gaps-in-sequences-postgresql/)
- [Locks in PostgreSQL - Concurrency Benefits and Performance Challenges](https://stormatics.tech/blogs/locks-in-postgresql-concurrency)

**PHP UTF-8 Handling:**
- [PHP: mb_substr - Manual](https://www.php.net/manual/en/function.mb-substr.php)
- [The Fun that is UTF-8 Support in PHP](https://blog.martinfjordvald.com/the-fun-that-is-utf-8-support-in-php/)
- [A Guide to UTF-8 Encoding in PHP and MySQL](https://www.toptal.com/php/a-utf-8-primer-for-php-and-mysql)
- [PHP str_contains() Function: A 2026 Engineer's Field Guide](https://thelinuxcode.com/php-str_contains-function-a-2026-engineers-field-guide/)

**WordPress & AJAX:**
- [Learn about Race Condition - Patchstack Academy](https://patchstack.com/academy/wordpress/vulnerabilities/race-condition/)
- [Finding and solving a race condition in WordPress](https://www.altis-dxp.com/finding-and-solving-a-race-condition-in-wordpress/)
- [Understanding Race Conditions: Strategies for WordPress Site Security](https://www.wpservices.com/understanding-race-conditions-strategies-for-wordpress-site-security/)
