# Stack Research: Sequential Class Code Generation

**Domain:** Server-side sequential ID generation for WordPress/PostgreSQL
**Researched:** 2026-01-22
**Confidence:** HIGH

## Executive Summary

Adding sequential class code generation (AGR0001, AGR0002, etc.) to an existing WordPress plugin with external PostgreSQL requires careful consideration of concurrency, race conditions, and code format requirements. The recommended approach uses PostgreSQL sequences with RETURNING clause for atomic operations, custom WordPress AJAX endpoints for server-side generation, and native PHP string functions for client name abbreviation.

**Key Decision:** Use PostgreSQL sequences (not MAX() queries) because the business accepts gaps in exchange for performance and simplicity.

## Recommended Stack

### Core Technologies

| Technology | Version | Purpose | Why Recommended |
|------------|---------|---------|-----------------|
| **PostgreSQL Sequences** | PostgreSQL 11+ | Sequential number generation | Native database feature, optimized for concurrent access, ACID-compliant. Existing `classes_class_id_seq` already in place as reference pattern. |
| **PDO with RETURNING** | PHP 8.1+ | Atomic insert + ID retrieval | Single query operation prevents race conditions, avoids unreliable `lastInsertId()` with PostgreSQL sequences |
| **WordPress AJAX (admin-ajax.php)** | WordPress 5.0+ | Code generation endpoint | Already used throughout plugin (15+ endpoints), proven pattern, no new infrastructure needed |
| **Native PHP String Functions** | PHP 8.1 | Client name abbreviation | Zero dependencies, built-in functions sufficient for English client names |

### Supporting Libraries

| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| **mb_string (polyfill)** | PHP 8.1 compatible | Multibyte-safe abbreviation | Only if client names contain unicode characters (e.g., "Société Générale" -> "SG") |
| **WordPress Transient API** | Core WordPress | Cache abbreviations | Optional optimization to avoid repeated DB queries for client names |
| **PDO Transactions** | PHP 8.1 PDO | Atomic code generation | Wrap SELECT + INSERT operations for consistency |

### Development Tools

| Tool | Purpose | Notes |
|------|---------|-------|
| **DatabaseService** | PostgreSQL abstraction | Already exists in plugin at `app/Services/Database/DatabaseService.php` |
| **WordPress nonce** | AJAX security | Use existing `wecoza_class_nonce` pattern from ClassController |
| **error_log()** | Debug race conditions | Enable WP_DEBUG during testing to catch sequence gaps |

## Recommended Approach: PostgreSQL Sequence with Prefix

### Pattern

```php
// 1. Generate client abbreviation from client_name
$clientAbbr = generateClientAbbreviation($clientName); // "Agri Vision" -> "AGR"

// 2. Get next sequence number (per-client or global)
$sql = "SELECT nextval('class_code_seq'::regclass) as next_id";
$stmt = $db->query($sql);
$nextNum = $stmt->fetch()['next_id'];

// 3. Format code
$classCode = sprintf("%s%04d", $clientAbbr, $nextNum); // "AGR0001"

// 4. Insert with RETURNING to verify
$insertSql = "INSERT INTO classes (class_code, ...) VALUES (?, ...) RETURNING class_id";
```

### Why This Works

**Sequence Benefits:**
- Concurrent-safe: `nextval()` is atomic and never returns duplicate values
- Performance: Far faster than `MAX()` queries (no table scans)
- Gaps acceptable: Business requirement is "simple codes" not "gapless codes"
- Already exists: `classes_class_id_seq` is defined in schema as reference pattern

**RETURNING Clause:**
- Single database round-trip
- Avoids race conditions with `lastInsertId()`
- Guarantees you get the ID of YOUR insert, not another concurrent transaction

