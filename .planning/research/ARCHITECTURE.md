# Architecture Research: Server-Side Sequential Code Generation

**Domain:** WordPress Plugin MVC with External PostgreSQL
**Researched:** 2026-01-22
**Confidence:** HIGH

## Current Architecture Overview

### Existing System Structure

```
┌─────────────────────────────────────────────────────────────┐
│                   PRESENTATION LAYER                         │
├─────────────────────────────────────────────────────────────┤
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │ Shortcodes   │  │  View Files  │  │  JavaScript  │      │
│  │ (WordPress)  │  │  (PHP)       │  │  (Browser)   │      │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘      │
│         │                 │                  │              │
│         └─────────────────┼──────────────────┘              │
│                           │                                 │
├───────────────────────────┼─────────────────────────────────┤
│                   CONTROLLER LAYER                           │
├─────────────────────────────────────────────────────────────┤
│  ┌────────────────────────────────────────────────────┐     │
│  │         ClassController (AJAX Endpoints)           │     │
│  │  - saveClassAjax()   - updateClassAjax()          │     │
│  │  - deleteClass()     - getCalendarEvents()        │     │
│  └────────────────────┬───────────────────────────────┘     │
│                       │                                     │
├───────────────────────┼─────────────────────────────────────┤
│                   MODEL LAYER                                │
├─────────────────────────────────────────────────────────────┤
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐         │
│  │ ClassModel  │  │ QAVisitModel│  │ Other Models│         │
│  │  - save()   │  │  - save()   │  │             │         │
│  │  - update() │  │  - find()   │  │             │         │
│  └──────┬──────┘  └──────┬──────┘  └──────┬──────┘         │
│         │                │                │                 │
├─────────┴────────────────┴────────────────┴─────────────────┤
│                   DATABASE SERVICE LAYER                     │
├─────────────────────────────────────────────────────────────┤
│  ┌─────────────────────────────────────────────────────┐    │
│  │     DatabaseService (PDO Singleton)                 │    │
│  │  - query()    - prepare()    - beginTransaction()   │    │
│  └────────────────────────┬────────────────────────────┘    │
│                           │                                 │
├───────────────────────────┼─────────────────────────────────┤
│                   DATA STORE                                 │
│  ┌────────────────────────────────────────────────────┐     │
│  │          PostgreSQL Database (External)            │     │
│  │  Table: classes (45+ fields including JSONB)       │     │
│  │  - class_code VARCHAR(50)                          │     │
│  │  - learner_ids JSONB, schedule_data JSONB, etc.    │     │
│  └────────────────────────────────────────────────────┘     │
└─────────────────────────────────────────────────────────────┘
```

### Component Responsibilities

| Component | Responsibility | Current Implementation |
|-----------|----------------|------------------------|
| **JavaScript (class-types.js)** | Client-side form handling, timestamp-based code generation | Vanilla JavaScript, AJAX via wp.ajax.post |
| **ClassController** | AJAX endpoint routing, form data processing, response formatting | Static methods, WordPress nonce security |
| **ClassModel** | Business logic, data validation, database persistence | Active Record pattern, PDO prepared statements |
| **DatabaseService** | Connection management, transaction handling, query execution | Singleton pattern, PDO wrapper for PostgreSQL |
| **PostgreSQL** | Persistent storage, ACID compliance, JSONB document storage | External DigitalOcean managed database |

## Target Architecture: Server-Side Code Generation

### Modified System Structure

```
CLIENT REQUEST FLOW:
┌─────────────────────────────────────────────────────────────┐
│ 1. User submits form (class-capture.js)                     │
│    POST data: client_id, class_type, class_subject, etc.    │
│    (NO class_code in POST — server will generate)           │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ↓
┌─────────────────────────────────────────────────────────────┐
│ 2. WordPress AJAX Handler (wp-admin/admin-ajax.php)         │
│    action=save_class → ClassController::saveClassAjax()     │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ↓
┌─────────────────────────────────────────────────────────────┐
│ 3. ClassController::saveClassAjax() [NEW LOGIC]             │
│    a. Verify nonce security                                 │
│    b. Process form data                                     │
│    c. GENERATE SEQUENTIAL CODE ★ NEW ★                      │
│       - Query: SELECT MAX(class_code) WHERE pattern...      │
│       - Extract sequential number                           │
│       - Increment and format new code                       │
│    d. Populate ClassModel with generated code               │
│    e. Call model->save()                                    │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ↓
┌─────────────────────────────────────────────────────────────┐
│ 4. ClassModel::save() [UNCHANGED]                           │
│    INSERT INTO classes (..., class_code, ...) VALUES (...)  │
│    - Transaction wrapped                                    │
│    - Returns class_id on success                            │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ↓
┌─────────────────────────────────────────────────────────────┐
│ 5. Response to Client                                        │
│    JSON: {success: true, class_id: 123, class_code: "..."}  │
│    JavaScript displays generated code to user               │
└─────────────────────────────────────────────────────────────┘
```

