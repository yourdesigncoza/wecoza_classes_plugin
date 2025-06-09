/**
 * Class Schedule Form JavaScript for WeCoza Classes Plugin
 *
 * Handles the client-side functionality for the class schedule form.
 * Extracted from WeCoza theme for standalone plugin
 */
console.log('class-schedule-form.js: File loading started');
(function($) {
    'use strict';
    console.log('class-schedule-form.js: Inside IIFE, jQuery available:', typeof $ !== 'undefined');

    // Holiday override data
    var holidayOverrides = {};

    /**
     * Initialize the class schedule form
     */
    function initClassScheduleForm() {
        console.log('class-schedule-form.js: initClassScheduleForm() called');

        // Initialize schedule pattern selection
        console.log('class-schedule-form.js: Initializing schedule pattern selection...');
        initSchedulePatternSelection();

        // Initialize time selection and duration calculation
        console.log('class-schedule-form.js: Initializing time selection...');
        initTimeSelection();

        // Initialize per-day time controls based on current selection
        console.log('class-schedule-form.js: Initializing per-day time controls...');
        updatePerDayTimeControls();

        // Initialize exception dates
        console.log('class-schedule-form.js: Initializing exception dates...');
        initExceptionDates();

        // Initialize holiday overrides
        console.log('class-schedule-form.js: Initializing holiday overrides...');
        initHolidayOverrides();

        // Initialize schedule data updates
        console.log('class-schedule-form.js: Initializing schedule data updates...');
        initScheduleDataUpdates();

        console.log('class-schedule-form.js: Initialization complete');
    }

    /**
     * Initialize schedule pattern selection
     */
    function initSchedulePatternSelection() {
        const $schedulePattern = $('#schedule_pattern');
        const $daySelection = $('#day-selection-container');
        const $dayOfMonth = $('#day-of-month-container');

        $schedulePattern.on('change', function() {
            const pattern = $(this).val();

            // Reset visibility
            $daySelection.addClass('d-none');
            $dayOfMonth.addClass('d-none');

            // Show appropriate fields based on pattern
            if (pattern === 'weekly' || pattern === 'biweekly') {
                $daySelection.removeClass('d-none');
                $('#schedule_day_of_month').removeAttr('required');

                // Ensure at least one day is selected for validation
                validateDaySelection();

                // Update per-day time controls based on current day selection
                updatePerDayTimeControls();
            } else if (pattern === 'monthly') {
                $dayOfMonth.removeClass('d-none');
                $('#schedule_day_of_month').attr('required', 'required');

                // For monthly pattern, always show single time controls
                // since monthly scheduling doesn't use multiple days
                resetToSingleTimeControls();
            } else {
                // For custom or no pattern, reset to single time controls
                resetToSingleTimeControls();
            }

            // Update schedule data
            updateScheduleData();

            // Check for holidays that conflict with the new pattern
            const startDate = $('#schedule_start_date').val();
            const endDate = $('#schedule_end_date').val();
            if (startDate) {
                checkForHolidays(startDate, endDate);
            }

            // Recalculate end date when pattern changes
            recalculateEndDate();
        });

        // Initialize day selection buttons
        $('#select-all-days').on('click', function() {
            $('.schedule-day-checkbox').prop('checked', true);
            validateDaySelection();
            updatePerDayTimeControls(); // Add conditional display logic
            updateScheduleData();
            restrictStartDateBySelectedDays();
            recalculateEndDate();
        });

        $('#clear-all-days').on('click', function() {
            $('.schedule-day-checkbox').prop('checked', false);
            validateDaySelection();
            updatePerDayTimeControls(); // Add conditional display logic
            updateScheduleData();
        });

        // Handle day checkbox changes - using event delegation in case checkboxes are loaded dynamically
        $(document).on('change', '.schedule-day-checkbox', function() {
            console.log('Day checkbox changed:', $(this).val(), 'checked:', $(this).is(':checked'));
            validateDaySelection();
            updatePerDayTimeControls(); // Add conditional display logic
            updateScheduleData();
            restrictStartDateBySelectedDays();

            // Check for holidays that conflict with the new day selection
            const startDate = $('#schedule_start_date').val();
            const endDate = $('#schedule_end_date').val();
            if (startDate) {
                checkForHolidays(startDate, endDate);
            }

            // Recalculate end date when day changes
            recalculateEndDate();
        });

        // Handle day of month selection changes
        $('#schedule_day_of_month').on('change', function() {
            updateScheduleData();

            // Check for holidays that conflict with the new day selection
            const startDate = $('#schedule_start_date').val();
            const endDate = $('#schedule_end_date').val();
            if (startDate) {
                checkForHolidays(startDate, endDate);
            }

            // Recalculate end date when day changes
            recalculateEndDate();
        });
    }

    /**
     * Validate that at least one day is selected
     */
    function validateDaySelection() {
        const anyDaySelected = $('.schedule-day-checkbox:checked').length > 0;
        const $daySelectionContainer = $('#day-selection-container');

        if (!$daySelectionContainer.hasClass('d-none')) {
            if (anyDaySelected) {
                $daySelectionContainer.find('.invalid-feedback').hide();
                $daySelectionContainer.find('.valid-feedback').show();
            } else {
                $daySelectionContainer.find('.invalid-feedback').show();
                $daySelectionContainer.find('.valid-feedback').hide();
            }
        }

        return anyDaySelected;
    }

    /**
     * Get all selected days
     */
    function getSelectedDays() {
        const selectedDays = [];
        $('.schedule-day-checkbox:checked').each(function() {
            selectedDays.push($(this).val());
        });
        console.log('getSelectedDays:', selectedDays);
        return selectedDays;
    }

    /**
     * Helper function to get day name from day index
     */
    function getDayName(dayIndex) {
        const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        return days[dayIndex];
    }

    /**
     * Update per-day time controls based on selected days
     */
    function updatePerDayTimeControls() {
        console.log('updatePerDayTimeControls called');
        const selectedDays = getSelectedDays();
        const $singleTimeControls = $('#single-time-controls');
        const $perDayTimeControls = $('#per-day-time-controls');
        const $perDaySectionsContainer = $('#per-day-sections-container');

        console.log('Selected days count:', selectedDays.length);
        console.log('Single time controls element:', $singleTimeControls.length);
        console.log('Per-day time controls element:', $perDayTimeControls.length);

        // Show/hide controls based on number of selected days
        if (selectedDays.length <= 1) {
            console.log('Showing single time controls');
            // Single day or no days: show single time controls
            $singleTimeControls.removeClass('d-none');
            $perDayTimeControls.addClass('d-none');

            // Clear per-day sections
            $perDaySectionsContainer.empty();
        } else {
            console.log('Showing per-day time controls');
            // Multiple days: show per-day time controls
            $singleTimeControls.addClass('d-none');
            $perDayTimeControls.removeClass('d-none');

            // Generate per-day sections
            generatePerDaySections(selectedDays);
        }
    }

    /**
     * Generate per-day time sections for selected days
     */
    function generatePerDaySections(selectedDays) {
        console.log('generatePerDaySections called with:', selectedDays);
        const $container = $('#per-day-sections-container');
        const $template = $('#day-time-section-template');

        console.log('Container element:', $container.length);
        console.log('Template element:', $template.length);

        // Clear existing sections
        $container.empty();

        // Create section for each selected day
        selectedDays.forEach(function(day, index) {
            console.log('Creating section for day:', day);
            const $section = $template.clone();
            $section.removeClass('d-none').removeAttr('id');
            $section.attr('data-day', day);

            // Update day name in header
            $section.find('.day-name').text(day);

            // Update data-day attributes for form elements
            $section.find('.day-start-time').attr('data-day', day).attr('name', 'day_start_time[' + day + ']');
            $section.find('.day-end-time').attr('data-day', day).attr('name', 'day_end_time[' + day + ']');

            // Show copy button only on first day
            if (index === 0) {
                $section.find('.copy-to-all-btn').show();
            } else {
                $section.find('.copy-to-all-btn').hide();
            }

            $container.append($section);
            console.log('Section appended for day:', day);
        });

        console.log('Total sections created:', $container.children().length);

        // Initialize event handlers for new sections
        initPerDayTimeHandlers();
    }

    /**
     * Initialize event handlers for per-day time controls
     */
    function initPerDayTimeHandlers() {
        // Handle time changes for duration calculation
        $('.day-start-time, .day-end-time').off('change.perday').on('change.perday', function() {
            const day = $(this).attr('data-day');
            calculatePerDayDuration(day);
            updateScheduleData();
        });

        // Handle copy to all days functionality
        $('.copy-to-all-btn').off('click.perday').on('click.perday', function() {
            const $section = $(this).closest('.per-day-time-section');
            const sourceDay = $section.attr('data-day');
            const startTime = $section.find('.day-start-time').val();
            const endTime = $section.find('.day-end-time').val();

            if (startTime && endTime) {
                // Copy times to all other day sections
                $('.per-day-time-section').not($section).each(function() {
                    $(this).find('.day-start-time').val(startTime).trigger('change');
                    $(this).find('.day-end-time').val(endTime).trigger('change');
                });
            }
        });
    }

    /**
     * Calculate duration for a specific day
     */
    function calculatePerDayDuration(day) {
        const $section = $('.per-day-time-section[data-day="' + day + '"]');
        const startTime = $section.find('.day-start-time').val();
        const endTime = $section.find('.day-end-time').val();
        const $durationDisplay = $section.find('.duration-value');

        if (startTime && endTime) {
            // Parse times
            const [startHour, startMinute] = startTime.split(':').map(Number);
            const [endHour, endMinute] = endTime.split(':').map(Number);

            // Calculate duration in hours
            let durationHours = endHour - startHour;
            let durationMinutes = endMinute - startMinute;

            if (durationMinutes < 0) {
                durationHours--;
                durationMinutes += 60;
            }

            // Format duration
            const duration = durationHours + (durationMinutes / 60);
            $durationDisplay.text(duration.toFixed(1));
        } else {
            $durationDisplay.text('-');
        }
    }

    /**
     * Reset to single time controls (for monthly/custom patterns)
     */
    function resetToSingleTimeControls() {
        const $singleTimeControls = $('#single-time-controls');
        const $perDayTimeControls = $('#per-day-time-controls');
        const $perDaySectionsContainer = $('#per-day-sections-container');

        // Show single time controls, hide per-day controls
        $singleTimeControls.removeClass('d-none');
        $perDayTimeControls.addClass('d-none');

        // Clear per-day sections
        $perDaySectionsContainer.empty();
    }

    /**
     * Initialize time selection and duration calculation
     */
    function initTimeSelection() {
        const $startTime = $('#schedule_start_time');
        const $endTime = $('#schedule_end_time');
        const $duration = $('#schedule_duration');

        // Calculate duration when times change
        $startTime.add($endTime).on('change', function() {
            calculateDuration();
            updateScheduleData();
            // Recalculate end date when duration changes
            recalculateEndDate();
        });

        // Calculate duration based on selected times
        function calculateDuration() {
            const startTime = $startTime.val();
            const endTime = $endTime.val();

            if (startTime && endTime) {
                // Parse times
                const [startHour, startMinute] = startTime.split(':').map(Number);
                const [endHour, endMinute] = endTime.split(':').map(Number);

                // Calculate duration in hours
                let durationHours = endHour - startHour;
                let durationMinutes = endMinute - startMinute;

                if (durationMinutes < 0) {
                    durationHours--;
                    durationMinutes += 60;
                }

                // Format duration
                const duration = durationHours + (durationMinutes / 60);
                $duration.val(duration.toFixed(1));
            } else {
                $duration.val('');
            }
        }

        // Calculate initial duration on page load for existing data
        calculateDuration();

        // Initialize date fields
        const $startDate = $('#schedule_start_date');
        const $classType = $('#class_type');

        // Update end date when start date or class type changes
        $startDate.add($classType).on('change', function() {
            console.log('Start date or class type changed, recalculating end date');

            // If start date changed, validate against class original start date
            if ($(this).attr('id') === 'schedule_start_date') {
                const startDate = $(this).val();
                const originalStartDate = $('#class_start_date').val();

                // Validate schedule start date against original start date
                if (startDate && originalStartDate && startDate < originalStartDate) {
                    // Show validation error
                    $(this).addClass('is-invalid');
                    $(this).siblings('.invalid-feedback').text('Start date cannot be before the class original start date');
                } else {
                    // Clear validation error
                    $(this).removeClass('is-invalid');
                    $(this).siblings('.invalid-feedback').text('Please select a start date.');
                }
            }

            // Use recalculateEndDate instead of calculateEndDate to account for exception dates
            recalculateEndDate();
            updateScheduleData();
        });
    }

    /**
     * Helper function to get class type hours
     */
    function getClassTypeHours(classTypeId) {
        // This would typically come from the server
        // For now, we'll use a simple mapping based on the class types
        const classTypeHours = {
            'AET_COMM': 120,
            'AET_NUM': 120,
            'GETC': 120,
            'BA_NQF2': 120,
            'BA_NQF3': 120,
            'BA_NQF4': 120,
            'REALLL': 160,
            'SKILL_PACKAGE': 80,
            'SOFT_SKILL': 40
        };

        // Default to 120 hours if not found
        return classTypeHours[classTypeId] || 120;
    }

    /**
     * Initialize exception dates
     */
    function initExceptionDates() {
        const $container = $('#exception-dates-container');
        const $template = $('#exception-date-row-template');
        const $addButton = $('#add-exception-date-btn');

        // Add exception date row
        $addButton.on('click', function() {
            console.log('Adding exception date row');
            const $newRow = $template.clone();
            $newRow.removeClass('d-none').removeAttr('id');
            $container.append($newRow);

            // Initialize remove button
            $newRow.find('.remove-exception-btn').on('click', function() {
                console.log('Removing exception date row');
                $newRow.remove();
                updateScheduleData();
                // Ensure end date is recalculated when an exception date is removed
                recalculateEndDate();
            });

            // Update schedule data when date or reason changes
            $newRow.find('input, select').on('change', function() {
                console.log('Exception date or reason changed');

                // Validate exception date against class start date
                const exceptionDate = $newRow.find('input[name="exception_dates[]"]').val();
                const startDate = $('#schedule_start_date').val();

                if (exceptionDate && startDate && exceptionDate < startDate) {
                    // Show validation error
                    $newRow.find('input[name="exception_dates[]"]').addClass('is-invalid');
                    $newRow.find('.invalid-feedback').text('Exception date cannot be before the class start date');
                } else {
                    // Clear validation error
                    $newRow.find('input[name="exception_dates[]"]').removeClass('is-invalid');
                    $newRow.find('.invalid-feedback').text('Please select a valid date.');
                }

                updateScheduleData();
                // Ensure end date is recalculated when an exception date is changed
                recalculateEndDate();
            });
        });
    }

    // Placeholder functions for features not yet implemented
    function initHolidayOverrides() {
        console.log('Holiday overrides functionality initialized (placeholder)');
    }

    function initScheduleDataUpdates() {
        console.log('Schedule data updates functionality initialized (placeholder)');
    }

    function updateScheduleData() {
        console.log('Updating schedule data (placeholder)');
    }

    function checkForHolidays(startDate, endDate) {
        console.log('Checking for holidays (placeholder)');
    }

    function recalculateEndDate() {
        console.log('Recalculating end date (placeholder)');
    }

    function restrictStartDateBySelectedDays() {
        console.log('Restricting start date by selected days (placeholder)');
    }

    // Initialize when document is ready
    console.log('class-schedule-form.js: About to set up document ready handler');
    $(document).ready(function() {
        console.log('class-schedule-form.js: Document ready, jQuery version:', $.fn.jquery);
        console.log('class-schedule-form.js: Looking for schedule_pattern element...');

        // Debug: Check all elements on page
        console.log('class-schedule-form.js: Total elements on page:', $('*').length);
        console.log('class-schedule-form.js: Schedule pattern element:', $('#schedule_pattern'));
        console.log('class-schedule-form.js: Schedule pattern length:', $('#schedule_pattern').length);

        // Check if we're on a page with the class schedule form
        if ($('#schedule_pattern').length > 0) {
            console.log('class-schedule-form.js: Schedule pattern element found, initializing form...');
            console.log('class-schedule-form.js: Day checkboxes found:', $('.schedule-day-checkbox').length);
            initClassScheduleForm();
        } else {
            console.log('class-schedule-form.js: Schedule pattern element not found');

            // Try again after a short delay in case of timing issues
            setTimeout(function() {
                console.log('class-schedule-form.js: Retrying after delay...');
                if ($('#schedule_pattern').length > 0) {
                    console.log('class-schedule-form.js: Schedule pattern found on retry, initializing...');
                    initClassScheduleForm();
                } else {
                    console.log('class-schedule-form.js: Still not found after delay');
                }
            }, 1000);
        }
    });

})(jQuery);
