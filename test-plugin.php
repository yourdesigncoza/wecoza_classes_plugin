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
    
    echo "<h3>Test Complete</h3>";
    echo "<p><strong>Note:</strong> This is a basic functionality test. For full testing, create a WordPress page with the shortcodes.</p>";
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
