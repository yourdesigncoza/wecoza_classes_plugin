<?php

/**
 * Class Capture Form View
 *
 * This view file renders a comprehensive form for creating and managing training classes in the WeCoza Classes Plugin.
 * It follows the MVC architecture pattern where this file (View) is responsible only for presentation,
 * while the ClassController handles the business logic and data processing.
 *
 * The form includes multiple sections:
 * - Basic Details: Client and site selection
 * - Class Info: Type, start date, subjects
 * - Date History: Managing stop/restart dates for classes
 * - Funding & Exam Details: SETA funding and exam information
 * - Exam Learners: Selection of learners taking exams (conditionally displayed)
 * - Class Notes & QA: Quality assurance information
 * - Assignments & Dates: Staff assignments and important dates
 *
 * This form uses various view helpers (from app/Helpers/ViewHelpers.php) to generate consistent UI elements:
 * - select_dropdown(): For dropdown menus
 * - form_input(): For input fields
 * - form_textarea(): For textarea fields
 * - form_row(): For complex form rows
 * - section_divider(): For visual section separators
 * - section_header(): For section titles
 * - button(): For form buttons
 *
 * The form includes client-side validation using Bootstrap's validation classes and custom JavaScript.
 * All validation is handled on the frontend for better user experience.
 *
 * JavaScript functionality is provided by class-capture.js, which handles:
 * - Dynamic form field behavior (conditional fields, multi-select)
 * - Form submission via AJAX
 * - Validation feedback
 *
 * @var array $data View data passed from ClassController containing:
 *   - clients: Array of client data with 'id' and 'name' keys
 *   - sites: Associative array of sites grouped by client ID
 *   - agents: Array of agent data with 'id' and 'name' keys
 *   - supervisors: Array of supervisor data with 'id' and 'name' keys
 *   - learners: Array of learner data with 'id' and 'name' keys
 *   - setas: Array of SETA data with 'id' and 'name' keys
 *   - products: Array of product/course data with 'id', 'name', and 'learning_area' keys
 *   - class_types: Array of class type data with 'id' and 'name' keys
 *   - yes_no_options: Array of Yes/No options with 'id' and 'name' keys
 *   - redirect_url: URL to redirect to after successful form submission
 *
 * @see \WeCozaClasses\Controllers\ClassController::captureClassShortcode() For the controller method that renders this view
 * @see \WeCozaClasses\Models\ClassModel For the data model that stores class information
 */

if ($data['mode'] === 'update'): 
   require_once WECOZA_CLASSES_APP_PATH . '/Views/components/class-capture-partials/update-class.php';
else:
   require_once WECOZA_CLASSES_APP_PATH . '/Views/components/class-capture-partials/create-class.php';
endif; ?>