### Component Changes

| Component | Current Behavior | New Behavior | Change Type |
|-----------|------------------|--------------|-------------|
| **class-types.js** | Generates timestamp-based code | Receives server-generated code, displays it | REMOVE generation logic |
| **ClassController::saveClassAjax()** | Accepts class_code from POST | Generates sequential code before save | ADD code generation |
| **ClassModel** | Stores whatever class_code provided | Same (no change needed) | UNCHANGED |
| **DatabaseService** | Query executor | Add sequential query method (optional) | ENHANCE (optional) |
| **PostgreSQL classes table** | Stores class_code VARCHAR(50) | Same, add INDEX if not present | OPTIMIZE (optional) |

## Data Flow: Code Generation Process

### Sequential Code Generation Flow

```
STEP 1: Extract Pattern Components
────────────────────────────────────
Input: client_id=11, class_type=REALLL, class_subject=RLN
Pattern: [ClientID]-[ClassType]-[SubjectID]-[Sequential]
Base: "11-REALLL-RLN-"

STEP 2: Query Database for MAX Sequential Number
────────────────────────────────────────────────
SQL:
  SELECT class_code
  FROM classes
  WHERE class_code LIKE '11-REALLL-RLN-%'
  ORDER BY class_code DESC
  LIMIT 1;

Result: "11-REALLL-RLN-0042" (most recent)

STEP 3: Extract and Increment Sequential Number
────────────────────────────────────────────────
Extract: "0042" → 42
Increment: 42 + 1 = 43
Format: str_pad(43, 4, '0', STR_PAD_LEFT) = "0043"

STEP 4: Construct New Code
────────────────────────────
New Code: "11-REALLL-RLN-0043"

STEP 5: Assign to Model and Save
────────────────────────────────
$class->setClassCode("11-REALLL-RLN-0043");
$class->save(); // Persists to database

STEP 6: Return to Client
────────────────────────────
Response: {
  success: true,
  class_id: 456,
  class_code: "11-REALLL-RLN-0043",
  message: "Draft class created successfully."
}
```

### Transaction Safety

```
BEGIN TRANSACTION;
  1. Lock pattern-specific rows (SELECT FOR UPDATE)
  2. Get MAX sequential number for pattern
  3. Generate new code
  4. INSERT new class with generated code
COMMIT TRANSACTION;

Race Condition Prevention:
- Database transaction isolation ensures atomicity
- SELECT FOR UPDATE locks rows during transaction
- Pattern-specific queries reduce lock contention
```

## Architectural Patterns

### Pattern 1: Server-Side Sequential Generation with Database Lock

**What:** Generate sequential codes on server using database-level locking to prevent race conditions

**When to use:**
- Multiple concurrent users creating classes
- Sequential numbering must be guaranteed unique
- Database supports row-level locking (PostgreSQL does)

**Trade-offs:**
- **Pro:** Race-condition safe, guaranteed uniqueness
- **Pro:** Single source of truth (database)
- **Con:** Slight performance overhead from locking
- **Con:** More complex error handling if transaction fails

**Example:**
```php
// In ClassController::saveClassAjax()
public static function generateSequentialClassCode($clientId, $classType, $subjectId) {
    $db = DatabaseService::getInstance();
    $db->beginTransaction();

    try {
        // Lock and get max sequential number for this pattern
        $pattern = "{$clientId}-{$classType}-{$subjectId}-%";
        $sql = "SELECT class_code FROM classes
                WHERE class_code LIKE ?
                ORDER BY class_code DESC
                LIMIT 1
                FOR UPDATE"; // Row lock

        $stmt = $db->query($sql, [$pattern]);
        $lastCode = $stmt->fetch();

        // Extract sequential number or start at 1
        $sequential = 1;
        if ($lastCode) {
            $parts = explode('-', $lastCode['class_code']);
            $sequential = intval(end($parts)) + 1;
        }

        // Format new code
        $newCode = sprintf("%s-%s-%s-%04d",
            $clientId, $classType, $subjectId, $sequential);

        $db->commit();
        return $newCode;

    } catch (\Exception $e) {
        $db->rollback();
        throw new \Exception("Failed to generate class code: " . $e->getMessage());
    }
}
```

