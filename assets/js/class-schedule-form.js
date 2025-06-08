/**
 * Class Schedule Form JavaScript for WeCoza Classes Plugin
 *
 * Handles the client-side functionality for the class schedule form.
 * Extracted from WeCoza theme for standalone plugin
 */
(function($) {
    'use strict';

    // Holiday override data
    var holidayOverrides = {};

    /**
     * Initialize the class schedule form
     */
    function initClassScheduleForm() {
        // Initialize schedule pattern selection
        initSchedulePatternSelection();

        // Initialize time selection and duration calculation
        initTimeSelection();

        // Initialize exception dates
        initExceptionDates();

        // Initialize holiday overrides
        initHolidayOverrides();

        // Initialize schedule data updates
        initScheduleDataUpdates();
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
            } else if (pattern === 'monthly') {
                $dayOfMonth.removeClass('d-none');
                $('#schedule_day_of_month').attr('required', 'required');
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
            updateScheduleData();
            restrictStartDateBySelectedDays();
            recalculateEndDate();
        });

        $('#clear-all-days').on('click', function() {
            $('.schedule-day-checkbox').prop('checked', false);
            validateDaySelection();
            updateScheduleData();
        });

        // Handle day checkbox changes
        $('.schedule-day-checkbox').on('change', function() {
            validateDaySelection();
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
    $(document).ready(function() {
        // Check if we're on a page with the class schedule form
        if ($('#schedule_pattern').length > 0) {
            initClassScheduleForm();
        }
    });

})(jQuery);