**References:**
- [PostgreSQL Sequence Manipulation Functions](https://www.postgresql.org/docs/current/functions-sequence.html) - HIGH CONFIDENCE
- [PHP PDO lastInsertId Issues with PostgreSQL](https://www.php.net/manual/en/pdo.lastinsertid.php) - HIGH CONFIDENCE
- [PostgreSQL FAQ: Using Sequences](https://www.neilconway.org/docs/sequences/) - MEDIUM CONFIDENCE

## Client Name Abbreviation Strategy

### Recommended: First Letters of Words

```php
function generateClientAbbreviation($clientName, $length = 3) {
    // Remove special characters and extra spaces
    $cleaned = preg_replace('/[^a-zA-Z\s]/', '', $clientName);
    $cleaned = preg_replace('/\s+/', ' ', trim($cleaned));

    // Split into words
    $words = explode(' ', $cleaned);

    // Take first letter of each word
    $abbr = '';
    foreach ($words as $word) {
        if (!empty($word)) {
            $abbr .= strtoupper($word[0]);
        }
    }

    // Fallback: if fewer letters than desired, pad or use prefix
    if (strlen($abbr) < $length) {
        // Take first N chars of first word
        $abbr = strtoupper(substr($words[0], 0, $length));
    }

    // Limit to desired length
    return substr($abbr, 0, $length);
}
```

**Examples:**
- "Agri Vision" -> "AGR" (first 3 chars of "Agri")
- "ABC Training" -> "ABC"
- "South African Institute" -> "SAI"
- "XYZ" -> "XYZ"

**Why Native PHP:**
- PHP 8.1 doesn't have `mb_ucfirst` (added in PHP 8.4)
- English client names don't require multibyte support
- Zero dependencies, maximum compatibility
- Fast execution for real-time generation

**References:**
- [PHP String Functions](https://www.php.net/manual/en/ref.strings.php) - HIGH CONFIDENCE
- [PHP 8.4 mb_ucfirst](https://php.watch/versions/8.4/mb_ucfirst-mb_ucfirst) - HIGH CONFIDENCE (not available in PHP 8.1)
- [Generate 3-letter abbreviation in PHP](https://gist.github.com/effone/9cabb71e1b81dbd6493f2a0eaddfd6f7) - MEDIUM CONFIDENCE

## WordPress AJAX Implementation

### Endpoint Pattern

```php
// In ClassController.php constructor
add_action('wp_ajax_generate_class_code', [__CLASS__, 'generateClassCodeAjax']);
add_action('wp_ajax_nopriv_generate_class_code', [__CLASS__, 'generateClassCodeAjax']);

// AJAX handler
public static function generateClassCodeAjax() {
    // Verify nonce
    check_ajax_referer('wecoza_class_nonce', 'nonce');

    // Get parameters
    $clientId = intval($_POST['client_id']);

    try {
        $db = DatabaseService::getInstance();

        // Get client name
        $sql = "SELECT client_name FROM clients WHERE client_id = ?";
        $stmt = $db->query($sql, [$clientId]);
        $client = $stmt->fetch();

        if (!$client) {
            wp_send_json_error(['message' => 'Client not found']);
            return;
        }

        // Generate abbreviation
        $abbr = self::generateClientAbbreviation($client['client_name']);

        // Get next sequence number
        $seqSql = "SELECT nextval('class_code_seq'::regclass) as next_id";
        $seqStmt = $db->query($seqSql);
        $nextNum = $seqStmt->fetch()['next_id'];

        // Format code
        $classCode = sprintf("%s%04d", $abbr, $nextNum);

        wp_send_json_success([
            'class_code' => $classCode,
            'abbreviation' => $abbr,
            'sequence_number' => $nextNum
        ]);

    } catch (Exception $e) {
        error_log('Class code generation error: ' . $e->getMessage());
        wp_send_json_error(['message' => 'Failed to generate class code']);
    }
}
```

**Why admin-ajax.php:**
- Already used in plugin (15+ endpoints)
- Proven reliable with existing nonce pattern
- No new infrastructure needed
- WordPress 5.0+ standard approach

**Alternative: REST API (Not Recommended for This Plugin):**
- Would require new authentication setup
- Adds complexity to existing AJAX-based architecture
- Only beneficial for external API consumers (not the case here)

**References:**
- [WordPress AJAX Documentation](https://developer.wordpress.org/plugins/javascript/ajax/) - HIGH CONFIDENCE
- [AJAX Best Practices in WordPress](https://dev.to/junkern/best-practices-on-responding-to-ajax-calls-in-wordpress-plugins-439b) - MEDIUM CONFIDENCE
- [WordPress AJAX Guide 2025](https://cyberpanel.net/blog/wordpress-ajax) - MEDIUM CONFIDENCE

## Database Schema Additions

### Required: Class Code Sequence

```sql
-- Create dedicated sequence for class codes
CREATE SEQUENCE IF NOT EXISTS class_code_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

ALTER SEQUENCE class_code_seq OWNER TO doadmin;

-- Add index on class_code (already exists per schema)
-- CREATE INDEX idx_classes_class_code ON classes(class_code);

-- Optional: Add unique constraint to prevent duplicates
ALTER TABLE classes ADD CONSTRAINT unique_class_code UNIQUE (class_code);
```

**Why Separate Sequence:**
- `classes_class_id_seq` is for `class_id` (internal PK)
- `class_code_seq` is for formatted codes (user-facing)
- Allows different numbering schemes (per-client, per-year, etc.)
- Can be reset independently if needed

**Why NOT Use classes_class_id_seq:**
- Different purpose: PK vs display code
- May want to reset class codes without affecting PKs
- Future flexibility for code format changes

## Concurrency Considerations

### Race Condition Scenarios

**Scenario 1: Two Users Create Classes Simultaneously**
```
User A: nextval() -> 1 -> AGR0001
User B: nextval() -> 2 -> AGR0002
Both commit successfully ✓
```
**Safe:** Sequences guarantee unique numbers

**Scenario 2: Transaction Rollback**
```
User A: nextval() -> 1 -> starts insert -> error -> rollback
User B: nextval() -> 2 -> AGR0002
User C: nextval() -> 3 -> AGR0003
Gap exists (no AGR0001) ✓
```
**Acceptable:** Business accepts gaps for simplicity

**Scenario 3: Out-of-Order Commits**
```
User A: nextval() -> 1 (slow transaction, 10 seconds)
User B: nextval() -> 2 -> commits immediately -> AGR0002 in DB
User A: finally commits -> AGR0001 in DB
Database now has AGR0002 created before AGR0001 ✓
```
**Acceptable:** Creation timestamp determines order, not code number

### What This Approach Does NOT Guarantee

**NOT Gapless:**
- Rollbacks create gaps
- Sequence cache can cause gaps
- This is intentional for performance

**NOT Strictly Ordered by Time:**
- AGR0002 may be created before AGR0001 if commits are out of order
- Use `created_at` timestamp for chronological ordering

**NOT Per-Client Sequential:**
- Global sequence means AGR0001, XYZ0002, AGR0003
- For per-client sequences, would need `class_code_seq_client_{id}` pattern

**References:**
- [PostgreSQL Sequence Race Conditions](https://www.neilconway.org/docs/sequences/) - MEDIUM CONFIDENCE
- [Generating Truly Sequential IDs in PostgreSQL](https://www.cloudscale.ch/en/engineering-blog/2025/10/09/generating-truly-sequential-ids-in-postgresql) - MEDIUM CONFIDENCE
- [PostgreSQL Concurrency Issues](https://www.postgresql.org/files/developer/concurrency.pdf) - MEDIUM CONFIDENCE

## Alternatives Considered

### Alternative 1: MAX() + 1 with SELECT FOR UPDATE

```php
$db->beginTransaction();
$sql = "SELECT COALESCE(MAX(CAST(SUBSTRING(class_code FROM '\d+$') AS INTEGER)), 0) + 1
        FROM classes WHERE class_code LIKE ? FOR UPDATE";
$stmt = $db->query($sql, [$abbr . '%']);
$nextNum = $stmt->fetch()['max'];
$classCode = sprintf("%s%04d", $abbr, $nextNum);
// Insert...
$db->commit();
```

| Aspect | MAX() Approach | Sequence Approach |
|--------|----------------|-------------------|
| Performance | Slow (table scan) | Fast (index lookup) |
| Gapless | Yes | No |
| Concurrency | Serialized (locks) | Parallel |
| Complexity | High | Low |

**Verdict:** NOT RECOMMENDED. Significant performance hit, complex locking, no business requirement for gapless codes.

**Reference:** [PostgreSQL Explicit Locking](https://www.postgresql.org/docs/current/explicit-locking.html) - HIGH CONFIDENCE

### Alternative 2: Application-Level Counter

Store counter in WordPress options table:

```php
$counter = get_option('class_code_counter', 0);
$counter++;
update_option('class_code_counter', $counter);
$classCode = sprintf("AGR%04d", $counter);
```

**Problems:**
- WordPress MySQL, not PostgreSQL
- Race conditions without proper locking
- Requires cross-database coordination
- Breaks plugin architecture (external DB principle)

**Verdict:** NOT RECOMMENDED. Violates architecture principle of external PostgreSQL database.

### Alternative 3: Truly Sequential IDs with Advisory Locks

From cloudscale.ch research: Use advisory locks + deferred triggers to guarantee strictly increasing IDs.

```sql
CREATE TABLE class_code_lock (lock_id INTEGER PRIMARY KEY);
INSERT INTO class_code_lock VALUES (1);

CREATE FUNCTION assign_class_code()
RETURNS TRIGGER AS $$
BEGIN
    LOCK TABLE class_code_lock IN EXCLUSIVE MODE;
    NEW.class_code := generate_code_with_sequence();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE CONSTRAINT TRIGGER set_class_code
    AFTER INSERT ON classes
    DEFERRABLE INITIALLY DEFERRED
    FOR EACH ROW EXECUTE FUNCTION assign_class_code();
```

**When to Use:**
- Audit log requirements (strict ordering)
- API pagination requiring monotonic IDs
- Compliance/legal requirements for gapless sequences

**Why NOT Recommended Here:**
- Adds significant complexity (triggers, lock tables)
- Performance impact (serializes all inserts)
- Business requirement is "simple codes" not "strictly ordered codes"
- Overkill for training class codes

**Reference:** [Generating Truly Sequential IDs in PostgreSQL](https://www.cloudscale.ch/en/engineering-blog/2025/10/09/generating-truly-sequential-ids-in-postgresql) - MEDIUM CONFIDENCE

## What NOT to Use

| Avoid | Why | Use Instead |
|-------|-----|-------------|
| **PDO lastInsertId() with PostgreSQL** | Returns wrong ID with concurrent inserts, non-transactional sequences make it unreliable | Use `RETURNING class_id` in INSERT statement |
| **MAX(id) + 1 without locks** | Race conditions guarantee duplicate codes with concurrent users | Use PostgreSQL sequences with nextval() |
| **Client-side timestamp codes** | Collisions possible, not human-readable, no sequential ordering | Server-side generation with sequences |
| **WordPress AUTO_INCREMENT** | Classes table in PostgreSQL, not MySQL; breaks architecture | Use PostgreSQL sequences |
| **UUID/GUID for class codes** | Not simple/memorable (e.g., "550e8400-e29b-41d4-a716"), fails business requirement | Use abbreviation + sequence pattern |
| **PHP 8.4 mb_ucfirst** | Not available in PHP 8.1 (current server version) | Use mb_substr + mb_strtoupper polyfill |
| **REST API for this endpoint** | Adds complexity, requires new auth, not needed for internal AJAX | Use existing admin-ajax.php pattern |

## Stack Patterns by Variant

### If Client Names Contain Unicode Characters:

Use mb_string polyfill for PHP 8.1:

```php
function mb_ucfirst_polyfill($string, $encoding = 'UTF-8') {
    $firstChar = mb_substr($string, 0, 1, $encoding);
    $rest = mb_substr($string, 1, null, $encoding);
    return mb_strtoupper($firstChar, $encoding) . $rest;
}
```

**Why:** Native `mb_ucfirst` only available in PHP 8.4+, server runs PHP 8.1

**Reference:** [PHP mb_ucfirst Polyfill](https://www.if-not-true-then-false.com/2010/php-mb_ucfirst-make-a-strings-first-character-uppercase-multibyte-function/) - MEDIUM CONFIDENCE

### If Per-Client Sequences Required:

Create sequences dynamically per client:

```php
// On first class for client
$seqName = "class_code_seq_client_{$clientId}";
$db->exec("CREATE SEQUENCE IF NOT EXISTS {$seqName} START WITH 1");

// Use client-specific sequence
$sql = "SELECT nextval('{$seqName}'::regclass) as next_id";
```

**Trade-off:** More complex, many sequences to manage, but guarantees AGR0001, AGR0002 all belong to same client

### If Gapless Codes Required (NOT RECOMMENDED):

Use exclusive table lock approach:

```php
$db->beginTransaction();
$db->exec("LOCK TABLE classes IN EXCLUSIVE MODE");
// Get MAX + 1
// Insert
$db->commit();
```

**Warning:** Severe performance impact, serializes all inserts, only use if legal/compliance requires gapless

## Version Compatibility

| Package | Compatible With | Notes |
|---------|-----------------|-------|
| PHP 8.1.2 | PostgreSQL 11+ | PDO PostgreSQL driver included |
| WordPress 5.0+ | PHP 8.1 | Current plugin compatibility |
| PostgreSQL 11+ | PHP PDO | RETURNING clause, sequences, advisory locks all supported |
| mb_string extension | PHP 8.1 | Available, but mb_ucfirst requires PHP 8.4+ |

## Installation

### Core (Already Installed)
No additional packages required. Feature uses existing infrastructure.

### Database Schema Changes
```bash
# Run this migration in PostgreSQL
psql -h db-wecoza-3-do-user-17263152-0.m.db.ondigitalocean.com \
     -p 25060 -U doadmin -d defaultdb << 'EOF'

CREATE SEQUENCE IF NOT EXISTS class_code_seq
    AS integer START WITH 1 INCREMENT BY 1;

ALTER TABLE classes ADD CONSTRAINT unique_class_code UNIQUE (class_code);
EOF
```

### JavaScript Update
Replace client-side `generateClassCode()` in `class-types.js` with AJAX call:

```javascript
async function fetchClassCode(clientId) {
    const response = await fetch(wecozaClass.ajaxUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'generate_class_code',
            nonce: wecozaClass.nonce,
            client_id: clientId
        })
    });

    const data = await response.json();
    if (data.success) {
        document.getElementById('class_code').value = data.data.class_code;
    }
}
```

## Performance Characteristics

### Expected Performance
| Operation | Expected Time | Notes |
|-----------|---------------|-------|
| nextval() sequence call | <1ms | In-memory operation, very fast |
| Client name query | 5-10ms | Indexed lookup by client_id |
| AJAX endpoint total | 50-100ms | Includes network latency to DigitalOcean |

### Concurrency Scaling
| Concurrent Users | Performance Impact | Notes |
|------------------|-------------------|-------|
| 1-10 | No impact | Sequences handle easily |
| 10-100 | No impact | Designed for high concurrency |
| 100+ | <5ms added latency | Minimal contention on sequence |

**Reference:** [PostgreSQL Scalable Sequences](https://dev.to/aws-heroes/scalable-sequence-for-postgresql-34o7) - MEDIUM CONFIDENCE

## Sources

**HIGH CONFIDENCE (PostgreSQL Official Docs):**
- [PostgreSQL Sequence Manipulation Functions](https://www.postgresql.org/docs/current/functions-sequence.html) - Sequence behavior, nextval(), currval()
- [PostgreSQL CREATE SEQUENCE](https://www.postgresql.org/docs/current/sql-createsequence.html) - Sequence syntax and options
- [PostgreSQL Explicit Locking](https://www.postgresql.org/docs/current/explicit-locking.html) - SELECT FOR UPDATE, table locks

**HIGH CONFIDENCE (PHP Official Docs):**
- [PHP PDO lastInsertId](https://www.php.net/manual/en/pdo.lastinsertid.php) - PostgreSQL-specific issues
- [PHP String Functions](https://www.php.net/manual/en/ref.strings.php) - Native string manipulation
- [PHP mb_ucfirst](https://php.watch/versions/8.4/mb_ucfirst-mb_ucfirst) - PHP 8.4+ only feature

**HIGH CONFIDENCE (WordPress Official Docs):**
- [WordPress AJAX Documentation](https://developer.wordpress.org/plugins/javascript/ajax/) - Official AJAX guide

**MEDIUM CONFIDENCE (Technical Articles 2025-2026):**
- [Generating Truly Sequential IDs in PostgreSQL](https://www.cloudscale.ch/en/engineering-blog/2025/10/09/generating-truly-sequential-ids-in-postgresql) - Advisory locks approach
- [WordPress AJAX Best Practices](https://dev.to/junkern/best-practices-on-responding-to-ajax-calls-in-wordpress-plugins-439b) - AJAX patterns
- [WordPress AJAX Guide 2025](https://cyberpanel.net/blog/wordpress-ajax) - Modern practices

**MEDIUM CONFIDENCE (Community Resources):**
- [FAQ: Using Sequences in PostgreSQL](https://www.neilconway.org/docs/sequences/) - Race conditions, gaps, concurrency
- [PostgreSQL Concurrency Issues (PDF)](https://www.postgresql.org/files/developer/concurrency.pdf) - Tom Lane's concurrency guide
- [Generate 3-letter abbreviation in PHP](https://gist.github.com/effone/9cabb71e1b81dbd6493f2a0eaddfd6f7) - Abbreviation patterns
- [PHP mb_ucfirst Polyfill](https://www.if-not-true-then-false.com/2010/php-mb_ucfirst-make-a-strings-first-character-uppercase-multibyte-function/) - PHP 8.1 compatibility

**MEDIUM CONFIDENCE (Performance):**
- [PostgreSQL Scalable Sequences](https://dev.to/aws-heroes/scalable-sequence-for-postgresql-34o7) - Sequence scalability

---
*Stack research for: Sequential class code generation in WordPress/PostgreSQL*
*Researched: 2026-01-22*
*For: WeCoza Classes Plugin*
*PHP Version: 8.1.2*
*PostgreSQL Version: 13+*