### Pattern 2: Graceful Fallback for Code Generation Failure

**What:** If sequential code generation fails, fall back to timestamp-based code to prevent blocking user

**When to use:**
- High availability requirements
- Prefer "mostly sequential" over "always sequential"
- Database connectivity issues possible

**Trade-offs:**
- **Pro:** System remains operational during database issues
- **Pro:** User never sees "failed to create class" due to code generation
- **Con:** May produce non-sequential codes during failures
- **Con:** Requires monitoring to detect fallback usage

**Example:**
```php
public static function generateClassCode($clientId, $classType, $subjectId) {
    try {
        // Attempt sequential generation
        return self::generateSequentialClassCode($clientId, $classType, $subjectId);
    } catch (\Exception $e) {
        error_log("Sequential code generation failed, using timestamp fallback: " . $e->getMessage());

        // Fallback to timestamp-based code
        return self::generateTimestampClassCode($clientId, $classType, $subjectId);
    }
}

private static function generateTimestampClassCode($clientId, $classType, $subjectId) {
    // Legacy format: [ClientID]-[ClassType]-[SubjectID]-[YYYY]-[MM]-[DD]-[HH]-[MM]
    return sprintf("%s-%s-%s-%s",
        $clientId, $classType, $subjectId,
        date('Y-m-d-H-i')
    );
}
```

### Pattern 3: POST Data Sanitization for Code Removal

**What:** Ensure JavaScript cannot override server-generated codes by stripping class_code from POST data

**When to use:** Always, for security and data integrity

**Trade-offs:**
- **Pro:** Prevents client tampering with sequential codes
- **Pro:** Single source of truth (server generates all codes)
- **Con:** None (this is a security best practice)

**Example:**
```php
// In ClassController::saveClassAjax()
$formData = self::processFormData($_POST, $_FILES);

// Remove class_code from form data if present (security)
unset($formData['class_code']);

// Generate code server-side
$classCode = self::generateClassCode(
    $formData['client_id'],
    $formData['class_type'],
    $formData['class_subject']
);

// Assign generated code to model
$formData['class_code'] = $classCode;
```

## Integration Points with Existing Code

### File: `/app/Controllers/ClassController.php`

**Current saveClassAjax() method (lines 652-801):**

Integration point: **After line 695 (processFormData) and before line 705 (database operation)**

```php
// LINE 695: Form data processed
$formData = self::processFormData($_POST, $_FILES);

// ★ INSERT NEW CODE HERE ★
// INTEGRATION POINT: Code generation logic
if (!isset($formData['id']) || empty($formData['id'])) {
    // Only generate code for NEW classes, not updates
    $classCode = self::generateClassCode(
        $formData['client_id'] ?? null,
        $formData['class_type'] ?? null,
        $formData['class_subject'] ?? null
    );
    $formData['class_code'] = $classCode;
}

// LINE 705: Existing database operation continues
try {
    $db = \WeCozaClasses\Services\Database\DatabaseService::getInstance();
    // ... rest of save logic
```

**Required new method in ClassController:**
```php
/**
 * Generate sequential class code
 *
 * @param string $clientId
 * @param string $classType
 * @param string $subjectId
 * @return string Generated class code
 */
private static function generateClassCode($clientId, $classType, $subjectId) {
    // Implementation from Pattern 2 (with fallback)
}
```

### File: `/assets/js/class-types.js`

**Current generateClassCode() function (lines 237-250):**

Two options for migration:

**Option A: Remove entirely (recommended)**
```javascript
// REMOVE lines 237-250
// DELETE function generateClassCode()

// REMOVE lines 121-124 (code assignment logic)
// REMOVE lines 217-227 (regenerateClassCode function)
```

**Option B: Convert to display-only (backward compatible)**
```javascript
// RENAME to displayClassCode()
// Keep only for displaying server-returned codes
function displayClassCode(classCode) {
    const classCodeInput = document.getElementById('class_code');
    if (classCodeInput && classCode) {
        classCodeInput.value = classCode;
        classCodeInput.disabled = true; // Make read-only
    }
}
```

### File: `/app/Models/ClassModel.php`

**No changes required** — Model already has:
- `setClassCode($classCode)` (line 325)
- `getClassCode()` (line 324)
- `class_code` property (line 23)
- INSERT/UPDATE queries include class_code field (lines 154-191, 221-245)

### File: `/assets/js/class-capture.js`

**Integration point: Response handling**

Current success handler needs update to display server-generated code:

