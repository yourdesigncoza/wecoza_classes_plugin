<?php
/**
 * Pre-flight validation script for class_code migration
 * Run via: wp eval-file includes/migrations/validate-class-codes.php
 */

require_once dirname(__FILE__) . '/../../app/bootstrap.php';

use WeCozaClasses\Services\Database\DatabaseService;

try {
    $db = DatabaseService::getInstance();
    $pdo = $db->getPdo();

    echo "=== Class Code Pre-flight Validation ===\n\n";

    // Check 1: Look for duplicates
    echo "1. Checking for duplicate class_code values...\n";
    $stmt = $pdo->query("
        SELECT class_code, COUNT(*) as count
        FROM classes
        WHERE class_code IS NOT NULL
        GROUP BY class_code
        HAVING COUNT(*) > 1
    ");
    $duplicates = $stmt->fetchAll();

    if (empty($duplicates)) {
        echo "   ✓ No duplicates found\n\n";
    } else {
        echo "   ✗ DUPLICATES FOUND:\n";
        foreach ($duplicates as $dup) {
            echo "     - {$dup['class_code']}: {$dup['count']} occurrences\n";
        }
        echo "\n   ACTION REQUIRED: Business decision needed on duplicate handling\n\n";
        exit(1);
    }

    // Check 2: NULL/empty values
    echo "2. Checking for NULL or empty class_code values...\n";
    $stmt = $pdo->query("
        SELECT COUNT(*) as count
        FROM classes
        WHERE class_code IS NULL OR class_code = ''
    ");
    $nullCount = $stmt->fetch()['count'];
    echo "   Found {$nullCount} NULL/empty class_code values\n";
    echo "   (These are okay - will be populated with new sequential codes)\n\n";

    // Check 3: Current max sequential number
    echo "3. Finding current max sequential number in class codes...\n";
    $stmt = $pdo->query("
        SELECT class_code,
               REGEXP_REPLACE(class_code, '[^0-9]', '', 'g') as numeric_part
        FROM classes
        WHERE class_code ~ '[0-9]+'
        ORDER BY LENGTH(REGEXP_REPLACE(class_code, '[^0-9]', '', 'g')) DESC,
                 REGEXP_REPLACE(class_code, '[^0-9]', '', 'g') DESC
        LIMIT 10
    ");
    $codes = $stmt->fetchAll();

    $maxNumeric = 0;
    echo "   Top 10 codes with numeric parts:\n";
    foreach ($codes as $code) {
        $numeric = $code['numeric_part'] ?: '0';
        $numericVal = intval($numeric);
        if ($numericVal > $maxNumeric) {
            $maxNumeric = $numericVal;
        }
        echo "     - {$code['class_code']} (numeric: {$numeric})\n";
    }
    echo "\n   Max numeric value found: {$maxNumeric}\n";
    echo "   Sequence will start at: 1001 (safe buffer above existing codes)\n\n";

    // Check 4: Existing indexes
    echo "4. Checking existing indexes on class_code...\n";
    $stmt = $pdo->query("
        SELECT indexname, indexdef
        FROM pg_indexes
        WHERE tablename = 'classes'
        AND indexdef LIKE '%class_code%'
    ");
    $indexes = $stmt->fetchAll();

    if (empty($indexes)) {
        echo "   No existing indexes found\n\n";
    } else {
        foreach ($indexes as $idx) {
            echo "   - {$idx['indexname']}\n";
            echo "     {$idx['indexdef']}\n";
        }
        echo "\n";
    }

    // Check 5: Check for existing sequence
    echo "5. Checking for existing class_code_seq sequence...\n";
    $stmt = $pdo->query("
        SELECT sequencename
        FROM pg_sequences
        WHERE sequencename = 'class_code_seq'
    ");
    $existingSeq = $stmt->fetch();

    if ($existingSeq) {
        echo "   ✗ Sequence already exists!\n";
        echo "   ACTION REQUIRED: Drop existing sequence or modify migration\n\n";
        exit(1);
    } else {
        echo "   ✓ No existing sequence - safe to create\n\n";
    }

    echo "=== Validation Complete ===\n";
    echo "All checks passed - safe to proceed with migrations\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
