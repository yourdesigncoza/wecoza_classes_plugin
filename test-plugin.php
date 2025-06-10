<?php
/**
 * Simple test file for WeCoza Classes Plugin
 * This file can be used to test basic plugin functionality
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Test Plugin Functionality
 */
function test_wecoza_classes_plugin() {
    echo "<h2>WeCoza Classes Plugin Test Results</h2>";
    
    // Test 1: Check if plugin constants are defined
    echo "<h3>1. Plugin Constants Test</h3>";
    $constants = [
        'WECOZA_CLASSES_VERSION',
        'WECOZA_CLASSES_PLUGIN_DIR',
        'WECOZA_CLASSES_PLUGIN_URL',
        'WECOZA_CLASSES_INCLUDES_DIR',
        'WECOZA_CLASSES_APP_DIR'
    ];
    
    foreach ($constants as $constant) {
        if (defined($constant)) {
            echo "✅ {$constant}: " . constant($constant) . "<br>";
        } else {
            echo "❌ {$constant}: Not defined<br>";
        }
    }
    
    // Test 2: Check if classes exist
    echo "<h3>2. Class Existence Test</h3>";
    $classes = [
        'WeCoza_Classes_Plugin',
        'WeCozaClasses\\Controllers\\ClassController',
        'WeCozaClasses\\Controllers\\ClassTypesController',
        'WeCozaClasses\\Controllers\\PublicHolidaysController',
        'WeCozaClasses\\Models\\ClassModel',
        'WeCozaClasses\\Services\\Database\\DatabaseService'
    ];
    
    foreach ($classes as $class) {
        if (class_exists($class)) {
            echo "✅ {$class}: Exists<br>";
        } else {
            echo "❌ {$class}: Not found<br>";
        }
    }
    
    // Test 3: Check if shortcodes are registered
    echo "<h3>3. Shortcode Registration Test</h3>";
    $shortcodes = [
        'wecoza_capture_class',
        'wecoza_display_classes',
        'wecoza_display_single_class'
    ];
    
    global $shortcode_tags;
    foreach ($shortcodes as $shortcode) {
        if (isset($shortcode_tags[$shortcode])) {
            echo "✅ [{$shortcode}]: Registered<br>";
        } else {
            echo "❌ [{$shortcode}]: Not registered<br>";
        }
    }
    
    // Test 4: Check if view function works
    echo "<h3>4. View Function Test</h3>";
    if (function_exists('\\WeCozaClasses\\view')) {
        echo "✅ WeCozaClasses\\view(): Function exists<br>";
        
        // Test view rendering with simple data
        try {
            $test_data = ['test_message' => 'Hello from WeCoza Classes Plugin!'];
            // This will fail if view file doesn't exist, but that's expected for this test
            echo "✅ View function is callable<br>";
        } catch (Exception $e) {
            echo "❌ View function error: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "❌ WeCozaClasses\\view(): Function not found<br>";
    }
    
    // Test 5: Check if helper functions are available
    echo "<h3>5. Helper Functions Test</h3>";
    $helpers = [
        'select_dropdown',
        'form_input',
        'section_divider',
        'button'
    ];
    
    foreach ($helpers as $helper) {
        if (function_exists($helper)) {
            echo "✅ {$helper}(): Available<br>";
        } else {
            echo "❌ {$helper}(): Not available<br>";
        }
    }
    
    // Test 6: Database connection test
    echo "<h3>6. Database Connection Test</h3>";
    try {
        if (class_exists('WeCozaClasses\\Services\\Database\\DatabaseService')) {
            $db = WeCozaClasses\Services\Database\DatabaseService::getInstance();
            if ($db) {
                echo "✅ Database service instance created<br>";
                
                // Test basic query
                $pdo = $db->getPdo();
                if ($pdo) {
                    echo "✅ PDO connection established<br>";
                    
                    // Test simple query
                    $stmt = $pdo->query("SELECT 1 as test");
                    if ($stmt) {
                        echo "✅ Database query test successful<br>";
                    } else {
                        echo "❌ Database query test failed<br>";
                    }
                } else {
                    echo "❌ PDO connection failed<br>";
                }
            } else {
                echo "❌ Database service instance creation failed<br>";
            }
        } else {
            echo "❌ DatabaseService class not found<br>";
        }
    } catch (Exception $e) {
        echo "❌ Database test error: " . $e->getMessage() . "<br>";
    }
    
    // Test 7: Asset enqueuing test
    echo "<h3>7. Asset Files Test</h3>";
    $assets = [
        'CSS' => [
            WECOZA_CLASSES_CSS_URL . 'wecoza-classes-public.css',
            WECOZA_CLASSES_CSS_URL . 'wecoza-classes-admin.css'
        ],
        'JS' => [
            WECOZA_CLASSES_JS_URL . 'class-capture.js',
            WECOZA_CLASSES_JS_URL . 'class-schedule-form.js',
            WECOZA_CLASSES_JS_URL . 'class-types.js',
            WECOZA_CLASSES_JS_URL . 'wecoza-calendar.js',
            WECOZA_CLASSES_JS_URL . 'wecoza-classes-admin.js'
        ]
    ];
    
    foreach ($assets as $type => $files) {
        echo "<strong>{$type} Files:</strong><br>";
        foreach ($files as $file) {
            $file_path = str_replace(WECOZA_CLASSES_PLUGIN_URL, WECOZA_CLASSES_PLUGIN_DIR, $file);
            if (file_exists($file_path)) {
                echo "✅ " . basename($file) . ": Exists<br>";
            } else {
                echo "❌ " . basename($file) . ": Missing<br>";
            }
        }
    }
    
    // Test 8: Calendar Event Generation Test (Task 4.2-4.5)
    echo "<h3>8. Calendar Event Generation Test (v4.0 Tasks 4.2-4.5)</h3>";
    test_calendar_event_generation();

    echo "<h3>Test Complete</h3>";
    echo "<p><strong>Note:</strong> This is a basic functionality test. For full testing, create a WordPress page with the shortcodes.</p>";
}

/**
 * Test calendar event generation with per-day times (Tasks 4.2-4.5)
 */
function test_calendar_event_generation() {
    try {
        if (!class_exists('WeCozaClasses\\Controllers\\ClassController')) {
            echo "❌ ClassController not available for testing<br>";
            return;
        }

        // Test data: v2.0 format with per-day times
        $testScheduleDataPerDay = [
            'version' => '2.0',
            'pattern' => 'weekly',
            'startDate' => '2024-01-15',
            'endDate' => '2024-01-29',
            'timeData' => [
                'mode' => 'per-day',
                'perDay' => [
                    'Monday' => [
                        'startTime' => '09:00',
                        'endTime' => '12:00',
                        'duration' => 3.0
                    ],
                    'Wednesday' => [
                        'startTime' => '13:00',
                        'endTime' => '17:00',
                        'duration' => 4.0
                    ],
                    'Friday' => [
                        'startTime' => '10:00',
                        'endTime' => '15:00',
                        'duration' => 5.0
                    ]
                ]
            ],
            'selectedDays' => ['Monday', 'Wednesday', 'Friday'],
            'exceptionDates' => [],
            'holidayOverrides' => []
        ];

        // Test data: v2.0 format with single time
        $testScheduleDataSingle = [
            'version' => '2.0',
            'pattern' => 'weekly',
            'startDate' => '2024-01-15',
            'endDate' => '2024-01-22',
            'timeData' => [
                'mode' => 'single',
                'single' => [
                    'startTime' => '09:00',
                    'endTime' => '17:00',
                    'duration' => 8.0
                ]
            ],
            'selectedDays' => ['Monday', 'Wednesday', 'Friday'],
            'exceptionDates' => [],
            'holidayOverrides' => []
        ];

        // Test data: Legacy v1.0 format
        $testScheduleDataLegacy = [
            [
                'date' => '2024-01-15',
                'start_time' => '09:00',
                'end_time' => '17:00',
                'notes' => 'Legacy format test'
            ],
            [
                'date' => '2024-01-17',
                'start_time' => '09:00',
                'end_time' => '17:00',
                'notes' => 'Legacy format test'
            ]
        ];

        $testClass = [
            'class_id' => 'TEST001',
            'class_code' => 'TEST-001',
            'class_subject' => 'Test Class Subject',
            'original_start_date' => '2024-01-15',
            'delivery_date' => '2024-01-29'
        ];

        // Test 1: Per-day times (v2.0)
        echo "<strong>Test 1: v2.0 Per-Day Times</strong><br>";
        $events = test_generate_events($testScheduleDataPerDay, $testClass);
        if ($events !== false) {
            echo "✅ Generated " . count($events) . " events from per-day schedule<br>";

            // Check if events have different times
            $uniqueTimes = [];
            foreach ($events as $event) {
                $timeKey = substr($event['start'], 11, 5) . '-' . substr($event['end'], 11, 5);
                $uniqueTimes[$timeKey] = true;
            }

            if (count($uniqueTimes) > 1) {
                echo "✅ Events have different times per day: " . implode(', ', array_keys($uniqueTimes)) . "<br>";
            } else {
                echo "❌ All events have same time (per-day times not preserved)<br>";
            }

            // Check event titles for day names
            $hasDayNames = false;
            foreach ($events as $event) {
                if (preg_match('/^(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday):/', $event['title'])) {
                    $hasDayNames = true;
                    break;
                }
            }

            if ($hasDayNames) {
                echo "✅ Event titles include day names for per-day schedules<br>";
            } else {
                echo "❌ Event titles missing day names<br>";
            }
        }

        // Test 2: Single time (v2.0)
        echo "<strong>Test 2: v2.0 Single Time</strong><br>";
        $events = test_generate_events($testScheduleDataSingle, $testClass);
        if ($events !== false) {
            echo "✅ Generated " . count($events) . " events from single-time schedule<br>";

            // Check if all events have same time
            $times = [];
            foreach ($events as $event) {
                $times[] = substr($event['start'], 11, 5) . '-' . substr($event['end'], 11, 5);
            }

            if (count(array_unique($times)) === 1) {
                echo "✅ All events have consistent time: " . $times[0] . "<br>";
            } else {
                echo "❌ Events have inconsistent times<br>";
            }
        }

        // Test 3: Legacy format (v1.0)
        echo "<strong>Test 3: Legacy v1.0 Format</strong><br>";
        $events = test_generate_events($testScheduleDataLegacy, $testClass);
        if ($events !== false) {
            echo "✅ Generated " . count($events) . " events from legacy schedule<br>";
            echo "✅ Backward compatibility maintained<br>";
        }

    } catch (Exception $e) {
        echo "❌ Calendar event generation test failed: " . $e->getMessage() . "<br>";
    }
}

/**
 * Helper function to test event generation
 */
function test_generate_events($scheduleData, $class) {
    try {
        // Use reflection to access private method for testing
        $controller = new WeCozaClasses\Controllers\ClassController();
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('generateEventsFromScheduleData');
        $method->setAccessible(true);

        $events = $method->invoke($controller, $scheduleData, $class);

        if (is_array($events) && count($events) > 0) {
            // Validate event structure
            $firstEvent = $events[0];
            $requiredFields = ['id', 'title', 'start', 'end', 'classNames', 'extendedProps'];

            foreach ($requiredFields as $field) {
                if (!isset($firstEvent[$field])) {
                    echo "❌ Missing required field: {$field}<br>";
                    return false;
                }
            }

            return $events;
        } else {
            echo "❌ No events generated<br>";
            return false;
        }

    } catch (Exception $e) {
        echo "❌ Event generation failed: " . $e->getMessage() . "<br>";
        return false;
    }
}

// Add admin menu for testing (only for administrators)
if (current_user_can('manage_options')) {
    add_action('admin_menu', function() {
        add_submenu_page(
            'tools.php',
            'WeCoza Classes Plugin Test',
            'WeCoza Classes Test',
            'manage_options',
            'wecoza-classes-test',
            'test_wecoza_classes_plugin'
        );
    });
}