```javascript
// After successful save, extract and display code
success: function(response) {
    if (response.success) {
        // Display generated class code if available
        if (response.data.class_code) {
            $('#class_code').val(response.data.class_code);
            $('#class_code').prop('disabled', true);
        }

        // Existing redirect logic
        if (response.data.redirect_url) {
            window.location.href = response.data.redirect_url;
        }
    }
}
```

### File: `/app/Services/Database/DatabaseService.php`

**Optional enhancement: Add helper method for sequential queries**

```php
/**
 * Get next sequential number for a pattern
 *
 * @param string $pattern LIKE pattern (e.g., "11-REALLL-RLN-%")
 * @return int Next sequential number
 */
public function getNextSequential($pattern) {
    $sql = "SELECT class_code FROM classes
            WHERE class_code LIKE ?
            ORDER BY class_code DESC
            LIMIT 1";

    $stmt = $this->query($sql, [$pattern]);
    $result = $stmt->fetch();

    if (!$result) {
        return 1; // Start at 1 if no existing codes
    }

    // Extract sequential number from code
    $parts = explode('-', $result['class_code']);
    $lastSequential = intval(end($parts));

    return $lastSequential + 1;
}
```

## Database Considerations

### Index Optimization

**Current state:** Index exists on `class_code` (line 5818 in schema)
```sql
CREATE INDEX idx_classes_class_code ON public.classes USING btree (class_code);
```

**Recommendation:** Add pattern prefix index for faster LIKE queries
```sql
CREATE INDEX idx_classes_class_code_pattern
ON public.classes (class_code text_pattern_ops);
```

This enables efficient `WHERE class_code LIKE '11-REALLL-RLN-%'` queries.

### Transaction Isolation Level

**Current:** PostgreSQL default is READ COMMITTED
**Recommendation:** Keep default, use explicit row locks (`FOR UPDATE`)

For sequential code generation:
```sql
-- Within transaction, lock rows matching pattern
SELECT class_code FROM classes
WHERE class_code LIKE ?
ORDER BY class_code DESC
LIMIT 1
FOR UPDATE; -- Explicit row lock
```

### Race Condition Testing

Test concurrent class creation:
```sql
-- Simulate two concurrent requests
-- Terminal 1:
BEGIN;
SELECT class_code FROM classes WHERE class_code LIKE '11-REALLL-RLN-%' ORDER BY class_code DESC LIMIT 1 FOR UPDATE;
-- (pause here)

-- Terminal 2:
BEGIN;
SELECT class_code FROM classes WHERE class_code LIKE '11-REALLL-RLN-%' ORDER BY class_code DESC LIMIT 1 FOR UPDATE;
-- (this will WAIT until Terminal 1 commits)

-- Terminal 1:
INSERT INTO classes (class_code, ...) VALUES ('11-REALLL-RLN-0043', ...);
COMMIT;

-- Terminal 2:
-- (now proceeds, will see new row 0043, generates 0044)
INSERT INTO classes (class_code, ...) VALUES ('11-REALLL-RLN-0044', ...);
COMMIT;
```

## Implementation Order

### Phase 1: Backend Code Generation (Core)
1. Add `generateClassCode()` method to ClassController
2. Add `generateSequentialClassCode()` private method with database locking
3. Integrate code generation into `saveClassAjax()` after line 695
4. Add error handling and logging
5. Test with single user creating classes

**Success criteria:** Server generates sequential codes, persists to database

### Phase 2: Frontend Integration (Display)
1. Update `class-capture.js` success handler to display server-generated code
2. Make `class_code` input read-only after generation
3. Remove `generateClassCode()` from `class-types.js` (or convert to display-only)
4. Remove event listeners that trigger client-side generation
5. Test form submission shows generated code

**Success criteria:** User sees sequential code after save, cannot manually edit

### Phase 3: Race Condition Testing (Reliability)
1. Test concurrent class creation (multiple browser tabs)
2. Verify sequential numbers never duplicate
3. Test transaction rollback scenarios
4. Add monitoring/logging for code generation
5. Load test with 10+ concurrent requests

**Success criteria:** No duplicate codes under concurrent load

### Phase 4: Fallback & Error Handling (Resilience)
1. Implement timestamp-based fallback for generation failures
2. Add error logging for fallback usage
3. Test database connection failure scenarios
4. Add monitoring alerts for fallback usage
5. Document recovery procedures

**Success criteria:** System remains operational during database issues

## Anti-Patterns

### Anti-Pattern 1: Client-Side Sequential Generation

