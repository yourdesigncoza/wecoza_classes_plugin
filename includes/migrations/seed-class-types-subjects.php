<?php
/**
 * Migration: Create and seed class_types and class_type_subjects tables
 * Moves hardcoded data from ClassTypesController to PostgreSQL
 *
 * @package WeCozaClasses
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create tables and seed class types + subjects data
 *
 * @return bool
 */
function wecoza_classes_seed_class_types_subjects(): bool {
    try {
        $db = \WeCozaClasses\Services\Database\DatabaseService::getInstance();

        $db->beginTransaction();

        // Create class_types table
        $db->exec("
            CREATE TABLE IF NOT EXISTS public.class_types (
                class_type_id SERIAL PRIMARY KEY,
                class_type_code VARCHAR(50) UNIQUE NOT NULL,
                class_type_name VARCHAR(100) NOT NULL,
                subject_selection_mode VARCHAR(20) NOT NULL
                    CHECK (subject_selection_mode IN ('own', 'all_subjects', 'progression')),
                progression_total_hours INTEGER DEFAULT NULL,
                display_order INTEGER NOT NULL DEFAULT 0,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),
                updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW()
            )
        ");

        $db->exec("CREATE INDEX IF NOT EXISTS idx_class_types_code ON public.class_types(class_type_code)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_class_types_active ON public.class_types(is_active)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_class_types_display_order ON public.class_types(display_order)");

        // Create class_type_subjects table
        $db->exec("
            CREATE TABLE IF NOT EXISTS public.class_type_subjects (
                class_type_subject_id SERIAL PRIMARY KEY,
                class_type_id INTEGER NOT NULL REFERENCES public.class_types(class_type_id) ON DELETE CASCADE,
                subject_code VARCHAR(50) NOT NULL,
                subject_name VARCHAR(100) NOT NULL,
                subject_duration INTEGER NOT NULL,
                display_order INTEGER NOT NULL DEFAULT 0,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),
                updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),
                UNIQUE(class_type_id, subject_code)
            )
        ");

        $db->exec("CREATE INDEX IF NOT EXISTS idx_cts_type_id ON public.class_type_subjects(class_type_id)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_cts_code ON public.class_type_subjects(subject_code)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_cts_active ON public.class_type_subjects(is_active)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_cts_display ON public.class_type_subjects(class_type_id, display_order)");

        // Seed class types
        // [code, name, mode, progression_hours, display_order]
        $types = [
            ['AET',   'AET Communication & Numeracy', 'own',          null, 1],
            ['REALLL','REALLL',                       'own',          null, 2],
            ['SOFT',  'Soft Skill Courses',           'own',          null, 3],
            ['GETC',  'GETC AET',                     'progression',  564,  4],
            ['BA2',   'Business Admin NQF 2',         'progression',  520,  5],
            ['BA3',   'Business Admin NQF 3',         'progression',  472,  6],
            ['BA4',   'Business Admin NQF 4',         'progression',  584,  7],
            ['WALK',  'Walk Package',                 'all_subjects', null, 8],
            ['HEXA',  'Hexa Packages',                'all_subjects', null, 9],
            ['RUN',   'Run Packages',                 'all_subjects', null, 10],
        ];

        $typeStmt = $db->prepare("
            INSERT INTO public.class_types
                (class_type_code, class_type_name, subject_selection_mode, progression_total_hours, display_order)
            VALUES (?, ?, ?, ?, ?)
            ON CONFLICT (class_type_code) DO NOTHING
        ");

        foreach ($types as $t) {
            $typeStmt->execute($t);
        }

        // Seed subjects: [code, name, duration, display_order]
        $subjects = [
            'AET' => [
                ['COMM',     'Communication (separate)',            120, 1],
                ['NUM',      'Numeracy (separate)',                 120, 2],
                ['COMM_NUM', 'Communication & Numeracy (both)',     240, 3],
            ],
            'GETC' => [
                ['CL4',   'Communication level 4',                  120, 1],
                ['NL4',   'Numeracy level 4',                       120, 2],
                ['LO4',   'Life Orientation level 4',                90, 3],
                ['HSS4',  'Human & Social Sciences level 4',         80, 4],
                ['EMS4',  'Economic & Management Sciences level 4',  94, 5],
                ['NS4',   'Natural Sciences level 4',                60, 6],
                ['SMME4', 'Small Micro Medium Enterprises level 4',  60, 7],
            ],
            'REALLL' => [
                ['RLC', 'Communication', 160, 1],
                ['RLN', 'Numeracy',      160, 2],
                ['RLF', 'Finance',        40, 3],
            ],
            'BA2' => [
                ['BA2LP9',  'LP9',  80, 1],
                ['BA2LP10', 'LP10', 64, 2],
                ['BA2LP1',  'LP1',  72, 3],
                ['BA2LP2',  'LP2',  56, 4],
                ['BA2LP3',  'LP3',  40, 5],
                ['BA2LP4',  'LP4',  20, 6],
                ['BA2LP5',  'LP5',  56, 7],
                ['BA2LP6',  'LP6',  60, 8],
                ['BA2LP7',  'LP7',  40, 9],
                ['BA2LP8',  'LP8',  32, 10],
            ],
            'BA3' => [
                ['BA3LP2',  'LP2',  52, 1],
                ['BA3LP4',  'LP4',  40, 2],
                ['BA3LP5',  'LP5',  36, 3],
                ['BA3LP6',  'LP6',  44, 4],
                ['BA3LP1',  'LP1',  60, 5],
                ['BA3LP7',  'LP7',  40, 6],
                ['BA3LP8',  'LP8',  44, 7],
                ['BA3LP9',  'LP9',  28, 8],
                ['BA3LP10', 'LP10', 48, 9],
                ['BA3LP11', 'LP11', 36, 10],
                ['BA3LP3',  'LP3',  44, 11],
            ],
            'BA4' => [
                ['BA4LP2', 'LP2', 104, 1],
                ['BA4LP3', 'LP3',  80, 2],
                ['BA4LP4', 'LP4',  64, 3],
                ['BA4LP1', 'LP1',  88, 4],
                ['BA4LP6', 'LP6',  84, 5],
                ['BA4LP5', 'LP5',  76, 6],
                ['BA4LP7', 'LP7',  88, 7],
            ],
            'SOFT' => [
                ['IPC',   'Introduction to Computers',      20, 1],
                ['EQ',    'Email Etiquette',                  6, 2],
                ['TM',    'Time Management',                 12, 3],
                ['SS',    'Supervisory Skills',              40, 4],
                ['EEPDL', 'EEP Digital Literacy',            40, 5],
                ['EEPPF', 'EEP Personal Finance',            40, 6],
                ['EEPWI', 'EEP Workplace Intelligence',      40, 7],
                ['EEPEI', 'EEP Emotional Intelligence',      40, 8],
                ['EEPBI', 'EEP Business Intelligence',       40, 9],
            ],
        ];

        $subStmt = $db->prepare("
            INSERT INTO public.class_type_subjects
                (class_type_id, subject_code, subject_name, subject_duration, display_order)
            SELECT ct.class_type_id, ?, ?, ?, ?
            FROM public.class_types ct
            WHERE ct.class_type_code = ?
            ON CONFLICT (class_type_id, subject_code) DO NOTHING
        ");

        foreach ($subjects as $typeCode => $subs) {
            foreach ($subs as $s) {
                $subStmt->execute([$s[0], $s[1], $s[2], $s[3], $typeCode]);
            }
        }

        $db->commit();
        error_log('WeCoza Classes: class_types and class_type_subjects seeded successfully');
        return true;

    } catch (\Exception $e) {
        if (isset($db) && $db->inTransaction()) {
            $db->rollback();
        }
        error_log('WeCoza Classes: Error seeding class types/subjects: ' . $e->getMessage());
        return false;
    }
}

/**
 * Rollback: drop the tables
 *
 * @return bool
 */
function wecoza_classes_rollback_class_types_subjects(): bool {
    try {
        $db = \WeCozaClasses\Services\Database\DatabaseService::getInstance();
        $db->exec("DROP TABLE IF EXISTS public.class_type_subjects CASCADE");
        $db->exec("DROP TABLE IF EXISTS public.class_types CASCADE");
        error_log('WeCoza Classes: class_types/class_type_subjects tables dropped');
        return true;
    } catch (\Exception $e) {
        error_log('WeCoza Classes: Error dropping tables: ' . $e->getMessage());
        return false;
    }
}