**What people do:** Generate sequential codes in JavaScript, relying on client-reported "last number"

**Why it's wrong:**
- Race conditions: Two users see same "last number", create duplicate codes
- Security: Client can manipulate sequential numbers
- Network delays cause out-of-order codes

**Do this instead:** Server-side generation with database locks (Pattern 1)

### Anti-Pattern 2: UUID/Timestamp Without Sequential Option

**What people do:** Use only UUIDs or timestamps to avoid complexity

**Why it's wrong:**
- Loss of business requirement (sequential numbering for auditing)
- Users want human-readable sequential codes
- Cannot reconstruct order without timestamps

**Do this instead:** Server-side sequential generation with timestamp fallback (Pattern 2)

### Anti-Pattern 3: Application-Level Locking (Mutex Files)

**What people do:** Use file-based locks or application-level mutexes for sequential generation

**Why it's wrong:**
- Doesn't scale across multiple PHP-FPM workers
- File locking unreliable on network filesystems
- Lock files can become stale (orphaned locks)
- Reinventing database transaction capabilities

**Do this instead:** Database row-level locks with `SELECT FOR UPDATE` (Pattern 1)

### Anti-Pattern 4: Post-Save Code Update

**What people do:**
```php
// WRONG: Save without code, then update with generated code
$class->save(); // Saves with null or temporary code
$code = generateCode();
$class->setClassCode($code);
$class->update(); // Second database operation
```

**Why it's wrong:**
- Two database operations instead of one
- Window for race condition between save and update
- Class briefly exists in invalid state (missing code)

**Do this instead:** Generate code before save, include in initial INSERT

```php
// RIGHT: Generate code, then save once
$code = generateSequentialCode();
$class->setClassCode($code);
$class->save(); // Single database operation with code
```

## Scaling Considerations

| Scale | Architecture Adjustments |
|-------|--------------------------|
| **0-100 concurrent users** | Pattern 1 (database locks) sufficient. Single database transaction per save. No caching needed. |
| **100-1,000 concurrent users** | Add pattern prefix index (`text_pattern_ops`). Monitor lock wait times. Consider pattern-specific sharding (different patterns on different database connections). |
| **1,000+ concurrent users** | Implement code pre-allocation: Reserve blocks of sequential numbers (e.g., server reserves 0001-0100, distributes without database query). Requires distributed cache (Redis) for reservation tracking. |

### Scaling Priorities

1. **First bottleneck:** Database lock contention on sequential code queries
   - **Detection:** Slow `SELECT FOR UPDATE` queries in logs, increased lock wait times
   - **Fix:** Add `text_pattern_ops` index, consider pattern-based sharding

2. **Second bottleneck:** Transaction throughput for class creation
   - **Detection:** Queue buildup, timeout errors, HTTP 504 responses
   - **Fix:** Read replica for SELECT queries, write operations remain on primary

### Performance Monitoring

**Queries to monitor:**
```sql
-- Check lock wait times
SELECT * FROM pg_stat_activity
WHERE wait_event_type = 'Lock'
AND query LIKE '%class_code%';

-- Check slow queries
SELECT query, mean_exec_time, calls
FROM pg_stat_statements
WHERE query LIKE '%class_code%'
ORDER BY mean_exec_time DESC;
```

**Application metrics:**
- Code generation time (should be <50ms)
- Fallback usage rate (should be <0.1%)
- Duplicate code errors (should be 0)

## Sources

**Codebase Analysis:**
- `/app/Controllers/ClassController.php` (lines 652-801): Current saveClassAjax implementation
- `/app/Models/ClassModel.php` (lines 324-326): Class code getter/setter
- `/assets/js/class-types.js` (lines 237-250): Current client-side generation
- `/app/Services/Database/DatabaseService.php`: PDO singleton implementation
- `schema/wecoza_db_schema_bu_oct_22.sql` (line 1673): class_code field definition

**PostgreSQL Documentation:**
- Row-level locking: https://www.postgresql.org/docs/current/explicit-locking.html#LOCKING-ROWS
- Text pattern operators: https://www.postgresql.org/docs/current/indexes-opclass.html
- Transaction isolation: https://www.postgresql.org/docs/current/transaction-iso.html

**WordPress Standards:**
- AJAX handling: https://developer.wordpress.org/plugins/javascript/ajax/
- Nonce verification: https://developer.wordpress.org/apis/security/nonces/

---
*Architecture research for: Server-side sequential class code generation in WordPress plugin with external PostgreSQL*
*Researched: 2026-01-22*
*Confidence: HIGH (based on direct codebase analysis)*
